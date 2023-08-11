<?php

namespace SEOPressPro\Services\Admin\Settings\LocalBusiness\Fields;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

trait FieldPhone {
    /**
     * @since 4.5.0
     *
     * @return void
     */
    public function renderFieldPhone() {
        $value = seopress_pro_get_service('OptionPro')->getLocalBusinessPhone(); ?>
        <input
            type="text"
            name="seopress_pro_option_name[seopress_local_business_phone]"
            placeholder="<?php echo esc_html__('eg: +33559240138', 'wp-seopress-pro'); ?>"
            aria-label="<?php echo __('Telephone', 'wp-seopress-pro'); ?>"
            value="<?php echo esc_html($value); ?>"
        />

        <p class="description"><?php _e('<span class="field-recommended">Recommended</span> property by Google.', 'wp-seopress-pro'); ?></p>

        <?php
    }
}
