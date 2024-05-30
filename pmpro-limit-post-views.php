<?php
/**
 * Plugin Name: Paid Memberships Pro - Limit Post Views Add On
 * Plugin URI: https://www.paidmembershipspro.com/add-ons/pmpro-limit-post-views/
 * Description: Integrates with Paid Memberships Pro to limit the number of times members and visitors can view posts on your site.
 * Version: 0.6.1
 * Author: Paid Memberships Pro
 * Author URI: https://www.paidmembershipspro.com
 * Text Domain: pmpro-limit-post-views
 * Domain Path: /languages
 */

define( 'PMPROLPV_BASE_FILE', __FILE__ );
define( 'PMPROLPV_BASENAME', plugin_basename( __FILE__ ) );
define( 'PMPROLPV_DIR', dirname( __FILE__ ) );
define( 'PMPROLPV_VERSION', '0.6.1' );

require_once( PMPROLPV_DIR . '/includes/functions.php' ); // Common functions.
require_once( PMPROLPV_DIR . '/includes/admin.php' ); // Settings page.
require_once( PMPROLPV_DIR . '/includes/restriction.php' ); // Restricting content.
require_once( PMPROLPV_DIR . '/includes/deprecated.php' ); // Deprecated functions.

/**
 * Load the languages folder for translations.
 *
 * @since TBD
 */
function pmprolpv_load_textdomain() {
	load_plugin_textdomain( 'pmpro-limit-post-views', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'pmprolpv_load_textdomain' );

/**
 * Function to add links to the plugin action links
 *
 * @param array $links Array of links to be shown in plugin action links.
 */
function pmprolpv_plugin_action_links( $links ) {
	if ( current_user_can( 'manage_options' ) ) {
		$new_links = array(
			'<a href="' . get_admin_url( null, 'admin.php?page=pmpro-limitpostviews' ) . '">' . __( 'Settings', 'pmpro-limit-post-views' ) . '</a>',
		);
	}
	return array_merge( $new_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pmprolpv_plugin_action_links' );

/**
 * Function to add links to the plugin row meta
 *
 * @param array  $links Array of links to be shown in plugin meta.
 * @param string $file Filename of the plugin meta is being shown for.
 */
function pmprolpv_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'pmpro-limit-post-views.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/add-ons/pmpro-limit-post-views/' ) . '" title="' . esc_attr( __( 'View Documentation', 'limit-post-views' ) ) . '">' . __( 'Docs', 'limit-post-views' ) . '</a>',
			'<a href="' . esc_url( 'http://paidmembershipspro.com/support/' ) . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'limit-post-views' ) ) . '">' . __( 'Support', 'limit-post-views' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmprolpv_plugin_row_meta', 10, 2 );
