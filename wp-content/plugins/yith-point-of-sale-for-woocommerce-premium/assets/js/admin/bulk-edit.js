/* global admin_i18n */
( function ( $ ) {
    var visibility = $( '#woocommerce-fields-bulk select.visibility' );

	if ( ! visibility.find( 'option[value=yith_pos]' ).length ) {
		visibility.append(
			$(
				'<option value="yith_pos">' +
					admin_i18n.pos_results_only +
					'</option>'
			)
		);
	}
} )( jQuery );
