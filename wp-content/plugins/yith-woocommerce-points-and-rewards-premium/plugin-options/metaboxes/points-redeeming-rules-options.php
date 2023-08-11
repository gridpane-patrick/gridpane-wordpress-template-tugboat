<?php
/**
 * Redeemint Points Rule metabox options
 *
 * @package YITH WooCommerce Points and Rewards Premium
 * @since   2.2.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

$currency                       = get_woocommerce_currency();
$current_user_role_enabled_type = '';

$global_role_enabled      = ywpar_get_option( 'user_role_redeem_type', 'all' );
$roles                    = yith_ywpar_get_roles();
$ywpar_user_role          = '';
$custom_attributes_levels = array();
$std                      = '';

if ( 'all' === $global_role_enabled ) {

	$custom_attributes_levels = array(
		'data-deps'       => 'ywpar_user_type',
		'data-deps_value' => 'levels',
	);

	$ywpar_user_type = array(
		'title'   => esc_html__( 'Apply rule to', 'yith-woocommerce-points-and-rewards' ),
		// translators: Placeholder are html tags.
		'desc'    => sprintf( _x( 'Choose to which users apply this rule. %1$sNote:%2$s you can apply rules only to user roles enabled in the global option. If you want to create rules for all users or other user roles you need to %3$sedit the global option%4$s', 'Placeholder are html tags.', 'yith-woocommerce-points-and-rewards' ), '<br><strong>', '</strong>', '<a href="' . admin_url( 'admin.php?page=yith_woocommerce_points_and_rewards&tab=redeem&sub_tab=redeem-standard#redeem-role-option' ) . '" target="_blank">', '</a>' ),
		'type'    => 'radio',
		'id'      => 'ywpar_user_type',
		'name'    => 'user_type',
		'options' => apply_filters(
			'ywpar_user_role_enabled_type_options',
			array(
				'all'    => esc_html__( 'All Users', 'yith-woocommerce-points-and-rewards' ),
				'roles'  => esc_html__( 'Only specified user roles', 'yith-woocommerce-points-and-rewards' ),
				'levels' => esc_html__( 'Users with specific points level', 'yith-woocommerce-points-and-rewards' ),
			)
		),
		'std'     => 'all',
	);

	$ywpar_user_role = array(
		'id'                => 'ywpar_user_roles_list',
		'name'              => 'user_roles_list',
		'title'             => esc_html__( 'Choose user roles', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => '',
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'css'               => 'min-width:300px',
		'multiple'          => true,
		'options'           => yith_ywpar_get_roles(),
		'std'               => array(),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_user_type',
			'data-deps_value' => 'roles',
		),
	);

} elseif ( 'roles' === $global_role_enabled ) {
	$user_options             = array();
	$roles_enabled            = ywpar_get_option( 'user_role_redeem_enabled' );
	$std                      = 'levels';
	$custom_attributes_levels = array();

	if ( $roles_enabled ) {
		foreach ( $roles_enabled as $current_role ) {
			$user_options[ $current_role ] = isset( $roles[ $current_role ] ) ? $roles[ $current_role ] : $current_role;
		}
		$std = $roles_enabled;
	}
	$user_options['levels']   = esc_html__( 'Users with specific points level', 'yith-woocommerce-points-and-rewards' );
	$ywpar_user_type          = array(
		'title'   => esc_html__( 'Apply rule to', 'yith-woocommerce-points-and-rewards' ),
		// translators: Placeholder are html tags.
		'desc'    => sprintf( _x( 'Choose to which users apply this rule. %1$sNote:%2$s you can apply rules only to user roles enabled in the global option. If you want to create rules for all users or other user roles you need to %3$sedit the global option%4$s', 'Placeholder are html tags.', 'yith-woocommerce-points-and-rewards' ), '<br><strong>', '</strong>', '<a href="' . admin_url( 'admin.php?page=yith_woocommerce_points_and_rewards&tab=redeem&sub_tab=redeem-standard#redeem-role-option' ) . '" target="_blank">', '</a>' ),
		'type'    => 'checkbox-array',
		'id'      => 'ywpar_user_type',
		'name'    => 'user_type',
		'options' => apply_filters( 'ywpar_user_role_enabled_type_options', $user_options ),
		'std'     => $std,
	);
	$custom_attributes_levels = array(
		'data-deps'       => 'ywpar_user_type',
		'data-deps_value' => 'levels',
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

$point_conversion_type = array();
if ( 'fixed' === ywpar_get_option( 'conversion_rate_method', 'fixed' ) ) {
	$point_conversion_type = array(
		'title'             => esc_html__( 'Reward Conversion Rate', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose how to calculate the discount when customers use their available points', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'options-conversion',
		'class'             => 'fixed_method',
		'std'               => array(
			$currency => array(
				'points' => 100,
				'money'  => 1,
			),
		),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_points_type,ywpar_apply_to',
			'data-deps_value' => 'conversion_rate,role|level',
		),
		'id'                => 'ywpar_rewards_conversion_rate',
		'name'              => 'conversion_rate',
	);

} else {
	$point_conversion_type = array(
		'title'             => esc_html__( 'Reward Conversion Rate', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose how to calculate the discount when customers use their available points', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'options-percentage-conversion',
		'std'               => array(
			$currency => array(
				'points'   => 20,
				'discount' => 5,
			),
		),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_points_type,ywpar_apply_to',
			'data-deps_value' => 'conversion_rate,role|level',
		),
		'id'                => 'ywpar_rewards_percentage_conversion_rate',
		'name'              => 'percentage_conversion_rate',
	);
}


$options = array(
	'ywpar_rule_name'                          => array(
		'id'      => 'ywpar_rule_name',
		'name'    => 'name',
		'type'    => 'text',
		'default' => 1,
		'title'   => __( 'Rule name', 'yith-woocommerce-points-and-rewards' ),
		'desc'    => __( 'Enter a name to identify this rule', 'yith-woocommerce-points-and-rewards' ),
		'std'     => '',
	),
	'ywpar_rule_status'                        => array(
		'id'    => 'ywpar_rule_status',
		'name'  => 'status',
		'title' => '',
		'type'  => 'hidden',
		'std'   => 'on',
	),
	'ywpar_priority'                           => array(
		'id'    => 'ywpar_priority',
		'name'  => 'priority',
		'type'  => 'number',
		'title' => __( 'Priority', 'yith-woocommerce-points-and-rewards' ),
		'desc'  => __( 'Set the priority to assign to this rule. This is important to overwrite rules. 1 is highest priority', 'yith-woocommerce-points-and-rewards' ),
		'std'   => 1,
	),
	'ywpar_points_type'                        => array(
		'id'      => 'ywpar_points_type',
		'name'    => 'type',
		'type'    => 'radio',
		'options' => array(
			'conversion_rate' => __( 'Redeem conversion rate', 'yith-woocommerce-points-and-rewards' ),
			'max_discount'    => __( 'Redeem max discount rate', 'yith-woocommerce-points-and-rewards' ),
		),
		'title'   => __( 'Rule type', 'yith-woocommerce-points-and-rewards' ),
		'desc'    => __( 'Choose how to apply the discount. The discount can either be a percentage or a fixed amount', 'yith-woocommerce-points-and-rewards' ),
		'std'     => 'conversion_rate',
	),

	'ywpar_rewards_percentage_conversion_rate' => $point_conversion_type,

	'ywpar_maximum_discount_type'              => array(
		'id'                => 'ywpar_maximum_discount_type',
		'name'              => 'maximum_discount_type',
		'type'              => 'radio',
		'options'           => apply_filters(
			'ywpar_redeem_points_maximum_discount_type',
			array(
				'percentage' => esc_html__(
					'Set a % max discount based on the global max discount.
(Example: with a global max discount of 50$ if you set a max discount of 10% for this product the user will get a max discount of 5$ for this product)',
					'yith-woocommerce-points-and-rewards'
				),
				'fixed'      => esc_html__(
					'Set a fixed max discount value
(Example: max 5$ of discount for this product)',
					'yith-woocommerce-points-and-rewards'
				),
			)
		),
		'title'             => __( 'Max discount type', 'yith-woocommerce-points-and-rewards' ),
		'std'               => 'fixed',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_points_type',
			'data-deps_value' => 'max_discount',
		),
	),

	'ywpar_max_discount'                       => array(
		'id'                => 'ywpar_max_discount',
		'name'              => 'max_discount',
		'type'              => 'text',
		'title'             => esc_html__( 'Max discount value', 'yith-woocommerce-points-and-rewards' ),
		'std'               => '',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_points_type,ywpar_maximum_discount_type',
			'data-deps_value' => 'max_discount,fixed',
			'data-desc'       => esc_attr( get_woocommerce_currency_symbol( get_woocommerce_currency() ) ),
		),
	),
	'ywpar_max_discount_percentage'            => array(
		'id'                => 'ywpar_max_discount_percentage',
		'name'              => 'max_discount_percentage',
		'type'              => 'text',
		'title'             => esc_html__( 'Max discount value', 'yith-woocommerce-points-and-rewards' ),
		'std'               => '',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_points_type,ywpar_maximum_discount_type',
			'data-deps_value' => 'max_discount,percentage',
			'data-desc'       => '%',
		),
	),
	'ywpar_max_discount_apply_to'              => array(
		'id'                => 'ywpar_max_discount_apply_to',
		'name'              => 'apply_to',
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'options'           => array(
			'all_products'        => esc_html__( 'All products', 'yith-woocommerce-points-and-rewards' ),
			'selected_products'   => esc_html__( 'Specific products', 'yith-woocommerce-points-and-rewards' ),
			'on_sale_products'     => esc_html__( 'On sale products', 'yith-woocommerce-points-and-rewards' ),
			'selected_categories' => esc_html__( 'Products of specific categories', 'yith-woocommerce-points-and-rewards' ),
			'selected_tags'       => esc_html__( 'Products of specific tags', 'yith-woocommerce-points-and-rewards' ),
		),
		'title'             => esc_html__( 'Apply rule to these products', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose if set a max discount when the user purchases all products or only specific products or products of specific categories', 'yith-woocommerce-points-and-rewards' ),
		'std'               => 'all_products',
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_points_type',
			'data-deps_value' => 'max_discount',
		),
	),
	'ywpar_apply_to_products_list'             => array(
		'title'             => esc_html__( 'Choose products', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose for which products to apply this rule', 'yith-woocommerce-points-and-rewards' ),
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
			'data-deps'       => 'ywpar_max_discount_apply_to',
			'data-deps_value' => 'selected_products',
		),
	),
	'ywpar_apply_to_categories_list'           => array(
		'title'             => esc_html__( 'Choose categories', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose for which category to apply this rule', 'yith-woocommerce-points-and-rewards' ),
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
			'data-deps'       => 'ywpar_max_discount_apply_to',
			'data-deps_value' => 'selected_categories',
		),
	),
	'ywpar_apply_to_tags_list'                 => array(
		'title'             => esc_html__( 'Choose tags', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Choose for which tags to apply this rule', 'yith-woocommerce-points-and-rewards' ),
		'type'              => 'ajax-terms',
		'std'               => array(),
		'data'              => array(
			'taxonomy'    => 'product_tag',
			'placeholder' => esc_html__( 'Search for a tag', 'yith-woocommerce-points-and-rewards' ),
		),
		'id'                => 'ywpar_apply_to_tags_list',
		'name'              => 'apply_to_tags_list',
		'multiple'          => true,
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_max_discount_apply_to',
			'data-deps_value' => 'selected_tags',
		),
	),
	'ywpar_exclude_products'                   => array(
		'id'                => 'ywpar_exclude_products',
		'name'              => 'exclude_products',
		'type'              => 'onoff',
		'std'               => 'no',
		'title'             => esc_html__( 'Exclude products', 'yith-woocommerce-points-and-rewards' ),
		'desc'              => esc_html__( 'Enable if you want to exclude some products from this rule', 'yith-woocommerce-points-and-rewards' ),
		'custom_attributes' => array(
			'data-deps'       => 'ywpar_points_type,ywpar_max_discount_apply_to',
			'data-deps_value' => 'max_discount,all_products|selected_categories|selected_tags|on_sale_products',
		),
	),
	'ywpar_exclude_products_list'              => array(
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
			'data-deps'       => 'ywpar_exclude_products,ywpar_max_discount_apply_to',
			'data-deps_value' => 'yes,all_products|selected_categories|on_sale_products|selected_tags',
		),
	),
	'ywpar_user_type'                          => $ywpar_user_type,


);
if ( ! empty( $custom_attributes_levels ) ) {
	$options['ywpar_user_levels_list'] = array(
		'id'                => 'ywpar_user_levels_list',
		'name'              => 'user_levels_list',
		// translators: Placeholder are html tags.
		'desc'              => '',
		'type'              => 'select',
		'class'             => 'wc-enhanced-select',
		'css'               => 'min-width:300px',
		'multiple'          => true,
		'title'             => __( 'Choose user levels', 'yith-woocommerce-points-and-rewards' ),
		'options'           => ywpar_get_user_levels(),
		'placeholder'       => __( 'Search user level', 'yith-woocommerce-points-and-rewards' ),
		'std'               => array(),
		'custom_attributes' => $custom_attributes_levels,
	);
}
if ( ! empty( $ywpar_user_role ) ) {
	$options['ywpar_user_roles_list'] = $ywpar_user_role;
}

return apply_filters( 'ywpar_points_rewards_rules_options', $options );
