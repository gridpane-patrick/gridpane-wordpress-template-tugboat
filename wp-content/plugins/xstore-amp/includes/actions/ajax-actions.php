<?php

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

class XStore_AMP_ajax extends XStore_AMP {
	
	public $added_to_cart_redirect;
	/**
	 * Hook in ajax handlers.
	 */
	public function __construct() {
		
		$this->added_to_cart_redirect = get_option('woocommerce_cart_redirect_after_add');
		
		add_filter('pre_option_woocommerce_cart_redirect_after_add', function() {
			return 'no';
		});
		
		self::add_ajax_events();
	}
	
	/**
	 * Creates specific ajax functions for AMP.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_ajax_events() {
		$ajax_events_nopriv = array(
			'cart_update',
			'checkout_update',
			'apply_coupon',
			'calculate_shipping',
			'add_to_cart',
			'save_address',
			'save_account_details',
			'process_login',
			'process_registration',
			'process_lost_password',
			'search',
			'dark_light_switcher'
		);
		foreach ( $ajax_events_nopriv as $ajax_event ) {
			add_action( 'wp_ajax_xstore_amp_' . $ajax_event, array( $this, $ajax_event ) );
			add_action( 'wp_ajax_nopriv_xstore_amp_' . $ajax_event, array( $this, $ajax_event ) );
		}
	}
	
	/**
	 * Cart update function.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function cart_update() {
		
		if ( ! ( isset( $_REQUEST['apply_coupon'] ) || isset( $_REQUEST['remove_coupon'] ) || isset( $_REQUEST['remove_item'] ) || isset( $_REQUEST['undo_item'] ) || isset( $_REQUEST['update_cart'] ) || isset( $_REQUEST['proceed'] ) ) ) {
			return;
		}
		
		header("access-control-allow-credentials:true");
		
		header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
		$siteUrl = parse_url(
			get_site_url()
		);
		header("AMP-Access-Control-Allow-Source-Origin:".esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']));
		header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
		header("Content-Type:application/json");
		
		$this->amp_add_query();
		
		wc_nocache_headers();
		
		$nonce_value = wc_get_var( $_REQUEST['woocommerce-cart-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.
		$passed_validation = false;
		$no_changes = true;
		
		if ( ! empty( $_POST['apply_coupon'] ) && ! empty( $_POST['coupon_code'] ) ) {
			WC()->cart->add_discount( wc_format_coupon_code( wp_unslash( $_POST['coupon_code'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$passed_validation = true;
			$no_changes = false;
		}
		
		// Update Cart - checks apply_coupon too because they are in the same form.
		if ( ( ! empty( $_POST['apply_coupon'] ) || ! empty( $_POST['update_cart'] ) || ! empty( $_POST['proceed'] ) ) && wp_verify_nonce( $nonce_value, 'woocommerce-cart' ) ) {
			
			$cart_updated = false;
			$cart_totals  = isset( $_POST['cart'] ) ? wp_unslash( $_POST['cart'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			
			if ( ! WC()->cart->is_empty() && is_array( $cart_totals ) ) {
				foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
					
					$_product = $values['data'];
					
					// Skip product if no updated quantity was posted.
					if ( ! isset( $cart_totals[ $cart_item_key ] ) || ! isset( $cart_totals[ $cart_item_key ]['qty'] ) ) {
						continue;
					}
					
					// Sanitize.
					$quantity = apply_filters( 'woocommerce_stock_amount_cart_item', wc_stock_amount( preg_replace( '/[^0-9\.]/', '', $cart_totals[ $cart_item_key ]['qty'] ) ), $cart_item_key );
					
					if ( '' === $quantity || $quantity === $values['quantity'] ) {
						continue;
					}
					
					// Update cart validation.
					$passed_validation = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $values, $quantity );
					
					// is_sold_individually.
					if ( $_product->is_sold_individually() && $quantity > 1 ) {
						/* Translators: %s Product title. */
//					    wc_add_notice( sprintf( __( 'You can only have 1 %s in your cart.', 'xstore-amp' ), $_product->get_name() ), 'error' );
						$passed_validation = false;
					}
					
					if ( $passed_validation ) {
						WC()->cart->set_quantity( $cart_item_key, $quantity, false );
						$cart_updated = true;
					}
				}
			}
			
			// Trigger action - let 3rd parties update the cart if they need to and update the $cart_updated variable.
			$cart_updated = apply_filters( 'woocommerce_update_cart_action_cart_updated', $cart_updated );
			
			if ( $cart_updated ) {
				WC()->cart->calculate_totals();
			}
			
			$no_changes = false;
			
			if ( ! empty( $_POST['proceed'] ) ) {
				$passed_validation = true;
			} elseif ( $cart_updated ) {
				$passed_validation = true;
			}
		}
		
		if ( $passed_validation || !$no_changes) {
			$Site_url = esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']);
			header('AMP-Access-Control-Allow-Source-Origin: '.esc_url($Site_url));
			if ( !$passed_validation ) {
				$message = 'Change anything and update your cart then';
			}
			else {
				if ( is_ssl() ) {
					$url = wc_get_cart_url();
					header( "AMP-Redirect-To: " . ( $url ) );
					header( "Access-Control-Expose-Headers:AMP-Redirect-To" );
					$message = 'Cart updated!';
				} else {
					$message = 'Cart updated! Please refresh the page now';
				}
			}
			$data = array(
				'status'      => 'success',
				'success_detail' => $message
			);
			
		}
		else {
			header('HTTP/1.1 500 FORBIDDEN');
			$data = array(
				'errors' =>
					array(
						'error_detail' => 'Cart was not updated'
					),
			);
		}
		
		wp_send_json( $data );
		die;
	}
	
	/**
	 * Description of the function.
	 *
	 * @since 1.0.2
	 *
	 * @return void
	 */
	function checkout_update() {
		
		header("access-control-allow-credentials:true");
		header("access-control-allow-headers:Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");
		header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
		$siteUrl = parse_url(
			get_site_url()
		);
		header("AMP-Access-Control-Allow-Source-Origin:".esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']));
		header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
		header("Content-Type:application/json");
		
//		header("HTTP/1.1 200 OK");
//
//		wp_send_json(array(
//			'response'=>'sdfsdf'
//		));
//
//		die();
		
//		check_ajax_referer( 'update-order-review', 'security' );
		
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );
		
//		if ( WC()->cart->is_empty() && ! is_customize_preview() && apply_filters( 'woocommerce_checkout_update_order_review_expired', true ) ) {
//			self::update_order_review_expired();
//		}
		
//		header("HTTP/1.1 200 OK");
		
//		write_log($_POST);
//		$_POST = implode('', $_POST);
//		write_log($_POST);
//
//		$data = preg_match_all('/([^=]*?)=([^&]*)&?/', $_POST, $matches);
//
//		$_POST = array_combine($matches[1], $matches[2]);
		
//		write_log($_POST);
		
//		write_log($_POST . ' 1');
//		write_log(json_encode($_POST) . ' 2');
//		$data_json = json_decode($_POST, JSON_UNESCAPED_UNICODE);
//		echo $data_json;
//
//		die();
		
		do_action( 'woocommerce_checkout_update_order_review', isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
		$posted_shipping_methods = isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : array();
		
		if ( is_array( $posted_shipping_methods ) ) {
			foreach ( $posted_shipping_methods as $i => $value ) {
				$chosen_shipping_methods[ $i ] = $value;
			}
		}
		
		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
		WC()->session->set( 'chosen_payment_method', empty( $_POST['payment_method'] ) ? '' : wc_clean( wp_unslash( $_POST['payment_method'] ) ) );
		WC()->customer->set_props(
			array(
				'billing_country'   => isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : null,
				'billing_state'     => isset( $_POST['state'] ) ? wc_clean( wp_unslash( $_POST['state'] ) ) : null,
				'billing_postcode'  => isset( $_POST['postcode'] ) ? wc_clean( wp_unslash( $_POST['postcode'] ) ) : null,
				'billing_city'      => isset( $_POST['city'] ) ? wc_clean( wp_unslash( $_POST['city'] ) ) : null,
				'billing_address_1' => isset( $_POST['address'] ) ? wc_clean( wp_unslash( $_POST['address'] ) ) : null,
				'billing_address_2' => isset( $_POST['address_2'] ) ? wc_clean( wp_unslash( $_POST['address_2'] ) ) : null,
			)
		);
		
		if ( wc_ship_to_billing_address_only() ) {
			WC()->customer->set_props(
				array(
					'shipping_country'   => isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : null,
					'shipping_state'     => isset( $_POST['state'] ) ? wc_clean( wp_unslash( $_POST['state'] ) ) : null,
					'shipping_postcode'  => isset( $_POST['postcode'] ) ? wc_clean( wp_unslash( $_POST['postcode'] ) ) : null,
					'shipping_city'      => isset( $_POST['city'] ) ? wc_clean( wp_unslash( $_POST['city'] ) ) : null,
					'shipping_address_1' => isset( $_POST['address'] ) ? wc_clean( wp_unslash( $_POST['address'] ) ) : null,
					'shipping_address_2' => isset( $_POST['address_2'] ) ? wc_clean( wp_unslash( $_POST['address_2'] ) ) : null,
				)
			);
		} else {
			WC()->customer->set_props(
				array(
					'shipping_country'   => isset( $_POST['s_country'] ) ? wc_clean( wp_unslash( $_POST['s_country'] ) ) : null,
					'shipping_state'     => isset( $_POST['s_state'] ) ? wc_clean( wp_unslash( $_POST['s_state'] ) ) : null,
					'shipping_postcode'  => isset( $_POST['s_postcode'] ) ? wc_clean( wp_unslash( $_POST['s_postcode'] ) ) : null,
					'shipping_city'      => isset( $_POST['s_city'] ) ? wc_clean( wp_unslash( $_POST['s_city'] ) ) : null,
					'shipping_address_1' => isset( $_POST['s_address'] ) ? wc_clean( wp_unslash( $_POST['s_address'] ) ) : null,
					'shipping_address_2' => isset( $_POST['s_address_2'] ) ? wc_clean( wp_unslash( $_POST['s_address_2'] ) ) : null,
				)
			);
		}
		
		if ( isset( $_POST['has_full_address'] ) && wc_string_to_bool( wc_clean( wp_unslash( $_POST['has_full_address'] ) ) ) ) {
			WC()->customer->set_calculated_shipping( true );
		} else {
			WC()->customer->set_calculated_shipping( false );
		}
		
		WC()->customer->save();
		
		// Calculate shipping before totals. This will ensure any shipping methods that affect things like taxes are chosen prior to final totals being calculated. Ref: #22708.
		WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();
		
		// Get order review fragment.
		ob_start();
		woocommerce_order_review();
		$woocommerce_order_review = ob_get_clean();
//
//		// Get checkout payment fragment.
		ob_start();
		woocommerce_checkout_payment();
		$woocommerce_checkout_payment = ob_get_clean();
		
		// Get messages if reload checkout is not true.
		$reload_checkout = isset( WC()->session->reload_checkout );
		if ( ! $reload_checkout ) {
			$messages = wc_print_notices( true );
		} else {
			$messages = '';
		}
		
		unset( WC()->session->refresh_totals, WC()->session->reload_checkout );
		
		if ( empty($messages) ) {
			header("HTTP/1.1 200 OK");
		}
		else {
			header("HTTP/1.1 500 FIELDS REQUIRED");
		}
		wp_send_json(
			array(
				'result'    => empty( $messages ) ? 'success' : 'failure',
				'messages'  => $messages,
				'reload'    => $reload_checkout,
				'fragments' => apply_filters(
					'woocommerce_update_order_review_fragments',
					array(
						'.woocommerce-checkout-review-order-table' => $woocommerce_order_review,
						'.woocommerce-checkout-payment' => $woocommerce_checkout_payment,
					)
				),
			)
		);
	}
	
	/**
	 * Apply coupon function.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function apply_coupon() {
		
		header("access-control-allow-credentials:true");
		
		header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
		$siteUrl = parse_url(
			get_site_url()
		);
		header("AMP-Access-Control-Allow-Source-Origin:".esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']));
		header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
		header("Content-Type:application/json");
		
		$this->amp_add_query();
		
//		wc_nocache_headers();
		
		$updated = false;
		$error_detail = esc_html__('Coupon was not applied', 'xstore-amp');
		
		if ( ! empty( $_POST['apply_coupon'] ) && ! empty( $_POST['coupon_code'] ) ) {
			try {
				WC()->cart->add_discount( wc_format_coupon_code( wp_unslash( $_POST['coupon_code'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				WC()->cart->calculate_totals();
				if ( 0 === wc_notice_count( 'error' ) ) {
					$updated = true;
				}
			}
			catch ( Exception $e ) {
				$error_detail = $e->getMessage(); // never works
				$updated = false;
			}
		}
		else {
			$error_detail = esc_html__('Coupon code is empty', 'xstore-amp');
		}
		
//		wc_clear_notices();
		
		$Site_url = esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']);
		header('AMP-Access-Control-Allow-Source-Origin: '.esc_url($Site_url));
		if ( $updated ) {
			if ( is_ssl() ) {
				$url = wc_get_checkout_url();
				$url = user_trailingslashit( trailingslashit( $url ) );
				$url = str_replace( 'http:', 'https:', $url );
				header( "AMP-Redirect-To: " . ( $url ) );
				header( "Access-Control-Expose-Headers:AMP-Redirect-To" );
				//header("Access-Control-Expose-Headers: AMP-Redirect-To, AMP-Access-Control-Allow-Source-Origin");
				$message = esc_html__('Coupon applied!', 'xstore-amp');
			} else {
				$message = esc_html__('Coupon applied! Please refresh the page now', 'xstore-amp');
			}
			$data = array(
				'status'      => 'success',
				'success_detail' => $message
			);
		}
		else {
			header('HTTP/1.1 500 FORBIDDEN');
			$data = array(
				'errors' =>
					array(
						'error_detail' => $error_detail
					),
			);
		}
		
		wp_send_json( $data );
		die;
	}
	
	function calculate_shipping() {
		header("access-control-allow-credentials:true");
		
		header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
		$siteUrl = parse_url(
			get_site_url()
		);
		header("AMP-Access-Control-Allow-Source-Origin:".esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']));
		header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
		header("Content-Type:application/json");
		
		$this->amp_add_query();

//		wc_nocache_headers();
		
		$updated = false;
		
		try {
			WC()->shipping()->reset_shipping();
			
			$address = array();
			
			$address['country']  = isset( $_POST['calc_shipping_country'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_country'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
			$address['state']    = isset( $_POST['calc_shipping_state'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_state'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
			$address['postcode'] = isset( $_POST['calc_shipping_postcode'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_postcode'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
			$address['city']     = isset( $_POST['calc_shipping_city'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_city'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
			
			$address = apply_filters( 'woocommerce_cart_calculate_shipping_address', $address );
			
			if ( $address['postcode'] && ! WC_Validation::is_postcode( $address['postcode'], $address['country'] ) ) {
				throw new Exception( __( 'Please enter a valid postcode / ZIP.', 'woocommerce' ) );
			} elseif ( $address['postcode'] ) {
				$address['postcode'] = wc_format_postcode( $address['postcode'], $address['country'] );
			}
			
			if ( $address['country'] ) {
				if ( ! WC()->customer->get_billing_first_name() ) {
					WC()->customer->set_billing_location( $address['country'], $address['state'], $address['postcode'], $address['city'] );
				}
				WC()->customer->set_shipping_location( $address['country'], $address['state'], $address['postcode'], $address['city'] );
			} else {
				WC()->customer->set_billing_address_to_base();
				WC()->customer->set_shipping_address_to_base();
			}
			
			WC()->customer->set_calculated_shipping( true );
			WC()->customer->save();
			
			wc_add_notice( __( 'Shipping costs updated.', 'woocommerce' ), 'notice' );
			
			do_action( 'woocommerce_calculated_shipping' );
			
		} catch ( Exception $e ) {
			if ( ! empty( $e ) ) {
				wc_add_notice( $e->getMessage(), 'error' );
			}
		}
		
		if ( 0 === wc_notice_count( 'error' ) ) {
			$updated = true;
		}

//		wc_clear_notices();
		
		$Site_url = esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']);
		header('AMP-Access-Control-Allow-Source-Origin: '.esc_url($Site_url));
		if ( $updated ) {
			if ( is_ssl() ) {
				$url = wc_get_checkout_url();
				$url = user_trailingslashit( trailingslashit( $url ) );
				$url = str_replace( 'http:', 'https:', $url );
				header( "AMP-Redirect-To: " . ( $url ) );
				header( "Access-Control-Expose-Headers:AMP-Redirect-To" );
				//header("Access-Control-Expose-Headers: AMP-Redirect-To, AMP-Access-Control-Allow-Source-Origin");
				$message = esc_html__('Shipping is changed!', 'xstore-amp');
			} else {
				$message = esc_html__('Shipping is changed! Please refresh the page now', 'xstore-amp');
			}
			$data = array(
				'status'      => 'success',
				'success_detail' => $message
			);
		}
		else {
			header('HTTP/1.1 500 FORBIDDEN');
			$data = array(
				'errors' =>
					array(
						'error_detail' => esc_html__('Error', 'xstore-amp')
					),
			);
		}
		
		wp_send_json( $data );
		die;
	}
	
	/**
	 * Function for add to cart and getting product variation data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function add_to_cart() {
		global $woocommerce, $xstore_amp_vars;
		
		$this->amp_add_query();
		
		wc_nocache_headers();
		
		wc_clear_notices();
		
		$count = WC()->cart->get_cart_contents_count();
		$data = array();
		if ( isset($_REQUEST['variation_id']) ) {
			$variable_product = wc_get_product( absint( $_REQUEST['variation_id'] ) );
			if ( !is_object($variable_product)) {
				header( 'HTTP/1.1 403 Forbidden' );
				$data =
					array(
						'errors' =>
							array(
								'error_detail' => esc_html__('Please choose product options', 'xstore-amp')
							),
					);
				wp_send_json( $data );
				die;
			}
			$variable_product_image = $variable_product->get_image_id();
			$main_image = wp_get_attachment_image_src( $variable_product_image, 'woocommerce_single' );
			$main_image = array(
				'src' => $main_image[0],
				'width' => $main_image[1],
				'height' => $main_image[2],
			);
			$thumb_image = wp_get_attachment_image_src( $variable_product_image, array(100,100) );
			$thumb_image = array(
				'src' => $thumb_image[0],
				'width' => $thumb_image[1],
				'height' => $thumb_image[2],
			);
			$data['variation'] = array(
				'price' => $variable_product->get_price(),
				'regular_price' => $variable_product->get_regular_price(),
				'sku' => ( $sku = $variable_product->get_sku() ) ? $sku : esc_html__( 'N/A', 'xstore-amp' ),
				'main_image' => $main_image,
				'thumb_image' => $thumb_image
			);
		}
		
		if ( isset($_POST['amp-get-variation']) && $_POST['amp-get-variation'] == 'yes') {
			unset($_POST['amp-get-variation']);
			unset($_POST['product_id']);
			$data = array_merge(
				$data,
				array(
					'count' => $count,
				)
			);
			wp_send_json( $data );
			die;
		}
		
		$domain_url = (isset( $_SERVER['HTTPS'] ) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		$is_ssl = is_ssl();
		
		header( "Content-type: application/json" );
		header( "Access-Control-Allow-Credentials: true" );
		header( "Access-Control-Allow-Origin: *.ampproject.org" );
		header( "Access-Control-Expose-Headers: AMP-Access-Control-Allow-Source-Origin" );
		header( "AMP-Access-Control-Allow-Source-Origin: " . $domain_url );
		
		if ( $xstore_amp_vars['cart_count'] < $count) {
			header( 'HTTP/1.1 200 OK' );
			$data = array_merge(
				$data,
				array(
					'status'      => 'success',
					'count' => $count,
					'hide_item' => false,
				)
			);
			
			if ( 'yes' == $this->added_to_cart_redirect ) {
				
				if ( $is_ssl ) {
					header( "AMP-Redirect-To:".wc_get_cart_url() );
					header( "Access-Control-Expose-Headers:AMP-Redirect-To" );
				}
			}
			else {
				$data['popup_message'] = sprintf(esc_html__('%1s Your product has been %2s successfully added to cart!%3s', 'xstore-amp'), '<h3 class="popup-title">', '<br/>', '</h3>').
				                         '<a class="button bordered medium arrow-left icon" href="'.apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ).'">'.
				                                                    '<i class="et-icon et-shop"></i>'.'</a>'.
				                         '<a class="button medium arrow-right" href="'.wc_get_cart_url().'">'.esc_html__('View cart', 'xstore-amp').'</a>';
			}
			
			wp_send_json( $data );
			die;
		}
		else {
			header( 'HTTP/1.1 403 Forbidden' );
			$data =
				array(
					'errors' =>
						array(
							'error_detail' => (( $xstore_amp_vars['cart_count'] == $count || empty($xstore_amp_vars['cart_count'])) ? esc_html__('Please choose product options', 'xstore-amp') : esc_html__('Error while adding to cart', 'xstore-amp'))
						),
			);
			wp_send_json( $data );
			die;
		}
	}
	
	/**
	 * Save address function.
	 * Uses for addresses in my-account billing/shipping tabs
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function save_address(){
		global $woocommerce, $wp;
		
		header("access-control-allow-credentials:true");
		header("access-control-allow-headers:Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");
		header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
		$siteUrl = parse_url(get_site_url());
		header("AMP-Access-Control-Allow-Source-Origin:".esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']));
		header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
		header("Content-Type:application/json");
		
		$data = array(
			'status'      => 'error',
			'success_detail' => esc_html__( 'Success', 'xstore-amp' ),
			'errors' => array()
		);
		
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			header('HTTP/1.1 500 FORBIDDEN');
			$error = esc_html__('Not requested method', 'xstore-amp');
			wc_add_notice( $error, 'error' );
			$data['errors'][] = array(
				'error_detail' => $error
			);
			echo wp_json_encode($data);
			exit;
		}
		
		$nonce_value = wc_get_var( $_REQUEST['wc-edit-address-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.
		
		if (  wp_verify_nonce( $nonce_value, 'woocommerce-edit_address' ) ) {
			header('HTTP/1.1 500 FORBIDDEN');
			$error = esc_html__('Sorry, your nonce did not verify.', 'xstore-amp');
			wc_add_notice( $error, 'error' );
			$data['errors'][] = array(
				'error_detail' => $error
			);
			echo wp_json_encode($data);
			die;
		}
		
		$user_id = get_current_user_id();
		
		if ( $user_id <= 0 ) {
			$error = esc_html__('Not an authorized user.', 'xstore-amp');
			wc_add_notice( $error, 'error' );
			$data['errors'][] = array(
				'error_detail' => $error
			);
			die;
		}
		
		$customer = new WC_Customer( $user_id );
		
		if ( ! $customer ) {
			$error = esc_html__('Not a woocommerce authorized user.', 'xstore-amp');
			wc_add_notice( $error, 'error' );
			$data['errors'][] = array(
				'error_detail' => $error
			);
			echo wp_json_encode($data);
			die;
		}
		
		$load_address = '';
		
		if( isset($_POST['address_type'] ) ){
			if ( $_POST['address_type'] == 'edit_billing_address' ) {
				$load_address = 'billing';
			}
			elseif ( $_POST['address_type'] == 'edit_shipping_address' ) {
				$load_address = 'shipping';
			}
		}
		
		$address = WC()->countries->get_address_fields( esc_attr( $_POST[ $load_address . '_country' ] ), $load_address . '_' );
		
		foreach ( $address as $key => $field ) {
			
			$field['type'] = isset( $field['type'] ) ? $field['type'] : 'text';
			
			// Get Value.
			if ( 'checkbox' === $field['type'] ) {
				$value = (int) isset( $_POST[ $key ] );
			} else {
				$value = isset( $_POST[ $key ] ) ? wc_clean( wp_unslash( $_POST[ $key ] ) ) : '';
			}
			
			// Hook to allow modification of value.
			$value = apply_filters( 'woocommerce_process_myaccount_field_' . $key, $value );
			
			// Validation: Required fields.
			
			if ( ! empty( $field['required'] ) && empty( $value ) ) {
				$error = sprintf( __( '%s is a required field.', 'xstore-amp' ), $field['label'] );
				wc_add_notice( $error, 'error' );
				$data['errors'][] = array(
					'error_detail' => $error
				);
			}
			
			if ( ! empty( $value ) ) {
				
				// Validation rules.
				if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
					foreach ( $field['validate'] as $rule ) {
						switch ( $rule ) {
							case 'postcode' :
								$value = strtoupper( str_replace( ' ', '', $value ) );
								
								if ( ! WC_Validation::is_postcode( $value, $_POST[ $load_address . '_country' ] ) ) {
									$error = __( 'Please enter a valid postcode / ZIP.', 'xstore-amp' );
									wc_add_notice( $error, 'error' );
									$data['errors'][] = array(
										'error_detail' => $error
									);
								} else {
									$value = wc_format_postcode( $value, $_POST[ $load_address . '_country' ] );
								}
								break;
							case 'phone' :
								if ( ! WC_Validation::is_phone( $value ) ) {
									$error = sprintf( __( '%s is not a valid phone number.', 'xstore-amp' ),  $field['label'] );
									wc_add_notice( $error, 'error' );
									$data['errors'][] = array(
										'error_detail' => $error
									);
								}
								break;
							case 'email' :
								$value = strtolower( $value );
								
								if ( ! is_email( $value ) ) {
									$error = sprintf( __( '%s is not a valid email address.', 'xstore-amp' ), $field['label'] );
									wc_add_notice( $error, 'error' );
									$data['errors'][] = array(
										'error_detail' => $error
									);
								}
								break;
						}
					}
				}
			}
			
			try {
				// Set prop in customer object.
				if ( is_callable( array( $customer, "set_$key" ) ) ) {
					$customer->{"set_$key"}( wc_clean( $value ) );
				} else {
					$customer->update_meta_data( $key, wc_clean( $value ) );
				}
				
				if ( WC()->customer && is_callable( array( WC()->customer, "set_$key" ) ) ) {
					WC()->customer->{"set_$key"}( wc_clean( $value ) );
				}
			} catch ( WC_Data_Exception $e ) {
				// Set notices. Ignore invalid billing email, since is already validated.
				if ( 'customer_invalid_billing_email' !== $e->getErrorCode() ) {
					wc_add_notice( $e->getMessage(), 'error' );
					$data['errors'][] = array(
						'error_detail' => $e->getMessage()
					);
					echo wp_json_encode($data);
					exit;
				}
			}
		}
		
		if ( 0 < wc_notice_count( 'error' ) ) {
			header("HTTP/1.1 500 FIELDS REQUIRED");
			wc_clear_notices();
			echo wp_json_encode($data);
			exit;
		}
		
	    $customer->save();
		
		$redirect_url = wc_get_endpoint_url( 'edit-address', '', wc_get_page_permalink( 'myaccount' ));
//		$redirect_url =  str_replace('http:', 'https:', $redirect_url); ?
		header( 'HTTP/1.1 200 OK' );
		header("Content-type: application/json");
		header("Access-Control-Allow-Credentials: true");
		header("Access-Control-Allow-Origin:".$_SERVER['HTTP_ORIGIN']);
		header("AMP-Access-Control-Allow-Source-Origin: ".$siteUrl['scheme'].'://'.$siteUrl['host']);
		if ( is_ssl() ) {
			header( "AMP-Redirect-To: " . $redirect_url );
			header( "Access-Control-Expose-Headers: AMP-Redirect-To, AMP-Access-Control-Allow-Source-Origin" );
		}
		else {
			$data['success_detail'] = esc_html__( 'Success. Please, refresh page to see changes', 'xstore-amp' );
		}
		
		wp_send_json( $data );
		die;
	}
	
	/**
	 * Function for saving account details.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function save_account_details() {
		global $woocommerce, $wp;
		
		header("access-control-allow-credentials:true");
		header("access-control-allow-headers:Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");
		header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
		$siteUrl = parse_url(get_site_url());
		header("AMP-Access-Control-Allow-Source-Origin:".esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']));
		header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
		header("Content-Type:application/json");
		
		$this->amp_add_query();
		
		$nonce_value = wc_get_var( $_REQUEST['amp-save-account-details-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.
		
		if ( wp_verify_nonce( $nonce_value, 'save_account_details' ) ) {
			header('HTTP/1.1 500 FORBIDDEN');
			echo wp_json_encode( array(
					'errors' => array(
						'error_detail' => esc_html__('Sorry, your nonce did not verify.', 'xstore-amp')
					))
			);
			exit;
		}
		
		$data = array(
			'status'      => 'error',
			'success_detail' => esc_html__( 'Success', 'xstore-amp' ),
			'errors' => array()
		);
		
		$user_id = get_current_user_id();
		
		if ( $user_id <= 0 ) {
			header('HTTP/1.1 500 FORBIDDEN');
			echo wp_json_encode( array(
					'errors' => array(
						'error_detail' => esc_html__('User is not valid.', 'xstore-amp')
					))
			);
			exit;
		}
		
		$account_first_name   = ! empty( $_POST['account_first_name'] ) ? wc_clean( wp_unslash( $_POST['account_first_name'] ) ) : '';
		$account_last_name    = ! empty( $_POST['account_last_name'] ) ? wc_clean( wp_unslash( $_POST['account_last_name'] ) ) : '';
		$account_display_name = ! empty( $_POST['account_display_name'] ) ? wc_clean( wp_unslash( $_POST['account_display_name'] ) ) : '';
		$account_email        = ! empty( $_POST['account_email'] ) ? wc_clean( wp_unslash( $_POST['account_email'] ) ) : '';
		$pass_cur             = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$pass1                = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$pass2                = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$save_pass            = true;
		
		// Current user data.
		$current_user       = get_user_by( 'id', $user_id );
		$current_first_name = $current_user->first_name;
		$current_last_name  = $current_user->last_name;
		$current_email      = $current_user->user_email;
		
		// New user data.
		$user               = new stdClass();
		$user->ID           = $user_id;
		$user->first_name   = $account_first_name;
		$user->last_name    = $account_last_name;
		$user->display_name = $account_display_name;
		
		// Prevent display name to be changed to email.
		if ( is_email( $account_display_name ) ) {
			$error = esc_html__( 'Display name cannot be changed to email address due to privacy concern.', 'xstore-amp' );
			wc_add_notice( $error, 'error' );
		}
		
		// Handle required fields.
		$required_fields = apply_filters(
			'woocommerce_save_account_details_required_fields',
			array(
				'account_first_name'   => __( 'First name', 'xstore-amp' ),
				'account_last_name'    => __( 'Last name', 'xstore-amp' ),
				'account_display_name' => __( 'Display name', 'xstore-amp' ),
				'account_email'        => __( 'Email address', 'xstore-amp' ),
			)
		);
		
		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $_POST[ $field_key ] ) ) {
				/* translators: %s: Field name. */
				$error = sprintf( __( '%s is a required field.', 'xstore-amp' ), '<strong>' . esc_html( $field_name ) . '</strong>' );
				wc_add_notice( $error, 'error', array( 'id' => $field_key ) );
				$data['errors'][] = array(
					'error_detail' => $error
				);
			}
		}
		
		if ( $account_email ) {
			$account_email = sanitize_email( $account_email );
			if ( ! is_email( $account_email ) ) {
				$error = __( 'Please provide a valid email address.', 'xstore-amp' );
				wc_add_notice( $error, 'error' );
				$data['errors'][] = array(
					'error_detail' => $error
				);
			} elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
				$error = __( 'This email address is already registered.', 'xstore-amp' );
				wc_add_notice( $error, 'error' );
				$data['errors'][] = array(
					'error_detail' => $error
				);
			}
			$user->user_email = $account_email;
		}
		
		if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
			$error = __( 'Please fill out all password fields.', 'xstore-amp' );
			wc_add_notice( $error, 'error' );
			$data['errors'][] = array(
				'error_detail' => $error
			);
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
			$error = __( 'Please enter your current password.', 'xstore-amp' );
			wc_add_notice( $error, 'error' );
			$data['errors'][] = array(
				'error_detail' => $error
			);
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			$error = __( 'Please re-enter your password.', 'xstore-amp' );
			wc_add_notice( $error, 'error' );
			$data['errors'][] = array(
				'error_detail' => $error
			);
			$save_pass = false;
		} elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			$error = __( 'New passwords do not match.', 'xstore-amp' );
			wc_add_notice( $error, 'error' );
			$data['errors'][] = array(
				'error_detail' => $error
			);
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
			$error = __( 'Your current password is incorrect.', 'xstore-amp' );
			wc_add_notice( $error, 'error' );
			$data['errors'][] = array(
				'error_detail' => $error
			);
			$save_pass = false;
		}
		
		if ( $pass1 && $save_pass ) {
			$user->user_pass = $pass1;
		}
		
		// Allow plugins to return their own errors.
		$errors = new WP_Error();
		do_action_ref_array( 'woocommerce_save_account_details_errors', array( &$errors, &$user ) );
		
		if ( $errors->get_error_messages() ) {
			foreach ( $errors->get_error_messages() as $error ) {
				wc_add_notice( $error, 'error' );
				$data['errors'][] = array(
					'error_detail' => $error
				);
			}
		}
		
		if ( 0 < wc_notice_count( 'error' ) ) {
			header("HTTP/1.1 500 FIELDS REQUIRED");
			wc_clear_notices();
			
			wp_send_json( $data );
			die;
		}
		
		wp_update_user( $user );
			
		// Update customer object to keep data in sync.
		$customer = new WC_Customer( $user->ID );
		
		if ( $customer ) {
			// Keep billing data in sync if data changed.
			if ( is_email( $user->user_email ) && $current_email !== $user->user_email ) {
				$customer->set_billing_email( $user->user_email );
			}
			
			if ( $current_first_name !== $user->first_name ) {
				$customer->set_billing_first_name( $user->first_name );
			}
			
			if ( $current_last_name !== $user->last_name ) {
				$customer->set_billing_last_name( $user->last_name );
			}
			
			$customer->save();
		}
		
		do_action( 'woocommerce_save_account_details', $user->ID );
		
		$data['status'] = 'success';
		
		$redirect_url = wc_get_endpoint_url( 'edit-account', '', wc_get_page_permalink( 'myaccount' ));
		$redirect_url =  str_replace('http:', 'https:', $redirect_url);
		header( 'HTTP/1.1 200 OK' );
		header("Content-type: application/json");
		header("Access-Control-Allow-Credentials: true");
		header("Access-Control-Allow-Origin:".$_SERVER['HTTP_ORIGIN']);
		header("AMP-Access-Control-Allow-Source-Origin: ".$siteUrl['scheme'].'://'.$siteUrl['host']);
		if ( is_ssl() ) {
			header( "AMP-Redirect-To: " . $redirect_url );
			header( "Access-Control-Expose-Headers: AMP-Redirect-To, AMP-Access-Control-Allow-Source-Origin" );
		}
		else {
			$data['success_detail'] = esc_html__( 'Account details changed successfully.', 'xstore-amp' );
		}
		
		wp_send_json( $data );
		die;
		
	}
	
	/**
	 * Login user function.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function process_login() {
		
		header("access-control-allow-credentials:true");
		header("access-control-allow-headers:Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");
		header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
		$siteUrl = parse_url(get_site_url());
		header("AMP-Access-Control-Allow-Source-Origin:".esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']));
		header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
		header("Content-Type:application/json");
		
		$this->amp_add_query();
		
		// The global form-login.php template used `_wpnonce` in template versions < 3.3.0.
		$nonce_value = wc_get_var( $_REQUEST['woocommerce-login-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.
		
		if ( isset( $_POST['login'], $_POST['username'], $_POST['password'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-login' ) ) {
			$data = array(
				'status'      => 'error',
				'success_detail' => esc_html__( 'Success', 'xstore-amp' ),
				'errors' => array()
			);
			try {
				$creds = array(
					'user_login'    => trim( wp_unslash( $_POST['username'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'user_password' => $_POST['password'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					'remember'      => isset( $_POST['rememberme'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);
				
				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'woocommerce_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );
				
				if ( $validation_error->get_error_code() ) {
					header('HTTP/1.1 500 FORBIDDEN');
					$data['errors'][] = array(
						'error_detail' => '<strong>' . __( 'Error:', 'xstore-amp' ) . '</strong> ' . $validation_error->get_error_message()
					);
					echo wp_json_encode($data);
					exit;
				}
				
				if ( empty( $creds['user_login'] ) ) {
					header('HTTP/1.1 500 FORBIDDEN');
					$data['errors'][] = array(
						'error_detail' => '<strong>' . __( 'Error:', 'xstore-amp' ) . '</strong> ' . __( 'Username is required.', 'xstore-amp' )
					);
					echo wp_json_encode($data);
					exit;
				}
				
				// On multisite, ensure user exists on current site, if not add them before allowing login.
				if ( is_multisite() ) {
					$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );
					
					if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
						add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
					}
				}
				
				// Perform the login.
				$user = wp_signon( apply_filters( 'woocommerce_login_credentials', $creds ), is_ssl() );
				
				if ( is_wp_error( $user ) ) {
					header('HTTP/1.1 500 FORBIDDEN');
					$data['errors'][] = array(
						'error_detail' => apply_filters( 'login_errors', $user->get_error_message() )
					);
					echo wp_json_encode($data);
					exit;
				} else {
					
					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = wp_unslash( $_POST['redirect'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} elseif ( wc_get_raw_referer() ) {
						$redirect = $siteUrl['scheme'].'://'.$siteUrl['host'] . wc_get_raw_referer();
					} else {
						$redirect = get_permalink(wc_get_page_id( 'myaccount' ));
					}
					$redirect_url = apply_filters( 'woocommerce_login_redirect', remove_query_arg( 'wc_error', $redirect ), $user );
//					$redirect_url =  str_replace('http:', 'https:', $redirect_url);
					header( 'HTTP/1.1 200 OK' );
					header("Content-type: application/json");
					header("Access-Control-Allow-Credentials: true");
					header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
					header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
					header("AMP-Access-Control-Allow-Source-Origin: ".$siteUrl['scheme'].'://'.$siteUrl['host']);
					if ( is_ssl() ) {
						header( "AMP-Redirect-To: " . esc_url($redirect_url) );
						header( "Access-Control-Expose-Headers:AMP-Redirect-To" );
					}
					else {
						$data['success_detail'] = esc_html__('Click on next link to go in your account', 'xstore-amp');
						$data['success_detail_button'] = '<a href="'.$redirect_url.'" class="button small">'.esc_html__('My Account', 'xstore-amp') . '</a>';
					}
					echo wp_json_encode($data);
					exit;
				}
			} catch ( Exception $e ) {
				header('HTTP/1.1 500 FORBIDDEN');
				$data['errors'][] = array(
					'error_detail' => apply_filters( 'login_errors', $e->getMessage() )
				);
				do_action( 'woocommerce_login_failed' );
				echo wp_json_encode($data);
				die;
			}
		}
	}
	
	/**
	 * Registration process function.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function process_registration() {
		
		header("access-control-allow-credentials:true");
		header("access-control-allow-headers:Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");
		header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
		$siteUrl = parse_url(get_site_url());
		header("AMP-Access-Control-Allow-Source-Origin:".esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']));
		header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
		header("Content-Type:application/json");
		
		$this->amp_add_query();
		
		$nonce_value = wc_get_var( $_REQUEST['woocommerce-register-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.
		
		if ( isset( $_POST['register'], $_POST['email'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-register' ) ) {
			
			$data = array(
				'status'      => 'error',
				'success_detail' => esc_html__( 'Success', 'xstore-amp' ),
				'errors' => array()
			);
			
			$username = 'no' === get_option( 'woocommerce_registration_generate_username' ) && isset( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$password = 'no' === get_option( 'woocommerce_registration_generate_password' ) && isset( $_POST['password'] ) ? $_POST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$email    = wp_unslash( $_POST['email'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			
			try {
				$validation_error  = new WP_Error();
				$validation_error  = apply_filters( 'woocommerce_process_registration_errors', $validation_error, $username, $password, $email );
				$validation_errors = $validation_error->get_error_messages();
				
				if ( 1 === count( $validation_errors ) ) {
					// throw new Exception( $validation_error->get_error_message() );
					header('HTTP/1.1 500 FORBIDDEN');
					$data['errors'][] = array(
						'error_detail' => $validation_error->get_error_message()
					);
					echo wp_json_encode($data);
					exit;
				} elseif ( $validation_errors ) {
					foreach ( $validation_errors as $message ) {
						// wc_add_notice( '<strong>' . __( 'Error:', 'xstore-amp' ) . '</strong> ' . $message, 'error' );
						$data['errors'][] = array(
							'error_detail' => '<strong>' . __( 'Error:', 'xstore-amp' ) . '</strong> ' . $message
						);
					}
					// throw new Exception();
					header('HTTP/1.1 500 FORBIDDEN');
					echo wp_json_encode($data);
					exit;
				}
				
				$new_customer = wc_create_new_customer( sanitize_email( $email ), wc_clean( $username ), $password );
				
				if ( is_wp_error( $new_customer ) ) {
					// throw new Exception( $new_customer->get_error_message() );
					
					$data['errors'][] = array(
						'error_detail' => $new_customer->get_error_message()
					);
					header('HTTP/1.1 500 FORBIDDEN');
					echo wp_json_encode($data);
					exit;
				}
				
				if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) ) {
					$data['success_detail'] = __( 'Your account was created successfully and a password has been sent to your email address.', 'xstore-amp' );
				} else {
					$data['success_detail'] = __( 'Your account was created successfully. Your login details have been sent to your email address.', 'xstore-amp' );
				}
				
				// Only redirect after a forced login - otherwise output a success notice.
				if ( apply_filters( 'woocommerce_registration_auth_new_customer', true, $new_customer ) ) {
					wc_set_customer_auth_cookie( $new_customer );
					
					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = wp_sanitize_redirect( wp_unslash( $_POST['redirect'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} elseif ( wc_get_raw_referer() ) {
						$redirect = $siteUrl['scheme'].'://'.$siteUrl['host'] . wc_get_raw_referer();
					} else {
						$redirect = wc_get_page_permalink( 'myaccount' );
					}
					
					$redirect_url = wp_validate_redirect( apply_filters( 'woocommerce_registration_redirect', $redirect ), wc_get_page_permalink( 'myaccount' ) ); //phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
					
					header( 'HTTP/1.1 200 OK' );
					header("Content-type: application/json");
					header("Access-Control-Allow-Credentials: true");
					header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
					header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
					header("AMP-Access-Control-Allow-Source-Origin: ".$siteUrl['scheme'].'://'.$siteUrl['host']);
					if ( is_ssl() ) {
						header( "AMP-Redirect-To: " . $redirect_url );
						header( "Access-Control-Expose-Headers:AMP-Redirect-To" );
					}
					else {
						$data['success_detail'] = esc_html__('Click on next link to go in your account', 'xstore-amp');
						$data['success_detail_button'] = '<a href="'.$redirect_url.'" class="button small">'.esc_html__('My Account', 'xstore-amp') . '</a>';
					}
					echo wp_json_encode($data);
					exit;
				}
			} catch ( Exception $e ) {
				if ( $e->getMessage() ) {
					// wc_add_notice( '<strong>' . __( 'Error:', 'xstore-amp' ) . '</strong> ' . $e->getMessage(), 'error' );
					$data['errors'][] = array(
						'error_detail' => '<strong>' . __( 'Error:', 'xstore-amp' ) . '</strong> ' . $e->getMessage()
					);
				}
				header('HTTP/1.1 500 FORBIDDEN');
				echo wp_json_encode($data);
				die;
			}
		}
	}
	
	/**
	 * Lost password process function.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function process_lost_password() {
		header("access-control-allow-credentials:true");
		header("access-control-allow-headers:Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");
		header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
		$siteUrl = parse_url(
			get_site_url()
		);
		header("AMP-Access-Control-Allow-Source-Origin:".esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']));
		header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
		header("Content-Type:application/json");
		
		$data = array(
			'status'      => 'error',
			'success_detail' => esc_html__( 'Success', 'xstore-amp' ),
			'errors' => array()
		);
		
		if ( isset( $_POST['wc_reset_password'], $_POST['user_login'] ) ) {
			$nonce_value = wc_get_var( $_REQUEST['woocommerce-lost-password-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.
			
			if ( ! wp_verify_nonce( $nonce_value, 'lost_password' ) ) {
				return;
			}
			
			$this->amp_add_query();
			
			$redirect_url = add_query_arg( 'reset-link-sent', 'true', wc_get_page_permalink( 'myaccount' ) );
			
			$result = $this->retrieve_password();
			
			// If successful, redirect to my account with query arg set.
			if ( $result['status'] == 'success' ) {
				
				header( 'HTTP/1.1 200 OK' );
				
				if ( is_ssl() ) {
					header( "AMP-Redirect-To: " . $redirect_url );
					header( "Access-Control-Expose-Headers:AMP-Redirect-To" );
				}
				else {
					$data['success_detail'] = esc_html__('Click on next link to go in your account', 'xstore-amp');
					$data['success_detail_button'] = '<a href="'.$redirect_url.'" class="button small">'.esc_html__('My Account', 'xstore-amp') . '</a>';
				}
				echo wp_json_encode($data);
				exit;
			}
			else {
				header('HTTP/1.1 500 FORBIDDEN');
				echo wp_json_encode($result);
				exit;
			}
		}
	}
	
	/**
	 * Retrieve password process function.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function retrieve_password() {
		$data = array(
			'status'      => 'error',
			'success_detail' => esc_html__( 'Success', 'xstore-amp' ),
			'errors' => array()
		);
		$login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : ''; // WPCS: input var ok, CSRF ok.
		
		if ( empty( $login ) ) {
			
//			wc_add_notice( __( 'Enter a username or email address.', 'xstore-amp' ), 'error' );
			
			$data['errors'][] = array(
				'error_detail' => __( 'Enter a username or email address.', 'xstore-amp' )
			);
			
			return $data;
			
		} else {
			// Check on username first, as customers can use emails as usernames.
			$user_data = get_user_by( 'login', $login );
		}
		
		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $login ) && apply_filters( 'woocommerce_get_username_from_email', true ) ) {
			$user_data = get_user_by( 'email', $login );
		}
		
		$errors = new WP_Error();
		
		do_action( 'lostpassword_post', $errors, $user_data );
		
		if ( $errors->get_error_code() ) {
//			wc_add_notice( $errors->get_error_message(), 'error' );
			$data['errors'][] = array(
				'error_detail' => $errors->get_error_message()
			);
			
			return $data;
		}
		
		if ( ! $user_data ) {
//			wc_add_notice( __( 'Invalid username or email.', 'xstore-amp' ), 'error' );
			$data['errors'][] = array(
				'error_detail' => __( 'Invalid username or email.', 'xstore-amp' )
			);
			
			return $data;
		}
		
		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
//			wc_add_notice( __( 'Invalid username or email.', 'xstore-amp' ), 'error' );
			$data['errors'][] = array(
				'error_detail' => __( 'Invalid username or email.', 'xstore-amp' )
			);
			
			return $data;
		}
		
		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		
		do_action( 'retrieve_password', $user_login );
		
		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );
		
		if ( ! $allow ) {
			
//			wc_add_notice( __( 'Password reset is not allowed for this user', 'xstore-amp' ), 'error' );
			
			$data['errors'][] = array(
				'error_detail' => __( 'Password reset is not allowed for this user.', 'xstore-amp' )
			);
			
			return $data;
			
		} elseif ( is_wp_error( $allow ) ) {
			
//			wc_add_notice( $allow->get_error_message(), 'error' );
			
			$data['errors'][] = array(
				'error_detail' => $allow->get_error_message()
			);
			
			return $data;
		}
		
		// Get password reset key (function introduced in WordPress 4.4).
		$key = get_password_reset_key( $user_data );
		
		// Send email notification.
		WC()->mailer(); // Load email classes.
		do_action( 'woocommerce_reset_password_notification', $user_login, $key );
		
		$data['status'] = 'success';
		
		return $data;
	}
	
	/**
	 * Ajax search action.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function search() {
		
		global $xstore_amp_settings, $xstore_amp_vars;
		
		header("access-control-allow-credentials:true");
		header("access-control-allow-headers:Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");
		header("Access-Control-Allow-Origin:".esc_attr($_SERVER['HTTP_ORIGIN']));
		$siteUrl = parse_url(
			get_site_url()
		);
		header("AMP-Access-Control-Allow-Source-Origin:".esc_attr($siteUrl['scheme']) . '://' . esc_attr($siteUrl['host']));
		header("access-control-expose-headers:AMP-Access-Control-Allow-Source-Origin");
		header("Content-Type:application/json");
		
		$response = array(
			'suggestions' => array(),
			'tabs' => array()
		);
		
		if ( !empty($_POST['s']) ) {
			$this->amp_add_query();
			require_once XStore_AMP_INCLUDES_PATH . "handles/ajax-search.php";
			$search                   = new XStore_AMP_ajax_search();
			$search->request['query'] = $_POST['s'];
			$search->request['limit'] = 30;
			$search->settings['search'] = array('products', 'posts');
			if ( isset($xstore_amp_settings['general']['search_results']) && !empty($xstore_amp_settings['general']['search_results']) ) {
				$search_options = explode(',', $xstore_amp_settings['general']['search_results']);
				foreach ( $search_options as $element_key => $element_name ) {
					if ( !isset($xstore_amp_settings['general'][$element_name.'_visibility']) || !$xstore_amp_settings['general'][$element_name.'_visibility'] ) {
						unset($search_options[$element_key]);
					}
				}
				$search->settings['search'] = $search_options;
			}
			$search->conditions['is_woocommerce'] = $xstore_amp_vars['is_woocommerce'];
			$response                 = $search->search_results();
			if ( count($response['suggestions']) < 1 ) {
				$response['suggestions'][] = array(
					'no_results' => true,
					'no_results_text' => esc_html__('No results were found', 'xstore-amp'),
					'no_results_description' => sprintf(esc_html__('No items matched your search %s.', 'xstore-amp'), '<strong>'.$_POST['s'].'</strong>')
				);
			}
		}
		
		header( "HTTP/1.1 200 OK" );
		
		wp_send_json( $response );
		die;
	}
	/**
	 * Dark/light switcher ajax action.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function dark_light_switcher() {
		if ( !empty($_POST['darkLightActiveMode'])) {
			header( 'HTTP/1.1 200 OK' );
			$_SESSION['xstore-amp-siteMode'] = $_POST['darkLightActiveMode'] == 'light-mode' ? 'dark-mode' : 'light-mode';
			echo wp_json_encode(array());
			exit;
		}
	}
}
$XStore_AMP_ajax = new XStore_AMP_ajax();