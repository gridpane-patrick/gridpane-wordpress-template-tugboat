<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function print_section_info_rewrite()
{
    print_pro_section('rewrite');

    if (! is_plugin_active('permalink-manager-pro/permalink-manager.php')) {
        if (function_exists('seopress_get_toggle_white_label_option') && '1' != seopress_get_toggle_white_label_option()) { ?>
<p>
    <a href="https://www.seopress.org/go/permalink-manager-pro" target="_blank">
        <?php _e('We recommend Permalink Manager PRO plugin to rewrite easily and efficiently your URLs for SEO. Starting from just €45.', 'wp-seopress-pro'); ?>
    </a>
    <span class="dashicons dashicons-external"></span>
</p>
<?php
        }
    }
}
