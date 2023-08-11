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
	'levels-badges' => array(
		'levels_badges_list_table' => array(
			'type'          => 'post_type',
			'post_type'     => 'ywpar-level-badge',
			'wp-list-style' => 'boxed',
		),
	),
);

