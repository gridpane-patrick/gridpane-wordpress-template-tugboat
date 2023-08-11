<?php namespace TierPricingTable\Integrations\Themes;

class Astra {

	public function __construct() {

		add_action( 'wp_head', function () {
			?>
			<style>
				.price-rules-table tbody td {
					padding-left: 15px !important;
				}
			</style>
			<?php
		} );
	}
}
