<?php

/**
 * Get LPV settings for a specified level.
 *
 * @param  int   $level_id The ID of the level to get limits for.
 * @return array $limit    Limit for this level. 2 values in the array 'views' and 'period'.
 */
function pmprolpv_get_level_limit( $level_id ) {
	$default_option = array(
		'views' => '',
		'period' => 'Month',
	);
	return get_option( 'pmprolpv_limit_' . $level_id, $default_option );	
}

/**
 * Check if we want to allow free views for a given post.
 *
 * @param WP_POST $post The post to check.
 * @return bool True if we want to allow free views for this post type, false otherwise.
 */
function pmprolpv_allow_free_views_for_post( $post ) {
	// If $post is not a WP_Post object, return false.
	if ( ! is_a( $post, 'WP_Post' ) ) {
		return false;
	}

	/**
	 * Filter which post types should be tracked by LPV.
	 *
	 * @since 0.4
	 *
	 * @param array $allowed_post_types Array of post types to track.
	 */
	$allowed_post_types = apply_filters( 'pmprolpv_post_types', array( 'post' ) );
	if ( empty( $post->post_type ) || ! in_array( $post->post_type, $allowed_post_types, true ) ) {
		return false;
	}

	/**
	 * Filter whether LPV should allow free views for a given post.
	 *
	 * @param bool  $allow_free_views True if we want to allow free views for this post type, false otherwise.
	 * @param WP_POST $post           The post to check.
	 */
	return apply_filters( 'pmprolpv_has_membership_access', true, $post );
}
