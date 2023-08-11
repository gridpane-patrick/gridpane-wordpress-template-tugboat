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
	'customers-tab' => array(
		'customers-tab-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => apply_filters(
				'ywpar_customers_tab_subtabs',
				array(
					'customers-tab-customers'  => array(
						'title' => esc_html__( 'Customer points', 'yith-woocommerce-points-and-rewards' ),
					),

					'customers-tab-bulk'       => array(
						'title' => esc_html__( 'Bulk Actions', 'yith-woocommerce-points-and-rewards' ),
					),

					'customers-tab-import'       => array(
						'title' => esc_html__( 'Import/Export', 'yith-woocommerce-points-and-rewards' ),
					),

					'customers-tab-ranking' => array(
						'title' => esc_html__( 'Ranking', 'yith-woocommerce-points-and-rewards' ),
					),

				)
			),
		),
	),
);
