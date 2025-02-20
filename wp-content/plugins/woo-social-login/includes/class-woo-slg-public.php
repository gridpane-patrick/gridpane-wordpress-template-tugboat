<?php
// Exit if accessed directly
if( !defined('ABSPATH') ) exit;

/**
 * Public Pages Class
 * 
 * Handles all the different features and functions
 * for the front end pages.
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
class WOO_Slg_Public {

	public $render, $model;
	public $socialfacebook, $sociallinkedin, $socialtwitter;
	public $socialfoursquare, $socialyahoo, $socialwindowslive, $socialvk;
	public $socialamazon, $socialpaypal,$socialline,$socialapple;

    public function __construct() {

		global $woo_slg_render, $woo_slg_model, $woo_slg_social_facebook,
		$woo_slg_social_linkedin, $woo_slg_social_twitter, $woo_slg_social_yahoo, $woo_slg_social_foursquare,
		$woo_slg_social_windowslive, $woo_slg_social_vk, $woo_slg_social_amazon, $woo_slg_social_paypal,$woo_slg_social_line,$woo_slg_social_apple;

		$this->render = $woo_slg_render;
		$this->model = $woo_slg_model;

		//social class objects
		$this->socialfacebook = $woo_slg_social_facebook;
		$this->sociallinkedin = $woo_slg_social_linkedin;
		$this->socialtwitter = $woo_slg_social_twitter;
		$this->socialyahoo = $woo_slg_social_yahoo;
		$this->socialfoursquare = $woo_slg_social_foursquare;
		$this->socialwindowslive = $woo_slg_social_windowslive;
		$this->socialvk = $woo_slg_social_vk;
		$this->socialamazon = $woo_slg_social_amazon;
		$this->socialpaypal = $woo_slg_social_paypal;
		$this->socialline   = $woo_slg_social_line;
		$this->socialapple   = $woo_slg_social_apple;
    }

    /**
     * AJAX Call
     * 
     * Handles to Call ajax for register user
     * ( Modified in version 1.3.0 )
     * 
     * @package WooCommerce - Social Login
     * @since 1.0.0
     */
    public function woo_slg_social_login() {

        global $woo_slg_options;
        if( isset($_REQUEST['wooslg']) && $_REQUEST['wooslg'] == 'apple' ) {
            $type = 'apple';
        } else {
            $type = isset( $_POST['type'] ) ? $this->model->woo_slg_escape_slashes_deep($_POST['type']) : '';
        }

        $result = array();
        $data = array();
        $usercreated = 0;

        //created user who will connect via facebook
        if( $type == 'facebook' ) {

            $userid = $this->socialfacebook->woo_slg_get_fb_user();
            
            //if user id is null then return
            if( empty($userid) ) return;

            $userdata = $this->socialfacebook->woo_slg_get_fb_userdata( $userid );

            //check permission data user given to application
            $permData = $this->socialfacebook->woo_slg_check_fb_app_permission( 'email' );

            //if user not give the permission to api and user type is facebook then it will redirected
            if( empty($permData) ) { 
                $result['redirect'] = '1';
                echo json_encode($result);
                //do exit to get proper result
                exit;
            }

            //check facebook user data is not empty or check social identifire for register without email
            //check isset user email from facebook 
            if( !empty($userdata) && isset($userdata['email']) || isset($userdata['id']) ) {
                $data = $this->model->woo_slg_get_user_common_social_data( $userdata, $type );
            }
        } elseif( $type == 'googleplus' ) {
            $gp_userdata = isset( $_POST["gp_userdata"] ) ? $this->model->woo_slg_escape_slashes_deep( $_POST["gp_userdata"] ) : array();
            if( !empty($gp_userdata) ) {
                $data = $this->model->woo_slg_get_user_common_social_data( $gp_userdata, $type );
            }
        } elseif( $type == 'linkedin' ) {
            $li_userdata = $this->sociallinkedin->woo_slg_get_linkedin_user_data();
            if( !empty($li_userdata['emailAddress']) ) {
                $data = $this->model->woo_slg_get_user_common_social_data( $li_userdata, $type );
            }
        } elseif( $type == 'yahoo' ) {

            $yh_userdata = $this->socialyahoo->woo_slg_get_yahoo_user_data();
            
            if( !empty($yh_userdata) ) {
                if( !empty($yh_userdata['email']) ) {
                    $data = $this->model->woo_slg_get_user_common_social_data( $yh_userdata, $type );
                }
            }
        } elseif( $type == 'foursquare' ) { //check type is four squere
            $fs_userdata = $this->socialfoursquare->woo_slg_get_foursquare_user_data();
            if( !empty($fs_userdata) ) {
                $data = $this->model->woo_slg_get_user_common_social_data( $fs_userdata, $type );
            }
        } elseif( $type == 'windowslive' ) { //check type is four squere

            $wl_userdata = $this->socialwindowslive->woo_slg_get_windowslive_user_data();

            //check windowslive user data is not empty
            if( !empty($wl_userdata) ) {
                $wlemail = isset($wl_userdata->emails->preferred) ? $wl_userdata->emails->preferred : $wl_userdata->emails->account;
                $wl_userdata->wlemail = $wlemail;
                $data = $this->model->woo_slg_get_user_common_social_data( $wl_userdata, $type );
            }
        } elseif( $type == 'vk' ) { //check type is vk
            $vk_userdata = $this->socialvk->woo_slg_get_vk_user_data();
            if( !empty($vk_userdata) ) {
                $data = $this->model->woo_slg_get_user_common_social_data( $vk_userdata, $type );
            }
        } elseif( $type == 'twitter' ) { //check type is twitter
            $tw_userdata = $this->socialtwitter->woo_slg_get_twitter_user_data();

            //check user id is set or not for twitter
            if( !empty($tw_userdata) && isset($tw_userdata->id) && !empty($tw_userdata->id) ) {
                $data = $this->model->woo_slg_get_user_common_social_data( $tw_userdata, $type );
            }
        } elseif( $type == 'amazon' ) { //check type is amazon

            $amazon_userdata = $this->socialamazon->woo_slg_get_amazon_user_data();
            //check user id is set or not for amazon
            if( !empty($amazon_userdata) && !empty($amazon_userdata->user_id) ) {
                $data = $this->model->woo_slg_get_user_common_social_data( $amazon_userdata, $type );
            }
        } elseif( $type == 'paypal' ) { //check type is paypal

            $paypal_userdata = $this->socialpaypal->woo_slg_get_paypal_user_data();

            //check user id is set or not for paypal
            if( !empty($paypal_userdata) && !empty($paypal_userdata->user_id) ) {
                $data = $this->model->woo_slg_get_user_common_social_data( $paypal_userdata, $type );
            }
        } elseif( $type == 'line' ) { //check type is Line

            $line_userdata = $this->socialline->woo_slg_get_line_user_data();

            if( !empty($line_userdata) ) { //check windowslive user data is not empty
                $data = $this->model->woo_slg_get_user_common_social_data( $line_userdata, $type );
            }
        }elseif( $type == 'apple' ) { //check type is Apple

            $apple_userdata = $this->socialapple->woo_slg_get_apple_user_data();

            if( !empty($apple_userdata->email) ) {
                $data = $this->model->woo_slg_get_user_common_social_data( $apple_userdata, $type );
            }
        }

        //If user data is not empty
        if( !empty($data) ) {
            $result = $this->woo_slg_process_profile( $data );
        }

        if( !is_user_logged_in() ) { //do action when user successfully created
            do_action( 'woo_slg_social_create_user_after', $type, $usercreated );
        }

        if( isset($_REQUEST['wooslg']) && $_REQUEST['wooslg'] == 'apple' ) {

            $current_page_url = \WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_fb_redirect_url');
            $current_page_url = !empty( $current_page_url ) ? $current_page_url : home_url();
            
            $redirect_url = !empty($woo_slg_options['woo_slg_redirect_url']) ? $woo_slg_options['woo_slg_redirect_url'] : $current_page_url;

            // set no cache param
            $redirect_url = woo_slg_add_no_cache_param( $redirect_url );

            wp_redirect( $redirect_url );
        } else {
            echo json_encode( $result );
        }
        
        //do exit to get proper result
        exit;
    }

    /**
     * AJAX Call for Email Login
     * 
     * Handles to Call ajax for register user with email
     * 
     * @package WooCommerce - Social Login
     * @since 1.8.2
     */
    public function woo_slg_login_email() {

        global $woo_slg_options, $woo_slg_model;

        if( isset($_POST['email']) && !empty($_POST['email']) ) {

            $resendit = isset( $_POST['resendit'] ) ? $this->model->woo_slg_escape_slashes_deep( $_POST['resendit'] ) : '';
            $email = $this->model->woo_slg_escape_slashes_deep( $_POST['email'] );
            $response = array( 'message' => '' );
            $admin_roles = woo_slg_assigned_admin_roles();
            $user_flag = true;

            // If user email not exist
            if( email_exists($email) == false ) {

            	// true param to indetify login via email method
                $user_id = $woo_slg_model->woo_slg_add_wp_user( array('email' => $email), true );
                $user_flag = false;
                if( $user_id ) {
                    update_user_meta( $user_id, 'woo_slg_social_user_connect_via', 'email' );
                }
            } else {

                $user = get_user_by('email', $email);
                $_woo_slg_user_activate_meta = get_user_meta($user->data->ID, "_woo_slg_user_activate", true);

                if( !empty($_woo_slg_user_activate_meta) && $_woo_slg_user_activate_meta == "not_activated" ) {
                    $user_id = $woo_slg_model->woo_slg_resend_mail( array('email' => $email) );
                }
            }

            // Check user exist then login
            $user = get_user_by( 'email', $email );

            if( !empty($user) ) {
                $user_roles = $user->roles;
                if( !empty(array_intersect($user_roles, $admin_roles)) ) {
                    $response['error'] = 1;
                    $response['message'] = esc_html__('You can\'t login with admin email for security reasons.', 'wooslg');
                } else {

                    // get email varification setting from email tabs
                    $woo_slg_enable_email_varification = get_option( 'woo_slg_enable_email_varification' );
                    $woo_slg_enable_email_otp_varification = get_option( 'woo_slg_enable_email_otp_varification' );

                    wp_clear_auth_cookie();

                    if( !empty($woo_slg_enable_email_varification) && $woo_slg_enable_email_varification == 'yes' ) {
                        $_woo_slg_user_activate_meta = get_user_meta( $user->data->ID, "_woo_slg_user_activate", true );

                        if( !empty($_woo_slg_user_activate_meta) && $_woo_slg_user_activate_meta == "not_activated" ) {

                            $response['success'] = 1;
                            $response['woo_slg_email_verification'] = $woo_slg_options['woo_slg_enable_email_varification'];

                            if( isset($resendit) && $resendit == '1' ) {
                                $response['message'] = esc_html__( 'A verification link has been resent to your email account. Please verify your email.', 'wooslg' );
                            } else {
                                $response['message'] = sprintf( esc_html__('A verification link has been sent to your email account. Please verify your email. Did not receive? %s Resend it %s', 'wooslg'), '<a href="javascript:;" onclick="document.getElementById(\'woo-slg-email-login-btn-resend\').click();">', '</a>' );
                            }
                        } elseif( !empty($_woo_slg_user_activate_meta) && $_woo_slg_user_activate_meta == "activated" ) {

                            if( !empty($woo_slg_enable_email_otp_varification) && $woo_slg_enable_email_otp_varification == 'yes' ) {

                                $woo_slg_confirmation_otp = mt_rand( 100000, 999999 );
                                //$woo_slg_admin_email = get_option( 'admin_email' );
                                $woo_slg_mail_otp_subject = get_option( 'woo_slg_mail_otp_subject' );
                                $woo_slg_mail_otp_content = get_option( 'woo_slg_mail_otp_content' );

                                $woo_slg_mail_otp_content = apply_filters( 'the_content', wp_kses_post(trim($woo_slg_mail_otp_content)) );

                                update_user_meta( $user->data->ID, "_woo_slg_activation_code", $woo_slg_confirmation_otp );

                                $search_arr		= array( '{otp}', '{site_title}');
                                $replace_arr	= array( $woo_slg_confirmation_otp , get_option('blogname') );

                                $woo_slg_mail_content = str_replace( $search_arr, $replace_arr, $woo_slg_mail_otp_content );
                                $woo_slg_mail_html = wp_specialchars_decode( $woo_slg_mail_content );

                                $woo_slg_mail_otp_subject = str_replace( $search_arr, $replace_arr, $woo_slg_mail_otp_subject );

                                $headers = array();
                                $headers[] = "MIME-Version: 1.0" . "\r\n";
                                $headers[] = "Content-type:text/html;charset=UTF-8" . "\r\n";
                                wp_mail( $user->data->user_email, $woo_slg_mail_otp_subject, $woo_slg_mail_html, $headers );

                                $response['success'] = 1;
                                $response['woo_slg_enable_email_otp_varification'] = $woo_slg_options['woo_slg_enable_email_otp_varification'];

                                if( isset($resendit) && $resendit == '1' ) {
                                    $response['message'] = esc_html__( 'OTP has been resent to your email account. Please verify the OTP from your email.', 'wooslg' );
                                } else {
                                    $response['message'] = sprintf( esc_html__('OTP has been sent to your email account. Please verify the OTP from your email. Did not receive? %s Resend it %s', 'wooslg'), '<a href="javascript:;" onclick="document.getElementById(\'woo-slg-email-login-btn-resend\').click();">', '</a>' );
                                }
                            } else {
                                wp_set_current_user( $user->ID );
                                wp_set_auth_cookie( $user->ID );
                                $response['success'] = 1;
                                $response['woo_slg_email_verification'] = $woo_slg_options['woo_slg_enable_email_varification'];
                            }
                        } else {
                            if( !empty($woo_slg_enable_email_otp_varification) && $woo_slg_enable_email_otp_varification == 'yes' ) {

                                $woo_slg_confirmation_otp = mt_rand( 100000, 999999 );
                                $woo_slg_mail_otp_subject = get_option( 'woo_slg_mail_otp_subject' );
                                $woo_slg_mail_otp_content = get_option( 'woo_slg_mail_otp_content' );

                                $woo_slg_mail_otp_content = apply_filters( 'the_content', wp_kses_post(trim($woo_slg_mail_otp_content)) );
                                
                                update_user_meta( $user->data->ID, "_woo_slg_activation_code", $woo_slg_confirmation_otp );

                                $search_arr		= array( '{otp}', '{site_title}');
                                $replace_arr	= array( $woo_slg_confirmation_otp , get_option('blogname') );

                                $woo_slg_mail_content = str_replace( $search_arr, $replace_arr, $woo_slg_mail_otp_content );
                                $woo_slg_mail_html = wp_specialchars_decode( $woo_slg_mail_content );

                                $woo_slg_mail_otp_subject = str_replace( $search_arr, $replace_arr, $woo_slg_mail_otp_subject );

                                $headers = array();
                                $headers[] = "MIME-Version: 1.0" . "\r\n";
                                $headers[] = "Content-type:text/html;charset=UTF-8" . "\r\n";
                                wp_mail( $user->data->user_email, $woo_slg_mail_otp_subject, $woo_slg_mail_html, $headers );

                                $response['success'] = 1;
                                $response['woo_slg_enable_email_otp_varification'] = $woo_slg_options['woo_slg_enable_email_otp_varification'];

                                if( isset($resendit) && $resendit == '1' ) {
                                    $response['message'] = esc_html__( 'OTP has been sent to your email account. Please verify the OTP from your email.', 'wooslg' );
                                }else{
                                    $response['message'] = sprintf( esc_html__('OTP has been sent to your email account. Please verify the OTP from your email. Did not receive? %s Resend it %s', 'wooslg'), '<a href="javascript:;" onclick="document.getElementById(\'woo-slg-email-login-btn-resend\').click();">', '</a>' );
                                }
                            } else {
                                
                                wp_set_current_user( $user->ID );
                                wp_set_auth_cookie( $user->ID );

                                $response['success'] = 1;
                                $response['woo_slg_email_verification'] = $woo_slg_options['woo_slg_enable_email_varification'];
                            }
                        }
                    } else {

                        wp_set_current_user( $user->ID );
                        wp_set_auth_cookie( $user->ID );

                        $response['success'] = 1;
                        $response['woo_slg_email_verification'] = $woo_slg_options['woo_slg_enable_email_varification'];
                    }

                }
            }

            echo json_encode( $response );
            //do exit to get proper response
            exit;
        }
    }

    /**
     * AJAX Call for OTP Email Login
     * Handles to Call ajax for register user with OTP email
     * 
     * @package WooCommerce - Social Login
     * @since 1.8.2
     */
    public function woo_slg_login_otp_email() {

        global $woo_slg_options, $woo_slg_model,$wpdb;

        if( isset($_POST['otp']) && !empty($_POST['otp']) ) {

            $otp = $this->model->woo_slg_escape_slashes_deep( $_POST['otp'] );
            $response = array( 'message' => '' );
            $admin_roles = woo_slg_assigned_admin_roles();

            // If user email not exist
            $user_id = $wpdb->get_var( 'SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE ( meta_key = "_woo_slg_activation_code" AND meta_value = "'.esc_attr($otp).'" )' );

            if( $user_id ) {
                $user = get_user_by( 'id', $user_id );
                $actual_otp = get_user_meta( $user_id, '_woo_slg_activation_code', true );

                if( trim($actual_otp) == trim($otp) ) {
                    
                    wp_set_current_user( $user_id );
                    wp_set_auth_cookie( $user_id );
                    
                    $response['success'] = 1;
                    $response['message'] = esc_html__( 'OTP verified successfully.', 'wooslg' );
                } else {
                    $response['error'] = 1;
                    $response['message'] = esc_html__( 'OTP is incorrect.', 'wooslg' );
                }
            } else {
                $response['error'] = 1;
                $response['message'] = esc_html__( 'OTP is incorrect.', 'wooslg' );
            }

            echo json_encode( $response );
            //do exit to get proper response
            exit;
        }
    }

    /**
     * Confirm User using confirmation link
     * 
     * @package WooCommerce - Social Login
     * @since 1.8.5
     */
    public function woo_slg_confirm_email_user() {
        global $woo_slg_options;

        if( !empty($_GET["woo_slg_verify"]) ) {

            $woo_slg_verify = $this->model->woo_slg_escape_slashes_deep( $_GET['woo_slg_verify'] );
            $woo_slg_verify_array = unserialize( base64_decode($woo_slg_verify) );

            if( !empty($woo_slg_verify_array['id']) && !empty($woo_slg_verify_array["code"]) ) {

                $stored_code = get_user_meta($woo_slg_verify_array['id'], '_woo_slg_activation_code', true);
                if( !empty($stored_code) && $stored_code == $woo_slg_verify_array["code"] ) {
                    
					update_user_meta( $woo_slg_verify_array['id'], '_woo_slg_user_activate', "activated" );
					wp_set_current_user( $woo_slg_verify_array['id'] );
					wp_set_auth_cookie( $woo_slg_verify_array['id'] );

                    $redirect_url = woo_slg_get_redirection_url();

                    wp_redirect( $redirect_url );
                    exit;
                }
            }
        }
    }

    /**
     * Process Profile
     * Handles to process social profile
     * 
     * @package WooCommerce - Social Login
     * @since 1.3.0
     */
    public function woo_slg_process_profile( $data = array() ) {

    	global $wpdb, $woo_slg_options;

    	$user = null;
    	$new_customer = false;
    	$found_via = null;

    	$message = woo_slg_messages();

    	if( !empty($data) && !empty($data['type']) ) {

            //social provider type
    		$type = isset( $data['type'] ) ? esc_attr( $data['type'] ) : '';
    		$identifier = isset( $data['id'] ) ? esc_attr( $data['id'] ) : '';
    		
    		$user_id = '';

            // First, try to identify user based on the social identifier
    		if( !empty($identifier) ) {
    			$user_id = $wpdb->get_var( 'SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE ( meta_key = "woo_slg_social_' . $type . '_identifier" AND meta_value = "'.$identifier.'" || meta_key = "woo_slg_social_identifier" AND meta_value = "'.$identifier.'")' );
    		}

    		if( $user_id ) {
    			$user = get_user_by( 'id', $user_id );
    			$found_via = 'social_identifier';
    		}

            // Fall back to email - user may already have an account on WooCommerce with the same email as in their social profile
    		if( !$user && !empty($data['email']) ) {
    			$user = get_user_by( 'email', $data['email'] );
    			$found_via = 'email';
    		}

    		// check if user loggedin
            if( is_user_logged_in() ) { // If a user is already logged in

                // check that the logged in user and found user are the same.
                // This happens when user is linking a new social profile to their account.
            	if( $user && get_current_user_id() !== $user->ID ) {
            		if( $found_via == 'social_identifier' ) {
            			$already_linked_error = isset($message['already_linked_error']) ? $message['already_linked_error'] : '';
            			if( class_exists('Woocommerce') ) {
            				return wc_add_notice($already_linked_error, 'error');
            			} else{
            				return $already_linked_error;
            			}
            		} else {
            			$account_exist_error = isset($message['account_exist_error']) ? $message['account_exist_error'] : '';
            			if( class_exists('Woocommerce') ) {
            				return wc_add_notice( $account_exist_error, 'error' );
            			} else {
            				return $account_exist_error;	
            			}
            		}
            	}

				// If the social profile is not linked to any user accounts,
				// use the currently logged in user as the customer
            	if( ! $user ) {
            		$user = get_user_by( 'id', get_current_user_id() );
            	}
            }

			if( ! $user ) { // If no user was found, create one
				$user_id = $this->woo_slg_add_user($data);
				$user = get_user_by('id', $user_id);

				// indicate that a new user was created
				$new_customer = true;
			}

			// Update customer's WP user profile and billing details
			$this->woo_slg_update_customer_profile( $user->ID, $data, $new_customer );

			if( $type == 'facebook' ) {
                global $woo_slg_persistant_anonymous;
                $sess_id = $woo_slg_persistant_anonymous->getSessionID();
				delete_site_transient( 'facebook_at_'.$sess_id );
				delete_site_transient( 'facebook_display' );
			}

            if( ! is_user_logged_in() ) { // Log user in or add account linked notice for a logged in user
            	wp_set_auth_cookie( $user->ID );

                //update last login with social account
            	woo_slg_update_social_last_login_timestamp( $user->ID, $type );

            	do_action( 'woo_slg_login_user_authenticated', $user->ID, $type );
            } else {
            	if( class_exists('Woocommerce') ) {
            		wc_add_notice( sprintf(esc_html__('Your %s account is now linked to your account.', 'wooslg'), $type), 'notice' );
            	}
            }
            return true;
        } else {
        	return false;
        }
    }

    /**
     * Update customer's social profiles
     * Handles to update customer's social profiles
     * 
     * @package WooCommerce - Social Login
     * @since 1.3.0
     */
    public function woo_slg_update_customer_profile( $wp_id = '', $wp_user_data = array(), $new_customer = false ) {
        
        if ($wp_id > 0 && isset( $wp_user_data['type'] ) && !empty($wp_user_data['type'])) { //check wordpress user id is greater then zero
            //type of social account
            $type = $wp_user_data['type'];

            //If new customer is created
            if( $new_customer ) {
                //social data update
                update_user_meta( $wp_id, 'woo_slg_social_data', $wp_user_data['all'] );
                if( isset($wp_user_data['id']) ) {
                    update_user_meta( $wp_id, 'woo_slg_social_identifier', $wp_user_data['id'] );
                } else {
                    update_user_meta( $wp_id, 'woo_slg_social_identifier', $wp_user_data );
                }
                update_user_meta( $wp_id, 'woo_slg_social_user_connect_via', $wp_user_data['type'] );

                // Updating billing information
                update_user_meta( $wp_id, 'billing_first_name', $wp_user_data['first_name'] );
                update_user_meta( $wp_id, 'billing_last_name', $wp_user_data['last_name'] );

                // Updating shipping information
                update_user_meta( $wp_id, 'shipping_first_name', $wp_user_data['first_name'] );
                update_user_meta( $wp_id, 'shipping_last_name', $wp_user_data['last_name'] );

                $wpuserdetails = array(
                    'ID' => $wp_id,
                    'user_url' => isset($wp_user_data['link']) ? $wp_user_data['link'] : '',
                    'first_name' => isset($wp_user_data['first_name']) ? $wp_user_data['first_name'] : '',
                    'last_name' => isset($wp_user_data['last_name']) ? $wp_user_data['last_name'] : '',
                    'nickname' => isset($wp_user_data['name']) ? $wp_user_data['name'] : '',
                    'display_name' => isset($wp_user_data['name']) ? $wp_user_data['name'] : ''
                );

                wp_update_user( $wpuserdetails );
            } else {

                $primary = get_user_meta( $wp_id, 'woo_slg_social_user_connect_via', true );
                $secondary = get_user_meta( $wp_id, 'woo_slg_social_' . $type . '_identifier', true );

                if( $primary != $type && $secondary != $type ) {

                    $user_data_id = isset( $wp_user_data['id'] ) ? $wp_user_data['id'] : '';

                    update_user_meta( $wp_id, 'woo_slg_social_' . $type . '_data', $wp_user_data['all'] );
                    update_user_meta( $wp_id, 'woo_slg_social_' . $type . '_identifier', $user_data_id );
                }
            }

            // get primary login account
            $primary_acc = get_user_meta( $wp_id, 'woo_slg_social_user_connect_via', true );

            // check if user login via secondary account
            if( $primary_acc != $type ) {
                return true;
            }

            do_action( 'woo_slg_after_update_customer_profile', $wp_id, $wp_user_data, $new_customer );
        }
    }

    /**
     * Add User
     * Handles to Add user to wordpress database
     * 
     * @package WooCommerce - Social Login
     * @since 1.0.0
     */
    public function woo_slg_add_user( $userdata = array() ) {

        // register a new WordPress user
        $wp_user_data = array();
        $wp_user_data['name'] = isset( $userdata['name'] ) ? $userdata['name'] : '';
        $wp_user_data['first_name'] = isset( $userdata['first_name'] ) ? $userdata['first_name'] : '';
        $wp_user_data['last_name'] = isset( $userdata['last_name'] ) ? $userdata['last_name'] : '';
        $wp_user_data['email'] = (!empty($userdata['email'])) ? $userdata['email'] : '';

        // added for vk.com
        $wp_user_data['id'] = isset( $userdata['id'] ) ? $userdata['id'] : '';
        $wp_user_data['type'] = isset( $userdata['type'] ) ? $userdata['type'] : '';

        $wp_id = $this->model->woo_slg_add_wp_user($wp_user_data);

        return $wp_id;
    }

    /**
     * Load Login Page For Social
     * 
     * Handles to load login page for social
     * when no email address found
     * 
     * @package WooCommerce - Social Login
     * @since 1.0.0
     */
    public function woo_slg_social_login_redirect() {

        global $woo_slg_options;

        $socialtype = isset($_GET['wooslgnetwork']) ? $this->model->woo_slg_escape_slashes_deep( $_GET['wooslgnetwork'] ) : '';

        //get all social networks
        $allsocialtypes = woo_slg_social_networks();

        if( !is_user_logged_in() && isset($_GET['woo_slg_social_login']) && !empty($socialtype) && array_key_exists($socialtype, $allsocialtypes) ) {

            // get redirect url from shortcode
            $stcd_redirect_url = \WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_stcd_redirect_url');
            $stcd_redirect_url = !empty( $stcd_redirect_url ) ? $stcd_redirect_url : '';

            //check button clicked from widget then redirect to widget page url
            if( isset($_GET['container']) && $_GET['container'] == 'widget' ) {
                // get redirect url from widget 
                $stcd_redirect_url = \WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_stcd_redirect_url_widget');
            }
            
            $redirect_url = !empty($stcd_redirect_url) ? $stcd_redirect_url : woo_slg_get_redirection_url();
            $redirect_url = !empty($redirect_url) ? $redirect_url : site_url();
            $redirect_url = woo_slg_add_no_cache_param( $redirect_url );

            $data = array();

            //wordpress error class
            $errors = new WP_Error();

            switch( $socialtype ) {

                case 'twitter':
                    
                    //get twitter user data
                	$tw_userdata = $this->socialtwitter->woo_slg_get_twitter_user_data();

                    //check user id is set or not for twitter
	                if( !empty($tw_userdata) && isset($tw_userdata->id) && !empty($tw_userdata->id) ) {

	                    $data['first_name'] = isset( $tw_userdata->name ) ? $tw_userdata->name : '';
	                    $data['last_name'] = '';
	                    $data['name'] = isset( $tw_userdata->screen_name ) ? $tw_userdata->screen_name : ''; //display name of user
	                    $data['type'] = 'twitter';
	                    $data['all'] = $tw_userdata;
	                    $data['link'] = esc_url('https://twitter.com/' . $tw_userdata->screen_name);
	                    $data['id'] = isset( $tw_userdata->id ) ? $tw_userdata->id : '';
	                    $data['email'] = !empty($tw_userdata->email) ? $tw_userdata->email : '';
                    }
                    break;
                }

	            //if cart is empty or user is not logged in social media
	            //and accessing the url then send back user to checkout page
                if( !isset($data['id']) || empty($data['id']) ) {
                    if( !empty(\WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_stcd_redirect_url_widget')) ) {
                        \WSL\PersistentStorage\WOOSLGPersistent::delete('woo_slg_stcd_redirect_url_widget');
                    }

                    if( !empty(\WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_stcd_redirect_url')) ) {
                        \WSL\PersistentStorage\WOOSLGPersistent::delete('woo_slg_stcd_redirect_url');
                    }

                    wp_redirect( $redirect_url );
                    exit;
                }

	            //when user will click submit button of custom login
	            //check user clicks submit button of registration page and get parameter should be valid param
                if( !empty($_POST['woo-slg-submit']) && $_POST['woo-slg-submit'] == esc_html__('Register', 'wooslg') ) {
                    $loginurl = wp_login_url();

                if( isset($_POST['woo_slg_social_email']) ) { //check email is set or not
                    $socialemail = $this->model->woo_slg_escape_slashes_deep( $_POST['woo_slg_social_email'] );

                    if( empty($socialemail) ) { //if email is empty
                        $errors->add('empty_email', '<strong>' . esc_html__('ERROR', 'wooslg') . ' :</strong> ' . esc_html__('Enter your email address.', 'wooslg'));
                    } elseif( !is_email($socialemail) ) { //if email is not valid
                        $errors->add('invalid_email', '<strong>' . esc_html__('ERROR', 'wooslg') . ' :</strong> ' . esc_html__('The email address did not validate.', 'wooslg'));
                        $socialemail = '';
                    } elseif( email_exists($socialemail) ) {//if email is exist or not
                        $errors->add( 'email_exists', '<strong>' . esc_html__('ERROR', 'wooslg') . ' :</strong> ' . esc_html__('Email already exists, If you have an account login first.', 'wooslg') );
                    }

                    if( $errors->get_error_code() == '' ) { //
                        if( !empty($data) ) { //check user data is not empty
                            $data['email'] = $socialemail;

                            //create user
                            $this->woo_slg_process_profile($data);

                            if( !empty(\WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_stcd_redirect_url_widget')) ) {
                                \WSL\PersistentStorage\WOOSLGPersistent::delete('woo_slg_stcd_redirect_url_widget');
                            }

                            if( !empty(\WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_stcd_redirect_url')) ) {
                                \WSL\PersistentStorage\WOOSLGPersistent::delete('woo_slg_stcd_redirect_url');
                            }

                            wp_redirect( $redirect_url );
                            exit;
                        }
                    }
                }
            }

            //redirect user to custom registration form
            if( isset($_GET['woo_slg_social_login']) && !empty($_GET['woo_slg_social_login']) && empty($tw_userdata->email) ) {

                //login call back url after registration
                $socialemail = isset($_POST['woo_slg_social_email']) ? $this->model->woo_slg_escape_slashes_deep($_POST['woo_slg_social_email']) : '';

                //check the user who is going to connect with site
                //it is alreay exist with same data or not 
                //if user is exist then simply make that user logged in
                $metaquery = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'woo_slg_social_' . $data['type'] . '_identifier',
                        'value' => $data['id']
                    ),
                    array(
                        'key' => 'woo_slg_social_identifier',
                        'value' => $data['id']
                    )
                );

                $getusers = get_users( array('meta_query' => $metaquery) );
                $wpuser = array_shift( $getusers ); //getting users

                //check user is exist or not conected with same metabox
                if( !empty($wpuser) ) {

                    //make user logged in
                    wp_set_auth_cookie($wpuser->ID, false);

                    //update last login with social account
                    woo_slg_update_social_last_login_timestamp($wpuser->ID, $socialtype);

                    if( !empty(\WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_stcd_redirect_url_widget')) ) {
                        \WSL\PersistentStorage\WOOSLGPersistent::delete('woo_slg_stcd_redirect_url_widget');
                    }

                    if( !empty(\WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_stcd_redirect_url')) ) {
                        \WSL\PersistentStorage\WOOSLGPersistent::delete('woo_slg_stcd_redirect_url');
                    }

                    if( !empty($_GET['page_id']) ) {
                        $redirect_url = get_permalink( $this->model->woo_slg_escape_slashes_deep($_GET['page_id']) );
                        $redirect_url = woo_slg_add_no_cache_param( $redirect_url );
                    }

                    wp_redirect( $redirect_url );
                    exit;
                } else {

                    //if user is not exist then show register user form
                    login_header( esc_html__('Registration Form', 'wooslg'), '<p class="message register">' . esc_html__('Please enter your email address to complete registration.', 'wooslg') . '</p>', $errors ); ?>

                    <form name="registerform" id="registerform" action="" method="post">
                        <p>
                        <label for="wcsl_email"><?php esc_html_e('E-mail', 'wooslg'); ?><br />
                            <input type="text" name="woo_slg_social_email" id="woo_slg_social_email" class="input" value="<?php echo $socialemail ?>" size="25" tabindex="20" /></label>
                        </p>

                        <p id="reg_passmail">
                            <?php esc_html_e('Username and Password will be sent to your email.', 'wooslg'); ?>
                        </p><br class="clear" />

                        <p class="submit"><input type="submit" name="woo-slg-submit" id="woo-slg-submit" class="button-primary" value="<?php esc_html_e('Register', 'wooslg'); ?>" tabindex="100" /></p>
                    </form>

                    <?php
                    login_footer('user_login');
                    exit;
                }
            } elseif( !empty($tw_userdata->email) && isset($_GET['woo_slg_social_login']) && !empty($_GET['woo_slg_social_login']) ) {

            	//create user
                $this->woo_slg_process_profile($data);

                if( !empty(\WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_stcd_redirect_url_widget')) ) {
                    \WSL\PersistentStorage\WOOSLGPersistent::delete('woo_slg_stcd_redirect_url_widget');  
                }

                if( !empty(\WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_stcd_redirect_url')) ) {
                    \WSL\PersistentStorage\WOOSLGPersistent::delete('woo_slg_stcd_redirect_url');
                }

                $current_page_url = \WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_fb_redirect_url');
                $current_page_url = !empty( $current_page_url )? $current_page_url : site_url();

                $redirect_url = !empty($woo_slg_options['woo_slg_redirect_url']) ? $woo_slg_options['woo_slg_redirect_url'] : $current_page_url;

                $redirect_url = woo_slg_add_no_cache_param( $redirect_url );

                wp_redirect($redirect_url);
                exit;
            }
        }
    }

    /**
     * Handles to change avatar image if user is connected via social service
     * 
     * @package WooCommerce - Social Login
     * @since 1.1
     */
    function woo_slg_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {

        $user_id = false;
        if( is_numeric($id_or_email) ) { // If user id is there
            $user_id = $id_or_email;
        } elseif( is_object($id_or_email) ) { // If data is from comment then take user id
            if( !empty($id_or_email->user_id) ) {
                $user_id = $id_or_email->user_id;
            }
        } else {
            $user = get_user_by('email', $id_or_email);
            $user_id = isset($user->ID) ? $user->ID : '';
        }

        // Getting profile pic
        $avatar_pic = $this->model->woo_slg_get_user_profile_pic($user_id);

        if( !empty($avatar_pic) ) {
            $avatar = '<img width="' . $size . '" height="' . $size . '" class="avatar avatar-' . $size . '" src="' . esc_url($avatar_pic) . '" alt="" />';
        }

        return $avatar;
    }

    /**
     * AJAX Call
     * Handles to Call ajax for unlink  user profile
     * 
     * @package WooCommerce - Social Login
     * @since 1.3.0
     */
    public function woo_slg_social_unlink_profile() {

        //get provider
        $provider = isset($_POST['provider']) ? $this->model->woo_slg_escape_slashes_deep($_POST['provider']) : '';

        $woo_slg_profile_data = '';
        $result = array();
        $data = '';

        if( is_user_logged_in() ) {

            // Get current user login id
            $user_id = get_current_user_id();

            if( $user_id ) { //If user id exist
                if( !empty($provider) ) {
                    delete_user_meta($user_id, 'woo_slg_social_' . $provider . '_data');
                    delete_user_meta($user_id, 'woo_slg_social_' . $provider . '_identifier');

                    delete_user_meta($user_id, 'woo_slg_social_' . $provider . '_login_timestamp');
                    delete_user_meta($user_id, 'woo_slg_social_' . $provider . '_login_timestamp_gmt');
                } else { // unlink primary account from version 1.6.1
                    delete_user_meta($user_id, 'woo_slg_social_data');
                    delete_user_meta($user_id, 'woo_slg_social_identifier');
                    delete_user_meta($user_id, 'woo_slg_social_user_connect_via');
                }

                ob_start();
                $this->render->woo_slg_social_profile();
                $data = ob_get_clean();

                $messages = woo_slg_messages();
                $account_unlinked_notice = $messages['account_unlinked_notice'] ? $messages['account_unlinked_notice'] : '';

                // display notice for unlink account
                if( class_exists('Woocommerce') ) {
                    wc_add_notice(sprintf($account_unlinked_notice, ucfirst($provider)), 'notice');
                }

                $result = array(
                    'success' => 1,
                    'data' => $data
                );
            }
        }

        echo json_encode($result);
        exit;
    }

    /**
     * Process Facebook data on page load
     * This function will Save the data of facebook in database
     * 
     * @package WooCommerce Social Login
     * @since Version 1.6.4
     */
    public function woo_slg_process_facebook_data() {
        global $woo_slg_options, $woo_slg_persistant_anonymous;
        
        $sess_id = $woo_slg_persistant_anonymous->getSessionID();

        if( get_site_transient('facebook_at_'.$sess_id) ) {

            $type = "facebook";
            $usercreated = 0;
            $userdata = $this->socialfacebook->woo_slg_live_connect_user_fb_profile();

            if( $userdata ) {
                //check facebook user data is not empty or check social identifire for register without email
                if( !empty($userdata) && isset($userdata['email']) || isset($userdata['id']) ) { //check isset user email from facebook 
                    $data = $this->model->woo_slg_get_user_common_social_data($userdata, $type);
                }

                $woo_slg_wpml_lang = \WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_wpml_lang');

                // check if WPML is active
                if( function_exists('icl_object_id') && class_exists('SitePress') && !empty($woo_slg_wpml_lang) ) {

                    // set this code to send user notification email with current WPML language
                    global $sitepress;
                    $sitepress->switch_lang( $woo_slg_wpml_lang, true );
                }

                if( !empty($data) ) { //If user data is not empty
                    $result = $this->woo_slg_process_profile($data);
                }

                if( !is_user_logged_in() ) { //do action when user successfully created
                    do_action( 'woo_slg_social_create_user_after', $type, $usercreated );
                }

                $current_page_url = \WSL\PersistentStorage\WOOSLGPersistent::get('woo_slg_fb_redirect_url');
                
                $current_page_url = !empty( $current_page_url ) ? $current_page_url : woo_slg_get_redirection_url();
                
                $redirect_url = !empty( $current_page_url ) ? $current_page_url : woo_slg_add_no_cache_param( site_url() );

                wp_redirect( $redirect_url );
                exit;
            }
        }
    }

    /**
     * Allow Social login buttons to display
     * 
     * Added compatibility of following theme :
     *  - https://themeforest.net/item/workscout-job-board-wordpress-theme/13591801
     * 
     * @package WooCommerce - Social Login
     * @since 1.3.0
     */
    public function woo_slg_allw_social_login_button( $allow ) {

        //Get current theme details
        $current_theme = wp_get_theme();

        //Check current theme
        if( function_exists('workscout_setup') ) {
            return true;
        }

        return false;
    }

    /**
     * Allow Social login buttons on login page of Wordpress
     * Handle login page Social login buttons based on settings
     * 
     * @package WooCommerce - Social Login
     * @since 1.3.0
     */
    public function woo_slg_social_login_buttons_on_wp_login() {
        //add social login buttons on wordpress login page
        add_action( 'login_footer', array($this->render, 'woo_slg_social_login_buttons_on_login') );
    }

    /**
     * Allow Social login buttons on register page of Wordpress
     * Handle register page Social login buttons based on settings
     * 
     * @package WooCommerce - Social Login
     * @since 1.3.0
     */
    public function woo_slg_social_login_buttons_on_wp_register() {
        //add social login buttons on wordpress login page
        add_action( 'login_footer', array($this->render, 'woo_slg_social_login_buttons_on_login') );
    }

    /**
     * Allow new account email to admin
     * Handles to send new account email to admin
     * 
     * @package WooCommerce - Social Login
     * @since 1.5.6
     */
    public function woo_slg_send_new_account_notification_admin( $headers, $email_type, $object ) {

        // Declare prefix
        $usermeta_prefix = WOO_SLG_USER_META_PREFIX;

        $admin_email = get_option('admin_email'); // Get admin email
        $allow_user_email = get_option('woo_slg_enable_notification');
        $allow_admin_email = get_option('woo_slg_send_new_account_email_to_admin'); // Check if admin email is ticked

        /*
         * * If admin email is not empty and
         * * if email notification type is woocommerce and
         * * if admin email option is ticked and
         * * object is instance of class user
         */
        if( !empty($admin_email) && $email_type == 'customer_new_account' && !empty($allow_user_email) && $allow_user_email == 'yes' && !empty($allow_admin_email) && $allow_admin_email == 'yes' && !empty($object) && is_object($object) && ($object instanceof WP_User) ) {

            // If meta for social login is saved
            $user_via_scial_login = get_user_meta( $object->ID, $usermeta_prefix . 'by_social_login', true );
            if( !empty($user_via_scial_login) && $user_via_scial_login == 'true' ) {

                // Add admin email to BCC
                $headers .= 'Bcc: ' . $admin_email . "\r\n";
            }
        }

        return apply_filters( 'woo_slg_send_new_account_notification_admin', $headers, $object );
    }

    /**
     * Upgrade database for our new options
     * Handles to upgrade database and save new options
     * 
     * @package WooCommerce - Social Login
     * @since 1.5.6
     */
    public function woo_slg_update_options() {

        $woo_slg_email_notification_type = get_option('woo_slg_email_notification_type');

        if( empty($woo_slg_email_notification_type) ) {

            $woo_slg_enable_notification = get_option('woo_slg_enable_notification');

            update_option('woo_slg_email_notification_type', 'wordpress');
            if( !empty($woo_slg_enable_notification) && $woo_slg_enable_notification == 'yes' ) {
                update_option('woo_slg_send_new_account_email_to_admin', 'yes');
            } else {
                update_option('woo_slg_send_new_account_email_to_admin', 'no');
            }
        }
    }

    public function woo_slg_peepso_custom_social_login_buttons($test){

    }

    /**
     * Adding Hooks
     * Adding proper hoocks for the public pages.
     * 
     * @package WooCommerce - Social Login
     * @since 1.0.0
     */
    public function add_hooks() {

        global $woo_slg_options;

        // add action to show link up button on my account page
        // Login with email address
        if( $woo_slg_options['woo_slg_login_email_position'] == 'bottom' ) {
            add_action('woo_slg_wrapper_login_with_email_bottom', array($this->render, 'woo_slg_wrapper_login_with_email_content'));
        } else {
            add_action('woo_slg_wrapper_login_with_email', array($this->render, 'woo_slg_wrapper_login_with_email_content'));
        }

        // Hook to display GDPR Privacy Notice
        add_action( 'woo_slg_before_social_buttons', array($this->render, 'woo_slg_gdpr_privacy_notice') );

        //check is there any social media is enable or not
        if( woo_slg_check_social_enable() ) {

            $woo_social_order = get_option('woo_social_order');

            //Initializes Facebook API
            add_action('init', array($this->socialfacebook, 'woo_slg_initialize_facebook'));

            add_action('wp', array($this, 'woo_slg_process_facebook_data'));
            
            // add action for linkedin login
            add_action('init', array($this->sociallinkedin, 'woo_slg_initialize_linkedin'));

            // add action for twitter login
            add_action('init', array($this->socialtwitter, 'woo_slg_initialize_twitter'));

            // add action for yahoo login
            add_action('init', array($this->socialyahoo, 'woo_slg_initialize_yahoo'));

            // add action for foursquare login
            add_action('init', array($this->socialfoursquare, 'woo_slg_initialize_foursquare'));

            //add action for windows live login
            add_action('init', array($this->socialwindowslive, 'woo_slg_initialize_windowslive'));

            // add action for vk login
            add_action('init', array($this->socialvk, 'woo_slg_initialize_vk'));

            // add action for amazon login
            add_action('init', array($this->socialamazon, 'woo_slg_initialize_amazon'));

            // add action for amazon login
            add_action('init', array($this->socialpaypal, 'woo_slg_initialize_paypal'));

            //add action for line login
            add_action('init', array($this->socialline , 'woo_slg_initialize_line'));

            //add action for apple login
            add_action('init', array($this->socialapple , 'woo_slg_initialize_apple'));

            // add action for email sign up confirmation link
            add_action('template_redirect', array($this, 'woo_slg_confirm_email_user'), 99);

            //check WooCommerce is activated or not
            if( class_exists('Woocommerce') ) {

                /**
                 * Check if Buttons position is top
                 * Display buttons to the top of login and register form
                 * @since 1.8.1
                 */
                if( $woo_slg_options['woo_slg_social_btn_position'] == 'top' ) {

                    // render login buttons on the before login form new position
                    add_action('woocommerce_login_form_start', array($this->render, 'woo_slg_myaccount_social_login_buttons'));

                    // add action to show social login form at checkout page
                    add_action('woocommerce_before_template_part', array($this->render, 'woo_slg_social_login_buttons'));

                    // render login buttons on the Woocommerce before register page
                    add_action('woocommerce_register_form_start', array($this->render, 'woo_slg_on_woo_register_social_login_buttons'));

                    // optional link buttons on thank you page
                    add_action('woocommerce_before_template_part', array($this->render, 'woo_slg_maybe_render_social_link_buttons'));
                } else {

                    // render login buttons on the after login form
                    add_action('woocommerce_login_form_end', array($this->render, 'woo_slg_myaccount_social_login_buttons'));

                    // render login buttons on the Woocommerce register page
                    add_action('woocommerce_register_form_end', array($this->render, 'woo_slg_on_woo_register_social_login_buttons'));

                    // add action to show social login form at checkout page.
                    add_action('woocommerce_after_template_part', array($this->render, 'woo_slg_social_login_buttons'));

                    // optional link buttons on thank you page
                    add_action('woocommerce_after_template_part', array($this->render, 'woo_slg_maybe_render_social_link_buttons'));
                }

                // allow to add admin email in bcc
                add_filter('woocommerce_email_headers', array($this, 'woo_slg_send_new_account_notification_admin'), 10, 3);
            }

            //add action to load login page
            add_action('login_init', array($this, 'woo_slg_social_login_redirect'));

            if( !empty($woo_social_order) ) {
                $priority = 5;
                foreach( $woo_social_order as $social ) {
                    if( $social == 'email' ) {
                        continue;
                    }

                    add_action('woo_slg_checkout_social_login', array($this->render, 'woo_slg_login_' . $social), $priority);
                    $priority += 5;
                }
            }
        }

        // Filter to change the avatar image
        add_filter('get_avatar', array($this, 'woo_slg_get_avatar'), 10, 5);

        //AJAX Call to Login Via Social Media
        add_action('wp_ajax_woo_slg_social_unlink_profile', array($this, 'woo_slg_social_unlink_profile'));
        add_action('wp_ajax_nopriv_woo_slg_social_unlink_profile', array($this, 'woo_slg_social_unlink_profile'));

        //AJAX Call to unlink Via Social Media
        add_action('wp_ajax_woo_slg_social_login', array($this, 'woo_slg_social_login'));
        add_action('wp_ajax_nopriv_woo_slg_social_login', array($this, 'woo_slg_social_login'));

        //AJAX Call to login with email
        add_action('wp_ajax_woo_slg_login_email', array($this, 'woo_slg_login_email'));
        add_action('wp_ajax_nopriv_woo_slg_login_email', array($this, 'woo_slg_login_email'));

        //AJAX Call to verify OTP with Email
        add_action('wp_ajax_woo_slg_login_otp_email', array($this, 'woo_slg_login_otp_email'));
        add_action('wp_ajax_nopriv_woo_slg_login_otp_email', array($this, 'woo_slg_login_otp_email'));

        if( isset($woo_slg_options['woo_slg_enable_wp_login_page']) && !empty($woo_slg_options['woo_slg_enable_wp_login_page']) && $woo_slg_options['woo_slg_enable_wp_login_page'] == "yes" ) { // check enable login page buttons from settings
            // not check position as this only the hook for login
            add_action('login_form_login', array($this, 'woo_slg_social_login_buttons_on_wp_login'));
        }

        if( isset($woo_slg_options['woo_slg_enable_wp_register_page']) && !empty($woo_slg_options['woo_slg_enable_wp_register_page']) && $woo_slg_options['woo_slg_enable_wp_register_page'] == "yes" ) { // check enable register page buttons from settings
            // not check position as this only the hook for login
            add_action('login_form_register', array($this, 'woo_slg_social_login_buttons_on_wp_register'));
        }

        // add filter for allow social login buttons display
        add_filter('woo_slg_allow_social_logn_button', array($this, 'woo_slg_allw_social_login_button'));

        add_action('init', array($this, 'woo_slg_update_options'));

        /**
         * Check if Buttons position is custom hooks
         * Display buttons to the custom hooks added in setting
         * @since 1.8.1
         */
        if( $woo_slg_options['woo_slg_social_btn_position'] == 'hook' ) {
            $custom_hooks = $woo_slg_options['woo_slg_social_btn_hooks'];
            if( !empty($custom_hooks) ) {
                foreach( $custom_hooks as $key => $custom_hook ) {
                    add_action($custom_hook, array($this->render, 'woo_slg_social_login_buttons_on_login'));
                }
            }
        }
    }
}