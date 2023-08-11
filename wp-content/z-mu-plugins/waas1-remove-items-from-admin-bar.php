<?php
/**
 * @package waas1-remove-items-from-admin-bar
 */
/*
Plugin Name: waas1-remove-items-from-admin-bar
Plugin URI: https://waas1.com/
Description: helper function to remove various items from the admin bar.
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



add_action('admin_bar_menu', function( $wp_admin_bar ){
	
	if ( is_admin() ) {
		$wp_admin_bar->remove_node('wp-logo');
        $wp_admin_bar->remove_node('updates');
        $wp_admin_bar->remove_node('comments');
        $wp_admin_bar->remove_node('new-content');
		$wp_admin_bar->remove_node('search');
		

		
        //$wp_admin_bar->remove_node('my-account');
		//$wp_admin_bar->remove_node('site-name');


		
    }else{
		
		$wp_admin_bar->remove_node('wp-logo');
		$wp_admin_bar->remove_node('updates');
		$wp_admin_bar->remove_node('comments');
		$wp_admin_bar->remove_node('new-content');
		$wp_admin_bar->remove_node('search');
		
		//$wp_admin_bar->remove_node('my-account');
		//$wp_admin_bar->remove_node('site-name');
        //$wp_admin_bar->remove_node('customize'); //only avaliable on front-end
		
	}
	
	
	
	
}, 9999);



?>