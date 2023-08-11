<?php
! defined( 'ABSPATH' ) && exit; // Exit if accessed directly

/**
 * Class YITH_POS_Integration
 *
 * @abstract
 * @author  Leanza Francesco <leanzafrancesco@gmail.com>
 * @since   1.0.6
 */
abstract class YITH_POS_Integration {

	/** @var YITH_POS_Integration */
	protected static $_instance;

	/** @var bool true if has the plugin active */
	protected $_plugin_active = false;

	/**
	 * Singleton implementation
	 *
	 * @param $plugin_active
	 * @param $integration_active
	 *
	 * @return YITH_POS_Integration
	 */
	public static function get_instance( $plugin_active ) {
		return ! is_null( static::$_instance ) ? static::$_instance : static::$_instance = new static( $plugin_active );
	}

	/**
	 * Constructor
	 *
	 * @param bool $plugin_active
	 *
	 * @access protected
	 */
	protected function __construct( $plugin_active ) {
		$this->_plugin_active = ! ! $plugin_active;
	}

	/**
	 * return true if the plugin is active
	 *
	 * @return bool
	 */
	public function has_plugin_active() {
		return ! ! $this->_plugin_active;
	}
	/**
	 * return true if the integration is active
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->has_plugin_active();
	}

}