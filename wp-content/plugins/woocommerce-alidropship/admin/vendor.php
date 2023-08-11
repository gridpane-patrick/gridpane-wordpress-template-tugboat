<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compatible with vendors plugins
 * Currently supports Dokan plugin only
 *
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Vendor
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Vendor {
	protected static $settings;
	protected $params;
	protected static $ajax_nonce;

	public function __construct() {
		self::$settings   = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		self::$ajax_nonce = 'woocommerce_alidropship_admin_ajax_vendor';
		if ( self::$settings->get_params( 'enable' ) && self::enable_vendor_integration() ) {
			add_filter( 'wp_count_posts', array( $this, 'wp_count_posts' ), 10, 3 );
			add_filter( 'vi_wad_admin_sub_menu_capability', array( $this, 'vi_wad_admin_sub_menu_capability' ), 10, 2 );
			add_filter( 'vi_wad_admin_menu_capability', array( $this, 'vi_wad_admin_menu_capability' ), 10, 2 );
			add_filter( 'vi_wad_admin_access_full_settings_capability', array( $this, 'vi_wad_admin_access_full_settings_capability' ) );
			/*Show Import list, Imported and ALD settings in the seller dashboard*/
			add_filter( 'dokan_get_dashboard_nav', array( $this, 'dokan_get_dashboard_nav' ) );
			add_filter( 'dokan_query_var_filter', array( $this, 'dokan_query_var_filter' ) );
			add_action( 'dokan_load_custom_template', array( $this, 'dokan_load_custom_template' ) );
			add_action( 'dokan_dashboard_ald_settings', array( $this, 'dokan_dashboard_ald_settings' ) );
			add_action( 'dokan_dashboard_ald_import_list', array( $this, 'dokan_dashboard_ald_import_list' ) );
			add_action( 'dokan_dashboard_ald_imported_list', array( $this, 'dokan_dashboard_ald_imported_list' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ), 0 );
			add_action( 'admin_init', array( $this, 'save_user_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'vi_wad_import_list_tags_allow_addition', array( $this, 'tags_allow_addition' ) );
			add_action( 'vi_wad_import_list_search_form', array( $this, 'add_nonce_if_vendor' ) );
			add_action( 'vi_wad_imported_list_search_form', array( $this, 'add_nonce_if_vendor' ) );
			add_filter( 'vi_wad_import_list_page_url', array( $this, 'vi_wad_import_list_page_url' ) );
			add_filter( 'vi_wad_admin_ajax_nonce', array( $this, 'vi_wad_admin_ajax_nonce' ) );
			add_filter( 'vi_wad_verify_ajax_nonce', array( $this, 'vi_wad_verify_ajax_nonce' ), 10, 2 );
			add_filter( 'vi_wad_empty_import_list_sql', array( $this, 'restrict_sql_by_vendor' ) );
			add_filter( 'vi_wad_empty_trash_sql', array( $this, 'restrict_sql_by_vendor' ) );
			add_filter( 'vi_wad_ajax_search_products_query', array( $this, 'restrict_query_by_vendor' ) );
			add_filter( 'vi_wad_get_products_to_update_query', array( $this, 'restrict_query_by_vendor' ) );
			add_filter( 'vi_wad_import_list_button_view_edit_html', array( $this, 'vi_wad_import_list_button_view_edit_html' ), 10, 2 );
			add_action( 'vi_wad_import_list_product_information', array( $this, 'add_vendor_info' ) );
			add_action( 'vi_wad_imported_list_product_information', array( $this, 'add_vendor_info' ) );
			add_action( 'vi_wad_import_list_product_message', array( $this, 'add_vendor_settings_notice' ), 10, 3 );
			add_action( 'vi_wad_import_list_product_message', array( $this, 'dokan_lite_not_support_variable_notice' ), 10, 3 );
			add_action( 'init', array( $this, 'init' ), 0 );

			add_action( 'parse_request', array( $this, 'maybe_change_vendor_role' ), - 1 );
			add_action( 'parse_request', array( $this, 'maybe_restore_vendor_role' ), 1 );
			add_filter( 'vi_wad_auth_granted_url', array( $this, 'vi_wad_auth_granted_url' ) );
			add_filter( 'vi_wad_import_list_product_data', array( $this, 'vi_wad_import_list_product_data' ), 10, 2 );

			/*Apply pricing rules by each vendor even sync is run by admin*/
			add_action( 'vi_wad_before_sync_product', array( $this, 'before_sync_product' ) );
			add_action( 'vi_wad_after_sync_product', array( $this, 'after_sync_product' ) );
			add_filter( 'vi_wad_product_sync_email_headers', array( $this, 'maybe_send_bcc_of_email_to_vendor' ), 10, 4 );

			add_action( 'dokan_enqueue_scripts', array( $this, 'vendor_dashboard_general' ) );

			/*Vendor - edit product screen*/
			add_action( 'dokan_enqueue_scripts', array( $this, 'dokan_enqueue_scripts' ) );
			add_action( 'dokan_product_gallery_image_count', array( $this, 'ald_product_info' ) );
			add_action( 'dokan_product_edit_after_pricing', array( $this, 'dokan_product_edit_after_pricing' ), 10, 2 );
			add_action( 'dokan_product_after_variable_attributes', array( $this, 'dokan_product_after_variable_attributes' ), 10, 3 );
		}
		add_filter( 'vi_wad_rest_check_product_create_permission', array( $this, 'vi_wad_rest_check_product_permission' ) );
		add_filter( 'vi_wad_rest_check_product_edit_permission', array( $this, 'vi_wad_rest_check_product_permission' ) );
	}

	/**
	 * Do not allow vendors to import/sync ALD products anymore if Dokan compatible option is disabled
	 *
	 * @param $allow
	 *
	 * @return bool
	 */
	public function vi_wad_rest_check_product_permission( $allow ) {
		if ( class_exists( 'WeDevs_Dokan' ) && ! current_user_can( 'manage_woocommerce' ) && ! self::$settings->get_params( 'restrict_products_by_vendor' ) ) {
			$allow = false;
		}

		return $allow;
	}

	/**
	 * Status of products imported by vendors should be the same as "New Product Status" option in dokan(pro) settings
	 *
	 * @param $product_data
	 * @param $original_product_data
	 *
	 * @return mixed
	 */
	public function vi_wad_import_list_product_data( $product_data, $original_product_data ) {
		if ( self::is_vendor_active() ) {
			if ( class_exists( 'Dokan_Pro' ) ) {
				$product_data['status'] = dokan_get_option( 'product_status', 'dokan_selling' );
			}
		}

		return $product_data;
	}

	/**
	 * Whether to allow vendors to add new tags
	 *
	 * @param $allow
	 *
	 * @return bool
	 */
	public function tags_allow_addition( $allow ) {
		if ( self::is_ald_vendor_page() && dokan_get_option( 'product_vendors_can_create_tags', 'dokan_selling' ) !== 'on' ) {
			$allow = false;
		}

		return $allow;
	}

	/**
	 * AliExpress product's basic info showing to vendors
	 */
	public function ald_product_info() {
		global $post;
		if ( $post ) {
			?>
            <div class="ald-vendor-product-info">
                <h2><?php esc_html_e( 'AliExpress product info', 'woocommerce-alidropship' ); ?></h2>
				<?php
				VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Product::add_meta_box_callback();
				?>
                <p>
                    <a href="<?php echo esc_url( self::generate_vendor_dashboard_url( 'woocommerce-alidropship-imported-list', array( 'vi_wad_search_woo_id' => $post->ID ) ) ); ?>"
                       target="_blank" class="dokan-btn">
						<?php esc_html_e( 'View on Imported page', 'woocommerce-alidropship' ); ?></a>
                </p>
            </div>
			<?php
		}
	}

	/**
	 * Basic css for vendors in general
	 */
	public function vendor_dashboard_general() {
		if ( dokan_is_seller_dashboard() ) {
			wp_enqueue_style( 'woocommerce-alidropship-vendor-dashboard', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'vendor.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
		}
	}

	/**
	 * Dokan product edit page
	 */
	public function dokan_enqueue_scripts() {
		global $wp;
		if ( dokan_is_seller_dashboard() && isset( $wp->query_vars['products'] ) ) {
			$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
			if ( $action === 'edit' && ! empty( $_GET['product_id'] ) ) {
				wp_enqueue_script( 'woocommerce-alidropship-dokan-edit-product', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'dokan-product.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
				wp_localize_script( 'woocommerce-alidropship-dokan-edit-product', 'vi_wad_vendor_product_params', array(
					'i18n_video_shortcode_copied' => esc_html__( 'Product video shortcode copied to clipboard. You can use it in product description, short description... to display video of this product', 'woocommerce-alidropship' ),
				) );
			}
		}
	}

	/**
	 * Show AliExpress sku attr after attributes(variable products)
	 *
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 */
	public function dokan_product_after_variable_attributes( $loop, $variation_data, $variation ) {
		VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Product::variation_add_aliexpress_variation_selection( $loop, $variation_data, $variation );
	}

	/**
	 * Show AliExpress sku attr after price(simple product)
	 *
	 * @param $post
	 * @param $post_id
	 */
	public function dokan_product_edit_after_pricing( $post, $post_id ) {
		?>
        <div class="show_if_simple">
			<?php VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Product::simple_add_aliexpress_variation_selection(); ?>
        </div>
		<?php
	}

	/**
	 * Filter settings so that the sync will use a vendor's settings
	 *
	 * @param $product_ids
	 */
	public function before_sync_product( $product_ids ) {
		$ald_product_id = $product_ids['id'];
		$post           = get_post( $ald_product_id );
		if ( $post && self::is_vendor_active( $post->post_author ) ) {
			$this->change_global_settings( $post->post_author );
		}
	}

	/**
	 * Remove settings filter to not affect after-sync tasks
	 *
	 * @param $product_ids
	 */
	public function after_sync_product( $product_ids ) {
		remove_action( 'vi_wad_before_sync_product', array( $this, 'before_sync_product' ) );
	}

	/**
	 * Send notice to vendors if enabled
	 *
	 * @param $headers
	 * @param $email
	 * @param $received_email
	 * @param $product_ids
	 *
	 * @return string
	 */
	public function maybe_send_bcc_of_email_to_vendor( $headers, $email, $received_email, $product_ids ) {
		if ( self::$settings->get_params( 'send_bcc_email_to_vendor' ) ) {
			$ald_product_id = $product_ids['id'];
			$post           = get_post( $ald_product_id );
			if ( $post && self::is_vendor_active( $post->post_author ) ) {
				$user = new WP_User( $post->post_author );
				if ( $received_email !== $user->user_email ) {
					$headers .= "Bcc: {$user->display_name}<{$user->user_email}>\r\n";
				}
			}
		}

		return $headers;
	}

	/**
	 * Only change granted url if vendors cannot access the admin dashboard
	 *
	 * @param $granted_url
	 *
	 * @return string
	 */
	public function vi_wad_auth_granted_url( $granted_url ) {
		if ( self::is_vendor_active() ) {
			$no_access = dokan_get_option( 'admin_access', 'dokan_general', 'on' );
			if ( 'on' === $no_access ) {
				$query = parse_url( urldecode_deep( html_entity_decode( $granted_url ) ) )['query'];
				wp_parse_str( $query, $qs );

				if ( ! empty( $qs['return_url'] ) ) {
					$return_url  = add_query_arg( array(
						'vendor-dashboard' => 1,
						'vendor_nonce'     => wp_create_nonce( 'vendor-dashboard' )
					), $qs['return_url'] );
					$granted_url = add_query_arg( array(
						'return_url' => rawurlencode( $return_url ),
					), $granted_url );
				}
			}
		}

		return $granted_url;
	}

	/**
	 * Must assign manage_woocommerce capability to vendors to access "Auth form grant access"
	 */
	public function maybe_change_vendor_role() {
		add_filter( 'user_has_cap', array( $this, 'grant_manage_woocommerce_to_vendors' ), 10, 4 );
	}

	/**
	 * Remove manage_woocommerce capability of vendors
	 */
	public function maybe_restore_vendor_role() {
		remove_filter( 'user_has_cap', array( $this, 'grant_manage_woocommerce_to_vendors' ), 10 );
	}

	/**
	 * Filter vendors cap
	 *
	 * @param $allcaps
	 * @param $caps
	 * @param $args
	 * @param $user
	 *
	 * @return mixed
	 */
	public function grant_manage_woocommerce_to_vendors( $allcaps, $caps, $args, $user ) {
		if ( ! empty( $allcaps['dokan_edit_product'] ) && empty( $allcaps['manage_woocommerce'] ) ) {
			$allcaps['manage_woocommerce'] = 1;
		}

		return $allcaps;
	}

	/**
	 * Add vendor information if a product is imported by a vendor
	 *
	 * @param $product WP_Post
	 */
	public function add_vendor_info( $product ) {
		$post_author = $product->post_author;
		if ( $post_author && $post_author != get_current_user_id() ) {
			$vendor = dokan()->vendor->get( $post_author );
			if ( $vendor ) {
				?>
                <div class="vi-wad-accordion-product-vendor"><?php esc_html_e( 'Vendor:', 'woocommerce-alidropship' ) ?>
                    <span><?php echo esc_html( $vendor->get_name() ) ?></span></div>
				<?php
			}
		}
	}

	/**
	 * Show warning to admins so that they do not import a product that's added by a vendor
	 *
	 * @param $product WP_Post
	 * @param $override_product
	 * @param $is_variable
	 */
	public function add_vendor_settings_notice( $product, $override_product, $is_variable ) {
		$post_author = $product->post_author;
		if ( $post_author && $post_author != get_current_user_id() ) {
			$vendor = dokan()->vendor->get( $post_author );
			if ( $vendor ) {
				?>
                <div class="vi-ui message warning">
					<?php esc_html_e( 'This product was added by a vendor, please do not import it. Otherwise, it will not be assigned to the vendor anymore after importing.', 'woocommerce-alidropship' ) ?>
                </div>
				<?php
			}
		}
	}

	/**
	 * Add warning that Dokan lite does not support variable products
	 *
	 * @param $product WP_Post
	 * @param $override_product
	 * @param $is_variable
	 */
	public function dokan_lite_not_support_variable_notice( $product, $override_product, $is_variable ) {
		if ( $is_variable && self::is_ald_vendor_page() && ! class_exists( 'Dokan_Pro' ) ) {
			?>
            <div class="vi-ui message warning">
				<?php esc_html_e( 'You will not be able to import variable products because Dokan lite only supports simple products.', 'woocommerce-alidropship' ) ?>
            </div>
			<?php
		}
	}

	/**
	 * Change link of Edit product button to vendor dashboard
	 *
	 * @param $html
	 * @param $woo_product_id
	 *
	 * @return false|string
	 */
	public function vi_wad_import_list_button_view_edit_html( $html, $woo_product_id ) {
		if ( self::is_ald_vendor_page() ) {
			ob_start();
			?>
            <a href="<?php echo esc_url( get_post_permalink( $woo_product_id ) ) ?>"
               target="_blank" class="vi-ui mini button labeled icon"
               rel="nofollow"><i class="icon eye"></i><?php esc_html_e( 'View', 'woocommerce-alidropship' ); ?></a>
            <a href="<?php echo esc_url( self::generate_vendor_dashboard_url( 'products', array(
				'product_id' => $woo_product_id,
				'action'     => 'edit'
			) ) ) ?>"
               target="_blank" class="vi-ui mini button labeled icon primary"
               rel="nofollow"><i class="icon edit"></i><?php esc_html_e( 'Edit', 'woocommerce-alidropship' ) ?></a>
			<?php
			$html = ob_get_clean();
		}

		return $html;
	}

	/**
	 * To make sure some SQL queries return results from current vendor
	 *
	 * @param $sql
	 *
	 * @return string
	 */
	public function restrict_sql_by_vendor( $sql ) {
		if ( self::is_vendor_active() ) {
			$sql .= " AND post_author='" . get_current_user_id() . "'";
		}

		return $sql;
	}

	/**
	 * To make sure some WP queries return results from current vendor
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function restrict_query_by_vendor( $args ) {
		if ( self::is_vendor_active() ) {
			$args['author'] = get_current_user_id();
		}

		return $args;
	}

	/**
	 * Verify ajax nonce for vendors
	 *
	 * @param $verify
	 * @param $page
	 *
	 * @return bool
	 */
	public function vi_wad_verify_ajax_nonce( $verify, $page ) {
		if ( self::is_ald_vendor_page() && self::is_vendor_active() ) {
			$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';
			switch ( $page ) {
				case 'woocommerce-alidropship':
					if ( in_array( $action, array(
						'wad_format_price_rules_test',
						'wad_search_product',
						'wad_search_cate',
						'wad_search_tags',
						'wad_get_custom_rule_html',
					) ) ) {
						$verify = true;
					}
					break;
				case 'woocommerce-alidropship-import-list':
					$verify = true;
					if ( in_array( $action, array(
						'vi_wad_import',
						'vi_wad_override'
					) ) ) {
						parse_str( $_POST['form_data'], $form_data );
						$data       = isset( $form_data['vi_wad_product'] ) ? $form_data['vi_wad_product'] : array();
						$product_id = array_keys( $data )[0];
						$verify     = self::current_vendor_can( 'edit_product', $product_id );
						if ( $verify && ! class_exists( 'Dokan_Pro' ) ) {
							add_action( 'vi_wad_import_list_before_import', array(
								$this,
								'do_not_import_variable_if_free'
							) );
						}
					} elseif ( in_array( $action, array(
						'vi_wad_save_attributes',
						'vi_wad_remove_attribute',
					) ) ) {
						parse_str( $_POST['form_data'], $form_data );
						$data       = isset( $form_data['vi_wad_product'] ) ? $form_data['vi_wad_product'] : array();
						$product_id = array_keys( $data )[0];
						$verify     = self::current_vendor_can( 'edit_product', $product_id );
					} else {
						$product_id = isset( $_REQUEST['product_id'] ) ? sanitize_text_field( $_REQUEST['product_id'] ) : '';
						if ( in_array( $action, array(
							'vi_wad_remove',
						) ) ) {
							$verify = self::current_vendor_can( 'delete_product', $product_id );
						} elseif ( in_array( $action, array(
							'vi_wad_split_product',
							'vi_wad_load_variations_table',
							'vi_wad_select_shipping',
						) ) ) {
							$verify = self::current_vendor_can( 'edit_product', $product_id );
						}
					}
					break;
				case 'woocommerce-alidropship-imported-list':
					$verify     = true;
					$product_id = isset( $_REQUEST['product_id'] ) ? sanitize_text_field( $_REQUEST['product_id'] ) : '';
					if ( in_array( $action, array(
						'vi_wad_trash_product',
						'vi_wad_delete_product',
					) ) ) {
						$verify = self::current_vendor_can( 'delete_product', $product_id );
					} elseif ( in_array( $action, array(
						'vi_wad_override_product',
						'vi_wad_restore_product',
					) ) ) {
						$verify = self::current_vendor_can( 'edit_product', $product_id );
					}
					break;
				default:
			}
		}

		return $verify;
	}

	/**
	 * @param $capability
	 * @param $object_id
	 *
	 * @return bool
	 */
	public static function current_vendor_can( $capability, $object_id ) {
		$can    = false;
		$object = get_post( $object_id );
		if ( $object ) {
			if ( $object->post_author ) {
				if ( current_user_can( $capability, $object_id ) || $object->post_author == get_current_user_id() ) {
					$can = true;
				}
			}
		}

		return $can;
	}

	/**
	 * Do not allow importing variable products if Dokan pro is not active because Dokan lite only support simple products
	 *
	 * @param $product_data
	 */
	public function do_not_import_variable_if_free( $product_data ) {
		$attributes = $product_data['attributes'];
		$variations = $product_data['variations'];
		if ( is_array( $attributes ) && count( $attributes ) && ( count( $variations ) > 1 || ! self::$settings->get_params( 'simple_if_one_variation' ) ) ) {
			wp_send_json( array(
				'status'     => 'error',
				'message'    => esc_html__( 'You cannot import this product because Dokan lite does not support variable products', 'woocommerce-alidropship' ),
				'product_id' => '',
			) );
		}
	}

	/**
	 * Filter admin ajax nonce
	 *
	 * @param $nonce
	 *
	 * @return false|string
	 */
	public function vi_wad_admin_ajax_nonce( $nonce ) {
		if ( self::is_ald_vendor_page() ) {
			$nonce = wp_create_nonce( self::$ajax_nonce );
		}

		return $nonce;
	}

	/**
	 * Change link to Import list from vendor dashboard
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function vi_wad_import_list_page_url( $url ) {
		if ( self::is_ald_vendor_page() ) {
			$url = self::generate_vendor_page_url( 'woocommerce-alidropship-import-list' );
		}

		return $url;
	}

	/**
	 * Add nonce for search form
	 */
	public function add_nonce_if_vendor() {
		if ( self::is_ald_vendor_page() ) {
			?>
            <input type="hidden" name="vendor-dashboard" value="1">
            <input type="hidden" name="vendor_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'vendor-dashboard' ) ) ?>">
			<?php
		}
	}

	/**
	 * Change global settings according to a vendor's settings
	 */
	public function init() {
		$disable_vendor_setting = self::$settings->get_params( 'disable_vendor_setting' );

		if ( self::is_ald_vendor_page() && ! $disable_vendor_setting ) {
			$this->change_global_settings();
		}
	}

	public function change_global_settings( $user_id = '' ) {
		$params = self::$settings->get_params();
		self::$settings->set_params( wp_parse_args( $this->get_user_settings( '', $user_id ), $params ) );
	}

	/**
	 * Get ALD settings by vendor
	 *
	 * @param string $name
	 * @param string $user_id
	 * @param string $language
	 *
	 * @return array|bool|mixed|void
	 */
	public function get_user_settings( $name = '', $user_id = '', $language = '' ) {
		if ( $this->params === null ) {
			$args = self::$settings->get_params();
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}
			$this->params = get_user_meta( $user_id, '_vi_wad_settings', true );
			if ( ! is_array( $this->params ) ) {
				$this->params = array();
			}
			$default = self::get_vendor_default_params();

			foreach ( $default as $key => $value ) {
				$default[ $key ] = $args[ $key ];
			}
			if ( class_exists( 'Dokan_Pro' ) ) {
				$default['product_status'] = dokan_get_option( 'product_status', 'dokan_selling' );
			}
			$this->params = wp_parse_args( $this->params, $default );
		}
		if ( ! $name ) {
			return $this->params;
		} else {
			if ( isset( $this->params[ $name ] ) ) {
				if ( $language ) {
					$name_language = $name . '_' . $language;
					if ( isset( $this->params[ $name_language ] ) ) {
						return apply_filters( 'vi_wad_vendor_settings-' . $name_language, $this->params[ $name_language ] );
					} else {
						return apply_filters( 'vi_wad_vendor_settings-' . $name_language, $this->params[ $name ] );
					}
				} else {
					return apply_filters( 'vi_wad_vendor_settings-' . $name, $this->params[ $name ] );
				}
			} else {
				return false;
			}
		}
	}

	/**
	 * All settings that vendors can have their own
	 *
	 * @return array
	 */
	private static function get_vendor_default_params() {
		$default = wp_parse_args( self::$settings->get_product_params(), self::$settings->get_product_sync_params() );
		foreach (
			array(
				'update_product_auto',
				'update_product_interval',
				'update_product_hour',
				'update_product_minute',
				'update_product_second',
				'update_product_http_only',
				'received_email',
				'send_email_if',
			) as $key
		) {
			unset( $default[ $key ] );
		}

		return $default;
	}

	/**
	 * Enqueue extra scripts if is vendor
	 */
	public function admin_enqueue_scripts() {
		if ( self::is_ald_vendor_page() ) {
			wp_enqueue_script( 'woocommerce-alidropship-vendor', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'ald-vendor.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			wp_enqueue_style( 'woocommerce-alidropship-vendor', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'ald-vendor.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
			if ( class_exists( 'Dokan_Pro' ) ) {
				$css = '.vi-wad-product-status-container,
.field.vi-wad-import-data-status-container {
    display: none;
}';
				wp_add_inline_style( 'woocommerce-alidropship-vendor', $css );
			}
			/*Query monitor causes js error here so dequeue it*/
			wp_dequeue_script( 'query-monitor' );
		}
	}

	/**
	 * Check if a page is from a vendor
	 *
	 * @param string $check_page
	 *
	 * @return bool
	 */
	public static function is_ald_vendor_page( $check_page = '' ) {
		global $pagenow;
		$page   = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		$_nonce = isset( $_REQUEST['vendor_nonce'] ) ? $_REQUEST['vendor_nonce'] : '';
		$valid  = true;
		if ( wp_doing_ajax() ) {
			$_vi_wad_ajax_nonce = isset( $_REQUEST['_vi_wad_ajax_nonce'] ) ? $_REQUEST['_vi_wad_ajax_nonce'] : '';
			if ( ! wp_verify_nonce( $_vi_wad_ajax_nonce, self::$ajax_nonce ) ) {
				$valid = false;
			}
		} else {
			if ( $pagenow !== 'admin.php' ) {
				$valid = false;
			}
			if ( ! in_array( $page, array(
				'woocommerce-alidropship',
				'vi-woocommerce-alidropship-auth',
				'woocommerce-alidropship-import-list',
				'woocommerce-alidropship-imported-list'
			) ) ) {
				$valid = false;
			} elseif ( $check_page ) {
				if ( $check_page !== $page ) {
					$valid = false;
				}
			}
			if ( empty( $_REQUEST['vendor-dashboard'] ) ) {
				$valid = false;
			}
			if ( ! wp_verify_nonce( $_nonce, 'vendor-dashboard' ) ) {
				$valid = false;
			}
		}

		return $valid;
	}

	/**
	 * Redirect if vendors cannot access the admin dashboard
	 * This override the default function by Dokan
	 */
	public function admin_init() {
		global $pagenow, $current_user;
		vi_wad_remove_filter( 'admin_init', 'WeDevs\Dokan\Core', 'block_admin_access' );

		// bail out if we are from WP Cli
		if ( defined( 'WP_CLI' ) ) {
			return;
		}

		$no_access   = dokan_get_option( 'admin_access', 'dokan_general', 'on' );
		$valid_pages = array( 'admin-ajax.php', 'admin-post.php', 'async-upload.php', 'media-upload.php' );
		$user_role   = reset( $current_user->roles );

		if ( ( 'on' === $no_access ) && ( ! in_array( $pagenow, $valid_pages ) ) && in_array( $user_role, array(
				'seller',
				'customer',
				'vendor_staff'
			) ) ) {
			if ( ! self::is_ald_vendor_page() || ( ! self::is_vendor_active() && self::is_ald_vendor_page( 'woocommerce-alidropship' ) ) ) {
				wp_redirect( home_url() );
				exit;
			}
		}
	}

	/**
	 * Save settings of vendors to user meta
	 */
	public function save_user_settings() {
		if ( self::is_ald_vendor_page() && self::is_vendor_active() && isset( $_POST['_wooaliexpressdropship_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['_wooaliexpressdropship_nonce'] ), 'wooaliexpressdropship_save_settings_vendor' ) ) {
			$user_id       = get_current_user_id();
			$user_settings = self::get_vendor_default_params();
			$params        = self::$settings->get_params();
			foreach ( $user_settings as $key => $arg ) {
				if ( isset( $_POST[ 'wad_' . $key ] ) ) {
					if ( is_array( $_POST[ 'wad_' . $key ] ) ) {
						$user_settings[ $key ] = stripslashes_deep( $_POST[ 'wad_' . $key ] );
					} else {
						$user_settings[ $key ] = sanitize_text_field( stripslashes( $_POST[ 'wad_' . $key ] ) );
					}
				} else {
					if ( is_array( $arg ) ) {
						$user_settings[ $key ] = array();
					} else {
						$user_settings[ $key ] = '';
					}
				}
			}
			/*Status should be the same as "New Product Status" in dokan settings*/
			if ( class_exists( 'Dokan_Pro' ) ) {
				$user_settings['product_status'] = dokan_get_option( 'product_status', 'dokan_selling' );
			}
			/*Adjust custom rules*/
			$user_settings['update_product_custom_rules'] = array_values( $user_settings['update_product_custom_rules'] );
			foreach ( $user_settings['update_product_custom_rules'] as &$custom_rule ) {
				$custom_rule = array_merge( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_default_custom_rules(), $custom_rule );
			}
			/*Format price rules*/
			$format_price_rules = array();
			if ( ! empty( $user_settings['format_price_rules']['from'] ) && is_array( $user_settings['format_price_rules']['from'] ) ) {
				for ( $i = 0; $i < count( $user_settings['format_price_rules']['from'] ); $i ++ ) {
					$format_price_rules[] = array(
						'from'       => $user_settings['format_price_rules']['from'][ $i ],
						'to'         => $user_settings['format_price_rules']['to'][ $i ],
						'part'       => $user_settings['format_price_rules']['part'][ $i ],
						'value_from' => $user_settings['format_price_rules']['value_from'][ $i ],
						'value_to'   => $user_settings['format_price_rules']['value_to'][ $i ],
						'value'      => $user_settings['format_price_rules']['value'][ $i ],
					);
				}
				$user_settings['format_price_rules'] = $format_price_rules;
			}
			update_user_meta( $user_id, '_vi_wad_settings', $user_settings );
			self::$settings->set_params( wp_parse_args( $user_settings, $params ) );
			$per_page = array(
				'per_page'          => 'import_list_per_page',
				'imported_per_page' => 'imported_per_page',
			);
			foreach ( $per_page as $per_page_k => $per_page_v ) {
				if ( ! empty( $_POST["wad_{$per_page_v}"] ) ) {
					update_user_meta( $user_id, "vi_wad_{$per_page_k}", $_POST["wad_{$per_page_v}"] );
				}
			}
		}
	}

	/**
	 * Page content in vendors dashboard
	 */
	public function admin_head() {
		if ( self::is_ald_vendor_page( 'woocommerce-alidropship' ) && self::is_vendor_active() ) {
			remove_all_actions( 'villatheme_support_woocommerce-alidropship' );
			?>
            <div class="wp-core-ui woocommerce-alidropship-vendor">
				<?php
				$disable_vendor_setting = self::$settings->get_params( 'disable_vendor_setting' );
				if ( ! $disable_vendor_setting ) {
					VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::settings_page_html( false );
				}
				?>
                <div class="woocommerce-alidropship-vendor-overlay vi-wad-hidden"></div>
            </div>
			<?php
			exit;
		} elseif ( self::is_ald_vendor_page( 'woocommerce-alidropship-import-list' ) ) {
			?>
            <div class="wp-core-ui woocommerce-alidropship-vendor">
				<?php
				VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::import_list_html( false );
				VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::set_price_modal();
				VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Import_List::override_product_options();
				?>
                <div class="woocommerce-alidropship-vendor-overlay vi-wad-hidden"></div>
            </div>
			<?php
			/*Required for wp_editor scripts*/
			do_action( 'admin_print_footer_scripts' );
			exit;
		} elseif ( self::is_ald_vendor_page( 'woocommerce-alidropship-imported-list' ) ) {
			?>
            <div class="wp-core-ui woocommerce-alidropship-vendor">
				<?php
				VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Imported::imported_list_html( false );
				VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Imported::delete_product_options();
				?>
                <div class="woocommerce-alidropship-vendor-overlay vi-wad-hidden"></div>
            </div>
			<?php
			exit;
		} elseif ( self::is_ald_vendor_page( 'vi-woocommerce-alidropship-auth' ) && self::is_vendor_active() ) {
			?>
            <div class="wp-core-ui woocommerce-alidropship-vendor">
				<?php
				VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Auth::page_callback();
				?>
                <div class="woocommerce-alidropship-vendor-overlay vi-wad-hidden"></div>
            </div>
			<?php
			exit;
		}
	}

	/**
	 * Allow vendors to access ALD settings page with some necessary options to import products
	 *
	 * @param $capability
	 * @param $menu_slug
	 *
	 * @return string
	 */
	public function vi_wad_admin_menu_capability( $capability, $menu_slug ) {
		if ( self::is_ald_vendor_page( $menu_slug ) && current_user_can( 'dokan_edit_product' ) && $menu_slug === 'woocommerce-alidropship' ) {
			$capability = 'dokan_edit_product';
		}

		return $capability;
	}

	/**
	 * Vendors are not allowed to access all settings
	 *
	 * @param $capability
	 *
	 * @return string
	 */
	public function vi_wad_admin_access_full_settings_capability( $capability ) {
		if ( current_user_can( 'dokan_edit_product' ) ) {
			$capability = 'manage_options';
		}

		return $capability;
	}

	/**
	 * Allow vendors to access Import list, Imported and Auth page
	 *
	 * @param $capability
	 * @param $menu_slug
	 *
	 * @return string
	 */
	public function vi_wad_admin_sub_menu_capability( $capability, $menu_slug ) {
		if ( current_user_can( 'dokan_edit_product' ) && in_array( $menu_slug, array(
				'vi-woocommerce-alidropship-auth',
				'woocommerce-alidropship-import-list',
				'woocommerce-alidropship-imported-list'
			) ) ) {
			$capability = 'dokan_edit_product';
		}

		return $capability;
	}

	/**
	 * Show ALD pages in vendors dashboard
	 *
	 * @param $urls
	 *
	 * @return mixed
	 */
	public function dokan_get_dashboard_nav( $urls ) {
		$disable_vendor_setting = self::$settings->get_params( 'disable_vendor_setting' );

		if ( self::is_vendor_active() && ! $disable_vendor_setting ) {
			$urls['ald_settings'] = array(
				'title' => __( 'ALD Settings', 'woocommerce-alidropship' ),
				'icon'  => '<img class="ald-plugin-icon-in-vendor-dashboard" src="' . VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES . 'icon.png' . '">',
				'url'   => dokan_get_navigation_url( 'ald_settings' ),
				'pos'   => 71
			);
		}
		$urls['ald_import_list']   = array(
			'title' => __( 'ALD Import List', 'woocommerce-alidropship' ),
			'icon'  => '<img class="ald-plugin-icon-in-vendor-dashboard" src="' . VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES . 'icon.png' . '">',
			'url'   => dokan_get_navigation_url( 'ald_import_list' ),
			'pos'   => 71
		);
		$urls['ald_imported_list'] = array(
			'title' => __( 'ALD Imported', 'woocommerce-alidropship' ),
			'icon'  => '<img class="ald-plugin-icon-in-vendor-dashboard" src="' . VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES . 'icon.png' . '">',
			'url'   => dokan_get_navigation_url( 'ald_imported_list' ),
			'pos'   => 71
		);

		return $urls;
	}

	/**
	 * Show ALD pages in vendors dashboard
	 *
	 * @param $query_vars
	 *
	 * @return mixed
	 */
	public function dokan_query_var_filter( $query_vars ) {
		$query_vars['ald_settings']      = 'ald_settings';
		$query_vars['ald_import_list']   = 'ald_import_list';
		$query_vars['ald_imported_list'] = 'ald_imported_list';

		return $query_vars;
	}

	/**
	 * Show ALD pages in vendors dashboard
	 *
	 * @param $query_vars
	 */
	public function dokan_load_custom_template( $query_vars ) {
		if ( isset( $query_vars['ald_settings'] ) ) {
			require_once VI_WOOCOMMERCE_ALIDROPSHIP_TEMPLATES . 'dokan/settings.php';
		}
		if ( isset( $query_vars['ald_import_list'] ) ) {
			require_once VI_WOOCOMMERCE_ALIDROPSHIP_TEMPLATES . 'dokan/import-list.php';
		}
		if ( isset( $query_vars['ald_imported_list'] ) ) {
			require_once VI_WOOCOMMERCE_ALIDROPSHIP_TEMPLATES . 'dokan/imported-list.php';
		}
	}

	/**
	 * Show ALD pages via iframe
	 */
	public function dokan_dashboard_ald_settings() {
		$url = self::generate_vendor_page_url( 'woocommerce-alidropship' );
		?>
        <iframe width="100%" height="800px"
                src="<?php echo esc_url( $url ) ?>"></iframe>
		<?php
	}

	/**
	 * Show ALD pages via iframe
	 */
	public function dokan_dashboard_ald_import_list() {
		$url = self::generate_vendor_page_url( 'woocommerce-alidropship-import-list' );
		?>
        <iframe width="100%" height="800px"
                src="<?php echo esc_url( $url ) ?>"></iframe>
		<?php
	}

	/**
	 * Show ALD pages via iframe
	 */
	public function dokan_dashboard_ald_imported_list() {
		$url = self::generate_vendor_page_url( 'woocommerce-alidropship-imported-list' );
		?>
        <iframe width="100%" height="800px"
                src="<?php echo esc_url( $url ) ?>"></iframe>
		<?php
	}

	/**
	 * ALD pages from vendors dashboard
	 *
	 * @param string $page
	 * @param array $args
	 *
	 * @return string
	 */
	public static function generate_vendor_page_url( $page = 'woocommerce-alidropship', $args = array() ) {
		$url = add_query_arg( array_merge( $args, array(
			'page'             => $page,
			'vendor-dashboard' => 1,
			'vendor_nonce'     => wp_create_nonce( 'vendor-dashboard' ),
		) ), admin_url( 'admin.php' ) );
		if ( $page === 'woocommerce-alidropship-imported-list' && ! empty( $_GET['vi_wad_search_woo_id'] ) ) {
			$url = add_query_arg( array(
				'vi_wad_search_woo_id' => sanitize_text_field( $_GET['vi_wad_search_woo_id'] ),
			), $url );
		}

		return $url;
	}

	/**
	 * ALD vendor dashboard pages
	 *
	 * @param string $page
	 * @param array $args
	 *
	 * @return string
	 */
	public static function generate_vendor_dashboard_url( $page = 'woocommerce-alidropship', $args = array() ) {
		$dashboard = dokan_get_option( 'dashboard', 'dokan_pages' );
		$url       = '';
		if ( $dashboard ) {
			switch ( $page ) {
				case 'woocommerce-alidropship-import-list':
					$slug = 'ald_import_list';
					break;
				case 'woocommerce-alidropship-imported-list':
					$slug = 'ald_imported_list';
					break;
				case 'woocommerce-alidropship':
					$slug = 'ald_settings';
					break;
				default:
					$slug = $page;
			}
			$url = add_query_arg( $args, trailingslashit( get_permalink( $dashboard ) ) . $slug );
		}

		return $url;
	}

	/**
	 * Only count products imported by a vendor themselves
	 *
	 * @param $counts
	 * @param $type
	 * @param $perm
	 *
	 * @return array|bool|false|mixed|object|void
	 */
	public function wp_count_posts( $counts, $type, $perm ) {
		global $wpdb;

		if ( $type === 'vi_wad_draft_product' && ! current_user_can( 'manage_options' ) && current_user_can( 'dokan_edit_product' ) ) {
			$user      = wp_get_current_user();
			$cache_key = _count_posts_cache_key( $type, $perm ) . '_' . $user->ID;

			$counts = wp_cache_get( $cache_key, 'counts' );
			if ( false !== $counts ) {
				// We may have cached this before every status was registered.
				foreach ( get_post_stati() as $status ) {
					if ( ! isset( $counts->{$status} ) ) {
						$counts->{$status} = 0;
					}
				}

				/** This filter is documented in wp-includes/post.php */
				return $counts;
			}

			$query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d  GROUP BY post_status";

			$results = (array) $wpdb->get_results( $wpdb->prepare( $query, $type, $user->ID ), ARRAY_A );
			$counts  = array_fill_keys( get_post_stati(), 0 );

			foreach ( $results as $row ) {
				$counts[ $row['post_status'] ] = $row['num_posts'];
			}

			$counts = (object) $counts;
			wp_cache_set( $cache_key, $counts, 'counts' );
		}

		return $counts;
	}

	/**
	 * Check if Dokan is active and vendor integration is enabled
	 *
	 * @return bool
	 */
	public static function enable_vendor_integration() {
		return self::$settings->get_params( 'restrict_products_by_vendor' ) && class_exists( 'WeDevs_Dokan' );
	}

	/**
	 * Check if current user is an active vendor
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public static function is_vendor_active( $id = '' ) {
		$active = false;
		if ( self::enable_vendor_integration() ) {
			if ( $id ) {

			} elseif ( is_user_logged_in() ) {
				$id = get_current_user_id();
			}
			$vendor = dokan()->vendor->get( $id );

			return apply_filters( 'vi_wad_is_vendor_active', $vendor->is_enabled(), $vendor, $id );
		}

		return $active;
	}
}
