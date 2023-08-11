<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Google News SECTION======================================================================
add_settings_section(
    'seopress_setting_section_news', // ID
    '',
    //__("Google News","wp-seopress-pro"), // Title
    'print_section_info_news', // Callback
    'seopress-settings-admin-news' // Page
);

add_settings_field(
    'seopress_news_enable', // ID
    __('Enable Google News Sitemap', 'wp-seopress-pro'), // Title
    'seopress_news_enable_callback', // Callback
    'seopress-settings-admin-news', // Page
    'seopress_setting_section_news' // Section
);

add_settings_field(
    'seopress_news_name', // ID
    __('Publication Name (must be the same as used in Google News)', 'wp-seopress-pro'), // Title
    'seopress_news_name_callback', // Callback
    'seopress-settings-admin-news', // Page
    'seopress_setting_section_news' // Section
);

add_settings_field(
    'seopress_news_name_post_types_list', // ID
    __('Select your Custom Post Type to INCLUDE in your Google News Sitemap', 'wp-seopress-pro'), // Title
    'seopress_news_name_post_types_list_callback', // Callback
    'seopress-settings-admin-news', // Page
    'seopress_setting_section_news' // Section
);
