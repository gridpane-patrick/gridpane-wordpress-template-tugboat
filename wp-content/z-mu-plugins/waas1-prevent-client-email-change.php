<?php
/**
 * @package waas1-prevent-client-user-removal
 */
/*
Plugin Name: waas1-prevent-client-user-removal
Plugin URI: https://waas1.com/
Description: Do not allow to delete the first client user.
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//if the call is from "wp-cli" don't run the code below
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	return;
}



add_filter( 'wp_pre_insert_user_data', 'waas1_client_wp_pre_insert_user_data', 10, 4 );

function waas1_client_wp_pre_insert_user_data( $data, $update, $id, $userdata ){
	
	$currentLoggedInUser = wp_get_current_user();
	//allow superduper to delete the user
	if( $currentLoggedInUser->data->user_login == 'superduper' ){
		return $data;
	}
	
	//allow if data is not being updated
	if( !$update ){
		return $data;
	}
	
	
	$userToChange = get_user_by( 'id', $id );
	$userToChangeEmail = $userToChange->data->user_email;
	//if the user is other than client email allow to change the email
	if( $userToChangeEmail != WAAS1_CLIENT_EMAIL ){
		return $data;
	}
	

	
	//otherwise do not allow to change the email
	$old_email = $userToChangeEmail;
	$new_email = $data['user_email'];
	
	if( $old_email != $new_email ){
		$data['user_email'] = $old_email;
	}
	
	return $data;
	
}



//also disable the email notification when users update their email address:
add_filter( 'send_email_change_email', 'waas1_client_send_email_change_email', 1, 3 );
function waas1_client_send_email_change_email( $send, $user, $userdata ){
	
	$currentLoggedInUser = wp_get_current_user();
	//allow superduper
	if( $currentLoggedInUser->data->user_login == 'superduper' ){
		return $send;
	}
	
	//if the user is other than client email allow to send email
	if( $user['user_email'] != WAAS1_CLIENT_EMAIL ){
		return $send;
	}
	
    return false;
}




?>