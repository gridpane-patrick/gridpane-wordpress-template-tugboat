<?php

namespace Vibe\Split_Orders;

use Exception;
use WC_Data_Exception;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * AJAX request handlers
 *
 * @since 1.0.0
 */
class AJAX {

	/**
	 * Creates an instance and sets up the AJAX actions
	 */
	public function __construct() {
		$action = Split_Orders::hook_prefix( 'popup_line_items' );
		add_action( "wp_ajax_{$action}", array( __CLASS__, 'get_popup' ) );

		$action = Split_Orders::hook_prefix( 'split_order' );
		add_action( "wp_ajax_{$action}", array( __CLASS__, 'split_order' ) );
	}

	/**
	 * Handles an AJAX request to fetch line item HTML in the splitting modal
	 *
	 * Sends a JSON response containing a success flag and the HTML to be used for the modal
	 */
	public static function get_popup() {
		$response = array(
			'success' => false
		);

		$nonce = isset( $_REQUEST['nonce'] ) ? wc_clean( $_REQUEST['nonce'] ) : false;

		if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, Split_Orders::hook_prefix( 'popup-nonce' ) ) ) {
			$order_id = isset( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : false;

			if ( Orders::can_split( $order_id ) ) {
				$response['html']    = Admin::get_splitting_popup( $order_id );
				$response['success'] = ! empty( $response['html'] );
			}
		}

		wp_send_json( $response );
	}

	/**
	 * Handles an AJAX request to split an order
	 *
	 * Sends a JSON response containing a success flag
	 */
	public static function split_order() {
		$response = array(
			'success' => false,
		);

		$nonce = isset( $_REQUEST['nonce'] ) ? wc_clean( $_REQUEST['nonce'] ) : false;

		if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, Split_Orders::hook_prefix( 'splitting-nonce' ) ) ) {
			$order_id = isset( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : false;

			if ( Orders::can_split( $order_id ) ) {
				try {
					$items       = isset( $_REQUEST['items'] ) ? wc_clean( $_REQUEST['items'] ) : array();
					$meta_fields = Settings::meta_fields();

					$result = Orders::split( $order_id, $items, $meta_fields );
					$response['success'] = ! empty( $result );
				} catch ( WC_Data_Exception $e ) {
					$response['success'] = false;
					$response['message'] = __( 'Error occurred creating order', 'split-orders' );
				} catch ( Exception $e ) {
					$response['success'] = false;
					$response['message'] = __( 'Error occurred creating order', 'split-orders' );
				}
			}
		}

		wp_send_json( $response );
	}
}
