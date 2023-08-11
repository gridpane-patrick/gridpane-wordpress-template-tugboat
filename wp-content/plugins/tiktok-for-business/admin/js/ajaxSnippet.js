jQuery( function( $ ) {
	$( document.body ).on( 'added_to_cart', function( e, fragments, cart_hash, thisbutton ) {
	var pixel_code = window.tt4b_script_vars.pixel_code;
	var currency = window.tt4b_script_vars.currency;
	var product_id = thisbutton.data( 'product_id' );
	var product_name = thisbutton.data( 'product_name' );
	var price = thisbutton.data( 'price' );

	ttq.instance(pixel_code).track('AddToCart',
		{
			'content_id': product_id,
			'content_name': product_name,
			'content_type': 'product',
			'price': price,
			'value': price,
			'quantity': 1,
			'currency': currency
		}
		);
	});
});

