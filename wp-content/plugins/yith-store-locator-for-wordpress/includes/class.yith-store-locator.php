<?php
/**
 * Main class
 *
 * @author YITH
 * @package YITH Store Locator
 * @version 1.0.0
 */

defined( 'YITH_SL' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'YITH_Store_Locator' ) ) {
	/**
	 * YITH Store Locator
	 *
	 * @since 1.0.0
	 */
	final class YITH_Store_Locator {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_Store_Locator
		 * @since 1.0.0
		 */
		private static $instance;


        /**
         * @var YITH_Store_Locator_Admin
         */
		public $admin;


        /**
         * @var YITH_Store_Locator_Frontend
         */
		public $frontend;


		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_Store_Locator
		 * @since 1.0.0
		 */
		public static function get_instance(){
			if( is_null( self::$instance ) ){
				self::$instance = new self();
			}
			return self::$instance;
		}


		/**
		 * Constructor
		 *
		 * @return void
		 * @since 1.0.0
		 */
		private function __construct() {

            // Load Plugin Framework
            add_action( 'after_setup_theme', [ $this, 'plugin_fw_loader' ], 15 );

            // Load Custom Post Type
            $this->load_custom_post_type();

            // Class admin
            if ( is_admin() && !defined( 'DOING_AJAX' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX && ( !isset( $_REQUEST[ 'context' ] ) || ( isset( $_REQUEST[ 'context' ] ) && $_REQUEST[ 'context' ] !== 'frontend' ) ) ) ) {
                require_once ( 'class.yith-store-locator-admin.php' );
                $this->admin = YITH_Store_Locator_Admin::get_instance();
            } else {
                require_once ( 'class.yith-store-locator-frontend.php' );
                $this->frontend = YITH_Store_Locator_Frontend::get_instance();
            }

            $this->shortcodes = YITH_Store_Locator_Shortcodes::get_instance();

            $this->filters = YITH_Store_Locator_Filters_Taxonomies();

            require_once ( 'assets/class.yith-store-locator-common-scripts.php' );
            YITH_Store_Locator_Common_Scripts();

            // Set transient yith_sl_stores which include the complete list of all stores
            add_action( 'init', [ $this, 'set_transient' ] );


        }

        /**
         * Load Plugin Framework
         *
         * @since  1.0
         * @access public
         * @return void
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function plugin_fw_loader() {
            if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if( ! empty( $plugin_fw_data ) ){
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
        }


        /**
         * Check if context is admin
         *
         * @since 1.0.0
         * @author Alessio Torrisi <alessio.torrisi@yithemes.com>
         * @return boolean
         */
        public function is_admin(){
            $is_ajax = ( defined( 'DOING_AJAX') && DOING_AJAX && isset( $_REQUEST['context'] ) && $_REQUEST['context'] == 'frontend' );
            return apply_filters( 'YITH_SL_is_admin', is_admin() && ! $is_ajax );
        }



        /**
         * Load custom post type inside plugin options panel
         */
        public function load_custom_post_type(){
            require_once ( 'class.yith-store-locator-post-type.php' );
            YITH_Store_Locator_Post_Type();
        }


        /**
         * Set transient "yith_sl_stores" which include the list of all stores
         */
        public function set_transient(){

            if( empty ( get_transient( 'yith_sl_stores' ) ) ){

                $args = array(
                    'posts_per_page' => -1,
                    'post_type'      => YITH_Store_Locator_Post_Type::$post_type_name,
                    'post_status'    => 'publish',
                    'fields'         => array('ids','post_title'),
                );
                $posts = get_posts($args);

                foreach ( $posts as $post ){
                    $stores[] = array(
                        'name'          =>  $post->post_title,
                        'id'            =>  $post->ID,
                        'slug'          =>  $post->post_name,
                        'latitude'      =>  get_post_meta( $post->ID,'_yith_sl_latitude',true ),
                        'longitude'     =>  get_post_meta( $post->ID,'_yith_sl_longitude',true ),
                    );
                }

                $stores = yith_sl_organize_store_informations( $posts );

                set_transient( 'yith_sl_stores', $stores );
            }
        }



	}
}

/**
 * Unique access to instance of YITH_Store_Locator class
 *
 * @return YITH_Store_Locator
 * @since 1.0.0
 */
function YITH_Store_Locator(){
	return YITH_Store_Locator::get_instance();
}
