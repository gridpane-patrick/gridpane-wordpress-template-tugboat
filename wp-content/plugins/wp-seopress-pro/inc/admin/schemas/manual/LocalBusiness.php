<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_get_schema_metaboxe_local_business($seopress_pro_rich_snippets_data, $key_schema = 0) {
    $seopress_pro_rich_snippets_lb_name = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_name']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_name'] : '';
    $seopress_pro_rich_snippets_lb_type = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_type']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_type'] : '';
    $seopress_pro_rich_snippets_lb_cuisine = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_cuisine']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_cuisine'] : '';
    $seopress_pro_rich_snippets_lb_menu = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_menu']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_menu'] : '';
    $seopress_pro_rich_snippets_lb_accepts_reservations = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_accepts_reservations']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_accepts_reservations'] : '';
    $seopress_pro_rich_snippets_lb_img = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_img']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_img'] : '';
    $seopress_pro_rich_snippets_lb_img_width = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_img_width']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_img_width'] : '';
    $seopress_pro_rich_snippets_lb_img_height = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_img_height']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_img_height'] : '';
    $seopress_pro_rich_snippets_lb_street_addr = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_street_addr']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_street_addr'] : '';
    $seopress_pro_rich_snippets_lb_city = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_city']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_city'] : '';
    $seopress_pro_rich_snippets_lb_state = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_state']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_state'] : '';
    $seopress_pro_rich_snippets_lb_pc = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_pc']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_pc'] : '';
    $seopress_pro_rich_snippets_lb_country = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_country']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_country'] : '';
    $seopress_pro_rich_snippets_lb_lat = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_lat']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_lat'] : '';
    $seopress_pro_rich_snippets_lb_lon = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_lon']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_lon'] : '';
    $seopress_pro_rich_snippets_lb_website = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_website']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_website'] : '';
    $seopress_pro_rich_snippets_lb_tel = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_tel']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_tel'] : '';
    $seopress_pro_rich_snippets_lb_price = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_price']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_price'] : '';

    $seopress_pro_rich_snippets_lb_opening_hours = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_opening_hours']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_lb_opening_hours'] : [];

    // SEOPress < 3.9
    if (apply_filters('seopress_get_pro_schemas_manual', true)) {
        // Double dimension required as a result of migration 3.9
        $seopress_pro_rich_snippets_lb_opening_hours = ['0' => $seopress_pro_rich_snippets_lb_opening_hours];
    }

    $seopress_lb_types = seopress_lb_types_list();

    $options = $seopress_pro_rich_snippets_lb_opening_hours;

    $days = [__('Monday', 'wp-seopress-pro'), __('Tuesday', 'wp-seopress-pro'), __('Wednesday', 'wp-seopress-pro'), __('Thursday', 'wp-seopress-pro'), __('Friday', 'wp-seopress-pro'), __('Saturday', 'wp-seopress-pro'), __('Sunday', 'wp-seopress-pro')];

    $hours = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];

    $mins = ['00', '15', '30', '45', '59']; ?>
<div class="wrap-rich-snippets-item wrap-rich-snippets-local-business">
    <div class="seopress-notice">
        <p>
            <?php _e('When users search for businesses on Google Search or Maps, Search results may display a prominent Knowledge Graph card with details about a business that matched the query. ', 'wp-seopress-pro'); ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_lb_name_meta">
            <?php _e('Name of your business', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_name_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_name]"
            placeholder="<?php echo esc_html__('eg: Miremont', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Name of your business', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_name; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_type_meta"><?php _e('Select a business type', 'wp-seopress-pro'); ?></label>
        <select id="seopress_pro_rich_snippets_lb_type_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_type]">';
            <?php foreach ($seopress_lb_types as $type_value => $type_i18n) { ?>
            <option <?php selected($type_value, $seopress_pro_rich_snippets_lb_type); ?>
                value="<?php echo $type_value; ?>">
                <?php echo $type_i18n; ?>
            </option>
            <?php } ?>
        </select>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_img_meta">
            <?php _e('Image', 'wp-seopress-pro'); ?>
        </label>
        <span class="description"><?php _e('Every page must contain at least one image (whether or not you include markup). Google will pick the best image to display in Search results based on the aspect ratio and resolution.<br> Image URLs must be crawlable and indexable.<br> Images must represent the marked up content.<br> Images must be in .jpg, .png, or. gif format.<br> For best results, provide multiple high-resolution images (minimum of 50K pixels when multiplying width and height) with the following aspect ratios: 16x9, 4x3, and 1x1.', 'wp-seopress-pro'); ?></span>
        <input id="seopress_pro_rich_snippets_lb_img_meta" type="text"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_img]"
            placeholder="<?php echo esc_html__('Select your image', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Image', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_img; ?>" />
        <input id="seopress_pro_rich_snippets_lb_img_width" type="hidden"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_img_width]"
            value="<?php echo $seopress_pro_rich_snippets_lb_img_width; ?>" />
        <input id="seopress_pro_rich_snippets_lb_img_height" type="hidden"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_img_height]"
            value="<?php echo $seopress_pro_rich_snippets_lb_img_height; ?>" />
        <input id="seopress_pro_rich_snippets_lb_img"
            class="<?php echo seopress_btn_secondary_classes(); ?> seopress_media_upload"
            type="button"
            value="<?php _e('Upload an Image', 'wp-seopress-pro'); ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_street_addr_meta">
            <?php _e('Street Address', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_street_addr_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_street_addr]"
            placeholder="<?php echo esc_html__('eg: Place Bellevue', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Street Address', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_street_addr; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_city_meta">
            <?php _e('City', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_city_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_city]"
            placeholder="<?php echo esc_html__('eg: Biarritz', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('City', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_city; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_state_meta">
            <?php _e('State', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_state_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_state]"
            placeholder="<?php echo esc_html__('eg: Pyrenees Atlantiques', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('State', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_state; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_pc_meta">
            <?php _e('Postal code', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_pc_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_pc]"
            placeholder="<?php echo esc_html__('eg: 64200', 'wp-seopress-pro') . '" aria-label="' . __('Postal code', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_pc; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_country_meta">
            <?php _e('Country', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_country_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_country]"
            placeholder="<?php echo esc_html__('eg: France', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Country', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_country; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_lat_meta">
            <?php _e('Latitude', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_lat_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_lat]"
            placeholder="<?php echo esc_html__('eg: 43.4831389', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Latitude', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_lat; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_lon_meta">
            <?php _e('Longitude', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_lon_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_lon]"
            placeholder="<?php echo esc_html__('eg: -1.5630987', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Longitude', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_lon; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_website_meta">
            <?php _e('URL', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_website_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_website]"
            placeholder="<?php echo get_home_url(); ?>"
            aria-label="<?php _e('URL', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_website; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_tel_meta">
            <?php _e('Telephone', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_tel_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_tel]"
            placeholder="<?php echo esc_html__('eg: +33559240138', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Telephone', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_tel; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_price_meta">
            <?php _e('Price range', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_price_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_price]"
            placeholder="<?php echo esc_html__('eg: $$, €€€, or ££££...', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Price', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_price; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_cuisine_meta">
            <?php _e('Cuisine served', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_cuisine_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_cuisine]"
            placeholder="<?php echo esc_html__('eg: French, Italian, Indian, American', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('The type of cuisine the restaurant serves.', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_cuisine; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_menu_meta">
            <?php _e('URL of the menu', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_menu_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_menu]"
            placeholder="<?php printf(esc_html__('eg: %s', 'wp-seopress-pro'), get_home_url()); ?>"
            aria-label="<?php _e('The URL of the menu.', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_menu; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_accepts_reservations_meta">
            <?php _e('Accepts reservations', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_lb_accepts_reservations_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_accepts_reservations]"
            placeholder="<?php echo esc_html__('eg: True', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Accepts reservations ', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_lb_accepts_reservations; ?>" />
        <span class="description"><?php _e('Indicates whether a FoodEstablishment accepts reservations. Values can be Boolean (True or False), an URL at which reservations can be made or (for backwards compatibility) the strings Yes or No.', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_lb_opening_hours_meta">
            <?php _e('Opening hours', 'wp-seopress-pro'); ?>
        </label>
        <span class="description"><?php _e('<strong>Morning and Afternoon are just time slots</strong>. Eg: if you\'re opened from 10:00 AM to 9:00 PM, check Morning and enter 10:00 / 21:00. If you are open non-stop, check Morning and enter 0:00 / 23:59.', 'wp-seopress-pro'); ?></span>
    </p>



    <ul class="wrap-opening-hours">

        <?php foreach ($days as $key => $day) { ?>
        <?php
            $check_day = isset($options[0]['seopress_local_business_opening_hours'][$key]['open']);

            $check_day_am = isset($options[0]['seopress_local_business_opening_hours'][$key]['am']['open']);

            $check_day_pm = isset($options[0]['seopress_local_business_opening_hours'][$key]['pm']['open']);

            $selected_start_hours = isset($options[0]['seopress_local_business_opening_hours'][$key]['am']['start']['hours']) ? $options[0]['seopress_local_business_opening_hours'][$key]['am']['start']['hours'] : null;

            $selected_start_mins = isset($options[0]['seopress_local_business_opening_hours'][$key]['am']['start']['mins']) ? $options[0]['seopress_local_business_opening_hours'][$key]['am']['start']['mins'] : null;

            ?>

        <li>
            <span class="day"><strong><?php echo $day; ?></strong></span>
            <ul>
                <?php //Closed??>
                <li>
                    <input
                        id="seopress_local_business_opening_hours[<?php echo $key; ?>][open]"
                        name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][open]"
                        type="checkbox" <?php if ('1' == $check_day) {
                echo 'checked="yes"';
            } ?>
                    value="1"
                    />

                    <label
                        for="seopress_local_business_opening_hours[<?php echo $key; ?>][open]">
                        <?php _e('Closed all the day?', 'wp-seopress-pro'); ?>
                    </label>

                    <?php if (isset($options['seopress_local_business_opening_hours'][$key]['open'])) { ?>
                    <?php echo esc_attr($options['seopress_local_business_opening_hours'][$key]['open']); ?>
                    <?php } ?>
                </li>

                <?php //AM?>
                <li>
                    <input
                        id="seopress_local_business_opening_hours[<?php echo $key; ?>][am][open]"
                        name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][am][open]"
                        type="checkbox" <?php if ('1' == $check_day_am) {
                echo 'checked="yes"';
            } ?>
                    value="1"
                    />

                    <label
                        for="seopress_local_business_opening_hours[<?php echo $key; ?>][am][open]">
                        <?php _e('Open in the morning?', 'wp-seopress-pro'); ?>
                    </label>
                    <?php
                            if (isset($options['seopress_local_business_opening_hours'][$key]['am']['open'])) {
                                esc_attr($options['seopress_local_business_opening_hours'][$key]['am']['open']);
                            }
                            ?>

                    <select
                        id="seopress_local_business_opening_hours[<?php echo $key; ?>][am][start][hours]"
                        name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][am][start][hours]">

                        <?php foreach ($hours as $hour) { ?>
                        <option <?php if ($hour == $selected_start_hours) {
                                echo 'selected="selected"';
                            } ?>
                            value="<?php echo $hour; ?>"
                            >
                            <?php echo $hour; ?>
                        </option>
                        <?php } ?>

                    </select> :

                    <select
                        id="seopress_local_business_opening_hours[<?php echo $key; ?>][am][start][mins]"
                        name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][am][start][mins]">

                        <?php foreach ($mins as $min) { ?>
                        <option <?php if ($min == $selected_start_mins) {
                                echo 'selected="selected"';
                            } ?>
                            value="<?php echo $min; ?>"><?php echo $min; ?>
                        </option>
                        <?php } ?>

                    </select>
                    <?php
                            if (isset($options['seopress_local_business_opening_hours'][$key]['am']['start']['hours'])) {
                                esc_attr($options['seopress_local_business_opening_hours'][$key]['am']['start']['hours']);
                            }

                            if (isset($options['seopress_local_business_opening_hours'][$key]['am']['start']['mins'])) {
                                esc_attr($options['seopress_local_business_opening_hours'][$key]['am']['start']['mins']);
                            }
                            ?>
                    -
                    <?php
                            $selected_end_hours = isset($options[0]['seopress_local_business_opening_hours'][$key]['am']['end']['hours']) ? $options[0]['seopress_local_business_opening_hours'][$key]['am']['end']['hours'] : null;

                            $selected_end_mins = isset($options[0]['seopress_local_business_opening_hours'][$key]['am']['end']['mins']) ? $options[0]['seopress_local_business_opening_hours'][$key]['am']['end']['mins'] : null;
                            ?>
                    <select
                        id="seopress_local_business_opening_hours[<?php echo $key; ?>][am][end][hours]"
                        name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][am][end][hours]">
                        <?php foreach ($hours as $hour) { ?>
                        <option <?php if ($hour == $selected_end_hours) {
                                echo 'selected="selected"';
                            } ?>
                            value="<?php echo $hour; ?>"
                            >
                            <?php echo $hour; ?>
                        </option>
                        <?php } ?>

                    </select>
                    :

                    <select
                        id="seopress_local_business_opening_hours[<?php echo $key; ?>][am][end][mins]"
                        name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][am][end][mins]">

                        <?php foreach ($mins as $min) { ?>
                        <option <?php if ($min == $selected_end_mins) {
                                echo 'selected="selected"';
                            } ?>
                            value="<?php echo $min; ?>"
                            >
                            <?php echo $min; ?>
                        </option>
                        <?php } ?>

                    </select>
                </li>
        </li>

        <?php //PM?>
        <li>
            <?php
                        $selected_start_hours2 = isset($options[0]['seopress_local_business_opening_hours'][$key]['pm']['start']['hours']) ? $options[0]['seopress_local_business_opening_hours'][$key]['pm']['start']['hours'] : null;

                        $selected_start_mins2 = isset($options[0]['seopress_local_business_opening_hours'][$key]['pm']['start']['mins']) ? $options[0]['seopress_local_business_opening_hours'][$key]['pm']['start']['mins'] : null;
                        ?>
            <input
                id="seopress_local_business_opening_hours[<?php echo $key; ?>][pm][open]"
                name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][pm][open]"
                type="checkbox" <?php if ('1' == $check_day_pm) {
                            echo 'checked="yes"';
                        } ?>
            value="1"
            />

            <label
                for="seopress_local_business_opening_hours[<?php echo $key; ?>][pm][open]">
                <?php _e('Open in the afternoon?', 'wp-seopress-pro'); ?>
            </label>

            <?php
                        if (isset($options['seopress_local_business_opening_hours'][$key]['pm']['open'])) {
                            esc_attr($options['seopress_local_business_opening_hours'][$key]['pm']['open']);
                        }
                        ?>

            <select
                id="seopress_local_business_opening_hours[<?php echo $key; ?>][pm][start][hours]"
                name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][pm][start][hours]">

                <?php foreach ($hours as $hour) { ?>
                <option <?php if ($hour == $selected_start_hours2) {
                            echo 'selected="selected"';
                        } ?>
                    value="<?php echo $hour; ?>"
                    >
                    <?php echo $hour; ?>
                </option>
                <?php } ?>

            </select>
            :
            <select
                id="seopress_local_business_opening_hours[<?php echo $key; ?>][pm][start][mins]"
                name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][pm][start][mins]">
                <?php foreach ($mins as $min) { ?>
                <option <?php if ($min == $selected_start_mins2) {
                            echo 'selected="selected"';
                        } ?>
                    value="<?php echo $min; ?>"
                    >
                    <?php echo $min; ?>
                </option>
                <?php } ?>
            </select>
            <?php
                        if (isset($options['seopress_local_business_opening_hours'][$key]['pm']['start']['hours'])) {
                            esc_attr($options['seopress_local_business_opening_hours'][$key]['pm']['start']['hours']);
                        }

                        if (isset($options['seopress_local_business_opening_hours'][$key]['pm']['start']['mins'])) {
                            esc_attr($options['seopress_local_business_opening_hours'][$key]['pm']['start']['mins']);
                        }
                        ?>
            -
            <?php
                        $selected_end_hours2 = isset($options[0]['seopress_local_business_opening_hours'][$key]['pm']['end']['hours']) ? $options[0]['seopress_local_business_opening_hours'][$key]['pm']['end']['hours'] : null;

                        $selected_end_mins2 = isset($options[0]['seopress_local_business_opening_hours'][$key]['pm']['end']['mins']) ? $options[0]['seopress_local_business_opening_hours'][$key]['pm']['end']['mins'] : null;
                        ?>
            <select
                id="seopress_local_business_opening_hours[<?php echo $key; ?>][pm][end][hours]"
                name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][pm][end][hours]">

                <?php foreach ($hours as $hour) { ?>
                <option <?php if ($hour == $selected_end_hours2) {
                            echo 'selected="selected"';
                        } ?>
                    value="<?php echo $hour; ?>">
                    <?php echo $hour; ?>
                </option>
                <?php } ?>

            </select>

            :

            <select
                id="seopress_local_business_opening_hours[<?php echo $key; ?>][pm][end][mins]"
                name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_lb_opening_hours][seopress_local_business_opening_hours][<?php echo $key; ?>][pm][end][mins]">
                <?php foreach ($mins as $min) { ?>
                <option <?php if ($min == $selected_end_mins2) {
                            echo 'selected="selected"';
                        } ?>
                    value="<?php echo $min; ?>"
                    >
                    <?php echo $min; ?>
                </option>
                <?php } ?>
            </select>
        </li>
        </li>
        <?php
            if (isset($options['seopress_local_business_opening_hours'][$key]['pm']['end']['hours'])) {
                esc_attr($options['seopress_local_business_opening_hours'][$key]['pm']['end']['hours']);
            }

            if (isset($options['seopress_local_business_opening_hours'][$key]['pm']['end']['mins'])) {
                esc_attr($options['seopress_local_business_opening_hours'][$key]['pm']['end']['mins']);
            }

            ?>
        <?php } ?>
    </ul>
</div>
<?php
}
