<?php
/**
 * @package b1-hide-theme-related-plugins-auto-active
 */
/*
Plugin Name: b1-hide-theme-related-plugins-auto-active.php
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




//hide themes related plugins from the plugin list.
add_action( 'pre_current_active_plugins', function(){
	
	//but do not hide for superduper user.
	$currentUser = wp_get_current_user();
	if( $currentUser->data->user_login == 'superduper' ){
		return;
	}
	
	
	global $wp_list_table;
	$pluginsToHide = array( 
						//Theme: OceanWP
						'ocean-extra/ocean-extra.php',
						
						//Theme: Avada
						//no plugins found related to this theme in git.
						
						//Theme: Ignition
						'ignition/ignition.php',
						
						//Theme: Storefront
						'storefront-powerpack/storefront-powerpack.php',
						'storefront-title-toggle/storefront-title-toggle.php',
						//plugin arabic fonts not found in git.

						//Theme: Famita
						'apus-framework/apus-framework.php',
						'cmb2/init.php',
						'js_composer/js_composer.php',
						'variation-swatches-for-woocommerce/variation-swatches-for-woocommerce.php',
						'woo-smart-compare/wpc-smart-compare.php',
						'woo-smart-wishlist/wpc-smart-wishlist.php',

						);
						
	$myplugins = $wp_list_table->items;
	
	foreach( $myplugins as $key => $val ) {
		
		if( in_array($key, $pluginsToHide) ) {
			unset( $wp_list_table->items[$key] );
		}

	}
	
	
}); //pre_current_active_plugins ends





//fire the hook when the theme is switched
add_action( 'after_switch_theme', 'june2720221012_after_switch_theme' );
function june2720221012_after_switch_theme(){
	
	$newTheme = wp_get_theme();
	$newTheme = strtolower( $newTheme->name );

	
	$pluginsArray = array(
		'oceanwp' 		=> array( 'ocean-extra/ocean-extra.php' ),
		'ignition' 		=> array( 'ignition/ignition.php' ),
		'storefront' 	=> array( 'storefront-powerpack/storefront-powerpack.php', 'storefront-title-toggle/storefront-title-toggle.php' ),
		'famita' 	=> array( 'apus-framework/apus-framework.php', 'cmb2/init.php', 'js_composer/js_composer.php','variation-swatches-for-woocommerce/variation-swatches-for-woocommerce.php','woo-smart-compare/wpc-smart-compare.php','woo-smart-wishlist/wpc-smart-wishlist.php', ),
		//'avada' 	=> array( ),
	);
	
	
	//all the following themes will actually activate storefront plugins.
	if( $newTheme == 'arcade' 
		|| $newTheme == 'bookshop'
		|| $newTheme == 'homestore'
		|| $newTheme == 'petshop'
		|| $newTheme == 'pharmacy'
		|| $newTheme == 'outlet'
		|| $newTheme == 'galleria'
	  ){
		$newTheme = 'storefront';
	}
	
	
	//all the following themes will actually activate ignition plugins.
	if( $newTheme == 'amaryllis - ignition'
		|| $newTheme == 'convert - ignition'
		|| $newTheme == 'decorist - ignition'
		|| $newTheme == 'loge - ignition'
		|| $newTheme == 'medi - ignition'
		|| $newTheme == 'milos - ignition'
		|| $newTheme == 'neto - ignition'
		|| $newTheme == 'nozama - ignition'
		|| $newTheme == 'spencer - ignition'
	  ){
		$newTheme = 'ignition';
	}
	
	
	
	$continue = false;
	foreach( $pluginsArray as $key=>$plugin ){
		if( $newTheme == $key ){
			$continue = true;
			break;
		}
	}
	
	//if we do not find the key return from here
	if( !$continue ){ return; }
	
	// Eslam: Enable activating plugin theme, to override plugns/themes per plan
	@file_put_contents(WPMU_PLUGIN_DIR . '/json/related-plugins-auto-active.json',json_encode($pluginsArray[$newTheme],JSON_UNESCAPED_UNICODE) );
	// End 
	
	foreach( $pluginsArray[$newTheme] as $plugin ){
		activate_plugin( $plugin );
		unset( $pluginsArray[$newTheme] ); //unset the array key
	}
	
	//deactivate all other theme related plugins.
	//create single dimension array
	$singDimensionPluginArray = array();
	foreach( $pluginsArray as $plugins ){
		foreach( $plugins as $plugin ){
			$singDimensionPluginArray[] = $plugin;
		}
	}
	
	deactivate_plugins( $singDimensionPluginArray );
	
	
}
