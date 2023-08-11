<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

?>

<div class="wrap-rich-snippets-services">
    <div class="seopress-notice">
        <p>
            <?php
                /* translators: %s: link documentation */
                printf(__('Learn more about the <strong>Service schema</strong> from the <a href="%s" target="_blank">Schema.org official documentation website</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), 'https://schema.org/Service');
            ?>
        </p>
    </div>

    <p>
        <label for="seopress_pro_rich_snippets_service_name_meta">
            <?php _e('Service name', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_name', 'default'); ?>
        <span class="description"><?php _e('The name of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_type_meta">
            <?php _e('Service type', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_type', 'default'); ?>
        <span class="description"><?php _e('The type of service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_description_meta"><?php _e('Service description', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_description', 'default'); ?>
        <span class="description"><?php _e('The description of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_img_meta"><?php _e('Image', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_img', 'image'); ?>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_area_meta"><?php _e('Area served', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_area', 'default'); ?>
        <span class="description"><?php _e('The area served by your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_provider_name_meta"><?php _e('Provider name', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_provider_name', 'default'); ?>
        <span class="description"><?php _e('The provider name of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_lb_img_meta"><?php _e('Location image', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_lb_img', 'image'); ?>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_provider_mobility_meta">
            <?php _e('Provider mobility (static or dynamic)', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_provider_mobility', 'default'); ?>
        <span class="description"><?php _e('The provider mobility of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_slogan_meta">
            <?php _e('Slogan', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_slogan', 'default'); ?>
        <span class="description"><?php _e('The slogan of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_street_addr_meta">
            <?php _e('Street Address', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_street_addr', 'default'); ?>
        <span class="description"><?php _e('The street address of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_city_meta">
            <?php _e('City', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_city', 'default'); ?>
        <span class="description"><?php _e('The city of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_state_meta">
            <?php _e('State', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_state', 'default'); ?>
        <span class="description"><?php _e('The state of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_pc_meta">
            <?php _e('Postal code', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_pc', 'default'); ?>
        <span class="description"><?php _e('The postal code of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_country_meta">
            <?php _e('Country', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_country', 'default'); ?>
        <span class="description"><?php _e('The country of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_lat_meta">
            <?php _e('Latitude', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_lat', 'default'); ?>
        <span class="description"><?php _e('The latitude of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_lon_meta">
            <?php _e('Longitude', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_lon', 'default'); ?>
        <span class="description"><?php _e('The longitude of your service', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_tel_meta">
            <?php _e('Telephone', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_tel', 'default'); ?>
        <span class="description"><?php _e('The telephone of your service (international format)', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_price_meta">
            <?php _e('Price range', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_service_price', 'default'); ?>
        <span class="description"><?php _e('The price range of your service', 'wp-seopress-pro'); ?></span>
    </p>
</div>
