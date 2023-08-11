<?php

namespace WCPM\Classes\Pixels\Google;

use WCPM\Classes\Pixels\Trait_Shop;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Google_Analytics extends Google {
	use Trait_Shop;

	public function __construct( $options ) {
		parent::__construct($options);

		$this->pixel_name = 'google_analytics';
	}
}
