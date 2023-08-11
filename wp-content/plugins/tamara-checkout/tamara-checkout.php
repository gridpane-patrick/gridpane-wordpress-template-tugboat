<?php
/**
 * Plugin Name: Tamara Checkout
 * Plugin URI:  https://tamara.co/
 * Description: This plugin enables Tamara Buy Now Pay later on your WooCommerce store. With Tamara, you can split your payments – totally interest-free. Accepts payments from Mada, Apple Pay, or Credit Cards.
 * Author:      dev@tamara.co
 * Author URI:  https://tamara.co/
 * Version:     1.9.3
 * Text Domain: tamara
 */

use Tamara\Wp\Plugin\TamaraCheckout;

defined('TAMARA_CHECKOUT_VERSION') || define('TAMARA_CHECKOUT_VERSION', '1.9.3');

// Use autoload if it isn't loaded before
// phpcs:ignore PSR2.ControlStructures.ControlStructureSpacing.SpacingAfterOpenBrace
if ( ! class_exists(TamaraCheckout::class)) {
    require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
}

$config = require(__DIR__ . DIRECTORY_SEPARATOR . 'config.php');
$config = array_merge($config, [
    'pluginFilename' => __FILE__,
]);

// We need to set up the main instance for the plugin.
// Use 'woocommerce_init' to execute after WC init.
add_action('woocommerce_init', function () use ($config) {
    TamaraCheckout::initInstanceWithConfig($config);

    register_activation_hook(__FILE__, [TamaraCheckout::getInstance(), 'activatePlugin']);
    register_deactivation_hook(__FILE__, [TamaraCheckout::getInstance(), 'deactivatePlugin']);

    TamaraCheckout::getInstance()->initPlugin();
});
