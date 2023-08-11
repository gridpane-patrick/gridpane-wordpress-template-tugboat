<?php
/**
 * Email Options
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

$section1 = array(
	'email_title'                       => array(
		'name' => esc_html__( 'Expiring points', 'yith-woocommerce-points-and-rewards' ),
		'type' => 'title',
		'id'   => 'ywpar_email_title',
	),
	'send_email_before_expiration_date' => array(
		'name'      => esc_html__( 'Send an email before the expiration date', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
		'id'        => 'ywpar_send_email_before_expiration_date',
	),

	'send_email_days_before'            => array(
		'name'      => esc_html__( 'Days before points expire', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Number of days before \'points expiration\' when the email will be sent', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'number',
		'default'   => '',
		'id'        => 'ywpar_send_email_days_before',
		'deps'      => array(
			'id'    => 'ywpar_send_email_before_expiration_date',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),

	'expiration_email_content'          => array(
		'name'      => esc_html__( 'Email content', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => _x(
			'You can use the following placeholders,<br>
                {username} = customer\'s username <br>
                {first_name} = customer\'s  first name <br>
                {last_name} = customer\'s last name <br>
                {expiring_points} = expiring points <br>
                {label_points} = label for points <br>
                {expiring_date} = points expiry date<br>
                {total_points} = current balance<br>
                {shop_url} = url of the shop<br>
                {discount} = value of the discount<br>
				{website_name} = website name',
			'do not translate the text inside the brackets',
			'yith-woocommerce-points-and-rewards'
		),
		'yith-type' => 'textarea',
		'type'      => 'yith-field',
		'default'   => _x(
			'Hi {username}!
			
We send you this message because the <b>{expiring_points} {label_points}</b> you\'ve already earned in our shop will expire on {expiring_date}.

<div class="points_banner">
Use them now to get a <b>discount of {discount}</b> in your purchase!
<a href="{shop_url}">Check our shop now ></a>
</div>

Regards,
{website_name} staff
',
			'do not translate the text inside the brackets',
			'yith-woocommerce-points-and-rewards'
		),
		'id'        => 'ywpar_expiration_email_content',
		'deps'      => array(
			'id'    => 'ywpar_send_email_before_expiration_date',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),


	'email_title_end'                   => array(
		'type' => 'sectionend',
		'id'   => 'ywpar_email_title_end',
	),

	'update_points_email_title'         => array(
		'name' => esc_html__( 'Updated Points', 'yith-woocommerce-points-and-rewards' ),
		'type' => 'title',
		'id'   => 'ywpar_update_points_email_title',
	),

	'enable_update_point_email'         => array(
		'name'      => esc_html__( 'Enable this email notification on updated points', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => '',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
		'id'        => 'ywpar_enable_update_point_email',
	),

	'update_point_mail_time'            => array(
		'name'      => esc_html__( 'Send Email', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'daily'        => esc_html__( 'Once a day if points have been updated', 'yith-woocommerce-points-and-rewards' ),
			'every_update' => esc_html__( 'As soon as points are updated', 'yith-woocommerce-points-and-rewards' ),
		),
		'default'   => 'daily',
		'id'        => 'ywpar_update_point_mail_time',
		'deps'      => array(
			'id'    => 'ywpar_enable_update_point_email',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),

	'update_point_mail_on_admin_action' => array(
		'name'      => esc_html__( 'Avoid email sending for manual update', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => esc_html__( 'Enable to not send email when the admin updates points manually', 'yith-woocommerce-points-and-rewards' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
		'id'        => 'ywpar_update_point_mail_on_admin_action',
		'deps'      => array(
			'id'    => 'ywpar_update_point_mail_time',
			'value' => 'every_update',
			'type'  => 'hide',
		),

	),

	'update_point_email_content'        => array(
		'name'      => esc_html__( 'Email content', 'yith-woocommerce-points-and-rewards' ),
		'desc'      => sprintf(
			'%s {username} = %s {first_name} = %s {last_name} = %s {latest_updates} = %s {total_points} = %s %s {shop_url} = %s {website_name} = %s',
			esc_html__( 'You can use the following placeholders', 'yith-woocommerce-points-and-rewards' ) . '<br>',
			esc_html__( "customer's username", 'yith-woocommerce-points-and-rewards' ) . '<br>',
			esc_html__( "customer's first name", 'yith-woocommerce-points-and-rewards' ) . '<br>',
			esc_html__( "customer's last name", 'yith-woocommerce-points-and-rewards' ) . '<br>',
			esc_html__( 'latest updates of your points', 'yith-woocommerce-points-and-rewards' ) . '<br>',
			esc_html__( 'label for points', 'yith-woocommerce-points-and-rewards' ),
			esc_html__( 'current balance', 'yith-woocommerce-points-and-rewards' ) . '<br>',
			esc_html__( 'Shop url', 'yith-woocommerce-points-and-rewards' ) . '<br>',
			esc_html__( 'Website name', 'yith-woocommerce-points-and-rewards' )
		),
		'yith-type' => 'textarea',
		'type'      => 'yith-field',
		'default'   => _x(
			'Hi {username}!
			
Here you can find the updated balance of your {label_points}.
 
 <div class="points_banner">
 Total points collected: <b>{total_points}</b>
 <a href="{shop_url}">Redeem them in your next order ></a>
</div>

 Well done!
 {website_name} staff',
			'do not translate the text inside the brackets',
			'yith-woocommerce-points-and-rewards'
		),
		'id'        => 'ywpar_update_point_email_content',
		'deps'      => array(
			'id'    => 'ywpar_enable_update_point_email',
			'value' => 'yes',
			'type'  => 'hide',
		),
	),

	'update_points_email_title_end'     => array(
		'type' => 'sectionend',
		'id'   => 'ywpar_update_points_email_title_end',
	),



);

return array( 'emails' => $section1 );
