<?php
/**
 * Updater for 1.0.2.
 *
 * @package WC_Shipping_Aramex
 */

/**
 * Update routine for 1.0.2 where shipping zone support is initially supported.
 */
class WC_Shipping_Aramex_Updater_1_0_2 implements WC_Shipping_Aramex_Updater {
	/**
	 * {@inheritdoc}
	 */
	public function update() {
		// If Aramex instance exists in "rest of the world" zone then nothing
		// to update.
		if ( $this->is_zone_has_aramex( 0 ) ) {
			return false;
		}

		$aramex_settings = get_option( 'woocommerce_wc_shipping_aramex_settings' );

		// No settings saved in the DB yet, so skip updating.
		if ( ! $aramex_settings ) {
			return false;
		}

		// Unset un-needed settings.
		unset( $aramex_settings['enabled'] );
		unset( $aramex_settings['availability'] );
		unset( $aramex_settings['countries'] );

		global $wpdb;

		$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}woocommerce_shipping_zone_methods ( zone_id, method_id, method_order, is_enabled  ) VALUES ( %d, %s, %d, %d  )", 0, 'wc_shipping_aramex', 1, 1 ) );

		// Add settings to the newly created instance to options table.
		add_option( 'woocommerce_wc_shipping_aramex_' . $wpdb->insert_id . '_settings', $aramex_settings );

		return true;
	}

	/**
	 * Helper method to check whether given zone_id has Aramex method instance.
	 *
	 * @since 1.0.2
	 *
	 * @param int $zone_id Zone ID.
	 *
	 * @return bool True if given zone_id has ups method instance
	 */
	public function is_zone_has_aramex( $zone_id ) {
		global $wpdb;

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(instance_id) FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE method_id = 'wc_shipping_aramex' AND zone_id = %d", $zone_id ) ) > 0;
	}
}
