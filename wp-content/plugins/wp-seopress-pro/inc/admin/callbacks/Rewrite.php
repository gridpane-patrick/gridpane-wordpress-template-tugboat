<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_rewrite_search_callback() {
    $options = get_option('seopress_pro_option_name');
    $check   = isset($options['seopress_rewrite_search']) ? esc_attr($options['seopress_rewrite_search']) : null; ?>

<input type="text" name="seopress_pro_option_name[seopress_rewrite_search]"
    placeholder="<?php esc_html_e('Search results base', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Search results base, eg: "search-results" without quotes', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<p class="description">
    <?php _e('Flush your permalinks each time you edit this setting.', 'wp-seopress-pro'); ?>
</p>

<?php
}
