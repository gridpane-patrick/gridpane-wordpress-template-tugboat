<?php

namespace Vibe\Split_Orders\Emails;

use Vibe\Split_Orders\Split_Orders;
use WC_Email;
use WC_Order;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Customer Order Split Email
 *
 * @since 1.3.0
 */
class Customer_Order_Split extends WC_Email {

	public $plugin_id = 'vibe_split_orders';

	/**
	 * The new order that was split from the original order
	 *
	 * @var WC_Order
	 */
	public $new_order;

	/**
	 * The original order that was split
	 *
	 * @var WC_Order
	 */
	public $old_order;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id             = 'customer_order_split';
		$this->customer_email = true;
		$this->title          = __( 'Split order', 'split-orders' );
		$this->description    = __( 'Split order emails are sent to customers when an order of theirs has been split.', 'split-orders' );
		$this->template_html  = 'emails/customer-order-split.php';
		$this->template_plain = 'emails/plain/customer-order-split.php';
		$this->template_base  = vibe_split_orders()->path( 'templates/' );

		$this->placeholders   = array(
			'{new_order_date}'   => '',
			'{new_order_number}' => '',
			'{old_order_date}'   => '',
			'{old_order_number}' => '',
		);

		// Triggers for this email
		add_action( Split_Orders::hook_prefix( 'after_order_split' ), array( $this, 'trigger' ), 10, 3 );

		// Call parent constructor
		parent::__construct();
	}

	/**
	 * Initialise Settings Form Fields
	 */
	public function init_form_fields() {
		/* translators: %s: list of placeholders */
		$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'split-orders' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );
		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'split-orders' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'split-orders' ),
				'default' => 'no',
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'split-orders' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'explanation_text'    => array(
				'title'       => __( 'Explanation text', 'split-orders' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_explanation_text(),
				'default'     => '',
			),
			'heading'    => array(
				'title'       => __( 'Email heading', 'split-orders' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'split-orders' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'split-orders' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Trigger the sending of this email
	 *
	 * @param WC_Order $new_order The new order that was split from the original order
	 * @param WC_Order $old_order The original order that was split
	 * @param array $items         An array of items that have been split into the new order, with line item ID as the key and the quantity
	 *                             as the value.
	 */
	public function trigger( WC_Order $new_order, WC_Order $old_order, array $items ) {
		$this->setup_locale();

		$this->new_order                          = $new_order;
		$this->old_order                          = $old_order;
		$this->object                             = $this->new_order;
		$this->recipient                          = $this->new_order->get_billing_email();
		$this->placeholders['{new_order_date}']   = wc_format_datetime( $this->new_order->get_date_created() );
		$this->placeholders['{new_order_number}'] = $this->new_order->get_order_number();
		$this->placeholders['{old_order_date}']   = wc_format_datetime( $this->old_order->get_date_created() );
		$this->placeholders['{old_order_number}'] = $this->old_order->get_order_number();

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * Get default email explanation text
	 *
	 * @return string
	 */
	public function get_default_explanation_text() {
		return __( 'Just to let you know - we have split your order so each part can be processed separately.', 'split-orders' );
	}

	/**
	 * Get email explanation text
	 *
	 * @return string
	 */
	public function get_explanation_text() {
		return apply_filters( Split_Orders::hook_prefix( "{$this->id}_explanation_text" ), $this->format_string( $this->get_option( 'explanation_text', $this->get_default_explanation_text() ) ), $this->new_order, $this->old_order, $this );
	}

	/**
	 * Get default email subject
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Your order has been split', 'split-orders' );
	}

	/**
	 * Get default email heading
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Your order has been split', 'split-orders' );
	}

	/**
	 * Get content html
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'new_order'        => $this->new_order,
				'old_order'        => $this->old_order,
				'email_heading'    => $this->get_heading(),
				'explanation_text' => $this->get_explanation_text(),
				'sent_to_admin'    => false,
				'plain_text'       => false,
				'email'            => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get content plain
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'new_order'        => $this->new_order,
				'old_order'        => $this->old_order,
				'email_heading'    => $this->get_heading(),
				'explanation_text' => $this->get_explanation_text(),
				'sent_to_admin'    => false,
				'plain_text'       => true,
				'email'            => $this,
			),
			'',
			$this->template_base
		);
	}
}
