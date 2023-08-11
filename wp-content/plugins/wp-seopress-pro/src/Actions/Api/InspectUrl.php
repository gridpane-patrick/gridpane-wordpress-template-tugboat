<?php

namespace SEOPressPro\Actions\Api;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Core\Hooks\ExecuteHooks;

class InspectUrl implements ExecuteHooks {
    public function hooks() {
        add_action('rest_api_init', [$this, 'register']);
    }

    public function register() {
        register_rest_route('seopress/v1', '/posts/(?P<id>\d+)/inspect', [
            'methods' => 'GET',
            'callback' => [$this, 'processGet'],
            'args' => [
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

                if ( ! current_user_can('edit_posts')) {
                    return false;
                }

                return true;
            },
        ]);

        register_rest_route('seopress/v1', '/posts/(?P<id>\d+)/inspect', [
            'methods' => 'POST',
            'callback' => [$this, 'processPost'],
            'permission_callback' => function ($request) {
                $nonce = $request->get_header('x-wp-nonce');
                if ( ! wp_verify_nonce($nonce, 'wp_rest')) {
                    return false;
                }

                if ( ! current_user_can('edit_posts')) {
                    return false;
                }

                return true;
            },
        ]);
    }

    public function processGet(\WP_REST_Request $request) {
        $postId = $request->get_param('id');

        $data = get_post_meta($postId, '_seopress_gsc_inspect_url_data', true);

        if (is_string($data)) {
            $data = json_decode($data);
        }

        //Get Google API Key
        $options = get_option('seopress_instant_indexing_option_name');
        $google_api_key = isset($options['seopress_instant_indexing_google_api_key']) ? $options['seopress_instant_indexing_google_api_key'] : '';

        return new \WP_REST_Response(['google_api_key_is_empty' => empty($google_api_key), 'data' => $data]);
    }

    public function processPost(\WP_REST_Request $request) {
        $postId = $request->get_param('id');

        $data = seopress_pro_get_service('InspectUrlGoogle')->handle($postId);

        if (is_string($data)) {
            $data = json_decode($data);
        }

        return new \WP_REST_Response(['data' => $data]);
    }
}
