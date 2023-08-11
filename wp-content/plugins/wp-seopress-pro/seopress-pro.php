<?php
/*
Plugin Name: SEOPress PRO
Plugin URI: https://www.seopress.org/seopress-pro/
Description: The PRO version of SEOPress. SEOPress required (free).
Version: 5.7.2
Author: SEOPress
Author URI: https://www.seopress.org/seopress-pro/
License: GPLv2
Text Domain: wp-seopress-pro
Domain Path: /languages
*/

/*  Copyright 2016 - 2022 - Benjamin Denis  (email : contact@seopress.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// To prevent calling the plugin directly
if ( ! function_exists('add_action')) {
    echo 'Please don&rsquo;t call the plugin directly. Thanks :)';
    exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//CRON
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_pro_cron() {
    //CRON - 404 cleaning
    if ( ! wp_next_scheduled('seopress_404_cron_cleaning')) {
        wp_schedule_event(time(), 'daily', 'seopress_404_cron_cleaning');
    }

    //CRON - GA stats in dashboard
    if ( ! wp_next_scheduled('seopress_google_analytics_cron')) {
        wp_schedule_event(time(), 'hourly', 'seopress_google_analytics_cron');
    }

    //CRON - Page Speed Insights
    if ( ! wp_next_scheduled('seopress_page_speed_insights_cron')) {
        wp_schedule_event(time(), 'daily', 'seopress_page_speed_insights_cron');
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Hooks activation
///////////////////////////////////////////////////////////////////////////////////////////////////
// Deactivate SEOPress PRO if the Free version is not activated/installed
//@since version 3.8.1
function seopress_pro_loaded() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    if ( ! function_exists('deactivate_plugins')) {
        return;
    }

    if ( ! is_plugin_active('wp-seopress/seopress.php')) {//if SEOPress Free NOT activated
        deactivate_plugins('wp-seopress-pro/seopress-pro.php');
        add_action('admin_notices', 'seopress_pro_notice');
    }
}
add_action('plugins_loaded', 'seopress_pro_loaded');

function seopress_pro_activation() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    if ( ! function_exists('activate_plugins')) {
        return;
    }

    if ( ! function_exists('get_plugins')) {
        return;
    }

    $plugins = get_plugins();
    if ( ! empty($plugins['wp-seopress/seopress.php'])) {//if SEOPress Free is installed
        if ( ! is_plugin_active('wp-seopress/seopress.php')) {//if SEOPress Free is not activated
            activate_plugins('wp-seopress/seopress.php');
        }
        add_option('seopress_pro_activated', 'yes');

        flush_rewrite_rules(false);

        seopress_pro_cron();
    }

    //Add Redirections caps to user with "manage_options" capability
    $roles = get_editable_roles();
    if ( ! empty($roles)) {
        foreach ($GLOBALS['wp_roles']->role_objects as $key => $role) {
            if (isset($roles[$key]) && $role->has_cap('manage_options')) {
                $role->add_cap('edit_redirection');
                $role->add_cap('edit_redirections');
                $role->add_cap('edit_others_redirections');
                $role->add_cap('publish_redirections');
                $role->add_cap('read_redirection');
                $role->add_cap('read_private_redirections');
                $role->add_cap('delete_redirection');
                $role->add_cap('delete_redirections');
                $role->add_cap('delete_others_redirections');
                $role->add_cap('delete_published_redirections');
            }
            if (isset($roles[$key]) && $role->has_cap('manage_options')) {
                $role->add_cap('edit_schema');
                $role->add_cap('edit_schemas');
                $role->add_cap('edit_others_schemas');
                $role->add_cap('publish_schemas');
                $role->add_cap('read_schema');
                $role->add_cap('read_private_schemas');
                $role->add_cap('delete_schema');
                $role->add_cap('delete_schemas');
                $role->add_cap('delete_others_schemas');
                $role->add_cap('delete_published_schemas');
            }
        }
    }

    do_action('seopress_pro_activation');
}
register_activation_hook(__FILE__, 'seopress_pro_activation');

function seopress_pro_deactivation() {
    delete_option('seopress_pro_activated');
    flush_rewrite_rules(false);
    wp_clear_scheduled_hook('seopress_404_cron_cleaning');
    wp_clear_scheduled_hook('seopress_google_analytics_cron');
    wp_clear_scheduled_hook('seopress_page_speed_insights_cron');
    do_action('seopress_pro_deactivation');
}
register_deactivation_hook(__FILE__, 'seopress_pro_deactivation');

/**
 * Hooks uninstall.
 *
 * @since 4.2
 *
 * @author Benjamin
 */
function seopress_pro_uninstall() {
    //Remove CRON
    wp_clear_scheduled_hook('seopress_404_cron_cleaning');
    wp_clear_scheduled_hook('seopress_google_analytics_cron');
    wp_clear_scheduled_hook('seopress_page_speed_insights_cron');
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Define
///////////////////////////////////////////////////////////////////////////////////////////////////
define('SEOPRESS_PRO_VERSION', '5.7.2');
define('SEOPRESS_PRO_AUTHOR', 'Benjamin Denis');
define('STORE_URL_SEOPRESS', 'https://www.seopress.org');
define('ITEM_ID_SEOPRESS', 113);
define('ITEM_NAME_SEOPRESS', 'SEOPress PRO');
define('SEOPRESS_LICENSE_PAGE', 'seopress-license');
define('SEOPRESS_PRO_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
define('SEOPRESS_PRO_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define('SEOPRESS_PRO_ASSETS_DIR', SEOPRESS_PRO_PLUGIN_DIR_URL . 'assets');
define('SEOPRESS_PRO_TEMPLATE_DIR', SEOPRESS_PRO_PLUGIN_DIR_PATH . 'templates');
define('SEOPRESS_PRO_TEMPLATE_JSON_SCHEMAS', SEOPRESS_PRO_TEMPLATE_DIR . '/json-schemas');
define('SEOPRESS_PRO_TEMPLATE_STOP_WORDS', SEOPRESS_PRO_TEMPLATE_DIR . '/stop-words');

use SEOPressPro\Core\Kernel;

require_once __DIR__ . '/seopress-autoload.php';

if (file_exists(__DIR__ . '/vendor/autoload.php') && file_exists(WP_PLUGIN_DIR . '/wp-seopress/seopress-autoload.php')) {
    require_once WP_PLUGIN_DIR . '/wp-seopress/seopress-autoload.php';
    require_once __DIR__ . '/seopress-pro-functions.php';
    require_once __DIR__ . '/inc/admin/cron.php';

    $versions = get_option('seopress_versions');
    $versionFree = isset($versions['free']) ? $versions['free'] : 0;
    if ('5.7.2' !== $versionFree && version_compare($versionFree, '4.5.1', '<=')) {
        return;
    }

    Kernel::execute([
        'file' => __FILE__,
        'slug' => 'wp-seopress-pro',
        'main_file' => 'seopress-pro',
        'root' => __DIR__,
    ]);
}

function seopress_rich_snippets_publisher_logo_option() {
    $seopress_rich_snippets_publisher_logo_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_rich_snippets_publisher_logo_option)) {
        foreach ($seopress_rich_snippets_publisher_logo_option as $key => $seopress_rich_snippets_publisher_logo_value) {
            $options[$key] = $seopress_rich_snippets_publisher_logo_value;
        }
        if (isset($seopress_rich_snippets_publisher_logo_option['seopress_rich_snippets_publisher_logo'])) {
            return $seopress_rich_snippets_publisher_logo_option['seopress_rich_snippets_publisher_logo'];
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//SEOPRESS PRO INIT
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_pro_init() {
    //CRON
    seopress_pro_cron();

    //i18n
    load_plugin_textdomain('wp-seopress-pro', false, dirname(plugin_basename(__FILE__)) . '/languages/');

    global $pagenow;

    if ( ! function_exists('seopress_capability')) {
        return;
    }

    if (is_admin() || is_network_admin()) {
        require_once dirname(__FILE__) . '/inc/admin/admin.php';
        require_once dirname(__FILE__) . '/inc/admin/ajax.php';
        if ('post-new.php' == $pagenow || 'post.php' == $pagenow) {
            require_once dirname(__FILE__) . '/inc/admin/metaboxes/admin-metaboxes.php';
        }
        global $pagenow;
        if ('index.php' == $pagenow) {
            require_once dirname(__FILE__) . '/inc/admin/dashboard-google-analytics.php';
        }

        //CSV Import
        include_once dirname(__FILE__) . '/inc/admin/import/class-csv-wizard.php';

        //Bot
        require_once dirname(__FILE__) . '/inc/admin/bot.php';
        require_once dirname(__FILE__) . '/inc/functions/bot/seopress-bot.php';
    }

    // Watchers
    require_once dirname(__FILE__) . '/inc/admin/watchers/index.php';

    require_once dirname(__FILE__) . '/inc/admin/redirections.php';
    require_once dirname(__FILE__) . '/inc/functions/options.php';

    //Elementor
    if (did_action('elementor/loaded')) {
        require_once dirname(__FILE__) . '/inc/admin/elementor/elementor.php';
    }

    //TranslationsPress
    if ( ! class_exists('SEOPRESS_Language_Packs')) {
        if (is_admin() || is_network_admin()) {
            require_once dirname(__FILE__) . '/inc/admin/updater/t15s-registry.php';
        }
    }

    // Blocks registration
    require_once dirname(__FILE__) . '/inc/functions/blocks.php';
}
add_action('plugins_loaded', 'seopress_pro_init', 999);

///////////////////////////////////////////////////////////////////////////////////////////////////
//TranslationsPress
///////////////////////////////////////////////////////////////////////////////////////////////////

function seopress_init_t15s() {
    if (class_exists('SEOPRESS_Language_Packs')) {
        $t15s_updater = new SEOPRESS_Language_Packs(
            'wp-seopress-pro',
            'https://packages.translationspress.com/seopress/wp-seopress-pro/packages.json'
        );
    }
}
add_action('init', 'seopress_init_t15s');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Check if a feature is ON
///////////////////////////////////////////////////////////////////////////////////////////////////
//Google Data Structured Types metaboxe ON?
function seopress_rich_snippets_enable_option() {
    $seopress_rich_snippets_enable_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_rich_snippets_enable_option)) {
        foreach ($seopress_rich_snippets_enable_option as $key => $seopress_rich_snippets_enable_value) {
            $options[$key] = $seopress_rich_snippets_enable_value;
        }
        if (isset($seopress_rich_snippets_enable_option['seopress_rich_snippets_enable'])) {
            return $seopress_rich_snippets_enable_option['seopress_rich_snippets_enable'];
        }
    }
}

// Is WooCommerce enable?
//@deprecated since version 3.8
function seopress_get_toggle_woocommerce_option() {
    $seopress_get_toggle_woocommerce_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_woocommerce_option)) {
        foreach ($seopress_get_toggle_woocommerce_option as $key => $seopress_get_toggle_woocommerce_value) {
            $options[$key] = $seopress_get_toggle_woocommerce_value;
        }
        if (isset($seopress_get_toggle_woocommerce_option['toggle-woocommerce'])) {
            return $seopress_get_toggle_woocommerce_option['toggle-woocommerce'];
        }
    }
}
// Is EDD enable?
//@deprecated since version 3.8
function seopress_get_toggle_edd_option() {
    $seopress_get_toggle_edd_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_edd_option)) {
        foreach ($seopress_get_toggle_edd_option as $key => $seopress_get_toggle_edd_value) {
            $options[$key] = $seopress_get_toggle_edd_value;
        }
        if (isset($seopress_get_toggle_edd_option['toggle-edd'])) {
            return $seopress_get_toggle_edd_option['toggle-edd'];
        }
    }
}
// Is Local Business enable?
//@deprecated since version 3.8
function seopress_get_toggle_local_business_option() {
    $seopress_get_toggle_local_business_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_local_business_option)) {
        foreach ($seopress_get_toggle_local_business_option as $key => $seopress_get_toggle_local_business_value) {
            $options[$key] = $seopress_get_toggle_local_business_value;
        }
        if (isset($seopress_get_toggle_local_business_option['toggle-local-business'])) {
            return $seopress_get_toggle_local_business_option['toggle-local-business'];
        }
    }
}
// Is Dublin Core enable?
//@deprecated since version 3.8
function seopress_get_toggle_dublin_core_option() {
    $seopress_get_toggle_dublin_core_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_dublin_core_option)) {
        foreach ($seopress_get_toggle_dublin_core_option as $key => $seopress_get_toggle_dublin_core_value) {
            $options[$key] = $seopress_get_toggle_dublin_core_value;
        }
        if (isset($seopress_get_toggle_dublin_core_option['toggle-dublin-core'])) {
            return $seopress_get_toggle_dublin_core_option['toggle-dublin-core'];
        }
    }
}
// Is Rich Snippets enable?
//@deprecated since version 3.8
function seopress_get_toggle_rich_snippets_option() {
    $seopress_get_toggle_rich_snippets_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_rich_snippets_option)) {
        foreach ($seopress_get_toggle_rich_snippets_option as $key => $seopress_get_toggle_rich_snippets_value) {
            $options[$key] = $seopress_get_toggle_rich_snippets_value;
        }
        if (isset($seopress_get_toggle_rich_snippets_option['toggle-rich-snippets'])) {
            return $seopress_get_toggle_rich_snippets_option['toggle-rich-snippets'];
        }
    }
}
// Is Breadcrumbs enable?
//@deprecated since version 3.8
function seopress_get_toggle_breadcrumbs_option() {
    $seopress_get_toggle_breadcrumbs_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_breadcrumbs_option)) {
        foreach ($seopress_get_toggle_breadcrumbs_option as $key => $seopress_get_toggle_breadcrumbs_value) {
            $options[$key] = $seopress_get_toggle_breadcrumbs_value;
        }
        if (isset($seopress_get_toggle_breadcrumbs_option['toggle-breadcrumbs'])) {
            return $seopress_get_toggle_breadcrumbs_option['toggle-breadcrumbs'];
        }
    }
}
// Is Robots enable?
//@deprecated since version 3.8
function seopress_get_toggle_robots_option() {
    $seopress_get_toggle_robots_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_robots_option)) {
        foreach ($seopress_get_toggle_robots_option as $key => $seopress_get_toggle_robots_value) {
            $options[$key] = $seopress_get_toggle_robots_value;
        }
        if (isset($seopress_get_toggle_robots_option['toggle-robots'])) {
            return $seopress_get_toggle_robots_option['toggle-robots'];
        }
    }
}
// Is Google News enable?
//@deprecated since version 3.8
function seopress_get_toggle_news_option() {
    $seopress_get_toggle_news_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_news_option)) {
        foreach ($seopress_get_toggle_news_option as $key => $seopress_get_toggle_news_value) {
            $options[$key] = $seopress_get_toggle_news_value;
        }
        if (isset($seopress_get_toggle_news_option['toggle-news'])) {
            return $seopress_get_toggle_news_option['toggle-news'];
        }
    }
}
// Is 404/301 enable?
//@deprecated since version 3.8
function seopress_get_toggle_404_option() {
    $seopress_get_toggle_404_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_404_option)) {
        foreach ($seopress_get_toggle_404_option as $key => $seopress_get_toggle_404_value) {
            $options[$key] = $seopress_get_toggle_404_value;
        }
        if (isset($seopress_get_toggle_404_option['toggle-404'])) {
            return $seopress_get_toggle_404_option['toggle-404'];
        }
    }
}
// Is Bot enable?
//@deprecated since version 3.8
function seopress_get_toggle_bot_option() {
    $seopress_get_toggle_bot_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_bot_option)) {
        foreach ($seopress_get_toggle_bot_option as $key => $seopress_get_toggle_bot_value) {
            $options[$key] = $seopress_get_toggle_bot_value;
        }
        if (isset($seopress_get_toggle_bot_option['toggle-bot'])) {
            return $seopress_get_toggle_bot_option['toggle-bot'];
        }
    }
}
//Rewrite ON?
//@deprecated since version 3.8
function seopress_get_toggle_rewrite_option() {
    $seopress_get_toggle_rewrite_option = get_option('seopress_toggle');
    if ( ! empty($seopress_get_toggle_rewrite_option)) {
        foreach ($seopress_get_toggle_rewrite_option as $key => $seopress_get_toggle_rewrite_value) {
            $options[$key] = $seopress_get_toggle_rewrite_value;
        }
        if (isset($seopress_get_toggle_rewrite_option['toggle-rewrite'])) {
            return $seopress_get_toggle_rewrite_option['toggle-rewrite'];
        }
    }
}
//White Label?
function seopress_get_toggle_white_label_option() {
    if (is_multisite()) {
        $seopress_toggle = get_blog_option(get_network()->site_id, 'seopress_toggle');
    } else {
        $seopress_toggle = get_option('seopress_toggle');
    }
    $seopress_get_toggle_white_label_option = $seopress_toggle;
    if ( ! empty($seopress_get_toggle_white_label_option)) {
        foreach ($seopress_get_toggle_white_label_option as $key => $seopress_get_toggle_white_label_value) {
            $options[$key] = $seopress_get_toggle_white_label_value;
        }
        if (isset($seopress_get_toggle_white_label_option['toggle-white-label'])) {
            return $seopress_get_toggle_white_label_option['toggle-white-label'];
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Loads the JS/CSS in admin
///////////////////////////////////////////////////////////////////////////////////////////////////
//Google Page Speed Insights
function seopress_pro_admin_ps_scripts() {
    wp_enqueue_script('seopress-page-speed', plugins_url('assets/js/seopress-page-speed.js', __FILE__), ['jquery', 'jquery-ui-accordion'], SEOPRESS_PRO_VERSION, true);

    $seopress_request_page_speed = [
        'seopress_nonce' => wp_create_nonce('seopress_request_page_speed_nonce'),
        'seopress_request_page_speed' => admin_url('admin-ajax.php'),
    ];
    wp_localize_script('seopress-page-speed', 'seopressAjaxRequestPageSpeed', $seopress_request_page_speed);

    $seopress_clear_page_speed_cache = [
        'seopress_nonce' => wp_create_nonce('seopress_clear_page_speed_cache_nonce'),
        'seopress_clear_page_speed_cache' => admin_url('admin-ajax.php'),
    ];
    wp_localize_script('seopress-page-speed', 'seopressAjaxClearPageSpeedCache', $seopress_clear_page_speed_cache);
}

//SEOPRESS PRO Options page
function seopress_pro_add_admin_options_scripts($hook) {
    $prefix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

    wp_register_style('seopress-pro-admin', plugins_url('assets/css/seopress-pro' . $prefix . '.css', __FILE__), [], SEOPRESS_PRO_VERSION);
    wp_enqueue_style('seopress-pro-admin');

    //Dashboard GA
    global $pagenow;
    if ('index.php' == $pagenow) {
        if (function_exists('seopress_google_analytics_dashboard_widget_option') && seopress_google_analytics_dashboard_widget_option() !=='1') {
            wp_register_style('seopress-ga-dashboard-widget', plugins_url('assets/css/seopress-pro-dashboard' . $prefix . '.css', __FILE__), [], SEOPRESS_PRO_VERSION);
            wp_enqueue_style('seopress-ga-dashboard-widget');

            //GA Embed API
            wp_enqueue_script('seopress-pro-ga-embed', plugins_url('assets/js/chart.bundle.min.js', __FILE__), [], SEOPRESS_PRO_VERSION);

            wp_enqueue_script('seopress-pro-ga', plugins_url('assets/js/seopress-pro-ga.js', __FILE__), ['jquery', 'jquery-ui-tabs'], SEOPRESS_PRO_VERSION);

            $seopress_request_google_analytics = [
                'seopress_nonce' => wp_create_nonce('seopress_request_google_analytics_nonce'),
                'seopress_request_google_analytics' => admin_url('admin-ajax.php'),
            ];
            wp_localize_script('seopress-pro-ga', 'seopressAjaxRequestGoogleAnalytics', $seopress_request_google_analytics);
        }
    }

    //Local Business widget
    if ('widgets.php' == $pagenow) {
        wp_enqueue_script('seopress-pro-lb-widget', plugins_url('assets/js/seopress-pro-lb-widget.js', __FILE__), ['jquery', 'jquery-ui-tabs'], SEOPRESS_PRO_VERSION);

        $seopress_pro_lb_widget = [
            'seopress_nonce' => wp_create_nonce('seopress_pro_lb_widget_nonce'),
            'seopress_pro_lb_widget' => admin_url('admin-ajax.php'),
        ];
        wp_localize_script('seopress-pro-lb-widget', 'seopressAjaxLocalBusinessOrder', $seopress_pro_lb_widget);
    }

    //GA tab
    if (isset($_GET['page']) && ('seopress-google-analytics' == $_GET['page'])) {
        wp_enqueue_script('seopress-pro-ga-lock', plugins_url('assets/js/seopress-pro-ga-lock.js', __FILE__), ['jquery'], SEOPRESS_PRO_VERSION, true);

        $seopress_google_analytics_lock = [
            'seopress_nonce' => wp_create_nonce('seopress_google_analytics_lock_nonce'),
            'seopress_google_analytics_lock' => admin_url('admin-ajax.php'),
        ];
        wp_localize_script('seopress-pro-ga-lock', 'seopressAjaxLockGoogleAnalytics', $seopress_google_analytics_lock);
    }

    //Pro Tabs
    if (isset($_GET['page']) && ('seopress-pro-page' == $_GET['page'])) {
        wp_enqueue_script('seopress-pro-admin-tabs-js', plugins_url('assets/js/seopress-pro-tabs.js', __FILE__), ['jquery-ui-tabs'], SEOPRESS_PRO_VERSION);
    }

    if (isset($_GET['page']) && ('seopress-pro-page' == $_GET['page'] || 'seopress-network-option' == $_GET['page'])) {
        //htaccess
        wp_enqueue_script('seopress-save-htaccess', plugins_url('assets/js/seopress-htaccess.js', __FILE__), ['jquery'], SEOPRESS_PRO_VERSION, true);

        $seopress_save_htaccess = [
            'seopress_nonce' => wp_create_nonce('seopress_save_htaccess_nonce'),
            'seopress_save_htaccess' => admin_url('admin-ajax.php'),
        ];
        wp_localize_script('seopress-save-htaccess', 'seopressAjaxSaveHtaccess', $seopress_save_htaccess);

        wp_enqueue_media();
    }

    //Google Page Speed
    if ('edit.php' == $hook) {
        seopress_pro_admin_ps_scripts();
    } elseif (isset($_GET['page']) && ('seopress-pro-page' == $_GET['page'])) {
        seopress_pro_admin_ps_scripts();
    }

    //Bot Tabs
    if (isset($_GET['page']) && ('seopress-bot-batch' == $_GET['page'])) {
        wp_enqueue_script('seopress-bot-admin-tabs-js', plugins_url('assets/js/seopress-bot-tabs.js', __FILE__), ['jquery-ui-tabs'], SEOPRESS_PRO_VERSION);

        $seopress_bot = [
            'seopress_nonce' => wp_create_nonce('seopress_request_bot_nonce'),
            'seopress_request_bot' => admin_url('admin-ajax.php'),
        ];
        wp_localize_script('seopress-bot-admin-tabs-js', 'seopressAjaxBot', $seopress_bot);
    }

    //License
    if (isset($_GET['page']) && ('seopress-license' == $_GET['page'])) {
        wp_enqueue_script('seopress-license', plugins_url('assets/js/seopress-pro-license.js', __FILE__), ['jquery'], SEOPRESS_PRO_VERSION, true);

        $seopress_request_reset_license = [
            'seopress_nonce' => wp_create_nonce('seopress_request_reset_license_nonce'),
            'seopress_request_reset_license' => admin_url('admin-ajax.php'),
        ];
        wp_localize_script('seopress-license', 'seopressAjaxResetLicense', $seopress_request_reset_license);
    }
}

add_action('admin_enqueue_scripts', 'seopress_pro_add_admin_options_scripts', 10, 1);

///////////////////////////////////////////////////////////////////////////////////////////////////
//SEOPress PRO Notices
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_pro_notice() {
    if ( ! is_plugin_active('wp-seopress/seopress.php') && current_user_can('manage_options')) {
        ?>
<div class="notice error">
    <p>
        <?php _e('Please enable <strong>SEOPress</strong> in order to use SEOPress PRO.', 'wp-seopress-pro'); ?>
        <a href="<?php echo esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=wp-seopress&TB_iframe=true&width=600&height=550')); ?>"
            class="thickbox btn btnPrimary" target="_blank"><?php _e('Enable / Download now!', 'wp-seopress-pro'); ?></a>
    </p>
</div>
<?php
    }
}
add_action('admin_notices', 'seopress_pro_notice');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Shortcut settings page
///////////////////////////////////////////////////////////////////////////////////////////////////
add_filter('plugin_action_links', 'seopress_pro_plugin_action_links', 10, 2);

function seopress_pro_plugin_action_links($links, $file) {
    static $this_plugin;

    if ( ! $this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="' . admin_url('admin.php?page=seopress-pro-page') . '">' . __('Settings', 'wp-seopress-pro') . '</a>';

        $website_link = '<a href="https://www.seopress.org/support/" target="_blank">' . __('Support', 'wp-seopress-pro') . '</a>';

        if ('valid' != get_option('seopress_pro_license_status')) {
            $license_link = '<a style="color:red;font-weight:bold" href="' . admin_url('admin.php?page=seopress-license') . '">' . __('Activate your license', 'wp-seopress-pro') . '</a>';
        } else {
            $license_link = '<a href="' . admin_url('admin.php?page=seopress-license') . '">' . __('License', 'wp-seopress-pro') . '</a>';
        }

        if (function_exists('seopress_get_toggle_white_label_option') && '1' == seopress_get_toggle_white_label_option() && function_exists('seopress_white_label_help_links_option') && '1' === seopress_white_label_help_links_option()) {
            array_unshift($links, $settings_link);
        } else {
            array_unshift($links, $settings_link, $website_link, $license_link);
        }
    }

    return $links;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//SEOPress PRO Updater
///////////////////////////////////////////////////////////////////////////////////////////////////
if ( ! class_exists('SEOPRESS_Updater')) {
    // load our custom updater
    require_once dirname(__FILE__) . '/inc/admin/updater/plugin-updater.php';
    require_once dirname(__FILE__) . '/inc/admin/updater/plugin-upgrader.php';
}

function SEOPRESS_Updater() {
    // To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
    $doing_cron = defined('DOING_CRON') && DOING_CRON;
    if ( ! current_user_can('manage_options') && ! $doing_cron) {
        return;
    }

    // retrieve our license key from the DB
    $license_key = defined('SEOPRESS_LICENSE_KEY') && ! empty(SEOPRESS_LICENSE_KEY) && is_string(SEOPRESS_LICENSE_KEY) ? SEOPRESS_LICENSE_KEY : trim(get_option('seopress_pro_license_key'));

    // setup the updater
    $edd_updater = new SEOPRESS_Updater(
        STORE_URL_SEOPRESS,
        __FILE__,
        [
            'version' => SEOPRESS_PRO_VERSION,
            'license' => $license_key,
            'item_id' => ITEM_ID_SEOPRESS,
            'author' => SEOPRESS_PRO_AUTHOR,
            'url' => home_url(),
            'beta' => false,
        ]
    );
}
add_action('init', 'SEOPRESS_Updater', 0);

///////////////////////////////////////////////////////////////////////////////////////////////////
//Google News Sitemap
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_xml_sitemap_news_enable_option() {
    $seopress_xml_sitemap_news_enable_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_xml_sitemap_news_enable_option)) {
        foreach ($seopress_xml_sitemap_news_enable_option as $key => $seopress_xml_sitemap_news_enable_value) {
            $options[$key] = $seopress_xml_sitemap_news_enable_value;
        }
        if (isset($seopress_xml_sitemap_news_enable_option['seopress_news_enable'])) {
            return $seopress_xml_sitemap_news_enable_option['seopress_news_enable'];
        }
    }
}

//WPML compatibility
if (defined('ICL_SITEPRESS_VERSION')) {
    add_filter('request', 'seopress_wpml_block_secondary_languages2');
}
function seopress_wpml_block_secondary_languages2($q) {
    $current_language = apply_filters('wpml_current_language', false);
    $default_language = apply_filters('wpml_default_language', false);
    if ($current_language !== $default_language) {
        unset($q['seopress_news']);
    }

    return $q;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Video XML Sitemap
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_xml_sitemap_video_enable_option() {
    $seopress_xml_sitemap_video_enable_option = get_option('seopress_xml_sitemap_option_name');
    if ( ! empty($seopress_xml_sitemap_video_enable_option)) {
        if (isset($seopress_xml_sitemap_video_enable_option['seopress_xml_sitemap_video_enable'])) {
            return $seopress_xml_sitemap_video_enable_option['seopress_xml_sitemap_video_enable'];
        }
    }
}

if ('1' == seopress_xml_sitemap_video_enable_option()) {
    //WPML compatibility
    if (defined('ICL_SITEPRESS_VERSION')) {
        add_filter('request', 'seopress_wpml_block_secondary_languages3');
    }
    function seopress_wpml_block_secondary_languages3($q) {
        $current_language = apply_filters('wpml_current_language', false);
        $default_language = apply_filters('wpml_default_language', false);
        if ($current_language !== $default_language) {
            unset($q['seopress_video']);
        }

        return $q;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Highlight Current menu when Editing Post Type
///////////////////////////////////////////////////////////////////////////////////////////////////
add_filter('parent_file', 'seopress_submenu_current');
function seopress_submenu_current($current_menu) {
    global $pagenow;
    global $typenow;
    if ('post-new.php' == $pagenow || 'post.php' == $pagenow) {
        if ('seopress_404' == $typenow || 'seopress_bot' == $typenow || 'seopress_backlinks' == $typenow || 'seopress_schemas' == $typenow) {
            global $plugin_page;
            $plugin_page = 'seopress-option';
        }
    }

    return $current_menu;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// 404 Cleaning CRON
///////////////////////////////////////////////////////////////////////////////////////////////////
//Enable CRON 404 cleaning
function seopress_404_cleaning_option() {
    $seopress_404_cleaning_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_404_cleaning_option)) {
        foreach ($seopress_404_cleaning_option as $key => $seopress_404_cleaning_value) {
            $options[$key] = $seopress_404_cleaning_value;
        }
        if (isset($seopress_404_cleaning_option['seopress_404_cleaning'])) {
            return $seopress_404_cleaning_option['seopress_404_cleaning'];
        }
    }
}

function seopress_404_cron_cleaning_action($force = false) {
    if ('1' === seopress_404_cleaning_option() || true === $force) {
        $args = [
            'date_query' => [
                [
                    'column' => 'post_date_gmt',
                    'before' => '1 month ago',
                ],
            ],
            'posts_per_page' => -1,
            'post_type' => 'seopress_404',
            'meta_key' => '_seopress_redirections_type',
            'meta_compare' => 'NOT EXISTS',
        ];

        $args = apply_filters('seopress_404_cleaning_query', $args);

        // The Query
        $old_404_query = new WP_Query($args);

        // The Loop
        if ($old_404_query->have_posts()) {
            while ($old_404_query->have_posts()) {
                $old_404_query->the_post();
                wp_delete_post(get_the_ID(), true);
            }
            /* Restore original Post Data */
            wp_reset_postdata();
        }
    }
}
add_action('seopress_404_cron_cleaning', 'seopress_404_cron_cleaning_action', 10, 1);

///////////////////////////////////////////////////////////////////////////////////////////////////
// Get LB types list
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_lb_types_list() {
    $seopress_lb_types = [
        'LocalBusiness' => __('Local Business (default)', 'wp-seopress-pro'),
        'AnimalShelter' => __('Animal Shelter', 'wp-seopress-pro'),
        'AutomotiveBusiness' => __('Automotive Business', 'wp-seopress-pro'),
        'AutoBodyShop' => __('|-Auto Body Shop', 'wp-seopress-pro'),
        'AutoDealer' => __('|-Auto Dealer', 'wp-seopress-pro'),
        'AutoPartsStore' => __('|-Auto Parts Store', 'wp-seopress-pro'),
        'AutoRental' => __('|-Auto Rental', 'wp-seopress-pro'),
        'AutoRepair' => __('|-Auto Repair', 'wp-seopress-pro'),
        'Auto Wash' => __('|-AutoWash', 'wp-seopress-pro'),
        'GasStation' => __('|-Gas Station', 'wp-seopress-pro'),
        'MotorcycleDealer' => __('|-Motorcycle Dealer', 'wp-seopress-pro'),
        'MotorcycleRepair' => __('|-Motorcycle Repair', 'wp-seopress-pro'),
        'ChildCare' => __('Child Care', 'wp-seopress-pro'),
        'DryCleaningOrLaundry' => __('Dry Cleaning Or Laundry', 'wp-seopress-pro'),
        'EmergencyService' => __('Emergency Service', 'wp-seopress-pro'),
        'FireStation' => __('|-Fire Station', 'wp-seopress-pro'),
        'Hospital' => __('|-Hospital', 'wp-seopress-pro'),
        'PoliceStation' => __('|-Police Station', 'wp-seopress-pro'),
        'EmploymentAgency' => __('Employment Agency', 'wp-seopress-pro'),
        'EntertainmentBusiness' => __('Entertainment Business', 'wp-seopress-pro'),
        'AdultEntertainment' => __('|-Adult Entertainment', 'wp-seopress-pro'),
        'AmusementPark' => __('|-Amusement Park', 'wp-seopress-pro'),
        'ArtGallery' => __('|-Art Gallery', 'wp-seopress-pro'),
        'Casino' => __('|-Casino', 'wp-seopress-pro'),
        'ComedyClub' => __('|-Comedy Club', 'wp-seopress-pro'),
        'MovieTheater' => __('|-Movie Theater', 'wp-seopress-pro'),
        'NightClub' => __('|-Night Club', 'wp-seopress-pro'),
        'FinancialService' => __('Financial Service', 'wp-seopress-pro'),
        'AccountingService' => __('|-Accounting Service', 'wp-seopress-pro'),
        'AutomatedTeller' => __('|-Automated Teller', 'wp-seopress-pro'),
        'BankOrCreditUnion' => __('|-Bank Or Credit Union', 'wp-seopress-pro'),
        'InsuranceAgency' => __('|-Insurance Agency', 'wp-seopress-pro'),
        'FoodEstablishment' => __('Food Establishment', 'wp-seopress-pro'),
        'Bakery' => __('|-Bakery', 'wp-seopress-pro'),
        'BarOrPub' => __('|-Bar Or Pub', 'wp-seopress-pro'),
        'Brewery' => __('|-Brewery', 'wp-seopress-pro'),
        'CafeOrCoffeeShop' => __('|-Cafe Or Coffee Shop', 'wp-seopress-pro'),
        'FastFoodRestaurant' => __('|-Fast Food Restaurant', 'wp-seopress-pro'),
        'IceCreamShop' => __('|-Ice Cream Shop', 'wp-seopress-pro'),
        'Restaurant' => __('|-Restaurant', 'wp-seopress-pro'),
        'Winery' => __('|-Winery', 'wp-seopress-pro'),
        'GovernmentOffice' => __('Government Office', 'wp-seopress-pro'),
        'PostOffice' => __('|-PostOffice', 'wp-seopress-pro'),
        'HealthAndBeautyBusiness' => __('Health And Beauty Business', 'wp-seopress-pro'),
        'BeautySalon' => __('|-Beauty Salon', 'wp-seopress-pro'),
        'DaySpa' => __('|-Day Spa', 'wp-seopress-pro'),
        'HairSalon' => __('|-Hair Salon', 'wp-seopress-pro'),
        'HealthClub' => __('|-Health Club', 'wp-seopress-pro'),
        'NailSalon' => __('|-Nail Salon', 'wp-seopress-pro'),
        'TattooParlor' => __('|-Tattoo Parlor', 'wp-seopress-pro'),
        'HomeAndConstructionBusiness' => __('Home And Construction Business', 'wp-seopress-pro'),
        'Electrician' => __('|-Electrician', 'wp-seopress-pro'),
        'HVACBusiness' => __('|-HVAC Business', 'wp-seopress-pro'),
        'HousePainter' => __('|-House Painter', 'wp-seopress-pro'),
        'Locksmith' => __('|-Locksmith', 'wp-seopress-pro'),
        'MovingCompany' => __('|-Moving Company', 'wp-seopress-pro'),
        'Plumber' => __('|-Plumber', 'wp-seopress-pro'),
        'RoofingContractor' => __('|-Roofing Contractor', 'wp-seopress-pro'),
        'InternetCafe' => __('Internet Cafe', 'wp-seopress-pro'),
        'MedicalBusiness' => __('Medical Business', 'wp-seopress-pro'),
        'CommunityHealth' => __('|-Community Health', 'wp-seopress-pro'),
        'Dentist' => __('|-Dentist', 'wp-seopress-pro'),
        'Dermatology' => __('|-Dermatology', 'wp-seopress-pro'),
        'DietNutrition' => __('|-Diet Nutrition', 'wp-seopress-pro'),
        'Emergency' => __('|-Emergency', 'wp-seopress-pro'),
        'Gynecologic' => __('|-Gynecologic', 'wp-seopress-pro'),
        'MedicalClinic' => __('|-Medical Clinic', 'wp-seopress-pro'),
        'Midwifery' => __('|-Midwifery', 'wp-seopress-pro'),
        'Nursing' => __('|-Nursing', 'wp-seopress-pro'),
        'Obstetric' => __('|-Obstetric', 'wp-seopress-pro'),
        'Oncologic' => __('|-Oncologic', 'wp-seopress-pro'),
        'Optician' => __('|-Optician', 'wp-seopress-pro'),
        'Optometric' => __('|-Optometric', 'wp-seopress-pro'),
        'Otolaryngologic' => __('|-Otolaryngologic', 'wp-seopress-pro'),
        'Pediatric' => __('|-Pediatric', 'wp-seopress-pro'),
        'Pharmacy' => __('|-Pharmacy', 'wp-seopress-pro'),
        'Physician' => __('|-Physician', 'wp-seopress-pro'),
        'Physiotherapy' => __('|-Physiotherapy', 'wp-seopress-pro'),
        'PlasticSurgery' => __('|-Plastic Surgery', 'wp-seopress-pro'),
        'Podiatric' => __('|-Podiatric', 'wp-seopress-pro'),
        'PrimaryCare' => __('|-Primary Care', 'wp-seopress-pro'),
        'Psychiatric' => __('|-Psychiatric', 'wp-seopress-pro'),
        'PublicHealth' => __('|-Public Health', 'wp-seopress-pro'),
        'VeterinaryCare' => __('|-Veterinary Care', 'wp-seopress-pro'),
        'LegalService' => __('Legal Service', 'wp-seopress-pro'),
        'Attorney' => __('|-Attorney', 'wp-seopress-pro'),
        'Notary' => __('|-Notary', 'wp-seopress-pro'),
        'Library' => __('Library', 'wp-seopress-pro'),
        'LodgingBusiness' => __('Lodging Business', 'wp-seopress-pro'),
        'BedAndBreakfast' => __('|-Bed And Breakfast', 'wp-seopress-pro'),
        'Campground' => __('|-Campground', 'wp-seopress-pro'),
        'Hostel' => __('|-Hostel', 'wp-seopress-pro'),
        'Hotel' => __('|-Hotel', 'wp-seopress-pro'),
        'Motel' => __('|-Motel', 'wp-seopress-pro'),
        'Resort' => __('|-Resort', 'wp-seopress-pro'),
        'ProfessionalService' => __('Professional Service', 'wp-seopress-pro'),
        'RadioStation' => __('Radio Station', 'wp-seopress-pro'),
        'RealEstateAgent' => __('Real Estate Agent', 'wp-seopress-pro'),
        'RecyclingCenter' => __('Recycling Center', 'wp-seopress-pro'),
        'SelfStorage' => __('Real Self Storage', 'wp-seopress-pro'),
        'ShoppingCenter' => __('Shopping Center', 'wp-seopress-pro'),
        'SportsActivityLocation' => __('Sports Activity Location', 'wp-seopress-pro'),
        'BowlingAlley' => __('|-Bowling Alley', 'wp-seopress-pro'),
        'ExerciseGym' => __('|-Exercise Gym', 'wp-seopress-pro'),
        'GolfCourse' => __('|-Golf Course', 'wp-seopress-pro'),
        'HealthClub' => __('|-Health Club', 'wp-seopress-pro'),
        'PublicSwimmingPool' => __('|-Public Swimming Pool', 'wp-seopress-pro'),
        'SkiResort' => __('|-Ski Resort', 'wp-seopress-pro'),
        'SportsClub' => __('|-Sports Club', 'wp-seopress-pro'),
        'StadiumOrArena' => __('|-Stadium Or Arena', 'wp-seopress-pro'),
        'TennisComplex' => __('|-Tennis Complex', 'wp-seopress-pro'),
        'Store' => __('Store', 'wp-seopress-pro'),
        'AutoPartsStore' => __('|-Auto Parts Store', 'wp-seopress-pro'),
        'BikeStore' => __('|-Bike Store', 'wp-seopress-pro'),
        'BookStore' => __('|-Book Store', 'wp-seopress-pro'),
        'ClothingStore' => __('|-Clothing Store', 'wp-seopress-pro'),
        'ComputerStore' => __('|-Computer Store', 'wp-seopress-pro'),
        'ConvenienceStore' => __('|-Convenience Store', 'wp-seopress-pro'),
        'DepartmentStore' => __('|-Department Store', 'wp-seopress-pro'),
        'ElectronicsStore' => __('|-Electronics Store', 'wp-seopress-pro'),
        'Florist' => __('|-Florist', 'wp-seopress-pro'),
        'FurnitureStore' => __('|-Furniture Store', 'wp-seopress-pro'),
        'GardenStore' => __('|-Garden Store', 'wp-seopress-pro'),
        'GroceryStore' => __('|-Grocery Store', 'wp-seopress-pro'),
        'HardwareStore' => __('|-Hardware Store', 'wp-seopress-pro'),
        'HobbyShop' => __('|-Hobby Shop', 'wp-seopress-pro'),
        'HomeGoodsStore' => __('|-Home Goods Store', 'wp-seopress-pro'),
        'JewelryStore' => __('|-Jewelry Store', 'wp-seopress-pro'),
        'LiquorStore' => __('|-Liquor Store', 'wp-seopress-pro'),
        'MensClothingStore' => __('|-Mens Clothing Store', 'wp-seopress-pro'),
        'MobilePhoneStore' => __('|-Mobile Phone Store', 'wp-seopress-pro'),
        'MovieRentalStore' => __('|-Movie Rental Store', 'wp-seopress-pro'),
        'MusicStore' => __('|-Music Store', 'wp-seopress-pro'),
        'OfficeEquipmentStore' => __('|-Office Equipment Store', 'wp-seopress-pro'),
        'OutletStore' => __('|-Outlet Store', 'wp-seopress-pro'),
        'PawnShop' => __('|-Pawn Shop', 'wp-seopress-pro'),
        'PetStore' => __('|-Pet Store', 'wp-seopress-pro'),
        'ShoeStore' => __('|-Shoe Store', 'wp-seopress-pro'),
        'SportingGoodsStore' => __('|-Sporting Goods Store', 'wp-seopress-pro'),
        'TireShop' => __('|-Tire Shop', 'wp-seopress-pro'),
        'ToyStore' => __('|-Toy Store', 'wp-seopress-pro'),
        'WholesaleStore' => __('|-Wholesale Store', 'wp-seopress-pro'),
        'TelevisionStation' => __('|-Wholesale Store', 'wp-seopress-pro'),
        'TouristInformationCenter' => __('Tourist Information Center', 'wp-seopress-pro'),
        'TravelAgency' => __('Travel Agency', 'wp-seopress-pro'),
    ];

    $seopress_lb_types = apply_filters('seopress_schemas_lb_types', $seopress_lb_types);

    return $seopress_lb_types;
}
