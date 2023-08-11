<?php
/**
 * @link              https://xstore.8theme.com
 * @since             1.0.0
 * @package           XStore White Label Branding
 *
 * Plugin Name:       XStore White Label Branding
 * Plugin URI:        http://8theme.com
 * Description:       Take control of your branding and customize your website to perfection with our White Label Branding Plugin for XStore theme.
 * Version:           1.0.6
 * Author:            8theme
 * Author URI:        https://xstore.8theme.com
 * Text Domain:       xstore-white-label-branding
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
if ( ! defined( 'XSTORE_WHITE_LABEL_BRANDING_VERSION' ) ) {
	define( 'XSTORE_WHITE_LABEL_BRANDING_VERSION', '1.0.6' );
}
// Plugin Folder Path.
if ( ! defined( 'XSTORE_WHITE_LABEL_BRANDING_PLUGIN_DIR' ) ) {
	define( 'XSTORE_WHITE_LABEL_BRANDING_PLUGIN_DIR', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}
// Plugin Folder URL.
if ( ! defined( 'XSTORE_WHITE_LABEL_BRANDING_PLUGIN_URL' ) ) {
	define( 'XSTORE_WHITE_LABEL_BRANDING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin Styles
if ( !defined('XSTORE_WHITE_LABEL_BRANDING_CSS') ) {
	define('XSTORE_WHITE_LABEL_BRANDING_CSS', XSTORE_WHITE_LABEL_BRANDING_PLUGIN_URL . '/css/');
}

// Plugin scripts
if ( !defined('XSTORE_WHITE_LABEL_BRANDING_JS') ) {
	define('XSTORE_WHITE_LABEL_BRANDING_JS', XSTORE_WHITE_LABEL_BRANDING_PLUGIN_URL . '/js/');
}

register_activation_hook( __FILE__, array( 'XStore_White_Label_Branding', 'activation' ) );

if ( ! class_exists( 'XStore_White_Label_Branding' ) ) {

	/**
	 * Main XStore_White_Label_Branding Class.
	 *
	 * @since 1.0
	 */
	class XStore_White_Label_Branding {

		/**
		 * The one, true instance of this object.
		 *
		 * @since 1.0
		 * @static
		 * @access private
		 * @var object
		 */
		private static $instance;

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @since 1.0
		 * @static
		 * @access public
		 */
		public static function get_instance() {

			// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
			if ( null === self::$instance ) {
				self::$instance = new XStore_White_Label_Branding();
			}
			return self::$instance;
		}

		/**
		 * Initializes the plugin by setting localization, hooks, filters,
		 * and administrative functions.
		 *
		 * @since 1.0
		 * @access private
		 */
		private function __construct() {

			// Include required files.
			$this->includes();

			// Load plugin textdomain.
			$this->textdomain();
			
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function includes() {
			require_once XSTORE_WHITE_LABEL_BRANDING_PLUGIN_DIR . 'inc/xstore-branding-admin.php';
			require_once XSTORE_WHITE_LABEL_BRANDING_PLUGIN_DIR . 'inc/update.php';
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since 1.1
		 * @return void
		 */
		public function textdomain() {

			// Set text domain.
			$domain = 'xstore-white-label-branding';

			// Load textdomain for plugin.
			load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
		
		/**
		 * Description of the function.
		 *
		 * @param $links
		 * @return mixed
		 *
		 * @since 1.0.1
		 *
		 */
		public function plugin_action_links( $links ) {
			if ( ! defined( 'ETHEME_CODE_IMAGES' ) || ! current_user_can('manage_options') )
				return $links;
			
			$settings_link = sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=et-panel-white-label-branding' ), __( 'Settings', 'xstore-white-label-branding' ) );
			
			array_unshift( $links, $settings_link );
			
			return $links;
		}

		/**
		 * Run on plugin activation.
		 *
		 * @access private
		 * @since 1.1
		 * @return void
		 */
		public static function activation() {
		}
	}
} // End if().

/**
 * Instantiate XStore_White_Label_Branding class.
 *
 * @since 1.0
 * @return void
 */
function xstore_white_label_branding_activate() {
	XStore_White_Label_Branding::get_instance();
}
add_action( 'after_setup_theme', 'xstore_white_label_branding_activate', 11 );
