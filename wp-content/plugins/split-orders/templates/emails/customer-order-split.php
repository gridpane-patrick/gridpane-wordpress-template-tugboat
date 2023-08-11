<?php
/**
 * Customer order split email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-order-split.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper for imported variables
 *
 * @var string $email_heading                                The email heading
 * @var Vibe\Split_Orders\Emails\Customer_Order_Split $email The email object
 * @var WC_Order $new_order                                  The new order that was split from the original order
 * @var WC_Order $old_order                                  The original order that was split
 * @var bool $sent_to_admin                                  Whether the email is being sent to the admin
 * @var bool $plain_text                                     Whether the email is in plain text
 * @var string $explanation_text                             The copy to be used before the order details
 */

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer first name */ ?>
	<p><?php printf( esc_html__( 'Hi %s,', 'split-orders' ), esc_html( $new_order->get_billing_first_name() ) ); ?></p>

	<p><?php echo esc_html( $explanation_text ); ?></p>
<?php


/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $old_order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $new_order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $old_order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
