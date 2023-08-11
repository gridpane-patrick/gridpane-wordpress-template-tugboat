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
	'points-earning-rules' => array(
		'points_earning_rule_list_table' => array(
			'type'          => 'post_type',
			'post_type'     => 'ywpar-earning-rule',
			'wp-list-style' => 'boxed',
		),
	),
);
