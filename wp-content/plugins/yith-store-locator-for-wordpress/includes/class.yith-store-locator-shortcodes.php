<?php
/**
 * Frontend class
 *
 * @author YITH
 * @package YITH_Store_Locator_Frontend
 * @version 1.0.0
 */

defined( 'YITH_SL' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'YITH_Store_Locator_Shortcodes' ) ) {
    /**
     * YITH_Store_Locator_Frontend
     *
     * @since 1.0.0
     */
    class YITH_Store_Locator_Shortcodes {


        /**
         * Single instance of the class
         *
         * @var YITH_Store_Locator_Shortcodes
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * @var string
         */
        private $_shortcodes = array(
            'yith_store_locator'            =>  array(
                'action'    =>  'show_store_locator_template',
                'atts'      =>  array(

                )
            ),
            'yith_store_locator_search'     =>  array(
                'action'    =>  'show_store_locator_search_box',
                'atts'      =>  array(

                )
            ),
            'yith_store_locator_map'        =>  array(
                'action'    =>  'show_store_locator_map',
                'atts'      =>  array(

                )
            ),
            'yith_store_locator_results'        =>  array(
                'action'    =>  'show_store_locator_results',
                'atts'      =>  array(

                )
            ),
            'yith_store_locator_filters'        =>  array(
                'action'    =>  'show_store_locator_filters',
                'atts'      =>  array(

                )
            ),
            'yith_store_locator_search_button'        =>  array(
                'action'    =>  'show_store_locator_search_button',
                'atts'      =>  array(

                )
            ),
        );



        /**
         * Returns single instance of the class
         *
         * @return YITH_Store_Locator_Shortcodes
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

            /* Shortcodes */
            $this->init_shortcodes();
        }


        private function init_shortcodes(){
            foreach ( $this->_shortcodes as $name => $args ){
                add_shortcode( $name, [ $this,$args['action'] ] );
            }
        }


        /**
         * Include Search Store Locators template
         * @param $atts
         * @param null $content
         * @return string
         */
        public function show_store_locator_template( $atts, $content = null ){
            ob_start();
            yith_sl_get_template( 'store-locator.php','frontend/shortcodes/' );
            return ob_get_clean();
        }


        /**
         * Show store locator search box template
         * @param $atts
         * @param null $content
         */
        public function show_store_locator_search_box( $atts, $content = null ){

            $args = shortcode_atts(array(
                'enable_search_bar'     =>  yith_sl_get_option( 'enable-search-bar', 'yes' ),
                'title_search_bar'      =>  yith_sl_get_option( 'title-search-bar', esc_html_x( 'Find our stores', 'Default text to show as title of Search Bar in store locator', 'yith-store-locator' ) ),
                'placeholder'           =>  yith_sl_get_option( 'placeholder-search-form', esc_html_x( 'Enter address / city', 'Placeholder to show for the search address input inside the store locator', 'yith-store-locator') ),
                'enable_geolocation'    =>  yith_sl_get_option( 'enable-geolocation','yes' ),
                'geolocation_style'     =>  yith_sl_get_option( 'geolocation-style','button' ),
                'geolocation_text'      =>  yith_sl_get_option( 'geolocation-text', esc_html_x( 'Use my position', 'Default text for the geolocalation button inside store locator', 'yith-store-locator' ) ),
                'enable_show_all_stores'=>  yith_sl_get_option( 'enable-view-all-stores','yes' ),
                'show_all_stores_text'  =>  yith_sl_get_option( 'view-all-stores-text', esc_html_x( 'View all stores', 'Text for the button "Show all stores" inside the store locator', 'yith-store-locator' ) ),
            ), $atts);

            yith_sl_get_template( 'search.php','frontend/shortcodes/',$args );

        }


        /**
         * Show store locator map template
         * @param $atts
         * @param null $content
         */
        public function show_store_locator_map( $atts, $content = null ){
            $height = yith_sl_get_option( 'map-height', '500' ) . 'px';
            $args = shortcode_atts(array(
                'height'    =>  $height
            ), $atts);

            yith_sl_get_template( 'map.php','frontend/shortcodes/',$args );

        }

        /**
         * Show store locator results
         * @param $atts
         * @param null $content
         */
        public function show_store_locator_results( $atts, $content = null ){

            yith_sl_get_template( 'results.php','frontend/shortcodes/' );

        }



        public function show_store_locator_filters( $atts, $content = null ){

            yith_sl_get_template( 'filters.php','frontend/shortcodes/' );
        }


        public function show_store_locator_search_button(  $atts, $content = null ){
            $args = array(
                'enable_instant_search'    =>  yith_sl_get_option( 'enable-instant-search','yes' ),
                'search_button_text'       =>  yith_sl_get_option( 'text-search-button','yes' ),
            );

            yith_sl_get_template( 'search-button.php','frontend/shortcodes/', $args );
        }




    }
}
