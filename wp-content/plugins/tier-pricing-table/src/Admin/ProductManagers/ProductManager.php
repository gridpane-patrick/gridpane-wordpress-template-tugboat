<?php namespace TierPricingTable\Admin\ProductManagers;

use TierPricingTable\PriceManager;
use WC_Product;

/**
 * Class ProductManager
 *
 * @package TierPricingTable\Admin\Product
 */
class ProductManager extends ProductManagerAbstract {

	/**
	 * Register hooks
	 */
	protected function hooks() {

		// Tiered Pricing Product Tab
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'registerTieredPricingProductTab' ), 99, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'renderTieredPricingTab' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'saveTieredPricingTab' ) );

		// Simple Product
		add_action( 'woocommerce_product_options_pricing', array( $this, 'renderPriceRules' ) );

		// Saving
		add_action( 'woocommerce_process_product_meta', array( $this, 'updatePriceRules' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'updatePriceRulesType' ) );
	}

	/**
	 * Add tiered pricing tab to woocommerce product tabs
	 *
	 * @param array $productTabs
	 *
	 * @return array
	 */
	public function registerTieredPricingProductTab( $productTabs ) {

		$productTabs['tiered-pricing-tab'] = array(
			'label'  => __( 'Tiered Pricing', 'tier-pricing-table' ),
			'target' => 'tiered-pricing-data',
			'class'  => array( 'show_if_simple', 'show_if_variable' )
		);

		return $productTabs;
	}

	/**
	 * Render content for the tiered pricing tab
	 */
	public function renderTieredPricingTab() {

		global $post;

		$min = PriceManager::getProductQtyMin( $post->ID, 'edit' );

		?>
		<div id="tiered-pricing-data" class="panel woocommerce_options_panel">
			<?php

			do_action( 'tier_pricing_table/admin/pricing_tab_begin', $post->ID );

			woocommerce_wp_text_input( array(
				'id'                => '_tiered_pricing_minimum',
				'wrapper_class'     => 'show_if_simple show_if_variable',
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => 1,
					'step' => 1
				),
				'value'             => $min,
				'label'             => __( 'Minimum quantity', 'tier-pricing-table' ),
				'description'       => __( 'Set if you are selling the product from qty more than 1',
					'tier-pricing-table' ),
				'default'           => 1,
				'desc_tip'          => true
			) );

			?>
			<div class="hidden show_if_variable">
				<?php

				$type = PriceManager::getPricingType( $post->ID, 'fixed', 'edit' );

				$this->fileManager->includeTemplate( 'admin/add-price-rules.php', array(
					'price_rules_fixed'      => PriceManager::getFixedPriceRules( $post->ID, 'edit' ),
					'price_rules_percentage' => PriceManager::getPercentagePriceRules( $post->ID, 'edit' ),
					'type'                   => $type,
					'prefix'                 => 'variable',
				) );

				?>
			</div>

			<?php do_action( 'tier_pricing_table/admin/pricing_tab_end', $post->ID ); ?>
		</div>
		<?php
	}

	/**
	 * Save tiered pricing tab data
	 *
	 * @param int $productId
	 */
	public function saveTieredPricingTab( $productId ) {

		$nonce = isset( $_POST['_simple_product_tier_nonce'] ) ? sanitize_key( $_POST['_simple_product_tier_nonce'] ) : false;

		if ( wp_verify_nonce( $nonce, 'save_simple_product_tier_price_data' ) ) {
			if ( isset( $_POST['_tiered_pricing_minimum'] ) ) {
				$min = intval( $_POST['_tiered_pricing_minimum'] );
				$min = $min > 0 ? $min : 1;

				PriceManager::updateProductQtyMin( $productId, $min );
			}
		}
	}

	/**
	 * Update price quantity rules for simple product
	 *
	 * @param int $product_id
	 */
	public function updatePriceRules( $product_id ) {

		$nonce = isset( $_POST['_simple_product_tier_nonce'] ) ? sanitize_key( $_POST['_simple_product_tier_nonce'] ) : false;

		if ( wp_verify_nonce( $nonce, 'save_simple_product_tier_price_data' ) ) {

			$data = $_POST;

			$prefix = isset( $data['product-type'] ) && in_array( $data['product-type'],
				array( 'simple', 'variable' ) ) ? sanitize_text_field( $data['product-type'] ) : 'simple';

			$fixedAmounts = isset( $data[ 'tiered_price_fixed_quantity_' . $prefix ] ) ? (array) $data[ 'tiered_price_fixed_quantity_' . $prefix ] : array();
			$fixedPrices  = ! empty( $data[ 'tiered_price_fixed_price_' . $prefix ] ) ? (array) $data[ 'tiered_price_fixed_price_' . $prefix ] : array();

			PriceManager::updateFixedPriceRules( $fixedAmounts, $fixedPrices, $product_id );

			$percentageAmounts = isset( $data[ 'tiered_price_percent_quantity_' . $prefix ] ) ? (array) $data[ 'tiered_price_percent_quantity_' . $prefix ] : array();
			$percentagePrices  = ! empty( $data[ 'tiered_price_percent_discount_' . $prefix ] ) ? (array) $data[ 'tiered_price_percent_discount_' . $prefix ] : array();

			PriceManager::updatePercentagePriceRules( $percentageAmounts, $percentagePrices, $product_id );

		}
	}

	/**
	 * Update product pricing type
	 *
	 * @param int $product_id
	 */
	public function updatePriceRulesType( $product_id ) {
		$nonce = isset( $_POST['_simple_product_tier_nonce'] ) ? sanitize_key( $_POST['_simple_product_tier_nonce'] ) : false;

		if ( wp_verify_nonce( $nonce, 'save_simple_product_tier_price_data' ) ) {

			$prefix = isset( $_POST['product-type'] ) && in_array( $_POST['product-type'],
				array( 'simple', 'variable' ) ) ? sanitize_text_field( $_POST['product-type'] ) : 'simple';

			if ( isset( $_POST[ 'tiered_price_rules_type_' . $prefix ] ) ) {
				PriceManager::updatePriceRulesType( $product_id,
					sanitize_text_field( $_POST[ 'tiered_price_rules_type_' . $prefix ] ) );
			}
		}
	}

	/**
	 * Render inputs for price rules on a simple product
	 *
	 * @global WC_Product $product_object
	 */
	public function renderPriceRules() {
		global $product_object;

		if ( $product_object instanceof WC_Product ) {
			$type = PriceManager::getPricingType( $product_object->get_id(), 'fixed', 'edit' );

			$this->fileManager->includeTemplate( 'admin/add-price-rules.php', array(
				'price_rules_fixed'      => PriceManager::getFixedPriceRules( $product_object->get_id(), 'edit' ),
				'price_rules_percentage' => PriceManager::getPercentagePriceRules( $product_object->get_id(), 'edit' ),
				'type'                   => $type,
				'prefix'                 => 'simple',
			) );
		}
	}
}
