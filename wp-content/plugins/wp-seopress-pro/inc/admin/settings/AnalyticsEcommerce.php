<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Google Analytics Ecommerce SECTION=================================================================
add_settings_section(
    'seopress_setting_section_google_analytics_ecommerce', // ID
    '',
    //__("Analytics","wp-seopress-pro"), // Title
    'print_section_info_google_analytics_ecommerce', // Callback
    'seopress-settings-admin-google-analytics-ecommerce' // Page
);
add_settings_field(
    'seopress_google_analytics_purchases', // ID
    __('Measure purchases', 'wp-seopress-pro'), // Title
    'seopress_google_analytics_purchases_callback', // Callback
    'seopress-settings-admin-google-analytics-ecommerce', // Page
    'seopress_setting_section_google_analytics_ecommerce' // Section
);
add_settings_field(
    'seopress_google_analytics_add_to_cart', // ID
    __('Add to cart event', 'wp-seopress-pro'), // Title
    'seopress_google_analytics_add_to_cart_callback', // Callback
    'seopress-settings-admin-google-analytics-ecommerce', // Page
    'seopress_setting_section_google_analytics_ecommerce' // Section
);
add_settings_field(
    'seopress_google_analytics_remove_from_cart', // ID
    __('Remove from cart event', 'wp-seopress-pro'), // Title
    'seopress_google_analytics_remove_from_cart_callback', // Callback
    'seopress-settings-admin-google-analytics-ecommerce', // Page
    'seopress_setting_section_google_analytics_ecommerce' // Section
);
