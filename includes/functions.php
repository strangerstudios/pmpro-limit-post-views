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
