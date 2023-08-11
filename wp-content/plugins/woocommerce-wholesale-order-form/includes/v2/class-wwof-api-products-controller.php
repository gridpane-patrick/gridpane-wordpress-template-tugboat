<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWOF_API_Products_Controller' ) ) {

    /**
     * Model that houses the logic of WWOF integration with WC Products API.
     *
     * @since 1.15
     */
    class WWOF_API_Products_Controller extends WC_REST_Products_Controller {

		/**
		 * WWOF Route base.
		 *
		 * @var string
		 */
        protected $rest_base = 'wwof/products';

        /**
         * WWOF_API_Products_Controller constructor.
         *
         * @since 1.15
         * @access public
         */
        public function __construct() {

			// Fires when preparing to serve an API request.
            add_action( "rest_api_init" , array( $this , "register_routes" ) );
            
            // Allow searching by sku
            $this->allow_search_by_sku();

        }

        /**
	     * API WP_Query args. Insert/update arguments.
	     *
         * @since 1.15
         * @param array $request
	     * @return array
	     */
        protected function prepare_objects_query( $request ) {
            
            $args = parent::prepare_objects_query( $request );

            // List product variation individually is enabled
            if( get_option( 'wwof_general_list_product_variation_individually' ) === 'yes' )
                $args[ 'post_type' ] = array( 'product', 'product_variation' );

            // Exclude not supported product types
            $args[ 'post__not_in' ] = array_merge( $args[ 'post__not_in' ] , $this->get_not_supported_types_product_ids() );
            
            // Sort results via the wwof option
            $this->sort_args( $args );

            // Short code 'categories' attribute
            $this->categories_attribute( $args );

            // Exclude 'hidden' products
            $this->exclude_hidden_products( $args );

            // Searching
            $this->searching( $args );

            return apply_filters( 'wwof_api_products_args' , $args );

        }

        /**
	     * Unsupported product types. Grouped, External and Variable if list product variation individually is enabled.
	     *
         * @since 1.15
	     * @return array
	     */
        public function get_not_supported_types_product_ids() {
            
            global $wpdb;

            $types = array( "grouped", "external" );

            if( get_option( 'wwof_general_list_product_variation_individually' ) === 'yes' )
                array_push( $types, "variable" );
                
            $types = apply_filters( 'wwof_unsupported_product_types' , $types );

            $unsupported_types = sprintf( "'%s'" , implode( "','" , $types ) );
            $q = "SELECT term_taxonomy_id FROM $wpdb->terms t, $wpdb->term_taxonomy tt WHERE t.term_id = tt.term_id AND name IN ( $unsupported_types )";
            
            $unsupported_term_taxonomy_id       = $wpdb->get_results( $q , ARRAY_A );
            $unsupported_term_taxonomy_id_array = array();

            if( $unsupported_term_taxonomy_id ){
                foreach( $unsupported_term_taxonomy_id as $term )
                    $unsupported_term_taxonomy_id_array[] = $term[ 'term_taxonomy_id' ];
            }
            
            $unsupported_product_ids = array();

            $q2 = "
                    SELECT DISTINCT post_meta_table1.object_id
                    FROM $wpdb->term_relationships post_meta_table1
                    WHERE post_meta_table1.term_taxonomy_id IN (" . implode( ',' , $unsupported_term_taxonomy_id_array ) . ")
                    ";

            $unsupported_products = $wpdb->get_results( $q2 , ARRAY_A );

            if( $unsupported_products ) {
                foreach( $unsupported_products as $product )
                    $unsupported_product_ids[] = (int) $product[ 'object_id' ];
            }
            
            return apply_filters( 'wwof_unsupported_product_types_product_ids' , $unsupported_product_ids );

        }

        /**
	     * Exclude hidden products.
	     *
         * @since 1.15.2
         * @param array     $args   WP_Query args. Passed by reference.
	     */
        public function exclude_hidden_products( &$args ) {

            $args[ 'tax_query' ][] = array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'exclude-from-catalog',
                'operator' => 'NOT IN'
            );

        }

        /**
	     * Sort results via WWOF Setting.
	     *
         * @since 1.15.2
         * @param array     $args   WP_Query args. Passed by reference.
	     */
        public function sort_args( &$args ) {

            $sort_by = get_option( 'wwof_general_sort_by' );

            if( $sort_by ) {

                switch( $sort_by ) {
                    case 'name':
                        $args[ 'orderby' ] = 'title';
                        break;
                    case 'date':
                        $args[ 'orderby' ] = 'date';
                        break;
                    case 'sku':
                        $args[ 'orderby' ] = 'meta_value';
                        if( empty( $_GET[ 'search' ] ) ) {
                            $args[ 'meta_query' ] = array(
                                'relation' => 'AND',
                                array(
                                    'relation' => 'OR',
                                    array(
                                        'key'     => '_sku',
                                        'compare' => 'NOT EXISTS',
                                    ),
                                    array(
                                        'key'     => '_sku',
                                        'compare' => 'EXISTS',
                                    )
                                ),
                            );
                        }
                        break;
                    case 'menu_order':
                        $args[ 'orderby' ] = 'menu_order title';
                        break;
                    default:
                        $args[ 'orderby' ] = 'title';
                }

            }
            
        }

        /**
	     * List products under the categories specified in 'categories' attribute in the shortcode
	     *
         * @since 1.15.2
         * @param array     $args   WP_Query args. Passed by reference.
	     */
        public function categories_attribute( &$args ) {

            if ( ! empty( $_GET[ 'categories' ] ) ) {
                $args[ 'tax_query' ][] = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => explode( ',', $_GET[ 'categories' ] ),
                    'operator' => 'IN'
                );
            }

        }

        /**
	     * When search is triggered.
	     *
         * @since 1.15.2
	     * @param array     $args   WP_Query args. Passed by reference.
	     */
        public function searching( &$args ) {
            
            if( get_option( 'wwof_general_allow_product_sku_search' ) === 'yes' && !empty( $args[ 's' ] ) ) {

                if( get_option( 'wwof_general_display_zero_products' ) == 'yes' ) {

                    $args[ 'meta_query' ] = $this->add_meta_query( // WPCS: slow query ok.
                        $args, array(
                            'key'     => '_stock_status',
                            'value'   => array( 'instock' , 'outofstock' ),
                            'compare' => 'IN'
                        )
                    );

                } else {

                    $args[ 'meta_query' ] = $this->add_meta_query( // WPCS: slow query ok.
                        $args, array(
                            'key'     => '_stock_status',
                            'value'   => 'instock'
                        )
                    );
                    
                }
               
            } else {

                if( get_option( 'wwof_general_display_zero_products' ) == 'yes' ) {

                    $args[ 'meta_query' ] = $this->add_meta_query( // WPCS: slow query ok.
                        $args, array(
                                'key'     => '_stock_status',
                                'value'   => array( 'instock' , 'outofstock' ),
                                'compare' => 'IN'
                            )
                        );

                } else {

                    $args[ 'meta_query' ] = $this->add_meta_query( // WPCS: slow query ok.
                        $args, array(
                                'key'     => '_stock_status',
                                'value'   => 'instock'
                            )
                        );

                }
                
            }
            
        }

        /**
	     * Allow search by sku. Append additional meta query to the search logic of wp search.
	     * REF: https://adambalee.com/search-wordpress-by-custom-fields-without-a-plugin/
         * 
         * @since 1.15.2
	     */
        public function allow_search_by_sku() {

            if( get_option( 'wwof_general_allow_product_sku_search' ) === 'yes' ) {

                add_filter( 'posts_join', function( $join ) {

                    global $wpdb;
                
                    if ( isset( $_GET[ 'search' ] ) )
                        $join .=' LEFT JOIN '.$wpdb->postmeta. ' as wwof_sku ON '. $wpdb->posts . '.ID = wwof_sku.post_id ';

                    return $join;

                } );
                
                add_filter( 'posts_where', function( $where ) {

                    global $pagenow, $wpdb;
                    
                    if ( isset( $_GET[ 'search' ] ) ) {

                        $where = preg_replace(
                            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                            "(".$wpdb->posts.".post_title LIKE $1) OR (wwof_sku.meta_key = '_sku' AND wwof_sku.meta_value LIKE $1)", $where );
                    
                    }
                    
                    return $where;

                } );

            }

        }

    }

    return new WWOF_API_Products_Controller();

}