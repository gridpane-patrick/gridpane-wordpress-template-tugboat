<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

//Google Search Console Inspect URL API
function seopress_pro_inspect_url_api_callback() {
    $docs = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';
    $options = get_option('seopress_instant_indexing_option_name');
    $check   = isset($options['seopress_instant_indexing_google_api_key']) ? esc_attr($options['seopress_instant_indexing_google_api_key']) : null;

    printf(
'<textarea id="seopress_instant_indexing_google_api_key" name="seopress_instant_indexing_option_name[seopress_instant_indexing_google_api_key]" rows="12" placeholder="' . esc_html__('Paste your Google JSON key file here', 'wp-seopress-pro') . '" aria-label="' . __('Paste your Google JSON key file here', 'wp-seopress-pro') . '">%s</textarea>',
esc_html($check)); ?>

<p class="seopress-help description"><?php printf(__('To use the <span class="dashicons dashicons-external"></span><a href="%1$s" target="_blank">Google Inspect URL API</a> and generate your JSON key file, please <span class="dashicons dashicons-external"></span><a href="%2$s" target="_blank">follow our guide.'), esc_url($docs['indexing_api']['api']), esc_url($docs['indexing_api']['google'])); ?></p>

<?php
}
