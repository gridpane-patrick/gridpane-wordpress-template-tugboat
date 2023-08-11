<?php
/**
 * Extra points levels field
 *
 * @since   2.2.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var $field
 */

defined( 'ABSPATH' ) || exit;

$index             = 0;
$value             = maybe_unserialize( $field['value'] );
$levels            = YITH_WC_Points_Rewards_Helper::get_levels_badges();
$custom_attributes = $field['custom_attributes'];

if ( isset( $value['list'] ) ) :
	foreach ( $value['list'] as $element ) :
		$index ++;
		$current_name = $field['name'] . '[list][' . $index . ']';
		$current_id   = $field['id'] . '[list][' . $index . ']';
		$hide_remove  = 1 === $index ? 'hide-remove' : '';
		$points       = ( isset( $element['points'] ) ) ? $element['points'] : '';
		$level        = ( isset( $element['level'] ) ) ? $element['level'] : '';
		$multiple     = isset( $multiple ) ? $multiple : 1;
		?>
		<div id="<?php echo esc_attr( $field['id'] ); ?>-container" data-index="<?php echo esc_attr( $index ); ?>"
			class="yit_options rm_option rm_input rm_text extrapoint-options">
			<div class="option">
				<span><?php esc_html_e( 'Assign', 'yith-woocommerce-points-and-rewards' ); ?></span>
				<input type="number" name="<?php echo esc_attr( $current_name ); ?>[points]" step="1" min="1"
					id="<?php echo esc_attr( $current_id ); ?>-points"
					value="<?php echo esc_attr( $points ); ?>"/>

				<span><?php echo esc_html__( 'points to users with level', 'yith-woocommerce-points-and-rewards' ); ?></span>
				<select <?php echo esc_html( $custom_attributes ); ?> name="<?php echo esc_attr( $current_name ); ?>[level]" id="<?php echo esc_attr( $current_id ); ?>-level">
					<?php
					foreach ( $levels as $level_id => $level_obj ) {
						?>
						<option value="<?php echo esc_attr( $level_id ); ?>" <?php selected( $level, $level_id ); ?>><?php echo esc_html( $level_obj->get_name() ); ?></option>
						<?php
					}
					?>
				</select>

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
