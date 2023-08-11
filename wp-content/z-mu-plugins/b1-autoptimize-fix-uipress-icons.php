<?php
/**
 * @package b1-autoptimize-fix-uipress-icons
 */
/*
Plugin Name: b1-autoptimize-fix-uipress-icons.php
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


//do not run if user is not in font-end
if ( is_admin() ) { return; }




//exclude all uipress icons
add_filter( 'autoptimize_filter_css_exclude', function( $exclude ){
	return $exclude . ', wp-content/plugins/uipress/assets/css/';
}, 10,  1 );





?>