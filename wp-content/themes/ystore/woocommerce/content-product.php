 <?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see     http://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

 // Ensure visibility
 if ( ! $product ) {
	 return;
 }
 
$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

 if ( $doing_ajax || !get_query_var('et_is-woocommerce-archive', false) ) {
    $variable_products_detach = etheme_get_option('variable_products_detach', false);
    $show_attributes = etheme_get_option('variation_product_name_attributes', true);
    
    $hover = etheme_get_option('product_img_hover', 'slider');
    $view = etheme_get_option('product_view', 'disable');
    $view_color = etheme_get_option('product_view_color', 'white');
    $just_catalog = etheme_is_catalog();
    $custom_template = etheme_get_option('custom_product_template', 'default');
    $show_excerpt = etheme_get_option('product_page_excerpt', 0);
    $excerpt_length = etheme_get_option('product_page_excerpt_length', 120);
    $product_settings = etheme_get_option('product_page_switchers', array('product_page_productname', 'product_page_cats', 'product_page_price','product_page_addtocart', 'product_page_productrating', 'hide_buttons_mobile'));
    $product_settings = !is_array( $product_settings ) ? array() : $product_settings;
    $product_new_label_range = etheme_get_option('product_new_label_range', 0);
    $product_title_limit_type = etheme_get_option('product_title_limit_type', 'chars');
    $product_title_limit = etheme_get_option('product_title_limit', 0);
    $product_has_quantity = etheme_get_option('product_page_smart_addtocart', 0);
    $view_mode = etheme_get_view_mode();
    $is_archive = is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || is_tax('brand') || is_post_type_archive( 'product' ) ||
                  ( defined('WC_PRODUCT_VENDORS_TAXONOMY') && is_tax( WC_PRODUCT_VENDORS_TAXONOMY ) ) || apply_filters('dokan_archive_page', false) || apply_filters('etheme_is_shop_page', false);
    $is_masonry = etheme_get_option( 'products_masonry', 0 );
    $show_quick_view = etheme_get_option('quick_view', 1);
}
 else {
    $variable_products_detach = get_query_var('et_product-variable-detach', false);
    $show_attributes = get_query_var('et_product-variable-name-attributes', true);
    $hover = get_query_var('et_product-hover', 'slider');
    $view = get_query_var('et_product-view', 'disable');
    $view_color = get_query_var('et_product-view-color', 'white');
    $just_catalog = get_query_var('et_is-catalog', false);
    $custom_template = get_query_var( 'et_custom_product_template' );
    $show_excerpt = get_query_var('et_product-excerpt', false);
    $excerpt_length = get_query_var('et_product-excerpt-length', 120);
    $product_settings = get_query_var('et_product-switchers', array());
    $product_new_label_range = get_query_var('et_product-new-label-range', 0);
    $product_title_limit_type = get_query_var('et_product-title-limit-type', 'chars');
    $product_title_limit = get_query_var('et_product-title-limit', 0);
    $product_has_quantity = get_query_var('et_product-with-quantity', 0);
    $view_mode = get_query_var('et_view-mode', 'grid');
	
    $is_archive = get_query_var('et_is-woocommerce-archive', false);
    $is_masonry = get_query_var('et_is-products-masonry', false);
    $show_quick_view = get_query_var('et_is-quick-view', false);
}


if ( get_theme_mod('product_variable_price_from', false)) {
 add_filter( 'woocommerce_format_price_range', function ( $price, $from, $to ) {
     return sprintf( '%s %s', esc_html__( 'From:', 'xstore' ), wc_price( $from ) );
 }, 10, 3 );
}

$size            = apply_filters( 'single_product_small_thumbnail_size', 'shop_catalog' );
$show_stock      = false;
$show_counter = false;
$with_new_label = false;
$product_type = $product->get_type();
$variation_product_detached = $variable_products_detach && $product_type == 'variation';
$atts = array();
$product_id = $product->get_ID();

if ( $product_new_label_range > 0 ) {
    $date_modified = $product->get_date_modified();
    $postdate        = get_the_modified_date( 'Y-m-d', $product->get_id() );
    $post_date_stamp = strtotime( $postdate );

    $with_new_label = ( time() - ( 60 * 60 * 24 * $product_new_label_range ) ) < $post_date_stamp;
}

if ( etheme_get_option( 'product_page_brands', 1 ) )
	$product_settings[] = 'product_page_brands';
$product_settings = apply_filters('etheme_product_content_settings', $product_settings);

 if ( isset( $woocommerce_loop['product_content_elements'] ) ) {
	 $product_settings = $woocommerce_loop['product_content_elements'];
	 if ( !in_array('product_page_swatches', $product_settings)) {
		 add_action('etheme_product_content_disable_swatches', '__return_true');
     }
 }
 
 if ( isset($woocommerce_loop['product_add_to_cart_quantity']) ) {
	 $product_has_quantity = $woocommerce_loop['product_add_to_cart_quantity'];
 }
 
// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ) {
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( get_query_var('et_cat-cols') ) {
	$woocommerce_loop['columns'] = apply_filters('loop_shop_columns', get_query_var('et_cat-cols'));
}
elseif (empty( $woocommerce_loop['columns'] )) {
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
}

if ( get_query_var('view_mode_smart', false) && !apply_filters( 'wc_loop_is_shortcode', wc_get_loop_prop( 'is_shortcode' ) ) ) {
	if ( isset( $_GET['et_columns-count'] ) ) {
		$woocommerce_loop['columns'] = $_GET['et_columns-count'];
	}
	else {
		$view_mode_smart_active = get_query_var('view_mode_smart_active', 4);
		$woocommerce_loop['columns'] = $view_mode_smart_active != 'list' ? $view_mode_smart_active : 4;
		$view_mode = $view_mode_smart_active == 'list' ? 'list' : $view_mode;
	}
}

if ( ! empty( $woocommerce_loop['show_counter'] ) ) {
	$show_counter = $woocommerce_loop['show_counter'];
}

if ( ! empty( $woocommerce_loop['hover'] ) )
	$hover = $woocommerce_loop['hover'];

if ( ! empty( $woocommerce_loop['product_view'] ) )
	$view = $woocommerce_loop['product_view'];

// it could help to fix issue for product elements with list mode and overlay hover but break some settings
// so if use product element with list view then select another hover type
// if ( ! empty( $woocommerce_loop['view_mode'] ) )
//     $view_mode = $woocommerce_loop['view_mode'];
 
if ( ! empty( $woocommerce_loop['product_view_color'] ) )
	$view_color = $woocommerce_loop['product_view_color'];

if ( ! empty( $woocommerce_loop['size'] ) )
	$size = $woocommerce_loop['size'];

if ( isset( $woocommerce_loop['show_excerpt'] ) )
	$show_excerpt = $woocommerce_loop['show_excerpt'];

if ( isset( $woocommerce_loop['excerpt_length'] ) )
	$excerpt_length = $woocommerce_loop['excerpt_length'];

if ( ! empty( $woocommerce_loop['custom_template'] ) )
	$custom_template = $woocommerce_loop['custom_template'];

if ( ! empty( $woocommerce_loop['show_stock'] ) )
	$show_stock = true;

$sale_counter = etheme_get_custom_field('sale_counter');
 if ( in_array($sale_counter, array('all', 'list', 'single_list')) ) {
  
     // include scripts/style in case we are on grid but could switch to list with ajax and make countdown work too
	 wp_enqueue_script( 'et_countdown');
//	 if ( function_exists('etheme_enqueue_style')) {
//		 if ( class_exists( 'WPBMap' ) ) {
//			 etheme_enqueue_style( 'wpb-et-timer' );
//		 } else {
//			 etheme_enqueue_style( 'et-timer' );
//		 }
//	 }
	 
     if ( $view_mode == 'list' || $sale_counter == 'all' )
	    $show_counter = true;
 }

// Use single product option
$single = etheme_get_custom_field('single_thumbnail_hover');
if ( $single && $single != 'inherit' ) $hover = $single;

$product_view = etheme_get_custom_field('product_view_hover');
if ( $product_view && $product_view != 'inherit' ) $view = $product_view;

$product_view_color = etheme_get_custom_field('product_view_color');
if ( $product_view_color && $product_view_color != 'inherit' ) $view_color = $product_view_color;

if ( $variation_product_detached ) {
}
elseif ( ! $product->is_visible() ) {
    return;
}

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if (!$woocommerce_loop['columns'] || $woocommerce_loop['columns'] == 'inherit') {
	$woocommerce_loop['columns'] = 4;
}
if ( 0 === ( $woocommerce_loop['loop'] - 1 ) % intval($woocommerce_loop['columns']) || 1 === $woocommerce_loop['columns'] ) {
	$classes[] = 'first';
	$classes[] = 'grid-sizer';
}
if ( 0 === $woocommerce_loop['loop'] % intval($woocommerce_loop['columns']) ) {
	$classes[] = 'last';
}

if(!class_exists('YITH_WCWL')) {
	$classes[] = 'wishlist-disabled';
}

$classes[] = etheme_get_product_class( $woocommerce_loop['columns'] );

if ( ! empty( $woocommerce_loop['isotope'] ) && $woocommerce_loop['isotope'] || $is_masonry && $is_archive ) {
	$classes[] = 'et-isotope-item';
}

if ( !in_array($view, array('mask3', 'mask', 'mask2', 'info', 'default', 'overlay') ) ) {
	$view_color = 'dark';
}

// if ( in_array($view, array('mask3', 'mask2', 'info') ) ) {
//     $hover = 'disabled';
// }

if ( in_array($view, array('overlay'))) {
	$hover = 'disabled';
}

if ( ! empty( $woocommerce_loop['loading_class']) ) {
	$classes[] = $woocommerce_loop['loading_class'];
}

if ( $view != 'custom' || !$custom_template ) {
	$classes[] = 'product-hover-' . $hover;
	$classes[] = 'product-view-' . $view;
	$classes[] = 'view-color-' . $view_color;
	if ( $hover == 'slider' ) $classes[] = 'arrows-hovered';
}

if ( in_array('product_page_addtocart', $product_settings) && ! $just_catalog ) {
	$classes[] = 'et_cart-on';
}
else {
	$classes[] = 'et_cart-off';
}
if ( ! in_array('hide_buttons_mobile', $product_settings) ) $classes[] = 'hide-hover-on-mobile';

$product_title = unicode_chars(get_the_title());
if ( $variation_product_detached ) {
	$product_title = unicode_chars( $product->get_name() );
}
$product_link = $product->get_permalink();

if ( $product_title_limit_type == 'chars' ) {
	if ( $product_title_limit && strlen( $product_title ) > $product_title_limit ) {
		$split         = preg_split( '/(?<!^)(?!$)/u', $product_title );
		$product_title = ( $product_title_limit != '' && $product_title_limit > 0 && ( count( $split ) >= $product_title_limit ) ) ? '' : $product_title;
		if ( $product_title == '' ) {
			for ( $i = 0; $i < $product_title_limit; $i ++ ) {
				$product_title .= $split[ $i ];
			}
			$product_title .= '...';
		}
	}
}

$excerpt = get_the_excerpt();
$excerpt_2 = '';
if ( $view_mode == 'grid' && $show_excerpt ) {
    if ( $variation_product_detached ) {
	    $custom_excerpt = $product->get_description();
	    if ( !empty($custom_excerpt)) {
		    $excerpt_2 = $custom_excerpt;
		    if ( $excerpt_length > 0 && strlen($excerpt_2) > 0 && ( strlen($excerpt_2) > $excerpt_length)) {
			    $excerpt_2         = substr($excerpt_2,0,$excerpt_length) . '...';
		    }
	    }
    }
	if ( $excerpt_length > 0 && strlen($excerpt) > 0 && ( strlen($excerpt) > $excerpt_length)) {
		$excerpt         = substr($excerpt,0,$excerpt_length) . '...';
	}
}

$product_type_quantity_types = apply_filters('etheme_product_type_show_quantity', array('simple', 'variable', 'variation'));

$with_quantity = false;

if (
	$product_has_quantity
	&& in_array('product_page_addtocart', $product_settings)
	&& in_array( $view, array('default', 'mask3', 'mask', 'mask2', 'overlay') )
	&& in_array($product_type, $product_type_quantity_types)
	&& !$just_catalog
	&& $product->is_in_stock()
){
	$with_quantity = true;
}


if ( $just_catalog ) {
	etheme_before_fix_just_catalog_link();
}

if ( !in_array('product_page_productrating', $product_settings ) ) {
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
}

add_filter( 'single_product_archive_thumbnail_size', function($old_size) use ($size){
	return $size;
} );

$hover = ($hover == 'swap' && get_query_var('is_mobile', false)) ? 'none' : $hover;
if ( $just_catalog && etheme_get_option( 'just_catalog_price', 0 ) && etheme_get_option( 'ltv_price', esc_html__( 'Login to view price', 'xstore' ) ) == '' ){
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
}

?>
	<div <?php wc_product_class( $classes, $product ); ?>>
        <?php
            // in case if custom product view specified for this product // load in this place to prevent css nth-child on grid
            if ( !in_array($view, array('disable', 'custom')) ) {
                etheme_enqueue_style('product-view-'.$view );
            }
        if ( in_array($sale_counter, array('all', 'list', 'single_list')) ) {
	
	        // include scripts/style in case we are on grid but could switch to list with ajax and make countdown work too
//            wp_enqueue_script( 'et_countdown');
	        if ( function_exists( 'etheme_enqueue_style' ) ) {
		        if ( class_exists( 'WPBMap' ) ) {
			        etheme_enqueue_style( 'wpb-et-timer' );
		        } else {
			        etheme_enqueue_style( 'et-timer' );
		        }
	        }
        }
        ?>
		<div class="content-product <?php if ($view == 'custom' && $custom_template != '') echo 'custom-template clearfix et-product-template-'.$custom_template; ?>">
			<?php // etheme_loader(); ?>
			
			<?php if ( $view == 'custom' && $custom_template != '' ) {
				
				$args = array( 'include' => $custom_template,'post_type' => 'vc_grid_item', 'posts_per_page' => 1);
				$myposts = get_posts( $args );
				
				if ( is_array($myposts) && isset($myposts[0]) && class_exists('ET_product_templates')) {
					$block = $myposts[0];
					
					$templates = new ET_product_templates();
					
					$content = $block->post_content;
					$templates->setTemplateById($custom_template);
					// $templates->setTemplateById($content, $custom_template);
					$shortcodes = $templates->shortcodes();
					$templates->mapShortcodes();
					WPBMap::addAllMappedShortcodes();
					
					$attr = ' width="' . $templates->gridAttribute( 'element_width', 12 ) . '"'
					        . ' is_end="' . ( 'true' === $templates->isEnd() ? 'true' : '' ) . '"';
					$content = preg_replace( '/(\[(\[?)vc_gitem\b)/', '$1' . $attr, $content );
					echo ( 1 === ( $woocommerce_loop['loop'] - 1 ) ) ? $templates->addShortcodesCustomCss($custom_template) : '';
					
					echo $templates->renderItem( get_post( (int) $product_id ), $content); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				else {
					echo '<div class="woocommerce-info">' . sprintf( esc_html__('To use this element you have to create custom product template via %1$1s. For more details %2$2s.', 'xstore'), '<a href="https://kb.wpbakery.com/docs/learning-more/grid-builder/" rel="nofollow" target="_blank">' . esc_html__('WPBakery page builder', 'xstore') . '</a>', '<a href="https://youtu.be/ER2njPVmsnk" rel="nofollow" target="_blank">' . esc_html__('Watch the tutorial', 'xstore') . '</a>' ) . '</div>';
				}
			}
			else {
				
				/**
				 * woocommerce_before_shop_loop_item hook.
				 *
				 * @hooked woocommerce_template_loop_product_link_open - 10
				 */
				if ( $view_mode == 'grid' && $view != 'booking' ) {
					remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
					do_action( 'woocommerce_before_shop_loop_item' );
					add_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
				}

                if ( $with_new_label ) {
                    ?>
                    <div class="sale-wrapper">
                        <span class="onsale left new-label"><?php echo esc_html__('New', 'xstore'); ?></span>
                    </div>
                    <?php
                }
				
				?>
				
				<div class="product-image-wrapper hover-effect-<?php echo esc_attr( $hover ); ?>">
					
					<?php if ( $view == 'overlay' ): ?>
                        <div class="quick-buttons">
							<?php
							if ( in_array('product_page_addtocart', $product_settings) && ! $just_catalog ) {
								add_filter( 'woocommerce_product_add_to_cart_text', '__return_false' );
								do_action( 'woocommerce_after_shop_loop_item' );
								remove_filter( 'woocommerce_product_add_to_cart_text', '__return_false' );
							}
							echo etheme_wishlist_btn(); ?>
                        </div>
                        <?php if ($show_quick_view) { ?>
                            <span class="show-quickly" data-prodid="<?php echo esc_attr($product_id);?>"><?php esc_html_e('Quick View', 'xstore') ?></span>
                        <?php }
						endif; ?>
					<?php if ( in_array($view, array('default')) ) echo etheme_wishlist_btn();
					
					if ( $view != 'booking' ) etheme_product_availability();
					if ( $hover == 'slider' ) echo '<div class="images-slider-wrapper">'; ?>
                        <a class="product-content-image" href="<?php echo esc_url($product_link); ?>" data-images="<?php echo etheme_get_image_list( $size ); ?>">
                            <?php if ( $view_mode == 'list' || $view == 'booking' ) {
                                if ($view == 'booking') {
                                    remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
                                }
                                do_action( 'woocommerce_before_shop_loop_item' );
                            }
                            if ( $view == 'booking' ) etheme_product_availability();
                            if( $hover == 'swap' ) etheme_get_second_image( $size );
                            // echo woocommerce_get_product_thumbnail( $size );
                            
                            /**
                             * woocommerce_before_shop_loop_item_title hook.
                             *
                             * @hooked woocommerce_show_product_loop_sale_flash - 10
                             * @hooked woocommerce_template_loop_product_thumbnail - 10
                             */
                            do_action( 'woocommerce_before_shop_loop_item_title' );
                            ?>
                        </a>
					<?php if ( $hover == 'slider' ) echo '</div>';
					
					if ( $view == 'booking' && $view_mode != 'list' && in_array('product_page_productname', $product_settings) ): ?>
						<h2 class="product-title">
							<a href="<?php echo esc_url($product_link); ?>"><?php echo wp_specialchars_decode($product_title); ?></a>
						</h2>
					<?php endif;
					
					if ($view == 'info'): ?>
						<div class="product-mask">
							
							<?php if (in_array('product_page_productname', $product_settings)): ?>
								<h2 class="product-title">
									<a href="<?php echo esc_url($product_link); ?>"><?php echo wp_specialchars_decode($product_title); ?></a>
								</h2>
							<?php endif;
							
							
							/**
							 * woocommerce_after_shop_loop_item_title hook
							 *
							 * @hooked woocommerce_template_loop_rating - 5
							 * @hooked woocommerce_template_loop_price - 10
							 */
							if (in_array('product_page_price', $product_settings)) {
								do_action( 'woocommerce_after_shop_loop_item_title' );
							}
							?>
						</div>
					<?php endif;
					
					if ( $view == 'booking' && $view_mode != 'list' ):
                        if ( in_array('product_page_price', $product_settings) ) do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
						<div class="product-excerpt">
							<?php echo do_shortcode($excerpt); ?>
						</div>
                        <?php if ( $excerpt_2 != '') { ?>
                            <div class="product-excerpt">
                                <?php echo do_shortcode($excerpt_2); ?>
                            </div>
                        <?php } ?>
						<div class="product-excerpt">
							<?php do_action( 'woocommerce_product_additional_information', $product ); ?>
						</div>
						<?php
						if (in_array('product_page_addtocart', $product_settings) && $view != 'booking' && !$just_catalog ) {
							do_action( 'woocommerce_after_shop_loop_item' );
						}
                        endif;
					
					if ( in_array($view, array('mask', 'mask2', 'mask3', 'default', 'info') ) ): ?>
						<footer class="footer-product">
							<?php if ( $view == 'mask3' ):
								echo etheme_wishlist_btn();
							else:
								if ($show_quick_view): ?>
									<span class="show-quickly" data-prodid="<?php echo esc_attr($product_id);?>"><?php esc_html_e('Quick View', 'xstore') ?></span>
								<?php endif;
							endif;
							
							if ( !in_array($view, array('default', 'overlay')) ) {
								//if (in_array('product_page_addtocart', $product_settings)) {
								remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
								add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
								do_action( 'woocommerce_after_shop_loop_item' );
								//}
							}
							if ( $view == 'mask3' ):
                                if ($show_quick_view): ?>
									<span class="show-quickly" data-prodid="<?php echo esc_attr($product_id);?>"><?php esc_html_e('Quick View', 'xstore') ?></span>
                                <?php endif;
                                
                            elseif (!in_array($view, array('default'))):
                                echo etheme_wishlist_btn();
                                
							endif; ?>
						</footer>
					<?php endif ?>
				</div>
				
				<?php if ( ( !in_array($view, array('info', 'booking')) || $view_mode == 'list' ) ):
     
					if ( $with_quantity ) {
						remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
						add_filter('woocommerce_product_add_to_cart_text', '__return_false');
						remove_action( 'woocommerce_before_quantity_input_field', 'et_quantity_minus_icon' );
						remove_action( 'woocommerce_after_quantity_input_field', 'et_quantity_plus_icon' );
						add_action( 'woocommerce_before_quantity_input_field', 'et_quantity_minus_icon' );
						add_action( 'woocommerce_after_quantity_input_field', 'et_quantity_plus_icon' );
					} ?>
     
					<div class="<?php if ( $view != 'light' ) : ?>text-center <?php endif; ?>product-details"><?php do_action( 'et_before_shop_loop_title' );
						
						if ( $view == 'light' ) echo '<div class="light-left-side">';
						
						if (in_array('product_page_cats', $product_settings)):
							etheme_product_cats();
						endif;
						
						if (in_array('product_page_productname', $product_settings)): ?>
							<h2 class="product-title">
								<a href="<?php echo esc_url($product_link); ?>"><?php echo wp_specialchars_decode($product_title); ?></a>
							</h2>
						<?php endif;
						
						etheme_dokan_seller();
						
						if ( etheme_get_option( 'enable_brands', 1 ) && in_array('product_page_brands', $product_settings) ) :
							etheme_product_brands();
						endif;
						
						if (in_array('product_page_product_sku', $product_settings) &&
						          wc_product_sku_enabled() && ( $product->get_sku() || $product_type == 'variable' )): ?>
							<span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'xstore' ); ?>
                        <span class="sku"><?php echo esc_html( ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'xstore' ) ); ?></span>
                    </span>
						<?php endif;
						
						/**
						 * woocommerce_after_shop_loop_item_title hook
						 *
						 * @hooked woocommerce_template_loop_rating - 5
						 * @hooked woocommerce_template_loop_price - 10
						 */
						
						if ( in_array('product_page_price', $product_settings) ) :
							if ( $view != 'light' ) :
								do_action( 'woocommerce_after_shop_loop_item_title' );
								do_action( 'et_after_shop_loop_title' );
								
                            else :
								if ( in_array('product_page_productrating', $product_settings ) ) {
                                    woocommerce_template_loop_rating();
								}
								do_action( 'et_after_shop_loop_title' );
								if ( $view_mode != 'list'  ): ?>
								<div class="switcher-wrapper">
									<div class="price-switcher">
										<div class="price-switch">
											<?php
											remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
											do_action( 'woocommerce_after_shop_loop_item_title' );
											add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
											?>
										</div>
										<div class="button-switch">
											<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
										</div>
									</div>
								</div>
							<?php endif;
							endif;
						endif;
						if ( $view_mode == 'list' ): ?>
							<div class="product-excerpt">
								<?php echo do_shortcode($excerpt); ?>
							</div>
							<?php if ( $excerpt_2 != '') { ?>
                                <div class="product-excerpt">
									<?php echo do_shortcode($excerpt_2); ?>
                                </div>
							<?php } ?>
						<?php endif ?>
						
						<?php if ( $view == 'light' && $view_mode == 'list' ) : ?>
							<div class="switcher-wrapper">
								<div class="price-switcher">
									<div class="price-switch">
										<?php woocommerce_template_loop_price(); ?>
									</div>
									<div class="button-switch">
										<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
									</div>
								</div>
							</div>
						<?php endif;
						
						
						if ( $show_stock && 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
//							$id = $product->get_ID(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
//							$product = wc_get_product($product_id);
							echo et_product_stock_line($product);
						}
						
						if( $show_counter ) etheme_product_countdown();
					
                        if ( $view_mode == 'grid' && $show_excerpt ): ?>
                            <div class="product-excerpt">
                                <?php echo do_shortcode($excerpt); ?>
                            </div>
                            <?php if ( $excerpt_2 != '') { ?>
                                <div class="product-excerpt">
                                    <?php echo do_shortcode($excerpt_2); ?>
                                </div>
                            <?php }
                        
                        endif;
						
						if ( in_array('product_page_addtocart', $product_settings) && ! $just_catalog ) {
							if ( ! in_array( $view, array( 'mask', 'mask3', 'light', 'overlay' ) ) ) {
								do_action( 'woocommerce_after_shop_loop_item' );
							}
							if ( $with_quantity && ! ($product_type == 'variable' && etheme_get_option( 'swatch_layout_shop', 'before' ) == 'popup') ) {
								echo '<div class="quantity-wrapper">';
								woocommerce_quantity_input( array(), $product, true );
								woocommerce_template_loop_add_to_cart();
								echo '</div>';
							}
						}
						
						if ( $view == 'light' ) echo '</div><!-- .light-left-side -->'; ?>

                        <?php if ( $view == 'light' ) : ?>
                            <div class="light-right-side">
                                <?php if ($show_quick_view): ?>
                                    <span class="show-quickly" data-prodid="<?php echo esc_attr($product_id);?>"><?php esc_html_e('Quick View', 'xstore') ?></span>
                                <?php endif;
                                
                                echo etheme_wishlist_btn(); ?>
                            </div><!-- .light-right-side -->
                        <?php endif; ?>
                    
                    </div><?php // .product-details ?>
        
					<?php
					if ( $with_quantity ) {
						add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
						remove_filter('woocommerce_product_add_to_cart_text', '__return_false');
					} ?>
				<?php endif;
			} // end if not custom template ?>
		</div><!-- .content-product -->
	</div>

<?php if ( $just_catalog ) {
	etheme_after_fix_just_catalog_link();
}

if ( !in_array('product_page_productrating', $product_settings ) ) {
	add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
}

remove_action('etheme_product_content_disable_swatches', '__return_true');