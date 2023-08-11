<?php
/**
 * Admin class
 *
 * @author YITH
 * @package YITH Store Locator
 * @version 1.0.0
 */

defined( 'YITH_SL' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'YITH_Store_Locator_Admin_Scripts' ) ) {
    /**
     * YITH Store Locator
     *
     * @since 1.0.0
     */
    final class YITH_Store_Locator_Admin_Scripts {

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
         * @return YITH_Store_Locator_Admin_Scripts
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

            add_action( 'admin_enqueue_scripts', [ $this,'enqueue_scripts' ] );

        }


        public function register_scripts(){

            $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

            wp_register_style( 'yith_sl_admin_style', YITH_SL_ASSETS_URL . 'css/admin/admin'. $suffix .'.css',array(),YITH_SL_VERSION );
            wp_register_script( 'yith-sl-admin-panel', YITH_SL_ASSETS_URL . 'js/admin/panel' . $suffix . '.js', array( 'jquery', 'jquery-ui-sortable' ),YITH_SL_VERSION,true );
            wp_register_script( 'yith-sl-admin-store', YITH_SL_ASSETS_URL . 'js/admin/store' . $suffix . '.js', array( 'jquery','yith-sl-google-map' ),YITH_SL_VERSION,true );



        }


        public function enqueue_scripts(){
            global $post;

            if( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'yith_sl_store' || isset( $post ) && $post->post_type === YITH_Store_Locator_Post_Type::$post_type_name && isset( $_GET['post'] )){
                wp_enqueue_style('yith_sl_admin_style' );
                wp_enqueue_script( 'yith-sl-admin-store' );
            }elseif( isset( $_GET['page'] ) && $_GET['page'] === 'yith_sl_panel' || isset( $_GET['sub_tab'] ) && $_GET['sub_tab'] === 'stores-filters' ) {

                wp_enqueue_style('yith_sl_admin_style' );
                wp_enqueue_script('yith-sl-admin-panel' );
                wp_enqueue_media();
                wp_enqueue_script('yith-plugin-fw-fields');
            }

            $args = array(
                'ajaxurl'                       =>  admin_url('admin-ajax.php'),
                'alert_delete_filter'           =>  esc_html__( 'Are you sure to delete filter and its terms?', 'yith-store-locator' ),
                'notice_filter_label_required'  =>  esc_html__( 'Filter label is a required field', 'yith-store-locator' ),
                'notice_no_filters_exists'      =>  esc_html__( 'No filters currently exist.', 'yith-store-locator' )
            );
            wp_localize_script( 'yith-sl-admin-panel','yith_sl_admin',$args );
            wp_localize_script( 'yith-sl-admin-store','yith_sl_admin',$args );

        }

    }
}

function YITH_Store_Locator_Admin_Scripts(){
    return YITH_Store_Locator_Admin_Scripts::get_instance();
}