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
 * Display membership limits section.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_section_limits() {
	echo '<p>' . esc_html( __( 'Allow visitors or members limited access to view protected content.', 'pmpro-limit-post-views' ) ) . '</p>';
}

/**
 * Display membership limits field.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_field_limits( $level_id ) {
	$limit = get_option( 'pmprolpv_limit_' . $level_id );
	?>
	<input size="2" type="text" id="level_<?php echo esc_attr( $level_id ); ?>_views"
	       name="pmprolpv_limit_<?php echo esc_attr( $level_id ); ?>[views]" value="<?php echo esc_attr( $limit['views'] ); ?>">
	<?php esc_html_e( ' views per ', 'pmpro-limit-post-views' ); ?>
	<select name="pmprolpv_limit_<?php echo esc_attr( $level_id ); ?>[period]" id="level_<?php echo esc_attr( $level_id ); ?>_period">
		<option value="hour" <?php if( ! empty( $limit['period'] ) ) { selected( $limit['period'], 'hour' ); } ?>><?php esc_html_e( 'Hour', 'pmpro-limit-post-views' ); ?></option>
		<option value="day" <?php if( !empty( $limit['period'] ) ) { selected( $limit['period'], 'day' ); } ?>><?php esc_html_e( 'Day', 'pmpro-limit-post-views' ); ?></option>
		<option value="week" <?php if( !empty( $limit['period'] ) ) { selected( $limit['period'], 'week' ); } ?>><?php esc_html_e( 'Week', 'pmpro-limit-post-views' ); ?></option>
		<option value="month" <?php if( !empty( $limit['period'] ) ) { selected( $limit['period'], 'month' ); } ?>><?php esc_html_e( 'Month', 'pmpro-limit-post-views' ); ?></option>
	</select>
	<?php
}

/**
 * Display redirection section.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_section_redirection() {
	echo '<p>' . esc_html( __( 'Control redirection behavior when a visitor or member reaches their limit.', 'pmpro-limit-post-views' ) ) . '</p>';
}

/**
 * Disable Redirection
 */
function pmprolpv_settings_field_disable_redirection(){
	$disable_redir = get_option( 'pmprolpv_disable_redirect' );
	?>
	<input value="1" type="checkbox" id="pmprolpv_disable_redirect" name="pmprolpv_disable_redirect" <?php checked( $disable_redir, 1 ); ?>>
	<label for="pmprolpv_disable_redirect"><?php esc_html_e( 'Do not redirect away from a protected post when a visitor or member reaches their limit.', 'pmpro-limit-post-views' ); ?></label>
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
		'selected' => $page_id,
		'name' => 'pmprolpv_redirect_page',
	) );
}

/**
 * Display JavaScript field.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_field_use_js() {
	$use_js = get_option( 'pmprolpv_use_js' );
	?>
	<input value="1" type="checkbox" id="use_js" name="pmprolpv_use_js" <?php checked( $use_js, 1 ); ?>>
	<label for="use_js"><?php _e("If you have page caching enabled or the PHP redirect otherwise won't work, check this to add our JS code to protected pages.", 'pmpro-limit-post-views' ); ?></label>
	<?php
}

/**
 * Display layout and design section.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_section_layout() {
	echo '<p>' . esc_html( __( 'Control the display and appearance of the optional banner showing the number of views remaining for this visitor or member.', 'pmpro-limit-post-views' ) ) . '</p>';
}

/**
 * Display Content Overlay
 *
 */
function pmprolpv_settings_field_content_overlay() {
	$overlay = get_option( 'pmprolpv_content_overlay' );
	?>
	<select name="pmprolpv_content_overlay" id="pmprolpv_content_overlay">
		<option value="none" <?php selected( $overlay, 'none' ); ?>><?php _e( 'None', 'pmpro-limit-post-views' ); ?></option>
		<option value="top" <?php selected( $overlay, 'top' ); ?>><?php _e( 'Top of Content', 'pmpro-limit-post-views' ); ?></option>
		<option value="bottom" <?php selected( $overlay, 'bottom' ); ?>><?php _e( 'Bottom of Content', 'pmpro-limit-post-views' ); ?></option>
		<option value="floating" <?php selected( $overlay, 'floating' ); ?>><?php _e( 'Floating', 'pmpro-limit-post-views' ); ?></option>
	</select>
	<?php
}

/**
 * Banner Background Color.
 *
 */
function pmprolpv_settings_field_content_background() {
	$color = pmprolpv_banner_background();
	?>
	<input type="text" name="pmprolpv_content_background" class="color-picker" value="<?php echo $color; ?>" />
	<?php
}

/**
 * Banner Text Color.
 *
 */
function pmprolpv_settings_field_content_text() {
	$color = pmprolpv_banner_text();
	?>
	<input type="text" name="pmprolpv_content_text" class="color-picker" value="<?php echo $color; ?>" />
	<?php

}

/**
 * Display settings page.
 *
 */
?>
	<h1><?php esc_html_e( 'Limit Post Views Add On', 'pmpro-limit-post-views' ); ?></h1>	
	<hr />
	<h2><?php esc_html_e( 'How This Plugin Works', 'pmpro-limit-post-views' );?></h2>
	<p><?php esc_html_e( 'This plugin allows visitors and members access to protected content based the settings below. Sites can choose to show a banner with number of views remaining and can control the redirection settings once the limit is reached.', 'pmpro-limit-post-views'); ?>
	<p><?php printf( __( 'By default, this plugin will only allow limited access to WordPress posts. You can <a href="%s" target="_blank">apply these limits to other post types by following the instructions here</a>.', 'pmpro-limit-post-views' ), 'https://www.paidmembershipspro.com/offer-limited-access-to-restricted-page-or-custom-post-type-content-using-the-limit-post-views-add-on/' ); ?></p>
	<hr />
	<form action="options.php" method="POST">
		<?php settings_fields( 'pmpro-limitpostviews' ); ?>
		<?php do_settings_sections( 'pmpro-limitpostviews' ); ?>
		<?php submit_button(); ?>
	</form>
<?php

require_once( PMPRO_DIR . '/adminpages/admin_footer.php' );
