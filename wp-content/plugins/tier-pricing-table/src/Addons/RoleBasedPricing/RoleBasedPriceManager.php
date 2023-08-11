<?php namespace TierPricingTable\Addons\RoleBasedPricing;

class RoleBasedPriceManager {

	public static function roleHasRules( $role, $product_id, $context = 'view' ) {

		$product = wc_get_product( $product_id );

		$parentRoleRulesExists = false;

		$productRoleRulesExists = metadata_exists( 'post', $product_id, "_{$role}_percentage_price_rules" )
								  || metadata_exists( 'post', $product_id, "_{$role}_fixed_price_rules" )
								  || metadata_exists( 'post', $product_id, "_{$role}_tiered_price_rules_type" )
								  || metadata_exists( 'post', $product_id, "_{$role}_tiered_price_minimum_qty" );


		if ( $product->is_type( 'variation' ) && 'edit' !== $context ) {

			$parentRoleRulesExists = metadata_exists( 'post', $product->get_parent_id(), "_{$role}_percentage_price_rules" )
									 || metadata_exists( 'post', $product->get_parent_id(), "_{$role}_fixed_price_rules" )
									 || metadata_exists( 'post', $product->get_parent_id(), "_{$role}_tiered_price_rules_type" )
									 || metadata_exists( 'post', $product->get_parent_id(), "_{$role}_tiered_price_minimum_qty" );

		}

		return $productRoleRulesExists || $parentRoleRulesExists;
	}

	public static function deleteAllDataForRole( $product_id, $role ) {
		delete_post_meta( $product_id, "_{$role}_tiered_price_regular_price" );
		delete_post_meta( $product_id, "_{$role}_tiered_price_sale_price" );
		delete_post_meta( $product_id, "_{$role}_percentage_price_rules" );
		delete_post_meta( $product_id, "_{$role}_fixed_price_rules" );
		delete_post_meta( $product_id, "_{$role}_tiered_price_rules_type" );
		delete_post_meta( $product_id, "_{$role}_tiered_price_minimum_qty" );
	}

	/**
	 * Return fixed price rules or empty array if not exist rules
	 *
	 * @param int $product_id
	 * @param string $role
	 * @param string $context
	 *
	 * @return array
	 */
	public static function getFixedPriceRules( $product_id, $role, $context = 'view' ) {
		return self::getPriceRules( $product_id, $role, 'fixed', $context );
	}

	/**
	 * Return percentage price rules or empty array if not exist rules
	 *
	 * @param $product_id
	 * @param $role
	 * @param string $context
	 *
	 * @return array
	 */
	public static function getPercentagePriceRules( $product_id, $role, $context = 'view' ) {
		return self::getPriceRules( $product_id, $role, 'percentage', $context );
	}

	/**
	 * Get product pricing rules for role
	 *
	 * @param int $product_id
	 * @param string $role
	 * @param bool $type
	 * @param string $context
	 *
	 * @return array
	 */
	public static function getPriceRules( $product_id, $role, $type = false, $context = 'view' ) {

		$type = $type ? $type : self::getPricingType( $product_id, $role, 'fixed', $context );

		if ( 'fixed' === $type ) {
			$rules = get_post_meta( $product_id, "_{$role}_fixed_price_rules", true );
		} else {
			$rules = get_post_meta( $product_id, "_{$role}_percentage_price_rules", true );
		}

		// If no rules for variation check for product level rules.
		if ( 'edit' !== $context && self::variationHasNoOwnRules( $product_id, $role, $rules ) ) {

			$product = wc_get_product( $product_id );

			$product_id = $product->get_parent_id();

			$type = self::getPricingType( $product_id, $role );

			if ( 'fixed' === $type ) {
				$rules = get_post_meta( $product_id, "_{$role}_fixed_price_rules", true );
			} else {
				$rules = get_post_meta( $product_id, "_{$role}_percentage_price_rules", true );
			}
		}

		$rules = ! empty( $rules ) ? $rules : array();

		ksort( $rules );

		if ( 'edit' !== $context ) {

			$rules = apply_filters( 'tier_pricing_table/role_based_rules/price/product_price_rules', $rules, $product_id, $type );
		}

		return $rules;
	}


	/**
	 * Get pricing type of product. Available: fixed or percentage
	 *
	 * @param int $product_id
	 * @param string $role
	 * @param string $default
	 * @param string $context
	 *
	 * @return string
	 */
	public static function getPricingType( $product_id, $role, $default = 'fixed', $context = 'view' ) {

		$type = get_post_meta( $product_id, "_{$role}_tiered_price_rules_type", true );

		// think about it
		if ( 'view' === $context && self::variationHasNoOwnRules( $product_id, $role ) ) {
			$product = wc_get_product( $product_id );

			$type = get_post_meta( $product->get_parent_id(), "_{$role}_tiered_price_rules_type", true );
		}

		$type = in_array( $type, array( 'fixed', 'percentage' ) ) ? $type : $default;

		if ( 'edit' !== $context ) {
			return apply_filters( 'tier_pricing_table/role_based_rules/price/type', $type, $role, $product_id );
		}

		return $type;
	}

	/**
	 * Update price rules for certain role
	 *
	 * @param array $amounts
	 * @param array $prices
	 * @param int $product_id
	 * @param string $role
	 */
	public static function updateFixedPriceRules( $amounts, $prices, $product_id, $role ) {
		$rules = array();

		foreach ( $amounts as $key => $amount ) {
			if ( ! empty( $amount ) && ! empty( $prices[ $key ] ) && ! key_exists( $amount, $rules ) ) {
				$rules[ $amount ] = wc_format_decimal( $prices[ $key ] );
			}
		}

		update_post_meta( $product_id, "_{$role}_fixed_price_rules", $rules );
	}

	/**
	 * Update price rules for certain role
	 *
	 * @param array $amounts
	 * @param array $percents
	 * @param int $product_id
	 * @param string $role
	 */
	public static function updatePercentagePriceRules( $amounts, $percents, $product_id, $role ) {
		$rules = array();

		foreach ( $amounts as $key => $amount ) {
			if ( ! empty( $amount ) && ! empty( $percents[ $key ] ) && ! key_exists( $amount,
					$rules ) && $percents[ $key ] < 99 ) {
				$rules[ $amount ] = $percents[ $key ];
			}
		}

		update_post_meta( $product_id, "_{$role}_percentage_price_rules", $rules );
	}

	/**
	 * Update product pricing type for role
	 *
	 * @param int $product_id
	 * @param string $type
	 * @param string $role
	 */
	public static function updatePriceRulesType( $product_id, $type, $role ) {
		if ( in_array( $type, array( 'percentage', 'fixed' ) ) ) {
			update_post_meta( $product_id, "_{$role}_tiered_price_rules_type", $type );
		}
	}

	/**
	 * Get minimum product qty for table for role
	 *
	 * @param int $product_id
	 * @param string $role
	 * @param string $context
	 *
	 * @return int
	 */
	public static function getProductQtyMin( $product_id, $role, $context = 'view' ) {

		$currentProductId = $product_id;
		$parentId         = false;

		if ( 'view' === $context && self::variationHasNoOwnRules( $product_id, $role ) ) {
			$product          = wc_get_product( $product_id );
			$parentId         = $product->get_parent_id();
			$currentProductId = $parentId;
		}

		$min = get_post_meta( $currentProductId, "_{$role}_tiered_price_minimum_qty", true );

		$min = $min ? intval( $min ) : 1;

		if ( 'view' === $context ) {
			return apply_filters( 'tier_pricing_table/role_based_rules/price/minimum', $min, $role, $product_id, $parentId );
		}

		return $min;
	}

	/**
	 * Update product min for certain role
	 *
	 * @param int $product_id
	 * @param int $min
	 * @param string $role
	 */
	public static function updateProductQtyMin( $product_id, $min, $role ) {

		$min = intval( $min );

		if ( $min > 0 ) {
			update_post_meta( $product_id, "_{$role}_tiered_price_minimum_qty", $min );
		}
	}

	/**
	 * Update product regular price for certain role
	 *
	 * @param int $product_id
	 * @param float|string $price
	 * @param string $role
	 */
	public static function updateRegularRolePrice( $product_id, $price, $role ) {

		$price = $price ? floatval( $price ) : '';

		update_post_meta( $product_id, "_{$role}_tiered_price_regular_price", $price );
	}

	/**
	 * Update product sale price for certain role
	 *
	 * @param int $product_id
	 * @param float|string $price
	 * @param string $role
	 */
	public static function updateSaleRolePrice( $product_id, $price, $role ) {

		$price = $price ? floatval( $price ) : '';

		update_post_meta( $product_id, "_{$role}_tiered_price_sale_price", $price );
	}

	/**
	 * Get regular product price for table for role
	 *
	 * @param int $product_id
	 * @param string $role
	 * @param string $context
	 *
	 * @return int
	 */
	public static function getProductRegularRolePrice( $product_id, $role, $context = 'view' ) {

		$price = get_post_meta( $product_id, "_{$role}_tiered_price_regular_price", true );

		if ( 0 != $price ) {
			$price = $price ? floatval( $price ) : '';
		}

		if ( 'edit' !== $context ) {
			return apply_filters( 'tier_pricing_table/role_based_rules/price/regular_price', $price, $role, $product_id );
		}

		return $price;
	}

	/**
	 * Get regular product price for table for role
	 *
	 * @param int $product_id
	 * @param string $role
	 * @param string $context
	 *
	 * @return int
	 */
	public static function getProductSaleRolePrice( $product_id, $role, $context = 'view' ) {

		$price = get_post_meta( $product_id, "_{$role}_tiered_price_sale_price", true );

		if ( 0 != $price ) {
			$price = $price ? floatval( $price ) : '';
		}

		if ( 'edit' !== $context ) {
			return apply_filters( 'tier_pricing_table/role_based_rules/price/sale_price', $price, $role, $product_id );
		}

		return $price;
	}

	/**
	 * Check if variation has no own rules
	 *
	 * @param int $product_id
	 * @param string $role
	 * @param bool $rules
	 *
	 * @return bool
	 */
	protected static function variationHasNoOwnRules( $product_id, $role, $rules = false ) {

		$rules = $rules ? $rules : self::getPriceRules( $product_id, $role, false, 'edit' );

		if ( empty( $rules ) ) {

			$product = wc_get_product( $product_id );

			return $product->is_type( 'variation' );
		}

		return false;
	}
}
