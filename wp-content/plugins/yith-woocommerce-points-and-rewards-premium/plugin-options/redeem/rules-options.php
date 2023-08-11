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
	'rules' => array(
		'points_rule_list_table' => array(
			'type'          => 'post_type',
			'post_type'     => 'ywpar-redeeming-rule',
			'wp-list-style' => 'boxed',
		),
	),
);
