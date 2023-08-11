<?php

namespace SEOPressPro\JsonSchemas;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Helpers\RichSnippetType;
use SEOPress\Models\GetJsonData;
use SEOPressPro\Models\JsonSchemaValue;

class Product extends JsonSchemaValue implements GetJsonData {
    const NAME = 'product';

    const ALIAS = ['products'];

    protected function getName() {
        return self::NAME;
    }

    /**
     * @since 4.7.0
     *
     * @return array
     */
    protected function getKeysForSchemaManual() {
        return [
            'type' => '_seopress_pro_rich_snippets_type',
            'name' => [
                'value' => '_seopress_pro_rich_snippets_product_name',
                'default' => '%%post_title%%',
            ],
            'description' => [
                'value' => '_seopress_pro_rich_snippets_product_description',
                'default' => '%%post_excerpt%%',
            ],
            'image' => [
                'value' => '_seopress_pro_rich_snippets_product_img',
                'default' => '%%post_thumbnail_url%%',
            ],
            'price' => [
                'value' => '_seopress_pro_rich_snippets_product_price',
                'default' => '%%wc_get_price%%',
            ],
            'priceValidDate' => [
                'value' => '_seopress_pro_rich_snippets_product_price_valid_date',
                'default' => '%%wc_price_valid_date%%',
            ],
            'sku' => [
                'value' => '_seopress_pro_rich_snippets_product_sku',
                'default' => '%%wc_sku%%',
            ],
            'brand' => '_seopress_pro_rich_snippets_product_brand',
            'globalIds' => '_seopress_pro_rich_snippets_product_global_ids',
            'globalIdsValue' => '_seopress_pro_rich_snippets_product_global_ids_value',
            'priceCurrency' => '_seopress_pro_rich_snippets_product_price_currency',
            'condition' => [
                'value' => '_seopress_pro_rich_snippets_product_condition',
                'default' => 'NewCondition',
            ],
            'availability' => '_seopress_pro_rich_snippets_product_availability',
        ];
    }

    /**
     * @since 4.6.0
     *
     * @return array
     *
     * @param array $keys
     * @param array $data
     */
    protected function getVariablesByKeysAndData($keys, $data = []) {
        $variables = parent::getVariablesByKeysAndData($keys, $data);

        if ('none' === $variables['globalIds']) {
            $variables['globalIds'] = '';
        }
        if ('none' === $variables['priceCurrency']) {
            $variables['priceCurrency'] = '';
        }

        return $variables;
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

        $typeSchema = isset($context['type']) ? $context['type'] : RichSnippetType::MANUAL;

        $variables = $this->getVariablesByType($typeSchema, $context);

        if (isset($variables['globalIds'],$variables['globalIdsValue']) && ! empty($variables['globalIds']) && ! empty($variables['globalIdsValue'])) {
            $data[$variables['globalIds']] = $variables['globalIdsValue'];
        }

        if (isset($variables['brand'], $context['post']->ID) && ! empty($variables['brand'])) {
            $term_list = wp_get_post_terms($context['post']->ID, $variables['brand'], ['fields' => 'names']);

            if ( ! empty($term_list) && ! is_wp_error($term_list)) {
                $variables['brand'] = $term_list[0];
            } else {
                unset($variables['brand']);
            }
        }

        //brand
        if (isset($variables['brand'])) {
            $contextWithVariables = $context;
            $contextWithVariables['variables'] = [
                'name' => $variables['brand'],
            ];
            $contextWithVariables['type'] = RichSnippetType::SUB_TYPE;
            $schema = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(Brand::NAME, $contextWithVariables, ['remove_empty' => true]);
            if (count($schema) > 1) {
                $data['brand'] = $schema;
            }
        }

        if (isset($context['product']) && null !== $context['product'] && isset($context['post']->ID) && comments_open($context['post']->ID)) {
            $args = [
                'meta_key' => 'rating',
                'number' => 1,
                'status' => 'approve',
                'post_status' => 'publish',
                'parent' => 0,
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
                'post_id' => $context['post']->ID,
                'post_type' => 'product',
            ];

            $comments = get_comments($args);

            if ( ! empty($comments)) {
                $contextWithVariables = $context;
                $contextWithVariables['variables'] = [
                    'ratingValue' => get_comment_meta($comments[0]->comment_ID, 'rating', true),
                    'ratingAuthor' => get_comment_author($comments[0]->comment_ID),
                ];
                $contextWithVariables['type'] = RichSnippetType::SUB_TYPE;
                $schema = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(Review::NAME, $contextWithVariables, ['remove_empty' => true]);
                if (count($schema) > 1) {
                    $data['review'] = $schema;
                }
            }

            if (function_exists('wc_get_product')) {
                $product = wc_get_product($context['post']->ID);

                if (method_exists($product, 'get_review_count') && $product->get_review_count() >= 1) {
                    $contextWithVariables = $context;
                    $contextWithVariables['variables'] = [
                        'ratingValue' => $product->get_average_rating(),
                        'reviewCount' => $product->get_review_count(),
                    ];
                    $contextWithVariables['type'] = RichSnippetType::SUB_TYPE;
                    $schema = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(AggregateRating::NAME, $contextWithVariables, ['remove_empty' => true]);
                    if (count($schema) > 1) {
                        $data['aggregateRating'] = $schema;
                    }
                }
            }
        }

        if (isset($variables['price'])) {
            $contextWithVariables = $context;
            $contextWithVariables['variables'] = [
                'url' => '%%post_url%%',
                'priceCurrency' => isset($variables['priceCurrency']) ? $variables['priceCurrency'] : '',
                'price' => isset($variables['price']) ? $variables['price'] : '',
                'priceValidUntil' => isset($variables['priceValidDate']) ? $variables['priceValidDate'] : '',
                'itemCondition' => isset($variables['condition']) ? sprintf('%sschema.org/%s', seopress_check_ssl(), $variables['condition']) : sprintf('%sschema.org/%s', seopress_check_ssl(), $variables['NewCondition']),
                'availability' => isset($variables['availability']) ? sprintf('%sschema.org/%s', seopress_check_ssl(), $variables['availability']) : '',
            ];

            $contextWithVariables['type'] = RichSnippetType::SUB_TYPE;
            $schema = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(Offer::NAME, $contextWithVariables, ['remove_empty' => true]);
            if (count($schema) > 1) {
                $data['offers'] = $schema;
            }
        }

        $data = seopress_get_service('VariablesToString')->replaceDataToString($data, $variables);

        return apply_filters('seopress_pro_get_json_data_product', $data, $context);
    }

    /**
     * @since 4.6.0
     *
     * @param  $data
     *
     * @return array
     */
    public function cleanValues($data) {
        if (isset($data['review']) && isset($data['review']['@context'])) {
            unset($data['review']['@context']);
        }

        return parent::cleanValues($data);
    }
}
