jQuery( document ).ready( function( $ ) {
	// Make an AJAX call to the pmprolpv_get_restriction_js action.
    $.ajax( {
        url: pmprolpv.ajaxurl + '?action=pmprolpv_get_restriction_js',
        type: 'POST',
        data: {
            url: window.location.href
        },
        success: function( response ) {
            // If the response is not empty, eval it.
			console.log(response);
            if ( response.success ) {
                eval( response.data );
            }
        }
    } );
} );