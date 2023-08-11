<?php

namespace SEOPressPro\Services\Admin\Settings\LocalBusiness\Fields;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

trait FieldStreetAddress {
    /**
     * @since 4.5.0
     *
     * @return void
     */
    public function renderFieldStreetAddress() {
        $check = seopress_pro_get_service('OptionPro')->getLocalBusinessStreetAddress(); ?>
        <input
            type="text"
            name="seopress_pro_option_name[seopress_local_business_street_address]"
            placeholder="<?php echo esc_html__('eg: Place Bellevue', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Street Address', 'wp-seopress-pro'); ?>"
            value="<?php echo esc_html($check); ?>" />

            <p class="description"><?php _e('<span class="field-required">Required</span> property by Google.', 'wp-seopress-pro'); ?></p>
        <?php
    }
}
