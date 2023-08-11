<?php  if ( ! defined('ETHEME_FW')) exit('No direct script access allowed');

// **********************************************************************//
// ! Add custom query data
// **********************************************************************//
add_action('wp', 'et_custom_query', 1);
if ( ! function_exists( 'et_custom_query' ) ) {
	function et_custom_query(){
	    if ( is_admin() ) return;

		global $post;

//		$old_options = get_option('et_options', array());
//		set_query_var( 'et_redux_options', $old_options );
		
		$is_woocommerce = class_exists('WooCommerce');
		set_query_var('et_is-woocommerce', $is_woocommerce);
		
//		$id = $post_id['id'];
		$id = ( $post && is_object($post) && $post->ID) ? $post->ID : 0;
		$is_mobile_device = wp_is_mobile();
		$is_customize_preview = is_customize_preview();
		$etheme_single_product_builder = get_option( 'etheme_single_product_builder', false );
		$fixed_footer = ( ( etheme_get_option('footer_fixed', 0) || etheme_get_custom_field('footer_fixed', $id) == 'yes' ) );
		
		set_query_var('et_fixed-footer', $fixed_footer);
		
		set_query_var('et_is_customize_preview', $is_customize_preview);
		set_query_var('et_btt', etheme_get_option('to_top', 1) );
		set_query_var('et_btt-mobile', etheme_get_option('to_top_mobile', 1) );
		
		$template = etheme_get_option('post_template', 'default');
		
		$custom = etheme_get_custom_field('post_template', $id);
		
		if( ! empty( $custom ) ) {
			$template = $custom;
		}
		
		if ( $is_woocommerce ) {
			$grid_sidebar = etheme_get_option('grid_sidebar', 'left');
			set_query_var('et_grid-sidebar', $grid_sidebar);
			
			// set catalog mode query
            set_query_var('et_is-catalog', etheme_is_catalog());
			
			set_query_var('et_is-swatches', etheme_get_option( 'enable_swatch', 1 ) && class_exists( 'St_Woo_Swatches_Base' ));
			set_query_var('et_is-quick-view', etheme_get_option('quick_view', 1));
			set_query_var('et_is-quick-view-type', etheme_get_option('quick_view_content_type', 'popup'));
			
            $is_product_cat = is_product_category();
            
			if  (is_shop() || $is_product_cat || is_product_tag() || is_product_taxonomy() || is_tax('brand') || is_post_type_archive( 'product' ) ||
                 ( defined('WC_PRODUCT_VENDORS_TAXONOMY') && is_tax( WC_PRODUCT_VENDORS_TAXONOMY ) ) ||
                 (function_exists('dokan_is_store_page') && dokan_is_store_page()) ||
			     apply_filters('etheme_is_shop_page', false) ) {
				$view_mode = etheme_get_view_mode();
				set_query_var( 'et_view-mode', $view_mode );
				
				set_query_var('et_is-woocommerce-archive', true);
				set_query_var('et_is-products-masonry', etheme_get_option( 'products_masonry', 0 ));
				
				if ( etheme_get_option('sidebar_widgets_scroll', 0) ) {
					set_query_var('et_sidebar-widgets-scroll', true);
				}
				if ( etheme_get_option('sidebar_widgets_open_close', 0) ) {
					set_query_var('et_widgets-open-close', true);
					set_query_var('et_sidebar-widgets-open-close', true);
				}
				if ( etheme_get_option('filters_area_widgets_open_close', 0) ) {
					set_query_var('et_widgets-open-close', true);
					set_query_var('et_filters-area-widgets-open-close', true);
                }
				$filters_area_widgets_open_close_type = etheme_get_option('filters_area_widgets_open_close_type', 'open');
				if ($filters_area_widgets_open_close_type == 'closed' || (($filters_area_widgets_open_close_type == 'closed_mobile') && $is_mobile_device) ) {
					set_query_var('et_filters-area-widgets-open-close-default', true);
				}
				
				$sidebar_widgets_open_close_type = etheme_get_option('sidebar_widgets_open_close_type', 'open');
				if ($sidebar_widgets_open_close_type == 'closed' || (($sidebar_widgets_open_close_type == 'closed_mobile') && $is_mobile_device) ) {
					set_query_var('et_sidebar-widgets-open-close-default', true);
				}
				if ( etheme_get_option('show_plus_filters',0) ) {
					set_query_var('et_widgets-show-more', true);
					set_query_var('et_widgets-show-more-after', etheme_get_option('show_plus_filter_after',3));
                }
				
				// set product options
				$product_settings = etheme_get_option('product_page_switchers', array(
						'product_page_productname',
						'product_page_cats',
						'product_page_price',
						'product_page_addtocart',
						'product_page_productrating',
						'hide_buttons_mobile')
				);
				$product_settings = !is_array( $product_settings ) ? array() : $product_settings;
				set_query_var('et_product-variable-detach', etheme_get_option('variable_products_detach', false));
				set_query_var('et_product-variable-name-attributes', etheme_get_option('variation_product_name_attributes', true));
				
				set_query_var('et_product-hover', etheme_get_option('product_img_hover', 'slider'));
				set_query_var('et_product-view', etheme_get_option('product_view', 'disable'));
				set_query_var('et_product-view-color', etheme_get_option('product_view_color', 'white'));
				set_query_var('et_product-excerpt', etheme_get_option('product_page_excerpt', false));
				
				set_query_var('et_product-excerpt-length', etheme_get_option('product_page_excerpt_length', 120));
				set_query_var('et_product-switchers', $product_settings);
				set_query_var('et_product-with-quantity', etheme_get_option('product_page_smart_addtocart', 0));

                set_query_var('et_product-new-label-range', etheme_get_option('product_new_label_range', 0));
				set_query_var('et_product-title-limit-type', etheme_get_option('product_title_limit_type', 'chars'));
				set_query_var('et_product-title-limit', etheme_get_option('product_title_limit', 0));
				
				set_query_var('et_product-bordered-layout', etheme_get_option('product_bordered_layout', 0));
				set_query_var('et_product-no-space', etheme_get_option('product_no_space', 0));
				set_query_var('et_product-shadow-hover', etheme_get_option('product_with_box_shadow_hover', 0));
				
				// set shop products custom template
				$grid_custom_template = etheme_get_option('custom_product_template', 'default');
				$list_custom_template = etheme_get_option('custom_product_template_list', 'default');
				$list_custom_template = ( $list_custom_template != '-1' ) ? $list_custom_template : $grid_custom_template;
				
				set_query_var('et_custom_product_template', ( $view_mode == 'grid' ? (int)$grid_custom_template : (int)$list_custom_template ) );
				
				$view_mode_smart = etheme_get_option('view_mode', 'grid_list') == 'smart';
				set_query_var('view_mode_smart', $view_mode_smart);
				$view_mode_smart_active = etheme_get_option('view_mode_smart_active', 4);
				set_query_var('view_mode_smart_active', $view_mode_smart_active);
			}
			
			if ( $is_product_cat ) {
				$categories_sidebar = etheme_get_option('category_sidebar', 'left');
				set_query_var('et_cat-sidebar', $categories_sidebar);
				if ( $view_mode_smart ) {
					$view_mode_smart_active = etheme_get_option('categories_view_mode_smart_active', 4);
					set_query_var('view_mode_smart_active', $view_mode_smart_active);
				}
				$category_cols = (int)etheme_get_option('category_page_columns', 'inherit');
				if ( $category_cols >= 1 ) {
					set_query_var('et_cat-cols', $category_cols);
				}
			}
			
			elseif ( is_tax('brand') ) {
				$brand_sidebar = etheme_get_option('brand_sidebar', 'left');
				set_query_var('et_cat-sidebar', $brand_sidebar);
				if ( $view_mode_smart ) {
					$view_mode_smart_active = etheme_get_option('brands_view_mode_smart_active', 4);
					set_query_var('view_mode_smart_active', $view_mode_smart_active);
				}
				$brand_cols = (int)etheme_get_option('brand_page_columns', 'inherit');
				if ( $brand_cols >= 1 ) {
					set_query_var('et_cat-cols', $brand_cols);
				}
			}
			
			elseif ( is_cart() ) {
				set_query_var('et_is-cart', true);
            }
            elseif ( is_checkout() ) {
				set_query_var('et_is-checkout', true);
            }

//             if ( is_product() ) {
			
			if ( !$etheme_single_product_builder ) {

//				$layout = $l['product_layout'];
				$layout = etheme_get_option('single_layout', 'default');
				$single_layout = etheme_get_custom_field('single_layout');
				if(!empty($single_layout) && $single_layout != 'standard') {
					$layout = $single_layout;
				}
				
				$thumbs_slider_mode = etheme_get_option('thumbs_slider_mode', 'enable');
				
				if ( $thumbs_slider_mode == 'enable' || ( $thumbs_slider_mode == 'enable_mob' && $is_mobile_device ) ) {
					$gallery_slider = true;
				}
				else {
					$gallery_slider = false;
				}
				
				$thumbs_slider = etheme_get_option('thumbs_slider_vertical', 'horizontal');
				
				$enable_slider = etheme_get_custom_field('product_slider', $id);
				
				$stretch_slider = etheme_get_option('stretch_product_slider', 1);
				
				$slider_direction = etheme_get_custom_field('slider_direction', $id);
				
				$vertical_slider = $thumbs_slider == 'vertical';
				
				if ( $slider_direction == 'vertical' ) {
					$vertical_slider = true;
				}
				elseif($slider_direction == 'horizontal') {
					$vertical_slider = false;
				}
				
				$show_thumbs = $thumbs_slider != 'disable';
				
				if ( $layout == 'large' && $stretch_slider ) {
					$show_thumbs = false;
				}
				if ( $slider_direction == 'disable' ) {
					$show_thumbs = false;
				}
				elseif ( in_array($slider_direction, array('vertical', 'horizontal') ) ) {
					$show_thumbs = true;
				}
				if ( $enable_slider == 'on' || ($enable_slider == 'on_mobile' && $is_mobile_device ) ) {
					$gallery_slider = true;
				}
				elseif ( $enable_slider == 'off' || ($enable_slider == 'on_mobile' && !$is_mobile_device ) ) {
					$gallery_slider = false;
					$show_thumbs = false;
				}

//                    $etheme_single_product_variation_gallery = $gallery_slider && $show_thumbs && etheme_get_option('enable_variation_gallery');
			
			}
			else {
				
				$gallery_type = etheme_get_option('product_gallery_type_et-desktop', 'thumbnails_bottom');
				$vertical_slider = $gallery_type == 'thumbnails_left';
				
				$gallery_slider = ( !in_array($gallery_type, array('one_image', 'double_image')) );
				$show_thumbs = ( in_array($gallery_type, array('thumbnails_bottom', 'thumbnails_bottom_inside', 'thumbnails_left')));
//				$thumbs_slider = etheme_get_option('product_gallery_thumbnails_et-desktop', 1);
				
				if( defined('DOING_AJAX') && DOING_AJAX ) {
					$gallery_slider = true;
				}

//                    $etheme_single_product_variation_gallery = etheme_get_option('enable_variation_gallery');
				
			}
			
			set_query_var( 'etheme_single_product_gallery_type', $gallery_slider );
			set_query_var( 'etheme_single_product_vertical_slider', $vertical_slider );
			set_query_var( 'etheme_single_product_show_thumbs', $show_thumbs );
			
			$single_page_shortcode = ( ! empty( $post->post_content ) && strstr( $post->post_content, '[product_page' ) );
			
			if ( $single_page_shortcode ) {
				set_query_var('is_single_product_shortcode', true);
			}
			
			if ( is_product() ) {
				set_query_var( 'etheme_single_product_variation_gallery', apply_filters('etheme_single_product_variation_gallery', etheme_get_option('enable_variation_gallery', 0) ) );
				set_query_var('is_single_product', true);
				if ( etheme_get_option('single_product_widget_area_1_widget_scroll_et-desktop', 0) ) {
					set_query_var('et_sidebar-widgets-scroll', true);
				}
				
				if ( etheme_get_option('single_product_widget_area_1_widget_toggle_et-desktop', 0) ) {
					set_query_var('et_widgets-open-close', true);
					set_query_var('et_sidebar-widgets-open-close', true);
				}
				$single_product_widget_area_1_widget_toggle_actions = etheme_get_option('single_product_widget_area_1_widget_toggle_actions_et-desktop', 'opened');
				if ($single_product_widget_area_1_widget_toggle_actions == 'closed' || (($single_product_widget_area_1_widget_toggle_actions == 'mob_closed') && $is_mobile_device) ) {
					set_query_var('et_sidebar-widgets-open-close-default', true);
                }
			}
			
			set_query_var('etheme_variable_products_detach', etheme_get_option('variable_products_detach', false));
			set_query_var('etheme_variation_product_parent_hidden', etheme_get_option('variation_product_parent_hidden', true));
			set_query_var('etheme_variation_product_name_attributes', etheme_get_option('variation_product_name_attributes', true));
			
			// }
		}
		
		if ( etheme_get_option('portfolio_projects', 1) ) {
			set_query_var( 'et_portfolio-projects', true );
			set_query_var( 'et_portfolio-page', get_theme_mod( 'portfolio_page', '' ) );
		}
		
		// placed here to make work ok with query vars set above
		$post_id = etheme_get_page_id();
		
		if ( in_array($post_id['type'], array('post', 'blog')) || is_search() || is_tag() || is_category() || is_date() || is_author() ) {
			set_query_var('et_is-blog-archive', true);
		}
		
		// ! set-query-var
		set_query_var( 'is_yp', isset($_GET['yp_page_type'])); // yellow pencil
		set_query_var( 'et_post-template', $template );
		set_query_var( 'is_mobile', $is_mobile_device );
		set_query_var('et_mobile-optimization', get_theme_mod('mobile_optimization', false) && !$is_customize_preview);
		set_query_var( 'et_page-id', $post_id );
		set_query_var( 'etheme_single_product_builder', $etheme_single_product_builder );
		
		// after all of that because this requires some query vars are set above
		$l = etheme_page_config();
		
		if ($l['breadcrumb'] !== 'disable' && !$l['slider']) {
			set_query_var('et_breadcrumbs', true);
			set_query_var('et_breadcrumbs-type', $l['breadcrumb']);
			set_query_var('et_breadcrumbs-effect', $l['bc_effect']);
			set_query_var('et_breadcrumbs-color', $l['bc_color']);
		}
		
		set_query_var('et_page-slider', $l['slider']);
		set_query_var('et_page-banner', $l['banner']);
		
		set_query_var('et_content-class', $l['content-class']);
		set_query_var('et_sidebar', $l['sidebar']);
		set_query_var('et_sidebar-mobile', $l['sidebar-mobile']);
		set_query_var('et_sidebar-class', $l['sidebar-class']);
		set_query_var('et_widgetarea', $l['widgetarea']);
		
		set_query_var('et_product-layout', $l['product_layout']);
		
		if ( $is_mobile_device && etheme_get_option('footer_widgets_open_close', 1) ) {
			set_query_var('et_widgets-open-close', true);
		}
		
        set_query_var('et_main-layout', etheme_get_option( 'main_layout' ));
		set_query_var('et_is-rtl', is_rtl());
		set_query_var('et_is-single', is_single());
	}
}

function etheme_child_styles() {
	// files:
	// parent-theme/style.css, parent-theme/bootstrap.css (parent-theme/xstore.css), secondary-menu.css, options-style.min.css, child-theme/style.css
	$theme = wp_get_theme();
	$depends = array();
	
	$generated_css_js = get_option('etheme_generated_css_js');
	$generated_css = false;
	
	if ( isset($generated_css_js['css']['is_enabled']) && $generated_css_js['css']['is_enabled'] ){
		if ( $generated_css_js['css']['is_enabled'] ){
			if ( file_exists ($generated_css_js['css']['path']) ){
				$generated_css = true;
			}
		}
	}

	// Temp disable generated_css remove after fix on server
	$generated_css = false;
	
	if ($generated_css){
		wp_enqueue_style("et-generated-css",$generated_css_js['css']['url'], array(), $theme->version);
		wp_enqueue_style( 'child-style',
			get_stylesheet_directory_uri() . '/style.css',
			array('et-generated-css'),
			$theme->version
		);
	} else {
		
		wp_enqueue_style( 'child-style',
			get_stylesheet_directory_uri() . '/style.css',
//			array('parent-style', 'bootstrap'),
			array( 'etheme-parent-style' ),
			$theme->version
		);
	}
}

// **********************************************************************//
// ! Add classes to body
// **********************************************************************//
add_filter('body_class', 'etheme_add_body_classes');
if(!function_exists('etheme_add_body_classes')) {
	function etheme_add_body_classes($classes) {
		
		$post_id = (array)get_query_var('et_page-id', array( 'id' => 0, 'type' => 'page' ));
		$post_template  = get_query_var('et_post-template', 'default');
		
		$id = $post_id['id'];
		$etheme_single_product_builder = get_query_var('etheme_single_product_builder', false);
		
		// portfolio page asap fix
		$portfolio_page_id = get_query_var('et_portfolio-page', false);
		
		if ( get_query_var( 'et_portfolio-projects', false ) && $portfolio_page_id ) {
			
			if ( function_exists('icl_object_id') ) {
				global $sitepress;
				if ( ! empty( $sitepress )  ) {
					$multy_id = icl_object_id ( $id, "page", false, $sitepress->get_default_language() );
				} elseif( function_exists( 'pll_current_language' ) ) {
					$multy_id = icl_object_id ( $id, "page", false, pll_current_language() );
				} else {
					$multy_id = false;
				}
				
				if (  $id == $portfolio_page_id || $portfolio_page_id == $multy_id ) {
					foreach ( $classes as $key => $value ) {
						if ( in_array($value, array('page-template-default', 'page-template-portfolio') ) ) unset( $classes[ $key ] );
					}
					$classes[] = 'page-template-portfolio';
					etheme_enqueue_style( 'portfolio' );
					// mostly filters are not shown on portfolio category
					if ( ! get_query_var( 'portfolio_category' ) ) {
						etheme_enqueue_style( 'isotope-filters' );
					}
				}
			} else {
				if (  $id == $portfolio_page_id ) {
					foreach ( $classes as $key => $value ) {
						if ( in_array($value, array('page-template-default', 'page-template-portfolio') ) ) unset( $classes[ $key ] );
					}
					$classes[] = 'page-template-portfolio';
					etheme_enqueue_style( 'portfolio' );
					// mostly filters are not shown on portfolio category
					if ( ! get_query_var( 'portfolio_category' ) ) {
						etheme_enqueue_style( 'isotope-filters' );
					}
				}
			}
		}
		
		if ( get_query_var('et_is-woocommerce', false)) {
			$cart = etheme_get_option( 'cart_icon_et-desktop', 'type1' );
			switch ( $cart ) {
				case 'type1':
					$classes[] = 'et_cart-type-1';
					break;
				case 'type2':
					$classes[] = 'et_cart-type-4';
					break;
				case 'type4':
					$classes[] = 'et_cart-type-3';
					break;
				default:
					$classes[] = 'et_cart-type-2';
					break;
			}
		}
		
		$classes[] = (etheme_get_option('header_overlap_et-desktop', 0)) ? 'et_b_dt_header-overlap' : 'et_b_dt_header-not-overlap';
		$classes[] = (etheme_get_option('header_overlap_et-mobile', 0)) ? 'et_b_mob_header-overlap' : 'et_b_mob_header-not-overlap';
		
		// on hard testing
		if ( get_query_var('et_breadcrumbs', false) ) {
			$classes[] = 'breadcrumbs-type-' . get_query_var( 'et_breadcrumbs-type', 'none' );
		}
		$classes[] = get_query_var('et_main-layout', 'wide');
        if ( get_query_var('et_is-cart', false) || get_query_var('et_is-checkout', false) ) {
	        $classes[] = ( etheme_get_option( 'cart_special_breadcrumbs', 1 ) ) ? 'special-cart-breadcrumbs' : '';
        }
		$classes[] = (etheme_get_option('site_preloader', 0)) ? 'et-preloader-on' : 'et-preloader-off';
		$classes[] = (get_query_var('et_is-catalog', false)) ? 'et-catalog-on' : 'et-catalog-off';
		$classes[] = ( get_query_var('is_mobile', false) ) ? 'mobile-device' : '';
		if ( get_query_var('is_mobile', false) && etheme_get_option('footer_widgets_open_close', 1) ) {
			$classes[] = 'f_widgets-open-close';
			$classes[] = (etheme_get_option('footer_widgets_open_close_type', 'closed_mobile') == 'closed_mobile') ? 'fwc-default' : '';
		}
		
		// globally because conditions are set properly
		if ( get_query_var('et_sidebar-widgets-scroll', false) ) {
			$classes[] = 's_widgets-with-scroll';
		}
		if ( get_query_var('et_sidebar-widgets-open-close', false) ) {
			$classes[] = 's_widgets-open-close';
			if ( get_query_var('et_sidebar-widgets-open-close-default', false) ) {
				$classes[] = 'swc-default';
			}
		}
		
		if ( get_query_var('et_is-woocommerce', false)) {
            if ( get_query_var('et_filters-area-widgets-open-close', false) ) {
				$classes[] = 'fa_widgets-open-close';
				if ( get_query_var('et_filters-area-widgets-open-close-default', false) ) {
					$classes[] = 'fawc-default';
				}
			}
			
			if ( get_query_var('is_single_product', false) ) {
				$classes[] = 'sticky-message-'.(etheme_get_option('sticky_added_to_cart_message', 1) ? 'on' : 'off');
				if ( !$etheme_single_product_builder ) {
					$classes[] = 'global-product-name-'.(etheme_get_option('product_name_signle', 0) && !etheme_get_option('product_name_single_duplicated', 0) ? 'off': 'on');
				}
			}
		}
		
		if ( did_action('etheme_load_all_departments_styles') ) {
			// secondary
			$classes[] = 'et-secondary-menu-on';
			$classes[] = 'et-secondary-visibility-' . etheme_get_option( 'secondary_menu_visibility', 'on_hover' );
			if ( etheme_get_option( 'secondary_menu_visibility', 'on_hover' ) == 'opened' ) {
				$classes[] = ( etheme_get_option( 'secondary_menu_home', '1' ) ) ? 'et-secondary-on-home' : '';
				$classes[] = ( etheme_get_option( 'secondary_menu_subpages' ) ) ? 'et-secondary-on-subpages' : '';
			}
		}
		
		if ( !get_query_var('is_single_product', false) && get_query_var('et_is-single', false) ) {
			if ( $post_template == 'large2' ) {
				$post_template = 'large global-post-template-large2';
			}
			$classes[] = 'global-post-template-' . $post_template;
		}
		
		if ( class_exists( 'WooCommerce_Quantity_Increment' ) ) $classes[] = 'et_quantity-off';
		
		if ( get_query_var('et_is-swatches', false) ) {
			$classes[] = 'et-enable-swatch';
		}
		
		if ( etheme_get_option( 'et_optimize_js', 0 ) ) {
			$classes[] = 'et-old-browser';
		}
		
		return $classes;
	}
}

// **********************************************************************//
// ! Render custom styles
// **********************************************************************//
if ( !function_exists('et_custom_styles') ) {
	function et_custom_styles () {
		
		$css = '';
		
		$fonts = get_option( 'etheme-fonts', false );
		if ( $fonts ) {
			foreach ( $fonts as $value ) {
				// ! Validate format
				switch ( $value['file']['extension'] ) {
					case 'ttf':
						$format = 'truetype';
						break;
					case 'otf':
						$format = 'opentype';
						break;
//						case 'eot':
//							$format = false;
//							break;
					case 'eot?#iefix':
						$format = 'embedded-opentype';
						break;
					case 'woff2':
						$format = 'woff2';
						break;
					case 'woff':
						$format = 'woff';
						break;
					default:
						$format = false;
						break;
				}
				
				$format = ( $format ) ? 'format("' . $format . '")' : '';
				
				$font_url = ( is_ssl() && (strpos($value['file']['url'], 'https') === false) ) ? str_replace('http', 'https', $value['file']['url']) : $value['file']['url'];
				
				// ! Set fonts
				$css .= '
						@font-face {
							font-family: "' . $value['name'] . '";
							src: url(' . $font_url . ') ' . $format . ';
						}
					';
			}
		}
		
		$sale_size = etheme_get_option('sale_icon_size', '');
		$sale_size = explode( 'x', $sale_size );
		
		if ( ! isset( $sale_size[0] ) ) $sale_size[0] = 3.75;
		if ( ! isset( $sale_size[1] ) ) $sale_size[1] = $sale_size[0];
		
		$sale_width = $sale_size[0];
		$sale_height = $sale_size[1];
		
		if ( !empty($sale_width) || !empty($sale_height)) {
			$css .= '.onsale{';
			$css .= ( ! empty( $sale_width ) ) ? 'width:' . $sale_width . 'em;' : '';
			$css .= ( ! empty( $sale_height ) ) ? 'height:' . $sale_height . 'em; line-height: 1.2;' : '';
			$css .= '}';
		}
		
		$active_buttons_bg = etheme_get_option('active_buttons_bg',
			array(
				'regular'    => '',
				'hover'   => '',
			)
		);
		
		if ( is_array($active_buttons_bg) && isset($active_buttons_bg['hover']) && $active_buttons_bg['hover'] != '' ) {
			$css .= '.btn-checkout:hover, .btn-view-wishlist:hover {
				opacity: 1 !important;
			}';
		}
		
		if ( get_query_var('et_is-quick-view', false) && get_query_var('et_is-quick-view-type', 'popup') == 'popup') {
			$q_dimentions = etheme_get_option('quick_dimentions',
				array(
					'width'  => '',
					'height' => '',
				)
			);
		    if ( !empty($q_dimentions['width']) || !empty($q_dimentions['height']) ) {
			    $css .= '@media (min-width: 768px) {';
			    $css .= '.quick-view-popup.et-quick-view-wrapper {';
			    if ( ! empty( $q_dimentions['width'] ) ) {
				    $css .= 'width: ' . $q_dimentions['width'] . ';';
			    }
			    if ( ! empty( $q_dimentions['height'] ) ) {
				    $css .= 'height: ' . $q_dimentions['height'] . ';';
			    }
			
			    $css .= '}';
			
			    if ( ! empty( $q_dimentions['height'] ) ) {
				    $css .= '.quick-view-popup .product-content {';
				    $css .= 'max-height:' . $q_dimentions['height'] . ';';
				    $css .= '}';
				    $css .= '.quick-view-layout-default img, .quick-view-layout-default iframe {';
				    $css .= 'max-height:' . $q_dimentions['height'] . ';';
				    $css .= 'margin: 0 auto !important;';
				    $css .= '}';
			    }
			    $css .= '}';
		    }
		}
		
		// ! breadcrumb background
		$bread_bg = etheme_get_option( 'breadcrumb_bg',
			array(
				'background-color'      => '',
				'background-image'      => '',
				'background-repeat'     => '',
				'background-position'   => '',
				'background-size'       => '',
				'background-attachment' => '',
			)
		);
		
		if( ! empty( $bread_bg['background-image'] ) || ! empty( $bread_bg['background-color'] ) ){
			$css .= '.page-heading {';
			// set 0 margin if specific breadcrumbs on cart/checkout
			if ( (get_query_var('et_is-cart', false) || get_query_var('et_is-checkout', false)) &&
                ( etheme_get_option( 'cart_special_breadcrumbs', 1 ) || get_option('xstore_sales_booster_settings_cart_checkout_countdown') ) ) {
				$css .= 'margin-bottom: 0px !important;';
			}
			else {
				$css .= 'margin-bottom: 25px;';
			}
			$css .= '}';
//            if ( (get_query_var('et_is-cart', false) || get_query_var('et_is-checkout', false) ) && !etheme_get_option( 'cart_special_breadcrumbs', 1 ) ) {
//                $css .= '.page-heading ~ .sales-booster-cart-countdown {';
//                $css .= 'margin-top: -25px;';
//                $css .= '}';
//            }
		}
		
		$css = et_minify_css($css);
		return $css;
	}
}

if ( !function_exists('et_custom_styles_responsive') ) {
	function et_custom_styles_responsive () {
		$css = '';
		$custom_css = etheme_get_option('custom_css_global', '');
		$custom_css_desktop = etheme_get_option('custom_css_desktop', '');
		$custom_css_tablet = etheme_get_option('custom_css_tablet', '');
		$custom_css_wide_mobile = etheme_get_option('custom_css_wide_mobile', '');
		$custom_css_mobile = etheme_get_option('custom_css_mobile', '');
		if($custom_css != '') {
			$css .= $custom_css;
		}
		if($custom_css_desktop != '') {
			$css .= '@media (min-width: 993px) { ' . $custom_css_desktop . ' }';
		}
		if($custom_css_tablet != '') {
			$css .= '@media (min-width: 768px) and (max-width: 992px) {' . $custom_css_tablet . ' }';
		}
		if($custom_css_wide_mobile != '') {
			$css .= '@media (min-width: 481px) and (max-width: 767px) { ' . $custom_css_wide_mobile . ' }';
		}
		if($custom_css_mobile != '') {
			$css .= '@media (max-width: 480px) { ' . $custom_css_mobile . ' }';
		}
		$css = et_minify_css($css);
		return $css;
	}
}

if ( !function_exists('et_minify_css') ) {
	function et_minify_css ($css) {
		// Normalize whitespace
		$css = preg_replace( '/\s+/', ' ', $css );
		
		// Remove spaces before and after comment
		$css = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css );
		// Remove comment blocks, everything between /* and */, unless
		// preserved with /*! ... */ or /** ... */
		$css = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $css );
		// Remove ; before }
		$css = preg_replace( '/;(?=\s*})/', '', $css );
		// Remove space after , : ; { } */ >
		$css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );
		// Remove space before , ; { } ( ) >
		$css = preg_replace( '/ (,|;|\{|}|>)/', '$1', $css );
		// Strips leading 0 on decimal values (converts 0.5px into .5px)
		$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
		// Strips units if value is 0 (converts 0px to 0)
		$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
		// Converts all zeros value into short-hand
		$css = preg_replace( '/0 0 0 0/', '0', $css );
		// Shortern 6-character hex color codes to 3-character where possible
		$css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );
		return trim( $css );
		
	}
}

// **********************************************************************//
// ! Check woocommerce installed
// **********************************************************************//
if( ! function_exists('etheme_woocommerce_installed') ) {
	function etheme_woocommerce_installed() {
		return class_exists('WooCommerce');
	}
}

// **********************************************************************//
// ! WooCommerce active notice
// **********************************************************************//
// @todo could be in core
if( ! function_exists('etheme_woocommerce_notice') ) {
	function etheme_woocommerce_notice($notice = '') {
		if ( ! class_exists('WooCommerce') ) {
			if ( $notice == '' ) $notice = esc_html__( 'To use this element install or activate WooCommerce plugin', 'xstore' );
			echo '<p class="woocommerce-warning">' . $notice . '</p>';
			return true;
		} else {
			return false;
		}
	}
}

// **********************************************************************//
// ! core plugin active notice
// **********************************************************************//
if( ! function_exists('etheme_xstore_plugin_notice') ) {
	function etheme_xstore_plugin_notice($notice = '') {
		if ( ! defined( 'ET_CORE_DIR' ) ) {
			if ( $notice == '' ) $notice = esc_html__( 'To use this element install or activate XStore Core plugin', 'xstore' );
			echo '<p class="woocommerce-warning">' . $notice . '</p>';
			return true;
		} else {
			return false;
		}
	}
}

// **********************************************************************//
// ! Wp title
// **********************************************************************//
if(!function_exists('etheme_wp_title')) {
	function etheme_wp_title($title, $sep ) {
		global $paged, $page;
		
		if ( is_feed() ) {
			return $title;
		}
		
		// Add the site name.
		$title .= get_bloginfo( 'name', 'display' );
		
		// Add the site description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$title = "$title $sep $site_description";
		}
		
		// Add a page number if necessary.
		if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
			$title = "$title $sep " . sprintf( esc_html__( 'Page %s', 'xstore' ), max( $paged, $page ) );
		}
		
		return $title;
	}
}

add_filter( 'wp_title', 'etheme_wp_title', 10, 2 );

// **********************************************************************//
// ! Get column class bootstrap
// **********************************************************************//
// @todo product_functions/portfolio ?
if(!function_exists('etheme_get_product_class')) {
	function etheme_get_product_class($columns = 3 ) {
		$columns = intval($columns);
		
		if (! $columns ) {
			$columns = 3;
		}
		$cols = 12 / $columns;
		
		$small = 6;
		$extra_small = 6;
		
		$class = 'col-md-' . $cols;
		$class .= ' col-sm-' . $small;
		$class .= ' col-xs-' . $extra_small;
		
		return $class;
	}
}

// **********************************************************************//
// ! Custom Comment Form
// **********************************************************************//
if(!function_exists('etheme_custom_comment_form')) {
	function etheme_custom_comment_form($defaults) {
		$defaults['comment_notes_before'] = '
			<p class="comment-notes">
				<span id="email-notes">
				' . esc_html__( 'Your email address will not be published. Required fields are marked', 'xstore' ) . '
				</span>
			</p>
		';
		$defaults['comment_notes_after'] = '';
		$dafaults['id_form'] = 'comments_form';
		
		$defaults['comment_field'] = '
			<div class="form-group">
				<label for="comment" class="control-label">'.esc_html__('Your Comment', 'xstore').'</label>
				<textarea placeholder="' . esc_html__('Comment', 'xstore') . '" class="form-control required-field"  id="comment" name="comment" cols="45" rows="12" aria-required="true"></textarea>
			</div>
		';
		
		return $defaults;
	}
}

add_filter('comment_form_defaults', 'etheme_custom_comment_form');

if(!function_exists('etheme_custom_comment_form_fields')) {
	function etheme_custom_comment_form_fields() {
		$commenter = wp_get_current_commenter();
		$req = get_option('require_name_email');
		$reqT = '<span class="required">*</span>';
		$aria_req = ($req ? " aria-required='true'" : ' ');
		$consent  = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';
		$fields = array(
			'author' => '
				<div class="form-group comment-form-author">'.
			            '<label for="author" class="control-label">'.esc_html__('Name', 'xstore').' '.($req ? $reqT : '').'</label>'.
			            '<input id="author" name="author" placeholder="' . esc_html__('Your name (required)', 'xstore') . '" type="text" class="form-control ' . ($req ? ' required-field' : '') . '" value="' . esc_attr($commenter['comment_author']) . '" size="30" ' . $aria_req . '>'.
			            '</div>
			',
			'email' => '
				<div class="form-group comment-form-email">'.
			           '<label for="email" class="control-label">'.esc_html__('Email', 'xstore').' '.($req ? $reqT : '').'</label>'.
			           '<input id="email" name="email" placeholder="' . esc_html__('Your email (required)', 'xstore') . '" type="text" class="form-control ' . ($req ? ' required-field' : '') . '" value="' . esc_attr($commenter['comment_author_email']) . '" size="30" ' . $aria_req . '>'.
			           '</div>
			',
			'url' => '
				<div class="form-group comment-form-url">'.
			         '<label for="url" class="control-label">'.esc_html__('Website', 'xstore').'</label>'.
			         '<input id="url" name="url" placeholder="' . esc_html__('Your website', 'xstore') . '" type="text" class="form-control" value="' . esc_attr($commenter['comment_author_url']) . '" size="30">'.
			         '</div>
			',
			'cookies' => '
				<p class="comment-form-cookies-consent">
					<label for="wp-comment-cookies-consent">
						<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $consent . ' />' . '
						<span>' . esc_html__( 'Save my name, email, and website in this browser for the next time I comment.', 'xstore' ) . '</span>
					</label>
				</p>'
		);
		
		return $fields;
	}
}

add_filter('comment_form_default_fields', 'etheme_custom_comment_form_fields');

if ( ! function_exists( 'filter_login_form_middle' ) ) {
	function filter_login_form_middle( $content, $args ){
		$content .= '<a href="'.wp_lostpassword_url().'" class="lost-password">'.esc_html__('Lost Password ?', 'xstore').'</a>';
		return $content;
	}
}
add_filter( 'login_form_middle', 'filter_login_form_middle', 10, 2 );

// **********************************************************************//
// ! Enable shortcodes in text widgets
// **********************************************************************//
add_filter('widget_text', 'do_shortcode');

// **********************************************************************//
// ! Search, search SKU
// **********************************************************************/

add_action('pre_get_posts', 'etheme_search_all_sku_query');
if (! function_exists('etheme_search_all_sku_query')) {
	function etheme_search_all_sku_query($query){
		//add_filter('posts_join', 'etheme_search_post_join');
		add_filter('posts_where', 'etheme_search_post_excerpt');
	}
}

function etheme_search_post_join($join = ''){
	
	global $wp_the_query, $wpdb;
	
	// default
	$prefix = 'wp_';
	if ( $wpdb->prefix ) {
		// current site prefix
		$prefix = $wpdb->prefix;
	} elseif ( $wpdb->base_prefix ) {
		// wp-config.php defined prefix
		$prefix = $wpdb->base_prefix;
	}
	
	// escape if not woocommerce search query
	
	if ( empty( $wp_the_query->query_vars['wc_query'] ) || empty( $wp_the_query->query_vars['s'] ) )
		return $join;
	
	$join .= 'INNER JOIN '.$prefix.'postmeta AS jcmt1 ON ('.$prefix.'posts.ID = jcmt1.post_id)';
	
	return $join;
}

// Attention Attention!
// Do not move it inside of etheme_search_post_excerpt
// Because etheme_search_post_excerpt called minimum 7 times
// And it will add the same query several times
add_filter( 'woocommerce_price_filter_sql', function( $sql, $meta_query_sql, $tax_query_sql ){
	if ( isset( $_GET['filter_brand'] ) && ! empty($_GET['filter_brand']) ) {
		global $wpdb;
		$ids = et_get_active_brand_ids($_GET['filter_brand']);
		if ($ids){
		    // change to str_contains for php 8
            // else if dose not work here
			if (strpos($sql, "product_id") !== false){
				$sql .= "AND product_id IN(SELECT $wpdb->term_relationships.object_id  FROM $wpdb->term_relationships WHERE term_taxonomy_id  IN (" . $ids . "))";
				return $sql;

			}
			if (strpos($sql, "{$wpdb->posts}.ID") !== false) {
				$sql .= "AND {$wpdb->posts}.ID IN(SELECT $wpdb->term_relationships.object_id  FROM $wpdb->term_relationships WHERE term_taxonomy_id  IN (" . $ids . "))";
				return $sql;
			}
		}
	}
	return $sql;
}, 10, 3 );

if (!function_exists('et_get_active_brand_ids')){
    function et_get_active_brand_ids($filter_brand){
        if (! $filter_brand ) return false;
	    $brands = explode(',', $filter_brand);
	    $ids    = array();

	    foreach ($brands as $key => $value) {
		    $term = get_term_by('slug', $value, 'brand');
		    if ( ! isset( $term->term_taxonomy_id ) || empty( $term->term_taxonomy_id ) ) // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
		    {
		    } else {
			    $ids[] = $term->term_taxonomy_id;
		    }
	    }

	    if ( ! implode( ',', $ids ) ) {
		    $ids = 0;
	    } else {
		    $ids = implode( ',', $ids );
	    }

	    return $ids;
    }
}

if ( ! function_exists( 'etheme_search_post_excerpt' ) ) :
	
	function etheme_search_post_excerpt($where = ''){
		
		global $wp_the_query;
		global $wpdb;
		
		$prefix = 'wp_';
		if ( $wpdb->prefix ) {
			// current site prefix
			$prefix = $wpdb->prefix;
		} elseif ( $wpdb->base_prefix ) {
			// wp-config.php defined prefix
			$prefix = $wpdb->base_prefix;
		}
		
		// ! Filter by brands
		if ( isset( $_GET['filter_brand'] ) && ! empty($_GET['filter_brand']) ) {

			$ids = et_get_active_brand_ids($_GET['filter_brand']);

			if ($ids){
				$where .= " AND " . $prefix . "posts.ID IN ( SELECT " . $prefix . "term_relationships.object_id  FROM " . $prefix . "term_relationships WHERE term_taxonomy_id  IN (" . $ids . ") )";
			}
//			return $where;
		}
		
		$variable_products_detach = etheme_get_option('variable_products_detach', false);
//		$variable_products_no_parent = etheme_get_option('variation_product_parent_hidden', true);
		
		// ! WooCommerce search query
		if (is_search()){
			if ( empty( $wp_the_query->query_vars['wc_query'] ) || empty( $wp_the_query->query_vars['s'] ) ) return $where;
			
			$s = $wp_the_query->query_vars['s'];
			
			// ! Search by sku
			if (etheme_get_option('search_by_sku_et-desktop', 1)){
				if ( defined( 'ICL_LANGUAGE_CODE' ) && ! defined( 'LOCO_LANG_DIR' ) && ! defined( 'POLYLANG_PRO' ) ){
					$where .= " OR ( " . $prefix . "posts.ID IN ( SELECT " . $prefix . "postmeta.post_id  FROM " . $prefix . "postmeta WHERE meta_key = '_sku' AND meta_value LIKE '%$s%' )
					AND " . $prefix . "posts.ID IN (
						SELECT ID FROM {$wpdb->prefix}posts
						LEFT JOIN {$wpdb->prefix}icl_translations ON {$wpdb->prefix}icl_translations.element_id = {$wpdb->prefix}posts.ID
						WHERE post_type = 'product'
						AND post_status = 'publish'
						AND {$wpdb->prefix}icl_translations.language_code = '". ICL_LANGUAGE_CODE ."'
					) )";
				} else {
					$where .= " OR " . $prefix . "posts.ID IN ( SELECT " . $prefix . "postmeta.post_id  FROM " . $prefix . "postmeta WHERE meta_key = '_sku' AND meta_value LIKE '%$s%' )";
					$where .= " AND post_type = 'product'";

					if (isset($_GET['product_cat'])) {
						$category = get_term_by( 'slug', $_GET['product_cat'], 'product_cat' );

						if ($category && isset($category->term_id)) {
							$where .= " AND " . $prefix . "posts.ID IN ( SELECT " . $prefix . "term_relationships.object_id  FROM " . $prefix . "term_relationships WHERE term_taxonomy_id = '".$category->term_id."' )";
						}
					}
				}
			}
			
			// ! Add product_variation to search result
			if ( etheme_get_option('search_product_variation_et-desktop', 0) || $variable_products_detach ){
//				if ( $variable_products_detach && $variable_products_no_parent ) {
//					$where .= "AND " . $prefix . "posts.ID NOT IN (SELECT posts.ID  FROM ".$prefix."posts AS posts
//                        INNER JOIN ".$prefix."term_relationships AS term_relationships ON posts.ID = term_relationships.object_id
//                        INNER JOIN ".$prefix."term_taxonomy AS term_taxonomy ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id
//                        INNER JOIN ".$prefix."terms AS terms ON term_taxonomy.term_id = terms.term_id
//                        WHERE
//                            term_taxonomy.taxonomy = 'product_type'
//                        AND terms.slug = 'variable')";
//                }
				$where .= " OR post_type = 'product_variation' AND post_status = 'publish' AND (
				    post_title LIKE '%$s%' OR post_excerpt LIKE '%$s%' OR post_content LIKE '%$s%'
				    OR " . $prefix . "posts.ID IN ( SELECT " . $prefix . "postmeta.post_id  FROM " . $prefix . "postmeta WHERE meta_key = '_sku' AND meta_value LIKE '%$s%' )
				) ";

				if (isset($_GET['product_cat'])) {
					$category = get_term_by( 'slug', $_GET['product_cat'], 'product_cat' );

					if ($category && isset($category->term_id)) {
						$where .= " AND " . $prefix . "posts.ID IN ( SELECT " . $prefix . "term_relationships.object_id  FROM " . $prefix . "term_relationships WHERE term_taxonomy_id = '".$category->term_id."' )";
					}
				}

				//$where .= " OR " . $prefix . "posts.ID IN ( SELECT " . $prefix . "postmeta.post_id  FROM " . $prefix . "postmeta WHERE meta_key = '_sku' AND meta_value LIKE '%$s%' )";
			}
		}
//		elseif ( $variable_products_detach ) {
//			if ( empty( $wp_the_query->query_vars['wc_query'] ) ) return $where;
//
//			$visibility    = wc_get_product_visibility_term_ids();
//			if ( $variable_products_no_parent ) {
//				$where .= "AND " . $prefix . "posts.ID NOT IN (SELECT posts.ID  FROM " . $prefix . "posts AS posts
//                    INNER JOIN " . $prefix . "term_relationships AS term_relationships ON posts.ID = term_relationships.object_id
//                    INNER JOIN " . $prefix . "term_taxonomy AS term_taxonomy ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id
//                    INNER JOIN " . $prefix . "terms AS terms ON term_taxonomy.term_id = terms.term_id
//                    WHERE
//                        term_taxonomy.taxonomy = 'product_type'
//                    AND terms.slug = 'variable')";
//			}
////			$where .= " OR (post_type = 'product_variation' AND post_status = 'publish')";
//			$product_visibility_terms  = wc_get_product_visibility_term_ids();
//			$where .= " OR (post_type = 'product_variation' AND post_status = 'publish' AND post_parent NOT IN (
//			    SELECT object_id FROM ".$prefix."term_relationships AS term
//    WHERE term_taxonomy_id IN (".implode(',',(array)$product_visibility_terms['exclude-from-catalog']).")))";
//
//
//        }
		
		return $where;
	}
endif;

// **********************************************************************//
// ! Footer widgets class
// **********************************************************************//
if(!function_exists('etheme_get_footer_widget_class')) {
	function etheme_get_footer_widget_class($n) {
		$class = 'col-md-';
		switch ($n) {
			case 1:
				$class .= 12;
				break;
			case 2:
				$class .= 6;
				break;
			case 3:
				$class .= 4;
				break;
			case 4:
				$class .= 3;
				$class .= ' col-sm-6';
				break;
			default:
				$class .= 3;
				break;
		}
		return $class;
	}
}

// **********************************************************************//
// ! Get activated theme
// **********************************************************************//
if( ! function_exists( 'etheme_activated_theme' ) ) {
	function etheme_activated_theme() {
		$activated_data = get_option( 'etheme_activated_data' );
		
		// auto update option for old users
		if ( isset( $activated_data['purchase'] ) && $activated_data['purchase'] && get_option( 'envato_purchase_code_15780546', 'undefined' ) === 'undefined' ) {
			update_option( 'envato_purchase_code_15780546', $activated_data['purchase'] );
			
		}
		if( isset( $activated_data['purchase'] ) && $activated_data['purchase'] && $activated_data['purchase'] != get_option( 'envato_purchase_code_15780546', false )){
			return false;
		}
		
		$theme = ( isset( $activated_data['theme'] ) && ! empty( $activated_data['theme'] ) ) ? $activated_data['theme'] : false ;
		return $theme;
	}
	
}

// **********************************************************************//
// ! Is theme activated
// **********************************************************************//
if(!function_exists('etheme_is_activated')) {
	function etheme_is_activated() {
		if ( etheme_activated_theme() != ETHEME_PREFIX ) return false;
		if ( ! get_option( 'etheme_is_activated' ) ) update_option( 'etheme_is_activated', true );
		return get_option( 'etheme_is_activated', false );
	}
}

// **********************************************************************//
// ! Get image by size function
// **********************************************************************//
if( ! function_exists('etheme_get_image') ) {
	if( ! function_exists('etheme_get_image') ) {
		function etheme_get_image($attach_id, $size, $location = '') {

			$type   = '';
			if ( !(isset($_GET['vc_editable']) || (defined( 'DOING_AJAX' ) && DOING_AJAX) || is_admin()) ) {
				$type = get_theme_mod( 'images_loading_type_et-desktop', 'lazy' );
			}

			$class = '';

			if ($type == 'lqip') {
				$class .= ' lazyload lazyload-lqip et-lazyload-fadeIn';
			} elseif($type == 'lazy'){
				$class .= ' lazyload lazyload-simple et-lazyload-fadeIn';
			}

			if (function_exists('wpb_getImageBySize')) {
				$image = wpb_getImageBySize( array(
					'attach_id' => $attach_id,
					'thumb_size' => $size,
					'class' => $class
				) );
				$image = (isset($image['thumbnail'])) ? $image['thumbnail'] : false;
			} elseif (!empty($size) && ( ( !is_array($size) && strpos($size, 'x') !== false ) || is_array($size) ) && defined('ELEMENTOR_PATH') ) {
				$size = is_array($size) ? $size : explode('x', $size);
				if ( ! class_exists( 'Group_Control_Image_Size' ) ) {
					require_once ELEMENTOR_PATH . '/includes/controls/groups/image-size.php';
				}
				$image = \Elementor\Group_Control_Image_Size::get_attachment_image_html(
					array(
						'image' => array(
							'id' => $attach_id,
						),
						'image_custom_dimension' => array('width' => $size[0], 'height' => $size[1]),
						'image_size' => 'custom',
						'hover_animation' => ' ' . $class
					)
				);
			}
			else {
				$image = wp_get_attachment_image( $attach_id, $size, false, array('class' => $class) );
			}

			if ( $type && $type != 'default' ) {
				if ( $type == 'lqip') {
					if ( $size == 'woocommerce_thumbnail' ) {
						$placeholder = wp_get_attachment_image_src( $attach_id, 'etheme-woocommerce-nimi' );
					} else {
						$placeholder = wp_get_attachment_image_src( $attach_id, 'etheme-nimi' );
					}
					if ( isset( $placeholder[0] ) ) {
						$new_attr = 'src="' . $placeholder[0] . '" data-src';
						$image    = str_replace( 'src', $new_attr, $image );
					}
				}
				else {
					
					if (function_exists('wpb_getImageBySize')) {
						$placeholder_image_id = (int)get_option( 'xstore_placeholder_image', 0 );
						$placeholder_image = wpb_getImageBySize( array(
							'attach_id' => $placeholder_image_id,
							'thumb_size' => $size,
							'class' => $class
						) );
						
						$placeholder_image = $placeholder_image['thumbnail'] ?? false;
						if ( $placeholder_image ) {
							$placeholder_image = string_between_two_string( $placeholder_image, 'src="', '" ' );
						}
						else {
							$placeholder_image = etheme_placeholder_image($size, $attach_id);
						}
						
					} else {
						$placeholder_image = etheme_placeholder_image($size, $attach_id);
					}

					$new_attr = 'src="' . $placeholder_image . '" data-src';
					$image    = str_replace( 'src', $new_attr, $image );
				}
				$image = str_replace( 'sizes', 'data-sizes', $image );

			}

			return $image;
		}
	}

	function string_between_two_string($str, $starting_word, $ending_word){
		$subtring_start = strpos($str, $starting_word);
		$subtring_start += strlen($starting_word);
		$size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
		return substr($str, $subtring_start, $size);
	}
}

// **********************************************************************//
// ! Check file exists by url
// **********************************************************************//
if ( ! function_exists( 'etheme_custom_font_exists' ) ) :
	function etheme_custom_font_exists( $url ) {
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'] . '/custom-fonts';
		$url = explode( '/custom-fonts', $url );
		
		return file_exists( $upload_dir . $url[1] );
	}
endif;

// **********************************************************************//
// ! Force name sorting
// **********************************************************************//
// @todo could be in core
if ( ! function_exists( 'et_force_name_sort' ) ) :
	function et_force_name_sort( $array, $order ){
		
		if ( is_wp_error( $array ) || count( $array ) <= 0 ) return;
		
		// ! Set values
		$to_sort = array();
		$sorted = array();
		
		// ! Set names array
		foreach ( $array as $key => $value ) {
			$to_sort[] = strtolower( $value->name );
		}
		
		// ! Sort names array
		sort( $to_sort );
		
		// ! Change order if need it
		if ( $order == 'DESC' ){
			$to_sort = array_reverse( $to_sort );
		}
		
		// ! Set new sorted array
		foreach ( $to_sort as $key => $value ) {
			foreach ( $array as $k => $v ) {
				if ( $value == strtolower( $v->name ) ) {
					$sorted[] = $v;
				}
			}
		}
		return $sorted;
	}
endif;

function unicode_chars($source, $iconv_to = 'UTF-8') {
	$decodedStr = '';
	$pos = 0;
	$len = strlen ($source);
	while ($pos < $len) {
		$charAt = substr ($source, $pos, 1);
		$decodedStr .= $charAt;
		$pos++;
	}
	
	if ($iconv_to != "UTF-8") {
		$decodedStr = iconv("UTF-8", $iconv_to, $decodedStr);
	}
	
	return $decodedStr;
}

// For wpml test
apply_filters( 'wpml_current_language', NULL );

// **********************************************************************//
// ! Add activation redirect
// **********************************************************************//
add_action( 'after_switch_theme', 'et_activation_redirect' );
if ( ! function_exists( 'et_activation_redirect' ) ) :
	function et_activation_redirect() {
		if ( isset($_GET['page']) && $_GET['page'] == '_options' ) {
			if ( !class_exists( 'Kirki' ) || !etheme_is_activated() ) {
				header( 'Location:' . admin_url( 'admin.php?page=et-panel-welcome' ) );
			}
			else {
				header( 'Location:' . wp_customize_url() );
			}
		}
	}
endif;

// **********************************************************************//
// ! Add custom fonts to customizer typography
// **********************************************************************//
function et_kirki_custom_fonts( $standard_fonts ){
	$etheme_fonts = get_option( 'etheme-fonts', false );
	if ( ! is_array($etheme_fonts) || count( $etheme_fonts ) < 1 ) {
		return $standard_fonts;
	}
	$custom_fonts = array();
	
	foreach ( $etheme_fonts as $value ) {
		$custom_fonts[$value['name']] = array(
			'label' => $value['name'],
			'variant' => '400',
			'stack' => '"'.$value['name'].'"'
		);
	}
	
	$std_fonts = array(
		"Arial, Helvetica, sans-serif",
		"Courier, monospace",
		"Garamond, serif",
		"Georgia, serif",
		"Impact, Charcoal, sans-serif",
		"Tahoma,Geneva, sans-serif",
		"Verdana, Geneva, sans-serif",
	);
	
	foreach ( $std_fonts as $value) {
		$custom_fonts[$value] = array(
			'label' => $value,
			'variant' => '400',
			'stack' => $value
		);
	}
	
	return array_merge_recursive( $custom_fonts, $standard_fonts );
}
add_filter( 'kirki/fonts/standard_fonts', 'et_kirki_custom_fonts', 20 );

// **********************************************************************//
// ! Visibility of next/prev pruduct
// **********************************************************************//

if ( ! function_exists('et_visible_product') ) :
	function et_visible_product( $id, $valid ){
		$product = wc_get_product( $id );
		
		// updated for woocommerce v3.0
		$visibility = $product->get_catalog_visibility();
		$stock = $product->is_in_stock();
		
		if (  $visibility  != 'hidden' &&  $visibility  != 'search' && $stock ) {
			return get_post( $id );
		}
		
		$the_query = new WP_Query( array( 'post_type' => 'product', 'p' => $id ) );
		
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$valid_post = ( $valid == 'next' ) ? get_adjacent_post( 1, '', 0, 'product_cat' ) : get_adjacent_post( 1, '', 1, 'product_cat' );
				if ( empty( $valid_post ) ) return;
				$next_post_id = $valid_post->ID;
				$visibility = wc_get_product( $next_post_id );
				$stock = $visibility->is_in_stock();
				$visibility = $visibility->get_catalog_visibility();
				
			}
			// Restore original Post Data
			wp_reset_postdata();
		}
		
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $stock ) {
			return et_visible_product( $next_post_id, $valid );
		}
		
		if ( $visibility == 'visible' || $visibility == 'catalog' && $stock ) {
			return $valid_post;
		} else {
			return et_visible_product( $next_post_id, $valid );
		}
		
	}
endif;

// **********************************************************************//
// ! Project links
// **********************************************************************//

if ( ! function_exists('etheme_project_links') ) :
	function etheme_project_links() {
		etheme_enqueue_style( 'navigation', true );
		get_template_part( 'templates/navigation', 'prev-next' );
	}
endif;

// **********************************************************************//
// ! Notice "Plugin version"
// **********************************************************************//
add_action( 'admin_notices', 'etheme_required_core_notice', 50 );
add_action( 'wp_body_open', 'etheme_required_plugin_notice_frontend', 50 );

function etheme_required_core_notice(){
	
	$xstore_branding_settings = get_option( 'xstore_white_label_branding_settings', array() );
	
	if (
		count($xstore_branding_settings)
		&& isset($xstore_branding_settings['control_panel'])
		&& isset($xstore_branding_settings['control_panel']['hide_updates'])
		&& $xstore_branding_settings['control_panel']['hide_updates'] == 'on'
	){
		return;
	}
	
	
	$file = ABSPATH . 'wp-content/plugins/et-core-plugin/et-core-plugin.php';
	
	if ( ! file_exists($file) ) return;
	
	$plugin = get_plugin_data( $file, false, false );
	
	if ( version_compare( ETHEME_CORE_MIN_VERSION, $plugin['Version'], '>' ) ) {
		$video = '<a class="et-button" href="https://www.youtube.com/watch?v=xMEoi3rKoHk" target="_blank" style="color: white!important; text-decoration: none"><span class="dashicons dashicons-video-alt3" style="color: var(--et_admin_red-color, #c62828);git "></span> Video tutorial</a>';
		
		echo '
        <div class="et-message et-warning">
            This theme version requires the following plugin <strong>XStore Core</strong> to be updated up to <strong>' . ETHEME_CORE_MIN_VERSION . ' version. </strong>You can install the updated version of XStore core plugin: <ul>
                <li>1) via <a href="'.admin_url('update-core.php').'">Dashboard</a> > Updates > click Check again button > update plugin</li>
                <li>2) via FTP using archive from <a href="https://www.8theme.com/downloads" target="_blank">Downloads</a></li>
                <li>3) via FTP using archive from the full theme package downloaded from <a href="https://themeforest.net/" target="_blank">ThemeForest</a></li>
                <li>4) via <a href="https://wordpress.org/plugins/easy-theme-and-plugin-upgrades/" target="_blank">Easy Theme and Plugin Upgrades</a> WordPress Plugin</li>
                <li>5) Don\'t Forget To Clear <strong style="color:#c62828;"> Cache! </strong></li>
                </ul>
                <br>
                ' . $video . '
                <br><br>
        </div>
    ';
	}
}

function etheme_required_plugin_notice_frontend(){
	if ( is_user_logged_in() && current_user_can('administrator') ) {
		
		$xstore_branding_settings = get_option( 'xstore_white_label_branding_settings', array() );
		
		if (
			count($xstore_branding_settings)
			&& isset($xstore_branding_settings['control_panel'])
			&& isset($xstore_branding_settings['control_panel']['hide_updates'])
			&& $xstore_branding_settings['control_panel']['hide_updates'] == 'on'
		){
			return;
		}
		
		if( !function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		$file = ABSPATH . 'wp-content/plugins/et-core-plugin/et-core-plugin.php';
		
		if ( ! file_exists($file) ) return;
		
		$plugin = get_plugin_data( $file, false, false );
		
		if ( version_compare( ETHEME_CORE_MIN_VERSION, $plugin['Version'], '>' ) ) {
			$video = '<a class="et-button et-button-active" href="https://www.youtube.com/watch?v=xMEoi3rKoHk" target="_blank"> Video tutorial</a>';
			echo '
				</br>
				<div class="woocommerce-massege woocommerce-info error">
					XStore theme requires the following plugin: <strong>Core plugin v.' . ETHEME_CORE_MIN_VERSION . '.</strong>
					'.$video.'. This warning is visible for <strong>administrator only</strong>.
				</div>
			';
		}
	}
}

// **********************************************************************//
// ! add sizes for LQIP
// **********************************************************************//
$cropping = get_option( 'woocommerce_thumbnail_cropping', '1:1' );

if ( 'uncropped' === $cropping ) {
	add_image_size( 'etheme-woocommerce-nimi', 10, 10 );
} elseif ( 'custom' === $cropping ) {
	$width          = max( 1, get_option( 'woocommerce_thumbnail_cropping_custom_width', '4' ) );
	$height         = max( 1, get_option( 'woocommerce_thumbnail_cropping_custom_height', '3' ) );
	add_image_size( 'etheme-woocommerce-nimi', $width, $height );
} else {
	$cropping_split = explode( ':', $cropping );
	$width          = max( 1, current( $cropping_split ) );
	$height         = max( 1, end( $cropping_split ) );
	add_image_size( 'etheme-woocommerce-nimi', $width, $height );
}

add_image_size( 'etheme-nimi', 10, 10 );

function etheme_get_image_sizes( $size = '' ) {
	$wp_additional_image_sizes = wp_get_additional_image_sizes();
	
	$sizes = array();
	$get_intermediate_image_sizes = get_intermediate_image_sizes();
	
	// Create the full array with sizes and crop info
	foreach( $get_intermediate_image_sizes as $_size ) {
		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
			$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );
		} elseif ( isset( $wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width' => $wp_additional_image_sizes[ $_size ]['width'],
				'height' => $wp_additional_image_sizes[ $_size ]['height'],
				'crop' =>  $wp_additional_image_sizes[ $_size ]['crop']
			);
		}
	}
	
	// Get only 1 size if found
	if ( $size ) {
		if( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}
	}
	return $sizes;
}

function etheme_get_demo_versions(){
	$versions   = get_transient( 'etheme_demo_versions_info' );
	$url        = 'http://8theme.com/import/xstore-demos/1/versions';
	$url        = apply_filters( 'etheme_demos_url', $url);
	
	if ( ! $versions || empty( $versions ) || isset($_GET['etheme_demo_versions_info']) ) {
		$api_response = wp_remote_get( $url );
		$code         = wp_remote_retrieve_response_code( $api_response );
		
		if ( $code == 200 ) {
			$api_response = wp_remote_retrieve_body( $api_response );
			$api_response = json_decode( $api_response, true );
			$versions = $api_response;
			set_transient( 'etheme_demo_versions_info', $versions, 12 * HOUR_IN_SECONDS );
		} else {
			$versions = array();
		}
	}
	return $versions;
}

add_filter( 'woocommerce_create_pages', 'etheme_do_not_setup_demo_pages', 10 );
function etheme_do_not_setup_demo_pages($args){
	if (
		isset($_REQUEST['action'])
		&& $_REQUEST['action'] == 'install_pages'
		&& isset($_REQUEST['page'])
		&& $_REQUEST['page'] == 'wc-status'
	){
		return $args;
	}
	return array();
}

if( ! function_exists('etheme_is_catalog') ){
	function etheme_is_catalog(){
		if ( etheme_get_option( 'just_catalog', 0 ) ){
			if ( etheme_get_option( 'just_catalog_type', 'all' ) == 'unregistered' && is_user_logged_in()){
				return false;
			} else {
				return true;
			}
		}
		return false;
	}
}

// speed optimizations
add_action('wp_head', function() {
//	global $post;
//	if ( is_object($post) ) {
//		if ( $post->post_type == 'staticblocks' && function_exists( 'wp_robots_no_robots' ) ) {
//			//wp_robots_no_robots();
//		}
//	}
	
	$icons_type = ( etheme_get_option('bold_icons', 0) ) ? 'bold' : 'light';
	?>

    <?php if ( apply_filters('etheme_preload_woff_icons', true)) : ?>
        <link rel="prefetch" as="font" href="<?php echo esc_url( get_template_directory_uri() ); ?>/fonts/xstore-icons-<?php echo esc_attr($icons_type); ?>.woff?v=<?php echo esc_attr( ETHEME_THEME_VERSION ); ?>" type="font/woff">
    <?php endif; ?>

	<?php if ( apply_filters('etheme_preload_woff2_icons', true)) : ?>
        <link rel="prefetch" as="font" href="<?php echo esc_url( get_template_directory_uri() ); ?>/fonts/xstore-icons-<?php echo esc_attr($icons_type); ?>.woff2?v=<?php echo esc_attr( ETHEME_THEME_VERSION ); ?>" type="font/woff2">
    <?php
    endif;
});

add_action('init', function () {
	$placeholder_image = get_option( 'xstore_placeholder_image', 0 );
	if ( ! empty( $placeholder_image ) ) {
		if ( ! is_numeric( $placeholder_image ) ) {
			return;
		} elseif ( $placeholder_image && wp_attachment_is_image( $placeholder_image ) ) {
			return;
		}
	}
	
	$upload_dir = wp_upload_dir();
	$source     = ETHEME_BASE . 'images/lazy' . ( get_theme_mod( 'dark_styles', 0 ) ? '-dark' : '' ) . '.png';
	$filename   = $upload_dir['basedir'] . '/xstore/xstore-placeholder.png';
	
	// let's create folder if not exists
	if ( ! file_exists( $upload_dir['basedir'] . '/xstore' ) ) {
		wp_mkdir_p( $upload_dir['basedir'] . '/xstore' );
	}
	
	if ( ! file_exists( $filename ) ) {
		copy( $source, $filename ); // @codingStandardsIgnoreLine.
	}
	
	if ( ! file_exists( $filename ) ) {
		update_option( 'xstore_placeholder_image', 0 );
		return;
	}
	
	$filetype   = wp_check_filetype( basename( $filename ), null );
	$attachment = array(
		'guid'           => $upload_dir['url'] . '/' . basename( $filename ),
		'post_mime_type' => $filetype['type'],
		'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$attach_id  = wp_insert_attachment( $attachment, $filename );
	
	update_option( 'xstore_placeholder_image', $attach_id );
	
	// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
	require_once ABSPATH . 'wp-admin/includes/image.php';
	
	// Generate the metadata for the attachment, and update the database record.
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id, $attach_data );
});

// tweak to include pagination style for shortcodes
add_filter('paginate_links_output', function ($r) {
    if (!empty($r)) {
	    etheme_enqueue_style( 'pagination' );
    }
    return $r;
});

// Show xstore avatars
add_filter('get_avatar_data', 'xstore_change_avatar', 100, 2);
function xstore_change_avatar($args, $id_or_email) {

	$xstore_avatar = get_user_meta( $id_or_email, 'xstore_avatar', true);
	if(get_theme_mod( 'load_social_avatar_value', 'off' ) === 'on' && $xstore_avatar) {
		$args['url'] = wp_get_attachment_url($xstore_avatar);
	}
	return $args;
}

// Maintenance mode
if ( get_option('etheme_maintenance_mode', false) ) {
 
	add_action('template_redirect', function () {
		$pages = get_pages(array(
			'meta_key' => '_wp_page_template',
			'meta_value' => 'maintenance.php'
		));
		
		$return = array();
		
		foreach($pages as $page){
			$return[] = $page->ID;
		}
		$page_id = current( $return );
		
		if ( ! $page_id ) {
			return;
		}
		
		if ( ! is_page( $page_id ) && ! is_user_logged_in() ) {
			wp_redirect( get_permalink( $page_id ) );
			exit();
		}
    }, 20);
	
}

add_action( 'wp_head', function(){
	if (
		etheme_get_option( 'et_seo_noindex', 0 )
        && get_query_var('et_is-woocommerce-archive', false)
	) {

		$url = parse_url($_SERVER['REQUEST_URI']);
		if (isset($url['query'])){
			echo "\n\t\t<!-- 8theme SEO v1.0.0 -->";
			echo '<meta name="robots" content="noindex, nofollow">';
			echo "\t\t<!-- 8theme SEO -->\n\n";
		}
	}
}, 1 );