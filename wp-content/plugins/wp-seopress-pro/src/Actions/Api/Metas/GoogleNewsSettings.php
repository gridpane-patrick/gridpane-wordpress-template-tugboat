<?php

namespace SEOPressPro\Actions\Api\Metas;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Core\Hooks\ExecuteHooks;

class GoogleNewsSettings implements ExecuteHooks {
    public function hooks() {
        add_action('rest_api_init', [$this, 'register']);
    }

    /**
     * @since 5.0.0
     *
     * @return void
     */
    public function register() {
        register_rest_route('seopress/v1', '/posts/(?P<id>\d+)/google-news-settings', [
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

        register_rest_route('seopress/v1', '/posts/(?P<id>\d+)/google-news-settings', [
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
            if(isset($params['_seopress_news_disabled']) && $params['_seopress_news_disabled'] === 'yes'){
                update_post_meta($id, '_seopress_news_disabled', 'yes');
            }
            else{

                delete_post_meta($id, '_seopress_news_disabled');
            }


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

    /**
     * @since 5.1.0
     */
    public function processGet(\WP_REST_Request $request) {
        $id    = $request->get_param('id');

        $data = [
            [
                'key'                => '_seopress_news_disabled',
                'type'               => 'checkbox',
                'placeholder'        => '',
                'use_default'        => '',
                'default'            => '',
                'value'              => !empty(get_post_meta($id, '_seopress_news_disabled', true)),
                'label'              => __(' Exclude this post from Google News Sitemap?', 'wp-seopress-pro'),
                'visible'            => true,
                'description'        => '',
            ]
        ];

        return new \WP_REST_Response($data);
    }
}
