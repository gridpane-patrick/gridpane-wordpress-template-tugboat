<?php
/**
 * Checkout coupon form
 *
 * @package    form-coupon.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

if ( ! wc_coupons_enabled() ) { // @codingStandardsIgnoreLine.
	return;
}

$xstore_amp = XStore_AMP::get_instance();

$submit_url =  admin_url('admin-ajax.php?action=xstore_amp_apply_coupon');
$submit_url = preg_replace('#^https?:#', '', $submit_url);

?>
<div class="woocommerce-form-coupon-toggle" on="tap:checkout_coupon.toggleVisibility" role="button" tabindex="-1">
	<?php wc_print_notice( apply_filters( 'woocommerce_checkout_coupon_message', esc_html__( 'Have a coupon?', 'xstore-amp' ) . ' <a href="#" class="showcoupon">' . esc_html__( 'Click here to enter your code', 'xstore-amp' ) . '</a>' ), 'notice' ); ?>
</div>

<form class="checkout_coupon woocommerce-form-coupon" action-xhr="<?php echo esc_url( $submit_url ); ?>" id="checkout_coupon" method="post" hidden>
	
	<p><?php esc_html_e( 'If you have a coupon code, please apply it below.', 'xstore-amp' ); ?></p>
	
	<p class="form-row form-row-first flex align-items-center coupon">
		<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'xstore-amp' ); ?>" id="coupon_code" value="" />
        <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply', 'xstore-amp' ); ?>"><?php esc_html_e( 'Apply', 'xstore-amp' ); ?></button>
	</p>
	
	<?php $xstore_amp->form_submitting(); ?>
	
	<?php $xstore_amp->form_success(); ?>
	
	<?php $xstore_amp->form_error(); ?>
</form>