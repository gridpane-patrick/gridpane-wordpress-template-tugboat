<?php
/**
 * Gutenberg options
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

$product_id      = 0;
$args            = array(
	'posts_per_page' => 1,
	'orderby'        => 'rand',
	'post_type'      => 'product',
);
$random_products = get_posts( $args );
if ( $random_products ) {
	foreach ( $random_products as $product ) {
		$product_id = $product->ID;
	}
}

$colors = ywpar_get_option(
	'single_product_points_message_colors',
	array(
		'text_color'       => '#000000',
		'background_color' => 'rgba(255,255,255,0)',
	)
);

$blocks = array(
	'yith-ywpar-customers-points'       => array(
		'style'                        => 'yith-ywraq-gutenberg',
		'title'                        => esc_html_x( 'Best user list - YITH WooCommerce Points and Rewards', '[gutenberg]: block name', 'yith-woocommerce-points-and-rewards' ),
		'description'                  => esc_html_x( 'Show the list of best customers.', '[gutenberg]: block description', 'yith-woocommerce-points-and-rewards' ),
		'shortcode_name'               => 'ywpar_customers_points',
		'elementor_map_from_gutenberg' => true,
		'elementor_icon'               => 'eicon-favorite',
		'do_shortcode'                 => 'yes',
		'keywords'                     => array(
			esc_html_x( 'YITH', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
			esc_html_x( 'Points and Rewards', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
			esc_html_x( 'Points and Rewards Widget', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
		),
		'attributes'                   => array(
			'style'            => array(
				'type'    => 'radio',
				'label'   => esc_html_x( 'Style of list', '[gutenberg]: Name of gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				'default' => 'simple',
				'options' => array(
					'simple' => esc_html_x( 'Simple', '[gutenberg]: Label for gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
					'boxed'  => esc_html_x( 'Boxed', '[gutenberg]: Label for gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				),
			),
			'tabs'             => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Tabs', '[gutenberg]: show or hide the tabs', 'yith-woocommerce-points-and-rewards' ),
				'default' => true,
				'helps'   => array(
					'yes' => esc_html__( 'Yes', 'yith-woocommerce-points-and-rewards' ),
					'no'  => esc_html__( 'No', 'yith-woocommerce-points-and-rewards' ),
				),
			),
			'num_of_customers' => array(
				'type'    => 'number',
				'label'   => esc_html_x( 'Number of customers', '[gutenberg]: number od customers to show', 'yith-woocommerce-points-and-rewards' ),
				'default' => 3,
			),
		),
	),
	'yith-ywpar-points'                 => array(
		'style'                        => 'yith-ywraq-gutenberg',
		'title'                        => esc_html_x( 'Customer Total points - YITH WooCommerce Points and Rewards', '[gutenberg]: block name', 'yith-woocommerce-points-and-rewards' ),
		'description'                  => esc_html_x( 'Show customer credit points.', '[gutenberg]: block description', 'yith-woocommerce-points-and-rewards' ),
		'shortcode_name'               => 'yith_ywpar_points',
		'elementor_map_from_gutenberg' => true,
		'elementor_icon'               => 'eicon-person',
		'do_shortcode'                 => 'yes',
		'keywords'                     => array(
			esc_html_x( 'Points and Rewards', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
			esc_html_x( 'Points', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
		),
		'attributes'                   => array(
			'label' => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Text before points', '[gutenberg]: Name of gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				'default' => esc_html_x( 'Your credit is ', '[gutenberg]: Label for gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
			),
		),
	),
	'yith-ywpar-points-product-message' => array(
		'style'                        => 'yith-ywraq-gutenberg',
		'title'                        => esc_html_x( 'Points message on product page - YITH WooCommerce Points and Rewards', '[gutenberg]: block name', 'yith-woocommerce-points-and-rewards' ),
		'description'                  => esc_html_x( 'Show a single product page message', '[gutenberg]: block description', 'yith-woocommerce-points-and-rewards' ),
		'shortcode_name'               => 'yith_points_product_message',
		'elementor_map_from_gutenberg' => true,
		'elementor_icon'               => 'eicon-product-description',
		'do_shortcode'                 => 'yes',
		'keywords'                     => array(
			esc_html_x( 'Points and Rewards', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
			esc_html_x( 'Points message', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
		),
		'attributes'                   => array(
			'product_id'       => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Product id', '[gutenberg]: Name of gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				'default' => $product_id,
			),
			'message'          => array(
				'type'    => 'textarea',
				'label'   => esc_html_x( 'Message', '[gutenberg]: Name of gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				'default' => ywpar_get_option( 'single_product_message' ),
			),
			'text_color'       => array(
				'type'    => 'colorpicker',
				'label'   => esc_html_x( 'Text Color', '[gutenberg]: title of widget', 'yith-woocommerce-points-and-rewards' ),
				'default' => apply_filters( 'ywpar_single_product_message_text_color', $colors['text_color'] ),
			),
			'background_color' => array(
				'type'    => 'colorpicker',
				'label'   => esc_html_x( 'Background Color', '[gutenberg]: title of widget', 'yith-woocommerce-points-and-rewards' ),
				'default' => '#fff',
			),
		),
	),
);
return apply_filters( 'ywraq_gutenberg_blocks', $blocks );
