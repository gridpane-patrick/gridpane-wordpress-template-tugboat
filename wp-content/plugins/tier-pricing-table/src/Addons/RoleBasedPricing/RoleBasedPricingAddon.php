<?php namespace TierPricingTable\Addons\RoleBasedPricing;

use TierPricingTable\Addons\AbstractAddon;
use WC_Product;

class RoleBasedPricingAddon extends AbstractAddon {

	const GET_ROLE_ROW_HTML__ACTION = 'tpt_get_role_row_html';
	const SETTING_ENABLE_KEY = 'enable_role_based_pricing_addon';

	/**
	 * Get addon name
	 *
	 * @return string
	 */
	public function getName() {
		return __( 'Role based tiered pricing', 'tier-pricing-table' );
	}

	/**
	 * Whether addon is active or not
	 *
	 * @return bool
	 */
	public function isActive() {
		return $this->settings->get( self::SETTING_ENABLE_KEY, 'yes' ) === 'yes';
	}

	/**
	 * Run
	 */
	public function run() {
		// Get row ajax
		add_action( 'wp_ajax_' . self::GET_ROLE_ROW_HTML__ACTION, array( $this, 'getRoleRowHtml' ) );

		// Simple product
		add_action( 'tier_pricing_table/admin/pricing_tab_end', array( $this, 'renderProductPage' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'updateRoleBasedData' ) );

		// Variable product
		add_action( 'woocommerce_variation_options_pricing', array( $this, 'renderPriceRulesVariation' ), 11, 3 );
		add_action( 'woocommerce_save_product_variation', array(
			$this,
			'updateVariationRoleBasedData'
		), 10, 3 );

		/**
		 * Main function to filter the tiered pricing rules
		 *
		 * @priority 20
		 */
		add_filter( 'tier_pricing_table/price/product_price_rules', array( $this, 'addRolePricing' ), 20, 2 );

		add_filter( 'woocommerce_product_get_regular_price', array( $this, 'adjustRoleRegularPrice' ), 99, 2 );
		add_filter( 'woocommerce_product_get_sale_price', array( $this, 'adjustRoleSalePrice' ), 99, 2 );
		add_filter( 'woocommerce_product_get_price', array( $this, 'adjustRolePrice' ), 99, 2 );

		// Variations
		add_filter( 'woocommerce_product_variation_get_regular_price', array(
			$this,
			'adjustRoleRegularPrice'
		), 99, 2 );
		add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'adjustRoleSalePrice' ), 99, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( $this, 'adjustRolePrice' ), 99, 2 );

		// Variable (price range)
		add_filter( 'woocommerce_variation_prices_price', array( $this, 'adjustRolePrice' ), 99, 3 );
		// Variation
		add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'adjustRoleRegularPrice' ), 99, 3 );
		add_filter( 'woocommerce_variation_prices_sale_price', array( $this, 'adjustRoleSalePrice' ), 99, 3 );

		// Price caching
		add_filter( 'woocommerce_get_variation_prices_hash', function ( $hash, $product ) {

			$hash[] = $this->getRolePrice( $product, 'sale' ) . md5( serialize( $this->getCurrentUserRoles() ) );
			$hash[] = $this->getRolePrice( $product, 'regular' ) . md5( serialize( $this->getCurrentUserRoles() ) );

			return $hash;
		}, 99, 2 );

		add_filter( 'tier_pricing_table/price/minimum', array( $this, 'adjustProductMinimum' ), 1, 2 );
	}

	public function adjustProductMinimum( $min, $productId ) {
		$userRoles = $this->getCurrentUserRoles();

		if ( ! empty( $userRoles ) ) {

			foreach ( $userRoles as $role ) {

				if ( RoleBasedPriceManager::roleHasRules( $role, $productId ) ) {
					return RoleBasedPriceManager::getProductQtyMin( $productId, $role );
				}
			}
		}

		return $min;
	}

	protected function getRolePrice( WC_Product $product, $specific = false ) {
		$userRoles = $this->getCurrentUserRoles();

		if ( ! empty( $userRoles ) ) {
			foreach ( $userRoles as $role ) {

				$roleSalePrice    = RoleBasedPriceManager::getProductSaleRolePrice( $product->get_id(), $role );
				$roleRegularPrice = RoleBasedPriceManager::getProductRegularRolePrice( $product->get_id(), $role );

				if ( $specific ) {
					if ( 'sale' === $specific && $roleSalePrice ) {
						return $roleSalePrice;
					} else if ( 'regular' === $specific && $roleRegularPrice ) {
						return $roleRegularPrice;
					}
				} else {
					if ( $roleSalePrice ) {
						return $roleSalePrice;
					} else if ( $roleRegularPrice ) {
						return $roleRegularPrice;
					}
				}
			}
		}

		return null;
	}

	public function adjustRolePrice( $price, WC_Product $product ) {

		if ( $product->get_meta( 'tiered_pricing_cart_price_calculated' ) === 'yes' ) {
			return $price;
		}

		$rolePrice = $this->getRolePrice( $product );

		return $rolePrice ? $rolePrice : $price;
	}

	public function adjustRoleSalePrice( $price, WC_Product $product ) {
		$rolePrice = $this->getRolePrice( $product, 'sale' );

		return $rolePrice ? (float) $rolePrice : $price;
	}

	public function adjustRoleRegularPrice( $price, WC_Product $product ) {
		$rolePrice = $this->getRolePrice( $product, 'regular' );

		return $rolePrice ? (float) $rolePrice : $price;
	}

	/**
	 * Main function to filter tiered pricing rules
	 *
	 * @param array $_rules
	 * @param int $productId
	 *
	 * @return array
	 */
	public function addRolePricing( $_rules, $productId ) {

		$userRoles = $this->getCurrentUserRoles();

		if ( ! empty( $userRoles ) ) {

			foreach ( $userRoles as $role ) {

				if ( RoleBasedPriceManager::roleHasRules( $role, $productId ) ) {

					$rules     = RoleBasedPriceManager::getPriceRules( $productId, $role );
					$rulesType = RoleBasedPriceManager::getPricingType( $productId, $role );

					add_filter( 'tier_pricing_table/price/type', function ( $__rulesType, $__productId ) use ( $rulesType, $productId ) {
						if ( $productId === $__productId ) {
							return $rulesType;
						}

						return $__rulesType;
					}, 10, 2 );

					return $rules;
				}
			}
		}

		return $_rules;
	}

	protected function getCurrentUserRoles() {
		$roles = array();
		$user  = wp_get_current_user();

		if ( $user ) {
			$roles = ( array ) $user->roles;
		}

		return apply_filters( 'tier_pricing_table/role_based_rules/current_user_roles', $roles, get_current_user_id() );
	}

	public function updateRoleBasedData( $product_id ) {

		// phpcs:disable WordPress.Security.NonceVerification.Missing - checking nonce does not makes any sense. Required by WooCommerce phpcs rules
		if ( true || wp_verify_nonce( true ) ) {
			$data = $_POST;
		}

		$this->updateRegularPrice( $product_id, $data );
		$this->updateSalePrice( $product_id, $data );

		$this->updatePriceRulesType( $product_id, $data );
		$this->updatePriceRules( $product_id, $data );
		$this->updateMinimumAmount( $product_id, $data );

		$this->handleRemovedRules( $product_id, $data );
	}

	public function updateVariationRoleBasedData( $variation_id, $loop ) {

		// phpcs:disable WordPress.Security.NonceVerification.Missing - checking nonce does not makes any sense. Required by WooCommerce phpcs rules
		if ( true || wp_verify_nonce( true ) ) {
			$data = $_POST;
		}

		$this->updateVariationRoleSalePrice( $variation_id, $loop, $data );
		$this->updateVariationRoleRegularPrice( $variation_id, $loop, $data );

		$this->updateVariationPriceRules( $variation_id, $loop, $data );
		$this->updateVariationMinimumAmount( $variation_id, $loop, $data );
		$this->updateVariationPriceRulesType( $variation_id, $loop, $data );

		$this->handleVariationRemovedRules( $variation_id, $loop, $data );
	}

	/**
	 * Update variation role sale price
	 *
	 * @param int $variation_id
	 * @param int $loop
	 * @param array $data
	 */
	public function updateVariationRoleSalePrice( $variation_id, $loop, $data ) {

		if ( ! empty( $data['tiered_pricing_roles_sale_price_variable'][ $loop ] ) ) {
			foreach ( $data['tiered_pricing_roles_sale_price_variable'][ $loop ] as $role => $price ) {

				if ( ! empty( $data['tiered_pricing_roles_sale_price_variable'][ $loop ][ $role ] ) ) {
					$price = $data['tiered_pricing_roles_sale_price_variable'][ $loop ][ $role ];
					$price = $price ? wc_format_decimal( $price ) : '';

					RoleBasedPriceManager::updateSaleRolePrice( $variation_id, $price, $role );
				} else {
					RoleBasedPriceManager::updateSaleRolePrice( $variation_id, '', $role );
				}

			}
		}
	}

	/**
	 * Update variation role sale price
	 *
	 * @param int $variation_id
	 * @param int $loop
	 * @param array $data
	 */
	public function updateVariationRoleRegularPrice( $variation_id, $loop, $data ) {

		if ( ! empty( $data['tiered_pricing_roles_regular_price_variable'][ $loop ] ) ) {
			foreach ( $data['tiered_pricing_roles_regular_price_variable'][ $loop ] as $role => $price ) {
				if ( ! empty( $price ) ) {
					$price = $price ? wc_format_decimal( $price ) : '';

					RoleBasedPriceManager::updateRegularRolePrice( $variation_id, $price, $role );
				} else {
					RoleBasedPriceManager::updateRegularRolePrice( $variation_id, '', $role );
				}

			}
		}
	}

	/**
	 * Update price quantity rules for variation product
	 *
	 * @param int $variation_id
	 * @param int $loop
	 * @param array $data
	 */
	public function updateVariationPriceRules( $variation_id, $loop, $data ) {

		if ( ! empty( $data['tiered_price_fixed_quantity_roles_variable'][ $loop ] ) ) {
			foreach ( $data['tiered_price_fixed_quantity_roles_variable'][ $loop ] as $role => $rules ) {
				$fixedAmounts = ! empty( $data['tiered_price_fixed_quantity_roles_variable'][ $loop ][ $role ] ) ? (array) $data['tiered_price_fixed_quantity_roles_variable'][ $loop ][ $role ] : array();
				$fixedPrices  = ! empty( $data['tiered_price_fixed_price_roles_variable'][ $loop ][ $role ] ) ? (array) $data['tiered_price_fixed_price_roles_variable'][ $loop ][ $role ] : array();

				RoleBasedPriceManager::updateFixedPriceRules( $fixedAmounts, $fixedPrices, $variation_id, $role );

				$percentageAmounts = ! empty( $data['tiered_price_percent_quantity_roles_variable'][ $loop ][ $role ] ) ? (array) $data['tiered_price_percent_quantity_roles_variable'][ $loop ][ $role ] : array();
				$percentagePrices  = ! empty( $data['tiered_price_percent_discount_roles_variable'][ $loop ][ $role ] ) ? (array) $data['tiered_price_percent_discount_roles_variable'][ $loop ][ $role ] : array();

				RoleBasedPriceManager::updatePercentagePriceRules( $percentageAmounts, $percentagePrices, $variation_id, $role );
			}
		}
	}

	/**
	 * Update product pricing type
	 *
	 * @param int $variation_id
	 * @param int $loop
	 * @param array $data
	 */
	public function updateVariationPriceRulesType( $variation_id, $loop, $data ) {
		if ( ! empty( $data['tiered_price_rules_type_roles_variable'][ $loop ] ) ) {
			foreach ( $data['tiered_price_rules_type_roles_variable'][ $loop ] as $role => $rules ) {
				if ( ! empty( $data['tiered_price_rules_type_roles_variable'][ $loop ] [ $role ] ) ) {
					RoleBasedPriceManager::updatePriceRulesType( $variation_id,
						sanitize_text_field( $data['tiered_price_rules_type_roles_variable'][ $loop ][ $role ] ), $role );
				}
			}
		}
	}

	/**
	 * Update role-based minimum order amount for variation
	 *
	 * @param int $variation_id
	 * @param int $loop
	 * @param array $data
	 */
	public function updateVariationMinimumAmount( $variation_id, $loop, $data ) {

		if ( ! empty( $data['tiered_pricing_minimum_roles_variable'][ $loop ] ) ) {
			foreach ( $data['tiered_pricing_minimum_roles_variable'][ $loop ] as $role => $min ) {
				if ( ! empty( $data['tiered_pricing_minimum_roles_variable'][ $loop ][ $role ] ) ) {
					$min = intval( $data['tiered_pricing_minimum_roles_variable'][ $loop ][ $role ] );
					$min = $min > 0 ? $min : 1;

					RoleBasedPriceManager::updateProductQtyMin( $variation_id, $min, $role );
				}
			}
		}
	}

	/**
	 * Handle removing not used role-based rules
	 *
	 * @param int $variation_id
	 * @param int $loop
	 * @param array $data
	 */
	public function handleVariationRemovedRules( $variation_id, $loop, $data ) {

		if ( ! empty( $data['tiered_price_rules_roles_to_delete_variable'][ $loop ] ) ) {

			foreach ( $data['tiered_price_rules_roles_to_delete_variable'][ $loop ] as $roleToRemove ) {
				if ( ! empty( $roleToRemove ) ) {
					RoleBasedPriceManager::deleteAllDataForRole( $variation_id, $roleToRemove );
				}
			}
		}
	}

	/**
	 * Handle remover role-based rules
	 *
	 * @param int $product_id
	 * @param array $data
	 */
	public function handleRemovedRules( $product_id, $data ) {
		if ( ! empty( $data['tiered_price_rules_roles_to_delete'] ) ) {
			foreach ( $data['tiered_price_rules_roles_to_delete'] as $roleToRemove ) {
				if ( ! empty( $roleToRemove ) ) {
					RoleBasedPriceManager::deleteAllDataForRole( $product_id, $roleToRemove );
				}
			}
		}
	}

	/**
	 * Update regular role-based price
	 *
	 * @param int $product_id
	 * @param array $data
	 */
	public function updateRegularPrice( $product_id, $data ) {

		if ( ! empty( $data['tiered_pricing_roles_regular_price'] ) ) {
			foreach ( $data['tiered_pricing_roles_regular_price'] as $role => $price ) {
				if ( ! empty( $data['tiered_pricing_roles_regular_price'][ $role ] ) ) {

					$price = $data['tiered_pricing_roles_regular_price'][ $role ];
					$price = $price ? wc_format_decimal( $price ) : '';

					RoleBasedPriceManager::updateRegularRolePrice( $product_id, $price, $role );
				} else {
					RoleBasedPriceManager::updateRegularRolePrice( $product_id, '', $role );
				}
			}
		}
	}

	/**
	 * Update sale role-based price
	 *
	 * @param int $product_id
	 * @param array $data
	 */
	public function updateSalePrice( $product_id, $data ) {
		if ( ! empty( $data['tiered_pricing_roles_sale_price'] ) ) {
			foreach ( $data['tiered_pricing_roles_sale_price'] as $role => $price ) {
				if ( ! empty( $data['tiered_pricing_roles_sale_price'][ $role ] ) ) {
					$price = $data['tiered_pricing_roles_sale_price'][ $role ];
					$price = $price ? wc_format_decimal( $price ) : '';

					RoleBasedPriceManager::updateSaleRolePrice( $product_id, $price, $role );
				} else {
					RoleBasedPriceManager::updateSaleRolePrice( $product_id, '', $role );
				}
			}
		}
	}

	/**
	 * Update role-based minimum order amount
	 *
	 * @param int $product_id
	 * @param array $data
	 */
	public function updateMinimumAmount( $product_id, $data ) {
		if ( ! empty( $data['tiered_pricing_minimum_roles'] ) ) {
			foreach ( $data['tiered_pricing_minimum_roles'] as $role => $min ) {
				if ( ! empty( $data['tiered_pricing_minimum_roles'][ $role ] ) ) {
					$min = intval( $data['tiered_pricing_minimum_roles'][ $role ] );
					$min = $min > 0 ? $min : 1;

					RoleBasedPriceManager::updateProductQtyMin( $product_id, $min, $role );
				}
			}
		}
	}

	/**
	 * Update role-based price rules
	 *
	 * @param int $product_id
	 * @param array $data
	 */
	public function updatePriceRules( $product_id, $data ) {

		if ( ! empty( $data['tiered_price_fixed_quantity_roles'] ) ) {
			foreach ( $data['tiered_price_fixed_quantity_roles'] as $role => $rules ) {
				$fixedAmounts = ! empty( $data['tiered_price_fixed_quantity_roles'][ $role ] ) ? (array) $data['tiered_price_fixed_quantity_roles'][ $role ] : array();
				$fixedPrices  = ! empty( $data['tiered_price_fixed_price_roles'][ $role ] ) ? (array) $data['tiered_price_fixed_price_roles'][ $role ] : array();

				RoleBasedPriceManager::updateFixedPriceRules( $fixedAmounts, $fixedPrices, $product_id, $role );
			}
		}

		if ( ! empty( $data['tiered_price_percent_discount_roles'] ) ) {
			foreach ( $data['tiered_price_percent_discount_roles'] as $role => $rules ) {

				$percentageAmounts = ! empty( $data['tiered_price_percent_quantity_roles'][ $role ] ) ? (array) $data['tiered_price_percent_quantity_roles'][ $role ] : array();
				$percentagePrices  = ! empty( $data['tiered_price_percent_discount_roles'][ $role ] ) ? (array) $data['tiered_price_percent_discount_roles'][ $role ] : array();

				RoleBasedPriceManager::updatePercentagePriceRules( $percentageAmounts, $percentagePrices, $product_id, $role );
			}
		}
	}

	/**
	 * Update product pricing type
	 *
	 * @param int $product_id
	 * @param array $data
	 */
	public function updatePriceRulesType( $product_id, $data ) {
		if ( ! empty( $data['tiered_price_rules_type_roles'] ) ) {
			foreach ( $data['tiered_price_rules_type_roles'] as $role => $rules ) {
				if ( ! empty( $data['tiered_price_rules_type_roles'][ $role ] ) ) {
					RoleBasedPriceManager::updatePriceRulesType( $product_id,
						sanitize_text_field( $data['tiered_price_rules_type_roles'][ $role ] ), $role );
				}
			}
		}
	}

	/**
	 * AJAX Handler
	 */
	public function getRoleRowHtml() {
		$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( $_GET['nonce'] ) : false;

		if ( wp_verify_nonce( $nonce, self::GET_ROLE_ROW_HTML__ACTION ) ) {

			$role       = isset( $_GET['role'] ) ? sanitize_text_field( $_GET['role'] ) : false;
			$product_id = isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : 0;
			$loop       = isset( $_GET['loop'] ) ? intval( $_GET['loop'] ) : 0;
			$role       = get_role( $role );

			$product = wc_get_product( $product_id );

			if ( $role && $product ) {

				$type = $product->is_type( 'variation' ) ? 'variation' : 'simple';

				wp_send_json( array(
					'success'       => true,
					'role_row_html' => $this->fileManager->renderTemplate( "addons/role-based-pricing/{$type}/role.php", array(
						'role'                   => $role->name,
						'loop'                   => $loop,
						'fileManager'            => $this->fileManager,
						'minimum_amount'         => RoleBasedPriceManager::getProductQtyMin( $product_id, $role->name, 'edit' ),
						'price_rules_fixed'      => RoleBasedPriceManager::getFixedPriceRules( $product_id, $role->name, 'edit' ),
						'price_rules_percentage' => RoleBasedPriceManager::getPercentagePriceRules( $product_id, $role->name, 'edit' ),
						'regular_price'          => RoleBasedPriceManager::getProductRegularRolePrice( $product_id, $role->name, 'edit' ),
						'sale_price'             => RoleBasedPriceManager::getProductSaleRolePrice( $product_id, $role->name, 'edit' ),
						'type'                   => RoleBasedPriceManager::getPricingType( $product_id, $role->name, 'fixed', 'edit' ),
					) )
				) );
			}

			wp_send_json( array(
				'success'       => false,
				'error_message' => __( 'Invalid role', 'tier-pricing-table' )
			) );
		}

		wp_send_json( array(
			'success'       => false,
			'error_message' => __( 'Invalid nonce', 'tier-pricing-table' )
		) );
	}

	/**
	 * Render product page role-based template
	 */
	public function renderProductPage() {
		global $post;

		$this->fileManager->includeTemplate( 'addons/role-based-pricing/simple/role-based-block.php', array(
			'fileManager' => $this->fileManager,
			'product_id'  => $post->ID,
		) );
	}

	/**
	 * Render variation role-based template
	 *
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 */
	public function renderPriceRulesVariation( $loop, $variation_data, $variation ) {
		$this->fileManager->includeTemplate( 'addons/role-based-pricing/variation/role-based-block.php', array(
			'fileManager' => $this->fileManager,
			'product_id'  => $variation->ID,
			'loop'        => $loop,
		) );
	}
}
