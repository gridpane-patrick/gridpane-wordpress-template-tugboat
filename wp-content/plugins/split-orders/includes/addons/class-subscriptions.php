<?php

namespace Vibe\Split_Orders\Addons;

use Vibe\Split_Orders\Split_Orders;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Provides support for Subscriptions plugin
 *
 * @since 1.4
 */
class Subscriptions {

	/**
	 * Creates an instance and sets up hooks to integrate with the rest of the extension, only if Subscriptions is
	 * installed
	 */
	public function __construct() {
		add_action( Split_Orders::hook_prefix( 'is_splittable_screen' ), array( __CLASS__, 'is_splittable_screen' ) );
	}

	/**
	 * Filters the screens splitting action is displayed on to remove from subscription orders
	 *
	 * Subscription parent orders can potentially be split as they may contain non-subscription products.
	 *
	 * @param bool $is_splittable If the current screen is one to support a splitting action or not
	 * @return bool False if the order is for a subscription renewal, resubscribe or switch, otherwise the core logic is
	 *              used to determine if the screen is suitable for splitting.
	 */
	public static function is_splittable_screen( $is_splittable ) {
		if ( $is_splittable && function_exists( 'wcs_order_contains_subscription' ) ) {
			// Attempt to get current order
			$order = wc_get_order();

			if ( $order && wcs_order_contains_subscription( $order, array( 'renewal', 'switch', 'resubscribe' ) ) ) {
				$is_splittable = false;
			}
		}

		return $is_splittable;
	}
}
