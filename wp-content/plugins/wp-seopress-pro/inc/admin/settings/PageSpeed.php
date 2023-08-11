<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//PageSpeed Insights SECTION=======================================================================
add_settings_section(
    'seopress_setting_section_page_speed', // ID
    '',
    //__("PageSpeed Insights","wp-seopress-pro"), // Title
    'print_section_info_page_speed', // Callback
    'seopress-settings-admin-page-speed' // Page
);

add_settings_field(
    'seopress_ps_url', // ID
    __('Enter a URL to check', 'wp-seopress-pro'), // Title
    'seopress_ps_url_callback', // Callback
    'seopress-settings-admin-page-speed', // Page
    'seopress_setting_section_page_speed' // Section
);

add_settings_field(
    'seopress_ps_api_key', // ID
    __('Enter your own Google Page Speed API key', 'wp-seopress-pro'), // Title
    'seopress_ps_api_key_callback', // Callback
    'seopress-settings-admin-page-speed', // Page
    'seopress_setting_section_page_speed' // Section
);
