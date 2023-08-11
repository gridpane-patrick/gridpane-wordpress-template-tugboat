<?php

namespace SEOPressPro\Actions\Api\Metas;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Core\Hooks\ExecuteHooks;

class VideoSitemap implements ExecuteHooks {
    public function hooks() {
        add_action('rest_api_init', [$this, 'register']);
    }

    /**
     * @since 5.0.0
     *
     * @return void
     */
    public function register() {
        register_rest_route('seopress/v1', '/posts/(?P<id>\d+)/video-sitemap', [
            'methods'             => 'GET',
            'callback'            => [$this, 'processGet'],
            'args'                => [
                'id' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                ],
            ],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('seopress/v1', '/posts/(?P<id>\d+)/video-sitemap', [
            'methods'             => 'PUT',
            'callback'            => [$this, 'processPut'],
            'args'                => [
                'id' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                ],
            ],
            'permission_callback' => function ($request) {
                $nonce = $request->get_header('x-wp-nonce');
                if ( ! wp_verify_nonce($nonce, 'wp_rest')) {
                    return false;
                }

                if(!current_user_can('edit_posts')){
                    return false;
                }

                return true;
            },
        ]);
    }

    /**
     * @since 5.1.0
     */
    public function processPut(\WP_REST_Request $request) {
        $id     = $request->get_param('id');
        $params = $request->get_params();

        try {
            if(isset($params['_seopress_video_disabled']) && $params['_seopress_video_disabled']){
                update_post_meta($id, '_seopress_video_disabled', 'yes');
            }
            else{
                delete_post_meta($id, '_seopress_video_disabled');
            }

            $videos = [];
            if(isset($params['videos'])){
                $videos = $params['videos'];
            }

            update_post_meta($id, '_seopress_video', $videos);

            return new \WP_REST_Response([
                'code' => 'success',
            ]);
        } catch (\Exception $e) {
            return new \WP_REST_Response([
                'code'         => 'error',
                'code_message' => 'execution_failed',
            ], 403);
        }
    }

    protected function getTypeByField($field){
        switch($field){
            case 'url':
            case 'title':
            case 'tag':
                return 'input';
            case 'desc':
                return 'textarea';
            case 'thumbnail':
                return 'upload';
            case 'internal_video';
            case 'family_friendly';
                return 'checkbox';
            case 'duration':
            case 'rating':
            case 'view_count':
                return 'number';

        }
    }

    protected function getLabelByField($field){
        switch($field){
            case 'url':
                return __('Video URL (required)', 'wp-seopress-pro');
            case 'title':
                return __('Video Title (required)', 'wp-seopress-pro');
            case 'desc':
                return __('Video Description (required)', 'wp-seopress-pro');
            case 'thumbnail':
                return __('Video Thumbnail (required)', 'wp-seopress-pro');
            case 'tag':
                return __('Video tags', 'wp-seopress-pro');
            case 'family_friendly';
                return __('NOT family friendly?', 'wp-seopress-pro');
            case 'internal_video';
                return __('NOT an external video (eg: video hosting on YouTube, Vimeo, Wistia...)? Check this if your video is hosting on this server.', 'wp-seopress-pro');
            case 'duration':
                return __("Video Duration (recommended)", 'wp-seopress-pro');
            case 'rating':
                return __("Video Rating", 'wp-seopress-pro');
            case 'view_count':
                return __("View count", 'wp-seopress-pro');

        }
    }

    protected function getDescriptionByField($field){
        switch($field){
            case 'url':
            case 'view_count':
                return '';
            case 'title':
                return __('Default: title tag, if not available, post title.', 'wp-seopress-pro');
            case 'desc':
                return __('2048 characters max.; default: meta description. If not available, use the beginning of the post content.', 'wp-seopress-pro');
            case 'thumbnail':
                return __('Minimum size: 160x90px (1920x1080 max), JPG, PNG or GIF formats. Default: your post featured image.', 'wp-seopress-pro');
            case 'tag':
                return __('32 tags max., separate tags with commas. Default: target keywords + post tags if available.', 'wp-seopress-pro');
            case 'internal_video_meta';
                return __('The video will be available only to users with SafeSearch turned off.?', 'wp-seopress-pro');
            case 'duration':
                return __("The duration of the video in seconds. Value must be between 0 and 28800 (8 hours).", 'wp-seopress-pro');
            case 'rating':
                return __("Allowed values are float numbers in the range 0.0 to 5.0.", 'wp-seopress-pro');

        }
    }

    /**
     * @since 5.1.0
     */
    public function processGet(\WP_REST_Request $request) {
        $id     = $request->get_param('id');
        $videos = get_post_meta($id, '_seopress_video', true);

        $fieldsVideo =  [
            'url',
            'view_count',
            'title',
            'desc',
            'thumbnail',
            'tag',
            'family_friendly',
            'internal_video',
            'duration',
            'rating',
        ];


        $dataVideos = [];

        foreach ($videos as $keyVideo => $video) {
            foreach ($video as $key => $value) {
                $field = [
                    'key' => $key,
                    'type' => $this->getTypeByField($key),
                    'label' => $this->getLabelByField($key),
                    'value' => $value,
                    'description' => $this->getDescriptionByField($key),
                    'visible' => true
                ];

                if($key === 'rating'){
                    $field['step'] = 0.1;
                    $field['min'] = 0;
                    $field['max'] = 5;
                }

                $dataVideos[$keyVideo][] = $field;
            }
        }

        foreach($fieldsVideo as $key=> $field){
            $bindField = [
                'key' => $field,
                'type' => $this->getTypeByField($field),
                'label' => $this->getLabelByField($field),
                'value' => '',
                'description' => $this->getDescriptionByField($field),
                'visible' => true
            ];

            if($field === 'rating'){
                $bindField['step'] = 0.1;
                $bindField['min'] = 0;
                $bindField['max'] = 5;
            }

            $fieldsVideo[$key] = $bindField;
        }


        $data = [
            [
                'key'                => '_seopress_video_disabled',
                'type'               => 'checkbox',
                'placeholder'        => '',
                'use_default'        => '',
                'default'            => '',
                'value'              => !empty(get_post_meta($id, '_seopress_video_disabled', true)),
                'label'              => __('Exclude this post from Video Sitemap?', 'wp-seopress-pro'),
                'visible'            => true,
                'description'        => __('YouTube videos are automatically added when you create / save a post, page or post type. If your post is set to noindex, it will be automatically excluded from the sitemap.', 'wp-seopress-pro'),
            ],
            [
                'key' => 'videos',
                'visible' => true,
                'type' => 'repeater',
                'data' => $dataVideos
            ]
        ];

        return new \WP_REST_Response(["data" => $data, "fields_video" => $fieldsVideo]);
    }
}
