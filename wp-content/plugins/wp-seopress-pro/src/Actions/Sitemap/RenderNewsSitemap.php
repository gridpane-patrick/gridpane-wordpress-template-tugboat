<?php

namespace SEOPressPro\Actions\Sitemap;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

use SEOPress\Core\Hooks\ExecuteHooksFrontend;

class RenderNewsSitemap implements ExecuteHooksFrontend {
    /**
     * @since 4.3.0
     *
     * @return void
     */
    public function hooks() {
        add_action('pre_get_posts', [$this, 'render'], 1);
    }

    /**
     * @since 4.3.0
     * @see @pre_get_posts
     *
     * @param Query $query
     *
     * @return void
     */
    public function render($query) {
        if ( ! $query->is_main_query()) {
            return;
        }

        if ('1' !== seopress_xml_sitemap_news_enable_option() || ! function_exists('seopress_get_toggle_option') || '1' !== seopress_get_toggle_option('news')) {
            return;
        }

        if ('1' === get_query_var('seopress_news')) {
            $filename = 'template-xml-sitemaps-news.php';
        }

        if (isset($filename) && file_exists(SEOPRESS_PRO_PLUGIN_DIR_PATH . 'inc/functions/google-news/' . $filename)) {
            include SEOPRESS_PRO_PLUGIN_DIR_PATH . 'inc/functions/google-news/' . $filename;
            exit();
        }
    }
}
