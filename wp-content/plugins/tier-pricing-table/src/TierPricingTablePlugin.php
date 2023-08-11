<?php namespace TierPricingTable;

use TierPricingTable\Addons\Addons;
use TierPricingTable\API\WooCommerceRESTAPI;
use TierPricingTable\Core\FileManager;
use TierPricingTable\Core\AdminNotifier;
use TierPricingTable\Settings\Settings;
use TierPricingTable\Admin\Admin;
use TierPricingTable\Frontend\Frontend;
use TierPricingTable\BackgroundProcessing\Updater\Updater;

/**
 * Class TierPricingTablePlugin
 *
 * @package TierPricingTable
 */
class TierPricingTablePlugin {

	/**
	 * FileManager
	 *
	 * @var FileManager
	 */
	private $fileManager;

	/**
	 * Settings
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * AdminNotifier
	 *
	 * @var AdminNotifier
	 */
	private $notifier;

	/**
	 * Initialization all plugin integrations
	 *
	 * @var Integrations\Integrations
	 */
	private $integrations;

	/**
	 * Addons
	 *
	 * @var Addons
	 */
	private $addons;

	const VERSION = '4.4.0';

	const DB_VERSION = '1.0.0';

	/**
	 * TierPricingTablePlugin constructor.
	 *
	 * @param string $mainFile
	 */
	public function __construct( $mainFile ) {
		$this->fileManager = new FileManager( $mainFile, 'tier-pricing-table' );
		$this->settings    = new Settings( $this->fileManager );
		$this->notifier    = new AdminNotifier();

		$this->integrations = new Integrations\Integrations();
		$this->addons       = new Addons( $this->fileManager, $this->settings );

		add_action( 'init', array( $this, 'loadTextDomain' ), - 999 );
		add_action( 'admin_init', array( $this, 'checkRequirePlugins' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( $this->fileManager->getMainFile() ),
			array( $this, 'addPluginAction' ), 10, 4 );
	}

	/**
	 * Add setting to plugin actions at plugins list
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	public function addPluginAction( $actions ) {
		$actions[] = '<a href="' . $this->settings->getLink() . '">' . __( 'Settings', 'tier-pricing-table' ) . '</a>';

		return $actions;
	}

	/**
	 * Run plugin part
	 */
	public function run() {
		$updater = new Updater();

		if ( ! $updater->checkForUpdates() ) {

			new Admin( $this->fileManager, $this->settings );
			new Frontend( $this->fileManager, $this->settings );

			new CartPriceManager( $this->settings );
			new CatalogPriceManager( $this->settings );

			new WooCommerceRESTAPI();

		} else {
			$updater->update();
		}
	}

	/**
	 * Load plugin translations
	 */
	public function loadTextDomain() {
		$name = $this->fileManager->getPluginName();
		load_plugin_textdomain( 'tier-pricing-table', false, $name . '/languages/' );
	}

	/**
	 * Validate required plugins
	 *
	 * @return array
	 */
	private function validateRequiredPlugins() {
		$plugins = array();

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		/**
		 * Check if WooCommerce is active
		 **/
		if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) ) {
			$plugins[] = '<a target="_blank" href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>';
		}

		return $plugins;
	}

	/**
	 * Check required plugins and push notifications
	 */
	public function checkRequirePlugins() {
		/* translators: %s: required plugin */
		$message = __( 'The Tiered Pricing Table plugin requires %s plugin to be active!', 'tier-pricing-table' );

		$plugins = $this->validateRequiredPlugins();

		if ( count( $plugins ) ) {
			foreach ( $plugins as $plugin ) {
				$error = sprintf( $message, $plugin );
				$this->notifier->push( $error, AdminNotifier::ERROR, false );
			}
		}
	}

	/**
	 * Fired during plugin uninstall
	 */
	public static function uninstall() {

		delete_option( Settings::SETTINGS_PREFIX . 'display' );
		delete_option( Settings::SETTINGS_PREFIX . 'position_hook' );
		delete_option( Settings::SETTINGS_PREFIX . 'head_quantity_text' );
		delete_option( Settings::SETTINGS_PREFIX . 'head_price_text' );
		delete_option( Settings::SETTINGS_PREFIX . 'display_type' );
		delete_option( Settings::SETTINGS_PREFIX . 'selected_quantity_color' );
		delete_option( Settings::SETTINGS_PREFIX . 'table_title' );
		delete_option( Settings::SETTINGS_PREFIX . 'table_css_class' );
		delete_option( Settings::SETTINGS_PREFIX . 'tooltip_size' );
		delete_option( Settings::SETTINGS_PREFIX . 'show_discount_in_cart' );
		delete_option( Settings::SETTINGS_PREFIX . 'tiered_price_at_catalog' );
		delete_option( Settings::SETTINGS_PREFIX . 'show_discount_column' );
		delete_option( Settings::SETTINGS_PREFIX . 'tiered_price_at_catalog_type' );
		delete_option( Settings::SETTINGS_PREFIX . 'lowest_prefix' );
		delete_option( Settings::SETTINGS_PREFIX . 'head_discount_text' );
		delete_option( Settings::SETTINGS_PREFIX . 'clickable_table_rows' );
		delete_option( Settings::SETTINGS_PREFIX . 'show_total_price' );
		delete_option( Settings::SETTINGS_PREFIX . 'tiered_price_at_product_page' );

		delete_option( Updater::DB_OPTION );
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		set_transient( 'tiered_pricing_table_activated', true, 100 );
	}

	public function deactivate() {

	}
}
