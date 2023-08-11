<?php

namespace SEOPressPro\Actions\Sitemap;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

use SEOPress\Core\Hooks\ExecuteHooks;

class RouterVideoSitemap implements ExecuteHooks {
    /**
     * @since 4.3.0
     *
     * @return void
     */
    public function hooks() {
        add_action('init', [$this, 'init']);
        add_action('query_vars', [$this, 'queryVars']);
    }

    /**
     * @since 4.3.0
     * @see init
     *
     * @return void
     */
    public function init() {
        if ('1' !== seopress_xml_sitemap_video_enable_option()) {
            return;
        }
        if (empty(seopress_xml_sitemap_video_enable_option())) {
            return;
        }

        $matches[2] = '';
        add_rewrite_rule('sitemaps/video([0-9]+)?.xml$', 'index.php?seopress_video=1&seopress_paged=' . $matches[2], 'top');
    }

    /**
     * @since 4.3.0
     * @see query_vars
     *
     * @param array $vars
     *
     * @return array
     */
    public function queryVars($vars) {
        if ('1' !== seopress_xml_sitemap_video_enable_option()) {
            return $vars;
        }
        if (empty(seopress_xml_sitemap_video_enable_option())) {
            return $vars;
        }

        $vars[] = 'seopress_video';

        return $vars;
    }
}
