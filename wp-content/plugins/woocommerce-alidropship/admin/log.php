<?php

/**
 * Class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Log
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
ini_set( 'auto_detect_line_endings', true );

class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Log {
	public function __construct() {
		add_action( 'wp_ajax_vi_wad_view_log', array( $this, 'generate_log_ajax' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 19 );
		add_action( 'admin_init', array( $this, 'encrypt_log_file' ), 0 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_init', array( $this, 'download_log_file' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wc_logs_enqueue_scripts' ) );
	}

	/**
	 * Use js to remove ALD- log files from WC/Status/Logs page because no PHP filters available
	 */
	public function wc_logs_enqueue_scripts() {
		global $pagenow;
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		$tab  = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';
		if ( $pagenow === 'admin.php' && $page === 'wc-status' && $tab === 'logs' ) {
			wp_enqueue_script( 'woocommerce-alidropship-wc-logs', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'wc-logs.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
		}
	}

	public function encrypt_log_file() {
		if ( ! get_option( 'vi_wad_log_file_prefix' ) ) {
			$prefix    = wp_hash( 'ald-log_' . time() );
			$log_files = self::log_files();
			foreach ( $log_files as $file_name ) {
				if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_CACHE . $file_name . '.txt' ) ) {
					@rename( VI_WOOCOMMERCE_ALIDROPSHIP_CACHE . $file_name . '.txt', VI_WOOCOMMERCE_ALIDROPSHIP_CACHE . $prefix . '_' . $file_name . '.txt' );
				}
			}
			update_option( 'vi_wad_log_file_prefix', $prefix );
		}
	}

	public function download_log_file() {
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
		if ( $nonce && wp_verify_nonce( $nonce, 'vi_wad_download_log' ) ) {
			$file = '';
			if ( isset( $_POST['vi_wad_download_log'] ) ) {
				$prefix   = get_option( 'vi_wad_log_file_prefix' );
				$log_file = sanitize_text_field( $_POST['vi_wad_download_log'] );
				if ( in_array( str_replace( $prefix . '_', '', $log_file ), $this->log_files() ) ) {
					$file = VI_WOOCOMMERCE_ALIDROPSHIP_CACHE . $log_file . '.txt';
				}
			} else {
				$logs = WC_Log_Handler_File::get_log_files();
				if ( ! empty( $_REQUEST['log_file'] ) && isset( $logs[ sanitize_title( wp_unslash( $_REQUEST['log_file'] ) ) ] ) ) { // WPCS: input var ok, CSRF ok.
					$log_file = $logs[ sanitize_title( wp_unslash( $_REQUEST['log_file'] ) ) ]; // WPCS: input var ok, CSRF ok.
					$file     = WC_LOG_DIR . $log_file;
				}
			}
			if ( is_file( $file ) ) {
				$fh = @fopen( 'php://output', 'w' );
				fprintf( $fh, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Content-Description: File Transfer' );
				header( 'Content-type: text/csv' );
				header( 'Content-Disposition: attachment; filename=' . $log_file . '__' . date( 'Y-m-d_H-i-s' ) . '.txt' );
				header( 'Expires: 0' );
				header( 'Pragma: public' );
				fputs( $fh, file_get_contents( $file ) );
				fclose( $fh );
				die();
			}
		}
	}

	public function admin_init() {
		global $pagenow;
		$page   = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		if ( $pagenow === 'admin.php' && $page === 'woocommerce-alidropship-logs' ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			if ( $action === 'vi_wad_delete_log' ) {
				$nonce    = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '';
				$log_file = isset( $_GET['vi_wad_file_name'] ) ? sanitize_text_field( $_GET['vi_wad_file_name'] ) : '';
				$prefix   = get_option( 'vi_wad_log_file_prefix' );
				if ( wp_verify_nonce( $nonce, 'vi_wad_delete_log' ) && in_array( str_replace( $prefix . '_', '', $log_file ), $this->log_files() ) ) {
					$file = VI_WOOCOMMERCE_ALIDROPSHIP_CACHE . $log_file . '.txt';
					if ( is_file( $file ) ) {
						wp_delete_file( $file );
						wp_safe_redirect( admin_url( 'admin.php?page=woocommerce-alidropship-logs' ) );
						exit();
					}
				}
			}
		}
	}

	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'woocommerce-alidropship-message', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'message.min.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
		wp_enqueue_style( 'woocommerce-alidropship-logs', VI_WOOCOMMERCE_ALIDROPSHIP_CSS . 'logs.css', '', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
		wp_enqueue_script( 'woocommerce-alidropship-logs', VI_WOOCOMMERCE_ALIDROPSHIP_JS . 'logs.js', array( 'jquery' ), VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
	}

	public function admin_menu() {
		$menu_slug = 'woocommerce-alidropship-logs';
		add_submenu_page( 'woocommerce-alidropship', esc_html__( 'Logs - AliExpress Dropshipping and Fulfillment for WooCommerce', 'woocommerce-alidropship' ), esc_html__( 'Logs', 'woocommerce-alidropship' ), apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', $menu_slug ), $menu_slug, array(
			$this,
			'page_callback'
		) );
	}

	public function log_files() {
		return array(
			'update_products',
			'cron_update_products',
			'cron_update_orders',
			'migrate_products',
			'debug'
		);
	}

	public function page_callback() {
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Your logs show here', 'woocommerce-alidropship' ) ?></h2>
            <form class="" action="" method="post">
				<?php
				wp_nonce_field( 'vi_wad_download_log' );
				$log_files = $this->log_files();
				$old_log   = '';
				foreach ( $log_files as $log_file ) {
					$log_file = self::build_log_file_name( $log_file );
					if ( is_file( $log_file ) ) {
						ob_start();
						?>
                        <li>
							<?php
							self::print_log_html( array( $log_file ) );
							?>
                        </li>
						<?php
						$old_log .= ob_get_clean();
					}
				}
				if ( $old_log ) {
					?>
                    <div class="vi-ui warning message">
                        <div class="header"><?php esc_html_e( 'Below are log files from versions before 1.0.9', 'woocommerce-alidropship' ) ?></div>
                        <ul class="list">
							<?php
							echo $old_log;
							?>
                        </ul>
                    </div>
					<?php
				}
				?>
            </form>
            <div class="vi-ui positive message">
                <div class="header"><?php esc_html_e( 'Since version 1.0.9, all log files are stored in the same log folder of WooCommerce.', 'woocommerce-alidropship' ) ?></div>
                <ul class="list">
                    <li><?php printf( esc_html__( 'Log folder: %s', 'woocommerce-alidropship' ), WC_LOG_DIR ) ?></li>
                    <li><?php printf( esc_html__( 'Log files older than %s days will be automatically deleted by WooCommerce', 'woocommerce-alidropship' ), apply_filters( 'woocommerce_logger_days_to_retain_logs', 30 ) ) ?></li>
                </ul>
            </div>
			<?php
			if ( class_exists( 'WC_Log_Handler_File' ) ) {
				$logs = WC_Log_Handler_File::get_log_files();
				if ( count( $logs ) ) {
					foreach ( $logs as $key => $value ) {
						if ( substr( $key, 0, 4 ) !== 'ald-' ) {
							unset( $logs[ $key ] );
						}
					}
				}
				if ( ! empty( $_REQUEST['log_file'] ) && isset( $logs[ sanitize_title( wp_unslash( $_REQUEST['log_file'] ) ) ] ) ) { // WPCS: input var ok, CSRF ok.
					$viewed_log = $logs[ sanitize_title( wp_unslash( $_REQUEST['log_file'] ) ) ]; // WPCS: input var ok, CSRF ok.
				} elseif ( ! empty( $logs ) ) {
					$viewed_log = current( $logs );
				}

				if ( ! empty( $_REQUEST['handle'] ) ) { // WPCS: input var ok, CSRF ok.
					if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'remove_log' ) ) { // WPCS: input var ok, sanitization ok.
						wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
					}
					if ( ! empty( $_REQUEST['handle'] ) ) {  // WPCS: input var ok.
						$log_handler = new WC_Log_Handler_File();
						$log_handler->remove( wp_unslash( $_REQUEST['handle'] ) ); // WPCS: input var ok, sanitization ok.
					}
					wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=woocommerce-alidropship-logs' ) ) );
					exit();
				}
				$log_of = isset( $_REQUEST['log_of'] ) ? sanitize_text_field( $_REQUEST['log_of'] ) : '';
				if ( $logs ) {
					?>
                    <div id="log-viewer-select">
                        <div class="alignleft">
                            <h2>
								<?php
								echo esc_html( $viewed_log );
								if ( ! empty( $viewed_log ) ) { ?>
                                    <a class="page-title-action vi-wad-delete-log"
                                       href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'handle' => sanitize_title( $viewed_log ) ), admin_url( 'admin.php?page=woocommerce-alidropship-logs' ) ), 'remove_log' ) ); ?>"
                                       class="button"><?php esc_html_e( 'Delete log', 'woocommerce' ); ?></a>
									<?php
								}
								?>
                            </h2>
                        </div>
                        <div class="alignright">
                            <form class="vi-wad-logs-form"
                                  action="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-alidropship-logs' ) ); ?>"
                                  method="post">
                                <select name="log_of">
                                    <option value=""><?php esc_html_e( 'All log files', 'woocommerce-alidropship' ) ?></option>
                                    <option value="manual-products-sync" <?php selected( $log_of, 'manual-products-sync' ); ?>><?php esc_html_e( 'Manual products sync', 'woocommerce-alidropship' ) ?></option>
                                    <option value="api-products-sync" <?php selected( $log_of, 'api-products-sync' ); ?>><?php esc_html_e( 'API products sync', 'woocommerce-alidropship' ) ?></option>
                                    <option value="migrate-products" <?php selected( $log_of, 'migrate-products' ); ?>><?php esc_html_e( 'Products migration', 'woocommerce-alidropship' ) ?></option>
                                    <option value="api-orders-sync" <?php selected( $log_of, 'api-orders-sync' ); ?>><?php esc_html_e( 'Orders sync', 'woocommerce-alidropship' ) ?></option>
                                    <option value="api-fulfill-order" <?php selected( $log_of, 'api-fulfill-order' ); ?>><?php esc_html_e( 'Auto fulfill order', 'woocommerce-alidropship' ) ?></option>
                                    <option value="debug" <?php selected( $log_of, 'debug' ); ?>><?php esc_html_e( 'Debug', 'woocommerce-alidropship' ) ?></option>
                                </select>
                                <select name="log_file">
                                    <option value=""><?php esc_html_e( 'No files found', 'woocommerce-alidropship' ) ?></option>
									<?php
									foreach ( $logs as $log_key => $log_file ) {
										$timestamp = filemtime( WC_LOG_DIR . $log_file );
										$date      = sprintf(
										/* translators: 1: last access date 2: last access time 3: last access timezone abbreviation */
											__( '%1$s at %2$s %3$s', 'woocommerce' ),
											wp_date( wc_date_format(), $timestamp ),
											wp_date( wc_time_format(), $timestamp ),
											wp_date( 'T', $timestamp )
										);
										?>
                                        <option value="<?php echo esc_attr( $log_key ); ?>" <?php selected( sanitize_title( $viewed_log ), $log_key ); ?>><?php echo esc_html( $log_file ); ?>
                                            (<?php echo esc_html( $date ); ?>)
                                        </option>
										<?php
									}
									?>
                                </select>
                                <button type="submit" class="button"
                                        value="<?php esc_attr_e( 'View', 'woocommerce' ); ?>"><?php esc_html_e( 'View', 'woocommerce' ); ?></button>
                                <button type="submit" class="button" name="_wpnonce"
                                        title="<?php esc_attr_e( 'Download selected log file to your device', 'woocommerce-alidropship' ); ?>"
                                        value="<?php echo esc_attr( wp_create_nonce( 'vi_wad_download_log' ) ); ?>"><?php esc_html_e( 'Download', 'woocommerce-alidropship' ); ?></button>
                            </form>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div id="log-viewer">
                        <pre><?php echo esc_html( file_get_contents( WC_LOG_DIR . $viewed_log ) ); ?></pre>
                    </div>
					<?php
				} else {
					?>
                    <div class="updated woocommerce-message inline">
                        <p><?php esc_html_e( 'There are currently no logs to view.', 'woocommerce' ); ?></p></div>
					<?php
				}
			}
			?>
        </div>
		<?php
	}

	public function generate_log_ajax() {
		check_ajax_referer( 'woocommerce_alidropship_admin_ajax', '_vi_wad_ajax_nonce' );
		/*Check the nonce*/
		if ( ! current_user_can( apply_filters( 'vi_wad_admin_sub_menu_capability', 'manage_options', 'woocommerce-alidropship-logs' ) ) || empty( $_GET['action'] ) || ! check_admin_referer( wp_unslash( $_GET['action'] ) ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'woocommerce-alidropship' ) );
		}
		if ( empty( $_GET['vi_wad_file'] ) ) {
			wp_die( esc_html__( 'No log file selected.', 'woocommerce-alidropship' ) );
		}
		$file = urldecode( wp_unslash( $_GET['vi_wad_file'] ) );
		if ( ! is_file( $file ) ) {
			wp_die( esc_html__( 'Log file not found.', 'woocommerce-alidropship' ) );
		}
		echo( wp_kses_post( nl2br( file_get_contents( $file ) ) ) );
		exit();
	}

	public static function error_log( $logs_content = '' ) {
		self::log( $logs_content, 'debug.txt' );
	}

	public static function wc_log( $content, $source = 'debug', $level = 'info' ) {
		$content = strip_tags( $content );
		$log     = wc_get_logger();
		$log->log( $level,
			$content,
			array(
				'source' => 'ALD-' . $source,
			)
		);
	}

	public static function log( $logs_content = '', $file_name = 'update_products.txt' ) {
		$log_file = self::build_log_file_name( $file_name );
		if ( $logs_content ) {
			$logs_content = "[" . date( "Y-m-d H:i:s" ) . "] {$logs_content}";
		}
		$logs_content .= PHP_EOL;
		if ( is_file( $log_file ) ) {
			file_put_contents( $log_file, $logs_content, FILE_APPEND );
		} else {
			self::create_plugin_cache_folder();
			file_put_contents( $log_file, $logs_content );
		}
	}

	public static function print_log_html( $logs ) {
		if ( is_array( $logs ) && count( $logs ) ) {
			$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
			foreach ( $logs as $log ) {
				?>
                <p><?php esc_html_e( $log ) ?>
                    <a target="_blank" href="<?php echo esc_url( add_query_arg( array(
						'action'             => 'vi_wad_view_log',
						'_vi_wad_ajax_nonce' => VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Settings::create_ajax_nonce(),
						'vi_wad_file'        => urlencode( $log ),
						'_wpnonce'           => wp_create_nonce( 'vi_wad_view_log' ),
					), admin_url( 'admin-ajax.php' ) ) ) ?>"><?php esc_html_e( 'View log', 'woocommerce-alidropship' ) ?>
                    </a>
					<?php
					if ( $page === 'woocommerce-alidropship-logs' ) {
						$file_name = explode( '.', substr( $log, strlen( VI_WOOCOMMERCE_ALIDROPSHIP_CACHE ) ) )[0];
						?>
                        ,
                        <a class="vi-wad-delete-log" href="<?php echo esc_url( add_query_arg( array(
							'action'           => 'vi_wad_delete_log',
							'vi_wad_file_name' => $file_name,
							'_wpnonce'         => wp_create_nonce( 'vi_wad_delete_log' ),
						) ) ) ?>"><?php esc_html_e( 'Delete', 'woocommerce-alidropship' ) ?>
                        </a>
						<?php
						esc_html_e( ' or ', 'woocommerce-alidropship' );
						?>
                        <button type="submit" name="vi_wad_download_log" value="<?php echo esc_attr( $file_name ) ?>"
                                class="vi-wad-download-log"><?php esc_html_e( 'Download log file', 'woocommerce-alidropship' ) ?>
                        </button>
						<?php
					}
					?>
                </p>
				<?php
			}
		}
	}

	public static function create_plugin_cache_folder() {
		if ( ! is_dir( VI_WOOCOMMERCE_ALIDROPSHIP_CACHE ) ) {
			wp_mkdir_p( VI_WOOCOMMERCE_ALIDROPSHIP_CACHE );
			file_put_contents( VI_WOOCOMMERCE_ALIDROPSHIP_CACHE . '.htaccess', '<IfModule !mod_authz_core.c>
Order deny,allow
Deny from all
</IfModule>
<IfModule mod_authz_core.c>
  <RequireAll>
    Require all denied
  </RequireAll>
</IfModule>
' );
		}
	}

	public static function build_log_file_name( $log_file ) {
		$prefix = get_option( 'vi_wad_log_file_prefix' );
		if ( $prefix ) {
			$prefix .= '_';
		}
		$ext      = '';
		$pathinfo = pathinfo( $log_file );
		if ( empty( $pathinfo['extension'] ) ) {
			$ext = '.txt';
		}

		return VI_WOOCOMMERCE_ALIDROPSHIP_CACHE . $prefix . $log_file . $ext;
	}
}
