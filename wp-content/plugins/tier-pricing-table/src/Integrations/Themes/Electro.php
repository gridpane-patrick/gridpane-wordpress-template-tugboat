<?php namespace TierPricingTable\Integrations\Themes;

class Electro {

	public function __construct() {
		add_action( 'wp_head', function () {
			?>
			<script>
				function tieredPriceTableGetProductPriceContainer() {
					return jQuery('form.cart').closest('.product-actions').find('[data-tiered-price-wrapper]');
				}
			</script>
			<?php
		} );
	}
}
