<?php

namespace WCPM\Classes\Pixels;

use WCPM\Classes\Admin\Environment_Check;
use WCPM\Classes\Pixels\Google\Google;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

trait Trait_Product {

	protected function get_formatted_variant_text( $product ) {

		$variant_text_array = [];

		$attributes = $product->get_attributes();
		if ($attributes) {
			foreach ($attributes as $key => $value) {

				$key_name             = str_replace('pa_', '', $key);
				$variant_text_array[] = ucfirst($key_name) . ': ' . strtolower($value);
			}
		}

		return implode(' | ', $variant_text_array);
	}

	protected function get_variation_or_product_id( $item, $variations_output = true ) {

		if (0 <> $item['variation_id'] && true === $variations_output) {
			return $item['variation_id'];
		} else {
			return $item['product_id'];
		}
	}

	// https://stackoverflow.com/a/56278308/4688612
	// https://stackoverflow.com/a/39034036/4688612
	public function get_brand_name( $product_id ) {

		$brand_taxonomy = 'pa_brand';

		if (( new Environment_Check($this->options) )->is_yith_wc_brands_active()) {
			$brand_taxonomy = 'yith_product_brand';
		} elseif (( new Environment_Check($this->options) )->is_woocommerce_brands_active()) {
			$brand_taxonomy = 'product_brand';
		}

		$brand_taxonomy = apply_filters_deprecated('wooptpm_custom_brand_taxonomy', [$brand_taxonomy], '1.13.0', 'wpm_custom_brand_taxonomy');

		// Use custom brand_taxonomy
		$brand_taxonomy = apply_filters('wpm_custom_brand_taxonomy', $brand_taxonomy);

		if ($this->get_brand_by_taxonomy($product_id, $brand_taxonomy)) {
			return $this->get_brand_by_taxonomy($product_id, $brand_taxonomy);
		} elseif ($this->get_brand_by_taxonomy($product_id, 'pa_' . $brand_taxonomy)) {
			return $this->get_brand_by_taxonomy($product_id, 'pa_' . $brand_taxonomy);
		} else {
			return '';
		}
	}

	public function get_brand_by_taxonomy( $product_id, $taxonomy ) {

		if (taxonomy_exists($taxonomy)) {
			$brand_names = wp_get_post_terms($product_id, $taxonomy, ['fields' => 'names']);
			return reset($brand_names);
		} else {
			return '';
		}
	}

	// get an array with all product categories
	public function get_product_category( $product_id ) {

		/**
		 * On some installs the categories don't sync down to the variations.
		 * Therefore, we get the categories from the parent product.
		 */
		if ('variation' === wc_get_product($product_id)->get_type()) {
			$product_id = wc_get_product($product_id)->get_parent_id();
		}

		$prod_cats        = get_the_terms($product_id, 'product_cat');
		$prod_cats_output = [];

		// only continue with the loop if one or more product categories have been set for the product
		if (!empty($prod_cats)) {

			foreach ((array) $prod_cats as $key) {
				$prod_cats_output[] = $key->name;
			}

			// apply filter to the $prod_cats_output array
			$prod_cats_output = apply_filters_deprecated('wgact_filter', [$prod_cats_output], '1.10.2', '', 'This filter has been deprecated without replacement.');
		}

		return $prod_cats_output;
	}

	protected function is_variable_product_by_id( $product_id ) {

		$product = wc_get_product($product_id);

		return $product->get_type() === 'variable';
	}

	protected function get_compiled_product_id( $product_id, $product_sku, $options, $channel = '' ) {

		// depending on setting use product IDs or SKUs
		if (0 == $this->options['google']['ads']['product_identifier'] || 'ga_ua' === $channel || 'ga_4' === $channel) {
			return (string) $product_id;
		} else {
			if (1 == $this->options['google']['ads']['product_identifier']) {
				return (string) 'woocommerce_gpf_' . $product_id;
			} else {
				if ($product_sku) {
					return (string) $product_sku;
				} else {
					return (string) $product_id;
				}
			}
		}
	}

	protected function get_dyn_r_ids( $product ) {

		$dyn_r_ids = [
			'post_id' => (string) $product->get_id(),
			'sku'     => (string) $product->get_sku() ? $product->get_sku() : $product->get_id(),
			'gpf'     => 'woocommerce_gpf_' . $product->get_id(),
			'gla'     => 'gla_' . $product->get_id(),
		];

		// if you want to add a custom dyn_r_id for each product
		$dyn_r_ids = apply_filters_deprecated('wooptpm_product_ids', [$dyn_r_ids, $product], '1.13.0', 'wpm_product_ids');
		return apply_filters('wpm_product_ids', $dyn_r_ids, $product);
	}

	protected function log_problematic_product_id( $product_id = 0 ) {

		wc_get_logger()->debug(
			'WooCommerce detects the page ID ' . $product_id . ' as product, but when invoked by wc_get_product( ' . $product_id . ' ) it returns no product object',
			['source' => 'wpm']
		);
	}

	protected function log_problematic_product( $product ) {

		wc_get_logger()->debug(
			'WooCommerce detects the following product as product , but when invoked by wc_get_product( ' . $product_id . ' ) it returns no product object',
			['source' => 'wpm']
		);
	}

	protected function get_order_item_ids( $order ) {

		$order_items       = $this->wpm_get_order_items($order);
		$order_items_array = [];

		foreach ((array) $order_items as $order_item) {

			$product_id = $this->get_variation_or_product_id($order_item->get_data(), $this->options_obj->general->variations_output);

			$product = wc_get_product($product_id);

			// only continue if WC retrieves a valid product
			if (is_object($product)) {

				$dyn_r_ids           = $this->get_dyn_r_ids($product);
				$product_id_compiled = $dyn_r_ids[$this->get_dyn_r_id_type()];
				$order_items_array[] = $product_id_compiled;
			} else {

				$this->log_problematic_product_id($product_id);
			}
		}

		return $order_items_array;
	}

	protected function get_order_items_formatted_for_purchase_event( $order ) {

		$order_items           = $this->wpm_get_order_items($order);
		$order_items_formatted = [];

		foreach ((array) $order_items as $order_item) {

			$product_id = $this->get_variation_or_product_id($order_item->get_data(), $this->options_obj->general->variations_output);

			$product         = wc_get_product($product_id);
			$product_details = [];

			// only continue if WC retrieves a valid product
			if (is_object($product)) {

				$dyn_r_ids           = $this->get_dyn_r_ids($product);
				$product_id_compiled = $dyn_r_ids[$this->get_dyn_r_id_type()];

				$product_details['id']       = $product_id_compiled;
				$product_details['name']     = $product->get_name();
				$product_details['quantity'] = $order_item->get_quantity();
				$product_details['price']    = $product->get_price();
				$product_details['brand']    = $this->get_brand_name($product_id);
				$product_details['category'] = implode(',', $this->get_product_category($product_id));

				if ($product->is_type('variation')) {
					$product_details['variant'] = $this->get_formatted_variant_text($product);

					$parent_id      = $product->get_parent_id();
					$parent_product = wc_get_product($parent_id);

					$dyn_r_ids_parent             = $this->get_dyn_r_ids($parent_product);
					$parent_product_id_compiled   = $dyn_r_ids_parent[$this->get_dyn_r_id_type()];
					$product_details['parent_id'] = $parent_product_id_compiled;
				}

				$order_items_formatted[] = $product_details;
			} else {

				$this->log_problematic_product_id($product_id);
			}
		}

		return $order_items_formatted;
	}

	protected function get_dyn_r_id_type( $pixel_name = null ) {

		if ($pixel_name) {
			$this->pixel_name = $pixel_name;
		}

		if (0 == $this->options_obj->google->ads->product_identifier) {
			$this->dyn_r_id_type = 'post_id';
		} elseif (1 == $this->options_obj->google->ads->product_identifier) {
			$this->dyn_r_id_type = 'gpf';
		} elseif (2 == $this->options_obj->google->ads->product_identifier) {
			$this->dyn_r_id_type = 'sku';
		} elseif (3 == $this->options_obj->google->ads->product_identifier) {
			$this->dyn_r_id_type = 'gla';
		}

		// If you want to change the dyn_r_id type programmatically
		$this->dyn_r_id_type = apply_filters_deprecated('wooptpm_product_id_type_for_' . $this->pixel_name, [$this->dyn_r_id_type], '1.13.0', 'wpm_product_id_type_for_');
		$this->dyn_r_id_type = apply_filters('wpm_product_id_type_for_' . $this->pixel_name, $this->dyn_r_id_type);

		return $this->dyn_r_id_type;
	}

	protected function wpm_get_order_items( $order ) {

		$order_items = apply_filters_deprecated('wooptpm_order_items', [$order->get_items(), $order], '1.13.0', 'wpm_order_items');

		// Give option to filter order items
		// then return
		return apply_filters('wpm_order_items', $order_items, $order);
	}

	protected function get_front_end_order_items( $order ) {

		$order_items           = $this->wpm_get_order_items($order);
		$order_items_formatted = [];

		foreach ((array) $order_items as $order_item) {

			$order_item_data = $order_item->get_data();

			$product = $order_item->get_product();

			if (!is_object($product)) {

				wc_get_logger()->debug('get_order_item_data received an order item which is not a valid product: ' . $order_item->get_id(), ['source' => 'wpm']);
				return [];
			}

			$order_items_formatted[$order_item_data['product_id']] = [
				'id'           => $order_item_data['product_id'],
				'variation_id' => $order_item_data['variation_id'],
				'name'         => $order_item_data['name'],
				'quantity'     => $order_item_data['quantity'],
				'price'        => ( new Google($this->options) )->wpm_get_order_item_price($order_item, $product),
				'subtotal'     => (float) wc_format_decimal($order_item_data['subtotal'], 2),
				'subtotal_tax' => (float) wc_format_decimal($order_item_data['subtotal_tax'], 2),
				'total'        => (float) wc_format_decimal($order_item_data['total'], 2),
				'total_tax'    => (float) wc_format_decimal($order_item_data['total_tax'], 2),
			];
		}

		return $order_items_formatted;
	}

	public function buffer_get_product_data_layer_script( $product, $set_position = true, $meta_tag = false ) {

		ob_start();

		$this->get_product_data_layer_script($product, $set_position = true, $meta_tag = false);

		return ob_get_clean();
	}

	public function get_product_data_layer_script( $product, $set_position = true, $meta_tag = false ) {

		if (!is_object($product)) {
			wc_get_logger()->debug('get_product_data_layer_script received an invalid product', ['source' => 'wpm']);
			return '';
		}

		$data = $this->get_product_details_for_datalayer($product);

		// If placed in <head> it must be a <meta> tag else, it can be an <input> tag
		// Added name and content to meta in order to pass W3 validation test at https://validator.w3.org/nu/
		$tag = $meta_tag ? "meta name='wpm-dataLayer-meta' content='" . $product->get_id() . "'" : "input type='hidden'";

		$this->get_product_data_layer_script_html_part_1($tag, $product, $data, $set_position, $meta_tag);
	}

	protected function get_product_data_layer_script_html_part_1( $tag, $product, $data, $set_position, $meta_tag ) {

		if ($meta_tag) {
			?>
			<meta name="pm-dataLayer-meta" content="<?php esc_html_e($product->get_id()); ?>" class="wpmProductId"
				  data-id="<?php esc_html_e($product->get_id()); ?>">
			<?php
		} else {
			?>
			<meta input type="hidden" class="wpmProductId" data-id="<?php esc_html_e($product->get_id()); ?>">
			<?php
		}

		?>
		<script>
			(window.wpmDataLayer = window.wpmDataLayer || {}).products             = window.wpmDataLayer.products || {}
			window.wpmDataLayer.products[<?php esc_html_e($product->get_id()); ?>] = <?php echo wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
			<?php $set_position ? $this->get_product_data_layer_script_html_part_2($product) : ''; ?>
		</script>
		<?php
	}

	protected function get_product_data_layer_script_html_part_2( $product ) {
		?>
		window.wpmDataLayer.products[<?php esc_html_e($product->get_id()); ?>]['position'] = window.wpmDataLayer.position++
		<?php
	}

	public function get_product_details_for_datalayer( $product ) {

		global $woocommerce_wpml;

		$dyn_r_ids = $this->get_dyn_r_ids($product);

		if (( new Environment_Check($this->options) )->is_wpml_woocommerce_multi_currency_active()) {
			$price = $woocommerce_wpml->multi_currency->prices->get_product_price_in_currency($product->get_id(), get_woocommerce_currency());
		} else {
			$price = $product->get_price();
		}

		$product_details = [
			'id'         => (string) $product->get_id(),
			'sku'        => (string) $product->get_sku(),
			'price'      => (float) wc_format_decimal($price, 2),
			'brand'      => $this->get_brand_name($product->get_id()),
			'quantity'   => 1,
			'dyn_r_ids'  => $dyn_r_ids,
			'isVariable' => $product->get_type() === 'variable',
		];

		if ($product->get_type() === 'variation') { // In case the product is a variation

			$parent_product = wc_get_product($product->get_parent_id());

			if ($parent_product) {

				$product_details['name'] = $parent_product->get_name();

				$product_details['parentId_dyn_r_ids'] = $this->get_dyn_r_ids($parent_product);
				$product_details['parentId']           = $parent_product->get_id();
			} else {

				wc_get_logger()->debug('Variation ' . $product->get_id() . ' doesn\'t link to a valid parent product.', ['source' => 'wpm']);
			}

			$product_details['variant']     = $this->get_formatted_variant_text($product);
			$product_details['category']    = $this->get_product_category($product->get_parent_id());
			$product_details['isVariation'] = true;
		} else { // It's not a variation, so get the fields for a regular product

			$product_details['name']        = (string) $product->get_name();
			$product_details['category']    = $this->get_product_category($product->get_id());
			$product_details['isVariation'] = false;
		}

		return $product_details;
	}
}
