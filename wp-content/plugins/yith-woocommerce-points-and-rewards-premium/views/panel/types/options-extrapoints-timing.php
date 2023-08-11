<?php
/**
 * Extra points timing fields
 *
 * @since   2.2.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var $field
 */

defined( 'ABSPATH' ) || exit;

$index = 0;
$value = maybe_unserialize( $field['value'] );

$day_options = array(
	'first_day' => esc_html__( '1st day', 'yith-woocommerce-points-and-rewards' ),
	'last_day'  => esc_html__( 'Last day', 'yith-woocommerce-points-and-rewards' ),
);

$when_options = array(
	'each_month' => esc_html__( 'Each month', 'yith-woocommerce-points-and-rewards' ),
	'each_week'  => esc_html__( 'Each week', 'yith-woocommerce-points-and-rewards' ),
);

$day  = ( isset( $value['day'] ) ) ? $value['day'] : 'first_day';
$when = ( isset( $value['when'] ) ) ? $value['when'] : 'each_month';

?>
<div id="<?php echo esc_attr( $field['id'] ); ?>-container"
	class="yit_options rm_option rm_input rm_text extrapoint-options">
	<div class="option">
		<select <?php echo esc_html( $field['custom_attributes'] ); ?>
			name="<?php echo esc_attr( $field['name'] ); ?>[day]" id="<?php echo esc_attr( $field['name'] ); ?>-day">
			<?php foreach ( $day_options as $day_v => $day_label ) : ?>
				<option value="<?php echo esc_attr( $day_v ); ?>" <?php selected( $day, $day_v ); ?>><?php echo esc_html( $day_label ); ?></option>
				<?php endforeach; ?>
		</select>

		<span><?php esc_html_e( 'of:', 'yith-woocommerce-points-and-rewards' ); ?></span>
		<select <?php echo esc_html( $field['custom_attributes'] ); ?> name="<?php echo esc_attr( $field['name'] ); ?>[when]" id="<?php echo esc_attr( $field['id'] ); ?>-when">
			<?php foreach ( $when_options as $when_v => $when_label ) : ?>
				<option value="<?php echo esc_attr( $when_v ); ?>" <?php selected( $when, $when_v ); ?>><?php echo esc_html( $when_label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

