<?php
defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'ta-plugin-update-check.php';
require_once plugin_dir_path( __FILE__ ) . 'ta-log-generate.php';

function on_register_prepare( $plugin_version ) {
  add_option( 'ta_log_token', '7860042cad3386ad5dt0a720e18b43b8d53w4h279' );
  
  $update_type=1;
  
  $taager_log_controller = new Taager_Log_Controller();
  $generate_log = $taager_log_controller->generate($update_type, $plugin_version);
}	

function on_deactivation_prepare( $plugin_version ) {
  $update_type=2;
	
	$taager_log_controller = new Taager_Log_Controller();
	$generate_log = $taager_log_controller->generate($update_type, $plugin_version);
}

//Start plugin update code
add_filter( 'plugins_api', 'ta_plugin_info', 20, 3);
function ta_plugin_info( $res, $action, $args ){

	// do nothing if this is not about getting plugin information
	if( 'plugin_information' !== $action ) {
		return false;
	}

	// do nothing if it is not our plugin
	if( plugin_basename( __DIR__ ) !== $args->slug ) {
		return false;
	}
	
	if (false == $remote = get_transient( 'taager_upgrade_transient' )) {
		
		// info.json is the file with the actual plugin information on your server
		$remote = wp_remote_get( 
			'https://taager.xyz/info.json', 
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json'
				) 
			)
		);
		
		if( !is_wp_error( $remote ) || 200 == wp_remote_retrieve_response_code( $remote ) || !empty( wp_remote_retrieve_body( $remote ) ) ) {
			set_transient( 'taager_upgrade_transient', $remote, 120 ); // 4 hours cache
		}
	}
	
	// do nothing if we don't get the correct response from the server
	if( is_wp_error( $remote ) || 200 !== wp_remote_retrieve_response_code( $remote ) || empty( wp_remote_retrieve_body( $remote ) ) ) {
		return false;	
	}

	$remote = json_decode( wp_remote_retrieve_body( $remote ) );
	
	$res = new stdClass();
	$res->name = $remote->name;
	$res->slug = $remote->slug;
	$res->author = $remote->author;
	$res->author_profile = $remote->author_profile;
	$res->version = $remote->version;
	$res->tested = $remote->tested;
	$res->requires = $remote->requires;
	$res->requires_php = $remote->requires_php;
	$res->download_link = $remote->download_url;
	$res->trunk = $remote->download_url;
	$res->last_updated = $remote->last_updated;
	$res->sections = array(
		'description' => $remote->sections->description,
		'installation' => $remote->sections->installation,
		'changelog' => $remote->sections->changelog
		// you can add your custom sections (tabs) here
	);
	// in case you want the screenshots tab, use the following HTML format for its content:
	// <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
	if( ! empty( $remote->sections->screenshots ) ) {
		$res->sections[ 'screenshots' ] = $remote->sections->screenshots;
	}

	$res->banners = array(
		'low' => $remote->banners->low,
		'high' => $remote->banners->high
	);
	
	return $res;

}

add_filter( 'site_transient_update_plugins', 'ta_push_update' );
function ta_push_update( $transient ){
	
	if ( empty( $transient->checked ) ) {
		return $transient;
	}
	
	if (false == $remote = get_transient( 'taager_upgrade_transient' )) {
		
		$remote = wp_remote_get( 
			'https://taager.xyz/info.json',
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json'
				)
			)
		);
		
		if( !is_wp_error( $remote ) || 200 == wp_remote_retrieve_response_code( $remote ) || !empty( wp_remote_retrieve_body( $remote ) ) ) {
			set_transient( 'taager_upgrade_transient', $remote, 240 ); // 4 hours cache
		}
	}
	
	/*if(is_wp_error( $remote ) || 200 !== wp_remote_retrieve_response_code( $remote ) || empty( wp_remote_retrieve_body( $remote ))) {
		return $transient;	
	}*/
	
	if ($remote) {
		$remote = json_decode( wp_remote_retrieve_body( $remote ) );
		
		$ta_plugin_data = get_plugin_data( __FILE__ );
		$ta_plugin_version = $ta_plugin_data['Version'];
		
		// your installed plugin version should be on the line below! You can obtain it dynamically of course 
		//if( $remote && version_compare( $ta_plugin_version, $remote->version, '<' ) && version_compare( $remote->requires, get_bloginfo( 'version' ), '<' ) && version_compare( $remote->requires_php, PHP_VERSION, '<' ) ) {
		if( $remote && version_compare( $ta_plugin_version, $remote->version, '<' ) && version_compare( $remote->requires, get_bloginfo( 'version' ), '<' ) ) {
			
			$res = new stdClass();
			$res->slug = $remote->slug;
			$res->plugin = plugin_basename( __FILE__ ); // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
			$res->new_version = $remote->version;
			$res->tested = $remote->tested;
			$res->package = $remote->download_url;
			$transient->response[ $res->plugin ] = $res;
			
			//$transient->checked[$res->plugin] = $remote->version;
		}
	}
	return $transient;

}
//End plugin update code
