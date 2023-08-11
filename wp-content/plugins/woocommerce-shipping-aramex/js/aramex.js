/* globals document, jQuery */
jQuery( document ).ready( function( $ ) {
	// Request pickup.
	$( '.aramex-pickup' ).click( function() {
		var button = $( this );
		var pickupDateDate = $( '.pickup_date' ).val();
		var pickupDateHour = $( '.pickup_date_hour' ).val();
		var pickupDateMinute = $( '.pickup_date_minute' ).val();
		var pickupDate = pickupDateDate + ' ' + pickupDateHour + ':' + pickupDateMinute;

		var orderId = button.data( 'id' );
		if ( pickupDate ) {
			// Show loader img.
			button.next( '.ajax-loader' ).show();

			var data = {
				'action': 'aramex_pickup',
				'order_id': orderId,
				'pickup_date': encodeURIComponent( pickupDate )
			};

			$.ajax( {
				url: ajaxurl,
				data: data,
				type: 'POST',
				success: function( msg ) {
					if ( 'done' === msg ) {
						location.reload( true );
					} else {
						var messages = $.parseJSON(msg);
						var ul = $( '<ul>' );
						$.each( messages, function( key, value ) {
							ul.append( '<li>' + value  + '</li>' );
						} );
						$( '.pickup_errors' ).addClass( 'error' ).html( ul );
					}
				}, complete: function() {
					// Hide loader img.
					$( '.ajax-loader' ).hide();
				}
			} );
		} else {
			alert( 'Add pickup date' );
		}

		return false;
	} );
} );
