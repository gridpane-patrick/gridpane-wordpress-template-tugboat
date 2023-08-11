<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Robots SECTION===========================================================================
if (is_network_admin() && is_multisite()) {
    add_settings_section(
        'seopress_mu_setting_section_robots', // ID
        '',
        //__("Robots","wp-seopress-pro"), // Title
        'print_section_info_robots', // Callback
        'seopress-mu-settings-admin-robots' // Page
    );
    add_settings_field(
        'seopress_mu_robots_enable', // ID
        __('Enable Robots', 'wp-seopress-pro'), // Title
        'seopress_robots_enable_callback', // Callback
        'seopress-mu-settings-admin-robots', // Page
        'seopress_mu_setting_section_robots' // Section
    );
    add_settings_field(
        'seopress_mu_robots_file', // ID
        __('Virtual Robots.txt file', 'wp-seopress-pro'), // Title
        'seopress_robots_file_callback', // Callback
        'seopress-mu-settings-admin-robots', // Page
        'seopress_mu_setting_section_robots' // Section
    );
} else {
    add_settings_section(
        'seopress_setting_section_robots', // ID
        '',
        //__("Robots","wp-seopress-pro"), // Title
        'print_section_info_robots', // Callback
        'seopress-settings-admin-robots' // Page
    );
    add_settings_field(
        'seopress_robots_enable', // ID
        __('Enable Robots', 'wp-seopress-pro'), // Title
        'seopress_robots_enable_callback', // Callback
        'seopress-settings-admin-robots', // Page
        'seopress_setting_section_robots' // Section
    );
    add_settings_field(
        'seopress_robots_file', // ID
        __('Virtual Robots.txt file', 'wp-seopress-pro'), // Title
        'seopress_robots_file_callback', // Callback
        'seopress-settings-admin-robots', // Page
        'seopress_setting_section_robots' // Section
    );
}
