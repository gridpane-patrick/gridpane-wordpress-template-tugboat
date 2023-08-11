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

global $wp_roles;
$roles = $wp_roles->roles;

$field['custom_attributes'] = ywpar_get_custom_attributes_of_custom_field( $field );

$class = isset( $field['option']['class'] ) ? $field['option']['class'] : '';
$value = maybe_unserialize( $field['value'] );
// filter to multi currencies integration.
$currencies = apply_filters( 'ywpar_get_active_currency_list', (array) get_woocommerce_currency() );
// DO_ACTION : ywpar_before_currency_loop : action triggered before the currency loop inside the option useful to multi-currency plugins.
do_action( 'ywpar_before_currency_loop' );
$index = 0;
$points_type = apply_filters( 'ywpar_enable_text_type_on_points_field', false ) ? 'text' : 'number';
if ( isset( $value['role_conversion'] ) ) :
	?>
	<div <?php echo wp_kses_post( $field['custom_attributes'] ); ?>>
		<?php
		foreach ( $value['role_conversion'] as $conversion ) :

			$index++;
			$single_role  = $conversion['role'];
			$current_name = $field['name'] . '[role_conversion][' . $index . ']';
			$current_id   = $field['id'] . '[role_conversion][' . $index . ']';
			$hide_remove  = 1 === $index ? 'hide-remove' : '';
			?>
			<div class="role-conversion-options-container">
				<div id="<?php echo esc_attr( $field['id'] ); ?>-container" data-index="<?php echo esc_attr( $index ); ?>"
					class="yit_options rm_option rm_input rm_text role-conversion-options <?php echo esc_attr( $class ); ?>">
					<div class="option">
						<div class="conversion-role">
							<p>
								<span><?php esc_html_e( 'For', 'yith-woocommerce-points-and-rewards' ); ?></span>
								<select class="ywpar_role" name="<?php echo esc_attr( $current_name ) . '[role]'; ?>"
									id="<?php echo esc_attr( $current_id ) . '[role]'; ?>">
									<?php foreach ( $roles as $key => $current_role ) : ?>
										<option
											value="<?php echo esc_attr( $key ); ?>" <?php selected( $single_role, $key, 1 ); ?>><?php echo esc_html( translate_user_role( $current_role['name'] ) ); ?></option>
									<?php endforeach; ?>
								</select>
							</p>
						</div>
						<div class="conversion-currencies">
							<?php
							foreach ( $currencies as $current_currency ) :
								$current_name = $field['name'] . '[role_conversion][' . $index . '][' . $current_currency . ']';
								$current_id   = $field['id'] . '[role_conversion][' . $index . '][' . $current_currency . ']';
								$points       = ( isset( $conversion[ $current_currency ]['points'] ) ) ? $conversion[ $current_currency ]['points'] : '';
								$money        = ( isset( $conversion[ $current_currency ]['money'] ) ) ? $conversion[ $current_currency ]['money'] : '';
								?>
								<?php if ( 'ywpar_earn_roles' === $class ) : ?>
									<p>
										<span><?php esc_html_e( 'each', 'yith-woocommerce-points-and-rewards' ); ?></span>
										<input
											type="<?php echo esc_attr( apply_filters( 'ywpar_conversion_money_field_type', 'number' ) ); ?>"
											name="<?php echo esc_attr( $current_name ); ?>[money]" step="1" min="0"
											id="<?php echo esc_attr( $current_id ); ?>-money"
											value="<?php echo esc_attr( $money ); ?>"/>



		<span><?php echo wp_kses_post( get_woocommerce_currency_symbol( $current_currency ) . ' (' . $current_currency . ')  ' . esc_html__( 'assign', 'yith-woocommerce-points-and-rewards' ) ); ?></span>
										<input type="<?php echo esc_attr( $points_type ); ?>" name="<?php echo esc_attr( $current_name ); ?>[points]"
											step="1" min="0"
											id="<?php echo esc_attr( $current_id ); ?>-points"
											value="<?php echo esc_attr( $points ); ?>"/>
										<span><?php esc_html_e( 'Points', 'yith-woocommerce-points-and-rewards' ); ?></span>

									</p>
								<?php else : ?>
								<p>
									<input type="number" name="<?php echo esc_attr( $current_name ); ?>[points]"
										step="1" min="0"
										id="<?php echo esc_attr( $current_id ); ?>-points"
										value="<?php echo esc_attr( $points ); ?>"/>
									<span><?php esc_html_e( 'Points', 'yith-woocommerce-points-and-rewards' ); ?> =</span>

									<input
										type="<?php echo esc_attr( apply_filters( 'ywpar_conversion_money_field_type', 'number' ) ); ?>"
										name="<?php echo esc_attr( $current_name ); ?>[money]" step="1" min="0"
										id="<?php echo esc_attr( $current_id ); ?>-money"
										value="<?php echo esc_attr( $money ); ?>"/>
									<span><?php echo wp_kses_post( get_woocommerce_currency_symbol( $current_currency ) . ' (' . $current_currency . ')' ) . ' ' . esc_html__( 'discount', 'yith-woocommerce-points-and-rewards' ); ?></span>
								</p>
								<?php endif; ?>

								<?php
							endforeach;
							?>
						</div>
						<div><span
								class="ywpar-remove-row yith-icon-trash <?php echo esc_attr( $hide_remove ); ?>"></span>
						</div>

					</div>
				</div>
				<!--<span class="ywpar-add-same-row"><?php esc_html_e( '+ Add rule for same user role', 'yith-woocommerce-points-and-rewards' ); ?></span>-->
				<div class="clear"></div>
			</div>
			<?php if ( count( $value['role_conversion'] ) === $index && count( $value['role_conversion'] ) > 0 ) : ?>
			<span
				class="ywpar-add-row"><?php esc_html_e( '+ Add rule for another user role', 'yith-woocommerce-points-and-rewards' ); ?></span>
			<div class="clear"></div>
		<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php
endif;
?>
