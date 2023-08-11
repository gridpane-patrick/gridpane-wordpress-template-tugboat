<?php
/**
 * Admin class
 *
 * @author YITH
 * @package YITH Store Locator
 * @version 1.0.0
 */

defined( 'YITH_SL' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'YITH_Store_Locator_Frontend_Scripts' ) ) {
    /**
     * YITH Store Locator
     *
     * @since 1.0.0
     */
    final class YITH_Store_Locator_Frontend_Scripts {

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
         * @return YITH_Store_Locator_Frontend_Scripts
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

            add_action( 'wp_enqueue_scripts',[ $this, 'register_scripts' ] );

            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );


        }


        public function register_scripts(){

            $suffix = (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

            wp_register_style( 'yith-sl-single',YITH_SL_ASSETS_URL . 'css/frontend/single' . $suffix . '.css',YITH_SL_VERSION );
            wp_register_style( 'yith-sl-frontend',YITH_SL_ASSETS_URL . 'css/frontend/frontend' . $suffix . '.css',array(), YITH_SL_VERSION );
            wp_register_script( 'yith-store-locator',YITH_SL_ASSETS_URL . 'js/frontend/store-locator' . $suffix . '.js', array( 'jquery', 'yith-sl-google-map' ),YITH_SL_VERSION );
            wp_register_script( 'yith-store-locator-filters-dropdown',YITH_SL_ASSETS_URL . 'js/frontend/filters-dropdown' . $suffix . '.js', array( 'jquery' ),YITH_SL_VERSION );
            wp_register_script( 'yith-sl-single',YITH_SL_ASSETS_URL . 'js/frontend/single' . $suffix . '.js', array( 'jquery', ),YITH_SL_VERSION );

            if( defined( 'YITH_PROTEO_VERSION' ) ){
                wp_register_script( 'yith-sl-proteo',YITH_SL_ASSETS_URL . 'third-parts/proteo/proteo' . $suffix . '.js', array( 'jquery', ),YITH_SL_VERSION );
            }

        }


        public function enqueue_scripts(){

            $custom_css = yith_sl_get_option('custom-css');

            if( YITH_Store_Locator_Frontend::get_instance()->is_store_page() ){

                $settings_css = require_once ( YITH_SL_PATH . 'assets/css/settings.php' );
                wp_enqueue_style( 'yith-sl-single' );
                wp_enqueue_script( 'yith-sl-single' );
                wp_add_inline_style( 'yith-sl-single', $settings_css );
                wp_add_inline_style( 'yith-sl-single', $custom_css );

            }elseif( YITH_Store_Locator_Frontend::get_instance()->is_store_locator_page() ){

                $settings_css = require_once ( YITH_SL_PATH . 'assets/css/settings.php' );
                wp_enqueue_style( 'yith-sl-frontend' );
                wp_add_inline_style( 'yith-sl-frontend', $settings_css );
                wp_add_inline_style( 'yith-sl-frontend', $custom_css );

                wp_enqueue_script( 'yith-store-locator' );
                wp_enqueue_script( 'yith-store-locator-filters-dropdown' );

                $args = array(
                    'show_map'                              =>  yith_sl_get_option('enable-map', 'yes' ),
                    'show_results'                          =>  yith_sl_get_option('enable-stores-list', 'yes' ),
                    'show_stores_by_default'                =>  yith_sl_get_option('stores-list-by-default', 'no' ),
                    'map_longitude'                         =>  yith_sl_get_option('map-default-position', array( 'longitude' => '15.142760' ) )['longitude'],
                    'map_latitude'                          =>  yith_sl_get_option('map-default-position', array( 'latitude' => '37.586310' ) )['latitude'],
                    'map_type'                              =>  yith_sl_get_option('map-default-type', 'roadmap' ),
                    'map_zoom'                              =>  yith_sl_get_option('map-default-zoom', 10 ),
                    'map_user_icon'                         =>  yith_sl_get_option( 'map-icon-user-position', YITH_SL_ASSETS_URL .'images/store-locator/user-position.svg' ),
                    'map_style'                             =>  json_decode( yith_sl_get_option( 'map-style', '' ) ),
                    'map_scroll_type'                       =>  yith_sl_get_option( 'map-scroll-type', 'cooperative' ),
                    'show_circle'                           =>  yith_sl_get_option( 'enable-circle', 'yes' ),
                    'circle_border_color'                   =>  yith_sl_get_option('circle-colors', array( 'border' => '#18BCA9' ) )['border'],
                    'circle_border_weight'                  =>  yith_sl_get_option( 'circle-border-weigth', '2' ),
                    'circle_background_color'               =>  yith_sl_get_option('circle-colors', array( 'background' => '#18BCA9' ) )['background'],
                    'pin_modal_trigger_event'               =>  yith_sl_get_option( 'pin-modal-trigger-event', 'mouseover' ),
                    'ajaxurl'                               =>  admin_url('admin-ajax.php'),
                    'alert_geolocalization_not_supported'   =>  esc_html__( 'Geolocalization not supported by your browser', 'yith-store-locator' ),
                    'alert_calculate_position_error'        =>  esc_html__( 'It\'s not possible geolocate your position', 'yith-sore-locator' ),
                    'action_get_results'                    => 'yith_sl_get_results',
                    'autogeolocation'                       =>  yith_sl_get_option('enable-autogeolocation', 'no' ),
                    'autosearch'                            =>  yith_sl_is_autosearch_enabled(),
                    'filter_radius_default_step'            =>  get_option( 'yith_sl_default_radius', '50' ),
                    'filter_radius_distance_unit'           =>  yith_sl_get_option('filter-radius-distance-unit', 'km' ),
                    'show_filters_with_results'             =>  yith_sl_get_option( 'show-filters-with-results', 'yes' ),
                    'search_bar_filters_position'           =>  yith_sl_get_option( 'search-bar-filters-position', 'beside-map' ),
                    'filter_radius_title'                   =>  YITH_Store_Locator_Filters_Taxonomies()->get_filter_radius_title(),
                    'results_columns'                       =>  yith_sl_get_option( 'results-columns', 'one' ),
                    'full_width_layout'                     =>  yith_sl_get_option('full-width-layout', 'no' ),
                    'left_padding_full_width_layout'        =>  yith_sl_get_option('side-padding-full-width', array( 'left'  =>  0 ) )['left'],
                    'right_padding_full_width_layout'       =>  yith_sl_get_option('side-padding-full-width', array( 'right'  =>  0 ) )['right'],
                    'geocode_no_results'                    =>  esc_html__( 'No results found', 'yith-store-locator' ),
                    'geocode_failed_to'                     =>  esc_html__( 'Geocoder failed due to:', 'yith-store-locator' )
                );
                wp_localize_script( 'yith-store-locator','yith_sl_store_locator',$args );
            }

            if( defined( 'YITH_PROTEO_VERSION' ) ){
                wp_enqueue_script( 'yith-sl-proteo' );
            }

            /* Inline scripts */

            $custom_js = yith_sl_get_option('custom-js' );
            wp_add_inline_script( 'jquery', $custom_js );

        }

    }
}

function YITH_Store_Locator_Frontend_Scripts(){
    return YITH_Store_Locator_Frontend_Scripts::get_instance();
}