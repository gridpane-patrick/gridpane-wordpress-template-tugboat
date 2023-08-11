<?php

namespace WCPM\Classes\Admin;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Environment_Check {

	public  $notifications;
	private $documentation;
	private $order_redirect = null;
	private $options;

	public function __construct( $options ) {

		if (is_admin()) {

			$this->options = $options;

			$this->notifications = new Notifications();
			$this->documentation = new Documentation();

//            add_action('admin_enqueue_scripts', [$this, 'environment_check_script']);
			add_action('wp_ajax_environment_check_handler', [$this, 'ajax_environment_check_handler']);

			// get all active payment gateways
//            add_action('plugins_loaded', [$this, 'get_active_payment_gateways_after_plugins_loaded']);

			add_action('admin_enqueue_scripts', [$this, 'wpm_notification_scripts']);
		}
	}

	public function is_allowed_notification_page( $page ) {

		$allowed_pages = ['page_wpm', 'index.php', 'dashboard'];

		foreach ($allowed_pages as $allowed_page) {
			if (strpos($page, $allowed_page) !== false) {
				return true;
			}
		}

		return false;
	}

	public function is_not_allowed_notification_page( $page ) {
		return !$this->is_allowed_notification_page($page);
	}

	public function wpm_notification_scripts( $hook_suffix ) {

		if ($this->is_not_allowed_notification_page($hook_suffix)) {
			return;
		}

		wp_enqueue_script(
			'wpm-notification',
			WPM_PLUGIN_DIR_PATH . 'js/admin/environment-check.js',
			['jquery'],
			WPM_CURRENT_VERSION,
			false
		);
	}

	public function run_incompatible_plugins_checks() {

		$saved_notifications = get_option(WPM_DB_NOTIFICATIONS_NAME);

		foreach ($this->get_incompatible_plugins_list() as $plugin) {

			if (is_array($saved_notifications) && !array_key_exists($plugin['slug'], $saved_notifications) && is_plugin_active($plugin['file_location'])) {

				( new Notifications() )->plugin_is_incompatible(
					$plugin['name'],
					$plugin['version'],
					$plugin['slug'],
					$plugin['link'],
					$plugin['wpm_doc_link']
				);
			}
		}
	}

	public function get_incompatible_plugins_list() {
		return [
			'wc-custom-thank-you' => [
				'name'          => 'WC Custom Thank You',
				'slug'          => 'wc-custom-thank-you',
				'file_location' => 'wc-custom-thank-you/woocommerce-custom-thankyou.php',
				'link'          => 'https://wordpress.org/plugins/wc-custom-thank-you/',
				'wpm_doc_link'  => $this->documentation->get_link('custom_thank_you'),
				'version'       => '1.2.1',
			]
		];
	}

	public function flush_cache_on_plugin_changes() {

		// flush cache after saving the plugin options
		add_action('update_option_wgact_plugin_options', [$this, 'flush_cache_of_all_cache_plugins'], 10, 3);

		// flush cache after install
		// we don't need that because after first install the user needs to set new options anyway where the cache flush happens too
//        add_filter('upgrader_post_install', [$this, 'flush_cache_of_all_cache_plugins'], 10, 3);

		// flush cache after plugin update
		add_action('upgrader_process_complete', [$this, 'upgrader_flush_cache_if_pmw_was_updated'], 10, 2);
	}

	public function upgrader_flush_cache_if_pmw_was_updated( $upgrader_object, $options ) {

		if (
			isset($options['type']) &&
			'plugin' === $options['type'] &&
			isset($options['plugins']) &&
			is_array($options['plugins']) &&
			in_array(WPM_PLUGIN_BASE_NAME, $options['plugins'], true)
		) {
			$this->flush_cache_of_all_cache_plugins();
		}
	}

	public function flush_cache_of_all_cache_plugins() {

//        error_log('flush cache of all cache plugins');
		if ($this->is_wp_rocket_active()) {
			$this->flush_wp_rocket_cache();
		}                                                                              // works
		if ($this->is_litespeed_active()) {
			$this->flush_litespeed_cache();
		}                                                                              // works
		if ($this->is_autoptimize_active()) {
			$this->flush_autoptimize_cache();
		}                                                                              // works
		if ($this->is_hummingbird_active()) {
			$this->flush_hummingbird_cache();
		}                                                                              // works
		if ($this->is_nitropack_active()) {
			$this->flush_nitropack_cache();
		}                                                                              // works
		if ($this->is_sg_optimizer_active()) {
			$this->flush_sg_optimizer_cache();
		}                                                                              // works
		if ($this->is_w3_total_cache_active()) {
			$this->flush_w3_total_cache();
		}                                                                              // works
		if ($this->is_wp_optimize_active()) {
			$this->flush_wp_optimize_cache();
		}                                                                              // works
		if ($this->is_wp_super_cache_active()) {
			$this->flush_wp_super_cache();
		}                                                                              // works
		if ($this->is_wp_fastest_cache_active()) {
			$this->flush_wp_fastest_cache();
		}                                                                              // works
		if ($this->is_cloudflare_active()) {
			$this->flush_cloudflare_cache();
		}                                                                              // works
		if ($this->is_flying_press_active()) {
			$this->flush_flying_press_cache();
		}                                                                              // works


		if ($this->is_hosting_wp_engine()) {
			$this->flush_wp_engine_cache();
		}         // works
//        if ($this->is_hosting_pagely()) $this->flush_pagely_cache();               // TODO test
		if ($this->is_hosting_kinsta()) {
			$this->flush_kinsta_cache();
		}                                                                          // TODO test
//
//        if ($this->is_nginx_helper_active()) $this->flush_nginx_cache();           // TODO test

		// TODO add generic varnish purge
	}

	private function flush_kinsta_cache() {
		try {
			wp_remote_get('https://localhost/kinsta-clear-cache-all', [
				'sslverify' => false,
				'timeout'   => 5
			]);

		} catch (\Exception $e) {
			error_log($e);
		}
	}

	public function is_nginx_helper_active() {
		return defined('NGINX_HELPER_BASEPATH');
	}

	private function flush_nginx_cache() {
		global $nginx_purger;
		if ($nginx_purger) {
			$nginx_purger->purge_all();
		}
		return true;
	}

	public function flush_cloudflare_cache() {
		try {
			if (class_exists('\CF\WordPress\Hooks')) {
				( new \CF\WordPress\Hooks() )->purgeCacheEverything();
			}
		} catch (\Exception $e) {
			error_log($e);
		}
	}

	public function flush_flying_press_cache() {
		try {
			if (class_exists('\FlyingPress\Purge') && method_exists('\FlyingPress\Purge', 'purge_cached_pages')) {
				\FlyingPress\Purge::purge_cached_pages();
			}

//			/**
//			 * Update config and flush cache
//			 */
//			if (class_exists('\FlyingPress\Config') && class_exists('\FlyingPress\Purge')) {
//
//				// This is the way to update the Flying Press settings.
//				// No other 'normal' way is possible.
//
//				if (!is_array(\FlyingPress\Config::$config['js_defer_excludes'])) {
//					\FlyingPress\Config::$config['js_defer_excludes'] = [];
//				}
//
//				\FlyingPress\Config::$config['js_defer_excludes'] = array_unique(array_merge(\FlyingPress\Config::$config['js_defer_excludes'], $this->get_wpm_script_identifiers()));
//
//				if (
//					method_exists('\FlyingPress\Config', 'update_config') &&
//					method_exists('\FlyingPress\Purge', 'purge_cached_pages')
//				) {
//					\FlyingPress\Config::update_config(\FlyingPress\Config::$config);
//					\FlyingPress\Purge::purge_cached_pages();
//				}
//			}

		} catch (\Exception $e) {
			error_log($e);
		}
	}

	public function flush_wp_engine_cache() {
		try {
			if (class_exists('WpeCommon')) {
				\WpeCommon::purge_varnish_cache_all();
			}
		} catch (\Exception $e) {
			error_log($e);
		}
	}

	private function flush_pagely_cache() {
		try {
			if (class_exists('PagelyCachePurge')) { // We need to have this check for clients that switch hosts
				$pagely = new \PagelyCachePurge();
				$pagely->purgeAll();
			}
		} catch (\Exception $e) {
			error_log($e);
		}
	}

	public function flush_wp_fastest_cache() {
		if (function_exists('wpfc_clear_all_cache')) {
			wpfc_clear_all_cache(true);
		}
	}

	public function flush_wp_super_cache() {
		if (function_exists('wp_cache_clean_cache')) {
			global $file_prefix;
			wp_cache_clean_cache($file_prefix, true);
		}
	}

	public function flush_wp_optimize_cache() {
		if (function_exists('wpo_cache_flush')) {
			wpo_cache_flush();
		}
	}

	public function flush_w3_total_cache() {
		if (function_exists('w3tc_flush_all')) {
			w3tc_flush_all();
		}
	}

	public function flush_sg_optimizer_cache() {
		if (function_exists('sg_cachepress_purge_everything')) {
			sg_cachepress_purge_everything();
		}
	}

	public function flush_nitropack_cache() {
		try {
			if (class_exists('\NitroPack\SDK\Api\Cache')) {
				$siteId     = get_option('nitropack-siteId');
				$siteSecret = get_option('nitropack-siteSecret');
				( new \NitroPack\SDK\Api\Cache($siteId, $siteSecret) )->purge();
			}

		} catch (\Exception $e) {
			error_log($e);
		}

//        do_action('nitropack_integration_purge_all');
	}

	public function flush_hummingbird_cache() {
		do_action('wphb_clear_page_cache');
	}

	public function flush_autoptimize_cache() {
		if (class_exists('autoptimizeCache')) {
			// we need the backslash because autoptimizeCache is in the global namespace
			// and otherwise our plugin would search in its own namespace and throw an error
			\autoptimizeCache::clearall();
		}
	}

	public function flush_litespeed_cache() {
		do_action('litespeed_purge_all');
	}

	protected function flush_wp_rocket_cache() {
		// flush WP Rocket cache
		if (function_exists('rocket_clean_domain')) {
			rocket_clean_domain();
		}

		// Preload cache.
		if (function_exists('run_rocket_bot')) {
			run_rocket_bot();
		}

		if (function_exists('run_rocket_sitemap_preload')) {
			run_rocket_sitemap_preload();
		}
	}

	public function check_active_off_site_payment_gateways() {

		if ($this->is_not_allowed_notification_page(get_current_screen()->id)) {
			return;
		}

		$wpm_notifications = get_option(WPM_DB_NOTIFICATIONS_NAME);

		if (
			!is_array($wpm_notifications) ||
			!array_key_exists('dismiss_paypal_standard_warning', $wpm_notifications) ||
			true !== $wpm_notifications['dismiss_paypal_standard_warning']
		) {
			if ($this->is_paypal_standard_active()) {
				// run off-site payment gateway warning
				( new Notifications() )->paypal_standard_active_warning();
			}
		}
	}

	public function get_active_payment_gateways_after_plugins_loaded() {
		error_log(print_r($this->get_active_payment_gateways(), true));
	}

	private static function get_active_payment_gateways() {
		$active_gateways = [];
		$gateways        = WC()->payment_gateways->payment_gateways();
		foreach ($gateways as $id => $gateway) {
			if (isset($gateway->enabled) && 'yes' === $gateway->enabled) {
				$active_gateways[$id] = [
					'title'    => $gateway->title,
					'supports' => $gateway->supports,
				];
			}
		}

		return $active_gateways;
	}

	public function run_checks() {
//        $this->check_wp_rocket_js_concatenation();
//        $this->check_litespeed_js_inline_after_dom();
	}

	public function environment_check_script() {
		wp_enqueue_script('wpm-environment-check', WPM_PLUGIN_DIR_PATH . 'js/admin/environment-check.js', ['jquery'], WPM_CURRENT_VERSION, true);
	}

	public function ajax_environment_check_handler() {

		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if (isset($_post['set'])) {

			$set = $_post['set'];

			if ('disable_wp_rocket_javascript_concatenation' == $set) {
				$wp_rocket_options                          = get_option('wp_rocket_settings');
				$wp_rocket_options['minify_concatenate_js'] = 0;
				update_option('wp_rocket_settings', $wp_rocket_options);
			}

			if ('dismiss_wp_rocket_javascript_concatenation_error' == $set) {
				$wpm_notifications                                                     = get_option(WPM_DB_NOTIFICATIONS_NAME);
				$wpm_notifications['dismiss_wp_rocket_javascript_concatenation_error'] = true;
				update_option(WPM_DB_NOTIFICATIONS_NAME, $wpm_notifications);
			}

			if ('disable_litespeed_inline_js_dom_ready' == $set) {
				$litespeed_inline_js_dom_ready_option = 0;
				update_option('litespeed.conf.optm-js_inline_defer', $litespeed_inline_js_dom_ready_option);
			}

			if ('dismiss_litespeed_inline_js_dom_ready' == $set) {
				$wpm_notifications                                                = get_option(WPM_DB_NOTIFICATIONS_NAME);
				$wpm_notifications['dismiss_litespeed_inline_js_dom_ready_error'] = true;
				update_option(WPM_DB_NOTIFICATIONS_NAME, $wpm_notifications);
			}

			if ('dismiss_paypal_standard_warning' == $set) {
				$wpm_notifications                                    = get_option(WPM_DB_NOTIFICATIONS_NAME);
				$wpm_notifications['dismiss_paypal_standard_warning'] = true;
				update_option(WPM_DB_NOTIFICATIONS_NAME, $wpm_notifications);
			}
		} else {
			if (isset($_post['disable_warning'])) {
				$wpm_notifications                            = get_option(WPM_DB_NOTIFICATIONS_NAME);
				$wpm_notifications[$_post['disable_warning']] = true;
				update_option(WPM_DB_NOTIFICATIONS_NAME, $wpm_notifications);
			}
		}

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	private function check_wp_rocket_js_concatenation() {
		$wpm_notifications = get_option(WPM_DB_NOTIFICATIONS_NAME);

		if ($this->is_wp_rocket_active() && ( !is_array($wpm_notifications) || false == $wpm_notifications['dismiss_wp_rocket_javascript_concatenation_error'] )) {

			$wp_rocket_settings = get_option('wp_rocket_settings');

			if ($wp_rocket_settings) {
				if (true == $wp_rocket_settings['minify_concatenate_js']) {
					// display warning
					( new Notifications() )->wp_rocket_js_concatenation_error();
				}
			}
		}
	}

	private function check_litespeed_js_inline_after_dom() {
		$wpm_notifications = get_option(WPM_DB_NOTIFICATIONS_NAME);

		if ($this->is_litespeed_active() && ( !is_array($wpm_notifications) || false == $wpm_notifications['dismiss_litespeed_inline_js_dom_ready_error'] )) {

			$litespeed_js_inline_defer_settings = get_option('litespeed.conf.optm-js_inline_defer');

			if ($litespeed_js_inline_defer_settings) {
				if (1 == $litespeed_js_inline_defer_settings) {
					// display warning
					( new Notifications() )->litespeed_js_defer_error();
				}
			}
		}
	}

	public function is_paypal_standard_active() {
		$woocommerce_paypal_settings = get_option('woocommerce_paypal_settings');

		if (
			!is_bool($woocommerce_paypal_settings) &&
			array_key_exists('enabled', $woocommerce_paypal_settings) &&
			'yes' === $woocommerce_paypal_settings['enabled']) {
			return true;
		} else {
			return false;
		}
	}

	public function is_wc_custom_thank_you_active() {
		return is_plugin_active('wc-custom-thank-you/woocommerce-custom-thankyou.php');
	}

	public function is_wp_rocket_active() {
		return is_plugin_active('wp-rocket/wp-rocket.php');
	}

	public function is_sg_optimizer_active() {
		return is_plugin_active('sg-cachepress/sg-cachepress.php');
	}

	public function is_w3_total_cache_active() {
		return is_plugin_active('w3-total-cache/w3-total-cache.php');
	}

	public function is_litespeed_active() {
		// TODO find out if there is a pro version with different folder and file name

		return is_plugin_active('litespeed-cache/litespeed-cache.php');
	}

	public function is_autoptimize_active() {
		// TODO find out if there is a pro version with different folder and file name

		return is_plugin_active('autoptimize/autoptimize.php');
	}

	public function is_hummingbird_active() {
		// TODO find out if there is a pro version with different folder and file name

		return is_plugin_active('hummingbird-performance/wp-hummingbird.php');
	}

	public function is_nitropack_active() {
		// TODO find out if there is a pro version with different folder and file name

		return is_plugin_active('nitropack/main.php');
	}

	public function is_yoast_seo_active() {
		// TODO find out if there is a pro version with different folder and file name

		return is_plugin_active('wordpress-seo/wp-seo.php');
	}

	public function is_borlabs_cookie_active() {
		// TODO find out if there is a pro version with different folder and file name

		return is_plugin_active('borlabs-cookie/borlabs-cookie.php');
	}

	public function is_cookiebot_active() {
		return is_plugin_active('cookiebot/cookiebot.php');
	}

	public function is_complianz_active() {
		return is_plugin_active('complianz-gdpr/complianz-gpdr.php');
	}

	// Cookie Notice by hu-manity.co
	public function is_cookie_notice_active() {
		return is_plugin_active('cookie-notice/cookie-notice.php');
	}

	public function is_cookie_script_active() {
		return is_plugin_active('cookie-script-com/cookie-script.php');
	}

	public function is_moove_gdpr_active() {
		return is_plugin_active('gdpr-cookie-compliance/moove-gdpr.php');
	}

	public function is_cookie_law_info_active() {
		return is_plugin_active('cookie-law-info/cookie-law-info.php');
	}

	public function is_some_cmp_active() {
		if (
			$this->is_borlabs_cookie_active() ||
			$this->is_cookiebot_active() ||
			$this->is_complianz_active() ||
			$this->is_cookie_notice_active() ||
			$this->is_cookie_script_active() ||
			$this->is_moove_gdpr_active() ||
			$this->is_cookie_law_info_active()
		) {
			return true;
		} else {
			return false;
		}
	}

	public function is_woocommerce_active() {
		return is_plugin_active('woocommerce/woocommerce.php');
	}

	public function is_wp_super_cache_active() {
		// TODO find out if there is a pro version with different folder and file name

		return is_plugin_active('wp-super-cache/wp-cache.php');
	}

	public function is_wp_fastest_cache_active() {
		// The pro version requires the free version to be active

		return is_plugin_active('wp-fastest-cache/wpFastestCache.php');
	}

	public function is_cloudflare_active() {
		return is_plugin_active('cloudflare/cloudflare.php');
	}

	public function is_wpml_woocommerce_multi_currency_active() {
		global $woocommerce_wpml;

		if (
			is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php') &&
			is_object($woocommerce_wpml->multi_currency)
		) {
			return true;
		} else {
			return false;
		}
	}

	public function is_woo_discount_rules_active() {
		if (
			is_plugin_active('woo-discount-rules/woo-discount-rules.php') ||
			is_plugin_active('woo-discount-rules-pro/woo-discount-rules-pro.php')
		) {
			return true;
		} else {
			return false;
		}
	}

	public function is_wp_optimize_active() {
		return is_plugin_active('wp-optimize/wp-optimize.php');
	}

	public function is_woocommerce_brands_active() {
		return is_plugin_active('woocommerce-brands/woocommerce-brands.php');
	}

	public function is_yith_wc_brands_active() {
		return is_plugin_active('yith-woocommerce-brands-add-on-premium/init.php');
	}

	public function is_optimocha_active() {
		// TODO find out if there is a pro version with different folder and file name
		return is_plugin_active('speed-booster-pack/speed-booster-pack.php');
	}

	public function is_async_javascript_active() {
		// TODO find out if there is a pro version with different folder and file name
		return is_plugin_active('async-javascript/async-javascript.php');
	}

	public function is_flying_press_active() {
		// TODO find out if there is a pro version with different folder and file name
		return is_plugin_active('flying-press/flying-press.php');
	}

	/*
	 * Check to find out what hosting provider is being used
	 * */

	public function is_hosting_flywheel() {
		return defined('FLYWHEEL_PLUGIN_DIR');
	}

	public function is_hosting_cloudways() {

		$_server = filter_input_array(INPUT_SERVER, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		return array_key_exists('cw_allowed_ip', $_server) || preg_match('~/home/.*?cloudways.*~', __FILE__);
	}

	public function is_hosting_wp_engine() {
		return !!getenv('IS_WPE');
	}

	public function is_hosting_godaddy_wpaas() {
		return class_exists('\WPaaS\Plugin');
	}

	public function is_hosting_siteground() {
		$configFilePath = $this->get_wpconfig_path();
		if (!$configFilePath) {
			return false;
		}
		return strpos(file_get_contents($configFilePath), 'Added by SiteGround WordPress management system') !== false;
	}

	public function is_hosting_gridpane() {
		$configFilePath = $this->get_wpconfig_path();
		if (!$configFilePath) {
			return false;
		}
		return strpos(file_get_contents($configFilePath), 'GridPane Cache Settings') !== false;
	}

	public function is_hosting_kinsta() {
		return defined('KINSTAMU_VERSION');
	}

	public function is_hosting_closte() {
		return defined('CLOSTE_APP_ID');
	}

	public function is_hosting_pagely() {
		return class_exists('\PagelyCachePurge');
	}

	public function get_hosting_provider() {
		if ($this->is_hosting_flywheel()) {
			return 'Flywheel';
		} elseif ($this->is_hosting_cloudways()) {
			return 'Cloudways';
		} elseif ($this->is_hosting_wp_engine()) {
			return 'WP Engine';
		} elseif ($this->is_hosting_siteground()) {
			return 'SiteGround';
		} elseif ($this->is_hosting_godaddy_wpaas()) {
			return 'GoDaddy WPaas';
		} elseif ($this->is_hosting_gridpane()) {
			return 'GridPane';
		} elseif ($this->is_hosting_kinsta()) {
			return 'Kinsta';
		} elseif ($this->is_hosting_closte()) {
			return 'Closte';
		} elseif ($this->is_hosting_pagely()) {
			return 'Pagely';
		} else {
			return 'unknown';
		}
	}

// https://github.com/wp-cli/wp-cli/blob/c3bd5bd76abf024f9d492579539646e0d263a05a/php/utils.php#L257
	public function get_wpconfig_path() {
		static $path;

		if (null === $path) {
			$path = false;

			if (getenv('WP_CONFIG_PATH') && file_exists(getenv('WP_CONFIG_PATH'))) {
				$path = getenv('WP_CONFIG_PATH');
			} elseif (file_exists(ABSPATH . 'wp-config.php')) {
				$path = ABSPATH . 'wp-config.php';
			} elseif (file_exists(dirname(ABSPATH) . '/wp-config.php') && !file_exists(dirname(ABSPATH) . '/wp-settings.php')) {
				$path = dirname(ABSPATH) . '/wp-config.php';
			}

			if ($path) {
				$path = realpath($path);
			}
		}

		return $path;
	}

	public function disable_yoast_seo_facebook_social( $option ) {
		$option['opengraph'] = false;
		return $option;
	}

	public function disable_litespeed_js_inline_after_dom( $option ) {
		return 0;
	}

//	public function disable_wp_rocket_js_optimizations( $option ) {
//		$option['minify_concatenate_js'] = 0;
////        $option['defer_all_js']          = 0;
////        $option['delay_js']              = 0;
//		return $option;
//	}


	public function wp_optimize_minify_default_exclusions( $default_exclusions ) {
		// $default_exclusions[] = 'something/else.js';
		// $default_exclusions[] = 'something/else.css';
		return array_unique(array_merge($default_exclusions, $this->get_wpm_script_identifiers()));
	}

// https://github.com/futtta/autoptimize/blob/37b13d4e19269bb2f50df123257de51afa37244f/classes/autoptimizeScripts.php#L387
	public function autoptimize_filter_js_consider_minified() {
		$exclude_js[] = 'wpm.min.js';
		$exclude_js[] = 'wpm.min.js';

		$exclude_js[] = 'wpm-public.p1.min.js';
		$exclude_js[] = 'wpm-public__premium_only.p1.min.js';

		$exclude_js[] = 'wpm-public.p2.min.js';
		$exclude_js[] = 'wpm-public__premium_only.p2.min.js';

//        $exclude_js[] = 'jquery.js';
//        $exclude_js[] = 'jquery.min.js';
		return $exclude_js;
	}

// https://github.com/futtta/autoptimize/blob/37b13d4e19269bb2f50df123257de51afa37244f/classes/autoptimizeScripts.php#L285
	public function autoptimize_filter_js_dontmove( $dontmove ) {
		$dontmove[] = 'wpm.js';
		$dontmove[] = 'wpm.min.js';

		$dontmove[] = 'wpm-public.p1.min.js';
		$dontmove[] = 'wpm-public__premium_only.p1.min.js';

		$dontmove[] = 'wpm-public.p2.min.js';
		$dontmove[] = 'wpm-public__premium_only.p2.min.js';

		$dontmove[] = 'jquery.js';
		$dontmove[] = 'jquery.min.js';
		return $dontmove;
	}

	public function litespeed_optm_cssjs( $excludes ) {
		return $excludes;
	}

	public function litespeed_optimize_js_excludes( $excludes ) {
		if (is_array($excludes)) {
			$excludes = array_unique(array_merge($excludes, $this->get_wpm_script_identifiers()));
		}

		return $excludes;
	}

	public function litespeed_cache_js_defer_exc( $excludes ) {
		if (is_array($excludes)) {
			$excludes = array_unique(array_merge($excludes, $this->get_wpm_script_identifiers()));
		}
		return $excludes;
	}

	public function sg_optimizer_js_exclude_combine_inline_content( $exclude_list ) {
		if (is_array($exclude_list)) {
			$exclude_list = array_unique(array_merge($exclude_list, $this->get_wpm_script_identifiers()));
		}

//        foreach ($this->get_wpm_script_identifiers() as $exclusion) {
//            $exclude_list[] = $exclusion;
//        }

		return $exclude_list;
	}

	public function sg_optimizer_js_minify_exclude( $exclude_list ) {

		$exclude_list[] = 'wpm-front-end-scripts';
		$exclude_list[] = 'wpm-front-end-scripts-premium-only';
		$exclude_list[] = 'wpm';
		$exclude_list[] = 'wpm-admin';
		$exclude_list[] = 'wpm-premium-only';
		$exclude_list[] = 'wpm-facebook';
		$exclude_list[] = 'wpm-script-blocker-warning';
		$exclude_list[] = 'wpm-admin-helpers';
		$exclude_list[] = 'wpm-admin-tabs';
		$exclude_list[] = 'wpm-selectWoo';
		$exclude_list[] = 'wpm-google-ads';
		$exclude_list[] = 'wpm-ga-ua-eec';
		$exclude_list[] = 'wpm-ga4-eec';
		$exclude_list[] = 'polyfill-io';

		$exclude_list[] = 'jquery';
		$exclude_list[] = 'jquery-core';
		$exclude_list[] = 'jquery-migrate';

		return $exclude_list;
	}

	public function sgo_javascript_combine_exclude_move_after( $exclude_list ) {

		if (is_array($exclude_list)) {
			$exclude_list = array_unique(array_merge($exclude_list, $this->get_wpm_script_identifiers()));
		}

		return $exclude_list;
	}

	public function add_wp_rocket_exclusions( $exclusions ) {
		if (is_array($exclusions)) {
			$exclusions = array_unique(array_merge($exclusions, $this->get_wpm_script_identifiers()));
		}

		return $exclusions;
	}


// works for WP Rocket >= 3.9
	public function exclude_inline_scripts_from_wp_rocket_using_options() {
		$options = get_option('wp_rocket_settings');

		// if no options array could be retrieved.
		if (!is_array($options)) {
			return;
		}

		$update_options = false;

		$js_to_exclude = $this->get_wpm_script_identifiers();

		foreach ($js_to_exclude as $string) {

			// add exclusions for inline js
//            if (array_key_exists('exclude_inline_js', $options) && is_array($options['exclude_inline_js']) && !in_array($string, $options['exclude_inline_js'])) {
//
//                array_push($options['exclude_inline_js'], $string);
//                $update_options = true;
//            }

			// add exclusions for js
//            if (array_key_exists('exclude_js', $options) && is_array($options['exclude_js']) && !in_array($string, $options['exclude_js'])) {
//
//                array_push($options['exclude_js'], $string);
//                $update_options = true;
//            }

			// remove scripts from delay_js_scripts
			if (array_key_exists('delay_js_scripts', $options) && is_array($options['delay_js_scripts']) && in_array($string, $options['delay_js_scripts'])) {

				unset($options['delay_js_scripts'][array_search($string, $options['delay_js_scripts'])]);
				$update_options = true;
			}

			// exclude_defer_js
//            if (array_key_exists('exclude_defer_js', $options) && is_array($options['exclude_defer_js']) && !in_array($string, $options['exclude_defer_js'])) {
//
//                array_push($options['exclude_defer_js'], $string);
//                $update_options = true;
//            }

			// exclude_delay_js
//            if (array_key_exists('delay_js_exclusions', $options) && is_array($options['delay_js_exclusions']) && !in_array($string, $options['delay_js_exclusions'])) {
//
//                array_push($options['delay_js_exclusions'], $string);
//                $update_options = true;
//            }
		}

		if (true === $update_options) {
			update_option('wp_rocket_settings', $options);
		}
	}

	public function disable_third_party_js_optimization() {

		/**
		 * SiteGround Optimizer
		 */

		if ($this->is_sg_optimizer_active()) {

			/**
			 * The function wpmFunctionExists needs to be excluded from combination from SGO.
			 * Otherwise, it won't work on pages which include WPM shortcodes.
			 * */

			add_filter('sgo_javascript_combine_excluded_inline_content', function ( $excluded_scripts ) {
				$excluded_scripts[] = 'wpmFunctionExists';
				return $excluded_scripts;
			});

			/**
			 * SGO's defer feature doesn't queue jQuery correctly on some pages,
			 * leading to errors "jQuery not defined" errors on several pages
			 * and thus breaking tracking in those cases.
			 *
			 * Therefore, we need to exclude jquery-core from deferring.
			 * */

			add_filter('sgo_js_async_exclude', function ( $excludes ) {
				$excludes[] = 'jquery-core';
				return $excludes;
			});
		}

		/**
		 * Litespeed
		 */

		if ($this->is_litespeed_active()) {
			add_filter('litespeed_optimize_js_excludes', function ( $excludes ) {
				if (is_array($excludes)) {
					$excludes[] = 'wpmFunctionExists';
				}

				return $excludes;
			});
		}
	}

	public function enable_compatibility_mode() {

		$this->compatibility_mode_yoast_seo();

		$this->compatibility_mode_prevent_third_party_js_optimization();
	}

	protected function compatibility_mode_prevent_third_party_js_optimization() {

		if ($this->is_wp_rocket_active()) {
			$this->disable_wp_rocket_js_optimization();
		}

		if ($this->is_flying_press_active()) {
			$this->disable_flying_press_js_optimization();
		}

		if ($this->is_optimocha_active()) {
			$this->disable_optimocha_js_optimization();
		}

		if ($this->is_wp_optimize_active()) {
			$this->disable_wp_optimize_js_optimization();
		}

		if ($this->is_async_javascript_active()) {
			$this->disable_async_javascript_js_optimization();
		}

		if ($this->is_sg_optimizer_active()) {
			$this->disable_sg_optimizer_js_optimization();
		}

		if ($this->is_litespeed_active()) {
			$this->disable_litespeed_js_optimization();
		}

		if ($this->is_autoptimize_active()) {
			$this->disable_autoptimze_js_optimization();
		}
	}

	protected function disable_sg_optimizer_js_optimization() {

		add_filter('sgo_javascript_combine_excluded_inline_content', [$this, 'sg_optimizer_js_exclude_combine_inline_content']);
		add_filter('sgo_javascript_combine_exclude', [$this, 'sgo_javascript_combine_exclude_move_after']);
		add_filter('sgo_javascript_combine_exclude_move_after', [$this, 'sgo_javascript_combine_exclude_move_after']);
		add_filter('sgo_js_minify_exclude', [$this, 'sg_optimizer_js_minify_exclude']);
		add_filter('sgo_js_async_exclude', [$this, 'sgo_javascript_combine_exclude_move_after']);
	}

	protected function disable_litespeed_js_optimization() {
		add_filter('litespeed_optimize_js_excludes', [$this, 'litespeed_optimize_js_excludes']);
		add_filter('litespeed_optm_js_defer_exc', [$this, 'litespeed_cache_js_defer_exc']);
		add_filter('litespeed_optm_cssjs', [$this, 'litespeed_optm_cssjs']);
		add_filter('option_litespeed.conf.optm-js_inline_defer', [$this, 'disable_litespeed_js_inline_after_dom']);
	}

	protected function disable_autoptimze_js_optimization() {
		add_filter('autoptimize_filter_js_consider_minified', [$this, 'autoptimize_filter_js_consider_minified']);
		add_filter('autoptimize_filter_js_dontmove', [$this, 'autoptimize_filter_js_dontmove']);
	}

	protected function disable_wp_optimize_js_optimization() {
		// add_filter('wpo_minify_inline_js', '__return_false');
		add_filter('wp-optimize-minify-default-exclusions', [$this, 'wp_optimize_minify_default_exclusions']);
	}

	protected function disable_async_javascript_js_optimization() {
		add_filter('option_aj_plugin_exclusions', function ( $options ) {

			if (!is_array($options)) {
				$options = [];
			}

			return array_unique(array_merge($options, [
				'woocommerce-google-adwords-conversion-tracking-tag',
				'woopt-pixel-manager-pro',
				'woocommerce-pixel-manager',
				'woocommerce-pixel-manager-pro',
			]));
		});
	}

	protected function disable_optimocha_js_optimization() {
		add_filter('option_sbp_options', function ( $options ) {

			if (isset($options['js_exclude'])) {
				$options['js_exclude'] = $options['js_exclude'] . PHP_EOL . implode(PHP_EOL, $this->get_wpm_script_identifiers());
				$js_include            = explode(PHP_EOL, $options['js_include']);
				$js_include            = array_filter($js_include, function ( $string ) {
					foreach ($this->get_wpm_script_identifiers() as $value) {
						if (strpos($string, $value) !== false) {
							return false;
						}
					}

					return true;
				});
				$options['js_include'] = implode(PHP_EOL, $js_include);
			}

			return $options;
		});
	}

	protected function disable_flying_press_js_optimization() {
		add_filter('pre_update_option_FLYING_PRESS_CONFIG', function ( $options ) {

			if (isset($options['js_defer_excludes'])) {
				$options['js_defer_excludes'] = array_unique(array_merge($options['js_defer_excludes'], $this->get_wpm_script_identifiers()));
			}

			return $options;
		});

		add_filter('option_FLYING_PRESS_CONFIG', function ( $options ) {

			if (isset($options['js_defer_excludes'])) {
				$options['js_defer_excludes'] = array_unique(array_merge($options['js_defer_excludes'], $this->get_wpm_script_identifiers()));
			}
			return $options;
		});

		// 		Make sure to never delay JS until interaction
//		if ($this->is_flying_press_active()) {
//			add_filter('option_FLYING_PRESS_CONFIG', function ( $options ) {
//				if (isset($options['js_interaction'])) {
//					$options['js_interaction'] = false;
//				}
//				return $options;
//			});
//		}
	}

	protected function disable_wp_rocket_js_optimization() {
		// for testing you need to clear the WP Rocket cache, only then the filters run
		$this->exclude_inline_scripts_from_wp_rocket_using_options();
		add_filter('rocket_delay_js_exclusions', [$this, 'add_wp_rocket_exclusions']);
		add_filter('rocket_defer_inline_exclusions', [$this, 'add_wp_rocket_exclusions']);
		add_filter('rocket_exclude_defer_js', [$this, 'add_wp_rocket_exclusions']);
		add_filter('rocket_exclude_js', [$this, 'add_wp_rocket_exclusions']);
		add_filter('rocket_minify_excluded_external_js', [$this, 'add_wp_rocket_exclusions']);
		add_filter('rocket_excluded_inline_js_content', [$this, 'add_wp_rocket_exclusions']);
	}

	public function compatibility_mode_yoast_seo() {

		if ($this->is_yoast_seo_active() && isset($this->options['facebook']['microdata']) && $this->options['facebook']['microdata']) {
			add_filter('option_wpseo_social', [$this, 'disable_yoast_seo_facebook_social']);
		}
	}

	private function get_wpm_script_identifiers() {
		return [
			'optimize.js',
			'googleoptimize.com/optimize.js',
			'jquery',
			'jQuery',
			'jQuery.min.js',
			'jquery.js',
			'jquery.min.js',
			'wpm',
			'wpm-js',
			'wpmDataLayer',
			'window.wpmDataLayer',
			'wpm.js',
			'wpm.min.js',
			'wpm__premium_only.js',
			'wpm__premium_only.min.js',
			'wpm-public.p1.min.js',
			'wpm-public__premium_only.p1.min.js',
			'polyfill-io',
			'polyfill.min.js',
			//            'facebook.js',
			//            'facebook.min.js',
			//            'facebook__premium_only.js',
			//            'facebook__premium_only.min.js',
			//            'google-ads.js',
			//            'google-ads.min.js',
			//            'google-ga-4-eec__premium_only.js',
			//            'google-ga-4-eec__premium_only.min.js',
			//            'google-ga-us-eec__premium_only.js',
			//            'google-ga-us-eec__premium_only.min.js',
			//            'google__premium_only.js',
			//            'google__premium_only.min.js',
			'window.dataLayer',
			//            '/gtag/js',
			'gtag',
			//            '/gtag/js',
			//            'gtag(',
			'gtm.js',
			//            '/gtm-',
			//            'GTM-',
			//            'fbq(',
			'fbq',
			'fbevents.js',
			//            'twq(',
			'twq',
			//            'e.twq',
			'static.ads-twitter.com/uwt.js',
			'platform.twitter.com/widgets.js',
			'uetq',
			'ttq',
			'events.js',
			'snaptr',
			'scevent.min.js',
		];
	}

	public function is_curl_active() {
		return function_exists('curl_version');
	}

	public function does_url_redirect( $url ) {

		if (null !== $this->order_redirect) {
			return $this->order_redirect;
		} else {

			$context = stream_context_create([
												 'ssl' => [
													 'verify_peer'      => false,
													 'verify_peer_name' => false,
												 ],
											 ]);

//		$headers = get_headers($url, 1, $context);

			$headers = $this->get_headers_with_stream_context($url, $context, 1);

			if (!empty($headers['Location'])) {
				$this->order_redirect = true;
				return true;
			} else {
				$this->order_redirect = false;
				return false;
			}
		}
	}

	// https://stackoverflow.com/questions/8429342/php-get-headers-set-temporary-stream-context
	protected function get_headers_with_stream_context( $url, $context, $assoc = 0 ) {

		$fp = @fopen($url, 'r', null, $context);

		if (!is_bool($fp)) {

			$metaData = stream_get_meta_data($fp);
			fclose($fp);

			$headerLines = $metaData['wrapper_data'];

			if (!$assoc) {
				return $headerLines;
			}

			$headers = [];
			foreach ($headerLines as $line) {
				if (strpos($line, 'HTTP') === 0) {
					$headers[0] = $line;
					continue;
				}

				list($key, $value) = explode(': ', $line);
				$headers[$key] = $value;
			}

			return $headers;
		} else {
			return [];
		}
	}

	public function get_redirect_url( $url ) {
		$headers = get_headers($url, 1);

		if (!empty($headers['Location'])) {
			if (is_array($headers['Location'])) {
				return end($headers['Location']);
			} else {
				return $headers['Location'];
			}
		} else {
			return '';
		}
	}

// https://stackoverflow.com/a/46690009/4688612
//	public function get_last_order_id() {
//		global $wpdb;
//		$statuses = array_keys(wc_get_order_statuses());
//		$statuses = implode("','", $statuses);
//
//		// Getting last Order ID (max value)
//		$results = $wpdb->get_col("
//            SELECT MAX(ID) FROM {$wpdb->prefix}posts
//            WHERE post_type LIKE 'shop_order'
//            AND post_status IN ('$statuses')
//        ");
//		error_log(reset($results));
//		return reset($results);
//	}

// https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query#usage
	public function get_last_order_id() {

		$orders = wc_get_orders([
									'limit'     => 1,
									'orderby'   => 'date',
									'order'     => 'DESC',
									'return'    => 'ids',
									'post_type' => 'shop_order'
								]);

//		error_log(reset($orders));

		return reset($orders);
	}

	public function get_last_order_url() {
		$last_order_id = $this->get_last_order_id();
		$last_order    = wc_get_order($last_order_id);

		if ($last_order) {
			return $last_order->get_checkout_order_received_url();
		} else {
			return '';
		}
	}

	public function does_one_order_exist() {
		if ($this->get_last_order_id()) {
			return true;
		} else {
			return false;
		}
	}
}
