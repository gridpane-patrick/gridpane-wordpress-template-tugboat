<?php

namespace SEOPressPro\Services\Admin\Settings\LocalBusiness\Fields;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

trait FieldAddressLocality {
    /**
     * @since 4.5.0
     *
     * @return void
     */
    public function renderFieldAddressLocality() {
        $value = seopress_pro_get_service('OptionPro')->getLocalBusinessAddressLocality(); ?>
        <input
            type="text"
            name="seopress_pro_option_name[seopress_local_business_address_locality]"
            placeholder="<?php echo esc_html__('eg: Biarritz', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('City', 'wp-seopress-pro'); ?>"
            value="<?php echo esc_html($value); ?>" />

        <p class="description"><?php _e('<span class="field-required">Required</span> property by Google.', 'wp-seopress-pro'); ?></p>
        <?php
    }
}
