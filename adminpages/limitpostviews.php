<?php
/**
 * PMPro Limit Post Views settings page
 *
 * Displays settings page.
 *
 * @since 0.3.0
 * @package PMPro_Limit_Post_Views
 */

// Check permissions first
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
	echo '<p>' . __( 'Limit post views by membership level below. Users without the specified membership level will be able to view that many posts which they normally would not have access to.', 'pmpro' ) . '</p>';
}

/**
 * Display memberhsip limits field.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_field_limits( $level_id ) {
	$limit = get_option( 'pmprolpv_limit_' . $level_id );
	?>
	<input size="2" type="text" id="level_<?php echo $level_id; ?>_views"
	       name="pmprolpv_limit_<?php echo $level_id; ?>[views]" value="<?php echo $limit['views']; ?>">
	<?php _e( ' views per ', 'pmprolpv' ); ?>
	<select name="pmprolpv_limit_<?php echo $level_id; ?>[period]" id="level_<?php echo $level_id; ?>_period">
		<option
			value="hour" <?php selected( $limit['period'], 'hour' ); ?>><?php _e( 'Hour', 'pmprolpv' ); ?></option>
		<option
			value="day" <?php selected( $limit['period'], 'day' ); ?>><?php _e( 'Day', 'pmprolpv' ); ?></option>
		<option
			value="week" <?php selected( $limit['period'], 'week' ); ?>><?php _e( 'Week', 'pmprolpv' ); ?></option>
		<option
			value="month" <?php selected( $limit['period'], 'month' ); ?>><?php _e( 'Month', 'pmprolpv' ); ?></option>
	</select>
	<?php
}

/**
 * Display redirection section.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_section_redirection() {
}

/**
 * Display redirection field.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_field_redirect_page() {

	global $pmpro_pages;
	$page_id = get_option('pmprolpv_redirect_page');

	// Default to Levels page
	if(empty($page_id))
		$page_id = $pmpro_pages['levels'];

	wp_dropdown_pages(array(
		'selected' => $page_id,
		'name' => 'pmprolpv_redirect_page'
	));
}

/**
 * Display JavaScript field.
 *
 * @since 0.3.0
 */
function pmprolpv_settings_field_use_js() {
	$use_js = get_option('pmprolpv_use_js');
	?>
	<input value="1" type="checkbox" id="use_js" name="pmprolpv_use_js" <?php checked($use_js, 1); ?>>
	<?php
}

// Display settings page.
?>
	<h2><?php _e( 'PMPro Limit Post Views', 'pmprolpv' ); ?></h2>
	<form action="options.php" method="POST">
		<?php settings_fields( 'pmpro-limitpostviews' ); ?>
		<?php do_settings_sections( 'pmpro-limitpostviews' ); ?>
		<?php submit_button(); ?>
	</form>
<?php

require_once(PMPRO_DIR . '/adminpages/admin_footer.php');