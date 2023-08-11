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

$custom_attributes = ywpar_get_custom_attributes_of_custom_field( $field );
$value             = maybe_unserialize( $field['value'] );
$selected          = $value['time'];
?>
	<div id="<?php echo esc_attr( $field['id'] ); ?>-container" class="yit_options rm_option rm_input rm_text expire-options" <?php echo wp_kses_post( $custom_attributes ); ?>>
		<div class="option">
			<input name="<?php echo esc_attr( $field['id'] ) . '[number]'; ?>" id="<?php echo esc_attr( $field['id'] ) . '[number]'; ?>" type="number" value="<?php echo esc_attr( $value['number'] ); ?>" step="1" />
			<select id="<?php echo esc_attr( $field['id'] ) . '[time]'; ?>" name="<?php echo esc_attr( $field['id'] ) . '[time]'; ?>">
				<option value="days" <?php selected( $selected, 'days' ); ?>><?php esc_html_e( 'Days', 'yith-woocommerce-points-and-rewards' ); ?></option>
				<option value="months" <?php selected( $selected, 'months' ); ?> ><?php esc_html_e( 'Months', 'yith-woocommerce-points-and-rewards' ); ?></option>
			</select>
		</div>

		<div class="clear"></div>
	</div>
