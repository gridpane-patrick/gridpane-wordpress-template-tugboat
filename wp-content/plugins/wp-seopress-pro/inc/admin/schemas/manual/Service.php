<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_get_schema_metaboxe_service($seopress_pro_rich_snippets_data, $key_schema = 0) {
    $seopress_pro_rich_snippets_service_name                        = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_name']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_name'] : '';
    $seopress_pro_rich_snippets_service_type                        = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_type']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_type'] : '';
    $seopress_pro_rich_snippets_service_description                 = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_description']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_description'] : '';
    $seopress_pro_rich_snippets_service_img                         = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_img']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_img'] : '';
    $seopress_pro_rich_snippets_service_area                        = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_area']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_area'] : '';
    $seopress_pro_rich_snippets_service_provider_name               = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_provider_name']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_provider_name'] : '';
    $seopress_pro_rich_snippets_service_lb_img                      = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_lb_img']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_lb_img'] : '';
    $seopress_pro_rich_snippets_service_provider_mobility           = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_provider_mobility']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_provider_mobility'] : '';
    $seopress_pro_rich_snippets_service_slogan                      = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_slogan']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_slogan'] : '';
    $seopress_pro_rich_snippets_service_street_addr                 = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_street_addr']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_street_addr'] : '';
    $seopress_pro_rich_snippets_service_city                        = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_city']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_city'] : '';
    $seopress_pro_rich_snippets_service_state                       = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_state']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_state'] : '';
    $seopress_pro_rich_snippets_service_pc                          = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_pc']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_pc'] : '';
    $seopress_pro_rich_snippets_service_country                     = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_country']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_country'] : '';
    $seopress_pro_rich_snippets_service_lat                         = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_lat']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_lat'] : '';
    $seopress_pro_rich_snippets_service_lon                         = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_lon']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_lon'] : '';
    $seopress_pro_rich_snippets_service_tel                         = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_tel']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_tel'] : '';
    $seopress_pro_rich_snippets_service_price                       = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_price']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_service_price'] : ''; ?>
<div class="wrap-rich-snippets-item wrap-rich-snippets-services">
    <div class="seopress-notice">
        <p>
            <?php _e('Add markup to your service pages so that Google can provide detailed service information in rich Search results.', 'wp-seopress-pro'); ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_service_name_meta">
            <?php _e('Service name', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_name_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_name]"
            placeholder="<?php echo esc_html__('The name of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Service name', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_name; ?>" />
        <span class="description"><?php _e('Default: post title', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_type_meta">
            <?php _e('Service type', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_type_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_type]"
            placeholder="<?php echo esc_html__('The type of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Service type', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_type; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_description_meta">
            <?php _e('Service description', 'wp-seopress-pro'); ?>
        </label>
        <textarea id="seopress_pro_rich_snippets_service_description_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_description]"
            placeholder="<?php echo esc_html__('The description of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Service description', 'wp-seopress-pro'); ?>"><?php echo $seopress_pro_rich_snippets_service_description; ?></textarea>
        <span class="description"><?php _e('Default: post excerpt', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_img_meta">
            <?php _e('Thumbnail', 'wp-seopress-pro'); ?>
        </label>
        <input id="seopress_pro_rich_snippets_service_img_meta" type="text"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_img]"
            placeholder="<?php echo esc_html__('Select your image', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Thumbnail', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_img; ?>" />
        <span class="description"><?php _e('Default: post thumbnail', 'wp-seopress-pro'); ?></span>
        <input id="seopress_pro_rich_snippets_service_img" class="<?php echo seopress_btn_secondary_classes(); ?> seopress_media_upload"
            type="button"
            value="<?php _e('Upload an Image', 'wp-seopress-pro'); ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_area_meta">
            <?php _e('Area served', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_area_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_area]"
            placeholder="<?php echo esc_html__('The area served by your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Area served', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_area; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_provider_name_meta">
            <?php _e('Provider name', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_provider_name_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_provider_name]"
            placeholder="<?php echo esc_html__('The provider name of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Provider name', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_provider_name; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_lb_img_meta"><?php _e('Location image', 'wp-seopress-pro'); ?>
        </label>
        <input id="seopress_pro_rich_snippets_service_lb_img_meta" type="text"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_lb_img]"
            placeholder="<?php echo esc_html__('Select your location image', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Location image', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_lb_img; ?>" />
        <input id="seopress_pro_rich_snippets_service_lb_img"
            class="<?php echo seopress_btn_secondary_classes(); ?> seopress_media_upload" type="button"
            value="<?php _e('Upload an Image', 'wp-seopress-pro'); ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_provider_mobility_meta">
            <?php _e('Provider mobility (static or dynamic)', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_provider_mobility_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_provider_mobility]"
            placeholder="<?php echo esc_html__('The provider mobility of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Provider mobility', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_provider_mobility; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_slogan_meta">
            <?php _e('Slogan', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_slogan_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_slogan]"
            placeholder="<?php echo esc_html__('The slogan of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Slogan', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_slogan; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_street_addr_meta">
            <?php _e('Street Address', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_street_addr_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_street_addr]"
            placeholder="<?php echo esc_html__('The street address of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Street Address', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_street_addr; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_city_meta">
            <?php _e('City', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_city_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_city]"
            placeholder="<?php echo esc_html__('The city of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('City', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_city; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_state_meta">
            <?php _e('State', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_state_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_state]"
            placeholder="<?php echo esc_html__('The state of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('State', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_state; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_pc_meta">
            <?php _e('Postal code', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_pc_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_pc]"
            placeholder="<?php echo esc_html__('The postal code of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Postal code', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_pc; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_country_meta">
            <?php _e('Country', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_country_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_country]"
            placeholder="<?php echo esc_html__('The country of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Country', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_country; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_lat_meta">
            <?php _e('Latitude', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_lat_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_lat]"
            placeholder="<?php echo esc_html__('The latitude of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Latitude', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_lat; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_lon_meta">
            <?php _e('Longitude', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_lon_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_lon]"
            placeholder="<?php echo esc_html__('The longitude of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Longitude', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_lon; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_tel_meta">
            <?php _e('Telephone', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_tel_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_tel]"
            placeholder="<?php echo esc_html__('The telephone of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Telephone', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_tel; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_service_price_meta">
            <?php _e('Price range', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_service_price_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_service_price]"
            placeholder="<?php echo esc_html__('The price range of your service', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Price range', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_service_price; ?>" />
    </p>
</div>
<?php
}
