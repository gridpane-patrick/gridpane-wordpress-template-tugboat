<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//White Label
//=================================================================================================
//Remove SEOPress admin header
function seopress_white_label_admin_header_option() {
    $seopress_white_label_admin_header_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_admin_header_option)) {
        foreach ($seopress_white_label_admin_header_option as $key => $seopress_white_label_admin_header_value) {
            $options[$key] = $seopress_white_label_admin_header_value;
        }
        if (isset($seopress_white_label_admin_header_option['seopress_white_label_admin_header'])) {
            return $seopress_white_label_admin_header_option['seopress_white_label_admin_header'];
        }
    }
}

if ('1' == seopress_white_label_admin_header_option()) {
    if ( ! defined('SEOPRESS_WL_ADMIN_HEADER')) {
        define('SEOPRESS_WL_ADMIN_HEADER', false);
    }
}

//Remove SEOPress admin header (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_admin_header_option() {
        $seopress_mu_white_label_admin_header_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_admin_header_option)) {
            foreach ($seopress_mu_white_label_admin_header_option as $key => $seopress_mu_white_label_admin_header_value) {
                $options[$key] = $seopress_mu_white_label_admin_header_value;
            }
            if (isset($seopress_mu_white_label_admin_header_option['seopress_mu_white_label_admin_header'])) {
                return $seopress_mu_white_label_admin_header_option['seopress_mu_white_label_admin_header'];
            }
        }
    }

    if ('1' == seopress_mu_white_label_admin_header_option()) {
        if ( ! defined('SEOPRESS_WL_ADMIN_HEADER')) {
            define('SEOPRESS_WL_ADMIN_HEADER', false);
        }
    }
}

//Remove SEOPress icons in header
function seopress_white_label_admin_notices_option() {
    $seopress_white_label_admin_notices_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_admin_notices_option)) {
        foreach ($seopress_white_label_admin_notices_option as $key => $seopress_white_label_admin_notices_value) {
            $options[$key] = $seopress_white_label_admin_notices_value;
        }
        if (isset($seopress_white_label_admin_notices_option['seopress_white_label_admin_notices'])) {
            return $seopress_white_label_admin_notices_option['seopress_white_label_admin_notices'];
        }
    }
}

if ('1' == seopress_white_label_admin_notices_option()) {
    if ( ! defined('SEOPRESS_WL_ICONS_HEADER')) {
        define('SEOPRESS_WL_ICONS_HEADER', false);
    }
}

//Remove SEOPress icons in header (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_admin_notices_option() {
        $seopress_mu_white_label_admin_notices_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_admin_notices_option)) {
            foreach ($seopress_mu_white_label_admin_notices_option as $key => $seopress_mu_white_label_admin_notices_value) {
                $options[$key] = $seopress_mu_white_label_admin_notices_value;
            }
            if (isset($seopress_mu_white_label_admin_notices_option['seopress_mu_white_label_admin_notices'])) {
                return $seopress_mu_white_label_admin_notices_option['seopress_mu_white_label_admin_notices'];
            }
        }
    }

    if ('1' == seopress_mu_white_label_admin_notices_option()) {
        if ( ! defined('SEOPRESS_WL_ICONS_HEADER')) {
            define('SEOPRESS_WL_ICONS_HEADER', false);
        }
    }
}

//Filter SEO admin menu dashicons
function seopress_white_label_admin_menu_option() {
    $seopress_white_label_admin_menu_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_admin_menu_option)) {
        foreach ($seopress_white_label_admin_menu_option as $key => $seopress_white_label_admin_menu_value) {
            $options[$key] = $seopress_white_label_admin_menu_value;
        }
        if (isset($seopress_white_label_admin_menu_option['seopress_white_label_admin_menu'])) {
            return esc_attr($seopress_white_label_admin_menu_option['seopress_white_label_admin_menu']);
        }
    }
}

if ('' != seopress_white_label_admin_menu_option()) {
    function sp_seo_admin_menu_hook($html) {
        return seopress_white_label_admin_menu_option();
    }
    add_filter('seopress_seo_admin_menu', 'sp_seo_admin_menu_hook');
}

//Filter SEO admin menu dashicons (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_admin_menu_option() {
        $seopress_mu_white_label_admin_menu_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_admin_menu_option)) {
            foreach ($seopress_mu_white_label_admin_menu_option as $key => $seopress_mu_white_label_admin_menu_value) {
                $options[$key] = $seopress_mu_white_label_admin_menu_value;
            }
            if (isset($seopress_mu_white_label_admin_menu_option['seopress_mu_white_label_admin_menu'])) {
                return $seopress_mu_white_label_admin_menu_option['seopress_mu_white_label_admin_menu'];
            }
        }
    }

    if ('' != seopress_mu_white_label_admin_menu_option()) {
        function sp_mu_seo_admin_menu_hook($html) {
            return seopress_mu_white_label_admin_menu_option();
        }
        add_filter('seopress_seo_admin_menu', 'sp_mu_seo_admin_menu_hook');
    }
}

//Change / remove SEOPress icon in admin bar
function seopress_white_label_admin_bar_icon_option() {
    $seopress_white_label_admin_bar_icon_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_admin_bar_icon_option)) {
        foreach ($seopress_white_label_admin_bar_icon_option as $key => $seopress_white_label_admin_bar_icon_value) {
            $options[$key] = $seopress_white_label_admin_bar_icon_value;
        }
        if (isset($seopress_white_label_admin_bar_icon_option['seopress_white_label_admin_bar_icon'])) {
            return $seopress_white_label_admin_bar_icon_option['seopress_white_label_admin_bar_icon'];
        }
    }
}

if ('' != seopress_white_label_admin_bar_icon_option()) {
    function sp_adminbar_icon_hook($html) {
        $html = seopress_white_label_admin_bar_icon_option();

        return $html;
    }
    add_filter('seopress_adminbar_icon', 'sp_adminbar_icon_hook');
}

//Change / remove SEOPress icon in admin bar (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_admin_bar_icon_option() {
        $seopress_mu_white_label_admin_bar_icon_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_admin_bar_icon_option)) {
            foreach ($seopress_mu_white_label_admin_bar_icon_option as $key => $seopress_mu_white_label_admin_bar_icon_value) {
                $options[$key] = $seopress_mu_white_label_admin_bar_icon_value;
            }
            if (isset($seopress_mu_white_label_admin_bar_icon_option['seopress_mu_white_label_admin_bar_icon'])) {
                return $seopress_mu_white_label_admin_bar_icon_option['seopress_mu_white_label_admin_bar_icon'];
            }
        }
    }

    if ('' != seopress_mu_white_label_admin_bar_icon_option()) {
        function sp_mu_adminbar_icon_hook($html) {
            $html = seopress_mu_white_label_admin_bar_icon_option();

            return $html;
        }
        add_filter('seopress_adminbar_icon', 'sp_mu_adminbar_icon_hook');
    }
}

//Change / remove SEOPress title from main menu
function seopress_white_label_admin_title_option() {
    $seopress_white_label_admin_title_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_admin_title_option)) {
        foreach ($seopress_white_label_admin_title_option as $key => $seopress_white_label_admin_title_value) {
            $options[$key] = $seopress_white_label_admin_title_value;
        }
        if (isset($seopress_white_label_admin_title_option['seopress_white_label_admin_title'])) {
            return $seopress_white_label_admin_title_option['seopress_white_label_admin_title'];
        }
    }
}

if ('' != seopress_white_label_admin_title_option()) {
    function sp_white_label_admin_title_hook($html) {
        $html = seopress_white_label_admin_title_option();

        return $html;
    }
    add_filter('seopress_seo_admin_menu_title', 'sp_white_label_admin_title_hook');
}

//Change / remove SEOPress title from main menu (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_admin_title_option() {
        $seopress_mu_white_label_admin_title_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_admin_title_option)) {
            foreach ($seopress_mu_white_label_admin_title_option as $key => $seopress_mu_white_label_admin_title_value) {
                $options[$key] = $seopress_mu_white_label_admin_title_value;
            }
            if (isset($seopress_mu_white_label_admin_title_option['seopress_mu_white_label_admin_title'])) {
                return $seopress_mu_white_label_admin_title_option['seopress_mu_white_label_admin_title'];
            }
        }
    }

    if ('' != seopress_mu_white_label_admin_title_option()) {
        function sp_mu_white_label_admin_title_hook($html) {
            $html = seopress_mu_white_label_admin_title_option();

            return $html;
        }
        add_filter('seopress_seo_admin_menu_title', 'sp_mu_white_label_admin_title_hook');
    }
}

//Add your custom logo in SEOPress admin header
function seopress_white_label_admin_bar_logo_option() {
    $seopress_white_label_admin_bar_logo_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_admin_bar_logo_option)) {
        foreach ($seopress_white_label_admin_bar_logo_option as $key => $seopress_white_label_admin_bar_logo_value) {
            $options[$key] = $seopress_white_label_admin_bar_logo_value;
        }
        if (isset($seopress_white_label_admin_bar_logo_option['seopress_white_label_admin_bar_logo'])) {
            return $seopress_white_label_admin_bar_logo_option['seopress_white_label_admin_bar_logo'];
        }
    }
}

if ('' != seopress_white_label_admin_bar_logo_option()) {
    if ( ! defined('SEOPRESS_WL_ADMIN_HEADER_LOGO')) {
        define('SEOPRESS_WL_ADMIN_HEADER_LOGO', seopress_white_label_admin_bar_logo_option());
    }
}

//Add your custom logo in SEOPress admin header (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_admin_bar_logo_option() {
        $seopress_mu_white_label_admin_bar_logo_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_admin_bar_logo_option)) {
            foreach ($seopress_mu_white_label_admin_bar_logo_option as $key => $seopress_mu_white_label_admin_bar_logo_value) {
                $options[$key] = $seopress_mu_white_label_admin_bar_logo_value;
            }
            if (isset($seopress_mu_white_label_admin_bar_logo_option['seopress_mu_white_label_admin_bar_logo'])) {
                return $seopress_mu_white_label_admin_bar_logo_option['seopress_mu_white_label_admin_bar_logo'];
            }
        }
    }

    if ('' != seopress_mu_white_label_admin_bar_logo_option()) {
        if ( ! defined('SEOPRESS_WL_ADMIN_HEADER_LOGO')) {
            define('SEOPRESS_WL_ADMIN_HEADER_LOGO', seopress_mu_white_label_admin_bar_logo_option());
        }
    }
}

//Remove SEOPress credits in footer admin pages
function seopress_white_label_footer_credits_option() {
    $seopress_white_label_footer_credits_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_footer_credits_option)) {
        foreach ($seopress_white_label_footer_credits_option as $key => $seopress_white_label_footer_credits_value) {
            $options[$key] = $seopress_white_label_footer_credits_value;
        }
        if (isset($seopress_white_label_footer_credits_option['seopress_white_label_footer_credits'])) {
            return $seopress_white_label_footer_credits_option['seopress_white_label_footer_credits'];
        }
    }
}

if ('1' == seopress_white_label_footer_credits_option()) {
    if (true === is_seopress_page()) {
        add_filter('admin_footer_text', '__return_empty_string');
    }
}

//Remove SEOPress credits in footer admin pages (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_footer_credits_option() {
        $seopress_mu_white_label_footer_credits_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_footer_credits_option)) {
            foreach ($seopress_mu_white_label_footer_credits_option as $key => $seopress_mu_white_label_footer_credits_value) {
                $options[$key] = $seopress_mu_white_label_footer_credits_value;
            }
            if (isset($seopress_mu_white_label_footer_credits_option['seopress_mu_white_label_footer_credits'])) {
                return $seopress_mu_white_label_footer_credits_option['seopress_mu_white_label_footer_credits'];
            }
        }
    }

    if ('1' == seopress_mu_white_label_footer_credits_option()) {
        if (true === is_seopress_page()) {
            add_filter('admin_footer_text', '__return_empty_string');
        }
    }
}

//Remove SEOPress help links
function seopress_white_label_help_links_option() {
    $seopress_white_label_help_links_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_help_links_option)) {
        foreach ($seopress_white_label_help_links_option as $key => $seopress_white_label_help_links_value) {
            $options[$key] = $seopress_white_label_help_links_value;
        }
        if (isset($seopress_white_label_help_links_option['seopress_white_label_help_links'])) {
            return $seopress_white_label_help_links_option['seopress_white_label_help_links'];
        }
    }
}

if ('1' == seopress_white_label_help_links_option()) {
    function seopress_white_label_css() {
        $get_current_screen = get_current_screen();
        $get_current_screen = $get_current_screen->id;
        if (true === is_seopress_page() || 'seopress_404' === $get_current_screen || 'seopress_bot' === $get_current_screen || 'seopress_backlinks' === $get_current_screen || 'seopress_schemas' === $get_current_screen) {
            echo '<style>.seopress-help, .seopress-doc, .seopress-your-schema .notice-info{display:none !important;}</style>';
        }
    }
    add_action('admin_head', 'seopress_white_label_css');
}

//Remove SEOPress help links (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_help_links_option() {
        $seopress_mu_white_label_help_links_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_help_links_option)) {
            foreach ($seopress_mu_white_label_help_links_option as $key => $seopress_mu_white_label_help_links_value) {
                $options[$key] = $seopress_mu_white_label_help_links_value;
            }
            if (isset($seopress_mu_white_label_help_links_option['seopress_mu_white_label_help_links'])) {
                return $seopress_mu_white_label_help_links_option['seopress_mu_white_label_help_links'];
            }
        }
    }

    if ('1' == seopress_mu_white_label_help_links_option()) {
        function seopress_white_label_css() {
            $get_current_screen = get_current_screen();
            $get_current_screen = $get_current_screen->id;
            if (true === is_seopress_page() || 'seopress_404' === $get_current_screen || 'seopress_bot' === $get_current_screen || 'seopress_backlinks' === $get_current_screen || 'seopress_schemas' === $get_current_screen) {
                echo '<style>.seopress-help, .seopress-doc, .seopress-your-schema .notice-info{display:none !important;}</style>';
            }
        }
        add_action('admin_head', 'seopress_white_label_css');
    }
}

//Remove SEOPress menu/submenu pages (multisite only)
if (is_multisite()) {
    function seopress_mu_white_label_menu_pages_option() {
        $seopress_mu_white_label_menu_pages_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_menu_pages_option)) {
            foreach ($seopress_mu_white_label_menu_pages_option as $key => $seopress_mu_white_label_menu_pages_value) {
                $options[$key] = $seopress_mu_white_label_menu_pages_value;
            }
            if (isset($seopress_mu_white_label_menu_pages_option['seopress_mu_white_label_menu_pages'])) {
                return $seopress_mu_white_label_menu_pages_option['seopress_mu_white_label_menu_pages'];
            }
        }
    }

    if ( ! empty(seopress_mu_white_label_menu_pages_option())) {
        if ( ! is_super_admin()) {
            add_action('admin_menu', 'seopress_remove_menu_page_hook');
            function seopress_remove_menu_page_hook() {
                $seopress_menu_pages_array = seopress_mu_white_label_menu_pages_option();

                if (array_key_exists('seopress-option', $seopress_menu_pages_array)) {
                    remove_menu_page('seopress-option'); //SEO
                }
            }

            add_action('admin_menu', 'seopress_remove_submenu_page_hook', 999);
            function seopress_remove_submenu_page_hook() {
                $seopress_menu_pages_array = seopress_mu_white_label_menu_pages_option();

                foreach ($seopress_menu_pages_array as $key => $value) {
                    remove_submenu_page('seopress-option', $key);

                    //Remove feature from Dashboard
                    $map = [
                        'seopress-titles'           => 'titles',
                        'seopress-xml-sitemap'      => 'xml_sitemap',
                        'seopress-social'           => 'social',
                        'seopress-google-analytics' => 'google_analytics',
                        'seopress-advanced'         => 'advanced',
                        'seopress-import-export'    => 'tools',
                        'seopress-pro-page'         => [
                            'woocommerce',
                            'edd',
                            'local_business',
                            'dublin_core',
                            'breadcrumbs',
                            'schemas',
                            'page_speed',
                            'robots',
                            'news',
                            'rewrite',
                            'htaccess',
                            'rss',
                            'redirects',
                        ],
                        'edit.php?post_type=seopress_404'     => 'redirects',
                        'edit.php?post_type=seopress_bot'     => 'bot',
                        'edit.php?post_type=seopress_schemas' => 'schemas',
                        'seopress-bot-batch'                  => 'bot',
                        'seopress-license'                    => 'license',
                    ];

                    if (array_key_exists($key, $map)) {
                        add_filter('seopress_remove_feature_' . $map[$key], '__return_false');
                        if ('seopress-pro-page' == $key) {
                            foreach ($map['seopress-pro-page'] as $_value) {
                                add_filter('seopress_remove_feature_' . $_value, '__return_false');
                            }
                        }
                    }
                }
            }
        }
    }
}

//Change plugin title in plugins list
function seopress_white_label_plugin_list_title_option() {
    $seopress_white_label_plugin_list_title_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_plugin_list_title_option)) {
        foreach ($seopress_white_label_plugin_list_title_option as $key => $seopress_white_label_plugin_list_title_value) {
            $options[$key] = $seopress_white_label_plugin_list_title_value;
        }
        if (isset($seopress_white_label_plugin_list_title_option['seopress_white_label_plugin_list_title'])) {
            return $seopress_white_label_plugin_list_title_option['seopress_white_label_plugin_list_title'];
        }
    }
}

//Change plugin title in plugins list (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_plugin_list_title_option() {
        $seopress_mu_white_label_plugin_list_title_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_plugin_list_title_option)) {
            foreach ($seopress_mu_white_label_plugin_list_title_option as $key => $seopress_mu_white_label_plugin_list_title_value) {
                $options[$key] = $seopress_mu_white_label_plugin_list_title_value;
            }
            if (isset($seopress_mu_white_label_plugin_list_title_option['seopress_mu_white_label_plugin_list_title'])) {
                return $seopress_mu_white_label_plugin_list_title_option['seopress_mu_white_label_plugin_list_title'];
            }
        }
    }
}

//Change plugin title in plugins list (PRO)
function seopress_white_label_plugin_list_title_pro_option() {
    $seopress_white_label_plugin_list_title_pro_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_plugin_list_title_pro_option)) {
        foreach ($seopress_white_label_plugin_list_title_pro_option as $key => $seopress_white_label_plugin_list_title_pro_value) {
            $options[$key] = $seopress_white_label_plugin_list_title_pro_value;
        }
        if (isset($seopress_white_label_plugin_list_title_pro_option['seopress_white_label_plugin_list_title_pro'])) {
            return $seopress_white_label_plugin_list_title_pro_option['seopress_white_label_plugin_list_title_pro'];
        }
    }
}

//Change plugin title in plugins list (PRO) - (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_plugin_list_title_pro_option() {
        $seopress_mu_white_label_plugin_list_title_pro_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_plugin_list_title_pro_option)) {
            foreach ($seopress_mu_white_label_plugin_list_title_pro_option as $key => $seopress_mu_white_label_plugin_list_title_pro_value) {
                $options[$key] = $seopress_mu_white_label_plugin_list_title_pro_value;
            }
            if (isset($seopress_mu_white_label_plugin_list_title_pro_option['seopress_mu_white_label_plugin_list_title_pro'])) {
                return $seopress_mu_white_label_plugin_list_title_pro_option['seopress_mu_white_label_plugin_list_title_pro'];
            }
        }
    }
}

//Change plugin desc in plugins list
function seopress_white_label_plugin_list_desc_option() {
    $seopress_white_label_plugin_list_desc_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_plugin_list_desc_option)) {
        foreach ($seopress_white_label_plugin_list_desc_option as $key => $seopress_white_label_plugin_list_desc_value) {
            $options[$key] = $seopress_white_label_plugin_list_desc_value;
        }
        if (isset($seopress_white_label_plugin_list_desc_option['seopress_white_label_plugin_list_desc'])) {
            return $seopress_white_label_plugin_list_desc_option['seopress_white_label_plugin_list_desc'];
        }
    }
}

//Change plugin desc in plugins list (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_plugin_list_desc_option() {
        $seopress_mu_white_label_plugin_list_desc_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_plugin_list_desc_option)) {
            foreach ($seopress_mu_white_label_plugin_list_desc_option as $key => $seopress_mu_white_label_plugin_list_desc_value) {
                $options[$key] = $seopress_mu_white_label_plugin_list_desc_value;
            }
            if (isset($seopress_mu_white_label_plugin_list_desc_option['seopress_mu_white_label_plugin_list_desc'])) {
                return $seopress_mu_white_label_plugin_list_desc_option['seopress_mu_white_label_plugin_list_desc'];
            }
        }
    }
}

//Change plugin desc in plugins list (PRO)
function seopress_white_label_plugin_list_desc_pro_option() {
    $seopress_white_label_plugin_list_desc_pro_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_plugin_list_desc_pro_option)) {
        foreach ($seopress_white_label_plugin_list_desc_pro_option as $key => $seopress_white_label_plugin_list_desc_pro_value) {
            $options[$key] = $seopress_white_label_plugin_list_desc_pro_value;
        }
        if (isset($seopress_white_label_plugin_list_desc_pro_option['seopress_white_label_plugin_list_desc_pro'])) {
            return $seopress_white_label_plugin_list_desc_pro_option['seopress_white_label_plugin_list_desc_pro'];
        }
    }
}

//Change plugin desc in plugins list (PRO) - (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_plugin_list_desc_pro_option() {
        $seopress_mu_white_label_plugin_list_desc_pro_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_plugin_list_desc_pro_option)) {
            foreach ($seopress_mu_white_label_plugin_list_desc_pro_option as $key => $seopress_mu_white_label_plugin_list_desc_pro_value) {
                $options[$key] = $seopress_mu_white_label_plugin_list_desc_pro_value;
            }
            if (isset($seopress_mu_white_label_plugin_list_desc_pro_option['seopress_mu_white_label_plugin_list_desc_pro'])) {
                return $seopress_mu_white_label_plugin_list_desc_pro_option['seopress_mu_white_label_plugin_list_desc_pro'];
            }
        }
    }
}

//Change plugin author in plugins list
function seopress_white_label_plugin_list_author_option() {
    $seopress_white_label_plugin_list_author_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_plugin_list_author_option)) {
        foreach ($seopress_white_label_plugin_list_author_option as $key => $seopress_white_label_plugin_list_author_value) {
            $options[$key] = $seopress_white_label_plugin_list_author_value;
        }
        if (isset($seopress_white_label_plugin_list_author_option['seopress_white_label_plugin_list_author'])) {
            return $seopress_white_label_plugin_list_author_option['seopress_white_label_plugin_list_author'];
        }
    }
}

//Change plugin author in plugins list (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_plugin_list_author_option() {
        $seopress_mu_white_label_plugin_list_author_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_plugin_list_author_option)) {
            foreach ($seopress_mu_white_label_plugin_list_author_option as $key => $seopress_mu_white_label_plugin_list_author_value) {
                $options[$key] = $seopress_mu_white_label_plugin_list_author_value;
            }
            if (isset($seopress_mu_white_label_plugin_list_author_option['seopress_mu_white_label_plugin_list_author'])) {
                return $seopress_mu_white_label_plugin_list_author_option['seopress_mu_white_label_plugin_list_author'];
            }
        }
    }
}

//Change plugin website in plugins list
function seopress_white_label_plugin_list_website_option() {
    $seopress_white_label_plugin_list_website_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_plugin_list_website_option)) {
        foreach ($seopress_white_label_plugin_list_website_option as $key => $seopress_white_label_plugin_list_website_value) {
            $options[$key] = $seopress_white_label_plugin_list_website_value;
        }
        if (isset($seopress_white_label_plugin_list_website_option['seopress_white_label_plugin_list_website'])) {
            return $seopress_white_label_plugin_list_website_option['seopress_white_label_plugin_list_website'];
        }
    }
}

//Change plugin website in plugins list (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_plugin_list_website_option() {
        $seopress_mu_white_label_plugin_list_website_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_plugin_list_website_option)) {
            foreach ($seopress_mu_white_label_plugin_list_website_option as $key => $seopress_mu_white_label_plugin_list_website_value) {
                $options[$key] = $seopress_mu_white_label_plugin_list_website_value;
            }
            if (isset($seopress_mu_white_label_plugin_list_website_option['seopress_mu_white_label_plugin_list_website'])) {
                return $seopress_mu_white_label_plugin_list_website_option['seopress_mu_white_label_plugin_list_website'];
            }
        }
    }
}

if ('' != seopress_white_label_plugin_list_title_option() || function_exists('seopress_mu_white_label_plugin_list_title_option') && '' != seopress_mu_white_label_plugin_list_title_option()
|| '' != seopress_white_label_plugin_list_title_pro_option() || function_exists('seopress_mu_white_label_plugin_list_title_pro_option') && '' != seopress_mu_white_label_plugin_list_title_pro_option()
|| '' != seopress_white_label_plugin_list_desc_option() || function_exists('seopress_mu_white_label_plugin_list_desc_option') && '' != seopress_mu_white_label_plugin_list_desc_option()
|| '' != seopress_white_label_plugin_list_desc_pro_option() || function_exists('seopress_mu_white_label_plugin_list_desc_pro_option') && '' != seopress_mu_white_label_plugin_list_desc_pro_option()
|| '' != seopress_white_label_plugin_list_author_option() || function_exists('seopress_mu_white_label_plugin_list_author_option') && '' != seopress_mu_white_label_plugin_list_author_option()
|| '' != seopress_white_label_plugin_list_website_option() || function_exists('seopress_mu_white_label_plugin_list_website_option') && '' != seopress_mu_white_label_plugin_list_website_option()
) {
    add_filter('all_plugins', 'seopress_filter_plugins_list', 10, 1);
    function seopress_filter_plugins_list($data) {
        //SEOPress
        if (array_key_exists('wp-seopress/seopress.php', $data)) {
            //Title
            if (is_multisite() && '' != seopress_mu_white_label_plugin_list_title_option()) {
                $data['wp-seopress/seopress.php']['Name']  = seopress_mu_white_label_plugin_list_title_option();
                $data['wp-seopress/seopress.php']['Title'] = seopress_mu_white_label_plugin_list_title_option();
            } elseif ('' != seopress_white_label_plugin_list_title_option()) {
                $data['wp-seopress/seopress.php']['Name']  = seopress_white_label_plugin_list_title_option();
                $data['wp-seopress/seopress.php']['Title'] = seopress_white_label_plugin_list_title_option();
            }

            //Description
            if (is_multisite() && '' != seopress_mu_white_label_plugin_list_desc_option()) {
                $data['wp-seopress/seopress.php']['Description'] = seopress_mu_white_label_plugin_list_desc_option();
            } elseif ('' != seopress_white_label_plugin_list_desc_option()) {
                $data['wp-seopress/seopress.php']['Description'] = seopress_white_label_plugin_list_desc_option();
            }

            //Author
            if (is_multisite() && '' != seopress_mu_white_label_plugin_list_author_option()) {
                $data['wp-seopress/seopress.php']['Author']     = seopress_mu_white_label_plugin_list_author_option();
                $data['wp-seopress/seopress.php']['AuthorName'] = seopress_mu_white_label_plugin_list_author_option();
            } elseif ('' != seopress_white_label_plugin_list_author_option()) {
                $data['wp-seopress/seopress.php']['Author']     = seopress_white_label_plugin_list_author_option();
                $data['wp-seopress/seopress.php']['AuthorName'] = seopress_white_label_plugin_list_author_option();
            }

            //Website
            if (is_multisite() && '' != seopress_mu_white_label_plugin_list_website_option()) {
                $data['wp-seopress/seopress.php']['AuthorURI'] = seopress_mu_white_label_plugin_list_website_option();
            } elseif ('' != seopress_white_label_plugin_list_website_option()) {
                $data['wp-seopress/seopress.php']['AuthorURI'] = seopress_white_label_plugin_list_website_option();
            }
        }

        //SEOPress PRO
        if (array_key_exists('wp-seopress-pro/seopress-pro.php', $data)) {
            //Title
            if (is_multisite() && '' != seopress_mu_white_label_plugin_list_title_pro_option()) {
                $data['wp-seopress-pro/seopress-pro.php']['Name']  = seopress_mu_white_label_plugin_list_title_pro_option();
                $data['wp-seopress-pro/seopress-pro.php']['Title'] = seopress_mu_white_label_plugin_list_title_pro_option();
            } elseif ('' != seopress_white_label_plugin_list_title_pro_option()) {
                $data['wp-seopress-pro/seopress-pro.php']['Name']  = seopress_white_label_plugin_list_title_pro_option();
                $data['wp-seopress-pro/seopress-pro.php']['Title'] = seopress_white_label_plugin_list_title_pro_option();
            }

            //Description
            if (is_multisite() && '' != seopress_mu_white_label_plugin_list_desc_pro_option()) {
                $data['wp-seopress-pro/seopress-pro.php']['Description'] = seopress_mu_white_label_plugin_list_desc_pro_option();
            } elseif ('' != seopress_white_label_plugin_list_desc_pro_option()) {
                $data['wp-seopress-pro/seopress-pro.php']['Description'] = seopress_white_label_plugin_list_desc_pro_option();
            }

            //Author
            if (is_multisite() && '' != seopress_mu_white_label_plugin_list_author_option()) {
                $data['wp-seopress-pro/seopress-pro.php']['Author']     = seopress_mu_white_label_plugin_list_author_option();
                $data['wp-seopress-pro/seopress-pro.php']['AuthorName'] = seopress_mu_white_label_plugin_list_author_option();
            } elseif ('' != seopress_white_label_plugin_list_author_option()) {
                $data['wp-seopress-pro/seopress-pro.php']['Author']     = seopress_white_label_plugin_list_author_option();
                $data['wp-seopress-pro/seopress-pro.php']['AuthorName'] = seopress_white_label_plugin_list_author_option();
            }

            //Website
            if (is_multisite() && '' != seopress_mu_white_label_plugin_list_website_option()) {
                $data['wp-seopress-pro/seopress-pro.php']['AuthorURI'] = seopress_mu_white_label_plugin_list_website_option();
            } elseif ('' != seopress_white_label_plugin_list_website_option()) {
                $data['wp-seopress-pro/seopress-pro.php']['AuthorURI'] = seopress_white_label_plugin_list_website_option();
            }
        }

        return $data;
    }
}

//Remove View details in plugin list
function seopress_white_label_plugin_list_view_details_option() {
    $seopress_white_label_plugin_list_view_details_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_white_label_plugin_list_view_details_option)) {
        foreach ($seopress_white_label_plugin_list_view_details_option as $key => $seopress_white_label_plugin_list_view_details_value) {
            $options[$key] = $seopress_white_label_plugin_list_view_details_value;
        }
        if (isset($seopress_white_label_plugin_list_view_details_option['seopress_white_label_plugin_list_view_details'])) {
            return $seopress_white_label_plugin_list_view_details_option['seopress_white_label_plugin_list_view_details'];
        }
    }
}

//Remove View details in plugin list (multisite)
if (is_multisite()) {
    function seopress_mu_white_label_plugin_list_view_details_option() {
        $seopress_mu_white_label_plugin_list_view_details_option = get_blog_option(get_network()->site_id, 'seopress_pro_mu_option_name');
        if ( ! empty($seopress_mu_white_label_plugin_list_view_details_option)) {
            foreach ($seopress_mu_white_label_plugin_list_view_details_option as $key => $seopress_mu_white_label_plugin_list_view_details_value) {
                $options[$key] = $seopress_mu_white_label_plugin_list_view_details_value;
            }
            if (isset($seopress_mu_white_label_plugin_list_view_details_option['seopress_mu_white_label_plugin_list_view_details'])) {
                return $seopress_mu_white_label_plugin_list_view_details_option['seopress_mu_white_label_plugin_list_view_details'];
            }
        }
    }
}

if ('' != seopress_white_label_plugin_list_view_details_option() || function_exists('seopress_mu_white_label_plugin_list_view_details_option') && '' != seopress_mu_white_label_plugin_list_view_details_option()) {
    add_filter('plugin_row_meta', 'seopress_filter_plugins_list_meta', 10, 2);
    function seopress_filter_plugins_list_meta($links, $file) {
        if (false !== strpos($file, 'wp-seopress/seopress.php') || false !== strpos($file, 'wp-seopress-pro/seopress-pro.php')) {
            unset($links[2]);
        }

        return $links;
    }
}
