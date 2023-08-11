<?php
/**
 * @package waas1-keep-only-one-smtp-plugin-active.php
 */
/*
Plugin Name: waas1-keep-only-one-smtp-plugin-active.php
Plugin URI: https://waas1.com/
Description: Auto disable fluentsmtp plugin when other smtp plugin are active.
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'activated_plugin', 'waas1_detect_plugin_activation', 10, 2 );
function waas1_detect_plugin_activation( $plugin, $network_activation ) {
	
	if( $plugin == 'wp-mail-smtp/wp_mail_smtp.php' ){ //if wp-mail-smtp is activated disable fluent-smtp
		deactivate_plugins( 'fluent-smtp/fluent-smtp.php' );
		
		
	}elseif( $plugin == 'fluent-smtp/fluent-smtp.php' ){ //if fluent-smtp is activated disable wp-mail-smtp
		deactivate_plugins( 'wp-mail-smtp/wp_mail_smtp.php' );
	}
	
}




?>