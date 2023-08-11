<?php
/**
 * @package waas1-generate-better-image-sizes
 */
/*
Plugin Name: waas1-generate-better-image-sizes
Plugin URI: https://waas1.com/
Description: Generate some better sized images
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//remove all the image size keepign only the default wordpres images
add_action('init', function(){
	foreach ( get_intermediate_image_sizes() as $size ) {
		remove_image_size( $size );
    }
	
	add_image_size( 'logo', 200, 100, array( 'center', 'center' ) );
	add_image_size( 'medium_2', 400, 400 );
	add_image_size( 'medium_2_large', 600, 600 );
	add_image_size( 'featured-portrait', 900, 1200, array( 'center', 'center' ) );
	add_image_size( 'featured-landscape', 1200, 900, array( 'center', 'center' ) );
	add_image_size( 'background', 1920, 1080, array( 'center', 'center' ) );

	
}, 9999);


//now even remove the default wordpress images

add_filter( 'intermediate_image_sizes_advanced', 'waas1_intermediate_image_sizes_advanced' );

// This will remove the default image sizes and the medium_large size. 
function waas1_intermediate_image_sizes_advanced( $sizes ) {
	
	unset( $sizes['medium'] );
	unset( $sizes['medium_large'] );
	unset( $sizes['large'] );
	
	return $sizes; //we want to return a size so that it can generate a thumbnail that is 150x150
	
}


?>