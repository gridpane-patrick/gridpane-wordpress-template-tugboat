<?php

namespace SEOPressPro\Services\Admin\Settings\LocalBusiness\Fields;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

trait FieldPriceRange {
    /**
     * @since 4.5.0
     *
     * @return void
     */
    public function renderFieldPriceRange() {
        $value = seopress_pro_get_service('OptionPro')->getLocalBusinessPriceRange(); ?>
        <input
            type="text"
            autocomplete="off" name="seopress_pro_option_name[seopress_local_business_price_range]"
            placeholder="<?php echo esc_html__('eg: $$, €€€, or ££££...', 'wp-seopress-pro'); ?>"
            aria-label="<?php echo __('Price range', 'wp-seopress-pro'); ?>"
            value="<?php echo esc_html($value); ?>"
        />

        <p class="description"><?php _e('<span class="field-recommended">Recommended</span> property by Google.', 'wp-seopress-pro'); ?></p>

        <?php
    }
}
