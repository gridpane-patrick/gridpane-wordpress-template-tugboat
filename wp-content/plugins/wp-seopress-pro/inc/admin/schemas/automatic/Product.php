<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

?>

<div class="wrap-rich-snippets-products">
    <div class="seopress-notice">
        <p>
            <?php /* translators: %s: link documentation */
                                                printf(__('Learn more about the <strong>Product schema</strong> from the <a href="%s" target="_blank">Google official documentation website</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), 'https://developers.google.com/search/docs/data-types/product'); ?>
        </p>
    </div>

    <?php if (is_plugin_active('woocommerce/woocommerce.php')) {
                                                    if (('no' == get_option('woocommerce_enable_reviews') && get_option('woocommerce_enable_reviews'))
                                        || ('no' == get_option('woocommerce_enable_review_rating') && get_option('woocommerce_enable_review_rating'))
                                        || ('no' == get_option('woocommerce_review_rating_required') && get_option('woocommerce_review_rating_required'))) { ?>
    <div class="seopress-notice">
        <p>
            <?php _e('To automatically add <strong>aggregateRating</strong> and <strong>Review</strong> properties to your schema, you have to enable <strong>User Reviews</strong> from WooCommerce settings.', 'wp-seopress-pro'); ?>
        </p>
        <p>
            <?php /* translators: %s: link to plugin settings page */
                        printf(__('Please activate these options from <strong>WC settings</strong>, <strong>Products</strong>, <a href="%s"><strong>General tab</strong></a>:', 'wp-seopress-pro'), admin_url('admin.php?page=wc-settings&tab=products'));
                    ?>
        </p>
        <ul>
            <?php
        }
                                                    if ('no' == get_option('woocommerce_enable_reviews') && get_option('woocommerce_enable_reviews')) { ?>
            <li>
                <span class="dashicons dashicons-minus"></span>
                <?php _e('Enable product reviews', 'wp-seopress-pro'); ?>
            </li>
            <?php }
                                                    if ('no' == get_option('woocommerce_enable_review_rating') && get_option('woocommerce_enable_review_rating')) { ?>
            <li>
                <span class="dashicons dashicons-minus"></span>
                <?php _e('Enable star rating on reviews', 'wp-seopress-pro'); ?>
            </li>
            <?php }
                                                    if ('no' == get_option('woocommerce_review_rating_required') && get_option('woocommerce_review_rating_required')) { ?>
            <li>
                <span class="dashicons dashicons-minus"></span>
                <?php _e('Star ratings should be required, not optional', 'wp-seopress-pro'); ?>
                <?php }
                                                    if (('no' == get_option('woocommerce_enable_reviews') && get_option('woocommerce_enable_reviews'))
                                        || ('no' == get_option('woocommerce_enable_review_rating') && get_option('woocommerce_enable_review_rating'))
                                        || ('no' == get_option('woocommerce_review_rating_required') && get_option('woocommerce_review_rating_required'))) {
                                                        echo '</ul></div>';
                                                    }

                                                    //WooCommerce Structured data
                                                    if (! function_exists('seopress_woocommerce_schema_output_option')) {
                                                        function seopress_woocommerce_schema_output_option()
                                                        {
                                                            $seopress_woocommerce_schema_output_option = get_option('seopress_pro_option_name');
                                                            if (! empty($seopress_woocommerce_schema_output_option)) {
                                                                foreach ($seopress_woocommerce_schema_output_option as $key => $seopress_woocommerce_schema_output_value) {
                                                                    $options[$key] = $seopress_woocommerce_schema_output_value;
                                                                }
                                                                if (isset($seopress_woocommerce_schema_output_option['seopress_woocommerce_schema_output'])) {
                                                                    return $seopress_woocommerce_schema_output_option['seopress_woocommerce_schema_output'];
                                                                }
                                                            }
                                                        }
                                                        if ('1' != seopress_woocommerce_schema_output_option()) { ?>
                <div class="seopress-notice is-error">
                    <p>
                        <?php
                                                            /* translators: %s: link to plugin settings page */
                                                            printf(__('You have not deactivated the default WooCommerce structured data type from our <a href="%s"><strong>PRO settings > WooCommerce tab</strong></a>. It\'s recommended to disable it to avoid any conflicts with your product schemas.', 'wp-seopress-pro'), admin_url('admin.php?page=seopress-pro-page#tab=tab_seopress_woocommerce'));
                                                        ?>
                    </p>
                </div>
                <?php }
                                                    }
                                                } else { ?>
                <div class="seopress-notice is-error">
                    <p>
                        <?php _e('WooCommerce is not enabled on your site. Some properties like <strong>aggregateRating</strong> and <strong>Review</strong> will not work out of the box.', 'wp-seopress-pro'); ?>
                    </p>
                </div>
                <?php } ?>

                <p>
                    <label for="seopress_pro_rich_snippets_product_name_meta">
                        <?php _e('Product name', 'wp-seopress-pro'); ?>
                    </label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_name', 'default'); ?>
                    <span class="description"><?php _e('The name of your product', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_description_meta"><?php _e('Product description', 'wp-seopress-pro'); ?></label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_description', 'default'); ?>
                    <span class="description"><?php _e('The description of the product', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_img_meta"><?php _e('Thumbnail', 'wp-seopress-pro'); ?></label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_img', 'image'); ?>
                    <span class="description"><?php _e('Pictures clearly showing the product, e.g. against a white background, are preferred.', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_price_meta">
                        <?php _e('Product price', 'wp-seopress-pro'); ?>
                    </label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_price', 'default'); ?>
                    <span class="description"><?php _e('Eg: 30', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_price_valid_date"><?php _e('Product price valid until', 'wp-seopress-pro'); ?></label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_price_valid_date', 'date'); ?>
                    <span class="description"><?php _e('Eg: YYYY-MM-DD', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_sku_meta">
                        <?php _e('Product SKU', 'wp-seopress-pro'); ?></label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_sku', 'default'); ?>
                    <span class="description"><?php _e('Eg: 0446310786', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_global_ids_meta">
                        <?php _e('Product Global Identifiers type', 'wp-seopress-pro'); ?></label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_global_ids', 'default'); ?>
                    <span class="description"><?php _e('Eg: gtin8', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_global_ids_value_meta">
                        <?php _e('Product Global Identifiers', 'wp-seopress-pro'); ?></label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_global_ids_value', 'default'); ?>
                    <span class="description"><?php _e('Eg: 925872', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_brand_meta">
                        <?php _e('Product Brand', 'wp-seopress-pro'); ?></label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_brand', 'default'); ?>
                    <span class="description"><?php _e('eg: Apple', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_price_currency_meta">
                        <?php _e('Product currency', 'wp-seopress-pro'); ?>
                    </label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_price_currency', 'default'); ?>
                    <span class="description"><?php _e('Eg: USD, EUR', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_condition_meta"><?php _e('Product Condition', 'wp-seopress-pro'); ?></label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_condition', 'default'); ?>
                    <span class="description"><?php _e('<strong>Authorized values:</strong> "NewCondition", "UsedCondition", "DamagedCondition", "RefurbishedCondition"', 'wp-seopress-pro'); ?></span>
                </p>
                <p>
                    <label for="seopress_pro_rich_snippets_product_availability_meta"><?php _e('Product Availability', 'wp-seopress-pro'); ?></label>
                    <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_product_availability', 'default'); ?>
                    <span class="description"><?php _e('<strong>Authorized values:</strong> "InStock", "InStoreOnly", "OnlineOnly", "LimitedAvailability", "SoldOut", "OutOfStock", "Discontinued", "PreOrder", "PreSale"', 'wp-seopress-pro'); ?></span>
                </p>
    </div>
