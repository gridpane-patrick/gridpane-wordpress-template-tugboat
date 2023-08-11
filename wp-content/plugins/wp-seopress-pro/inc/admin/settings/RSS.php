<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//RSS SECTION==============================================================================
add_settings_section(
    'seopress_setting_section_rss', // ID
    '',
    //__("RSS","wp-seopress-pro"), // Title
    'print_section_info_rss', // Callback
    'seopress-settings-admin-rss' // Page
);

add_settings_field(
    'seopress_rss_before_html', // ID
    __('Display content before each post', 'wp-seopress-pro'), // Title
    'seopress_rss_before_html_callback', // Callback
    'seopress-settings-admin-rss', // Page
    'seopress_setting_section_rss' // Section
);

add_settings_field(
    'seopress_rss_after_html', // ID
    __('Display content after each post', 'wp-seopress-pro'), // Title
    'seopress_rss_after_html_callback', // Callback
    'seopress-settings-admin-rss', // Page
    'seopress_setting_section_rss' // Section
);

add_settings_field(
    'seopress_rss_disable_comments_feed', // ID
    __('Disable comments RSS feed', 'wp-seopress-pro'), // Title
    'seopress_rss_disable_comments_feed_callback', // Callback
    'seopress-settings-admin-rss', // Page
    'seopress_setting_section_rss' // Section
);

add_settings_field(
    'seopress_rss_disable_posts_feed', // ID
    __('Disable posts RSS feed', 'wp-seopress-pro'), // Title
    'seopress_rss_disable_posts_feed_callback', // Callback
    'seopress-settings-admin-rss', // Page
    'seopress_setting_section_rss' // Section
);

add_settings_field(
    'seopress_rss_disable_extra_feed', // ID
    __('Disable extra RSS feed', 'wp-seopress-pro'), // Title
    'seopress_rss_disable_extra_feed_callback', // Callback
    'seopress-settings-admin-rss', // Page
    'seopress_setting_section_rss' // Section
);

add_settings_field(
    'seopress_rss_disable_all_feeds', // ID
    __('Disable all RSS feeds', 'wp-seopress-pro'), // Title
    'seopress_rss_disable_all_feeds_callback', // Callback
    'seopress-settings-admin-rss', // Page
    'seopress_setting_section_rss' // Section
);
