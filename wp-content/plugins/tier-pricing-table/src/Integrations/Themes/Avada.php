<?php namespace TierPricingTable\Integrations\Themes;

class Avada {

	public function __construct() {
		add_action( 'wp_head', function () {
			?>
			<style>
				.price-rules-table tbody tr {
					height: inherit;
				}

				.price-rules-table tbody td {
					padding: 15px 0 15px 10px;
				}

				.price-rules-table th {
					padding-left: 10px;
				}
			</style>
			<?php
		} );
	}
}
