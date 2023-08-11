<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Imported
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Imported {
	private static $settings;
	private static $product_count;

	public function __construct() {
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		add_action( 'admin_init', array( $this, 'cancel_overriding' ) );
		add_action( 'admin_init', array( $this, 'empty_trash' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 15 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 999999 );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
		add_action( 'admin_head', array( $this, 'menu_product_count' ), 999 );
		add_action( 'wp_ajax_vi_wad_override_product', array( $this, 'override_product' ) );
		add_action( 'wp_ajax_vi_wad_trash_product', array( $this, 'trash' ) );
		add_action( 'wp_ajax_vi_wad_restore_product', array( $this, 'restore' ) );
		add_action( 'wp_ajax_vi_wad_delete_product', array( $this, 'delete' ) );
		add_action( 'wp_ajax_vi_wad_dismiss_product_notice', array( $this, 'dismiss_product_notice' ) );
		add_action( 'wp_ajax_vi_wad_select_shipping_imported', array(
			$this,
			'select_shipping'
		) );
	}

	/**
	 *
	 */
	public function empty_trash() {
		global $wpdb;
		$page = isset( $_GET['page'] ) ? wp_unslash( $_GET['page'] ) : '';
		if ( ! empty( $_GET['vi_wad_empty_trash'] ) && $page === 'woocommerce-alidropship-imported-list' ) {
			$nonce = isset( $_GET['_wpnonce'] ) ? wp_unslash( $_GET['_wpnonce'] ) : '';
			if ( wp_verify_nonce( $nonce ) ) {
				$posts = "{$wpdb->prefix}posts";
				$wpdb->query( apply_filters( 'vi_wad_empty_trash_sql', "DELETE from {$posts} WHERE {$posts}.post_type='vi_wad_draft_product' AND {$posts}.post_status='trash'" ) );
				wp_safe_redirect( admin_url( "admin.php?page={$page}" ) );
				exit();
			}
		}
	}

	/**
	 * Allow changing selected shipping company for imported products because this is also used when syncing products
	 */
	public function select_shipping() {
		self::check_ajax_referer();
		$key                  = isset( $_POST['product_index'] ) ? absint( sanitize_text_field( $_POST['product_index'] ) ) : '';
		$product_id           = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$country              = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '';
		$company              = isset( $_POST['company'] ) ? sanitize_text_field( $_POST['company'] ) : '';
		$shipping_option_html = '';
		if ( self::$settings->get_params( 'show_shipping_option' ) ) {
			$shipping_info = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::get_shipping_info( $product_id, $country, $company, 3600 );
			if ( $shipping_info['shipping_cost'] !== '' ) {
				$update = get_post_meta( $product_id, '_vi_wad_update_product_notice', true );
				if ( isset( $update['shipping_removed'] ) && $update['shipping_removed'] ) {
					$update['shipping_removed'] = false;
					update_post_meta( $product_id, '_vi_wad_update_product_notice', $update );
				}
			}
			ob_start();
			VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::shipping_option_html( $shipping_info, $key, $product_id, 'simple' );
			$shipping_option_html = ob_get_clean();
		} else {
			update_post_meta( $product_id, '_vi_wad_shipping_info', array(
				'time'          => 0,
				'country'       => $country,
				'company'       => '',
				'company_name'  => '',
				'freight'       => json_encode( array() ),
				'shipping_cost' => '',
				'delivery_time' => '',
			) );
		}
		wp_send_json(
			array(
				'status' => 'success',
				'data'   => $shipping_option_html
			)
		);
	}

	/**
	 * Remove notices generated after syncing products
	 */
	public function dismiss_product_notice() {
		self::check_ajax_referer();
		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		if ( $product_id ) {
			$product_notice = get_post_meta( $product_id, '_vi_wad_update_product_notice', true );
			if ( $product_notice ) {
				$product_notice['hide']          = time();
				$product_notice['not_available'] = array();
				$product_notice['out_of_stock']  = array();
				$product_notice['price_changes'] = array();
				update_post_meta( $product_id, '_vi_wad_update_product_notice', $product_notice );
				wp_send_json_success();
			}
		}
		wp_send_json_error();
	}

	/**
	 * Cancel overriding button handler
	 */
	public function cancel_overriding() {
		$page = isset( $_REQUEST['page'] ) ? wp_unslash( $_REQUEST['page'] ) : '';
		if ( $page === 'woocommerce-alidropship-imported-list' ) {
			$overridden_product = isset( $_REQUEST['overridden_product'] ) ? wp_unslash( $_REQUEST['overridden_product'] ) : '';
			$cancel_overriding  = isset( $_REQUEST['cancel_overriding'] ) ? wp_unslash( $_REQUEST['cancel_overriding'] ) : '';
			$_wpnonce           = isset( $_REQUEST['_wpnonce'] ) ? wp_unslash( $_REQUEST['_wpnonce'] ) : '';
			if ( $overridden_product && $cancel_overriding && wp_verify_nonce( $_wpnonce, 'cancel_overriding_nonce' ) ) {
				$product = get_post( $cancel_overriding );
				if ( $product && $product->post_status === 'override' && $product->post_parent == $overridden_product ) {
					wp_update_post( array(
						'ID'          => $cancel_overriding,
						'post_parent' => '',
						'post_status' => 'draft',
					) );
				}
				wp_safe_redirect( remove_query_arg( array( 'cancel_overriding', '_wpnonce', 'overridden_product' ) ) );
				exit();
			}
		}
	}

	/**
	 * Delete an imported product
	 * Opt to also delete the connected Woo product
	 *
	 */
	public function delete() {
		self::check_ajax_referer();
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-imported-list' ) ) ) {
			wp_die();
		}
		vi_wad_set_time_limit();
		$product_id         = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$delete_woo_product = isset( $_POST['delete_woo_product'] ) ? sanitize_text_field( $_POST['delete_woo_product'] ) : '';
		if ( $delete_woo_product != self::$settings->get_params( 'delete_woo_product' ) ) {
			$args                       = self::$settings->get_params();
			$args['delete_woo_product'] = $delete_woo_product;
			update_option( 'wooaliexpressdropship_params', $args );
		}
		$response = array(
			'status'  => 'success',
			'message' => '',
		);
		if ( $product_id ) {
			$woo_product_id = get_post_meta( $product_id, '_vi_wad_woo_id', true );
			if ( get_post( $product_id ) ) {
				$delete = wp_delete_post( $product_id, true );
				if ( false === $delete ) {
					$response['status']  = 'error';
					$response['message'] = esc_html__( 'Can not delete product', 'woocommerce-alidropship' );
				}
			}
			if ( $woo_product_id && get_post( $woo_product_id ) && current_user_can( 'delete_product', $woo_product_id ) ) {
				delete_post_meta( $woo_product_id, '_vi_wad_aliexpress_product_id' );
				delete_post_meta( $woo_product_id, '_vi_wad_aliexpress_variation_attr' );
				delete_post_meta( $woo_product_id, '_vi_wad_aliexpress_variation_id' );
				delete_post_meta( $woo_product_id, '_vi_wad_aliexpress_variation_ship_from' );
				delete_post_meta( $woo_product_id, '_vi_wad_migrate_from_id' );
				if ( 1 == $delete_woo_product ) {
					$delete = wp_delete_post( $woo_product_id, true );
					if ( false === $delete ) {
						$response['status']  = 'error';
						$response['message'] = esc_html__( 'Can not delete product', 'woocommerce-alidropship' );
					}
				}
			}
		}
		wp_send_json( $response );
	}

	/**
	 * Restore a product from trash
	 */
	public function restore() {
		self::check_ajax_referer();
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-imported-list' ) ) ) {
			wp_die();
		}
		vi_wad_set_time_limit();
		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$response   = array(
			'status'  => 'success',
			'message' => '',
		);
		if ( $product_id ) {
			$post = get_post( $product_id );
			wp_publish_post( $post );
			$woo_id = get_post_meta( $product_id, '_vi_wad_woo_id', true );
			wp_untrash_post( $woo_id );
		}
		wp_send_json( $response );
	}

	/**
	 * Trash a product, available when the connected Woo product no longer exists
	 */
	public function trash() {
		self::check_ajax_referer();
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-imported-list' ) ) ) {
			wp_die();
		}
		vi_wad_set_time_limit();
		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$response   = array(
			'status'  => 'success',
			'message' => '',
		);
		if ( $product_id ) {
			$result = wp_trash_post( $product_id );
			if ( ! $result ) {
				$response['status']  = 'error';
				$response['message'] = esc_html__( 'Can not trash product', 'woocommerce-alidropship' );
			}
		}
		wp_send_json( $response );
	}

	/**
	 * Adds the order processing count to the menu.
	 */
	public function menu_product_count() {
		global $submenu;
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();//show_menu_count may be changed after saving settings
		if ( isset( $submenu['woocommerce-alidropship'] ) && in_array( 'imported', self::$settings->get_params( 'show_menu_count' ) ) ) {
			// Add count if user has access.
			if ( apply_filters( 'woo_aliexpress_dropship_product_count_in_menu', true ) || current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-imported-list' ) ) ) {
				$product_count = self::get_product_count();
				foreach ( $submenu['woocommerce-alidropship'] as $key => $menu_item ) {
					if ( 0 === strpos( $menu_item[0], _x( 'Imported', 'Admin menu name', 'woocommerce-alidropship' ) ) ) {
						$submenu['woocommerce-alidropship'][ $key ][0] .= ' <span class="update-plugins count-' . esc_attr( $product_count->publish ) . '"><span class="' . self::set( 'imported-list-count' ) . '">' . number_format_i18n( $product_count->publish ) . '</span></span>'; // WPCS: override ok.
						break;
					}
				}
			}
		}
	}

	/**
	 * @return stdClass
	 */
	private static function get_product_count() {
		if ( self::$product_count === null ) {
			self::$product_count = wp_count_posts( 'vi_wad_draft_product' );
		}

		return self::$product_count;
	}

	/**
	 * Select a product to override
	 */
	public function override_product() {
		self::check_ajax_referer();
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', 'woocommerce-alidropship-imported-list' ) ) ) {
			wp_die();
		}
		vi_wad_set_time_limit();
		$override_product_url = isset( $_POST['override_product_url'] ) ? sanitize_text_field( stripslashes( $_POST['override_product_url'] ) ) : '';
		$step                 = isset( $_POST['step'] ) ? sanitize_text_field( stripslashes( $_POST['step'] ) ) : '';
		$product_id           = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$response             = array(
			'status'           => 'error',
			'message'          => '',
			'image'            => '',
			'title'            => '',
			'data'             => '',
			'exist_product_id' => '',
		);
		$cookies              = get_option( 'vi_woo_alidropship_cookies_for_importing', array() );
		if ( $cookies ) {
			if ( ! is_array( $cookies ) ) {
				$cookies = array(
					'xman_f' => $cookies,
				);
			}
		} else {
			$cookies = array();
		}
		$product_sku  = '';
		$is_valid_url = false;
		if ( wc_is_valid_url( $override_product_url ) ) {
			$is_valid_url = true;
			preg_match( '/item\/{1,}(.+)\.html/im', $override_product_url, $match );
			if ( $match && ! empty( $match[1] ) ) {
				$product_sku = $match[1];
			}
		} else {
			$product_sku = $override_product_url;
		}

		if ( $product_sku ) {
			$exist_product_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_aliexpress_id( $product_sku, array( 'draft' ) );
			if ( ! $exist_product_id ) {
				$exist_product_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_aliexpress_id( $product_sku, array(
					'publish',
					'override'
				) );
			}
			if ( $step === 'check' ) {
				if ( $exist_product_id ) {
					$exist_product                = get_post( $exist_product_id );
					$response['exist_product_id'] = $exist_product_id;
					$response['title']            = $exist_product->post_title;
					$gallery                      = get_post_meta( $exist_product_id, '_vi_wad_gallery', true );
					$response['image']            = ( is_array( $gallery ) && count( $gallery ) ) ? $gallery[0] : wc_placeholder_img_src();
					if ( $exist_product->post_status === 'draft' ) {
						$response['status'] = 'success';
					} else if ( $exist_product->post_status === 'publish' ) {
						$response['status'] = 'success';
					} else {
						$response['status']  = 'override';
						$response['message'] = esc_html__( 'This product is overriding an other product.', 'woocommerce-alidropship' );
					}
				} else {
					if ( ! $is_valid_url ) {
						$override_product_url = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $product_sku );
					}
					$get_data = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_data( $override_product_url, array(
						'cookies' => $cookies
					) );
					if ( $get_data['status'] === 'success' ) {
						$data = $get_data['data'];
						if ( count( $data ) ) {
							$product_sku = $data['sku'];
							if ( $product_sku ) {
								$response['title']  = $data['name'];
								$response['data']   = base64_encode( json_encode( $data ) );
								$response['image']  = ( is_array( $data['gallery'] ) && count( $data['gallery'] ) ) ? $data['gallery'][0] : wc_placeholder_img_src();
								$response['status'] = 'success';
							} else {
								$response['message'] = esc_html__( 'Not found', 'woocommerce-alidropship' );
							}
						} else {
							$response['message'] = esc_html__( 'Not found', 'woocommerce-alidropship' );
						}
					} else {
						$response['message'] = esc_html__( 'Not found', 'woocommerce-alidropship' );
					}
				}
			} else {
				$post = get_post( $product_id );
				if ( $post ) {
					$get_new = true;
					if ( $exist_product_id ) {
						$override_product = get_post( $exist_product_id );
						if ( $override_product && $override_product->post_status === 'draft' ) {
							$get_new     = false;
							$update_post = wp_update_post( array(
									'ID'          => $exist_product_id,
									'post_status' => 'override',
									'post_parent' => $product_id,
									'edit_date'   => true,
								)
							);
							if ( ! is_wp_error( $update_post ) ) {
								$title                            = $override_product->post_title;
								$response['status']               = 'success';
								$response['button_override_html'] = self::button_override_html( $product_id, $exist_product_id );
								$response['data']                 = '<div class="vi-ui message"><span>' . sprintf( __( 'This product is being overridden by: %s. Please go to %s to complete the process.', 'woocommerce-alidropship' ), '<strong>' . $title . '</strong>', '<a target="_blank" href="' . admin_url( 'admin.php?page=woocommerce-alidropship-import-list&vi_wad_search_id=' . $exist_product_id ) . '">Import list</a>' ) . '</span></div>';
							} else {
								$response['message'] = $update_post->get_error_message();
							}
						}
					}
					if ( $get_new ) {
						$data = isset( $_POST['override_product_data'] ) ? base64_decode( sanitize_text_field( $_POST['override_product_data'] ) ) : '';
						if ( $data ) {
							$data = vi_wad_json_decode( $data );
						}
						if ( ! $data ) {
							if ( ! $is_valid_url ) {
								$override_product_url = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $product_sku );
							}
							$get_data = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_data( $override_product_url, array(
								'cookies' => $cookies
							) );
							if ( $get_data['status'] === 'success' ) {
								$data = $get_data['data'];
							}
						}
						if ( is_array( $data ) && count( $data ) ) {
							$post_id = self::$settings->create_product( $data, get_post_meta( $product_id, '_vi_wad_shipping_info', true ), array(
								'post_status' => 'override',
								'post_parent' => $product_id
							) );
							if ( ! is_wp_error( $post_id ) ) {
								$title                            = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
								$response['status']               = 'success';
								$response['button_override_html'] = self::button_override_html( $product_id, $post_id );
								$response['data']                 = '<div class="vi-ui message"><span>' . sprintf( __( 'This product is being overridden by: %s. Please go to %s to complete the process.', 'woocommerce-alidropship' ), '<strong>' . $title . '</strong>', '<a target="_blank" href="' . admin_url( 'admin.php?page=woocommerce-alidropship-import-list&vi_wad_search_id=' . $post_id ) . '">Import list</a>' ) . '</span></div>';
							} else {
								$response['message'] = $post_id->get_error_message();
							}
						} else {
							$response['message'] = sprintf( __( 'Can not retrieve product data, please go to <a href="%s" target="_blank">%s</a> to import product to import list then try again.', 'woocommerce-alidropship' ), VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $product_sku ), VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $product_sku ) );
						}
					}
				} else {
					$response['message'] = esc_html__( 'Not found', 'woocommerce-alidropship' );
				}
			}

		} else {
			$response['message'] = esc_html__( 'Not found', 'woocommerce-alidropship' );
		}

		wp_send_json( $response );
	}

	/**
	 *
	 */
	public function admin_enqueue_scripts() {
		$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		global $pagenow;
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-alidropship-imported-list' ) {
			self::enqueue_scripts();
		}
	}

	/**
	 *
	 */
	public static function enqueue_scripts() {
		VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::enqueue_3rd_library();
		wp_enqueue_style( 'woocommerce-alidropship-imported-list', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'imported-list.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
		wp_enqueue_script( 'woocommerce-alidropship-imported-list', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'imported-list.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
		wp_localize_script( 'woocommerce-alidropship-imported-list', 'vi_wad_imported_list_params', array(
				'url'                => admin_url( 'admin-ajax.php' ),
				'_vi_wad_ajax_nonce' => VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::create_ajax_nonce(),
				'check'              => esc_html__( 'Check', 'woocommerce-alidropship' ),
				'override'           => esc_html__( 'Override', 'woocommerce-alidropship' ),
			)
		);
		add_action( 'admin_footer', array( __CLASS__, 'delete_product_options' ) );
	}

	/**
	 * Popup for deleting products
	 */
	public static function delete_product_options() {
		?>
        <div class="<?php echo esc_attr( self::set( array(
			'delete-product-options-container',
			'hidden'
		) ) ) ?>">
            <div class="<?php echo esc_attr( self::set( 'overlay' ) ) ?>"></div>
            <div class="<?php echo esc_attr( self::set( 'delete-product-options-content' ) ) ?>">
                <div class="<?php echo esc_attr( self::set( 'delete-product-options-content-header' ) ) ?>">
                    <h2 class="<?php echo esc_attr( self::set( array(
						'delete-product-options-content-header-delete',
						'hidden'
					) ) ) ?>"><?php esc_html_e( 'Delete: ', 'woocommerce-alidropship' ) ?><span
                                class="<?php echo esc_attr( self::set( 'delete-product-options-product-title' ) ) ?>"></span>
                    </h2>
                    <span class="<?php echo esc_attr( self::set( 'delete-product-options-close' ) ) ?>"></span>
                    <h2 class="<?php echo esc_attr( self::set( array(
						'delete-product-options-content-header-override',
						'hidden'
					) ) ) ?>"><?php esc_html_e( 'Override: ', 'woocommerce-alidropship' ) ?><span
                                class="<?php echo esc_attr( self::set( 'delete-product-options-product-title' ) ) ?>"></span>
                    </h2>
                </div>
                <div class="<?php echo esc_attr( self::set( 'delete-product-options-content-body' ) ) ?>">
                    <div class="<?php echo esc_attr( self::set( 'delete-product-options-content-body-row' ) ) ?>">
                        <div class="<?php echo esc_attr( self::set( array(
							'delete-product-options-delete-woo-product-wrap',
							'hidden'
						) ) ) ?>">
                            <input type="checkbox" <?php checked( self::$settings->get_params( 'delete_woo_product' ), 1 ) ?>
                                   value="1"
                                   id="<?php echo esc_attr( self::set( 'delete-product-options-delete-woo-product' ) ) ?>"
                                   class="<?php echo esc_attr( self::set( 'delete-product-options-delete-woo-product' ) ) ?>">
                            <label for="<?php echo esc_attr( self::set( 'delete-product-options-delete-woo-product' ) ) ?>"><?php esc_html_e( 'Also delete product from your WooCommerce store.', 'woocommerce-alidropship' ) ?></label>
                        </div>
                        <div class="<?php echo esc_attr( self::set( array(
							'delete-product-options-override-product-wrap',
							'hidden'
						) ) ) ?>">
                            <label for="<?php echo esc_attr( self::set( 'delete-product-options-override-product' ) ) ?>"><?php esc_html_e( 'AliExpress Product URL/ID:', 'woocommerce-alidropship' ) ?></label>
                            <input type="text"
                                   id="<?php echo esc_attr( self::set( 'delete-product-options-override-product' ) ) ?>"
                                   class="<?php echo esc_attr( self::set( 'delete-product-options-override-product' ) ) ?>">
                            <div class="<?php echo esc_attr( self::set( array(
								'delete-product-options-override-product-new-wrap',
								'hidden'
							) ) ) ?>">
                                <span class="<?php echo esc_attr( self::set( 'delete-product-options-override-product-new-close' ) ) ?>"></span>
                                <div class="<?php echo esc_attr( self::set( 'delete-product-options-override-product-new-image' ) ) ?>">
                                    <img src="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES . 'loading.gif' ) ?>">
                                </div>
                                <div class="<?php echo esc_attr( self::set( 'delete-product-options-override-product-new-title' ) ) ?>"></div>
                            </div>
                        </div>
                        <div class="<?php echo esc_attr( self::set( 'delete-product-options-override-product-message' ) ) ?>"></div>
                    </div>
                </div>
                <div class="<?php echo esc_attr( self::set( 'delete-product-options-content-footer' ) ) ?>">
                    <span class="vi-ui button positive mini <?php echo esc_attr( self::set( array(
	                    'delete-product-options-button-override',
	                    'hidden'
                    ) ) ) ?>"
                          data-product_id="" data-woo_product_id="">
                            <?php esc_html_e( 'Check', 'woocommerce-alidropship' ) ?>
                        </span>
                    <span class="vi-ui button negative mini <?php echo esc_attr( self::set( array(
						'delete-product-options-button-delete',
						'hidden'
					) ) ) ?>"
                          data-product_id="" data-woo_product_id="">
                            <?php esc_html_e( 'Delete', 'woocommerce-alidropship' ) ?>
                        </span>
                    <span class="vi-ui button mini <?php echo esc_attr( self::set( 'delete-product-options-button-cancel' ) ) ?>">
                            <?php esc_html_e( 'Cancel', 'woocommerce-alidropship' ) ?>
                        </span>
                </div>
            </div>
            <div class="<?php echo esc_attr( self::set( 'saving-overlay' ) ) ?>"></div>
        </div>
		<?php
	}

	public function admin_menu() {
		$menu_slug     = 'woocommerce-alidropship-imported-list';
		$imported_list = add_submenu_page( 'woocommerce-alidropship',
			esc_html__( 'Imported Products - AliExpress Dropshipping and Fulfillment for WooCommerce', 'woocommerce-alidropship' ),
			esc_html__( 'Imported', 'woocommerce-alidropship' ), apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_woocommerce', $menu_slug ),
			$menu_slug, array(
				$this,
				'imported_list_callback'
			) );
		add_action( "load-$imported_list", array( $this, 'screen_options_page_imported' ) );
	}

	/**
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public function save_screen_options( $status, $option, $value ) {
		if ( $option === 'vi_wad_imported_per_page' ) {
			return $value;
		}

		return $status;
	}

	/**
	 * All imported products show here
	 */
	public function imported_list_callback() {
		$is_main = true;
		if ( ! current_user_can( 'manage_woocommerce' ) && VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Vendor::enable_vendor_integration() ) {
			$is_main = false;
		}
		self::imported_list_html( $is_main );
	}

	/**
	 * Content of imported page
	 *
	 * @param bool $is_main
	 */
	public static function imported_list_html( $is_main = true ) {
		$user = wp_get_current_user();
		if ( $is_main ) {
			$screen   = get_current_screen();
			$option   = $screen->get_option( 'per_page', 'option' );
			$per_page = get_user_meta( $user->ID, $option, true );
			if ( empty ( $per_page ) || $per_page < 1 ) {
				$per_page = $screen->get_option( 'per_page', 'default' );
			}
		} else {
			$per_page = get_user_meta( $user->ID, 'vi_wad_imported_per_page', true );
			if ( empty ( $per_page ) || $per_page < 1 ) {
				$per_page = 20;
			}
		}
		$is_vendor       = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Vendor::is_ald_vendor_page();
		$import_list_url = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::get_url();
		$paged           = isset( $_GET['paged'] ) ? sanitize_text_field( $_GET['paged'] ) : 1;
		$status          = ! empty( $_GET['post_status'] ) ? sanitize_text_field( $_GET['post_status'] ) : 'publish';
		?>
        <div class="wrap woocommerce-alidropship-imported-list">
            <h2><?php esc_html_e( 'Imported AliExpress products', 'woocommerce-alidropship' ) ?></h2>
			<?php
			$args = array(
				'post_type'      => 'vi_wad_draft_product',
				'post_status'    => $status,
				'order'          => 'DESC',
				'orderby'        => 'meta_value_num',
				'fields'         => 'ids',
				'posts_per_page' => $per_page,
				'paged'          => $paged,
				'meta_query'     => array(
					'relation' => 'and',
					array(
						'key'     => '_vi_wad_woo_id',
						'compare' => 'exists',
					),
				),
			);
			if ( ! $is_main ) {
				$args['author'] = $user->ID;
			}
			$keyword          = isset( $_GET['vi_wad_search'] ) ? sanitize_text_field( stripslashes( $_GET['vi_wad_search'] ) ) : '';
			$vi_wad_search_id = isset( $_GET['vi_wad_search_woo_id'] ) ? sanitize_text_field( $_GET['vi_wad_search_woo_id'] ) : '';
			if ( $vi_wad_search_id ) {
				$args['meta_value']     = $vi_wad_search_id;
				$args['posts_per_page'] = 1;
				$keyword                = '';
			} else if ( $keyword ) {
				$args['s'] = $keyword;
			}
			$the_query     = new WP_Query( $args );
			$count         = $the_query->found_posts;
			$total_page    = $the_query->max_num_pages;
			$paged         = $total_page >= intval( $paged ) ? $paged : 1;
			$product_count = self::get_product_count();
			if ( $the_query->have_posts() ) {
				ob_start();
				?>
                <form method="get" class="<?php echo esc_attr( self::set( 'imported-products-' . $status ) ) ?>">
                    <input type="hidden" name="page" value="woocommerce-alidropship-imported-list">
					<?php
					do_action( 'vi_wad_imported_list_search_form' );
					?>
                    <input type="hidden" name="post_status" value="<?php echo esc_attr( $status ) ?>">
                    <div class="tablenav top">
                        <div class="<?php echo esc_attr( self::set( 'button-update-products-container' ) ) ?>">
							<?php
							if ( $status !== 'trash' ) {
								?>
                                <a class="vi-ui button green mini <?php echo esc_attr( self::set( array(
									'button-update-products',
									'hidden'
								) ) ) ?> inverted labeled icon"
                                   target="_blank"
                                   href="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_update_product_url( $the_query->posts[0] ) ) ?>"
                                   title="<?php esc_attr_e( 'Sync imported products with AliExpress using chrome extension', 'woocommerce-alidropship' ) ?>"><i
                                            class="icon external"></i><?php esc_html_e( 'Sync Products', 'woocommerce-alidropship' ) ?>
                                </a>
                                <a target="_blank"
                                   href="https://downloads.villatheme.com/?download=alidropship-extension"
                                   title="<?php esc_attr_e( 'To sync products manually, please install the chrome extension', 'woocommerce-alidropship' ) ?>"
                                   class="vi-ui positive button labeled icon mini <?php echo esc_attr( self::set( 'download-chrome-extension' ) ) ?>"><i
                                            class="external icon"></i><?php esc_html_e( 'Install Extension', 'woocommerce-alidropship' ) ?>
                                </a>
								<?php
							} else {
								?>
                                <a class="vi-ui button negative mini <?php echo esc_attr( self::set( 'button-empty-trash' ) ) ?> labeled icon"
                                   href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'vi_wad_empty_trash', 1 ) ) ) ?>"
                                   title="<?php esc_attr_e( 'Permanently delete all products from the trash', 'woocommerce-alidropship' ) ?>"><i
                                            class="icon trash"></i><?php esc_html_e( 'Empty Trash', 'woocommerce-alidropship' ) ?>
                                </a>
								<?php
							}
							?>
                        </div>
                        <div class="subsubsub">
                            <ul>
                                <li class="<?php echo esc_attr( self::set( 'imported-products-count-publish-container' ) ) ?>">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-alidropship-imported-list' ) ) ?>">
										<?php esc_html_e( 'Publish', 'woocommerce-alidropship' ); ?></a>
                                    (<span class="<?php echo esc_attr( self::set( 'imported-products-count-publish' ) ) ?>"><?php echo esc_html( $product_count->publish ) ?></span>)
                                </li>
                                |
                                <li class="<?php echo esc_attr( self::set( 'imported-products-count-trash-container' ) ) ?>">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-alidropship-imported-list&post_status=trash' ) ) ?>">
										<?php esc_html_e( 'Trash', 'woocommerce-alidropship' ); ?></a>
                                    (<span class="<?php echo esc_attr( self::set( 'imported-products-count-trash' ) ) ?>"><?php echo esc_html( $product_count->trash ) ?></span>)
                                </li>
                            </ul>
                        </div>
                        <div class="tablenav-pages">
                            <div class="pagination-links">
								<?php
								if ( $paged > 2 ) {
									?>
                                    <a class="prev-page button" href="<?php echo esc_url( add_query_arg(
										array(
											'page'          => 'woocommerce-alidropship-imported-list',
											'paged'         => 1,
											'vi_wad_search' => $keyword,
											'post_status'   => $status,
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
											'page'          => 'woocommerce-alidropship-imported-list',
											'paged'         => $p_paged,
											'vi_wad_search' => $keyword,
											'post_status'   => $status,
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
                                    <span class="tablenav-paging-text">
                                        <input class="current-page" type="text" name="paged" size="1"
                                               value="<?php echo esc_html( $paged ) ?>"><span
                                                class="tablenav-paging-text"><?php esc_html_e( ' of ', 'woocommerce-alidropship' ) ?>
                                            <span
                                                    class="total-pages"><?php echo esc_html( $total_page ) ?></span>
                                        </span>
                                    </span>
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
											'page'          => 'woocommerce-alidropship-imported-list',
											'paged'         => $n_paged,
											'vi_wad_search' => $keyword,
											'post_status'   => $status,
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
											'page'          => 'woocommerce-alidropship-imported-list',
											'paged'         => $total_page,
											'vi_wad_search' => $keyword,
											'post_status'   => $status,
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
                                   placeholder="<?php esc_attr_e( 'Search imported product', 'woocommerce-alidropship' ) ?>"
                                   value="<?php echo esc_attr( $keyword ) ?>">
                            <input type="submit" name="submit" class="button"
                                   value="<?php esc_attr_e( 'Search product', 'woocommerce-alidropship' ) ?>">
                        </p>
                    </div>
                </form>
				<?php
				$pagination_html = ob_get_clean();
				echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $pagination_html );
				$key         = 0;
				$date_format = get_option( 'date_format' );
				if ( ! $date_format ) {
					$date_format = 'F j, Y';
				}
				$show_shipping_option = self::$settings->get_params( 'show_shipping_option' );
				foreach ( $the_query->posts as $product_id ) {
					$product            = get_post( $product_id );
					$woo_product_id     = get_post_meta( $product_id, '_vi_wad_woo_id', true );
					$video              = get_post_meta( $woo_product_id, '_vi_wad_product_video', true );
					$title              = $product->post_title;
					$woo_product        = wc_get_product( $woo_product_id );
					$woo_product_status = '';
					$woo_product_name   = $title;
					$sku                = get_post_meta( $product_id, '_vi_wad_sku', true );
					$woo_sku            = $sku;
					if ( $woo_product ) {
						$woo_sku            = $woo_product->get_sku();
						$woo_product_status = $woo_product->get_status();
						$woo_product_name   = $woo_product->get_name();
					}
					$gallery    = get_post_meta( $product_id, '_vi_wad_gallery', true );
					$store_info = get_post_meta( $product_id, '_vi_wad_store_info', true );
					$image      = wp_get_attachment_thumb_url( get_post_meta( $product_id, '_vi_wad_product_image', true ) );
					if ( ! $image ) {
						$image = ( is_array( $gallery ) && count( $gallery ) ) ? array_shift( $gallery ) : '';
					}
					$variations         = get_post_meta( $product_id, '_vi_wad_variations', true );
					$overriding_product = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_overriding_product( $product_id );
					$notice_time        = '';
					$accordion_active   = '';
					$message_status     = 'warning';
					$message            = self::get_product_message( $product_id, $accordion_active, $message_status, $notice_time );
					if ( $overriding_product ) {
						$accordion_active = 'active';
					}
					if ( $status === 'trash' ) {
						$accordion_active = '';
					}
					?>
                    <div class="vi-ui styled fluid accordion <?php echo esc_attr( self::set( 'accordion' ) ); ?>"
                         id="<?php echo esc_attr( self::set( 'product-item-id-' . $product_id ) ) ?>">
                        <div class="title <?php echo esc_attr( $accordion_active ) ?>">
                            <i class="dropdown icon <?php echo esc_attr( self::set( 'accordion-title-icon' ) ); ?>"></i>
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
										$import_date = $product->post_date;
										if ( $woo_product && $woo_product->get_date_created() ) {
											$import_date = $woo_product->get_date_created()->date_i18n();
										}
										?>
                                        <div class="<?php echo esc_attr( self::set( 'accordion-product-date' ) ) ?>"><?php esc_html_e( 'Import date: ', 'woocommerce-alidropship' ) ?>
                                            <span><?php echo esc_html( $import_date ) ?></span>
                                        </div>
										<?php
										do_action( 'vi_wad_imported_list_product_information', $product );
										?>
                                    </div>
                                </div>
                                <div class="<?php echo esc_attr( self::set( 'button-view-and-edit' ) ) ?>">
                                    <a href="<?php echo( esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $sku ) ) ) ?>"
                                       target="_blank" class="vi-ui mini button labeled icon"
                                       rel="nofollow"><i
                                                class="icon external"></i><?php esc_html_e( 'View on AliExpress', 'woocommerce-alidropship' ) ?>
                                    </a>
									<?php
									if ( $woo_product ) {
										if ( $woo_product_status !== 'trash' ) {
											echo VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::get_button_view_edit_html( $woo_product_id );
											?>
                                            <a href="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_update_product_url( $product_id, false ) ) ?>"
                                               target="_blank"
                                               class="vi-ui button mini green inverted labeled icon <?php echo esc_attr( self::set( array(
												   'button-update-product',
												   'hidden'
											   ) ) ) ?>"
                                               rel="nofollow"><i
                                                        class="icon external"></i><?php esc_html_e( 'Sync', 'woocommerce-alidropship' ); ?>
                                            </a>
                                            <a target="_blank"
                                               href="https://downloads.villatheme.com/?download=alidropship-extension"
                                               title="<?php esc_attr_e( 'To sync this product manually, please install the chrome extension', 'woocommerce-alidropship' ) ?>"
                                               class="vi-ui positive button labeled icon mini <?php echo esc_attr( self::set( 'download-chrome-extension' ) ) ?>"><i
                                                        class="external icon"></i><?php esc_html_e( 'Install Extension', 'woocommerce-alidropship' ) ?>
                                            </a>
											<?php
										} else {
											if ( $status !== 'trash' ) {
												?>
                                                <span class="vi-ui black button mini <?php echo esc_attr( self::set( 'button-trash' ) ) ?>"
                                                      title="<?php esc_attr_e( 'This product is trashed from your WooCommerce store.', 'woocommerce-alidropship' ) ?>"
                                                      data-product_title="<?php echo esc_attr( $title ) ?>"
                                                      data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                                      data-woo_product_id=""><?php esc_html_e( 'Trash', 'woocommerce-alidropship' ) ?>
                                                </span>
                                                <span class="vi-ui button negative mini <?php echo esc_attr( self::set( 'button-delete' ) ) ?>"
                                                      title="<?php esc_attr_e( 'Delete this product permanently', 'woocommerce-alidropship' ) ?>"
                                                      data-product_title="<?php echo esc_attr( $title ) ?>"
                                                      data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                                      data-woo_product_id="<?php echo esc_attr( $woo_product ? $woo_product_id : '' ) ?>"><?php esc_html_e( 'Delete', 'woocommerce-alidropship' ) ?>
                                                </span>
												<?php
											} else {
												?>
                                                <span class="vi-ui button positive mini <?php echo esc_attr( self::set( 'button-restore' ) ) ?>"
                                                      title="<?php esc_attr_e( 'Restore this product', 'woocommerce-alidropship' ) ?>"
                                                      data-product_title="<?php echo esc_attr( $title ) ?>"
                                                      data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                                      data-woo_product_id="<?php echo esc_attr( $woo_product ? $woo_product_id : '' ) ?>"><?php esc_html_e( 'Restore', 'woocommerce-alidropship' ) ?></span>
                                                <span class="vi-ui button negative mini <?php echo esc_attr( self::set( 'button-delete' ) ) ?>"
                                                      title="<?php esc_attr_e( 'Delete this product permanently', 'woocommerce-alidropship' ) ?>"
                                                      data-product_title="<?php echo esc_attr( $title ) ?>"
                                                      data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                                      data-woo_product_id="<?php echo esc_attr( $woo_product ? $woo_product_id : '' ) ?>"><?php esc_html_e( 'Delete', 'woocommerce-alidropship' ) ?></span>
												<?php
											}
										}
									} else {
										if ( $status !== 'trash' ) {
											?>
                                            <span class="vi-ui black mini button <?php echo esc_attr( self::set( 'button-trash' ) ) ?>"
                                                  title="<?php esc_attr_e( 'This product is deleted from your WooCommerce store.', 'woocommerce-alidropship' ) ?>"
                                                  data-product_title="<?php echo esc_attr( $title ) ?>"
                                                  data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                                  data-woo_product_id=""><?php esc_html_e( 'Trash', 'woocommerce-alidropship' ) ?>
                                            </span>
                                            <span class="vi-ui button negative mini <?php echo esc_attr( self::set( 'button-delete' ) ) ?>"
                                                  title="<?php esc_attr_e( 'Delete this product permanently', 'woocommerce-alidropship' ) ?>"
                                                  data-product_title="<?php echo esc_attr( $title ) ?>"
                                                  data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                                  data-woo_product_id="<?php echo esc_attr( $woo_product ? $woo_product_id : '' ) ?>"><?php esc_html_e( 'Delete', 'woocommerce-alidropship' ) ?>
                                            </span>
											<?php
										} else {
											?>
                                            <span class="vi-ui button negative mini <?php echo esc_attr( self::set( 'button-delete' ) ) ?>"
                                                  title="<?php esc_attr_e( 'Delete this product permanently', 'woocommerce-alidropship' ) ?>"
                                                  data-product_title="<?php echo esc_attr( $title ) ?>"
                                                  data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                                  data-woo_product_id="<?php echo esc_attr( $woo_product ? $woo_product_id : '' ) ?>"><?php esc_html_e( 'Delete', 'woocommerce-alidropship' ) ?>
                                            </span>
											<?php
										}
									}
									?>
                                    <span class="vi-ui button negative mini loading <?php echo esc_attr( self::set( 'button-deleting' ) ) ?>"><?php esc_html_e( 'Delete', 'woocommerce-alidropship' ) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="content <?php echo esc_attr( $accordion_active ) ?>">
							<?php
							if ( $overriding_product && $status !== 'trash' ) {
								$overriding_product_title = get_the_title( $overriding_product );
								?>
                                <div class="vi-ui message">
                                    <span><?php printf( __( 'This product is being overridden by: %s. Please go to %s to complete the process.', 'woocommerce-alidropship' ), '<strong>' . $overriding_product_title . '</strong>', '<a target="_blank" href="' . add_query_arg( array( 'vi_wad_search_id' => $overriding_product ), $import_list_url ) . '">Import list</a>' ) ?></span>
                                </div>
								<?php
							}
							?>
                            <div class="<?php echo esc_attr( self::set( 'message' ) ) ?>">
								<?php
								if ( $message && $status !== 'trash' ) {
									?>
                                    <div class="vi-ui message <?php echo esc_attr( self::set( 'product-notice-message' ) ) ?> <?php echo esc_attr( $message_status ) ?>">
                                        <div>
                                            <span>
                                                <?php
                                                echo esc_html( $message );
                                                if ( $notice_time ) {
	                                                ?>
                                                    <span class="<?php echo esc_attr( self::set( 'product-notice-time' ) ) ?>">
                                                        <?php
                                                        printf( esc_html__( '(%s)', 'woocommerce-alidropship' ), date_i18n( "{$date_format} h:i:s A", $notice_time ) );
                                                        ?>
                                                    </span>
	                                                <?php
                                                }
                                                ?>
                                            </span>
											<?php
											if ( $message_status !== 'negative' ) {
												?>
                                                <i class="vi-ui icon cancel <?php echo esc_attr( self::set( 'product-notice-dismiss' ) ) ?>"
                                                   data-product_id="<?php echo esc_attr( $product_id ) ?>"></i>
												<?php
											}
											?>
                                        </div>
                                    </div>
									<?php
								}
								?>
                            </div>
                            <form class="vi-ui form <?php echo esc_attr( self::set( 'product-container' ) ) ?>"
                                  method="post">
								<?php
								ob_start();
								?>
                                <div class="field">
                                    <div class="fields">
                                        <div class="three wide field">
                                            <div class="<?php echo esc_attr( self::set( 'product-image' ) ) ?>">
                                                <img src="<?php echo esc_url( $image ? $image : wc_placeholder_img_src() ) ?>"
                                                     class="<?php echo esc_attr( self::set( 'import-data-image' ) ) ?>">
                                                <input type="hidden"
                                                       name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][image]' ) ?>"
                                                       value="<?php echo esc_attr( $image ? $image : wc_placeholder_img_src() ) ?>">
                                            </div>
                                        </div>
                                        <div class="thirteen wide field">
                                            <div class="field">
                                                <label><?php esc_html_e( 'WooCommerce product title' ) ?></label>
                                                <input type="text" value="<?php echo esc_attr( $woo_product_name ) ?>"
                                                       readonly
                                                       name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][title]' ) ?>"
                                                       class="<?php echo esc_attr( self::set( 'import-data-title' ) ) ?>">
                                            </div>

											<?php
											ob_start();
											?>
                                            <div class="four wide field">
                                                <label><?php esc_html_e( 'Cost', 'woocommerce-alidropship' ) ?></label>
                                                <div class="<?php echo esc_attr( self::set( 'price-field' ) ) ?>">
													<?php
													if ( count( $variations ) == 1 ) {
														$variation_sale_price    = $variations[0]['sale_price'];
														$variation_regular_price = $variations[0]['regular_price'];
														$price                   = $variation_sale_price ? $variation_sale_price : $variation_regular_price;
														echo wc_price( $price, array(
															'currency'     => 'USD',
															'decimals'     => '2',
															'price_format' => '%1$s&nbsp;%2$s'
														) );
													} else {
														$min_price = 0;
														$max_price = 0;
														foreach ( $variations as $variation_k => $variation_v ) {
															$variation_sale_price    = $variation_v['sale_price'];
															$variation_regular_price = $variation_v['regular_price'];
															$price                   = $variation_sale_price ? $variation_sale_price : $variation_regular_price;
															if ( ! $min_price ) {
																$min_price = $price;
															}
															if ( $price < $min_price ) {
																$min_price = $price;
															}
															if ( $price > $max_price ) {
																$max_price = $price;
															}
														}
														if ( $min_price && $min_price != $max_price ) {
															echo wc_price( $min_price, array(
																	'currency'     => 'USD',
																	'decimals'     => '2',
																	'price_format' => '%1$s&nbsp;%2$s'
																) ) . ' - ' . wc_price( $max_price, array(
																	'currency'     => 'USD',
																	'decimals'     => '2',
																	'price_format' => '%1$s&nbsp;%2$s'
																) );
														} elseif ( $max_price ) {
															echo wc_price( $max_price, array(
																'currency'     => 'USD',
																'decimals'     => '2',
																'price_format' => '%1$s&nbsp;%2$s'
															) );
														}
													}
													?>
                                                </div>
                                            </div>
											<?php
											$price_html = ob_get_clean();
											?>
                                            <div class="field">
                                                <div class="fields">
													<?php
													echo $price_html;
													/*$cache_time=time() to not request the latest shipping info, only load existing data from db*/
													$shipping_info       = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::get_shipping_info( $product_id, '', '', time() );
													$shipping_info_class = array( 'imported-list-shipping-info' );
													if ( ! $show_shipping_option || ( $message && $message_status === 'negative' ) ) {
														$shipping_info_class[] = 'imported-list-shipping-info-refresh';
													}
													?>
                                                    <div class="twelve wide field <?php echo esc_attr( self::set( $shipping_info_class ) ) ?>">
                                                        <div class="<?php echo esc_attr( self::set( array(
															'imported-list-shipping-info-overlay',
															'hidden'
														) ) ) ?>"></div>
                                                        <label><?php esc_html_e( 'Shipping info to calculate price when syncing products', 'woocommerce-alidropship' ) ?></label>
														<?php VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::shipping_option_html( $shipping_info, $key, $product_id, 'simple' ); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'Sku', 'woocommerce-alidropship' ) ?></label>
                                                        <input type="text" value="<?php echo esc_attr( $woo_sku ) ?>"
                                                               readonly
                                                               name="<?php echo esc_attr( 'vi_wad_product[' . $product_id . '][sku]' ) ?>"
                                                               class="<?php echo esc_attr( self::set( 'import-data-sku' ) ) ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'WC product status', 'woocommerce-alidropship' ) ?></label>
                                                        <div class="<?php echo esc_attr( self::set( 'price-field' ) ) ?>"><?php echo esc_html( $woo_product_status ); ?></div>
                                                    </div>
													<?php
													if ( $woo_product && $woo_product_status !== 'trash' ) {
														?>
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'WooCommerce Price', 'woocommerce-alidropship' ) ?></label>
                                                            <div class="<?php echo esc_attr( self::set( 'price-field' ) ) ?>">
																<?php
																echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $woo_product->get_price_html() );
																?>
                                                            </div>
                                                        </div>
														<?php
													}
													?>
                                                </div>
                                            </div>

                                            <div class="field">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <div class="<?php echo esc_attr( self::set( 'button-override-container' ) ) ?>">
															<?php
															if ( $status !== 'trash' ) {
																if ( $woo_product && $woo_product_status !== 'trash' ) {
																	?>
                                                                    <span class="vi-ui button negative mini <?php echo esc_attr( self::set( 'button-delete' ) ) ?>"
                                                                          title="<?php esc_attr_e( 'Delete this product permanently', 'woocommerce-alidropship' ) ?>"
                                                                          data-product_title="<?php echo esc_attr( $title ) ?>"
                                                                          data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                                                          data-woo_product_id="<?php echo esc_attr( $woo_product ? $woo_product_id : '' ) ?>"><?php esc_html_e( 'Delete', 'woocommerce-alidropship' ) ?></span>
																	<?php
																	if ( ! $overriding_product ) {
																		?>
                                                                        <span class="vi-ui button positive mini <?php echo esc_attr( self::set( 'button-override' ) ) ?>"
                                                                              title="<?php esc_attr_e( 'Override this product', 'woocommerce-alidropship' ) ?>"
                                                                              data-product_title="<?php echo esc_attr( $title ) ?>"
                                                                              data-product_id="<?php echo esc_attr( $product_id ) ?>"
                                                                              data-woo_product_id="<?php echo esc_attr( $woo_product_id ) ?>"><?php esc_html_e( 'Override', 'woocommerce-alidropship' ) ?></span>
                                                                        <a title="<?php esc_attr_e( 'Reimport this product', 'woocommerce-alidropship' ) ?>"
                                                                           class="vi-ui button positive mini labeled icon <?php echo esc_attr( self::set( 'button-reimport' ) ) ?>"
                                                                           target="<?php echo esc_attr( $is_vendor ? '_self' : '_blank' ) ?>"
                                                                           href="<?php echo esc_url( add_query_arg( array(
																			   'reimport_id' => $product_id,
																			   '_wpnonce'    => wp_create_nonce( 'reimport_nonce' )
																		   ), $import_list_url ) ) ?>"><i
                                                                                    class="icon external"></i><?php esc_html_e( 'Reimport', 'woocommerce-alidropship' ) ?>
                                                                        </a>
																		<?php
																	} else {
																		echo self::button_override_html( $product_id, $overriding_product );
																	}
																}
															}
															?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<?php
								$content = ob_get_clean();
								if ( $video ) {
									?>
                                    <div class="vi-ui attached tabular menu">
                                        <div class="item active" data-tab="<?php echo esc_attr( 'general-' . $key ) ?>">
											<?php esc_html_e( 'Basic info', 'woocommerce-alidropship' ) ?>
                                        </div>
                                        <div class="item" data-tab="<?php echo esc_attr( 'video-' . $key ) ?>">
											<?php esc_html_e( 'Video', 'woocommerce-alidropship' ) ?>
                                        </div>
                                    </div>
                                    <div class="vi-ui bottom attached tab segment active <?php echo esc_attr( self::set( 'general-tab' ) ) ?>"
                                         data-tab="<?php echo esc_attr( 'general-' . $key ) ?>">
										<?php
										echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $content );
										?>
                                    </div>
                                    <div class="vi-ui bottom attached tab segment <?php echo esc_attr( self::set( 'video-tab' ) ) ?>"
                                         data-tab="<?php echo esc_attr( 'video-' . $key ) ?>">
										<?php echo do_shortcode( '[video src="' . esc_url( $video ) . '"]' ); ?>
                                    </div>
									<?php
								} else {
									echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $content );
								}
								?>
                            </form>
                        </div>
                    </div>
					<?php
					$key ++;
				}
				echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $pagination_html );
			} else {
				?>
                <form method="get">
                    <input type="hidden" name="page" value="woocommerce-alidropship-imported-list">
					<?php
					do_action( 'vi_wad_imported_list_search_form' );
					?>
                    <input type="search" class="text short" name="vi_wad_search"
                           placeholder="<?php esc_attr_e( 'Search product', 'woocommerce-alidropship' ) ?>"
                           value="<?php echo esc_attr( $keyword ) ?>">
                    <input type="submit" name="submit" class="button"
                           value="<?php esc_attr_e( 'Search product', 'woocommerce-alidropship' ) ?>">
                    <p>
						<?php esc_html_e( 'No products found', 'woocommerce-alidropship' ) ?>
                    </p>
                </form>
				<?php
			}
			wp_reset_postdata();
			?>
        </div>
		<?php
	}

	/**
	 * @param $product_id
	 * @param $overriding_product
	 *
	 * @return false|string
	 */
	public static function button_override_html( $product_id, $overriding_product ) {
		if ( get_post_meta( $product_id, '_vi_wad_sku', true ) == get_post_meta( $overriding_product, '_vi_wad_sku', true ) ) {
			$text_complete  = __( 'Complete reimporting', 'woocommerce-alidropship' );
			$text_cancel    = __( 'Cancel reimporting', 'woocommerce-alidropship' );
			$title_complete = __( 'Go to Import list to complete reimporting', 'woocommerce-alidropship' );
			$title_cancel   = __( 'Cancel reimporting this product', 'woocommerce-alidropship' );
		} else {
			$text_complete  = __( 'Complete overriding', 'woocommerce-alidropship' );
			$text_cancel    = __( 'Cancel overriding', 'woocommerce-alidropship' );
			$title_complete = __( 'Go to Import list to complete overriding', 'woocommerce-alidropship' );
			$title_cancel   = __( 'Cancel overriding this product', 'woocommerce-alidropship' );
		}
		$is_vendor = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Vendor::is_ald_vendor_page();
		ob_start();
		?>
        <a title="<?php echo esc_attr( $title_complete ) ?>"
           class="vi-ui button positive mini labeled icon <?php echo esc_attr( self::set( 'button-complete-overriding' ) ) ?>"
           target="<?php echo esc_attr( $is_vendor ? '_self' : '_blank' ) ?>"
           href="<?php echo esc_url( add_query_arg( array( 'vi_wad_search_id' => $overriding_product ), VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::get_url() ) ) ?>"><i
                    class="icon external"></i><?php echo esc_html( $text_complete ) ?>
        </a>
        <a title="<?php echo esc_attr( $title_cancel ) ?>"
           class="vi-ui button mini <?php echo esc_attr( self::set( 'button-cancel-overriding' ) ) ?>"
           target="_self"
           href="<?php echo esc_url( add_query_arg( array(
			   'page'               => 'woocommerce-alidropship-imported-list',
			   'overridden_product' => $product_id,
			   'cancel_overriding'  => $overriding_product,
			   '_wpnonce'           => wp_create_nonce( 'cancel_overriding_nonce' )
		   ), admin_url( 'admin.php' ) ) ) ?>"><?php echo esc_html( $text_cancel ) ?></a>
		<?php
		return ob_get_clean();
	}

	/**
	 *
	 */
	public function screen_options_page_imported() {
		add_screen_option( 'per_page', array(
			'label'   => esc_html__( 'Number of items per page', 'wp-admin' ),
			'default' => 5,
			'option'  => 'vi_wad_imported_per_page'
		) );
	}

	private static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_ALIDROPSHIP_DATA::set( $name, $set_name );
	}

	/**
	 * @param $product_id
	 * @param $accordion_active
	 * @param $message_status
	 * @param $notice_time
	 *
	 * @return string
	 */
	public static function get_product_message( $product_id, &$accordion_active, &$message_status, &$notice_time ) {
		$product_notice = get_post_meta( $product_id, '_vi_wad_update_product_notice', true );
		$message        = '';
		if ( $product_notice ) {
			$notice_time = $product_notice['time'];
			if ( $product_notice['is_offline'] ) {
				$accordion_active = 'active';
				$message_status   = 'negative';
				$message          = esc_html__( 'This product is no longer available', 'woocommerce-alidropship' );
			} elseif ( $product_notice['shipping_removed'] ) {
				$accordion_active = 'active';
				$message_status   = 'negative';
				if ( $product_notice['shipping_removed'] === true ) {
					$message = esc_html__( 'The selected shipping method of this product is no longer available', 'woocommerce-alidropship' );
				} else {
					$message = sprintf( esc_html__( 'The shipping method %s is no longer available', 'woocommerce-alidropship' ), $product_notice['shipping_removed'] );
				}
			} elseif ( $product_notice['is_out_of_stock'] ) {
				$accordion_active = 'active';
				$message_status   = 'negative';
				$message          = esc_html__( 'This product is out of stock', 'woocommerce-alidropship' );
			} elseif ( empty( $product_notice['hide'] ) ) {
				if ( count( $product_notice['not_available'] ) ) {
					$accordion_active = 'active';
					$message          = sprintf( _n( '%1s variation of this product is no longer available: #%2s', '%1s variations of this product are no longer available: #%2s', count( $product_notice['not_available'] ), 'woocommerce-alidropship' ), count( $product_notice['not_available'] ), implode( ', #', $product_notice['not_available'] ) );
				} elseif ( count( $product_notice['out_of_stock'] ) ) {
					$message          = sprintf( _n( '%1s variation of this product is out of stock: #%2s', '%1s variations of this product are out of stock: #%2s', count( $product_notice['out_of_stock'] ), 'woocommerce-alidropship' ), count( $product_notice['out_of_stock'] ), implode( ', #', $product_notice['out_of_stock'] ) );
					$accordion_active = 'active';
				} elseif ( $price_changes = count( $product_notice['price_changes'] ) ) {
					if ( $price_changes === 1 ) {
						$message = sprintf( esc_html__( 'This product has price changed: #%1s', 'woocommerce-alidropship' ), $product_notice['price_changes'][0] );
					} else {
						$message = sprintf( esc_html__( '%1s variations of this product have price changed: #%2s', 'woocommerce-alidropship' ), $price_changes, implode( ', #', $product_notice['price_changes'] ) );
					}
					$accordion_active = 'active';
				} elseif ( isset( $product_notice['price_exceeds'] ) && count( $product_notice['price_exceeds'] ) ) {
					$message          = sprintf( esc_html__( 'Price sync skipped because the percentage of change exceeds the set value: #%1s', 'woocommerce-alidropship' ), implode( ', #', $product_notice['price_exceeds'] ) );
					$accordion_active = 'active';
				}
			}
		}

		return $message;
	}

	/**
	 * Check ajax referer
	 */
	private static function check_ajax_referer() {
		VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::check_ajax_referer( 'woocommerce-alidropship-imported-list' );
	}
}