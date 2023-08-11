<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              piwebsolution.com
 * @since             1.3.9.27
 * @package           Pi_Dcw
 *
 * @wordpress-plugin
 * Plugin Name:       Direct checkout, WooCommerce Single page checkout , WooCommerce One page checkout - PRO
 * Plugin URI:        https://www.piwebsolution.com/product/add-to-cart-direct-checkout-for-woocommerce-pro/
 * Description:       WooCommerce single page checkout, lets you show cart and checkout option on single page, that is one page checkout for WooCommerce, along with it you can redirect user directly to checkout as they click add to cart
 * Version:           1.3.9.27
 * Author:            PI Websolution
 * Author URI:        https://www.piwebsolution.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pi-dcw
 * Domain Path:       /languages
 * WC tested up to: 6.5.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * Checking Pro version
 */
function pi_dcw_free_check(){
	if(is_plugin_active( 'add-to-cart-direct-checkout-for-woocommerce/pi-dcw.php')){
		return true;
	}
	return false;
}

if(pi_dcw_free_check()){
	/** if free version is then deactivate the pro version */
    function pi_dcw_free_error_notice() {
        ?>
        <div class="error notice">
            <p><?php _e( 'Please Un-Install or deactivate the FREE version of Add to cart direct redirect plugin', 'pi-dcw' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'pi_dcw_free_error_notice' );
    deactivate_plugins(plugin_basename(__FILE__));
    return;
}else{

/** check woocommerce */
if(!is_plugin_active( 'woocommerce/woocommerce.php')){
    function pi_dcw_my_error_notice() {
        ?>
        <div class="error notice">
            <p><?php _e( 'Please Install and Activate WooCommerce plugin, without that this plugin cant work', 'pi-dcw' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'pi_dcw_my_error_notice' );
    deactivate_plugins(plugin_basename(__FILE__));
    return;
}

function pisol_dcw_get_transient($key){
    $caching_enabled = get_option('pi_dcw_enable_caching', 0);

    if(empty($caching_enabled)) return false;

    $slug = 'pisol_dcw_cache_'.$key;
    $val = get_transient($slug);
    return $val;
}

function pisol_dcw_set_transient($key, $value){
    $caching_enabled = get_option('pi_dcw_enable_caching', 0);
    if(empty($caching_enabled)) return false;

    $expiry = (int)get_option('pisol_dcw_cache_expiry', 30);
    
    $expiry_seconds = 60 *  $expiry;
    $slug = 'pisol_dcw_cache_'.$key;
    return  set_transient($slug, $value, $expiry_seconds);
}

/* buy link and buy price */
define('PI_DCW_BUY_URL', 'https://www.piwebsolution.com/cart/?add-to-cart=1015');
define('PI_DCW_PRICE', '$15');

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PI_DCW_VERSION', '1.3.9.27' );
define( 'PISOL_DCW_DELETE_SETTING', false);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pi-dcw-activator.php
 */
function activate_pi_dcw() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pi-dcw-activator.php';
	Pi_Dcw_Activator::activate();
}

/**
 * Register language file
 */
if ( ! function_exists( 'pisol_dcw_plugins_loaded' ) ) {
    function pisol_dcw_plugins_loaded() {
        load_plugin_textdomain( 'pi-dcw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
    add_action( 'init', 'pisol_dcw_plugins_loaded', 10000 );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pi-dcw-deactivator.php
 */
function deactivate_pi_dcw() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pi-dcw-deactivator.php';
	Pi_Dcw_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pi_dcw' );
register_deactivation_hook( __FILE__, 'deactivate_pi_dcw' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pi-dcw.php';

add_action('init', 'pisol_dcw_direct_checkout_update_checking');
function pisol_dcw_direct_checkout_update_checking()
{
    new pisol_update_notification_v1(plugin_basename(__FILE__), PI_DCW_VERSION);
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pi_dcw() {

	$plugin = new Pi_Dcw();
	$plugin->run();

}
run_pi_dcw();

}