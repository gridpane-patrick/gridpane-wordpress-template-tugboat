<?php
/**
 * @package waas1-updraftplus-hooks
 */
/*
Plugin Name: waas1-updraftplus-hooks
Plugin URI: https://waas1.com/
Description: Skip linked plugins/themes/mu-plugins from getting backedup
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/




// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//**********
//Updraft Plus - Do not add themes/plugins added using repos.
//**********
add_filter( 'updraftplus_exclude_directory', function( $filter, $fullPath, $storedPath ){
	
	//(Example) $fullPath = /var/www/plugins/acf-extended/0.8.7.6/includes/admin/tools
	//(Example) $storedPath = plugins/acf-extended/includes/admin
	
	$explodedStoredPath = explode( '/', $storedPath );
	
	$foldersToSkipArray = array( 'mu-plugins', 'w3tc-config' );
	
	if( isset($explodedStoredPath[0]) ){
		if( in_array($explodedStoredPath[0], $foldersToSkipArray) ){
			return true;
		}
	}
	
	
	if( !isset($explodedStoredPath[1]) ){
		return false;
	}
	
	$checkPath = WP_CONTENT_DIR.'/'.$explodedStoredPath[0].'/'.$explodedStoredPath[1];
	
	
	$isLink = is_link( $checkPath );

	if( $isLink ){
		return true;
	}else{
		
		return false;
	}
	
}, 10, 3 );



//restore -- this will only short circute plugins and themes dirs.
add_filter( 'updraft_move_existing_to_old_short_circuit', function( $circuit, $type ){
	return true;
}, 10, 2 );



?>