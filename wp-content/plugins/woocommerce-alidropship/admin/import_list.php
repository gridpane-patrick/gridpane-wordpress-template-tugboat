<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List {
	private static $settings;
	private static $variations_count;
	private static $categories_options;
	private static $shipping_class_options;
	private static $tags_options;
	private static $override_selecteds;
	public static $process_image;

	public function __construct() {
		self::$variations_count   = 0;
		self::$override_selecteds = array();
		self::$categories_options = self::$shipping_class_options = self::$tags_options = '';
		self::$settings           = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		VILLATHEME_ADMIN_SHOW_MESSAGE::get_instance();
		add_action( 'init', array( $this, 'background_process' ) );
		add_action( 'admin_init', array( $this, 'empty_import_list' ) );
		add_action( 'admin_init', array( $this, 'move_queued_images' ) );
		add_action( 'admin_init', array( $this, 'reimport_product' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 999999 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
		add_action( 'wp_ajax_vi_wad_import', array( $this, 'import' ) );
		add_action( 'wp_ajax_vi_wad_switch_product_attributes_values', array(
			$this,
			'switch_product_attributes_values'
		) );
		add_action( 'wp_ajax_vi_wad_select_shipping', array(
			$this,
			'select_shipping'
		) );
		add_action( 'wp_ajax_vi_wad_load_variations_table', array(
			$this,
			'load_variations_table'
		) );
		add_action( 'wp_ajax_vi_wad_split_product', array( $this, 'split_product' ) );
		add_action( 'wp_ajax_vi_wad_override', array( $this, 'override' ) );
		add_action( 'wp_ajax_vi_wad_remove', array( $this, 'remove' ) );
		add_action( 'wp_ajax_vi_wad_save_attributes', array( $this, 'save_attributes' ) );
		add_action( 'wp_ajax_vi_wad_remove_attribute', array( $this, 'ajax_remove_attribute' ) );
		add_action( 'admin_head', array( $this, 'menu_product_count' ), 999 );
	}

	/**
	 * Used to switch AliExpress attributes but not so helpful because the alternatives do not match with variation images
     * Currently hidden from UI
	 */
	public function switch_product_attributes_values() {
		self::check_ajax_referer();
		$key        = isset( $_POST['product_index'] ) ? absint( sanitize_text_field( $_POST['product_index'] ) ) : '';
		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$from_tab   = isset( $_POST['from_tab'] ) ? sanitize_text_field( $_POST['from_tab'] ) : '';
		if ( $key > - 1 && $product_id ) {
			$currency                    = 'USD';
			$woocommerce_currency        = get_option( 'woocommerce_currency' );
			$woocommerce_currency_symbol = get_woocommerce_currency_symbol();
			$manage_stock                = self::$settings->get_params( 'manage_stock' );
			$use_different_currency      = false;
			$decimals                    = wc_get_price_decimals();
			$variations                  = get_post_meta( $product_id, '_vi_wad_variations', true );
			$attributes                  = get_post_meta( $product_id, '_vi_wad_attributes', true );
			if ( is_array( $variations ) && count( $variations ) && is_array( $attributes ) && count( $attributes ) ) {
				if ( empty( $variations[0]['attributes_sub_edited'] ) && ! empty( $variations[0]['attributes_edited'] ) ) {
					wp_send_json(
						array(
							'status'  => 'error',
							'message' => esc_html__( 'Not supported, please try reimporting this product from AliExpress.', 'woocommerce-alidropship' )
						)
					);
				}
				foreach ( $attributes as $attribute_k => $attribute ) {
					if ( ! empty( $attribute['values_sub'] ) ) {
						$temp                                     = $attribute['values'];
						$attributes[ $attribute_k ]['values']     = $attribute['values_sub'];
						$attributes[ $attribute_k ]['values_sub'] = $temp;
					}
				}
				update_post_meta( $product_id, '_vi_wad_attributes', $attributes );

				foreach ( $variations as $variation_k => $variation ) {
					if ( isset( $variation['attributes_sub'] ) && is_array( $variation['attributes_sub'] ) && count( $variation['attributes_sub'] ) === count( $variation['attributes'] ) ) {
						$temp                                         = $variation['attributes'];
						$variations[ $variation_k ]['attributes']     = $variation['attributes_sub'];
						$variations[ $variation_k ]['attributes_sub'] = $temp;
					}
					if ( ! empty( $variation['sku'] ) ) {
						$temp                                  = $variation['sku'];
						$variations[ $variation_k ]['sku']     = $variation['sku_sub'];
						$variations[ $variation_k ]['sku_sub'] = $temp;
					}
				}
				update_post_meta( $product_id, '_vi_wad_variations', $variations );
			} else {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Can not find replacement for product attributes values. Please remove this product and import it again with the latest version of this plugin and Chrome Extension', 'woocommerce-alidropship' )
					)
				);
			}

			$list_attributes = get_post_meta( $product_id, '_vi_wad_list_attributes', true );
			if ( is_array( $list_attributes ) && count( $list_attributes ) ) {
				foreach ( $list_attributes as $list_attribute_k => $list_attribute ) {
					if ( ! empty( $list_attribute['name_sub'] ) ) {
						$temp                                             = $list_attribute['name'];
						$list_attributes[ $list_attribute_k ]['name']     = $list_attribute['name_sub'];
						$list_attributes[ $list_attribute_k ]['name_sub'] = $temp;
					}
				}
				update_post_meta( $product_id, '_vi_wad_list_attributes', $list_attributes );
			}
			$parent = array();
			if ( is_array( $attributes ) && count( $attributes ) ) {
				foreach ( $attributes as $attribute_k => $attribute_v ) {
					$parent[ $attribute_k ] = $attribute_v['slug'];
				}
			}
			if ( $decimals < 1 ) {
				$decimals = 1;
			} else {
				$decimals = pow( 10, ( - 1 * $decimals ) );
			}
			if ( strtolower( $woocommerce_currency ) != strtolower( $currency ) ) {
				$use_different_currency = true;
			}
			$variations_tab = '';
			if ( 'variations' === $from_tab ) {
				ob_start();
				self::variation_html( $key, $parent, $attributes, $manage_stock, $variations, $use_different_currency, $currency, $product_id, $woocommerce_currency_symbol, $decimals, false );
				$variations_tab = ob_get_clean();
			}
			ob_start();
			self::attributes_tab_html( $product_id, $attributes );
			$attributes_tab = ob_get_clean();
			wp_send_json(
				array(
					'status'         => 'success',
					'message'        => '',
					'variations_tab' => $variations_tab,
					'attributes_tab' => $attributes_tab,
				)
			);
		} else {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Can not find replacement for product attributes values. Please remove this product and import it again with the latest version of this plugin and Chrome Extension', 'woocommerce-alidropship' )
				)
			);
		}
	}

	/**
	 * Handle Reimport button from Imported page
	 */
	public function reimport_product() {
		$page = isset( $_REQUEST['page'] ) ? wp_unslash( $_REQUEST['page'] ) : '';
		if ( $page === 'woocommerce-alidropship-import-list' ) {
			$product_id = isset( $_REQUEST['reimport_id'] ) ? wp_unslash( $_REQUEST['reimport_id'] ) : '';
			$_wpnonce   = isset( $_REQUEST['_wpnonce'] ) ? wp_unslash( $_REQUEST['_wpnonce'] ) : '';
			if ( $product_id && wp_verify_nonce( $_wpnonce, 'reimport_nonce' ) ) {
				$product = get_post( $product_id );
				if ( $product && $product->post_status === 'publish' ) {
					$cookies = get_option( 'vi_woo_alidropship_cookies_for_importing', array() );
					if ( $cookies ) {
						if ( ! is_array( $cookies ) ) {
							$cookies = array(
								'xman_f' => $cookies,
							);
						}
					} else {
						$cookies = array();
					}
					$product_sku = get_post_meta( $product_id, '_vi_wad_sku', true );
					$get_data    = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_data( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $product_sku ), array(
						'cookies' => $cookies
					) );
					if ( $get_data['status'] === 'success' ) {
						$data = $get_data['data'];
						if ( count( $data ) ) {
							$overriding_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_overriding_product( $product_id );
							$post_id       = self::$settings->create_product( $data, get_post_meta( $product_id, '_vi_wad_shipping_info', true ), array(
								'post_status' => 'override',
								'post_parent' => $product_id
							) );
							if ( ! is_wp_error( $post_id ) ) {
								if ( $overriding_id ) {
									wp_update_post( array(
											'ID'          => $overriding_id,
											'post_status' => 'draft',
											'post_parent' => '',
											'edit_date'   => true,
										)
									);
								}
								wp_safe_redirect( add_query_arg( array( 'vi_wad_search_id' => $post_id ), remove_query_arg( array(
									'_wpnonce',
									'reimport_id'
								) ) ) );
								exit();
							}
						}
					}
				}
				wp_safe_redirect( remove_query_arg( array(
					'_wpnonce',
					'reimport_id'
				) ) );
				exit();
			}
		}
	}

	/**
	 * Update attributes after removing an attribute
	 */
	public function ajax_remove_attribute() {
		self::check_ajax_referer();
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-import-list' ) ) ) {
			wp_die();
		}
		$response = array(
			'status'  => 'error',
			'html'    => '',
			'message' => esc_html__( 'Invalid data', 'woocommerce-alidropship' ),
		);
		parse_str( $_POST['form_data'], $form_data );
		$data         = isset( $form_data['vi_wad_product'] ) ? $form_data['vi_wad_product'] : array();
		$product_data = array_values( $data )[0];
		$product_id   = array_keys( $data )[0];
		/*sanitize_text_field will remove many blank spaces from $attribute_value so do not use it here*/
		$attribute_value  = isset( $_POST['attribute_value'] ) ? stripslashes( $_POST['attribute_value'] ) : '';
		$remove_attribute = isset( $product_data['attributes'] ) ? stripslashes_deep( $product_data['attributes'] ) : array();
		$product          = get_post( $product_id );
		if ( $product && $product->post_type === 'vi_wad_draft_product' && in_array( $product->post_status, array(
				'draft',
				'override'
			) ) ) {
//			$attributes       = get_post_meta( $product_id, '_vi_wad_attributes', true );
			$attributes = self::get_product_attributes( $product_id );
//			$variations       = get_post_meta( $product_id, '_vi_wad_variations', true );
			$variations       = self::get_product_variations( $product_id, false, false );
			$split_variations = get_post_meta( $product_id, '_vi_wad_split_variations', true );
			if ( self::remove_product_attribute( $product_id, $remove_attribute, $attribute_value, $split_variations, $attributes, $variations ) ) {
				$response['status'] = 'success';
				if ( ! count( $attributes ) ) {
					$key                         = isset( $_POST['product_index'] ) ? absint( sanitize_text_field( $_POST['product_index'] ) ) : '';
					$currency                    = 'USD';
					$woocommerce_currency        = get_option( 'woocommerce_currency' );
					$woocommerce_currency_symbol = get_woocommerce_currency_symbol( $woocommerce_currency );
					$manage_stock                = self::$settings->get_params( 'manage_stock' );
					$use_different_currency      = false;
					$decimals                    = wc_get_price_decimals();
					if ( $decimals < 1 ) {
						$decimals = 1;
					} else {
						$decimals = pow( 10, ( - 1 * $decimals ) );
					}
					if ( strtolower( $woocommerce_currency ) != strtolower( $currency ) ) {
						$use_different_currency = true;
					}
					ob_start();
					self::simple_product_price_field_html( $key, $manage_stock, $variations, $use_different_currency, $currency, $product_id, $woocommerce_currency_symbol, $decimals, '', '' );
					$response['html'] = ob_get_clean();
				}
				$response['message'] = esc_html__( 'Remove attribute successfully', 'woocommerce-alidropship' );
			}
		} else {
			$response['message'] = esc_html__( 'Invalid product', 'woocommerce-alidropship' );
		}
		wp_send_json( $response );
	}

	/**
     * Remove an attribute
     * Save as edited attribute and also update in variations data
     *
	 * @param $product_id
	 * @param $remove_attribute
	 * @param $attribute_value
	 * @param $split_variations
	 * @param $attributes
	 * @param $variations
	 *
	 * @return bool
	 */
	public static function remove_product_attribute( $product_id, $remove_attribute, $attribute_value, $split_variations, &$attributes, &$variations ) {
		$remove = false;
		if ( count( $remove_attribute ) && count( $attributes ) ) {
			$new_attribute_v = array_values( $remove_attribute )[0];
			$attribute_k     = array_keys( $remove_attribute )[0];
			if ( ( ! isset( $new_attribute_v['name'] ) || $new_attribute_v['name'] ) && isset( $attributes[ $attribute_k ] ) ) {
				$attribute_slug = isset( $attributes[ $attribute_k ]['slug_edited'] ) ? $attributes[ $attribute_k ]['slug_edited'] : $attributes[ $attribute_k ]['slug'];
				foreach ( $variations as $variation_k => $variation ) {
					if ( isset( $variation['attributes_edited'] ) ) {
						if ( isset( $variation['attributes_edited'][ $attribute_slug ] ) ) {
							if ( ! self::is_attribute_value_equal( $variation['attributes_edited'][ $attribute_slug ], $attribute_value ) ) {
								unset( $variations[ $variation_k ] );
								if ( is_array( $split_variations ) && count( $split_variations ) ) {
									$search = array_search( $variation['skuAttr'], $split_variations );
									if ( $search !== false ) {
										unset( $split_variations[ $search ] );
									} else {
										$search = array_search( "{$variation['skuId']}{$variation['skuAttr']}", $split_variations );
										if ( $search !== false ) {
											unset( $split_variations[ $search ] );
										}
									}
								}
							}
							unset( $variations[ $variation_k ]['attributes_edited'][ $attribute_slug ] );
						}
					} else {
						$variation_attributes = $variation['attributes'];
						if ( isset( $variation_attributes[ $attribute_slug ] ) ) {
							if ( ! self::is_attribute_value_equal( $variation_attributes[ $attribute_slug ], $attribute_value ) ) {
								unset( $variations[ $variation_k ] );
								if ( is_array( $split_variations ) && count( $split_variations ) ) {
									$search = array_search( $variation['skuAttr'], $split_variations );
									if ( $search !== false ) {
										unset( $split_variations[ $search ] );
									} else {
										$search = array_search( "{$variation['skuId']}{$variation['skuAttr']}", $split_variations );
										if ( $search !== false ) {
											unset( $split_variations[ $search ] );
										}
									}
								}
							} else {
								unset( $variation_attributes[ $attribute_slug ] );
								$variations[ $variation_k ]['attributes_edited'] = $variation_attributes;
							}
//							unset( $variations[ $variation_k ]['attributes'][ $attribute_slug ] );
						}
					}
				}
				unset( $attributes[ $attribute_k ] );
				$variations = array_values( $variations );
				update_post_meta( $product_id, '_vi_wad_attributes', $attributes );
				update_post_meta( $product_id, '_vi_wad_variations', $variations );
				if ( is_array( $split_variations ) ) {
					update_post_meta( $product_id, '_vi_wad_split_variations', $split_variations );
				}
				$remove = true;
			}
		}

		return $remove;
	}

	/**
	 * Delete all products from Import list
	 */
	public function empty_import_list() {
		global $wpdb;
		$page = isset( $_GET['page'] ) ? wp_unslash( $_GET['page'] ) : '';
		if ( ! empty( $_GET['vi_wad_empty_product_list'] ) && $page === 'woocommerce-alidropship-import-list' ) {
			$nonce = isset( $_GET['_wpnonce'] ) ? wp_unslash( $_GET['_wpnonce'] ) : '';
			if ( wp_verify_nonce( $nonce ) ) {
				if ( current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-import-list' ) ) ) {
					$posts = "{$wpdb->prefix}posts";
					$sql   = "DELETE from {$posts} WHERE {$posts}.post_type='vi_wad_draft_product' AND {$posts}.post_status='draft'";
					$sql   = apply_filters( 'vi_wad_empty_import_list_sql', $sql );
					$wpdb->query( $sql );
					wp_safe_redirect( admin_url( "admin.php?page={$page}" ) );
					exit();
				}
			}
		}
	}

	/**
	 * Move all images that are process in the background to Failed images so that they can be imported manually
	 */
	public function move_queued_images() {
		global $wpdb;
		if ( ! empty( $_GET['vi_wad_move_queued_images'] ) ) {
			$nonce = isset( $_GET['_wpnonce'] ) ? wp_unslash( $_GET['_wpnonce'] ) : '';
			if ( wp_verify_nonce( $nonce ) ) {
				$table   = "{$wpdb->prefix}options";
				$query   = "select * from {$table} where option_name like '%vi_wad_background_download_images_batch%'";
				$results = $wpdb->get_results( $query, ARRAY_A );
				foreach ( $results as $result ) {
					$images = maybe_unserialize( $result['option_value'] );
					$delete = false;
					foreach ( $images as $image ) {
						if ( get_post_type( $image['woo_product_id'] ) === 'product' ) {
							if ( VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::insert( $image['woo_product_id'], implode( ',', $image['product_ids'] ), $image['src'], intval( $image['set_gallery'] ) ) ) {
								$delete = true;
							}
						} else {
							$delete = true;
						}
					}
					if ( $delete ) {
						delete_option( $result['option_name'] );
					}
				}
				wp_safe_redirect( remove_query_arg( array( 'vi_wad_move_queued_images', '_wpnonce' ) ) );
				exit();
			}
		}
	}

	/**
	 * Add notices when images are being imported, when images are in the queue but not processed, when images are all imported or when cron is late
	 */
	public function admin_notices() {
		if ( self::$process_image->is_process_running() ) {
			$is_late = false;
			$next    = wp_next_scheduled( 'wp_vi_wad_background_download_images_cron' );
			if ( $next ) {
				$late = $next - time();
				if ( $late < - 300 ) {
					$is_late = true;
				}
			}
			if ( $is_late ) {
				?>
                <div class="notice notice-error">
                    <p>
						<?php printf( __( '<strong>ALD - AliExpress Dropshipping and Fulfillment for WooCommerce</strong>: <i>wp_vi_wad_background_download_images_cron</i> is late, queued product images may not be processed. If you want to move all queued images to Failed images page to handle them manually, please click <a href="%s">Move</a>', 'woocommerce-alidropship' ), wp_nonce_url( add_query_arg( array( 'vi_wad_move_queued_images' => 1 ) ) ) ) ?>
                    </p>
                </div>
				<?php
			} else {
				?>
                <div class="notice notice-warning">
                    <p>
						<?php _e( '<strong>ALD - AliExpress Dropshipping and Fulfillment for WooCommerce</strong>: Product images are still being processed in the background, please do not edit products/go to product edit page until all images are processed completely.', 'woocommerce-alidropship' ) ?>
                    </p>
                </div>
				<?php
			}
		} else {
			if ( self::$process_image->is_queue_empty() ) {
				if ( get_transient( 'vi_wad_background_download_images_complete' ) ) {
					delete_transient( 'vi_wad_background_download_images_complete' );
					?>
                    <div class="updated">
                        <p>
							<?php _e( '<strong>ALD - AliExpress Dropshipping and Fulfillment for WooCommerce</strong>: Finish processing product images', 'woocommerce-alidropship' ) ?>
                        </p>
                    </div>
					<?php
				}
			} else {
				?>
                <div class="notice notice-warning">
                    <p>
						<?php _e( '', 'woocommerce-alidropship' ) ?>
						<?php printf( __( '<strong>ALD - AliExpress Dropshipping and Fulfillment for WooCommerce</strong>: There are still images in the queue but background process is not running. <a href="%s">Run</a> or <a href="%s">Move to Failed images</a>', 'woocommerce-alidropship' ), wp_nonce_url( add_query_arg( array( 'vi_wad_run_download_product_image' => 1 ) ) ), wp_nonce_url( add_query_arg( array( 'vi_wad_move_queued_images' => 1 ) ) ) ) ?>
                    </p>
                </div>
				<?php
			}
		}
	}

	/**
	 * Save attributes after changing slug and/or values
	 */
	public function save_attributes() {
		self::check_ajax_referer();
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-import-list' ) ) ) {
			wp_die();
		}
		$response = array(
			'status'       => 'error',
			'new_slug'     => '',
			'change_value' => false,
			'message'      => '',
		);
		parse_str( $_POST['form_data'], $form_data );
		$data          = isset( $form_data['vi_wad_product'] ) ? $form_data['vi_wad_product'] : array();
		$product_data  = array_values( $data )[0];
		$product_id    = array_keys( $data )[0];
		$new_attribute = isset( $product_data['attributes'] ) ? stripslashes_deep( $product_data['attributes'] ) : array();
		$attributes    = get_post_meta( $product_id, '_vi_wad_attributes', true );
//		$attributes    = self::get_product_attributes($product_id);
		$variations = get_post_meta( $product_id, '_vi_wad_variations', true );
//		$variations    = self::get_product_variations($product_id);

		$change_slug  = '';
		$change_value = false;
		if ( count( $new_attribute ) && count( $attributes ) ) {
			$response['status'] = 'success';
			$new_attribute_v    = array_values( $new_attribute )[0];
			$attribute_k        = array_keys( $new_attribute )[0];
			if ( ! empty( $new_attribute_v['name'] ) && isset( $attributes[ $attribute_k ] ) ) {
				$new_slug       = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::sanitize_taxonomy_name( $new_attribute_v['name'] );
				$attribute_slug = isset( $attributes[ $attribute_k ]['slug_edited'] ) ? $attributes[ $attribute_k ]['slug_edited'] : $attributes[ $attribute_k ]['slug'];
				if ( ! self::is_attribute_value_equal( $new_slug, $attribute_slug ) ) {
					$change_slug = $new_slug;
					foreach ( $variations as $variation_k => $variation ) {
						$v_attributes = isset( $variation['attributes_edited'] ) ? $variation['attributes_edited'] : $variation['attributes'];
						if ( isset( $v_attributes[ $attribute_slug ] ) ) {
							$v_attributes[ $new_slug ] = $v_attributes[ $attribute_slug ];
							unset( $v_attributes[ $attribute_slug ] );
							$variations[ $variation_k ]['attributes_edited'] = $v_attributes;
						}
					}
					$attributes[ $attribute_k ]['slug_edited'] = $new_slug;
					$attributes[ $attribute_k ]['name_edited'] = $new_attribute_v['name'];
					$attribute_slug                            = $new_slug;
				}
				if ( ! empty( $new_attribute_v['values'] ) ) {
					$new_values    = $new_attribute_v['values'];
					$values_edited = isset( $attributes[ $attribute_k ]['values_edited'] ) ? $attributes[ $attribute_k ]['values_edited'] : $attributes[ $attribute_k ]['values'];
					foreach ( $values_edited as $value_k => $value ) {
						if ( ! empty( $new_values[ $value_k ] ) ) {
							$new_value = trim( $new_values[ $value_k ] );
							if ( $new_value !== $value ) {
								$change_value = true;
								foreach ( $variations as $variation_k => $variation ) {
									$v_attributes = isset( $variation['attributes_edited'] ) ? $variation['attributes_edited'] : $variation['attributes'];
									if ( isset( $v_attributes[ $attribute_slug ] ) && self::is_attribute_value_equal( $v_attributes[ $attribute_slug ], $value ) ) {

										$values_edited[ $value_k ] = $new_value;

										$v_attributes[ $attribute_slug ] = $new_value;

										$variations[ $variation_k ]['attributes_edited'] = $v_attributes;
									}
								}
								$attributes[ $attribute_k ]['values_edited'] = $values_edited;
							}
						}
					}
				}
			}
		}

		if ( $change_slug || $change_value ) {
			update_post_meta( $product_id, '_vi_wad_attributes', $attributes );
			update_post_meta( $product_id, '_vi_wad_variations', $variations );
//			VI_WOOCOMMERCE_ALIDROPSHIP_DATA::update_attributes_list( self::get_product_attributes($product_id) );
		}
		$response['new_slug']     = $change_slug;
		$response['change_value'] = $change_value;
		wp_send_json( $response );
	}

	/**
     * Compare 2 attribute values - ignore case-sensitive
     *
	 * @param $value_1
	 * @param $value_2
	 *
	 * @return bool
	 */
	public static function is_attribute_value_equal( $value_1, $value_2 ) {
		if ( function_exists( 'mb_strtolower' ) ) {
			return ( mb_strtolower( $value_1 ) === mb_strtolower( $value_2 ) );
		} else {
			return ( strtolower( $value_1 ) === strtolower( $value_2 ) );
		}
	}

	/**
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public function save_screen_options( $status, $option, $value ) {
		if ( $option === 'vi_wad_per_page' ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Use ajax to load variations tab when it's open to avoid too much html loaded
	 */
	public function load_variations_table() {
		self::check_ajax_referer();
		$key        = isset( $_GET['product_index'] ) ? absint( sanitize_text_field( $_GET['product_index'] ) ) : '';
		$product_id = isset( $_GET['product_id'] ) ? sanitize_text_field( $_GET['product_id'] ) : '';
		if ( $key > - 1 && $product_id ) {
			remove_all_filters( 'woocommerce_currency_symbol' );
			$currency                    = 'USD';
			$woocommerce_currency        = get_option( 'woocommerce_currency' );
			$woocommerce_currency_symbol = get_woocommerce_currency_symbol( $woocommerce_currency );
			$manage_stock                = self::$settings->get_params( 'manage_stock' );
			$use_different_currency      = false;
//			$variations                  = get_post_meta( $product_id, '_vi_wad_variations', true );
			$variations = self::get_product_variations( $product_id );
			$decimals   = wc_get_price_decimals();
			if ( $decimals < 1 ) {
				$decimals = 1;
			} else {
				$decimals = pow( 10, ( - 1 * $decimals ) );
			}
			if ( strtolower( $woocommerce_currency ) != strtolower( $currency ) ) {
				$use_different_currency = true;
			}
//			$attributes = get_post_meta( $product_id, '_vi_wad_attributes', true );
			$attributes   = self::get_product_attributes( $product_id );
			$parent       = array();
			$split_option = '';
			if ( is_array( $attributes ) && count( $attributes ) ) {
				foreach ( $attributes as $attribute_k => $attribute_v ) {
					$parent[ $attribute_k ] = $attribute_v['slug'];
					$attribute_values_count = count( $attribute_v['values'] );
					$attribute_name         = isset( $attribute_v['name'] ) ? $attribute_v['name'] : ucfirst( $attribute_v['slug'] );
					if ( is_array( $attribute_v['values'] ) && $attribute_values_count > 1 ) {
						$split_option .= '<span class="vi-ui button mini green ' . self::set( 'button-split' ) . '" data-split_product_message="' . sprintf( _n( 'Split to %s product by %s?', 'Split to %s products by %s?', $attribute_values_count, 'woocommerce-alidropship' ), $attribute_values_count, $attribute_name ) . '" data-product_id="' . $product_id . '" data-split_attribute_id="' . $attribute_k . '">' . sprintf( __( 'Split product by %s(%s)', 'woocommerce-alidropship' ), $attribute_name, $attribute_values_count ) . '</span>';
					}
				}
			}
			if ( $split_option ) {
				$split_option .= '<span class="vi-ui button mini green ' . esc_attr( self::set( 'button-split' ) ) . '"
                                                  data-product_id="' . esc_attr( $product_id ) . '">' . esc_html__( 'Split product by selected variation(s)', 'woocommerce-alidropship' ) . '</span>';
			}
			ob_start();
			self::variation_html( $key, $parent, $attributes, $manage_stock, $variations, $use_different_currency, $currency, $product_id, $woocommerce_currency_symbol, $decimals, false );
			$return = ob_get_clean();
			wp_send_json(
				array(
					'status'           => 'success',
					'data'             => $return,
					'split_option'     => $split_option,
					'variations_count' => self::$variations_count,
				)
			);
		} else {
			wp_send_json(
				array(
					'status' => 'error',
					'data'   => esc_html__( 'Missing required arguments', 'woocommerce-alidropship' )
				)
			);
		}
	}

	/**
	 * Ajax split product
	 */
	public function split_product() {
		self::check_ajax_referer();
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-import-list' ) ) ) {
			wp_die();
		}
		$product_id           = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$split_attribute_id   = isset( $_POST['split_attribute_id'] ) ? sanitize_text_field( $_POST['split_attribute_id'] ) : '';
		$response             = array(
			'status'  => 'error',
			'message' => esc_html__( 'Can not split this product', 'woocommerce-alidropship' ),
		);
		$split_variations_ids = isset( $_POST['split_variations_ids'] ) ? stripslashes_deep( $_POST['split_variations_ids'] ) : array();
		$product              = get_post( $product_id );
		if ( $product ) {
			$attributes    = get_post_meta( $product_id, '_vi_wad_attributes', true );
			$variations    = get_post_meta( $product_id, '_vi_wad_variations', true );
			$product_title = $product->post_title;
			if ( $split_attribute_id ) {
				if ( count( $attributes ) && count( $variations ) > 1 ) {
					$split_attribute = isset( $attributes[ $split_attribute_id ] ) ? $attributes[ $split_attribute_id ] : array();
					if ( count( $split_attribute ) ) {
						if ( count( $split_attribute['values'] ) > 1 ) {
							$split_variations = array();
							foreach ( $split_attribute['values'] as $split_attribute_k => $split_attribute_v ) {
								$new_variations = array(
									'images'          => array(),
									'skuId'           => array(),
									'attribute_value' => $split_attribute_v,
								);
								foreach ( $variations as $variation_k => $variation_v ) {
									if ( $variation_v['attributes'][ $split_attribute['slug'] ] == $split_attribute_v ) {
										$new_variations['skuId'][] = $variation_v['skuAttr'];
										if ( $variation_v['image'] ) {
											$new_variations['images'][] = $variation_v['image'];
										}
									}
								}
								if ( count( $new_variations['skuId'] ) ) {
									$new_variations['images'] = array_unique( $new_variations['images'] );
									$split_variations[]       = $new_variations;
								}
							}
							if ( count( $split_variations ) > 1 ) {
								$split_variation = array_shift( $split_variations );
								update_post_meta( $product_id, '_vi_wad_split_variations', $split_variation['skuId'] );
								if ( count( $split_variation['images'] ) ) {
									$gallery = get_post_meta( $product_id, '_vi_wad_gallery', true );
									update_post_meta( $product_id, '_vi_wad_gallery', array_merge( $split_variation['images'], $gallery ) );
								}
								$meta             = get_post_meta( $product_id );
								$split_success    = 1;
								$clone_variations = $variations;
								$clone_attributes = $attributes;
								if ( self::$settings->get_params( 'split_auto_remove_attribute' ) ) {
									if ( self::remove_product_attribute( $product_id, array( $split_attribute_id => $split_attribute ), $split_variation['attribute_value'], $split_variation['skuId'], $clone_attributes, $clone_variations ) ) {
										wp_update_post(
											array(
												'ID'         => $product_id,
												'post_title' => "{$product_title} - {$split_variation['attribute_value']}"
											) );
									}
								}
								foreach ( $split_variations as $split_variation ) {
									$split_product = wp_insert_post( array(
										'post_title'   => $product->post_title,
										'post_type'    => 'vi_wad_draft_product',
										'post_status'  => 'draft',
										'post_excerpt' => '',
										'post_content' => $product->post_content,
										'post_date'    => $product->post_date,
									), true );
									if ( ! is_wp_error( $split_product ) ) {
										$split_success ++;
										foreach ( $meta as $meta_key => $meta_value ) {
											$meta_value = maybe_unserialize( $meta_value[0] );
											if ( $meta_key === '_vi_wad_split_variations' ) {
												continue;
											}
											if ( $meta_key === '_vi_wad_gallery' ) {
												if ( count( $split_variation['images'] ) ) {
													$meta_value = array_merge( $split_variation['images'], $meta_value );
												}
											}
											update_post_meta( $split_product, $meta_key, $meta_value );
										}
										update_post_meta( $split_product, '_vi_wad_split_variations', $split_variation['skuId'] );
										$clone_variations = $variations;
										$clone_attributes = $attributes;
										if ( self::$settings->get_params( 'split_auto_remove_attribute' ) ) {
											if ( self::remove_product_attribute( $split_product, array( $split_attribute_id => $split_attribute ), $split_variation['attribute_value'], $split_variation['skuId'], $clone_attributes, $clone_variations ) ) {
												wp_update_post(
													array(
														'ID'         => $split_product,
														'post_title' => "{$product_title} - {$split_variation['attribute_value']}"
													) );
											}
										}
									}
								}
								$response['status']  = 'success';
								$response['message'] = sprintf( _n( 'Split %s product successfully', 'Split %s products successfully', $split_success, 'woocommerce-alidropship' ), $split_success, 'woocommerce-alidropship' );
							}
						} else {

						}
					} else {

					}
				} else {

				}
			} elseif ( count( $split_variations_ids ) < count( $variations ) ) {
				$new_variations_1 = array(
					'images' => array(),
					'skuId'  => array(),
				);
				$new_variations_2 = array(
					'images' => array(),
					'skuId'  => array(),
				);
				foreach ( $variations as $variation_k => $variation_v ) {
					$variation_sku_id = $variation_v['skuAttr'];
					if ( in_array( $variation_sku_id, $split_variations_ids ) ) {
						$new_variations_1['skuId'][] = $variation_sku_id;
						if ( $variation_v['image'] ) {
							$new_variations_1['images'][] = $variation_v['image'];
						}
					} else {
						$new_variations_2['skuId'][] = $variation_sku_id;
						if ( $variation_v['image'] ) {
							$new_variations_2['images'][] = $variation_v['image'];
						}
					}
				}
				if ( count( $new_variations_1['skuId'] ) && count( $new_variations_2['skuId'] ) ) {
					$new_variations_1['images'] = array_unique( $new_variations_1['images'] );
					$new_variations_2['images'] = array_unique( $new_variations_2['images'] );
					update_post_meta( $product_id, '_vi_wad_split_variations', $new_variations_2['skuId'] );

					$meta          = get_post_meta( $product_id );
					$split_product = wp_insert_post( array(
						'post_title'   => $product->post_title,
						'post_type'    => 'vi_wad_draft_product',
						'post_status'  => 'draft',
						'post_excerpt' => '',
						'post_content' => $product->post_content,
					), true );
					$split_success = 1;
					if ( ! is_wp_error( $split_product ) ) {
						$split_success ++;
						foreach ( $meta as $meta_key => $meta_value ) {
							$meta_value = maybe_unserialize( $meta_value[0] );
							if ( $meta_key === '_vi_wad_split_variations' ) {
								continue;
							}
							if ( $meta_key === '_vi_wad_gallery' ) {
								if ( count( $new_variations_1['images'] ) ) {
									$meta_value = array_merge( $new_variations_1['images'], $meta_value );
								}
							}
							update_post_meta( $split_product, $meta_key, $meta_value );
						}
						update_post_meta( $split_product, '_vi_wad_split_variations', $new_variations_1['skuId'] );
					}
					$response['status']  = 'success';
					$response['message'] = sprintf( _n( 'Split %s product successfully', 'Split %s products successfully', $split_success, 'woocommerce-alidropship' ), $split_success, 'woocommerce-alidropship' );
				} else {

				}
			} else {

			}
		} else {

		}
		wp_send_json( $response );
	}

	/**
	 * Adds the order processing count to the menu.
	 */
	public function menu_product_count() {
		global $submenu;
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();//show_menu_count may be changed after saving settings
		if ( isset( $submenu['woocommerce-alidropship'] ) && in_array( 'import_list', self::$settings->get_params( 'show_menu_count' ) ) ) {
			// Add count if user has access.
			if ( apply_filters( 'woo_aliexpress_dropship_product_count_in_menu', true ) || current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-import-list' ) ) ) {
				$count         = wp_count_posts( 'vi_wad_draft_product', 'readable' );
				$product_count = $count->draft + $count->override;
				foreach ( $submenu['woocommerce-alidropship'] as $key => $menu_item ) {
					if ( 0 === strpos( $menu_item[0], _x( 'Import List', 'Admin menu name', 'woocommerce-alidropship' ) ) ) {
						$submenu['woocommerce-alidropship'][ $key ][0] .= ' <span class="update-plugins count-' . esc_attr( $product_count ) . '"><span class="' . self::set( 'import-list-count' ) . '">' . number_format_i18n( $product_count ) . '</span></span>'; // WPCS: override ok.
						break;
					}
				}
			}
		}
	}

	/**
     * Selection when overridden by a simple product
     *
	 * @param $variation
	 * @param bool $is_link
	 *
	 * @return false|string
	 */
	public static function get_override_simple_select_html( $variation, $is_link = false ) {
		ob_start();
		?>
        <select class="vi-ui fluid dropdown <?php echo esc_attr( self::set( 'override-with' ) ) ?>">
            <option value=""><?php esc_html_e( $is_link ? 'Do not link' : 'Do not replace', 'woocommerce-alidropship' ) ?></option>
            <option value="<?php echo esc_attr( $variation['skuAttr'] ? $variation['skuAttr'] : 'yes' ) ?>"><?php esc_html_e( $is_link ? 'Link to product' : 'Replace with new product', 'woocommerce-alidropship' ) ?></option>
        </select>
		<?php
		return ob_get_clean();
	}

	/**
     * Selection when overridden by a variable product
     *
	 * @param $product_or_variation_id
	 * @param $variations
	 * @param string $current
	 * @param bool $is_link
	 *
	 * @return false|string
	 */
	public static function get_override_variable_select_html( $product_or_variation_id, $variations, $current = '', $is_link = false ) {
		ob_start();
		?>
        <select class="vi-ui fluid dropdown <?php echo esc_attr( self::set( 'override-with' ) ) ?>">
            <option value=""><?php esc_html_e( $is_link ? 'Do not link' : 'Do not replace', 'woocommerce-alidropship' ) ?></option>
			<?php
			$sku_props = get_post_meta( $product_or_variation_id, '_aliexpress_sku_props', true );
			$search    = false;
			if ( ! $sku_props ) {
				/*Automatically select variation for products migrated from ali2woo*/
				$sku_props = get_post_meta( $product_or_variation_id, '_vi_wad_aliexpress_variation_attr', true );
			}
			if ( $sku_props ) {
				/*Automatically select variation when reimporting products*/
				$search = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::search_sku_attr( $sku_props, array_column( $variations, 'skuAttr' ) );
			}
			foreach ( $variations as $key => $variation ) {
				$attribute = implode( ', ', array_map( array(
					'VI_WOOCOMMERCE_ALIDROPSHIP_DATA',
					'sanitize_taxonomy_name'
				), array_values( $variation['attributes'] ) ) );
				$selected  = '';
				if ( $current ) {
					if ( ! in_array( $current, self::$override_selecteds ) ) {
						if ( $search === $key || self::is_attribute_value_equal( $current, $attribute ) ) {
							$selected                   = 'selected';
							self::$override_selecteds[] = $current;
						}
					}
				}
				if ( ! $selected ) {
					if ( $search === $key ) {
						$selected = 'selected';
					}
				}
				?>
                <option value="<?php echo esc_attr( $variation['skuAttr'] ) ?>" <?php echo esc_attr( $selected ) ?>><?php echo esc_html( $attribute ) ?></option>
				<?php
			}
			?>
        </select>
		<?php
		return ob_get_clean();
	}

	/**
     * Override a simple product
     *
	 * @param $woo_product WC_Product
	 * @param $woo_product_id
	 * @param $variations
	 * @param $found_item
	 * @param $is_simple
	 * @param bool $is_link
	 *
	 * @return false|string
	 */
	public static function get_override_simple_html( $woo_product, $woo_product_id, $variations, $found_item, $is_simple, $is_link = false ) {
		ob_start();
		?>
        <tr class="<?php echo esc_attr( self::set( 'override-order-container' ) ) ?>"
            data-replace_item_id="<?php echo esc_attr( $woo_product_id ) ?>">
            <td class="<?php echo esc_attr( self::set( 'override-from-td' ) ) ?>">
                <div class="<?php echo esc_attr( self::set( 'override-from' ) ) ?>">
					<?php
					if ( $woo_product->get_image_id() ) {
						$image_src = wp_get_attachment_thumb_url( $woo_product->get_image_id() );
					} elseif ( $woo_product->get_image_id() ) {
						$image_src = wp_get_attachment_thumb_url( $woo_product->get_image_id() );
					} else {
						$image_src = wc_placeholder_img_src();
					}
					if ( $image_src ) {
						?>
                        <div class="<?php echo esc_attr( self::set( 'override-from-image' ) ) ?>">
                            <img src="<?php echo esc_url( $image_src ) ?>" width="30px"
                                 height="30px">
                        </div>
						<?php
					}
					?>
                    <div class="<?php echo esc_attr( self::set( 'override-from-title' ) ) ?>">
						<?php
						echo esc_html( $woo_product->get_title() );
						?>
                    </div>
                </div>
            </td>
            <td class="<?php echo esc_attr( self::set( 'override-unfulfilled-items-count' ) ) ?>">
				<?php
				echo $found_item;
				?>
            </td>
            <td class="<?php echo esc_attr( self::set( 'override-with-attributes' ) ) ?>">
				<?php
				if ( $is_simple ) {
					echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( self::get_override_simple_select_html( $variations[0], $is_link ) );
				} else {
					echo self::get_override_variable_select_html( $woo_product_id, $variations, '', $is_link );
				}
				?>
            </td>
        </tr>
		<?php
		return ob_get_clean();
	}

	/**
     * Override a variable product
     *
	 * @param $woo_product WC_Product
	 * @param $woo_product_child
	 * @param $variations
	 * @param $item_count
	 * @param $is_simple
	 * @param bool $is_link
	 *
	 * @return false|string
	 */
	public static function get_override_variation_html( $woo_product, $woo_product_child, $variations, $item_count, $is_simple, $is_link = false ) {
		$html                  = '';
		$woo_product_child_obj = wc_get_product( $woo_product_child );
		if ( $woo_product_child_obj ) {
			$current = implode( ', ', $woo_product_child_obj->get_attributes() );
			ob_start();
			?>
            <tr class="<?php echo esc_attr( self::set( 'override-order-container' ) ) ?>"
                data-replace_item_id="<?php echo esc_attr( $woo_product_child ) ?>">
                <td class="<?php echo esc_attr( self::set( 'override-from-td' ) ) ?>">
                    <div class="<?php echo esc_attr( self::set( 'override-from' ) ) ?>">
						<?php
						if ( $woo_product_child_obj ) {
							if ( $woo_product_child_obj->get_image_id() ) {
								$image_src = wp_get_attachment_thumb_url( $woo_product_child_obj->get_image_id() );
							} elseif ( $woo_product->get_image_id() ) {
								$image_src = wp_get_attachment_thumb_url( $woo_product->get_image_id() );
							} else {
								$image_src = wc_placeholder_img_src();
							}
							if ( $image_src ) {
								?>
                                <div class="<?php echo esc_attr( self::set( 'override-from-image' ) ) ?>">
                                    <img src="<?php echo esc_url( $image_src ) ?>" width="30px"
                                         height="30px">
                                </div>
								<?php
							}

						}
						?>
                        <div class="<?php echo esc_attr( self::set( 'override-from-title' ) ) ?>">
							<?php
							echo esc_html( $current );
							?>
                        </div>
                    </div>
                </td>
                <td><?php echo esc_html( $item_count ); ?></td>
                <td class="<?php echo esc_attr( self::set( 'override-with-attributes' ) ) ?>">
					<?php
					if ( $is_simple ) {
						echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( self::get_override_simple_select_html( $variations[0], $is_link ) );
					} else {
						echo self::get_override_variable_select_html( $woo_product_child, $variations, $current, $is_link );
					}
					?>
                </td>
            </tr>
			<?php
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
     * Override a product
     *
	 * @throws Exception
	 */
	public function override() {
		self::check_ajax_referer();
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-import-list' ) ) ) {
			wp_die();
		}
		vi_wad_set_time_limit();
		parse_str( $_POST['form_data'], $form_data );
		if ( ! isset( $form_data['z_check_max_input_vars'] ) ) {
			/*z_check_max_input_vars is the last key of POST data. If it does not exist in $form_data after using parse_str(), some data may also be missing*/
			wp_send_json( array(
				'status'  => 'error',
				'message' => esc_html__( 'PHP max_input_vars is too low, please increase it in php.ini', 'woocommerce-alidropship' ),
			) );
		}
		$data                    = isset( $form_data['vi_wad_product'] ) ? $form_data['vi_wad_product'] : array();
		$selected                = isset( $_POST['selected'] ) ? vi_wad_json_decode( stripslashes_deep( $_POST['selected'] ) ) : array();
		$override_product_id     = isset( $_POST['override_product_id'] ) ? sanitize_text_field( $_POST['override_product_id'] ) : '';
		$override_woo_id         = isset( $_POST['override_woo_id'] ) ? sanitize_text_field( $_POST['override_woo_id'] ) : '';
		$override_options        = array(
			'override_title'       => isset( $_POST['override_title'] ) ? sanitize_text_field( $_POST['override_title'] ) : '',
			'override_images'      => isset( $_POST['override_images'] ) ? sanitize_text_field( $_POST['override_images'] ) : '',
			'override_description' => isset( $_POST['override_description'] ) ? sanitize_text_field( $_POST['override_description'] ) : '',
		);
		$override_hide           = isset( $_POST['override_hide'] ) ? sanitize_text_field( $_POST['override_hide'] ) : '';
		$override_keep_product   = isset( $_POST['override_keep_product'] ) ? sanitize_text_field( $_POST['override_keep_product'] ) : '';
		$override_link_only      = isset( $_POST['override_link_only'] ) ? sanitize_text_field( $_POST['override_link_only'] ) : '';
		$override_find_in_orders = isset( $_POST['override_find_in_orders'] ) ? sanitize_text_field( $_POST['override_find_in_orders'] ) : '';
		if ( $override_hide ) {
			/*If "Save my choices and do not show these options again" is checked, save user's choices*/
			$params = self::$settings->get_params();
			foreach ( $override_options as $override_option_k => $override_option_v ) {
				$params[ $override_option_k ] = $override_option_v;
			}
			$params['override_hide']           = $override_hide;
			$params['override_keep_product']   = $override_keep_product;
			$params['override_link_only']      = $override_link_only;
			$params['override_find_in_orders'] = $override_find_in_orders;
			update_option( 'wooaliexpressdropship_params', $params );
		} elseif ( self::$settings->get_params( 'override_hide' ) ) {
			/*If Hide options is checked, get these options from settings*/
			foreach ( $override_options as $override_option_k => $override_option_v ) {
				$override_options[ $override_option_k ] = self::$settings->get_params( $override_option_k );
			}
			$override_keep_product   = self::$settings->get_params( 'override_keep_product' );
			$override_link_only      = self::$settings->get_params( 'override_link_only' );
			$override_find_in_orders = self::$settings->get_params( 'override_find_in_orders' );
		}
		if ( ! $override_product_id && ! $override_woo_id ) {
			wp_send_json( array(
				'status'  => 'error',
				'message' => esc_html__( 'Product is deleted from your store', 'woocommerce-alidropship' ),
			) );
		}
		$product_data     = array_values( $data )[0];
		$product_draft_id = array_keys( $data )[0];


		$check_orders     = isset( $_POST['check_orders'] ) ? sanitize_text_field( $_POST['check_orders'] ) : '';
		$found_items      = isset( $_POST['found_items'] ) ? stripslashes_deep( $_POST['found_items'] ) : array();
		$replace_items    = isset( $_POST['replace_items'] ) ? array_filter( stripslashes_deep( $_POST['replace_items'] ) ) : array();
		if ( $override_product_id ) {
			$woo_product_id = get_post_meta( $override_product_id, '_vi_wad_woo_id', true );
		} else {
			$woo_product_id      = $override_woo_id;
			$override_product_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_woo_id( $woo_product_id, false, false, array(
				'publish',
				'override'
			) );
		}
		$attributes = self::get_product_attributes( $product_draft_id );
		if ( ! count( $selected[ $product_draft_id ] ) ) {
			wp_send_json( array(
				'status'  => 'error',
				'message' => esc_html__( 'Please select at least 1 variation to import this product.', 'woocommerce-alidropship' ),
			) );
		}
		if ( ! $product_draft_id ) {
			wp_send_json( array(
				'status'  => 'error',
				'message' => esc_html__( 'Invalid data', 'woocommerce-alidropship' ),
			) );
		}
		if ( ! get_post_status( $product_draft_id ) ) {
			wp_send_json( array(
				'status'  => 'error',
				'message' => esc_html__( 'This product was removed', 'woocommerce-alidropship' ),
			) );
		}
		if ( ! in_array( get_post_status( $product_draft_id ), array( 'draft', 'override' ) ) ) {
			wp_send_json( array(
				'status'  => 'error',
				'message' => esc_html__( 'This product has already been linked', 'woocommerce-alidropship' ),
			) );
		}
		if ( ! self::$settings->get_params( 'auto_generate_unique_sku' ) && VI_WOOCOMMERCE_ALIDROPSHIP_DATA::sku_exists( $product_data['sku'] ) && $product_data['sku'] != get_post_meta( $woo_product_id, '_sku', true ) ) {
			wp_send_json( array(
				'status'  => 'error',
				'message' => esc_html__( 'Sku exists.', 'woocommerce-alidropship' ),
			) );
		}
		if ( ! $override_product_id || get_post_meta( $override_product_id, '_vi_wad_sku', true ) == get_post_meta( $product_draft_id, '_vi_wad_sku', true ) ) {
			$override_keep_product = '1';
		}
		$woo_product         = wc_get_product( $woo_product_id );
		$migration_link_only = isset( $_POST['migration_link_only'] ) ? sanitize_text_field( $_POST['migration_link_only'] ) : '';

		if ( $woo_product ) {
			if ( 1 != $check_orders && ( $override_find_in_orders == 1 || $override_keep_product == 1 ) ) {
				$is_simple = false;
				if ( ! is_array( $attributes ) || ! count( $attributes ) || ( isset( $product_data['variations'] ) && count( $selected[ $product_draft_id ] ) === 1 && self::$settings->get_params( 'simple_if_one_variation' ) ) ) {
					$is_simple = true;
				}
				if ( $is_simple ) {
					$variations = array( array_values( $product_data['variations'] )[0] );
				} else {
					if ( isset( $product_data['variations'] ) ) {
						$variations = array_values( $product_data['variations'] );
					} else {
						$variations = self::get_product_variations( $product_draft_id, true );
					}
				}
				$replace_order_html = '';
				if ( $override_woo_id && $migration_link_only ) {
					if ( $woo_product->is_type( 'variable' ) ) {
						$woo_product_children = $woo_product->get_children();
						if ( count( $woo_product_children ) ) {
							foreach ( $woo_product_children as $woo_product_child ) {
								$found_item = self::query_order_item_meta( array( 'order_item_type' => 'line_item' ), array(
									'meta_key'   => '_variation_id',
									'meta_value' => $woo_product_child
								) );
								self::skip_item_with_ali_order_id( $found_item );
								$found_items[ $woo_product_child ] = $found_item;
								$replace_order_html                .= self::get_override_variation_html( $woo_product, $woo_product_child, $variations, count( $found_item ), $is_simple, true );
							}
						}
					} else {
						$found_item = self::query_order_item_meta( array( 'order_item_type' => 'line_item' ), array(
							'meta_key'   => '_product_id',
							'meta_value' => $woo_product_id,
						) );
						self::skip_item_with_ali_order_id( $found_item );
						$found_items[ $woo_product_id ] = $found_item;
						$replace_order_html             = self::get_override_simple_html( $woo_product, $woo_product_id, $variations, count( $found_item ), $is_simple, true );
					}
				} else {
					if ( ! $is_simple && ( $override_link_only || $woo_product->is_type( 'variable' ) ) ) {
						if ( $woo_product->is_type( 'variable' ) ) {
							$woo_product_children = $woo_product->get_children();
							if ( count( $woo_product_children ) ) {
								foreach ( $woo_product_children as $woo_product_child ) {
									$found_item = self::query_order_item_meta( array( 'order_item_type' => 'line_item' ), array(
										'meta_key'   => '_variation_id',
										'meta_value' => $woo_product_child
									) );
									self::skip_item_with_ali_order_id( $found_item );
									if ( $override_keep_product || count( $found_item ) ) {
										$found_items[ $woo_product_child ] = $found_item;
										$replace_order_html                .= self::get_override_variation_html( $woo_product, $woo_product_child, $variations, count( $found_item ), $is_simple );
									}
								}
							}
						} else {
							$found_item = self::query_order_item_meta( array( 'order_item_type' => 'line_item' ), array(
								'meta_key'   => '_product_id',
								'meta_value' => $woo_product_id,
							) );
							self::skip_item_with_ali_order_id( $found_item );
							$found_items[ $woo_product_id ] = $found_item;
							$replace_order_html             = self::get_override_simple_html( $woo_product, $woo_product_id, $variations, count( $found_item ), $is_simple );
						}
					} else {
						$found_item = self::query_order_item_meta( array( 'order_item_type' => 'line_item' ), array(
							'meta_key'   => '_product_id',
							'meta_value' => $woo_product_id,
						) );
						self::skip_item_with_ali_order_id( $found_item );
						if ( count( $found_item ) ) {
							$found_items[ $woo_product_id ] = $found_item;
							$replace_order_html             = self::get_override_simple_html( $woo_product, $woo_product_id, $variations, count( $found_item ), $is_simple );
						}
					}
				}
				if ( count( $found_items ) ) {
					$message = $replace_col_head = $original_col_head = '';
					if ( $override_woo_id && $migration_link_only ) {
						$message           = '<div class="vi-ui message">' . esc_html__( '"Link variation only" option is enabled, this will only link target product(simple) or its variations(variable product) so that it can be managed by our plugin while its data will be the same', 'woocommerce-alidropship' ) . '</div>';
						$replace_col_head  = esc_html__( 'Link', 'woocommerce-alidropship' );
						$original_col_head = esc_html__( 'Current items', 'woocommerce-alidropship' );
					} else {
						$replace_col_head = esc_html__( 'Replacement', 'woocommerce-alidropship' );
						if ( $override_keep_product ) {
							$message = '<div class="vi-ui message warning">' . esc_html__( 'By selecting a replacement, a new variation will be created by modifying the respective overridden variation. Overridden variations with no replacement selected will be deleted', 'woocommerce-alidropship' ) . '</div>';
						}
						$original_col_head = esc_html__( 'Overridden items', 'woocommerce-alidropship' );
					}
					wp_send_json( array(
						'status'             => 'checked',
						'message'            => '',
						'found_items'        => $found_items,
						'replace_order_html' => $message . '<table class="vi-ui celled table"><thead><tr><th>' . $original_col_head . '</th><th width="1%">' . esc_html__( 'Found in unfulfilled orders', 'woocommerce-alidropship' ) . '</th><th>' . $replace_col_head . '</th></tr></thead><tbody>' . $replace_order_html . '</tbody></table>',
					) );
				}
			}
		} else {
			wp_send_json( array(
				'status'  => 'error',
				'message' => esc_html__( 'Overridden product does not exist', 'woocommerce-alidropship' ),
			) );
		}
		$variations_attributes = array();

		if ( isset( $product_data['variations'] ) ) {
			$variations = array_values( $product_data['variations'] );
			if ( count( $variations ) > 1 ) {
				$var_default = isset( $product_data['default_variation'] ) ? $product_data['default_variation'] : '';
				foreach ( $variations as $variations_v ) {
					if ( $var_default === $variations_v['skuAttr'] ) {
						$product_data['variation_default'] = $variations_v['attributes'];
					}
					$variations_attribute = isset( $variations_v['attributes'] ) ? $variations_v['attributes'] : array();
					if ( is_array( $variations_attribute ) && count( $variations_attribute ) ) {
						foreach ( $variations_attribute as $variations_attribute_k => $variations_attribute_v ) {
							if ( ! isset( $variations_attributes[ $variations_attribute_k ] ) ) {
								$variations_attributes[ $variations_attribute_k ] = array( $variations_attribute_v );
							} elseif ( ! in_array( $variations_attribute_v, $variations_attributes[ $variations_attribute_k ] ) ) {
								$variations_attributes[ $variations_attribute_k ][] = $variations_attribute_v;
							}
						}
					}
				}

				if ( is_array( $attributes ) && count( $attributes ) ) {
					foreach ( $attributes as $attributes_k => $attributes_v ) {
						if ( ! empty( $variations_attributes[ $attributes_v['slug'] ] ) ) {
							$attributes[ $attributes_k ]['values'] = array_intersect( $attributes[ $attributes_k ]['values'], $variations_attributes[ $attributes_v['slug'] ] );
						}
					}
				}
			}
		} else {
			$variations    = self::get_product_variations( $product_draft_id, true );
			$shipping_cost = 0;
			if ( self::$settings->get_params( 'show_shipping_option' ) ) {
				$shipping_info = self::get_shipping_info( $product_draft_id, '', '' );
				$shipping_cost = abs( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $shipping_info['shipping_cost'] ) );
			}
			if ( self::$settings->get_params( 'shipping_cost_after_price_rules' ) ) {
				foreach ( $variations as $variations_k => $variations_v ) {
					$variation_sale_price    = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations_v['sale_price'] );
					$variation_regular_price = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations_v['regular_price'] );
					$price                   = $variation_sale_price ? $variation_sale_price : $variation_regular_price;
					$sale_price              = self::$settings->process_price( $price, true );
					if ( $sale_price ) {
						$sale_price += $shipping_cost;
					}
					$regular_price                                = self::$settings->process_price( $price ) + $shipping_cost;
					$variations[ $variations_k ]['sale_price']    = self::$settings->process_exchange_price( $sale_price );
					$variations[ $variations_k ]['regular_price'] = self::$settings->process_exchange_price( $regular_price );
				}
			} else {
				foreach ( $variations as $variations_k => $variations_v ) {
					$variation_sale_price                         = $variations_v['sale_price'] ? ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations_v['sale_price'] ) + $shipping_cost ) : VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations_v['sale_price'] );
					$variation_regular_price                      = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations_v['regular_price'] ) + $shipping_cost;
					$price                                        = $variation_sale_price ? $variation_sale_price : $variation_regular_price;
					$variations[ $variations_k ]['sale_price']    = self::$settings->process_exchange_price( self::$settings->process_price( $price, true ) );
					$variations[ $variations_k ]['regular_price'] = self::$settings->process_exchange_price( self::$settings->process_price( $price ) );
				}
			}
		}

		if ( count( $variations ) ) {
			if ( $override_woo_id && $migration_link_only ) {
				if ( get_post_meta( $woo_product_id, '_vi_wad_aliexpress_product_id', true ) ) {
					wp_send_json( array(
						'status'      => 'error',
						'product_id'  => $woo_product_id,
						'message'     => esc_html__( 'This product has already been linked', 'woocommerce-alidropship' ),
						'button_html' => '',
					) );
				}
				if ( $woo_product->is_type( 'variable' ) ) {
					$children = $woo_product->get_children();
					if ( count( $replace_items ) < count( $children ) ) {
						wp_send_json( array(
							'status'      => 'error',
							'product_id'  => $woo_product_id,
							'message'     => esc_html__( 'Please select an original AliExpress variation for all existing variations', 'woocommerce-alidropship' ),
							'button_html' => '',
						) );
					}
					$skuAttrArray = array_column( $variations, 'skuAttr' );
					foreach ( $replace_items as $variation_id => $replace_items_v ) {
						$variations_key = array_search( $replace_items_v, $skuAttrArray );
						if ( $variations_key !== false ) {
							$product_variation = $variations[ $variations_key ];
							$product_obj       = wc_get_product( $variation_id );
							if ( $product_obj && in_array( $variation_id, $children ) ) {
								self::handle_aliexpress_variation_meta( $variation_id, $product_variation );
							}
						}
					}

				} else {
					if ( empty( $replace_items[ $woo_product_id ] ) ) {
						wp_send_json( array(
							'status'      => 'error',
							'product_id'  => $woo_product_id,
							'message'     => esc_html__( 'Please select an original AliExpress variation for all existing variations', 'woocommerce-alidropship' ),
							'button_html' => '',
						) );
					}
					$skuAttrArray   = array_column( $variations, 'skuAttr' );
					$variations_key = array_search( $replace_items[ $woo_product_id ], $skuAttrArray );
					if ( $variations_key !== false ) {
						self::handle_aliexpress_variation_meta( $woo_product_id, $variations[ $variations_key ] );
					}
				}
				wp_update_post( array(
					'ID'          => $product_draft_id,
					'post_status' => 'publish'
				) );
				update_post_meta( $product_draft_id, '_vi_wad_woo_id', $woo_product_id );
				update_post_meta( $woo_product_id, '_vi_wad_aliexpress_product_id', get_post_meta( $product_draft_id, '_vi_wad_sku', true ) );
				wp_send_json( array(
					'status'      => 'success',
					'product_id'  => $woo_product_id,
					'message'     => '',
					'button_html' => self::get_button_view_edit_html( $woo_product_id ),
				) );
			} else {
				if ( 1 != $override_options['override_title'] ) {
					$product_data['title'] = $woo_product->get_title();
				}
				if ( 1 != $override_options['override_images'] ) {
					$product_data['old_product_image']   = get_post_meta( $woo_product_id, '_thumbnail_id', true );
					$product_data['old_product_gallery'] = get_post_meta( $woo_product_id, '_product_image_gallery', true );
				}
				if ( 1 != $override_options['override_description'] ) {
					$product_data['short_description'] = $woo_product->get_short_description();
					$product_data['description']       = $woo_product->get_description();
				}
				if ( isset( $product_data['gallery'] ) ) {
					$product_data['gallery'] = array_values( array_filter( $product_data['gallery'] ) );
					if ( $product_data['image'] ) {
						$product_image_key = array_search( $product_data['image'], $product_data['gallery'] );
						if ( $product_image_key !== false ) {
							unset( $product_data['gallery'][ $product_image_key ] );
							$product_data['gallery'] = array_values( $product_data['gallery'] );
						}
					}
				} else {
					$product_data['gallery'] = array();
				}
				$variation_images                 = get_post_meta( $product_draft_id, '_vi_wad_variation_images', true );
				$product_data['variation_images'] = $variation_images;
				$product_data['attributes']       = $attributes;
				$product_data['variations']       = $variations;
				$product_data['parent_id']        = $product_draft_id;
				$product_data['ali_product_id']   = get_post_meta( $product_draft_id, '_vi_wad_sku', true );
				$disable_background_process       = self::$settings->get_params( 'disable_background_process' );
				if ( $override_keep_product ) {
					$is_simple  = false;
					$simple_key = 0;
					if ( ! is_array( $attributes ) || ! count( $attributes ) || ( count( $variations ) === 1 && self::$settings->get_params( 'simple_if_one_variation' ) ) ) {
						$is_simple = true;
					}
					if ( $override_link_only ) {
						if ( ! $replace_items ) {
							wp_send_json( array(
								'status'  => 'error',
								'message' => $woo_product->is_type( 'variable' ) ? esc_html__( 'Please select replacement for at least 1 variation', 'woocommerce-alidropship' ) : esc_html__( 'Please select replacement to override this product', 'woocommerce-alidropship' ),
							) );
						}
						if ( $woo_product->is_type( 'simple' ) ) {
							$is_simple = true;
							foreach ( $variations as $variation_k => $variation_v ) {
								if ( $variation_v['skuAttr'] === $replace_items[ $woo_product_id ] ) {
									$simple_key = $variation_k;
									break;
								}
							}
						}
					}
					$woo_product->set_status( $product_data['status'] );
					if ( $product_data['sku'] ) {
						$woo_product->set_sku( wc_product_generate_unique_sku( $woo_product_id, $product_data['sku'] ) );
					}
					if ( 1 == $override_options['override_title'] && $product_data['title'] ) {
						$woo_product->set_name( $product_data['title'] );
					}
					$dispatch = false;
					if ( 1 == $override_options['override_images'] ) {
						if ( $product_data['image'] ) {
							$thumb_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::download_image( $image_id, $product_data['image'], $woo_product_id );
							if ( ! is_wp_error( $thumb_id ) ) {
								update_post_meta( $woo_product_id, '_thumbnail_id', $thumb_id );
							}
						}
						self::process_gallery_images( $product_data['gallery'], $disable_background_process, $woo_product_id, $product_draft_id, $dispatch );
					}
					if ( 1 == $override_options['override_description'] ) {
						$woo_product->set_description( $product_data['description'] );
						self::process_description_images( $product_data['description'], $disable_background_process, $woo_product_id, $product_draft_id, $dispatch );
					}
					/*Set product tag*/
					if ( isset( $product_data['tags'] ) && is_array( $product_data['tags'] ) && count( $product_data['tags'] ) ) {
						self::set_product_tags( $woo_product_id, $product_data['tags'] );
						wp_set_post_terms( $woo_product_id, $product_data['tags'], 'product_tag', true );
					}
					/*Set product categories*/
					if ( isset( $product_data['categories'] ) && is_array( $product_data['categories'] ) && count( $product_data['categories'] ) ) {
						wp_set_post_terms( $woo_product_id, $product_data['categories'], 'product_cat', true );
					}
					/*Set product shipping class*/
					if ( isset( $product_data['shipping_class'] ) && $product_data['shipping_class'] && get_term_by( 'id', $product_data['shipping_class'], 'product_shipping_class' ) ) {
						wp_set_post_terms( $woo_product_id, array( intval( $product_data['shipping_class'] ) ), 'product_shipping_class', false );
					}
					update_post_meta( $woo_product_id, '_vi_wad_aliexpress_product_id', $product_data['ali_product_id'] );
					vi_wad_set_catalog_visibility( $woo_product_id, $product_data['catalog_visibility'] );
					$manage_stock = self::$settings->get_params( 'manage_stock' );
					$manage_stock = $manage_stock ? 'yes' : 'no';
					if ( $is_simple ) {
						self::handle_aliexpress_variation_meta( $woo_product_id, $variations[ $simple_key ] );
						if ( $woo_product->is_type( 'variable' ) ) {
							$woo_product->set_attributes( array() );
							$woo_product->save();
							$children = $woo_product->get_children();
							if ( count( $children ) ) {
								foreach ( $children as $variation_id ) {
									wp_delete_post( $variation_id, true );
								}
							}
							wp_set_object_terms( $woo_product_id, 'simple', 'product_type' );
						}
						$sale_price    = isset( $variations[ $simple_key ]['sale_price'] ) ? floatval( $variations[ $simple_key ]['sale_price'] ) : '';
						$regular_price = isset( $variations[ $simple_key ]['regular_price'] ) ? floatval( $variations[ $simple_key ]['regular_price'] ) : '';
						$price         = $regular_price;
						if ( $sale_price && $sale_price > 0 && $regular_price && $sale_price < $regular_price ) {
							$price = $sale_price;
						} else {
							$sale_price = '';
						}
						$woo_product->set_regular_price( $regular_price );
						$woo_product->set_sale_price( $sale_price );
						$woo_product->set_price( $price );
						$woo_product->set_manage_stock( $manage_stock );
						$woo_product->set_stock_status( 'instock' );
						if ( ! empty( $variations[ $simple_key ]['stock'] ) && $manage_stock === 'yes' ) {
							$woo_product->set_stock_quantity( $variations[ $simple_key ]['stock'] );
						}
						$woo_product->save();
						wp_set_object_terms( $woo_product_id, 'simple', 'product_type' );
						if ( $dispatch ) {
							self::$process_image->save()->dispatch();
						}
					} else {
						delete_post_meta( $woo_product_id, '_vi_wad_aliexpress_variation_id' );
						delete_post_meta( $woo_product_id, '_vi_wad_aliexpress_variation_attr' );
						delete_post_meta( $woo_product_id, '_vi_wad_aliexpress_variation_ship_from' );
						$default_attr = isset( $product_data['variation_default'] ) ? $product_data['variation_default'] : array();
						$attr_data    = self::create_product_attributes( $attributes, $default_attr );
						if ( count( $attr_data ) ) {
							$woo_product->set_attributes( $attr_data );
							if ( $default_attr ) {
								$woo_product->set_default_attributes( $default_attr );
							}
							$woo_product->save();
						}
						wp_set_object_terms( $woo_product_id, 'variable', 'product_type' );
						$children = array();
						if ( $woo_product->is_type( 'variable' ) ) {
							$children = $woo_product->get_children();
						} else {

						}
						$use_global_attributes = self::$settings->get_params( 'use_global_attributes' );
						if ( count( $children ) ) {
							$skuAttrArray = array_column( $variations, 'skuAttr' );
							foreach ( $children as $variation_id ) {
								if ( ! empty( $replace_items[ $variation_id ] ) ) {
									$variations_key = array_search( $replace_items[ $variation_id ], $skuAttrArray );
									if ( $variations_key !== false ) {
										$variation = new WC_Product_Variation( $variation_id );
										if ( $variation ) {
											$product_data['variations'][ $variations_key ]['variation_id'] = $variation_id;
//											if ( 1 != $override_options['override_images'] && ! $variation->get_image_id() ) {
//												$product_data['variations'][ $variations_key ]['image'] = '';
//											}
											$product_variation = $variations[ $variations_key ];
											$stock_quantity    = isset( $product_variation['stock'] ) ? absint( $product_variation['stock'] ) : 0;
											$v_attributes      = array();
											if ( $use_global_attributes ) {
												foreach ( $product_variation['attributes'] as $option_k => $attr ) {
													$attribute_id  = wc_attribute_taxonomy_id_by_name( $option_k );
													$attribute_obj = wc_get_attribute( $attribute_id );
													if ( $attribute_obj ) {
														$attribute_value = self::get_term_by_name( $attr, $attribute_obj->slug );
														if ( $attribute_value ) {
															$v_attributes[ strtolower( urlencode( $attribute_obj->slug ) ) ] = $attribute_value->slug;
														}
													}
												}
											} else {
												foreach ( $product_variation['attributes'] as $option_k => $attr ) {
													$v_attributes[ strtolower( urlencode( $option_k ) ) ] = $attr;
												}
											}
											$variation->set_attributes( $v_attributes );
											$fields = array(
												'sku'            => $product_variation['sku'] ? wc_product_generate_unique_sku( $variation_id, $product_variation['sku'] ) : '',
												'regular_price'  => $product_variation['regular_price'],
												'price'          => $product_variation['regular_price'],
												'sale_price'     => '',
												'manage_stock'   => $manage_stock,
												'stock_status'   => 'instock',
												'stock_quantity' => $stock_quantity,
											);

											if ( isset( $product_variation['sale_price'] ) && $product_variation['sale_price'] && $product_variation['sale_price'] < $product_variation['regular_price'] ) {
												$fields['sale_price'] = $product_variation['sale_price'];
												$fields['price']      = $product_variation['sale_price'];
											}
											foreach ( $fields as $field => $value ) {
												$variation->{"set_$field"}( wc_clean( $value ) );
											}
											$variation->save();
											self::handle_aliexpress_variation_meta( $variation_id, $product_variation );
										}
									} else {
										wp_delete_post( $variation_id, true );
									}
								} else {
									wp_delete_post( $variation_id, true );
								}
							}
						}
						if ( $override_link_only ) {
							/*If link only, unset all variations that do not exist in the target Woo product*/
							foreach ( $product_data['variations'] as $product_data_variation_k => $product_data_variation ) {
								if ( empty( $product_data_variation['variation_id'] ) ) {
									unset( $product_data['variations'][ $product_data_variation_k ] );
								}
							}
						}
						/*Create product variation*/
						$this->import_product_variation( $woo_product_id, $product_data, $dispatch, $disable_background_process );
					}
					wp_update_post( array(
						'ID'          => $product_draft_id,
						'post_status' => 'publish'
					) );
					update_post_meta( $product_draft_id, '_vi_wad_woo_id', $woo_product_id );
					if ( $override_product_id ) {
						wp_delete_post( $override_product_id );
					}
					wp_send_json( array(
						'status'      => 'success',
						'product_id'  => $woo_product_id,
						'message'     => '',
						'button_html' => self::get_button_view_edit_html( $woo_product_id ),
					) );
				} else {
					$product_data['replace_items'] = $replace_items;
					$product_data['replace_title'] = $override_options['override_title'];
					$product_data['found_items']   = $found_items;
					$product_id                    = $this->import_product( $product_data );
					$response                      = array(
						'status'     => 'error',
						'message'    => '',
						'product_id' => '',
					);
					if ( ! is_wp_error( $product_id ) ) {
						if ( $override_product_id ) {
							wp_delete_post( $override_product_id );
						}
						wp_delete_post( $woo_product_id );
						$response['status']      = 'success';
						$response['product_id']  = $product_id;
						$response['button_html'] = self::get_button_view_edit_html( $woo_product_id );
					} else {
						$response['message'] = $product_id->get_error_messages();
					}
					wp_send_json( $response );
				}
			}
		} else {
			wp_send_json( array(
				'status'  => 'error',
				'message' => esc_html__( 'Please select at least 1 variation to import this product.', 'woocommerce-alidropship' ),
			) );
		}
	}

	/**
	 * @param $id
	 * @param $variation_data
	 */
	public static function handle_aliexpress_variation_meta( $id, $variation_data ) {
		if ( ! empty( $variation_data['skuAttr'] ) ) {
			update_post_meta( $id, '_vi_wad_aliexpress_variation_attr', $variation_data['skuAttr'] );
		} elseif ( get_post_meta( $id, '_vi_wad_aliexpress_variation_attr', true ) ) {
			delete_post_meta( $id, '_vi_wad_aliexpress_variation_attr' );
		}
		if ( ! empty( $variation_data['skuId'] ) ) {
			update_post_meta( $id, '_vi_wad_aliexpress_variation_id', $variation_data['skuId'] );
		} elseif ( get_post_meta( $id, '_vi_wad_aliexpress_variation_id', true ) ) {
			delete_post_meta( $id, '_vi_wad_aliexpress_variation_id' );
		}
		if ( ! empty( $variation_data['ship_from'] ) ) {
			update_post_meta( $id, '_vi_wad_aliexpress_variation_ship_from', $variation_data['ship_from'] );
		} elseif ( get_post_meta( $id, '_vi_wad_aliexpress_variation_ship_from', true ) ) {
			delete_post_meta( $id, '_vi_wad_aliexpress_variation_ship_from' );
		}
	}

	/**
	 * @param $items
	 *
	 * @throws Exception
	 */
	public static function skip_item_with_ali_order_id( &$items ) {
		foreach ( $items as $key => $item ) {
			if ( wc_get_order_item_meta( $item['order_item_id'], '_vi_wad_aliexpress_order_id', true ) ) {
				unset( $items[ $key ] );
			}
		}
		$items = array_values( $items );
	}

	/**
	 * @param array $args1 $key=>$value are key and value of woocommerce_order_items table
	 * @param array $args2 $key=>$value are key and value of woocommerce_order_itemmeta table
	 *
	 * @return array|null|object
	 */
	public static function query_order_item_meta( $args1 = array(), $args2 = array() ) {
		global $wpdb;
		$sql  = "SELECT * FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items JOIN {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_items.order_item_id=woocommerce_order_itemmeta.order_item_id";
		$args = array();
		if ( count( $args1 ) ) {
			foreach ( $args1 as $key => $value ) {
				if ( is_array( $value ) ) {
					$sql .= " AND woocommerce_order_items.{$key} IN (" . implode( ', ', array_fill( 0, count( $value ), '%s' ) ) . ")";
					foreach ( $value as $v ) {
						$args[] = $v;
					}
				} else {
					$sql    .= " AND woocommerce_order_items.{$key}='%s'";
					$args[] = $value;
				}
			}
		}
		if ( count( $args2 ) ) {
			foreach ( $args2 as $key => $value ) {
				if ( is_array( $value ) ) {
					$sql .= " AND woocommerce_order_itemmeta.{$key} IN (" . implode( ', ', array_fill( 0, count( $value ), '%s' ) ) . ")";
					foreach ( $value as $v ) {
						$args[] = $v;
					}
				} else {
					$sql    .= " AND woocommerce_order_itemmeta.{$key}='%s'";
					$args[] = $value;
				}
			}
		}
		$query      = $wpdb->prepare( $sql, $args );
		$line_items = $wpdb->get_results( $query, ARRAY_A );

		return $line_items;
	}

	/**
	 * @param $attributes
	 * @param $default_attr
	 *
	 * @return array
	 */
	public static function create_product_attributes( $attributes, &$default_attr ) {
		global $wp_taxonomies;
		$position  = 0;
		$attr_data = array();
		if ( self::$settings->get_params( 'use_global_attributes' ) ) {
			foreach ( $attributes as $key => $attr ) {
				$attribute_name = isset( $attr['name'] ) ? $attr['name'] : VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attribute_name_by_slug( $attr['slug'] );
				$attribute_id   = wc_attribute_taxonomy_id_by_name( $attribute_name );

				if ( ! $attribute_id ) {
					$attribute_id = wc_create_attribute( array(
						'name'         => $attribute_name,
						'slug'         => $attr['slug'],
						'type'         => 'select',
						'order_by'     => 'menu_order',
						'has_archives' => false,
					) );
				}
				if ( $attribute_id && ! is_wp_error( $attribute_id ) ) {
					$attribute_obj     = wc_get_attribute( $attribute_id );
					$attribute_options = array();
					if ( ! empty( $attribute_obj ) ) {
						$taxonomy = $attribute_obj->slug; // phpcs:ignore
						if ( isset( $default_attr[ $attr['slug'] ] ) ) {
							$default_attr[ $taxonomy ] = $default_attr[ $attr['slug'] ];
							unset( $default_attr[ $attr['slug'] ] );
						}
						/*Update global $wp_taxonomies for latter insert attribute values*/
						$wp_taxonomies[ $taxonomy ] = new WP_Taxonomy( $taxonomy, 'product' );
						if ( count( $attr['values'] ) ) {
							foreach ( $attr['values'] as $attr_value ) {
								$attr_value  = strval( wc_clean( $attr_value ) );
								$insert_term = wp_insert_term( $attr_value, $taxonomy );
								if ( ! is_wp_error( $insert_term ) ) {
									$attribute_options[] = $insert_term['term_id'];
									if ( isset( $default_attr[ $taxonomy ] ) ) {
										$term_exists = get_term_by( 'id', $insert_term['term_id'], $taxonomy );
										if ( $term_exists ) {
											$default_attr[ $taxonomy ] = $term_exists->slug;
										}
									}
								} elseif ( isset( $insert_term->error_data ) && isset( $insert_term->error_data['term_exists'] ) ) {
									$attribute_options[] = $insert_term->error_data['term_exists'];
									if ( isset( $default_attr[ $taxonomy ] ) ) {
										$term_exists = get_term_by( 'id', $insert_term->error_data['term_exists'], $taxonomy );
										if ( $term_exists ) {
											$default_attr[ $taxonomy ] = $term_exists->slug;
										}
									}
								}
							}
						}
					}
					$attribute_object = new WC_Product_Attribute();
					$attribute_object->set_id( $attribute_id );
					$attribute_object->set_name( wc_attribute_taxonomy_name_by_id( $attribute_id ) );
					if ( count( $attribute_options ) ) {
						$attribute_object->set_options( $attribute_options );
					} else {
						$attribute_object->set_options( $attr['values'] );
					}
					$attribute_object->set_position( isset( $attr['position'] ) ? $attr['position'] : $position );
					$attribute_object->set_visible( self::$settings->get_params( 'variation_visible' ) ? 1 : '' );
					$attribute_object->set_variation( 1 );
					$attr_data[] = $attribute_object;
				}
				$position ++;
			}
		} else {
			foreach ( $attributes as $key => $attr ) {
				$attribute_name   = isset( $attr['name'] ) ? $attr['name'] : VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attribute_name_by_slug( $attr['slug'] );
				$attribute_object = new WC_Product_Attribute();
				$attribute_object->set_name( $attribute_name );
				$attribute_object->set_options( $attr['values'] );
				$attribute_object->set_position( isset( $attr['position'] ) ? $attr['position'] : $position );
				$attribute_object->set_visible( self::$settings->get_params( 'variation_visible' ) ? 1 : '' );
				$attribute_object->set_variation( 1 );
				$attr_data[] = $attribute_object;
				$position ++;
			}
		}

		return $attr_data;
	}

	/**
     * Import a product from Import list
     *
	 * @param $product_data
	 *
	 * @return int|WP_Error
	 * @throws Exception
	 */
	public function import_product( $product_data ) {
		$product_data = apply_filters( 'vi_wad_import_list_product_data', $product_data, $product_data );
		do_action( 'vi_wad_import_list_before_import', $product_data );
		wp_suspend_cache_invalidation( true );
		vi_wad_set_time_limit();
		$ali_product_id             = $product_data['ali_product_id'];
		$parent_id                  = $product_data['parent_id'];
		$image                      = $product_data['image'];
		$categories                 = isset( $product_data['categories'] ) ? $product_data['categories'] : array();
		$show_product_video_tab     = isset( $product_data['show_product_video_tab'] ) ? $product_data['show_product_video_tab'] : '';
		$shipping_class             = isset( $product_data['shipping_class'] ) ? $product_data['shipping_class'] : '';
		$title                      = $product_data['title'];
		$sku                        = $product_data['sku'] ? wc_product_generate_unique_sku( 0, $product_data['sku'] ) : '';
		$status                     = $product_data['status'];
		$tags                       = isset( $product_data['tags'] ) ? $product_data['tags'] : array();
		$description                = $product_data['description'];
		$variations                 = $product_data['variations'];
		$gallery                    = $product_data['gallery'];
		$attributes                 = $product_data['attributes'];
		$catalog_visibility         = $product_data['catalog_visibility'];
		$default_attr               = isset( $product_data['variation_default'] ) ? $product_data['variation_default'] : array();
		$disable_background_process = self::$settings->get_params( 'disable_background_process' );
		if ( is_array( $attributes ) && count( $attributes ) && ( count( $variations ) > 1 || ! self::$settings->get_params( 'simple_if_one_variation' ) ) ) {
			$attr_data = self::create_product_attributes( $attributes, $default_attr );

			/*Create data for product*/
			$data = array( // Set up the basic post data to insert for our product
				'post_excerpt' => '',
				'post_content' => $description,
				'post_title'   => $title,
				'post_status'  => $status,
				'post_type'    => 'product',
				'meta_input'   => array(
					'_sku'        => $sku,
					'_visibility' => 'visible',
				)
			);

			$product_id = wp_insert_post( $data, true ); // Insert the post returning the new post id

			if ( ! is_wp_error( $product_id ) ) {
				if ( $parent_id ) {
					wp_update_post( array(
						'ID'          => $parent_id,
						'post_status' => 'publish',
						'post_author' => get_current_user_id(),
					) );
					update_post_meta( $parent_id, '_vi_wad_woo_id', $product_id );
				}
				if ( self::$settings->get_params( 'import_product_video' ) ) {
					$video = self::get_video_url( $parent_id );
					if ( $video ) {
						update_post_meta( $product_id, '_vi_wad_product_video', $video );
						update_post_meta( $product_id, '_vi_wad_show_product_video_tab', $show_product_video_tab );
					}
				}
				update_post_meta( $product_id, '_vi_wad_aliexpress_product_id', $ali_product_id );
				// Set it to a variable product type
				wp_set_object_terms( $product_id, 'variable', 'product_type' );

				if ( count( $attr_data ) ) {
					$product_obj = wc_get_product( $product_id );
					if ( $product_obj ) {
						$product_obj->set_attributes( $attr_data );
						if ( $default_attr ) {
							$product_obj->set_default_attributes( $default_attr );
						}
						$product_obj->save();
						/*Set product type again here in case other plugins override product type after product is saved*/
						wp_set_object_terms( $product_id, 'variable', 'product_type' );
					}
				}
				/*download image gallery*/
				$dispatch = false;
				if ( isset( $product_data['old_product_image'] ) ) {
					if ( $product_data['old_product_image'] ) {
						update_post_meta( $product_id, '_thumbnail_id', $product_data['old_product_image'] );
					}
					if ( isset( $product_data['old_product_gallery'] ) && $product_data['old_product_gallery'] ) {
						update_post_meta( $product_id, '_product_image_gallery', $product_data['old_product_gallery'] );
					}
				} else {
					if ( $image ) {
						$thumb_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::download_image( $image_id, $image, $product_id );
						if ( ! is_wp_error( $thumb_id ) ) {
							update_post_meta( $product_id, '_thumbnail_id', $thumb_id );
						}
					}
					self::process_gallery_images( $gallery, $disable_background_process, $product_id, $parent_id, $dispatch );
				}
				self::process_description_images( $description, $disable_background_process, $product_id, $parent_id, $dispatch );

				/*Set product tag*/
				if ( is_array( $tags ) && count( $tags ) ) {
					self::set_product_tags( $product_id, $tags );
				}
				/*Set product categories*/
				if ( is_array( $categories ) && count( $categories ) ) {
					wp_set_post_terms( $product_id, $categories, 'product_cat', true );
				}
				/*Set product shipping class*/
				if ( $shipping_class && get_term_by( 'id', $shipping_class, 'product_shipping_class' ) ) {
					wp_set_post_terms( $product_id, array( intval( $shipping_class ) ), 'product_shipping_class', false );
				}
				/*Create product variation*/
				$this->import_product_variation( $product_id, $product_data, $dispatch, $disable_background_process );
				vi_wad_set_catalog_visibility( $product_id, $catalog_visibility );
			}
		} else {
			/*Create data for product*/
			$sale_price    = isset( $variations[0]['sale_price'] ) ? floatval( $variations[0]['sale_price'] ) : '';
			$regular_price = isset( $variations[0]['regular_price'] ) ? floatval( $variations[0]['regular_price'] ) : '';
			$data          = array( // Set up the basic post data to insert for our product
				'post_excerpt' => '',
				'post_content' => $description,
				'post_title'   => $title,
				'post_status'  => $status,
				'post_type'    => 'product',
				'meta_input'   => array(
					'_sku'           => $sku,
					'_visibility'    => 'visible',
					'_regular_price' => $regular_price,
					'_price'         => $regular_price,
					'_manage_stock'  => self::$settings->get_params( 'manage_stock' ) ? 'yes' : 'no',
					'_stock_status'  => 'instock',
				)
			);
			if ( ! empty( $variations[0]['stock'] ) && $data['meta_input']['_manage_stock'] === 'yes' ) {
				$data['meta_input']['_stock'] = absint( $variations[0]['stock'] );
			}
			if ( $sale_price ) {
				$data['meta_input']['_sale_price'] = $sale_price;
				$data['meta_input']['_price']      = $sale_price;
			}
			$product_id = wp_insert_post( $data, true ); // Insert the post returning the new post id

			if ( ! is_wp_error( $product_id ) ) {
				update_post_meta( $product_id, '_vi_wad_aliexpress_product_id', $ali_product_id );
				if ( $parent_id ) {
					wp_update_post( array(
						'ID'          => $parent_id,
						'post_status' => 'publish',
						'post_author' => get_current_user_id(),
					) );
					update_post_meta( $parent_id, '_vi_wad_woo_id', $product_id );
				}
				if ( self::$settings->get_params( 'import_product_video' ) ) {
					$video = self::get_video_url( $parent_id );
					if ( $video ) {
						update_post_meta( $product_id, '_vi_wad_product_video', $video );
					}
				}
				// Set it to a variable product type
				wp_set_object_terms( $product_id, 'simple', 'product_type' );
				/*download image gallery*/
				$dispatch = false;
				if ( isset( $product_data['old_product_image'] ) ) {
					if ( $product_data['old_product_image'] ) {
						update_post_meta( $product_id, '_thumbnail_id', $product_data['old_product_image'] );
					}
					if ( isset( $product_data['old_product_gallery'] ) && $product_data['old_product_gallery'] ) {
						update_post_meta( $product_id, '_product_image_gallery', $product_data['old_product_gallery'] );
					}
				} else {
					if ( $image ) {
						$thumb_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::download_image( $image_id, $image, $product_id );
						if ( ! is_wp_error( $thumb_id ) ) {
							update_post_meta( $product_id, '_thumbnail_id', $thumb_id );
						}
					}
					self::process_gallery_images( $gallery, $disable_background_process, $product_id, $parent_id, $dispatch );
				}
				self::process_description_images( $description, $disable_background_process, $product_id, $parent_id, $dispatch );
				if ( $dispatch ) {
					self::$process_image->save()->dispatch();
				}
				/*Set product tag*/
				if ( is_array( $tags ) && count( $tags ) ) {
					self::set_product_tags( $product_id, $tags );
				}
				/*Set product categories*/
				if ( is_array( $categories ) && count( $categories ) ) {
					wp_set_post_terms( $product_id, $categories, 'product_cat', true );
				}
				/*Set product shipping class*/
				if ( $shipping_class && get_term_by( 'id', $shipping_class, 'product_shipping_class' ) ) {
					wp_set_post_terms( $product_id, array( intval( $shipping_class ) ), 'product_shipping_class', false );
				}
				self::handle_aliexpress_variation_meta( $product_id, $variations[0] );
				$found_items   = isset( $product_data['found_items'] ) ? $product_data['found_items'] : array();
				$replace_items = isset( $product_data['replace_items'] ) ? $product_data['replace_items'] : array();
				$replace_title = isset( $product_data['replace_title'] ) ? $product_data['replace_title'] : '';
				$replaces      = array_keys( $replace_items, $variations[0]['skuId'] );

				if ( count( $replaces ) ) {
					foreach ( $replaces as $old_variation_id ) {
						$order_item_data = isset( $found_items[ $old_variation_id ] ) ? $found_items[ $old_variation_id ] : array();
						if ( count( $order_item_data ) ) {
							foreach ( $order_item_data as $order_item_data_k => $order_item_data_v ) {
								$order_id      = $order_item_data_v['order_id'];
								$order_item_id = $order_item_data_v['order_item_id'];
								if ( 1 == $replace_title ) {
									wc_update_order_item( $order_item_id, array( 'order_item_name' => $title ) );
								}
								if ( $order_item_data_v['meta_key'] === '_variation_id' ) {
									$old_variation = wc_get_product( $old_variation_id );
									if ( $old_variation ) {
										$_product_id = wc_get_order_item_meta( $order_item_id, '_product_id', true );
										$note        = sprintf( esc_html__( 'Product #%s is replaced with product #%s.', 'woocommerce-alidropship' ), $_product_id, $product_id );
										$this->add_order_note( $order_id, $note );
										$old_attributes = $old_variation->get_attributes();
										if ( count( $old_attributes ) ) {
											foreach ( $old_attributes as $old_attribute_k => $old_attribute_v ) {
												wc_delete_order_item_meta( $order_item_id, $old_attribute_k );
											}
										}
									}
									wc_delete_order_item_meta( $order_item_id, '_variation_id' );
								} else {
									$note = sprintf( esc_html__( 'Product #%s is replaced with product #%s.', 'woocommerce-alidropship' ), $old_variation_id, $product_id );
									$this->add_order_note( $order_id, $note );
								}
								wc_update_order_item_meta( $order_item_id, '_product_id', $product_id );
							}
						}
					}
				}
				vi_wad_set_catalog_visibility( $product_id, $catalog_visibility );
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$product->save();
				}
			}
		}

		return $product_id;
	}

	/**
     * Set tags when importing products
     *
	 * @param $product_id
	 * @param $tags
	 */
	private static function set_product_tags( $product_id, $tags ) {
		if ( is_array( $tags ) && count( $tags ) ) {
			$new_tags = array();
			foreach ( $tags as $tag ) {
				if ( $tag ) {
					$new_tags = array_merge( array_filter( explode( ',', $tag ) ), $new_tags );
				}
			}
			wp_set_post_terms( $product_id, array_values( array_unique( array_map( 'trim', $new_tags ) ) ), 'product_tag', true );
		}
	}

	/**
     * Push gallery image to queue to process in the background or add to Failed images if background process is disabled
     *
	 * @param $gallery
	 * @param $disable_background_process
	 * @param $product_id
	 * @param $parent_id
	 * @param $dispatch
	 */
	public static function process_gallery_images( $gallery, $disable_background_process, $product_id, $parent_id, &$dispatch ) {
		if ( is_array( $gallery ) && count( $gallery ) ) {
			if ( $disable_background_process ) {
				foreach ( $gallery as $image_url ) {
					$image_data = array(
						'woo_product_id' => $product_id,
						'parent_id'      => $parent_id,
						'src'            => $image_url,
						'product_ids'    => array(),
						'set_gallery'    => 1,
					);
					VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::insert( $product_id, implode( ',', $image_data['product_ids'] ), $image_data['src'], intval( $image_data['set_gallery'] ) );
				}
			} else {
				$dispatch = true;
				foreach ( $gallery as $image_url ) {
					$image_data = array(
						'woo_product_id' => $product_id,
						'parent_id'      => $parent_id,
						'src'            => $image_url,
						'product_ids'    => array(),
						'set_gallery'    => 1,
					);
					self::$process_image->push_to_queue( $image_data );
				}
			}
		}
	}

	/**
	 * By default, images in product description are used as external links
	 *
	 * @param $description
	 * @param $disable_background_process
	 * @param $product_id
	 * @param $parent_id
	 * @param $dispatch
	 */
	public static function process_description_images( $description, $disable_background_process, $product_id, $parent_id, &$dispatch ) {
		if ( $description && ! self::$settings->get_params( 'use_external_image' ) && self::$settings->get_params( 'download_description_images' ) ) {
			preg_match_all( '/src="([\s\S]*?)"/im', $description, $matches );
			if ( isset( $matches[1] ) && is_array( $matches[1] ) && count( $matches[1] ) ) {
				$description_images = array_unique( $matches[1] );
				if ( $disable_background_process ) {
					foreach ( $description_images as $description_image ) {
						VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::insert( $product_id, '', $description_image, 2 );
					}
				} else {
					foreach ( $description_images as $description_image ) {
						$images_data = array(
							'woo_product_id' => $product_id,
							'parent_id'      => $parent_id,
							'src'            => $description_image,
							'product_ids'    => array(),
							'set_gallery'    => 2,
						);
						self::$process_image->push_to_queue( $images_data );
					}
					$dispatch = true;
				}
			}
		}
	}

	/**
     * Import variations
     *
	 * @param $product_id
	 * @param $product_data
	 * @param $dispatch
	 * @param $disable_background_process
	 *
	 * @throws Exception
	 */
	public function import_product_variation( $product_id, $product_data, $dispatch, $disable_background_process ) {
		$product = wc_get_product( $product_id );
		if ( $product ) {
			if ( is_array( $product_data['variations'] ) && count( $product_data['variations'] ) ) {
				$found_items   = isset( $product_data['found_items'] ) ? $product_data['found_items'] : array();
				$replace_items = isset( $product_data['replace_items'] ) ? $product_data['replace_items'] : array();
				$replace_title = isset( $product_data['replace_title'] ) ? $product_data['replace_title'] : '';
				$variation_ids = [];
				if ( count( $product_data['variation_images'] ) ) {
					foreach ( $product_data['variation_images'] as $key => $val ) {
						$variation_ids[ $key ] = array();
					}
				}
				$use_global_attributes = self::$settings->get_params( 'use_global_attributes' );
				$manage_stock          = self::$settings->get_params( 'manage_stock' ) ? 'yes' : 'no';

				foreach ( $product_data['variations'] as $product_variation ) {
					if ( ! empty( $product_variation['variation_id'] ) ) {
						$variation_id = $product_variation['variation_id'];
					} else {
						$stock_quantity = isset( $product_variation['stock'] ) ? absint( $product_variation['stock'] ) : 0;
						$variation      = new WC_Product_Variation();
						$variation->set_parent_id( $product_id );
						$attributes = array();

						if ( $use_global_attributes ) {
							foreach ( $product_variation['attributes'] as $option_k => $attr ) {
								$attribute_id  = wc_attribute_taxonomy_id_by_name( $option_k );
								$attribute_obj = wc_get_attribute( $attribute_id );
								if ( $attribute_obj ) {
									$attribute_value = self::get_term_by_name( $attr, $attribute_obj->slug );
									if ( $attribute_value ) {
										$attributes[ strtolower( urlencode( $attribute_obj->slug ) ) ] = $attribute_value->slug;
									}
								}
							}
						} else {
							foreach ( $product_variation['attributes'] as $option_k => $attr ) {
								$attributes[ strtolower( urlencode( $option_k ) ) ] = $attr;
							}
						}

						$variation->set_attributes( $attributes );
						/*Set metabox for variation . Check field name at woocommerce/includes/class-wc-ajax.php*/
						$fields = array(
							'sku'            => $product_variation['sku'] ? wc_product_generate_unique_sku( 0, $product_variation['sku'] ) : '',
							'regular_price'  => $product_variation['regular_price'],
							'price'          => $product_variation['regular_price'],
							'manage_stock'   => $manage_stock,
							'stock_status'   => 'instock',
							'stock_quantity' => $stock_quantity,
						);
						if ( isset( $product_variation['sale_price'] ) && $product_variation['sale_price'] && $product_variation['sale_price'] < $product_variation['regular_price'] ) {
							$fields['sale_price'] = $product_variation['sale_price'];
							$fields['price']      = $product_variation['sale_price'];
						}
						foreach ( $fields as $field => $value ) {
							$variation->{"set_$field"}( wc_clean( $value ) );
						}
						do_action( 'product_variation_linked', $variation->save() );
						$variation_id = $variation->get_id();
						$replaces     = array_keys( $replace_items, $product_variation['skuAttr'] );
						if ( count( $replaces ) ) {
							foreach ( $replaces as $old_variation_id ) {
								$order_item_data = isset( $found_items[ $old_variation_id ] ) ? $found_items[ $old_variation_id ] : array();
								if ( count( $order_item_data ) ) {
									foreach ( $order_item_data as $order_item_data_k => $order_item_data_v ) {
										$order_id      = $order_item_data_v['order_id'];
										$order_item_id = $order_item_data_v['order_item_id'];
										if ( 1 == $replace_title ) {
											wc_update_order_item( $order_item_id, array( 'order_item_name' => $replace_title ) );
										}
										if ( $order_item_data_v['meta_key'] === '_variation_id' ) {
											$old_variation = wc_get_product( $old_variation_id );
											if ( $old_variation ) {
												$_product_id = wc_get_order_item_meta( $order_item_id, '_product_id', true );
												$note        = sprintf( esc_html__( 'Product #%s is replaced with product #%s. Variation #%s is replaced with variation #%s.', 'woocommerce-alidropship' ), $_product_id, $product_id, $old_variation_id, $variation_id );
												$this->add_order_note( $order_id, $note );
												$old_attributes = $old_variation->get_attributes();
												if ( count( $old_attributes ) ) {
													foreach ( $old_attributes as $old_attribute_k => $old_attribute_v ) {
														wc_delete_order_item_meta( $order_item_id, $old_attribute_k );
													}
												}
											}

										} else {
											$note = sprintf( esc_html__( 'Product #%s is replaced with product #%s.', 'woocommerce-alidropship' ), $old_variation_id, $product_id );
											$this->add_order_note( $order_id, $note );
											foreach ( $product_variation['attributes'] as $new_attribute_k => $new_attribute_v ) {
												wc_update_order_item_meta( $order_item_id, $new_attribute_k, $new_attribute_v );
											}
										}
										foreach ( $product_variation['attributes'] as $new_attribute_k => $new_attribute_v ) {
											wc_update_order_item_meta( $order_item_id, $new_attribute_k, $new_attribute_v );
										}
										wc_update_order_item_meta( $order_item_id, '_product_id', $product_id );
										wc_update_order_item_meta( $order_item_id, '_variation_id', $variation_id );
									}
								}
							}
						}
						self::handle_aliexpress_variation_meta( $variation_id, $product_variation );
					}
					if ( $product_variation['image'] ) {
						$pos = array_search( $product_variation['image'], $product_data['variation_images'] );
						if ( $pos !== false ) {
							$variation_ids[ $pos ][] = $variation_id;
						}
					}
				}
				if ( count( $variation_ids ) ) {
					if ( $disable_background_process ) {
						foreach ( $variation_ids as $key => $values ) {
							if ( count( $values ) && ! empty( $product_data['variation_images'][ $key ] ) ) {
								$image_data = array(
									'woo_product_id' => $product_id,
									'parent_id'      => '',
									'src'            => $product_data['variation_images'][ $key ],
									'product_ids'    => $values,
									'set_gallery'    => 0,
								);
								VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::insert( $product_id, implode( ',', $image_data['product_ids'] ), $image_data['src'], intval( $image_data['set_gallery'] ) );
							}
						}
					} else {
						foreach ( $variation_ids as $key => $values ) {
							if ( count( $values ) && ! empty( $product_data['variation_images'][ $key ] ) ) {
								$dispatch   = true;
								$image_data = array(
									'woo_product_id' => $product_id,
									'parent_id'      => '',
									'src'            => $product_data['variation_images'][ $key ],
									'product_ids'    => $values,
									'set_gallery'    => 0,
								);
								self::$process_image->push_to_queue( $image_data );
							}
						}
					}
				}
			}

			$data_store = $product->get_data_store();
			$data_store->sort_all_product_variations( $product->get_id() );
		}
		if ( $dispatch ) {
			self::$process_image->save()->dispatch();
		}
	}

	/**
     * Search if a term exists
     *
	 * @param $value
	 * @param string $taxonomy
	 * @param string $output
	 * @param string $filter
	 *
	 * @return array|bool|WP_Error|WP_Term|null
	 */
	public static function get_term_by_name( $value, $taxonomy = '', $output = OBJECT, $filter = 'raw' ) {
		// 'term_taxonomy_id' lookups don't require taxonomy checks.
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		// No need to perform a query for empty 'slug' or 'name'.
		$value = (string) $value;

		if ( 0 === strlen( $value ) ) {
			return false;
		}

		$args = array(
			'get'                    => 'all',
			'name'                   => $value,
			'number'                 => 0,
			'taxonomy'               => $taxonomy,
			'update_term_meta_cache' => false,
			'orderby'                => 'none',
			'suppress_filter'        => true,
		);

		$terms = get_terms( $args );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return false;
		}
		if ( count( $terms ) > 1 ) {
			foreach ( $terms as $term ) {
				if ( $term->name === $value ) {
					return get_term( $term, $taxonomy, $output, $filter );
				}
			}
		}
		$term = array_shift( $terms );

		return get_term( $term, $taxonomy, $output, $filter );
	}

	/**
	 * @param $order_id
	 * @param $note
	 */
	public function add_order_note( $order_id, $note ) {
		$commentdata = apply_filters(
			'woocommerce_new_order_note_data',
			array(
				'comment_post_ID'      => $order_id,
				'comment_author'       => '',
				'comment_author_email' => __( 'WooCommerce', 'woocommerce' ),
				'comment_author_url'   => '',
				'comment_content'      => $note,
				'comment_agent'        => 'WooCommerce',
				'comment_type'         => 'order_note',
				'comment_parent'       => 0,
				'comment_approved'     => 1,
			),
			array(
				'order_id'         => $order_id,
				'is_customer_note' => 0,
			)
		);
		wp_insert_comment( $commentdata );
	}

	/**
	 * @param $woo_product_id
	 *
	 * @return false|string
	 */

	public static function get_button_view_edit_html( $woo_product_id ) {
		ob_start();
		?>
        <a href="<?php echo esc_url( get_post_permalink( $woo_product_id ) ) ?>"
           target="_blank" class="vi-ui mini button labeled icon"
           rel="nofollow"><i class="icon eye"></i><?php esc_html_e( 'View', 'woocommerce-alidropship' ); ?></a>
        <a href="<?php echo esc_url( admin_url( "post.php?post={$woo_product_id}&action=edit" ) ) ?>"
           target="_blank" class="vi-ui mini button labeled icon primary"
           rel="nofollow"><i class="icon edit"></i><?php esc_html_e( 'Edit', 'woocommerce-alidropship' ) ?></a>
		<?php
		return apply_filters( 'vi_wad_import_list_button_view_edit_html', ob_get_clean(), $woo_product_id );
	}

	/**
	 * Handle shipping selection
	 */
	public function select_shipping() {
		self::check_ajax_referer();
		$key          = isset( $_POST['product_index'] ) ? absint( sanitize_text_field( $_POST['product_index'] ) ) : '';
		$product_id   = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$product_type = isset( $_POST['product_type'] ) ? sanitize_text_field( $_POST['product_type'] ) : '';
		$country      = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '';
		$company      = isset( $_POST['company'] ) ? sanitize_text_field( $_POST['company'] ) : '';
		if ( $key > - 1 && $product_id && $product_type ) {
			$currency                    = 'USD';
			$woocommerce_currency        = get_option( 'woocommerce_currency' );
			$woocommerce_currency_symbol = get_woocommerce_currency_symbol( $woocommerce_currency );
			$manage_stock                = self::$settings->get_params( 'manage_stock' );
			$use_different_currency      = false;
//			$variations                  = get_post_meta( $product_id, '_vi_wad_variations', true );
			$variations = self::get_product_variations( $product_id );
			$decimals   = wc_get_price_decimals();
			if ( $decimals < 1 ) {
				$decimals = 1;
			} else {
				$decimals = pow( 10, ( - 1 * $decimals ) );
			}
			if ( strtolower( $woocommerce_currency ) != strtolower( $currency ) ) {
				$use_different_currency = true;
			}
			ob_start();
			if ( $product_type === 'variable' ) {
//				$attributes = get_post_meta( $product_id, '_vi_wad_attributes', true );
				$attributes = self::get_product_attributes( $product_id );
				$parent     = array();
				if ( is_array( $attributes ) && count( $attributes ) ) {
					foreach ( $attributes as $attribute_k => $attribute_v ) {
						$parent[ $attribute_k ] = $attribute_v['slug'];
					}
				}
				self::variation_html( $key, $parent, $attributes, $manage_stock, $variations, $use_different_currency, $currency, $product_id, $woocommerce_currency_symbol, $decimals, false, $country, $company );
			} else {
				self::simple_product_price_field_html( $key, $manage_stock, $variations, $use_different_currency, $currency, $product_id, $woocommerce_currency_symbol, $decimals, $country, $company );
			}
			$return = ob_get_clean();
			wp_send_json(
				array(
					'status' => 'success',
					'data'   => $return
				)
			);
		} else {
			wp_send_json(
				array(
					'status' => 'error',
					'data'   => esc_html__( 'Missing required arguments', 'woocommerce-alidropship' )
				)
			);
		}
	}

	/**
     * Ajax handler for importing product
     *
	 * @throws Exception
	 */
	public function import() {
		self::check_ajax_referer();
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-import-list' ) ) ) {
			wp_die();
		}
		parse_str( $_POST['form_data'], $form_data );
		$data     = isset( $form_data['vi_wad_product'] ) ? stripslashes_deep( $form_data['vi_wad_product'] ) : array();
		$selected = isset( $_POST['selected'] ) ? vi_wad_json_decode( stripslashes_deep( $_POST['selected'] ) ) : array();
		$response = array(
			'status'         => 'error',
			'message'        => '',
			'woo_product_id' => '',
			'button_html'    => '',
		);
		if ( ! isset( $form_data['z_check_max_input_vars'] ) ) {
			/*z_check_max_input_vars is the last key of POST data. If it does not exist in $form_data after using parse_str(), some data may also be missing*/
			$response['message'] = esc_html__( 'PHP max_input_vars is too low, please increase it in php.ini', 'woocommerce-alidropship' );
		} elseif ( count( $data ) === 0 ) {
			$response['message'] = esc_html__( 'Please select product to import', 'woocommerce-alidropship' );
		} else {
			$product_data     = array_values( $data )[0];
			$product_draft_id = array_keys( $data )[0];
			if ( ! count( $selected[ $product_draft_id ] ) ) {
				$response['message'] = esc_html__( 'Please select at least 1 variation to import this product.', 'woocommerce-alidropship' );
				wp_send_json( $response );
			}
			if ( ! $product_draft_id || ( ! self::$settings->get_params( 'auto_generate_unique_sku' ) && VI_WOOCOMMERCE_ALIDROPSHIP_DATA::sku_exists( $product_data['sku'] ) ) ) {
				$response['message'] = esc_html__( 'Sku exists.', 'woocommerce-alidropship' );
				wp_send_json( $response );
			}
			$variations_attributes = array();
			$attributes            = self::get_product_attributes( $product_draft_id );
			if ( isset( $product_data['variations'] ) ) {
				$variations = array_values( $product_data['variations'] );
				if ( count( $variations ) > 1 ) {
					$var_default = isset( $product_data['default_variation'] ) ? $product_data['default_variation'] : '';
					foreach ( $variations as $variations_v ) {
						if ( $var_default === $variations_v['skuAttr'] ) {
							$product_data['variation_default'] = $variations_v['attributes'];
						}
						$variations_attribute = isset( $variations_v['attributes'] ) ? $variations_v['attributes'] : array();
						if ( is_array( $variations_attribute ) && count( $variations_attribute ) ) {
							foreach ( $variations_attribute as $variations_attribute_k => $variations_attribute_v ) {
								if ( ! isset( $variations_attributes[ $variations_attribute_k ] ) ) {
									$variations_attributes[ $variations_attribute_k ] = array( $variations_attribute_v );
								} elseif ( ! in_array( $variations_attribute_v, $variations_attributes[ $variations_attribute_k ] ) ) {
									$variations_attributes[ $variations_attribute_k ][] = $variations_attribute_v;
								}
							}
						}
					}

					if ( is_array( $attributes ) && count( $attributes ) ) {
						foreach ( $attributes as $attributes_k => $attributes_v ) {
							if ( ! empty( $variations_attributes[ $attributes_v['slug'] ] ) ) {
								$attributes[ $attributes_k ]['values'] = array_intersect( $attributes[ $attributes_k ]['values'], $variations_attributes[ $attributes_v['slug'] ] );
							}
						}
					}
				}
			} else {
				$variations    = self::get_product_variations( $product_draft_id, true );
				$shipping_cost = 0;
				if ( self::$settings->get_params( 'show_shipping_option' ) ) {
					$shipping_info = self::get_shipping_info( $product_draft_id, '', '' );
					$shipping_cost = abs( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $shipping_info['shipping_cost'] ) );
				}
				if ( self::$settings->get_params( 'shipping_cost_after_price_rules' ) ) {
					foreach ( $variations as $variations_k => $variations_v ) {
						$variation_sale_price    = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations_v['sale_price'] );
						$variation_regular_price = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations_v['regular_price'] );
						$price                   = $variation_sale_price ? $variation_sale_price : $variation_regular_price;
						$sale_price              = self::$settings->process_price( $price, true );
						if ( $sale_price ) {
							$sale_price += $shipping_cost;
						}
						$regular_price                                = self::$settings->process_price( $price ) + $shipping_cost;
						$variations[ $variations_k ]['sale_price']    = self::$settings->process_exchange_price( $sale_price );
						$variations[ $variations_k ]['regular_price'] = self::$settings->process_exchange_price( $regular_price );
					}
				} else {
					foreach ( $variations as $variations_k => $variations_v ) {
						$variation_sale_price                         = $variations_v['sale_price'] ? ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations_v['sale_price'] ) + $shipping_cost ) : VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations_v['sale_price'] );
						$variation_regular_price                      = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations_v['regular_price'] ) + $shipping_cost;
						$price                                        = $variation_sale_price ? $variation_sale_price : $variation_regular_price;
						$variations[ $variations_k ]['sale_price']    = self::$settings->process_exchange_price( self::$settings->process_price( $price, true ) );
						$variations[ $variations_k ]['regular_price'] = self::$settings->process_exchange_price( self::$settings->process_price( $price ) );
					}
				}
			}
			if ( count( $variations ) ) {
				if ( isset( $product_data['gallery'] ) ) {
					$product_data['gallery'] = array_values( array_filter( $product_data['gallery'] ) );
					if ( $product_data['image'] ) {
						$product_image_key = array_search( $product_data['image'], $product_data['gallery'] );
						if ( $product_image_key !== false ) {
							unset( $product_data['gallery'][ $product_image_key ] );
							$product_data['gallery'] = array_values( $product_data['gallery'] );
						}
					}
				} else {
					$product_data['gallery'] = array();
				}
				$variation_images                 = get_post_meta( $product_draft_id, '_vi_wad_variation_images', true );
				$product_data['variation_images'] = $variation_images;
				$product_data['attributes']       = $attributes;
				$product_data['variations']       = $variations;
				$product_data['parent_id']        = $product_draft_id;
				$product_data['ali_product_id']   = get_post_meta( $product_draft_id, '_vi_wad_sku', true );
				$woo_product_id                   = $this->import_product( $product_data );

				if ( ! is_wp_error( $woo_product_id ) ) {
					$response['status']         = 'success';
					$response['message']        = esc_html__( 'Import successfully', 'woocommerce-alidropship' );
					$response['woo_product_id'] = $woo_product_id;

					$response['button_html'] = self::get_button_view_edit_html( $woo_product_id );
				} else {
					$response['message'] = $woo_product_id->get_error_messages();
				}
			} else {
				$response['message'] = esc_html__( 'Please select at least 1 variation to import this product.', 'woocommerce-alidropship' );
			}
		}
		wp_send_json( $response );
	}

	/**
	 * Delete a product from Import list
	 */
	public function remove() {
		self::check_ajax_referer();
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-import-list' ) ) ) {
			wp_die();
		}
		vi_wad_set_time_limit();
		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		if ( $product_id ) {
			$map_woo_id = get_post_meta( $product_id, '_vi_wad_map_woo_id', true );
			if ( wp_delete_post( $product_id, true ) ) {
				if ( $map_woo_id ) {
					delete_post_meta( $map_woo_id, '_vi_wad_migrate_from_id' );
				}
				wp_send_json( array(
					'status'  => 'success',
					'message' => esc_html__( 'Removed', 'woocommerce-alidropship' ),
				) );
			} else {
				wp_send_json( array(
					'status'  => 'error',
					'message' => esc_html__( 'Error', 'woocommerce-alidropship' ),
				) );
			}
		} else {
			wp_send_json( array(
				'status'  => 'error',
				'message' => esc_html__( 'Not found', 'woocommerce-alidropship' ),
			) );
		}
	}

	/**
	 *
	 */
	public function admin_enqueue_scripts() {
		$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		global $pagenow;
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-alidropship-import-list' ) {
			self::enqueue_scripts();
		}
	}

	public static function enqueue_scripts() {
		VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::enqueue_3rd_library();
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'woocommerce-alidropship-import-list', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'import-list.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
		wp_enqueue_script( 'woocommerce-alidropship-import-list', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'import-list.js', array(
			'jquery',
			'jquery-ui-sortable'
		), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
		wp_localize_script( 'woocommerce-alidropship-import-list', 'vi_wad_import_list_params', array(
			'url'                                    => admin_url( 'admin-ajax.php' ),
			'_vi_wad_ajax_nonce'                     => VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::create_ajax_nonce(),
			'decimals'                               => wc_get_price_decimals(),
			'i18n_empty_variation_error'             => esc_html__( 'Please select at least 1 variation to import.', 'woocommerce-alidropship' ),
			'i18n_empty_price_error'                 => esc_html__( 'Regular price can not be empty.', 'woocommerce-alidropship' ),
			'i18n_sale_price_error'                  => esc_html__( 'Sale price must be smaller than regular price.', 'woocommerce-alidropship' ),
			'i18n_not_found_error'                   => esc_html__( 'No product found.', 'woocommerce-alidropship' ),
			'i18n_import_all_confirm'                => esc_html__( 'Import all products on this page to your WooCommerce store?', 'woocommerce-alidropship' ),
			'i18n_remove_product_confirm'            => esc_html__( 'Remove this product from import list?', 'woocommerce-alidropship' ),
			'i18n_bulk_remove_product_confirm'       => esc_html__( 'Remove selected product(s) from import list?', 'woocommerce-alidropship' ),
			'i18n_bulk_import_product_confirm'       => esc_html__( 'Import all selected product(s)?', 'woocommerce-alidropship' ),
			'i18n_split_product_confirm'             => esc_html__( 'Split to 2 products by selected variation(s)?', 'woocommerce-alidropship' ),
			'i18n_split_product_no_variations'       => esc_html__( 'Please select variations to split', 'woocommerce-alidropship' ),
			'i18n_split_product_too_many_variations' => esc_html__( 'Please select less variations to split', 'woocommerce-alidropship' ),
			'i18n_split_product_message'             => esc_html__( 'If product is split successfully, page will be reloaded automatically to load new products.', 'woocommerce-alidropship' ),
			'i18n_empty_attribute_name'              => esc_html__( 'Attribute name can not be empty', 'woocommerce-alidropship' ),
			'i18n_invalid_attribute_values'          => esc_html__( 'Attribute value can not be empty or duplicated', 'woocommerce-alidropship' ),
			'product_categories'                     => self::$settings->get_params( 'product_categories' ),
			'migration_link_only'                    => self::$settings->get_params( 'migration_link_only' ),
			'tags_allow_addition'                    => apply_filters( 'vi_wad_import_list_tags_allow_addition', true ),
		) );
		add_action( 'admin_footer', array( __CLASS__, 'set_price_modal' ) );
		add_action( 'admin_footer', array( __CLASS__, 'override_product_options' ) );
	}

	/**
	 * Bulk actions popup: set price, remove an attribute, set categories...
	 */
	public static function set_price_modal() {
		?>
        <div class="<?php echo esc_attr( self::set( array( 'modal-popup-container', 'hidden' ) ) ) ?>">
            <div class="<?php echo esc_attr( self::set( 'overlay' ) ) ?>"></div>
            <div class="<?php echo esc_attr( self::set( array(
				'modal-popup-content',
				'modal-popup-content-set-price'
			) ) ) ?>">
                <div class="<?php echo esc_attr( self::set( 'modal-popup-header' ) ) ?>">
                    <h2><?php esc_html_e( 'Set price', 'woocommerce-alidropship' ) ?></h2>
                    <span class="<?php echo esc_attr( self::set( 'modal-popup-close' ) ) ?>"></span>
                </div>
                <div class="<?php echo esc_attr( self::set( 'modal-popup-content-body' ) ) ?>">
                    <div class="<?php echo esc_attr( self::set( 'modal-popup-content-body-row' ) ) ?>">
                        <div class="<?php echo esc_attr( self::set( 'set-price-action-wrap' ) ) ?>">
                            <label for="<?php echo esc_attr( self::set( 'set-price-action' ) ) ?>"><?php esc_html_e( 'Action', 'woocommerce-alidropship' ) ?></label>
                            <select id="<?php echo esc_attr( self::set( 'set-price-action' ) ) ?>"
                                    class="<?php echo esc_attr( self::set( 'set-price-action' ) ) ?>">
                                <option value="set_new_value"><?php esc_html_e( 'Set to this value', 'woocommerce-alidropship' ) ?></option>
                                <option value="increase_by_fixed_value"><?php esc_html_e( 'Increase by fixed value(' . get_woocommerce_currency_symbol( get_option( 'woocommerce_currency' ) ) . ')', 'woocommerce-alidropship' ) ?></option>
                                <option value="increase_by_percentage"><?php esc_html_e( 'Increase by percentage(%)', 'woocommerce-alidropship' ) ?></option>
                            </select>
                        </div>
                        <div class="<?php echo esc_attr( self::set( 'set-price-amount-wrap' ) ) ?>">
                            <label for="<?php echo esc_attr( self::set( 'set-price-amount' ) ) ?>"><?php esc_html_e( 'Amount', 'woocommerce-alidropship' ) ?></label>
                            <input type="text"
                                   id="<?php echo esc_attr( self::set( 'set-price-amount' ) ) ?>"
                                   class="<?php echo esc_attr( self::set( 'set-price-amount' ) ) ?>">
                        </div>
                    </div>
                </div>
                <div class="<?php echo esc_attr( self::set( 'modal-popup-content-footer' ) ) ?>">
                        <span class="button button-primary <?php echo esc_attr( self::set( 'set-price-button-set' ) ) ?>">
                            <?php esc_html_e( 'Set', 'woocommerce-alidropship' ) ?>
                        </span>
                    <span class="button <?php echo esc_attr( self::set( 'set-price-button-cancel' ) ) ?>">
                            <?php esc_html_e( 'Cancel', 'woocommerce-alidropship' ) ?>
                        </span>
                </div>
            </div>
            <div class="<?php echo esc_attr( self::set( array(
				'modal-popup-content',
				'modal-popup-content-remove-attribute'
			) ) ) ?>">
                <div class="<?php echo esc_attr( self::set( 'modal-popup-header' ) ) ?>">
                    <h2><?php esc_html_e( 'Please select default value to fulfill orders after this attribute is removed', 'woocommerce-alidropship' ) ?></h2>
                    <span class="<?php echo esc_attr( self::set( 'modal-popup-close' ) ) ?>"></span>
                </div>
                <div class="<?php echo esc_attr( self::set( 'modal-popup-content-body' ) ) ?>">
                    <div class="<?php echo esc_attr( self::set( array(
						'modal-popup-content-body-row',
						'modal-popup-select-attribute'
					) ) ) ?>">

                    </div>
                </div>
            </div>
            <div class="<?php echo esc_attr( self::set( array(
				'modal-popup-content',
				'modal-popup-content-set-categories'
			) ) ) ?>">
                <div class="<?php echo esc_attr( self::set( 'modal-popup-header' ) ) ?>">
                    <h2><?php esc_html_e( 'Bulk set product categories', 'woocommerce-alidropship' ) ?></h2>
                    <span class="<?php echo esc_attr( self::set( 'modal-popup-close' ) ) ?>"></span>
                </div>
                <div class="<?php echo esc_attr( self::set( 'modal-popup-content-body' ) ) ?>">
                    <div class="<?php echo esc_attr( self::set( array(
						'modal-popup-content-body-row',
						'modal-popup-set-categories'
					) ) ) ?>">
                        <div class="<?php echo esc_attr( self::set( 'modal-popup-set-categories-select-wrap' ) ) ?>">
							<?php echo str_replace( array(
								'vi_wad_product[{ali_product_id}][categories][]',
								'vi-wad-import-data-categories',
								'vi-ui dropdown search'
							), array(
								esc_attr( 'vi_wad_bulk_set_categories' ),
								self::set( esc_attr( 'modal-popup-set-categories-select' ) ),
								'vi-ui dropdown fluid search'
							), self::$categories_options ); ?>
                            <span class="vi-ui black button mini <?php echo esc_attr( self::set( 'modal-popup-set-categories-clear' ) ) ?>"><?php esc_html_e( 'Clear selected', 'woocommerce-alidropship' ) ?></span>
                        </div>
                    </div>
                </div>
                <div class="<?php echo esc_attr( self::set( 'modal-popup-content-footer' ) ) ?>">
                    <span class="button button-primary <?php echo esc_attr( self::set( 'set-categories-button-add' ) ) ?>"
                          title="<?php esc_attr_e( 'Add selected and keep existing categories', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Add', 'woocommerce-alidropship' ) ?></span>
                    <span class="button button-primary <?php echo esc_attr( self::set( 'set-categories-button-set' ) ) ?>"
                          title="<?php esc_attr_e( 'Remove existing categories and add selected', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Set', 'woocommerce-alidropship' ) ?></span>
                    <span class="button <?php echo esc_attr( self::set( 'set-categories-button-cancel' ) ) ?>"><?php esc_html_e( 'Cancel', 'woocommerce-alidropship' ) ?></span>
                </div>
            </div>
            <div class="<?php echo esc_attr( self::set( array(
				'modal-popup-content',
				'modal-popup-content-set-tags'
			) ) ) ?>">
                <div class="<?php echo esc_attr( self::set( 'modal-popup-header' ) ) ?>">
                    <h2><?php esc_html_e( 'Bulk set product tags', 'woocommerce-alidropship' ) ?></h2>
                    <span class="<?php echo esc_attr( self::set( 'modal-popup-close' ) ) ?>"></span>
                </div>
                <div class="<?php echo esc_attr( self::set( 'modal-popup-content-body' ) ) ?>">
                    <div class="<?php echo esc_attr( self::set( array(
						'modal-popup-content-body-row',
						'modal-popup-set-tags'
					) ) ) ?>">
                        <div class="<?php echo esc_attr( self::set( 'modal-popup-set-tags-select-wrap' ) ) ?>">
                            <select name="<?php echo esc_attr( 'vi_wad_bulk_set_tags' ) ?>"
                                    class="vi-ui dropdown fluid search <?php echo esc_attr( self::set( 'modal-popup-set-tags-select' ) ) ?>"
                                    multiple="multiple">
								<?php echo self::$tags_options; ?>
                            </select>
                            <span class="vi-ui black button mini <?php echo esc_attr( self::set( 'modal-popup-set-tags-clear' ) ) ?>"><?php esc_html_e( 'Clear selected', 'woocommerce-alidropship' ) ?></span>
                        </div>
                    </div>
                </div>
                <div class="<?php echo esc_attr( self::set( 'modal-popup-content-footer' ) ) ?>">
                    <span class="button button-primary <?php echo esc_attr( self::set( 'set-tags-button-add' ) ) ?>"
                          title="<?php esc_attr_e( 'Add selected and keep existing tags', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Add', 'woocommerce-alidropship' ) ?></span>
                    <span class="button button-primary <?php echo esc_attr( self::set( 'set-tags-button-set' ) ) ?>"
                          title="<?php esc_attr_e( 'Remove existing tags and add selected', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Set', 'woocommerce-alidropship' ) ?></span>
                    <span class="button <?php echo esc_attr( self::set( 'set-tags-button-cancel' ) ) ?>"><?php esc_html_e( 'Cancel', 'woocommerce-alidropship' ) ?></span>
                </div>
            </div>
            <div class="<?php echo esc_attr( self::set( array(
				'modal-popup-content',
				'modal-popup-content-set-shipping-class'
			) ) ) ?>">
                <div class="<?php echo esc_attr( self::set( 'modal-popup-header' ) ) ?>">
                    <h2><?php esc_html_e( 'Bulk set product shipping class', 'woocommerce-alidropship' ) ?></h2>
                    <span class="<?php echo esc_attr( self::set( 'modal-popup-close' ) ) ?>"></span>
                </div>
                <div class="<?php echo esc_attr( self::set( 'modal-popup-content-body' ) ) ?>">
                    <div class="<?php echo esc_attr( self::set( array(
						'modal-popup-content-body-row',
						'modal-popup-set-shipping-class'
					) ) ) ?>">
                        <div class="<?php echo esc_attr( self::set( 'modal-popup-set-shipping-class-select-wrap' ) ) ?>">
                            <select name="<?php echo esc_attr( 'vi_wad_bulk_set_shipping_class' ) ?>"
                                    class="vi-ui dropdown fluid search <?php echo esc_attr( self::set( 'modal-popup-set-shipping-class-select' ) ) ?>">
                                <option value=""><?php esc_html_e( 'No shipping class', 'woocommerce-alidropship' ) ?></option>
								<?php echo self::$shipping_class_options; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="<?php echo esc_attr( self::set( 'modal-popup-content-footer' ) ) ?>">
                    <span class="button button-primary <?php echo esc_attr( self::set( 'set-shipping-class-button-set' ) ) ?>"><?php esc_html_e( 'Set', 'woocommerce-alidropship' ) ?></span>
                    <span class="button <?php echo esc_attr( self::set( 'set-shipping-class-button-cancel' ) ) ?>"><?php esc_html_e( 'Cancel', 'woocommerce-alidropship' ) ?></span>
                </div>
            </div>
            <div class="<?php echo esc_attr( self::set( array( 'saving-overlay', 'hidden' ) ) ) ?>"></div>
        </div>
		<?php
	}

	/**
	 *
	 */
	public static function override_product_options() {
		$all_options = array(
			'override-keep-product'   => esc_html__( 'Keep Woo product', 'woocommerce-alidropship' ),
			'override-link-only'      => esc_html__( 'Link existing variations only', 'woocommerce-alidropship' ),
			'override-find-in-orders' => esc_html__( 'Find in unfulfilled orders', 'woocommerce-alidropship' ),
			'override-title'          => esc_html__( 'Replace product title', 'woocommerce-alidropship' ),
			'override-images'         => esc_html__( 'Replace product image and gallery', 'woocommerce-alidropship' ),
			'override-description'    => esc_html__( 'Replace description and short description', 'woocommerce-alidropship' ),
			'override-hide'           => wp_kses_post( __( 'Save my choices and do not show these options again(you can still change this in <a target="_blank" href="admin.php?page=woocommerce-alidropship#/override">plugin settings</a>).', 'woocommerce-alidropship' ) ),
		);
		?>
        <div class="<?php echo esc_attr( self::set( array(
			'override-product-options-container',
			'hidden'
		) ) ) ?>">
            <div class="<?php echo esc_attr( self::set( 'override-product-overlay' ) ) ?>"></div>
            <div class="<?php echo esc_attr( self::set( 'override-product-options-content' ) ) ?>">
                <div class="<?php echo esc_attr( self::set( 'override-product-options-content-header' ) ) ?>">
                    <h2>
                        <span class="<?php echo esc_attr( self::set( 'override-product-text-override' ) ) ?>"><?php esc_html_e( 'Override: ', 'woocommerce-alidropship' ) ?></span><span
                                class="<?php echo esc_attr( self::set( 'override-product-text-reimport' ) ) ?>"><?php esc_html_e( 'Reimport: ', 'woocommerce-alidropship' ) ?></span><span
                                class="<?php echo esc_attr( self::set( 'override-product-text-map-existing' ) ) ?>"><?php esc_html_e( 'Import & map existing Woo product: ', 'woocommerce-alidropship' ) ?></span><span
                                class="<?php echo esc_attr( self::set( 'override-product-title' ) ) ?>"></span>
                    </h2>
                    <span class="<?php echo esc_attr( self::set( 'override-product-options-close' ) ) ?>"></span>
                    <div class="vi-ui message warning <?php echo esc_attr( self::set( 'override-product-remove-warning' ) ) ?>"><?php esc_html_e( 'Overridden product and all of its data(including variations, reviews, metadata...) will be deleted. Please make sure you had backed up those kinds of data before continuing!', 'woocommerce-alidropship' ) ?></div>
                </div>
				<?php
				if ( ! self::$settings->get_params( 'override_hide' ) ) {
					?>
                    <div class="<?php echo esc_attr( self::set( array(
						'override-product-options-content-body',
						'override-product-options-content-body-option'
					) ) ) ?>">
						<?php
						foreach ( $all_options as $option_key => $option_value ) {
							?>
                            <div class="<?php echo esc_attr( self::set( array(
								'override-product-options-content-body-row',
								"override-product-options-content-body-row-{$option_key}"
							) ) ) ?>">
                                <div class="<?php echo esc_attr( self::set( 'override-product-options-option-wrap' ) ) ?>">
                                    <input type="checkbox"
                                           value="1" <?php checked( 1, self::$settings->get_params( str_replace( '-', '_', $option_key ) ) ) ?>
                                           data-order_option="<?php echo esc_attr( $option_key ) ?>"
                                           id="<?php echo esc_attr( self::set( 'override-product-options-' . $option_key ) ) ?>"
                                           class="<?php echo esc_attr( self::set( array(
										       'override-product-options-option',
										       'override-product-options-' . $option_key
									       ) ) ) ?>">
                                    <label for="<?php echo esc_attr( self::set( 'override-product-options-' . $option_key ) ) ?>"><?php echo $option_value ?></label>
                                </div>
                            </div>
							<?php
						}
						?>
                    </div>
					<?php
				}
				?>
                <div class="<?php echo esc_attr( self::set( array(
					'override-product-options-content-body',
					'override-product-options-content-body-override-old'
				) ) ) ?>">
                </div>
                <div class="<?php echo esc_attr( self::set( 'override-product-options-content-footer' ) ) ?>">
                    <span class="vi-ui button positive mini <?php echo esc_attr( self::set( array(
	                    'override-product-options-button-override',
                    ) ) ) ?>" data-override_product_id="">
                            <span class="<?php echo esc_attr( self::set( 'override-product-text-override' ) ) ?>"><?php esc_html_e( 'Override', 'woocommerce-alidropship' ) ?></span><span
                                class="<?php echo esc_attr( self::set( 'override-product-text-map-existing' ) ) ?>"><?php esc_html_e( 'Import & Link', 'woocommerce-alidropship' ) ?></span><span
                                class="<?php echo esc_attr( self::set( 'override-product-text-reimport' ) ) ?>"><?php esc_html_e( 'Reimport', 'woocommerce-alidropship' ) ?></span>
                        </span>
                    <span class="vi-ui button mini <?php echo esc_attr( self::set( array(
						'override-product-options-button-cancel',
					) ) ) ?>">
                            <?php esc_html_e( 'Cancel', 'woocommerce-alidropship' ) ?>
                        </span>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Background process to import images
	 */
	public function background_process() {
		self::$process_image        = new VI_WOOCOMMERCE_ALIDROPSHIP_BACKGROUND_DOWNLOAD_IMAGES();
		$nonce                      = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
		if ( wp_verify_nonce( $nonce ) ) {
			if ( ! empty( $_REQUEST['vi_wad_cancel_download_product_image'] ) ) {
				self::$process_image->kill_process();
				wp_safe_redirect( @remove_query_arg( array( 'vi_wad_cancel_download_product_image', '_wpnonce' ) ) );
				exit;
			}
			if ( ! empty( $_REQUEST['vi_wad_run_download_product_image'] ) ) {
				if ( ! self::$process_image->is_process_running() && ! self::$process_image->is_queue_empty() ) {
					self::$process_image->dispatch();
				}
				wp_safe_redirect( @remove_query_arg( array( 'vi_wad_run_download_product_image', '_wpnonce' ) ) );
				exit;
			}
		}
	}

	/**
	 *
	 */
	public function admin_menu() {
		$menu_slug   = 'woocommerce-alidropship-import-list';
		$import_list = add_submenu_page(
			'woocommerce-alidropship',
			esc_html__( 'Import List - AliExpress Dropshipping and Fulfillment for WooCommerce', 'woocommerce-alidropship' ),
			esc_html__( 'Import List', 'woocommerce-alidropship' ),
			apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', $menu_slug ), $menu_slug, array(
			$this,
			'import_list_callback'
		) );
		add_action( "load-$import_list", array( $this, 'screen_options_page' ) );
	}

	/**
	 *
	 */
	public function import_list_callback() {
		$is_main = true;
		if ( ! current_user_can( 'manage_woocommerce' ) && VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Vendor::enable_vendor_integration() ) {
			$is_main = false;
		}
		self::import_list_html( $is_main );
	}

	/**
	 * @param bool $is_main true = for admin
	 */
	public static function import_list_html( $is_main = true ) {
		$user = wp_get_current_user();
		if ( $is_main ) {
			$screen   = get_current_screen();
			$option   = $screen->get_option( 'per_page', 'option' );
			$per_page = get_user_meta( $user->ID, $option, true );
			if ( empty ( $per_page ) || $per_page < 1 ) {
				$per_page = $screen->get_option( 'per_page', 'default' );
			}
		} else {
			$per_page = get_user_meta( $user->ID, 'vi_wad_per_page', true );
			if ( empty ( $per_page ) || $per_page < 1 ) {
				$per_page = 20;
			}
		}
		$decimals = wc_get_price_decimals();
		if ( $decimals < 1 ) {
			$decimals = 1;
		} else {
			$decimals = pow( 10, ( - 1 * $decimals ) );
		}
		$paged = isset( $_GET['paged'] ) ? sanitize_text_field( $_GET['paged'] ) : 1;
		?>
        <div class="wrap woocommerce-alidropship-import-list">
            <h2><?php esc_html_e( 'Import List', 'woocommerce-alidropship' ) ?></h2>
			<?php
			$args = array(
				'post_type'      => 'vi_wad_draft_product',
				'post_status'    => array( 'draft', 'override' ),
				'order'          => 'DESC',
				'orderby'        => 'date',
				'fields'         => 'ids',
				'posts_per_page' => $per_page,
				'paged'          => $paged,
			);
			if ( ! $is_main ) {
				$args['author'] = $user->ID;
			}
			$vi_wad_search_id = isset( $_GET['vi_wad_search_id'] ) ? sanitize_text_field( $_GET['vi_wad_search_id'] ) : '';
			$keyword          = isset( $_GET['vi_wad_search'] ) ? sanitize_text_field( stripslashes( $_GET['vi_wad_search'] ) ) : '';
			if ( $vi_wad_search_id ) {
				$args['post__in']       = array( $vi_wad_search_id );
				$args['posts_per_page'] = 1;
				$keyword                = '';
			} else if ( $keyword ) {
				$args['s'] = $keyword;
			}
			$the_query    = new WP_Query( $args );
			$count        = $the_query->found_posts;
			$total_page   = $the_query->max_num_pages;
			$page_content = '';
			if ( $the_query->have_posts() ) {
				/*After a product is imported, its html content(Import list) will be removed*/
				/*The first wp_editor call includes css file for all editors so call it here and hide it so that editor css is not removed after the first product is imported*/
				?>
                <div class="<?php echo esc_attr( self::set( 'hidden' ) ) ?>">
					<?php
					wp_editor( '', self::set( 'editor-css-inline-holder' ), array(
						'default_editor' => 'html',
						'media_buttons'  => false,
						'tinymce'        => true,
						'quicktags'      => true,
					) );
					?>
                </div>
				<?php
				ob_start();
				?>
                <form method="get" class="vi-ui segment <?php echo esc_attr( self::set( 'pagination-form' ) ) ?>">
                    <input type="hidden" name="page" value="woocommerce-alidropship-import-list">
					<?php
					do_action( 'vi_wad_import_list_search_form' );
					?>
                    <div class="tablenav top">
                        <div class="<?php echo esc_attr( self::set( 'button-import-all-container' ) ) ?>">
                            <input type="checkbox"
                                   class="<?php echo esc_attr( self::set( 'accordion-bulk-item-check-all' ) ) ?>">
                            <span class="vi-ui button primary mini <?php echo esc_attr( self::set( 'button-import-all' ) ) ?>"
                                  title="<?php esc_attr_e( 'Import all products on this page', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Import All', 'woocommerce-alidropship' ) ?></span>
                            <a class="vi-ui button negative mini <?php echo esc_attr( self::set( 'button-empty-import-list' ) ) ?>"
                               href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'vi_wad_empty_product_list', 1 ) ) ) ?>"
                               title="<?php esc_attr_e( 'Remove all products(except overriding products) from Import list', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Empty List', 'woocommerce-alidropship' ) ?></a>
                            <span class="<?php echo esc_attr( self::set( 'accordion-bulk-actions-container' ) ) ?>">
                                <select name="<?php echo esc_attr( 'vi_wad_bulk_actions' ) ?>"
                                        class="vi-ui dropdown <?php echo esc_attr( self::set( 'accordion-bulk-actions' ) ) ?>">
                                    <option value=""><?php esc_html_e( 'Bulk Action', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_categories"><?php esc_html_e( 'Set categories', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_shipping_class"><?php esc_html_e( 'Set shipping class', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_tags"><?php esc_html_e( 'Set tags', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_status_publish"><?php esc_html_e( 'Set status - Publish', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_status_pending"><?php esc_html_e( 'Set status - Pending', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_status_draft"><?php esc_html_e( 'Set status - Draft', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_visibility_visible"><?php esc_html_e( 'Set visibility - Shop and search results', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_visibility_catalog"><?php esc_html_e( 'Set visibility - Shop only', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_visibility_search"><?php esc_html_e( 'Set visibility - Search results only', 'woocommerce-alidropship' ) ?></option>
                                    <option value="set_visibility_hidden"><?php esc_html_e( 'Set visibility - Hidden', 'woocommerce-alidropship' ) ?></option>
                                    <option value="import"><?php esc_html_e( 'Import selected', 'woocommerce-alidropship' ) ?></option>
                                    <option value="remove"><?php esc_html_e( 'Remove selected', 'woocommerce-alidropship' ) ?></option>
                                </select>
                            </span>
                        </div>
                        <div class="tablenav-pages">
                            <div class="pagination-links">
								<?php
								if ( $paged > 2 ) {
									?>
                                    <a class="prev-page button" href="<?php echo esc_url( add_query_arg(
										array(
//											'page'          => 'woocommerce-alidropship-import-list',
											'paged'         => 1,
											'vi_wad_search' => $keyword,
										)
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
//											'page'          => 'woocommerce-alidropship-import-list',
											'paged'         => $p_paged,
											'vi_wad_search' => $keyword,
										)
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
//											'page'          => 'woocommerce-alidropship-import-list',
											'paged'         => $n_paged,
											'vi_wad_search' => $keyword,
										)
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
//											'page'          => 'woocommerce-alidropship-import-list',
											'paged'         => $total_page,
											'vi_wad_search' => $keyword,
										)
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
                                   placeholder="<?php esc_attr_e( 'Search product in import list', 'woocommerce-alidropship' ) ?>"
                                   value="<?php echo esc_attr( $keyword ) ?>">
                            <input type="submit" name="submit" class="button"
                                   value="<?php echo esc_attr( 'Search', 'woocommerce-alidropship' ) ?>">
                        </p>
                    </div>
                </form>
				<?php
				$pagination_html             = ob_get_clean();
				$key                         = 0;
				$currency                    = 'USD';
				$woocommerce_currency        = get_option( 'woocommerce_currency' );
				$woocommerce_currency_symbol = get_woocommerce_currency_symbol( $woocommerce_currency );
				$default_select_image        = self::$settings->get_params( 'product_gallery' );
				$manage_stock                = self::$settings->get_params( 'manage_stock' );
				$product_tags                = self::$settings->get_params( 'product_tags' );
				$product_shipping_class      = self::$settings->get_params( 'product_shipping_class' );
				$product_status              = self::$settings->get_params( 'product_status' );
				$catalog_visibility          = self::$settings->get_params( 'catalog_visibility' );
				$product_sku                 = self::$settings->get_params( 'product_sku' );
				$use_different_currency      = false;
				if ( strtolower( $woocommerce_currency ) !== strtolower( $currency ) ) {
					$use_different_currency = true;
				}
				/*
				$categories = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'orderby'    => 'name, parent',
						'order'      => 'ASC',
						'hide_empty' => false
					)
				);
				if ( is_array( $categories ) && count( $categories ) ) {
					ob_start();
					foreach ( $categories as $category ) {
						$category_name = self::build_category_name( $category->name, $category );
						?>
                        <option value="<?php echo esc_attr( $category->term_id ) ?>"
							<?php if ( in_array( $category->term_id, $product_categories ) ) {
								echo esc_attr( 'selected' );
							} ?>><?php echo esc_html( $category_name ); ?></option>
						<?php
					}
					self::$categories_options = ob_get_clean();
				}
                */
				self::$categories_options = self::dropdown_categories( array(
					'name'  => 'vi_wad_product[{ali_product_id}][categories][]',
					'class' => 'vi-ui dropdown search ' . esc_attr( self::set( 'import-data-categories' ) )
				) );
				ob_start();
				?>
                <option value="publish" <?php selected( $product_status, 'publish' ) ?>><?php esc_html_e( 'Publish', 'woocommerce-alidropship' ) ?></option>
                <option value="pending" <?php selected( $product_status, 'pending' ) ?>><?php esc_html_e( 'Pending', 'woocommerce-alidropship' ) ?></option>
                <option value="draft" <?php selected( $product_status, 'draft' ) ?>><?php esc_html_e( 'Draft', 'woocommerce-alidropship' ) ?></option>
				<?php
				$product_status_options = ob_get_clean();

				ob_start();
				?>
                <option value="visible" <?php selected( $catalog_visibility, 'visible' ) ?>><?php esc_html_e( 'Shop and search results', 'woocommerce-alidropship' ) ?></option>
                <option value="catalog" <?php selected( $catalog_visibility, 'catalog' ) ?>><?php esc_html_e( 'Shop only', 'woocommerce-alidropship' ) ?></option>
                <option value="search" <?php selected( $catalog_visibility, 'search' ) ?>><?php esc_html_e( 'Search results only', 'woocommerce-alidropship' ) ?></option>
                <option value="hidden" <?php selected( $catalog_visibility, 'hidden' ) ?>><?php esc_html_e( 'Hidden', 'woocommerce-alidropship' ) ?></option>
				<?php
				$catalog_visibility_options = ob_get_clean();

				$tags = get_terms(
					array(
						'taxonomy'   => 'product_tag',
						'orderby'    => 'name',
						'order'      => 'ASC',
						'hide_empty' => false
					)
				);
				if ( is_array( $tags ) && count( $tags ) ) {
					ob_start();
					foreach ( $tags as $tag ) {
						?>
                        <option value="<?php echo esc_attr( $tag->name ) ?>"
							<?php if ( in_array( $tag->name, $product_tags ) ) {
								echo esc_attr( 'selected' );
							} ?>><?php echo esc_html( $tag->name ); ?></option>
						<?php
					}
					self::$tags_options = ob_get_clean();
				}

				$shipping_classes = get_terms(
					array(
						'taxonomy'   => 'product_shipping_class',
						'orderby'    => 'name',
						'order'      => 'ASC',
						'hide_empty' => false
					)
				);
				if ( is_array( $shipping_classes ) && count( $shipping_classes ) ) {
					ob_start();
					foreach ( $shipping_classes as $shipping_class ) {
						?>
                        <option value="<?php echo esc_attr( $shipping_class->term_id ) ?>"
							<?php selected( $shipping_class->term_id, $product_shipping_class ) ?>><?php echo esc_html( $shipping_class->name ); ?></option>
						<?php
					}
					self::$shipping_class_options = ob_get_clean();
				}

				foreach ( $the_query->posts as $product_id ) {
					$product            = get_post( $product_id );
					$title              = $product->post_title;
					$description        = $product->post_content;
					$sku                = get_post_meta( $product_id, '_vi_wad_sku', true );
					$product_sku_output = str_replace( '{ali_product_id}', $sku, $product_sku );
//					$attributes   = get_post_meta( $product_id, '_vi_wad_attributes', true );
					$attributes   = self::get_product_attributes( $product_id );
					$store_info   = get_post_meta( $product_id, '_vi_wad_store_info', true );
					$parent       = array();
					$split_option = '';
					if ( is_array( $attributes ) && count( $attributes ) ) {
						foreach ( $attributes as $attribute_k => $attribute_v ) {
							$parent[ $attribute_k ] = $attribute_v['slug'];
							$attribute_values_count = count( $attribute_v['values'] );
							$attribute_name         = isset( $attribute_v['name'] ) ? $attribute_v['name'] : ucfirst( $attribute_v['slug'] );
							if ( is_array( $attribute_v['values'] ) && $attribute_values_count > 1 ) {
								$split_option .= '<span class="vi-ui button mini green ' . self::set( 'button-split' ) . '" data-split_product_message="' . sprintf( _n( 'Split to %s product by %s?', 'Split to %s products by %s?', $attribute_values_count, 'woocommerce-alidropship' ), $attribute_values_count, $attribute_name ) . '" data-product_id="' . $product_id . '" data-split_attribute_id="' . $attribute_k . '">' . sprintf( __( 'Split product by %s(%s)', 'woocommerce-alidropship' ), $attribute_name, $attribute_values_count ) . '</span>';
							}
						}
					}
					$gallery = get_post_meta( $product_id, '_vi_wad_gallery', true );
					$video   = '';
					if ( self::$settings->get_params( 'import_product_video' ) ) {
						$video = self::get_video_url( $product_id );
					}

					if ( ! $gallery ) {
						$gallery = array();
					}
					$desc_images = get_post_meta( $product_id, '_vi_wad_description_images', true );
					if ( ! $desc_images ) {
						$desc_images = array();
					} else {
						$desc_images = array_values( array_unique( $desc_images ) );
					}
					$image = isset( $gallery[0] ) ? $gallery[0] : '';
//					$variations          = get_post_meta( $product_id, '_vi_wad_variations', true );
					$variations  = self::get_product_variations( $product_id );
					$price_array = array_filter( array_merge( array_column( $variations, 'sale_price' ), array_column( $variations, 'regular_price' ) ) );
					$price_alert = false;
					if ( count( $price_array ) ) {
						$min_price = min( $price_array );
						if ( $min_price ) {
							$min_price = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $min_price );
							if ( $min_price === 0.01 ) {
								$price_alert = true;
							}
						}
					}

					$split_variations    = get_post_meta( $product_id, '_vi_wad_split_variations', true );
					$is_variable         = ( is_array( $parent ) && count( $parent ) ) ? 1 : 0;
					$product_type        = $product->post_status;
					$override_product_id = $product->post_parent;
					$override_product    = '';
					if ( $product_type === 'override' && $override_product_id ) {
						$override_product = get_post( $override_product_id );
						if ( ! $override_product ) {
							$product_type        = 'draft';
							$override_product_id = '';
							wp_update_post( array(
								'ID'          => $product_id,
								'post_parent' => 0,
								'post_status' => $product_type,
							) );
						}
					}
					$accordion_class = array(
						'vi-ui',
						'styled',
						'fluid',
						'accordion',
						'active',
						self::set( 'accordion' ),
					);
					if ( $price_alert ) {
						$accordion_class[] = self::set( 'product-price-alert' );
					}
					ob_start();
					?>
                    <div class="<?php echo esc_attr( implode( ' ', $accordion_class ) ) ?>"
                         id="<?php echo esc_attr( self::set( 'product-item-id-' . $product_id ) ) ?>"
                         data-product_id="<?php echo esc_attr( $product_id ) ?>">
                        <div class="title active">
                            <i class="dropdown icon <?php echo esc_attr( self::set( 'accordion-title-icon' ) ); ?>"></i>
                            <input type="checkbox"
                                   class="<?php echo esc_attr( self::set( 'accordion-bulk-item-check' ) ); ?>">
                            <div class="<?php echo esc_attr( self::set( 'accordion-product-image-title-container' ) ) ?>">
                                <div class="<?php echo esc_attr( self::set( 'accordion-product-image-title' ) ) ?>">
                                    <img src="<?php echo esc_url( $image ? $image : wc_placeholder_img_src() ) ?>"
                                         class="<?php echo esc_attr( self::set( 'accordion-product-image' ) ) ?>">
                                    <div class="<?php echo esc_attr( self::set( 'accordion-product-title-container' ) ) ?>">
                                        <div class="<?php echo esc_attr( self::set( 'accordion-product-title' ) ) ?>"
                                             title="<?php echo esc_attr( $title ) ?>"><?php echo esc_html( $title ) ?></div>
										<?php
										if ( ! empty( $store_info['name'] ) ) {
											$store_name = $store_info['name'];
											if ( ! empty( $store_info['url'] ) ) {
												$store_name = '<a class="' . esc_attr__( self::set( 'accordion-store-url' ) ) . '" href="' . esc_attr__( $store_info['url'] ) . '" target="_blank">' . $store_name . '</a>';
											}
											?>
                                            <div>
												<?php
												esc_html_e( 'Store: ', 'woocommerce-alidropship' );
												echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $store_name );
												?>
                                            </div>
											<?php
										}
										?>
                                        <div class="<?php echo esc_attr( self::set( 'accordion-product-date' ) ) ?>"><?php esc_html_e( 'Date: ', 'woocommerce-alidropship' ) ?>
                                            <span><?php echo esc_html( $product->post_date ) ?></span></div>
										<?php
										do_action( 'vi_wad_import_list_product_information', $product );
										?>
                                    </div>
                                </div>
                            </div>
                            <div class="<?php echo esc_attr( self::set( 'button-view-and-edit' ) ) ?>">
                                <a href="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $sku ) ); ?>"
                                   target="_blank" class="vi-ui mini button labeled icon" rel="nofollow"
                                   title="<?php esc_attr_e( 'View this product on AliExpress.com', 'woocommerce-alidropship' ) ?>"><i
                                            class="icon external"></i>
									<?php esc_html_e( 'View on AliExpress', 'woocommerce-alidropship' ) ?></a>
                                <span class="vi-ui button mini negative <?php echo esc_attr( self::set( 'button-remove' ) ) ?>"
                                      data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                      title="<?php esc_attr_e( 'Remove this product from import list', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Remove', 'woocommerce-alidropship' ) ?></span>
								<?php
								if ( $override_product ) {
									if ( $sku == get_post_meta( $override_product_id, '_vi_wad_sku', true ) ) {
										?>
                                        <span class="vi-ui button mini positive <?php echo esc_attr( self::set( array(
											'button-override',
											'button-reimport'
										) ) ) ?>"
                                              data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                              data-override_product_id="<?php echo esc_attr( $override_product_id ) ?>"><?php esc_html_e( 'Reimport', 'woocommerce-alidropship' ) ?></span>
										<?php
									} else {
										?>
                                        <span class="vi-ui button mini positive <?php echo esc_attr( self::set( 'button-override' ) ) ?>"
                                              data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                              data-override_product_id="<?php echo esc_attr( $override_product_id ) ?>"><?php esc_html_e( 'Import & Override', 'woocommerce-alidropship' ) ?></span>
										<?php
									}
								} else {
									?>
                                    <span class="vi-ui button mini positive <?php echo esc_attr( self::set( 'button-import' ) ) ?>"
                                          data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                          title="<?php esc_attr_e( 'Import this product to your WooCommerce store', 'woocommerce-alidropship' ) ?>"><?php esc_html_e( 'Import Now', 'woocommerce-alidropship' ) ?></span>
                                    <span class="vi-ui button mini positive <?php echo esc_attr( self::set( array(
										'button-override',
										'button-map-existing',
										'hidden',
									) ) ) ?>"
                                          title="<?php esc_attr_e( 'Import this product to your WooCommerce store', 'woocommerce-alidropship' ) ?>"
                                          data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                          data-override_product_id="<?php echo esc_attr( $override_product_id ) ?>"><?php esc_html_e( 'Import & Link', 'woocommerce-alidropship' ) ?></span>
									<?php
								}
								?>
                            </div>
                        </div>
                        <div class="content active">
							<?php
							if ( $override_product ) {
								?>
                                <div class="vi-ui message <?php echo esc_attr( self::set( 'override-product-message' ) ) ?>"><?php esc_html_e( 'This product will override: ', 'woocommerce-alidropship' ) ?>
                                    <strong class="<?php echo esc_attr( self::set( 'override-product-product-title' ) ) ?>"><?php echo esc_html( $override_product->post_title ) ?></strong>
                                </div>
								<?php
							}
							?>
                            <div class="<?php echo esc_attr( self::set( 'message' ) ) ?>"></div>
							<?php
							if ( $price_alert ) {
								?>
                                <div class="vi-ui warning message">
									<?php esc_html_e( 'First-purchase discount may apply to this product, please check its price carefully or import with consideration.', 'woocommerce-alidropship' ); ?>
                                </div>
								<?php
							}
							do_action( 'vi_wad_import_list_product_message', $product, $override_product, $is_variable );
							?>
                            <form class="vi-ui form <?php echo esc_attr( self::set( 'product-container' ) ) ?>"
                                  method="post">
                                <div class="vi-ui attached tabular menu">
                                    <div class="item active" data-tab="<?php echo esc_attr( 'product-' . $key ) ?>">
										<?php esc_html_e( 'Product', 'woocommerce-alidropship' ) ?>
                                    </div>
                                    <div class="item <?php echo esc_attr( self::set( 'description-tab-menu' ) ) ?>"
                                         data-tab="<?php echo esc_attr( 'description-' . $key ) ?>">
										<?php esc_html_e( 'Description', 'woocommerce-alidropship' ) ?>
                                    </div>
									<?php
									if ( $is_variable ) {
										$tab_class = array( 'variations-tab-menu' );
										if ( ! self::load_variations_ajax( $variations ) ) {
											$tab_class[] = 'lazy-load';
										}
										?>
                                        <div class="item <?php echo esc_attr( self::set( 'attributes-tab-menu' ) ) ?>"
                                             data-tab="<?php echo esc_attr( 'attributes-' . $key ) ?>">
											<?php esc_html_e( 'Attributes', 'woocommerce-alidropship' ) ?>
                                        </div>
                                        <div class="item <?php echo esc_attr( self::set( $tab_class ) ) ?>"
                                             data-tab="<?php echo esc_attr( 'variations-' . $key ) ?>">
											<?php printf( __( 'Variations(%s)', 'woocommerce-alidropship' ), '<span class="' . self::set( 'selected-variation-count' ) . '">' . ( $split_variations ? count( $split_variations ) : count( $variations ) ) . '</span>', 'woocommerce-alidropship' ) ?>
                                        </div>
										<?php
									}
									if ( count( $gallery ) ) {
										$gallery_count = $default_select_image ? count( $gallery ) : 0;
										?>
                                        <div class="item <?php echo esc_attr( self::set( array(
											'lazy-load',
											'gallery-tab-menu'
										) ) ) ?>"
                                             data-tab="<?php echo esc_attr( 'gallery-' . $key ) ?>">
											<?php printf( __( 'Gallery(%s)', 'woocommerce-alidropship' ), '<span class="' . self::set( 'selected-gallery-count' ) . '">' . $gallery_count . '</span>', 'woocommerce-alidropship' ) ?>
                                        </div>
										<?php
									}
									if ( $video ) {
										?>
                                        <div class="item <?php echo esc_attr( self::set( array(
											'lazy-load',
											'video-tab-menu'
										) ) ) ?>"
                                             data-tab="<?php echo esc_attr( 'video-' . $key ) ?>">
											<?php esc_html_e( 'Video', 'woocommerce-alidropship' ) ?>
                                        </div>
										<?php
									}
									?>
                                </div>
                                <div class="vi-ui bottom attached tab segment active <?php echo esc_attr( self::set( 'product-tab' ) ) ?>"
                                     data-tab_name="product"
                                     data-tab="<?php echo esc_attr( 'product-' . $key ) ?>">
                                    <div class="field">
                                        <div class="fields">
                                            <div class="three wide field">
                                                <div class="<?php echo esc_attr( self::set( 'product-image' ) ) ?> <?php if ( $default_select_image )
													esc_attr_e( self::set( 'selected-item' ) ) ?> ">
                                                    <span class="<?php echo esc_attr( self::set( 'selected-item-icon-check' ) ) ?>"></span>
													<?php
													if ( $image ) {
														?>
                                                        <img src="<?php echo esc_url( $image ) ?>"
                                                             class="<?php echo esc_attr( self::set( 'import-data-image' ) ) ?>">
                                                        <input type="hidden"
                                                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][image]' ) ?>"
                                                               value="<?php echo esc_attr( $default_select_image ? $image : '' ) ?>">
														<?php
													} else {
														?>
                                                        <img src="<?php echo esc_url( wc_placeholder_img_src() ) ?>"
                                                             class="<?php echo esc_attr( self::set( 'import-data-image' ) ) ?>">
                                                        <input type="hidden"
                                                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][image]' ) ?>"
                                                               value="">
														<?php
													}
													?>

                                                </div>
                                            </div>
                                            <div class="thirteen wide field">
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Product title', 'woocommerce-alidropship' ) ?></label>
                                                    <input type="text" value="<?php echo esc_attr( $title ) ?>"
                                                           name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][title]' ) ?>"
                                                           class="<?php echo esc_attr( self::set( 'import-data-title' ) ) ?>">
                                                </div>
                                                <div class="field <?php echo esc_attr( self::set( 'import-data-sku-status-visibility' ) ) ?>">
                                                    <div class="equal width fields">
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Sku', 'woocommerce-alidropship' ) ?></label>
                                                            <input type="text"
                                                                   value="<?php echo esc_attr( ( ! $is_variable && ! empty( $variations[0]['sku'] ) ) ? str_replace( '{ali_product_id}', $variations[0]['sku'], $product_sku ) : str_replace( '{ali_product_id}', $sku, $product_sku ) ) ?>"
                                                                   name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][sku]' ) ?>"
                                                                   class="<?php echo esc_attr( self::set( 'import-data-sku' ) ) ?>">
                                                        </div>
                                                        <div class="field <?php echo esc_attr( self::set( 'import-data-status-container' ) ) ?>">
                                                            <label><?php esc_html_e( 'Product status', 'woocommerce-alidropship' ) ?></label>
                                                            <select
                                                                    name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][status]' ) ?>"
                                                                    class="<?php echo esc_attr( self::set( 'import-data-status' ) ) ?> vi-ui fluid dropdown">
																<?php echo $product_status_options ?>
                                                            </select>
                                                        </div>
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Catalog visibility', 'woocommerce-alidropship' ) ?></label>
                                                            <select
                                                                    name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][catalog_visibility]' ) ?>"
                                                                    class="<?php echo esc_attr( self::set( 'import-data-catalog-visibility' ) ) ?> vi-ui fluid dropdown">
																<?php echo $catalog_visibility_options ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
												<?php
												if ( ! $is_variable ) {
													self::simple_product_price_field_html( $key, $manage_stock, $variations, $use_different_currency, $currency, $product_id, $woocommerce_currency_symbol, $decimals );
												}
												?>
                                                <div class="field">
                                                    <div class="equal width fields">
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Categories', 'woocommerce-alidropship' ) ?></label>
															<?php echo str_replace( '{ali_product_id}', $product_id, self::$categories_options ); ?>
                                                        </div>
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Tags', 'woocommerce-alidropship' ) ?></label>
                                                            <select name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][tags][]' ) ?>"
                                                                    class="vi-ui dropdown search <?php echo esc_attr( self::set( 'import-data-tags' ) ) ?>"
                                                                    multiple="multiple">
																<?php echo self::$tags_options; ?>
                                                            </select>
                                                        </div>
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Shipping class', 'woocommerce-alidropship' ) ?></label>
                                                            <select name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][shipping_class]' ) ?>"
                                                                    class="vi-ui dropdown search <?php echo esc_attr( self::set( 'import-data-shipping-class' ) ) ?>">
                                                                <option value=""><?php esc_html_e( 'No shipping class', 'woocommerce-alidropship' ) ?></option>
																<?php echo self::$shipping_class_options; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
												<?php
												if ( ! $override_product ) {
													?>
                                                    <div class="field">
                                                        <div class="equal width fields">
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Link existing Woo product', 'woocommerce-alidropship' ) ?></label>
                                                                <select name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][override_woo_id]' ) ?>"
                                                                        class="search-product <?php echo esc_attr( self::set( 'override-woo-id' ) ) ?>">
																	<?php
																	$map_woo_id = get_post_meta( $product_id, '_vi_wad_map_woo_id', true );
																	if ( $map_woo_id ) {
																		$map_woo_product = wc_get_product( $map_woo_id );
																		if ( $map_woo_product ) {
																			?>
                                                                            <option value="<?php echo esc_attr( $map_woo_id ) ?>"
                                                                                    selected><?php echo esc_html( "(#{$map_woo_id}) " . $map_woo_product->get_title() ) ?></option>
																			<?php
																		}
																	}
																	?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
													<?php
												}
												?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="vi-ui bottom attached tab segment <?php echo esc_attr( self::set( 'description-tab' ) ) ?>"
                                     data-tab_name="description"
                                     data-tab="<?php echo esc_attr( 'description-' . $key ) ?>">
									<?php
									wp_editor( $description, self::set( 'product-description-' ) . $product_id, array(
										'default_editor' => 'html',
										'media_buttons'  => false,
										'tinymce'        => true,
										'quicktags'      => true,
										'editor_class'   => esc_attr__( self::set( 'import-data-description' ) ),
										'textarea_name'  => esc_attr__( 'vi_wad_product[' . $product_id . '][description]' ),
									) );
									?>
                                </div>
								<?php
								if ( $is_variable ) {
									$variations_tab_class = array( 'variations-tab' );
									$variations_html      = '';
									if ( ! self::load_variations_ajax( $variations ) ) {
										$variations_tab_class[] = 'variations-tab-loaded';
										$variations_tab_class[] = 'lazy-load-tab-data';
										ob_start();
										self::variation_html( $key, $parent, $attributes, $manage_stock, $variations, $use_different_currency, $currency, $product_id, $woocommerce_currency_symbol, $decimals );
										$variations_html = ob_get_clean();
									}
									?>
                                    <div class="vi-ui bottom attached tab segment <?php echo esc_attr( self::set( 'attributes-tab' ) ) ?>"
                                         data-tab="<?php echo esc_attr( 'attributes-' . $key ) ?>"
                                         data-tab_name="attributes"
                                         data-product_id="<?php echo esc_attr( $product_id ) ?>">
										<?php
										/*
										?>
										<div class="vi-ui positive small message">
											<?php esc_html_e( 'If the attribute values do not seem to have meaning, try: ', 'woocommerce-alidropship' ) ?>
											<span data-product_id="<?php echo esc_attr( $product_id ) ?>"
												  data-product_index="<?php echo esc_attr( $key ) ?>"
												  class="vi-ui mini button inverted green <?php echo esc_attr( self::set( 'switch-product-attributes-values' ) ) ?>"><?php esc_html_e( 'Switch attribute values', 'woocommerce-alidropship' ) ?></span>
										</div>
										<?php
										*/
										?>
                                        <table class="vi-ui celled table">
                                            <thead>
                                            <tr>
                                                <th class="<?php echo esc_attr( self::set( 'attributes-attribute-col-position' ) ) ?>"><?php esc_html_e( 'Position', 'woocommerce-alidropship' ) ?></th>
                                                <th class="<?php echo esc_attr( self::set( 'attributes-attribute-col-name' ) ) ?>"><?php esc_html_e( 'Name', 'woocommerce-alidropship' ) ?></th>
                                                <th class="<?php echo esc_attr( self::set( 'attributes-attribute-col-slug' ) ) ?>"><?php esc_html_e( 'Slug', 'woocommerce-alidropship' ) ?></th>
                                                <th class="<?php echo esc_attr( self::set( 'attributes-attribute-col-values' ) ) ?>"><?php esc_html_e( 'Values', 'woocommerce-alidropship' ) ?></th>
                                                <th class="<?php echo esc_attr( self::set( 'attributes-attribute-col-action' ) ) ?>"><?php esc_html_e( 'Action', 'woocommerce-alidropship' ) ?></th>
                                            </tr>
                                            </thead>
                                            <tbody class="ui sortable <?php echo esc_attr( self::set( 'attributes-table-body' ) ) ?>">
											<?php
											self::attributes_tab_html( $product_id, $attributes );
											?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="vi-ui bottom attached tab segment <?php echo esc_attr( self::set( $variations_tab_class ) ) ?>"
                                         data-tab="<?php echo esc_attr( 'variations-' . $key ) ?>"
                                         data-tab_name="variations"
                                         data-product_id="<?php echo esc_attr( $product_id ) ?>">
										<?php
										if ( count( $variations ) ) {
											?>
                                            <div class="vi-ui positive small message">
												<?php esc_html_e( 'You can edit product attributes on Attributes tab.', 'woocommerce-alidropship' ) ?>
                                            </div>
                                            <table class="form-table <?php echo esc_attr( self::set( array(
												'variations-table',
												'table-fix-head',
												'variation-table-attributes-count-' . count( $attributes )
											) ) ) ?>"><?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $variations_html ); ?></table>
											<?php
										}
										?>
                                        <div class="<?php echo esc_attr( self::set( 'button-split-container' ) ) ?>">
											<?php
											echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $split_option );
											?>
                                            <span class="vi-ui button mini green <?php echo esc_attr( self::set( 'button-split' ) ) ?>"
                                                  data-product_id="<?php echo esc_attr( $product_id ) ?>"><?php esc_html_e( 'Split product by selected variation(s)', 'woocommerce-alidropship' ) ?></span>
                                        </div>
                                    </div>
									<?php
								}
								$gallery = array_merge( $gallery, $desc_images );
								if ( count( $gallery ) ) {
									?>
                                    <div class="vi-ui bottom attached tab segment <?php echo esc_attr( self::set( array(
										'product-gallery',
										'lazy-load-tab-data'
									) ) ) ?>"
                                         data-tab_name="gallery"
                                         data-tab="<?php echo esc_attr( 'gallery-' . $key ) ?>">
                                        <div class="segment ui-sortable">
											<?php
											if ( $default_select_image ) {
												foreach ( $gallery as $gallery_k => $gallery_v ) {
													if ( ! in_array( $gallery_v, $desc_images ) ) {
														$item_class = array(
															'product-gallery-item',
															'selected-item'
														);
														if ( $gallery_k === 0 ) {
															$item_class[] = 'is-product-image';
														}
														?>
                                                        <div class="<?php echo esc_attr( self::set( $item_class ) ) ?>">
                                                            <span class="<?php echo esc_attr( self::set( 'selected-item-icon-check' ) ) ?>"></span>
                                                            <i class="<?php echo esc_attr( self::set( 'set-product-image' ) ) ?> star icon"></i>
                                                            <i class="<?php echo esc_attr( self::set( 'set-variation-image' ) ) ?> hand outline up icon"
                                                               title="<?php esc_attr_e( 'Set image for selected variation(s)', 'woocommerce-alidropship' ) ?>"></i>
                                                            <img src="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES . 'loading.gif' ) ?>"
                                                                 data-image_src="<?php echo esc_url( $gallery_v ) ?>"
                                                                 class="<?php echo esc_attr( self::set( 'product-gallery-image' ) ) ?>">
                                                            <input type="hidden"
                                                                   name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][gallery][]' ) ?>"
                                                                   value="<?php echo esc_attr( $gallery_v ) ?>">
                                                        </div>
														<?php
													} else {
														?>
                                                        <div class="<?php echo esc_attr( self::set( 'product-gallery-item' ) ) ?>">
                                                            <span class="<?php echo esc_attr( self::set( 'selected-item-icon-check' ) ) ?>"></span>
                                                            <i class="<?php echo esc_attr( self::set( 'set-product-image' ) ) ?> star icon"></i>
                                                            <i class="<?php echo esc_attr( self::set( 'set-variation-image' ) ) ?> hand outline up icon"
                                                               title="<?php esc_attr_e( 'Set image for selected variation(s)', 'woocommerce-alidropship' ) ?>"></i>
                                                            <img src="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES . 'loading.gif' ) ?>"
                                                                 data-image_src="<?php echo esc_url( $gallery_v ) ?>"
                                                                 class="<?php echo esc_attr( self::set( 'product-gallery-image' ) ) ?>">
                                                            <input type="hidden"
                                                                   name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][gallery][]' ) ?>"
                                                                   value="">
                                                        </div>
														<?php
													}
												}
											} else {
												foreach ( $gallery as $gallery_k => $gallery_v ) {
													?>
                                                    <div class="<?php echo esc_attr( self::set( 'product-gallery-item' ) ) ?>">
                                                        <span class="<?php echo esc_attr( self::set( 'selected-item-icon-check' ) ) ?>"></span>
                                                        <i class="<?php echo esc_attr( self::set( 'set-product-image' ) ) ?> star icon"></i>
                                                        <i class="<?php echo esc_attr( self::set( 'set-variation-image' ) ) ?> hand outline up icon"
                                                           title="<?php esc_attr_e( 'Set image for selected variation(s)', 'woocommerce-alidropship' ) ?>"></i>
                                                        <img src="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES . 'loading.gif' ) ?>"
                                                             data-image_src="<?php echo esc_url( $gallery_v ) ?>"
                                                             class="<?php echo esc_attr( self::set( 'product-gallery-image' ) ) ?>">
                                                        <input type="hidden"
                                                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][gallery][]' ) ?>"
                                                               value="">
                                                    </div>
													<?php
												}
											}
											?>
                                        </div>
                                    </div>
									<?php
								}
								if ( $video ) {
									$video_tab = self::$settings->get_params( 'show_product_video_tab' ) ? esc_html__( 'Show', 'woocommerce-alidropship' ) : esc_html__( 'Hide', 'woocommerce-alidropship' );
									?>
                                    <div class="vi-ui bottom attached tab segment <?php echo esc_attr( self::set( array(
										'product-video',
										'lazy-load-tab-data'
									) ) ) ?>"
                                         data-tab_name="video"
                                         data-tab="<?php echo esc_attr( 'video-' . $key ) ?>">
                                        <table class="form-table">
                                            <tbody>
                                            <tr>
                                                <th><?php esc_html_e( 'Product video tab', 'woocommerce-alidropship' ) ?>                                                </th>
                                                <td>
                                                    <select name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][show_product_video_tab]' ) ?>"
                                                            class="vi-ui dropdown <?php echo self::set( 'show_product_video_tab' ) ?>">
                                                        <option value=""><?php printf( esc_html__( 'Global setting(%s)', 'woocommerce-alidropship' ), $video_tab ); ?></option>
                                                        <option value="show"><?php esc_html_e( 'Show', 'woocommerce-alidropship' ); ?></option>
                                                        <option value="hide"><?php esc_html_e( 'Hide', 'woocommerce-alidropship' ); ?></option>
                                                    </select>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <div class="<?php echo esc_attr( self::set( 'video-wrap' ) ) ?>">
											<?php echo do_shortcode( '[video src="' . esc_url( $video ) . '"]' ); ?>
                                        </div>
                                    </div>
									<?php
								}
								?>
                            </form>
                        </div>
                        <div class="<?php echo esc_attr( self::set( array(
							'product-overlay',
							'hidden'
						) ) ) ?>"></div>
                    </div>
					<?php
					$page_content .= ob_get_clean();
					$key ++;
				}
			} else {
				ob_start();
				?>
                <form method="get" class="vi-ui segment">
                    <input type="hidden" name="page" value="woocommerce-alidropship-import-list">
					<?php
					do_action( 'vi_wad_import_list_search_form' );
					?>
                    <input type="search" class="text short" name="vi_wad_search"
                           placeholder="<?php esc_attr_e( 'Search product', 'woocommerce-alidropship' ) ?>"
                           value="<?php echo esc_attr( $keyword ) ?>">
                    <input type="submit" name="submit" class="button"
                           value="<?php echo esc_attr( 'Search', 'woocommerce-alidropship' ) ?>">
                    <p>
						<?php esc_html_e( 'No products found', 'woocommerce-alidropship' ) ?>
                    </p>
					<?php
					if ( $paged == 1 && ! $vi_wad_search_id && ! $keyword ) {
						?>
                        <p class="<?php echo esc_attr( self::set( 'download-chrome-extension' ) ) ?>"><?php printf( esc_html__( 'To import AliExpress products, you have to install and activate %s', 'woocommerce-alidropship' ), '<a href="https://downloads.villatheme.com/?download=alidropship-extension"
                               target="_blank">WooCommerce AliExpress Dropshipping Extension</a>' ) ?></p>
                        <p>
							<?php VI_WOOCOMMERCE_ALIDROPSHIP_DATA::chrome_extension_buttons(); ?>
                            <a target="_blank" href="https://www.aliexpress.com/"
                               class="vi-ui positive button labeled icon <?php echo esc_attr( self::set( array(
								   'import-aliexpress-products',
								   'hidden'
							   ) ) ) ?>"><i
                                        class="external icon"></i><?php esc_html_e( 'Import AliExpress Products', 'woocommerce-alidropship' ) ?>
                            </a>
                        </p>
						<?php
					}
					?>
                </form>
				<?php
				$pagination_html = ob_get_clean();
			}
			wp_reset_postdata();
			echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $pagination_html );
			if ( $page_content ) {
				do_action( 'vi_wad_import_list_before_products_list' );
				?>
                <div class="vi-ui segment <?php echo esc_attr( self::set( 'import-list' ) ) ?>">
					<?php
					echo $page_content;
					?>
                </div>
				<?php
			}
			?>
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
			'option'  => 'vi_wad_per_page'
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

	/**
	 * @param $attributes
	 * @param $variations
	 */
	public static function split_attributes( &$attributes, $variations ) {
		foreach ( $attributes as $attribute_k => &$attribute_v ) {
			foreach ( $attribute_v['values'] as $value_k => $value_v ) {
				$count = 0;
				foreach ( $variations as $variation_k => $variation_v ) {
					if ( $variation_v['attributes'][ $attribute_v['slug'] ] == $value_v ) {
						$count ++;
					}
				}
				if ( $count === 0 ) {
					unset( $attribute_v['values'][ $value_k ] );
				}
			}
		}
	}

	/**
     * Variations list
     *
	 * @param $key
	 * @param $parent
	 * @param $attributes
	 * @param $manage_stock
	 * @param $variations
	 * @param $use_different_currency
	 * @param $currency
	 * @param $product_id
	 * @param $woocommerce_currency_symbol
	 * @param $decimals
	 * @param bool $lazy_load
	 * @param string $coutry
	 * @param string $company
	 */
	public static function variation_html( $key, $parent, $attributes, $manage_stock, $variations, $use_different_currency, $currency, $product_id, $woocommerce_currency_symbol, $decimals, $lazy_load = true, $coutry = '', $company = '' ) {
		?>
        <thead>
        <tr>
            <td width="1%"></td>
            <td class="<?php echo esc_attr( self::set( 'fix-width' ) ) ?>">
                <input type="checkbox" checked
                       class="<?php echo esc_attr( self::set( array(
					       'variations-bulk-enable',
					       'variations-bulk-enable-' . $key
				       ) ) ) ?>">
            </td>
            <td class="<?php echo esc_attr( self::set( 'fix-width' ) ) ?>">
                <input type="checkbox" checked
                       class="<?php echo esc_attr( self::set( array(
					       'variations-bulk-select-image',
				       ) ) ) ?>">
            </td>
            <th class="<?php echo esc_attr( self::set( 'fix-width' ) ) ?>"><?php esc_html_e( 'Default variation', 'woocommerce-alidropship' ) ?></th>
            <th><?php esc_html_e( 'Sku', 'woocommerce-alidropship' ) ?></th>
			<?php
			if ( is_array( $parent ) && count( $parent ) ) {
				foreach ( $parent as $parent_k => $parent_v ) {
					?>
                    <th class="<?php echo esc_attr( self::set( 'attribute-filter-list-container' ) ) ?>">
						<?php
						$attribute_name = isset( $attributes[ $parent_k ]['name'] ) ? $attributes[ $parent_k ]['name'] : VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attribute_name_by_slug( $parent_v );
						echo esc_html( $attribute_name );
						$attribute_values = isset( $attributes[ $parent_k ]['values'] ) ? $attributes[ $parent_k ]['values'] : array();
						if ( count( $attribute_values ) ) {
							?>
                            <ul class="<?php echo esc_attr( self::set( 'attribute-filter-list' ) ) ?>"
                                data-attribute_slug="<?php echo esc_attr( $parent_v ) ?>">
								<?php
								foreach ( $attribute_values as $attribute_value ) {
									?>
                                    <li class="<?php echo esc_attr( self::set( 'attribute-filter-item' ) ) ?>"
                                        title="<?php echo esc_attr( $attribute_value ) ?>"
                                        data-attribute_slug="<?php echo esc_attr( $parent_v ) ?>"
                                        data-attribute_value="<?php echo esc_attr( trim( $attribute_value ) ) ?>"><?php echo esc_html( $attribute_value ) ?></li>
									<?php
								}
								?>
                            </ul>
							<?php
						}
						?>
                    </th>
					<?php
				}
			}
			$show_shipping_option            = self::$settings->get_params( 'show_shipping_option' );
			$shipping_cost_after_price_rules = self::$settings->get_params( 'shipping_cost_after_price_rules' );
			$shipping_cost_html              = '';
			$shipping_cost                   = 0;
			if ( $show_shipping_option ) {
				$shipping_info = self::get_shipping_info( $product_id, $coutry, $company );
				$shipping_cost = abs( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $shipping_info['shipping_cost'] ) );
				if ( $shipping_info['shipping_cost'] === '' ) {
					$shipping_company = $shipping_info['company'];
					if ( ! empty( $shipping_info['company_name'] ) ) {
						$shipping_company = $shipping_info['company_name'];
					}
					if ( $shipping_company && $shipping_info['freight'] ) {
						$not_available_message = sprintf( esc_html__( '%s is not available', 'woocommerce-alidropship' ), $shipping_company );
					} else {
						$not_available_message = esc_html__( 'Not available', 'woocommerce-alidropship' );
					}
					$shipping_cost_html = '<span class="' . self::set( 'error' ) . '">' . $not_available_message . '</span>';
				} else {
					$shipping_cost_html = wc_price( $shipping_info['shipping_cost'], array(
						'currency'     => $currency,
						'decimals'     => 2,
						'price_format' => '%1$s&nbsp;%2$s'
					) );
				}
				if ( $use_different_currency && $shipping_cost ) {
					$shipping_cost_html .= '(' . wc_price( self::$settings->process_exchange_price( $shipping_cost ) ) . ')';
				}
				?>
                <th class="<?php echo esc_attr( self::set( 'sale-price-col' ) ) ?>"><?php esc_html_e( 'Shipping cost', 'woocommerce-alidropship' ) ?>
					<?php self::shipping_option_html( $shipping_info, $key, $product_id ); ?>
                </th>
				<?php
			}
			?>
            <th><?php esc_html_e( 'Cost', 'woocommerce-alidropship' ) ?>
				<?php
				if ( $show_shipping_option && ! $shipping_cost_after_price_rules ) {
					?>
                    <div><?php esc_html_e( '(price+shipping)', 'woocommerce-alidropship' ) ?></div>
					<?php
				}
				?>
            </th>
            <th class="<?php echo esc_attr( self::set( 'sale-price-col' ) ) ?>"><?php esc_html_e( 'Sale price', 'woocommerce-alidropship' ) ?>
                <div class="<?php echo esc_attr( self::set( 'set-price' ) ) ?>"
                     data-set_price="sale_price"><?php esc_html_e( 'Set price', 'woocommerce-alidropship' ) ?></div>
            </th>
            <th class="<?php echo esc_attr( self::set( 'regular-price-col' ) ) ?>"><?php esc_html_e( 'Regular price', 'woocommerce-alidropship' ) ?>
                <div class="<?php echo esc_attr( self::set( 'set-price' ) ) ?>"
                     data-set_price="regular_price"><?php esc_html_e( 'Set price', 'woocommerce-alidropship' ) ?></div>
            </th>
			<?php
			if ( $manage_stock ) {
				?>
                <th class="<?php echo esc_attr( self::set( 'inventory-col' ) ) ?>"><?php esc_html_e( 'Inventory', 'woocommerce-alidropship' ) ?></th>
				<?php
			}
			?>
        </tr>
        </thead>
        <tbody>
		<?php
		$split_variations = get_post_meta( $product_id, '_vi_wad_split_variations', true );
		foreach ( $variations as $variation_key => $variation ) {
			$variation_image = $variation['image'];
			$inventory       = intval( $variation['stock'] );
			if ( $shipping_cost_after_price_rules ) {
				$variation_sale_price    = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variation['sale_price'] );
				$variation_regular_price = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variation['regular_price'] );
				$price                   = $variation_sale_price ? $variation_sale_price : $variation_regular_price;
				$sale_price              = self::$settings->process_price( $price, true );
				if ( $sale_price ) {
					$sale_price += $shipping_cost;
				}
				$regular_price = self::$settings->process_price( $price ) + $shipping_cost;
			} else {
				$variation_sale_price    = $variation['sale_price'] ? ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variation['sale_price'] ) + $shipping_cost ) : VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variation['sale_price'] );
				$variation_regular_price = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variation['regular_price'] ) + $shipping_cost;
				$price                   = $variation_sale_price ? $variation_sale_price : $variation_regular_price;
				$sale_price              = self::$settings->process_price( $price, true );
				$regular_price           = self::$settings->process_price( $price );
			}
			$profit      = $variation_sale_price ? ( $sale_price - $variation_sale_price ) : ( $regular_price - $variation_regular_price );
			$cost_html   = wc_price( $price, array(
				'currency'     => $currency,
				'decimals'     => 2,
				'price_format' => '%1$s&nbsp;%2$s'
			) );
			$profit_html = wc_price( $profit, array(
				'currency'     => $currency,
				'decimals'     => 2,
				'price_format' => '%1$s&nbsp;%2$s'
			) );
			if ( $use_different_currency ) {
				$cost_html   .= '(' . wc_price( self::$settings->process_exchange_price( $price ) ) . ')';
				$profit_html .= '(' . wc_price( self::$settings->process_exchange_price( $profit ) ) . ')';
			}
			$sale_price            = self::$settings->process_exchange_price( $sale_price );
			$regular_price         = self::$settings->process_exchange_price( $regular_price );
			$image_src             = $variation_image ? $variation_image : wc_placeholder_img_src();
			$checked               = '';
			$variation_image_class = array( 'variation-image' );
			if ( ! $split_variations || in_array( $variation['skuAttr'], $split_variations ) || in_array( "{$variation['skuId']}{$variation['skuAttr']}", $split_variations ) ) {
				$checked                 = 'checked';
				$variation_image_class[] = 'selected-item';
				self::$variations_count ++;
			}
			?>
            <tr class="<?php echo esc_attr( self::set( 'product-variation-row' ) ) ?>">
                <td class="<?php echo esc_attr( self::set( 'product-variation-row-number' ) ) ?>"><?php echo esc_html( $variation_key + 1 ) ?></td>
                <td>
                    <input type="checkbox" <?php echo esc_attr( $checked ) ?>
                           class="<?php echo esc_attr( self::set( array(
						       'variation-enable',
						       'variation-enable-' . $key,
						       'variation-enable-' . $key . '-' . $variation_key
					       ) ) ) ?>">
                </td>
                <td>
                    <div class="<?php echo esc_attr( self::set( $variation_image_class ) ) ?>">
                        <span class="<?php echo esc_attr( self::set( 'selected-item-icon-check' ) ) ?>"></span>
                        <img src="<?php echo esc_url( $lazy_load ? VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES . 'loading.gif' : $image_src ) ?>"
                             data-image_src="<?php echo esc_url( $image_src ) ?>"
                             class="<?php echo esc_attr( self::set( 'import-data-variation-image' ) ) ?>">
                        <input type="hidden"
                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][' . $variation_key . '][image]' ) ?>"
                               value="<?php echo esc_attr( $variation_image ? $variation_image : '' ) ?>">
                    </div>
                </td>
                <td><input type="radio"
                           class="<?php echo esc_attr( self::set( 'import-data-variation-default' ) ) ?>"
                           name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][default_variation]' ) ?>"
                           value="<?php echo esc_attr( $variation['skuAttr'] ) ?>">
                </td>
                <td>
                    <div>
                        <input type="text"
                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][' . $variation_key . '][sku]' ) ?>"
                               value="<?php echo esc_attr( $variation['sku'] ) ?>"
                               class="<?php echo esc_attr( self::set( 'import-data-variation-sku' ) ) ?>">
                        <input type="hidden"
                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][' . $variation_key . '][skuId]' ) ?>"
                               value="<?php echo esc_attr( $variation['skuId'] ) ?>"
                               class="<?php echo esc_attr( self::set( 'import-data-variation-sku-id' ) ) ?>">
                        <input type="hidden"
                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][' . $variation_key . '][skuAttr]' ) ?>"
                               value="<?php echo esc_attr( $variation['skuAttr'] ) ?>"
                               class="<?php echo esc_attr( self::set( 'import-data-variation-sku-attr' ) ) ?>">
                        <input type="hidden"
                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][' . $variation_key . '][ship_from]' ) ?>"
                               value="<?php echo esc_attr( isset( $variation['ship_from'] ) ? $variation['ship_from'] : '' ) ?>"
                               class="<?php echo esc_attr( self::set( 'import-data-variation-ship-from' ) ) ?>">
                    </div>
                </td>
				<?php
				if ( is_array( $parent ) && count( $parent ) ) {
					foreach ( $parent as $parent_k => $parent_v ) {
						?>
                        <td>
                            <input type="text" readonly
                                   data-attribute_slug="<?php echo esc_attr( $parent_v ) ?>"
                                   data-attribute_value="<?php echo esc_attr( isset( $variation['attributes'][ $parent_v ] ) ? trim( $variation['attributes'][ $parent_v ] ) : '' ) ?>"
                                   name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][' . $variation_key . '][attributes][' . $parent_v . ']' ) ?>"
                                   class="<?php echo esc_attr( self::set( 'import-data-variation-attribute' ) ) ?>"
                                   value="<?php echo esc_attr( isset( $variation['attributes'][ $parent_v ] ) ? $variation['attributes'][ $parent_v ] : '' ) ?>">
                        </td>
						<?php
					}
				}
				if ( $shipping_cost_html !== '' ) {
					?>
                    <td>
                        <div class="<?php echo esc_attr( self::set( 'price-field' ) ) ?>">
                            <span class="<?php echo esc_attr( self::set( 'import-data-shipping-cost' ) ) ?>"><?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $shipping_cost_html ) ?></span>
                        </div>
                    </td>
					<?php
				}
				?>
                <td>
                    <div class="<?php echo esc_attr( self::set( 'price-field' ) ) ?>">
                        <span class="<?php echo esc_attr( self::set( 'import-data-variation-cost' ) ) ?>">
                            <?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $cost_html ) ?>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="vi-ui left labeled input">
                        <label for="amount"
                               class="vi-ui label"><?php echo esc_html( $woocommerce_currency_symbol ) ?></label>
                        <input type="number" min="0"
                               step="<?php echo esc_attr( $decimals ) ?>"
                               value="<?php echo esc_attr( $sale_price ) ?>"
                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][' . $variation_key . '][sale_price]' ) ?>"
                               class="<?php echo esc_attr( self::set( 'import-data-variation-sale-price' ) ) ?>">
                    </div>
                </td>
                <td>
                    <div class="vi-ui left labeled input">
                        <label for="amount"
                               class="vi-ui label"><?php echo esc_html( $woocommerce_currency_symbol ) ?></label>
                        <input type="number" min="0"
                               step="<?php echo esc_attr( $decimals ) ?>"
                               value="<?php echo esc_attr( $regular_price ) ?>"
                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][' . $variation_key . '][regular_price]' ) ?>"
                               class="<?php echo esc_attr( self::set( 'import-data-variation-regular-price' ) ) ?>">
                    </div>
                </td>
				<?php
				if ( $manage_stock ) {
					?>
                    <td>
                        <input type="number" min="0"
                               step="<?php echo esc_attr( $decimals ) ?>"
                               value="<?php echo esc_attr( $inventory ) ?>"
                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][' . $variation_key . '][stock]' ) ?>"
                               class="<?php echo esc_attr( self::set( 'import-data-variation-inventory' ) ) ?>">
                    </td>
					<?php
				}
				?>
            </tr>
			<?php
		}
		?>
        </tbody>
		<?php
	}

	/**
     * Get shipping info, save changes if any
     * Data are cached to reduce requests as this use an open API from AliExpress
     *
	 * @param $product_id
	 * @param $country
	 * @param string $company
	 * @param int $cache_time
	 *
	 * @return array|mixed
	 */
	public static function get_shipping_info( $product_id, $country, $company = '', $cache_time = 600 ) {
		$shipping_info = get_post_meta( $product_id, '_vi_wad_shipping_info', true );
		$now           = time();
		$freight       = array();
		if ( ! $shipping_info ) {
			if ( ! $country ) {
				$country = 'US';
			}
			$shipping_info = array(
				'time'          => 0,
				'country'       => $country,
				'company'       => '',
				'company_name'  => '',
				'freight'       => json_encode( $freight ),
				'shipping_cost' => '',
				'delivery_time' => '',
			);
		} else {
			$freight = vi_wad_json_decode( $shipping_info['freight'] );
			if ( ! $country ) {
				$country = $shipping_info['country'];
				if ( ! $company ) {
					$company = $shipping_info['company'];
				}
			} elseif ( $country === $shipping_info['country'] && ! $company ) {
				$company = $shipping_info['company'];
			}
		}
		$ali_product_id = get_post_meta( $product_id, '_vi_wad_sku', true );
		if ( $ali_product_id ) {
			$maybe_update = false;
			if ( $cache_time == 0 || $now - floatval( $shipping_info['time'] ) > $cache_time || $country !== $shipping_info['country'] ) {
				$maybe_update             = true;
				$shipping_info['country'] = $country;
				$get_freight              = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_freight( $ali_product_id, $country );
				if ( $get_freight['status'] === 'success' ) {
					$shipping_info['time'] = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_shipping_cache_time( $now );
					$freight               = $get_freight['freight'];
				} else {
					if ( $get_freight['code'] !== 'http_request_failed' ) {
						$shipping_info['time'] = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_shipping_cache_time( $now );
						$freight               = array();
					}
				}
				$shipping_info['freight'] = json_encode( $freight );

//				$freight                  = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_shipping($ali_product_id,VI_WOOCOMMERCE_ALIDROPSHIP_Admin_API::filter_country( $country ));
//				$shipping_info['freight'] = json_encode( $freight );
//				$maybe_update             = true;
			}

//			if ( $company && $company !== $shipping_info['company'] || $shipping_info['shipping_cost'] === null ) {
//				$shipping_info['shipping_cost'] = '';
//				$shipping_info['delivery_time'] = '';
//				$maybe_update                   = true;
//				foreach ( $freight as $key => $value ) {
//					if ( $value['company'] === $company ) {
//						$shipping_info['company']       = $company;
//						$shipping_info['delivery_time'] = $value['delivery_time'];
//						$shipping_info['shipping_cost'] = $value['shipping_cost'];
//						$found = true;
//						break;
//					}
//				}
//			}
//			if ( ! $found && ! $company ) {
//				$shipping_info['company']       = $freight[0]['company'];
//				$shipping_info['delivery_time'] = $freight[0]['delivery_time'];
//				$shipping_info['shipping_cost'] = $freight[0]['shipping_cost'];
//				$maybe_update = true;
//			}

			if ( count( $freight ) ) {
				$found = false;
				if ( $maybe_update || ( $company && $company !== $shipping_info['company'] ) || $shipping_info['shipping_cost'] === null ) {
					$shipping_info['shipping_cost'] = '';
					$shipping_info['delivery_time'] = '';
					$maybe_update                   = true;
					foreach ( $freight as $key => $value ) {
						if ( $value['serviceName'] === $company ) {
							$shipping_info['company']       = $company;
							$shipping_info['company_name']  = isset( $value['company'] ) ? $value['company'] : $value['service'];
							$shipping_info['delivery_time'] = isset( $value['time'] ) ? $value['time'] : $value['dateEstimated'];
							$shipping_info['shipping_cost'] = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_freight_amount( $value );
							$found                          = true;
							break;
						}
					}
				}
				if ( ! $found ) {
					if ( ! $company ) {
						$maybe_update                   = true;
						$shipping_info['company']       = $freight[0]['serviceName'];
						$shipping_info['company_name']  = isset( $freight[0]['company'] ) ? $freight[0]['company'] : $freight[0]['service'];
						$shipping_info['delivery_time'] = isset( $freight[0]['time'] ) ? $freight[0]['time'] : $freight[0]['dateEstimated'];
						$shipping_info['shipping_cost'] = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_freight_amount( $freight[0] );
					} elseif ( $company !== $shipping_info['company'] ) {
						$maybe_update                   = true;
						$shipping_info['shipping_cost'] = '';
						$shipping_info['delivery_time'] = '';
					}
				}
			} else {
				$shipping_info['shipping_cost'] = '';
				$shipping_info['delivery_time'] = '';
			}
			if ( $maybe_update ) {
				update_post_meta( $product_id, '_vi_wad_shipping_info', $shipping_info );
			}
		}
		$shipping_info['freight'] = $freight;

		return $shipping_info;
	}

	/**
     * Shipping options
     *
	 * @param $shipping_info
	 * @param $key
	 * @param $product_id
	 * @param string $product_type
	 */
	public static function shipping_option_html( $shipping_info, $key, $product_id, $product_type = 'variable' ) {
		$wc_country       = new WC_Countries();
		$countries        = $wc_country->get_countries();
		$shipping_company = $shipping_info['company'];
		$shipping_country = $shipping_info['country'];
		if ( $shipping_country === 'UK' ) {
			$shipping_country = 'GB';
		}
		?>
        <div class="<?php echo esc_attr( self::set( 'shipping-info' ) ) ?>"
             data-product_index="<?php echo esc_attr( $key ) ?>"
             data-product_type="<?php echo esc_attr( $product_type ) ?>"
             data-product_id="<?php echo esc_attr( $product_id ) ?>">
			<?php
			if ( count( $countries ) ) {
				?>
                <div class="<?php echo esc_attr( self::set( 'shipping-info-country-wrap' ) ) ?>">
                    <select name="<?php echo esc_attr( self::set( 'shipping-info-country', true ) ) ?>"
                            class="vi-ui fluid search dropdown <?php echo esc_attr( self::set( 'shipping-info-country' ) ) ?>">
                        <option><?php esc_html_e( 'Select country', 'woocommerce-alidropship' ) ?></option>
						<?php
						foreach ( $countries as $countries_k => $value ) {
							?>
                            <option value="<?php echo esc_attr( $countries_k ) ?>" <?php selected( $shipping_country, $countries_k ) ?>><?php echo esc_html( $value ) ?></option>
							<?php
						}
						?>
                    </select>
                </div>
				<?php
			}
			if ( self::$settings->get_params( 'show_shipping_option' ) ) {
				if ( count( $shipping_info['freight'] ) ) {
					$found = false;
					ob_start();
					if ( $product_type === 'simple' ) {
						foreach ( $shipping_info['freight'] as $freight ) {
							$selected = '';
							if ( $shipping_company === $freight['serviceName'] ) {
								$selected = 'selected';
								$found    = true;
							}
							$delivery_time   = self::process_delivery_time( isset( $freight['time'] ) ? $freight['time'] : $freight['dateEstimated'] );
							$shipping_amount = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_freight_amount( $freight );
							$company         = isset( $freight['company'] ) ? $freight['company'] : $freight['service'];
							?>
                            <option value="<?php echo esc_attr( $freight['serviceName'] ) ?>" <?php echo esc_attr( $selected ) ?>><?php echo esc_html( "\${$shipping_amount}-{$company}({$delivery_time})" ) ?></option>
							<?php
						}
					} else {
						foreach ( $shipping_info['freight'] as $freight ) {
							$selected = '';
							if ( $shipping_company === $freight['serviceName'] ) {
								$selected = 'selected';
								$found    = true;
							}
							$delivery_time   = self::process_delivery_time( isset( $freight['time'] ) ? $freight['time'] : $freight['dateEstimated'] );
							$shipping_amount = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_freight_amount( $freight );
							$company         = isset( $freight['company'] ) ? $freight['company'] : $freight['service'];
							?>
                            <option value="<?php echo esc_attr( $freight['serviceName'] ) ?>" <?php echo esc_attr( $selected ) ?>><?php echo esc_html( "{$company}({$delivery_time}, \${$shipping_amount})" ) ?></option>
							<?php
						}
					}
					$option_html = ob_get_clean();
					?>
                    <div class="<?php echo esc_attr( self::set( 'shipping-info-company-wrap' ) ) ?>">
                        <select name="<?php echo esc_attr( self::set( 'shipping-info-company', true ) ) ?>"
                                class="vi-ui dropdown fluid <?php echo esc_attr( self::set( 'shipping-info-company' ) ) ?>">
							<?php
							if ( ! $found ) {
								?>
                                <option value=""><?php esc_html_e( 'Select shipping company', 'woocommerce-alidropship' ) ?></option>
								<?php
							}
							echo $option_html;
							?>
                        </select>
                    </div>
					<?php
				} elseif ( $product_type === 'simple' ) {
					?>
                    <div class="<?php echo esc_attr( self::set( 'shipping-info-company-wrap' ) ) ?>"><?php esc_html_e( 'Not available', 'woocommerce-alidropship' ) ?></div>
					<?php
				}
			}
			?>
        </div>
		<?php
	}

	/**
     * For simple products
     *
	 * @param $key
	 * @param $manage_stock
	 * @param $variations
	 * @param $use_different_currency
	 * @param $currency
	 * @param $product_id
	 * @param $woocommerce_currency_symbol
	 * @param $decimals
	 * @param string $coutry
	 * @param string $company
	 */
	public static function simple_product_price_field_html( $key, $manage_stock, $variations, $use_different_currency, $currency, $product_id, $woocommerce_currency_symbol, $decimals, $coutry = '', $company = '' ) {
		$show_shipping_option            = self::$settings->get_params( 'show_shipping_option' );
		$shipping_cost_after_price_rules = self::$settings->get_params( 'shipping_cost_after_price_rules' );
		$shipping_cost_html              = '';
		$cost_label                      = esc_html__( 'Cost', 'woocommerce-alidropship' );
		$shipping_cost                   = 0;
		if ( $show_shipping_option ) {
			if ( ! $shipping_cost_after_price_rules ) {
				$cost_label = esc_html__( 'Cost(price+shipping)', 'woocommerce-alidropship' );
			}
			$shipping_info = self::get_shipping_info( $product_id, $coutry, $company );
			$shipping_cost = abs( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $shipping_info['shipping_cost'] ) );
			ob_start();
			?>
            <div class="field">
                <label><?php esc_html_e( 'Shipping cost', 'woocommerce-alidropship' ) ?></label>
				<?php self::shipping_option_html( $shipping_info, $key, $product_id, 'simple' ); ?>
            </div>
			<?php
			$shipping_cost_html = ob_get_clean();
		}
		$inventory = intval( $variations[0]['stock'] );
		if ( $shipping_cost_after_price_rules ) {
			$variation_sale_price    = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations[0]['sale_price'] );
			$variation_regular_price = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations[0]['regular_price'] );
			$price                   = $variation_sale_price ? $variation_sale_price : $variation_regular_price;
			$sale_price              = self::$settings->process_price( $price, true );
			if ( $sale_price ) {
				$sale_price += $shipping_cost;
			}
			$regular_price = self::$settings->process_price( $price ) + $shipping_cost;
		} else {
			$variation_sale_price    = $variations[0]['sale_price'] ? ( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations[0]['sale_price'] ) + $shipping_cost ) : VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations[0]['sale_price'] );
			$variation_regular_price = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $variations[0]['regular_price'] ) + $shipping_cost;
			$price                   = $variation_sale_price ? $variation_sale_price : $variation_regular_price;
			$sale_price              = self::$settings->process_price( $price, true );
			$regular_price           = self::$settings->process_price( $price );
		}
		$profit      = $variation_sale_price ? ( $sale_price - $variation_sale_price ) : ( $regular_price - $variation_regular_price );
		$cost_html   = wc_price( $price, array(
			'currency'     => $currency,
			'decimals'     => 2,
			'price_format' => '%1$s&nbsp;%2$s'
		) );
		$profit_html = wc_price( $profit, array(
			'currency'     => $currency,
			'decimals'     => 2,
			'price_format' => '%1$s&nbsp;%2$s'
		) );
		if ( $use_different_currency ) {
			$cost_html   .= '(' . wc_price( self::$settings->process_exchange_price( $price ) ) . ')';
			$profit_html .= '(' . wc_price( self::$settings->process_exchange_price( $profit ) ) . ')';
		}
		$sale_price    = self::$settings->process_exchange_price( $sale_price );
		$regular_price = self::$settings->process_exchange_price( $regular_price );
		?>
        <div class="field <?php echo esc_attr( self::set( 'simple-product-price-field' ) ) ?>">
            <input type="hidden"
                   name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][0][skuId]' ) ?>"
                   value="<?php echo esc_attr( $variations[0]['skuId'] ) ?>">
            <input type="hidden"
                   name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][0][skuAttr]' ) ?>"
                   value="<?php echo esc_attr( $variations[0]['skuAttr'] ) ?>">
            <div class="equal width fields">
				<?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $shipping_cost_html ); ?>
                <div class="field">
                    <label><?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $cost_label ); ?></label>
                    <div class="<?php echo esc_attr( self::set( 'price-field' ) ) ?>">
						<?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $cost_html ) ?>
                    </div>
                </div>
                <div class="field">
                    <label><?php esc_html_e( 'Sale price', 'woocommerce-alidropship' ) ?></label>
                    <div class="vi-ui left labeled input">
                        <label for="amount"
                               class="vi-ui label"><?php echo esc_html( $woocommerce_currency_symbol ) ?></label>
                        <input
                                type="number" min="0"
                                step="<?php echo esc_attr( $decimals ) ?>"
                                value="<?php echo esc_attr( $sale_price ) ?>"
                                name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][0][sale_price]' ) ?>"
                                class="<?php echo esc_attr( self::set( 'import-data-variation-sale-price' ) ) ?>">
                    </div>
                </div>
                <div class="field">
                    <label><?php esc_html_e( 'Regular price', 'woocommerce-alidropship' ) ?></label>
                    <div class="vi-ui left labeled input">
                        <label for="amount"
                               class="vi-ui label"><?php echo esc_html( $woocommerce_currency_symbol ) ?></label>
                        <input type="number" min="0"
                               step="<?php echo esc_attr( $decimals ) ?>"
                               value="<?php echo esc_attr( $regular_price ) ?>"
                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][0][regular_price]' ) ?>"
                               class="<?php echo esc_attr( self::set( 'import-data-variation-regular-price' ) ) ?>">
                    </div>
                </div>
				<?php
				if ( $manage_stock ) {
					?>
                    <div class="field">
                        <label><?php esc_html_e( 'Inventory', 'woocommerce-alidropship' ) ?></label>
                        <input
                                type="number" min="0"
                                step="<?php echo esc_attr( $decimals ) ?>"
                                value="<?php echo esc_attr( $inventory ) ?>"
                                name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][variations][0][stock]' ) ?>"
                                class="<?php echo esc_attr( self::set( 'import-data-variation-inventory' ) ) ?>">
                    </div>
					<?php
				}
				?>
            </div>
        </div>
		<?php
	}

	/**
     * Variations tabs will be loaded via ajax when clicked if show_shipping_option option is ON or a product has from 10 variations
     *
	 * @param $variations
	 *
	 * @return bool
	 */
	public static function load_variations_ajax( $variations ) {
		return ( self::$settings->get_params( 'show_shipping_option' ) || count( $variations ) >= 10 );
	}

	/**
	 * @param $time
	 *
	 * @return string
	 */
	public static function process_delivery_time( $time ) {
		$time_arr = explode( '-', $time );
		if ( count( $time_arr ) === 2 ) {
			$min = intval( $time_arr[0] );
			if ( $min === intval( $time_arr[1] ) ) {
				$return = sprintf( _n( '%s day', '%s days', $min, 'woocommerce-alidropship' ), $min );
			} else {
				$return = sprintf( esc_html__( '%s days', 'woocommerce-alidropship' ), $time );
			}
		} else {
			$return = sprintf( _n( '%s day', '%s days', $time, 'woocommerce-alidropship' ), $time );
		}

		return $return;
	}

	/**
     * Get filtered attributes data(edited or mapped if any)
     *
	 * @param $product_id
	 *
	 * @return array|mixed
	 */
	public static function get_product_attributes( $product_id ) {
		$attributes        = get_post_meta( $product_id, '_vi_wad_attributes', true );
		$origin_attributes = $attributes;
		if ( is_array( $attributes ) && count( $attributes ) ) {
			$attributes_mapping_origin      = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_origin();
			$attributes_mapping_replacement = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_replacement();
			foreach ( $attributes as $key => $value ) {
				if ( ! empty( $value['slug_edited'] ) ) {
					$attributes[ $key ]['slug'] = $value['slug_edited'];
					unset( $attributes[ $key ]['slug_edited'] );
				}
				if ( ! empty( $value['name_edited'] ) ) {
					$attributes[ $key ]['name'] = $value['name_edited'];
					unset( $attributes[ $key ]['name_edited'] );
				}
				if ( ! empty( $value['values_edited'] ) ) {
					$attributes[ $key ]['values'] = $value['values_edited'];
					unset( $attributes[ $key ]['values_edited'] );
				} elseif ( is_array( $value['values'] ) ) {
					foreach ( $value['values'] as $term_k => $term_v ) {
						$found = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::find_attribute_replacement( $attributes_mapping_origin, $attributes_mapping_replacement, $term_v, $origin_attributes[ $key ]['slug'] );
						if ( $found ) {
							$attributes[ $key ]['values'][ $term_k ] = $found;
						}
					}
				}
			}
		}

		return $attributes;
	}

	/**
	 * @param $product_id
	 *
	 * @return bool|string
	 */
	public static function get_video_url( $product_id ) {
		$video      = '';
		$video_info = get_post_meta( $product_id, '_vi_wad_video', true );
		if ( ! empty( $video_info['ali_member_id'] ) && ! empty( $video_info['media_id'] ) ) {
			if ( ! empty( $video_info['url'] ) ) {
				$video = esc_url_raw( $video_info['url'] );
			} else {
				$video_link = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_valid_aliexpress_video_link( $video_info );
				if ( $video_link ) {
					$video             = $video_link;
					$video_info['url'] = $video;
					update_post_meta( $product_id, '_vi_wad_video', $video_info );
				}
			}
		}

		return $video;
	}

	/**
     * Get variations
     *
	 * @param $product_id
	 * @param bool $split
	 * @param bool $adjust_attributes_key
	 *
	 * @return array|mixed
	 */
	public static function get_product_variations( $product_id, $split = false, $adjust_attributes_key = true ) {
		$variations       = get_post_meta( $product_id, '_vi_wad_variations', true );
		$split_variations = get_post_meta( $product_id, '_vi_wad_split_variations', true );
		if ( is_array( $variations ) && count( $variations ) ) {
			$attributes_mapping_origin      = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_origin();
			$attributes_mapping_replacement = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attributes_mapping_replacement();
			foreach ( $variations as $key => $value ) {
				if ( ! $split || empty( $split_variations ) || in_array( $value['skuAttr'], $split_variations ) || in_array( "{$value['skuId']}{$value['skuAttr']}", $split_variations ) ) {
					$original_attributes = $value['attributes'];
					$edited              = false;
					if ( isset( $value['attributes_edited'] ) ) {
						$edited                           = true;
						$variations[ $key ]['attributes'] = $value['attributes_edited'];
						unset( $variations[ $key ]['attributes_edited'] );
					}
					if ( is_array( $variations[ $key ]['attributes'] ) ) {
						foreach ( $variations[ $key ]['attributes'] as $attribute_slug => $attribute_value ) {
							$found = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::find_attribute_replacement( $attributes_mapping_origin, $attributes_mapping_replacement, $attribute_value, $attribute_slug );
							if ( $found ) {
								$variations[ $key ]['attributes'][ $attribute_slug ] = $found;
							}
						}
					}
					if ( $edited && ! $adjust_attributes_key ) {
						$variations[ $key ]['attributes_edited'] = $variations[ $key ]['attributes'];
						$variations[ $key ]['attributes']        = $original_attributes;
					}
				} else {
					unset( $variations[ $key ] );
				}
			}
			$variations = array_values( $variations );
		}

		return (array) $variations;
	}

	/**
     * Show category tree
     *
	 * @param $category_name
	 * @param $category
	 *
	 * @return string
	 */
	public static function build_category_name( $category_name, $category ) {
		if ( $category->parent ) {
			$category_parent = get_term_by( 'id', $category->parent, 'product_cat' );
			if ( $category_parent ) {
				$category_name = "{$category_parent->name} > {$category_name}";
				$category_name = self::build_category_name( $category_name, $category_parent );
			}
		}

		return $category_name;
	}

	/**
	 * @param $args
	 * @param bool $multiple
	 *
	 * @return mixed|string
	 */
	public static function dropdown_categories( $args, $multiple = true ) {
		add_filter( 'list_cats', array( __CLASS__, 'build_category_name' ), 10, 2 );
		$output = wp_dropdown_categories(
			array_merge( array(
					'hide_empty'    => 0,
					'hide_if_empty' => false,
					'taxonomy'      => 'product_cat',
					'name'          => '',
					'orderby'       => 'name',
					'hierarchical'  => true,
					'echo'          => '',
				)
				, $args ) );
		remove_filter( 'list_cats', array( __CLASS__, 'build_category_name' ), 10 );
		if ( $multiple ) {
			$output = str_replace( '<select', '<select multiple', $output );
		}

		return $output;
	}

	/**
	 * @return mixed|void
	 */
	public static function get_url() {
		return apply_filters( 'vi_wad_import_list_page_url', add_query_arg( array( 'page' => 'woocommerce-alidropship-import-list' ), admin_url( 'admin.php' ) ) );
	}

	private static function check_ajax_referer() {
		VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::check_ajax_referer( 'woocommerce-alidropship-import-list' );
	}

	/**
	 * @param $product_id
	 * @param $attributes
	 */
	private static function attributes_tab_html( $product_id, $attributes ) {
		$position = 1;
		foreach ( $attributes as $attributes_key => $attribute ) {
			$attribute_name = isset( $attribute['name'] ) ? $attribute['name'] : VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_attribute_name_by_slug( $attribute['slug'] );
			?>
            <tr class="<?php echo esc_attr( self::set( 'attributes-attribute-row' ) ) ?>">
                <td><?php echo esc_html( $position ) ?></td>
                <td><input type="text"
                           class="<?php echo esc_attr( self::set( 'attributes-attribute-name' ) ) ?>"
                           value="<?php echo esc_attr( $attribute_name ) ?>"
                           data-attribute_name="<?php echo esc_attr( $attribute_name ) ?>"
                           readonly
                           name="<?php echo esc_attr( "vi_wad_product[{$product_id}][attributes][{$attributes_key}][name]" ) ?>">
                </td>
                <td>
                    <span class="<?php echo esc_attr( self::set( 'attributes-attribute-slug' ) ) ?>"
                          data-attribute_slug="<?php echo esc_attr( $attribute['slug'] ) ?>"><?php echo esc_html( $attribute['slug'] ) ?></span>
                </td>
                <td>
                    <div class="<?php echo esc_attr( self::set( 'attributes-attribute-values' ) ) ?>">
						<?php
						foreach ( $attribute['values'] as $values_k => $values_v ) {
							?>
                            <input type="text"
                                   class="<?php echo esc_attr( self::set( 'attributes-attribute-value' ) ) ?>"
                                   value="<?php echo esc_attr( $values_v ) ?>"
                                   data-attribute_value="<?php echo esc_attr( $values_v ) ?>"
                                   readonly
                                   name="<?php echo esc_attr( "vi_wad_product[{$product_id}][attributes][{$attributes_key}][values][{$values_k}]" ) ?>">
							<?php
						}
						?>
                    </div>
                </td>
                <td>
                    <span class="vi-ui button mini icon <?php echo esc_attr( self::set( 'attributes-button-edit' ) ) ?>"
                          title="<?php esc_attr_e( 'Edit this attribute', 'woocommerce-alidropship' ) ?>"><i
                                class="icon edit"></i></span>
                    <span class="vi-ui button mini negative icon <?php echo esc_attr( self::set( 'attributes-attribute-remove' ) ) ?>"
                          title="<?php esc_attr_e( 'Remove this attribute', 'woocommerce-alidropship' ) ?>"><i
                                class="icon trash"></i></span>
                    <div class="<?php echo esc_attr( self::set( array(
						'attributes-button-save-cancel',
					) ) ) ?>">
                        <span class="vi-ui button mini green icon <?php echo esc_attr( self::set( array(
	                        'attributes-button-save',
                        ) ) ) ?>"
                              title="<?php esc_attr_e( 'Save', 'woocommerce-alidropship' ) ?>"><i
                                    class="icon save"></i></span>
                        <span class="vi-ui button mini icon <?php echo esc_attr( self::set( array(
							'attributes-button-cancel',
						) ) ) ?>"
                              title="<?php esc_attr_e( 'Cancel', 'woocommerce-alidropship' ) ?>"><i
                                    class="icon cancel"></i></span>
                    </div>
                </td>
            </tr>
			<?php
			$position ++;
		}
	}
}