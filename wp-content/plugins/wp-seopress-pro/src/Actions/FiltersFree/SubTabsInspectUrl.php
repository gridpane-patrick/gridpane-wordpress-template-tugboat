<?php

namespace SEOPressPro\Actions\FiltersFree;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Core\Hooks\ExecuteHooks;

class SubTabsInspectUrl implements ExecuteHooks {
    public function hooks() {
        add_filter('seopress_active_inspect_url', '__return_true');
    }
}
