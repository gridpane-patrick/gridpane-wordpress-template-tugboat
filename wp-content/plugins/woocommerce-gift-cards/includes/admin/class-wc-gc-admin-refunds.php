<?php
/**
 * Admin refunds controller class.
 *
 * @package  WooCommerce Gift Cards
 * @since    1.10.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_GC_Admin_Refunds Class.
 *
 * @version 1.10.0
 */
class WC_GC_Admin_Refunds {

	/**
	 * Filter UI runtime flag.
	 */
	private static $should_force_render_refunds_form;

	/**
	 * Setup refunds in admin.
	 */
	public static function init() {

		// Display.
		add_action( 'woocommerce_admin_order_totals_after_total', array( __CLASS__, 'add_admin_refund_totals' ), 9 );
		add_action( 'woocommerce_after_order_refund_item_name', array( __CLASS__, 'add_admin_refund_line_description' ) );

		// Fix admin-order refunds UI.
		add_action( 'woocommerce_admin_order_totals_after_tax', array( __CLASS__, 'maybe_force_render_refunds_interface' ) );
	}

	/**
	 * Add gift card specific data when creating a new refund object.
	 * TODO: In case of failure, revert refunds.
	 *
	 * @param  WC_Order_Refund  $refund
	 * @param  array            $args
	 * @return void
	 */
	public static function handle_create_refund( $refund, $args ) {

		// Bail early.
		if ( ! isset( $args[ 'amount_to_gift_cards' ] ) || 0 > $args[ 'amount_to_gift_cards' ] ) {
			return;
		}

		$refund_amount = (float) $args[ 'amount_to_gift_cards' ];
		if ( 0 > $refund_amount ) {
			throw new Exception( __( 'Invalid refund amount.', 'woocommerce-gift-cards' ) );
		}

		$order        = wc_get_order( $refund->get_parent_id() );
		$order_id     = $order->get_id();

		$left         = $refund_amount;
		$user         = wp_get_current_user();
		$user_id      = is_a( $user, 'WP_User' ) ? $user->ID : 1;
		$activity_ids = array();

		// Find gift cards to refund.
		foreach ( $order->get_items( 'gift_card' ) as $item ) {

			$giftcard = new WC_GC_Gift_Card( $item->get_giftcard_id() );
			if ( ! $giftcard->get_id() ) {
				continue;
			}

			$captured_amount = $item->get_captured_amount();
			if ( 0 == $captured_amount ) {
				continue;
			}

			// Hint: Use min() here for safety and rounding errors.
			$balance_to_use = min( $captured_amount, $left );
			$left          -= $balance_to_use;

			$giftcard->credit( $balance_to_use, $order, false );

			// Log action.
			$activity_ids[] = WC_GC()->db->activity->add( array(
				'type'       => 'manually_refunded',
				'gc_id'      => $giftcard->get_id(),
				'object_id'  => $order_id,
				'user_id'    => $user_id,
				'amount'     => $balance_to_use
			) );

			// Clear caches.
			$cache_key = WC_Cache_Helper::get_cache_prefix( 'orders' ) . 'gc_total_refunded_' . $giftcard->get_id() . '_' . $order_id;
			wp_cache_set( $cache_key, false, 'order-items' );

			if ( 0 == $left ) {
				break;
			}
		}

		if ( empty( $activity_ids ) ) {
			throw new Exception( __( 'Cannot process refund. Please try again.', 'woocommerce-gift-cards' ) );
		} else {
			$refund->add_meta_data( '_wc_gc_refund_activities', $activity_ids );

			// Handle pending balances.
			WC_GC()->order->handle_pending_balance_tracking( $order_id );
		}
	}

	/**
	 * When refunding an order, create a refund line item if the partial refunds do not match gift card order total.
	 *
	 * @param  int       $order_id
	 * @param  WC_Order  $order
	 * @return void
	 */
	public static function order_fully_refunded( $order_id, $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		// Add a new refund.
		$total_captured = WC_GC()->order->get_order_total_captured( $order, 'db' );
		if ( 0 < $total_captured ) {

			wc_switch_to_site_locale();
			wc_gc_create_refund( array(
				'amount'   => $total_captured,
		        'order_id' => $order_id,
			) );
			wc_restore_locale();
		}
	}

	/**
	 * Re-hook emails actions for the WC_Email_Customer_Refunded_Order email.
	 *
	 * @param  int    $refund_id
	 * @param  array  $args
	 * @return void
	 */
	public static function enable_refund_emails( $refund_id, $args ) {
		$order = wc_get_order( absint( $args[ 'order_id' ] ) );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		if ( ! did_action( 'woocommerce_order_status_refunded' ) ) {
			return;
		}

		$max_conventional_refund = wc_format_decimal( $order->get_total() - $order->get_total_refunded() );
		if ( ! $max_conventional_refund ) {
			return;
		}

		$emails = WC()->mailer->get_emails();
		if ( isset( $emails[ 'WC_Email_Customer_Refunded_Order' ] ) ) {
			add_action( 'woocommerce_order_fully_refunded_notification', array( $emails[ 'WC_Email_Customer_Refunded_Order' ], 'trigger_full' ), 10, 2 );

			add_action( 'woocommerce_order_partially_refunded_notification', array( $emails[ 'WC_Email_Customer_Refunded_Order' ], 'trigger_partial' ), 10, 2 );
		}
	}

	/**
	 * Un-hook emails actions for the WC_Email_Customer_Refunded_Order email.
	 *
	 * @param  int    $refund_id
	 * @param  array  $args
	 * @return void
	 */
	public static function disable_refund_emails( $args ) {
		$order = wc_get_order( absint( $args[ 'order_id' ] ) );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		if ( ! did_action( 'woocommerce_order_status_refunded' ) ) {
			return;
		}

		$max_conventional_refund = wc_format_decimal( $order->get_total() - $order->get_total_refunded() );
		if ( ! $max_conventional_refund ) {
			return;
		}

		$emails = WC()->mailer->get_emails();
		if ( isset( $emails[ 'WC_Email_Customer_Refunded_Order' ] ) ) {
			remove_action( 'woocommerce_order_fully_refunded_notification', array( $emails[ 'WC_Email_Customer_Refunded_Order' ], 'trigger_full' ), 10, 2 );

			remove_action( 'woocommerce_order_partially_refunded_notification', array( $emails[ 'WC_Email_Customer_Refunded_Order' ], 'trigger_partial' ), 10, 2 );
		}
	}

	/**
	 * Replicate a partial refund action when in fully refunded context and the order has still balance captured.
	 *
	 * @param  int  $order_id
	 * @param  int  $refund_id
	 * @return void
	 */
	public static function maybe_disable_order_fully_refunded_hook( $order_id, $refund_id ) {

		$order = wc_get_order( $order_id );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		// Has gift cards?
		$giftcards = $order->get_items( 'gift_card' );
		if ( empty( $giftcards ) ) {
			return;
		}

		// Check if there is any balance captured.
		if ( 0 < WC_GC()->order->get_order_total_captured( $order ) ) {
			remove_all_actions( 'woocommerce_order_fully_refunded' );
			do_action( 'woocommerce_order_partially_refunded', $order_id, $refund_id );
		}
	}

	/**
	 * Take into account gift cards, before auto transition the order to refunded.
	 *
	 * @param  string  $status
	 * @param  int     $order_id
	 * @return void
	 */
	public static function maybe_prevent_order_fully_refunded_status( $status, $order_id ) {
		$total = WC_GC()->order->get_order_total_captured( $order_id );
		return $total > 0 ? '' : $status;
	}

	/**
	 * Maybe setup filters for refunds UI, if the order is fully refunded.
	 *
	 * @param  int  $order_id
	 * @return void
	 */
	public static function maybe_force_render_refunds_interface( $order_id ) {
		if ( ! is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		if ( $order->is_editable() ) {
			return;
		}

		if ( 0 >= WC_GC()->order->get_order_total_gift_cards( $order ) ) {
			return;
		}

		// Hint: Is (a) fully refunded and (b) has balance captured? Then, filter the refunds UI.
		if ( ( 0 >= $order->get_total() - $order->get_total_refunded() || 0 >= absint( $order->get_item_count() - $order->get_item_count_refunded() ) ) && 0 < WC_GC()->order->get_order_total_captured( $order ) ) {

			// Render the "Refund" button.
			add_action( 'woocommerce_admin_order_totals_after_total', array( __CLASS__, 'enable_display_filters' ), 99999 );
			add_action( 'woocommerce_order_item_add_action_buttons', array( __CLASS__, 'disable_display_filters'), -99999 );

			// Render the "Refund Summary" details.
			add_action( 'woocommerce_order_item_add_line_buttons', array( __CLASS__, 'enable_display_filters' ), 99999 );
			add_action( 'pre_option_woocommerce_manage_stock', array( __CLASS__, 'disable_display_filters'), -99999 );
		}
	}

	/**
	 * Turn on filters for refunds UI.
	 */
	public static function enable_display_filters() {
		self::$should_force_render_refunds_form = true;
		add_action( 'woocommerce_order_get_total', array( __CLASS__, 'modify_order_total_for_rendering' ), 10, 2 );
	}

	/**
	 * Turn off filters for refunds UI.
	 */
	public static function disable_display_filters() {
		if ( true === self::$should_force_render_refunds_form ) {
			self::$should_force_render_refunds_form = null;
			remove_action( 'woocommerce_order_get_total', array( __CLASS__, 'modify_order_total_for_rendering' ), 10, 2 );
		}

		// This return is set for the `get_option()` to work properly.
		return false;
	}

	/**
	 * Filter the order total to make sure refunds UI gets rendered.
	 *
	 * @param  float     $value
	 * @param  WC_Order  $order
	 * @return float
	 */
	public static function modify_order_total_for_rendering( $value, $order ) {
		if ( true !== self::$should_force_render_refunds_form ) {
			return $value;
		}

		// Hint: Adding one more is enough for `get_total()` to create a diff from `get_total_refunded()` in a fully refunded order.
		return $value + 1;
	}

	/**
	 * Adds Gift Cards refund data in admin order totals.
	 *
	 * @param  int  $order_id
	 * @return void
	 */
	public static function add_admin_refund_totals( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$total_gift_cards = WC_GC()->order->get_order_total_gift_cards( $order );
		$total_captured   = WC_GC()->order->get_order_total_captured( $order );

		if ( $total_gift_cards > $total_captured ) {
			?>
			<tr>
				<td class="label refunded-total gift-cards-refunded-total"><?php esc_html_e( 'Refunded', 'woocommerce' ); ?> <small><?php esc_html_e( '(to gift cards)', 'woocommerce-gift-cards' ); ?></small>:</td>
				<td width="1%"></td>
				<td class="total refunded-total">-<?php echo wc_price( $total_gift_cards - $total_captured, array( 'currency' => $order->get_currency() ) ); ?></td>
			</tr>
			<tr>
				<td class="label label-highlight"><?php esc_html_e( 'Net payment', 'woocommerce-gift-cards' ); ?> <small><?php esc_html_e( '(via gift cards)', 'woocommerce-gift-cards' ); ?></small>:</td>
				<td width="1%"></td>
				<td class="total"><?php echo wc_price( $total_captured, array( 'currency' => $order->get_currency() ) ); ?></td>
			</tr>
			<?php
		}

		// Hint: jQuery will move these elements below into the summary totals table.
		?>
		<tr class="wc_gc_move_row_to_refund_summary" style="display: none;">
			<td class="label"><?php esc_html_e( 'Total available to refund to gift cards', 'woocommerce-gift-cards' ); ?>:</td>
			<td class="total"><?php echo wc_price( $total_captured, array( 'currency' => $order->get_currency() ) ); ?></td>
		</tr>
		<tr class="wc_gc_move_row_to_refund_summary" style="display: none;">
			<td class="label"><?php esc_html_e( 'Amount already refunded to gift cards', 'woocommerce-gift-cards' ); ?>:</td>
			<td class="total">
				-<?php echo wc_price( $total_gift_cards - $total_captured, array( 'currency' => $order->get_currency() ) ); ?>
				<input type="hidden" id="gift_card_refunded_amount" name="gift_card_refunded_amount" value="<?php echo esc_attr( number_format( $total_gift_cards - $total_captured, wc_get_price_decimals() ) ); ?>" />
			</td>
		</tr>
		<?php
	}

	/**
	 * Adds Gift Cards refund description in admin order totals.
	 *
	 * @param  WC_Order_Refund  $refund
	 * @return void
	 */
	public static function add_admin_refund_line_description( $refund ) {

		if ( ! is_a( $refund, 'WC_Order_Refund' ) ) {
			return;
		}

		$activities = $refund->get_meta( '_wc_gc_refund_activities', true );
		if ( empty( $activities ) ) {
			return;
		}

		$text  = _n( 'Refunded to gift card code:', 'Refunded to gift card codes:', count( $activities ), 'woocommerce-gift-cards' );
		$codes = array();

		foreach ( $activities as $id ) {
			$activity = WC_GC()->db->activity->get( $id );
			if ( ! $activity ) {
				continue;
			}
			$codes[]  = $activity->get_gc_code();
		}
		$text .= ' ' . implode( ', ', $codes );
		?>
		<p class="description">
			<?php echo esc_html( $text ); ?>
		</p>
		<?php
	}

	/**
	 * Delete a refund.
	 * @override
	 *
	 * @return void
	 */
	public static function delete_refund() {
		check_ajax_referer( 'order-item', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) || ! isset( $_POST[ 'refund_id' ] ) ) {
			wp_die( -1 );
		}

		$refund_ids   = array_map( 'absint', is_array( $_POST[ 'refund_id' ] ) ? wp_unslash( $_POST[ 'refund_id' ] ) : array( wp_unslash( $_POST[ 'refund_id' ] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$errors       = array();

		foreach ( $refund_ids as $refund_id ) {
			$result = self::handle_delete_refund( $refund_id );
			if ( is_string( $result ) ) {
				$errors[ $refund_id ] = $result;
			} elseif ( false === $result ) {
				$errors[ $refund_id ] = __( 'The refund could not be processed.', 'woocommerce-gift-cards' );
			}
		}

		if ( ! empty( $errors ) ) {
			wp_send_json_error( array( 'errors' => $errors ) );
		}

		// Move on...
		WC_AJAX::delete_refund();
	}

	/**
	 * Handle deleting a refund.
	 *
	 * @param  int  $refund_id
	 * @return bool|string
	 */
	public static function handle_delete_refund( $refund_id ) {

		if ( empty( $refund_id ) ) {
			return false;
		}

		$refund = wc_get_order( $refund_id );
		if ( ! is_a( $refund, 'WC_Order_Refund' ) || 'shop_order_refund' !== $refund->get_type() ) {
			return false;
		}

		$activity_ids = array();
		$error        = '';
		$activities   = $refund->get_meta( '_wc_gc_refund_activities', true );
		if ( empty( $activities ) ) {
			// No need to work, move on.
			return true;
		}

		// Do a sanity validation. Does all gift cards has enough balance to proceed?
		$invalid_codes_for_debit = array();
		foreach ( $activities as $activity_id ) {
			$activity = WC_GC()->db->activity->get( $activity_id );
			if ( ! $activity || ! $activity->is_type( 'manually_refunded' ) ) {
				continue;
			}

			$giftcard = wc_gc_get_gift_card( $activity->get_gc_id() );
			if ( ! $giftcard ) {
				continue;
			}

			if ( $activity->get_amount() > $giftcard->get_balance() ) {
				$invalid_codes_for_debit[] = $giftcard->get_code();
			} else {
				$activity_ids[] = $activity_id;
			}
		}

		// Bail early if there is at least one invalid code.
		if ( ! empty( $invalid_codes_for_debit ) ) {

			return _n( sprintf( 'Cannot revert funds for Refund #%d: Not enough balance for gift card code `%s`.', $refund_id, implode( ', ', $invalid_codes_for_debit ) ), sprintf( 'Cannot revert funds for Refund #%d: Not enough balance for gift card codes `%s`.', $refund_id, implode( ', ', $invalid_codes_for_debit ) ), count( $invalid_codes_for_debit ), 'woocommerce-gift-cards' );
		}

		// Do the actual refund reverse to gift cards.
		$unique_order_ids = array();
		if ( ! empty( $activity_ids ) ) {
			foreach ( $activity_ids as $activity_id ) {
				$activity = WC_GC()->db->activity->get( $activity_id );
				if ( ! $activity || ! $activity->is_type( 'manually_refunded' ) ) {
					continue;
				}

				$giftcard = wc_gc_get_gift_card( $activity->get_gc_id() );
				if ( ! $giftcard ) {
					continue;
				}

				// Decorate object for payments.
				$giftcard = new WC_GC_Gift_Card( $giftcard );
				if ( $giftcard->debit( $activity->get_amount(), false, false ) ) {

					// Log the action.
					$user          = wp_get_current_user();
					$activity_args = array(
						'gc_id'      => $giftcard->get_id(),
						'user_id'    => $user->ID,
						'user_email' => $user->user_email,
						'object_id'  => $activity->get_object_id(),
						'type'       => 'refund_reversed',
						'amount'     => $activity->get_amount()
					);

					$unique_order_ids[] = $activity->get_object_id();

					WC_GC()->db->activity->add( $activity_args );

					// Clear caches for refunded.
					$cache_key = WC_Cache_Helper::get_cache_prefix( 'orders' ) . 'gc_total_refunded_' . $giftcard->get_id() . '_' . $activity->get_object_id();
					wp_cache_set( $cache_key, false, 'order-items' );
				}
			}

			// Handle pending balances.
			$unique_order_ids = array_unique( $unique_order_ids );
			foreach ( $unique_order_ids as $order_id ) {
				WC_GC()->order->handle_pending_balance_tracking( $order_id );
			}
		}

		return true;
	}

	/**
	 * Handle a refund via the edit order screen.
	 *
	 * @return void
	 */
	public static function refund_line_items() {
		ob_start();

		check_ajax_referer( 'order-item', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( -1 );
		}

		$order_id                  = isset( $_POST[ 'order_id' ] ) ? absint( $_POST[ 'order_id' ] ) : 0;
		$refund_amount             = isset( $_POST[ 'refund_amount' ] ) ? wc_format_decimal( sanitize_text_field( wp_unslash( $_POST[ 'refund_amount' ] ) ), wc_get_price_decimals() ) : 0;
		$refunded_amount           = isset( $_POST[ 'refunded_amount' ] ) ? wc_format_decimal( sanitize_text_field( wp_unslash( $_POST[ 'refunded_amount' ] ) ), wc_get_price_decimals() ) : 0;
		$gift_card_refunded_amount = isset( $_POST[ 'gift_card_refunded_amount' ] ) ? wc_format_decimal( sanitize_text_field( wp_unslash( $_POST[ 'gift_card_refunded_amount' ] ) ), wc_get_price_decimals() ) : 0;
		$refund_reason             = isset( $_POST[ 'refund_reason' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'refund_reason' ] ) ) : '';
		$line_item_qtys            = isset( $_POST[ 'line_item_qtys' ] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST[ 'line_item_qtys' ] ) ), true ) : array();
		$line_item_totals          = isset( $_POST[ 'line_item_totals' ] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST[ 'line_item_totals' ] ) ), true ) : array();
		$line_item_tax_totals      = isset( $_POST[ 'line_item_tax_totals' ] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST[ 'line_item_tax_totals' ] ) ), true ) : array();
		$restock_refunded_items    = isset( $_POST[ 'restock_refunded_items' ] ) && 'true' === $_POST[ 'restock_refunded_items' ];
		$refund                    = false;
		$response                  = array();

		try {
			$order = wc_get_order( $order_id );
			if ( ! is_a( $order, 'WC_Order' ) ) {
				throw new Exception( __( 'Error processing refund.', 'woocommerce-gift-cards' ) );
			}

			$giftcard_items = $order->get_items( 'gift_card' );
			if ( empty( $giftcard_items ) ) {
				throw new Exception( __( 'Error processing refund. No gift cards found.', 'woocommerce-gift-cards' ) );
			}

			$max_refund = wc_format_decimal( WC_GC()->order->get_order_total_captured( $order, 'db' ), wc_get_price_decimals() );

			if ( ( ! $refund_amount && ( wc_format_decimal( 0, wc_get_price_decimals() ) !== $refund_amount ) ) || $max_refund < $refund_amount || 0 > $refund_amount ) {
				throw new Exception( __( 'Invalid refund amount', 'woocommerce' ) );
			}

			if ( wc_format_decimal( $order->get_total_refunded(), wc_get_price_decimals() ) !== $refunded_amount ) {
				throw new Exception( __( 'Error processing refund. Please try again.', 'woocommerce' ) );
			}

			if ( wc_format_decimal( WC_GC()->order->get_order_total_gift_cards( $order ) - WC_GC()->order->get_order_total_captured( $order ), wc_get_price_decimals() ) !== $gift_card_refunded_amount ) {
				throw new Exception( __( 'Error processing refund. Please try again.', 'woocommerce' ) );
			}

			// Prepare line items which we are refunding.
			$line_items = array();
			$item_ids   = array_unique( array_merge( array_keys( $line_item_qtys ), array_keys( $line_item_totals ) ) );

			foreach ( $item_ids as $item_id ) {
				$line_items[ $item_id ] = array(
					'qty'          => 0,
					'refund_total' => 0,
					'refund_tax'   => array(),
				);
			}
			foreach ( $line_item_qtys as $item_id => $qty ) {
				$line_items[ $item_id ][ 'qty' ] = max( $qty, 0 );
			}
			foreach ( $line_item_totals as $item_id => $total ) {
				$line_items[ $item_id ][ 'refund_total' ] = wc_format_decimal( $total );
			}
			foreach ( $line_item_tax_totals as $item_id => $tax_totals ) {
				$line_items[ $item_id ][ 'refund_tax' ] = array_filter( array_map( 'wc_format_decimal', $tax_totals ) );
			}

			wc_switch_to_site_locale();
			$refund = wc_gc_create_refund( array(
				'amount'        => $refund_amount,
				'reason'        => $refund_reason,
				'order_id'      => $order_id,
				'line_items'    => $line_items,
				'restock_items' => $restock_refunded_items,
			) );
			wc_restore_locale();

			if ( is_wp_error( $refund ) ) {
				throw new Exception( $refund->get_error_message() );
			}

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}

		wp_send_json_success( $response );
	}
}

WC_GC_Admin_Refunds::init();
