<?php

namespace SEOPressPro\Services\Forms\Schemas;

defined('ABSPATH') || exit;

use SEOPressPro\Core\FormApi;

class FormSchemaHowTo extends FormApi {
    protected function getTypeByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_how_to_name':
            case '_seopress_pro_rich_snippets_how_to_desc':
            case '_seopress_pro_rich_snippets_how_to_currency':
            case '_seopress_pro_rich_snippets_how_to_cost':
            case '_seopress_pro_rich_snippets_how_to_total_time':
                return 'input';
            case '_seopress_pro_rich_snippets_how_to_img':
                return 'upload';
            case '_seopress_pro_rich_snippets_how_to':
                return 'repeater_how_to';
        }
    }

    protected function getLabelByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_how_to_name':
                return __('Title of the how-to', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_how_to_desc':
                return __('How-to description (default excerpt, or beginning of the content)', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_how_to_currency':
                return __('Currency', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_how_to_cost':
                return __('Estimated cost', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_how_to_total_time':
                return __('Total time needed', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_how_to_img':
                return __('Image thumbnail', 'wp-seopress-pro');
        }
    }

    protected function getPlaceholderByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_how_to_name':
                return __('The name of your how-to', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_how_to_desc':
                return __('Enter your how-to description', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_how_to_currency':
                return __('The currency of the estimated cost', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_how_to_cost':
                return __('The estimated cost', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_how_to_total_time':
                return __('Eg: HH:MM:SS', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_how_to_img':
                return __('Select your image', 'wp-seopress-pro');
            default:
                return '';
        }
    }

    protected function getDescriptionByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_how_to_img':
                return __('Minimum width: 720px - Recommended size: 1920px -  .jpg, .png, or. gif format - crawlable and indexable', 'wp-seopress-pro');
        }
    }

    protected function getDetails() {
        return [
            [
                'key' => '_seopress_pro_rich_snippets_how_to_name',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_how_to_desc',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_how_to_img',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_how_to_cost',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_how_to_currency',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_how_to_total_time',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_how_to',
            ],
        ];
    }
}
