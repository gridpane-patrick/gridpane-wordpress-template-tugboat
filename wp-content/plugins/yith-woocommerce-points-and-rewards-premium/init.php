<?php
/**
 * Plugin Name: YITH WooCommerce Points and Rewards Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-points-and-rewards/
 * Description: With <code><strong>YITH WooCommerce Points and Rewards</strong></code> you can start a rewards program with points to gain your customers' loyalty. Your customers will be able to use their points to get discounts making it a perfect marketing strategy for your store. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce shop on <strong>YITH</strong></a>.
 * Version: 3.7.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-points-and-rewards
 * Domain Path: /languages/
 * WC requires at least: 6.1
 * WC tested up to: 6.3
 **/

/*
 * @package YITH WooCommerce Points and Rewards
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

! defined( 'YITH_YWPAR_DIR' ) && define( 'YITH_YWPAR_DIR', plugin_dir_path( __FILE__ ) );

if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_YWPAR_FREE_INIT', plugin_basename( __FILE__ ) );

/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_YWPAR_DIR . 'plugin-fw/init.php' ) ) {
	require_once YITH_YWPAR_DIR . 'plugin-fw/init.php';
}
yit_maybe_plugin_fw_loader( YITH_YWPAR_DIR );

// Define constants ________________________________________.
! defined( 'YITH_YWPAR_VERSION' ) && define( 'YITH_YWPAR_VERSION', '3.7.0' );
! defined( 'YITH_YWPAR_PREMIUM' ) && define( 'YITH_YWPAR_PREMIUM', plugin_basename( __FILE__ ) );
! defined( 'YITH_YWPAR_INIT' ) && define( 'YITH_YWPAR_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_YWPAR_FILE' ) && define( 'YITH_YWPAR_FILE', __FILE__ );
! defined( 'YITH_YWPAR_URL' ) && define( 'YITH_YWPAR_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_YWPAR_ASSETS_URL' ) && define( 'YITH_YWPAR_ASSETS_URL', YITH_YWPAR_URL . 'assets' );
! defined( 'YITH_YWPAR_TEMPLATE_PATH' ) && define( 'YITH_YWPAR_TEMPLATE_PATH', YITH_YWPAR_DIR . 'templates' );
! defined( 'YITH_YWPAR_VIEWS_PATH' ) && define( 'YITH_YWPAR_VIEWS_PATH', YITH_YWPAR_DIR . 'views' );
! defined( 'YITH_YWPAR_INC' ) && define( 'YITH_YWPAR_INC', YITH_YWPAR_DIR . 'includes/' );
! defined( 'YITH_YWPAR_SLUG' ) && define( 'YITH_YWPAR_SLUG', 'yith-woocommerce-points-and-rewards' );
! defined( 'YITH_YWPAR_SECRET_KEY' ) && define( 'YITH_YWPAR_SECRET_KEY', '12345' );

if ( ! defined( 'YITH_YWPAR_SUFFIX' ) ) {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	define( 'YITH_YWPAR_SUFFIX', $suffix );
}

if ( ! function_exists( 'yith_ywpar_install_woocommerce_admin_notice' ) ) {
	/**
	 * Admin notice when WooCommerce isn't installed.
	 */
	function yith_ywpar_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'YITH WooCommerce Points and Rewards is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-points-and-rewards' ); ?></p>
		</div>
		<?php
	}
}



if ( ! function_exists( 'yith_ywpar_premium_install' ) ) {
	/**
	 * Check if create the points db table.
	 */
	function yith_ywpar_premium_install() {
		// Woocommerce installation check _________________________.
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_ywpar_install_woocommerce_admin_notice' );
			return;
		}

		// DO_ACTION : yith_ywpar_init : action triggered before install the plugin.
		do_action( 'yith_ywpar_init' );

		// check for update table.
		require_once YITH_YWPAR_INC . 'functions-yith-wc-points-rewards-updates.php';
		yith_ywpar_update_db_check();

	}
}

add_action( 'plugins_loaded', 'yith_ywpar_premium_install', 11 );

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );


if ( ! function_exists( 'yith_ywpar_premium_constructor' ) ) {
	/**
	 * Start the game
	 */
	function yith_ywpar_premium_constructor() {
		// Load ywpar text domain ___________________________________.
		load_plugin_textdomain( 'yith-woocommerce-points-and-rewards', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		require_once YITH_YWPAR_INC . 'class-yith-wc-points-rewards.php';

		yith_points();
	}
}
add_action( 'yith_ywpar_init', 'yith_ywpar_premium_constructor' );


if ( ! function_exists( 'ywpar_remove_cron_scheduled' ) ) {
	/**
	 * Remove CRON schedule when the plugin will be deactivated.
	 */
	function ywpar_remove_cron_scheduled() {
		wp_clear_scheduled_hook( 'ywpar_cron' );
		wp_clear_scheduled_hook( 'ywpar_cron_birthday' );
	}
}

/**
 * Remove Cron Scheduled Events
 */
register_deactivation_hook( __FILE__, 'ywpar_remove_cron_scheduled' );

