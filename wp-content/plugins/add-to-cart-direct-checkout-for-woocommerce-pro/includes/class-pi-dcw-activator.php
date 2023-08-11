<?php

/**
 * Fired during plugin activation
 *
 * @link       piwebsolution.com
 * @since      1.0.0
 *
 * @package    Pi_Dcw
 * @subpackage Pi_Dcw/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Pi_Dcw
 * @subpackage Pi_Dcw/includes
 * @author     PI Websolution <sales@piwebsolution.com>
 */
class Pi_Dcw_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		add_option('pi_dcw_do_activation_redirect', true);
	}

}
