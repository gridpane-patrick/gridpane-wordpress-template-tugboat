<?php

namespace WCPM\Classes;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Default_Options {

	// get the default options
	public function get_default_options() {

		// default options settings
		return [
			'google'     => [
				'ads'          => [
					'conversion_id'            => '',
					'conversion_label'         => '',
					'aw_merchant_id'           => '',
					'product_identifier'       => 0,
					'google_business_vertical' => 0,
					'dynamic_remarketing'      => 0,
					'phone_conversion_number'  => '',
					'phone_conversion_label'   => '',
					'enhanced_conversions'     => 0,
				],
				'analytics'    => [
					'universal'        => [
						'property_id' => '',
					],
					'ga4'              => [
						'measurement_id' => '',
						'api_secret'     => '',
					],
					'eec'              => 0,
					'link_attribution' => 0,
				],
				'optimize'     => [
					'container_id' => '',
				],
				'gtag'         => [
					'deactivation' => 0,
				],
				'consent_mode' => [
					'active'  => 0,
					'regions' => [],
				],
				'user_id'      => 0,
			],
			'facebook'   => [
				'pixel_id'  => '',
				'microdata' => 0,
				'capi'      => [
					'token'             => '',
					'user_transparency' => [
						'process_anonymous_hits'             => false,
						'send_additional_client_identifiers' => false,
					]
				]
			],
			'bing'       => [
				'uet_tag_id' => ''
			],
			'twitter'    => [
				'pixel_id' => ''
			],
			'pinterest'  => [
				'pixel_id' => ''
			],
			'snapchat'   => [
				'pixel_id' => ''
			],
			'tiktok'     => [
				'pixel_id' => ''
			],
			'hotjar'     => [
				'site_id' => ''
			],
			'shop'       => [
				'order_total_logic'    => 0,
				'cookie_consent_mgmt'  => [
					'cookiebot'        => [  // This Cookiebot setting is deprecated. Not in use anymore.
											 'active' => 0
					],
					'explicit_consent' => 0,
				],
				'order_deduplication'  => 1,
				'disable_tracking_for' => [],
			],
			'general'    => [
				'variations_output'          => 1,
				'maximum_compatibility_mode' => 0,
				'pro_version_demo'           => 0,
			],
			'db_version' => WPM_DB_VERSION,
		];
	}

	public function update_with_defaults( $target_array, $default_array ) {

//		error_log(print_r($target_array, true));

		// Walk through every key in the default array
		foreach ($default_array as $default_key => $default_value) {

			// If the target key doesn't exist yet
			// copy all default values,
			// including the subtree if one exists,
			// into the target array.
			if (!isset($target_array[$default_key])) {
				$target_array[$default_key] = $default_value;

				// We only want to keep going down the tree
				// if the array contains more settings in an associative array,
				// otherwise we keep the settings of what's in the target array.
			} elseif ($this->is_associative_array($default_value)) {

				$target_array[$default_key] = $this->update_with_defaults($target_array[$default_key], $default_value);
			}
		}

//		error_log(print_r($target_array, true));
		return $target_array;
	}

	protected function does_contain_nested_arrays( $array ) {

		foreach ($array as $key) {
			if (is_array($key)) {
				return true;
			}
		}

		return false;
	}

	protected function is_associative_array( $array ) {

		if (is_array($array)) {
			return ( array_values($array) !== $array );
		} else {
			return false;
		}
	}
}
