<?php
/**
 * Frontend class
 *
 * @author YITH
 * @package YITH_Store_Locator_Frontend
 * @version 1.0.0
 */

defined( 'YITH_SL' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'YITH_Store_Locator_Frontend' ) ) {
    /**
     * YITH_Store_Locator_Frontend
     *
     * @since 1.0.0
     */
    class YITH_Store_Locator_Frontend {


        /**
         * Single instance of the class
         *
         * @var YITH_Store_Locator_Frontend
         * @since 1.0.0
         */
        protected static $instance;


        /**
         * @var string
         */
        private $_ajax_get_results = 'get_results';


        /**
         * Returns single instance of the class
         *
         * @return YITH_Store_Locator_Frontend
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

            require_once ( 'assets/class.yith-store-locator-frontend-scripts.php' );
            YITH_Store_Locator_Frontend_Scripts();

            /* Ajax call */
            add_action( 'wp_ajax_yith_sl_' . $this->_ajax_get_results, [ $this,'get_results' ] );
            add_action( 'wp_ajax_nopriv_yith_sl_' . $this->_ajax_get_results, [ $this,'get_results' ] );

            /* Filter body class to add layout */
            add_filter( 'body_class', [ $this,'add_body_class' ] );

            /* Load template for single page layout of a store */
            add_filter('single_template', [ $this,'yith_sl_load_single_template' ], 100 );

            add_action( 'template_redirect',[ $this,'compose_store_locator_layout' ] );

            /* Add page loader */
            add_action( 'wp_footer', [ $this, 'add_page_loader' ] );

            // Hide header and footer
          //  add_filter( 'page_template', [ $this, 'locate_template' ] );

            add_filter( 'yith_sl_store_locator_options', [ $this, 'filter_options' ],10,2 );

        }


        /**
         * Ajax call used to print results on frontend
         */
        public function get_results(){

            $args = array(
                'filters'       =>  isset( $_POST['filters'] ) ? $_POST['filters'] : '',
                'latitude'      =>  isset( $_POST['latitude'] ) ? $_POST['latitude'] : '',
                'longitude'     =>  isset( $_POST['longitude'] ) ? $_POST['longitude'] : '',
                'radius_step'   =>  isset( $_POST['radius_step'] ) ? absint( $_POST['radius_step'] ) : get_option( 'yith_sl_default_radius', 50 )
            );

            $stores = yith_sl_get_stores( $args );

            $template_args = array(
                'stores'    =>  $stores,
                'filters'   =>  isset( $_POST['filters'] ) ? $_POST['filters'] : '',
            );

            ob_start();

            yith_sl_get_template( 'results.php', 'frontend/shortcodes/store-locator/', $template_args );

            $html_results = ob_get_clean();

            ob_start();

            yith_sl_get_template( 'active-filters.php','frontend/shortcodes/store-locator/',$args );

            $html_filters = ob_get_clean();

            $html = array(
                'active_filters'    =>  $html_filters,
                'results'           =>  $html_results,
                'markers'           =>  $stores,
            );
            wp_send_json($html);

            die();

        }


        /**
         * Load template for single store locator page
         * @param $single
         * @return string
         */
        public function yith_sl_load_single_template( $template ){
            global $post;

            if ( $post->post_type === YITH_Store_Locator_Post_Type::$post_type_name ) {
                $template = yith_sl_get_template( 'main.php', 'frontend/single/', array(), false );
            }

            return $template;

        }


        /**
         * Check if is store locator page
         * @return bool
         */
        public function is_store_page(){
            global $post;
            return $post && $post instanceof WP_Post && $post->post_type == YITH_Store_Locator_Post_Type::$post_type_name;
        }


        /**
         * Check if he page is the one used for search locators
         * @return bool
         */
        public function is_store_locator_page(){
            global $post;
            return $post && $post instanceof WP_Post && has_shortcode( $post->post_content,'yith_store_locator' );
        }


        /**
         * Add custom class to body class
         * @param $classes
         * @return array
         */
        public function add_body_class( $classes ){
            $single_layout                  = yith_sl_get_option('single-layout', 'classic' );
            $show_image                     = yith_sl_get_option('stores-list-show-store-image', 'yes' );
            $store_image_position           = yith_sl_get_option('stores-list-image-position', 'left' );
            $search_bar_filters_position    = yith_sl_get_option( 'search-bar-filters-position', 'beside-map' );
            $map_position                   = yith_sl_get_option( 'map-position', 'map-right' );
            $full_width_layout              = yith_sl_get_option( 'full-width-layout', 'no' );
            if( $this->is_store_page() ){
                $classes[] = 'yith-sl-layout-' . $single_layout;
            }elseif( $this->is_store_locator_page() ){
                $classes[] = $show_image === 'yes' ? 'yith-sl-with-image yith-sl-image-' . $store_image_position : 'yith-sl-no-image';
                $classes[] = 'filters-' . $search_bar_filters_position;
                $classes[] = $map_position;
                if( $full_width_layout === 'yes' ){
                    $classes [] = 'full-width';
                }
            }
            return $classes;
        }


        /**
         * Organize layout of Store Locator page
         */
        public function compose_store_locator_layout(){
            $show_results                       =   yith_sl_get_option( 'enable-stores-list', 'yes' );
            $shop_map                           =   yith_sl_get_option('enable-map', 'yes' );
            $show_filters                       =   yith_sl_get_option('enable-filters', 'yes' );
            $show_radius_filter                 =   yith_sl_get_option('enable-filter-radius', 'yes' );
            $search_bar_filters_position        =   yith_sl_get_option( 'search-bar-filters-position', 'beside-map' );
            $hook_for_search_bar_and_filters    =   $search_bar_filters_position === 'beside-map' ? 'yith-sl-store-locator-left-content' : 'yith-sl-before-main-sections';
            $hook_for_search_button             =   $search_bar_filters_position === 'beside-map' ? 'yith-sl-after-filters-container' : 'yith-sl-after-filters-list' ;

            add_action( $hook_for_search_bar_and_filters, [ YITH_Store_Locator_Shortcodes::get_instance(), 'show_store_locator_search_box' ] );
            if( $show_filters === 'yes' || $show_radius_filter === 'yes' ){
                add_action( $hook_for_search_bar_and_filters, [ YITH_Store_Locator_Shortcodes::get_instance(), 'show_store_locator_filters' ],15 );
                add_action( $hook_for_search_button, [ YITH_Store_Locator_Shortcodes::get_instance(), 'show_store_locator_search_button' ] );
            }else{
                add_action( 'yith-sl-after-search-store-container', [ YITH_Store_Locator_Shortcodes::get_instance(), 'show_store_locator_search_button' ] );
            }
            if( $show_results === 'yes' ){
                add_action( 'yith-sl-store-locator-left-content', [ YITH_Store_Locator_Shortcodes::get_instance(), 'show_store_locator_results' ], 20 );
            }
            if( $shop_map === 'yes' ){
                add_action( 'yith-sl-store-locator-right-content', [ YITH_Store_Locator_Shortcodes::get_instance(), 'show_store_locator_map' ] );
            }

        }

        /**
         * Add page loader inside the Store Locator page
         */
        public function add_page_loader(){
            if( $this->is_store_locator_page() ){
                yith_sl_get_template( 'loader.php', 'frontend/' );
            }
        }


        public function locate_template( $template ){
            if( $this->is_store_locator_page() ){
                $template = YITH_SL_TEMPLATE_PATH . '/frontend/store-locator.php';
                return $template;
            }
        }


        public function filter_options( $value, $key ){
            if( wp_is_mobile() && $key === 'pin-modal-trigger-event' ){
                $value = 'click';
            }
            return $value;
        }

    }
}
