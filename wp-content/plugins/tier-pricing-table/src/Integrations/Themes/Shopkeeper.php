<?php namespace TierPricingTable\Integrations\Themes;

class Shopkeeper {

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
				.price-rules-table {
					border-collapse: collapse !important;
					padding-left: 10px;
				}
			</style>

			<script>
				function tieredPriceTableGetProductPriceContainer() {
					return jQuery('form.cart').closest('.product_infos').find('[data-tiered-price-wrapper]');
				}
			</script>
			<?php
		} );
	}
}
