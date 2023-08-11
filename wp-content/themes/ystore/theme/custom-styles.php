<?php  if ( ! defined('ETHEME_FW')) exit('No direct script access allowed');
// **********************************************************************//
// ! Initialize theme configuration and variables
// **********************************************************************//

// priority of 999 is in kirki 
add_action('wp_head', 'etheme_assets', 1000);
if(!function_exists('etheme_assets')) {
	function etheme_assets() {
		$post_id = (array)get_query_var('et_page-id', array( 'id' => 0, 'type' => 'page' ));
		$is_rtl = get_query_var('et_is-rtl', false);
		$css = et_custom_styles();
		$css .= et_custom_styles_responsive();
		
		$bg_image = etheme_get_custom_field('bg_image', $post_id['id']);
		$bg_color = etheme_get_custom_field('bg_color', $post_id['id']);
		
		if( ! empty( $bg_image ) || ! empty( $bg_color ) ) {
			$css .= 'body {';
			if( ! empty( $bg_color ) ) $css .= 'background-color: ' . $bg_color . '!important;';
			if( ! empty( $bg_image ) ) $css .= 'background-image: url(' . $bg_image .')!important;';
			$css .= '}';
		}
		
		// ! Breadcrumb background image for single pages
		global $post;
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
		
		$post_id = etheme_get_page_id( true );
		
		if ( $post_id['id'] == NULL ) {
			$post_id['id'] = is_object($post) ? $post->ID : 0;
		}
		
		if( in_array($post_id['type'], array('page', 'shop', 'blog', 'portfolio')) && $post_id['id'] > 0 && has_post_thumbnail($post_id['id']) && ! get_query_var( 'portfolio_category' ) ) {
			if ( $post_id['type'] == 'shop' && get_option( 'woocommerce_shop_page_id', '' ) == '' ) {
				$bread_bg['background-image'] = '';
			}
			else {
				$bread_bg['background-image'] = wp_get_attachment_url( get_post_thumbnail_id($post_id['id']), 'large');
			}
		}

//	if( is_category() || is_tax('product_cat') ) {
//		$term_id = get_queried_object()->term_id;
//
//		if( $term_id && $image = get_term_meta( $term_id, '_et_page_heading', true ) ) {
//			$bread_bg['background-image'] = $image;
//		}
//	}
		
		if ( get_query_var('portfolio_category') ) {
			$portfolio_page = etheme_get_option('portfolio_page', '');
			if ( ! empty( $portfolio_page ) && has_post_thumbnail( $portfolio_page ) ) {
				$bread_bg['background-image'] = get_the_post_thumbnail_url( $portfolio_page, 'large' );
			}
		}
		if( ! empty( $bread_bg['background-image'] ) || ! empty( $bread_bg['background-color'] ) ){
			$css .= '.page-heading {';
			if ( ! empty( $bread_bg['background-image'] ) ) {
				    $css .= 'background-image: url(' . $bread_bg['background-image'] . ');';
			}
            $css .= 'margin-bottom: 25px;';
			$css .= '}';
		}
		
		if ( !$bread_bg['background-image'] ) {
			wp_dequeue_script( 'breadcrumbs-effect-mouse');
		}
		
		// ! End of "Breadcrumb background image for single pages"
		if ( etheme_get_option('slider_arrows_colors', 'transparent') == 'transparent') {
			$css .= '.swiper-custom-right:not(.et-swiper-elementor-nav), .swiper-custom-left:not(.et-swiper-elementor-nav){
			background: transparent !important;
		}';
		}
		
		// site width + 50px size of arrow + 10px move path
		$css .= '@media only screen and (max-width: '.(get_theme_mod('site_width', 1170) + 50 + 10).'px) {';
            $css .= '.swiper-custom-left, .middle-inside .swiper-entry .swiper-button-prev, .middle-inside.swiper-entry .swiper-button-prev { '.($is_rtl ? 'right' : 'left').': -15px; }';
            $css .= '.swiper-custom-right, .middle-inside .swiper-entry .swiper-button-next, .middle-inside.swiper-entry .swiper-button-next { '.($is_rtl ? 'left' : 'right').': -15px; }';
            $css .= '.swiper-entry:hover .swiper-custom-left, .middle-inside .swiper-entry:hover .swiper-button-prev, .middle-inside.swiper-entry:hover .swiper-button-prev { '.($is_rtl ? 'right' : 'left').': -5px; }';
            $css .= '.swiper-entry:hover .swiper-custom-right, .middle-inside .swiper-entry:hover .swiper-button-next, .middle-inside.swiper-entry:hover .swiper-button-next { '.($is_rtl ? 'left' : 'right').': -5px; }';
        $css .= '}';
		
		if ( get_query_var( 'etheme_single_product_variation_gallery', false ) ) {
			$css .= '.swiper-control-top, .swiper-control-bottom { transition: min-height .3s ease-in-out; }';
		}
		
		// et-core-plugin
		$element_options = array();
		
		$element_options['media_query'] = get_theme_mod('mobile_header_start_from', 992);
		
		$element_options['item_model_box_def'] = 				array(
			'margin-top'          => '0px',
			'margin-right'        => '0px',
			'margin-bottom'       => '0px',
			'margin-left'         => '0px',
			'border-top-width'    => '0px',
			'border-right-width'  => '0px',
			'border-bottom-width' => '0px',
			'border-left-width'   => '0px',
			'padding-top'         => '10px',
			'padding-right'       => '10px',
			'padding-bottom'      => '10px',
			'padding-left'        => '10px',
		);
		// together options
		$element_options['menu_item_box_model_et-desktop']   = get_theme_mod( 'menu_item_box_model_et-desktop', $element_options['item_model_box_def']);
		$element_options['menu_nice_space_et-desktop']       = get_theme_mod( 'menu_nice_space_et-desktop', '0' );
		$element_options['menu_2_item_box_model_et-desktop'] = get_theme_mod( 'menu_2_item_box_model_et-desktop', $element_options['item_model_box_def'] );
		$element_options['menu_2_nice_space_et-desktop']     = get_theme_mod( 'menu_2_nice_space_et-desktop', '0' );

		ob_start();
		if ( $element_options['menu_nice_space_et-desktop'] ) { ?>
			.header-main-menu.et_element-top-level .menu {
			<?php echo ( isset( $element_options['menu_item_box_model_et-desktop']['margin-right'] ) ) ? 'margin-right:' . '-' . $element_options['menu_item_box_model_et-desktop']['margin-right'] : ''; ?>;
			<?php echo ( isset( $element_options['menu_item_box_model_et-desktop']['margin-left'] ) ) ? 'margin-left:' . '-' . $element_options['menu_item_box_model_et-desktop']['margin-left'] : ''; ?>;
			}
		<?php }
		
		if ( $element_options['menu_2_nice_space_et-desktop'] ) { ?>
			.header-main-menu2.et_element-top-level .menu {
			<?php echo ( isset( $element_options['menu_2_item_box_model_et-desktop']['margin-right'] ) ) ? 'margin-right:' . '-' . $element_options['menu_2_item_box_model_et-desktop']['margin-right'] : ''; ?>;
			<?php echo ( isset( $element_options['menu_2_item_box_model_et-desktop']['margin-left'] ) ) ? 'margin-left:' . '-' . $element_options['menu_2_item_box_model_et-desktop']['margin-left'] : ''; ?>;
			}
		<?php }
		
		if ( !get_query_var('et_mobile-optimization', false) ) { ?>
            @media only screen and (max-width: <?php echo esc_html($element_options['media_query']); ?>px) {
                .header-wrapper,
                .site-header-vertical {
                    display: none;
                }
            }

            @media only screen and (min-width: <?php echo esc_html($element_options['media_query'] + 1); ?>px) {
                .mobile-header-wrapper {
                    display: none;
                }
            }
		
		<?php }
		$css .= ob_get_clean();
		
		// JetPack fix for carousels
		$css .= '.swiper-container{width: auto}';
		
		if ( get_theme_mod('product_stretch_img', true)) {
			$css .= '.content-product .product-content-image img, .category-grid img, .categoriesCarousel .category-grid img{width: 100%}';
		}
		
		// Elementor widgets to make init slides with correct width proportions
		$css .= '.etheme-elementor-slider:not(.swiper-container-initialized) .swiper-slide{max-width:calc(100% / var(--slides-per-view, 4))}';
		
//		wp_add_inline_style('xstore-inline-custom-css', $css);
		echo '<style type="text/css" class="et_custom-css">' . et_minify_css($css). '</style>';
	}
}