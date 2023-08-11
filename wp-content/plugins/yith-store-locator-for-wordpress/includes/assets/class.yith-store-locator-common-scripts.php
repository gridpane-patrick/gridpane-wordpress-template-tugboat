<?php
/**
 * Admin class
 *
 * @author YITH
 * @package YITH Store Locator
 * @version 1.0.0
 */

defined( 'YITH_SL' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'YITH_Store_Locator_Common_Scripts' ) ) {
    /**
     * YITH Store Locator
     *
     * @since 1.0.0
     */
    final class YITH_Store_Locator_Common_Scripts {

        /**
         * Single instance of the class
         *
         * @var YITH_Store_Locator_Admin
         * @since 1.0.0
         */
        private static $instance;


        /**
         * Returns single instance of the class
         *
         * @return YITH_Store_Locator_Common_Scripts
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

            add_action( 'admin_enqueue_scripts',[ $this,'register_scripts' ] );
            add_action( 'wp_enqueue_scripts', [ $this,'register_scripts' ] );
            add_action( 'admin_enqueue_scripts', [ $this,'enqueue_scripts' ] );
            add_action( 'wp_enqueue_scripts', [ $this,'enqueue_scripts' ] );

        }


        public function register_scripts(){

            $gmap_api_key = yith_sl_get_option( 'google-maps-api-key' );

            wp_register_script('yith-sl-google-map','https://maps.googleapis.com/maps/api/js?key='. $gmap_api_key .'&libraries=places', array(),false,true );



        }


        public function enqueue_scripts(){

            wp_enqueue_script('yith-sl-google-map');

        }

    }
}


function YITH_Store_Locator_Common_Scripts(){
    return YITH_Store_Locator_Common_Scripts::get_instance();
}