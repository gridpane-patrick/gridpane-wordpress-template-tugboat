<?php

namespace SEOPressPro\JsonSchemas;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Helpers\RichSnippetType;
use SEOPress\Models\GetJsonData;
use SEOPressPro\Models\JsonSchemaValue;

class SiteNavigationElement extends JsonSchemaValue implements GetJsonData {
    const NAME = 'site-navigation-element';

    const ALIAS = ['site-navigation'];

    protected function getName() {
        return self::NAME;
    }

    /**
     * @since 4.6.0
     *
     * @param array $context
     *
     * @return array
     */
    public function getJsonData($context = null) {
        $data = $this->getArrayJson();

        if ( ! function_exists('wp_get_nav_menu_items')) {
            return [];
        }

        $navItems  = seopress_pro_get_service('OptionPro')->getRichSnippetsSiteNavigation();

        $menuItems = wp_get_nav_menu_items($navItems);

        if (empty($menuItems)) {
            return [];
        }

        foreach ($menuItems as $item) {
            $data['name'][] = $item->title;
        }

        foreach ($menuItems as $item) {
            $data['name'][] = $item->url;
        }

        return apply_filters('seopress_pro_get_json_data_site_navigation_element', $data, $context);
    }
}
