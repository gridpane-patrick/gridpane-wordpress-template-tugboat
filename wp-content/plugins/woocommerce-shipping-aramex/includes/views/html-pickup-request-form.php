<?php
/**
 * Pickup request form HTML.
 *
 * Included by WC_Shipping_Aramex::pickup_request_form().
 *
 * @package WC_Shipping_Aramex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$is_pre_wc_30 = version_compare( WC_VERSION, '3.0', '<' );

$order_id = $is_pre_wc_30 ? $order->id : $order->get_id();

$order_date = $is_pre_wc_30
	? date_i18n( 'Y-m-d', strtotime( $order->order_date ) )
	: ( $order->get_date_created() ? gmdate( 'Y-m-d', $order->get_date_created()->getOffsetTimestamp() ) : ''  );

$order_hour = $is_pre_wc_30
	? date_i18n( 'H', strtotime( $order->order_date ) )
	: ( $order->get_date_created() ? gmdate( 'H', $order->get_date_created()->getOffsetTimestamp() ) : ''  );

$order_minute = $is_pre_wc_30
	? date_i18n( 'i', strtotime( $order->order_date ) )
	: ( $order->get_date_created() ? gmdate( 'i', $order->get_date_created()->getOffsetTimestamp() ) : ''  );

$pickup_date = get_post_meta( $order_id, '_pickup_date', true );
$pickup_id   = get_post_meta( $order_id, '_pickup_id', true );
$pickup_guid = get_post_meta( $order_id, '_pickup_guid', true );
?>

<p class="form-field form-field-wide">
	<?php if ( ! empty( $pickup_id ) ) : ?>
		<label for="order_date"><?php _e( 'Pickup info:', 'woocommerce-shipping-aramex' ) ?></label>
		<?php
		/* translators: placeholder is pickup ID from Aramex. */
		echo sprintf( __( 'Pickup has been created with ID <strong>%s</strong>.', 'woocommerce-shipping-aramex' ), $pickup_id );
		?>

		<br>
		<?php if ( ! empty( $pickup_date ) ) : ?>
			<?php
			/* translators: placeholder is pickup date. */
			echo sprintf( __( 'Pickup date: <strong>%s</strong>', 'woocommerce-shipping-aramex' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $pickup_date ) ) );
			?>
		<?php endif; ?>

	<?php else : ?>
		<label for="order_date"><?php _e( 'Pickup date:', 'woocommerce-shipping-aramex' ) ?></label>
		<input
			type="text"
			class="pickup_date date-picker"
			name="pickup_date" id="pickup_date"
			maxlength="10"
			value="<?php echo esc_attr( $order_date ); ?>"
			pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />@
		<input
			type="text"
			class="hour pickup_date_hour"
			placeholder="<?php _e( 'h', 'woocommerce-shipping-aramex' ) ?>"
			name="pickup_date_hour"
			id="pickup_date_hour"
			maxlength="2"
			size="2"
			value="<?php echo esc_attr( $order_hour ); ?>"
			pattern="([0-3][0-9])" />:
		<input
			type="text"
			class="minute pickup_date_minute"
			placeholder="<?php _e( 'm', 'woocommerce-shipping-aramex' ) ?>"
			name="pickup_date_minute"
			id="pickup_date_minute"
			maxlength="2"
			size="2"
			value="<?php echo esc_attr( $order_minute ); ?>"
			pattern="[0-5][0-9]" />

		<?php if ( empty( $pickup_id ) ) : ?>
			<a class="button-secondary aramex-pickup" href="#" data-id="<?php echo esc_attr( $order_id ); ?>"><?php _e( 'Request Pickup', 'woocommerce-shipping-aramex' ); ?></a>
			<img src="<?php echo WC_Aramex()->plugin_url . '/images/ajax-loader.gif'; ?>" class="ajax-loader" style="display: none;"/>
		<?php endif; ?>
	<?php endif; ?>
</p>
<div class="pickup_errors" style="clear: both"></div>
