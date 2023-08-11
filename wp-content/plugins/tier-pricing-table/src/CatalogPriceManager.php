<?php namespace TierPricingTable;

use TierPricingTable\Settings\Settings;
use WC_Product;
use WC_Product_Variable;

/**
 * Class CatalogPriceManager
 *
 * @package TierPricingTable
 */
class CatalogPriceManager {

	/**
	 * Settings
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * CatalogPriceManager constructor.
	 *
	 * @param Settings $settings
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;

		if ( $this->isEnable() && ! is_admin() ) {
			add_filter( 'woocommerce_get_price_html', array( $this, 'formatPrice' ), 999, 2 );
		}
	}

	/**
	 * Change logic showing prince at catalog for product with tiered price rules
	 *
	 * @param string $priceHtml
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	public function formatPrice( $priceHtml, $product ) {

		$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

		$formatCondition = 'yes' === $this->settings->get( 'tiered_price_at_product_page',
				'no' ) || get_queried_object_id() != $product_id;

		// Variable product. Product page.
		if ( get_queried_object_id() == $product_id && $product instanceof WC_Product_Variable ) {
			$price = $this->formatPriceForVariableProduct( $product );

			if ( $price ) {
				return $price . $product->get_price_suffix();
			}
		}

		if ( $formatCondition ) {
			$displayPriceType = $this->getDisplayType();

			if ( in_array( $product->get_type(), array( 'simple', 'variation' ) ) ) {
				$rules = PriceManager::getPriceRules( $product->get_id() );

				if ( ! empty( $rules ) ) {
					if ( 'range' === $displayPriceType ) {
						return $this->getRange( $rules, $product ) . $product->get_price_suffix();
					} else {
						return $this->getLowestPrice( $rules, $product ) . $product->get_price_suffix();
					}
				}
			}

			if ( $product instanceof WC_Product_Variable && 'yes' === $this->useForVariable() ) {
				$price = $this->formatPriceForVariableProduct( $product );

				if ( $price ) {
					return $price . $product->get_price_suffix();
				}
			}
		}

		return $priceHtml;
	}

	/**
	 * Format price for variable product. Range uses lowest and high prices from all variations
	 *
	 * @param WC_Product_Variable $product
	 *
	 * @return bool|string
	 */
	protected function formatPriceForVariableProduct( WC_Product_Variable $product ) {

		// With taxes
		$maxPrice  = $product->get_variation_price( 'max', true );
		$minPrices = array( $product->get_variation_price( 'min', true ) );

		foreach ( $product->get_available_variations() as $variation ) {
			$rules = PriceManager::getPriceRules( $variation['variation_id'] );

			if ( ! empty( $rules ) ) {
				$minPrices[] = $this->getLowestPrice( $rules, wc_get_product( $variation['variation_id'] ), false );
			}
		}

		if ( ! empty( $minPrices ) ) {
			if ( 'range' === $this->getDisplayType() ) {

				if ( min( $minPrices ) === $maxPrice ) {
					return false;
				}

				return wc_price( min( $minPrices ) ) . ' - ' . wc_price( $maxPrice );
			} else {
				return $this->getLowestPrefix() . ' ' . wc_price( min( $minPrices ) );
			}
		}

		return false;
	}

	/**
	 * Get range from lowest to highest price from price rules
	 *
	 * @param array $rules
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	protected function getRange( $rules, $product ) {

		$lowest = array_pop( $rules );

		$highest_html = wc_price( wc_get_price_to_display( $product, array(
			'price' => $product->get_price(),
		) ) );

		if ( 'percentage' === PriceManager::getPricingType( $product->get_id() ) ) {

			$lowest_html = wc_price(
				wc_get_price_to_display( $product, array(
					'price' => PriceManager::getPriceByPercentDiscount( $product->get_price(), $lowest ),
				) )
			);

			$range = $lowest_html . ' - ' . $highest_html;
		} else {
			$lowest_html = wc_price(
				wc_get_price_to_display( $product, array(
					'price' => $lowest,
				) )
			);

			$range = $lowest_html . ' - ' . $highest_html;
		}

		if ( $lowest_html !== $highest_html ) {
			return $range;
		}

		return $lowest_html;
	}

	/**
	 * Get lowest price from price rules
	 *
	 * @param array $rules
	 * @param WC_Product $product
	 *
	 * @param bool $html
	 *
	 * @return string|float
	 */
	protected function getLowestPrice( $rules, $product, $html = true ) {
		if ( 'percentage' === PriceManager::getPricingType( $product->get_id() ) ) {
			$lowest = PriceManager::getPriceByPercentDiscount( $product->get_price(),
				array_pop( $rules ) );
		} else {
			$lowest = array_pop( $rules );
		}

		if ( ! $html ) {
			return wc_get_price_to_display( $product, array(
				'price' => $lowest
			) );
		}

		return $this->getLowestPrefix() . ' ' . wc_price( wc_get_price_to_display( $product, array(
				'price' => $lowest
			) ) );
	}

	public function getLowestPrefix() {
		return $this->settings->get( 'lowest_prefix', __( 'From', 'tier-pricing-table' ) );
	}

	public function isEnable() {
		return 'yes' === $this->settings->get( 'tiered_price_at_catalog', 'yes' );
	}

	public function getDisplayType() {
		return $this->settings->get( 'tiered_price_at_catalog_type', 'range' );
	}

	public function useForVariable() {
		return $this->settings->get( 'tiered_price_at_catalog_for_variable', 'yes' );
	}
}
