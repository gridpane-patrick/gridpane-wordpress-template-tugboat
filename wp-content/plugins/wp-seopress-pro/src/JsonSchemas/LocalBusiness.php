<?php

namespace SEOPressPro\JsonSchemas;

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPress\Helpers\RichSnippetType;
use SEOPress\Models\GetJsonData;
use SEOPressPro\Models\JsonSchemaValue;

class LocalBusiness extends JsonSchemaValue implements GetJsonData {
    const NAME = 'local-business';

    const ALIAS = ['localbusiness'];

    protected function getName() {
        return self::NAME;
    }

    protected function getDayByKey($key) {
        switch ($key) {
            case 0:
                return 'Monday';
            case 1:
                return 'Tuesday';
            case 2:
                return 'Wednesday';
            case 3:
                return 'Thursday';
            case 4:
                return 'Friday';
            case 5:
                return 'Saturday';
            case 6:
                return 'Sunday';
        }
    }

    /**
     * @since 4.6.0
     *
     * @return array
     */
    protected function getKeysForOptionLocalBusiness() {
        return [
            'type' => '%%local_business_type%%',
            'image' => '%%social_knowledge_image%%',
            'id' => '%%siteurl%%',
            'name' => '%%social_knowledge_name%%',
            'url' => '%%local_business_url%%',
            'telephone' => '%%local_business_phone%%',
            'priceRange' => '%%local_business_price_range%%',
            'servesCuisines' => '%%local_business_cuisine%%',
            'acceptsReservations' => '%%local_business_accepts_reservations%%',
            'menu' => '%%local_business_menu%%',
        ];
    }

    /**
     * @since 4.7.0
     *
     * @return array
     */
    protected function getKeysForSchemaManual() {
        return [
            'type' => '_seopress_pro_rich_snippets_lb_type',
            'image' => '_seopress_pro_rich_snippets_lb_img',
            'url' => '_seopress_pro_rich_snippets_lb_website',
            'telephone' => '_seopress_pro_rich_snippets_lb_tel',
            'priceRange' => '_seopress_pro_rich_snippets_lb_price',
            'country' => '_seopress_pro_rich_snippets_lb_country',
            'postalCode' => '_seopress_pro_rich_snippets_lb_pc',
            'state' => '_seopress_pro_rich_snippets_lb_state',
            'city' => '_seopress_pro_rich_snippets_lb_city',
            'address' => '_seopress_pro_rich_snippets_lb_street_addr',
            'menu' => '_seopress_pro_rich_snippets_lb_menu',
            'acceptsReservations' => '_seopress_pro_rich_snippets_lb_accepts_reservations',
            'servesCuisines' => '_seopress_pro_rich_snippets_lb_cuisine',
            'name' => [
                'value' => '_seopress_pro_rich_snippets_lb_name',
                'default' => '%%sitetitle%%',
            ],
            'id' => [
                'default' => '%%schema_article_canonical%%',
            ],
            'openingHours' => '_seopress_pro_rich_snippets_lb_opening_hours',
        ];
    }

    /**
     * @since 4.7.0
     *
     * @return array
     */
    protected function getTypeFood() {
        return [
            'FoodEstablishment',
            'Bakery',
            'BarOrPub',
            'Brewery',
            'CafeOrCoffeeShop',
            'FastFoodRestaurant',
            'IceCreamShop',
            'Restaurant',
            'Winery',
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

        if (isset($variables['servesCuisines']) && ! in_array($variables['type'], $this->getTypeFood(), true)) {
            unset($variables['servesCuisines']);
        }

        if (isset($variables['openingHours']['seopress_local_business_opening_hours'])) {
            $variables['openingHours'] = $variables['openingHours']['seopress_local_business_opening_hours'];
        }

        return $variables;
    }

    /**
     * @since 4.5.0
     *
     * @param array $context
     *
     * @return array
     */
    public function getJsonData($context = null) {
        $data = $this->getArrayJson();

        $typeSchema = isset($context['type']) ? $context['type'] : RichSnippetType::OPTION_LOCAL_BUSINESS;

        $openingHours = [];
        $variables = $this->getVariablesByType($typeSchema, $context);

        if (RichSnippetType::OPTION_LOCAL_BUSINESS === $typeSchema) {
            $openingHours = seopress_pro_get_service('OptionPro')->getLocalBusinessOpeningHours();
        } elseif (isset($variables['openingHours'])) {
            $openingHours = $variables['openingHours'];
        }

        $data = seopress_get_service('VariablesToString')->replaceDataToString($data, $variables);

        $schema = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(PostalAddress::NAME, $context, ['remove_empty' => true]);

        if (count($schema) > 1) {
            $data['address'] = $schema;
        }

        $schema = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(Geo::NAME, $context, ['remove_empty' => true]);

        if (count($schema) > 1) {
            $data['geo'] = $schema;
        }

        if ( ! empty($openingHours)) {
            foreach ($openingHours as $key => $day) {
                if (isset($day['open']) && '1' === $day['open']) { // bad name => reality is closed
                    continue;
                }

                foreach ($day as $keyHalfDay => $halfDay) {
                    if ( ! isset($halfDay['open']) || '1' !== $halfDay['open']) {
                        continue;
                    }

                    $variablesOpeningHours = [
                        'dayOfWeek' => $this->getDayByKey($key),
                        'opens' => \sprintf('%s:%s:00', $halfDay['start']['hours'], $halfDay['start']['mins']),
                        'closes' => \sprintf('%s:%s:00', $halfDay['end']['hours'], $halfDay['end']['mins']),
                    ];

                    $schema = seopress_get_service('JsonSchemaGenerator')->getJsonFromSchema(OpeningHours::NAME, ['variables' => $variablesOpeningHours], ['remove_empty' => true]);
                    if (count($schema) > 1) {
                        $data['openingHoursSpecification'][] = $schema;
                    }
                }
            }
        }
        return apply_filters('seopress_pro_get_json_data_local_business', $data, $context);
    }

    public function cleanValues($data) {
        if (isset($data['@type']) && ! in_array($data['@type'], $this->getTypeFood(), true)) {
            $removeKeys = ['menu', 'acceptsReservations'];
            foreach ($removeKeys as $key => $value) {
                if (isset($data[$value])) {
                    unset($data[$value]);
                }
            }
        }

        return $data;
    }
}
