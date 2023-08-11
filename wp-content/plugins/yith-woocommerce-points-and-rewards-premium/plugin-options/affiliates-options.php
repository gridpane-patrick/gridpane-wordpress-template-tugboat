<?php
/**
 * Plugin Options
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;
$currency = ywpar_get_currency();
$section1 = array(
	'affiliates_title'                     => array(
		'name' => esc_html__( 'YITH WooCommerce Affiliates Integration', 'yith-woocommerce-points-and-rewards' ),
		'type' => 'title',
		'id'   => 'ywpar_affiliates_title',
	),

	'affiliates_enabled'                   => array(
		'name'      => esc_html__( 'Enable Integration', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Enable the integration with YITH WooCommerce Affiliates plugin', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
		'id'        => 'ywpar_affiliates_enabled',
	),

	'affiliates_earning_conversion_points' => array(
		'name'              => esc_html__( 'Points for affiliates', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Select the method to calculate points for your affiliates', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'yith-field',
		'yith-type'         => 'select',
		'default'           => 'fixed',
		'options'           => array(
			'fixed'      => esc_html__( 'Fixed amount of points for each order', 'yith-woocommerce-points-and-rewards' ),
			'percentage' => esc_html__( 'Percent of points earned by customer', 'yith-woocommerce-points-and-rewards' ),
			'conversion' => esc_html__( 'Conversion based on order subtotal', 'yith-woocommerce-points-and-rewards' ),
		),
		'id'                => 'ywpar_affiliates_earning_conversion_points',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_affiliates_enabled',
			'data-deps_value' => 'yes',
		),
	),

	'affiliates_earning_fixed'             => array(
		'name'              => esc_html__( 'Number of points earned for each commission', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => '',
		'type'              => 'yith-field',
		'yith-type'         => 'number',
		'default'           => 0,
		'id'                => 'ywpar_affiliates_earning_fixed',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_affiliates_enabled,ywpar_affiliates_earning_conversion_points',
			'data-deps_value' => 'yes,fixed',
			'style'           => 'width:70px',
		),
	),

	'affiliates_earning_percentage'        => array(
		'name'              => esc_html__( 'Percent of points', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Percent of points earned by customer', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'yith-field',
		'yith-type'         => 'number',
		'default'           => 0,
		'step'              => 1,
		'min'               => 0,
		'max'               => 100,
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_affiliates_enabled,ywpar_affiliates_earning_conversion_points',
			'data-deps_value' => 'yes,percentage',
			'style'           => 'width:70px',
			'data-desc'       => '%',
		),
		'id'                => 'ywpar_affiliates_earning_percentage',

	),

	'affiliates_earning_conversion'        => array(
		'name'              => esc_html__( 'Assign points based on the order subtotal', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Decide how many points will be assigned to each order based on the currency', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'         => 'options-conversion-earning',
		'type'              => 'yith-field',
		'default'           => array(
			$currency => array(
				'points' => 1,
				'money'  => 10,
			),
		),
		'id'                => 'ywpar_affiliates_earning_conversion',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_affiliates_enabled,ywpar_affiliates_earning_conversion_points',
			'data-deps_value' => 'yes,conversion',
		),
	),

	'label_affiliates'                     => array(
		'name'              => __( 'Affiliate commission', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => '',
		'type'              => 'yith-field',
		'yith-type'         => 'text',
		'default'           => __( 'Affiliate commission', 'yith-woocommerce-points-and-rewards' ),
		'id'                => 'ywpar_label_affiliates',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_affiliates_enabled',
			'data-deps_value' => 'yes',
		),
	),

	'affiliates_title_end'                 => array(
		'type' => 'sectionend',
		'id'   => 'ywpar_affiliates_title_end',
	),

);

return array( 'affiliates' => $section1 );
