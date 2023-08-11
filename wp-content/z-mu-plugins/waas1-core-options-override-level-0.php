<?php
/**
 * @package waas1-core-options-override-level-0
 */
/*
Plugin Name: waas1-core-options-override-level-0
Plugin URI: https://waas1.com/
Description: override core options values
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}





//always organize uploads into month- and year-based folders
add_filter( 'pre_update_option_uploads_use_yearmonth_folders', function( $old ) {
   return '1';
}, 10);




//disable updating of WordPress Address (URL) field in settings -> General Settings
//but only when the request is not coming from CLI
add_filter( 'pre_update_option_siteurl', function( $new, $old ) {
	
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return $new;
	}
	$currentLoggedInUser = wp_get_current_user();
	if( $currentLoggedInUser->data->user_login == 'superduper' ){
		return $new;
	}
	return $old;
   
}, 10, 2);

add_filter( 'pre_update_option_home', function( $new, $old ) {
	
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return $new;
	}
	$currentLoggedInUser = wp_get_current_user();
	if( $currentLoggedInUser->data->user_login == 'superduper' ){
		return $new;
	}
	return $old;
   
}, 10, 2);



//stop showing admin email verification every 6 months
add_filter( 'admin_email_check_interval', '__return_false' );



//disable Application Passwords from -> users edit screen.
//add_filter( 'wp_is_application_passwords_available', '__return_false' );

?>