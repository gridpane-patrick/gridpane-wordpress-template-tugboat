<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migrate AliExpress products which were previously imported by other platform/plugins
 *
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Migrate_Products
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Migrate_Products {
	protected static $settings;

	public function __construct() {
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 16 );
		add_action( 'wp_ajax_vi_wad_migrate_products', array( $this, 'migrate_products' ) );
	}

	private static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_ALIDROPSHIP_DATA::set( $name, $set_name );
	}

	/**
	 * @param $args
	 * @param $product_categories
	 * @param $exclude_categories
	 */
	private function build_query( &$args, $product_categories, $exclude_categories ) {
		if ( count( $product_categories ) || count( $exclude_categories ) ) {
			$args['tax_query'] = array(
				'relation' => 'AND'
			);
			if ( count( $product_categories ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'ID',
					'terms'    => $product_categories,
					'operator' => 'IN'
				);
			}
			if ( count( $exclude_categories ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'ID',
					'terms'    => $exclude_categories,
					'operator' => 'NOT IN'
				);
			}
		}
	}

	/**
	 * Ajax handler for migrating products
	 */
	public function migrate_products() {
		check_ajax_referer( 'woocommerce_alidropship_admin_ajax', '_vi_wad_ajax_nonce' );
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-migrate-products' ) ) ) {
			wp_die();
		}
		vi_wad_set_time_limit();
		$step                  = isset( $_POST['step'] ) ? sanitize_text_field( $_POST['step'] ) : '';
		$product_source        = isset( $_POST['product_source'] ) ? sanitize_text_field( $_POST['product_source'] ) : '';
		$product_source_meta   = isset( $_POST['product_source_meta'] ) ? sanitize_text_field( $_POST['product_source_meta'] ) : '';
		$attribute_source_meta = isset( $_POST['attribute_meta'] ) ? sanitize_text_field( $_POST['attribute_meta'] ) : '';
		$product_categories    = isset( $_POST['product_categories'] ) ? wc_clean( $_POST['product_categories'] ) : array();
		$exclude_categories    = isset( $_POST['exclude_categories'] ) ? wc_clean( $_POST['exclude_categories'] ) : array();
		$response              = array(
			'status'  => 'error',
			'message' => '',
			'stop'    => '',
		);
		$meta_key              = '';
		$attribute_meta        = '';
		switch ( $product_source ) {
			case 'ali2woo':
				$meta_key       = '_a2w_external_id';
				$attribute_meta = '_aliexpress_sku_props';
				break;
			case 'alidropship_woo':
				$meta_key       = '_sku';
				$attribute_meta = 'adswSKU';
				break;
			default:
				if ( ! in_array( $product_source_meta, array(
					'_vi_wad_aliexpress_product_id',
					'_vi_wad_migrate_from_id',
//					'_sku',
					'_visibility',
					'_tax_status',
					'total_sales',
					'_tax_class',
					'_manage_stock',
					'_backorders',
					'_sold_individually',
					'_virtual',
					'_downloadable',
					'_download_limit',
					'_download_expiry',
					'_stock',
					'_stock_status',
					'_wc_average_rating',
					'_wc_review_count',
					'_product_attributes',
					'_product_version',
					'_edit_lock',
					'_edit_last',
					'wc_productdata_options',
					'_default_attributes',
					'_product_image_gallery',
					'_price',
					'_sale_price',
					'_regular_price',
					'_thumbnail_id',
				) )
				) {
					$meta_key       = $product_source_meta;
					$attribute_meta = $attribute_source_meta;
				}
		}
		if ( ! $meta_key ) {
			$response['message'] = sprintf( __( 'This meta key cannot be the one that another plugin can use to store AliExpress product ID. Please consult that plugin\'s author or contact %s for help.', 'woocommerce-alidropship' ), '<a href="mailto:support@villatheme">support@villatheme</a>' );
			wp_send_json( $response );
		}
		if ( $step === 'migrate' ) {
			$access_token     = self::$settings->get_params( 'access_token' );
			$use_access_token = $access_token && ! get_transient( 'vi_wad_migrate_using_direct_request' );
			$page             = isset( $_POST['page'] ) ? absint( sanitize_text_field( $_POST['page'] ) ) : 1;
			$max_page         = isset( $_POST['max_page'] ) ? absint( sanitize_text_field( $_POST['max_page'] ) ) : 1;
			$per_page         = $use_access_token ? 20 : 1;
			$args             = array(
				'post_type'      => 'product',
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => $meta_key,
						'compare' => 'exists',
					),
					array(
						'key'     => $meta_key,
						'value'   => '',
						'compare' => '!=',
					),
					array(
						'key'     => '_vi_wad_aliexpress_product_id',
						'compare' => 'not exists',
					),
					array(
						'key'     => '_vi_wad_migrate_from_id',
						'compare' => 'not exists',
					),
				),
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			);
			self::build_query( $args, $product_categories, $exclude_categories );
			$the_query = new WP_Query( $args );

			if ( $the_query->have_posts() ) {
				$woo_ids = $the_query->posts;
				if ( $use_access_token && $the_query->post_count > 1 ) {
					vi_wad_set_time_limit();
					$public_params = array(
						'app_key'     => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
						'format'      => 'json',
						'method'      => 'aliexpress.ds.product.get',
						'partner_id'  => 'apidoc',
						'session'     => $access_token,
						'sign_method' => 'md5',
						'timestamp'   => '',
						'v'           => '2.0',
					);
					$params        = array();
					foreach ( $woo_ids as $woo_id ) {
						$params[] = array(
							'product_id'      => get_post_meta( $woo_id, $meta_key, true ),
							'target_currency' => 'USD',
						);
					}
					if ( count( $params ) ) {
						$get_sign = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_get_sign( array(
							'download_key' => self::$settings->get_params( 'key' ),
							'access_token' => $access_token,
							'app_key'      => VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY,
							'site_url'     => VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_domain_name(),
							'data'         => json_encode( $params ),
						), 'get_product_v2' );
						if ( $get_sign['status'] === 'success' ) {
							$public_params['sign']      = $get_sign['data']['data'];
							$public_params['timestamp'] = date( 'Y-m-d H:i:s', $get_sign['data']['timestamp'] );
							$url                        = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::ali_ds_get_url( true, ! self::$settings->get_params( 'update_product_http_only' ) );
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
								if ( count( $responses ) === count( $params ) ) {
									foreach ( $responses as $key => $response_data ) {
										$woo_id   = $woo_ids[ $key ];
										$data     = vi_wad_json_decode( $response_data );
										$res_key  = str_replace( '.', '_', $public_params['method'] ) . '_response';
										$view_url = admin_url( "post.php?post={$woo_id}&action=edit" );
										$log      = "Product <a href='{$view_url}' target='_blank'>#{$woo_id}</a>: ";
										if ( isset( $data[ $res_key ] ) ) {
											$product_sku = $params[ $key ]['product_id'];
											if ( isset( $data[ $res_key ]['rsp_code'] ) && $data[ $res_key ]['rsp_code'] == 200 ) {
												self::handle_product( $product_sku, $data[ $res_key ]['result'], $woo_id, $attribute_meta );
											} else {
												if ( isset( $data[ $res_key ]['rsp_code'] ) ) {
													if ( $data[ $res_key ]['rsp_code'] == 404 ) {
														self::handle_product( $product_sku, '', $woo_id, $attribute_meta );
													} elseif ( $data[ $res_key ]['rsp_code'] == 10004000 ) {
														self::log( "{$log}This product is no longer available" );
													}
												} else {
													self::log( "{$log}Invalid data" );
												}
											}
										} elseif ( isset( $data['error_response'] ) ) {
											self::log( "{$log}{$data['error_response']['code']} - {$data['error_response']['msg']}" );
										}
									}
								} else {
									self::log( esc_html__( 'Product data not matched', 'woocommerce-alidropship' ) );
								}
							} else {
								self::log( "{$request->get_error_message()}, " . json_encode( $woo_ids ) );
							}
						} else {
							set_transient( 'vi_wad_migrate_using_direct_request', time(), DAY_IN_SECONDS );
							$response['message'] = $get_sign['data'];
							$response['status']  = 'retry';
							self::log( "{$get_sign['code']} - {$get_sign['data']}" );
							wp_send_json( $response );
						}
					}
				} else {
					foreach ( $woo_ids as $woo_id ) {
						self::handle_product( get_post_meta( $woo_id, $meta_key, true ), '', $woo_id, $attribute_meta );
					}
				}
			}
			if ( $page === 1 ) {
				$max_page = $the_query->max_num_pages;
			}
			$response['max_page'] = $max_page;
			if ( $max_page > 0 ) {
				$response['percent'] = intval( 100 * ( $page / $max_page ) );
				if ( $page < $max_page ) {
					$page ++;
				} else {
					$response['message'] = esc_html__( 'Complete. Please go to Import list to finish migrating', 'woocommerce-alidropship' );
				}
			} else {
				$response['percent'] = 100;
			}
			$response['page']   = $page;
			$response['status'] = 'success';
			wp_send_json( $response );
		} else {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => $meta_key,
						'compare' => 'exists',
					),
					array(
						'key'     => $meta_key,
						'value'   => '',
						'compare' => '!=',
					),
					array(
						'key'     => '_vi_wad_aliexpress_product_id',
						'compare' => 'not exists',
					),
					array(
						'key'     => '_vi_wad_migrate_from_id',
						'compare' => 'not exists',
					),
				),
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			);
			self::build_query( $args, $product_categories, $exclude_categories );
			$the_query                = new WP_Query( $args );
			$response['availability'] = $the_query->found_posts;
			wp_reset_postdata();
			$args     ['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'     => $meta_key,
					'compare' => 'exists',
				),
				array(
					'key'     => $meta_key,
					'value'   => '',
					'compare' => '!=',
				),
				array(
					'key'     => '_vi_wad_aliexpress_product_id',
					'compare' => 'not exists',
				),
				array(
					'key'     => '_vi_wad_migrate_from_id',
					'compare' => 'exists',
				),
			);
			$the_query               = new WP_Query( $args );
			$response['in_progress'] = $the_query->found_posts;
			wp_reset_postdata();
			$args     ['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'     => $meta_key,
					'compare' => 'exists',
				),
				array(
					'key'     => $meta_key,
					'value'   => '',
					'compare' => '!=',
				),
				array(
					'key'     => '_vi_wad_aliexpress_product_id',
					'compare' => 'exists',
				),
				array(
					'key'     => '_vi_wad_migrate_from_id',
					'compare' => 'exists',
				),
			);
			$the_query               = new WP_Query( $args );
			$response['migrated']    = $the_query->found_posts;
			wp_reset_postdata();
			if ( $response['availability'] > 0 || $response['in_progress'] > 0 || $response['migrated'] > 0 ) {
				$response['status'] = 'success';
			} else {
				$response['message'] = esc_html__( 'No products found', 'woocommerce-alidropship' );
			}
		}

		wp_send_json( $response );
	}

	/**
     * Create products, add to import list or link immediately
     *
	 * @param $product_sku
	 * @param $product_data
	 * @param $woo_id
	 * @param string $attribute_meta
	 */
	private static function handle_product( $product_sku, $product_data, $woo_id, $attribute_meta = '' ) {
		$view_url  = admin_url( "post.php?post={$woo_id}&action=edit" );
		$log       = "Product <a href='{$view_url}' target='_blank'>#{$woo_id}</a>: ";
		$log_level = WC_Log_Levels::INFO;
		$get_data  = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_data( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $product_sku ), array(
			'cookies' => get_option( 'vi_woo_alidropship_cookies_for_importing', array() )
		), $product_data );
		if ( $get_data['status'] === 'success' ) {
			$data = $get_data['data'];
			if ( count( $data ) ) {
				$post_id = self::$settings->create_product( $data, array(
					'time'          => time() - HOUR_IN_SECONDS,
					'country'       => get_option( 'woocommerce_default_country', '' ),
					'company'       => '',
					'company_name'  => '',
					'freight'       => json_encode( array() ),
					'shipping_cost' => null,
					'delivery_time' => '',
				) );
				if ( $post_id && ! is_wp_error( $post_id ) ) {
					update_post_meta( $woo_id, '_vi_wad_migrate_from_id', $product_sku );
					update_post_meta( $post_id, '_vi_wad_map_woo_id', $woo_id );
					$variations = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::get_product_variations( $post_id );
					$is_simple  = true;
					if ( ! empty( $variations[0]['skuAttr'] ) ) {
						$is_simple = false;
					}
					$import = false;
					if ( self::$settings->get_params( 'migration_link_only' ) && $attribute_meta ) {
						$product = wc_get_product( $woo_id );
						if ( $product->is_type( 'variable' ) ) {
							$children = $product->get_children();
							if ( ! $is_simple ) {
								$mapped_variations = array();
								foreach ( $children as $child ) {
									$sku_props = get_post_meta( $child, $attribute_meta, true );
									if ( $sku_props ) {
										$search = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::search_sku_attr( $sku_props, array_column( $variations, 'skuAttr' ) );
										if ( $search !== false ) {
											$mapped_variations[ $child ] = $search;
										}
									}
								}
								if ( count( $mapped_variations ) === count( $children ) ) {
									foreach ( $mapped_variations as $variation_id => $variation_key ) {
										VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::handle_aliexpress_variation_meta( $variation_id, $variations[ $variation_key ] );
									}
									$import = true;
								}
							} else {

							}
						} else {
							if ( ! $is_simple ) {
								$sku_props = get_post_meta( $woo_id, $attribute_meta, true );
								if ( $sku_props ) {
									$search = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::search_sku_attr( $sku_props, array_column( $variations, 'skuAttr' ) );
									if ( $search !== false ) {
										$import = true;
										VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::handle_aliexpress_variation_meta( $woo_id, $variations[ $search ] );
									}
								}
							} else {
								$import = true;
							}
						}
					}
					if ( $import ) {
						wp_update_post( array(
							'ID'          => $post_id,
							'post_status' => 'publish'
						) );
						update_post_meta( $post_id, '_vi_wad_woo_id', $woo_id );
						update_post_meta( $woo_id, '_vi_wad_aliexpress_product_id', $product_sku );
						$log .= esc_html__( 'Successfully migrated', 'woocommerce-alidropship' );
					} else {
						$log .= esc_html__( 'Added to Import list', 'woocommerce-alidropship' );
					}
				} else {
					$log       .= $post_id->get_error_message();
					$log_level = WC_Log_Levels::NOTICE;
				}
			} else {
				$log       .= esc_html__( 'Cannot get product data', 'woocommerce-alidropship' );
				$log_level = WC_Log_Levels::NOTICE;
			}
		} else {
			$log       .= $get_data['message'];
			$log_level = WC_Log_Levels::NOTICE;
		}
		self::log( $log, $log_level );
	}

	public function admin_menu() {
		$menu_slug = 'woocommerce-alidropship-migrate-products';
		add_submenu_page( 'woocommerce-alidropship', esc_html__( 'Migrate Products', 'woocommerce-alidropship' ), esc_html__( 'Migrate Products', 'woocommerce-alidropship' ), apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', $menu_slug ), $menu_slug, array(
			$this,
			'page_callback'
		) );
	}

	public function page_callback() {
		$log_page = admin_url( 'admin.php?page=woocommerce-alidropship-logs' );
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Migrate Products', 'woocommerce-alidropship' ) ?></h2>
            <div class="vi-ui message positive">
                <ul class="list">
                    <li><?php esc_html_e( 'Scan for AliExpress products imported by other plugins', 'woocommerce-alidropship' ); ?></li>
                    <li><?php esc_html_e( 'Successfully migrated products will be added to Import list with "Link existing Woo product" field automatically and correctly selected', 'woocommerce-alidropship' ); ?></li>
                    <li><?php printf( esc_html__( 'For details of migration, please go to %s', 'woocommerce-alidropship' ), '<a target="_blank" href="' . esc_url( $log_page ) . '">' . esc_html( $log_page ) . '</a>' ); ?></li>
                </ul>
            </div>
            <div class="vi-ui segment">
                <div class="vi-ui steps fluid">
                    <div class="step active <?php echo esc_attr( self::set( array( 'migrate-product-step-1' ) ) ) ?>">
                        <i class="settings icon"></i>
                        <div class="content">
                            <div class="title"><?php esc_html_e( 'Select options', 'woocommerce-alidropship' ); ?></div>
                        </div>
                    </div>
                    <div class="step disabled <?php echo esc_attr( self::set( array( 'migrate-product-step-2' ) ) ) ?>">
                        <i class="search icon"></i>
                        <div class="content">
                            <div class="title"><?php esc_html_e( 'Scan results', 'woocommerce-alidropship' ); ?></div>
                        </div>
                    </div>
                    <div class="step disabled <?php echo esc_attr( self::set( array( 'migrate-product-step-3' ) ) ) ?>">
                        <i class="refresh icon"></i>
                        <div class="content">
                            <div class="title"><?php esc_html_e( 'Migrate', 'woocommerce-alidropship' ); ?></div>
                        </div>
                    </div>
                </div>
                <div class="vi-ui form">
                    <div class="field <?php echo esc_attr( self::set( array(
						'migrate-product-step-content-1',
						'migrate-product-step-content'
					) ) ) ?>">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="<?php echo esc_attr( self::set( 'product-source' ) ) ?>"><?php esc_html_e( 'Product source', 'woocommerce-alidropship' ) ?></label>
                                </th>
                                <td>
                                    <select name="<?php echo esc_attr( self::set( 'product-source' ) ) ?>"
                                            id="<?php echo esc_attr( self::set( 'product-source' ) ) ?>"
                                            class="<?php echo esc_attr( self::set( 'product-source' ) ) ?> vi-ui dropdown">
                                        <option value="ali2woo"><?php esc_html_e( 'Ali2Woo', 'woocommerce-alidropship' ) ?></option>
                                        <option value="alidropship_woo"><?php esc_html_e( 'AliDropship Woo', 'woocommerce-alidropship' ) ?></option>
                                        <option value="other"><?php esc_html_e( 'Other', 'woocommerce-alidropship' ) ?></option>
                                    </select>
                                    <p class="vi-ui fluid input">
                                        <input type="text"
                                               name="<?php echo esc_attr( self::set( 'product-source-meta' ) ) ?>"
                                               id="<?php echo esc_attr( self::set( 'product-source-meta' ) ) ?>"
                                               placeholder="<?php esc_attr_e( 'Please enter the post meta key that the other plugin uses to store the AliExpress product ID', 'woocommerce-alidropship' ) ?>"
                                               value="">
                                    </p>
                                    <p><?php esc_html_e( 'If you choose to migrate from AliDropship Woo plugin, our plugin will scan AliExpress product IDs by _sku meta key so the scan results may not be as expected. However, they will be validated while migrating so if SKU of a product is an actual AliExpress product ID, it will be migrated properly.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php echo esc_attr( self::set( 'product-categories' ) ) ?>"><?php esc_html_e( 'Include categories', 'woocommerce-alidropship' ); ?></label>
                                </th>
                                <td>
                                    <select name="<?php echo esc_attr( self::set( 'product-categories', true ) ) ?>"
                                            class="<?php echo esc_attr( self::set( 'product-categories' ) ) ?> search-category"
                                            id="<?php echo esc_attr( self::set( 'product-categories' ) ) ?>"
                                            multiple="multiple">
                                    </select>
                                    <p><?php esc_html_e( 'Only scan products that belong to selected categories.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="<?php echo esc_attr( self::set( 'exclude-categories' ) ) ?>"><?php esc_html_e( 'Exclude categories', 'woocommerce-alidropship' ); ?></label>
                                </th>
                                <td>
                                    <select name="<?php echo esc_attr( self::set( 'exclude-categories', true ) ) ?>"
                                            class="<?php echo esc_attr( self::set( 'exclude-categories' ) ) ?> search-category"
                                            id="<?php echo esc_attr( self::set( 'exclude-categories' ) ) ?>"
                                            multiple="multiple">
                                    </select>
                                    <p><?php esc_html_e( 'Do not scan products that belong to selected categories.', 'woocommerce-alidropship' ) ?></p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p class="<?php echo esc_attr( self::set( array( 'button-scan-container' ) ) ) ?>">
                            <span class="vi-ui labeled icon button primary <?php echo esc_attr( self::set( array( 'button-scan' ) ) ) ?>"><i
                                        class="icon search"></i><?php esc_html_e( 'Scan', 'woocommerce-alidropship' ) ?></span>
                        </p>
                    </div>
                    <div class="field <?php echo esc_attr( self::set( array(
						'migrate-product-step-content',
						'migrate-product-step-content-2',
						'hidden'
					) ) ) ?>">
                        <table class="vi-ui celled table">
                            <thead>
                            <tr>
                                <th>
                                    <label></label>
                                </th>
                                <th>
                                    <label><?php esc_html_e( 'Scan results', 'woocommerce-alidropship' ) ?></label>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <label><?php esc_html_e( 'In progress', 'woocommerce-alidropship' ) ?></label>
                                </td>
                                <td>
                                    <span class="<?php echo esc_attr( self::set( array( 'migrate-in-progress' ) ) ) ?>"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label><?php esc_html_e( 'Migrated', 'woocommerce-alidropship' ) ?></label>
                                </td>
                                <td>
                                    <span class="<?php echo esc_attr( self::set( array( 'migrate-migrated' ) ) ) ?>"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label><?php esc_html_e( 'Availability', 'woocommerce-alidropship' ) ?></label>
                                </td>
                                <td>
                                    <span class="<?php echo esc_attr( self::set( array( 'migrate-availability' ) ) ) ?>"></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p class="<?php echo esc_attr( self::set( array( 'buttons-container' ) ) ) ?>">
                            <span class="vi-ui labeled icon button <?php echo esc_attr( self::set( array( 'button-back' ) ) ) ?>"><i
                                        class="icon step undo"></i><?php esc_html_e( 'Back', 'woocommerce-alidropship' ) ?></span>
                            <span class="vi-ui labeled icon button primary <?php echo esc_attr( self::set( array( 'button-migrate' ) ) ) ?>"><i
                                        class="refresh icon"></i><?php esc_html_e( 'Migrate', 'woocommerce-alidropship' ) ?></span>
                        </p>
                    </div>
                    <div class="field <?php echo esc_attr( self::set( array(
						'migrate-product-step-content',
						'migrate-product-step-content-3',
						'hidden'
					) ) ) ?>">
                        <div class="vi-ui indicating progress standard active <?php echo esc_attr( self::set( 'migrate-progress' ) ) ?>">
                            <div class="label"></div>
                            <div class="bar">
                                <div class="progress"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	public function admin_enqueue_scripts() {
		global $pagenow;
		$page = isset( $_REQUEST['page'] ) ? wp_unslash( sanitize_text_field( $_REQUEST['page'] ) ) : '';
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-alidropship-migrate-products' ) {
			VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::enqueue_3rd_library( array( 'data-table', 'accordion' ), true );
			wp_enqueue_style( 'woocommerce-alidropship-migrate-products', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'migrate-products.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_enqueue_script( 'woocommerce-alidropship-migrate-products', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'migrate-products.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_localize_script( 'woocommerce-alidropship-migrate-products', 'vi_wad_params_admin_migrate_products', array(
				'url'                       => admin_url( 'admin-ajax.php' ),
				'_vi_wad_ajax_nonce'        => VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::create_ajax_nonce(),
				'i18n_error'                => esc_html__( 'An error occurs, please try again later', 'woocommerce-alidropship' ),
				'i18n_error_product_source' => esc_html__( 'Please enter the post meta key that the other plugin uses to store AliExpress original product ID', 'woocommerce-alidropship' ),
			) );
		}
	}

	/**
	 * @param $content
	 * @param string $log_level
	 */
	private static function log( $content, $log_level = 'notice' ) {
		VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Log::wc_log( $content, 'migrate-products', $log_level );
	}
}