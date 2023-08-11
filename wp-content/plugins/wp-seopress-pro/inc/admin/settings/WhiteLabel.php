<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//White Label SECTION==============================================================================
if (is_network_admin() && is_multisite()) {
    add_settings_section(
        'seopress_mu_setting_section_white_label', // ID
        '',
        //__("White Label","wp-seopress-pro"), // Title
        'print_section_info_white_label', // Callback
        'seopress-mu-settings-admin-white-label' // Page
    );

    add_settings_field(
        'seopress_mu_white_label_admin_header', // ID
        __('Remove SEOPress cards from SEO dashboard', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_header_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_mu_white_label_admin_notices', // ID
        __('Remove SEOPress icons in header', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_notices_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_mu_white_label_admin_menu', // ID
        __('Filter SEO admin menu dashicons', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_menu_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_mu_white_label_admin_bar_icon', // ID
        __('Edit SEOPress item in admin bar', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_bar_icon_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_mu_white_label_admin_title', // ID
        __('Edit SEOPress title in main menu', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_title_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_mu_white_label_admin_bar_logo', // ID
        __('Add your custom logo in SEOPress admin header', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_bar_logo_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_mu_white_label_footer_credits', // ID
        __('Remove SEOPress credits in footer admin pages', 'wp-seopress-pro'), // Title
        'seopress_white_label_footer_credits_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_mu_white_label_help_links', // ID
        __('Hide SEOPress links / help icons', 'wp-seopress-pro'), // Title
        'seopress_white_label_help_links_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_mu_white_label_menu_pages', // ID
        __('Remove SEOPress menu/submenu pages/dashboard items', 'wp-seopress-pro'), // Title
        'seopress_white_label_menu_pages_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_mu_white_label_plugin_list_title', // ID
        __('Change plugin title in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_title_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_mu_white_label_plugin_list_title_pro', // ID
        __('Change plugin title (PRO) in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_title_pro_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_mu_white_label_plugin_list_desc', // ID
        __('Change plugin description in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_desc_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_mu_white_label_plugin_list_desc_pro', // ID
        __('Change plugin description (PRO) in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_desc_pro_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_mu_white_label_plugin_list_author', // ID
        __('Change plugin author in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_author_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_mu_white_label_plugin_list_website', // ID
        __('Change plugin website in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_website_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_mu_white_label_plugin_list_view_details', // ID
        __('Remove View details in plugin list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_view_details_callback', // Callback
        'seopress-mu-settings-admin-white-label', // Page
        'seopress_mu_setting_section_white_label' // Section
    );
} else {
    add_settings_section(
        'seopress_setting_section_white_label', // ID
        '',
        //__("White Label","wp-seopress-pro"), // Title
        'print_section_info_white_label', // Callback
        'seopress-settings-admin-white-label' // Page
    );

    add_settings_field(
        'seopress_white_label_admin_header', // ID
        __('Remove SEOPress cards from SEO dashboard', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_header_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_white_label_admin_notices', // ID
        __('Remove SEOPress icons in header', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_notices_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_white_label_admin_menu', // ID
        __('Filter SEO admin menu dashicons', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_menu_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_white_label_admin_bar_icon', // ID
        __('Edit SEOPress item in admin bar', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_bar_icon_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_white_label_admin_title', // ID
        __('Edit SEOPress title in main menu', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_title_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_white_label_admin_bar_logo', // ID
        __('Add your custom logo in SEOPress admin header', 'wp-seopress-pro'), // Title
        'seopress_white_label_admin_bar_logo_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_white_label_footer_credits', // ID
        __('Remove SEOPress credits in footer admin pages', 'wp-seopress-pro'), // Title
        'seopress_white_label_footer_credits_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );

    add_settings_field(
        'seopress_white_label_help_links', // ID
        __('Hide SEOPress links / help icons', 'wp-seopress-pro'), // Title
        'seopress_white_label_help_links_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_white_label_plugin_list_title', // ID
        __('Change plugin title in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_title_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_white_label_plugin_list_title_pro', // ID
        __('Change plugin title (PRO) in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_title_pro_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_white_label_plugin_list_desc', // ID
        __('Change plugin description in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_desc_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_white_label_plugin_list_desc_pro', // ID
        __('Change plugin description (PRO) in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_desc_pro_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_white_label_plugin_list_author', // ID
        __('Change plugin author in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_author_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_white_label_plugin_list_website', // ID
        __('Change plugin website in plugins list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_website_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );
    add_settings_field(
        'seopress_white_label_plugin_list_view_details', // ID
        __('Remove View details in plugin list', 'wp-seopress-pro'), // Title
        'seopress_white_label_plugin_list_view_details_callback', // Callback
        'seopress-settings-admin-white-label', // Page
        'seopress_setting_section_white_label' // Section
    );
}
