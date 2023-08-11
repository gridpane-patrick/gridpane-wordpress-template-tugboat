<?php

namespace Vibe\Split_Orders;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Sets up Settings page and provides access to setting values
 *
 * @since 1.4.0
 */
class Settings {

	/**
	 * Creates an instance and sets up the hooks to integrate with the admin
	 */
	public function __construct() {
		add_filter( 'woocommerce_get_sections_advanced', array( __CLASS__, 'add_settings_page' ) );
		add_filter( 'woocommerce_get_settings_advanced', array( __CLASS__, 'add_settings' ), 10, 2 );
	}

	/**
	 * Adds a section to the advanced tab
	 *
	 * @param array $sections The existing settings sections on the advanced tab
	 *
	 * @return array The sections with the split-orders settings section added
	 */
	public static function add_settings_page( array $sections ) {
		$sections['split-orders'] = __( 'Split orders', 'split-orders' );

		return $sections;
	}

	/**
	 * Adds setting fields to the split-orders section of the settings
	 *
	 * @param array  $settings        The current settings
	 * @param string $current_section The name of the current section of settings
	 *
	 * @return array The settings fields including split orders settings if the current section is 'split-orders'
	 */
	public static function add_settings( array $settings, $current_section ) {
		if ( 'split-orders' != $current_section ) {
			return $settings;
		}

		$settings[] = array(
			'name' => __( 'Split orders', 'split-orders' ),
			'type' => 'title',
			'desc' => __( 'The following options are used to configure the Split Orders extension.', 'split-orders' )
		);

		$settings[] = array(
			'name'     => __( 'Additional fields', 'split-orders' ),
			'desc_tip' => __( 'These fields will be copied to the new order created by a split in addition to the standard fields.<br /><br />
							   Input each field on a new line, or separated by a comma.', 'split-orders' ),
			'id'       => Split_Orders::hook_prefix( 'meta_fields' ),
			'type'     => 'textarea',
			'css'      => 'min-width: 50%; height: 100px;'
		);

		$settings = apply_filters( Split_Orders::hook_prefix( 'settings' ), $settings );

		$settings[] = array( 'type' => 'sectionend', 'id' => 'split-orders' );

		return $settings;
	}

	/**
	 * Fetches and returns the meta fields setting after cleaning it and splitting into an array
	 *
	 * @return array The meta fields setting cleaned up
	 */
	public static function meta_fields() {
		$option = get_option( Split_Orders::hook_prefix( 'meta_fields' ), '' );

		// Split at commas and new-line characters
		$fields = preg_split( '/[\n,]/', $option );

		// Trim
		$fields = array_map( 'trim', $fields );

		// Remove blanks
		$fields = array_filter( $fields );

		// Remove any duplicates
		$fields = array_unique( $fields );

		return array_values( $fields );
	}
}
