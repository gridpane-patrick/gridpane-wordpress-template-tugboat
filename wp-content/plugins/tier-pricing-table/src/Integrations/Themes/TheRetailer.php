<?php namespace TierPricingTable\Integrations\Themes;

class TheRetailer {

	public function __construct() {
		add_action( 'wp_head', function () {
			?>

			<style>
				.price-rules-table tbody td {
					padding: 10px !important;
				}

				.price-rules-table th {
					padding-left: 10px !important;
				}
			</style>

			<?php
		} );
	}
}
