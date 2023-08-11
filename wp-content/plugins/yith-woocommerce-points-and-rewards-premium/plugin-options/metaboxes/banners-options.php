<?php
/**
 * Banner Options
 *
 * @package YITH WooCommerce Points and Rewards Premium
 * @since   2.2.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

/* get target options by target extra points active */
$defaul_target_options = array(
	'enable_point_on_achieve_level_exp' => esc_html__( 'Points of next level', 'yith-woocommerce-points-and-rewards' ),
	'enable_amount_spent_exp'           => esc_html__( 'Extra points for amount spent', 'yith-woocommerce-points-and-rewards' ),
	'enable_number_of_points_exp'       => esc_html__( 'Extra points for points collected', 'yith-woocommerce-points-and-rewards' ),
);

$defaul_get_points_options = array(
	'enable_points_on_referral_registration_exp' => esc_html__( 'Refer a friend', 'yith-woocommerce-points-and-rewards' ),
	'enable_points_on_referral_purchase_exp'     => esc_html__( 'Refer a friend ', 'yith-woocommerce-points-and-rewards' ),
	'enable_review_exp'                          => esc_html__( 'Leave a review', 'yith-woocommerce-points-and-rewards' ),
	'enable_points_on_completed_profile_exp'     => esc_html__( 'Complete Profile', 'yith-woocommerce-points-and-rewards' ),
);

$active_extra_options = ywpar_get_active_extra_points_rules();
$target_options       = array();
$get_points_options   = array();

foreach ( $active_extra_options as $ao ) {
	if ( array_key_exists( $ao, $defaul_target_options ) ) {
		$target_options[ $ao ] = $defaul_target_options[ $ao ];
	}
}

foreach ( $active_extra_options as $ao ) {
	if ( array_key_exists( $ao, $defaul_get_points_options ) ) {
		$get_points_options[ $ao ] = $defaul_get_points_options[ $ao ];
	}
}


$banner_colors       = isset( $post ) ? get_post_meta( $post->ID, 'ywpar_banner_colors', true ) : array();
$progress_bar_colors = isset( $post ) ? get_post_meta( $post->ID, 'ywpar_banner_pbar_colors', true ) : array();

$options = array(
	array(
		'id'      => 'ywpar_banner_name',
		'name'    => 'name',
		'type'    => 'text',
		'default' => 1,
		'title'   => __( 'Banner name', 'yith-woocommerce-points-and-rewards' ),
		'desc'    => __( 'Enter a name to identify this banner', 'yith-woocommerce-points-and-rewards' ),
		'std'     => '',
	),
	array(
		'id'    => 'ywpar_banner_status',
		'name'  => 'status',
		'title' => '',
		'type'  => 'hidden',
		'std'   => 'on',
	),
	array(
		'id'    => 'ywpar_banner_priority',
		'name'  => 'priority',
		'title' => '',
		'type'  => 'hidden',
		'std'   => 1,
	),
	array(
		'id'      => 'ywpar_banner_type',
		'name'    => 'type',
		'type'    => 'select',
		'options' => array(
			'simple'     => __( 'Simple', 'yith-woocommerce-points-and-rewards' ),
			'target'     => __( 'Target', 'yith-woocommerce-points-and-rewards' ),
			'get_points' => __( 'Get points', 'yith-woocommerce-points-and-rewards' ),
		),
		'title'   => esc_html__( 'Banner type', 'yith-woocommerce-points-and-rewards' ),
		'desc'    => esc_html__( 'Choose the banner type', 'yith-woocommerce-points-and-rewards' ),
		'std'     => 'target',
	),
	array(
		'id'                => 'ywpar_banner_action_type',
		'name'              => 'action_type',
		'type'              => 'select',
		'options'           => $get_points_options,
		'title'             => esc_html__( 'Action type', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose the banner action type. To create target banners you need to enable extra points options first.', 'yith-woocommerce-points-and-rewards' ),
		'std'               => 'simple',
		'class'             => 'wc-enhanced-select',
		'placeholder'       => empty( $get_points_options ) ? __( ' No extra rules found', 'yith-woocommerce-points-and-rewards' ) : '',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_banner_type',
			'data-deps_value' => 'get_points',

		),
	),
	array(
		'id'                => 'ywpar_banner_action_target_type',
		'name'              => 'action_target_type',
		'type'              => 'select',
		'options'           => $target_options,
		'title'             => esc_html__( 'Action type', 'yith-woocommerce-points-and-rewards' ),
		// translators: placeholder is an html tag.
		'desc'              => sprintf( esc_html_x( 'Choose the banner action type.%s To create target banners you need to enable extra points options first.', 'placeholder is an html tag', 'yith-woocommerce-points-and-rewards' ), '<br/>' ),
		'std'               => 'simple',
		'class'             => 'wc-enhanced-select',
		'placeholder'       => empty( $target_options ) ? __( ' No extra rules found', 'yith-woocommerce-points-and-rewards' ) : '',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_banner_type',
			'data-deps_value' => 'target',
		),
	),
	array(
		'id'                => 'ywpar_max_review_products_to_show',
		'name'              => 'max_review_products_to_show',
		'type'              => 'number',
		'title'             => esc_html__( 'Products to show', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enter the maximum number of products the user can review to earn extra points', 'yith-woocommerce-points-and-rewards' ),
		'std'               => 5,
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_banner_type,ywpar_banner_action_type',
			'data-deps_value' => 'get_points,enable_review_exp',
		),
	),

	array(
		'id'                 => 'ywpar_banner_title',
		'name'               => 'title',
		'type'               => 'text',
		'title'              => esc_html__( 'Banner title', 'yith-woocommerce-points-and-rewards' ),
		'desc'               => esc_html__( 'Enter the title to show in this banner', 'yith-woocommerce-points-and-rewards' ),
		'std'                => '',
		'precompiled_values' => ywpar_get_banner_precompiled_titles(),
	),
	array(
		'id'                 => 'ywpar_banner_subtitle',
		'name'               => 'subtitle',
		'type'               => 'text',
		'title'              => esc_html__( 'Banner text', 'yith-woocommerce-points-and-rewards' ),
		'desc'               => esc_html__( 'Enter the text to show in this banner', 'yith-woocommerce-points-and-rewards' ),
		'std'                => '',
		'precompiled_values' => ywpar_get_banner_precompiled_texts(),
	),
	array(
		'id'    => 'ywpar_banner_image',
		'name'  => 'image',
		'type'  => 'upload',
		'title' => esc_html__( 'Banner image', 'yith-woocommerce-points-and-rewards' ),
		'desc'  => esc_html__( 'Upload an image for this banner', 'yith-woocommerce-points-and-rewards' ),
		'std'   => '',
	),
	array(
		'id'           => 'ywpar_banner_colors',
		'name'         => 'banner_colors',
		'type'         => 'multi-colorpicker',
		'title'        => esc_html__( 'Banner colors', 'yith-woocommerce-points-and-rewards' ),
		'desc'         => '',
		'colorpickers' => array(
			array(
				'id'      => 'background',
				'name'    => esc_html__( 'Background', 'yith-woocommerce-points-and-rewards' ),
				'default' => ( isset( $banner_colors['background'] ) ) ? $banner_colors['background'] : '#ebebeb',
			),
			array(
				'id'      => 'title',
				'name'    => esc_html__( 'Title', 'yith-woocommerce-points-and-rewards' ),
				'default' => ( isset( $banner_colors['title'] ) ) ? $banner_colors['title'] : '#424141',
			),
			array(
				'id'      => 'text',
				'name'    => esc_html__( 'Text', 'yith-woocommerce-points-and-rewards' ),
				'default' => ( isset( $banner_colors['text'] ) ) ? $banner_colors['text'] : '#333333',
			),

		),
	),
	array(
		'id'                => 'ywpar_banner_link_status',
		'name'              => 'link_status',
		'type'              => 'onoff',
		'title'             => esc_html__( 'Add link', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enable to add a link to this banner', 'yith-woocommerce-points-and-rewards' ),
		'std'               => 'no',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_banner_type',
			'data-deps_value' => 'simple|target',
		),
	),
	array(
		'id'                => 'ywpar_banner_link_url',
		'name'              => 'link_url',
		'type'              => 'text',
		'title'             => esc_html__( 'Banner links to', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enter the url of this banner', 'yith-woocommerce-points-and-rewards' ),
		'std'               => '',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_banner_type,ywpar_banner_link_status',
			'data-deps_value' => 'simple|target,yes',
		),
	),
	array(
		'id'                => 'ywpar_banner_progress_bar_status',
		'name'              => 'progress_bar_status',
		'type'              => 'onoff',
		'title'             => esc_html__( 'Show progress slider', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enable to show a slider with user progress', 'yith-woocommerce-points-and-rewards' ),
		'std'               => 'no',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_banner_type',
			'data-deps_value' => 'target',
		),
	),

	array(
		'id'                => 'ywpar_banner_pbar_colors',
		'name'              => 'progress_bar_colors',
		'type'              => 'multi-colorpicker',
		'title'             => esc_html__( 'Banner progress bar colors', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => '',
		'colorpickers'      => array(
			array(
				'id'      => 'bar',
				'name'    => esc_html__( 'Bar', 'yith-woocommerce-points-and-rewards' ),
				'default' => ( isset( $progress_bar_colors['bar'] ) ) ? $progress_bar_colors['bar'] : '#ffffff',
			),
			array(
				'id'      => 'progress',
				'name'    => esc_html__( 'Progress', 'yith-woocommerce-points-and-rewards' ),
				'default' => ( isset( $progress_bar_colors['progress'] ) ) ? $progress_bar_colors['progress'] : '#78ac48',
			),
		),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_banner_type,ywpar_banner_progress_bar_status',
			'data-deps_value' => 'target,yes',
		),
	),
	array(
		'id'                => 'ywpar_banner_simple_position',
		'name'              => 'simple_position',
		'type'              => 'radio',
		'title'             => esc_html__( 'Show', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose in which section to show this banner', 'yith-woocommerce-points-and-rewards' ),
		'options'           => array(
			'target'     => esc_html__( 'in Target tab', 'yith-woocommerce-points-and-rewards' ),
			'get_points' => esc_html__( 'in Get points tab', 'yith-woocommerce-points-and-rewards' ),
		),
		'std'               => 'target',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_banner_type',
			'data-deps_value' => 'simple',
		),
	),
);

return apply_filters( 'ywpar_banner_options', $options );
