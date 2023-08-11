<?php
/**
 * Description
 *
 * @package    init.php
 * @since      8.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

/*
* Elementor compatibilities
* ******************************************************************* */
if ( defined('ELEMENTOR_VERSION')) {
	require_once( apply_filters( 'etheme_file_url', ETHEME_CODE . 'compatibility/elementor.php' ) );
}

/*
* WPBakery compatibilities
* ******************************************************************* */
if ( defined('WPB_VC_VERSION') ) {
	require_once( apply_filters( 'etheme_file_url', ETHEME_CODE . 'compatibility/wpbakery.php' ) );
}

/*
* Dokan compatibilities
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'compatibility/dokan.php') );

/*
* Bbpress compatibilities
* ******************************************************************* */
if ( class_exists( 'bbPress' ) ) {
	require_once( apply_filters( 'etheme_file_url', ETHEME_CODE . 'compatibility/bbpress.php' ) );
}

/*
* WCMarketplace compatibilities
* ******************************************************************* */
if ( class_exists('WCMp') ) {
	require_once( apply_filters( 'etheme_file_url', ETHEME_CODE . 'compatibility/wcmp.php' ) );
}

/*
* Yoast compatibilities
* ******************************************************************* */
if ( defined('WPSEO_VERSION') ) {
	require_once( apply_filters( 'etheme_file_url', ETHEME_CODE . 'compatibility/wpseo.php' ) );
}

/*
* WPML compatibilities
* ******************************************************************* */
if ( defined('WPML_TM_VERSION') && defined('WPML_ST_VERSION') ) {
	require_once( apply_filters( 'etheme_file_url', ETHEME_CODE . 'compatibility/wpml.php' ) );
}