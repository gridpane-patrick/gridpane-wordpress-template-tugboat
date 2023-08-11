<?php
defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

/**
 * Adds Local_Business_Widget widget.
 */
class Local_Business_Widget extends WP_Widget {
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'seopress_pro_lb_widget', // Base ID
            'Local Business', // Name
            ['description' => __('Display local business informations', 'wp-seopress-pro')] // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     widget arguments
     * @param array $instance saved values from database
     */
    public function widget($args, $instance) {
        extract($args);

        $title = isset($instance['title']) ? esc_attr($instance['title']) : null;
        $desc = isset($instance['desc']) ? esc_html($instance['desc']) : null;
        $street = isset($instance['street']) ? esc_attr($instance['street']) : null;
        $city = isset($instance['city']) ? esc_attr($instance['city']) : null;
        $state = isset($instance['state']) ? esc_attr($instance['state']) : null;
        $code = isset($instance['code']) ? esc_attr($instance['code']) : null;
        $country = isset($instance['country']) ? esc_attr($instance['country']) : null;
        $map = isset($instance['map']) ? esc_attr($instance['map']) : null;
        $phone = isset($instance['phone']) ? esc_attr($instance['phone']) : null;
        $opening_hours = isset($instance['opening_hours']) ? esc_attr($instance['opening_hours']) : null;
        $hide_opening_hours = isset($instance['hide_opening_hours']) ? esc_attr($instance['hide_opening_hours']) : null;
        $order = isset($instance['order']) ? $instance['order'] : [0 => '1', 1 => '2', 2 => '3', 3 => '4', 4 => '5', 5 => '6', 6 => '7', 7 => '8', 8 => '9', 9 => '10'];
        if (is_string($order)) {
            $order = explode(',', $order);
        }

        $title = apply_filters('seopress_lb_widget_title', $title);
        $desc = apply_filters('seopress_lb_widget_desc', $desc);

        if ( ! empty($street) && function_exists('seopress_local_business_street_address_option') && ('1' != seopress_local_business_street_address_option() || '' != seopress_local_business_street_address_option())) {
            $street = seopress_local_business_street_address_option();
        }
        $street = apply_filters('seopress_lb_widget_street_address', $street);

        if ( ! empty($city) && function_exists('seopress_local_business_address_locality_option') && ('1' != seopress_local_business_address_locality_option() || '' != seopress_local_business_address_locality_option())) {
            $city = seopress_local_business_address_locality_option();
        }
        $city = apply_filters('seopress_lb_widget_city', $city);

        if ( ! empty($state) && function_exists('seopress_local_business_address_region_option') && ('1' != seopress_local_business_address_region_option() || '' != seopress_local_business_address_region_option())) {
            $state = seopress_local_business_address_region_option();
        }
        $state = apply_filters('seopress_lb_widget_state', $state);

        if ( ! empty($code) && function_exists('seopress_local_business_postal_code_option') && ('1' != seopress_local_business_postal_code_option() || '' != seopress_local_business_postal_code_option())) {
            $code = seopress_local_business_postal_code_option();
        }
        $code = apply_filters('seopress_lb_widget_code', $code);

        if ( ! empty($country) && function_exists('seopress_local_business_address_country_option') && ('1' != seopress_local_business_address_country_option() || '' != seopress_local_business_address_country_option())) {
            $country = seopress_local_business_address_country_option();
        }
        $country = apply_filters('seopress_lb_widget_country', $country);

        if ( ! empty($map) && function_exists('seopress_local_business_lat_option') && ('' != seopress_local_business_lat_option() && '1' != seopress_local_business_lat_option()) && function_exists('seopress_local_business_lon_option') && ('' != seopress_local_business_lon_option() && '1' != seopress_local_business_lon_option())) {
            $place_id = '';
            if (function_exists('seopress_local_business_place_id_option') && '' != seopress_local_business_place_id_option()) {
                $place_id = '&query_place_id=' . seopress_local_business_place_id_option();
            }
            $map = '<a href="https://www.google.com/maps/search/?api=1' . $place_id . '&query=' . seopress_local_business_lat_option() . ',' . seopress_local_business_lon_option() . '" title="' . __('View this local business on Google Maps (new window)', 'wp-seopress-pro') . '" target="_blank">' . __('View on Google Maps', 'wp-seopress-pro') . '</a>';
        }
        $map = apply_filters('seopress_lb_widget_map', $map);

        if ( ! empty($phone) && function_exists('seopress_local_business_phone_option') && ('' != seopress_local_business_phone_option() || '1' != seopress_local_business_phone_option())) {
            $phone = seopress_local_business_phone_option();
        }
        $phone = apply_filters('seopress_lb_widget_phone', $phone);

        if ( ! empty($opening_hours) && function_exists('seopress_local_business_opening_hours_option') && ('' != seopress_local_business_opening_hours_option() || '1' != seopress_local_business_opening_hours_option())) {
            $opening_hours = seopress_local_business_opening_hours_option();
        }
        $opening_hours = apply_filters('seopress_lb_widget_opening_hours', $opening_hours);

        $fields = [];

        //Title
        $fields[1] = '';
        if ( ! empty($title)) {
            $fields[1] = $before_title . $title . $after_title;
        }

        //Desc
        $fields[2] = '';
        if ( ! empty($desc)) {
            $fields[2] = '<p class="sp-description">' . esc_html($desc) . '</p>';
        }

        //Street
        $fields[3] = '';
        if ( ! empty($street)) {
            $fields[3] = '<span class="sp-street">' . $street . '</span>';
        }

        //City
        $fields[4] = '';
        if ( ! empty($city)) {
            $comma = '';
            if ( ! empty($code)) {
                $comma = ', ';
            }
            $fields[4] = '<span class="sp-city">' . $city . $comma . '</span>';
        }

        //Code
        $fields[5] = '';
        if ( ! empty($code)) {
            $fields[5] = '<span class="sp-code">' . $code . '</span>';
        }

        //State
        $fields[6] = '';
        if ( ! empty($state)) {
            $fields[6] = '<span class="sp-state">' . $state . '</span>';
        }

        //Country
        $fields[7] = '';
        if ( ! empty($country)) {
            $fields[7] = '<span class="sp-country">' . $country . '</span>';
        }

        //Map link
        $fields[8] = '';
        if ( ! empty($map) && '1' != $map && '' != $map) {
            $fields[8] = '<span class="sp-map-link">' . $map . '</span>';
        }

        //Phone
        $fields[9] = '';
        if ( ! empty($phone)) {
            $fields[9] = '<span class="sp-phone"><a href="tel:' . $phone . '">' . $phone . '</a></span>';
        }

        //Opening hours
        $fields[10] = '';
        if ( ! empty($opening_hours)) {
            $hours = '<table class="sp-opening-hours"><tbody>';

            foreach ($opening_hours as $key => $days) {
                if ( ! empty($days)) {
                    switch ($key) {
                            case 0:
                                $day = __('Monday', 'wp-seopress-pro');
                                break;
                            case 1:
                                $day = __('Tuesday', 'wp-seopress-pro');
                                break;
                            case 2:
                                $day = __('Wednesday', 'wp-seopress-pro');
                                break;
                            case 3:
                                $day = __('Thursday', 'wp-seopress-pro');
                                break;
                            case 4:
                                $day = __('Friday', 'wp-seopress-pro');
                                break;
                            case 5:
                                $day = __('Saturday', 'wp-seopress-pro');
                                break;
                            case 6:
                                $day = __('Sunday', 'wp-seopress-pro');
                                break;
                        }

                    //If Hide closed days ON
                    if ( ! empty($hide_opening_hours)) {
                        if (empty($days['open'])) {
                            $hours .= '<tr>';
                            $hours .= '<th scope="row">' . $day . '</th>';
                        }
                    } else {
                        if (
                            ! empty($days['open']) && '1' == $days['open'] ||
                            (
                                ( ! isset($days['am']['open']) || '1' !== $days['am']['open']) &&
                                ( ! isset($days['pm']['open']) || '1' !== $days['pm']['open'])
                            )
                        ) {
                            $hours .= '<tr class="sp-lb-closed">';
                        } else {
                            $hours .= '<tr>';
                        }
                        $hours .= '<th scope="row">' . $day . '</th>';
                    }

                    if ( ! empty($days['open']) && '1' == $days['open']) {
                        if (empty($hide_opening_hours)) {
                            $hours .= '<td colspan="2" class="sp-lb-closed sp-lb-closed-all-day">';
                            $hours .= __('Closed', 'wp-seopress-pro');
                            $hours .= '</td>';
                        }
                    } else {
                        foreach ($days as $keyHalfDay => $valueHalfDay) {
                            if ( ! empty($valueHalfDay['start']) || ! empty($valueHalfDay['end'])) {
                                if ( ! isset($valueHalfDay['open']) || '1' !== $valueHalfDay['open']) {
                                    $hours .= '<td class="sp-lb-closed">';
                                } else {
                                    $hours .= '<td>';
                                }
                            }

                            if ( ! empty($valueHalfDay['start']) && isset($valueHalfDay['open']) && '1' === $valueHalfDay['open']) {
                                $hours .= $valueHalfDay['start']['hours'];
                                $hours .= __(':', 'wp-seopress-pro');
                                $hours .= $valueHalfDay['start']['mins'];
                            }
                            if ( ! empty($valueHalfDay['end']) && isset($valueHalfDay['open']) && '1' === $valueHalfDay['open']) {
                                $hours .= __(' - ', 'wp-seopress-pro');
                                $hours .= $valueHalfDay['end']['hours'];
                                $hours .= __(':', 'wp-seopress-pro');
                                $hours .= $valueHalfDay['end']['mins'];
                            }

                            if ( ! empty($valueHalfDay['start']) || ! empty($valueHalfDay['end'])) {
                                $hours .= '</td>';
                            }
                        }
                    }

                    if ( ! empty($hide_opening_hours)) {
                        if (empty($days['open'])) {
                            $hours .= '</tr>';
                        }
                    } else {
                        $hours .= '</tr>';
                    }
                }
            }
            $hours .= '</tbody></table>';

            $fields[10] = $hours;
        }

        if ( ! empty($order)) {
            $fields = array_replace(array_flip($order), $fields);
        }

        //HTML
        $html = '';

        $html .= $before_widget;

        $html .= '<div class="widget_seopress_pro_wrap_lb">'; //Fix for Page builders

        $css = '<style>.widget_seopress_pro_wrap_lb span{display:inline-block;width:100%}.widget_seopress_pro_wrap_lb span.sp-city,.widget_seopress_pro_wrap_lb span.sp-code{width:auto}</style>';

        $css = apply_filters('seopress_lb_widget_css', $css);

        $html .= $css;

        foreach ($fields as $value) {
            $html .= $value;
        }

        $html .= '</div>';

        $html .= $after_widget;

        $html = apply_filters('seopress_lb_widget_html', $html);

        echo $html;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance previously saved values from database
     */
    public function form($instance) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : null;
        $order = isset($instance['order']) ? esc_attr($instance['order']) : [0 => '1', 1 => '2', 2 => '3', 3 => '4', 4 => '5', 5 => '6', 6 => '7', 7 => '8', 8 => '9', 9 => '10'];
        $desc = isset($instance['desc']) ? esc_html($instance['desc']) : null;
        $street = isset($instance['street']) ? esc_attr($instance['street']) : null;
        $city = isset($instance['city']) ? esc_attr($instance['city']) : null;
        $state = isset($instance['state']) ? esc_attr($instance['state']) : null;
        $code = isset($instance['code']) ? esc_attr($instance['code']) : null;
        $country = isset($instance['country']) ? esc_attr($instance['country']) : null;
        $map = isset($instance['map']) ? esc_attr($instance['map']) : null;
        $phone = isset($instance['phone']) ? esc_attr($instance['phone']) : null;
        $opening_hours = isset($instance['opening_hours']) ? esc_attr($instance['opening_hours']) : null;
        $hide_opening_hours = isset($instance['hide_opening_hours']) ? esc_attr($instance['hide_opening_hours']) : null;

        if ( ! is_array($order)) {
            $order = explode(',', $order);
        } ?>
<div class="seopress-sortable-lb-widget">
    <p>
        <?php
        /* translators: %s: link documentation */
        printf('<a href="%s">' . __('Edit your Local Business information here', 'wp-seopress-pro') . '</a>', admin_url('admin.php?page=seopress-pro-page#tab=tab_seopress_local_business')); ?>
    </p>
    <p>
        <?php
            _e('<strong>Drag and drop fields</strong> to change their order in front-end.', 'wp-seopress-pro'); ?>
    </p>
    <?php foreach ($order as $item) {
                switch ($item) {
                case '1':
                    ?>
    <!-- Title -->
    <p data-id="1">
        <span class="dashicons dashicons-sort"></span>
        <label
            for="<?php echo $this->get_field_name('title'); ?>"><?php _e('Title:', 'wp-seopress-pro'); ?></label>
        <input class="widefat title-data"
            id="<?php echo $this->get_field_name('title'); ?>"
            name="<?php echo $this->get_field_name('title'); ?>"
            type="text" value="<?php echo esc_attr($title); ?>" />
    </p>
    <?php
break;
                case '2':
                    ?>
    <!-- Desc -->
    <p data-id="2">
        <span class="dashicons dashicons-sort"></span>
        <label
            for="<?php echo $this->get_field_name('desc'); ?>"><?php _e('Description:', 'wp-seopress-pro'); ?></label>
        <textarea rows="12" class="widefat content"
            id="<?php echo $this->get_field_name('desc'); ?>"
            name="<?php echo $this->get_field_name('desc'); ?>"
            type="text"
            aria-label="<?php esc_html(_e('Description about your local business', 'wp-seopress-pro')); ?>"
            placeholder="<?php esc_html(_e('Add additional information here.', 'wp-seopress-pro')); ?>"><?php echo esc_html($desc); ?></textarea>
    </p>
    <?php
break;
                case '3':
                    ?>
    <!-- Street Address -->
    <p data-id="3">
        <span class="dashicons dashicons-sort"></span>
        <label
            for="<?php echo $this->get_field_name('street'); ?>">
            <input class="widefat"
                id="<?php echo $this->get_field_name('street'); ?>"
                name="<?php echo $this->get_field_name('street'); ?>"
                type="checkbox" <?php if ('1' == $street) {
                        echo 'checked="yes"';
                    } ?> value="1"/>
            <?php _e('Show street address?', 'wp-seopress-pro'); ?>
        </label>
    </p>
    <?php
break;
                case '4':
                    ?>
    <!-- City -->
    <p data-id="4">
        <span class="dashicons dashicons-sort"></span>
        <label
            for="<?php echo $this->get_field_name('city'); ?>">
            <input class="widefat"
                id="<?php echo $this->get_field_name('city'); ?>"
                name="<?php echo $this->get_field_name('city'); ?>"
                type="checkbox" <?php if ('1' == $city) {
                        echo 'checked="yes"';
                    } ?> value="1"/>
            <?php _e('Show city?', 'wp-seopress-pro'); ?>
        </label>
    </p>
    <?php
break;
                case '5':
                    ?>
    <!-- State -->
    <p data-id="5">
        <span class="dashicons dashicons-sort"></span>
        <label
            for="<?php echo $this->get_field_name('state'); ?>">
            <input class="widefat"
                id="<?php echo $this->get_field_name('state'); ?>"
                name="<?php echo $this->get_field_name('state'); ?>"
                type="checkbox" <?php if ('1' == $state) {
                        echo 'checked="yes"';
                    } ?> value="1"/>
            <?php _e('Show state?', 'wp-seopress-pro'); ?>
        </label>
    </p>
    <?php
break;
                case '6':
                    ?>
    <!-- Code -->
    <p data-id="6">
        <span class="dashicons dashicons-sort"></span>
        <label
            for="<?php echo $this->get_field_name('code'); ?>">
            <input class="widefat"
                id="<?php echo $this->get_field_name('code'); ?>"
                name="<?php echo $this->get_field_name('code'); ?>"
                type="checkbox" <?php if ('1' == $code) {
                        echo 'checked="yes"';
                    } ?> value="1"/>
            <?php _e('Show postal code?', 'wp-seopress-pro'); ?>
        </label>
    </p>
    <?php
break;
                case '7':
                    ?>
    <!-- Country -->
    <p data-id="7">
        <span class="dashicons dashicons-sort"></span>
        <label
            for="<?php echo $this->get_field_name('country'); ?>">
            <input class="widefat"
                id="<?php echo $this->get_field_name('country'); ?>"
                name="<?php echo $this->get_field_name('country'); ?>"
                type="checkbox" <?php if ('1' == $country) {
                        echo 'checked="yes"';
                    } ?> value="1"/>
            <?php _e('Show country?', 'wp-seopress-pro'); ?>
        </label>
    </p>
    <?php
break;
                case '8':
                    ?>
    <!-- Map -->
    <p data-id="8">
        <span class="dashicons dashicons-sort"></span>
        <label
            for="<?php echo $this->get_field_name('map'); ?>">
            <input class="widefat"
                id="<?php echo $this->get_field_name('map'); ?>"
                name="<?php echo $this->get_field_name('map'); ?>"
                type="checkbox" <?php if ('1' == $map) {
                        echo 'checked="yes"';
                    } ?> value="1"/>
            <?php _e('Show map link (new window)?', 'wp-seopress-pro'); ?>
        </label>
    </p>
    <?php
break;
                case '9':
                    ?>
    <!-- Phone -->
    <p data-id="9">
        <span class="dashicons dashicons-sort"></span>
        <label
            for="<?php echo $this->get_field_name('phone'); ?>">
            <input class="widefat"
                id="<?php echo $this->get_field_name('phone'); ?>"
                name="<?php echo $this->get_field_name('phone'); ?>"
                type="checkbox" <?php if ('1' == $phone) {
                        echo 'checked="yes"';
                    } ?> value="1"/>
            <?php _e('Show phone number?', 'wp-seopress-pro'); ?>
        </label>
    </p>
    <?php
break;
                case '10':
                    ?>
    <!-- Opening hours -->
    <p data-id="10">
        <span class="dashicons dashicons-sort"></span>
        <label
            for="<?php echo $this->get_field_name('opening_hours'); ?>">
            <input class="widefat"
                id="<?php echo $this->get_field_name('opening_hours'); ?>"
                name="<?php echo $this->get_field_name('opening_hours'); ?>"
                type="checkbox" <?php if ('1' == $opening_hours) {
                        echo 'checked="yes"';
                    } ?> value="1"/>
            <?php _e('Show opening hours?', 'wp-seopress-pro'); ?>
        </label>
    </p>
    <?php
break;
            }
            } ?>

</div>
<!-- Hide opening hours -->
<p>
    <label
        for="<?php echo $this->get_field_name('hide_opening_hours'); ?>">
        <input class="widefat"
            id="<?php echo $this->get_field_name('hide_opening_hours'); ?>"
            name="<?php echo $this->get_field_name('hide_opening_hours'); ?>"
            type="checkbox" <?php if ('1' == $hide_opening_hours) {
                echo 'checked="yes"';
            } ?> value="1"/>
        <?php _e('Hide closed days?', 'wp-seopress-pro'); ?>
    </label>
</p>

<input type="hidden" class="data-order"
    id="<?php echo $this->get_field_name('order'); ?>"
    name="<?php echo $this->get_field_name('order'); ?>"
    value="<?php echo implode(',', $order); ?>">

<?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance values just sent to be saved
     * @param array $old_instance previously saved values from database
     *
     * @return array updated safe values to be saved
     */
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = ( ! empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['desc'] = ( ! empty($new_instance['desc'])) ? strip_tags($new_instance['desc']) : '';
        $instance['street'] = ( ! empty($new_instance['street'])) ? strip_tags($new_instance['street']) : '';
        $instance['city'] = ( ! empty($new_instance['city'])) ? strip_tags($new_instance['city']) : '';
        $instance['state'] = ( ! empty($new_instance['state'])) ? strip_tags($new_instance['state']) : '';
        $instance['code'] = ( ! empty($new_instance['code'])) ? strip_tags($new_instance['code']) : '';
        $instance['country'] = ( ! empty($new_instance['country'])) ? strip_tags($new_instance['country']) : '';
        $instance['map'] = ( ! empty($new_instance['map'])) ? strip_tags($new_instance['map']) : '';
        $instance['phone'] = ( ! empty($new_instance['phone'])) ? strip_tags($new_instance['phone']) : '';
        $instance['opening_hours'] = ( ! empty($new_instance['opening_hours'])) ? strip_tags($new_instance['opening_hours']) : '';
        $instance['hide_opening_hours'] = ( ! empty($new_instance['hide_opening_hours'])) ? strip_tags($new_instance['hide_opening_hours']) : '';

        if ( ! empty($new_instance['order'])) {
            $instance['order'] = $new_instance['order'];
        } elseif ( ! empty($old_instance['order'])) {
            $instance['order'] = $old_instance['order'];
        } else {
            $instance['order'] = [0 => '1', 1 => '2', 2 => '3', 3 => '4', 4 => '5', 5 => '6', 6 => '7', 7 => '8', 8 => '9', 9 => '10'];
        }

        return $instance;
    }
} // class Local_Business_Widget
