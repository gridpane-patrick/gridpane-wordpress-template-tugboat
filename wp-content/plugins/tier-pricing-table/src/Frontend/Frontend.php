<?php namespace TierPricingTable\Frontend;

use TierPricingTable\Settings\Settings;
use TierPricingTable\Core\FileManager;
use TierPricingTable\PriceManager;
use TierPricingTable\TierPricingTablePlugin;
use WP_Post;
use WC_Product;

/**
 * Class Frontend
 *
 * @package TierPricingTable\Frontend
 */
class Frontend {

	/**
	 * FileManager
	 *
	 * @var FileManager
	 */
	private $fileManager;

	/**
	 * Settings
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Frontend constructor.
	 *
	 * @param FileManager $fileManager
	 * @param Settings $settings
	 */
	public function __construct( FileManager $fileManager, Settings $settings ) {
		$this->fileManager = $fileManager;
		$this->settings    = $settings;

		// Render price table
		add_action( $this->settings->get( 'position_hook', 'woocommerce_before_add_to_cart_button' ), array(
			$this,
			'displayPricingTable'
		), - 999 );

		// Wrap price
		add_action( 'woocommerce_get_price_html', array( $this, 'wrapPrice' ), 10, 2 );

		// Get table for variation
		add_action( 'wc_ajax_get_price_table', array( $this, 'getPriceTableVariation' ), 10, 1 );

		// Enqueue frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueAssets' ), 10, 1 );

		// Render tooltip near product price if selected display type is "tooltip"
		if ( 'yes' === $this->settings->get( 'display',
				'yes' ) && 'tooltip' === $this->settings->get( 'display_type' ) ) {
			add_filter( 'woocommerce_get_price_html', array( $this, 'renderTooltip' ), 10, 2 );
		}

		// Render summary block for the product
		if ( 'yes' === $this->settings->get( 'display_summary', 'yes' ) ) {
			add_action( $this->settings->get( 'summary_position_hook', 'woocommerce_after_add_to_cart_button' ),
				array( $this, 'renderSummary' ), 10 );
		}

	}


	public function renderSummary() {

		global $post;

		if ( ! $post ) {
			return;
		}

		$product = wc_get_product( $post->ID );

		if ( ! $product ) {
			return;
		}

		$type = $this->settings->get( 'summary_type', 'table' );

		$this->fileManager->includeTemplate( 'frontend/summary-' . $type . '.php', array(
			'needHide'   => $product->is_type( 'variable' ),
			'totalLabel' => $this->settings->get( 'summary_total_label', 'Total:' ),
			'eachLabel'  => $this->settings->get( 'summary_each_label', 'Each:' ),
			'title'      => $this->settings->get( 'summary_title', '' ),
		) );
	}

	/**
	 *  Display table at frontend
	 */
	public function displayPricingTable() {

		global $post;

		if ( ! $post ) {
			return;
		}

		$product = wc_get_product( $post->ID );

		if ( $product ) {
			if ( in_array( $product->get_type(), $this->getSupportedSimpleProductTypes() ) ) {
				$this->renderPricingTable( $product->get_id() );
			} elseif ( in_array( $product->get_type(), $this->getSupportedVariableProductTypes() ) ) {

				$variation_id         = 0;
				$is_default_variation = false;
				foreach ( $product->get_available_variations() as $variation_values ) {
					foreach ( $variation_values['attributes'] as $key => $attribute_value ) {
						$attribute_name = str_replace( 'attribute_', '', $key );
						$default_value  = $product->get_variation_default_attribute( $attribute_name );
						if ( $default_value == $attribute_value ) {
							$is_default_variation = true;
						} else {
							$is_default_variation = false;
							break;
						}
					}
					if ( $is_default_variation ) {
						$variation_id = $variation_values['variation_id'];
						break;
					}
				}

				// Now we get the default variation data
				if ( $is_default_variation ) {
					?>
					<div data-variation-price-rules-table>
						<?php $this->renderPricingTable( $product->get_id(), $variation_id ); ?>
					</div>
					<?php
				} else {
					echo '<div data-variation-price-rules-table></div>';
				}

			}
		}
	}

	/**
	 * Wrap product price for managing it by JS
	 *
	 * @param string $price_html
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	public function wrapPrice( $price_html, $product ) {

		$supportedTypes = array_merge( $this->getSupportedSimpleProductTypes(), array( 'variation' ) );

		if ( $this->settings->get( 'show_total_price', 'no' ) === 'yes' ) {
			$supportedTypes = array_merge( $supportedTypes, $this->getSupportedVariableProductTypes() );
		}

		if ( is_single() && in_array( $product->get_type(), $supportedTypes ) ) {
			return '<span data-tiered-price-wrapper>' . $price_html . '</span>';
		}

		return $price_html;
	}

	/**
	 * Render tooltip near product price if selected display type is "tooltip"
	 *
	 * @param string $price
	 * @param WC_Product $_product
	 *
	 * @return string
	 */
	public function renderTooltip( $price, $_product ) {

		if ( is_product() ) {

			$page_product_id = get_queried_object_id();

			if ( $_product->is_type( 'variation' ) && $_product->get_parent_id() === $page_product_id
				 || ( is_product() && $_product->is_type( 'simple' ) && $page_product_id === $_product->get_id() ) ) {

				$rules = PriceManager::getPriceRules( $_product->get_id() );

				if ( ! empty( $rules ) ) {
					return $price . $this->fileManager->renderTemplate( 'frontend/tooltip.php', array(
								'color' => $this->settings->get( 'tooltip_color', '#cc99c2' ),
								'size'  => $this->settings->get( 'tooltip_size', 15 ) . 'px',
							)
						);

				}
			}
		}

		return $price;
	}

	/**
	 * Enqueue assets at simple product and variation product page.
	 *
	 * @global WP_Post $post .
	 */
	public function enqueueAssets() {
		global $post;

		if ( is_product() ) {
			$product = wc_get_product( $post->ID );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-tooltip' );

			wp_enqueue_script( 'tier-pricing-table-front-js',
				$this->fileManager->locateAsset( 'frontend/product-tier-pricing-table.js' ),
				array( 'jquery', 'jquery-ui-tooltip' ),
				TierPricingTablePlugin::VERSION );

			wp_enqueue_style( 'tier-pricing-table-front-css',
				$this->fileManager->locateAsset( 'frontend/main.css' ), null, TierPricingTablePlugin::VERSION );

			wp_localize_script( 'tier-pricing-table-front-js', 'tieredPricingTable',
				array(
					'product_type'     => $product->get_type(),
					'load_table_nonce' => wp_create_nonce( 'get_price_table' ),
					'settings'         => $this->settings->getAll(),
					'is_premium'       => 'yes',
					'currency_options' => array(
						'currency_symbol'    => get_woocommerce_currency_symbol(),
						'decimal_separator'  => wc_get_price_decimal_separator(),
						'thousand_separator' => wc_get_price_thousand_separator(),
						'decimals'           => wc_get_price_decimals(),
						'price_format'       => get_woocommerce_price_format(),
						'price_suffix'       => $product->get_price_suffix(),
					)
				) );
		}

	}

	/**
	 * Fired when user choose some variation. Render price rules table for it if it exists
	 *
	 * @global WP_Post $post .
	 */
	public function getPriceTableVariation() {

		$product_id = isset( $_POST['variation_id'] ) ? sanitize_text_field( $_POST['variation_id'] ) : false;
		$nonce      = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : false;

		if ( wp_verify_nonce( $nonce, 'get_price_table' ) ) {
			$product = wc_get_product( $product_id );

			if ( $product ) {
				$this->renderPricingTable( $product->get_parent_id(), $product->get_id() );
			}

		}
	}

	/**
	 * Main function for rendering pricing table for product
	 *
	 * @param int $product_id
	 * @param int $variation_id
	 */
	public function renderPricingTable( $product_id, $variation_id = null ) {
		$product = wc_get_product( $product_id );

		$product_id = ! is_null( $variation_id ) ? $variation_id : $product->get_id();

		$supportedTypes = array_merge( $this->getSupportedSimpleProductTypes(),
			$this->getSupportedVariableProductTypes() );

		// Exit if product is not valid
		if ( ! $product || ! in_array( $product->get_type(), $supportedTypes ) ) {
			return;
		}

		$rules = PriceManager::getPriceRules( $product_id );

		$real_price   = ! is_null( $variation_id ) ? wc_get_product( $variation_id )->get_price() : $product->get_price();
		$product_name = ! is_null( $variation_id ) ? wc_get_product( $variation_id )->get_name() : $product->get_name();

		if ( ! empty( $rules ) ) {

			$template = 'percentage' === PriceManager::getPricingType( $product_id ) ? 'price-table-percentage.php' : 'price-table-fixed.php';

			$this->fileManager->includeTemplate( 'frontend/' . $template, array(
				'price_rules'  => $rules,
				'real_price'   => $real_price,
				'product_name' => $product_name,
				'product_id'   => $product_id,
				'minimum'      => PriceManager::getProductQtyMin( $product_id, 'view' ),
				'settings'     => $this->settings->getAll()
			) );
		}
	}

	public function getSupportedSimpleProductTypes() {
		return apply_filters( 'tier_pricing_table/frontend/supported_simple_product_types', array(
			'simple',
			'subscription'
		) );
	}

	public function getSupportedVariableProductTypes() {
		return apply_filters( 'tier_pricing_table/frontend/supported_variable_product_types', array(
			'variable',
			'variable-subscription'
		) );
	}
}
