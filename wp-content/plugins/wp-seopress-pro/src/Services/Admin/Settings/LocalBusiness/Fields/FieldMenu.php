<?php

namespace SEOPressPro\Services\Admin\Settings\LocalBusiness\Fields;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

trait FieldMenu {
    /**
     *
     * @return void
     */
    public function renderFieldMenu() {
        $value = seopress_pro_get_service('OptionPro')->getLocalBusinessMenu(); ?>
<input type="text" name="seopress_pro_option_name[seopress_local_business_menu]"
    placeholder="<?php printf(esc_html__('eg: %s', 'wp-seopress-pro'), get_home_url()); ?>"
    aria-label="<?php _e('The URL of the menu.', 'wp-seopress-pro'); ?>"
    value="<?php echo esc_html($value); ?>" />

<p class="description"><?php _e('<span class="field-recommended">Recommended</span> property by Google.', 'wp-seopress-pro'); ?>
</p>

<?php
    }
}
