<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings {
	private static $settings;
	private static $orders_tracking_active;
	private static $decimals;
	protected static $update_product_next_schedule;
	protected static $update_order_next_schedule;
	protected static $next_schedule;
	protected static $languages;
	protected static $default_language;
	protected static $languages_data;

	public function __construct() {
		self::$settings                     = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		self::$languages                    = array();
		self::$languages_data               = array();
		self::$default_language             = '';
		self::$orders_tracking_active       = false;
		self::$next_schedule                = wp_next_scheduled( 'vi_wad_auto_update_exchange_rate' );
		self::$update_product_next_schedule = wp_next_scheduled( 'vi_wad_auto_update_product' );
		self::$update_order_next_schedule   = wp_next_scheduled( 'vi_wad_auto_update_order' );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'check_update' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 999999 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_ajax_wad_search_product', array( $this, 'search_product' ) );
		add_action( 'wp_ajax_wad_search_cate', array( $this, 'search_cate' ) );
		add_action( 'wp_ajax_wad_search_tags', array( $this, 'search_tags' ) );
		add_action( 'wp_ajax_wad_format_price_rules_test', array( $this, 'format_price_rules_test' ) );
		add_action( 'wp_ajax_wad_get_product_attributes_mapping', array( $this, 'get_product_attributes_mapping' ) );
		add_action( 'wp_ajax_wad_get_shipping_company_mask', array( $this, 'get_shipping_company_mask' ) );
		add_action( 'wp_ajax_wad_get_exchange_rate', array( $this, 'get_exchange_rate_ajax' ) );
		add_action( 'wp_ajax_wad_save_access_token', array( $this, 'save_access_token' ) );
		add_action( 'wp_ajax_wad_remove_access_token', array( $this, 'remove_access_token' ) );
		add_action( 'wp_ajax_wad_get_custom_rule_html', array( $this, 'get_custom_rule_html' ) );
		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
		add_action( 'vi_wad_auto_update_exchange_rate', array( $this, 'auto_update_exchange_rate' ) );
	}

	/**
	 *
	 */
	public function get_custom_rule_html() {
		self::check_ajax_referer();
		ob_start();
		self::custom_rule_html( 0, VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_default_custom_rules() );
		$html = ob_get_clean();
		wp_send_json( array(
			'status' => 'success',
			'data'   => $html
		) );
	}

	/**
	 *
	 */
	public function check_update() {
		if ( class_exists( 'VillaTheme_Plugin_Check_Update' ) ) {
			$setting_url = admin_url( 'admin.php?page=woocommerce-alidropship' );
			$key         = self::$settings->get_params( 'key' );
			new VillaTheme_Plugin_Check_Update (
				VI_WOOCOMMERCE_ALIDROPSHIP_VERSION,                    // current version
				'https://villatheme.com/wp-json/downloads/v3',  // update path
				'woocommerce-alidropship/woocommerce-alidropship.php',                  // plugin file slug
				'woocommerce-alidropship', '43001', $key, $setting_url
			);
			new VillaTheme_Plugin_Updater( 'woocommerce-alidropship/woocommerce-alidropship.php', 'woocommerce-alidropship', $setting_url );
		}
	}

	/**
	 * Save access token to use with AliExpress API
	 */
	public function save_access_token() {
		self::check_ajax_referer();
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		$access_token = isset( $_POST['access_token'] ) ? array_map( 'sanitize_text_field', $_POST['access_token'] ) : array();
		$response     = array(
			'status'  => 'error',
			'message' => esc_html__( 'Invalid access token', 'woocommerce-alidropship' ),
			'data'    => '',
		);
		if ( count( $access_token ) && ! empty( $access_token['access_token'] ) ) {
			$response['status']  = 'success';
			$response['message'] = esc_html__( 'Successful', 'woocommerce-alidropship' );
			$access_tokens       = self::$settings->get_params( 'access_tokens' );
			if ( ! is_array( $access_tokens ) ) {
				$access_tokens = array();
			}
			foreach ( $access_tokens as $key => $value ) {
				if ( $access_token['user_nick'] === $value['user_nick'] ) {
					unset( $access_tokens[ $key ] );
				}
			}
			$args                  = self::$settings->get_params();
			$access_tokens[]       = $access_token;
			$args['access_tokens'] = array_values( $access_tokens );
			$args['access_token']  = $access_token['access_token'];
			update_option( 'wooaliexpressdropship_params', $args );
			ob_start();
			self::access_tokens_list( $access_tokens, $access_token['access_token'] );
			$response['data'] = ob_get_clean();
		}
		wp_send_json( $response );
	}

	/**
	 *
	 */
	public function remove_access_token() {
		self::check_ajax_referer();
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		$access_token = isset( $_POST['access_token'] ) ? sanitize_text_field( $_POST['access_token'] ) : '';
		$response     = array(
			'status'  => 'error',
			'message' => esc_html__( 'Invalid access token', 'woocommerce-alidropship' ),
			'data'    => '',
		);
		if ( $access_token ) {
			$success       = false;
			$access_tokens = self::$settings->get_params( 'access_tokens' );
			if ( is_array( $access_tokens ) && count( $access_tokens ) ) {
				foreach ( $access_tokens as $key => $value ) {
					if ( $access_token === $value['access_token'] ) {
						unset( $access_tokens[ $key ] );
						$args                  = self::$settings->get_params();
						$args['access_tokens'] = array_values( $access_tokens );
						if ( $args['access_token'] === $access_token ) {
							$args['access_token'] = '';
						}
						update_option( 'wooaliexpressdropship_params', $args );
						$success = true;
						break;
					}
				}
				if ( $success ) {
					$response['status']  = 'success';
					$response['message'] = esc_html__( 'Successful', 'woocommerce-alidropship' );
				}
			}
		}
		wp_send_json( $response );
	}

	/**
	 *
	 */
	public function auto_update_exchange_rate() {
		$exchange_rate_api = self::$settings->get_params( 'exchange_rate_api' );
		$args              = self::$settings->get_params();
		if ( self::$settings->get_params( 'exchange_rate_auto' ) && $exchange_rate_api ) {
			$update = false;
			$rate   = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_exchange_rate( $exchange_rate_api );
			if ( $rate ) {
				$args['import_currency_rate'] = $rate;
				$update                       = true;
			}
			foreach ( array( 'CNY', 'RUB' ) as $custom_currency ) {
				$custom_rate = self::$settings->get_params( "import_currency_rate_{$custom_currency}" );
				if ( $custom_rate ) {
					sleep( 1 );
					/*Only update this rate if it's previously set on purpose*/
					$rate = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_exchange_rate( $exchange_rate_api, 'USD', $custom_currency === 'CNY' ? 2 : 3, $custom_currency );
					if ( $rate ) {
						$args["import_currency_rate_{$custom_currency}"] = $rate;
						$update                                          = true;
					}
				}
			}
			if ( $update ) {
				update_option( 'wooaliexpressdropship_params', $args );
			}
		} else {
			$this->unschedule_event();
			$args['exchange_rate_auto'] = '';
			update_option( 'wooaliexpressdropship_params', $args );
		}
	}

	/**
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public function cron_schedules( $schedules ) {
		$schedules['vi_wad_exchange_rate_interval'] = array(
			'interval' => DAY_IN_SECONDS * absint( self::$settings->get_params( 'exchange_rate_interval' ) ),
			'display'  => esc_html__( 'Auto update exchange rate', 'woocommerce-alidropship' ),
		);

		return $schedules;
	}

	/**
	 * @param $decimals
	 *
	 * @return mixed
	 */
	public function change_decimals_for_ajax( $decimals ) {
		if ( self::$decimals !== null ) {
			$decimals = self::$decimals;
		}

		return $decimals;
	}

	/**
	 *
	 */
	public function get_exchange_rate_ajax() {
		self::check_ajax_referer();
		$api            = isset( $_GET['api'] ) ? sanitize_text_field( $_GET['api'] ) : '';
		$currency       = isset( $_GET['currency'] ) ? sanitize_text_field( $_GET['currency'] ) : 'USD';
		self::$decimals = isset( $_GET['decimals'] ) ? absint( sanitize_text_field( $_GET['decimals'] ) ) : 0;
		add_filter( 'wooaliexpressdropship_params_exchange_rate_decimals', array( $this, 'change_decimals_for_ajax' ) );
		$response = array(
			'status'  => 'error',
			'message' => '',
			'data'    => '',
		);
		if ( $api ) {
			if ( $currency === 'USD' ) {
				$get_exchange_rate = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_exchange_rate( $api );
			} else {
				$get_exchange_rate = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_exchange_rate( $api, 'USD', $currency === 'CNY' ? 2 : 3, $currency );
			}
			if ( $get_exchange_rate !== false ) {
				$response['status'] = 'success';
				$response['data']   = $get_exchange_rate;
			} else {
				$response['data'] = esc_html__( 'Can not get exchange rate', 'woocommerce-alidropship' );
			}
		} else {
			$response['message'] = esc_html__( 'Empty API', 'woocommerce-alidropship' );
		}
		wp_send_json( $response );
	}

	/**
	 * @param $attributes
	 */
	private static function sort_attributes( &$attributes ) {
		foreach ( $attributes as $attribute_slug => $attribute_values ) {
			sort( $attribute_values );
			$attributes[ $attribute_slug ] = array_values( $attribute_values );
		}
	}

	/**
	 * @param $attributes
	 */
	private static function htmlentities( &$attributes ) {
		foreach ( $attributes as $k => &$v ) {
			if ( is_array( $v ) ) {
				$v = array_map( 'htmlentities', $v );
			}
		}
	}

	/**
	 * Ajax handler for Attribute mapping list
	 */
	public function get_product_attributes_mapping() {
		self::check_ajax_referer();
		$attributes_list = get_transient( 'vi_wad_product_attributes_list' );
		$response        = array(
			'status'                         => 'success',
			'attributes_list'                => '{}',
			'attributes_list_html'           => '',
			'page'                           => 1,
			'percent'                        => 1,
			'attributes_mapping_origin'      => '[]',
			'attributes_mapping_replacement' => '[]',
		);
		if ( $attributes_list !== false ) {
			$response['attributes_mapping_origin'] = json_encode( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_origin(), JSON_UNESCAPED_UNICODE );
			$replacement                           = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_replacement();
			self::htmlentities( $replacement );
			$response['attributes_mapping_replacement'] = json_encode( $replacement, JSON_UNESCAPED_UNICODE );
			$attributes_list                            = vi_wad_json_decode( $attributes_list );
			self::sort_attributes( $attributes_list );
			$response['attributes_list'] = json_encode( $attributes_list, JSON_UNESCAPED_UNICODE );
//			ob_start();
//			self::attributes_list_html( $attributes_list );
//			$response['attributes_list_html'] = ob_get_clean();
		} else {
			$page            = isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1;
			$attributes_list = isset( $_POST['attributes_list'] ) ? sanitize_text_field( stripslashes( $_POST['attributes_list'] ) ) : '[]';
			$attributes_list = vi_wad_json_decode( $attributes_list );
			$args            = array(
				'post_type'      => 'vi_wad_draft_product',
				'paged'          => $page,
				'posts_per_page' => 50,
				'meta_key'       => '_vi_wad_sku',
				'post_status'    => array(
					'publish',
					'draft',
					'override'
				),
				'fields'         => 'ids'
			);

			$the_query = new WP_Query( $args );
			if ( $the_query->have_posts() ) {
				foreach ( $the_query->posts as $product_id ) {
					$attributes = get_post_meta( $product_id, '_vi_wad_attributes', true );
					foreach ( $attributes as $key => $attribute ) {
						if ( isset( $attribute['slug'] ) ) {
							if ( ! isset( $attributes_list[ $attribute['slug'] ] ) ) {
								$attributes_list[ $attribute['slug'] ] = array();
							}
							if ( is_array( $attribute['values'] ) ) {
								$attributes_list[ $attribute['slug'] ] = array_values( array_unique( array_merge( $attributes_list[ $attribute['slug'] ], array_map( array(
									'VI_WOOCOMMERCE_ALIDROPSHIP_DATA',
									'strtolower'
								), $attribute['values'] ) ) ) );
							}
						}
					}
				}
			}
			wp_reset_postdata();
			if ( $page < $the_query->max_num_pages ) {
				$response['attributes_list'] = json_encode( $attributes_list, JSON_UNESCAPED_UNICODE );
				$response['percent']         = intval( 100 * ( $page / $the_query->max_num_pages ) );
				$page ++;
			} else {
				self::sort_attributes( $attributes_list );
				$response['attributes_list']           = json_encode( $attributes_list, JSON_UNESCAPED_UNICODE );
				$response['attributes_mapping_origin'] = json_encode( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_origin(), JSON_UNESCAPED_UNICODE );
				$replacement                           = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_replacement();
				self::htmlentities( $replacement );
				$response['attributes_mapping_replacement'] = json_encode( $replacement, JSON_UNESCAPED_UNICODE );
				set_transient( 'vi_wad_product_attributes_list', $response['attributes_list'], 30 * DAY_IN_SECONDS );
//				ob_start();
//				self::attributes_list_html( $attributes_list );
//				$response['attributes_list_html'] = ob_get_clean();
				$response['percent'] = 100;
			}
			$response['page'] = $page;
		}
		wp_send_json( $response );
	}

	/**
	 * Ajax handler for shipping company mask
	 */
	public function get_shipping_company_mask() {
		self::check_ajax_referer();
		$response     = array(
			'status'                => 'success',
			'shipping_company_mask' => '',
			'page'                  => 1,
			'max_page'              => 1,
			'percent'               => 1,
		);
		$page         = isset( $_POST['page'] ) ? absint( sanitize_text_field( $_POST['page'] ) ) : 1;
		$max_page     = isset( $_POST['max_page'] ) ? absint( sanitize_text_field( $_POST['max_page'] ) ) : 1;
		$force_update = isset( $_POST['force_update'] ) ? sanitize_text_field( $_POST['force_update'] ) : '';
		$per_page     = 500;
		$company_mask = self::$settings->get_params( 'ali_shipping_company_mask' );
		if ( $company_mask ) {
			$company_mask = vi_wad_json_decode( $company_mask );
		}
		if ( ! is_array( $company_mask ) || ! count( $company_mask ) ) {
			$company_mask = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_default_masked_shipping_companies();
		}
		$now = time();
		if ( $page === 1 ) {
			if ( $force_update || self::$settings->get_params( 'ali_shipping_company_mask_time' ) < $now - DAY_IN_SECONDS ) {
				$count    = VI_WOOCOMMERCE_ALIDROPSHIP_Ali_Shipping_Info_Table::get_rows( 0, 0, true );
				$max_page = $count > 0 ? ceil( $count / $per_page ) : 1;
			} else {
				$response['page']     = $page;
				$response['percent']  = 100;
				$response['max_page'] = $max_page;
				ob_start();
				self::shipping_company_mask_html( $company_mask );
				$response['shipping_company_mask'] = ob_get_clean();
				wp_send_json( $response );
			}
		}
		$results = VI_WOOCOMMERCE_ALIDROPSHIP_Ali_Shipping_Info_Table::get_rows( $per_page, ( $page - 1 ) * $per_page, false );
		foreach ( $results as $result ) {
			$shipping_info = maybe_unserialize( $result['shipping_info'] );
			if ( isset( $shipping_info['freight'] ) ) {
				$freight = $shipping_info['freight'];
				if ( count( $freight ) ) {
					foreach ( $freight as $freight_ ) {
						if ( ! empty( $freight_['company'] ) && ! isset( $company_mask[ $freight_['company'] ] ) ) {
							$company_mask[ $freight_['company'] ] = array(
								'origin' => $freight_['company_name'],
								'new'    => ''
							);
						}
					}
				}
			}
		}
		$params                                   = self::$settings->get_params();
		$params['ali_shipping_company_mask']      = json_encode( $company_mask );
		$params['ali_shipping_company_mask_time'] = $now;
		update_option( 'wooaliexpressdropship_params', $params );
		$response['percent']  = intval( 100 * ( $page / $max_page ) );
		$response['max_page'] = $max_page;
		if ( $page < $max_page ) {
			$page ++;
		} else {
			ob_start();
			self::shipping_company_mask_html( $company_mask );
			$response['shipping_company_mask'] = ob_get_clean();
		}
		$response['page'] = $page;
		wp_send_json( $response );
	}

	/**
	 * @param $company_mask
	 */
	protected static function shipping_company_mask_html( $company_mask ) {
		uasort( $company_mask, 'VI_WOOCOMMERCE_ALIDROPSHIP_DATA::sort_by_column_origin' );
		foreach ( $company_mask as $key => $value ) {
			?>
            <tr>
                <td class="<?php echo self::set( 'shipping-company-mask-origin' ) ?>"><?php echo esc_html( $value['origin'] ) ?></td>
                <td><input type="text" name="<?php self::set_params( "shipping_company_mask_new[{$key}]" ) ?>"
                           class="<?php echo self::set( 'shipping-company-mask-new' ) ?>"
                           value="<?php echo esc_attr( htmlentities( $value['new'] ) ) ?>">
                </td>
            </tr>
			<?php
		}
	}

	/**
	 * Ajax handler for price rules test
	 */
	public function format_price_rules_test() {
		self::check_ajax_referer();
		global $wooaliexpressdropship_settings;
		$price                                                = isset( $_GET['format_price_rules_test'] ) ? sanitize_text_field( $_GET['format_price_rules_test'] ) : '';
		$format_price_rules                                   = isset( $_GET['format_price_rules'] ) ? stripslashes_deep( $_GET['format_price_rules'] ) : array();
		$wooaliexpressdropship_settings['format_price_rules'] = $format_price_rules;
		self::$settings                                       = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance( true );
		$applied                                              = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::format_price( $price );
		if ( count( $applied ) ) {
			$result = sprintf( esc_html__( '%s => Applied rule number: %s', 'woocommerce-alidropship' ), $price, implode( ',', array_map( array(
				__CLASS__,
				'increase_by_one'
			), $applied ) ) );
		} else {
			$result = sprintf( esc_html__( '%s => No rule matched', 'woocommerce-alidropship' ), $price );
		}
		wp_send_json( array( 'result' => $result, 'applied' => $applied ) );
	}

	public static function increase_by_one( $number ) {
		$number = intval( $number );
		$number ++;

		return $number;
	}

	/**
	 * Show error notice in admin dashboard about: permalink, ssl or expired access token
	 */
	public function admin_notices() {
		$errors              = array();
		$permalink_structure = get_option( 'permalink_structure' );
		if ( ! $permalink_structure ) {
			$errors[] = __( 'You are using Permalink structure as Plain. Please go to <a href="' . admin_url( 'options-permalink.php' ) . '" target="_blank">Permalink Settings</a> to change it.', 'woocommerce-alidropship' );
		}
		if ( ! is_ssl() ) {
			$errors[] = __( 'Your site is not using HTTPS. For more details, please read <a target="_blank" href="https://make.wordpress.org/support/user-manual/web-publishing/https-for-wordpress/">HTTPS for WordPress</a>', 'woocommerce-alidropship' );
		}
		$access_token  = self::$settings->get_params( 'access_token' );
		$access_tokens = self::$settings->get_params( 'access_tokens' );
		$page          = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if ( $access_token ) {
			foreach ( $access_tokens as $at_k => $at_v ) {
				if ( $at_v['access_token'] === $access_token ) {
					if ( $at_v['expire_time'] / 1000 < time() ) {
						$errors[] = sprintf( __( 'Your AliExpress access token expires, products/tracking numbers auto-updating and bulk AliExpress orders no longer work. <a class="vi-wad-get-access-token-shortcut" target="%s" href="%s">Get access token</a>', 'woocommerce-alidropship' ), $page === 'woocommerce-alidropship' ? '' : '_blank', admin_url( 'admin.php?page=woocommerce-alidropship#/update' ) );
					}
					break;
				}
			}
		}
		if ( count( $errors ) ) {
			?>
            <div class="error">
                <h3><?php echo _n( 'ALD - AliExpress Dropshipping and Fulfillment for WooCommerce:', 'AliExpress Dropshipping and Fulfillment for WooCommerce: you can not import products or fulfil AliExpress orders unless below issues are resolved', count( $errors ), 'woocommerce-alidropship' ); ?></h3>
				<?php
				foreach ( $errors as $error ) {
					?>
                    <p><?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $error ); ?></p>
					<?php
				}
				?>
            </div>
			<?php
		}
	}

	/**
	 * @param $name
	 * @param bool $set_name
	 *
	 * @return string|void
	 */
	private static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_ALIDROPSHIP_DATA::set( $name, $set_name );
	}

	/**
	 * Ajax tags search
	 */
	public function search_tags() {
		self::check_ajax_referer();
		$keyword    = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_tag',
				'orderby'    => 'name',
				'order'      => 'ASC',
				'search'     => $keyword,
				'hide_empty' => false
			)
		);
		$items      = array();
		$items[]    = array( 'id' => $keyword, 'text' => $keyword );
		if ( count( $categories ) ) {
			foreach ( $categories as $category ) {
				$items[] = array(
					'id'   => $category->name,
					'text' => $category->name
				);
			}
		}
		wp_send_json( $items );
	}

	/**
	 * Ajax products search
	 *
	 * @throws Exception
	 */
	public function search_product() {
		self::check_ajax_referer();
		$keyword              = isset( $_GET['keyword'] ) ? sanitize_text_field( $_GET['keyword'] ) : '';
		$exclude_ali_products = isset( $_GET['exclude_ali_products'] ) ? sanitize_text_field( $_GET['exclude_ali_products'] ) : '';
		if ( empty( $keyword ) ) {
			die();
		}
		$post_status = array( 'publish' );
		if ( current_user_can( 'edit_private_products' ) ) {
			if ( $exclude_ali_products ) {
				$post_status = array(
					'private',
					'draft',
					'pending',
					'publish'
				);
			} else {
				$post_status = array(
					'private',
					'publish'
				);
			}
		}
		$data_store = WC_Data_Store::load( 'product' );
		$ids        = $data_store->search_products( $keyword, '', true, true );
		$arg        = array(
			'post_type'      => 'product',
			'posts_per_page' => 50,
			'product_search' => true,
			'post_status'    => apply_filters( 'vi_wad_search_product_statuses', $post_status ),
			'fields'         => 'ids',
			'post__in'       => array_merge( $ids, array( 0 ) ),
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_vi_wad_aliexpress_product_id',
					'compare' => $exclude_ali_products ? 'NOT EXISTS' : 'EXISTS'
				)
			),
		);

		$the_query      = new WP_Query( apply_filters( 'vi_wad_ajax_search_products_query', $arg ) );
		$found_products = array();
		if ( $the_query->have_posts() ) {
			foreach ( $the_query->posts as $product_id ) {
				$found_products[] = array(
					'id'   => $product_id,
					'text' => "(#{$product_id}) " . get_the_title( $product_id )
				);
			}
		}
		wp_send_json( $found_products );
	}

	/**
	 * Ajax categories search
	 */
	public function search_cate() {
		self::check_ajax_referer();
		$keyword    = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'orderby'    => 'name',
				'order'      => 'ASC',
				'search'     => $keyword,
				'hide_empty' => false
			)
		);
		$items      = array();
		if ( count( $categories ) ) {
			foreach ( $categories as $category ) {
				$items[] = array(
					'id'   => $category->term_id,
					'text' => VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::build_category_name( $category->name, $category )
				);
			}
		}
		wp_send_json( $items );
	}

	/**
	 * Enqueue
	 */
	public function admin_enqueue_scripts() {
		$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		global $pagenow;
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-alidropship' ) {
			self::enqueue_3rd_library();
			wp_enqueue_style( 'woocommerce-alidropship-admin-style', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'admin.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_enqueue_script( 'woocommerce-alidropship-admin', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'admin.js', array(
				'jquery',
				'jquery-ui-sortable'
			), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			$decimals = wc_get_price_decimals();
			wp_localize_script( 'woocommerce-alidropship-admin', 'vi_wad_admin_settings_params', array(
				'decimals'                    => $decimals,
				'url'                         => admin_url( 'admin-ajax.php' ),
				'_vi_wad_ajax_nonce'          => self::create_ajax_nonce(),
				'i18n_error_max_digit'        => esc_html__( 'Maximum {value} digit', 'woocommerce-alidropship' ),
				'i18n_error_max_digits'       => esc_html__( 'Maximum {value} digits', 'woocommerce-alidropship' ),
				'i18n_error_digit_only'       => esc_html__( 'Numerical digit only', 'woocommerce-alidropship' ),
				'i18n_error_digit_and_x_only' => esc_html__( 'Numerical digit & X only', 'woocommerce-alidropship' ),
				'i18n_error_min_digits'       => esc_html__( 'Minimum 2 digits', 'woocommerce-alidropship' ),
				'i18n_error_min_max'          => esc_html__( 'Min can not > max', 'woocommerce-alidropship' ),
				'i18n_error_max_min'          => esc_html__( 'Max can not < min', 'woocommerce-alidropship' ),
				'i18n_error_max_decimals'     => sprintf( _n( 'Max decimal: %s', 'Max decimals: %s', $decimals, 'woocommerce-alidropship' ), '<a target="_blank" href="admin.php?page=wc-settings#woocommerce_price_num_decimals">' . $decimals . '</a>' ),
				'attributes_mapping_per_page' => self::$settings->get_params( 'attributes_mapping_per_page' ),
				'client_id'                   => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
			) );
		}
	}

	/**
	 * @param array $elements
	 * @param bool $exclude
	 */
	public static function enqueue_3rd_library( $elements = array(), $exclude = false ) {
		global $wp_scripts;
		$scripts         = $wp_scripts->registered;
		$exclude_dequeue = apply_filters( 'vi_wad_exclude_dequeue_scripts', array( 'dokan-vue-bootstrap' ) );
		foreach ( $scripts as $k => $script ) {
			if ( in_array( $script->handle, $exclude_dequeue ) ) {
				continue;
			}
			preg_match( '/bootstrap/i', $k, $result );
			if ( count( array_filter( $result ) ) ) {
				unset( $wp_scripts->registered[ $k ] );
				wp_dequeue_script( $script->handle );
			}
		}
		wp_dequeue_script( 'select-js' );//Causes select2 error, from ThemeHunk MegaMenu Plus plugin
		wp_dequeue_style( 'eopa-admin-css' );
		$all_elements = array(
			'accordion',
			'button',
			'checkbox',
			'dimmer',
			'divider',
			'dropdown',
			'form',
			'grid',
			'icon',
			'image',
			'input',
			'label',
			'loader',
			'menu',
			'message',
			'progress',
			'segment',
			'tab',
			'table',
			'select2',
			'step',
			'data-table',
			'sortable',
		);
		if ( ! count( $elements ) ) {
			$elements = $all_elements;
		} elseif ( $exclude ) {
			$elements = array_diff( $all_elements, $elements );
		}
		foreach ( $elements as $element ) {
			if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_CSS_DIR . "{$element}.min.css" ) ) {
				wp_enqueue_style( "woocommerce-alidropship-{$element}", VI_WOOCOMMERCE_ALIDROPSHIP_CSS . "{$element}.min.css" );
			} elseif ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_CSS_DIR . "{$element}.css" ) ) {
				wp_enqueue_style( "woocommerce-alidropship-{$element}", VI_WOOCOMMERCE_ALIDROPSHIP_CSS . "{$element}.css" );
			}
			if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_JS_DIR . "{$element}.min.js" ) ) {
				wp_enqueue_script( "woocommerce-alidropship-{$element}", VI_WOOCOMMERCE_ALIDROPSHIP_JS . "{$element}.min.js", array( 'jquery' ) );
			} elseif ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_JS_DIR . "{$element}.js" ) ) {
				wp_enqueue_script( "woocommerce-alidropship-{$element}", VI_WOOCOMMERCE_ALIDROPSHIP_JS . "{$element}.js", array( 'jquery' ) );
			}
		}
		if ( in_array( 'sortable', $elements ) ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
		}
		if ( in_array( 'data-table', $elements ) ) {
			wp_enqueue_script( 'jquery-data-table', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'jquery.dataTables.min.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_enqueue_script( 'semantic-ui-data-table', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'dataTables.semanticui.min.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
		}
		if ( in_array( 'select2', $elements ) ) {
			wp_enqueue_style( 'select2', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'select2.min.css' );
			if ( woocommerce_version_check( '3.0.0' ) ) {
				wp_enqueue_script( 'select2' );
			} else {
				wp_enqueue_script( 'select2-v4', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'select2.js', array( 'jquery' ), '4.0.3' );
			}
		}
		if ( in_array( 'dropdown', $elements ) ) {
			wp_enqueue_style( 'woocommerce-alidropship-transition', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'transition.min.css' );
			wp_enqueue_script( 'woocommerce-alidropship-transition', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'transition.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'woocommerce-alidropship-address', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'jquery.address-1.6.min.js', array( 'jquery' ) );
		}
	}

	/**
	 * Save settings
	 */
	public function save_settings() {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if ( $page === 'woocommerce-alidropship' ) {
			if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
				global $sitepress;
				$default_lang           = $sitepress->get_default_language();
				self::$default_language = $default_lang;
				$languages              = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );
				self::$languages_data   = $languages;
				if ( count( $languages ) ) {
					foreach ( $languages as $key => $language ) {
						if ( $key != $default_lang ) {
							self::$languages[] = $key;
						}
					}
				}
			} elseif ( class_exists( 'Polylang' ) ) {
				/*Polylang*/
				$languages    = pll_languages_list();
				$default_lang = pll_default_language( 'slug' );
				foreach ( $languages as $language ) {
					if ( $language == $default_lang ) {
						continue;
					}
					self::$languages[] = $language;
				}
			}
		}
		if ( is_plugin_active( 'woo-orders-tracking/woo-orders-tracking.php' ) || is_plugin_active( 'woocommerce-orders-tracking/woocommerce-orders-tracking.php' ) ) {
			self::$orders_tracking_active = true;
		}
		global $wooaliexpressdropship_settings;
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_menu_capability', 'manage_options', 'woocommerce-alidropship' ) ) ) {
			return;
		}
		if ( isset( $_POST['_wooaliexpressdropship_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['_wooaliexpressdropship_nonce'] ), 'wooaliexpressdropship_save_settings' ) ) {
			$args                           = self::$settings->get_params();
			$access_tokens                  = self::$settings->get_params( 'access_tokens' );
			$exchange_rate_shipping         = self::$settings->get_params( 'exchange_rate_shipping' );
			$shipping_company_mapping       = self::$settings->get_params( 'shipping_company_mapping' );
			$shipping_company_mask          = self::$settings->get_params( 'ali_shipping_company_mask' );
			$ali_shipping_company_mask_time = self::$settings->get_params( 'ali_shipping_company_mask_time' );
			$show_menu_count                = self::$settings->get_params( 'show_menu_count' );
			$amo                            = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_origin();
			$amr                            = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_replacement();
			if ( $shipping_company_mask ) {
				$shipping_company_mask = vi_wad_json_decode( $shipping_company_mask );
			}
			if ( isset( $_REQUEST['vi_wad_setup_wizard'] ) ) {
				/*Save settings for setup wizard*/
				foreach ( $args as $key => $arg ) {
					if ( isset( $_POST[ 'wad_' . $key ] ) ) {
						if ( is_array( $_POST[ 'wad_' . $key ] ) ) {
							$args[ $key ] = stripslashes_deep( $_POST[ 'wad_' . $key ] );
						} else if ( in_array( $key, array( 'fulfill_order_note' ) ) ) {
							$args[ $key ] = stripslashes( wp_kses_post( $_POST[ 'wad_' . $key ] ) );
						} else {
							$args[ $key ] = sanitize_text_field( stripslashes( $_POST[ 'wad_' . $key ] ) );
						}
					} elseif ( in_array( $key, array(
						'show_shipping_option',
						'shipping_cost_after_price_rules',
						'use_external_image',
						'use_global_attributes',
					) ) ) {
						$args[ $key ] = '';
					}
				}
			} else {
				foreach ( $args as $key => $arg ) {
					if ( isset( $_POST[ 'wad_' . $key ] ) ) {
						if ( is_array( $_POST[ 'wad_' . $key ] ) ) {
							$args[ $key ] = stripslashes_deep( $_POST[ 'wad_' . $key ] );
						} else if ( in_array( $key, array( 'fulfill_order_note' ) ) ) {
							$args[ $key ] = stripslashes( wp_kses_post( $_POST[ 'wad_' . $key ] ) );
						} else {
							$args[ $key ] = sanitize_text_field( stripslashes( $_POST[ 'wad_' . $key ] ) );
						}
					} else {
						if ( is_array( $arg ) ) {
							$args[ $key ] = array();
						} else {
							$args[ $key ] = '';
						}
					}
				}
			}
			/*Adjust custom rules*/
			$args['update_product_custom_rules'] = array_values( $args['update_product_custom_rules'] );
			foreach ( $args['update_product_custom_rules'] as &$custom_rule ) {
				$custom_rule = array_merge( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_default_custom_rules(), $custom_rule );
			}
			$args['shipping_company_mapping']    = array_merge( $shipping_company_mapping, $args['shipping_company_mapping'] );
			$args['attributes_mapping_per_page'] = isset( $_POST['vi-wad-attributes-mapping-table_length'] ) ? sanitize_text_field( $_POST['vi-wad-attributes-mapping-table_length'] ) : '';
			/*Adjust attributes mapping*/
			$attributes_mapping_origin      = vi_wad_json_decode( $args['attributes_mapping_origin'] );
			$attributes_mapping_replacement = vi_wad_json_decode( $args['attributes_mapping_replacement'] );
			$attributes_list                = get_transient( 'vi_wad_product_attributes_list' );
			if ( $attributes_list !== false ) {
				$attributes_list = vi_wad_json_decode( $attributes_list );
			} else {
				$attributes_list = array();
			}
			foreach ( $amo as $amo_key => $amo_value ) {
				if ( isset( $attributes_mapping_origin[ $amo_key ] ) ) {
					$diff = array_diff( $amo_value, $attributes_mapping_origin[ $amo_key ] );
					if ( count( $diff ) ) {
						foreach ( $diff as $diff_key => $diff_value ) {
							if ( ! isset( $attributes_list[ $amo_key ] ) || false === array_search( strtolower( $diff_value ), $attributes_list[ $amo_key ] ) ) {
								$attributes_mapping_origin[ $amo_key ][]      = $diff_value;
								$attributes_mapping_replacement[ $amo_key ][] = $amr[ $amo_key ][ $diff_key ];
							}
						}
					}
				} else {
					if ( ! isset( $attributes_list[ $amo_key ] ) ) {
						$attributes_mapping_origin[ $amo_key ]      = $amo_value;
						$attributes_mapping_replacement[ $amo_key ] = $amr[ $amo_key ];
					}
				}
			}
			$args['attributes_mapping_origin']      = json_encode( $attributes_mapping_origin, JSON_UNESCAPED_UNICODE );
			$args['attributes_mapping_replacement'] = json_encode( $attributes_mapping_replacement, JSON_UNESCAPED_UNICODE );
			/*Save WPML fields*/
			if ( count( self::$languages ) ) {
				foreach ( self::$languages as $key => $value ) {
					$args[ 'ali_shipping_option_text_' . $value ]                   = isset( $_POST[ 'wad_ali_shipping_option_text_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'wad_ali_shipping_option_text_' . $value ] ) ) : '';
					$args[ 'ali_shipping_label_' . $value ]                         = isset( $_POST[ 'wad_ali_shipping_label_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'wad_ali_shipping_label_' . $value ] ) ) : '';
					$args[ 'ali_shipping_label_free_' . $value ]                    = isset( $_POST[ 'wad_ali_shipping_label_free_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'wad_ali_shipping_label_free_' . $value ] ) ) : '';
					$args[ 'ali_shipping_select_variation_message_' . $value ]      = isset( $_POST[ 'wad_ali_shipping_select_variation_message_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'wad_ali_shipping_select_variation_message_' . $value ] ) ) : '';
					$args[ 'ali_shipping_product_text_' . $value ]                  = isset( $_POST[ 'wad_ali_shipping_product_text_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'wad_ali_shipping_product_text_' . $value ] ) ) : '';
					$args[ 'ali_shipping_product_not_available_message_' . $value ] = isset( $_POST[ 'wad_ali_shipping_product_not_available_message_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'wad_ali_shipping_product_not_available_message_' . $value ] ) ) : '';
					$args[ 'ali_shipping_not_available_message_' . $value ]         = isset( $_POST[ 'wad_ali_shipping_not_available_message_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'wad_ali_shipping_not_available_message_' . $value ] ) ) : '';
				}
			}
			/*Shipping company mask*/
			$args['ali_shipping_company_mask_time'] = $ali_shipping_company_mask_time;
			if ( isset( $_POST['wad_shipping_company_mask_new'] ) ) {
				foreach ( $shipping_company_mask as $shipping_company_mask_k => $shipping_company_mask_v ) {
					if ( isset( $_POST['wad_shipping_company_mask_new'][ $shipping_company_mask_k ] ) ) {
						$shipping_company_mask[ $shipping_company_mask_k ]['new'] = trim( stripslashes( sanitize_text_field( $_POST['wad_shipping_company_mask_new'][ $shipping_company_mask_k ] ) ) );
					}
				}
			}
			$args['ali_shipping_company_mask'] = json_encode( $shipping_company_mask );
			/*Format price rules*/
			$format_price_rules = array();
			if ( ! empty( $args['format_price_rules']['from'] ) && is_array( $args['format_price_rules']['from'] ) ) {
				for ( $i = 0; $i < count( $args['format_price_rules']['from'] ); $i ++ ) {
					$format_price_rules[] = array(
						'from'       => $args['format_price_rules']['from'][ $i ],
						'to'         => $args['format_price_rules']['to'][ $i ],
						'part'       => $args['format_price_rules']['part'][ $i ],
						'value_from' => $args['format_price_rules']['value_from'][ $i ],
						'value_to'   => $args['format_price_rules']['value_to'][ $i ],
						'value'      => $args['format_price_rules']['value'][ $i ],
					);
				}
				$args['format_price_rules'] = $format_price_rules;
			}
			/*String replace*/
			if ( ! empty( $args['string_replace']['from_string'] ) && is_array( $args['string_replace']['from_string'] ) ) {
				$strings          = $args['string_replace']['from_string'];
				$strings_replaces = array(
					'from_string' => array(),
					'to_string'   => array(),
					'sensitive'   => array(),
				);
				$count            = count( $strings );
				for ( $i = 0; $i < $count; $i ++ ) {
					if ( $strings[ $i ] !== '' ) {
						$strings_replaces['from_string'][] = $args['string_replace']['from_string'][ $i ];
						$strings_replaces['to_string'][]   = $args['string_replace']['to_string'][ $i ];
						$strings_replaces['sensitive'][]   = $args['string_replace']['sensitive'][ $i ];
					}
				}
				$args['string_replace'] = $strings_replaces;
			}
			/*Specification replace*/
			if ( ! empty( $args['specification_replace']['from_name'] ) && is_array( $args['specification_replace']['from_name'] ) ) {
				$strings          = $args['specification_replace']['from_name'];
				$strings_replaces = array(
					'from_name' => array(),
					'to_name'   => array(),
					'sensitive' => array(),
					'new_value' => array(),
				);
				$count            = count( $strings );
				for ( $i = 0; $i < $count; $i ++ ) {
					if ( $strings[ $i ] !== '' ) {
						$strings_replaces['from_name'][] = $args['specification_replace']['from_name'][ $i ];
						$strings_replaces['to_name'][]   = $args['specification_replace']['to_name'][ $i ];
						$strings_replaces['sensitive'][] = $args['specification_replace']['sensitive'][ $i ];
						$strings_replaces['new_value'][] = $args['specification_replace']['new_value'][ $i ];
					}
				}
				$args['specification_replace'] = $strings_replaces;
			}
			/*Carrier name replace*/
			if ( ! empty( $args['carrier_name_replaces']['from_string'] ) && is_array( $args['carrier_name_replaces']['from_string'] ) ) {
				$strings_replaces = array(
					'from_string' => array(),
					'to_string'   => array(),
					'sensitive'   => array(),
				);
				$count            = count( $args['carrier_name_replaces']['from_string'] );
				for ( $i = 0; $i < $count; $i ++ ) {
					if ( $args['carrier_name_replaces']['from_string'][ $i ] !== '' ) {
						$strings_replaces['from_string'][] = $args['carrier_name_replaces']['from_string'][ $i ];
						$strings_replaces['to_string'][]   = $args['carrier_name_replaces']['to_string'][ $i ];
						$strings_replaces['sensitive'][]   = $args['carrier_name_replaces']['sensitive'][ $i ];
					}
				}
				$args['carrier_name_replaces'] = $strings_replaces;
			}
			/*Carrier url replace*/
			if ( ! empty( $args['carrier_url_replaces']['from_string'] ) && is_array( $args['carrier_url_replaces']['from_string'] ) ) {
				$strings_replaces = array(
					'from_string' => array(),
					'to_string'   => array(),
				);
				$count            = count( $args['carrier_url_replaces']['from_string'] );
				for ( $i = 0; $i < $count; $i ++ ) {
					if ( $args['carrier_url_replaces']['from_string'][ $i ] !== '' && $args['carrier_url_replaces']['to_string'][ $i ] !== '' ) {
						$strings_replaces['from_string'][] = $args['carrier_url_replaces']['from_string'][ $i ];
						$url                               = $args['carrier_url_replaces']['to_string'][ $i ];
						$url                               = str_replace( '{tracking_number}', '___wot_tracking_number___', $url );
						$url                               = str_replace( '{postal_code}', '___wot_postal_code___', $url );
						$url                               = esc_url_raw( $url );
						$url                               = str_replace( '___wot_tracking_number___', '{tracking_number}', $url );
						$url                               = str_replace( '___wot_postal_code___', '{postal_code}', $url );
						$strings_replaces['to_string'][]   = $url;
					}
				}
				$args['carrier_url_replaces'] = $strings_replaces;
			}
			$args['exchange_rate_interval']  = absint( $args['exchange_rate_interval'] );
			$args['exchange_rate_hour']      = absint( $args['exchange_rate_hour'] );
			$args['exchange_rate_minute']    = absint( $args['exchange_rate_minute'] );
			$args['exchange_rate_second']    = absint( $args['exchange_rate_second'] );
			$args['update_product_interval'] = absint( $args['update_product_interval'] );
			$args['update_product_hour']     = absint( $args['update_product_hour'] );
			$args['update_product_minute']   = absint( $args['update_product_minute'] );
			$args['update_product_second']   = absint( $args['update_product_second'] );

			$args['update_order_interval'] = absint( $args['update_order_interval'] );
			$args['update_order_hour']     = absint( $args['update_order_hour'] );
			$args['update_order_minute']   = absint( $args['update_order_minute'] );
			$args['update_order_second']   = absint( $args['update_order_second'] );
			if ( empty( $args['import_currency_rate'] ) ) {
				$args['import_currency_rate'] = 1;
			}
			$args['import_currency_rate'] = abs( floatval( $args['import_currency_rate'] ) );
			$args['received_email']       = sanitize_email( $args['received_email'] );
			$args['product_sku']          = str_replace( ' ', '', $args['product_sku'] );
			/*access_tokens and exchange_rate_shipping cannot be edited by user*/
			$args['access_tokens']          = $access_tokens;
			$args['exchange_rate_shipping'] = $exchange_rate_shipping;

			$args['ali_shipping_not_available_time_min'] = intval( $args['ali_shipping_not_available_time_min'] );
			$args['ali_shipping_not_available_time_max'] = intval( $args['ali_shipping_not_available_time_max'] );
			if ( $args['ali_shipping_not_available_time_min'] > $args['ali_shipping_not_available_time_max'] ) {
				$args['ali_shipping_not_available_time_min'] = $args['ali_shipping_not_available_time_max'];
			}
			$update_product_old = array(
				'update_product_auto'     => self::$settings->get_params( 'update_product_auto' ),
				'update_product_interval' => self::$settings->get_params( 'update_product_interval' ),
				'update_product_hour'     => self::$settings->get_params( 'update_product_hour' ),
				'update_product_minute'   => self::$settings->get_params( 'update_product_minute' ),
				'update_product_second'   => self::$settings->get_params( 'update_product_second' ),
			);
			$update_order_old   = array(
				'update_order_auto'     => self::$settings->get_params( 'update_order_auto' ),
				'update_order_interval' => self::$settings->get_params( 'update_order_interval' ),
				'update_order_hour'     => self::$settings->get_params( 'update_order_hour' ),
				'update_order_minute'   => self::$settings->get_params( 'update_order_minute' ),
				'update_order_second'   => self::$settings->get_params( 'update_order_second' ),
			);
			$args               = apply_filters( 'vi_wad_save_plugin_settings_params', $args );
			if ( $args['exchange_rate_auto'] && ( ! self::$next_schedule || ! self::$settings->get_params( 'exchange_rate_auto' ) || $args['exchange_rate_interval'] != self::$settings->get_params( 'exchange_rate_interval' ) || $args['exchange_rate_hour'] != self::$settings->get_params( 'exchange_rate_hour' ) || $args['exchange_rate_minute'] != self::$settings->get_params( 'exchange_rate_minute' ) || $args['exchange_rate_second'] != self::$settings->get_params( 'exchange_rate_second' ) ) ) {
				$wooaliexpressdropship_settings = $args;
				$this->unschedule_event();
				$schedule_time = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_schedule_time_from_local_time( $args['exchange_rate_hour'], $args['exchange_rate_minute'], $args['exchange_rate_second'] );
				/*Call here to apply new interval to cron_schedules filter when calling method wp_schedule_event*/
				self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance( true );
				$schedule       = wp_schedule_event( $schedule_time, 'vi_wad_exchange_rate_interval', 'vi_wad_auto_update_exchange_rate' );

				if ( $schedule !== false ) {
					self::$next_schedule = $schedule_time;
				} else {
					self::$next_schedule = '';
				}
			} else {
				$wooaliexpressdropship_settings = $args;
				self::$settings                 = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance( true );
				if ( ! $args['exchange_rate_auto'] ) {
					$this->unschedule_event();
				}
			}
			if ( $args['update_product_auto'] && ( ! self::$update_product_next_schedule || ! $update_product_old['update_product_auto'] || $args['update_product_interval'] != $update_product_old['update_product_interval'] || $args['update_product_hour'] != $update_product_old['update_product_hour'] || $args['update_product_minute'] != $update_product_old['update_product_minute'] || $args['update_product_second'] != $update_product_old['update_product_second'] ) ) {
				$wooaliexpressdropship_settings = $args;
				$this->update_product_unschedule_event();
				$schedule_time = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_schedule_time_from_local_time( $args['update_product_hour'], $args['update_product_minute'], $args['update_product_second'] );
				$last_schedule = get_transient( 'vi_wad_auto_update_product_time' );
				if ( $last_schedule && ( $schedule_time - $last_schedule < DAY_IN_SECONDS * 0.5 ) ) {
					$schedule_time += DAY_IN_SECONDS;
				}
				/*Call here to apply new interval to cron_schedules filter when calling method wp_schedule_event*/
				self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance( true );
				$schedule       = wp_schedule_event( $schedule_time, 'vi_wad_update_product_interval', 'vi_wad_auto_update_product' );
				if ( $schedule !== false ) {
					self::$update_product_next_schedule = $schedule_time;
				} else {
					self::$update_product_next_schedule = '';
				}
			} else {
				$wooaliexpressdropship_settings = $args;
				self::$settings                 = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance( true );
				if ( ! $args['update_product_auto'] ) {
					$this->update_product_unschedule_event();
				}
			}
			if ( $args['update_order_auto'] && ( ! self::$update_order_next_schedule || ! $update_order_old['update_order_auto'] || $args['update_order_interval'] != $update_order_old['update_order_interval'] || $args['update_order_hour'] != $update_order_old['update_order_hour'] || $args['update_order_minute'] != $update_order_old['update_order_minute'] || $args['update_order_second'] != $update_order_old['update_order_second'] ) ) {
				$wooaliexpressdropship_settings = $args;
				$this->update_order_unschedule_event();
				$schedule_time = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_schedule_time_from_local_time( $args['update_order_hour'], $args['update_order_minute'], $args['update_order_second'] );
				$last_schedule = get_transient( 'vi_wad_auto_update_order_time' );
				if ( $last_schedule && ( $schedule_time - $last_schedule < DAY_IN_SECONDS * 0.5 ) ) {
					$schedule_time += DAY_IN_SECONDS;
				}
				/*Call here to apply new interval to cron_schedules filter when calling method wp_schedule_event*/
				self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance( true );
				$schedule       = wp_schedule_event( $schedule_time, 'vi_wad_update_order_interval', 'vi_wad_auto_update_order' );
				if ( $schedule !== false ) {
					self::$update_order_next_schedule = $schedule_time;
				} else {
					self::$update_order_next_schedule = '';
				}
			} else {
				$wooaliexpressdropship_settings = $args;
				self::$settings                 = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance( true );
				if ( ! $args['update_order_auto'] ) {
					$this->update_order_unschedule_event();
				}
			}
			$per_page = array(
				'per_page'              => 'import_list_per_page',
				'imported_per_page'     => 'imported_per_page',
				'error_images_per_page' => 'error_images_per_page',
				'ali_orders_per_page'   => 'ali_orders_per_page',
			);
			$user_id  = get_current_user_id();
			foreach ( $per_page as $per_page_k => $per_page_v ) {
				if ( ! empty( $_POST["wad_{$per_page_v}"] ) ) {
					update_user_meta( $user_id, "vi_wad_{$per_page_k}", $_POST["wad_{$per_page_v}"] );
				}
			}
			update_option( 'wooaliexpressdropship_params', $args );
			if ( isset( $_POST['vi_wad_check_key'] ) ) {
				delete_site_transient( 'update_plugins' );
				delete_transient( 'villatheme_item_43001' );
				delete_option( 'woocommerce-alidropship_messages' );
				do_action( 'villatheme_save_and_check_key_woocommerce-alidropship', self::$settings->get_params( 'key' ) );
			}
			if ( isset( $_POST['vi_wad_setup_redirect'] ) ) {
				$url_redirect = esc_url_raw( $_POST['vi_wad_setup_redirect'] );
				wp_safe_redirect( $url_redirect );
				exit;
			}
		}
	}

	public function unschedule_event() {
		if ( self::$next_schedule ) {
			wp_unschedule_hook( 'vi_wad_auto_update_exchange_rate' );
			self::$next_schedule = '';
		}
	}

	public function update_product_unschedule_event() {
		if ( self::$update_product_next_schedule ) {
			wp_unschedule_hook( 'vi_wad_auto_update_product' );
			self::$update_product_next_schedule = '';
		}
	}

	public function update_order_unschedule_event() {
		if ( self::$update_order_next_schedule ) {
			wp_unschedule_hook( 'vi_wad_auto_update_order' );
			self::$update_order_next_schedule = '';
		}
	}

	private static function stripslashes_deep( $value ) {
		if ( is_array( $value ) ) {
			$value = array_map( 'stripslashes_deep', $value );
		} else {
			$value = wp_kses_post( stripslashes( $value ) );
		}

		return $value;
	}

	/**
	 *
	 */
	public function page_callback() {
		self::settings_page_html( current_user_can( apply_filters( 'vi_wad_admin_access_full_settings_capability', apply_filters( 'vi_wad_admin_menu_capability', 'manage_options', 'woocommerce-alidropship' ) ) ) );
	}

	/**
	 * Main settings page content
	 *
	 * @param bool $is_main true = for admin
	 */
	public static function settings_page_html( $is_main = true ) {
		$shipping_companies = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_masked_shipping_companies();
		?>
        <div class="wrap woocommerce-alidropship">
            <h2><?php esc_html_e( 'ALD - AliExpress Dropshipping and Fulfillment for WooCommerce Settings', 'woocommerce-alidropship' ) ?></h2>
			<?php
			$messages = array();
			if ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_disable_wp_cron() ) {
				$messages[] = __( '<strong>DISABLE_WP_CRON</strong> is set to true, product images may not be downloaded properly. Please try option <strong>"Disable background process"</strong>', 'woocommerce-alidropship' );
			}
			if ( is_plugin_active( 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php' ) ) {
				foreach (
					array(
						'cpf_custom_meta_key',
						'billing_number_meta_key',
						'shipping_number_meta_key',
						'billing_neighborhood_meta_key',
						'shipping_neighborhood_meta_key'
					) as $br_custom_field
				) {
					if ( ! self::$settings->get_params( $br_custom_field ) ) {
						$messages[] = __( 'Some extra checkout fields are not configured which may lead to incorrect address of Brazilian customers when fulfilling AliExpress orders. Please go to <a href="#fulfill">Fulfill</a> tab to configure CPF, Billing/Shipping number and neighborhood meta fields. If you already use your own code to handle these custom fields, please ignore this warning.', 'woocommerce-alidropship' );
						break;
					}
				}
			}
			if ( $messages ) {
				?>
                <div class="vi-ui message negative">
                    <div class="header"><?php esc_html_e( 'ALD - Warning', 'woocommerce-alidropship' ) ?>:</div>
                    <ul class="list">
						<?php
						foreach ( $messages as $message ) {
							?>
                            <li><?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $message ) ?></li>
							<?php
						}
						?>
                    </ul>
                </div>
				<?php
			}
			?>
            <form method="post" action="" class="vi-ui form">
				<?php wp_nonce_field( $is_main ? 'wooaliexpressdropship_save_settings' : 'wooaliexpressdropship_save_settings_vendor', '_wooaliexpressdropship_nonce' ); ?>
                <div class="vi-ui attached tabular menu">
                    <div class="item active <?php self::set_params( 'tab-item', true ) ?>" data-tab="general">
						<?php esc_html_e( 'General', 'woocommerce-alidropship' ) ?>
                    </div>
                    <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="products">
						<?php esc_html_e( 'Products', 'woocommerce-alidropship' ) ?>
                    </div>
                    <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="price">
						<?php esc_html_e( 'Product Price', 'woocommerce-alidropship' ) ?>
                    </div>
					<?php
					if ( $is_main ) {
						?>
                        <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="attributes">
							<?php esc_html_e( 'Product Attributes', 'woocommerce-alidropship' ) ?>
                        </div>
                        <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="video">
							<?php esc_html_e( 'Product Video', 'woocommerce-alidropship' ) ?>
                        </div>
						<?php
					}
					?>
                    <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="product_update">
						<?php esc_html_e( 'Product Sync', 'woocommerce-alidropship' ) ?>
                    </div>
					<?php
					if ( $is_main ) {
						?>
                        <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="product_split">
							<?php esc_html_e( 'Product Splitting', 'woocommerce-alidropship' ) ?>
                        </div>
                        <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="override">
							<?php esc_html_e( 'Product Overriding', 'woocommerce-alidropship' ) ?>
                        </div>
                        <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="migration">
							<?php esc_html_e( 'Product Migration', 'woocommerce-alidropship' ) ?>
                        </div>
                        <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="fulfill">
							<?php esc_html_e( 'Fulfill', 'woocommerce-alidropship' ) ?>
                        </div>
                        <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="shipping">
							<?php esc_html_e( 'Frontend Shipping', 'woocommerce-alidropship' ) ?>
                        </div>
						<?php
						if ( self::$orders_tracking_active ) {
							?>
                            <div class="item" data-tab="tracking_carrier">
								<?php esc_html_e( 'Tracking Carrier', 'woocommerce-alidropship' ) ?>
                            </div>
							<?php
						}
						if ( class_exists( 'WeDevs_Dokan' ) ) {
							?>
                            <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="vendor">
								<?php esc_html_e( 'Vendor', 'woocommerce-alidropship' ) ?>
                            </div>
							<?php
						}
						?>
                        <div class="item <?php self::set_params( 'tab-item', true ) ?>" data-tab="update">
							<?php esc_html_e( 'Update', 'woocommerce-alidropship' ) ?>
                        </div>
						<?php
					}
					?>
                </div>
                <div class="vi-ui bottom attached tab segment active <?php self::set_params( 'tab-content', true ) ?>"
                     data-tab="general">
					<?php
					if ( $is_main ) {
						?>
                        <div class="vi-ui message positive">
                            <ul class="list">
                                <li><?php _e( 'Since version 1.0.2 of the Chrome extension, you can authenticate your extension using WooCommerce REST API authentication(recommended). To edit or revoke your APIs, please go to <a href="admin.php?page=wc-settings&tab=advanced&section=keys" target="_blank">WooCommerce settings/Advanced/REST API</a>', 'woocommerce-alidropship' ) ?></li>
                                <li><?php _e( 'Connecting with extension using secret key may be deprecated in an update in the near future.', 'woocommerce-alidropship' ) ?></li>
                            </ul>
                        </div>
						<?php
					}
					?>

                    <table class="form-table">
                        <tbody>
						<?php
						if ( $is_main ) {
							?>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'enable', true ) ?>">
										<?php esc_html_e( 'Enable', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'enable', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'enable' ), 1 ) ?>
                                               tabindex="0" class="<?php self::set_params( 'enable', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'enable' ) ?>"/>
                                        <label><?php esc_html_e( 'You need to enable this to let WooCommerce AliExpress Dropshipping Extension connect to your store', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'secret_key', true ) ?>"><?php esc_html_e( 'Secret key', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td class="vi-wad relative">
                                    <div class="vi-ui left labeled input fluid">
                                        <label class="vi-ui label">
                                            <div class="vi-wad-buttons-group">
                                            <span class="vi-wad-copy-secretkey"
                                                  title="<?php esc_attr_e( 'Copy Secret key', 'woocommerce-alidropship' ) ?>">
                                                <i class="dashicons dashicons-admin-page"></i>
                                            </span>
                                                <span class="vi-wad-generate-secretkey"
                                                      title="<?php esc_attr_e( 'Generate new key', 'woocommerce-alidropship' ) ?>">
                                                <i class="dashicons dashicons-image-rotate"></i>
                                            </span>
                                            </div>
                                        </label>
                                        <input type="text" name="<?php self::set_params( 'secret_key' ) ?>"
                                               value="<?php echo self::$settings->get_params( 'secret_key' ) ?>"
                                               id="<?php self::set_params( 'secret_key', true ) ?>"
                                               class="<?php self::set_params( 'secret_key', true ) ?>">
                                    </div>
                                    <p><?php esc_html_e( 'Secret key is one of the two ways to connect the chrome extension with your store. The other way is to use WooCommerce authentication.', 'woocommerce-alidropship' ) ?></p>
                                    <p class="vi-wad-connect-extension-desc vi-wad-hidden"><?php esc_html_e( 'To let the chrome extension connect with this store, please click the "Connect the Extension" button below.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                </th>
                                <td>
                                    <p>
                                        <a href="https://downloads.villatheme.com/?download=alidropship-extension"
                                           target="_blank">
											<?php esc_html_e( 'Add WooCommerce AliExpress Dropshipping Extension', 'woocommerce-alidropship' ); ?>
                                        </a>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <div class="vi-ui styled fluid accordion">
                                        <div class="title active"><i
                                                    class="dropdown icon"></i><?php esc_html_e( 'Install and connect the chrome extension', 'woocommerce-alidropship' ) ?>
                                        </div>
                                        <div class="content active" style="text-align: center">
                                            <iframe width="560" height="315"
                                                    src="https://www.youtube-nocookie.com/embed/eO_C_b4ZQmo"
                                                    title="YouTube video player" frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen></iframe>
                                        </div>
                                        <div class="title"><i
                                                    class="dropdown icon"></i><?php esc_html_e( 'How to use this plugin?', 'woocommerce-alidropship' ) ?>
                                        </div>
                                        <div class="content" style="text-align: center">
                                            <iframe width="560" height="315"
                                                    src="https://www.youtube-nocookie.com/embed/eCt8sJVsBXk"
                                                    frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen></iframe>
                                        </div>
                                    </div>
                                </td>
                            </tr>
							<?php
						}

						$user_id  = get_current_user_id();
						$per_page = get_user_meta( $user_id, 'vi_wad_per_page', true );
						if ( empty ( $per_page ) || $per_page < 1 ) {
							$per_page = 5;
						}
						$imported_per_page = get_user_meta( $user_id, 'vi_wad_imported_per_page', true );
						if ( empty ( $imported_per_page ) || $imported_per_page < 1 ) {
							$imported_per_page = 5;
						}
						?>
                        <tr>
                            <th>
                                <label><?php esc_html_e( 'Number of items per page', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <div class="equal width fields">
                                    <div class="field">
                                        <div class="vi-ui left labeled input fluid">
                                            <label class="vi-ui label"
                                                   for="<?php self::set_params( 'import_list_per_page', true ) ?>"><?php esc_html_e( 'Import list', 'woocommerce-alidropship' ) ?></label>
                                            <input type="number"
                                                   name="<?php self::set_params( 'import_list_per_page' ) ?>"
                                                   step="1"
                                                   min="1"
                                                   value="<?php echo esc_attr( $per_page ) ?>"
                                                   id="<?php self::set_params( 'import_list_per_page', true ) ?>"
                                                   class="<?php self::set_params( 'import_list_per_page', true ) ?>">
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="vi-ui left labeled input fluid">
                                            <label class="vi-ui label"
                                                   for="<?php self::set_params( 'imported_per_page', true ) ?>"><?php esc_html_e( 'Imported', 'woocommerce-alidropship' ) ?></label>
                                            <input type="number"
                                                   name="<?php self::set_params( 'imported_per_page' ) ?>"
                                                   step="1" min="1"
                                                   value="<?php echo esc_attr( $imported_per_page ) ?>"
                                                   id="<?php self::set_params( 'imported_per_page', true ) ?>"
                                                   class="<?php self::set_params( 'imported_per_page', true ) ?>">
                                        </div>
                                    </div>
									<?php
									if ( $is_main ) {
										$error_images_per_page = get_user_meta( $user_id, 'vi_wad_error_images_per_page', true );
										if ( empty ( $error_images_per_page ) || $error_images_per_page < 1 ) {
											$error_images_per_page = 5;
										}
										$ali_orders_per_page = get_user_meta( $user_id, 'vi_wad_ali_orders_per_page', true );
										if ( empty ( $ali_orders_per_page ) || $ali_orders_per_page < 1 ) {
											$ali_orders_per_page = 5;
										}
										?>
                                        <div class="field">
                                            <div class="vi-ui left labeled input fluid">
                                                <label class="vi-ui label"
                                                       for="<?php self::set_params( 'error_images_per_page', true ) ?>"><?php esc_html_e( 'Failed Images', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number"
                                                       name="<?php self::set_params( 'error_images_per_page' ) ?>"
                                                       step="1"
                                                       min="1"
                                                       value="<?php echo esc_attr( $error_images_per_page ) ?>"
                                                       id="<?php self::set_params( 'error_images_per_page', true ) ?>"
                                                       class="<?php self::set_params( 'error_images_per_page', true ) ?>">
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui left labeled input fluid">
                                                <label class="vi-ui label"
                                                       for="<?php self::set_params( 'ali_orders_per_page', true ) ?>"><?php esc_html_e( 'Ali Orders', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number"
                                                       name="<?php self::set_params( 'ali_orders_per_page' ) ?>"
                                                       step="1"
                                                       min="1"
                                                       value="<?php echo esc_attr( $ali_orders_per_page ) ?>"
                                                       id="<?php self::set_params( 'ali_orders_per_page', true ) ?>"
                                                       class="<?php self::set_params( 'ali_orders_per_page', true ) ?>">
                                            </div>
                                        </div>
										<?php
									}
									?>
                                </div>
                                <p><?php esc_html_e( 'If you increase the "Number of items per page" using in the Screen options on each page above too high and the page can not be fully loaded, you can use this option to decrease the value accordingly.', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
						<?php
						if ( $is_main ) {
							?>
                            <tr>
                                <th>
                                    <label><?php esc_html_e( 'Show menu count', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <select id="<?php self::set_params( 'show_menu_count', true ) ?>"
                                            class="vi-ui dropdown" multiple
                                            name="<?php self::set_params( 'show_menu_count', false, true ) ?>">
										<?php
										$show_menu_count = self::$settings->get_params( 'show_menu_count' );
										foreach (
											array(
												'import_list'   => esc_html__( 'Import List', 'woocommerce-alidropship' ),
												'imported'      => esc_html__( 'Imported', 'woocommerce-alidropship' ),
												'ali_orders'    => esc_html__( 'Ali Orders', 'woocommerce-alidropship' ),
												'failed_images' => esc_html__( 'Failed Images', 'woocommerce-alidropship' ),
											) as $option_k => $menu
										) {
											$selected = '';
											if ( in_array( $option_k, $show_menu_count ) ) {
												$selected = 'selected';
											}
											?>
                                            <option value="<?php echo esc_attr( $option_k ) ?>" <?php echo esc_attr( $selected ) ?>><?php echo esc_html( $menu ); ?></option>
											<?php
										}
										?>
                                    </select>
                                    <p><?php esc_html_e( 'Select elements that you want to show menu count for.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
							<?php
						}
						?>
                        </tbody>
                    </table>
                </div>

                <div class="vi-ui bottom attached tab segment <?php self::set_params( 'tab-content', true ) ?>"
                     data-tab="product_update">
					<?php
					if ( $is_main ) {
						if ( self::$update_product_next_schedule ) {
							$gmt_offset = intval( get_option( 'gmt_offset' ) );
							?>
                            <div class="vi-ui positive message">
								<?php
								printf( __( 'Next schedule: <strong>%s</strong>', 'woocommerce-alidropship' ), date_i18n( 'F j, Y g:i:s A', ( self::$update_product_next_schedule + HOUR_IN_SECONDS * $gmt_offset ) ) );
								?>
                            </div>
							<?php
						} else {
							?>
                            <div class="vi-ui negative message">
								<?php esc_html_e( 'Product auto-sync is currently DISABLED', 'woocommerce-alidropship' ); ?>
                            </div>
							<?php
						}
						if ( ! self::$settings->get_params( 'access_token' ) ) {
							?>
                            <div class="vi-ui message negative <?php self::set_params( 'update-product-message', true ) ?>">
								<?php esc_html_e( 'This function will not work because access token is missing.', 'woocommerce-alidropship' ) ?>
                                <a class="vi-ui button positive tiny" href="#/update"
                                   target="_self"><?php esc_html_e( 'Get access token', 'woocommerce-alidropship' ) ?></a>
                            </div>
							<?php
						}
						?>
                        <table class="form-table">
                            <tbody>
							<?php
							$update_product_auto          = self::$settings->get_params( 'update_product_auto' );
							$update_product_options_class = array( 'update-product-options' );
							if ( ! $update_product_auto ) {
								$update_product_options_class[] = 'hidden';
							}

							?>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'update_product_auto', true ) ?>"><?php esc_html_e( 'Enable product auto-sync', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'update_product_auto' ) ?>"
                                               id="<?php self::set_params( 'update_product_auto', true ) ?>"
                                               class="<?php self::set_params( 'update_product_auto', true ) ?>"
                                               value="1" <?php checked( $update_product_auto, 1 ) ?>>
                                        <label></label>
                                    </div>
                                </td>
                            </tr>
							<?php
							$update_product_interval = self::$settings->get_params( 'update_product_interval' );
							if ( intval( $update_product_interval ) < 1 ) {
								$update_product_interval = 1;
							}
							?>
                            <tr class="<?php echo esc_attr( self::set( $update_product_options_class ) ) ?>">
                                <th>
                                    <label for="<?php echo esc_attr( self::set( 'update_product_interval' ) ) ?>"><?php esc_html_e( 'Sync products every', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui right labeled input">
                                        <input type="number" min="1"
                                               name="<?php self::set_params( 'update_product_interval' ) ?>"
                                               id="<?php echo esc_attr( self::set( 'update_product_interval' ) ) ?>"
                                               value="<?php echo esc_attr( $update_product_interval ) ?>">
                                        <label for="<?php echo esc_attr( self::set( 'update_product_interval' ) ) ?>"
                                               class="vi-ui label"><?php esc_html_e( 'Day(s)', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( self::set( $update_product_options_class ) ) ?>">
                                <th>
                                    <label for="<?php echo esc_attr( self::set( 'update_product_hour' ) ) ?>"><?php esc_html_e( 'Sync products at', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <label for="<?php echo esc_attr( self::set( 'update_product_hour' ) ) ?>"
                                                       class="vi-ui label"><?php esc_html_e( 'Hour', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number" min="0" max="23"
                                                       name="<?php self::set_params( 'update_product_hour' ) ?>"
                                                       id="<?php echo esc_attr( self::set( 'update_product_hour' ) ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'update_product_hour' ) ) ?>">
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <label for="<?php echo esc_attr( self::set( 'update_product_minute' ) ) ?>"
                                                       class="vi-ui label"><?php esc_html_e( 'Minute', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number" min="0" max="59"
                                                       name="<?php self::set_params( 'update_product_minute' ) ?>"
                                                       id="<?php echo esc_attr( self::set( 'update_product_minute' ) ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'update_product_minute' ) ) ?>">
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <label for="<?php echo esc_attr( self::set( 'update_product_second' ) ) ?>"
                                                       class="vi-ui label"><?php esc_html_e( 'Second', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number" min="0" max="59"
                                                       name="<?php self::set_params( 'update_product_second' ) ?>"
                                                       id="<?php echo esc_attr( self::set( 'update_product_second' ) ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'update_product_second' ) ) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( self::set( $update_product_options_class ) ) ?>">
                                <th>
                                    <label for="<?php self::set_params( 'update_product_http_only', true ) ?>"><?php esc_html_e( 'Use HTTP service URL', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'update_product_http_only' ) ?>"
                                               id="<?php self::set_params( 'update_product_http_only', true ) ?>"
                                               class="<?php self::set_params( 'update_product_http_only', true ) ?>"
                                               value="1" <?php checked( self::$settings->get_params( 'update_product_http_only' ), 1 ) ?>>
                                        <label><?php esc_html_e( 'Enable this if your products are unable to be synced due to "Connection timed out" error. To check this, please go to Logs', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
						<?php
					}
					?>
                    <div class="vi-ui message positive">
						<?php esc_html_e( 'Configure what the plugin will do when you sync products both automatically via API and manually with the chrome extension', 'woocommerce-alidropship' ) ?>
                    </div>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'update_product_statuses', true ) ?>"><?php esc_html_e( 'Product status', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <select id="<?php self::set_params( 'update_product_statuses', true ) ?>"
                                        class="vi-ui dropdown" multiple
                                        name="<?php self::set_params( 'update_product_statuses', false, true ) ?>">
									<?php
									$product_statuses        = array(
										'publish' => esc_html__( 'Publish', 'woocommerce-alidropship' ),
										'pending' => esc_html__( 'Pending', 'woocommerce-alidropship' ),
										'draft'   => esc_html__( 'Draft', 'woocommerce-alidropship' ),
										'private' => esc_html__( 'Private', 'woocommerce-alidropship' ),
									);
									$update_product_statuses = self::$settings->get_params( 'update_product_statuses' );
									foreach ( $product_statuses as $option_k => $update_product_status ) {
										$selected = '';
										if ( in_array( $option_k, $update_product_statuses ) ) {
											$selected = 'selected';
										}
										?>
                                        <option value="<?php echo esc_attr( $option_k ) ?>" <?php echo esc_attr( $selected ) ?>><?php echo esc_html( $update_product_status ); ?></option>
										<?php
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'Only sync products with selected statuses. Leave empty to select all statuses.', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'update_product_attributes', true ) ?>"><?php esc_html_e( 'Sync attributes', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'update_product_attributes', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'update_product_attributes' ), 1 ) ?>
                                           tabindex="0"
                                           class="<?php self::set_params( 'update_product_attributes', true ) ?>"
                                           value="1"
                                           name="<?php self::set_params( 'update_product_attributes' ) ?>"/>
                                    <label><?php esc_html_e( 'Sync attributes of products by applying attribute mapping rules', 'woocommerce-alidropship' ) ?></label>
                                </div>
                                <p><?php _e( '<strong>Caution:</strong> This is a heavy task hence it may cause the sync slower. Therefore, you should only enable this when needed', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'update_product_price', true ) ?>"><?php esc_html_e( 'Sync price', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'update_product_price', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'update_product_price' ), 1 ) ?>
                                           tabindex="0"
                                           class="<?php self::set_params( 'update_product_price', true ) ?>"
                                           value="1"
                                           name="<?php self::set_params( 'update_product_price' ) ?>"/>
                                    <label><?php esc_html_e( 'Sync price of WooCommerce products with AliExpress. All rules in Product Price tab will be applied to new price.', 'woocommerce-alidropship' ) ?></label>
                                </div>
                            </td>
                        </tr>

                        <tr class="<?php self::set_params( 'update_product_price_dependency', true ) ?>">
                            <td colspan="2">
                                <div class="vi-ui segment <?php self::set_params( 'custom_price_rules_wrap', true ) ?>">
                                    <h4><?php esc_html_e( 'Custom pricing rules for syncing product price. If no rule matched, the original pricing rules in the tab "Product Price" will be used.', 'woocommerce-alidropship' ); ?></h4>
                                    <div class="<?php self::set_params( 'custom_price_rules_container', true ) ?>">
										<?php
										$custom_rules = self::$settings->get_params( 'update_product_custom_rules' );
										foreach ( $custom_rules as $custom_rule_id => $custom_rule ) {
											self::custom_rule_html( $custom_rule_id, $custom_rule );
										}
										?>
                                    </div>
                                    <div class="<?php self::set_params( 'custom_price_rule_add_container', true ) ?>"><span
                                                class="<?php self::set_params( 'custom_price_rule_add', true ) ?> vi-ui button labeled icon positive mini"
                                                title="<?php esc_attr_e( 'Add a custom price rule', 'woocommerce-alidropship' ) ?>"><i
                                                    class="icon add"></i><?php esc_html_e( 'Add custom rule', 'woocommerce-alidropship' ); ?></span>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr class="<?php self::set_params( 'update_product_price_dependency', true ) ?>">
                            <th>
                                <label for="<?php self::set_params( 'update_product_exclude_onsale', true ) ?>"><?php esc_html_e( 'Exclude on-sale products', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'update_product_exclude_onsale', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'update_product_exclude_onsale' ), 1 ) ?>
                                           tabindex="0"
                                           class="<?php self::set_params( 'update_product_exclude_onsale', true ) ?>"
                                           value="1"
                                           name="<?php self::set_params( 'update_product_exclude_onsale' ) ?>"/>
                                    <label><?php esc_html_e( 'Do not sync price if a product is on sale', 'woocommerce-alidropship' ) ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr class="<?php self::set_params( 'update_product_price_dependency', true ) ?>">
                            <th>
                                <label for="<?php self::set_params( 'update_product_exclude_products', true ) ?>"><?php esc_html_e( 'Exclude products', 'woocommerce-alidropship' ); ?></label>
                            </th>
                            <td>
                                <select name="<?php self::set_params( 'update_product_exclude_products', false, true ) ?>"
                                        class="<?php self::set_params( 'update_product_exclude_products', true ) ?> search-product"
                                        id="<?php self::set_params( 'update_product_exclude_products', true ) ?>"
                                        multiple="multiple">
									<?php
									$excl_products = self::$settings->get_params( 'update_product_exclude_products' );
									if ( is_array( $excl_products ) && count( $excl_products ) ) {
										foreach ( $excl_products as $excl_product_id ) {
											$excl_product = wc_get_product( $excl_product_id );
											if ( $excl_product ) {
												?>
                                                <option value="<?php echo esc_attr( $excl_product_id ) ?>"
                                                        selected><?php echo esc_html( $excl_product->get_name() ); ?></option>
												<?php
											}
										}
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'If you don\'t want to sync price of some specific products, enter them here', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr class="<?php self::set_params( 'update_product_price_dependency', true ) ?>">
                            <th>
                                <label for="<?php self::set_params( 'update_product_exclude_categories', true ) ?>"><?php esc_html_e( 'Exclude categories', 'woocommerce-alidropship' ); ?></label>
                            </th>
                            <td>
                                <select name="<?php self::set_params( 'update_product_exclude_categories', false, true ) ?>"
                                        class="<?php self::set_params( 'update_product_exclude_categories', true ) ?> search-category"
                                        id="<?php self::set_params( 'update_product_exclude_categories', true ) ?>"
                                        multiple="multiple">
									<?php
									$excl_categories = self::$settings->get_params( 'update_product_exclude_categories' );
									if ( is_array( $excl_categories ) && count( $excl_categories ) ) {
										foreach ( $excl_categories as $category_id ) {
											$category = get_term( $category_id );
											if ( $category ) {
												?>
                                                <option value="<?php echo esc_attr( $category_id ) ?>"
                                                        selected><?php echo esc_html( $category->name ); ?></option>
												<?php
											}
										}
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'If you don\'t want to sync price of products from some specific categories, enter them here', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
						<?php
						/*
						?>
						<tr>
							<th>
								<label for="<?php self::set_params( 'price_change_max', true ) ?>"><?php esc_html_e( 'Skip if change exceeds', 'woocommerce-alidropship' ) ?></label>
							</th>
							<td>
								<div class="vi-ui right labeled input">
									<input type="number" min="0"
										   name="<?php self::set_params( 'price_change_max' ) ?>"
										   id="<?php echo esc_attr( self::set( 'price_change_max' ) ) ?>"
										   value="<?php echo esc_attr( self::$settings->get_params( 'price_change_max' ) ) ?>">
									<label for="<?php echo esc_attr( self::set( 'price_change_max' ) ) ?>"
										   class="vi-ui label"><?php esc_html_e( '%', 'woocommerce-alidropship' ) ?></label>
								</div>
								<p><?php esc_html_e( 'Do not sync price if percentage of change in price exceeds this value. Leave 0 or empty to disable this feature.', 'woocommerce-alidropship' ) ?></p>
							</td>
						</tr>
						<?php
						*/
						?>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'update_product_quantity', true ) ?>"><?php esc_html_e( 'Sync quantity', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'update_product_quantity', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'update_product_quantity' ), 1 ) ?>
                                           tabindex="0"
                                           class="<?php self::set_params( 'update_product_quantity', true ) ?>"
                                           value="1"
                                           name="<?php self::set_params( 'update_product_quantity' ) ?>"/>
                                    <label><?php esc_html_e( 'Sync quantity of WooCommerce products with AliExpress', 'woocommerce-alidropship' ) ?></label>
                                </div>
                            </td>
                        </tr>
						<?php
						$if_out_of_stock   = self::$settings->get_params( 'update_product_if_out_of_stock' );
						$if_not_available  = self::$settings->get_params( 'update_product_if_not_available' );
						$if_shipping_error = self::$settings->get_params( 'update_product_if_shipping_error' );
						$removed_variation = self::$settings->get_params( 'update_product_removed_variation' );
						$product_statuses  = array(
							'outofstock' => esc_html__( 'Set product out-of-stock', 'woocommerce-alidropship' ),
							'draft'      => esc_html__( 'Change product status to Draft', 'woocommerce-alidropship' ),
							'pending'    => esc_html__( 'Change product status to Pending', 'woocommerce-alidropship' ),
							'private'    => esc_html__( 'Change product status to Private', 'woocommerce-alidropship' ),
							'trash'      => esc_html__( 'Trash product', 'woocommerce-alidropship' ),
						);
						?>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'update_product_if_out_of_stock', true ) ?>"><?php esc_html_e( 'If a product is out of stock', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <select id="<?php self::set_params( 'update_product_if_out_of_stock', true ) ?>"
                                        class="vi-ui dropdown"
                                        name="<?php self::set_params( 'update_product_if_out_of_stock' ) ?>">
                                    <option value=""><?php esc_html_e( 'Do nothing', 'woocommerce-alidropship' ) ?></option>
									<?php
									foreach ( $product_statuses as $option_k => $update_product_status ) {
										?>
                                        <option value="<?php echo esc_attr( $option_k ) ?>" <?php selected( $option_k, $if_out_of_stock ) ?>><?php echo esc_html( $update_product_status ); ?></option>
										<?php
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'Select an action when an AliExpress product is out-of-stock', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'update_product_if_not_available', true ) ?>"><?php esc_html_e( 'If a product is no longer available', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <select id="<?php self::set_params( 'update_product_if_not_available', true ) ?>"
                                        class="vi-ui dropdown"
                                        name="<?php self::set_params( 'update_product_if_not_available' ) ?>">
                                    <option value=""><?php esc_html_e( 'Do nothing', 'woocommerce-alidropship' ) ?></option>
									<?php
									foreach ( $product_statuses as $option_k => $update_product_status ) {
										?>
                                        <option value="<?php echo esc_attr( $option_k ) ?>" <?php selected( $option_k, $if_not_available ) ?>><?php echo esc_html( $update_product_status ); ?></option>
										<?php
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'Select an action when an AliExpress product is no longer available', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'update_product_if_shipping_error', true ) ?>"><?php esc_html_e( 'If selected shipping method is no longer available', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <select id="<?php self::set_params( 'update_product_if_shipping_error', true ) ?>"
                                        class="vi-ui dropdown"
                                        name="<?php self::set_params( 'update_product_if_shipping_error' ) ?>">
                                    <option value=""><?php esc_html_e( 'Do nothing', 'woocommerce-alidropship' ) ?></option>
									<?php
									foreach ( $product_statuses as $option_k => $update_product_status ) {
										?>
                                        <option value="<?php echo esc_attr( $option_k ) ?>" <?php selected( $option_k, $if_shipping_error ) ?>><?php echo esc_html( $update_product_status ); ?></option>
										<?php
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'Select an action when an AliExpress product\'s selected shipping method is removed or no shipping methods available', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'update_product_removed_variation', true ) ?>"><?php esc_html_e( 'If a variation is no longer available', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <select id="<?php self::set_params( 'update_product_removed_variation', true ) ?>"
                                        class="vi-ui dropdown"
                                        name="<?php self::set_params( 'update_product_removed_variation' ) ?>">
                                    <option value=""><?php esc_html_e( 'Do nothing', 'woocommerce-alidropship' ) ?></option>
                                    <option value="disable" <?php selected( 'disable', $removed_variation ) ?>><?php esc_html_e( 'Disable', 'woocommerce-alidropship' ); ?></option>
                                    <option value="outofstock" <?php selected( 'outofstock', $removed_variation ) ?>><?php esc_html_e( 'Set variation out-of-stock', 'woocommerce-alidropship' ) ?></option>
                                </select>
                                <p><?php esc_html_e( 'Select an action when a variation of an AliExpress product is no longer available', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
						<?php
						if ( $is_main ) {
							$send_email_if      = self::$settings->get_params( 'send_email_if' );
							$send_email_options = array(
								'is_offline'       => esc_html__( 'A product is no longer available', 'woocommerce-alidropship' ),
								'shipping_removed' => esc_html__( 'A product\'s shipping method is longer available', 'woocommerce-alidropship' ),
								'is_out_of_stock'  => esc_html__( 'A product is out of stock', 'woocommerce-alidropship' ),
								'price_changes'    => esc_html__( 'A product\'s price changes', 'woocommerce-alidropship' ),
//								'price_exceeds'    => esc_html__( 'Percentage of change in price exceeds the set value', 'woocommerce-alidropship' ),
							);
							?>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'send_email_if', true ) ?>"><?php esc_html_e( 'Notification email', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <select id="<?php self::set_params( 'send_email_if', true ) ?>"
                                            class="vi-ui dropdown"
                                            name="<?php self::set_params( 'send_email_if[]' ) ?>" multiple>
										<?php
										foreach ( $send_email_options as $send_email_option_k => $send_email_option ) {
											?>
                                            <option value="<?php echo esc_attr( $send_email_option_k ) ?>" <?php if ( in_array( $send_email_option_k, $send_email_if ) ) {
												echo esc_attr( 'selected' );
											} ?>><?php echo esc_html( $send_email_option ); ?></option>
											<?php
										}
										?>
                                    </select>
                                    <p><?php esc_html_e( 'When syncing products, send email to admin if an AliExpress product is no longer available/is out of stock/has price changed', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'received_email', true ) ?>"><?php esc_html_e( 'Received address', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <input id="<?php self::set_params( 'received_email', true ) ?>"
                                           type="text"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'received_email' ) ) ?>"
                                           name="<?php self::set_params( 'received_email' ) ?>"/>
                                    <p><?php _e( 'Notification will be sent to this address. If not set, the "From" address in <a target="_blank" href="admin.php?page=wc-settings&tab=email">WooCommerce settings/Emails</a> will be used.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
							<?php
						}
						?>
                        </tbody>
                    </table>
                </div>
				<?php
				if ( $is_main ) {
					?>
                    <div class="vi-ui bottom attached tab segment <?php self::set_params( 'tab-content', true ) ?>"
                         data-tab="product_split">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'split_auto_remove_attribute', true ) ?>"><?php esc_html_e( 'Automatically remove attribute', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'split_auto_remove_attribute', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'split_auto_remove_attribute' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'split_auto_remove_attribute', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'split_auto_remove_attribute' ) ?>"/>
                                        <label><?php esc_html_e( 'When splitting a product by a specific attribute, remove that attribute of split products', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment <?php self::set_params( 'tab-content', true ) ?>"
                         data-tab="override">
                        <div class="vi-ui positive small message">
							<?php esc_html_e( 'Below options are used when you override or reimport a product', 'woocommerce-alidropship' ); ?>
                        </div>
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'override_keep_product', true ) ?>"><?php esc_html_e( 'Keep Woo product', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'override_keep_product', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'override_keep_product' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'override_keep_product', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'override_keep_product' ) ?>"/>
                                        <label><?php esc_html_e( 'Instead of deleting old product to create a new one, it will update the overridden old product\'s prices/stock/attributes/variations based on the new data. This way, data such as reviews, metadata... will not be lost.', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                    <p><?php _e( '<strong>Note:</strong> When reimporting products, this option will always be considered as "Enabled"', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'override_link_only', true ) ?>"><?php esc_html_e( 'Link existing variations only', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'override_link_only', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'override_link_only' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'override_link_only', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'override_link_only' ) ?>"/>
                                        <label><?php esc_html_e( 'Do not create new variations even if the number of variations you select when overriding/reimporting a product is greater than the number of variations of target product.', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                    <p><?php esc_html_e( 'If disabled, new variations will be created if not exist', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'override_find_in_orders', true ) ?>"><?php esc_html_e( 'Find in unfulfilled orders', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'override_find_in_orders', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'override_find_in_orders' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'override_find_in_orders', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'override_find_in_orders' ) ?>"/>
                                        <label><?php esc_html_e( 'Check for existence of overridden product in unfulfilled orders before overriding', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'override_title', true ) ?>"><?php esc_html_e( 'Override title', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'override_title', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'override_title' ), 1 ) ?>
                                               tabindex="0" class="<?php self::set_params( 'override_title', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'override_title' ) ?>"/>
                                        <label><?php esc_html_e( 'Replace title of overridden product with new product\'s title', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'override_images', true ) ?>"><?php esc_html_e( 'Override images', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'override_images', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'override_images' ), 1 ) ?>
                                               tabindex="0" class="<?php self::set_params( 'override_images', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'override_images' ) ?>"/>
                                        <label><?php esc_html_e( 'Replace images and gallery of overridden product with new product\'s images and gallery', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'override_description', true ) ?>"><?php esc_html_e( 'Override description', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'override_description', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'override_description' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'override_description', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'override_description' ) ?>"/>
                                        <label><?php esc_html_e( 'Replace description and short description of overridden product with new product\'s description and short description', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'override_hide', true ) ?>"><?php esc_html_e( 'Hide options', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'override_hide', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'override_hide' ), 1 ) ?>
                                               tabindex="0" class="<?php self::set_params( 'override_hide', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'override_hide' ) ?>"/>
                                        <label><?php esc_html_e( 'Do not show these options when overriding product', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment <?php self::set_params( 'tab-content', true ) ?>"
                         data-tab="migration">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'migration_link_only', true ) ?>"><?php esc_html_e( 'Link variation only', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'migration_link_only', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'migration_link_only' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'migration_link_only', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'migration_link_only' ) ?>"/>
                                        <label><?php esc_html_e( 'When migrating a product from other plugins(Link existing Woo product), only link existing variations', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment <?php echo esc_attr( self::set( array(
						'attributes-tab',
						'tab-content'
					) ) ) ?>"
                         data-tab="attributes">
                        <div class="vi-ui message positive">
                            <ul class="list">
                                <li><?php esc_html_e( 'This feature is to automatically replace original attribute term with respective value in the Import list.', 'woocommerce-alidropship' ) ?></li>
                                <li><?php _e( '<strong>Note</strong>: This does not apply to products whose attributes were edited.', 'woocommerce-alidropship' ) ?></li>
                            </ul>
                        </div>
                        <div class="<?php echo esc_attr( self::set( 'attributes-mapping-table-container' ) ) ?>">
                            <table id="<?php echo esc_attr( self::set( 'attributes-mapping-table' ) ) ?>"
                                   class="vi-ui celled table <?php echo esc_attr( self::set( 'attributes-mapping-table' ) ) ?>">
                                <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Attribute slug', 'woocommerce-alidropship' ) ?></th>
                                    <th><?php esc_html_e( 'Original attribute term(case-insensitive)', 'woocommerce-alidropship' ) ?></th>
                                    <th><?php esc_html_e( 'Replacement', 'woocommerce-alidropship' ) ?></th>
                                </tr>
                                </thead>
                                <tbody class="<?php echo esc_attr( self::set( 'attributes-mapping' ) ) ?>">
                                </tbody>
                            </table>
                            <input type="hidden" name="<?php self::set_params( 'attributes_mapping_origin' ) ?>"
                                   value="<?php echo esc_attr( self::$settings->get_params( 'attributes_mapping_origin' ) ) ?>">
                            <input type="hidden" name="<?php self::set_params( 'attributes_mapping_replacement' ) ?>"
                                   value="<?php echo esc_attr( self::$settings->get_params( 'attributes_mapping_replacement' ) ) ?>">
                        </div>
                        <div class="<?php echo esc_attr( self::set( array(
							'overlay',
						) ) ) ?>">
                            <div class="vi-ui indicating progress standard active <?php echo esc_attr( self::set( 'attributes-mapping-progress' ) ) ?>">
                                <div class="label"></div>
                                <div class="bar">
                                    <div class="progress"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="vi-ui bottom attached tab segment <?php echo esc_attr( self::set( array(
						'tab-content'
					) ) ) ?>"
                         data-tab="video">
                        <div class="vi-ui positive message">
                            <ul class="list">
                                <li><?php esc_html_e( 'Product video uses original AliExpress video url', 'woocommerce-alidropship' ); ?></li>
                                <li><?php esc_html_e( 'For products you imported before 1.0.9, please sync them for videos to be updated', 'woocommerce-alidropship' ); ?></li>
                            </ul>
                        </div>
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'import_product_video', true ) ?>">
										<?php esc_html_e( 'Import product video', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'import_product_video', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'import_product_video' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'import_product_video', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'import_product_video' ) ?>"/>
                                        <label><?php esc_html_e( 'Product video will be imported as an external link', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'show_product_video_tab', true ) ?>">
										<?php esc_html_e( 'Show product video tab', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'show_product_video_tab', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'show_product_video_tab' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'show_product_video_tab', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'show_product_video_tab' ) ?>"/>
                                        <label><?php esc_html_e( 'Display product video on a separate tab in the frontend', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'product_video_tab_priority', true ) ?>">
										<?php esc_html_e( 'Video tab priority', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <input id="<?php self::set_params( 'product_video_tab_priority', true ) ?>"
                                           type="number"
                                           min="0"
                                           class="<?php self::set_params( 'product_video_tab_priority', true ) ?>"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'product_video_tab_priority' ) ) ?>"
                                           name="<?php self::set_params( 'product_video_tab_priority' ) ?>"/>
                                    <p class="description"><?php esc_html_e( 'You can adjust this value to change order of video tab', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'product_video_full_tab', true ) ?>">
										<?php esc_html_e( 'Make video full tab width', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'product_video_full_tab', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'product_video_full_tab' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'product_video_full_tab', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'product_video_full_tab' ) ?>"/>
                                        <label><?php esc_html_e( 'By default, product videos are displayed in their original width. Enable this option to make product videos have the same width as the tab.', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
					<?php
					if ( class_exists( 'WeDevs_Dokan' ) ) {
						?>
                        <div class="vi-ui bottom attached tab segment <?php self::set_params( 'tab-content', true ) ?>"
                             data-tab="vendor">
                            <div class="vi-ui positive message">
                                <div class="header"><?php esc_html_e( '', 'woocommerce-alidropship' ); ?></div>
                                <ul class="list">
                                    <li><?php esc_html_e( 'Allow vendors to access Import list, Imported page and have their own ALD necessary settings to import and manage AliExpress products that the vendors import.', 'woocommerce-alidropship' ); ?></li>
                                    <li><?php esc_html_e( 'If vendors\' products are synced by admin(auto or manual), the vendors\' Product sync settings are still used just like when the vendors manually sync their products themselves', 'woocommerce-alidropship' ); ?></li>
                                    <li><?php _e( '<strong>*Important</strong>: Vendors must import AliExpress products using <strong>Authentication method</strong>', 'woocommerce-alidropship' ); ?></li>
                                </ul>
                            </div>
                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'restrict_products_by_vendor', true ) ?>">
											<?php esc_html_e( 'Dokan compatible', 'woocommerce-alidropship' ) ?>
                                        </label>
                                    </th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input id="<?php self::set_params( 'restrict_products_by_vendor', true ) ?>"
                                                   type="checkbox" <?php checked( self::$settings->get_params( 'restrict_products_by_vendor' ), 1 ) ?>
                                                   tabindex="0"
                                                   class="<?php self::set_params( 'restrict_products_by_vendor', true ) ?>"
                                                   value="1"
                                                   name="<?php self::set_params( 'restrict_products_by_vendor' ) ?>"/>
                                            <label><?php esc_html_e( 'Enable', 'woocommerce-alidropship' ) ?></label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'disable_vendor_setting', true ) ?>">
											<?php esc_html_e( "Don't allow vendor setting", 'woocommerce-alidropship' ) ?>
                                        </label>
                                    </th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input id="<?php self::set_params( 'disable_vendor_setting', true ) ?>"
                                                   type="checkbox" <?php checked( self::$settings->get_params( 'disable_vendor_setting' ), 1 ) ?>
                                                   tabindex="0"
                                                   class="<?php self::set_params( 'disable_vendor_setting', true ) ?>"
                                                   value="1"
                                                   name="<?php self::set_params( 'disable_vendor_setting' ) ?>"/>
                                            <label><?php esc_html_e( 'Enable', 'woocommerce-alidropship' ) ?></label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'send_bcc_email_to_vendor', true ) ?>">
											<?php esc_html_e( 'Send Bcc of notification email to vendor', 'woocommerce-alidropship' ) ?>
                                        </label>
                                    </th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input id="<?php self::set_params( 'send_bcc_email_to_vendor', true ) ?>"
                                                   type="checkbox" <?php checked( self::$settings->get_params( 'send_bcc_email_to_vendor' ), 1 ) ?>
                                                   tabindex="0"
                                                   class="<?php self::set_params( 'send_bcc_email_to_vendor', true ) ?>"
                                                   value="1"
                                                   name="<?php self::set_params( 'send_bcc_email_to_vendor' ) ?>"/>
                                            <label><?php esc_html_e( 'When syncing products which are imported by a vendor, if "Notification email" is set and sent, also send a copy of the email to the vendor', 'woocommerce-alidropship' ) ?></label>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
						<?php
					}
					?>
                    <div class="vi-ui bottom attached tab segment <?php self::set_params( 'tab-content', true ) ?>"
                         data-tab="fulfill">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'fulfill_default_carrier', true ) ?>">
										<?php esc_html_e( 'Carrier company', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <select class="vi-ui dropdown fluid"
                                            name="<?php self::set_params( 'fulfill_default_carrier' ) ?>"
                                            id="<?php self::set_params( 'fulfill_default_carrier', true ) ?>">
										<?php
										$fulfill_default_carrier = self::$settings->get_params( 'fulfill_default_carrier' );
										foreach ( $shipping_companies as $key => $value ) {
											if ( is_array( $value ) ) {
												echo "<option value='$key' " . selected( $fulfill_default_carrier, $key ) . ">{$value['origin']}</option>";
											} else {
												echo "<option value='$key' " . selected( $fulfill_default_carrier, $key ) . ">$value</option>";
											}
										}
										?>
                                    </select>
                                    <p><?php esc_html_e( "Default carrier company. If order item doesn't  include a shipping carrier, the default carrier will be used.", 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'always_use_default_carrier', true ) ?>">
										<?php esc_html_e( 'Always use default carrier', 'woocommerce-alidropship' ) ?>
                                    </label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'always_use_default_carrier' ) ?>"
                                               id="<?php self::set_params( 'always_use_default_carrier', true ) ?>"
                                               class="<?php self::set_params( 'always_use_default_carrier', true ) ?>"
                                               value="1" <?php checked( self::$settings->get_params( 'always_use_default_carrier' ), 1 ) ?>>
                                        <label><?php esc_html_e( 'Always use default carrier instead of order item carrier', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'fulfill_default_phone_number', true ) ?>">
										<?php esc_html_e( 'Default phone number', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui labeled left input fluid wad-labeled-button <?php self::set_params( 'fulfill_default_phone_number_container', true ) ?>">
                                        <label class="vi-ui label">
                                            <select class="vi-ui dropdown search"
                                                    name="<?php self::set_params( 'fulfill_default_phone_country' ) ?>"
                                                    class="<?php self::set_params( 'fulfill_default_phone_country', true ) ?>"
                                                    id="<?php self::set_params( 'fulfill_default_phone_country', true ) ?>">
												<?php
												$phone_country   = self::$settings->get_params( 'fulfill_default_phone_country' );
												$phone_countries = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_API::get_phone_country_code();
												ksort( $phone_countries );
												foreach ( $phone_countries as $phone_country_k => $phone_country_v ) {
													?>
                                                    <option value="<?php echo esc_attr( $phone_country_k ) ?>" <?php selected( $phone_country, $phone_country_k ) ?>><?php echo esc_html( $phone_country_v ? "{$phone_country_k}({$phone_country_v})" : $phone_country_k ) ?></option>
													<?php
												}
												?>
                                            </select>
                                        </label>
                                        <input type="tel"
                                               id="<?php self::set_params( 'fulfill_default_phone_number', true ) ?>"
                                               name="<?php self::set_params( 'fulfill_default_phone_number' ) ?>"
                                               value="<?php echo esc_attr( self::$settings->get_params( 'fulfill_default_phone_number' ) ) ?>">
                                    </div>
                                    <p><?php esc_html_e( 'If an order does not have phone number, this number will be used when fulfilling AliExpress order', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'fulfill_default_phone_number_override', true ) ?>"><?php esc_html_e( 'Override customer phone number', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'fulfill_default_phone_number_override' ) ?>"
                                               id="<?php self::set_params( 'fulfill_default_phone_number_override', true ) ?>"
                                               class="<?php self::set_params( 'fulfill_default_phone_number_override', true ) ?>"
                                               value="1" <?php checked( self::$settings->get_params( 'fulfill_default_phone_number_override' ), 1 ) ?>>
                                        <label><?php esc_html_e( 'Always use Default phone number when fulfilling AliExpress order no matter your customers have phone number or not', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                    <p><?php _e( '<strong>*Note:</strong> This only overrides a customer\'s phone number if the default phone country is the same as the customer\'s country', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'cpf_custom_meta_key', true ) ?>"><?php esc_html_e( 'CPF meta field', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <input type="text"
                                           name="<?php self::set_params( 'cpf_custom_meta_key' ) ?>"
                                           id="<?php self::set_params( 'cpf_custom_meta_key', true ) ?>"
                                           class="<?php self::set_params( 'cpf_custom_meta_key', true ) ?>"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'cpf_custom_meta_key' ) ) ?>">
                                    <p><?php esc_html_e( 'The order meta field that a 3rd party plugin uses to store customer\'s CPF field.', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php esc_html_e( 'This is used only for Customers from Brazil. If empty, billing company will be used as CPF when fulfilling AliExpress orders.', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php _e( 'If you use <a target="_blank" href="https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/">Brazilian Market on WooCommerce</a>, please fill this option with <strong>_billing_cpf</strong>', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'add_cpf_to_street', true ) ?>"><?php esc_html_e( 'Add cpf to street', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'add_cpf_to_street' ) ?>"
                                               id="<?php self::set_params( 'add_cpf_to_street', true ) ?>"
                                               class="<?php self::set_params( 'add_cpf_to_street', true ) ?>"
											<?php checked( self::$settings->get_params( 'add_cpf_to_street' ), 1 ) ?>
                                               value="1"><label><?php esc_html_e( 'Yes', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                    <p><?php esc_html_e( 'Append customer\'s cpf to street for easier lookup on AliExpress', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'billing_number_meta_key', true ) ?>"><?php esc_html_e( 'Billing number meta field', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <input type="text"
                                           name="<?php self::set_params( 'billing_number_meta_key' ) ?>"
                                           id="<?php self::set_params( 'billing_number_meta_key', true ) ?>"
                                           class="<?php self::set_params( 'billing_number_meta_key', true ) ?>"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'billing_number_meta_key' ) ) ?>">
                                    <p><?php esc_html_e( 'If you customize checkout fields to add the billing number field, please enter the order meta field which is used to store billing number here.', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php _e( 'If you use <a target="_blank" href="https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/">Brazilian Market on WooCommerce</a>, please fill this option with <strong>_billing_number</strong>', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php _e( '<strong>*Caution: </strong>If you already use a custom PHP snippet to append billing number to order address via <strong>vi_wad_fulfillment_customer_info</strong> filter hook, please leave this field empty to avoid billing number being added twice to order address which makes the address become incorrect.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'shipping_number_meta_key', true ) ?>"><?php esc_html_e( 'Shipping number meta field', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <input type="text"
                                           name="<?php self::set_params( 'shipping_number_meta_key' ) ?>"
                                           id="<?php self::set_params( 'shipping_number_meta_key', true ) ?>"
                                           class="<?php self::set_params( 'shipping_number_meta_key', true ) ?>"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'shipping_number_meta_key' ) ) ?>">
                                    <p><?php esc_html_e( 'If you customize checkout fields to add the shipping number field, please enter the order meta field which is used to store shipping number here.', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php _e( 'If you use <a target="_blank" href="https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/">Brazilian Market on WooCommerce</a>, please fill this option with <strong>_shipping_number</strong>', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php _e( '<strong>*Caution: </strong>If you already use a custom PHP snippet to append shipping number to order address via <strong>vi_wad_fulfillment_customer_info</strong> filter hook, please leave this field empty to avoid shipping number being added twice to order address which makes the address become incorrect.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'billing_neighborhood_meta_key', true ) ?>"><?php esc_html_e( 'Billing neighborhood meta field', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <input type="text"
                                           name="<?php self::set_params( 'billing_neighborhood_meta_key' ) ?>"
                                           id="<?php self::set_params( 'billing_neighborhood_meta_key', true ) ?>"
                                           class="<?php self::set_params( 'billing_neighborhood_meta_key', true ) ?>"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'billing_neighborhood_meta_key' ) ) ?>">
                                    <p><?php esc_html_e( 'If you customize checkout fields to add the billing neighborhood field, please enter the order meta field which is used to store billing neighborhood here.', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php _e( 'If you use <a target="_blank" href="https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/">Brazilian Market on WooCommerce</a>, please fill this option with <strong>_billing_neighborhood</strong>', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php _e( '<strong>*Caution: </strong>If you already use a custom PHP snippet to append billing neighborhood to order address via <strong>vi_wad_fulfillment_customer_info</strong> filter hook, please leave this field empty to avoid billing neighborhood being added twice to order address which makes the address become incorrect.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'shipping_neighborhood_meta_key', true ) ?>"><?php esc_html_e( 'Shipping neighborhood meta field', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <input type="text"
                                           name="<?php self::set_params( 'shipping_neighborhood_meta_key' ) ?>"
                                           id="<?php self::set_params( 'shipping_neighborhood_meta_key', true ) ?>"
                                           class="<?php self::set_params( 'shipping_neighborhood_meta_key', true ) ?>"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'shipping_neighborhood_meta_key' ) ) ?>">
                                    <p><?php esc_html_e( 'If you customize checkout fields to add the shipping neighborhood field, please enter the order meta field which is used to store shipping neighborhood here.', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php _e( 'If you use <a target="_blank" href="https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/">Brazilian Market on WooCommerce</a>, please fill this option with <strong>_shipping_neighborhood</strong>', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php _e( '<strong>*Caution: </strong>If you already use a custom PHP snippet to append shipping neighborhood to order address via <strong>vi_wad_fulfillment_customer_info</strong> filter hook, please leave this field empty to avoid shipping neighborhood being added twice to order address which makes the address become incorrect.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'rut_meta_key', true ) ?>"><?php esc_html_e( 'RUT meta field', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <input type="text"
                                           name="<?php self::set_params( 'rut_meta_key' ) ?>"
                                           id="<?php self::set_params( 'rut_meta_key', true ) ?>"
                                           class="<?php self::set_params( 'rut_meta_key', true ) ?>"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'rut_meta_key' ) ) ?>">
                                    <p><?php esc_html_e( 'The order meta field that a 3rd party plugin uses to store customer\'s RUT number.', 'woocommerce-alidropship' ) ?></p>
                                    <p><?php esc_html_e( 'RUT number is required when you fulfill orders of Customers from Chile.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'fulfill_order_note', true ) ?>">
										<?php esc_html_e( 'AliExpress Order note', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                               <textarea type="text" id="<?php self::set_params( 'fulfill_order_note', true ) ?>"
                                         name="<?php self::set_params( 'fulfill_order_note' ) ?>"><?php echo wp_kses_post( self::$settings->get_params( 'fulfill_order_note' ) ) ?></textarea>
                                    <p><?php esc_html_e( 'Add this note to AliExpress order when fulfilling', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Show action', 'woocommerce-alidropship' ) ?></th>
                                <td>
                                    <select class="vi-wad-order-status-for-fulfill vi-ui fluid dropdown"
                                            multiple="multiple"
                                            name="<?php self::set_params( 'order_status_for_fulfill', false, true ) ?>">
										<?php
										$order_status_for_fulfill = self::$settings->get_params( 'order_status_for_fulfill' );
										foreach ( wc_get_order_statuses() as $key => $value ) {
											$selected = '';
											if ( is_array( $order_status_for_fulfill ) ) {
												$selected = in_array( $key, $order_status_for_fulfill ) ? 'selected' : '';
											}
											echo "<option value='$key' {$selected}>$value</option>";
										}
										?>
                                    </select>
                                    <p><?php esc_html_e( 'Only show action buttons for orders with status among these', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Change order status when Ali order ID is filled', 'woocommerce-alidropship' ) ?></th>
                                <td>
									<?php
									$order_status_after_ali_order = self::$settings->get_params( 'order_status_after_ali_order' );
									?>
                                    <select class="vi-wad-order-status-after-sync vi-ui dropdown"
                                            name="<?php self::set_params( 'order_status_after_ali_order', false, false ) ?>">
                                        <option><?php esc_html_e( 'No change', 'woocommerce-alidropship' ); ?></option>
										<?php
										foreach ( wc_get_order_statuses() as $key => $value ) {
											$selected = $key == $order_status_after_ali_order ? 'selected' : '';
											echo "<option value='$key' {$selected}>$value</option>";
										}
										?>
                                    </select>
                                    <p><?php esc_html_e( 'Only work if an order does not have any tracking numbers', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Change order status when both Ali order ID and tracking number are filled', 'woocommerce-alidropship' ) ?></th>
                                <td>
									<?php
									$order_status_after_sync = self::$settings->get_params( 'order_status_after_sync' );
									?>
                                    <select class="vi-wad-order-status-after-sync vi-ui dropdown"
                                            name="<?php self::set_params( 'order_status_after_sync', false, false ) ?>">
                                        <option><?php esc_html_e( 'No change', 'woocommerce-alidropship' ); ?></option>
										<?php
										foreach ( wc_get_order_statuses() as $key => $value ) {
											$selected = $key == $order_status_after_sync ? 'selected' : '';
											echo "<option value='$key' {$selected}>$value</option>";
										}
										?>
                                    </select>
                                    <p><?php esc_html_e( 'Automatically change order status after order id & tracking number of an order are synced successfully', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'batch_request_enable', true ) ?>"><?php esc_html_e( 'Use batch request', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'batch_request_enable' ) ?>"
                                               id="<?php self::set_params( 'batch_request_enable', true ) ?>"
                                               class="<?php self::set_params( 'batch_request_enable', true ) ?>"
                                               value="1" <?php checked( self::$settings->get_params( 'batch_request_enable' ), 1 ) ?>>
                                        <label><?php _e( 'With batch request, you can fulfill a maximum of 20 orders in a single request but those orders <strong>CANNOT be paid with PayPal and payment card</strong> at the moment. Therefore, you should only enable this option if you do not use PayPal/card to pay fulfilled AliExpress orders.', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'auto_order_if_payment', true ) ?>"><?php esc_html_e( 'Auto fulfill', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui segment">
                                        <div class="vi-ui two column very relaxed grid">
                                            <div class="column">
                                                <div class="header"><?php esc_html_e( 'Payment method in', 'woocommerce-alidropship' ) ?></div>
                                                <select class="vi-ui fluid dropdown" multiple="multiple"
                                                        name="<?php self::set_params( 'auto_order_if_payment', false, true ) ?>">
													<?php
													$payment_gateways  = WC()->payment_gateways->payment_gateways();
													$auto_ali_order_if = self::$settings->get_params( 'auto_order_if_payment' );
													foreach ( $payment_gateways as $payment_method ) {
														if ( $payment_method->enabled ) {
															$title_show = ! empty( $payment_method->method_title ) ? $payment_method->method_title : $payment_method->title;
															?>
                                                            <option <?php if ( in_array( $payment_method->id, $auto_ali_order_if ) ) {
																echo 'selected';
															} ?> value="<?php echo esc_attr( $payment_method->id ) ?>"><?php echo esc_html( $title_show ) ?></option>
															<?php
														}
													}
													?>
                                                </select>
                                            </div>
                                            <div class="column">
                                                <div class="header"><?php esc_html_e( 'Order status in', 'woocommerce-alidropship' ) ?></div>
                                                <select class="vi-ui fluid dropdown" multiple="multiple"
                                                        name="<?php self::set_params( 'auto_order_if_status', false, true ) ?>">
													<?php
													$auto_order_if_status = self::$settings->get_params( 'auto_order_if_status' );
													foreach ( wc_get_order_statuses() as $key => $value ) {
														$selected = '';
														if ( in_array( $key, $auto_order_if_status ) ) {
															$selected = 'selected';
														}
														echo "<option value='$key' {$selected}>$value</option>";
													}
													?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="vi-ui vertical divider">
											<?php esc_html_e( 'And', 'woocommerce-alidropship' ) ?>
                                        </div>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'When a new order is placed on your site via one of chosen payment methods and order status is among chosen statuses, automatically place that order on AliExpress.', 'woocommerce-alidropship' ) ?></p>
                                    <p class="description"><?php _e( '<strong>*Note: </strong>If frontend shipping option is not enabled, the first(cheapest) available shipping company will be used.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
						<?php
						if ( self::$update_order_next_schedule ) {
							$gmt_offset = intval( get_option( 'gmt_offset' ) );
							?>
                            <div class="vi-ui positive message">
								<?php
								printf( __( 'Next schedule: <strong>%s</strong>', 'woocommerce-alidropship' ), date_i18n( 'F j, Y g:i:s A', ( self::$update_order_next_schedule + HOUR_IN_SECONDS * $gmt_offset ) ) );
								?>
                            </div>
							<?php
						} else {
							?>
                            <div class="vi-ui negative message">
								<?php esc_html_e( 'Order auto-sync is currently DISABLED', 'woocommerce-alidropship' ); ?>
                            </div>
							<?php
						}
						if ( ! self::$settings->get_params( 'access_token' ) ) {
							?>
                            <div class="vi-ui message negative <?php self::set_params( 'update-order-message', true ) ?>">
								<?php esc_html_e( 'This function will not work because access token is missing.', 'woocommerce-alidropship' ) ?>
                                <a class="vi-ui button positive tiny" href="#/update"
                                   target="_self"><?php esc_html_e( 'Get access token', 'woocommerce-alidropship' ) ?></a>
                            </div>
							<?php
						}
						?>
                        <table class="form-table">
                            <tbody>
							<?php
							$update_order_auto          = self::$settings->get_params( 'update_order_auto' );
							$update_order_options_class = array( 'update-order-options' );
							if ( ! $update_order_auto ) {
								$update_order_options_class[] = 'hidden';
							}

							?>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'update_order_auto', true ) ?>"><?php esc_html_e( 'Get tracking number automatically', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'update_order_auto' ) ?>"
                                               id="<?php self::set_params( 'update_order_auto', true ) ?>"
                                               class="<?php self::set_params( 'update_order_auto', true ) ?>"
                                               value="1" <?php checked( $update_order_auto, 1 ) ?>>
                                        <label><?php esc_html_e( 'When fulfilling orders, tracking number is not available yet. This function helps you check and sync tracking number automatically', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
							<?php
							$update_order_interval = self::$settings->get_params( 'update_order_interval' );
							if ( intval( $update_order_interval ) < 1 ) {
								$update_order_interval = 1;
							}
							?>
                            <tr class="<?php echo esc_attr( self::set( $update_order_options_class ) ) ?>">
                                <th>
                                    <label for="<?php echo esc_attr( self::set( 'update_order_interval' ) ) ?>"><?php esc_html_e( 'Get tracking number every', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui right labeled input">
                                        <input type="number" min="1"
                                               name="<?php self::set_params( 'update_order_interval' ) ?>"
                                               id="<?php echo esc_attr( self::set( 'update_order_interval' ) ) ?>"
                                               value="<?php echo esc_attr( $update_order_interval ) ?>">
                                        <label for="<?php echo esc_attr( self::set( 'update_order_interval' ) ) ?>"
                                               class="vi-ui label"><?php esc_html_e( 'Day(s)', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( self::set( $update_order_options_class ) ) ?>">
                                <th>
                                    <label for="<?php echo esc_attr( self::set( 'update_order_hour' ) ) ?>"><?php esc_html_e( 'Get tracking number at', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <label for="<?php echo esc_attr( self::set( 'update_order_hour' ) ) ?>"
                                                       class="vi-ui label"><?php esc_html_e( 'Hour', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number" min="0" max="23"
                                                       name="<?php self::set_params( 'update_order_hour' ) ?>"
                                                       id="<?php echo esc_attr( self::set( 'update_order_hour' ) ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'update_order_hour' ) ) ?>">
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <label for="<?php echo esc_attr( self::set( 'update_order_minute' ) ) ?>"
                                                       class="vi-ui label"><?php esc_html_e( 'Minute', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number" min="0" max="59"
                                                       name="<?php self::set_params( 'update_order_minute' ) ?>"
                                                       id="<?php echo esc_attr( self::set( 'update_order_minute' ) ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'update_order_minute' ) ) ?>">
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <label for="<?php echo esc_attr( self::set( 'update_order_second' ) ) ?>"
                                                       class="vi-ui label"><?php esc_html_e( 'Second', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number" min="0" max="59"
                                                       name="<?php self::set_params( 'update_order_second' ) ?>"
                                                       id="<?php echo esc_attr( self::set( 'update_order_second' ) ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'update_order_second' ) ) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment <?php self::set_params( 'tab-content', true ) ?>"
                         data-tab="shipping">
                        <div class="vi-ui message positive">
                            <ul class="list">
                                <li><?php esc_html_e( 'This feature allows your customers to select shipping method for each item like you do on AliExpress', 'woocommerce-alidropship' ) ?></li>
                                <li><?php esc_html_e( 'Shipping cost of all cart items will be calculated and applied to the cart so you should not add shipping cost to product price when importing AliExpress products to avoid making the final price of products paid by your customers too high', 'woocommerce-alidropship' ) ?></li>
                                <li><?php printf( __( 'You have to create at least 1 shipping method in <a target="_blank" href="%s">WooCommerce settings/Shipping</a>', 'woocommerce-alidropship' ), admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ) ?></li>
                                <li><?php printf( __( '<strong>Important:</strong> For this feature to work correctly with products imported before version <strong>1.0.3</strong>, please go to <a target="_blank" href="%s">Imported List</a> to sync products using chrome extension', 'woocommerce-alidropship' ), admin_url( 'admin.php?page=woocommerce-alidropship-imported-list' ) ) ?></li>
                            </ul>
                        </div>
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'ali_shipping', true ) ?>"><?php esc_html_e( 'Enable', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'ali_shipping' ) ?>"
                                               id="<?php self::set_params( 'ali_shipping', true ) ?>"
                                               class="<?php self::set_params( 'ali_shipping', true ) ?>"
                                               value="1" <?php checked( self::$settings->get_params( 'ali_shipping' ), 1 ) ?>>
                                        <label><?php esc_html_e( 'Allow customers to choose shipping method while shopping', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'All options below will only work if this option is enabled', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
							<?php
							$ali_shipping_display = self::$settings->get_params( 'ali_shipping_display' );
							?>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'ali_shipping_display', true ) ?>"><?php esc_html_e( 'Shipping selection type', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <select class="vi-ui dropdown"
                                            name="<?php self::set_params( 'ali_shipping_display' ) ?>"
                                            id="<?php self::set_params( 'ali_shipping_display', true ) ?>">
                                        <option value="popup" <?php selected( $ali_shipping_display, 'popup' ) ?>><?php esc_html_e( 'Popup', 'woocommerce-alidropship' ) ?></option>
                                        <option value="select" <?php selected( $ali_shipping_display, 'select' ) ?>><?php esc_html_e( 'Select', 'woocommerce-alidropship' ) ?></option>
                                        <option value="radio" <?php selected( $ali_shipping_display, 'radio' ) ?>><?php esc_html_e( 'Radio', 'woocommerce-alidropship' ) ?></option>
                                    </select>
                                </td>
                            </tr>
							<?php
							$ali_shipping_type = self::$settings->get_params( 'ali_shipping_type' );
							?>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'ali_shipping_type', true ) ?>"><?php esc_html_e( 'Shipping calculation', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <select class="vi-ui fluid dropdown"
                                            name="<?php self::set_params( 'ali_shipping_type' ) ?>"
                                            id="<?php self::set_params( 'ali_shipping_type', true ) ?>">
                                        <option value="none" <?php selected( $ali_shipping_type, 'none' ) ?>><?php esc_html_e( 'Do not calculate item shipping, only save customer\'s shipping option', 'woocommerce-alidropship' ) ?></option>
                                        <option value="new" <?php selected( $ali_shipping_type, 'new' ) ?>><?php esc_html_e( 'Create a new shipping method and add it to currently available shipping options', 'woocommerce-alidropship' ) ?></option>
                                        <option value="new_only" <?php selected( $ali_shipping_type, 'new_only' ) ?>><?php esc_html_e( 'Create a new shipping method and make it the only available shipping option', 'woocommerce-alidropship' ) ?></option>
                                        <option value="add" <?php selected( $ali_shipping_type, 'add' ) ?>><?php esc_html_e( 'Calculate AliExpress shipping cost of all items in cart and add the cost to all currently available shipping options ', 'woocommerce-alidropship' ) ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Shipping packages are cached so if you change this option, you\'ll need to update your existing cart to make changes apply.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'ali_shipping_option_text', true ) ?>"><?php esc_html_e( 'AliExpress shipping option text', 'woocommerce-alidropship' ) ?></label>
                                <td>
									<?php
									self::default_language_flag_html( self::set( 'ali-shipping-option-text' ) );
									?>
                                    <input id="<?php self::set_params( 'ali_shipping_option_text', true ) ?>"
                                           type="text"
                                           class="<?php self::set_params( 'ali_shipping_option_text', true ) ?>"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_option_text' ) ) ?>"
                                           name="<?php self::set_params( 'ali_shipping_option_text' ) ?>"/>
									<?php
									if ( count( self::$languages ) ) {
										foreach ( self::$languages as $key => $value ) {
											?>
                                            <p>
                                                <label for="<?php echo esc_attr( self::set( 'ali-shipping-option-text-' . $value ) ) ?>"><?php
													if ( isset( self::$languages_data[ $value ]['country_flag_url'] ) && self::$languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( self::$languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( self::$languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . self::$languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
                                            <input id="<?php self::set_params( 'ali_shipping_option_text_' . $value, true ) ?>"
                                                   type="text"
                                                   class="<?php self::set_params( 'ali_shipping_option_text_' . $value, true ) ?>"
                                                   value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_option_text', $value ) ) ?>"
                                                   name="<?php self::set_params( 'ali_shipping_option_text_' . $value ) ?>"/>
											<?php
										}
									}
									self::table_of_placeholders( array(
										'shipping_cost'    => esc_html__( 'Shipping cost', 'woocommerce-alidropship' ),
										'shipping_company' => esc_html__( 'Shipping Company', 'woocommerce-alidropship' ),
										'delivery_time'    => esc_html__( 'Delivery time', 'woocommerce-alidropship' ),
									) );
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'ali_shipping_label', true ) ?>"><?php esc_html_e( 'Shipping label', 'woocommerce-alidropship' ) ?></label>
                                <td>
									<?php
									self::default_language_flag_html( self::set( 'ali-shipping-label' ) );
									?>
                                    <input id="<?php self::set_params( 'ali_shipping_label', true ) ?>"
                                           type="text"
                                           class="<?php self::set_params( 'ali_shipping_label', true ) ?>"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_label' ) ) ?>"
                                           name="<?php self::set_params( 'ali_shipping_label' ) ?>"/>
									<?php
									if ( count( self::$languages ) ) {
										foreach ( self::$languages as $key => $value ) {
											?>
                                            <p>
                                                <label for="<?php echo esc_attr( self::set( 'ali-shipping-label-' . $value ) ) ?>"><?php
													if ( isset( self::$languages_data[ $value ]['country_flag_url'] ) && self::$languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( self::$languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( self::$languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . self::$languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
                                            <input id="<?php self::set_params( 'ali_shipping_label_' . $value, true ) ?>"
                                                   type="text"
                                                   class="<?php self::set_params( 'ali_shipping_label_' . $value, true ) ?>"
                                                   value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_label', $value ) ) ?>"
                                                   name="<?php self::set_params( 'ali_shipping_label_' . $value ) ?>"/>
											<?php
										}
									}
									?>
                                    <p><?php esc_html_e( 'Label of added shipping method in cart/checkout', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'ali_shipping_label_free', true ) ?>"><?php esc_html_e( 'Free Shipping label', 'woocommerce-alidropship' ) ?></label>
                                <td>
									<?php
									self::default_language_flag_html( self::set( 'ali-shipping-label-free' ) );
									?>
                                    <input id="<?php self::set_params( 'ali_shipping_label_free', true ) ?>"
                                           type="text"
                                           class="<?php self::set_params( 'ali_shipping_label_free', true ) ?>"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_label_free' ) ) ?>"
                                           name="<?php self::set_params( 'ali_shipping_label_free' ) ?>"/>
									<?php
									if ( count( self::$languages ) ) {
										foreach ( self::$languages as $key => $value ) {
											?>
                                            <p>
                                                <label for="<?php echo esc_attr( self::set( 'ali-shipping-label-free-' . $value ) ) ?>"><?php
													if ( isset( self::$languages_data[ $value ]['country_flag_url'] ) && self::$languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( self::$languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( self::$languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . self::$languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
                                            <input id="<?php self::set_params( 'ali_shipping_label_free_' . $value, true ) ?>"
                                                   type="text"
                                                   class="<?php self::set_params( 'ali_shipping_label_free_' . $value, true ) ?>"
                                                   value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_label_free', $value ) ) ?>"
                                                   name="<?php self::set_params( 'ali_shipping_label_free_' . $value ) ?>"/>
											<?php
										}
									}
									?>
                                    <p><?php esc_html_e( 'Label of added free shipping method in cart/checkout', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'ali_shipping_show_tracking', true ) ?>"><?php esc_html_e( 'Tracking availability', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'ali_shipping_show_tracking' ) ?>"
                                               id="<?php self::set_params( 'ali_shipping_show_tracking', true ) ?>"
                                               class="<?php self::set_params( 'ali_shipping_show_tracking', true ) ?>"
                                               value="1" <?php checked( self::$settings->get_params( 'ali_shipping_show_tracking' ), 1 ) ?>>
                                        <label><?php esc_html_e( 'Show tracking availability of each shipping company', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'Only available for Popup type(both on single product and cart/checkout page).', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'ali_shipping_remember_company', true ) ?>"><?php esc_html_e( 'Remember shipping company', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'ali_shipping_remember_company' ) ?>"
                                               id="<?php self::set_params( 'ali_shipping_remember_company', true ) ?>"
                                               class="<?php self::set_params( 'ali_shipping_remember_company', true ) ?>"
                                               value="1" <?php checked( self::$settings->get_params( 'ali_shipping_remember_company' ), 1 ) ?>>
                                        <label><?php esc_html_e( 'When customers switch country in cart/checkout, keep the previously selected shipping company if it is still available for the new country', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'If disabled, the first available shipping company will be selected when switching country.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="vi-ui segment">
                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_product_enable', true ) ?>"><?php esc_html_e( 'Show on Single product', 'woocommerce-alidropship' ) ?></label>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox"
                                                   name="<?php self::set_params( 'ali_shipping_product_enable' ) ?>"
                                                   id="<?php self::set_params( 'ali_shipping_product_enable', true ) ?>"
                                                   class="<?php self::set_params( 'ali_shipping_product_enable', true ) ?>"
                                                   value="1" <?php checked( self::$settings->get_params( 'ali_shipping_product_enable' ), 1 ) ?>>
                                            <label><?php esc_html_e( 'Yes', 'woocommerce-alidropship' ) ?></label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_product_text', true ) ?>"><?php esc_html_e( 'Shipping selection label', 'woocommerce-alidropship' ) ?></label>
                                    <td>
										<?php
										self::default_language_flag_html( self::set( 'ali-shipping-product-text' ) );
										?>
                                        <input id="<?php self::set_params( 'ali_shipping_product_text', true ) ?>"
                                               type="text"
                                               class="<?php self::set_params( 'ali_shipping_product_text', true ) ?>"
                                               value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_product_text' ) ) ?>"
                                               name="<?php self::set_params( 'ali_shipping_product_text' ) ?>"/>
										<?php
										if ( count( self::$languages ) ) {
											foreach ( self::$languages as $key => $value ) {
												?>
                                                <p>
                                                    <label for="<?php echo esc_attr( self::set( 'ali-shipping-product-text-' . $value ) ) ?>"><?php
														if ( isset( self::$languages_data[ $value ]['country_flag_url'] ) && self::$languages_data[ $value ]['country_flag_url'] ) {
															?>
                                                            <img src="<?php echo esc_url( self::$languages_data[ $value ]['country_flag_url'] ); ?>">
															<?php
														}
														echo $value;
														if ( isset( self::$languages_data[ $value ]['translated_name'] ) ) {
															echo '(' . self::$languages_data[ $value ]['translated_name'] . ')';
														}
														?>:</label>
                                                </p>
                                                <input id="<?php self::set_params( 'ali_shipping_product_text_' . $value, true ) ?>"
                                                       type="text"
                                                       class="<?php self::set_params( 'ali_shipping_product_text_' . $value, true ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_product_text', $value ) ) ?>"
                                                       name="<?php self::set_params( 'ali_shipping_product_text_' . $value ) ?>"/>
												<?php
											}
										}
										self::table_of_placeholders( array(
											'country' => esc_html__( 'Customer\'s country', 'woocommerce-alidropship' ),
										) );
										?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_product_not_available_message', true ) ?>"><?php esc_html_e( 'Shipping not available message', 'woocommerce-alidropship' ) ?></label>
                                    <td>
										<?php
										self::default_language_flag_html( self::set( 'ali-shipping-product-not-available-message' ) );
										?>
                                        <input id="<?php self::set_params( 'ali_shipping_product_not_available_message', true ) ?>"
                                               type="text"
                                               class="<?php self::set_params( 'ali_shipping_product_not_available_message', true ) ?>"
                                               value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_product_not_available_message' ) ) ?>"
                                               name="<?php self::set_params( 'ali_shipping_product_not_available_message' ) ?>"/>
										<?php
										if ( count( self::$languages ) ) {
											foreach ( self::$languages as $key => $value ) {
												?>
                                                <p>
                                                    <label for="<?php echo esc_attr( self::set( 'ali-shipping-product-not-available-message-' . $value ) ) ?>"><?php
														if ( isset( self::$languages_data[ $value ]['country_flag_url'] ) && self::$languages_data[ $value ]['country_flag_url'] ) {
															?>
                                                            <img src="<?php echo esc_url( self::$languages_data[ $value ]['country_flag_url'] ); ?>">
															<?php
														}
														echo $value;
														if ( isset( self::$languages_data[ $value ]['translated_name'] ) ) {
															echo '(' . self::$languages_data[ $value ]['translated_name'] . ')';
														}
														?>:</label>
                                                </p>
                                                <input id="<?php self::set_params( 'ali_shipping_product_not_available_message_' . $value, true ) ?>"
                                                       type="text"
                                                       class="<?php self::set_params( 'ali_shipping_product_not_available_message_' . $value, true ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_product_not_available_message', $value ) ) ?>"
                                                       name="<?php self::set_params( 'ali_shipping_product_not_available_message_' . $value ) ?>"/>
												<?php
											}
										}
										self::table_of_placeholders( array(
											'country' => esc_html__( 'Customer\'s country', 'woocommerce-alidropship' ),
										) );
										?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_select_variation_message', true ) ?>"><?php esc_html_e( 'Require to select a variation message', 'woocommerce-alidropship' ) ?></label>
                                    <td>
										<?php
										self::default_language_flag_html( self::set( 'ali-shipping-select-variation-message' ) );
										?>
                                        <input id="<?php self::set_params( 'ali_shipping_select_variation_message', true ) ?>"
                                               type="text"
                                               class="<?php self::set_params( 'ali_shipping_select_variation_message', true ) ?>"
                                               value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_select_variation_message' ) ) ?>"
                                               name="<?php self::set_params( 'ali_shipping_select_variation_message' ) ?>"/>
										<?php
										if ( count( self::$languages ) ) {
											foreach ( self::$languages as $key => $value ) {
												?>
                                                <p>
                                                    <label for="<?php echo esc_attr( self::set( 'ali-shipping-select-variation-message-' . $value ) ) ?>"><?php
														if ( isset( self::$languages_data[ $value ]['country_flag_url'] ) && self::$languages_data[ $value ]['country_flag_url'] ) {
															?>
                                                            <img src="<?php echo esc_url( self::$languages_data[ $value ]['country_flag_url'] ); ?>">
															<?php
														}
														echo $value;
														if ( isset( self::$languages_data[ $value ]['translated_name'] ) ) {
															echo '(' . self::$languages_data[ $value ]['translated_name'] . ')';
														}
														?>:</label>
                                                </p>
                                                <input id="<?php self::set_params( 'ali_shipping_select_variation_message_' . $value, true ) ?>"
                                                       type="text"
                                                       class="<?php self::set_params( 'ali_shipping_select_variation_message_' . $value, true ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_select_variation_message', $value ) ) ?>"
                                                       name="<?php self::set_params( 'ali_shipping_select_variation_message_' . $value ) ?>"/>
												<?php
											}
										}
										self::table_of_placeholders( array(
											'country' => esc_html__( 'Customer\'s country', 'woocommerce-alidropship' ),
										) );
										?>
                                    </td>
                                </tr>
								<?php
								$ali_shipping_product_display = self::$settings->get_params( 'ali_shipping_product_display' );
								?>
                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_product_display', true ) ?>"><?php esc_html_e( 'Shipping selection type on Single product', 'woocommerce-alidropship' ) ?></label>
                                    <td>
                                        <select class="vi-ui dropdown"
                                                name="<?php self::set_params( 'ali_shipping_product_display' ) ?>"
                                                id="<?php self::set_params( 'ali_shipping_product_display', true ) ?>">
                                            <option value="popup" <?php selected( $ali_shipping_product_display, 'popup' ) ?>><?php esc_html_e( 'Popup', 'woocommerce-alidropship' ) ?></option>
                                            <option value="select" <?php selected( $ali_shipping_product_display, 'select' ) ?>><?php esc_html_e( 'Select', 'woocommerce-alidropship' ) ?></option>
                                            <option value="radio" <?php selected( $ali_shipping_product_display, 'radio' ) ?>><?php esc_html_e( 'Radio', 'woocommerce-alidropship' ) ?></option>
                                        </select>
                                    </td>
                                </tr>
								<?php
								$ali_shipping_product_position = self::$settings->get_params( 'ali_shipping_product_position' );
								?>
                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_product_position', true ) ?>"><?php esc_html_e( 'Position of shipping selection on Single product', 'woocommerce-alidropship' ) ?></label>
                                    <td>
                                        <select class="vi-ui dropdown"
                                                name="<?php self::set_params( 'ali_shipping_product_position' ) ?>"
                                                id="<?php self::set_params( 'ali_shipping_product_position', true ) ?>">
                                            <option value="before_cart" <?php selected( $ali_shipping_product_position, 'before_cart' ) ?>><?php esc_html_e( 'Before add-to-cart button', 'woocommerce-alidropship' ) ?></option>
                                            <option value="after_cart" <?php selected( $ali_shipping_product_position, 'after_cart' ) ?>><?php esc_html_e( 'After add-to-cart button', 'woocommerce-alidropship' ) ?></option>
                                        </select>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="vi-ui segment">
                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_not_available_remove', true ) ?>"><?php esc_html_e( 'Remove items that shipping is not available', 'woocommerce-alidropship' ) ?></label>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="checkbox"
                                                   name="<?php self::set_params( 'ali_shipping_not_available_remove' ) ?>"
                                                   id="<?php self::set_params( 'ali_shipping_not_available_remove', true ) ?>"
                                                   class="<?php self::set_params( 'ali_shipping_not_available_remove', true ) ?>"
                                                   value="1" <?php checked( self::$settings->get_params( 'ali_shipping_not_available_remove' ), 1 ) ?>>
                                            <label><?php esc_html_e( 'When customers go to checkout, remove all items which are not available to ship to customers\' country. During a customer session, items removed for this reason will be restored automatically if customer changes billing/shipping country to which the items are available to ship.', 'woocommerce-alidropship' ) ?></label>
                                        </div>
                                        <p><?php esc_html_e( 'If you allow those items to be ordered normally, you have to find alternative products from other suppliers before fulfilling AliExpress orders.', 'woocommerce-alidropship' ) ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_not_available_message', true ) ?>"><?php esc_html_e( 'Default message for items that shipping is not available', 'woocommerce-alidropship' ) ?></label>
                                    <td>
										<?php
										self::default_language_flag_html( self::set( 'ali-shipping-not-available-message' ) );
										?>
                                        <input id="<?php self::set_params( 'ali_shipping_not_available_message', true ) ?>"
                                               type="text"
                                               class="<?php self::set_params( 'ali_shipping_not_available_message', true ) ?>"
                                               value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_not_available_message' ) ) ?>"
                                               name="<?php self::set_params( 'ali_shipping_not_available_message' ) ?>"/>
										<?php
										if ( count( self::$languages ) ) {
											foreach ( self::$languages as $key => $value ) {
												?>
                                                <p>
                                                    <label for="<?php echo esc_attr( self::set( 'ali-shipping-not-available-message-' . $value ) ) ?>"><?php
														if ( isset( self::$languages_data[ $value ]['country_flag_url'] ) && self::$languages_data[ $value ]['country_flag_url'] ) {
															?>
                                                            <img src="<?php echo esc_url( self::$languages_data[ $value ]['country_flag_url'] ); ?>">
															<?php
														}
														echo $value;
														if ( isset( self::$languages_data[ $value ]['translated_name'] ) ) {
															echo '(' . self::$languages_data[ $value ]['translated_name'] . ')';
														}
														?>:</label>
                                                </p>
                                                <input id="<?php self::set_params( 'ali_shipping_not_available_message_' . $value, true ) ?>"
                                                       type="text"
                                                       class="<?php self::set_params( 'ali_shipping_not_available_message_' . $value, true ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_not_available_message', $value ) ) ?>"
                                                       name="<?php self::set_params( 'ali_shipping_not_available_message_' . $value ) ?>"/>
												<?php
											}
										}
										?>
                                        <div class="<?php self::set_params( 'ali_shipping_not_available_remove_dependency', true ) ?>">
                                            <p><?php esc_html_e( 'Below placeholders can only be used if the "Remove items that shipping is not available" option is disabled', 'woocommerce-alidropship' ) ?></p>
											<?php
											self::table_of_placeholders( array(
												'shipping_cost' => esc_html__( 'Default shipping cost', 'woocommerce-alidropship' ),
												'delivery_time' => esc_html__( 'Default delivery time', 'woocommerce-alidropship' ),
											) );
											?>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="<?php self::set_params( 'ali_shipping_not_available_remove_dependency', true ) ?>">
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_not_available_cost', true ) ?>"><?php esc_html_e( 'Default shipping cost', 'woocommerce-alidropship' ) ?></label>
                                    <td>
                                        <div class="vi-ui labeled left input">
                                            <label class="vi-ui label"><?php echo get_woocommerce_currency_symbol( get_option( 'woocommerce_currency' ) ); ?></label>
                                            <input type="number"
                                                   min="0"
                                                   step="any"
                                                   name="<?php self::set_params( 'ali_shipping_not_available_cost' ) ?>"
                                                   id="<?php self::set_params( 'ali_shipping_not_available_cost', true ) ?>"
                                                   class="<?php self::set_params( 'ali_shipping_not_available_cost', true ) ?>"
                                                   value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_not_available_cost' ) ) ?>">
                                        </div>
                                        <p><?php esc_html_e( 'Apply this shipping cost for items that shipping is not available. 0 means free shipping', 'woocommerce-alidropship' ) ?></p>
                                    </td>
                                </tr>
                                <tr class="<?php self::set_params( 'ali_shipping_not_available_remove_dependency', true ) ?>">
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_not_available_time_min', true ) ?>"><?php esc_html_e( 'Default min delivery time', 'woocommerce-alidropship' ) ?></label>
                                    <td>
                                        <div class="vi-ui right labeled input">
                                            <input type="number"
                                                   min="0"
                                                   step="1"
                                                   name="<?php self::set_params( 'ali_shipping_not_available_time_min' ) ?>"
                                                   id="<?php self::set_params( 'ali_shipping_not_available_time_min', true ) ?>"
                                                   class="<?php self::set_params( 'ali_shipping_not_available_time_min', true ) ?>"
                                                   value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_not_available_time_min' ) ) ?>">
                                            <label class="vi-ui label"><?php esc_html_e( 'Day(s)', 'woocommerce-alidropship' ) ?></label>
                                        </div>
                                        <p><?php esc_html_e( 'Min delivery time shown for items that shipping is not available', 'woocommerce-alidropship' ) ?></p>
                                    </td>
                                </tr>
                                <tr class="<?php self::set_params( 'ali_shipping_not_available_remove_dependency', true ) ?>">
                                    <th>
                                        <label for="<?php self::set_params( 'ali_shipping_not_available_time_max', true ) ?>"><?php esc_html_e( 'Default max delivery time', 'woocommerce-alidropship' ) ?></label>
                                    <td>
                                        <div class="vi-ui right labeled input">
                                            <input type="number"
                                                   min="0"
                                                   step="1"
                                                   name="<?php self::set_params( 'ali_shipping_not_available_time_max' ) ?>"
                                                   id="<?php self::set_params( 'ali_shipping_not_available_time_max', true ) ?>"
                                                   class="<?php self::set_params( 'ali_shipping_not_available_time_max', true ) ?>"
                                                   value="<?php echo esc_attr( self::$settings->get_params( 'ali_shipping_not_available_time_max' ) ) ?>">
                                            <label class="vi-ui label"><?php esc_html_e( 'Day(s)', 'woocommerce-alidropship' ) ?></label>
                                        </div>
                                        <p><?php esc_html_e( 'Max delivery time shown for items that shipping is not available', 'woocommerce-alidropship' ) ?></p>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="vi-ui message positive">
                            <div class="head">
								<?php esc_html_e( 'Mask Shipping companies', 'woocommerce-alidropship' ) ?>
                            </div>
                            <ul class="list">
                                <li><?php esc_html_e( 'Change how shipping company name displays to your customers', 'woocommerce-alidropship' ) ?></li>
                                <li><?php esc_html_e( 'Leave the replacement of respective company blank if you want your customer see the original name', 'woocommerce-alidropship' ) ?></li>
                                <li><?php esc_html_e( 'The list of available shipping companies will grow in time and is updated everyday. You can also: ', 'woocommerce-alidropship' ) ?>
                                    <span class="vi-ui green button tiny <?php echo esc_attr( self::set( 'shipping-company-update' ) ) ?>"><?php esc_html_e( 'Update now', 'woocommerce-alidropship' ) ?></span>
                                </li>
                            </ul>
                        </div>
                        <div class="vi-ui labeled left input fluid">
                            <label class="vi-ui label green"><i
                                        class="icon search"></i><?php esc_html_e( 'Search', 'woocommerce-alidropship' ) ?>
                            </label>
                            <input type="text" class="<?php echo esc_attr( self::set( 'shipping-company-search' ) ) ?>"
                                   placeholder="<?php esc_attr_e( 'Enter shipping company name to search', 'woocommerce-alidropship' ) ?>">
                        </div>
                        <div class="<?php echo esc_attr( self::set( 'shipping-company-mask-table-container' ) ) ?>">
                            <table class="vi-ui celled table <?php echo esc_attr( self::set( 'shipping-company-mask-table' ) ) ?>">
                                <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Original shipping company name', 'woocommerce-alidropship' ) ?></th>
                                    <th><?php esc_html_e( 'Replacement', 'woocommerce-alidropship' ) ?></th>
                                </tr>
                                </thead>
                                <tbody class="<?php echo esc_attr( self::set( 'shipping-company-mask' ) ) ?>">
                                </tbody>
                            </table>
                        </div>
                        <div class="<?php echo esc_attr( self::set( array(
							'overlay',
						) ) ) ?>">
                            <div class="vi-ui indicating progress standard active <?php echo esc_attr( self::set( 'shipping-company-mask-progress' ) ) ?>">
                                <div class="label"></div>
                                <div class="bar">
                                    <div class="progress"></div>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php
					if ( self::$orders_tracking_active ) {
						if ( class_exists( 'VI_WOOCOMMERCE_ORDERS_TRACKING_DATA' ) ) {
							$carriers = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_carriers();
						} else {
							$orders_tracking_data = new VI_WOO_ORDERS_TRACKING_DATA();
							$carriers             = VI_WOO_ORDERS_TRACKING_DATA::shipping_carriers();
							$custom_carriers      = $orders_tracking_data->get_params( 'custom_carriers_list' );
							if ( $custom_carriers ) {
								$carriers = array_merge( $carriers, vi_wad_json_decode( $custom_carriers ) );
							}
						}
						?>
                        <div class="vi-ui bottom attached tab segment <?php self::set_params( 'tab-content', true ) ?>"
                             data-tab="tracking_carrier">
                            <div class="vi-ui positive tiny message">
                                <div class="header">
									<?php esc_html_e( 'Shipping company mapping', 'woocommerce-alidropship' ); ?>
                                </div>
                                <ul class="list">
                                    <li><?php _e( '<strong>Orders Tracking for WooCommerce</strong> plugin will set carrier for each tracking number based on shipping company', 'woocommerce-alidropship' ); ?></li>
                                </ul>
                            </div>
                            <table class="vi-ui celled table <?php self::set_params( 'shipping-company-mapping', true ) ?>">
                                <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Shipping company(selected when fulfilling)', 'woocommerce-alidropship' ); ?></th>
                                    <th><?php esc_html_e( 'Shipping carrier', 'woocommerce-alidropship' ); ?></th>
                                </tr>
                                </thead>
                                <tbody>
								<?php
								$shipping_company_mapping = self::$settings->get_params( 'shipping_company_mapping' );
								foreach ( $shipping_companies as $sc_id => $sc_name ) {
									?>
                                    <tr>
                                        <td class="<?php self::set_params( 'shipping-company-mapping-name-td', true ) ?>">
                                            <input type="text" readonly
                                                   value="<?php echo esc_attr( is_array( $sc_name ) ? $sc_name['origin'] : $sc_name ) ?>"
                                                   class="<?php self::set_params( 'shipping-company-mapping-name', true ) ?>">
                                        </td>
                                        <td>
                                            <select class="vi-ui fluid search dropdown <?php self::set_params( 'shipping-company-mapping-carrier', true ) ?>"
                                                    name="<?php self::set_params( 'shipping_company_mapping[' . $sc_id . ']' ) ?>">
                                                <option value=""></option>
												<?php
												foreach ( $carriers as $carrier ) {
													$selected = '';
													if ( isset( $shipping_company_mapping[ $sc_id ] ) && $shipping_company_mapping[ $sc_id ] === $carrier['slug'] ) {
														$selected = 'selected';
													}
													?>
                                                    <option value="<?php echo esc_attr( $carrier['slug'] ) ?>" <?php echo esc_attr( $selected ) ?>><?php echo esc_html( $carrier['name'] ) ?></option>
													<?php
												}
												?>
                                            </select>
                                        </td>
                                    </tr>
									<?php
								}
								?>
                                </tbody>
                            </table>
                            <div class="vi-ui positive tiny message">
                                <div class="header">
									<?php esc_html_e( 'Search and Replace', 'woocommerce-alidropship' ); ?>
                                </div>
                                <ul class="list">
                                    <li><?php _e( 'This feature is used for <strong>Orders Tracking for WooCommerce</strong> plugin when syncing tracking info.', 'woocommerce-alidropship' ); ?></li>
                                    <li><?php _e( 'When syncing orders with AliExpress, if Orders Tracking for WooCommerce plugin is active, it will automatically search for carrier URL in the existing carriers of this plugin (The <strong>Search and Replace</strong> function runs right before this step). If found, it will save tracking info with that carrier; otherwise, a new <strong>Custom carrier</strong> will be created.', 'woocommerce-alidropship' ); ?></li>
                                    <li><?php _e( 'Skip if carrier is <strong>AliExpress Standard Shipping</strong>', 'woocommerce-alidropship' ); ?></li>
                                </ul>
                            </div>
                            <div class="vi-ui segment string-replace-url">
                                <div class="vi-ui blue tiny message">
                                    <div class="header">
										<?php esc_html_e( 'Replace carrier URL', 'woocommerce-alidropship' ); ?>
                                    </div>
                                    <ul class="list">
                                        <li><?php esc_html_e( 'Replace carrier URL with respective URL below if DOMAIN of original carrier URL contains search strings(case-insensitive).', 'woocommerce-alidropship' ); ?></li>
                                        <li><?php esc_html_e( 'Search will take place with priority from top to bottom and will STOP after first match.', 'woocommerce-alidropship' ); ?></li>
                                    </ul>
                                </div>
                                <table class="vi-ui celled table">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Search', 'woocommerce-alidropship' ); ?></th>
                                        <th><?php esc_html_e( 'Replace carrier URL with', 'woocommerce-alidropship' ); ?></th>
                                        <th style="width: 1%"><?php esc_html_e( 'Remove', 'woocommerce-alidropship' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php
									$carrier_url_replaces       = self::$settings->get_params( 'carrier_url_replaces' );
									$carrier_url_replaces_count = 1;
									if ( ! empty( $carrier_url_replaces['from_string'] ) && ! empty( $carrier_url_replaces['to_string'] ) && is_array( $carrier_url_replaces['from_string'] ) ) {
										$carrier_url_replaces_count = count( $carrier_url_replaces['from_string'] );
									}
									for ( $i = 0; $i < $carrier_url_replaces_count; $i ++ ) {
										?>
                                        <tr class="clone-source">
                                            <td>
                                                <input type="text"
                                                       value="<?php echo esc_attr( isset( $carrier_url_replaces['from_string'][ $i ] ) ? $carrier_url_replaces['from_string'][ $i ] : '' ) ?>"
                                                       name="<?php self::set_params( 'carrier_url_replaces[from_string][]' ) ?>">
                                            </td>
                                            <td>
                                                <input type="text"
                                                       placeholder="<?php esc_attr_e( 'URL of a replacement carrier', 'woocommerce-alidropship' ); ?>"
                                                       value="<?php echo esc_attr( isset( $carrier_url_replaces['to_string'][ $i ] ) ? $carrier_url_replaces['to_string'][ $i ] : '' ) ?>"
                                                       name="<?php self::set_params( 'carrier_url_replaces[to_string][]' ) ?>">
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="vi-ui button mini negative delete-string-replace-rule">
                                                    <i class="dashicons dashicons-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
										<?php
									}
									?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th colspan="4">
                                            <button type="button"
                                                    class="vi-ui button labeled icon mini positive add-string-replace-rule-url">
                                                <i class="icon plus"></i>
												<?php esc_html_e( 'Add', 'woocommerce-alidropship' ); ?>
                                            </button>
                                        </th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="vi-ui segment string-replace-name">
                                <div class="vi-ui blue tiny message">
                                    <div class="header">
										<?php esc_html_e( 'Search and replace strings in Carrier name', 'woocommerce-alidropship' ); ?>
                                    </div>
                                    <ul class="list">
                                        <li><?php esc_html_e( 'Search for strings in Carrier name and replace found strings with respective values.', 'woocommerce-alidropship' ); ?></li>
                                        <li><?php _e( 'This only works when new <strong>Custom carrier</strong> is created', 'woocommerce-alidropship' ); ?></li>
                                    </ul>
                                </div>
                                <table class="vi-ui celled table">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Search', 'woocommerce-alidropship' ); ?></th>
                                        <th><?php esc_html_e( 'Case Sensitive', 'woocommerce-alidropship' ); ?></th>
                                        <th><?php esc_html_e( 'Replace', 'woocommerce-alidropship' ); ?></th>
                                        <th style="width: 1%"><?php esc_html_e( 'Remove', 'woocommerce-alidropship' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php
									$carrier_name_replaces       = self::$settings->get_params( 'carrier_name_replaces' );
									$carrier_name_replaces_count = 1;
									if ( ! empty( $carrier_name_replaces['from_string'] ) && ! empty( $carrier_name_replaces['to_string'] ) && is_array( $carrier_name_replaces['from_string'] ) ) {
										$carrier_name_replaces_count = count( $carrier_name_replaces['from_string'] );
									}
									for ( $i = 0; $i < $carrier_name_replaces_count; $i ++ ) {
										$checked = $case_sensitive = '';
										if ( ! empty( $carrier_name_replaces['sensitive'][ $i ] ) ) {
											$checked        = 'checked';
											$case_sensitive = 1;
										}
										?>
                                        <tr class="clone-source">
                                            <td>
                                                <input type="text"
                                                       value="<?php echo esc_attr( isset( $carrier_name_replaces['from_string'][ $i ] ) ? $carrier_name_replaces['from_string'][ $i ] : '' ) ?>"
                                                       name="<?php self::set_params( 'carrier_name_replaces[from_string][]' ) ?>">
                                            </td>
                                            <td>
                                                <div class="<?php echo esc_attr( self::set( 'string-replace-sensitive-container' ) ) ?>">
                                                    <input type="checkbox"
                                                           value="1" <?php echo esc_attr( $checked ) ?>
                                                           class="<?php echo esc_attr( self::set( 'string-replace-sensitive' ) ) ?>">
                                                    <input type="hidden"
                                                           class="<?php echo esc_attr( self::set( 'string-replace-sensitive-value' ) ) ?>"
                                                           value="<?php echo esc_attr( $case_sensitive ) ?>"
                                                           name="<?php self::set_params( 'carrier_name_replaces[sensitive][]' ) ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text"
                                                       placeholder="<?php esc_attr_e( 'Leave blank to delete matches', 'woocommerce-alidropship' ); ?>"
                                                       value="<?php echo esc_attr( isset( $carrier_name_replaces['to_string'][ $i ] ) ? $carrier_name_replaces['to_string'][ $i ] : '' ) ?>"
                                                       name="<?php self::set_params( 'carrier_name_replaces[to_string][]' ) ?>">
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="vi-ui button tiny negative delete-string-replace-rule">
                                                    <i class="dashicons dashicons-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
										<?php
									}
									?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th colspan="4">
                                            <button type="button"
                                                    class="vi-ui button labeled icon mini positive add-string-replace-rule-name">
                                                <i class="icon plus"></i>
												<?php esc_html_e( 'Add', 'woocommerce-alidropship' ); ?>
                                            </button>
                                        </th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
						<?php
					}
					?>
                    <div class="vi-ui bottom attached tab segment" data-tab="update">
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="auto-update-key"><?php esc_html_e( 'Auto Update Key', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="fields">
                                        <div class="ten wide field">
                                            <input type="text"
                                                   name="<?php self::set_params( 'key' ) ?>"
                                                   id="auto-update-key"
                                                   class="villatheme-autoupdate-key-field"
                                                   value="<?php echo esc_attr( self::$settings->get_params( 'key' ) ); ?>">
                                        </div>
                                        <div class="six wide field">
                                        <span class="vi-ui button small green villatheme-get-key-button"
                                              data-href="https://api.envato.com/authorization?response_type=code&client_id=villatheme-download-keys-6wzzaeue&redirect_uri=https://villatheme.com/update-key"
                                              data-id="29457839"><?php esc_html_e( 'Get Key', 'woocommerce-alidropship' ) ?></span>
                                        </div>
                                    </div>
									<?php do_action( 'woocommerce-alidropship_key' ) ?>
                                    <p><?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( __( 'Please enter the key that you get from <a target="_blank" href="https://villatheme.com/my-download">https://villatheme.com/my-download</a> to enable auto update and use AliExpress API. Please read <a target="_blank" href="https://villatheme.com/knowledge-base/how-to-use-auto-update-feature/">guide</a>', 'woocommerce-alidropship' ) ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="vi-ui message positive">
                                        <ul class="list">
                                            <li><?php _e( 'Access token is used for AliExpress API to bulk <a target="_blank" href="admin.php?page=woocommerce-alidropship-ali-orders">fulfill AliExpress orders</a> or to <a target="_self" href="#product_update">sync products</a>/<a target="_self" href="#fulfill">orders</a> automatically without using chrome extension', 'woocommerce-alidropship' ) ?></li>
                                            <li><?php _e( 'Auto update key is <strong>required</strong> and each key can be used for <strong>1 site</strong> only', 'woocommerce-alidropship' ) ?></li>
                                            <li><?php _e( 'Only Get new access token if your current access token is <strong>expired</strong> or <strong>invalid</strong>.', 'woocommerce-alidropship' ) ?></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="auto-update-key"><?php esc_html_e( 'AliExpress API', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <table class="vi-ui celled table <?php echo esc_attr( self::set( 'access-token-table' ) ) ?>">
                                        <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'AliExpress account', 'woocommerce-alidropship' ) ?></th>
                                            <th><?php esc_html_e( 'Expire time', 'woocommerce-alidropship' ) ?></th>
                                            <th><?php esc_html_e( 'Default', 'woocommerce-alidropship' ) ?></th>
                                            <th style="width:1%;"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php
										$access_tokens = self::$settings->get_params( 'access_tokens' );
										$access_token  = self::$settings->get_params( 'access_token' );
										self::access_tokens_list( $access_tokens, $access_token );
										?>
                                        </tbody>
                                    </table>
									<?php
									$button_class   = '';
									$button_class_1 = array( 'get-key-shortcut' );
									if ( self::$settings->get_params( 'key' ) ) {
										$button_class_1[] = 'hidden';
									} else {
										$button_class = 'disabled';
									}
									?>
                                    <span class="vi-ui button green mini <?php echo esc_attr( self::set( 'get-access-token' ) ) ?> <?php echo esc_attr( $button_class ) ?>"><?php esc_html_e( 'Get Access Token', 'woocommerce-alidropship' ) ?></span>
                                    <span class="<?php echo esc_attr( self::set( $button_class_1 ) ) ?>"><?php esc_html_e( 'Please get your auto update key to use this feature.', 'woocommerce-alidropship' ) ?><span
                                                class="vi-ui button green mini <?php echo esc_attr( self::set( 'get-key' ) ) ?>"><?php esc_html_e( 'Get Key', 'woocommerce-alidropship' ) ?></span></span>
                                    <span class="<?php echo esc_attr( self::set( array(
										'get-access-token-message',
										'hidden'
									) ) ) ?>"><i
                                                class="vi-ui icon check green"></i><?php esc_html_e( 'Successfully get new access token', 'woocommerce-alidropship' ) ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
					<?php
				}
				?>
                <div class="vi-ui bottom attached tab segment <?php self::set_params( 'tab-content', true ) ?>"
                     data-tab="products">
                    <table class="form-table">
                        <tbody>
                        <tr class="<?php self::set_params( 'product_status_container', true ) ?>">
                            <th>
                                <label for="<?php self::set_params( 'product_status', true ) ?>"><?php esc_html_e( 'Product status', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <select name="<?php self::set_params( 'product_status' ) ?>"
                                        id="<?php self::set_params( 'product_status', true ) ?>"
                                        class="<?php self::set_params( 'product_status', true ) ?> vi-ui dropdown">
                                    <option value="publish" <?php selected( self::$settings->get_params( 'product_status' ), 'publish' ) ?>><?php esc_html_e( 'Publish', 'woocommerce-alidropship' ) ?></option>
                                    <option value="pending" <?php selected( self::$settings->get_params( 'product_status' ), 'pending' ) ?>><?php esc_html_e( 'Pending', 'woocommerce-alidropship' ) ?></option>
                                    <option value="draft" <?php selected( self::$settings->get_params( 'product_status' ), 'draft' ) ?>><?php esc_html_e( 'Draft', 'woocommerce-alidropship' ) ?></option>
                                </select>
                                <p><?php esc_html_e( 'Imported products status will be set to this value.', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'product_sku', true ) ?>">
									<?php esc_html_e( 'Product sku', 'woocommerce-alidropship' ) ?>
                                </label>
                            </th>
                            <td>
                                <input id="<?php self::set_params( 'product_sku', true ) ?>"
                                       type="text"
                                       class="<?php self::set_params( 'product_sku', true ) ?>"
                                       value="<?php echo esc_attr( self::$settings->get_params( 'product_sku' ) ) ?>"
                                       name="<?php self::set_params( 'product_sku' ) ?>"/>
                                <p><?php _e( '<strong>{ali_product_id}</strong>: ID of AliExpress product', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'auto_generate_unique_sku', true ) ?>">
									<?php esc_html_e( 'Auto generate unique sku if exists', 'woocommerce-alidropship' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'auto_generate_unique_sku', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'auto_generate_unique_sku' ), 1 ) ?>
                                           tabindex="0"
                                           class="<?php self::set_params( 'auto_generate_unique_sku', true ) ?>"
                                           value="1"
                                           name="<?php self::set_params( 'auto_generate_unique_sku' ) ?>"/>
                                    <label><?php _e( 'When importing product in Import list, automatically generate unique sku by adding increment if sku exists', 'woocommerce-alidropship' ) ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'use_global_attributes', true ) ?>">
									<?php esc_html_e( 'Use global attributes', 'woocommerce-alidropship' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'use_global_attributes', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'use_global_attributes' ), 1 ) ?>
                                           tabindex="0"
                                           class="<?php self::set_params( 'use_global_attributes', true ) ?>" value="1"
                                           name="<?php self::set_params( 'use_global_attributes' ) ?>"/>
                                    <label><?php _e( 'Global attributes will be used instead of custom attributes. More details about <a href="https://woocommerce.com/document/managing-product-taxonomies/#product-attributes" target="_blank">Product attributes</a>', 'woocommerce-alidropship' ) ?></label>
                                </div>
                            </td>
                        </tr>
						<?php
						/*
						?>
						<tr>
							<th>
								<label for="<?php self::set_params( 'alternative_attribute_values', true ) ?>">
									<?php esc_html_e( 'Use alternative attribute values', 'woocommerce-alidropship' ) ?>
								</label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php self::set_params( 'alternative_attribute_values', true ) ?>"
										   type="checkbox" <?php checked( self::$settings->get_params( 'alternative_attribute_values' ), 1 ) ?>
										   tabindex="0"
										   class="<?php self::set_params( 'alternative_attribute_values', true ) ?>" value="1"
										   name="<?php self::set_params( 'alternative_attribute_values' ) ?>"/>
									<label><?php _e( 'Yes', 'woocommerce-alidropship' ) ?></label>
								</div>
								<p><?php esc_html_e( 'By default, the original attribute values as shown on AliExpress will be used. However, they sometimes do not have meaning and alternative attribute values may be better.', 'woocommerce-alidropship' ) ?></p>
								<p><?php esc_html_e( 'You can also switch between them while importing products from Import list.', 'woocommerce-alidropship' ) ?></p>
							</td>
						</tr>
						<?php
						*/
						if ( $is_main ) {
							?>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'show_shipping_option', true ) ?>">
										<?php esc_html_e( 'Show shipping option', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'show_shipping_option', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'show_shipping_option' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'show_shipping_option', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'show_shipping_option' ) ?>"/>
                                        <label><?php esc_html_e( 'Shipping cost will be added to price of original product before applying price rules. You can select shipping country/company to calculate shipping cost of products before importing.', 'woocommerce-alidropship' ) ?></label>
                                        <p><?php _e( '<strong>*Note:</strong> This is not shipping cost/method that your customers see at your store.', 'woocommerce-alidropship' ) ?></p>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'shipping_cost_after_price_rules', true ) ?>">
										<?php esc_html_e( 'Add shipping cost after price rules', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'shipping_cost_after_price_rules', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'shipping_cost_after_price_rules' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'shipping_cost_after_price_rules', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'shipping_cost_after_price_rules' ) ?>"/>
                                        <label><?php esc_html_e( 'Shipping cost will be added to price of original product after applying price rules.', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
							<?php
						}
						?>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'simple_if_one_variation', true ) ?>">
									<?php esc_html_e( 'Import as simple product', 'woocommerce-alidropship' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'simple_if_one_variation', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'simple_if_one_variation' ), 1 ) ?>
                                           tabindex="0"
                                           class="<?php self::set_params( 'simple_if_one_variation', true ) ?>"
                                           value="1"
                                           name="<?php self::set_params( 'simple_if_one_variation' ) ?>"/>
                                    <label><?php esc_html_e( 'If a product has only 1 variation or you select only 1 variation to import, that product will be imported as simple product. Variation sku and attributes will not be used.', 'woocommerce-alidropship' ) ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'catalog_visibility', true ) ?>"><?php esc_html_e( 'Catalog visibility', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <select name="<?php self::set_params( 'catalog_visibility' ) ?>"
                                        id="<?php self::set_params( 'catalog_visibility', true ) ?>"
                                        class="<?php self::set_params( 'catalog_visibility', true ) ?> vi-ui dropdown">
                                    <option value="visible" <?php selected( self::$settings->get_params( 'catalog_visibility' ), 'visible' ) ?>><?php esc_html_e( 'Shop and search results', 'woocommerce-alidropship' ) ?></option>
                                    <option value="catalog" <?php selected( self::$settings->get_params( 'catalog_visibility' ), 'catalog' ) ?>><?php esc_html_e( 'Shop only', 'woocommerce-alidropship' ) ?></option>
                                    <option value="search" <?php selected( self::$settings->get_params( 'catalog_visibility' ), 'search' ) ?>><?php esc_html_e( 'Search results only', 'woocommerce-alidropship' ) ?></option>
                                    <option value="hidden" <?php selected( self::$settings->get_params( 'catalog_visibility' ), 'hidden' ) ?>><?php esc_html_e( 'Hidden', 'woocommerce-alidropship' ) ?></option>
                                </select>
                                <p><?php esc_html_e( 'This setting determines which shop pages products will be listed on.', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'product_description', true ) ?>"><?php esc_html_e( 'Product description', 'woocommerce-alidropship' ) ?></label>
                            </th>
                            <td>
                                <select name="<?php self::set_params( 'product_description' ) ?>"
                                        id="<?php self::set_params( 'product_description', true ) ?>"
                                        class="<?php self::set_params( 'product_description', true ) ?> vi-ui dropdown">
                                    <option value="none" <?php selected( self::$settings->get_params( 'product_description' ), 'none' ) ?>><?php esc_html_e( 'None', 'woocommerce-alidropship' ) ?></option>
                                    <option value="item_specifics" <?php selected( self::$settings->get_params( 'product_description' ), 'item_specifics' ) ?>><?php esc_html_e( 'Item specifics', 'woocommerce-alidropship' ) ?></option>
                                    <option value="description" <?php selected( self::$settings->get_params( 'product_description' ), 'description' ) ?>><?php esc_html_e( 'Product Description', 'woocommerce-alidropship' ) ?></option>
                                    <option value="item_specifics_and_description" <?php selected( self::$settings->get_params( 'product_description' ), 'item_specifics_and_description' ) ?>><?php esc_html_e( 'Item specifics & Product Description', 'woocommerce-alidropship' ) ?></option>
                                </select>
                                <p><?php esc_html_e( 'Default product description when adding product to import list', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
						<?php
						if ( $is_main ) {
							?>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'use_external_image', true ) ?>">
										<?php esc_html_e( 'Use external links for images', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'use_external_image', true ) ?>"
                                               type="checkbox" <?php
										if ( class_exists( 'EXMAGE_WP_IMAGE_LINKS' ) ) {
											checked( self::$settings->get_params( 'use_external_image' ), 1 );
										} else {
											echo esc_attr( 'disabled' );
										}
										?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'use_external_image', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'use_external_image' ) ?>"/>
                                        <label><?php esc_html_e( 'This helps save storage by using original AliExpress image URLs but you will not be able to edit them', 'woocommerce-alidropship' ) ?></label>
                                    </div>
									<?php
									if ( ! class_exists( 'EXMAGE_WP_IMAGE_LINKS' ) ) {
										$plugins     = get_plugins();
										$plugin_slug = 'exmage-wp-image-links';
										$plugin      = "{$plugin_slug}/{$plugin_slug}.php";
										if ( ! isset( $plugins[ $plugin ] ) ) {
											$button = '<a href="' . esc_url( wp_nonce_url( self_admin_url( "update.php?action=install-plugin&plugin={$plugin_slug}" ), "install-plugin_{$plugin_slug}" ) ) . '" target="_blank" class="button button-primary">' . esc_html__( 'Install now', 'woocommerce-alidropship' ) . '</a>';;
										} else {
											$button = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
													'action' => 'activate',
													'plugin' => $plugin
												), admin_url( 'plugins.php' ) ), "activate-plugin_{$plugin}" ) ) . '" target="_blank" class="button button-primary">' . esc_html__( 'Activate now', 'woocommerce-alidropship' ) . '</a>';
										}
										?>
                                        <p>
                                            <strong>*</strong><?php printf( esc_html__( 'To use this feature, you have to install and activate %s plugin. %s', 'woocommerce-alidropship' ), '<a target="_blank" href="https://bit.ly/exmage">EXMAGE – WordPress Image Links</a>', $button ) ?>
                                        </p>
										<?php
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'download_description_images', true ) ?>">
										<?php esc_html_e( 'Import description images', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'download_description_images', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'download_description_images' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'download_description_images', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'download_description_images' ) ?>"/>
                                        <label><?php esc_html_e( 'Upload images in product description if any. If disabled, images in description will use the original AliExpress cdn links', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
							<?php
						}
						?>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'product_gallery', true ) ?>">
									<?php esc_html_e( 'Default select product images', 'woocommerce-alidropship' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'product_gallery', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'product_gallery' ), 1 ) ?>
                                           tabindex="0" class="<?php self::set_params( 'product_gallery', true ) ?>"
                                           value="1"
                                           name="<?php self::set_params( 'product_gallery' ) ?>"/>
                                    <label><?php esc_html_e( 'First image will be selected as product image and other images(except images from product description) are selected in gallery when adding product to import list', 'woocommerce-alidropship' ) ?></label>
                                </div>
                            </td>
                        </tr>
						<?php
						if ( $is_main ) {
							?>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'disable_background_process', true ) ?>">
										<?php esc_html_e( 'Disable background process', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'disable_background_process', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'disable_background_process' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'disable_background_process', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'disable_background_process' ) ?>"/>
                                        <label><?php esc_html_e( 'When importing products, instead of letting their images import in the background, main product image will be imported directly while gallery and variation images(if any) will be added to Failed images page so that you can go there to import them manually.', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
							<?php
						}
						?>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'product_categories', true ) ?>"><?php esc_html_e( 'Default categories', 'woocommerce-alidropship' ); ?></label>
                            </th>
                            <td>
                                <select name="<?php self::set_params( 'product_categories', false, true ) ?>"
                                        class="<?php self::set_params( 'product_categories', true ) ?> search-category"
                                        id="<?php self::set_params( 'product_categories', true ) ?>"
                                        multiple="multiple">
									<?php
									$categories = self::$settings->get_params( 'product_categories' );
									if ( is_array( $categories ) && count( $categories ) ) {
										foreach ( $categories as $category_id ) {
											$category = get_term( $category_id );
											if ( $category ) {
												?>
                                                <option value="<?php echo esc_attr( $category_id ) ?>"
                                                        selected><?php echo esc_html( VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::build_category_name( $category->name, $category ) ); ?></option>
												<?php
											}
										}
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'Imported products will be added to these categories.', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'product_shipping_class', true ) ?>"><?php esc_html_e( 'Default shipping class', 'woocommerce-alidropship' ); ?></label>
                            </th>
                            <td>
                                <select name="<?php self::set_params( 'product_shipping_class', false, false ) ?>"
                                        class="vi-ui dropdown search <?php self::set_params( 'product_shipping_class', true ) ?>"
                                        id="<?php self::set_params( 'product_shipping_class', true ) ?>">
                                    <option value=""><?php esc_html_e( 'No shipping class', 'woocommerce-alidropship' ) ?></option>
									<?php
									$shipping_classes       = get_terms(
										array(
											'taxonomy'   => 'product_shipping_class',
											'orderby'    => 'name',
											'order'      => 'ASC',
											'hide_empty' => false
										)
									);
									$product_shipping_class = self::$settings->get_params( 'product_shipping_class' );
									if ( is_array( $shipping_classes ) && count( $shipping_classes ) ) {
										foreach ( $shipping_classes as $shipping_class ) {
											?>
                                            <option value="<?php echo esc_attr( $shipping_class->term_id ) ?>"
												<?php selected( $shipping_class->term_id, $product_shipping_class ) ?>><?php echo esc_html( $shipping_class->name ); ?></option>
											<?php
										}
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'Shipping class selected here will also be selected by default in the Import list', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'product_tags', true ) ?>"><?php esc_html_e( 'Default product tags', 'woocommerce-alidropship' ); ?></label>
                            </th>
                            <td>
                                <select name="<?php self::set_params( 'product_tags', false, true ) ?>"
                                        class="<?php self::set_params( 'product_tags', true ) ?> search-tags"
                                        id="<?php self::set_params( 'product_tags', true ) ?>"
                                        multiple="multiple">
									<?php
									$product_tags = self::$settings->get_params( 'product_tags' );
									if ( is_array( $product_tags ) && count( $product_tags ) ) {
										foreach ( $product_tags as $product_tag_id ) {
											?>
                                            <option value="<?php echo esc_attr( $product_tag_id ) ?>"
                                                    selected><?php echo esc_html( $product_tag_id ); ?></option>
											<?php
										}
									}
									?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'variation_visible', true ) ?>">
									<?php esc_html_e( 'Product variations is visible on product page', 'woocommerce-alidropship' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'variation_visible', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'variation_visible' ), 1 ) ?>
                                           tabindex="0" class="<?php self::set_params( 'variation_visible', true ) ?>"
                                           value="1"
                                           name="<?php self::set_params( 'variation_visible' ) ?>"/>
                                    <label>
										<?php esc_html_e( 'Enable to make variations of imported products visible on product page', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'manage_stock', true ) ?>">
									<?php esc_html_e( 'Manage stock', 'woocommerce-alidropship' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'manage_stock', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'manage_stock' ), 1 ) ?>
                                           tabindex="0" class="<?php self::set_params( 'manage_stock', true ) ?>"
                                           value="1"
                                           name="<?php self::set_params( 'manage_stock' ) ?>"/>
                                    <label>
										<?php esc_html_e( 'Enable manage stock and import product inventory.', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </div>
                                <p><?php esc_html_e( 'If this option is disabled, products stock status will be set "Instock" and product inventory will not be imported', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'ignore_ship_from', true ) ?>">
									<?php esc_html_e( 'Remove Ship-from attribute', 'woocommerce-alidropship' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php self::set_params( 'ignore_ship_from', true ) ?>"
                                           type="checkbox" <?php checked( self::$settings->get_params( 'ignore_ship_from' ), 1 ) ?>
                                           tabindex="0" class="<?php self::set_params( 'ignore_ship_from', true ) ?>"
                                           value="1"
                                           name="<?php self::set_params( 'ignore_ship_from' ) ?>"/>
                                    <label>
										<?php esc_html_e( 'Automatically remove Ship-from attribute if any', 'woocommerce-alidropship' ) ?>
                                    </label>
                                    <p><?php esc_html_e( 'If Ship-from attribute of a product does not contain the selected "Default Ship-from country" below, Ship-from attribute will not be removed', 'woocommerce-alidropship' ) ?></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php self::set_params( 'ignore_ship_from_default', true ) ?>">
									<?php esc_html_e( 'Default Ship-from country', 'woocommerce-alidropship' ) ?>
                                </label>
                            </th>
                            <td>
                                <select id="<?php self::set_params( 'ignore_ship_from_default', true ) ?>"
                                        class="vi-ui dropdown"
                                        name="<?php self::set_params( 'ignore_ship_from_default' ) ?>">
									<?php
									$ignore_ship_from_default = self::$settings->get_params( 'ignore_ship_from_default' );
									$ship_from                = array(
										'CN',
										'RU',
										'PL',
										'BE',
										'ES',
										'FR',
										'US',
										'DE',
										'UA',
										'UK',
										'AU',
										'CZ',
										'IT',
										'TR',
										'AE',
										'ZA',
										'ID',
										'CL',
										'BR',
										'VN',
										'IL',
										'SA',
										'KR'
									);
									$countries                = WC()->countries->get_countries();
									foreach ( $ship_from as $ship_from_country ) {
										?>
                                        <option value="<?php echo esc_attr( $ship_from_country ) ?>" <?php selected( $ship_from_country, $ignore_ship_from_default ) ?>><?php echo esc_html( isset( $countries[ $ship_from_country ] ) ? $countries[ $ship_from_country ] : '' ); ?></option>
										<?php
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'When Ship-from is removed from a product, keep this country as the default value of Ship-from attribute for that product to fulfill AliExpress orders', 'woocommerce-alidropship' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="vi-ui segment find-and-replace">
                                    <div class="vi-ui blue small message">
                                        <div class="header">
											<?php esc_html_e( 'Find and Replace', 'woocommerce-alidropship' ); ?>
                                        </div>
                                        <ul class="list">
                                            <li><?php esc_html_e( 'The first table is to find and replace product specifications by name', 'woocommerce-alidropship' ); ?></li>
                                            <li><?php esc_html_e( 'The second table is to search for strings in product title and description and replace found strings with respective values.', 'woocommerce-alidropship' ); ?></li>
                                        </ul>
                                    </div>
                                    <table class="vi-ui celled table specification-replace">
                                        <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Specification Name', 'woocommerce-alidropship' ); ?></th>
                                            <th><?php esc_html_e( 'Case Sensitive', 'woocommerce-alidropship' ); ?></th>
                                            <th><?php esc_html_e( 'Specification New Name', 'woocommerce-alidropship' ); ?></th>
                                            <th><?php esc_html_e( 'Specification New Value', 'woocommerce-alidropship' ); ?></th>
                                            <th><?php esc_html_e( 'Remove', 'woocommerce-alidropship' ); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php
										$specification_replace       = self::$settings->get_params( 'specification_replace' );
										$specification_replace_count = 1;
										if ( ! empty( $specification_replace['from_name'] ) && ! empty( $specification_replace['to_name'] ) && is_array( $specification_replace['from_name'] ) ) {
											$specification_replace_count = count( $specification_replace['from_name'] );
										}
										for ( $i = 0; $i < $specification_replace_count; $i ++ ) {
											$checked = $case_sensitive = '';
											if ( ! empty( $specification_replace['sensitive'][ $i ] ) ) {
												$checked        = 'checked';
												$case_sensitive = 1;
											}
											?>
                                            <tr class="clone-source">
                                                <td>
                                                    <input type="text"
                                                           value="<?php echo esc_attr( isset( $specification_replace['from_name'][ $i ] ) ? $specification_replace['from_name'][ $i ] : '' ) ?>"
                                                           name="<?php self::set_params( 'specification_replace[from_name][]' ) ?>">
                                                </td>
                                                <td>
                                                    <div class="<?php self::set_params( 'specification-replace-sensitive-container', true ) ?>">
                                                        <input type="checkbox"
                                                               value="1" <?php echo esc_attr( $checked ) ?>
                                                               class="<?php self::set_params( 'specification-replace-sensitive', true ) ?>">
                                                        <input type="hidden"
                                                               class="<?php self::set_params( 'specification-replace-sensitive-value', true ) ?>"
                                                               value="<?php echo esc_attr( $case_sensitive ) ?>"
                                                               name="<?php self::set_params( 'specification_replace[sensitive][]' ) ?>">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           placeholder="<?php esc_attr_e( 'Leave blank to delete matches', 'woocommerce-alidropship' ); ?>"
                                                           value="<?php echo esc_attr( isset( $specification_replace['to_name'][ $i ] ) ? $specification_replace['to_name'][ $i ] : '' ) ?>"
                                                           name="<?php self::set_params( 'specification_replace[to_name][]' ) ?>">
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           placeholder="{old_value}"
                                                           value="<?php echo esc_attr( isset( $specification_replace['new_value'][ $i ] ) ? $specification_replace['new_value'][ $i ] : '' ) ?>"
                                                           name="<?php self::set_params( 'specification_replace[new_value][]' ) ?>">
                                                </td>
                                                <td>
                                                    <button type="button"
                                                            class="vi-ui button negative mini delete-specification-replace-rule">
                                                        <i class="dashicons dashicons-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
											<?php
										}
										?>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th colspan="5">
                                                <button type="button"
                                                        class="vi-ui button labeled icon positive add-specification-replace-rule mini">
                                                    <i class="icon plus"></i>
													<?php esc_html_e( 'Add', 'woocommerce-alidropship' ); ?>
                                                </button>
                                            </th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                    <table class="vi-ui celled table string-replace">
                                        <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Search', 'woocommerce-alidropship' ); ?></th>
                                            <th><?php esc_html_e( 'Case Sensitive', 'woocommerce-alidropship' ); ?></th>
                                            <th><?php esc_html_e( 'Replace with', 'woocommerce-alidropship' ); ?></th>
                                            <th><?php esc_html_e( 'Remove', 'woocommerce-alidropship' ); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php
										$string_replace       = self::$settings->get_params( 'string_replace' );
										$string_replace_count = 1;
										if ( ! empty( $string_replace['from_string'] ) && ! empty( $string_replace['to_string'] ) && is_array( $string_replace['from_string'] ) ) {
											$string_replace_count = count( $string_replace['from_string'] );
										}
										for ( $i = 0; $i < $string_replace_count; $i ++ ) {
											$checked = $case_sensitive = '';
											if ( ! empty( $string_replace['sensitive'][ $i ] ) ) {
												$checked        = 'checked';
												$case_sensitive = 1;
											}
											?>
                                            <tr class="clone-source">
                                                <td>
                                                    <input type="text"
                                                           value="<?php echo esc_attr( isset( $string_replace['from_string'][ $i ] ) ? $string_replace['from_string'][ $i ] : '' ) ?>"
                                                           name="<?php self::set_params( 'string_replace[from_string][]' ) ?>">
                                                </td>
                                                <td>
                                                    <div class="<?php self::set_params( 'string-replace-sensitive-container', true ) ?>">
                                                        <input type="checkbox"
                                                               value="1" <?php echo esc_attr( $checked ) ?>
                                                               class="<?php self::set_params( 'string-replace-sensitive', true ) ?>">
                                                        <input type="hidden"
                                                               class="<?php self::set_params( 'string-replace-sensitive-value', true ) ?>"
                                                               value="<?php echo esc_attr( $case_sensitive ) ?>"
                                                               name="<?php self::set_params( 'string_replace[sensitive][]' ) ?>">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           placeholder="<?php esc_attr_e( 'Leave blank to delete matches', 'woocommerce-alidropship' ); ?>"
                                                           value="<?php echo esc_attr( isset( $string_replace['to_string'][ $i ] ) ? $string_replace['to_string'][ $i ] : '' ) ?>"
                                                           name="<?php self::set_params( 'string_replace[to_string][]' ) ?>">
                                                </td>
                                                <td>
                                                    <button type="button"
                                                            class="vi-ui button negative mini delete-string-replace-rule">
                                                        <i class="dashicons dashicons-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
											<?php
										}
										?>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th colspan="4">
                                                <button type="button"
                                                        class="vi-ui button labeled icon positive add-string-replace-rule mini">
                                                    <i class="icon plus"></i>
													<?php esc_html_e( 'Add', 'woocommerce-alidropship' ); ?>
                                                </button>
                                            </th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="vi-ui bottom attached tab segment <?php self::set_params( 'tab-content', true ) ?>"
                     data-tab="price">
                    <div class="vi-ui yellow small message">
                        <div class="header">
							<?php esc_html_e( 'Important', 'woocommerce-alidropship' ); ?>
                        </div>
                        <ul class="list">
                            <li><?php esc_html_e( 'Products are imported in USD, the price of imported products will be converted after applying the price rules below', 'woocommerce-alidropship' ); ?></li>
                            <li><?php esc_html_e( 'The product sync functionality also uses below price rules/price format rules', 'woocommerce-alidropship' ); ?></li>
                            <li><?php esc_html_e( 'If you change price rules/price format rules and want changes to be applied to imported products(pushed to store), you have to enable price sync then sync all products', 'woocommerce-alidropship' ); ?></li>
                        </ul>
                    </div>
					<?php
					if ( $is_main ) {
						?>
                        <table class="form-table">
                            <tbody>
							<?php
							self::exchange_rate_fields();
							?>
                            <tr>
                                <td colspan="2">
									<?php
									if ( self::$next_schedule ) {
										$gmt_offset = intval( get_option( 'gmt_offset' ) );
										?>
                                        <div class="vi-ui positive message"><?php printf( __( 'Next schedule: <strong>%s</strong>', 'woocommerce-alidropship' ), date_i18n( 'F j, Y g:i:s A', ( self::$next_schedule + HOUR_IN_SECONDS * $gmt_offset ) ) ); ?></div>
										<?php
									} else {
										?>
                                        <div class="vi-ui negative message"><?php esc_html_e( 'Exchange rate auto-update is currently DISABLED', 'woocommerce-alidropship' );; ?></div>
										<?php
									}
									?>
                                </td>
                            </tr>
							<?php
							$exchange_rate_auto          = self::$settings->get_params( 'exchange_rate_auto' );
							$exchange_rate_options_class = array( 'exchange-rate-options' );
							if ( ! $exchange_rate_auto ) {
								$exchange_rate_options_class[] = 'hidden';
							}
							?>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'exchange_rate_auto', true ) ?>"><?php esc_html_e( 'Update rate automatically', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="<?php self::set_params( 'exchange_rate_auto' ) ?>"
                                               id="<?php self::set_params( 'exchange_rate_auto', true ) ?>"
                                               class="<?php self::set_params( 'exchange_rate_auto', true ) ?>"
                                               value="1" <?php checked( $exchange_rate_auto, 1 ) ?>>
                                        <label></label>
                                    </div>
                                </td>
                            </tr>
							<?php
							$exchange_rate_interval = self::$settings->get_params( 'exchange_rate_interval' );
							if ( intval( $exchange_rate_interval ) < 1 ) {
								$exchange_rate_interval = 1;
							}
							?>
                            <tr class="<?php echo esc_attr( self::set( $exchange_rate_options_class ) ) ?>">
                                <th>
                                    <label for="<?php self::set_params( 'exchange_rate_interval', true ) ?>"><?php esc_html_e( 'Update rate every', 'woocommerce-alidropship' ) ?></label>
                                <td>
                                    <div class="vi-ui right labeled input">
                                        <input type="number" min="1"
                                               name="<?php self::set_params( 'exchange_rate_interval' ) ?>"
                                               id="<?php echo esc_attr( self::set( 'exchange_rate_interval' ) ) ?>"
                                               value="<?php echo esc_attr( $exchange_rate_interval ) ?>">
                                        <label for="<?php echo esc_attr( self::set( 'exchange_rate_interval' ) ) ?>"
                                               class="vi-ui label"><?php esc_html_e( 'Day(s)', 'woocommerce-alidropship' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( self::set( $exchange_rate_options_class ) ) ?>">
                                <th>
                                    <label for="<?php echo esc_attr( self::set( 'exchange_rate_hour' ) ) ?>"><?php esc_html_e( 'Update rate at', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <label for="<?php echo esc_attr( self::set( 'exchange_rate_hour' ) ) ?>"
                                                       class="vi-ui label"><?php esc_html_e( 'Hour', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number" min="0" max="23"
                                                       name="<?php self::set_params( 'exchange_rate_hour' ) ?>"
                                                       id="<?php echo esc_attr( self::set( 'exchange_rate_hour' ) ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'exchange_rate_hour' ) ) ?>">
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <label for="<?php echo esc_attr( self::set( 'exchange_rate_minute' ) ) ?>"
                                                       class="vi-ui label"><?php esc_html_e( 'Minute', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number" min="0" max="59"
                                                       name="<?php self::set_params( 'exchange_rate_minute' ) ?>"
                                                       id="<?php echo esc_attr( self::set( 'exchange_rate_minute' ) ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'exchange_rate_minute' ) ) ?>">
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <label for="<?php echo esc_attr( self::set( 'exchange_rate_second' ) ) ?>"
                                                       class="vi-ui label"><?php esc_html_e( 'Second', 'woocommerce-alidropship' ) ?></label>
                                                <input type="number" min="0" max="59"
                                                       name="<?php self::set_params( 'exchange_rate_second' ) ?>"
                                                       id="<?php echo esc_attr( self::set( 'exchange_rate_second' ) ) ?>"
                                                       value="<?php echo esc_attr( self::$settings->get_params( 'exchange_rate_second' ) ) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
						<?php
					}
					?>
                    <div class="vi-ui segment <?php self::set_params( 'price_rule_wrapper', true ) ?>">
                        <div class="vi-ui positive small message">
							<?php esc_html_e( 'For each price, first matched rule(from top to bottom) will be applied. If no rules match, the default will be used.', 'woocommerce-alidropship' ) ?>
                        </div>
                        <table class="vi-ui celled table price-rule">
							<?php
							self::price_rule_table_head();
							$decimals      = wc_get_price_decimals();
							$decimals_unit = 1;
							if ( $decimals > 0 ) {
								$decimals_unit = pow( 10, ( - 1 * $decimals ) );
							}
							$price_from      = self::$settings->get_params( 'price_from' );
							$price_default   = self::$settings->get_params( 'price_default' );
							$price_to        = self::$settings->get_params( 'price_to' );
							$plus_value      = self::$settings->get_params( 'plus_value' );
							$plus_sale_value = self::$settings->get_params( 'plus_sale_value' );
							$plus_value_type = self::$settings->get_params( 'plus_value_type' );
							?>
                            <tbody class="<?php self::set_params( 'price_rule_container', true ) ?> ui-sortable">
							<?php
							$price_from_count = count( $price_from );
							if ( $price_from_count > 0 ) {
								/*adjust price rules since version 1.0.1.1*/
								if ( ! is_array( $price_to ) || count( $price_to ) !== $price_from_count ) {
									if ( $price_from_count > 1 ) {
										$price_to   = array_values( array_slice( $price_from, 1 ) );
										$price_to[] = '';
									} else {
										$price_to = array( '' );
									}
								}
								for ( $i = 0; $i < count( $price_from ); $i ++ ) {
									switch ( $plus_value_type[ $i ] ) {
										case 'fixed':
											$value_label_left  = '+';
											$value_label_right = '$';
											break;
										case 'percent':
											$value_label_left  = '+';
											$value_label_right = '%';
											break;
										case 'multiply':
											$value_label_left  = 'x';
											$value_label_right = '';
											break;
										default:
											$value_label_left  = '=';
											$value_label_right = '$';
									}
									?>
                                    <tr class="<?php self::set_params( 'price_rule_row', true ) ?>">
                                        <td>
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <div class="vi-ui left labeled input fluid">
                                                        <label for="amount" class="vi-ui label">$</label>
                                                        <input
                                                                step="any"
                                                                type="number"
                                                                min="0"
                                                                value="<?php echo esc_attr( $price_from[ $i ] ); ?>"
                                                                name="<?php self::set_params( 'price_from', false, true ); ?>"
                                                                class="<?php self::set_params( 'price_from', true ); ?>">
                                                    </div>
                                                </div>
                                                <span class="<?php self::set_params( 'price_from_to_separator', true ); ?>">-</span>
                                                <div class="field">
                                                    <div class="vi-ui left labeled input fluid">
                                                        <label for="amount" class="vi-ui label">$</label>
                                                        <input
                                                                step="any"
                                                                type="number"
                                                                min="0"
                                                                value="<?php echo esc_attr( $price_to[ $i ] ); ?>"
                                                                name="<?php self::set_params( 'price_to', false, true ); ?>"
                                                                class="<?php self::set_params( 'price_to', true ); ?>">
                                                    </div>
                                                </div>

                                            </div>
                                        </td>
                                        <td>
                                            <select name="<?php self::set_params( 'plus_value_type', false, true ); ?>"
                                                    class="vi-ui fluid dropdown <?php self::set_params( 'plus_value_type', true ); ?>">
                                                <option value="fixed" <?php selected( $plus_value_type[ $i ], 'fixed' ) ?>><?php esc_html_e( 'Increase by Fixed amount($)', 'woocommerce-alidropship' ) ?></option>
                                                <option value="percent" <?php selected( $plus_value_type[ $i ], 'percent' ) ?>><?php esc_html_e( 'Increase by Percentage(%)', 'woocommerce-alidropship' ) ?></option>
                                                <option value="multiply" <?php selected( $plus_value_type[ $i ], 'multiply' ) ?>><?php esc_html_e( 'Multiply with', 'woocommerce-alidropship' ) ?></option>
                                                <option value="set_to" <?php selected( $plus_value_type[ $i ], 'set_to' ) ?>><?php esc_html_e( 'Set to', 'woocommerce-alidropship' ) ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="vi-ui right labeled input fluid">
                                                <label for="amount"
                                                       class="vi-ui label <?php self::set_params( 'value-label-left', true ); ?>"><?php echo esc_html( $value_label_left ) ?></label>
                                                <input type="number" min="-1" step="any"
                                                       value="<?php echo esc_attr( $plus_sale_value[ $i ] ); ?>"
                                                       name="<?php self::set_params( 'plus_sale_value', false, true ); ?>"
                                                       class="<?php self::set_params( 'plus_sale_value', true ); ?>">
                                                <div class="vi-ui basic label <?php self::set_params( 'value-label-right', true ); ?>"><?php echo esc_html( $value_label_right ) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="vi-ui right labeled input fluid">
                                                <label for="amount"
                                                       class="vi-ui label <?php self::set_params( 'value-label-left', true ); ?>"><?php echo esc_html( $value_label_left ) ?></label>
                                                <input type="number" min="0" step="any"
                                                       value="<?php echo esc_attr( $plus_value[ $i ] ); ?>"
                                                       name="<?php self::set_params( 'plus_value', false, true ); ?>"
                                                       class="<?php self::set_params( 'plus_value', true ); ?>">
                                                <div class="vi-ui basic label <?php self::set_params( 'value-label-right', true ); ?>"><?php echo esc_html( $value_label_right ) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="">
                                                <span class="vi-ui button icon negative mini <?php self::set_params( 'price_rule_remove', true ) ?>"
                                                      title="<?php esc_attr_e( 'Remove', 'woocommerce-alidropship' ) ?>"><i
                                                            class="icon trash"></i></span>
                                            </div>
                                        </td>
                                    </tr>
									<?php
								}
							}
							?>
                            </tbody>
                            <tfoot>
							<?php
							$plus_value_type_d = isset( $price_default['plus_value_type'] ) ? $price_default['plus_value_type'] : 'multiply';
							$plus_sale_value_d = isset( $price_default['plus_sale_value'] ) ? $price_default['plus_sale_value'] : 1;
							$plus_value_d      = isset( $price_default['plus_value'] ) ? $price_default['plus_value'] : 2;
							switch ( $plus_value_type_d ) {
								case 'fixed':
									$value_label_left  = '+';
									$value_label_right = '$';
									break;
								case 'percent':
									$value_label_left  = '+';
									$value_label_right = '%';
									break;
								case 'multiply':
									$value_label_left  = 'x';
									$value_label_right = '';
									break;
								default:
									$value_label_left  = '=';
									$value_label_right = '$';
							}
							?>
                            <tr class="<?php echo esc_attr( self::set( array( 'price-rule-row-default' ) ) ) ?>">
                                <th><?php esc_html_e( 'Default', 'woocommerce-alidropship' ) ?></th>
                                <th>
                                    <select name="<?php self::set_params( 'price_default[plus_value_type]', false ); ?>"
                                            class="vi-ui fluid dropdown <?php self::set_params( 'plus_value_type', true ); ?>">
                                        <option value="fixed" <?php selected( $plus_value_type_d, 'fixed' ) ?>><?php esc_html_e( 'Increase by Fixed amount($)', 'woocommerce-alidropship' ) ?></option>
                                        <option value="percent" <?php selected( $plus_value_type_d, 'percent' ) ?>><?php esc_html_e( 'Increase by Percentage(%)', 'woocommerce-alidropship' ) ?></option>
                                        <option value="multiply" <?php selected( $plus_value_type_d, 'multiply' ) ?>><?php esc_html_e( 'Multiply with', 'woocommerce-alidropship' ) ?></option>
                                        <option value="set_to" <?php selected( $plus_value_type_d, 'set_to' ) ?>><?php esc_html_e( 'Set to', 'woocommerce-alidropship' ) ?></option>
                                    </select>
                                </th>
                                <th>
                                    <div class="vi-ui right labeled input fluid">
                                        <label for="amount"
                                               class="vi-ui label <?php self::set_params( 'value-label-left', true ); ?>"><?php echo esc_html( $value_label_left ) ?></label>
                                        <input type="number" min="-1" step="any"
                                               value="<?php echo esc_attr( $plus_sale_value_d ); ?>"
                                               name="<?php self::set_params( 'price_default[plus_sale_value]', false ); ?>"
                                               class="<?php self::set_params( 'plus_sale_value', true ); ?>">
                                        <div class="vi-ui basic label <?php self::set_params( 'value-label-right', true ); ?>"><?php echo esc_html( $value_label_right ) ?></div>
                                    </div>
                                </th>
                                <th>
                                    <div class="vi-ui right labeled input fluid">
                                        <label for="amount"
                                               class="vi-ui label <?php self::set_params( 'value-label-left', true ); ?>"><?php echo esc_html( $value_label_left ) ?></label>
                                        <input type="number" min="0" step="any"
                                               value="<?php echo esc_attr( $plus_value_d ); ?>"
                                               name="<?php self::set_params( 'price_default[plus_value]', false ); ?>"
                                               class="<?php self::set_params( 'plus_value', true ); ?>">
                                        <div class="vi-ui basic label <?php self::set_params( 'value-label-right', true ); ?>"><?php echo esc_html( $value_label_right ) ?></div>
                                    </div>
                                </th>
                                <th>
                                </th>
                            </tr>
                            </tfoot>
                        </table>
                        <span class="<?php self::set_params( 'price_rule_add', true ) ?> vi-ui button labeled icon positive mini"
                              title="<?php esc_attr_e( 'Add a new price range', 'woocommerce-alidropship' ) ?>"><i
                                    class="icon add"></i><?php esc_html_e( 'Add price range', 'woocommerce-alidropship' ); ?></span>
                    </div>
                    <div class="vi-ui segment">
                        <div class="vi-ui positive small message">
                            <div class="header">
								<?php esc_html_e( 'How does it work?', 'woocommerce-alidropship' ); ?>
                            </div>
                            <ul class="list">
                                <li><?php esc_html_e( 'Rules will be looped from top to bottom grouped by Compared part to find matches', 'woocommerce-alidropship' ); ?></li>
                                <li><?php esc_html_e( 'Your input price can only be applied by 1 rule for each part(fraction/integer)=>maximum 2 rules in total(1 for Integer part and 1 for Fraction part)', 'woocommerce-alidropship' ); ?></li>
                                <li><?php esc_html_e( 'Rules for Fraction part will be applied before rules for Integer part', 'woocommerce-alidropship' ); ?></li>
                            </ul>
                            <div class="header">
								<?php esc_html_e( 'Rules for Fraction part', 'woocommerce-alidropship' ); ?>
                            </div>
                            <ul class="list">
                                <li><?php _e( 'Leave Price range <strong>empty</strong> to apply to all prices that have decimal part matches the Compared part range', 'woocommerce-alidropship' ); ?></li>
                                <li><?php _e( 'Leave Compared part range <strong>empty</strong> to apply to all prices in the Price range', 'woocommerce-alidropship' ); ?></li>
                                <li><?php _e( 'Can use an <strong>x</strong> in New value of compared part to remain the respective digit in the Compared part of input price', 'woocommerce-alidropship' ); ?></li>
                                <li><?php printf( _n( 'New value of compared part can contain maximum %s digit which is the Number of decimals in your <a href="admin.php?page=wc-settings#woocommerce_price_num_decimals" target="_blank">WooCommerce settings</a>', 'New value of compared part can contain maximum %s digits which is the Number of decimals in your <a href="admin.php?page=wc-settings#woocommerce_price_num_decimals" target="_blank">WooCommerce settings</a>', $decimals, 'woocommerce-alidropship' ), $decimals ); ?></li>
                            </ul>
                            <div class="header">
								<?php esc_html_e( 'Rules for Integer part', 'woocommerce-alidropship' ); ?>
                            </div>
                            <ul class="list">
                                <li><?php esc_html_e( 'Maximum number of digits of Compared part range is 1 subtracted from the minimum number of digits of Price range', 'woocommerce-alidropship' ); ?></li>
                                <li><?php esc_html_e( 'Maximum number of digits of New value of compared part is the maximum number of digits of Compared part range', 'woocommerce-alidropship' ); ?></li>
                                <li><?php _e( 'Leave Compared part range <strong>empty</strong> to apply to all prices in the Price range', 'woocommerce-alidropship' ); ?></li>
                            </ul>
                            <div class="vi-ui segment">
                                <div class="vi-ui accordion">
                                    <div class="title"><?php esc_html_e( 'View detailed example with explanation', 'woocommerce-alidropship' ) ?></div>
                                    <div class="content"><img
                                                src="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES . 'price-format-rules.png' ); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="<?php self::set_params( 'format_price_rules_enable', true ) ?>">
										<?php esc_html_e( 'Price format', 'woocommerce-alidropship' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php self::set_params( 'format_price_rules_enable', true ) ?>"
                                               type="checkbox" <?php checked( self::$settings->get_params( 'format_price_rules_enable' ), 1 ) ?>
                                               tabindex="0"
                                               class="<?php self::set_params( 'format_price_rules_enable', true ) ?>"
                                               value="1"
                                               name="<?php self::set_params( 'format_price_rules_enable' ) ?>"/>
                                        <label>
											<?php esc_html_e( 'Adjust product prices following below rules after prices are calculated with above rules', 'woocommerce-alidropship' ) ?>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
						<?php
						if ( $decimals < 1 ) {
							?>
                            <div class="vi-ui message">
								<?php printf( wp_kses_post( __( 'Rules for Fraction part will not take effect because you set %s for Number of decimals in your <a href="admin.php?page=wc-settings#woocommerce_price_num_decimals" target="_blank">WooCommerce settings</a>', 'woocommerce-alidropship' ) ), $decimals ); ?>
                            </div>
							<?php
						}
						?>
                        <table class="vi-ui celled table <?php self::set_params( 'format_price_rules_table', true ) ?>">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'No.', 'woocommerce-alidropship' ) ?></th>
                                <th><?php esc_html_e( 'Price range', 'woocommerce-alidropship' ) ?></th>
                                <th class="<?php self::set_params( 'format_price_rules_col', true ) ?>"><?php esc_html_e( 'Compared part', 'woocommerce-alidropship' ) ?></th>
                                <th><?php esc_html_e( 'Compared part range', 'woocommerce-alidropship' ) ?>
                                <th class="<?php self::set_params( 'format_price_rules_col', true ) ?>"><?php esc_html_e( 'New value of compared part', 'woocommerce-alidropship' ) ?></th>
                            </tr>
                            </thead>
                            <tbody class="<?php self::set_params( 'format_price_rules_container', true ) ?> ui-sortable">
							<?php
							$format_price_rules = self::$settings->get_params( 'format_price_rules' );

							if ( ! is_array( $format_price_rules ) || ! count( $format_price_rules ) ) {
								$format_price_rules = array(
									array(
										'from'       => '0',
										'to'         => '0',
										'part'       => 'fraction',
										'value_from' => '0',
										'value_to'   => '0',
										'value'      => '0',
									)
								);
							}
							foreach ( $format_price_rules as $rule_no => $format_price_rule ) {
								$label_class    = self::set( 'format-price-rules-label' );
								$label_class    .= $format_price_rule['part'] === 'fraction' ? ' left' : ' right';
								$label_integer  = '.0';
								$label_fraction = '0.';
								?>
                                <tr>
                                    <th>
                                        <span class="<?php self::set_params( 'format_price_rules_number', true ); ?>"><?php echo esc_html( $rule_no + 1 ); ?></span>
                                    </th>
                                    <td>
                                        <div class="equal width fields">
                                            <div class="field <?php self::set_params( 'error-message-parent', true ); ?>">
                                                <div class="vi-ui left labeled input fluid">
                                                    <label for="amount" class="vi-ui label">$</label>
                                                    <input
                                                            type="number"
                                                            step="<?php echo esc_attr( $decimals_unit ) ?>"
                                                            min="0"
                                                            value="<?php echo esc_attr( $format_price_rule['from'] ) ?>"
                                                            name="<?php self::set_params( 'format_price_rules[from]', false, true ); ?>"
                                                            class="<?php self::set_params( 'format_price_rules_from', true ); ?>">
                                                </div>
                                                <div class="<?php self::set_params( 'error-message', true ); ?>"></div>
                                            </div>
                                            <span class="<?php self::set_params( 'price_from_to_separator', true ); ?>">-</span>
                                            <div class="field <?php self::set_params( 'error-message-parent', true ); ?>">
                                                <div class="vi-ui left labeled input fluid">
                                                    <label for="amount" class="vi-ui label">$</label>
                                                    <input
                                                            type="number"
                                                            min="0"
                                                            step="<?php echo esc_attr( $decimals_unit ) ?>"
                                                            value="<?php echo esc_attr( $format_price_rule['to'] ) ?>"
                                                            name="<?php self::set_params( 'format_price_rules[to]', false, true ); ?>"
                                                            class="<?php self::set_params( 'format_price_rules_to', true ); ?>">
                                                </div>
                                                <div class="<?php self::set_params( 'error-message', true ); ?>"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <select name="<?php self::set_params( 'format_price_rules[part]', false, true ); ?>"
                                                class="vi-ui fluid dropdown <?php self::set_params( 'format_price_rules_part', true ); ?>">
                                            <option value="integer" <?php selected( $format_price_rule['part'], 'integer' ) ?>><?php esc_html_e( 'Integer', 'woocommerce-alidropship' ) ?></option>
                                            <option value="fraction" <?php selected( $format_price_rule['part'], 'fraction' ) ?>><?php esc_html_e( 'Fraction', 'woocommerce-alidropship' ) ?></option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="equal width fields">
                                            <div class="field <?php self::set_params( 'error-message-parent', true ); ?>">
                                                <div class="vi-ui <?php echo esc_attr( $label_class ) ?> labeled input fluid">
                                                    <label for="amount"
                                                           class="vi-ui label <?php self::set_params( 'format_price_rules_label_fraction', true ); ?>"><?php echo esc_html__( $label_fraction ) ?></label>
                                                    <input
                                                            type="number"
                                                            step="1"
                                                            min="0"
                                                            value="<?php echo esc_attr( $format_price_rule['value_from'] ) ?>"
                                                            name="<?php self::set_params( 'format_price_rules[value_from]', false, true ); ?>"
                                                            class="<?php self::set_params( 'format_price_rules_value_from', true ); ?>">
                                                    <label for="amount"
                                                           class="vi-ui label <?php self::set_params( 'format_price_rules_label_integer', true ); ?>"><?php echo esc_html__( $label_integer ) ?></label>
                                                </div>
                                                <div class="<?php self::set_params( 'error-message', true ); ?>"></div>
                                            </div>
                                            <span class="<?php self::set_params( 'price_from_to_separator', true ); ?>">-</span>
                                            <div class="field <?php self::set_params( 'error-message-parent', true ); ?>">
                                                <div class="vi-ui <?php echo esc_attr( $label_class ) ?> labeled input fluid">
                                                    <label for="amount"
                                                           class="vi-ui label <?php self::set_params( 'format_price_rules_label_fraction', true ); ?>"><?php echo esc_html__( $label_fraction ) ?></label>
                                                    <input
                                                            type="number"
                                                            step="1"
                                                            min="0"
                                                            value="<?php echo esc_attr( $format_price_rule['value_to'] ) ?>"
                                                            name="<?php self::set_params( 'format_price_rules[value_to]', false, true ); ?>"
                                                            class="<?php self::set_params( 'format_price_rules_value_to', true ); ?>">
                                                    <label for="amount"
                                                           class="vi-ui label <?php self::set_params( 'format_price_rules_label_integer', true ); ?>"><?php echo esc_html__( $label_integer ) ?></label>
                                                </div>
                                                <div class="<?php self::set_params( 'error-message', true ); ?>"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="<?php echo esc_attr( self::set( array(
										'format-price-rules-value-td',
										'error-message-parent'
									) ) ); ?>">
                                        <div class="vi-ui <?php echo esc_attr( $label_class ) ?> labeled input fluid">
                                            <label for="amount"
                                                   class="vi-ui label <?php self::set_params( 'format_price_rules_label_fraction', true ); ?>"><?php echo esc_html__( $label_fraction ) ?></label>
                                            <input type="text"
                                                   value="<?php echo esc_attr( $format_price_rule['value'] ) ?>"
                                                   name="<?php self::set_params( 'format_price_rules[value]', false, true ); ?>"
                                                   class="<?php self::set_params( 'format_price_rules_value', true ); ?>">
                                            <label for="amount"
                                                   class="vi-ui label <?php self::set_params( 'format_price_rules_label_integer', true ); ?>"><?php echo esc_html__( $label_integer ) ?></label>
                                        </div>
                                        <div class="<?php self::set_params( 'format_price_rules_action_buttons', true ) ?>">
                                            <i class="vi-ui icon copy green <?php self::set_params( 'format_price_rules_duplicate', true ) ?>"
                                               title="<?php esc_attr_e( 'Duplicate this row', 'woocommerce-alidropship' ) ?>"></i>
                                            <i class="vi-ui icon trash red <?php self::set_params( 'format_price_rules_remove', true ) ?>"
                                               title="<?php esc_attr_e( 'Remove this row', 'woocommerce-alidropship' ) ?>"></i>
                                        </div>
                                        <div class="<?php self::set_params( 'error-message', true ); ?>"></div>
                                    </td>
                                </tr>
								<?php
							}
							?>
                            </tbody>
                        </table>
                        <div class="equal width fields form-table">
                            <div class="field">
                                <div class="vi-ui right labeled input fluid wad-labeled-button">
                                    <input type="number"
                                           placeholder="<?php esc_attr_e( 'Enter a price to test', 'woocommerce-alidropship' ) ?>"
                                           step="<?php echo esc_attr( $decimals_unit ) ?>"
                                           min="0"
                                           value="<?php echo esc_attr( self::$settings->get_params( 'format_price_rules_test' ) ) ?>"
                                           name="<?php self::set_params( 'format_price_rules_test', false, false ); ?>"
                                           class="<?php self::set_params( 'format_price_rules_test', true ); ?>">
                                    <label for="amount" class="vi-ui label"><span
                                                class="vi-ui positive button small <?php self::set_params( 'format_price_rules_test_button', true ); ?>"><?php esc_html_e( 'View result', 'woocommerce-alidropship' ) ?></span></label>
                                </div>
                            </div>
                            <div class="field <?php self::set_params( 'format_price_rules_test_result_container', true ); ?>">
                                <span class="<?php self::set_params( 'format_price_rules_test_result', true ); ?>"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="<?php echo esc_attr( self::set( 'save-settings-container' ) ) ?>">
                    <button type="submit"
                            class="vi-ui button primary labeled icon <?php echo esc_attr( self::set( 'save-settings' ) ) ?>"
                            name="<?php echo esc_attr( self::set( 'save-settings', true ) ) ?>"><i
                                class="save icon"></i><?php esc_html_e( 'Save Settings', 'woocommerce-alidropship' ) ?>
                    </button>
					<?php
					if ( $is_main ) {
						?>
                        <button type="submit"
                                class="vi-ui button labeled icon <?php echo esc_attr( self::set( 'check-key' ) ) ?>"
                                name="<?php echo esc_attr( self::set( 'check-key', true ) ) ?>"><i
                                    class="save icon"></i><?php esc_html_e( 'Save & Check Key', 'woocommerce-alidropship' ) ?>
                        </button>
						<?php
					}
					VI_WOOCOMMERCE_ALIDROPSHIP_DATA::chrome_extension_buttons();
					?>
                </p>
            </form>
			<?php do_action( 'villatheme_support_woocommerce-alidropship' ) ?>
        </div>
		<?php
	}

	/**
	 *
	 */
	protected static function price_rule_table_head() {
		?>
        <thead>
        <tr>
            <th><?php esc_html_e( 'Price range', 'woocommerce-alidropship' ) ?></th>
            <th><?php esc_html_e( 'Actions', 'woocommerce-alidropship' ) ?></th>
            <th><?php esc_html_e( 'Sale price', 'woocommerce-alidropship' ) ?>
                <div class="<?php self::set_params( 'description', true ) ?>">
					<?php esc_html_e( '(Set -1 to not use sale price)', 'woocommerce-alidropship' ) ?>
                </div>
            </th>
            <th style="min-width: 135px"><?php esc_html_e( 'Regular price', 'woocommerce-alidropship' ) ?></th>
            <th></th>
        </tr>
        </thead>
		<?php
	}

	/**
	 * Access token list
	 *
	 * @param $access_tokens
	 * @param $access_token
	 */
	protected static function access_tokens_list( $access_tokens, $access_token ) {
		if ( count( $access_tokens ) ) {
			foreach ( $access_tokens as $token ) {
				if ( $token['access_token'] ) {
					$class = $token['expire_time'] < 1000 * time() ? 'error' : '';
					?>
                    <tr>
                        <td><?php echo isset( $token['user_nick'] ) ? $token['user_nick'] : ''; ?></td>
                        <td class="<?php echo esc_attr( $class ) ?>"><?php echo isset( $token['expire_time'] ) ? date( 'Y-m-d H:i:s', intval( $token['expire_time'] / 1000 ) ) : ''; ?></td>
                        <td>
                            <div class="vi-ui toggle checkbox">
                                <input type="radio"
                                       name="<?php self::set_params( 'access_token' ) ?>"
                                       value="<?php echo esc_attr( $token['access_token'] ); ?>"
                                       class="<?php echo esc_attr( self::set( 'access-token-default' ) ) ?>" <?php if ( $access_token && $access_token === $token['access_token'] ) {
									echo esc_attr( 'checked' );
								} ?>><label></label></div>
                        </td>
                        <td>
                            <span class="vi-ui button negative mini icon <?php echo esc_attr( self::set( 'remove-access-token' ) ) ?>"><i
                                        class="icon trash"></i></span>
                        </td>
                    </tr>
					<?php
				}
			}
		}
	}

	/**
	 * @param $attributes
	 */
	protected static function attributes_list_html( $attributes ) {
		$attributes_mapping_origin      = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_origin();
		$attributes_mapping_replacement = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_replacement();
		foreach ( $attributes as $attribute_slug => $attribute_values ) {
			sort( $attribute_values );
			$row_span = count( $attribute_values );
			if ( $row_span ) {
				for ( $i = 0; $i < $row_span; $i ++ ) {
					?>
                    <tr data-attribute_slug="<?php echo esc_attr( $attribute_slug ) ?>">
                        <td class="<?php echo self::set( 'product-attribute-slug' ) ?>"><?php echo esc_html( $attribute_slug ) ?></td>
                        <td class="<?php echo self::set( 'product-attribute-original-term' ) ?>"><?php echo esc_html( $attribute_values[ $i ] ) ?></td>
                        <td><input type="text"
                                   class="<?php echo self::set( 'product-attribute-replacement' ) ?>"
                                   value="<?php echo esc_attr( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::find_attribute_replacement( $attributes_mapping_origin, $attributes_mapping_replacement, $attribute_values[ $i ], $attribute_slug ) ) ?>">
                        </td>
                    </tr>
					<?php
				}
			}
		}
	}

	/**
	 * @param string $name
	 * @param bool $class
	 * @param bool $multiple
	 */
	public static function set_params( $name = '', $class = false, $multiple = false ) {
		if ( $name ) {
			if ( $class ) {
				echo 'vi-wad-' . str_replace( '_', '-', $name );
			} else {
				if ( $multiple ) {
					echo 'wad_' . $name . '[]';
				} else {
					echo 'wad_' . $name;
				}
			}
		}
	}

	public function admin_menu() {
		$menu_slug = 'woocommerce-alidropship';
		add_menu_page(
			esc_html__( 'ALD - AliExpress Dropshipping and Fulfillment for WooCommerce Settings', 'woocommerce-alidropship' ),
			esc_html__( 'Dropship & Fulfill', 'woocommerce-alidropship' ),
			apply_filters( 'vi_wad_admin_menu_capability', 'manage_options', $menu_slug ),
			$menu_slug,
			array( $this, 'page_callback' ),
			VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES . 'icon.png',
			2
		);
	}

	/**
	 * @param string $name
	 */
	private static function default_language_flag_html( $name = '' ) {
		if ( self::$default_language ) {
			?>
            <p>
                <label for="<?php echo esc_attr( $name ) ?>"><?php
					if ( isset( self::$languages_data[ self::$default_language ]['country_flag_url'] ) && self::$languages_data[ self::$default_language ]['country_flag_url'] ) {
						?>
                        <img src="<?php echo esc_url( self::$languages_data[ self::$default_language ]['country_flag_url'] ); ?>">
						<?php
					}
					echo self::$default_language;
					if ( isset( self::$languages_data[ self::$default_language ]['translated_name'] ) ) {
						echo '(' . self::$languages_data[ self::$default_language ]['translated_name'] . '):';
					}
					?></label>
            </p>
			<?php
		}
	}

	/**
	 * @param $args
	 */
	public static function table_of_placeholders( $args ) {
		if ( count( $args ) ) {
			?>
            <table class="vi-ui celled table <?php echo esc_attr( self::set( 'table-of-placeholders' ) ) ?>">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Placeholder', 'woocommerce-alidropship' ) ?></th>
                    <th><?php esc_html_e( 'Explanation', 'woocommerce-alidropship' ) ?></th>
                </tr>
                </thead>
                <tbody>
				<?php
				foreach ( $args as $key => $value ) {
					?>
                    <tr>
                        <td class="<?php echo esc_attr( self::set( 'placeholder-value-container' ) ) ?>"><input
                                    class="<?php echo esc_attr( self::set( 'placeholder-value' ) ) ?>" type="text"
                                    readonly value="<?php echo esc_attr( "{{$key}}" ); ?>"><i
                                    class="vi-ui icon copy <?php echo esc_attr( self::set( 'placeholder-value-copy' ) ) ?>"
                                    title="<?php esc_attr_e( 'Copy', 'woocommerce-alidropship' ) ?>"></i></td>
                        <td><?php echo esc_html( "{$value}" ); ?></td>
                    </tr>
					<?php
				}
				?>
                </tbody>
            </table>
			<?php
		}
	}

	/**
	 * @return mixed|void
	 */
	public static function create_ajax_nonce() {
		return apply_filters( 'vi_wad_admin_ajax_nonce', wp_create_nonce( 'woocommerce_alidropship_admin_ajax' ) );
	}

	/**
	 * @param string $page
	 */
	public static function check_ajax_referer( $page = 'woocommerce-alidropship' ) {
		if ( ! apply_filters( 'vi_wad_verify_ajax_nonce', false, $page ) ) {
			check_ajax_referer( 'woocommerce_alidropship_admin_ajax', '_vi_wad_ajax_nonce' );
		}
	}

	/**
	 * Custom rule for products sync
	 *
	 * @param $custom_rule_id
	 * @param $custom_rule
	 */
	private static function custom_rule_html( $custom_rule_id, $custom_rule ) {
		?>
        <div class="sixteen wide field <?php self::set_params( 'custom_price_rule_wrap', true ) ?>"
             data-custom_rule_id="<?php echo esc_attr( $custom_rule_id ); ?>">
            <div class="vi-ui fluid styled accordion">
                <div class="title"><i
                            class="dropdown icon"></i><?php esc_html_e( 'Apply to', 'woocommerce-alidropship' ); ?>
                    <span class="<?php self::set_params( 'custom_price_rule_remove', true ) ?>"><i
                                class="icon trash"></i></span>
                </div>
                <div class="content">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th><?php esc_html_e( 'Include products', 'woocommerce-alidropship' ); ?></th>
                            <td>
                                <select name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][products]", false, true ) ?>"
                                        multiple="multiple"
                                        class="search-product">
									<?php
									foreach ( $custom_rule['products'] as $product_id ) {
										$product = wc_get_product( $product_id );
										if ( $product ) {
											?>
                                            <option value="<?php echo esc_attr( $product_id ) ?>"
                                                    selected><?php echo esc_html( "(#{$product_id}) " . $product->get_title() ) ?></option>
											<?php
										}
									}
									?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Exclude products', 'woocommerce-alidropship' ); ?></th>
                            <td>
                                <select name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][excl_products]", false, true ) ?>"
                                        multiple="multiple"
                                        class="search-product">
									<?php
									foreach ( $custom_rule['excl_products'] as $product_id ) {
										$product = wc_get_product( $product_id );
										if ( $product ) {
											?>
                                            <option value="<?php echo esc_attr( $product_id ) ?>"
                                                    selected><?php echo esc_html( "(#{$product_id}) " . $product->get_title() ) ?></option>
											<?php
										}
									}
									?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Include categories', 'woocommerce-alidropship' ); ?></th>
                            <td>
                                <select name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][categories]", false, true ) ?>"
                                        class="search-category"
                                        multiple="multiple">
									<?php
									foreach ( $custom_rule['categories'] as $category_id ) {
										$category = get_term( $category_id );
										if ( $category ) {
											?>
                                            <option value="<?php echo esc_attr( $category_id ) ?>"
                                                    selected><?php echo esc_html( VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::build_category_name( $category->name, $category ) ); ?></option>
											<?php
										}
									}
									?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Exclude categories', 'woocommerce-alidropship' ); ?></th>
                            <td>
                                <select name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][excl_categories]", false, true ) ?>"
                                        class="search-category"
                                        multiple="multiple">
									<?php
									foreach ( $custom_rule['excl_categories'] as $category_id ) {
										$category = get_term( $category_id );
										if ( $category ) {
											?>
                                            <option value="<?php echo esc_attr( $category_id ) ?>"
                                                    selected><?php echo esc_html( VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::build_category_name( $category->name, $category ) ); ?></option>
											<?php
										}
									}
									?>
                                </select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="title"><i
                            class="dropdown icon"></i><?php esc_html_e( 'Pricing rules', 'woocommerce-alidropship' ); ?>
                </div>
                <div class="content <?php self::set_params( 'price_rule_wrapper', true ) ?>">
                    <table class="vi-ui celled table price-rule">
						<?php
						self::price_rule_table_head();
						$price_from      = $custom_rule['price_from'];
						$price_default   = $custom_rule['price_default'];
						$price_to        = $custom_rule['price_to'];
						$plus_value      = $custom_rule['plus_value'];
						$plus_sale_value = $custom_rule['plus_sale_value'];
						$plus_value_type = $custom_rule['plus_value_type'];
						?>
                        <tbody class="<?php self::set_params( 'price_rule_container', true ) ?> ui-sortable">
						<?php
						$price_from_count = count( $price_from );
						if ( $price_from_count > 0 ) {
							/*adjust price rules since version 1.0.1.1*/
							if ( ! is_array( $price_to ) || count( $price_to ) !== $price_from_count ) {
								if ( $price_from_count > 1 ) {
									$price_to   = array_values( array_slice( $price_from, 1 ) );
									$price_to[] = '';
								} else {
									$price_to = array( '' );
								}
							}
							for ( $i = 0; $i < count( $price_from ); $i ++ ) {
								switch ( $plus_value_type[ $i ] ) {
									case 'fixed':
										$value_label_left  = '+';
										$value_label_right = '$';
										break;
									case 'percent':
										$value_label_left  = '+';
										$value_label_right = '%';
										break;
									case 'multiply':
										$value_label_left  = 'x';
										$value_label_right = '';
										break;
									default:
										$value_label_left  = '=';
										$value_label_right = '$';
								}
								?>
                                <tr class="<?php self::set_params( 'price_rule_row', true ) ?>">
                                    <td>
                                        <div class="equal width fields">
                                            <div class="field">
                                                <div class="vi-ui left labeled input fluid">
                                                    <label for="amount"
                                                           class="vi-ui label">$</label>
                                                    <input
                                                            step="any"
                                                            type="number"
                                                            min="0"
                                                            value="<?php echo esc_attr( $price_from[ $i ] ); ?>"
                                                            name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][price_from]", false, true ); ?>"
                                                            class="<?php self::set_params( 'price_from', true ); ?>">
                                                </div>
                                            </div>
                                            <span class="<?php self::set_params( 'price_from_to_separator', true ); ?>">-</span>
                                            <div class="field">
                                                <div class="vi-ui left labeled input fluid">
                                                    <label for="amount"
                                                           class="vi-ui label">$</label>
                                                    <input
                                                            step="any"
                                                            type="number"
                                                            min="0"
                                                            value="<?php echo esc_attr( $price_to[ $i ] ); ?>"
                                                            name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][price_to]", false, true ); ?>"
                                                            class="<?php self::set_params( 'price_to', true ); ?>">
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                    <td>
                                        <select name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][plus_value_type]", false, true ); ?>"
                                                class="vi-ui fluid dropdown <?php self::set_params( 'plus_value_type', true ); ?>">
                                            <option value="fixed" <?php selected( $plus_value_type[ $i ], 'fixed' ) ?>><?php esc_html_e( 'Increase by Fixed amount($)', 'woocommerce-alidropship' ) ?></option>
                                            <option value="percent" <?php selected( $plus_value_type[ $i ], 'percent' ) ?>><?php esc_html_e( 'Increase by Percentage(%)', 'woocommerce-alidropship' ) ?></option>
                                            <option value="multiply" <?php selected( $plus_value_type[ $i ], 'multiply' ) ?>><?php esc_html_e( 'Multiply with', 'woocommerce-alidropship' ) ?></option>
                                            <option value="set_to" <?php selected( $plus_value_type[ $i ], 'set_to' ) ?>><?php esc_html_e( 'Set to', 'woocommerce-alidropship' ) ?></option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="vi-ui right labeled input fluid">
                                            <label for="amount"
                                                   class="vi-ui label <?php self::set_params( 'value-label-left', true ); ?>"><?php echo esc_html( $value_label_left ) ?></label>
                                            <input type="number" min="-1"
                                                   step="any"
                                                   value="<?php echo esc_attr( $plus_sale_value[ $i ] ); ?>"
                                                   name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][plus_sale_value]", false, true ); ?>"
                                                   class="<?php self::set_params( 'plus_sale_value', true ); ?>">
                                            <div class="vi-ui basic label <?php self::set_params( 'value-label-right', true ); ?>"><?php echo esc_html( $value_label_right ) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="vi-ui right labeled input fluid">
                                            <label for="amount"
                                                   class="vi-ui label <?php self::set_params( 'value-label-left', true ); ?>"><?php echo esc_html( $value_label_left ) ?></label>
                                            <input type="number" min="0"
                                                   step="any"
                                                   value="<?php echo esc_attr( $plus_value[ $i ] ); ?>"
                                                   name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][plus_value]", false, true ); ?>"
                                                   class="<?php self::set_params( 'plus_value', true ); ?>">
                                            <div class="vi-ui basic label <?php self::set_params( 'value-label-right', true ); ?>"><?php echo esc_html( $value_label_right ) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="">
                                                                                    <span class="vi-ui button icon negative mini <?php self::set_params( 'price_rule_remove', true ) ?>"
                                                                                          title="<?php esc_attr_e( 'Remove', 'woocommerce-alidropship' ) ?>"><i
                                                                                                class="icon trash"></i></span>
                                        </div>
                                    </td>
                                </tr>
								<?php
							}
						}
						?>
                        </tbody>
                        <tfoot>
						<?php
						$plus_value_type_d = isset( $price_default['plus_value_type'] ) ? $price_default['plus_value_type'] : 'multiply';
						$plus_sale_value_d = isset( $price_default['plus_sale_value'] ) ? $price_default['plus_sale_value'] : 1;
						$plus_value_d      = isset( $price_default['plus_value'] ) ? $price_default['plus_value'] : 2;
						switch ( $plus_value_type_d ) {
							case 'fixed':
								$value_label_left  = '+';
								$value_label_right = '$';
								break;
							case 'percent':
								$value_label_left  = '+';
								$value_label_right = '%';
								break;
							case 'multiply':
								$value_label_left  = 'x';
								$value_label_right = '';
								break;
							default:
								$value_label_left  = '=';
								$value_label_right = '$';
						}
						?>
                        <tr class="<?php echo esc_attr( self::set( array( 'price-rule-row-default' ) ) ) ?>">
                            <th><?php esc_html_e( 'Default', 'woocommerce-alidropship' ) ?></th>
                            <th>
                                <select name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][price_default][plus_value_type]", false ); ?>"
                                        class="vi-ui fluid dropdown <?php self::set_params( 'plus_value_type', true ); ?>">
                                    <option value="fixed" <?php selected( $plus_value_type_d, 'fixed' ) ?>><?php esc_html_e( 'Increase by Fixed amount($)', 'woocommerce-alidropship' ) ?></option>
                                    <option value="percent" <?php selected( $plus_value_type_d, 'percent' ) ?>><?php esc_html_e( 'Increase by Percentage(%)', 'woocommerce-alidropship' ) ?></option>
                                    <option value="multiply" <?php selected( $plus_value_type_d, 'multiply' ) ?>><?php esc_html_e( 'Multiply with', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_to" <?php selected( $plus_value_type_d, 'set_to' ) ?>><?php esc_html_e( 'Set to', 'woocommerce-alidropship' ) ?></option>
                                </select>
                            </th>
                            <th>
                                <div class="vi-ui right labeled input fluid">
                                    <label for="amount"
                                           class="vi-ui label <?php self::set_params( 'value-label-left', true ); ?>"><?php echo esc_html( $value_label_left ) ?></label>
                                    <input type="number" min="-1" step="any"
                                           value="<?php echo esc_attr( $plus_sale_value_d ); ?>"
                                           name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][price_default][plus_sale_value]", false ); ?>"
                                           class="<?php self::set_params( 'plus_sale_value', true ); ?>">
                                    <div class="vi-ui basic label <?php self::set_params( 'value-label-right', true ); ?>"><?php echo esc_html( $value_label_right ) ?></div>
                                </div>
                            </th>
                            <th>
                                <div class="vi-ui right labeled input fluid">
                                    <label for="amount"
                                           class="vi-ui label <?php self::set_params( 'value-label-left', true ); ?>"><?php echo esc_html( $value_label_left ) ?></label>
                                    <input type="number" min="0" step="any"
                                           value="<?php echo esc_attr( $plus_value_d ); ?>"
                                           name="<?php self::set_params( "update_product_custom_rules[$custom_rule_id][price_default][plus_value]", false ); ?>"
                                           class="<?php self::set_params( 'plus_value', true ); ?>">
                                    <div class="vi-ui basic label <?php self::set_params( 'value-label-right', true ); ?>"><?php echo esc_html( $value_label_right ) ?></div>
                                </div>
                            </th>
                            <th>
                            </th>
                        </tr>
                        </tfoot>
                    </table>
                    <span class="<?php self::set_params( 'price_rule_add', true ) ?> vi-ui button labeled icon positive mini"
                          title="<?php esc_attr_e( 'Add a new price range', 'woocommerce-alidropship' ) ?>"><i
                                class="icon add"></i><?php esc_html_e( 'Add price range', 'woocommerce-alidropship' ); ?></span>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Exchange rate related fields
	 */
	public static function exchange_rate_fields() {
		$exchange_api           = self::$settings->get_params( 'exchange_rate_api' );
		$supported_exchange_api = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_supported_exchange_api();
		?>
        <tr>
            <th>
                <label for="<?php self::set_params( 'exchange_rate_api', true ) ?>"><?php esc_html_e( 'Exchange rate API', 'woocommerce-alidropship' ) ?></label>
            <td>
                <select id="<?php self::set_params( 'exchange_rate_api', true ) ?>"
                        class="vi-ui dropdown"
                        name="<?php self::set_params( 'exchange_rate_api' ) ?>">
                    <option value=""><?php esc_html_e( 'None', 'woocommerce-alidropship' ) ?></option>
					<?php
					foreach ( $supported_exchange_api as $supported_exchange_api_k => $supported_exchange_api_v ) {
						?>
                        <option value="<?php echo esc_attr( $supported_exchange_api_k ) ?>" <?php selected( $supported_exchange_api_k, $exchange_api ) ?>><?php echo esc_html( $supported_exchange_api_v ); ?></option>
						<?php
					}
					?>
                </select>
                <p><?php esc_html_e( 'Get exchange rate from this selected API', 'woocommerce-alidropship' ) ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="<?php self::set_params( 'import_currency_rate', true ) ?>"><?php printf( esc_html__( 'Exchange rate - USD/%s', 'woocommerce-alidropship' ), get_option( 'woocommerce_currency' ) ) ?></label>
            <td>
                <div class="vi-ui left labeled input wad-labeled-button">
                    <label class="vi-ui label"><span data-currency="USD"
                                                     class="vi-ui button positive small labeled icon <?php self::set_params( 'import_currency_rate_button', true ) ?>"><i
                                    class="icon download cloud"></i><?php esc_html_e( 'Update rate', 'woocommerce-alidropship' ) ?></span></label>
                    <input type="number" <?php checked( self::$settings->get_params( 'import_currency_rate' ), 1 ) ?>
                           step="any"
                           id="<?php self::set_params( 'import_currency_rate', true ) ?>"
                           class="<?php self::set_params( 'import_currency_rate', true ) ?>"
                           value="<?php echo self::$settings->get_params( 'import_currency_rate' ) ?>"
                           name="<?php self::set_params( 'import_currency_rate' ) ?>"/>
                </div>
                <p><?php printf( __( 'This is exchange rate to convert product price from USD to your store\'s currency(%s) when adding products to import list, syncing products and convert shipping cost(if Frontend shipping functionality is enabled).', 'woocommerce-alidropship' ), get_option( 'woocommerce_currency' ) ) ?></p>
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
                <p><?php esc_html_e( 'E.g: Your store currency is VND:', 'woocommerce-alidropship' ) ?></p>
                <p><?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( __( '1 USD = 23 000 VND => set "Exchange rate - USD/VND" <strong>23000</strong>', 'woocommerce-alidropship' ) ) ?></p>
            </td>
        </tr>
		<?php
		$exchange_rate_decimals = self::$settings->get_params( 'exchange_rate_decimals' );
		?>
        <tr>
            <th>
                <label for="<?php self::set_params( 'exchange_rate_decimals', true ) ?>"><?php esc_html_e( 'Exchange rate decimals', 'woocommerce-alidropship' ) ?></label>
            <td>
                <select id="<?php self::set_params( 'exchange_rate_decimals', true ) ?>"
                        class="vi-ui dropdown <?php self::set_params( 'exchange_rate_decimals', true ) ?>"
                        name="<?php self::set_params( 'exchange_rate_decimals' ) ?>">
					<?php
					for ( $dec_num = 0; $dec_num < 11; $dec_num ++ ) {
						?>
                        <option value="<?php echo esc_attr( $dec_num ) ?>" <?php selected( $dec_num, $exchange_rate_decimals ) ?>><?php echo esc_html( $dec_num ); ?></option>
						<?php
					}
					?>
                </select>
                <p><?php esc_html_e( 'Number of decimals to round exchange rate when updating exchange rate with API', 'woocommerce-alidropship' ) ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="<?php self::set_params( 'import_currency_rate_CNY', true ) ?>"><?php esc_html_e( 'Exchange rate - CNY/USD', 'woocommerce-alidropship' ) ?></label>
            <td>
                <div class="vi-ui left labeled input wad-labeled-button">
                    <label class="vi-ui label"><span data-currency="CNY"
                                                     class="vi-ui button positive small labeled icon <?php self::set_params( 'import_currency_rate_button', true ) ?>"><i
                                    class="icon download cloud"></i><?php esc_html_e( 'Update rate', 'woocommerce-alidropship' ) ?></span></label>
                    <input type="number" <?php checked( self::$settings->get_params( 'import_currency_rate_CNY' ), 1 ) ?>
                           step="any"
                           id="<?php self::set_params( 'import_currency_rate_CNY', true ) ?>"
                           class="<?php self::set_params( 'import_currency_rate_CNY', true ) ?>"
                           value="<?php echo self::$settings->get_params( 'import_currency_rate_CNY' ) ?>"
                           name="<?php self::set_params( 'import_currency_rate_CNY' ) ?>"/>
                </div>
                <p><?php esc_html_e( 'In some cases, prices are only available in CNY so we first have to convert them to USD. If not set, our plugin will skip syncing price. This rate always uses 2 decimals when updated.', 'woocommerce-alidropship' ) ?></p>
            </td>
        </tr>
		<?php
		if ( get_option( 'woocommerce_currency' ) !== 'RUB' ) {
			?>
            <tr>
                <th>
                    <label for="<?php self::set_params( 'import_currency_rate_RUB', true ) ?>"><?php esc_html_e( 'Exchange rate - RUB/USD', 'woocommerce-alidropship' ) ?></label>
                <td>
                    <div class="vi-ui left labeled input wad-labeled-button">
                        <label class="vi-ui label"><span data-currency="RUB"
                                                         class="vi-ui button positive small labeled icon <?php self::set_params( 'import_currency_rate_button', true ) ?>"><i
                                        class="icon download cloud"></i><?php esc_html_e( 'Update rate', 'woocommerce-alidropship' ) ?></span></label>
                        <input type="number" <?php checked( self::$settings->get_params( 'import_currency_rate_RUB' ), 1 ) ?>
                               step="0.001"
                               min="0.001"
                               id="<?php self::set_params( 'import_currency_rate_RUB', true ) ?>"
                               class="<?php self::set_params( 'import_currency_rate_RUB', true ) ?>"
                               value="<?php echo self::$settings->get_params( 'import_currency_rate_RUB' ) ?>"
                               name="<?php self::set_params( 'import_currency_rate_RUB' ) ?>"/>
                    </div>
                    <p><?php esc_html_e( 'In some cases, prices are only available in RUB so we first have to convert them to USD. If not set, you will not be able to import products in RUB(if the store currency is not RUB) and our plugin will skip syncing price. This rate always uses 3 decimals when updated.', 'woocommerce-alidropship' ) ?></p>
                    <p><?php esc_html_e( 'If you want to import products from aliexpress.ru, this is required.', 'woocommerce-alidropship' ) ?></p>
                </td>
            </tr>
			<?php
		}
	}
}