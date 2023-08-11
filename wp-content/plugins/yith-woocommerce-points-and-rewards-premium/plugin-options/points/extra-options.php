<?php
/**
 * Plugin Options
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;


$birthday_field_pages_to_show = array(
	'my-account'    => esc_html__( 'My Account Page', 'yith-woocommerce-points-and-rewards' ),
	'register_form' => esc_html__( 'Registration Form', 'yith-woocommerce-points-and-rewards' ),
	'checkout'      => esc_html__( 'Checkout Page', 'yith-woocommerce-points-and-rewards' ),
);

$custom_tab = array(
	'points-extra' => array(
		'point_extra_title'                          => array(
			'name' => esc_html__( 'Extra Points', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_point_extra_title',
		),
		'point_extra_title_end'                      => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_point_extra_title_end',
		),

		/* EXTRA POINTS REGISTRATION */
		'point_on_registration_title'                => array(
			'name' => esc_html__( 'Extra points for user registration', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_point_on_registration_title_title',
		),

		'enable_points_on_registration_exp'          => array(
			'name'      => esc_html__( 'Assign points when a user registers', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Assign extra points to newly registered users', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_points_on_registration_exp',
		),

		'points_on_registration'                     => array(
			'name'              => esc_html__( 'Points to assign to new users', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => __( 'Set how many points to assign to new users', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 1,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => 'style="width:70px"',
			'id'                => 'ywpar_points_on_registration',
			'deps'              => array(
				'id'    => 'ywpar_enable_points_on_registration_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'point_on_registration_title_end'            => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_point_on_registration_title_end',
		),

		/* EXTRA POINTS REGISTRATION */
		'point_on_registration_title'                => array(
			'name' => esc_html__( 'Extra points for user registration', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_point_on_registration_title_title',
		),

		'enable_points_on_registration_exp'          => array(
			'name'      => esc_html__( 'Assign points when a user registers', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Assign extra points to newly registered users', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_points_on_registration_exp',
		),

		'points_on_registration'                     => array(
			'name'              => esc_html__( 'Points to assign:', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set how many points to assing', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 1,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => 'style="width:70px"',
			'id'                => 'ywpar_points_on_registration',
			'deps'              => array(
				'id'    => 'ywpar_enable_points_on_registration_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'point_on_registration_title_end'            => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_point_on_registration_title_end',
		),

		/* DAILY LOGIN POINTS */
		'point_on_daily_login_title'                 => array(
			'name' => esc_html__( 'Extra points for the first daily login', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_point_on_daily_login_title',
		),

		'enable_points_on_daily_login_exp'           => array(
			'name'      => esc_html__( 'Assign points each first time the user logs in daily', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Assign extra points for the each first daily login', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_points_on_daily_login_exp',
		),

		'points_on_daily_login'                      => array(
			'name'              => esc_html__( 'Points to assign:', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set how many points to assign', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 1,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => 'style="width:70px"',
			'id'                => 'ywpar_points_on_daily_login',
			'deps'              => array(
				'id'    => 'ywpar_enable_points_on_daily_login_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'point_on_daily_login_title_end'             => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_point_on_daily_login_title_end',
		),

		/* Complete Profile POINTS */
		'point_on_completed_profile_title'           => array(
			'name' => esc_html__( 'Extra points for completed profile', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_point_on_completed_profile_title',
		),

		'enable_points_on_completed_profile_exp'     => array(
			'name'      => esc_html__( 'Assign points when the user completes all fields in the user profile', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Assign extra points for a completed profile', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_points_on_completed_profile_exp',
		),

		'points_on_completed_profile'                => array(
			'name'              => esc_html__( 'Points to assign:', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set how many points to assign', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 1,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => 'style="width:70px"',
			'id'                => 'ywpar_points_on_completed_profile',
			'deps'              => array(
				'id'    => 'ywpar_enable_points_on_completed_profile_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'point_on_completed_profile_title_end'       => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_point_on_completed_profile_title_end',
		),

		/* Referral Registration POINTS */
		'point_on_referral_registration_title'       => array(
			'name' => esc_html__( 'Extra points for referrals', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_point_on_referral_registration_title',
		),

		'enable_points_on_referral_registration_exp' => array(
			'name'      => esc_html__( "Referral link signup", 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Assign extra points when a new user signs up using a customer created referral link', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_points_on_referral_registration_exp',
		),

		'points_referral_registration_profile'       => array(
			'name'              => esc_html__( 'Points to assign:', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set how many points to assign', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 1,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => 'style="width:70px"',
			'id'                => 'ywpar_points_referral_registration',
			'deps'              => array(
				'id'    => 'ywpar_enable_points_on_referral_registration_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'remove_points_on_referral_registration_exp' => array(
			'name'      => esc_html__( 'Revoke referral points if referred user account is deleted or banned', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Enable to automatically delete referral points if the referred user account is deleted or banned', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_remove_points_on_referral_registration_exp',
			'deps'      => array(
				'id'    => 'ywpar_enable_points_on_referral_registration_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'point_on_referral_registration_title_end'   => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_point_on_referral_registration_title_end',
		),

		/* Referral Purchase POINTS */
		'point_on_referral_purchase_title'           => array(
			'name' => esc_html__( 'Extra points for referral purchase', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_point_on_referral_purchase_title',
		),

		'enable_points_on_referral_purchase_exp'     => array(
			'name'      => esc_html__( "Assign points when a user purchases through the user's referral link", 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Assign extra points when a user places an order using the referral link', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_points_on_referral_purchase_exp',
		),

		'points_referral_purchase'                   => array(
			'name'              => esc_html__( 'Points to assign:', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set how many points to assign', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 1,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => 'style="width:70px"',
			'id'                => 'ywpar_points_referral_purchase',
			'deps'              => array(
				'id'    => 'ywpar_enable_points_on_referral_purchase_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'remove_points_on_referral_purchase_exp'     => array(
			'name'      => esc_html__( 'Revoke referral if referred user account is deleted or banned', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Enable to automatically delete referral points if the referred user account is deleted or banned', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_remove_points_on_referral_purchase_exp',
			'deps'      => array(
				'id'    => 'ywpar_enable_points_on_referral_purchase_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'point_on_referral_purchase_title_end'       => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_point_on_referral_purchase_title_end',
		),

		/* EXTRA POINTS for User that collected more points */
		'point_on_collected_points_title'            => array(
			'name' => esc_html__( 'Extra points for user that collected the most points', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_point_on_collected_points',
		),

		'enable_points_on_collected_points_exp'      => array(
			'name'      => esc_html__( 'Assign points to the user that collected the most points', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Assign extra points to the user that collected the most points', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_point_on_collected_points_exp',
		),

		'points_on_collected_points'                 => array(
			'name'              => esc_html__( 'Points to assign:', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set how many points to assign to the user that collected the most points', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 1,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => 'style=width:100px',
			'id'                => 'ywpar_points_on_collected_points',
			'deps'              => array(
				'id'    => 'ywpar_enable_point_on_collected_points_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'points_on_collected_points_timing'          => array(
			'name'              => esc_html__( 'Check and assign points on:', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set when to check points of all users and assign these extra points', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'options-extrapoints-timing',
			'default'           => array(
				'day'  => 'first_day',
				'when' => 'each_month',
			),
			'custom_attributes' => 'style=width:150px',
			'step'              => 1,
			'min'               => 1,
			'id'                => 'ywpar_points_on_collected_points_timing',
			'deps'              => array(
				'id'    => 'ywpar_enable_point_on_collected_points_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'point_on_collected_points_title_end'        => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_point_collected_points_title_end',
		),

		/* LEVELS EXTRA POINTS */
		'point_on_levels_title'                      => array(
			'name' => esc_html__( 'Extra points for users that achieve specific levels', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_point_on_achieve_level',
		),

		'enable_points_on_levels_exp'                => array(
			'name'      => esc_html__( 'Assign points to the users that achieve levels', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Assign extra points to users that achieve specific levels', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_point_on_achieve_level_exp',
		),

		'points_on_levels'                           => array(
			'name'              => esc_html__( 'Points to assign:', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set how many points to assign to users when they achieve a specific level', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'options-extrapoints-levels',
			'default'           => array(
				'list' => array(
					array(
						'points' => 10,
						'level'  => '',
					),
				),
			),
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => 'style=width:200px',
			'id'                => 'ywpar_points_on_levels',
			'deps'              => array(
				'id'    => 'ywpar_enable_point_on_achieve_level_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'point_on_levels_title_end'                  => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_point_on_levels_title_end',
		),

		/* EXTRA POINTS POINTS COLLECTED */
		'number_of_points_title'                     => array(
			'name' => esc_html__( 'Enable points incentive on total collected points', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_number_of_points_title',
		),

		'enable_number_of_points_exp'                => array(
			'name'      => esc_html__( 'Assign extra points based on the total number of points collected', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Assign extra points to users that achieve a set number points collected', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_number_of_points_exp',
		),

		'number_of_points_exp'                       => array(
			'name'        => esc_html__( 'Points to assign per points collected', 'yith-woocommerce-points-and-rewards' ),
			'desc'        => esc_html__( 'Set how many points to assign based on the total of points collected', 'yith-woocommerce-points-and-rewards' ),
			'type'        => 'yith-field',
			'yith-type'   => 'options-extrapoints',
			'label'       => esc_html__( 'points =', 'yith-woocommerce-points-and-rewards' ),
			'default'     => array(
				'list' => array(
					array(
						'number' => 1,
						'points' => 10,
						'repeat' => 0,
					),
				),
			),
			'repeat_last' => true,
			'id'          => 'ywpar_number_of_points_exp',
			'deps'        => array(
				'id'    => 'ywpar_enable_number_of_points_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'number_of_points_title_end'                 => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_number_of_points_title_end',
		),

		/* EXTRA POINTS BIRTHDAY */
		'point_on_birthday_title'                    => array(
			'name' => esc_html__( 'Extra points for users\' birthday', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_point_on_registration_title',
		),

		'enable_points_on_birthday_exp'              => array(
			'name'      => esc_html__( 'Assign extra points on customer\'s birthday', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Enable to assign points on customer\'s birthday', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_points_on_birthday_exp',
		),

		'points_on_birthday'                         => array(
			'name'              => esc_html__( 'Points to assign per birthday', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set how many points to assign to users on their birthday', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'default'           => 1,
			'step'              => 1,
			'min'               => 1,
			'custom_attributes' => 'style="width:70px"',
			'id'                => 'ywpar_points_on_birthday',
			'deps'              => array(
				'id'    => 'ywpar_enable_points_on_birthday_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'birthday_date_field_where'                  => array(
			'name'      => esc_html__( 'Show birthday input field in', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Choose where to show the Birthday Date Field', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'checkbox-array',
			'multiple'  => true,
			'options'   => $birthday_field_pages_to_show,
			'default'   => array( 'my-account', 'register_form', 'checkout' ),
			'id'        => 'ywpar_birthday_date_field_where',
			'deps'      => array(
				'id'    => 'ywpar_enable_points_on_birthday_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'birthday_date_format'                       => array(
			'name'      => esc_html__( 'Birthday input date format', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Choose the format to use for the Birthday Date Field', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'class'     => 'wc-enhanced-select',
			'options'   => ywpar_date_placeholders(),
			'default'   => 'yy-mm-dd',
			'id'        => 'ywpar_birthday_date_format',
			'deps'      => array(
				'id'    => 'ywpar_enable_points_on_birthday_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'point_on_birthday_title_end'                => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_point_on_birthday_title_end',
		),

		/* EXTRA POINTS REVIEWS */
		'extra_points_review_title'                  => array(
			'name' => esc_html__( 'Extra Points for reviews', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_extra_points_review_title',
		),

		'enable_review_exp'                          => array(
			'name'      => esc_html__( 'Assign points to users that leave a product review', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Enable to assign points to users that leave a product review', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_review_exp',
		),

		'review_exp'                                 => array(
			'name'                   => esc_html__( 'Points to assign for reviews', 'yith-woocommerce-points-and-rewards' ),
			'desc'                   => esc_html__( 'Set how many points to assign for reviews. Check "repeat" to repeat the rules: if you set 5 reviews = 10 points. Check repeat, and 10 reviews will get 20 points and so on', 'yith-woocommerce-points-and-rewards' ),
			'type'                   => 'yith-field',
			'yith-type'              => 'options-extrapoints',
			'label'                  => esc_html_x( 'review(s) is equivalent to', 'Part of an option where for each renew are assigned some points', 'yith-woocommerce-points-and-rewards' ),
			'default'                => array(
				'list' => array(
					array(
						'number' => 1,
						'points' => 10,
						'repeat' => 0,
					),
				),
			),
			'repeat_last'            => true,
			'id'                     => 'ywpar_review_exp',
			'deps'                   => array(
				'id'    => 'ywpar_enable_review_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
			'yith-sanitize-callback' => 'ywpar_option_extrapoints_sanitize',
		),

		'extra_points_review_title_end'              => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_extra_points_review_title_end',
		),

		/* EXTRA POINTS NUM ORDERS */
		'extra_num_order_title'                      => array(
			'name' => esc_html__( 'Extra points for orders', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_extra_num_order_title',
		),

		'enable_num_order_exp'                       => array(
			'name'      => esc_html__( 'Assign points based on orders placed', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Enable to assign points to a user, when an order is placed', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_num_order_exp',
		),

		'num_order_exp'                              => array(
			'name'                   => esc_html__( 'Points to assign per order', 'yith-woocommerce-points-and-rewards' ),
			'desc'                   => esc_html__( 'Set how many points to assign per order placed', 'yith-woocommerce-points-and-rewards' ),
			'type'                   => 'yith-field',
			'yith-type'              => 'options-extrapoints',
			'label'                  => esc_html__( 'order(s) =', 'yith-woocommerce-points-and-rewards' ),
			'default'                => array(
				'list' => array(
					array(
						'number' => 1,
						'points' => 10,
						'repeat' => 0,
					),
				),
			),
			'id'                     => 'ywpar_num_order_exp',

			'deps'                   => array(
				'id'    => 'ywpar_enable_num_order_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
			'yith-sanitize-callback' => 'ywpar_option_extrapoints_sanitize',
		),

		'extra_num_order_title_end'                  => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_extra_num_order_title_end',
		),

		/* EXTRA POINTS CART TOTAL */
		'checkout_threshold_title'                   => array(
			'name' => esc_html__( 'Extra Points for cart total', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_checkout_threshold_title',
		),

		'enable_checkout_threshold_exp'              => array(
			'name'      => esc_html__( 'Assign points based on the cart total', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Enable to assign points to user, based on the cart total', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_checkout_threshold_exp',
		),

		'checkout_threshold_exp'                     => array(
			'name'        => esc_html__( 'Points to assign on cart total', 'yith-woocommerce-points-and-rewards' ),
			'desc'        => esc_html__( 'Set how many points to assign based on cart total', 'yith-woocommerce-points-and-rewards' ),
			'type'        => 'yith-field',
			'yith-type'   => 'options-extrapoints-multi',
			'label'       => '',
			'default'     => array(
				'list' => array(
					array(
						'number' => 1000,
						'points' => 10,
					),
				),
			),
			'show_repeat' => false,
			'id'          => 'ywpar_checkout_threshold_exp',
			'deps'        => array(
				'id'    => 'ywpar_enable_checkout_threshold_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'checkout_threshold_not_cumulate'            => array(
			'name'      => esc_html__( 'Based on cart total, allow user to achieve multiple thresholds', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'If you created multiple points based rules on cart total, choose whether to apply all rules or just the highest value rule', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'default'   => 'yes',
			'options'   => array(
				'no'  => esc_html__( 'Apply all rules and sum up the points', 'yith-woocommerce-points-and-rewards' ),
				'yes' => esc_html__( 'Apply only the rule with the highest number of points in cart', 'yith-woocommerce-points-and-rewards' ),
			),
			'id'        => 'ywpar_checkout_threshold_not_cumulate',
			'deps'      => array(
				'id'    => 'ywpar_enable_checkout_threshold_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'checkout_threshold_show_message'            => array(
			'name'      => esc_html__( 'Show threshold message in cart and checkout', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Enable to show messages about thresholds achieved on Cart & Checkout pages', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_checkout_threshold_show_message',
			'deps'      => array(
				'id'    => 'ywpar_enable_checkout_threshold_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),
		'checkout_threshold_show_message_title'      => array(
			'name'              => esc_html__( 'Title of threshold message', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__( 'Set the text to show as title of threshold message', 'yith-woocommerce-points-and-rewards' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'default'           => esc_html__( 'Get points based on your order amount!', 'yith-woocommerce-points-and-rewards' ),
			'id'                => 'ywpar_checkout_threshold_show_message_title',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_checkout_threshold_exp,ywpar_checkout_threshold_show_message',
				'data-deps_value' => 'yes,yes',
			),
		),
		'checkout_threshold_title_end'               => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_checkout_threshold_title_end',
		),

		/* EXTRA POINTS AMOUNT SPENT */
		'amount_spent_title'                         => array(
			'name' => esc_html__( 'Extra points for amount spent', 'yith-woocommerce-points-and-rewards' ),
			'type' => 'title',
			'id'   => 'ywpar_amount_spent_title',
		),

		'enable_amount_spent_exp'                    => array(
			'name'      => esc_html__( 'Assign points based on the total amount spent', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__( 'Enable to assign points to a user, when an order is placed', 'yith-woocommerce-points-and-rewards' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'id'        => 'ywpar_enable_amount_spent_exp',
		),

		'amount_spent_exp'                           => array(
			'name'        => esc_html__( 'Points to assign per amount reached', 'yith-woocommerce-points-and-rewards' ),
			'desc'        => esc_html__( 'Set how many points to allocate for each amount reached', 'yith-woocommerce-points-and-rewards' ),
			'type'        => 'yith-field',
			'yith-type'   => 'options-extrapoints-multi',
			'repeat_last' => true,
			'label'       => '',
			'default'     => array(
				'list' => array(
					array(
						'number' => 1000,
						'points' => 10,
						'repeat' => 0,
					),
				),
			),
			'id'          => 'ywpar_amount_spent_exp',
			'deps'        => array(
				'id'    => 'ywpar_enable_amount_spent_exp',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),

		'amount_spent_title_end'                     => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_amount_spent_title_end',
		),
	),
);

if ( 'no' === ywpar_get_option( 'enable_ranking', 'yes' ) ) {
	unset( $custom_tab['point_on_collected_points_title']);
	unset( $custom_tab['enable_points_on_collected_points_exp']);
	unset( $custom_tab['points_on_collected_points']);
	unset( $custom_tab['points_on_collected_points_timing']);
	unset( $custom_tab['point_on_collected_points_title_end']);
}

return apply_filters( 'ywpar_extra_points_options', $custom_tab );


