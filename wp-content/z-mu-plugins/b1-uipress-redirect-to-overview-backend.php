<?php
/**
 * @package b1-uipress-redirect-to-overview-backend
 */
/*
Plugin Name: b1-uipress-redirect-to-overview-backend.php
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

//if the call is from "wp-cli" don't run the code below
if ( defined( 'WP_CLI' ) && WP_CLI ) { return; }

//do not run if the call is ajax
if ( defined('DOING_AJAX') && DOING_AJAX) { return; }


//do not run if user is not in admin/backend side
if ( !is_admin() ) { return; }







add_action( 'init', function(){
	
	$get_view = basename($_SERVER['REQUEST_URI']);
	if( $get_view == 'wp-admin' && $_SERVER['REQUEST_METHOD'] == 'GET' ){
		//only redirect if uipress plugin is active:
		if( is_plugin_active('uipress/uipress.php') ){
			wp_redirect( admin_url('/admin.php?page=uip-overview') );
		}
	}
	
});






?>