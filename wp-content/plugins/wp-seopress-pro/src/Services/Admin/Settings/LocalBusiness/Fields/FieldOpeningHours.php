<?php

namespace SEOPressPro\Services\Admin\Settings\LocalBusiness\Fields;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

use SEOPress\Helpers\OpeningHoursHelper;

trait FieldOpeningHours {
    /**
     * @since 4.5.0
     *
     * @return void
     */
    public function renderFieldOpeningHours() {
        $options = seopress_pro_get_service('OptionPro')->getLocalBusinessOpeningHours();

        $options = seopress_pro_get_service('TransformOldOpeningHours')->transform($options);

        $days = OpeningHoursHelper::getDays();
        $hours = OpeningHoursHelper::getHours();
        $mins = OpeningHoursHelper::getMinutes();

        $halfDay = ['am', 'pm']; ?>

<div class="seopress-notice">
    <p>
        <?php _e('<strong>Morning and Afternoon are just time slots</strong>.', 'wp-seopress-pro'); ?>
    </p>
    <p>
        <?php _e('Eg: if you\'re opened from 10:00 AM to 9:00 PM, check Morning and enter 10:00 / 21:00.', 'wp-seopress-pro'); ?>
    </p>
    <p>
        <?php _e('If you are open non-stop, check Morning and enter 0:00 / 23:59.', 'wp-seopress-pro'); ?>
    </p>
</div>

<ul class="wrap-opening-hours">
    <?php
            foreach ($days as $key => $day) {
                $closedAllDay = isset($options[$key]['open']) ? $options[$key]['open'] : 0; ?>
    <li>
        <span class="day">
            <?php echo $day; ?>
        </span>
        <input
            id="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][open]"
            name="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][open]"
            type="checkbox" <?php checked($closedAllDay, '1'); ?>
        value="1"/>

        <label
            for="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][open]">
            <?php _e('Closed all the day?', 'wp-seopress-pro'); ?>
        </label>
        <?php foreach ($halfDay as $valueHalfDay) {
                    $open = isset($options[$key][$valueHalfDay]['open']) ? $options[$key][$valueHalfDay]['open'] : 0;

                    $startHours = isset($options[$key][$valueHalfDay]['start']['hours']) ? $options[$key][$valueHalfDay]['start']['hours'] : '00';
                    $endHours = isset($options[$key][$valueHalfDay]['end']['hours']) ? $options[$key][$valueHalfDay]['end']['hours'] : '00';
                    $startMins = isset($options[$key][$valueHalfDay]['start']['mins']) ? $options[$key][$valueHalfDay]['start']['mins'] : '00';
                    $endMins = isset($options[$key][$valueHalfDay]['end']['mins']) ? $options[$key][$valueHalfDay]['end']['mins'] : '00'; ?>
        <div class="hours">
            <div class="range">
                <label
                    for="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][open]">
                    <input
                        id="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][open]"
                        name="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][open]"
                        type="checkbox" <?php checked($open, '1'); ?>
                    value="1"
                    />
                    <?php if ('am' === $valueHalfDay) { ?>

                    <?php _e('Open in the morning?', 'wp-seopress-pro'); ?>
                    <?php } else { ?>
                    <?php _e('Open in the afternoon?', 'wp-seopress-pro'); ?>
                    <?php } ?>
                </label>
            </div>

            <div class="range">
                <select
                    id="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][start][hours]"
                    name="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][start][hours]">
                    <?php foreach ($hours as $hour) { ?>
                    <option <?php selected($hour, $startHours); ?>
                        value="<?php echo $hour; ?>">
                        <?php echo $hour; ?>
                    </option>
                    <?php } ?>

                </select>

                <span>:</span>

                <select
                    id="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][start][mins]"
                    name="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][start][mins]">

                    <?php foreach ($mins as $min) { ?>
                    <option <?php selected($min, $startMins); ?>
                        value="<?php echo $min; ?>">
                        <?php echo $min; ?>
                    </option>
                    <?php } ?>

                </select>

                <span>-</span>

                <select
                    id="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][end][hours]"
                    name="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][end][hours]">

                    <?php foreach ($hours as $hour) { ?>
                    <option <?php selected($hour, $endHours); ?>
                        value="<?php echo $hour; ?>">
                        <?php echo $hour; ?>
                    </option>
                    <?php } ?>
                </select>

                <span>:</span>

                <select
                    id="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][end][mins]"
                    name="seopress_pro_option_name[seopress_local_business_opening_hours][<?php echo $key; ?>][<?php echo $valueHalfDay; ?>][end][mins]">

                    <?php foreach ($mins as $min) { ?>
                    <option <?php selected($min, $endMins); ?>
                        value="<?php echo $min; ?>">
                        <?php echo $min; ?>
                    </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <?php
                } ?>

    </li>
    <?php
            } ?>
</ul>

<p class="description">
    <?php _e('<span class="field-recommended">Recommended</span> property by Google.', 'wp-seopress-pro'); ?>
</p>

<?php
    }
}
