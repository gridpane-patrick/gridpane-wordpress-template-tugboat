<?php

namespace Vibe\Split_Orders;

use WC_Order;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Provides support for the WooCommerce Payments payment gateway
 *
 * @since 1.5
 */
class WooCommerce_Payments {

	/**
	 * Creates an instance and sets up hooks to provide WooCommerce Payments specific support
	 */
	public function __construct() {
		add_filter( Split_Orders::hook_prefix( 'meta_fields' ), array( __CLASS__, 'meta_fields' ), 10, 3 );
		add_filter( Split_Orders::hook_prefix( 'pre_split_notices' ), array( __CLASS__, 'uncaptured_notice' ), 10, 2 );
	}

	/**
	 * Adds WooCommerce Payments specific meta fields to copy to the target order, if the payment method on the source
	 * was woocommerce payments.
	 *
	 * @param array $meta_fields The array to add meta fields to
	 * @param WC_Order $target_order The order that the meta fields will be copied to
	 * @param WC_Order $source_order The order that the meta fields will be copied from
	 *
	 * @return array The updated array of meta fields
	 */
	public static function meta_fields( array $meta_fields, WC_Order $target_order, WC_Order $source_order ) {
		$payment_method = $source_order->get_payment_method();

		if ( 'woocommerce_payments' == $payment_method ) {
			$meta_fields[] = '_payment_method_id';
			$meta_fields[] = '_stripe_customer_id';
			$meta_fields[] = '_charge_id';
		}

		return $meta_fields;
	}

	/**
	 * Adds a pre-splitting warning notice if payment has not been captured yet
	 *
	 * @param array    $notices The notices array to add the warning to
	 * @param WC_Order $order   The order being split, to check if payment has been captured
	 */
	public static function uncaptured_notice( array $notices, WC_Order $order ) {
		$payment_method = $order->get_payment_method();

		if ( 'woocommerce_payments' === $payment_method && $order->get_meta( '_intention_status' ) === 'requires_capture' ) {
			$notices = Admin::add_notice( $notices, __( 'WooCommerce Payments does not support capturing payments in multiple parts. <br />Capturing payment before splitting is recommended.', 'split-orders' ), 'warning' );
		}

		return $notices;
	}
}
