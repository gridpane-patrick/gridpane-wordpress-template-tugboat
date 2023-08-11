<?php

namespace Vibe\Split_Orders;

use Vibe\Split_Orders\Addons\Braintree;
use Vibe\Split_Orders\Addons\Sequential_Order_Numbers_Pro;
use Vibe\Split_Orders\Addons\Stripe;
use Vibe\Split_Orders\Addons\Subscriptions;
use Vibe\Split_Orders\Upgrades\Upgrades;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Main plugin class, responsible for initialising the plugin
 *
 * @since 1.0
 */
final class Split_Orders {

	/**
	 * The single instance of the class
	 *
	 * @var Split_Orders
	 */
	private static $instance;

	/**
	 * Current version of the plugin
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Path to the plugin
	 *
	 * @var string
	 */
	private $path;

	/**
	 * URI of the plugin
	 *
	 * @var string
	 */
	private $uri;

	/**
	 * Array of required core classes
	 *
	 * @var array
	 */
	private $core = array();

	/**
	 * Returns the singleton instance of this class
	 *
	 * @return Split_Orders The singleton instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor to prevent multiple instantiation.
	 */
	private function __construct() {
		$this->version = defined( 'VIBE_SPLIT_ORDERS_VERSION' ) ? VIBE_SPLIT_ORDERS_VERSION : '';

		$parent = plugin_dir_path( __FILE__ );

		$this->path = plugin_dir_path( $parent );
		$this->uri  = plugin_dir_url( $parent );

		$this->init_core();

		/**
		 * Plugin loaded
		 */
		do_action( self::hook_prefix( 'loaded' ) );
	}

	/**
	 * Constructs required core classes
	 */
	private function init_core() {
		$this->core[ Admin::class ]    = new Admin();
		$this->core[ Settings::class ] = new Settings();
		$this->core[ AJAX::class ]     = new AJAX();
		$this->core[ Orders::class ]   = new Orders();
		$this->core[ Emails::class ]   = new Emails();
		$this->core[ Upgrades::class ] = new Upgrades();

		// Payment gateway integrations
		$this->core[ PayPal::class ]               = new PayPal();
		$this->core[ WooCommerce_Payments::class ] = new WooCommerce_Payments();

		// Ensure plugin functions are loaded before checking plugin activation
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( is_plugin_active( 'woocommerce-sequential-order-numbers-pro/woocommerce-sequential-order-numbers-pro.php' ) ) {
			$this->core[ Sequential_Order_Numbers_Pro::class ] = new Sequential_Order_Numbers_Pro();
		}

		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			$this->core[ Subscriptions::class ] = new Subscriptions();
		}

		if ( is_plugin_active( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php' ) ) {
			$this->core[ Stripe::class ] = new Stripe();
		}

		if ( is_plugin_active( 'woocommerce-gateway-paypal-powered-by-braintree/woocommerce-gateway-paypal-powered-by-braintree.php' ) ) {
			$this->core[ Braintree::class ] = new Braintree();
		}
	}

	/**
	 * Returns the current version of the plugin
	 *
	 * @return string Version number of the plugin
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Returns the path to the plugin with the given path appended
	 *
	 * @param string $append Optional string to append to the path without leading slash
	 *
	 * @return string The path to the plugin with provided path appended
	 */
	public function path( $append = '' ) {
		if ( $append ) {
			return trailingslashit( $this->path ) . $append;
		}

		return $this->path;
	}

	/**
	 * Returns the URI of the plugin with the given path appended
	 *
	 * @param string $append Optional string to append to the URI without leading slash
	 *
	 * @return string The URI of the plugin
	 */
	public function uri( $append = '' ) {
		if ( $append ) {
			return trailingslashit( $this->uri ) . $append;
		}

		return $this->uri;
	}

	/**
	 * Returns the requested core class instances
	 *
	 * @param string $name The name of the core class instance to return
	 *
	 * @return object The requested core class instance or null if it does not exist
	 */
	public function __get( $name ) {
		return isset( $this->core[ $name ] ) ? $this->core[ $name ] : null;
	}

	/**
	 * Appends the plugin's hook prefix to the given hook name
	 *
	 * @param string $hook The hook to prefix
	 *
	 * @return string The prefixed hook
	 */
	public static function hook_prefix( $hook ) {
		return "vibe_split_orders_{$hook}";
	}

	/**
	 * Private method to prevent cloning the singleton instance
	 *
	 * @return void
	 */
	private function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Nope', 'split-orders' ) ), '1.0.0' );
	}

	/**
	 * Triggers an error to prevent unserializing the singleton instance
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Nope', 'split-orders' ) ), '1.0.0' );
	}
}
