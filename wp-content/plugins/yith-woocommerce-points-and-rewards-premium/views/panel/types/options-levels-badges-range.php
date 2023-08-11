<?php
/**
 * Conversion field for specific roles
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var $field
 */

defined( 'ABSPATH' ) || exit;

$field['custom_attributes'] = ywpar_get_custom_attributes_of_custom_field( $field );



$value = maybe_unserialize( $field['value'] );

?>
<div class="option" <?php echo wp_kses_post( $field['custom_attributes'] ); ?>>
	<label for="<?php echo esc_html( $field['id'] . '-from' ); ?>"><?php esc_html_e( 'From', 'yith-woocommerce-points-and-rewards' ); ?></label>
	<input type="number" name="<?php echo esc_attr( $field['name'] ); ?>[from]" id="<?php echo esc_attr( $field['id'] ); ?>-from" min="0" value="<?php echo esc_attr( isset( $value['from'] ) ? $value['from'] : '' ); ?>"/>
	<label for="<?php echo esc_html( $field['id'] . '-to' ); ?>"><?php esc_html_e( 'to', 'yith-woocommerce-points-and-rewards' ); ?></label>
	<input type="number" name="<?php echo esc_attr( $field['name'] ); ?>[to]" id="<?php echo esc_attr( $field['id'] ); ?>-to" min="0" value="<?php echo esc_attr( isset( $value['to'] ) ? $value['to'] : '' ); ?>"/>
</div>
