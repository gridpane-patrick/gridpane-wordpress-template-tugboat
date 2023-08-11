<?php
/**
 * Extra points multi settings field
 *
 * @since   2.2.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var $field
 */

defined( 'ABSPATH' ) || exit;

$value       = $field['value'];
$name        = $field['name'];
$index       = 0;
$repeat_last = ( isset( $field['repeat_last'] ) ) ? $field['repeat_last'] : 0;
$currencies  = apply_filters( 'ywpar_get_active_currency_list', (array) get_woocommerce_currency() );

// DO_ACTION : ywpar_before_currency_loop : action triggered before the currency loop inside the option useful to multi-currency plugins.
do_action( 'ywpar_before_currency_loop' );

$value = maybe_unserialize( $value );

if ( isset( $value['list'] ) ) :
	foreach ( $value['list'] as $element ) :
		$index++;
		$current_name = $name . '[list][' . $index . ']';
		$current_id   = $field['id'] . '[list][' . $index . ']';
		$hide_remove  = 1 === $index ? 'hide-remove' : '';
		$repeat       = ( isset( $element['repeat'] ) ) ? $element['repeat'] : 0;
		$multiple     = isset( $field['multiple'] ) ? $field['multiple'] : 1;
		$show_repeat  = isset( $field['show_repeat'] ) ? $field['show_repeat'] : 1;

		$class_repeat = '';
		$size         = count( $value['list'] );
		if ( $repeat_last && $size > $index ) {
			$class_repeat = ( $repeat_last && $size > $index ) ? 'hide' : '';
			$repeat       = 0;
		}
		?>
		<div id="<?php echo esc_attr( $field['id'] ); ?>-container" data-index="<?php echo esc_attr( $index ); ?>"
			class="yit_options rm_option rm_input rm_text extrapoint-options">
			<?php
			$counter = 0;
			foreach ( $currencies as $current_currency ) :
				$counter++;
				$curr_name = $current_name . '[' . $current_currency . ']';
				$curr_id   = $current_id . '[' . $current_currency . ']';
				$points    = ( isset( $element[ $current_currency ]['points'] ) ) ? $element[ $current_currency ]['points'] : '';
				$number    = ( isset( $element[ $current_currency ]['number'] ) ) ? $element[ $current_currency ]['number'] : '';
				?>
				<div class="option">
					<div class="left-side">
						<div>
							<input type="number" name="<?php echo esc_attr( $curr_name ); ?>[number]" step="1" min="1"
								id="<?php echo esc_attr( $curr_id ); ?>-number"
								value="<?php echo esc_attr( $number ); ?>"/>
							<span><?php echo sprintf( '%s (%s)', esc_html( get_woocommerce_currency_symbol( $current_currency ) ), esc_html( $current_currency ) ) . esc_html__( ' spent =', 'yith-woocommerce-points-and-rewards' ); ?></span>

							<input type="number" name="<?php echo esc_attr( $curr_name ); ?>[points]" step="1" min="1"
								id="<?php echo esc_attr( $curr_id ); ?>-points"
								value="<?php echo esc_attr( $points ); ?>"/>
							<span>
							<?php
							if ( 'ywpar_number_of_points_exp' === esc_attr( $field['id'] ) ) {
								esc_html_e( 'points extra', 'yith-woocommerce-points-and-rewards' );
							} else {
								esc_html_e( 'points', 'yith-woocommerce-points-and-rewards' );
							}
							?>
								</span>
						</div>
					</div>
					<div class="right_side">
						<?php if ( 1 === $counter && $show_repeat ) : ?>
						<div class="repeat <?php echo esc_attr( $class_repeat ); ?>">
							<input type="checkbox" name="<?php echo esc_attr( $current_name ); ?>[repeat]" value="1"
								id="<?php echo esc_attr( $current_id ); ?>-repeat" <?php checked( $repeat, 1, 1 ); ?>>
							<small><?php esc_html_e( 'repeat', 'yith-woocommerce-points-and-rewards' ); ?></small>
						</div>
						<?php endif ?>
						<?php if ( $multiple && 1 === $counter ) : ?>
							<span
								class="ywpar-remove-row yith-icon-trash <?php echo esc_attr( $hide_remove ); ?>"></span>
						<?php endif ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( count( $value['list'] ) === $index && count( $value['list'] ) > 0 ) : ?>
		<span class="ywpar-add-row"><?php esc_html_e( '+ Add rule' ); ?></span>
		<div class="clear"></div>
	<?php endif; ?>
		<?php
	endforeach;
endif;
?>
