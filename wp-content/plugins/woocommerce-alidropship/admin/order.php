<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Order {
	private static $settings;
	private $is_orders_tracking_active;

	public function __construct() {
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		//Add column in Order page
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'column_callback_order' ), 10, 2 );
		add_filter( 'woocommerce_order_item_display_meta_key', array(
			$this,
			'woocommerce_order_item_display_meta_key'
		), 99, 3 );
		add_filter( 'woocommerce_order_item_display_meta_value', array(
			$this,
			'woocommerce_order_item_display_meta_value'
		), 99, 3 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'woocommerce_hidden_order_itemmeta' ) );
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'show_item_shipping_info' ), 10, 3 );
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'woocommerce_after_order_itemmeta' ), 10, 3 );
		add_action( 'wp_ajax_vi_wad_manually_update_ali_order_id', array( $this, 'update_ali_order_id' ) );
		add_action( 'wp_ajax_vi_wad_ali_order_detail', array( $this, 'get_ali_order_detail' ) );
		add_filter( 'posts_where', array( $this, 'filter_where' ), 10, 2 );
		add_action( 'woocommerce_new_order_item', array( $this, 'add_order_item_meta' ), 10, 2 );
		add_filter( 'views_edit-shop_order', array( $this, 'ali_filter' ) );
		add_action( 'woocommerce_order_actions_end', array( $this, 'order_ali_button' ) );
		add_filter( 'posts_where', array( $this, 'posts_where' ), 1, 2 );
		add_action( 'admin_head-edit.php', array( $this, 'sync_orders_button' ) );
	}

	public function sync_orders_button() {
		global $current_screen;
		if ( 'shop_order' != $current_screen->post_type ) {
			return;
		}
		?>
        <script type="text/javascript">
            'use strict';
            jQuery(document).ready(function ($) {
                jQuery(".wrap .page-title-action").eq(0).after("<a class='page-title-action' target='_blank' href='<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_get_tracking_url() ) ?>'><?php esc_html_e( 'AliExpress sync', 'woocommerce-alidropship' ) ?></a>");
            });
        </script>
		<?php
	}

	/**
	 * @param $item_id
	 * @param $item WC_Order_Item
	 * @param $product WC_Product
	 */
	public function show_item_shipping_info( $item_id, $item, $product ) {
		global $post;
		if ( ! $post || ! is_a( $item, 'WC_Order_Item_Product' ) || ! is_object( $product ) ) {
			return;
		}
		$order_id   = $post->ID;
		$product_id = $product->get_id();
		if ( ! get_post_meta( $product_id, '_vi_wad_aliexpress_product_id', true ) && ! get_post_meta( $product_id, '_vi_wad_aliexpress_variation_attr', true ) ) {
			return;
		}
		$shipping_info = $item->get_meta( '_vi_wot_customer_chosen_shipping' );
		if ( $shipping_info ) {
			$shipping_info = vi_wad_json_decode( $shipping_info );
			$delivery_time = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::process_delivery_time( $shipping_info['delivery_time'] );
			$shipping_cost = abs( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::string_to_float( $shipping_info['shipping_cost'] ) );
			if ( $shipping_cost ) {
				$shipping_cost    = wc_price( $shipping_cost, array( 'currency' => get_post_meta( $order_id, '_order_currency', true ) ) );
				$shipping_display = "[{$shipping_cost}] {$shipping_info['company_name']}({$delivery_time})";
			} else {
				$shipping_display = esc_html__( 'Free', 'woocommerce-alidropship' ) . "({$delivery_time})";
			}
			?>
            <div class="vi-wad-order-item-shipping">
                <strong><?php esc_html_e( 'Shipping: ', 'woocommerce-alidropship' ); ?></strong><?php echo $shipping_display; ?>
            </div>
			<?php
		}
	}

	public function posts_join( $join, $wp_query ) {
		global $wpdb;
		$join .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items as vi_wad_woocommerce_order_items ON $wpdb->posts.ID=vi_wad_woocommerce_order_items.order_id";
		$join .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as vi_wad_woocommerce_order_itemmeta ON vi_wad_woocommerce_order_items.order_item_id=vi_wad_woocommerce_order_itemmeta.order_item_id";

		return $join;
	}

	public function posts_where( $where, $wp_query ) {
		global $pagenow, $wpdb;
		$search    = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';

		if ( $pagenow === 'edit.php' && $search && $post_type === 'shop_order' ) {
			$where .= $wpdb->prepare( " OR (vi_wad_woocommerce_order_itemmeta.meta_key='_vi_wad_aliexpress_order_id' AND vi_wad_woocommerce_order_itemmeta.meta_value = %s)", $search );
			add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
			add_filter( 'posts_distinct', array( $this, 'posts_distinct' ), 10, 2 );
		}

		return $where;
	}

	public function posts_distinct( $join, $wp_query ) {
		return 'DISTINCT';
	}

	/**
	 * Update Ali order ID manually
	 *
	 * @throws Exception
	 */
	public function update_ali_order_id() {
		check_ajax_referer( 'woocommerce_alidropship_admin_ajax', '_vi_wad_ajax_nonce' );
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-ali-orders' ) ) ) {
			wp_die();
		}
		$ali_order_id    = isset( $_POST['ali_order_id'] ) ? trim( sanitize_text_field( $_POST['ali_order_id'] ) ) : '';
		$order_id        = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
		$item_id         = isset( $_POST['item_id'] ) ? sanitize_text_field( $_POST['item_id'] ) : '';
		$return_shipping = isset( $_POST['return_shipping'] ) ? sanitize_text_field( $_POST['return_shipping'] ) : '';
		$response        = array(
			'status'                    => 'error',
			'message'                   => '',
			'text'                      => '',
			'delete_tracking'           => 'no',
			'shipping_company_html'     => '',
			'shipping_company_selected' => '',
		);
		if ( $item_id ) {
			if ( wc_update_order_item_meta( $item_id, '_vi_wad_aliexpress_order_id', $ali_order_id ) ) {
				if ( $ali_order_id ) {
					wc_update_order_item_meta( $item_id, '_vi_wad_aliexpress_order_item_status', 'processing' );
					$response['text'] = $this->status_switch( 'processing' );
				} else {
					wc_update_order_item_meta( $item_id, '_vi_wad_aliexpress_order_item_status', 'pending' );
					$response['text'] = $this->status_switch( 'pending' );
					if ( $return_shipping ) {
						$order = wc_get_order( $order_id );
						if ( $order ) {
							$item = $order->get_item( $item_id );
							if ( $item ) {
								$woo_product_id   = $item->get_product_id();
								$woo_variation_id = $item->get_variation_id();
								$quantity         = $item->get_quantity();
								if ( $woo_product_id ) {
									$product_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_woo_id( $woo_product_id );
									$ship_from  = '';
									if ( $woo_variation_id ) {
										$ship_from = get_post_meta( $woo_variation_id, '_vi_wad_aliexpress_variation_ship_from', true );
									}
									if ( $product_id ) {
										$currency               = 'USD';
										$woocommerce_currency   = get_option( 'woocommerce_currency' );
										$use_different_currency = false;
										if ( strtolower( $woocommerce_currency ) !== strtolower( $currency ) ) {
											$use_different_currency = true;
										}
										$response['shipping_company_html']     = VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_Orders::get_shipping_html( $product_id, $woo_product_id, $order, VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Ali_Orders::get_item_saved_shipping( $item ), $ship_from, $quantity, $use_different_currency, $freights, $shipping_total, $placeable_items_count, $shipping_company );
										$response['shipping_company_selected'] = $shipping_company;
									}
								}
							}
						}
					}
				}
				$item_tracking_data = wc_get_order_item_meta( $item_id, '_vi_wot_order_item_tracking_data', true );
				if ( $item_tracking_data ) {
					$item_tracking_data    = vi_wad_json_decode( $item_tracking_data );
					$current_tracking_data = array_pop( $item_tracking_data );
					if ( $current_tracking_data['tracking_number'] || ( $current_tracking_data['carrier_slug'] && $current_tracking_data['carrier_url'] && $current_tracking_data['carrier_name'] ) ) {
						$item_tracking_data[] = array(
							'tracking_number' => '',
							'carrier_slug'    => '',
							'carrier_url'     => '',
							'carrier_name'    => '',
							'carrier_type'    => '',
							'time'            => time(),
						);
						if ( wc_update_order_item_meta( $item_id, '_vi_wot_order_item_tracking_data', json_encode( $item_tracking_data ) ) ) {
							$response['delete_tracking'] = 'yes';
						}
					}
				}

				$response['status'] = 'success';
			}
		}
		wp_send_json( $response );
	}

	public function status_switch( $stt ) {
		$pattern = array(
			'pending'    => array( esc_html__( 'To Order', 'woocommerce-alidropship' ), 'red' ),
			'processing' => array( esc_html__( 'Processing', 'woocommerce-alidropship' ), '#0089F7' ),
			'shipped'    => array( esc_html__( 'Shipped', 'woocommerce-alidropship' ), '#00B400' ),
		);

		return isset( $pattern[ $stt ] ) ? $pattern[ $stt ] : $pattern['pending'];
	}

	public function admin_enqueue_scripts( $page ) {
		global $post_type;
		if ( $page === 'post.php' ) {
			$screen = get_current_screen();
			if ( $screen->id === 'shop_order' ) {
				wp_enqueue_style( 'woocommerce-alidropship-admin-edit-order', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'admin-order.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
				wp_enqueue_script( 'woocommerce-alidropship-admin-edit-order', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'admin-order.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
				wp_localize_script( 'woocommerce-alidropship-admin-edit-order', 'vi_wad_edit_order', array(
					'url'                => admin_url( 'admin-ajax.php' ),
					'_vi_wad_ajax_nonce' => VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::create_ajax_nonce(),
				) );
				if ( class_exists( 'WOO_ORDERS_TRACKING' ) || class_exists( 'VI_WOOCOMMERCE_ORDERS_TRACKING_DATA' ) ) {
					$this->is_orders_tracking_active = true;
				} else {
					$this->is_orders_tracking_active = false;
				}
			}
		} elseif ( $page === 'edit.php' && $post_type === 'shop_order' ) {
			wp_enqueue_style( 'woocommerce-alidropship-popup', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'popup.min.css' );
			wp_enqueue_style( 'woocommerce-alidropship-order-status', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'order-status.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_enqueue_script( 'woocommerce-alidropship-order-status', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'order-status.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_localize_script( 'woocommerce-alidropship-order-status', 'vi_wad_edit_order', array(
				'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
				'_vi_wad_ajax_nonce' => VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::create_ajax_nonce(),
			) );
		}
	}

	public function woocommerce_hidden_order_itemmeta( $hidden_order_itemmeta ) {
		$hidden_order_itemmeta[] = '_vi_wad_match_aliexpress_order_id';
		$hidden_order_itemmeta[] = '_vi_wad_aliexpress_order_id';
		$hidden_order_itemmeta[] = '_vi_order_item_tracking_code';
		$hidden_order_itemmeta[] = '_vi_wad_aliexpress_order_item_status';
		$hidden_order_itemmeta[] = '_vi_wot_order_item_tracking_data';
		$hidden_order_itemmeta[] = '_vi_wot_order_item_saved_shipping';

		$hidden_order_itemmeta[] = '_vi_wot_customer_chosen_shipping';

		return $hidden_order_itemmeta;
	}

	/**
	 * @param $item_id
	 * @param $item
	 * @param $product WC_Product
	 *
	 * @throws Exception
	 */
	public function woocommerce_after_order_itemmeta( $item_id, $item, $product ) {
		global $post;
		if ( ! $post || ! is_a( $item, 'WC_Order_Item_Product' ) || ! is_object( $product ) ) {
			return;
		}
		$order_id   = $post->ID;
		$product_id = $product->get_id();
		if ( ! get_post_meta( $product_id, '_vi_wad_aliexpress_product_id', true ) && ! get_post_meta( $product_id, '_vi_wad_aliexpress_variation_attr', true ) ) {
			return;
		}
		$aliexpress_order_id = wc_get_order_item_meta( $item_id, '_vi_wad_aliexpress_order_id', true );
		$ali_order_detail    = $tracking_url = $tracking_url_btn = '';
		if ( $aliexpress_order_id ) {
			$ali_order_detail = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_order_detail_url( $aliexpress_order_id );
			$tracking_url     = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_tracking_url( $aliexpress_order_id );
			$tracking_url_btn = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_get_tracking_url( $aliexpress_order_id );
		}
		$item_tracking_data    = wc_get_order_item_meta( $item_id, '_vi_wot_order_item_tracking_data', true );
		$current_tracking_data = array(
			'tracking_number' => '',
			'carrier_slug'    => '',
			'carrier_url'     => '',
			'carrier_name'    => '',
			'carrier_type'    => '',
			'time'            => time(),
		);
		if ( $item_tracking_data ) {
			$item_tracking_data    = vi_wad_json_decode( $item_tracking_data );
			$current_tracking_data = array_pop( $item_tracking_data );
		}
		$tracking_number = apply_filters( 'vi_woo_orders_tracking_current_tracking_number', $current_tracking_data['tracking_number'], $item_id, $order_id );
		$carrier_url     = apply_filters( 'vi_woo_orders_tracking_current_tracking_url', $current_tracking_data['carrier_url'], $item_id, $order_id );
		$carrier_name    = apply_filters( 'vi_woo_orders_tracking_current_carrier_name', $current_tracking_data['carrier_name'], $item_id, $order_id );
		$carrier_slug    = apply_filters( 'vi_woo_orders_tracking_current_carrier_slug', $current_tracking_data['carrier_slug'], $item_id, $order_id );
		$get_tracking    = array( 'item-actions-get-tracking' );
		if ( ! $aliexpress_order_id ) {
			$get_tracking[] = 'hidden';
		}
		?>
        <div class="<?php echo esc_attr( self::set( 'container' ) ) ?>">
            <div class="<?php echo esc_attr( self::set( array(
				'item-details',
				'item-ali-order-id'
			) ) ) ?>"
                 data-product_item_id="<?php echo esc_attr( $item_id ) ?>">
                <div class="<?php echo esc_attr( self::set( 'item-label' ) ) ?>">
                    <span><?php esc_html_e( 'Ali Order ID', 'woocommerce-alidropship' ) ?></span>
                </div>
                <div class="<?php echo esc_attr( self::set( 'item-value' ) ) ?>">
                    <a class="<?php echo esc_attr( self::set( 'ali-order-id' ) ) ?>"
                       href="<?php echo esc_url( $ali_order_detail ) ?>"
                       data-old_ali_order_id="<?php echo esc_attr( $aliexpress_order_id ) ?>"
                       target="_blank">
                        <input readonly
                               class="<?php echo esc_attr( self::set( array( 'ali-order-id-input' ) ) ) ?>"
                               value="<?php echo esc_attr( $aliexpress_order_id ) ?>">
                    </a>
                </div>
                <div class="<?php echo esc_attr( self::set( 'item-actions' ) ) ?>">
                    <span class="dashicons dashicons-edit <?php echo esc_attr( self::set( 'item-actions-edit' ) ) ?>"
                          title="<?php esc_attr_e( 'Edit', 'woocommerce-alidropship' ) ?>">
                    </span>
                    <span class="dashicons dashicons-yes <?php echo esc_attr( self::set( array(
						'item-actions-save',
						'hidden'
					) ) ) ?>"
                          title="<?php esc_attr_e( 'Save', 'woocommerce-alidropship' ) ?>">
                    </span>
                    <span class="dashicons dashicons-no-alt <?php echo esc_attr( self::set( array(
						'item-actions-cancel',
						'hidden'
					) ) ) ?>"
                          title="<?php esc_attr_e( 'Cancel', 'woocommerce-alidropship' ) ?>">
                    </span>
					<?php
					if ( $this->is_orders_tracking_active ) {
						?>
                        <a href="<?php echo esc_url( $tracking_url_btn ) ?>" target="_blank">
                            <span class="dashicons dashicons-arrow-down-alt <?php echo esc_attr( self::set( $get_tracking ) ) ?>"
                                  title="<?php esc_attr_e( 'Get tracking', 'woocommerce-alidropship' ) ?>">
                            </span>
                        </a>
						<?php
					}
					?>
                </div>
                <div class="<?php echo esc_attr( self::set( array(
					'item-value-overlay',
					'hidden'
				) ) ) ?>"></div>
            </div>
			<?php
			if ( ! $this->is_orders_tracking_active ) {
				?>
                <div class="<?php echo esc_attr( self::set( array(
					'item-details',
					'item-tracking-number'
				) ) ) ?>" data-product_item_id="<?php echo esc_attr( $item_id ) ?>">
                    <div class="<?php echo esc_attr( self::set( 'item-label' ) ) ?>">
                        <span><?php esc_html_e( 'Tracking number', 'woocommerce-alidropship' ) ?></span>
                    </div>
                    <div class="<?php echo esc_attr( self::set( 'item-value' ) ) ?>">
                        <a class="<?php echo esc_attr( self::set( 'ali-tracking-number' ) ) ?>"
                           href="<?php echo esc_url( $tracking_url ) ?>"
                           target="_blank">
                            <input readonly
                                   class="<?php echo esc_attr( self::set( array( 'ali-tracking-number-input' ) ) ) ?>"
                                   value="<?php echo esc_attr( $tracking_number ) ?>">
                        </a>
                    </div>
                    <div class="<?php echo esc_attr( self::set( 'item-actions' ) ) ?>">
                        <a href="<?php echo esc_url( $tracking_url_btn ) ?>" target="_blank">
                            <span class="dashicons dashicons-arrow-down-alt <?php echo esc_attr( self::set( $get_tracking ) ) ?>"
                                  title="<?php esc_attr_e( 'Get tracking', 'woocommerce-alidropship' ) ?>">
                            </span>
                        </a>
                    </div>
                </div>
				<?php
			}
			?>

        </div>
		<?php
	}

	private static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_ALIDROPSHIP_DATA::set( $name, $set_name );
	}

	public function woocommerce_order_item_display_meta_key( $display_key, $meta, $item ) {
		if ( $meta->key === '_vi_wad_match_aliexpress_order_id' ) {
			$display_key = esc_html__( 'AliExpress order ID', 'woocommerce-alidropship' );
		}

		return $display_key;
	}

	public function woocommerce_order_item_display_meta_value( $display_value, $meta, $item ) {
		if ( $meta->key === '_vi_wad_match_aliexpress_order_id' ) {
			$value = $meta->value;
			if ( $value ) {
				$display_value = sprintf( '<a target="_blank" href="%s">%s</a>', VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_order_detail_url( $value ), $value );
			}
		}

		return $display_value;
	}

	/**
	 * @param $item_id
	 * @param $values
	 *
	 * @throws Exception
	 */
	public function add_order_item_meta( $item_id, $values ) {
		$pid = $values['product_id'];
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			global $sitepress;
			$pid = apply_filters(
				'wpml_object_id', $pid, 'product', false, $sitepress->get_default_language()
			);
		}
		if ( get_post_meta( $pid, '_vi_wad_aliexpress_product_id', true ) ) {
			wc_update_order_item_meta( $item_id, '_vi_wad_aliexpress_order_item_status', 'pending' );
			wc_update_order_item_meta( $item_id, '_vi_wad_aliexpress_order_id', '' );
		}
	}

	/**
	 * @param $col_id
	 * @param $order_id
	 *
	 * @throws Exception
	 */
	public function column_callback_order( $col_id, $order_id ) {
		if ( $col_id === 'order_number' ) {
			$order          = wc_get_order( $order_id );
			$statuses       = self::$settings->get_params( 'order_status_for_fulfill' );
			$order_items    = $order->get_items();
			$fulfill_action = $status = $ali_product_id = $ali_pid = '';
			$total          = $ordered = $shipped = $tracking_number = 0;
			$order_stt      = $color = '';

			if ( count( $order_items ) ) {
				foreach ( $order_items as $item_id => $order_item ) {
					$pid            = $order_item->get_data()['product_id'];
					$ali_product_id = get_post_meta( $pid, '_vi_wad_aliexpress_product_id', true );
					$ali_pid        = $ali_product_id ? $ali_product_id : $ali_pid;

					if ( $ali_product_id ) {
						$item_tracking_data = wc_get_order_item_meta( $item_id, '_vi_wot_order_item_tracking_data', true );
						if ( $item_tracking_data ) {
							$item_tracking_data    = vi_wad_json_decode( $item_tracking_data );
							$current_tracking_data = array_pop( $item_tracking_data );
							if ( $current_tracking_data['tracking_number'] ) {
								$tracking_number ++;
							}
						}

						if ( $order_item->get_meta( '_vi_wad_aliexpress_order_id' ) ) {
							$ordered ++;
						}
						if ( $order_item->get_meta( '_vi_wad_aliexpress_order_item_status' ) == 'shipped' ) {
							$shipped ++;
						}
						$total ++;
					}
				}

				if ( $total && $ali_pid && ( ! $statuses || is_array( $statuses ) && in_array( 'wc-' . $order->get_status(), $statuses ) ) ) {

					$order_rate    = $ordered / $total;
					$tracking_rate = $tracking_number / $total;
					$shipped_rate  = $shipped / $total;

					$href   = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_to_order_aliexpress_url( $order_id, $ali_pid );
					$target = '_blank';

					if ( $shipped_rate == 1 ) {
						$order_stt = esc_html__( 'Shipped', 'woocommerce-alidropship' );
						$color     = 'shipped';
					} else {
						if ( $order_rate == 0 && $tracking_rate == 0 ) {
							$order_stt = esc_html__( 'To Order', 'woocommerce-alidropship' );
							$color     = 'to-order';
						} elseif ( $order_rate < 1 && $tracking_rate <= 1 ) {
							$order_stt = esc_html__( 'Processing', 'woocommerce-alidropship' );
							$color     = 'processing';
						} elseif ( $order_rate == 1 && $tracking_rate < 1 ) {
							$order_stt = esc_html__( 'Processing', 'woocommerce-alidropship' );
							$color     = 'full-processing';
							$href      = 'javascript:void(0)';
							$target    = '';
						} elseif ( $order_rate == 1 && $tracking_rate == 1 ) {
							$order_stt = esc_html__( 'In transit', 'woocommerce-alidropship' );
							$color     = 'completed';
							$href      = 'javascript:void(0)';
							$target    = '';
						}
					}

					$tooltip        = 'Light green: No order  &#xa;Orange: Not enough order & tracking code &#xa;Gray: Not enough tracking code &#xa;Light blue: Full tracking code';
					$fulfill_action = "<a data-tooltip='{$tooltip}' data-position='bottom center' data-inverted='' class='wad-fulfill-button' target='{$target}' href='" . esc_attr( $href ) . "'>" . $order_stt . "</a>";
					$status         = "<button type='button' class='wad-show-detail {$color}' data-id='{$order_id}'><i class='wad-icon dashicons dashicons-arrow-down wad-spinner'></i></button>"; //<span class='wad-shipped-status {$shipped_color}'>{$shipped_view} </span>
				}
			}

			echo "<div class='wad-fulfill-group {$color}'>" . $fulfill_action . $status . '</div>';
		}
	}

	/**
	 * @throws Exception
	 */
	public function get_ali_order_detail() {
		check_ajax_referer( 'woocommerce_alidropship_admin_ajax', '_vi_wad_ajax_nonce' );
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-ali-orders' ) ) ) {
			wp_die();
		}
		$order_id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
		if ( ! $order_id ) {
			wp_die();
		}

		$order       = wc_get_order( $order_id );
		$order_items = $order->get_items();
		$res         = '';

		if ( ! empty( $order_items ) ) {
			foreach ( $order_items as $item ) {
				$item_id           = $item->get_id();
				$item_data         = $item->get_data();
				$vid               = $item_data['variation_id'] ? $item_data['variation_id'] : $item_data['product_id'];
				$pid               = $item_data['product_id'];
				$name              = $item_data['name'];
				$ali_pid           = get_post_meta( $pid, '_vi_wad_aliexpress_product_id', true );
				$variation_product = wc_get_product( $vid );
				if ( ! $variation_product ) {
					continue;
				}
				$link               = $variation_product->get_permalink();
				$sku                = $variation_product->get_sku();
				$ali_order_id       = $item->get_meta( '_vi_wad_aliexpress_order_id' );
				$item_tracking_data = wc_get_order_item_meta( $item_id, '_vi_wot_order_item_tracking_data', true );
				$tracking_number    = '';
				$carrier_name       = '';
				$carrier_url        = '';
				if ( $item_tracking_data ) {
					$item_tracking_data    = vi_wad_json_decode( $item_tracking_data );
					$current_tracking_data = array_pop( $item_tracking_data );
					$current_tracking_data = apply_filters( 'vi_woo_alidropship_order_item_tracking_data', $current_tracking_data, $item_id, $order_id );
					$tracking_number       = $current_tracking_data['tracking_number'];
					$carrier_name          = $current_tracking_data['carrier_name'];
					$carrier_url           = $current_tracking_data['carrier_url'];
				}
				$status = $item->get_meta( '_vi_wad_aliexpress_order_item_status' );
				if ( $ali_pid ) {
					$color = $item_stt = $manual = '';
					if ( $status == 'shipped' ) {
						$item_stt = "<span class='wad-item-stt shipped'>" . esc_html__( 'Shipped', 'woocommerce-alidropship' ) . "</span>";
					} else {
						if ( ! $ali_order_id ) {
							$item_stt = "<span class='wad-item-stt to-order'>" . esc_html__( 'To order', 'woocommerce-alidropship' ) . "</span>";
						} elseif ( $ali_order_id && ! $tracking_number ) {
							$item_stt = "<span class='wad-item-stt processing'>" . esc_html__( 'Processing', 'woocommerce-alidropship' ) . "</span>";
						} elseif ( $ali_order_id && $tracking_number ) {
							$item_stt = "<span class='wad-item-stt completed'>" . esc_html__( 'In transit', 'woocommerce-alidropship' ) . "</span>";
						}
					}

					if ( ! $ali_order_id ) {
						$manual = '<a class="wad-manual-btn" href="' . VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $ali_pid ) . '" target="_blank">' . esc_html__( 'Manual', 'woocommerce-alidropship' ) . '</a>';
					}
					ob_start();
					?>
                    <div class='wad-ali-order-item <?php echo esc_attr( $color ) ?>'>
                        <div>
							<?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( "{$item_stt}{$manual}" ) ?>
                            <a class="wad-order-item-name"
                               href="<?php echo esc_url( $link ) ?>"><?php echo esc_html( $name ) ?></a>
                        </div>
                        <div>
                            <table class="wad-list-ali-order-items" item-id="<?php echo esc_attr( $item_id ) ?>">
                                <tr>
                                    <td>
										<?php
										esc_html_e( 'SKU: ', 'woocommerce-alidropship' );
										esc_html_e( $sku );
										?>
                                    </td>
                                    <td>
										<?php
										esc_html_e( 'Order ID: ', 'woocommerce-alidropship' );
										?>
                                        <a target="_blank"
                                           href="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_order_detail_url( $ali_order_id ) ) ?>"
                                           class="wad-ali-product-link"><?php echo( $ali_order_id ) ?></a>
                                        <input type="text" name="wad_ali_order_ID" class="wad-ali-order-id"
                                               value="<?php echo esc_attr( $ali_order_id ) ?>">
                                        <span data-tooltip="Save" data-inverted=''>
                                            <i class="wad-icon dashicons dashicons-yes"></i>
                                        </span>
                                        <span data-tooltip="Edit" data-inverted=''>
                                            <i class="wad-icon dashicons dashicons-edit"></i>
                                        </span>
                                    </td>
                                    <td class="wad-column">
										<?php
										echo esc_html__( 'Tracking code: ', 'woocommerce-alidropship' );
										if ( $carrier_url ) {
											?>
                                            <a href="<?php echo esc_url( $carrier_url ) ?>"
                                               target="_blank"><?php echo esc_html( $tracking_number ) ?></a>
											<?php
										} else {
											?>
                                            <span><?php echo esc_html( $tracking_number ) ?></span>
											<?php
										}
										if ( $ali_order_id ) {
											?>
                                            <a href="<?php echo esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_get_tracking_url( $ali_order_id ) ) ?>"
                                               target="_blank" class="wad-get-tracking-code-manual">
                                                <i class="dashicons dashicons-arrow-down-alt"></i>
                                            </a>
											<?php
										}
										?>
                                    </td>
                                    <td>
										<?php
										if ( $carrier_name ) {
											esc_html_e( 'Carrier: ', 'woocommerce-alidropship' );
											if ( $carrier_url ) {
												?>
                                                <a href="<?php echo esc_url( $carrier_url ) ?>"
                                                   target="_blank"><?php echo esc_html( $carrier_name ) ?></a>
												<?php
											} else {
												?>
                                                <span><?php echo esc_html( $carrier_name ) ?></span>
												<?php
											}
										}
										?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
					<?php
					$res .= ob_get_clean();
				}
			}
			if ( $res ) {
				wp_send_json_success( $res );
			} else {
				wp_send_json_error();
			}
		}
		wp_die();
	}

	public function filter_order() {
		if ( get_current_screen()->id === 'edit-shop_order' ) {
			$stt = '';
			if ( isset( $_GET['vi_wad_order_stt'] ) ) {
				$stt = esc_attr( sanitize_text_field( $_GET['vi_wad_order_stt'] ) );
			}
			$options = array(
				''           => esc_html__( 'Filter by AliExpress order status', 'woocommerce-alidropship' ),
				'pending'    => esc_html__( 'To Order', 'woocommerce-alidropship' ),
				'processing' => esc_html__( 'Processing', 'woocommerce-alidropship' ),
				'shipped'    => esc_html__( 'Shipped', 'woocommerce-alidropship' ),
			);
			?>
            <select name="vi_wad_order_stt" class="wad-order-filter">
				<?php
				foreach ( $options as $option => $show ) {
					$selected = selected( $stt, $option );
					echo "<option value='{$option}' {$selected}>{$show}</option>";
				}
				?>
            </select>
			<?php
		}
	}

	public function filter_where( $where, $wp_q ) {
		global $wpdb;
		if ( isset( $_GET['post_status'], $_GET['post_type'] ) && $_GET['post_status'] === 'ali_filter' && $_GET['post_type'] === 'shop_order' ) {
			$order_status_for_fulfill = self::$settings->get_params( 'order_status_for_fulfill' );
			$where                    .= " AND vi_wad_woocommerce_order_itemmeta.meta_key='_vi_wad_aliexpress_order_id' AND vi_wad_woocommerce_order_itemmeta.meta_value=''";
			if ( $order_status_for_fulfill ) {
				$where .= " AND wp_posts.post_status IN ( '" . implode( "','", $order_status_for_fulfill ) . "' )";
			}
			add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
			add_filter( 'posts_distinct', array( $this, 'posts_distinct' ), 10, 2 );
		}

		return $where;
	}

	public function ali_filter( $views ) {
		$views['ali_filter'] = "<a href='edit.php?post_status=ali_filter&post_type=shop_order'>" . esc_html__( 'To order', 'woocommerce-alidropship' ) . "</a>(" . VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_ali_orders() . ")";

		return $views;
	}

	public function order_ali_button( $order_id ) {
		$order       = new WC_Order( $order_id );
		$order_items = $order->get_items();
		$ali_pid     = '';

		if ( count( $order_items ) ) {
			foreach ( $order_items as $order_item ) {
				$pid            = $order_item->get_data()['product_id'];
				$ali_product_id = get_post_meta( $pid, '_vi_wad_aliexpress_product_id', true );
				if ( $ali_product_id ) {
					$ali_pid = $ali_product_id;
					break;
				}
			}
		}
		$statuses = self::$settings->get_params( 'order_status_for_fulfill' );
		if ( ! $statuses || ( is_array( $statuses ) && $ali_pid && in_array( 'wc-' . $order->get_status(), $statuses ) ) ) {
			$href = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_to_order_aliexpress_url( $order_id, $ali_pid );
			?>
            <li class="wide">
                <div class="vi-wad-ali-order-btn">
                    <a href="<?php echo esc_url( $href ); ?>" target="_blank" class="button">
						<?php esc_html_e( 'To Order AliExpress', 'woocommerce-alidropship' ); ?></a>
                </div>
            </li>
			<?php
		}
	}
}
