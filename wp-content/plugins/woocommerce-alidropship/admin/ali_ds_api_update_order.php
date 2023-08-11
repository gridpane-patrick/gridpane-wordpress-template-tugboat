<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update tracking numbers of AliExpress orders automatically
 *
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_DS_API_Update_Order
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_DS_API_Update_Order {
	protected static $settings;
	private static $get_data_to_update;

	public function __construct() {
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		add_action( 'init', array( $this, 'background_process' ) );
		add_action( 'vi_wad_auto_update_order', array( $this, 'auto_update_order' ) );
		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
	}

	/**
	 * Filter cron schedule based on settings, min 1 day
	 *
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public function cron_schedules( $schedules ) {
		$schedules['vi_wad_update_order_interval'] = array(
			'interval' => DAY_IN_SECONDS * absint( self::$settings->get_params( 'update_order_interval' ) ),
			'display'  => esc_html__( 'Auto update order', 'woocommerce-alidropship' ),
		);

		return $schedules;
	}

	/**
	 * Background process that uses AliExpress API to fetch latest orders data
	 */
	public function background_process() {
		self::$get_data_to_update = new VI_WOOCOMMERCE_ALIDROPSHIP_BACKGROUND_ALI_API_GET_ORDER_DATA();
	}

	/**
	 * Get all orders that have AliExpress order ID but do not have tracking numbers, push to queue to sync in the background
	 * Each queue contains 20 orders because AliExpress API supports a maximum of 20 orders per request
	 *
	 * @throws Exception
	 */
	public function auto_update_order() {
		global $wpdb;
		vi_wad_set_time_limit();
		if ( ! empty( $_REQUEST['crontrol-single-event'] ) ) {
			/*Do not run if manually triggered by WP Crontrol plugin*/
			return;
		}
		$access_token = self::$settings->get_params( 'access_token' );
		if ( self::$settings->get_params( 'update_order_auto' ) ) {
			if ( $access_token ) {
				if ( ! self::$get_data_to_update->is_process_running() && self::$get_data_to_update->is_queue_empty() ) {
					set_transient( 'vi_wad_auto_update_order_time', time() );
					$woocommerce_order_itemmeta = $wpdb->prefix . "woocommerce_order_itemmeta";
					$woocommerce_order_items    = $wpdb->prefix . "woocommerce_order_items";
					$query                      = "SELECT * FROM {$woocommerce_order_items} as vi_wad_wc_order_items JOIN  {$woocommerce_order_itemmeta} AS vi_wad_wc_order_itemmeta ON vi_wad_wc_order_items.order_item_id=vi_wad_wc_order_itemmeta.order_item_id WHERE vi_wad_wc_order_itemmeta.meta_key='_vi_wad_aliexpress_order_id' AND vi_wad_wc_order_itemmeta.meta_value!=''";
					$results                    = $wpdb->get_results( $query, ARRAY_A );
					if ( count( $results ) ) {
						$dispatch = false;
						$ids      = array();
						foreach ( $results as $result ) {
							$item_tracking_data = wc_get_order_item_meta( $result['order_item_id'], '_vi_wot_order_item_tracking_data', true );
							if ( $item_tracking_data ) {
								$item_tracking_data = vi_wad_json_decode( $item_tracking_data );
								if ( is_array( $item_tracking_data ) ) {
									$count = count( $item_tracking_data );
									if ( $count === 0 || empty( $item_tracking_data[ $count - 1 ]['tracking_number'] ) ) {
										$ids[] = array(
											'order_id'      => $result['order_id'],
											'order_item_id' => $result['order_item_id'],
											'ali_id'        => $result['meta_value'],
										);
									}
								}
							} else {
								$ids[] = array(
									'order_id'      => $result['order_id'],
									'order_item_id' => $result['order_item_id'],
									'ali_id'        => $result['meta_value'],
								);
							}
							if ( count( $ids ) === 20 ) {
								self::$get_data_to_update->push_to_queue( $ids );
								$dispatch = true;
								$ids      = array();
							}
						}
						if ( count( $ids ) ) {
							self::$get_data_to_update->push_to_queue( $ids );
							$dispatch = true;
						}
						if ( $dispatch ) {
							self::$get_data_to_update->save()->dispatch();
						} else {
							self::log( 'Cron: get order tracking number, no orders found',WC_Log_Levels::NOTICE );
						}
					} else {
						self::log( 'Cron: get order tracking number, no orders found' ,WC_Log_Levels::NOTICE);
					}
				}
			} else {
				self::log( 'Missing access token' );
			}
		} else {
			$args = self::$settings->get_params();
			wp_unschedule_hook( 'vi_wad_auto_update_order' );
			$args['update_order_auto'] = '';
			update_option( 'wooaliexpressdropship_params', $args );
		}
	}

	/**
	 * @param $content
	 * @param string $log_level
	 */
	private static function log( $content, $log_level = 'alert' ) {
		VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Log::wc_log( $content, 'api-orders-sync', $log_level );
	}
}
