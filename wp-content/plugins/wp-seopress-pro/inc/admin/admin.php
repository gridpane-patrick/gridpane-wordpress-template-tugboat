<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

class seopress_pro_options
{
    /**
     * Holds the values to be used in the fields callbacks.
     */
    private $options;

    /**
     * Start up.
     */
    public function __construct()
    {
        //License activation / deactivation
        require_once dirname(__FILE__) . '/callbacks/License.php';

        add_action('admin_menu', [$this, 'add_plugin_page'], 20);
        add_action('admin_init', [$this, 'pro_set_default_values'], 10);
        add_action('network_admin_menu', [$this, 'add_network_plugin_page'], 10);
        add_action('admin_init', [$this, 'page_init']);

        add_action('admin_init', [$this, 'seopress_feature_save'], 30);
        add_action('admin_init', [$this, 'seopress_feature_title'], 20);
        add_action('admin_init', [$this, 'load_sections'], 30);
        add_action('admin_init', [$this, 'load_callbacks'], 40);
    }

    public function seopress_feature_save()
    {
        $html = '';
        if (isset($_GET['settings-updated']) && 'true' === $_GET['settings-updated']) {
            $html .= '<div id="seopress-notice-save" class="sp-components-snackbar-list">';
        } else {
            $html .= '<div id="seopress-notice-save" class="sp-components-snackbar-list" style="display:none">';
        }
        $html .= '<div class="sp-components-snackbar">
                <div class="sp-components-snackbar__content">
                    <span class="dashicons dashicons-yes"></span>
                    ' . __('Your settings have been saved.', 'wp-seopress-pro') . '
                </div>
            </div>
        </div>';

        return $html;
    }

    public function seopress_feature_title($feature)
    {
        global $title;

        $html = '<h1>' . $title;

        if (null !== $feature) {
            if ('1' == seopress_get_toggle_option($feature)) {
                $toggle = '"1"';
            } else {
                $toggle = '"0"';
            }

            $html .= '<input type="checkbox" name="toggle-' . $feature . '" id="toggle-' . $feature . '" class="toggle" data-toggle=' . $toggle . '>';
            $html .= '<label for="toggle-' . $feature . '"></label>';

            $html .= $this->seopress_feature_save();

            if ('1' == seopress_get_toggle_option($feature)) {
                $html .= '<span id="titles-state-default" class="feature-state"><span class="dashicons dashicons-arrow-left-alt"></span>' . __('Click to disable this feature', 'wp-seopress-pro') . '</span>';
                $html .= '<span id="titles-state" class="feature-state feature-state-off"><span class="dashicons dashicons-arrow-left-alt"></span>' . __('Click to enable this feature', 'wp-seopress-pro') . '</span>';
            } else {
                $html .= '<span id="titles-state-default" class="feature-state"><span class="dashicons dashicons-arrow-left-alt"></span>' . __('Click to enable this feature', 'wp-seopress-pro') . '</span>';
                $html .= '<span id="titles-state" class="feature-state feature-state-off"><span class="dashicons dashicons-arrow-left-alt"></span>' . __('Click to disable this feature', 'wp-seopress-pro') . '</span>';
            }
        }

        $html .= '<hr role="presentation"></h1>';

        return $html;
    }

    public function pro_set_default_values()
    {
        $seopress_pro_option_name = get_option('seopress_pro_option_name');

        //Init if option doesn't exist
        if (false === $seopress_pro_option_name) {
            $seopress_pro_option_name = [];
        }

        //WooCommerce==============================================================================
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            $seopress_pro_option_name['seopress_woocommerce_cart_page_no_index']             = '1';
            $seopress_pro_option_name['seopress_woocommerce_checkout_page_no_index']         = '1';
            $seopress_pro_option_name['seopress_woocommerce_customer_account_page_no_index'] = '1';
            $seopress_pro_option_name['seopress_woocommerce_product_og_price']               = '1';
            $seopress_pro_option_name['seopress_woocommerce_product_og_currency']            = '1';
            $seopress_pro_option_name['seopress_woocommerce_meta_generator']                 = '1';
        }

        //DublinCore===============================================================================
        $seopress_pro_option_name['seopress_dublin_core_enable'] = '1';

        //Check if the value is an array (important!)
        if (is_array($seopress_pro_option_name)) {
            add_option('seopress_pro_option_name', $seopress_pro_option_name);
        }

        //BOT======================================================================================
        $seopress_bot_option_name = get_option('seopress_bot_option_name');

        //Init if option doesn't exist
        if (false === $seopress_pro_option_name) {
            $seopress_pro_option_name = [];
        }

        $seopress_bot_option_name['seopress_bot_scan_settings_post_types']['post']['include'] = '1';
        $seopress_bot_option_name['seopress_bot_scan_settings_post_types']['page']['include'] = '1';
        $seopress_bot_option_name['seopress_bot_scan_settings_404']                           = '1';

        //Check if the value is an array (important!)
        if (is_array($seopress_bot_option_name)) {
            add_option('seopress_bot_option_name', $seopress_bot_option_name);
        }
    }

    /**
     * Add options page.
     */
    public function add_network_plugin_page()
    {
        if (has_filter('seopress_seo_admin_menu')) {
            $sp_seo_admin_menu['icon'] = '';
            $sp_seo_admin_menu['icon'] = apply_filters('seopress_seo_admin_menu', $sp_seo_admin_menu['icon']);
        } else {
            $sp_seo_admin_menu['icon'] = 'dashicons-admin-seopress';
        }

        $sp_seo_admin_menu['title'] = __('SEO', 'wp-seopress-pro');
        if (has_filter('seopress_seo_admin_menu_title')) {
            $sp_seo_admin_menu['title'] = apply_filters('seopress_seo_admin_menu_title', $sp_seo_admin_menu['title']);
        }

        add_menu_page(__('SEO Network settings', 'wp-seopress-pro'), $sp_seo_admin_menu['title'], seopress_capability('manage_options', 'menu'), 'seopress-network-option', [$this, 'create_network_admin_page'], $sp_seo_admin_menu['icon'], 90);
    }

    public function add_plugin_page()
    {
        add_submenu_page('seopress-option', __('PRO', 'wp-seopress-pro'), __('PRO', 'wp-seopress-pro'), seopress_capability('manage_options', 'pro'), 'seopress-pro-page', [$this, 'seopress_pro_page']);
        if ('1' == seopress_get_toggle_option('rich-snippets')) {
            add_submenu_page('seopress-option', __('Schemas', 'wp-seopress-pro'), __('Schemas', 'wp-seopress-pro'), seopress_capability('edit_schemas', 'menu'), 'edit.php?post_type=seopress_schemas', null);
        }
        if ('1' == seopress_get_toggle_option('404')) {
            add_submenu_page('seopress-option', __('Redirections', 'wp-seopress-pro'), __('Redirections', 'wp-seopress-pro'), seopress_capability('edit_redirections', 'menu'), 'edit.php?post_type=seopress_404', null);
        }
        if ('1' == seopress_get_toggle_option('bot')) {
            add_submenu_page('seopress-option', __('Broken links', 'wp-seopress-pro'), __('Broken links', 'wp-seopress-pro'), seopress_capability('manage_options', 'menu'), 'edit.php?post_type=seopress_bot', null);
        }
        $seopress_license_help_tab = add_submenu_page('seopress-option', __('License', 'wp-seopress-pro'), __('License', 'wp-seopress-pro'), seopress_capability('manage_options', 'menu'), 'seopress-license', [$this, 'seopress_license_page']);

        //Help tabs
        require_once dirname(__FILE__) . '/help-tabs/License.php';
    }



    public function seopress_pro_page()
    {
        require_once dirname(__FILE__) . '/admin-pages/Pro.php';
    }

    public function seopress_license_page()
    {
        require_once dirname(__FILE__) . '/admin-pages/License.php';
    }

    public function create_network_admin_page()
    {
        require_once dirname(__FILE__) . '/admin-pages/NetworkAdmin.php';
    }

    public function page_init()
    {
        require_once dirname(__FILE__) . '/settings/Main.php';
        require_once dirname(__FILE__) . '/settings/Bot.php';
        require_once dirname(__FILE__) . '/settings/WooCommerce.php';
        require_once dirname(__FILE__) . '/settings/EasyDigitalDownloads.php';
        require_once dirname(__FILE__) . '/settings/DublinCore.php';
        require_once dirname(__FILE__) . '/settings/Schemas.php';
        require_once dirname(__FILE__) . '/settings/Breadcrumbs.php';
        require_once dirname(__FILE__) . '/settings/PageSpeed.php';
        require_once dirname(__FILE__) . '/settings/InspectURL.php';
        require_once dirname(__FILE__) . '/settings/Robots.php';
        require_once dirname(__FILE__) . '/settings/GoogleNews.php';
        require_once dirname(__FILE__) . '/settings/Redirections.php';
        require_once dirname(__FILE__) . '/settings/Htaccess.php';
        require_once dirname(__FILE__) . '/settings/RSS.php';
        require_once dirname(__FILE__) . '/settings/Analytics.php';
        require_once dirname(__FILE__) . '/settings/AnalyticsEcommerce.php';
        require_once dirname(__FILE__) . '/settings/Security.php';
        require_once dirname(__FILE__) . '/settings/Rewrite.php';
        require_once dirname(__FILE__) . '/settings/WhiteLabel.php';
    }

    public function sanitize($input)
    {
        require_once dirname(__FILE__) . '/sanitize/Sanitize.php';

        return seopress_pro_sanitize_options_fields($input);
    }

    public function load_sections()
    {
        require_once dirname(__FILE__) . '/sections/Pro.php';
        require_once dirname(__FILE__) . '/sections/Bot.php';
        require_once dirname(__FILE__) . '/sections/WooCommerce.php';
        require_once dirname(__FILE__) . '/sections/EasyDigitalDownloads.php';
        require_once dirname(__FILE__) . '/sections/DublinCore.php';
        require_once dirname(__FILE__) . '/sections/Schemas.php';
        require_once dirname(__FILE__) . '/sections/Breadcrumbs.php';
        require_once dirname(__FILE__) . '/sections/PageSpeed.php';
        require_once dirname(__FILE__) . '/sections/InspectURL.php';
        require_once dirname(__FILE__) . '/sections/Robots.php';
        require_once dirname(__FILE__) . '/sections/GoogleNews.php';
        require_once dirname(__FILE__) . '/sections/Redirections.php';
        require_once dirname(__FILE__) . '/sections/Htaccess.php';
        require_once dirname(__FILE__) . '/sections/RSS.php';
        require_once dirname(__FILE__) . '/sections/Analytics.php';
        require_once dirname(__FILE__) . '/sections/AnalyticsEcommerce.php';
        require_once dirname(__FILE__) . '/sections/Rewrite.php';
        require_once dirname(__FILE__) . '/sections/WhiteLabel.php';
        require_once dirname(__FILE__) . '/sections/Advanced.php';
    }

    public function load_callbacks()
    {
        require_once dirname(__FILE__) . '/callbacks/Bot.php';
        require_once dirname(__FILE__) . '/callbacks/WooCommerce.php';
        require_once dirname(__FILE__) . '/callbacks/EasyDigitalDownloads.php';
        require_once dirname(__FILE__) . '/callbacks/DublinCore.php';
        require_once dirname(__FILE__) . '/callbacks/Schemas.php';
        require_once dirname(__FILE__) . '/callbacks/Breadcrumbs.php';
        require_once dirname(__FILE__) . '/callbacks/PageSpeed.php';
        require_once dirname(__FILE__) . '/callbacks/InspectURL.php';
        require_once dirname(__FILE__) . '/callbacks/Robots.php';
        require_once dirname(__FILE__) . '/callbacks/GoogleNews.php';
        require_once dirname(__FILE__) . '/callbacks/Redirections.php';
        require_once dirname(__FILE__) . '/callbacks/Htaccess.php';
        require_once dirname(__FILE__) . '/callbacks/RSS.php';
        require_once dirname(__FILE__) . '/callbacks/Analytics.php';
        require_once dirname(__FILE__) . '/callbacks/AnalyticsEcommerce.php';
        require_once dirname(__FILE__) . '/callbacks/Security.php';
        require_once dirname(__FILE__) . '/callbacks/Rewrite.php';
        require_once dirname(__FILE__) . '/callbacks/WhiteLabel.php';
    }
}

if (is_admin()) {
    $my_settings_page = new seopress_pro_options();
}
