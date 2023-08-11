<?php

namespace SEOPressPro\JsonSchemas;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Helpers\RichSnippetType;
use SEOPress\JsonSchemas\Image;
use SEOPress\Models\GetJsonData;
use SEOPressPro\Models\JsonSchemaValue;

class Review extends JsonSchemaValue implements GetJsonData {
    const NAME = 'review';

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
            'item'         => '_seopress_pro_rich_snippets_review_item',
            'itemType'     => '_seopress_pro_rich_snippets_review_item_type',
            'image'        => '_seopress_pro_rich_snippets_review_img',
            'ratingValue'  => '_seopress_pro_rich_snippets_review_rating',
            'bestRating'   => '_seopress_pro_rich_snippets_review_max_rating',
            'reviewBody'   => '_seopress_pro_rich_snippets_review_body',
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

        if (empty($variables['itemType'])) {
            $variables['itemType'] = 'Thing';
        }

        $variables['ratingAuthor'] = '%%post_author%%';

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

        $variables  = $this->getVariablesByType($typeSchema, $context);

        if (isset($variables['item'])) {
            $data['itemReviewed'] = [
                '@type'  => isset($variables['itemType']) ? $variables['itemType'] : '',
                'name'   => $variables['item'],
                'author' => isset($variables['ratingAuthor']) ? $variables['ratingAuthor'] : '',
            ];
        }

        if (isset($variables['image'])) {
            $contextWithVariables              = $context;
            $contextWithVariables['variables'] = [
                'url'    => $variables['image'],
            ];
            $schema                            = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(Image::NAME, $contextWithVariables, ['remove_empty'=> true]);
            if (count($schema) > 1) {
                $data['image'] = $schema;
            }
        }

        if (isset($variables['ratingValue'])) {
            $contextWithVariables              = $context;
            $contextWithVariables['variables'] = [
                'ratingValue'  => $variables['ratingValue'],
                'bestRating'  => isset($variables['bestRating']) ? $variables['bestRating'] : '',
                'worstRating'  => empty($variables['bestRating']) ? '' : 1
            ];
            $contextWithVariables['type']      = RichSnippetType::SUB_TYPE;
            $schema                            = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(Rating::NAME, $contextWithVariables, ['remove_empty'=> true]);
            if (count($schema) > 1) {
                $data['reviewRating'] = $schema;
            }
        }

        if (isset($variables['ratingAuthor'])) {
            $contextWithVariables              = $context;
            $contextWithVariables['variables'] = [
                'name'  => $variables['ratingAuthor'],
            ];
            $contextWithVariables['type']      = RichSnippetType::SUB_TYPE;
            $schema                            = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(Person::NAME, $contextWithVariables, ['remove_empty'=> true]);
            if (count($schema) > 1) {
                $data['author'] = $schema;
            }
        }

        if (isset($variables['reviewBody'])) {
            $data['reviewBody'] = $variables['reviewBody'];
        }

        if (isset($context['post']->ID)) {
            $variables['datePublished'] = get_the_date('c', $context['post']->ID);
        }

        return apply_filters('seopress_pro_get_json_data_review', $data, $context);
    }
}
