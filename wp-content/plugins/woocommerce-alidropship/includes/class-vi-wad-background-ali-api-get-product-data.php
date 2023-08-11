<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_ALIDROPSHIP_BACKGROUND_ALI_API_GET_PRODUCT_DATA extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'vi_wad_ali_api_get_product_data';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		if ( is_array( $item ) && count( $item ) ) {
			try {
				$settings         = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
				$access_token     = $settings->get_params( 'access_token' );
				$if_not_available = $settings->get_params( 'update_product_if_not_available' );
				if ( $access_token ) {
					vi_wad_set_time_limit();
					$public_params = array(
						'app_key'     => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
						'format'      => 'json',
						'method'      => '',
						'partner_id'  => 'apidoc',
						'session'     => $access_token,
						'sign_method' => 'md5',
						'timestamp'   => '',
						'v'           => '2.0',
					);
					$params        = array();
					if ( empty( $item[0]['recheck'] ) ) {
						$api_type                = 'get_product_v2';
						$public_params['method'] = 'aliexpress.ds.product.get';
						foreach ( $item as $value ) {
							$param         = array(
								'product_id'      => $value['ali_id'],
								'target_currency' => 'USD',
							);
							$shipping_info = get_post_meta( $value['id'], '_vi_wad_shipping_info', true );
							if ( $shipping_info && ! empty( $shipping_info['country'] ) ) {
								if ( $shipping_info['country'] !== 'US' ) {//a lot of products have different IDs for US and the API returns empty data
									//if product_id is for US version, ship_to_country will cause API to return empty data
									$param['ship_to_country'] = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_API::filter_country( $shipping_info['country'] );
								}
							}
							$params[] = $param;
						}
					} else {
						$api_type                = 'get_product';
						$public_params['method'] = 'aliexpress.postproduct.redefining.findaeproductbyidfordropshipper';
						foreach ( $item as $value ) {
							$param         = array(
								'product_id' => $value['ali_id'],
							);
							$shipping_info = get_post_meta( $value['id'], '_vi_wad_shipping_info', true );
							if ( $shipping_info && ! empty( $shipping_info['country'] ) ) {
								$param['local_country']  = $shipping_info['country'];
								$ship_to                 = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_country_locale( $shipping_info['country'] );
								$param['local_language'] = $ship_to ? $ship_to : 'en';
							}
							$params[] = $param;
						}
					}
					$get_sign = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_get_sign( array(
						'download_key' => $settings->get_params( 'key' ),
						'access_token' => $access_token,
						'app_key'      => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
						'site_url'     => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_domain_name(),
						'data'         => json_encode( $params ),
					), $api_type );
					if ( $get_sign['status'] === 'success' ) {
						$public_params['sign']      = $get_sign['data']['data'];
						$public_params['timestamp'] = date( 'Y-m-d H:i:s', $get_sign['data']['timestamp'] );
						$url                        = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_get_url( true, ! $settings->get_params( 'update_product_http_only' ) );
						$url                        = add_query_arg( array_map( 'urlencode', $public_params ), $url );
						$separator                  = urlencode( '{villatheme}' );
						add_filter( 'http_request_timeout', array(
							'VI_WOOCOMMERCE_ALIDROPSHIP_DATA',
							'bump_request_timeout'
						), PHP_INT_MAX );
						$request = wp_remote_post( $url, array(
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
							if ( count( $responses ) === count( $item ) ) {
								/*There may be products that the new API does not return data but they are not offline. Remember and recheck with the old API after the current queue is processed*/
								$recheck = get_option( 'vi_wad_recheck_products' );
								if ( $recheck ) {
									$recheck = vi_wad_json_decode( $recheck );
								}
								if ( ! is_array( $recheck ) ) {
									$recheck = array();
								}
								$update_recheck = false;
								foreach ( $responses as $key => $response ) {
									$data     = vi_wad_json_decode( $response );
									$res_key  = str_replace( '.', '_', $public_params['method'] ) . '_response';
									$view_url = admin_url( "admin.php?page=woocommerce-alidropship-imported-list&vi_wad_search_woo_id={$item[ $key ]['woo_id']}" );
									$ali_url  = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $item[ $key ]['ali_id'] );
									$log      = "Product <a href='{$view_url}' target='_blank'>#{$item[ $key ]['woo_id']}</a>(Ali ID <a href='{$ali_url}' target='_blank'>{$item[ $key ]['ali_id']}</a>): ";
									if ( isset( $data[ $res_key ] ) ) {
										if ( $api_type === 'get_product_v2' ) {
											if ( isset( $data[ $res_key ]['rsp_code'] ) && $data[ $res_key ]['rsp_code'] == 200 ) {
												if ( $data[ $res_key ]['result'] ) {
													VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_DS_API_Update_Product::update_product_by_id( array(
														'id'     => $item[ $key ]['id'],
														'woo_id' => $item[ $key ]['woo_id'],
														'ali_id' => $item[ $key ]['ali_id']
													), $data[ $res_key ]['result'] );
												} else {
													//Need to recheck with the old API
													$recheck[]      = $item[ $key ];
													$update_recheck = true;
												}
											} else {
												if ( isset( $data[ $res_key ]['rsp_code'] ) ) {
													if ( $data[ $res_key ]['rsp_code'] == 404 ) {
														//Need to recheck with the old API
														$recheck[]      = $item[ $key ];
														$update_recheck = true;
													} elseif ( $data[ $res_key ]['rsp_code'] == 10004000 ) {
														self::handle_offline_product( $log, $if_not_available, $item[ $key ] );
													}
												} else {
													self::log( "{$log}Invalid data" );
												}
											}
										} else {
											$result = $data[ $res_key ]['result'];
											if ( isset( $result['error_message'] ) ) {
												if ( isset( $result['error_code'] ) && ( $result['error_code'] == 10004000 || $result['error_code'] == - 99999 ) ) {
													self::handle_offline_product( $log, $if_not_available, $item[ $key ] );
												} else {
													self::log( "{$log}" . json_encode( $result ) );
												}
											} elseif ( isset( $result['error_response'] ) ) {
												self::log( "{$log}{$result['error_response']['code']} - {$result['error_response']['msg']}" );
											} else {
												VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_DS_API_Update_Product::update_product_by_id( array(
													'id'     => $item[ $key ]['id'],
													'woo_id' => $item[ $key ]['woo_id'],
													'ali_id' => $item[ $key ]['ali_id']
												), $result );
											}
										}
									} elseif ( isset( $data['error_response'] ) ) {
										self::log( "{$log}{$data['error_response']['code']} - {$data['error_response']['msg']}" );
									}
								}
								if ( $update_recheck ) {
									update_option( 'vi_wad_recheck_products', json_encode( $recheck ) );
								}
							} else {
								self::log( "Responses not matching: " . json_encode( $item ) . json_encode( $responses ) );
							}
						} else {
							self::log( "Error syncing products: {$request->get_error_message()}, " . json_encode( $item ) );
						}
					} else {
						self::log( "Error syncing products: {$get_sign['code']} - {$get_sign['data']}" );
						self::kill_process();
					}
				} else {
					self::log( 'Missing access token' );
					self::kill_process();
				}
			} catch ( Error $e ) {
				self::log( 'Uncaught error: ' . $e->getMessage() . ' on ' . $e->getFile() . ':' . $e->getLine() );

				return false;
			} catch ( Exception $e ) {
				self::log( 'Can not get product data to sync: ' . $e->getMessage() );

				return false;
			}
		} else {
			self::log( 'Invalid data' );
		}

		return false;
	}

	/**
	 * If a product is offline: add to log, maybe change status and send notification email to admin
	 *
	 * @param $log
	 * @param $if_not_available
	 * @param $item_data
	 */
	private static function handle_offline_product( $log, $if_not_available, $item_data ) {
		$log    = "{$log}This product is no longer available";
		$update = array(
			'time'             => time(),
			'hide'             => '',
			'is_offline'       => true,
			'shipping_removed' => false,
			'not_available'    => array(),
			'out_of_stock'     => array(),
			'is_out_of_stock'  => false,
			'price_changes'    => array(),
		);
		update_post_meta( $item_data['id'], '_vi_wad_update_product_notice', $update );
		$woo_product = wc_get_product( $item_data['woo_id'] );
		if ( $woo_product ) {
			VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_DS_API_Update_Product::update_product_if( $woo_product, $if_not_available, $log );
			VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_DS_API_Update_Product::maybe_send_admin_email( $update, $log, $item_data );
		}
		self::log( $log );
	}

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_process_running() {
		return parent::is_process_running();
	}

	/**
	 * Is the queue empty
	 *
	 * @return boolean
	 */
	public function is_queue_empty() {
		return parent::is_queue_empty();
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		// Show notice to user or perform some other arbitrary task...
		parent::complete();
		if ( $this->is_queue_empty() && ! $this->is_process_running() ) {
			/*After current queue is processed, handle products that need rechecking*/
			$recheck = get_option( 'vi_wad_recheck_products' );
			if ( $recheck ) {
				$recheck = vi_wad_json_decode( $recheck );
				if ( ! json_last_error() ) {
					$ids = array();
					foreach ( $recheck as $value ) {
						$value['recheck'] = 1;
						$ids[]            = $value;
						if ( count( $ids ) === 20 ) {
							VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_DS_API_Update_Product::$get_data_to_update->push_to_queue( $ids );
							$ids = array();
						}
					}
					if ( count( $ids ) ) {
						VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_DS_API_Update_Product::$get_data_to_update->push_to_queue( $ids );
					}
					VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_DS_API_Update_Product::$get_data_to_update->save()->dispatch();
				}
				delete_option( 'vi_wad_recheck_products' );
			}
		}
	}

	/**
	 * Delete all batches.
	 *
	 * @return VI_WOOCOMMERCE_ALIDROPSHIP_BACKGROUND_ALI_API_GET_PRODUCT_DATA
	 */
	public function delete_all_batches() {
		global $wpdb;

		$table  = $wpdb->options;
		$column = 'option_name';

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		}

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE {$column} LIKE %s", $key ) ); // @codingStandardsIgnoreLine.

		return $this;
	}

	/**
	 * Kill process.
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public function kill_process() {
		if ( ! $this->is_queue_empty() ) {
			$this->delete_all_batches();
			wp_clear_scheduled_hook( $this->cron_hook_identifier );
		}
	}

	/**
	 * @param $content
	 * @param string $log_level
	 */
	private static function log( $content, $log_level = 'alert' ) {
		VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Log::wc_log( $content, 'api-products-sync', $log_level );
	}
}