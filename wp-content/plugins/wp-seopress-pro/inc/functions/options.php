<?php
defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

///////////////////////////////////////////////////////////////////////////////////////////////////
//SEOPRESS Core
///////////////////////////////////////////////////////////////////////////////////////////////////

//Local Business
if ('1' == seopress_get_toggle_option('local-business')) {
    require_once dirname(__FILE__) . '/options-local-business.php'; //Local Business (required for Page Builders)

    //Register Local Business widget
    add_action('widgets_init', 'seopress_pro_lb_register_widget');
    function seopress_pro_lb_register_widget() {
        require_once dirname(__FILE__) . '/options-local-business-widget.php'; //Local Business
        register_widget('Local_Business_Widget');
    }
}

//WooCommerce
if ('1' == seopress_get_toggle_option('woocommerce')) {
    add_action('init', 'seopress_pro_woocommerce_sitemap', 0);
    function seopress_pro_woocommerce_sitemap() {
        if ( ! is_admin()) {
            require_once dirname(__FILE__) . '/options-woocommerce-sitemap.php'; //WooCommerce sitemap
        } else {
            require_once dirname(__FILE__) . '/options-woocommerce-admin.php'; //WooCommerce in admin
        }
    }
    //add_action('get_header', 'seopress_pro_woocommerce', 0);
    add_action('wp_head', 'seopress_pro_woocommerce', 0);
    function seopress_pro_woocommerce() {
        if ( ! is_admin()) {
            require_once dirname(__FILE__) . '/options-woocommerce.php'; //WooCommerce
        }
    }
}

//EDD
if ('1' == seopress_get_toggle_option('edd')) {
    add_action('get_header', 'seopress_pro_edd', 0);
    function seopress_pro_edd() {
        if ( ! is_admin()) {
            require_once dirname(__FILE__) . '/options-edd.php'; //EDD
        }
    }
}

//Dublin Core
if ('1' == seopress_get_toggle_option('dublin-core')) {
    add_action('wp_head', 'seopress_pro_dublin_core', 0);
    function seopress_pro_dublin_core() {
        if ( ! is_admin()) {
            if ((function_exists('is_wpforo_page') && is_wpforo_page()) || (class_exists('Ecwid_Store_Page') && Ecwid_Store_Page::is_store_page())) {//disable on wpForo pages to avoid conflicts
                //do nothing
            } else {
                require_once dirname(__FILE__) . '/options-dublin-core.php'; //Dublin Core
            }
        }
    }
}

//Rich Snippets
if ('1' == seopress_get_toggle_option('rich-snippets')) {
    add_action('wp_head', 'seopress_pro_rich_snippets', 2); // Must be !=0
    function seopress_pro_rich_snippets() {
        if ( ! is_admin()) {
            require_once dirname(__FILE__) . '/options-rich-snippets.php'; //Rich Snippets
            require_once dirname(__FILE__) . '/options-automatic-rich-snippets.php'; //Automatic Rich Snippets
        }
    }
    add_action('init', 'seopress_load_schemas_options', 9);
    function seopress_load_schemas_options() {
        require_once dirname(dirname(__FILE__)) . '/admin/schemas/schemas.php'; //Schemas
    }
    function seopress_pro_schemas_notice() {
        global $typenow;
        if (current_user_can(seopress_capability('manage_options', 'notice')) && (isset($typenow) && 'seopress_schemas' === $typenow)) {
            if (function_exists('seopress_rich_snippets_enable_option') && '1' != seopress_rich_snippets_enable_option()) {
                ?>
<div class="seopress-notice is-error">
    <p>
        <?php _e('Please enable <strong>Structured Data Types metabox for your posts, pages and custom post types</strong> option in order to use automatic schemas. (SEO > PRO > Structured Data Types (schema.org)', 'wp-seopress-pro'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=seopress-pro-page#tab=tab_seopress_rich_snippets')); ?>"
            class="btn btnPrimary"><?php _e('Fix this!', 'wp-seopress-pro'); ?></a>
    </p>
</div>
<?php
            }
        }
    }
    add_action('admin_notices', 'seopress_pro_schemas_notice');
}

//Breadcrumbs
if ('1' == seopress_get_toggle_option('breadcrumbs')) {
    //Breadcrumbs
    function seopress_breadcrumbs_enable_option() {
        $seopress_breadcrumbs_enable_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_breadcrumbs_enable_option)) {
            foreach ($seopress_breadcrumbs_enable_option as $key => $seopress_breadcrumbs_enable_value) {
                $options[$key] = $seopress_breadcrumbs_enable_value;
            }
            if (isset($seopress_breadcrumbs_enable_option['seopress_breadcrumbs_enable'])) {
                return $seopress_breadcrumbs_enable_option['seopress_breadcrumbs_enable'];
            }
        }
    }

    //Breadcrumbs JSON-LD
    function seopress_breadcrumbs_json_enable_option() {
        $seopress_breadcrumbs_json_enable_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_breadcrumbs_json_enable_option)) {
            foreach ($seopress_breadcrumbs_json_enable_option as $key => $seopress_breadcrumbs_json_enable_value) {
                $options[$key] = $seopress_breadcrumbs_json_enable_value;
            }
            if (isset($seopress_breadcrumbs_json_enable_option['seopress_breadcrumbs_json_enable'])) {
                return $seopress_breadcrumbs_json_enable_option['seopress_breadcrumbs_json_enable'];
            }
        }
    }
    //WooCommerce / Storefront with Breadcrumbs
    if ('1' == seopress_breadcrumbs_json_enable_option()) {
        add_action('init', 'seopress_pro_compatibility_wc');
        function seopress_pro_compatibility_wc() {
            //If WooCommerce, disable default JSON-LD Breadcrumbs to avoid conflicts
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            if (is_plugin_active('woocommerce/woocommerce.php')) {
                add_action('woocommerce_structured_data_breadcrumblist', '__return_false', 10, 2);
                remove_action('storefront_before_content', 'woocommerce_breadcrumb', 10);
            }
        }
    }
    require_once dirname(__FILE__) . '/options-breadcrumbs.php'; //Breadcrumbs
}

//RSS
add_action('init', 'seopress_pro_rss', 0);
function seopress_pro_rss() {
    if ( ! is_admin()) {
        require_once dirname(__FILE__) . '/options-rss.php'; //RSS
    }
}

//Rewrite
if ('1' == seopress_get_toggle_option('rewrite')) {
    add_action('init', 'seopress_pro_rewrite', 0);
    function seopress_pro_rewrite() {
        require_once dirname(__FILE__) . '/options-rewrite.php'; //Rewrite
    }
}

//White Label
if ('1' == seopress_get_toggle_white_label_option()) {
    if (is_admin() || is_network_admin()) {
        require_once dirname(__FILE__) . '/options-white-label.php'; //White Label
    }
}

//Robots
if (function_exists('seopress_get_toggle_option') && '1' == seopress_get_toggle_option('robots') || ( ! is_network_admin() && is_multisite())) {
    require_once dirname(__FILE__) . '/options-robots-txt.php'; //Robots.txt
}

//Video XML sitemaps
if ('1' === seopress_get_toggle_option('xml-sitemap') && '1' === seopress_get_service('SitemapOption')->isEnabled() && method_exists(seopress_get_service('SitemapOption'), 'videoIsEnabled') && '1' === seopress_get_service('SitemapOption')->videoIsEnabled()) {
    add_action('save_post', 'seopress_pro_video_xml_sitemap', 10, 3);
    function seopress_pro_video_xml_sitemap($post_id, $post, $update = '') {
        require_once dirname(__FILE__) . '/options-video-sitemap.php';
    }
}
