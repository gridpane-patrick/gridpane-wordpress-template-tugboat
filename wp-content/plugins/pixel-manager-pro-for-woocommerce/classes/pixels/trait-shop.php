<?php

namespace WCPM\Classes\Pixels;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use WC_Geolocation;
use WCPM\Classes\Admin\Documentation;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

trait Trait_Shop {

	protected $clv_orders_by_billing_email = null;

	protected function get_list_name_suffix() {

		$list_suffix = '';

		if (is_product_category()) {

			$category    = get_queried_object();
			$list_suffix = ' | ' . wp_specialchars_decode($category->name);
			$list_suffix = $this->add_parent_category_name($category, $list_suffix);
		} else {
			if (is_product_tag()) {
				$tag         = get_queried_object();
				$list_suffix = ' | ' . wp_specialchars_decode($tag->name);
			}
		}

		return $list_suffix;
	}

	protected function add_parent_category_name( $category, $list_suffix ) {

		if ($category->parent > 0) {

			$parent_category = get_term_by('id', $category->parent, 'product_cat');
			$list_suffix     = ' | ' . wp_specialchars_decode($parent_category->name) . $list_suffix;
			$list_suffix     = $this->add_parent_category_name($parent_category, $list_suffix);
		}

		return $list_suffix;
	}

	protected function get_list_id_suffix() {

		$list_suffix = '';

		if (is_product_category()) {
			$category    = get_queried_object();
			$list_suffix = '.' . $category->slug;
			$list_suffix = $this->add_parent_category_id($category, $list_suffix);
		} else {
			if (is_product_tag()) {
				$tag         = get_queried_object();
				$list_suffix = '.' . $tag->slug;
			}
		}

		return $list_suffix;
	}

	protected function add_parent_category_id( $category, $list_suffix ) {

		if ($category->parent > 0) {

			$parent_category = get_term_by('id', $category->parent, 'product_cat');
			$list_suffix     = '.' . $parent_category->slug . $list_suffix;
			$list_suffix     = $this->add_parent_category_id($parent_category, $list_suffix);
		}

		return $list_suffix;
	}

	// https://stackoverflow.com/a/49616130/4688612
	protected function get_order_from_order_received_page() {

		if ($this->get_order_from_query_vars()) {
			return $this->get_order_from_query_vars();
		} else {
			if ($this->get_order_with_url_order_key()) {
				return $this->get_order_with_url_order_key();
			} else {
				return false;
			}
		}
	}

	protected function wpm_is_order_received_page() {

		return is_order_received_page() || $this->is_valid_order_key_in_url();
	}

	protected function is_valid_order_key_in_url() {

		$_get = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if (isset($_get['key']) && wc_get_order_id_by_order_key($_get['key'])) {
			return true;
		} else {
			return false;
		}
	}

	protected function wpm_get_order_total( $order ) {

		$order_total = 0 == $this->options_obj->shop->order_total_logic ? $order->get_subtotal() - $order->get_total_discount() : $order->get_total();

		// filter to adjust the order value
		$order_total = apply_filters_deprecated('wgact_conversion_value_filter', [$order_total, $order], '1.10.2', 'wooptpm_conversion_value_filter');
		$order_total = apply_filters_deprecated('wooptpm_conversion_value_filter', [$order_total, $order], '1.13.0', 'wpm_conversion_value_filter');
		$order_total = apply_filters('wpm_conversion_value_filter', $order_total, $order);

		return wc_format_decimal((float) $order_total, 2);
	}

	protected function get_order_from_query_vars() {

		global $wp;

		if (!isset($wp->query_vars['order-received'])) {
			return false;
		}

		$order_id = absint($wp->query_vars['order-received']);

		if ($order_id && 0 != $order_id && wc_get_order($order_id)) {

			return wc_get_order($order_id);
		} else {

			wc_get_logger()->debug(
				'WooCommerce couldn\'t retrieve the order ID from $wp->query_vars[\'order-received\']',
				['source' => 'wpm']
			);

			wc_get_logger()->debug(
				print_r($wp->query_vars, true),
				['source' => 'wpm']
			);

			return false;
		}
	}


	protected function get_order_with_url_order_key() {

		$_get = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if (isset($_get['key'])) {

			$order_key = $_get['key'];
			return wc_get_order(wc_get_order_id_by_order_key($order_key));
		} else {

			wc_get_logger()->debug(
				'WooCommerce couldn\'t retrieve the order ID from order key in the URL',
				['source' => 'wpm']
			);

			$order_key = $_get['key'] ? $_get['key'] : '';
			wc_get_logger()->debug(
				'URL order key: ' . $order_key,
				['source' => 'wpm']
			);

			return false;
		}
	}

	protected function get_order_currency( $order ) {

		// use the right function to get the currency depending on the WooCommerce version
		return $this->woocommerce_3_and_above() ? $order->get_currency() : $order->get_order_currency();
	}

	protected function woocommerce_3_and_above() {

		global $woocommerce;

		if (version_compare($woocommerce->version, 3.0, '>=')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Don't count in the current order
	 * https://stackoverflow.com/a/46216073/4688612
	 * https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query#description
	 */
	protected function is_existing_customer( $order ) {

		$query_arguments = [
			'return'      => 'ids',
			'exclude'     => [$order->get_id()],
			'post_status' => wc_get_is_paid_statuses(),
			'limit'       => 1,
		];

		if (is_user_logged_in()) {
			$current_user                = wp_get_current_user();
			$query_arguments['customer'] = sanitize_email($current_user->user_email);

		} else {
			$query_arguments['billing_email'] = sanitize_email($order->get_billing_email());
		}

		$orders = wc_get_orders($query_arguments);

		return count($orders) > 0;
	}

	protected function is_new_customer( $order ) {
		return !$this->is_existing_customer($order);
	}

	// https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query
	// https://github.com/woocommerce/woocommerce/blob/5d7f6acbcb387f1d51d51305bf949d07fa3c4b08/includes/data-stores/class-wc-customer-data-store.php#L401
	protected function get_clv_order_total_by_billing_email( $billing_email ) {

		$orders = $this->get_all_paid_orders_by_billing_email($billing_email);

		$value = 0;

		foreach ($orders as $order) {
			$value += $order->get_total();
		}

		return wc_format_decimal($value, 2);
	}

	protected function get_clv_value_filtered_by_billing_email( $billing_email ) {

		$orders = $this->get_all_paid_orders_by_billing_email($billing_email);

		$value = 0;

		foreach ($orders as $order) {
			$value += (float) $this->wpm_get_order_total($order);
		}

		return wc_format_decimal($value, 2);
	}

	protected function get_all_paid_orders_by_billing_email( $billing_email ) {

		if ($this->clv_orders_by_billing_email) {
			return $this->clv_orders_by_billing_email;
		} else {

			$query_arguments = [
				'billing_email' => sanitize_email($billing_email),
				//			'return'        => 'ids',
				'post_status'   => wc_get_is_paid_statuses(),
				'limit'         => -1,
			];

			$orders = wc_get_orders($query_arguments);

			$this->clv_orders_by_billing_email = $orders;

			return $orders;
		}
	}

	protected function get_user_ip() {

		if ($this->is_localhost()) {
			$ip = WC_Geolocation::get_external_ip_address();
		} else {
			$ip = WC_Geolocation::get_ip_address();
		}

		// only set the IP if it is a public address
		$ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

		// Remove the IPv6 to IPv4 mapping in case the IP contains one
		// and return the IP plain public IPv4 or IPv6 IP
		// https://en.wikipedia.org/wiki/IPv6_address
		return str_replace('::ffff:', '', $ip);
	}

	protected function get_visitor_country() {

		$location = WC_Geolocation::geolocate_ip($this->get_user_ip());

		return $location['country'];
	}

	protected function is_localhost() {

		// If the IP is local, return true, else false
		// https://stackoverflow.com/a/13818647/4688612

		return !filter_var(
			WC_Geolocation::get_ip_address(),
			FILTER_VALIDATE_IP,
			FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
	}

	protected function get_user_email( $algo = null ) {

		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$email        = $current_user->user_email;
		} elseif ($this->wpm_is_order_received_page()) {
			$order = $this->get_order_from_order_received_page();

			if ($order) {
				$email = $order->get_billing_email();
			} else {
				$email = '';
			}
		} else {
			$email = '';
		}

		// encrypt email
		if ($email && $algo && in_array($algo, hash_algos(), true)) {
			$email = hash($algo, $email);
		}

		return $email;
	}

	private function get_e164_formatted_phone_number( $number, $country ) {

		try {
			$phone_util    = PhoneNumberUtil::getInstance();
			$number_parsed = $phone_util->parse($number, $country);
			return $phone_util->format($number_parsed, PhoneNumberFormat::E164);
		} catch (NumberParseException $e) {
			error_log($e);
			return $number;
		}
	}

	protected function track_user( $user_id = null ) {

		$user = null;

		if (0 === $user_id) {                                    // If anonymous visitor then track
			return true;
		} elseif ($user_id && 0 <= $user_id) {                   // If user ID is known, get the user
			$user = get_user_by('id', $user_id);
		} elseif (null === $user_id && is_user_logged_in()) {    // If user id is not given, but the user is logged in, get the user
			$user = wp_get_current_user();
		}

		// Find out if the user has a role that is restricted from tracking
		if ($user) {

			foreach ($user->roles as $role) {
				if (in_array($role, $this->options_obj->shop->disable_tracking_for, true)) {
					return false;
				}
			}
		}

		return true;
	}

	protected function do_not_track_user( $user_id = null ) {
		return !$this->track_user($user_id);
	}

	// https://wordpress.stackexchange.com/a/95440/68337
	// https://wordpress.stackexchange.com/a/31435/68337
	// https://developer.wordpress.org/reference/functions/get_the_title/
	// https://codex.wordpress.org/Data_Validation#Output_Sanitation
	// https://developer.wordpress.org/reference/functions/wp_specialchars_decode/
	protected function wpm_get_the_title( $post = 0 ) {

		$post  = get_post($post);
		$title = isset($post->post_title) ? $post->post_title : '';

		return wp_specialchars_decode($title);
	}

	protected function is_backend_manual_order( $post_id ) {

		// Only continue if this is a back-end order
		if (metadata_exists('post', $post_id, '_created_via') && 'admin' === get_post_meta($post_id, '_created_via', true)) {
			return true;
		} else {
			return false;
		}
	}

	protected function is_backend_subscription_renewal_order( $post_id ) {

		// Only continue if this is a back-end order
		if (metadata_exists('post', $post_id, '_created_via') && 'subscription' === get_post_meta($post_id, '_created_via', true)) {
			return true;
		} else {
			return false;
		}
	}

	protected function was_order_created_while_wpm_was_active( $order_id ) {
		if (get_post_meta($order_id, '_wpm_process_through_wpm', true)) {
			return true;
		} else {
			return false;
		}
	}

	protected function was_order_created_while_wpm_premium_was_active( $order_id ) {
		if (get_post_meta($order_id, '_wpm_premium_active', true)) {
			return true;
		} else {
			return false;
		}
	}

	protected function wpm_get_order_user_id( $order_id ) {

		if (metadata_exists('post', $order_id, '_wpm_customer_user')) {
			return (int) get_post_meta($order_id, '_wpm_customer_user', true);
		} else {
			return (int) get_post_meta($order_id, '_customer_user', true);
		}
	}

	protected function is_browser_on_shop() {

		$_server = filter_input_array(INPUT_SERVER, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

//		error_log(print_r($_server, true));
//		error_log(print_r($_server['HTTP_HOST'], true));
//		error_log('get_site_url(): ' . parse_url(get_site_url(), PHP_URL_HOST));
//		error_log('parse url https://www.exampel.com : ' . parse_url('https://www.exampel.com', PHP_URL_HOST));

		// Servers like Siteground don't seem to always provide $_server['HTTP_HOST']
		// In that case we need to pretend that we're on the same server
		if (!isset($_server['HTTP_HOST'])) {
			return true;
		}

		if (wp_parse_url(get_site_url(), PHP_URL_HOST) === $_server['HTTP_HOST']) {
			return true;
		} else {
			return false;
		}
	}

	// https://stackoverflow.com/a/60199374/4688612
	protected function is_iframe() {
		if (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && 'iframe' === $_SERVER['HTTP_SEC_FETCH_DEST']) {
			return true;
		} else {
			return false;
		}
	}

	protected function can_order_confirmation_be_processed( $order ) {

		$conversion_prevention = false;
		$conversion_prevention = apply_filters_deprecated('wgact_conversion_prevention', [$conversion_prevention, $order], '1.10.2', 'wooptpm_conversion_prevention');
		$conversion_prevention = apply_filters_deprecated('wooptpm_conversion_prevention', [$conversion_prevention, $order], '1.13.0', 'wpm_conversion_prevention');
		$conversion_prevention = apply_filters('wpm_conversion_prevention', $conversion_prevention, $order);

		if (
			$this->is_nodedupe_parameter_set() ||
			(
				!$order->has_status('failed') &&
				$this->track_user() &&
				false === $conversion_prevention &&
				(
					!$this->options['shop']['order_deduplication'] ||
					$this->has_conversion_pixel_already_fired($order) !== true
				)
			)
		) {
			return true;
		} else {
			return false;
		}
	}

	protected function has_conversion_pixel_already_fired( $order ) {

		if (true) {

			if (get_post_meta($order->get_id(), '_wpm_conversion_pixel_fired', true)) {
				return true;
			} elseif (get_post_meta($order->get_id(), '_wooptpm_conversion_pixel_fired', true)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	protected function is_nodedupe_parameter_set() {

		$_get = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if (isset($_get['nodedupe'])) {
			return true;
		} else {
			return false;
		}
	}

	protected function conversion_pixels_already_fired_html() {
		?>

		<!--	----------------------------------------------------------------------------------------------------
				The conversion pixels have not been fired. Possible reasons:
					- The user role has been disabled for tracking.
					- The order payment has failed.
					- The pixels have already been fired. To prevent double counting the pixels are only fired once.

				If you want to test the order you have two options:
					- Turn off order duplication prevention in the advanced settings
					- Add the '&nodedupe' parameter to the order confirmation URL like this:
					  https://example.test/checkout/order-received/123/?key=wc_order_123abc&nodedupe

				More info on testing: <?php esc_html_e(( new Documentation() )->get_link('test_order')); ?>

				----------------------------------------------------------------------------------------------------
		-->
		<?php
	}

	protected function get_options_object( $options ) {

		// TODO find out why I did this weird transformation and simplify it
		$options_obj = json_decode(wp_json_encode($options));

		if (function_exists('get_woocommerce_currency')) {
			$options_obj->shop->currency = get_woocommerce_currency();
		}

		return $options_obj;
	}

	protected function is_email( $email ) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
}

