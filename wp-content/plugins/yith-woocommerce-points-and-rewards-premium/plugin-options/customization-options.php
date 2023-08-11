<?php
/**
 * Customization Options
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

$loop_message_icon           = '<img style="max-width: 16px; margin-right: 5px;" src="' . YITH_YWPAR_ASSETS_URL . '/images/badge.svg" />';
$single_product_message_icon = '<img style="max-width: 16px; margin-right: 5px;" src="' . YITH_YWPAR_ASSETS_URL . '/images/ywpar_message.svg" />';
$message_earning_points      = '<img style="max-width: 35px; margin-right: 5px;" src="' . YITH_YWPAR_ASSETS_URL . '/images/prize.svg" />';

$section1 = array(
	'general_title'                        => array(
		'name' => esc_html__( 'General', 'yith-woocommerce-points-and-rewards' ),
		'type' => 'title',
		'id'   => 'ywpar_general_option',
	),

	'hide_point_system_to_guest'           => array(
		'name'      => esc_html__( 'Hide points messages to guest users', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Enable to hide point messages to guest users', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
		'id'        => 'ywpar_hide_point_system_to_guest',
	),

	'general_settings_end'                 => array(
		'type' => 'sectionend',
		'id'   => 'ywpar_general_option_end',
	),

	// MESSAGE IN LOOP.
	'points_in_shop_pages'                 => array(
		'name' => esc_html__( 'Points in shop pages', 'yith-woocommerce-points-and-rewards' ),
		'type' => 'title',
		'id'   => 'ywpar_points_in_shop_pages',
	),

	'enabled_loop_message'                 => array(
		'name'      => esc_html__( 'Show points message in shop pages (loop)', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Enable to show the message related to points in all shop pages', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
		'id'        => 'ywpar_enabled_loop_message',
	),

	'loop_message'                         => array(
		'name'          => esc_html__( 'Loop Message', 'yith-woocommerce-points-and-rewards' ),
		'desc'          => _x( '{points} number of points earned;<br>{points_label} of points;<br>{price_discount_fixed_conversion} the value corresponding to points', 'do not translate the text inside the brackets', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'     => 'textarea-editor',
		'type'          => 'yith-field',
		'default'       => $loop_message_icon . _x( '<strong>{points}</strong> {points_label}', 'do not translate the text inside the brackets', 'yith-woocommerce-points-and-rewards' ),
		'id'            => 'ywpar_loop_message',
		'deps'          => array(
			'id'    => 'ywpar_enabled_loop_message',
			'value' => 'yes',
			'type'  => 'hide',
		),
		'textarea_rows' => 5,
	),

	'loop_points_message_colors'           => array(
		'id'           => 'ywpar_loop_points_message_colors',
		'type'         => 'yith-field',
		'yith-type'    => 'multi-colorpicker',
		'name'         => esc_html__( 'Colors', 'yith-woocommerce-points-and-rewards' ),
		'colorpickers' => array(
			array(
				'id'      => 'text_color',
				'name'    => esc_html__( 'Text', 'yith-woocommerce-points-and-rewards' ),
				'default' => '#000000',
			),
			array(
				'id'      => 'background_color',
				'name'    => esc_html__( 'Background', 'yith-woocommerce-points-and-rewards' ),
				'default' => 'rgba(255,255,255,0)',
			),
			array(
				'id'      => 'border_color',
				'name'    => esc_html__( 'Border', 'yith-woocommerce-points-and-rewards' ),
				'default' => '#000000',
			),
		),
		'deps'         => array(
			'id'    => 'ywpar_enabled_loop_message',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),

	// SINGLE PRODUCT PAGE.
	'enabled_single_product_message'       => array(
		'name'      => esc_html__( 'Show points message in product page', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Enable to show the message related to points in all product pages', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
		'id'        => 'ywpar_enabled_single_product_message',
	),

	'single_product_message_position'      => array(
		'name'      => esc_html__( 'Message position', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Choose where to show the points message in the product page', 'yith-woocommerce-points-and-rewards' ),
		'yith-type' => 'select',
		'class'     => 'wc-enhanced-select',
		'type'      => 'yith-field',
		'options'   => array(
			'before_add_to_cart' => esc_html__( 'Before "Add to cart" button', 'yith-woocommerce-points-and-rewards' ),
			'after_add_to_cart'  => esc_html__( 'After "Add to cart" button', 'yith-woocommerce-points-and-rewards' ),
			'before_excerpt'     => esc_html__( 'Before excerpt', 'yith-woocommerce-points-and-rewards' ),
			'after_excerpt'      => esc_html__( 'After excerpt', 'yith-woocommerce-points-and-rewards' ),
			'after_meta'         => esc_html__( 'After product meta', 'yith-woocommerce-points-and-rewards' ),
		),
		'default'   => 'before_add_to_cart',
		'id'        => 'ywpar_single_product_message_position',
		'deps'      => array(
			'id'    => 'ywpar_enabled_single_product_message',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),

	'single_product_message'               => array(
		'name'          => esc_html__( 'Single Product Page Message', 'yith-woocommerce-points-and-rewards' ),
		'desc'          => _x( '{points} number of points earned;<br>{points_label} of points;<br>{price_discount_fixed_conversion} the value corresponding to points ', 'do not translate the text inside the brackets', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'     => 'textarea-editor',
		'type'          => 'yith-field',
		'default'       => $loop_message_icon . _x( 'Purchase this item and get <strong>{points} {points_label}</strong> - a worth of <strong>{price_discount_fixed_conversion}</strong>', 'do not translate the text inside the brackets', 'yith-woocommerce-points-and-rewards' ),
		'id'            => 'ywpar_single_product_message',
		'deps'          => array(
			'id'    => 'ywpar_enabled_single_product_message',
			'value' => 'yes',
			'type'  => 'hide',
		),
		'textarea_rows' => 5,
	),

	'single_product_points_message_colors' => array(
		'id'           => 'ywpar_single_product_points_message_colors',
		'type'         => 'yith-field',
		'yith-type'    => 'multi-colorpicker',
		'name'         => esc_html__( 'Colors', 'yith-woocommerce-points-and-rewards' ),
		'colorpickers' => array(
			array(
				'id'      => 'text_color',
				'name'    => esc_html__( 'Text', 'yith-woocommerce-points-and-rewards' ),
				'default' => '#000000',
			),
			array(
				'id'      => 'background_color',
				'name'    => esc_html__( 'Background', 'yith-woocommerce-points-and-rewards' ),
				'default' => '#E4F6F3',
			),
		),
		'deps'         => array(
			'id'    => 'ywpar_enabled_single_product_message',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),

	'points_in_shop_pages_end'             => array(
		'type' => 'sectionend',
		'id'   => 'ywpar_points_in_shop_pages',
	),

	// MY ACCOUNT PAGE.
	'show_points_in_myaccount_options'     => array(
		'name' => esc_html__( 'Points in My Account', 'yith-woocommerce-points-and-rewards' ),
		'type' => 'title',
		'id'   => 'ywpar_show_options',
	),


	'show_point_list_my_account_page'      => array(
		'name'      => esc_html__( 'Show points on My Account', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Enable to show points in the "My Account" page of all users', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
		'id'        => 'ywpar_show_point_list_my_account_page',
	),

	'my_account_page_label'                => array(
		'name'      => esc_html__( 'Label for points section', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Enter a label to identify the points section in My Account page', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'My Points', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_my_account_page_label',
		'deps'      => array(
			'id'    => 'ywpar_show_point_list_my_account_page',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),

	'my_account_page_endpoint'             => array(
		'name'      => esc_html__( 'Endpoint for points section', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Enter the endpoint of the Points in My account page. Endpoints cannot contain any spaces nor uppercase letters', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => 'my-points',
		'id'        => 'ywpar_my_account_page_endpoint',
		'deps'      => array(
			'id'    => 'ywpar_show_point_list_my_account_page',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),

	'show_points_worth_money'              => array(
		'name'      => esc_html__( 'Show Points Value', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'enable to show the points value. Example: "You have 100 points - 15$', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
		'id'        => 'ywpar_show_point_worth_my_account',
		'deps'      => array(
			'id'    => 'ywpar_show_point_list_my_account_page',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),

	'show_point_summary_on_order_details'  => array(
		'name'      => esc_html__( 'Show points earned and spent', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Show points earned and spent in My Account > Order details', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
		'id'        => 'ywpar_show_point_summary_on_order_details',
	),
	'show_point_summary_on_email'          => array(
		'name'      => '',
		'desc'      => esc_html__( 'Show points earned and spent in the email of Order completed', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
		'id'        => 'ywpar_show_point_summary_on_email',
	),
	'show_options_end'                     => array(
		'type' => 'sectionend',
		'id'   => 'ywpar_show_options',
	),

	// CART & CHECKOUT PAGES.
	'points_in_cart_checkout_pages'        => array(
		'name' => esc_html__( 'Points in Cart & Checkout', 'yith-woocommerce-points-and-rewards' ),
		'type' => 'title',
		'id'   => 'ywpar_points_in_cart_checkout_pages',
	),

	'enabled_cart_message'                 => array(
		'name'      => esc_html__( 'Show points in Cart page', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Enable to show the points message in cart page', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
		'id'        => 'ywpar_enabled_cart_message',
	),

	'cart_message'                         => array(
		'name'          => esc_html__( 'Message text in cart', 'yith-woocommerce-points-and-rewards' ),
		'desc'          => esc_html__( 'Enter the text to show in cart page', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'     => 'textarea-editor',
		'type'          => 'yith-field',
		'default'       => $message_earning_points . _x( 'If you proceed to checkout, you will earn <strong>{points}</strong> {points_label}!', 'do not translate the text inside the brackets', 'yith-woocommerce-points-and-rewards' ),
		'id'            => 'ywpar_cart_message',
		'deps'          => array(
			'id'    => 'ywpar_enabled_cart_message',
			'value' => 'yes',
			'type'  => 'hide',
		),
		'textarea_rows' => 5,
	),

	'enabled_checkout_message'             => array(
		'name'      => esc_html__( 'Show points in Checkout page', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Enable to show the points message in checkout page', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
		'id'        => 'ywpar_enabled_checkout_message',
	),

	'checkout_message'                     => array(
		'name'          => esc_html__( 'Message text in checkout', 'yith-woocommerce-points-and-rewards' ),
		'desc'          => esc_html__( 'Enter the text to show in Checkout page', 'yith-woocommerce-points-and-rewards' ),
		'yith-type'     => 'textarea-editor',
		'type'          => 'yith-field',
		'default'       => $message_earning_points . _x( 'If you proceed to checkout, you will earn <strong>{points}</strong> {points_label}!', 'do not translate the text inside the brackets', 'yith-woocommerce-points-and-rewards' ),
		'id'            => 'ywpar_checkout_message',
		'deps'          => array(
			'id'    => 'ywpar_enabled_checkout_message',
			'value' => 'yes',
			'type'  => 'hide',
		),
		'textarea_rows' => 5,
	),


	'points_in_cart_checkout_pages_end'    => array(
		'type' => 'sectionend',
		'id'   => 'ywpar_points_in_cart_checkout_pages',
	),

	'labels_title'                         => array(
		'name' => esc_html__( 'Labels Settings', 'yith-woocommerce-points-and-rewards' ),
		'type' => 'title',
		'id'   => 'ywpar_labels_title',
	),

	'points_label_singular'                => array(
		'name'      => esc_html__( 'Singular label replacing "point"', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Point', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_points_label_singular',
	),

	'points_label_plural'                  => array(
		'name'      => esc_html__( 'Plural label replacing "points"', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Points', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_points_label_plural',
	),

	'label_order_completed'                => array(
		'name'      => esc_html__( 'Order Completed', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Order Completed', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_order_completed',
	),

	'label_order_processing'               => array(
		'name'      => esc_html__( 'Order Processing', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Order Processing', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_order_processing',
	),

	'label_order_cancelled'                => array(
		'name'      => esc_html__( 'Order Cancelled', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Order Cancelled', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_order_cancelled',
	),

	'label_admin_action'                   => array(
		'name'      => esc_html__( 'Admin Action', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Admin Action', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_admin_action',
	),

	'label_reviews_exp'                    => array(
		'name'      => esc_html__( 'Reviews', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Reviews', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_reviews_exp',
	),

	'label_registration_exp'               => array(
		'name'      => esc_html__( 'Registration', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Registration', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_registration_exp',
	),

	'label_points_exp'                     => array(
		'name'      => __( 'Target - Total Points', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => __( 'Target achieved - Points collected', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_points_exp',
	),

	'label_amount_spent_exp'               => array(
		'name'      => esc_html__( 'Target - Total Amount', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Target achieved - Total spend', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_amount_spent_exp',
	),

	'label_num_of_orders_exp'              => array(
		'name'      => esc_html__( 'Target - Total Orders', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Target achieved - Total Orders', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_num_of_orders_exp',
	),
	'label_checkout_threshold_exp'         => array(
		'name'      => esc_html__( 'Target Checkout Total Threshold', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Target achieved - Checkout Total Threshold', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_checkout_threshold_exp',
	),
	'label_birthday_exp'                   => array(
		'name'      => esc_html__( 'Birthday', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Target achieved - Birthday', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_birthday_exp',
	),
	'label_daily_login_exp'                => array(
		'name'      => esc_html__( 'Daily Login', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Target achieved - Daily Login', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_daily_login_exp',
	),
	'label_ref_registration_exp'           => array(
		'name'      => esc_html__( 'Registration by referral', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'User registration by referral', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_referral_registration_exp',
	),
	'label_ref_removed_registration_exp'   => array(
		'name'      => esc_html__( 'Removed registration points due to a referred user cancellation', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Removed registration points due to a referred user cancellation', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_ref_removed_registration_exp',
	),
	'label_ref_purchase_exp'               => array(
		'name'      => esc_html__( 'Purchase by referral', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Purchase by referral', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_referral_purchase_exp',
	),

	'label_ref_removed_purchase_exp'       => array(
		'name'      => esc_html__( 'Removed points due to a referred user cancellation', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Removed points due to a referred user cancellation', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_ref_removed_purchase_exp',
	),

	'label_collected_points_exp'           => array(
		'name'      => esc_html__( 'Collected Points', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Collected Points', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_collected_points_exp',
	),

	'label_level_achieved_exp'             => array(
		'name'      => esc_html__( 'Level Achieved', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Target achieved - Level', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_level_achieved_exp',
	),
	'label_completed_profile_exp'          => array(
		'name'      => esc_html__( 'Profile Complete', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Target achieved - Profile Completed', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_completed_profile_exp',
	),
	'label_expired_points'                 => array(
		'name'      => esc_html__( 'Expired Points', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Expired Points', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_expired_points',
	),

	'label_order_refund'                   => array(
		'name'      => esc_html__( 'Order Refund', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Order Refund', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_order_refund',
	),

	'label_refund_deleted'                 => array(
		'name'      => esc_html__( 'Order Refund Deleted', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Order Refund Deleted', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_refund_deleted',
	),

	'label_redeemed_points'                => array(
		'name'      => esc_html__( 'Redeemed Points', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Redeemed Points for order', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_redeemed_points',
	),

	'label_shared_points'                  => array(
		'name'      => esc_html__( 'Shared Points', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Shared point with a coupon', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_shared_points',
	),

	'label_apply_discounts'                => array(
		'name'      => esc_html__( 'Apply Discount Button', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Apply Points', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_apply_discounts',
	),

	'label_applied_coupon_label'           => array(
		'name'      => esc_html__( 'Applied Points Discount Label', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => esc_html__( 'Redeem points', 'yith-woocommerce-points-and-rewards' ),
		'id'        => 'ywpar_label_applied_coupon',
	),

	'labels_title_end'                     => array(
		'type' => 'sectionend',
		'id'   => 'ywpar_labels_title_end',
	),

);

return apply_filters( 'ywpar_customization_options', array( 'customization' => $section1 ) );


