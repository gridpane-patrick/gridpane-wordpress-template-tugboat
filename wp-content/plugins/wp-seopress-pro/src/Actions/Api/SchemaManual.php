<?php

namespace SEOPressPro\Actions\Api;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Core\Hooks\ExecuteHooks;

class SchemaManual implements ExecuteHooks {
    public function hooks() {
        add_action('rest_api_init', [$this, 'register']);
    }

    public function register() {
        register_rest_route('seopress/v1', '/posts/(?P<id>\d+)/schemas-manual', [
            'methods' => 'GET',
            'callback' => [$this, 'processGet'],
            'args' => [
                'id' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                ],
            ],
            'permission_callback' => '__return_true',
        ]);
        register_rest_route('seopress/v1', '/posts/(?P<id>\d+)/schemas-manual', [
            'methods' => 'PUT',
            'callback' => [$this, 'processPut'],
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
    }

    public function cleanData($data) {
        if (empty($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if ('howto' === $value['_seopress_pro_rich_snippets_type']) {
                $data[$key]['_seopress_pro_rich_snippets_how_to'] = array_values($value['_seopress_pro_rich_snippets_how_to']);
            }
        }

        return $data;
    }

    public function processGet(\WP_REST_Request $request) {
        $id = $request->get_param('id');
        $data = get_post_meta($id, '_seopress_pro_schemas_manual', true);

        if (empty($data)) {
            $data = [];
        }

        $schemasAvailable = seopress_pro_get_service('FormSchemaAvailable')->getData();

        $fields = [];
        foreach ($schemasAvailable as $key => $item) {
            $class = new $item['class']();
            $fields[$item['type']] = $class->getFields();
        }

        $data = $this->cleanData($data);

        return new \WP_REST_Response(['data' => $data, 'fields' => $fields, 'schemas' => $schemasAvailable]);
    }

    public function processPut(\WP_REST_Request $request) {
        $id = $request->get_param('id');
        $params = $request->get_params();

        if ( ! isset($params['schemas'])) {
            return new \WP_REST_Response([
                'code' => 'error',
                'code_message' => 'missing_parameters',
            ], 403);
        }

        update_post_meta($id, '_seopress_pro_schemas_manual', $params['schemas']);

        return new \WP_REST_Response([
            'code' => 'success',
        ]);
    }
}
