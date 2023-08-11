<?php

defined('ABSPATH') || exit;
define('TAAGER_URL', 'https://woocommerce-api-gw.api.taager.com');

/**
 * Load plugin js, styles
 */
add_filter('admin_body_class', function ($classes) {
	global $pagenow;

	if (in_array($_GET['page'], ['taager_product_setting', 'taager_setting', 'taager_country_selection'])) {
		$classes .= ' shahbandr ';
	}

	return $classes;
});

add_action('admin_enqueue_scripts', function () {
	if (in_array($_GET['page'], ['taager_product_setting', 'taager_setting', 'taager_country_selection'])) {

		wp_enqueue_script('pagination-js-file', 'https://pagination.js.org/dist/2.1.5/pagination.min.js', '', time());
		wp_enqueue_script('taager-js-file', plugin_dir_url(__DIR__) . 'assets-new/js/script.js', '', time());
		wp_enqueue_script('taager-waitMe-js-file', plugin_dir_url(__DIR__) . 'assets-new/js/waitMe.js', '', time());
		wp_enqueue_style('taager-css-file', plugin_dir_url(__DIR__) . 'assets-new/css/style.css', '', time());
		wp_enqueue_style('taager-waitMe-css-file', plugin_dir_url(__DIR__) . 'assets-new/css/waitMe.min.css', '', time());
		wp_enqueue_style('taager-css-file', 'https://pagination.js.org/dist/2.0.7/pagination.css', '', time());
	}
});

add_action('admin_enqueue_scripts', 'ta_admin_enqueue');
function ta_admin_enqueue($hook)
{
	wp_enqueue_script('ta_script', plugin_dir_url(__DIR__) . '/assets/js/admin.js', array('jquery'), rand(), true);
	wp_enqueue_script('ta_country_selection_script', plugin_dir_url(__DIR__) . '/assets/js/country-selection.js', array('jquery'), rand(), true);
	wp_enqueue_style('ta_style', plugin_dir_url(__DIR__) . '/assets/css/admin.css', array(), rand());
	$localize_array = [
		'ajaxURL'        => admin_url('admin-ajax.php'),
		'taager_product' => 0,
	];
	if (isset($_GET['post'])) {
		$product_id = intval($_GET['post']);
		$ptype      = get_post_type($product_id);
		if ('product' == $ptype) {
			$is_taager_product = get_post_meta($product_id, 'taager_product', true);
			$taager_price      = get_post_meta($product_id, '_ta_product_price', true);

			$localize_array['taager_product'] = $is_taager_product ? $is_taager_product : 0;
			$localize_array['taager_price']   = $taager_price;
			$localize_array['currency']       = get_woocommerce_currency_symbol();
		}
	}
	wp_localize_script('ta_script', 'ta_admin', $localize_array);
}

/**
 * Get product profit when request Ajax post from frontend
 */
add_action('wp_ajax_get_product_profit', 'get_product_profit');
function get_product_profit()
{
	$product_id = intval($_POST['productId']);

	// Get product profit from meta data
	$product_profit = get_post_meta($product_id, '_ta_product_profit', true);

	echo $product_profit;

	wp_die();
}

/**
 * Initialize cURL for HTTP requests
 */
function callAPI($method, $url, $data = false)
{
	$ta_api_username = get_option('ta_api_username');
	$ta_api_password = get_option('ta_api_password');
	$ta_selected_country = get_option('ta_selected_country', 'EGY');

	$curl = curl_init();

	$headers = array();

	switch ($method) {
		case 'POST':
			curl_setopt($curl, CURLOPT_POST, 1);

			if ($data) {
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				$headers[] = 'Content-Type: application/json';
			}

			break;
		case 'PUT':
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');

			break;
		default:
			if ($data) {
				$url = sprintf('%s?%s', $url, http_build_query($data));
			}
	}

	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($curl, CURLOPT_USERPWD, $ta_api_username . ':' . $ta_api_password);

	// Adding country header
	if ($ta_selected_country) {
		$headers[] = "country: $ta_selected_country";
	}

	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($curl);
	$status = curl_getinfo($curl);

	if (!$result && $status['http_code'] != 200) {
		die('Connection Failure');
	}

	curl_close($curl);

	return json_decode($result);
}

/**
 * Initialize Taager API
 *
 * Import Categories, Provinces from backend via the APIs and navigate to taager product setting page
 */
function ta_initialize()
{
	update_option('ta_initial_status', 'running');

	if (class_exists('SQLite3')) {
		if (!file_exists(WP_CONTENT_DIR . '/ta.db')) {
			new SQLite3(WP_CONTENT_DIR . '/ta.db');
		} 
		try {
			$db = new PDO("sqlite:" . WP_CONTENT_DIR . '/ta.db');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (Exception $e) {
			echo "Unable to connect to database";
			echo $e->getMessage();
			exit;
		}

		import_categories($db);

	} else {
		import_categories();
	}
	//wp_schedule_event( time(), 'every_one_hour', 'ta_hourly_update_hook' );

	update_option('ta_initial_status', 'done');
	wp_redirect(admin_url('admin.php?page=taager_product_setting'));
}

/**
 * Import Products from backend via the APIs
 */
// add_action( 'taager_import_products', 'ta_import_products', 10, 2 );
function ta_import_products($product_category, $product_name)
{
	update_option('ta_product_import_status', 'running');

	import_products($product_category, $product_name);

	update_option('ta_product_import_status', 'done');
}

/**
 * Import categories from backend
 */
function import_categories($db = null)
{
	$categories = callAPI('GET', TAAGER_URL . '/category/');
	$taager_categories_name_lits = array();


	if ($db) {
		$db->exec("CREATE TABLE IF NOT EXISTS categories(
			id INTEGER PRIMARY KEY, 
			_id TEXT,
			country TEXT, 
			name TEXT , 
			text TEXT)");
	}

	try {
		$db->beginTransaction();
		$stmt = $db->prepare("INSERT INTO categories  ('_id', 'country', 'name', 'text') VALUES (?,?,?,?)");
	
		for ($i = 0; $i < count($categories->data); $i++) {

			$category = $categories->data[$i];
			$taager_categories_name_lits[] = $category->text;
			if ($db) {


				if (!$db->query("SELECT * FROM categories where _id='$category->_id'")->fetch(PDO::FETCH_ASSOC)) {
					$stmt->execute([$category->_id, $category->country, $category->name, $category->text]);
				}
			}

			// Add new category if don't exist alrady
			if (!term_exists($category->text, 'product_cat')) {
				$cat_id_data = wp_insert_term($category->text, 'product_cat');
				$cat_id = $cat_id_data['term_id'];
				add_term_meta($cat_id, "category_type", "taager");
			}

			if ($category->text == 'عروض تاجر الحصرية') {
				$offer_term = get_term_by('slug', $category->text, 'product_cat');
				wp_update_term($offer_term->term_id, 'product_cat', array(
					'name' => $category->text,
				));
			}
		}
		$db->commit();
	} catch (Exception $e) {
		//An exception has occured, which means that one of our database queries
		//failed.
		//Print out the error message.
		echo $e->getMessage();
		//Rollback the transaction.
		$db->rollBack();
	}
	if (!empty($taager_categories_name_lits)) :
		update_option('taager_categories_name_lits', $taager_categories_name_lits);
	endif;
}


/**
 * wc default hook to disable payment functionality on checkout.
 */
// Include plugin.php
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
// Create the plugins folder and file variables
$plugin_folder = get_plugins('/' . 'woocommerce');
$plugin_file   = 'woocommerce.php';

// If the plugin version number is set, return it
if (isset($plugin_folder[$plugin_file]['Version'])) {
	$wpowp_wc_version = $plugin_folder[$plugin_file]['Version'];

	if (version_compare($wpowp_wc_version, '4.7.0', '<')) {
		// Disable payment method in cart & checcout
		add_filter('woocommerce_cart_needs_payment', 'disable_payment_cart_and_checcout');
	} else {
		// Disable payment method in cart & checcout
		add_filter('woocommerce_cart_needs_payment', 'disable_payment_cart_and_checcout');
		add_filter('woocommerce_order_needs_payment', 'disable_payment_cart_and_checcout');
	}
}
function disable_payment_cart_and_checcout()
{
	return false;
}
/**
 * Remove available payment gateways
 */
add_filter('woocommerce_available_payment_gateways', 'all_payment_gateway_disable');
function all_payment_gateway_disable($available_gateways)
{
	return [];
}

/**
 * Disable shipping functionality
 */
add_action('wp', 'ta_default_shipping');
function ta_default_shipping()
{
	if (!is_admin() && has_taager_product_in_cart()) {
		add_filter('wc_shipping_enabled', '__return_false');
	}
}

/**
 * Customize address fields in checkout
 */

add_filter('woocommerce_checkout_fields', 'ta_billing_fields_modify');
function ta_billing_fields_modify($address_fields)
{
	//$address_fields['billing']['billing_email']['required'] = false;
	//unset($address_fields['billing']['billing_email']);

	$address_fields['billing']['billing_email']['required'] = false;

	if (!has_taager_product_in_cart()) {
		return $address_fields;
	}
	if (isset($address_fields['billing']['billing_state']) && !empty($address_fields['billing']['billing_state'])) {
		unset($address_fields['billing']['billing_state']['validate']);

		global $wpdb;
		$ta_selected_country = get_option('ta_selected_country');
		if ($ta_selected_country == '') {
			$ta_selected_country = "EGY";
		}

		$province_array = array();
		$old_provinces = file_get_contents('https://dev.myshahbandr.com/wp-content/provinces.json');
		if ($old_provinces) {
			$old_provinces = json_decode($old_provinces);
		}
		$province_array[''] = "اختار المنطقة";
		if (isset($old_provinces->{$ta_selected_country})) {
			foreach ($old_provinces->{$ta_selected_country} as $key => $value) {
				if ($value->country == $ta_selected_country and $value->active_status == 1) {
					$province_array[$value->province] = $value->province;
				}
			}
		}


		// Customize city field
		$address_fields['billing']['billing_state']['type']    = 'select';
		$address_fields['billing']['billing_state']['options'] = $province_array;
	}

	return $address_fields;
}
add_filter('woocommerce_default_address_fields', 'ta_customized_address_fields');

function ta_customized_address_fields($address_fields)
{
	global $wpdb;
	global $pagenow;

	if (!is_admin()) {
		if (!has_taager_product_in_cart()) {
			return $address_fields;
		}
		// Remove company, country, address_2, state, postcode fields

		$lang = get_bloginfo("language");

		unset($address_fields['company']);
		unset($address_fields['country']);
		unset($address_fields['address_2']);
		unset($address_fields['city']);
		unset($address_fields['postcode']);
		unset($address_fields['last_name']);

		//first name in full width
		$address_fields['first_name']['class'] = array('form-row-wide');

		if ($lang == 'ar') {
			$address_fields['first_name']['label'] = 'الاسم بالكامل';
		} else {
			$address_fields['first_name']['label'] = 'Full Name';
		}

		$address_fields['address_1']['class'] = array('form-row-wide');
	}
	return $address_fields;
}

/**
 * Send created order to backend via POST order API
 */
add_action('woocommerce_checkout_order_created', 'ta_send_order');
function ta_send_order($order)
{
	global $wpdb;

	$wp_order_id = $order->get_id(); // Get order ID

	//my test dr
	echo get_post_meta($wp_order_id, 'ta__shipping_charge', true);

	if (!has_taager_product_in_order($wp_order_id)) {
		return;
	}

	$is_external       = true;
	//$external_name   = 'WooCommerce';
	$external_name     = ucfirst(get_bloginfo('name'));
	$order_received_by = 'WooCommerce_cart';
	//$order_received_by = ucfirst(get_bloginfo( 'name' ));
	$order_status      = 'order_received';

	$customer_name = $order->get_formatted_billing_full_name(); // Get customer's full name
	$full_address  = $order->get_billing_address_1(); // Get full address
	$province      = $order->get_billing_state(); // Get province
	$phone_number  = $order->get_billing_phone(); // Get phone number
	//$order_note    = $order->get_customer_note(); // Get customer note

	if (get_current_blog_id() == 3549) {
		$order_note = "يرجى كرما اخبار العميل ان الطلب من موقع مولازا مصر وشكرا لكل فريق التأكيد المحترم";
	} elseif (get_current_blog_id() == 3550) {
		$order_note = "يرجى كرما اخبار العميل ان الطلب من موقع مولازا السعودية وشكرا لكل فريق التأكيد المحترم";
	} else {
		$order_note    = $order->get_customer_note(); // Get customer note
	}

	$phone_number2 = get_post_meta($wp_order_id, '_billing_phone2', true);

	$product_ids        = array();
	$product_prices     = array();
	$product_quantities = array();

	$ta__flatrate_shipping = null;
	$my__flatrateId = array();
	// Loop through order items to get products info
	foreach ($order->get_items() as $item_id => $item) {
		$product              = $item->get_product();
		$product_ids[]        = $product->get_sku(); // Get the product SKU
		$product_prices[]     = intval($item->get_total()); // Get the product price
		$product_quantities[] = $item->get_quantity(); // Get the product quantities

		$flatrate_product_id   = $item->get_product_id();
		$ta__flatrate_shipping = get_post_meta($flatrate_product_id, 'ta__shipping_charge', true);
		if ($ta__flatrate_shipping == 'yes' || !empty($ta__flatrate_shipping)) {
			if (empty($my__flatrateId)) {
				$my__flatrateId[] = $product->get_sku();
			}
		}
	}

	//$plugin_version = get_option( 'ta_plugin_version' );

	$cash_on_delivery = intval($order->get_total());
	// Payload for POST order API
	$order_data = array(
		'isExternal'         => $is_external,
		'externalName'       => $external_name,
		'orderReceivedBy'    => $order_received_by,
		'status'             => $order_status,
		'customerName'       => $customer_name,
		'fullAddress'        => $full_address,
		'province'           => $province,
		'phoneNumber'        => $phone_number,
		'phoneNumberAlt'     => $phone_number2,
		'message'            => $order_note,
		'cashOnDelivery'     => $cash_on_delivery,
		'productIds'         => $product_ids,
		'productPrices'      => $product_prices,
		'productQuantities'  => $product_quantities,
		'flat_rate_products' => $my__flatrateId,
	);
	/*if($plugin_version) {
		$order_data['pluginVersion'] = $plugin_version;
	}*/

	// Send new order to server and get back the order info from the server
	$response  = callAPI('POST', TAAGER_URL . '/orders', json_encode($order_data));
	$order_num = $response->data->orderNum;
	$ta_order_id = $response->data->orderID;
	$ta_order_status = $response->data->status;

	// Save meta data for order number
	update_post_meta($wp_order_id, '_ta_order_num', $order_num);
	update_post_meta($wp_order_id, 'taager_order_id', $ta_order_id);
	update_post_meta($wp_order_id, 'taager_order_status', $ta_order_status);
}

/**
 * Update order status as 'pending' when created new order
 */
add_action('woocommerce_thankyou', 'ta_update_order_status');
function ta_update_order_status($order_id)
{
	if (!has_taager_product_in_order($order_id)) {
		return;
	}

	unset($_SESSION['taager_shipping_province']);
	delete_option('taager_shipping_province');

	$order = wc_get_order($order_id);
	foreach ($order->get_items() as $item_id => $item) {
		$p_id = $item->get_product_id();
		if (get_post_meta($p_id, 'taager_product', true)) {
			return;
		}
	}

	// Get order number from meta data
	$order_num = get_post_meta($order_id, '_ta_order_num', true);

	// Query params for GET order API
	$query_params = array(
		'order_num' => $order_num,
	);

	// Get existing order from server
	$response     = callAPI('GET', TAAGER_URL . '/orders', $query_params);
	$order_status = ($response->data->status);

	if ($order_status == 'order_received') {
		// Update order status as 'pending'
		$order->update_status('pending');
	}
}

/**
 * Send order status cancelled to backend via PUT order API
 */
add_action('woocommerce_order_status_changed', 'ta_cancel_order');
function ta_cancel_order($order_id)
{
	if (!has_taager_product_in_order($order_id)) {
		return;
	}
	$order = wc_get_order($order_id);

	// Get updated order status
	$order_status = $order->get_status();

	if ($order_status == 'cancelled') {
		// Get order number from meta data
		$order_num = get_post_meta($order_id, '_ta_order_num', true);

		// Cancel order in the server
		callAPI('PUT', TAAGER_URL . '/orders/cancel/' . intval($order_num));
	}
}

/**
 * restricts addition of taager product in cart
 * if already non taager prodect added and vice versa
 */
add_filter('woocommerce_add_to_cart_validation', 'ta_cart_validation', 10, 3);
function ta_cart_validation($passed, $product_id, $quantity)
{

	global $woocommerce;
	$items = $woocommerce->cart->get_cart();

	foreach ($items as $item => $values) {
		$cart_taager_product = get_post_meta($values['data']->get_id(), 'taager_product', true) ? get_post_meta($values['data']->get_id(), 'taager_product', true) : 0;
		break;
	}
	$current_taager_product = (get_post_meta($product_id, 'taager_product', true)) ? get_post_meta($product_id, 'taager_product', true) : 0;

	if ($items && ($cart_taager_product != $current_taager_product)) {
		//$message = ($current_taager_product) ? __( 'You added taager product.', 'woocommerce' ) : __( 'You added non-taager product.', 'woocommerce' );
		wc_add_notice(__('You can not add this product at the moment.', 'woocommerce'), 'error');
		$passed = false;
	}
	return $passed;
}

/**
 * returns the respective shipping charge according to the taager province
 */

function taager_province_shipping($province)
{
	$ta_selected_country = get_option('ta_selected_country');
	if ($ta_selected_country == '') {
		$ta_selected_country = "EGY";
	}
	$old_provinces = file_get_contents('https://dev.myshahbandr.com/wp-content/provinces.json');
	if ($old_provinces) {
		$old_provinces = json_decode($old_provinces);
	}
	if (isset($old_provinces->{$ta_selected_country})) {
		$old_provinces = $old_provinces->{$ta_selected_country};
	}

	return intval($old_provinces->{$province}->shipping_revenue);
}

/**
 * checks if cart has taager product or not
 * checks first cart item
 */
function has_taager_product_in_cart()
{
	global $woocommerce;
	$items = $woocommerce->cart->get_cart();
	if (!$items) {
		return false;
	}
	foreach ($items as $item => $values) {
		$cart_taager_product = get_post_meta($values['data']->get_id(), 'taager_product', true) ? true : false;
		break;
	}
	return $cart_taager_product;
}

function has_taager_product_in_order($order_id)
{
	$order = wc_get_order($order_id);
	foreach ($order->get_items() as $item_id => $item) {
		$p_id = $item->get_product_id();
		return get_post_meta($p_id, 'taager_product', true) ? true : false;
		exit;
	}
}
add_filter('woocommerce_package_rates', 'hide_other_shipping_when_free_is_available', 100, 2);

function hide_other_shipping_when_free_is_available($rates, $package)
{

	$free = array();
	foreach ($rates as $rate_id => $rate) {
		if ('free_shipping' === $rate->method_id) {
			$free[$rate_id] = $rate;
			break;
		}
	}
	return !empty($free) ? $free : $rates;
}
/**
 * adding shipping cost according to taager province
 */
add_action('woocommerce_cart_calculate_fees', 'ta_add_shipping_fee_by_taager');
function ta_add_shipping_fee_by_taager()
{
	global $wpdb;
	if ((is_admin() && !defined('DOING_AJAX')) || (!has_taager_product_in_cart() || is_cart())) {
		return;
	}

	$ta__check_shipping = null;
	$ta_product_price_total = '';
	foreach (WC()->cart->get_cart() as $cart_item) {
		$product_id         = $cart_item['product_id'];
		$ta__check_shipping = get_post_meta($product_id, 'ta__shipping_charge', true);
		$ta__cs[]           = $ta__check_shipping;

		$ta_product_price = get_post_meta($product_id, '_ta_product_price', true);
		$ta_product_price_with_quantity = $ta_product_price * $cart_item['quantity'];
		if ($ta_product_price != '') {
			$ta_product_price_total = $ta_product_price_total + $ta_product_price_with_quantity;
		}
	}
	// if (get_option('taager_enable_free_shipping') == 'yes') {
	// 	$lang = get_bloginfo("language");
	// 	if ($lang == "ar") {
	// 		$shipping_text = "الشحن";
	// 	} else {
	// 		$shipping_text = "Shipping";
	// 	}

	// 	WC()->cart->add_fee($shipping_text, 0);

	// 	return;
	// }
	if (in_array('yes', $ta__cs)) {
		$ta__check_shipping = 'yes';
	} else {
		$ta__check_shipping = 'no';
	}
	//echo $ta__check_shipping;

	$option_table_nm = $wpdb->base_prefix . 'options';
	$taager_enable_network_shipping_module_data = $wpdb->get_row("SELECT * FROM `" . $option_table_nm . "` WHERE `option_name` = 'taager_enable_network_shipping_module' LIMIT 1 ");
	$taager_enable_network_shipping_module = $taager_enable_network_shipping_module_data->option_value;

	if ($taager_enable_network_shipping_module == 1) {

		//$taager_free_shipping_by = get_option( 'taager_free_shipping_by' );
		$taager_free_shipping_by_data = $wpdb->get_row("SELECT * FROM `" . $option_table_nm . "` WHERE `option_name` = 'taager_free_shipping_by' LIMIT 1 ");
		$taager_free_shipping_by = $taager_free_shipping_by_data->option_value;

		if ($taager_free_shipping_by == 0) {
			//$free_shipping_price = get_option( 'taager_network_free_shipping_price' );
			$network_free_shipping_price_data = $wpdb->get_row("SELECT * FROM `" . $option_table_nm . "` WHERE `option_name` = 'taager_network_free_shipping_price' LIMIT 1 ");
			$network_free_shipping_price = $network_free_shipping_price_data->option_value;
			if ($network_free_shipping_price != '') {
				$free_shipping_price = $network_free_shipping_price;
			} else {
				$free_shipping_price = 0;
			}
		} else {

			$enable_free_shipping = get_option('taager_enable_free_shipping');
			if ($enable_free_shipping == 1) {

				$taager_vendor_force_shipping_price_data = $wpdb->get_row("SELECT * FROM `" . $option_table_nm . "` WHERE `option_name` = 'taager_vendor_force_shipping_price' LIMIT 1 ");
				$taager_vendor_force_shipping_price = $taager_vendor_force_shipping_price_data->option_value;
				if ($taager_vendor_force_shipping_price != '') {
					$free_shipping_price = $taager_vendor_force_shipping_price;
				} else {
					$vendor_free_shipping_price = get_option('taager_free_shipping_price');
					if ($vendor_free_shipping_price != '') {
						$free_shipping_price = $vendor_free_shipping_price;
					} else {
						$free_shipping_price = 0;
					}
				}
			} else {
				$free_shipping_price = 0;
			}
		}
	}

	$lang = get_bloginfo("language");
	if ($lang == "ar") {
		$shipping_text = "الشحن";
	} else {
		$shipping_text = "Shipping";
	}

	if ($taager_enable_network_shipping_module == 1 && $ta_product_price_total >= $free_shipping_price && $free_shipping_price != 0) {
		WC()->cart->add_fee($shipping_text, 0);
	} else {
		if ($ta__check_shipping == 'no') {
			$shipping_address = '';
			if (isset($_POST['city'])) {
				$shipping_address = $_POST['city'];
			}

			if (isset($_POST['state'])) {
				$shipping_address = $_POST['state'];
			}

			// if (isset($_POST['city'])) {
			//     //ta__shipping_charge
			//     $shipping_charge                      = taager_province_shipping($_POST['city']);
			//     $_SESSION['taager_shipping_province'] = $_POST['city'];
			//     WC()->cart->add_fee(__('Shipping', 'taagerapi'), $shipping_charge);
			// } elseif (isset($_SESSION['taager_shipping_province'])) {
			//     $shipping_charge = taager_province_shipping($_SESSION['taager_shipping_province']);
			//     WC()->cart->add_fee(__('Shipping', 'taagerapi'), $shipping_charge);
			// }

			if ($shipping_address) {
				//ta__shipping_charge
				$shipping_charge                      = taager_province_shipping($shipping_address);
				$_SESSION['taager_shipping_province'] = $shipping_address;
				WC()->cart->add_fee($shipping_text, $shipping_charge);
			} elseif (isset($_SESSION['taager_shipping_province'])) {
				$shipping_charge = taager_province_shipping($_SESSION['taager_shipping_province']);
				WC()->cart->add_fee($shipping_text, $shipping_charge);
			}
		}
	}
}

/**
 * initialize session
 *
 */
function ta_session_start()
{
	if (!session_id()) {
		session_start();
	}
}

add_action('init', 'ta_session_start');


/**
 * validation for product price in compared to taager product price
 */
add_action('save_post', 'ta_product_save_post', 10, 2);
function ta_product_save_post($prod_id, $prod)
{
	if ($_SERVER['REQUEST_METHOD'] != 'POST') {
		return;
	}
	$product = wc_get_product($prod_id);

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || $prod->post_type != 'product') {
		return;
	}

	$errors = array();

	$ta__check_shipping = get_post_meta($_POST['post_ID'], 'ta__shipping_charge', true);
	if ($ta__check_shipping == 'yes') {

		$taager_max_shipping = 0;
		$ta_selected_country = get_option('ta_selected_country');
		if ($ta_selected_country == '') {
			$ta_selected_country = "EGY";
		}


		$old_provinces = file_get_contents('https://dev.myshahbandr.com/wp-content/provinces.json');

		if ($old_provinces) {
			$old_provinces = json_decode($old_provinces);
			if (isset($old_provinces->{$ta_selected_country})) {
				$old_provinces = $old_provinces->{$ta_selected_country};
			}
			foreach ($old_provinces as  $prov) {
				if ($taager_max_shipping < $prov->shipping_revenue) {
					$taager_max_shipping = $prov->shipping_revenue;
				}
			}
		}
		if ($product->is_type('variable')) {

			$i = 0;
			foreach ($_POST['variable_post_id'] as $variable_post_id) {

				$taager_profit = get_post_meta($variable_post_id, '_ta_product_profit', true);
				$is_taager_product = get_post_meta($variable_post_id, 'taager_product', true);
				$taager_price      = get_post_meta($variable_post_id, '_ta_product_price', true);
				$ta_new_min_price  = $taager_price - $taager_profit;
				$ta_new_min_price  = $ta_new_min_price + $taager_max_shipping;
				$ta_currency       = get_woocommerce_currency_symbol();
				$prev_price        = get_post_meta($variable_post_id, '_price', true);
				$price_check = $_POST['variable_sale_price'][$i] != '' ? $_POST['variable_sale_price'][$i] : $_POST['variable_regular_price'][$i];
				if (get_post_meta($variable_post_id, 'taager_product', true) && ($price_check < $ta_new_min_price)) {
					$errors['ta-price'] = 'Product price should be atleast ' . wc_price($ta_new_min_price);
					update_post_meta($variable_post_id, '_price', $prev_price);
				}
				$i++;
			}
		} else {
			$taager_profit       = get_post_meta($_POST['post_ID'], '_ta_product_profit', true);

			$is_taager_product = get_post_meta($_POST['post_ID'], 'taager_product', true);
			$taager_price      = get_post_meta($_POST['post_ID'], '_ta_product_price', true);
			$ta_new_min_price  = $taager_price - $taager_profit;
			$ta_new_min_price  = $ta_new_min_price + $taager_max_shipping;
			$ta_currency       = get_woocommerce_currency_symbol();
			$prev_price        = get_post_meta($_POST['post_ID'], '_price', true);
			$price_check 	   = $_POST['_sale_price'] != '' ? $_POST['_sale_price'] : $_POST['_regular_price'];
			if (get_post_meta($_POST['post_ID'], 'taager_product', true) && ($price_check < $ta_new_min_price)) {
				$errors['ta-price'] = 'Product price should be atleast ' . wc_price($ta_new_min_price);
				update_post_meta($_POST['post_ID'], '_price', $prev_price);
			}
		}
	} else {

		if ($product->is_type('variable')) {

			$i = 0;
			foreach ($_POST['variable_post_id'] as $variable_post_id) {

				$price_check = $_POST['variable_sale_price'][$i] != '' ? $_POST['variable_sale_price'][$i] : $_POST['variable_regular_price'][$i];
				if (get_post_meta($variable_post_id, 'taager_product', true) && ($price_check < get_post_meta($variable_post_id, '_ta_product_price', true))) {
					$prev_price         = get_post_meta($variable_post_id, '_price', true);
					$errors['ta-price'] = 'Product price should be atleast ' . wc_price(get_post_meta($variable_post_id, '_ta_product_price', true));
					update_post_meta($variable_post_id, '_price', $prev_price);
				}
				$i++;
			}
		} else {

			if (get_post_meta($_POST['post_ID'], 'taager_product', true) && ($_POST['_regular_price'] < get_post_meta($_POST['post_ID'], '_ta_product_price', true))) {
				$prev_price         = get_post_meta($_POST['post_ID'], '_regular_price', true);
				$errors['ta-price'] = 'Product price should be atleast ' . wc_price(get_post_meta($_POST['post_ID'], '_ta_product_price', true));
				update_post_meta($_POST['post_ID'], '_regular_price', $prev_price);
			}
		}
	}

	if (!empty($errors)) {
		remove_action('save_post', 'ta_product_save_post');

		$_SESSION['my_admin_notices'] = $errors;
		$prod->post_status            = 'draft';

		wp_update_post($prod);

		add_action('save_post', 'ta_product_save_post');

		add_filter('redirect_post_location', 'ta_product_redirect_filter');
	}
}

function ta_product_redirect_filter($location)
{
	$location = remove_query_arg('message', $location);

	$location = add_query_arg('ta-price', 'error', $location);

	return $location;
}

// Add new admin message
add_action('admin_notices', 'ta_error_admin_message', 99);

function ta_error_admin_message()
{
	if (isset($_GET['ta-price']) && $_GET['ta-price'] == 'error') {
		// lets get the errors from the option product_errors
		$errors = $_SESSION['my_admin_notices'];

		unset($_SESSION['my_admin_notices']);

		$display = '<div id="notice" class="error"><ul>';

		// Because we are storing as an array we should loop through them
		foreach ($errors as $error) {
			$display .= '<li>' . $error . '</li>';
		}

		$display .= '</ul></div>';

		// finally echo out our display
		echo $display;

		// add some jQuery
?>
		<script>
			jQuery(function($) {
				$("#_regular_price").css({
					"border": "1px solid red"
				})
			});
		</script>
<?php
	}
}

//add three extra fields in order dashboard
add_filter('manage_edit-shop_order_columns', '_ta_order_admin_dashboard_column');
function _ta_order_admin_dashboard_column($columns)
{
	$new_columns = (is_array($columns)) ? $columns : array();
	// unset( $new_columns[ 'order_actions' ] );

	//edit this for your column(s)
	//all of your columns will be added before the actions column
	$new_columns['order_id_']                 = 'Order ID';
	$new_columns['order_status_']             = 'Order Status';
	//$new_columns['suspended_reason']          = 'Suspended Reason';
	//$new_columns['customer_rejected_reason']  = 'Customer Rejected Reason';
	//$new_columns['delivery_suspended_reason'] = 'Delivery Suspended Reason';

	//stop editing
	$new_columns['order_actions'] = $columns['order_actions'];
	return $new_columns;
}
add_filter('woocommerce_shipping_free_shipping_is_available', function ($is_available) {

	// if (get_option('taager_enable_free_shipping') == 'yes') {
	// 	$is_available = true;
	// }

	return $is_available;
}, 99);

add_filter('woocommerce_available_shipping_methods', function ($available_methods) {

	foreach ($available_methods as $key => $method) {
		if ($method->method_id == 'free_shipping') {
			$available_methods = array();
			$available_methods['free_shipping:1'] = $method;
			break;
		}
	}
	return $available_methods;
}, 99);


add_action('manage_shop_order_posts_custom_column', '_ta_order_admin_dashboard_column_values');
function _ta_order_admin_dashboard_column_values($column)
{
	global $post;

	switch ($column) {
		case 'order_id_':
			echo get_post_meta($post->ID, 'taager_order_id', true);
			break;
		case 'order_status_':
			echo get_post_meta($post->ID, 'taager_order_status', true);
			break;
		case 'suspended_reason':
			echo get_post_meta($post->ID, 'taager_suspended_reason', true);
			break;
		case 'customer_rejected_reason':
			echo get_post_meta($post->ID, 'taager_customer_rejected_reason', true);
			break;
		case 'delivery_suspended_reason':
			echo get_post_meta($post->ID, 'taager_delivery_suspended_reason', true);
			break;
	}
}

//delete taxonomy hook
//add_action('delete_term_taxonomy', 'cs_delete_term_taxonomy');
function cs_delete_term_taxonomy()
{

	if (!is_network_admin() && ($_POST['taxonomy'] == 'product_cat')) {
		$term_id = $_POST['tag_ID'];
		$term_name = get_term($term_id)->name;

		$categories = callAPI('GET', TAAGER_URL . '/category/');
		//echo "<pre>"; print_r($categories); exit();

		for ($i = 0; $i < count($categories->data); $i++) {
			$category = $categories->data[$i];

			if ($category->text == $term_name) {
				echo "You can not delete this category";
				break;
				exit();
			}
		}
	}
}

add_filter('product_cat_row_actions', 'cs_product_cat_row_actions', 10, 2);
function cs_product_cat_row_actions($actions, $term)
{

	if (!is_network_admin()) {
		$taager_categories_names = get_option('taager_categories_name_lits');
		if (in_array($term->name, $taager_categories_names)) {
			unset($actions['edit']);
			unset($actions['inline hide-if-no-js']);
			unset($actions['delete']);
		}
	}

	return $actions;
}

//add meta for taager category 
//add_action('admin_init', 'cs_update_prod_cat_term_meta');
function cs_update_prod_cat_term_meta()
{

	global $wpdb;

	$tagger_category = array();
	$categories = callAPI('GET', TAAGER_URL . '/category/');
	for ($i = 0; $i < count($categories->data); $i++) {
		$tagger_category[] = $categories->data[$i]->text;
	}

	$db_prefix = $wpdb->prefix;

	$cat_list = $wpdb->get_results(
		"SELECT * FROM
				" . $db_prefix . "terms
			LEFT JOIN
				" . $db_prefix . "term_taxonomy ON
					" . $db_prefix . "terms.term_id = " . $db_prefix . "term_taxonomy.term_id
			WHERE
				" . $db_prefix . "term_taxonomy.taxonomy = 'product_cat'"
	);

	foreach ($cat_list as $cat) {
		if (in_array($cat->name, $tagger_category) &&  $cat->slug != 'uncategorized') {

			$check_termmeta = $wpdb->get_col(
				"SELECT meta_id FROM `" . $db_prefix . "termmeta`
					WHERE `term_id` = $cat->term_id
					AND `meta_key` = 'category_type' 
					AND `meta_value` = 'taager'"
			);

			if (!$check_termmeta) {
				$term_id = $cat->term_id;
				$meta_key = 'category_type';
				$meta_value = 'taager';

				$term_sql = $wpdb->prepare("INSERT INTO `" . $db_prefix . "termmeta` (`term_id`, `meta_key`, `meta_value`) values (%d, %s, %s)", $term_id, $meta_key, $meta_value);
				$wpdb->query($term_sql);
			}
		}
	}
}

// Add How To Use Meta box to admin products pages
add_action('add_meta_boxes', 'create_product_technical_specs_meta_box', 20);
function create_product_technical_specs_meta_box()
{
	add_meta_box(
		'how_to_use_product_meta_box',
		__('How To Use', 'woocommerce'),
		'how_to_use_content_meta_box',
		'product',
		'normal',
		'default'
	);
}

// How To Use metabox content in admin product pages
function how_to_use_content_meta_box($post)
{
	$product = wc_get_product($post->ID);
	$content = $product->get_meta('_ta_how_to_use');

	echo '<div class="product_ta_how_to_use">';

	wp_editor($content, '_ta_how_to_use', ['textarea_rows' => 10]);

	echo '</div>';
}

// Save How To Use field value from product admin pages
add_action('woocommerce_admin_process_product_object', 'save_product_ta_how_to_use_field', 10, 1);
function save_product_ta_how_to_use_field($product)
{
	if (isset($_POST['_ta_how_to_use']))
		$product->update_meta_data('_ta_how_to_use', wp_kses_post($_POST['_ta_how_to_use']));
}

// Add "How To Use" product tab
add_filter('woocommerce_product_tabs', 'cs_add_ta_how_to_use_product_tab', 10, 1);
function cs_add_ta_how_to_use_product_tab($tabs)
{

	global $product;
	$lang = get_bloginfo("language");
	if ($lang == "ar") {
		$how_to_text = "طريقة الاستخدام";
	} else {
		$how_to_text = "How To Use";
	}
	if ($product->get_meta('_ta_how_to_use')) {
		$tabs['test_tab'] = array(
			'title'         => __($how_to_text, 'woocommerce'),
			'priority'      => 20,
			'callback'      => 'cs_display_ta_how_to_use_product_tab_content'

		);
	}

	// if ($product->is_type('variable')) {

	if ($product->get_meta('_ta_youtube_url')) {
		if ($lang == "ar") {
			$video_tab_text = "فيديو المنتج";
		} else {
			$video_tab_text = "Videos";
		}
		$tabs['product_videos'] = array(
			'title'         => __($video_tab_text, 'woocommerce'),
			'priority'      => 25,
			'callback'      => 'cs_display_ta_product_video_tab_content'

		);
	}

	//	$tabs['description']['callback'] = 'cs_custom_description_callback';
	// }

	return $tabs;
}

// Display "How To Use" content tab
function cs_display_ta_how_to_use_product_tab_content()
{
	global $product;
	echo '<div class="wrapper-how_to_use">' . $product->get_meta('_ta_how_to_use') . '</div>';
}

// Display "Speceification" content tab
function cs_display_ta_product_short_description_tab_content()
{
	global $product;
	echo '<div class="wrapper-short_description">' . $product->get_short_description() . '</div>';
}

// Display "description/specifications" content tab
function cs_custom_description_callback()
{
	global $product;
	$p_id = $product->get_id();
	$custom_specification_data = get_post_meta($p_id, "_ta_product_specifications", true);
	$custom_specifications = @unserialize($custom_specification_data);
	if ($custom_specifications !== false) {
		echo "<ul class='specification_ul'>";
		foreach ($custom_specifications as $custom_specification) {
			echo "<li>" . $custom_specification . "</li>";
		}
		echo "</ul>";
	} else {
		echo $custom_specification_data;
	}
}

// Display "description/specifications" content tab
function cs_display_ta_product_video_tab_content()
{

	global $product;
	$custom_specification_data = $product->get_meta('_ta_youtube_url');
	$custom_specification_data = str_replace('/watch?v=', '/embed/', $custom_specification_data);
	echo '<div class="cs_video_tab_content"><iframe width="100%" height="600" src="' . $custom_specification_data . '"></iframe></div>';
}

//shipping update
function taager_shipping_update()
{

	$update_date = date("Y-m-d H:i:s");
	update_option('taager_last_update_provinces', $update_date);

	echo $shipping_update_time = date('d/m/Y h:i:s A', strtotime($update_date));
	die;
}
add_action('wp_ajax_taager_shipping_update', 'taager_shipping_update');

/* Start add phone number field on checkout page */
add_filter('woocommerce_checkout_fields', 'cs_custom_override_checkout_fields', 99);
function cs_custom_override_checkout_fields($fields)
{

	if (get_current_blog_id() == 3550) {
		$phone2_label = 'الجوال البديل';
	} else {
		$phone2_label = 'هاتف بديل';
	}
	$fields['billing']['billing_phone2'] = array(
		'type' => 'tel',
		'label'     => __($phone2_label, 'woocommerce'),
		//'placeholder'   => _x('رقم هاتف بديل', 'placeholder', 'woocommerce'),
		'required'  => false,
		'class'     => array('form-row-wide'),
		'clear'     => true
	);

	return $fields;
}

add_action('woocommerce_admin_order_data_after_billing_address', 'cs_custom_checkout_field_display_admin_order_meta', 10, 1);
function cs_custom_checkout_field_display_admin_order_meta($order)
{
	$order_id = $order->get_id();
	if (get_post_meta($order_id, '_billing_phone2', true)) echo '<p><strong>' . __('Phone 2') . ':</strong>' . get_post_meta($order_id, '_billing_phone2', true) . '</p>';
}

add_action('woocommerce_email_after_order_table', 'cs_show_new_checkout_field_emails', 20, 4);
function cs_show_new_checkout_field_emails($order, $sent_to_admin, $plain_text, $email)
{
	if (get_post_meta($order->get_id(), '_billing_phone2', true)) echo '<p><strong>' . __('رقم هاتف بديل') . ':</strong>' . get_post_meta($order->get_id(), '_billing_phone2', true) . '</p>';
}

add_filter('woocommerce_checkout_fields', 'cs_checkout_fields_custom_attributes', 999);
function cs_checkout_fields_custom_attributes($fields)
{

	$ta_selected_country = get_option('ta_selected_country');
	if ($ta_selected_country === 'SAU') {
		$phone_number_digits_count = 10;
	} else {
		$phone_number_digits_count = 11;
	}


	$fields['billing']['billing_phone']['custom_attributes']['minlength'] = $phone_number_digits_count;
	$fields['billing']['billing_phone']['maxlength'] = $phone_number_digits_count;
	$fields['billing']['billing_phone2']['custom_attributes']['minlength'] = $phone_number_digits_count;
	$fields['billing']['billing_phone2']['maxlength'] = $phone_number_digits_count;

	$fields['billing']['billing_phone']['priority'] = 90;
	$fields['billing']['billing_phone2']['priority'] = 100;
	//$fields['billing']['billing_email']['priority'] = 120;

	//set phoneNumber field lable and set as required

	if (get_current_blog_id() == 3550) {
		$phone1_label = 'الجوال ( بدون الصفر الذي في البداية مثال: 512345678 )';
	} else {
		$phone1_label = 'الهاتف';
	}

	$fields['billing']['billing_phone']['label'] = $phone1_label;
	$fields['billing']['billing_phone']['required'] = true;

	return $fields;
}

/* Show error on submitting the checkout form with wrong number of digits for phonenumber */
add_action('woocommerce_checkout_process', 'cs_checkout_fields_custom_validation');
function cs_checkout_fields_custom_validation()
{

	$ta_selected_country = get_option('ta_selected_country');

	if ($ta_selected_country === 'SAU') {
		$phone_number_digits_count = 10;
	} else {
		$phone_number_digits_count = 11;
	}

	if ($phone_number_digits_count <= 10) {
		$validation_message_digits_count = $phone_number_digits_count . ' أرقام فقط';
	} else {
		$validation_message_digits_count = $phone_number_digits_count . ' رقم فقط';
	}

	if (isset($_POST['billing_phone'])) {
		if (!(preg_match('/^[0-9]{' . $phone_number_digits_count . '}$/D', $_POST['billing_phone']))) {
			wc_add_notice(' رقم الهاتف يجب ان يحتوى على' . $validation_message_digits_count, 'error');
		}
	}
	if (isset($_POST['billing_phone2']) && $_POST['billing_phone2'] != "") {
		if (!(preg_match('/^[0-9]{' . $phone_number_digits_count . '}$/D', $_POST['billing_phone2']))) {
			wc_add_notice(' رقم الهاتف البديل يجب ان يحتوى على' . $validation_message_digits_count, 'error');
		}
	}
}

/* End add phone number field on checkout page */

function taager_frontend_css_js_equeue()
{
	wp_enqueue_script('taager_custom_script', plugin_dir_url(__DIR__) . '/assets/js/taager_custom_script.js', array('jquery'), rand(), true);

	wp_enqueue_style('taager_custom_script', plugin_dir_url(__DIR__) . '/assets/css/taager_custom_style.css', array(), rand());
}
add_action('wp_enqueue_scripts', 'taager_frontend_css_js_equeue', 15);

//Free shipping text
add_filter('woocommerce_cart_totals_fee_html', 'custom_woocommerce_cart_totals_fee_html', 10, 2);
function custom_woocommerce_cart_totals_fee_html($cart_totals_fee_html, $fee)
{

	$fee_total = $fee->total;
	if ($fee_total == 0) {
		$cart_totals_fee_html = "شحن مجانى";
	}
	return $cart_totals_fee_html;
}

//display taager price in product edit page
add_action('woocommerce_product_options_general_product_data', 'ta_display_taager_price');
function ta_display_taager_price()
{

	if (isset($_GET['post'])) {
		$product_id = intval($_GET['post']);
		$product = wc_get_product($product_id);
		if ($product->is_type('variable')) {
			$children_ids = $product->get_children();
			$product_id = $children_ids[0];
		}
		$is_taager_product = get_post_meta($product_id, 'taager_product', true);
		if (!empty($is_taager_product)) {

			$product_id = get_post_meta($product_id, '_sku', true);
			$pid_param = ($product_id) ? '&prod_ids=' . urlencode($product_id) : '';
			$product_data = callAPI('GET', TAAGER_URL . '/product/?taager_import=1' . $pid_param);
			if (!empty($product_data->data)) {
				$taager_price = intval($product_data->data[0]->productPrice);
			} else {
				$taager_price = 0;
			}

			$lang = get_bloginfo("language");
			if ($lang == "ar") {
				$text_input_lable = 'السعر الحالى للمنتج فى منصة تاجر';
			} else {
				$text_input_lable = 'Taager Current Product Price';
			}

			if (!empty($product_data->data)) {
				woocommerce_wp_text_input(
					array(
						'id'          => '_cs_taager_price',
						//'label'       => 'Taager Product Price',
						'label' 	  => __($text_input_lable, 'woocommerce'),
						'description' => 'This is the amount of a taager price.',
						'desc_tip'    => 'true',
						'class'    	  => 'cs_disable_taager_field',
						'value'		  => $taager_price,
					)
				);
			}
		}
	}
}

function searchForId($id, $array)
{
	foreach ($array as $key => $val) {
		if ($val->location === $id) {
			return $key;
		}
	}
	return null;
}

/* Cron set */
// add_action('wp', 'custom_taager_schedule_events');
// function custom_taager_schedule_events()
// {
// 	// Schedule an action if it's not already scheduled
// 	if (!wp_next_scheduled('ta_hourly_update_hook')) {
// 		wp_schedule_event(time(), 'every_one_hour', 'ta_hourly_update_hook');
// 	}
// }

//change attribute text
add_filter('woocommerce_attribute_label', 'cs_woocommerce_attribute_label', 99, 3);
function cs_woocommerce_attribute_label($label, $name, $product)
{

	if (get_locale() == 'ar') {
		if ($label == 'color') {
			$label = 'اللون';
		}
		if ($label == 'size') {
			$label = 'المقاس';
		}
	}
	return $label;
}
