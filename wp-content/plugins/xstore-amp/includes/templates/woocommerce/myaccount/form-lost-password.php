<?php
/**
 * Lost password form
 *
 * @package    form-lost-password.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit;

$xstore_amp = XStore_AMP::get_instance();

$action_lost_pass_url =  admin_url('admin-ajax.php?action=xstore_amp_process_lost_password');
$action_lost_pass_url = preg_replace('#^https?:#', '', $action_lost_pass_url);

do_action( 'woocommerce_before_lost_password_form' );
?>
	
	<form method="post" class="woocommerce-ResetPassword lost_reset_password" action-xhr="<?php echo esc_url($action_lost_pass_url); ?>">
		
		<p><?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'xstore-amp' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>
		
		<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
			<label for="user_login"><?php esc_html_e( 'Username or email', 'xstore-amp' ); ?></label>
			<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" />
		</p>
		
		<div class="clear"></div>
		
		<?php do_action( 'woocommerce_lostpassword_form' ); ?>
		
		<p class="woocommerce-form-row form-row">
			<input type="hidden" name="wc_reset_password" value="true" />
			<button type="submit" class="woocommerce-Button button" value="<?php esc_attr_e( 'Reset password', 'xstore-amp' ); ?>"><?php esc_html_e( 'Reset password', 'xstore-amp' ); ?></button>
		</p>
		
		<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
		
		<?php $xstore_amp->form_submitting(); ?>
		
		<?php $xstore_amp->form_success(); ?>
		
		<?php $xstore_amp->form_error(); ?>
	
	</form>
<?php
do_action( 'woocommerce_after_lost_password_form' );