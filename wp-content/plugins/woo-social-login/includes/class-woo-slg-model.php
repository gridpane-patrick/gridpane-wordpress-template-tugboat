<?php
// Exit if accessed directly
if( !defined('ABSPATH') ) exit;

/**
 * Model Class
 * Handles generic plugin functionality.
 *
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
class WOO_Slg_Model {

	public $foursquare;

	public function __construct() {

		global $woo_slg_social_foursquare;

		$this->foursquare = $woo_slg_social_foursquare;
	}

	/**
	 * Escape Tags & Slashes
	 * Handles escapping the slashes and tags
	 *
	 * @package  WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_escape_attr( $data ) {
		return esc_attr( stripslashes($data) );
	}

	/**
	 * Strip Slashes From Array
	 *
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_escape_slashes_deep($data = array(), $flag = false) {

		if( $flag != true ) {
			$data = $this->woo_slg_nohtml_kses( $data );
		}

		$data = stripslashes_deep( $data );
		return $data;
	}

	/**
	 * Strip Html Tags
	 * It will sanitize text input (strip html tags, and escape characters)
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_nohtml_kses( $data = array() ) {

		if( is_array($data) ) {
			$data = array_map( array($this, 'woo_slg_nohtml_kses'), $data );
		} elseif( is_string($data) ) {
			$data = wp_filter_nohtml_kses( $data );
		}

		return $data;
	}

	/**
	 * Convert Object To Array
	 * Converting Object Type Data To Array Type
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_object_to_array( $result ) {
		$array = array();
		
		// check if result empty
		if( !empty($result) ) return $array;

		foreach( $result as $key => $value ) {
			if( is_object($value) ) {
				$array[$key] = $this->woo_slg_object_to_array( $value );
			} else {
				$array[$key] = $value;
			}
		}

		return $array;
	}

	/**
	 * Create User
	 *
	 * Function to add connected users to the WordPress users database
	 * and add the role subscriber
	 *
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_add_wp_user( $criteria, $is_login_with_email = false ) {

		global $wp_version, $woo_slg_options, $wp;

		// get email varification setting from email tabs
		$woo_slg_enable_email_varification = get_option( 'woo_slg_enable_email_varification' );

		$usermeta_prefix = WOO_SLG_USER_META_PREFIX;

		$username = $this->woo_slg_create_username($criteria);

		$name = isset($criteria['name']) ? $criteria['name'] : '';
		$first_name = isset($criteria['first_name']) ? $criteria['first_name'] : '';
		$last_name = isset($criteria['last_name']) ? $criteria['last_name'] : '';
		$password = wp_generate_password(12, false);
		$email = isset( $criteria['email'] ) ? $criteria['email'] : '';
		
		// code for register without email
		$criteria['username'] = $username;

		$wp_id = 0;

		//create the WordPress user
		if( version_compare($wp_version, '3.1', '<') ) {
			require_once(ABSPATH . WPINC . '/registration.php');
		}

		//check user id is exist or not
		if( $this->woo_slg_check_user_exists($criteria) == false ) {

			if( !empty($email) ) {
				$wp_id = wp_create_user( $username, $password, $email );
			} else { // code for register without email
				$wp_id = wp_create_user( $username, $password );
			}

			if( !empty($wp_id) ) { //if user is created then update some data
				$role = !empty($woo_slg_options['woo_slg_default_role']) ? $woo_slg_options['woo_slg_default_role'] : 'subscriber';

				$user = new WP_User($wp_id);
				$user->set_role($role);

				do_action( 'woo_slg_add_wp_user_after_setup_roles', $wp_id );

				// update user meta for keeping record that user is created by social login
				update_user_meta( $wp_id, $usermeta_prefix . 'by_social_login', 'true' );

				$send_email_to = '';
				if( empty($woo_slg_enable_email_varification) || $woo_slg_enable_email_varification == 'no' ) {
					if( !empty($woo_slg_options['woo_slg_enable_notification']) && $woo_slg_options['woo_slg_enable_notification'] == 'yes' && !empty($woo_slg_options['woo_slg_send_new_account_email_to_admin']) && $woo_slg_options['woo_slg_send_new_account_email_to_admin'] == 'yes' ) {

						$send_email_to = 'both';
					} elseif( !empty($woo_slg_options['woo_slg_enable_notification']) && $woo_slg_options['woo_slg_enable_notification'] == 'yes' ) {

						$send_email_to = 'user';
					} elseif( !empty($woo_slg_options['woo_slg_send_new_account_email_to_admin']) && $woo_slg_options['woo_slg_send_new_account_email_to_admin'] == 'yes' ) {

						$send_email_to = 'admin';
					}
				} elseif( $is_login_with_email ) {

					// check verify tick and admin then send mail
					if( !empty($woo_slg_enable_email_varification) && $woo_slg_enable_email_varification == 'yes' && !empty($woo_slg_options['woo_slg_send_new_account_email_to_admin']) && $woo_slg_options['woo_slg_send_new_account_email_to_admin'] == 'yes' ) {
						$send_email_to = 'both';
						$woo_slg_options['woo_slg_email_notification_type'] = "wordpress";
					} elseif( !empty($woo_slg_enable_email_varification) && $woo_slg_enable_email_varification == 'yes' ) {
						$send_email_to = 'user';
						$woo_slg_options['woo_slg_email_notification_type'] = "wordpress";
					} elseif( !empty($woo_slg_options['woo_slg_send_new_account_email_to_admin']) && $woo_slg_options['woo_slg_send_new_account_email_to_admin'] == 'yes' ) {
						$send_email_to = 'admin';
					}
				}

				if( !empty($woo_slg_options['woo_slg_email_notification_type']) ) {

					// If email option is selected as woocommerce and woocommerce class is active
					if( $woo_slg_options['woo_slg_email_notification_type'] == 'woocommerce' && class_exists('Woocommerce') && (!empty($woo_slg_options['woo_slg_enable_notification']) || !empty($woo_slg_enable_email_varification)) && ($woo_slg_options['woo_slg_enable_notification'] == 'yes' || $woo_slg_enable_email_varification == 'yes') ) {

						$new_customer_data = apply_filters( 'woo_slg_new_user_data', array(
							'user_login' => $user->data->user_login,
							'user_pass' => $password,
							'user_email' => $user->data->user_email,
							'role' => $user->roles[0],
						) );

						// Do action to send email through WooCommerce
						do_action( 'woocommerce_created_customer', $wp_id, $new_customer_data, true );
					} elseif( $woo_slg_options['woo_slg_email_notification_type'] == 'wordpress' && !empty($send_email_to) ) {
						if( (empty($woo_slg_enable_email_varification) || $woo_slg_enable_email_varification == 'no') || $is_login_with_email == false ) {
							wp_new_user_notification($wp_id, null, apply_filters('woo_slg_new_user_notify_to', $send_email_to));
						} elseif( $is_login_with_email == true && $woo_slg_enable_email_varification == 'yes' ) {

							$woo_slg_mail_subject = get_option( 'woo_slg_mail_subject' );
							$woo_slg_mail_content = get_option( 'woo_slg_mail_content' );

							$woo_slg_mail_content = apply_filters( 'the_content', wp_kses_post(trim($woo_slg_mail_content)) );

							$code = md5( time() );
							$woo_slg_confirmation_otp = mt_rand( 100000, 999999 );
							
							$current_url = isset( $_POST['current_url'] ) ? $this->woo_slg_escape_slashes_deep( $_POST['current_url'] ) : '';
							
							$woo_slg_verify_array = array(
								'id' => $user->data->ID,
								'code' => $code
							);

							$verification_url = add_query_arg( array(
								'woo_slg_verify' => base64_encode( serialize($woo_slg_verify_array) )
							), $current_url );

							update_user_meta( $user->data->ID, "_woo_slg_activation_code", $code );

							$woo_slg_confirmation_link = '<a href="' . $verification_url . '">here</a>'; 

							$woo_slg_mail_content = str_replace("{verify_link}", $woo_slg_confirmation_link, $woo_slg_mail_content);
							$woo_slg_mail_html = $woo_slg_mail_content;

							$headers = array();
							$headers[] = "MIME-Version: 1.0" . "\r\n";
							$headers[] = "Content-type:text/html;charset=UTF-8" . "\r\n";
							wp_mail( $user->data->user_email, $woo_slg_mail_subject, $woo_slg_mail_html, $headers );

							update_user_meta( $wp_id, "_woo_slg_user_activate", "not_activated" );
						}
					}
				}

				do_action( 'woo_slg_social_user_created', $wp_id, $user, $password, $criteria );
			}

			//Update unique id to usermeta
			if( !empty($criteria['id']) ) {
				update_user_meta( $wp_id, $usermeta_prefix . 'unique_id', $criteria['id'] );
			}
		} else {
			//get user from email or username
			$userdata = $this->woo_slg_get_user_by( $criteria );

			if( !empty($userdata) ) { //check user is exit or not
				$wp_id = $userdata->ID;
			}
		}
		return $wp_id;
	}

	/**
	 * Resent Mail
	 *
	 * Function to resend mail after user sign up & sign in with 
	 * email address.
	 *
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_resend_mail( $criteria ) {

		global $wp_version, $woo_slg_options;

		$email = isset( $criteria['email'] ) ? $criteria['email'] : '';
		// code for register without email

		$user = get_user_by("email", $email);

		if( !empty($user) ) {
			$woo_slg_mail_subject = get_option( 'woo_slg_mail_subject' );
			$woo_slg_mail_content = get_option( 'woo_slg_mail_content' );

			$woo_slg_mail_content = apply_filters( 'the_content', wp_kses_post(trim($woo_slg_mail_content)) );

			$code = md5( time() );
			$woo_slg_confirmation_otp = mt_rand( 100000, 999999 );
			
			$woo_slg_verify_array = array( 'id' => $user->data->ID, 'code' => $code );
			update_user_meta( $user->data->ID, "_woo_slg_activation_code", $code );
			
			$_woo_slg_user_activate_meta = get_user_meta($user->data->ID, "_woo_slg_user_activate", true);
			
			if( !empty($_woo_slg_user_activate_meta) && $_woo_slg_user_activate_meta == "not_activated" ) {

				$woo_slg_confirmation_link = '<a href="' . get_site_url() . '/?woo_slg_verify=' . base64_encode(serialize($woo_slg_verify_array)) . '">here</a>';     // creates the activation url
				$woo_slg_mail_content = str_replace("{verify_link}", $woo_slg_confirmation_link, $woo_slg_mail_content);
				$woo_slg_mail_html = $woo_slg_mail_content;

				$headers = array();

				$headers[] = "MIME-Version: 1.0" . "\r\n";
				$headers[] = "Content-type:text/html;charset=UTF-8" . "\r\n";
				wp_mail($user->data->user_email, $woo_slg_mail_subject, $woo_slg_mail_html, $headers);
			}

			return $user->data->ID;
		}
		return '';
	}

	/**
	 * Get Social Connected Users Count
	 * Handles to return connected user counts
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_social_get_users( $args = array() ) {

		$userargs = array();
		$metausr1 = array();

		if( isset($args['network']) && !empty($args['network']) ) { //check network is set or not
			$metausr1['key'] = 'woo_slg_social_user_connect_via';
			$metausr1['value'] = $args['network'];
		}

		if( !empty($metausr1) ) { //meta query
			$userargs['meta_query'] = array( $metausr1 );
		}

		//get users data
		$result = new WP_User_Query( $userargs );

		if( isset($args['getcount']) && !empty($args['getcount']) ) { //get count of users
			$users = $result->total_users;
		} else {
			//retrived data is in object format so assign that data to array for listing
			$users = $this->woo_slg_object_to_array( $users->results );
		}

		return $users;
	}

	/**
	 * Create User Name for VK.com and Instagram
	 * Function to check type is vk or instagram then create user name based on user id.
	 *
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_create_username( $criteria ) {

		global $woo_slg_options;

		$prefix = WOO_SLG_USER_PREFIX;

		//Initilize username
		$username = '';

		//Get base of username
		$woo_user_base = isset($woo_slg_options['woo_slg_base_reg_username']) ? $woo_slg_options['woo_slg_base_reg_username'] : '';

		switch( $woo_user_base ) {

			case 'realname':

				//Get first name
				$first_name = isset($criteria['first_name']) ? strtolower($criteria['first_name']) : '';
				
				//Get last name
				$last_name = isset($criteria['last_name']) ? strtolower($criteria['last_name']) : '';

				if( empty( $criteria['first_name'] ) && empty( $criteria['last_name'] ) && !empty( $criteria['name'] ) ){
					$temp_name = explode(' ', $criteria['name'] );
					if( count( $temp_name ) > 1 ){
						$first_name = $temp_name[0];
						$last_name = $temp_name[1];
					} elseif ( count( $temp_name ) == 1 ) {
						$first_name = $temp_name[0];
					}
				}

				//Get username using fname and lname
				$username = $this->woo_slg_username_by_fname_lname($first_name, $last_name);
			break;

			case 'emailbased':

				//Get user email
				$user_email = isset($criteria['email']) ? $criteria['email'] : '';

				//Create username using email
				$username = $this->woo_slg_username_by_email($user_email);
			break;

			case 'realemailbased':
				//Get user email
				$user_email = isset($criteria['email']) ? $criteria['email'] : '';

				//Create username as real email
				$username = $user_email;
			break;

			default:
			break;
		}

		//If username get empty
		if( empty($username) ) {
			// if service is vk.com OR instagram then create username with unique id
			if( $criteria['type'] == 'vk' ) {
				$username = $prefix . $criteria['id'];
			} else { // else create create username with random string
				$username = $prefix . wp_rand( 100, 9999999 );
			}
		}

		//Apply filter to modify username logic
		$username = apply_filters( 'woo_slg_social_username', $username, $criteria );

		//Assign username to temporary variable
		$temp_user_name = $username;

		//Make sure the name is unique: if we've already got a user with this name, append a number to it.
		$counter = 1;
		if( username_exists($temp_user_name) ) {
			do {
				$username = $temp_user_name;
				$counter++;
				$username = $username . $counter;
			} while( username_exists($username) );
		} else {
			$username = $temp_user_name;
		}

		return $username;
	}

	/**
	 * Check User Exists
	 *
	 * Function to check user is exists or not based on either username or email
	 * for VK and 
	 * for Instragram(only by username, because it can't contain email)
	 *
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_check_user_exists( $criteria ) {

		$prefix = WOO_SLG_USER_PREFIX;

		if( !empty($criteria['type']) && $criteria['type'] == 'vk' && empty($criteria['email']) ) {
			//return username_exists($prefix.$criteria['id']);
			return $this->woo_slg_user_meta_exists( $criteria['id'] );
		} else {
			if( !empty($criteria['email']) ) {
				return email_exists( $criteria['email'] );
			} else { // code for register without email
				return username_exists( $criteria['username'] );
			}
		}
	}

	/**
	 * Get User by email or username
	 *
	 * Function to get user by email or username
	 * for VK and 
	 * for Instragram(only by username, because it can't contain email)
	 *
	 * @package WooCommerce - Social Login
	 * @since 1.0.0
	 */
	public function woo_slg_get_user_by( $criteria ) {

		$prefix = WOO_SLG_USER_PREFIX;

		if( $criteria['type'] == 'vk' && empty($criteria['email']) ) {
			return $this->woo_slg_get_user_by_meta( $criteria['id'] );
		} else {
			if( isset($criteria['email']) ) {
				return get_user_by( 'email', $criteria['email'] );
			}
		}
	}

	/**
	 * User exist from meta
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.4
	 */
	public function woo_slg_user_meta_exists( $criteria_id = '', $only_id = true ) {

		//Usermeta prefix
		$user_meta_prefix = WOO_SLG_USER_META_PREFIX;

		$user = array();

		//Get user by meta
		$users = get_users( array(
			'meta_key' => $user_meta_prefix . 'unique_id',
			'meta_value' => $criteria_id,
			'number' => 1,
			'count_total' => false
		) );

		//If user not empty
		if( !empty($users) ) {
			$user = reset( $users );
		}

		return isset( $user->ID ) ? $user->ID : false;
	}

	/**
	 * User Data By MetaData
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.4
	 */
	public function woo_slg_get_user_by_meta( $criteria_id ) {

		//Usermeta prefix
		$user_meta_prefix = WOO_SLG_USER_META_PREFIX;

		$user = array();

		//Get user by meta
		$users = get_users( array(
			'meta_key' => $user_meta_prefix . 'unique_id',
			'meta_value' => $criteria_id,
			'number' => 1,
			'count_total' => false
		) );

		//If user not empty
		if( !empty($users) ) {
			$user = reset($users);
		}

		return apply_filters( 'woo_slg_get_user_by_meta', $user, $criteria_id );
	}

	/**
	 * Username Using Fname And Lname
	 * Handle to create username using api firstname and lastname
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.4
	 */
	public function woo_slg_username_by_fname_lname( $first_name = '', $last_name = '' ) {

		//Initilize username
		$username = '';

		//If firstname is not empty
		if( !empty($first_name) ) {
			$username .= $first_name;
		}

		//If lastname is not empty
		if( !empty($last_name) ) {
			$username .= '_' . $last_name;
		}

		return apply_filters( 'woo_slg_username_by_fname_lname', $username, $first_name, $last_name );
	}

	/**
	 * Username Using Email
	 * Handle to create username using social email address
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.0.4
	 */
	public function woo_slg_username_by_email( $user_email = '' ) {

		//Initilize username
		$username = '';

		$username = str_replace( '@', '_', $user_email );
		$username = str_replace( '.', '_', $username );

		return apply_filters( 'woo_slg_username_by_email', $username, $user_email );
	}

	/**
	 * Get User profile pic
	 * Function to get user profile pic from user meta type its social type 
	 *
	 * @package WooCommerce - Social Login
	 * @since 1.1
	 */
	public function woo_slg_get_user_profile_pic( $user_id = false ) {

		global $woo_slg_options;

		// Taking some defaults
		$profile_pic_url = '';

		// If user id is passed then take otherwise take current user
		$user_id = !empty( $user_id ) ? $user_id : '';

		if( $user_id ) {

			// Getting some user details
			$woo_slg_social_type = get_user_meta( $user_id, 'woo_slg_social_user_connect_via', true );
			$woo_slg_data = get_user_meta( $user_id, 'woo_slg_social_data', true );

			if( !empty($woo_slg_social_type) && !empty($woo_slg_data) ) {

				// If facebook avatar is enable
				if( !empty($woo_slg_options['woo_slg_enable_fb_avatar']) && $woo_slg_options['woo_slg_enable_fb_avatar'] == "yes" ) {

					// If user is from facebook
					if( $woo_slg_social_type == 'facebook' ) {
						$profile_pic_url = !empty($woo_slg_data['picture']) ? $woo_slg_data['picture'] : '';
					}
				}

				// If twitter avatar is enable
				if( isset($woo_slg_options['woo_slg_enable_tw_avatar']) && !empty($woo_slg_options['woo_slg_enable_tw_avatar']) && $woo_slg_options['woo_slg_enable_tw_avatar'] == "yes" ) {

					// If user is from twitter
					if( $woo_slg_social_type == 'twitter' ) {
						$profile_pic_url = !empty($woo_slg_data->profile_image_url_https) ? esc_url($woo_slg_data->profile_image_url_https) : '';
					}
				}

				// If google plus avatar is enable
				if( !empty($woo_slg_options['woo_slg_enable_gp_avatar']) && $woo_slg_options['woo_slg_enable_gp_avatar'] == "yes" ) {

					// If user is from googleplus
					if( $woo_slg_social_type == 'googleplus' ) {
						if( !empty($woo_slg_data->picture) ) {
							$profile_pic_url = $woo_slg_data->picture;
						} elseif( is_array($woo_slg_data) && $woo_slg_data['image']['url'] && !empty($woo_slg_data['image']['url']) ) { // Added for backward compitibility
							$profile_pic_url = $woo_slg_data['image']['url'];
						}
					}
				}

				// If linked in avatar is enable
				if( !empty($woo_slg_options['woo_slg_enable_li_avatar']) && $woo_slg_options['woo_slg_enable_li_avatar'] == "yes" ) {

					// If user is from linkedin
					if( $woo_slg_social_type == 'linkedin' ) {

						$profile_pic_url = '';

						// Added for backward compitibility
						if( !empty($woo_slg_data['picture-url']) ) {
							$profile_pic_url = $woo_slg_data['picture-url'];
						} elseif( !empty($woo_slg_data['pictureUrl']) ) {
							$profile_pic_url = $woo_slg_data['pictureUrl'];
						}
					}
				}

				// If yahoo avatar is enable
				if( !empty($woo_slg_options['woo_slg_enable_yh_avatar']) && $woo_slg_options['woo_slg_enable_yh_avatar'] == "yes" ) {

					// If user is from yahoo
					if( $woo_slg_social_type == 'yahoo' ) {
						$profile_pic_url = !empty($woo_slg_data['image']['imageUrl']) ? $woo_slg_data['image']['imageUrl'] : '';
					}
				}

				// If foursquer avatar is enable
				if( !empty($woo_slg_options['woo_slg_enable_fs_avatar']) && $woo_slg_options['woo_slg_enable_fs_avatar'] == "yes" ) {

					// If user is from foursquare
					if( $woo_slg_social_type == 'foursquare' ) {
						$profile_pic_url = $this->foursquare->woo_slg_get_foursquare_profile_picture( array('size' => '64'), $woo_slg_data );
					}
				}

				// If vk avatar is enable
				if( !empty($woo_slg_options['woo_slg_enable_vk_avatar']) && $woo_slg_options['woo_slg_enable_vk_avatar'] == "yes" ) {

					// If user is from vk
					if( $woo_slg_social_type == 'vk' ) {
						$profile_pic_url = !empty($woo_slg_data['photo_big']) ? $woo_slg_data['photo_big'] : '';
					}
				}

				// If lINE avatar is enable
				if( !empty($woo_slg_options['woo_slg_enable_line_avatar']) && $woo_slg_options['woo_slg_enable_line_avatar'] == "yes" ) {

					// If user is from facebook
					if( $woo_slg_social_type == 'line' ) {
						$profile_pic_url = !empty($woo_slg_data['picture']) ? $woo_slg_data['picture'] : '';
					}
				}
			}
		}

		return apply_filters( 'woo_slg_get_user_profile_pic', $profile_pic_url, $user_id );
	}

	/**
	 * Common Social Data Convertion
	 * 
	 * @package WooCommerce - Social Login
	 * @since 1.3.0
	 */
	public function woo_slg_get_user_common_social_data( $social_data = array(), $social_type = '' ) {

		$common_social_data = array();

		// If social type is not empty
		if( !empty($social_type) ) {
			switch( $social_type ) {

				case 'facebook':
					$common_social_data['first_name'] = isset( $social_data['first_name'] ) ? $social_data['first_name'] : '';
					$common_social_data['last_name'] = isset( $social_data['last_name'] ) ? $social_data['last_name'] : '';
					$common_social_data['name'] = isset( $social_data['name'] ) ? $social_data['name'] : '';
					$common_social_data['email'] = (isset($social_data['email'])) ? $social_data['email'] : '';
					$common_social_data['type'] = $social_type;
					$common_social_data['all'] = $social_data;
					$common_social_data['link'] = isset( $social_data['link'] ) ? $social_data['link'] : '';
					$common_social_data['id'] =  isset( $social_data['id'] ) ? $social_data['id'] : '';
				break;

				case 'googleplus':

					if( is_array($social_data) ) {
						$firstname	= isset($social_data["given_name"]) ? $social_data["given_name"] : '';
						$lastname	= isset($social_data["family_name"]) ? $social_data["family_name"] : '';
						$name		= isset($social_data["name"]) ? $social_data["name"] : '';
						$email		= isset($social_data["email"]) ? $social_data["email"] : '';
						$id			= isset($social_data["id"]) ? $social_data["id"] : '';
						$social_data['image']['url'] = isset($social_data["img_url"]) ? $social_data["img_url"] : '';
						$img_url = $social_data['image']['url'];
					} else if( is_object( $social_data ) ) {
						$firstname	= isset($social_data->given_name) ? $social_data->given_name : '';
						$lastname	= isset($social_data->family_name) ? $social_data->family_name : '';
						$name		= isset($social_data->name) ? $social_data->name : '';
						$email		= isset($social_data->email) ? $social_data->email : '';
						$id			= isset($social_data->id) ? $social_data->id : '';
						$img_url	= isset($social_data->picture) ? $social_data->picture : '';
					}

					$common_social_data['first_name']	= $firstname;
					$common_social_data['last_name']	= $lastname;
					$common_social_data['name']			= $name;
					$common_social_data['email']		= $email;
					$common_social_data['type']			= $social_type;
					$common_social_data['all']			= $social_data;
					$common_social_data['link']			= '';
					$common_social_data['id']			= $id;
					$common_social_data['image']['url']	= $img_url;
				break;

				case 'linkedin':
					$common_social_data['first_name'] = isset( $social_data['firstName'] ) ? $social_data['firstName'] : '';
					$common_social_data['last_name'] = isset( $social_data['lastName'] ) ? $social_data['lastName'] : '';
					$common_social_data['name'] = $common_social_data['first_name'] . ' ' . $common_social_data['last_name'];
					$common_social_data['email'] = isset( $social_data['emailAddress'] ) ? $social_data['emailAddress'] : '';
					$common_social_data['type'] = $social_type;
					$common_social_data['all'] = $social_data;
					$common_social_data['link'] = isset( $social_data['publicProfileUrl'] ) ? $social_data['publicProfileUrl'] : '';
					$common_social_data['id'] = isset( $social_data['id'] ) ? $social_data['id'] : '';
				break;

				case 'yahoo':
					$common_social_data['first_name'] = isset( $social_data['first_name'] ) ? $social_data['first_name'] : '';
					$common_social_data['last_name'] = isset( $social_data['last_name'] ) ? $social_data['last_name'] : '';
					$common_social_data['name'] = $common_social_data['first_name'].' '.$common_social_data['last_name'];
					$common_social_data['email'] = !empty( $social_data['email'] ) ? $social_data['email'] : '';
					$common_social_data['type'] = $social_type;
					$common_social_data['all'] = $social_data;
					$common_social_data['link'] = '';
					$common_social_data['id'] = isset( $social_data['identifier'] ) ? $social_data['identifier'] : '';
				break;

				case 'foursquare':
					$common_social_data['first_name'] = isset( $social_data->firstName ) ? $social_data->firstName : '';
					$common_social_data['last_name'] = isset( $social_data->lastName ) ? $social_data->lastName : '';
					$common_social_data['name'] = $common_social_data['first_name'] . ' ' . $common_social_data['last_name'];
					$common_social_data['email'] = isset( $social_data->contact ) ? $social_data->contact->email : '';
					$common_social_data['type'] = $social_type;
					$common_social_data['all'] = $social_data;
					$common_social_data['link'] = isset( $social_data->id ) ? esc_url('https://foursquare.com/user/' . $social_data->id) : '';
					$common_social_data['id'] = isset( $social_data->id ) ? $social_data->id : '';
				break;

				case 'windowslive':
					$common_social_data['first_name'] = isset( $social_data->first_name ) ? $social_data->first_name : '';
					$common_social_data['last_name'] = isset( $social_data->last_name ) ? $social_data->last_name : '';
					$common_social_data['name'] = isset( $social_data->name ) ? $social_data->name : '';
					$common_social_data['email'] = isset( $social_data->wlemail ) ? $social_data->wlemail : '';
					$common_social_data['type'] = $social_type;
					$common_social_data['all']  = $social_data;
					$common_social_data['link'] = isset( $social_data->link ) ? $social_data->link : '';
					$common_social_data['id']   = isset( $social_data->id ) ? $social_data->id : '';
				break;

				case 'vk':
					$common_social_data['first_name'] = isset( $social_data['first_name'] ) ? $social_data['first_name'] : '';
					$common_social_data['last_name'] = isset( $social_data['last_name'] ) ? $social_data['last_name'] : '';
					$common_social_data['name'] = $common_social_data['first_name'] . ' ' . $common_social_data['last_name'];
					$common_social_data['email'] = isset($social_data['email']) ? $social_data['email'] : '';
					$common_social_data['type'] = $social_type;
					$common_social_data['all'] = $social_data;
					$common_social_data['link'] = isset( $social_data['screen_name'] ) ? WOO_SLG_VK_LINK . '/' . $social_data['screen_name'] : '';
					$common_social_data['id'] = isset( $social_data['uid'] ) ? $social_data['uid'] : '';
				break;

				case 'twitter':
					$common_social_data['first_name'] = isset( $social_data->name ) ? $social_data->name : '';
					$common_social_data['last_name'] = '';
					$common_social_data['name'] = isset( $social_data->screen_name ) ? $social_data->screen_name : ''; //display name of user
					$common_social_data['type'] = 'twitter';
					$common_social_data['all'] = $social_data;
					$common_social_data['link'] = isset( $social_data->screen_name ) ? esc_url('https://twitter.com/' . $social_data->screen_name) : '';
					$common_social_data['id'] = isset( $social_data->id ) ? $social_data->id : '';
					$common_social_data['email'] = isset( $social_data->email ) ? $social_data->email : ''; // Twitter new option
				break;

				case 'amazon':
					$common_social_data['name'] = isset( $social_data->name ) ? $social_data->name : ''; //display name of user
					$common_social_data['id'] = isset( $social_data->user_id ) ? $social_data->user_id : '';
					$common_social_data['email'] = isset( $social_data->email ) ? $social_data->email : '';
					$common_social_data['all'] = $social_data;
					$common_social_data['type'] = $social_type;

				case 'paypal':
					$common_social_data['first_name'] = isset($social_data->given_name) ? $social_data->given_name : '';
					$common_social_data['last_name'] = isset($social_data->family_name) ? $social_data->family_name : '';
					$common_social_data['email'] = isset( $social_data->email ) ? $social_data->email : '';
					$common_social_data['name'] = isset($social_data->name) ? $social_data->name : ''; //display name of user
					$common_social_data['type'] = $social_type;
					$common_social_data['all'] = $social_data;
					$common_social_data['id'] = isset( $social_data->user_id ) ? $social_data->user_id : '';
				break;

				case 'line':
					$common_social_data['first_name'] = isset( $social_data['name'] ) ? $social_data['name'] : '';
					$common_social_data['last_name'] = '';
					$common_social_data['name'] = isset( $social_data['name'] ) ? $social_data['name'] : '';
					$common_social_data['email'] = isset( $social_data['email'] ) ? $social_data['email'] : '';
					$common_social_data['type'] = $social_type;
					$common_social_data['all'] = $social_data;
					$common_social_data['link'] = '';
					$common_social_data['id'] = isset( $social_data['id'] ) ? $social_data['id'] : '';
				break;

				case 'apple':
					$common_social_data['first_name'] = isset($social_data->name->firstName) ? $social_data->name->firstName : '';
					$common_social_data['last_name'] = isset($social_data->name->lastName
						) ? $social_data->name->lastName : '';
					$common_social_data['email'] = isset($social_data->email) ? $social_data->email : '';
					$common_social_data['name'] = isset($social_data->name->firstName) ? $social_data->name->firstName.' '.$social_data->name->lastName : '';
					$common_social_data['sub'] = isset($social_data->sub) ? $social_data->sub : ''; //display name of user
					$common_social_data['c_hash'] = isset($social_data->c_hash) ? $social_data->c_hash : '';
					$common_social_data['type'] = $social_type;
					$common_social_data['all'] = $social_data;
					$common_social_data['link'] = '';
					$common_social_data['sub'] = isset($social_data->sub) ? $social_data->sub : '';
				break;
			}
		}

		return apply_filters('woo_slg_get_user_common_social_data', $common_social_data, $social_type);
	}

	/**
	 * Check license key is activated or not
	 *
	 * @package WooCommerce - Social Login
	 * @since 1.6.3
	 */
	public function woo_slg_is_activated() {

		$purchase_code = wpweb_get_plugin_purchase_code( WOO_SLG_PLUGIN_KEY );
		$email = wpweb_get_plugin_purchase_email( WOO_SLG_PLUGIN_KEY );

		if( !empty($purchase_code) && !empty($email) ) {
			return true;
		}
		return false;
	}

}
