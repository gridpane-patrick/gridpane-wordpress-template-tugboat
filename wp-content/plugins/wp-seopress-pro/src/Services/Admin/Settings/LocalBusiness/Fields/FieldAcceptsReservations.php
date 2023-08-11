<?php

namespace SEOPressPro\Services\Admin\Settings\LocalBusiness\Fields;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

trait FieldAcceptsReservations {
    /**
     *
     * @return void
     */
    public function renderFieldAcceptsReservations() {
        $value = seopress_pro_get_service('OptionPro')->getLocalBusinessAcceptsReservations(); ?>
<input type="text" name="seopress_pro_option_name[seopress_local_business_accepts_reservations]"
    placeholder="<?php echo esc_html__('eg: True', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Accepts reservations ', 'wp-seopress-pro'); ?>"
    value="<?php echo esc_html($value); ?>" />
<p class="description">
    <?php _e('Indicates whether a FoodEstablishment accepts reservations. Values can be Boolean (True or False), an URL at which reservations can be made or (for backwards compatibility) the strings Yes or No.', 'wp-seopress-pro'); ?>
</p>


<p class="description"><?php _e('<span class="field-recommended">Recommended</span> property by Google.', 'wp-seopress-pro'); ?>
</p>

<?php
    }
}
