<?php

/**
 * Give all users access to all posts. LPV will redirect away if the user runs out of free views.
 *
 * @since 1.0
 *
 * @param bool $has_access Whether the user has access to the post.
 * @param WP_Post $post    The post being checked.
 * @return bool $has_access True if the user has access to the post.
 */
function pmprolpv_has_membership_access_filter( $has_access, $post ) {
	// Check if we want to allow free views for this post type.
	if ( ! pmprolpv_allow_free_views_for_post( $post ) ) {
		return $has_access;
	}
	return true;
}
add_filter( 'pmpro_has_membership_access_filter', 'pmprolpv_has_membership_access_filter', 10, 2 );

/**
 * Enqueue frontend script to restrict content when needed.
 *
 * @since 1.0
 */
function pmprolpv_wp_enqueue_scripts() {
	wp_register_script( 'pmprolpv', plugins_url( 'js/pmprolpv.js', PMPROLPV_BASE_FILE ), array( 'jquery' ), PMPROLPV_VERSION, array( 'in_footer' => true ) );
	wp_localize_script( 'pmprolpv', 'pmprolpv', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'pmprolpv' );
}
add_action( 'wp_enqueue_scripts', 'pmprolpv_wp_enqueue_scripts' );

/**
 * Handler for thepmprolpv_get_restriction_js AJAX endpoint.
 *
 * @since 1.0
 */
function pmprolpv_get_restriction_js() {
	// Check parameters.
	if ( empty( $_REQUEST['url'] ) ) {
		wp_send_json_error( 'No URL provided.' );
	}

	// Get the post ID for the passed URL.
	$post_id = url_to_postid( $_REQUEST['url'] );
	if ( empty( $post_id ) ) {
		wp_send_json_error( 'Invalid URL.' );
	}

	// Check if we want to allow free views for this post type.
	$post = get_post( $post_id );
	if ( ! pmprolpv_allow_free_views_for_post( $post ) ) {
		wp_send_json_success( 'return;' );
	}

	// Check if the user has access to the post.
	// If they don't even when we are filtering to true, then another plugin is blocking access. Let them continue handling it.
	if ( ! pmpro_has_membership_access( $post_id ) ) {
		wp_send_json_success( '' );
	}

	// Unhook the LPV has_access filter to see if the user truly has access to this post.
	remove_filter( 'pmpro_has_membership_access_filter', 'pmprolpv_has_membership_access_filter', 10, 2 );

	// Check if the user has access to the post. If they do, then they don't need to use a LPV view to access this content.
	if ( pmpro_has_membership_access( $post_id ) ) {
		wp_send_json_success( 'return;' );
	}

	// The user should not have access to this post. Check if they still have LPV views remaining.
	// LPV post view data is stored in the cookie pmprolpv cookie as a string [post_id],[timestamp];
	$lpv_data_string = isset( $_COOKIE['pmprolpv'] ) ? $_COOKIE['pmprolpv'] : '';
	$lpv_data_array  = array();
	if ( ! empty( $lpv_data_string ) ) {
	    foreach ( explode( ';', $lpv_data_string ) as $lpv_data ) {
		$lpv_data_parts = explode( ',', $lpv_data );
		if ( count( $lpv_data_parts ) === 2 ) {
		    $lpv_data_array[] = array(
			'post_id' => (int) $lpv_data_parts[0],
			'timestamp' => (int) $lpv_data_parts[1],
		    );
		}
	    }
	}

	// Get the number of posts this hour, day, week, and month.
	$lpv_data_period_counts = array(
		'hour' => 0,
		'day' => 0,
		'week' => 0,
		'month' => 0,
	);

	$current_time = current_time( 'timestamp' );

	foreach ( $lpv_data_array as $lpv_data ) {
	    $viewed_time = $lpv_data['timestamp'];
	    if ( $viewed_time >= strtotime( '-1 hour', $current_time ) ) {
	        $lpv_data_period_counts['hour']++;
	    }
	    if ( $viewed_time >= strtotime( '-1 day', $current_time ) ) {
	        $lpv_data_period_counts['day']++;
	    }
	    if ( $viewed_time >= strtotime( '-1 week', $current_time ) ) {
	        $lpv_data_period_counts['week']++;
	    }
	    if ( $viewed_time >= strtotime( '-1 month', $current_time ) ) {
	        $lpv_data_period_counts['month']++;
	    }
	}

	// Get all of the user's current membership level IDs.
	$user_levels    = pmpro_getMembershipLevelsForUser();
	$user_level_ids = empty( $user_levels ) ? array( 0 ) : wp_list_pluck( $user_levels, 'ID' );

	// Get the maximum remaining views for the user's membership levels.
	$views_remaining = 0;
	$level_views     = 0;
	$level_period    = '';
	foreach ( $user_level_ids as $level_id ) {
		// Get the limits for this level.
		$level_limit = pmprolpv_get_level_limit( $level_id );

		// Update $views_remaining based on this level's data.
		if ( $level_limit['views'] - $lpv_data_period_counts[ $level_limit['period'] ] > $views_remaining ) {
			$views_remaining = $level_limit['views'] - $lpv_data_period_counts[ $level_limit['period'] ];
			$level_views     = $level_limit['views'];
			$level_period    = $level_limit['period'];
		} elseif ( empty( $views_remaining ) && empty( $level_views ) && empty( $level_period ) && ! empty( $level_limit['views'] ) ) {
			// In cases where we haven't found a level with views remaining, try to find a level with views set in case we want to show a banner with "used views" or something.
			$level_views  = $level_limit['views'];
			$level_period = $level_limit['period'];
		}
	}

	// If the user has remaining views, track the view in the cookie and alert the number of views remaining.
	if ( $views_remaining > 0 ) {
		$lpv_data_string .= ';' . $post_id . ',' . $current_time;
		setcookie( 'pmprolpv', $lpv_data_string, $current_time + 60 * 60 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN );

		// Decrement views_remaining now that we added a view.
		$views_remaining--;

		$notification_js = '';
		/**
		 * Filter the JavaScript to run when LPV grants access to a post.
		 * For example, this can be used to show a popup or a banner with the remaining view count.
		 *
		 * @since 1.0
		 *
		 * @param string  $notification_js JavaScript to run when LPV grants access to a post.
		 * @param int     $views_remaining Number of views remaining.
		 * @param int     $level_views     Number of views allowed for the user's level.
		 * @param string  $level_period    Period for the user's level.
		 * @param WP_POST $post            The post being viewed.
		 */
		$notification_js = apply_filters( 'pmprolpv_allow_view_js', $notification_js, $views_remaining, $level_views, $level_period, $post );
		wp_send_json_success( $notification_js );
	}

	// The user has used all of their views. Redirect them to the LPV redirect page.
	$page_id = get_option( 'pmprolpv_redirect_page' );
	$redirect_url = empty( $page_id ) ? pmpro_url( 'levels' ) : get_the_permalink( $page_id );
	$restriction_js = 'window.location.href = "' . esc_url( $redirect_url ) . ' ";';
	/**
	 * Filter the JavaScript to run when LPV denies access to a post.
	 * For example, this could be used to blur the page and show a message or redirect.
	 *
	 * @since 1.0
	 *
	 * @param string $restriction_js JavaScript to run when LPV denies access to a post.
	 * @param int    $level_views    Number of views allowed for the user's level.
	 * @param string $level_period   Period for the user's level.
	 * @param WP_POST $post          The post being viewed.
	 */
	$restriction_js = apply_filters( 'pmprolpv_deny_view_js', $restriction_js, $level_views, $level_period, $post );
	wp_send_json_success( $restriction_js );
}
add_action( 'wp_ajax_pmprolpv_get_restriction_js', 'pmprolpv_get_restriction_js' );
add_action( 'wp_ajax_nopriv_pmprolpv_get_restriction_js', 'pmprolpv_get_restriction_js' );
