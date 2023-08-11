<?php

class WOOMULTI_CURRENCY_Plugin_Product_Add_On_Ultimate {
	public function __construct() {
		add_filter( 'pewc_filter_field_price', [ $this, 'field_price_convert' ] );
		add_filter( 'pewc_filter_option_price', [ $this, 'field_price_convert' ] );
		add_filter( 'pewc_price_with_extras_before_calc_totals', [ $this, 'revert_set_price' ] );
	}

	public function field_price_convert( $amount ) {
		return wmc_get_price( $amount );
	}

	public function revert_set_price( $price ) {
		return wmc_revert_price( $price );
	}
}