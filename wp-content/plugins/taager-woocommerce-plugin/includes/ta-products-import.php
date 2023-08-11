<?php

defined('ABSPATH') || exit;

add_action('wp_ajax_list_taager_products', 'list_taager_products');
function list_taager_products()
{
	global $wpdb;
	$product_name = trim($_GET['product_name']);
	$pageNumber = $_GET['pageNumber'];
	$product_sort = $_GET['product_sort'];
	$pageSize = $_GET['pageSize'];
	$product_category = $_GET['product_category'];

	$page = 1;
	if (!empty($pageNumber)) {
		$page = $pageNumber;
	}

	$items_per_page = $pageSize;
	$offset = ($page - 1) * $items_per_page;

	if (class_exists('SQLite3')) {
		try {
			$db = new PDO("sqlite:" . WP_CONTENT_DIR . '/ta.db');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			try {
				$db->query("SELECT count(*) FROM products  limit 1");
			} catch (\Throwable $th) {
				hourly_action_scheduler_taager_product_update_db($db);
			}
		} catch (Exception $e) {
			echo "Unable to connect to database";
			echo $e->getMessage();
			exit;
		}
		$where = "where is_min = '1' ";
		$_sku = $wpdb->get_results("SELECT * from $wpdb->postmeta where meta_key = '_sku' and post_id in(select post_id from  $wpdb->postmeta where meta_key ='taager_product' )");
		if ($_sku) {
			$ta_ids = wp_list_pluck($_sku, 'meta_value');
			$ta_ids_string = implode("','", $ta_ids);
			$where .= " and products.prodID NOT IN('$ta_ids_string') ";
		}
		if ($product_category != '') {
			$product_category = $product_category;
			$where .= " and products.categoryId ='$product_category' ";
		}

		switch ($product_sort) {
			case 'max_price':
				$orderBy = 'price desc';
				break;
			case 'lowest_price':
				$orderBy = 'price asc';
				break;
			case 'lowest_profit':
				$orderBy = 'profit asc';
				break;
			default:
				$orderBy = 'profit desc';
				break;
		}

		if ($product_name != '') {
			$where .= " and products.productName like '%$product_name%' 	";
		}

		$sql = "SELECT products.prodID as id , products.productName as name ,
					    CAST(products.productPrice AS UNSIGNED) as price ,products.categoryId,
					    CAST(products.productProfit AS UNSIGNED) as  profit ,categories.text as cat,
					    products.productPicture as image 
					    FROM products 
					left JOIN categories ON categories._id = products.categoryId
					$where 
					order by $orderBy
					LIMIT $offset,$items_per_page 
				";

		echo json_encode([
			'products' => $db->query("SELECT count(*) as count FROM products left JOIN categories ON categories._id = products.categoryId $where LIMIT 1")->fetch(PDO::FETCH_ASSOC)['count'],
			'items' =>  $db->query($sql)->fetchall(PDO::FETCH_ASSOC),
		]);
	} else {
		echo notSQLite();
	}

	die;
}

function notSQLite()
{
	global $wpdb;

	$product_category = $_GET['product_category'];
	$product_name = $_GET['product_name'];
	$cat_param   = ($product_category) ? '&category_name_ar=' . urlencode($product_category) : null;
	$products = [];
	$api_products = callAPI('GET', TAAGER_URL . '/product/?taager_import=1' . $cat_param);
	$products_excluded = [];
	$products_name = [];
	foreach ($wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key ='_sku' ") as  $_sku) {
		$products_excluded[] = $_sku->meta_value;
	}

	foreach ($api_products->data as $key => $prod) {
		if (in_array($prod->prodID, $products_excluded)) {
			continue;
		}
		if ($prod->isProductAvailableToSell and !in_array($prod->productName, $products_name)) {
			$cat = '';
			if (isset($prod->category->text)) {
				$cat = $prod->category->text;
			}
			if ($product_name) {
				if (stripos($prod->productName, $product_name) !== false) {
					$products_name[] = $prod->productName;

					$products[] = [
						'id' => $prod->prodID,
						'name' => $prod->productName,
						'price' => $prod->productPrice,
						'profit' => $prod->productProfit,
						'image' => $prod->productPicture,
						'cat' => $cat
					];
				}
			} else {
				$products_name[] = $prod->productName;
				$products[] = [
					'id' => $prod->prodID,
					'name' => $prod->productName,
					'price' => $prod->productPrice,
					'profit' => $prod->productProfit,
					'image' => $prod->productPicture,
					'cat' => $cat
				];
			}
		}
	}

	if ($_GET['product_sort']) {
		if ($_GET['product_sort'] == 'max_price') {
			array_multisort(array_column($products, 'price'), SORT_DESC, $products);
		} elseif ($_GET['product_sort'] == 'lowest_price') {
			array_multisort(array_column($products, 'price'), SORT_ASC, $products);
		} elseif ($_GET['product_sort'] == 'max_profit') {
			array_multisort(array_column($products, 'profit'), SORT_DESC, $products);
		} elseif ($_GET['product_sort'] == 'lowest_profit') {
			array_multisort(array_column($products, 'profit'), SORT_ASC, $products);
		}
	}


	$co = count($products);
	$page = !empty($_GET['pageNumber']) ? (int) $_GET['pageNumber'] : 1;
	$pageSize = !empty($_GET['pageSize']) ? (int) $_GET['pageSize'] : 1;

	$products = array_chunk($products, $pageSize);
	$nthPage = $products[$page - 1] ?? [];
	header('Content-type: application/json');

	return json_encode(['products' => $co, 'items' => (array) $nthPage]);
}
add_action('wp_ajax_taager_products_import', 'ta_product_import_ajax');
function ta_product_import_ajax()
{
	$increase_price = isset($_POST['increase_price']) ? $_POST['increase_price'] : 0;
	$enable_increase_price = isset($_POST['enable_increase_price']) ? $_POST['enable_increase_price'] : null;
	$increase_price_by = isset($_POST['increase_price_by']) ? $_POST['increase_price_by'] : null;
	$product_cat = isset($_POST['product_cat']) ? $_POST['product_cat'] : null;

	if (isset($_POST['product_name'])) {
		if (class_exists('SQLite3')) {
			$db = new PDO("sqlite:" . WP_CONTENT_DIR . '/ta.db');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			foreach ($_POST['product_name'] as $name) {
				import_products(null, $name, null, $enable_increase_price, $increase_price, $increase_price_by,$db);
			}
		} else {

			foreach ($_POST['product_name'] as $name) {
				noSql_import_products(null, $name, null, $enable_increase_price, $increase_price, $increase_price_by);
			}
		}
	} else {

		if (class_exists('SQLite3')) {
			$db = new PDO("sqlite:" . WP_CONTENT_DIR . '/ta.db');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$response = import_products($_POST['product_cat'], null, null, $enable_increase_price, $increase_price, $increase_price_by,$db);
		} else {
			$response = noSql_import_products($_POST['product_cat'], null, null, $enable_increase_price, $increase_price, $increase_price_by);
		}

		if (true == $response['import']) {
			$existing_product = '';
			if ($response['existing_count'] > 0) {
				$product_text = 'product';
				$was          = 'was';
				$exist_text   = 'exists';
				if (1 < $response['existing_count']) {
					$product_text = 'products';
					$exist_text   = 'exist';
					$was          = 'were';
				}

				//$existing_product = '<div class="existing_products_list">' . $response['existing_count'] . ' ' . $product_text . ' ' . $was . ' already imported before. List of those products are: <ul>';
				$existing_product = '<div class="existing_products_list">' . $response['existing_count'] . ' منتجات موجودة وتم استيرادها من قبل<ul>';
				foreach ($response['existing_post'] as $product) {
					$existing_product .= "<li>{$product}</li>";
				}
				$existing_product .= '</ul></div>';
			}
			$imported_product_text = ($response['imported_count'] <= 1) ? 'المنتجات' : 'المنتجات';
			echo "<p>{$response['imported_count']} {$imported_product_text} المستوردة. </p>" . $existing_product;
		}
	}

	die;
}

/**
 * Import products from backend
 */
function import_products($product_category, $product_name, $product_ids, $enable_increase_price, $increase_price, $increase_price_by,$db)
{
	// Set unlimited execution time

	set_time_limit(0);
	global $wpdb;


	if ($product_ids) {
		$ta_ids_string = implode("','", $product_ids);
		$where = " where prodID  IN('$ta_ids_string') ";
	} elseif ($product_name) {
		$where = " where productName like '%$product_name%' ";
	} else if($product_category){
		$product_category = $product_category;
		$where = " where categoryId ='$product_category' ";
	}
	$sql = "SELECT products.* , categories.text as categoryText  from products   
					left JOIN categories ON categories._id = products.categoryId
					$where
	";
	$products = $db->query($sql)->fetchall(PDO::FETCH_OBJ);
	if(!$products){
		return[];
	}
	$existing_posts               = [];
	$count_import_product = 0;
	foreach ($products as $key => $product) {
		$product->additionalMedia = json_decode($product->additionalMedia);
		$product->attributes = json_decode($product->attributes);

		$sku       = $product->prodID;

		$data       = generate_product_data($product, $enable_increase_price, $increase_price, $increase_price_by);
		$existing_wc_products = get_posts_by_sku($sku, 'product');
		$existing_wc_product_variations = get_posts_by_sku($sku, 'product_variation');

		$insert_product_data = array(
			'post_title'   => $product->productName,
			'post_content' => $product->specifications,
			'post_excerpt' => $product->productDescription,
		);

		if ($existing_wc_products) {
			$existing_posts[] = $product->productName;
			unset($insert_product_data['post_title']);
			foreach ($existing_wc_products as $existing_wc_product) {
				$insert_product_data['ID'] = $existing_wc_product->ID;
				$product_id                = wp_update_post($insert_product_data);

				// meta data to identify that product come from taager api
				update_post_meta($existing_wc_product->ID, 'taager_product', 1);
				$stock_status = $product->isProductAvailableToSell ? 'instock' : 'outofstock';
				update_post_meta($existing_wc_product->ID, '_stock_status', $stock_status);
				$current_product_price = get_post_meta($existing_wc_product->ID, '_regular_price', true);
				if (intval($current_product_price) < intval($product->productPrice)) {
					update_post_meta($existing_wc_product->ID, '_regular_price', $product->productPrice);
					update_post_meta($existing_wc_product->ID, '_price', $product->productPrice);
					$update_product_wc_status = array(
						'post_title'  => $product->productName,
						//'post_status' => 'draft',
					);
					wp_update_post($update_product_wc_status);
				}
				update_post_meta($existing_wc_product->ID, '_ta_product_price', $product->productPrice);
				update_post_meta($existing_wc_product->ID, '_ta_how_to_use', $product->howToUse);
			}
		} else if ($existing_wc_product_variations) {
			$existing_variant_name = "$product->productName [[$product->prodID]]";
			$existing_posts[] = $existing_variant_name;
			foreach ($existing_wc_product_variations as $existing_wc_product_variation) {
				$existing_variation = wc_get_product($existing_wc_product_variation->ID);
				$current_product_price = get_post_meta($existing_wc_product_variation->ID, '_regular_price', true);
				if (intval($current_product_price) < intval($product->productPrice)) {
					update_post_meta($existing_wc_product->ID, '_regular_price', $product->productPrice);
					update_post_meta($existing_wc_product->ID, '_price', $product->productPrice);
					$update_product_wc_status = array(
						'ID' => $existing_wc_product_variation->ID,
						'post_title'  => $product->productName,
						//'post_status' => 'draft',
					);
					wp_update_post($update_product_wc_status);
				}
				update_post_meta($existing_wc_product_variation->ID, "_ta_how_to_use", $product->howToUse);
				update_post_meta($existing_wc_product_variation->ID, "taager_product", 1);
				$stock_status = $product->isProductAvailableToSell ? 'instock' : 'outofstock';
				$existing_variation->set_stock_status($stock_status);
				$existing_variation->save();
			}
		} else if ($product->isProductAvailableToSell && !empty($product->attributes)) {

			$check_main_variable_product = check_main_variable_product($product, $data);

			$parent_product = get_parent_product($product, $data);

			$variable_product_attributes = [];
			foreach ($product->attributes as $attribute) {
				$variable_product_attributes[] = add_product_attribute($attribute, $parent_product);
			}
			$parent_product->set_attributes($variable_product_attributes);
			$parent_product->save();

			$variant_product_id = create_product_variant($product, $parent_product, $data, $enable_increase_price, $increase_price, $increase_price_by);
			if (empty($parent_product->get_default_attributes())) {
				$default_attributes = array();
				foreach ($product->attributes as $attribute) {
					$default_attributes[$attribute->type] = ta_get_attribute_name($attribute->value);
				}
				$parent_product->set_default_attributes($default_attributes);
			}
			update_post_meta($variant_product_id, '_ta_how_to_use', $product->howToUse);

			$parent_product->save();
			if ($check_main_variable_product !== false) {
				$count_import_product++;
			}
		} else if ($product->isProductAvailableToSell && empty($product->attributes)) {
			$table     = $wpdb->prefix . 'posts';
			$sql       = "INSERT INTO `$table` (`post_title`, `post_name`, `post_type`, `post_status`, `post_content`, `post_excerpt`, `post_date` ) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', now());";
			$post_data = [$product->productName, sanitize_title($product->productName), 'product', 'publish', $product->specifications, $product->productDescription];
			$wpdb->prepare($sql, $post_data);

			$wpdb->query($wpdb->prepare($sql, $post_data));

			$product_id = $wpdb->insert_id;

			// Assign product to a category
			$term_id = $data['category']->term_id;
			wp_set_object_terms($product_id, $term_id, 'product_cat');

			// Set product type
			wp_set_object_terms($product_id, 'simple', 'product_type');

			set_product_thumbnail_and_gallery($product, $product_id, $data);
			$custom_specification = array();
			$pro_specifications = explode("\r\n", $product->specifications);
			if (!empty($pro_specifications)) {
				foreach ($pro_specifications as $pro_specification) {
					if (strpos($pro_specification, 'فيديو تشغيلى') === false and strpos($pro_specification, 'فيديو للمنتج') === false) {
						$custom_specification[] = str_replace('• ', '', $pro_specification);
					} else {
			        	preg_match_all('#(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\-nocookie\.com\/embed\/|youtube\.com\/(?:embed\/|v\/|e\/|\?v=|shared\?ci=|watch\?v=|watch\?.+&v=))([-_A-Za-z0-9]{10}[AEIMQUYcgkosw048])(.*?)\b#s', $pro_specification, $youtube_match, PREG_SET_ORDER);

						// preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $pro_specification, $youtube_match);
					}
				}
				$custom_specification_data = serialize($custom_specification);
			} else {
				$custom_specification_data = $product->specifications;
			}
			if (!isset($youtube_match[0][0])) {
				preg_match_all('#(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\-nocookie\.com\/embed\/|youtube\.com\/(?:embed\/|v\/|e\/|\?v=|shared\?ci=|watch\?v=|watch\?.+&v=))([-_A-Za-z0-9]{10}[AEIMQUYcgkosw048])(.*?)\b#s', $product->productDescription, $youtube_match, PREG_SET_ORDER);
			}

			update_post_meta($product_id, "_ta_product_specifications", $custom_specification_data);

			if (isset($youtube_match[0][0])) {
				update_post_meta($product_id, "_ta_youtube_url", $youtube_match[0][0]);
			}
			update_post_meta($product_id, '_ta_how_to_use', $product->howToUse);

			// if product was created/updated successfully
			if (!is_wp_error($product_id)) {
				// Save meta data
				foreach ($data['meta'] as $meta_key => $meta_value) {
					update_post_meta($product_id, $meta_key, $meta_value);
				}
				// meta data to identify that product come from taager api
				update_post_meta($product_id, 'taager_product', 1);
			}
			$count_import_product++;
		}
	}

	$response = [
		'import'         => true,
		'imported_count' => $count_import_product,
		'existing_count' => count($existing_posts),
		'existing_post'  => $existing_posts,
	];

	return $response;
}
function noSql_import_products($product_category, $product_name, $product_ids, $enable_increase_price, $increase_price, $increase_price_by)
{
	// Set unlimited execution time

	set_time_limit(0);
	global $wpdb;

	$cat_param   = ($product_category) ? '&category_name_ar=' . urlencode($product_category) : '';
	$pname_param = ($product_name) ? '&prod_name=' . urlencode($product_name) : '';
	$pid_param = ($product_ids) ? '&prod_ids=' . implode(',', $product_ids) : '';

	if ($product_ids) {
		$products = callAPI('GET', TAAGER_URL . '/product/?taager_import=1');
	} elseif ($product_name) {
		$products = callAPI('GET', TAAGER_URL . '/product/?taager_import=1' . $pname_param);
	} else {
		$products = callAPI('GET', TAAGER_URL . '/product/?taager_import=1' . $cat_param . $pname_param . $pid_param);
	}


	$existing_posts               = [];
	$count_import_product = 0;
	for ($i = 0; $i < count($products->data); $i++) {
		$product = $products->data[$i];


		if ($product_category || $product_name) {
			$pos = stripos($product->productName, $product_name);

			if ($product_category && $product_name) {
				if (($product->category->text != $product_category) && ($pos === false)) {
					continue;
				}
			} elseif ($product_name) {
				if ($pos === false) {
					continue;
				}
			}
		}


		$sku       = $product->prodID;

		$data       = generate_product_data($product, $enable_increase_price, $increase_price, $increase_price_by);
		$existing_wc_products = get_posts_by_sku($sku, 'product');
		$existing_wc_product_variations = get_posts_by_sku($sku, 'product_variation');

		$insert_product_data = array(
			'post_title'   => $product->productName,
			'post_content' => $product->specifications,
			'post_excerpt' => $product->productDescription,
		);

		if ($existing_wc_products) {
			$existing_posts[] = $product->productName;
			unset($insert_product_data['post_title']);
			foreach ($existing_wc_products as $existing_wc_product) {
				$insert_product_data['ID'] = $existing_wc_product->ID;
				$product_id                = wp_update_post($insert_product_data);

				// meta data to identify that product come from taager api
				update_post_meta($existing_wc_product->ID, 'taager_product', 1);
				$stock_status = $product->isProductAvailableToSell ? 'instock' : 'outofstock';
				update_post_meta($existing_wc_product->ID, '_stock_status', $stock_status);
				$current_product_price = get_post_meta($existing_wc_product->ID, '_regular_price', true);
				if (intval($current_product_price) < intval($product->productPrice)) {
					update_post_meta($existing_wc_product->ID, '_regular_price', $product->productPrice);
					update_post_meta($existing_wc_product->ID, '_price', $product->productPrice);
					$update_product_wc_status = array(
						'post_title'  => $product->productName,
						//'post_status' => 'draft',
					);
					wp_update_post($update_product_wc_status);
				}
				update_post_meta($existing_wc_product->ID, '_ta_product_price', $product->productPrice);
				update_post_meta($existing_wc_product->ID, '_ta_how_to_use', $product->howToUse);
			}
		} else if ($existing_wc_product_variations) {
			$existing_variant_name = "$product->productName [[$product->prodID]]";
			$existing_posts[] = $existing_variant_name;
			foreach ($existing_wc_product_variations as $existing_wc_product_variation) {
				$existing_variation = wc_get_product($existing_wc_product_variation->ID);
				$current_product_price = get_post_meta($existing_wc_product_variation->ID, '_regular_price', true);
				if (intval($current_product_price) < intval($product->productPrice)) {
					update_post_meta($existing_wc_product->ID, '_regular_price', $product->productPrice);
					update_post_meta($existing_wc_product->ID, '_price', $product->productPrice);
					$update_product_wc_status = array(
						'ID' => $existing_wc_product_variation->ID,
						'post_title'  => $product->productName,
						//'post_status' => 'draft',
					);
					wp_update_post($update_product_wc_status);
				}
				update_post_meta($existing_wc_product_variation->ID, "_ta_how_to_use", $product->howToUse);
				update_post_meta($existing_wc_product_variation->ID, "taager_product", 1);
				$stock_status = $product->isProductAvailableToSell ? 'instock' : 'outofstock';
				$existing_variation->set_stock_status($stock_status);
				$existing_variation->save();
			}
		} else if ($product->isProductAvailableToSell && !empty($product->attributes)) {

			$check_main_variable_product = check_main_variable_product($product, $data);

			$parent_product = get_parent_product($product, $data);

			$variable_product_attributes = [];
			foreach ($product->attributes as $attribute) {
				$variable_product_attributes[] = add_product_attribute($attribute, $parent_product);
			}
			$parent_product->set_attributes($variable_product_attributes);
			$parent_product->save();

			$variant_product_id = create_product_variant($product, $parent_product, $data, $enable_increase_price, $increase_price, $increase_price_by);
			if (empty($parent_product->get_default_attributes())) {
				$default_attributes = array();
				foreach ($product->attributes as $attribute) {
					$default_attributes[$attribute->type] = ta_get_attribute_name($attribute->value);
				}
				$parent_product->set_default_attributes($default_attributes);
			}
			update_post_meta($variant_product_id, '_ta_how_to_use', $product->howToUse);

			$parent_product->save();
			if ($check_main_variable_product !== false) {
				$count_import_product++;
			}
		} else if ($product->isProductAvailableToSell && empty($product->attributes)) {
			$table     = $wpdb->prefix . 'posts';
			$sql       = "INSERT INTO `$table` (`post_title`, `post_name`, `post_type`, `post_status`, `post_content`, `post_excerpt`, `post_date` ) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', now());";
			$post_data = [$product->productName, sanitize_title($product->productName), 'product', 'publish', $product->specifications, $product->productDescription];
			$wpdb->prepare($sql, $post_data);

			$wpdb->query($wpdb->prepare($sql, $post_data));

			$product_id = $wpdb->insert_id;

			// Assign product to a category
			$term_id = $data['category']->term_id;
			wp_set_object_terms($product_id, $term_id, 'product_cat');

			// Set product type
			wp_set_object_terms($product_id, 'simple', 'product_type');

			set_product_thumbnail_and_gallery($product, $product_id, $data);
			$custom_specification = array();
			$pro_specifications = explode("\r\n", $product->specifications);
			if (!empty($pro_specifications)) {
				foreach ($pro_specifications as $pro_specification) {
					if (strpos($pro_specification, 'فيديو تشغيلى') === false and strpos($pro_specification, 'فيديو للمنتج') === false) {
						$custom_specification[] = str_replace('• ', '', $pro_specification);
					} else {
			        	preg_match_all('#(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\-nocookie\.com\/embed\/|youtube\.com\/(?:embed\/|v\/|e\/|\?v=|shared\?ci=|watch\?v=|watch\?.+&v=))([-_A-Za-z0-9]{10}[AEIMQUYcgkosw048])(.*?)\b#s', $pro_specification, $youtube_match, PREG_SET_ORDER);

						// preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $pro_specification, $youtube_match);
					}
				}
				$custom_specification_data = serialize($custom_specification);
			} else {
				$custom_specification_data = $product->specifications;
			}
			if (!isset($youtube_match[0][0])) {
				preg_match_all('#(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\-nocookie\.com\/embed\/|youtube\.com\/(?:embed\/|v\/|e\/|\?v=|shared\?ci=|watch\?v=|watch\?.+&v=))([-_A-Za-z0-9]{10}[AEIMQUYcgkosw048])(.*?)\b#s', $product->productDescription, $youtube_match, PREG_SET_ORDER);
			}

			update_post_meta($product_id, "_ta_product_specifications", $custom_specification_data);

			if (isset($youtube_match[0][0])) {
				update_post_meta($product_id, "_ta_youtube_url", $youtube_match[0][0]);
			}
			update_post_meta($product_id, '_ta_how_to_use', $product->howToUse);

			// if product was created/updated successfully
			if (!is_wp_error($product_id)) {
				// Save meta data
				foreach ($data['meta'] as $meta_key => $meta_value) {
					update_post_meta($product_id, $meta_key, $meta_value);
				}
				// meta data to identify that product come from taager api
				update_post_meta($product_id, 'taager_product', 1);
			}
			$count_import_product++;
		}
	}

	$response = [
		'import'         => true,
		'imported_count' => $count_import_product,
		'existing_count' => count($existing_posts),
		'existing_post'  => $existing_posts,
	];

	return $response;
}

function set_product_thumbnail_and_gallery($product, $product_id, $data)
{

	// Set product feature image
	$featured_image_url = $product->productPicture;

	// if (class_exists('Featured_Image_By_URL') && is_multisite()) {

	fn_taager_product_thumbnail($product_id, $featured_image_url);

	// Set gallery image
	$cs_pro_gallery = $data['gallery_images'];
	fn_taager_product_gallery($product_id, $cs_pro_gallery);
	// } else {

	// 	attach_product_thumbnail($product_id, $featured_image_url, 0);

	// 	$gallery_img_list = [];
	// 	foreach ($data['gallery_images'] as $image_url) {
	// 		if ($image_url == '') {
	// 			continue;
	// 		}

	// 		attach_product_thumbnail($product_id, $image_url, 1);
	// 	}
	// }
}

function get_parent_product($product, $data)
{
	$posts = get_posts(
		array(
			'posts_per_page' => 1,
			'post_type'      => 'product',
			'post_status' => array('publish', 'draft'),
			'title'       => $product->productName,
		)
	);
	$id = '';
	if ($posts[0]) {
		$id = $posts[0]->ID;
	} else {
		$id = create_parent_product($product);

		// set main image
		// if (class_exists('Featured_Image_By_URL') && is_multisite()) {
		$featured_image_url = $product->productPicture;
		fn_taager_product_thumbnail($id, $featured_image_url);

		$cs_pro_gallery = $data['gallery_images'];
		fn_taager_product_gallery($id, $cs_pro_gallery);
		// }
	}
	return wc_get_product($id);
}

function check_main_variable_product($product, $data)
{
	$posts = get_posts(
		array(
			'posts_per_page' => 1,
			'post_type'      => 'product',
			'post_status' => array('publish', 'draft'),
			'title'       => $product->productName,
		)
	);
	$id = '';
	if ($posts[0]) {
		return false;
	} else {
		return true;
	}
}

function create_parent_product($product)
{
	$variable_product_parent = new WC_Product_Variable();
	$variable_product_parent->set_description($product->specifications);
	$variable_product_parent->set_short_description($product->productDescription);
	$variable_product_parent->set_name($product->productName);
	$variable_product_parent->set_status('publish');
	$categoryText = isset($product->category->text) ?  $product->category->text  : $product->categoryText;
	$category_term = get_term_by('name', $categoryText, 'product_cat');
	$variable_product_parent->set_category_ids(array(intval($category_term->term_id)));

	$product_id = $variable_product_parent->save();

	$custom_specification = array();
	$pro_specifications = explode("\r\n", $product->specifications);
	if (!empty($pro_specifications)) {
		foreach ($pro_specifications as $pro_specification) {
			if (strpos($pro_specification, 'فيديو تشغيلى') === false and strpos($pro_specification, 'فيديو للمنتج') === false) {
				$custom_specification[] = str_replace('• ', '', $pro_specification);
			} else {
				preg_match_all('#(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\-nocookie\.com\/embed\/|youtube\.com\/(?:embed\/|v\/|e\/|\?v=|shared\?ci=|watch\?v=|watch\?.+&v=))([-_A-Za-z0-9]{10}[AEIMQUYcgkosw048])(.*?)\b#s', $pro_specification, $youtube_match, PREG_SET_ORDER);

				// preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $pro_specification, $youtube_match);
			}
		}
		$custom_specification_data = serialize($custom_specification);
	} else {
		$custom_specification_data = $product->specifications;
	}

	update_post_meta($product_id, "_ta_product_specifications", $custom_specification_data);

	if (isset($youtube_match[0][0])) {
		update_post_meta($product_id, "_ta_youtube_url", $youtube_match[0][0]);
	}

	return $product_id;
	//return $variable_product_parent->save();
}

function create_parent_product__old($product)
{
	$variable_product_parent = new WC_Product_Variable();
	$variable_product_parent->set_description($product->productDescription);
	$variable_product_parent->set_name($product->productName);
	$variable_product_parent->set_status('draft');
	$categoryText = isset($product->category->text) ?  $product->category->text  : $product->categoryText;

	$category_term = get_term_by('name', $categoryText, 'product_cat');
	$variable_product_parent->set_category_ids(array(intval($category_term->term_id)));

	return $variable_product_parent->save();
}

function add_product_attribute($attribute, $parent_product)
{

	$current_attributes = $parent_product->get_attributes('edit');
	foreach ($current_attributes as $current_attribute) {
		if ($current_attribute->get_name() === $attribute->type) {
			$attribute_options = $current_attribute->get_options();
			if (!in_array(ta_get_attribute_name($attribute->value), $attribute_options)) {
				$attribute_options[] = ta_get_attribute_name($attribute->value);
			}
			return create_new_attribute($attribute->type, $attribute_options);
		}
	}

	return create_new_attribute($attribute->type, array(ta_get_attribute_name($attribute->value)));
}

function create_new_attribute($attribute_name, $attribute_options)
{
	$createdAttribute = new WC_Product_Attribute();
	$createdAttribute->set_id(0);
	$createdAttribute->set_name($attribute_name);
	//$createdAttribute->set_options($attribute_options);
	$createdAttribute->set_options(str_replace('\\', '-', $attribute_options));
	$createdAttribute->set_visible(true);
	$createdAttribute->set_variation(true);
	return $createdAttribute;
}

function create_product_variant($product, $parent_product, $data, $enable_increase_price, $increase_price, $increase_price_by)
{

	if ($enable_increase_price == 1) {
		if ($increase_price != '') {
			if ($increase_price_by == 'by_price') {
				$set_product_price = $product->productPrice + $increase_price;
			} elseif ($increase_price_by == 'by_percentage') {
				$set_product_price =  $product->productPrice + ($product->productPrice * $increase_price / 100);
			}
		} else {
			$set_product_price = $product->productPrice;
		}
	} else {
		$set_product_price = $product->productPrice;
	}
	$set_product_price = intval($set_product_price);

	$variable_product_variant = new WC_Product_Variation();
	$variable_product_variant->set_parent_id($parent_product->get_id());
	$variable_product_variant->set_sku($product->prodID);
	$variable_product_variant->set_price($set_product_price);
	$variable_product_variant->set_regular_price($set_product_price);
	$variable_product_variant->set_stock_status(($product->isProductAvailableToSell) ? 'instock' : 'outofstock');

	$variable_product_attributes = [];
	foreach ($product->attributes as $attribute) {
		$attribute_value = str_replace('\\', '-', $attribute->value);
		$variable_product_attributes[$attribute->type] = ta_get_attribute_name($attribute_value);
	}
	$variable_product_variant->set_attributes($variable_product_attributes);

	$variable_product_variant->save();
	$variant_product_id = get_posts(array(
		'post_type'  => 'product_variation',
		'meta_query' => array(
			array(
				'key'   => '_sku',
				'value' => $product->prodID,
			)
		)
	))[0]->ID;

	update_post_meta($variant_product_id, "_ta_product_price", $product->productPrice);
	update_post_meta($variant_product_id, "_ta_how_to_use", $product->howToUse);
	update_post_meta($variant_product_id, "_regular_price", $set_product_price);
	update_post_meta($variant_product_id, "taager_product", 1);
	update_post_meta($variant_product_id, '_ta_product_profit', $product->productProfit);

	update_post_meta($parent_product->get_id(), 'taager_product', 1);

	set_product_thumbnail_and_gallery($product, $variant_product_id, $data);
	//set_product_thumbnail_and_gallery( $product, $parent_product->get_id(), $data );

	if (!$parent_product->get_image_id()) {
		$product_image_id = get_post_meta($variant_product_id, "_thumbnail_id", true);
		$parent_product->set_image_id($product_image_id);

		$parent_product_image_gallery = get_post_meta($variant_product_id, "_product_image_gallery", true);
		update_post_meta($parent_product->get_id(), '_product_image_gallery', $parent_product_image_gallery);
		$parent_product->save();
	}
}


/**
 * Generate product metadata
 */
function generate_product_data($product, $enable_increase_price, $increase_price, $increase_price_by)
{
	$categoryText = isset($product->category->text) ?  $product->category->text  : $product->categoryText;

	$category       = get_term_by('name', $categoryText, 'product_cat');
	$extra_images = array(
		$product->extraImage1,
		$product->extraImage2,
		$product->extraImage3,
		$product->extraImage4,
		$product->extraImage5,
		$product->extraImage6,
	);
	$gallery_images = array_merge($extra_images, $product->additionalMedia);
	$gallery_images = array_filter($gallery_images);

	if ($product->isProductAvailableToSell) {
		$stock_status = 'instock';
	} else {
		$stock_status = 'outofstock';
	}

	if ($enable_increase_price == 1) {
		if ($increase_price != '') {
			if ($increase_price_by == 'by_price') {
				$set_product_price = $product->productPrice + $increase_price;
			} elseif ($increase_price_by == 'by_percentage') {
				$set_product_price =  $product->productPrice + ($product->productPrice * $increase_price / 100);
			}
		} else {
			$set_product_price = $product->productPrice;
		}
	} else {
		$set_product_price = $product->productPrice;
	}
	$set_product_price = intval($set_product_price);

	$data = array(
		'category'       => $category,
		'gallery_images' => $gallery_images,
		'meta'           => array(
			'_sku'               => $product->prodID,
			'_price'             => $set_product_price,
			'_regular_price'     => $set_product_price,
			'_stock_status'      => $stock_status,
			'_weight'            => $product->productWeight,
			'_featured'          => $product->featured,
			'_ta_product_profit' => $product->productProfit,
			'_ta_product_price'  => $product->productPrice,
		),
	);

	return $data;
}

/**
 * Get product by sku
 */
function get_posts_by_sku($sku, $post_type)
{
	$posts = get_posts(
		array(
			'posts_per_page' => -1,
			'post_type'      => $post_type,
			'post_status' => array('publish', 'draft'),
			'meta_key'       => '_sku',
			'meta_value'     => $sku,
		)
	);

	return $posts ?: null;
}

//Add taager image as featured image
function fn_taager_product_thumbnail($post_id, $knawatfibu_url)
{

	$image_meta_url = '_knawatfibu_url';
	$image_meta_alt = '_knawatfibu_alt';

	if (isset($knawatfibu_url)) {
		global $knawatfibu;
		// Update Featured Image URL
		$image_url = isset($knawatfibu_url) ? esc_url($knawatfibu_url) : '';

		if ($image_url != '') {
			if (get_post_type($post_id) == 'product' || get_post_type($post_id) == 'product_variation') {
				$img_url = get_post_meta($post_id, $image_meta_url, true);
				if (is_array($img_url) && isset($img_url['img_url']) && $image_url == $img_url['img_url']) {
					$image_url = array(
						'img_url' => $image_url,
						'width'	  => $img_url['width'],
						'height'  => $img_url['height']
					);
				} else {
					$imagesize = @getimagesize($image_url);
					$image_url = array(
						'img_url' => $image_url,
						'width'	  => isset($imagesize[0]) ? $imagesize[0] : '',
						'height'  => isset($imagesize[1]) ? $imagesize[1] : ''
					);
				}
			}
			update_post_meta($post_id, $image_meta_url, $image_url);
		} else {
			delete_post_meta($post_id, $image_meta_url);
			delete_post_meta($post_id, $image_meta_alt);
		}
	}
}

//Add taager gallery as featured gallery
function fn_taager_product_gallery($post_id, $knawatfibu_wcgallary)
{

	global $knawatfibu;
	$gallery_key = '_knawatfibu_wcgallary';

	$old_images = get_post_meta($post_id, '_knawatfibu_wcgallary', true);
	if (!empty($old_images)) {
		foreach ($old_images as $key => $value) {
			$old_images[$value['url']] = $value;
		}
	}

	$gallary_images = array();
	if (!empty($knawatfibu_wcgallary)) {
		foreach ($knawatfibu_wcgallary as $knawatfibu_gallary) {
			if (isset($knawatfibu_gallary) && $knawatfibu_gallary != '') {
				$gallary_image = array();
				$gallary_image['url'] = $knawatfibu_gallary;

				if (isset($old_images[$gallary_image['url']]['width']) && $old_images[$gallary_image['url']]['width'] != '') {
					$gallary_image['width'] = isset($old_images[$gallary_image['url']]['width']) ? $old_images[$gallary_image['url']]['width'] : '';
					$gallary_image['height'] = isset($old_images[$gallary_image['url']]['height']) ? $old_images[$gallary_image['url']]['height'] : '';
				} else {
					$imagesizes = @getimagesize($knawatfibu_gallary);
					$gallary_image['width'] = isset($imagesizes[0]) ? $imagesizes[0] : '';
					$gallary_image['height'] = isset($imagesizes[1]) ? $imagesizes[1] : '';
				}

				$gallary_images[] = $gallary_image;
			}
		}
	}

	if (!empty($gallary_images)) {
		update_post_meta($post_id, $gallery_key, $gallary_images);
	} else {
		delete_post_meta($post_id, $gallery_key);
	}
}

/**
 * Attach images to product (feature / gallery)
 */
function attach_product_thumbnail($product_id, $url, $flag)
{

	global $wpdb;

	// If allow_url_fopen is enable in php.ini then use this
	$image_url        = $url;
	$url_array        = explode('/', $url);
	$image_name       = $url_array[count($url_array) - 1];
	$image_data       = file_get_contents($image_url); // Get image data
	$upload_dir       = wp_upload_dir(); // Set upload folder
	$unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
	$filename         = basename($unique_file_name); // Create image file name

	// Check folder permission and define file location
	if (wp_mkdir_p($upload_dir['path'])) {
		$file = $upload_dir['path'] . '/' . $filename;
	} else {
		$file = $upload_dir['basedir'] . '/' . $filename;
	}

	// Create the image file on the server
	file_put_contents($file, $image_data);

	// Check image file type
	$wp_filetype = wp_check_filetype($filename, null);

	// Set attachment data
	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title'     => sanitize_file_name($filename),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$image_query = "SELECT * FROM {$wpdb->posts} WHERE post_title LIKE '%$image_name%'";
	$image_id = intval($wpdb->get_var($image_query));

	if ($image_id != 0) {

		$attach_id = $image_id;
	} else {
		// Create the attachment
		$attach_id = wp_insert_attachment($attachment, $file, $product_id);

		// Include image.php
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata($attach_id, $file);

		// Assign metadata to attachment
		wp_update_attachment_metadata($attach_id, $attach_data);
	}

	// Assign featured image to product
	if ($flag == 0) {
		set_post_thumbnail($product_id, $attach_id);
	}

	// Assign gallery images to product
	if ($flag == 1) {
		$attach_id_array  = get_post_meta($product_id, '_product_image_gallery', true);
		$attach_id_array .= ',' . $attach_id;
		update_post_meta($product_id, '_product_image_gallery', $attach_id_array);
	}
}
