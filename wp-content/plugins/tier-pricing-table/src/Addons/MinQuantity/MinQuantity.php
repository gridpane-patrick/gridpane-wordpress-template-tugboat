<?php namespace TierPricingTable\Addons\MinQuantity;

use TierPricingTable\Addons\AbstractAddon;
use TierPricingTable\PriceManager;

class MinQuantity extends AbstractAddon {

	public function getName() {
		return __( 'Real minimum', 'tier-pricing-table' );
	}

	public function isActive() {
		return apply_filters( 'tier_pricing_table/addons/min_quantity_active', true, $this );
	}

	public function run() {

		add_filter( 'woocommerce_quantity_input_args', function ( $args ) {
			global $product;

			if ( $product && $product instanceof \WC_Product_Simple ) {
				$min               = PriceManager::getProductQtyMin( $product->get_id() );
				$min               = max( 1, $min - getProductCartQuantity( $product->get_id() ) );
				$args['min_value'] = $min;
			}

			return $args;
		} );

		add_filter( 'woocommerce_add_to_cart_validation', function ( $passed, $product_id, $qty ) {
			$min = PriceManager::getProductQtyMin( $product_id );
			$min = max( 1, $min - getProductCartQuantity( $product_id ) );

			if ( $qty < $min ) {

				wc_add_notice( sprintf( 'Minimum quantity for the product is %s', $min ), 'error' );

				return false;
			}

			return $passed;

		}, 10, 3 );

		add_filter( 'woocommerce_update_cart_validation', function ( $passed, $cart_item_key, $values, $quantity ) {
			$productId = $values['variation_id'] ? $values['variation_id'] : $values['product_id'];
			$min       = PriceManager::getProductQtyMin( $productId );

			if ( $quantity < $min ) {
				wc_add_notice( sprintf( 'Minimum quantity for the product is %s', $min ), 'error' );

				return false;
			}

			return $passed;

		}, 10, 4 );

		add_filter( 'woocommerce_available_variation', function ( $variation ) {
			$min = PriceManager::getProductQtyMin( $variation['variation_id'], 'view' );

			$min = max( 1, $min - getProductCartQuantity( $variation['variation_id'] ) );

			$variation['min_qty']   = $min;
			$variation['qty_value'] = $min;

			return $variation;
		} );


		function getProductCartQuantity( $product_id ) {
			$qty = 0;

			if ( is_array( wc()->cart->cart_contents ) ) {
				foreach ( wc()->cart->cart_contents as $cart_content ) {
					if ( $cart_content['product_id'] == $product_id ) {
						$qty += $cart_content['quantity'];
					}
				}
			}

			return $qty;
		}
	}
}
