<?php

namespace SEOPressPro\Services\Forms\Schemas;

defined('ABSPATH') || exit;

use SEOPressPro\Core\FormApi;

class FormSchemaSotfware extends FormApi {
    protected function getTypeByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_softwareapp_cat':
                return 'select';
            case '_seopress_pro_rich_snippets_softwareapp_max_rating':
            case '_seopress_pro_rich_snippets_softwareapp_rating':
                return 'number';
            case '_seopress_pro_rich_snippets_softwareapp_name':
            case '_seopress_pro_rich_snippets_softwareapp_os':
            case '_seopress_pro_rich_snippets_softwareapp_price':
            case '_seopress_pro_rich_snippets_softwareapp_currency':
                return 'input';
        }
    }

    protected function getLabelByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_softwareapp_name':
                return __('Software name', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_os':
                return __('Operating system', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_cat':
                return __('Application category', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_price':
                return __('Price of your app', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_currency':
                return __('Currency', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_rating':
                return __('Your rating', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_max_rating':
                return __('Max best rating', 'wp-seopress-pro');
        }
    }

    protected function getPlaceholderByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_softwareapp_name':
                return __('The name of your app', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_os':
                return __('The operating system(s) required to use the app', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_price':
                return __('The price of your app (set "0" if the app is free of charge)', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_currency':
                return __('Currency: USD, EUR...', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_rating':
                return __('The item rating', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_softwareapp_max_rating':
                return __('Max best rating', 'wp-seopress-pro');
        }
    }

    protected function getOptions($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_softwareapp_cat':
                return [
                    ['value' => 'GameApplication', 'label' => __('GameApplication', 'wp-seopress-pro')],
                    ['value' => 'SocialNetworkingApplication', 'label' => __('SocialNetworkingApplication', 'wp-seopress-pro')],
                    ['value' => 'TravelApplication', 'label' => __('TravelApplication', 'wp-seopress-pro')],
                    ['value' => 'ShoppingApplication', 'label' => __('ShoppingApplication', 'wp-seopress-pro')],
                    ['value' => 'SportsApplication', 'label' => __('SportsApplication', 'wp-seopress-pro')],
                    ['value' => 'LifestyleApplication', 'label' => __('LifestyleApplication', 'wp-seopress-pro')],
                    ['value' => 'BusinessApplication', 'label' => __('BusinessApplication', 'wp-seopress-pro')],
                    ['value' => 'DesignApplication', 'label' => __('DesignApplication', 'wp-seopress-pro')],
                    ['value' => 'DeveloperApplication', 'label' => __('DeveloperApplication', 'wp-seopress-pro')],
                    ['value' => 'DriverApplication', 'label' => __('DriverApplication', 'wp-seopress-pro')],
                    ['value' => 'EducationalApplication', 'label' => __('EducationalApplication', 'wp-seopress-pro')],
                    ['value' => 'HealthApplication', 'label' => __('HealthApplication', 'wp-seopress-pro')],
                    ['value' => 'FinanceApplication', 'label' => __('FinanceApplication', 'wp-seopress-pro')],
                    ['value' => 'SecurityApplication', 'label' => __('SecurityApplication', 'wp-seopress-pro')],
                    ['value' => 'BrowserApplication', 'label' => __('BrowserApplication', 'wp-seopress-pro')],
                    ['value' => 'CommunicationApplication', 'label' => __('CommunicationApplication', 'wp-seopress-pro')],
                    ['value' => 'DesktopEnhancementApplication', 'label' => __('DesktopEnhancementApplication', 'wp-seopress-pro')],
                    ['value' => 'EntertainmentApplication', 'label' => __('EntertainmentApplication', 'wp-seopress-pro')],
                    ['value' => 'MultimediaApplication', 'label' => __('MultimediaApplication', 'wp-seopress-pro')],
                    ['value' => 'HomeApplication', 'label' => __('HomeApplication', 'wp-seopress-pro')],
                    ['value' => 'UtilitiesApplication', 'label' => __('UtilitiesApplication', 'wp-seopress-pro')],
                    ['value' => 'ReferenceApplication', 'label' => __('ReferenceApplication', 'wp-seopress-pro')],
                ];
        }
    }

    protected function getDetails() {
        return [
            [
                'key' => '_seopress_pro_rich_snippets_softwareapp_name',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_softwareapp_os',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_softwareapp_cat',
                'value' => 'GameApplication'
            ],
            [
                'key' => '_seopress_pro_rich_snippets_softwareapp_price',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_softwareapp_currency',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_softwareapp_rating',
                'min' => 1,
            ],
            [
                'key' => '_seopress_pro_rich_snippets_softwareapp_max_rating',
            ],
        ];
    }
}
