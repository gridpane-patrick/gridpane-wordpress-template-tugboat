<?php
/**
 * Plugin Options
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

return array(
	'points' => array(
		'points-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'points-standard'      => array(
					'title' => esc_html__( 'Points Options', 'yith-woocommerce-points-and-rewards' ),
				),
				'points-earning-rules' => array(
					'title' => esc_html__( 'Points Rules', 'yith-woocommerce-points-and-rewards' ),
				),
				'points-extra'         => array(
					'title' => esc_html__( 'Extra points', 'yith-woocommerce-points-and-rewards' ),
				),
				'levels-badges'        => array(
					'title' => esc_html__( 'Levels & Badges', 'yith-woocommerce-points-and-rewards' ),
				),
				'banners'              => array(
					'title' => esc_html__( 'Banners', 'yith-woocommerce-points-and-rewards' ),
				),
			),
		),
	),
);

