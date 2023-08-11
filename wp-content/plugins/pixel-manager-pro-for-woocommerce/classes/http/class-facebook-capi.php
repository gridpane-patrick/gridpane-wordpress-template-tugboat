<?php

namespace WCPM\Classes\Http;

use WC_Geolocation;
use WCPM\Classes\Pixels\Trait_Product;
use WCPM\Classes\Pixels\Trait_Shop;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Facebook_CAPI extends Http {

	use Trait_Product;
	use Trait_Shop;

	protected $fbp_key;
	protected $fbc_key;
	protected $facebook_key;
	protected $capi_purchase_hit_key;
	protected $test_event_code;
	protected $pixel_name;
	protected $request_url;
	protected $opt_out;
	protected $purchase_logging;

	public function __construct( $options ) {

		parent::__construct($options);

		$pixel_id     = $this->options_obj->facebook->pixel_id;
		$access_token = $this->options_obj->facebook->capi->token;

		$this->fbp_key      = 'facebook_fbp_' . $pixel_id;
		$this->fbc_key      = 'facebook_fbc_' . $pixel_id;
		$this->facebook_key = 'facebook_user_identifiers_' . $pixel_id;

		$this->test_event_code = apply_filters_deprecated('wooptpm_facebook_capi_test_event_code', [false], '1.13.0', 'wpm_facebook_capi_test_event_code');
		// Filter to inject the Facebook CAPI test event code
		$this->test_event_code = apply_filters('wpm_facebook_capi_test_event_code', $this->test_event_code);

		$this->capi_purchase_hit_key = 'wpm_facebook_capi_purchase_hit';
		$this->pixel_name            = 'facebook';

		$server_url = 'graph.facebook.com';

		$api_version = 'v14.0';
		$api_version = apply_filters_deprecated('wooptpm_facebook_capi_api_version', [$api_version], '1.13.0', 'wpm_facebook_capi_api_version');
		// Filter to change the Facebook API version
		$api_version = apply_filters('wpm_facebook_capi_api_version', $api_version);

		$endpoint          = 'events';
		$this->request_url = 'https://' . $server_url . '/' . $api_version . '/' . $pixel_id . '/' . $endpoint . '?access_token=' . $access_token;

		$this->opt_out = apply_filters_deprecated('wooptpm_facebook_capi_ads_delivery_opt_out', [false], '1.13.0', 'wpm_facebook_capi_ads_delivery_opt_out');
		$this->opt_out = apply_filters('wpm_facebook_capi_ads_delivery_opt_out', $this->opt_out);

		$this->post_request_args['blocking'] = apply_filters_deprecated('wooptpm_send_http_api_facebook_capi_requests_blocking', [$this->post_request_args['blocking']], '1.13.0', 'wpm_send_http_api_facebook_capi_requests_blocking');
		// Send the Facebook CAPI request blocking, so that we can analyse it
		$this->post_request_args['blocking'] = apply_filters('wpm_send_http_api_facebook_capi_requests_blocking', $this->post_request_args['blocking']);

		$this->post_request_args['headers'] = [
			'Content-Type' => 'application/json; charset=utf-8',
		];

		$this->purchase_logging = apply_filters_deprecated('wooptpm_facebook_capi_purchase_logging', [false], '1.13.0', 'wpm_facebook_capi_purchase_logging');
		// Enable Facebook CAPI purchase logging
		$this->purchase_logging = apply_filters('wpm_facebook_capi_purchase_logging', $this->purchase_logging);

		$this->logger_context = ['source' => 'wpm-facebook-capi'];

		add_action('wp_ajax_wpm_facebook_capi_event', [$this, 'wpm_facebook_capi_event']);
		add_action('wp_ajax_nopriv_wpm_facebook_capi_event', [$this, 'wpm_facebook_capi_event']);
	}

	/**
	 * We pass the $order, $fbp and $fbc$fbp and $fbc are only necessary if it is a subscription renewal order
	 * https://developers.facebook.com/docs/marketing-api/conversions-api/using-the-api#send
	 * https://developers.facebook.com/docs/marketing-api/conversions-api/parameters
	 * https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event#event-name
	 **/
	public function send_purchase_hit( $order, $fbp = null, $fbc = null ) {

		if ($this->do_not_track_user($this->wpm_get_order_user_id($order->get_id()))) {
			return;
		}

//        error_log('processing Facebook CAPI purchase hit 1');
//        error_log('key exists: ' . get_post_meta($order->get_id(), $this->capi_purchase_hit_key, true));

		// only run, if the hit has not been sent already (check in db)
		if (get_post_meta($order->get_id(), $this->capi_purchase_hit_key)) {
//            error_log('Facebook CAPI purchase hit already processed');
			return;
		}

		/**
		 * Privacy filter
		 * if user didn't provide fbp he probably doesn't want to be tracked -> stop processing
		 * if fbp is available, continue with minimally required identifiers
		 * the shop owner can choose to add all available identifiers
		 * give the shop owner the choice to filter the user_data, based on IP
		 **/

		$facebook_identifiers = $this->get_identifiers_from_order($order);

		// If fbp is missing and the store owner didn't instruct to process anonymous sessions, we stop.
		if (!isset($facebook_identifiers['fbp']) && !$this->process_anonymous_hits()) {
//            error_log('fbp missing. Store owner doesn\'t want anonymous hits to be processed. Purchase hit prevented.');
			return;
		}

		// Add event data
		$capi_event_data = [
			'event_name'       => 'Purchase',
			'event_time'       => $facebook_identifiers['event_time'], // try to match browser event_time
			'event_id'         => (string) $order->get_id(),
			'opt_out'          => (bool) $this->opt_out,
			'action_source'    => 'website',
			'event_source_url' => $order->get_checkout_order_received_url(),
		];

		// Add user data
		$capi_event_data['user_data'] = $this->get_user_data($facebook_identifiers, $order);

//		wc_get_logger()->debug(print_r($capi_event_data['user_data'], true), ['source' => 'wpm-facebook-capi-purchase-user-data']);

//		error_log(print_r($capi_event_data['user_data'], true));

		// add order data
		$capi_event_data['custom_data'] = [
			'value'        => (float) $this->wpm_get_order_total($order),
			'currency'     => (string) $order->get_currency(),
			'content_ids'  => (array) $this->get_order_item_ids($order),
			'content_type' => 'product'
		];

		// data processing options
		$capi_event_data = $this->add_data_processing_options($capi_event_data);

		$payload = [
			'data' => [$capi_event_data],
		];

		if ($this->test_event_code) {
//            error_log('Facebook CAPI test event code enabled');
			$payload['test_event_code'] = $this->test_event_code;
		}

//		error_log('send FB CAPI purchase hit');
//		error_log(print_r($payload, true));

		if ($this->purchase_logging) {

			wc_get_logger()->info('Facebook CAPI hit on order ' . $order->get_id() . ': start', $this->logger_context);

			$this->post_request_args['blocking'] = true;
		}

		$this->send_payload_to_logger($payload);

		$this->send_hit($this->request_url, $payload);

		// Now we let the server know, that the hit has already been successfully sent.
		update_post_meta($order->get_id(), $this->capi_purchase_hit_key, true);

		if ($this->purchase_logging) {
			wc_get_logger()->info('Facebook CAPI hit on order ' . $order->get_id() . ': end', $this->logger_context);
		}
	}

	public function send_event_hit( $browser_event_data ) {
//        error_log('processing Facebook CAPI event hit');

		if ($this->do_not_track_user(get_current_user_id())) {
			return;
		}

		/**
		 * Privacy filter
		 * if user didn't provide fbp he probably doesn't want to be tracked -> stop processing
		 * if fbp is available, continue with minimally required identifiers
		 * the shop owner can choose to add all available identifiers
		 * give the shop owner the choice to filter the user_data, based on IP
		 **/

		// If fbp is missing and the store owner didn't instruct to process anonymous sessions, we stop.
		if (!isset($browser_event_data['user_data']['fbp']) && !$this->process_anonymous_hits()) {
//            error_log('fbp missing. Store owner doesn\'t want anonymous hits to be processed. Purchase hit prevented.');
			wc_get_logger()->info('fbp missing. Store owner doesn\'t want anonymous hits to be processed. Purchase hit prevented.', $this->logger_context);
			return;
		}

		$capi_event_data = $browser_event_data;

		$capi_event_data['action_source'] = 'website';
		$capi_event_data['event_time']    = time();
		$capi_event_data['opt_out']       = (bool) $this->opt_out;

		$capi_event_data['user_data']['client_ip_address'] = $this->get_user_ip();

		// data processing options
		$capi_event_data = $this->add_data_processing_options($capi_event_data);

		$payload = [
			'data' => [$capi_event_data],
		];

		if ($this->test_event_code) {
//			error_log('Facebook CAPI test event code enabled');
			$payload['test_event_code'] = $this->test_event_code;
		}

		$this->send_payload_to_logger($payload);

//		error_log('send FB CAPI event hit');
//		error_log(print_r($payload, true));

		$this->send_hit($this->request_url, $payload);
	}

	protected function send_payload_to_logger( $payload ) {

		// Output Facebook CAPI event debug info
		if (apply_filters('wpm_facebook_capi_event_logger', false)) {
			wc_get_logger()->debug(print_r($payload, true), ['source' => 'wpm-facebook-capi-events']);
		}
	}

	// https://developers.facebook.com/docs/marketing-apis/data-processing-options
	// https://developers.facebook.com/docs/marketing-apis/data-processing-options#conversions-api-and-offline-conversions-api
	protected function add_data_processing_options( $capi_event_data ) {
		$processing_options = apply_filters_deprecated('wooptpm_facebook_capi_data_processing_options', [[]], '1.13.0', 'wpm_facebook_capi_data_processing_options');

		return array_merge($capi_event_data, apply_filters('wpm_facebook_capi_data_processing_options', $processing_options));
	}

	protected function process_anonymous_hits() {
		return $this->options_obj->facebook->capi->user_transparency->process_anonymous_hits;
	}

	protected function send_additional_client_identifiers() {
		return $this->options_obj->facebook->capi->user_transparency->send_additional_client_identifiers;
	}

	protected function get_user_data( $facebook_identifiers, $order = null ) {

		$user_data = [];

//        error_log(print_r($facebook_identifiers, true));

		// If fbp exists we set all real data
		// If fbp doesn't exist, we only set required fields with random data
		if (isset($facebook_identifiers['fbp'])) {
			$user_data['fbp'] = $facebook_identifiers['fbp'];
		}

		// Set fbc
		if (isset($facebook_identifiers['fbc'])) {
			$user_data['fbc'] = $facebook_identifiers['fbc'];
		}

		if (isset($facebook_identifiers['client_user_agent'])) {
			$user_data['client_user_agent'] = $facebook_identifiers['client_user_agent'];
		} else {
			$user_data['client_user_agent'] = ( new User_Agent() )->get_random_user_agent();
		}

		// https://developers.facebook.com/docs/marketing-api/conversions-api/parameters
		// https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/customer-information-parameters
		// https://developers.facebook.com/docs/marketing-api/audiences/guides/custom-audiences/#example_sha256
		if ($this->send_additional_client_identifiers()) {

			// Set client_ip_address
			if (isset($facebook_identifiers['client_ip_address'])) {
				$user_data['client_ip_address'] = $facebook_identifiers['client_ip_address'];
			}

			if ($order) {

				// set user_id
				// must be sent by the browser simultaneously
				// https://developers.facebook.com/docs/meta-pixel/advanced/advanced-matching
				if ($this->wpm_get_order_user_id($order->get_id()) !== 0) {
					$user_data['external_id'] = hash('sha256', $this->wpm_get_order_user_id($order->get_id()));
				}

				// set em (email)
				$user_data['em'] = hash('sha256', trim(strtolower($order->get_billing_email())));

				if ($order->get_billing_phone()) {

					$phone = $order->get_billing_phone();
					$phone = $this->get_e164_formatted_phone_number($phone, $order->get_billing_country());
					$phone = str_replace('+', '', $phone);
					$phone = hash('sha256', $phone);

					$user_data['ph'] = $phone;
				}

				if ($order->get_billing_first_name()) {
					$user_data['fn'] = hash('sha256', trim(strtolower($order->get_billing_first_name())));
				}

				if ($order->get_billing_last_name()) {
					$user_data['ln'] = hash('sha256', trim(strtolower($order->get_billing_last_name())));
				}

				if ($order->get_billing_city()) {
					$user_data['ct'] = hash('sha256', str_replace(' ', '', trim(strtolower($order->get_billing_city()))));
				}

				if ($order->get_billing_state()) {
					$user_data['st'] = hash('sha256', trim(strtolower($order->get_billing_state())));
				}

				if ($order->get_billing_postcode()) {
					$user_data['zp'] = hash('sha256', $order->get_billing_postcode());
				}

				if ($order->get_billing_country()) {
					$user_data['country'] = hash('sha256', trim(strtolower($order->get_billing_country())));
				}
			}

			if (!$order && is_user_logged_in()) {

				$wp_user_info = get_userdata(get_current_user_id());

				// set user_id
				// must be sent by the browser simultaneously
				if (get_current_user_id() !== 0) {
					$user_data['external_id'] = hash('sha256', get_current_user_id());
				}

				// set em (email)
				$user_data['em'] = hash('sha256', trim(strtolower($wp_user_info->user_email)));

				if (isset($wp_user_info->first_name)) {
					$user_data['fn'] = hash('sha256', trim(strtolower($wp_user_info->first_name)));
				}

				if (isset($wp_user_info->last_name)) {
					$user_data['ln'] = hash('sha256', trim(strtolower($wp_user_info->last_name)));
				}
			}
		}

//		error_log(print_r($user_data, true));

		return $user_data;
	}

	// https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc
	public function wpm_facebook_set_session_identifiers() {

		// Don't run if WC has not initialized a session yet
		if (!WC()->session->has_session()) {
			return;
		}

		// Don't run if we already have set the FB user identifiers into the session
		if (null !== WC()->session->get($this->facebook_key)) {
			return;
		}

		$facebook_identifiers = $this->get_identifiers_from_browser();

//		error_log('Facebook identifiers');
//		error_log(print_r($facebook_identifiers, true));

		WC()->session->set($this->facebook_key, $facebook_identifiers);
	}

	protected function get_identifiers_from_browser() {

		$_server = filter_input_array(INPUT_SERVER, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$_cookie = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$facebook_identifiers = [];

		if (isset($_cookie['_fbp']) && $this->is_valid_fbp($_cookie['_fbp'])) {
			$facebook_identifiers['fbp'] = $_cookie['_fbp'];
		}

		if (isset($_cookie['_fbc']) && $this->is_valid_fbc($_cookie['_fbc'])) {
			$facebook_identifiers['fbc'] = $_cookie['_fbc'];
		}

		if ($this->get_user_ip()) {
			$facebook_identifiers['client_ip_address'] = $this->get_user_ip();
		}

		if (isset($_server['HTTP_USER_AGENT'])) {
			$facebook_identifiers['client_user_agent'] = $_server['HTTP_USER_AGENT'];
		}

		return $facebook_identifiers;
	}

	// https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc/
	protected function is_valid_fbp( $fbp ) {

		if (preg_match('/^fb\.[0-2]\.\d{13}\.\d{8,20}$/', $fbp)) {
			return true;
		} else {
			return false;
		}
	}

	// https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc/
	protected function is_valid_fbc( $fbc ) {

		if (preg_match('/^fb\.[0-2]\.\d{13}\.[\da-zA-Z_-]{8,}/', $fbc)) {
			return true;
		} else {
			return false;
		}
	}

	public function wpm_facebook_capi_event() {

		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		// don't check the nonce since it could have been cached on the front end

		// build in a way to only use nonces for logged in users

		if (!isset($_post['data'])) {
			wp_die();
		}

		$browser_event_data = $_post['data'];

		// use data to send FB CAPI event
//        error_log('data');
//        error_log(print_r($browser_event_data,true));

		$this->send_event_hit($browser_event_data);

		wp_die(); // this is required to terminate immediately and return a proper response
	}


	public function set_identifiers_on_order( $order ) {

		if (WC()->session->get($this->facebook_key)) {    // If the FB identifiers have been set on the session, get them from the session

			$facebook_identifiers = WC()->session->get($this->facebook_key);

			$facebook_identifiers['event_time'] = time();

			update_post_meta($order->get_id(), $this->facebook_key, $facebook_identifiers);

		} elseif (!get_post_meta($order->get_id(), $this->facebook_key)) { // Only run this if we haven't set a value already

			// Prevent reading out from an iframe
			if ($this->is_iframe()) {
				return;
			}

			$_cookie = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

			if (isset($_cookie['_fbp'])) {            // If we can get the identifiers from the browser cookies

				$facebook_identifiers = $this->get_identifiers_from_browser();

				wc_get_logger()->debug('Couldn\'t retrieve identifiers from session, but was able to get them directly from browser.', $this->logger_context);
			} else {

				$facebook_identifiers['client_user_agent'] = ( new User_Agent() )->get_random_user_agent();

				wc_get_logger()->debug('Couldn\'t retrieve identifiers from session and save them on order. Random identifiers have been set.', $this->logger_context);
			}

			$facebook_identifiers['event_time'] = time();

			update_post_meta($order->get_id(), $this->facebook_key, $facebook_identifiers);
		}
	}

	protected function get_identifiers_from_order( $order ) {

		/**
		 * If a client pays an order
		 * that the admin created in the back-end
		 * and fbp / fbc are available in the browser
		 * then return the fbp / fbc from the browser.
		 */
		if (
			!is_admin() &&
			$this->is_backend_manual_order($order->get_id()) &&
			$this->get_identifiers_from_browser()
		) {

			$facebook_identifiers = $this->get_identifiers_from_browser();

			$this->update_facebook_identifiers_on_order($order->get_id(), $this->facebook_key, $facebook_identifiers);

			return $facebook_identifiers;
		}

		$facebook_identifiers = get_post_meta($order->get_id(), $this->facebook_key, true);

		if (is_array($facebook_identifiers)) {
			return $facebook_identifiers;
		} else {
			return $this->get_random_base_identifiers();
		}

//        error_log('echo facebook identifiers');
//        error_log(print_r($facebook_identifiers, true));
//        error_log('fbp from server: ' . $facebook_identifiers['fbp']);

//        return get_post_meta($order->get_id(), $this->facebook_key);
	}

	protected function update_facebook_identifiers_on_order( $order_id, $facebook_key, $facebook_identifiers ) {

		$data = get_post_meta($order_id, $facebook_key, true);

		if (isset($facebook_identifiers['fbp'])) {
			$data['fbp'] = $facebook_identifiers['fbp'];
		}

		if (isset($facebook_identifiers['fbc'])) {
			$data['fbc'] = $facebook_identifiers['fbc'];
		}

		if (isset($facebook_identifiers['client_ip_address'])) {
			$data['client_ip_address'] = $facebook_identifiers['client_ip_address'];
		}

		if (isset($facebook_identifiers['client_user_agent'])) {
			$data['client_user_agent'] = $facebook_identifiers['client_user_agent'];
		}

		$facebook_identifiers['event_time'] = time();

		update_post_meta($order_id, $facebook_key, $data);
	}

	protected function get_random_base_identifiers() {
		return [
			'client_user_agent' => ( new User_Agent() )->get_random_user_agent(),
			'event_time'        => time(),
		];
	}

	/**
	 * Facebook suggests to user their SDK to generate the random fbp
	 * but, we won't do that. If we want true anonymity we need to generate the random
	 * number on our own terms.
	 * https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc/
	 **/

	protected function get_random_fbp() {
		$random_fbp = [
			'version'         => 'fb',
			'subdomain_index' => 1,
			'creation_time'   => time(),
			'random_number'   => random_int(1000000000, 9999999999),
		];

		return implode('.', $random_fbp);
	}
}
