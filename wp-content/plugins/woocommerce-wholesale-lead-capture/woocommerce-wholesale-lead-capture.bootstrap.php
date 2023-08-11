<?php
/*
Plugin Name:    WooCommerce Wholesale Lead Capture
Plugin URI:     https://wholesalesuiteplugin.com/
Description:    WooCommerce extension to provide functionality of capturing wholesale leads.
Author:         Rymera Web Co
Version:        1.14.3
Author URI:     https://rymera.com.au/
Text Domain:    woocommerce-wholesale-lead-capture
WC requires at least: 3.3.0
WC tested up to: 4.0.1
*/

require_once ( 'woocommerce-wholesale-lead-capture.functions.php' );

// Delete code activation flag on plugin deactivate.
register_deactivation_hook( __FILE__ , 'wwlc_global_plugin_deactivate' );

$missing_required_plugins = wwlc_check_plugin_dependencies();

// Check if WooCommerce is active
if ( count( $missing_required_plugins ) <= 0 ) {

	// Include Necessary Files
	require_once ( 'woocommerce-wholesale-lead-capture.options.php' );
    require_once ( 'woocommerce-wholesale-lead-capture.plugin.php' );

	// Get Instance of Main Plugin Class
    $wc_wholesale_lead_capture              = WooCommerce_Wholesale_Lead_Capture::instance();
	$GLOBALS[ 'wc_wholesale_lead_capture' ] = $wc_wholesale_lead_capture;

    $wc_wholesale_lead_capture->run();

} else {

    /**
     * Provide admin notice to users that a required plugin dependency of WooCommerce Wholesale Lead Capture plugin is missing.
     *
     * @since 1.6.2
     */
    function wwlcAdminNotices () {

        global $missing_required_plugins;

        $adminNoticeMsg = '';

        if ( ! $missing_required_plugins )
            $missing_required_plugins = wwlc_check_plugin_dependencies();

        foreach ( $missing_required_plugins as $plugin ) {

            $pluginFile     = $plugin[ 'plugin-base' ];
            $sptFile        = trailingslashit( WP_PLUGIN_DIR ) . plugin_basename( $pluginFile );

            $sptInstallText = '<a href="' . wp_nonce_url( 'update.php?action=install-plugin&plugin=' . $plugin[ 'plugin-key' ], 'install-plugin_' . $plugin[ 'plugin-key' ] ) . '">' . __( 'Click here to install from WordPress.org repo &rarr;', 'woocommerce-wholesale-lead-capture' ) . '</a>';
            if ( file_exists( $sptFile ) )
                $sptInstallText = '<a href="' . wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $pluginFile . '&amp;plugin_status=all&amp;s', 'activate-plugin_' . $pluginFile ) . '" title="' . __( 'Activate this plugin', 'woocommerce-wholesale-lead-capture' ) . '" class="edit">' . __( 'Click here to activate &rarr;', 'woocommerce-wholesale-lead-capture' ) . '</a>';

            $adminNoticeMsg .= sprintf( __( '<br/>Please ensure you have the <a href="%1$s" target="_blank">%2$s</a> plugin installed and activated.<br/>', 'woocommerce-wholesale-lead-capture' ), 'http://wordpress.org/plugins/' . $plugin[ 'plugin-key' ] . '/', $plugin[ 'plugin-name' ] );
            $adminNoticeMsg .= $sptInstallText . '<br/>';

        } ?>

        <div class="error">
            <p>
                <?php _e( '<b>WooCommerce Wholesale Lead Capture</b> plugin missing dependency.<br/>', 'woocommerce-wholesale-lead-capture' ); ?>
                <?php echo $adminNoticeMsg; ?>
            </p>
        </div><?php

    }

    add_action( 'admin_notices', 'wwlcAdminNotices' );

}
