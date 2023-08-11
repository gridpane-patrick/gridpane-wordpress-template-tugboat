let vibe_split_orders = (function( $ ) {

	/**
	 * The line items div
	 */
	let $postbox;

	let _init = function() {
		$postbox = $( '#woocommerce-order-items' );

		if ( $postbox.length > 0 ) {
			$postbox.on( 'click', 'button.split-order', _begin_split_order );
		}

		$( document.body ).on( 'wc_backbone_modal_response', _popup_confirmed );
	};

	let _begin_split_order = function( e ) {
		e.preventDefault();

		_show_popup();
	};

	let _block_el = function( $el ) {
		$el.block( {
			message: null, overlayCSS: {
				background: '#fff', opacity: 0.6
			}
		} );
	};

	let _unblock_el = function( $el ) {
		$el.unblock();
	};

	let _show_popup = function() {
		$( this ).WCBackboneModal( {
			template: 'wc-modal-split-order'
		} );

		_refresh_popup();
	};

	let _popup_confirmed = function( event, target ) {
		if ( 'wc-modal-split-order' === target ) {
			_split_order();
		}
	};

	let _refresh_popup = function() {
		_block_el( $( '#modal-split-order-line-items' ) );

		let data = {
			action:   'vibe_split_orders_popup_line_items',
			nonce:    vibe_split_orders_data.popup_nonce,
			order_id: $( '#post_ID' ).val()
		};

		$.get( vibe_split_orders_data.ajaxurl, data, _show_popup_response )
	};

	let _show_popup_response = function( response ) {
		let $modal_container = $( '#modal-split-order-line-items' );
		_unblock_el( $modal_container );

		if ( response.success ) {
			$modal_container.html( response.html );
		}
	};

	let _split_order = function() {
		_block_el( $postbox );

		let $popup = $( '#split-orders-popup' );
		let items = {};

		$popup.find( '.item' ).each( function() {
			let $item = $( this );

			let qty = parseFloat( $item.find( '.qty-split' ).val() );

			if ( qty ) {
				items[ $item.data( 'item-id' ) ] = qty;
			}
		} );

		let data = {
			action:   'vibe_split_orders_split_order',
			nonce:    vibe_split_orders_data.splitting_nonce,
			items:    items,
			order_id: $popup.data( 'order-id' )
		};

		$.post( vibe_split_orders_data.ajaxurl, data, _split_order_response );
	};

	let _split_order_response = function( response ) {
		_unblock_el( $postbox );

		if ( response.success ) {
			window.location.reload();
		}
	};

	$( _init );

	return {};

})( jQuery );