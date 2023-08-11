<?php
/**
 * Edit address form
 *
 * @package    form-edit-address.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit;

$xstore_amp = XStore_AMP::get_instance();

$action_url =  admin_url('admin-ajax.php?action=xstore_amp_save_address');
$action_url = preg_replace('#^https?:#', '', $action_url);

$address_type = '';
if( $load_address == 'billing'){
	$address_type = 'edit_billing_address';
}elseif ($load_address == 'shipping') {
	$address_type = 'edit_shipping_address';
}

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Billing address', 'xstore-amp' ) : esc_html__( 'Shipping address', 'xstore-amp' );

do_action( 'woocommerce_before_edit_account_address_form' ); ?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else :

?>
	
	<form method="post" action-xhr="<?php echo esc_url($action_url); ?>">
		
		<h3><?php echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ); ?></h3><?php // @codingStandardsIgnoreLine ?>
		
		<div class="woocommerce-address-fields">
			<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>
			
			<div class="woocommerce-address-fields__field-wrapper">
				<?php
//				foreach ( $address as $key => $field ) {
//					woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
//				}
				foreach ( $address as $key => $field ) {
					if ( isset( $field['country_field'], $address[ $field['country_field'] ] ) ) {
						$field['country'] = wc_get_post_data_by_key( $field['country_field'], $address[ $field['country_field'] ]['value'] );
					}
					ob_start();
					woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
					$test = ob_get_contents();
					ob_get_clean();
					echo $test;
				}
				?>
			</div>
			
			<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>
			
			<p>
				<button type="submit" class="button" name="save_address" value="<?php esc_attr_e( 'Save address', 'xstore-amp' ); ?>"><?php esc_html_e( 'Save address', 'xstore-amp' ); ?></button>
				<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
				
<!--				<input type="hidden" name="action" value="edit_address" />-->
				
				<input type="hidden" name="address_type" value="<?php echo $address_type;?>" />
			</p>
		</div>
		
		<?php $xstore_amp->form_submitting(); ?>
		
		<?php $xstore_amp->form_success(); ?>
		
		<?php $xstore_amp->form_error(); ?>
	
	</form>

<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' );