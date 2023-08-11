<?php

namespace WCPM\Classes\Admin;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Documentation {

	public function get_link( $key = 'default' ) {

		$documentation_links = [
			'default'                                                            => [
				'default' => '/docs/wpm/',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/'],
			'script_blockers'                                                    => [
				'default' => '/docs/wpm/setup/script-blockers/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=script-blocker-error',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/script-blockers/'],
			'google_analytics_universal_property'                                => [
				'default' => '/docs/wpm/plugin-configuration/google-analytics?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-analytics-property-id',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-analytics/'],
			'google_analytics_4_id'                                              => [
				'default' => '/docs/wpm/plugin-configuration/google-analytics?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-analytics-4-id#connect-an-existing-google-analytics-4-property',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-analytics/#section-3'],
			'google_ads_conversion_id'                                           => [
				'default' => '/docs/wpm/plugin-configuration/google-ads?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-ads-configure-the-plugin#configure-the-plugin',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-ads/#section-2'],
			'google_ads_conversion_label'                                        => [
				'default' => '/docs/wpm/plugin-configuration/google-ads?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-ads-configure-the-plugin#configure-the-plugin',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-ads/#section-2'],
			'google_optimize_container_id'                                       => [
				'default' => '/docs/wpm/plugin-configuration/google-optimize?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-optimize',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-optimize/'],
			'facebook_pixel_id'                                                  => [
				'default' => '/docs/wpm/plugin-configuration/meta?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=facebook-pixel-id#find-the-pixel-id',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/facebook/#find-the-pixel-id'],
			'bing_uet_tag_id'                                                    => [
				'default' => '/docs/wpm/plugin-configuration/microsoft-advertising?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=microsoft-advertising-uet-tag-id#setting-up-the-uet-tag',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/microsoft-advertising-bing-ads/#section-1'],
			'twitter_pixel_id'                                                   => [
				'default' => '/docs/wpm/plugin-configuration/twitter',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/'],
			'pinterest_pixel_id'                                                 => [
				'default' => '/docs/wpm/plugin-configuration/pinterest',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/'],
			'snapchat_pixel_id'                                                  => [
				'default' => '/docs/wpm/plugin-configuration/snapchat',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/'],
			'tiktok_pixel_id'                                                    => [
				'default' => '/docs/wpm/plugin-configuration/tiktok',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/'],
			'hotjar_site_id'                                                     => [
				'default' => '/docs/wpm/plugin-configuration/hotjar?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=hotjar-site-id#hotjar-site-id',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/hotjar/#section-1'],
			'google_gtag_deactivation'                                           => [
				'default' => '/docs/wpm/faq/&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=gtag-js#google-tag-assistant-reports-multiple-installations-of-global-site-tag-gtagjs-detected-what-shall-i-do',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/'],
			'google_consent_mode'                                                => [
				'default' => '/docs/wpm/consent-management/google-consent-mode?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-consent-mode',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/consent-management/google-consent-mode/'],
			'google_consent_regions'                                             => [
				'default' => '/docs/wpm/consent-management/google-consent-mode?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-consent-mode-regions#regions',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/consent-management/google-consent-mode/#section-3'],
			'google_analytics_eec'                                               => [
				'default' => '/docs/wpm/plugin-configuration/google-analytics#enhanced-e-commerce-funnel-setup',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-analytics/#section-5'],
			'google_analytics_4_api_secret'                                      => [
				'default' => '/docs/wpm/plugin-configuration/google-analytics?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-analytics-4-api-secret#ga4-api-secret',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-analytics/#section-4'],
			'google_ads_enhanced_conversions'                                    => [
				'default' => '/docs/wpm/plugin-configuration/google-ads?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-ads-enhanced-conversions#enhanced-conversions',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-ads/#section-5'],
			'google_ads_phone_conversion_number'                                 => [
				'default' => '/docs/wpm/plugin-configuration/google-ads?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-ads-phone-conversion-number#phone-conversion-number',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-ads/#section-4'],
			'google_ads_phone_conversion_label'                                  => [
				'default' => '/docs/wpm/plugin-configuration/google-ads?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-ads-phone-conversion-number#phone-conversion-number',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-ads/#section-4'],
			'explicit_consent_mode'                                              => [
				'default' => '/docs/wpm/consent-management/overview/#explicit-consent-mode',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/consent-management/overview/#section-1'],
			'facebook_capi_token'                                                => [
				'default' => '/docs/wpm/plugin-configuration/meta?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=facebook-capi-token#facebook-conversion-api-capi',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/facebook/#section-4'],
			'facebook_capi_user_transparency_process_anonymous_hits'             => [
				'default' => '/docs/wpm/plugin-configuration/meta?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=facebook-capi-transparency-settings#user-transparency-settings',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/facebook/#section-5'],
			'facebook_capi_user_transparency_send_additional_client_identifiers' => [
				'default' => '/docs/wpm/plugin-configuration/meta?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=facebook-capi-transparency-settings#user-transparency-settings',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/facebook/#section-5'],
			'facebook_microdata'                                                 => [
				'default' => '/docs/wpm/plugin-configuration/meta?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=facebook-microdata#microdata-tags-for-catalogues',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/facebook/#section-8'],
			'maximum_compatibility_mode'                                         => [
				'default' => '/docs/wpm/plugin-configuration/general-settings/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=maximum-compatibility-mode#maximum-compatibility-mode',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/'],
			'google_ads_dynamic_remarketing'                                     => [
				'default' => '/docs/wpm/plugin-configuration/dynamic-remarketing?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=dynamic-remarketing',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/dynamic-remarketing/'],
			'variations_output'                                                  => [
				'default' => '/docs/wpm/plugin-configuration/dynamic-remarketing?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=dynamic-remarketing',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/dynamic-remarketing/'],
			'aw_merchant_id'                                                     => [
				'default' => '/docs/wpm/plugin-configuration/google-ads/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=conversion-cart-data#conversion-cart-data',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/pmw-plugin-configuration/google-ads/#section-3'],
			'custom_thank_you'                                                   => [
				'default' => '/docs/wpm/troubleshooting/#wc-custom-thank-you',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/troubleshooting/#wc-custom-thank-you'],
			'the_dismiss_button_doesnt_work_why'                                 => [
				'default' => '/docs/wpm/faq/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=wpp-pixel-manager-docs&utm_content=dismiss-button-info#the-dismiss-button-doesnt-work-why',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/faq/#section-10'],
			'wp-rocket-javascript-concatenation'                                 => [
				'default' => '/docs/wpm/troubleshooting?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=wp-rocket-javascript-concatenation-error',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/'],
			'litespeed-cache-inline-javascript-after-dom-ready'                  => [
				'default' => '/docs/wpm/troubleshooting?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=litespeed-inline-js-dom-ready-error',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/'],
			'payment-gateways'                                                   => [
				'default' => '/docs/wpm/setup/requirements?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=paypal-standard-warning#payment-gateways',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/requirements/#payment-gateways'],
			'test_order'                                                         => [
				'default' => '/docs/wpm/testing#test-order',
				'wcm'     => '/document/pixel-manager-pro-for-woocommerce/testing/'],
		];

		if (array_key_exists($key, $documentation_links)) {

			// Change to wcm through gulp for the wcm distribution
			$doc_host_url = 'wcm';

			return $this->get_documentation_host() . $documentation_links[$key][$doc_host_url];
		} else {
			error_log('wpm documentation key "' . $key . '" not available');
			return $this->get_documentation_host() . $documentation_links['default'];
		}
	}

	private function get_documentation_host() {
		return 'https://woocommerce.com';
	}
}
