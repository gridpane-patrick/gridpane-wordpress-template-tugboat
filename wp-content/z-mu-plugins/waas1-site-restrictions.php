<?php
/**
 * @package waas1-site-restrictions
 */
/*
Plugin Name: waas1-site-restrictions.php
Plugin URI: https://waas1.com/
Description: Plugin to apply restrictions for non "superduper" user
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


	
	
if( !defined('WAAS1_RESTRICTION_ALLOW_PLUGINS_INSTALL') ){
	define( 'WAAS1_RESTRICTION_ALLOW_PLUGINS_INSTALL',	false ); //default not to allow plugins install
}

if( !defined('WAAS1_RESTRICTION_ALLOW_PLUGINS_ACTIVATE') ){
	define( 'WAAS1_RESTRICTION_ALLOW_PLUGINS_ACTIVATE',	false ); //default not to allow plugins activation/deactivation
}


if( !defined('WAAS1_RESTRICTION_ALLOW_THEMES_INSTALL') ){
	define( 'WAAS1_RESTRICTION_ALLOW_THEMES_INSTALL',	false ); //default not to allow themes install
}

if( !defined('WAAS1_RESTRICTION_ALLOW_THEMES_SWITCH') ){
	define( 'WAAS1_RESTRICTION_ALLOW_THEMES_SWITCH',	false ); //default not to allow themes switch
}



//filter capabilities
add_filter( 'map_meta_cap', function($caps, $cap){

	//no restrictions for the "superduper user"
	$user = wp_get_current_user();
	if( $user->user_login == 'superduper' ){
		return $caps;
	}
	//no restrictions for the "superduper user"
	
	
	if ( $cap === 'install_plugins' && !WAAS1_RESTRICTION_ALLOW_PLUGINS_INSTALL ) {
		$caps[] = 'do_not_allow'; //setting it up as do_not_allow is important
	}
	
	if ( $cap === 'activate_plugins' && !WAAS1_RESTRICTION_ALLOW_PLUGINS_ACTIVATE ) {
		$caps[] = 'do_not_allow'; //setting it up as do_not_allow is important
	}
	
	if ( $cap === 'install_themes' && !WAAS1_RESTRICTION_ALLOW_THEMES_INSTALL ) {
		$caps[] = 'do_not_allow'; //setting it up as do_not_allow is important
	}
	
	if ( $cap === 'switch_themes' && !WAAS1_RESTRICTION_ALLOW_THEMES_SWITCH ) {
		$caps[] = 'do_not_allow'; //setting it up as do_not_allow is important
	}

	return $caps;
}, 10, 2 );
		
	




?>