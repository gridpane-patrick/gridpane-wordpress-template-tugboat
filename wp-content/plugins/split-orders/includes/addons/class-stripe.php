<?php

namespace Vibe\Split_Orders\Addons;

use Vibe\Split_Orders\Admin;
use Vibe\Split_Orders\Split_Orders;
use WC_Order;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Provides support for the Stripe payment gateway
 *
 * @since 1.5
 */
class Stripe {

	/**
	 * Creates an instance and sets up hooks to provide Stripe specific support
	 */
	public function __construct() {
		add_filter( Split_Orders::hook_prefix( 'meta_fields' ), array( __CLASS__, 'meta_fields' ), 10, 3 );
		add_filter( Split_Orders::hook_prefix( 'pre_split_notices' ), array( __CLASS__, 'uncaptured_notice' ), 10, 2 );
	}

	/**
	 * Adds stripe specific meta fields to copy to the target order, if the payment method on the source was stripe.
	 *
	 * @param array    $meta_fields  The array to add meta fields to
	 * @param WC_Order $target_order The order that the meta fields will be copied to
	 * @param WC_Order $source_order The order that the meta fields will be copied from
	 *
	 * @return array The updated array of meta fields
	 */
	public static function meta_fields( array $meta_fields, WC_Order $target_order, WC_Order $source_order ) {
		$payment_method = $source_order->get_payment_method();

		if ( 'stripe' == $payment_method ) {
			// _stripe_fee and _stripe_net remain on the original order - in case of refund, payout will go negative
			$meta_fields[] = '_stripe_charge_captured';
			$meta_fields[] = '_stripe_customer_id';
			$meta_fields[] = '_stripe_currency';
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

		if ( 'stripe' === $payment_method && $order->get_meta( '_stripe_charge_captured' ) !== 'yes' ) {
			$notices = Admin::add_notice( $notices, __( 'Stripe does not support capturing payments in multiple parts. <br />Capturing payment before splitting is recommended.', 'split-orders' ), 'warning' );
		}

		return $notices;
	}
}
