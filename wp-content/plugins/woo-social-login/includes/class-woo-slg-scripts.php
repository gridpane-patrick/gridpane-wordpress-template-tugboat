<?php
// Exit if accessed directly
if( !defined('ABSPATH') ) exit;

/**
 * Scripts Class
 * 
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
class WOO_Slg_Scripts{

	public $socialtwitter;

	public function __construct() {

		global $woo_slg_social_twitter;

		//social class objects
		$this->socialtwitter = $woo_slg_social_twitter;
	}

	/**
	 * Enqueue Styles for backend on needed page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_admin_styles( $hook_suffix ) {

		$pages_hook_suffix = array( 'post-new.php', 'post.php', 'toplevel_page_woo-social-login', 'user-edit.php', 'profile.php', 'woocommerce-social-login_page_woo-social-settings' );

		wp_register_style( 'woo-slg-notice-style', esc_url(WOO_SLG_URL) . 'includes/css/style-notice.css', array(), WOO_SLG_VERSION );
		
		wp_register_style( 'woo-slg-style-admin-popup', esc_url(WOO_SLG_URL) . 'includes/css/style-admin-popup.css', array(), WOO_SLG_VERSION );

		$theme = wp_get_theme( get_template() );

		if( $theme->get('Author') == "Elegant Themes" && $theme->get('Name') == "Divi" ){
			wp_enqueue_style( 'woo-slg-style-admin-popup' );
		}
		
		//Check pages when you needed
		if( in_array($hook_suffix, $pages_hook_suffix) ) {

			wp_register_style( 'woo-slg-select2-min-styles', esc_url(WOO_SLG_URL) . 'includes/css/select2.min.css', array(), WOO_SLG_VERSION );
			wp_enqueue_style( 'woo-slg-select2-min-styles' );

			wp_register_style( 'woo-slg-admin-styles', esc_url(WOO_SLG_URL) . 'includes/css/style-admin.css', array(), WOO_SLG_VERSION );
			wp_enqueue_style( 'woo-slg-admin-styles' );
		}
	}

	/**
	 * Enqueue Scripts for backend on needed page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_admin_scripts( $hook_suffix ) {

		global $wp_version;
		$newui = $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader

        wp_register_script( 'woo-slg-notice', esc_url(WOO_SLG_URL) . 'includes/js/woo-slg-notice.js', array('jquery'), WOO_SLG_VERSION, true );

        // Localize script
        wp_localize_script( 'woo-slg-notice', 'WooVouAdminOptions', array(
            'woo_slg_version' => WOO_SLG_VERSION
        ) );

		$pages_hook_suffix = array( 'toplevel_page_woo-social-login', 'woocommerce-social-login_page_woo-social-settings' );

		//Check pages when you needed
		if( in_array($hook_suffix, $pages_hook_suffix) ) {

			// loads the required scripts for the meta boxes
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'postbox' );

			wp_register_script( 'woo-slg-select2-min-scripts', esc_url(WOO_SLG_URL) . 'includes/js/select2.min.js', array('jquery'), WOO_SLG_VERSION, true );
			wp_enqueue_script( 'woo-slg-select2-min-scripts' );

			wp_register_script( 'woo-slg-admin-scripts', esc_url(WOO_SLG_URL) . 'includes/js/woo-slg-admin.js', array('jquery', 'jquery-ui-sortable'), WOO_SLG_VERSION, true );
			wp_enqueue_script( 'woo-slg-admin-scripts' );

			wp_localize_script( 'woo-slg-admin-scripts', 'WooVouAdminSettings', array(
				'new_media_ui' => $newui,
				'reset_settings_warning' => esc_html__( 'Click OK to reset all options. All settings will be lost!', 'wooslg' ),
			) );

			wp_enqueue_media();
		}
	}

	/**
	 * Enqueue Scripts for public side
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_public_scripts() {

		global $woo_slg_options, $post;

		//check if site is secure then use https:// else http://
		$suffix = is_ssl() ? 'https://' : 'http://';

		//check facebook social login enable or not
		if( !empty($woo_slg_options['woo_slg_enable_facebook']) && WOO_SLG_FB_APP_ID != '' && WOO_SLG_FB_APP_SECRET != '' ) {

			wp_deregister_script( 'facebook' );
			wp_register_script( 'facebook', $suffix . 'connect.facebook.net/' . $woo_slg_options['woo_slg_fb_language'] . '/all.js#xfbml=1&appId=' . WOO_SLG_FB_APP_ID, false, WOO_SLG_VERSION );
		}

		if( ! empty($woo_slg_options['woo_slg_enable_amazon']) && WOO_SLG_AMAZON_CLIENT_ID != '' && WOO_SLG_AMAZON_CLIENT_SECRET != '' ) {
			wp_deregister_script( 'amazon' );

			// Downloaded from https://api-cdn.amazon.com/sdk/login1.js
			wp_register_script( 'amazon', esc_url(WOO_SLG_URL) . 'includes/js/sdk/amazon/login1.js' );
		}

		//if there is no authentication data entered in settings page then so error
		$fberror = $gperror = $lierror = $twerror = $yherror = $fserror = $wlerror = $vkerror = $amazonerror = $paypalerror = $lineerror = $appleerror = '';
		if( WOO_SLG_FB_APP_ID == '' || WOO_SLG_FB_APP_SECRET == '' ) {
			$fberror = '1';
		}
		if( WOO_SLG_GP_CLIENT_ID == '' ) {
			$gperror = '1';
		}
		if( WOO_SLG_LI_APP_ID == '' || WOO_SLG_LI_APP_SECRET == '' ) {
			$lierror = '1';
		}
		if( WOO_SLG_TW_CONSUMER_KEY == '' || WOO_SLG_TW_CONSUMER_SECRET == '' ) {
			$twerror = '1';
		}
		if( WOO_SLG_YH_CONSUMER_KEY == '' || WOO_SLG_YH_CONSUMER_SECRET == '' ) {
			$yherror = '1';
		}
		if( WOO_SLG_FS_CLIENT_ID == '' || WOO_SLG_FS_CLIENT_SECRET == '' ) {
			$fserror = '1';
		}
		if( WOO_SLG_WL_CLIENT_ID == '' || WOO_SLG_WL_CLIENT_SECRET == '' ) {
			$wlerror = '1';
		}
		if( WOO_SLG_VK_APP_ID == '' || WOO_SLG_VK_APP_SECRET == '' ) {
			$vkerror = '1';
		}
		if( WOO_SLG_AMAZON_CLIENT_ID == '' || WOO_SLG_AMAZON_CLIENT_SECRET == '' ) {
			$amazonerror = '1';
		}
		if( WOO_SLG_PAYPAL_CLIENT_ID == '' || WOO_SLG_PAYPAL_CLIENT_SECRET == '' ) {
			$paypalerror = '1';
		}
		if( WOO_SLG_LINE_CLIENT_ID == '' || WOO_SLG_LINE_CLIENT_SECRET == '' ) {
			$lineerror = '1';
		}
		if( WOO_SLG_APPLE_CLIENT_ID == '' ) {
			$appleerror = '1';
		}

		//get login url
		$loginurl = wp_login_url();
		$login_array = array(
			'woo_slg_social_login' => 1,
			'wooslgnetwork' => 'twitter'
		);

		if( is_singular() && isset($post->ID) ) {
			$login_array['page_id'] = $post->ID;
		}

		$userid = '';
		if( is_user_logged_in() ) {
			$userid = get_current_user_id();
		}

		//messages
		$messages = woo_slg_messages();

		$ajax_url = 'admin-ajax.php';

		// check if WPML is active
		if( function_exists('icl_object_id') && class_exists('SitePress') ) {
		// set this code to send user notification email with current WPML language
			$ajax_url = 'admin-ajax.php?lang=' . ICL_LANGUAGE_CODE;
			$login_array['lang'] = ICL_LANGUAGE_CODE;
		}

		$ajax_url = admin_url( $ajax_url, (is_ssl() ? 'https' : 'http') );
		$loginurl = add_query_arg( $login_array, $loginurl );
		$tw_authurl = '';

		// add code to get twitter auth url for login and passed in localization
		if( WOO_SLG_TW_CONSUMER_KEY != '' && WOO_SLG_TW_CONSUMER_SECRET != '' ) {
			$tw_authurl = $this->socialtwitter->woo_slg_get_twitter_auth_url();
		}

		$woo_slg_version = WOO_SLG_VERSION;
		$caching_enable  = '';
		if( !empty($woo_slg_options['woo_slg_public_js_unique_version']) && $woo_slg_options['woo_slg_public_js_unique_version'] == 'yes' ) {
			$woo_slg_version = time();
			$caching_enable  = 'yes';
		}

		/**
		 * added since 1.9.0 for google login
		 * Downloaded from https://apis.google.com/js/api:client.js
		 */
		wp_register_script( 'woo-slg-google-api-client', esc_url(WOO_SLG_URL) . 'includes/js/sdk/google/api_client.js', array(), WOO_SLG_VERSION );
		if( $woo_slg_options['woo_slg_enable_googleplus'] == "yes" ) {
			wp_enqueue_script( 'woo-slg-google-api-client' );
		}

		wp_register_script( 'woo-slg-public-script', esc_url(WOO_SLG_URL) . 'includes/js/woo-slg-public.js', array('jquery'), $woo_slg_version, true );
		
		wp_localize_script( 'woo-slg-public-script', 'WOOSlg', array(
			'ajaxurl' => $ajax_url,
			'fbappid' => WOO_SLG_FB_APP_ID,
			'fberror' => $fberror,
			'gperror' => $gperror,
			'lierror' => $lierror,
			'twerror' => $twerror,
			'yherror' => $yherror,
			'fserror' => $fserror,
			'wlerror' => $wlerror,
			'vkerror' => $vkerror,
			'amazonerror' => $amazonerror,
			'paypalerror' => $paypalerror,
			'lineerror' => $lineerror,
			'appleerror' => $appleerror,
			'fberrormsg' => '<span>' . (isset($messages['fberrormsg']) ? $messages['fberrormsg'] : '') . '</span>',
			'gperrormsg' => '<span>' . (isset($messages['gperrormsg']) ? $messages['gperrormsg'] : '') . '</span>',
			'lierrormsg' => '<span>' . (isset($messages['lierrormsg']) ? $messages['lierrormsg'] : '') . '</span>',
			'twerrormsg' => '<span>' . (isset($messages['twerrormsg']) ? $messages['twerrormsg'] : '') . '</span>',
			'yherrormsg' => '<span>' . (isset($messages['yherrormsg']) ? $messages['yherrormsg'] : '') . '</span>',
			'fserrormsg' => '<span>' . (isset($messages['fserrormsg']) ? $messages['fserrormsg'] : '') . '</span>',
			'wlerrormsg' => '<span>' . (isset($messages['wlerrormsg']) ? $messages['wlerrormsg'] : '') . '</span>',
			'vkerrormsg' => '<span>' . (isset($messages['vkerrormsg']) ? $messages['vkerrormsg'] : '') . '</span>',
			'amazonerrormsg' => '<span>' . (isset($messages['amazonerrormsg']) ? $messages['amazonerrormsg'] : '') . '</span>',
			'paypalerrormsg' => '<span>' . (isset($messages['paypalerrormsg']) ? $messages['paypalerrormsg'] : '') . '</span>',
			'emailerrormsg' => '<span>' . (isset($messages['emailerrormsg']) ? $messages['emailerrormsg'] : '') . '</span>',
			'otperrormsg' => '<span>' . (isset($messages['otperrormsg']) ? $messages['otperrormsg'] : '') . '</span>',
			'appleerrormsg' => '<span>' . (isset($messages['appleerrormsg']) ? $messages['appleerrormsg'] : '') . '</span>',
			'lineerrormsg' => '<span>' . (isset($messages['lineerrormsg']) ? $messages['lineerrormsg'] : '') . '</span>',
			'socialloginredirect' => $loginurl,
			'userid' => $userid,
			'woo_slg_amazon_client_id' => WOO_SLG_AMAZON_CLIENT_ID,
			'tw_authurl' => $tw_authurl,
			'caching_enable' => $caching_enable,
			'google_client_id' => $woo_slg_options['woo_slg_gp_client_id']
		) );

		// unlink script
		wp_register_script( 'woo-slg-unlink-script', esc_url(WOO_SLG_URL) . 'includes/js/woo-slg-unlink.js', array('jquery'), WOO_SLG_VERSION, true );
		wp_localize_script( 'woo-slg-unlink-script', 'WOOSlgUnlink', array(
			'ajaxurl' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')),
			'confirm_msg' => esc_html__('Are you sure you want to unlink primary account?', 'wooslg')
		) );
	}

	/**
	 * Enqueue Styles
	 * Loads the css file for the front end.
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_public_styles() {
		wp_register_style( 'woo-slg-public-style', esc_url(WOO_SLG_URL) . 'includes/css/style-public.css', array(), WOO_SLG_VERSION );
		wp_enqueue_style( 'woo-slg-public-style' );
	}

	/**
	 * Register and Enqueue Script For Chart
	 * Handles to load chart scipts
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_chart_scripts( $hook_suffix ) {

		$pages_hook_suffix = array( 'toplevel_page_woo-social-login' );

		//Check pages when you needed
		if( in_array($hook_suffix, $pages_hook_suffix) ) {

			//check if site is secure then use https:// else http://
			$suffix = is_ssl() ? 'https://' : 'http://';

			wp_register_script( 'google-jsapi', esc_url(WOO_SLG_URL) . 'includes/js/google-chart/loader.js', array('jquery'), WOO_SLG_VERSION, false ); // in header
			wp_enqueue_script( 'google-jsapi' );

			wp_register_script( 'woo-slg-admin-chart-data', esc_url(WOO_SLG_URL) . 'includes/js/woo-slg-admin-chart.js', array('jquery'), WOO_SLG_VERSION, true );

			wp_enqueue_script( 'woo-slg-admin-chart-data' );
		}
	}

	/**
	 * Display button in post / page container
	 * Handles to display button in post / page container
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.1.0
	 */
	public function woo_slg_shortcode_display_button($buttons) {
		if( isset($_GET["page"]) && $_GET["page"] == "woo-social-settings" ) {
			return $buttons;	
		}
		array_push( $buttons, "|", "woo_social_login" );
		return $buttons;
	}

	/**
	 * Include js for add button in post / page container
	 * Handles to include js for add button in post / page container
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.1.0
	 */
	public function woo_slg_shortcode_button($plugin_array) {

		wp_register_script( 'woo-slg-select2-min-scripts', esc_url(WOO_SLG_URL) . 'includes/js/select2.min.js', array('jquery'), WOO_SLG_VERSION, true );
		wp_enqueue_script( 'woo-slg-select2-min-scripts' );

		$plugin_array['woo_social_login'] = esc_url(WOO_SLG_URL) . 'includes/js/woo-slg-shortcodes.js?ver=' . WOO_SLG_VERSION;
		return $plugin_array;
	}

	/**
	 * Display button in post / page container
	 * Handles to display button in post / page container
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.1.0
	 */
	public function woo_slg_add_shortcode_button() {
		if( current_user_can('manage_options') || current_user_can('edit_posts') ) {
			add_filter( 'mce_external_plugins', array($this, 'woo_slg_shortcode_button') );
			add_filter( 'mce_buttons', array($this, 'woo_slg_shortcode_display_button') );
		}
	}

	/**
	 * Add Faceook Root Div
	 * Handles to add facebook root
	 * div to page
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_fb_root() {
		echo '<div id="fb-root"></div>';
	}

	public function woo_slg_inline_style() {
		global $woo_slg_options;
		$enable_login_with_email = (isset($woo_slg_options['woo_slg_enable_email'])) ? $woo_slg_options['woo_slg_enable_email'] : '';

		if( $enable_login_with_email == 'yes' ) {
			wp_register_style( 'woo-slg-style-social', esc_url(WOO_SLG_URL) . 'includes/css/style-social.css', array(), WOO_SLG_VERSION );
			wp_enqueue_style( 'woo-slg-style-social' );
		}
	}

	/**
	 * Adding Hooks
	 * Adding proper hoocks for the scripts.
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function add_hooks() {

		//add styles for back end
		add_action( 'admin_enqueue_scripts', array($this, 'woo_slg_admin_styles'));

		//add script to back side for social login
		add_action( 'admin_enqueue_scripts', array($this, 'woo_slg_admin_scripts'));

		//add script for chart in social login
		add_action( 'admin_enqueue_scripts', array($this, 'woo_slg_chart_scripts'));

		//add script to front side for social login
		add_action( 'wp_enqueue_scripts', array($this, 'woo_slg_public_scripts'), 999);

		//add styles for front end
		add_action( 'wp_enqueue_scripts', array($this, 'woo_slg_public_styles'));

		//add styles for login page
		add_action( 'login_enqueue_scripts', array($this, 'woo_slg_public_styles') );

		//add scripts for login page
		add_action( 'login_enqueue_scripts', array($this, 'woo_slg_public_scripts'), 999 );

		// add filters for add add button in post / page container
		add_action( 'admin_init', array($this, 'woo_slg_add_shortcode_button') );

		//add facebook root div
		add_action( 'wp_footer', array($this, 'woo_slg_fb_root') );

		add_action( 'wp_enqueue_scripts', array($this, 'woo_slg_inline_style') );
	}
}