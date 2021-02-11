<?php
/**
 * Admin functions
 *
 * Sets up admin pages and options.
 *
 * @since 0.3.0
 * @package PMPro_Limit_Post_views
 */

function pmprolpv_admin_scripts(){

	if( !empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'pmpro-limitpostviews' ){

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script('jquery');
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'pmprolpv-admin-script', PMPRO_LPV_URL.'/assets/js/admin.js', array( 'wp-color-picker','jquery' ), false, true );

	}

}
add_action( 'admin_enqueue_scripts', 'pmprolpv_admin_scripts' );

/**
 * Add settings page to admin menu.
 *
 * @since 0.3.0
 */
function pmprolpv_admin_menu() {
	if ( ! defined( 'PMPRO_VERSION' ) ) {
        return;
    }
	
	$cap = apply_filters( 'pmpro_edit_member_capability', 'manage_options' );

	if( version_compare( PMPRO_VERSION, '2.0' ) >= 0 ) {
		add_submenu_page( 'pmpro-dashboard', __( 'Limit Post Views', 'pmpro-limitpostviews' ), __( 'Limit Post View', 'pmpro-limitpostviews' ), $cap, 'pmpro-limitpostviews', 'pmprolpv_settings_page' );
	} else {
		add_submenu_page( 'pmpro-membershiplevels', __( 'Limit Post Views', 'pmpro-limitpostviews' ), __( 'Limit Post View', 'pmpro-limitpostviews' ), $cap, 'pmpro-limitpostviews', 'pmprolpv_settings_page' );
	}
}
add_action( 'admin_menu', 'pmprolpv_admin_menu' );

/**
 * Include settings page.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_page() {
	require_once( plugin_dir_path( __FILE__ ) . '../adminpages/limitpostviews.php' );
}

/**
 * Register settings sections and fields.
 *
 * @since 0.3.0
 */
function pmprolpv_admin_init() {
	if ( function_exists( 'pmpro_getAllLevels' ) ) {
		// Register limits settings section.
		add_settings_section(
			'pmprolpv_limits',
			'Membership Post View Limits',
			'pmprolpv_settings_section_limits',
			'pmpro-limitpostviews'
		);

		// Register redirection settings section.
		add_settings_section(
			'pmprolpv_redirection',
			'Redirection',
			'pmprolpv_settings_section_redirection',
			'pmpro-limitpostviews'
		);

		// Register limits settings fields.
		$levels = pmpro_getAllLevels( true, true );
		$levels[0] = new stdClass();
		$levels[0]->name = __( 'Non-members', 'pmpro' );
		asort( $levels );
		foreach ( $levels as $id => $level ) {
			$title = $level->name;
			add_settings_field(
				'pmprolpv_limit_' . $id,
				$title,
				'pmprolpv_settings_field_limits',
				'pmpro-limitpostviews',
				'pmprolpv_limits',
				$id
			);

			// Register JavaScript setting.
			register_setting(
				'pmpro-limitpostviews',
				'pmprolpv_limit_' . $id,
				'pmprolpv_sanitize_limit'
			);
		}

		// Register redirection settings field.
		add_settings_field(
			'pmprolpv_content_overlay',
			'Enable Content Banner',
			'pmprolpv_settings_field_content_overlay',
			'pmpro-limitpostviews',
			'pmprolpv_redirection'
		);

		// Register redirection setting.
		register_setting(
			'pmpro-limitpostviews',
			'pmprolpv_content_overlay'
		);


		// Register redirection settings field.
		add_settings_field(
			'pmprolpv_content_background',
			'Banner Background Color',
			'pmprolpv_settings_field_content_background',
			'pmpro-limitpostviews',
			'pmprolpv_redirection'
		);
		
		// Register redirection setting.
		register_setting(
			'pmpro-limitpostviews',
			'pmprolpv_content_background'
		);

		// Register redirection settings field.
		add_settings_field(
			'pmprolpv_content_text',
			'Banner Text Color',
			'pmprolpv_settings_field_content_text',
			'pmpro-limitpostviews',
			'pmprolpv_redirection'
		);
		
		// Register redirection setting.
		register_setting(
			'pmpro-limitpostviews',
			'pmprolpv_content_text'
		);

		// Register redirection settings field.
		add_settings_field(
			'pmprolpv_redirect_page',
			'Redirect to',
			'pmprolpv_settings_field_redirect_page',
			'pmpro-limitpostviews',
			'pmprolpv_redirection'
		);

		// Register redirection setting.
		register_setting(
			'pmpro-limitpostviews',
			'pmprolpv_redirect_page'
		);

		// Register JavaScript settings field.
		add_settings_field(
			'pmprolpv_use_js',
			'Use JavaScript redirection',
			'pmprolpv_settings_field_use_js',
			'pmpro-limitpostviews',
			'pmprolpv_redirection'
		);

		// Register JavaScript setting.
		register_setting(
			'pmpro-limitpostviews',
			'pmprolpv_use_js'
		);
	}
}
add_action( 'admin_init', 'pmprolpv_admin_init' );

/**
 * Sanitize limit fields
 *
 * @since 0.3.0
 * @param $args
 *
 * @return mixed
 */
function pmprolpv_sanitize_limit( $args ) {
	if ( ! is_numeric( $args['views'] ) ) {
		$args['views'] = '';
		$args['period'] = '';
	}

	return $args;
}
