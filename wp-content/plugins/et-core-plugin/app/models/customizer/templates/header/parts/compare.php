<?php
/**
 * The template for displaying header compare block
 *
 * @since   2.3.7
 * @version 1.0.0
 */
?>

<?php

global $et_compare_icons, $et_builder_globals;

$element_options = array();
$element_options['built_in_compare'] = get_theme_mod('xstore_compare', false) && class_exists('WooCommerce');
$element_options['is_YITH_Woocompare'] = defined('YITH_WOOCOMPARE') && class_exists('YITH_Woocompare_Frontend');

if ( !$element_options['built_in_compare'] && !$element_options['is_YITH_Woocompare'] ) { ?>
	<div class="et_element et_b_header-compare" data-title="<?php esc_html_e( 'Compare', 'xstore-core' ); ?>">
            <span class="flex flex-wrap full-width align-items-center currentColor">
                <span class="flex-inline justify-content-center align-items-center flex-nowrap">
                    <?php esc_html_e( 'Compare ', 'xstore-core' ); ?>
                    <span class="mtips" style="text-transform: none;">
                        <i class="et-icon et-exclamation" style="margin-left: 3px; vertical-align: middle; font-size: 75%;"></i>
                        <span class="mt-mes"><?php echo current_user_can( 'edit_theme_options' ) ? sprintf(
                            /* translators: %s: URL to header image configuration in Customizer. */
                                __( 'Please, enable <a style="text-decoration: underline" href="%s" target="_blank">Built-in Compare</a>.', 'xstore-core'),
                                admin_url( 'customize.php?autofocus[section]=xstore-compare' )) :
                                __( 'Please, enable Built-in Compare.', 'xstore-core'); ?></span>
                    </span>
                    </span>
                </span>
            </span>
	</div>
	<?php
	return;
}

$html = '';

$element_options['compare_style'] = get_theme_mod( 'compare_style_et-desktop', 'type1' );
$element_options['compare_style'] = apply_filters('compare_style', $element_options['compare_style']);

if ( $et_builder_globals['in_mobile_menu'] ) {
	$element_options['compare_style'] = 'type1';
}

// header compare classes
$element_options['wrapper_class'] = ' flex align-items-center';
if ( $et_builder_globals['in_mobile_menu'] ) $element_options['wrapper_class'] .= ' justify-content-inherit';
$element_options['wrapper_class'] .= ' compare-' . $element_options['compare_style'];
$element_options['compare_off_canvas'] = false;
$element_options['etheme_mini_compare_content_type'] = 'none';
if ( $element_options['built_in_compare'] ) {
    $element_options['compare_type_et-desktop'] = get_theme_mod( 'compare_icon_et-desktop', 'type1' );
    $element_options['compare_type_et-desktop'] = apply_filters('compare_icon', $element_options['compare_type_et-desktop']);

    if ( !get_theme_mod('bold_icons', 0) ) {
        $element_options['compare_icons'] = $et_compare_icons['light'];
    }
    else {
        $element_options['compare_icons'] = $et_compare_icons['bold'];
    }

    $element_options['icon_custom'] = get_theme_mod('compare_icon_custom_svg_et-desktop', '');
    $element_options['icon_custom'] = apply_filters('compare_icon_custom', $element_options['icon_custom']);
    $element_options['icon_custom'] = isset($element_options['icon_custom']['id']) ? $element_options['icon_custom']['id'] : '';

    if ( $element_options['compare_type_et-desktop'] == 'custom' ) {
        if ( $element_options['icon_custom'] != '' ) {
            $element_options['compare_icons']['custom'] = etheme_get_svg_icon($element_options['icon_custom']);
        }
        else {
            $element_options['compare_icons']['custom'] = $element_options['compare_icons']['type1'];
        }
    }

    $element_options['compare_icon'] = $element_options['compare_icons'][$element_options['compare_type_et-desktop']];
    
    $element_options['compare_quantity_et-desktop'] = get_theme_mod( 'compare_quantity_et-desktop', '1' );
    $element_options['compare_quantity_position_et-desktop'] = ( $element_options['compare_quantity_et-desktop'] ) ? ' et-quantity-' . get_theme_mod( 'compare_quantity_position_et-desktop', 'right' ) : '';

    $element_options['compare_content_position_et-desktop'] = get_theme_mod( 'compare_content_position_et-desktop', 'right' );

    $element_options['compare_content_type_et-desktop'] = get_theme_mod( 'compare_content_type_et-desktop', 'dropdown' );

    $element_options['compare_dropdown_position_et-desktop'] = get_theme_mod( 'compare_dropdown_position_et-desktop', 'right' );
    
    $element_options['compare_quantity_et-desktop'] = get_theme_mod( 'compare_quantity_et-desktop', '1' );
    $element_options['compare_quantity_position_et-desktop'] = ( $element_options['compare_quantity_et-desktop'] ) ? ' et-quantity-' . get_theme_mod( 'compare_quantity_position_et-desktop', 'right' ) : '';
    if ( $et_builder_globals['in_mobile_menu'] ) {
        $element_options['compare_style'] = 'type1';
        $element_options['compare_quantity_et-desktop'] = false;
        $element_options['compare_quantity_position_et-desktop'] = '';
        $element_options['compare_content_type_et-desktop'] = 'none';
    }

    $element_options['not_compare_page'] = true;
    if ( $element_options['built_in_compare'] ) {
        $compare_page_id = get_theme_mod('xstore_compare_page', '');
        if ( ! empty( $compare_page_id ) && is_page( $compare_page_id ) || (isset($_GET['et-compare-page']) && is_account_page()) ) {
            $element_options['not_compare_page'] = false;
        }
    }

    // filters
    $element_options['etheme_mini_compare_content_type'] = apply_filters('etheme_mini_compare_content_type', $element_options['compare_content_type_et-desktop']);

    $element_options['etheme_mini_compare_content'] = $element_options['etheme_mini_compare_content_type'] != 'none';
    $element_options['etheme_mini_compare_content'] = apply_filters('etheme_mini_compare_content', $element_options['etheme_mini_compare_content']);

    $element_options['etheme_mini_compare_content_position'] = apply_filters('etheme_mini_compare_content_position', $element_options['compare_content_position_et-desktop']);

    $element_options['compare_off_canvas'] = $element_options['etheme_mini_compare_content_type'] == 'off_canvas';
    $element_options['compare_off_canvas'] = apply_filters('compare_off_canvas', $element_options['compare_off_canvas']);

    $element_options['wrapper_class'] .= ' ' . $element_options['compare_quantity_position_et-desktop'];
    $element_options['wrapper_class'] .= ( $element_options['compare_off_canvas'] ) ? ' et-content-' . $element_options['etheme_mini_compare_content_position'] : '';
    $element_options['wrapper_class'] .= ( !$element_options['compare_off_canvas'] && $element_options['compare_dropdown_position_et-desktop'] != 'custom' ) ? ' et-content-' . $element_options['compare_dropdown_position_et-desktop'] : '';
    $element_options['wrapper_class'] .= ( $element_options['compare_off_canvas'] && $element_options['etheme_mini_compare_content'] && $element_options['not_compare_page']) ? ' et-off-canvas et-off-canvas-wide et-content_toggle' : ' et-content-dropdown et-content-toTop';
    $element_options['wrapper_class'] .= ( $element_options['compare_quantity_et-desktop'] && $element_options['compare_icon'] == '' ) ? ' static-quantity' : '';
}
$element_options['wrapper_class'] .= ( $et_builder_globals['in_mobile_menu'] ) ? '' : ' et_element-top-level';

$element_options['is_customize_preview'] = apply_filters('is_customize_preview', false);
$element_options['attributes'] = array();

if ( $element_options['is_customize_preview'] )
	$element_options['attributes'] = array(
		'data-title="' . esc_html__( 'Compare', 'xstore-core' ) . '"',
		'data-element="compare"'
	);

//if ( !$element_options['built_in_compare'])
//    wp_enqueue_script('et_compare');

if ( $element_options['compare_off_canvas'] || $element_options['is_customize_preview'] ) {
    // could be via default wp
    if ( function_exists('etheme_enqueue_style')) {
        etheme_enqueue_style( 'off-canvas' );
    }
}

if ( $element_options['etheme_mini_compare_content_type'] || $element_options['is_customize_preview'] ) {
    if ( function_exists('etheme_enqueue_style')) {
        etheme_enqueue_style( 'cart-widget' );
    }
}

?>
	
	<div class="et_element et_b_header-compare <?php echo $element_options['wrapper_class']; ?>" <?php echo implode( ' ', $element_options['attributes'] ); ?>>
		<?php echo header_compare_callback(); ?>
	</div>

<?php unset($element_options);