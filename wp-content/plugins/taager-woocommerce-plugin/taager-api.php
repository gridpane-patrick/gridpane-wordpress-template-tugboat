<?php

/**
 * Plugin Name: Taager API
 * Description: A plugin to manage Woocommerce products, orders with Taager.
 * Author: Taager
 * Version: 2.1
 **/

defined('ABSPATH') || exit;


include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (!is_plugin_active('woocommerce/woocommerce.php')) {

    add_action('admin_notices', function () {
?>
        <div class="error notice">
            <p><?php _e('The WooCommerce plugin must be installed in order to use the Taager Plugin !', 'my_plugin_textdomain'); ?></p>
        </div>
<?php
    });
}
if (is_plugin_active('woocommerce/woocommerce.php')) {
    if (!class_exists('Featured_Image_By_URL')) :
        require_once plugin_dir_path(__FILE__) . 'featured-image-by-url/featured-image-by-url.php';
    endif;
    require_once plugin_dir_path(__FILE__) . 'includes/ta-admin-functions.php';
    require_once plugin_dir_path(__FILE__) . 'includes/ta-functions.php';
    require_once plugin_dir_path(__FILE__) . 'includes/ta-checkbox.php';
    if (defined('WAAS1_WP_HOME')) {
        require_once plugin_dir_path(__FILE__) . 'includes/ta-scheduled-tasks.php';
    }
    require_once plugin_dir_path(__FILE__) . 'includes/ta-products-import.php';
    require_once plugin_dir_path(__FILE__) . 'includes/ta-color-variants-mapper.php';
    require_once plugin_dir_path(__FILE__) . 'includes/pages/ta-account.php';
    require_once plugin_dir_path(__FILE__) . 'includes/pages/ta-country-selection.php';
    require_once plugin_dir_path(__FILE__) . 'includes/pages/ta-products-import-page.php';
}

global $wpdb;

/**
 * Clear cron event when deactivate this plugin
 */
function my_plugin_activate()
{
    if (defined('WAAS1_WP_HOME')) {

        if (!wp_next_scheduled('ta_hourly_update_hook')) {
            wp_schedule_event(current_time('timestamp'), 'every_one_hour', 'ta_hourly_update_hook');
        }
    }
    /* activation code here */
}
register_activation_hook(__FILE__, 'my_plugin_activate');

register_deactivation_hook(__FILE__, 'clear_cron_event');
function clear_cron_event()
{
    // $last_category_filter = get_option('ta_last_category_filter');
    // $last_name_filter = get_option('ta_last_name_filter');
    // $args = array($last_category_filter, $last_name_filter);

    // wp_clear_scheduled_hook('taager_import_products', $args);
    wp_clear_scheduled_hook('ta_hourly_update_hook');

    // $plugin_data = get_plugin_data(__FILE__);
    // $plugin_version = $plugin_data['Version'];


}
