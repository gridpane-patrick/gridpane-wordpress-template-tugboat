<?php
/**
 * Extra Points on Levels Option
 *
 * @package    YITH
 * @author    Armando Liccardo
 * @since     3.0.0
 *
 * @var array $field
 */

defined( 'ABSPATH' ) || exit;

$index             = 0;
$value             = maybe_unserialize( $field['value'] );
$plans             = yith_wcmbs_get_plans();
$custom_attributes = $field['custom_attributes'];

if ( isset( $value['list'] ) ) :
	foreach ( $value['list'] as $element ) :
		$index++;
		$current_name = $field['name'] . '[list][' . $index . ']';
		$current_id   = $field['id'] . '[list][' . $index . ']';
		$hide_remove  = 1 === $index ? 'hide-remove' : 1;
		$points       = ( isset( $element['points'] ) ) ? $element['points'] : '';
		$plan         = ( isset( $element['plan'] ) ) ? $element['plan'] : '';
		$multiple     = isset( $multiple ) ? $multiple : 1;
		?>
		<div id="<?php echo esc_attr( $field['id'] ); ?>-container" data-index="<?php echo esc_attr( $index ); ?>"
			class="yit_options rm_option rm_input rm_text extrapoint-options">
			<div class="option">
				<span><?php esc_html_e( 'Assign', 'yith-woocommerce-points-and-rewards' ); ?></span>
				<input type="number" name="<?php echo esc_attr( $current_name ); ?>[points]" step="1" min="1"
					id="<?php echo esc_attr( $current_id ); ?>-points"
					value="<?php echo esc_attr( $points ); ?>"/>

				<span><?php esc_html_e( 'points to member of', 'yith-woocommerce-points-and-rewards' ); ?></span>
				<select <?php echo esc_attr( $custom_attributes ); ?> name="<?php echo esc_attr( $current_name ); ?>[plan]" id="<?php echo esc_attr( $current_id ); ?>-plan">
					<?php
					foreach ( $plans as $plan_item ) {
						?>
						<option value="<?php echo esc_attr( $plan_item ); ?>" <?php selected( (int) $plan, (int) $plan_item ); ?>><?php echo esc_html( get_the_title( $plan_item ) ); ?></option>
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
