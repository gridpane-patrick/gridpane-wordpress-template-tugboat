<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Easy Digital Downloads SECTION======================================================================
add_settings_section(
    'seopress_setting_section_edd', // ID
    '',
    //__("Easy Digital Downloads","wp-seopress-pro"), // Title
    'print_section_info_edd', // Callback
    'seopress-settings-admin-edd' // Page
);

add_settings_field(
    'seopress_edd_product_og_price', // ID
    __('OG Price', 'wp-seopress-pro'), // Title
    'seopress_edd_product_og_price_callback', // Callback
    'seopress-settings-admin-edd', // Page
    'seopress_setting_section_edd' // Section
);

add_settings_field(
    'seopress_edd_product_og_currency', // ID
    __('OG Currency', 'wp-seopress-pro'), // Title
    'seopress_edd_product_og_currency_callback', // Callback
    'seopress-settings-admin-edd', // Page
    'seopress_setting_section_edd' // Section
);

add_settings_field(
    'seopress_edd_meta_generator', // ID
    __('Remove Easy Digital Downloads generator tag in your head', 'wp-seopress-pro'), // Title
    'seopress_edd_meta_generator_callback', // Callback
    'seopress-settings-admin-edd', // Page
    'seopress_setting_section_edd' // Section
);
