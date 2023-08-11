<?php
/**
 * Plugin Options
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;
$currency = get_woocommerce_currency();

return array(
	'share' => array(
		// REDEEMING.
		'rewards_point_option'              => array(
			'name' => esc_html__( 'Share points', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_rewards_point_option',
		),

		'enable_sharing'                      => array(
			'name'      => esc_html__( 'Allow users to convert points in a coupon to share', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Enable to allow customers to convert their points in a coupon code in the My Account page', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'yes',
			'id'        => 'ywpar_enable_sharing',
		),

		'apply_limits_to_share_coupon'      => array(
			'name'              => esc_html__( 'Apply limits to points coupons', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Enable to set a minimum or maximum of points that can be converted into a coupon code', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'onoff',
			'default'           => 'yes',
			'id'                => 'ywpar_apply_limits_to_share_coupon',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_share',
				'data-deps_value' => 'yes',
			),
			'default'           => 'off',
		),

		'mim_limit_to_share_coupon'         => array(
			'name'              => esc_html__( 'Minimum amount of points that can be converted', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set the minimum number of points that the user can convert into a coupon code', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 1,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_share,ywpar_apply_limits_to_share_coupon',
				'data-deps_value' => 'yes,yes',
				'style'           => 'width:70px',
				'data-desc'       => esc_html__( 'points', 'yith-woocommerce-points-and-rewards' ),
			),
			'id'                => 'ywpar_min_limit_to_share_coupon',
		),

		'max_limit_to_share_coupon'         => array(
			'name'              => esc_html__( 'Maximum amount of points that can be converted', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set the maximum number of points that the user can convert into a coupon code', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 100,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_share,ywpar_apply_limits_to_share_coupon',
				'data-deps_value' => 'yes,yes',
				'style'           => 'width:70px',
				'data-desc'       => esc_html__( 'points', 'yith-woocommerce-points-and-rewards' ),
			),
			'id'                => 'ywpar_max_limit_to_share_coupon',
		),

		'enable_expiration_to_share_coupon' => array(
			'name'              => esc_html__( 'Set the expiration for points coupon codes', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Enable to set an expiry date for points coupon codes to be used', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'onoff',
			'default'           => 'yes',
			'id'                => 'ywpar_enable_expiration_to_share_coupon',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_share',
				'data-deps_value' => 'yes',
			),
			'default'           => 'off',
		),

		'expiration_time_of_share_coupon'   => array(
			'name'              => esc_html__( 'Points coupon will expire after', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set after how many days a points coupon code will expire', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 30,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_share,ywpar_enable_expiration_to_share_coupon',
				'data-deps_value' => 'yes,yes',
				'style'           => 'width:70px',
				'data-desc'       => esc_html__( 'days', 'yith-woocommerce-points-and-rewards' ),
			),
			'id'                => 'ywpar_expiration_time_of_share_coupon',
		),

		'rewards_point_option_option_end'   => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_rewards_point_option_end',
		),

	),
);
