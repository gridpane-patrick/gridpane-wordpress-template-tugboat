<?php
if ( ! class_exists( 'WC_Abstract_Privacy' ) ) {
	return;
}

class WC_Shipping_Aramex_Privacy extends WC_Abstract_Privacy {
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		parent::__construct( __( 'Aramex', 'woocommerce-shipping-aramex' ) );

		$this->add_exporter( 'woocommerce-shipping-aramex-order-data', __( 'WooCommerce Aramex Order Data', 'woocommerce-shipping-aramex' ), array( $this, 'order_data_exporter' ) );
		$this->add_eraser( 'woocommerce-shipping-aramex-order-data', __( 'WooCommerce Aramex Data', 'woocommerce-shipping-aramex' ), array( $this, 'order_data_eraser' ) );
	}

	/**
	 * Returns a list of orders.
	 *
	 * @param string  $email_address
	 * @param int     $page
	 *
	 * @return array WP_Post
	 */
	protected function get_aramex_orders( $email_address, $page ) {
		$user = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.

		$order_query    = array(
			'limit'          => 10,
			'page'           => $page,
		);

		if ( $user instanceof WP_User ) {
			$order_query['customer_id'] = (int) $user->ID;
		} else {
			$order_query['billing_email'] = $email_address;
		}

		return wc_get_orders( $order_query );
	}

	/**
	 * Gets the message of the privacy to display.
	 *
	 */
	public function get_privacy_message() {
		return wpautop( sprintf( __( 'By using this extension, you may be storing personal data or sharing data with an external service. <a href="%s" target="_blank">Learn more about how this works, including what you may want to include in your privacy policy.</a>', 'woocommerce-shipping-aramex' ), 'https://docs.woocommerce.com/document/privacy-shipping/#woocommerce-shipping-aramex' ) );
	}

	/**
	 * Handle exporting data for Orders.
	 *
	 * @param string $email_address E-mail address to export.
	 * @param int    $page          Pagination of data.
	 *
	 * @return array
	 */
	public function order_data_exporter( $email_address, $page = 1 ) {
		$done           = false;
		$data_to_export = array();

		$orders = $this->get_aramex_orders( $email_address, (int) $page );

		$done = true;

		if ( 0 < count( $orders ) ) {
			foreach ( $orders as $order ) {
				$data_to_export[] = array(
					'group_id'    => 'woocommerce_orders',
					'group_label' => __( 'Orders', 'woocommerce-shipping-aramex' ),
					'item_id'     => 'order-' . $order->get_id(),
					'data'        => array(
						array(
							'name'  => __( 'Aramex Pickup Date', 'woocommerce-shipping-aramex' ),
							'value' => get_post_meta( $order->get_id(), '_pickup_date', true ),
						),
						array(
							'name'  => __( 'Aramex Pickup ID', 'woocommerce-shipping-aramex' ),
							'value' => get_post_meta( $order->get_id(), '_pickup_id', true ),
						),
						array(
							'name'  => __( 'Aramex Pickup GUID', 'woocommerce-shipping-aramex' ),
							'value' => get_post_meta( $order->get_id(), '_pickup_guid', true ),
						),
					),
				);
			}

			$done = 10 > count( $orders );
		}

		return array(
			'data' => $data_to_export,
			'done' => $done,
		);
	}

	/**
	 * Finds and erases order data by email address.
	 *
	 * @since 3.4.0
	 * @param string $email_address The user email address.
	 * @param int    $page  Page.
	 * @return array An array of personal data in name value pairs
	 */
	public function order_data_eraser( $email_address, $page ) {
		$orders = $this->get_aramex_orders( $email_address, (int) $page );

		$items_removed  = false;
		$items_retained = false;
		$messages       = array();

		foreach ( (array) $orders as $order ) {
			$order = wc_get_order( $order->get_id() );

			list( $removed, $retained, $msgs ) = $this->maybe_handle_order( $order );
			$items_removed  |= $removed;
			$items_retained |= $retained;
			$messages        = array_merge( $messages, $msgs );
		}

		// Tell core if we have more orders to work on still
		$done = count( $orders ) < 10;

		return array(
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => $done,
		);
	}

	/**
	 * Handle eraser of data tied to Orders
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	protected function maybe_handle_order( $order ) {
		$order_id    = $order->get_id();
		$pickup_date = get_post_meta( $order_id, '_pickup_date', true );
		$pickup_id   = get_post_meta( $order_id, '_pickup_id', true );
		$pickup_guid = get_post_meta( $order_id, '_pickup_guid', true );

		if ( empty( $pickup_date ) && empty( $pickup_id ) && empty( $pickup_guid ) ) {
			return array( false, false, array() );
		}

		delete_post_meta( $order_id, '_pickup_date' );
		delete_post_meta( $order_id, '_pickup_id' );
		delete_post_meta( $order_id, '_pickup_guid' );

		return array( true, false, array( __( 'Aramex Order Data Erased.', 'woocommerce-shipping-aramex' ) ) );
	}
}

new WC_Shipping_Aramex_Privacy();
