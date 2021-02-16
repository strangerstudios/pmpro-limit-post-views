jQuery(document).ready(function($){
	jQuery(".color-picker").each( function( key, val ){
		jQuery(this).wpColorPicker();	
	});

	jQuery('#pmprolpv_disable_redirect').change(function () {
		if ( jQuery('#pmprolpv_disable_redirect').is(':checked') ) {
			jQuery('#pmprolpv_redirect_page').prop('disabled', true);
			jQuery('#use_js').prop('disabled', true);
        } else {
			jQuery('#pmprolpv_redirect_page').prop('disabled', false)
			jQuery('#use_js').prop('disabled', false);
        }
    }).change();
});
