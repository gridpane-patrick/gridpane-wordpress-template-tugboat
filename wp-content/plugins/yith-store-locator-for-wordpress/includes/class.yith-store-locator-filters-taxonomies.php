<?php
/**
 * Main class
 *
 * @author YITH
 * @package YITH Store Locator
 * @version 1.0.0
 */

defined( 'YITH_SL' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'YITH_Store_Locator_Filters_Taxonomies' ) ) {
    /**
     * YITH_Store_Locator_Filters_Taxonomies
     *
     * @since 1.0.0
     */
    final class YITH_Store_Locator_Filters_Taxonomies {

        /**
         * Single instance of the class
         *
         * @var YITH_Store_Locator_Filters_Taxonomies
         * @since 1.0.0
         */
        private static $instance;

        /**
         * @var YITH_Store_Locator_Filters_Taxonomies types
         */
        private $types;

        /**
         * Ajax method used to add a filter
         * @var string
         */
        public $ajax_add_filter = 'add_new_filter';

        /**
         * Ajax method used to delete a filter
         * @var string
         */
        public $ajax_delete_filter = 'delete_filter';

        /**
         * Ajax method tu update order filters with drag and drop
         * @var string
         */
        public $ajax_update_order_filters = 'update_order_filters';

        /**
         * Ajax method to update default value for a taxonomy
         * @var string
         */
        public $ajax_update_default_value = 'update_default_value';

        /**
         * Default slug for filter radius
         * @var string
         */
        private $filter_radius_slug = 'radius';

        /**
         * List of all filter taxonomies
         * @var
         */
        private $filters;

        /**
         * Filter taxonomies prefix
         * @var string
         */
        private $prefix = 'yisl_';



        /**
         * Returns single instance of the class
         *
         * @return YITH_Store_Locator_Filters_Taxonomies
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

            $this->create_table();

            $this->create_filter_radius();

            $this->set_props();

            add_action( 'yith_sl_output_filters_page', [ $this, 'output_filters_page' ] );

            add_action( 'wp_ajax_yith_sl_' . $this->ajax_add_filter, [ $this,'add_new_filter' ] );
            add_action( 'wp_ajax_nopriv_yith_sl_' . $this->ajax_add_filter, [ $this,'add_new_filter' ] );

            add_action( 'wp_ajax_yith_sl_' . $this->ajax_delete_filter, [ $this,'delete_filter' ] );
            add_action( 'wp_ajax_nopriv_yith_sl_' . $this->ajax_delete_filter, [ $this,'delete_filter' ] );

            add_action( 'wp_ajax_yith_sl_' . $this->ajax_update_order_filters, [ $this,'update_order_filters' ] );
            add_action( 'wp_ajax_nopriv_yith_sl_' . $this->ajax_update_order_filters, [ $this,'update_order_filters' ] );

            add_action( 'wp_ajax_yith_sl_' . $this->ajax_update_default_value, [ $this,'update_default_value' ] );
            add_action( 'wp_ajax_nopriv_yith_sl_' . $this->ajax_update_default_value, [ $this,'update_default_value' ] );

            add_action( 'init', [ $this, 'edit_taxonomy_filter' ] );

            add_action( 'init', [ $this, 'register_taxonomies' ] );

            /* Create default step for radius filter */
            add_action( 'init', [ $this, 'create_default_radius_terms' ], 50 );

            /* Order by numeric filter radius steps */
            add_filter( 'get_terms', [ $this, 'order_radius_terms' ], 10, 3 );

            /* Add Default column to Radius taxonomy page */
            add_filter( 'manage_edit-'. $this->get_radius_filter_taxonomy_slug() .'_columns', [ $this, 'add_custom_column_to_radius_taxonomy_page' ] );
            add_filter( 'manage_'. $this->get_radius_filter_taxonomy_slug() .'_custom_column', [ $this, 'manage_radius_taxonomy_columns' ], 10, 3);

            /* Hide radius taxonomy in post type page */
             add_action( 'admin_menu' , [ $this, 'remove_filter_radius_metaboxes' ], 100 );



        }


        public function remove_filter_radius_metaboxes(){
            remove_meta_box( $this->get_radius_filter_taxonomy_slug() . 'div', YITH_Store_Locator_Post_Type::$post_type_name, 'side' );
        }

        /**
         * Create Filters table
         */
        private function create_table(){

            if ( get_option( 'yith_sl_filters_table_created' ) && ! isset( $_GET['yith_sl_force_create_filters_table'] ) ) {
                return;
            }

            /**
             * Check if dbDelta() exists
             */
            if ( ! function_exists( 'dbDelta' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            }

            global $wpdb;

            $charset_collate = $wpdb->get_charset_collate();

            $create = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . YITH_SL_FILTERS_TABLE ." (
                          filter_id BIGINT UNSIGNED NOT NULL auto_increment,
                          filter_slug varchar(200) NOT NULL,
                          filter_label varchar(200) NULL,
                          filter_type varchar(20) NOT NULL,
                          filter_icon varchar(200) NOT NULL,
                          filter_show_label varchar(20) NOT NULL,
                          filter_order int(20) NOT NULL,
                          filter_orderby varchar(20) NOT NULL,
                          filter_public int(1) NOT NULL DEFAULT 1,
                          PRIMARY KEY  (filter_id),
                          KEY filter_slug (filter_slug(20))
                        ) $charset_collate;";
            dbDelta( $create );

            add_option( 'yith_sl_db_version', YITH_SL_DB_VERSION );
            add_option( 'yith_sl_filters_table_created', true );
        }


        /**
         * Create filter radius by default
         */
        private function create_filter_radius(){

            if ( get_option( 'yith_sl_filter_radius_created' ) && ! isset( $_GET['yith_sl_force_create_filter_radius'] ) ) {
                return;
            }

            global $wpdb;

            $filter_order = $wpdb->get_var( "SELECT MAX(filter_order) FROM " . $wpdb->prefix . YITH_SL_FILTERS_TABLE );

            $data = array(
                'filter_label'      =>  esc_html__( 'Radius', 'yith-store-locator' ),
                'filter_slug'       =>  $this->filter_radius_slug,
                'filter_type'       =>  'dropdown',
                'filter_icon'       =>  '',
                'filter_show_label' =>  1,
                'filter_order'      =>  $filter_order + 1
            );

            $wpdb->insert( $wpdb->prefix . YITH_SL_FILTERS_TABLE, $data );

            update_option( 'yith_sl_filter_radius_created', 'yes'  );

        }



        /**
         * Set props
         */
        private function set_props(){
            $this->types = array(
                'dropdown'    =>  esc_html__( 'Select','yith-store-locator' ),
                'checkbox'  =>  esc_html__( 'Checkbox', 'yith-store-locator' )
            );
            $this->filters = $this->get_filters();

        }

        /**
         * Get filter taxonomies prefix
         * @return string
         */
        public function get_prefix(){
            return $this->prefix;
        }

        /**
         * Get all stores filters
         * @return array|null|object
         */
        public function get_filters(){

            if( is_null( $this->filters ) ){
                global $wpdb;
                $query = "SELECT * FROM ". $wpdb->prefix . YITH_SL_FILTERS_TABLE . " ORDER BY filter_order ASC";
                $rows = $wpdb->get_results( $query );
                foreach ( $rows as $row ){
                    $slug = $row->filter_slug;
                    $this->filters[$slug] = array(
                        'id'            =>  $row->filter_id,
                        'label'         =>  $row->filter_label,
                        'type'          =>  $row->filter_type,
                        'icon'          =>  $row->filter_icon,
                        'show_label'    =>  $row->filter_show_label
                    );
                }

            }
            return $this->filters;
        }

        /**
         * Get Filter types
         * @return mixed
         */
        public function get_types(){
            return apply_filters( 'yith_sl_filters_types', $this->types );
        }


        /**
         * Show HTML for Stores Filters page
         */
        public function output_filters_page(){

            if( isset( $_GET['edit-taxonomy'] ) ){
                $template = 'edit.php';
                $args = array(
                    'types'     =>  $this->get_types(),
                    'filters_layout_general'    =>  yith_sl_get_option( "filters-display-mode", "opened" )
                );

            }else{
                $template = 'filters.php';
                $args = array(
                    'types'                     =>  $this->get_types(),
                    'filters_layout_general'    =>  yith_sl_get_option( "filters-display-mode", "opened" )
                );
            }

            yith_sl_get_template( $template, 'admin/filters/', $args );

        }

        /**
         * Prepare slug to insert correctly in the database. Prevent usage of the same slug and avoid too long slug
         * @param $slug
         * @return string
         */
        public function prepare_slug_for_db( $slug, $context = 'new' ){
            $slug = substr( $slug, 0, 20 );
            if( array_key_exists( $slug, $this->filters ) && $context === 'new' ){
                $slug .= '-2';
            }
            return sanitize_title_with_dashes( $slug, '', 'save' );
        }

        /**
         * Add new filter in ajax
         */
        public function add_new_filter(){

            if( !! $_POST['form'] ) {
                parse_str( $_POST['form'], $form );
            };
            if ( !!$form && !! $form['yith_sl_add_new_filter']  || wp_verify_nonce( $form['yith_sl_add_new_filter'] ) ){

                global $wpdb;

                $label  =   !! $form[ 'filter_label' ] ? $form[ 'filter_label' ] : 'Filter ' . rand();
                $slug   =   !! $form[ 'filter_slug' ] ? $this->prepare_slug_for_db( $form[ 'filter_slug' ] ) : $this->prepare_slug_for_db( $label );
                $type   =   !! $form[ 'filter_type' ] ? $form[ 'filter_type' ] : 'dropdown';
                $icon   =   !! $form[ 'filter_icon' ] ? $form[ 'filter_icon' ] : '';
                $show_label  =  isset( $form[ 'filter_show_label' ] ) ? $form[ 'filter_show_label' ] : '0';

                $filter_order = $wpdb->get_var( "SELECT MAX(filter_order) FROM " . $wpdb->prefix . YITH_SL_FILTERS_TABLE );

                $data = array(
                    'filter_label'      =>  $label,
                    'filter_slug'       =>  $slug,
                    'filter_type'       =>  $type,
                    'filter_icon'       =>  $icon,
                    'filter_show_label' =>  $show_label,
                    'filter_order'      =>  $filter_order + 1

                );

                $wpdb->insert( $wpdb->prefix . YITH_SL_FILTERS_TABLE, $data );
                $lastid = $wpdb->insert_id;

                if( $lastid > 0 ){
                    $notice = '<p class="notice notice-success"> '. esc_html__( 'New filter has been created correctly.', 'yith-store-locator' ) .' </p>  ';
                    $data['filter_id'] = $lastid;
                    ob_start();
                    yith_sl_get_template( 'filter-row.php', 'admin/filters/', $data );
                    $new_filter = ob_get_clean();
                }

                else{
                    $notice = '<p class="notice notice-error"> '. esc_html__( 'An error occurred while creating the filter. Please, try again.', 'yith-store-locator' ) .' </p>  ';
                }

            }else{
                $notice = '<p class="notice notice-error"> '. esc_html__( 'An error occurred while creating the filter. Please, try again.', 'yith-store-locator' ) .' </p>  ';
            }

            $json_result = array(
                'notice'    =>  $notice,
                'new_filter'   =>  isset( $new_filter ) ? $new_filter : null
            );

            wp_send_json( $json_result );

            echo $notice;
            die();

        }


        /**
         * Delete filter and associated terms from the database
         */
        public function delete_filter(){
            global $wpdb;
            $taxonomy_id = $_POST['filter_id'];
            $taxonomy_slug = $_POST['filter_slug'];
            $query_get_terms = $wpdb->prepare( "
                SELECT term_taxonomy_id
                FROM {$wpdb->term_taxonomy}
                WHERE taxonomy = %d
            ", $taxonomy_slug ) ;
            $terms = $wpdb->get_results( $query_get_terms );
            foreach ( $terms as $term ){
                wp_delete_term( $term->term_taxonomy_id, $taxonomy_slug );
            }

            $wpdb->delete( $wpdb->prefix . YITH_SL_FILTERS_TABLE, array( 'filter_id' => $taxonomy_id ) );
            $wpdb->delete( $wpdb->options, array( 'option_name' => $taxonomy_slug . '_children' ) );
        }

        /**
         * Edit taxonomy filter
         */
        public function edit_taxonomy_filter(){
            if( isset( $_POST['yith-sl-edit-filter'] ) ){
                global $wpdb;
                $label      =   !! $_POST['filter_label'] ? $_POST['filter_label'] : '';
                $icon       =   !! $_POST['filter_icon'] ? $_POST['filter_icon'] : '';
                $slug       =   isset( $_POST['filter_slug'] ) ? $this->prepare_slug_for_db( $_POST['filter_slug'], 'edit' ) : 'filter';
                $type       =   isset( $_POST['filter_type'] ) ? $_POST['filter_type'] : 'dropdown';
                $id         =   $_POST['yith-sl-edit-filter'];
                $show_label  =  isset( $_POST[ 'filter_show_label' ] ) ? $_POST[ 'filter_show_label' ] : '0';

                $query = "UPDATE " . $wpdb->prefix . YITH_SL_FILTERS_TABLE ." 
                          SET filter_slug = '{$slug}', filter_label = '{$label}', filter_icon = '{$icon}', filter_type = '{$type}', filter_show_label = '{$show_label}'
                          WHERE filter_id = {$id}  
                        ";
                
                $result = $wpdb->query( $query );

                if( $result !== false ) {
                    add_action('yith_sl_before_edit_filter_fields', [$this, 'show_success_notice']);
                }else {
                    add_action('yith_sl_before_edit_filter_fields', [$this, 'show_error_notice']);
                }
            }
        }

        /**
         * Register taxonomies dynamically
         */
        public function register_taxonomies(){
            if( is_array( $this->filters ) ){
                foreach ( $this->filters as $slug => $tax ){
                    $labels = array(
                        'name'                  => $tax['label'],
                        'singular_name'         => $tax['label'],
                        'menu_name'             => $tax['label'],
                        'name_admin_bar'        => $tax['label'],
                        'add_new'               => esc_html__( 'Add new', 'yith-store-locator' ),
                        'add_new_item'          => sprintf( esc_html__( 'Add new term for %s', 'yith-store-locator' ), $tax['label'] ),
                        'new_item'              => sprintf( esc_html__( 'New term for %s', 'yith-store-locator' ), $tax['label'] ),
                        'edit_item'             => sprintf( esc_html__( 'Edit new term for %s', 'yith-store-locator' ), $tax['label'] ),
                        'view_item'             => sprintf( esc_html__( 'View term for %s', 'yith-store-locator' ), $tax['label'] ),
                        'all_items'             => sprintf( esc_html__( 'All terms for  %s', 'yith-store-locator' ), $tax['label'] ),
                        'search_items'          => sprintf( esc_html__( 'Search term for %s', 'yith-store-locator' ), $tax['label'] ),
                        'parent_item_colon'     => sprintf( esc_html__( 'Parent term for %s', 'yith-store-locator' ), $tax['label'] ),
                        'not_found'             => sprintf( esc_html__( 'No term found for %s ', 'yith-store-locator' ), $tax['label'] ),
                        'not_found_in_trash'    => sprintf( esc_html__( 'Add term for %s found in trash', 'yith-store-locator' ), $tax['label'] ),
                        'archives'              => sprintf( esc_html__( 'Term for %s archived', 'yith-store-locator' ), $tax['label'] ),
                        'insert_into_item'      => sprintf( esc_html__( 'Insert into %s', 'yith-store-locator' ), $tax['label'] ),
                        'items_list'            => sprintf( esc_html__( 'Term for %s list', 'yith-store-locator' ), $tax['label'] ),
                    );
                    $args = array(
                        'hierarchical'      => true,
                        'labels'            => $labels,
                        'show_ui'           => true,
                        'show_admin_column' => true,
                        'query_var'         => true,
                    );
                    register_taxonomy( $this->prefix . $slug, YITH_Store_Locator_Post_Type::$post_type_name, $args );

                }
            }
        }

        /**
         * Create default values for filter radius
         */
        public function create_default_radius_terms(){
            $radius_terms_created = get_option( 'yith_sl_radius_terms_created', 'no' );
            if( $radius_terms_created === 'no' ){
                $terms = array( 5, 10, 20, 50, 100, 500 );
                foreach ( $terms as $term ){
                    wp_insert_term( $term,$this->prefix . $this->filter_radius_slug );
                }
                update_option( 'yith_sl_radius_terms_created', 'yes' );
                update_option( 'yith_sl_default_radius', 50 );
            }

        }

        /**
         * Show notice when an a filter is created/edited with success
         */
        public function show_success_notice(){
            $notice = '<p class="notice notice-success"> '. esc_html__( 'Filter has been edited correctly', 'yith-store-locator' ) .' </p>  ';
            echo $notice;
        }

        /**
         * Show notice when an error occurs creating/editing a filter
         */
        public function show_error_notice(){
            $notice = '<p class="notice notice-error"> '. esc_html__( 'An error occurred while editing the filter. Please, try again.', 'yith-store-locator' ) .' </p>  ';
            echo $notice;

        }

        /**
         * Update order filters in ajax
         */
        public function update_order_filters(){
            if( !! $_POST['filters'] ) {
                global $wpdb;
                foreach ( $_POST['filters'] as $order => $id ){
                    $query = $wpdb->prepare( "UPDATE " . $wpdb->prefix . YITH_SL_FILTERS_TABLE ." 
                          SET filter_order = %d 
                          WHERE filter_id = %d", $order, $id  );
                     $wpdb->query( $query );
                }
            }
            die();
        }

        /**
         * Update default value for taxonomy filter
         */
        public function update_default_value(){
            if( isset( $_POST['taxonomy'] ) ){
                $default_value = get_option( 'yith_sl_default_' . $_POST['taxonomy']);
                $new_value = $default_value === $_POST['value'] ? '' : $_POST['value'];
                update_option( 'yith_sl_default_' . $_POST['taxonomy'], $new_value  );
            }
            die();
        }

        /**
         * Return radius filter taxonomy slug
         * @return string
         */
        public function get_radius_filter_taxonomy_slug(){
            return $this->prefix . $this->filter_radius_slug;
        }

        /**Get filter radius slug
         * @return string
         */
        public function get_radius_filter_slug(){
            return $this->filter_radius_slug;
        }

        /** Order radius terms
         * @param $terms
         * @param $taxonomies
         * @param $args
         * @return mixed
         */
        public function order_radius_terms( $terms, $taxonomies, $args ){

            if( $args['taxonomy'][0] === $this->get_radius_filter_taxonomy_slug() ){
                usort( $terms, function($a, $b) {
                    if( isset( $a->name ) || isset( $b->name ) ){
                        $ai = filter_var($a->name, FILTER_SANITIZE_NUMBER_INT);
                        $bi = filter_var($b->name, FILTER_SANITIZE_NUMBER_INT);
                        if ($ai == $bi) {
                            return 0;
                        }
                        return ($ai < $bi) ? -1 : 1;
                    }
                });
            }
            return $terms;
        }

        /**
         * Get filter radius title
         * @return mixed
         */
        public function get_filter_radius_title(){
            return $this->filters[$this->filter_radius_slug]['label'];
        }

        /**
         * Add custom columns to radius taxonomy page
         * @param $columns
         * @return array
         */
        public function add_custom_column_to_radius_taxonomy_page( $columns ){
            $columns = array(
                'cb'            => '<input type="checkbox" />',
                'name'          =>  esc_html__('Name', 'yith-store-locator' ),
                'default_value' =>  esc_html__('Default', 'yith-store-locator' )
            );
            return $columns;
        }

        /**
         * Render Default column for radius taxonomy page
         * @param $out
         * @param $column_name
         * @param $term_id
         * @return string
         */
        public function manage_radius_taxonomy_columns( $out, $column_name, $term_id ) {
            $term = get_term($term_id, 'yisl_radius');
            $default_value = get_option( 'yith_sl_default_radius', 50 );
            switch ($column_name) {
                case 'default_value':
                    $out .= "
                            <span class='wrap-default-input'><input class='yith-sl-set-default' data-taxonomy='radius' data-value=". esc_attr( $term->name ) ." type='checkbox'". checked( $default_value, $term->name, false ) ."></span>
                            <span class='label'>". esc_html__( 'Set as default', 'yith-store-locator' ) ."</span>";
                    break;

                default:
                    break;
            }
            return $out;
        }



    }
}

/**
 * Unique access to instance of YITH_Store_Locator class
 *
 * @return YITH_Store_Locator_Filters_Taxonomies
 * @since 1.0.0
 */
function YITH_Store_Locator_Filters_Taxonomies(){
    return YITH_Store_Locator_Filters_Taxonomies::get_instance();
}
