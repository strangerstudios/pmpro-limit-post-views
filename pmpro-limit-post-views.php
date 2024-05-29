<?php
/**
 * Plugin Name: Paid Memberships Pro - Limit Post Views Add On
 * Plugin URI: https://www.paidmembershipspro.com/add-ons/pmpro-limit-post-views/
 * Description: Integrates with Paid Memberships Pro to limit the number of times members and visitors can view posts on your site.
 * Version: 0.6.1
 * Author: Paid Memberships Pro
 * Author URI: https://www.paidmembershipspro.com
 */

define( 'PMPROLPV_BASE_FILE', __FILE__ );
define( 'PMPROLPV_BASENAME', plugin_basename( __FILE__ ) );
define( 'PMPROLPV_DIR', dirname( __FILE__ ) );

require_once( PMPROLPV_DIR . '/includes/admin.php' );
require_once( PMPROLPV_DIR . '/includes/deprecated.php' );

/**
 * Get LPV settings for a specified level.
 *
 * @param  int   $level_id The ID of the level to get limits for.
 * @return array $limit    Limit for this level. 2 values in the array 'views' and 'period'.
 */
function pmpro_lpv_get_level_limit( $level_id ) {
	$default_option = array(
		'views' => '',
		'period' => 'Month',
	);
	return get_option( 'pmprolpv_limit_' . $level_id, $default_option );	
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
