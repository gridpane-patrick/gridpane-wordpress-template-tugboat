<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * WooCommerce Orders Tracking
 */
if ( ! class_exists( 'VI_WOOCOMMERCE_ALIDROPSHIP_Plugins_WooCommerce_Orders_Tracking' ) ) {
	class VI_WOOCOMMERCE_ALIDROPSHIP_Plugins_WooCommerce_Orders_Tracking {
		protected static $settings;

		public function __construct() {
			add_filter( 'vi_woo_orders_tracking_show_tracking_of_order_item', array(
				$this,
				'show_tracking_of_order_item'
			), 10, 3 );
		}

		/**
		 * Make sure tracking number field is shown for an order line item if it is an AliExpress product
		 *
		 * @param $show
		 * @param $item_id
		 * @param $order_id
		 *
		 * @return bool
		 */
		public function show_tracking_of_order_item( $show, $item_id, $order_id ) {
			if ( ! $show ) {
				$order   = wc_get_order( $order_id );
				$item    = $order->get_item( $item_id );
				$product = $item->get_product();
				/**
				 * @var $product WC_Product
				 */
				if ( $product ) {
					if ( $product->is_type( 'variation' ) ) {
						$parent_id = $product->get_parent_id();
						if ( get_post_meta( $parent_id, '_vi_wad_aliexpress_product_id', true ) ) {
							$show = true;
						}
					} else {
						if ( $product->get_meta( '_vi_wad_aliexpress_product_id' ) ) {
							$show = true;
						}
					}
				}
			}

			return $show;
		}
	}
}
