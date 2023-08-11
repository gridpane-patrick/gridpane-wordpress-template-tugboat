<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Transfer_Settings {
	protected static $settings;
	protected $error;

	public function __construct() {
		self::$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
	}

	private static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_ALIDROPSHIP_DATA::set( $name, $set_name );
	}

	public function admin_init() {
		global $wooaliexpressdropship_settings;
		if ( isset( $_POST['vi_wad_import_settings'] ) && isset( $_POST['_wooaliexpressdropship_nonce'] ) && wp_verify_nonce( $_POST['_wooaliexpressdropship_nonce'], 'wooaliexpressdropship_save_settings' ) ) {
			$args = vi_wad_json_decode( base64_decode( sanitize_text_field( $_POST['vi_wad_transfer_settings>'] ) ) );
			if ( is_array( $args ) && count( $args ) ) {
				$this->error = false;
				/*Do not migrate auto update key and access token as they are unable to use in other sites*/
				unset( $args['key'] );
				unset( $args['access_tokens'] );
				unset( $args['access_token'] );
				$args = array_merge( self::$settings->get_params(), $args );
				update_option( 'wooaliexpressdropship_params', $args );
				$wooaliexpressdropship_settings = $args;
				self::$settings                 = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance( true );
			} else {
				$this->error = true;
			}
		}
	}

	public function admin_menu() {
		$menu_slug = 'woocommerce-alidropship-transfer-settings';
		add_submenu_page( 'woocommerce-alidropship', esc_html__( 'Transfer Settings', 'woocommerce-alidropship' ), esc_html__( 'Transfer Settings', 'woocommerce-alidropship' ), apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', $menu_slug ), $menu_slug, array(
			$this,
			'page_callback'
		) );
	}

	public function page_callback() {
		?>
        <div class="wrap">
            <h2><?php esc_attr_e( 'Export/Import Settings', 'woocommerce-alidropship' ) ?></h2>
            <div class="vi-ui segment">
				<?php
				if ( $this->error === true ) {
					?>
                    <div class="vi-ui negative message">
                        <div class="header">
							<?php esc_html_e( 'Invalid input' ); ?>
                        </div>
                    </div>
					<?php
				} elseif ( $this->error === false ) {
					?>
                    <div class="vi-ui positive message">
                        <div class="header">
							<?php esc_html_e( 'Import settings successfully' ); ?>
                        </div>
                    </div>
					<?php
				}
				?>
                <form class="vi-ui form" method="post">
					<?php
					wp_nonce_field( 'wooaliexpressdropship_save_settings', '_wooaliexpressdropship_nonce' )
					?>
                    <h4><?php esc_html_e( 'Your current settings:', 'woocommerce-alidropship' ) ?></h4>
                    <textarea style="width: 100%;min-height: 200px; "
                              name="<?php esc_attr_e( self::set( 'transfer-settings', true ) ) ?>>"><?php esc_html_e( trim( base64_encode( json_encode( self::$settings->get_params() ) ) ) ); ?></textarea>
                    <p>
                        <input type="submit" class="vi-ui primary button"
                               name="<?php esc_attr_e( self::set( 'import-settings', true ) ) ?>"
                               value="<?php esc_attr_e( 'Import Settings', 'woocommerce-alidropship' ) ?>">
                    </p>
                </form>
                <div class="vi-ui positive message">
                    <div>
						<?php esc_html_e( 'To move your settings from site A to site B, please copy this field from site A and paste it to the same field on site B then click Import Settings.' ); ?>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	public function enqueue_semantic() {
		/*Stylesheet*/
		wp_enqueue_style( 'vi-woocommerce-alidropship-form', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'form.min.css' );
		wp_enqueue_style( 'vi-woocommerce-alidropship-table', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'table.min.css' );
		wp_enqueue_style( 'vi-woocommerce-alidropship-icon', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'icon.min.css' );
		wp_enqueue_style( 'vi-woocommerce-alidropship-segment', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'segment.min.css' );
		wp_enqueue_style( 'vi-woocommerce-alidropship-button', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'button.min.css' );
		wp_enqueue_style( 'vi-woocommerce-alidropship-message', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'message.min.css' );
		wp_enqueue_style( 'select2', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'select2.min.css' );
		if ( woocommerce_version_check( '3.0.0' ) ) {
			wp_enqueue_script( 'select2' );
		} else {
			wp_enqueue_script( 'select2-v4', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'select2.js', array( 'jquery' ), '4.0.3' );
		}
	}


	public function admin_enqueue_scripts() {
		global $pagenow;
		$page = isset( $_REQUEST['page'] ) ? wp_unslash( sanitize_text_field( $_REQUEST['page'] ) ) : '';
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-alidropship-transfer-settings' ) {
			$this->enqueue_semantic();
		}
	}
}