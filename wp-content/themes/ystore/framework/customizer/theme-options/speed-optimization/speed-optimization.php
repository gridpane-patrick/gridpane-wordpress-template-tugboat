<?php  
/**
 * The template created for displaying general optimization options 
 *
 * @version 0.0.4
 * @since 6.0.0
 * @log
 * 0.0.2
 * ADDED: Disable Gutenberg CSS option
 * ADDED: Wishlist for variation products
 * 0.0.3
 * ADDED: Always load wc-cart-fragments
 */
add_filter( 'et/customizer/add/sections', function($sections)  use($priorities){

	$args = array(
		'general-optimization'	 => array(
			'name'        => 'general-optimization',
			'title'          => esc_html__( 'Speed Optimization', 'xstore' ),
			'icon' => 'dashicons-dashboard',
			'priority' => $priorities['speed-optimization'],
			'type'		=> 'kirki-lazy',
			'dependency'    => array()
		)
	);
	return array_merge( $sections, $args );
});

$hook = class_exists('ETC_Initial') ? 'et/customizer/add/fields/general-optimization' : 'et/customizer/add/fields';
add_filter( $hook, function ( $fields ) {
	$args = array();
	
	if ( function_exists('et_b_get_widgets') ) {
		$ajaxify_widgets = et_b_get_widgets();
		// prevent some widgets from ajaxify because they contain conditions of is_shop || is_product_taxonomy()
		// so that return false in ajax actions
		$prevent_widgets_ajaxify = array(
			'WC_Widget_Layered_Nav_Filters',
			'WC_Widget_Layered_Nav',
			'WC_Widget_Price_Filter',
			'WC_Widget_Rating_Filter',
			'ETC\App\Models\Widgets\Price_Filter',
			'ETC\App\Models\Widgets\Apply_All_Filters',
			'ETC\App\Models\Widgets\Brands_Filter',
			'ETC\App\Models\Widgets\Layered_Nav_Filters',
			'ETC\App\Models\Widgets\Product_Status_Filter',
			'ETC\App\Models\Widgets\Swatches_Filter',
		);
		
		foreach ( $prevent_widgets_ajaxify as $prevent_widget_ajaxify_key => $prevent_widget_ajaxify_name ) {

			if( isset( $ajaxify_widgets[ $prevent_widget_ajaxify_name ] ) ){
				$prevent_widgets_ajaxify[ $prevent_widget_ajaxify_name ] = $ajaxify_widgets[ $prevent_widget_ajaxify_name ];
			}

			unset( $prevent_widgets_ajaxify[ $prevent_widget_ajaxify_key ] );
		}
		
		$ajaxify_widgets = array_diff_key( $ajaxify_widgets, $prevent_widgets_ajaxify );
	}
	else {
		$ajaxify_widgets = array();
	}
	

	// Array of fields
	$args = array(
		'images_loading_type_et-desktop'	=> array(
			'name'		  => 'images_loading_type_et-desktop',
			'type'        => 'select',
			'settings'    => 'images_loading_type_et-desktop',
			'label'       => esc_html__( 'Image Loading Type', 'xstore' ),
			'description' => esc_html__( 'It can improve the loading time. Lazy Load - images will be loaded only as they enter the viewport and reduces the number of requests. LQIP(Low-Quality Image Placeholders) - initially loads a low-quality (smaller version) of the final image to fill in the container until the high-resolution version can load.', 'xstore' ),
			'section'     => 'general-optimization',
			'default'     => 'lazy',
			'choices'     => array(
				'lazy' => esc_html__( 'Lazy', 'xstore' ),
				'lqip' => esc_html__( 'LQIP', 'xstore' ),
				'default' => esc_html__( 'Default', 'xstore' ),
			),
			'priority'	  => 1,
		),

		'et_optimize_js'	=> array(
			'name'		  => 'et_optimize_js',
			'type'        => 'toggle',
			'settings'    => 'et_optimize_js',
			'label'       => esc_html__( 'Old browser support', 'xstore' ),
			'description' => esc_html__( 'Turn on to load additional JS library to support old browsers.', 'xstore' ),
			'section'     => 'general-optimization',
			'default'     => 0,
			'priority'	  => 2,
		),

//		'et_optimize_css'	=> array(
//			'name'		  => 'et_optimize_css',
//			'type'        => 'toggle',
//			'settings'    => 'et_optimize_css',
//			'label'       => esc_html__( 'Optimize frontend CSS', 'xstore' ),
//			'description' => esc_html__( 'Turn on to load optimized CSS. Read our documentation to do it in a properly way if you are using child theme installed before 5.0 theme release.', 'xstore' ),
//			'section'     => 'general-optimization',
//			'default'     => 0,
//			'priority'	  => 3,
//		),

//		'global_masonry'	=> array(
//			'name'		  => 'global_masonry',
//			'type'        => 'toggle',
//			'settings'    => 'global_masonry',
//			'label'       => esc_html__( 'Masonry scripts', 'xstore' ),
//			'description' => esc_html__( 'Turn on to load masonry scripts to all pages. Enable this option if you plan to use WPBakery Brands list, 8theme Product Looks elements.', 'xstore' ),
//			'tooltip' => esc_html__( 'Loads masonry scripts needed to work for masonry elements (115kb of page size)', 'xstore' ),
//			'section'     => 'general-optimization',
//			'default'     => 0,
//			'priority'	  => 4,
//		),
		
		// fa_icons_library
		'fa_icons_library'	=> array(
			'name'		  => 'fa_icons_library',
			'type'        => 'select',
			'settings'    => 'fa_icons_library',
			'label'       => esc_html__( 'FontAwesome support', 'xstore' ),
			'description' => esc_html__( 'Turn on to load FontAwesome icons font and scripts.', 'xstore' ),
			'tooltip' => esc_html__( 'Running FontAwesome scripts and styles needed to work for some elements that use those icons, e.g. menu subitem item icons (51kb of page size)', 'xstore' ),
			'section'     => 'general-optimization',
			'multiple'    => 1,
			'choices'     => array(
				'disable' => esc_html__('Disable', 'xstore'),
				'4.7.0' => esc_html__('4.7.0 version', 'xstore'),
				'5.15.3' => esc_html__('5.15.3 version', 'xstore'),
			),
			'default' => 'disable',
			'priority'	  => 3,
		),

		'menu_dropdown_ajax'	=> array(
			'name'		  => 'menu_dropdown_ajax',
			'type'        => 'toggle',
			'settings'    => 'menu_dropdown_ajax',
			'label'       => esc_html__( 'Menu dropdown ajax loading', 'xstore' ),
			'description' => esc_html__( 'Enable ajax load on mouseover for menu dropdowns.', 'xstore' ),
			'section'     => 'general-optimization',
			'default'     => 1,
			'priority'	  => 4,
		),
		
		'menu_dropdown_ajax_cache'	=> array(
			'name'		  => 'menu_dropdown_ajax_cache',
			'type'        => 'toggle',
			'settings'    => 'menu_dropdown_ajax_cache',
			'label'       => esc_html__( 'Menu dropdown cache', 'xstore' ),
			'description' => esc_html__( 'Enable localStorage cache for menu dropdowns. If you are still in develop mode, please, keep this option disabled to see changes at once.', 'xstore' ),
			'section'     => 'general-optimization',
			'default'     => 1,
			'priority'	  => 5,
			'active_callback' => array(
				array(
					'setting'  => 'menu_dropdown_ajax',
					'operator' => '==',
					'value'    => 1,
				),
			),
		),
	
		'menu_cache'	=> array(
			'name'		  => 'menu_cache',
			'type'        => 'toggle',
			'settings'    => 'menu_cache',
			'label'       => esc_html__( 'Menu cache', 'xstore' ),
			'description' => esc_html__( 'Enable object cache for menu.', 'xstore' ),
			'section'     => 'general-optimization',
			'default'     => 0,
			'priority'	  => 6,
		),

		'static_block_cache'	=> array(
			'name'		  => 'static_block_cache',
			'type'        => 'toggle',
			'settings'    => 'static_block_cache',
			'label'       => esc_html__( 'Static Blocks cache', 'xstore' ),
			'description' => esc_html__( 'Enable object cache for Static Blocks.', 'xstore' ),
			'section'     => 'general-optimization',
			'default'     => 1,
			'priority'	  => 7,
		),

		'wishlist_for_variations_new'	=> array(
			'name'		  => 'wishlist_for_variations_new',
			'type'        => 'toggle',
			'settings'    => 'wishlist_for_variations_new',
			'label'       => esc_html__( 'Wishlist for variation products', 'xstore' ),
			'description' => esc_html__( 'Wishlist from shop page will add selected product variation.', 'xstore' ),
			'section'     => 'general-optimization',
			'default'     => 0,
			'priority'	  => 11,
		),

		'load_wc_cart_fragments'	=> array(
			'name'		  => 'load_wc_cart_fragments',
			'type'        => 'toggle',
			'settings'    => 'load_wc_cart_fragments',
			'label'       => esc_html__( 'Always load wc-cart-fragments', 'xstore' ),
			'description' => esc_html__( 'WooCommerce “Cart Fragments” is a script using admin ajax to update the cart without refreshing the page. This functionality will slow down the speed of your site.', 'xstore' ),
			'section'     => 'general-optimization',
			'default'     => 0,
			'priority'	  => 12,
		),

		'et_load_css_minify'	=> array(
			'name'		  => 'et_load_css_minify',
			'type'        => 'toggle',
			'settings'    => 'et_load_css_minify',
			'label'       => esc_html__( 'Minify CSS', 'xstore' ),
			'description' => esc_html__( 'Minify theme and core plugin css assets', 'xstore' ),
			'section'     => 'general-optimization',
			'default'     => 1,
			'priority'	  => 13,
		),
		
		// widgets_ajaxify
		'widgets_ajaxify'	=> array(
			'name'		  => 'widgets_ajaxify',
			'type'        => 'select',
			'multiple'    => PHP_INT_MAX, // 0 as infinite does not work
			'settings'    => 'widgets_ajaxify',
			'label'       => esc_html__('Ajaxify Widgets', 'xstore'),
			'description' => esc_html__( 'Ajax loading of widgets set in this option', 'xstore' ),
			'section'     => 'general-optimization',
			'choices'     => $ajaxify_widgets,
		),
		
		// menus_ajaxify
		'menus_ajaxify'	=> array(
			'name'		  => 'menus_ajaxify',
			'type'        => 'select',
			'multiple'    => PHP_INT_MAX, // 0 as infinite does not work
			'settings'    => 'menus_ajaxify',
			'label'       => esc_html__('Ajaxify Menus', 'xstore'),
			'description' => esc_html__( 'Ajax loading of menus set in this option', 'xstore' ),
			'section'     => 'general-optimization',
			'choices'     => et_b_get_terms('nav_menu'),
		),
	);

	if ( defined( 'ET_CORE_VERSION' ) ) {

		$args = array_merge( $args, array(
			'cssjs_ver'	=> array(
				'name'		  => 'cssjs_ver',
				'type'        => 'toggle',
				'settings'    => 'cssjs_ver',
				'label'       => esc_html__( 'Remove query strings from theme static resources', 'xstore' ),
				'description' => esc_html__( 'Enable to remove the version query string from static resources to improve the Remove query strings from static resources grade on GT Metrix. Don\'t enable if you use cache plugin where this option is also enabled', 'xstore' ),
				'section'     => 'general-optimization',
				'default'     => 0,
				'priority'	  => 8,
			),

			'disable_emoji'	=> array(
				'name'		  => 'disable_emoji',
				'type'        => 'toggle',
				'settings'    => 'disable_emoji',
				'label'       => esc_html__( 'Disable emoji', 'xstore' ),
				'description' => esc_html__( 'It generates an additional HTTP request on your WordPress site to load the wp-emoji-release.min.js file. ', 'xstore' ),
				'section'     => 'general-optimization',
				'default'     => 0,
				'priority'	  => 9,
			),

			'disable_block_css'	=> array(
				'name'		  => 'disable_block_css',
				'type'        => 'toggle',
				'settings'    => 'disable_block_css',
				'label'       => esc_html__( 'Disable Gutenberg styles', 'xstore' ),
				'section'     => 'general-optimization',
				'default'     => 0,
				'priority'	  => 10,
			),

			'disable_elementor_dialog_js'	=> array(
				'name'		  => 'disable_elementor_dialog_js',
				'type'        => 'toggle',
				'settings'    => 'disable_elementor_dialog_js',
				'label'       => esc_html__( 'Disable elementor dialog js', 'xstore' ),
				'section'     => 'general-optimization',
				'default'     => 1,
				'priority'	  => 10,
			),
            'disable_theme_swiper_js'	=> array(
                'name'		  => 'disable_theme_swiper_js',
                'type'        => 'toggle',
                'settings'    => 'disable_theme_swiper_js',
                'label'       => esc_html__( 'Disable theme swiper js library', 'xstore' ),
                'description' => esc_html__( 'Theme use own swiper.js library, if you use plugins that have they own, enable this option to prevent loading second library.', 'xstore' ),
                'section'     => 'general-optimization',
                'default'     => 0,
                'priority'	  => 10,
            ),
		));

	}

	return array_merge( $fields, $args );

});