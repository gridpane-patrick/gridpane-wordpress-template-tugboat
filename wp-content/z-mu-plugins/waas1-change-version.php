<?php
/**
 * @package waas1-change-version
 */
/*
Plugin Name: waas1-change-version
Plugin URI: https://waas1.com/
Description: Change Plugins/Theme versions
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




if ( ! defined( 'WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR' ) ) {
	
	if( is_dir('../plugins') ){ //we are on linux
		define( 'WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR', '../plugins' );
	}else{ //we are on windows
		define( 'WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR', '../../../plugins/' );
	}
	
}



if ( ! defined( 'WAAS1_CHANGE_VERSION_THEME_REPO_DIR' ) ) {
	
	if( is_dir('../themes') ){ //we are on linux
		define( 'WAAS1_CHANGE_VERSION_THEME_REPO_DIR', '../themes' );
	}else{ //we are on windows
		define( 'WAAS1_CHANGE_VERSION_THEME_REPO_DIR', '../../../themes/' );
	}
	
}



if ( ! defined( 'WAAS1_CHANGE_VERSION_PLUGIN_ACTION' ) ) {
	define( 'WAAS1_CHANGE_VERSION_PLUGIN_ACTION', 'waas1_change_version_plugin_action' );
}

if ( ! defined( 'WAAS1_CHANGE_VERSION_PLUGIN_URL' ) ) {
	define( 'WAAS1_CHANGE_VERSION_PLUGIN_URL', 'waas1-change-version' );
}

if ( ! defined( 'WAAS1_CHANGE_VERSION_VERSION' ) ) {
	define( 'WAAS1_CHANGE_VERSION_VERSION', '1.0' );
}





add_action( 'admin_enqueue_scripts', function( $hook ){
	

	if ( 'themes.php' === $hook ) {
		//css
		wp_enqueue_style( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_theme_css', WPMU_PLUGIN_URL . '/assets/css/waas1-change-version-theme.css', array(), WAAS1_CHANGE_VERSION_VERSION );
		
		//js
		wp_enqueue_script( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_theme_js', WPMU_PLUGIN_URL . '/assets/js/waas1-change-version-theme.js', array( 'jquery' ), WAAS1_CHANGE_VERSION_VERSION, true );
	}
	
	
	if( 'plugins_page_waas1-change-version' === $hook || 'appearance_page_waas1-change-version' === $hook ){

		//css
		wp_enqueue_style( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_css', WPMU_PLUGIN_URL . '/assets/css/'.WAAS1_CHANGE_VERSION_PLUGIN_URL.'.css', array(), WAAS1_CHANGE_VERSION_VERSION );
		wp_enqueue_style( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_modal_css', WPMU_PLUGIN_URL . '/assets/css/magnific-popup.css' , array(), WAAS1_CHANGE_VERSION_VERSION );
			
		//js
		wp_enqueue_script( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_modal_js', WPMU_PLUGIN_URL . '/assets/js/jquery.magnific-popup.js', array( 'jquery' ), WAAS1_CHANGE_VERSION_VERSION, true );
		wp_enqueue_script( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_hs', WPMU_PLUGIN_URL . '/assets/js/'.WAAS1_CHANGE_VERSION_PLUGIN_URL.'.js', array( 'jquery' ), WAAS1_CHANGE_VERSION_VERSION, true );
		
	}
	
});









//disable core wordpress updates
add_filter( 'pre_site_transient_update_core', function( $transient ){
	global $wp_version;
	return(object) array(
		'last_checked'=> time(),
		'version_checked'=> $wp_version,
		'updates' => array()
	);
});








//runs when wordpress send and api request for a theme new version
add_filter( 'pre_set_site_transient_update_themes', function( $transient ){
	
	if ( !is_object($transient) ){
		return $transient;
	}
	
	if( isset($transient->response) && is_array($transient->response) ){ //wordpress have checked for a new version and new version is out.
		
		//now check if we are maintaining the package or not.
		foreach( $transient->response as $theme_path=>$theme_data ){
			
			$args = array();
			if( isset($theme_data->slug) ){
				$args['slug'] = $theme_data->slug;
			}else{//build the slug from $theme_path
				$explodedPath = explode( '/', $theme_path );
				$args['slug'] = $explodedPath[0];
			}
			
		
			//now check if we have versions in our repo
			$result = waas1_check_if_we_have_repo( 'theme', $args );
			if( $result == false ){
				continue;
			}
			
			
			//if we are here it means we are maintaing the theme repo ourself.
			//but
			//again check if we symlinked the theme
			$isLink = waas1_change_version_check_if_symlink( 'theme', $args );
			

			if( $isLink ){
				//we are maintaing it and theme is symlinked
				unset( $transient->response[$theme_path] );
			}
			
			
		}
	} //if transient response end
	return $transient;
	
});







//runs when wordpress send and api request for a plugin new version
add_filter( 'pre_set_site_transient_update_plugins', function( $transient ){
	
	if ( !is_object($transient) ){
		return $transient;
	}

	if( isset($transient->response) && is_array($transient->response) ){ //wordpress have checked for a new version and new version is out.
	
		//now check if we are maintaining the package or not.
		foreach( $transient->response as $plugin_path=>$plugin_data ){
			
			
			$args = array();
			if( isset($plugin_data->slug) ){
				$args['slug'] = $plugin_data->slug;
			}else{//build the slug from $plugin_path
				$explodedPath = explode( '/', $plugin_path );
				$args['slug'] = $explodedPath[0];
			}
			
			//now check if we have versions in our repo
			$result = waas1_check_if_we_have_repo( 'plugin', $args );
			if( $result == false ){
				continue;
			}
			
			//if we are here it means we are maintaing the plugin repo ourself.
			//but
			//again check if we symlinked the plugin
			$isLink = waas1_change_version_check_if_symlink( 'plugin', $args );
			if( $isLink ){
				//we are maintaing it and plugin is symlinked
				unset($transient->response[$plugin_path]);
			}
	
		}
		
	} //if transient response end
	
	return $transient;
});







add_filter( 'wp_prepare_themes_for_js', function( $prepared_themes ){
	
	// Multisite check.
	if ( is_multisite() && ( ! is_network_admin() && ! is_main_site() ) ) {
		return $prepared_themes;
	}
	

	foreach( $prepared_themes as $key=>$theme ){
		
		$args = array();
		$args['slug'] = $theme['id'];

		//now check if we have versions in our repo
		$result = waas1_check_if_we_have_repo( 'theme', $args );
		
		if( $result == false ){
			$prepared_themes[$key]['waas1_repo'] = false;
		}else{
			$dirs = waas1_list_repo_version( 'theme', $args );
			if( count($dirs) <= 1 ){
				$prepared_themes[$key]['waas1_repo'] = false;
			}else{
				
				$prepared_themes[$key]['waas1_repo'] = true;
			
				$isLink = waas1_change_version_check_if_symlink( 'theme', $args );
				if( $isLink ){
					$prepared_themes[$key]['waas1_repo_islinked'] = true;
				}else{
					$prepared_themes[$key]['waas1_repo_islinked'] = false;
				}
		
				
				// Base changeversion URL
				$changeVersion_url = 'themes.php?page='.WAAS1_CHANGE_VERSION_PLUGIN_URL.'&type=theme';
				$changeVersion_url = add_query_arg(
											array(
												'installed_version' 		=> urlencode( $theme['version'] ),
												'rollback_name'   			=> urlencode( $theme['name'] ),
												'slug'     					=> urlencode( $theme['id'] ),
												'_wpnonce'       			=> wp_create_nonce( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_nonce' ),
											), $changeVersion_url
										);
				$prepared_themes[$key]['waas1_repo_dirs'] = $dirs;
				
				if( $dirs[0] == '999.999.999.999.999' ){
					$gitRepoThemeVersion = $dirs[1];
				}elseif( $dirs[0] == 'main' ){
					$gitRepoThemeVersion = $dirs[1];
				}else{
					$gitRepoThemeVersion = $dirs[0];
				}
				
				if( $gitRepoThemeVersion == '999.999.999.999.999' ){
					$changeVersionText = '<div class="change-repo-version"><a href="' . esc_url( $changeVersion_url ) . '">Git Change Branch</a></div>';
				}else{
					if( version_compare($gitRepoThemeVersion, $theme['version'], '>' ) ){
						$changeVersionText = '<div class="change-repo-version change-repo-important"><a href="' . esc_url( $changeVersion_url ) . '">New Version Available</a></div>';
					}else{
						$changeVersionText = '<div class="change-repo-version"><a href="' . esc_url( $changeVersion_url ) . '">Git Change Branch</a></div>';
					}
				}
				
				
				// Final Output
				$prepared_themes[$key]['waas1_change_version'] = $changeVersionText;
				
			}
		}
		
	
	}//end foreach
	
	return $prepared_themes; 
	
});





//show a button next to plugin updates within the plugin admin screen
add_filter( 'plugin_action_links', function ( $actions, $plugin_file, $plugin_data, $context ) {
	

	if( !isset($plugin_data['slug']) && !isset($plugin_data['TextDomain']) ){
		return $actions;
	}
	

	// Multisite check.
	if ( is_multisite() && ( ! is_network_admin() && ! is_main_site() ) ) {
		return $actions;
	}
	
	// Must have version.
	if ( ! isset( $plugin_data['Version'] ) ) {
		return $actions;
	}


	$args = array();
	if( isset($plugin_data['slug']) ){
		$args['slug'] = $plugin_data['slug'];
	}else{//build the slug from $plugin_path
		$explodedPath = explode( '/', $plugin_file );
		$args['slug'] = $explodedPath[0];
	}
	
	//override for some plugins
	if( $args['slug'] == 'bp-loader' ){ //buddyboss
		$args['slug'] = 'buddyboss-platform';
		
	}elseif( $args['slug'] == 'sfwd_lms' ){ //learn dash
		$args['slug'] = 'sfwd-lms';
	}
	
	
	
	//now check if we have versions in our repo
	$result = waas1_check_if_we_have_repo( 'plugin', $args );
	if( $result == false ){
		return $actions;
	}
	
	
	//if we are here it means we are maintaing the plugin repo ourself.
	//before removing delete action make sure we really have a symlink otherwise the clinet will be not able to delete the plugin.
	$isLink = waas1_change_version_check_if_symlink( 'plugin', $args );
	if( $isLink ){
		unset($actions['delete']); //now remove the delete action
	}else{
		return $actions;
	}
	
	

	// Base changeversion URL
	$changeVersion_url = 'plugins.php?page='.WAAS1_CHANGE_VERSION_PLUGIN_URL.'&type=plugin&file=' . $plugin_file;
	
	$changeVersion_url = add_query_arg( array(
										'installed_version' 	=> urlencode( $plugin_data['Version'] ),
										'rollback_name'   		=> urlencode( $plugin_data['Name'] ),
										'slug'     				=> urlencode( $args['slug'] ),
										'text_domain'    => urlencode( $plugin_data['TextDomain'] ),
										'_wpnonce'       		=> wp_create_nonce( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_nonce' ),
										), $changeVersion_url );
										
										
	$dirs = waas1_list_repo_version( 'plugin', $args );
	
	if( count($dirs) == 1 ){
		return $actions;
	}
	
	if( $dirs[0] == '999.999.999.999.999' ){
		$gitRepoPluginVersion = $dirs[1];
	}elseif( $dirs[0] == 'main' ){
		$gitRepoPluginVersion = $dirs[1];
	}else{
		$gitRepoPluginVersion = $dirs[0];
	}
	
	if( $gitRepoPluginVersion == '999.999.999.999.999' ){
		$changeVersionText = '<a href="' . esc_url( $changeVersion_url ) . '">Git Change Branch</a>';
	}else{
		if( version_compare($gitRepoPluginVersion, $plugin_data['Version'], '>' ) ){
			$changeVersionText = '<a style="color:red;" href="' . esc_url( $changeVersion_url ) . '">New Version Available</a>';
		}else{
			$changeVersionText = '<a href="' . esc_url( $changeVersion_url ) . '">Git Change Branch</a>';
		}
	}
	
	
	// Final Output
	$actions['waas1_change_version'] = $changeVersionText;
	

	return $actions;
	
}, 20, 4 );












//show the button in plugins page:
add_action( 'admin_menu', function(){
	
	// Only show menu item when necessary (user is interacting with plugin, ie rolling back something)
	if ( isset( $_GET['page'] ) && $_GET['page'] == WAAS1_CHANGE_VERSION_PLUGIN_URL ) {
		
		if( isset($_GET['type']) && $_GET['type'] == 'theme' ){
			add_submenu_page( 'themes.php', 'Change Version', 'Change Version', 'update_plugins',  WAAS1_CHANGE_VERSION_PLUGIN_URL, 'waas1_change_version_build_admin_page' );
		}else{
			add_submenu_page( 'plugins.php', 'Change Version', 'Change Version', 'update_plugins',  WAAS1_CHANGE_VERSION_PLUGIN_URL, 'waas1_change_version_build_admin_page' );
		}
		
	}
	
}, 20 );





//first page
function waas1_change_version_build_admin_page() {
	
	// Permissions check
	if ( ! current_user_can( 'update_plugins' ) ) {
		wp_die( __( 'You do not have sufficient permissions to perform rollbacks for this site.', 'wp-rollback' ) );
	}
	

	// Get the necessary class
	//include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	
	
	
	//its necessary to setup the defaults as this is determine if we need to api call or show the plugins/themes options
	$defaults = array(
		'page'				=> WAAS1_CHANGE_VERSION_PLUGIN_URL,
		'type'				=> '',
		'new_version'   	=> '',
		'file'   			=> '',
	);
	$args = wp_parse_args( $_GET, $defaults );
	

	
	
	if( $args['new_version'] != '' ){
		

		
		if( $args['type'] == 'plugin' ){
			
			check_admin_referer( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_nonce' ); //this will also exit the process
			waas1_change_version_api_call( 'plugin', $args );
			
		}elseif( $args['type'] == 'theme' ){
			
			check_admin_referer( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_nonce' ); //this will also exit the process
			waas1_change_version_api_call( 'theme', $args );
			
		}else{
			
			wp_die( __( 'Change Version is missing necessary parameters to continue. Please contact support.', 'wp-rollback' ) );
			
		}
		
	}else{
		check_admin_referer( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_nonce' ); //this will also exit the process
		waas1_change_version_show_options( $args );
	}
	

}













//plugin last step
function waas1_change_version_api_call( $type, $args ){
	
	
	$dirs = waas1_list_repo_version( $type, $args );
	$linkedWithMain = false;
	
	$html = '';
	$html .= '<div class="wrap"> <div class="wpr-content-wrap">';
	$html .= '<h1>Git Change Branch: '.$args['rollback_name'].'</h1>';
	
	
	//if plugin starts
	if( $type == 'plugin' ){
		
		foreach( $dirs as $dir ){
			if( $dir == '999.999.999.999.999' ){
				$result = readlink( WP_PLUGIN_DIR.'/'.$args['slug'] );
				if( strpos($result, '999.999.999.999.999') !== false ){
					$linkedWithMain = true;
				}
			}
			if( $dir == 'main' ){
				$result = readlink( WP_PLUGIN_DIR.'/'.$args['slug'] );
				if( strpos($result, 'main') !== false ){
					$linkedWithMain = true;
				}
			}
		}
		
		//setup vars
		$backButtonHtml = '<a style="margin-top:5%;" href="'.admin_url( '/plugins.php' ).'" class="button-primary">Back to plugins</a>';
		

		if( $args['installed_version'] == $args['new_version'] && $linkedWithMain == false ){
			$html .= '<p>Plugin is already at version: '.$args['new_version'].'</p>';
			$html .= '<p>No action taken...</p>';
			$html .= '</div></div>';
			$html .= $backButtonHtml;
			echo $html;
			exit(); //make sure to exit from here
		}
	
	
		if( !is_dir(WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR.$args['slug'].'/'.$args['new_version']) ){
			$html .= '<p>Plugin Version not found in our repo! Please contact admin.</p>';
			$html .= '<p>No action taken...</p>';
			$html .= '</div></div>';
			$html .= $backButtonHtml;
			echo $html;
			exit(); //make sure to exit from here
		}
		
		
		//$html .= '<p>Check if the plugin is currently active?</p>';
		//$isActive = is_plugin_active( $args['file'] );
		//if( $isActive ){
		//	$html .= '<span>Yes plugin is currently active.</span>';
		//	$html .= '<p>Deactivating the plugin before version change....</p>';
		//	deactivate_plugins( $args['file'] );
		//	$html .= '<span>Deactivation successful.</span>';
		//}else{
		//	$html .= '<span>Plugin is not currently active.</span>';
		//}
		
		if( $linkedWithMain ){
			$html .= '<p>Changing Version from (main) to ('.$args['new_version'].')</p>';
		}else{
			$html .= '<p>Changing Version from ('.$args['installed_version'].') to ('.$args['new_version'].')</p>';
		}
		

		//$html .= '<p>Removing Version ('.$args['installed_version'].')</p>';
		//if( is_link(WP_PLUGIN_DIR.'/'.$args['slug']) ){
		//	unlink( WP_PLUGIN_DIR.'/'.$args['slug'] );
		//}
	
		
		
	}else{
		
		foreach( $dirs as $dir ){
			if( $dir == '999.999.999.999.999' ){
				$result = readlink( WP_CONTENT_DIR.get_theme_roots().'/'.$args['slug'] );
				if( strpos($result, '999.999.999.999.999') !== false ){
					$linkedWithMain = true;
				}
			}
			if( $dir == 'main' ){
				$result = readlink( WP_CONTENT_DIR.get_theme_roots().'/'.$args['slug'] );
				if( strpos($result, 'main') !== false ){
					$linkedWithMain = true;
				}
			}
		}
		
		//setup vars
		$backButtonHtml = '<a style="margin-top:5%;" href="'.admin_url( '/themes.php' ).'" class="button-primary">Back to themes</a>';
		
		
		if( $args['installed_version'] == $args['new_version'] && $linkedWithMain == false ){
			$html .= '<p>Theme is already at version: '.$args['new_version'].'</p>';
			$html .= '<p>No action taken...</p>';
			$html .= '</div></div>';
			$html .= $backButtonHtml;
			echo $html;
			exit(); //make sure to exit from here
		}
		
		
		if( !is_dir(WAAS1_CHANGE_VERSION_THEME_REPO_DIR.$args['slug'].'/'.$args['new_version']) ){
			$html .= '<p>Theme Version not found in our repo! Please contact admin.</p>';
			$html .= '<p>No action taken...</p>';
			$html .= '</div></div>';
			$html .= $backButtonHtml;
			echo $html;
			exit(); //make sure to exit from here
		}
		

		
	}
	//if theme ends



	$html .= '<p>Installing Version ('.$args['new_version'].')</p>';
	
	$waas1WorkerApi = new waas1WorkerApi( 'site/change-version/', array( 'type'=>$type, 'slug'=>$args['slug'], 'version'=>$args['new_version'] ) );
	if( !$waas1WorkerApi->execute() ){
		
		$html .= '<span>An Error Occurred ID: #344433</span>';
		$html .= '<p>Please Contact System Admin</p>';
		$html .= '</div></div>';
		$html .= $backButtonHtml;
		echo $html;
		exit(); //make sure to exit from here
		
	}
	
	
	$workerApiResponse = json_decode( $waas1WorkerApi->getResponse() );
	if( !$workerApiResponse->status ){
		$html .= '<span>An Error Occurred ID: #344439</span>';
		$html .= '<p>Please Contact System Admin</p>';
		$html .= '</div></div>';
		$html .= $backButtonHtml;
		echo $html;
		exit(); //make sure to exit from here
	}
	
	
	if( $linkedWithMain ){
		$html .= '<p>Changing Version from (main) to ('.$args['new_version'].')</p>';
	}else{
		$html .= '<p>Changing Version from ('.$args['installed_version'].') to ('.$args['new_version'].')</p>';
	}

	$html .= '</div></div>';
	$html .= $backButtonHtml;
	
	echo '<pre>';
	print_r( $html );
	echo '</pre>';
	die;
	
	
	if( $type == 'plugin' ){
		wp_redirect( admin_url( '/plugins.php' ) );
	}else{
		wp_redirect( admin_url( '/themes.php' ) );
	}
	echo $html;
	
}
























//second page
function waas1_change_version_show_options( $args ){
	
	// Ensure we have our necessary query strings
	if ( ( ! isset( $_GET['type'] ) && ! isset( $_GET['theme'] ) ) || ( ! isset( $_GET['type'] ) && ! isset( $_GET['plugin_file'] ) ) ) {
		wp_die( __( 'Change Version is missing necessary parameters to continue. Please contact support.', 'wp-rollback' ) );
	}

	
	$html = '';
	$html .= '<div class="wrap"> <div class="wpr-content-wrap">';
	$html .= '<h1>Git Change Branch</h1>';
	
	$html .= '<p>'. sprintf( 'Please select which %1$s version you would like to rollback to from the releases listed below. You currently have version %2$s installed of %3$s.', '<span class="type">' . ( $args['type'] == 'theme' ? 'theme' : 'plugin' ) . '</span>', '<span class="current-version">' . esc_html( $args['installed_version'] ) . '</span>', '<span class="rollback-name">' . esc_html( $args['rollback_name'] ) . '</span>' ). '</p>';
	
	
	
	if( $args['type'] == 'plugin' ){
		
		//$plugins	= get_plugins();
		$versions 	= waas1_change_version_versions_select( 'plugin', $args );
		
	}elseif( $args['type'] == 'theme' ){
		
		$versions 	= waas1_change_version_versions_select( 'theme', $args );
		
	}else{
		// Fallback check
		wp_die( 'Oh no! We\'re missing required rollback query strings. Please contact support so we can check this bug out and squash it!' );
	}
	

	
	
	if( $args['type'] == 'plugin' ){
		$html .= '<form name="check_for_rollbacks" class="rollback-form" action="'. admin_url( '/plugins.php' ) .'">';
	}else{
		$html .= '<form name="check_for_rollbacks" class="rollback-form" action="'. admin_url( '/themes.php' ) .'">';
	}
	
	
	
	  $html .= '<div class="wpr-versions-wrap">';
	    $html .= $versions;
	  $html .= '</div>';
	  
	  
	  
	  $html .='<div class="wpr-submit-wrap">';
		$html .='<a href="#wpr-modal-confirm" class="magnific-popup button-primary wpr-rollback-disabled">Git Change Branch</a>';
		$html .='<input type="button" value="Cancel" class="button" onclick="location.href='.wp_get_referer().'" />';
	  $html .='</div>';
	  
	  
	  
	  $html .= '
	  <input type="hidden" name="page" value="'.WAAS1_CHANGE_VERSION_PLUGIN_URL.'">
	  <input type="hidden" name="rollback_name" value="' .esc_attr( $args['rollback_name'] ). '">
	  <input type="hidden" name="installed_version" value="' .esc_attr( $args['installed_version'] ). '">
	  ';
	  $html .= wp_nonce_field( WAAS1_CHANGE_VERSION_PLUGIN_ACTION.'_nonce' );
	  
	  $html .= '<input type="hidden" name="type" value="'.esc_attr( $args['type'] ).'">';
	  $html .= '<input type="hidden" name="file" value="'.esc_attr( $args['file'] ).'">';
	  $html .= '<input type="hidden" name="slug" value="'.esc_attr( $args['slug'] ).'">';

	  
	  
	  
	  
	  
	  //build the popup modal
	  $html .='<div id="wpr-modal-confirm" class="white-popup mfp-hide"><div class="wpr-modal-inner">';
	    $html .='<p class="wpr-rollback-intro">Are you sure you want to perform the following version change?</p>';
		
		$html .='<div class="rollback-details">';
		  $html .='<table class="widefat"><tbody>';
		  
		    $html .='<tr>';
			  $html .='<td class="row-title">';
			    $html .='<label for="tablecell">';
				
				if ( $args['type'] == 'plugin' ) {
					$html .='Plugin Name:';
				}else{
					$html .='Theme Name:';
				}

				$html .='</label>';
			  $html .='</td>';
			  $html .='<td><span class="wpr-plugin-name"></span></td>';
			$html .='</tr>';
			
			
			$html .='<tr class="alternate">';
			  $html .='<td class="row-title"><label for="tablecell">Installed Version:</label></td><td><span class="wpr-installed-version"></span></td>';
			$html .='</tr>';
			
			
			$html .='<tr>';
			  $html .='<td class="row-title"><label for="tablecell">New Version:</label></td><td><span class="wpr-new-version"></span></td>';
			$html .='</tr>';
			
		  $html.='</tbody></table>';
		$html .='</div>';
		
		
		//$html .='<div class="wpr-error"><p><strong>Notice:</strong> We strongly recommend you <strong>create a complete backup</strong> of your site.</p></div>';
		
		$html .='
				<input type="submit" value="Git Change Branch" class="button-primary wpr-go" />
				<a href="#" class="button wpr-close">Cancel</a>
			   ';
		
	  $html .='</div></div>';
	  
	  
	  

					
	  
	  
	
	$html .= '</form>';
	
	
	
	
	

	$html .='</div></div>';
	
	echo $html;
	
}//waas1_change_version_show_options ends







function waas1_change_version_check_if_symlink( $type, $args ){
	
	if( $type == 'plugin' ){
		$result = is_link( WP_PLUGIN_DIR.'/'.$args['slug'] );
	}
	
	
	if( $type == 'theme' ){
		$result = is_link( WP_CONTENT_DIR.get_theme_roots().'/'.$args['slug'] );
	}
	
	return $result;
}








function waas1_check_if_we_have_repo( $type, $args ){
	
	
	if( $type == 'plugin' ){
		
		if( file_exists( WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR.$args['slug'].'/.gitworktree' ) ){
			return true;
		}
		if( file_exists( WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR.$args['slug'].'/gitworktree' ) ){
			return true;
		}
		if( file_exists( WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR.$args['slug'].'/gitworktree.txt' ) ){
			return true;
		}
		if( file_exists( WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR.$args['slug'].'/versions.ini' ) ){
			return true;
		}
		return false;
	}
	
	
	if( $type == 'theme' ){
		
		if( file_exists( WAAS1_CHANGE_VERSION_THEME_REPO_DIR.$args['slug'].'/.gitworktree' ) ){
			return true;
		}
		if( file_exists( WAAS1_CHANGE_VERSION_THEME_REPO_DIR.$args['slug'].'/gitworktree' ) ){
			return true;
		}
		if( file_exists( WAAS1_CHANGE_VERSION_THEME_REPO_DIR.$args['slug'].'/gitworktree.txt' ) ){
			return true;
		}
		if( file_exists( WAAS1_CHANGE_VERSION_THEME_REPO_DIR.$args['slug'].'/versions.ini' ) ){
			return true;
		}
		return false;
		
	}
	
}













function waas1_change_version_versions_select( $type, $args ){
	
	$dirs = waas1_list_repo_version( $type, $args );
	
	$html = '<ul class="wpr-version-list">';
	$linkedWithMain = false;
	
	if( $type == 'plugin' ){
		
		foreach( $dirs as $dir ) {
		   if( is_dir(WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR.$args['slug'].'/'.$dir) ){
			   $html .= '<li class="wpr-version-li">';
				 $html .= '<label>';
				   $html .= '<input type="radio" value="' .$dir. '" name="new_version">';
				   $html .=  $dir;
				   
					
					if( $dir == '999.999.999.999.999' ){
						$result = readlink( WP_PLUGIN_DIR.'/'.$args['slug'] );
						if( strpos($result, '999.999.999.999.999') !== false ){
							$linkedWithMain = true;
							$html .= '<span class="current-version">Installed</span>';
						}
					}
					
					if( $dir == 'main' ){
						$result = readlink( WP_PLUGIN_DIR.'/'.$args['slug'] );
						if( strpos($result, 'main') !== false ){
							$linkedWithMain = true;
							$html .= '<span class="current-version">Installed</span>';
						}
					}
				
	
				   if( $args['installed_version'] == $dir ){
					   if( $linkedWithMain ){
							$html .= '<span class="current-version"><--- Base Version</span>';
					   }else{
						   $html .= '<span class="current-version">Installed Version</span>';
					   }
				   }
				   
				 $html .= '</label>';
			   $html .= '</li>';
		   }
		}
		
	}
	
	
	if( $type == 'theme' ){
		
		foreach( $dirs as $dir ) {
		   if( is_dir(WAAS1_CHANGE_VERSION_THEME_REPO_DIR.$args['slug'].'/'.$dir) ){
			   $html .= '<li class="wpr-version-li">';
				 $html .= '<label>';
				   $html .= '<input type="radio" value="' .$dir. '" name="new_version">';
				   $html .=  $dir;
				   
				   if( $dir == '999.999.999.999.999' ){
						$result = readlink( WP_CONTENT_DIR.get_theme_roots().'/'.$args['slug'] );
						if( strpos($result, '999.999.999.999.999') !== false ){
							$linkedWithMain = true;
							$html .= '<span class="current-version">Installed</span>';
						}
					}
					
					if( $dir == 'main' ){
						$result = readlink( WP_CONTENT_DIR.get_theme_roots().'/'.$args['slug'] );
						if( strpos($result, 'main') !== false ){
							$linkedWithMain = true;
							$html .= '<span class="current-version">Installed</span>';
						}
					}
					
				   if( $args['installed_version'] == $dir ){
					   if( $linkedWithMain ){
							$html .= '<span class="current-version"><--- Base Version</span>';
					   }else{
						   $html .= '<span class="current-version">Installed Version</span>';
					   }
				   }
				   
				   
				 $html .= '</label>';
			   $html .= '</li>';
		   }
		}
		
	}
	
	
	$html .= '</ul>';
	return $html;
}










function waas1_list_repo_version( $type, $args ){
	
	if( $type == 'plugin' ){
		
		$dirToScan = WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR.$args['slug'].'/';
		
		if( file_exists($dirToScan.'.gitworktree') ){
			$file = $dirToScan.'.gitworktree';
			
		}elseif( file_exists($dirToScan.'gitworktree') ){
			$file = $dirToScan.'gitworktree';
			
		}elseif( file_exists($dirToScan.'gitworktree.txt') ){
			$file = $dirToScan.'gitworktree.txt';
		}
		
		if( file_exists($file) ){
			
			$scanDir = scandir( $dirToScan );
			$scanDir = array_diff( $scanDir, array( '.', '..' ) );
			
			$dirBuilder = array();
			foreach( $scanDir as $dir ){
				if( is_dir(WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR.$args['slug'].'/'.$dir) ){
					array_push( $dirBuilder, $dir );
				}
			}
			
			sort($dirBuilder, SORT_NUMERIC);
			$dirBuilder = array_reverse( $dirBuilder );
			
			
			if( in_array('main', $dirBuilder) ){
				array_pop( $dirBuilder ); //remove the last element as main will always be last
				array_unshift( $dirBuilder , 'main' ); //add the main to the very top now
			}
			
			return $dirBuilder;
		}
		
		//old version file way
		$file = WAAS1_CHANGE_VERSION_PLUGIN_REPO_DIR.$args['slug'].'/versions.ini';
		if( file_exists($file) ){
			$versions = parse_ini_file( $file, false, INI_SCANNER_TYPED );
			return $versions['versions'];
		}

	}
	
	
	
	if( $type == 'theme' ){
		
		$dirToScan = WAAS1_CHANGE_VERSION_THEME_REPO_DIR.$args['slug'].'/';
		
		if( file_exists($dirToScan.'.gitworktree') ){
			$file = $dirToScan.'.gitworktree';
			
		}elseif( file_exists($dirToScan.'gitworktree') ){
			$file = $dirToScan.'gitworktree';
			
		}elseif( file_exists($dirToScan.'gitworktree.txt') ){
			$file = $dirToScan.'gitworktree.txt';
		}
		
		if( file_exists($file) ){
			
			$scanDir = scandir( $dirToScan );
			$scanDir = array_diff( $scanDir, array( '.', '..' ) );
			
			$dirBuilder = array();
			foreach( $scanDir as $dir ){
				if( is_dir(WAAS1_CHANGE_VERSION_THEME_REPO_DIR.$args['slug'].'/'.$dir) ){
					array_push( $dirBuilder, $dir );
				}
			}
			

			sort($dirBuilder, SORT_NUMERIC);
			$dirBuilder = array_reverse( $dirBuilder );
			
			if( in_array('main', $dirBuilder) ){
				array_pop( $dirBuilder ); //remove the last element as main will always be last
				array_unshift( $dirBuilder , 'main' ); //add the main to the very top now
			}
			
			return $dirBuilder;
		}
		
		
		//old version file way
		$file = WAAS1_CHANGE_VERSION_THEME_REPO_DIR.$args['slug'].'/versions.ini';
		if( file_exists($file) ){
			$versions = parse_ini_file( $file, false, INI_SCANNER_TYPED );
			return $versions['versions'];
		}
		
	}
	
	return false;
}











?>