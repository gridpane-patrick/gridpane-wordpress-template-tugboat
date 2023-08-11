<?php

namespace SEOPressPro\Services\Forms\Schemas;

defined('ABSPATH') || exit;

use SEOPressPro\Core\FormApi;

class FormSchemaCourse extends FormApi {
    protected function getTypeByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_courses_title':
            case '_seopress_pro_rich_snippets_courses_desc':
            case '_seopress_pro_rich_snippets_courses_school':
            case '_seopress_pro_rich_snippets_courses_website':
                return 'input';
        }
    }

    protected function getLabelByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_courses_title':
                return __('Title', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_courses_desc':
                return __('Course description', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_courses_school':
                return __('School/Organization', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_courses_website':
                return __('School/Organization Website', 'wp-seopress-pro');
        }
    }

    protected function getPlaceholderByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_courses_title':
                return __('The title of your lesson, course...', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_courses_desc':
                return __('Enter your course/lesson description', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_courses_school':
                return __('Name of university, organization...', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_courses_website':
                return __('Enter the URL like https://example.com/', 'wp-seopress-pro');
            default:
                return '';
        }
    }

    protected function getDetails() {
        return [
            [
                'key' => '_seopress_pro_rich_snippets_courses_title',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_courses_desc',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_courses_school',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_courses_website',
            ],
        ];
    }
}
