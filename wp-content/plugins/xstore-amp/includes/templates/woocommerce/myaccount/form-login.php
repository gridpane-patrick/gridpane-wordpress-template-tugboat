<?php
/**
 * Login Form
 *
 * @package    form-login.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$xstore_amp = XStore_AMP::get_instance();

$action_login_url =  admin_url('admin-ajax.php?action=xstore_amp_process_login');
$action_login_url = preg_replace('#^https?:#', '', $action_login_url);

$action_registration_url =  admin_url('admin-ajax.php?action=xstore_amp_process_registration');
$action_registration_url = preg_replace('#^https?:#', '', $action_registration_url);

$with_registration = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );

do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php if ( $with_registration ) : ?>

<div class="u-columns col2-set" id="customer_login">

    <amp-selector role="tablist"
                  on="select:TabPanels.toggle(index=event.targetOption, value=true)"
                  keyboard-select-mode="focus">
        <div id="tab1"
             role="tab"
             aria-controls="tabpanel1"
             option="0"
             selected><?php esc_html_e('Login','xstore-amp'); ?></div>
        <span class="tab-separator">/</span>
        <div id="tab2"
             role="tab"
             aria-controls="tabpanel2"
             option="1"><?php esc_html_e('Register', 'xstore-amp'); ?></div>
    </amp-selector>
	
        <amp-selector id="TabPanels">

    <div id="tabpanel1"
         role="tabpanel"
         aria-labelledby="tab1"
         option
         selected>
		
<?php endif; ?>
		
		    <form class="woocommerce-form woocommerce-form-login login" method="post" action-xhr="<?php echo esc_url($action_login_url); ?>">
			
			<?php do_action( 'woocommerce_login_form_start' ); ?>
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="username"><?php esc_html_e( 'Username or email address', 'xstore-amp' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password"><?php esc_html_e( 'Password', 'xstore-amp' ); ?>&nbsp;<span class="required">*</span></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
			</p>
			
			<?php do_action( 'woocommerce_login_form' ); ?>
			
			<p class="form-row">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'xstore-amp' ); ?></span>
				</label>
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit" class="woocommerce-button button woocommerce-form-login__submit" name="login" value="<?php esc_attr_e( 'Log in', 'xstore-amp' ); ?>"><?php esc_html_e( 'Log in', 'xstore-amp' ); ?></button>
			</p>
			<p class="woocommerce-LostPassword lost_password">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'xstore-amp' ); ?></a>
			</p>
			
            <?php $xstore_amp->form_submitting(); ?>
        
            <?php $xstore_amp->form_success(); ?>
        
            <?php $xstore_amp->form_error(); ?>
			
			<?php do_action( 'woocommerce_login_form_end' ); ?>
		
		</form>
		
		<?php if ( $with_registration ) : ?>
        
        </div>

        <div id="tabpanel2"
             role="tabpanel"
             aria-labelledby="tab2"
             option>
		
		    <form method="post" action-xhr="<?php echo esc_url($action_registration_url); ?>" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
			
			<?php do_action( 'woocommerce_register_form_start' ); ?>
			
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
				
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_username"><?php esc_html_e( 'Username', 'xstore-amp' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
				</p>
			
			<?php endif; ?>
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_email"><?php esc_html_e( 'Email address', 'xstore-amp' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
			</p>
			
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
				
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_password"><?php esc_html_e( 'Password', 'xstore-amp' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
				</p>
			
			<?php else : ?>
				
				<p><?php esc_html_e( 'A password will be sent to your email address.', 'xstore-amp' ); ?></p>
			
			<?php endif; ?>
			
			<?php do_action( 'woocommerce_register_form' ); ?>
			
			<p class="woocommerce-form-row form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'xstore-amp' ); ?>"><?php esc_html_e( 'Register', 'xstore-amp' ); ?></button>
			</p>
			
            <?php $xstore_amp->form_submitting(); ?>
        
            <?php $xstore_amp->form_success(); ?>
        
            <?php $xstore_amp->form_error(); ?>
			
			<?php do_action( 'woocommerce_register_form_end' ); ?>
		
		</form>
        
        </div>
	
    </amp-selector>

</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' );