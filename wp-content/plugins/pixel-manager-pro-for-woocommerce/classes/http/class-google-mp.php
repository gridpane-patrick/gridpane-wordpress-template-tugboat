<?php

namespace WCPM\Classes\Http;

use WCPM\Classes\Pixels\Trait_Shop;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Google_MP extends Http {

	use Trait_Shop;

	protected $cid_key;
	protected $cid;
	protected $use_debug_endpoint;

	public function __construct( $options ) {

		parent::__construct($options);

		$this->use_debug_endpoint = apply_filters_deprecated('wooptpm_google_mp_use_debug_endpoint', [false], '1.13.0', 'wpm_google_mp_use_debug_endpoint');
		$this->use_debug_endpoint = apply_filters('wpm_google_mp_use_debug_endpoint', $this->use_debug_endpoint);
	}

	protected function has_partial_refund_hit_already_been_sent( $order_id, $refund_id, $mp_partial_refund_hit_key ) {

		$post_meta = get_post_meta($order_id, $mp_partial_refund_hit_key, true);

		if ($post_meta) {
			return in_array($refund_id, $post_meta);
		} else {
			return false;
		}
	}

	protected function save_partial_refund_hit_to_db( $order_id, $refund_id, $mp_partial_refund_hit_key ) {

		$post_meta = get_post_meta($order_id, $mp_partial_refund_hit_key, true);

		if (!is_array($post_meta)) {
			$post_meta = [];
		}

		$post_meta[] = $refund_id;

		update_post_meta($order_id, $mp_partial_refund_hit_key, $post_meta);
	}

	public function wpm_google_analytics_set_session_data() {

		// Don't run if we're in an iframe
//		if (!$this->is_browser_on_shop()) {
//			return;
//		}

		// Don't run if we're in an iframe
		if ($this->is_iframe()) {
			return;
		}

		// Don't run if no WC session exists
		if (!WC()->session->has_session()) {
			return;
		}

		// Don't run if the identifiers already have been set on the WC session
		if (
			null !== WC()->session->get('google_cid_' . $this->options_obj->google->analytics->universal->property_id) ||
			null !== WC()->session->get('google_cid_' . $this->options_obj->google->analytics->ga4->measurement_id)
		) {
			return;
		}

		$data = $this->get_ga_identifiers_from_browser();

		$target_ids = [];

		if ($this->options_obj->google->analytics->universal->property_id) {
			$target_ids[] = $this->options_obj->google->analytics->universal->property_id;
		}

		if ($this->options_obj->google->analytics->ga4->measurement_id) {
			$target_ids[] = $this->options_obj->google->analytics->ga4->measurement_id;
		}

		foreach ($target_ids as $target_id) {

			$data['target_id'] = $target_id;

			$this->set_data_on_session($target_id, $data);
		}
	}

	// Getting GA4 session ID from browser
	//
	// gtag('get', 'GA4_MEASUREMENT_ID', 'session_id', (session_id) => {
	//    console.log(session_id)
	// })

	protected function get_ga4_session_id_from_cookie( $target_id ) {

		$_cookie = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if (isset($_cookie['_ga_' . $this->get_ga4_target_id_suffix($target_id)])) {

			preg_match('/^GS1\.\d\.(\d*)/', $_cookie['_ga_' . $this->get_ga4_target_id_suffix($target_id)], $matches);

			if (isset($matches[1])) {
				return $matches[1];
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	protected function get_ga4_target_id_suffix( $target_id ) {

		$re = '/[\dA-Z]{4,}/';

		preg_match($re, $target_id, $matches);

		if (isset($matches[0])) {
			return $matches[0];
		} else {
			return null;
		}
	}

	protected function set_data_on_session( $target_id, $data ) {

//		error_log('setting session data');
//		error_log(print_r($data, true));

		WC()->session->set('google_cid_' . $target_id, $data);

//        $data = WC()->session->get($cid_key);
//        $data = WC()->session->get('google_cid_' . $target_id);
//        error_log(print_r($data, true) );
	}

	protected function get_cid_from_session( $cid_key ) {

		$data = WC()->session->get($cid_key);

		if (isset($data['client_id'])) {
			return $data['client_id'];
		} else {
//            return bin2hex(random_bytes(10));
			return false;
		}
	}

	// TODO needs refactoring (simplification)
	protected function get_visitor_session_data_from_wc_session( $cid_key ) {

		$data = null;

		if (isset(WC()->session)) {
			$data = WC()->session->get($cid_key);
		}

		if (is_array($data) && $data) {
			return $data;
		} else {
			if (is_string($data) && $data) {
				return [
					'client_id' => $data,
				];
			} else {
//            return bin2hex(random_bytes(10));
				return [];
			}
		}
	}

	public function set_wc_session_data_on_order( $order, $cid_key ) {

		// Get the cid if the client provides one, if not, generate an anonymous one
		$data = $this->get_visitor_session_data_from_wc_session($cid_key);

//		error_log('wpm: session data order ID: ' . $order->get_id() . ' and cid key: ' . $cid_key);
//		error_log(print_r($data, true));

		/**
		 * If we're able to get the client ID from the session, write it.
		 * For some reason the function can be triggered multiple times.
		 * In that case it is ok to overwrite the value if we can get the client ID from
		 * the session. This way we can overwrite a random value that might have
		 * been set by a trigger that didn't provide a session with a client ID (possibly and iframe).
		 */
		if (isset($data['client_id'])) {

			// filter should be apply_filters('wpm_get_ga_cid_logger', false) after deprecation message is removed
			if (apply_filters('wpm_get_ga_cid_logger', apply_filters_deprecated('wooptpm_get_ga_cid_logger', [false], '1.13.0', 'wpm_get_ga_cid_logger'))) {
				wc_get_logger()->debug('Successfully received cid from session: ' . $data['client_id'] . ' for order ID ' . $order->get_id(), ['source' => 'wpm-cid']);
			}

			update_post_meta($order->get_id(), $cid_key, $data);

		} elseif (!get_post_meta($order->get_id(), $cid_key)) {  // Only run this if we haven't set a value already

			/**
			 * In case we were not able to get a client ID from the session,
			 * try to get the identifiers from the browser.
			 */

//			error_log('could not get cid from session');

			// Prevent reading out from an iframe
			if ($this->is_iframe()) {
				return;
			}

			$data = $this->get_ga_identifiers_from_browser();

			if (!isset($data['client_id'])) {
				$data['client_id'] = $this->get_random_cid();
			}

			update_post_meta($order->get_id(), $cid_key, $data);
		}


//		error_log('wpm: saving the following data on order');
//		error_log(print_r($data, true));
//
//		update_post_meta($order->get_id(), $cid_key, $data);
	}

	protected function get_ga_identifiers_from_browser() {

		$_cookie = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$_server = filter_input_array(INPUT_SERVER, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$_get    = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);


		$data = [];

		// Get Google Analytics client ID
		if (isset($_cookie['_ga'])) {
			$data['client_id'] = $this->get_ga_client_id_from_ga_cookie($_cookie['_ga']);
		}

		// Get Google Ads click ID
		if (isset($_cookie['_gcl_aw'])) {
			$data['gclid'] = $this->get_gclid_from_gcl_aw_cookie($_cookie['_gcl_aw']);
		} elseif (isset($_get['gclid'])) {
			$data['gclid'] = $_get['gclid'];
		}

		// Google Ads Double Click ID
		if (isset($_cookie['_gcl_dc'])) {
			$data['dclid'] = $this->get_gclid_from_gcl_aw_cookie($_cookie['_gcl_dc']);
		}

		// Get the referrer host
		if (isset($_cookie['wpmReferrer'])) {
			$data['referrer'] = $_cookie['wpmReferrer'];
		}

		// Get the user agent
		if (isset($_server['HTTP_USER_AGENT'])) {
			$data['user_agent'] = $_server['HTTP_USER_AGENT'];
		}

		if ($this->options_obj->google->analytics->ga4->measurement_id) {

			if ($this->get_ga4_session_id_from_cookie($this->options_obj->google->analytics->ga4->measurement_id)) {
				$data['ga4_session_id'] = $this->get_ga4_session_id_from_cookie($this->options_obj->google->analytics->ga4->measurement_id);
			}
		}

		return $data;
	}

	private function get_ga_client_id_from_ga_cookie( $cookie ) {

		preg_match('/(GA1.[\d]*.)(.*)/', $cookie, $matches);

		if (isset($matches[2])) {
			return $matches[2];
		} else {
			return null;
		}
	}

	private function get_gclid_from_gcl_aw_cookie( $cookie ) {

		preg_match('/(GCL.[\d]*.)(.*)/', $cookie, $matches);

		if (isset($matches[2])) {
			return $matches[2];
		} else {
			return null;
		}
	}

	protected function get_ga4_sid_from_ga_cookie( $cookie ) {

		$cookie = 'GS1.1.1653218243.2.1.1653218250.53';

		preg_match('/^GS1\.[\d]\.([\d]*)\./', $cookie, $matches);

		if (isset($matches[1])) {
			return $matches[1];
		} else {
			return null;
		}
	}

	public function get_cid_from_order( $order, $cid_key ) {

		/**
		 * If a client pays an order
		 * that the admin created in the back-end
		 * and a cid is available in the browser
		 * then return the cid from the browser.
		 */
		if (
			!is_admin() &&
			$this->is_backend_manual_order($order->get_id()) &&
			$this->get_cid_from_browser()
		) {

			$cid = $this->get_cid_from_browser();
			$this->update_cid_on_order($order->get_id(), $cid_key, $cid);

			return $cid;
		}

		$data = get_post_meta($order->get_id(), $cid_key, true);

		// If cid was saved on the order, get it
		if (isset($data['client_id'])) {
			return $data['client_id'];
		} elseif ($data && is_string($data)) {
			return $data;
		}

		if ($this->is_filter_wpm_get_ga_cid_logger_active()) {
			wc_get_logger()->debug('Couldn\'t retrieve cid for order ID: ' . $order->get_id() . '. Setting random cid', ['source' => 'wpm-cid']);
		}

		return $this->get_random_cid();
	}

	public function get_ga4_session_id_from_order( $order, $cid_key ) {

		/**
		 * If a client pays an order
		 * that the admin created in the back-end
		 * and a cid is available in the browser
		 * then return the cid from the browser.
		 */
		if (
			!is_admin() &&
			$this->is_backend_manual_order($order->get_id()) &&
			$this->get_ga4_session_id_from_cookie($this->options_obj->google->analytics->ga4->measurement_id)
		) {
			$ga4_session_id = $this->get_ga4_session_id_from_cookie($this->options_obj->google->analytics->ga4->measurement_id);
			$this->update_ga4_sid_on_order($order->get_id(), $cid_key, $ga4_session_id);

			return $ga4_session_id;
		}

		$data = get_post_meta($order->get_id(), $cid_key, true);

		// If cid was saved on the order, get it
		if (isset($data['ga4_session_id'])) {
			return $data['ga4_session_id'];
		}

		return null;
	}

	protected function update_cid_on_order( $order_id, $cid_key, $cid ) {

		$data = get_post_meta($order_id, $cid_key, true);

		if (isset($data['client_id'])) {

			$data['client_id'] = $cid;
			update_post_meta($order_id, $cid_key, $data);
		}
	}

	protected function update_ga4_sid_on_order( $order_id, $cid_key, $ga4_session_id ) {

		$data = get_post_meta($order_id, $cid_key, true);

		if (isset($data['ga4_session_id'])) {

			$data['ga4_session_id'] = $ga4_session_id;
			update_post_meta($order_id, $cid_key, $data);
		}
	}

	protected function get_cid_from_browser() {

		$_cookie = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if (isset($_cookie['_ga'])) {
			return $this->get_ga_client_id_from_ga_cookie($_cookie['_ga']);
		}

		return false;
	}

	protected function is_filter_wpm_get_ga_cid_logger_active() {

		// Enable wpm_get_ga_cid_logger
		return apply_filters('wpm_get_ga_cid_logger', apply_filters_deprecated('wooptpm_get_ga_cid_logger', [false], '1.13.0', 'wpm_get_ga_cid_logger'));
	}

	public function get_tracking_parameter_from_order( $order, $cid_key, $parameter ) {

		$data = get_post_meta($order->get_id(), $cid_key, true);

		if (isset($data[$parameter])) {
			return $data[$parameter];
		} else {
			return null;
		}
	}

	public function is_cid_set_on_order( $order, $cid_key ) {

		$data = get_post_meta($order->get_id(), $cid_key, true);

		if (isset($data['client_id'])) {
			return true;
		} else {
			return false;
		}
	}

	protected function approve_purchase_hit_processing( $order, $cid, $cid_key ) {

		/**
		 * Only approve, if the hit has not been sent already (check in db)
		 *
		 * Also approve subscription renewals (cid is missing on order but available as argument),
		 * but don't approve normal orders before premium activation where the cid is missing on the order.
		 */

		// Don't approve if the purchase hit has already been processed
		if ($this->check_if_purchase_hit_post_meta_exists($order->get_id(), $this->mp_purchase_hit_key)) {
			return false;
		}

		// Process order if it is a backend order, otherwise it will fail in the next test
		if ($this->is_backend_manual_order($order->get_id())) {
			return true;
		}

//		if ($this->was_order_created_while_wpm_was_active($order->get_id())) {
//			return true;
//		}

//		if ($this->is_backend_subscription_renewal_order($order->get_id())) {
//			return true;
//		}

		if ($this->was_order_created_while_wpm_premium_was_active($order->get_id())) {
			return true;
		}

		/**
		 * Don't approve if cid is missing and no cid has been set on the order,
		 *
		 * This means the order has been placed before WPM was active,
		 * and before we introduced _wpm_process_through_wpm meta key.
		 *
		 * The cid is sent on subscription renewals, because the new order was not
		 * created by a customer and doesn't contain a cid. But when the
		 * renewal is created WPM hooks into it, searches the cid on the parent
		 * order and sends it with the approval request.
		 */
		if (null === $cid && $this->is_cid_set_on_order($order, $cid_key) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Needed to create this because I changed the key and now some order confirmations get re-sent to GA
	 *
	 * @param $order_id
	 * @param $key
	 * @return bool
	 */

	protected function check_if_purchase_hit_post_meta_exists( $order_id, $key ) {

		/**
		 * List of possible db meta keys:
		 * wpm_google_analytics_ua_mp_purchase_hit
		 * wooptpm_google_analytics_ua_mp_purchase_hit
		 * wpm_google_analytics_4_mp_purchase_hit
		 * wooptpm_google_analytics_4_mp_purchase_hit
		 */

		if ('wpm_google_analytics_ua_mp_purchase_hit' === $key) {
			if (
				get_post_meta($order_id, 'wpm_google_analytics_ua_mp_purchase_hit') ||
				get_post_meta($order_id, 'wooptpm_google_analytics_ua_mp_purchase_hit')
			) {
				return true;
			} else {
				return false;
			}
		} else {
			if (
				get_post_meta($order_id, 'wpm_google_analytics_4_mp_purchase_hit') ||
				get_post_meta($order_id, 'wooptpm_google_analytics_4_mp_purchase_hit')
			) {
				return true;
			} else {
				return false;
			}
		}
	}

	protected function get_random_cid() {
		return random_int(1000000000, 9999999999) . '.' . time();
	}
}
