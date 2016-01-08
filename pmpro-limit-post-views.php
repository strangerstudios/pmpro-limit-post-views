<?php
/*
Plugin Name: Paid Memberships Pro - Limit Post Views Add On
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-limit-post-views/
Description: Integrates with Paid Memberships Pro to limit the number of times non-members can view posts on your site.
Version: .3
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/
/*
	The Idea
	- Track a cookie on the user's computer.
	- Only track on pages the user doesn't have access to.
	- Allow up to 4 views without a membership level.
	- On 4th view each month, redirect to a specific page to get them to sign up.
*/

/*
	NOTE: These constants are no longer needed. Instead you should remove them from your custom plugin
		  and set the values on the Memberships -> Limit Post Views settings page in the dashboard.
	
	define('PMPRO_LPV_LIMIT', 3);			//<-- how many posts can a user view per month
	define('PMPRO_LPV_USE_JAVASCRIPT', false);
*/

/**
 * Include required setup files.
 *
 * @since 0.3.0
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/admin.php' );

/**
 * Set up limit and whether or not to use JavaScript.
 *
 * @since 0.3.0
 */
function pmprolpv_init() {

	// Check for backwards compatibility
	if ( ! defined( 'PMPRO_LPV_LIMIT' ) ) {

		global $current_user;
		if(!empty($current_user->membership_level))
			$level_id = $current_user->membership_level->id;
		if ( empty( $level_id ) )
			$level_id = 0;

		$limit = get_option( 'pmprolpv_limit_' . $level_id );
		if(!empty($limit)) {
			define( 'PMPRO_LPV_LIMIT', $limit['views'] );
			define( 'PMPRO_LPV_LIMIT_PERIOD', $limit['period'] );
		}
	}

	// Check for backwards compatibility
	if ( ! defined( 'PMPRO_LPV_USE_JAVASCRIPT' ) ) {
		$use_js = get_option( 'pmprolpv_use_js' );
		define( 'PMPRO_LPV_USE_JAVASCRIPT', $use_js );
	}

}

add_action( 'init', 'pmprolpv_init' );

//php limit (deactivate JS version below if you use this)
add_action( "wp", "pmpro_lpv_wp" );
function pmpro_lpv_wp() {

	if ( function_exists( "pmpro_has_membership_access" ) ) {
		/*
			If we're viewing a page that the user doesn't have access to...
			Could add extra checks here.
		*/

		if ( ! pmpro_has_membership_access() ) {

			//ignore non-posts
			$queried_object = get_queried_object();						
			if ( empty($queried_object) || empty($queried_object->post_type) || $queried_object->post_type != "post" ) {			
				return;
			}

			$hasaccess = apply_filters('pmprolpv_has_membership_access', true, $queried_object);

			if ( false === $hasaccess ) {

				pmpro_lpv_redirect();
			}

			//if we're using javascript, just give them access and let JS redirect them
			if ( PMPRO_LPV_USE_JAVASCRIPT ) {
				wp_enqueue_script( 'wp-utils', includes_url( '/js/utils.js' ) );
				add_action( "wp_footer", "pmpro_lpv_wp_footer" );
				add_filter( "pmpro_has_membership_access_filter", "__return_true" );


				return;
			}

			//PHP is going to handle cookie check and redirect
			$thismonth = date( "n" );

			//check for past views
			if ( ! empty( $_COOKIE['pmpro_lpv_count'] ) ) {
				$parts = explode( ",", $_COOKIE['pmpro_lpv_count'] );
				$month = $parts[1];
				if ( $month == $thismonth ) {
					$count = intval( $parts[0] ) + 1;
				}    //same month as other views
				else {
					$count = 1;                        //new month
					$month = $thismonth;
				}
			} else {
				//new user
				$count = 1;
				$month = $thismonth;
			}

			//if count is above limit, redirect, otherwise update cookie
			if ( $count > PMPRO_LPV_LIMIT ) {

				pmpro_lpv_redirect();

			} else {
				//give them access and track the view
				add_filter( "pmpro_has_membership_access_filter", "__return_true" );

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

				setcookie( 'pmpro_lpv_count', $count . ',' . $month, $expires, '/' );
			}
		}
	}
}

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

	wp_redirect( $redirect_url );    //here is where you can change which page is redirected to
	exit;
}
/*
	javascript limit (hooks for these are above)
	this is only loaded on pages that are locked for members
*/
function pmpro_lpv_wp_footer() {
	?>
	<script>
		//vars
		var pmpro_lpv_count;		//stores cookie
		var parts;					//cookie convert to array of 2 parts
		var count;					//part 0 is the view count
		var month;					//part 1 is the month

		//what is the current month?
		var d = new Date();
		var thismonth = d.getMonth();

		//get cookie
		pmpro_lpv_count = wpCookies.get('pmpro_lpv_count');

		if (pmpro_lpv_count) {
			//get values from cookie
			parts = pmpro_lpv_count.split(',');
			month = parts[1];
			if (month == thismonth)
				count = parseInt(parts[0]) + 1;	//same month as other views
			else {
				count = 1;						//new month
				month = thismonth;
			}
		}
		else {
			//defaults
			count = 1;
			month = thismonth;
		}

		//if count is above limit, redirect, otherwise update cookie
		if (count > <?php echo intval(PMPRO_LPV_LIMIT); ?>) {
			<?php
				$page_id = get_option('pmprolpv_redirect_page');
				if(empty($page_id))
					$redirect_url = pmpro_url('levels');
				else
					$redirect_url = get_the_permalink($page_id);
			?>
			window.location.replace('<?php echo $redirect_url;?>');
		}
		else {
			<?php
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
				
				if(empty($expires))
					$expires = DAY_IN_SECONDS * 30;
			?>
			//track the view
			wpCookies.set('pmpro_lpv_count', String(count) + ',' + String(month), <?php echo $expires; ?>, '/');
		}
	</script>
	<?php
}

/*
Function to add links to the plugin row meta
*/
function pmpro_lpv_plugin_row_meta($links, $file) {
	if(strpos($file, 'pmpro-limit-post-views.php') !== false)
	{
		$new_links = array(
			'<a href="' . esc_url('http://www.paidmembershipspro.com/add-ons/plugins-on-github/pmpro-limit-post-views/')  . '" title="' . esc_attr( __( 'View Documentation', 'pmpro' ) ) . '">' . __( 'Docs', 'pmpro' ) . '</a>',
			'<a href="' . esc_url('http://paidmembershipspro.com/support/') . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro' ) ) . '">' . __( 'Support', 'pmpro' ) . '</a>',
		);
		$links = array_merge($links, $new_links);
	}
	return $links;
}
add_filter('plugin_row_meta', 'pmpro_lpv_plugin_row_meta', 10, 2);