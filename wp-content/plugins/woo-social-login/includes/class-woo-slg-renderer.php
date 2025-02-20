<?php 
// Exit if accessed directly
if( !defined('ABSPATH') ) exit;

/**
 * Renderer Class
 * To handles some small HTML content for front end
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
class WOO_Slg_Renderer {

	public $model, $socialfacebook, $sociallinkedin, $socialtwitter;
	public $socialfoursquare, $socialyahoo, $socialwindowslive, $socialvk, $socialamazon, $socialpaypal, $socialline, $sociallinelive,$socialapple;

	public function __construct() {
		
		global $woo_slg_model,$woo_slg_social_facebook,$woo_slg_social_linkedin,
		$woo_slg_social_twitter,$woo_slg_social_yahoo,$woo_slg_social_foursquare,
		$woo_slg_social_windowslive,$woo_slg_social_vk, $woo_slg_social_amazon, $woo_slg_social_paypal,$woo_slg_social_line,$woo_slg_social_apple;
		
		$this->model = $woo_slg_model;
		
		//social class objects
		$this->socialfacebook	= $woo_slg_social_facebook;
		$this->sociallinkedin	= $woo_slg_social_linkedin;
		$this->socialtwitter	= $woo_slg_social_twitter;
		$this->socialyahoo		= $woo_slg_social_yahoo;
		$this->socialfoursquare	= $woo_slg_social_foursquare;
		$this->socialwindowslive= $woo_slg_social_windowslive;
		$this->socialvk			= $woo_slg_social_vk;
		$this->socialamazon     = $woo_slg_social_amazon;
		$this->socialpaypal     = $woo_slg_social_paypal;
		$this->socialline       = $woo_slg_social_line;
		$this->socialapple       = $woo_slg_social_apple;
	}

	/**
	 * Show All Social Login Buttons
	 * 
	 * Handles to show all social login buttons
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_social_login_inner_buttons( $redirect_url = '', $networks = array() ) {
		
		global $woo_slg_options, $post, $pagenow;

		// get redirect url from settings
		if( empty($redirect_url) ) {
			$redirect_url = woo_slg_get_redirection_url();
		}

		// Print the GDPR Privacy Notice
		do_action('woo_slg_before_social_buttons');
		
		//load social button
		woo_slg_get_template( 'social-buttons.php', array( 'login_redirect_url' => $redirect_url, 'networks' => $networks ) );
		
		//enqueue social front script
		wp_enqueue_script( 'woo-slg-public-script' );
	}

	/**
	 * Add Social Login Buttons To 
	 * Checkout page
	 * 
	 * Handles to add all social media login
	 * buttons to woo checkout page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_social_login_buttons( $title = '', $redirect_url = '' ) {
		
		global $woo_slg_options, $post;
		
		//check user is logged in to site or not and any single social login button is enable or not
		if( !is_user_logged_in() && woo_slg_check_social_enable() && $title == 'checkout/form-login.php' && $woo_slg_options['woo_slg_enable_on_checkout_page'] != "no" ) {
			$this->woo_slg_social_login();
		}
		//check user is logged in to site  and any single social login button is enable or not 
		elseif( is_user_logged_in() && woo_slg_check_social_enable() && ( ( WC_VERSION < 2.6 && $title == 'myaccount/my-downloads.php' ) || ( WC_VERSION >= 2.6 && $title == 'myaccount/form-edit-account.php' && $woo_slg_options['woo_slg_display_link_acc_detail'] == 'yes') ) ) {
			$this->woo_slg_social_profile();
		}
	}

	/**
	 * display list of connected social media
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_social_profile() {
		global $woo_slg_options;

		$user_id = get_current_user_id();

		//get primary social account type if exist
		$primary_social	= get_user_meta( $user_id, 'woo_slg_social_user_connect_via', true );

		$message = woo_slg_messages();
		$linked_profiles_arr = $this->woo_slg_get_user_social_linked_profiles();
		if( isset($linked_profiles_arr['email']) ) {
			unset($linked_profiles_arr['email']);
		}
		
		woo_slg_get_template( 'social-profile-list.php', array(
			'linked_profiles'		=> $linked_profiles_arr,
			'primary_social'		=> $primary_social,
			'user_id'				=> $user_id,
			'can_link'				=> woo_slg_can_show_all_social_link_container(),
			'woo_slg_display_link_acc_detail'	=> $woo_slg_options['woo_slg_display_link_acc_detail'],
			'add_more_link'			=> isset( $message['add_more_link'] ) ? $message['add_more_link'] : '',
			'connected_link_heading'=> isset( $message['connected_link_heading'] ) ? $message['connected_link_heading'] : '',
			'no_social_connected'	=> isset( $message['no_social_connected'] ) ? $message['no_social_connected'] : '',
			'connect_now_link'		=> isset( $message['connect_now_link'] ) ? $message['connect_now_link'] : '',
		) );

		wp_enqueue_script( 'woo-slg-unlink-script' );
	}

	/**
	 * Social Link button on thankyou page
	 * 
	 * Handles to display social link buttons on thankyou page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_maybe_render_social_link_buttons( $template_name ) {
		if( is_user_logged_in() && 'checkout/thankyou.php' === $template_name
			&& woo_slg_check_social_enable() && woo_slg_link_display_on_thankyou_page() ) {

		 	//display link buttons
			woo_slg_link_buttons();
		}
	}

	/**
	 * Social Login button on my account page
	 * 
	 * Handles to display social login buttons on my account page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_myaccount_social_login_buttons() {

		if( (woo_slg_login_display_on_myaccount_page()) || (apply_filters('woo_slg_allow_social_logn_button', false)) ) { //is my account page

			global $woo_slg_options;

			// Get redirection url
			$redirect_url = woo_slg_get_redirection_url();

			// Old twitter method does not reture the email,
			// Redirection is account/checkout page will not work there
			$tw_redirect_url = !empty( $woo_slg_options['woo_slg_redirect_url'] ) ? $woo_slg_options['woo_slg_redirect_url'] : woo_vou_get_current_page_url();

			//session create for redirect url
			if( !isset($_GET['wooslgnetwork']) ) {
				\WSL\PersistentStorage\WOOSLGPersistent::set('woo_slg_stcd_redirect_url', $tw_redirect_url);
			}

			// get title from settings
			$login_heading = isset( $woo_slg_options['woo_slg_login_heading'] ) ? $woo_slg_options['woo_slg_login_heading'] : '';

			$login_heading = apply_filters( 'woo_slg_login_heading_text', $login_heading );
			
			//do action to add login with email section
			do_action( 'woo_slg_wrapper_login_with_email', $redirect_url );

			echo '<div class="woo-slg-social-container">';
			if( !empty( $login_heading ) ) {
				echo '<span><legend>' . $login_heading . '</legend></span>';
			}
			$this->woo_slg_social_login_inner_buttons( $redirect_url );
			echo '</div>';

			//do action to add login with email section bottom
			do_action( 'woo_slg_wrapper_login_with_email_bottom', $redirect_url );
		}
	}

	/**
	 * Add Social Login Buttons To
	 * Login page
	 * 
	 * Handles to add all social media login
	 * buttons to Login page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.1
	 */
	public function woo_slg_social_login_buttons_on_login() {

		global $woo_slg_options, $post;
		//check user is logged in to site or not and any single social login button is enable or not
		if( !is_user_logged_in() && woo_slg_check_social_enable() ) {

			// get title from settings
			$login_heading = isset( $woo_slg_options['woo_slg_login_heading'] ) ? $woo_slg_options['woo_slg_login_heading'] : '';

			// Get redirection url
			$redirect_url = woo_slg_get_redirection_url();

			// Old twitter method does not reture the email,
			// Redirection is account/checkout page will not work there
			$tw_redirect_url = !empty( $woo_slg_options['woo_slg_redirect_url'] ) 
			? $woo_slg_options['woo_slg_redirect_url'] : woo_vou_get_current_page_url();

			//session create for redirect url
			if( !isset( $_GET['wooslgnetwork'] ) ) {
				\WSL\PersistentStorage\WOOSLGPersistent::set('woo_slg_stcd_redirect_url', $tw_redirect_url);
			}

			//do action to add login with email section
			do_action( 'woo_slg_wrapper_login_with_email', $redirect_url );

			echo '<div id="woo-slg-social-container-login" class="woo-slg-social-container' . '">';

			if( !empty($login_heading) ) {
				echo '<span><legend>' . $login_heading . '</legend></span>';
			}
			$this->woo_slg_social_login_inner_buttons( $redirect_url );

			echo '</div><!--.woo-slg-widget-content-->';
			//do action to add login with email section bottom
			do_action( 'woo_slg_wrapper_login_with_email_bottom', $redirect_url );
		}
	}

	/**
	 * Show Facebook Login Button
	 * 
	 * Handles to show facebook social login
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_login_facebook() {

		global $woo_slg_options,$woo_slg_social_facebook;

		//check facebook is enable or not
		if( $woo_slg_options['woo_slg_enable_facebook'] == "yes" ) {

			$fbimgurl = isset( $woo_slg_options['woo_slg_fb_icon_url'] ) && !empty( $woo_slg_options['woo_slg_fb_icon_url'] ) 
			? $woo_slg_options['woo_slg_fb_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/facebook.png';

			//Get template arguments
			$template_args	= array( 
				'fbimgurl' 		=> $fbimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_fb_icon_text'] ) ? $woo_slg_options['woo_slg_fb_icon_text'] : '',
				'facebookClass' => $woo_slg_social_facebook
			);

			//load facebook button
			woo_slg_get_template( 'social-buttons/facebook.php', $template_args );
			
			if( WOO_SLG_FB_APP_ID != '' && WOO_SLG_FB_APP_SECRET != '' ) {
				
				//enqueue FB init script
				wp_enqueue_script( 'facebook' );
				wp_enqueue_script( 'woo-slg-fbinit' );
			}
		}
	}

	/**
	 * Show Apple Login Button
	 * 
	 * Handles to show apple social login
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_login_apple() {

		global $woo_slg_options,$woo_slg_social_apple;

		//check facebook is enable or not
		if( $woo_slg_options['woo_slg_enable_apple'] == "yes" ) {

			$appleimgurl = isset( $woo_slg_options['woo_slg_apple_icon_url'] ) && !empty( $woo_slg_options['woo_slg_apple_icon_url'] ) 
			? $woo_slg_options['woo_slg_apple_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/apple.png';

			//Get template arguments
			$template_args	= array( 
				'appleimgurl' 		=> $appleimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_apple_icon_text'] ) ? $woo_slg_options['woo_slg_apple_icon_text'] : '',
				'appleClass' => $woo_slg_social_apple
			);

			//load facebook button
			woo_slg_get_template( 'social-buttons/apple.php', $template_args );
			
			if( WOO_SLG_APPLE_CLIENT_ID != '' ) {
				$apple_authurl = $this->socialapple->woo_slg_get_apple_login_url();
				
				echo '<input type="hidden" class="woo-slg-social-apple-redirect-url" id="woo_slg_social_apple_redirect_url" name="woo_slg_social_apple_redirect_url" value="'.esc_url($apple_authurl).'"/>';
			}
		}
	}
	
	/**
	 * Show Google+ Login Button
	 * 
	 * Handles to show google+ social login
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_login_googleplus() {

		global $woo_slg_options;
		
		//check google+ is enable or not
		if( $woo_slg_options['woo_slg_enable_googleplus'] == "yes" ) {
			
			$gpimgurl = isset( $woo_slg_options['woo_slg_gp_icon_url'] ) && !empty( $woo_slg_options['woo_slg_gp_icon_url'] ) 
			? $woo_slg_options['woo_slg_gp_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/googleplus.png';

			//Get template arguments
			$template_args	= array( 
				'gpimgurl' 		=> $gpimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_gp_icon_text'] ) ? $woo_slg_options['woo_slg_gp_icon_text'] : '',
			);

			//load googleplus button
			woo_slg_get_template( 'social-buttons/googleplus.php', $template_args );
			
			if( WOO_SLG_GP_CLIENT_ID != '' ) {
				$gp_authurl = '';
				
				echo '<input type="hidden" class="woo-slg-social-gp-redirect-url" id="woo_slg_social_gp_redirect_url" name="woo_slg_social_gp_redirect_url" value="'.esc_url($gp_authurl).'"/>';
			}
		}
	}
	
	/**
	 * Show Linkedin Login Button
	 * 
	 * Handles to show linkedin social login
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_login_linkedin() {

		global $woo_slg_options;
		
		//check linkedin is enable or not
		if( $woo_slg_options['woo_slg_enable_linkedin'] == "yes" ) {
			
			$liimgurl = isset( $woo_slg_options['woo_slg_li_icon_url'] ) && !empty( $woo_slg_options['woo_slg_li_icon_url'] ) 
			? $woo_slg_options['woo_slg_li_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/linkedin.png';

			//Get template arguments
			$template_args	= array( 
				'liimgurl' 		=> $liimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_li_icon_text'] ) ? $woo_slg_options['woo_slg_li_icon_text'] : '',
			);

			//load linkedin button
			woo_slg_get_template( 'social-buttons/linkedin.php', $template_args );
			
			if( WOO_SLG_LI_APP_ID != '' && WOO_SLG_LI_APP_SECRET != '' ) {
				
				$li_authurl = $this->sociallinkedin->woo_slg_linkedin_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-li-redirect-url" id="woo_slg_social_li_redirect_url" name="woo_slg_social_li_redirect_url" value="'.esc_url($li_authurl).'"/>';
			}
		}
	}
	
	/**
	 * Show Twitter Login Button
	 * 
	 * Handles to show twitter social login
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_login_twitter() {

		global $woo_slg_options;
		
		//check twitter is enable or not
		if( $woo_slg_options['woo_slg_enable_twitter'] == "yes" ) {

			$twimgurl = isset( $woo_slg_options['woo_slg_tw_icon_url'] ) && !empty( $woo_slg_options['woo_slg_tw_icon_url'] ) 
			? $woo_slg_options['woo_slg_tw_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/twitter.png';

			//Get template arguments
			$template_args	= array( 
				'twimgurl' 		=> $twimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_tw_icon_text'] ) ? $woo_slg_options['woo_slg_tw_icon_text'] : '',
			);

			//load twitter button
			woo_slg_get_template( 'social-buttons/twitter.php', $template_args );
		}
	}
	
	/**
	 * Show Yahoo Login Button
	 * 
	 * Handles to show yahoo social login
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_login_yahoo() {

		global $woo_slg_options;
		
		//check yahoo is enable or not
		if( $woo_slg_options['woo_slg_enable_yahoo'] == "yes" ) {

			$yhimgurl = isset( $woo_slg_options['woo_slg_yh_icon_url'] ) && !empty( $woo_slg_options['woo_slg_yh_icon_url'] ) 
			? $woo_slg_options['woo_slg_yh_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/yahoo.png';

			//Get template arguments
			$template_args	= array( 
				'yhimgurl' 		=> $yhimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_yh_icon_text'] ) ? $woo_slg_options['woo_slg_yh_icon_text'] : '',
			);

			//load yahoo button
			woo_slg_get_template( 'social-buttons/yahoo.php', $template_args );
			
			if( WOO_SLG_YH_CONSUMER_KEY != '' && WOO_SLG_YH_CONSUMER_SECRET != '' ) {

				$yh_authurl = $this->socialyahoo->woo_slg_get_yahoo_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-yh-redirect-url" id="woo_slg_social_yh_redirect_url" name="woo_slg_social_yh_redirect_url" value="'.esc_url($yh_authurl).'"/>';
				
			}
		}
	}
	
	/**
	 * Show Foursquare Login Button
	 * 
	 * Handles to show foursquare social login
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_login_foursquare() {

		global $woo_slg_options;
		
		//check yahoo is enable or not
		if( $woo_slg_options['woo_slg_enable_foursquare'] == "yes" ) {

			$fsimgurl = isset( $woo_slg_options['woo_slg_fs_icon_url'] ) && !empty( $woo_slg_options['woo_slg_fs_icon_url'] ) 
			? $woo_slg_options['woo_slg_fs_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/foursquare.png';

			//Get template arguments
			$template_args	= array( 
				'fsimgurl' 		=> $fsimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_fs_icon_text'] ) ? $woo_slg_options['woo_slg_fs_icon_text'] : '',
			);

			//load foursquare button
			woo_slg_get_template( 'social-buttons/foursquare.php', $template_args );
			
			if( WOO_SLG_FS_CLIENT_ID != '' && WOO_SLG_FS_CLIENT_SECRET != '' ) {

				$fs_authurl = $this->socialfoursquare->woo_slg_get_foursquare_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-fs-redirect-url" id="woo_slg_social_fs_redirect_url" name="woo_slg_social_fs_redirect_url" value="'.esc_url($fs_authurl).'"/>';
				
			}
		}
	}
	
	/**
	 * Show Windows Live Login Button
	 * 
	 * Handles to show windowlive social login
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_login_windowslive() {
		
		global $woo_slg_options;
		
		//check yahoo is enable or not
		if( $woo_slg_options['woo_slg_enable_windowslive'] == "yes" ) {
			
			$wlimgurl = isset( $woo_slg_options['woo_slg_wl_icon_url'] ) && !empty( $woo_slg_options['woo_slg_wl_icon_url'] ) 
			? $woo_slg_options['woo_slg_wl_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/windowslive.png';

			//Get template arguments
			$template_args	= array( 
				'wlimgurl' 		=> $wlimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_wl_icon_text'] ) ? $woo_slg_options['woo_slg_wl_icon_text'] : '',
			);

			//load windows live button
			woo_slg_get_template( 'social-buttons/windowslive.php', $template_args );
			
			if( WOO_SLG_WL_CLIENT_ID != '' && WOO_SLG_WL_CLIENT_SECRET != '' ) {
				
				$wl_authurl = $this->socialwindowslive->woo_slg_get_wl_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-wl-redirect-url" id="woo_slg_social_wl_redirect_url" name="woo_slg_social_wl_redirect_url" value="'.esc_url($wl_authurl).'"/>';
			}
		}
	}


	/**
	 * Show VK Login Button
	 * 
	 * Handles to show vk social login button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.2.0
	 */
	public function woo_slg_login_vk() {
		
		global $woo_slg_options;
		
		//check vk is enable or not
		if( $woo_slg_options['woo_slg_enable_vk'] == "yes" ) {
			
			$vkimgurl = isset( $woo_slg_options['woo_slg_vk_icon_url'] ) && !empty( $woo_slg_options['woo_slg_vk_icon_url'] ) 
			? $woo_slg_options['woo_slg_vk_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/vk.png';

			//Get template arguments
			$template_args	= array( 
				'vkimgurl' 		=> $vkimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_vk_icon_text'] ) ? $woo_slg_options['woo_slg_vk_icon_text'] : '',
			);

			//load vk button
			woo_slg_get_template( 'social-buttons/vk.php', $template_args );
			
			if( WOO_SLG_VK_APP_ID != '' && WOO_SLG_VK_APP_SECRET != '' ) {
				
				if( !empty($this->socialvk->vk_authurl) ) {
					$vk_authurl = $this->socialvk->vk_authurl;					
				} else {
					$vk_authurl = $this->socialvk->woo_slg_get_vk_auth_url();
				}
				
				echo '<input type="hidden" class="woo-slg-social-vk-redirect-url" id="woo_slg_social_vk_redirect_url" name="woo_slg_social_vk_redirect_url" value="'.esc_url($vk_authurl).'"/>';
			}
		}
	}
	
	/**
	 * Show login wrapper class on checkout page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.2.0
	 */
	public function woo_slg_checkout_wrapper_social_login_content() {

		global $post;

		$redirect_url = isset($post->ID) ? get_permalink( $post->ID ) : '';
		
		echo '<div class="woo-slg-social-container">';
		$this->woo_slg_social_login_inner_buttons( $redirect_url );
		echo '</div>';

	}
	
	/**
	 * Add Social Login Buttons To 
	 * Login page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_social_login() {
		
		global $post, $woo_slg_options;
		
		// get title from settings
		$login_heading = isset( $woo_slg_options['woo_slg_login_heading'] ) ? $woo_slg_options['woo_slg_login_heading'] : esc_html__( 'Prefer Social Login', 'wooslg' );
		
		$login_heading = apply_filters( 'woo_slg_login_heading_text', $login_heading);
		
		$defaulturl = isset( $post->ID ) ? get_permalink( $post->ID ) : '';

		//session create for redirect url 
		if( !isset( $_GET['wooslgnetwork'] ) ) {

			\WSL\PersistentStorage\WOOSLGPersistent::set('woo_slg_stcd_redirect_url', $defaulturl);
		}
		
		//load social button wrapper for checkout page
		woo_slg_get_template( 'checkout-social-wrapper.php', array( 'login_heading' => $login_heading ) );
	}
	
	/**
	 * Get list of all connected social media
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_get_user_social_linked_profiles( $user_id = null ) {

		// check useris login
		if( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$linked_social_login_profiles = array();
		
		$woo_social_order = get_option( 'woo_social_order' );	
		
		//get primary social account type if exist
		$primary_social	= get_user_meta( $user_id, 'woo_slg_social_user_connect_via', true );
		
		if( !empty($woo_social_order) ) {
			// Get list of saved profiles
			foreach( $woo_social_order as $provider ) {
				if( $primary_social == $provider ) {
					$social_profile = get_user_meta( $user_id, 'woo_slg_social_data', true );
				} else {
					$social_profile = get_user_meta( $user_id, 'woo_slg_social_' . $provider . '_data', true );
				}
				
				// check profile is saved
				if( !empty($social_profile) || $primary_social == $provider ) {
					// add provider to profile, as it's not saved with the raw profile
					$linked_social_login_profiles[ $provider ] =  $social_profile;
				}
			}
		}	
		
		return apply_filters( 'woo_get_user_social_linked_profiles', $linked_social_login_profiles );
	}
	
	/**
	 * Show Facebook Link Login Button
	 * Handles to show facebook Link social button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_login_link_facebook() {

		global $woo_slg_options,$woo_slg_social_facebook;

		//can show link or not
		$show_link = woo_slg_can_show_social_link( 'facebook' );

		if( $woo_slg_options['woo_slg_enable_facebook'] == "yes" && $show_link ) {

			$fblinkimgurl = isset( $woo_slg_options['woo_slg_fb_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_fb_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_fb_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/facebook-link.png';

			//Get template arguments
			$template_args	= array( 
				'fblinkimgurl' 	=> $fblinkimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_fb_link_icon_text'] ) ? $woo_slg_options['woo_slg_fb_link_icon_text'] : '',
				'facebookClass' => $woo_slg_social_facebook
			);

			//load facebook link button
			woo_slg_get_template( 'social-link-buttons/facebook_link.php', $template_args );

			if( WOO_SLG_FB_APP_ID != '' && WOO_SLG_FB_APP_SECRET != '' ) {

				//enqueue FB init script
				wp_enqueue_script( 'facebook' );
				wp_enqueue_script( 'woo-slg-fbinit' );
			}
		}
	}

	/**
	 * Show Google+ Login Link Button
	 * 
	 * Handles to show google+ social login link
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_login_link_googleplus() {

		global $woo_slg_options;
		
		//can show link or not
		$show_link = woo_slg_can_show_social_link( 'googleplus' );
		
		//check google+ is enable or not
		if( $woo_slg_options['woo_slg_enable_googleplus'] == "yes" && $show_link ) {

			$gpimglinkurl = isset( $woo_slg_options['woo_slg_gp_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_gp_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_gp_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/googleplus-link.png';

			//Get template arguments
			$template_args	= array( 
				'gpimglinkurl' 	=> $gpimglinkurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_gp_link_icon_text'] ) ? $woo_slg_options['woo_slg_gp_link_icon_text'] : '',
			);

			//load googleplus link button
			woo_slg_get_template( 'social-link-buttons/googleplus_link.php', $template_args );
			
			if( WOO_SLG_GP_CLIENT_ID != '' ) {

				$gp_authurl = '';
				
				echo '<input type="hidden" class="woo-slg-social-gp-redirect-url" id="woo_slg_social_gp_redirect_url" name="woo_slg_social_gp_redirect_url" value="'.esc_url($gp_authurl).'"/>';
			}
		}
	}


	/**
	 * Show Apple Login Link Button
	 * 
	 * Handles to show apple social link login
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_login_link_apple() {

		global $woo_slg_options,$woo_slg_social_apple;
		
		//can show link or not
		$show_link = woo_slg_can_show_social_link( 'apple' );
		
		//check apple is enable or not
		if( $woo_slg_options['woo_slg_enable_apple'] == "yes" && $show_link ) {

			$applelinkimgurl = isset( $woo_slg_options['woo_slg_apple_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_apple_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_apple_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/apple-link.png';

			//Get template arguments
			$template_args	= array( 
				'applelinkimgurl' 	=> $applelinkimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_apple_link_icon_text'] ) ? $woo_slg_options['woo_slg_apple_link_icon_text'] : '',
				'appleClass' => $woo_slg_social_apple
			);

			//load apple link button
			woo_slg_get_template( 'social-link-buttons/apple_link.php', $template_args );
			
			if( WOO_SLG_APPLE_CLIENT_ID != '' ) {

				$apple_authurl = $this->socialapple->woo_slg_get_apple_login_url();
				
				echo '<input type="hidden" class="woo-slg-social-apple-redirect-url" id="woo_slg_social_apple_redirect_url" name="woo_slg_social_apple_redirect_url" value="'.esc_url($apple_authurl).'"/>';
			}		
		}

	}
	
	/**
	 * Show Linkedin Login Link Button
	 * 
	 * Handles to show linkedin social link login
	 * button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_login_link_linkedin() {

		global $woo_slg_options;
		
		//can show link or not
		$show_link = woo_slg_can_show_social_link( 'linkedin' );
		
		//check linkedin is enable or not
		if( $woo_slg_options['woo_slg_enable_linkedin'] == "yes" && $show_link ) {

			$lilinkimgurl = isset( $woo_slg_options['woo_slg_li_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_li_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_li_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/linkedin-link.png';

			//Get template arguments
			$template_args	= array( 
				'lilinkimgurl' 	=> $lilinkimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_li_link_icon_text'] ) ? $woo_slg_options['woo_slg_li_link_icon_text'] : '',
			);

			//load linkedin link button
			woo_slg_get_template( 'social-link-buttons/linkedin_link.php', $template_args );
			
			if( WOO_SLG_LI_APP_ID != '' && WOO_SLG_LI_APP_SECRET != '' ) {

				$li_authurl = $this->sociallinkedin->woo_slg_linkedin_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-li-redirect-url" id="woo_slg_social_li_redirect_url" name="woo_slg_social_li_redirect_url" value="'.esc_url($li_authurl).'"/>';
			}		
		}
	}
	
	/**
	 * Show Twitter Link Button
	 * Handles to show twitter social link  button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_login_link_twitter() {

		global $woo_slg_options;
		
		//can show link or not
		$show_link = woo_slg_can_show_social_link( 'twitter' );
		
		//check twitter is enable or not
		if( $woo_slg_options['woo_slg_enable_twitter'] == "yes" && $show_link ) {

			$twlinkimgurl = isset( $woo_slg_options['woo_slg_tw_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_tw_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_tw_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/twitter-link.png';

			//Get template arguments
			$template_args	= array( 
				'twlinkimgurl' 	=> $twlinkimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_tw_link_icon_text'] ) ? $woo_slg_options['woo_slg_tw_link_icon_text'] : '',
			);

			//load twitter link button
			woo_slg_get_template( 'social-link-buttons/twitter_link.php', $template_args );
		}
	}
	
	/**
	 * Show Yahoo Link Button
	 * Handles to show yahoo social link button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_login_link_yahoo() {

		global $woo_slg_options;
		
		//can show link or not
		$show_link = woo_slg_can_show_social_link( 'yahoo' );
		
		//check yahoo is enable or not
		if( $woo_slg_options['woo_slg_enable_yahoo'] == "yes" && $show_link ) {
			
			$yhimgurl = isset( $woo_slg_options['woo_slg_yh_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_yh_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_yh_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/yahoo-link.png';

			//Get template arguments
			$template_args	= array( 
				'yhimgurl' 		=> $yhimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_yh_link_icon_text'] ) ? $woo_slg_options['woo_slg_yh_link_icon_text'] : '',
			);

			//load yahoo link button
			woo_slg_get_template( 'social-link-buttons/yahoo_link.php', $template_args );
			
			if( WOO_SLG_YH_CONSUMER_KEY != '' && WOO_SLG_YH_CONSUMER_SECRET != '' ) {

				$yh_authurl = $this->socialyahoo->woo_slg_get_yahoo_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-yh-redirect-url" id="woo_slg_social_yh_redirect_url" name="woo_slg_social_yh_redirect_url" value="'.esc_url($yh_authurl).'"/>';
			}
		}
	}

	/**
	 * Show Foursquare Link Button 
	 * Handles to show foursquare social link button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_login_link_foursquare() {

		global $woo_slg_options;
		
		//can show link or not
		$show_link = woo_slg_can_show_social_link( 'foursquare' );
		
		//check foursquare is enable or not
		if( $woo_slg_options['woo_slg_enable_foursquare'] == "yes" && $show_link ) {

			$fsimgurl = isset( $woo_slg_options['woo_slg_fs_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_fs_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_fs_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/foursquare-link.png';

			//Get template arguments
			$template_args	= array( 
				'fsimgurl' 		=> $fsimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_fs_link_icon_text'] ) ? $woo_slg_options['woo_slg_fs_link_icon_text'] : '',
			);

			//load foursquare link button
			woo_slg_get_template( 'social-link-buttons/foursquare_link.php', $template_args );
			
			if( WOO_SLG_FS_CLIENT_ID != '' && WOO_SLG_FS_CLIENT_SECRET != '' ) {

				$fs_authurl = $this->socialfoursquare->woo_slg_get_foursquare_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-fs-redirect-url" id="woo_slg_social_fs_redirect_url" name="woo_slg_social_fs_redirect_url" value="'.esc_url($fs_authurl).'"/>';
			}
		}
	}
	

	/**
	 * Show Line Live Login Button
	 * Handles to show linelive social login button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.9.14
	 */
	public function woo_slg_login_line() {
		
		global $woo_slg_options;
		
		//check line is enable or not
		if( $woo_slg_options['woo_slg_enable_line'] == "yes" ) {
			
			$llimgurl = isset( $woo_slg_options['woo_slg_line_icon_url'] ) && !empty( $woo_slg_options['woo_slg_line_icon_url'] ) 
			? $woo_slg_options['woo_slg_line_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/line.png';

			//Get template arguments
			$template_args	= array( 
				'lineimgurl' 		=> $llimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_line_icon_text'] ) ? $woo_slg_options['woo_slg_line_icon_text'] : '',
			);

			//load line live button
			woo_slg_get_template( 'social-buttons/line.php', $template_args );
			
			if( WOO_SLG_LINE_CLIENT_ID != '' && WOO_SLG_LINE_CLIENT_SECRET != '' ) {
				
				$line_authurl = $this->socialline->woo_slg_get_line_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-line-redirect-url" id="woo_slg_social_line_redirect_url" name="woo_slg_social_line_redirect_url" value="'.esc_url($line_authurl).'"/>';
			}
		}
	}

	/**
	 * Show Line Link Button
	 * Handles to show windowlive link login button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.9.14
	 */
	public function woo_slg_login_link_line() {
		
		global $woo_slg_options;
		
		//can show link or not
		$show_link = woo_slg_can_show_social_link( 'line' );
		
		//check Line is enable or not
		if( $woo_slg_options['woo_slg_enable_line'] == "yes" && $show_link ) {
			
			$lineimgurl = isset( $woo_slg_options['woo_slg_line_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_line_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_line_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/line-link.png';

			//Get template arguments
			$template_args	= array( 
				'lineimgurl' 		=> $lineimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_line_link_icon_text'] ) ? $woo_slg_options['woo_slg_line_link_icon_text'] : '',
			);

			//load Line link button
			woo_slg_get_template( 'social-link-buttons/line_link.php', $template_args );
			
			if( WOO_SLG_LINE_CLIENT_ID != '' && WOO_SLG_LINE_CLIENT_SECRET != '' ) {
				
				$line_authurl = $this->socialline->woo_slg_get_line_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-line-redirect-url" id="woo_slg_social_line_redirect_url" name="woo_slg_social_line_redirect_url" value="'.esc_url($line_authurl).'"/>';
			}
		}
	}

	/**
	 * Show Windows Live Link Button
	 * Handles to show windowlive link login button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_login_link_windowslive() {
		
		global $woo_slg_options;
		
		//can show link or not
		$show_link = woo_slg_can_show_social_link( 'windowslive' );
		
		//check yahoo is enable or not
		if( $woo_slg_options['woo_slg_enable_windowslive'] == "yes" && $show_link ) {
			
			$wlimgurl = isset( $woo_slg_options['woo_slg_wl_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_wl_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_wl_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/windowslive-link.png';

			//Get template arguments
			$template_args	= array( 
				'wlimgurl' 		=> $wlimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_wl_link_icon_text'] ) ? $woo_slg_options['woo_slg_wl_link_icon_text'] : '',
			);

			//load windows live link button
			woo_slg_get_template( 'social-link-buttons/windowslive_link.php', $template_args );
			
			if( WOO_SLG_WL_CLIENT_ID != '' && WOO_SLG_WL_CLIENT_SECRET != '' ) {
				
				$wl_authurl = $this->socialwindowslive->woo_slg_get_wl_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-wl-redirect-url" id="woo_slg_social_wl_redirect_url" name="woo_slg_social_wl_redirect_url" value="'.esc_url($wl_authurl).'"/>';
			}
		}
	}
	
	/**
	 * Show VK Link Button
	 * Handles to show vk link button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_login_link_vk() {
		
		global $woo_slg_options;
		
		//can show link or not
		$show_link = woo_slg_can_show_social_link( 'vk' );
		
		//check vk is enable or not
		if( $woo_slg_options['woo_slg_enable_vk'] == "yes" && $show_link ) {
			
			$vkimgurl = isset( $woo_slg_options['woo_slg_vk_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_vk_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_vk_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/vk-link.png';

			//Get template arguments
			$template_args	= array( 
				'vkimgurl' 		=> $vkimgurl,
				'button_type' 	=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 	=> !empty( $woo_slg_options['woo_slg_vk_link_icon_text'] ) ? $woo_slg_options['woo_slg_vk_link_icon_text'] : '',
			);

			//load vk link button
			woo_slg_get_template( 'social-link-buttons/vk_link.php', $template_args );
			
			if( WOO_SLG_VK_APP_ID != '' && WOO_SLG_VK_APP_SECRET != '' ) {
				
				$vk_authurl = $this->socialvk->woo_slg_get_vk_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-vk-redirect-url" id="woo_slg_social_vk_redirect_url" name="woo_slg_social_vk_redirect_url" value="'.esc_url($vk_authurl).'"/>';
			}
		}
	}	
	
	/**
	 * Show Amazon Login Button
	 * Handles to show amazon social login button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.4.0
	 */
	public function woo_slg_login_amazon() {
		
		global $woo_slg_options;

		//check amazon is enable or not
		if( $woo_slg_options['woo_slg_enable_amazon'] == "yes") {
			
			$amazonimgurl = isset( $woo_slg_options['woo_slg_amazon_icon_url'] ) && !empty( $woo_slg_options['woo_slg_amazon_icon_url'] ) 
			? $woo_slg_options['woo_slg_amazon_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/amazon.png';			

			//Get template arguments
			$template_args	= array( 
				'amazonimgurl' 		=> $amazonimgurl,
				'amazonclientid' 	=> WOO_SLG_AMAZON_CLIENT_ID,
				'button_type' 		=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 		=> !empty( $woo_slg_options['woo_slg_amazon_icon_text'] ) ? $woo_slg_options['woo_slg_amazon_icon_text'] : '',
			);

			//load amazon button
			woo_slg_get_template( 'social-buttons/amazon.php', $template_args );
			
			if( WOO_SLG_AMAZON_CLIENT_ID != '' && WOO_SLG_AMAZON_CLIENT_SECRET != '' ) {
				$amazon_authurl = $this->socialamazon->woo_slg_get_amazon_auth_url();			
				echo '<input type="hidden" class="woo-slg-social-amazon-redirect-url" id="woo_slg_social_amazon_redirect_url" name="woo_slg_social_amazon_redirect_url" value="'.esc_url($amazon_authurl).'"/>';
				
				wp_enqueue_script( 'amazon' );			
			}
			
		}
	}
	
	/**
	 * Show Amazon Login Button
	 * 
	 * Handles to show amazon social login button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.4.0
	 */
	public function woo_slg_login_link_amazon() {
		
		global $woo_slg_options;
		
		$show_link = woo_slg_can_show_social_link( 'amazon' );
		
		//check amazon is enable or not
		if( $woo_slg_options['woo_slg_enable_amazon'] == "yes" && $show_link) {
			
			$amazonimglinkurl = isset( $woo_slg_options['woo_slg_amazon_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_amazon_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_amazon_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/amazon-link.png';			

			//Get template arguments
			$template_args	= array( 
				'amazonimgurl' 		=> $amazonimglinkurl,
				'button_type' 		=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 		=> !empty( $woo_slg_options['woo_slg_amazon_link_icon_text'] ) ? $woo_slg_options['woo_slg_amazon_link_icon_text'] : '',
			);

			//load amazon button
			woo_slg_get_template( 'social-link-buttons/amazon_link.php', $template_args );
			
			if( WOO_SLG_AMAZON_CLIENT_ID != '' && WOO_SLG_AMAZON_CLIENT_SECRET != '' ) {
				$amazon_authurl = $this->socialamazon->woo_slg_get_amazon_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-amazon-redirect-url" id="woo_slg_social_amazon_redirect_url" name="woo_slg_social_amazon_redirect_url" value="'.esc_url($amazon_authurl).'"/>';
				
				wp_enqueue_script( 'amazon' );			
			}
			
		}
	}
	
	
	/**
	 * Show Paypal Login Button
	 * 
	 * Handles to show paypal social login button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.4.0
	 */
	public function woo_slg_login_paypal() {
		
		global $woo_slg_options;

		//check paypal is enable or not
		if( $woo_slg_options['woo_slg_enable_paypal'] == "yes") {
			
			$paypalimgurl = isset( $woo_slg_options['woo_slg_paypal_icon_url'] ) && !empty( $woo_slg_options['woo_slg_paypal_icon_url'] ) 
			? $woo_slg_options['woo_slg_paypal_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/paypal.png';

			//Get template arguments
			$template_args	= array( 
				'paypalimgurl' 		=> $paypalimgurl,
				'button_type' 		=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 		=> !empty( $woo_slg_options['woo_slg_paypal_icon_text'] ) ? $woo_slg_options['woo_slg_paypal_icon_text'] : '',
			);

			//load paypal button
			woo_slg_get_template( 'social-buttons/paypal.php', $template_args );
			
			if( WOO_SLG_PAYPAL_CLIENT_ID != '' && WOO_SLG_PAYPAL_CLIENT_SECRET != '' ) {
				$paypal_authurl = $this->socialpaypal->woo_slg_get_paypal_auth_url();			
				echo '<input type="hidden" class="woo-slg-social-paypal-redirect-url" id="woo_slg_social_paypal_redirect_url" name="woo_slg_social_paypal_redirect_url" value="'.esc_url($paypal_authurl).'"/>';
			}
		}
	}
	
	/**
	 * Show Paypal Login Button
	 * 
	 * Handles to show paypal social login button
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.4.0
	 */
	public function woo_slg_login_link_paypal() {
		
		global $woo_slg_options;
		
		$show_link = woo_slg_can_show_social_link( 'paypal' );
		
		//check amazon is enable or not
		if( $woo_slg_options['woo_slg_enable_paypal'] == "yes" && $show_link) {
			
			$paypalimglinkurl = isset( $woo_slg_options['woo_slg_paypal_link_icon_url'] ) && !empty( $woo_slg_options['woo_slg_paypal_link_icon_url'] ) 
			? $woo_slg_options['woo_slg_paypal_link_icon_url'] : esc_url(WOO_SLG_IMG_URL) . '/paypal-link.png';						

			//Get template arguments
			$template_args	= array( 
				'paypalimglinkurl' 	=> $paypalimglinkurl,
				'button_type' 		=> !empty( $woo_slg_options['woo_slg_social_btn_type'] ) ? $woo_slg_options['woo_slg_social_btn_type'] : '',
				'button_text' 		=> !empty( $woo_slg_options['woo_slg_paypal_link_icon_text'] ) ? $woo_slg_options['woo_slg_paypal_link_icon_text'] : '',
			);

			//load paypal button
			woo_slg_get_template( 'social-link-buttons/paypal_link.php', $template_args );
			
			if( WOO_SLG_PAYPAL_CLIENT_ID != '' && WOO_SLG_PAYPAL_CLIENT_SECRET != '' ) {
				$paypal_authurl = $this->socialpaypal->woo_slg_get_paypal_auth_url();
				
				echo '<input type="hidden" class="woo-slg-social-paypal-redirect-url" id="woo_slg_social_paypal_redirect_url" name="woo_slg_social_paypal_redirect_url" value="'.esc_url($paypal_authurl).'"/>';			
			}
		}
	}

	/**
	 * Social Login button on Woocommercce registration page
	 * 
	 * Handles to display social login buttons on Woocommercce registration page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_on_woo_register_social_login_buttons() {

		if ( ( woo_slg_login_display_on_woo_register_page() ) || ( apply_filters( 'woo_slg_allow_social_logn_button_on_woo_registeration_page', false ) ) ) { //is my account page

			global $woo_slg_options;

			// Get redirection url
			$redirect_url = woo_slg_get_redirection_url();

			// Old twitter method does not reture the email,
			// Redirection is account/checkout page will not work there
			$tw_redirect_url = !empty( $woo_slg_options['woo_slg_redirect_url'] ) 
			? $woo_slg_options['woo_slg_redirect_url'] : woo_vou_get_current_page_url();
			
			//session create for redirect url
			if( !isset($_GET['wooslgnetwork']) ) {
				\WSL\PersistentStorage\WOOSLGPersistent::set('woo_slg_stcd_redirect_url', $tw_redirect_url);
			}

			//do action to add login with email section
			do_action( 'woo_slg_wrapper_login_with_email', $redirect_url );
			
			// get title from settings
			$login_heading = isset( $woo_slg_options['woo_slg_login_heading'] ) ? $woo_slg_options['woo_slg_login_heading'] : '';

			$login_heading = apply_filters( 'woo_slg_login_heading_text', $login_heading );

			echo '<div class="woo-slg-social-container">';
			if( !empty($login_heading) ) {
				echo '<span><legend>' . $login_heading . '</legend></span>';
			}

			$this->woo_slg_social_login_inner_buttons( $redirect_url );
			echo '</div>';
			//do action to add login with email section bottom
			do_action( 'woo_slg_wrapper_login_with_email_bottom', $redirect_url );
		}
	}

	/**
	 * Social Login button on Woocommercce registration page
	 * 
	 * Handles to display social login buttons on Woocommercce registration page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_wrapper_login_with_email_content( $redirect_url = '' ) {

		global $woo_slg_options, $pagenow;

		$enable_login_with_email = ( !empty($woo_slg_options['woo_slg_enable_email'])) ? $woo_slg_options['woo_slg_enable_email'] : '' ;

		if( $enable_login_with_email == 'yes' ) {
			
			$login_email_heading = (!empty($woo_slg_options['woo_slg_login_email_heading'])) ? $woo_slg_options['woo_slg_login_email_heading'] : '';

			$login_email_placeholder = (!empty($woo_slg_options['woo_slg_login_email_placeholder'])) ? $woo_slg_options['woo_slg_login_email_placeholder'] : esc_html__('Enter your email address', 'wooslg') ;

			$login_btn_text = (!empty($woo_slg_options['woo_slg_login_btn_text'])) ? $woo_slg_options['woo_slg_login_btn_text'] : esc_html__('Sign in', 'wooslg');

			$seprater_text = (!empty($woo_slg_options['woo_slg_login_email_seprater_text'])) ? $woo_slg_options['woo_slg_login_email_seprater_text'] : '';

			$position = (!empty($woo_slg_options['woo_slg_login_email_position'])) ? $woo_slg_options['woo_slg_login_email_position'] : 'top';

			// Get redirection url
			if( empty($redirect_url) ) $redirect_url = woo_slg_get_redirection_url();

			//Get template arguments
			$template_args	= array( 
				'redirect_url' 				=> $redirect_url,
				'login_email_heading' 		=> $login_email_heading,
				'login_email_placeholder' 	=> $login_email_placeholder,
				'login_btn_text' 			=> $login_btn_text,
				'seprater_text' 			=> $seprater_text,
				'position'					=> $position,
			);

			//load email login section
			woo_slg_get_template( 'email-login.php', $template_args );
		}
	}

	/**
	 * Social Link button on thankyou page
	 * 
	 * Handles to display social link buttons on thankyou page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.8.3
	 */
	public function woo_slg_gdpr_privacy_notice() {

		global $woo_slg_options;
		$gdpr_privacy_notice = '';

		if ( !empty($woo_slg_options['woo_slg_enable_gdpr']) && ($woo_slg_options['woo_slg_enable_gdpr']=='yes') && !empty($woo_slg_options['woo_slg_gdpr_privacy_page']) && !empty($woo_slg_options['woo_slg_gdpr_privacy_policy']) ) {

		 	//display link buttons
			$privacy_page_id = $woo_slg_options['woo_slg_gdpr_privacy_page'];

			$privacy_page_link = '<a href="'.get_permalink($privacy_page_id).'" class="wooslg-privacy-policy-link" target="_blank">'. esc_html__('privacy policy','wooslg').'</a>';
			$privacy_policy_text = str_replace( '[privacy_policy]', $privacy_page_link, $woo_slg_options['woo_slg_gdpr_privacy_policy'] );

			$gdpr_privacy_notice = '<div class="wooslg-privacy-policy-text"><p>'. $privacy_policy_text .'</p></div>';

			//Get template arguments
			$template_args	= array( 
				'privacy_page_id' 		=> $privacy_page_id,
				'privacy_page_link' 	=> $privacy_page_link,
				'privacy_policy_text' 	=> $privacy_policy_text
			);

			//load email login section
			woo_slg_get_template( 'gdpr-text.php', $template_args );
		}
	}

}