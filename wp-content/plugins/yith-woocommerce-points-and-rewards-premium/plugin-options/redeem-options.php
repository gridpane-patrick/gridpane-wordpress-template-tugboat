<?php
/**
 * Redeem Options
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

return array(
	'redeem' => array(
		'redeem-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'redeem-standard' => array(
					'title' => esc_html__( 'Redeem Options', 'yith-woocommerce-points-and-rewards' ),
				),
				'rules'           => array(
					'title' => esc_html__( 'Redeem Rules', 'yith-woocommerce-points-and-rewards' ),
				),
				'share'           => array(
					'title' => esc_html__( 'Share points', 'yith-woocommerce-points-and-rewards' ),
				),
			),
		),
	),
);


