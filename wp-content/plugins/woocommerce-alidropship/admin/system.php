<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_System
 */
class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_System {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu_page' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function admin_enqueue_scripts() {
		$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		global $pagenow;
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-alidropship-status' ) {
			VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::enqueue_3rd_library( array( 'button', 'icon' ) );
		}
	}

	public function page_callback() {
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'System Status', 'woocommerce-alidropship' ) ?></h2>
            <table class="widefat">
                <tbody>
                <tr>
                    <td><a target="_blank"
                           href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ) ?>"><?php esc_html_e( 'Permalink structure' ) ?></a>
                    </td>
                    <td>
						<?php
						if ( get_option( 'permalink_structure' ) ) {
							?><i class="check green icon"></i><?php
						} else {
							?><i class="cancel red icon"></i><?php
						}
						?>
                    </td>
                    <td><?php esc_html_e( 'Should be "Post name" and must not be "Plain"', 'woocommerce-alidropship' ) ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'SSL', 'woocommerce-alidropship' ) ?></td>
                    <td>
						<?php
						if ( is_ssl() ) {
							?><i class="check green icon"></i><?php
						} else {
							?><i class="cancel red icon"></i><?php
						}
						?>
                    </td>
                    <td><?php esc_html_e( '*Required', 'woocommerce-alidropship' ) ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'PHP Max Execution Time', 'woocommerce-alidropship' ) ?></td>
                    <td><?php echo ini_get( 'max_execution_time' ); ?></td>
                    <td><?php esc_html_e( 'Should be greater than 100', 'woocommerce-alidropship' ) ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'PHP Max Input Vars', 'woocommerce-alidropship' ) ?></td>
                    <td><?php echo ini_get( 'max_input_vars' ); ?></td>
                    <td><?php esc_html_e( 'Should be greater than 10000', 'woocommerce-alidropship' ) ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Memory Limit', 'woocommerce-alidropship' ) ?></td>
                    <td><?php echo ini_get( 'memory_limit' ); ?></td>
                    <td><?php esc_html_e( 'Should be greater than 128MB', 'woocommerce-alidropship' ) ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'WooCommerce AliExpress Dropshipping Extension installed and active', 'woocommerce-alidropship' ) ?></td>
                    <td>
                        <i class="red cancel icon <?php echo esc_attr( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::set( 'chrome-extension-active' ) ) ?>"></i>
                        <a target="_blank" href="https://downloads.villatheme.com/?download=alidropship-extension"
                           title="<?php esc_attr_e( 'You have to install the chrome extension to import products from AliExpress', 'woocommerce-alidropship' ) ?>"
                           class="vi-ui positive button labeled icon mini <?php echo esc_attr( VI_WOOCOMMERCE_ALIDROPSHIP_DATA::set( 'download-chrome-extension' ) ) ?>"><i
                                    class="external icon"></i><?php esc_html_e( 'Install Extension', 'woocommerce-alidropship' ) ?>
                        </a>
                    </td>
                    <td><?php esc_html_e( '*Required to be able to import AliExpress products', 'woocommerce-alidropship' ) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
		<?php
	}

	/**
	 * Register a custom menu page.
	 */
	public function menu_page() {
		$menu_slug = 'woocommerce-alidropship-status';
		add_submenu_page(
			'woocommerce-alidropship',
			esc_html__( 'System Status', 'woocommerce-alidropship' ),
			esc_html__( 'System Status', 'woocommerce-alidropship' ),
			apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', $menu_slug ),
			$menu_slug,
			array( $this, 'page_callback' )
		);
	}
}
