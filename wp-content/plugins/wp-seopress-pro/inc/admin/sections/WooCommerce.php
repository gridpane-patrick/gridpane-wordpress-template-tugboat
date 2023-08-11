<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function print_section_info_woocommerce() {
    print_pro_section('woocommerce');

    if ( ! is_plugin_active('woocommerce/woocommerce.php')) { ?>

        <div class="seopress-notice is-warning">
            <p><?php _e('You need to enable <strong>WooCommerce</strong> to apply these settings.', 'wp-seopress-pro'); ?></p>
        </div>

        <?php
    }
}
