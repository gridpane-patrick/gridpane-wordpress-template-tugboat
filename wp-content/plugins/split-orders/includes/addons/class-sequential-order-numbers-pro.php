<?php

namespace Vibe\Split_Orders\Addons;

use Vibe\Split_Orders\Split_Orders;
use WC_Order;
use WC_Seq_Order_Number_Pro;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Provides support for Sequential Order Numbers Pro plugin
 *
 * @since 1.2
 */
class Sequential_Order_Numbers_Pro {

	/**
	 * Creates an instance and sets up hooks to integrate with the rest of the extension, only if Sequential Order
	 * Numbers Pro plugin is installed
	 */
	public function __construct() {
		add_filter( Split_Orders::hook_prefix( 'settings' ), array( __CLASS__, 'add_settings' ) );
		add_action( Split_Orders::hook_prefix( 'orders_updated' ), array( __CLASS__, 'orders_updated' ), 10, 2 );
	}

	public static function add_settings( $settings ) {
		$settings[] = array(
			'name'     => __( 'Append order number suffix', 'split-orders' ),
			'desc'     => __( 'Enable order number suffix', 'split-orders' ),
			'desc_tip' => __( 'Assign the same order number to split orders as the order they were split from, with an index appended, e.g. #123-1', 'split-orders' ),
			'id'       => Split_Orders::hook_prefix( 'order_number_suffix' ),
			'type'     => 'checkbox'
		);

		return $settings;
	}

	/**
	 * Assigns a sequential order number to newly created orders in a split
	 *
	 * @param WC_Order $new_order The newly created order
	 */
	public static function orders_updated( WC_Order $new_order, WC_Order $source_order ) {
		if ( ! class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
			return;
		}

		if ( static::is_order_number_suffix_enabled() ) {
			static::assign_order_number( $new_order, $source_order );
		} else {
			WC_Seq_Order_Number_Pro::instance()->set_sequential_order_number( $new_order );
		}
	}

	/**
	 * Fetches and returns the order number suffix setting
	 *
	 * @return bool True if order number suffix is enabled, false otherwise
	 */
	public static function is_order_number_suffix_enabled() {
		return boolval( get_option( Split_Orders::hook_prefix( 'order_number_suffix' ), false ) );
	}

	/**
	 * Generates and saves an order number to the given new order, using the split index as a suffix
	 *
	 * @param WC_Order $new_order    The order to assign a new order number to
	 * @param WC_Order $source_order The original order that the new order was split from
	 */
	private static function assign_order_number( WC_Order $new_order, WC_Order $source_order ) {
		$order_number = $source_order->get_order_number();
		$split_index  = $new_order->get_meta( '_vibe_split_orders_split_index' );

		if ( $split_index ) {
			$order_number = sprintf( '%s-%d', $order_number, $split_index );

			$order_number = apply_filters( Split_Orders::hook_prefix( 'split_order_number' ), $order_number, $new_order, $source_order, $split_index );

			$new_order->update_meta_data( '_order_number_formatted', $order_number );
			$new_order->save_meta_data();
		}
	}
}
