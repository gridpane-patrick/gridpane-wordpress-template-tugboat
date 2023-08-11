<?php

namespace Vibe\Split_Orders\Upgrades;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Runs required upgrades for the 1.4.0 release
 *
 * @since 1.4.0
 */
class Upgrade_140 extends Upgrade {

	/**
	 * Regenerates the shipping and billing address indexes for all split orders
	 */
	public function run() {
		// Ensure we filter the orders to only ones created by a split
		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( __CLASS__, 'split_orders_query_meta' ), 10, 2 );

		$orders = $this->split_orders();

		remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( __CLASS__, 'split_orders_query_meta' ), 10 );

		foreach ( $orders as $order ) {
			update_post_meta( $order->get_id(), '_billing_address_index', implode( ' ', $order->get_address( 'billing' ) ) );
			update_post_meta( $order->get_id(), '_shipping_address_index', implode( ' ', $order->get_address( 'shipping' ) ) );
		}
	}

	public function split_orders() {
		return wc_get_orders( array( 'type' => 'shop_order', 'split-order' => true, 'limit' => -1 ) );
	}

	public static function split_orders_query_meta( array $query, array $query_vars ) {
		if ( ! empty( $query_vars['split-order'] ) && $query_vars['split-order'] ) {
			$query['meta_query'][] = array(
				'key' => '_vibe_split_orders_split_from',
				'compare' => 'EXISTS',
			);
		}

		return $query;
	}
}
