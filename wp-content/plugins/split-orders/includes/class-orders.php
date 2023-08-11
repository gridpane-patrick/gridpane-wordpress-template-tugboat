<?php

namespace Vibe\Split_Orders;

use Automattic\WooCommerce\Admin\Schedulers\OrdersScheduler as OldOrdersScheduler;
use Automattic\WooCommerce\Internal\Admin\Schedulers\OrdersScheduler;
use Exception;
use WC_Data_Exception;
use WC_Order;
use WC_Order_Item_Product;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Handles order functions such as splitting orders and adding notes
 *
 * @since 1.0
 */
class Orders {

	/**
	 * Returns true if the given order can be split by the current user
	 *
	 * @param int $order_id The ID of the order to check
	 *
	 * @return bool True if the order can be split and false otherwise
	 */
	public static function can_split( $order_id ) {
		$user_can = current_user_can( 'edit_shop_order', $order_id );

		return apply_filters( Split_Orders::hook_prefix( 'can_split' ), $user_can, wc_get_order( $order_id ) );
	}

	/**
	 * Splits an order into two orders with the provided array of items moved to a new order.
	 *
	 * The split order will be assigned the following information to match the original order:
	 *
	 * - Billing address
	 * - Shipping address
	 * - Date created
	 * - Currency
	 * - Customer IP
	 * - Customer user agent
	 * - Customer note
	 * - Date paid
	 * - Date completed
	 * - Payment method
	 * - Payment method title
	 * - Transaction ID
	 *
	 * Shipping methods are also copied to the new order, but with the shipping price set to zero.
	 *
	 * @param int   $order_id    The ID of the order to split
	 * @param array $items       An array of items to split to the new order, with line item ID as the key and the quantity
	 *                           as the value. All line item IDs must match line items from the given order and the quantity
	 *                           must not exceed the quantity of the line item on the existing order.
	 * @param array $meta_fields Additional meta data fields to copy if they exist on the source order
	 *
	 * @return WC_Order The new order that was created
	 * @throws WC_Data_Exception Exception thrown if products or meta data cannot be assigned to the new order
	 * @throws Exception Exception thrown if unable to set line item meta on the new order line items
	 */
	public static function split( $order_id, array $items, array $meta_fields = array() ) {
		$source_order        = wc_get_order( $order_id );
		$items_for_new_order = array();

		foreach ( $items as $item_id => $item_split_qty ) {
			// Sanitize as a stock amount
			$item_split_qty = wc_stock_amount( $item_split_qty );

			/* @var WC_Order_Item_Product $source_item */
			$source_item = $source_order->get_item( $item_id );
			$subtotal    = $source_order->get_item_subtotal( $source_item, false, false );
			$total       = $source_order->get_item_total( $source_item, false, false );

			$original_qty           = $source_item->get_quantity( 'edit' );
			$original_reduced_stock = $source_item->get_meta( '_reduced_stock' );
			$updated_qty            = $original_qty - $item_split_qty;

			// Reduced stock value should be split between the items, prioritising the original order
			$updated_reduced_stock = ( $updated_qty >= $original_reduced_stock ) ? $original_reduced_stock : $updated_qty;
			$reduced_stock         = $original_reduced_stock ? ( $original_reduced_stock - $updated_reduced_stock ) : null;

			$target_item = clone $source_item;
			$target_item->set_id( 0 );

			$subtotal_price = max( 0.0, (float) $subtotal );
			$total_price    = max( 0.0, (float) $total );
			$qty            = max( 0.0, (float) $item_split_qty );
			$subtotal       = $subtotal_price * $qty;
			$total          = $total_price * $qty;

			$target_item->set_quantity( $qty );
			$target_item->set_subtotal( $subtotal );
			$target_item->set_total( $total );

			// Only add reduced stock meta for non-0 value
			if ( $reduced_stock ) {
				$target_item->update_meta_data( '_reduced_stock', $reduced_stock );
			} else {
				$target_item->delete_meta_data( '_reduced_stock' );
			}

			$items_for_new_order[] = $target_item;

			/**
			 * Filters whether to update the source order, by removing the specified quantity of items
			 *
			 * @param bool     $update       True if the source order should be updated, false otherwise. Defaults to true.
			 * @param WC_Order $source_order The original order that is being split
			 * @param array    $items        The line items being split
			 *
			 * @since 1.5.0
			 */
			if ( apply_filters( Split_Orders::hook_prefix( 'update_source_order' ), true, $source_order, $items ) ) {

				if ( 0 == $updated_qty ) {
					$source_order->remove_item( $item_id );
				} else {
					$subtotal = $subtotal_price * $updated_qty;
					$total    = $total_price * $updated_qty;

					$source_item->set_quantity( $updated_qty );
					$source_item->set_subtotal( $subtotal );
					$source_item->set_total( $total );

					// We don't want to add reduced stock meta for a 0 value.
					if ( $updated_reduced_stock ) {
						$source_item->update_meta_data( '_reduced_stock', $updated_reduced_stock );
					}

					$source_item->save();
				}
			}
		}

		// Update the split count on the source order
		$split_count = $source_order->get_meta( '_vibe_split_orders_split_count' );
		$split_count = $split_count ? ( intval( $split_count ) + 1 ) : 1;

		$source_order->update_meta_data( '_vibe_split_orders_split_count', $split_count );

		// Calculate totals also saves changes to the order
		$source_order->calculate_totals();

		$billing_address      = $source_order->get_address( 'billing' );
		$shipping_address     = $source_order->get_address( 'shipping' );
		$date_created         = $source_order->get_date_created();
		$currency             = $source_order->get_currency();
		$prices_include_tax   = $source_order->get_prices_include_tax();
		$customer_ip          = $source_order->get_customer_ip_address();
		$customer_user_agent  = $source_order->get_customer_user_agent();
		$customer_note        = $source_order->get_customer_note();
		$date_paid            = $source_order->get_date_paid();
		$date_completed       = $source_order->get_date_completed();
		$payment_method       = $source_order->get_payment_method();
		$payment_method_title = $source_order->get_payment_method_title();
		$transaction_id       = $source_order->get_transaction_id();
		$shipping_methods     = $source_order->get_shipping_methods();

		$billing_address_index  = get_post_meta( $source_order->get_id(), '_billing_address_index', true );
		$shipping_address_index = get_post_meta( $source_order->get_id(), '_shipping_address_index', true );

		// Disable emails temporarily
		self::maybe_disable_emails( $source_order );

		/* @var WC_Order $target_order */
		$target_order = wc_create_order( array(
			'status'      => apply_filters( Split_Orders::hook_prefix( 'split_order_status' ), $source_order->get_status(), $source_order, $items ),
			'customer_id' => $source_order->get_customer_id(),
			'created_via' => __( 'Order split', 'split-orders' )
		) );

		do_action( Split_Orders::hook_prefix( 'order_created' ), $target_order, $source_order, $items );

		// Restore emails
		self::maybe_restore_emails( $source_order );

		foreach ( $items_for_new_order as $target_item ) {
			$target_order->add_item( $target_item );
		}

		if ( apply_filters( Split_Orders::hook_prefix( 'clone_shipping' ), true, $target_order, $source_order, $items ) ) {
			// Copy shipping with price as zero - a shipping line is often needed by order fulfilment to know the method
			foreach ( $shipping_methods as $shipping_method ) {
				$shipping_method->set_id( 0 );
				$shipping_method->set_total( 0 );

				$target_order->add_item( $shipping_method );
			}
		}

		$target_order->set_address( $billing_address, 'billing' );
		$target_order->set_address( $shipping_address, 'shipping' );
		$target_order->set_currency( $currency );
		$target_order->set_prices_include_tax( $prices_include_tax );
		$target_order->set_customer_ip_address( $customer_ip );
		$target_order->set_customer_user_agent( $customer_user_agent );
		$target_order->set_customer_note( $customer_note );
		$target_order->set_date_paid( $date_paid );
		$target_order->set_date_completed( $date_completed );
		$target_order->set_payment_method( $payment_method );
		$target_order->set_payment_method_title( $payment_method_title );
		$target_order->set_transaction_id( $transaction_id );

		if ( apply_filters( Split_Orders::hook_prefix( 'clone_date_created' ), false, $target_order, $source_order, $items ) ) {
			$target_order->set_date_created( $date_created );
		}

		$meta_fields = apply_filters( Split_Orders::hook_prefix( 'meta_fields' ), $meta_fields, $target_order, $source_order );

		// Copy any additional requested meta fields
		foreach ( $meta_fields as $meta_field ) {
			if ( $source_order->meta_exists( $meta_field ) ) {
				$meta_values = $source_order->get_meta( $meta_field, false, 'edit' );

				foreach ( $meta_values as $meta_value ) {
					$target_order->add_meta_data( $meta_field, $meta_value->value );
				}
			}
		}

		// Copy the address indexes to retain search - WooCommerce doesn't set these up automatically when setting address
		update_post_meta( $target_order->get_id(), '_billing_address_index', $billing_address_index );
		update_post_meta( $target_order->get_id(), '_shipping_address_index', $shipping_address_index );

		// Save meta data about the split itself
		$target_order->add_meta_data( '_vibe_split_orders_split_from', $source_order->get_id(), true );
		$target_order->add_meta_data( '_vibe_split_orders_split_index', $split_count, true );

		// Calculating totals also saves changes to the order
		$target_order->calculate_totals();

		$source_order = wc_get_order( $source_order->get_id() );
		$target_order = wc_get_order( $target_order->get_id() );

		do_action( Split_Orders::hook_prefix( 'orders_updated' ), $target_order, $source_order, $items );

		// Schedule the updated orders to be re-imported to the analytics, to update the stored data
		self::possibly_schedule_import( $source_order, $target_order );

		self::add_splitting_notes( $target_order, $source_order );

		do_action( Split_Orders::hook_prefix( 'after_order_split' ), $target_order, $source_order, $items );

		return $target_order;
	}

	/**
	 * Schedule the import of the updated orders into the WooCommerce Admin
	 *
	 * @param WC_Order $source_order The source order to import
	 * @param WC_Order $target_order The target order to import
	 *
	 * @return void
	 */
	protected static function possibly_schedule_import( WC_Order $source_order, WC_Order $target_order ) {
		$scheduler_function = array( 'Automattic\WooCommerce\Internal\Admin\Schedulers\OrdersScheduler', 'possibly_schedule_import' );
		$scheduler_function_pre64 = array( 'Automattic\WooCommerce\Admin\Schedulers\OrdersScheduler', 'possibly_schedule_import' );

		if ( ! method_exists( ...$scheduler_function ) ) {
			$scheduler_function = $scheduler_function_pre64;
		}

		if ( method_exists( ...$scheduler_function ) ) {
			$scheduler_function( $source_order->get_id() );
			$scheduler_function( $target_order->get_id() );
		}
	}

	/**
	 * Disable customer and admin emails unless vibe_split_orders_disable_emails filter returns false for this order
	 *
	 * Emails that will be disabled are:
	 *
	 * - Customer - Processing order
	 * - Customer - Completed order
	 * - Customer - Refunded order
	 * - Customer - On-hold order
	 * - Admin - New order
	 * - Admin - Cancelled order
	 * - Admin - Failed order
	 *
	 * @param WC_Order $order The order that is being split used for filtering
	 */
	public static function maybe_disable_emails( WC_Order $order ) {
		if ( apply_filters( Split_Orders::hook_prefix( 'disable_emails' ), true, $order ) ) {
			add_action( 'woocommerce_email_enabled_customer_processing_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_customer_completed_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_customer_refunded_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_customer_on_hold_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_cancelled_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_failed_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_new_order', '__return_false' );

			do_action( Split_Orders::hook_prefix( 'emails_disabled' ), $order );
		}
	}

	/**
	 * Restore customer and admin emails unless vibe_split_orders_disable_emails filter returns false for this order
	 *
	 * Emails that will be restored are:
	 *
	 * - Customer - Processing order
	 * - Customer - Completed order
	 * - Customer - Refunded order
	 * - Customer - On-hold order
	 * - Admin - New order
	 * - Admin - Cancelled order
	 * - Admin - Failed order
	 *
	 * @param WC_Order $order The order that is being split used for filtering
	 */
	public static function maybe_restore_emails( WC_Order $order ) {
		if ( apply_filters( Split_Orders::hook_prefix( 'disable_emails' ), true, $order ) ) {
			remove_action( 'woocommerce_email_enabled_customer_processing_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_customer_completed_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_customer_refunded_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_customer_on_hold_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_cancelled_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_failed_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_new_order', '__return_false' );

			do_action( Split_Orders::hook_prefix( 'emails_restored' ), $order );
		}
	}

	/**
	 * Adds a note to the given orders to record a split
	 *
	 * @param WC_Order $new_order      The new order that was split from the original order
	 * @param WC_Order $original_order The original order that was split
	 */
	public static function add_splitting_notes( WC_Order $new_order, WC_Order $original_order ) {
		if ( ! apply_filters( Split_Orders::hook_prefix( 'add_splitting_notes' ), true ) ) {
			return;
		}

		$message = sprintf(
		/* translators: 1: Link to the order split from 2: The order number of the order split from */
			__( 'Order split from <a href="%1$s">#%2$s</a>.', 'split-orders' ),
			get_edit_post_link( $original_order->get_id() ),
			$original_order->get_order_number()
		);

		$new_order->add_order_note( $message, 0, false );

		$message = sprintf(
		/* translators: 1: Link to the order 2: The order number of the order split to */
			__( 'Order split into <a href="%1$s">#%2$s</a>.', 'split-orders' ),
			get_edit_post_link( $new_order->get_id() ),
			$new_order->get_order_number()
		);

		$original_order->add_order_note( $message, 0, false );
	}

	/**
	 * Checks whether the given order is one part of a split order
	 *
	 * @param WC_Order $order The order to check
	 *
	 * @return bool True if the order is either part of a split, false otherwise
	 */
	public static function is_split( WC_Order $order ) {
		return static::is_split_parent( $order ) || static::is_split_child( $order );
	}

	/**
	 * Checks whether the given order is the original order in at least one split operation
	 *
	 * Note that an order may be both a parent of a split and a child of a split.
	 *
	 * @param WC_Order $order The order to check
	 *
	 * @return bool True if the order has been split, false otherwise
	 */
	public static function is_split_parent( WC_Order $order ) {
		return $order->meta_exists( '_vibe_split_orders_split_count' );
	}

	/**
	 * Checks whether the given order was created as part of a split operation
	 *
	 * Note that an order may be both a parent of a split and a child of a split.
	 *
	 * @param WC_Order $order The order to check
	 *
	 * @return bool True if the order was created by a split, false otherwise
	 */
	public static function is_split_child( WC_Order $order ) {
		return $order->meta_exists( '_vibe_split_orders_split_from' );
	}
}
