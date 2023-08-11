<?php
/**
 * Breadcrumbs Block display callback
 *
 * @param   array     $attributes  Block attributes
 * @param   string    $content     Inner block content
 * @param   WP_Block  $block       Actual block
 * @return  string    $html
 */
function seopress_pro_breadcrumb_block( $attributes, $content, $block ){
    $html = '';
    if ( '1' == seopress_get_toggle_option( 'breadcrumbs' ) ) {
        if ( '1' == seopress_breadcrumbs_enable_option() || '1' == seopress_breadcrumbs_json_enable_option() ) {
            $attr   = get_block_wrapper_attributes();
            $html   = sprintf( '<div %s>%s</div>', $attr, seopress_display_breadcrumbs( false ) );
        }
    }
    return apply_filters( 'seopress_pro_breadcrumb_block_html', $html, $attributes, $content, $block );
}