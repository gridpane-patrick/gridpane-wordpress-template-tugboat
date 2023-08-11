<?php
/**
 * @package waas1-worker-api
 */
/*
Plugin Name: waas1-worker-api
Plugin URI: https://waas1.com/
Description: Will make calls to our worker server
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//only needs to run in the admin area
if( !is_admin() ){
	return;
}



class waas1WorkerApi{
	
	var $url = WAAS1_SITE_API_URL.'/api-v1/';
	var $postData = array();
	var $error;
	var $response;
	
	
	function __construct( $endPoint, $postData ){
		
		$this->url = $this->url.$endPoint;
		
		$this->postData = array('user' => 'site'.THIS_SITE_ID, 'site-id' => THIS_SITE_ID, 'key' => WAAS1_SITE_API_KEY );
		$this->postData = array_merge($this->postData, $postData);
		
	}
	
	
	
	function execute(){
		
		$response = wp_remote_post( $this->url, array(
			'method'      => 'POST',
			'timeout'       => 10,
			'blocking'    => true,
			'body'        => $this->postData
			)
		);
		
		if ( is_wp_error( $response ) ) {
			$this->error = $response->get_error_message();
			return false;
		} else {
			$this->response = $response['body'];
			return true;
		}
		
	}
	
	
	function getError(){
		return $this->error;
	}
	
	function getResponse(){
		return $this->response;
	}
	
}






?>