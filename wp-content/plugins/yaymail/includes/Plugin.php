<?php
namespace YayMail;

use YayMail\Page\Source\CustomPostType;

defined( 'ABSPATH' ) || exit;
/**
 * Plugin activate/deactivate logic
 */
class Plugin {

	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			$versionCurrent = YAYMAIL_VERSION;
			$versionOld     = get_option( 'yaymail_version' );
			if ( $versionCurrent != $versionOld ) {
				self::activate();
			}
		}

		return self::$instance;
	}

	/**
	 *
	 * Plugin activated hook
	 */
	public static function activate() {
		Helper\ActivePlugin::getInstance();
	}

	/**
	 *
	 * Plugin deactivate hook
	 */
	public static function deactivate() {

	}
}
