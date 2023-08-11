<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_license_help_tab() {
    $screen = get_current_screen();
    $docs     = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';

    $seopress_license_help_tab_content = '<br>' . wp_oembed_get('https://www.youtube.com/watch?v=0OAq8HcFxPg', ['width'=>530]) . '<br>';

    $screen->add_help_tab([
        'id'        => 'seopress_license_help_tab',
        'title'     => __('Enable your license', 'wp-seopress-pro'),
        'content'   => $seopress_license_help_tab_content,
    ]);

    $screen->set_help_sidebar(
        '<ul>
            <li><a href="' . $docs['guides'] . '" target="_blank">' . __('Browse our guides', 'wp-seopress-pro') . '</a></li>
            <li><a href="' . $docs['faq'] . '" target="_blank">' . __('Read our FAQ', 'wp-seopress-pro') . '</a></li>
            <li><a href="' . $docs['website'] . '" target="_blank">' . __('Check our website', 'wp-seopress-pro') . '</a></li>
        </ul>'
    );
}
add_action('load-' . $seopress_license_help_tab, 'seopress_license_help_tab');
