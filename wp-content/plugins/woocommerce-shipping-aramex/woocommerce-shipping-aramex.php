<?php
/**
 * Plugin Name: WooCommerce Aramex
 * Plugin URI: https://woocommerce.com/products/woocommerce-shipping-aramex/
 * Description: Offer shipping of your WooCommerce orders using the Aramex courier company.
 * Author: WooCommerce
 * Author URI: https://woocommerce/
 * Version: 1.0.10
 * WC tested up to: 3.6
 * WC requires at least: 2.6
 *
 * Copyright (c) 2017 WooCommerce
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * Woo: 976365:f58e7b801b2b7a4c5d722b89ea49d996
 * @package WC_Shipping_Aramex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'f58e7b801b2b7a4c5d722b89ea49d996', '976365' );

if ( ! defined( 'WC_SHIPPING_ARAMEX_VERSION' ) ) {
	define( 'WC_SHIPPING_ARAMEX_VERSION', '1.0.10' );
}

if ( ! class_exists( 'WC_Aramex' ) ) :

	/**
	 * Plugin main class.
	 */
	class WC_Aramex {
		/**
		 * WC_Aramex The single instance of WC_Aramex.
		 *
		 * @var   object
		 * @access  private
		 * @since   1.0.0
		 */
		private static $_instance = null;

		/**
		 * Plugin base directory.
		 *
		 * @since 1.0.2
		 *
		 * @var string
		 */
		public $plugin_dir;

		/**
		 * Plugin base URL.
		 *
		 * @since 1.0.2
		 *
		 * @var string
		 */
		public $plugin_url;

		/**
		 * Instance of order admin stuff.
		 *
		 * @since 1.0.2
		 *
		 * @var WC_Shipping_Aramex_Order_Admin
		 */

		/**
		 * Construct the plugin.
		 */
		public function __construct() {
			$this->plugin_dir = untrailingslashit( plugin_dir_path( __FILE__ ) );
			$this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );

			// Load updater class.
			require_once( $this->plugin_dir . '/includes/class-wc-shipping-aramex-updater.php' );

			// Load order admin handler.
			require_once( $this->plugin_dir . '/includes/class-wc-shipping-aramex-order-admin.php' );
			$this->order_admin = new WC_Shipping_Aramex_Order_Admin();

			add_action( 'plugins_loaded', array( $this, 'init' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_action( 'init', array( 'WC_Shipping_Aramex_Update', 'check_update' ) );
			add_action( 'admin_notices', array( 'WC_Shipping_Aramex_Update', 'check_update_notices' ) );
			add_action( 'wp_ajax_wc_shipping_aramex_dismiss_upgrade_notice', array( 'WC_Shipping_Aramex_Update', 'dismiss_update_notice' ) );
			load_plugin_textdomain( 'woocommerce-shipping-aramex', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Main WC_Aramex Instance
		 *
		 * Ensures only one instance of WC_Aramex is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see WC_Aramex()
		 * @return Main WC_Aramex instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Initialize the plugin.
		 *
		 * @since 1.0.0
		 */
		public function init() {
			// Bail out if WooCommerce is not active.
			if ( ! is_woocommerce_active() ) {
				return;
			}

			require_once( $this->plugin_dir . '/includes/class-wc-shipping-aramex-privacy.php' );

			// Register the shipping method class based on shipping zone availability.
			$class_filepath = ( version_compare( WC_VERSION, '2.6.0', '>=' ) )
				?  $this->plugin_dir . '/includes/class-wc-shipping-aramex.php'
				:  $this->plugin_dir . '/includes/legacy/class-wc-shipping-aramex.php';

			require_once( $class_filepath );

			add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
		}

		/**
		 * Added Aramex custom plugin action links.
		 *
		 * @since 1.0.3
		 * @version 1.0.3
		 *
		 * @param array $links Links.
		 *
		 * @return array Links.
		 */
		public function plugin_action_links( $links ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipping_aramex' ) . '">' . __( 'Settings', 'woocommerce-shipping-aramex' ) . '</a>',
				'<a href="https://woocommerce.com/my-account/tickets">' . __( 'Support', 'woocommerce-shipping-aramex' ) . '</a>',
				'<a href="https://docs.woocommerce.com/document/woocommerce-shipping-aramex/">' . __( 'Docs', 'woocommerce-shipping-aramex' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Add a new shipping method into WooCommerce.
		 *
		 * @since 1.0.0
		 * @version 1.0.2
		 *
		 * @param array $methods Shipping methods.
		 */
		public function add_shipping_method( $methods ) {
			if ( version_compare( WC_VERSION, '2.6', '>=' ) ) {
				$methods['wc_shipping_aramex'] = 'WC_Shipping_Aramex';
			} else {
				$methods[] = 'WC_Shipping_Aramex';
			}
			return $methods;
		}
	}

	/**
	 * Returns the main instance of WC_Aramex to prevent the need to use globals.
	 *
	 * @since 1.0.0
	 *
	 * @return object WC_Aramex
	 */
	function WC_Aramex() {
		return WC_Aramex::instance();
	}

	WC_Aramex();

endif;
