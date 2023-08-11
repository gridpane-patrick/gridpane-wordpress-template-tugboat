<?php
/**
 * @package waas1-all-in-one-migration.php
 */
/*
Plugin Name: waas1-all-in-one-migration.php
Plugin URI: https://waas1.com/
Description: Filter to skip the unwanted files and folders
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


add_filter( 'ai1wm_exclude_themes_from_export', 'waas1_exclude_themes_folders' );
add_filter( 'ai1wm_exclude_plugins_from_export', 'waas1_exclude_plugins_folders' );
add_filter( 'ai1wm_exclude_content_from_export', 'waas1_exclude_other_folders' );




function waas1_exclude_themes_folders( $foldersToSkip ){
	
	$allInstalledThemes = wp_get_themes();
	$activeTheme  		= wp_get_theme();
	
	
	//skip disabled plugins
	foreach( $allInstalledThemes as $installedTheme ){
		if( $installedTheme->template != $activeTheme->template ){
			$foldersToSkip[] = $installedTheme->template;
		}
	}

	return $foldersToSkip;
	
};



function waas1_exclude_plugins_folders( $foldersToSkip ){
	
	// Check if get_plugins() function exists. This is required on the front end of the
	// site, since it is in a file that is normally only loaded in the admin.
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	
	$allInstalledPlugins 	= get_plugins();
	$activePlugins 			= get_option( 'active_plugins' );
	
	
	//skip disabled plugins
	foreach( $activePlugins as $activePlugin ){
		unset( $allInstalledPlugins[$activePlugin] );
	}
	
	foreach( $allInstalledPlugins as $pluginFullName=>$allInstalledPlugin ){
		$exploded = explode( '/', $pluginFullName );
		$foldersToSkip[] = $exploded[0];
	}
	
	//skip critical plugins
	$foldersToSkip[] = 'autoptimize';
	$foldersToSkip[] = 'canvas-image-resize';
	$foldersToSkip[] = 'clean-image-filenames';
	$foldersToSkip[] = 'disable-comments';
	$foldersToSkip[] = 'fluent-smtp';
	$foldersToSkip[] = 'flying-pages';
	$foldersToSkip[] = 'mainwp-child';
	$foldersToSkip[] = 'one-time-login';
	$foldersToSkip[] = 'resmushit-image-optimizer';
	$foldersToSkip[] = 'updraftplus';
	$foldersToSkip[] = 'w3-total-cache';
	$foldersToSkip[] = 'wp-rest-cache';
	
	return $foldersToSkip;
}







function waas1_exclude_other_folders( $foldersToSkip ){
	
	
	
	$allInstalledMuPlugins = get_mu_plugins();
	
	//skip symlinked mu-plugins
	foreach( $allInstalledMuPlugins as $pluginFullName=>$allInstalledPlugin ){
		$isLink = is_link( WPMU_PLUGIN_DIR.'/'.$pluginFullName );
		if( $isLink ){
			$foldersToSkip[] = 'mu-plugins/'.$pluginFullName;
		}
	}
	
	//skip wp-content/mu-plugins/assets
	$foldersToSkip[] = 'mu-plugins/assets';
	
	//skip wp-content/cache
	$foldersToSkip[] = 'cache';
	
	//skip wp-content/updraft
	$foldersToSkip[] = 'updraft';
	
	//skip wp-content/w3tc-config
	$foldersToSkip[] = 'w3tc-config';
	
	//skip wp-content/debug.log
	$foldersToSkip[] = 'debug.log';
	
	//skip wp-content/object-cache.php
	$foldersToSkip[] = 'object-cache.php';
	
	//skip wp-content/advanced-cache.php
	$foldersToSkip[] = 'advanced-cache.php';
	
	
	return $foldersToSkip;
	
}


?>