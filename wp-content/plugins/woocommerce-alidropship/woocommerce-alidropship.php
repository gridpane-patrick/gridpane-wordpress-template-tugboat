<?php
/**
 * Plugin Name: ALD - Aliexpress Dropshipping and Fulfillment for WooCommerce
 * Plugin URI: https://villatheme.com/extensions/aliexpress-dropshipping-and-fulfillment-for-woocommerce/
 * Description: Transfer data from AliExpress products to WooCommerce effortlessly and fulfill WooCommerce order to AliExpress automatically.
 * Version: 1.1.9
 * Author: VillaTheme(villatheme.com)
 * Author URI: http://villatheme.com
 * Text Domain: woocommerce-alidropship
 * Copyright 2020-2023 VillaTheme.com. All rights reserved.
 * Tested up to: 6.1
 * WC tested up to: 7.4
 * Requires PHP: 7.0
 **/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VI_WOOCOMMERCE_ALIDROPSHIP_VERSION', '1.1.9' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_DIR', plugin_dir_path( __FILE__ ) );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES', VI_WOOCOMMERCE_ALIDROPSHIP_DIR . "includes" . DIRECTORY_SEPARATOR );
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-ali-orders-info-table.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-ali-orders-info-table.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-ali-shipping-info-table.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-ali-shipping-info-table.php";
}
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "define.php";
}

/**
 * Class VI_WOOCOMMERCE_ALIDROPSHIP
 */
class VI_WOOCOMMERCE_ALIDROPSHIP {
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		add_action( 'admin_notices', array( $this, 'global_note' ) );
		add_action( 'admin_init', array( $this, 'update_db_new_version' ), 0 );
	}

	public function update_db_new_version() {
		$option = 'vi_wad_add_shipping_info_table_version_1.0.3';
		if ( ! get_option( $option ) ) {
			VI_WOOCOMMERCE_ALIDROPSHIP_Ali_Orders_Info_Table::create_table();
			VI_WOOCOMMERCE_ALIDROPSHIP_Ali_Shipping_Info_Table::create_table();
			VI_WOOCOMMERCE_ALIDROPSHIP_Ali_Shipping_Info_Table::add_column( 'ali_id' );
			update_option( $option, time() );
		}
		$option = 'vi_wad_update_access_token_new_app_1.1.0';
		if ( ! get_option( $option ) ) {
			$args = get_option( 'wooaliexpressdropship_params' );
			if ( isset( $args['access_tokens'] ) && is_array( $args['access_tokens'] ) && $args['access_tokens'] ) {
				foreach ( $args['access_tokens'] as &$access_token ) {
					$access_token['expire_time'] = 1000 * ( time() - DAY_IN_SECONDS );
				}
				update_option( 'wooaliexpressdropship_params', $args );
			}
			update_option( $option, time() );
		}
	}

	public function global_note() {
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			?>
            <div id="message" class="error">
                <p><?php _e( 'Please install and activate WooCommerce to use ALD - AliExpress Dropshipping and Fulfillment for WooCommerce plugin.', 'woocommerce-alidropship' ); ?></p>
            </div>
			<?php
		}
	}


	/**
	 * When active plugin Function will be call
	 */
	public function activate() {
		global $wp_version;
		if ( version_compare( $wp_version, "2.9", "<" ) ) {
			deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
			wp_die( "This plugin requires WordPress version 2.9 or higher." );
		}
		VI_WOOCOMMERCE_ALIDROPSHIP_Ali_Orders_Info_Table::create_table();
		VI_WOOCOMMERCE_ALIDROPSHIP_Ali_Shipping_Info_Table::create_table();
		if ( class_exists( 'VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table' ) ) {
			VI_WOOCOMMERCE_ALIDROPSHIP_Error_Images_Table::create_table();
		}
		$check_active = get_option( 'wooaliexpressdropship_params' );
		if ( ! $check_active ) {
			update_option( 'vi_wad_update_access_token_new_app_1.1.0', time() );
			if ( class_exists( 'VI_WOOCOMMERCE_ALIDROPSHIP_DATA' ) ) {
				$settings             = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
				$params               = $settings->get_params();
				$params['secret_key'] = md5( time() );
				if ( is_plugin_active( 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php' ) ) {
					/*Set default custom fields if Brazilian Market on WooCommerce plugin is active*/
					$params['cpf_custom_meta_key']            = '_billing_cpf';
					$params['billing_number_meta_key']        = '_billing_number';
					$params['shipping_number_meta_key']       = '_shipping_number';
					$params['billing_neighborhood_meta_key']  = '_billing_neighborhood';
					$params['shipping_neighborhood_meta_key'] = '_shipping_neighborhood';
				}
				update_option( 'wooaliexpressdropship_params', $params );
				add_action( 'activated_plugin', array( $this, 'after_activated' ) );
			}
		} else {
			if ( wp_next_scheduled( 'vi_wad_update_aff_urls' ) ) {
				wp_unschedule_hook( 'vi_wad_update_aff_urls' );
			}
			if ( class_exists( 'VI_WOOCOMMERCE_ALIDROPSHIP_DATA' ) ) {
				$settings = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_instance();
				$args     = $settings->get_params();
				if ( $args['exchange_rate_auto'] && ! wp_next_scheduled( 'vi_wad_auto_update_exchange_rate' ) ) {
					$schedule_time = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_schedule_time_from_local_time( $args['exchange_rate_hour'], $args['exchange_rate_minute'], $args['exchange_rate_second'] );
					wp_schedule_event( $schedule_time, 'vi_wad_exchange_rate_interval', 'vi_wad_auto_update_exchange_rate' );
				}
				if ( $args['update_product_auto'] && ! wp_next_scheduled( 'vi_wad_auto_update_product' ) ) {
					$schedule_time = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_schedule_time_from_local_time( $args['update_product_hour'], $args['update_product_minute'], $args['update_product_second'] );
					wp_schedule_event( $schedule_time, 'vi_wad_update_product_interval', 'vi_wad_auto_update_product' );
				}
				if ( $args['update_order_auto'] && ! wp_next_scheduled( 'vi_wad_auto_update_order' ) ) {
					$schedule_time = VI_WOOCOMMERCE_ALIDROPSHIP_DATA::get_schedule_time_from_local_time( $args['update_order_hour'], $args['update_order_minute'], $args['update_order_second'] );
					wp_schedule_event( $schedule_time, 'vi_wad_update_order_interval', 'vi_wad_auto_update_order' );
				}
			}
		}
	}

	/**
	 * When plugin is deactivated, unschedule hook and disable update rate option
	 */
	public function deactivate() {
		wp_unschedule_hook( 'vi_wad_auto_update_exchange_rate' );
		wp_unschedule_hook( 'vi_wad_auto_update_product' );
		wp_unschedule_hook( 'vi_wad_auto_update_order' );
	}

	public function after_activated( $plugin ) {
		if ( $plugin === plugin_basename( __FILE__ ) ) {
			$url = add_query_arg( array(
				'vi_wad_setup_wizard' => true,
				'_wpnonce'            => wp_create_nonce( 'vi_wad_setup' ),
			), admin_url() );
			exit( wp_redirect( $url ) );
		}
	}
}

new VI_WOOCOMMERCE_ALIDROPSHIP();