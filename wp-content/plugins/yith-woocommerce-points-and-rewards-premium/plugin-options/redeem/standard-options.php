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

$redeem_options = array(
	// REDEEMING.
	'rewards_point_option'                      => array(
		'name' => esc_html__( 'Points redeeming', 'yith-woocommerce-points-and-rewards' ),
		'type' => 'title',
		'id'   => 'ywpar_rewards_point_option',
	),

	'enable_rewards_points'                     => array(
		'name'      => esc_html__( 'Allow users to redeem points', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Choose if a user can redeem points automatically or if you want to manage points redeeming manually', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
		'id'        => 'ywpar_enable_rewards_points',
	),

	'user_enabled_to_redeem'                    => array(
		'name'              => esc_html__( 'User that can redeem points', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => __( 'Choose if all users can redeem points or only specified user roles can do that. ', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'yith-field',
		'yith-type'         => 'radio',
		'id'                => 'ywpar_user_role_redeem_type',
		'default'           => 'all',
		'options'           => apply_filters(
			'ywpar_user_role_redeem_type_options',
			array(
				'all'   => esc_html__( 'All', 'yith-woocommerce-points-and-rewards' ),
				'roles' => esc_html__( 'Only specified user roles', 'yith-woocommerce-points-and-rewards' ),
			)
		),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points',
			'data-deps_value' => 'yes',
		),

	),

	'user_role_redeem_enabled'                  => array(
		'name'              => esc_html__( 'User roles', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose which user roles can redeem points', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'yith-field',
		'yith-type'         => 'select',
		'class'             => 'wc-enhanced-select',
		'css'               => 'min-width:300px',
		'multiple'          => true,
		'id'                => 'ywpar_user_role_redeem_enabled',
		'options'           => yith_ywpar_get_roles(),
		'default'           => array(),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points,ywpar_user_role_redeem_type',
			'data-deps_value' => 'yes,roles',
		),
	),


	'conversion_rate_method'                    => array(
		'name'              => esc_html__( 'Reward Conversion Method', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose how to apply the discount. The discount can either be a percent or a fixed amount', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'yith-field',
		'yith-type'         => 'radio',
		'default'           => 'fixed',
		'options'           => array(
			'fixed'      => esc_html__( 'Fixed Price Discount', 'yith-woocommerce-points-and-rewards' ),
			'percentage' => esc_html__( 'Percentage Discount', 'yith-woocommerce-points-and-rewards' ),
		),
		'id'                => 'ywpar_conversion_rate_method',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points',
			'data-deps_value' => 'yes',
		),
	),

	'rewards_conversion_rate'                   => array(
		'name'              => esc_html__( 'Reward Conversion Rate', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose how to calculate the discount when customers use their available points', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'options-conversion',
		'type'              => 'yith-field',
		'class'             => 'fixed_method',
		'default'           => array(
			$currency => array(
				'points' => 100,
				'money'  => 1,
			),
		),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points,ywpar_conversion_rate_method',
			'data-deps_value' => 'yes,fixed',
		),
		'id'                => 'ywpar_rewards_conversion_rate',
	),

	'rewards_percentage_conversion_rate'        => array(
		'name'              => esc_html__( 'Reward Conversion Rate', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => __( 'Choose how to calculate the discount when customers use their available points', 'yith-woocommerce-points-and-rewards' ) . '<a name="redeem-role-option"></a>',
		'yith-type'         => 'options-percentage-conversion',
		'type'              => 'yith-field',
		'default'           => array(
			$currency => array(
				'points'   => 20,
				'discount' => 5,
			),
		),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points,ywpar_conversion_rate_method',
			'data-deps_value' => 'yes,percentage',
		),
		'id'                => 'ywpar_rewards_percentual_conversion_rate',
	),

	'redeem_prices_tax'                         => array(
		'name'              => esc_html__( 'When redeeming, calculate the discount on the product price with:', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose whether to calculate the redeem discount on prices with prices with taxes or without taxes', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'yith-field',
		'yith-type'         => 'radio',
		'id'                => 'ywpar_redeem_prices_tax',
		'options'           => array(
			'incl' => esc_html__( 'taxes included', 'yith-woocommerce-points-and-rewards' ),
			'excl' => esc_html__( 'taxes excluded', 'yith-woocommerce-points-and-rewards' ),
		),
		'default'           => 'yes' === get_option( 'woocommerce_prices_include_tax' ) ? 'incl' : 'excl',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points',
			'data-deps_value' => 'yes',
		),
	),

	'redeeem_exclude_product_on_sale'               => array(
		'name'              => esc_html__( 'Exclude on sale products from the discount amount calculation', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'If enabled, sale products will not be used to redeem points', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'onoff',
		'type'              => 'yith-field',
		'id'                => 'ywpar_redeeem_exclude_product_on_sale',
		'default'           => 'no',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points',
			'data-deps_value' => 'yes',
		),
	),

	'autoapply_points_cart_checkout'            => array(
		'name'              => esc_html__( 'Automatically redeem points on Cart/Checkout Page', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enable to automatically apply points on the cart/checkout page', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'yith-field',
		'yith-type'         => 'onoff',
		'default'           => 'no',
		'id'                => 'ywpar_autoapply_points_cart_checkout',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points',
			'data-deps_value' => 'yes',
		),

	),

	'enabled_rewards_cart_message_layout_style' => array(
		'name'      => esc_html__( 'Redeem box style', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Choose the style for the redeem points section', 'yith-woocommerce-points-and-rewards' ),
		'yith-type' => 'radio',
		'type'      => 'yith-field',
		'options'   => array(
			'default' => esc_html__( 'Default', 'yith-woocommerce-points-and-rewards' ),
			'custom'  => esc_html__( 'Custom', 'yith-woocommerce-points-and-rewards' ),
		),
		'id'        => 'ywpar_enabled_rewards_cart_message_layout_style',
		'default'   => 'default',
		'deps'      => array(
			'id'    => 'ywpar_enable_rewards_points',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),

	'rewards_cart_message'                      => array(
		'name'              => esc_html__( 'Redeem message in Cart and Checkout', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => _x( ' Enter the redeem message to show in cart and checkout page. <br />You can use these placeholders:<br />{points} number of points earned;<br>{points_label} of points;<br>{max_discount} maximum discount value', 'do not translate the text inside the brackets', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'textarea-editor',
		'type'              => 'yith-field',
		'default'           => _x( 'Use <strong>{points}</strong> {points_label} for a <strong>{max_discount}</strong> discount on this order!', 'do not translate the text inside the brackets', 'yith-woocommerce-points-and-rewards' ),
		'id'                => 'ywpar_rewards_cart_message',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points,ywpar_enabled_rewards_cart_message_layout_style',
			'data-deps_value' => 'yes,custom',
		),
		'textarea_rows'     => 5,
	),

	'allow_free_shipping_to_redeem'             => array(
		'name'              => esc_html__( 'Offer free shipping when user redeem', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enable to offer free shipping to users that redeem their points. A free shipping method must be enabled and set up in your shipping zones to generate “a valid free shipping coupon”', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'onoff',
		'type'              => 'yith-field',
		'id'                => 'ywpar_allow_free_shipping_to_redeem',
		'default'           => 'no',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points',
			'data-deps_value' => 'yes',
		),
	),

	'other_coupons'                             => array(
		'name'              => esc_html__( 'Coupons allowed', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Select if you want to allow the use of point-redemption coupons, WooCommerce coupons or both', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'radio',
		'type'              => 'yith-field',
		'options'           => array(
			'wc_coupon' => esc_html__( 'Use only WooCommerce coupons', 'yith-woocommerce-points-and-rewards' ),
			'ywpar'     => esc_html__( 'Use only points-redemption coupon', 'yith-woocommerce-points-and-rewards' ),
			'both'      => esc_html__( 'Use both coupons', 'yith-woocommerce-points-and-rewards' ),

		),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points',
			'data-deps_value' => 'yes',
		),
		'default'           => 'both',
		'id'                => 'ywpar_other_coupons',
	),

	'rewards_point_option_end'                  => array(
		'type' => 'sectionend',
		'id'   => 'ywpar_rewards_point_option_end',
	),

	'min_max_option'                            => array(
		'name' => esc_html__( 'Redeeming restrictions', 'yith-woocommerce-points-and-rewards' ),
		'type' => 'title',
		'id'   => 'ywpar_min_max_option',
	),

	'enable_redeem_restrictions'                => array(
		'name'              => esc_html__( 'Apply redeeming restrictions', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enable to setup redeeming restrictions for your users', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'onoff',
		'type'              => 'yith-field',
		'id'                => 'ywpar_apply_redeem_restrictions',
		'default'           => 'no',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points',
			'data-deps_value' => 'yes',
		),
	),

	// showed if conversion_rate_method == percentage.
	'min_percentual_discount'                   => array(
		'name'              => esc_html__( 'Minimum discount users can get', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( '( in %) Set minimum discount percentage allowed in cart when redeeming points', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'text',
		'type'              => 'yith-field',
		'id'                => 'ywpar_min_percentual_discount',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points,ywpar_apply_redeem_restrictions,ywpar_conversion_rate_method',
			'data-deps_value' => 'yes,yes,percentage',
		),
		'default'           => '',
	),

	// showed if conversion_rate_method == percentage.
	'max_percentual_discount'                   => array(
		'name'              => esc_html__( 'Maximum discount users can get', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( '( in %) Set minimum discount percentage allowed in cart when redeeming points', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'text',
		'type'              => 'yith-field',
		'id'                => 'ywpar_max_percentual_discount',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points,ywpar_apply_redeem_restrictions,ywpar_conversion_rate_method',
			'data-deps_value' => 'yes,yes,percentage',
		),
		'default'           => '50',
	),

	// showed if conversion_rate_method == fixed.
	'max_points_discount'                       => array(
		'name'              => esc_html__( 'Maximum discount users can get', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Set the maximum discount amount that your users can get when they redeem their points', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'options-restrictions-text-input',
		'type'              => 'yith-field',
		'id'                => 'ywpar_max_points_discount',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points,ywpar_apply_redeem_restrictions,ywpar_conversion_rate_method',
			'data-deps_value' => 'yes,yes,fixed',
		),
	),

	'minimum_amount_to_redeem'                  => array(
		'name'              => esc_html__( 'Minimum cart amount to redeem points', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Set the minimum cart amount required to redeem points', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'options-restrictions-text-input',
		'type'              => 'yith-field',
		'default'           => '',
		'id'                => 'ywpar_minimum_amount_to_redeem',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points,ywpar_apply_redeem_restrictions',
			'data-deps_value' => 'yes,yes',
		),

	),

	// showed if conversion_rate_method == fixed.
	'minimum_amount_discount_to_redeem'         => array(
		'name'              => esc_html__( 'Minimum discount required to redeem', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Set the minimum discount to redeem points', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'options-restrictions-text-input',
		'type'              => 'yith-field',
		'id'                => 'ywpar_minimum_amount_discount_to_redeem',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points,ywpar_apply_redeem_restrictions,ywpar_conversion_rate_method',
			'data-deps_value' => 'yes,yes,fixed',
		),
	),

	// showed if conversion_rate_method == fixed.
	'max_points_product_discount'               => array(
		'name'              => esc_html__( 'Maximum discount for a single product', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Set the maximum discount that can be applied per product', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'options-restrictions-text-input',
		'type'              => 'yith-field',
		'id'                => 'ywpar_max_points_product_discount',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_enable_rewards_points,ywpar_apply_redeem_restrictions,ywpar_conversion_rate_method',
			'data-deps_value' => 'yes,yes,fixed',
		),
	),

	'min_max_option_end'                        => array(
		'type' => 'sectionend',
		'id'   => 'ywpar_min_max_option_end',
	),

);

return array(
	'redeem-standard' => apply_filters( 'ywpar_redeem_standard_options', $redeem_options ),
);
