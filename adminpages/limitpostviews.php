<?php
/**
 * Paid Memberships Pro - Limit Post Views Settings Page
 *
 * Displays settings page.
 *
 * @since 0.3.0
 * @package PMPro_Limit_Post_Views
 */

// Check permissions first.
if ( ! current_user_can( apply_filters( 'pmpro_edit_member_capability', 'manage_options' ) ) ) {
	wp_die( 'You do not have sufficient permissions to access this page.' );
}

require_once( PMPRO_DIR . '/adminpages/admin_header.php' );

/**
 * Display non-member limits section.
 *
 * @since TBD
 */
function pmprolpv_settings_section_non_member_limits() { ?>
	<div id="pmprolpv-non-member-limits" class="pmpro_section_toggle" data-visibility="hidden" data-activated="false">
		<button class="pmpro_section-toggle-button" type="button" aria-expanded="false">
			<span class="dashicons dashicons-arrow-up-alt2"></span>
			<?php esc_html_e( 'Non-Member Post Views', 'pmpro-limit-post-views' ); ?>
		</button>
	</div>
	<div class="pmpro_section_inside">
		<p><?php esc_html_e( 'Give non-members limited access to view protected posts. This setting includes site visitors and logged-in users without an active membership level.', 'pmpro-limit-post-views' ); ?></p>
	<?php
}

/**
 * Display member limits section.
 *
 * @since TBD
 */
function pmprolpv_settings_section_member_limits() { ?>
	<div id="pmprolpv-member-limits" class="pmpro_section_toggle" data-visibility="hidden" data-activated="false">
		<button class="pmpro_section-toggle-button" type="button" aria-expanded="false">
			<span class="dashicons dashicons-arrow-down-alt2"></span>
			<?php esc_html_e( 'Member Post Views', 'pmpro-limit-post-views' ); ?>
		</button>
	</div>
	<div class="pmpro_section_inside" style="display: none;">
		<p><?php esc_html_e( 'Give members limited access to view posts that they do not already have access to view.', 'pmpro-limit-post-views' ); ?></p>
	<?php
}

/**
 * Display membership limits field.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_field_limits( $level_id ) {
	$limit = pmprolpv_get_level_limit( $level_id );

	$period = ( !empty( $limit['period'] ) ) ? $limit['period'] : 'month';
	$views = ( !empty( $limit['views'] ) ) ? $limit['views'] : '';
	?>
	<input size="2" type="number" id="level_<?php echo esc_attr( $level_id ); ?>_views"
	       name="pmprolpv_limit_<?php echo esc_attr( $level_id ); ?>[views]" value="<?php echo esc_attr( $views ); ?>">
	<?php esc_html_e( ' views per ', 'pmpro-limit-post-views' ); ?>
	<select name="pmprolpv_limit_<?php echo esc_attr( $level_id ); ?>[period]" id="level_<?php echo esc_attr( $level_id ); ?>_period">
		<option
			value="hour" <?php selected( $period, 'hour' ); ?>><?php esc_html_e( 'Hour', 'pmpro-limit-post-views' ); ?></option>
		<option
			value="day" <?php selected( $period, 'day' ); ?>><?php esc_html_e( 'Day', 'pmpro-limit-post-views' ); ?></option>
		<option
			value="week" <?php selected( $period, 'week' ); ?>><?php esc_html_e( 'Week', 'pmpro-limit-post-views' ); ?></option>
		<option
			value="month" <?php selected( $period, 'month' ); ?>><?php esc_html_e( 'Month', 'pmpro-limit-post-views' ); ?></option>
	</select>
	<?php
}

/**
 * Display redirection section.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_section_redirection() { ?>
	<div id="pmprolpv-redirection" class="pmpro_section_toggle" data-visibility="hidden" data-activated="false">
		<button class="pmpro_section-toggle-button" type="button" aria-expanded="false">
			<span class="dashicons dashicons-arrow-up-alt2"></span>
			<?php esc_html_e( 'Redirection Settings', 'pmpro-limit-post-views' ); ?>
		</button>
	</div>
	<div class="pmpro_section_inside">
	<?php
}

/**
 * Display redirection field.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_field_redirect_page() {
	global $pmpro_pages;
	$page_id = get_option( 'pmprolpv_redirect_page' );

	// Default to Levels page.
	if ( empty( $page_id ) ) {
		$page_id = $pmpro_pages['levels'];
	}

	wp_dropdown_pages( array(
		'selected' => esc_html( $page_id ),
		'name' => 'pmprolpv_redirect_page',
	) );

	echo '<p class="description">' . esc_html( __( 'Set the page to redirect users to once they have reached their view limit.', 'pmpro-limit-post-views' ) ) . '</p>';

}

// Display settings page.
?>
	<h1><?php esc_html_e( 'Limit Post Views Settings', 'pmpro-limit-post-views' ); ?></h1>
	<form action="options.php" method="POST">
		<?php settings_fields( 'pmpro-limitpostviews' ); ?>
		<?php do_settings_sections( 'pmpro-limitpostviews' ); ?>
		<?php submit_button(); ?>
	</form>
<?php

require_once( PMPRO_DIR . '/adminpages/admin_footer.php' );
