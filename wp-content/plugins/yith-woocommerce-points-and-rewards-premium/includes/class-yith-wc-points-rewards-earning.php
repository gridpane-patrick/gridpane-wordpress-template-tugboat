<?php
/**
 * Class to earning points
 *
 * @class   YITH_WC_Points_Rewards_Earning
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

require_once YITH_YWPAR_INC . 'legacy/abstract-yith-wc-points-rewards-earning-legacy.php';

if ( ! class_exists( 'YITH_WC_Points_Rewards_Earning' ) ) {

	/**
	 * Class YITH_WC_Points_Rewards_Earning
	 */
	class YITH_WC_Points_Rewards_Earning extends YITH_WC_Points_Rewards_Earning_Legacy {


		/**
		 * Single instance of the class
		 *
		 * @var YITH_WC_Points_Rewards_Earning
		 */
		protected static $instance;

		/**
		 * Single instance of the class
		 *
		 * @var bool
		 */
		protected $points_applied = false;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WC_Points_Rewards_Earning
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
			if ( ! ywpar_automatic_earning_points_enabled() ) {
				return;
			}

			add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_points_earned_from_cart' ) );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'set_points_on_cart_item' ), 20, 2 );

			// add points for previous orders to a new registered user.
			if ( 'yes' === ywpar_get_option( 'assign_older_orders_points_to_new_registered_user' ) ) {
				add_action( 'user_register', array( $this, 'add_points_for_previous_orders_on_registration' ) );
			}

			add_action( 'init', array( $this, 'init' ) );

		}

		/**
		 * Save the total point inside the cart item
		 *
		 * @param array $cart_item Cart item.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return array
		 */
		public function set_points_on_cart_item( $cart_item, $cart_item_key ) {
			$cart_item['ywpar_total_points'] = $this->calculate_points_for_cart_item( $cart_item, $cart_item_key );

			return $cart_item;
		}

		/**
		 *  Start the game for earn point with order and extra points.
		 *
		 *  Triggered by 'init' hook.
		 *
		 * @since  1.6.0
		 */
		public function init() {

			$status_to_earn = ywpar_get_option( 'order_status_to_earn_points' );

			if ( $status_to_earn ) {
				foreach ( $status_to_earn as $hook ) {
					add_action( $hook, array( $this, 'add_order_points' ), 5 );
				}
			}

		}

		/**
		 * Save the points that are in cart in a post meta of the order
		 *
		 * @param int $order_id Order id.
		 *
		 * @return  void
		 * @since   1.5.0
		 */
		public function save_points_earned_from_cart( $order_id ) {
			$points_from_cart = $this->calculate_points_on_cart();
			$order            = wc_get_order( $order_id );
			do_action( 'ywpar_saved_points_earned_from_cart', $order );
			$order->update_meta_data( 'ywpar_points_from_cart', $points_from_cart );
			$order->save();
		}


		/**
		 * Calculate the points for a cart item.
		 *
		 * @param array $cart_item Cart item.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return int
		 */
		public function calculate_points_for_cart_item( $cart_item, $cart_item_key ) {

			$cart_contents        = WC()->cart->get_cart_contents();
			$total_product_points = 0;

			if ( isset( $cart_item['bundled_by'], $cart_contents[ $cart_item['bundled_by'] ] ) ) {
				$bundle_id      = $cart_contents[ $cart_item['bundled_by'] ]['product_id'];
				$bundle_product = wc_get_product( $bundle_id );
				if ( $bundle_product ) {
					$p_earned = $bundle_product->get_meta( '_ywpar_point_earned' );
					if ( ! empty( $p_earned ) && $bundle_product->get_meta( '_yith_wcpb_per_item_pricing' ) === 'yes' ) {
						return $total_product_points;
					}
				}
			}

			$product_point = 0;
			if ( apply_filters( 'ywpar_calculate_points_for_product', true, $cart_item, $cart_item_key ) ) {
				$product_point = $this->get_product_points( $cart_item['data'], '', false );
			}

			$total_product_points = floatval( $product_point * $cart_item['quantity'] );

			if ( WC()->cart->applied_coupons && isset( WC()->cart->discount_cart ) && WC()->cart->discount_cart > 0 ) {
				if ( ywpar_get_option( 'remove_points_coupon', 'yes' ) === 'yes' && $cart_item['line_subtotal'] ) {
					$total_product_points = ( $cart_item['line_total'] / $cart_item['line_subtotal'] ) * $total_product_points;
				}

				if ( apply_filters( 'ywpar_disable_earning_if_there_is_a_coupon', false ) || ( 'yes' === ywpar_get_option( 'disable_earning_while_reedeming' ) && ywpar_cart_has_redeeming_coupon() ) ) {
					$total_product_points = 0;
				}
			}

			return yith_ywpar_round_points( $total_product_points );
		}

		/**
		 * Calculate the total points in the carts
		 *
		 * @param bool $integer Precision of points.
		 *
		 * @return int $points
		 * @since   1.0.0
		 */
		public function calculate_points_on_cart( $integer = true ) {

			$items      = WC()->cart->get_cart();
			$tot_points = 0;

			foreach ( $items as $cart_item_key => $cart_item ) {
				$tot_points += $this->calculate_points_for_cart_item( $cart_item, $cart_item_key );
			}
			$tot_points = ( $tot_points < 0 ) ? 0 : $tot_points;
			$tot_points = $integer ? yith_ywpar_round_points( $tot_points ) : $tot_points;

			return apply_filters( 'ywpar_calculate_points_on_cart', $tot_points );
		}

		/**
		 * Add points to the order from order_id
		 *
		 * @param int $order_id Order id.
		 *
		 * @return void
		 * @since   1.0.0
		 */
		public function add_order_points( $order_id ) {

			$order  = wc_get_order( $order_id );
			$is_set = $order->get_meta( '_ywpar_points_earned' );

			// return if the points are already calculated.
			if ( '' !== $is_set || is_array( $this->points_applied ) && in_array( $order_id, $this->points_applied, true ) || apply_filters( 'ywpar_add_order_points', false, $order_id ) ) {
				return;
			}
			$customer = ywpar_get_point_customer_from_order( $order );

			if ( ! $customer || ! $customer->is_enabled() ) {
				return;
			}

			// if the order has a redeeming coupon and the option disable_earning_while_reedeming is on return.
			if ( ywpar_get_option( 'disable_earning_while_reedeming', 'no' ) === 'yes' && ywpar_order_has_redeeming_coupon( $order ) ) {
				return;
			}

			$tot_points = (int) trim( $order->get_meta( 'ywpar_points_from_cart' ) );
			$tot_points = ( '' === $tot_points ) ? $this->calculate_order_points_from_items( $order, $customer ) : $tot_points;
			$tot_points = apply_filters( 'ywpar_earned_total_points_by_order', $tot_points, $order );

			// update order meta and add note to the order.
			$order->update_meta_data( '_ywpar_points_earned', $tot_points );
			$order->update_meta_data( '_ywpar_conversion_points', $this->get_conversion_option( $order->get_currency(), $order ) );
			$order->save();

			$this->points_applied[] = $order_id;
			// translators: First placeholder: number of points; second placeholder: label of points.
			$order->add_order_note( sprintf( _x( 'Customer earned %1$d %2$s for this purchase.', 'First placeholder: number of points; second placeholder: label of points', 'yith-woocommerce-points-and-rewards' ), $tot_points, ywpar_get_option( 'points_label_plural' ) ), 0 );
			do_action( 'ywpar_added_earned_points_to_order', $order );
			$customer->update_points( $tot_points, 'order_completed', array( 'order_id' => $order_id ) );
			yith_points()->extra_points->handle_actions( array( 'num_of_orders', 'amount_spent', 'checkout_threshold' ), $customer, $order_id );
		}

		/**
		 * Return the global points of an object from price
		 *
		 * @param float $price Price of the object.
		 * @param string $currency Currency.
		 * @param bool $integer Precision of points.
		 *
		 * @return int|float
		 */
		public function get_points_earned_from_price( $price, $currency, $integer = false ) {
			$conversion = $this->get_conversion_option( $currency );
			$points     = ( (float) $price / (float) $conversion['money'] ) * $conversion['points'];

			return $integer ? yith_ywpar_round_points( $points ) : $points;
		}


		/**
		 * Return the global points of an object from price
		 *
		 * @param int $points Points.
		 * @param bool $integer Precision of points.
		 *
		 * @return float
		 * @since   1.0.0
		 */
		public function get_price_from_point_earned( $points, $integer = false ) {
			$conversion = $this->get_conversion_option();

			$price = $points * $conversion['money'] / $conversion['points'];

			return $price;
		}


		/**
		 * Get points of a product.
		 *
		 * @param WC_Product $product Product.
		 * @param string $currency Currency.
		 * @param bool $integer Precision of points.
		 * @param WP_User|YITH_WC_Points_Rewards_Customer $user User.
		 *
		 * @return int|float
		 */
		public function get_product_points( $product, $currency = '', $integer = true, $user = null ) {
			if ( is_numeric( $product ) ) {
				$product = wc_get_product( $product );
			}

			$product_points = false;
			$currency       = ywpar_get_currency( $currency );

			$customer_id   = ywpar_get_current_customer_id( $user );
			$points_cached = wp_cache_get( 'ywpar_product_points', 'ywpar_points' );

			if ( false !== $points_cached ) {
				$index = $currency . '_' . ( $integer ? 'integer' : 'decimal' );
				if ( isset( $points_cached[ $product->get_id() ][ $customer_id ][ $index ] ) ) {
					$product_points = $points_cached[ $product->get_id() ][ $customer_id ][ $index ];
				}
			}

			if ( ! $product_points ) {
				$product_points = $this->calculate_product_points( $product, $currency, $integer, $user );

				$points_cached[ $product->get_id() ][ $customer_id ][ $currency ] = $product_points;
				wp_cache_set( 'ywpar_product_points', $points_cached, 'ywpar_points' );
			}

			return $product_points;
		}

		/**
		 * Return the points of a product.
		 *
		 * @param WC_Product $product Product.
		 * @param string $currency Currency.
		 * @param bool $integer Precision of points.
		 * @param WP_User $user User.
		 *
		 * @return int|float
		 */
		public function calculate_product_points( $product, $currency = '', $integer = true, $user = null ) {
			$calculated_points = 0;
			$currency          = ywpar_get_currency( $currency );
			$product           = is_numeric( $product ) ? wc_get_product( $product ) : $product;

			if ( ! $product instanceof WC_Product || ywpar_exclude_product_on_sale( $product ) ) {
				return $calculated_points;
			}

			if ( $product->is_type( 'variable' ) ) {
				/**
				 * Variable product.
				 *
				 * @var $product WC_Product_Variable
				 */
				return $this->calculate_product_points_on_variable( $product, $integer );
			}

			if ( $product->is_type( 'grouped' ) ) {

				foreach ( $product->get_children() as $child_id ) {
					$child             = wc_get_product( $child_id );
					$calculated_points += $this->calculate_product_points( $child, $integer, $currency );
				}

				return $calculated_points;
			}

			$product_price     = ywpar_get_product_price( $product, 'earn', $currency );
			$calculated_points = $this->get_points_earned_from_price( $product_price, $currency, true );

			$valid_rules = YITH_WC_Points_Rewards_Helper::get_earning_rules_valid_for_product( $product, $user );

			$product_rules    = array();
			$on_sale_rules    = array();
			$categories_rules = array();
			$tags_rules       = array();
			$general_rules    = array();

			if ( $valid_rules ) {
				foreach ( $valid_rules as $valid_rule ) {

					switch ( $valid_rule->get_apply_to() ) {
						case 'selected_products':
							array_push( $product_rules, $valid_rule );
							break;
						case 'on_sale_products':
							array_push( $on_sale_rules, $valid_rule );
							break;
						case 'selected_categories':
							array_push( $categories_rules, $valid_rule );
							break;
						case 'selected_tags':
							array_push( $tags_rules, $valid_rule );
							break;
						default:
							array_push( $general_rules, $valid_rule );
					}
				}

				if ( ! empty( $product_rules ) ) {
					$valid_rule        = $product_rules[0];
					$calculated_points = $valid_rule->calculate_points( $product, $calculated_points, $currency );
				}
				if ( ! empty( $on_sale_rules ) ) {
					$valid_rule        = $on_sale_rules[0];
					$calculated_points = $valid_rule->calculate_points( $product, $calculated_points, $currency );
				} elseif ( ! empty( $categories_rules ) ) {
					$valid_rule        = $categories_rules[0];
					$calculated_points = $valid_rule->calculate_points( $product, $calculated_points, $currency );
				} elseif ( ! empty( $tags_rules ) ) {
					$valid_rule        = $tags_rules[0];
					$calculated_points = $valid_rule->calculate_points( $product, $calculated_points, $currency );
				} elseif ( ! empty( $general_rules ) ) {
					$valid_rule        = $general_rules[0];
					$calculated_points = $valid_rule->calculate_points( $product, $calculated_points, $currency );
				}
			}

			return $integer ? yith_ywpar_round_points( $calculated_points ) : $calculated_points;

		}

		/**
		 * Calculate the points of a product variable for a single item
		 *
		 * @param WC_Product_Variable|int $product Variable product.
		 * @param bool $integer Precision of points.
		 *
		 * @return int|string
		 * @since   1.0.0
		 */
		public function calculate_product_points_on_variable( $product, $integer = true ) {
			$calculated_points = 0;
			$product           = is_numeric( $product ) ? wc_get_product( $product ) : $product;

			if ( ! $product->is_type( 'variable' ) ) {
				return $calculated_points;
			}

			$variations = $product->get_available_variations();
			$points     = array();
			if ( ! empty( $variations ) ) {
				foreach ( $variations as $variation ) {
					$points[] = $this->calculate_product_points( $variation['variation_id'] );
				}
			}

			$points = array_unique( $points );

			if ( count( $points ) === 1 ) {
				$calculated_points = $points[0];
			} elseif ( count( $points ) > 0 ) {
				$calculated_points = min( $points ) . '-' . max( $points );
			}

			return apply_filters( 'ywpar_calculate_product_points_on_variable', $calculated_points, $product );
		}

		/**
		 * Return the conversion options
		 *
		 * @param string $currency Currency.
		 * @param bool|WC_Order $order Order.
		 *
		 * @return  array
		 * @since   1.0.0
		 */
		public function get_conversion_option( $currency = '', $order = false ) {
			$currency = ywpar_get_currency( $currency );

			$conversion = $this->get_main_conversion_option( $currency );

			$conversion['money']  = ( empty( $conversion['money'] ) ) ? 1 : $conversion['money'];
			$conversion['points'] = ( empty( $conversion['points'] ) ) ? 1 : $conversion['points'];

			return apply_filters( 'ywpar_conversion_points_rate', $conversion );
		}


		/**
		 * Return the main conversion rate
		 *
		 * @param string $currency Currency.
		 *
		 * @return  array
		 * @since   2.2.0
		 */
		public function get_main_conversion_option( $currency = '' ) {
			$currency   = ywpar_get_currency( $currency );
			$conversion = ywpar_get_option( 'earn_points_conversion_rate' );

			$conversion = isset( $conversion[ $currency ] ) ? $conversion[ $currency ] : array(
				'money'  => 0,
				'points' => 0,
			);

			return apply_filters( 'ywpar_conversion_points_rate', $conversion );
		}

		/**
		 * Assign Points to previous orders on user registration by user email = billing email.
		 *
		 * @param int $user_id user id.
		 *
		 * @since  1.7.3
		 * @author Armando Liccardo
		 */
		public function add_points_for_previous_orders_on_registration( $user_id ) {
			// getting the user.
			$customer = ywpar_get_customer( $user_id );

			if ( ! $customer ) {
				return;
			}

			$user_email = $customer->get_wc_customer()->get_email();

			if ( empty( $user_email ) ) {
				return;
			}

			$orders_query = array(
				'billing_email' => $user_email,
				'status'        => 'completed',
				'limit'         => - 1,
				'orderby'       => 'date',
				'order'         => 'DESC',
			);

			$start_date = yith_points()->points_log->get_start_date_of_all_actions();
			if ( $start_date ) {
				$orders_query['date_completed'] = '>=' . $start_date;
			}

			$orders = wc_get_orders( $orders_query );

			if ( $orders ) {
				foreach ( $orders as $order ) {
					$this->add_order_points( $order->get_id() );
				}
			}

		}

		/**
		 * Calculate order points from order items
		 *
		 * @param WC_Order $order Order.
		 * @param YITH_WC_Points_Rewards_Customer $customer Customer.
		 *
		 * @return int
		 */
		private function calculate_order_points_from_items( $order, $customer ) {
			$tot_points  = 0;
			$order_items = $order->get_items();

			if ( ! empty( $order_items ) ) {
				foreach ( $order_items as $order_item ) {
					$product     = $order_item->get_product();
					$price       = $order_item->get_subtotal();
					$item_points = $this->get_points_earned_from_price( $price, $order->get_currency() );

					if ( $product ) {
						$product_points = $this->get_product_points( $product, $order->get_currency(), true, $customer );
						// get the minor value.
						$item_points = $product_points < $item_points ? $product_points : $item_points;
					}

					$tot_points += $item_points * $order_item['qty'];
				}
			}

			if ( isset( $_REQUEST['action'] ) && 'ywpar_bulk_action' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) { //phpcs:ignore
				$coupons = $order->get_coupon_codes();

				if ( count( $coupons ) > 0 && ywpar_get_option( 'remove_points_coupon' ) === 'yes' ) {
					$tot_points -= $this->get_points_earned_from_price( $order->get_total_discount(), $order->get_currency() );
				}
			}

			return ( $tot_points < 0 ) ? 0 : yith_ywpar_round_points( $tot_points );
		}
	}

}
