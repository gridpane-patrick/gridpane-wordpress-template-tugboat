<?php

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

class XStore_AMP_actions extends XStore_AMP {
	
	public function __construct() {
	    
	    
	    global $xstore_amp_settings;
		
	    // new
		add_action('init', function () {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
		}, 999);
		
		add_action('init', array($this, 'filter_templates'));
		
		add_action( 'wp', array($this, 'search_page'), 9 );
		
		add_action('template_redirect', array($this, 'set_recently_viewed'), 20);
		
		// woocommerce
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'update_cart_action' ), 20 );
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'checkout_action' ), 20 );
		remove_action( 'template_redirect', array( 'WC_Form_Handler', 'save_address' ) );
		remove_action( 'template_redirect', array( 'WC_Form_Handler', 'save_account_details' ) );
		
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'process_login' ), 20 );
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'process_registration' ), 20 );
		
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'process_lost_password' ), 20 );
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'process_reset_password' ), 20 );
		
		add_action( 'wp_loaded', array( $this, 'update_cart_action' ), 20 );
		
		add_action( 'wp_loaded', array( $this, 'variation_add_to_cart_request' ) );
		
		remove_action('woocommerce_after_single_product', 'etheme_sticky_add_to_cart', 1);
		add_action( 'wp', function () {
			remove_action( 'woocommerce_share', 'etheme_product_single_sharing', 20 );
		}, 99);
		
		// cart page
		remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
		add_action('woocommerce_after_cart', 'woocommerce_cross_sell_display');
		
		// cart empty
		remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
		
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		
		add_action('init', function() {
			// mini-cart
			add_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
			
		    // remove add to cart on shop
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			
			// remove breadcrumbs
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
			
			// add breadcrumbs in own position
			add_action('xstore_amp_before_main_content', 'woocommerce_breadcrumb', 10);
			
			// dokan registration
			remove_action( 'woocommerce_register_form', 'dokan_seller_reg_form_fields' );
		});
		
		// category images
		remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
		add_action( 'woocommerce_before_subcategory_title', array($this, 'etheme_woocommerce_subcategory_thumbnail'), 10 );
		
		// head metadata
        // use woocommerce data if class exists and otherwise use own
        if ( class_exists('WC_Structured_Data') ) {
            $WC_Structured_Data = new WC_Structured_Data();
	        add_action( 'xstore_amp_after_footer', array( $WC_Structured_Data, 'output_structured_data' ) );
        }
        else {
	        add_action( 'xstore_amp_after_footer', array( $this, 'print_schemaorg_metadata' ) );
        }
		
		// woocommerce template overwrite action
		add_action('pre_amp_render_post', array($this, 'woocommerce_template_overwrite'), 10);
		
		// header sidebars
		add_action('xstore_amp_after_header', array($this, 'mobile_menu_sidebar'));
		
		// cart sidebar
		add_action('xstore_amp_after_header', array($this, 'cart_sidebar'));
		
		// if search
		if ( !isset($xstore_amp_settings['general']['header_search']) || $xstore_amp_settings['general']['header_search'] ) {
			parent::add_custom_css_action( 'search', 'base' );
			parent::add_custom_css_action( 'tagcloud', 'widgets' );
			parent::add_custom_css_action( 'carousel', 'base' );
			// for form loader
			parent::add_custom_css_action( 'rotate', 'animations' );
			global $xstore_amp_scripts, $xstore_amp_scripts_templates;
			$xstore_amp_scripts['form']               = 'amp-form-0.1';
			$xstore_amp_scripts['list']               = 'amp-list-0.1';
			$xstore_amp_scripts['carousel'] = 'amp-carousel-0.2';
			$xstore_amp_scripts_templates['mustache'] = 'amp-mustache-0.2';
			add_action( 'xstore_amp_after_header', array( $this, 'search_form' ) );
		}
		
		// if back top
		if ( !isset($xstore_amp_settings['general']['back_top']) || $xstore_amp_settings['general']['back_top'] ) {
			parent::add_custom_css_action( 'backTop', 'base' );
			add_action( 'xstore_amp_after_header', array( $this, 'backTopAnchor' ) );
			add_action( 'xstore_amp_after_footer', array( $this, 'backTop' ), 99 );
		}
		
		// if mobile panel
		if ( !isset($xstore_amp_settings['mobile_panel']['mobile_panel']) || $xstore_amp_settings['mobile_panel']['mobile_panel'] ) {
            parent::add_custom_css_action( 'mobilePanel-config', 'base', 'php' );
            parent::add_custom_css_action( 'mobilePanel', 'base' );
			
            add_action( 'xstore_amp_after_footer', array( $this, 'mobilePanel' ), 10 );
		}
		
		// if dark/light switcher
		if ( isset($xstore_amp_settings['general']['dark_light_switcher']) && $xstore_amp_settings['general']['dark_light_switcher'] ) {
			global $xstore_amp_scripts;
			$xstore_amp_scripts['form'] = 'amp-form-0.1';
			parent::add_custom_css_action( 'darkLightSwitcher-Config', 'extra', 'php' );
			parent::add_custom_css_action( 'darkLightSwitcher', 'extra' );
			add_action( 'xstore_amp_after_footer', array( $this, 'darkLightSwitcher' ), 20 );
		}
		
		if ( (isset($xstore_amp_settings['advanced']['google_analytics']) && $xstore_amp_settings['advanced']['google_analytics']) &&
		     isset($xstore_amp_settings['advanced']['gtag_id']) && $xstore_amp_settings['advanced']['gtag_id'] != '' ) {
			global $xstore_amp_scripts;
			$xstore_amp_scripts['analytics'] = 'amp-analytics-0.1';
			add_action( 'xstore_amp_after_footer', array( $this, 'google_analytics' ), 30 );
        }
	}
    
	/**
	 * Function to update cart with AMP-form actions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function update_cart_action() {
		wc_nocache_headers();
		
		$nonce_value = wc_get_var( $_REQUEST['woocommerce-cart-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.
		
		if ( isset( $_GET['remove_coupon'] ) ) {
			WC()->cart->remove_coupon( wc_format_coupon_code( urldecode( wp_unslash( $_GET['remove_coupon'] ) ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$passed_validation = true;
			$no_changes = false;
		} elseif ( ! empty( $_GET['remove_item'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-cart' ) ) {
			$cart_item_key = sanitize_text_field( wp_unslash( $_GET['remove_item'] ) );
			$cart_item     = WC()->cart->get_cart_item( $cart_item_key );
			
			if ( $cart_item ) {
				WC()->cart->remove_cart_item( $cart_item_key );
				
				$product = wc_get_product( $cart_item['product_id'] );
				
				/* translators: %s: Item name. */
				$item_removed_title = apply_filters( 'woocommerce_cart_item_removed_title', $product ? sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'xstore-amp' ), $product->get_name() ) : __( 'Item', 'xstore-amp' ), $cart_item );
				
				// Don't show undo link if removed item is out of stock.
				if ( $product && $product->is_in_stock() && $product->has_enough_stock( $cart_item['quantity'] ) ) {
					/* Translators: %s Product title. */
					$removed_notice  = sprintf( __( '%s removed.', 'xstore-amp' ), $item_removed_title );
					$removed_notice .= ' <a href="' . esc_url( wc_get_cart_undo_url( $cart_item_key ) ) . '" class="restore-item">' . __( 'Undo?', 'xstore-amp' ) . '</a>';
				} else {
					/* Translators: %s Product title. */
					$removed_notice = sprintf( __( '%s removed.', 'xstore-amp' ), $item_removed_title );
				}
				
				wc_add_notice( $removed_notice, apply_filters( 'woocommerce_cart_item_removed_notice_type', 'success' ) );
			}
			
			$referer = wp_get_referer() ? remove_query_arg( array( 'remove_item', 'add-to-cart', 'added-to-cart', 'order_again', '_wpnonce' ), add_query_arg( 'removed_item', '1', wp_get_referer() ) ) : wc_get_cart_url();
			wp_safe_redirect( $referer );
			exit;
			$passed_validation = true;
			$no_changes = false;
			
		} elseif ( ! empty( $_GET['undo_item'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-cart' ) ) {
			
			// Undo Cart Item.
			$cart_item_key = sanitize_text_field( wp_unslash( $_GET['undo_item'] ) );
			
			WC()->cart->restore_cart_item( $cart_item_key );
			
			$referer = wp_get_referer() ? remove_query_arg( array( 'undo_item', '_wpnonce' ), wp_get_referer() ) : wc_get_cart_url();
			wp_safe_redirect( $referer );
			exit;
			
		}
	}
	
	/**
	 * Function for filtering templates/tempate_parts and include amp-templates/amp-template_parts instead of them.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function filter_templates() {
		add_filter('amp_post_template_file', array($this, 'amp_post_filter_template_file'), 3, 10);
		add_filter('amp_post_template_parts', array($this, 'amp_post_filter_template_parts'), 3, 10);
	}
	
	/**
	 * Filters page template.
	 *
	 * @param $file
	 * @param $post_id
	 * @param $post
	 * @return string
	 *
	 * @since 1.0.0
	 *
	 */
	public function amp_post_filter_template_file($file, $post_id, $post) {
		global $xstore_amp_scripts;
		global $xstore_amp_scripts_templates;
		global $xstore_amp_vars;
		
		if ( $xstore_amp_vars['is_woocommerce'] ) {
			parent::add_custom_css_action( 'global', 'woocommerce' );
			parent::add_custom_css_action( 'star-rating', 'woocommerce' );
		}
		if ( is_search() ) {
			parent::add_custom_css_action( 'carousel', 'base' );
			
			parent::add_custom_css_action( 'global', 'blog' );
			parent::add_custom_css_action( 'carousel-posts', 'blog' );
			parent::add_custom_css_action( 'archive', 'woocommerce' );
			parent::add_custom_css_action( 'navigation', 'woocommerce' );
			
			parent::add_custom_css_action('search-page', 'base');
			
			$xstore_amp_scripts['carousel'] = 'amp-carousel-0.2';
			$xstore_amp_scripts['form'] = 'amp-form-0.1';
//			add_filter('woocommerce_show_page_title', '__return_false');
			$file = XStore_AMP_TEMPLATES_PATH . 'woocommerce/archive-product.php';
        }
//		if ( $xstore_amp_vars['is_woocommerce'] && is_archive() ) {
        elseif ( $xstore_amp_vars['is_woocommerce'] && ( is_product_taxonomy() || is_post_type_archive( 'product' )
                                                     || is_page( wc_get_page_id( 'shop' ) ) ) ) {
			parent::add_custom_css_action( 'archive', 'woocommerce' );
			parent::add_custom_css_action( 'navigation', 'woocommerce' );
			// $this->add_custom_css_action( 'tables', 'base' ); // only for calendar widget
			$xstore_amp_scripts['form'] = 'amp-form-0.1';
			
			$file = XStore_AMP_TEMPLATES_PATH . 'woocommerce/archive-product.php';
		}
		elseif ( $xstore_amp_vars['is_woocommerce'] && is_cart() ) {
			
			remove_action('xstore_amp_before_main_content', 'woocommerce_breadcrumb', 10);
			
			parent::add_custom_css_action( 'step-breadcrumbs', 'woocommerce' );
			$page_type = 'cart';
			add_action('xstore_amp_after_header', function($page) use ($page_type) {
			    $this->steps_breadcrumbs($page_type);
            }, 10);
			
			if ( $xstore_amp_vars['cart_count'] < 1) {
				parent::add_custom_css_action( 'cart-empty', 'woocommerce' );
				$file = XStore_AMP_TEMPLATES_PATH . 'woocommerce/cart/cart-empty.php';
			}
			else {
				parent::add_custom_css_action( 'tables', 'base' );
				parent::add_custom_css_action( 'cart', 'woocommerce' );
				parent::add_custom_css_action( 'coupon', 'woocommerce' );
				
				parent::add_custom_css_action( 'carousel', 'base' );
				// forms
				parent::add_custom_css_action( 'global', 'forms' );
				parent::add_custom_css_action( 'rotate', 'animations' );
				
				$xstore_amp_scripts['carousel'] = 'amp-carousel-0.2';
				$xstore_amp_scripts['form'] = 'amp-form-0.1';
				$xstore_amp_scripts_templates['mustache'] = 'amp-mustache-0.2';
				$file = XStore_AMP_TEMPLATES_PATH . 'woocommerce/cart/cart.php';
			}
		}
		elseif ( $xstore_amp_vars['blog_id'] == $post_id ) {
			parent::add_custom_css_action( 'grid', 'base' );
			parent::add_custom_css_action( 'global', 'blog' );
			parent::add_custom_css_action( 'navigation', 'blog' );
			$file = XStore_AMP_TEMPLATES_PATH . 'archive.php';
		}
		elseif ( is_singular('product') ) {
			parent::add_custom_css_action( 'accordion', 'base' );
			parent::add_custom_css_action( 'selector', 'base' );
			// tables for additional info
			parent::add_custom_css_action( 'tables', 'base' );
			// comments
			parent::add_custom_css_action( 'comment-form', 'base' );
            // forms
            parent::add_custom_css_action( 'global', 'forms' );
			parent::add_custom_css_action( 'popup', 'forms' );
			parent::add_custom_css_action( 'rotate', 'animations' );
			// content
			if ( class_exists( 'WPBMap' ) ) {
				parent::add_custom_css_action( 'wpbakery', 'builders' );
			}
			if ( defined( 'ELEMENTOR_VERSION' ) ) {
				parent::add_custom_css_action( 'elementor', 'builders' );
			}
			
			parent::add_custom_css_action( 'single-product', 'woocommerce' );
			
			// related/up-sells
			parent::add_custom_css_action( 'carousel', 'base' );
			
			$xstore_amp_scripts['form'] = 'amp-form-0.1';
			$xstore_amp_scripts_templates['mustache'] = 'amp-mustache-0.2';
			$xstore_amp_scripts['carousel'] = 'amp-carousel-0.2';
			$xstore_amp_scripts['selector'] = 'amp-selector-0.1';
			$xstore_amp_scripts['lightbox-gallery'] = 'amp-lightbox-gallery-0.1';
			$xstore_amp_scripts['accordion'] = 'amp-accordion-0.1';
			
			// if sticky cart
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
			
            remove_action( 'xstore_amp_after_footer', array( $this, 'mobilePanel' ), 10 );
            parent::remove_custom_css_action( 'base/mobilePanel-config.php' );
            parent::remove_custom_css_action( 'base/mobilePanel.css' );

            parent::add_custom_css_action( 'sticky-cartConfig', 'woocommerce', 'php' );
            parent::add_custom_css_action( 'sticky-cart', 'woocommerce' );
            add_action( 'xstore_amp_after_footer', array( $this, 'stickyCart' ), 10 );
			
			$file = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product.php';
		}
		elseif (is_home() || is_front_page()) {
			parent::add_custom_css_action( 'carousel', 'base' );
			
			parent::add_custom_css_action( 'global', 'blog' );
			parent::add_custom_css_action( 'carousel-posts', 'blog' );
			
			parent::add_custom_css_action( 'global', 'woocommerce' );
			parent::add_custom_css_action( 'star-rating', 'woocommerce' );
			
			parent::add_custom_css_action( 'home' );
			
			$xstore_amp_scripts['carousel'] = 'amp-carousel-0.2';
			$file = XStore_AMP_TEMPLATES_PATH . 'page.php';
		}
		elseif ( is_404() ) {
			parent::add_custom_css_action( '404-config', 'base', 'php' );
			parent::add_custom_css_action( '404', 'base' );
			$file = XStore_AMP_TEMPLATES_PATH . '404.php';
		}
		elseif ( is_singular(array('post', 'etheme_portfolio')) ) {
			parent::add_custom_css_action( 'comment-form', 'base' );
			parent::add_custom_css_action( 'global', 'blog' );
			parent::add_custom_css_action( 'single', 'blog' );
			
			if ( class_exists( 'WPBMap' ) ) {
				parent::add_custom_css_action( 'wpbakery', 'builders' );
			}
			if ( defined( 'ELEMENTOR_VERSION' ) ) {
				parent::add_custom_css_action( 'elementor', 'builders' );
			}
			
			$file = XStore_AMP_TEMPLATES_PATH . 'single.php';
		}
		elseif ( $xstore_amp_vars['is_woocommerce'] && is_account_page() ) {
			// forms
			parent::add_custom_css_action( 'global', 'forms' );
			parent::add_custom_css_action( 'rotate', 'animations' );
			$xstore_amp_scripts['form'] = 'amp-form-0.1';
			$xstore_amp_scripts_templates['mustache'] = 'amp-mustache-0.2';
			
		    if ( is_user_logged_in() ) {
			    // tables for orders
			    parent::add_custom_css_action( 'tables', 'base' );
			    parent::add_custom_css_action( 'account-loggedIn', 'woocommerce' );
			    
		        $file = XStore_AMP_TEMPLATES_PATH . 'woocommerce/myaccount/my-account.php';
		    }
		    elseif ( is_lost_password_page() ) {
			    parent::add_custom_css_action( 'account-loggedOut', 'woocommerce' );
                $file = XStore_AMP_TEMPLATES_PATH . 'woocommerce/myaccount/form-lost-password.php';
            }
		    else {
			    $social_settings = array(
				    'facebook' => array(
					    'id' => get_theme_mod('facebook_app_id'),
					    'secret' => get_theme_mod('facebook_app_secret'),
				    ),
				    'google' =>  array(
					    'id' => get_theme_mod('google_app_id'),
					    'secret' => get_theme_mod('google_app_secret'),
				    )
			    );
			
			    foreach ( $social_settings as $key => $value ) {
				    if ( ! $value['id'] || !$value['secret']){
					    unset($social_settings[$key]);
				    }
			    }
			    parent::add_custom_css_action( 'account-loggedOut', 'woocommerce' );
			    // add socials login css only if needed
			    if ( count($social_settings) ) {
				    parent::add_custom_css_action( 'account-socialLogin', 'woocommerce' );
                }
			    if ( ! empty( $_GET['reset-link-sent'] ) ) {
				    $file = XStore_AMP_TEMPLATES_PATH . 'woocommerce/myaccount/lost-password-confirmation.php';
			    }
			    else {
				    $xstore_amp_scripts['selector'] = 'amp-selector-0.1';
				    parent::add_custom_css_action( 'tabs', 'base' );
				    $file = XStore_AMP_TEMPLATES_PATH . 'woocommerce/myaccount/form-login.php';
			    }
		    }
		}
		elseif ( get_theme_mod('portfolio_projects', 1) && ( get_theme_mod( 'portfolio_page', '' ) == $post_id || get_query_var('portfolio_category') ) ) {
			parent::add_custom_css_action( 'grid', 'base' );
			parent::add_custom_css_action( 'global', 'blog' );
			parent::add_custom_css_action( 'navigation', 'blog' );
			$file = XStore_AMP_TEMPLATES_PATH . 'portfolio.php';
		}
		else {
		    global $wp;
//			wp_redirect(home_url()); // redirect to home
			wp_redirect(add_query_arg('no-amp', '1', home_url( $wp->request ))); // redirect to page from request
			exit();
		}
		
		return $file;
	}
	
	/**
	 * Filters template_parts for page.
	 *
	 * @param $template_parts
	 * @param $file
	 * @param $post_id
	 * @return mixed
	 *
	 * @since 1.0.0
	 *
	 */
	public static function amp_post_filter_template_parts($template_parts, $file, $post_id) {
		switch (basename($file)) {
			case 'archive-product.php':
			    if ( !is_search()) {
                    parent::add_custom_css_action( 'sidebar', 'woocommerce' );
				    parent::add_custom_css_action( 'tagcloud', 'widgets' );
				    if ( is_active_sidebar( 'xstore-amp-shop-sidebar' ) ) {
					    global $xstore_amp_scripts;
					    $xstore_amp_scripts['animation']         = 'amp-animation-0.1';
					    $xstore_amp_scripts['position-observer'] = 'amp-position-observer-0.1';
				    }
                    add_action('xstore_amp_after_header_content', function() {
                        if ( is_active_sidebar( 'xstore-amp-shop-sidebar' ) ) {
                            ?>
                            <span id="shopSidebarButton" on="tap:shopSidebar.open" role="button" tabindex="-1" class="filters-button pointer flex align-items-center text-center pos-fixed invisible with-shadow">
                                <svg version="1.1" width="1em" height="1em" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" fill="currentColor" style="enable-background:new 0 0 100 100; margin-right: 5px;" xml:space="preserve"><path d="M94.8,0H5.6C4,0,2.6,0.9,1.9,2.3C1.1,3.7,1.3,5.4,2.2,6.7l32.7,46c0,0,0,0,0,0c1.2,1.6,1.8,3.5,1.8,5.5v37.5c0,1.1,0.4,2.2,1.2,3c0.8,0.8,1.8,1.2,3,1.2c0.6,0,1.1-0.1,1.6-0.3l18.4-7c1.6-0.5,2.7-2.1,2.7-3.9V58.3c0-2,0.6-3.9,1.8-5.5c0,0,0,0,0,0l32.7-46c0.9-1.3,1.1-3,0.3-4.4C97.8,0.9,96.3,0,94.8,0z M61.4,49.7c-1.8,2.5-2.8,5.5-2.8,8.5v29.8l-16.8,6.4V58.3c0-3.1-1-6.1-2.8-8.5L7.3,5.1h85.8L61.4,49.7z"></path></svg>
                                <span><?php echo esc_html__('Filters', 'xstore-amp'); ?></span>
                            </span>
                            <?php
                        }
                    });
                    $template_parts[] = XStore_AMP_TEMPLATES_PATH . 'woocommerce/sidebar.php';
                }
				break;
			default;
		}
		return $template_parts;
	}
	
	/**
	 * Creates request for variation of product.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function variation_add_to_cart_request() {
		global $woocommerce;
		
		if (empty( $_REQUEST['amp-add-to-cart'] ) || !is_numeric( $_REQUEST['amp-add-to-cart'] ) ) {
			return;
		}
		
		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['amp-add-to-cart'] ) );
		$product    = wc_get_product( $product_id );
		
		if ( !is_object( $product ) || !$product->get_id() ) {
			return;
		}
		
		$variations = $product->get_available_variations();
		
		foreach ( $variations as $variation ) {
			$res_arr = array_intersect_assoc( $variation['attributes'], $_REQUEST );
			if ( count( $variation['attributes'] ) == count( $res_arr ) ) {
				$_REQUEST['variation_id'] = $variation['variation_id'];
			}
		}
	}
	
	/**
	 * Filters for woocommerce templates.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function woocommerce_template_overwrite() {
		add_filter( 'woocommerce_locate_template', function ( $template, $template_name, $template_path ) {
			$basename = basename( $template );
			switch ($basename) {
				case 'orderby.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/loop/orderby.php';
					break;
				case 'product-image.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/product-image.php';
					break;
				case 'simple.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/add-to-cart/simple.php';
					break;
				case 'variable.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/add-to-cart/variable.php';
					break;
				case 'variation-add-to-cart-button.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/add-to-cart/variation-add-to-cart-button.php';
					break;
				case 'external.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/add-to-cart/external.php';
					break;
				case 'quantity-input.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/global/quantity-input.php';
					break;
				case 'grouped.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/add-to-cart/grouped.php';
					break;
				case 'tabs.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/tabs/tabs.php';
					break;
				case 'meta.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/meta.php';
					break;
				case 'form-coupon.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/checkout/form-coupon.php';
					break;
                case 'related.php':
	                $template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/related.php';
                    break;
				case 'up-sells.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/up-sells.php';
					break;
				case 'cross-sells.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/cart/cross-sells.php';
					break;
				case 'mini-cart.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/cart/mini-cart.php';
					break;
				case 'result-count.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/loop/result-count.php';
					break;
				case 'content-widget-product.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/content-widget-product.php';
					break;
				case 'content-widget-reviews.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/content-widget-reviews.php';
					break;
                case 'form-edit-address.php':
	                $template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/myaccount/form-edit-address.php';
                    break;
				case 'form-edit-account.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/myaccount/form-edit-account.php';
					break;
				default:
					if ( false !== strpos( $template_name, 'product_cat' ) || false !== strpos( $template_name, 'product_tag' ) ) {
						$template_name = str_replace( '_', '-', $template_name );
					}
					$new_template = WC()->plugin_path() . '/templates/' . $template_name;
					$template = file_exists( $new_template ) ? $new_template : $template;
					
					break;
			}
			
			return $template;
		}, 10, 3 );
		
		add_filter('wc_get_template_part', function( $template, $slug, $name ) {
			$basename = basename( $template );
			switch ($basename) {
				case 'content-single-product.php':
					$template = XStore_AMP_TEMPLATES_PATH . 'woocommerce/content-single-product.php';
					break;
				default:
					$fallback = WC()->plugin_path() . "/templates/{$slug}-{$name}.php";
					$template = file_exists( $fallback ) ? $fallback : $template;
					break;
			}
			return $template;
		}, 10, 3);
	}
	
	public function etheme_woocommerce_subcategory_thumbnail($category) {
		$small_thumbnail_size = apply_filters( 'subcategory_archive_thumbnail_size', 'woocommerce_thumbnail' );
		$dimensions           = wc_get_image_size( $small_thumbnail_size );
		$thumbnail_id         = get_term_meta( $category->term_id, 'thumbnail_id', true );
		
		$force_ratio = apply_filters('xstore_amp_render_image_force_ratio', false);
		
		if ( $thumbnail_id ) {
			$image        = wp_get_attachment_image_src( $thumbnail_id, $small_thumbnail_size );
			$image        = $image[0];
			$image_srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $thumbnail_id, $small_thumbnail_size ) : false;
			$image_sizes  = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $thumbnail_id, $small_thumbnail_size ) : false;
		} else {
			$image        = wc_placeholder_img_src();
			$image_srcset = false;
			$image_sizes  = false;
		}
		
		if ( $image ) {
			// Prevent esc_url from breaking spaces in urls for image embeds.
			// Ref: https://core.trac.wordpress.org/ticket/23605.
			$image = str_replace( ' ', '%20', $image );
			
			$attributes = array();
			$attributes[] = 'alt="' . esc_attr($category->name) . '"';
			
			// Add responsive image markup if available.
			if ( $image_srcset && $image_sizes ) {
				$attributes[] = 'srcset="' . esc_attr( $image_srcset ) . '"';
				$attributes[] = 'sizes="' . esc_attr( $image_sizes ) . '"';
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
			<?php echo ob_get_clean();
		}
	}
	
	public function search_page() {
	    global $xstore_amp_vars, $xstore_amp_settings;
		if ( ( isset( $_GET['et_result'] ) && $_GET['et_result'] == 'products' ) || ! is_search() || !$xstore_amp_vars['is_woocommerce'] ) {
			return;
		}
		
		$search_content = array(
				'products',
				'posts'
			);
		
		if ( isset($xstore_amp_settings['general']['search_results']) && !empty($xstore_amp_settings['general']['search_results']) ) {
			$search_content = explode(',', $xstore_amp_settings['general']['search_results']);
			foreach ( $search_content as $element_key => $element_name ) {
				if ( !isset($xstore_amp_settings['general'][$element_name.'_visibility']) || !$xstore_amp_settings['general'][$element_name.'_visibility'] ) {
					unset($search_content[$element_key]);
				}
			}
		}
		
		if ( ! is_array( $search_content ) ) {
			return;
		}
		
		if ( in_array('products', $search_content) && woocommerce_product_loop() ) {
			add_action( 'xstore_amp_before_product_loop_start', function($count){
				printf(
					'<h2 class="widget-title"><span>%s </span><span>%s</span></h2>',
					$count,
					_nx( 'Product found', 'Products found', $count, 'Search results page - products found text', 'xstore-amp' )
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
						add_action('xstore_amp_before_product_loop_start',array($this, $value . '_in_search_results'), $key + 10);
					}
				} else {
					if ( in_array($value, $search_content) ) {
						add_action('xstore_amp_after_product_loop_end',array($this, $value . '_in_search_results'), $key + 10);
					}
				}
			}
		}
	}
	
	public function posts_in_search_results(){
		if(!is_search()) return;
		
		if( get_search_query() ) :
   
			$args = array(
				's'                   => get_search_query(),
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => 50,
			);
			
			$posts = get_posts( $args );
			
			if ( count($posts) ) {
			 
				remove_action('woocommerce_no_products_found', 'wc_no_products_found', 10);
				
				printf(
					'<h2 class="widget-title"><span>%s </span><span>%s</span></h2>',
					count($posts),
					_nx( 'Post found', 'Posts found', count($posts), 'Search results page - posts found text', 'xstore-amp' )
				);
				
				parent::create_carousel(
                    $posts,
                    'posts_array',
					array(
						'data-slides' => 1.5
					)
                );
			}
			?>
		<?php endif;
	}
	
	/**
	 * Set recently viewed products to use them on home page if needed.
	 *
	 * @since 1.0.2
	 *
	 * @return void
	 */
	public function set_recently_viewed() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}
		
		global $post;
		
		if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) { // @codingStandardsIgnoreLine.
			$viewed_products = array();
		} else {
			$viewed_products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ); // @codingStandardsIgnoreLine.
		}
		
		// Unset if already in viewed products list.
		$keys = array_flip( $viewed_products );
		
		if ( isset( $keys[ $post->ID ] ) ) {
			unset( $viewed_products[ $keys[ $post->ID ] ] );
		}
		
		$viewed_products[] = $post->ID;
		
		if ( count( $viewed_products ) > 15 ) {
			array_shift( $viewed_products );
		}
		
		// Store for session only.
		wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
	}
	
	/**
	 * Mobile menu sidebar content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function mobile_menu_sidebar() {
		include_once XStore_AMP_TEMPLATES_PATH . 'header/parts/mobile_menu_sidebar.php';
	}
	
	/**
	 * Cart sidebar content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function cart_sidebar() {
		include_once XStore_AMP_TEMPLATES_PATH . 'header/parts/cart_sidebar.php';
	}
	
	public static function search_form() {
		include_once XStore_AMP_TEMPLATES_PATH . 'header/parts/search_form.php';
	}
	
	/**
	 * Back top content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function backTop() {
		include_once XStore_AMP_TEMPLATES_PATH . 'backTop.php';
	}
	
	/**
	 * Back top observer.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function backTopAnchor() {
		include_once XStore_AMP_TEMPLATES_PATH . 'anchors/backTop.php';
	}
	
	/**
	 * Mobile panel content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function mobilePanel() {
		include_once XStore_AMP_TEMPLATES_PATH . 'mobile-panel.php';
	}
	
	/**
	 * Sticky cart content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function stickyCart() {
		include_once XStore_AMP_TEMPLATES_PATH . 'woocommerce/single-product/sticky-cart.php';
	}
	
	/**
	 * Dark/light switcher content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function darkLightSwitcher() {
		include_once XStore_AMP_TEMPLATES_PATH . 'extra/dark-light-switcher.php';
	}
	
	/**
	 * Google analytics amp-script.
	 *
	 * @since 1.0.2
	 *
	 * @return void
	 */
	public static function google_analytics() {
		global $xstore_amp_settings;
	    ?>
        <amp-analytics type="gtag" data-credentials="include">
            <script type="application/json">
                {
                    "vars" : {
                        "gtag_id": "<?php echo $xstore_amp_settings['advanced']['gtag_id'] ?>",
                        "config" : {
                            "<?php echo $xstore_amp_settings['advanced']['gtag_id'] ?>": { "groups": "default" }
                        }
                    }
                }
            </script>
        </amp-analytics>
        <?php
	}
	
	public function get_post_image_metadata( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}
		
		$post_image_meta = null;
		$post_image_id   = false;
		
		if ( has_post_thumbnail( $post->ID ) ) {
			$post_image_id = get_post_thumbnail_id( $post->ID );
		} elseif ( ( 'attachment' === $post->post_type ) && wp_attachment_is( 'image', $post ) ) {
			$post_image_id = $post->ID;
		} else {
			$attached_image_ids = get_posts(
				[
					'post_parent'      => $post->ID,
					'post_type'        => 'attachment',
					'post_mime_type'   => 'image',
					'posts_per_page'   => 1,
					'orderby'          => 'menu_order',
					'order'            => 'ASC',
					'fields'           => 'ids',
					'suppress_filters' => false,
				]
			);
			
			if ( ! empty( $attached_image_ids ) ) {
				$post_image_id = array_shift( $attached_image_ids );
			}
		}
		
		if ( ! $post_image_id ) {
			return false;
		}
		
		$post_image_src = wp_get_attachment_image_src( $post_image_id, 'full' );
		
		if ( is_array( $post_image_src ) ) {
			$post_image_meta = [
				'@type'  => 'ImageObject',
				'url'    => $post_image_src[0],
				'width'  => $post_image_src[1],
				'height' => $post_image_src[2],
			];
		}
		
		return $post_image_meta;
	}
	
	public function get_schemaorg_metadata() {
		$metadata = [
			'@context'  => 'http://schema.org',
			'publisher' => [
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			],
		];

//			$publisher_logo = amp_get_publisher_logo();
//			if ( $publisher_logo ) {
//				$metadata['publisher']['logo'] = [
//					'@type' => 'ImageObject',
//					'url'   => $publisher_logo,
//				];
//			}
		
		$queried_object = get_queried_object();
		if ( $queried_object instanceof WP_Post ) {
			$metadata = array_merge(
				$metadata,
				[
					'@type'            => is_page() ? 'WebPage' : 'BlogPosting',
					'mainEntityOfPage' => get_permalink(),
					'headline'         => get_the_title(),
					'datePublished'    => mysql2date( 'c', $queried_object->post_date_gmt, false ),
					'dateModified'     => mysql2date( 'c', $queried_object->post_modified_gmt, false ),
				]
			);
			
			$post_author = get_userdata( $queried_object->post_author );
			if ( $post_author ) {
				$metadata['author'] = [
					'@type' => 'Person',
					'name'  => html_entity_decode( $post_author->display_name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
				];
			}
			
			$image_metadata = $this->get_post_image_metadata( $queried_object );
			if ( $image_metadata ) {
				$metadata['image'] = $image_metadata['url'];
			}
			
			/**
			 * Filters Schema.org metadata for a post.
			 *
			 * The 'post_template' in the filter name here is due to this filter originally being introduced in `AMP_Post_Template`.
			 * In general the `amp_schemaorg_metadata` filter should be used instead.
			 *
			 * @since 0.3
			 *
			 * @param array   $metadata       Metadata.
			 * @param WP_Post $queried_object Post.
			 */
			$metadata = apply_filters( 'amp_post_template_metadata', $metadata, $queried_object );
		} elseif ( is_archive() ) {
			$metadata['@type'] = 'CollectionPage';
		}
		
		/**
		 * Filters Schema.org metadata for a query.
		 *
		 * Check the the main query for the context for which metadata should be added.
		 *
		 * @since 0.7
		 *
		 * @param array   $metadata Metadata.
		 */
		$metadata = apply_filters( 'amp_schemaorg_metadata', $metadata );
		
		return $metadata;
	}
	
	
	public function print_schemaorg_metadata() {
		$metadata = $this->get_schemaorg_metadata();
		if ( empty( $metadata ) ) {
			return;
		}
		?>
        <script type="application/ld+json"><?php echo wp_json_encode( $metadata, JSON_UNESCAPED_UNICODE ); ?></script>
		<?php
	}
	
	/**
	 * Steps breadcrumbs (cart, checkout).
	 *
	 * @param $page
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 */
	public static function steps_breadcrumbs($page) {
	    ?>
        <div class="step-breadcrumbs">
            <span <?php if ($page == 'cart') echo 'class="active"'; ?> data-step="1"><?php echo esc_html__('Shopping Cart', 'xstore-amp'); ?></span>
            <span <?php if ($page == 'checkout') echo 'class="active"'; ?> data-step="2"><?php echo esc_html__('Checkout', 'xstore-amp'); ?></span>
        </div>
        <?php
	}
	
	
}

$XStore_AMP_actions = new XStore_AMP_actions();