<?php namespace TierPricingTable\Integrations\Plugins;

class WooCommerceDeposits {

	public function __construct() {
		add_filter( 'tier_pricing_table/cart/product_cart_price', function ( $new_price, $cart_item, $key ) {

			if ( $new_price ) {
				// WooCommerce Deposit
				$cart = wc()->cart;

				if ( isset( $cart->cart_contents[ $key ]['full_amount'] ) ) {

					$depositPercentage = 1 / ( $cart->cart_contents[ $key ]['full_amount'] / $cart->cart_contents[ $key ]['deposit_amount'] );

					$cart->cart_contents[ $key ]['full_amount']    = $new_price;
					$cart->cart_contents[ $key ]['deposit_amount'] = $cart->cart_contents[ $key ]['full_amount'] * $depositPercentage;
				}
			}

			return $new_price;

		}, 10, 3 );
	}
}
