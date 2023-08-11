<?php
/**
 * @package b1-site-domain-change-post-hooks
 */
/*
Plugin Name: b1-site-domain-change-post-hooks.php
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


add_action( 'waas1_tenant_lifecycle_post_domain_change', function( $assoc_args ){ //hook as soon as new site is created and ready.

	
	//rewite permalinks:
	flush_rewrite_rules();
	
	//clear all cache
	if( function_exists('w3tc_flush_all') ){
		w3tc_flush_all();
	}
	
	
});


?>