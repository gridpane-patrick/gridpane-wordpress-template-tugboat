<?php defined( 'ABSPATH' ) || exit;
/**
 * Check for plugin updates
 *
 * @version 1.0.0
 * @since   1.0.4
 */

class XStore_White_Label_Branding_Update {
	/**
	 * Plugin slug.
	 * Must be the same for
	 * main plugin file name, plugin archive name, part of plugin update transient name,
	 * database plugin name, plugin folder name, plugin text domain.
	 *
	 * @access   private
	 *
	 * @version  1.0.0
	 * @since    1.0.4
	 */
	private $plugin_slug = 'xstore-white-label-branding';

	/**
	 * Constructor.
	 *
	 * @access   public
	 *
	 * @version  1.0.0
	 * @since    1.0.4
	 */
	public function __construct() {
		add_filter('pre_set_site_transient_update_plugins', array( $this, 'xstore_pre_set_site_transient_update_plugins' ) );
	}

	/**
	 * Setup new update data.
	 *
	 * @access   public
	 *
	 * @param $transient {object} plugins update info
	 *
	 * @return object
	 * @version  1.0.0
	 * @since    1.0.4
	 */
	public function xstore_pre_set_site_transient_update_plugins($transient): object {
		if ( ! defined( 'ETHEME_API' ) || ! defined( 'ETHEME_THEME_SLUG' ) ) return $transient;
		if ( $this->white_label_check() ) return $transient;

		$latest_version = $this->get_latest_version();

		if ( ! $latest_version ) return $transient;

		if ( version_compare( XSTORE_WHITE_LABEL_BRANDING_VERSION, $latest_version, '<' ) ) {
			$plugin = new stdClass();
			$plugin->slug = $this->plugin_slug;
			$plugin->plugin = $this->plugin_slug.'/'.$this->plugin_slug.'.php';
			$plugin->new_version = $latest_version;
			$plugin->url = 'http://8theme.com/demo/xstore/change-log.php';
			$plugin->package = $this->get_file_url();
			$plugin->tested = '5.7';
			$plugin->icons = Array(
				'2x' =>esc_url( XSTORE_WHITE_LABEL_BRANDING_PLUGIN_URL . '/images/256x256.png' ),
				'1x' =>esc_url( XSTORE_WHITE_LABEL_BRANDING_PLUGIN_URL . '/images/128x128.png' )
			);
			$transient->response[$this->plugin_slug.'/'.$this->plugin_slug.'.php'] = $plugin;
		}
		return $transient;
	}

	/**
	 * Get latest version.
	 *
	 * @access   protected
	 *
	 * @version  1.0.0
	 * @since    1.0.4
	 */
	protected function get_latest_version() {
		$transient =  get_transient($this->plugin_slug.'-last-version');

		if ( !$transient || isset( $_GET['force-check'] ) && $_GET['force-check'] == '1') {
			$url = ETHEME_API . 'info/' . ETHEME_THEME_SLUG . '?plugin=' . $this->plugin_slug;
			$response = wp_remote_get( $url );
			$response_code = wp_remote_retrieve_response_code( $response );

			if( $response_code != '200' ) return false;

			$response = json_decode( wp_remote_retrieve_body( $response ) );
			if( ! isset( $response ) || ! isset( $response->plugin_version ) ) {
				return false;
			} else {
				set_transient( $this->plugin_slug.'-last-version', $response->plugin_version, DAY_IN_SECONDS*2  );
				return $response->plugin_version;
			}
		} else {
			return $transient;
		}
	}

	/**
	 * Get file url.
	 *
	 * @access   protected
	 *
	 * @return string
	 * @version  1.0.0
	 * @since    1.0.4
	 */
	protected function get_file_url(): string {
		$data  = get_option( 'etheme_activated_data' );
		$token = '?token=' . $data['api_key'];
	    return apply_filters( 'etheme_plugin_url', ETHEME_API . 'files/get/' . $this->plugin_slug . '.zip' . $token );
	}

	/**
	 * White label "disable update" check.
	 *
	 * @access   protected
	 *
	 * @return bool
	 * @version  1.0.0
	 * @since    1.0.4
	 */
	protected function white_label_check(): bool {
		$xstore_branding_settings = get_option( 'xstore_white_label_branding_settings', array() );

		return (
			count($xstore_branding_settings)
			&& isset($xstore_branding_settings['control_panel'])
			&& isset($xstore_branding_settings['control_panel']['hide_updates'])
			&& $xstore_branding_settings['control_panel']['hide_updates'] == 'on'
		);
	}
}

new XStore_White_Label_Branding_Update();