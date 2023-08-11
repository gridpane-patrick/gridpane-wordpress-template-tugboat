<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_Store_Locator_Settings' ) ) {

	class YITH_Store_Locator_Settings {

		/**
		 * @var $_options array options array
		 */
		private $_options = array();

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_Store_Locator_Settings
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_Store_Locator_Settings
		 * @since 1.0.0
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alessio Torrisi
		 */
		private function __construct() {
		}

		/**
		 * Load plugin options
		 *
		 * @since   1.0.0
		 *
		 * @param   $parent string
		 *
		 * @return  array
		 * @author  Alessio Torrisi
		 */
		private function get_options( $parent ) {
			if ( ! isset( $this->_options[ $parent ] ) ) {

				$options = get_option( "yit_{$parent}_options" );

				if ( $options == '' ) {
					$options = array();
					update_option( "yit_{$parent}_options", array() );
				}

				$this->_options[ $parent ] = $options;

			}

			return $this->_options[ $parent ];
		}

		/**
		 * Get selected option
		 *
		 * @since   1.0.0
		 *
		 * @param   $parent  string
		 * @param   $key     string
		 * @param   $default mixed
		 *
		 * @return  mixed
		 * @author  Alessio Torrisi
		 */
		public function get_option( $parent, $key, $default = false ) {
			$options = $this->get_options( $parent );
            $option_value = is_array( $options ) && array_key_exists( $key, $options ) ? $options[ $key ] : $default;
			return apply_filters( 'yith_sl_store_locator_options', $option_value, $key, $default ) ;
		}



	}

}