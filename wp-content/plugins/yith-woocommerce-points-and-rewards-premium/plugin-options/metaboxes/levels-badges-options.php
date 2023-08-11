<?php
/**
 * Levels and Badges metabox options
 *
 * @package YITH WooCommerce Points and Rewards Premium
 * @since   2.2.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

$options = array(
	array(
		'id'      => 'ywpar_lb_name',
		'name'    => 'name',
		'type'    => 'text',
		'default' => 1,
		'title'   => esc_html_x( 'Level name', 'Admin option title', 'yith-woocommerce-points-and-rewards' ),
		'desc'    => esc_html_x( 'Enter a name to identify this level', 'Admin option description', 'yith-woocommerce-points-and-rewards' ),
		'std'     => '',
	),
	array(
		'id'    => 'status',
		'name'  => 'status',
		'title' => '',
		'type'  => 'hidden',
		'std'   => 'on',
	),
	array(
		'id'    => 'ywpar_lb_points_to_collect',
		'name'  => 'points_to_collect',
		'type'  => 'options-levels-badges-range',
		'title' => esc_html_x( 'Points to collect', 'Admin option title', 'yith-woocommerce-points-and-rewards' ),
		'desc'  => sprintf( '%s <br> %s', esc_html_x( 'Set how many points the user has to collect to achieve this level.', 'Admin option description', 'yith-woocommerce-points-and-rewards' ), esc_html_x( 'Leave "to" empty if this level is the last achievable.', 'Admin option description', 'yith-woocommerce-points-and-rewards' ) ),
		'std'   => array(),
	),
	array(
		'id'    => 'ywpar_lb_badge_enabled',
		'name'  => 'badge_enabled',
		'type'  => 'onoff',
		'title' => esc_html_x( 'Add a badge image', 'Admin option title', 'yith-woocommerce-points-and-rewards' ),
		'desc'  => esc_html_x( 'Enable if you want to upload a badge image to identify this level', 'Admin option description', 'yith-woocommerce-points-and-rewards' ),
		'std'   => 'no',
	),
	array(
		'id'                => 'ywpar_lb_badge_image',
		'name'              => 'image',
		'type'              => 'upload',
		'title'             => esc_html_x( 'Upload badge', 'Admin option title', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html_x( 'Upload an image to identify this level', 'Admin option description', 'yith-woocommerce-points-and-rewards' ),
		'std'               => '',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_lb_badge_enabled',
			'data-deps_value' => 'yes',
		),
	),
	array(
		'id'            => 'ywpar_lb_level_color',
		'name'          => 'level_color',
		'type'          => 'colorpicker',
		'title'         => esc_html_x( 'Level text color', 'Admin option title', 'yith-woocommerce-points-and-rewards' ),
		'desc'          => esc_html_x( 'Set the color for level label text (shown in shortcodes and widgets)', 'Admin option description', 'yith-woocommerce-points-and-rewards' ),
		'std'           => '#000000',
		'alpha_enabled' => false,
	),
);

return apply_filters( 'ywpar_levels_badges_options', $options );
