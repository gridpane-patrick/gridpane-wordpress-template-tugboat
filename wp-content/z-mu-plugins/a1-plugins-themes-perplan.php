<?php

/**
 * @package 
 */
/*
Plugin Name: 
Plugin URI: https://waas1.com/
Description: custom hooks only for this platform.
Version: 1.0.0
Author: 
Author URI: https://waas1.com/
License: GPLv2 or later
*/


//change log
// version 1.0.1



// Exit if accessed directly.
if (!defined('ABSPATH')) { //this is for secuirty
    exit;
}

//do not run if user is not in back-end
if (!is_admin()) {
    return;
} //this is for performance


//if the call is from "wp-cli" don't run the code below
if (defined('WP_CLI') && WP_CLI) {
    return;
} //this is important that controlpanel can see all the plugins



//do not run if the call is ajax
if (defined('DOING_AJAX') && DOING_AJAX) {
    return;
} //we do not need to run this in javascript ajax call = This is for perforamnce





//also please exclude your code for superduper.!!!
//First test this using sftp. Do not push the code on GIT while your are testing







//////
///Eslamn code is following
///////



//and please write comments.


$GLOBALS['current_user_super_admin'] = false;
$GLOBALS['plans_plugins'] = [];


function _filter_all_plugins($get_plugins)
{
    $files_plans = array_diff(scandir(WPMU_PLUGIN_DIR . '/json/plans/'), array('.', '..'));

    foreach ($files_plans as  $f_plans) {
        $_f_plans = json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/plans/' . $f_plans), true);
        foreach ($_f_plans['plugins'] as  $p_value) {
            if (!in_array($_f_plans['name'], is_array($GLOBALS['plans_plugins'][$p_value]) ? $GLOBALS['plans_plugins'][$p_value] : [])) {

                $GLOBALS['plans_plugins'][$p_value][] = $_f_plans['name'];
            }
        }
    }


    if (is_user_logged_in() and in_array(wp_get_current_user()->user_login, ['superduper'])) {
        $GLOBALS['current_user_super_admin'] = true;
        return $get_plugins;
    }
    $plugins_allowed = [];

    $plan = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/plans/' . WAAS1_RESTRICTION_GROUP_ID . '.json'), true);
    // $plan = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/plans/2.json'), true);
    $critical_plugins = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/plugins/critical_plugins.json'), true);
    $plugins_hidden = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/plugins/plugins_hidden.json'), true);
    $plugins_auto_active = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/related-plugins-auto-active.json'), true);
    $exclude_plugin = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/exclude_plugin.json'), true);


    $plugins_hidden = array_merge(
        $critical_plugins,
        (is_array($plugins_hidden) ? $plugins_hidden : []),
        (is_array($plugins_auto_active) ? $plugins_auto_active : [])
    );
    if (WAAS1_RESTRICTION_GROUP_ID == 5) {
        $GLOBALS['onlyAdminPlugins'] = isset($plan['onlyAdminPlugins']) ? $plan['onlyAdminPlugins'] : [];
    }

    if (isset($plan['plugins'])) {
        if (is_array($exclude_plugin)) {
            $plan['plugins'] = array_merge($plan['plugins'], $exclude_plugin);
        }
        foreach ($get_plugins as $key =>  $plugin) {
            $plugin['hidden'] = 1;
            if (in_array($key, $plan['plugins']) and !in_array($key, $plugins_hidden)) {
                $plugins_allowed[$key] = $plugin;
            } else if (!in_array($key, $plugins_hidden)) {
                $plugin['hidden'] = 0;

                $plugins_allowed[$key] = $plugin;
            }
        }

        foreach (get_option('active_plugins') as  $plugin) {
            if (!in_array($plugin, $plan['plugins']) and !in_array($plugin, $plugins_hidden)) {
                deactivate_plugins("/$plugin");
            }
        }
        activate_plugin($critical_plugins);
    }

    return  $plugins_allowed;
}

function filter_all_themes($get_themes)
{

    if (is_user_logged_in() and in_array(wp_get_current_user()->data->user_login, ['superduper'])) {
        return $get_themes;
    }
    $themes_allowed = [];

    $plan = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/plans/' . WAAS1_RESTRICTION_GROUP_ID . '.json'), true);
    // $plan = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/plans/2.json'), true);
    if (isset($plan['themes'])) {
        foreach ($get_themes as $key =>  $theme) {
            $theme['hidden'] = 0;
            if (in_array($key, $plan['themes'])) {
                $themes_allowed[$key] = $theme;
            } else {
                $theme['hidden'] = -1;
                $themes_allowed[$key] = $theme;
            }
        }
    }
    return $themes_allowed;
}


if (!in_array(WAAS1_RESTRICTION_GROUP_ID,[1,6])) {
add_action('admin_init', function () {

    if (isset($_COOKIE['taager']) and current_user_can('administrator')) {
        activate_plugin("/taager-woocommerce-plugin/taager-api.php");
        unset($_COOKIE['taager']);
        setcookie('taager', '', time() - 3600, '/', '.myshahbandr.com', true, true);
    }
});
add_filter('all_plugins', '_filter_all_plugins', 99, 1);

add_filter('wp_prepare_themes_for_js', 'filter_all_themes', 1, 2);
add_filter('show_advanced_plugins', function ($default, $type) {
    if ($type == 'mustuse') return false; // Hide Must-Use
    return $default;
}, 10, 2);
add_action('activated_plugin', function ($plugin) {
    if (is_user_logged_in() and in_array(wp_get_current_user()->user_login, ['superduper'])) {
        $exclude_plugin = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/exclude_plugin.json'), true);
        $exclude_plugin[] = $plugin;
        $exclude_plugin = array_values(array_unique($exclude_plugin));
        file_put_contents(WPMU_PLUGIN_DIR . '/exclude_plugin.json', json_encode($exclude_plugin));
    }
});

add_action('deactivated_plugin', function ($plugin) {
    if (is_user_logged_in() and in_array(wp_get_current_user()->user_login, ['superduper'])) {
        $exclude_plugin = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/exclude_plugin.json'), true);
        unset($exclude_plugin[array_search($plugin, $exclude_plugin)]);
        $exclude_plugin = array_values(array_unique($exclude_plugin));
        file_put_contents(WPMU_PLUGIN_DIR . '/exclude_plugin.json', json_encode($exclude_plugin));
    }
});


require_once __DIR__ . '/rebranding/rebranding.php';
}
