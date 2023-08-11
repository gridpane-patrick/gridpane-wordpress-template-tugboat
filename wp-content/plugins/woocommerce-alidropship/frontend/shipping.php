<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Frontend_Shipping
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Frontend_Shipping {
	private static $settings;
	private $cart_item_key = '';
	private $is_minicart = false;
	protected static $language;

	public function __construct() {
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		self::$language = '';
		if ( self::$settings->get_params( 'enable' ) && self::$settings->get_params( 'ali_shipping' ) ) {
			add_filter( 'woocommerce_cart_item_name', array( $this, 'woocommerce_cart_item_name' ), 10, 3 );
			add_filter( 'woocommerce_get_item_data', array( $this, 'woocommerce_get_item_data' ), 999, 2 );
			add_filter( 'woocommerce_package_rates', array( $this, 'woocommerce_package_rates' ), 99, 2 );
			add_action( 'woocommerce_cart_loaded_from_session', array(
				$this,
				'woocommerce_cart_loaded_from_session'
			), 9, 2 );
			add_action( 'woocommerce_checkout_create_order_line_item', array(
				$this,
				'woocommerce_checkout_create_order_line_item'
			), 10, 4 );
			add_filter( 'woocommerce_update_cart_action_cart_updated', array(
				$this,
				'woocommerce_update_cart_action_cart_updated'
			) );
			add_action( 'woocommerce_checkout_update_order_review', array(
				$this,
				'woocommerce_checkout_update_order_review'
			), 99 );
			add_filter( 'woocommerce_checkout_update_order_review_expired', array(
				$this,
				'woocommerce_checkout_update_order_review_expired'
			) );
			add_action( 'wp_enqueue_scripts', array(
				$this,
				'wp_enqueue_scripts'
			) );
			add_action( 'woocommerce_before_template_part', array(
				$this,
				'woocommerce_before_template_part'
			) );
			add_action( 'woocommerce_after_template_part', array(
				$this,
				'woocommerce_after_template_part'
			) );
			/*Do not show shipping info in mini cart*/
			add_action( 'woocommerce_before_mini_cart', array(
				$this,
				'woocommerce_before_mini_cart'
			) );
			add_action( 'woocommerce_after_mini_cart', array(
				$this,
				'woocommerce_after_mini_cart'
			) );
			/*Do not show shipping info in Side cart - WooCommerce Cart All In One plugin*/
			add_action( 'woocommerce_before_template_part', array(
				$this,
				'woocommerce_before_template_caio'
			) );
			add_action( 'woocommerce_after_template_part', array(
				$this,
				'woocommerce_after_template_caio'
			) );
			add_action( 'wp_footer', array(
				$this,
				'wp_footer'
			) );
			add_action( 'cartimize_cart_html_table_start', array( $this, 'cartimize_cart_html_table_start' ) );
			/*Show shipping options on single product page*/
			if ( self::$settings->get_params( 'ali_shipping_product_enable' ) ) {
				add_action( 'wp_ajax_vi_wad_reload_shipping_single_product', array(
					$this,
					'reload_shipping_single_product'
				) );
				add_action( 'wp_ajax_nopriv_vi_wad_reload_shipping_single_product', array(
					$this,
					'reload_shipping_single_product'
				) );
				add_action( 'woocommerce_add_to_cart', array(
					$this,
					'woocommerce_add_to_cart'
				), 10, 6 );
				if ( self::$settings->get_params( 'ali_shipping_product_position' ) === 'before_cart' ) {
					add_action( 'woocommerce_before_add_to_cart_button', array(
						$this,
						'display_shipping_selection_on_single_product'
					) );
				} else {
					add_action( 'woocommerce_after_add_to_cart_button', array(
						$this,
						'display_shipping_selection_on_single_product'
					) );
				}
			}
		}
	}

	/**
	 * Linear Checkout for WooCommerce by Cartimize
	 */
	public function cartimize_cart_html_table_start() {
		add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_allowed_html' ), 10, 2 );
	}

	/**
	 * Add holder for popup type
	 * This is to avoid css of the container, especially on cart and checkout page
	 */
	public function wp_footer() {
		if ( is_cart() || is_checkout() || is_product() ) {
			?>
            <div class="vi-wad-item-shipping-select-popup-holder"></div>
			<?php
		}
	}

	/**
	 * Set shipping for cart item after product is added to cart
	 *
	 * @param $cart_item_key
	 * @param $product_id
	 * @param $quantity
	 * @param $variation_id
	 * @param $variation
	 * @param $cart_item_data
	 */
	public function woocommerce_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		if ( $product_id && $cart_item_key && ! empty( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
			$company = isset( $_POST['vi_wad_item_shipping'][ $product_id ]['company'] ) ? sanitize_text_field( $_POST['vi_wad_item_shipping'][ $product_id ]['company'] ) : '';
			if ( $company ) {
				if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['vi_wad_item_shipping'] ) ) {
					$quantity = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
				}
				$wpml_product_id   = '';
				$wpml_variation_id = '';
				if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
					global $sitepress;
					$default_lang     = apply_filters( 'wpml_default_language', null );
					$current_language = apply_filters( 'wpml_current_language', null );
					if ( $current_language && $current_language !== $default_lang ) {
						$wpml_object_id = apply_filters(
							'wpml_object_id', $product_id, 'product', false, $sitepress->get_default_language()
						);
						if ( $wpml_object_id != $product_id ) {
							$wpml_product = wc_get_product( $wpml_object_id );
							if ( $wpml_product ) {
								$wpml_product_id = $wpml_object_id;
							}
						}
						if ( $variation_id ) {
							$wpml_object_id = apply_filters(
								'wpml_object_id', $variation_id, 'product', false, $sitepress->get_default_language()
							);
							if ( $wpml_object_id != $variation_id ) {
								$wpml_variation = wc_get_product( $wpml_object_id );
								if ( $wpml_variation ) {
									$wpml_variation_id = $wpml_object_id;
								}
							}
						}
					}
				}
				if ( $wpml_product_id ) {
					$ali_id    = get_post_meta( $wpml_product_id, '_vi_wad_aliexpress_product_id', true );
					$ship_from = get_post_meta( $wpml_product_id, '_vi_wad_aliexpress_variation_ship_from', true );
				} else {
					$ali_id    = get_post_meta( $product_id, '_vi_wad_aliexpress_product_id', true );
					$ship_from = get_post_meta( $product_id, '_vi_wad_aliexpress_variation_ship_from', true );
				}
				if ( $variation_id ) {
					if ( $wpml_variation_id ) {
						$ship_from = get_post_meta( $wpml_variation_id, '_vi_wad_aliexpress_variation_ship_from', true );
					} else {
						$ship_from = get_post_meta( $variation_id, '_vi_wad_aliexpress_variation_ship_from', true );
					}
				}
				$country = self::get_customer_country();
				$state   = $city = '';
				if ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::is_shipping_supported_by_province_city( $country ) ) {
					$state = self::get_customer_state_name( self::get_customer_state(), $country );
					$city  = self::get_customer_city();
				}
				$freight = self::get_shipping( $wpml_product_id ? $wpml_product_id : $product_id, $country, $ship_from, $quantity, $state, $city );
				if ( count( $freight ) ) {
					$search = array_search( $company, array_column( $freight, 'company' ) );
					if ( $search !== false ) {
						$shipping_info                                                      = array(
							'time'          => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_shipping_cache_time( time() ),
							'country'       => $country,
							'company'       => $company,
							'company_name'  => $freight[ $search ]['company_name'],
							'freight'       => $freight,
							'shipping_cost' => $freight[ $search ]['shipping_cost'],
							'delivery_time' => $freight[ $search ]['delivery_time'],
							'quantity'      => $quantity,
							'state'         => $state,
							'city'          => $city,
						);
						WC()->cart->cart_contents[ $cart_item_key ]['vi_wad_item_shipping'] = $shipping_info;
					}
				}
			}
		}
	}

	/**
	 * Ajax load shipping selection on single product
	 */
	public function reload_shipping_single_product() {
		self::$language = isset( $_POST['language'] ) ? sanitize_text_field( $_POST['language'] ) : '';
		$product_id     = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$variation_id   = isset( $_POST['variation_id'] ) ? sanitize_text_field( $_POST['variation_id'] ) : '';
		$quantity       = isset( $_POST['quantity'] ) ? sanitize_text_field( $_POST['quantity'] ) : '';
		$product        = wc_get_product( $product_id );
		$response       = array(
			'status'        => 'error',
			'shipping_html' => '',
			'message'       => 'Error',
		);
		if ( $product ) {
			ob_start();
			$this->single_product_shipping_html( $product, $quantity, $variation_id );
			$response['shipping_html'] = ob_get_clean();
			if ( $response['shipping_html'] ) {
				$response['status']  = 'success';
				$response['message'] = 'Success';
			}
		}
		wp_send_json( $response );
	}

	/**
	 * Display shipping selection on single product
	 */
	public function display_shipping_selection_on_single_product() {
		if ( wp_doing_ajax() ) {
			return;
		}
		global $product;
		if ( $product ) {
			$quantity = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity();
			$this->single_product_shipping_html( $product, $quantity );
		}
	}

	/**
	 * @param $product WC_Product
	 * @param $quantity
	 * @param string $variation_id
	 */
	private function single_product_shipping_html( $product, $quantity, $variation_id = '' ) {
		$product_id      = $product->get_id();
		$wpml_product_id = '';
		if ( self::$language ) {
			global $sitepress;
			$wpml_object_id = apply_filters(
				'wpml_object_id', $product_id, 'product', false, $sitepress->get_default_language()
			);
			if ( $wpml_object_id != $product_id ) {
				$wpml_product = wc_get_product( $wpml_object_id );
				if ( $wpml_product ) {
					$wpml_product_id = $wpml_object_id;
				}
			}
		}
		$ship_from_g = false;
		$country     = self::get_customer_country();
		$state       = $city = '';
		if ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::is_shipping_supported_by_province_city( $country ) ) {
			$state = self::get_customer_state_name( self::get_customer_state(), $country );
			$city  = self::get_customer_city();
			if ( ! $state ) {
				$state = 'Acre';
				$city  = 'Acrelandia';
			} elseif ( ! $city ) {
				$default_city = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_city_code( $country, $state, '' );
				if ( ! empty( $default_city['n'] ) ) {
					$city = $default_city['n'];
				} else {
					$state = '';
				}
			}
		}
		$wc_countries = WC()->countries->get_countries();
		$country_name = isset( $wc_countries[ $country ] ) ? $wc_countries[ $country ] : esc_html__( 'your country', 'woocommerce-alidropship' );
		if ( $product->is_type( 'variable' ) ) {
			$children = $product->get_children();
			if ( count( $children ) ) {
				$ship_froms = array();
				foreach ( $children as $child ) {
					$ship_from_v = get_post_meta( $child, '_vi_wad_aliexpress_variation_ship_from', true );
					if ( $ship_from_v ) {
						$ship_froms[] = $ship_from_v;
					}
				}
				$ship_froms = array_unique( $ship_froms );
				if ( ! $variation_id ) {
					if ( count( $ship_froms ) > 1 ) {
						?>
                        <div class="vi-wad-single-product-shipping-wrap vi-wad-single-product-shipping-need-select-variation">
                            <div class="vi-wad-single-product-shipping-overlay vi-wad-hidden"></div>
                            <div class="vi-wad-single-product-shipping-error"><?php echo str_replace( '{country}', $country_name, self::$settings->get_params( 'ali_shipping_select_variation_message', self::$language ) ) ?></div>
                        </div>
						<?php
						return;
					} elseif ( count( $ship_froms ) === 1 ) {
						$ship_from_g = $ship_froms[0];
					} else {
						$ship_from_g = '';
					}
				}
			}
		}
		if ( $wpml_product_id ) {
			$ali_id = get_post_meta( $wpml_product_id, '_vi_wad_aliexpress_product_id', true );
		} else {
			$ali_id = get_post_meta( $product_id, '_vi_wad_aliexpress_product_id', true );
		}
		if ( $ali_id ) {
			if ( $wpml_product_id ) {
				$ship_from = get_post_meta( $wpml_product_id, '_vi_wad_aliexpress_variation_ship_from', true );
			} else {
				$ship_from = get_post_meta( $product_id, '_vi_wad_aliexpress_variation_ship_from', true );
			}
			$class = 'vi-wad-single-product-shipping-wrap';
			if ( $variation_id ) {
				$ship_from = get_post_meta( $variation_id, '_vi_wad_aliexpress_variation_ship_from', true );
			} elseif ( $ship_from_g ) {
				$ship_from = $ship_from_g;
				$class     .= ' vi-wad-single-product-shipping-not-refresh';
			} elseif ( $ship_from_g === '' && ! $ship_from ) {
				$class .= ' vi-wad-single-product-shipping-not-refresh';
			}
			$freight       = self::get_shipping( $wpml_product_id ? $wpml_product_id : $product_id, $country, $ship_from, $quantity, $state, $city );
			$shipping_info = array(
				'time'          => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_shipping_cache_time( time() ),
				'country'       => $country,
				'company'       => isset( $_POST['vi_wad_item_shipping'][ $product_id ]['company'] ) ? sanitize_text_field( $_POST['vi_wad_item_shipping'][ $product_id ]['company'] ) : '',
				'company_name'  => '',
				'freight'       => $freight,
				'shipping_cost' => '',
				'delivery_time' => '',
				'quantity'      => $quantity,
				'state'         => $state,
				'city'          => $city,
			);
			if ( count( $freight ) ) {
				if ( $shipping_info['company'] ) {
					$search = array_search( $shipping_info['company'], array_column( $freight, 'company' ) );
					if ( $search !== false ) {
						$shipping_info['company_name'] = $freight[ $search ]['company_name'];
					} else {
						$shipping_info['company']      = $freight[0]['company'];
						$shipping_info['company_name'] = $freight[0]['company_name'];
					}
				} else {
					$shipping_info['company']      = $freight[0]['company'];
					$shipping_info['company_name'] = $freight[0]['company_name'];
				}
				$shipping_info['shipping_cost'] = $freight[0]['shipping_cost'];
				$shipping_info['delivery_time'] = $freight[0]['delivery_time'];
			}
			$ali_shipping_display = self::$settings->get_params( 'ali_shipping_product_display' );
			ob_start();
			if ( $shipping_info['shipping_cost'] === '' && self::$settings->get_params( 'ali_shipping_not_available_remove' ) ) {
				$class .= ' vi-wad-single-product-shipping-not-available';
				?>
                <div class="vi-wad-single-product-shipping-error">
					<?php
					echo str_replace( '{country}', $country_name, self::$settings->get_params( 'ali_shipping_product_not_available_message', self::$language ) );
					?>
                </div>
				<?php
			} else {
				?>
                <div class="vi-wad-single-product-shipping-label">
					<?php
					echo str_replace( '{country}', $country_name, self::$settings->get_params( 'ali_shipping_product_text', self::$language ) );
					?>
                </div>
				<?php
				echo $this->show_shipping_selection( $shipping_info, $ali_shipping_display, $product_id, 'vi-wad-single-product-shipping-container' );
			}
			$shipping_detail = ob_get_clean();
			?>
            <div class="<?php echo esc_attr( $class ) ?>">
                <div class="vi-wad-single-product-shipping-overlay vi-wad-hidden"></div>
				<?php
				echo $shipping_detail;
				?>
            </div>
			<?php
		}
	}

	/**
	 * Calculate shipping for whole cart
	 *
	 * @param $methods
	 * @param $package
	 *
	 * @return array
	 */
	public function woocommerce_package_rates( $methods, $package ) {
		if ( ! empty( $package['contents'] ) ) {
			$ali_shipping_type = self::$settings->get_params( 'ali_shipping_type' );
			if ( $ali_shipping_type !== 'none' ) {
				$ali_total_shipping = 0;
				$ali_shipping       = false;
				$items_in_package   = array();
				$remove_cart_item   = self::$settings->get_params( 'ali_shipping_not_available_remove' );
				$not_available_cost = apply_filters( 'vi_wad_frontend_item_not_available_cost', apply_filters( 'wmc_change_3rd_plugin_price', self::$settings->get_params( 'ali_shipping_not_available_cost' ) ) );
				foreach ( $package['contents'] as $cart_item_key => $cart_item ) {
					if ( isset( $cart_item['vi_wad_item_shipping'] ) ) {
						$ali_shipping  = true;
						$shipping_info = $cart_item['vi_wad_item_shipping'];
						if ( $shipping_info['shipping_cost'] !== '' ) {
							$shipping_info['shipping_cost'] = abs( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $shipping_info['shipping_cost'] ) );
							$shipping_info['shipping_cost'] = self::$settings->process_exchange_price( $shipping_info['shipping_cost'] );
							$ali_total_shipping             += apply_filters( 'vi_wad_frontend_item_shipping_cost', apply_filters( 'wmc_change_3rd_plugin_price', $shipping_info['shipping_cost'] ), $shipping_info );
							$items_in_package[]             = $cart_item['data']->get_name() . ' &times; ' . $cart_item['quantity'];
						} else {
							if ( ! $remove_cart_item && $not_available_cost ) {
								$ali_total_shipping += $not_available_cost;
							}
						}
					}
				}
				if ( $ali_shipping ) {
					if ( $ali_total_shipping ) {
						$ali_total_shipping = apply_filters( 'vi_wad_frontend_total_shipping_cost', $ali_total_shipping );
						$id                 = 'flat_rate';
						$label              = self::$settings->get_params( 'ali_shipping_label', self::$language );
						if ( ! $label ) {
							$label = esc_html__( 'Shipping', 'woocommerce-alidropship' );
						}
					} else {
						$id    = 'free_shipping';
						$label = self::$settings->get_params( 'ali_shipping_label_free', self::$language );
						if ( ! $label ) {
							$label = esc_html__( 'Free Shipping', 'woocommerce-alidropship' );
						}
					}
//				if ( ! count( $methods ) && $ali_shipping_type === 'add' ) {
//					$ali_shipping_type = 'new';
//				}
					switch ( $ali_shipping_type ) {
						case 'new':
							/*Create a new shipping method but still show other available shipping methods*/
							$taxes          = WC_Tax::calc_shipping_tax( $ali_total_shipping, WC_Tax::get_shipping_tax_rates() );
							$methods[ $id ] = new WC_Shipping_Rate( $id, $label, $ali_total_shipping, $taxes, $id, '' );
							if ( count( $items_in_package ) ) {
								$methods[ $id ]->add_meta_data( __( 'Items', 'woocommerce' ), implode( ', ', $items_in_package ) );
							}
							break;
						case 'new_only':
							/*Create a new shipping method and make it the only available shipping method*/
							$taxes   = WC_Tax::calc_shipping_tax( $ali_total_shipping, WC_Tax::get_shipping_tax_rates() );
							$methods = array( $id => new WC_Shipping_Rate( $id, $label, $ali_total_shipping, $taxes, $id, '' ) );
							if ( count( $items_in_package ) ) {
								$methods[ $id ]->add_meta_data( __( 'Items', 'woocommerce' ), implode( ', ', $items_in_package ) );
							}
							break;
						case 'add':
							/*Add shipping cost to all available shipping methods*/
							if ( $ali_total_shipping ) {
								foreach ( $methods as $rate_k => $rate ) {
									if ( is_a( $rate, 'WC_Shipping_Rate' ) && $rate && $rate->get_method_id() !== 'free_shipping' ) {
										$cost  = $rate->get_cost() + $ali_total_shipping;
										$taxes = WC_Tax::calc_shipping_tax( $cost, WC_Tax::get_shipping_tax_rates() );
										$methods[ $rate_k ]->set_cost( $cost );
										$methods[ $rate_k ]->set_taxes( $taxes );
									}
								}
							}
							break;
						default:
					}
				}
			}
		}

		return $methods;
	}

	/**
	 * Detect mini cart
	 */
	public function woocommerce_before_mini_cart() {
		$this->is_minicart = true;
	}

	public function woocommerce_after_mini_cart() {
		$this->is_minicart = false;
	}

	/**
	 * Detect Side cart - Cart all in one
	 *
	 * @param $name
	 */
	public function woocommerce_before_template_caio( $name ) {
		if ( $name === 'sc-product-list-html.php' ) {
			$this->is_minicart = true;
		}
	}

	/**
	 * @param $name
	 */
	public function woocommerce_after_template_caio( $name ) {
		if ( $name === 'sc-product-list-html.php' ) {
			$this->is_minicart = false;
		}
	}

	/**
	 * Change allowed html tags to use for cart item shipping html
	 *
	 * @param $name
	 */
	public function woocommerce_before_template_part( $name ) {
		if ( $name === 'cart/cart-item-data.php' ) {
			add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_allowed_html' ), 10, 2 );
		}
	}

	/**
	 * @param $name
	 */
	public function woocommerce_after_template_part( $name ) {
		if ( $name === 'cart/cart-item-data.php' ) {
			remove_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_allowed_html' ), 10 );
		}
	}

	/**
	 * Add select, option, input to allowed html list to print out shipping select
	 *
	 * @param $allowedposttags
	 * @param $context
	 *
	 * @return mixed
	 */
	public function wp_kses_allowed_html( $allowedposttags, $context ) {
		if ( ! isset( $allowedposttags['select'] ) ) {
			$allowedposttags['select'] = array();
		}
		$allowedposttags['select']['id']     = 1;
		$allowedposttags['select']['class']  = 1;
		$allowedposttags['select']['name']   = 1;
		$allowedposttags['select']['data-*'] = 1;
		if ( ! isset( $allowedposttags['option'] ) ) {
			$allowedposttags['option'] = array();
		}
		$allowedposttags['option']['class']    = 1;
		$allowedposttags['option']['value']    = 1;
		$allowedposttags['option']['selected'] = 1;
		$allowedposttags['option']['data-*']   = 1;
		if ( ! isset( $allowedposttags['input'] ) ) {
			$allowedposttags['input'] = array();
		}
		$allowedposttags['input']['type']    = 1;
		$allowedposttags['input']['name']    = 1;
		$allowedposttags['input']['class']   = 1;
		$allowedposttags['input']['value']   = 1;
		$allowedposttags['input']['checked'] = 1;
		$allowedposttags['input']['data-*']  = 1;

		return $allowedposttags;
	}

	/**
	 *
	 */
	public function wp_enqueue_scripts() {
		if ( is_cart() || is_checkout() || ( is_product() && self::$settings->get_params( 'ali_shipping_product_enable' ) ) ) {
			if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
				$default_lang     = apply_filters( 'wpml_default_language', null );
				$current_language = apply_filters( 'wpml_current_language', null );
				if ( $current_language && $current_language !== $default_lang ) {
					self::$language = $current_language;
				}
			}
			wp_enqueue_script( 'woocommerce-alidropship-shipping-select', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'shipping.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_enqueue_style( 'woocommerce-alidropship-shipping-select', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'shipping.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			if ( is_product() ) {
				wp_enqueue_script( 'woocommerce-alidropship-shipping-select-single-product', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'shipping-single-product.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
				wp_enqueue_style( 'woocommerce-alidropship-shipping-select-single-product', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'shipping-single-product.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			}
			wp_localize_script( 'woocommerce-alidropship-shipping-select', 'vi_wad_shipping', array(
				'url'                                           => admin_url( 'admin-ajax.php' ),
				'language'                                      => self::$language,
				'countries_supported_shipping_by_province_city' => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::countries_supported_shipping_by_province_city(),
			) );
		}
	}

	/**
	 * Get cart item key to use later
	 *
	 * @param $name
	 * @param $cart_item
	 * @param $cart_item_key
	 *
	 * @return mixed
	 */
	public function woocommerce_cart_item_name( $name, $cart_item, $cart_item_key ) {
		$this->cart_item_key = $cart_item_key;

		return $name;
	}

	/**
	 * Display shipping selection for cart/checkout
	 *
	 * @param $item_data
	 * @param $cart_item
	 *
	 * @return array
	 */
	public function woocommerce_get_item_data( $item_data, $cart_item ) {
		if ( ! $this->is_minicart && $this->cart_item_key && isset( $cart_item['vi_wad_item_shipping'] ) ) {
			$shipping_info        = $cart_item['vi_wad_item_shipping'];
			$ali_shipping_display = self::$settings->get_params( 'ali_shipping_display' );
			$item_data[]          = array(
				'key'   => esc_html__( 'Shipping', 'woocommerce-alidropship' ),
				'value' => $this->show_shipping_selection( $shipping_info, $ali_shipping_display, $this->cart_item_key, 'vi-wad-cart-item-shipping-container' ),
			);
			$this->cart_item_key  = '';
		}

		return $item_data;
	}

	/**
	 * Shipping selection html
	 *
	 * @param $shipping_info
	 * @param $ali_shipping_display
	 * @param $cart_item_key
	 * @param string $container_class
	 *
	 * @return string
	 */
	public function show_shipping_selection( $shipping_info, $ali_shipping_display, $cart_item_key, $container_class = '' ) {
		if ( $shipping_info['shipping_cost'] === '' ) {
			$shipping_display = $this->get_default_shipping_message();
		} else {
			if ( count( $shipping_info['freight'] ) ) {
				$option_html                = '';
				$popup_item                 = '';
				$ali_shipping_show_tracking = self::$settings->get_params( 'ali_shipping_show_tracking' );
				$option_text                = self::$settings->get_params( 'ali_shipping_option_text', self::$language );
				if ( ! $option_text ) {
					$option_text = self::$settings->get_default( 'ali_shipping_option_text' );
				}
				$mask_company = self::$settings->get_params( 'ali_shipping_company_mask' );
				if ( $mask_company ) {
					$mask_company = vi_wad_json_decode( $mask_company );
				}
				foreach ( $shipping_info['freight'] as $freight_k => $freight_v ) {
					$delivery_time = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::process_delivery_time( $freight_v['delivery_time'] );
					if ( $freight_v['shipping_cost'] == 0 ) {
						$shipping_amount = esc_html__( 'Free', 'woocommerce-alidropship' );
					} else {
						$freight_v['shipping_cost'] = self::$settings->process_exchange_price( $freight_v['shipping_cost'] );
						$shipping_amount            = wc_price( apply_filters( 'vi_wad_frontend_item_shipping_cost', apply_filters( 'wmc_change_3rd_plugin_price', $freight_v['shipping_cost'] ), $freight_v ) );
					}
					$company_name = str_replace( "\xc2\xa0", ' ', $freight_v['company_name'] );
					if ( isset( $mask_company[ $freight_v['company'] ] ) && ! empty( $mask_company[ $freight_v['company'] ]['new'] ) ) {
						$company_name = $mask_company[ $freight_v['company'] ]['new'];
					}
					$company_name        = esc_html( $company_name );
					$option_text_current = str_replace( array(
						'{shipping_cost}',
						'{shipping_company}',
						'{delivery_time}'
					), array( $shipping_amount, $company_name, $delivery_time ), $option_text );
					if ( $ali_shipping_display === 'select' ) {
						ob_start();
						?>
                        <option value="<?php echo esc_attr( $freight_v['company'] ) ?>" <?php selected( $shipping_info['company'], $freight_v['company'] ) ?>><?php echo $option_text_current ?></option>
						<?php
						$option_html .= ob_get_clean();
					} elseif ( $ali_shipping_display === 'radio' ) {
						$option_html .= '<label><input class="vi-wad-item-shipping-select" type="radio"
                                   value="' . esc_attr( $freight_v['company'] ) . '" name="vi_wad_item_shipping[' . esc_attr( $cart_item_key ) . '][company]" ' . checked( $shipping_info['company'], $freight_v['company'], false ) . '>' . $option_text_current . '</label>';

					} else {
						if ( $shipping_info['company'] === $freight_v['company'] ) {
							$option_html .= $option_text_current;
						}
						$tracking_class = 'vi-wad-item-shipping-select-popup-content-item-tracking-availability-' . ( $freight_v['tracking'] ? 'yes' : 'no' );
						ob_start();
						?>
                        <div class="vi-wad-item-shipping-select-popup-content-item vi-wad-item-shipping-select-popup-content-item-company">
                            <input class="vi-wad-item-shipping-select" type="radio"
                                   value="<?php echo esc_attr( $freight_v['company'] ) ?>"
                                   data-shipping_amount_html="<?php echo esc_attr( htmlentities( $option_text_current ) ) ?>"
                                   name="vi_wad_item_shipping[<?php echo esc_attr( $cart_item_key ) ?>][company]" <?php checked( $shipping_info['company'], $freight_v['company'], true ) ?>>
                        </div>
                        <div class="vi-wad-item-shipping-select-popup-content-item vi-wad-item-shipping-select-popup-content-item-delivery-time">
                            <span><?php echo $delivery_time ?></span>
                        </div>
                        <div class="vi-wad-item-shipping-select-popup-content-item vi-wad-item-shipping-select-popup-content-item-shipping-amount">
                            <span><?php echo $shipping_amount ?></span>
                        </div>
                        <div class="vi-wad-item-shipping-select-popup-content-item vi-wad-item-shipping-select-popup-content-item-company-name">
                            <span><?php echo $company_name ?></span>
                        </div>
						<?php
						if ( $ali_shipping_show_tracking ) {
							?>
                            <div class="vi-wad-item-shipping-select-popup-content-item vi-wad-item-shipping-select-popup-content-item-tracking <?php echo esc_attr( $tracking_class ) ?>">
                                <span><?php echo $freight_v['tracking'] ? esc_html__( 'Yes', 'woocommerce-alidropship' ) : esc_html__( 'No', 'woocommerce-alidropship' ); ?></span>
                            </div>
							<?php
						}
						$popup_item .= ob_get_clean();
					}
				}
				if ( $ali_shipping_display === 'select' ) {
					$shipping_display = '<select class="vi-wad-item-shipping-select" name="vi_wad_item_shipping[' . esc_attr( $cart_item_key ) . '][company]">' . $option_html . '</select>';
				} elseif ( $ali_shipping_display === 'radio' ) {
					$shipping_display = $option_html;
				} else {
					$content_class = array( 'vi-wad-item-shipping-select-popup-content' );
					if ( $ali_shipping_show_tracking ) {
						$content_class[] = 'vi-wad-item-shipping-select-popup-content-show-tracking';
					}
					ob_start();
					?>
                    <div class="vi-wad-item-shipping-select-popup"><span
                                class="vi-wad-item-shipping-select-popup-selected"><?php echo $option_html; ?></span>
                    </div>
                    <div class="vi-wad-item-shipping-select-popup-modal vi-wad-hidden">
                        <div class="vi-wad-item-shipping-select-popup-overlay"></div>
                        <div class="vi-wad-item-shipping-select-popup-main">
                            <div class="vi-wad-item-shipping-select-popup-header">
                                <div class="vi-wad-item-shipping-select-popup-header-content">
                                    <div class="vi-wad-item-shipping-select-popup-title">
										<?php echo apply_filters( 'vi_wad_frontend_shipping_popup_title', esc_html__( 'Please select a shipping method', 'woocommerce-alidropship' ) ) ?>
                                    </div>
                                    <span class="vi-wad-item-shipping-select-popup-close"></span>
                                </div>
                            </div>
                            <div class="<?php echo esc_attr( implode( ' ', $content_class ) ); ?>">
                                <div class="vi-wad-item-shipping-select-popup-content-item vi-wad-item-shipping-select-popup-content-head"></div>
                                <div class="vi-wad-item-shipping-select-popup-content-item vi-wad-item-shipping-select-popup-content-head">
									<?php esc_html_e( 'Estimated Delivery', 'woocommerce-alidropship' ) ?>
                                </div>
                                <div class="vi-wad-item-shipping-select-popup-content-item vi-wad-item-shipping-select-popup-content-head">
									<?php esc_html_e( 'Cost', 'woocommerce-alidropship' ) ?>
                                </div>
                                <div class="vi-wad-item-shipping-select-popup-content-item vi-wad-item-shipping-select-popup-content-head">
									<?php esc_html_e( 'Carrier', 'woocommerce-alidropship' ) ?>
                                </div>
								<?php
								if ( $ali_shipping_show_tracking ) {
									?>
                                    <div class="vi-wad-item-shipping-select-popup-content-item vi-wad-item-shipping-select-popup-content-head">
										<?php esc_html_e( 'Tracking', 'woocommerce-alidropship' ) ?>
                                    </div>
									<?php
								}
								echo $popup_item;
								?>
                            </div>
                            <div class="vi-wad-item-shipping-select-popup-footer">
                                <span class="vi-wad-item-shipping-select-popup-confirm"><?php echo apply_filters( 'vi_wad_frontend_shipping_confirm_button_title', esc_html__( 'OK', 'woocommerce-alidropship' ) ) ?></span>
                            </div>
                        </div>
                    </div>
					<?php
					$shipping_display = ob_get_clean();
				}
			} else {
				$shipping_display = $this->get_default_shipping_message();
			}
		}

		return '<div class="vi-wad-item-shipping ' . $container_class . '" data-display_type="' . esc_attr( $ali_shipping_display ) . '">' . $shipping_display . '<input type="hidden" name="vi_wad_language" value="' . esc_attr( self::$language ) . '"></div>';
	}

	/**
	 * Default message when AliExpress shipping is not available
	 *
	 * @return mixed
	 */
	private function get_default_shipping_message() {
		$not_available_message = self::$settings->get_params( 'ali_shipping_not_available_message', self::$language );
		$remove_cart_item      = self::$settings->get_params( 'ali_shipping_not_available_remove' );
		if ( $remove_cart_item ) {
			$default_shipping_message = str_replace( array(
				'{shipping_cost}',
				'{delivery_time}'
			), '', $not_available_message );
		} else {
			$not_available_cost = apply_filters( 'vi_wad_frontend_item_not_available_cost', apply_filters( 'wmc_change_3rd_plugin_price', self::$settings->get_params( 'ali_shipping_not_available_cost' ) ) );
			if ( ! $not_available_cost ) {
				$not_available_cost = esc_html__( 'Free', 'woocommerce-alidropship' );
			} else {
				$not_available_cost = wc_price( $not_available_cost );
			}
			$default_shipping_message = str_replace( array(
				'{shipping_cost}',
				'{delivery_time}'
			), array(
				$not_available_cost,
				VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::process_delivery_time( self::$settings->get_params( 'ali_shipping_not_available_time_min' ) . '-' . self::$settings->get_params( 'ali_shipping_not_available_time_max' ) )
			), $not_available_message );
		}

		return $default_shipping_message;
	}

	/**
	 * Load shipping info for items in cart
	 *
	 * @param $cart
	 */
	public function woocommerce_cart_loaded_from_session( $cart ) {
//		$customer = WC()->session->get( 'customer' );
//		$country  = isset( $customer['shipping_country'] ) ? $customer['shipping_country'] : '';
		if ( isset( $_REQUEST['wc-ajax'] ) && sanitize_text_field( $_REQUEST['wc-ajax'] ) === 'get_refreshed_fragments' ) {
			return;
		}
		$country         = self::get_customer_country();
		$update_shipping = false;
		$nonce_value     = isset( $_POST['woocommerce-shipping-calculator-nonce'] ) ? sanitize_text_field( $_POST['woocommerce-shipping-calculator-nonce'] ) : '';
		if ( ! empty( $_POST['calc_shipping'] ) && ( wp_verify_nonce( $nonce_value, 'woocommerce-shipping-calculator' ) || wp_verify_nonce( $nonce_value, 'woocommerce-cart' ) ) ) { // WPCS: input var ok.
			if ( ! empty( $_POST['calc_shipping_country'] ) ) {
				$update_shipping = true;
				$country         = sanitize_text_field( $_POST['calc_shipping_country'] );
			}
		}

		if ( $country ) {
			$now = time();
			if ( ! empty( $cart->cart_contents ) ) {
				foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
					$company = '';
					if ( isset( $cart_item['vi_wad_item_shipping'] ) ) {
						$shipping_info = $cart_item['vi_wad_item_shipping'];
						if ( ! empty( $shipping_info['company'] ) ) {
							$company = $shipping_info['company'];
						}
						$time = isset( $shipping_info['time'] ) ? $shipping_info['time'] : 0;
						if ( $now - $time < HOUR_IN_SECONDS ) {
							if ( $country === $shipping_info['country'] && $cart_item['quantity'] === $shipping_info['quantity'] ) {
								continue;
							}
							if ( empty( $shipping_info['company'] ) && ! $update_shipping ) {
								continue;
							}
						}
					}
					$ali_id = get_post_meta( $cart_item['product_id'], '_vi_wad_aliexpress_product_id', true );
					if ( $ali_id ) {
						$ship_from = get_post_meta( $cart_item['product_id'], '_vi_wad_aliexpress_variation_ship_from', true );
						if ( $cart_item['variation_id'] ) {
							$ship_from = get_post_meta( $cart_item['variation_id'], '_vi_wad_aliexpress_variation_ship_from', true );
						}
						$state = $city = '';
						if ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::is_shipping_supported_by_province_city( $country ) ) {
							$state = self::get_customer_state_name( self::get_customer_state(), $country );
							$city  = self::get_customer_city();
						}
						$shipping_info            = array(
							'time'          => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_shipping_cache_time( time() ),
							'country'       => $country,
							'company'       => $company,
							'company_name'  => '',
							'freight'       => array(),
							'shipping_cost' => '',
							'delivery_time' => '',
							'quantity'      => 0,
							'state'         => $state,
							'city'          => $city,
						);
						$freight                  = self::get_shipping( $cart_item['product_id'], $country, $ship_from, $cart_item['quantity'], $state, $city );
						$shipping_info['freight'] = $freight;
						if ( $freight ) {
							self::handle_cart_shipping_info( $freight, $cart_item['quantity'], $shipping_info );
						}
						WC()->cart->cart_contents[ $cart_item_key ]['vi_wad_item_shipping'] = $shipping_info;
					}
				}
			}
		}
	}

	/**
	 * Save correct shipping company for respective order items
	 *
	 * @param $item WC_Order_Item_Product
	 * @param $cart_item_key
	 * @param $values
	 * @param $order WC_Order
	 */
	public function woocommerce_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
		if ( isset( $values['vi_wad_item_shipping'] ) ) {
			$cost_added = false;
			switch ( self::$settings->get_params( 'ali_shipping_type' ) ) {
				case 'new':
					$shipping_methods = $order->get_shipping_methods();
					if ( count( $shipping_methods ) ) {
						foreach ( $shipping_methods as $shipping_method ) {
							if ( 'flat_rate' === $shipping_method->get_instance_id() ) {
								$cost_added = true;
								break;
							}
						}
					}
					break;
				case 'new_only':
				case 'add':
					$cost_added = true;
					break;
				case 'none':
				default:
			}
			$shipping_info = $values['vi_wad_item_shipping'];
			if ( ! $shipping_info['shipping_cost'] ) {
				if ( ! $shipping_info['company'] ) {
					if ( ! self::$settings->get_params( 'ali_shipping_not_available_remove' ) ) {
						$shipping_info['shipping_cost'] = apply_filters( 'vi_wad_frontend_item_not_available_cost', apply_filters( 'wmc_change_3rd_plugin_price', self::$settings->get_params( 'ali_shipping_not_available_cost' ) ) );
						$shipping_info['delivery_time'] = self::$settings->get_params( 'ali_shipping_not_available_time_min' ) . '-' . self::$settings->get_params( 'ali_shipping_not_available_time_max' );
					}
				}
			} else {
				$shipping_info['shipping_cost'] = self::$settings->process_exchange_price( $shipping_info['shipping_cost'] );
				$shipping_info['shipping_cost'] = apply_filters( 'vi_wad_frontend_item_shipping_cost', apply_filters( 'wmc_change_3rd_plugin_price', $shipping_info['shipping_cost'] ), $shipping_info );
			}
			if ( ! $shipping_info['shipping_cost'] ) {
				$cost_added = false;
			}
			$item->update_meta_data( '_vi_wot_customer_chosen_shipping', json_encode( array(
				'company'       => $shipping_info['company'],
				'company_name'  => $shipping_info['company_name'],
				'delivery_time' => $shipping_info['delivery_time'],
				'shipping_cost' => $shipping_info['shipping_cost'],
				'quantity'      => $shipping_info['quantity'] ? $shipping_info['quantity'] : $item->get_quantity(),
				'cost_added'    => $cost_added,
			) ) );
		}
	}

	/**
	 * Update cart when selecting an other shipping carrier
	 *
	 * @param $cart_updated
	 *
	 * @return bool
	 */
	public function woocommerce_update_cart_action_cart_updated( $cart_updated ) {
		$country = self::get_customer_country();
		$state   = $city = '';
		if ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::is_shipping_supported_by_province_city( $country ) ) {
			$state = self::get_customer_state_name( self::get_customer_state(), $country );
			$city  = self::get_customer_city();
		}
		$vi_wad_item_shipping = isset( $_POST['vi_wad_item_shipping'] ) ? wc_clean( $_POST['vi_wad_item_shipping'] ) : array();
		self::$language       = isset( $_POST['vi_wad_language'] ) ? sanitize_text_field( $_POST['vi_wad_language'] ) : '';
		if ( ! WC()->cart->is_empty() && is_array( $vi_wad_item_shipping ) && count( $vi_wad_item_shipping ) ) {
			$now            = time();
			$change_company = false;
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( isset( $cart_item['vi_wad_item_shipping'] ) ) {
					$shipping_info = $cart_item['vi_wad_item_shipping'];
					$new_company   = isset( $vi_wad_item_shipping[ $cart_item_key ]['company'] ) ? $vi_wad_item_shipping[ $cart_item_key ]['company'] : '';
					if ( $cart_item['quantity'] !== $shipping_info['quantity'] ) {
						$ali_id = get_post_meta( $cart_item['product_id'], '_vi_wad_aliexpress_product_id', true );
						if ( $ali_id ) {
							$ship_from = get_post_meta( $cart_item['product_id'], '_vi_wad_aliexpress_variation_ship_from', true );
							if ( $cart_item['variation_id'] ) {
								$ship_from = get_post_meta( $cart_item['variation_id'], '_vi_wad_aliexpress_variation_ship_from', true );
							}
							$freight                  = self::get_shipping( $cart_item['product_id'], $country, $ship_from, $cart_item['quantity'], $state, $city );
							$shipping_info['time']    = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_shipping_cache_time( $now );
							$shipping_info['country'] = $country;
							$shipping_info['freight'] = $freight;
							if ( count( $freight ) ) {
								self::handle_cart_shipping_info( $freight, $cart_item['quantity'], $shipping_info );
							}
							WC()->cart->cart_contents[ $cart_item_key ]['vi_wad_item_shipping'] = $shipping_info;
						}
					} elseif ( $shipping_info['country'] === $country && $new_company !== $shipping_info['company'] ) {
						$change_company = true;
						foreach ( $shipping_info['freight'] as $freight_k => $freight_v ) {
							if ( $freight_v['company'] === $new_company ) {
								$shipping_info['company']                                           = $freight_v['company'];
								$shipping_info['company_name']                                      = $freight_v['company_name'];
								$shipping_info['delivery_time']                                     = $freight_v['delivery_time'];
								$shipping_info['shipping_cost']                                     = $freight_v['shipping_cost'];
								$shipping_info['quantity']                                          = $cart_item['quantity'];
								WC()->cart->cart_contents[ $cart_item_key ]['vi_wad_item_shipping'] = $shipping_info;
								$cart_updated                                                       = true;
								break;
							}
						}
					}
				}
			}
			if ( $change_company ) {
				$shipping_packages = WC()->shipping()->get_packages();
				foreach ( $shipping_packages as $shipping_package ) {
					if ( isset( $shipping_package['rates'] ) ) {
						if ( isset( $shipping_package['rates']['flat_rate'] ) ) {
							WC()->session->set( 'chosen_shipping_methods', array( 'flat_rate' ) );
							break;
						} elseif ( isset( $shipping_package['rates']['free_shipping'] ) ) {
							WC()->session->set( 'chosen_shipping_methods', array( 'free_shipping' ) );
							break;
						}
					}
				}
			}
		}

		return $cart_updated;
	}

	/**
	 * Do not expire cart if cart contains removed items because of unavailable Ali shipping
	 *
	 * @param $expired
	 *
	 * @return bool
	 */
	public function woocommerce_checkout_update_order_review_expired( $expired ) {
		$cart = WC()->cart;
		if ( ! empty( $cart->removed_cart_contents ) ) {
			$country = isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : null;
			foreach ( $cart->removed_cart_contents as $cart_item_key => $cart_item ) {
				if ( isset( $cart_item['vi_wad_item_shipping'] ) && isset( $cart_item['vi_wad_remove_no_shipping_item'] ) ) {
					$expired = false;
//					$countries = isset( $cart_item['vi_wad_remove_no_shipping_countries'] ) ? $cart_item['vi_wad_remove_no_shipping_countries'] : array();
//					if ( ! in_array( $country, $countries ) ) {
//						$expired = false;
//					}
				}
			}
		}

		return $expired;
	}

	/**
	 * Update checkout when selecting an other shipping carrier or country
	 *
	 * @param $data
	 */
	public function woocommerce_checkout_update_order_review( $data ) {
		if ( is_string( $data ) ) {
			parse_str( $data, $post_data );
		} else {
			$post_data = array();
		}
		$country        = isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : null;
		$state          = isset( $_POST['state'] ) ? wc_clean( wp_unslash( $_POST['state'] ) ) : null;
		$city           = isset( $_POST['city'] ) ? wc_clean( wp_unslash( $_POST['city'] ) ) : null;
		self::$language = isset( $post_data['vi_wad_language'] ) ? sanitize_text_field( $post_data['vi_wad_language'] ) : '';
		if ( ! wc_ship_to_billing_address_only() && isset( $post_data['ship_to_different_address'] ) && $post_data['ship_to_different_address'] ) {
			$country = isset( $_POST['s_country'] ) ? wc_clean( wp_unslash( $_POST['s_country'] ) ) : null;
			$state   = isset( $_POST['s_state'] ) ? wc_clean( wp_unslash( $_POST['s_state'] ) ) : null;
			$city    = isset( $_POST['s_city'] ) ? wc_clean( wp_unslash( $_POST['s_city'] ) ) : null;
		}
		if ( ! $country ) {
			$country = self::get_customer_country();
		}
		$shipping_by_province_city = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::is_shipping_supported_by_province_city( $country );
		if ( $shipping_by_province_city ) {
			if ( $state ) {
				$states = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_states( $country );
				if ( isset( $states[ $state ] ) ) {
					$state = $states[ $state ];
				} else {
					$state = '';
					$city  = '';
				}
			} else {
				$state = '';
				$city  = '';
			}
		} else {
			$state = '';
			$city  = '';
		}

		$cart = WC()->cart;
		$now  = time();
		if ( ! empty( $cart->removed_cart_contents ) ) {
			/*Restore previously removed items due to shipping not available to a country*/
			foreach ( $cart->removed_cart_contents as $cart_item_key => $cart_item ) {
				if ( isset( $cart_item['vi_wad_item_shipping'] ) && isset( $cart_item['vi_wad_remove_no_shipping_item'] ) ) {
					$countries = isset( $cart_item['vi_wad_remove_no_shipping_countries'] ) ? $cart_item['vi_wad_remove_no_shipping_countries'] : array();

					if ( ! in_array( $country, $countries ) ) {
						unset( WC()->cart->removed_cart_contents[ $cart_item_key ]['vi_wad_remove_no_shipping_item'] );
						if ( WC()->cart->restore_cart_item( $cart_item_key ) ) {

						}
					}
				}
			}
		}
		if ( ! empty( $cart->cart_contents ) ) {
			$change_company   = false;
			$remove_cart_item = self::$settings->get_params( 'ali_shipping_not_available_remove' );
			foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( isset( $cart_item['vi_wad_item_shipping'] ) ) {
					$shipping_info = $cart_item['vi_wad_item_shipping'];
					$new_company   = isset( $post_data['vi_wad_item_shipping'][ $cart_item_key ]['company'] ) ? $post_data['vi_wad_item_shipping'][ $cart_item_key ]['company'] : '';
					if ( $shipping_info['country'] !== $country || ( $shipping_by_province_city && ( ! isset( $shipping_info['state'] ) || $state !== $shipping_info['state'] || ! isset( $shipping_info['city'] ) || $city !== $shipping_info['city'] ) ) ) {
						$ali_id = get_post_meta( $cart_item['product_id'], '_vi_wad_aliexpress_product_id', true );
						if ( $ali_id ) {
							$ship_from = get_post_meta( $cart_item['product_id'], '_vi_wad_aliexpress_variation_ship_from', true );
							if ( $cart_item['variation_id'] ) {
								$ship_from = get_post_meta( $cart_item['variation_id'], '_vi_wad_aliexpress_variation_ship_from', true );
							}
							$freight                  = self::get_shipping( $cart_item['product_id'], $country, $ship_from, $cart_item['quantity'], $state, $city );
							$shipping_info['time']    = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_shipping_cache_time( $now );
							$shipping_info['country'] = $country;
							$shipping_info['state']   = $state;
							$shipping_info['city']    = $city;
							$shipping_info['freight'] = $freight;
							if ( count( $freight ) ) {
								self::handle_cart_shipping_info( $freight, $cart_item['quantity'], $shipping_info );
								WC()->cart->cart_contents[ $cart_item_key ]['vi_wad_item_shipping'] = $shipping_info;
							} else {
								if ( $remove_cart_item ) {
									self::remove_cart_item( $cart_item_key, $cart_item, $country );
								} else {
									$shipping_info['shipping_cost']                                     = '';
									$shipping_info['delivery_time']                                     = '';
									WC()->cart->cart_contents[ $cart_item_key ]['vi_wad_item_shipping'] = $shipping_info;
								}
							}
						}
					} elseif ( $new_company && $new_company !== $shipping_info['company'] ) {
						if ( $cart_item['quantity'] !== $shipping_info['quantity'] ) {
							$change_company = true;
						}
						$has_shipping = false;
						foreach ( $shipping_info['freight'] as $freight_k => $freight_v ) {
							if ( $freight_v['company'] === $new_company ) {
								$has_shipping                                                       = true;
								$shipping_info['company']                                           = $freight_v['company'];
								$shipping_info['company_name']                                      = $freight_v['company_name'];
								$shipping_info['delivery_time']                                     = $freight_v['delivery_time'];
								$shipping_info['shipping_cost']                                     = $freight_v['shipping_cost'];
								$shipping_info['quantity']                                          = $cart_item['quantity'];
								WC()->cart->cart_contents[ $cart_item_key ]['vi_wad_item_shipping'] = $shipping_info;
								break;
							}
						}
						if ( ! $has_shipping && $remove_cart_item ) {
							self::remove_cart_item( $cart_item_key, $cart_item, $country );
						}
					} elseif ( ! count( $shipping_info['freight'] ) && $remove_cart_item ) {
						self::remove_cart_item( $cart_item_key, $cart_item, $country );
					}
				}
			}
			if ( $change_company ) {
				$shipping_packages = WC()->shipping()->get_packages();
				foreach ( $shipping_packages as $shipping_package ) {
					if ( isset( $shipping_package['rates'] ) ) {
						if ( isset( $shipping_package['rates']['flat_rate'] ) ) {
							$_POST['shipping_method'] = array( 'flat_rate' );
							break;
						} elseif ( isset( $shipping_package['rates']['free_shipping'] ) ) {
							$_POST['shipping_method'] = array( 'free_shipping' );
							break;
						}
					}
				}
			}
		}
	}

	/**
	 * Remove cart item if shipping is not available
	 *
	 * @param $cart_item_key
	 * @param $cart_item
	 * @param $country
	 */
	private static function remove_cart_item( $cart_item_key, $cart_item, $country ) {
		if ( $cart_item ) {
			if ( WC()->cart->remove_cart_item( $cart_item_key ) ) {
				$removed_cart_contents                                                                = WC()->cart->removed_cart_contents[ $cart_item_key ];
				WC()->cart->removed_cart_contents[ $cart_item_key ]['vi_wad_remove_no_shipping_item'] = time();
				if ( $country ) {
					$countries                                                                                 = isset( $removed_cart_contents['vi_wad_remove_no_shipping_countries'] ) ? $removed_cart_contents['vi_wad_remove_no_shipping_countries'] : array();
					$countries[]                                                                               = $country;
					$countries                                                                                 = array_unique( $countries );
					WC()->cart->removed_cart_contents[ $cart_item_key ]['vi_wad_remove_no_shipping_countries'] = $countries;
				}
				$product = wc_get_product( $cart_item['product_id'] );
				/* translators: %s: Item name. */
				$item_removed_title = apply_filters( 'woocommerce_cart_item_removed_title', $product ? sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce-alidropship' ), $product->get_name() ) : __( 'Item', 'woocommerce-alidropship' ), $cart_item );
				$wc_countries       = WC()->countries->get_countries();
				$removed_notice     = isset( $wc_countries[ $country ] ) ? sprintf( __( '%s removed because it can not be delivered to %s.', 'woocommerce-alidropship' ), $item_removed_title, $wc_countries[ $country ] ) : sprintf( __( '%s removed because it can not be delivered to your country.', 'woocommerce-alidropship' ), $item_removed_title );
				wc_add_notice( $removed_notice, apply_filters( 'woocommerce_cart_item_removed_notice_type', 'error' ) );
			}
		}
	}

	/**
	 * Get current customer's country from WooCommerce
	 *
	 * @return string
	 */
	private static function get_customer_country() {
		$country = WC()->customer->get_shipping_country();
		if ( ! $country ) {
			$country = WC()->customer->get_billing_country();
		}

		return $country;
	}

	/**
	 * Get current customer's state from WooCommerce
	 *
	 * @return string
	 */
	private static function get_customer_state() {
		$state = WC()->customer->get_shipping_state();
		if ( ! $state ) {
			$state = WC()->customer->get_billing_state();
		}

		return $state;
	}

	/**
	 * Get customer's state name in English
	 *
	 * @param $state
	 * @param $country
	 *
	 * @return mixed
	 */
	private static function get_customer_state_name( $state, $country ) {
		$states = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_states( $country );
		if ( isset( $states[ $state ] ) ) {
			$state = $states[ $state ];
		}

		return $state;
	}

	/**
	 * Get current customer's city from WooCommerce
	 *
	 * @return string
	 */
	private static function get_customer_city() {
		$city = WC()->customer->get_shipping_city();
		if ( ! $city ) {
			$city = WC()->customer->get_billing_city();
		}

		return $city;
	}

	/**
	 * Get AliExpress shipping info
	 *
	 * @param $woo_id
	 * @param $country
	 * @param $ship_from
	 * @param $quantity
	 * @param string $province
	 * @param string $city
	 *
	 * @return mixed|void
	 */
	private static function get_shipping( $woo_id, $country, $ship_from, $quantity, $province = '', $city = '' ) {
		return apply_filters( 'vi_wad_frontend_shipping_options', VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_shipping_by_woo_id( $woo_id, $country, $ship_from, $quantity, $province, $city ), $woo_id, $country, $ship_from, $quantity );
	}

	/**
	 * Set default shipping option for a cart item
	 *
	 * @param $freight
	 * @param $quantity
	 * @param $shipping_info
	 */
	private static function handle_cart_shipping_info( $freight, $quantity, &$shipping_info ) {
		$has_shipping = false;
		if ( ! empty( $shipping_info['company'] ) && self::$settings->get_params( 'ali_shipping_remember_company' ) ) {
			foreach ( $freight as $freight_k => $freight_v ) {
				if ( $shipping_info['company'] === $freight_v['company'] ) {
					$has_shipping                   = true;
					$shipping_info['company']       = $freight_v['company'];
					$shipping_info['company_name']  = $freight_v['company_name'];
					$shipping_info['delivery_time'] = $freight_v['delivery_time'];
					$shipping_info['shipping_cost'] = $freight_v['shipping_cost'];
					$shipping_info['quantity']      = $quantity;
					break;
				}
			}
		}
		if ( ! $has_shipping ) {
			$shipping_info['company']       = $freight[0]['company'];
			$shipping_info['company_name']  = $freight[0]['company_name'];
			$shipping_info['delivery_time'] = $freight[0]['delivery_time'];
			$shipping_info['shipping_cost'] = $freight[0]['shipping_cost'];
			$shipping_info['quantity']      = $quantity;
		}
	}
}