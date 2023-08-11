<?php
/**
 * Product quantity inputs
 *
 * @package    quantity-input.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

if ( $max_value && $min_value === $max_value ) {
	?>
	<div class="quantity">
		<input type="number"
               id="<?php echo esc_attr( $input_id ); ?>"
               class="qty"
               name="<?php echo esc_attr( $input_name ); ?>"
               value="<?php echo esc_attr( $min_value ); ?>"
               step="<?php echo esc_attr( $step ); ?>"
               min="<?php echo esc_attr( $min_value ); ?>"
               max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
        />
	</div>
	<?php
} else {
	/* translators: %s: Quantity. */
	$label = ! empty( $args['product_name'] ) ? sprintf( esc_html__( '%s quantity', 'xstore-amp' ), wp_strip_all_tags( $args['product_name'] ) ) : esc_html__( 'Quantity', 'xstore-amp' );
	$amp_qty_id = 'qty_'.$input_id;
	$minus_conditions = "(".$amp_qty_id." - ".$step.")";
	$plus_conditions = "(".$amp_qty_id." + ".$step.")";
	if ( $max_value != '' && $max_value > 0) {
		$plus_conditions = "(".$amp_qty_id." >= ". $max_value .") ? ".$max_value." : (".$amp_qty_id." + ".$step.")";
	}
	if ( $min_value != '' ) {
		$minus_conditions = "(".$amp_qty_id." <= ". $min_value .") ? ".$min_value." : (".$amp_qty_id." - ".$step.")";
	}
	?>
        <amp-state id="<?php echo esc_attr($amp_qty_id); ?>">
            <script type="application/json">
                <?php echo ( $input_value ) ? esc_attr($input_value) : 0; ?>
            </script>
        </amp-state>
    <p class="quantity-label"><?php echo esc_html__('Quantity:', 'xstore-amp'); ?></p>
	<div class="quantity">
		<?php do_action( 'woocommerce_before_quantity_input_field' ); ?>
		<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $label ); ?></label>
		<span role="button" tabindex="-1" on="tap:AMP.setState({'<?php echo $amp_qty_id; ?>': <?php echo $minus_conditions; ?> })"><i class="et-icon et-minus"></i></span>
		<input
			type="number"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
			step="<?php echo esc_attr( $step ); ?>"
			min="<?php echo esc_attr( $min_value ); ?>"
			max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'xstore-amp' ); ?>"
			size="4"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			inputmode="<?php echo esc_attr( $inputmode ); ?>"
			[value]="<?php echo esc_attr($amp_qty_id); ?>"/>
		<span role="button" tabindex="-1" on="tap:AMP.setState({'<?php echo $amp_qty_id; ?>': <?php echo $plus_conditions; ?> })"><i class="et-icon et-plus"></i></span>
		<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>
	</div>
	<?php
}