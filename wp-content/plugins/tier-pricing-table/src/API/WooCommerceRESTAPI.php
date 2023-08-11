<?php namespace TierPricingTable\API;

use TierPricingTable\PriceManager;
use WC_Product;

class WooCommerceRESTAPI {

	public function __construct() {

		add_action( 'rest_api_init', function () {
			register_rest_field( 'product',
				'tiered_pricing_fixed_rules',
				array(
					'get_callback'    => function ( $product ) {
						return PriceManager::getPriceRules( $product['id'] );
					},
					'update_callback' => function ( $value, WC_Product $object ) {
						$rules = $this->decodeRules( $value );

						update_post_meta( $object->get_id(), '_fixed_price_rules', $rules );
					},
					'schema'          => array(
						'description' => __( 'Tiered Pricing fixed rules. The format is the following: "quantity:price". For example, "10:20,5:40" means 10$ per piece if users buy 20pcs and 5$ per piece if users buy 40pcs', 'tier-pricing-table' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					)
				)
			);
			register_rest_field( 'product',
				'tiered_pricing_percentage_rules',
				array(
					'get_callback'    => function ( $product ) {
						return PriceManager::getPriceRules( $product['id'] );
					},
					'update_callback' => function ( $value, WC_Product $object ) {
						$rules = $this->decodeRules( $value );

						update_post_meta( $object->get_id(), '_percentage_price_rules', $rules );
					},
					'schema'          => array(
						'description' => __( 'Tiered Pricing percentage rules. The format is the following: "quantity:percentage_discount". For example, "10:20,20:40" means 10% off per piece if users buy 20pcs and 20% off per piece if users buy 40pcs', 'tier-pricing-table' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					)
				)
			);

			register_rest_field( 'product',
				'tiered_pricing_type',
				array(
					'get_callback'    => function ( $product ) {
						return PriceManager::getPricingType( $product['id'] );
					},
					'update_callback' => function ( $value, WC_Product $object ) {

						if ( in_array( $value, array( 'fixed', 'percentage' ) ) ) {
							PriceManager::updatePriceRulesType( $object->get_id(), $value );
						}
					},
					'schema'          => array(
						'description' => __( 'Tiered Pricing type. can be either "percentage" or "fixed"', 'tier-pricing-table' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					)
				)
			);
			register_rest_field( 'product',
				'tiered_pricing_minimum',
				array(
					'get_callback'    => function ( $product ) {
						return PriceManager::getProductQtyMin( $product['id'] );
					},
					'update_callback' => function ( $value, WC_Product $object ) {
						PriceManager::updateProductQtyMin( $object->get_id(), intval( $value ) );
					},
					'schema'          => array(
						'description' => __( 'Minimum order quantity. Goes from Tiered Pricing Table plugin.', 'tier-pricing-table' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					)
				)
			);
		} );
	}

	protected function decodeRules( $data ) {
		$rules = explode( ',', $data );
		$data  = array();

		if ( $rules ) {
			foreach ( $rules as $rule ) {
				$rule = explode( ':', $rule );

				if ( isset( $rule[0] ) && isset( $rule[1] ) ) {
					$data[ intval( $rule[0] ) ] = $rule[1];
				}
			}

		}

		$data = array_filter( $data );

		return ! empty( $data ) ? $data : array();
	}
}
