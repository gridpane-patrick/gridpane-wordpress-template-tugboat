<?php
/**
 * @package waas1-filter-http-api
 */
/*
Plugin Name: waas1-filter-http-api.php
Plugin URI: https://waas1.com/
Description: Only allow whitelist of urls to make connections
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//define( 'WP_HTTP_BLOCK_EXTERNAL', true );


add_action( 'admin_init', function(){
	
	add_filter( 'pre_http_request', 'july2122522_pre_http_request', 999, 3 );

	function july2122522_pre_http_request( $preempt, $parsed_args, $url ){
		
		
		// Split the URL into useful parts.
		$url_data = parse_url( $url );
		
		
		#1
		$hostCheck = 'api.wordpress.org';
		if( $url_data['host'] == $hostCheck ){
			
			if( isset($url_data['path']) && $url_data['path'] == '/plugins/update-check/1.1/' ){
				return new WP_Error( 'http_request_block', 'disabled '.$hostCheck.' using mu-plugin: waas1-filter-http-api.php' );
			}
			if( isset($url_data['path']) && $url_data['path'] == '/themes/update-check/1.1/' ){
				return new WP_Error( 'http_request_block', 'disabled '.$hostCheck.' using mu-plugin: waas1-filter-http-api.php' );
			}
			if( isset($url_data['path']) && $url_data['path'] == '/core/version-check/1.7/' ){
				return new WP_Error( 'http_request_block', 'disabled '.$hostCheck.' using mu-plugin: waas1-filter-http-api.php' );
			}
			if( isset($url_data['path']) && $url_data['path'] == '/core/browse-happy/1.1/' ){
				return new WP_Error( 'http_request_block', 'disabled '.$hostCheck.' using mu-plugin: waas1-filter-http-api.php' );
			}
			if( isset($url_data['path']) && $url_data['path'] == '/core/serve-happy/1.0/' ){
				return new WP_Error( 'http_request_block', 'disabled '.$hostCheck.' using mu-plugin: waas1-filter-http-api.php' );
			}
			if( isset($url_data['path']) && $url_data['path'] == '/translations/core/1.0/' ){
				return new WP_Error( 'http_request_block', 'disabled '.$hostCheck.' using mu-plugin: waas1-filter-http-api.php' );
			}
			
		}
		
		
		
		$debugLog = array( 'url'=>$url_data );
		if( isset($parsed_args['body']) ){
			$debugLog['body'] = $parsed_args['body'];
		}else{
			$debugLog['body'] = 'body not found';
		}
		
		
		do_action( 'qm/debug', $debugLog );
		
		return $preempt;

	}

	
});














?>