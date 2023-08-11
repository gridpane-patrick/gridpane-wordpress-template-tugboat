<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'VI_WOOCOMMERCE_ALIDROPSHIP_Draft_Product' ) ) {
	class VI_WOOCOMMERCE_ALIDROPSHIP_Draft_Product {

		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );
		}

		public function init() {
			/*Register post type*/
			$this->register_post_type();
			self::register_post_status();
		}

		public static function register_post_status() {
			register_post_status( 'override', array(
				'label'                     => _x( 'Override', 'Order status', 'woocommerce-alidropship' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => false,
				/* translators: %s: number of orders */
				'label_count'               => '',
			) );
		}

		/**
		 * Register post type email
		 */
		protected function register_post_type() {
			if ( post_type_exists( 'vi_wad_draft_product' ) ) {
				return;
			}
			$labels = array(
				'name'               => _x( 'Ali product', 'woocommerce-alidropship' ),
				'singular_name'      => _x( 'Ali product', 'woocommerce-alidropship' ),
				'edit_item'          => __( 'Edit', 'woocommerce-alidropship' ),
				'view_item'          => __( 'View', 'woocommerce-alidropship' ),
				'all_items'          => __( 'All products', 'woocommerce-alidropship' ),
				'search_items'       => __( 'Search product', 'woocommerce-alidropship' ),
				'not_found'          => __( 'No draft product found.', 'woocommerce-alidropship' ),
				'not_found_in_trash' => __( 'No draft product found in Trash.', 'woocommerce-alidropship' )
			);
			$args   = array(
				'labels'              => $labels,
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'query_var'           => true,
				'capability_type'     => 'post',
				'capabilities'        => array(
					'create_posts' => false,
				),
				'map_meta_cap'        => true,
				'has_archive'         => false,
				'hierarchical'        => false,
				'menu_position'       => 2,
				'supports'            => array( 'title' ),
				'exclude_from_search' => true,
			);
			register_post_type( 'vi_wad_draft_product', $args );
		}
	}
}

new VI_WOOCOMMERCE_ALIDROPSHIP_Draft_Product();