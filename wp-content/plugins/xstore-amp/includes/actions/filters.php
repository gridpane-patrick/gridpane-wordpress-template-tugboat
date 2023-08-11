<?php

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

class XStore_AMP_filters extends XStore_AMP {
	
	public function init_filters() {
		
		// avatar filter
		add_filter( 'get_avatar', array($this, 'get_avatar_filter'), 10, 5 );
		
		// global amp-images
		add_filter('wp_get_attachment_image', array($this, 'wp_get_attachment_image_filter'), 10, 5);
		
		// placeholder amp-image
		add_filter( 'woocommerce_placeholder_img', array($this, 'woocommerce_placeholder_img_filter'), 10, 3);
		
		// fixes after test on server
		add_filter('woocommerce_product_get_image', array($this, 'woocommerce_product_get_image_filter'), 10, 5);
		
		remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
		remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
		
		// make woocommerce_template_loop_product_thumbnail link
		add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 1);
		add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 20);
		
		// make title link
		add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 9);
		add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 11);
		
		// if enabled in options
		if ( array_search('product_page_cats', (array)get_theme_mod('product_page_switchers', array())) ) {
			add_filter( 'woocommerce_before_shop_loop_item_title', function () {
				global $product;
				$taxonomy = 'product_cat';
				add_filter( 'term_links-' . $taxonomy, array( $this, 'limit_product_taxonomy' ) );
				$items = get_the_term_list( $product->get_ID(), $taxonomy, '', ',', '' );
				if ( $items ) {
					echo '<div class="product-categories product-taxonomies">'.$items.'</div>';
				}
				remove_filter( 'term_links-' . $taxonomy, array( $this, 'limit_product_taxonomy' ) );
			}, 20 );
		}
		
		// if enabled in options
		if ( get_theme_mod('product_page_brands', false)) {
			add_filter( 'woocommerce_after_shop_loop_item_title', function () {
				global $product;
				$taxonomy = 'brand';
				add_filter( 'term_links-' . $taxonomy, array( $this, 'limit_product_taxonomy' ) );
				$items = get_the_term_list( $product->get_ID(), $taxonomy, '', ',', '' );
				if ( $items ) {
				    echo '<div class="product-brands product-taxonomies">'.$items.'</div>';
                }
				remove_filter( 'term_links-' . $taxonomy, array( $this, 'limit_product_taxonomy' ) );
			}, 7 );
		}
		
		// woocommerce tag cloud
		add_filter('woocommerce_product_tag_cloud_widget_args', function($args) {
			$args['smallest'] = 1;
			$args['unit'] = '';
			return $args;
		});
		
		// wp tagcloud
		add_filter('wp_generate_tag_cloud_data', function($args) {
			foreach ( $args as $key => $value ) {
				$value['font_size'] = '';
				$args[$key] = $value;
			}
			return $args;
		});
		
		add_filter('woocommerce_default_address_fields', array($this, 'woocommerce_default_address_fields_filter'));
		
		// single tabs
        add_filter('woocommerce_product_description_heading', '__return_false');
		add_filter('woocommerce_product_additional_information_heading', '__return_false');
		
        // widgets removing
//		add_filter( 'sidebars_widgets', array($this, 'sidebars_widgets_filter') );
		
		// shop archive loop
		$products_per_page = 12;
		add_filter( 'loop_shop_per_page', function () use ( $products_per_page ) {
			return $products_per_page;
		}, 50 );
		
//		add_filter('woocommerce_breadcrumb_defaults', function( $breadcrumbs ) {
//		    if ( wp_get_referer() ) {
//			    $breadcrumbs['wrap_after'] = '<a class="return" href="'.wp_get_referer().'">' . esc_html__( 'Return', 'xstore-amp' ) . '</a>' . $breadcrumbs['wrap_after'];
//		    }
//		    return $breadcrumbs;
//        });
		
//		add_filter( 'get_search_form', array($this, 'search_form') );
		
		add_action( 'wp', function () {
			add_filter('pre_option_etheme_single_product_builder', function() {
				return '';
			}, 99);
        }, 99);
		add_action( 'init', function () {
			add_filter( 'comments_template', array( $this, 'comments_template_loader' ) );
		});
        
        add_filter('woocommerce_checkout_registration_enabled', '__return_false');
        add_filter('pre_option_woocommerce_enable_checkout_login_reminder', function() {
            return 'no';
        }, 99);
		
		add_filter('pre_option_yith_woocompare_compare_button_in_product_page', function() {
			return 'no';
		}, 99);
		
		add_filter('pre_option_yith_woocompare_compare_button_in_products_list', function() {
			return 'no';
		}, 99);
		
		add_filter('pre_option_amp-options', function() {
			return array(
                'theme_support' => 'transitional'
            );
		}, 99);
		
        // reset yith wishlist position
		add_filter('pre_option_yith_wcwl_button_position', function() {
			return '';
		}, 99);
		add_filter('pre_option_yith_wcwl_loop_position', function() {
			return '';
		}, 99);
        
        add_filter('the_content', function($content){
            return parent::render_amp_content($content);
        });
        
        add_filter('etheme_instagram_content', function ($content){
            return parent::render_amp_content($content);
        });
        
        add_filter('etheme_widget_slider', '__return_false');
        
        add_filter('et_ajax_widgets', '__return_false');
        
        add_filter('woocommerce_gateway_icon', function ($content){
	        return parent::render_amp_content($content);
        });
		
	}
	
	/**
	 * Creates amp-img avatar.
	 *
	 * @param $avatar
	 * @param $id_or_email
	 * @param $size
	 * @param $default
	 * @param $alt
	 * @return false|string
	 *
	 * @since 1.0.0
	 *
	 */
	function get_avatar_filter( $avatar, $id_or_email, $size, $default, $alt ) {
		$size = ($size == 32) ? '64px' : $size;
		$image = get_avatar_url($id_or_email);
		ob_start(); ?>
		<amp-img src="<?php echo esc_url( $image ); ?>" width="<?php echo $size; ?>"
		         height="<?php echo $size; ?>"
		         alt="<?php echo $alt; ?>"
		         class="avatar avatar-<?php echo esc_attr($size); ?> photo"
		         layout="fixed">
		</amp-img>
		<?php
		return ob_get_clean();
	}
	
	/**
	 * Creates amp-img for most wp images.
	 *
	 * @param $html
	 * @param $attachment_id
	 * @param $size
	 * @param $icon
	 * @param $attr
	 * @return false|string
	 *
	 * @since 1.0.0
	 *
	 */
	public function wp_get_attachment_image_filter($html, $attachment_id, $size, $icon, $attr) {
		$force_ratio = apply_filters('xstore_amp_render_image_force_ratio', false);
		ob_start();
		parent::render_image(
		        array(
                    'image_id' => (int) $attachment_id,
                    'size' => $size,
                    'attr'=> $attr,
                    'force_ratio' => $force_ratio
                )
        );
		$html = ob_get_clean();
		return $html;
	}
	
	/**
	 * Creates amp-img for woocommerce placeholder.
	 *
	 * @param $image_html
	 * @param $size
	 * @param $dimensions
	 * @return false|string
	 *
	 * @since 1.0.0
	 *
	 */
	public function woocommerce_placeholder_img_filter($image_html, $size, $dimensions) {
		$placeholder_image = get_option( 'woocommerce_placeholder_image', 0 );
		$default_attr = array(
			'class' => 'woocommerce-placeholder wp-post-image',
			'alt'   => __( 'Placeholder', 'xstore-amp' ),
		);
		$attr = '';
		$attr = wp_parse_args( $attr, $default_attr );
		$force_ratio = apply_filters('xstore_amp_render_image_force_ratio', false);
		if ( wp_attachment_is_image( $placeholder_image ) ) {
            ob_start();
			    parent::render_image(
			            array(
                            'image_id' => (int) $placeholder_image,
                            'size' => $size,
                            'attr' => $attr,
                            'force_ratio' => $force_ratio
                        )
                );
			$image_html = ob_get_clean();
		} else {
			$image      = wc_placeholder_img_src( $size );
			$attributes = array();
			
			foreach ( $attr as $name => $value ) {
				$attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
			}
			
			if ( $force_ratio ) {
				$attributes[] = 'width="1"';
				$attributes[] = 'height="1"';
			}
			else {
				if ( isset( $dimensions['width'] ) ) {
					$attributes[] = 'width="' . $dimensions['width'] . '"';
				}
				
				if ( isset( $dimensions['height'] ) ) {
					$attributes[] = 'height="' . $dimensions['height'] . '"';
				}
			}
			
			ob_start(); ?>
			<amp-img src="<?php echo esc_url( $image ); ?>"
				<?php echo implode( ' ', $attributes ); ?>
                    layout="responsive">
			</amp-img>
			<?php $image_html = ob_get_clean();
		}
		return $image_html;
	}
	
	/**
	 * Creates amp-img for woocommerce images.
	 *
	 * @param $image
	 * @param $_this
	 * @param $size
	 * @param $attr
	 * @param $placeholder
	 * @return false|string
	 *
	 * @since 1.0.0
	 *
	 */
	public function woocommerce_product_get_image_filter ( $image, $_this, $size, $attr, $placeholder ) {
		$force_ratio = apply_filters('xstore_amp_render_image_force_ratio', false);
		ob_start();
		$image_id = get_post_thumbnail_id( $_this->get_id() );
		if ( wp_get_attachment_image_src( $image_id, $size ) ) {
			parent::render_image(
                array(
                    'image_id' => (int) $image_id,
                    'size' => $size,
                    'force_ratio' => $force_ratio
                )
            );
		}
		else { // placeholder most cases
            echo $this->woocommerce_placeholder_img_filter($image, $size, wc_get_image_size($size));
		}
		return ob_get_clean();
	}
	
	/**
	 * Limits product taxonomies with single result.
	 *
	 * @param $items
	 * @return array
	 *
	 * @since 1.0.2
	 *
	 */
	public static function limit_product_taxonomy($items) {
		if ( isset($items[0])) return array($items[0]);
		return $items;
	}
	
	/**
	 * Removes some attributes from woocommerce address fields.
	 *
	 * @param $fields
	 * @return mixed
	 *
	 * @since 1.0.0
	 *
	 */
	public static function woocommerce_default_address_fields_filter($fields) {
		foreach ( $fields as $field => $field_val) {
			unset($field_val['autocomplete']);
			$fields[$field] = $field_val;
		}
		return $fields;
	}
	
	/**
	 * Gets plugin's comments template.
	 *
	 * @param $template
	 * @return string
	 *
	 * @since 1.0.0
	 *
	 */
	public static function comments_template_loader( $template ) {
		if ( get_post_type() !== 'product' ) {
			return $template;
		}
		
		$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product-reviews.php';
		
		return $template;
	}
	
	/**
	 * Search form output.
	 *
	 * @param $form
	 * @return false|string
	 *
	 * @since 1.0.0
	 *
	 */
	public static function search_form( $form ) {
	    global $xstore_amp_vars;
		$action_url =  $xstore_amp_vars['is_woocommerce'] ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url();
		$action_url = preg_replace('#^https?:#', '', $action_url);
	    ob_start(); ?>
            <form action-xhr="<?php echo esc_url($action_url); ?>" action="<?php echo esc_url($action_url); ?>" role="searchform" method="get" target="_blank">
                <div class="input-row">
                    <input type="text" value="" placeholder="<?php esc_attr_e( 'Type here...', 'xstore-amp' ); ?>" autocomplete="off" class="form-control" name="s" />
                    <input type="hidden" name="post_type" value="product" />
                    <?php if ( defined( 'ICL_LANGUAGE_CODE' ) && ! defined( 'LOCO_LANG_DIR' ) ) : ?>
                        <input type="hidden" name="lang" value="<?php echo ICL_LANGUAGE_CODE; ?>"/>
                    <?php endif ?>
                    <button type="submit"><?php esc_html_e( 'Search', 'xstore-amp' ); ?></button>
                </div>
            </form>
		<?php return ob_get_clean();
	}
	
}

$XStore_AMP_filters = new XStore_AMP_filters();
$XStore_AMP_filters->init_filters();