<?php 

/*

	* Plugin Name: KoalaApps - Size Chart for WooCommerce

	* Plugin URI: https://woocommerce.com/products/size-chart/

	* Description: With size chart extension, merchants can now create multiple size guides and attach them to relevant products and categories

	* Author: KoalaApps

	* Author URI: http://www.koalaapps.net

	* Text Domain: koalaapps_psc

	* Version: 1.2.0

	* Woo: 5909114:1678d3022130e4acb65b83ea79984027

	* WC requires at least: 3.0.9

	* WC tested up to: 5.*.*

*/



// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {



	function KA_Psc_Admin_Notice() {



		$ka_psc_allowed_tags = array(

			'a' => array(

				'class' => array(),

				'href'  => array(),

				'rel'   => array(),

				'title' => array(),

			),

			'b' => array(),



			'div' => array(

				'class' => array(),

				'title' => array(),

				'style' => array(),

			),

			'p' => array(

				'class' => array(),

			),

			'strong' => array(),



		);



		// Deactivate the plugin

		deactivate_plugins(__FILE__);



		$ka_psc_woo_check = '<div id="message" class="error">

			<p><strong>KoalaApps Size Chart plugin is inactive.</strong> The <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce plugin</a> must be active for this plugin to work. Please install &amp; activate WooCommerce »</p></div>';

		echo wp_kses( __( $ka_psc_woo_check, 'koalaapps_psc' ), $ka_psc_allowed_tags);



	}

	add_action('admin_notices', 'KA_Psc_Admin_Notice');

}



if (!class_exists('KA_Pro_Size_Charts') ) {



	class KA_Pro_Size_Charts {



		public function __construct() {



			$this->KA_Psc_Global_Constents_Vars();

			

			add_action('wp_loaded', array( $this, 'KA_Psc_Init' ));

			add_action( 'init', array($this, 'KA_Psc_Custom_Post_Type' ));

			if (is_admin() ) {

				include_once KA_PSC_PLUGIN_DIR . 'admin/class-kapsc-admin.php';

			} else {

				include_once KA_PSC_PLUGIN_DIR . 'front/class-kapsc-front.php';

			}

		}

		

		public function KA_Psc_Global_Constents_Vars() {



			if (!defined('KA_PSC_URL') ) {

				define('KA_PSC_URL', plugin_dir_url(__FILE__));

			}



			if (!defined('KA_PSC_BASENAME') ) {

				define('KA_PSC_BASENAME', plugin_basename(__FILE__));

			}



			if (! defined('KA_PSC_PLUGIN_DIR') ) {

				define('KA_PSC_PLUGIN_DIR', plugin_dir_path(__FILE__));

			}

		}



		public function KA_Psc_Init() {

			if (function_exists('load_plugin_textdomain') ) {

				load_plugin_textdomain('koalaapps_psc', false, dirname(plugin_basename(__FILE__)) . '/languages/');

			}

		}



		public function KA_Psc_Custom_Post_Type() {



			$labels = array(

				'name'                => esc_html__('Size Chart', 'koalaapps_psc'),

				'singular_name'       => esc_html__('Size Chart', 'koalaapps_psc'),

				'add_new'             => esc_html__('Add New Chart', 'koalaapps_psc'),

				'add_new_item'        => esc_html__('Add New Chart', 'koalaapps_psc'),

				'edit_item'           => esc_html__('Edit Chart', 'koalaapps_psc'),

				'new_item'            => esc_html__('New Chart', 'koalaapps_psc'),

				'view_item'           => esc_html__('View Chart', 'koalaapps_psc'),

				'search_items'        => esc_html__('Search Chart', 'koalaapps_psc'),

				'exclude_from_search' => true,

				'not_found'           => esc_html__('No chart found', 'koalaapps_psc'),

				'not_found_in_trash'  => esc_html__('No chart found in trash', 'koalaapps_psc'),

				'parent_item_colon'   => '',

				'all_items'           => esc_html__('All Charts', 'koalaapps_psc'),

				'menu_name'           => esc_html__('Size Chart', 'koalaapps_psc'),

			);



			$args = array(

				'labels' => $labels,

				'menu_icon'  => '',

				'public' => false,

				'publicly_queryable' => false,

				'show_ui' => true,

				'show_in_menu' => false,

				'query_var' => true,

				'rewrite' => true,

				'capability_type' => 'post',

				'has_archive' => true,

				'hierarchical' => false,

				'menu_position' => 30,

				'rewrite' => array('slug' => 'koalaapps_psc', 'with_front'=>false ),

				'supports' => array('title')

			);



			register_post_type( 'koalaapps_psc', $args );



		}



		

	}



	new KA_Pro_Size_Charts();



}

