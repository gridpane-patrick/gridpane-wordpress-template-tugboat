<?php
/**
 * Social Button Template
 * 
 * Handles to load social button template
 * 
 * Override this template by copying it to yourtheme/woo-social-login/social-buttons.php
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */

if( ! defined('ABSPATH') ) exit; // Exit if accessed directly ?>

<div class="woo-slg-social-wrap">

	<?php
	if( !empty($networks) ) { // code to display selected networks only from shortcode
		global $woo_slg_render;

		$render	= $woo_slg_render;
		$priority = 5;

		foreach( $networks as $key => $network ) {
			if( empty($network) ) {
				continue;
			}

			add_action( 'woo_slg_shortcode_selected_social_login', array( $render, 'woo_slg_login_' . $network ), $priority );
			$priority += 5;
		}

		do_action( 'woo_slg_shortcode_selected_social_login' ); //do action to add social login buttons 
	} else {
		do_action( 'woo_slg_checkout_social_login' ); //do action to add social login buttons 
	} ?>

	<div class="woo-slg-clear"></div>
</div><!--.woo-slg-social-wrap-->

<div class="woo-slg-login-error"></div><!--woo-slg-login-error-->

<div class="woo-slg-login-loader">
	<img src="<?php echo esc_url(WOO_SLG_IMG_URL);?>/social-loader.gif" alt="<?php esc_html_e( 'Social Loader', 'wooslg');?>"/>
</div><!--.woo-slg-login-loader-->

<!-- After Login Redirect To This URL -->
<input type="hidden" class="woo-slg-redirect-url" id="woo_slg_redirect_url" value="<?php echo $login_redirect_url;?>" />
<?php 
\WSL\PersistentStorage\WOOSLGPersistent::set( 'woo_slg_fb_redirect_url', $login_redirect_url );

// check if WPML is active
if( function_exists('icl_object_id') && class_exists('SitePress') ) {
	// set this code to send user notification email with current WPML language
    \WSL\PersistentStorage\WOOSLGPersistent::set('woo_slg_wpml_lang', ICL_LANGUAGE_CODE);
} ?>