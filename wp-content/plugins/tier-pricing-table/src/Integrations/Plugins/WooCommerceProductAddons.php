<?php namespace TierPricingTable\Integrations\Plugins;

class WooCommerceProductAddons {

	/**
	 * WooCommerceProductAddons constructor.
	 */
	public function __construct() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {

			add_action( 'wp_head', array( $this, 'addCompatibilityScript' ) );

			add_action( 'tier_pricing_table/cart/product_cart_price', array( $this, 'addAddonsPrice' ), 10, 2 );
			add_action( 'tier_pricing_table/cart/product_cart_price/item', array( $this, 'addAddonsPrice' ), 10, 2 );
		}
	}

	/**
	 * Add extra addons costs to product price in cart.
	 *
	 * @param float $price
	 * @param array $cart_item
	 *
	 * @return int|mixed
	 */
	public function addAddonsPrice( $price, $cart_item ) {

		$extra_cost = 0;

		if ( isset( $cart_item['addons'] ) && false != $price ) {
			foreach ( $cart_item['addons'] as $addon ) {
				$price_type    = $addon['price_type'];
				$addon_price   = $addon['price'];

				switch ( $price_type ) {

					case 'percentage_based':
						$extra_cost += (float) ( $price * ( $addon_price / 100 ) );
						break;
					case 'flat_fee':
						$extra_cost += (float) ( $addon_price / $cart_item['quantity'] );
						break;
					default:
						$extra_cost += (float) $addon_price;
						break;
				}
			}

			return $price + $extra_cost;
		}

		return $price;

	}

	/**
	 * Render compatibility script
	 */
	public function addCompatibilityScript() {
		?>
		<script>
			(function ($) {
				$(document).on('tiered_price_update', function (event, data) {
					$('#product-addons-total').data('price', data.price);
				});
			})(jQuery);
		</script>
		<?php
	}
}
