<?php
/**
 * @package waas1-tenant-lifecycle-hooks
 */
/*
Plugin Name: waas1-tenant-lifecycle-hooks.php
Plugin URI: https://waas1.com/
Description: Tenant Lifecycle hooks
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//if the call is not from "wp-cli" don't run the code below
if ( ! defined('WP_CLI') ){
	return;
}



	
WP_CLI::add_command( 'waas1-tenant post-created', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_post_created', $assoc_args );
});

WP_CLI::add_command( 'waas1-tenant pre-delete', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_pre_delete', $assoc_args );
});



//tenant activate
WP_CLI::add_command( 'waas1-tenant pre-activate', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_pre_activate', $assoc_args );
});
WP_CLI::add_command( 'waas1-tenant post-activate', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_post_activate', $assoc_args );
});




//tenant deactivate
WP_CLI::add_command( 'waas1-tenant pre-deactivate', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_pre_deactivate', $assoc_args );
});
WP_CLI::add_command( 'waas1-tenant post-deactivate', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_post_deactivate', $assoc_args );
});




//php version
WP_CLI::add_command( 'waas1-tenant pre-php-ver-change', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_pre_php_ver_change', $assoc_args );
});
WP_CLI::add_command( 'waas1-tenant post-php-ver-change', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_post_php_ver_change', $assoc_args );
});




//wp version
WP_CLI::add_command( 'waas1-tenant pre-wp-ver-change', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_pre_wp_ver_change', $assoc_args );
});
WP_CLI::add_command( 'waas1-tenant post-wp-ver-change', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_post_wp_ver_change', $assoc_args );
});


//restrictions group changed
WP_CLI::add_command( 'waas1-tenant pre-restrictions-group-change', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_pre_restrictions_group_change', $assoc_args );
});
WP_CLI::add_command( 'waas1-tenant post-restrictions-group-change', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_post_restrictions_group_change', $assoc_args );
});


//domain change
WP_CLI::add_command( 'waas1-tenant pre-domain-change', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_pre_domain_change', $assoc_args );
});
WP_CLI::add_command( 'waas1-tenant post-domain-change', function( $args, $assoc_args ){
	do_action( 'waas1_tenant_lifecycle_post_domain_change', $assoc_args );
});



?>