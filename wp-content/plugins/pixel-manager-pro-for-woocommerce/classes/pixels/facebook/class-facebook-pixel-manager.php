<?php

namespace WCPM\Classes\Pixels\Facebook;

use WCPM\Classes\Http\Facebook_CAPI;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Facebook_Pixel_Manager {

	protected $facebook_capi;

	public function __construct( $options ) {

		if (true && $options['facebook']['capi']['token']) {

			$this->facebook_capi = new Facebook_CAPI($options);

			// Save the Facebook session identifiers on the order so that we can use them later when the order gets paid or completed
			// https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-checkout.html#source-view.403
			add_action('woocommerce_checkout_order_created', [$this, 'facebook_save_session_identifiers_on_order__premium_only']);

			// Process the purchase through Facebook CAPI when they are paid,
			// or when they are manually completed.

			add_action('woocommerce_order_status_on-hold', [$this, 'facebook_capi_report_purchase__premium_only']);
			add_action('woocommerce_order_status_processing', [$this, 'facebook_capi_report_purchase__premium_only']);
			add_action('woocommerce_payment_complete', [$this, 'facebook_capi_report_purchase__premium_only']);
			add_action('woocommerce_order_status_completed', [$this, 'facebook_capi_report_purchase__premium_only']);

			// Process subscription renewals
			// https://docs.woocommerce.com/document/subscriptions/develop/action-reference/
//        add_action('woocommerce_subscription_renewal_payment_complete', [$this, 'facebook_capi_report_subscription_purchase_renewal__premium_only']);
		}
	}

	public function facebook_save_session_identifiers_on_order__premium_only( $order ) {
		$this->facebook_capi->set_identifiers_on_order($order);
	}

	public function facebook_capi_report_purchase__premium_only( $order_id ) {

		$order = wc_get_order($order_id);

		$this->facebook_capi->send_purchase_hit($order);
	}
}
