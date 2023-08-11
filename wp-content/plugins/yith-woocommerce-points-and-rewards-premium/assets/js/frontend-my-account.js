jQuery(function ($) {
	"use strict";

	var $body = $('body'),
		blockParams = {
			message: null,
			overlayCSS: {background: '#fff', opacity: 0.7},
			ignoreIfBlocked: true
		};

	/* datepicker for birthday extra point */
	function start_date_picker() {
		let dformat = yith_ywpar_general.birthday_date_format.replace('yy', 'yyyy');
		var instance = new dtsel.DTS('input[name="yith_birthday"]', {
			dateFormat: dformat,
		});
	}

	if ($('input[name="yith_birthday"]').length > 0) {
		start_date_picker();
	}

	/* ywpar tabs */
	function openTab(evt, obj, tab) {
		obj.find('.ywpar_tabcontent').each(function () {
			$(this).hide();
			$(this).removeClass('active');

		});
		obj.find('.ywpar_tabs_links').each(function () {
			$(this).removeClass('active');
		});
		obj.find('#' + tab).addClass('active').fadeIn('slow');
		evt.currentTarget.className += " active";
	}

	$('.ywpar_tabs_links').on('click', function (e) {
		var selected = $(this).closest('#ywpar_tabs');
		openTab(e, selected, $(this).data('target'));
	});

	$('.ywpar_tabs_links:first-child').each(function () {
		$(this).click();
	});


	/* copy to clipboard button */
	var clearSelection = function () {
		var selection = 'getSelection' in window ? window.getSelection() : false;
		if (selection) {
			if ('empty' in selection) {  // Chrome.
				selection.empty();
			} else if ('removeAllRanges' in selection) {  // Firefox.
				selection.removeAllRanges();
			}
		} else if ('selection' in document) {  // IE.
			document.selection.empty();
		}
	}

	$(document).on('click', '.ywpar-copy-to-clipboard__copy', function () {
		var wrap = $(this).closest('#ywpar-copy-to-clipboard-wrapper'),
			input = wrap.find('input.ywpar-copy-to-clipboard__field'),
			tip = wrap.find('.ywpar-copy-to-clipboard__tip'),
			timeout = wrap.data('tip-timeout');

		timeout && clearTimeout(timeout);

		input.select();
		document.execCommand('copy');
		clearSelection();

		tip.fadeIn(400);

		// Use timeout instead of delay to prevent issues with multiple clicks.
		timeout = setTimeout(function () {
			tip.fadeOut(400);
		}, 1500);
		wrap.data('tip-timeout', timeout);
	});

	var checkPointToShare = function () {
		var $t = $(document).find('#ywpar_share_points_to_share'),
			currentValue = parseInt($t.val()),
			min = parseInt($(document).find('#ywpar-share-points__min').val()),
			max = parseInt($(document).find('#ywpar-share-points__max').val());

		$(document).find('#share_points .error').hide();
		$t.removeClass('input-error');
		if (currentValue > max) {
			$(document).find('#share_points .error.max-exceed').show();
			$t.addClass('input-error');
			return false;
		} else if (min > 0 && currentValue < min) {
			$(document).find('#share_points .error.min-exceed').show();
			$t.addClass('input-error');
			return false;
		} else {
			var data = {
				points: currentValue,
				action: 'ywpar_calculate_worth_from_points_on_share_points',
				customer: $(document).find('#ywpar-share-points__customer').val(),
				security: $(document).find('#_wpnonce').val()
			}

			$.post(yith_ywpar_general.ajax_url, data, function (response) {
				if (response.success) {
					$(document).find('.worth-price').html(response.data.worth);
				} else {
					window.alert(response.data.error);
				}
			});

			return true;
		}
	}
	$(document).on('keyup blur', '#ywpar_share_points_to_share', function (e) {
		checkPointToShare();
	});

	var bgopacity = 1.0,
		bgfade = function () {
			var newCoupon = $(document).find('#share_points .ywpar_share_points_table.my_account_orders tr:first-child td');
			bgopacity -= 0.02;
			newCoupon.css({backgroundColor: "rgba(255, 255, 255, " + bgopacity + ")"});
			if (bgopacity >= 0) {
				setTimeout(bgfade, 50);
			}
		},
		reloadSharePointView = function () {
			$.post(document.location.href, function (data) {
				if (data != '') {
					bgopacity = 1.0;
					var c = $("<div></div>").html(data),
						wrap = c.find('#share_points'),
						currentPoints = c.find('.ywpar_myaccount_entry_info');
					$(document).find('#share_points').html(wrap.html());
					$(document).find('.ywpar_myaccount_entry_info').html(currentPoints.html());
					$(document).find('#share_points .ywpar_share_points_table.my_account_orders tr:first-child').addClass('highlight')
					bgfade();
				}
			});
		}

	$(document).on('click', '#ywpar-share-points__submit', function (e) {
		e.preventDefault();
		if (checkPointToShare()) {
			var data = {
					points: $(document).find('#ywpar_share_points_to_share').val(),
					action: 'ywpar_create_share_points_coupon',
					customer: $(document).find('#ywpar-share-points__customer').val(),
					security: $(document).find('#_wpnonce').val()
				},
				container = $(document).find('#ywpar-share-points');

			container.block(blockParams);

			$.post(yith_ywpar_general.ajax_url, data, function (response) {
				if (response.success) {
					container.unblock(blockParams);
					reloadSharePointView();
				} else {
					window.alert(response.data.error);
				}
			});
		}

	});
});
