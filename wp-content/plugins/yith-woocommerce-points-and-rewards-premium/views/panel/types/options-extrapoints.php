<?php
/**
 * Extra points field
 *
 * @since   2.2.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var $field
 */

defined( 'ABSPATH' ) || exit;

$index = 0;

$value       = maybe_unserialize( $field['value'] );
$repeat_last = ( isset( $field['repeat_last'] ) ) ? $field['repeat_last'] : 0;
if ( isset( $value['list'] ) ) :
	$size = count( $value['list'] );
	foreach ( $value['list'] as $element ) :
		$index++;
		$class_repeat = '';
		$repeat       = ( isset( $element['repeat'] ) ) ? $element['repeat'] : 0;

		if ( $repeat_last && $size > $index ) {
			$class_repeat = ( $repeat_last && $size > $index ) ? 'hide' : '';
			$repeat = 0;
		}

		$current_name = $field['name'] . '[list][' . $index . ']';
		$current_id   = $field['id'] . '[list][' . $index . ']';
		$hide_remove  = 1 === $index ? 'hide-remove' : '';
		$points       = ( isset( $element['points'] ) ) ? $element['points'] : '';
		$number       = ( isset( $element['number'] ) ) ? $element['number'] : '';
		$multiple     = isset( $multiple ) ? $multiple : 1;
		$show_repeat = isset( $show_repeat ) ? $show_repeat : 1;
		?>
		<div id="<?php echo esc_attr( $field['id'] ); ?>-container" data-index="<?php echo esc_attr( $index ); ?>"
			class="yit_options rm_option rm_input rm_text extrapoint-options">
			<div class="option">

				<input type="number" name="<?php echo esc_attr( $current_name ); ?>[number]" step="1" min="1"
					id="<?php echo esc_attr( $current_id ); ?>-number"
					value="<?php echo esc_attr( $number ); ?>"/>
				<span><?php echo esc_html( $field['label'] ); ?></span>

				<input type="number" name="<?php echo esc_attr( $current_name ); ?>[points]" step="1" min="1"
					id="<?php echo esc_attr( $current_id ); ?>-points"
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

				<?php if ( $show_repeat ) : ?>
					<div class="repeat <?php echo esc_attr( $class_repeat ); ?>">
						<input type="checkbox" name="<?php echo esc_attr( $current_name ); ?>[repeat]" value="1"
							id="<?php echo esc_attr( $current_id ); ?>-repeat" <?php checked( $repeat, 1, 1 ); ?>>
						<small><?php esc_html_e( 'repeat', 'yith-woocommerce-points-and-rewards' ); ?></small>
					</div>
				<?php endif ?>
				<?php if ( $multiple ) : ?>
					<span class="ywpar-remove-row yith-icon-trash <?php echo esc_attr( $hide_remove ); ?>"></span>

				<?php endif ?>
			</div>

		</div>
		<?php if ( count( $value['list'] ) === $index && count( $value['list'] ) > 0 ) : ?>
		<span class="ywpar-add-row"><?php esc_html_e( '+ Add rule', 'yith-woocommerce-points-and-rewards' ); ?></span>
		<div class="clear"></div>
	<?php endif; ?>
	<?php
	endforeach;
endif;
?>
