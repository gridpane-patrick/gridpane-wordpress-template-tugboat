<?php
/**
 * Admin class
 *
 * @author YITH
 * @package YITH Store Locator
 * @version 1.0.0
 */

defined( 'YITH_SL' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'YITH_Store_Locator_Admin' ) ) {
    /**
     * YITH Store Locator
     *
     * @since 1.0.0
     */
    class YITH_Store_Locator_Admin {


        /**
         * Single instance of the class
         *
         * @var YITH_Store_Locator_Admin
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * @var $panel Object
         */
        protected $panel = null;

        /**
         * @var string Plugin panel page
         */
        protected $panel_page = 'yith_sl_panel';


        public $filters;


        /**
         * Returns single instance of the class
         *
         * @return YITH_Store_Locator_Admin
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

            // Add panel options
            add_action( 'admin_menu', [ $this, 'register_panel' ], 5 );
            // Add action links
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_SL_PATH . '/' . basename( YITH_SL_FILE ) ), [ $this, 'action_links' ] );
            add_filter( 'yith_show_plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 5 );

            // Register plugin to licence/update system
            add_action( 'wp_loaded', [ $this, 'register_plugin_for_activation' ], 99 );
            add_action( 'admin_init', [ $this, 'register_plugin_for_updates' ] );

            require_once ( 'assets/class.yith-store-locator-admin-scripts.php' );
            YITH_Store_Locator_Admin_Scripts();

            /* Show taxonomies inside Store Locators tab */
            add_filter('yith_plugin_fw_panel_yith_sl_panel_get_taxonomy_tabs', array($this, 'panel_tabs_in_taxonomies'), 10, 2);

            add_action( 'wp_loaded', [ $this, 'create_page' ],100 );

        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0.0
         * @author   Alessio Torrisi <alessio.torrisi@yithemes.com>
         * @use      /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( ! empty( $this->panel ) ) {
                return;
            }

            $admin_tabs = array(
                'stores'                => esc_html_x( 'Stores', 'Tab name in plugin options panel','yith-store-locator' ),
                'general'               => esc_html_x( 'General', 'Tab name in plugin options panel', 'yith-store-locator' ),
                'store-locator-page'    => esc_html_x( 'Store Locator Page', 'Tab name in plugin options panel', 'yith-store-locator' ),
                //'map'                   => esc_html_x( 'Map', 'Tab name in plugin options panel', 'yith-store-locator' ),
                //'page-layout'           => esc_html_x( 'Page layout', 'Tab name in plugin options panel', 'yith-store-locator' ),
                'search'                => esc_html_x( 'Search', 'Tab name in plugin options panel', 'yith-store-locator' ),
                //'filters'               => esc_html_x( 'Filters', 'Tab name in plugin options panel', 'yith-store-locator' ),
                'stores-results'        => esc_html_x( 'Results list', 'Tab name in plugin options panel', 'yith-store-locator' ),

            );

            $args = array(
                'create_menu_page'      => true,
                'parent_slug'           => '',
                'page_title'            => 'YITH Store Locator',
                'plugin_description'    => esc_html_x( '#', 'plugin description on options page', 'yith-store-locator' ),
                'menu_title'            => 'Store Locator',
                'capability'            => 'manage_options',
                'parent'                => 'store-locator',
                'parent_page'           => 'yith_plugin_panel',
                'page'                  => $this->panel_page,
                'admin-tabs'            => $admin_tabs,
                'options-path'          => YITH_SL_PATH . '/plugin-options',
                'class'                 => yith_set_wrapper_class( 'yith-store-locator' )
            );


            $this->panel = new YIT_Plugin_Panel( $args );
        }

        
        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @param $links | links plugin array
         *
         * @since    1.0.0
         * @author   Alessio Torrisi <alessio.torrisi@yithemes.com>
         * @return   mixed
         * @use      plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {
            $links = yith_add_action_links( $links, $this->panel_page, true );
            return $links;
        }

        /**
         * plugin_row_meta
         *
         * add the action links to plugin admin page
         *
         * @param $new_row_meta_args
         * @param $plugin_meta
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         *
         * @return   array
         * @since    1.0.0
         * @author   Alessio Torrisi <alessio.torrisi@yithemes.com>
         * @use plugin_row_meta
         */
        public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status ) {
            if ( defined( 'YITH_SL_INIT' ) && YITH_SL_INIT === $plugin_file ) {
                $new_row_meta_args['slug']          = YITH_SL_SLUG;
                $new_row_meta_args['is_premium']    = true;
            }

            return $new_row_meta_args;
        }

        /**
         * Register plugins for activation tab
         *
         * @return   void
         * @since    1.0.0
         * @author   Alessio Torrisi <alessio.torrisi@yithemes.com>
         */
        public function register_plugin_for_activation() {
            if( ! class_exists( 'YIT_Plugin_Licence' ) ) {
                require_once( '../plugin-upgrade/lib/yit-licence.php' );
                require_once( '../plugin-upgrade/lib/yit-plugin-licence.php' );
            }
            YIT_Plugin_Licence()->register( YITH_SL_INIT, YITH_SL_SECRET_KEY, YITH_SL_SLUG );
        }

        /**
         * Register plugins for update tab
         *
         * @return   void
         * @since    2.0.0
         * @author   Alessio Torrisi <alessio.torrisi@yithemes.com>
         */
        public function register_plugin_for_updates() {
            if( ! class_exists( 'YIT_Upgrade' ) ) {
                require_once( '../plugin-fw/lib/yit-upgrade.php' );
            }
            YIT_Upgrade()->register( YITH_SL_SLUG, YITH_SL_INIT );
        }

        /**
         * Show filter taxonomies under Store Locators panel
         * @param $tabs
         * @param $taxonomy
         * @return array
         */
        public function panel_tabs_in_taxonomies($tabs, $taxonomy){
            if (strpos($taxonomy, 'yisl_') === 0){
                // tab=stores&sub_tab=stores-filters
                $tabs = array('tab' => 'stores', 'sub_tab' => 'stores-filters');
            }
            return $tabs;
        }


        /**
         * Create page with inside the shortcode [yith_store_locator]
         */
        public function create_page(){
            global $wpdb;

            $option_value = get_option( 'yith-sl-page-id' );

            if ( $option_value > 0 && get_post( $option_value ) )
                return;

            $page_found = $wpdb->get_var( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_name` = 'yith-store-locator' LIMIT 1;" );
            if ( $page_found ) :
                if ( ! $option_value )
                    update_option( 'yith-sl-page-id', $page_found );
                return;
            endif;

            $page_data = array(
                'post_status' 		=> 'publish',
                'post_type' 		=> 'page',
                'post_author' 		=> 1,
                'post_name' 		=> 'yith-store-locator',
                'post_title' 		=> __( 'Store Locator', 'yith-store-locator' ),
                'post_content' 		=> '<!-- wp:shortcode -->[yith_store_locator]<!-- /wp:shortcode -->',
                'post_parent' 		=> 0,
                'comment_status' 	=> 'closed'
            );
            $page_id = wp_insert_post( $page_data );

            update_option( 'yith-sl-page-id', $page_id );
        }


    }
}