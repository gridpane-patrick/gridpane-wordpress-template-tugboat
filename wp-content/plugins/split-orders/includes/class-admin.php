<?php

namespace Vibe\Split_Orders;

use WC_Order;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Sets up Admin modifications
 *
 * @since 1.0.0
 */
class Admin {

	/**
	 * Creates an instance and sets up the hooks to integrate with the admin
	 */
	public function __construct() {
		add_action( 'woocommerce_order_item_add_action_buttons', array( __CLASS__, 'output_split_button' ) );
		add_action( 'in_admin_footer', array( __CLASS__, 'output_modal' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Outputs an action button to split an order if not creating a new order and if the given order can be split
	 *
	 * @param WC_Order $order The order that would be split
	 */
	public static function output_split_button( $order ) {
		if ( static::is_splittable_screen() && Orders::can_split( $order->get_id() ) ) {
			printf( '<button type="button" class="button split-order">%s</button>', esc_html__( 'Split order', 'split-orders' ) );
		}
	}

	/**
	 * Checks whether the current screen is one to display split functionality on
	 *
	 * By default splittable screens would be any for the shop_order post type, except adding a new post
	 *
	 * @return bool True if the current screen is one that should include a split button, false otherwise
	 */
	public static function is_splittable_screen() {
		$screen        = get_current_screen();
		$screen_base   = isset( $screen->base ) ? $screen->base : '';
		$screen_id     = isset( $screen->id ) ? $screen->id : '';
		$screen_action = isset( $screen->action ) ? $screen->action : '';

		$is_splittable = 'post' === $screen_base && 'shop_order' === $screen_id && 'add' !== $screen_action;

		return apply_filters( Split_Orders::hook_prefix( 'is_splittable_screen' ), $is_splittable, $screen );
	}

	/**
	 * Outputs the HTML for a modal to be used for splitting an order
	 */
	public static function output_modal() {
		if ( static::is_splittable_screen() ) {
			?>
			<script type="text/template" id="tmpl-wc-modal-split-order">
				<div class="wc-backbone-modal">
					<div class="wc-backbone-modal-content">
						<section class="wc-backbone-modal-main" role="main">
							<header class="wc-backbone-modal-header">
								<h1><?php esc_html_e( 'Split order', 'split-orders' ); ?></h1>
								<button class="modal-close modal-close-link dashicons dashicons-no-alt">
									<span class="screen-reader-text">Close modal panel</span>
								</button>
							</header>
							<article id="modal-split-order-line-items">
								<?php // Will be populated by AJAX when opened ?>
							</article>
							<footer>
								<div class="inner">
									<button id="btn-ok" class="button button-primary button-large"><?php esc_html_e( 'Complete split', 'split-orders' ); ?></button>
								</div>
							</footer>
						</section>
					</div>
				</div>
				<div class="wc-backbone-modal-backdrop modal-close"></div>
			</script>
			<?php
		}
	}

	/**
	 * Returns the HTML to populate the order splitting modal, with line items from the given order
	 *
	 * @param int $order_id The ID of the order to generate the output for
	 *
	 * @return string An HTML string with controls for splitting the given order
	 */
	public static function get_splitting_popup( $order_id ) {
		$order = wc_get_order( $order_id );
		$items = $order ? $order->get_items() : false;

		if ( ! $items ) {
			return '';
		}

		/**
		 * Filters the notices to display prior to splitting the given order
		 *
		 * The notices array should be arranged with the key being one of the following notice types and the values
		 * being either a string to be used as a message, or an array of strings to use as messages.
		 *
		 * - info
		 * - success
		 * - warning
		 * - error
		 */
		$notices = apply_filters( Split_Orders::hook_prefix( 'pre_split_notices' ), array(), $order );

		ob_start();

		include vibe_split_orders()->path( 'includes/partials/popup.php' );

		return ob_get_clean();
	}

	/**
	 * Enqueues scripts and styles on the order admin pages
	 */
	public static function enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'post' != $screen->base || 'shop_order' != $screen->id ) {
			return;
		}

		$handle = Split_Orders::hook_prefix( 'js' );

		wp_register_script(
			$handle,
			vibe_split_orders()->uri( 'assets/js/vibe-split-orders.min.js' ),
			array( 'jquery' ),
			vibe_split_orders()->get_version(),
			true
		);
		wp_localize_script( $handle, 'vibe_split_orders_data', static::script_data() );

		wp_enqueue_script( $handle );

		$handle = Split_Orders::hook_prefix( 'css' );
		wp_enqueue_style(
			$handle,
			vibe_split_orders()->uri( 'assets/css/vibe-split-orders.min.css' ),
			array(),
			vibe_split_orders()->get_version(),
			'all'
		);
	}

	/**
	 * Sets up data to be passed to front end via script localisation
	 *
	 * @return array An array of data items
	 */
	public static function script_data() {
		$script_data['ajaxurl']         = admin_url( 'admin-ajax.php' );
		$script_data['popup_nonce']     = wp_create_nonce( Split_Orders::hook_prefix( 'popup-nonce' ) );
		$script_data['splitting_nonce'] = wp_create_nonce( Split_Orders::hook_prefix( 'splitting-nonce' ) );

		return apply_filters( Split_Orders::hook_prefix( 'script_data' ), $script_data );
	}

	/**
	 * Outputs the given array of notices
	 *
	 * The notices array should be arranged with the key being one of the following notice types and the values
	 * being either a string to be used as a message, or an array of strings to use as messages.
	 *
	 * - info
	 * - success
	 * - warning
	 * - error
	 *
	 * @param array $notices
	 */
	public static function output_notices( array $notices ) {
		foreach ( $notices as $notice_type => $type_notices ) {
			if ( ! static::is_valid_notice_type( $notice_type ) ) {
				continue;
			}

			foreach ( $type_notices as $message ) {
				?>

				<div class="notice notice-<?php echo esc_attr( $notice_type ); ?>">
					<?php echo wp_kses_post( $message ); ?>
				</div>

				<?php
			}
		}
	}

	public static function add_notice( array $notices, $message, $type = 'info' ) {
		if ( ! static::is_valid_notice_type( $type ) ) {
			$type = 'info';
		}

		if ( ! isset( $notices[ $type ] ) ) {
			$notices[ $type ] = array();
		} elseif ( ! is_array( $notices[ $type ] ) ) {
			$notices[ $type ] = array( $notices[ $type ] );
		}

		$notices[ $type ][] = strval( $message );

		return $notices;
	}

	protected static function is_valid_notice_type( $type ) {
		return in_array( $type, array( 'info', 'success', 'warning', 'error' ) );
	}
}
