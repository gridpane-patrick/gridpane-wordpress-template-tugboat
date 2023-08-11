<?php
/**
 * Taager API Uninstall
 *
 * Uninstalling Taager API deletes province table.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Remove Options for Taager API
delete_option( 'ta_user' );
delete_option( 'ta_api_username' );
delete_option( 'ta_api_password' );
delete_option( 'ta_initial_status' );
delete_option( 'ta_product_import_status' );
delete_option( 'ta_last_category_filter' );
delete_option( 'ta_last_name_filter' );

// $table_full_name = $wpdb->prefix . 'taager_provinces';

// $sql = "DROP TABLE IF EXISTS $table_full_name";
// $wpdb->query($sql);

// Clear any cached data that has been removed.
wp_cache_flush();