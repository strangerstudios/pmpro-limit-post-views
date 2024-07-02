<?php
/**
 * Admin functions
 *
 * Sets up admin pages and options.
 *
 * @since 0.3.0
 * @package PMPro_Limit_Post_views
 */

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
		add_submenu_page( 'pmpro-dashboard', __( 'Limit Post Views', 'pmpro-limit-post-views' ), __( 'Limit Post Views', 'pmpro-limit-post-views' ), $cap, 'pmpro-limitpostviews', 'pmprolpv_settings_page' );
	} else {
		add_submenu_page( 'pmpro-membershiplevels', __( 'Limit Post Views', 'pmpro-limit-post-views' ), __( 'Limit Post Views', 'pmpro-limit-post-views' ), $cap, 'pmpro-limitpostviews', 'pmprolpv_settings_page' );
	}
}
add_action( 'admin_menu', 'pmprolpv_admin_menu' );

/**
 * Include settings page.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_page() {
	require_once( PMPROLPV_DIR . '/adminpages/limitpostviews.php' );
}

/**
 * Register settings sections and fields.
 *
 * @since 0.3.0
 */
function pmprolpv_admin_init() {
	if ( function_exists( 'pmpro_getAllLevels' ) ) {
		// Register non-member limits settings section.
		add_settings_section(
			'pmprolpv_non_member_limits',
			'',
			'pmprolpv_settings_section_non_member_limits',
			'pmpro-limitpostviews',
			array(
				'before_section' => '<div class="pmpro_section">',
				'after_section' => '</div></div>',
			),
		);

		// Register member limits settings section.
		add_settings_section(
			'pmprolpv_member_limits',
			'',
			'pmprolpv_settings_section_member_limits',
			'pmpro-limitpostviews',
			array(
				'before_section' => '<div class="pmpro_section">',
				'after_section' => '</div></div>',
			),
		);

		// Register redirection settings section.
		add_settings_section(
			'pmprolpv_redirection',
			'',
			'pmprolpv_settings_section_redirection',
			'pmpro-limitpostviews',
			array(
				'before_section' => '<div class="pmpro_section">',
				'after_section' => '</div></div>',
			),
		);

		// Register non-member limit settings field.
		add_settings_field(
			'pmprolpv_limit_0',
			__( 'Non-members', 'pmpro-limit-post-views' ),
			'pmprolpv_settings_field_limits',
			'pmpro-limitpostviews',
			'pmprolpv_non_member_limits',
			0
		);

		// Register JavaScript setting.
		register_setting(
			'pmpro-limitpostviews',
			'pmprolpv_limit_0',
			'pmprolpv_sanitize_limit'
		);

		// Register member limit settings field.
		$levels = pmpro_getAllLevels( true, true );
		asort( $levels );
		foreach ( $levels as $id => $level ) {
			$title = $level->name;
			add_settings_field(
				'pmprolpv_limit_' . $id,
				$title,
				'pmprolpv_settings_field_limits',
				'pmpro-limitpostviews',
				'pmprolpv_member_limits',
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
			'pmprolpv_redirect_page',
			__( 'Redirect to', 'pmpro-limit-post-views' ),
			'pmprolpv_settings_field_redirect_page',
			'pmpro-limitpostviews',
			'pmprolpv_redirection'
		);

		// Register redirection setting.
		register_setting(
			'pmpro-limitpostviews',
			'pmprolpv_redirect_page'
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
