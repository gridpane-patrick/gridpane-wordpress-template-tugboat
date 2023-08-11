<?php
/**
 * Main Frontend Class
 *
 * @class   YITH_WC_Points_Rewards_Frontend
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

require_once YITH_YWPAR_INC . 'legacy/abstract-yith-wc-points-rewards-frontend-legacy.php';

if ( ! class_exists( 'YITH_WC_Points_Rewards_Frontend' ) ) {

	/**
	 * Class YITH_WC_Points_Rewards_Frontend
	 */
	class YITH_WC_Points_Rewards_Frontend extends YITH_WC_Points_Rewards_Frontend_Legacy {

		/**
		 * Endpoint of my account page.
		 *
		 * @var string
		 */
		public $endpoint;

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WC_Points_Rewards_Frontend
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WC_Points_Rewards_Frontend
		 * @since  1.0.0
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}


		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since 1.0.0
		 */
		private function __construct() {

			// Hide messages of points for guests or if the automatic earning points are enabled.
			if ( ywpar_hide_points_for_guests() ) {
				return;
			}

			add_action( 'template_redirect', array( $this, 'show_messages' ), 30 );
			add_action( 'init', array( $this, 'init' ), 5 );

		}

		/**
		 *  Add hooks when init action is triggered
		 */
		public function init() {

			$ywpar_customer = ywpar_get_current_customer();

			if ( is_user_logged_in() ) {
				if ( ywpar_get_option( 'enable_rewards_points' ) === 'yes' && $ywpar_customer->is_enabled( 'redeem' ) ) {
					add_action( 'wc_ajax_ywpar_update_cart_rewards_messages', array( $this, 'print_rewards_message' ) );
				}

				if ( $ywpar_customer && $ywpar_customer->is_enabled( 'earn' ) ) {
					if ( ywpar_automatic_earning_points_enabled() ) {
						// check if the messages on cart are enabled for ajax calls.
						if ( ywpar_get_option( 'enabled_cart_message' ) === 'yes' ) {
							add_action( 'wc_ajax_ywpar_update_cart_messages', array( $this, 'print_cart_message' ) );
						}

						if ( 'yes' === ywpar_get_option( 'show_point_summary_on_order_details' ) ) {
							add_action( 'woocommerce_view_order', 'ywpar_add_order_points_summary', 5 );
						}
					}
				}
			}

			$this->endpoint = yith_points()->endpoint; // retro compatibility.
			// add points to earn to each variation.
			add_filter( 'woocommerce_available_variation', array( $this, 'add_params_to_available_variation' ), 10, 3 );

			// ____ ONLY FOR ENABLED CUSTOMERS ___.
			if ( ywpar_get_option( 'enabled_cart_message' ) === 'yes' ) {
				add_action( 'wc_ajax_ywpar_update_cart_messages', array( $this, 'print_cart_message' ) );
			}

			if ( ! $ywpar_customer || ! $ywpar_customer->is_enabled( 'earn' ) ) {
				return;
			}

			// Add the endpoints to WooCommerce My Account.
			if ( ywpar_get_option( 'show_point_list_my_account_page' ) === 'yes' ) {
				add_action( 'woocommerce_account_my-points_endpoint', array( $this, 'add_endpoint' ) );
				add_filter( 'woocommerce_endpoint_my-points_title', array( $this, 'load_endpoint_title' ) );

				if ( defined( 'YITH_PROTEO_VERSION' ) ) {
					add_filter( 'yith_proteo_myaccount_custom_icon', array( $this, 'customize_my_account_proteo_icon' ), 10, 2 );
				}
			}

		}

		/**
		 * Show messages on cart or checkout page if the options are enabled
		 */
		public function show_messages() {

			if ( ywpar_automatic_earning_points_enabled() ) {
				// check and show messages on single product page.
				if ( ywpar_get_option( 'enabled_single_product_message' ) === 'yes' ) {
					$this->show_single_product_message_position();
				}

				// check and show messages on show page.
				if ( ywpar_get_option( 'enabled_loop_message' ) === 'yes' ) {
						$this->show_single_loop_position();
				}

				if ( 'yes' !== ywpar_get_option( 'hide_point_system_to_guest' ) ) {
					if ( ywpar_get_option( 'enabled_cart_message' ) === 'yes' ) {
						add_action( 'woocommerce_before_cart', array( $this, 'print_messages_in_cart' ) );
					}

					// check if the messages are enabled at checkout.
					if ( ywpar_get_option( 'enabled_checkout_message' ) === 'yes' ) {
						add_action( 'woocommerce_before_checkout_form', array( $this, 'print_messages_in_cart' ) );
						add_action( 'before_woocommerce_pay', array( $this, 'print_messages_in_cart' ) );
					}
				}
			}

			// ____ ONLY FOR CUSTOMERS ___.
			$ywpar_customer = ywpar_get_current_customer();
			if ( ! $ywpar_customer ) {
				return;
			}

			// ____ ONLY FOR CUSTOMERS THAT CAN EARN ___.
			if ( $ywpar_customer->is_enabled( 'earn' ) && ywpar_automatic_earning_points_enabled() ) {
				if ( ywpar_get_option( 'disable_earning_while_reedeming', 'no' ) === 'yes' && ywpar_cart_has_redeeming_coupon() ) {
					return;
				}

				if ( ywpar_get_option( 'enabled_cart_message' ) === 'yes' ) {
					add_action( 'woocommerce_before_cart', array( $this, 'print_messages_in_cart' ) );
				}

				// check if the messages are enabled at checkout.
				if ( ywpar_get_option( 'enabled_checkout_message' ) === 'yes' ) {
					add_action( 'woocommerce_before_checkout_form', array( $this, 'print_messages_in_cart' ) );
					add_action( 'before_woocommerce_pay', array( $this, 'print_messages_in_cart' ) );
				}

				// show if enabled the message for checkout thresholds extra points.
				if ( 'yes' === ywpar_get_option( 'enable_checkout_threshold_exp' ) && 'yes' === ywpar_get_option( 'checkout_threshold_show_message', 'no' ) ) {
					add_action( 'woocommerce_before_cart', array( $this, 'print_checkout_threshold' ) );
					add_action( 'woocommerce_before_checkout_form', array( $this, 'print_checkout_threshold' ) );
				}
			}

			// ____ ONLY FOR CUSTOMERS THAT CAN REDEEM ___.
			if ( ywpar_get_option( 'enable_rewards_points' ) === 'yes' && $ywpar_customer->is_enabled( 'redeem' ) ) {
				add_action( 'woocommerce_before_cart', array( $this, 'print_rewards_message_in_cart' ) );
				add_action( 'woocommerce_before_checkout_form', array( $this, 'print_rewards_message_in_cart' ) );
				add_action( 'wc_ajax_ywpar_apply_points', array( $this, 'print_rewards_message' ) );
			}
		}

		/**
		 * Set the position where display the message in single product
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function show_single_product_message_position() {

			$position                = ywpar_get_option( 'single_product_message_position' );
			$priority_single_excerpt = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt' );
			$priority_after_meta     = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta' );

			$hooks = array(
				'before_add_to_cart' => array(
					'action'   => 'woocommerce_before_add_to_cart_form',
					'priority' => 10,
				),
				'after_add_to_cart'  => array(
					'action'   => 'woocommerce_after_add_to_cart_form',
					'priority' => 10,
				),
				'before_excerpt'     => array(
					'action'   => 'woocommerce_single_product_summary',
					'priority' => $priority_single_excerpt ? $priority_single_excerpt - 1 : 18,
				),
				'after_excerpt'      => array(
					'action'   => 'woocommerce_single_product_summary',
					'priority' => $priority_single_excerpt ? $priority_single_excerpt + 1 : 22,
				),
				'after_meta'         => array(
					'action'   => 'woocommerce_single_product_summary',
					'priority' => $priority_after_meta ? $priority_after_meta + 1 : 42,
				),

			);

			// APPLY_FILTER : ywpar_show_single_product_message_position_action: filtering action where show the message in single product page.
			$action = apply_filters( 'ywpar_show_single_product_message_position_action', $hooks[ $position ]['action'], $position, $hooks[ $position ]['priority'] );
			// APPLY_FILTER : ywpar_show_single_product_message_position_priority: filtering the priority where show the message in single product page.
			$priority = apply_filters( 'ywpar_show_single_product_message_position_priority', $hooks[ $position ]['priority'], $position, $hooks[ $position ]['action'] );

			add_action( $action, array( $this, 'print_single_product_message' ), $priority );
		}

		/**
		 * Set the position where display the message in loop
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function show_single_loop_position() {
			$customer = ywpar_get_current_customer();

			if ( ! ywpar_automatic_earning_points_enabled() || ( is_user_logged_in() && ! ( $customer && $customer->is_enabled() ) ) || ( ! is_user_logged_in() && 'yes' === ywpar_get_option( 'hide_point_system_to_guest' ) ) ) {
				return;
			}

			// APPLY_FILTER : ywpar_loop_position: filtering the position where show the message in the loop.
			$position = apply_filters( 'ywpar_loop_position', 'woocommerce_after_shop_loop_item_title' );
			// APPLY_FILTER : ywpar_loop_position: filtering the priority where show the message in the loop.
			$priority = apply_filters( 'ywpar_loop_position_priority', 11 );
			add_action( $position, array( $this, 'print_messages_in_loop' ), $priority );
		}

		/**
		 * Print earning point message on product page
		 */
		public function print_single_product_message() {
			$customer = ywpar_get_current_customer();
			if ( ! ywpar_automatic_earning_points_enabled() || ( is_user_logged_in() && ! ( $customer && $customer->is_enabled() ) ) || ( ! is_user_logged_in() && 'yes' === ywpar_get_option( 'hide_point_system_to_guest' ) ) ) {
				return;
			}
			$shortcode = '[yith_points_product_message]';
			echo is_callable( 'apply_shortcodes' ) ? apply_shortcodes( $shortcode ) : do_shortcode( $shortcode ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Print the cart message on ajax call the cart is updated.
		 *
		 * @since   1.1.3
		 * @author  Andrea Frascaspata
		 */
		public function print_cart_message() {
			echo $this->get_cart_message(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			die();
		}

		/**
		 * Print earning point message in loop
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function print_messages_in_loop() {
			$shortcode = '[yith_points_product_message_loop]';
			echo is_callable( 'apply_shortcodes' ) ? apply_shortcodes( $shortcode ) : do_shortcode( $shortcode ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Print a message in cart/checkout page or in my account pay order page.
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function print_messages_in_cart() {
			$points_earned = false;

			$message      = $this->get_cart_message( $points_earned );
			$message_type = 'earn';
			if ( ! empty( $message ) ) {
				// APPLY_FILTER : yith_par_messages_class: filtering the classes of messages in cart/checkout.
				$yith_par_message_classes = apply_filters(
					'yith_par_messages_class',
					array(
						'woocommerce-cart-notice',
						'woocommerce-cart-notice-minimum-amount',
						'woocommerce-info',
					),
					$message_type
				);
				$classes                  = count( $yith_par_message_classes ) > 0 ? implode( ' ', $yith_par_message_classes ) : '';
				printf( '<div id="yith-par-message-cart" class="%s">%s</div>', esc_attr( $classes ), $message ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		/**
		 * Add custom params to variations
		 *
		 * @access public
		 *
		 * @param array  $args Current arguments.
		 * @param object $product Product.
		 * @param object $variation Variation.
		 *
		 * @return array
		 * @since  1.1.1
		 */
		public function add_params_to_available_variation( $args, $product, $variation ) {

			if ( $variation ) {
				$var_points                                        = yith_points()->earning->get_product_points( $variation );
				$args['variation_points']                          = $var_points;
				$args['variation_price_discount_fixed_conversion'] = yith_points()->redeeming->calculate_price_worth( $variation->get_id(), $var_points, true );
			}

			return $args;
		}


		/**
		 * Print the Checkout Thresholds Extra Points Message
		 *
		 * @return  void
		 * @since   1.7.9
		 * @author  Armando Liccardo
		 */
		public function print_checkout_threshold() {
			$shortcode = '[yith_checkout_thresholds_message]';
			echo is_callable( 'apply_shortcodes' ) ? apply_shortcodes( $shortcode ) : do_shortcode( $shortcode ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}


		/**
		 * Return the message to show on cart or checkout for point to earn.
		 *
		 * @param int $total_points Total points.
		 *
		 * @return string
		 * @since   1.1.3
		 * @author  Andrea Frascaspata
		 */
		private function get_cart_message( $total_points = 0 ) {

			if ( isset( $_GET['cart_or_checkout'] ) ) { //phpcs:ignore
				$page = 'checkout' === $_GET['cart_or_checkout'] ? 'checkout' : 'cart'; //phpcs:ignore
			} else {
				$page = is_checkout() ? 'checkout' : 'cart';
			}

			$message  = ywpar_get_option( $page . '_message' );
			$singular = ywpar_get_option( 'points_label_singular' );
			$plural   = ywpar_get_option( 'points_label_plural' );

			if ( 0 === (int) $total_points ) {
				$total_points = yith_points()->earning->calculate_points_on_cart();
				if ( 0 === $total_points ) {
					return '';
				}
			}

			$conversion_method = yith_points()->redeeming->get_conversion_method();

			$discount = '';

			if ( 'fixed' === $conversion_method ) {
				$conversion  = yith_points()->redeeming->get_conversion_rate_rewards();
				$point_value = $conversion['money'] / $conversion['points'];
				$discount    = $total_points * $point_value;
			}

			$total_points = apply_filters( 'ywpar_product_points_formatted', $total_points );
			$message      = str_replace( '{points}', $total_points, $message );
			$message      = str_replace( '{points_label}', ( $total_points > 1 ) ? $plural : $singular, $message );
			$message      = str_replace( '{price_discount_fixed_conversion}', ! empty( $discount ) ? wc_price( $discount ) : '', $message );

			// APPLY_FILTER : ywpar_cart_message_filter: filtering the cart messages in cart/checkout.
			return apply_filters( 'ywpar_cart_message_filter', $message, $total_points, $discount );
		}


		/**
		 * Show the points My account page
		 */
		public function add_endpoint() {
			$shortcode = '[ywpar_my_account_points]';
			echo is_callable( 'apply_shortcodes' ) ? apply_shortcodes( $shortcode ) : do_shortcode( $shortcode ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Set the endpoint title
		 *
		 * @param string $title Title.
		 * @return string
		 */
		public function load_endpoint_title( $title ) {
			return ywpar_get_option( 'my_account_page_label', esc_html__( 'My Points', 'yith-woocommerce-points-and-rewards' ) );
		}

		/**
		 * Change the icon inside my account on Proteo Theme
		 *
		 * @param string $icon Icon.
		 * @param string $endpoint Endpoint.
		 *
		 * @return string
		 */
		public function customize_my_account_proteo_icon( $icon, $endpoint ) {
			if ( $endpoint === yith_points()->endpoint ) {
				return '<span class="yith-proteo-myaccount-icons lnr lnr-diamond"></span>';
			}

			return $icon;
		}

		/**
		 * Print rewards message in cart/checkout page
		 *
		 * @return  string
		 * @since   1.0.0
		 */
		public function print_rewards_message_in_cart() {

			$message_type = 'reward';

			// the message will not showed if the coupon is just applied to cart.
			if ( ywpar_cart_has_redeeming_coupon() ) {
				return '';
			}

			$message = $this->get_rewards_message();
			if ( $message ) {
				// APPLY_FILTER : yith_par_messages_class: filtering the classes of messages in cart/checkout.
				$yith_par_message_classes = apply_filters(
					'yith_par_messages_class',
					array(
						'woocommerce-cart-notice',
						'woocommerce-cart-notice-minimum-amount',
						'woocommerce-info',
					),
					$message_type
				);

				$classes  = count( $yith_par_message_classes ) > 0 ? implode( ' ', $yith_par_message_classes ) : '';
				$classes .= ( 'default' === ywpar_get_option( 'enabled_rewards_cart_message_layout_style' ) ) ? ' default-layout' : ' custom-layout';

				printf( '<div id="yith-par-message-reward-cart" class="%s">%s</div>', esc_attr( $classes ), $message ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

		}

		/**
		 * Rewards message.
		 *
		 * @since   1.1.3
		 * @author  Andrea Frascaspata
		 */
		public function print_rewards_message() {
			// the message will not be displayed if the coupon is just applied to cart.
			if ( ywpar_cart_has_redeeming_coupon() ) {
				return '';
			}
			echo $this->get_rewards_message(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			die();
		}

		/**
		 * Return the reward message
		 *
		 * @return mixed|string|void
		 * @since   1.1.3
		 * @author  Andrea Frascaspata
		 */
		private function get_rewards_message() {

			if ( ! yith_points()->redeeming->is_valid_redeeming_points() ) {
				return '';
			}

			$max_discount = yith_points()->redeeming->calculate_rewards_discount();
			$max_points   = yith_points()->redeeming->get_max_points();

			if ( ! $max_discount || $max_discount <= 0 || $max_points <= 0 ) {
				return '';
			}

			$conversion_method                 = yith_points()->redeeming->get_conversion_method();
			$minimum_amount_discount_to_redeem = yith_points()->redeeming->get_min_discount_amount_to_redeem();

			$min_value_to_redeem_error_msg = '';
			$minimum_points_to_redeem      = '';
			if ( 'fixed' === $conversion_method ) {
				if ( '' !== $minimum_amount_discount_to_redeem ) {
					$conversion               = yith_points()->redeeming->get_conversion_rate_rewards();
					$minimum_points_to_redeem = ( $conversion['points'] / $conversion['money'] ) * floatval( $minimum_amount_discount_to_redeem );
					// APPLY_FILTER : ywpar_min_value_to_reedem_error_msg: change the error message that appears on trying to use less points than the ones needed as minimum.
					$min_value_to_redeem_error_msg = apply_filters( 'ywpar_min_value_to_reedem_error_msg', __( 'The minimum value to redeem is ', 'yith-woocommerce-points-and-rewards' ) . $minimum_points_to_redeem, $minimum_points_to_redeem );
				}
			}
			$customer = ywpar_get_customer( get_current_user_id() );
			$args     = array(
				'conversion_method'             => $conversion_method,
				'max_discount'                  => $max_discount,
				'max_percentage_discount'       => yith_points()->redeeming->get_max_percentage_discount(),
				'max_points'                    => $max_points,
				'min_value_to_redeem_error_msg' => $min_value_to_redeem_error_msg,
				'minimum_points_to_redeem'      => $minimum_points_to_redeem,
				'usable_points'                 => $customer->get_usable_points(),
			);

			return ( 'default' === ywpar_get_option( 'enabled_rewards_cart_message_layout_style' ) ) ? $this->get_rewards_message_default_layout( $args ) : $this->get_rewards_message_custom_layout( $args );
		}

		/**
		 * Print the message with Default Layout
		 *
		 * @param array $args Arguments.
		 * @return mixed|string|void
		 * @since   2.0.0
		 * @author  Armando Liccardo
		 */
		public function get_rewards_message_default_layout( $args ) {
			// DO_ACTION : ywpar_before_rewards_message_default_layout : action triggered before the rewards message.
			do_action( 'ywpar_before_rewards_message_default_layout' );

			list( $conversion_method, $max_discount, $max_percentage_discount, $max_points, $min_value_to_redeem_error_msg, $minimum_points_to_redeem, $usable_points ) = yith_plugin_fw_extract( $args, 'conversion_method', 'max_discount', 'max_percentage_discount', 'max_points', 'min_value_to_redeem_error_msg', 'minimum_points_to_redeem', 'usable_points' );

			// APPLY_FILTER : ywpar_hide_value_for_max_discount: hide the message if $max_discount is < 0.
			$max_discount_2       = apply_filters( 'ywpar_hide_value_for_max_discount', $max_discount );
			$plural               = ywpar_get_option( 'points_label_plural' );
			$max_points_formatted = apply_filters( 'ywpar_max_points_formatted', $max_points );

			if ( 'fixed' === $conversion_method ) {
				$message      = apply_filters( 'ywpar_reward_message_format', esc_html__( 'You have {points} {points_label}. Use {points_field} points to get a discount of {max_discount} on this order.', 'yith-woocommerce-points-and-rewards' ) );
				$points_field = '<span><input type="text" min="' . $minimum_points_to_redeem . '" name="ywpar_input_points" class="input-text"  id="ywpar-points-max" value="' . $max_points . '"></span>';

				$message  = str_replace( '{points_label}', '<strong>' . $plural . '</strong>', $message );
				$message  = str_replace( '{max_discount}', '<strong>' . wc_price( $max_discount ) . '</strong>', $message );
				$message  = str_replace( '{points}', '<strong>' . $usable_points . '</strong>', $message );
				$message  = str_replace( '{points_field}', $points_field, $message );
				$message  = '<form class="ywpar_apply_discounts" method="post">' . wp_nonce_field( 'ywpar_apply_discounts', 'ywpar_input_points_nonce' ) . $message;
				$message .= ' <span><input type="submit" class="button ywpar_apply_discounts ywpar-fixed-discount" name="ywpar_apply_discounts" id="ywpar_apply_discounts" value="' . ywpar_get_option( 'label_apply_discounts' ) . '"></span>';
				$message .= '<input type="hidden" name="ywpar_points_max" value="' . $max_points . '">
						             <input type="hidden" name="ywpar_max_discount" value="' . $max_discount_2 . '">
						             <input type="hidden" name="ywpar_rate_method" value="fixed">
						             <input type="hidden" name="ywpar_input_points_check" id="ywpar_input_points_check" value="0">
						             <div style="display: none" class="ywpar_min_reedem_value_error">' . $min_value_to_redeem_error_msg . '</div>';
				$message .= '</form><div class="clear"></div>';

			} else {
				$message = apply_filters( 'ywpar_reward_percentage_message_format', esc_html__( 'Use {points} {points_label} for a {max_discount} discount on this order!', 'yith-woocommerce-points-and-rewards' ) );
				$message = str_replace( '{points_label}', '<strong>' . $plural . '</strong>', $message );
				$message = str_replace( '{max_discount}', '<strong>' . wc_price( $max_discount ) . '</strong>', $message );
				$message = str_replace( '{max_percentual_discount}', $max_percentage_discount . '%', $message );
				$message = str_replace( '{points}', '<strong>' . $max_points_formatted . '</strong>', $message );

				$message .= ' <a class="ywpar-button-message ywpar-button-percentage-discount">' . ywpar_get_option( 'label_apply_discounts' ) . '</a>';
				$message .= '<div class="ywpar_apply_discounts_container"><form class="ywpar_apply_discounts" method="post">' . wp_nonce_field( 'ywpar_apply_discounts', 'ywpar_input_points_nonce' ) . '
                                     <input type="hidden" name="ywpar_points_max" value="' . $max_points . '">
                                     <input type="hidden" name="ywpar_max_discount" value="' . $max_discount_2 . '">
                                     <input type="hidden" name="ywpar_rate_method" value="percentage">';
				$message .= '</form></div>';
			}

			// DO_ACTION : ywpar_after_rewards_message : action triggered after the rewards message.
			do_action( 'ywpar_after_rewards_message' );
			return $message;
		}

		/**
		 * Add the menu item on WooCommerce My account Menu
		 * before the Logout item menu.
		 *
		 * @param array $wc_menu WooCommerce menu.
		 *
		 * @return mixed
		 */
		public function ywpar_add_points_menu_items( $wc_menu ) {

			if ( isset( $wc_menu['customer-logout'] ) ) {
				$logout = $wc_menu['customer-logout'];
				unset( $wc_menu['customer-logout'] );
			}

			$wc_menu[ $this->endpoint ] = ywpar_get_option( 'my_account_page_label', __( 'My Points', 'yith-woocommerce-points-and-rewards' ) );

			if ( isset( $logout ) ) {
				$wc_menu['customer-logout'] = $logout;
			}

			return $wc_menu;
		}

		/**
		 * Return the custom rewards message
		 *
		 * @param array $args Arguments.
		 *
		 * @return mixed|string|void
		 * @since   1.1.3
		 */
		public function get_rewards_message_custom_layout( $args ) {

			// DO_ACTION : ywpar_before_rewards_message : action triggered before the rewards message.
			do_action( 'ywpar_before_rewards_message' );
			list( $conversion_method, $max_discount, $max_percentage_discount, $max_points, $min_value_to_redeem_error_msg, $minimum_points_to_redeem, $usable_points ) = yith_plugin_fw_extract( $args, 'conversion_method', 'max_discount', 'max_percentage_discount', 'max_points', 'min_value_to_redeem_error_msg', 'minimum_points_to_redeem', 'usable_points' );
			$message = ywpar_get_option( 'rewards_cart_message' );
			$plural  = ywpar_get_option( 'points_label_plural' );

			// APPLY_FILTER : ywpar_hide_value_for_max_discount: hide the message if $max_discount is < 0.
			$max_discount_2       = apply_filters( 'ywpar_hide_value_for_max_discount', $max_discount );
			$max_points_formatted = apply_filters( 'ywpar_max_points_formatted', $max_points );

			if ( 'fixed' === $conversion_method ) {
				$before_ywpar_points_max = apply_filters( 'ywpar_before_ywpar-points-max', '' ); //phpcs:ignore
				$after_ywpar_points_max  = apply_filters( 'ywpar_after_ywpar-points-max', '' ); //phpcs:ignore
				$max_points_formatted    = apply_filters( 'ywpar_max_points_formatted', $max_points );
				$message                 = str_replace( '{points_label}', $plural, $message );
				$message                 = str_replace( '{max_discount}', wc_price( apply_filters( 'ywpar_max_discount_redeem_message', $max_discount, 'custom-layout' ) ), $message );
				$message                 = str_replace( '{points}', $max_points_formatted, $message );
				$message                .= ' <a class="ywpar-button-message">' . ywpar_get_option( 'label_apply_discounts' ) . '</a>';
				$message                .= '<div class="clear"></div><div class="ywpar_apply_discounts_container"><form class="ywpar_apply_discounts" method="post">' . wp_nonce_field( 'ywpar_apply_discounts', 'ywpar_input_points_nonce' ) . '
                                    <input type="hidden" name="ywpar_points_max" value="' . $max_points . '">
                                    <input type="hidden" name="ywpar_max_discount" value="' . $max_discount_2 . '">
                                    <input type="hidden" name="ywpar_rate_method" value="fixed">
                                    ' . $before_ywpar_points_max . '
                                    <p class="form-row form-row-first">
                                        <input type="text" min="' . $minimum_points_to_redeem . '" name="ywpar_input_points" class="input-text"  id="ywpar-points-max" value="' . $max_points . '">
                                        <input type="hidden" name="ywpar_input_points_check" id="ywpar_input_points_check" value="0">
                                    </p>
                                    ' . $after_ywpar_points_max . '
                                    <p class="form-row form-row-last">
                                        <input type="submit" class="button" name="ywpar_apply_discounts" id="ywpar_apply_discounts" value="' . ywpar_get_option( 'label_apply_discounts' ) . '">
                                    </p>
                                    <div class="clear"></div>
                                    <div style="display: none" class="ywpar_min_reedem_value_error">' . $min_value_to_redeem_error_msg . '</div>
                                </form></div>';

			} else {

				$message  = str_replace( '{points_label}', $plural, $message );
				$message  = str_replace( '{max_discount}', wc_price( $max_discount ), $message );
				$message  = str_replace( '{max_percentual_discount}', $max_percentage_discount . '%', $message );
				$message  = str_replace( '{points}', $max_points_formatted, $message );
				$message .= ' <a class="ywpar-button-message ywpar-button-percentage-discount">' . ywpar_get_option( 'label_apply_discounts' ) . '</a>';
				$message .= '<div class="ywpar_apply_discounts_container"><form class="ywpar_apply_discounts" method="post">' . wp_nonce_field( 'ywpar_apply_discounts', 'ywpar_input_points_nonce' ) . '
                                     <input type="hidden" name="ywpar_points_max" value="' . $max_points . '">
                                     <input type="hidden" name="ywpar_max_discount" value="' . $max_discount_2 . '">
                                     <input type="hidden" name="ywpar_rate_method" value="percentage">';

				$message .= '</form></div>';
			}
			// DO_ACTION : ywpar_after_rewards_message : action triggered after the rewards message.
			do_action( 'ywpar_after_rewards_message' );

			return $message;
		}
	}

}
