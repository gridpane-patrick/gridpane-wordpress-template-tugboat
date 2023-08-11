<?php
/**
 * Split Orders uninstall
 *
 * Uninstalling deletes all options.
 */

// Exit if not called from WordPress
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Delete all options
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_vibe_split_orders_%';" );
