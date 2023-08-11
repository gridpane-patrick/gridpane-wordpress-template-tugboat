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
$currency                   = get_woocommerce_currency();
$value                      = $field['value'];
?>
<div <?php echo wp_kses_post( $field['custom_attributes'] ); ?>>
<input type="text" name="<?php echo esc_html( $field['name'] ); ?>" id="<?php echo esc_html( $field['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" class="<?php echo ( ! empty( $class ) ) ? esc_html( $class ) : ''; ?>">

	<span class="<?php echo esc_html( $field['name'] ); ?>_currency">
		<?php echo get_woocommerce_currency_symbol( $currency ) . ' (' . $currency . ')'; ?>
	</span>

</div>
