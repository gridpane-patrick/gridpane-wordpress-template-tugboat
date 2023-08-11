<?php

namespace Vibe\Split_Orders\Addons;

use Vibe\Split_Orders\Admin;
use Vibe\Split_Orders\Orders;
use Vibe\Split_Orders\Split_Orders;
use WC_Order;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Provides support for the Braintree payment gateway
 *
 * @since 1.5
 */
class Braintree {

	/**
	 * Creates an instance and sets up hooks to provide Braintree specific support
	 */
	public function __construct() {
		add_filter( Split_Orders::hook_prefix( 'meta_fields' ), array( __CLASS__, 'meta_fields' ), 10, 3 );
		add_filter( Split_Orders::hook_prefix( 'pre_split_notices' ), array( __CLASS__, 'uncaptured_notice' ), 10, 2 );

		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'output_refund_warning_script' ) );
	}

	/**
	 * Adds braintree specific meta fields to copy to the target order, if the payment method on the source was braintree.
	 *
	 * @param array    $meta_fields  The array to add meta fields to
	 * @param WC_Order $target_order The order that the meta fields will be copied to
	 * @param WC_Order $source_order The order that the meta fields will be copied from
	 *
	 * @return array The updated array of meta fields
	 */
	public static function meta_fields( array $meta_fields, WC_Order $target_order, WC_Order $source_order ) {
		$payment_method = $source_order->get_payment_method();

		if ( 'braintree_credit_card' === $payment_method ) {
			$prefix            = '_wc_braintree_credit_card_';
			$additional_fields = array( 'environment', 'charge_captured' );
		} elseif ( 'braintree_paypal' === $payment_method ) {
			$prefix            = '_wc_braintree_paypal_';
			$additional_fields = array(
				'environment',
				'charge_captured',
				'trans_id',
				'trans_date',
				'payer_email',
				'payment_id'
			);
		}

		foreach ( $additional_fields as $field ) {
			$meta_fields[] = $prefix . $field;
		}

		return $meta_fields;
	}

	/**
	 * Outputs an inline script to display a warning message about refunds on split orders
	 *
	 * The script is only output on admin order pages for split orders paid with braintree credit card.
	 */
	public static function output_refund_warning_script() {
		// Check we're on the order single admin screen
		$screen      = get_current_screen();
		$screen_base = isset( $screen->base ) ? $screen->base : '';
		$screen_id   = isset( $screen->id ) ? $screen->id : '';

		if ( 'post' !== $screen_base || 'shop_order' !== $screen_id ) {
			return;
		}

		$order = wc_get_order();

		// Check the order has been split and the payment method was braintree credit card (paypal settles immediately)
		if ( ! $order instanceof WC_Order || ! Orders::is_split( $order ) || $order->get_payment_method() !== 'braintree_credit_card' ) {
			return;
		}

		$message = __( '<strong>Warning:</strong> Refunding a split order before the payment has settled may void the whole transaction.', 'split-orders' );
		?>

		<script>
			jQuery( '.wc-order-refund-items .wc-order-totals' ).after( '<div class="clear"></div><p style="font-style: italic;"><?php echo wp_kses_post( $message ); ?></p>' );
		</script>

		<?php
	}

	/**
	 * Adds a pre-splitting warning notice if payment has not been captured yet
	 *
	 * @param array    $notices The notices array to add the warning to
	 * @param WC_Order $order   The order being split, to check if payment has been captured
	 */
	public static function uncaptured_notice( array $notices, WC_Order $order ) {
		$payment_method = $order->get_payment_method();

		if ( 'braintree_credit_card' === $payment_method && $order->get_meta( '_wc_braintree_credit_card_charge_captured' ) !== 'yes' ) {
			$notices = Admin::add_notice( $notices, __( 'Braintree does not support capturing payments in multiple parts. <br />Capturing payment before splitting is recommended.', 'split-orders' ), 'warning' );
		}

		return $notices;
	}
}
