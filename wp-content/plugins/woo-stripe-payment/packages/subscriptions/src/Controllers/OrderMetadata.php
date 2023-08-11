<?php

namespace PaymentPlugins\Stripe\WooCommerceSubscriptions\Controllers;

class OrderMetadata {

	public function __construct() {
		$this->initialize();
	}

	private function initialize() {
		add_action( 'wc_stripe_save_order_meta', [ $this, 'save_order_metadata' ], 10, 4 );
	}

	/**
	 * @param \WC_Order                  $order
	 * @param \WC_Payment_Gateway_Stripe $payment_method
	 * @param \Stripe\Charge             $charge
	 * @param \WC_Payment_Token_Stripe   $token
	 *
	 * @return void
	 * @throws \WC_Data_Exception
	 */
	public function save_order_metadata( $order, $payment_method, $charge = null, $token = null ) {
		// if WCS is active and there are subscriptions in the order, save meta data
		if ( wcs_stripe_active() && wcs_order_contains_subscription( $order ) ) {
			if ( $charge ) {
				foreach ( wcs_get_subscriptions_for_order( $order ) as $subscription ) {
					$subscription->set_transaction_id( $charge->id );
					$subscription->set_payment_method_title( $token->get_payment_method_title() );
					$subscription->update_meta_data( \WC_Stripe_Constants::MODE, wc_stripe_mode() );
					$subscription->update_meta_data( \WC_Stripe_Constants::CHARGE_STATUS, $charge->status );
					$subscription->update_meta_data( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $token->get_token() );
					$subscription->update_meta_data( \WC_Stripe_Constants::CUSTOMER_ID, wc_stripe_get_customer_id( $order->get_user_id() ) );
					if ( isset( $charge->payment_method_details->card->mandate ) ) {
						$subscription->update_meta_data( \WC_Stripe_Constants::STRIPE_MANDATE, $charge->payment_method_details->card->mandate );
					} elseif ( $payment_method->is_mandate_required( $order ) ) {
						// load the token from the database so it's mandate can be added to the subscription
						$token = $payment_method->get_token( $token->get_token(), $token->get_user_id() );
						if ( $token ) {
							$subscription->update_meta_data( \WC_Stripe_Constants::STRIPE_MANDATE, $token->get_meta( \WC_Stripe_Constants::STRIPE_MANDATE ) );
						}
					}
					$subscription->save();
				}
			} else {
				$this->save_zero_total_order_metadata( $order, $payment_method, $token );
			}
		}
	}

	/**
	 * @param \WC_Order                  $order
	 * @param \WC_Payment_Gateway_Stripe $payment_method
	 * @param \WC_Payment_Token_Stripe   $token
	 *
	 * @return void
	 */
	private function save_zero_total_order_metadata( $order, $payment_method, $token ) {
		if ( wcs_stripe_active() && wcs_order_contains_subscription( $order ) ) {
			foreach ( wcs_get_subscriptions_for_order( $order ) as $subscription ) {
				/**
				 *
				 * @var WC_Subscription $subscription
				 */
				$subscription->set_payment_method_title( $token->get_payment_method_title() );
				$subscription->update_meta_data( \WC_Stripe_Constants::MODE, wc_stripe_mode() );
				$subscription->update_meta_data( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $token->get_token() );
				$subscription->update_meta_data( \WC_Stripe_Constants::CUSTOMER_ID, wc_stripe_get_customer_id( $order->get_user_id() ) );
				if ( $payment_method->is_mandate_required( $order ) ) {
					$subscription->update_meta_data( \WC_Stripe_Constants::STRIPE_MANDATE, $order->get_meta( \WC_Stripe_Constants::STRIPE_MANDATE ) );
				}
				$subscription->save();
			}
		}
	}

}