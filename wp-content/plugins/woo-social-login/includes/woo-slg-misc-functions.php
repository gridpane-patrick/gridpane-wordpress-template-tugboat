<?php
// Exit if accessed directly
if( !defined('ABSPATH') ) exit;

/**
 * Misc Functions
 * 
 * All misc functions handles to
 * different functions
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */

/**
 * All Social Deals Networks
 * 
 * Handles to return all social networks
 * names
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_slg_social_networks() {

    $socialnetworks = array(
        'facebook'		=> esc_html__( 'Facebook', 'wooslg' ),
        'twitter'		=> esc_html__( 'Twitter', 'wooslg' ),
        'googleplus'	=> esc_html__( 'Google', 'wooslg' ),
        'linkedin'		=> esc_html__( 'LinkedIn', 'wooslg' ),
        'yahoo'			=> esc_html__( 'Yahoo', 'wooslg' ),
        'foursquare'	=> esc_html__( 'Foursquare', 'wooslg' ),
        'windowslive'	=> esc_html__( 'Windows Live', 'wooslg' ),
        'vk'			=> esc_html__( 'VK', 'wooslg' ),
        'amazon'		=> esc_html__( 'Amazon', 'wooslg' ),
        'paypal'		=> esc_html__( 'Paypal', 'wooslg' ),
        'email'			=> esc_html__( 'Email', 'wooslg' ),
        'line'			=> esc_html__( 'Line', 'wooslg' ),
        'apple'			=> esc_html__( 'Apple', 'wooslg' ),
    );

    return apply_filters( 'woo_slg_social_networks', $socialnetworks );
}

/**
 * Get Social Network Sorted List
 * as per saved in options
 * 
 * Handles to return social networks sorted
 * array to list in page
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_slg_get_sorted_social_network() {

    global $woo_slg_options;

    $woo_social_order = $woo_slg_options['woo_social_order']; // Get option for the sorting order stored

    $socials = woo_slg_social_networks(); // Get option for all social networks array
    // If option for ordering social netowrk is not set then return all networks
    if( !isset($woo_social_order) || empty($woo_social_order) ) {
        return $socials;
    } else { // Else check if any of the social network is not present. If not then add it to array
        if( !in_array('facebook', $woo_social_order) ) {
            $woo_social_order[] = 'facebook';
        }
        if( !in_array('twitter', $woo_social_order) ) {
            $woo_social_order[] = 'twitter';
        }
        if( !in_array('googleplus', $woo_social_order) ) {
            $woo_social_order[] = 'googleplus';
        }
        if( !in_array('linkedin', $woo_social_order) ) {
            $woo_social_order[] = 'linkedin';
        }
        if( !in_array('yahoo', $woo_social_order) ) {
            $woo_social_order[] = 'yahoo';
        }
        if( !in_array('foursquare', $woo_social_order) ) {
            $woo_social_order[] = 'foursquare';
        }
        if( !in_array('windowslive', $woo_social_order) ) {
            $woo_social_order[] = 'windowslive';
        }
        if( !in_array('vk', $woo_social_order) ) {
            $woo_social_order[] = 'vk';
        }
        if( !in_array('amazon', $woo_social_order) ) {
            $woo_social_order[] = 'amazon';
        }
        if( !in_array('paypal', $woo_social_order) ) {
            $woo_social_order[] = 'paypal';
        }
        if( !in_array('email', $woo_social_order) ) {
            $woo_social_order[] = 'email';
        }
        if( !in_array('line', $woo_social_order) ) {
            $woo_social_order[] = 'line';
        }
    }

    $sorted_socials = $woo_social_order;
    $return = array();
    for( $i = 0; $i < count($socials); $i++ ) {
        if( !empty($sorted_socials[$i]) ) {
            $return[$sorted_socials[$i]] = $socials[$sorted_socials[$i]];
        }
    }

    return apply_filters( 'woo_slg_sorted_social_networks', $return );
}

/**
 * Initialize some needed variables
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_slg_initialize() {

    global $woo_slg_options;

    //facebook variable initialization
    $fb_app_id = isset($woo_slg_options['woo_slg_fb_app_id']) ? $woo_slg_options['woo_slg_fb_app_id'] : '';
    $fb_app_secret = isset($woo_slg_options['woo_slg_fb_app_secret']) ? $woo_slg_options['woo_slg_fb_app_secret'] : '';

    if( !defined('WOO_SLG_FB_APP_ID') ) {
        define( 'WOO_SLG_FB_APP_ID', $fb_app_id );
    }
    if( !defined('WOO_SLG_FB_APP_SECRET') ) {
        define( 'WOO_SLG_FB_APP_SECRET', $fb_app_secret );
    }
    if( !defined('WOO_SLG_FB_REDIRECT_URL') ) {
        $facebookurl = add_query_arg( 'wooslg', 'facebook', site_url('/') );
        define( 'WOO_SLG_FB_REDIRECT_URL', $facebookurl );
    }

    //google+ variable initialization
    $gp_client_id = isset( $woo_slg_options['woo_slg_gp_client_id'] ) ? $woo_slg_options['woo_slg_gp_client_id'] : '';
    
    if( !defined('WOO_SLG_GP_CLIENT_ID') ) {
        define( 'WOO_SLG_GP_CLIENT_ID', $gp_client_id );
    }
    if( !defined('WOO_SLG_GP_REDIRECT_URL') ) {
        $ssl = "http://";
        if( is_ssl() ) $ssl = "https://";

        $googleurl = $ssl.$_SERVER["HTTP_HOST"];
        $googleurl = apply_filters( 'woo_slg_google_redirect_url', $googleurl, 'wooslg', 'google', site_url() ); // filter since 1.7.4

        define( 'WOO_SLG_GP_REDIRECT_URL', $googleurl );
    }

    //linkedin variable initialization
    $li_app_id = isset($woo_slg_options['woo_slg_li_app_id']) ? $woo_slg_options['woo_slg_li_app_id'] : '';
    $li_app_secret = isset($woo_slg_options['woo_slg_li_app_secret']) ? $woo_slg_options['woo_slg_li_app_secret'] : '';

    if( !defined('WOO_SLG_LI_APP_ID') ) {
        define( 'WOO_SLG_LI_APP_ID', $li_app_id );
    }
    if( !defined('WOO_SLG_LI_APP_SECRET') ) {
        define( 'WOO_SLG_LI_APP_SECRET', $li_app_secret );
    }

    // For line variable initialization
    $line_client_id = isset($woo_slg_options['woo_slg_line_client_id']) ? $woo_slg_options['woo_slg_line_client_id'] : '';
    $line_client_secret = isset($woo_slg_options['woo_slg_line_client_secret']) ? $woo_slg_options['woo_slg_line_client_secret'] : '';

    if( !defined('WOO_SLG_LINE_CLIENT_ID') ) {
        define( 'WOO_SLG_LINE_CLIENT_ID', $line_client_id );
    }
    if( !defined('WOO_SLG_LINE_CLIENT_SECRET') ) {
        define( 'WOO_SLG_LINE_CLIENT_SECRET', $line_client_secret );
    }
    if( !defined('WOO_SLG_LINE_REDIRECT_URL') ) {
        $lineurl = site_url('/');
        define( 'WOO_SLG_LINE_REDIRECT_URL', $lineurl );
    }

    // For LinkedIn Port http / https
    if( !defined('LINKEDIN_PORT_HTTP') ) { //http port value
        define( 'LINKEDIN_PORT_HTTP', '80' );
    }
    if( !defined('LINKEDIN_PORT_HTTP_SSL') ) { //ssl port value
        define( 'LINKEDIN_PORT_HTTP_SSL', '443' );
    }
    if( !defined('WOO_SLG_LI_REDIRECT_URL') ) {
        $linkedinurl = add_query_arg( 'wooslg', 'linkedin', trailingslashit(site_url()) );
        define(	'WOO_SLG_LI_REDIRECT_URL', $linkedinurl );
    }

    //twitter variable initialization
    $tw_consumer_key = isset($woo_slg_options['woo_slg_tw_consumer_key']) ? $woo_slg_options['woo_slg_tw_consumer_key'] : '';
    $tw_consumer_secrets = isset($woo_slg_options['woo_slg_tw_consumer_secret']) ? $woo_slg_options['woo_slg_tw_consumer_secret'] : '';

    if( !defined('WOO_SLG_TW_CONSUMER_KEY') ) {
        define( 'WOO_SLG_TW_CONSUMER_KEY', $tw_consumer_key );
    }
    if( !defined('WOO_SLG_TW_CONSUMER_SECRET') ) {
        define( 'WOO_SLG_TW_CONSUMER_SECRET', $tw_consumer_secrets );
    }

    //yahoo variable initialization
    $yh_consumer_key = isset($woo_slg_options['woo_slg_yh_consumer_key']) ? $woo_slg_options['woo_slg_yh_consumer_key'] : '';
    $yh_consumer_secret = isset($woo_slg_options['woo_slg_yh_consumer_secret']) ? $woo_slg_options['woo_slg_yh_consumer_secret'] : '';

    if( !defined('WOO_SLG_YH_CONSUMER_KEY') ) {
        define( 'WOO_SLG_YH_CONSUMER_KEY', $yh_consumer_key );
    }
    if( !defined('WOO_SLG_YH_CONSUMER_SECRET') ) {
        define( 'WOO_SLG_YH_CONSUMER_SECRET', $yh_consumer_secret );
    }
    if( !defined('WOO_SLG_YH_REDIRECT_URL') ) {
        $yahoourl = site_url( "/" );
        define( 'WOO_SLG_YH_REDIRECT_URL', $yahoourl );
    }

    //foursquare variable initialization
    $fs_client_id = isset($woo_slg_options['woo_slg_fs_client_id']) ? $woo_slg_options['woo_slg_fs_client_id'] : '';
    $fs_client_secrets = isset($woo_slg_options['woo_slg_fs_client_secret']) ? $woo_slg_options['woo_slg_fs_client_secret'] : '';

    if( !defined('WOO_SLG_FS_CLIENT_ID') ) {
        define( 'WOO_SLG_FS_CLIENT_ID', $fs_client_id );
    }
    if( !defined('WOO_SLG_FS_CLIENT_SECRET') ) {
        define( 'WOO_SLG_FS_CLIENT_SECRET', $fs_client_secrets );
    }
    if( !defined('WOO_SLG_FS_REDIRECT_URL') ) {
        $fsredirecturl = add_query_arg( 'wooslg', 'foursquare', site_url() );
        define( 'WOO_SLG_FS_REDIRECT_URL', $fsredirecturl );
    }

    //windows live variable initialization
    $wl_client_id = isset($woo_slg_options['woo_slg_wl_client_id']) ? $woo_slg_options['woo_slg_wl_client_id'] : '';
    $wl_client_secrets = isset($woo_slg_options['woo_slg_wl_client_secret']) ? $woo_slg_options['woo_slg_wl_client_secret'] : '';

    if( !defined('WOO_SLG_WL_CLIENT_ID') ) {
        define( 'WOO_SLG_WL_CLIENT_ID', $wl_client_id );
    }
    if( !defined('WOO_SLG_WL_CLIENT_SECRET') ) {
        define( 'WOO_SLG_WL_CLIENT_SECRET', $wl_client_secrets );
    }
    if( !defined('WOO_SLG_WL_REDIRECT_URL') ) {
        $wlredirecturl = add_query_arg( 'wooslg', 'windowslive', site_url() );
        define( 'WOO_SLG_WL_REDIRECT_URL', $wlredirecturl );
    }

    //vk variable initialization
    $vk_client_id = isset($woo_slg_options['woo_slg_vk_app_id']) ? $woo_slg_options['woo_slg_vk_app_id'] : '';
    $vk_client_secrets = isset($woo_slg_options['woo_slg_vk_app_secret']) ? $woo_slg_options['woo_slg_vk_app_secret'] : '';

    if( !defined('WOO_SLG_VK_APP_ID') ) {
        define( 'WOO_SLG_VK_APP_ID', $vk_client_id );
    }
    if( !defined('WOO_SLG_VK_APP_SECRET') ) {
        define( 'WOO_SLG_VK_APP_SECRET', $vk_client_secrets );
    }
    if( !defined('WOO_SLG_VK_REDIRECT_URL') ) {
        $vkredirecturl = add_query_arg( 'wooslg', 'vk', site_url('/') );
        define( 'WOO_SLG_VK_REDIRECT_URL', $vkredirecturl );
    }

    //  define vk variable for link
    if( !defined('WOO_SLG_VK_LINK') ) {
        $vk_link = 'https://vk.com';
        define( 'WOO_SLG_VK_LINK', $vk_link );
    }

    //Amazon variable initialization
    $amazon_client_id = isset($woo_slg_options['woo_slg_amazon_client_id']) ? $woo_slg_options['woo_slg_amazon_client_id'] : '';
    $amazon_client_secrets = isset($woo_slg_options['woo_slg_amazon_client_secret']) ? $woo_slg_options['woo_slg_amazon_client_secret'] : '';

    if( !defined('WOO_SLG_AMAZON_CLIENT_ID') ) {
        define( 'WOO_SLG_AMAZON_CLIENT_ID', $amazon_client_id );
    }
    if( !defined('WOO_SLG_AMAZON_CLIENT_SECRET') ) {
        define( 'WOO_SLG_AMAZON_CLIENT_SECRET', $amazon_client_secrets );
    }
    if( !defined('WOO_SLG_AMAZON_REDIRECT_URL') ) {
        $amazonredirecturl = add_query_arg( 'wooslg', 'amazon', site_url() );
        define( 'WOO_SLG_AMAZON_REDIRECT_URL', $amazonredirecturl );
    }

    //Payapl variable initialization
    $paypal_client_id = isset($woo_slg_options['woo_slg_paypal_client_id']) ? $woo_slg_options['woo_slg_paypal_client_id'] : '';
    $paypal_client_secrets = isset($woo_slg_options['woo_slg_paypal_client_secret']) ? $woo_slg_options['woo_slg_paypal_client_secret'] : '';
    $paypal_environment = isset($woo_slg_options['woo_slg_paypal_environment']) ? $woo_slg_options['woo_slg_paypal_environment'] : 'sandbox';

    if( !defined('WOO_SLG_PAYPAL_CLIENT_ID') ) {
        define( 'WOO_SLG_PAYPAL_CLIENT_ID', $paypal_client_id );
    }
    if( !defined('WOO_SLG_PAYPAL_CLIENT_SECRET') ) {
        define( 'WOO_SLG_PAYPAL_CLIENT_SECRET', $paypal_client_secrets );
    }
    if( !defined('WOO_SLG_PAYPAL_REDIRECT_URL') ) {
        $paypalredirecturl = add_query_arg( 'wooslg', 'paypal', site_url() );
        define( 'WOO_SLG_PAYPAL_REDIRECT_URL', $paypalredirecturl );
    }
    if( !defined('WOO_SLG_PAYPAL_ENVIRONMENT') ) {
        define( 'WOO_SLG_PAYPAL_ENVIRONMENT', $paypal_environment );
    }

    //apple variable initialization
    $apple_client_id = isset($woo_slg_options['woo_slg_apple_client_id']) ? $woo_slg_options['woo_slg_apple_client_id'] : '';

    if( !defined('WOO_SLG_APPLE_CLIENT_ID') ) {
        define( 'WOO_SLG_APPLE_CLIENT_ID', $apple_client_id );
    }
    if( !defined('WOO_SLG_APPLE_REDIRECT_URL') ) {
        $appleurl = add_query_arg( 'wooslg', 'apple', site_url('/') );
        define( 'WOO_SLG_APPLE_REDIRECT_URL', $appleurl );
    }
}

/**
 * Checkout Page URL
 * Handles to return checkout page url
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_slg_send_on_checkout_page( $queryarg = array() ) {

    global $woo_slg_options;

    $sendcheckout = get_permalink( $woo_slg_options['purchase_page'] );

    $sendcheckouturl = add_query_arg( $queryarg, $sendcheckout );
    $sendcheckouturl = woo_slg_add_no_cache_param( $sendcheckouturl );

    wp_redirect( apply_filters('woo_slg_checkout_page_redirect', $sendcheckouturl, $queryarg) );
    exit;
}

/**
 * Check Any One Social Media
 * Login is enable or not
 * 
 * Handles to Check any one social 
 * media login is enable or not
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_slg_check_social_enable() {

    global $woo_slg_options;

    $return = false;

    //check if any social is activated or not
    if( ($woo_slg_options['woo_slg_enable_facebook'] == "yes") || 
    	($woo_slg_options['woo_slg_enable_googleplus'] == "yes") ||
        ($woo_slg_options['woo_slg_enable_linkedin'] == "yes") || 
        ($woo_slg_options['woo_slg_enable_twitter'] == "yes") ||
        ($woo_slg_options['woo_slg_enable_yahoo'] == "yes") || 
        ($woo_slg_options['woo_slg_enable_windowslive'] == "yes") ||
        ($woo_slg_options['woo_slg_enable_vk'] == "yes") || 
        ($woo_slg_options['woo_slg_enable_amazon'] == "yes") || 
        ($woo_slg_options['woo_slg_enable_paypal'] == "yes") || 
        ($woo_slg_options['woo_slg_enable_email'] == "yes") || 
        ($woo_slg_options['woo_slg_enable_line'] == "yes") || 
        ($woo_slg_options['woo_slg_enable_apple'] == "yes") ) {

        $return = true;
    }

    return apply_filters( 'woo_slg_check_social_enable', $return );
}

/**
 * Current Page URL
 * 
 * @package  WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_slg_get_current_page_url() {
    $curent_page_url = remove_query_arg( array('oauth_token', 'oauth_verifier'), woo_vou_get_current_page_url() );
    return $curent_page_url;
}

/**
 * Current Page URL
 * 
 * @package  WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_vou_get_current_page_url() {

    global $post;

    if( is_front_page() ):
        $page_url = home_url();
    else:
        $page_url = 'http';
        if( isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ) $page_url .= "s";
        $page_url .= "://";

        if( $_SERVER["SERVER_PORT"] != "80" ) {
            $page_url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $page_url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
    endif;

    return apply_filters( 'woo_vou_get_current_page_url', esc_url($page_url) );
}

/**
 * Get redirection url
 *
 * Returs redirect url, when user,
 * logged in with social account
 *
 * @package  WooCommerce - Social Login
 * @since 2.2.1
 */
function woo_slg_get_redirection_url() {

	global $woo_slg_options, $pagenow, $woo_slg_model;

	$redirect_url = woo_vou_get_current_page_url();
	if( function_exists('is_checkout') && is_checkout() ) {
        $redirect_url = get_permalink( wc_get_page_id('checkout') );

    } elseif( function_exists('is_account_page') && is_account_page() ) {
        $redirect_url = get_permalink( wc_get_page_id('myaccount') );

    } elseif( $pagenow == 'wp-login.php' && !isset($_REQUEST['action']) && !empty($_GET['redirect_to']) ) {

		$admin_redirect = $woo_slg_model->woo_slg_escape_slashes_deep( $_GET['redirect_to'] );
		if( !empty($admin_redirect) ) {
			$redirect_url = urldecode( $admin_redirect );
		}
	} elseif( ! empty($woo_slg_options['woo_slg_redirect_url']) ) {
		$redirect_url = $woo_slg_options['woo_slg_redirect_url'];
	}

	$redirect_url = woo_slg_add_no_cache_param( $redirect_url );

	return apply_filters( 'woo_slg_login_redirection_url', $redirect_url );
}

/**
 * Social link buttons
 * 
 * @package  WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_slg_link_buttons( $redirect_url = '' ) {

    global $woo_slg_options;

    $can_show_container = woo_slg_can_show_all_social_link_container();

    $link_button_html = '';

    // can show container
    if( $can_show_container ) {

        // get redirect url from settings
        if( empty($redirect_url) ) $redirect_url = woo_slg_get_redirection_url();

        ob_start(); ?>

        <p><?php echo esc_html__('You can link your account to the following providers:', 'wooslg'); ?></p>
        <div class="woo-slg-social-container woo-slg-social-wrap woo-slg-social-container-checkout woo-social-link-buttons">
            <input type="hidden" class="woo-slg-redirect-url" id="woo_slg_redirect_url" value="<?php echo $redirect_url; ?>" />
            <!-- Display buttons which are not linked-->
            <?php do_action('woo_slg_checkout_social_login_link'); ?>
            <div class="woo-slg-login-error"></div>
        </div>
		
		<?php
        $link_button_html .= ob_get_clean();
    }

    echo apply_filters( 'woo_slg_link_buttons', $link_button_html );
    wp_enqueue_script( 'woo-slg-public-script' );
}

/**
 * Display Or Not On Thankyou Page
 * Handles to check wether it display on thankyou page or not
 * 
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function woo_slg_link_display_on_thankyou_page() {

    global $woo_slg_options;

    $enable = false;

    $link_on_thankyou_page = isset($woo_slg_options['woo_slg_display_link_thank_you']) ? $woo_slg_options['woo_slg_display_link_thank_you'] : '';

    if( $link_on_thankyou_page == 'yes' ) {
        $enable = true;
    }

    return apply_filters( 'woo_slg_link_display_on_thankyou_page', $enable );
}

/**
 * Display Link Buttons On MyAccount
 * Handles to display link buttons on my account page
 * 
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function woo_slg_login_display_on_myaccount_page() {

    global $woo_slg_options;

    $enable = false;
    $login_on_myaccount_page = isset($woo_slg_options['woo_slg_enable_login_page']) ? $woo_slg_options['woo_slg_enable_login_page'] : '';

    if( $login_on_myaccount_page == 'yes' && !is_checkout() ) {
        $enable = true;
    }

    return apply_filters( 'woo_slg_login_display_on_myaccount_page', $enable );
}

/**
 * Get Woocommerce Screen ID
 * Handles to get woocommerce screen id
 * 
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function woo_slg_get_wc_screen_id() {
    $wc_screen_id = sanitize_title( esc_html__('WooCommerce', 'wooslg') );
    return apply_filters( 'woo_slg_get_wc_screen_id', $wc_screen_id );
}

/**
 * Can Show Social Link
 * Handles to check this social link can show or not
 * 
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function woo_slg_can_show_social_link( $social = '' ) {

    global $current_user;

    $display = false;

    //user id not found
    $user_id = isset($current_user->ID) ? $current_user->ID : '';

    if( !empty($user_id) ) {

        //get primary social api
        $primary_social = get_user_meta( $user_id, 'woo_slg_social_user_connect_via', true );

        //get currunt social api meta
        $social_profile = get_user_meta( $user_id, 'woo_slg_social_' . $social . '_data', true );

        // check  current provider is linked or not
        if( !$social_profile && $primary_social != $social ) {
            $display = true;
        }
    }

    return apply_filters( 'woo_slg_can_display_social_link', $display, $social );
}

/**
 * Can Show Social Link Container
 * Handles to check this social link can show or not
 * 
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function woo_slg_can_show_all_social_link_container() {

    global $current_user, $woo_slg_options;

    $display = false;

    //user id not found
    $user_id = isset($current_user->ID) ? $current_user->ID : '';

    if( !empty($user_id) ) { // if user is not empty

        //get all social api in order
        $woo_social_order = get_option('woo_social_order');

        if( !empty($woo_social_order) ) {

            //profile already linked as primary account
            $primary_social = get_user_meta( $user_id, 'woo_slg_social_user_connect_via', true );

            foreach( $woo_social_order as $social ) {

                //profile already linked as secondary account
                $social_profile = get_user_meta( $user_id, 'woo_slg_social_' . $social . '_data', true );

                //if enable social account
                $enable_social = ( $woo_slg_options['woo_slg_enable_' . $social] == "yes" ) ? true : false;

                if( !$social_profile && $primary_social != $social && $enable_social ) {
                    $display = true;
                    break;
                }
            }
        }
    }

    return apply_filters( 'woo_slg_can_show_all_social_link_container', $display );
}

/**
 * Update Last Login Social Account
 * Handles to update last login social account
 * 
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function woo_slg_update_social_last_login_timestamp( $user_id, $social_type ) {

	// if user id and social type is not empty
    if( !empty($user_id) && !empty($social_type) ) {
        
        //get primary account
        $primary_social = get_user_meta( $user_id, 'woo_slg_social_user_connect_via', true );

        $timestamp = current_time( 'timestamp' );
        $timestamp_gmt = time();

        // if $social_type is primary account
        if( $primary_social == $social_type ) {
            update_user_meta( $user_id, 'woo_slg_social_login_timestamp', $timestamp );
            update_user_meta( $user_id, 'woo_slg_social_login_timestamp_gmt', $timestamp_gmt );
        } else { // If $social_type is secondary account
            update_user_meta( $user_id, 'woo_slg_social_' . $social_type . '_login_timestamp', $timestamp );
            update_user_meta( $user_id, 'woo_slg_social_' . $social_type . '_login_timestamp_gmt', $timestamp_gmt );
        }
    }
}

/**
 * Get Last Login Social Account
 * Handles to get last login social account
 * 
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function woo_slg_get_social_last_login_timestamp( $user_id, $social_type, $is_gmt = false ) {

    $social_login_timestamp = array( 'timestamp' => '', 'timestamp_gmt' => '' );

    // if user id and social type is not empty
    if( !empty($user_id) && !empty($social_type) ) {
        
        //get primary account
        $primary_social = get_user_meta( $user_id, 'woo_slg_social_user_connect_via', true );

        // if $social_type is primary account
        if( $primary_social == $social_type ) {
            $social_login_timestamp['timestamp'] = get_user_meta( $user_id, 'woo_slg_social_login_timestamp', true );
            $social_login_timestamp['timestamp_gmt'] = get_user_meta( $user_id, 'woo_slg_social_login_timestamp_gmt', true );
        } else { // If $social_type is secondary account
            $social_login_timestamp['timestamp'] = get_user_meta( $user_id, 'woo_slg_social_' . $social_type . '_login_timestamp', true );
            $social_login_timestamp['timestamp_gmt'] = get_user_meta( $user_id, 'woo_slg_social_' . $social_type . '_login_timestamp_gmt', true );
        }
    }

    $login_timestamp = ( $is_gmt ) ? $social_login_timestamp['timestamp_gmt'] : $social_login_timestamp['timestamp'];

    return apply_filters( 'woo_slg_get_social_last_login_timestamp', $login_timestamp, $user_id, $social_type, $is_gmt );
}

/**
 * Social Login Messages
 * 
 * Handles to change social login mesages
 * and links displayed at front side
 * 
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function woo_slg_messages() {

	return apply_filters( 'woo_slg_messages', array(
		'connected_link_heading'	=> esc_html__('Your account is connected to the following social login providers.', 'wooslg'),
		'no_social_connected'		=> esc_html__('You have no social login profiles connected.', 'wooslg'),
		'add_more_link'				=> esc_html__('Add More...', 'wooslg'),
		'connect_now_link'			=> esc_html__('Connect one now', 'wooslg'),
		'account_unlinked_notice'	=> esc_html__('%s account was successfully unlinked from your account.', 'wooslg'),
		'already_linked_error'		=> esc_html__('This account is already linked with another account.', 'wooslg'),
		'account_exist_error'		=> esc_html__('This account is already exist', 'wooslg'),
		'fberrormsg'	=> esc_html__('Please enter Facebook API Key & Secret in settings page.', 'wooslg'),
		'gperrormsg'	=> esc_html__('Please enter Google Client ID in settings page.', 'wooslg'),
		'lierrormsg'	=> esc_html__('Please enter LinkedIn API Key & Secret in settings page.', 'wooslg'),
		'twerrormsg'	=> esc_html__('Please enter Twitter Consumer Key & Secret in settings page.', 'wooslg'),
		'yherrormsg'	=> esc_html__('Please enter Yahoo API Consumer Key, Secret & App Id in settings page.', 'wooslg'),
		'fserrormsg'	=> esc_html__('Please enter Foursquare API Client ID & Secret in settings page.', 'wooslg'),
		'wlerrormsg'	=> esc_html__('Please enter Windows Live API Client ID & Secret in settings page.', 'wooslg'),
		'vkerrormsg'	=> esc_html__('Please enter VK API Client ID & Secret in settings page.', 'wooslg'),
		'amazonerrormsg'	=> esc_html__('Please enter Amazon API Client ID & Secret in settings page.', 'wooslg'),
		'paypalerrormsg'	=> esc_html__('Please enter Paypal API Client ID & Secret in settings page.', 'wooslg'),
		'emailerrormsg'		=> esc_html__('Please enter Vaild Email.', 'wooslg'),
		'otperrormsg'		=> esc_html__('Please enter Valid OTP.', 'wooslg'),
		'appleerrormsg'		=> esc_html__('Please enter Apple Client ID in settings page.', 'wooslg'),
		'emailverifymmsg'	=> sprintf(esc_html__( '%s Success: %s Your account has been activated!', 'wooslg'), '<strong>', '</strong>'), // added for verify user message
		'lineerrormsg'		=> esc_html__('Please enter Line API Client ID & Secret in settings page.', 'wooslg'),
	) );
}

/**
 * Insert value in array
 * Handles to add row in some array after some key
 * 
 * @package WooCommerce - Social Login
 * @since 1.4.7
 */
function woo_slg_array_insert_after( $array, $insert_key, $element ) {
    $new_array = array();
    foreach( $array as $key => $value ) {
        $new_array[$key] = $value;
        if( $insert_key == $key ) {
            foreach( $element as $k => $v ) {
                $new_array[$k] = $v;
            }
        }
    }
    return $new_array;
}

/**
 * Display Link Buttons On Woocommerc registration page
 * Handles to display link buttons on Woocommerc registration page
 * 
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function woo_slg_login_display_on_woo_register_page() {

    global $woo_slg_options;

    $enable = false;
    $login_on_woo_register_page = isset($woo_slg_options['woo_slg_enable_woo_register_page']) ? $woo_slg_options['woo_slg_enable_woo_register_page'] : '';

    if( $login_on_woo_register_page == 'yes' ) {
        $enable = true;
    }

    return apply_filters( 'woo_slg_login_display_on_woo_register_page', $enable );
}

/**
 * Get all required system information for generating system log file.
 *
 * @package WooCommerce - Social Login
 * @since 1.6.6
 */
function woo_slg_get_system_info() {

    $active_theme = wp_get_theme();

    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    require_once( ABSPATH . 'wp-admin/includes/update.php' );

    if( !function_exists('get_plugin_updates') ) {
        return array();
    }

    // Get both site plugins and network plugins
    $active_plugins = (array) get_option( 'active_plugins', array() );
    if( is_multisite() ) {
        $network_activated_plugins = array_keys(get_site_option('active_sitewide_plugins', array()));
        $active_plugins = array_merge($active_plugins, $network_activated_plugins);
    }

    $active_plugins_data = array();
    $available_updates = get_plugin_updates();

    foreach( $active_plugins as $plugin ) {
        
        $data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
        $dirname = dirname( $plugin );
        
        $version_latest = '';
        
        $slug = explode( '/', $plugin );
        $slug = explode( '.', end($slug) );
        $slug = $slug[0];

        if( 'woocommerce' !== $slug && ( strstr($data['PluginURI'], 'woothemes.com') || strstr($data['PluginURI'], 'woocommerce.com')) ) {
            if( false === ( $version_data = get_transient(md5($plugin) . '_version_data')) ) {
                $changelog = wp_safe_remote_get( esc_url('http://dzv365zjfbd8v.cloudfront.net/changelogs/' . $dirname . '/changelog.txt') );
                $cl_lines = explode("\n", wp_remote_retrieve_body($changelog));
                if( !empty($cl_lines) ) {
                    foreach( $cl_lines as $line_num => $cl_line ) {

                        if( preg_match('/^[0-9]/', $cl_line) ) {
                            $date = str_replace('.', '-', trim(substr($cl_line, 0, strpos($cl_line, '-'))));
                            $version = preg_replace('~[^0-9,.]~', '', stristr($cl_line, "version"));
                            $update = trim(str_replace("*", "", $cl_lines[$line_num + 1]));
                            $version_data = array('date' => $date, 'version' => $version, 'update' => $update, 'changelog' => $changelog);
                            set_transient(md5($plugin) . '_version_data', $version_data, DAY_IN_SECONDS);
                            break;
                        }
                    }
                }
            }

            $version_latest = $version_data['version'];
        } elseif( isset($available_updates[$plugin]->update->new_version) ) {
            $version_latest = $available_updates[$plugin]->update->new_version;
        }

        // convert plugin data to json response format.
		$system_info['plugins'][] = array(
			'plugin'			=> $plugin,
			'name'				=> $data['Name'],
			'version'			=> $data['Version'],
			'version_latest'	=> $version_latest,
			'url'				=> $data['PluginURI'],
			'author_name'		=> $data['AuthorName'],
			'network_activated'	=> $data['Network'],
		);

        $system_info['theme'] = array(
            'name'				=> $active_theme->Name,
            'version'			=> $active_theme->Version,
            'author_url'		=> esc_url_raw($active_theme->{'Author URI'}),
            'is_child_theme'	=> is_child_theme(),
        );

        $system_info['environment'] = array(
            'home_url'					=> get_option('home'),
            'wp_version'				=> get_bloginfo('version'),
            'wp_debug_mode'				=> ( defined('WP_DEBUG') && WP_DEBUG ),
            'wp_cron'					=> !( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ),
            'php_version'				=> phpversion(),
            'fsockopen_or_curl_enabled'	=> ( function_exists('fsockopen') || function_exists('curl_init') ),
        );
    }

    return $system_info;
}

/**
 * Get Voucher Admin Roles
 * 
 * @package WooCommerce - Social Login
 * @since 1.8.2
 */
function woo_slg_assigned_admin_roles() {
    return apply_filters( 'woo_slg_assigned_admin_roles', array('administrator') );
}

/**
 * Set No cache parameter to the query string
 * 
 * @package WooCommerce - Social Login
 * @since 1.8.4
 */
function woo_slg_add_no_cache_param( $url ) {
    
    global $woo_slg_options;

    if( isset($woo_slg_options['woo_slg_public_js_unique_version']) && 
    	!empty($woo_slg_options['woo_slg_public_js_unique_version']) && 
    	$woo_slg_options['woo_slg_public_js_unique_version'] == 'yes' && !empty($url) ) {

        $url = add_query_arg( array( 'no-caching' => '1'), $url );    
    }

    return $url;
}

/**
 * Check if server is wpengine
 * 
 * @package WooCommerce - Social Login
 * @since 1.8.4
 */
function woo_slg_is_wpengine(){
    if( class_exists('WpePlugin_common', false) ) {
        return true;
    }
    return false;
}

/**
 * Set cookies
 *
 * @package WooCommerce - Social Login
 * @since 1.8.4
 */
function woo_slg_set_cookie( $name, $value, $expire ){
    setcookie($name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN);
}

/**
 * Get from cookies
 *
 * @package WooCommerce - Social Login
 * @since 1.8.4
 */
function woo_slg_get_cookie( $name = '' ){
    if( isset($_COOKIE[$name]) && !empty($_COOKIE[$name]) ) {
        return $_COOKIE[$name];
    }
    return '';
}

/**
 * Delete cookie parameter
 *
 * @package WooCommerce - Social Login
 * @since 1.8.4
 */
function woo_slg_remove_cookie( $name = ''){
    if( isset($_COOKIE[$name]) && !empty($_COOKIE[$name]) ) {
        unset( $_COOKIE[$name] );
    }
}

/**
 * Instanciate the filesystem class
 *
 * @package WooCommerce - Social Login
 * @since 2.2.1
 * @return object WP_Filesystem_Direct instance
 */
function woo_slg_direct_filesystem() {
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
    return new WP_Filesystem_Direct( new StdClass() );
}