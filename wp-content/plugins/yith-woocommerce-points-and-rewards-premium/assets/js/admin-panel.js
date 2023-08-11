/* global yith_ywpar_customer_panel */
jQuery(function ($) {

	if ($('.ywpar_back_button').length > 0) {
		var backButton = $('.ywpar_back_button');
		var wrap = backButton.closest('.wrap');
		wrap.prepend(backButton);
		backButton.addClass('show');
	}

	$('#ywpar_min_percentual_discount, #ywpar_max_percentual_discount,#ywpar_percentage_points_to_earn').after('<span class="ywpar_type_sign">%</span>');
	$('#ywpar_fixed_points_to_earn').after('<span class="ywpar_type_sign">'+$('#ywpar_fixed_points_to_earn').data('sign')+'</span>');

	$(document).on('change', 'input[name="is_rule_scheduled"]', function () {
		var $t = $(this),
			radioValue = $t.val();
		if ('scheduled' === radioValue) {
			$('.ywpar_is_rule_scheduled .description').hide();
		} else {
			$('.ywpar_is_rule_scheduled .description').show();
		}
	});

	$(document).on('click', '.ywpar-rule-status input', function () {
		var $t = $(this),
			$wrapper = $t.closest('.yith-plugin-ui'),
			post_id = $wrapper.data('id'),
			nonce = $wrapper.data('nonce'),
			status = $t.is(':checked') ? 'yes' : 'no',
			data = {
				post_id: post_id,
				security: nonce,
				status: status,
				action: 'ywpar_change_status_to_earning_rule',
			}

		$.ajax({
			type: 'POST',
			data: data,
			success: function (response) {
				if (response.error) {
					console.log(response.error);
				}
			},
			url: yith_ywpar_panel.ajax_url
		});
	});

	$(document).on('click', '.ywpar-rule-redeeming-status input', function () {
		var $t = $(this),
			$wrapper = $t.closest('.yith-plugin-ui'),
			post_id = $wrapper.data('id'),
			nonce = $wrapper.data('nonce'),
			status = $t.is(':checked') ? 'yes' : 'no',
			data = {
				post_id: post_id,
				security: nonce,
				status: status,
				action: 'ywpar_change_status_to_redeeming_rule',
			}

		$.ajax({
			type: 'POST',
			data: data,
			success: function (response) {
				if (response.error) {
					console.log(response.error);
				}
			},
			url: yith_ywpar_panel.ajax_url
		});
	});

	$(document).on('click', '.ywpar-lb-status input', function () {
		var $t = $(this),
			$wrapper = $t.closest('.yith-plugin-ui'),
			post_id = $wrapper.data('id'),
			nonce = $wrapper.data('nonce'),
			status = $t.is(':checked') ? 'yes' : 'no',
			data = {
				post_id: post_id,
				security: nonce,
				status: status,
				action: 'ywpar_change_status_to_level',
			}

		$.ajax({
			type: 'POST',
			data: data,
			success: function (response) {
				if (response.error) {
					console.log(response.error);
				}
			},
			url: yith_ywpar_panel.ajax_url
		});
	});

	$(document).on('click', '.ywpar-banner-status input', function () {
		var $t = $(this),
			$wrapper = $t.closest('.yith-plugin-ui'),
			post_id = $wrapper.data('id'),
			nonce = $wrapper.data('nonce'),
			status = $t.is(':checked') ? 'yes' : 'no',
			data = {
				post_id: post_id,
				security: nonce,
				status: status,
				action: 'ywpar_change_status_to_banner',
			}

		$.ajax({
			type: 'POST',
			data: data,
			success: function (response) {
				if (response.error) {
					console.log(response.error);
				}
			},
			url: yith_ywpar_panel.ajax_url
		});
	});

	$('.form-table').find('[data-desc]').each(function () {
		var t = $(this),
			desc = t.attr('data-desc').split(',');
        if( t.attr('type') === 'number'){
			t.after('<span class="ywpar_type_sign">' + desc[0] + '</span>');
		}else{
			t.find('input').after('<span class="ywpar_type_sign">' + desc + '</span>');
		}

	});


	$('.form-table').find('[data-deps]').each(function () {
		var t = $(this),
			input = t.find('input'),
			wrap = t.closest('tr'),
			deps = t.attr('data-deps').split(','),
			values = t.attr('data-deps_value').split(','),
			conditions = [];

		if ($('[name="user_type"]').attr('type') !== 'radio') {
			if ($(this).hasClass('ywpar_user_plans_list') || $(this).hasClass('ywpar_user_levels_list')) {
				return;
			}
		}

		$.each(deps, function (i, dep) {

			if ($('[name="user_type"]').attr('type') !== 'radio' && 'ywpar_user_type' === dep) {
				return;
			}

			$('[id="' + dep + '"]').on('change', function () {

				var value = this.value,
					check_values = '';

				// exclude radio if not checked
				if (this.type == 'radio' && !$(this).is(':checked')) {
					return;
				}

				if (this.type == 'checkbox') {
					value = $(this).is(':checked') ? 'yes' : 'no';

				}

				check_values = values[i] + ''; // force to string
				check_values = check_values.split('|');
				conditions[i] = $.inArray(value, check_values) !== -1;

				if ($.inArray(false, conditions) === -1) {
					wrap.show();
				} else {
					wrap.hide();
				}

			}).change();
		});
	});

	if ($('[name="user_type"]').attr('type') !== 'radio') {
		$(document).on('change', '#ywpar_user_type-levels', function () {
			var $t = $(this);
			if ($t.is(':checked')) {
				$('.form-table').find('.ywpar_user_levels_list').show();

			} else {
				$('.form-table').find('.ywpar_user_levels_list').hide();

			}
		});

		$(document).on('change', '#ywpar_user_type-membership', function () {
			var $t = $(this);
			if ($t.is(':checked')) {
				$('.form-table').find('.ywpar_user_plans_list').show();

			} else {
				$('.form-table').find('.ywpar_user_plans_list').hide();

			}

		});

		$('#ywpar_user_type-levels').change();
		$('#ywpar_user_type-membership').change();
	}




	$(document).on('click', '.options-role-conversion .ywpar-add-row, .options-role-percentage-conversion .ywpar-add-row', function () {
		var $t = $(this),
			wrapper = $t.closest('.yith-plugin-fw-field-wrapper'),
			current_option = $t.parent().find('.role-conversion-options[data-index="1"]'),
			current_index = parseInt(current_option.data('index')),
			clone = current_option.clone(),
			options = wrapper.find('.role-conversion-options'),
			add_same_rule_button_clone = current_option.parent().find('.ywpar-add-same-row').clone(),
			max_index = 1;

		options.each(function () {
			var index = $(this).data('index');
			if (index > max_index) {
				max_index = index;
			}
		});

		var new_index = max_index + 1;
		clone.attr('data-index', new_index);

		var fields = clone.find("[name*='role_conversion']");
		fields.each(function () {
			var $t = $(this),
				name = $t.attr('name'),
				id = $t.attr('id'),

				new_name = name.replace('[role_conversion][' + current_index + ']', '[role_conversion][' + new_index + ']'),
				new_id = id.replace('[role_conversion][' + current_index + ']', '[role_conversion][' + new_index + ']');

			$t.attr('name', new_name);
			$t.attr('id', new_id);
			$t.val('');

		});


		clone.find('.ywpar-remove-row').removeClass('hide-remove');
		clone.find('.chosen-container').remove();

		var roles = [];
		if ($t.parent().find('.role-conversion-options').hasClass('ywpar_redeem_roles')) {
			roles = get_used_roles('.ywpar_redeem_roles select');
		} else {
			roles = get_used_roles('.ywpar_earn_roles select');
		}

		roles.forEach(
			function (element) {
				clone.find('select.ywpar_role option[value="' + element + '"]').remove();
			}
		);

		$t.before('<div class="role-conversion-options-container">' + clone[0].outerHTML + '<div class="clear"></div></div>');

	});

	function get_used_roles(el) {
		var roles = [];

		$(el).each(function () {
			var v = $(this).children("option:selected").val();
			if (typeof v !== 'undefined' && typeof roles[v] !== v) {
				roles.push($(this).children("option:selected").val());
			}
		});


		return roles;
	}


	/* extra options tab */
	$('#yith_woocommerce_points_and_rewards_points-extra form h2:not(:first-child)').on('click', function () {
		if ($(this).hasClass('opened')) {
			$(this).removeClass('opened');
			$(this).next().removeClass('opened');
		} else {
			$(this).addClass('opened');
			$(this).next().addClass('opened');
		}

	});

	/****
	 * add a row in custom type field
	 ****/
	$(document).on('click', '#yith_woocommerce_points_and_rewards_points-extra .ywpar-add-row', function () {
		var $t = $(this),
			wrapper = $t.closest('.yith-plugin-fw-field-wrapper'),
			current_option = $t.prev('.extrapoint-options'),
			current_index = parseInt(current_option.data('index')),
			clone = current_option.clone(),
			options = wrapper.find('.extrapoint-options'),
			max_index = 1;
		    current_option.find('.repeat').addClass('hide');
		    current_option.find('.repeat input').prop('checked', false );

		options.each(function () {
			var index = $(this).data('index');
			if (index > max_index) {
				max_index = index;
			}
		});

		var new_index = max_index + 1;
		clone.attr('data-index', new_index);
		var fields = clone.find("[name*='list']");
		fields.each(function () {
			var $t = $(this),
				name = $t.attr('name'),
				id = $t.attr('id'),

				new_name = name.replace('[list][' + current_index + ']', '[list][' + new_index + ']'),
				new_id = id.replace('[list][' + current_index + ']', '[list][' + new_index + ']');

			$t.attr('name', new_name);
			$t.attr('id', new_id);
			$t.val('');

		});

		clone.find('.ywpar-remove-row').removeClass('hide-remove');
		clone.find('.chosen-container').remove();
		$(document).trigger('row-cloned', {clone: clone});
		$t.before(clone);

	});


	var $wrapperPlan = $('.yith-plugin-fw-options-extrapoints-membership-plans-field-wrapper');
	if ($wrapperPlan.length > 0) {
		var firstSelect = $wrapperPlan.find('select').first();
		var cloneOptions = firstSelect.find('option');

		$(document).on('row-cloned', function (e, data) {
			var $clone = data.clone;
			var parent = $($clone).closest('#ywpar_points_on_membership_plan-container');

			if (parent.length == 0) {
				return;
			}

			$($clone).find('select').find('option').remove();
			$.map(cloneOptions, function (option) {
				$($clone).find('select').append(new Option(option.text, option.value));
			});

			var $wrapper = $('.yith-plugin-fw-options-extrapoints-membership-plans-field-wrapper'),
				selects = $wrapper.find('select');

			selects.each(function (index, obj) {
				if ($(obj).val() != null) {
					$($clone).find('select option[value="' + $(obj).val() + '"]').remove();
				}
			});
		});
	}


	/****
	 * remove a row in custom type field
	 ****/
	$(document).on('click', '#yith_woocommerce_points_and_rewards_points .ywpar-remove-row', function () {
		var $t = $(this),
			current_row = $t.closest('.role-conversion-options');

		current_row.remove();
	});

	$(document).on('click', '.extrapoint-options .ywpar-remove-row', function () {
		var $t = $(this),
			current_row = $t.closest('.extrapoint-options');
		previous = current_row.prev();
		previous.find('.repeat').removeClass('hide');
		current_row.remove();
	});

	$(document).on('click', '.options-role-conversion .ywpar-remove-row, .options-role-percentage-conversion .ywpar-remove-row', function () {
		var $t = $(this),
			current_row = $t.closest('.role-conversion-options'),
			container = current_row.parent();

		current_row.remove();

		if (container.find('.role-conversion-options').length === 0) {
			container.remove();
		}

	});

	/* open extra options tabs for the ones that are active */
	$('#yith_woocommerce_points_and_rewards_points-extra .form-table tr.onoff:first-child input.on_off').each(function () {
		if ('yes' === $(this).val()) {
			$(this).closest('.form-table').prev().trigger('click');
		}
	});

	/* banner pre-complied-values for target type */
	$(document).on('change', '#ywpar_banner_type', function () {
		$('.ywpar_banner_title .forminp .description.precompiled').hide();
		$('.ywpar_banner_subtitle .forminp .description.precompiled').hide();
		if ('target' === $(this).val()) {
			$('.description[data-ref=' + $('#ywpar_banner_action_target_type').val() + ']').show();
		} else if ('get_points' === $(this).val()) {
			$('.description[data-ref=' + $('#ywpar_banner_action_type').val() + ']').show();
		}
	});
	$(document).on('change', '#ywpar_banner_action_target_type, #ywpar_banner_action_type', function () {
		$('.ywpar_banner_title .forminp .description.precompiled').hide();
		$('.ywpar_banner_subtitle .forminp .description.precompiled').hide();
		$('.description[data-ref=' + $(this).val() + ']').show();
	});


	$('#ywpar_banner_type').change();

	var initSortTable = function () {
			var table_to_sort = ['.post-type-ywpar-banner', '.post-type-ywpar-earning-rule', '.post-type-ywpar-redeeming-rule'];
			$.each(table_to_sort, function (i, currentPostPage) {

				$(currentPostPage + " .wp-list-table  tbody tr").addClass('ui-state-default');
				$(currentPostPage + " .wp-list-table  tbody").sortable({
					placeholder: "ui-state-highlight",
					axis: 'y',
					handle: ".yith-plugin-fw__action-button--drag-action",
					helper: fixWidthSortHelper,
					stop: function () {
						$sorted = $(currentPostPage + " .wp-list-table").find('[name="post[]"]');
						var sortedList = [];
						$sorted.each(function (i, input) {
							sortedList.push($(input).val());
						})
						sortTable(sortedList);
					}
				}).disableSelection();
			});
		},
		fixWidthSortHelper = function (e, ui) {
			ui.children().each(function () {
				$(this).width($(this).width());
			});
			return ui;
		},
		//Sort the table
		sortTable = function (sortedList) {
			$.post(yith_ywpar_panel.ajax_url, {action: 'ywpar_order_list_table', request: "sort", sorted: sortedList})
				.done(function (data) {
				});
		};

	initSortTable();

	/**
	 * Import Export Tab Javascript
	 */
	if ($('#ywpar_import_points').length) {
		$('#ywpar_import_points').closest('form').attr('enctype', "multipart/form-data");
	}

	$('#file_import_csv_btn').on('click', function (e) {
		e.preventDefault();
		$('#file_import_csv').click();
	});

	$('#file_import_csv').on('change', function () {
		var fname = document.getElementById("file_import_csv").files[0].name;

		if (fname !== '') {
			$('span.ywpar_file_name').html(fname);
		}
	});

	$('button#ywpar_import_points').on('click', function (e) {
		e.preventDefault();
		var action = $('#type_action').val();
		$('.ywpar_safe_submit_field').val(action + '_points');
		$(this).closest('form').submit();
	});

	if ($('.ywpar_import_result').length) {
		setTimeout(function () {
			$('.ywpar_import_result').remove();
		}, 3000);
	}

	$('#type_action').on('change', function () {
		var $t = $(this),
			action = $t.val();

		if (action === 'import') {
			$('#yith_woocommerce_points_and_rewards_import_export .upload.file_import_csv').show();
			$('#yith_woocommerce_points_and_rewards_import_export .radio.csv_import_action').show();
			$('#ywpar_import_points').text(yith_ywpar_customer_panel.import_button_label);
		} else {
			$('#yith_woocommerce_points_and_rewards_import_export .upload.file_import_csv').hide();
			$('#yith_woocommerce_points_and_rewards_import_export .radio.csv_import_action').hide();
			$('#ywpar_import_points').text(yith_ywpar_customer_panel.export_button_label);
		}
	});
	$('#type_action').change();

	$('#ywpar_enable_rewards_points').on('change', function(){
		var $t = $(this),
		    $headers = $('#plugin-fw-wc').find('h2').last();

		if( $t.is(':checked')){
			$headers.show();
		}else{
			$headers.hide();
		}
	}).change();

	$('#ywpar_apply_points_from_wc_points_rewards_btn').on('click', function(e) {
		e.preventDefault();
		var container   = $('#ywpar_apply_points_from_wc_points_rewards_btn').closest('.forminp-yith-field');

		container.find('.response').remove();

		$.ajax({
			type    : 'POST',
			url     : yith_ywpar_panel.ajax_url,
			dataType: 'json',
			data    : 'action=ywpar_apply_wc_points_rewards&security=' + yith_ywpar_panel.apply_wc_points_rewards,
			success : function (response) {
				container.append('<span class="response">'+response+'</span>');
			}
		});
	});

	$('#post-query-submit,#posts-filter,#doaction, #ywpar_import_points, #ywpar_import_points, .yith-nav-tab-wrapper a, .yith-nav-sub-tab-wrapper a').on('click', function (e) {
		window.onbeforeunload = null;
	});
});