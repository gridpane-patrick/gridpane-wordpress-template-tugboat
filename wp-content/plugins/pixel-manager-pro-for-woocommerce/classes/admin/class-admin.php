<?php

// TODO move script for copying debug info into a proper .js enqueued file, or switch tabs to JavaScript switching and always save all settings at the same time

namespace WCPM\Classes\Admin;

use WCPM\Classes\Pixels\Google\Google;
use WCPM\Classes\Pixels\Trait_Shop;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Admin {
	use Trait_Shop;

	public    $ip;
	protected $text_domain;
	protected $options;
	protected $plugin_hook;
	protected $documentation;
	private   $consent_mode_regions;
	private   $validations;
	private   $google;

	public function __construct( $options ) {

		$this->options = $options;

		$this->plugin_hook   = 'woocommerce_page_wpm';
		$this->documentation = new Documentation();
		$this->google        = new Google($this->options);

		add_action('admin_enqueue_scripts', [$this, 'wpm_admin_scripts']);

		// add the admin options page
		add_action('admin_menu', [$this, 'wpm_plugin_admin_add_page'], 99);

		// install a settings page in the admin console
		add_action('admin_init', [$this, 'wpm_plugin_admin_init']);

		// Load textdomain
		add_action('init', [$this, 'load_plugin_textdomain']);

		// end __construct
		$this->consent_mode_regions = new Consent_Mode_Regions();
		$this->validations          = new Validations();

		add_action('wp_ajax_wpm_save_imported_settings', [$this, 'wpm_save_imported_settings']);
	}

	public function wpm_save_imported_settings() {

		$_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$options = $_post['settings'];

		// Validate imported options
		if ($this->validations->validate_imported_options($options)) {
			update_option(WPM_DB_OPTIONS_NAME, $options);
			wp_send_json_success(['message' => 'Options saved']);
		} else {
			wp_send_json_error(['message' => 'Invalid options']);
		}
	}

	protected function if_is_wpm_admin_page() {

		$_get = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if (!empty($_get['page']) && 'wpm' === $_get['page']) {
			return true;
		} else {
			return false;
		}
	}

	public function freemius_enqueue_deactivation_button_js() {
		wp_enqueue_script(
			'freemius-enqueue-deactivation-button',
			WPM_PLUGIN_DIR_PATH . 'js/admin/wpm-admin-freemius.p1.min.js',
			['jquery'],
			WPM_CURRENT_VERSION,
			true
		);
	}

	public function wpm_admin_scripts( $hook_suffix ) {
		// only output the admin scripts on the plugin pages
//        if ($this->plugin_hook != $hook_suffix) {
//            return;
//        }

		if (!strpos($hook_suffix, 'page_wpm')) {
			return;
		}

		wp_enqueue_script(
			'wpm-admin',
			WPM_PLUGIN_DIR_PATH . 'js/admin/wpm-admin.p1.min.js',
			['jquery'],
			WPM_CURRENT_VERSION,
			false
		);

//        wp_enqueue_script('wpm-script-blocker-warning', WPM_PLUGIN_DIR_PATH . 'js/admin/script-blocker-warning.js', ['jquery'], WPM_CURRENT_VERSION, false);
//        wp_enqueue_script('wpm-admin-helpers', WPM_PLUGIN_DIR_PATH . 'js/admin/helpers.js', ['jquery'], WPM_CURRENT_VERSION, false);
//        wp_enqueue_script('wpm-admin-tabs', WPM_PLUGIN_DIR_PATH . 'js/admin/tabs.js', ['jquery'], WPM_CURRENT_VERSION, false);

		wp_enqueue_script(
			'wpm-selectWoo',
			WPM_PLUGIN_DIR_PATH . 'js/admin/selectWoo.full.min.js',
			['jquery'],
			WPM_CURRENT_VERSION,
			false
		);

		wp_enqueue_style(
			'wpm-admin',
			WPM_PLUGIN_DIR_PATH . 'css/admin.css',
			[],
			WPM_CURRENT_VERSION
		);

		wp_enqueue_style(
			'wpm-selectWoo',
			WPM_PLUGIN_DIR_PATH . 'css/selectWoo.min.css',
			[],
			WPM_CURRENT_VERSION
		);
	}

	// Load text domain function
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'woocommerce-google-adwords-conversion-tracking-tag',
			false,
			dirname(plugin_basename(__FILE__)) . '/languages/'
		);
	}

	// add the admin options page
	public function wpm_plugin_admin_add_page() {
		//add_options_page('WPM Plugin Page', 'WPM Plugin Menu', 'manage_options', 'wpm', array($this, 'wpm_plugin_options_page'));
		add_submenu_page(
			$this->get_submenu_parent_slug(),
			esc_html__('Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag'),
			esc_html__('Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag'),
			'manage_options',
			'wpm',
			[$this, 'wpm_plugin_options_page']
		);
	}

	protected function get_submenu_parent_slug() {

		if (is_plugin_active('woocommerce/woocommerce.php')) {
			return 'woocommerce';
		} else {
			return 'options-general.php';
		}
	}

	// add the admin settings and such
	public function wpm_plugin_admin_init() {

		register_setting(
			'wpm_plugin_options_group',
			'wgact_plugin_options',
			[$this, 'wpm_options_validate']
		);

		// don't load the UX if we are not on the plugin UX page
		if (!$this->if_is_wpm_admin_page()) {
			return;
		}

		$this->add_section_main();
		$this->add_section_advanced();
		$this->add_section_beta();
		$this->add_section_support();
		$this->add_section_author();
	}

	public function add_section_main() {
		$section_ids = [
			'title'         => esc_html__('Main', 'woocommerce-google-adwords-conversion-tracking-tag'),
			'slug'          => 'main',
			'settings_name' => 'wpm_plugin_main_section',
		];

		$this->output_section_data_field($section_ids);

		add_settings_section(
			$section_ids['settings_name'],
			esc_html__($section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag'),
			[$this, 'wpm_plugin_section_main_description'],
			'wpm_plugin_options_page'
		);

		$this->add_section_main_subsection_google_ads($section_ids);
		$this->add_section_main_subsection_facebook($section_ids);
		$this->add_section_main_subsection_more_pixels($section_ids);
	}

	public function add_section_main_subsection_google_ads( $section_ids ) {
		$sub_section_ids = [
			'title' => esc_html__(
				'Google',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'slug'  => 'google'
		];

		add_settings_field(
			'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function () use ( $section_ids, $sub_section_ids ) {
				$this->wpm_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
			},
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add the field for the conversion id
		add_settings_field(
			'wpm_plugin_conversion_id',
			esc_html__(
				'Google Ads Conversion ID',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_option_html_google_ads_conversion_id'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add the field for the conversion label
		add_settings_field(
			'wpm_plugin_conversion_label',
			esc_html__(
				'Google Ads Purchase Conversion Label',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_option_html_google_ads_conversion_label'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		add_settings_field(
			'wpm_plugin_analytics_ua_property_id',
			esc_html__(
				'Google Analytics UA',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_option_html_google_analytics_universal_property'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		add_settings_field(
			'wpm_plugin_analytics_4_measurement_id',
			esc_html__(
				'Google Analytics 4',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_option_html_google_analytics_4_id'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		add_settings_field(
			'wpm_plugin_google_optimize_container_id',
			esc_html__(
				'Google Optimize',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_option_html_google_optimize_container_id'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);
	}

	public function add_section_main_subsection_facebook( $section_ids ) {
		$sub_section_ids = [
			'title' => esc_html__(
				'Meta (Facebook)',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'slug'  => 'facebook'
		];

		add_settings_field(
			'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function () use ( $section_ids, $sub_section_ids ) {
				$this->wpm_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
			},
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add the field for the conversion label
		add_settings_field(
			'wpm_plugin_facebook_pixel_id',
			esc_html__(
				'Meta (Facebook) pixel ID',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_option_html_facebook_pixel_id'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);
	}

	public function add_section_main_subsection_more_pixels( $section_ids ) {
		$sub_section_ids = [
			'title' => esc_html__(
				'more pixels',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'slug'  => 'more-pixels'
		];

		add_settings_field(
			'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function () use ( $section_ids, $sub_section_ids ) {
				$this->wpm_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
			},
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		if (true || $this->options['general']['pro_version_demo']) {

			// add the field for the Bing Ads UET tag ID
			add_settings_field(
				'wpm_plugin_bing_uet_tag_id',
				esc_html__(
					'Microsoft Advertising UET tag ID',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_option_html_bing_uet_tag_id'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);

			// add the field for the Twitter pixel
			add_settings_field(
				'wpm_plugin_twitter_pixel_id',
				esc_html__(
					'Twitter pixel ID',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_option_html_twitter_pixel_id'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);

			// add the field for the Pinterest pixel
			add_settings_field(
				'wpm_plugin_pinterest_pixel_id',
				esc_html__(
					'Pinterest pixel ID',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_option_html_pinterest_pixel_id'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);

			// add the field for the Snapchat pixel
			add_settings_field(
				'wpm_plugin_snapchat_pixel_id',
				esc_html__(
					'Snapchat pixel ID',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_option_html_snapchat_pixel_id'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);

			// add the field for the TikTok pixel
			add_settings_field(
				'wpm_plugin_tiktok_pixel_id',
				esc_html__(
					'TikTok pixel ID',
					'woocommerce-google-adwords-conversion-tracking-tag'
				) . $this->html_beta(),
				[$this, 'wpm_option_html_tiktok_pixel_id'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}

		// add the field for the Hotjar pixel
		add_settings_field(
			'wpm_plugin_hotjar_site_id',
			esc_html__(
				'Hotjar site ID',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_option_html_hotjar_site_id'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);
	}

	public function add_section_advanced() {
		$section_ids = [
			'title'         => esc_html__(
				'Advanced',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'slug'          => 'advanced',
			'settings_name' => 'wpm_plugin_advanced_section',
		];

		add_settings_section(
			$section_ids['settings_name'],
			esc_html__(
				$section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_plugin_section_advanced_description'],
			'wpm_plugin_options_page'
		);

		$this->output_section_data_field($section_ids);

		$this->add_section_advanced_subsection_shop($section_ids);
		$this->add_section_advanced_subsection_google($section_ids);

		if (true || $this->pro_version_demo_active()) {
			$this->add_section_advanced_subsection_facebook($section_ids);
			$this->add_section_advanced_subsection_cookie_consent_mgmt($section_ids);
		}
	}

	public function add_section_advanced_subsection_shop( $section_ids ) {
		$sub_section_ids = [
			'title' => 'Shop',
			'slug'  => 'shop'
		];

		add_settings_field(
			'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function () use ( $section_ids, $sub_section_ids ) {
				$this->wpm_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
			},
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add fields for the order total logic
		add_settings_field(
			'wpm_plugin_order_total_logic',
			esc_html__(
				'Order Total Logic',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_option_html_shop_order_total_logic'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add checkbox for order duplication prevention
		add_settings_field(
			'wpm_setting_order_duplication_prevention',
			esc_html__(
				'Order Duplication Prevention',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_setting_html_order_duplication_prevention'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add checkbox for maximum compatibility mode
		add_settings_field(
			'wpm_setting_maximum_compatibility_mode',
			esc_html__(
				'Maximum Compatibility Mode',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_setting_html_maximum_compatibility_mode'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		if (true || $this->options['general']['pro_version_demo']) {

			// add checkbox for disabling tracking for user roles
			add_settings_field(
				'wpm_setting_disable_tracking_for_user_roles',
				esc_html__(
					'Disable Tracking for User Roles',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_disable_tracking_for_user_roles'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}
	}

	public function add_section_advanced_subsection_google( $section_ids ) {
		$sub_section_ids = [
			'title' => 'Google',
			'slug'  => 'google'
		];

		add_settings_field(
			'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function () use ( $section_ids, $sub_section_ids ) {
				$this->wpm_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
			},
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);


		// add the field for the aw_merchant_id
		add_settings_field(
			'wpm_plugin_aw_merchant_id',
			esc_html__(
				'Conversion Cart Data',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_plugin_setting_aw_merchant_id'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		if (true || $this->pro_version_demo_active()) {
			// add fields for the Google enhanced e-commerce
			add_settings_field(
				'wpm_setting_google_analytics_eec',
				esc_html__(
					'Enhanced E-Commerce',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_google_analytics_eec'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);

			// add fields for the Google GA 4 API secret
			add_settings_field(
				'wpm_setting_google_analytics_4_api_secret',
				esc_html__(
					'GA 4 API secret',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_google_analytics_4_api_secret'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}

		// add fields for the Google Analytics link attribution
		add_settings_field(
			'wpm_setting_google_analytics_link_attribution',
			esc_html__(
				'Enhanced Link Attribution',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_setting_html_google_analytics_link_attribution'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		if (true || $this->pro_version_demo_active()) {
			// add user_id for the Google
			add_settings_field(
				'wpm_setting_google_user_id',
				esc_html__(
					'Google User ID',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_google_user_id'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);

			// add Google Ads Enhanced Conversions
			add_settings_field(
				'wpm_setting_google_ads_enhanced_conversions',
				esc_html__(
					'Google Ads Enhanced Conversions',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_google_ads_enhanced_conversions'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}

		if (true || $this->pro_version_demo_active()) {
			// add fields for the Google Ads phone conversion number
			add_settings_field(
				'wpm_plugin_google_ads_phone_conversion_number',
				esc_html__(
					'Google Ads Phone Conversion Number',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_google_ads_phone_conversion_number'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);

			// add fields for the Google Ads phone conversion label
			add_settings_field(
				'wpm_plugin_google_ads_phone_conversion_label',
				esc_html__(
					'Google Ads Phone Conversion Label',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_google_ads_phone_conversion_label'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}
	}

	public function add_section_advanced_subsection_cookie_consent_mgmt( $section_ids ) {
		$sub_section_ids = [
			'title' => esc_html__(
				'Cookie Consent Management',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'slug'  => 'cookie-consent-mgmt'
		];

		add_settings_field(
			'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function () use ( $section_ids, $sub_section_ids ) {
				$this->wpm_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
			},
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add fields for the Google Consent beta
		add_settings_field(
			'wpm_setting_google_consent_mode_active',
			esc_html__(
				'Google Consent Mode',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_setting_html_google_consent_mode_active'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add fields for the Google consent regions
		add_settings_field(
			'wpm_setting_google_consent_regions',
			esc_html__(
				'Google Consent Regions',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_setting_html_google_consent_regions'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add fields for explicit cookie consent mode
		add_settings_field(
			'wpm_setting_explicit_consent_mode',
			esc_html__(
				'Explicit Consent Mode',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_setting_html_explicit_consent_mode'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		if (( new Environment_Check($this->options) )->is_borlabs_cookie_active()) {
			// add fields for the Borlabs Cookie support
			add_settings_field(
				'wpm_setting_borlabs_support',
				esc_html__(
					'Borlabs Cookie support',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_borlabs_support'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}

		if (( new Environment_Check($this->options) )->is_cookiebot_active()) {
			// add fields for the Cookiebot support
			add_settings_field(
				'wpm_setting_cookiebot_support',
				esc_html__(
					'Cookiebot support',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_cookiebot_support'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}

		if (( new Environment_Check($this->options) )->is_complianz_active()) {
			// add fields for the Complianz GDPR support
			add_settings_field(
				'wpm_setting_complianz_support',
				esc_html__(
					'Complianz GDPR support',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_complianz_support'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}

		if (( new Environment_Check($this->options) )->is_cookie_notice_active()) {
			// add fields for the Cookie Notice by hu-manity.co support
			add_settings_field(
				'wpm_setting_cookie_notice_support',
				esc_html__(
					'Cookie Notice support',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_cookie_notice_support'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}

		if (( new Environment_Check($this->options) )->is_cookie_script_active()) {
			// add fields for the Cookie Script support
			add_settings_field(
				'wpm_setting_cookie_script_support',
				esc_html__(
					'Cookie Script support',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_cookie_script_support'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}

		if (( new Environment_Check($this->options) )->is_moove_gdpr_active()) {
			// add fields for the GDPR Cookie Compliance support
			add_settings_field(
				'wpm_setting_moove_gdpr_support',
				esc_html__(
					'GDPR Cookie Compliance support',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_moove_gdpr_support'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}

		if (( new Environment_Check($this->options) )->is_cookie_law_info_active()) {
			// add fields for the GDPR Cookie Consent support
			add_settings_field(
				'wpm_setting_cookie_law_info_support',
				esc_html__(
					'GDPR Cookie Consent support',
					'woocommerce-google-adwords-conversion-tracking-tag'
				),
				[$this, 'wpm_setting_html_cookie_law_info_support'],
				'wpm_plugin_options_page',
				$section_ids['settings_name']
			);
		}
	}

	public function add_section_advanced_subsection_facebook( $section_ids ) {
		$sub_section_ids = [
			'title' => 'Meta (Facebook)',
			'slug'  => 'facebook'
		];

		add_settings_field(
			'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
			esc_html__(
				$sub_section_ids['title'],
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			function () use ( $section_ids, $sub_section_ids ) {
				$this->wpm_subsection_generic_opening_div_html($section_ids, $sub_section_ids);
			},
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add field for the Facebook CAPI token
		add_settings_field(
			'wpm_setting_facebook_capi_token',
			esc_html__(
				'Meta (Facebook) CAPI: token',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_setting_html_facebook_capi_token'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add field for the Facebook CAPI user transparency process anonymous hits
		add_settings_field(
			'wpm_setting_facebook_capi_user_transparency_process_anonymous_hits',
			esc_html__(
				'Meta (Facebook) CAPI: process anonymous hits',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_setting_facebook_capi_user_transparency_process_anonymous_hits'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add field for the Facebook CAPI user transparency send additional client identifiers
		add_settings_field(
			'wpm_setting_facebook_capi_user_transparency_send_additional_client_identifiers',
			esc_html__(
				'Meta (Facebook) CAPI: send additional visitor identifiers',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_setting_facebook_capi_user_transparency_send_additional_client_identifiers'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);

		// add fields for Facebook microdata
		add_settings_field(
			'wpm_setting_facebook_microdata_active',
			esc_html__(
				'Meta (Facebook) Microdata Tags for Catalogues',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_setting_html_facebook_microdata'],
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);
	}

	public function add_section_beta() {
		$section_ids = [
			'title'         => esc_html__(
				'Dynamic Remarketing',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			'slug'          => 'dynamic-remarketing',
			'settings_name' => 'wpm_plugin_beta_section',
		];

		$this->output_section_data_field($section_ids);

		// add new section for cart data
		add_settings_section(
			'wpm_plugin_beta_section',
			esc_html__(
				'Dynamic Remarketing',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_plugin_section_add_cart_data_description'],
			'wpm_plugin_options_page'
		);

		// add checkbox for dynamic remarketing
		add_settings_field(
			'wpm_plugin_option_gads_dynamic_remarketing',
			esc_html__(
				'Dynamic Remarketing',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_option_html_google_ads_dynamic_remarketing'],
			'wpm_plugin_options_page',
			'wpm_plugin_beta_section'
		);

		// add fields for the product identifier
		add_settings_field(
			'wpm_plugin_option_product_identifier',
			esc_html__(
				'Product Identifier',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_plugin_option_product_identifier'],
			'wpm_plugin_options_page',
			'wpm_plugin_beta_section'
		);

		// add checkbox for variations output
		add_settings_field(
			'wpm_plugin_option_variations_output',
			esc_html__(
				'Variations output',
				'woocommerce-google-adwords-conversion-tracking-tag'
			),
			[$this, 'wpm_option_html_variations_output'],
			'wpm_plugin_options_page',
			'wpm_plugin_beta_section'
		);

		if (true || $this->pro_version_demo_active()) {
			// google_business_vertical
			add_settings_field(
				'wpm_plugin_google_business_vertical',
				esc_html__(
					'Google Business Vertical',
					'woocommerce-google-adwords-conversion-tracking-tag'
				) . $this->html_pro_feature(),
				[$this, 'wpm_plugin_option_google_business_vertical'],
				'wpm_plugin_options_page',
				'wpm_plugin_beta_section'
			);
		}
	}

	public function add_section_support() {
		$section_ids = [
			'title'         => esc_html__('Support', 'woocommerce-google-adwords-conversion-tracking-tag'),
			'slug'          => 'support',
			'settings_name' => 'wpm_plugin_support_section',
		];

		$this->output_section_data_field($section_ids);

		add_settings_section(
			'wpm_plugin_support_section',
			esc_html__('Support', 'woocommerce-google-adwords-conversion-tracking-tag'),
			[$this, 'wpm_plugin_section_support_description'],
			'wpm_plugin_options_page'
		);
	}

	public function add_section_author() {
		$section_ids = [
			'title'         => esc_html__('Author', 'woocommerce-google-adwords-conversion-tracking-tag'),
			'slug'          => 'author',
			'settings_name' => 'wpm_plugin_author_section',
		];

		$this->output_section_data_field($section_ids);

		// end add_section_author
	}

	protected function output_section_data_field( array $section_ids ) {
		add_settings_field(
			'wgact_plugin_section_' . $section_ids['slug'] . '_opening_div',
			'',
			function () use ( $section_ids ) {
				$this->wpm_section_generic_opening_div_html($section_ids);
			},
			'wpm_plugin_options_page',
			$section_ids['settings_name']
		);
	}

	public function wpm_section_generic_opening_div_html( $section_ids ) {
		echo '<div class="section" data-section-title="' . esc_js($section_ids['title']) . '" data-section-slug="' . esc_js($section_ids['slug']) . '"></div>';
	}

	public function wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids ) {
		echo '<div class="subsection" data-section-slug="' . esc_js($section_ids['slug']) . '" data-subsection-title="' . esc_js($sub_section_ids['title']) . '" data-subsection-slug="' . esc_js($sub_section_ids['slug']) . '"></div>';
	}

	// display the admin options page
	public function wpm_plugin_options_page() {
		?>

		<div id="script-blocker-notice"
			 style="
			 font-weight: bold;
			 width:90%;
			 float: left;
			 margin: 5px 15px 2px;
			 padding: 1px 12px;
			 background: #fff;
			 border: 1px solid #c3c4c7;
			 border-left-width: 4px;
			 border-left-color: #d63638;
			 box-shadow: 0 1px 1px rgb(0 0 0 / 4%);">
			<p>
				<?php
				esc_html_e('It looks like you are using some sort of ad or script blocker which is blocking the script and CSS files of this plugin.
                    In order for the plugin to work properly you need to disable the script blocker.', 'woocommerce-google-adwords-conversion-tracking-tag');
				?>
			</p>
			<p>
				<a href="<?php echo esc_url($this->documentation->get_link('script_blockers')); ?>"
				   target="_blank">
					<?php esc_html_e('Learn more', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
				</a>
			</p>

			<script>
				if (typeof wpm_hide_script_blocker_warning === "function") {
					wpm_hide_script_blocker_warning()
				}
			</script>

		</div>

		<div style="width:90%; margin: 5px">

			<?php settings_errors(); ?>

			<h2 class="nav-tab-wrapper"></h2>

			<form id="wpm_settings_form" action="options.php" method="post">

				<?php

				settings_fields('wpm_plugin_options_group');
				do_settings_sections('wpm_plugin_options_page');
				submit_button();

				?>

			</form>
		</div>
		<?php
	}

	private function get_link_locale() {

		if (substr(get_user_locale(), 0, 2) === 'de') {
			return 'de';
		} else {
			return 'en';
		}
	}

	/*
	 * descriptions
	 */

	public function wpm_plugin_section_main_description() {
		// do nothing
	}

	public function wpm_plugin_section_advanced_description() {
		// do nothing
	}

	public function wpm_plugin_section_add_cart_data_description() {
//        echo '<div id="beta-description" style="margin-top:20px">';
//        esc_html_e('Find out more about this new feature: ', 'woocommerce-google-adwords-conversion-tracking-tag');
//        echo '<a href="https://support.google.com/google-ads/answer/9028254" target="_blank">https://support.google.com/google-ads/answer/9028254</a><br>';
//        echo '</div>';
	}

	public function wpm_plugin_section_support_description() {
		?>
		<div style="margin-top:20px">
			<h2><?php esc_html_e('Contacting Support', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></h2>

		</div>
		<?php $this->support_info_for_wc_market(); ?>
		<hr style="border: none;height: 1px; color: #333; background-color: #333;">
		<?php
		// placeholder
		?>
		<div>
			<h2><?php esc_html_e('Debug Information', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></h2>

			<div>
				<textarea id="debug-info-textarea" class=""
						  style="display:block; margin-bottom: 10px; width: 100%;resize: none;color:dimgrey;"
						  cols="100%" rows="30"
						  readonly><?php esc_html_e(( new Debug_Info($this->options) )->get_debug_info()); ?>
				</textarea>
				<button id="debug-info-button"
						type="button"><?php esc_html_e('copy to clipboard', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></button>
			</div>

		</div>
		<hr class="pmw-hr">

		<div>
			<h2><?php esc_html_e('Export settings', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></h2>

			<div>
				<textarea id="export-settings-json" class=""
						  style="display:block; margin-bottom: 10px; width: 100%;resize: none;color:dimgrey;"
						  cols="100%" rows="10"
						  readonly><?php echo wc_esc_json(json_encode($this->options)); ?>
				</textarea>
				<button
						id="debug-info-button"
						type="button"
						onclick="wpm.saveSettingsToDisk()"
				><?php esc_html_e('Export to disk', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></button>
			</div>
		</div>

		<hr class="pmw-hr">

		<div>
			<h2><?php esc_html_e('Import settings', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></h2>
			<div>
				<input type="file" id="json-settings-file-input"/>
				<pre id="upload-status-success" style="display: none; white-space: pre-line;">
					<span style="color: green; font-weight: bold">
						<?php esc_html_e('Settings imported successfully!', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
					</span>
					<span>
						<?php esc_html_e('Reloading...(in 5 seconds)!', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
					</span>
				</pre>

				<pre id="upload-status-error" style="display: none; white-space: pre-line;">
					<span style="color: red; font-weight: bold">
						<?php esc_html_e('There was an error importing that file! Please try again.', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
					</span>
				</pre>
			</div>
		</div>

		<hr class="pmw-hr">


		<?php
	}

	private function support_info_for_wc_market() {
		?>
		<div style="margin-bottom: 30px;">
			<ul>
				<li>
					<?php esc_html_e('Send us your support request through the WooCommerce.com dashboard: ', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
					<a href="https://woocommerce.com/my-account/create-a-ticket/" target="_blank">WooCommerce support
						dashboard</a>
				</li>
			</ul>
		</div>

		<?php
	}

	public function wpm_option_html_google_analytics_universal_property() {
		?>
		<input id='wpm_plugin_analytics_ua_property_id'
			   name='wgact_plugin_options[google][analytics][universal][property_id]'
			   size='40'
			   type='text'
			   value='<?php esc_html_e($this->options['google']['analytics']['universal']['property_id']); ?>'
		/>
		<?php
		$this->get_status_icon_new($this->options['google']['analytics']['universal']['property_id']);
		$this->get_documentation_html_by_key('google_analytics_universal_property');
		echo '<br><br>';
		esc_html_e('The Google Analytics Universal property ID looks like this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>UA-12345678-1</i>';
	}

	public function wpm_option_html_google_analytics_4_id() {
		?>
		<input id='wpm_plugin_analytics_4_measurement_id'
			   name='wgact_plugin_options[google][analytics][ga4][measurement_id]'
			   size='40'
			   type='text'
			   value='<?php esc_html_e($this->options['google']['analytics']['ga4']['measurement_id']); ?>'
		/>
		<?php
		$this->get_status_icon_new($this->options['google']['analytics']['ga4']['measurement_id']);
		$this->get_documentation_html_by_key('google_analytics_4_id');
		echo '<br><br>';
		esc_html_e('The Google Analytics 4 measurement ID looks like this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>G-R912ZZ1MHH0</i>';
	}

	public function wpm_option_html_google_ads_conversion_id() {
		?>
		<input id='wpm_plugin_conversion_id'
			   name='wgact_plugin_options[google][ads][conversion_id]'
			   size='40'
			   type='text'
			   value='<?php esc_html_e($this->options['google']['ads']['conversion_id']); ?>'
		/>
		<?php
		$this->get_status_icon_new($this->options['google']['ads']['conversion_id']);
		$this->get_documentation_html_by_key('google_ads_conversion_id');
		echo '<br><br>';
		esc_html_e('The conversion ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>123456789</i>';
	}

	public function wpm_option_html_google_ads_conversion_label() {
		?>
		<input id='wpm_plugin_conversion_label'
			   name='wgact_plugin_options[google][ads][conversion_label]'
			   size='40'
			   type='text'
			   value='<?php esc_html_e($this->options['google']['ads']['conversion_label']); ?>'
		/>
		<?php
		$this->get_status_icon_new($this->options['google']['ads']['conversion_label'], $this->options['google']['ads']['conversion_id']);
		$this->get_documentation_html_by_key('google_ads_conversion_label');
		echo '<br><br>';
		esc_html_e('The purchase conversion label looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>Xt19CO3axGAX0vg6X3gM</i>';
		if ($this->options['google']['ads']['conversion_label'] && !$this->options['google']['ads']['conversion_id']) {
			echo '<p><span class="dashicons dashicons-info"></span>';
			esc_html_e('Requires an active Google Ads Conversion ID', 'woocommerce-google-adwords-conversion-tracking-tag');
		}
		echo '</p>';
	}

	public function wpm_option_html_google_optimize_container_id() {
		?>
		<input id='wpm_plugin_google_optimize_container_id'
			   name='wgact_plugin_options[google][optimize][container_id]'
			   size='40'
			   type='text'
			   value='<?php esc_html_e($this->options['google']['optimize']['container_id']); ?>'
		/>
		<?php
		$this->get_status_icon_new($this->options['google']['optimize']['container_id']);
//        echo $this->get_documentation_html('/wgact/#/plugin-configuration?id=configure-the-plugin');
		$this->get_documentation_html_by_key('google_optimize_container_id');
		echo '<br><br>';
		esc_html_e('The Google Optimize container ID looks like this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>GTM-WMAB1BM</i>';
	}

	public function wpm_option_html_facebook_pixel_id() {
		?>
		<input id='wpm_plugin_facebook_pixel_id'
			   name='wgact_plugin_options[facebook][pixel_id]'
			   size='40'
			   type='text'
			   value='<?php esc_html_e($this->options['facebook']['pixel_id']); ?>'
		/>
		<?php
		$this->get_status_icon_new($this->options['facebook']['pixel_id']);
		$this->get_documentation_html_by_key('facebook_pixel_id');
		echo '<br><br>';
		esc_html_e('The Meta (Facebook) pixel ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>765432112345678</i>';
	}

	public function wpm_option_html_bing_uet_tag_id() {
		?>
		<input id='wpm_plugin_bing_uet_tag_id'
			   name='wgact_plugin_options[bing][uet_tag_id]'
			   size='40'
			   type='text'
			   value='<?php esc_html_e($this->options['bing']['uet_tag_id']); ?>'
			<?php esc_html_e($this->disable_if_demo()); ?>
		/>
		<?php
		$this->get_status_icon_new($this->options['bing']['uet_tag_id']);
		$this->get_documentation_html_by_key('bing_uet_tag_id');
		$this->html_pro_feature();
		echo '<br><br>';
		esc_html_e('The Microsoft Advertising UET tag ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>12345678</i>';
	}

	public function wpm_option_html_twitter_pixel_id() {
		?>
		<input
				id='wpm_plugin_twitter_pixel_id'
				name='wgact_plugin_options[twitter][pixel_id]'
				size='40'
				type='text'
				value='<?php esc_html_e($this->options['twitter']['pixel_id']); ?>'
			<?php esc_html_e($this->disable_if_demo()); ?>
		/>
		<?php
		$this->get_status_icon_new($this->options['twitter']['pixel_id']);
//        ($this->get_documentation_html_by_key('twitter_pixel_id'));
		$this->html_pro_feature();
		echo '<br><br>';
		esc_html_e('The Twitter pixel ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>a1cde</i>';
	}

	public function wpm_option_html_pinterest_pixel_id() {
		?>
		<input
				id='wpm_plugin_pinterest_pixel_id'
				name='wgact_plugin_options[pinterest][pixel_id]'
				size='40'
				type='text'
				value='<?php esc_html_e($this->options['pinterest']['pixel_id']); ?>'
			<?php esc_html_e($this->disable_if_demo()); ?>
		/>
		<?php
		$this->get_status_icon_new($this->options['pinterest']['pixel_id']);
//        ($this->get_documentation_html_by_key('pinterest_pixel_id'));
		$this->html_pro_feature();
		echo '<br><br>';
		esc_html_e('The Pinterest pixel ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>1234567890123</i>';
	}

	public function wpm_option_html_snapchat_pixel_id() {
		?>
		<input
				id='wpm_plugin_snapchat_pixel_id'
				name='wgact_plugin_options[snapchat][pixel_id]'
				size='40'
				type='text'
				value='<?php esc_html_e($this->options['snapchat']['pixel_id']); ?>'
			<?php esc_html_e($this->disable_if_demo()); ?>
		/>
		<?php
		$this->get_status_icon_new($this->options['snapchat']['pixel_id']);
//        ($this->get_documentation_html_by_key('snapchat_pixel_id'));
		$this->html_pro_feature();
		echo '<br><br>';
		esc_html_e('The Snapchat pixel ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>1a2345b6-cd78-9012-e345-fg6h7890ij12</i>';
	}

	public function wpm_option_html_tiktok_pixel_id() {
		?>
		<input
				id='wpm_plugin_tiktok_pixel_id'
				name='wgact_plugin_options[tiktok][pixel_id]'
				size='40'
				type='text'
				value='<?php esc_html_e($this->options['tiktok']['pixel_id']); ?>'
			<?php esc_html_e($this->disable_if_demo()); ?>
		/>
		<?php
		$this->get_status_icon_new($this->options['tiktok']['pixel_id']);
//        ($this->get_documentation_html_by_key('tiktok_pixel_id'));
		$this->html_pro_feature();
		echo '<br><br>';
		esc_html_e('The TikTok pixel ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>ABCD1E2FGH3IJK45LMN6</i>';
	}

	public function wpm_option_html_hotjar_site_id() {
		?>
		<input id='wpm_plugin_hotjar_site_id' name='wgact_plugin_options[hotjar][site_id]' size='40' type='text'
			   value='<?php esc_html_e($this->options['hotjar']['site_id']); ?>'/>
		<?php
		$this->get_status_icon_new($this->options['hotjar']['site_id']);
		$this->get_documentation_html_by_key('hotjar_site_id');
		echo '<br><br>';
		esc_html_e('The Hotjar site ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '&nbsp;<i>1234567</i>';
	}

	public function wpm_option_html_shop_order_total_logic() {
		?>
		<label>
			<input type='radio' id='wpm_plugin_order_total_logic_0'
				   name='wgact_plugin_options[shop][order_total_logic]'
				   value='0' <?php echo( checked(0, $this->options['shop']['order_total_logic'], false) ); ?> >
			<?php esc_html_e('Use order_subtotal: Doesn\'t include tax and shipping (default)', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_order_total_logic_1'
				   name='wgact_plugin_options[shop][order_total_logic]'
				   value='1' <?php echo( checked(1, $this->options['shop']['order_total_logic'], false) ); ?> >
			<?php esc_html_e('Use order_total: Includes tax and shipping', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br><br>
		<?php esc_html_e('This is the order total amount reported back to Google Ads', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		<?php
	}

	private function get_documentation_html_by_key( $key = 'default' ) {
		return $this->get_documentation_html($this->documentation->get_link($key));
	}

	protected function get_documentation_html( $path ) {
//		$html  = '<a class="documentation-icon" href="' . $path . '" target="_blank">';
//		$html .= '<span style="vertical-align: top; margin-top: 0px" class="dashicons dashicons-info-outline tooltip"><span class="tooltiptext">';
//		$html .= esc_html__('open the documentation', 'woocommerce-google-adwords-conversion-tracking-tag');
//		$html .= '</span></span></a>';
//
//		return $html;

		?>
		<a class="documentation-icon" href="<?php echo esc_url($path); ?>" target="_blank">
		<span style="vertical-align: top; margin-top: 0" class="dashicons dashicons-info-outline tooltip"><span
					class="tooltiptext">
		<?php esc_html_e('open the documentation', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</span></span></a>

		<?php
	}


	public function wpm_setting_html_google_consent_mode_active() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][consent_mode][active]'>
			<input type='checkbox' id='wpm_setting_google_consent_mode_active'
				   name='wgact_plugin_options[google][consent_mode][active]'
				   value='1'
				<?php checked($this->options['google']['consent_mode']['active']); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php esc_html_e('Enable Google consent mode with standard settings', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['google']['consent_mode']['active'], true, true);
		?>
		<?php
		$this->get_documentation_html_by_key('google_consent_mode');
		$this->html_pro_feature();
	}

	public function wpm_setting_html_google_consent_regions() {
		// https://semantic-ui.com/modules/dropdown.html#multiple-selection
		// https://developer.woocommerce.com/2017/08/08/selectwoo-an-accessible-replacement-for-select2/
		// https://github.com/woocommerce/selectWoo
		?>
		<select id="wpm_setting_google_consent_regions" multiple="multiple"
				name="wgact_plugin_options[google][consent_mode][regions][]"
				style="width:350px;" data-placeholder="Choose countries&hellip;" aria-label="Country"
				class="wc-enhanced-select"
			<?php esc_html_e($this->disable_if_demo()); ?>
		>
			<?php foreach ($this->consent_mode_regions->get_consent_mode_regions() as $region_code => $region_name) : ?>
				<option value="<?php esc_html_e($region_code); ?>" <?php esc_html_e(in_array($region_code, $this->options['google']['consent_mode']['regions'], true) ? 'selected' : ''); ?>><?php esc_html_e($region_name); ?></option>
			<?php endforeach; ?>

		</select>
		<script>
			jQuery("#wpm_setting_google_consent_regions").select2({
				// theme: "classic"
			})
		</script>
		<?php
		$this->get_documentation_html_by_key('google_consent_regions');
		$this->html_pro_feature();
		?>
		<p>
			<span class="dashicons dashicons-info"></span>
			<?php
			esc_html_e('If no region is set, then the restrictions are enabled for all regions. If you specify one or more regions, then the restrictions only apply for the specified regions.', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</p>
		<?php

	}

	public function wpm_setting_html_google_analytics_eec() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][analytics][eec]'>
			<input type='checkbox' id='wpm_setting_google_analytics_eec'
				   name='wgact_plugin_options[google][analytics][eec]'
				   value='1'
				<?php checked($this->options['google']['analytics']['eec']); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php
			esc_html_e('Enable Google Analytics enhanced e-commerce', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['google']['analytics']['eec'], $this->options['google']['analytics']['universal']['property_id'] || $this->options['google']['analytics']['ga4']['measurement_id'], true);
		$this->html_pro_feature();
//        ($this->get_documentation_html_by_key('google_analytics_eec'));
		?>
		<?php
		if ($this->options['google']['analytics']['eec'] && ( !$this->options['google']['analytics']['universal']['property_id'] && !$this->options['google']['analytics']['ga4']['measurement_id'] )) {
			echo '<p></p><span class="dashicons dashicons-info"></span>';
			esc_html_e('You need to activate at least Google Analytics UA or Google Analytics 4', 'woocommerce-google-adwords-conversion-tracking-tag');
			echo '</p><br>';
		}
	}

	public function wpm_setting_html_google_analytics_4_api_secret() {
		?>
		<input
				id='wpm_setting_google_analytics_4_api_secret'
				name='wgact_plugin_options[google][analytics][ga4][api_secret]'
				size='40'
				type='text'
				value='<?php esc_html_e($this->options['google']['analytics']['ga4']['api_secret']); ?>'
			<?php esc_html_e($this->disable_if_demo()); ?>
		/>
		<?php
		$this->get_status_icon_new($this->options['google']['analytics']['ga4']['api_secret'], $this->options['google']['analytics']['eec']);
		$this->get_documentation_html_by_key('google_analytics_4_api_secret');
		$this->html_pro_feature();
		echo '<br><br>';
		if (!$this->options['google']['analytics']['ga4']['measurement_id']) {
			echo '<p></p><span class="dashicons dashicons-info" style="margin-right: 10px"></span>';
			esc_html_e('Google Analytics 4 activation required', 'woocommerce-google-adwords-conversion-tracking-tag');
			echo '</p>';
		}
		if (!$this->options['google']['analytics']['eec']) {
			echo '<p></p><span class="dashicons dashicons-info" style="margin-right: 10px"></span>';
			esc_html_e('Enhanced E-Commerce activation required', 'woocommerce-google-adwords-conversion-tracking-tag');
			echo '</p><br>';
		}
		esc_html_e('If enabled, purchase and refund events will be sent to Google through the measurement protocol for increased accuracy.', 'woocommerce-google-adwords-conversion-tracking-tag');
	}

	public function wpm_setting_html_google_analytics_link_attribution() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][analytics][link_attribution]'>
			<input type='checkbox' id='wpm_setting_google_analytics_link_attribution'
				   name='wgact_plugin_options[google][analytics][link_attribution]'
				   value='1' <?php checked($this->options['google']['analytics']['link_attribution']); ?>
			/>
			<?php
			esc_html_e('Enable Google Analytics enhanced link attribution', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new(
			$this->options['google']['analytics']['link_attribution'],
			$this->options['google']['analytics']['universal']['property_id'] || $this->options['google']['analytics']['ga4']['measurement_id'],
			true
		);
		?>
		<?php
//        echo $this->get_documentation_html('/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-consent-mode#/consent-mgmt/google-consent-mode');
		?>
		<?php
		if ($this->options['google']['analytics']['link_attribution'] && ( !$this->options['google']['analytics']['universal']['property_id'] && !$this->options['google']['analytics']['ga4']['measurement_id'] )) {
			echo '<p></p><span class="dashicons dashicons-info"></span>';
			esc_html_e('You need to activate at least Google Analytics UA or Google Analytics 4', 'woocommerce-google-adwords-conversion-tracking-tag');
			echo '</p><br>';
		}
	}

	public function wpm_setting_html_google_user_id() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][user_id]'>
			<input type='checkbox' id='wpm_setting_google_user_id'
				   name='wgact_plugin_options[google][user_id]'
				   value='1'
				<?php checked($this->options['google']['user_id']); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php
			esc_html_e('Enable Google user ID', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['google']['user_id'], $this->options['google']['analytics']['universal']['property_id'] || $this->options['google']['analytics']['ga4']['measurement_id'] || $this->google->is_google_ads_active(), true);
		$this->html_pro_feature();
//        echo $this->get_documentation_html('/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-consent-mode#/consent-mgmt/google-consent-mode');
		?>
		<?php
		if ($this->options['google']['analytics']['eec'] && ( !$this->options['google']['analytics']['universal']['property_id'] && !$this->options['google']['analytics']['ga4']['measurement_id'] )) {
			echo '<p></p><span class="dashicons dashicons-info"></span>';
			esc_html_e('You need to activate at least Google Analytics UA or Google Analytics 4', 'woocommerce-google-adwords-conversion-tracking-tag');
			echo '</p><br>';
		}
	}

	public function wpm_setting_html_google_ads_enhanced_conversions() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][ads][enhanced_conversions]'>
			<input type='checkbox' id='wpm_setting_google_user_id'
				   name='wgact_plugin_options[google][ads][enhanced_conversions]'
				   value='1'
				<?php checked($this->options['google']['ads']['enhanced_conversions']); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php
			esc_html_e('Enable Google Ads Enhanced Conversions', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['google']['ads']['enhanced_conversions'], $this->google->is_google_ads_active(), false);
		$this->html_pro_feature();
		$this->get_documentation_html_by_key('google_ads_enhanced_conversions');
		?>
		<?php
		if (!$this->google->is_google_ads_active()) {
			echo '<p></p><span class="dashicons dashicons-info"></span>';
			esc_html_e('You need to activate Google Ads', 'woocommerce-google-adwords-conversion-tracking-tag');
			echo '</p><br>';
		}
	}

	public function wpm_setting_html_google_ads_phone_conversion_number() {
		?>
		<input
				id='wpm_plugin_google_ads_phone_conversion_number'
				name='wgact_plugin_options[google][ads][phone_conversion_number]'
				size='40'
				type='text'
				value='<?php esc_html_e($this->options['google']['ads']['phone_conversion_number']); ?>'
			<?php esc_html_e($this->disable_if_demo()); ?>
		/>
		<?php
		$this->get_status_icon_new($this->options['google']['ads']['phone_conversion_number'], $this->options['google']['ads']['phone_conversion_label'] && $this->options['google']['ads']['phone_conversion_number']);
		$this->get_documentation_html_by_key('google_ads_phone_conversion_number');
		$this->html_pro_feature();
		echo '<br><br>';
		esc_html_e('The Google Ads phone conversion number must be in the same format as on the website.', 'woocommerce-google-adwords-conversion-tracking-tag');
	}

	public function wpm_setting_html_google_ads_phone_conversion_label() {
		?>
		<input
				id='wpm_plugin_google_ads_phone_conversion_label'
				name='wgact_plugin_options[google][ads][phone_conversion_label]'
				size='40'
				type='text'
				value='<?php esc_html_e($this->options['google']['ads']['phone_conversion_label']); ?>'
			<?php esc_html_e($this->disable_if_demo()); ?>
		/>
		<?php
		$this->get_status_icon_new($this->options['google']['ads']['phone_conversion_label'], $this->options['google']['ads']['phone_conversion_label'] && $this->options['google']['ads']['phone_conversion_number']);
		$this->get_documentation_html_by_key('google_ads_phone_conversion_label');
		$this->html_pro_feature();
		echo '<br><br>';
//        esc_html_e('The Google Ads phone conversion label must be in the same format as on the website.', 'woocommerce-google-adwords-conversion-tracking-tag');
	}

	public function wpm_setting_html_borlabs_support() {
		esc_html_e('Borlabs Cookie detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag');
		$this->get_status_icon_new(true, true, true);
		$this->html_pro_feature();
	}

	public function wpm_setting_html_cookiebot_support() {
		esc_html_e('Cookiebot detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag');
		$this->get_status_icon_new(true, true, true);
		$this->html_pro_feature();
	}

	public function wpm_setting_html_complianz_support() {
		esc_html_e('Complianz GDPR detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag');
		$this->get_status_icon_new(true, true, true);
		$this->html_pro_feature();
	}

	public function wpm_setting_html_cookie_notice_support() {
		esc_html_e('Cookie Notice (by hu-manity.co) detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag');
		$this->get_status_icon_new(true, true, true);
		$this->html_pro_feature();
	}

	public function wpm_setting_html_cookie_script_support() {
		esc_html_e('Cookie Script (by cookie-script.com) detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag');
		$this->get_status_icon_new(true, true, true);
		$this->html_pro_feature();
	}

	public function wpm_setting_html_moove_gdpr_support() {
		esc_html_e('GDPR Cookie Compliance (by Moove Agency) detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag');
		$this->get_status_icon_new(true, true, true);
		$this->html_pro_feature();
	}

	public function wpm_setting_html_cookie_law_info_support() {
		esc_html_e('GDPR Cookie Consent (by WebToffee) detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag');
		$this->get_status_icon_new(true, true, true);
		$this->html_pro_feature();
	}

	public function wpm_setting_html_explicit_consent_mode() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[shop][cookie_consent_mgmt][explicit_consent]'>
			<input type='checkbox' id='wpm_setting_explicit_consent_mode'
				   name='wgact_plugin_options[shop][cookie_consent_mgmt][explicit_consent]'
				   value='1'
				<?php checked($this->options['shop']['cookie_consent_mgmt']['explicit_consent']); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php
			esc_html_e('Enable Explicit Consent Mode', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['shop']['cookie_consent_mgmt']['explicit_consent'], true, true);
		$this->get_documentation_html_by_key('explicit_consent_mode');
		$this->html_pro_feature();

		echo '<p style="margin-top:10px">';
		esc_html_e('Only activate the Explicit Consent Mode if you are also using a Cookie Management Platform (a cookie banner) that is compatible with this plugin. Find a list of compatible plugins in the documentation.', 'woocommerce-google-adwords-conversion-tracking-tag');
		echo '</p>';

	}

	public function wpm_setting_html_facebook_capi_token() {
		?>
		<textarea id='wpm_setting_facebook_capi_token'
				  name='wgact_plugin_options[facebook][capi][token]'
				  cols='60'
				  rows='5'
		<?php esc_html_e($this->disable_if_demo()); ?>
		><?php esc_html_e($this->options['facebook']['capi']['token']); ?></textarea>
		<?php
		$this->get_status_icon_new($this->options['facebook']['capi']['token'], $this->options['facebook']['pixel_id']);
		$this->get_documentation_html_by_key('facebook_capi_token');
		$this->html_pro_feature();
		if (!$this->options['facebook']['pixel_id']) {
			echo '<p></p><span class="dashicons dashicons-info"></span>';
			esc_html_e('You need to activate the Meta (Facebook) pixel', 'woocommerce-google-adwords-conversion-tracking-tag');
			echo '</p><br>';
		}
//        echo $this->get_documentation_html('/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-ads-conversion-id#/pixels/google-ads?id=configure-the-plugin');
		echo '<br><br>';
//        esc_html_e('The conversion ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
//        echo '&nbsp;<i>123456789</i>';
	}

	public function wpm_setting_facebook_capi_user_transparency_process_anonymous_hits() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0'
				   name='wgact_plugin_options[facebook][capi][user_transparency][process_anonymous_hits]'>
			<input type='checkbox' id='wpm_setting_facebook_capi_user_transparency_process_anonymous_hits'
				   name='wgact_plugin_options[facebook][capi][user_transparency][process_anonymous_hits]'
				   value='1'
				<?php checked($this->options['facebook']['capi']['user_transparency']['process_anonymous_hits']); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php
			esc_html_e('Send CAPI hits for anonymous visitors who likely have blocked the Meta (Facebook) pixel.', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['facebook']['capi']['user_transparency']['process_anonymous_hits'], $this->options['facebook']['pixel_id'], true);
		$this->get_documentation_html_by_key('facebook_capi_user_transparency_process_anonymous_hits');
		$this->html_pro_feature();
		if ($this->options['facebook']['capi']['user_transparency']['process_anonymous_hits'] && !$this->options['facebook']['pixel_id']) {
			echo '<p></p><span class="dashicons dashicons-info"></span>';
			esc_html_e('You need to activate the Meta (Facebook) pixel', 'woocommerce-google-adwords-conversion-tracking-tag');
			echo '</p><br>';
		}
	}

	public function wpm_setting_facebook_capi_user_transparency_send_additional_client_identifiers() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0'
				   name='wgact_plugin_options[facebook][capi][user_transparency][send_additional_client_identifiers]'>
			<input type='checkbox' id='wpm_setting_facebook_capi_user_transparency_send_additional_client_identifiers'
				   name='wgact_plugin_options[facebook][capi][user_transparency][send_additional_client_identifiers]'
				   value='1'
				<?php checked($this->options['facebook']['capi']['user_transparency']['send_additional_client_identifiers']); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php
			esc_html_e('Include additional visitor\'s identifiers, such as IP address, email and shop ID in the CAPI hit.', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['facebook']['capi']['user_transparency']['send_additional_client_identifiers'], $this->options['facebook']['pixel_id'], true);
		$this->get_documentation_html_by_key('facebook_capi_user_transparency_send_additional_client_identifiers');
		$this->html_pro_feature();
		if ($this->options['facebook']['capi']['user_transparency']['send_additional_client_identifiers'] && !$this->options['facebook']['pixel_id']) {
			echo '<p></p><span class="dashicons dashicons-info"></span>';
			esc_html_e('You need to activate the Meta (Facebook) pixel', 'woocommerce-google-adwords-conversion-tracking-tag');
			echo '</p><br>';
		}
	}

	public function wpm_setting_html_facebook_microdata() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[facebook][microdata]'>
			<input type='checkbox' id='wpm_setting_facebook_microdata_active'
				   name='wgact_plugin_options[facebook][microdata]'
				   value='1'
				<?php checked($this->options['facebook']['microdata']); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php
			esc_html_e('Enable Meta (Facebook) product microdata output', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['facebook']['microdata'], $this->options['facebook']['pixel_id'], true);
		$this->get_documentation_html_by_key('facebook_microdata');
		$this->html_pro_feature();
		if ($this->options['facebook']['microdata'] && !$this->options['facebook']['pixel_id']) {
			echo '<p></p><span class="dashicons dashicons-info"></span>';
			esc_html_e('You need to activate the Meta (Facebook) pixel', 'woocommerce-google-adwords-conversion-tracking-tag');
			echo '</p><br>';
		}
	}

	public function wpm_setting_html_order_duplication_prevention() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[shop][order_deduplication]'>
			<input type='checkbox' id='wpm_setting_order_duplication_prevention'
				   name='wgact_plugin_options[shop][order_deduplication]'
				   value='1' <?php checked($this->options['shop']['order_deduplication']); ?>
			/>
			<?php
			$this->get_order_duplication_prevention_text();
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['shop']['order_deduplication']);
		?>
		<br>
		<p>
			<span class="dashicons dashicons-info"></span>
			<?php
			esc_html_e('Only disable order duplication prevention for testing. Remember to re-enable the setting once done.', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</p>
		<?php
	}

	public function wpm_setting_html_maximum_compatibility_mode() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[general][maximum_compatibility_mode]'>
			<input type='checkbox' id='wpm_setting_maximum_compatibility_mode'
				   name='wgact_plugin_options[general][maximum_compatibility_mode]'
				   value='1' <?php checked($this->options['general']['maximum_compatibility_mode']); ?> />
			<?php
			esc_html_e('Enable the maximum compatibility mode', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['general']['maximum_compatibility_mode'], true, true);
		$this->get_documentation_html_by_key('maximum_compatibility_mode');
	}

	public function wpm_setting_html_disable_tracking_for_user_roles() {
		// https://semantic-ui.com/modules/dropdown.html#multiple-selection
		// https://developer.woocommerce.com/2017/08/08/selectwoo-an-accessible-replacement-for-select2/
		// https://github.com/woocommerce/selectWoo
		?>
		<select id="wpm_setting_disable_tracking_for_user_roles" multiple="multiple"
				name="wgact_plugin_options[shop][disable_tracking_for][]"
				style="width:350px; padding-left: 10px" data-placeholder="Choose roles&hellip;" aria-label="Roles"
				class="wc-enhanced-select"
			<?php esc_html_e($this->disable_if_demo()); ?>
		>
			<?php foreach (get_editable_roles() as $role => $details) : ?>
				<option value="<?php esc_html_e($role); ?>" <?php esc_html_e(in_array($role, $this->options['shop']['disable_tracking_for'], true) ? 'selected' : ''); ?>><?php esc_html_e($details['name']); ?></option>
			<?php endforeach; ?>

		</select>
		<script>
			jQuery("#wpm_setting_disable_tracking_for_user_roles").select2({
				// theme: "classic"
			})
		</script>
		<?php
//		$this->get_documentation_html_by_key('google_consent_regions');
		$this->html_pro_feature();
	}

	private function get_order_duplication_prevention_text() {
		if (true) {
			esc_html_e('Advanced order duplication prevention is ', 'woocommerce-google-adwords-conversion-tracking-tag');
		} else {
			esc_html_e('Basic order duplication prevention is ', 'woocommerce-google-adwords-conversion-tracking-tag');
		}
	}

	private function add_to_cart_requirements_fulfilled() {
		if ($this->options['google']['ads']['conversion_id'] && $this->options['google']['ads']['conversion_label'] && $this->options['google']['ads']['aw_merchant_id']) {
			return true;
		} else {
			return false;
		}
	}

	public function wpm_option_html_google_ads_dynamic_remarketing() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][ads][dynamic_remarketing]'>
			<input type='checkbox' id='wpm_plugin_option_gads_dynamic_remarketing'
				   name='wgact_plugin_options[google][ads][dynamic_remarketing]'
				   value='1' <?php checked($this->options['google']['ads']['dynamic_remarketing']); ?> />

			<?php
			esc_html_e('Enable dynamic remarketing audience collection', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['google']['ads']['dynamic_remarketing'], $this->options['google']['ads']['conversion_id']);
		?>
		<?php
		$this->get_documentation_html_by_key('google_ads_dynamic_remarketing');
		?>
		<p>
			<?php
			if (!$this->options['google']['ads']['conversion_id']) {
				?>
				<span class="dashicons dashicons-info"></span>
				<?php
				esc_html_e('Requires an active Google Ads Conversion ID', 'woocommerce-google-adwords-conversion-tracking-tag');
				echo '<br>';
			}
			?>
			<span class="dashicons dashicons-info"></span>
			<?php
			esc_html_e('You need to choose the correct product identifier setting in order to match the product identifiers in the product feeds.', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</p>
		<?php
	}

	public function wpm_option_html_variations_output() {
		// adding the hidden input is a hack to make WordPress save the option with the value zero,
		// instead of not saving it and remove that array key entirely
		// https://stackoverflow.com/a/1992745/4688612
		?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[general][variations_output]'>
			<input type='checkbox' id='wpm_plugin_option_variations_output'
				   name='wgact_plugin_options[general][variations_output]'
				   value='1' <?php checked($this->options['general']['variations_output']); ?>
			/>
			<?php
			esc_html_e('Enable variations output', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</label>
		<?php
		$this->get_status_icon_new($this->options['general']['variations_output'], $this->options['google']['ads']['dynamic_remarketing'], true);
		?>
		<?php
		$this->get_documentation_html_by_key('variations_output');
		?>
		<p><span class="dashicons dashicons-info"></span>
			<?php
			esc_html_e('In order for this to work you need to upload your product feed including product variations and the item_group_id. Disable it, if you choose only to upload the parent product for variable products.', 'woocommerce-google-adwords-conversion-tracking-tag');
			?>
		</p>
		<?php
	}

	public function wpm_plugin_option_google_business_vertical() {
		?>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_0'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='0'
				<?php echo( checked(0, $this->options['google']['ads']['google_business_vertical'], false) ); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php esc_html_e('Retail', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_1'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='1'
				<?php echo( checked(1, $this->options['google']['ads']['google_business_vertical'], false) ); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php esc_html_e('Education', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_3'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='3'
				<?php echo( checked(3, $this->options['google']['ads']['google_business_vertical'], false) ); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php esc_html_e('Hotels and rentals', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_4'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='4'
				<?php echo( checked(4, $this->options['google']['ads']['google_business_vertical'], false) ); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php esc_html_e('Jobs', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_5'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='5'
				<?php echo( checked(5, $this->options['google']['ads']['google_business_vertical'], false) ); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php esc_html_e('Local deals', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_6'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='6'
				<?php echo( checked(6, $this->options['google']['ads']['google_business_vertical'], false) ); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php esc_html_e('Real estate', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_8'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='8'
				<?php echo( checked(8, $this->options['google']['ads']['google_business_vertical'], false) ); ?>
				<?php esc_html_e($this->disable_if_demo()); ?>
			/>
			<?php esc_html_e('Custom', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<?php
	}


	public function wpm_plugin_setting_aw_merchant_id() {
		?>
		<input type="text"
			   id="wpm_plugin_aw_merchant_id"
			   name="wgact_plugin_options[google][ads][aw_merchant_id]"
			   size="40"
			   value="<?php esc_html_e($this->options['google']['ads']['aw_merchant_id']); ?>"
		/>
		<?php $this->get_status_icon_new($this->options['google']['ads']['aw_merchant_id']); ?>
		<?php $this->get_documentation_html_by_key('aw_merchant_id'); ?>
		<br><br>
		<?php
		esc_html_e('ID of your Google Merchant Center account. It looks like this: 12345678', 'woocommerce-google-adwords-conversion-tracking-tag');
	}

	public function wpm_plugin_option_product_identifier() {
		?>
		<label>
			<input type='radio' id='wpm_plugin_option_product_identifier_0'
				   name='wgact_plugin_options[google][ads][product_identifier]'
				   value='0' <?php echo( checked(0, $this->options['google']['ads']['product_identifier'], false) ); ?>/>
			<?php esc_html_e('post ID (default)', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_option_product_identifier_2'
				   name='wgact_plugin_options[google][ads][product_identifier]'
				   value='2' <?php echo( checked(2, $this->options['google']['ads']['product_identifier'], false) ); ?>/>
			<?php esc_html_e('SKU', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_option_product_identifier_1'
				   name='wgact_plugin_options[google][ads][product_identifier]'
				   value='1' <?php echo( checked(1, $this->options['google']['ads']['product_identifier'], false) ); ?>/>
			<?php esc_html_e('ID for the WooCommerce Google Product Feed. Outputs the post ID with woocommerce_gpf_ prefix *', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_option_product_identifier_3'
				   name='wgact_plugin_options[google][ads][product_identifier]'
				   value='3' <?php echo( checked(3, $this->options['google']['ads']['product_identifier'], false) ); ?>/>
			<?php esc_html_e('ID for the WooCommerce Google Listings & Ads Plugin. Outputs the post ID with gla_ prefix **', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</label>
		<br>
		<p style="margin-top:10px">
			<?php esc_html_e('Choose a product identifier.', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		</p>
		<br>
		<?php esc_html_e('* This is for users of the WooCommerce Google Product Feed Plugin', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		<a href="https://woocommerce.com/products/google-product-feed/" target="_blank">WooCommerce Google Product Feed
			Plugin</a>
		<br>
		<?php esc_html_e('** This is for users of the WooCommerce Google Listings & Ads Plugin', 'woocommerce-google-adwords-conversion-tracking-tag'); ?>
		<a href="https://woocommerce.com/products/google-listings-and-ads/" target="_blank">WooCommerce Google Listings
			& Ads Plugin
			Plugin</a>

		<?php
	}

	private function html_beta() {
		return '<div class="status-icon beta">' . esc_html__('beta', 'woocommerce-google-adwords-conversion-tracking-tag') . '</div>';
	}

	private function html_active() {
		return '<div class="status-icon active">' . esc_html__('active', 'woocommerce-google-adwords-conversion-tracking-tag') . '</div>';
	}

	private function html_active_new() {
		?>
		<div class="status-icon active"><?php esc_html_e('active', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></div>
		<?php
	}

	private function html_inactive() {
		return '<div class="status-icon inactive">' . esc_html__('inactive', 'woocommerce-google-adwords-conversion-tracking-tag') . '</div>';
	}

	private function html_inactive_new() {
		?>
		<div class="status-icon inactive"><?php esc_html_e('inactive', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></div>
		<?php
	}

	private function html_partially_active() {
		return '<div class="status-icon partially-active">' . esc_html__('partially active', 'woocommerce-google-adwords-conversion-tracking-tag') . '</div>';
	}

	private function html_partially_active_new() {
		?>
		<div class="status-icon partially-active"><?php esc_html_e('partially active', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></div>
		<?php
	}

	private function html_pro_feature() {
		if (!true && $this->options['general']['pro_version_demo']) {
//            if (1===1) {

//			return '<div class="pro-feature">' . esc_html__('Pro Feature', 'woocommerce-google-adwords-conversion-tracking-tag') . '</div>';

			?>
			<div class="pro-feature"><?php esc_html_e('Pro Feature', 'woocommerce-google-adwords-conversion-tracking-tag'); ?></div>

			<?php
		}
	}

	private function get_status_icon( $status, $requirements = true, $inactive_silent = false ) {
		if ($status && $requirements) {
			return $this->html_active();
		} elseif ($status && !$requirements) {
			return $this->html_partially_active();
		} elseif (false == $inactive_silent) {
			return $this->html_inactive();
		}
		return '';
	}

	private function get_status_icon_new( $status, $requirements = true, $inactive_silent = false ) {
		if ($status && $requirements) {
			$this->html_active_new();
		} elseif ($status && !$requirements) {
			$this->html_partially_active_new();
		} elseif (false == $inactive_silent) {
			$this->html_inactive_new();
		}
		return '';
	}

	private function disable_if_demo() {
		if (!true && $this->options['general']['pro_version_demo']) {
			return 'disabled';
		} else {
			return '';
		}
	}

	// validate the options
	public function wpm_options_validate( $input ) {
//        error_log(print_r($input, true));

		// validate Google Analytics Universal property ID
		if (isset($input['google']['analytics']['universal']['property_id'])) {
			if (!$this->validations->is_google_analytics_universal_property_id($input['google']['analytics']['universal']['property_id'])) {
				$input['google']['analytics']['universal']['property_id'] = isset($this->options['google']['analytics']['universal']['property_id']) ? $this->options['google']['analytics']['universal']['property_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-google-analytics-universal-property-id', esc_html__('You have entered an invalid Google Analytics Universal property ID.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate Google Analytics 4 measurement ID
		if (isset($input['google']['analytics']['ga4']['measurement_id'])) {
			if (!$this->validations->is_google_analytics_4_measurement_id($input['google']['analytics']['ga4']['measurement_id'])) {
				$input['google']['analytics']['ga4']['measurement_id'] = isset($this->options['google']['analytics']['ga4']['measurement_id']) ? $this->options['google']['analytics']['ga4']['measurement_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-google-analytics-4-measurement-id', esc_html__('You have entered an invalid Google Analytics 4 measurement ID.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate Google Analytics 4 API key
		if (isset($input['google']['analytics']['ga4']['api_secret'])) {
			if (!$this->validations->is_google_analytics_4_api_secret($input['google']['analytics']['ga4']['api_secret'])) {
				$input['google']['analytics']['ga4']['api_secret'] = isset($this->options['google']['analytics']['ga4']['api_secret']) ? $this->options['google']['analytics']['ga4']['api_secret'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-google-analytics-4-measurement-id', esc_html__('You have entered an invalid Google Analytics 4 API key.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate ['google]['ads']['conversion_id']
		if (isset($input['google']['ads']['conversion_id'])) {
			if (!$this->validations->is_gads_conversion_id($input['google']['ads']['conversion_id'])) {
				$input['google']['ads']['conversion_id'] = isset($this->options['google']['ads']['conversion_id']) ? $this->options['google']['ads']['conversion_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-conversion-id', esc_html__('You have entered an invalid conversion ID. It only contains 8 to 10 digits.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate ['google]['ads']['conversion_label']
		if (isset($input['google']['ads']['conversion_label'])) {
			if (!$this->validations->is_gads_conversion_label($input['google']['ads']['conversion_label'])) {
				$input['google']['ads']['conversion_label'] = isset($this->options['google']['ads']['conversion_label']) ? $this->options['google']['ads']['conversion_label'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-conversion-label', esc_html__('You have entered an invalid conversion label.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate ['google]['ads']['phone_conversion_label']
		if (isset($input['google']['ads']['phone_conversion_label'])) {
			if (!$this->validations->is_gads_conversion_label($input['google']['ads']['phone_conversion_label'])) {
				$input['google']['ads']['phone_conversion_label'] = isset($this->options['google']['ads']['phone_conversion_label']) ? $this->options['google']['ads']['phone_conversion_label'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-conversion-label', esc_html__('You have entered an invalid conversion label.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate ['google]['ads']['aw_merchant_id']
		if (isset($input['google']['ads']['aw_merchant_id'])) {
			if (!$this->validations->is_gads_aw_merchant_id($input['google']['ads']['aw_merchant_id'])) {
				$input['google']['ads']['aw_merchant_id'] = isset($this->options['google']['ads']['aw_merchant_id']) ? $this->options['google']['ads']['aw_merchant_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-aw-merchant-id', esc_html__('You have entered an invalid merchant ID. It only contains 6 to 12 digits.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate Google Optimize container ID
		if (isset($input['google']['optimize']['container_id'])) {
			if (!$this->validations->is_google_optimize_measurement_id($input['google']['optimize']['container_id'])) {
				$input['google']['optimize']['container_id'] = isset($this->options['google']['optimize']['container_id']) ? $this->options['google']['optimize']['container_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-google-optimize-container-id', esc_html__('You have entered an invalid Google Optimize container ID.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate ['facebook']['pixel_id']
		if (isset($input['facebook']['pixel_id'])) {
			if (!$this->validations->is_facebook_pixel_id($input['facebook']['pixel_id'])) {
				$input['facebook']['pixel_id'] = isset($this->options['facebook']['pixel_id']) ? $this->options['facebook']['pixel_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-facebook-pixel-id', esc_html__('You have entered an invalid Meta (Facebook) pixel ID. It only contains 14 to 16 digits.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate ['facebook']['capi']['token']
		if (isset($input['facebook']['capi']['token'])) {
			if (!$this->validations->is_facebook_capi_token($input['facebook']['capi']['token'])) {
				$input['facebook']['capi']['token'] = isset($this->options['facebook']['capi']['token']) ? $this->options['facebook']['capi']['token'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-facebook-pixel-id', esc_html__('You have entered an invalid Meta (Facebook) CAPI token.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate Bing Ads UET tag ID
		if (isset($input['bing']['uet_tag_id'])) {
			if (!$this->validations->is_bing_uet_tag_id($input['bing']['uet_tag_id'])) {
				$input['bing']['uet_tag_id'] = isset($this->options['bing']['uet_tag_id']) ? $this->options['bing']['uet_tag_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-bing-ads-uet-tag-id', esc_html__('You have entered an invalid Bing Ads UET tag ID. It only contains 7 to 9 digits.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate Twitter pixel ID
		if (isset($input['twitter']['pixel_id'])) {
			if (!$this->validations->is_twitter_pixel_id($input['twitter']['pixel_id'])) {
				$input['twitter']['pixel_id'] = isset($this->options['twitter']['pixel_id']) ? $this->options['twitter']['pixel_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-twitter-pixel-id', esc_html__('You have entered an invalid Twitter pixel ID. It only contains 5 to 7 lowercase letters and numbers.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate Pinterest pixel ID
		if (isset($input['pinterest']['pixel_id'])) {
			if (!$this->validations->is_pinterest_pixel_id($input['pinterest']['pixel_id'])) {
				$input['pinterest']['pixel_id'] = isset($this->options['pinterest']['pixel_id']) ? $this->options['pinterest']['pixel_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-pinterest-pixel-id', esc_html__('You have entered an invalid Pinterest pixel ID. It only contains 13 digits.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate Snapchat pixel ID
		if (isset($input['snapchat']['pixel_id'])) {
			if (!$this->validations->is_snapchat_pixel_id($input['snapchat']['pixel_id'])) {
				$input['snapchat']['pixel_id'] = isset($this->options['snapchat']['pixel_id']) ? $this->options['snapchat']['pixel_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-snapchat-pixel-id', esc_html__('You have entered an invalid Snapchat pixel ID.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate TikTok pixel ID
		if (isset($input['tiktok']['pixel_id'])) {
			if (!$this->validations->is_tiktok_pixel_id($input['tiktok']['pixel_id'])) {
				$input['tiktok']['pixel_id'] = isset($this->options['tiktok']['pixel_id']) ? $this->options['tiktok']['pixel_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-tiktok-pixel-id', esc_html__('You have entered an invalid TikTok pixel ID.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		// validate Hotjar site ID
		if (isset($input['hotjar']['site_id'])) {
			if (!$this->validations->is_hotjar_site_id($input['hotjar']['site_id'])) {
				$input['hotjar']['site_id'] = isset($this->options['hotjar']['site_id']) ? $this->options['hotjar']['site_id'] : '';
				add_settings_error('wgact_plugin_options', 'invalid-hotjar-site-id', esc_html__('You have entered an invalid Hotjar site ID. It only contains 6 to 9 digits.', 'woocommerce-google-adwords-conversion-tracking-tag'));
			}
		}

		/**
		 * Merging with the existing options and overwriting old values
		 * since disabling a checkbox doesn't send a value,
		 * we need to set one to overwrite the old value
		 */

		return array_replace_recursive($this->non_form_keys($input), $input);
	}

	// Recursively go through the array and merge (overwrite old values with new ones
	// if a value is missing in the input array, set it to value zero in the options array
	// Omit key like 'db_version' since they would be overwritten with zero.
	protected function merge_options( $array_existing, $array_input ) {
		$array_output = [];

		foreach ($array_existing as $key => $value) {
			if (array_key_exists($key, $array_input)) {
				if (is_array($value)) {
					$array_output[$key] = $this->merge_options($value, $array_input[$key]);
				} else {
					$array_output[$key] = $array_input[$key];
				}
			} else {
				if (is_array($value)) {
					$array_output[$key] = $this->set_array_value_to_zero($value);
				} else {
					$array_output[$key] = 0;
				}
			}
		}

		return $array_output;
	}

	protected function non_form_keys( $input ) {
		// place here what could be overwritten when a form field is missing
		// and what should not be re-set to the default value
		// but should be preserved

		$non_form_keys = [
			'db_version' => $this->options['db_version'],
			'shop'       => [
				'disable_tracking_for' => [],
			]
		];

		// in case the form field input is missing
//        if (!array_key_exists('google_business_vertical', $input['google']['ads'])) {
//            $non_form_keys['google']['ads']['google_business_vertical'] = $this->options['google']['ads']['google_business_vertical'];
//        }

		return $non_form_keys;
	}

	private function set_array_value_to_zero( $array ) {
		array_walk_recursive(
			$array,
			function ( &$leafnode ) {
				$leafnode = 0;
			}
		);

		return $array;
	}

	private function pro_version_demo_active() {
		if ($this->options['general']['pro_version_demo']) {
			return true;
		} else {
			return false;
		}
	}
}
