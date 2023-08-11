<?php
/**
 * Conversion field
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var $field
 */

defined( 'ABSPATH' ) || exit;


$name              = $field['name'];
$custom_attributes = ywpar_get_custom_attributes_of_custom_field( $field );

// filter to multi currencies integration.
$currencies = apply_filters( 'ywpar_get_active_currency_list', (array) get_woocommerce_currency() );

// DO_ACTION : ywpar_before_currency_loop : action triggered before the currency loop inside the option useful to multi-currency plugins.
do_action( 'ywpar_before_currency_loop' );
$value = maybe_unserialize( $field['value'] );
$points_type = apply_filters( 'ywpar_enable_text_type_on_points_field', false ) ? 'text' : 'number';
?>
<div <?php echo wp_kses_post( $custom_attributes ); ?>>
	<?php
	foreach ( $currencies as $current_currency ) :
		$curr_name   = $name . '[' . $current_currency . ']';
		$curr_id     = $field['id'] . '[' . $current_currency . ']';
		$points      = ( isset( $value[ $current_currency ]['points'] ) ) ? $value[ $current_currency ]['points'] : ( isset( $value['points'] ) ? $value['points'] : '' );
		$money       = ( isset( $value[ $current_currency ]['money'] ) ) ? $value[ $current_currency ]['money'] : ( isset( $value['money'] ) ? $value['money'] : '' );


		?>

		<div id="<?php echo esc_attr( $field['id'] ); ?>-container" class="yit_options rm_option rm_input rm_text conversion-options">
			<div class="option">
				<span><?php esc_html_e( 'For each', 'yith-woocommerce-points-and-rewards' ); ?></span>
				<input type="<?php echo esc_attr( apply_filters( 'ywpar_conversion_money_field_type', 'number' ) ); ?>" name="<?php echo esc_attr( $curr_name ); ?>[money]"  min="0" id="<?php echo esc_attr( $curr_id ); ?>-money" value="<?php echo esc_attr( $money ); ?>"/>
				<span><?php echo wp_kses_post( get_woocommerce_currency_symbol( $current_currency ) . ' (' . $current_currency . ')' ) . ' ' . esc_html__( 'assign', 'yith-woocommerce-points-and-rewards' ); ?></span>

				<input type="<?php echo esc_attr( $points_type ); ?>" name="<?php echo esc_attr( $curr_name ); ?>[points]" step="1" min="1" id="<?php echo esc_attr( $curr_id ); ?>-points" value="<?php echo esc_attr( $points ); ?>"/>
				<span><?php esc_html_e( 'Points', 'yith-woocommerce-points-and-rewards' ); ?></span>
			</div>

			<div class="clear"></div>
		</div>
	<?php endforeach; ?>
</div>
