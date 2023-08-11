<?php

namespace SEOPressPro\JsonSchemas;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Helpers\RichSnippetType;
use SEOPress\JsonSchemas\Organization;
use SEOPress\Models\GetJsonData;
use SEOPressPro\Models\JsonSchemaValue;

class Course extends JsonSchemaValue implements GetJsonData {
    const NAME = 'course';

    const ALIAS = ['courses'];

    protected function getName() {
        return self::NAME;
    }

    /**
     * @since 4.6.0
     *
     * @return array
     *
     * @param array $schemaManual
     */
    protected function getVariablesForManualSnippet($schemaManual) {
        $keys = [
            'type'           => '_seopress_pro_rich_snippets_type',
            'name'           => '_seopress_pro_rich_snippets_courses_title',
            'description'    => '_seopress_pro_rich_snippets_courses_desc',
            'school'         => '_seopress_pro_rich_snippets_courses_school',
            'website'        => '_seopress_pro_rich_snippets_courses_website',
        ];
        $variables = [];

        foreach ($keys as $key => $value) {
            $variables[$key] = isset($schemaManual[$value]) ? $schemaManual[$value] : '';
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

        $variables = [];

        switch ($typeSchema) {
            case RichSnippetType::MANUAL:
                $schemaManual = $this->getCurrentSchemaManual($context);

                if (null === $schemaManual) {
                    return $data;
                }

                $variables = $this->getVariablesForManualSnippet($schemaManual);
                break;
        }

        if (isset($variables['school'])) {
            $variablesSchema = [
                'type'    => 'Organization',
                'name'    => $variables['school'],
            ];
            $contextWithVariables              = $context;
            $contextWithVariables['variables'] = $variablesSchema;
            $contextWithVariables['type']      = RichSnippetType::SUB_TYPE;
            $schema                            = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(Organization::NAME, $contextWithVariables, ['remove_empty'=> true]);
            if (count($schema) > 1) {
                $data['provider'] = $schema;

                if (isset($variables['website'])) {
                    $data['provider']['sameAs'] = $variables['website'];
                }
            }
        }

        $data = seopress_get_service('VariablesToString')->replaceDataToString($data, $variables);

        return apply_filters('seopress_pro_get_json_data_course', $data, $context);
    }

    public function cleanValues($data) {
        if (isset($data['provider']['@context'])) {
            unset($data['provider']['@context']);
        }

        if (isset($data['provider']['contactPoint'])) {
            unset($data['provider']['contactPoint']);
        }

        return $data;
    }
}
