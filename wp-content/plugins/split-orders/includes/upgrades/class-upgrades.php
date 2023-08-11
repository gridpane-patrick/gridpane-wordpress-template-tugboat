<?php

namespace Vibe\Split_Orders\Upgrades;

use Vibe\Split_Orders\Split_Orders;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Manages upgrade routines
 *
 * @since 1.4.0
 */
class Upgrades {

	// Upgrade routines in order from lowest to highest
	private static $upgrades = array(
		'1.4.0' => Upgrade_140::class
	);

	/**
	 * Creates an instance and sets up the hooks to check if upgrade routines need running
	 */
	public function __construct() {
		add_action( 'admin_init', array( __CLASS__, 'run' ) );
	}

	/**
	 * Runs any outstanding upgrade routines and sets the database version to match current plugin version
	 */
	public static function run() {
		$current_version = Split_Orders::instance()->get_version();
		$db_version = self::db_version();

		foreach ( self::$upgrades as $version => $upgrade ) {
			if ( version_compare( $db_version, $version, '<' ) ) {
				$upgrade_routine = new $upgrade();
				$upgrade_routine->run();

				self::update_db_version( $version );

				$db_version = $version;
			}
		}

		// Make sure the database version is set to the current latest
		if ( version_compare( $db_version, $current_version, '<' ) ) {
			static::update_db_version();
		}
	}

	/**
	 * Returns the current version as stored in the database
	 *
	 * @return string the database version in the format x.x.x
	 */
	public static function db_version() {
		return get_option( Split_Orders::hook_prefix( 'version' ), '0.0.0' );
	}

	/**
	 * Overwrites the version number in the database
	 *
	 * @param string $version The version number to set or null if the current plugin version should be used
	 */
	public static function update_db_version( $version = null ) {
		update_option( Split_Orders::hook_prefix( 'version' ), is_null( $version ) ? Split_Orders::instance()->get_version() : $version );
	}
}
