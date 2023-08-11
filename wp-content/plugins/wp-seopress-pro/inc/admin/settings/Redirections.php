<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//404 SECTION=========================================================================
add_settings_section(
    'seopress_setting_section_monitor_404', // ID
    '',
    //__("404","wp-seopress-pro"), // Title
    'print_section_info_monitor_404', // Callback
    'seopress-settings-admin-monitor-404' // Page
);

add_settings_field(
    'seopress_404_enable', // ID
    __('404 log', 'wp-seopress-pro'), // Title
    'seopress_404_enable_callback', // Callback
    'seopress-settings-admin-monitor-404', // Page
    'seopress_setting_section_monitor_404' // Section
);

add_settings_field(
    'seopress_404_cleaning', // ID
    __('404 cleaning', 'wp-seopress-pro'), // Title
    'seopress_404_cleaning_callback', // Callback
    'seopress-settings-admin-monitor-404', // Page
    'seopress_setting_section_monitor_404' // Section
);

add_settings_field(
    'seopress_404_redirect_home', // ID
    __('Redirect 404 to', 'wp-seopress-pro'), // Title
    'seopress_404_redirect_home_callback', // Callback
    'seopress-settings-admin-monitor-404', // Page
    'seopress_setting_section_monitor_404' // Section
);

add_settings_field(
    'seopress_404_redirect_custom_url', // ID
    __('Redirect to specific URL', 'wp-seopress-pro'), // Title
    'seopress_404_redirect_custom_url_callback', // Callback
    'seopress-settings-admin-monitor-404', // Page
    'seopress_setting_section_monitor_404' // Section
);

add_settings_field(
    'seopress_404_redirect_status_code', // ID
    __('Status code of redirections', 'wp-seopress-pro'), // Title
    'seopress_404_redirect_status_code_callback', // Callback
    'seopress-settings-admin-monitor-404', // Page
    'seopress_setting_section_monitor_404' // Section
);


add_settings_field(
    'seopress_404_enable_mails', // ID
    __('Email notifications', 'wp-seopress-pro'), // Title
    'seopress_404_enable_mails_callback', // Callback
    'seopress-settings-admin-monitor-404', // Page
    'seopress_setting_section_monitor_404' // Section
);

add_settings_field(
    'seopress_404_enable_mails_from', // ID
    __('Send emails to', 'wp-seopress-pro'), // Title
    'seopress_404_enable_mails_from_callback', // Callback
    'seopress-settings-admin-monitor-404', // Page
    'seopress_setting_section_monitor_404' // Section
);

add_settings_field(
    'seopress_404_disable_automatic_redirects', // ID
    __('Disable automatic redirects', 'wp-seopress-pro'), // Title
    'seopress_404_disable_automatic_redirects_callback', // Callback
    'seopress-settings-admin-monitor-404', // Page
    'seopress_setting_section_monitor_404' // Section
);

add_settings_field(
    'seopress_404_ip_logging', // ID
    __('Disable IP logging', 'wp-seopress-pro'), // Title
    'seopress_404_ip_logging_callback', // Callback
    'seopress-settings-admin-monitor-404', // Page
    'seopress_setting_section_monitor_404' // Section
);
