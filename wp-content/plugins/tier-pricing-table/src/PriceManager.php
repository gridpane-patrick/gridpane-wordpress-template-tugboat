<?php namespace TierPricingTable;

use WC_Product;

class PriceManager {

	/**
	 * Return fixed price rules or empty array if not exist rules
	 *
	 * @param $product_id
	 * @param string $context
	 *
	 * @return array
	 */
	public static function getFixedPriceRules( $product_id, $context = 'view' ) {
		return self::getPriceRules( $product_id, 'fixed', $context );
	}

	/**
	 * Return percentage price rules or empty array if not exist rules
	 *
	 * @param $product_id
	 * @param string $context
	 *
	 * @return array
	 */
	public static function getPercentagePriceRules( $product_id, $context = 'view' ) {
		return self::getPriceRules( $product_id, 'percentage', $context );
	}

	/**
	 * Get product pricing rules
	 *
	 * @param int $product_id
	 * @param bool $type
	 * @param string $context
	 *
	 * @return array
	 */
	public static function getPriceRules( $product_id, $type = false, $context = 'view' ) {

		$type = $type ? $type : self::getPricingType( $product_id, 'fixed', $context );

		if ( 'fixed' === $type ) {
			$rules = get_post_meta( $product_id, '_fixed_price_rules', true );
		} else {
			$rules = get_post_meta( $product_id, '_percentage_price_rules', true );
		}

		$parent_id = $product_id;

		// If no rules for variation check for product level rules.
		if ( 'edit' !== $context && self::variationHasNoOwnRules( $product_id, $rules ) ) {

			$product = wc_get_product( $product_id );

			$parent_id = $product->get_parent_id();

			$type = self::getPricingType( $parent_id );

			if ( 'fixed' === $type ) {
				$rules = get_post_meta( $parent_id, '_fixed_price_rules', true );
			} else {
				$rules = get_post_meta( $parent_id, '_percentage_price_rules', true );
			}
		}

		$rules = ! empty( $rules ) ? $rules : array();

		ksort( $rules );

		if ( 'edit' !== $context ) {
			$rules = apply_filters( 'tier_pricing_table/price/product_price_rules', $rules, $product_id, $type, $parent_id );

		}

		return $rules;
	}

	/**
	 * Get price by product quantity
	 *
	 * @param int $quantity
	 * @param int $product_id
	 * @param string $context
	 * @param string $place
	 *
	 * @return bool|float|int
	 */
	public static function getPriceByRules( $quantity, $product_id, $context = 'view', $place = 'shop' ) {

		$rules = self::getPriceRules( $product_id );

		$type = self::getPricingType( $product_id );

		if ( 'fixed' === $type ) {
			foreach ( array_reverse( $rules, true ) as $_amount => $price ) {
				if ( $_amount <= $quantity ) {

					$product_price = $price;

					if ( 'view' === $context ) {
						$product = wc_get_product( $product_id );

						$product_price = self::getPriceWithTaxes( $product_price, $product, $place );
					}

					break;
				}
			}
		}

		if ( 'percentage' === $type ) {
			$product = wc_get_product( $product_id );

			foreach ( array_reverse( $rules, true ) as $_amount => $percentDiscount ) {
				if ( $_amount <= $quantity ) {

					$product_price = self::getPriceByPercentDiscount( $product->get_price(), $percentDiscount );

					if ( 'view' === $context ) {

						$product = wc_get_product( $product_id );

						$product_price = self::getPriceWithTaxes( $product_price, $product, $place );
					}

					break;
				}
			}
		}

		$product_price = isset( $product_price ) ? $product_price : false;

		return apply_filters( 'tier_pricing_table/price/price_by_rules', $product_price, $quantity, $product_id, $context, $place );
	}

	/**
	 * Calculate displayed price depend on taxes
	 *
	 * @param float $price
	 * @param WC_Product $product
	 * @param string $place
	 *
	 * @return float
	 */
	public static function getPriceWithTaxes( $price, $product, $place = 'shop' ) {

		if ( wc_tax_enabled() ) {

			if ( 'cart' === $place ) {
				$price = 'incl' === get_option( 'woocommerce_tax_display_cart' ) ?

					wc_get_price_including_tax( $product, array(
							'qty'   => 1,
							'price' => $price
						)
					) :

					wc_get_price_excluding_tax( $product, array(
							'qty'   => 1,
							'price' => $price
						)
					);
			} else {
				$price = wc_get_price_to_display( $product, array(
					'price' => $price,
					'qty'   => 1,
				) );
			}
		}

		return $price;
	}


	/**
	 * Calculate price using percentage discount
	 *
	 * @param float|int $price
	 * @param float|int $discount
	 *
	 * @return bool|float|int
	 */
	public static function getPriceByPercentDiscount( $price, $discount ) {
		if ( $price > 0 && $discount <= 100 ) {
			$discount_amount = ( $price / 100 ) * $discount;

			return $price - $discount_amount;
		}

		return false;
	}

	/**
	 * Get pricing type of product. Available: fixed or percentage
	 *
	 * @param int $product_id
	 * @param string $default
	 * @param string $context
	 *
	 * @return string
	 */
	public static function getPricingType( $product_id, $default = 'fixed', $context = 'view' ) {

		$type = get_post_meta( $product_id, '_tiered_price_rules_type', true );

		if ( 'view' === $context && self::variationHasNoOwnRules( $product_id ) ) {
			$product = wc_get_product( $product_id );

			$type = get_post_meta( $product->get_parent_id(), '_tiered_price_rules_type', true );
		}

		$type = in_array( $type, array( 'fixed', 'percentage' ) ) ? $type : $default;

		return apply_filters( 'tier_pricing_table/price/type', $type, $product_id, $context );
	}

	/**
	 * Update price rules
	 *
	 * @param array $amounts
	 * @param array $prices
	 * @param int $product_id
	 */
	public static function updateFixedPriceRules( $amounts, $prices, $product_id ) {
		$rules = array();

		foreach ( $amounts as $key => $amount ) {
			if ( ! empty( $amount ) && ! empty( $prices[ $key ] ) && ! key_exists( $amount, $rules ) ) {
				$rules[ $amount ] = wc_format_decimal( $prices[ $key ] );
			}
		}

		update_post_meta( $product_id, '_fixed_price_rules', $rules );
	}

	/**
	 * Update price rules
	 *
	 * @param array $amounts
	 * @param array $percents
	 * @param int $product_id
	 */
	public static function updatePercentagePriceRules( $amounts, $percents, $product_id ) {
		$rules = array();

		foreach ( $amounts as $key => $amount ) {
			if ( ! empty( $amount ) && ! empty( $percents[ $key ] ) && ! key_exists( $amount,
					$rules ) && $percents[ $key ] < 99 ) {
				$rules[ $amount ] = $percents[ $key ];
			}
		}

		update_post_meta( $product_id, '_percentage_price_rules', $rules );
	}

	/**
	 * Update product pricing type
	 *
	 * @param int $product_id
	 * @param string $type
	 */
	public static function updatePriceRulesType( $product_id, $type ) {
		if ( in_array( $type, array( 'percentage', 'fixed' ) ) ) {
			update_post_meta( $product_id, '_tiered_price_rules_type', $type );
		}
	}

	/**
	 * Get minimum product qty for table
	 *
	 * @param int $product_id
	 * @param string $context
	 *
	 * @return int
	 */
	public static function getProductQtyMin( $product_id, $context = 'view' ) {

		$currentProductId = $product_id;
		$parentId         = false;

		if ( 'view' === $context && self::variationHasNoOwnRules( $product_id ) ) {
			$product          = wc_get_product( $product_id );
			$parentId         = $product->get_parent_id();
			$currentProductId = $parentId;
		}

		$min = get_post_meta( $currentProductId, '_tiered_price_minimum_qty', true );


		$min = $min ? intval( $min ) : 1;

		if ( 'view' === $context ) {
			return apply_filters( 'tier_pricing_table/price/minimum', $min, $product_id, $parentId );
		}

		return $min;
	}

	/**
	 * Update product min
	 *
	 * @param int $product_id
	 * @param int $min
	 */
	public static function updateProductQtyMin( $product_id, $min ) {

		$min = intval( $min );

		if ( $min > 0 ) {
			update_post_meta( $product_id, '_tiered_price_minimum_qty', $min );
		}
	}

	/**
	 * Check if variation has no own rules
	 *
	 * @param int $product_id
	 * @param bool $rules
	 *
	 * @return bool
	 */
	protected static function variationHasNoOwnRules( $product_id, $rules = false ) {

		$rules = $rules ? $rules : self::getPriceRules( $product_id, false, 'edit' );

		if ( empty( $rules ) ) {

			$product = wc_get_product( $product_id );

			return $product->is_type( 'variation' );
		}

		return false;
	}
}
