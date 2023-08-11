<?php
/**
 * Earning Points Rule metabox options
 *
 * @package YITH WooCommerce Points and Rewards Premium
 * @since   2.2.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

$currency = get_woocommerce_currency();

// setting the dynamic option for users to apply the role that depends on the global setting rule.
$global_role_enabled        = ywpar_get_option( 'user_role_enabled_type', 'all' );
$roles                      = yith_ywpar_get_roles();
$ywpar_user_role            = '';
$customer_attributes_levels = '';
if ( 'all' === $global_role_enabled ) {

	$custom_attributes_levels = array(
		'data-deps'       => 'ywpar_user_type',
		'data-deps_value' => 'levels',
	);

	$user_options = array(
		'all'    => esc_html__( 'All Users', 'yith-woocommerce-points-and-rewards' ),
		'roles'  => esc_html__( 'Only specified user roles', 'yith-woocommerce-points-and-rewards' ),
		'levels' => esc_html__( 'Users with specific points level', 'yith-woocommerce-points-and-rewards' ),
	);

	$ywpar_user_role = array(
		'id'                => 'ywpar_user_roles_list',
		'name'              => 'user_roles_list',
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'css'               => 'min-width:300px',
		'multiple'          => true,
		'title'             => esc_html__( 'Choose user roles', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => '',
		'options'           => yith_ywpar_get_roles(),
		'placeholder'       => esc_html__( 'Search user role', 'yith-woocommerce-points-and-rewards' ),
		'std'               => array(),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_user_type',
			'data-deps_value' => 'roles',
		),
	);
	$ywpar_user_type = array(
		'title'   => esc_html__( 'Apply rule to:', 'yith-woocommerce-points-and-rewards' ),
		// translators: Placeholder are html tags.
		'desc'    => sprintf( _x( 'Choose to which users apply this rule. %1$sNote:%2$s you can apply rules only to user roles enabled in the global option. If you want to create rules for all users or other user roles you need to %3$sedit the global option%4$s', 'Placeholder are html tags.', 'yith-woocommerce-points-and-rewards' ), '<br><strong>', '</strong>', '<a href="' . admin_url( 'admin.php?page=yith_woocommerce_points_and_rewards&tab=points&sub_tab=points-standard#earn-role-option' ) . '" target="_blank">', '</a>' ),
		'type'    => 'radio',
		'id'      => 'ywpar_user_type',
		'name'    => 'user_type',
		'options' => apply_filters(
			'ywpar_user_role_enabled_type_options',
			$user_options
		),
		'std'     => 'all',
	);
} elseif ( 'roles' === $global_role_enabled ) {

	$custom_attributes_levels = array(
		'data-deps'       => 'ywpar_user_type',
		'data-deps_value' => 'levels',
	);

	$user_options  = array();
	$roles_enabled = ywpar_get_option( 'user_role_enabled', yith_ywpar_get_roles() );
	$roles_enabled = empty( $roles_enabled ) ? yith_ywpar_get_roles() : $roles_enabled;
	$std           = 'levels';
	if ( $roles_enabled ) {
		foreach ( $roles_enabled as $current_role ) {
			$user_options[ $current_role ] = isset( $roles[ $current_role ] ) ? $roles[ $current_role ] : $current_role;
		}
		$std = $roles_enabled;
	}
	$user_options['levels'] = esc_html__( 'Users with specific points level', 'yith-woocommerce-points-and-rewards' );
	$ywpar_user_type        = array(
		'title'   => esc_html__( 'Apply rule to:', 'yith-woocommerce-points-and-rewards' ),
		// translators: Placeholder are html tags.
		'desc'    => sprintf( _x( 'Choose to which users apply this rule. %1$sNote:%2$s you can apply rules only to user roles enabled in the global option. If you want to create rules for all users or other user roles you need to %3$sedit the global option%4$s', 'Placeholder are html tags.', 'yith-woocommerce-points-and-rewards' ), '<br><strong>', '</strong>', '<a href="' . admin_url( 'admin.php?page=yith_woocommerce_points_and_rewards&tab=points&sub_tab=points-standard#earn-role-option' ) . '" target="_blank">', '</a>' ),
		'type'    => 'checkbox-array',
		'id'      => 'ywpar_user_type',
		'name'    => 'user_type',
		'options' => apply_filters(
			'ywpar_user_role_enabled_type_options',
			$user_options
		),
		'std'     => $std,
	);

} else {
	$user_options    = array();
	$ywpar_user_type = array(
		'title' => '',
		'desc'  => '',
		'type'  => 'hidden',
		'id'    => 'ywpar_user_type',
		'name'  => 'user_type',
		'value' => 'membership',
	);
}

$options = array(
	'ywpar_rule_name'                   => array(
		'id'      => 'ywpar_rule_name',
		'name'    => 'name',
		'type'    => 'text',
		'default' => 1,
		'title'   => esc_html__( 'Rule name', 'yith-woocommerce-points-and-rewards' ),
		'desc'    => esc_html__( 'Enter a name to identify this rule', 'yith-woocommerce-points-and-rewards' ),
		'std'     => '',
	),
	'ywpar_rule_status'                 => array(
		'id'    => 'ywpar_rule_status',
		'name'  => 'status',
		'title' => '',
		'type'  => 'hidden',
		'std'   => 'on',
	),
	'ywpar_priority'                    => array(
		'id'    => 'ywpar_priority',
		'name'  => 'priority',
		'type'  => 'number',
		'title' => esc_html__( 'Priority', 'yith-woocommerce-points-and-rewards' ),
		'desc'  => esc_html__( 'Set the priority to assign to this rule. This is important to overwrite rules. 1 is highest priority', 'yith-woocommerce-points-and-rewards' ),
		'std'   => 1,
	),
	'ywpar_points_type_conversion'      => array(
		'id'      => 'ywpar_points_type_conversion',
		'name'    => 'points_type_conversion',
		'type'    => 'radio',
		'options' => array(
			'fixed'      => esc_html_x( 'Assign a fixed amount of points', 'Admin option', 'yith-woocommerce-points-and-rewards' ),
			'percentage' => esc_html_x( 'Set a % amount of points based on global points rules', 'Admin option', 'yith-woocommerce-points-and-rewards' ),
			'override'   => esc_html_x( 'Set a fixed amount of points based on product prices', 'Admin option', 'yith-woocommerce-points-and-rewards' ),
			'not_assign' => esc_html_x( 'Don\'t assign points', 'Admin option', 'yith-woocommerce-points-and-rewards' ),
		),
		'title'   => esc_html__( 'Points type', 'yith-woocommerce-points-and-rewards' ),
		'desc'    => esc_html__( 'Choose if to assign a fixed amount of points or calculate the points from the product price', 'yith-woocommerce-points-and-rewards' ),
		'std'     => 'fixed',
	),
	'ywpar_fixed_points_to_earn'        => array(
		'title'             => esc_html__( 'Points to assign', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => '',
		'type'              => 'number',
		'std'               => 0,
		'id'                => 'ywpar_fixed_points_to_earn',
		'name'              => 'fixed_points_to_earn',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_points_type_conversion',
			'data-deps_value' => 'fixed',
			'data-sign'       => __( 'points', 'yith-woocommerce-points-and-rewards' ),
		),
	),
	'ywpar_percentage_points_to_earn'   => array(
		'title'             => esc_html__( 'Points to assign', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => '',
		'type'              => 'number',
		'std'               => 0,
		'id'                => 'ywpar_percentage_points_to_earn',
		'name'              => 'percentage_points_to_earn',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_points_type_conversion',
			'data-deps_value' => 'percentage',
		),
	),
	'ywpar_earn_points_conversion_rate' => array(
		'title'             => esc_html__( 'Points to assign', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => '',
		'type'              => 'options-conversion-earning',
		'std'               => array(
			$currency => array(
				'points' => 1,
				'money'  => 10,
			),
		),
		'id'                => 'ywpar_earn_points_conversion_rate',
		'name'              => 'earn_points_conversion_rate',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_points_type_conversion',
			'data-deps_value' => 'override',
		),
	),
	'ywpar_is_rule_scheduled'           => array(
		'id'      => 'ywpar_is_rule_scheduled',
		'name'    => 'is_rule_scheduled',
		'type'    => 'radio',
		'options' => array(
			'no'  => esc_html_x( 'From now, until	it\'s ended manually', 'Admin option', 'yith-woocommerce-points-and-rewards' ),
			'yes' => esc_html_x( 'Schedule a start and end date', 'Admin option', 'yith-woocommerce-points-and-rewards' ),
		),
		'title'   => esc_html__( 'Rule will be valid', 'yith-woocommerce-points-and-rewards' ),
		'desc'    => esc_html__( 'Choose to schedule the global points overriding or if to start and end it manually', 'yith-woocommerce-points-and-rewards' ),
		'std'     => 'no',
	),
	'ywpar_rule_schedule'               => array(
		'id'                => 'ywpar_rule_schedule',
		'name'              => 'rule_schedule',
		'type'              => 'inline-fields',
		'fields'            => array(
			'html0' => array(
				'type' => 'html',
				'html' => esc_html__( 'From', 'yith-woocommerce-points-and-rewards' ),
			),
			'from'  => array(
				'title' => esc_html__( 'Add points to orders placed from', 'yith-woocommerce-points-and-rewards' ),
				'desc'  => esc_html__( 'Choose from which date to assign points to previous orders', 'yith-woocommerce-points-and-rewards' ),
				'type'  => 'datepicker',
				'data'  => array(
					'date-format' => 'yy-mm-dd',
				),
				'std'   => '',
			),
			'html1' => array(
				'type' => 'html',
				'html' => esc_html__( 'To', 'yith-woocommerce-points-and-rewards' ),
			),
			'to'    => array(
				'title' => '',
				'desc'  => esc_html__( 'Choose from which date to assign points to previous orders', 'yith-woocommerce-points-and-rewards' ),
				'type'  => 'datepicker',
				'data'  => array(
					'date-format' => 'yy-mm-dd',
				),
				'std'   => '',
			),
		),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_is_rule_scheduled',
			'data-deps_value' => 'yes',
		),
		'title'             => '',
		'desc'              => esc_html__( 'Choose to schedule the global points overriding or if to start and end it manually', 'yith-woocommerce-points-and-rewards' ),
		'std'               => 'fixed',
	),
	'ywpar_apply_to'                    => array(
		'id'      => 'ywpar_apply_to',
		'name'    => 'apply_to',
		'type'    => 'select',
		'class'   => 'wc-enhanced-select',
		'options' => array(
			'all_products'        => esc_html__( 'All products', 'yith-woocommerce-points-and-rewards' ),
			'selected_products'   => esc_html__( 'Specific products', 'yith-woocommerce-points-and-rewards' ),
			'on_sale_products'     => esc_html__( 'On sale products', 'yith-woocommerce-points-and-rewards' ),
			'selected_categories' => esc_html__( 'Products of specific categories', 'yith-woocommerce-points-and-rewards' ),
			'selected_tags'       => esc_html__( 'Products of specific tags', 'yith-woocommerce-points-and-rewards' ),
		),
		'title'   => esc_html__( 'Apply rule to:', 'yith-woocommerce-points-and-rewards' ),
		'desc'    => esc_html__( 'Choose if to assign these points when the user purchases all products or only specific products or products of specific categories', 'yith-woocommerce-points-and-rewards' ),
		'std'     => 'all_products',
	),
	'ywpar_apply_to_products_list'      => array(
		'title'             => esc_html__( 'Choose products', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose for which products to apply these points', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'ajax-products',
		'std'               => array(),
		'data'              => array(
			'action'   => 'woocommerce_json_search_products_and_variations',
			'security' => wp_create_nonce( 'search-products' ),
		),
		'id'                => 'ywpar_apply_to_products_list',
		'name'              => 'apply_to_products_list',
		'multiple'          => true,
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_apply_to',
			'data-deps_value' => 'selected_products',
		),
	),
	'ywpar_apply_to_categories_list'    => array(
		'title'             => esc_html__( 'Choose categories', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose for which products to apply these points', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'ajax-terms',
		'std'               => array(),
		'data'              => array(
			'taxonomy'    => 'product_cat',
			'placeholder' => esc_html__( 'Search for a category', 'yith-woocommerce-points-and-rewards' ),
		),
		'id'                => 'ywpar_apply_to_categories_list',
		'name'              => 'apply_to_categories_list',
		'multiple'          => true,
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_apply_to',
			'data-deps_value' => 'selected_categories',
		),
	),
	'ywpar_apply_to_tags_list'          => [
		'title'             => esc_html__( 'Choose tags', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose for which products to apply these points', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'ajax-terms',
		'std'               => array(),
		'data'              => array(
			'taxonomy'    => 'product_tag',
			'placeholder' => esc_html__( 'Search for a tag', 'yith-woocommerce-points-and-rewards' ),
		),
		'id'                => 'ywpar_apply_to_tags_list',
		'name'              => 'apply_to_tags_list',
		'multiple'          => true,
		'custom_attributes' => [
			'data-deps'       => 'ywpar_apply_to',
			'data-deps_value' => 'selected_tags',
		],
	],
	'ywpar_exclude_products'            => array(
		'id'                => 'ywpar_exclude_products',
		'name'              => 'exclude_products',
		'type'              => 'onoff',
		'std'               => 'no',
		'title'             => esc_html__( 'Exclude products', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enable if you want to exclude some products from this rule', 'yith-woocommerce-points-and-rewards' ),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_apply_to',
			'data-deps_value' => 'all_products|selected_categories|selected_tags|on_sale_products',
		),
	),
	'ywpar_exclude_products_list'       => array(
		'title'             => esc_html__( 'Choose products', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose which products to exclude', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'ajax-products',
		'std'               => array(),
		'data'              => array(
			'action'   => 'woocommerce_json_search_products_and_variations',
			'security' => wp_create_nonce( 'search-products' ),
		),
		'id'                => 'ywpar_exclude_products_list',
		'name'              => 'exclude_products_list',
		'multiple'          => true,
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_exclude_products,ywpar_apply_to',
			'data-deps_value' => 'yes,all_products|selected_categories|selected_tags|on_sale_products',
		),
	),
	'ywpar_user_type'                   => $ywpar_user_type,
);
if ( ! empty( $custom_attributes_levels ) ) {
	$options['ywpar_user_levels_list'] = array(
		'id'                => 'ywpar_user_levels_list',
		'name'              => 'user_levels_list',
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'css'               => 'min-width:300px',
		'multiple'          => true,
		'title'             => esc_html__( 'Choose user levels', 'yith-woocommerce-points-and-rewards' ),
		// translators: Placeholder are html tags.
		'desc'              => '',
		'options'           => ywpar_get_user_levels(),
		'placeholder'       => esc_html__( 'Search user level', 'yith-woocommerce-points-and-rewards' ),
		'std'               => array(),
		'custom_attributes' => $custom_attributes_levels,
	);
}
if ( ! empty( $ywpar_user_role ) ) {
	$options['ywpar_user_roles_list'] = $ywpar_user_role;
}

return apply_filters( 'ywpar_points_rules_options', $options );
