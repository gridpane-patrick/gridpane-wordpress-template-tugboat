<?php
/**
 * The admin-specific template of the plugin for license activation.
 *
 * @link       https://wpswings.com/?utm_source=wpswings-official&utm_medium=order-bump-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    Upsell-Order-Bump-Offer-For-Woocommerce-Pro
 * @subpackage    Upsell-Order-Bump-Offer-For-Woocommerce-Pro/admin/partials/templates
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;
}
?>

<!-- License panel html. -->
<div class="wps-upsell-bump-wrap">
	<h1 class="wps_upsell_offer_sections"><?php esc_html_e( 'Your License', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></h1  >
	<div class="wps_upsell_bump_license_text">

		<p>
		<?php
			esc_html_e( 'This is the License Activation Panel. After purchasing extension from WP Swings you will get the purchase code of this extension. Please verify your purchase below so that you can use feature of this plugin.', 'upsell-order-bump-offer-for-woocommerce-pro' );
		?>
		</p>

		<form id="wps_upsell_bump_license_form"> 
			<table class="wps-upsell-bump-pro-form-table">
				<div id="wps_upsell_bump_license_ajax_loader"><img src="images/spinner-2x.gif"></div>
				<tr>
					<th scope="row"><label for="puchase-code"><?php esc_html_e( 'Purchase Code : ', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></label></th>
					<td>
						<input type="text" id="wps_bump_offer_license_key" name="purchase-code" required="" size="30" class="wps-upsell-bump-pro-purchase-code" value="" placeholder="<?php esc_html_e( 'Enter your code here...', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>">
					</td>
				</tr>
			</table>

			<!-- Notify Here. -->
			<p id="wps_upsell_bump_offer_license_activation_status"></p>
			<p class="submit">
				<button id="wps_upsell_bump_license_activate" required="" class="button-primary woocommerce-save-button" name="wps_bump_offer_license_settings"><?php esc_html_e( 'Validate', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></button>
			</p>
		</form>

	</div>
</div>
