<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Structured Data Types Core SECTION===============================================================
add_settings_section(
    'seopress_setting_section_rich_snippets', // ID
    '',
    //__("Structured Data Types","wp-seopress-pro"), // Title
    'print_section_info_rich_snippets', // Callback
    'seopress-settings-admin-rich-snippets' // Page
);

add_settings_field(
    'seopress_rich_snippets_enable', // ID
    __('Enable Structured Data Types', 'wp-seopress-pro'), // Title
    'seopress_rich_snippets_enable_callback', // Callback
    'seopress-settings-admin-rich-snippets', // Page
    'seopress_setting_section_rich_snippets' // Section
);

add_settings_field(
    'seopress_rich_snippets_publisher_logo', // ID
    __('Upload your publisher logo', 'wp-seopress-pro'), // Title
    'seopress_rich_snippets_publisher_logo_callback', // Callback
    'seopress-settings-admin-rich-snippets', // Page
    'seopress_setting_section_rich_snippets' // Section
);

add_settings_field(
    'seopress_rich_snippets_site_nav', // ID
    __('Add SiteNavigationElement schema to your main menu', 'wp-seopress-pro'), // Title
    'seopress_rich_snippets_site_nav_callback', // Callback
    'seopress-settings-admin-rich-snippets', // Page
    'seopress_setting_section_rich_snippets' // Section
);
