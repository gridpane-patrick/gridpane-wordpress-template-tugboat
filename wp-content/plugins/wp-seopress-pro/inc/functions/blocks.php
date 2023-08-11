<?php
/**
 * This file contains block registration as well as dynamic callbacks for custom editor blocks
 */

add_action( 'init', 'seopress_pro_register_blocks', 100 );
/**
 * Register editor blocks
 */
function seopress_pro_register_blocks(){

    // Register Local Business block
    require_once SEOPRESS_PRO_PLUGIN_DIR_PATH . '/inc/functions/blocks-local-business.php';
    register_block_type( SEOPRESS_PRO_PLUGIN_DIR_PATH . 'assets/js/src/blocks/local-business' );
    register_block_type( SEOPRESS_PRO_PLUGIN_DIR_PATH . 'assets/js/src/blocks/local-business-field', [
        'render_callback' => 'seopress_pro_local_business_field_block',
    ]);
    wp_set_script_translations( 'wpseopress/local-business', 'wp-seopress-pro' );
    wp_set_script_translations( 'wpseopress/local-business-field', 'wp-seopress-pro' );

    // Register Breadcrumbs block
    require_once SEOPRESS_PRO_PLUGIN_DIR_PATH . '/inc/functions/blocks-breadcrumbs.php';
    register_block_type( SEOPRESS_PRO_PLUGIN_DIR_PATH . 'assets/js/src/blocks/breadcrumbs', [
        'render_callback' => 'seopress_pro_breadcrumb_block',
        'attributes'      => [
            'inlineStyles' => [
                'type'    => 'string',
                'default' => function_exists('seopress_breadcrumbs_inline_css') ? seopress_breadcrumbs_inline_css( '', false ) : '',
            ],
            'homeOption' => [
                'type'    => 'string',
                'default' => function_exists('seopress_breadcrumbs_i18n_home_option') && ! empty( seopress_breadcrumbs_i18n_home_option() ) ? seopress_breadcrumbs_i18n_home_option() : __( 'Home', 'wp-seopress-pro' ),
            ],
        ]
    ] );
    wp_set_script_translations( 'wpseopress/breadcrumbs', 'wp-seopress-pro' );

    // Register How-to block
    register_block_type( SEOPRESS_PRO_PLUGIN_DIR_PATH . 'assets/js/src/blocks/how-to' );
    register_block_type( SEOPRESS_PRO_PLUGIN_DIR_PATH . 'assets/js/src/blocks/how-to-step' );
    wp_set_script_translations( 'wpseopress/how-to', 'wp-seopress-pro' );
    wp_set_script_translations( 'wpseopress/how-to-step', 'wp-seopress-pro' );
}
