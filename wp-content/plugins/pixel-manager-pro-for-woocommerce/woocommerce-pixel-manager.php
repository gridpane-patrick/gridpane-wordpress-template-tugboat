<?php
/**
 * Plugin Name:          Pixel Manager Pro for WooCommerce
 * Description:          Visitor and conversion value tracking for WooCommerce. Highly optimized for data accuracy.
 * Author:               SweetCode
 * Plugin URI:           https://woocommerce.com/products/pixel-manager-pro-for-woocommerce/
 * Author URI:           https://sweetcode.com
 * Developer:            SweetCode
 * Developer URI:        https://sweetcode.com
 * Text Domain:          woocommerce-google-adwords-conversion-tracking-tag
 * Domain path:          /languages
 * Woo:                 18734000100772:676d930a45effbae15e3a023e452d2f6
 * Version:              1.17.11
 *
 * WC requires at least: 3.7
 * WC tested up to:      6.6
 *
 * License:              GNU General Public License v3.0
 * License URI:          http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @fs_premium_only /classes/pixels/bing/, /classes/pixels/twitter/, /classes/pixels/pinterest/, /classes/pixels/snapchat/, /classes/pixels/tiktok/, /classes/pixels/facebook/class-facebook-microdata.php, /classes/pixels/google/class-google-analytics-4-eec-pixel.php, /classes/pixels/google/class-google-analytics-ua-eec-pixel.php, /classes/pixels/google/class-google-analytics-ua-refund-pixel.php, /classes/http/, /js/public/wpm-public__premium_only.p1.min.js, /js/public/wpm-public__premium_only.p1.min.js.map
 **/

const WPM_CURRENT_VERSION = '1.17.11';

// TODO export settings function
// TODO add option checkbox on uninstall and ask if user wants to delete options from db

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use WCPM\Classes\Admin\Admin;
use WCPM\Classes\Admin\Environment_Check;
use WCPM\Classes\Db_Upgrade;
use WCPM\Classes\Default_Options;
use WCPM\Classes\Deprecated_Filters;
use WCPM\Classes\Pixels\Pixel_Manager;

class WCPM {

		protected $options;
		protected $environment_check;

	public function __construct() {

		define('WPM_PLUGIN_PREFIX', 'wpm_');
		define('WPM_DB_VERSION', '3');
		define('WPM_DB_OPTIONS_NAME', 'wgact_plugin_options');
		define('WPM_DB_NOTIFICATIONS_NAME', 'wgact_notifications');
		define('WPM_PLUGIN_DIR_PATH', plugin_dir_url(__FILE__));
		define('WPM_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
//			require_once dirname(__FILE__) . '/vendor/sweetcode-com/wp-flush/wp-flush.php';

		// check if WooCommerce is running
		// currently this is the most reliable test for single and multisite setups
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ($this->are_requirements_met()) {

			// autoloader
			require_once 'lib/autoload.php';

			if (is_readable(__DIR__ . '/vendor/autoload.php')) {
				require __DIR__ . '/vendor/autoload.php';
			}

			// running the DB updater
			if (get_option(WPM_DB_OPTIONS_NAME)) {
				( new Db_Upgrade() )->run_options_db_upgrade();
			}

			// load the options
			$this->wpm_options_init();

			$this->environment_check = new Environment_Check($this->options);

			if (isset($this->options['google']['gads']['dynamic_remarketing']) && $this->options['google']['gads']['dynamic_remarketing']) {
				// make sure to disable the WGDR plugin in case we use dynamic remarketing in this plugin
				add_filter('wgdr_third_party_cookie_prevention', '__return_true');
			}

			// run environment workflows
			add_action('admin_notices', [$this, 'run_admin_compatibility_checks']);
			add_action('admin_notices', [$this, 'environment_check_admin_notices']);
			add_action('admin_notices', function () {
				$this->environment_check->run_incompatible_plugins_checks();
			});

			$this->environment_check->disable_third_party_js_optimization();

			if ($this->options['general']['maximum_compatibility_mode']) {
				$this->environment_check->enable_compatibility_mode();
			}
			$this->environment_check->flush_cache_on_plugin_changes();
			register_activation_hook(__FILE__, [$this, 'plugin_activated']);
			register_deactivation_hook(__FILE__, [$this, 'plugin_deactivated']);

			if ($this->is_woocommerce_active()) {
				add_action('woocommerce_init', [$this, 'init']);
			} else {
				add_action('init', [$this, 'init']);
			}

		} else {
			add_action('admin_menu', [$this, 'add_empty_admin_page'], 99);
			add_action('admin_notices', [$this, 'requirements_error']);
		}
	}

	protected function is_woocommerce_active() {
		return is_plugin_active('woocommerce/woocommerce.php');
	}

	protected function are_requirements_met() {
		if (true) {
			return true;
		} else {
			return $this->is_woocommerce_active();
		}
	}

	public function add_empty_admin_page() {
		add_submenu_page(
			'woocommerce',
			esc_html__('Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag'),
			esc_html__('Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag'),
			'manage_options',
			'wpm',
			function () {
			}
		);
	}

		// https://github.com/iandunn/WordPress-Plugin-Skeleton/blob/master/views/requirements-error.php
	public function requirements_error() {
		?>

			<div class="error">
				<p>
					<strong>
					<?php esc_html_e('Pixel Manager Pro for WooCommerce error', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
					</strong>:
				<?php esc_html_e("Your environment doesn't meet all the system requirements listed below.", 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
				</p>

				<ul class="ul-disc">
					<li><?php esc_html_e('The WooCommerce plugin needs to be activated', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></li>
				</ul>
			</div>
			<style>
				.fs-tab {
					display: none !important;
				}
			</style>

			<?php
	}

	public function plugin_activated() {
		$this->environment_check->flush_cache_of_all_cache_plugins();
	}

	public function plugin_deactivated() {
		$this->environment_check->flush_cache_of_all_cache_plugins();
	}

	public function environment_check_admin_notices() {

		if (apply_filters('wpm_show_admin_alerts', apply_filters_deprecated('wooptpm_show_admin_alerts', [true], '1.13.0', 'wpm_show_admin_alerts'))) {
			$this->environment_check->check_active_off_site_payment_gateways();
		}

		// https://developer.wordpress.org/reference/hooks/admin_notices/#comment-5163
//			if (defined('DISABLE_NAG_NOTICES') && DISABLE_NAG_NOTICES) {
//				// do some stuff
//			}
	}

		// startup all functions
	public function init() {

		if (is_admin()) {

			// display admin views
			new Admin($this->options);

			// add a settings link on the plugins page
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'wpm_settings_link']);
		}

		( new Deprecated_Filters() )->load_deprecated_filters();

		// inject pixels into front end
//			add_action('after_setup_theme', [$this, 'inject_pixels']);
		$this->inject_pixels();
	}

	public function inject_pixels() {

		// TODO Remove the cookie prevention filters by January 2023
		$cookie_prevention = apply_filters_deprecated(
			'wgact_cookie_prevention',
			[false],
			'1.10.4',
			'wooptpm_cookie_prevention'
		);

		$cookie_prevention = apply_filters_deprecated(
			'wooptpm_cookie_prevention', [$cookie_prevention],
			'1.12.1',
			'',
			'This filter has been replaced by a much more robust cookie consent handing in the plugin. Please read more about it in the documentation.'
		);

		if (false === $cookie_prevention) {

			// inject pixels
			new Pixel_Manager($this->options);
		}
	}

	public function run_admin_compatibility_checks() {
		$this->environment_check->run_checks();
	}

		// initialise the options

		/**
		 * Noinspection
		 *
		 * @noinspection GrazieInspection
		 */
	private function wpm_options_init() {

		// set options equal to defaults
		$this->options = get_option(WPM_DB_OPTIONS_NAME);

		if (false === $this->options) { // if no options have been set yet, initiate default options

			$this->options = ( new Default_Options() )->get_default_options();

		} else {  // Check if each single option has been set. If not, set them. That is necessary when new options are introduced.

			// add new default options to the options db array
			$this->options = ( new Default_Options() )->update_with_defaults($this->options, ( new Default_Options() )->get_default_options());
		}

		update_option(WPM_DB_OPTIONS_NAME, $this->options);
	}

		/**
		 * Adds a link on the plugins page for the settings
		 * ! It can't be required. Must be in the main plugin file!
		 */
	public function wpm_settings_link( $links ) {

		if (is_plugin_active('woocommerce/woocommerce.php')) {
			$admin_page = 'admin.php';
		} else {
			$admin_page = 'options-general.php';
		}

		$links[] = '<a href="' . admin_url($admin_page . '?page=wpm') . '">Settings</a>';

		return $links;
	}

	protected function is_development_install() {

		if (class_exists('FS_Site')) {
			return FS_Site::is_localhost_by_address(get_site_url());
		} else {
			return false;
		}
	}
}

	new WCPM();
