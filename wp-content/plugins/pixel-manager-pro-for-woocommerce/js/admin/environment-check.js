jQuery(function () {

	// disable incompatible plugin warning
	jQuery(document).on('click', '.incompatible-plugin-error-dismissal-button', function (e) {
		e.preventDefault();

		let data = {
			'action'         : 'environment_check_handler',
			'disable_warning': jQuery(this).data('plugin-slug'),
		};

		wpm_send_ajax_data(data);
	});


	// disable WP Rocket JavaScript concatenation
	jQuery(document).on('click', '#wpm-wp-rocket-js-concatenation-disable', function (e) {
		e.preventDefault();

		let data = {
			'action': 'environment_check_handler',
			'set'   : 'disable_wp_rocket_javascript_concatenation'
		};

		wpm_send_ajax_data(data);
	});

	// dismiss WP Rocket JavaScript concatenation error
	jQuery(document).on('click', '#wpm-dismiss-wp-rocket-js-concatenation-error', function (e) {
		e.preventDefault();

		let data = {
			'action': 'environment_check_handler',
			'set'   : 'dismiss_wp_rocket_javascript_concatenation_error'
		};

		wpm_send_ajax_data(data);
	});

	// disable WP Rocket JavaScript concatenation
	jQuery(document).on('click', '#wpm-litespeed-inline-js-dom-ready-disable', function (e) {
		e.preventDefault();

		let data = {
			'action': 'environment_check_handler',
			'set'   : 'disable_litespeed_inline_js_dom_ready'
		};

		wpm_send_ajax_data(data);
	});

	// dismiss WP Rocket JavaScript concatenation error
	jQuery(document).on('click', '#wpm-dismiss-litespeed-inline-js-dom-ready-error', function (e) {
		e.preventDefault();

		let data = {
			'action': 'environment_check_handler',
			'set'   : 'dismiss_litespeed_inline_js_dom_ready'
		};

		wpm_send_ajax_data(data);
	});

	// dismiss PayPal standard payment gateway warning
	jQuery(document).on('click', '#wpm-paypal-standard-error-dismissal-button', function (e) {
		e.preventDefault();

		let data = {
			'action': 'environment_check_handler',
			'set'   : 'dismiss_paypal_standard_warning'
		};

		wpm_send_ajax_data(data);
	});

});

function wpm_send_ajax_data(data) {
	jQuery.post(ajaxurl, data, function (response) {
		// console.log('Got this from the server: ' + response);
		// console.log('update rating done');
		location.reload();
	});
}
