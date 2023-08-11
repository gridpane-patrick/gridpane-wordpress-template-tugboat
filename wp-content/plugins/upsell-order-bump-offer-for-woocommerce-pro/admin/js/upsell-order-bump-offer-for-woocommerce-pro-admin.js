jQuery(document).ready(function ($) {

	// License.
	jQuery('#wps_bump_offer_license_key').on('click', function (e) {
		jQuery('#wps_upsell_bump_offer_license_activation_status').html('');
	});

	jQuery('form#wps_upsell_bump_license_form').on('submit', function (e) {

		e.preventDefault();

		$('#wps_upsell_bump_license_ajax_loader').css("display", "flex");

		var license_key = $('#wps_bump_offer_license_key').val();

		wps_upsell_bump_send_license_request(license_key);
	});

	function wps_upsell_bump_send_license_request(license_key) {

		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			url: wps_upsell_bump_ajaxurl.ajaxurl,
			data: { nonce: wps_upsell_bump_ajaxurl.auth_nonce, action: 'wps_upsell_bump_validate_license_key', purchase_code: license_key },

			success: function (data) {

				$('#wps_upsell_bump_license_ajax_loader').hide();

				if (data.status == true) {

					$("#wps_upsell_bump_offer_license_activation_status").css("color", "#42b72a");

					jQuery('#wps_upsell_bump_offer_license_activation_status').html(data.msg);

					location = wps_upsell_bump_ajaxurl.wps_upsell_bump_location;
				}

				else {

					$("#wps_upsell_bump_offer_license_activation_status").css("color", "#ff3333");

					jQuery('#wps_upsell_bump_offer_license_activation_status').html(data.msg);

					jQuery('#wps_bump_offer_license_key').val("");
				}
			}
		});
	}

	// After v1.2.0
	jQuery('#wps_ubo_offer_replace_target').on('click', function (e) {

		var is_update_needed = jQuery('#is_update_needed').val();

		if ('true' == is_update_needed) {

			jQuery(this).prop('checked', false);
			jQuery('.wps_ubo_update_popup_wrapper').addClass('wps_ubo_lite_update_popup_show');
			jQuery('body').addClass('wps_ubo_lite_go_pro_popup_body');
		}
	});

	// Onclick outside the div close for Update popup.
	jQuery('body').click(function (e) {

		if (e.target.className == 'wps_ubo_update_popup_wrapper wps_ubo_lite_update_popup_show') {

			jQuery('.wps_ubo_update_popup_wrapper').removeClass('wps_ubo_lite_update_popup_show');
			jQuery('body').removeClass('wps_ubo_lite_go_pro_popup_body');
		}
	});

	// Close popup on clicking buttons.
	jQuery('.wps_ubo_update_yes, .wps_ubo_update_no').click(function (e) {

		jQuery('.wps_ubo_update_popup_wrapper').removeClass('wps_ubo_lite_update_popup_show');
		jQuery('body').removeClass('wps_ubo_lite_go_pro_popup_body');

	});

	// If org is not updated then you might not use multischedule.
	if ('true' == wps_upsell_bump_ajaxurl.is_org_needs_update) {

		jQuery('.wc-bump-schedule-search').hide();
		jQuery('.wc-bump-schedule-search').closest('td').html('<i>Please update the plugin to use our new features.</i>');

		// Show a notice to update org plugin.
		jQuery('.wps_ubo_update_popup_wrapper').addClass('wps_ubo_lite_update_popup_show');
		jQuery('body').addClass('wps_ubo_lite_go_pro_popup_body');
	}

	/**=======================================
	 * New Meta form Js.
	 *  
	 * @since 1.3.0
	 =========================================*/

	/**
	 * Check and Show the table content.
	 */
	const showHideTableContent = ( tableObj='' ) => {
		if( tableObj.length == 0 ) {
			tableObj = jQuery('#wps_ubo_offer_meta_forms');
		}

		if (tableObj.prop("checked") == true) {
			jQuery('.wps-ubo-meta-form__table-wrap').slideDown();
		}
		else if (tableObj.prop("checked") == false) {
			jQuery('.wps-ubo-meta-form__table-wrap').slideUp();
		}
	}

	/**
	 * Show the Add new/edit form content.
	 */
	 const showDialog = ( event ) => {
		event.preventDefault();
		if (jQuery('body').hasClass('wps-modal--close')) {
			jQuery('body').removeClass('wps-modal--close');
		}
		jQuery('body').addClass('wps-modal--open');

	}

	/**
	 * Close the Add new/edit form content.
	 */
	 const closeDialog = ( event ) => {
		if (event) {
			event.preventDefault();			 
		}
		setTimeout(function () {
			jQuery('body').removeClass('wps-modal--open');
		}, 350);
		jQuery('body').addClass('wps-modal--close');
	}

	const renewForm = () => {
		$('.wps-ubo-meta-form').trigger("reset");
	}
	
	/**
	 * Check and Show the table content at load.
	 */
	setTimeout(() => {
		showHideTableContent();	
	}, 100);

	/**
	 * Check and Show the table content at change.
	 */
	jQuery('#wps_ubo_offer_meta_forms').on('click', function () {
		showHideTableContent( jQuery(this) );
	})

	/**
	 * Modal Js - Open ( Add / Edit ).
	 */
	$(document).on( 'click', '#wps-ubo-meta-add_new__btn, .wps-ubo-meta-form__table-icon--edit', function(e) {

		// Edit it.
		if( jQuery(this).attr('class').includes("icon--edit") ) {

			jQuery( '.wps-ubo-meta-form' ).removeClass( 'add-new-enabled' );
			jQuery( '.wps-ubo-meta-form' ).addClass( 'edit-enabled' );
			jQuery( '.wps-ubo-meta-form' ).attr( 'edit-enabled-key', jQuery(this).attr( 'data-row-id' ) );

			var savedValues = {};
			jQuery(this).closest('tr').addClass( 'edit-enabled-tr' );
			jQuery(this).closest('tr').find('td').each(function( index ) {
				switch (index) {
					case 0:
						savedValues.label = jQuery( this ).text();
						break;
					case 1:
						savedValues.placeholder = jQuery( this ).text();	
						break;
					case 2:
						savedValues.description = jQuery( this ).text();	
						break;
					case 3:
						savedValues.type = jQuery( this ).text();	
						break;
					case 4:
						savedValues.options = jQuery( this ).text();	
						break;
				}
			});

			// Row Id.
			savedValues.row = jQuery( this ).attr( 'data-row-id' );

			// Got the values add to the form.
			for ( var key in savedValues ) {
				jQuery( '.wps_add_new_field_' + key ).val(savedValues[key]);
			}

		} else {

			jQuery( '.wps-ubo-meta-form' ).removeClass( 'edit-enabled' );
			jQuery( '.wps-ubo-meta-form' ).attr( 'edit-enabled-key', '' );
			jQuery( '.wps-ubo-meta-form' ).addClass( 'add-new-enabled' );
			renewForm();
		}

		// Show Popup.
		showDialog(e);
	});

	/**
	 * Modal Js - Close.
	 */
	jQuery('.wps-ubo-meta-form__modal-close, .wps-ubo-meta-form__edit-modal-close').on('click', function (e) {
		closeDialog(e);
	});


	/**
	 * Modal Js - Submit.
	 */
	jQuery('.wps-ubo-meta-form').on('submit', function (e) {
		e.preventDefault();
		
		var new_row_id = jQuery( '#wps_ubo_meta_new_row_id' ).val();

		if ( jQuery( this ).hasClass( 'edit-enabled' ) ) {
			new_row_id = '';
			edit_row_id = jQuery( this ).attr( 'edit-enabled-key' );
		} else {
			new_row_id = parseInt( new_row_id ) + 1;
			edit_row_id = '';
		}
	
		var form_data = jQuery(this).serialize();

		form_data = form_data.replaceAll( 'wps_add_new_field_', '' );

		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			url: wps_upsell_bump_ajaxurl.ajaxurl,
			data: { 
				nonce: wps_upsell_bump_ajaxurl.auth_nonce,
				action: 'wps_upsell_bump_save_meta_form',
				form_data: form_data,
				order_bump: jQuery( '.wps_upsell_bump_id' ).val(),
				new_row_id: new_row_id,
				edit_row_id: edit_row_id,
			},
			success: function (data) {

				if ( data.operation == 'new' ) {
					jQuery('.wps-ubo-meta-form__table').append( data.html );
					jQuery( '.wps-ubo-meta-form__no_fields' ).remove();
					jQuery( '#wps_ubo_meta_new_row_id' ).val( new_row_id );
				} else {
					jQuery('.edit-enabled-tr').replaceWith(data.html);
					jQuery( '.edit-enabled-tr' ).removeClass( 'edit-enabled-tr' );
				}

				closeDialog();
			}
		});	
	});

	/**
	 * Delete.
	 */
	$(document).on( 'click', '.wps-ubo-meta-form__table--icon--delete', function(e) {
		e.preventDefault();
		jQuery(this).closest('tr').remove();
		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			url: wps_upsell_bump_ajaxurl.ajaxurl,
			data: { 
				nonce: wps_upsell_bump_ajaxurl.auth_nonce,
				action: 'wps_upsell_bump_delete_meta_row',
				order_bump: jQuery( '.wps_upsell_bump_id' ).val(),
				del_row_id: jQuery( this ).attr( 'data-row-id' ),
			},
			success: function () {
			}
		});	
	});
	/*==========================================================================
								JS for Quantity functionality
	============================================================================*/
	$('#wps_upsell_offer_quantity_type_id').on('change', function() {
		if ( this.value === 'fixed_q' ) {
			$('.wps_variable_quantity').hide();
			$('#fixed_quantity').show();
		} else {
			$('.wps_variable_quantity').show();
			$('#fixed_quantity').hide();
		}
	  });
	$(document).find('#wps_upsell_offer_quantity_type_id').trigger("change");

	const triggerError = () => {
		swal({
			title: "Attention Required!",
			text: "Similiar Priority Encountered for multiple order bumps!",
			icon: "error",
			button: "Let me handle it",
		});
	}

	if( jQuery( '.wps_upsell_bumps_list' ).length > 0 ){
		let priArr = [];
		jQuery( '.wps-bump-priority' ).each(function() {
			if ( ! isNaN( jQuery(this).text() ) ) {
				if ( '-1' != jQuery.inArray( jQuery(this).text(), priArr ) ) {
					triggerError();
				} else {
					priArr.push( jQuery(this).text() );
				}
			}
		});
	}

	// end of scripts.
});