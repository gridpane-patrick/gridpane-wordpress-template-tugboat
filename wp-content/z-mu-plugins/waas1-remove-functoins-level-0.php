<?php
/**
 * @package waas1-helper-remove-functions-type-0
 */
/*
Plugin Name: waas1-helper-remove-functions-type-0
Plugin URI: https://waas1.com/
Description: helper function to remove various functions of different plugins that we don't need.
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//https://github.com/herewithme/wp-filters-extras/blob/master/wp-filters-extras.php

/**
 * Allow to remove method for an hook when, it's a class method used and class don't have global for instanciation !
 */
function remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	// Take only filters on right hook name and priority
	if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
		return false;
	}

	// Loop on filters registered
	foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
			// Test if object is a class and method is equal to param !
			if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && $filter_array['function'][1] == $method_name ) {
				// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
				if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
					unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
				} else {
					unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
				}
			}
		}

	}

	return false;
}

/**
 * Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
 */
function remove_filters_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	// Take only filters on right hook name and priority
	if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
		return false;
	}

	// Loop on filters registered
	foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
			// Test if object is a class, class and method is equal to param !
			if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
				// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
				if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
					unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
				} else {
					unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
				}
			}
		}

	}

	return false;
}






//**********
//Site Health Check
//**********
add_action( 'admin_menu', function(){
	remove_submenu_page( 'tools.php', 'site-health.php' );
});

// block site health page screen
add_action( 'current_screen', function(){
	if ( is_admin() ) {
        $screen = get_current_screen();
        // if screen id is site health
        if ( 'site-health' == $screen->id ) {
            wp_redirect( admin_url() );
            exit;
        }
    }
});

//remove site health dashboard widget
add_action('wp_dashboard_setup', function(){
	remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
});




//**********
//Redirect attachment page
//**********
add_action( 'template_redirect', function(){
	
	if ( is_attachment() ) {
		global $post;
		if ( $post && $post->post_parent ) {
			wp_redirect( esc_url( get_permalink( $post->post_parent ) ), 301 );
			exit;
		} else {
			wp_redirect( esc_url( home_url( '/' ) ), 301 );
			exit;
		}
	}
	
});






?>