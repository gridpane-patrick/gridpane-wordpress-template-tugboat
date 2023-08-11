<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Google Analytics SECTION=================================================================
add_settings_section(
    'seopress_setting_section_google_analytics_dashboard', // ID
    '',
    //__("Analytics","wp-seopress-pro"), // Title
    'print_section_info_google_analytics_dashboard', // Callback
    'seopress-settings-admin-google-analytics-dashboard' // Page
);
add_settings_field(
    'seopress_google_analytics_auth', // ID
    __('Connect with Google Analytics API', 'wp-seopress-pro'), // Title
    'seopress_google_analytics_auth_callback', // Callback
    'seopress-settings-admin-google-analytics-dashboard', // Page
    'seopress_setting_section_google_analytics_dashboard' // Section
);
add_settings_field(
    'seopress_google_analytics_auth_client_id', // ID
    __('Google Console Client ID', 'wp-seopress-pro'), // Title
    'seopress_google_analytics_auth_client_id_callback', // Callback
    'seopress-settings-admin-google-analytics-dashboard', // Page
    'seopress_setting_section_google_analytics_dashboard' // Section
);
add_settings_field(
    'seopress_google_analytics_auth_secret_id', // ID
    __('Google Console Secret ID', 'wp-seopress-pro'), // Title
    'seopress_google_analytics_auth_secret_id_callback', // Callback
    'seopress-settings-admin-google-analytics-dashboard', // Page
    'seopress_setting_section_google_analytics_dashboard' // Section
);
add_settings_field(
    'seopress_google_analytics_dashboard_widget', // ID
    __('Remove Analytics dashboard widget', 'wp-seopress-pro'), // Title
    'seopress_google_analytics_dashboard_widget_callback', // Callback
    'seopress-settings-admin-google-analytics-dashboard', // Page
    'seopress_setting_section_google_analytics_dashboard' // Section
);
