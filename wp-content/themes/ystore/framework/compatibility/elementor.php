<?php
/**
 * Description
 *
 * @package    elementor.php
 * @since      8.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

// compatibility with elementor header/footer builders
// rewritten due to a single post template error
function etheme_register_elementor_locations( $elementor_theme_manager ) {
	
	// the default locations
	$core_locations = $elementor_theme_manager->get_core_locations();
	
	// do not rewrite this locations
	unset($core_locations['archive']);
	unset($core_locations['single']);
	
	foreach ( $core_locations as $location => $settings ) {
		// rewrite locations to default
		$elementor_theme_manager->register_location( $location, $settings );
	}
	
	// previse rewritten all locations
	//$elementor_theme_manager->register_all_core_location();
}

add_action( 'elementor/theme/register_locations', 'etheme_register_elementor_locations' );

add_action( "elementor/theme/before_do_header", function() {
	ob_start();
	
	do_action( 'et_after_body', true )
	
	?>
	<div class="template-container">
	
	<?php
	/**
	 * Hook: etheme_header_before_template_content.
	 *
	 * @hooked etheme_top_panel_content - 10
	 * @hooked etheme_mobile_menu_content - 20
	 *
	 * @version 6.0.0 +
	 * @since 6.0.0 +
	 *
	 */
	do_action( 'etheme_header_before_template_content' );
	?>
	<div class="template-content">
	<div class="page-wrapper">
	<?php
	echo ob_get_clean();
} );

add_action( "elementor/theme/before_do_footer", function() {
	ob_start(); ?>
	</div> <!-- page wrapper -->
	
	</div> <!-- template-content -->
	
	<?php do_action('after_page_wrapper'); ?>
	</div> <!-- template-container -->
	<?php echo ob_get_clean();
});

add_action('wp', function () {
    $is_preview = Elementor\Plugin::$instance->preview->is_preview_mode();
    if ( $is_preview ) {
        // disable mega menu lazy load if in Elementor edit mode
        add_filter( 'menu_dropdown_ajax', '__return_false' );
        // disable mobile optimization in editor/preview mode
        // to make Elementor resize work normally
        set_query_var('et_mobile-optimization', false);
    }
    if ( defined('ELEMENTOR_PRO_VERSION') && ( get_query_var('et_is-cart', false) || get_query_var('et_is-checkout', false) ) ) {

        if ( $is_preview ) {
            add_filter('etheme_elementor_cart_page', '__return_true');
            add_filter('etheme_elementor_checkout_page', '__return_true');
        }
        else {

            $document = \Elementor\Plugin::$instance->documents->get( get_query_var('et_page-id', array('id' => 0))['id'] );

            if ( is_object( $document ) ) {
                $data = $document->get_elements_data();
                \Elementor\Plugin::$instance->db->iterate_data( $data, function( $element ) {
                    if (
                        isset( $element['widgetType'] )
                    )  {
                        switch($element['widgetType']) {
                            case 'woocommerce-cart':
                                add_filter('etheme_elementor_cart_page', '__return_true');
                                break;
                            case 'woocommerce-checkout-page':
                                add_filter('etheme_elementor_checkout_page', '__return_true');
                                break;
                        }
                    }
                });
            }
        }
    }
});

add_action( 'elementor/frontend/after_enqueue_scripts', function () {
	// is elementor preview load
	if ( Elementor\Plugin::$instance->preview->is_preview_mode() ) {
		wp_enqueue_script( 'etheme_parallax_scroll_effect' ); // works always
		wp_enqueue_script( 'etheme_parallax_3d_hover_effect' ); // works always
		wp_enqueue_script( 'etheme_parallax_hover_effect' ); // works always
	}
}, 50 );

add_action( 'elementor/frontend/before_register_scripts', function() {
//    $scripts_2_register = array(
//        'etheme_countdown',
//        'etheme_animated_headline',
//	    'etheme_progress_bar',
//	    'etheme_timeline',
//	    'etheme_product_filters',
//    );
//	foreach ($scripts_2_register as $script){
//		wp_register_script(
//			$scripts[$script]['name'],
//			get_template_directory_uri() . $scripts[$script]['file'],
//			(isset($scripts[$script]['deps']) ? $scripts[$script]['deps'] : array('jquery', 'etheme')),
//			(isset($scripts[$script]['version']) ? $scripts[$script]['version'] : ''),
//			$scripts[$script]['in_footer']
//		);
//	}
	$theme = wp_get_theme();
	foreach (etheme_config_js_files() as $script){
		wp_register_script(
			$script['name'],
			get_template_directory_uri() . $script['file'],
			(isset($script['deps']) ? $script['deps'] : array('jquery', 'etheme')),
			(isset($script['version']) ? $script['version'] : $theme->version),
			$script['in_footer']
		);
	}
	
}, 99);

add_action( 'elementor/frontend/before_register_styles', function() {
 
	$is_rtl = get_query_var('et_is-rtl', false);
	$theme = wp_get_theme();
	
	foreach (etheme_config_css_files() as $script){
		if ( !isset($script['deps'])) $script['deps'] = array("etheme-parent-style");
		
		if ( $is_rtl ) {
			$rtl_file = get_template_directory() . esc_attr( $script['file'] ) . '-rtl'.ETHEME_MIN_CSS.'.css';
			if (file_exists($rtl_file)) {
				$script['file'] .= '-rtl';
			}
		}
		
		wp_register_style(  'etheme-'.$script['name'], get_template_directory_uri() . $script['file'] . ETHEME_MIN_CSS .'.css', $script['deps'], $theme->version );
	}
}, 99);

// filters/action for product grid Elementor widget
add_filter('etheme_product_filters_taxonomies', function ($elements) {
	if ( etheme_get_option( 'enable_brands', 1 ) ) {
		$elements['brand'] = esc_html__( 'Brand', 'xstore' );
	}
    return $elements;
});

add_filter( 'etheme_product_grid_list_product_hover_elements', function ( $elements ) {
	if ( get_theme_mod( 'quick_view', 1 ) )
		$elements['quick_view'] = esc_html__( 'Show Quick View', 'xstore' );
	return $elements;
} );

add_action( 'etheme_product_grid_list_product_hover_element_render', function ( $key, $product, $edit_mode ) {
	if ( $key == 'quick_view' ) {
		if ( !$edit_mode && !wp_doing_ajax() ) {
			etheme_enqueue_style( "quick-view" );
			if ( get_theme_mod( 'quick_view_content_type', 'popup' ) == 'off_canvas' ) {
				etheme_enqueue_style( "off-canvas" );
			}
		}
		echo '<span class="show-quickly" data-prodid="' . esc_attr( $product->get_ID() ) . '" data-text="'.esc_attr('Quick View', 'xstore').'">' .
		     '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.664 8.688v0c-4.8-4.776-12.6-4.776-17.376 0l-3.288 3.264 3.36 3.36c2.328 2.328 5.4 3.6 8.712 3.6 3.288 0 6.384-1.272 8.712-3.6l3.216-3.264-3.336-3.36zM4.152 14.496l-2.52-2.544 2.496-2.496c4.344-4.344 11.4-4.344 15.744 0l2.52 2.544-2.496 2.496c-4.344 4.32-11.4 4.32-15.744 0zM12 6.648c-2.952 0-5.352 2.4-5.352 5.352s2.4 5.352 5.352 5.352c2.952 0 5.352-2.4 5.352-5.352s-2.4-5.352-5.352-5.352zM12 16.176c-2.304 0-4.176-1.872-4.176-4.176s1.872-4.176 4.176-4.176 4.176 1.872 4.176 4.176-1.872 4.176-4.176 4.176z"></path>
                    </svg>' .
		     '</span>';
	}
}, 10, 3 );

add_filter('etheme_product_grid_list_product_elements', 'etheme_product_grid_list_product_and_hover_elements_filter');
add_filter('etheme_product_grid_list_product_hover_elements', 'etheme_product_grid_list_product_and_hover_elements_filter');

function etheme_product_grid_list_product_and_hover_elements_filter($elements) {
	if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
		$excerpt_position = array_search('excerpt', array_keys($elements));
		if ( $excerpt_position > 1 ) {
			$elements = array_slice( $elements, 0, $excerpt_position, true ) +
			            array( 'stock_line' => esc_html__( 'Show Stock Line', 'xstore' ) ) +
			            array_slice( $elements, $excerpt_position, count( $elements ) - $excerpt_position, true );
		}
		else {
			$elements['stock_line'] = esc_html__( 'Show Stock Line', 'xstore' );
		}
	}
	if ( etheme_get_option( 'enable_brands', 1 ) ) {
		$rating_position = array_search('rating', array_keys($elements));
		if ( $rating_position > 1 ) {
			$elements = array_slice( $elements, 0, $rating_position, true ) +
			            array( 'brands' => esc_html__( 'Show Brands', 'xstore' ) ) +
			            array_slice( $elements, $rating_position, count( $elements ) - $rating_position, true );
		}
		else {
			$elements['brands'] = esc_html__( 'Show Brands', 'xstore' );
		}
	}
	return $elements;
}

add_filter('etheme_product_grid_list_product_hover_info_elements', function ($elements) {
	$elements[] = 'brands';
	$elements[] = 'stock_line';
	return $elements;
});

add_action( 'etheme_product_grid_list_product_element_render', 'etheme_product_grid_list_product_and_hover_element_render', 10, 3 );
add_action( 'etheme_product_grid_list_product_hover_element_render', 'etheme_product_grid_list_product_and_hover_element_render', 10, 3 );

function etheme_product_grid_list_product_and_hover_element_render($key, $product, $edit_mode) {
	switch ($key) {
		case 'stock_line':
			echo et_product_stock_line($product);
			break;
		case 'brands':
			etheme_product_brands();
			break;
	}
}

add_action('etheme_product_grid_list_product_elements_style', 'etheme_product_grid_list_product_and_hover_elements_style');

function etheme_product_grid_list_product_and_hover_elements_style ($control) {
	$control->start_controls_section(
		'section_brands_style',
		[
			'label' => __( 'Brands', 'xstore' ),
			'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
			'condition' => [
				'product_brands!' => ''
			],
		]
	);
	
	$control->add_group_control(
		\Elementor\Group_Control_Typography::get_type(),
		[
			'name' => 'brands_typography',
			'selector' => '{{WRAPPER}} .products-page-brands',
		]
	);
	
	$control->start_controls_tabs('tabs_brands_colors');
	
	$control->start_controls_tab( 'tabs_brands_color_normal',
		[
			'label' => esc_html__('Normal', 'xstore')
		]
	);
	
	$control->add_control(
		'brands_color',
		[
			'label' => __( 'Color', 'xstore' ),
			'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .products-page-brands, {{WRAPPER}} .products-page-brands a' => 'color: {{VALUE}};',
			],
		]
	);
	
	$control->end_controls_tab();
	
	$control->start_controls_tab( 'tabs_brands_color_hover',
		[
			'label' => esc_html__('Hover', 'xstore')
		]
	);
	
	$control->add_control(
		'brands_color_hover',
		[
			'label' => __( 'Color', 'xstore' ),
			'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .products-page-brands a:hover' => 'color: {{VALUE}};',
			],
		]
	);
	
	$control->end_controls_tab();
	$control->end_controls_tabs();
	
	$control->add_control(
		'brands_space',
		[
			'label' => __( 'Bottom Space', 'xstore' ),
			'type' => \Elementor\Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range' => [
				'px' => [
					'min' => 0,
					'max' => 50,
					'step' => 1,
				],
			],
			'selectors' => [
				'{{WRAPPER}} .products-page-brands' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			],
		]
	);
	
	$control->end_controls_section();
}

add_filter('etheme_product_grid_list_product_hover_info_elements_render', function ($info_elements, $hover_effect, $all_elements) {
	if ( in_array($hover_effect, array('info', 'overlay', 'default')) ) {
	    if ( array_key_exists('brands', $all_elements) ) {
		    $info_elements['brands'] = $all_elements['brands'];
        }
    }
	return $info_elements;
}, 10, 3);

add_filter('etheme_product_grid_list_product_hover_elements_render', function ($elements, $hover_effect, $info_elements) {
	if ( in_array($hover_effect, array('info', 'overlay', 'default')) ) {
		if ( array_key_exists('brands', $elements) ) {
			unset($elements['brands']);
		}
	}
	return $elements;
}, 10, 3);

add_filter('etheme_product_grid_list_product_taxonomies', function ($taxonomies) {
	if ( etheme_get_option( 'enable_brands', 1 ) ) {
		$taxonomies['brand'] = esc_html__( 'Brands', 'xstore' );
	}
    return $taxonomies;
});
// insert quick view in specific position after cart
//	add_filter('etheme_product_grid_list_product_hover_elements_render', function ($elements) {
//	    if ( array_key_exists('quick_view', $elements) && count($elements) > 1 ) {
//	        $quick_view = $elements['quick_view'];
//	        unset($elements['quick_view']);
//		    array_splice( $elements, 1, 0, $quick_view );
//        }
//	    return $elements;
//    });
//}, 9);

// Posts widget
add_filter('etheme_posts_post_meta_data', function ($meta) {
	$excerpt_position = array_search('comments', array_keys($meta));
	if ( $excerpt_position ) {
		$meta = array_slice( $meta, 0, $excerpt_position, true ) +
		            array( 'views' => esc_html__( 'Views', 'xstore' ) ) +
		            array_slice( $meta, $excerpt_position, count( $meta ) - $excerpt_position, true );
	}
	else {
		$meta['views'] = esc_html__( 'Views', 'xstore' );
	}
    return $meta;
}, 10);

add_action('etheme_posts_post_meta_data_render', function ($key, $meta, $post_id) {
    if ( $key == 'views') {
	    $number = get_post_meta( $post_id, '_et_views_count', true );
	    if( empty($number) ) $number = 0;
	    echo '<span class="etheme-post-views-count">' .
             '<a href="'.get_permalink($post_id).'">'.
            '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                <path d="M20.664 8.688v0c-4.8-4.776-12.6-4.776-17.376 0l-3.288 3.264 3.36 3.36c2.328 2.328 5.4 3.6 8.712 3.6 3.288 0 6.384-1.272 8.712-3.6l3.216-3.264-3.336-3.36zM4.152 14.496l-2.52-2.544 2.496-2.496c4.344-4.344 11.4-4.344 15.744 0l2.52 2.544-2.496 2.496c-4.344 4.32-11.4 4.32-15.744 0zM12 6.648c-2.952 0-5.352 2.4-5.352 5.352s2.4 5.352 5.352 5.352c2.952 0 5.352-2.4 5.352-5.352s-2.4-5.352-5.352-5.352zM12 16.176c-2.304 0-4.176-1.872-4.176-4.176s1.872-4.176 4.176-4.176 4.176 1.872 4.176 4.176-1.872 4.176-4.176 4.176z"></path>
            </svg>'.
             $number .
             '</a>'.
         '</span>';
    }
}, 10, 3);


// check if core is enabled because it uses functions from core plugin
if ( defined('ET_CORE_VERSION') ) {
    // Lazyload Elementor widgets
	add_filter( 'elementor/widget/render_content', 'etheme_ajaxify_elementor_widgets', PHP_INT_MAX, 2 );
}

add_action( 'elementor/element/common/_section_style/before_section_start', function( $element, $args ) {
    
    $element->start_controls_section(
        'etheme_section_lazy_load',
        array(
            'label'     => __( 'XSTORE Ajaxify', 'xstore' ),
            'tab'       => \Elementor\Controls_Manager::TAB_ADVANCED,
        )
    );
    
    $element->add_control(
        'etheme_ajaxify',
        [
            'label' => __('Lazy Loading', 'xstore'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
        ]
    );
    
    $element->end_controls_section();
    
}, 10, 2);

/**
 * Filter for Elementor render callback to modify html output for lazyloading.
 *
 * @param $widget_content
 * @param $that
 * @return mixed|string
 *
 * @since 8.1.5
 *
 */
function etheme_ajaxify_elementor_widgets($widget_content, $that){
	if (defined('DOING_ETHEME_AJAXIFY') || \Elementor\Plugin::$instance->editor->is_edit_mode() || isset($_GET['et_ajax']) || !apply_filters('etheme_ajaxify_elementor_widget', true, $that)){
		return $widget_content;
	}
	$data = $that->get_data();
	if ( isset($data['settings']['etheme_ajaxify']) && $data['settings']['etheme_ajaxify'] == 'yes' ){
	    add_filter('etheme_ajaxify_script', '__return_true');
		// in case our old ajax option is enabled then make is false to use our new ajax loading action
		if ( isset($data['settings']['ajax']) && in_array($data['settings']['ajax'], array('true', 'yes'))) {
			$data['settings']['ajax'] = false;
		}
		$widget_content = '<span class="etheme-ajaxify-lazy-wrapper etheme-ajaxify-replace" data-type="elementor" data-request="'.etheme_encoding(json_encode(array('elementor', get_the_ID(), etheme_ajaxify_set_lazyload_buffer($data)))).'">' . '</span>';
	}
	return $widget_content;
}