/**
 * Product Meta Boxes.
 *
 * @package WC_Store_Credit/Assets/Js/Admin
 * @since   3.2.0
 */

(function( $ ) {

	'use strict';

	$(function() {

		var wc_store_credit_meta_boxes_product = {

			fields: {},

			/**
			 * Initialize actions.
			 */
			init: function() {
				this.addShowHideClasses();

				// Trigger the initial change event.
				$( 'select#product-type' ).trigger( 'change' );

				this.bindEvents();
			},

			/**
			 * Bind events.
			 */
			bindEvents: function() {
				var that = this;

				this.getField( 'allow_different_receiver' ).on( 'change', function() {
					that.toggleFields( ['display_receiver_fields', 'receiver_fields_title'], $( this ).prop( 'checked' ) )
				}).trigger( 'change' );

				this.getField( 'allow_custom_amount' ).on( 'change', function() {
					that.toggleFields( ['min_custom_amount', 'max_custom_amount', 'custom_amount_step'], $( this ).prop( 'checked' ) )
				}).trigger( 'change' );
			},

			/**
			 * Add the show/hide classes to the store credit fields.
			 */
			addShowHideClasses: function() {
				$( '#general_product_data .pricing.show_if_simple' ).addClass( 'show_if_store_credit' );
				$( '#general_product_data #_tax_status' ).closest( '.show_if_simple' ).addClass( 'show_if_store_credit' );

				$( '#inventory_product_data ._manage_stock_field' ).addClass( 'show_if_store_credit' );
				$( '#inventory_product_data ._sold_individually_field' )
					.addClass( 'show_if_store_credit' )
					.closest( '.options_group' )
					.addClass( 'show_if_store_credit' );
			},

			/**
			 * Gets the jQuery object which represents the form field.
			 */
			getField: function( key ) {
				// Load field on demand.
				if ( ! this.fields[ key ] ) {
					this.fields[ key ] = $( '#_store_credit_' + key );
				}

				return this.fields[ key ];
			},

			/**
			 * Handles the visibility of the form fields.
			 */
			toggleFields: function( keys, visible ) {
				var that = this;

				if ( ! Array.isArray( keys ) ) {
					keys = [ keys ];
				}

				$.each( keys, function( index, key ) {
					that.getField( key ).closest( '.form-field' ).toggle( visible );
				});
			}
		};

		wc_store_credit_meta_boxes_product.init();
	});
})( jQuery );
