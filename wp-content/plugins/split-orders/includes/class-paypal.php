<?php

namespace Vibe\Split_Orders;

use WC_Order;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Provides support for the PayPal Standard payment gateway
 *
 * @since 1.5
 */
class PayPal {

	/**
	 * Creates an instance and sets up hooks to provide PayPal specific support
	 */
	public function __construct() {
		add_filter( Split_Orders::hook_prefix( 'pre_split_notices' ), array( __CLASS__, 'uncaptured_notice' ), 10, 2 );
	}

	/**
	 * Adds a pre-splitting warning notice if payment has not been captured yet
	 *
	 * @param array    $notices The notices array to add the warning to
	 * @param WC_Order $order   The order being split, to check if payment has been captured
	 */
	public static function uncaptured_notice( array $notices, WC_Order $order ) {
		$payment_method = $order->get_payment_method();

		if ( 'paypal' === $payment_method && $order->get_meta( '_paypal_status' ) === 'authorized' ) {
			$notices = Admin::add_notice( $notices, __( 'PayPal does not support capturing payments in multiple parts. <br />Capturing payment before splitting is recommended.', 'split-orders' ), 'warning' );
		}

		return $notices;
	}
}
