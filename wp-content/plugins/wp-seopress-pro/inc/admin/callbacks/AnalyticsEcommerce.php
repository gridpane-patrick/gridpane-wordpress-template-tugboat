<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_google_analytics_purchases_callback() {
    $options = get_option('seopress_google_analytics_option_name');

    $check = isset($options['seopress_google_analytics_purchases']); ?>

<label for="seopress_google_analytics_purchases">
    <input id="seopress_google_analytics_purchases"
        name="seopress_google_analytics_option_name[seopress_google_analytics_purchases]" type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Measure purchases', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_google_analytics_purchases'])) {
        esc_attr($options['seopress_google_analytics_purchases']);
    }
}

function seopress_google_analytics_add_to_cart_callback() {
    $options = get_option('seopress_google_analytics_option_name');

    $check = isset($options['seopress_google_analytics_add_to_cart']); ?>

<label for="seopress_google_analytics_add_to_cart">
    <input id="seopress_google_analytics_add_to_cart"
        name="seopress_google_analytics_option_name[seopress_google_analytics_add_to_cart]" type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Measure additions to shopping carts', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_google_analytics_add_to_cart'])) {
        esc_attr($options['seopress_google_analytics_add_to_cart']);
    }
}

function seopress_google_analytics_remove_from_cart_callback() {
    $options = get_option('seopress_google_analytics_option_name');

    $check = isset($options['seopress_google_analytics_remove_from_cart']); ?>

<label for="seopress_google_analytics_remove_from_cart">
    <input id="seopress_google_analytics_remove_from_cart"
        name="seopress_google_analytics_option_name[seopress_google_analytics_remove_from_cart]" type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Measure removals from shopping carts', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_google_analytics_remove_from_cart'])) {
        esc_attr($options['seopress_google_analytics_remove_from_cart']);
    }
}
