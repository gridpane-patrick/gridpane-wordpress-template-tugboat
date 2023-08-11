<?php
/**
 * @package b1-site2-only
 */
/*
Plugin Name: b1-site2-only.php
Plugin URI: https://waas1.com/
Description: This mu script should only run on site id = 2 that is https://myshahbandr.com/my-account/
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//keep this THIS_SITE_ID in this order to save some performance.
//do not run this script is siteid is not 2 that is = https://myshahbandr.com/my-account/
$allowScriptToRun = false;
if ( THIS_CONTROLLER_TAG == 1 && THIS_SITE_ID == 2 ){ $allowScriptToRun = true; }

if( !$allowScriptToRun ){ return; }




//do not run this script if the call is from WP_CLI
if ( defined( 'WP_CLI' ) && WP_CLI ) { return; }


//do not run this script if the call is from ajax
if ( defined('DOING_AJAX') && DOING_AJAX) { return; }


//do not run this script in the backend of the wp
if ( is_admin() ) { return; }




//------------
//if we are here it means we are safe to run the rest of the script.
//------------


if( isset($_SERVER['HTTP_REFERER']) ){
	
	add_action( 'wp_headers', 'july522228_wp_headers', 999 );
	function july522228_wp_headers( $headers ) {
        $headers['Content-Security-Policy'] = 'frame-ancestors *';
		return $headers;
	};
	
}




