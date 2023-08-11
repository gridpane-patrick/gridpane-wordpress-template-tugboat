<?php

use TierPricingTable\TierPricingTablePlugin;

/**
 *
 * Plugin Name:       Tiered Price Table for WooCommerce
 * Description:       Allows you to set price for a certain quantity of product. Show a table with a pricing policy on a product page. Simple and powerful.
 * Version:           4.4.0
 * Author:            Kolya Lukin
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tier-pricing-table
 * Domain Path:       /languages/
 *
 * WC requires at least: 4.0
 * WC tested up to: 6.2
 *
 * Woo: 4688341:4df6277d69a5a71a9489359f4adca64a
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

call_user_func( function () {

	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

	$main = new TierPricingTablePlugin( __FILE__ );

	register_activation_hook( __FILE__, array( $main, 'activate' ) );

	register_deactivation_hook( __FILE__, array( $main, 'deactivate' ) );

	add_action( 'uninstall', array( TierPricingTablePlugin::class, 'uninstall' ) );

	$main->run();
} );
