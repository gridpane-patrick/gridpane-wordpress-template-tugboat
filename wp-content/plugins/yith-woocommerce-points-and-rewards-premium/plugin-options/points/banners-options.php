<?php
/**
 * Plugin Options
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

$custom_tab = array(
	'banners' => array(
		'banners_list_table' => array(
			'type'          => 'post_type',
			'post_type'     => 'ywpar-banner',
			'wp-list-style' => 'boxed',
		),
	),
);

return $custom_tab;
