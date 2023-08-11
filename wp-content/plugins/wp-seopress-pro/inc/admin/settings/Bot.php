<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Bot SECTION======================================================================
add_settings_section(
    'seopress_setting_section_bot', // ID
    '',
    //__("Bot","wp-seopress-pro"), // Title
    'print_section_info_bot', // Callback
    'seopress-settings-admin-bot' // Page
);

add_settings_section(
    'seopress_setting_section_bot_settings', // ID
    '',
    //__("Settings","wp-seopress-pro"), // Title
    'print_section_info_bot_settings', // Callback
    'seopress-settings-admin-bot-settings' // Page
);

add_settings_field(
    'seopress_bot_scan_settings_post_types', // ID
    __('Post types to scan', 'wp-seopress-pro'), // Title
    'seopress_bot_scan_settings_post_types_callback', // Callback
    'seopress-settings-admin-bot-settings', // Page
    'seopress_setting_section_bot_settings' // Section
);

add_settings_field(
    'seopress_bot_scan_settings_where', // ID
    __('Find links in', 'wp-seopress-pro'), // Title
    'seopress_bot_scan_settings_where_callback', // Callback
    'seopress-settings-admin-bot-settings', // Page
    'seopress_setting_section_bot_settings' // Section
);

add_settings_field(
    'seopress_bot_scan_settings_number', // ID
    __('Number of posts / pages / post types to scan', 'wp-seopress-pro'), // Title
    'seopress_bot_scan_settings_number_callback', // Callback
    'seopress-settings-admin-bot-settings', // Page
    'seopress_setting_section_bot_settings' // Section
);

add_settings_field(
    'seopress_bot_scan_settings_type', // ID
    __('Scan link type (slow down the bot)', 'wp-seopress-pro'), // Title
    'seopress_bot_scan_settings_type_callback', // Callback
    'seopress-settings-admin-bot-settings', // Page
    'seopress_setting_section_bot_settings' // Section
);

add_settings_field(
    'seopress_bot_scan_settings_404', // ID
    __('Scan 404 only', 'wp-seopress-pro'), // Title
    'seopress_bot_scan_settings_404_callback', // Callback
    'seopress-settings-admin-bot-settings', // Page
    'seopress_setting_section_bot_settings' // Section
);

add_settings_field(
    'seopress_bot_scan_settings_timeout', // ID
    __('Request Timeout (default 5 sec)', 'wp-seopress-pro'), // Title
    'seopress_bot_scan_settings_timeout_callback', // Callback
    'seopress-settings-admin-bot-settings', // Page
    'seopress_setting_section_bot_settings' // Section
);

add_settings_field(
    'seopress_bot_scan_settings_cleaning', // ID
    __('Clean broken links list when requesting a new scan', 'wp-seopress-pro'), // Title
    'seopress_bot_scan_settings_cleaning_callback', // Callback
    'seopress-settings-admin-bot-settings', // Page
    'seopress_setting_section_bot_settings' // Section
);
