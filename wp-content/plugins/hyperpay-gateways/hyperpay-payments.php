<?php

/**
 * Plugin Name: HyperPay Payments
 * Description: Hyperpay is the first one stop-shop service company for online merchants in MENA Region.<strong>If you have any question, please <a href="http://www.hyperpay.com/" target="_new">contact Hyperpay</a>.</strong>
 * Version:     2.3.3
 * Text Domain: hyperpay-payments
 * Domain Path: /languages
 * Author:      Hyperpay Team
 * Author URI:  https://www.hyperpay.com
 * Requires at least: 5.3
 * Requires PHP: 7.1
 * WC requires at least: 3.0.9
 * WC tested up to: 6.2.1
 * 
 */


if (!function_exists('add_settings_error')) {
    require_once ABSPATH . '/wp-admin/includes/template.php';
}


if (!defined('HYPERPAY_PLUGIN_FILE')) {
    define('HYPERPAY_PLUGIN_FILE', __FILE__);
}

if (!defined('HYPERPAY_PLUGIN_DIR')) {

    define('HYPERPAY_PLUGIN_DIR', untrailingslashit(plugins_url('/', HYPERPAY_PLUGIN_FILE)));
}

if (!defined('HYPERPAY_ABSPATH')) {
    define('HYPERPAY_ABSPATH', dirname(HYPERPAY_PLUGIN_FILE) . '/');
}

if (!class_exists('hyperpay_main', false)) {

    include_once dirname(HYPERPAY_PLUGIN_FILE) . '/includes/class-install.php';

}


include_once dirname(HYPERPAY_PLUGIN_FILE) . '/includes/class-wp-webhook.php';


/**
 * load styles
 */
add_action( 'wp_enqueue_scripts', 'hyperpay_load_styles' );

function hyperpay_load_styles(){
    
    // wp_enqueue_style('hyperpay_custom_style', HYPERPAY_PLUGIN_DIR . '/assets/css/style.css');
    
    // if (substr( get_locale(),0 ,2) == 'ar')
    //     wp_enqueue_style('hyperpay_custom_style', HYPERPAY_PLUGIN_DIR . '/assets/css/style-rtl.css');
}


/**
 * Initialize the plugin and its modules.
 */

add_action('plugins_loaded',  ['hyperpay_main', 'load']);


/*
* Load plugin textdomain.
*/
function hyperpay_plugin_load_textdomain() {

load_plugin_textdomain( 'hyperpay-payments', false, basename( dirname( __FILE__ ) ) . '/languages' );
wp_enqueue_style('hyperpay_custom_style', HYPERPAY_PLUGIN_DIR . '/assets/css/style.css' , [] , '4');
    
if (is_rtl())
    wp_enqueue_style('hyperpay_custom_style_ar', HYPERPAY_PLUGIN_DIR . '/assets/css/style-rtl.css');
}

add_action( 'init', 'hyperpay_plugin_load_textdomain' );

add_action('woocommerce_order_actions',  'capture_payment', 10, 2 );


function capture_payment( $actions , $order ) {
    $is_pre_authorization = $order->get_meta( 'is_pre_authorization');
    if ( is_array( $actions ) && $is_pre_authorization) {
        $actions['capture_payment'] = __( 'Capture Pre Authorization','hyperpay-payments') ;
    }
    return $actions;
}