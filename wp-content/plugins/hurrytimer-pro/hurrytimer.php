<?php
// removeIf(!pro)
/**
 * The plugin bootstrap file
 *
 * @link              http://hurrytimer.com
 * @since             1.0.0
 * @package           Hurrytimer
 *
 * @wordpress-plugin
 * Plugin Name:       HurryTimer PRO
 * Plugin URI:        https://hurrytimer.com
 * Description:       A Scarcity and Urgency Countdown Timer for WordPress & WooCommerce with recurring and evergreen mode.
 * Version:           2.7.3
 * Author:            Nabil Lemsieh
 * Author URI:        https://hurrytimer.com
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl.html
 * Text Domain:       hurrytimer
 * Domain Path:       /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 6.3
 */
// endRemoveIf(!pro)


// If this file is called directly, abort.

if (!defined('WPINC')) {
    die;
}

if(!(defined( 'WP_CLI' ) && WP_CLI) && function_exists('\is_plugin_active') && function_exists('\deactivate_plugins')):

// removeif(!pro)
if ( defined( 'HURRYT_VERSION' ) && ! defined( 'HURRYT_IS_PRO' ) ) {
	if ( is_plugin_active( 'hurrytimer/hurrytimer.php' ) ) {
		deactivate_plugins( 'hurrytimer/hurrytimer.php' );
	}

	wp_redirect( $_SERVER['REQUEST_URI'] );
	exit;
}
// endRemoveIf(!pro)

endif;

define('HURRYT_VERSION', '2.7.3');

// removeIf(!pro)
define('HURRYT_IS_PRO', '1');
// endRemoveIf(!pro)

define('HURRYT_DIR', plugin_dir_path(__FILE__));
define('HURRYT_URL', plugin_dir_url(__FILE__));
define('HURRYT_BASENAME', plugin_basename(__FILE__));
define('HURRYT_POST_TYPE', 'hurrytimer_countdown');

require_once __DIR__ . '/vendor/autoload.php';

//removeIf(!pro)
try {
    require_once __DIR__ . '/lib/wp-package-updater/class-wp-package-updater.php';
    $__u = new \WP_Package_Updater_HURRYT(
        'https://hurrytimer.com',
        wp_normalize_path(__FILE__),
        wp_normalize_path(HURRYT_DIR),
        'hurrytimer-pro',
        'https://hurrytimer.com/contact/'
    );

    if ($__u->__pb()) {
        return;
    }

    add_action('hurryt_manage_license', [$__u, 'show_license_form']);
} catch (\Exception $e) {
}

//endRemoveIf(!pro)

register_activation_hook(__FILE__, [Hurrytimer\Installer::get_instance(), 'activate']);

add_action('plugins_loaded', function () {
    (new \Hurrytimer\Bootstrap())->run();
    do_action('hurrytimer_init');
});
