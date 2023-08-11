<?php

namespace SEOPressPro\Services\Admin\Settings\LocalBusiness\Fields;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

trait FieldAddressCountry {
    /**
     * @since 4.5.0
     *
     * @return void
     */
    public function renderFieldAddressCountry() {
        $value = seopress_pro_get_service('OptionPro')->getLocalBusinessAddressCountry(); ?>
        <input
            type="text"
            name="seopress_pro_option_name[seopress_local_business_address_country]"
            placeholder="<?php echo esc_html__('eg: France', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Country', 'wp-seopress-pro'); ?>"
            value="<?php echo esc_html($value); ?>" />

        <p class="description"><?php _e('<span class="field-required">Required</span> property by Google.', 'wp-seopress-pro'); ?></p>
        <?php
    }
}
