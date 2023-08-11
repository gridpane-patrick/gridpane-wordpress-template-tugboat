<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//htaccess SECTION=========================================================================
add_settings_section(
    'seopress_setting_section_htaccess', // ID
    '',
    //__("htaccess","wp-seopress-pro"), // Title
    'print_section_info_htaccess', // Callback
    'seopress-settings-admin-htaccess' // Page
);

add_settings_field(
    'seopress_htaccess_file', // ID
   __('Edit your htaccess file', 'wp-seopress-pro'), // Title
    'seopress_htaccess_file_callback', // Callback
    'seopress-settings-admin-htaccess', // Page
    'seopress_setting_section_htaccess' // Section
);
