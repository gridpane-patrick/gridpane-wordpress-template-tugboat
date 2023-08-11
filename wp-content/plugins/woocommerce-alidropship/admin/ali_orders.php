<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bulk orders(Ali orders page)
 * Automatically fulfill AliExpress order when Woo order payment is successful
 *
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_Orders
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_Orders {
	private static $settings;
	private static $order_status = 'to_order';
	private static $check_order = false;
	private static $store_num = array();

	public function __construct() {
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
		add_action( 'admin_head', array( $this, 'menu_order_count' ) );
		add_action( 'wp_ajax_vi_wad_place_ali_orders', array( $this, 'place_ali_orders' ) );
		add_action( 'wp_ajax_vi_wad_save_selected_shipping_company', array( $this, 'save_selected_shipping_company' ) );
		add_action( 'woocommerce_payment_complete', array( $this, 'woocommerce_payment_complete' ) );
		foreach ( self::$settings->get_params( 'auto_order_if_status' ) as $status ) {
			$status = substr( $status, 3 );
			add_action( "woocommerce_payment_complete_order_status_{$status}", array(
				$this,
				'woocommerce_payment_complete'
			) );
		}
	}

	/**
	 * Auto fulfill when a new order is placed and the payment is complete
	 *
	 * @param $order_id
	 *
	 * @throws Exception
	 */
	public function woocommerce_payment_complete( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {

			return;
		}
		if ( self::$check_order ) {

			return;
		}
		if ( ! in_array( $order->get_payment_method(), self::$settings->get_params( 'auto_order_if_payment' ) ) ) {

			return;
		}
		if ( ! in_array( 'wc-' . $order->get_status(), self::$settings->get_params( 'auto_order_if_status' ) ) ) {

			return;
		}
		self::$check_order = true;
		$access_token      = self::$settings->get_params( 'access_token' );
		if ( $access_token ) {
			$item_ids                = array();
			$product_items           = array();
			$order_item_ids_by_store = array();
			$customer_info           = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_API::get_customer_info( $order );
			$order_items             = $order->get_items();
			foreach ( $order_items as $order_item_id => $order_item ) {
				$get_item = self::get_item_data( $order_item_id, '', $customer_info, $order );
				if ( $get_item['status'] === 'success' ) {
					$item_exists = false;
					foreach ( $product_items as &$product_item ) {
						if ( $product_item['product_id'] == $get_item['product_items']['product_id'] && $product_item['sku_attr'] == $get_item['product_items']['sku_attr'] ) {
							$product_item['product_count'] += $get_item['product_items']['product_count'];
							$item_exists                   = true;
							break;
						}
					}
					if ( ! $item_exists ) {
						$product_items[] = $get_item['product_items'];
					}
					$item_ids[] = $order_item_id;
					$store_num  = self::get_store_num( $order->get_item( $order_item_id ) );
					if ( $store_num ) {
						if ( ! isset( $order_item_ids_by_store[ $store_num ] ) ) {
							$order_item_ids_by_store[ $store_num ] = array();
						}
						$order_item_ids_by_store[ $store_num ][] = $order_item_id;
					}
				} elseif ( $get_item['status'] === 'error' ) {
					self::log( sprintf( esc_html__( 'Order #%1s, item #%2s: %3s', '' ), $order_id, $order_item_id, $get_item['message'] ) );
				}
			}

			if ( count( $product_items ) ) {
				$logistics_address = self::get_logistics_address( $customer_info );
				$args              = array(
					'download_key' => self::$settings->get_params( 'key' ),
					'access_token' => $access_token,
					'app_key'      => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
					'site_url'     => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_domain_name(),
					'data'         => json_encode( array(
						array(
							'address'  => $logistics_address,
							'items'    => $product_items,
							'order_id' => $order_id,
						)
					) ),
				);
				$order_data        = array(
					'app_key'                                   => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
					'session'                                   => $access_token,
					'method'                                    => 'aliexpress.trade.buy.placeorder',
					'param_place_order_request4_open_api_d_t_o' => json_encode( array(
							'logistics_address' => $logistics_address,
							'product_items'     => $product_items,
						)
					),
					'sign'                                      => '',
					'timestamp'                                 => '',
				);
				add_filter( 'http_request_timeout', array(
					'VI_WOOCOMMERCE_ALIDROPSHIP_DATA',
					'bump_request_timeout'
				), PHP_INT_MAX );
				$get_sign = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_get_sign( $args, 'place_order' );
				if ( $get_sign['status'] === 'success' ) {
					$sign_data = vi_wad_json_decode( $get_sign['data']['data'] );
					if ( ! empty( $sign_data[ $order_id ] ) ) {
						$order_data['sign']      = $sign_data[ $order_id ];
						$order_data['timestamp'] = date( 'Y-m-d H:i:s', $get_sign['data']['timestamp'] );
						add_filter( 'http_request_timeout', array(
							'VI_WOOCOMMERCE_ALIDROPSHIP_DATA',
							'bump_request_timeout'
						), PHP_INT_MAX );
						$request = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_request( $order_data );
						if ( $request['status'] === 'success' ) {
							if ( isset( $request['data']['is_success'] ) && $request['data']['is_success'] == 1 && isset( $request['data']['order_list']['number'] ) ) {
								$ali_order_ids           = $request['data']['order_list']['number'];
								$order_item_ids_by_store = array_values( $order_item_ids_by_store );
								if ( count( $ali_order_ids ) === count( $order_item_ids_by_store ) ) {
									foreach ( $ali_order_ids as $key => $ali_order_id ) {
										foreach ( $order_item_ids_by_store[ $key ] as $order_item_id ) {
											wc_add_order_item_meta( $order_item_id, '_vi_wad_match_aliexpress_order_id', $ali_order_id );
											wc_update_order_item_meta( $order_item_id, '_vi_wad_aliexpress_order_id', $ali_order_id );
											wc_update_order_item_meta( $order_item_id, '_vi_wad_aliexpress_order_item_status', 'processing' );
										}
									}
									$order_status_after_ali_order = self::$settings->get_params( 'order_status_after_ali_order' );
									if ( $order_status_after_ali_order ) {
										$order->update_status( $order_status_after_ali_order );
									}
									self::log( sprintf( esc_html__( 'Order #%s is fulfilled successfully.', 'woocommerce-alidropship' ), $order_id ), WC_Log_Levels::INFO );
								} else {
									self::log( sprintf( esc_html__( 'Order #%s is placed but items number does not match.', 'woocommerce-alidropship' ), $order_id ) );
								}
							} else {
								$error = isset( $request['data']['error_code'] ) ? $request['data']['error_code'] : '';
								if ( $error ) {
									$error_m = self::place_orders_error_code( $error );
									if ( $error_m ) {
										$error = $error_m;
									}
								}
								self::log( sprintf( esc_html__( 'Order #%s: %s.', 'woocommerce-alidropship' ), $order_id, $error ) );
							}
						} else {
							self::log( sprintf( esc_html__( 'Order #%s: %s.', 'woocommerce-alidropship' ), $order_id, $request['data'] ) );
						}
					} else {
						self::log( sprintf( esc_html__( 'Order #%s: Cannot generate signature.', 'woocommerce-alidropship' ), $order_id ) );
					}
				} else {
					self::log( sprintf( esc_html__( 'Order #%s: %s.', 'woocommerce-alidropship' ), $order_id, $get_sign['data'] ) );
				}
			} else {
				self::log( sprintf( esc_html__( 'Order #%s: No items.', 'woocommerce-alidropship' ), $order_id ) );
			}
		} else {
			self::log( sprintf( esc_html__( 'Order #%s: Missing access token.', 'woocommerce-alidropship' ), $order_id ) );
		}
	}

	/**
	 * Save shipping method after selecting
	 */
	public function save_selected_shipping_company() {
		check_ajax_referer( 'woocommerce_alidropship_admin_ajax', '_vi_wad_ajax_nonce' );
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-ali-orders' ) ) ) {
			wp_die();
		}
		$order_id      = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
		$item_id       = isset( $_POST['item_id'] ) ? sanitize_text_field( $_POST['item_id'] ) : '';
		$company       = isset( $_POST['company'] ) ? sanitize_text_field( $_POST['company'] ) : '';
		$company_name  = isset( $_POST['company_name'] ) ? sanitize_text_field( $_POST['company_name'] ) : '';
		$delivery_time = isset( $_POST['delivery_time'] ) ? sanitize_text_field( $_POST['delivery_time'] ) : '';
		$shipping_cost = isset( $_POST['shipping_cost'] ) ? sanitize_text_field( $_POST['shipping_cost'] ) : '';
		if ( $order_id && $item_id && $company ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$item = $order->get_item( $item_id );
				if ( $item && ! $item->get_meta( '_vi_wad_aliexpress_order_id', true ) ) {
					$item->update_meta_data( '_vi_wot_order_item_saved_shipping', json_encode( array(
						'company'       => $company,
						'company_name'  => $company_name,
						'delivery_time' => $delivery_time,
						'shipping_cost' => $shipping_cost,
					) ) );
					$item->save();
					wp_send_json_success();
				}
			}
		}
		wp_send_json_error();
	}

	/**
	 * Possible errors when placing orders according to the official api documentation
	 *
	 * @param $code
	 *
	 * @return mixed|string
	 */
	protected static function place_orders_error_code( $code ) {
		$code   = strtoupper( $code );
		$errors = array(
			'B_DROPSHIPPER_DELIVERY_ADDRESS_VALIDATE_FAIL' => esc_html__( 'Invalid shipping address', 'woocommerce-alidropship' ),
			'BLACKLIST_BUYER_IN_LIST'                      => esc_html__( 'Buyer is in blacklist', 'woocommerce-alidropship' ),
			'USER_ACCOUNT_DISABLED'                        => esc_html__( 'Buyer account has been disabled', 'woocommerce-alidropship' ),
			'PRICE_PAY_CURRENCY_ERROR'                     => esc_html__( 'Products should declare as same currency', 'woocommerce-alidropship' ),
			'DELIVERY_METHOD_NOT_EXIST'                    => esc_html__( 'Invalid delivery method', 'woocommerce-alidropship' ),
			'INVENTORY_HOLD_ERROR'                         => esc_html__( 'Insufficient inventory or system error', 'woocommerce-alidropship' ),
			'REPEATED_ORDER_ERROR'                         => esc_html__( 'Repeated placed order', 'woocommerce-alidropship' ),
			'ERROR_WHEN_BUILD_FOR_PLACE_ORDER'             => esc_html__( 'Invalid order items or system error', 'woocommerce-alidropship' ),
		);

		return isset( $errors[ $code ] ) ? $errors[ $code ] : '';
	}

	/**
	 * @param $item WC_Order_Item
	 *
	 * @return mixed
	 */
	private static function get_store_num( $item ) {
		$woo_id = $item->get_product_id();
		if ( ! isset( self::$store_num[ $woo_id ] ) ) {
			$ald_id    = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_woo_id( $woo_id, false, false, array(
					'publish',
					'override'
				)
			);
			$store_num = '';
			if ( $ald_id ) {
				$store_info = get_post_meta( $ald_id, '_vi_wad_store_info', true );
				if ( ! empty( $store_info['num'] ) ) {
					$store_num = $store_info['num'];
				}
			}
			self::$store_num[ $woo_id ] = $store_num;
		}

		return self::$store_num[ $woo_id ];
	}

	/**
	 * Place order
	 * If there's an error with an order item, the whole order will not be placed
	 *
	 * @throws Exception
	 */
	public function place_ali_orders() {
		check_ajax_referer( 'woocommerce_alidropship_admin_ajax', '_vi_wad_ajax_nonce' );
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-ali-orders' ) ) ) {
			wp_die();
		}
		vi_wad_set_time_limit();
		$step                         = isset( $_POST['step'] ) ? sanitize_text_field( $_POST['step'] ) : 'get_signature';
		$batch_request_enable         = isset( $_POST['batch_request_enable'] ) ? sanitize_text_field( $_POST['batch_request_enable'] ) : '';
		$response                     = array(
			'status'  => 'success',
			'details' => array(),
			'errors'  => array(),
			'message' => esc_html__( 'Cannot place orders', 'woocommerce-alidropship' ),
		);
		$access_token                 = self::$settings->get_params( 'access_token' );
		$order_status_after_ali_order = self::$settings->get_params( 'order_status_after_ali_order' );
		if ( ! in_array( $order_status_after_ali_order, array_keys( wc_get_order_statuses() ) ) ) {
			$order_status_after_ali_order = '';
		}
		if ( ! $access_token ) {
			$response['status']  = 'error';
			$response['message'] = esc_html__( 'Missing access token', 'woocommerce-alidropship' );
			wp_send_json( $response );
		}
		$details = array();
		if ( $step === 'get_signature' ) {
			$orders = isset( $_POST['orders'] ) ? stripslashes_deep( $_POST['orders'] ) : array();
			if ( count( $orders ) ) {
				$errors    = array();
				$args      = array(
					'download_key' => self::$settings->get_params( 'key' ),
					'access_token' => $access_token,
					'app_key'      => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
					'site_url'     => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_domain_name(),
					'data'         => array(),
				);
				$map_items = array();
				foreach ( $orders as $order_data ) {
					$order_id    = $order_data['order_id'];
					$order_items = $order_data['order_items'];
					$order       = wc_get_order( $order_id );
					$detail      = array(
						'order_id'      => $order_id,
						'order_item_id' => '',
						'message'       => '',
					);
					if ( ! $order ) {
						$detail['message'] = esc_html__( 'Order does not exist', 'woocommerce-alidropship' );
						$errors[]          = $detail;
						continue;
					}
					$items = $order->get_items();
					if ( ! count( $order_items ) || ! count( $items ) ) {
						$detail['message'] = esc_html__( 'Order item does not exist', 'woocommerce-alidropship' );
						$errors[]          = $detail;
						continue;
					} else {
						$item_ids                = array();
						$product_items           = array();
						$customer_info           = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_API::get_customer_info( $order );
						$order_item_ids_by_store = array();//different products/variations from the same seller will have the same AliExpress order id after fulfillment
						foreach ( $order_items as $order_item_data ) {
							$order_item_id           = $order_item_data['order_item_id'];
							$detail['order_item_id'] = $order_item_id;
							$detail['message']       = '';
							$get_item                = self::get_item_data( $order_item_id, isset( $order_item_data['shipping_company'] ) ? $order_item_data['shipping_company'] : '', $customer_info, $order );
							if ( $get_item['status'] === 'success' ) {
								$item_exists = false;
								foreach ( $product_items as &$product_item ) {
									if ( $product_item['product_id'] == $get_item['product_items']['product_id'] && $product_item['sku_attr'] == $get_item['product_items']['sku_attr'] ) {
										$product_item['product_count'] += $get_item['product_items']['product_count'];
										$item_exists                   = true;
										break;
									}
								}
								if ( ! $item_exists ) {
									$product_items[] = $get_item['product_items'];
								}
								$item_ids[] = $order_item_id;
								$store_num  = self::get_store_num( $order->get_item( $order_item_id ) );
								if ( $store_num ) {
									$store_num = strval( $store_num );
									if ( ! isset( $order_item_ids_by_store[ $store_num ] ) ) {
										$order_item_ids_by_store[ $store_num ] = array();
									}
									$order_item_ids_by_store[ $store_num ][] = $order_item_id;
								}
							} else {
								$detail['message'] = $get_item['message'];
								$errors[]          = $detail;
								continue;
							}
						}
						if ( count( $product_items ) ) {
							$logistics_address = self::get_logistics_address( $customer_info );
							if ( $batch_request_enable ) {
								$args['data'][] = array(
									'param_place_order_request4_open_api_d_t_o' => json_encode( array(
										'logistics_address' => $logistics_address,
										'product_items'     => $product_items,
									) )
								);
								$map_items[]    = array(
									'order_id'                => $order_id,
									'order_item_ids'          => $item_ids,
									'order_item_ids_by_store' => array_values( $order_item_ids_by_store ),
								);
							} else {
								$args['data'][] = array(
									'address'  => $logistics_address,
									'items'    => $product_items,
									'order_id' => $order_id,
								);

								$details[] = array(
									'order_id'                => $order_id,
									'order_item_ids'          => $item_ids,
									'order_item_ids_by_store' => array_values( $order_item_ids_by_store ),
									'data'                    => array(
										'app_key'                                   => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
										'session'                                   => $access_token,
										'method'                                    => 'aliexpress.trade.buy.placeorder',
										'param_place_order_request4_open_api_d_t_o' => json_encode( array(
												'logistics_address' => $logistics_address,
												'product_items'     => $product_items,
											)
										),
										'sign'                                      => '',
										'timestamp'                                 => '',
									),
								);
							}
						}
					}
				}
				if ( count( $args['data'] ) ) {
					$args['data'] = json_encode( $args['data'] );
					add_filter( 'http_request_timeout', array(
						'VI_WOOCOMMERCE_ALIDROPSHIP_DATA',
						'bump_request_timeout'
					), PHP_INT_MAX );
					if ( $batch_request_enable ) {
						/*Place maximum 20 orders in a single request*/
						$get_sign = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_get_sign( $args, 'place_order_batch' );
						if ( $get_sign['status'] === 'success' ) {
							$public_params = array(
								'app_key'     => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
								'format'      => 'json',
								'method'      => 'aliexpress.trade.buy.placeorder',
								'partner_id'  => 'apidoc',
								'session'     => $access_token,
								'sign_method' => 'md5',
								'v'           => '2.0',
								'sign'        => $get_sign['data']['data'],
								'timestamp'   => date( 'Y-m-d H:i:s', $get_sign['data']['timestamp'] ),
							);

							$url       = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_get_url( true );
							$url       = add_query_arg( array_map( 'urlencode', $public_params ), $url );
							$separator = urlencode( '{villatheme}' );
							$request   = wp_remote_post( $url, array(
								'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',
								'headers'    => array(
									'Content-Type'      => 'text/plain;charset=UTF-8',
									'top-api-separator' => $separator,
								),
								'body'       => $get_sign['data']['payload'],
								'timeout'    => 60,
							) );
							if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
								if ( strpos( $request['body'], $separator ) === false ) {
									$responses = array( $request['body'] );
								} else {
									$responses = explode( $separator, $request['body'] );
								}
								if ( count( $responses ) === count( $map_items ) ) {
									foreach ( $responses as $response_k => $response_v ) {
										$data    = vi_wad_json_decode( $response_v );
										$res_key = str_replace( '.', '_', $public_params['method'] ) . '_response';
										if ( isset( $data[ $res_key ] ) ) {
											$result = $data[ $res_key ]['result'];
											if ( isset( $result['is_success'] ) && $result['is_success'] == 1 && isset( $result['order_list']['number'] ) ) {
												$ali_order_ids           = $result['order_list']['number'];
												$order_item_ids_by_store = $map_items[ $response_k ]['order_item_ids_by_store'];
												if ( count( $ali_order_ids ) === count( $order_item_ids_by_store ) ) {
													foreach ( $ali_order_ids as $key => $ali_order_id ) {
														foreach ( $order_item_ids_by_store[ $key ] as $order_item_id ) {
															wc_add_order_item_meta( $order_item_id, '_vi_wad_match_aliexpress_order_id', $ali_order_id );
															wc_update_order_item_meta( $order_item_id, '_vi_wad_aliexpress_order_id', $ali_order_id );
															wc_update_order_item_meta( $order_item_id, '_vi_wad_aliexpress_order_item_status', 'processing' );
															$details[] = array(
																'order_id'             => $map_items[ $response_k ]['order_id'],
																'order_item_id'        => $order_item_id,
																'status'               => 'success',
																'ali_order_id'         => $ali_order_id,
																'ali_order_detail_url' => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_order_detail_url( $ali_order_id ),
																'ali_tracking_url'     => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_tracking_url( $ali_order_id ),
																'message'              => sprintf( esc_html__( 'Order is placed successfully. Please go to %s to make payment', 'woocommerce-alidropship' ), VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_order_detail_url( $ali_order_id ) ),
															);
														}
													}
													if ( $order_status_after_ali_order ) {
														$order = wc_get_order( $map_items[ $response_k ]['order_id'] );
														if ( $order ) {
															$order->update_status( $order_status_after_ali_order );
														}
													}
												} else {
													self::set_item_error( $map_items[ $response_k ]['order_id'], $map_items[ $response_k ]['order_item_ids'], esc_html__( 'Order is placed but items number does not match', 'woocommerce-alidropship' ), $details );
												}
											} else {
												$error = isset( $result['error_code'] ) ? $result['error_code'] : '';
												if ( $error ) {
													$error_m = self::place_orders_error_code( $error );
													if ( $error_m ) {
														$error = $error_m;
													}
												}
												self::set_item_error( $map_items[ $response_k ]['order_id'], $map_items[ $response_k ]['order_item_ids'], $error, $details );
											}
										} elseif ( isset( $data['error_response'] ) && isset( $data['error_response']['msg'] ) ) {
											$details[] = array(
												'order_id'      => $map_items[ $response_k ]['order_id'],
												'order_item_id' => '',
												'status'        => 'error',
												'message'       => $data['error_response']['msg'],
											);
										}
									}
								} else {
									$response['status']  = 'error';
									$response['message'] = esc_html__( 'Responses not matching', 'woocommerce-alidropship' );
								}
							} else {
								$response['status']  = 'error';
								$response['message'] = $request->get_error_message();
							}
						} else {
							$response['status']  = 'error';
							$response['message'] = $get_sign['data'];
						}
					} else {
						$get_sign = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_get_sign( $args, 'place_order' );
						if ( $get_sign['status'] === 'success' ) {
							$sign_data = vi_wad_json_decode( $get_sign['data']['data'] );
							foreach ( $details as $key => $value ) {
								if ( ! empty( $sign_data[ $value['order_id'] ] ) ) {
									$details[ $key ]['data']['sign']      = $sign_data[ $value['order_id'] ];
									$details[ $key ]['data']['timestamp'] = date( 'Y-m-d H:i:s', $get_sign['data']['timestamp'] );
								} else {
									$errors[] = array(
										'order_id'      => $value['order_id'],
										'order_item_id' => '',
										'message'       => esc_html__( 'Cannot generate signature', 'woocommerce-alidropship' ),
									);
									unset( $details[ $key ] );
								}
							}
							if ( ! count( $details ) ) {
								$response['status']  = 'error';
								$response['message'] = esc_html__( 'Cannot generate signature', 'woocommerce-alidropship' );
							}
						} else {
							$response['status']  = 'error';
							$response['message'] = $get_sign['data'];
						}
					}
				} else {
					$response['status'] = 'error';
				}
				$response['details'] = $details;
				$response['errors']  = $errors;
			} else {
				$response['status']  = 'error';
				$response['message'] = esc_html__( 'Empty data', 'woocommerce-alidropship' );
			}
		} else {
			$order_id                = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
			$item_ids                = isset( $_POST['order_item_ids'] ) ? stripslashes_deep( $_POST['order_item_ids'] ) : array();
			$order_item_ids_by_store = isset( $_POST['order_item_ids_by_store'] ) ? stripslashes_deep( $_POST['order_item_ids_by_store'] ) : array();
			$order_data              = isset( $_POST['order_data'] ) ? stripslashes_deep( $_POST['order_data'] ) : array();
			$order                   = wc_get_order( $order_id );
			if ( $order ) {
				add_filter( 'http_request_timeout', array(
					'VI_WOOCOMMERCE_ALIDROPSHIP_DATA',
					'bump_request_timeout'
				), PHP_INT_MAX );
				$request = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_request( $order_data );
				if ( $request['status'] === 'success' ) {
					if ( isset( $request['data']['is_success'] ) && $request['data']['is_success'] == 1 && isset( $request['data']['order_list']['number'] ) ) {
						$ali_order_ids = $request['data']['order_list']['number'];
						if ( count( $ali_order_ids ) === count( $order_item_ids_by_store ) ) {
							foreach ( $ali_order_ids as $key => $ali_order_id ) {
								foreach ( $order_item_ids_by_store[ $key ] as $order_item_id ) {
									wc_add_order_item_meta( $order_item_id, '_vi_wad_match_aliexpress_order_id', $ali_order_id );
									wc_update_order_item_meta( $order_item_id, '_vi_wad_aliexpress_order_id', $ali_order_id );
									wc_update_order_item_meta( $order_item_id, '_vi_wad_aliexpress_order_item_status', 'processing' );
									$details[] = array(
										'order_id'             => $order_id,
										'order_item_id'        => $order_item_id,
										'status'               => 'success',
										'ali_order_id'         => $ali_order_id,
										'ali_order_detail_url' => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_order_detail_url( $ali_order_id ),
										'ali_tracking_url'     => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_tracking_url( $ali_order_id ),
										'message'              => sprintf( esc_html__( 'Order is placed successfully. Please go to %s to make payment', 'woocommerce-alidropship' ), VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_order_detail_url( $ali_order_id ) ),
									);
								}
							}
							if ( $order_status_after_ali_order ) {
								$order->update_status( $order_status_after_ali_order );
							}
						} else {
							self::set_item_error( $order_id, $item_ids, esc_html__( 'Order is placed but items number does not match', 'woocommerce-alidropship' ), $details );
						}
					} else {
						$error = isset( $request['data']['error_code'] ) ? $request['data']['error_code'] : '';
						if ( $error ) {
							$error_m = self::place_orders_error_code( $error );
							if ( $error_m ) {
								$error = $error_m;
							}
						}
						self::set_item_error( $order_id, $item_ids, $error, $details );
					}
				} else {
					self::set_item_error( $order_id, $item_ids, $request['data'], $details );
				}
			} else {
				$details[] = array(
					'order_id'      => $order_id,
					'order_item_id' => '',
					'status'        => 'error',
					'message'       => esc_html__( 'Order does not exist', 'woocommerce-alidropship' ),
				);
			}
			$response['details'] = $details;
		}

		wp_send_json( $response );
	}

	/**
	 * @param $customer_info
	 *
	 * @return array|mixed|void
	 */
	private static function get_logistics_address( $customer_info ) {
		$logistics_address = array(
			'address'        => $customer_info['street'],
			'city'           => remove_accents( $customer_info['city'] ),
			'contact_person' => $customer_info['name'],
			'country'        => $customer_info['country'],
//								'cpf'            => '',
//								'rutNo'            => '',
			'full_name'      => $customer_info['name'],
//						    'locale'                => 'en_US',
			'mobile_no'      => $customer_info['phone'],
//						    'passport_no'           => '',
//						    'passport_no_date'      => '',
//						    'passport_organization' => '',
			'phone_country'  => $customer_info['phoneCountry'],
			'province'       => remove_accents( $customer_info['state'] ),
//						    'tax_number'            => '',
		);
		if ( ! empty( $customer_info['cpf'] ) ) {
			$logistics_address['cpf'] = $customer_info['cpf'];
		}
		if ( ! empty( $customer_info['rutNo'] ) ) {
			$logistics_address['rutNo'] = $customer_info['rutNo'];
		}
		if ( $customer_info['postcode'] ) {
			$logistics_address['zip'] = $customer_info['postcode'];
		}
		if ( $customer_info['address2'] ) {
			if ( $logistics_address['address'] ) {
				$logistics_address['address2'] = remove_accents( $customer_info['address2'] );
			} else {
				$logistics_address['address'] = remove_accents( $customer_info['address2'] );
			}
		}
		$logistics_address = apply_filters( 'vi_wad_bulk_orders_logistics_address', $logistics_address, $customer_info );

		return $logistics_address;
	}

	/**
	 * @param $order_item_id
	 * @param $shipping_company
	 * @param $customer_info
	 * @param $order WC_Order
	 *
	 * @return array
	 */
	public static function get_item_data( $order_item_id, $shipping_company, $customer_info, $order ) {
		$response = array(
			'status'        => 'error',
			'message'       => '',
			'product_items' => array(),
		);
		$item     = $order->get_item( $order_item_id );
		if ( ! $item ) {
			$response['message'] = esc_html__( 'Order item does not exist', 'woocommerce-alidropship' );

			return $response;
		}
		if ( $item->get_meta( '_vi_wad_aliexpress_order_id', true ) ) {
			$response['status']  = 'exist';
			$response['message'] = esc_html__( 'Ali order exists', 'woocommerce-alidropship' );

			return $response;
		}
		$woo_product = is_callable( array(
			$item,
			'get_product'
		) ) ? $item->get_product() : null;
		if ( ! $woo_product ) {
			$response['message'] = esc_html__( 'Product does not exist', 'woocommerce-alidropship' );

			return $response;
		}
		$woo_product_id   = $item->get_product_id();
		$woo_variation_id = $item->get_variation_id();
		$ali_product_id   = get_post_meta( $woo_product_id, '_vi_wad_aliexpress_product_id', true );
		if ( ! $ali_product_id ) {
			$response['status']  = 'skip';
			$response['message'] = esc_html__( 'AliExpress product does not exist', 'woocommerce-alidropship' );

			return $response;
		}
		if ( ! $shipping_company ) {
			/*If frontend shipping is enabled, select company that customers choose*/
			$saved_shipping = self::get_item_saved_shipping( $item );
			if ( ! empty( $saved_shipping['company'] ) ) {
				$shipping_company = $saved_shipping['company'];
			}
		}
		$quantity = $item->get_quantity() + $order->get_qty_refunded_for_item( $order_item_id );
		if ( ! $shipping_company ) {
			/*If no shipping company, choose the first available option if any*/
			$wpml_product_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wpml_get_original_object_id( $woo_product_id );
			if ( $woo_variation_id ) {
				$wpml_variation_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wpml_get_original_object_id( $woo_variation_id );
				if ( $wpml_variation_id ) {
					$ship_from = get_post_meta( $wpml_variation_id, '_vi_wad_aliexpress_variation_ship_from', true );
				} else {
					$ship_from = get_post_meta( $woo_variation_id, '_vi_wad_aliexpress_variation_ship_from', true );
				}
			} else {
				if ( $wpml_product_id ) {
					$ship_from = get_post_meta( $wpml_product_id, '_vi_wad_aliexpress_variation_ship_from', true );
				} else {
					$ship_from = get_post_meta( $woo_product_id, '_vi_wad_aliexpress_variation_ship_from', true );
				}
			}
			$shipping_country = $order->get_shipping_country();
			$state            = $city = '';
			if ( ! $shipping_country ) {
				$shipping_country = $order->get_billing_country();
				if ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::is_shipping_supported_by_province_city( $shipping_country ) ) {
					$state = $order->get_billing_state();
					$city  = $order->get_billing_city();
				}
			} else {
				if ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::is_shipping_supported_by_province_city( $shipping_country ) ) {
					$state = $order->get_shipping_state();
					$city  = $order->get_shipping_city();
				}
			}
			$freights = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_shipping_by_woo_id( $wpml_product_id ? $wpml_product_id : $woo_product_id, $shipping_country, $ship_from, $quantity, $state, $city );
			if ( count( $freights ) && ! empty( $freights[0]['company'] ) ) {
				$shipping_company = $freights[0]['company'];
			}
		}
		if ( ! $shipping_company ) {
			$response['message'] = esc_html__( 'Missing Shipping company', 'woocommerce-alidropship' );

			return $response;
		}
		if ( ! $customer_info['phone'] ) {
			$response['message'] = esc_html__( 'Phone number is required', 'woocommerce-alidropship' );

			return $response;
		}
		if ( ! $customer_info['street'] && ! $customer_info['address2'] ) {
			$response['message'] = esc_html__( 'Street is required', 'woocommerce-alidropship' );

			return $response;
		}
		if ( ! $customer_info['name'] ) {
			$response['message'] = esc_html__( 'Contact name is required', 'woocommerce-alidropship' );

			return $response;
		}
		if ( ! $customer_info['country'] ) {
			$response['message'] = esc_html__( 'Country is required', 'woocommerce-alidropship' );

			return $response;
		}
		if ( ! $customer_info['city'] && ! $customer_info['state'] ) {
			$response['message'] = esc_html__( 'City/State/Province is required', 'woocommerce-alidropship' );

			return $response;
		}
		if ( ! $customer_info['postcode'] ) {
			$response['message'] = esc_html__( 'Zip/Postal code is required', 'woocommerce-alidropship' );

			return $response;
		}
		if ( $customer_info['country'] === 'BR' && ! $customer_info['cpf'] ) {
			$response['message'] = esc_html__( 'CPF is mandatory in Brazil', 'woocommerce-alidropship' );

			return $response;
		}
		if ( $customer_info['country'] === 'CL' && ! $customer_info['rutNo'] ) {
			$response['message'] = esc_html__( 'RUT number is mandatory for Chilean customers', 'woocommerce-alidropship' );

			return $response;
		}
		if ( $woo_variation_id ) {
			$sku_attr = get_post_meta( $woo_variation_id, '_vi_wad_aliexpress_variation_attr', true );
		} else {
			$sku_attr = get_post_meta( $woo_product_id, '_vi_wad_aliexpress_variation_attr', true );
		}

		if ( $quantity > 0 ) {
			$response['status']        = 'success';
			$response['product_items'] = array(
				'product_count'          => $quantity,
				'product_id'             => $ali_product_id,
				'sku_attr'               => $sku_attr,
				'logistics_service_name' => $shipping_company,
				'order_memo'             => self::$settings->get_params( 'fulfill_order_note' ),
			);
		}

		return $response;
	}

	/**
	 * Build error for each item
	 *
	 * @param $order_id
	 * @param $item_ids
	 * @param $message
	 * @param $details
	 */
	protected static function set_item_error( $order_id, $item_ids, $message, &$details ) {
		foreach ( $item_ids as $item_id ) {
			$details[] = array(
				'order_id'      => $order_id,
				'order_item_id' => $item_id,
				'status'        => 'error',
				'message'       => $message,
			);
		}
	}

	/**
	 * Menu count for Ali orders
	 */
	public function menu_order_count() {
		global $submenu;
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();//show_menu_count may be changed after saving settings
		if ( isset( $submenu['woocommerce-alidropship'] ) && in_array( 'ali_orders', self::$settings->get_params( 'show_menu_count' ) ) ) {
			// Add count if user has access.
			if ( apply_filters( 'woo_aliexpress_dropship_order_count_in_menu', true ) || current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-ali-orders' ) ) ) {
				$orders_count = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_orders();
				foreach ( $submenu['woocommerce-alidropship'] as $key => $menu_item ) {
					if ( 0 === strpos( $menu_item[0], _x( 'Ali Orders', 'Admin menu name', 'woocommerce-alidropship' ) ) ) {
						$submenu['woocommerce-alidropship'][ $key ][0] .= ' <span class="update-plugins count-' . esc_attr( $orders_count ) . '"><span class="' . self::set( 'ali-orders-count' ) . '">' . number_format_i18n( $orders_count ) . '</span></span>'; // WPCS: override ok.
						break;
					}
				}
			}
		}
	}

	public function admin_enqueue_scripts() {
		$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		global $pagenow;
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-alidropship-ali-orders' ) {
			VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::enqueue_3rd_library();
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_style( 'woocommerce-alidropship-ali-orders', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'ali-orders.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_enqueue_script( 'woocommerce-alidropship-ali-orders', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'ali-orders.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			self::$order_status = ! empty( $_GET['order_status'] ) ? sanitize_text_field( $_GET['order_status'] ) : 'to_order';
			wp_localize_script( 'woocommerce-alidropship-ali-orders', 'vi_wad_ali_orders', array(
				'currency'              => get_woocommerce_currency_symbol( get_option( 'woocommerce_currency' ) ),
				'decimals'              => wc_get_price_decimals(),
				'url'                   => admin_url( 'admin-ajax.php' ),
				'_vi_wad_ajax_nonce'    => VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::create_ajax_nonce(),
				'batch_request_enable'  => self::$settings->get_params( 'batch_request_enable' ),
				'i18n_order_id'         => esc_html__( 'Order ID', 'woocommerce-alidropship' ),
				'i18n_item'             => esc_html__( 'Item', 'woocommerce-alidropship' ),
				'i18n_qty'              => esc_html__( 'Quantity', 'woocommerce-alidropship' ),
				'i18n_income'           => esc_html__( 'Income', 'woocommerce-alidropship' ),
				'i18n_total'            => esc_html__( 'Total', 'woocommerce-alidropship' ),
				'i18n_cost'             => esc_html__( 'Cost', 'woocommerce-alidropship' ),
				'i18n_total_cost'       => esc_html__( 'Total Cost', 'woocommerce-alidropship' ),
				'i18n_ship_to'          => esc_html__( 'Ship To', 'woocommerce-alidropship' ),
				'i18n_shipping_company' => esc_html__( 'Shipping Company', 'woocommerce-alidropship' ),
				'i18n_shipping_cost'    => esc_html__( 'Shipping Cost', 'woocommerce-alidropship' ),
				'i18n_delivery_time'    => esc_html__( 'Delivery Time', 'woocommerce-alidropship' ),
				'i18n_status'           => esc_html__( 'Status', 'woocommerce-alidropship' ),
				'check_all'             => self::$order_status === 'to_order' ? 1 : 0,
			) );
			add_action( 'admin_footer', array( $this, 'confirm_orders' ) );
		}
	}

	/**
	 *
	 */
	public function confirm_orders() {
		?>
        <div class="<?php echo esc_attr( self::set( array( 'confirm-orders-container', 'hidden' ) ) ) ?>">
            <div class="<?php echo esc_attr( self::set( 'overlay' ) ) ?>"></div>
            <div class="<?php echo esc_attr( self::set( 'confirm-orders-content' ) ) ?>">
                <div class="<?php echo esc_attr( self::set( 'confirm-orders-content-header' ) ) ?>">
                    <h2><?php esc_html_e( 'Confirm orders', 'woocommerce-alidropship' ) ?></h2>
                    <span class="<?php echo esc_attr( self::set( 'confirm-orders-close' ) ) ?>"></span>
                </div>
                <div class="<?php echo esc_attr( self::set( 'confirm-orders-content-body' ) ) ?>">
                </div>
                <div class="<?php echo esc_attr( self::set( 'confirm-orders-content-footer' ) ) ?>">
                    <a href="<?php echo esc_url( add_query_arg( array(
						'viWadAction'         => 'make_payment',
						'redirectOrderStatus' => 'waitBuyerPayment',
					), 'https://aliexpress.com' ) ); ?>"
                       target="_blank"
                       class="vi-ui button positive mini <?php echo esc_attr( self::set( 'confirm-orders-view-awaiting-payment-orders' ) ) ?>">
						<?php esc_html_e( 'Make Payment', 'woocommerce-alidropship' ); ?></a>
                    <span class="vi-ui button primary mini <?php echo esc_attr( self::set( 'confirm-orders-button-place' ) ) ?>">
                        <?php esc_html_e( 'Place', 'woocommerce-alidropship' ) ?>(<span
                                class="<?php echo esc_attr( self::set( 'confirm-orders-count' ) ) ?>"></span>)
                    </span>
                    <span class="vi-ui button mini <?php echo esc_attr( self::set( 'confirm-orders-button-cancel' ) ) ?>">
                        <?php esc_html_e( 'Cancel', 'woocommerce-alidropship' ) ?>
                    </span>
                    <span class="vi-ui button mini <?php echo esc_attr( self::set( 'confirm-orders-button-ok' ) ) ?>">
                        <?php esc_html_e( 'OK', 'woocommerce-alidropship' ) ?>
                    </span>
                </div>
            </div>
            <div class="<?php echo esc_attr( self::set( 'saving-overlay' ) ) ?>"></div>
        </div>
		<?php
	}

	/**
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public function save_screen_options( $status, $option, $value ) {
		if ( $option === 'vi_wad_ali_orders_per_page' ) {
			return $value;
		}

		return $status;
	}

	/**
	 *
	 */
	public function admin_menu() {
		$menu_slug   = 'woocommerce-alidropship-ali-orders';
		$import_list = add_submenu_page( 'woocommerce-alidropship', esc_html__( 'Ali Orders - AliExpress Dropshipping and Fulfillment for WooCommerce', 'woocommerce-alidropship' ), esc_html__( 'Ali Orders', 'woocommerce-alidropship' ), apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', $menu_slug ), $menu_slug, array(
			$this,
			'page_callback'
		) );
		add_action( "load-$import_list", array( $this, 'screen_options_page' ) );
	}

	/**
	 * @param $item WC_Order_Item
	 *
	 * @return array|mixed|null
	 */
	public static function get_item_saved_shipping( $item ) {
		$return         = array();
		$saved_shipping = $item->get_meta( '_vi_wot_order_item_saved_shipping', true );
		if ( $saved_shipping ) {
			$return = vi_wad_json_decode( $saved_shipping );
		} else {
			$chosen_shipping = $item->get_meta( '_vi_wot_customer_chosen_shipping', true );
			if ( $chosen_shipping ) {
				$return = vi_wad_json_decode( $chosen_shipping );
			}
		}

		return $return;
	}

	/**
	 * @param $product_id
	 * @param $woo_product_id
	 * @param $order WC_Order
	 * @param $saved_shipping
	 * @param $ship_from
	 * @param $quantity
	 * @param $use_different_currency
	 * @param $freights
	 * @param $shipping_total
	 * @param $placeable_items_count
	 * @param $shipping_company
	 *
	 * @return false|string
	 */
	public static function get_shipping_html( $product_id, $woo_product_id, $order, $saved_shipping, $ship_from, $quantity, $use_different_currency, &$freights, &$shipping_total, &$placeable_items_count, &$shipping_company ) {
		$shipping_country = $order->get_shipping_country();
		$state            = $city = '';
		if ( ! $shipping_country ) {
			$shipping_country = $order->get_billing_country();
			if ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::is_shipping_supported_by_province_city( $shipping_country ) ) {
				$state = $order->get_billing_state();
				$city  = $order->get_billing_city();
			}
		} else {
			if ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::is_shipping_supported_by_province_city( $shipping_country ) ) {
				$state = $order->get_shipping_state();
				$city  = $order->get_shipping_city();
			}
		}
		$freights         = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_shipping_by_woo_id( $woo_product_id, $shipping_country, $ship_from, $quantity, $state, $city );
		$shipping_company = isset( $saved_shipping['company'] ) ? $saved_shipping['company'] : '';
		if ( ! $shipping_company ) {
			$product_shipping = get_post_meta( $product_id, '_vi_wad_shipping_info', true );
			if ( $product_shipping && ! empty( $product_shipping['company'] ) ) {
				$shipping_company = $product_shipping['company'];
			}
		}
		if ( count( $freights ) ) {
			$shipping_cost         = false;
			$current_delivery_time = '';
			$shipping_company_name = '';
			foreach ( $freights as $freight ) {
				if ( $freight['company'] === $shipping_company ) {
					$shipping_cost         = abs( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $freight['shipping_cost'] ) );
					$current_delivery_time = $freight['delivery_time'];
					$shipping_company_name = $freight['company_name'];
					break;
				}
			}
			if ( $shipping_cost === false ) {
				$shipping_cost         = abs( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $freights[0]['shipping_cost'] ) );
				$current_delivery_time = $freights[0]['delivery_time'];
				$shipping_company_name = $freights[0]['company_name'];
			}
			if ( $shipping_cost == 0 ) {
				$shipping_cost_html = esc_html__( 'Free', 'woocommerce-alidropship' );
			} else {
				$shipping_total     += $shipping_cost;
				$shipping_cost_html = self::wc_price( $shipping_cost );
//				if ( $use_different_currency ) {
//					$shipping_cost_html .= '(' . wc_price( self::$settings->process_exchange_price( $shipping_cost ) ) . ')';
//				}
			}
			$placeable_items_count ++;
			ob_start();
			?>
            <div class="<?php echo esc_attr( self::set( 'shipping-info-company-wrap' ) ) ?>">
                <select name="<?php echo esc_attr( self::set( 'shipping-info-company', true ) ) ?>"
                        class="vi-ui dropdown fluid <?php echo esc_attr( self::set( 'shipping-info-company' ) ) ?>">
					<?php
					foreach ( $freights as $freight ) {
						$delivery_time   = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::process_delivery_time( $freight['delivery_time'] );
						$shipping_amount = $freight['shipping_cost'];
						$selected        = '';
						if ( $shipping_company === $freight['company'] ) {
							$selected = 'selected';
						}
						$shipping_amount_show = $shipping_amount == 0 ? esc_html__( 'Free', 'woocommerce-alidropship' ) : "\${$shipping_amount}";
						?>
                        <option value="<?php echo esc_attr( $freight['company'] ) ?>" <?php echo esc_attr( $selected ); ?>
                                data-company="<?php echo esc_attr( $freight['company_name'] ) ?>"
                                data-delivery_time="<?php echo esc_attr( $delivery_time ) ?>"
                                data-shipping_amount="<?php echo esc_attr( $shipping_amount ) ?>"
                                data-shipping_amount_html="<?php echo esc_attr( htmlentities( $shipping_amount == 0 ? esc_html__( 'Free', 'woocommerce-alidropship' ) : self::wc_price( $shipping_amount ) ) ) ?>"><?php echo esc_html( "{$freight['company_name']}({$delivery_time}, {$shipping_amount_show})" ) ?></option>
						<?php
					}
					?>
                </select>
                <div class="<?php echo esc_attr( self::set( 'shipping-info-company-name' ) ) ?>">
                    <span><?php echo esc_html( $shipping_company_name ) ?></span>
                </div>
            </div>
            <div>
                <strong><?php esc_html_e( 'Cost: ', 'woocommerce-alidropship' ) ?></strong><span
                        class="<?php echo esc_attr( self::set( 'order-item-shipping-cost' ) ) ?>"><?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $shipping_cost_html ); ?></span>
            </div>
			<?php
			if ( $current_delivery_time ) {
				?>
                <div>
                    <strong><?php esc_html_e( 'Delivery time: ', 'woocommerce-alidropship' ) ?></strong><span
                            class="<?php echo esc_attr( self::set( 'order-item-shipping-time' ) ) ?>"><?php echo VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::process_delivery_time( $current_delivery_time ); ?></span>
                </div>
				<?php
			}
			$shipping_column_html = ob_get_clean();
		} else {
			$shipping_column_html = esc_html__( 'Not available', 'woocommerce-alidropship' );
		}

		return $shipping_column_html;
	}

	/**
	 *
	 */
	public function page_callback() {
		$user     = get_current_user_id();
		$screen   = get_current_screen();
		$option   = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta( $user, $option, true );
		if ( empty ( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}
		$paged = isset( $_GET['paged'] ) ? sanitize_text_field( $_GET['paged'] ) : 1;

		add_filter( 'posts_where', array( $this, 'filter_where' ), 10, 2 );
		$order_status_for_fulfill = self::$settings->get_params( 'order_status_for_fulfill' );
		$args                     = array(
			'post_type'      => 'shop_order',
			'post_status'    => 'any',
			'order'          => 'DESC',
			'orderby'        => 'ID',
			'fields'         => 'ids',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
		);
		if ( $order_status_for_fulfill ) {
			$args['post_status'] = $order_status_for_fulfill;
		}
		$vi_wad_search_id = isset( $_GET['vi_wad_search_id'] ) ? sanitize_text_field( $_GET['vi_wad_search_id'] ) : '';
		$keyword          = isset( $_GET['vi_wad_search'] ) ? sanitize_text_field( $_GET['vi_wad_search'] ) : '';
		if ( $vi_wad_search_id ) {
			$args['post__in'] = array( $vi_wad_search_id );
			$keyword          = '';
		} else if ( $keyword ) {
			$order_ids = wc_order_search( $keyword );

			if ( ! empty( $order_ids ) ) {
				$args['post__in'] = array_merge( $order_ids, array( 0 ) );
			}
		}
		$the_query = new WP_Query( $args );
		$count     = $the_query->found_posts;
		wp_reset_postdata();
		remove_filter( 'posts_where', array( $this, 'filter_where' ), 10 );
		remove_filter( 'posts_join', array( $this, 'posts_join' ), 10 );
		remove_filter( 'posts_distinct', array( $this, 'posts_distinct' ), 10 );
		$access_token  = self::$settings->get_params( 'access_token' );
		$access_tokens = self::$settings->get_params( 'access_tokens' );
		$page_content  = '';
		if ( $the_query->have_posts() ) {
			ob_start();
			?>
            <form method="get" class="vi-ui segment <?php echo esc_attr( self::set( 'pagination-form' ) ) ?>">
                <input type="hidden" name="page" value="woocommerce-alidropship-ali-orders">
                <input type="hidden" name="order_status" value="<?php echo esc_attr( self::$order_status ) ?>">
                <div class="tablenav top">
                    <div class="<?php echo esc_attr( self::set( 'button-bulk-place-order-container' ) ) ?>">
                        <input type="checkbox"
                               class="<?php echo esc_attr( self::set( 'button-bulk-select-all-orders' ) ) ?>">
                        <span class="vi-ui button blue inverted disabled mini <?php echo esc_attr( self::set( 'button-bulk-place-order' ) ) ?>"
                              title="<?php esc_attr_e( 'Select multiple orders to place', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Bulk place orders', 'woocommerce-alidropship' ) ?>(<span
                                    class="<?php echo esc_attr( self::set( 'button-bulk-place-order-count' ) ) ?>">0</span>)</span>
                    </div>
                    <div class="tablenav-pages">
                        <div class="pagination-links">
							<?php
							$total_page = ceil( $count / $per_page );
							if ( $paged > 2 ) {
								?>
                                <a class="prev-page button" href="<?php echo esc_url( add_query_arg(
									array(
										'page'          => 'woocommerce-alidropship-ali-orders',
										'paged'         => 1,
										'vi_wad_search' => $keyword,
										'order_status'  => self::$order_status,
									), admin_url( 'admin.php' )
								) ) ?>"><span
                                            class="screen-reader-text"><?php esc_html_e( 'First Page', 'woocommerce-alidropship' ) ?></span><span
                                            aria-hidden="true">«</span></a>
								<?php
							} else {
								?>
                                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
								<?php
							}
							/*Previous button*/
							if ( $per_page * $paged > $per_page ) {
								$p_paged = $paged - 1;
							} else {
								$p_paged = 0;
							}
							if ( $p_paged ) {
								$p_url = add_query_arg(
									array(
										'page'          => 'woocommerce-alidropship-ali-orders',
										'paged'         => $p_paged,
										'vi_wad_search' => $keyword,
										'order_status'  => self::$order_status,
									), admin_url( 'admin.php' )
								);
								?>
                                <a class="prev-page button" href="<?php echo esc_url( $p_url ) ?>"><span
                                            class="screen-reader-text"><?php esc_html_e( 'Previous Page', 'woocommerce-alidropship' ) ?></span><span
                                            aria-hidden="true">‹</span></a>
								<?php
							} else {
								?>
                                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
								<?php
							}
							?>
                            <span class="screen-reader-text"><?php esc_html_e( 'Current Page', 'woocommerce-alidropship' ) ?></span>
                            <span id="table-paging" class="paging-input">
                                    <input class="current-page" type="text" name="paged" size="1"
                                           value="<?php echo esc_html( $paged ) ?>"><span class="tablenav-paging-text"> of <span
                                            class="total-pages"><?php echo esc_html( $total_page ) ?></span></span>
                                    </span>
							<?php /*Next button*/
							if ( $per_page * $paged < $count ) {
								$n_paged = $paged + 1;
							} else {
								$n_paged = 0;
							}
							if ( $n_paged ) {
								$n_url = add_query_arg(
									array(
										'page'          => 'woocommerce-alidropship-ali-orders',
										'paged'         => $n_paged,
										'vi_wad_search' => $keyword,
										'order_status'  => self::$order_status,
									), admin_url( 'admin.php' )
								); ?>
                                <a class="next-page button" href="<?php echo esc_url( $n_url ) ?>"><span
                                            class="screen-reader-text"><?php esc_html_e( 'Next Page', 'woocommerce-alidropship' ) ?></span><span
                                            aria-hidden="true">›</span></a>
								<?php
							} else {
								?>
                                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
								<?php
							}
							if ( $total_page > $paged + 1 ) {
								?>
                                <a class="next-page button" href="<?php echo esc_url( add_query_arg(
									array(
										'page'          => 'woocommerce-alidropship-ali-orders',
										'paged'         => $total_page,
										'vi_wad_search' => $keyword,
										'order_status'  => self::$order_status,
									), admin_url( 'admin.php' )
								) ) ?>"><span
                                            class="screen-reader-text"><?php esc_html_e( 'Last Page', 'woocommerce-alidropship' ) ?></span><span
                                            aria-hidden="true">»</span></a>
								<?php
							} else {
								?>
                                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
								<?php
							}
							?>
                        </div>
                    </div>
                    <p class="search-box">
                        <input type="search" class="text short" name="vi_wad_search"
                               placeholder="<?php esc_attr_e( 'Search order', 'woocommerce-alidropship' ) ?>"
                               value="<?php echo esc_attr( $keyword ) ?>">
                        <input type="submit" name="submit" class="button"
                               value="<?php echo esc_attr( 'Search order', 'woocommerce-alidropship' ) ?>">
                    </p>
                </div>
            </form>
			<?php
			$pagination_html             = ob_get_clean();
			$key                         = 0;
			$currency                    = 'USD';
			$woocommerce_currency        = get_option( 'woocommerce_currency' );
			$woocommerce_currency_symbol = get_woocommerce_currency_symbol( $woocommerce_currency );
			$use_different_currency      = false;
			if ( strtolower( $woocommerce_currency ) !== strtolower( $currency ) ) {
				$use_different_currency = true;
			}
			$is_wpml = false;
			global $sitepress;
			if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
				$default_lang     = apply_filters( 'wpml_default_language', null );
				$current_language = apply_filters( 'wpml_current_language', null );
				if ( $current_language && $current_language !== $default_lang ) {
					$is_wpml = true;
				}
			}
			foreach ( $the_query->posts as $order_id ) {
				$order = wc_get_order( $order_id );
				if ( $order ) {
					$order_currency = $order->get_currency() ? $order->get_currency() : $woocommerce_currency;
					$order_items    = $order->get_items();
					$ali_pid        = '';
					if ( count( $order_items ) ) {
						$order_items_html = '';
						$product_cost     = $shipping_total = $woo_line_total = $placeable_items_count = 0;
						foreach ( $order_items as $item_id => $item ) {
							$ali_order_id     = $item->get_meta( '_vi_wad_aliexpress_order_id', true );
							$ali_order_detail = $tracking_url = $tracking_url_btn = '';
							if ( $ali_order_id ) {
								$ali_order_detail = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_order_detail_url( $ali_order_id );
								$tracking_url     = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_tracking_url( $ali_order_id );
								$tracking_url_btn = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_get_tracking_url( $ali_order_id );
							}
							$item_tracking_data = $item->get_meta( '_vi_wot_order_item_tracking_data', true );
							$tracking_number    = '';
							if ( $item_tracking_data ) {
								$item_tracking_data    = vi_wad_json_decode( $item_tracking_data );
								$current_tracking_data = array_pop( $item_tracking_data );
								$tracking_number       = $current_tracking_data['tracking_number'];
							}
							$get_tracking = array( 'item-actions' );
							if ( ! $ali_order_id ) {
								$get_tracking[] = 'invisible';
							}
							$woo_product    = is_callable( array(
								$item,
								'get_product'
							) ) ? $item->get_product() : null;
							$is_ali_product = false;
							if ( $woo_product ) {
								$woo_product_id   = $item->get_product_id();
								$woo_variation_id = $item->get_variation_id();
								$wpml_product_id  = $wpml_variation_id = '';
								if ( $is_wpml ) {
									/*If this product is translated by WPML, only the original product has connection with AliExpress*/
									$wpml_object_id = apply_filters(
										'wpml_object_id', $woo_product_id, 'product', false, $sitepress->get_default_language()
									);
									if ( $wpml_object_id != $woo_product_id ) {
										$wpml_product = wc_get_product( $wpml_object_id );
										if ( $wpml_product ) {
											$wpml_product_id = $wpml_object_id;
										}
									}
									if ( $woo_variation_id ) {
										$wpml_object_id = apply_filters(
											'wpml_object_id', $woo_variation_id, 'product', false, $sitepress->get_default_language()
										);
										if ( $wpml_object_id != $woo_variation_id ) {
											$wpml_variation = wc_get_product( $wpml_object_id );
											if ( $wpml_variation ) {
												$wpml_variation_id = $wpml_object_id;
											}
										}
									}
								}
								if ( $wpml_product_id ) {
									$ali_product_id = get_post_meta( $wpml_product_id, '_vi_wad_aliexpress_product_id', true );
								} else {
									$ali_product_id = get_post_meta( $woo_product_id, '_vi_wad_aliexpress_product_id', true );
								}
								if ( $ali_product_id ) {
									if ( $wpml_product_id ) {
										$product_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_woo_id( $wpml_product_id );
									} else {
										$product_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_woo_id( $woo_product_id );
									}
									if ( $product_id ) {
										$product_obj = get_post( $product_id );
										if ( $product_obj ) {
											$ship_from = '';
											if ( $woo_variation_id ) {
												if ( $wpml_variation_id ) {
													$sku_attr  = get_post_meta( $wpml_variation_id, '_vi_wad_aliexpress_variation_attr', true );
													$ship_from = get_post_meta( $wpml_variation_id, '_vi_wad_aliexpress_variation_ship_from', true );
												} else {
													$sku_attr  = get_post_meta( $woo_variation_id, '_vi_wad_aliexpress_variation_attr', true );
													$ship_from = get_post_meta( $woo_variation_id, '_vi_wad_aliexpress_variation_ship_from', true );
												}
											} else {
												if ( $wpml_product_id ) {
													$sku_attr = get_post_meta( $wpml_product_id, '_vi_wad_aliexpress_variation_attr', true );
												} else {
													$sku_attr = get_post_meta( $woo_product_id, '_vi_wad_aliexpress_variation_attr', true );
												}
											}
											$variations = get_post_meta( $product_id, '_vi_wad_variations', true );
											if ( count( $variations ) ) {
												$is_ali_product = true;
												if ( $sku_attr ) {
													$variation   = array();
													$variation_k = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::search_sku_attr( $sku_attr, array_column( $variations, 'skuAttr' ) );
													if ( $variation_k !== false ) {
														$variation = $variations[ $variation_k ];
													}
//													foreach ( $variations as $variation_k => $variation_v ) {
//														if ( $variation_v['skuAttr'] === $sku_attr ) {
//															$variation = $variation_v;
//															break;
//														}
//													}
												} else {
													$variation = $variations[0];
												}

												$order_item_class = array( 'order-item' );
												if ( count( $variation ) ) {
													$image                = $woo_product->get_image();
													$quantity             = $item->get_quantity() + $order->get_qty_refunded_for_item( $item_id );
													$price                = $variation['sale_price'] ? $variation['sale_price'] : $variation['regular_price'];
													$product_cost         += ( $price * $quantity );
													$item_sub_total       = $order->get_item_subtotal( $item, true, true ) * $quantity;
													$woo_line_total       += $item_sub_total;
													$woo_shipping_info    = $item->get_meta( '_vi_wot_customer_chosen_shipping' );
													$woo_shipping_display = '-';
													if ( $woo_shipping_info ) {
														$woo_shipping_info = vi_wad_json_decode( $woo_shipping_info );
														$delivery_time     = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::process_delivery_time( $woo_shipping_info['delivery_time'] );
														$woo_shipping_cost = abs( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $woo_shipping_info['shipping_cost'] ) );
														if ( $woo_shipping_cost ) {
															$woo_shipping_cost    = wc_price( $woo_shipping_cost, array( 'currency' => get_post_meta( $order_id, '_order_currency', true ) ) );
															$woo_shipping_display = "[{$woo_shipping_cost}] {$woo_shipping_info['company_name']}({$delivery_time})";
														} else {
															$woo_shipping_display = esc_html__( 'Free', 'woocommerce-alidropship' ) . "({$delivery_time})";
														}
													}
													$shipping_column_html = '';
													$saved_shipping       = self::get_item_saved_shipping( $item );
													if ( ! $ali_order_id ) {
														if ( ! $ali_pid ) {
															$ali_pid = $ali_product_id;
														}
														$shipping_column_html = self::get_shipping_html( $product_id, $wpml_product_id ? $wpml_product_id : $woo_product_id, $order, $saved_shipping, $ship_from, $quantity, $use_different_currency, $freights, $shipping_total, $placeable_items_count, $shipping_company );
													} else {
														$ali_order = VI_WOOCOMMERCE_ALIDROPSHIP_Ali_Orders_Info_Table::get_row_by_order_id( $ali_order_id );
														if ( ! empty( $ali_order['order_total'] ) ) {
															$price        = $ali_order['order_total'];
															$product_cost += ( $price * $quantity );
														}
														$order_item_class[] = 'order-item-can-not-be-ordered';
														$freights           = array();
														if ( isset( $saved_shipping['company_name'] ) ) {
															ob_start();
															?>
                                                            <div class="<?php echo esc_attr( self::set( 'shipping-info-company-name' ) ) ?>"
                                                                 title="<?php echo esc_attr( $saved_shipping['company_name'] ) ?>">
                                                                        <span><input type="text" readonly
                                                                                     value="<?php echo esc_html( $saved_shipping['company_name'] ) ?>"></span>
                                                            </div>
															<?php
															$shipping_column_html .= ob_get_clean();
														}
														if ( isset( $saved_shipping['shipping_cost'] ) && $saved_shipping['shipping_cost'] !== '' ) {
															$shipping_cost      = abs( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $saved_shipping['shipping_cost'] ) );
															$shipping_total     += $shipping_cost;
															$shipping_cost_html = '';
															if ( strval( $saved_shipping['shipping_cost'] ) === '0' ) {
																$shipping_cost_html = esc_html__( 'Free', 'woocommerce-alidropship' );
															} elseif ( $saved_shipping['shipping_cost'] ) {
																$shipping_cost_html = self::wc_price( $shipping_cost );
															}
															ob_start();
															?>
                                                            <div>
                                                                <strong><?php esc_html_e( 'Cost: ', 'woocommerce-alidropship' ) ?></strong><span
                                                                        class="<?php echo esc_attr( self::set( 'order-item-shipping-cost' ) ) ?>"><?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $shipping_cost_html ); ?></span>
                                                            </div>
															<?php
															$shipping_column_html .= ob_get_clean();
														}
														if ( isset( $saved_shipping['delivery_time'] ) && $saved_shipping['delivery_time'] !== '' ) {
															ob_start();
															?>
                                                            <div>
                                                                <strong><?php esc_html_e( 'Delivery time: ', 'woocommerce-alidropship' ) ?></strong><span
                                                                        class="<?php echo esc_attr( self::set( 'order-item-shipping-time' ) ) ?>"><?php echo esc_html( $saved_shipping['delivery_time'] ); ?></span>
                                                            </div>
															<?php
															$shipping_column_html .= ob_get_clean();
														}
													}
													ob_start();
													?>
                                                    <tr class="<?php echo esc_attr( self::set( $order_item_class ) ); ?>"
                                                        data-cost="<?php echo esc_attr( $price ); ?>"
                                                        data-quantity="<?php echo esc_attr( $quantity ); ?>"
                                                        data-order_item_id="<?php echo esc_attr( $item_id ); ?>"
                                                        data-sub_total_html="<?php echo htmlentities( wc_price( $item_sub_total, array(
														    'currency' => $order_currency,
													    ) ) ); ?>"
                                                        data-sub_total="<?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $item_sub_total ); ?>">
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="check" width="1%">
                                                            <input class="<?php echo esc_attr( self::set( 'order-item-check' ) ) ?>"
                                                                   type="checkbox" <?php if ( $ali_order_id || empty( $freights ) ) {
																echo esc_attr( 'disabled' );
															} ?>>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="image">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-image' ) ) ?>">
																<?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $image ); ?>
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="name">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-name' ) ) ?>"
                                                                 title="<?php echo esc_attr( $product_obj->post_title ); ?>">
                                                                <a target="_blank"
                                                                   href="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $ali_product_id ) ) ?>"><?php echo esc_html( $product_obj->post_title ); ?></a>
                                                            </div>
															<?php
															$attribute = '';
															if ( isset( $variation['attributes'] ) && is_array( $variation['attributes'] ) ) {
																$attribute = implode( '/ ', $variation['attributes'] );
															}
															if ( $attribute ) {
																?>
                                                                <div class="<?php echo esc_attr( self::set( 'order-item-variation' ) ) ?>">
                                                                    <strong><?php echo esc_html( $attribute ); ?></strong>
                                                                </div>
																<?php
															}
															if ( $variation['sku'] ) {
																?>
                                                                <div class="<?php echo esc_attr( self::set( 'order-item-sku' ) ) ?>">
                                                                    <strong><?php esc_html_e( 'Sku: ', 'woocommerce-alidropship' ); ?></strong><?php echo esc_html( $variation['sku'] ); ?>
                                                                </div>
																<?php
															}
															$notice_time    = '';
															$message_status = 'warning';
															$product_notice = get_post_meta( $product_id, '_vi_wad_update_product_notice', true );
															$message        = '';
															if ( $product_notice ) {
																/*Notice generated are syncing products*/
																$notice_time = $product_notice['time'];
																if ( $product_notice['is_offline'] ) {
																	$message_status = 'negative';
																	$message        = esc_html__( 'Ali product is no longer available', 'woocommerce-alidropship' );
																} elseif ( $product_notice['is_out_of_stock'] ) {
																	$message_status = 'negative';
																	$message        = esc_html__( 'Ali product is out of stock', 'woocommerce-alidropship' );
																} elseif ( empty( $product_notice['hide'] ) ) {
																	if ( count( array_intersect( $product_notice['not_available'], array(
																		$wpml_product_id ? $wpml_product_id : $woo_product_id,
																		$wpml_variation_id ? $wpml_variation_id : $woo_variation_id
																	) ) ) ) {
																		$message = esc_html__( 'Ali product is no longer available', 'woocommerce-alidropship' );
																	} elseif ( count( array_intersect( $product_notice['out_of_stock'], array(
																		$wpml_product_id ? $wpml_product_id : $woo_product_id,
																		$wpml_variation_id ? $wpml_variation_id : $woo_variation_id
																	) ) ) ) {
																		$message = esc_html__( 'Ali product is out of stock', 'woocommerce-alidropship' );
																	} elseif ( count( array_intersect( $product_notice['price_changes'], array(
																		$wpml_product_id ? $wpml_product_id : $woo_product_id,
																		$wpml_variation_id ? $wpml_variation_id : $woo_variation_id
																	) ) ) ) {
																		$message = esc_html__( 'Ali product has price changed', 'woocommerce-alidropship' );
																	}
																}
															}
															if ( $message ) {
																?>
                                                                <div class="vi-ui message <?php echo esc_attr( self::set( 'product-notice-message' ) ) ?> <?php echo esc_attr( $message_status ) ?>">
                                                                    <div>
                                                                        <span>
                                                                            <?php
                                                                            echo esc_html( $message );
                                                                            if ( $notice_time ) {
	                                                                            $date_format = get_option( 'date_format' );
	                                                                            if ( ! $date_format ) {
		                                                                            $date_format = 'F j, Y';
	                                                                            }
	                                                                            ?>
                                                                                <span class="<?php echo esc_attr( self::set( 'product-notice-time' ) ) ?>">
                                                                                            <?php
                                                                                            printf( esc_html__( '(%s)', 'woocommerce-alidropship' ), date_i18n( "{$date_format}", $notice_time ) );
                                                                                            ?>
                                                                                        </span>
	                                                                            <?php
                                                                            }
                                                                            ?>
                                                                            <a href="<?php echo esc_url( admin_url( "admin.php?page=woocommerce-alidropship-imported-list&post_status=publish&vi_wad_search_woo_id={$woo_product_id}" ) ) ?>"
                                                                               target="_blank"
                                                                               class="<?php echo esc_attr( self::set( 'view-item-on-imported-page' ) ) ?>"
                                                                               title="<?php esc_attr_e( 'View item on Imported page', 'woocommerce-alidropship' ); ?>"><i
                                                                                        class="icon eye"></i></a>
                                                                        </span>
                                                                    </div>
                                                                </div>
																<?php
															}
															?>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="subtotal">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-subtotal' ) ) ?>">
																<?php echo wc_price( $item_sub_total, array(
																	'currency' => $order_currency,
																) ); ?>
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="woo_shipping_cost">
                                                            <div class="<?php echo esc_attr( self::set( 'woo-item-shipping' ) ) ?>">
																<?php
																echo $woo_shipping_display;
																?>
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="cost">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-cost' ) ) ?>">
																<?php echo self::wc_price( $price ); ?>
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="quantity">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-quantity' ) ) ?>">
                                                                <div>
                                                                    <span>× </span><span><?php echo esc_html( $quantity ) ?></span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="shipping">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-shipping' ) ) ?>">
																<?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $shipping_column_html ); ?>
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="ali_order">
                                                            <div class="<?php echo esc_attr( self::set( 'item-ali-order-container' ) ) ?>">
                                                                <div class="<?php echo esc_attr( self::set( array(
																	'item-ali-order-details',
																	'item-ali-order-id'
																) ) ) ?>"
                                                                     data-product_item_id="<?php echo esc_attr( $item_id ) ?>">
                                                                    <div class="<?php echo esc_attr( self::set( 'item-ali-order-label' ) ) ?>">
                                                                        <span><?php esc_html_e( 'Ali Order ID', 'woocommerce-alidropship' ) ?></span>
                                                                    </div>
                                                                    <div class="<?php echo esc_attr( self::set( 'item-ali-order-value' ) ) ?>">
                                                                        <a class="<?php echo esc_attr( self::set( 'ali-order-id' ) ) ?>"
                                                                           href="<?php echo esc_url( $ali_order_detail ) ?>"
                                                                           data-old_ali_order_id="<?php echo esc_attr( $ali_order_id ) ?>"
                                                                           target="_blank">
                                                                            <input readonly
                                                                                   class="<?php echo esc_attr( self::set( array( 'ali-order-id-input' ) ) ) ?>"
                                                                                   value="<?php echo esc_attr( $ali_order_id ) ?>">
                                                                        </a>
                                                                        <div class="<?php echo esc_attr( self::set( 'item-actions' ) ) ?>">
                                                                                <span class="dashicons dashicons-edit <?php echo esc_attr( self::set( 'item-actions-edit' ) ) ?>"
                                                                                      title="<?php esc_attr_e( 'Edit', 'woocommerce-alidropship' ) ?>"></span>
                                                                            <span class="dashicons dashicons-yes <?php echo esc_attr( self::set( array(
																				'item-actions-save',
																				'hidden'
																			) ) ) ?>"
                                                                                  title="<?php esc_attr_e( 'Save', 'woocommerce-alidropship' ) ?>"></span>
                                                                            <span class="dashicons dashicons-no-alt <?php echo esc_attr( self::set( array(
																				'item-actions-cancel',
																				'hidden'
																			) ) ) ?>"
                                                                                  title="<?php esc_attr_e( 'Cancel', 'woocommerce-alidropship' ) ?>"></span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="<?php echo esc_attr( self::set( array(
																		'item-ali-order-value-overlay',
																		'hidden'
																	) ) ) ?>"></div>
                                                                </div>
                                                                <div class="<?php echo esc_attr( self::set( array(
																	'item-ali-order-details',
																	'item-tracking-number'
																) ) ) ?>"
                                                                     data-product_item_id="<?php echo esc_attr( $item_id ) ?>">
                                                                    <div class="<?php echo esc_attr( self::set( 'item-ali-order-label' ) ) ?>">
                                                                        <span><?php esc_html_e( 'Tracking number', 'woocommerce-alidropship' ) ?></span>
                                                                    </div>
                                                                    <div class="<?php echo esc_attr( self::set( 'item-ali-order-value' ) ) ?>">
                                                                        <a class="<?php echo esc_attr( self::set( 'ali-tracking-number' ) ) ?>"
                                                                           href="<?php echo esc_url( $tracking_url ) ?>"
                                                                           target="_blank">
                                                                            <input readonly
                                                                                   class="<?php echo esc_attr( self::set( array( 'ali-tracking-number-input' ) ) ) ?>"
                                                                                   value="<?php echo esc_attr( $tracking_number ) ?>">
                                                                        </a>
                                                                        <div class="<?php echo esc_attr( self::set( $get_tracking ) ) ?>">
                                                                            <a href="<?php echo esc_url( $tracking_url_btn ) ?>"
                                                                               target="_blank">
                                                                                <span class="dashicons dashicons-arrow-down-alt <?php echo esc_attr( self::set( 'item-actions-get-tracking' ) ) ?>"
                                                                                      title="<?php esc_attr_e( 'Get tracking', 'woocommerce-alidropship' ) ?>">
                                                                                </span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </td>
                                                    </tr>
													<?php
													$order_items_html .= ob_get_clean();
												} else {
													$order_item_class[] = 'order-item-can-not-be-ordered';
													if ( ! $ali_pid ) {
														$ali_pid = $ali_product_id;
													}
													$image          = $woo_product->get_image();
													$quantity       = $item->get_quantity();
													$item_sub_total = $order->get_item_subtotal( $item, true, true ) * $quantity;
													$woo_line_total += $item_sub_total;
													ob_start();
													?>
                                                    <tr class="<?php echo esc_attr( self::set( $order_item_class ) ); ?>"
                                                        data-cost=""
                                                        data-quantity="<?php echo esc_attr( $quantity ); ?>"
                                                        data-order_item_id="<?php echo esc_attr( $item_id ); ?>"
                                                        data-sub_total_html="<?php echo htmlentities( wc_price( $item_sub_total, array(
														    'currency' => $order_currency,
													    ) ) ); ?>"
                                                        data-sub_total="<?php echo esc_attr( $item_sub_total ); ?>">
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="check" width="1%">
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="image">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-image' ) ) ?>">
																<?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $image ); ?>
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="name">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-name' ) ) ?>"
                                                                 title="<?php echo esc_attr( $item->get_name() ); ?>">
                                                                <a target="_blank"
                                                                   href="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $ali_product_id ) ) ?>"><?php echo esc_html( $item->get_name() ); ?></a>
                                                            </div>
															<?php
															if ( $woo_product->get_sku() ) {
																?>
                                                                <div class="<?php echo esc_attr( self::set( 'order-item-sku' ) ) ?>">
                                                                    <strong><?php esc_html_e( 'Sku: ', 'woocommerce-alidropship' ); ?></strong><?php echo esc_html( $woo_product->get_sku() ); ?>
                                                                </div>
																<?php
															}
															if ( ! $ali_order_id ) {
																?>
                                                                <div class="vi-ui message negative"><?php esc_html_e( 'This item may be no longer available to order.', 'woocommerce-alidropship' ); ?>
                                                                    <a href="<?php echo esc_url( admin_url( "admin.php?page=woocommerce-alidropship-imported-list&post_status=publish&vi_wad_search_woo_id={$woo_product_id}" ) ) ?>"
                                                                       target="_blank"
                                                                       class="<?php echo esc_attr( self::set( 'view-item-on-imported-page' ) ) ?>"
                                                                       title="<?php esc_attr_e( 'View item on Imported page', 'woocommerce-alidropship' ); ?>"><i
                                                                                class="icon eye"></i></a>
                                                                </div>
																<?php
															}
															?>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="subtotal">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-subtotal' ) ) ?>">
																<?php echo wc_price( $item_sub_total, array(
																	'currency' => $order_currency,
																) ); ?>
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="woo_shipping_cost">
                                                            <div class="<?php echo esc_attr( self::set( 'woo-item-shipping' ) ) ?>">
																<?php
																//                                                                echo self::wc_price( $price );
																?>
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="cost">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-cost' ) ) ?>">
                                                                -
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="quantity">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-quantity' ) ) ?>">
                                                                <div>
                                                                    <span>× </span><span><?php echo esc_html( $item->get_quantity() ) ?></span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="shipping">
                                                            <div class="<?php echo esc_attr( self::set( 'order-item-shipping' ) ) ?>"></div>
                                                        </td>
                                                        <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                                            data-column_name="ali_order">
                                                            <div class="<?php echo esc_attr( self::set( 'item-ali-order-container' ) ) ?>">
                                                                <div class="<?php echo esc_attr( self::set( array(
																	'item-ali-order-details',
																	'item-ali-order-id'
																) ) ) ?>"
                                                                     data-product_item_id="<?php echo esc_attr( $item_id ) ?>">
                                                                    <div class="<?php echo esc_attr( self::set( 'item-ali-order-label' ) ) ?>">
                                                                        <span><?php esc_html_e( 'Ali Order ID', 'woocommerce-alidropship' ) ?></span>
                                                                    </div>
                                                                    <div class="<?php echo esc_attr( self::set( 'item-ali-order-value' ) ) ?>">
                                                                        <a class="<?php echo esc_attr( self::set( 'ali-order-id' ) ) ?>"
                                                                           href="<?php echo esc_url( $ali_order_detail ) ?>"
                                                                           data-old_ali_order_id="<?php echo esc_attr( $ali_order_id ) ?>"
                                                                           target="_blank">
                                                                            <input readonly
                                                                                   class="<?php echo esc_attr( self::set( array( 'ali-order-id-input' ) ) ) ?>"
                                                                                   value="<?php echo esc_attr( $ali_order_id ) ?>">
                                                                        </a>
                                                                        <div class="<?php echo esc_attr( self::set( 'item-actions' ) ) ?>">
                                                                                <span class="dashicons dashicons-edit <?php echo esc_attr( self::set( 'item-actions-edit' ) ) ?>"
                                                                                      title="<?php esc_attr_e( 'Edit', 'woocommerce-alidropship' ) ?>"></span>
                                                                            <span class="dashicons dashicons-yes <?php echo esc_attr( self::set( array(
																				'item-actions-save',
																				'hidden'
																			) ) ) ?>"
                                                                                  title="<?php esc_attr_e( 'Save', 'woocommerce-alidropship' ) ?>"></span>
                                                                            <span class="dashicons dashicons-no-alt <?php echo esc_attr( self::set( array(
																				'item-actions-cancel',
																				'hidden'
																			) ) ) ?>"
                                                                                  title="<?php esc_attr_e( 'Cancel', 'woocommerce-alidropship' ) ?>"></span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="<?php echo esc_attr( self::set( array(
																		'item-ali-order-value-overlay',
																		'hidden'
																	) ) ) ?>"></div>
                                                                </div>
                                                                <div class="<?php echo esc_attr( self::set( array(
																	'item-ali-order-details',
																	'item-tracking-number'
																) ) ) ?>"
                                                                     data-product_item_id="<?php echo esc_attr( $item_id ) ?>">
                                                                    <div class="<?php echo esc_attr( self::set( 'item-ali-order-label' ) ) ?>">
                                                                        <span><?php esc_html_e( 'Tracking number', 'woocommerce-alidropship' ) ?></span>
                                                                    </div>
                                                                    <div class="<?php echo esc_attr( self::set( 'item-ali-order-value' ) ) ?>">
                                                                        <a class="<?php echo esc_attr( self::set( 'ali-tracking-number' ) ) ?>"
                                                                           href="<?php echo esc_url( $tracking_url ) ?>"
                                                                           target="_blank">
                                                                            <input readonly
                                                                                   class="<?php echo esc_attr( self::set( array( 'ali-tracking-number-input' ) ) ) ?>"
                                                                                   value="<?php echo esc_attr( $tracking_number ) ?>">
                                                                        </a>
                                                                        <div class="<?php echo esc_attr( self::set( $get_tracking ) ) ?>">
                                                                            <a href="<?php echo esc_url( $tracking_url_btn ) ?>"
                                                                               target="_blank">
                                                                                <span class="dashicons dashicons-arrow-down-alt <?php echo esc_attr( self::set( 'item-actions-get-tracking' ) ) ?>"
                                                                                      title="<?php esc_attr_e( 'Get tracking', 'woocommerce-alidropship' ) ?>">
                                                                                </span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </td>
                                                    </tr>
													<?php
													$order_items_html .= ob_get_clean();
												}
											}
										}
									}
								}
							}
							if ( ! $is_ali_product ) {
								$order_item_class = array( 'order-item', 'order-item-can-not-be-ordered' );
								$image            = $woo_product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $woo_product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) : '';
								$quantity         = $item->get_quantity();
								$item_sub_total   = $order->get_item_subtotal( $item, true, true ) * $quantity;
								$woo_line_total   += $item_sub_total;
								ob_start();
								?>
                                <tr class="<?php echo esc_attr( self::set( $order_item_class ) ); ?>"
                                    data-cost=""
                                    data-quantity="<?php echo esc_attr( $quantity ); ?>"
                                    data-order_item_id="<?php echo esc_attr( $item_id ); ?>"
                                    data-sub_total_html="<?php echo htmlentities( wc_price( $item_sub_total, array(
									    'currency' => $order_currency,
								    ) ) ); ?>"
                                    data-sub_total="<?php echo esc_attr( $item_sub_total ); ?>">
                                    <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                        data-column_name="check" width="1%">
                                    </td>
                                    <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                        data-column_name="image">
                                        <div class="<?php echo esc_attr( self::set( 'order-item-image' ) ) ?>">
											<?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $image ); ?>
                                        </div>
                                    </td>
                                    <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                        data-column_name="name">
                                        <div class="<?php echo esc_attr( self::set( 'order-item-name' ) ) ?>"
                                             title="<?php echo esc_attr( $item->get_name() ); ?>">
											<?php
											if ( $woo_product ) {
												?>
                                                <a target="_blank"
                                                   href="<?php echo esc_url( admin_url( 'post.php?post=' . $item->get_product_id() . '&action=edit' ) ) ?>"><?php echo esc_html( $item->get_name() ); ?></a>
												<?php
											} else {
												echo esc_html( $item->get_name() );
											}
											?>

                                        </div>
										<?php
										if ( $woo_product && $woo_product->get_sku() ) {
											?>
                                            <div class="<?php echo esc_attr( self::set( 'order-item-sku' ) ) ?>">
                                                <strong><?php esc_html_e( 'Sku: ', 'woocommerce-alidropship' ); ?></strong><?php echo esc_html( $woo_product->get_sku() ); ?>
                                            </div>
											<?php
										}
										?>
                                        <div class="vi-ui message negative"><?php esc_html_e( 'This item is not an AliExpress product or it was deleted', 'woocommerce-alidropship' ); ?></div>
                                    </td>
                                    <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                        data-column_name="subtotal">
                                        <div class="<?php echo esc_attr( self::set( 'order-item-subtotal' ) ) ?>">
											<?php echo wc_price( $item_sub_total, array(
												'currency' => $order_currency,
											) ); ?>
                                        </div>
                                    </td>
                                    <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                        data-column_name="woo_shipping_cost">
                                        <div class="<?php echo esc_attr( self::set( 'woo-item-shipping' ) ) ?>">
											<?php
											//                                                                echo self::wc_price( $price );
											?>
                                        </div>
                                    </td>
                                    <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                        data-column_name="cost">
                                        <div class="<?php echo esc_attr( self::set( 'order-item-cost' ) ) ?>">
                                            -
                                        </div>
                                    </td>
                                    <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                        data-column_name="quantity">
                                        <div class="<?php echo esc_attr( self::set( 'order-item-quantity' ) ) ?>">
                                            <div>
                                                <span>× </span><span><?php echo esc_html( $item->get_quantity() ) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                        data-column_name="shipping">
                                        <div class="<?php echo esc_attr( self::set( 'order-item-shipping' ) ) ?>"></div>
                                    </td>
                                    <td class="<?php echo esc_attr( self::set( 'order-table-column' ) ) ?>"
                                        data-column_name="ali_order">
                                    </td>
                                </tr>
								<?php
								$order_items_html .= ob_get_clean();
							}
						}
						if ( $order_items_html ) {
							ob_start();
							?>
                            <div class="<?php echo esc_attr( self::set( 'order-container' ) ) ?>"
                                 id="<?php echo esc_attr( self::set( 'order-id-' . $order_id ) ) ?>"
                                 data-order_id="<?php echo esc_attr( $order_id ); ?>"
                                 data-order_currency="<?php echo esc_attr( $order_currency ); ?>">
                                <div class="vi-ui form">
                                    <table class="vi-ui celled table">
                                        <thead>
                                        <tr>
                                            <th colspan="9">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <div class="<?php echo esc_attr( self::set( 'order-name' ) ) ?>">
															<?php
															$buyer = '';
															if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
																/* translators: 1: first name 2: last name */
																$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $order->get_billing_first_name(), $order->get_billing_last_name() ) );
															} elseif ( $order->get_billing_company() ) {
																$buyer = trim( $order->get_billing_company() );
															} elseif ( $order->get_customer_id() ) {
																$user  = get_user_by( 'id', $order->get_customer_id() );
																$buyer = ucwords( $user->display_name );
															}
															/**
															 * Filter buyer name in list table orders.
															 *
															 * @param string $buyer Buyer name.
															 * @param WC_Order $order Order data.
															 *
															 * @since 3.7.0
															 *
															 */
															$buyer = apply_filters( 'woocommerce_admin_order_buyer_name', $buyer, $order );
															if ( $order->get_status() === 'trash' ) {
																echo '<strong>#' . esc_attr( $order->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong>';
															} else {
																echo '<a target="_blank" href="' . esc_url( admin_url( 'post.php?post=' . absint( $order->get_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $order->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong></a>';
															}
															?>
                                                        </div>
                                                    </div>
                                                    <div class="field">
                                                        <div class="<?php echo esc_attr( self::set( 'order-date' ) ) ?>">
															<?php
															$order_timestamp = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : '';
															if ( ! $order_timestamp ) {
																echo '&ndash;';
															} else {
																// Check if the order was created within the last 24 hours, and not in the future.
																if ( $order_timestamp > strtotime( '-1 day', time() ) && $order_timestamp <= time() ) {
																	$show_date = sprintf(
																	/* translators: %s: human-readable time difference */
																		_x( '%s ago', '%s = human-readable time difference', 'woocommerce' ),
																		human_time_diff( $order->get_date_created()->getTimestamp(), time() )
																	);
																} else {
																	$show_date = $order->get_date_created()->date_i18n( apply_filters( 'woocommerce_admin_order_date_format', __( 'M j, Y', 'woocommerce' ) ) );
																}
																?>
                                                                <strong><?php esc_html_e( 'Created: ', 'woocommerce-alidropship' ) ?></strong>
																<?php
																printf(
																	'<time datetime="%1$s" title="%2$s">%3$s</time>',
																	esc_attr( $order->get_date_created()->date( 'c' ) ),
																	esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
																	esc_html( $show_date )
																);
															}
															?>
                                                        </div>
                                                    </div>
													<?php
													$order_status   = $order->get_status();
													$order_statuses = wc_get_order_statuses();
													?>
                                                    <div class="field">
                                                        <div class="<?php echo esc_attr( self::set( array(
															'order-status',
															"order-status-{$order_status}"
														) ) ) ?>">
                                                            <strong><?php esc_html_e( 'Status: ', 'woocommerce-alidropship' ) ?></strong><span
                                                                    class="vi-ui mini button <?php echo esc_attr( self::set( 'order-status-text' ) ) ?>"><?php echo isset( $order_statuses[ $order_status ] ) ? $order_statuses[ $order_status ] : ucwords( $order_status ) ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="field">
                                                        <div class="<?php echo esc_attr( self::set( 'order-shipping-address' ) ) ?>">
															<?php
															$shipping_address = $order->get_address( 'shipping' );
															if ( empty( $shipping_address['country'] ) ) {
																$shipping_address = $order->get_address( 'billing' );
															}
															$countries         = WC()->countries->get_countries();
															$formatted_address = WC()->countries->get_formatted_address( $shipping_address, ', ' );
															?>
                                                            <strong><?php esc_html_e( 'Ship to: ', 'woocommerce-alidropship' ) ?></strong>
                                                            <span class="<?php echo esc_attr( self::set( 'order-ship-to' ) ) ?>"
                                                                  title="<?php echo esc_attr( $formatted_address ) ?>"><?php echo isset( $countries[ $shipping_address['country'] ] ) ? $countries[ $shipping_address['country'] ] : $formatted_address ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th rowspan="2"
                                                class="<?php echo esc_attr( self::set( 'order-table-column-head' ) ) ?>"
                                                data-column_name="check"><input
                                                        class="<?php echo esc_attr( self::set( 'order-check-all' ) ) ?>"
                                                        type="checkbox" <?php if ( $placeable_items_count < 1 ) {
													echo esc_attr( 'disabled' );
												} ?>></th>
                                            <th class="<?php echo esc_attr( self::set( 'order-table-column-head' ) ) ?>"
                                                colspan="2"
                                                rowspan="2"
                                                data-column_name="name"><?php esc_html_e( 'Item detail', 'woocommerce-alidropship' ) ?></th>
                                            <th colspan="2"
                                                class="<?php echo esc_attr( self::set( 'order-table-column-head' ) ) ?>"
                                                data-column_name="subtotal"><?php esc_html_e( 'Income', 'woocommerce-alidropship' ) ?></th>
                                            <th colspan="3"
                                                class="<?php echo esc_attr( self::set( 'order-table-column-head' ) ) ?>"
                                                data-column_name="cost"><?php esc_html_e( 'Cost', 'woocommerce-alidropship' ) ?></th>
                                            <th rowspan="2"
                                                class="<?php echo esc_attr( self::set( 'order-table-column-head' ) ) ?>"
                                                data-column_name="ali_order"><?php esc_html_e( 'Ali order', 'woocommerce-alidropship' ) ?></th>
                                        </tr>
                                        <tr>
                                            <th class="<?php echo esc_attr( self::set( array(
												'order-table-column-head',
												'ignore-border-first-child'
											) ) ) ?>"
                                                data-column_name="subtotal"><?php esc_html_e( 'Subtotal', 'woocommerce-alidropship' ) ?></th>
                                            <th class="<?php echo esc_attr( self::set( 'order-table-column-head' ) ) ?>"
                                                data-column_name="subtotal"><?php esc_html_e( 'Shipping', 'woocommerce-alidropship' ) ?></th>
                                            <th class="<?php echo esc_attr( self::set( 'order-table-column-head' ) ) ?>"
                                                data-column_name="subtotal"><?php esc_html_e( 'Item price', 'woocommerce-alidropship' ) ?></th>
                                            <th class="<?php echo esc_attr( self::set( 'order-table-column-head' ) ) ?>"
                                                data-column_name="quantity"><?php esc_html_e( 'Qty', 'woocommerce-alidropship' ) ?></th>
                                            <th class="<?php echo esc_attr( self::set( 'order-table-column-head' ) ) ?>"
                                                data-column_name="shipping"><?php esc_html_e( 'Shipping', 'woocommerce-alidropship' ) ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php
										echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $order_items_html );
										$order_total        = $order->get_total();
										$woo_shipping_total = $order->get_shipping_total() + $order->get_shipping_tax();
										?>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th rowspan="2" colspan="3">
                                                <div class="<?php echo esc_attr( self::set( 'order-actions' ) ) ?>">
													<?php
													if ( $ali_pid ) {
														?>
                                                        <a href="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_to_order_aliexpress_url( $order_id, $ali_pid ) ); ?>"
                                                           target="_blank"
                                                           class="vi-ui labeled icon button positive mini <?php echo esc_attr( self::set( array(
															   'order-with-extension',
															   'hidden'
														   ) ) ) ?>"><i
                                                                    class="icon external"></i>
															<?php esc_html_e( 'Order with Extension', 'woocommerce-alidropship' ); ?>
                                                        </a>
                                                        <a target="_blank"
                                                           href="https://downloads.villatheme.com/?download=alidropship-extension"
                                                           title="<?php esc_attr_e( 'To fulfill this order manually, please install the chrome extension', 'woocommerce-alidropship' ) ?>"
                                                           class="vi-ui positive button labeled icon mini <?php echo esc_attr( self::set( 'download-chrome-extension' ) ) ?>"><i
                                                                    class="external icon"></i><?php esc_html_e( 'Install Extension', 'woocommerce-alidropship' ) ?>
                                                        </a>
														<?php
													}
													?>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="<?php echo esc_attr( self::set( 'order-product-subtotal-sum' ) ) ?>">
                                                            <span class="<?php echo esc_attr( self::set( 'order-product-subtotal-sum' ) ) ?>"><?php echo wc_price( $woo_line_total, array(
		                                                            'currency' => $order_currency,
	                                                            ) ) ?></span>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="<?php echo esc_attr( self::set( 'order-product-shipping-sum' ) ) ?>">
                                                            <span class="<?php echo esc_attr( self::set( 'order-product-shipping-sum' ) ) ?>"><?php echo wc_price( $woo_shipping_total, array(
		                                                            'currency' => $order_currency,
	                                                            ) ) ?></span>
                                                </div>
                                            </th>
                                            <th colspan="2">
                                                <div class="<?php echo esc_attr( self::set( 'order-product-cost' ) ) ?>">
                                                    <span class="<?php echo esc_attr( self::set( 'order-product-cost-amount' ) ) ?>"><?php echo self::wc_price( $product_cost ) ?></span>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="<?php echo esc_attr( self::set( 'order-shipping-total' ) ) ?>">
                                                    <span class="<?php echo esc_attr( self::set( 'order-shipping-total-amount' ) ) ?>"><?php echo self::wc_price( $shipping_total ) ?></span>
                                                </div>
                                            </th>
                                            <th rowspan="2">
                                            </th>
                                        </tr>
                                        <tr>
                                            <th colspan="2"
                                                class="<?php echo esc_attr( self::set( 'ignore-border-first-child' ) ) ?>">
                                                <div class="<?php echo esc_attr( self::set( 'order-product-subtotal' ) ) ?>">
                                                            <span class="<?php echo esc_attr( self::set( 'order-product-subtotal' ) ) ?>"><?php echo wc_price( $order_total, array(
		                                                            'currency' => $order_currency,
	                                                            ) ) ?></span>
                                                </div>
                                            </th>
                                            <th colspan="3">
                                                <div class="<?php echo esc_attr( self::set( 'order-total-cost' ) ) ?>">
                                                    <strong><?php esc_html_e( 'Total cost: ', 'woocommerce-alidropship' ) ?></strong><span
                                                            class="<?php echo esc_attr( self::set( 'order-total-cost-amount' ) ) ?>"><?php echo self::wc_price( $product_cost + $shipping_total ) ?></span>
                                                </div>
                                            </th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="<?php echo esc_attr( self::set( array(
									'order-overlay',
									'hidden'
								) ) ) ?>"></div>
                            </div>
							<?php
							$page_content .= ob_get_clean();
						}

						$key ++;
					}

				}
			}
		} else {
			ob_start();
			?>
            <form method="get" class="vi-ui segment">
                <input type="hidden" name="page" value="woocommerce-alidropship-ali-orders">
                <input type="search" class="text short" name="vi_wad_search"
                       placeholder="<?php esc_attr_e( 'Search order', 'woocommerce-alidropship' ) ?>"
                       value="<?php echo esc_attr( $keyword ) ?>">
                <input type="submit" name="submit" class="button"
                       value="<?php echo esc_attr( 'Search order', 'woocommerce-alidropship' ) ?>">
                <p>
					<?php esc_html_e( 'No orders found', 'woocommerce-alidropship' ) ?>
                </p>
            </form>
			<?php
			$pagination_html = ob_get_clean();
		}
		wp_reset_postdata();
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'AliExpress orders', 'woocommerce-alidropship' ) ?><a
                        class="vi-ui labeled icon button mini <?php echo esc_attr( self::set( 'aliexpress-sync' ) ) ?>"
                        target="_blank"
                        title="<?php esc_attr_e( 'Sync orders with AliExpress using chrome extension', 'woocommerce-alidropship' ) ?>"
                        href="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_get_tracking_url() ) ?>"><i
                            class="icon external"></i><?php esc_html_e( 'AliExpress sync', 'woocommerce-alidropship' ) ?>
                </a></h2>
			<?php
			echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $pagination_html );
			?>
            <div class="vi-ui segment <?php echo esc_attr( self::set( 'orders-container' ) ) ?>">
                <div class="vi-ui positive message">
                    <div><?php esc_html_e( 'Bulk orders functionality uses the official API of AliExpress which only accepts unaccented latin characters in shipping address so please use the chrome extension to fulfill orders whose address(including customer\'s name) contains:', 'woocommerce-alidropship' ) ?></div>
                    <ul class="list">
                        <li><?php esc_html_e( 'Non-latin characters', 'woocommerce-alidropship' ) ?></li>
                        <li><?php esc_html_e( 'Accented words that affect the address if their accents are removed', 'woocommerce-alidropship' ) ?></li>
                    </ul>
                </div>
				<?php
				if ( ! $access_token ) {
					?>
                    <div class="vi-ui negative message"><?php esc_html_e( 'You cannot bulk place orders because access token is missing.', 'woocommerce-alidropship' ) ?>
                        <a class="vi-ui button positive mini" href="admin.php?page=woocommerce-alidropship#/update"
                           target="_blank"><?php esc_html_e( 'Get access token', 'woocommerce-alidropship' ) ?></a>
                    </div>
					<?php
				} else {
					foreach ( $access_tokens as $at_k => $at_v ) {
						if ( $at_v['access_token'] === $access_token ) {
							if ( $at_v['expire_time'] / 1000 < time() ) {
								?>
                                <div class="vi-ui negative message"><?php esc_html_e( 'Your access token expires', 'woocommerce-alidropship' ) ?>
                                    <a class="vi-ui button positive mini"
                                       href="admin.php?page=woocommerce-alidropship#/update"
                                       target="_blank"><?php esc_html_e( 'Get access token', 'woocommerce-alidropship' ) ?></a>
                                </div>
								<?php
							}
							break;
						}
					}
				}
				?>
                <div class="vi-ui menu tabular">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-alidropship-ali-orders&order_status=to_order' ) ) ?>"
                       class="item <?php if ( self::$order_status === 'to_order' ) {
						   echo esc_attr( 'active' );
					   } ?>">
						<?php esc_html_e( 'To order', 'woocommerce-alidropship' ) ?>
                        <div class="vi-ui label"><?php echo esc_html( self::$order_status === 'to_order' ? ( $keyword ? VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_orders() : $count ) : VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_orders() ) ?></div>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-alidropship-ali-orders&order_status=all' ) ) ?>"
                       class="item <?php if ( self::$order_status !== 'to_order' ) {
						   echo esc_attr( 'active' );
					   } ?>">
						<?php esc_html_e( 'All orders', 'woocommerce-alidropship' ) ?>
                        <div class="vi-ui label"><?php echo esc_html( self::$order_status === 'to_order' ? VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_orders( true, 'all' ) : ( $keyword ? VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_orders( true, 'all' ) : $count ) ) ?></div>
                    </a>
                </div>
				<?php
				if ( $page_content ) {
					echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $page_content );
				}
				?>
            </div>
        </div>
		<?php
	}

	/**
	 *
	 */
	public function screen_options_page() {
		add_screen_option( 'per_page', array(
			'label'   => esc_html__( 'Number of items per page', 'wp-admin' ),
			'default' => 5,
			'option'  => 'vi_wad_ali_orders_per_page'
		) );
	}

	/**
	 * @param $name
	 * @param bool $set_name
	 *
	 * @return string
	 */
	private static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_ALIDROPSHIP_DATA::set( $name, $set_name );
	}

	public function filter_where( $where, $wp_q ) {
		global $wpdb;
		$where .= " AND vi_wad_woocommerce_order_itemmeta.meta_key='_vi_wad_aliexpress_order_id'";
		if ( self::$order_status === 'to_order' ) {
			$where .= " AND vi_wad_woocommerce_order_itemmeta.meta_value=''";
		}
		add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
		add_filter( 'posts_distinct', array( $this, 'posts_distinct' ), 10, 2 );

		return $where;
	}

	public function posts_join( $join, $wp_query ) {
		global $wpdb;
		$join .= " JOIN {$wpdb->prefix}woocommerce_order_items as vi_wad_woocommerce_order_items ON $wpdb->posts.ID=vi_wad_woocommerce_order_items.order_id";
		$join .= " JOIN {$wpdb->prefix}woocommerce_order_itemmeta as vi_wad_woocommerce_order_itemmeta ON vi_wad_woocommerce_order_items.order_item_id=vi_wad_woocommerce_order_itemmeta.order_item_id";

		return $join;
	}


	public function posts_distinct( $join, $wp_query ) {
		return 'DISTINCT';
	}

	public static function wc_price( $price ) {
		return wc_price( $price, array(
			'currency'     => 'USD',
			'decimals'     => 2,
			'price_format' => '%1$s&nbsp;%2$s'
		) );
	}

	private static function log( $content, $log_level = 'notice' ) {
		VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Log::wc_log( $content, 'api-fulfill-order', $log_level );
	}
}