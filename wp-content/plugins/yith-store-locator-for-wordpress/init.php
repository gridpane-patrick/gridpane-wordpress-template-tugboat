<?php
/**
 * Plugin Name: YITH Store Locator for WordPress
 * Plugin URI: https://yithemes.com/themes/plugins/yith-store-locator-for-wordpress/
 * Description: YITH Store Locator helps your customers to find your stores quickly and easily.
 * Version: 1.0.2
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-store-locator
 * Domain Path: /languages/
 * @author YITH
 * @package YITH Store Locator for WordPress
 * @version 1.0.2
 */
/*  Copyright 2020  YITH  ( email: plugins@yithemes.com )

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

defined( 'YITH_SL' )					|| define( 'YITH_SL', true );
defined( 'YITH_SL_VERSION' )			|| define( 'YITH_SL_VERSION', '1.0.2' );
defined( 'YITH_SL_INIT' ) 			|| define( 'YITH_SL_INIT', plugin_basename( __FILE__ ) );
defined( 'YITH_SL_FILE' ) 			|| define( 'YITH_SL_FILE', __FILE__ );
defined( 'YITH_SL_URL' ) 			    || define( 'YITH_SL_URL', plugin_dir_url( __FILE__ ) );
defined( 'YITH_SL_PATH' )			    || define( 'YITH_SL_PATH', plugin_dir_path( __FILE__ ) );
defined( 'YITH_SL_TEMPLATE_PATH' )	|| define( 'YITH_SL_TEMPLATE_PATH', YITH_SL_PATH . 'templates/' );
defined( 'YITH_SL_THEME_PATH' )	    || define( 'YITH_SL_THEME_PATH', get_template_directory() . '/yith-store-locator/' );
defined( 'YITH_SL_ASSETS_URL' )		|| define( 'YITH_SL_ASSETS_URL', YITH_SL_URL . 'assets/' );
defined( 'YITH_SL_SLUG' )			    || define( 'YITH_SL_SLUG', 'yith-store-locator-for-wordpress' );
defined( 'YITH_SL_SECRET_KEY' )		|| define( 'YITH_SL_SECRET_KEY', '12345' );
defined( 'YITH_SL_DIR' )		        || define( 'YITH_SL_DIR', plugin_dir_path( __FILE__ ) );
defined( 'YITH_SL_DEBUG' )	        || define( 'YITH_SL_DEBUG', false );
defined( 'YITH_SL_FILTERS_TABLE' )	|| define( 'YITH_SL_FILTERS_TABLE', 'yith_sl_filters_taxonomies' );
defined( 'YITH_SL_DB_VERSION' )	    || define( 'YITH_SL_DB_VERSION', '1.0' );


/* Plugin Framework Version Check */
if( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_SL_PATH . 'plugin-fw/init.php' ) ) {
	require_once( YITH_SL_PATH . 'plugin-fw/init.php' );
}

yit_maybe_plugin_fw_loader( YITH_SL_PATH  );

function yith_sl_init() {

	load_plugin_textdomain( 'yith-store-locator', false, dirname( YITH_SL_INIT ). '/languages/' );

	require_once( 'includes/class.yith-store-locator.php' );
    require_once( 'includes/class.yith-sl-store.php');
    require_once( 'includes/class.yith-store-locator-shortcodes.php');
    require_once( 'includes/class.yith-store-locator-settings.php' );
    require_once( 'includes/class.yith-store-locator-filters-taxonomies.php' );
    require_once( 'includes/functions.php' );
    YITH_Store_Locator();
}

add_action( 'plugins_loaded', 'yith_sl_init', 11 );