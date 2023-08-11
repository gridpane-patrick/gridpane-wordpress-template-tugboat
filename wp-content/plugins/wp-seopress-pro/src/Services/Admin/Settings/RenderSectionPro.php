<?php

namespace SEOPressPro\Services\Admin\Settings;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

class RenderSectionPro
{
    /**
     * @since 4.5.0
     *
     * @param string $key
     */
    public function render($key)
    {
        $docs     = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';

        $breadcrumbs_desc = __('Configure your breadcrumbs, using schema.org markup, allowing it to appear in Google\'s search results.', 'wp-seopress-pro') . ' <a class="seopress-help" href="https://developers.google.com/search/docs/data-types/breadcrumb" target="_blank" title="' . __('Google developers website (new window)', 'wp-seopress-pro') . '">' . __('Lean more on Google developers website', 'wp-seopress-pro') . '</a><span class="seopress-help dashicons dashicons-external"></span>';

        $sections = [
            'local-business'=> [
                'toggle' => 1,
                'title'  => __('Local Business', 'wp-seopress-pro'),
                'desc'   => sprintf(__('Local Business data type for Google. This schema will be displayed on the homepage. <br>You can also display these informations using our <a href="%1$s">Local Business widget</a> or Local Business block to optimize your site for <a class="seopress-help" href="%2$s" target="_blank" title="' . __('Optimizing WordPress sites for Google EAT (new window)', 'wp-seopress-pro') . '">Google EAT</a><span class="seopress-help dashicons dashicons-external"></span>.', 'wp-seopress-pro'), admin_url('widgets.php'), $docs['lb']['eat']),
            ],
            'edd'=> [
                'toggle' => 1,
                'title'  => __('Easy Digital Downloads', 'wp-seopress-pro'),
                'desc'   => __('Improve Easy Digital Downloads SEO.', 'wp-seopress-pro'),
            ],
            'woocommerce'=> [
                'toggle' => 1,
                'title'  => __('WooCommerce', 'wp-seopress-pro'),
                'desc'   => __('Improve WooCommerce SEO. By enabling this feature, we‘ll automatically add <strong>product identifiers type</strong> and <strong>product identifiers value</strong> fields to the WooCommerce product metabox (barcode) for the Product schema.', 'wp-seopress-pro'),
            ],
            'dublin-core'=> [
                'toggle' => 1,
                'title'  => __('Dublin Core', 'wp-seopress-pro'),
                'desc'   => __('Dublin Core is a set of meta tags to describe your content.<br> These tags are automatically generated. Recognized by states / governements, they are used by directories, Bing, Baidu and Yandex.', 'wp-seopress-pro'),
            ],
            'rich-snippets'=> [
                'toggle' => 1,
                'title'  => __('Structured Data Types (schema.org)', 'wp-seopress-pro'),
                'desc'   => __('Add Structured Data Types support, mark your content, and get better Google Search Results.', 'wp-seopress-pro'),
            ],
            'page-speed'=> [
                'title' => __('PageSpeed Insights', 'wp-seopress-pro'),
                'desc'  => __('Check your site performance with Google PageSpeed Insights.', 'wp-seopress-pro'),
            ],
            'robots'=> [
                'toggle' => 1,
                'title'  => __('robots.txt', 'wp-seopress-pro'),
                'desc'   => __('Configure your virtual robots.txt file.', 'wp-seopress-pro'),
            ],
            'news'=> [
                'toggle' => 1,
                'title'  => __('Google News', 'wp-seopress-pro'),
                'desc'   => __('Enable your Google News Sitemap.', 'wp-seopress-pro'),
            ],
            '404'=> [
                'toggle' => 1,
                'title'  => __('404 monitoring / Redirections', 'wp-seopress-pro'),
                'desc'   => __('Monitor 404 urls in your Dashboard. Crawlers (robots/spiders) will be automatically exclude (eg: Google Bot, Yahoo, Bing...).', 'wp-seopress-pro'),
            ],
            'htaccess'=> [
                'title' => __('.htaccess', 'wp-seopress-pro'),
                'desc'  => __('Edit your htaccess file.', 'wp-seopress-pro'),
            ],
            'rss'=> [
                'title' => __('RSS feeds', 'wp-seopress-pro'),
                'desc'  => sprintf(__('Configure WordPress default feeds. <br><br><a href="%s" class="btn btnSecondary" target="_blank">View my RSS feed</a>', 'wp-seopress-pro'), get_home_url() . '/feed'),
            ],
            'rewrite'=> [
                'toggle' => 1,
                'title'  => __('Rewrite', 'wp-seopress-pro'),
                'desc'   => sprintf(__('Change the URL rewriting. To remove the <strong>/category/</strong> or <strong>/product-category/</strong> in URL, <a href="%s">click here</a>.', 'wp-seopress-pro'), admin_url('admin.php?page=seopress-advanced')),
            ],
            'white-label'=> [
                'toggle' => 1,
                'title'  => __('White Label', 'wp-seopress-pro'),
                'desc'   => __('Enable White Label. By enabling this feature, the <strong>"How-to get started notice"</strong> will be removed from the SEOPress dashboard.', 'wp-seopress-pro'),
            ],
            'breadcrumbs'=> [
                'toggle' => 1,
                'title'  => __('Breadcrumbs', 'wp-seopress-pro'),
                'desc'   => $breadcrumbs_desc,
            ],
        ];

        if (! empty($sections)) {
            if ('1' == seopress_get_toggle_option($key)) {
                $seopress_get_toggle_option = '1';
            } else {
                $seopress_get_toggle_option = '0';
            } ?>
<div class="sp-section-header">
    <h2>
        <?php echo $sections[$key]['title']; ?>
    </h2>

    <?php if (! empty($sections[$key]['toggle']) && 1 == $sections[$key]['toggle']) { ?>
    <div class="wrap-toggle-checkboxes">
        <input type="checkbox" name="toggle-<?php echo $key; ?>"
            id="toggle-<?php echo $key; ?>" class="toggle"
            data-toggle="<?php echo $seopress_get_toggle_option; ?>">
        <label for="toggle-<?php echo $key; ?>"></label>

        <?php
                if ('1' == $seopress_get_toggle_option) { ?>
        <span id="<?php echo $key; ?>-state-default"
            class="feature-state">
            <span class="dashicons dashicons-arrow-left-alt"></span>
            <?php _e('Click to disable this feature', 'wp-seopress-pro'); ?>
        </span>
        <span id="<?php echo $key; ?>-state"
            class="feature-state feature-state-off">
            <span class="dashicons dashicons-arrow-left-alt"></span>
            <?php _e('Click to enable this feature', 'wp-seopress-pro'); ?>
        </span>
        <?php } else { ?>
        <span id="<?php echo $key; ?>-state-default"
            class="feature-state">
            <span class="dashicons dashicons-arrow-left-alt"></span>
            <?php _e('Click to enable this feature', 'wp-seopress-pro'); ?>
        </span>
        <span id="<?php echo $key; ?>-state"
            class="feature-state feature-state-off">
            <span class="dashicons dashicons-arrow-left-alt"></span>
            <?php _e('Click to disable this feature', 'wp-seopress-pro'); ?>
        </span>
        <?php } ?>
    </div>
    <?php } ?>
</div>
<p>
    <?php echo $sections[$key]['desc']; ?>
</p>
<?php
        }
    }
}
