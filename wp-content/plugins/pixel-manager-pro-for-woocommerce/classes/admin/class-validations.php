<?php

namespace WCPM\Classes\Admin;

class Validations {

	public function validate_imported_options( $options ) {

		if (array_key_exists('db_version', $options)) {
			return true;
		} else {
			return false;
		}
	}

	public function is_gads_conversion_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{8,11}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_hotjar_site_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{6,9}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_facebook_capi_token( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[a-zA-Z\d_-]{150,250}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_gads_conversion_label( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[-a-zA-Z_0-9]{17,20}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_gads_aw_merchant_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{6,12}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_google_optimize_measurement_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^(GTM|OPT)-[A-Z0-9]{6,8}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_google_analytics_universal_property_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^UA-\d{6,10}-\d{1,2}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_google_analytics_4_measurement_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^G-[A-Z0-9]{10,12}$/m';

		return $this->validate_with_regex($re, $string);
	}


	public function is_google_analytics_4_api_secret( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[a-zA-Z\d_-]{18,26}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_facebook_pixel_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{14,16}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_bing_uet_tag_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{7,9}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_twitter_pixel_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[a-z0-9]{5,7}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_pinterest_pixel_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{13}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_snapchat_pixel_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[a-z0-9\-]*$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function is_tiktok_pixel_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[A-Z0-9]{20,20}$/m';

		return $this->validate_with_regex($re, $string);
	}

	public function validate_with_regex( $re, $string ) {
		preg_match_all($re, $string, $matches, PREG_SET_ORDER, 0);

		if (isset($matches[0])) {
			return true;
		} else {
			return false;
		}
	}
}
