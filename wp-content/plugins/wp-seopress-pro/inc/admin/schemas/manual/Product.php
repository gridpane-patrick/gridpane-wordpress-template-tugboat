<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_get_schema_metaboxe_product($seopress_pro_rich_snippets_data, $key_schema = 0) {
    $options_currencies = seopress_get_options_schema_currencies();

    $seopress_pro_rich_snippets_product_name                        = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_name']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_name'] : '';
    $seopress_pro_rich_snippets_product_description                 = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_description']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_description'] : '';
    $seopress_pro_rich_snippets_product_img                         = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_img']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_img'] : '';
    $seopress_pro_rich_snippets_product_price                       = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_price']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_price'] : '';
    $seopress_pro_rich_snippets_product_price_valid_date            = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_price_valid_date']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_price_valid_date'] : '';
    $seopress_pro_rich_snippets_product_sku                         = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_sku']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_sku'] : '';
    $seopress_pro_rich_snippets_product_brand                       = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_brand']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_brand'] : '';
    $seopress_pro_rich_snippets_product_global_ids                  = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_global_ids']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_global_ids'] : '';
    $seopress_pro_rich_snippets_product_global_ids_value            = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_global_ids_value']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_global_ids_value'] : '';
    $seopress_pro_rich_snippets_product_price_currency              = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_price_currency']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_price_currency'] : '';
    $seopress_pro_rich_snippets_product_condition                   = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_condition']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_condition'] : '';
    $seopress_pro_rich_snippets_product_availability                = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_availability']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_product_availability'] : ''; ?>
<div class="wrap-rich-snippets-item wrap-rich-snippets-products">
    <div class="seopress-notice">
        <p>
            <?php _e('Add markup to your product pages so Google can provide detailed product information in rich Search results - including Image Search. Users can see price, availability... right on Search results.', 'wp-seopress-pro'); ?>
        </p>
    </div>
    <div class="seopress-notice is-warning">
        <ul class="advice seopress-list">
            <li><?php _e('<strong>Use markup for a specific product, not a category or list of products.</strong> For example, "shoes in our shop" is not a specific product.', 'wp-seopress-pro'); ?>
            </li>
            <li><?php _e('<strong>Adult-related products are not supported.</strong>', 'wp-seopress-pro'); ?>
            </li>
            <li><?php _e('<strong>Works best with WooCommerce: we automatically add aggregateRating properties from user reviews (you have to enable this option from WooCommerce settings).</strong>', 'wp-seopress-pro'); ?>
            </li>
        </ul>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_product_name_meta">
            <?php _e('Product name', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_product_name_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_name]"
            placeholder="<?php echo esc_html__('The name of your product', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Product name', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_product_name; ?>" />
        <span class="description"><?php _e('Default: product title', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_product_description_meta">
            <?php _e('Product description', 'wp-seopress-pro'); ?>
        </label>
        <textarea id="seopress_pro_rich_snippets_product_description_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_description]"
            placeholder="<?php echo esc_html__('The description of the product', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Product description', 'wp-seopress-pro'); ?>"><?php echo $seopress_pro_rich_snippets_product_description; ?></textarea>
        <span class="description"><?php _e('Default: product excerpt', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_product_img_meta">
            <?php _e('Thumbnail', 'wp-seopress-pro'); ?>
        </label>
        <span class="description"><?php _e('Pictures clearly showing the product, e.g. against a white background, are preferred', 'wp-seopress-pro'); ?></span>
        <input id="seopress_pro_rich_snippets_product_img_meta" type="text"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_img]"
            placeholder="<?php echo esc_html__('Select your image', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Thumbnail', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_product_img; ?>" />
        <input id="seopress_pro_rich_snippets_product_img" class="<?php echo seopress_btn_secondary_classes(); ?> seopress_media_upload"
            type="button"
            value="<?php _e('Upload an Image', 'wp-seopress-pro'); ?>" />
        <span class="description"><?php _e('Default: product image', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_product_price_meta">
            <?php _e('Product price', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_product_price_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_price]"
            placeholder="<?php echo esc_html__('Eg: 30', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Product price', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_product_price; ?>" />
        <span class="description"><?php _e('Default: active product price', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress-date-picker6">
            <?php _e('Product price valid until', 'wp-seopress-pro'); ?>
        </label>
        <input id="seopress-date-picker6" type="text"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_price_valid_date]"
            class="seopress-date-picker"
            placeholder="<?php echo esc_html__('Eg: YYYY-MM-DD', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Product price valid until', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_product_price_valid_date; ?>" />
        <span class="description"><?php _e('Default: sale price dates To field', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_product_sku_meta">
            <?php _e('Product SKU', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_product_sku_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_sku]"
            placeholder="<?php echo esc_html__('Eg: 0446310786', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Product SKU', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_product_sku; ?>" />
        <span class="description"><?php _e('Default: product SKU', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_product_global_ids_meta">
            <?php _e('Product Global Identifiers type', 'wp-seopress-pro'); ?>
        </label>
        <select id="seopress_pro_rich_snippets_product_global_ids_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_global_ids]">
            <option <?php selected('none', $seopress_pro_rich_snippets_product_global_ids); ?>
                value="none"><?php _e('Select a global identifier', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('gtin8', $seopress_pro_rich_snippets_product_global_ids); ?>
                value="gtin8"><?php _e('gtin8 (ean8)', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('gtin12', $seopress_pro_rich_snippets_product_global_ids); ?>
                value="gtin12"><?php _e('gtin12 (ean12)', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('gtin13', $seopress_pro_rich_snippets_product_global_ids); ?>
                value="gtin13"><?php _e('gtin13 (ean13)', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('gtin14', $seopress_pro_rich_snippets_product_global_ids); ?>
                value="gtin14"><?php _e('gtin14 (ean14)', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('mpn', $seopress_pro_rich_snippets_product_global_ids); ?>
                value="mpn"><?php _e('mpn', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('isbn', $seopress_pro_rich_snippets_product_global_ids); ?>
                value="isbn"><?php _e('isbn', 'wp-seopress-pro'); ?>
            </option>
        </select>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_product_global_ids_value_meta">
            <?php _e('Product Global Identifier value', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_product_global_ids_value_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_global_ids_value]"
            placeholder="<?php echo esc_html__('Eg: 925872', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Product Global Identifiers', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_product_global_ids_value; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_product_brand_meta">
            <?php _e('Product Brand', 'wp-seopress-pro'); ?>
        </label>
        <?php
            if (function_exists('seopress_get_taxonomies')) {
                $seopress_get_taxonomies = seopress_get_taxonomies();
                if ( ! empty(seopress_get_taxonomies())) {
                    ?>
        <select id="seopress_pro_rich_snippets_product_brand_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_brand]">
            <option <?php selected('none', $seopress_pro_rich_snippets_product_brand); ?>
                value="none">
                <?php _e('Select a taxonomy', 'wp-seopress-pro'); ?>
            </option>

            <?php foreach ($seopress_get_taxonomies as $key => $value) { ?>
            <option <?php selected($key, $seopress_pro_rich_snippets_product_brand); ?>
                value="<?php echo $key; ?>"><?php echo $key; ?>
            </option>
            <?php } ?>
        </select>
        <?php
                }
            } ?>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_product_price_currency_meta">
            <?php _e('Product currency', 'wp-seopress-pro'); ?>
        </label>
        <select id="seopress_pro_rich_snippets_product_price_currency_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_price_currency]">
            <?php foreach ($options_currencies as $item) { ?>
            <option <?php selected($item['value'], $seopress_pro_rich_snippets_product_price_currency); ?>
                value="<?php echo $item['value']; ?>">
                <?php echo $item['label']; ?>
            </option>
            <?php } ?>
        </select>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_product_condition_meta"><?php _e('Product Condition', 'wp-seopress-pro'); ?>
        </label>
        <select id="seopress_pro_rich_snippets_product_condition_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_condition]">
            <option <?php selected('NewCondition', $seopress_pro_rich_snippets_product_condition); ?>
                value="NewCondition"><?php _e('New', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('UsedCondition', $seopress_pro_rich_snippets_product_condition); ?>
                value="UsedCondition"><?php _e('Used', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('DamagedCondition', $seopress_pro_rich_snippets_product_condition); ?>
                value="DamagedCondition"><?php _e('Damaged', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('RefurbishedCondition', $seopress_pro_rich_snippets_product_condition); ?>
                value="RefurbishedCondition"><?php _e('Refurbished', 'wp-seopress-pro'); ?>
            </option>
        </select>
        <span class="description"><?php _e('Default: new', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_product_availability_meta"><?php _e('Product Availability', 'wp-seopress-pro'); ?>
        </label>
        <select id="seopress_pro_rich_snippets_product_availability_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_product_availability]">
            <option <?php selected('InStock', $seopress_pro_rich_snippets_product_availability); ?>
                value="InStock"><?php _e('In Stock', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('InStoreOnly', $seopress_pro_rich_snippets_product_availability); ?>
                value="InStoreOnly"><?php _e('In Store Only', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('OnlineOnly', $seopress_pro_rich_snippets_product_availability); ?>
                value="OnlineOnly"><?php _e('Online Only', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('LimitedAvailability', $seopress_pro_rich_snippets_product_availability); ?>
                value="LimitedAvailability"><?php _e('Limited Availability', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('SoldOut', $seopress_pro_rich_snippets_product_availability); ?>
                value="SoldOut"><?php _e('Sold Out', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('OutOfStock', $seopress_pro_rich_snippets_product_availability); ?>
                value="OutOfStock"><?php _e('Out Of Stock', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('Discontinued', $seopress_pro_rich_snippets_product_availability); ?>
                value="Discontinued"><?php _e('Discontinued', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('PreOrder', $seopress_pro_rich_snippets_product_availability); ?>
                value="PreOrder"><?php _e('Pre Order', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('PreSale', $seopress_pro_rich_snippets_product_availability); ?>
                value="PreSale"><?php _e('Pre Sale', 'wp-seopress-pro'); ?>
            </option>
        </select>
    </p>
</div>
<?php
}
