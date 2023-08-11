<?php
/**
 * @package b1-site-create-post-hooks
 */
/*
Plugin Name: b1-site-create-post-hooks.php
Plugin URI: https://waas1.com/
Description: custom hooks only for this platform.
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'waas1_tenant_post_created', function( $assoc_args ){ //hook as soon as new site is created and ready.

	//always remove this user if found:
	$oldUser = get_user_by( 'email', 'tsharkas@gmail.com' ); 
	$newUser = get_user_by( 'email', WAAS1_CLIENT_EMAIL );
	
	if( $oldUser ){ //only when old user is found
		wp_delete_user( $oldUser->data->ID, $newUser->data->ID );
	}
	
	
	//add firstname and lastname to the client user
	$injectedData = WAAS1_INJECT_DATA;
	$convertJsonFormSafeIniStore = str_replace( '*', '"', $injectedData );
	$injectedDataArray = json_decode( $convertJsonFormSafeIniStore, true );
	
	
	if( is_array($injectedDataArray) ){
		$updateArray = array( 'ID'=>$newUser->data->ID );
		
		if( isset($injectedDataArray['inject-first_name']) ){
			$updateArray['first_name'] 		= $injectedDataArray['inject-first_name'];
			$updateArray['nickname'] 		= $injectedDataArray['inject-first_name'];
			$updateArray['display_name'] 	= $injectedDataArray['inject-first_name'];
		}
		
		if( isset($injectedDataArray['inject-last_name']) ){
			$updateArray['last_name'] = $injectedDataArray['inject-last_name'];
		}
		
		
		if( isset($injectedDataArray['inject-first_name']) && isset($injectedDataArray['inject-last_name']) ){
			$updateArray['display_name'] = $injectedDataArray['inject-first_name'].' '.$injectedDataArray['inject-last_name'];
		}
		
		
		wp_update_user( $updateArray );
	}

	
	//set the default site language to arabic.
	update_option( 'WPLANG', 'ar' );
	
	//delete new_admin_email if found any
	delete_option( 'new_admin_email');
	
	//rewite permalinks:
	flush_rewrite_rules();
	
	//clear all cache
	if( function_exists('w3tc_flush_all') ){
		w3tc_flush_all();
	}
	
	
});


?>