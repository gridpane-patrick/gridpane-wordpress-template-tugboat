<?php
/**
 * @package waas1-hide-critical-plugins
 */
/*
Plugin Name: waas1-hide-critical-plugins
Plugin URI: https://waas1.com/
Description: this will hide all the critical plugins from the dashboard
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



//hide critical plugins from the installed plugins list
add_action( 'pre_current_active_plugins', function(){
	
	
	
	$currentUser = wp_get_current_user();
	if( $currentUser->data->user_login == 'superduper' ){
		return;
	}
	
	
	
	global $wp_list_table;
	$pluginsToHide = array( 
						'w3-total-cache/w3-total-cache.php',
						'autoptimize/autoptimize.php',
						'flying-pages/flying-pages.php',
						'canvas-image-resize/canvas-image-resize.php',
						'clean-image-filenames/clean-image-filenames.php',
						'resmushit-image-optimizer/resmushit.php',
						'wp-rest-cache/wp-rest-cache.php',
						'updraftplus/updraftplus.php',
						'mainwp/mainwp.php',
						'mainwp-child/mainwp-child.php',
						'fluent-smtp/fluent-smtp.php',
						'disable-comments/disable-comments.php',
						'one-time-login/one-time-login.php',
						);
						
	$myplugins = $wp_list_table->items;
	
	foreach( $myplugins as $key => $val ) {
		
		if( in_array($key, $pluginsToHide) ) {
			unset( $wp_list_table->items[$key] );
		}
		
	}
	
});








//hide admin top bar items
add_action('admin_bar_menu', function( $wp_admin_bar ){
	
	
	
	$currentUser = wp_get_current_user();
	if( $currentUser->data->user_login == 'superduper' ){
		return;
	}
	
	
	
	if ( is_admin() ) {
		
		//w3tc
		$wp_admin_bar->remove_node('w3tc_support');
		$wp_admin_bar->remove_node('w3tc_settings_extensions');
		$wp_admin_bar->remove_node('w3tc_settings_faq');
		$wp_admin_bar->remove_node('w3tc_settings_general');
		$wp_admin_bar->remove_node('w3tc_feature_showcase');
		$wp_admin_bar->remove_node('w3tc_flush_all');
		$wp_admin_bar->remove_node('w3tc_overlay_upgrade'); //only avaiable in admin
		
		//autoptimize
		$wp_admin_bar->remove_node('autoptimize');

		
    }else{
		
		//w3tc
		$wp_admin_bar->remove_node('w3tc_support');
		$wp_admin_bar->remove_node('w3tc_settings_extensions');
		$wp_admin_bar->remove_node('w3tc_settings_faq');
		$wp_admin_bar->remove_node('w3tc_settings_general');
		$wp_admin_bar->remove_node('w3tc_feature_showcase');
		$wp_admin_bar->remove_node('w3tc_flush_all');

		//autoptimize
		$wp_admin_bar->remove_node('autoptimize');
	}
	
	
	
	
}, 9999);






//hide admin menu pages
add_action( 'admin_init', function(){
	
	
	if( wp_doing_ajax() ) {
		return;
	}
	
	$currentUser = wp_get_current_user();
	if( $currentUser->data->user_login == 'superduper' ){
		return;
	}

	
	//remove plugin links from admin
	remove_menu_page( 'w3tc_dashboard' );
	remove_submenu_page( 'index.php', 'update-core.php' );
	remove_submenu_page( 'options-general.php', 'autoptimize' );
	remove_submenu_page( 'options-general.php', 'flying-pages' );
	remove_submenu_page( 'options-general.php', 'canvas-image-resize' );
	remove_submenu_page( 'options-general.php', 'wp-rest-cache' );
	remove_submenu_page( 'options-general.php', 'updraftplus' );
	remove_submenu_page( 'upload.php', 'resmushit_options' );
	remove_submenu_page( 'options-general.php', 'mainwp_child_tab' );
	remove_submenu_page( 'options-general.php', 'disable_comments_settings' );
	remove_submenu_page( 'tools.php', 'disable_comments_tools' );
	remove_submenu_page( 'options-general.php', 'fluent-mail' );
	remove_submenu_page( 'options-general.php', 'admin2020-settings' );
	
	
	
	
	
	
	if ( isset($_GET['page']) ) {
		
		global $pagenow;
		
		$restrictedParentMenu = array( 
									'options-general.php',
									'admin.php',
									'upload.php',
								);
									
		$restrictedSubMenu = array(
								'w3tc_dashboard', 'w3tc_feature_showcase', 'w3tc_general', 'w3tc_pgcache', 'w3tc_minify', 'w3tc_dbcache', 'w3tc_objectcache', 'w3tc_browsercache', 'w3tc_cdn', 'w3tc_support', 'w3tc_userexperience', 'w3tc_install', 'w3tc_setup_guide', 'w3tc_about', 'w3tc_extensions', 'w3tc_stats',
								'updraftplus',
								'mainwp_child_tab',
								'canvas-image-resize',
								'autoptimize',
								'resmushit_options',
								'flying-pages',
								'wp-rest-cache',
								'disable_comments_settings',
								'fluent-mail',
								'admin2020-settings',
							);
		
		if( in_array($pagenow, $restrictedParentMenu) ) {
			if( in_array($_GET['page'], $restrictedSubMenu) ) {
				wp_redirect( admin_url() );
			}
		}
		
	}
	
	
	
});







//**********
//canvas-image-resize
//**********

add_filter( 'pre_update_option_canvas-image-resize_settings', function( $old ) {
	return array( 'image_max_width' => 2000, 'image_max_height' => 2000, 'image_max_quality' => 100 );
}, 9999);


//**********
//clean-image-filenames
//**********

add_filter( 'pre_update_option_clean_image_filenames_mime_types', function( $old ) {
   return 'all';
}, 9999);




//**********
//W3TC
//**********

//since we have locked the W3TC settings files and only root user can change them. W3TC will keep on showing an error.
//we need to remove it by returning an empty array. filter defined in: w3-total-cache\Generic_Plugin_Admin.php
add_filter( 'w3tc_errors', function( $error ){
	return array();
});

//removing w3tc footer comment from every page
add_filter( 'w3tc_can_print_comment', function( $w3tc_setting ) { return false; }, 10, 1 );





//**********
//autoptimize
//**********
add_filter( 'autoptimize_filter_cache_do_fallback', '__return_false' );



//**********
//WP REST Cache
//**********
add_filter( 'wp_rest_cache/display_clear_cache_button', function( $w3tc_setting ) { return false; }, 10, 1 );








?>