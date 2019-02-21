<?php
/**
 * Plugin Name: Paid Memberships Pro - Limit Post Views Add On
 * Plugin URI: https://www.paidmembershipspro.com/add-ons/pmpro-limit-post-views/
 * Description: Integrates with Paid Memberships Pro to limit the number of times members and visitors can view posts on your site.
 * Version: .5
 * Author: Paid Memberships Pro
 * Author URI: https://www.paidmembershipspro.com
 */

require_once( plugin_dir_path( __FILE__ ) . 'includes/admin.php' );

/**
 * Set up limit and whether or not to use JavaScript.
 *
 * @since 0.3.0
 */
function pmprolpv_init() {

	// Check for backwards compatibility.
	if ( ! defined( 'PMPRO_LPV_LIMIT' ) ) {
		global $current_user;
		if ( ! empty( $current_user->membership_level ) ) {
			$level_id = $current_user->membership_level->id;
		}
		if ( empty( $level_id ) ) {
			$level_id = 0;
		}

		$limit = get_option( 'pmprolpv_limit_' . $level_id );
		if ( ! empty( $limit ) ) {
			define( 'PMPRO_LPV_LIMIT', $limit['views'] );
			define( 'PMPRO_LPV_LIMIT_PERIOD', $limit['period'] );
		}
	}

	// Check for backwards compatibility.
	if ( ! defined( 'PMPRO_LPV_USE_JAVASCRIPT' ) ) {
		$use_js = get_option( 'pmprolpv_use_js' );
		define( 'PMPRO_LPV_USE_JAVASCRIPT', $use_js );
	}
}

add_action( 'init', 'pmprolpv_init' );

/**
 * Limit post views or load JS to do the same.
 * Hooks into wp action: add_action( 'wp', 'pmpro_lpv_wp' );
 */
function pmpro_lpv_wp() {
	global $current_user;
	if ( function_exists( 'pmpro_has_membership_access' ) ) {
		/*
			If we're viewing a page that the user doesn't have access to...
			Could add extra checks here.
		*/
		if ( ! pmpro_has_membership_access() ) {
			/**
			 * Filter which post types should be tracked by LPV
			 *
			 * @since .4
			 */
			$pmprolpv_post_types = apply_filters( 'pmprolpv_post_types', array( 'post' ) );
			$queried_object = get_queried_object();
			
			if ( empty( $queried_object ) || empty( $queried_object->post_type ) || ! in_array( $queried_object->post_type, $pmprolpv_post_types, true ) ) {
				return;
			}

			$hasaccess = apply_filters( 'pmprolpv_has_membership_access', true, $queried_object );
			if ( false === $hasaccess ) {
				pmpro_lpv_redirect();
			}

			// if we're using javascript, just give them access and let JS redirect them.
			if ( defined( 'PMPRO_LPV_USE_JAVASCRIPT' ) && PMPRO_LPV_USE_JAVASCRIPT ) {
				wp_enqueue_script( 'wp-utils', includes_url( '/js/utils.js' ) );
				add_action( 'wp_footer', 'pmpro_lpv_wp_footer' );
				add_filter( 'pmpro_has_membership_access_filter', '__return_true' );

				return;
			}

			// PHP is going to handle cookie check and redirect.
			$thismonth = date( 'n', current_time( 'timestamp' ) );
			$level = pmpro_getMembershipLevelForUser( $current_user->ID );
			if ( ! empty( $level->id ) ) {
				$level_id = $level->id;
			} else {
				$level_id = null;
			}

			// check for past views.
			if ( ! empty( $_COOKIE['pmpro_lpv_count'] ) ) {
				$month = $thismonth;
				$parts = explode( ';', sanitize_text_field( $_COOKIE['pmpro_lpv_count'] ) );
				if ( count( $parts ) > 1 ) { // just in case.
					$month = $parts[1];
				} else { // for one-time cookie format migration.
					$parts[0] = '0,0';
				}
				$limitparts = explode( ',', $parts[0] );
				$levellimits = array();
				$length = count( $limitparts );
				for ( $i = 0; $i < $length; $i++ ) {
					if ( $i % 2 === 1 ) {
						$levellimits[ $limitparts[ $i -1 ] ] = $limitparts[ $i ];
					}
				}
				if ( $month == $thismonth && array_key_exists( $level_id, $levellimits ) ) {
					$count = $levellimits[ $level_id ] + 1; // same month as other views.
					$levellimits[ $level_id ]++;
				} elseif ( $month == $thismonth ) { // same month, but we haven't ticked yet.
					$count = 1;
					$levellimits[ $level_id ] = 1;
				} else {
					$count = 1;                     // new month.
					$levellimits = array();
					$levellimits[ $level_id ] = 1;
					$month = $thismonth;
				}
			} else {
				// new user.
				$count = 1;
				$levellimits = array();
				$levellimits[ $level_id ] = 1;
				$month = $thismonth;
			}

			// if count is above limit, redirect, otherwise update cookie.
			if ( defined( 'PMPRO_LPV_LIMIT' ) && $count > PMPRO_LPV_LIMIT ) {
				pmpro_lpv_redirect();
			} else {
				// give them access and track the view.
				add_filter( 'pmpro_has_membership_access_filter', '__return_true' );

				if ( defined( 'PMPRO_LPV_LIMIT_PERIOD' ) ) {
					switch ( PMPRO_LPV_LIMIT_PERIOD ) {
						case 'hour':
							$expires = current_time( 'timestamp' ) + HOUR_IN_SECONDS;
							break;
						case 'day':
							$expires = current_time( 'timestamp' ) + DAY_IN_SECONDS;
							break;
						case 'week':
							$expires = current_time( 'timestamp' ) + WEEK_IN_SECONDS;
							break;
						case 'month':
							$expires = current_time( 'timestamp' ) + ( DAY_IN_SECONDS * 30 );
					}
				} else {
					$expires = current_time( 'timestamp' ) + ( DAY_IN_SECONDS * 30 );
				}

				// put the cookie string back together with updated values.
				$cookiestr = '';
				foreach ( $levellimits as $curlev => $curviews ) {
					$cookiestr .= "$curlev,$curviews";
				}
				setcookie( 'pmpro_lpv_count', $cookiestr . ';' . $month, $expires, '/' );
			}
		}
	}
}
add_action( 'wp', 'pmpro_lpv_wp' );

/**
 * Redirect to  the configured page or the default levels page
 */
function pmpro_lpv_redirect() {

	$page_id = get_option( 'pmprolpv_redirect_page' );

	if ( empty( $page_id ) ) {
		$redirect_url = pmpro_url( 'levels' );
	} else {
		$redirect_url = get_the_permalink( $page_id );
	}

	wp_redirect( $redirect_url );    // here is where you can change which page is redirected to.
	exit;
}

/**
	Javascript limit (hooks for these are above)
	This is only loaded on pages that are locked for members
 */
function pmpro_lpv_wp_footer() {
	global $current_user;
	
	// Get the current user's level id.
	if ( ! empty( $current_user->membership_level ) ) {
		$level_id = $current_user->membership_level->id;
	}
	if ( empty( $level_id ) ) {
		$level_id = 0;
	}
	
	// Figure out the redirect URL.
	$page_id = get_option( 'pmprolpv_redirect_page' );
	if ( empty( $page_id ) ) {
		$redirect_url = pmpro_url( 'levels' );
	} else {
		$redirect_url = get_the_permalink( $page_id );
	}
	
	// Figure out the expiration period.
	if ( defined( 'PMPRO_LPV_LIMIT_PERIOD' ) ) {
		switch ( PMPRO_LPV_LIMIT_PERIOD ) {
			case 'hour':
				$expires = HOUR_IN_SECONDS;
				break;
			case 'day':
				$expires = DAY_IN_SECONDS;
				break;
			case 'week':
				$expires = WEEK_IN_SECONDS;
				break;
			case 'month':
				$expires = DAY_IN_SECONDS * 30;
		}
	}
	if ( empty( $expires ) ) {
		$expires = DAY_IN_SECONDS * 30;
	}
?>
	<script>
		//vars
		var pmpro_lpv_count;        //stores cookie
		var parts;                  //cookie convert to array of 2 parts
		var count;                  //part 0 is the view count
		var month;                  //part 1 is the month
		var newticks = [];          // this will hold our usage this month by level
		
		//what is the current month?
		var d = new Date();
		var thismonth = d.getMonth();
		
		// set mylevel to user's current level.		
		var mylevel = <?php echo json_encode( intval( $level_id ) ); ?>;
		
		//get cookie
		pmpro_lpv_count = wpCookies.get('pmpro_lpv_count');

		if (pmpro_lpv_count) {          
			//get values from cookie
			parts = pmpro_lpv_count.split(';');
			month = parts[1];
			if(month === undefined) { month = thismonth; parts[0] = "0,0"; } // just in case, and for cookie format migration
			limitparts = parts[0].split(',');
			var limitarrlength = limitparts.length;
			var curkey = -1;
			for (var i = 0; i < limitarrlength; i ++) {
				if(i % 2 == 0) {
					curkey = parseInt(limitparts[i], 10);
				} else {
					newticks[curkey] = parseInt(limitparts[i], 10);
					curkey = -1;
				}
			}
			if (month == thismonth && newticks[mylevel] !== undefined) {
				count = newticks[mylevel] + 1;  // same month as other views
				newticks[mylevel]++;            // advance it for writing to the cookie
			} else if(month == thismonth) { // it's the current month, but we haven't ticked yet.
				count = 1;
				newticks[mylevel] = 1;
			} else {
				count = 1;                      //new month
				newticks = [];                  // new month, so we don't care about old ticks
				newticks[mylevel] = 1;
				month = thismonth;
			}
		}
		else {
			//defaults
			count = 1;
			newticks[mylevel] = 1;
			month = thismonth;
		}

		// if count is above limit, redirect, otherwise update cookie.
		if ( count > <?php echo intval( PMPRO_LPV_LIMIT ); ?>) {	
			window.location.replace('<?php echo $redirect_url;?>');
		} else {			
			// put the cookie string back together with updated values.
			var arrlen = newticks.length;
			var outstr = "";
			for(var i=0;i<arrlen;i++) {
				if(newticks[i] !== undefined) {
					outstr += "," + i + "," + newticks[i];
				}
			}
			// output the cookie to track the view
			wpCookies.set('pmpro_lpv_count', outstr.slice(1) + ';' + String(month), <?php echo $expires; ?>, '/');
		}
	</script>
	<?php
}

/**
 * Function to add links to the plugin action links
 *
 * @param array $links Array of links to be shown in plugin action links.
 */
function pmpro_lpv_plugin_action_links( $links ) {
	if ( current_user_can( 'manage_options' ) ) {
		$new_links = array(
			'<a href="' . get_admin_url( null, 'admin.php?page=pmpro-limitpostviews' ) . '">' . __( 'Settings', 'pmpro-limit-post-views' ) . '</a>',
		);
	}
	return array_merge( $new_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pmpro_lpv_plugin_action_links' );


/**
 * Function to add links to the plugin row meta
 *
 * @param array  $links Array of links to be shown in plugin meta.
 * @param string $file Filename of the plugin meta is being shown for.
 */
function pmpro_lpv_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'pmpro-limit-post-views.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/add-ons/pmpro-limit-post-views/' ) . '" title="' . esc_attr( __( 'View Documentation', 'pmpro' ) ) . '">' . __( 'Docs', 'pmpro' ) . '</a>',
			'<a href="' . esc_url( 'http://paidmembershipspro.com/support/' ) . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro' ) ) . '">' . __( 'Support', 'pmpro' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmpro_lpv_plugin_row_meta', 10, 2 );
