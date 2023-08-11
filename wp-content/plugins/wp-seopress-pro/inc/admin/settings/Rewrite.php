<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Rewrite SECTION==============================================================================
add_settings_section(
    'seopress_setting_section_rewrite', // ID
    '',
    //__("Rewrite","wp-seopress-pro"), // Title
    'print_section_info_rewrite', // Callback
    'seopress-settings-admin-rewrite' // Page
);

add_settings_field(
    'seopress_rewrite_search', // ID
    __('Custom URL for search results', 'wp-seopress-pro'), // Title
    'seopress_rewrite_search_callback', // Callback
    'seopress-settings-admin-rewrite', // Page
    'seopress_setting_section_rewrite' // Section
);
