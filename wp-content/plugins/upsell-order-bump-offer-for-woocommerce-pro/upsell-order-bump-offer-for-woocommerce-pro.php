<?php
/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpswings.com/?utm_source=wpswings-official&utm_medium=order-bump-pro-backend&utm_campaign=official
 * @since             1.0.0
 * @package           upsell-order-bump-offer-for-woocommerce-pro
 *
 * @wordpress-plugin
 * Plugin Name:       Upsell Order Bump Offer for WooCommerce Pro
 * Plugin URI:        https://wpswings.com/product/upsell-order-bump-offer-for-woocommerce-pro/?utm_source=wpswings-orderbump-pro&utm_medium=orderbump-pro-backend&utm_campaign=pro-plugin
 * Description:       <code><strong>Upsell Order Bump Offer for WooCommerce Pro</strong></code> shows exclusive bump offers on checkout page that grows your sales & increases average order value. <a target="_blank" href="https://wpswings.com/woocommerce-plugins/?utm_source=wpswings-orderbump-shop&utm_medium=orderbump-pro-backend&utm_campaign=shop-page" >Elevate your e-commerce store by exploring more on <strong>WP Swings</strong></a>.
 *
 * Requires at least:       4.4
 * Tested up to:            5.9.3
 * WC requires at least:    3.0.0
 * WC tested up to:         6.4.1
 *
 * Version:           2.1.1
 * Author:            WP Swings
 * Author URI:        https://wpswings.com/?utm_source=wpswings-official&utm_medium=order-bump-pro-backend&utm_campaign=official
 * License:           WP Swings License
 * License URI:       https://wpswings.com/license-agreement.txt
 * Text Domain:       upsell-order-bump-offer-for-woocommerce-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Plugin Active Detection.
 *
 * @since    1.0.0
 * @param    string $plugin_slug index file of plugin.
 */
function wps_ubo_is_plugin_active( $plugin_slug = '' ) {

	if ( empty( $plugin_slug ) ) {

		return false;
	}

	$active_plugins = (array) get_option( 'active_plugins', array() );

	if ( is_multisite() ) {

		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );

	}

	return in_array( $plugin_slug, $active_plugins, true ) || array_key_exists( $plugin_slug, $active_plugins );

}

$old_org_present   = false;
$installed_plugins = get_plugins();

if ( array_key_exists( 'upsell-order-bump-offer-for-woocommerce/upsell-order-bump-offer-for-woocommerce.php', $installed_plugins ) ) {
	$base_plugin = $installed_plugins['upsell-order-bump-offer-for-woocommerce/upsell-order-bump-offer-for-woocommerce.php'];
	if ( version_compare( $base_plugin['Version'], '2.1.0', '<' ) ) {
		$old_org_present = true;
	}
}

if ( true === $old_org_present ) {

	// Try org update to minimum.
	add_action( 'admin_notices', 'wps_ubo_upgrade_old_plugin' );
	/**
	 * Try org update to minimum.
	 */
	function wps_ubo_upgrade_old_plugin() {
		require_once 'upsell-order-bump-offer-install-free.php';
		wps_ubo_org_replace_plugin();
	}

	/**
	 * Migration to new domain notice.
	 *
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array  $plugin_data An array of plugin data.
	 * @param string $status Status filter currently applied to the plugin list.
	 */
	function wps_ubo_upgrade_notice( $plugin_file, $plugin_data, $status ) {

		?>
	<tr class="plugin-update-tr active notice-warning notice-alt">
		<td colspan="4" class="plugin-update colspanchange">
			<div class="notice notice-error inline update-message notice-alt">
				<p class='wps-notice-title wps-notice-section'>
					<?php esc_html_e( 'Heads up, We highly recommend Also Update Latest Org Plugin. The latest update includes some substantial changes across different areas of the plugin.', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>
				</p>
			</div>
		</td>
	</tr>
	<style>
		.wps-notice-section > p:before {
			content: none;
		}
	</style>
		<?php
	}
	add_action( 'after_plugin_row_upsell-order-bump-offer-for-woocommerce/upsell-order-bump-offer-for-woocommerce.php', 'wps_ubo_upgrade_notice', 0, 3 );
	add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), 'wps_ubo_upgrade_notice', 0, 3 );

	add_action( 'toplevel_page_upsell-order-bump-offer-for-woocommerce-setting', 'block_page_screen' );
	add_action( 'order-bump_page_upsell-order-bump-offer-for-woocommerce-reporting', 'block_page_screen' );

	/**
	 * Currently plugin version.
	 */
	function block_page_screen() {
		require 'upsell-order-bump-offer-for-woocommerce-pro-incompatible.php';
		die;
	}
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_VERSION', '2.1.1' );

/**
 * The code that runs during plugin activation.
 */
function activate_upsell_order_bump_offer_for_woocommerce_pro() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-upsell-order-bump-offer-for-woocommerce-pro-activator.php';
	Upsell_Order_Bump_Offer_For_Woocommerce_Pro_Activator::activate();

	if ( ! wp_next_scheduled( 'wps_upsell_bump_check_license_hook' ) ) {

		wp_schedule_event( time(), 'daily', 'wps_upsell_bump_check_license_hook' );
	}
}

/**
 * Conditional dependency for plugin activation.
 */
function wps_bump_pro_plugin_activation() {

	$activation['status']  = true;
	$activation['message'] = '';

	// If org plugin is inactive, load nothing.
	if ( ! wps_ubo_is_plugin_active( 'upsell-order-bump-offer-for-woocommerce/upsell-order-bump-offer-for-woocommerce.php' ) ) {

		$activation['status']  = false;
		$activation['message'] = 'org_inactive';
	}

	// Dependant plugin.
	if ( ! wps_ubo_is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

		$activation['status']  = false;
		$activation['message'] = 'woo_inactive';
	}

	return $activation;
}

// Check dependency during activation.
$wps_bump_plugin_activation = wps_bump_pro_plugin_activation();

if ( true === $wps_bump_plugin_activation['status'] ) {

	// Define all neccessary details.
	define( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_URL', plugin_dir_url( __FILE__ ) );

	define( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_DIRPATH', plugin_dir_path( __FILE__ ) );

	if ( ! defined( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_SPECIAL_SECRET_KEY' ) ) {

		define( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_SPECIAL_SECRET_KEY', '59f32ad2f20102.74284991' );
	}

	if ( ! defined( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_LICENSE_SERVER_URL' ) ) {

		define( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_LICENSE_SERVER_URL', 'https://wpswings.com' );
	}

	if ( ! defined( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_ITEM_REFERENCE' ) ) {

		define( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_ITEM_REFERENCE', 'Upsell Order Bump Offer For Woocommerce Pro' );
	}

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wps_ubo_plugin_action_links' );

	/**
	 * Add settings link in plugin list.
	 *
	 * @param    string $links       The settings page link.
	 */
	function wps_ubo_plugin_action_links( $links ) {

		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=upsell-order-bump-offer-for-woocommerce-setting' ) . '">' . __( 'Settings', 'upsell-order-bump-offer-for-woocommerce-pro' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	add_filter( 'plugin_row_meta', 'wps_ubo_add_important_links', 10, 2 );

	/**
	 * Add custom links for getting premium version.
	 *
	 * @param   string $links link to index file of plugin.
	 * @param   string $file index file of plugin.
	 *
	 * @since    1.3.0
	 */
	function wps_ubo_add_important_links( $links, $file ) {

		if ( strpos( $file, 'upsell-order-bump-offer-for-woocommerce-pro.php' ) !== false ) {

			$row_meta = array(
				'demo'    => '<a href="https://demo.wpswings.com/upsell-order-bump-offer-for-woocommerce-pro/?utm_source=wpswings-orderbump-demo&utm_medium=orderbump-pro-backend&utm_campaign=demo" target="_blank"><img src="' . esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL ) . 'admin/resources/icons/Demo.svg" class="wps-info-img" alt="Demo image">' . esc_html__( 'Demo', 'upsell-order-bump-offer-for-woocommerce-pro' ) . '</a>',
				'doc'     => '<a href="https://docs.wpswings.com/upsell-order-bump-offer-for-woocommerce-pro/?utm_source=wpswings-orderbump-doc&utm_medium=orderbump-pro-backend&utm_campaign=doc" target="_blank"><img src="' . esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL ) . 'admin/resources/icons/Documentation.svg" class="wps-info-img" alt="Documentation image">' . esc_html__( 'Documentation', 'upsell-order-bump-offer-for-woocommerce-pro' ) . '</a>',
				'support' => '<a href="https://wpswings.com/submit-query/?utm_source=wpswings-orderbump-support&utm_medium=orderbump-pro-backend&utm_campaign=support" target="_blank"><img src="' . esc_url( UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_URL ) . 'admin/resources/icons/Support.svg" class="wps-info-img" alt="DeSupportmo image">' . esc_html__( 'Support', 'upsell-order-bump-offer-for-woocommerce-pro' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	register_activation_hook( __FILE__, 'activate_upsell_order_bump_offer_for_woocommerce_pro' );

	/**
	 * Plugin Auto Update.
	 */
	function auto_update_wps_upsell_bump_offers_plugin() {

		$wps_upsell_bump_license_key = get_option( 'wps_upsell_bump_license_key', '' );

		define( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_LICENSE_KEY', $wps_upsell_bump_license_key );

		define( 'UPSELL_ORDER_BUMP_OFFER_FOR_WOOCOMMERCE_PRO_BASE_FILE', __FILE__ );

		$update_check_bump = 'https://wpswings.com/pluginupdates/upsell-order-bump-offer-for-woocommerce-pro/update.php';

		require_once 'class-wps-upsell-bump-update.php';
	}

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-upsell-order-bump-offer-for-woocommerce-pro.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_upsell_order_bump_offer_for_woocommerce_pro() {

		// Plugin auto update.
		auto_update_wps_upsell_bump_offers_plugin();
		$plugin = new Upsell_Order_Bump_Offer_For_Woocommerce_Pro();
		$plugin->run();

	}

	run_upsell_order_bump_offer_for_woocommerce_pro();

} else {

	add_action( 'admin_init', 'wps_bump_plugin_activation_failure' );

	/**
	 * Deactivate this plugin.
	 */
	function wps_bump_plugin_activation_failure() {

		global $wps_bump_plugin_activation;

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'upsell-order-bump-offer-for-woocommerce-pro' ) );
		}

		if ( 'woo_inactive' === $wps_bump_plugin_activation['message'] ) {

			// To hide Plugin activated notice.
			if ( ! empty( $_GET['activate'] ) ) {

				unset( $_GET['activate'] );
			}

			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	}

	// Add admin error notice.
	add_action( 'admin_notices', 'wps_bump_plugin_activation_admin_notice' );

	/**
	 * This function is used to display plugin activation error notice.
	 */
	function wps_bump_plugin_activation_admin_notice() {

		global $wps_bump_plugin_activation;

		?>

		<?php if ( 'woo_inactive' === $wps_bump_plugin_activation['message'] ) : ?>

			<div class="error">
				<p><strong><?php esc_html_e( 'WooCommerce', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></strong><?php esc_html_e( ' is not activated, Please activate WooCommerce first to activate ', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?><strong><?php esc_html_e( 'Upsell Order Bump Offer for WooCommerce Pro' ); ?></strong><?php esc_html_e( '.', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>
			</div>

		<?php endif; ?>

		<?php
		if ( 'org_inactive' === $wps_bump_plugin_activation['message'] ) :

			$screen = get_current_screen();
			?>

			<?php if ( 'plugins' === $screen->id ) : ?>

				<style type="text/css">
					.wps_ubo_buttons{
						padding-top: 0px !important;
					}
				</style>
				<div class="notice notice-info is-dismissible">
					<p><strong><?php esc_html_e( 'Upsell Order Bump Offer for WooCommerce Pro', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></strong><?php esc_html_e( ' Plugin requires the', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?> <strong><?php esc_html_e( 'Order Bump Free Org', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></strong><?php esc_html_e( ' plugin to work.', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></p>
					<p class="submit wps_ubo_buttons"><a target="_blank" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=Upsell+Order+Bump+Offer+for+WooCommerce+upselling+plugin&tab=search&type=term' ) ); ?>" class="button-primary"><?php esc_html_e( 'Install & Activate →', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></a>
						<a href="https://wordpress.org/plugins/upsell-order-bump-offer-for-woocommerce/" target="_blank" class="button"><?php esc_html_e( 'View on ORG page →', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></a></p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?></span></button>
				</div>
			<?php endif; ?>
			<?php
		endif;
	}
}

add_action( 'admin_init', 'upsell_orderbump_parent_plugin' );

/**
 * Function to make org and pro dependent.
 *
 * @return void
 */
function upsell_orderbump_parent_plugin() {
	if ( is_admin() && current_user_can( 'activate_plugins' ) && ! is_plugin_active( 'upsell-order-bump-offer-for-woocommerce/upsell-order-bump-offer-for-woocommerce.php' ) ) {
		add_action( 'admin_notices', 'upsell_orderbump_org_plugin_notice' );

		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

/**
 * Function for plugin notice.
 *
 * @return void
 */
function upsell_orderbump_org_plugin_notice() {
	?>
	<div class="error">
		<p>
		<?php esc_html_e( 'Sorry, but Upsell order bump for woocommerce pro Plugin requires the Upsell order bump for woocommerce plugin to be installed and active', 'upsell-order-bump-offer-for-woocommerce-pro' ); ?>.
		</p>
	</div>
	<?php
}

?>
