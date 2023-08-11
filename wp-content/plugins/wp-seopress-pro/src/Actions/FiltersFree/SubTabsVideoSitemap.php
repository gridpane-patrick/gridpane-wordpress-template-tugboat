<?php

namespace SEOPressPro\Actions\FiltersFree;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Core\Hooks\ExecuteHooks;

class SubTabsVideoSitemap implements ExecuteHooks {
    public function hooks() {
        add_filter('seopress_active_video_sitemap', function () {
            if ( ! function_exists('seopress_xml_sitemap_video_enable_option')) {
                return true;
            }

            return seopress_xml_sitemap_video_enable_option() === '1';
        });
    }
}
