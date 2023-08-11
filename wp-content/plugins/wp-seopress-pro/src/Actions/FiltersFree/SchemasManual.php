<?php

namespace SEOPressPro\Actions\FiltersFree;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Core\Hooks\ExecuteHooks;

class SchemasManual implements ExecuteHooks {
    public function hooks() {
        if ('1' == seopress_rich_snippets_enable_option()) {
            add_filter('seopress_active_schemas_manual_universal_metabox', '__return_true');
        }
    }
}
