<?php

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

if ( defined( 'ET_CORE_DIR' ) ) {
	add_filter( 'vc_font_container_output_data', 'et_vc_font_container_output_data', 4, 20 );
}

if ( ! function_exists( 'et_vc_font_container_output_data' ) ) {
	function et_vc_font_container_output_data( $data, $fields, $values, $settings ) {
		$font_container = new Vc_Font_Container();
		$tooltip        = false;
		
		if ( ! empty( $fields ) ) {
			if ( isset( $fields['tag'] ) ) {
				$data['tag'] = '
	            <div class="vc_row-fluid vc_column">
	            	<div class="wpb_element_label">' . esc_html__( 'Element tag', 'xstore' ) . '</div>
	                <div class="vc_font_container_form_field-tag-container hidden">
	                    <select class="vc_font_container_form_field-tag-select">';
				$tags        = $font_container->_vc_font_container_get_allowed_tags();
				foreach ( $tags as $tag ) {
					$data['tag'] .= '<option value="' . $tag . '" class="' . $tag . '" ' . ( $values['tag'] === $tag ? 'selected' : '' ) . '>' . $tag . '</option>';
				}
				$data['tag'] .= '</select>';
				$data['tag'] .= '</div>';
				$data['tag'] .= '<div class="xstore-vc-button-set et-font_container" data-type="tag">';
				$data['tag'] .= '<ul class="xstore-vc-button-set-list">';
				foreach ( $tags as $tag ) {
					$data['tag'] .= '<li class="vc-button-set-item' . ( $tooltip ? 'mtips mtips-top' : '' ) . ( $values['tag'] === $tag ? ' active' : '' ) . '" data-value="' . $tag . '">';
					$data['tag'] .= '<span>' . $tag . '</span>';
					if ( $tooltip ) {
						$data['tag'] .= '<span class="mt-mes">' . $tag . '</span>';
					}
					$data['tag'] .= '</li>';
				}
				$data['tag'] .= '</ul>';
				$data['tag'] .= '</div>';
				
				$data['tag'] .= '</div>';
			}
			
			if ( isset( $fields['text_align'] ) ) {
				
				$align = array(
					'left'    => esc_html__( 'left', 'xstore' ),
					'right'   => esc_html__( 'right', 'xstore' ),
					'center'  => esc_html__( 'center', 'xstore' ),
					'justify' => esc_html__( 'justify', 'xstore' ),
				);
				
				$data['text_align'] = '
	            <div class="vc_row-fluid vc_column">
	                <div class="wpb_element_label">' . esc_html__( 'Text align', 'xstore' ) . '</div>
	                <div class="vc_font_container_form_field-text_align-container hidden">
	                    <select class="vc_font_container_form_field-text_align-select">';
				foreach ( $align as $key => $value ) {
					$data['text_align'] .= '<option value="' . $key . '" class="' . $key . '" ' . ( $key === $values['text_align'] ? 'selected="selected"' : '' ) . '>' . $value . '</option>';
				}
				$data['text_align'] .= '</select>
	                </div>';
				$data['text_align'] .= '<div class="xstore-vc-button-set et-font_container" data-type="text_align">';
				$data['text_align'] .= '<ul class="xstore-vc-button-set-list">';
				foreach ( $align as $key => $value ) {
					$data['text_align'] .= '<li class="vc-button-set-item' . ( $tooltip ? 'mtips mtips-top' : '' ) . ( $key === $values['text_align'] ? ' active' : '' ) . '" data-value="' . $key . '">';
					$data['text_align'] .= '<span>' . $key . '</span>';
					if ( $tooltip ) {
						$data['text_align'] .= '<span class="mt-mes">' . $value . '</span>';
					}
					$data['text_align'] .= '</li>';
				}
				$data['text_align'] .= '</ul>';
				$data['text_align'] .= '</div>';
				$data['text_align'] .= '</div>';
			}
		}
		
		return $data;
	}
}

add_action( 'init', 'etheme_VC_setup' );

if ( ! function_exists( 'etheme_VC_setup' ) ) {
	function etheme_VC_setup() {
		vc_remove_element( "vc_tour" );
	}
}

if ( defined( 'VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG' ) ) {
	add_filter( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'etheme_vc_custom_css_class', 10, 3 );
	if ( ! function_exists( 'etheme_vc_custom_css_class' ) ) {
		function etheme_vc_custom_css_class( $classes, $base, $atts = array() ) {
			if ( ! empty( $atts['fixed_background'] ) ) {
				$classes .= ' et-attachment-fixed';
			}
			if ( ! empty( $atts['fixed_background'] ) ) {
				$classes .= ' et-parallax et-parallax-' . $atts['fixed_background'];
			} elseif ( ! empty( $atts['background_position'] ) ) {
				$classes .= ' et-parallax et-parallax-' . $atts['background_position'];
			}
			if ( ! empty( $atts['off_center'] ) ) {
				$classes .= ' off-center-' . $atts['off_center'];
			}
			if ( ! empty( $atts['columns_reverse'] ) ) {
				$classes .= ' columns-mobile-reverse';
			}
			
			if ( ! empty( $atts['md_bg_off'] ) ) {
				$classes .= ' et-md-no-bg';
			}
			
			if ( ! empty( $atts['sm_bg_off'] ) ) {
				$classes .= ' et-sm-no-bg';
			}
			
			if ( ! empty( $atts['xs_bg_off'] ) ) {
				$classes .= ' et-xs-no-bg';
			}
			
			if ( ! empty( $atts['align'] ) ) {
				$classes .= ' align-' . $atts['align'];
			}
			
			if ( ! empty( $atts['mob_align'] ) ) {
				$classes .= ' mob-align-' . $atts['mob_align'];
			}
			
			if ( ! empty( $atts['_et_uniqid'] ) ) {
				$classes .= ' ' . $atts['_et_uniqid'];
			} elseif ( ! empty( $atts['et_uniqid'] ) ) {
				$classes .= ' ' . $atts['et_uniqid'];
			}
			
			return $classes;
		}
	}
}

add_action( 'wp_head', function () {
	
	if ( is_admin() ) {
		return;
	}
	
	global $post;
	
	if ( ! is_object( $post ) ) {
		return;
	}
	
	$css = array(
		'global' => array(),
		'md'     => array(),
		'sm'     => array(),
		'xs'     => array(),
	);
	
	$css2 = et_custom_shortcodes_css( $post->post_content );
	
	if ( is_array( $css2 ) ) {
		$css = array(
			'global' => array_merge( $css['global'], $css2['global'] ),
			'md'     => array_merge( $css['md'], $css2['md'] ),
			'sm'     => array_merge( $css['sm'], $css2['sm'] ),
			'xs'     => array_merge( $css['xs'], $css2['xs'] ),
		);
	}
	
	$css = array(
		'global' => array_unique( $css['global'] ),
		'md'     => array_unique( $css['md'] ),
		'sm'     => array_unique( $css['sm'] ),
		'xs'     => array_unique( $css['xs'] ),
	);
	
	$css['xs'][] = 'div.et-xs-no-bg { background-image: none !important; }';
	
	echo '<style type="text/css" data-type="et_vc_shortcodes-custom-css">';
	
	if ( count( $css['global'] ) ) {
		echo implode( '', $css['global'] );
	}
	
	if ( count( $css['md'] ) ) {
		echo '@media only screen and (max-width: 1199px) {' . implode( '', $css['md'] ) . '}';
	}
	
	echo '@media only screen and (max-width: 1199px) and (min-width: 769px) { div.et-md-no-bg { background-image: none !important; } }';
	
	if ( count( $css['sm'] ) ) {
		echo '@media only screen and (max-width: 768px) {' . implode( '', $css['sm'] ) . '}';
	}
	
	echo '@media only screen and (max-width: 768px) and (min-width: 480px) { div.et-sm-no-bg { background-image: none !important; } }';
	
	if ( count( $css['xs'] ) ) {
		echo '@media only screen and (max-width: 480px) {' . implode( '', $css['xs'] ) . '}';
	}
	
	echo '</style>';
	
}, 1001 );

function et_custom_shortcodes_css( $content ) {
	
	$css = array(
		'global' => array(),
		'md'     => array(),
		'sm'     => array(),
		'xs'     => array()
	);
	
	$et_wpbakery_css_module = etheme_get_option( 'et_wpbakery_css_module', 0 );
	
	if ( ! class_exists( 'WPBMap' ) || ! method_exists( 'WPBMap', 'addAllMappedShortcodes' ) ) {
		return;
	}
	
	WPBMap::addAllMappedShortcodes();
	preg_match_all( '/' . get_shortcode_regex() . '/', $content, $shortcodes );
	foreach ( $shortcodes[2] as $index => $tag ) {
		
		$shortcode  = WPBMap::getShortCode( $tag );
		$attr_array = shortcode_parse_atts( trim( $shortcodes[3][ $index ] ) );
		$prefix     = '';
		
		if ( in_array($tag, array('vc_column', 'vc_column_inner'))) {
			$prefix = ' > .vc_column-inner';
		}
		
		if ( isset( $shortcode['params'] ) && ! empty( $shortcode['params'] ) ) {
			foreach ( $shortcode['params'] as $param ) {
				if ( isset( $param['type'] ) && isset( $attr_array[ $param['param_name'] ] ) ) {
					
					$translate = $local_css = '';
					
					$box_shadow = array(
						'box_shadow_inset'  => '',
						'box_shadow_h'      => '0',
						'box_shadow_v'      => '0',
						'box_shadow_blur'   => '0',
						'box_shadow_spread' => '0',
						'box_shadow_color'  => '',
					);
					
					$add_box_shadow = false;
					
					if ( isset( $attr_array['translate_x'] ) ) {
						$translate .= ' translateX(' . $attr_array['translate_x'] . ')';
					}
					
					if ( isset( $attr_array['translate_y'] ) ) {
						$translate .= ' translateY(' . $attr_array['translate_y'] . ')';
					}
					
					if ( $translate != '' ) {
						$local_css .= 'transform: ' . $translate . ';';
					}
					
					if ( isset( $attr_array['box_shadow_inset'] ) ) {
						$box_shadow['box_shadow_inset'] = 'inset';
					}
					
					if ( isset( $attr_array['box_shadow_h'] ) ) {
						$add_box_shadow             = true;
						$box_shadow['box_shadow_h'] = $attr_array['box_shadow_h'];
					}
					
					if ( isset( $attr_array['box_shadow_v'] ) ) {
						$add_box_shadow             = true;
						$box_shadow['box_shadow_v'] = $attr_array['box_shadow_v'];
					}
					
					if ( isset( $attr_array['box_shadow_blur'] ) ) {
						$add_box_shadow                = true;
						$box_shadow['box_shadow_blur'] = $attr_array['box_shadow_blur'];
					}
					
					if ( isset( $attr_array['box_shadow_spread'] ) ) {
						$add_box_shadow                  = true;
						$box_shadow['box_shadow_spread'] = $attr_array['box_shadow_spread'];
					}
					
					if ( isset( $attr_array['box_shadow_color'] ) ) {
						$add_box_shadow                 = true;
						$box_shadow['box_shadow_color'] = $attr_array['box_shadow_color'];
					}
					
					if ( $add_box_shadow ) {
						$local_css .= 'box-shadow: ' . implode( ' ', $box_shadow ) . ';';
					}
					
					$isset_uniq = isset( $attr_array['_et_uniqid'] ) || isset( $attr_array['et_uniqid'] );
					
					if ( isset( $attr_array['_et_uniqid'] ) ) {
						$selector = $attr_array['_et_uniqid'];
					} elseif ( isset( $attr_array['et_uniqid'] ) ) {
						$selector = $attr_array['et_uniqid'];
					}
					
					if ( $isset_uniq ) {
						if ( $local_css != '' ) {
							$css['global'][] = '.' . $selector . $prefix . '{ ' . $local_css . ' }';
						}
						if ( isset( $attr_array['z_index'] ) ) {
							$css['global'][] = '.' . $selector . ' { z-index: ' . $attr_array['z_index'] . '; }';
						}
					}
					
					if ( 'css_editor' === $param['type'] && $et_wpbakery_css_module ) {
						
						if ( $isset_uniq ) {
							
							if ( isset( $attr_array['css_md'] ) ) {
								$class       = vc_shortcode_custom_css_class( $attr_array['css_md'] );
								$css['md'][] = str_replace( $class, $selector . $prefix, $attr_array['css_md'] );
							}
							
							if ( isset( $attr_array['css_sm'] ) ) {
								$class       = vc_shortcode_custom_css_class( $attr_array['css_sm'] );
								$css['sm'][] = str_replace( $class, $selector . $prefix, $attr_array['css_sm'] );
							}
							
							if ( isset( $attr_array['css_xs'] ) ) {
								$class       = vc_shortcode_custom_css_class( $attr_array['css_xs'] );
								$css['xs'][] = str_replace( $class, $selector . $prefix, $attr_array['css_xs'] );
							}
							
						}
						
					}
					
				}
			}
		}
	}
	foreach ( $shortcodes[5] as $shortcode_content ) {
		$css2 = et_custom_shortcodes_css( $shortcode_content );
		if ( is_array( $css2 ) ) {
			$css = array(
				'global' => array_merge( $css['global'], $css2['global'] ),
				'md'     => array_merge( $css['md'], $css2['md'] ),
				'sm'     => array_merge( $css['sm'], $css2['sm'] ),
				'xs'     => array_merge( $css['xs'], $css2['xs'] ),
			);
		}
	}
	
	return $css;
}

// **********************************************************************//
// ! Add new option to vc_column
// **********************************************************************//
add_action( 'init', 'etheme_columns_options' );
if ( ! function_exists( 'etheme_columns_options' ) ) {
	function etheme_columns_options() {
		if ( ! function_exists( 'vc_map' ) ) {
			return;
		}
		
		$css_devices = array(
			array(
				'type'             => 'xstore_title_divider',
				'title'            => esc_html__( 'Css box', 'xstore' ),
				'group'            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-12 css_box_tabs',
				'param_name'       => 'column_css_divider',
			),
			array(
				'type'             => 'css_editor',
				'heading'          => esc_html__( 'CSS box (Desktop)', 'xstore' ),
				'param_name'       => 'css',
				'group'            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-12 vc_column et_css-query et_css-query-global',
			),
			array(
				'type'             => 'css_editor',
				'heading'          => esc_html__( 'CSS box (Tablet landscape)', 'xstore' ),
				'param_name'       => 'css_md',
				'group'            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-12 vc_column vc_dependent-hidden et_css-query et_css-query-tablet',
			),
			array(
				"type"             => "checkbox",
				"heading"          => esc_html__( "Disable background on tablet landscape", 'xstore' ),
				"param_name"       => "md_bg_off",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'value'            => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' ),
				'edit_field_class' => 'vc_col-xs-12 vc_column vc_dependent-hidden et_css-query et_css-query-tablet',
			),
			array(
				'type'             => 'css_editor',
				'heading'          => esc_html__( 'CSS box (Tablet portrait)', 'xstore' ),
				'param_name'       => 'css_sm',
				'group'            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-12 vc_column vc_dependent-hidden et_css-query et_css-query-ipad',
			),
			array(
				"type"             => "checkbox",
				"heading"          => esc_html__( "Disable background on tablet portrait", 'xstore' ),
				"param_name"       => "sm_bg_off",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'value'            => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' ),
				'edit_field_class' => 'vc_col-xs-12 vc_column vc_dependent-hidden et_css-query et_css-query-ipad',
			),
			array(
				'type'             => 'css_editor',
				'heading'          => esc_html__( 'CSS box (Mobile)', 'xstore' ),
				'param_name'       => 'css_xs',
				'group'            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-12 vc_column vc_dependent-hidden et_css-query et_css-query-mobile',
			),
			array(
				"type"             => "checkbox",
				"heading"          => esc_html__( "Disable background on mobile", 'xstore' ),
				"param_name"       => "xs_bg_off",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'value'            => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' ),
				'edit_field_class' => 'vc_col-xs-12 vc_column vc_dependent-hidden et_css-query et_css-query-mobile',
			),
		);
		
		if ( etheme_get_option( 'et_wpbakery_css_module', 0 ) ) {
			
			vc_remove_param( 'vc_column', 'css' );
			
			vc_add_params( 'vc_column', $css_devices );
			
			vc_remove_param( 'vc_column_inner', 'css' );
			
			vc_add_params( 'vc_column_inner', $css_devices );
			
			vc_remove_param( 'vc_row', 'css' );
			
			vc_add_params( 'vc_row', $css_devices );
			
			vc_remove_param( 'vc_row_inner', 'css' );
			
			vc_add_params( 'vc_row_inner', $css_devices );
			
		}
		
		vc_add_params( 'vc_row_inner', array(
			array(
				'type'       => 'hidden',
				'param_name' => 'et_uniqid',
				"group"      => esc_html__( 'Design Options', 'xstore' ),
				'value'      => 'et_custom_uniqid_' . uniqid(), // old
			),
			array(
				'type'       => 'xstore_uniqid',
				'param_name' => '_et_uniqid', // new
				"group"      => esc_html__( 'Design Options', 'xstore' ),
			)
		) );
		
		vc_add_params( 'vc_column', array(
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'XStore options', 'xstore' ),
				'group'      => esc_html__( 'Design Options', 'xstore' ),
				'param_name' => 'column_xstore_options_divider',
			),
			array(
				"type"             => "dropdown",
				"heading"          => esc_html__( "Background position", 'xstore' ),
				"param_name"       => "background_position",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				"value"            => array(
					''                              => '',
					__( "Left top", 'xstore' )      => 'left_top',
					__( "Left center", 'xstore' )   => 'left',
					__( "Left bottom", 'xstore' )   => 'left_bottom',
					__( "Right top", 'xstore' )     => 'right_top',
					__( "Right center", 'xstore' )  => 'right',
					__( "Right bottom", 'xstore' )  => 'right_bottom',
					__( "Center top", 'xstore' )    => 'center_top',
					__( "Center center", 'xstore' ) => 'center',
					__( "Center bottom", 'xstore' ) => 'center_bottom',
				),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "dropdown",
				"heading"          => esc_html__( "Fixed background position (paralax effect)", 'xstore' ),
				"param_name"       => "fixed_background",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				"value"            => array(
					''                              => '',
					__( "Left top", 'xstore' )      => 'left_top',
					__( "Left center", 'xstore' )   => 'left',
					__( "Left bottom", 'xstore' )   => 'left_bottom',
					__( "Right top", 'xstore' )     => 'right_top',
					__( "Right center", 'xstore' )  => 'right',
					__( "Right bottom", 'xstore' )  => 'right_bottom',
					__( "Center top", 'xstore' )    => 'center_top',
					__( "Center center", 'xstore' ) => 'center',
					__( "Center bottom", 'xstore' ) => 'center_bottom',
				),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"       => "xstore_button_set",
				"heading"    => esc_html__( "Off center", 'xstore' ),
				"group"      => esc_html__( 'Design Options', 'xstore' ),
				"param_name" => "off_center",
				"value"      => array(
					__( "Unset", 'xstore' ) => '',
					__( "Left", 'xstore' )  => 'left',
					__( "Right", 'xstore' ) => 'right',
				)
			),
			array(
				"type"             => "xstore_button_set",
				"heading"          => esc_html__( "Align", 'xstore' ),
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				"param_name"       => "align",
				"value"            => array(
					__( "Unset", 'xstore' )  => '',
					__( "Start", 'xstore' )  => 'start',
					__( "Center", 'xstore' ) => 'center',
					__( "End", 'xstore' )    => 'end',
				),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "xstore_button_set",
				"heading"          => esc_html__( "Mobile Align", 'xstore' ),
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				"param_name"       => "mob_align",
				"value"            => array(
					__( "Inherit", 'xstore' ) => '',
					__( "Start", 'xstore' )   => 'start',
					__( "Center", 'xstore' )  => 'center',
					__( "End", 'xstore' )     => 'end',
				),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'Advanced options', 'xstore' ),
				'group'      => esc_html__( 'Design Options', 'xstore' ),
				'param_name' => 'transform_xstore_options_divider',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "Translate X", 'xstore' ),
				"param_name"       => "translate_x",
				'description'      => esc_html__( 'Examples: 0px, 10%, -15px', 'xstore' ),
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "Translate Y", 'xstore' ),
				"param_name"       => "translate_y",
				'description'      => esc_html__( 'Examples: 0px, 10%, -15px', 'xstore' ),
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"       => "textfield",
				"heading"    => esc_html__( "Z-index", 'xstore' ),
				"param_name" => "z_index",
				"group"      => esc_html__( 'Design Options', 'xstore' ),
			),
			array(
				'type'       => 'hidden',
				'param_name' => 'et_uniqid',
				"group"      => esc_html__( 'Design Options', 'xstore' ),
				'value'      => 'et_custom_uniqid_' . uniqid(), // old
			),
			array(
				'type'       => 'xstore_uniqid',
				'param_name' => '_et_uniqid', // new
				"group"      => esc_html__( 'Design Options', 'xstore' ),
			)
		) );
		
		vc_add_params( 'vc_column_inner', array(
			array(
				'type'       => 'hidden',
				'param_name' => 'et_uniqid',
				"group"      => esc_html__( 'Design Options', 'xstore' ),
				'value'      => 'et_custom_uniqid_' . uniqid(), // old
			),
			array(
				'type'       => 'xstore_uniqid',
				'param_name' => '_et_uniqid', // new
				"group"      => esc_html__( 'Design Options', 'xstore' ),
			)
		) );
		
		vc_add_params( 'vc_row', array(
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'XStore options', 'xstore' ),
				'group'      => esc_html__( 'Design Options', 'xstore' ),
				'param_name' => 'row_xstore_options_divider',
			),
			array(
				"type"             => "dropdown",
				"heading"          => esc_html__( "Background position", 'xstore' ),
				"param_name"       => "background_position",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				"value"            => array(
					''                              => '',
					__( "Left top", 'xstore' )      => 'left_top',
					__( "Left center", 'xstore' )   => 'left',
					__( "Left bottom", 'xstore' )   => 'left_bottom',
					__( "Right top", 'xstore' )     => 'right_top',
					__( "Right center", 'xstore' )  => 'right',
					__( "Right bottom", 'xstore' )  => 'right_bottom',
					__( "Center top", 'xstore' )    => 'center_top',
					__( "Center center", 'xstore' ) => 'center',
					__( "Center bottom", 'xstore' ) => 'center_bottom',
				),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "dropdown",
				"heading"          => esc_html__( "Fixed background position", 'xstore' ),
				"param_name"       => "fixed_background",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				"value"            => array(
					''                              => '',
					__( "Left top", 'xstore' )      => 'left_top',
					__( "Left center", 'xstore' )   => 'left',
					__( "Left bottom", 'xstore' )   => 'left_bottom',
					__( "Right top", 'xstore' )     => 'right_top',
					__( "Right center", 'xstore' )  => 'right',
					__( "Right bottom", 'xstore' )  => 'right_bottom',
					__( "Center top", 'xstore' )    => 'center_top',
					__( "Center center", 'xstore' ) => 'center',
					__( "Center bottom", 'xstore' ) => 'center_bottom',
				),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Columns reverse on mobile", 'xstore' ),
				"param_name" => "columns_reverse",
				"group"      => esc_html__( 'Design Options', 'xstore' ),
			),
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'Advanced options', 'xstore' ),
				'group'      => esc_html__( 'Design Options', 'xstore' ),
				'param_name' => 'transform_xstore_options_divider',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "Translate X", 'xstore' ),
				"param_name"       => "translate_x",
				'description'      => esc_html__( 'Examples: 0px, 10%, -15px', 'xstore' ),
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "Translate Y", 'xstore' ),
				"param_name"       => "translate_y",
				'description'      => esc_html__( 'Examples: 0px, 10%, -15px', 'xstore' ),
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"       => "textfield",
				"heading"    => esc_html__( "Z-index", 'xstore' ),
				"param_name" => "z_index",
				"group"      => esc_html__( 'Design Options', 'xstore' ),
			),
			array(
				'type'       => 'hidden',
				'param_name' => 'et_uniqid',
				"group"      => esc_html__( 'Design Options', 'xstore' ),
				'value'      => 'et_custom_uniqid_' . uniqid(), // old
			),
			array(
				'type'       => 'xstore_uniqid',
				'param_name' => '_et_uniqid', // new
				"group"      => esc_html__( 'Design Options', 'xstore' ),
			)
		) );
		
		vc_add_params( 'vc_section', array(
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'XStore options', 'xstore' ),
				'group'      => esc_html__( 'Design Options', 'xstore' ),
				'param_name' => 'section_xstore_options_divider',
			),
			array(
				"type"             => "dropdown",
				"heading"          => esc_html__( "Background position", 'xstore' ),
				"param_name"       => "background_position",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				"value"            => array(
					''                              => '',
					__( "Left top", 'xstore' )      => 'left_top',
					__( "Left center", 'xstore' )   => 'left',
					__( "Left bottom", 'xstore' )   => 'left_bottom',
					__( "Right top", 'xstore' )     => 'right_top',
					__( "Right center", 'xstore' )  => 'right',
					__( "Right bottom", 'xstore' )  => 'right_bottom',
					__( "Center top", 'xstore' )    => 'center_top',
					__( "Center center", 'xstore' ) => 'center',
					__( "Center bottom", 'xstore' ) => 'center_bottom',
				),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "dropdown",
				"heading"          => esc_html__( "Fixed background position", 'xstore' ),
				"param_name"       => "fixed_background",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				"value"            => array(
					''                              => '',
					__( "Left top", 'xstore' )      => 'left_top',
					__( "Left center", 'xstore' )   => 'left',
					__( "Left bottom", 'xstore' )   => 'left_bottom',
					__( "Right top", 'xstore' )     => 'right_top',
					__( "Right center", 'xstore' )  => 'right',
					__( "Right bottom", 'xstore' )  => 'right_bottom',
					__( "Center top", 'xstore' )    => 'center_top',
					__( "Center center", 'xstore' ) => 'center',
					__( "Center bottom", 'xstore' ) => 'center_bottom',
				),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Columns reverse on mobile", 'xstore' ),
				"param_name" => "columns_reverse",
				"group"      => esc_html__( 'Design Options', 'xstore' ),
			),
			array(
				'type'       => 'hidden',
				'param_name' => 'et_uniqid',
				"group"      => esc_html__( 'Design Options', 'xstore' ),
				'value'      => 'et_custom_uniqid_' . uniqid(), // old
			),
			array(
				'type'       => 'xstore_uniqid',
				'param_name' => '_et_uniqid', // new
				"group"      => esc_html__( 'Design Options', 'xstore' ),
			)
		) );
		
		$box_shadow = array(
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'Box shadow', 'xstore' ),
				'group'      => esc_html__( 'Design Options', 'xstore' ),
				'param_name' => 'box_shadow_options_divider',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "Horizontal offset", 'xstore' ),
				"param_name"       => "box_shadow_h",
				'description'      => esc_html__( 'Examples: 0px, 3px, 5px', 'xstore' ),
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "Vertical offset", 'xstore' ),
				"param_name"       => "box_shadow_v",
				'description'      => esc_html__( 'Examples: 0px, 3px, 5px', 'xstore' ),
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "Blur", 'xstore' ),
				"param_name"       => "box_shadow_blur",
				'description'      => esc_html__( 'Examples: 0px, 3px, 5px', 'xstore' ),
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "Spread", 'xstore' ),
				"param_name"       => "box_shadow_spread",
				'description'      => esc_html__( 'Examples: 0px, 3px, 5px', 'xstore' ),
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "checkbox",
				"heading"          => esc_html__( "Box shadow inset", 'xstore' ),
				"param_name"       => "box_shadow_inset",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-6',
			),
			array(
				"type"             => "colorpicker",
				"heading"          => __( "Box shadow color", "xstore" ),
				"param_name"       => "box_shadow_color",
				"group"            => esc_html__( 'Design Options', 'xstore' ),
				'edit_field_class' => 'vc_col-xs-6',
			),
		);
		
		vc_add_params( 'vc_column', $box_shadow );
		
		vc_add_params( 'vc_column_inner', $box_shadow );
		
		vc_add_params( 'vc_row', $box_shadow );
		
		vc_add_params( 'vc_row_inner', $box_shadow );
		
		vc_add_params( 'vc_section', $box_shadow );
		
	}
}

if ( ! function_exists( 'etheme_get_slider_params' ) ) {
	function etheme_get_slider_params( $dependency = false ) {
		
		$counter         = 0;
		$slider_settings = array(
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'General settings', 'xstore' ),
				'group'      => esc_html__( 'Slider settings', 'xstore' ),
				'param_name' => 'slider_divider' . $counter ++
			),
			array(
				"type"        => "textfield",
				"heading"     => esc_html__( "Slider speed", 'xstore' ),
				"param_name"  => "slider_speed",
				"group"       => esc_html__( 'Slider settings', 'xstore' ),
				"description" => sprintf( esc_html__( 'Duration of transition between slides. Default: 300', 'xstore' ) ),
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Slider autoplay", 'xstore' ),
				"param_name" => "slider_autoplay",
				"group"      => esc_html__( 'Slider settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' )
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Stop autoplay on mouseover", 'xstore' ),
				"param_name" => "slider_stop_on_hover",
				"group"      => esc_html__( 'Slider settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' ),
				'dependency' => array(
					'element' => 'slider_autoplay',
					'value'   => 'yes',
				),
			),
			array(
				"type"        => "textfield",
				"heading"     => esc_html__( "Autoplay speed", 'xstore' ),
				"param_name"  => "slider_interval",
				"group"       => esc_html__( 'Slider settings', 'xstore' ),
				"description" => sprintf( esc_html__( 'Interval between slides. In milliseconds. Default: 1000', 'xstore' ) ),
				'dependency'  => array(
					'element' => 'slider_autoplay',
					'value'   => 'yes',
				),
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Slider loop", 'xstore' ),
				"param_name" => "slider_loop",
				"group"      => esc_html__( 'Slider settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' ),
			),
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'Navigation settings', 'xstore' ),
				'group'      => esc_html__( 'Slider settings', 'xstore' ),
				'param_name' => 'slider_divider' . $counter ++
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Hide prev/next buttons", 'xstore' ),
				"param_name" => "hide_buttons",
				"group"      => esc_html__( 'Slider settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' )
			),
			array(
				'type'       => 'xstore_button_set',
				'heading'    => esc_html__( 'Hide navigation only for', 'xstore' ),
				'param_name' => 'hide_buttons_for',
				'dependency' => array(
					'element' => 'hide_buttons',
					'value'   => 'yes',
				),
				'group'      => esc_html__( 'Slider settings', 'xstore' ),
				'value'      => array(
					__( 'Both', 'xstore' )    => '',
					__( 'Mobile', 'xstore' )  => 'mobile',
					__( 'Desktop', 'xstore' ) => 'desktop',
				),
			),
			array(
				'type'       => 'xstore_button_set',
				'heading'    => esc_html__( 'Pagination type', 'xstore' ),
				'param_name' => 'pagination_type',
				'group'      => esc_html__( 'Slider settings', 'xstore' ),
				'value'      => array(
					__( 'Hide', 'xstore' )    => 'hide',
					__( 'Bullets', 'xstore' ) => 'bullets',
					__( 'Lines', 'xstore' )   => 'lines',
				),
			),
			array(
				'type'       => 'xstore_button_set',
				'heading'    => esc_html__( 'Hide pagination only for', 'xstore' ),
				'param_name' => 'hide_fo',
				'dependency' => array(
					'element' => 'pagination_type',
					'value'   => array( 'bullets', 'lines' ),
				),
				'group'      => esc_html__( 'Slider settings', 'xstore' ),
				'value'      => array(
					__( 'Unset', 'xstore' )   => '',
					__( 'Mobile', 'xstore' )  => 'mobile',
					__( 'Desktop', 'xstore' ) => 'desktop',
				),
			),
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'Navigation colors', 'xstore' ),
				'group'      => esc_html__( 'Slider settings', 'xstore' ),
				'param_name' => 'slider_divider' . $counter ++,
				'dependency' => array(
					'element' => 'pagination_type',
					'value'   => array( 'bullets', 'lines' ),
				),
			),
			array(
				"type"             => "colorpicker",
				"heading"          => __( "Pagination default color", "xstore" ),
				"param_name"       => "default_color",
				'dependency'       => array(
					'element' => 'pagination_type',
					'value'   => array( 'bullets', 'lines' ),
				),
				"group"            => esc_html__( 'Slider settings', 'xstore' ),
				"value"            => '#e1e1e1',
				'edit_field_class' => 'vc_col-sm-6 vc_column',
			),
			array(
				"type"             => "colorpicker",
				"heading"          => __( "Pagination active color", "xstore" ),
				"param_name"       => "active_color",
				'dependency'       => array(
					'element' => 'pagination_type',
					'value'   => array( 'bullets', 'lines' ),
				),
				"group"            => esc_html__( 'Slider settings', 'xstore' ),
				"value"            => '#222',
				'edit_field_class' => 'vc_col-sm-6 vc_column',
			),
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'Responsive settings', 'xstore' ),
				'group'      => esc_html__( 'Slider settings', 'xstore' ),
				'subtitle'   => esc_html__( 'Number of slides per view', 'xstore' ),
				'param_name' => 'slider_divider' . $counter ++
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "Large screens", 'xstore' ),
				"param_name"       => "large",
				"group"            => esc_html__( 'Slider settings', 'xstore' ),
				'edit_field_class' => 'vc_col-md-3 vc_col-xs-6 vc_column',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "On notebooks", 'xstore' ),
				"param_name"       => "notebook",
				"group"            => esc_html__( 'Slider settings', 'xstore' ),
				'edit_field_class' => 'vc_col-md-3 vc_col-xs-6 vc_column',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "On tablet portrait", 'xstore' ),
				"param_name"       => "tablet_land",
				"group"            => esc_html__( 'Slider settings', 'xstore' ),
				'edit_field_class' => 'vc_col-md-3 vc_col-xs-6 vc_column',
			),
			array(
				"type"             => "textfield",
				"heading"          => esc_html__( "On mobile", 'xstore' ),
				"param_name"       => "mobile",
				"group"            => esc_html__( 'Slider settings', 'xstore' ),
				'edit_field_class' => 'vc_col-md-3 vc_col-xs-6 vc_column',
			),
		);
		
		if ( is_array( $dependency ) ) {
			foreach ( $slider_settings as $setting => $value ) {
				if ( ! isset( $slider_settings[ $setting ]['dependency'] ) ) {
					$slider_settings[ $setting ]['dependency'] = $dependency;
				}
			}
		}
		
		return $slider_settings;
		
	}
}

if ( ! function_exists( 'etheme_get_brands_list_params' ) ) {
	function etheme_get_brands_list_params() {
		
		$counter = 0;
		
		return array(
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'Layout settings', 'xstore' ),
				'group'      => esc_html__( 'Brand settings', 'xstore' ),
				'param_name' => 'brands_divider' . $counter ++
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Display A-Z filter", 'xstore' ),
				"param_name" => "hide_a_z",
				"group"      => esc_html__( 'Brand settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' )
			),
			array(
				'type'       => 'xstore_button_set',
				'heading'    => esc_html__( 'Columns', 'xstore' ),
				'param_name' => 'columns',
				'group'      => esc_html__( 'Brand settings', 'xstore' ),
				'value'      => array(
					__( '1', 'xstore' ) => '1',
					__( '2', 'xstore' ) => '2',
					__( '3', 'xstore' ) => '3',
					__( '4', 'xstore' ) => '4',
					__( '5', 'xstore' ) => '5',
					__( '6', 'xstore' ) => '6',
				),
			),
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'Styles', 'xstore' ),
				'group'      => esc_html__( 'Brand settings', 'xstore' ),
				'param_name' => 'brands_divider' . $counter ++
			),
			array(
				'type'       => 'xstore_button_set',
				'heading'    => esc_html__( 'Alignment', 'xstore' ),
				'param_name' => 'alignment',
				'group'      => esc_html__( 'Brand settings', 'xstore' ),
				'value'      => array(
					__( 'Left', 'xstore' )   => 'left',
					__( 'Center', 'xstore' ) => 'center',
					__( 'Right', 'xstore' )  => 'right',
				),
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Display brands capital letter", 'xstore' ),
				"param_name" => "capital_letter",
				"group"      => esc_html__( 'Brand settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' )
			),
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'Image settings', 'xstore' ),
				'group'      => esc_html__( 'Brand settings', 'xstore' ),
				'param_name' => 'brands_divider' . $counter ++
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Brand image", 'xstore' ),
				"param_name" => "brand_image",
				"group"      => esc_html__( 'Brand settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' )
			),
			array(
				'type'       => 'textfield',
				'heading'    => esc_html__( 'Images size', 'xstore' ),
				'param_name' => 'size',
				'value'      => '',
				'group'      => esc_html__( 'Brand settings', 'xstore' ),
				'dependency' => array( 'element' => "brand_image", 'value' => array( 'yes' ) ),
			),
			array(
				'type'       => 'xstore_title_divider',
				'title'      => esc_html__( 'Content settings', 'xstore' ),
				'group'      => esc_html__( 'Brand settings', 'xstore' ),
				'param_name' => 'brands_divider' . $counter ++
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Brand title", 'xstore' ),
				"param_name" => "brand_title",
				"group"      => esc_html__( 'Brand settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' ),
				'std'        => 'yes'
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Title with tooltip", 'xstore' ),
				"param_name" => "tooltip",
				"group"      => esc_html__( 'Brand settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' )
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Brand description ", 'xstore' ),
				"param_name" => "brand_desc",
				"group"      => esc_html__( 'Brand settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' )
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Hide empty", 'xstore' ),
				"param_name" => "hide_empty",
				"group"      => esc_html__( 'Brand settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' )
			),
			array(
				"type"       => "checkbox",
				"heading"    => esc_html__( "Show Product Counts", 'xstore' ),
				"param_name" => "show_product_counts",
				"group"      => esc_html__( 'Brand settings', 'xstore' ),
				'value'      => array( esc_html__( 'Yes, please', 'xstore' ) => 'yes' )
			),
		);
	}
}

// **********************************************************************//
// ! Rewrite vc google font
// **********************************************************************//

if ( ! function_exists( 'et_rewrite_vc_google_font' ) ) {
	
	function et_rewrite_vc_google_font( $fonts ) {
		
		$fonts_list = '[{"font_family":"Catamaran","font_styles":"regular","font_types":"100 thin:100:normal,200 extra-light:200:normal,300 light:300:normal,400 regular:400:normal,500 medium:500:normal,600 semi-bold:600:normal,700 bold:700:normal,800 extra-bold:800:normal,900 black:900:normal"},{"font_family":"Cormorant Garamond","font_styles":"serif,regular,italic,300italic,400italic,500italic,600italic,700italic","font_types":"300 light regular:300:normal,300 light italic:300:italic,400 regular:400:normal,400 italic:400:italic,500 bold regular:500:normal,500 bold italic:500:italic,700 bold regular:700:normal,700 bold italic:700:italic"},{"font_family":"Norican","font_styles":"cursive","font_types":"400 regular:400:normal"},{"font_family":"Molle","font_styles":"400i","font_types":"400 italic:400:italic"},{"font_family":"Palanquin","font_styles":"regular","font_types":"100 thin:100:normal,200 extra-light:200:normal,300 light:300:normal,400 regular:400:normal,500 medium:500:normal,600 semi-bold:600:normal,700 bold:700:normal"},{"font_family":"Trirong","font_styles":"serif,regular,italic,100italic,200italic,300italic,400italic,500italic,600italic,700italic,800italic,900italic","font_types":"100 lighter regular:100:normal,100 lighter italic:100:italic,200 lighter regular:200:normal,200 lighter italic:200:italic,300 light regular:300:normal,300 light italic:300:italic,400 regular:400:normal,400 italic:400:italic,500 bold regular:500:normal,500 bold italic:500:italic,600 bold regular:600:normal,600 bold italic:600:italic,700 bold regular:700:normal,700 bold italic:700:italic,800 bolder regular:800:normal,800 bolder italic:800:italic,900 bolder regular:900:normal,900 bolder italic:900:italic"}, {"font_family":"Yantramanav","font_styles":"100,300,regular,500,700,900","font_types":"100 ultra-light regular:100:normal,300 light regular:300:normal,400 regular:400:normal,500 medium regular:500:normal,700 bold regular:700:normal,900 ultra-bold regular:900:normal"},{"font_family":"Poppins","font_styles":"regular","font_types":"300 light:300:normal,400 regular:400:normal,500 medium:500:normal,600 semi-bold:600:normal,700 bold:700:normal,800 extra-bold:800:normal,800 extra-bold italic:800:italic,900 black:900:normal, 900 black italic:900:italic"}]';
		
		$et_fonts      = get_option( 'etheme-fonts', false );
		$et_fonts_list = array();
		
		if ( $et_fonts ) {
			foreach ( $et_fonts as $value ) {
				$et_fonts_list[] = '{"font_family":"' . $value['name'] . '"}';
			}
		}
		
		$et_fonts_list = '[' . implode( ',', $et_fonts_list ) . ']';
		
		$fonts = array_merge( json_decode( $et_fonts_list ), $fonts, json_decode( $fonts_list ) );
		
		return $fonts;
	}
	
	add_filter( 'vc_google_fonts_get_fonts_filter', 'et_rewrite_vc_google_font', 1, 10 );
	
}

// Etheme content product shortcode included to vc grid type
add_filter( 'vc_grid_item_shortcodes', 'et_add_vc_grid_shortcodes' );
function et_add_vc_grid_shortcodes( $shortcodes ) {
	
	require_once vc_path_dir( 'CONFIG_DIR', 'content/vc-custom-heading-element.php' );
	$title_custom_heading = vc_map_integrate_shortcode( vc_custom_heading_element_params(), 'title_', esc_html__( 'Typography', 'xstore' ), array(
		'exclude' => array(
			'link',
			'source',
			'text',
			'css',
			'el_class',
			'css_animation'
		),
	), array(
		'element' => 'use_custom_fonts_title',
		'value'   => 'true',
	) );
	
	// This is needed to remove custom heading _tag and _align options.
	if ( is_array( $title_custom_heading ) && ! empty( $title_custom_heading ) ) {
		foreach ( $title_custom_heading as $key => $param ) {
			if ( is_array( $param ) && isset( $param['type'] ) && 'font_container' === $param['type'] ) {
				$title_custom_heading[ $key ]['value'] = '';
				if ( isset( $param['settings'] ) && is_array( $param['settings'] ) && isset( $param['settings']['fields'] ) ) {
					$sub_key = array_search( 'text_align', $param['settings']['fields'] );
					if ( false !== $sub_key ) {
						unset( $title_custom_heading[ $key ]['settings']['fields'][ $sub_key ] );
					} elseif ( isset( $param['settings']['fields']['text_align'] ) ) {
						unset( $title_custom_heading[ $key ]['settings']['fields']['text_align'] );
					}
					$sub_key = array_search( 'font_size', $param['settings']['fields'] );
					if ( false !== $sub_key ) {
						unset( $title_custom_heading[ $key ]['settings']['fields'][ $sub_key ] );
					} elseif ( isset( $param['settings']['fields']['font_size'] ) ) {
						unset( $title_custom_heading[ $key ]['settings']['fields']['font_size'] );
					}
					$sub_key = array_search( 'line_height', $param['settings']['fields'] );
					if ( false !== $sub_key ) {
						unset( $title_custom_heading[ $key ]['settings']['fields'][ $sub_key ] );
					} elseif ( isset( $param['settings']['fields']['line_height'] ) ) {
						unset( $title_custom_heading[ $key ]['settings']['fields']['line_height'] );
					}
					$sub_key = array_search( 'color', $param['settings']['fields'] );
					if ( false !== $sub_key ) {
						unset( $title_custom_heading[ $key ]['settings']['fields'][ $sub_key ] );
					} elseif ( isset( $param['settings']['fields']['color'] ) ) {
						unset( $title_custom_heading[ $key ]['settings']['fields']['color'] );
					}
				}
			}
		}
	}
	
	$horiz_align = array(
		__( 'Left', 'xstore' )    => 'left',
		__( 'Right', 'xstore' )   => 'right',
		__( 'Center', 'xstore' )  => 'center',
		__( 'Justify', 'xstore' ) => 'justify'
	);
	
	$text_transform = array(
		__( 'None', 'xstore' )       => '',
		__( 'Uppercase', 'xstore' )  => 'text-uppercase',
		__( 'Lowercase', 'xstore' )  => 'text-lowercase',
		__( 'Capitalize', 'xstore' ) => 'text-capitalize'
	);
	
	$compare_arr = $compare_checkbox = array();
	
	$sorted_list = array(
		array(
			'type'        => 'sorted_list',
			'heading'     => __( 'Buttons layout', 'xstore' ),
			'param_name'  => 'sorting',
			'description' => __( 'Sorting the buttons layout', 'xstore' ),
			'value'       => 'cart,wishlist,q_view',
			'options'     => array(
				array(
					'cart',
					__( 'Add to cart', 'xstore' ),
				),
				array(
					'wishlist',
					__( 'Wishlist', 'xstore' ),
				),
				array(
					'q_view',
					__( 'Quick view', 'xstore' ),
				),
			),
		)
	);
	
	$sizes_select2 = array();
	
	if ( function_exists('etheme_get_image_sizes')) {
		
		$sizes = etheme_get_image_sizes();
		foreach ( $sizes as $size => $value ) {
			$sizes[ $size ] = $sizes[ $size ]['width'] . 'x' . $sizes[ $size ]['height'];
		}
		
		$sizes_select = array(
			'shop_catalog'                  => 'shop_catalog',
			'woocommerce_thumbnail'         => 'woocommerce_thumbnail',
			'woocommerce_gallery_thumbnail' => 'woocommerce_gallery_thumbnail',
			'woocommerce_single'            => 'woocommerce_single',
			'shop_thumbnail'                => 'shop_thumbnail',
			'shop_single'                   => 'shop_single',
			'thumbnail'                     => 'thumbnail',
			'medium'                        => 'medium',
			'large'                         => 'large',
			'full'                          => 'full'
		);
		
		foreach ( $sizes_select as $item => $value ) {
			if ( isset( $sizes[ $item ] ) ) {
				$sizes_select2[ $item ] = $value . ' (' . $sizes[ $item ] . ')';
			} else {
				$sizes_select2[ $item ] = $value;
			}
		}
		
		$sizes_select2 = array_flip( $sizes_select2 );
		$sizes_select2['custom'] = 'custom';
		
	}
	
	if ( class_exists( 'YITH_Woocompare' ) ) {
		$compare_checkbox = array(
			array(
				'type'        => 'checkbox',
				'heading'     => esc_html__( 'Compare', 'xstore' ),
				'param_name'  => 'compare',
				'description' => esc_html__( 'Compare button.', 'xstore' ),
			),
		);
		$compare_arr      = array(
			array(
				'type'       => 'xstore_button_set',
				'heading'    => esc_html__( 'Type', 'xstore' ),
				'param_name' => 'compare_type',
				'value'      => array(
					__( 'Icon', 'xstore' )        => 'icon',
					__( 'Text', 'xstore' )        => 'text',
					__( 'Icon + text', 'xstore' ) => 'icon-text',
					__( 'Button', 'xstore' )      => 'button',
				),
				'group'      => 'Compare',
				'dependency' => array( 'element' => 'compare', 'value' => 'true' )
			),
			array(
				'type'       => 'textfield',
				'heading'    => esc_html__( 'Font size', 'xstore' ),
				'param_name' => 'c_size',
				'group'      => 'Compare',
				'dependency' => array( 'element' => 'compare', 'value' => 'true' )
			),
			array(
				'type'       => 'xstore_button_set',
				'heading'    => esc_html__( 'Text transform', 'xstore' ),
				'param_name' => 'c_transform',
				'value'      => array(
					__( 'None', 'xstore' )       => '',
					__( 'Uppercase', 'xstore' )  => 'uppercase',
					__( 'Lowercase', 'xstore' )  => 'lowercase',
					__( 'Capitalize', 'xstore' ) => 'capitalize'
				),
				'group'      => 'Compare',
				'dependency' => array( 'element' => 'compare_type', 'value_not_equal_to' => 'icon' ),
			),
			array(
				'type'             => 'colorpicker',
				'heading'          => esc_html__( 'Button background color', 'xstore' ),
				'param_name'       => 'c_bg',
				'group'            => 'Compare',
				'dependency'       => array( 'element' => 'compare_type', 'value' => array( 'button', 'icon' ) ),
				'edit_field_class' => 'vc_col-sm-6 vc_column',
			),
			array(
				'type'             => 'colorpicker',
				'heading'          => esc_html__( 'Button background color (hover)', 'xstore' ),
				'param_name'       => 'c_hover_bg',
				'group'            => 'Compare',
				'dependency'       => array( 'element' => 'compare_type', 'value' => array( 'button', 'icon' ) ),
				'edit_field_class' => 'vc_col-sm-6 vc_column',
			),
			array(
				'type'             => 'textfield',
				'heading'          => 'Border radius',
				'param_name'       => 'c_radius',
				'group'            => 'Compare',
				'dependency'       => array( 'element' => 'compare_type', 'value' => array( 'button', 'icon' ) ),
				'edit_field_class' => 'vc_col-sm-6 vc_column',
			),
			array(
				'type'             => 'textfield',
				'heading'          => esc_html__( 'Margins (top right bottom left)', 'xstore' ),
				'param_name'       => 'c_margin',
				'group'            => 'Compare',
				'description'      => esc_html__( 'Use this field to add element margin. For example 10px 20px 30px 40px', 'xstore' ),
				'dependency'       => array( 'element' => 'compare_type', 'value' => array( 'button', 'icon' ) ),
				'edit_field_class' => 'vc_col-sm-6 vc_column',
			),
		);
		$sorted_list      = array(
			array(
				'type'        => 'sorted_list',
				'heading'     => __( 'Buttons layout', 'xstore' ),
				'param_name'  => 'sorting',
				'description' => __( 'Sorting the buttons layout', 'xstore' ),
				'value'       => 'cart,wishlist,compare,q_view',
				'options'     => array(
					array(
						'cart',
						__( 'Add to cart', 'xstore' ),
					),
					array(
						'wishlist',
						__( 'Wishlist', 'xstore' ),
					),
					array(
						'compare',
						__( 'Compare', 'xstore' ),
					),
					array(
						'q_view',
						__( 'Quick view', 'xstore' ),
					),
				),
			)
		);
	}
	
	$shortcodes['etheme_product_name'] = array(
		'name'        => __( 'Product title', 'xstore' ),
		'base'        => 'etheme_product_name',
		'category'    => __( 'Content product by 8theme', 'xstore' ),
		'icon'        => ETHEME_CODE_IMAGES . 'vc/Autoscrolling-text.png',
		'description' => __( 'Show current product name', 'xstore' ),
		'post_type'   => Vc_Grid_Item_Editor::postType(),
		'params'      => array_merge(
			array(
				array(
					'type'       => 'xstore_button_set',
					'heading'    => __( 'Add link', 'xstore' ),
					'param_name' => 'link',
					'value'      => array(
						__( 'Product link', 'xstore' ) => 'product_link',
						__( 'Custom link', 'xstore' )  => 'custom',
						__( 'None', 'xstore' )         => ''
					),
				),
				array(
					'type'       => 'vc_link',
					'heading'    => __( 'Custom link', 'xstore' ),
					'param_name' => 'url',
					'dependency' => array(
						'element' => 'link',
						'value'   => 'custom',
					),
				),
				array(
					'type'       => 'xstore_button_set',
					'heading'    => __( 'Cut product name', 'xstore' ),
					'param_name' => 'cutting',
					'value'      => array(
						__( 'None', 'xstore' )    => 'none',
						__( 'Words', 'xstore' )   => 'words',
						__( 'Letters', 'xstore' ) => 'letters'
					),
				),
				array(
					'type'       => 'textfield',
					'heading'    => __( 'Count words/letters', 'xstore' ),
					'param_name' => 'count',
					'dependency' => array(
						'element'            => 'cutting',
						'value_not_equal_to' => 'none'
					),
				),
				array(
					'type'        => 'textfield',
					'heading'     => __( 'Symbols', 'xstore' ),
					'param_name'  => 'symbols',
					'description' => esc_html__( 'Default "...".', 'xstore' ),
					'dependency'  => array(
						'element'            => 'cutting',
						'value_not_equal_to' => 'none'
					),
				),
				array(
					'type'       => 'xstore_button_set',
					'heading'    => __( 'Text align', 'xstore' ),
					'param_name' => 'align',
					'value'      => $horiz_align,
				),
				array(
					'type'       => 'textfield',
					'heading'    => __( 'Font size', 'xstore' ),
					'param_name' => 'size',
					'group'      => 'Typography'
				),
				array(
					'type'       => 'textfield',
					'heading'    => __( 'Letter spacing', 'xstore' ),
					'param_name' => 'spacing',
					'group'      => 'Typography'
				),
				array(
					'type'       => 'textfield',
					'heading'    => __( 'Line height', 'xstore' ),
					'param_name' => 'line_height',
					'group'      => 'Typography'
				),
				array(
					'type'       => 'colorpicker',
					'heading'    => __( 'Color', 'xstore' ),
					'param_name' => 'color',
					'group'      => 'Typography'
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Use custom font ?', 'xstore' ),
					'param_name'  => 'use_custom_fonts_title',
					'description' => esc_html__( 'Enable Google fonts.', 'xstore' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => __( 'Extra class name', 'xstore' ),
					'param_name'  => 'el_class',
					'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'xstore' ),
				),
			),
			$title_custom_heading,
			array(
				array(
					'type'       => 'css_editor',
					'heading'    => esc_html__( 'CSS box', 'xstore' ),
					'param_name' => 'css',
					'group'      => esc_html__( 'Design', 'xstore' )
				),
			)
		),
	);
	
	$shortcodes['etheme_product_image'] = array(
		'name'      => __( 'Product image', 'xstore' ),
		'base'      => 'etheme_product_image',
		'category'  => __( 'Content product by 8theme', 'xstore' ),
		'icon'      => ETHEME_CODE_IMAGES . 'vc/Image.png',
		'post_type' => Vc_Grid_Item_Editor::postType(),
		'params'    => array(
			array(
				'type'       => 'xstore_button_set',
				'heading'    => __( 'Add link', 'xstore' ),
				'param_name' => 'link',
				'value'      => array(
					__( 'Product link', 'xstore' ) => 'product_link',
					__( 'Custom link', 'xstore' )  => 'custom',
					__( 'None', 'xstore' )         => ''
				),
			),
			array(
				'type'        => 'vc_link',
				'heading'     => __( 'URL (Link)', 'xstore' ),
				'param_name'  => 'url',
				'dependency'  => array(
					'element' => 'link',
					'value'   => array( 'custom' ),
				),
				'description' => __( 'Add custom link.', 'xstore' ),
			),
			array(
				'type'        => 'xstore_button_set',
				'heading'     => __( 'Image alignment', 'xstore' ),
				'param_name'  => 'align',
				'value'       => array_diff( $horiz_align, array( __( 'Justify', 'xstore' ) => 'justify' ) ),
				'description' => __( 'Select image alignment.', 'xstore' ),
			),
			array(
				'type'        => 'dropdown',
				'heading'     => __( 'Image style', 'xstore' ),
				'param_name'  => 'style',
				'value'       => vc_get_shared( 'single image styles' ),
				'description' => __( 'Select image display style.', 'xstore' ),
			),
			array(
				'type'       => 'xstore_button_set',
				'heading'    => __( 'Hover effect', 'xstore' ),
				'param_name' => 'hover',
				'value'      => array(
					__( 'Disable', 'xstore' ) => '',
					__( 'Swap', 'xstore' )    => 'swap',
					__( 'Slider', 'xstore' )  => 'slider'
				),
			),
			array(
				'type'       => 'dropdown',
				'param_name' => 'size',
				'heading'    => esc_html__( 'Image size', 'xstore' ),
				'value'      => $sizes_select2,
				'default'    => 'shop_catalog'
			),
			array(
				'type'        => 'textfield',
				'heading'    => esc_html__( 'Image size custom', 'xstore' ),
				'param_name'  => 'size_custom',
				'dependency'         => array(
					'element' => 'size',
					'value'   => array(
						'custom',
                    ),
				),
			),
			array(
				'type'       => 'xstore_button_set',
				'heading'    => __( 'Hide stock status', 'xstore' ),
				'param_name' => 'stock_status',
				'value'      => array(
					__( 'No', 'xstore' )  => 'no',
					__( 'Yes', 'xstore' ) => 'yes',
				),
			),
			array(
				'type'               => 'dropdown',
				'heading'            => __( 'Border color', 'xstore' ),
				'param_name'         => 'border_color',
				'value'              => vc_get_shared( 'colors' ),
				'std'                => 'grey',
				'dependency'         => array(
					'element' => 'style',
					'value'   => array(
						'vc_box_border',
						'vc_box_border_circle',
						'vc_box_outline',
						'vc_box_outline_circle',
					),
				),
				'description'        => __( 'Border color.', 'xstore' ),
				'param_holder_class' => 'vc_colored-dropdown',
			),
			array(
				'type'        => 'textfield',
				'heading'     => __( 'Extra class name', 'xstore' ),
				'param_name'  => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'xstore' ),
			),
			array(
				'type'       => 'css_editor',
				'heading'    => __( 'CSS box', 'xstore' ),
				'param_name' => 'css',
				'group'      => __( 'Design Options', 'xstore' ),
			),
		),
	);
	
	$shortcodes['etheme_product_excerpt'] = array(
		'name'      => __( 'Product excerpt', 'xstore' ),
		'base'      => 'etheme_product_excerpt',
		'category'  => __( 'Content product by 8theme', 'xstore' ),
		'icon'      => ETHEME_CODE_IMAGES . 'vc/Excerpt.png',
		'post_type' => Vc_Grid_Item_Editor::postType(),
		'params'    => array_merge(
			array(
				array(
					'type'       => 'xstore_button_set',
					'heading'    => __( 'Cut excerpt', 'xstore' ),
					'param_name' => 'cutting',
					'value'      => array(
						__( 'None', 'xstore' )    => 'none',
						__( 'Words', 'xstore' )   => 'words',
						__( 'Letters', 'xstore' ) => 'letters'
					),
				),
				array(
					'type'       => 'textfield',
					'heading'    => __( 'Count words/letters', 'xstore' ),
					'param_name' => 'count',
					'dependency' => array(
						'element'            => 'cutting',
						'value_not_equal_to' => 'none'
					),
				),
				array(
					'type'        => 'textfield',
					'heading'     => __( 'Symbols after string', 'xstore' ),
					'param_name'  => 'symbols',
					'description' => esc_html__( 'Default "...".', 'xstore' ),
					'dependency'  => array(
						'element'            => 'cutting',
						'value_not_equal_to' => 'none'
					),
				),
				array(
					'type'       => 'xstore_button_set',
					'heading'    => __( 'Text align', 'xstore' ),
					'param_name' => 'align',
					'value'      => $horiz_align,
				),
				array(
					'type'       => 'textfield',
					'heading'    => __( 'Font size', 'xstore' ),
					'param_name' => 'size',
					'group'      => 'Typography'
				),
				array(
					'type'       => 'textfield',
					'heading'    => __( 'Letter spacing', 'xstore' ),
					'param_name' => 'spacing',
					'group'      => 'Typography'
				),
				array(
					'type'       => 'textfield',
					'heading'    => __( 'Line height', 'xstore' ),
					'param_name' => 'line_height',
					'group'      => 'Typography'
				),
				array(
					'type'       => 'colorpicker',
					'heading'    => __( 'Color', 'xstore' ),
					'param_name' => 'color',
					'group'      => 'Typography'
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Use custom font ?', 'xstore' ),
					'param_name'  => 'use_custom_fonts_title',
					'description' => esc_html__( 'Enable Google fonts.', 'xstore' ),
				),
				array(
					'type'       => 'textfield',
					'heading'    => __( 'Class', 'xstore' ),
					'param_name' => 'el_class',
				),
			),
			$title_custom_heading,
			array(
				array(
					'type'       => 'css_editor',
					'heading'    => __( 'CSS box', 'xstore' ),
					'param_name' => 'css',
					'group'      => __( 'Design Options', 'xstore' ),
				)
			)
		),
	);
	
	$shortcodes['etheme_product_rating'] = array(
		'name'      => __( 'Product rating', 'xstore' ),
		'base'      => 'etheme_product_rating',
		'category'  => __( 'Content product by 8theme', 'xstore' ),
		'icon'      => ETHEME_CODE_IMAGES . 'vc/Rating.png',
		'post_type' => Vc_Grid_Item_Editor::postType(),
		'params'    => array(
			array(
				'type'       => 'checkbox',
				'heading'    => __( 'Show by default ?', 'xstore' ),
				'param_name' => 'default',
			),
			array(
				'type'       => 'textfield',
				'heading'    => __( 'Class', 'xstore' ),
				'param_name' => 'el_class',
			),
			array(
				'type'       => 'css_editor',
				'heading'    => __( 'CSS box', 'xstore' ),
				'param_name' => 'css',
				'group'      => __( 'Design Options', 'xstore' ),
			)
		),
	);
	
	$shortcodes['etheme_product_price'] = array(
		'name'      => __( 'Product price', 'xstore' ),
		'base'      => 'etheme_product_price',
		'category'  => __( 'Content product by 8theme', 'xstore' ),
		'icon'      => ETHEME_CODE_IMAGES . 'vc/Price.png',
		'post_type' => Vc_Grid_Item_Editor::postType(),
		'params'    => array(
			array(
				'type'       => 'xstore_button_set',
				'heading'    => __( 'Text align', 'xstore' ),
				'param_name' => 'align',
				'value'      => $horiz_align,
			),
			array(
				"type"       => 'textfield',
				"heading"    => __( 'Font size', 'xstore' ),
				"param_name" => 'size',
				'group'      => 'Typography',
			),
			array(
				"type"        => "textfield",
				"heading"     => "Letter spacing",
				"param_name"  => "spacing",
				'group'       => 'Typography',
				'description' => esc_html__( 'Enter letter spacing', 'xstore' ),
			),
			array(
				"type"       => 'colorpicker',
				"heading"    => __( 'Regular price color', 'xstore' ),
				"param_name" => 'color',
				'group'      => 'Typography',
			),
			array(
				"type"       => 'colorpicker',
				"heading"    => __( 'Sale price color', 'xstore' ),
				"param_name" => 'color_sale',
				'group'      => 'Typography'
			),
			array(
				'type'       => 'css_editor',
				'heading'    => __( 'CSS box', 'xstore' ),
				'param_name' => 'css',
				'group'      => __( 'Design Options', 'xstore' ),
			),
			array(
				'type'       => 'textfield',
				'heading'    => __( 'Class', 'xstore' ),
				'param_name' => 'el_class',
			),
		),
	);
	$shortcodes['etheme_product_sku']   = array(
		'name'      => __( 'Product sku', 'xstore' ),
		'base'      => 'etheme_product_sku',
		'category'  => __( 'Content product by 8theme', 'xstore' ),
		'icon'      => ETHEME_CODE_IMAGES . 'vc/Sku.png',
		'post_type' => Vc_Grid_Item_Editor::postType(),
		'params'    => array(
			array(
				'type'       => 'xstore_button_set',
				'heading'    => __( 'Text align', 'xstore' ),
				'param_name' => 'align',
				'value'      => $horiz_align,
			),
			array(
				'type'       => 'xstore_button_set',
				'heading'    => __( 'Text transform', 'xstore' ),
				'param_name' => 'transform',
				'value'      => $text_transform,
			),
			array(
				"type"       => 'textfield',
				"heading"    => __( 'Font size', 'xstore' ),
				"param_name" => 'size',
				'group'      => 'Typography',
			),
			array(
				"type"       => 'colorpicker',
				"heading"    => __( 'Color', 'xstore' ),
				"param_name" => 'color',
				'group'      => 'Typography',
			),
			array(
				'type'       => 'css_editor',
				'heading'    => __( 'CSS box', 'xstore' ),
				'param_name' => 'css',
				'group'      => __( 'Design Options', 'xstore' ),
			),
			array(
				'type'       => 'textfield',
				'heading'    => __( 'Class', 'xstore' ),
				'param_name' => 'el_class',
			),
		),
	);
	
	/* Product brands shortcode */
	
	
	/* Product stock */
	$shortcodes['etheme_product_stock'] = array(
		'name'      => __( 'Product stock', 'xstore' ),
		'base'      => 'etheme_product_stock',
		'category'  => __( 'Content product by 8theme', 'xstore' ),
		'icon'      => ETHEME_CODE_IMAGES . 'vc/Stock-status.png',
		'post_type' => Vc_Grid_Item_Editor::postType(),
		'params'    => array(
			array(
				'type'             => 'xstore_button_set',
				'heading'          => esc_html__( 'Stock type', 'xstore' ),
				'param_name'       => 'product_stock_type',
				'value'            => array(
					__( 'Default', 'xstore' )  => 'default',
					__( 'Advanced', 'xstore' ) => 'advanced',
				),
				'edit_field_class' => 'vc_col-sm-6 vc_column',
			),
			array(
				'type'       => 'xstore_button_set',
				'heading'    => __( 'Text align', 'xstore' ),
				'param_name' => 'align',
				'value'      => $horiz_align,
			),
			array(
				'type'       => 'colorpicker',
				'heading'    => esc_html__( 'Text color', 'xstore' ),
				'param_name' => 'color',
				'default'    => '#000'
			),
			array(
				'type'       => 'css_editor',
				'heading'    => __( 'CSS box', 'xstore' ),
				'param_name' => 'css',
				'group'      => __( 'Design Options', 'xstore' ),
			)
		),
	);
	
	$shortcodes['etheme_product_categories'] = array(
		'name'      => __( 'Product categories', 'xstore' ),
		'base'      => 'etheme_product_categories',
		'category'  => __( 'Content product by 8theme', 'xstore' ),
		'icon'      => ETHEME_CODE_IMAGES . 'vc/Categories.png',
		'post_type' => Vc_Grid_Item_Editor::postType(),
		'params'    => array_merge(
			array(
				array(
					'type'       => 'xstore_button_set',
					'heading'    => __( 'Text align', 'xstore' ),
					'param_name' => 'align',
					'value'      => $horiz_align,
				),
				array(
					'type'        => 'checkbox',
					'heading'     => esc_html__( 'Use custom font ?', 'xstore' ),
					'param_name'  => 'use_custom_fonts_title',
					'description' => esc_html__( 'Enable Google fonts.', 'xstore' ),
				),
				array(
					'type'       => 'textfield',
					'heading'    => __( 'Class', 'xstore' ),
					'param_name' => 'el_class',
				),
			),
			$title_custom_heading,
			array(
				array(
					'type'       => 'css_editor',
					'heading'    => __( 'CSS box', 'xstore' ),
					'param_name' => 'css',
					'group'      => __( 'Design Options', 'xstore' ),
				)
			)
		),
	);
	
	$shortcodes['etheme_product_buttons'] = array(
		'name'      => __( 'Product buttons ', 'xstore' ),
		'base'      => 'etheme_product_buttons',
		'category'  => __( 'Content product by 8theme', 'xstore' ),
		'icon'      => ETHEME_CODE_IMAGES . 'vc/Fancy-button.png',
		'post_type' => Vc_Grid_Item_Editor::postType(),
		'params'    => array_merge(
			$sorted_list,
			array(
				array(
					'type'       => 'xstore_button_set',
					'heading'    => esc_html__( 'Design type', 'xstore' ),
					'param_name' => 'type',
					'value'      => array(
						esc_html__( 'Horizontal', 'xstore' ) => '',
						esc_html__( 'Vertical', 'xstore' )   => 'vertical',
					),
				),
				array(
					'type'       => 'xstore_button_set',
					'heading'    => esc_html__( 'Align', 'xstore' ),
					'param_name' => 'align',
					'value'      => array(
						esc_html__( 'Left', 'xstore' )                  => 'start',
						esc_html__( 'Right', 'xstore' )                 => 'end',
						esc_html__( 'Center', 'xstore' )                => 'center',
						esc_html__( 'Stretch', 'xstore' )               => 'between',
						esc_html__( 'Stretch (no paddings)', 'xstore' ) => 'around',
					),
				),
				array(
					'type'       => 'xstore_button_set',
					'heading'    => esc_html__( 'Vertical align', 'xstore' ),
					'param_name' => 'v_align',
					'value'      => array(
						esc_html__( 'Top', 'xstore' )         => 'start',
						esc_html__( 'Bottom', 'xstore' )      => 'end',
						esc_html__( 'Middle', 'xstore' )      => 'center',
						esc_html__( 'Full height', 'xstore' ) => 'stretch',
					),
				),
				// Cart options
				array(
					'type'       => 'xstore_button_set',
					'heading'    => esc_html__( 'Type', 'xstore' ),
					'param_name' => 'cart_type',
					'value'      => array(
						esc_html__( 'Icon', 'xstore' )        => 'icon',
						esc_html__( 'Text', 'xstore' )        => 'text',
						esc_html__( 'Icon + text', 'xstore' ) => 'icon-text',
						esc_html__( 'Button', 'xstore' )      => 'button',
					),
					'group'      => 'Cart',
				),
				array(
					'type'       => 'textfield',
					'heading'    => esc_html__( 'Font size', 'xstore' ),
					'param_name' => 'a_size',
					'group'      => 'Cart'
				),
				array(
					'type'       => 'xstore_button_set',
					'heading'    => esc_html__( 'Text transform', 'xstore' ),
					'param_name' => 'a_transform',
					'value'      => array(
						__( 'None', 'xstore' )       => '',
						__( 'Uppercase', 'xstore' )  => 'uppercase',
						__( 'Lowercase', 'xstore' )  => 'lowercase',
						__( 'Capitalize', 'xstore' ) => 'capitalize'
					),
					'group'      => 'Cart',
					'dependency' => array( 'element' => 'cart_type', 'value_not_equal_to' => 'icon' ),
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => esc_html__( 'Button background color', 'xstore' ),
					'param_name'       => 'a_bg',
					'group'            => 'Cart',
					'dependency'       => array( 'element' => 'cart_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => esc_html__( 'Button background color (hover)', 'xstore' ),
					'param_name'       => 'a_hover_bg',
					'group'            => 'Cart',
					'dependency'       => array( 'element' => 'cart_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				array(
					'type'             => 'textfield',
					'heading'          => 'Border radius',
					'param_name'       => 'a_radius',
					'group'            => 'Cart',
					'dependency'       => array( 'element' => 'cart_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				array(
					'type'             => 'textfield',
					'heading'          => esc_html__( 'Margins (top right bottom left)', 'xstore' ),
					'param_name'       => 'a_margin',
					'group'            => 'Cart',
					'description'      => esc_html__( 'Use this field to add element margin. For example 10px 20px 30px 40px', 'xstore' ),
					'dependency'       => array( 'element' => 'cart_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				
				// Wishlist options
				array(
					'type'       => 'xstore_button_set',
					'heading'    => esc_html__( 'Type', 'xstore' ),
					'param_name' => 'w_type',
					'value'      => array(
						__( 'Icon', 'xstore' )        => 'icon',
						__( 'Text', 'xstore' )        => 'text',
						__( 'Icon + text', 'xstore' ) => 'icon-text',
						__( 'Button', 'xstore' )      => 'button',
					),
					'group'      => 'Wishlist'
				),
				array(
					'type'       => 'textfield',
					'heading'    => esc_html__( 'Font size', 'xstore' ),
					'param_name' => 'w_size',
					'group'      => 'Wishlist'
				),
				array(
					'type'       => 'xstore_button_set',
					'heading'    => esc_html__( 'Text transform', 'xstore' ),
					'param_name' => 'w_transform',
					'value'      => array(
						__( 'None', 'xstore' )       => '',
						__( 'Uppercase', 'xstore' )  => 'uppercase',
						__( 'Lowercase', 'xstore' )  => 'lowercase',
						__( 'Capitalize', 'xstore' ) => 'capitalize'
					),
					'group'      => 'Wishlist',
					'dependency' => array( 'element' => 'w_type', 'value_not_equal_to' => 'icon' )
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => esc_html__( 'Button background color', 'xstore' ),
					'param_name'       => 'w_bg',
					'group'            => 'Wishlist',
					'dependency'       => array( 'element' => 'w_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => esc_html__( 'Button background color (hover)', 'xstore' ),
					'param_name'       => 'w_hover_bg',
					'group'            => 'Wishlist',
					'dependency'       => array( 'element' => 'w_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				array(
					'type'             => 'textfield',
					'heading'          => 'Border radius',
					'param_name'       => 'w_radius',
					'group'            => 'Wishlist',
					'dependency'       => array( 'element' => 'w_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				array(
					'type'             => 'textfield',
					'heading'          => esc_html__( 'Margins (top right bottom left)', 'xstore' ),
					'param_name'       => 'w_margin',
					'group'            => 'Wishlist',
					'description'      => esc_html__( 'Use this field to add element margin. For example 10px 20px 30px 40px', 'xstore' ),
					'dependency'       => array( 'element' => 'w_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				
				// Quick view
				array(
					'type'       => 'xstore_button_set',
					'heading'    => esc_html__( 'Type', 'xstore' ),
					'param_name' => 'quick_type',
					'value'      => array(
						__( 'Icon', 'xstore' )        => 'icon',
						__( 'Text', 'xstore' )        => 'text',
						__( 'Icon + text', 'xstore' ) => 'icon-text',
						__( 'Button', 'xstore' )      => 'button',
					),
					'group'      => 'Quick view'
				),
				array(
					'type'       => 'textfield',
					'heading'    => esc_html__( 'Font size', 'xstore' ),
					'param_name' => 'q_size',
					'group'      => 'Quick view'
				),
				array(
					'type'       => 'xstore_button_set',
					'heading'    => esc_html__( 'Text transform', 'xstore' ),
					'param_name' => 'q_transform',
					'value'      => array(
						__( 'None', 'xstore' )       => '',
						__( 'Uppercase', 'xstore' )  => 'uppercase',
						__( 'Lowercase', 'xstore' )  => 'lowercase',
						__( 'Capitalize', 'xstore' ) => 'capitalize'
					),
					'group'      => 'Quick view',
					'dependency' => array( 'element' => 'quick_type', 'value_not_equal_to' => 'icon' ),
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => esc_html__( 'Button background color', 'xstore' ),
					'param_name'       => 'q_bg',
					'group'            => 'Quick view',
					'dependency'       => array( 'element' => 'quick_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => esc_html__( 'Button background color (hover)', 'xstore' ),
					'param_name'       => 'q_hover_bg',
					'group'            => 'Quick view',
					'dependency'       => array( 'element' => 'quick_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				array(
					'type'             => 'textfield',
					'heading'          => 'Border radius',
					'param_name'       => 'q_radius',
					'group'            => 'Quick view',
					'dependency'       => array( 'element' => 'quick_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				array(
					'type'             => 'textfield',
					'heading'          => esc_html__( 'Margins (top right bottom left)', 'xstore' ),
					'param_name'       => 'q_margin',
					'group'            => 'Quick view',
					'description'      => esc_html__( 'Use this field to add element margin. For example 10px 20px 30px 40px', 'xstore' ),
					'dependency'       => array( 'element' => 'quick_type', 'value' => array( 'button', 'icon' ) ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
			),
			
			// Compare
			$compare_arr,
			array(
				array(
					'type'             => 'colorpicker',
					'heading'          => esc_html__( 'Text/icons color', 'xstore' ),
					'param_name'       => 'color',
					'edit_field_class' => 'vc_col-sm-3 vc_column',
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => esc_html__( 'Background color', 'xstore' ),
					'param_name'       => 'bg',
					'edit_field_class' => 'vc_col-sm-3 vc_column',
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => esc_html__( 'Text/icons hover color', 'xstore' ),
					'param_name'       => 'hover_color',
					'edit_field_class' => 'vc_col-sm-3 vc_column',
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => esc_html__( 'Background hover color', 'xstore' ),
					'param_name'       => 'hover_bg',
					'edit_field_class' => 'vc_col-sm-3 vc_column',
				),
				array(
					'type'             => 'textfield',
					'heading'          => esc_html__( 'Border radius', 'xstore' ),
					'param_name'       => 'radius',
					'edit_field_class' => 'vc_col-sm-4 vc_column',
				),
				array(
					'type'             => 'textfield',
					'heading'          => esc_html__( 'Paddings (top right bottom left)', 'xstore' ),
					'param_name'       => 'paddings',
					'description'      => esc_html__( 'Use this field to add element paddings. For example 10px 20px 30px 40px', 'xstore' ),
					'edit_field_class' => 'vc_col-sm-4 vc_column',
				),
				array(
					'type'       => 'textfield',
					'heading'    => esc_html__( 'Extra class', 'xstore' ),
					'param_name' => 'el_class',
				),
				array(
					'type'       => 'css_editor',
					'heading'    => esc_html__( 'CSS box', 'xstore' ),
					'param_name' => 'css',
					'group'      => __( 'Design Options', 'xstore' ),
				)
			)
		),
	);
	
	return $shortcodes;
}

if ( ! function_exists( 'etheme_product_stock_render' ) ) {
	function etheme_product_stock_render( $atts ) {
		
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		
		global $post, $woocommerce_loop;
		$id      = $post->ID;
		$product = wc_get_product( $id );
		if ( !is_object($product)) return;
		
		if ( ! $product->is_purchasable() ) {
			return;
		}
		
		$atts = shortcode_atts(
			array(
				'product_stock_type' => 'default',
				'align'              => 'left',
				'color'              => '#000',
				'css'                => '',
				'el_class'           => '',
			), $atts );
		
		$custom_template = etheme_get_custom_product_template();
		if ( ! empty( $woocommerce_loop['custom_template'] ) ) {
			$custom_template = $woocommerce_loop['custom_template'];
		}
		
		$out = $out_css = '';
		
		$options = array();
		
		if ( ! empty( $atts['css'] ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$atts['el_class'] .= ' ' . vc_shortcode_custom_css_class( $atts['css'] );
		}
		
		// add style one time on first load
		
		$options['inline_css'] = apply_filters( 'etheme_output_shortcodes_inline_css', isset($_GET['vc_editable']) );
		
		if ( ( ! $options['inline_css'] && ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'stock_css_added' ] ) ) || $options['inline_css'] ) {
			
			$options['selectors'] = array(
				'stock' => '.products-template-' . $custom_template . ' .content-product .product-stock-wrapper .stock'
			);
			
			$options['css'] = array(
				'stock' => array(
					'display: block',
					'position: static',
					'transform: none',
					'padding: 0',
					'margin: 0',
					'font-size: 1rem',
					'background: transparent'
				),
			);
			
			if ( ! empty( $atts['align'] ) ) {
				$options['css']['stock'][] = 'text-align: ' . $atts['align'];
			}
			
			if ( ! empty( $atts['color'] ) ) {
				$options['css']['stock'][] = 'color: ' . $atts['color'];
			}
			
			$options['output_css'] = array();
			
			if ( count( $options['css']['stock'] ) ) {
				$options['output_css'][] = $options['selectors']['stock'] . ' {' . implode( ';', $options['css']['stock'] ) . '}';
			}
		}
		
		if ( ! $options['inline_css'] ) {
			if ( ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'stock_css_added' ] ) ) {
				
				wp_add_inline_style( 'xstore-inline-css', implode( ' ', $options['output_css'] ) );
				
				$woocommerce_loop[ 'custom_template_' . $custom_template . 'stock_css_added' ] = 'true';
			}
		} else {
			$out_css .= '<style>' . implode( ' ', $options['output_css'] ) . '</style>';
		}
		
		ob_start();
		
		if ( $atts['product_stock_type'] == 'default' ) {
			echo wc_get_stock_html( $product ); // WPCS: XSS ok.
		} else {
			if ( $product->is_in_stock() ) {
				echo et_product_stock_line( $product );
			} else {
				echo wc_get_stock_html( $product ); // WPCS: XSS ok.
			}
		}
		
		$out = ob_get_clean();
		
		if ( ! empty( $out ) ) {
			$out = '<div class="product-stock-wrapper ' . $atts['el_class'] . '">' . $out . '</div>';
		}
		
		return $out_css . $out;
	}
}

/* **************************** */
/* === Product title render === */
/* **************************** */

if ( ! function_exists( 'etheme_product_name_render' ) ) {
	function etheme_product_name_render( $atts ) {
		
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		
		if ( ! class_exists( 'ETC\App\Controllers\Shortcodes' ) ) {
			return;
		}
		
		global $post, $woocommerce_loop;
		$id      = $post->ID;
		$product = wc_get_product($id);
		if ( !is_object($product)) return;
		
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}
		
		extract( shortcode_atts(
				array(
					'align'              => '',
					'link'               => 'product_link',
					'url'                => '',
					'symbols'            => '...',
					'title_google_fonts' => 'font_family:Abril%20Fatface%3Aregular|font_style:400%20regular%3A400%3Anormal',
					'cutting'            => '',
					'count'              => '',
					'spacing'            => '',
					'size'               => '',
					'line_height'        => '',
					'color'              => '',
					'el_class'           => '',
					'css'                => ''
				), $atts )
		);
		
		$full_name = $post_name = unicode_chars( $product->get_title() );
		
		// get the link
		$link     = ( $link != '' ) ? get_permalink() : '';
		$url      = vc_build_link( $url );
		$a_target = '_self';
		$a_title  = $class = $style = '';
		$el_class .= ( ! empty( $align ) ) ? ' text-' . $align : '';
		if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$el_class .= ' ' . vc_shortcode_custom_css_class( $css );
		}
		
		if ( isset( $url['url'] ) && strlen( $url['url'] ) > 0 ) {
			$link     = $url['url'];
			$a_title  = $url['title'];
			$a_target = strlen( $url['target'] ) > 0 ? $url['target'] : '_self';
		}
		
		if ( strlen( $post_name ) > 0 && $cutting != 'none' ) {
			if ( $cutting == 'letters' ) {
				$split = preg_split( '/(?<!^)(?!$)/u', $post_name );
			} else {
				$split = explode( ' ', $post_name );
			}
			
			$post_name = ( $count != '' && $count > 0 && ( count( $split ) >= $count ) ) ? '' : $post_name;
			if ( $post_name == '' ) {
				if ( $cutting == 'letters' ) {
					for ( $i = 0; $i < $count; $i ++ ) {
						$post_name .= $split[ $i ];
					}
				} else {
					for ( $i = 0; $i < $count; $i ++ ) {
						$post_name .= ' ' . $split[ $i ];
					}
				}
			}
			if ( strlen( $post_name ) < strlen( $full_name ) ) {
				$post_name .= $symbols;
			}
		}
		
		$custom_template = etheme_get_custom_product_template();
		if ( ! empty( $woocommerce_loop['custom_template'] ) ) {
			$custom_template = $woocommerce_loop['custom_template'];
		}
		
		$out = '';
		
		$options = array();
		
		$options['inline_css'] = apply_filters( 'etheme_output_shortcodes_inline_css', isset($_GET['vc_editable']) );
		
		if ( ( ! $options['inline_css'] && ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'title_css_added' ] ) ) || $options['inline_css'] ) {
			
			$options['selectors'] = array(
				'title' => '.products-template-' . $custom_template . ' .content-product .product-title'
			);
			
			$options['css'] = array(
				'title' => array()
			);
			
			if ( ! empty( $spacing ) ) {
				$options['css']['title'][] = 'letter-spacing: ' . $spacing;
			}
			
			if ( ! empty( $color ) ) {
				$options['css']['title'][] = 'color: ' . $color;
			}
			
			if ( ! empty( $size ) ) {
				$options['css']['title'][] = 'font-size: ' . $size;
			}
			
			$options['output_css'] = array();
			
			if ( count( $options['css']['title'] ) ) {
				$options['output_css'][] = $options['selectors']['title'] . ' {' . implode( ';', $options['css']['title'] ) . '}';
			}
		}
		
		// add style one time on first load
		
		if ( ! $options['inline_css'] ) {
			if ( ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'title_css_added' ] ) ) {
				
				wp_add_inline_style( 'xstore-inline-css', implode( ' ', $options['output_css'] ) );
				
				$woocommerce_loop[ 'custom_template_' . $custom_template . 'title_css_added' ] = 'true';
			}
		} else {
			
			$out .= '<style>' . implode( ' ', $options['output_css'] ) . '</style>';
		}
		
		unset( $options );
		
		$atts['title_link'] = '';
		$atts['title']      = $post_name;
		
		$out .= '<div class="' . $el_class . ' text-' . $align . '">';
		
		$out .= ( $link != '' ) ? '<a href="' . $link . '" title="' . $a_title . '" target="' . $a_target . '">' : '';
		
		$out .= ETC\App\Controllers\Shortcodes::getHeading( 'title', $atts, 'product-title' );
		
		$out .= ( $link != '' ) ? '</a>' : '';
		
		$out .= '</div>';
		
		return $out; // usage of template variable post_data with argument "ID"
	}
}

/* **************************** */
/* === Product image render === */
/* **************************** */

if ( ! function_exists( 'etheme_product_image_render' ) ) {
	function etheme_product_image_render( $atts ) {
		global $post;
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		
		extract( shortcode_atts(
				array(
					'link'         => 'product_link',
					'url'          => '',
					'align'        => '',
					'style'        => '',
					'hover'        => '',
					'size'         => 'shop_catalog',
					'size_custom' => '',
					'stock_status' => 'no',
					'border_color' => '',
					'el_class'     => '',
					'css'          => ''
				), $atts )
		);
		
		$id       = $post->ID;
		$product = wc_get_product($id);
		if ( !is_object($product)) return;
		$el_class .= ' ' . $style;
		$el_class .= ' hover-effect-' . $hover;
		// get the link
		$link     = ( $link != '' ) ? get_permalink() : '';
		$url      = vc_build_link( $url );
		$a_target = '_self';
		$a_title  = '';
		if ( isset( $url['url'] ) && strlen( $url['url'] ) > 0 ) {
			$link     = $url['url'];
			$a_title  = $url['title'];
			$a_target = strlen( $url['target'] ) > 0 ? $url['target'] : '_self';
		}
		
		// get the css
		if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$el_class .= ' ' . vc_shortcode_custom_css_class( $css );
		}
		
		// vc image style
		$el_class .= ( $border_color != '' ) ? ' vc_box_border_' . $border_color : ' vc_box_border_grey';
		
		// product image under
		$post_thumbnail_id = get_post_thumbnail_id( $id );
		$full_size_image   = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
		$placeholder       = has_post_thumbnail() ? 'with-images' : 'without-images';
		$attributes        = array(
			'title'                   => get_post_field( 'post_title', $post_thumbnail_id ),
			'data-caption'            => get_post_field( 'post_excerpt', $post_thumbnail_id ),
			'data-src'                => $full_size_image[0],
			'data-large_image'        => $full_size_image[0],
			'data-large_image_width'  => $full_size_image[1],
			'data-large_image_height' => $full_size_image[2],
		);
		
		$img = '';
		
		if ( $size == 'custom' ) {
			if ( ! in_array( $size_custom, array( 'thumbnail', 'medium', 'large', 'full' ) ) ) {
				$size = explode( 'x', $size_custom );
			} else {
				$size = $size_custom;
			}
			$img = etheme_get_image($post_thumbnail_id, $size);
		}
		else {
			$img = ( get_the_post_thumbnail( $id, $size ) != '' ) ? get_the_post_thumbnail( $id, $size, $attributes ) : wc_placeholder_img();
		}
		
		// echo product image
		ob_start();
		
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		
		?>
        <div class="wpb_single_image text-<?php echo esc_attr( $align );
		echo ( 'slider' == $hover ) ? ' arrows-hovered' : ''; ?>">
            <div class="product-image-wrapper vc_single_image-wrapper <?php echo esc_attr( $el_class ); ?>">
				<?php
				
				if ( $hover == 'slider' ) {
					echo '<div class="images-slider-wrapper">';
				}
				
				do_action( 'woocommerce_before_shop_loop_item' );
				if ( $stock_status != 'yes' ) {
					etheme_product_availability();
				}
				?>
				
				<?php if ( $link != '' ) { ?> <a class="product-content-image" href="<?php echo esc_url( $link ); ?>"
                                                 data-images="<?php echo etheme_get_image_list( 'shop_catalog' ); ?>"> <?php } ?>
					<?php if ( $hover == 'swap' ) {
						echo etheme_get_second_image( $size );
					} ?>
					<?php echo ( '' != $img ) ? $img : wc_placeholder_img(); ?>
					<?php if ( $link != '' ) { ?> </a> <?php } ?>
				
				<?php if ( $hover == 'slider' ) {
					echo '</div>';
				} ?>
            </div>
        </div>
		
		<?php add_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 ); ?>
		
		
		<?php return ob_get_clean(); // usage of template variable post_data with argument "ID"
	}
}

/* **************************** */
/* === Product excerpt render === */
/* **************************** */

if ( ! function_exists( 'etheme_product_excerpt_render' ) ) {
	function etheme_product_excerpt_render( $atts ) {
		global $post, $woocommerce_loop;
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		
		if ( ! class_exists( 'ETC\App\Controllers\Shortcodes' ) ) {
			return;
		}
		
		$id      = $post->ID;
		$product = wc_get_product( $id );
		if ( !is_object($product)) return;
		
		$atts = shortcode_atts(
			array(
				'cutting'                => '',
				'count'                  => '',
				'align'                  => '',
				'title_google_fonts'     => 'font_family:Abril%20Fatface%3Aregular|font_style:400%20regular%3A400%3Anormal',
				'use_custom_fonts_title' => '',
				'title_font_container'   => '',
				'title_use_theme_fonts'  => '',
				'symbols'                => '...',
				'spacing'                => '',
				'size'                   => '',
				'color'                  => '',
				'css'                    => '',
				'el_class'               => ''
			), $atts );
		
		$atts['el_class'] .= ( ! empty( $atts['align'] ) ) ? ' text-' . $atts['align'] : '';
		if ( ! empty( $atts['css'] ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$atts['el_class'] .= ' ' . vc_shortcode_custom_css_class( $atts['css'] );
		}
		
		$custom_template = etheme_get_custom_product_template();
		if ( ! empty( $woocommerce_loop['custom_template'] ) ) {
			$custom_template = $woocommerce_loop['custom_template'];
		}
		
		$out = '';
		
		$options = array();
		
		$options['excerpt'] = $options['short_descr'] = unicode_chars( $product->get_short_description() );
		if ( strlen( $options['short_descr'] ) > 0 && $atts['cutting'] != 'none' ) {
			if ( $atts['cutting'] == 'letters' ) {
				$split = preg_split( '/(?<!^)(?!$)/u', $options['short_descr'] );
			} else {
				$split = explode( ' ', $options['short_descr'] );
			}
			
			$options['excerpt'] = ( $atts['count'] != '' && $atts['count'] > 0 && ( count( $split ) >= $atts['count'] ) ) ? '' : $options['short_descr'];
			if ( $options['excerpt'] == '' ) {
				if ( $atts['cutting'] == 'letters' ) {
					for ( $i = 0; $i < $atts['count']; $i ++ ) {
						$options['excerpt'] .= $split[ $i ];
					}
				} else {
					for ( $i = 0; $i < $atts['count']; $i ++ ) {
						$options['excerpt'] .= ' ' . $split[ $i ];
					}
				}
			}
			if ( strlen( $options['excerpt'] ) < strlen( $options['short_descr'] ) ) {
				$options['excerpt'] .= $atts['symbols'];
			}
		}
		
		// add style one time on first load
		
		$options['inline_css'] = apply_filters( 'etheme_output_shortcodes_inline_css', isset($_GET['vc_editable']) );
		
		if ( ( ! $options['inline_css'] && ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'excerpt_css_added' ] ) ) || $options['inline_css'] ) {
			
			$options['selectors'] = array(
				'excerpt' => '.products-template-' . $custom_template . ' .content-product .excerpt'
			);
			
			$options['css'] = array(
				'excerpt' => array()
			);
			
			if ( ! empty( $atts['spacing'] ) ) {
				$options['css']['excerpt'][] = 'letter-spacing: ' . $atts['spacing'];
			}
			
			if ( ! empty( $atts['color'] ) ) {
				$options['css']['excerpt'][] = 'color: ' . $atts['color'];
			}
			
			if ( ! empty( $atts['size'] ) ) {
				$options['css']['excerpt'][] = 'font-size: ' . $atts['size'];
			}
			
			$options['output_css'] = array();
			
			if ( count( $options['css']['excerpt'] ) ) {
				$options['output_css'][] = $options['selectors']['excerpt'] . ' {' . implode( ';', $options['css']['excerpt'] ) . '}';
			}
			
		}
		
		if ( ! $options['inline_css'] ) {
			if ( ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'excerpt_css_added' ] ) ) {
				
				wp_add_inline_style( 'xstore-inline-css', implode( ' ', $options['output_css'] ) );
				
				$woocommerce_loop[ 'custom_template_' . $custom_template . 'excerpt_css_added' ] = 'true';
			}
		} else {
			$out .= '<style>' . implode( ' ', $options['output_css'] ) . '</style>';
		}
		
		$atts['title_link'] = '';
		$atts['title']      = $options['excerpt'];
		
		unset( $options );
		
		$out .= ETC\App\Controllers\Shortcodes::getHeading( 'title', $atts, 'excerpt ' . $atts['el_class'] );
		
		return $out;
	}
}

/* **************************** */
/* === Product rating render === */
/* **************************** */

if ( ! function_exists( 'etheme_product_rating_render' ) ) {
	function etheme_product_rating_render( $atts ) {
		global $post;
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		
		$id      = $post->ID;
		$product = wc_get_product( $id );
		if ( !is_object($product)) return;
		
		extract( shortcode_atts(
				array(
					'default'  => '',
					'css'      => '',
					'el_class' => ''
				), $atts )
		);
		$rating_count = $product->get_rating_count();
		$review_count = $product->get_review_count();
		$average      = $product->get_average_rating();
		$out          = '';
		
		if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$el_class .= ' ' . vc_shortcode_custom_css_class( $css );
		}
		
		if ( $default ) {
			$rating_html = '<div class="woocommerce-product-rating ' . $el_class . '">';
			$rating_html .= '<div class="star-rating" title="' . sprintf( esc_attr__( 'Rated %s out of 5', 'xstore' ), $average ) . '">';
			$rating_html .= '<span style="width:' . ( ( $average / 5 ) * 100 ) . '%"><strong class="rating">' . $average . '</strong> ' . esc_html__( 'out of 5', 'xstore' ) . '</span>';
			$rating_html .= '</div>';
			$rating_html .= '</div>';
			$out         = apply_filters( 'woocommerce_product_get_rating_html', $rating_html, $average );
		} elseif ( $rating_count > 0 ) {
			$out .= '<div class="woocommerce-product-rating ' . $el_class . '">';
			$out .= wc_get_rating_html( $average, $rating_count );
			$out .= '</div>';
		}
		
		return $out;
	}
}

/* **************************** */
/* === Product price render === */
/* **************************** */

if ( ! function_exists( 'etheme_product_price_render' ) ) {
	function etheme_product_price_render( $atts ) {
		global $post, $woocommerce_loop;
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		$id      = $post->ID;
		$product = wc_get_product( $id );
		if ( !is_object($product)) return;
		
		extract( shortcode_atts(
				array(
					'align'      => '',
					'spacing'    => '',
					'color'      => '',
					'color_sale' => '',
					'size'       => '',
					'css'        => '',
					'el_class'   => ''
				), $atts )
		);
		
		$el_class .= ( ! empty( $align ) ) ? ' text-' . $align : '';
		$out      = '';
		
		if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$el_class .= ' ' . vc_shortcode_custom_css_class( $css );
		}
		
		$custom_template = etheme_get_custom_product_template();
		if ( ! empty( $woocommerce_loop['custom_template'] ) ) {
			$custom_template = $woocommerce_loop['custom_template'];
		}
		
		$options = array();
		
		$options['inline_css'] = apply_filters( 'etheme_output_shortcodes_inline_css', isset($_GET['vc_editable']) );
		
		if ( ( ! $options['inline_css'] && ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'price_css_added' ] ) ) || $options['inline_css'] ) {
			
			$options['selectors'] = array();
			
			$options['selectors']['custom_template'] = '.products-template-' . $custom_template;
			$options['selectors']['price']           = $options['selectors']['custom_template'] . ' .content-product .price';
			$options['selectors']['sale_price']      = $options['selectors']['price'] . ' ins .amount';
			
			$options['css'] = array(
				'price'      => array(),
				'sale_price' => array()
			);
			
			if ( ! empty( $spacing ) ) {
				$options['css']['price'][] = 'letter-spacing: ' . $spacing;
			}
			
			if ( ! empty( $color ) ) {
				$options['css']['price'][] = 'color: ' . $color;
			}
			
			if ( ! empty( $size ) ) {
				$options['css']['price'][] = 'font-size: ' . $size;
			}
			
			// sale price
			if ( ! empty( $color_sale ) ) {
				$options['css']['sale_price'][] = 'color: ' . $color_sale;
			}
			
			$options['output_css'] = array();
			
			if ( count( $options['css']['price'] ) ) {
				$options['output_css'][] = $options['selectors']['price'] . ' {' . implode( ';', $options['css']['price'] ) . '}';
			}
			
			if ( count( $options['css']['sale_price'] ) ) {
				$options['output_css'][] = $options['selectors']['sale_price'] . ' {' . implode( ';', $options['css']['sale_price'] ) . '}';
			}
			
		}
		
		// add style one time on first load
		
		if ( ! $options['inline_css'] ) {
			if ( ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'price_css_added' ] ) ) {
				
				wp_add_inline_style( 'xstore-inline-css', implode( ' ', $options['output_css'] ) );
				
				$woocommerce_loop[ 'custom_template_' . $custom_template . 'price_css_added' ] = 'true';
			}
		} else {
			$out .= '<style>' . implode( ' ', $options['output_css'] ) . '</style>';
		}
		
		
		$out .= '<div class="price ' . $el_class . '">';
		
		$out .= $product->get_price_html();
		
		$out .= '</div>';
		
		unset( $options );
		
		return $out;
	}
}

/* **************************** */
/* === Product sku render === */
/* **************************** */

if ( ! function_exists( 'etheme_product_sku_render' ) ) {
	function etheme_product_sku_render( $atts ) {
		global $post, $woocommerce_loop;
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		$id      = $post->ID;
		$product = wc_get_product( $id );
		if ( !is_object($product)) return;
		
		extract( shortcode_atts(
				array(
					'align'     => '',
					'transform' => '',
					'color'     => '',
					'size'      => '',
					'css'       => '',
					'el_class'  => ''
				), $atts )
		);
		
		$el_class .= ( ! empty( $align ) ) ? ' text-' . $align : '';
		$el_class .= ( ! empty( $transform ) ) ? ' ' . $transform : '';
		
		$custom_template = etheme_get_custom_product_template();
		if ( ! empty( $woocommerce_loop['custom_template'] ) ) {
			$custom_template = $woocommerce_loop['custom_template'];
		}
		
		$options = array();
		
		$options['sku'] = $product->get_sku();
		$options['out'] = '';
		
		if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$el_class = ' ' . vc_shortcode_custom_css_class( $css );
		}
		
		$options['inline_css'] = apply_filters( 'etheme_output_shortcodes_inline_css', isset($_GET['vc_editable']) );
		
		if ( ( ! $options['inline_css'] && ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'sku_css_added' ] ) ) || $options['inline_css'] ) {
			
			$options['selectors'] = array(
				'sku' => '.products-template-' . $custom_template . ' .content-product .sku'
			);
			
			$options['css'] = array(
				'sku' => array()
			);
			
			if ( ! empty( $color ) ) {
				$options['css']['sku'][] = 'color: ' . $color;
			}
			
			if ( ! empty( $size ) ) {
				$options['css']['sku'][] = 'font-size: ' . $size;
			}
			
			$options['output_css'] = array();
			
			if ( count( $options['css']['sku'] ) ) {
				$options['output_css'][] = $options['selectors']['sku'] . ' {' . implode( ';', $options['css']['sku'] ) . '}';
			}
			
		}
		
		// add style one time on first load
		
		if ( ! $options['inline_css'] ) {
			if ( ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'sku_css_added' ] ) ) {
				
				wp_add_inline_style( 'xstore-inline-css', implode( ' ', $options['output_css'] ) );
				
				$woocommerce_loop[ 'custom_template_' . $custom_template . 'sku_css_added' ] = 'true';
			}
		} else {
			$options['out'] .= '<style>' . implode( ' ', $options['output_css'] ) . '</style>';
		}
		
		if ( strlen( $options['sku'] ) > 0 ) {
			
			$options['out'] .= '<div class="sku ' . $el_class . '">';
			
			$options['out'] .= $options['sku'];
			
			$options['out'] .= '</div>';
		}
		
		$out = $options['out'];
		
		unset( $options );
		
		return $out;
	}
}


/* **************************** */
/* === Product brands render === */
/* **************************** */

if ( ! function_exists( 'etheme_product_brands_render' ) ) {
	function etheme_product_brands_render( $atts ) {
		global $post, $woocommerce_loop;
		
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		
		// The check for option is missing, so if brands are disabled, category pages break
		if ( ! etheme_get_option( 'enable_brands', true ) ) {
			return;
		}
		
		if ( !is_object(wc_get_product($post->ID))) return;
		
		extract( shortcode_atts(
				array(
					'align'     => '',
					'transform' => '',
					'spacing'   => '',
					'size'      => '',
					'img'       => '',
					'css'       => '',
					'el_class'  => ''
				), $atts )
		);
		
		$el_class .= ( ! empty( $align ) ) ? ' text-' . $align : '';
		$el_class .= ( ! empty( $transform ) ) ? ' ' . $transform : '';
		
		if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$el_class = ' ' . vc_shortcode_custom_css_class( $css );
		}
		
		$custom_template = etheme_get_custom_product_template();
		if ( ! empty( $woocommerce_loop['custom_template'] ) ) {
			$custom_template = $woocommerce_loop['custom_template'];
		}
		
		$options = array();
		
		$options['inline_css'] = apply_filters( 'etheme_output_shortcodes_inline_css', isset($_GET['vc_editable']) );
		
		if ( ( ! $options['inline_css'] && ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'brands_css_added' ] ) ) || $options['inline_css'] ) {
			
			$options['selectors'] = array(
				'brands' => '.products-template-' . $custom_template . ' .content-product .product_brand'
			);
			
			$options['css'] = array(
				'brands' => array()
			);
			
			if ( ! empty( $spacing ) ) {
				$options['css']['brands'][] = 'letter-spacing: ' . $spacing;
			}
			
			if ( ! empty( $color ) ) {
				$options['css']['brands'][] = 'color: ' . $color;
			}
			
			if ( ! empty( $size ) ) {
				$options['css']['brands'][] = 'font-size: ' . $size;
			}
			
			$options['output_css'] = array();
			
			if ( count( $options['css']['brands'] ) ) {
				$options['output_css'][] = $options['selectors']['brands'] . ' {' . implode( ';', $options['css']['brands'] ) . '}';
			}
			
		}
		
		$terms = wp_get_post_terms( $post->ID, 'brand' );
		if ( count( $terms ) < 1 ) {
			return;
		}
		
		ob_start();
		
		// add style one time on first load
		
		if ( ! $options['inline_css'] ) {
			if ( ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'brands_css_added' ] ) ) {
				
				wp_add_inline_style( 'xstore-inline-css', implode( ' ', $options['output_css'] ) );
				
				$woocommerce_loop[ 'custom_template_' . $custom_template . 'brands_css_added' ] = 'true';
			}
		} else {
			echo '<style>' . implode( ' ', $options['output_css'] ) . '</style>';
		}
		
		$_i = 0;
		?>
        <div class="product_brand <?php echo esc_attr( $el_class ); ?>">
			<?php foreach ( $terms as $brand ) : $_i ++; ?>
				<?php
				$thumbnail_id = absint( get_term_meta( $brand->term_id, 'thumbnail_id', true ) ); ?>
                <a href="<?php echo get_term_link( $brand ); ?>">
					<?php if ( $thumbnail_id && $img ) {
						echo wp_get_attachment_image( $thumbnail_id, 'full' );
					} else { ?>
						<?php echo esc_html( $brand->name ); ?>
					<?php } ?>
                </a>
				<?php if ( count( $terms ) > $_i ) {
					echo ", ";
				} ?>
			<?php endforeach; ?>
        </div>
		<?php
		
		unset( $options );
		
		return ob_get_clean();
	}
}

/* **************************** */
/* === Product categories render === */
/* **************************** */

if ( ! function_exists( 'etheme_product_categories_render' ) ) {
	function etheme_product_categories_render( $atts ) {
		global $post;
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		
		if ( ! class_exists( 'ETC\App\Controllers\Shortcodes' ) ) {
			return;
		}
		
		$id      = $post->ID;
		$product = wc_get_product( $id );
		if ( !is_object($product)) return;
		
		$atts    = shortcode_atts(
			array(
				'cutting'                => '',
				'count'                  => '',
				'tag'                    => 'p',
				'align'                  => '',
				'title_link'             => '',
				'title'                  => '',
				'title_google_fonts'     => 'font_family:Abril%20Fatface%3Aregular|font_style:400%20regular%3A400%3Anormal',
				'use_custom_fonts_title' => '',
				'title_font_container'   => '',
				'title_use_theme_fonts'  => '',
				'symbols'                => '...',
				'css'                    => '',
				'el_class'               => ''
			), $atts );
		
		$atts['el_class'] .= ' products-page-cats';
		$atts['el_class'] .= ( ! empty( $atts['align'] ) ) ? ' text-' . $atts['align'] : '';
		
		if ( ! empty( $atts['css'] ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$atts['el_class'] .= ' ' . vc_shortcode_custom_css_class( $atts['css'] );
		}
		
		$atts['title_link'] = '';
		$atts['title']      = $cats = wc_get_product_category_list( $id, ', ' );
		
		if ( ! $atts['use_custom_fonts_title'] ) {
			$out = '<div class="' . $atts['el_class'] . '">' . $cats . '</div>';
		} else {
			$out = ETC\App\Controllers\Shortcodes::getHeading( 'title', $atts, $atts['el_class'] );
		}
		
		return $out; // usage of template variable post_data with argument "ID"
	}
}

/* **************************** */
/* === Product buttons render === */
/* **************************** */

if ( ! function_exists( 'etheme_product_buttons_render' ) ) {
	function etheme_product_buttons_render( $atts ) {
		global $post, $woocommerce_loop;
		
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		
		$id      = $post->ID;
		$product = wc_get_product( $id );
		if ( !is_object($product)) return;
		
		extract( shortcode_atts(
				array(
					// Buttons types
					'cart_type'    => 'icon',
					'w_type'       => 'icon',
					'compare_type' => 'icon',
					'quick_type'   => 'icon',
					// Sizes
					'a_size'       => '',
					'w_size'       => '',
					'q_size'       => '',
					'c_size'       => '',
					// Transforms
					'a_transform'  => '',
					'w_transform'  => '',
					'q_transform'  => '',
					'c_transform'  => '',
					// Background colors
					'a_bg'         => '',
					'w_bg'         => '',
					'q_bg'         => '',
					'c_bg'         => '',
					
					// Background colors (hover)
					'a_hover_bg'   => '',
					'w_hover_bg'   => '',
					'q_hover_bg'   => '',
					'c_hover_bg'   => '',
					
					// Border radius
					'a_radius'     => '',
					'w_radius'     => '',
					'q_radius'     => '',
					'c_radius'     => '',
					
					// Margins
					'a_margin'     => '',
					'w_margin'     => '',
					'q_margin'     => '',
					'c_margin'     => '',
					
					// Common options
					'align'        => 'start',
					'v_align'      => 'start',
					'type'         => '',
					'color'        => '',
					'bg'           => '',
					'hover_color'  => '',
					'hover_bg'     => '',
					
					// Paddings and radius
					'radius'       => '',
					'paddings'     => '',
					
					'el_class' => '',
					'css'      => '',
					'sorting'  => '',
				), $atts )
		);
		$out = $style = $footer_class = '';
		
		$sorting = ( ! empty( $sorting ) ) ? explode( ',', $sorting ) : array();
		
		$custom_template = etheme_get_custom_product_template();
		if ( ! empty( $woocommerce_loop['custom_template'] ) ) {
			$custom_template = $woocommerce_loop['custom_template'];
		}
		
		$footer_class .= ( ! empty( $align ) ) ? ' justify-content-' . $align : '';
		$footer_class .= ( ! empty( $v_align ) ) ? ' align-items-' . $v_align : '';
		$footer_class .= ' ' . $type;
		
		if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$el_class .= ' ' . vc_shortcode_custom_css_class( $css );
		}
		
		$sorting = array_flip( $sorting );
		
		$options = array();
		
		$options['inline_css'] = apply_filters( 'etheme_output_shortcodes_inline_css', isset($_GET['vc_editable']) );
		
		if ( ( ! $options['inline_css'] && ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'buttons_css_added' ] ) ) || $options['inline_css'] ) {
			
			$options['item']                     = '.products-template-' . $custom_template . ' .footer-product2';
			$options['item_hover']               = $options['item'] . ':hover';
			$options['add_to_cart_button']       = $options['item'] . ' .add_to_cart_button';
			$options['add_to_cart_button']       .= ', ' . $options['item'] . ' .product_type_external';
			$options['add_to_cart_button']       .= ', ' . $options['item'] . ' .product_type_variable';
			$options['add_to_cart_button']       .= ', ' . $options['item'] . ' .product_type_simple';
			$options['add_to_cart_button_hover'] = $options['item'] . ' .add_to_cart_button:hover';
			$options['add_to_cart_button_hover'] .= ', ' . $options['item'] . ' .product_type_external:hover';
			$options['add_to_cart_button_hover'] .= ', ' . $options['item'] . ' .product_type_variable:hover';
			$options['add_to_cart_button_hover'] .= ', ' . $options['item'] . ' .product_type_simple:hover';
			
			$options['wishlist']       = $options['item'] . ' .et-wishlist-holder';
			$options['wishlist_hover'] = $options['wishlist'] . ':hover';
			
			$options['quick_view']       = $options['item'] . ' .show-quickly';
			$options['quick_view_hover'] = $options['quick_view'] . ':hover';
			
			$options['compare']       = $options['item'] . ' .compare';
			$options['compare_hover'] = $options['compare'] . ':hover';
			
			$options['selectors_color']       = $options['item'] . '> *,' . $options['item'] . ' .button,' . $options['item'] . ' a';
			$options['selectors_color_hover'] = $options['item'] . '> *:hover,' . $options['item'] . '> *:hover *, ' . $options['item'] . ' .button:hover, ' . $options['item'] . ' .yith-wcwl-wishlistexistsbrowse.show a:before';
			
			$options['selectors'] = array(
				'item'                     => array(),
				'item_hover'               => array(),
				'add_to_cart_button'       => array(),
				'add_to_cart_button_hover' => array(),
				'wishlist'                 => array(),
				'wishlist_hover'           => array(),
				'quick_view'               => array(),
				'quick_view_hover'         => array(),
				'compare'                  => array(),
				'compare_hover'            => array(),
				'selectors_color'          => array(),
				'selectors_color_hover'    => array(),
			);
			
			if ( $color ) {
				$options['selectors']['selectors_color'][] = 'color:' . $color;
			}
			
			if ( $hover_color ) {
				$options['selectors']['selectors_color_hover'][] = 'color:' . $hover_color;
			}
			
			if ( $bg ) {
				$options['selectors']['item'][] = 'background-color:' . $bg;
			}
			
			if ( $hover_bg ) {
				$options['selectors']['item_hover'][] = 'background-color:' . $hover_bg;
			}
			
			if ( $radius ) {
				$options['selectors']['item'][] = 'border-radius:' . $radius;
			}
			
			if ( $paddings ) {
				$options['selectors']['item'][] = 'padding:' . $paddings;
			}
			
			// add to cart button
			if ( $a_transform ) {
				$options['selectors']['add_to_cart_button'][] = 'text-transform: ' . $a_transform;
			}
			
			if ( $a_size ) {
				$options['selectors']['add_to_cart_button'][] = 'font-size: ' . $a_size;
			}
			
			if ( $a_bg ) {
				$options['selectors']['add_to_cart_button'][] = 'background-color:' . $a_bg;
			}
			
			if ( $a_radius ) {
				$options['selectors']['add_to_cart_button'][] = 'border-radius:' . $a_radius;
			}
			
			if ( $a_margin ) {
				$options['selectors']['add_to_cart_button'][] = 'margin:' . $a_margin;
			}
			
			if ( isset( $sorting['cart'] ) ) {
				$options['selectors']['add_to_cart_button'][] = 'order: ' . $sorting['cart'];
			}
			
			// add to cart button on hover
			if ( $a_hover_bg ) {
				$options['selectors']['add_to_cart_button_hover'][] = 'background-color:' . $a_hover_bg;
			}
			
			// wishlist
			if ( $w_transform ) {
				$options['selectors']['wishlist'][] = 'text-transform: ' . $w_transform;
			}
			if ( $w_size ) {
				$options['selectors']['wishlist'][] = 'font-size: ' . $w_size;
			}
			if ( $w_bg ) {
				$options['selectors']['wishlist'][] = 'background-color:' . $w_bg;
			}
			if ( $w_radius ) {
				$options['selectors']['wishlist'][] = 'border-radius:' . $w_radius;
			}
			if ( $w_margin ) {
				$options['selectors']['wishlist'][] = 'margin:' . $w_margin;
			}
			if ( isset( $sorting['wishlist'] ) ) {
				$options['selectors']['wishlist'][] = 'order: ' . $sorting['wishlist'];
			}
			
			// wishlist hover
			if ( $w_hover_bg ) {
				$options['selectors']['wishlist_hover'][] = 'background-color:' . $w_hover_bg;
			}
			
			// quick view
			if ( $q_transform ) {
				$options['selectors']['quick_view'][] = 'text-transform: ' . $q_transform;
			}
			if ( $q_size ) {
				$options['selectors']['quick_view'][] = 'font-size: ' . $q_size;
			}
			if ( $q_bg ) {
				$options['selectors']['quick_view'][] = 'background-color:' . $q_bg;
			}
			if ( $q_radius ) {
				$options['selectors']['quick_view'][] = 'border-radius:' . $q_radius;
			}
			if ( $q_margin ) {
				$options['selectors']['quick_view'][] = 'margin:' . $q_margin;
			}
			if ( isset( $sorting['q_view'] ) ) {
				$options['selectors']['quick_view'][] = 'order: ' . $sorting['q_view'];
			}
			
			// quick view hover
			if ( $q_hover_bg ) {
				$options['selectors']['quick_view_hover'][] = 'background-color:' . $q_hover_bg;
			}
			
			// compare
			if ( $c_transform ) {
				$options['selectors']['compare'][] = 'text-transform: ' . $c_transform;
			}
			if ( $c_size ) {
				$options['selectors']['compare'][] = 'font-size: ' . $c_size;
			}
			if ( $c_bg ) {
				$options['selectors']['compare'][] = 'background-color:' . $c_bg;
			}
			if ( $c_radius ) {
				$options['selectors']['compare'][] = 'border-radius:' . $c_radius;
			}
			if ( $c_margin ) {
				$options['selectors']['compare'][] = 'margin:' . $c_margin;
			}
			if ( isset( $sorting['compare'] ) ) {
				$options['selectors']['compare'][] = 'order: ' . $sorting['compare'];
			}
			
			// compare hover
			if ( $c_hover_bg ) {
				$options['selectors']['compare_hover'][] = 'background-color:' . $c_hover_bg;
			}
			
			// output css generate
			$options['output_css'] = array();
			
			if ( count( $options['selectors']['item'] ) ) {
				$options['output_css'][] = $options['item'] . ' {' . implode( ';', $options['selectors']['item'] ) . '}';
			}
			
			if ( count( $options['selectors']['item_hover'] ) ) {
				$options['output_css'][] = $options['item_hover'] . ' {' . implode( ';', $options['selectors']['item_hover'] ) . '}';
			}
			
			if ( count( $options['selectors']['add_to_cart_button'] ) ) {
				$options['output_css'][] = $options['add_to_cart_button'] . ' {' . implode( ';', $options['selectors']['add_to_cart_button'] ) . '}';
			}
			
			if ( count( $options['selectors']['add_to_cart_button_hover'] ) ) {
				$options['output_css'][] = $options['add_to_cart_button_hover'] . ' {' . implode( ';', $options['selectors']['add_to_cart_button_hover'] ) . '}';
			}
			
			if ( count( $options['selectors']['wishlist'] ) ) {
				$options['output_css'][] = $options['wishlist'] . ' {' . implode( ';', $options['selectors']['wishlist'] ) . '}';
			}
			
			if ( count( $options['selectors']['wishlist_hover'] ) ) {
				$options['output_css'][] = $options['wishlist_hover'] . ' {' . implode( ';', $options['selectors']['wishlist_hover'] ) . '}';
			}
			
			if ( count( $options['selectors']['quick_view'] ) ) {
				$options['output_css'][] = $options['quick_view'] . ' {' . implode( ';', $options['selectors']['quick_view'] ) . '}';
			}
			
			if ( count( $options['selectors']['quick_view_hover'] ) ) {
				$options['output_css'][] = $options['quick_view_hover'] . ' {' . implode( ';', $options['selectors']['quick_view_hover'] ) . '}';
			}
			
			if ( count( $options['selectors']['compare'] ) ) {
				$options['output_css'][] = $options['compare'] . ' {' . implode( ';', $options['selectors']['compare'] ) . '}';
			}
			
			if ( count( $options['selectors']['compare_hover'] ) ) {
				$options['output_css'][] = $options['compare_hover'] . ' {' . implode( ';', $options['selectors']['compare_hover'] ) . '}';
			}
			
			if ( count( $options['selectors']['selectors_color'] ) ) {
				$options['output_css'][] = $options['selectors_color'] . ' {' . implode( ';', $options['selectors']['selectors_color'] ) . '}';
			}
			
			if ( count( $options['selectors']['selectors_color_hover'] ) ) {
				$options['output_css'][] = $options['selectors_color_hover'] . ' {' . implode( ';', $options['selectors']['selectors_color_hover'] ) . '}';
			}
			
		}
		
		ob_start();
		
		// add style one time on first load
		
		if ( ! $options['inline_css'] ) {
			if ( ! isset( $woocommerce_loop[ 'custom_template_' . $custom_template . 'buttons_css_added' ] ) ) {
				
				wp_add_inline_style( 'xstore-inline-css', implode( ' ', $options['output_css'] ) );
				
				$woocommerce_loop[ 'custom_template_' . $custom_template . 'buttons_css_added' ] = 'true';
			}
		} else {
			echo '<style>' . implode( ' ', $options['output_css'] ) . '</style>';
		}
		
		if ( count( $sorting ) > 0 && ! array_key_exists( 'compare', $sorting ) ) {
			$footer_class .= ' compare-hidden';
		}
		if ( ( count( $sorting ) > 0 && array_key_exists( 'cart', $sorting ) ) || count( $sorting ) == 0 ) {
			$footer_class .= ' cart-type-' . $cart_type;
		}
		if ( ( count( $sorting ) > 0 && array_key_exists( 'compare', $sorting ) ) || count( $sorting ) == 0 ) {
			$footer_class .= ' compare-type-' . $compare_type;
		}
		
		unset( $options );
		
		?>
        <footer class="footer-product2 <?php echo esc_attr( $footer_class ); ?>">
            <div class="footer-inner <?php echo esc_attr( $el_class ); ?>">
				<?php
				if ( ( count( $sorting ) > 0 && array_key_exists( 'q_view', $sorting ) ) || count( $sorting ) == 0 ) : ?>

                    <span class="show-quickly type-<?php echo esc_attr( $quick_type ); ?>"
                          data-prodid="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Quick View', 'xstore' ); ?></span>
				
				<?php endif; ?>
				
				<?php if ( ( count( $sorting ) > 0 && array_key_exists( 'cart', $sorting ) ) || count( $sorting ) == 0 ) {
					do_action( 'woocommerce_after_shop_loop_item' );
				}
				
				if ( ( count( $sorting ) > 0 && array_key_exists( 'wishlist', $sorting ) ) || count( $sorting ) == 0 ) {
					echo etheme_wishlist_btn( array( 'type' => $w_type ) );
				}
				?>

            </div>
        </footer>
		
		<?php return ob_get_clean();
		
	}
}

class ET_product_templates {
	
	protected $template = '';
	protected $html_template = false;
	protected $post = false;
	protected $grid_atts = array();
	protected $is_end = false;
	protected static $templates_added = false;
	protected $shortcodes = false;
	protected $found_variables = false;
	protected static $predefined_templates = false;
	protected $template_id = false;
	protected static $custom_fields_meta_data = false;
	
	/**
	 * Get shortcodes to build vc grid item templates.
	 *
	 * @return bool|mixed|void
	 */
	public function shortcodes() {
		if ( false === $this->shortcodes ) {
			$this->shortcodes = include vc_path_dir( 'PARAMS_DIR', 'vc_grid_item/shortcodes.php' );
			$this->shortcodes = apply_filters( 'vc_grid_item_shortcodes', $this->shortcodes );
		}
		add_filter( 'vc_shortcode_set_template_vc_icon', array( $this, 'addVcIconShortcodesTemplates' ) );
		add_filter( 'vc_shortcode_set_template_vc_button2', array( $this, 'addVcButton2ShortcodesTemplates' ) );
		add_filter( 'vc_shortcode_set_template_vc_single_image', array(
			$this,
			'addVcSingleImageShortcodesTemplates',
		) );
		add_filter( 'vc_shortcode_set_template_vc_custom_heading', array(
			$this,
			'addVcCustomHeadingShortcodesTemplates',
		) );
		add_filter( 'vc_shortcode_set_template_vc_btn', array( $this, 'addVcBtnShortcodesTemplates' ) );
		
		add_filter( 'vc_gitem_template_attribute_post_image_background_image_css', array(
			$this,
			'vc_gitem_template_attribute_post_image_background_image_css'
		), 10, 2 );
		
		return $this->shortcodes;
	}
	
	/**
	 * Get post image url
	 *
	 * @param $value
	 * @param $data
	 *
	 * @return string
	 */
	public function vc_gitem_template_attribute_post_image_background_image_css( $value, $data ) {
		$output = '';
		if ( ! class_exists( 'WooCommerce' ) ) {
			return $output;
		}
		
		global $post;
		/**
		 * @var null|Wp_Post $post ;
		 */
		extract( array_merge( array(
			'data' => '',
		), $data ) );
		$size = 'shop_catalog'; // default size
		
		if ( ! empty( $data ) ) {
			$size = $data;
		}
		
		$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
		$src               = wp_get_attachment_image_src( $post_thumbnail_id, $size );
		
		if ( ! empty( $src ) ) {
			$output = 'background-image: url(\'' . ( is_array( $src ) ? $src[0] : $src ) . '\') !important;';
		} elseif ( class_exists( 'WooCommerce' ) ) {
			$output = 'background-image: url(\'' . wc_placeholder_img_src() . '\') !important;';
		}
		
		return apply_filters( 'vc_gitem_template_attribute_post_image_background_image_css_value', $output );
	}
	
	/**
	 * Used by filter vc_shortcode_set_template_vc_icon to set custom template for vc_icon shortcode.
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function addVcIconShortcodesTemplates( $template ) {
		$file = vc_path_dir( 'TEMPLATES_DIR', 'params/vc_grid_item/shortcodes/vc_icon.php' );
		if ( is_file( $file ) ) {
			return $file;
		}
		
		return $template;
	}
	
	/**
	 * Used by filter vc_shortcode_set_template_vc_button2 to set custom template for vc_button2 shortcode.
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function addVcButton2ShortcodesTemplates( $template ) {
		$file = vc_path_dir( 'TEMPLATES_DIR', 'params/vc_grid_item/shortcodes/vc_button2.php' );
		if ( is_file( $file ) ) {
			return $file;
		}
		
		return $template;
	}
	
	/**
	 * Used by filter vc_shortcode_set_template_vc_single_image to set custom template for vc_single_image shortcode.
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function addVcSingleImageShortcodesTemplates( $template ) {
		$file = vc_path_dir( 'TEMPLATES_DIR', 'params/vc_grid_item/shortcodes/vc_single_image.php' );
		if ( is_file( $file ) ) {
			return $file;
		}
		
		return $template;
	}
	
	/**
	 * Used by filter vc_shortcode_set_template_vc_custom_heading to set custom template for vc_custom_heading
	 * shortcode.
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function addVcCustomHeadingShortcodesTemplates( $template ) {
		$file = vc_path_dir( 'TEMPLATES_DIR', 'params/vc_grid_item/shortcodes/vc_custom_heading.php' );
		if ( is_file( $file ) ) {
			return $file;
		}
		
		return $template;
	}
	
	/**
	 * Used by filter vc_shortcode_set_template_vc_button2 to set custom template for vc_button2 shortcode.
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function addVcBtnShortcodesTemplates( $template ) {
		$file = vc_path_dir( 'TEMPLATES_DIR', 'params/vc_grid_item/shortcodes/vc_btn.php' );
		if ( is_file( $file ) ) {
			return $file;
		}
		
		return $template;
	}
	
	/**
	 * Map shortcodes for vc_grid_item param type.
	 */
	public function mapShortcodes() {
		// @kludge
		// TODO: refactor with with new way of roles for shortcodes.
		// NEW ROLES like post_type for shortcode and access policies.
		$shortcodes = $this->shortcodes();
		foreach ( $shortcodes as $shortcode_settings ) {
			vc_map( $shortcode_settings );
		}
	}
	
	/**
	 * Get list of predefined templates.
	 *
	 * @return bool|mixed
	 */
	public static function predefinedTemplates() {
		if ( false === self::$predefined_templates ) {
			self::$predefined_templates = apply_filters( 'vc_grid_item_predefined_templates',
				include vc_path_dir( 'PARAMS_DIR', 'vc_grid_item/templates.php' ) );
		}
		
		return self::$predefined_templates;
	}
	
	/**
	 * @param $id - Predefined templates id
	 *
	 * @return array|bool
	 */
	public static function predefinedTemplate( $id ) {
		if ( $id == '' ) {
			$id = etheme_get_custom_product_template();
		}
		$predefined_templates = self::predefinedTemplates();
		if ( isset( $predefined_templates[ $id ]['template'] ) ) {
			return $predefined_templates[ $id ];
		}
		
		return false;
	}
	
	/**
	 * Set template which should grid used when vc_grid_item param value is rendered.
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function setTemplateById( $id ) {
		if ( $id == '' ) {
			$id = etheme_get_custom_product_template();
		}
		require_once vc_path_dir( 'PARAMS_DIR', 'vc_grid_item/templates.php' );
		if ( 0 === strlen( $id ) ) {
			return false;
		}
		if ( preg_match( '/^\d+$/', $id ) ) {
			$post = get_post( (int) $id );
			$post && $this->setTemplate( $post->post_content, $post->ID );
			
			return true;
		} elseif ( false !== ( $predefined_template = $this->predefinedTemplate( $id ) ) ) {
			$this->setTemplate( $predefined_template['template'], $id );
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Setter for template attribute.
	 *
	 * @param $template
	 * @param $template_id
	 */
	public function setTemplate( $template, $template_id ) {
		$this->template    = $template;
		$this->template_id = $template_id;
		$this->parseTemplate( $template );
	}
	
	/**
	 * Add custom css from shortcodes that were mapped for vc grid item.
	 * @return string
	 */
	public function addShortcodesCustomCss( $id = '' ) {
		global $woocommerce_loop;
		$output = $shortcodes_custom_css = '';
		if ( preg_match( '/^\d+$/', $id ) ) {
			$shortcodes_custom_css = get_post_meta( $id, '_wpb_shortcodes_custom_css', true );
		} elseif ( false !== ( $predefined_template = $this->predefinedTemplate( $id ) ) ) {
			$shortcodes_custom_css = visual_composer()->parseShortcodesCustomCss( $predefined_template['template'] );
		}
		
		if ( ! empty( $shortcodes_custom_css ) ) {
			
			$shortcodes_custom_css = strip_tags( $shortcodes_custom_css );
			
			// add style one time on first load
			
			if ( ! apply_filters( 'etheme_output_shortcodes_inline_css', isset($_GET['vc_editable']) ) ) {
				
				if ( ! isset( $woocommerce_loop[ 'custom_template_' . $id . 'et_custom_template_css_added' ] ) ) {
					
					wp_add_inline_style( 'xstore-inline-css', $shortcodes_custom_css );
					
					$woocommerce_loop[ 'custom_template_' . $id . 'et_custom_template_css_added' ] = 'true';
					
				}
			} else {
				$output .= '<style type="text/css" data-type="vc_shortcodes-custom-css">';
				$output .= $shortcodes_custom_css;
				$output .= '</style>';
			}
			
		}
		
		return $output;
	}
	
	/**
	 * Generates html with template's variables for rendering new project.
	 *
	 * @param $template
	 */
	public function parseTemplate( $template ) {
		$this->mapShortcodes();
		WPBMap::addAllMappedShortcodes();
		$attr                = ' width="' . $this->gridAttribute( 'element_width', 12 ) . '"'
		                       . ' is_end="' . ( 'true' === $this->isEnd() ? 'true' : '' ) . '"';
		$template            = preg_replace( '/(\[(\[?)vc_gitem\b)/', '$1' . $attr, $template );
		$this->html_template .= do_shortcode( $template );
	}
	
	/**
	 * Regexp for variables.
	 * @return string
	 */
	public function templateVariablesRegex() {
		return '/\{\{' . '\{?' . '\s*' . '([^\}\:]+)(\:([^\}]+))?' . '\s*' . '\}\}' . '\}?/';
	}
	
	/**
	 * Get default variables.
	 *
	 * @return array|bool
	 */
	public function getTemplateVariables() {
		if ( ! is_array( $this->found_variables ) ) {
			preg_match_all( $this->templateVariablesRegex(), $this->html_template, $this->found_variables, PREG_SET_ORDER );
		}
		
		return $this->found_variables;
	}
	
	/**
	 * Render item by replacing template variables for exact post.
	 *
	 * @param WP_Post $post
	 *
	 * @return mixed
	 */
	function renderItem( WP_Post $post, $content ) {
		$pattern     = array();
		$replacement = array();
		foreach ( $this->getTemplateVariables() as $var ) {
			$pattern[]     = '/' . preg_quote( $var[0], '/' ) . '/';
			$replacement[] = preg_replace( '/\\$/', '\\\$', $this->attribute( $var[1], $post, isset( $var[3] ) ? trim( $var[3] ) : '' ) );
		}
		
		return preg_replace( $pattern, $replacement, do_shortcode( $content ) );
	}
	
	/**
	 * Adds filters to build templates variables values.
	 */
	public function addAttributesFilters() {
		require_once vc_path_dir( 'PARAMS_DIR', 'vc_grid_item/attributes.php' );
	}
	
	/**
	 * Setter for Grid shortcode attributes.
	 *
	 * @param        $name
	 * @param string $default
	 *
	 * @return string
	 */
	public function gridAttribute( $name, $default = '' ) {
		return isset( $this->grid_atts[ $name ] ) ? $this->grid_atts[ $name ] : $default;
	}
	
	/**
	 * Get attribute value for WP_post object.
	 *
	 * @param        $name
	 * @param        $post
	 * @param string $data
	 *
	 * @return mixed|void
	 */
	public function attribute( $name, $post, $data = '' ) {
		$data = html_entity_decode( $data );
		
		return apply_filters( 'vc_gitem_template_attribute_' . trim( $name ),
			( isset( $post->$name ) ? $post->$name : '' ), array(
				'post' => $post,
				'data' => $data,
			) );
	}
	
	/**
	 * Checks is the end.
	 * @return bool
	 */
	public function isEnd() {
		return $this->is_end;
	}
	
}