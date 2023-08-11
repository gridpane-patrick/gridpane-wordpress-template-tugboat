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


//do not allow to delete superduper
add_action( 'delete_user', function( $id ) {
	

	$currentLoggedInUser = wp_get_current_user();
	if( $currentLoggedInUser->data->user_login == 'superduper' ){
		return;
	}
	
	
	$user = get_user_by( 'id', $id );
	if( $user->user_login == WAAS1_CLIENT_EMAIL ){ //do not allow to delete if user = WAAS1_CLIENT_EMAIL
		wp_redirect( admin_url() );
        exit();
	}

	
});


?>