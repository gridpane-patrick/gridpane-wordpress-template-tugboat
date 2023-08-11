<?php

/**
 * Plugin Name: MyFatoorah - WooCommerce
 * Plugin URI: https://myfatoorah.readme.io/docs/woocommerce
 * Description: MyFatoorah Payment Gateway for WooCommerce. Integrated with MyFatoorah DHL/Aramex Shipping Methods
 * Version: 2.2.2
 * Tested up to: 6.2.2
 * Author: MyFatoorah
 * Author URI: https://www.myfatoorah.com/
 * 
 * Text Domain: myfatoorah-woocommerce
 * Domain Path: /i18n/languages/
 *
 * WC requires at least: 4.0
 * WC tested up to: 7.7.0
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * @package MyFatoorah
 */
if (!defined('ABSPATH')) {
    exit;
}

//MFWOO_PLUGIN
define('MYFATOORAH_WOO_PLUGIN_VERSION', '2.2.2');
define('MYFATOORAH_WOO_PLUGIN', plugin_basename(__FILE__));
define('MYFATOORAH_WOO_PLUGIN_NAME', dirname(MYFATOORAH_WOO_PLUGIN));
define('MYFATOORAH_WOO_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * MyFatoorah WooCommerce Class
 */
class MyfatoorahWoocommerce {
//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct() {

        add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);

        //actions
        add_action('activate_plugin', array($this, 'superessActivate'), 0);
        add_action('plugins_loaded', array($this, 'init'), 0);
        add_action('in_plugin_update_message-' . MYFATOORAH_WOO_PLUGIN, array($this, 'prefix_plugin_update_message'), 10, 2);
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Show row meta on the plugin screen.
     *
     * @param mixed $links Plugin Row Meta.
     * @param mixed $file  Plugin Base file.
     *
     * @return array
     */
    public static function plugin_row_meta($links, $file) {

        if (MYFATOORAH_WOO_PLUGIN === $file) {
            $row_meta = array(
                'docs'    => '<a href="' . esc_url('https://myfatoorah.readme.io/docs/woocommerce') . '" aria-label="' . esc_attr__('View MyFatoorah documentation', 'myfatoorah-woocommerce') . '">' . esc_html__('Docs', 'woocommerce') . '</a>',
                'apidocs' => '<a href="' . esc_url('https://myfatoorah.readme.io/docs') . '" aria-label="' . esc_attr__('View MyFatoorah API docs', 'myfatoorah-woocommerce') . '">' . esc_html__('API docs', 'woocommerce') . '</a>',
                'support' => '<a href="' . esc_url('https://myfatoorah.com/contact.html') . '" aria-label="' . esc_attr__('Visit premium customer support', 'myfatoorah-woocommerce') . '">' . esc_html__('Premium support', 'woocommerce') . '</a>',
            );

            //unset($links[2]);
            return array_merge($links, $row_meta);
        }

        return (array) $links;
    }

//-----------------------------------------------------------------------------------------------------------------------------
    function superessActivate($plugin) {

        // Localisation
        $arTrans = 'myfatoorah-woocommerce-ar';
        if (is_dir(WP_LANG_DIR . '/plugins/')) {
            $filePath = WP_LANG_DIR . '/plugins/' . $arTrans;
            $moFileAr = $filePath . '.mo';
            $poFileAr = $filePath . '.po';

            $newFilePath = MYFATOORAH_WOO_PLUGIN_PATH . 'i18n/languages/' . $arTrans;
            $moNewFileAr = $newFilePath . '.mo';
            $poNewFileAr = $newFilePath . '.po';

            copy($moNewFileAr, $moFileAr);
            copy($poNewFileAr, $poFileAr);
        }
        //it is very important to say that the plugin is MyFatoorah 
        if ($plugin == MYFATOORAH_WOO_PLUGIN && !in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) && !array_key_exists('woocommerce/woocommerce.php', apply_filters('active_plugins', get_site_option('active_sitewide_plugins')))) {
            wp_die(__('WooCommerce plugin needs to be activated first to activate MyFatoorah plugin', 'myfatoorah-woocommerce'));
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Init localizations and files
     */
    public function init() {
        // Localisation
        load_plugin_textdomain('myfatoorah-woocommerce', false, MYFATOORAH_WOO_PLUGIN_NAME . '/i18n/languages');
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Show important release note
     * @param type $data
     * @param type $response
     */
    function prefix_plugin_update_message($data, $response) {

        $notice = null;
        if (!empty($data['upgrade_notice'])) {
            $notice = trim(strip_tags($data['upgrade_notice']));
        } else if (!empty($response->upgrade_notice)) {
            $notice = trim(strip_tags($response->upgrade_notice));
        }

        if (!empty($notice)) {
            printf(
                    '<div class="update-message notice-error"><p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px"><strong>Important Upgrade Notice: </strong>%s',
                    __($notice, 'myfatoorah-woocommerce')
            );
        }
        //https://andidittrich.com/2015/05/howto-upgrade-notice-for-wordpress-plugins.html
    }

//-----------------------------------------------------------------------------------------------------------------------------
}

new MyfatoorahWoocommerce();

//load payment
if (!class_exists('MyfatoorahWoocommercePayment')) {
    require_once 'payment.php';
    new MyfatoorahWoocommercePayment('v2');
    new MyfatoorahWoocommercePayment('embedded');
}

//load shipping
if (!class_exists('MyfatoorahWoocommerceShipping')) {
    require_once 'shipping.php';
    new MyfatoorahWoocommerceShipping();
}

//load webhook
if (!class_exists('MyfatoorahWoocommerceWebhook')) {
    require_once 'webhook.php';
    new MyfatoorahWoocommerceWebhook();
}
