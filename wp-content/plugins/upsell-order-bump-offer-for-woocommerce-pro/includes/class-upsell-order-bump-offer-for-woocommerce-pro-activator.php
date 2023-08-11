<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://wpswings.com/?utm_source=wpswings-official&utm_medium=order-bump-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    Upsell-Order-Bump-Offer-For-Woocommerce-Pro
 * @subpackage    Upsell-Order-Bump-Offer-For-Woocommerce-Pro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Upsell-Order-Bump-Offer-For-Woocommerce-Pro
 * @subpackage    Upsell-Order-Bump-Offer-For-Woocommerce-Pro/includes
 * @author     WP Swings<webmaster@wpswings.com>
 */
class Upsell_Order_Bump_Offer_For_Woocommerce_Pro_Activator {

	/**
	 * Just set a transient to get tabs operative.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Set default timestamp.
		$timestamp = get_option( 'wps_upsell_bump_activated_timestamp', 'not_set' );

		if ( 'not_set' === $timestamp ) {

			$current_time = time();

			$thirty_days = strtotime( '+30 days', $current_time );

			update_option( 'wps_upsell_bump_activated_timestamp', $thirty_days );
		}
	}
}
