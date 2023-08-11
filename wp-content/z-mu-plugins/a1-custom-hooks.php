<?php
/**
 * @package a1-custom-hooks
 */
/*
Plugin Name: a1-custom-hooks.php
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




//use the following constants
//THIS_ENV_IS_LOCAL = 

//THIS_SITE_ID = 25
//THIS_CONTROLLER_TAG = 1


//WAAS1_PLATFORM_DOMAIN	=	"yoursubsite.com"
//WAAS1_SITE_API_KEY		=	"fc17b88bce6e8cd9e2e19f26afc0e42e"
//WAAS1_SITE_API_URL		=	"https://ctrl-1.yoursubsite.com"
	
//WAAS1_DB_HOST			=	"192.168.6.3"
//WAAS1_DB_USER			=	"site25"
//WAAS1_DB_PASSWORD		=	"44b29b5a7ac104c"
//WAAS1_DB_NAME			=	"site25"


//WAAS1_WP_HOME			=	"https://excuse-4615.yoursubsite.com"
//WAAS1_WP_CONTENT_URL	=	"https://excuse-4615.yoursubsite.com/wp-content"
//WAAS1_WP_CONTENT_DIR	=	"/var/www/site25/wp-content"
//WAAS1_WP_DEBUG_LOG		=	"/mnt/app_1_006/site25/logs/wp.log"

//REGISTERABLE_DOMAIN		=	"yoursubsite.com"
//CLOUDFLARE_CDN_CNAME	=	"cfcdn1site25-fc"

//WAAS1_CLIENT_EMAIL		=   "aliraza@invokers.net"

//PLUGIN_FLUENT_SMTP_FLUENTMAIL_SENDINBLUE_API_KEY	=	"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"

//WAAS1_INJECT_DATA		=   "{"inject-first_name":"Anna","inject-last_name":"Howerton","inject-email_address":"anna@cultivateadvisors.com"}"


//PLATFORM_BRAND_LOGO_URL		= "https://wptenant.gitlab.io/assets/images/yoursubsite.com/logo-white.png"
//PLATFORM_BRAND_LOGO_URL_BLACK		= "https://wptenant.gitlab.io/assets/images/root/logo-black.png"
//PLATFORM_BRAND_BACKGROUND_COLOR		= "#fffbe5"
//PLATFORM_BRAND_BACKGROUND_COLOR_BLACK		= "#fffbe5"
//PLATFORM_BRAND_PRIMARY_COLOR		= "#F34068"
//PLATFORM_BRAND_PRIMARY_COLOR_BLACK		= "#F34068"
//PLATFORM_BRAND_SITE_URL		= "https://performanceplatform.app/"
//PLATFORM_BRAND_NAME		= "Performance Platform"

//WAAS1_RESTRICTION_GROUP_ID = 3
//WAAS1_RESTRICTION_ALLOW_PLUGINS_ACTIVATE	= 
//WAAS1_RESTRICTION_ALLOW_PLUGINS_INSTALL	= 
//WAAS1_RESTRICTION_ALLOW_SITE_SETTINGS_MU_PLUGIN	= 
//WAAS1_RESTRICTION_ALLOW_THEMES_INSTALL	= 
//WAAS1_RESTRICTION_ALLOW_THEMES_SWITCH	= 
//WAAS1_RESTRICTION_PHP_MAX_REQUEST	= "500"
//WAAS1_RESTRICTION_PHP_MAX_WORKER	= "2"
//WAAS1_RESTRICTION_PHP_MEMORY_LIMIT	= "64M"


//WAAS1_TOTAL_DB_SIZE_MB = 0
//WAAS1_TOTAL_APP_SIZE_MB = 0
//WAAS1_TOTAL_APP_INODES = 0

//ANALYTICS_MONTH = "2022-05"
//ANALYTICS_DATE = "2022-05-24"
//TOTAL_PAGE_VIEWS = 11679
//TOTAL_REQUESTS = 37862
//TOTAL_UNIQUE_VISITORS = 193
//TOTAL_BANDWIDTH_BYTES = 303154522
//TOTAL_BANDWIDTH_MB = 289
//TOTAL_THREATS = 0








////-----------////
//allow only superduper user to access user profile application field:
////-----------////


add_action( 'admin_init', function(){
	//disable application password but not for superduper user and wpcli
	add_filter( 'wp_is_application_passwords_available', 'waas1_007_wp_is_application_passwords_available' );
});





//this hook must be initalized using admin_init
function waas1_007_wp_is_application_passwords_available(){
	
	//make sure this process only on admin side.
	if( !is_admin() ){
		return false;
	}
	
	//if the call is from "wp-cli" allow the call.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}
	
	
	$currentUserId = get_current_user_id();

	$currentLoggedInUser = wp_get_current_user();
	if( $currentLoggedInUser->data->user_login == 'superduper' ){
		return true;
	}
	
	//otherwise return false;
	return false;
}
/**
 * [sitename] — Site name shortcode
 */
add_shortcode( 'sitename', function() { return get_bloginfo(); } );

?>