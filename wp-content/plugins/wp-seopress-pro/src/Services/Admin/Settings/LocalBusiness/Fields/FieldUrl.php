<?php

namespace SEOPressPro\Services\Admin\Settings\LocalBusiness\Fields;

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

trait FieldUrl
{
    /**
     * @since 4.5.0
     *
     * @return void
     */
    public function renderFieldUrl()
    {
        $value = seopress_pro_get_service('OptionPro')->getLocalBusinessUrl(); ?>
<input type="text" name="seopress_pro_option_name[seopress_local_business_url]"
    placeholder="<?php printf(esc_html__('default: %s', 'wp-seopress-pro'), get_home_url()); ?>"
    aria-label="<?php _e('URL', 'wp-seopress-pro'); ?>"
    value="<?php esc_html_e($value); ?>" />
<p class="description">
    <?php _e('Default: homepage. Google recommends to include your business details (address, phone, website...) for your visitors too.', 'wp-seopress-pro'); ?>
</p>

<p class="description"><?php _e('<span class="field-recommended">Recommended</span> property by Google.', 'wp-seopress-pro'); ?>
</p>

<?php
    }
}
