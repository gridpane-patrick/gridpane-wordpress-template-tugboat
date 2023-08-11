<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_ALIDROPSHIP_Admin_Admin {

	public function __construct() {
		add_filter(
			'plugin_action_links_woocommerce-alidropship/woocommerce-alidropship.php', array(
				$this,
				'settings_link'
			)
		);
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'vi_wad_print_scripts', array( $this, 'dismiss_notice' ) );
	}

	/**
	 * Link to Settings
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public function settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=woocommerce-alidropship" title="' . esc_attr__( 'Settings', 'woocommerce-alidropship' ) . '">' . esc_html__( 'Settings', 'woocommerce-alidropship' ) . '</a>';

		array_unshift( $links, $settings_link );

		return $links;
	}


	/**
	 * Function init when run plugin+
	 */
	public function init() {
		/*Register post type*/
		load_plugin_textdomain( 'woocommerce-alidropship' );
		$this->load_plugin_textdomain();
		if ( class_exists( 'VillaTheme_Support_Pro' ) ) {
			new VillaTheme_Support_Pro(
				array(
					'support'   => 'https://villatheme.com/supports/forum/plugins/aliexpress-dropshipping-and-fulfillment-for-woocommerce/',
					'docs'      => 'http://docs.villatheme.com/?item=aliexpress-dropshipping-and-fulfillment-for-woocommerce',
					'review'    => 'https://codecanyon.net/downloads',
					'css'       => VI_WOOCOMMERCE_ALIDROPSHIP_CSS,
					'image'     => VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES,
					'slug'      => 'woocommerce-alidropship',
					'menu_slug' => 'woocommerce-alidropship',
					'version'   => VI_WOOCOMMERCE_ALIDROPSHIP_VERSION,
				)
			);
		}
	}


	/**
	 * load Language translate
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-alidropship' );
		load_textdomain( 'woocommerce-alidropship', VI_WOOCOMMERCE_ALIDROPSHIP_LANGUAGES . "woocommerce-alidropship-$locale.mo" );
		load_plugin_textdomain( 'woocommerce-alidropship', false, VI_WOOCOMMERCE_ALIDROPSHIP_LANGUAGES );
	}


	public function dismiss_notice() {
		update_user_meta( get_current_user_id(), 'vi_wad_show_notice', VI_WOOCOMMERCE_ALIDROPSHIP_VERSION );
	}
}
