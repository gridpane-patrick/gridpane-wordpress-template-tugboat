<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function print_section_info_page_speed() {
    print_pro_section('page-speed');
    $options = get_option('seopress_pro_option_name');
    $url = isset($options['seopress_ps_url']) ? $options['seopress_ps_url'] : get_home_url();
    ?>

    <div class="seopress-notice">
    <h3><?php _e('Google Page Speed Score', 'wp-seopress-pro'); ?>
    </h3>

    <p><?php _e('Learn how your site has performed, based on data from your actual users around the world.', 'wp-seopress-pro'); ?>
    </p>

    <p>
        <button type="button" class="seopress-request-page-speed btn btnPrimary"
            data_permalink="<?php if (isset($url)) {
                echo esc_html($url);
            } else {
             echo get_home_url();
            } ?>">
            <?php _e('Analyse with PageSpeed Insights', 'wp-seopress-pro'); ?>
        </button>

        <a href="javascript:window.print()" class="btn btnSecondary">
            <?php _e('Save as PDF', 'wp-seopress-pro'); ?>
        </a>

        <button type="button" id="seopress-clear-page-speed-cache" class="btn btnSecondary">
            <?php _e('Remove last analysis', 'wp-seopress-pro'); ?>
        </button>

        <span class="spinner"></span>
    </p>

    <?php

if ( ! is_plugin_active('wp-rocket/wp-rocket.php')) {
    if (function_exists('seopress_get_toggle_white_label_option') && '1' != seopress_get_toggle_white_label_option()) { ?>
    <p>
        <a href="https://www.seopress.org/go/wp-rocket" target="_blank">
            <?php _e('We recommend WP Rocket caching plugin to quickly and easily optimize your WordPress site. Starting from just $49.', 'wp-seopress-pro'); ?>
        </a>
        <span class="dashicons dashicons-external"></span>
    </p>
    <?php
    }
}

    if (function_exists('seopress_get_toggle_white_label_option') && '1' != seopress_get_toggle_white_label_option()) { ?>
    <p>
        <a class="seopress-help" href="https://www.dareboost.com/en/home" target="_blank">
            <?php _e('Get an insightful audit of your website\'s quality for better performances with Dareboost.', 'wp-seopress-pro'); ?>
        </a>
        <span class="seopress-help dashicons dashicons-external"></span>
    </p>
    <?php
} ?>
</div>
    <?php
}
