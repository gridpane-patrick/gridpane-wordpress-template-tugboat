<?php
/**
 * WooCommerce Store Credit Uninstall
 *
 * Deletes the plugin options.
 *
 * @package WC_Store_Credit/Uninstaller
 * @version 3.6.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Plugin uninstall script.
 *
 * @since 3.6.0
 *
 * @global wpdb $wpdb The WordPress Database Access Abstraction Object.
 */
function wc_store_credit_uninstall() {
	global $wpdb;

	/*
	 * Only remove ALL the plugin data if WC_REMOVE_ALL_DATA constant is set to true in the wp-config.php file.
	 * This is to prevent data loss when deleting the plugin from the backend
	 * and to ensure only the site owner can perform this action.
	 */
	if ( defined( 'WC_REMOVE_ALL_DATA' ) && true === WC_REMOVE_ALL_DATA ) {
		// Delete options.
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wc_store_credit_%';" );
	}
}
wc_store_credit_uninstall();
