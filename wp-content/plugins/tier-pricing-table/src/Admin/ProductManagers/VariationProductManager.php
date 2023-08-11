<?php namespace TierPricingTable\Admin\ProductManagers;

use TierPricingTable\PriceManager;
use WP_Post;

/**
 * Class VariationProduct
 *
 * @package TierPricingTable\Admin\Product
 */
class VariationProductManager extends ProductManagerAbstract {

	/**
	 * Register hooks
	 */
	protected function hooks() {
		add_action( 'woocommerce_variation_options_pricing', array( $this, 'renderPriceRules' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'updatePriceRules' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'updatePriceRulesTypes' ), 10, 3 );
	}

	/**
	 * Update price quantity rules for variation product
	 *
	 * @param int $variation_id
	 * @param int $loop
	 */
	public function updatePriceRules( $variation_id, $loop ) {

		check_ajax_referer( 'save-variations', 'security' );

		$data = $_POST;

		if ( isset( $data['tiered_price_fixed_quantity'][ $loop ] ) ) {
			$fixedAmounts = $data['tiered_price_fixed_quantity'][ $loop ];
			$fixedPrices  = ! empty( $data['tiered_price_fixed_price'][ $loop ] ) ? (array) $data['tiered_price_fixed_price'][ $loop ] : array();

			PriceManager::updateFixedPriceRules( $fixedAmounts, $fixedPrices, $variation_id );
		}

		if ( isset( $data['tiered_price_percent_quantity'][ $loop ] ) ) {
			$amounts = $data['tiered_price_percent_quantity'][ $loop ];
			$prices  = ! empty( $data['tiered_price_percent_discount'][ $loop ] ) ? (array) $data['tiered_price_percent_discount'][ $loop ] : array();

			PriceManager::updatePercentagePriceRules( $amounts, $prices, $variation_id );
		}

		if ( isset( $_POST['_tiered_pricing_minimum'][ $loop ] ) ) {
			$min = intval( $_POST['_tiered_pricing_minimum'][ $loop ] );
			$min = $min > 0 ? $min : 1;

			PriceManager::updateProductQtyMin( $variation_id, $min );
		}

	}

	/**
	 * Update product pricing type
	 *
	 * @param int $variation_id
	 * @param int $loop
	 */
	public function updatePriceRulesTypes( $variation_id, $loop ) {
		check_ajax_referer( 'save-variations', 'security' );

		if ( ! empty( $_POST['tiered_price_rules_type'][ $loop ] ) ) {
			PriceManager::updatePriceRulesType( $variation_id,
				sanitize_text_field( $_POST['tiered_price_rules_type'][ $loop ] ) );
		}
	}

	/**
	 * Render inputs for price rules on variation
	 *
	 * @param int $loop
	 * @param array $variation_data
	 * @param WP_Post $variation
	 */
	public function renderPriceRules( $loop, $variation_data, $variation ) {

		$this->fileManager->includeTemplate( 'admin/add-price-rules-variation.php', array(
			'price_rules_fixed'      => PriceManager::getFixedPriceRules( $variation->ID, 'edit' ),
			'price_rules_percentage' => PriceManager::getPercentagePriceRules( $variation->ID, 'edit' ),
			'minimum'                => PriceManager::getProductQtyMin( $variation->ID, 'edit' ),
			'type'                   => PriceManager::getPricingType( $variation->ID, 'fixed', 'edit' ),
			'i'                      => $loop,
			'variation_data'         => $variation_data,
		) );
	}
}
