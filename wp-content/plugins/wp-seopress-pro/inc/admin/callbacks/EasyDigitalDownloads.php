<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_edd_product_og_price_callback() {
    $options = get_option('seopress_pro_option_name');

    $check = isset($options['seopress_edd_product_og_price']); ?>

<label for="seopress_edd_product_og_price">
    <input id="seopress_edd_product_og_price" name="seopress_pro_option_name[seopress_edd_product_og_price]"
        type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Add product:price:amount meta for product', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_edd_product_og_price'])) {
        esc_attr($options['seopress_edd_product_og_price']);
    }
}

function seopress_edd_product_og_currency_callback() {
    $options = get_option('seopress_pro_option_name');

    $check = isset($options['seopress_edd_product_og_currency']); ?>

<label for="seopress_edd_product_og_currency">
    <input id="seopress_edd_product_og_currency" name="seopress_pro_option_name[seopress_edd_product_og_currency]"
        type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Add product:price:currency meta for product', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_edd_product_og_currency'])) {
        esc_attr($options['seopress_edd_product_og_currency']);
    }
}

function seopress_edd_meta_generator_callback() {
    $options = get_option('seopress_pro_option_name');

    $check = isset($options['seopress_edd_meta_generator']); ?>

<label for="seopress_edd_meta_generator">
    <input id="seopress_edd_meta_generator" name="seopress_pro_option_name[seopress_edd_meta_generator]" type="checkbox"
        <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Remove EDD meta generator', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_edd_meta_generator'])) {
        esc_attr($options['seopress_edd_meta_generator']);
    }
}
