<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Breadcrumbs SECTION======================================================================
add_settings_section(
    'seopress_setting_section_breadcrumbs', // ID
    '',
    //__("Breadcrumbs","wp-seopress-pro"), // Title
    'print_section_info_breadcrumbs', // Callback
    'seopress-settings-admin-breadcrumbs' // Page
);

add_settings_field(
    'seopress_breadcrumbs_enable', // ID
    __('Enable Breadcrumbs', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_enable_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_enable_json', // ID
    __('Enable JSON-LD Breadcrumbs', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_enable_json_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_separator', // ID
    __('Breadcrumbs Separator', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_separator_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_cpt', // ID
    __('Post type to show in Breadcrumbs for taxonomies (archive)', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_cpt_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_tax', // ID
    __('Taxonomy to show in Breadcrumbs for post types (singular)', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_tax_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_remove_blog_page', // ID
    __('Remove Posts page', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_remove_blog_page_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_remove_shop_page', // ID
    __('Remove Shop page', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_remove_shop_page_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_i18n_here', // ID
    __('Display a text before the breadcrumbs', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_i18n_here_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_i18n_home', // ID
    __('Translation for homepage', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_i18n_home_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_i18n_author', // ID
    __('Translation for "Author:"', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_i18n_author_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_i18n_404', // ID
    __('Translation for "Error 404"', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_i18n_404_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_i18n_search', // ID
    __('Translation for "Search results for"', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_i18n_search_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_i18n_no_results', // ID
    __('Translation for "No results"', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_i18n_no_results_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);
add_settings_field(
    'seopress_breadcrumbs_i18n_attachments', // ID
    __('Translation for "Attachments"', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_i18n_attachments_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);
add_settings_field(
    'seopress_breadcrumbs_i18n_paged', // ID
    __('Translation for "Page "', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_i18n_paged_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);

add_settings_field(
    'seopress_breadcrumbs_separator_disable', // ID
    __('Disable default breadcrumbs separator', 'wp-seopress-pro'), // Title
    'seopress_breadcrumbs_separator_disable_callback', // Callback
    'seopress-settings-admin-breadcrumbs', // Page
    'seopress_setting_section_breadcrumbs' // Section
);
