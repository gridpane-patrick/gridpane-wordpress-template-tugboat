<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Security SECTION=======================================================================
add_settings_field(
    'seopress_advanced_security_metaboxe_sdt_role', // ID
    __('Block Structured Data Types metabox to user roles', 'wp-seopress-pro'), // Title
    'seopress_advanced_security_metaboxe_sdt_role_callback', // Callback
    'seopress-settings-admin-advanced-security', // Page
    'seopress_setting_section_advanced_security' // Section
);

add_settings_field(
    'seopress_advanced_security_ga_widget_role', // ID
    __('Google Analytics widget permission', 'wp-seopress-pro'), // Title
    'seopress_advanced_security_ga_widget_role_callback', // Callback
    'seopress-settings-admin-advanced-security', // Page
    'seopress_setting_section_advanced_security_ga' // Section
);
