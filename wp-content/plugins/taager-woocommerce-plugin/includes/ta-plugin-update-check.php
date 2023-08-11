<?php
defined( 'ABSPATH' ) || exit;


if( ! class_exists( 'TaagerUpdateChecker' ) ) {

	class TaagerUpdateChecker{
		
		public $plugin_slug;
		public $version;
		public $cache_key;
		public $cache_allowed;

		public function __construct() {
			
			//$get_plugin_slug = plugin_basename( __DIR__ );
			//$get_plugin_slug = explode("/",$get_plugin_slug);
			
			$get_plugin_slug = basename( plugin_dir_path(  dirname( __FILE__ , 1 ) ) );
			
			$plugin_path = WP_PLUGIN_DIR . '/'. $get_plugin_slug . '/taager-api.php';
			$ta_plugin_data = get_plugin_data( $plugin_path );
			$ta_plugin_version = $ta_plugin_data['Version'];
			
			$this->plugin_slug = $get_plugin_slug;
			$this->version = $ta_plugin_version;
			$this->cache_key = 'taager_custom_upd';
			$this->cache_allowed = true;

			add_filter( 'plugins_api', array( $this, 'info' ), 20, 3 );
			add_filter( 'site_transient_update_plugins', array( $this, 'update' ) );
			add_action( 'upgrader_process_complete', array( $this, 'purge' ), 10, 2 );

		}

		public function request(){

			$remote = get_transient( $this->cache_key );
			
			if( false === $remote || ! $this->cache_allowed ) {
				
				$remote = wp_remote_get(
					'https://taager.xyz/info.json',
					array(
						'timeout' => 10,
						'headers' => array(
							'Accept' => 'application/json'
						)
					)
				);

				if(
					is_wp_error( $remote )
					|| 200 !== wp_remote_retrieve_response_code( $remote )
					|| empty( wp_remote_retrieve_body( $remote ) )
				) {
					return false;
				}
				
				set_transient( $this->cache_key, $remote, 60 );

			}

			$remote = json_decode( wp_remote_retrieve_body( $remote ) );
			//echo "<pre>"; print_r($remote); exit();
			
			return $remote;

		}


		function info( $res, $action, $args ) {

			// print_r( $action );
			// print_r( $args );

			// do nothing if you're not getting plugin information right now
			if( 'plugin_information' !== $action ) {
				return false;
			}

			// do nothing if it is not our plugin
			if( $this->plugin_slug !== $args->slug ) {
				return false;
			}

			// get updates
			$remote = $this->request();

			if( ! $remote ) {
				return false;
			}

			$res = new stdClass();

			$res->name = $remote->name;
			$res->slug = $remote->slug;
			$res->version = $remote->version;
			$res->tested = $remote->tested;
			$res->requires = $remote->requires;
			$res->author = $remote->author;
			$res->author_profile = $remote->author_profile;
			$res->download_link = $remote->download_url;
			$res->trunk = $remote->download_url;
			$res->requires_php = $remote->requires_php;
			$res->last_updated = $remote->last_updated;

			$res->sections = array(
				'description' => $remote->sections->description,
				'installation' => $remote->sections->installation,
				'changelog' => $remote->sections->changelog
			);

			if( ! empty( $remote->banners ) ) {
				$res->banners = array(
					'low' => $remote->banners->low,
					'high' => $remote->banners->high
				);
			}

			return $res;

		}

		public function update( $transient ) {
			
			if ( empty($transient->checked ) ) {
				return $transient;
			}

			$remote = $this->request();
			
			if(
				$remote
				&& version_compare( $this->version, $remote->version, '<' )
				&& version_compare( $remote->requires, get_bloginfo( 'version' ), '<' )
			) {
				
				
				$get_plugin_slug2 = basename( plugin_dir_path(  dirname( __FILE__ , 1 ) ) );
				$plugin_path2 = $get_plugin_slug2 . '/taager-api.php';
			
				$res = new stdClass();
				$res->slug = $this->plugin_slug;
				$res->plugin = $plugin_path2;
				$res->new_version = $remote->version;
				$res->tested = $remote->tested;
				$res->package = $remote->download_url;
				
				$transient->response[ $res->plugin ] = $res;

			}
			
			//echo "<pre>"; print_r($transient); exit();
			return $transient;

		}

		public function purge($upgrader_object, $options){
			
			$remote = $this->request();
			
			update_option( 'ta_log_token', '7860042cad3386ad5dt0a720e18b43b8d53w4h279' );
			$ta_get_plugin_slug = basename( plugin_dir_path(  dirname( __FILE__ , 1 ) ) );
			$ta_plugin_path = $ta_get_plugin_slug . '/taager-api.php';
			
			if (
				$this->cache_allowed
				&& 'update' === $options['action']
				&& 'plugin' === $options[ 'type' ]
			) {
				// just clean the cache when new plugin version is installed
				delete_transient( $this->cache_key );
			}
			
			if (
				'update' === $options['action']
				&& 'plugin' === $options[ 'type' ]
			) {
				foreach( $options['plugins'] as $plugin ) {
					if( $plugin == $ta_plugin_path ) {
						$update_type=3;
						$ta_version = $remote->version;
						$taager_log_controller = new Taager_Log_Controller();
						$generate_log = $taager_log_controller->generate($update_type, $ta_version);
					}
				}
			}
		}
	}

	new TaagerUpdateChecker();

}
?>
