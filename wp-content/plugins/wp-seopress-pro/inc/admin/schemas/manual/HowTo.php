<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_get_schema_metaboxe_how_to($seopress_pro_rich_snippets_data, $key_schema = 0) {
    $seopress_pro_rich_snippets_how_to_name = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_name']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_name'] : '';
    $seopress_pro_rich_snippets_how_to_desc = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_desc']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_desc'] : '';

    $seopress_pro_rich_snippets_how_to_img = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_img']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_img'] : '';
    $seopress_pro_rich_snippets_how_to_img_width = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_img_width']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_img_width'] : '';
    $seopress_pro_rich_snippets_how_to_img_height = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_img_height']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_img_height'] : '';

    $seopress_pro_rich_snippets_how_to_currency = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_currency']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_currency'] : '';
    $seopress_pro_rich_snippets_how_to_cost = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_cost']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_cost'] : '';
    $seopress_pro_rich_snippets_how_to_total_time = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_total_time']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to_total_time'] : '';
    $seopress_pro_rich_snippets_how_to = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_how_to'] : [];

    // SEOPress < 3.9
    if (apply_filters('seopress_get_pro_schemas_manual', true)) {
        // Double dimension required as a result of migration 3.9
        $seopress_pro_rich_snippets_how_to = ['0' => $seopress_pro_rich_snippets_how_to];
    } ?>

<div class="wrap-rich-snippets-item wrap-rich-snippets-how-to">
    <div class="seopress-notice">
        <p>
            <?php _e('Mark up your How-to page with JSON-LD to try to get the position 0 in search results. ', 'wp-seopress-pro'); ?>
        </p>
    </div>

    <p>
        <label for="seopress_pro_rich_snippets_how_to_name_meta">
            <?php _e('Title of the how-to', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_how_to_name_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to_name]"
            placeholder="<?php echo esc_html__('The name of your how-to', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('How-to name', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_how_to_name; ?>" />
    </p>

    <p>
        <label for="seopress_pro_rich_snippets_how_to_desc">
            <?php _e('How-to description (default excerpt, or beginning of the content)', 'wp-seopress-pro'); ?>
        </label>
        <textarea id="seopress_pro_rich_snippets_how_to_desc"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to_desc]"
            placeholder="<?php echo esc_html__('Enter your how-to description', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('How-to description', 'wp-seopress-pro'); ?>"><?php echo $seopress_pro_rich_snippets_how_to_desc; ?></textarea>
    </p>

    <p>
        <label for="seopress_pro_rich_snippets_how_to_img_meta">
            <?php _e('Image thumbnail', 'wp-seopress-pro'); ?>
        </label>
        <span class="description"><?php _e('Minimum width: 720px - Recommended size: 1920px -  .jpg, .png, or. gif format - crawlable and indexable', 'wp-seopress-pro'); ?></span>

        <!-- URL -->
        <input id="seopress_pro_rich_snippets_how_to_img_meta" type="text"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to_img]"
            placeholder="<?php echo esc_html__('Select your image', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Image thumbnail', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_how_to_img; ?>" />

        <!-- Width -->
        <input id="seopress_pro_rich_snippets_how_to_img_width" type="hidden"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to_img_width]"
            value="<?php echo $seopress_pro_rich_snippets_how_to_img_width; ?>" />

        <!-- Height -->
        <input id="seopress_pro_rich_snippets_how_to_img_height" type="hidden"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to_img_height]"
            value="<?php echo $seopress_pro_rich_snippets_how_to_img_height; ?>" />

        <!-- Upload -->
        <input id="seopress_pro_rich_snippets_how_to_img"
            class="<?php echo seopress_btn_secondary_classes(); ?> seopress_media_upload"
            type="button"
            value="<?php _e('Upload an Image', 'wp-seopress-pro'); ?>" />
    </p>

    <p>
        <label for="seopress_pro_rich_snippets_how_to_cost_meta">
            <?php _e('Estimated cost', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_how_to_cost_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to_cost]"
            placeholder="<?php echo esc_html__('The estimated cost', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('How-to estimated cost', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_how_to_cost; ?>" />
    </p>

    <p>
        <label for="seopress_pro_rich_snippets_how_to_currency_meta">
            <?php _e('Currency', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_how_to_currency_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to_currency]"
            placeholder="<?php echo esc_html__('The currency of the estimated cost', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('How-to currency', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_how_to_currency; ?>" />
    </p>

    <p>
        <label for="seopress_pro_rich_snippets_how_to_total_time_meta">
            <?php _e('Total time needed', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_how_to_total_time_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to_total_time]"
            placeholder="<?php echo esc_html__('Eg: HH:MM:SS', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Total time needed', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_how_to_total_time; ?>" />
    </p>

    <?php //Init $seopress_how_to array if empty
        if (empty($seopress_pro_rich_snippets_how_to)) {
            $seopress_pro_rich_snippets_how_to = ['0' => ['']];
        }

    $total = count($seopress_pro_rich_snippets_how_to[0]);

    if ($total > 0) {
        ?>
    <div id="wrap-how-to" data-count="<?php echo $total; ?>">
        <?php foreach ($seopress_pro_rich_snippets_how_to[0] as $key => $value) {
            $num = $key + 1;
            $check_name = isset($seopress_pro_rich_snippets_how_to[0][$key]['name']) ? esc_attr($seopress_pro_rich_snippets_how_to[0][$key]['name']) : null;
            $check_text = isset($seopress_pro_rich_snippets_how_to[0][$key]['text']) ? esc_textarea($seopress_pro_rich_snippets_how_to[0][$key]['text']) : null;
            $check_img = isset($seopress_pro_rich_snippets_how_to[0][$key]['image']) ? esc_textarea($seopress_pro_rich_snippets_how_to[0][$key]['image']) : null;
            $check_img_width = isset($seopress_pro_rich_snippets_how_to[0][$key]['width']) ? esc_textarea($seopress_pro_rich_snippets_how_to[0][$key]['width']) : null;
            $check_img_height = isset($seopress_pro_rich_snippets_how_to[0][$key]['height']) ? esc_textarea($seopress_pro_rich_snippets_how_to[0][$key]['height']) : null; ?>
        <div class="step">
            <h3 class="accordion-section-title" tabindex="0">
                <?php echo $check_name; ?>
            </h3>
            <div class="accordion-section-content">
                <div class="inside">
                    <p>
                        <label
                            for="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][<?php echo $key; ?>][name]">
                            <?php _e('The title of the step (required)', 'wp-seopress-pro'); ?>
                        </label>
                        <input
                            id="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][<?php echo $key; ?>][name]"
                            type="text"
                            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][<?php echo $key; ?>][name]"
                            placeholder="<?php echo esc_html__('Enter a title for this step', 'wp-seopress-pro'); ?>"
                            aria-label="<?php _e('Step name', 'wp-seopress-pro'); ?>"
                            value="<?php echo $check_name; ?>" />
                    </p>

                    <p>
                        <label
                            for="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][<?php echo $key; ?>][text]">
                            <?php _e('The text of your step (required)', 'wp-seopress-pro'); ?>
                        </label>
                        <textarea
                            id="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][<?php echo $key; ?>][text]"
                            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][<?php echo $key; ?>][text]"
                            placeholder="<?php echo esc_html__('Enter the text of your step', 'wp-seopress-pro'); ?>"
                            aria-label="<?php _e('Step text', 'wp-seopress-pro'); ?>"
                            rows="8"><?php echo $check_text; ?></textarea>
                    </p>
                    <p class="js-media-upload-how-to-repeater">
                        <label
                            for="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][<?php echo $key; ?>][image]">
                            <?php _e('Image thumbnail', 'wp-seopress-pro'); ?>
                        </label>
                        <input
                            id="seopress_pro_rich_snippets_data_<?php echo $key_schema; ?>_<?php echo $key; ?>_image_meta"
                            type="text"
                            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][<?php echo $key; ?>][image]"
                            placeholder="<?php echo esc_html__('Select your image', 'wp-seopress-pro'); ?>"
                            aria-label="<?php _e('Image thumbnail', 'wp-seopress-pro'); ?>"
                            value="<?php echo $check_img; ?>" />
                        <!-- Width -->
                        <input
                            id="seopress_pro_rich_snippets_data_<?php echo $key_schema; ?>_<?php echo $key; ?>_image_width"
                            type="hidden"
                            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][<?php echo $key; ?>][width]"
                            value="<?php echo $check_img_width; ?>" />

                        <!-- Height -->
                        <input
                            id="seopress_pro_rich_snippets_data_<?php echo $key_schema; ?>_<?php echo $key; ?>_image_height"
                            type="hidden"
                            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][<?php echo $key; ?>][height]"
                            value="<?php echo $check_img_height; ?>" />

                        <input
                            id="seopress_pro_rich_snippets_data_<?php echo $key_schema; ?>_<?php echo $key; ?>_image"
                            class="<?php echo seopress_btn_secondary_classes(); ?> seopress_media_upload"
                            type="button"
                            value="<?php _e('Upload an Image', 'wp-seopress-pro'); ?>"
                            style="width:auto;" />
                    </p>



                    <p>
                        <a href="#" class="remove-step button">
                            <?php _e('Remove step', 'wp-seopress-pro'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <?php
        } ?>
    </div>
    <?php
    } else { ?>
    <div id="wrap-how-to" data-count="1">
        <div class="step">
            <h3 class="accordion-section-title" tabindex="0">
                <?php _e('Step', 'wp-seopress-pro'); ?>
            </h3>
            <div class="accordion-section-content">
                <div class="inside">
                    <p>
                        <label
                            for="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][0][name]">
                            <?php _e('The title of the step (required)', 'wp-seopress-pro'); ?>
                        </label>
                        <input
                            id="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][0][name]"
                            type="text"
                            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][0][name]"
                            placeholder="<?php echo esc_html__('Enter a title for this step', 'wp-seopress-pro'); ?>"
                            aria-label="<?php _e('Step name', 'wp-seopress-pro'); ?>"
                            value="" />
                    </p>

                    <p>
                        <label
                            for="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][0][text]">
                            <?php _e('The text of your step (required)', 'wp-seopress-pro'); ?>
                        </label>
                        <textarea
                            id="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][0][text]"
                            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][0][text]"
                            placeholder="<?php echo esc_html__('Enter the text of your step', 'wp-seopress-pro'); ?>"
                            aria-label="<?php _e('Step text', 'wp-seopress-pro'); ?>"
                            rows="8"></textarea>
                    </p>
                    <p class="js-media-upload-how-to-repeater">
                        <label
                            for="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][0][image]">
                            <?php _e('Image thumbnail', 'wp-seopress-pro'); ?>
                        </label>
                        <input
                            id="seopress_pro_rich_snippets_data_<?php echo $key_schema; ?>_0_image_meta"
                            type="text"
                            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][0][image]"
                            placeholder="<?php echo esc_html__('Select your image', 'wp-seopress-pro'); ?>"
                            aria-label="<?php _e('Image thumbnail', 'wp-seopress-pro'); ?>"
                            value="" />
                        <!-- Width -->
                        <input
                            id="seopress_pro_rich_snippets_data_<?php echo $key_schema; ?>_0_image_width"
                            type="hidden"
                            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][0][width]"
                            value="" />

                        <!-- Height -->
                        <input
                            id="seopress_pro_rich_snippets_data_<?php echo $key_schema; ?>_0_image_height"
                            type="hidden"
                            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_how_to][0][height]"
                            value="" />

                        <input
                            id="seopress_pro_rich_snippets_data_<?php echo $key_schema; ?>_0_image"
                            class="<?php echo seopress_btn_secondary_classes(); ?> seopress_media_upload"
                            type="button"
                            value="<?php _e('Upload an Image', 'wp-seopress-pro'); ?>"
                            style="width:auto;" />
                    </p>

                    <p>
                        <a href="#" class="remove-step button">
                            <?php _e('Remove step', 'wp-seopress-pro'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    <p><a href="#" id="add-step"
            class="add-step components-button <?php echo seopress_btn_secondary_classes(); ?>"><?php _e('Add step', 'wp-seopress-pro'); ?></a>
    </p>
</div>
<?php
}
