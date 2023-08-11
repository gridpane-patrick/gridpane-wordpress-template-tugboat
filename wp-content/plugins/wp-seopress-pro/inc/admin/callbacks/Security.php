<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_advanced_security_metaboxe_sdt_role_callback() {
    $docs     = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';

    $options = get_option('seopress_advanced_option_name');

    global $wp_roles;

    if ( ! isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    } ?>
<div class="wrap-user-roles">
    <?php
    foreach ($wp_roles->get_names() as $key => $value) {
        $check = isset($options['seopress_advanced_security_metaboxe_sdt_role'][$key]); ?>
    <p>
        <label
            for="seopress_advanced_security_metaboxe_sdt_role_<?php echo $key; ?>">
            <input
                id="seopress_advanced_security_metaboxe_sdt_role_<?php echo $key; ?>"
                name="seopress_advanced_option_name[seopress_advanced_security_metaboxe_sdt_role][<?php echo $key; ?>]"
                type="checkbox" <?php if ('1' == $check) { ?>
            checked="yes"
            <?php } ?>
            value="1"/>

            <strong><?php echo $value; ?></strong> (<em><?php echo translate_user_role($value,  'default'); ?>)</em>
        </label>

    </p>

    <?php if (isset($options['seopress_advanced_security_metaboxe_sdt_role'][$key])) {
            esc_attr($options['seopress_advanced_security_metaboxe_sdt_role'][$key]);
        }
    } ?>
</div>
<?php echo seopress_tooltip_link($docs['security']['metaboxe_data_types'], __('Hook to filter Structured data types metabox call by post type - new window', 'wp-seopress-pro'));
}


function seopress_advanced_security_ga_widget_role_callback() {
    $docs     = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';

    $options = get_option('seopress_advanced_option_name');

    global $wp_roles;

    if ( ! isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    } ?>
<div class="wrap-user-roles">
    <?php
    foreach ($wp_roles->get_names() as $key => $value) {
        $check = isset($options['seopress_advanced_security_ga_widget_role'][$key]); ?>
    <p>
        <label
            for="seopress_advanced_security_ga_widget_role_<?php echo $key; ?>">
            <input
                id="seopress_advanced_security_ga_widget_role_<?php echo $key; ?>"
                name="seopress_advanced_option_name[seopress_advanced_security_ga_widget_role][<?php echo $key; ?>]"
                type="checkbox" <?php if ('1' == $check) { ?>
            checked="yes"
            <?php } ?>
            value="1"/>

            <strong><?php echo $value; ?></strong> (<em><?php echo translate_user_role($value,  'default'); ?>)</em>
        </label>

    </p>

    <?php if (isset($options['seopress_advanced_security_ga_widget_role'][$key])) {
            esc_attr($options['seopress_advanced_security_ga_widget_role'][$key]);
        }
    } ?>
</div>
<?php echo seopress_tooltip_link($docs['security']['ga_widget'], __('Hook to filter user capability for GA stats in dashboard widget - new window', 'wp-seopress-pro'));
}
