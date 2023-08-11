<?php 

    add_action( 'init', function(){
        if ( !class_exists( 'Kirki' ) ) {
            return;
        }

        $options = array(
            'config' => 'config',
            'general' => 'layout',
            'typography' => 'global',
            'breadcrumbs' => 'breadcrumbs',
            'footer' => 'global',
            'styling' => 'styling',
            'portfolio' => 'portfolio',
            'woocommerce' => ( class_exists('WooCommerce') ? 'global' : 'section' ),
            'blog' => 'global',
            'social-sharing' => 'social-sharing',
            '404-page' => '404-page',
            'custom-css' => 'custom-css',
            'speed-optimization' => 'speed-optimization'
        );

        foreach ($options as $key => $value) {
            require_once apply_filters('etheme_file_url', ETHEME_CODE . 'customizer/theme-options/'.$key.'/'.$value.'.php');   
        }

        do_action( 'customizer_after_including_fields' );
        
        // Config
        Kirki::add_config( 'et_kirki_options', array(
            'capability'    => 'edit_theme_options',
            'option_type'   => 'theme_mod',
        ) );

        // panel
        $panels = apply_filters( 'et/customizer/add/panels', array() );
        foreach ($panels as $panel) {
            kirki::add_panel($panel['id'], $panel);
        }

        // section
        $sections = apply_filters( 'et/customizer/add/sections', array() );
        foreach ($sections as $section) {
            Kirki::add_section($section['name'], $section);
        }

        // field
        $fields = apply_filters( 'et/customizer/add/fields', array() );
        foreach ($fields as $field) {
            Kirki::add_field('et_kirki_options', $field);
        }

    });

    // copy full when update will be on 
    if ( ! class_exists( 'Kirki' ) ) {
        global $et_options;

        $base_options = get_template_directory() . '/theme/base-options.php';
        $base_options = require_once $base_options;
        $base_options = json_decode( $base_options, true );
        $et_options   = $base_options;
        return;
    }

    function etheme_refresh_header_buttons_partials( WP_Customize_Manager $wp_customize ) {
    // Abort if selective refresh is not available.
    if ( ! isset( $wp_customize->selective_refresh ) ) {
        return;
    }
        $et_partials = array();
    
        $et_partials['header_partials'] = array (
                // header parts
                array(
                    'selector' => '.header-top',
                    'partial' => 'top_header_style_separator',
                ),
                array(
                    'selector' => '.header-main',
                    'partial' => 'main_header_style_separator',
                ),
                array(
                    'selector' => '.header-bottom',
                    'partial' => 'bottom_header_style_separator',
                ),
                // vertical header 
                array(
                    'selector' => '#header-vertical',
                    'partial' => 'header_vertical_et-desktop',
                ),
                array(
                    'selector' => '#header-vertical .et_b_header-logo.et_element-top-level',
                    'partial' => 'header_vertical_logo_img_et-desktop',
                ),
                array(
                    'selector' => '#header-vertical .header-vertical-menu-icon-wrapper, .header-vertical-menu',
                    'partial' => 'header_vertical_menu_type_et-desktop',
                ),
                // header elements 
                array(
                    'selector' => '.header-button-wrapper.et_element-top-level',
                    'partial' => 'button_content_separator',
                ),
                array(
                    'selector' => '.site-header .et_b_header-logo.et_element-top-level',
                    'partial' => 'logo_content_separator',
                ),
                // all departments menu 
                array(
                    'selector' => '.et_b_header-menu.et_element-top-level .secondary-menu-wrapper',
                    'partial' => 'secondary_menu_content_separator',
                ),
                array(
                    'selector' => '.et_b_header-mobile-menu',
                    'partial' => 'mobile_menu_content_separator',
                ),
                array(
                    'selector' => '.et_b_header-wishlist.et_element-top-level',
                    'partial' => 'wishlist_content_separator',
                ),
                array(
                    'selector' => '.et_b_header-socials.et_element-top-level',
                    'partial' => 'header_socials_content_separator',
                ),
                array(
                    'selector' => '.et_b_header-languages.et_element-top-level',
                    'partial' => 'languages_content_separator',
                ),
                array(
                    'selector' => '.header-html_block1',
                    'partial' => 'html_block1',
                ),
                array(
                    'selector' => '.header-html_block2',
                    'partial' => 'html_block2',
                ),
                array(
                    'selector' => '.header-html_block3',
                    'partial' => 'html_block3',
                ),
                array(
                    'selector' => '.et_b_header-widget',
                    'partial' => 'header_widget1_content_separator',
                ),
                array(
                    'selector' => '.et_b_header-account.et_element-top-level',
                    'partial' => 'account_content_separator',
                ),
                array(
                    'selector' => '.et_b_header-search.et_element-top-level',
                    'partial' => 'search_content_separator',
                ),
                array(
                    'selector' => '.header-promo-text',
                    'partial' => 'promo_text_content_separator',
                ),
                array(
                    'selector' => '.et_b_header-newsletter.et_element-top-level',
                    'partial' => 'newsletter_content_separator',
                ),
                array(
                    'selector' => '.et_b_header-cart.et_element-top-level',
                    'partial' => 'cart_content_separator',
                ),
                array(
                    'selector' => '.header-main-menu.et_element-top-level',
                    'partial' => 'menu_content_separator',
                ),
                array(
                    'selector' => '.header-main-menu2.et_element-top-level',
                    'partial' => 'menu_2_content_separator',
                ),
                array(
                    'selector' => '.site-header .et_b_header-menu.et_element-top-level:not(.header-secondary-menu) .nav-sublist-dropdown, .site-header .header-secondary-menu.et_element-top-level .menu .nav-sublist-dropdown',
                    'partial' => 'menu_dropdown_content_separator',
                ),
                array(
                    'selector' => '.et_b_header-contacts.et_element-top-level',
                    'partial' => 'contacts_content_separator',
                ),
            );

        if ( get_option( 'etheme_single_product_builder', false ) ) {
            // single product elements 
            $et_partials['single_product_partials'] = array(
                array(
                    'partial' => 'product_gallery_content_separator',
                    'selector' => '.woocommerce-product-gallery'
                ),
                array(
                    'partial' => 'product_title_style_separator',
                    'selector' => 'h1.product_title',
                ),
                array(
                    'partial' => 'product_price_style_separator',
                    'selector' => '.et_product-block > .price, .element > .price',
                ),
                array(
                    'partial' => 'product_rating_content_separator',
                    'selector' => '.woocommerce-product-rating .star-rating',
                ),
                array(
                    'partial' => 'product_meta_content_separator',
                    'selector' => '.product_meta',
                ),
                array(
                    'partial' => 'product_cart_style_separator',
                    'selector' => 'form.cart',
                ),
                array(
                    'partial' => 'product_tabs_content_separator',
                    'selector' => '.woocommerce-tabs',
                ),
                array(
                    'partial' => 'product_short_description_style_separator',
                    'selector' => '.woocommerce-product-details__short-description',
                ),
                array(
                    'partial' => 'product_sharing_content_separator',
                    'selector' => '.single-product-socials',
                ),
                array(
                    'partial' => 'product_size_guide_content_separator',
                    'selector' => '.single-product-size-guide',
                ),
                array(
                    'partial' => 'product_wishlist_content_separator',
                    'selector' => '.single-wishlist',
                ),
                array(
                    'partial' => 'product_compare_style_separator',
                    'selector' => '.single-compare',
                ),
                array(
                    'partial' => 'product_breadcrumbs_content_separator',
                    'selector' => 'body.single-product .page-heading',
                ),
                array(
                    'partial' => 'products_upsell_content_separator',
                    'selector' => 'body.single-product .upsell-products',
                ),
	            array(
		            'partial' => 'products_cross_sell_content_separator',
		            'selector' => 'body.single-product .cross-sell-products',
	            ),
                array(
                    'partial' => 'products_related_content_separator',
                    'selector' => '.related-products',
                ),
                array(
                    'partial' => 'single_product_html_block1_content_separator',
                    'selector' => '.single_product-html_block',
                ),
                // bought together
                array(
                    'partial' => 'single_product_bought_together_content_separator',
                    'selector' => '.bought-together-products',
                ),
                // sidebar settings 
                array(
                    'partial' => 'single_product_layout_content_separator',
                    'selector' => '.single-product .widget-area'
                )
            );
        }
        else {
            $et_partials['single_product_partials'] = array(
                // product brands
                array(
                    'selector' => '.product_brand, .products-page-brands',
                    'partial' => 'enable_brands'
                ),

                array(
                    'selector' => 'body.single-product .page-heading',
                    'partial' => 'breadcrumb_type'
                ),

                // sale, outofstock
                array(
                    'selector' => '.sale-wrapper',
                    'partial' => 'sale_icon'
                ),

                // single related products 
                array(
                    'partial' => 'show_related',
                    'selector' => '.related-products',
                ),

                // tabs 
                array(
                    'selector' => '.single .single-product .woocommerce-tabs',
                    'partial' => 'tabs_type'
                ),
            );
        }

        $et_partials['global_partials'] = array (

            // breadcrumbs 
            array(
                'selector' => 'body:not(.single-product) .page-heading, .cart-checkout-nav',
                'partial' => 'breadcrumb_type',
            ),

            // footer 
            array(
                'selector' => '.footer',
                'partial' => 'footer_columns',
            ),
            array(
                'selector' => '.footer-bottom',
                'partial' => 'copyrights_color',
            ),

            // woocommerce 
            array(
                'selector' => '.product-category',
                'partial' => 'cat_style'
            ),
            array(
                'selector' => '.content-product',
                'partial' => 'product_view'
            ),
	        array(
		        'selector' => '.woocommerce-cart .cross-sell-products',
		        'partial' => 'cart_products_cross_sell_type_et-desktop'
	        ),

            // toolbar 
            array(
                'selector' => '.filter-wrap',
                'partial' => 'view_mode'
            ),

            // swatches 
            array(
                'selector' => '.st-swatch-in-loop, .st-swatch-preview-single-product',
                'partial' => 'enable_swatch'
            ),

            // cart empty
            array(
                'selector' => '.cart-empty',
                'partial' => 'empty_cart_content'
            ),

            // sale, outofstock
            array(
                'selector' => '.content-product .stock, .content-product .available-on-backorder',
                'partial' => 'sale_icon'
            ),

            // default woocommerce checkout 
            array(
                'selector' => '.woocommerce-checkout',
                'partial' => 'wp_page_for_privacy_policy'
            ),

            // social sharing 
            array(
                'selector' => '.product-share, .share-post',
                'partial' => 'socials',
            ),

            // page 404 content
            array(
                'selector' => '.page-404',
                'partial' => '404_text',
            ),

            // mobile panel 
            array(
                'selector' => '.mobile-panel-wrapper',
                'partial' => 'mobile_panel_content_separator',
            ),
            
        );

        foreach ($et_partials['header_partials'] as $key) {
            $wp_customize->selective_refresh->add_partial( $key['partial'],
                array(
                    'selector' => $key['selector'],
                )
            );
        }

        foreach ($et_partials['single_product_partials'] as $key) {
            $wp_customize->selective_refresh->add_partial( $key['partial'],
                array(
                    'selector' => $key['selector'],
                )
            );
        }

        foreach ($et_partials['global_partials'] as $key) {
            $wp_customize->selective_refresh->add_partial( $key['partial'],
                array(
                    'selector' => $key['selector'],
                )
            );
        }

        unset($et_partials);
}

add_action( 'customize_register', 'etheme_refresh_header_buttons_partials' );

add_action( 'customize_register', function ($wp_customize) {
	if ( !class_exists('WC_Shop_Customizer')) return;
	
	$wp_customize->add_setting(
		'woocommerce_shop_page_categories_appearance',
		array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'manage_woocommerce',
			'sanitize_callback' => function($value) {
				$options = array( '', 'separated' );
				return in_array( $value, $options, true ) ? $value : '';
			},
		)
	);
	
	$wp_customize->add_control(
		'woocommerce_shop_page_categories_appearance',
		array(
			'label'       => __( 'Categories Appearance', 'xstore' ),
			'description' => __( 'Choose how to display product categories on the product archives.', 'xstore' ),
			'section'     => 'woocommerce_product_catalog',
			'settings'    => 'woocommerce_shop_page_categories_appearance',
			'type'        => 'select',
			'choices'     => array(
				''              => __( 'Merged with Products', 'xstore' ),
				'separated' => __( 'Apart from Products', 'xstore' ),
			),
		)
	);
} );

add_action( 'customize_controls_print_scripts', function () {
	if ( !class_exists('WC_Shop_Customizer')) return;
	?>
	<script type="text/javascript">
         jQuery( function( $ ) {
			wp.customize( 'woocommerce_shop_page_display', function( setting ) {
				setting.bind( function( value ) {
        
				    if ( value == 'both' || value == 'subcategories') {
                        jQuery(wp.customize.control( 'woocommerce_shop_page_categories_appearance' ).selector).show();
				    }
				    else {
				        if ( wp.customize.control( 'woocommerce_category_archive_display' ).setting.get() != 'both')
                            jQuery(wp.customize.control( 'woocommerce_shop_page_categories_appearance' ).selector).hide();
				    }
				} );
			} );
             wp.customize( 'woocommerce_category_archive_display', function( setting ) {
                 setting.bind( function( value ) {

                     if ( value == 'both' || value == 'subcategories') {
                         jQuery(wp.customize.control( 'woocommerce_shop_page_categories_appearance' ).selector).show();
                     }
                     else {
                         if ( wp.customize.control( 'woocommerce_shop_page_display' ).setting.get() != 'both')
                            jQuery(wp.customize.control( 'woocommerce_shop_page_categories_appearance' ).selector).hide();
                     }
                 } );
             } );
	         <?php
                 if ( get_option('woocommerce_shop_page_display') != 'both' && get_option('woocommerce_category_archive_display') != 'both') {
                     ?>
                        jQuery(wp.customize.control( 'woocommerce_shop_page_categories_appearance' ).selector).hide();
                     <?php
                 }
	         ?>
         } );
	</script>
	<?php
}, 30 );