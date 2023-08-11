<?php

namespace Vibe\Split_Orders;

use Vibe\Split_Orders\Emails\Customer_Order_Split;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class Emails {

	/**
	 * Creates an instance and sets up hooks related to emails
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_classes', array( __CLASS__, 'email_classes' ) );
	}

	/**
	 * Adds our emails classes to the WooCommerce email classes array
	 *
	 * @param array $emails The WooCommerce email classes
	 *
	 * @return array WooCommerce email classes, with ours added
	 */
	public static function email_classes( array $emails ) {
		$emails['Customer_Order_Split'] = new Customer_Order_Split();

		return $emails;
	}
}
