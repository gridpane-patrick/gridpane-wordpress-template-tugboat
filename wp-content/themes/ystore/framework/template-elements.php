<?php  if ( ! defined('ABSPATH')) exit('No direct script access allowed');
// **********************************************************************//
// ! Page heading
// **********************************************************************//

if(!function_exists('etheme_page_heading')) {
	
	add_action('etheme_page_heading', 'etheme_page_heading', 10);
	
	function etheme_page_heading() {
		
		get_template_part( 'templates/page-heading' );
		
		return;
		
	}
}

// **********************************************************************//
// ! ET loader HTML
// **********************************************************************//
if (!function_exists('etheme_loader')) {
	function etheme_loader($echo = true, $class="") {
		
		$type = get_theme_mod( 'images_loading_type_et-desktop', 'lazy' );
		
		if ( in_array( $type, array('default', 'lqip') ) && !in_array( $class, array( 'no-lqip', 'product-ajax' ) ) ) {
			return;
		}
		
		$img = etheme_get_option( 'preloader_images', '' );
		
		$html = '';
		
		if ( ! empty( $img['url'] ) ){
			$html .= '<img class="et-loader-img" src="' . $img['url'] . '" alt="et-loader">';
		} else {
			$html .= '<svg class="loader-circular" viewBox="25 25 50 50" width="30" height="30"><circle class="loader-path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"></circle></svg>';
		}
		
		if ($echo) {
			echo '<div class="et-loader ' . esc_attr( $class ) . '">' . $html .'</div>';
		} else {
			return '<div class="et-loader ' . esc_attr( $class ) . '">' . $html .'</div>';
		}
	}
}

// **********************************************************************//
// ! Site preloader
// **********************************************************************//
if ( ! function_exists('etheme_site_preloader') ) {
	function etheme_site_preloader(){
		
		if ( !etheme_get_option('site_preloader', 0) ) return;
		
		$img = etheme_get_option( 'preloader_img', '' );
		
		$html = '';
		
		if ( ! empty( $img['url'] ) ){
			$html .= '<img class="et-loader-img" src="' . $img['url'] . '" alt="et-loader">';
		} else {
			$html .= '<svg class="loader-circular" viewBox="25 25 50 50"><circle class="loader-path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"></circle></svg>';
		}
		
		echo '<div class="et-loader">' . $html .'</div>';
		
	}
}
add_action( 'et_after_body', 'etheme_site_preloader', 100, 1);

// **********************************************************************// 
// ! Pagination links
// **********************************************************************// 
if(!function_exists('etheme_pagination')) {
	function etheme_pagination($args = array()) {
		extract( shortcode_atts( array(
			'type'   => 'default',
			'url'    => '',
			'pages'  => 1,
			'paged'  => 1,
			'range'  => 2,
			'class'  => '',
			'before' => '',
			'after'  => '',
			'prev_next' => true,
			'prev_text' => '<i class="et-icon et-'.(is_rtl() ? 'right' : 'left').'-arrow"></i>',
			'next_text' => '<i class="et-icon et-'.(is_rtl() ? 'left' : 'right').'-arrow"></i>'
		), $args ) );
		
		if( $pages != 1 ){
			etheme_enqueue_style('pagination', true );
			$showitems = ( $range * 2 )+1;
			$out = '';
			
			if ( $type != 'default' ) {
				
				if ( ! $url ) {
					$url = get_permalink();
				}
				
				if( $prev_next && $paged > 1  ){
					$out .= '<li><a href="' . add_query_arg( 'et-paged', ( $paged - 1 ), $url ) . '" class="prev page-numbers">' . $prev_text . '</a></li>';
				}
				
				
				for ( $i=1; $i <= $pages; $i++ ){
					if ( $pages != 1 &&( ! ( $i >= $paged+$range+1 || $i <= $paged-$range-1 ) || $pages <= $showitems ) ){
						if ( $paged == $i ) {
							$out .= '<li><span class="page-numbers current">' . $i . '</span></li>';
						} else {
							$out .= '<li><a href="' . add_query_arg( 'et-paged', $i, $url ) . '" class="inactive">' . $i . '</a></li>';
						}
					}
				}
				
				if ( $prev_next && $paged < $pages ){
					$out .= '<li><a href="' . add_query_arg( 'et-paged', ( $paged + 1 ), $url ) . '" class="next page-numbers">' . $next_text . '</a></li>';
				}
				
			} else {
				if( $prev_next && $paged > 1  ){
					$out .= '<li><a href="' . get_pagenum_link($paged-1) . '" class="prev page-numbers">' . $prev_text . '</a></li>';
				}
				
				for ( $i=1; $i <= $pages; $i++ ){
					if ( $pages != 1 &&( ! ( $i >= $paged+$range+1 || $i <= $paged-$range-1 ) || $pages <= $showitems ) ){
						if ( $paged == $i ) {
							$out .= '<li><span class="page-numbers current">' . $i . '</span></li>';
						} else {
							$out .= '<li><a href="' . get_pagenum_link($i) . '" class="inactive">' . $i . '</a></li>';
						}
					}
				}
				
				if ( $prev_next && $paged < $pages ){
					$out .= '<li><a href="' . get_pagenum_link($paged + 1) . '" class="next page-numbers">' . $next_text . '</a></li>';
				}
			}
			
			
			echo '
				<div class="etheme-pagination ' . $class . '">
				' . $before . '
				<nav class="pagination-cubic"><ul class="page-numbers">' . $out . '</ul></nav>
				' . $after . '
				</div>
	        ';
		}
	}
}

// **********************************************************************//
// ! Function to display comments @see comments.php
// **********************************************************************//
if(!function_exists('etheme_comments')) {
	function etheme_comments($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited valid usecase
		if( get_comment_type() == 'pingback' || get_comment_type() == 'trackback' ) : ?>
            <li id="comment-<?php comment_ID(); ?>" class="pingback">
            <div class="comment-block row">
                <div class="col-md-12">
                    <div class="author-link"><?php esc_html_e('Pingback:', 'xstore') ?></div>
                    <div class="comment-reply"> <?php edit_comment_link(); ?></div>
					<?php comment_author_link(); ?>
                </div>
            </div>
            <div class="media">
                <h4 class="media-heading"><?php esc_html_e('Pingback:', 'xstore') ?></h4>
				<?php comment_author_link(); ?>
				<?php edit_comment_link(); ?>
            </div>
		<?php elseif(get_comment_type() == 'comment') :
			$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) ); ?>

            <li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
            <div class="media">
                <div class="pull-left"><?php echo get_avatar($comment, 80); ?></div>
				
				<?php if ( $rating && get_option( 'woocommerce_enable_review_rating' ) == 'yes' ) : ?>
                    <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title="<?php echo sprintf( esc_html__( 'Rated %d out of 5', 'xstore' ), $rating ) ?>">
							<span style="width:<?php echo esc_attr( ( $rating / 5 ) * 100 ); ?>%">
								<strong itemprop="ratingValue"><?php echo intval( $rating ); ?></strong> <?php esc_html_e( 'out of 5', 'xstore' ); ?>
							</span>
                    </div>
				<?php endif; ?>

                <div class="media-body">
                    <h4 class="media-heading"><?php comment_author_link(); ?></h4>
                    <div class="meta-comm"><?php comment_date(); ?> - <?php comment_time(); ?></div>
					
					<?php if ($comment->comment_approved == '0'): ?>
                        <p class="awaiting-moderation"><?php esc_html__('Your comment is awaiting moderation.', 'xstore') ?></p>
					<?php endif;
					
					comment_text();
					
					comment_reply_link(array_merge(
						$args, array('reply_text' => esc_html__('Reply to comment', 'xstore'),
						             'depth' => $depth, 'max_depth' => $args['max_depth'])
					));
					?>
                </div>
            </div>
		<?php endif;
	}
}

// **********************************************************************//
// ! Site breadcrumbs
// **********************************************************************//
if(!function_exists('etheme_breadcrumbs')) {
	function etheme_breadcrumbs() {
		get_template_part( 'templates/breadcrumbs' );
		return;
	}
}

if(!function_exists('etheme_back_to_page')) {
	function etheme_back_to_page() {
		echo '<a class="back-history" href="javascript: history.go(-1)">' . esc_html__( 'Return to previous page', 'xstore' ) . '</a>';
	}
}

// **********************************************************************//
// ! Back to top button
// **********************************************************************//
if(!function_exists('etheme_btt_button')) {
	function etheme_btt_button() {
		get_template_part( 'templates/btt-button' );
		return;
	}
}
add_action('after_page_wrapper', 'etheme_btt_button', 30);

// **********************************************************************// 
// ! Bordered layout
// **********************************************************************//
if(!function_exists('etheme_bordered_layout')) {
	function etheme_bordered_layout() {
		
		if(get_query_var('et_main-layout', 'wide') != 'bordered') return;
		
		?>
        <div class="body-border-left"></div>
        <div class="body-border-top"></div>
        <div class="body-border-right"></div>
        <div class="body-border-bottom"></div>
		<?php
	}
	
}
add_action('et_after_body', 'etheme_bordered_layout');

// **********************************************************************// 
// ! Hook photoswipe template to the footer
// **********************************************************************//
if( ! function_exists( 'etheme_photoswipe_template' ) ) {
	function etheme_photoswipe_template() {
		if ( !get_query_var('is_single_product', false) ) return;
		etheme_enqueue_style('photoswipe');
		get_template_part( 'templates/photoswipe' );
		return;
	}
}
add_action('after_page_wrapper', 'etheme_photoswipe_template', 30);

if ( !function_exists('et_notify') ) {
	function et_notify() { ?>
        <div class="et-notify pos-fixed top right" data-type=""></div>
	<?php }
}
add_action('after_page_wrapper', 'et_notify', 40);

if ( !function_exists('et_buffer') ) {
	function et_buffer() { ?>
        <div id="et-buffer"></div>
	<?php }
}
add_action('after_page_wrapper', 'et_buffer', 40);

// **********************************************************************//
// ! add action for etheme_prefooter
// **********************************************************************//
add_action( 'etheme_prefooter', 'etheme_prefooter_content', 10 );

function etheme_prefooter_content(){
	get_template_part( 'templates/footer/prefooter');
}

// **********************************************************************//
// ! add actions for etheme_footer
// **********************************************************************//
add_action( 'etheme_footer', 'etheme_footer_content', 10 );

function etheme_footer_content(){
	get_template_part( 'templates/footer/footer');
}

add_action( 'etheme_footer', 'etheme_copyrights_content', 20 );
function etheme_copyrights_content(){
	get_template_part( 'templates/footer/copyrights');
}

// **********************************************************************//
// ! etheme slider
// **********************************************************************//
if( ! function_exists( 'etheme_slider' ) ) {
	function etheme_slider( $args, $type = 'post', $atts = array() ) {
		// ! Slider args
		$slider_atts = array(
			'title'              => false,
			'before'             => '',
			'after'              => '',
			'wrapper_class'		 => '',
			'class'              => '',
			'attr'               => '',
			'echo'               => false,
			'large'              => 4,
			'notebook'           => 4,
			'tablet_land'        => 3,
			'tablet_portrait'    => 2,
			'mobile'             => 2,
			'slider_autoplay'    => 'no',
			'slider_speed'       => 300,
			'slider_interval'    => 3000,
			'slider_stop_on_hover' => false,
			'slider_loop'        => false,
            'slider_space' => false,
			'autoheight'         => true,
			'pagination_type'    => 'hide',
			'nav_color' 		 => '',
			'arrows_bg_color' 	 => '',
			'nav_color_hover' 		 => '',
			'arrows_bg_color_hover' 	 => '',
			'default_color'      => '#e6e6e6',
			'active_color'       => '#b3a089',
			'hide_fo'            => '',
			'hide_buttons'       => false,
			'navigation_type'    => 'arrow',
			'navigation_position_style' => 'arrows-hover',
			'navigation_style'	 => '',
			'navigation_position'=> 'middle',
			'hide_buttons_for'	 => '',
			'size'               => 'shop_catalog',
			'per_move'           => 1,
			// ! blog args
			'slide_view'         => '',
			'hide_img' => false,
			'blog_align'         => '',
			'blog_hover'		 => 'zoom',
			// ! Products args
			'block_id'           => false,
			'style'              => 'default',
			'product_view'       => '',
			'product_view_color' => '',
			'no_spacing'         => '',
			'shop_link'          => false,
			'slider_type'        => false,
			'from_first'         => '',
			'widget'             => false,
			'wrap_widget_items_in_div' => false,
			'elementor'		 	 => false,
			'is_preview'		 => false
		);
		
		extract( shortcode_atts( $slider_atts, $atts ) );
		
		add_filter('et_view-mode-grid', '__return_true');
		
		// fix for variation galleries
		if ( function_exists('remove_et_variation_gallery_filter'))
			remove_et_variation_gallery_filter('');
		
		$box_id      = rand( 1000, 10000 );
//		$variable_products_detach = etheme_get_option('variable_products_detach', false);
//		if ( $variable_products_detach ) {
//			$variable_products_no_parent = etheme_get_option('variation_product_parent_hidden', true);
//			$show_attributes = etheme_get_option('variation_product_name_attributes', true);
//			if ( $show_attributes ) {
//				add_filter( 'woocommerce_product_variation_title_include_attributes', '__return_true' );
//			}
//		    if ( $args['post_type'] == 'product' ) {
//		        $args['post_type'] = array('product', 'product_variation');
//            }
//		    elseif (is_array($args['post_type']) && in_array('product', $args['post_type'])) {
//			    $args['post_type'] = array('product', 'product_variation');
//            }
//			$posts_not_in = etheme_product_variations_excluded();
//			if ( array_key_exists('post__not_in', $args) ) {
//				$args['post__not_in'] = array_unique( array_merge((array)$args['post__not_in'], $posts_not_in) );
//			}
//			else {
//				$args['post__not_in'] = array_unique( $posts_not_in );
//			}
//			// hides all variable products
//			if ( $variable_products_no_parent ) {
//				$args['tax_query'][] = array(
//					array(
//						'taxonomy' => 'product_type',
//						'field'    => 'slug',
//						'terms'    => 'variable',
//						'operator' => 'NOT IN',
//					),
//				);
//			}
//        }
		$multislides = new WP_Query( $args );
		$loop = $slide_class = $html = '';
		
		if ( $slider_stop_on_hover )
			$class .= ' stop-on-hover';
		
		if ( $type == 'post' ) {
			global $et_loop;
			$et_loop['slider']      = true;
			$et_loop['blog_layout'] = 'default';
			$et_loop['size']        = $size;
			$et_loop['hide_img'] = $hide_img;
			$et_loop['blog_hover'] = $blog_hover;
			$et_loop['slide_view']  = $slide_view;
			$et_loop['blog_align']  = $blog_align;
			$class .= ' posts-slider';
		} else {
			if( ! class_exists( 'Woocommerce' ) ) return;
			global $woocommerce_loop;
			if ( !isset($woocommerce_loop['size']) || empty( $woocommerce_loop['size'] ) )
				$woocommerce_loop['size'] = $size;
			
			if( ! $slider_type ) {
				$woocommerce_loop['lazy-load'] = false;
				$woocommerce_loop['style'] = $style;
			}
			
			if( !empty($woocommerce_loop['product_view'])) {
				$product_view = $woocommerce_loop['product_view'];
			}
			else {
				$product_view = etheme_get_option('product_view', 'disable');
            }
			
			if( !empty($woocommerce_loop['custom_template'])) {
				$custom_template = $woocommerce_loop['custom_template'];
			}
			else {
				$custom_template = etheme_get_custom_product_template();
            }
			
			$block = '';
			$class .= ' products-slider';
			if ( !empty($woocommerce_loop['hover_shadow']) && $woocommerce_loop['hover_shadow'] )
				$class .= ' products-hover-shadow';
			
			$slide_class .= ' slide-item product-slide ';
			$slide_class .= $slider_type . '-slide';
			
			if( $no_spacing == 'yes' ) $slide_class .= ' item-no-space';
			
			if( $block_id && $block_id != '' && etheme_static_block( $block_id, false ) != '' ) {
				ob_start();
				echo '<div class="slide-item '.$slider_type.'-slide">';
				etheme_static_block($block_id, true);
				echo '</div><!-- slide-item -->';
				$block = ob_get_contents();
				ob_end_clean();
			}
		}
		
		if ( $multislides->have_posts() ) {
			if ( $type == 'post' ) {
				add_filter( 'excerpt_length', 'etheme_excerpt_length_sliders', 1000 );
			}
			
			if ( $type == 'product' ) {
			    $ajax = isset($woocommerce_loop['doing_ajax']) && $woocommerce_loop['doing_ajax'];
			    if ( !$ajax ) {
				    etheme_enqueue_style( 'woocommerce' );
				    etheme_enqueue_style( 'woocommerce-archive' );
				    if ( get_query_var( 'et_is-swatches', false ) ) {
					    etheme_enqueue_style( "swatches-style" );
				    }
				    if ( ! in_array( $product_view, array( 'disable', 'custom' ) ) ) {
					    etheme_enqueue_style( 'product-view-' . $product_view );
				    }
				
				    if ( $product_view == 'custom' || ! empty( $woocommerce_loop['custom_template'] ) ) {
					    etheme_enqueue_style( 'content-product-custom' );
				    }
				
//				    if ( get_query_var('et_is-quick-view', false) ) {
//					    etheme_enqueue_style( "quick-view" );
//					    if ( get_query_var('et_is-quick-view-type', 'popup') == 'off_canvas' ) {
//						    etheme_enqueue_style( "off-canvas" );
//					    }
//				    }
			    }
			}
			$autoheight = ( $autoheight ) ? 'data-autoheight="1"' : '';
			$lines = ( $pagination_type == 'lines' ) ? 'swiper-pagination-lines' : '';
			$slider_speed = ( $slider_speed ) ? 'data-speed="' . $slider_speed . '"' : '';
			
			if ( $slider_autoplay ) $slider_autoplay = $slider_interval;
			if ( $autoheight ) $autoheight = 'data-autoheight="1"';
			if ( $multislides->post_count <= $large ) {
				$slider_loop = false;
			}
			if ( $slider_loop ) $loop = ' data-loop="true"';
			
			$selectors = array();
			$selectors['slider'] = '.slider-'.$box_id;
			$selectors['pagination'] = $selectors['slider'] . ' .swiper-pagination-bullet';
			$selectors['pagination_active'] = $selectors['pagination'] . '-active,' . $selectors['pagination'] . ':hover';
			
			$selectors['navigation'] = $selectors['slider'] . ' ~ .swiper-button-prev,' . $selectors['slider'] . ' ~ .swiper-button-next';
			$selectors['navigation_active'] = $selectors['slider'] . ' ~ .swiper-button-prev:hover,' . $selectors['slider'] . ' ~ .swiper-button-next:hover';
			
			$output_css = array(
				'pagination' => array(),
				'navigation' => array()
			);
			
			$wrapper_class .= ' ' . $navigation_position;
			$wrapper_class .= ' ' . $navigation_position_style;
			
			$html .= '<div class="swiper-entry '.$wrapper_class.'">';
			$html .= $before;
			
			$html .= ( $title ) ? '<h3 class="title"><span>' . $title . '</span></h3>' : '';
			
			if ( $type == 'product' && $product_view == 'custom' && $custom_template != '' ) {
				$class  .= ' products-with-custom-template products-template-'.$custom_template;
				$attr .= ' data-post-id="'.$custom_template.'"';
			}
			
			// bug in mini-cart products
//			if ( $widget ) {
//				$class .= ' swiper-container-multirow';
//			}
			
			$elementor_nospacing = '';
			if ( true == $elementor && 'yes' == $no_spacing ) {
				$elementor_nospacing = 'data-space="0"';
			}
            if ( $slider_space && !$elementor_nospacing) {
                $elementor_nospacing .= ' data-space="'.$slider_space.'"';
            }
			
			$html .='
	                <div
	                    class="swiper-container carousel-area ' . $class . ' slider-' . $box_id . ' ' . $lines . '"
	                    '. $elementor_nospacing .'
	                    data-breakpoints="1"
	                    data-xs-slides="' . esc_js( $mobile ) . '"
	                    data-sm-slides="' . esc_js( $tablet_land ) . '"
	                    data-md-slides="' . esc_js( $notebook ) . '"
	                    data-lt-slides="' . esc_js( $large ) . '"
	                    data-slides-per-view="' . esc_js( $large ) . '"
	                    ' . $autoheight . '
	                    data-slides-per-group="' . esc_attr( $per_move ). '"
	                    data-autoplay="' . esc_attr( $slider_autoplay ) . '"
	                    ' . $slider_speed . ' ' . $loop . ' ' . $attr . '
	                >
	            ';
			
			$backup_style = get_query_var('is_mobile', false) ? $mobile : $large;
			$backup_style = 'style="width:'.(100/$backup_style).'%"';
			
			$swiper_slide_class_css = '.swiper-container.slider-'.$box_id.':not(.initialized) .swiper-slide';
			
			$media = $swiper_slide_class_css .' {width: '.(100/$mobile).'% !important;}';
			$media .= '@media only screen and (min-width: 640px) { '.$swiper_slide_class_css.' {width: '.(100/$tablet_land).'% !important;}}';
			$media .= '@media only screen and (min-width: 1024px) { '.$swiper_slide_class_css.' {width: '.(100/$notebook).'% !important;}}';
			$media .= '@media only screen and (min-width: 1370px) { '.$swiper_slide_class_css.' {width: '.(100/$large).'% !important;}}';
			
			wp_add_inline_style( 'xstore-inline-css', $media);
			
			$html .= '<div class="swiper-wrapper">';
			$_i=0;
			
			ob_start();
			
			while ( $multislides->have_posts() ) : $multislides->the_post();
				$_i++;
				
				if ( $type == 'product' ) {
					
					global $product;
					
					if( ( $from_first == 'no' && $_i ==  2) || ( $from_first != 'no' && $_i == 1 ) ) {
						echo $block; // All data escaped
					}

//		                        $product_type = $product->get_type();
//		                        if ( $variable_products_detach && $product_type == 'variation') {
//                                }
//		                        else
					if ( ! $product->is_visible() ) continue;
					
					if ( $widget ) {
						if ( $wrap_widget_items_in_div ) {
							$is_widget_slider = true;
							echo '<div class="swiper-slide ' . esc_attr( $slide_class ) . '">';
						}
						wc_get_template_part( 'content', 'widget-product-slider' );
						if ( $wrap_widget_items_in_div ) {
							unset($is_widget_slider);
							echo '</div>';
						}
						
					} else {
						echo '<div class="swiper-slide' . esc_attr( $slide_class ) . '" '.$backup_style.'>';
						wc_get_template_part( 'content', 'product-slider' );
						echo '</div>';
					}
					
				} else {
					echo '<div class="swiper-slide' . esc_attr( $slide_class ) . '" '.$backup_style.'>';
					get_template_part( 'content', 'grid' );
					echo '</div>';
				}
			
			endwhile;
			
			$html .= ob_get_clean();
			$html .= '</div><!-- slider wrapper-->';
			
			if ( $pagination_type != 'hide' ) {
				$pagination_class = '';
				if ( $hide_fo == 'desktop' )
					$pagination_class = ' dt-hide';
                elseif ( $hide_fo == 'mobile' )
					$pagination_class = ' mob-hide';
				
				$html .= '<div class="swiper-pagination '.$pagination_class.'"></div>';
				
				if ( !empty($default_color) ) {
					$output_css['pagination'][] = $selectors['pagination'] . ' { background-color: ' .$default_color . '; }';
				}
				
				if ( !empty($active_color) ) {
					$output_css['pagination'][] = $selectors['pagination_active'] . '{ background-color: ' .$active_color . '; }';
				}
				
				if ( count($output_css['pagination']) ) {
					
					if ( $is_preview ) {
						$html .= '<style>' . implode(' ', $output_css['pagination']) . '</style>';
					}
					else {
						wp_add_inline_style( 'xstore-inline-css', implode(' ', $output_css['pagination']) );
					}
				}
				
			}
			$html .= '</div><!-- slider container-->';
			
			if ( ! $hide_buttons || ( $hide_buttons && $hide_buttons_for != '' ) ) {
				$navigation_class = '';
				if ( $elementor == true ) {
					$navigation_class .= ' et-swiper-elementor-nav';
				}
				if ( $hide_buttons_for == 'desktop' )
					$navigation_class = ' dt-hide';
                elseif ( $hide_buttons_for == 'mobile' )
					$navigation_class = ' mob-hide';
				
				$navigation_class_left  = 'swiper-custom-left' . ' ' . $navigation_class;
				$navigation_class_right = 'swiper-custom-right' . ' ' . $navigation_class;
				
				$navigation_class_left .= ' type-' . $navigation_type . ' ' . $navigation_style;
				$navigation_class_right .= ' type-' . $navigation_type . ' ' . $navigation_style;
				
				if ( $navigation_position == 'bottom' )
					$html .= '<div class="swiper-navigation">';
				
				if ( false == $elementor || ( true == $elementor && 'middle' == $navigation_position || 'middle-inside' == $navigation_position ) ) {
					$html .= '
                		<div class="swiper-button-prev ' . $navigation_class_left . '"></div>
                		<div class="swiper-button-next ' . $navigation_class_right . '"></div>
                		';
				}
				
				if ( $navigation_position == 'bottom' )
					$html .= '</div>';
				
				if ( !empty($arrows_bg_color) ) {
					$output_css['navigation'][] = 'background-color: ' .$arrows_bg_color;
				}
				
				if ( !empty($nav_color) ) {
					$output_css['navigation'][] = 'color: ' .$nav_color;
				}
				
				if ( !empty($nav_color_hover) ) {
					$output_css['navigation_hover'][] = 'color: ' .$nav_color_hover;
                }
				
				if ( !empty($arrows_bg_color_hover) ) {
					$output_css['navigation_hover'][] = 'opacity: 1; background-color: ' .$arrows_bg_color_hover;
				}
				
				if ( count($output_css['navigation']) ) {
					
					$output_css['navigation'] = $selectors['navigation'] . '{' . implode(';', $output_css['navigation']) . '}';
					
					if ( $is_preview ) {
						$html .= '<style>' . $output_css['navigation'] . '</style>';
					}
					else {
						wp_add_inline_style( 'xstore-inline-css', $output_css['navigation'] );
					}
				}
				
				// wp_add_inline_style( 'xstore-inline-css',
				//     	'.slider-'.$box_id.' .swiper-button-prev, .slider-'.$box_id . ' .swiper-button-next {background-color:'.$arrows_bg_color.'; color: '. $nav_color .';}'
			}
			
			$html .= $after;
			$html .= '</div><div class="clear"></div><!-- slider-entry -->';
			if ( $type == 'post' ) {
				remove_filter( 'excerpt_length', 'etheme_excerpt_length_sliders', 1000 );
			}
		};
		
		// end fix for variation galleries
		if (function_exists('add_et_variation_gallery_filter'))
			add_et_variation_gallery_filter('');
		
		remove_filter('et_view-mode-grid', '__return_true');
		
		remove_filter( 'woocommerce_product_variation_title_include_attributes', '__return_true' );
		
		if ( $type == 'post' ) {
			unset( $et_loop );
			wp_reset_postdata();
		} else {
			wp_reset_query();
			unset( $woocommerce_loop['lazy-load'] );
			unset( $woocommerce_loop['style'] );
		}
		
		if ( $is_preview )
			$html .= '<script>jQuery(document).ready(function(){
                    etTheme.swiperFunc();
			        etTheme.secondInitSwipers();
			        etTheme.global_image_lazy();
			        if ( etTheme.contentProdImages !== undefined )
	                    etTheme.contentProdImages();
			        if ( etTheme.countdown !== undefined )
	                    etTheme.countdown();
			        etTheme.customCss();
			        etTheme.customCssOne();
			        if ( etTheme.reinitSwatches !== undefined )
	                    etTheme.reinitSwatches();
                });</script>';
		
		
		if ( $echo ) {
			echo $html; // All data escaped
		} else {
			return $html;
		}
	}
}

add_action( 'wp', 'etheme_modify_search_page', 9 );

function etheme_modify_search_page(){
	
	if ( ( isset( $_GET['et_result'] ) && $_GET['et_result'] == 'products' ) || ! is_search() || !class_exists('WooCommerce') ) {
		return;
	}
	
	$search_content = etheme_get_option( 'search_results_content_et-desktop',
		array(
			'products',
			'posts'
		)
	);
	
	$search_aditional = etheme_get_option('search_page_custom_area_position_et-desktop', 'none');
	
	if ( ! is_array( $search_content ) ) {
		return;
	}
	
	if ( isset($_GET['et_search']) && is_search() && $search_aditional != 'none' ) {
		
		$custom_area = etheme_get_option('search_page_custom_area', '');
		$search_section = etheme_get_option('search_page_sections', 0);
		if ( $search_section ) {
			
			$custom_area = etheme_static_block( etheme_get_option('search_page_section', '') );
		}
		
		if ( $search_aditional == 'before' ) {
			add_action('etheme_before_product_loop_start', function($out) use($custom_area){
				echo do_shortcode( $custom_area );
				return;
			}, 5);
		} else {
			add_action('etheme_after_product_loop_end', function($out) use($custom_area){
				echo do_shortcode( $custom_area );
				return;
			}, 15);
		}
	}
	
	if ( in_array('products', $search_content) && woocommerce_product_loop() ) {
		add_filter('theme_mod_ajax_product_pagination', '__return_false');
		add_action( 'etheme_before_product_loop_start', function($count){
			printf(
				'<h2 class="products-title"><span>%s </span><span>%s</span></h2>',
				$count,
				_nx( 'Product found', 'Products found', $count, 'Search results page - products found text', 'xstore' )
			);
		}, 20 );
	}
	
	
	
	$i = 10;
	foreach ( $search_content as $key => $value ) {
		if ( $value == 'products' && woocommerce_product_loop() ) {
			$i = 20;
		} elseif( isset($_GET['et_search']) && $value != 'products' ) {
			if ($i == 10) {
				if ( in_array($value, $search_content) ) {
					add_action('etheme_before_product_loop_start','etheme_' . $value . '_in_search_results', $key + 10);
				}
			} else {
				if ( in_array($value, $search_content) ) {
					add_action('etheme_after_product_loop_end','etheme_' . $value . '_in_search_results', $key + 10);
				}
			}
		}
	}
}

function etheme_pages_in_search_results(){
	if(!is_search()) return;
	global $post;
	
	?>
	<?php if( get_search_query() ) : ?>
		<?php
		$args = array(
			's'                   => get_search_query(),
			'post_type'           => 'page',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			// 'posts_per_page'      => 50,
			'orderby'             => '',
			// 'post_type'           => array(),
			// 'post_status'         => 'publish',
			'posts_per_page'      => 50,
			// 'ignore_sticky_posts' => 1,
			'post_password'       => '',
			// 'suppress_filters'    => false,
		);
		
		$posts = get_posts( $args );
		$box_id      = rand( 1000, 10000 );
		
		if ( count($posts) ) {
			remove_action('woocommerce_no_products_found', 'wc_no_products_found', 10);
			
			printf(
				'<h2 class="products-title"><span>%s </span><span>%s</span></h2>',
				count($posts),
				_nx( 'Page found', 'Pages found', count($posts), 'Search results page - pages found text', 'xstore' )
			);
			
			$mobile = 1;
			$tablet_land = 2;
			$notebook = 3;
			$large = 3;
			
			$backup_style = get_query_var('is_mobile', false) ? $mobile : $large;
			$backup_style = 'style="width:'.(100/$backup_style).'%"';
			
			$swiper_slide_class_css = '.swiper-container.slider-'.$box_id.':not(.initialized) .swiper-slide';
			
			$media = $swiper_slide_class_css .' {width: '.(100/$mobile).'% !important;}';
			$media .= '@media only screen and (min-width: 640px) { '.$swiper_slide_class_css.' {width: '.(100/$tablet_land).'% !important;}}';
			$media .= '@media only screen and (min-width: 1024px) { '.$swiper_slide_class_css.' {width: '.(100/$notebook).'% !important;}}';
			$media .= '@media only screen and (min-width: 1370px) { '.$swiper_slide_class_css.' {width: '.(100/$large).'% !important;}}';
			
			wp_add_inline_style( 'xstore-inline-css', $media);
			
			echo '<div class="swiper-entry pages-result-slider"><div
                        class="swiper-container posts-slider carousel-area slider-' . $box_id . '"
                        data-breakpoints="1"
                        data-xs-slides="'.esc_js($mobile).'"
                        data-sm-slides="'.esc_js($tablet_land).'"
                        data-md-slides="'.esc_js($notebook).'"
                        data-lt-slides="'.esc_js($large).'"
                        data-slides-per-view="'.esc_js($large).'"
                    >';
			echo '<div class="swiper-wrapper">';
			foreach ($posts as $key => $value) {
				
				$postClass      = etheme_post_class( 'grid' );
				$postClass[] = 'col-md-6';
				
				echo '<div class="swiper-slide" '.$backup_style.'>';
				?>
                <article <?php post_class( $postClass ); ?> id="post-<?php echo esc_attr( $value->ID ); ?>">
                    <div>
						
						<?php if ( ! empty( $et_loop['slide_view'] ) && $et_loop['slide_view'] == 'timeline2' ): ?>
                            <div class="meta-post-timeline">
                                <span class="time-day"><?php the_time('d'); ?></span>
                                <span class="time-mon"><?php the_time('M'); ?></span>
                            </div>
						<?php endif; ?>
						<?php
						//                                        $excerpt_length = etheme_get_option('excerpt_length', 25);
						etheme_post_thumb( array('size' => 'shop_catalog', 'in_slider' => true, 'ID' => $value->ID) );
						
						?>

                        <div class="grid-post-body">
                            <div class="post-heading">
                                <h2><a href="<?php the_permalink($value->ID); ?>"><?php echo esc_html( $value->post_title ); ?></a></h2>
								<?php if(etheme_get_option('blog_byline', 1)): ?>
									<?php etheme_byline( array( 'author' => 0, 'ID' => $value->ID, 'views_counter' => false, 'in_slider' => true ) );  ?>
								<?php endif; ?>
                            </div>

                            <div class="content-article">
								<?php etheme_read_more( get_the_permalink($value->ID), true ) ?>
                            </div>
                        </div>
                    </div>
                </article>
				
				<?php
				echo '</div>';
			}
			
			echo '</div><!-- slider wrapper-->';
			echo '</div>';
			echo '<div class="swiper-button-prev swiper-custom-left"></div>
	                    <div class="swiper-button-next swiper-custom-right"></div>';
			echo '</div><div class="clear"></div><!-- slider-entry -->';
		}
		?>
	<?php endif; ?>
	
	<?php
}

function etheme_portfolio_in_search_results(){
	if(!is_search()) return;
	global $post;
	
	?>
	<?php if( get_search_query() ) : ?>
		<?php
		$args = array(
			's'                   => get_search_query(),
			'post_type'           => 'etheme_portfolio',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			// 'posts_per_page'      => 50,
			'orderby'             => '',
			// 'post_type'           => array(),
			// 'post_status'         => 'publish',
			'posts_per_page'      => 50,
			// 'ignore_sticky_posts' => 1,
			'post_password'       => '',
			// 'suppress_filters'    => false,
		);
		
		$posts = get_posts( $args );
		$box_id      = rand( 1000, 10000 );
		
		if ( count($posts) ) {
			remove_action('woocommerce_no_products_found', 'wc_no_products_found', 10);
			
			printf(
				'<h2 class="products-title"><span>%s </span><span>%s</span></h2>',
				count($posts),
				_nx( 'Portfolio found', 'Portfolios found', count($posts), 'Search results page - portfolios found text', 'xstore' )
			);
			
			$mobile = 1;
			$tablet_land = 2;
			$notebook = 3;
			$large = 3;
			
			$backup_style = get_query_var('is_mobile', false) ? $mobile : $large;
			$backup_style = 'style="width:'.(100/$backup_style).'%"';
			
			$swiper_slide_class_css = '.swiper-container.slider-'.$box_id.':not(.initialized) .swiper-slide';
			
			$media = $swiper_slide_class_css .' {width: '.(100/$mobile).'% !important;}';
			$media .= '@media only screen and (min-width: 640px) { '.$swiper_slide_class_css.' {width: '.(100/$tablet_land).'% !important;}}';
			$media .= '@media only screen and (min-width: 1024px) { '.$swiper_slide_class_css.' {width: '.(100/$notebook).'% !important;}}';
			$media .= '@media only screen and (min-width: 1370px) { '.$swiper_slide_class_css.' {width: '.(100/$large).'% !important;}}';
			
			wp_add_inline_style( 'xstore-inline-css', $media);
			
			echo '<div class="swiper-entry portfolios-result-slider"><div
                        class="swiper-container posts-slider carousel-area slider-' . $box_id . '"
                        data-breakpoints="1"
                        data-xs-slides="'.esc_js($mobile).'"
                        data-sm-slides="'.esc_js($tablet_land).'"
                        data-md-slides="'.esc_js($notebook).'"
                        data-lt-slides="'.esc_js($large).'"
                        data-slides-per-view="'.esc_js($large).'"
                    >';
			echo '<div class="swiper-wrapper">';
			foreach ($posts as $key => $value) {
				
				$postClass      = etheme_post_class( 'grid' );
				$postClass[] = 'col-md-6';
				
				echo '<div class="swiper-slide" '.$backup_style.'>';
				?>
                <article <?php post_class( $postClass ); ?> id="post-<?php echo esc_attr( $value->ID ); ?>">
                    <div>
						
						<?php if ( ! empty( $et_loop['slide_view'] ) && $et_loop['slide_view'] == 'timeline2' ): ?>
                            <div class="meta-post-timeline">
                                <span class="time-day"><?php the_time('d'); ?></span>
                                <span class="time-mon"><?php the_time('M'); ?></span>
                            </div>
						<?php endif; ?>
						<?php
						//                                        $excerpt_length = etheme_get_option('excerpt_length' , 25);
						etheme_post_thumb( array('size' => 'shop_catalog', 'in_slider' => true, 'ID' => $value->ID) );
						
						?>

                        <div class="grid-post-body">
                            <div class="post-heading">
                                <h2><a href="<?php the_permalink($value->ID); ?>"><?php echo esc_html( $value->post_title ); ?></a></h2>
								<?php if(etheme_get_option('blog_byline', 1)): ?>
									<?php etheme_byline( array( 'author' => 0, 'ID' => $value->ID, 'views_counter' => false ) );  ?>
								<?php endif; ?>
                            </div>

                            <div class="content-article">
								<?php etheme_read_more( get_the_permalink($value->ID), true ) ?>
                            </div>
                        </div>
                    </div>
                </article>
				
				<?php
				echo '</div>';
			}
			
			echo '</div><!-- slider wrapper-->';
			echo '</div>';
			echo '<div class="swiper-button-prev swiper-custom-left"></div>
	                    <div class="swiper-button-next swiper-custom-right"></div>';
			echo '</div><div class="clear"></div><!-- slider-entry -->';
		}
		?>
	<?php endif; ?>
	
	<?php
}

function etheme_posts_in_search_results(){
	if(!is_search()) return;
	global $post;
	?>
	<?php if( get_search_query() ) : ?>
		<?php
		// wp_reset_postdata();
		$args = array(
			's'                   => get_search_query(),
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => 50,
		);
		
		$posts = get_posts( $args );
		$box_id      = rand( 1000, 10000 );
		
		// echo '<h4>' . esc_html__( 'Posts found', 'xstore' ) .  '</h4>';
		if ( count($posts) ) {
			remove_action('woocommerce_no_products_found', 'wc_no_products_found', 10);
			printf(
				'<h2 class="products-title"><span>%s </span><span>%s</span></h2>',
				count($posts),
				_nx( 'Post found', 'Posts found', count($posts), 'Search results page - posts found text', 'xstore' )
			);
			
			$mobile = 1;
			$tablet_land = 2;
			$notebook = 3;
			$large = 3;
			
			$backup_style = get_query_var('is_mobile', false) ? $mobile : $large;
			$backup_style = 'style="width:'.(100/$backup_style).'%"';
			
			$swiper_slide_class_css = '.swiper-container.slider-'.$box_id.':not(.initialized) .swiper-slide';
			
			$media = $swiper_slide_class_css .' {width: '.(100/$mobile).'% !important;}';
			$media .= '@media only screen and (min-width: 640px) { '.$swiper_slide_class_css.' {width: '.(100/$tablet_land).'% !important;}}';
			$media .= '@media only screen and (min-width: 1024px) { '.$swiper_slide_class_css.' {width: '.(100/$notebook).'% !important;}}';
			$media .= '@media only screen and (min-width: 1370px) { '.$swiper_slide_class_css.' {width: '.(100/$large).'% !important;}}';
			
			wp_add_inline_style( 'xstore-inline-css', $media);
			
			echo '<div class="swiper-entry posts-result-slider"><div
                        class="swiper-container posts-slider carousel-area slider-' . $box_id . '"
                        data-breakpoints="1"
                        data-xs-slides="'.esc_js($mobile).'"
                        data-sm-slides="'.esc_js($tablet_land).'"
                        data-md-slides="'.esc_js($notebook).'"
                        data-lt-slides="'.esc_js($large).'"
                        data-slides-per-view="'.esc_js($large).'"
                    >';
			echo '<div class="swiper-wrapper">';
			foreach ($posts as $key => $value) {
				
				$postClass      = etheme_post_class( 'grid' );
				
				$postClass[] = 'col-md-6';
				
				echo '<div class="swiper-slide" '.$backup_style.'>';
				?>
                <article <?php post_class( $postClass ); ?> id="post-<?php echo esc_attr( $value->ID ); ?>">
                    <div>
						
						<?php if ( ! empty( $et_loop['slide_view'] ) && $et_loop['slide_view'] == 'timeline2' ): ?>
                            <div class="meta-post-timeline">
                                <span class="time-day"><?php the_time('d'); ?></span>
                                <span class="time-mon"><?php the_time('M'); ?></span>
                            </div>
						<?php endif; ?>
						<?php
						$excerpt_length = etheme_get_option('excerpt_length', 25);
						etheme_post_thumb( array('size' => 'shop_catalog', 'in_slider' => true, 'ID' => $value->ID) );
						
						?>

                        <div class="grid-post-body">
                            <div class="post-heading">
                                <h2><a href="<?php the_permalink($value->ID); ?>"><?php echo esc_html( $value->post_title ); ?></a></h2>
								<?php if(etheme_get_option('blog_byline', 1)): ?>
									<?php etheme_byline( array( 'author' => 0, 'ID' => $value->ID, 'in_slider' => true ) );  ?>
								<?php endif; ?>
                            </div>

                            <div class="content-article">
								<?php if ( $excerpt_length > 0 ) {
									if ( strlen(get_the_excerpt($value->ID)) > 0 ) {
										$excerpt_length = apply_filters( 'excerpt_length', $excerpt_length );
										$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
										$text         = wp_trim_words( get_the_excerpt($value->ID), $excerpt_length, $excerpt_more );
										echo apply_filters( 'wp_trim_excerpt', $text, $text );
									}
									else {
										echo apply_filters( 'the_excerpt', get_the_excerpt($value->ID) );
									}
								}  ?>
								<?php etheme_read_more( get_the_permalink($value->ID), true ) ?>
                            </div>
                        </div>
                    </div>
                </article>
				
				<?php
				echo '</div>';
			}
			
			echo '</div><!-- slider wrapper-->';
			echo '</div>';
			echo '<div class="swiper-button-prev swiper-custom-left"></div>
	                    <div class="swiper-button-next swiper-custom-right"></div>';
			echo '</div><div class="clear"></div><!-- slider-entry -->';
		}
		// wp_reset_query();
		?>
	<?php endif; ?>
	
	<?php
}
