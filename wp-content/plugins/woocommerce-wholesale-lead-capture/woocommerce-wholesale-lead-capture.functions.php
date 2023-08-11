<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 |--------------------------------------------------------------------------------------------------------------
 | MISC Functions
 |--------------------------------------------------------------------------------------------------------------
 */

/**
 * Check for plugin dependencies of WooCommerce Wholesale Lead Capture plugin.
 *
 * @since 1.6.2
 * @return array Array of required plugins that are not present
 */
if( ! function_exists( 'wwlc_check_plugin_dependencies' ) ) {

    function wwlc_check_plugin_dependencies() {

        // Makes sure the plugin is defined before trying to use it
        if ( ! function_exists( 'is_plugin_active' ) )
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $i = 0;
        $plugins = array();
        $requiredPlugins = apply_filters( 'wwlc_required_plugins', array( 'woocommerce/woocommerce.php' ) );

        foreach ( $requiredPlugins as $plugin ) {

            if ( ! is_plugin_active( $plugin ) ) {

                $pluginName = explode( '/', $plugin );

                $plugins[ $i ][ 'plugin-key' ]  = $pluginName[ 0 ];
                $plugins[ $i ][ 'plugin-base' ] = $plugin;
                $plugins[ $i ][ 'plugin-name' ] = ucwords( str_replace( '-', ' ', $pluginName[ 0 ] ) );

            }

            $i++;

        }

        return $plugins;

    }

}

/**
 * Delete code activation flag on plugin deactivate.
 *
 * @param bool $network_wide
 *
 * @since 1.3.0
 * @since 1.11 Includes removal of license related options
 */
if( ! function_exists( 'wwlc_global_plugin_deactivate' ) ) {

    function wwlc_global_plugin_deactivate( $network_wide ) {

        global $wpdb;

        // check if it is a multisite network
        if ( is_multisite() ) {

            // check if the plugin has been deactivated on the network or on a single site
            if ( $network_wide ) {

                // get ids of all sites
                $blogIDs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                foreach ( $blogIDs as $blogID ) {

                    switch_to_blog( $blogID );
                    delete_option( 'wwlc_activation_code_triggered' );
                    delete_site_option( 'wwlc_option_installed_version' );
                    delete_site_option( 'wwlc_update_data' );
                    delete_site_option( 'wwlc_license_expired' );

                }

                restore_current_blog();

            } else {

                // deactivated on a single site, in a multi-site
                delete_option( 'wwlc_activation_code_triggered' );
                delete_site_option( 'wwlc_option_installed_version' );
                delete_site_option( 'wwlc_update_data' );
                delete_site_option( 'wwlc_license_expired' );

            }

        } else {

            // deactivated on a single site
            delete_option( 'wwlc_activation_code_triggered' );
            delete_option( 'wwlc_option_installed_version' );
            delete_option( 'wwlc_update_data' );
            delete_option( 'wwlc_license_expired' );

        }

    }
    
}

/**
 * Log deprecated function error to the php_error.log file and display on screen when not on AJAX.
 *
 * @since 1.7.0
 * @access public
 *
 * @param array  $trace       debug_backtrace() output
 * @param string $function    Name of depecrated function.
 * @param string $version     Version when the function is set as depecrated since.
 * @param string $replacement Name of function to be replaced.
 */
function wwlc_deprecated_function( $trace , $function , $version , $replacement = null ) {

	$caller = array_shift( $trace );

	$log_string  = "The <em>{$function}</em> function is deprecated since version <em>{$version}</em>.";
	$log_string .= $replacement ? " Replace with <em>{$replacement}</em>." : '';
	$log_string .= ' Trace: <strong>' . $caller[ 'file' ] . '</strong> on line <strong>' . $caller[ 'line' ] . '</strong>';

	error_log( strip_tags( $log_string ) );

	if ( ! is_ajax() && WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true ) )
		echo $log_string;
}

/**
 * Get the page url. We need to return only the page URL.
 *
 * @param string    $page_option     Contains option name
 *
 * @access public
 * @since 1.8.0
 * @return string
 */
function wwlc_get_url_of_page_option( $page_option ) {

    $page_option = get_option( $page_option );

    if( $page_option ) {

        $page_id = intval( $page_option );

        if( $page_id )
            return trim( get_permalink( $page_id ) );

        return trim( $page_option );

    }

    return '';

}

/**
 * Get the user role.
 *
 * @param int    $user_id     User ID
 *
 * @since 1.8.0
 * @return string
 */
function wwlc_get_user_role( $user_id ) {
    
    global $wp_roles;

    $custom_role        = get_user_meta( $user_id , 'wwlc_custom_set_role' , true );
    $wwlc_new_lead_role = get_option( 'wwlc_general_new_lead_role' );
            
    if( $custom_role )
        return $wp_roles->roles[ $custom_role ][ 'name' ]; // Custom Role set via shortcode role="your_role"
    else
        return $wp_roles->roles[ $wwlc_new_lead_role ][ 'name' ]; // Get via wwlc 'New Lead Role' option

}

/**
 * Get current url.
 *
 * @since 1.8.0
 * @return string
 */
function wwlc_get_current_url() {
    
    return ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

}

/**
 * Check if WWP or both WWP and WWPP are active.
 *
 * @since 1.8.0
 * @return bool
 */
function wwlc_is_wwp_and_wwpp_active( $check_if_wpp_is_active = false ) {

    if( $check_if_wpp_is_active )
        return is_plugin_active( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' ) && is_plugin_active( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php' );
    else
        return is_plugin_active( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' );

}

/**
 * Strip custom field slashes before adding and updating.
 *
 * @param array    $custom_field     Custom Fields Array
 * 
 * @since 1.12.0
 * @return bool
 */
function wwlc_strip_slashes( $custom_field = array() ) {

    // Strip extra slashes
    if( $custom_field ) {
        foreach( $custom_field as $key => $value ) {
            switch( $key ) {
                case 'field_name':
                case 'field_placeholder':
                case 'default_value':
                    $custom_field[ $key ] = stripslashes( $value );
                    break;
                case 'options':
                    if( !empty( $value ) ) {
                        foreach( $value as $index => $option_val ) {
                            $custom_field[ $key ][ $index ][ 'value' ] = stripslashes( $option_val[ 'value' ] );
                            $custom_field[ $key ][ $index ][ 'text' ] = stripslashes( $option_val[ 'text'] );
                        }
                    }
                    break;
            }
        }
    }

    return $custom_field;
    
}

/**
 * Convert special chars to html entities.
 *
 * @param array    $custom_field     Custom Fields Array
 * 
 * @since 1.12.0
 * @return bool
 */
function wwlc_htmlspecialchars( $custom_field = array() ) {

    // Strip extra slashes
    if( isset( $custom_field[ 'field_type' ] ) && in_array( $custom_field[ 'field_type' ] , array( 'radio' , 'select' , 'checkbox' ) ) ) {
        foreach( $custom_field as $key => $value ) {
            switch( $key ) {
                case 'options':
                    if( !empty( $value ) ) {
                        foreach( $value as $index => $option_val ) {
                            $custom_field[ $key ][ $index ][ 'value' ] = htmlspecialchars( $option_val[ 'value' ] );
                            $custom_field[ $key ][ $index ][ 'text' ] = htmlspecialchars( $option_val[ 'text'] );
                        }
                    }
                    break;
            }
        }
    }

    return $custom_field;
    
} 