<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get orders data using AliExpress API, update tracking numbers and maybe change order status
 *
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_BACKGROUND_ALI_API_GET_ORDER_DATA
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_BACKGROUND_ALI_API_GET_ORDER_DATA extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'vi_wad_ali_api_get_order_data';

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
				$settings     = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
				$access_token = $settings->get_params( 'access_token' );
				if ( $access_token ) {
					vi_wad_set_time_limit();
					$public_params = array(
						'app_key'     => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
						'format'      => 'json',
						'method'      => 'aliexpress.trade.ds.order.get',
						'partner_id'  => 'apidoc',
						'session'     => $access_token,
						'sign_method' => 'md5',
						'timestamp'   => '',
						'v'           => '2.0',
					);
					$params        = array();
					foreach ( $item as $value ) {
						$params[] = array( 'single_order_query' => json_encode( array( 'order_id' => $value['ali_id'] ) ) );
					}
					$get_sign = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_get_sign( array(
						'download_key' => $settings->get_params( 'key' ),
						'access_token' => $access_token,
						'app_key'      => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
						'site_url'     => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_domain_name(),
						'data'         => json_encode( $params ),
					), 'get_order' );
					if ( $get_sign['status'] === 'success' ) {
						$public_params['sign']      = $get_sign['data']['data'];
						$public_params['timestamp'] = date( 'Y-m-d H:i:s', $get_sign['data']['timestamp'] );
						$url                        = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_get_url( true );
						$url                        = add_query_arg( array_map( 'urlencode', $public_params ), $url );
						$separator                  = urlencode( '{villatheme}' );
						add_filter( 'http_request_timeout', array(
							'VI_WOOCOMMERCE_ALIDROPSHIP_DATA',
							'bump_request_timeout'
						), PHP_INT_MAX );
						$request = wp_remote_post( $url, array(
							'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',
							'headers' => array(
								'Content-Type'      => 'text/plain;charset=UTF-8',
								'top-api-separator' => $separator,
							),
							'body'    => $get_sign['data']['payload'],
							'timeout' => 60,
						) );
						if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
							if ( strpos( $request['body'], $separator ) === false ) {
								$responses = array( $request['body'] );
							} else {
								$responses = explode( $separator, $request['body'] );
							}
							if ( count( $responses ) === count( $item ) ) {
								$order_item_ids = array();
								foreach ( $responses as $key => $response ) {
									$data     = vi_wad_json_decode( $response );
									$res_key  = str_replace( '.', '_', $public_params['method'] ) . '_response';
									$ali_url  = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_order_detail_url( $item[ $key ]['ali_id'] );
									$view_url = admin_url( "post.php?post={$item[ $key ]['order_id']}&action=edit" );
									$log      = "Order <a href='{$view_url}' target='_blank'>#{$item[ $key ]['order_id']}</a>(Ali ID <a href='{$ali_url}' target='_blank'>{$item[ $key ]['ali_id']}</a>): ";
									if ( isset( $data[ $res_key ] ) ) {
										$result = $data[ $res_key ]['result'];
										if ( isset( $result['error_message'] ) ) {
											self::log( "{$log}{$result['error_code']} - {$result['error_message']}" );
										} elseif ( isset( $result['error_response'] ) ) {
											self::log( "{$log}{$result['error_response']['code']} - {$result['error_response']['msg']}" );
										} else {
											$order_item_id    = $item[ $key ]['order_item_id'];
											$order_item_ids[] = $order_item_id;
											if ( isset( $result['logistics_info_list'] ) && isset( $result['logistics_info_list']['aeop_order_logistics_info'] ) && is_array( $result['logistics_info_list']['aeop_order_logistics_info'] ) && count( $result['logistics_info_list']['aeop_order_logistics_info'] ) ) {
												$latest_tracking = array_pop( $result['logistics_info_list']['aeop_order_logistics_info'] );
												if ( ! empty( $latest_tracking['logistics_no'] ) ) {
													$tracking_number = $latest_tracking['logistics_no'];
													self::log( "{$log}Tracking number updated {$tracking_number}" ,WC_Log_Levels::INFO);
													$tracking_logisticsType = $latest_tracking['logistics_service'];
													$old_tracking_data      = $current_tracking_data = array(
														'tracking_number' => '',
														'carrier_slug'    => '',
														'carrier_url'     => '',
														'carrier_name'    => '',
														'carrier_type'    => '',
														'time'            => time(),
													);
													$item_tracking_data     = wc_get_order_item_meta( $order_item_id, '_vi_wot_order_item_tracking_data', true );
													if ( $item_tracking_data ) {
														$item_tracking_data = vi_wad_json_decode( $item_tracking_data );
														$old_tracking_data  = $item_tracking_data[ ( count( $item_tracking_data ) - 1 ) ];
														foreach ( $item_tracking_data as $order_tracking_data_k => $order_tracking_data_v ) {
															if ( $order_tracking_data_v['tracking_number'] == $tracking_number ) {
																$current_tracking_data = $order_tracking_data_v;
																unset( $item_tracking_data[ $order_tracking_data_k ] );
																break;
															}
														}
														$item_tracking_data = array_values( $item_tracking_data );
													} else {
														$item_tracking_data = array();
													}
													$current_tracking_data['tracking_number'] = $tracking_number;
													$found_carrier                            = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_API::get_orders_tracking_carrier( '', '', false, $tracking_logisticsType );
													if ( count( $found_carrier ) ) {
														$current_tracking_data['carrier_slug'] = $found_carrier['slug'];
														$current_tracking_data['carrier_url']  = $found_carrier['url'];
														$current_tracking_data['carrier_name'] = $found_carrier['name'];
													} else {
														$current_tracking_data['carrier_url']  = '';
														$current_tracking_data['carrier_name'] = '';
													}
													$item_tracking_data[] = $current_tracking_data;
													wc_update_order_item_meta( $order_item_id, '_vi_wot_order_item_tracking_data', json_encode( $item_tracking_data ) );
													$status_switch_to_shipped = false;
													if ( isset( $result['logistics_status'] ) && strtolower( trim( $result['logistics_status'] ) ) === 'buyer_accept_goods' ) {
														if ( wc_get_order_item_meta( $order_item_id, '_vi_wad_aliexpress_order_item_status' ) !== 'shipped' ) {
															$status_switch_to_shipped = true;
															wc_update_order_item_meta( $order_item_id, '_vi_wad_aliexpress_order_item_status', 'shipped' );
														}
													}
													do_action( 'vi_wad_sync_aliexpress_order_tracking_info', $current_tracking_data, $old_tracking_data, $status_switch_to_shipped, $order_item_id, $item[ $key ]['order_id'] );
												} else {
													self::log( "{$log}Tracking number is not available",WC_Log_Levels::INFO );
												}
											} else {
												self::log( "{$log}Tracking number is not available",WC_Log_Levels::INFO );
											}

											if ( ! empty( $result['order_amount'] ) ) {
												$order_amount = $result['order_amount'];
												if ( $order_amount['currency_code'] === 'USD' && isset( $order_amount['amount'] ) ) {
													VI_WOOCOMMERCE_ALIDROPSHIP_Ali_Orders_Info_Table::insert( $item[ $key ]['ali_id'], $order_amount['currency_code'], $order_amount['amount'] );
												}
											}
										}
										if ( count( $order_item_ids ) ) {
											VI_WOOCOMMERCE_ALIDROPSHIP_Admin_API::change_order_status( $order_item_ids );
										}
									} elseif ( isset( $data['error_response'] ) ) {
										self::log( "{$log}{$data['error_response']['code']} - {$data['error_response']['msg']}" );
									}
								}
							} else {
								self::log( "Responses not matching: " . json_encode( $item ) );
							}
						} else {
							self::log( "Error syncing orders: {$request->get_error_message()}, " . json_encode( $item ) );
						}
					} else {
						self::log( "Error getting signature: {$get_sign['code']} - {$get_sign['data']}, " . json_encode( $item ) );
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
				self::log( 'Can not get orders track info: ' . $e->getMessage() );

				return false;
			}
		} else {
			self::log( 'Invalid data' );
		}

		return false;
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
	}

	/**
	 * Delete all batches.
	 *
	 * @return VI_WOOCOMMERCE_ALIDROPSHIP_BACKGROUND_ALI_API_GET_ORDER_DATA
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

	private static function log( $content, $log_level = 'alert' ) {
		VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Log::wc_log( $content, 'api-orders-sync', $log_level );
	}
}