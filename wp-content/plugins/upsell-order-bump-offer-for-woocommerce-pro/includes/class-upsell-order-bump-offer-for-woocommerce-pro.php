<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wpswings.com/?utm_source=wpswings-official&utm_medium=order-bump-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    Upsell-Order-Bump-Offer-For-Woocommerce-Pro
 * @subpackage    Upsell-Order-Bump-Offer-For-Woocommerce-Pro/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Upsell-Order-Bump-Offer-For-Woocommerce-Pro
 * @subpackage    Upsell-Order-Bump-Offer-For-Woocommerce-Pro/includes
 * @author     WP Swings<webmaster@wpswings.com>
 */
class Upsell_Order_Bump_Offer_For_Woocommerce_Pro {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @var      Upsell_Order_Bump_Offer_For_Woocommerce_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_VERSION' ) ) {

			$this->version = UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_VERSION;

		} else {

			$this->version = '1.0.0';
		}

		$this->plugin_name = 'upsell-order-bump-offer-for-woocommerce-pro';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_premium_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Upsell_Order_Bump_Offer_For_Woocommerce_Pro_Loader. Orchestrates the hooks of the plugin.
	 * - Upsell_Order_Bump_Offer_For_Woocommerce_Pro_I18n. Defines internationalization functionality.
	 * - Upsell_Order_Bump_Offer_For_Woocommerce_Pro_Admin. Defines all hooks for the admin area.
	 * - Upsell_Order_Bump_Offer_For_Woocommerce_Pro_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-upsell-order-bump-offer-for-woocommerce-pro-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-upsell-order-bump-offer-for-woocommerce-pro-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-upsell-order-bump-offer-for-woocommerce-pro-admin.php';

		$this->loader = new Upsell_Order_Bump_Offer_For_Woocommerce_Pro_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Upsell_Order_Bump_Offer_For_Woocommerce_Pro_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function set_locale() {

		$plugin_i18n = new Upsell_Order_Bump_Offer_For_Woocommerce_Pro_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Upsell_Order_Bump_Offer_For_Woocommerce_Pro_Admin( $this->get_plugin_name(), $this->get_version() );

		// Validate License Key.
		$this->loader->add_action( 'wp_ajax_wps_upsell_bump_validate_license_key', $plugin_admin, 'wps_upsell_bump_validate_license_key' );

		// Check daily that license is correct.
		$this->loader->add_action( 'wps_upsell_bump_check_license_hook', $plugin_admin, 'wps_upsell_bump_check_license' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'license_redirect_pathvia_notice' );

		// Validate Free version compatibility.
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'validate_version_compatibility' );

		$wps_upsell_bump_callname_lic = self::$wps_upsell_bump_lic_callback_function;

		$wps_upsell_bump_callname_lic_initial = self::$wps_upsell_bump_lic_ini_callback_function;

		$day_count = self::$wps_upsell_bump_callname_lic_initial();

		if ( self::$wps_upsell_bump_callname_lic() || 0 <= $day_count ) {

			$this->loader->add_filter( 'wps_ubo_lite_heading', $plugin_admin, 'pro_heading' );

			// After v1.3.1 :callback for meta forms.
			$this->loader->add_action( 'wp_ajax_wps_upsell_bump_save_meta_form', $plugin_admin, 'wps_upsell_bump_save_meta_form' );
			$this->loader->add_action( 'wp_ajax_wps_upsell_bump_delete_meta_row', $plugin_admin, 'wps_upsell_bump_delete_meta_row' );

		}

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function define_premium_hooks() {

		if ( is_admin() ) {
			return;
		}

		$this->loader->add_action( 'woocommerce_coupon_get_discount_amount', $this, 'zero_discount_for_offer_products', 12, 5 );
		$this->loader->add_filter( 'wps_meta_forms_allowed_submission', $this, 'wps_meta_forms_render', 10, 3 );
		$this->loader->add_filter( 'wps_meta_variable_forms_allowed_submission', $this, 'wps_meta_forms_render_in_variable', 10, 2 );

	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Upsell_Order_Bump_Offer_For_Woocommerce_Pro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Public static variable to be accessed in this plugin.
	 *
	 * @var     callback string
	 * @since   1.0.0
	 */
	public static $wps_upsell_bump_lic_callback_function = 'wps_upsell_bump_check_lcns_validity';

	/**
	 * Public static variable to be accessed in this plugin.
	 *
	 * @var     callback string
	 * @since   1.0.0
	 */
	public static $wps_upsell_bump_lic_ini_callback_function = 'wps_upsell_bump_check_lcns_initial_days';

	/**
	 * Validate the use of features of this plugin.
	 *
	 * @since    1.0.0
	 */
	public static function wps_upsell_bump_check_lcns_validity() {

		$wps_upsell_bump_lcns_key = get_option( 'wps_upsell_bump_license_key', '' );

		$wps_upsell_bump_lcns_status = get_option( 'wps_upsell_bump_license_check', '' );

		if ( $wps_upsell_bump_lcns_key && ( true === $wps_upsell_bump_lcns_status || 1 === (int) $wps_upsell_bump_lcns_status ) ) {

			return true;

		} else {

			return false;
		}
	}

	/**
	 * Validate the use of features of this plugin for initial days.
	 *
	 * @since    1.0.0
	 */
	public static function wps_upsell_bump_check_lcns_initial_days() {

		$thirty_days = get_option( 'wps_upsell_bump_activated_timestamp', 0 );

		$current_time = time();

		$day_count = ( $thirty_days - $current_time ) / ( 24 * 60 * 60 );

		return $day_count;
	}

	/**
	 * Skip offer product in case of the purchased in prevous orders.
	 *
	 * @param      string $offer_product_id    The Offer product id to check.
	 *
	 * @since    1.0.1
	 */
	public static function wps_ubo_skip_for_pre_order( $offer_product_id = '' ) {

		if ( empty( $offer_product_id ) ) {

			return;
		}

		$offer_product = wc_get_product( $offer_product_id );

		// Current user ID.
		$customer_user_id = get_current_user_id();

		// Getting current customer orders.
		$order_statuses = array( 'wc-on-hold', 'wc-processing', 'wc-completed' );

		$customer_orders = get_posts(
			array(
				'numberposts' => -1,
				'fields'      => 'ids', // Return only order ids.
				'meta_key'    => '_customer_user', //phpcs:ignore
				'meta_value'  => $customer_user_id, //phpcs:ignore
				'post_type'   => wc_get_order_types(),
				'post_status' => $order_statuses,
				'order'       => 'DESC', // Get last order first.
			)
		);

		// Past Orders.
		foreach ( $customer_orders as $key => $single_order_id ) {

			// Continue if order is not a valid one.
			if ( ! $single_order_id ) {

				continue;
			}

			$single_order = wc_get_order( $single_order_id );

			// Continue if Order object is not a valid one.
			if ( empty( $single_order ) || ! is_object( $single_order ) || is_wp_error( $single_order ) ) {

				continue;
			}

			$items_purchased = $single_order->get_items();

			foreach ( $items_purchased as $key => $single_item ) {

				$product_id   = ! empty( $single_item['product_id'] ) ? $single_item['product_id'] : '';
				$variation_id = ! empty( $single_item['variation_id'] ) ? $single_item['variation_id'] : '';

				if ( (string) $product_id === (string) $offer_product_id || (string) $variation_id === (string) $offer_product_id ) {

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Replace target product with offer product.
	 *
	 * @param string $offer_cart_key offer_cart_key.
	 * @param string $target_cart_key target_cart_key.
	 * @since    1.0.1
	 */
	public static function wps_ubo_upgrade_offer( $offer_cart_key = '', $target_cart_key = '' ) {

		// Set Smart Offer Upgrade cart item meta for Offer product.
		if ( ! empty( WC()->cart->cart_contents[ $offer_cart_key ] ) ) {

			WC()->cart->cart_contents[ $offer_cart_key ]['wps_ubo_sou_offer'] = true;
		}

		// Set Smart Offer Upgrade cart item meta for Target product.
		if ( ! empty( WC()->cart->cart_contents[ $target_cart_key ] ) ) {

			WC()->cart->cart_contents[ $target_cart_key ]['wps_ubo_sou_target'] = true;
		}

		// Remove Target Product.
		if ( ! empty( $target_cart_key ) ) {

			WC()->cart->remove_cart_item( $target_cart_key );
		}

	}

	/**
	 * Retrieve Target product when offer is removed.
	 *
	 * @param string $target_cart_key target_cart_key.
	 *
	 * @since    1.0.1
	 */
	public static function wps_ubo_retrieve_target( $target_cart_key = '' ) {

		// Restore Target product.
		if ( ! empty( $target_cart_key ) ) {

			WC()->cart->restore_cart_item( $target_cart_key );
		}
	}

	/**
	 * Handle the order bump limit if avaiable.
	 *
	 * @param string $order_bump_id Order bump id.
	 *
	 * @since    1.0.1
	 */
	public static function wps_ubo_manage_exclusive_limit( $order_bump_id = '' ) {

		$order_bump_list = get_option( 'wps_ubo_bump_list', array() );
		$order_bump      = ! empty( $order_bump_list[ $order_bump_id ] ) ? $order_bump_list[ $order_bump_id ] : array();

		$result = false;

		$exclusive_limit_switch = ! empty( $order_bump['wps_ubo_offer_exclusive_limit_switch'] ) ? $order_bump['wps_ubo_offer_exclusive_limit_switch'] : 'no';
		$exclusive_limit        = ! empty( $order_bump['wps_ubo_offer_exclusive_limit'] ) ? $order_bump['wps_ubo_offer_exclusive_limit'] : '0';
		$exclusive_limit_used   = ! empty( $order_bump['bump_orders_count'] ) ? count( $order_bump['bump_orders_count'] ) : '0';

		// Limit is available.
		if ( (int) $exclusive_limit > (int) $exclusive_limit_used ) {
			$result = true;
		}

		// Limit is zero or not set.
		if ( false === $result && in_array( (string) $exclusive_limit, array( '', 0, '0' ), true ) || 'no' === $exclusive_limit_switch ) {
			$result = true;
		}

		return $result;
	}

	/**
	 * Handle the order bump limit if avaiable.
	 *
	 * @param string $order_id Order id.
	 *
	 * @since    1.0.1
	 */
	public function exclusive_limit_callback( $order_id = '' ) {

		$associations = WC()->session->get( 'bump_offer_associations' );

		if ( null !== $associations ) {

			// Get all Bump if already some funnels are present.
			$wps_upsell_bumps_list = get_option( 'wps_ubo_bump_list', array() );
			$associations          = explode( '___', $associations );

			if ( ! empty( $associations ) && is_array( $associations ) ) {
				foreach ( $associations as $key => $indexs ) {
					if ( ! empty( $indexs ) ) {
						$bump_id = str_replace( 'index_', '', $indexs );
						$bump    = $wps_upsell_bumps_list[ $bump_id ];

						if ( ! empty( $bump['bump_orders_count'] ) && is_array( $bump['bump_orders_count'] ) ) {
							$existing_orders = $bump['bump_orders_count'];
							array_push( $existing_orders, $order_id );

						} else {
							$existing_orders = array( $order_id );
						}

						$bump['bump_orders_count']         = array_unique( $existing_orders );
						$wps_upsell_bumps_list[ $bump_id ] = $bump;

					}
				}
			}

			update_option( 'wps_ubo_bump_list', $wps_upsell_bumps_list );
			WC()->session->__unset( 'bump_offer_associations' );
		}
	}

	/**
	 * On successful order reset data.
	 *
	 * @param string $discount The cart discount.
	 * @param string $discounting_amount The cart discount amount.
	 * @param object $cart_item The cart item.
	 * @param string $single The cart item.
	 * @param object $coupon The cart item.
	 *
	 * @since    1.0.0
	 */
	public function zero_discount_for_offer_products( $discount, $discounting_amount, $cart_item, $single, $coupon ) {

		// Saved Global Options.
		$wps_ubo_global_options = get_option( 'wps_ubo_global_options', wps_ubo_lite_default_global_options() );

		$bump_offer_coupon_restriction = ! empty( $wps_ubo_global_options['wps_ubo_offer_restrict_coupons'] ) ? $wps_ubo_global_options['wps_ubo_offer_restrict_coupons'] : 'no';

		if ( 'no' !== $bump_offer_coupon_restriction ) {
			if ( ! empty( $cart_item['wps_discounted_price'] ) ) {
				$discount = 0;
			}
		}

		return $discount;
	}


	/**
	 * On successful order reset data.
	 *
	 * @param string $order_bump_div_id The cart discount.
	 * @param string $meta_forms_allowed The cart discount amount.
	 * @param array  $meta_form_fields The cart item.
	 *
	 * @since    1.0.0
	 */
	public function wps_meta_forms_render( $order_bump_div_id = '', $meta_forms_allowed = 'no', $meta_form_fields = array() ) {

		?>
		<!-- If meta forms allowed then create html -->
		<?php if ( ! empty( $order_bump_div_id ) && ! empty( $meta_forms_allowed ) && 'yes' === $meta_forms_allowed && ! empty( $meta_form_fields ) ) : ?>
			<?php ob_start(); ?>
			<div class="wps-g-modal">
				<div class="wps-g-modal__cover">
					<div class="wps-g-modal__message">
						<div class="wps-g-modal__close-wrap"><span class="wps-g-modal__close"></span></div>
						<div class="wps-g-modal__content">
						<!-- Meta form fields start. -->
						<div id="wps-meta-form-index-<?php echo esc_html( str_replace( '#wps_upsell_offer_main_id_', '', $order_bump_div_id ) ); ?>" class="wps_bump_popup_meta_form">
							<!-- validate meta forms array data -->
							<?php if ( ! empty( $meta_form_fields ) && is_array( $meta_form_fields ) ) : ?>
								<?php foreach ( $meta_form_fields as $key => $meta_fields ) : ?>
									<?php
									$label       = ! empty( $meta_fields['label'] ) ? $meta_fields['label'] : '';
									$placeholder = ! empty( $meta_fields['placeholder'] ) ? $meta_fields['placeholder'] : '';
									$description = ! empty( $meta_fields['description'] ) ? $meta_fields['description'] : '';
									$type        = ! empty( $meta_fields['type'] ) ? strtolower( $meta_fields['type'] ) : '';
									$options     = ! empty( $meta_fields['options'] ) ? explode( ' | ', $meta_fields['options'] ) : array();

									?>
									<?php if ( ! empty( $type ) ) : ?>
										<div class="wps_ubo_bump_meta_field wps-ubo-form-grp">

											<!-- Field label -->
											<label class="meta-form-field-label" for="<?php echo esc_html( $label ); ?>">
												<?php echo esc_html( $label ); ?>
											</label>
											<div class="meta-form__input-wrap">
												<!-- if select type -->
												<?php
												switch ( $type ) {
													case 'select':
														?>
														<select name="<?php echo esc_html( $label ); ?>" id="<?php echo esc_html( $label ); ?>" class="wps_ubo_custom_meta_field">
															<option value=""><?php echo esc_html( $placeholder ); ?></option>
															<?php if ( ! empty( $options ) && is_array( $options ) ) : ?>
																<!-- if options provided  -->
																<?php foreach ( $options as $key => $value ) : ?>
																	<option value="<?php echo esc_html( $value ); ?>"><?php echo esc_html( $value ); ?></option>
																<?php endforeach; ?>
															<?php endif; ?>
														</select>
														<?php
														break;

													case 'number':
														?>
														<input type="number" pattern="[0-9]+" min="0" name="<?php echo esc_html( $label ); ?>" id="<?php echo esc_html( $label ); ?>" class="wps_ubo_custom_meta_field" placeholder="<?php echo esc_html( $placeholder ); ?>">
														<?php
														break;

													default:
														?>
														<input type="<?php echo esc_html( $type ); ?>" name="<?php echo esc_html( $label ); ?>" id="<?php echo esc_html( $label ); ?>" class="wps_ubo_custom_meta_field" placeholder="<?php echo esc_html( $placeholder ); ?>">
														<?php
												}
												?>
												<!-- Field description.  -->
												<?php if ( ! empty( $description ) ) : ?>
													<span class="meta-form-field-desc"><?php echo esc_html( $description ); ?></span>
												<?php endif; ?>
											</div>
										</div>

									<?php endif; ?>
								<?php endforeach; ?>	
							<?php endif; ?>

							<!-- Submit button starts. -->
							<a href="javascript:void(0);" class="single_add_to_cart_button button alt wps-meta-form-submit" id="wps-meta-form-submit-<?php echo esc_html( str_replace( '#wps_upsell_offer_main_id_', '', $order_bump_div_id ) ); ?>"  >
								<?php esc_html_e( 'Submit', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>
							</a>
							<!-- Submit button ends. -->

						</div>
						<!-- Meta form fields end. -->
						</div>
					</div>
				</div>	
			</div>
			<?php $content = ob_get_clean(); ?>
		<?php endif; ?>
		<?php
		return ! empty( $content ) ? $content : '';
	}
	/**
	 * On successful order reset data.
	 *
	 * @param string $order_bump_div_id The cart discount.
	 * @param array  $meta_form_attr The cart item.
	 *
	 * @since    1.0.0
	 */
	public function wps_meta_forms_render_in_variable( $order_bump_div_id = '', $meta_form_attr = array() ) {

		$meta_forms_allowed = ! empty( $meta_form_attr['meta_forms_allowed'] ) ? $meta_form_attr['meta_forms_allowed'] : '';
		$meta_form_fields   = ! empty( $meta_form_attr['meta_form_fields'] ) ? $meta_form_attr['meta_form_fields'] : array();
		?>
		<!-- If meta forms allowed then create html -->
		<?php if ( ! empty( $meta_forms_allowed ) && 'yes' === $meta_forms_allowed && ! empty( $meta_form_fields ) ) : ?>
			<!-- Meta form fields start. -->
			<div id="wps-meta-form-index-<?php echo esc_html( $order_bump_div_id ); ?>" class="wps_bump_popup_meta_form wps_bump_popup_variable_meta_form">
				<!-- validate meta forms array data -->
				<?php if ( ! empty( $meta_form_fields ) && is_array( $meta_form_fields ) ) : ?>
					<?php foreach ( $meta_form_fields as $key => $meta_fields ) : ?>
						<?php
						$label       = ! empty( $meta_fields['label'] ) ? $meta_fields['label'] : '';
						$placeholder = ! empty( $meta_fields['placeholder'] ) ? $meta_fields['placeholder'] : '';
						$description = ! empty( $meta_fields['description'] ) ? $meta_fields['description'] : '';
						$type        = ! empty( $meta_fields['type'] ) ? strtolower( $meta_fields['type'] ) : '';
						$options     = ! empty( $meta_fields['options'] ) ? explode( ' | ', $meta_fields['options'] ) : array();

						?>
						<?php if ( ! empty( $type ) ) : ?>
							<div class="wps_ubo_bump_meta_field">

								<!-- Field label -->
								<label class="meta-form-field-label" for="<?php echo esc_html( $label ); ?>">
									<?php echo esc_html( $label ); ?>
								</label>
									<!-- if select type -->
									<?php
									switch ( $type ) {
										case 'select':
											?>
											<select name="<?php echo esc_html( $label ); ?>" id="<?php echo esc_html( $label ); ?>" class="wps_ubo_custom_meta_field">
												<option value=""><?php echo esc_html( $placeholder ); ?></option>
												<?php if ( ! empty( $options ) && is_array( $options ) ) : ?>
													<!-- if options provided  -->
													<?php foreach ( $options as $key => $value ) : ?>
														<option value="<?php echo esc_html( $value ); ?>"><?php echo esc_html( $value ); ?></option>
													<?php endforeach; ?>
												<?php endif; ?>
											</select>
											<?php
											break;

										case 'number':
											?>
											<input type="number" pattern="[0-9]+" min="0" name="<?php echo esc_html( $label ); ?>" id="<?php echo esc_html( $label ); ?>" class="wps_ubo_custom_meta_field" placeholder="<?php echo esc_html( $placeholder ); ?>">
											<?php
											break;

										default:
											?>
											<input type="<?php echo esc_html( $type ); ?>" name="<?php echo esc_html( $label ); ?>" id="<?php echo esc_html( $label ); ?>" class="wps_ubo_custom_meta_field" placeholder="<?php echo esc_html( $placeholder ); ?>">
											<?php
									}
									?>

									<!-- Field description.  -->
									<?php if ( ! empty( $description ) ) : ?>
										<span class="meta-form-field-desc"><?php echo esc_html( $description ); ?></span>
									<?php endif; ?>
							</div>

						<?php endif; ?>
					<?php endforeach; ?>	
				<?php endif; ?>

			</div>
			<!-- Meta form fields end. -->
		<?php endif; ?>
		<?php
	}

	// End of class.
}
