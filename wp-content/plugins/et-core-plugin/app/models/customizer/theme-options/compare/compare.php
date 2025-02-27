<?php
/**
 * The template created for displaying built-in compare options
 *
 * @version 1.0.0
 * @since   4.3.9
 */

add_filter( 'et/customizer/add/sections', function ( $sections ) {
	
	$args = array(
		'xstore-compare' => array(
			'name'       => 'xstore-compare',
			'title'      => esc_html__( 'Built-in Compare', 'xstore-core' ),
			'description' => esc_html__('Make shopping even more convenient and easy with our compare tool. Simply select your desired products and compare them side by side to make the best decision for your needs. With our user-friendly interface, you can easily navigate through product features, specifications, and prices.', 'xstore-core'),
			'panel'      => 'woocommerce',
			'icon'       => 'dashicons-update-alt',
			'type'       => 'kirki-lazy',
			'dependency' => array()
		)
	);
	
	return array_merge( $sections, $args );
} );

add_filter( 'et/customizer/add/fields/xstore-compare', function ( $fields ) use ( $separators, $sep_style, $strings, $choices, $box_models ) {
    $select_pages = et_b_get_posts(
        array(
            'post_per_page' => -1,
            'nopaging'      => true,
            'post_type'     => 'page',
            'with_select_page' => true
        )
    );

    $select_pages[0] = esc_html__('Dynamic page', 'xstore-core');

    $sections = et_b_get_posts(
        array(
            'post_per_page' => -1,
            'nopaging'      => true,
            'post_type'     => 'staticblocks',
            'with_none' => true
        )
    );

    $is_spb = get_option( 'etheme_single_product_builder', false );

    $estimate_popup_content_default =
        esc_html__( 'You may add any content here from Customizer -> WooCommerce -> Built-in Compare -> Popup content.', 'xstore-core');

	$args = array();
	
	// Array of fields
	$args = array(

		'xstore_compare' => array(
			'name'     => 'xstore_compare',
			'type'     => 'toggle',
			'settings' => 'xstore_compare',
			'label'    => __( 'Enable Built-in Compare', 'xstore-core' ),
			'tooltip' => esc_html__(' By enabling this feature, you\'ll be offering your customers an unparalleled shopping experience that will keep them coming back for more. So, give your customers the gift of convenience with our built-in Compare feature today and watch your sales soar!', 'xstore-core'),
			'section'  => 'xstore-compare',
			'default'  => '0',
		),

        // product_compare_icon
        'xstore_compare_icon'                    => array(
            'name'     => 'xstore_compare_icon',
            'type'     => 'radio-image',
            'settings' => 'xstore_compare_icon',
            'label'    => $strings['label']['icon'],
            'tooltip'     => $strings['description']['icon'],
            'section'  => 'xstore-compare',
            'default'  => 'type1',
            'choices'  => array(
                'type1' => ETHEME_CODE_CUSTOMIZER_IMAGES . '/header/compare/Compare-1.svg',
                'custom' => ETHEME_CODE_CUSTOMIZER_IMAGES . '/global/icon-custom.svg',
//                'none'  => ETHEME_CODE_CUSTOMIZER_IMAGES . '/global/icon-none.svg'
            ),
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

        // cart_icon_custom_svg
        'xstore_compare_icon_custom_svg' => array(
            'name'            => 'xstore_compare_icon_custom_svg',
            'type'            => 'image',
            'settings'        => 'xstore_compare_icon_custom_svg',
            'label'           => $strings['label']['custom_image_svg'],
            'tooltip'     => $strings['description']['custom_image_svg'],
            'section'  => 'xstore-compare',
            'default'         => '',
            'choices'         => array(
                'save_as' => 'array',
            ),
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
                array(
                    'setting'  => 'xstore_compare_icon',
                    'operator' => '==',
                    'value'    => 'custom',
                ),
            ),
        ),

        // xstore_compare_label_add_to_compare
        'xstore_compare_label_add_to_compare'              => array(
            'name'     => 'xstore_compare_label_add_to_compare',
            'type'     => 'etheme-text',
            'settings' => 'xstore_compare_label_add_to_compare',
            'label'    => esc_html__( '"Add to compare" text', 'xstore-core' ),
            'tooltip'  => esc_html__( 'Customize the title text for adding a product to the compare list action, with the default value being "Add to compare".', 'xstore-core' ),
            'section'  => 'xstore-compare',
            'default'  => esc_html__( 'Add to compare', 'xstore-core' ),
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

        // xstore_compare_label_browse_compare
        'xstore_compare_label_browse_compare'              => array(
            'name'     => 'xstore_compare_label_browse_compare',
            'type'     => 'etheme-text',
            'settings' => 'xstore_compare_label_browse_compare',
            'label'    => esc_html__( '"Remove from compare" text', 'xstore-core' ),
            'tooltip'  => esc_html__( 'Customize the title text for removing a product from the compare list action, with the default value being "Remove".', 'xstore-core'),
            'section'  => 'xstore-compare',
            'default'  => esc_html__( 'Remove', 'xstore-core' ),
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

        'xstore_compare_notify_type' => array(
            'name'            => 'xstore_compare_notify_type',
            'type'            => 'select',
            'settings'        => 'xstore_compare_notify_type',
            'label'           => esc_html__( 'Product added notification', 'xstore-core' ),
            'tooltip' => esc_html__( 'Choose the type of notification that will be displayed when the product is added to the compare list.', 'xstore-core' ),
            'section'  => 'xstore-compare',
            'default'         => 'alert',
            'choices'         => array(
                'none'      => esc_html__( 'None', 'xstore-core' ),
                'alert'     => esc_html__( 'Alert', 'xstore-core' ),
                'alert_advanced'     => esc_html__( 'Alert advanced', 'xstore-core' ),
                'mini_compare' => esc_html__( 'Open compare Off-canvas/dropdown content', 'xstore-core' ),
            ),
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

//        'xstore_compare_animated_hearts' => array(
//            'name'     => 'xstore_compare_animated_hearts',
//            'type'     => 'toggle',
//            'settings' => 'xstore_compare_animated_hearts',
//            'label'    => __( 'Animated hearts', 'xstore-core' ),
//            'section'  => 'xstore-compare',
//            'default'  => 1,
//            'active_callback' => array(
//                array(
//                    'setting'  => 'xstore_compare',
//                    'operator' => '!=',
//                    'value'    => '0',
//                ),
//            )
//        ),

        'xstore_compare_cache_time' => array(
            'name'            => 'xstore_compare_cache_time',
            'type'            => 'select',
            'settings'        => 'xstore_compare_cache_time',
            'label'           => esc_html__( 'Cache lifespan', 'xstore-core' ),
            'tooltip' => esc_html__( 'Specify the time after which the customer compare items cache will be cleared. Note: the customer compare list items will be kept in the cache for the time you set in this option or until the browser cookies are cleared. This will add an additional cookie to the customer\'s browser with the following parameters: name: "xstore_compare_ids_0", purpose: "To store Built-in Compare product information", expiry: "7 days by default".', 'xstore-core' ) . '<br/>' .
                esc_html__('Note: Please remember to include this in the security policy (GDPR).', 'xstore-core'),
            'section'  => 'xstore-compare',
            'default'         => 'week',
            'choices'         => array(
                'day' => esc_html__('One day', 'xstore-core'),
                'week' => esc_html__('One week', 'xstore-core'),
                'month' => esc_html__('One month', 'xstore-core'),
                '3months' => esc_html__('Three months', 'xstore-core'),
                'year' => esc_html__('One year', 'xstore-core'),
            ),
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

        // go to product header compare
        'go_to_section_header_compare'                 => array(
            'name'     => 'go_to_section_header_compare',
            'type'     => 'custom',
            'settings' => 'go_to_section_header_compare',
            'section'  => 'xstore-compare',
            'default'  => '<span class="et_edit" data-parent="compare" data-section="compare_content_separator" style="padding: 6px 7px; border-radius: 5px; background: var(--customizer-dark-color, #222); color: var(--customizer-white-color, #fff); font-size: calc(var(--customizer-ui-content-zoom, 1) * 12px); text-align: center;">' . esc_html__( 'Header Compare', 'xstore-core' ) . '</span>',
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

        // go to product single product compare
        'go_to_section_product_compare'                 => array(
            'name'     => 'go_to_section_product_compare',
            'type'     => 'custom',
            'settings' => 'go_to_section_product_compare',
            'section'  => 'xstore-compare',
            'default'  => '<span class="et_edit" data-parent="'.($is_spb ? 'product_compare' : 'single-product-page-compare').'" data-section="'.($is_spb ? 'product_compare_content_separator' : 'xstore_compare_single_product_position').'" style="padding: 6px 7px; border-radius: 5px; background: var(--customizer-dark-color, #222); color: var(--customizer-white-color, #fff); font-size: calc(var(--customizer-ui-content-zoom, 1) * 12px); text-align: center;">' . esc_html__( 'Single Product Compare', 'xstore-core' ) . '</span>',
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

        // content separator
        'xstore_compare_page_content_separator' => array(
            'name'     => 'xstore_compare_page_content_separator',
            'type'     => 'custom',
            'settings' => 'xstore_compare_page_content_separator',
            'section'  => 'xstore-compare',
            'default' => '<div style="' . $sep_style . '"><span class="dashicons dashicons-update-alt"></span> <span style="padding-inline-start: 5px;">' . esc_html__('Compare page settings', 'xstore-core') . '</span></div>',
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

        'xstore_compare_page' => array(
            'name'            => 'xstore_compare_page',
            'type'            => 'select',
            'settings'        => 'xstore_compare_page',
            'label'           => esc_html__( 'Compare page', 'xstore-core' ),
            'tooltip'     => esc_html__( 'Choose a page to be the main Compare page. Make sure to add the [xstore_compare_page] shortcode to the page content.', 'xstore-core') . '<br/>' .
                esc_html__('Choose the "Dynamic page" option to create a compare page based on the "Account" page link, with a few extra query parameters added to the URL.', 'xstore-core'),
            'section'  => 'xstore-compare',
            'default'         => '',
            'choices'         => $select_pages,
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

        // account_content_alignment
        'xstore_compare_page_content_alignment' => array(
            'name'      => 'xstore_compare_page_content_alignment',
            'type'      => 'radio-buttonset',
            'settings'  => 'xstore_compare_page_content_alignment',
            'label'     => $strings['label']['alignment'],
            'tooltip'   => esc_html__('Using this option, you can choose an alignment value for the content in compare table.', 'xstore-core'),
            'section'  => 'xstore-compare',
            'default'   => 'center',
            'choices'   => $choices['alignment'],
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            ),
            'transport' => 'auto',
            'output'    => array(
                array(
                    'context'  => array( 'editor', 'front' ),
                    'element'  => '.xstore-compare-items td',
                    'property' => 'text-align'
                ),
            ),
        ),

        'xstore_compare_page_content' => array(
            'name'            => 'xstore_compare_page_content',
            'type'            => 'sortable',
            'settings'        => 'xstore_compare_page_content',
            'label'           => esc_html__( 'Table content', 'xstore-core' ),
            'tooltip'     => esc_html__( 'Revamp the contents of the compare page easily by turning on or off the necessary elements.', 'xstore-core' ),
            'section'  => 'xstore-compare',
            'default'         => array(
                'action',
                'product',
                'title',
                'quantity',
                'price',
                'rating',
                'button',
                'excerpt',
                'stock_status',
                'brand',
                'sku',
//                'gtin',
//                'attributes',
            ),
            'choices'         => array(
                'action' => esc_html__('Action', 'xstore-core'),
                'product'  => esc_html__('Image', 'xstore-core'),
                'title'  => esc_html__('Product name', 'xstore-core'),
                'quantity'  => esc_html__('Quantity', 'xstore-core'),
                'price'  => esc_html__('Price', 'xstore-core'),
                'rating'  => esc_html__('Rating', 'xstore-core'),
                'button'  => esc_html__('Product button', 'xstore-core'),
                'excerpt'  => esc_html__('Short description', 'xstore-core'),
                'stock_status'  => esc_html__('Stock status', 'xstore-core'),
                'brand'  => esc_html__('Product brand', 'xstore-core'),
                'sku'  => esc_html__('SKU', 'xstore-core'),
                'gtin'  => esc_html__('GTIN', 'xstore-core'),
                'attributes'  => esc_html__('Attributes', 'xstore-core'),
            ),
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

        'xstore_compare_empty_page_content' => array(
            'name'        => 'xstore_compare_empty_page_content',
            'type'        => 'editor',
            'settings'    => 'xstore_compare_empty_page_content',
            'label'       => esc_html__( 'Empty compare content', 'xstore-core' ),
            'tooltip'     => $strings['description']['editor_control'] . '<br/>' .
                esc_html__('Leave the content blank to use the default content.', 'xstore-core'),
            'section'  => 'xstore-compare',
            'default'     => '<h1 style="text-align: center;">Your compare is empty</h1><p style="text-align: center;">We invite you to get acquainted with an assortment of our shop. Surely you can find something for yourself!</p> ',
            'active_callback' => array(
                array(
                    'setting'  => 'xstore_compare',
                    'operator' => '!=',
                    'value'    => '0',
                ),
            )
        ),

	);
	
	return array_merge( $fields, $args );
	
} );