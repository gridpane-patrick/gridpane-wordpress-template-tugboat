<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Inspect URL SECTION==============================================================================
add_settings_section(
    'seopress_setting_section_inspect_url', // ID
    '',
    //__("Inspect URL","wp-seopress-pro"), // Title
    'print_section_info_inspect_url', // Callback
    'seopress-settings-admin-inspect-url' // Page
);

add_settings_field(
    'seopress_pro_inspect_url_api', // ID
    __('Google Inspect URL API key', 'wp-seopress-pro'), // Title
    'seopress_pro_inspect_url_api_callback', // Callback
    'seopress-settings-admin-inspect-url', // Page
    'seopress_setting_section_inspect_url' // Section
);
