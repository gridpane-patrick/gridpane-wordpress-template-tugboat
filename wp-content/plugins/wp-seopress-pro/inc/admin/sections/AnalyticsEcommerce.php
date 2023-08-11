<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function print_section_info_google_analytics_ecommerce()
{
    $docs     = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : ''; ?>
<div class="sp-section-header">
    <h2>
        <?php _e('Ecommerce', 'wp-seopress-pro'); ?>
    </h2>
</div>
<p><?php _e('Track your ecommerce metrics with Google Analytics Enhanced Ecommerce.', 'wp-seopress-pro'); ?>
</p>

<p class="seopress-help">
    <a href="<?php echo $docs['analytics']['ecommerce']; ?>"
        target="_blank">
        <?php _e('Learn how to setup Google Analytics Enhanced Ecommerce', 'wp-seopress-pro'); ?>
    </a>
    <span class="dashicons dashicons-external"></span>
</p>

<?php if (! is_plugin_active('woocommerce/woocommerce.php')) { ?>
<div class="seopress-notice is-warning">
    <p>
        <?php _e('You need to enable <strong>WooCommerce</strong> to apply these settings.', 'wp-seopress-pro'); ?>
    </p>
</div>
<?php
    }
}
