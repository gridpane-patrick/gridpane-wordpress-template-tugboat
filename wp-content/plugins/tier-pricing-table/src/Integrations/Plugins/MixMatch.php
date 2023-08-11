<?php namespace TierPricingTable\Integrations\Plugins;

class MixMatch {

	public function __construct() {
		add_filter( 'tier_pricing_table/cart/need_price_recalculation', function ( $bool, $cart_item ) {

			if ( isset( $cart_item['mnm_container'] ) ) {
				return false;
			}

			return $bool;

		}, 10, 2 );
	}
}
