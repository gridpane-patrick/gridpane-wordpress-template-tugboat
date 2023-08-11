<?php namespace TierPricingTable\Admin\Import;

use WC_Product;

class Woocommerce {

	/**
	 * Import constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'addColumnsToImporter' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns',
			array( $this, 'addColumnToMappingScreen' ) );
		add_filter( 'woocommerce_product_import_pre_insert_product_object', array( $this, 'processImport' ), 10, 2 );
	}

	/**
	 * Register the 'Tiered pricing' column in the importer.
	 *
	 * @param array $options
	 *
	 * @return array $options
	 */
	public function addColumnsToImporter( $options ) {

		$options['tiered_price_type']       = __( 'Tiered pricing type', 'tier-pricing-table' );
		$options['tiered_price_minimum']    = __( 'Tiered pricing minimum product quantity', 'tier-pricing-table' );
		$options['tiered_price_fixed']      = __( 'Fixed Tiered prices', 'tier-pricing-table' );
		$options['tiered_price_percentage'] = __( 'Percentage Tiered Prices', 'tier-pricing-table' );

		return $options;
	}


	/**
	 * Add automatic mapping support for 'Tiered pricing'.
	 *
	 * @param array $columns
	 *
	 * @return array $columns
	 */
	public function addColumnToMappingScreen( $columns ) {

		$columns[ __( 'Fixed Tiered prices', 'tier-pricing-table' ) ]                     = 'tiered_price_fixed';
		$columns[ __( 'Percentage Tiered prices', 'tier-pricing-table' ) ]                = 'tiered_price_percentage';
		$columns[ __( 'Tiered pricing type', 'tier-pricing-table' ) ]                     = 'tiered_price_type';
		$columns[ __( 'Tiered pricing minimum product quantity', 'tier-pricing-table' ) ] = 'tiered_price_minimum';

		return $columns;
	}

	/**
	 * Process the data read from the CSV file.
	 *
	 * @param WC_Product $product - Product being imported or updated.
	 * @param array $data - CSV data read for the product.
	 *
	 * @return WC_Product $object
	 */
	public function processImport( $product, $data ) {

		if ( isset( $data['tiered_price_fixed'] ) ) {

			$fixed = $this->decodeExport( $data['tiered_price_fixed'] );

			$product->update_meta_data( '_fixed_price_rules', $fixed );
		}

		if ( isset( $data['tiered_price_percentage'] ) ) {

			$percentage = $this->decodeExport( $data['tiered_price_percentage'] );

			$product->update_meta_data( '_percentage_price_rules', $percentage );
		}

		if ( isset( $data['tiered_price_type'] ) ) {

			if ( in_array( $data['tiered_price_type'], array( 'fixed', 'percentage' ) ) ) {
				$product->update_meta_data( '_tiered_price_rules_type', $data['tiered_price_type'] );
			}
		}

		if ( isset( $data['tiered_price_minimum'] ) ) {

			$minimum = (int) $data['tiered_price_minimum'];

			$product->update_meta_data( '_tiered_price_minimum_qty', $minimum );
		}

		return $product;
	}

	/**
	 * Decode export file format to array
	 *
	 * @param string $data
	 *
	 * @return array
	 */
	protected function decodeExport( $data ) {
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
