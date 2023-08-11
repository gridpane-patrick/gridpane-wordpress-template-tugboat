<?php

namespace SEOPressPro\Services\Forms\Schemas;

defined('ABSPATH') || exit;

use SEOPressPro\Core\FormApi;

class FormSchemaVideo extends FormApi {
    protected function getTypeByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_videos_img':
                return 'upload';
            case '_seopress_pro_rich_snippets_videos_description':
                return 'textarea';
            case '_seopress_pro_rich_snippets_videos_name':
            case '_seopress_pro_rich_snippets_videos_duration':
            case '_seopress_pro_rich_snippets_videos_url':
                return 'input';
        }
    }

    protected function getLabelByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_videos_name':
                return __('Video name', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_videos_description':
                return __('Video description', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_videos_img':
                return __('Video thumbnail', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_videos_duration':
                return __('Duration of your video (format: hh:mm:ss)', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_videos_url':
                return __('Video URL', 'wp-seopress-pro');
        }
    }

    protected function getPlaceholderByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_videos_name':
                return __('The title of your video', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_videos_description':
                return __('The description of the video', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_videos_duration':
                return __('eg: 00:04:30 for 4 minutes and 30 seconds', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_videos_url':
                return __('Eg: https://example.com/video.mp4', 'wp-seopress-pro');
        }
    }

    protected function getDescriptionByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_videos_img':
                return __('Minimum size: 160px by 90px - Max size: 1920x1080px - crawlable and indexable', 'wp-seopress-pro');
        }
    }

    protected function getDetails() {
        return [
            [
                'key' => '_seopress_pro_rich_snippets_videos_name',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_videos_description',
                'class' => 'seopress-textarea-high-size'
            ],
            [
                'key' => '_seopress_pro_rich_snippets_videos_img',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_videos_duration',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_videos_url',
            ],
        ];
    }
}
