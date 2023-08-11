<?php

defined('ABSPATH') || exit;

/* 
  Adding more options for wp_schedule_event parameter $recurrence
  The values supported by default are ‘hourly’, ‘twicedaily’, ‘daily’, and ‘weekly’
*/
add_filter('cron_schedules', 'add_custom_recurrences');
function add_custom_recurrences($schedules)
{
	// $schedules['every_five_minutes'] = array(
	// 	'interval' => 300,
	// 	'display'  => __( 'Every 5 Minutes', 'taager-api' ),
	// );
	// $schedules['every_ten_minutes'] = array(
	// 	'interval' => 600,
	// 	'display'  => __( 'Every 10 Minutes', 'taager-api' ),
	// );
	$schedules['every_one_hour']    = array(
		'interval' => HOUR_IN_SECONDS,
		'display'  => __('Every One Hour', 'taager-api'),
	);
	return $schedules;
}
add_action('init', function () {
	// hourly_action_scheduler();
	if (!wp_next_scheduled('ta_hourly_update_hook')) {
		wp_schedule_event(time(), 'every_one_hour', 'ta_hourly_update_hook');
	}
});
add_action('ta_hourly_update_hook', 'hourly_action_scheduler');
function  hourly_action_scheduler()
{
	try {
		if (file_exists(WP_CONTENT_DIR . '/ta.db')) {
			unlink(WP_CONTENT_DIR . '/ta.db');
			new SQLite3(WP_CONTENT_DIR . '/ta.db');
		} else {
			new SQLite3(WP_CONTENT_DIR . '/ta.db');
		}
		$db = new PDO("sqlite:" . WP_CONTENT_DIR . '/ta.db');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (Exception $e) {
		echo "Unable to connect to database";
		echo $e->getMessage();
		exit;
	}
	hourly_action_scheduler_taager_order_check();
	hourly_action_scheduler_taager_product_update_db($db);
	hourly_action_scheduler_taager_product_check($db);
}

/**
 * scheduled function that syncs taager products order hourly.
 *
 * @return void
 */
function hourly_action_scheduler_taager_order_check()
{

	global $wpdb;

	$result = $wpdb->get_results(" SELECT * FROM $wpdb->posts WHERE post_type ='shop_order' and 
									ID in(SELECT post_id from $wpdb->postmeta where meta_key ='_ta_order_num' and meta_value IS NOT NULL) 
									and  post_status not in('wc-completed','wc-failed', 'wc-refunded', 'wc-cancelled') 
									and  DATE_FORMAT(post_date, '%Y-%m-%d') >= '2022-05-01'; 
									");
	$all_ta_status = [
		'confirmed' => 'processing',
		'pending_shipping_company' => 'processing',
		'delivery_in_progress' => 'processing',
		'replacement_in_progress' => 'processing',

		'refund_in_progress' => 'refunded',
		'return-verified' => 'refunded',
		'return_verified' => 'refunded',
		'return_in_progress' => 'refunded',
		'refund_verified' => 'refunded',

		'taager_cancelled' => 'cancelled',
		'customer_rejected' => 'cancelled',
		'suspended' => 'cancelled',
		'delivery_suspended' => 'cancelled',
		'customer_refused' => 'cancelled',
		'delivery_cancelled' => 'cancelled',
		'failed' => 'cancelled',
		'cancelled' => 'cancelled',

		'replacement_verified' => 'completed',
		'delivered' => 'completed',
		'pending' => 'on-hold'
	];
	foreach ($result as $key => $_order) {
		$wp_order_id     =  $_order->ID;

		$ta_order_no  = get_post_meta($wp_order_id, '_ta_order_num', true);

		$query_params = array(
			'order_num' => $ta_order_no,
		);
		$response     = callAPI('GET', 'https://woocommerce-api-gw.api.taager.com/orders', $query_params);


		if (isset($response->data) and isset($response->data->orderID)) {
			$ta_order_id                = (isset($response->data->orderID)) ? $response->data->orderID : '';
			$ta_status                  = (isset($response->data->status)) ? $response->data->status : '';
			$ta_suspendedReason         = (isset($response->data->suspendedReason)) ? $response->data->suspendedReason : '';
			$ta_customerRejectedReason  = (isset($response->data->customerRejectedReason)) ? $response->data->customerRejectedReason : '';
			$ta_deliverySuspendedReason = (isset($response->data->deliverySuspendedReason)) ? $response->data->deliverySuspendedReason : '';

			update_post_meta($wp_order_id, 'taager_order_id', $ta_order_id);
			update_post_meta($wp_order_id, 'taager_order_status', $ta_status);
			update_post_meta($wp_order_id, 'taager_suspended_reason', $ta_suspendedReason);
			update_post_meta($wp_order_id, 'taager_customer_rejected_reason', $ta_customerRejectedReason);
			update_post_meta($wp_order_id, 'taager_delivery_suspended_reason', $ta_deliverySuspendedReason);

			if (isset($all_ta_status[$ta_status])) {
				$order_data = wc_get_order($wp_order_id);
				$order_data->update_status($all_ta_status[$ta_status], '', true);
			}
		}
	}
}

/**
 * scheduled function that syncs imported taager products hourly
 *
 * @return void
 */
function hourly_action_scheduler_taager_product_update_db($db)
{

	$db->exec("DROP TABLE IF EXISTS products;");
	$db->exec("CREATE TABLE IF NOT EXISTS products(
														id INTEGER PRIMARY KEY, 
														is_min INT,
														additionalMedia TEXT,
														attributes TEXT,
														categoryId TEXT , 
														extraImage1 TEXT,
														extraImage2 TEXT,
														extraImage3 TEXT,
														extraImage4 TEXT,
														extraImage5 TEXT,
														extraImage6 TEXT,
														howToUse TEXT,
														isExpired TEXT,
														isProductAvailableToSell TEXT,
														prodID TEXT,
														productAvailability TEXT,
														productDescription TEXT,
														productName TEXT,
														productPicture TEXT,
														productPrice TEXT,
														productProfit TEXT,
														specifications TEXT
		)");
	$api_products = callAPI('GET', TAAGER_URL . '/product/?taager_import=1');
	$products_name = [];
	try {

		$db->beginTransaction();
		$stmt = $db->prepare("INSERT INTO products('is_min','additionalMedia', 'attributes', 'categoryId', 'extraImage1', 'extraImage2', 'extraImage3', 'extraImage4','extraImage5','extraImage6','isExpired','isProductAvailableToSell','prodID','productAvailability','productDescription','productName','productPicture','productPrice','productProfit','specifications') VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

		foreach (array_chunk($api_products->data, 100) as $key => $data) {
			foreach ($data as $key => $prod) {
				$additionalMedia = json_encode($prod->additionalMedia);
				$attributes = json_encode($prod->attributes);
				try {
					$is_min = 0;
					if ($prod->isProductAvailableToSell and !in_array($prod->productName, $products_name)) {
						$products_name[] = $prod->productName;
						$is_min = 1;
					}
					$stmt->execute([$is_min, $additionalMedia, $attributes, $prod->categoryId, $prod->extraImage1, $prod->extraImage2, $prod->extraImage3, $prod->extraImage4, $prod->extraImage5, $prod->extraImage6, $prod->isExpired, $prod->isProductAvailableToSell, $prod->prodID, $prod->productAvailability, $prod->productDescription, $prod->productName, $prod->productPicture, $prod->productPrice, $prod->productProfit, $prod->specifications]);
				} catch (\Throwable $th) {
					//throw $th;
				}
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
}
function hourly_action_scheduler_taager_product_check($db)
{
	global $wpdb;


	$result = $wpdb->get_results("SELECT * from $wpdb->postmeta where meta_key = '_sku' and post_id in(select post_id from  $wpdb->postmeta where meta_key ='taager_product' )");

	if (!$result) {
		return;
	}
	$ta_ids = wp_list_pluck($result, 'meta_value');
	$ta_ids_string = implode("','", $ta_ids);
	$products = $db->query("SELECT * FROM products where prodID in('$ta_ids_string')")->fetchall(PDO::FETCH_ASSOC);

	$taager_max_shipping = 0;
	$ta_selected_country =  get_option('ta_selected_country', 'EGY');

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

	foreach ($result as $post) {
		$p_id = $post->post_id;


		$key = array_search($post->meta_value, array_column($products, 'prodID'));
		if ($key != '') {
			$response = (object) $products[$key];
			if ($response) {
				$taager_price = get_post_meta($p_id, '_ta_product_price', true);
				$_stock = get_post_meta($p_id, '_stock_status', true);

				if ($response->isProductAvailableToSell) {
					$stock_status = 'instock';
				} else {
					$stock_status = 'outofstock';
				}

				if ($_stock == $stock_status and $response->productPrice == $taager_price) {
					continue;
				}

				$product_data = wc_get_product($p_id);
				$product_type = $product_data->get_type();

				$ta_shipping = get_post_meta($p_id, 'ta__shipping_charge', true);

				$product_data->set_stock_status($stock_status);
				$product_data->save();
				$current_product_price = get_post_meta($p_id, '_price', true);


				if ('yes' == $ta_shipping) {
					$taager_profit       = get_post_meta($p_id, '_ta_product_profit', true);

					$ta_new_min_price = $taager_price - $taager_profit + $taager_max_shipping;

					if (intval($current_product_price) < intval($ta_new_min_price)) {
						update_post_meta($p_id, '_price', $ta_new_min_price);
					}
				} else {
					if (intval($taager_price) != intval($response->productPrice)) {

						$update_product_wc_status = array(
							'ID' => $p_id,
							'post_status' => 'draft',
						);
						wp_update_post($update_product_wc_status);
					}
				}
				update_post_meta($p_id, '_ta_product_price', $response->productPrice);
				update_post_meta($p_id, '_ta_product_profit', $response->productProfit);
			} else {
				$product_data = wc_get_product($p_id);
				$stock_status = 'outofstock';
				//update_post_meta( $p_id, '_stock_status', $stock_status );
				$product_data->set_stock_status($stock_status);
				$product_data->save();
			}
		}
	}
}
