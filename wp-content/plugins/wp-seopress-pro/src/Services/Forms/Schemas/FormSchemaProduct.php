<?php

namespace SEOPressPro\Services\Forms\Schemas;

defined('ABSPATH') || exit;

use SEOPressPro\Core\FormApi;
use SEOPressPro\Helpers\Schemas\Currencies;

class FormSchemaProduct extends FormApi {
    protected function getTypeByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_product_description':
                return 'textarea';
            case '_seopress_pro_rich_snippets_product_img':
                return 'upload';
            case '_seopress_pro_rich_snippets_product_price_valid_date':
                return 'date';
            case '_seopress_pro_rich_snippets_product_global_ids':
            case '_seopress_pro_rich_snippets_product_brand':
            case '_seopress_pro_rich_snippets_product_price_currency':
            case '_seopress_pro_rich_snippets_product_condition':
            case '_seopress_pro_rich_snippets_product_availability':
                return 'select';
            case '_seopress_pro_rich_snippets_product_name':
            case '_seopress_pro_rich_snippets_product_price':
            case '_seopress_pro_rich_snippets_product_sku':
            case '_seopress_pro_rich_snippets_product_global_ids_value':
                return 'input';
        }
    }

    protected function getLabelByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_product_name':
                return __('Product name', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_description':
                return __('Product description', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_img':
                return __('Thumbnail', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_price':
                return __('Product price', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_price_valid_date':
                return __('Product price valid until', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_sku':
                return __('Product SKU', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_brand':
                return __('Product Brand', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_global_ids':
                return __('Product Global Identifiers type', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_global_ids_value':
                return __('Product Global Identifier value', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_price_currency':
                return __('Product currency', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_condition':
                return __('Product Condition', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_availability':
                return __('Product Availability', 'wp-seopress-pro');
        }
    }

    protected function getPlaceholderByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_product_name':
                return __('The name of your product', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_description':
                return __('The description of the product', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_price':
                return __('Eg: 30', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_price_valid_date':
                return __('Eg: YYYY-MM-DD', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_sku':
                return __('Eg: 0446310786', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_global_ids_value':
                return __('Eg: 925872', 'wp-seopress-pro');
        }
    }

    protected function getDescriptionByField($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_product_name':
                return __('Default: product title', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_description':
                return __('Default: product excerpt', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_img':
                return __('Pictures clearly showing the product, e.g. against a white background, are preferred', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_price':
                return __('Default: active product price', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_price_valid_date':
                return __('Default: sale price dates To field', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_sku':
                return __('Default: product SKU', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_condition':
                return __('Default: new', 'wp-seopress-pro');
            case '_seopress_pro_rich_snippets_product_availability':
                return '';
        }
    }

    protected function getOptions($field) {
        switch ($field) {
            case '_seopress_pro_rich_snippets_product_global_ids':
                return [
                    ['value' => 'none', 'label' => __('Select a global identifier', 'wp-seopress-pro')],
                    ['value' => 'gtin8', 'label' => __('gtin8 (ean8)', 'wp-seopress-pro')],
                    ['value' => 'gtin12', 'label' => __('gtin12 (ean12)', 'wp-seopress-pro')],
                    ['value' => 'gtin13', 'label' => __('gtin13 (ean13)', 'wp-seopress-pro')],
                    ['value' => 'gtin14', 'label' => __('gtin14 (ean14)', 'wp-seopress-pro')],
                    ['value' => 'mpn', 'label' => __('mpn', 'wp-seopress-pro')],
                    ['value' => 'isbn', 'label' => __('isbn', 'wp-seopress-pro')],
                ];
            case '_seopress_pro_rich_snippets_product_brand':
                $data = [['value' => 'none', 'label' => __('Select a taxonomy', 'wp-seopress-pro')]];
                if ( ! function_exists('seopress_get_taxonomies')) {
                    return $data;
                }

                $taxonomies = seopress_get_taxonomies();
                if (empty($taxonomies)) {
                    return $data;
                }
                foreach ($taxonomies as $key => $value) {
                    $data[] = ['value' => $key, 'label' => $key];
                }

                return $data;
            case '_seopress_pro_rich_snippets_product_price_currency':
                return Currencies::getOptions();
            case '_seopress_pro_rich_snippets_product_condition':
                return [
                    ['value' => 'NewCondition', 'label' => __('New', 'wp-seopress-pro')],
                    ['value' => 'UsedCondition', 'label' => __('Used', 'wp-seopress-pro')],
                    ['value' => 'DamagedCondition', 'label' => __('Damaged', 'wp-seopress-pro')],
                    ['value' => 'RefurbishedCondition', 'label' => __('Refurbished', 'wp-seopress-pro')],
                ];
            case '_seopress_pro_rich_snippets_product_availability':
                return [
                    ['value' => 'InStock', 'label' => __('In Stock', 'wp-seopress-pro')],
                    ['value' => 'InStoreOnly', 'label' => __('In Store Only', 'wp-seopress-pro')],
                    ['value' => 'OnlineOnly', 'label' => __('Online Only', 'wp-seopress-pro')],
                    ['value' => 'LimitedAvailability', 'label' => __('Limited Availability', 'wp-seopress-pro')],
                    ['value' => 'SoldOut', 'label' => __('Sold Out', 'wp-seopress-pro')],
                    ['value' => 'OutOfStock', 'label' => __('Out Of Stock', 'wp-seopress-pro')],
                    ['value' => 'Discontinued', 'label' => __('Discontinued', 'wp-seopress-pro')],
                    ['value' => 'PreOrder', 'label' => __('Pre Order', 'wp-seopress-pro')],
                    ['value' => 'PreSale', 'label' => __('Pre Sale', 'wp-seopress-pro')],
                ];
        }
    }

    protected function getDetails() {
        return [
            [
                'key' => '_seopress_pro_rich_snippets_product_name',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_description',
                'class' => 'seopress-textarea-high-size'
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_img',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_price',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_price_valid_date',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_sku',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_global_ids',
                'value' => 'none',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_global_ids_value',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_brand',
                'value' => 'none',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_price_currency',
                'value' => 'none',
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_condition',
                'value' => 'NewCondition'
            ],
            [
                'key' => '_seopress_pro_rich_snippets_product_availability',
                'value' => 'InStock'
            ],
        ];
    }
}
