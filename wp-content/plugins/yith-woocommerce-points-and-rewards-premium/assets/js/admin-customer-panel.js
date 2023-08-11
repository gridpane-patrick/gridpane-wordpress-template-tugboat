/* global yith_ywpar_customer_panel */
jQuery(function ($) {
	var editPointsDialog = $(document).find('.ywpar-points-collected__popup_wrapper'),
		modal = false,

		openPopupEditPoint = function (title, button) {

			// init dialog
			modal = yith.ui.modal({
				title: title,
				content: editPointsDialog,
				classes: {
					wrap: 'ywpar-points-update-wrapper'
				},
				footer: '<a href="#" class="yith-plugin-fw__button--primary form-submit float-right">' + button + '</a>'
			});


		};

	$(document).on('click', '.ywpar-customer-list #doaction', function (e) {
		e.preventDefault();
		e.stopPropagation();

		var selectedElement = $('#bulk-action-selector-top').val();

		if ('reset' === selectedElement) {
			yith.ui.confirm(
				{
					title: yith_ywpar_customer_panel.reset_points_title,
					message: yith_ywpar_customer_panel.reset_points_message_bulk,
					confirmButtonType: 'delete',
					confirmButton: yith_ywpar_customer_panel.remove_points_save,
					closeAfterConfirm: true,
					onConfirm: function () {
						window.onbeforeunload = null;
						$('#posts-filter').submit();
					},
				}
			);
		}else{
			$('#posts-filter').submit();
		}
	});

	$(document).on('click', '.yith-plugin-fw__action-button--add-action', function (e) {
		e.preventDefault();
		e.stopPropagation();
		editPointsDialog.find('form').find('input[name="action_type"]').val('add');
		openPopupEditPoint(yith_ywpar_customer_panel.add_points_title, yith_ywpar_customer_panel.add_points_save);
	});

	$(document).on('click', '.yith-plugin-fw__action-button--remove-action', function (e) {
		e.preventDefault();
		e.stopPropagation();
		editPointsDialog.find('form').find('input[name="action_type"]').val('remove');
		openPopupEditPoint(yith_ywpar_customer_panel.remove_points_title, yith_ywpar_customer_panel.remove_points_save);
	});

	$(document).on('click', '.yith-plugin-fw__action-button--reset-action a', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var url = $(this).attr('href');

		yith.ui.confirm(
			{
				title: yith_ywpar_customer_panel.reset_points_title,
				message: yith_ywpar_customer_panel.reset_points_message,
				confirmButtonType: 'delete',
				confirmButton: yith_ywpar_customer_panel.remove_points_save,
				closeAfterConfirm: true,
				onConfirm: function () {
					window.location.href = url;
				},
			}
		);
	});

	$(document).on('click', '.form-submit', function (e) {
		e.preventDefault();
		var $t = $(this),
			currentForm = $t.closest('.yith-plugin-fw__modal__main').find('form'),
			action = currentForm.find('[name="action_type"]').val();

		if( 'remove' === action ){
			var max_remove = parseFloat(currentForm.find('[name="max_points_to_remove"]').val()),
			    pointvalue = parseFloat(currentForm.find('#points_amount').val());
			
			if( max_remove !== ''  && max_remove < pointvalue ){
				currentForm.find('#points_amount').val(max_remove);
			}
		}

		$t.block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6,
			}
		});

		$.ajax({
			url: yith_ywpar_customer_panel.ajax_url + '?action=ywpar_update_points&context=admin_action',
			data: editPointsDialog.find('form').serialize(),
			type: 'POST',
			beforeSend: function () {
				$t.block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6,
					}
				});
			},
			success: function (response) {
				if (response.success) {
					$t.unblock();
					modal.close();
					window.onbeforeunload = null;
					window.location.reload();
				} else {
					console.log(response.error);
				}
			}
		});

	})


	var bulkform = $('#yith_woocommerce_points_and_rewards_bulk_form');
	/* bulk action ajax */
	$('#ywpar_bulk_action_points').on('click', function (e) {
		e.preventDefault();
		window.onbeforeunload = '';

		var form = $(this).closest('form'),
			form_values = form.serializeArray(),
			container = $('#yith_woocommerce_points_and_rewards_bulk-container');


		if ("from" === form_values.ywpar_apply_points_previous_order_to && "" === form_values.ywpar_apply_points_previous_order) {
			$('#ywpar_apply_points_previous_order').addClass('ywpar-error');
			return;
		}

		container.block({
			message: null,
			overlayCSS: {
				background: 'transparent',
				opacity: 0.5,
				cursor: 'none'
			}
		});

		$('.ywpar-bulk-trigger').append('<div class="ywpar-bulk-progress"><div>0%</div></div>');

		process_step(1, form_values, form);

	});

	var process_step = function (step, data, form) {

		var block_container = $('.ywpar-bulk-trigger'),
			container = $('#yith_woocommerce_points_and_rewards_bulk-container');
			data.push( { name: 'action', value: 'ywpar_bulk_action' }, { name: 'step', value: step } );
		$.ajax({
			type: 'POST',
			url: yith_ywpar_customer_panel.ajax_url,
			data: data,
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					if ('done' === response.data.step) {
						block_container.find('.ywpar-bulk-progress').hide('slow').remove();
						$(document).find('.ywpar-bulk-trigger').append('<span class="ywpar-bulk-response"><i class="yith-icon yith-icon-check"></i>' + response.data.message + '</span>');
						container.unblock();


						setTimeout(function () {
							$('span.ywpar-bulk-response').remove();
							window.location.reload();
						}, 2000);
					} else {
						block_container.find('.ywpar-bulk-progress div').html(response.data.percentage + '%');
						block_container.find('.ywpar-bulk-progress div').animate({
							width: response.data.percentage + '%',
						}, 50, function () {
							// Animation complete.
						});
						process_step(parseInt(response.data.step), data, form);
					}
				} else {
					$(document).find('.ywpar-bulk-trigger').append('<span class="ywpar-bulk-response"><i class="yith-icon yith-icon-check"></i>' + response.data.error + '</span>');
					setTimeout(function () {
						$('span.ywpar-bulk-response').remove();
						window.location.reload();
					}, 2000);
				}
			}
		});

	};

	$.fn.serializefiles = function () {
		var obj = $(this);
		/* ADD FILE TO PARAM AJAX */
		var formData = new FormData();
		var params = $(obj).serializeArray();

		$.each(params, function (i, val) {
			formData.append(val.name, val.value);
		});

		return formData;
	};

	$.fn.serializeObject = function () {
		var o = {};
		var a = this.serializeArray();
		$.each(a, function () {
			if (o[this.name]) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};



	var checkRankingOption = function(){

		if( $(document).find('#ywpar_enable_ranking').is(':checked')){
			$('tr.yith-plugin-fw-panel-wc-row.html').show();
		}else{
			$('tr.yith-plugin-fw-panel-wc-row.html').hide();
		}
	};
	checkRankingOption();
	$(document).on( 'click', '#ywpar_enable_ranking', checkRankingOption);

});