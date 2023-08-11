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
	'customers-tab-import' => array(
		'customers' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_ywpar_import_export',
		),
	),
);


