<?php
/**
 * @package waas1-clear-cache-after-plugin-activated-deactivated
 */
/*
Plugin Name: waas1-clear-cache-after-plugin-activated-deactivated
Plugin URI: https://waas1.com/
Description: Clear cache after plugin is activated or deativated
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/



// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'activate_plugin', function(){
	
	if (function_exists('w3tc_flush_all')){
		w3tc_flush_all();
	}
	
},10, 1 );


add_action( 'deactivate_plugin', function(){
	
	if (function_exists('w3tc_flush_all')){
		w3tc_flush_all();
	}
	
},10, 1 );

?>