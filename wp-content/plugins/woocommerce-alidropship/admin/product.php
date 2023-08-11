<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Product
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Product {
	private static $settings;

	public function __construct() {
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
		add_action( 'deleted_post', array( $this, 'deleted_post' ) );
//		add_action( 'edit_form_top', array( $this, 'link_to_imported_page' ) );
		add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 20, 2 );
		add_action( 'woocommerce_product_after_variable_attributes', array(
			$this,
			'variation_add_aliexpress_variation_selection'
		), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'woocommerce_save_product_variation' ), 10, 2 );

		/*Need to check the case when removing attribute, variation is removed from _vi_wad_variations*/
		add_action( 'woocommerce_product_options_pricing', array(
			$this,
			'simple_add_aliexpress_variation_selection'
		), 99 );
		add_action( 'woocommerce_process_product_meta_simple', array(
			$this,
			'woocommerce_process_product_meta_simple'
		) );
		add_action( 'woocommerce_admin_process_product_object', array(
			$this,
			'woocommerce_admin_process_product_object'
		) );
		add_action( 'add_meta_boxes', array( $this, 'ald_product_info' ) );
	}

	/**
	 * Metabox that contains basic AliExpress product info
	 */
	public function ald_product_info() {
		global $pagenow, $post;
		if ( $pagenow === 'post.php' && $post ) {
			$product_id = $post->ID;
			if ( get_post_meta( $product_id, '_vi_wad_aliexpress_product_id', true ) ) {
				add_meta_box(
					'ald_product_info',
					esc_html__( 'AliExpress product info', 'woocommerce-alidropship' ),
					array( $this, 'add_meta_box_callback' ),
					'product',
					'side',
					'high'
				);
			}
		}
	}

	/**
	 *
	 */
	public static function add_meta_box_callback() {
		global $post;
		$product_id          = $post->ID;
		$ali_product_id      = get_post_meta( $product_id, '_vi_wad_aliexpress_product_id', true );
		$product_video_tab   = get_post_meta( $product_id, '_vi_wad_show_product_video_tab', true );
		$g_product_video_tab = self::$settings->get_params( 'show_product_video_tab' );
		?>
        <p>
			<?php printf( __( 'External ID: <a target="_blank" href="%s">%s</a>', 'woocommerce-alidropship' ), esc_url( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $ali_product_id ) ), $ali_product_id ) ?>
        </p>
		<?php
		$video = get_post_meta( $product_id, '_vi_wad_product_video', true );
		if ( $video ) {
			$video_tab = $g_product_video_tab ? esc_html__( 'Show', 'woocommerce-alidropship' ) : esc_html__( 'Hide', 'woocommerce-alidropship' );
			?>
            <p>
                <label for="vi-wad-show-product-video-tab"><?php esc_html_e( 'Product video tab: ', 'woocommerce-alidropship' ); ?></label>
                <select id="vi-wad-show-product-video-tab" name="_vi_wad_show_product_video_tab">
                    <option value=""><?php printf( esc_html__( 'Global setting(%s)', 'woocommerce-alidropship' ), $video_tab ); ?></option>
                    <option value="show" <?php selected( $product_video_tab, 'show' ) ?>><?php esc_html_e( 'Show', 'woocommerce-alidropship' ); ?></option>
                    <option value="hide" <?php selected( $product_video_tab, 'hide' ) ?>><?php esc_html_e( 'Hide', 'woocommerce-alidropship' ); ?></option>
                </select>
            </p>
            <div class="vi-wad-product-video-container"><?php echo do_shortcode( '[video src="' . $video . '"]' ); ?></div>
            <p><?php esc_html_e( 'Product video shortcode: ', 'woocommerce-alidropship' ); ?><input
                        title="<?php esc_attr_e( 'Click here to copy this shortcode', 'woocommerce-alidropship' ); ?>"
                        class="ald-video-shortcode" type="text" readonly
                        value="<?php echo esc_attr( '[ald_product_video product_id="' . $product_id . '"]' ); ?>">
            </p>
			<?php
		}
		?>
        <p class="vi-wad-view-original-product-button">
            <a href="<?php echo esc_url( admin_url( "admin.php?page=woocommerce-alidropship-imported-list&vi_wad_search_woo_id={$product_id}" ) ); ?>"
               target="_blank" class="button">
				<?php esc_html_e( 'View on Imported page', 'woocommerce-alidropship' ); ?></a>
        </p>
		<?php
	}

	/**
     *
	 * @param $product_id
	 */
	public function woocommerce_process_product_meta_simple( $product_id ) {
		$skuAttr = isset( $_POST['vi_wad_simple_variation_attr'] ) ? stripslashes( $_POST['vi_wad_simple_variation_attr'] ) : '';
		$skuId   = isset( $_POST['vi_wad_simple_variation_id'] ) ? stripslashes( $_POST['vi_wad_simple_variation_id'] ) : '';
		if ( $skuAttr ) {
			update_post_meta( $product_id, '_vi_wad_aliexpress_variation_attr', $skuAttr );
		}
		if ( $skuId ) {
			update_post_meta( $product_id, '_vi_wad_aliexpress_variation_id', $skuId );
		}
	}

	/**
	 * @param $product WC_Product
	 */
	public function woocommerce_admin_process_product_object( $product ) {
		if ( $product && isset( $_POST['_vi_wad_show_product_video_tab'] ) ) {
			update_post_meta( $product->get_id(), '_vi_wad_show_product_video_tab', sanitize_text_field( $_POST['_vi_wad_show_product_video_tab'] ) );
		}
	}

	/**
	 *
	 */
	public static function simple_add_aliexpress_variation_selection() {
		global $post;
		$product_id = $post->ID;
		if ( get_post_meta( $product_id, '_vi_wad_aliexpress_product_id', true ) ) {
			$from_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_woo_id( $product_id, false, false, array(
				'publish',
				'override'
			) );
			if ( $from_id ) {
				$variations = get_post_meta( $from_id, '_vi_wad_variations', true );
				$skuAttr    = get_post_meta( $product_id, '_vi_wad_aliexpress_variation_attr', true );
				if ( $skuAttr || count( $variations ) > 1 ) {
					$skuId = '';
					$id    = "vi-wad-original-attributes-simple-{$product_id}";
					?>
                    <p class="vi-wad-original-attributes vi-wad-original-attributes-simple form-field"><label
                                for="<?php echo esc_attr( $id ) ?>"><?php esc_html_e( 'Original AliExpress variation', 'woocommerce-alidropship' ); ?></label><?php echo wc_help_tip( __( 'If your customers buy this product, this selected AliExpress variation will be used when fulfilling AliExpress orders', 'woocommerce-alidropship' ) ) ?>
                        <select id="<?php echo esc_attr( $id ) ?>"
                                class="vi-wad-original-attributes-select"
                                name="vi_wad_simple_variation_attr">
                            <option value=""><?php esc_html_e( 'Please select original variation', 'woocommerce-alidropship' ); ?></option>
							<?php
							foreach ( $variations as $key => $value ) {
								$attr_name = $value['skuAttr'];
								if ( isset( $value['attributes_sub'] ) && count( $value['attributes_sub'] ) > count( $value['attributes'] ) ) {
									$attr_name = implode( ', ', $value['attributes_sub'] );
								} elseif ( count( $value['attributes'] ) ) {
									$attr_name = implode( ', ', $value['attributes'] );
								}
								$selected = selected( $value['skuAttr'], $skuAttr, false );
								if ( $selected ) {
									$skuId = $value['skuId'];
								}
								?>
                                <option value="<?php echo esc_attr( $value['skuAttr'] ) ?>"
                                        data-vi_wad_sku_id="<?php echo esc_attr( $value['skuId'] ) ?>" <?php echo esc_attr( $selected ) ?>><?php echo esc_html( $attr_name ) ?></option>
								<?php
							}
							?>
                        </select>
                        <input type="hidden" name="vi_wad_simple_variation_id" class="vi-wad-original-variation-id"
                               value="<?php echo esc_attr( $skuId ) ?>">
                    </p>
					<?php
				}
			}
		}
	}

	/**
	 * @param $variation_id
	 * @param $i
	 */
	public function woocommerce_save_product_variation( $variation_id, $i ) {
		$skuAttr = isset( $_POST['vi_wad_variation_attr'], $_POST['vi_wad_variation_attr'][ $i ] ) ? stripslashes( $_POST['vi_wad_variation_attr'][ $i ] ) : '';
		$skuId   = isset( $_POST['vi_wad_variation_id'], $_POST['vi_wad_variation_id'][ $i ] ) ? stripslashes( $_POST['vi_wad_variation_id'][ $i ] ) : '';
		if ( $skuAttr ) {
			update_post_meta( $variation_id, '_vi_wad_aliexpress_variation_attr', $skuAttr );
		}
		if ( $skuId ) {
			update_post_meta( $variation_id, '_vi_wad_aliexpress_variation_id', $skuId );
		}
	}

	/**
	 * @param $loop
	 * @param $variation_data
	 * @param $variation WP_Post
	 */
	public static function variation_add_aliexpress_variation_selection( $loop, $variation_data, $variation ) {
		global $post;
		$product_id = $post->ID;
		if ( $variation && get_post_meta( $product_id, '_vi_wad_aliexpress_product_id', true ) ) {
			$from_id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_woo_id( $product_id, false, false, array(
				'publish',
				'override'
			) );
			if ( $from_id ) {
				$variation_id = $variation->ID;
				$variations   = get_post_meta( $from_id, '_vi_wad_variations', true );
				$skuAttr      = get_post_meta( $variation_id, '_vi_wad_aliexpress_variation_attr', true );
				$skuId        = '';
				$id           = "vi-wad-original-attributes-{$variation_id}";
				?>
                <div class="vi-wad-original-attributes vi-wad-original-attributes-variable"><label
                            for="<?php echo esc_attr( $id ) ?>"><?php esc_html_e( 'Original AliExpress variation', 'woocommerce-alidropship' ); ?></label><?php echo wc_help_tip( __( 'If your customers buy this product, this selected AliExpress variation will be used when fulfilling AliExpress orders', 'woocommerce-alidropship' ) ) ?>
                    <select id="<?php echo esc_attr( $id ) ?>"
                            class="vi-wad-original-attributes-select"
                            name="vi_wad_variation_attr[<?php echo esc_attr( $loop ) ?>]">
						<?php
						if ( ! $skuAttr ) {
							?>
                            <option value=""><?php esc_html_e( 'Please select original variation', 'woocommerce-alidropship' ); ?></option>
							<?php
						}
						foreach ( $variations as $key => $value ) {
							$attr_name = $value['skuAttr'];
							if ( isset( $value['attributes_sub'] ) && count( $value['attributes_sub'] ) > count( $value['attributes'] ) ) {
								$attr_name = implode( ', ', $value['attributes_sub'] );
							} elseif ( count( $value['attributes'] ) ) {
								$attr_name = implode( ', ', $value['attributes'] );
							}
							$selected = selected( $value['skuAttr'], $skuAttr, false );
							if ( $selected ) {
								$skuId = $value['skuId'];
							}
							?>
                            <option value="<?php echo esc_attr( $value['skuAttr'] ) ?>"
                                    data-vi_wad_sku_id="<?php echo esc_attr( $value['skuId'] ) ?>" <?php echo esc_attr( $selected ) ?>><?php echo esc_html( $attr_name ) ?></option>
							<?php
						}
						?>
                    </select>
                    <input type="hidden" name="vi_wad_variation_id[<?php echo esc_attr( $loop ) ?>]"
                           class="vi-wad-original-variation-id" value="<?php echo esc_attr( $skuId ) ?>">
                </div>
				<?php
			}
		}
	}

	/**
	 * @param $actions
	 * @param $post
	 *
	 * @return mixed
	 */
	public function post_row_actions( $actions, $post ) {
		if ( $post && $post->post_type === 'product' && $post->post_status !== 'trash' ) {
			$ali_sku = get_post_meta( $post->ID, '_vi_wad_aliexpress_product_id', true );
			if ( $ali_sku ) {
				$actions['vi_wad_view_on_aliexpress']    = '<a href="' . VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_aliexpress_product_url( $ali_sku ) . '" title="' . esc_attr__( 'View product on AliExpress', 'woocommerce-alidropship' ) . '" target="_blank">' . esc_html__( 'View on AliExpress', 'woocommerce-alidropship' ) . '</a>';
				$href                                    = admin_url( "admin.php?page=woocommerce-alidropship-imported-list&vi_wad_search_woo_id={$post->ID}" );
				$actions['vi_wad_view_on_imported_page'] = '<a href="' . $href . '" title="' . esc_attr__( 'View product on Imported page', 'woocommerce-alidropship' ) . '" target="_blank">' . esc_html__( 'View on Imported', 'woocommerce-alidropship' ) . '</a>';
			}
		}

		return $actions;
	}

	/**
	 * @param $post
	 */
	public function link_to_imported_page( $post ) {
		if ( $post->post_type === 'product' && get_post_meta( $post->ID, '_vi_wad_aliexpress_product_id', true ) ) {
			$href = admin_url( "admin.php?page=woocommerce-alidropship-imported-list&vi_wad_search_woo_id={$post->ID}" );
			$link = "<a href='{$href}' target='_blank' class='page-title-action' style='margin-top:10px '>" . esc_html__( 'View on Imported page', 'woocommerce-alidropship' ) . "</a>";
			?>
            <script type="text/javascript">
                'use strict';
                jQuery(document).ready(function ($) {
                    let html = `<?php echo VI_WOOCOMMERCE_ALIDROPSHIP_DATA::wp_kses_post( $link )?>`;
                    $('.wp-header-end').before(html);
                });
            </script>
			<?php
		}
	}

	/**
     * Set a product status
	 *
	 * @param $product_id
	 * @param string $status
	 */
	public static function set_status( $product_id, $status = 'trash' ) {
		$ali_sku = get_post_meta( $product_id, '_vi_wad_aliexpress_product_id', true );
		if ( $ali_sku ) {
			if ( $status === 'publish' ) {
				$id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_woo_id( $product_id, false, false, 'trash' );
			} else {
				$id = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::product_get_id_by_woo_id( $product_id );
			}
			if ( $id ) {
				wp_update_post( array( 'ID' => $id, 'post_status' => $status ) );
			}
		}
	}

	/**
     * Set a product status to trash when a WC product is deleted
	 *
	 * @param $product_id
	 */
	public function deleted_post( $product_id ) {
		self::set_status( $product_id, 'trash' );
	}

	/**
     * Set a product status to trash when a WC product is trashed and set to publish when a trashed product is restored
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		if ( 'product' === $post->post_type ) {
			$product_id = $post->ID;
			if ( 'trash' === $new_status ) {
				self::set_status( $product_id );
			} elseif ( $old_status === 'trash' ) {
				self::set_status( $product_id, 'publish' );
			}
		}
	}

	/**
	 * @param $page
	 */
	public function admin_enqueue_scripts( $page ) {
		global $post_type;
		if ( $page === 'post.php' && $post_type === 'product' ) {
			wp_enqueue_script( 'woocommerce-alidropship-admin-edit-product', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'admin-product.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_enqueue_style( 'woocommerce-alidropship-admin-edit-product', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'admin-product.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_localize_script( 'woocommerce-alidropship-admin-edit-product', 'vi_wad_admin_product_params', array(
				'i18n_video_shortcode_copied' => esc_html__( 'Product video shortcode copied to clipboard. You can use it in product description, short description... to display video of this product', 'woocommerce-alidropship' ),
				'show_product_video_tab'      => self::$settings->get_params( 'show_product_video_tab' ),
			) );
		}
	}
}
