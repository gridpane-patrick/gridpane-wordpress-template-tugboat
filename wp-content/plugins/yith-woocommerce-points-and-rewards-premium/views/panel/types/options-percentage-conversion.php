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

$class = isset( $option['class'] ) ? $option['class'] : '';

$custom_attributes = ywpar_get_custom_attributes_of_custom_field( $field );

// filter to multi currencies integration.
$currencies = apply_filters( 'ywpar_get_active_currency_list', (array) get_woocommerce_currency() );
$value      = maybe_unserialize( $field['value'] );

// DO_ACTION : ywpar_before_currency_loop : action triggered before the currency loop inside the option useful to multi-currency plugins.
do_action( 'ywpar_before_currency_loop' );
$points_type = apply_filters( 'ywpar_enable_text_type_on_points_field', false ) ? 'text' : 'number';
?>
<div <?php echo wp_kses_post( $custom_attributes ); ?>>
<?php
foreach ( $currencies as $current_currency ) :

	$curr_name = $field['name'] . '[' . $current_currency . ']';
	$curr_id   = $field['id'] . '[' . $current_currency . ']';
	$points    = ( isset( $value[ $current_currency ]['points'] ) ) ? $value[ $current_currency ]['points'] : ( isset( $value['points'] ) ? $value['points'] : '' );
	$discount  = ( isset( $value[ $current_currency ]['discount'] ) ) ? $value[ $current_currency ]['discount'] : ( isset( $value['discount'] ) ? $value['discount'] : '' );

	?>
	<div id="<?php echo esc_attr( $field['id'] ); ?>-container"
		class="yit_options rm_option rm_input rm_text conversion-options <?php echo esc_attr( $class ); ?>">
		<div class="option">
			<input type="<?php echo esc_attr( $points_type ); ?>" name="<?php echo esc_attr( $curr_name ); ?>[points]" step="1" min="0"
				id="<?php echo esc_attr( $curr_id ); ?>-points"
				value="<?php echo esc_attr( $points ); ?>"/>
			<span><?php esc_html_e( 'Points', 'yith-woocommerce-points-and-rewards' ); ?> =</span>
			<input type="number"
				name="<?php echo esc_attr( $curr_name ); ?>[discount]" step="1" min="0" id="<?php echo esc_attr( $curr_id ); ?>-discount"
				value="<?php echo esc_attr( $discount ); ?>"/>
			<span>% <?php echo esc_html__( 'discount', 'yith-woocommerce-points-and-rewards' ); ?></span>
		</div>
		<div class="clear"></div>
	</div>

<?php endforeach; ?>
</div>
