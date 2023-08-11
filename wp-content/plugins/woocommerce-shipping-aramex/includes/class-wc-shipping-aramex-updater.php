<?php
/**
 * Updater class for Shipping Aramex.
 *
 * @package WC_Shipping_Aramex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Aramex update routine performer from version-to-version.
 *
 * This class performs the update of a given version. If a particular version
 * needs update routine (e.g. DB migration) then the updater should be defined
 * in `self::get_updaters()`.
 *
 * @since 1.0.2
 * @version 1.0.2
 */
class WC_Shipping_Aramex_Update {
	/**
	 * List of updaters from version-to-version.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @var array
	 */
	protected static $updaters;

	/**
	 * Get list of version updaters.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @return array Array of updaters where key is the version and value is
	 *               updater array.
	 */
	protected static function get_updaters() {
		if ( ! empty( self::$updaters ) ) {
			return self::$updaters;
		}

		$basedir = WC_Aramex()->plugin_dir;

		$shipping_zones_admin_url = add_query_arg(
			array(
				'page' => 'wc-settings',
				'tab' => 'shipping',
			),
			admin_url( 'admin.php' )
		);

		self::$updaters = array(
			'1.0.2' => array(
				'path'   => $basedir . '/includes/updates/class-wc-shipping-aramex-updater-1.0.2.php',
				'class'  => 'WC_Shipping_Aramex_Updater_1_0_2',
				/* translators: placeholders are opening and closing anchor tag that link to admin shipping setting. */
				'notice' => sprintf( __( 'Aramex now supports shipping zones. The zone settings were added to a new Aramex method on the "Rest of the World" Zone. See the zones %1$shere%2$s ', 'woocommerce-shipping-aramex' ), '<a href="' . esc_url( $shipping_zones_admin_url ) . '">', '</a>' ),
			),
		);

		return self::$updaters;
	}

	/**
	 * Check for update based on current plugin's version versus installed
	 * version. Perform update routine if version mismatches.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 */
	public static function check_update() {
		$installed_version = get_option( 'woocommerce_shipping_aramex_version' );
		if ( WC_SHIPPING_ARAMEX_VERSION !== $installed_version ) {
			self::maybe_perform_update( $installed_version );
			self::update_version();
		}
	}

	/**
	 * Hooked into `admin_notices` by plugin's main class.
	 *
	 * This check for any update notices that should be displayed in admin
	 * pages.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 */
	public static function check_update_notices() {
		if ( 'yes' !== get_option( 'woocommerce_wc_shipping_aramex_show_update_notice' ) ) {
			return;
		}

		// If no notice for this version, don't display the notice.
		$notice = self::get_update_notice();
		if ( ! $notice ) {
			self::dismiss_update_notice();
			return;
		}

		?>
		<div class="notice notice-success is-dismissible wc-shipping-aramex-notice">
			<p><?php echo $notice; // xss ok. ?></p>
		</div>

		<script type="application/javascript">
		jQuery( '.notice.wc-shipping-aramex-notice'  ).on( 'click', '.notice-dismiss', function () {
			wp.ajax.post( 'wc_shipping_aramex_dismiss_upgrade_notice' );
		} );
		</script>
		<?php
	}

	/**
	 * Show update notice.
	 *
	 * Flag, via option, if notice needs to be displayed after updater runs.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 */
	public static function show_update_notice() {
		update_option( 'woocommerce_wc_shipping_aramex_show_update_notice', 'yes' );
	}

	/**
	 * Dismiss update notice.
	 *
	 * Remove the flag, in option, that's used to display notice. This is also
	 * called, via AJAX, when dismiss button is clicked.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 */
	public static function dismiss_update_notice() {
		update_option( 'woocommerce_wc_shipping_aramex_show_update_notice', 'no' );
	}

	/**
	 * Get update notice message for latest version.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @return string Update notice.
	 */
	protected static function get_update_notice() {
		$updaters = self::get_updaters();

		return ! empty( $updaters[ WC_SHIPPING_ARAMEX_VERSION ]['notice'] )
			?  $updaters[ WC_SHIPPING_ARAMEX_VERSION ]['notice']
			: '';
	}

	/**
	 * Maybe perform update if there's an update routine for a given version.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param string $installed_version Installed version found in DB.
	 */
	protected static function maybe_perform_update( $installed_version ) {
		require_once( WC_Aramex()->plugin_dir . '/includes/updates/interface-wc-shipping-aramex-updater.php' );
		foreach ( self::get_updaters() as $version => $updater ) {
			if ( version_compare( $installed_version, $version, '>=' ) ) {
				continue;
			}

			self::maybe_updater_runs_update( $updater );
		}
	}

	/**
	 * Maybe the updater will runs `update` routine.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param array $updater Updater array.
	 */
	protected static function maybe_updater_runs_update( array $updater ) {
		require_once( $updater['path'] );

		$updater_instance = new $updater['class']();
		if ( ! is_a( $updater_instance, 'WC_Shipping_Aramex_Updater' ) ) {
			return;
		}

		$updated = $updater_instance->update();

		if ( $updated && ! empty( $updater['notice'] ) ) {
			self::show_update_notice();
		}
	}

	/**
	 * Update version that's stored in DB to the latest version.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 */
	protected static function update_version() {
		delete_option( 'woocommerce_shipping_aramex_version' );
		add_option( 'woocommerce_shipping_aramex_version', WC_SHIPPING_ARAMEX_VERSION );
	}
}
