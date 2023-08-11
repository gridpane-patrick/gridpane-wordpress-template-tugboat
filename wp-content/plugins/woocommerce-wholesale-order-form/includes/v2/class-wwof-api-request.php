<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require __DIR__ . '/../../vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

if ( !class_exists( 'WWOF_API_Request' ) ) {

    class WWOF_API_Request {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */
        
        private static $_instance;

        private $api_keys = array();

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        public function __construct() { 

            $this->api_keys = $this->wwof_get_wc_api_keys();

        }
        
        public static function instance( $dependencies = null ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
	     * WC API Keys.
	     *
         * @since 1.15
	     * @return array
	     */
        public function wwof_get_wc_api_keys() {
            
            return array(
                        'consumer_key'      => get_option( 'wwof_order_form_v2_consumer_key' ),
                        'consumer_secret'   => get_option( 'wwof_order_form_v2_consumer_secret' )
                    );

        }

        /**
	     * Get WWOF Settings.
	     *
         * @since 1.15
	     * @return array
	     */
        private function wwof_get_settings() {

            $api_keys = $this->api_keys;

            $woocommerce = new Client(
                site_url(), 
                $api_keys[ 'consumer_key' ],
                $api_keys[ 'consumer_secret' ],
                [
                    'version' => 'wc/v3',
                ]
            );

            try {

                $results = $woocommerce->get( 'settings/wwof_settings' );
                $settings = array();

                if( $results ) {
                    foreach( $results as $key => $result )
                        $settings[ $result->id ] = $result->value;
                }

                // Product Thumbnail Size
                $product_thumbnail_size = get_option( 'wwof_general_product_thumbnail_image_size' , false );
                
                if( $product_thumbnail_size != false ) {
                    
                    if( !empty( $product_thumbnail_size[ 'width' ] ) && !empty( $product_thumbnail_size[ 'height' ] ) )
                        $settings[ 'wwof_general_product_thumbnail_image_size' ] = $product_thumbnail_size;
                    else if( !empty( $product_thumbnail_size[ 'width' ] ) )
                        $settings[ 'wwof_general_product_thumbnail_image_size' ] = array( 'width' => $product_thumbnail_size[ 'width' ] , 'height' => $product_thumbnail_size[ 'width' ] );
                    else if( !empty( $product_thumbnail_size[ 'height' ] ) )
                        $settings[ 'wwof_general_product_thumbnail_image_size' ] = array( 'width' => $product_thumbnail_size[ 'height' ] , 'height' => $product_thumbnail_size[ 'height' ] );
                    else
                        $settings[ 'wwof_general_product_thumbnail_image_size' ] = array( 'width' => '48' , 'height' => '48' );

                } else $settings[ 'wwof_general_product_thumbnail_image_size' ] = array( 'width' => '48' , 'height' => '48' );

                if( $settings[ 'wwof_general_sort_by' ] ) {
                    switch( $settings[ 'wwof_general_sort_by' ] ) {
                        case 'default':
                            $settings[ 'wwof_general_sort_by' ] = 'date';
                        // case 'menu_order':
                        case 'name':
                            $settings[ 'wwof_general_sort_by' ] = 'title';
                        // case 'date':
                        // case 'sku':
                        default: break;
                    }
                }
                
                if( isset( $settings[ 'wwof_order_form_v2_consumer_key' ] ) || isset( $settings[ 'wwof_order_form_v2_consumer_secret' ] ) ) {
                    unset( $settings[ 'wwof_order_form_v2_consumer_key' ] );
                    unset( $settings[ 'wwof_order_form_v2_consumer_secret' ] );
                }

                return array(
                    'status' => 'success',
                    'settings' => $settings
                );
                
            } catch ( HttpClientException $e ) {

                return array(
                    'status'    => 'error',
                    'message'   => $e->getMessage()
                );

            }
                
        }

        /**
	     * Check if user is a wholesale customer
	     *
         * @since 1.15
	     * @return bool
	     */
        public function is_wholesale_customer() {

            if( is_plugin_active( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' ) ) {
                
                global $wc_wholesale_prices;
                $wholesale_role = $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole();
                
                return isset( $wholesale_role[ 0 ] ) ? $wholesale_role[ 0 ] : '';

            }

            return false;

        }

        /**
	     * Get products. If user is wholesale customer then use wwpp api else use custom wwof api endpoint.
	     *
         * @since 1.15
	     * @return array
	     */
        public function get_products() {

            $wholesale_role = $this->is_wholesale_customer();

            // WWPP Compat will be next phase
            if( false && !empty( $wholesale_role ) )
                $this->get_wholesale_products( $wholesale_role );
            else
                $this->get_regular_products();

        }

        /**
	     * Get regular products using WWOF API custom endpoint.
	     *
         * @since 1.15
	     * @return array
	     */
        public function get_regular_products() {
            
            $api_keys       = $this->api_keys;
            $settings       = $this->wwof_get_settings();
            // sleep(1); // add delay, sometimes api not yet returned anything

            if( isset( $settings[ 'settings' ] ) ) {

                $wwof_settings = $settings[ 'settings' ];

                $woocommerce = new Client(
                    site_url(), 
                    $api_keys[ 'consumer_key' ],
                    $api_keys[ 'consumer_secret' ],
                    [
                        'version' => 'wc/v3',
                    ]
                );

                try {

                    $sort_order = 'desc';
                    if( !empty( $_POST[ 'sort_order' ] ) )
                        $sort_order = $_POST[ 'sort_order' ];
                    else if( !empty( $wwof_settings[ 'wwof_general_sort_order' ] ) )
                        $sort_order = $wwof_settings[ 'wwof_general_sort_order' ];
                        
                    $args = array(
                        'per_page'      => !empty( $settings[ 'settings' ][ 'wwof_general_products_per_page' ] ) ? $settings[ 'settings' ][ 'wwof_general_products_per_page' ] : 12,
                        'search'        => isset( $_POST[ 'search' ] ) ? $_POST[ 'search' ] : '',
                        'category'      => isset( $_POST[ 'category' ] ) && $_POST[ 'category' ] != 0 ? $_POST[ 'category' ] : '',
                        'page'          => isset( $_POST[ 'page' ] ) ? $_POST[ 'page' ] : 1,
                        'order'         => $sort_order,
                        'orderby'       => 'date',
                        'categories'    => $_POST[ 'categories' ]
                    );

                    if( !empty( $_POST[ 'products' ] ) ) {
                        if( !empty( $args[ 'include' ] ) )
                            $args[ 'include' ] = array_merge( $args[ 'include' ] , $_POST[ 'products' ] );
                        else
                            $args[ 'include' ] = explode( ',', $_POST[ 'products' ] );
                    }
                    
                    if( get_option( 'wwof_filters_product_category_filter' ) ) {
                        $products_to_include = $this->include_products_from_category();
                        if( !empty( $args[ 'include' ] ) )
                            $args[ 'include' ] = array_merge( $args[ 'include' ] , $products_to_include );
                        else
                            $args[ 'include' ] = $products_to_include;
                    } else if( get_option( 'wwof_filters_exclude_product_filter' ) )
                        $args[ 'exclude' ] = get_option( 'wwof_filters_exclude_product_filter' );

                    if( $_POST[ 'searching' ] === 'no' && get_option( 'wwof_general_default_product_category_search_filter' ) ) {
                        $category = get_term_by( 'slug', get_option( 'wwof_general_default_product_category_search_filter' ) , 'product_cat' );
                        if( $category && filter_var( $_POST[ 'show_all' ], FILTER_VALIDATE_BOOLEAN ) !== true )
                            $args[ 'category' ] = $category->term_id;
                    }
                    
                    $results = $woocommerce->get( 'wwof/products' , $args );

                    $response       = $woocommerce->http->getResponse();
                    $headers        = $response->getHeaders();
                    $tota_pages     = $headers[ 'X-WP-TotalPages' ];
                    $total_products = $headers[ 'X-WP-Total' ];

                    if( $wwof_settings[ 'wwof_general_list_product_variation_individually' ] !== 'yes' )
                        $variations = $this->get_variations( $results, true );
                    else
                        $variations = array();
                        
                    global $wc_wholesale_order_form;

                    wp_send_json(
                        array(
                            'status'            => 'success',
                            'products'          => $results,
                            'variations'        => $variations,
                            'settings'          => ( $settings[ 'status' ] == 'success' ) ? $settings[ 'settings' ] : $settings[ 'message' ],
                            'total_page'        => $tota_pages,
                            'total_products'    => $total_products,
                            'cart_subtotal'     => $wc_wholesale_order_form->_wwof_product_listings->wwof_get_cart_subtotal(),
                            'cart_url'          => wc_get_cart_url()
                        )
                    );
                    
                } catch ( HttpClientException $e ) {

                    wp_send_json(
                        array(
                            'status'    => 'error',
                            'message'   => $e->getMessage() // error
                        )
                    );

                }

            } else wp_send_json( $settings ); // error

        }

        /**
	     * Get wholesale products using WWPP API custom endpoint.
         * Note: not yet used will use this in the next phase.
	     *
         * @since 1.15
	     * @return array
	     */
        public function get_wholesale_products( $wholesale_role ) {
            
            $api_keys       = $this->api_keys;
            $settings       = $this->wwof_get_settings();

            $woocommerce = new Client(
                site_url(), 
                $api_keys[ 'consumer_key' ],
                $api_keys[ 'consumer_secret' ],
                [
                    'version' => 'wc/v3',
                ]
            );
            
            try {

                $args = array(
                    'wholesale_role'    => $wholesale_role,
                    'per_page'          => isset( $settings[ 'settings' ][ 'wwof_general_products_per_page' ] ) ? $settings[ 'settings' ][ 'wwof_general_products_per_page' ] : 12,
                    'search'            => isset( $_POST[ 'search' ] ) ? $_POST[ 'search' ] : '',
                    'category'          => isset( $_POST[ 'category' ] ) && $_POST[ 'category' ] != 0 ? $_POST[ 'category' ] : '',
                    'page'              => isset( $_POST[ 'page' ] ) ? $_POST[ 'page' ] : 1
                );

                $results = $woocommerce->get( 'wholesale/products' , $args );
                
                $response       = $woocommerce->http->getResponse();
                $headers        = $response->getHeaders();
                $tota_pages     = $headers[ 'X-WP-TotalPages' ];
                $total_products = $headers[ 'X-WP-Total' ];
                $variations     = $this->get_variations( $results );

                wp_send_json( array(
                        'status'            => 'success',
                        'products'          => $results,
                        'variations'        => $variations,
                        'settings'          => ( $settings[ 'status' ] == 'success' ) ? $settings[ 'settings' ] : $settings[ 'message' ],
                        'total_page'        => $tota_pages,
                        'total_products'    => $total_products
                    ) );
                
            } catch ( HttpClientException $e ) {

                wp_send_json( array(
                        'status'    => 'error',
                        'message'   => $e
                    ) );

            }

        }

        /**
	     * Get categories via WC API.
	     *
         * @since 1.15
	     * @return array
	     */
        public function get_categories() {
            
            $api_keys = $this->api_keys;
            
            $woocommerce = new Client(
                site_url(), 
                $api_keys[ 'consumer_key' ],
                $api_keys[ 'consumer_secret' ],
                [
                    'version' => 'wc/v3',
                ]
            );
            
            try {

                $args = array( 
                    'hide_empty'    => true,
                    'per_page'      => 100
                );

                // WWOF Product Category Filter Option
                $categories = get_option( 'wwof_filters_product_category_filter' );
                $cat_ids    = array();
                if( $categories ) {
                    foreach( $categories as $slug ) {
                        $category = get_term_by( 'slug', $slug, 'product_cat' );
                        if( $category )
                            $cat_ids[] = $category->term_id;
                    }
                    if( $cat_ids )
                        $args[ 'include' ] = $cat_ids;
                    
                }

                // WWOF Product Categories Shortcode Attribute
                if( !empty( $_POST[ 'categories' ] ) ) {
                    if( !empty( $args[ 'include' ] ) )
                        $args[ 'include' ] = array_merge( $args[ 'include' ] , explode( ',', $_POST[ 'categories' ] ) );
                    else
                        $args[ 'include' ] = explode( ',', $_POST[ 'categories' ] );
                }
                
                $results = $woocommerce->get( 'products/categories' , $args );
                
                $category_hierarchy = array();
                $this->assign_category_children( $results , $category_hierarchy );
                
                wp_send_json(
                    array(
                        'status'        => 'success',
                        'categories'    => $category_hierarchy
                    )
                );
                
            } catch ( HttpClientException $e ) {

                wp_send_json(
                    array(
                        'status'        => 'error',
                        'message'    => $e->getMessage()
                    )
                );

            }

        }

        /**
	     * Group category children ito their own parents.
	     *
         * @since 1.15.2
         * @param array $cats       List of categories
         * @param array $into       New sorted children
         * @param array $parent_id  The parent ID. 0 is for grand parent.
	     * @return array
	     */
        public function assign_category_children( Array &$cats, Array &$into, $parent_id = 0 ) {

            foreach ( $cats as $i => $cat ) {

                if ( $cat->parent == $parent_id ) {

                    $into[] = $cat;
                    unset( $cats[ $i ] );

                }

            }
        
            foreach ( $into as $top_cat ) {

                $top_cat->children = array();
                $this->assign_category_children( $cats , $top_cat->children , $top_cat->id );

            }
            
        }

        /**
	     * Get product variations via WC API endpoint.
	     *
         * @since 1.15
         * @param array $products
	     * @return array
	     */
        public function get_variations( $products , $get_all_variations = false ) {

            $variations = array();
            $api_keys = $this->api_keys;
            
            $woocommerce = new Client(
                site_url(), 
                $api_keys[ 'consumer_key' ],
                $api_keys[ 'consumer_secret' ],
                [
                    'version' => 'wc/v3',
                ]
            );

            if( $get_all_variations === true && $products ) {

                // Fetch all variations per variable product
                foreach( $products as $product ) {
                    
                    if( $product->type === 'variable' ) {
                        
                        try {

                            $results = $woocommerce->get( 'products/' . $product->id . '/variations' );

                            if( $results ) {
                                
                                foreach( $results as $index => $variation )
                                    $results[ $index ]->price = wc_price( $variation->price );
                                
                                $variations[ $product->id ] = $results;

                            }
                                
                            
                        } catch ( HttpClientException $e ) {
            
                            error_log(print_r($e,true));
            
                        }

                    }
                    
                }

            } else {

                // Lazy loading on scroll in variation dropdown
                
                try {
                        
                    $args = array(
                        'status'    => 'publish',
                        'page'      => $_POST[ 'page' ],
                        'search' => isset( $_POST[ 'search' ] ) ? $_POST[ 'search' ] : ''
                    );

                    $results = $woocommerce->get( 'products/' . $_POST[ 'product_id' ] . '/variations' , $args );
                    
                    $response           = $woocommerce->http->getResponse();
                    $headers            = $response->getHeaders();
                    $tota_pages         = $headers[ 'X-WP-TotalPages' ];
                    $total_variations   = $headers[ 'X-WP-Total' ];

                    if( $results ) {
                        
                        foreach( $results as $index => $variation )
                            $results[ $index ]->price = wc_price( $variation->price );
                        
                        $variations[ $_POST[ 'product_id' ] ] = $results;

                    }
                    
                } catch ( HttpClientException $e ) {
    
                    error_log(print_r($e,true));
    
                }

            }

            if ( $get_all_variations === false && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                wp_send_json(
                    array(
                        'status'            => 'success',
                        'variations'        => $variations,
                        'total_pages'       => $tota_pages,
                        'total_variations'  => $total_variations
                    )
                );

            } else return $variations;
            
        }

        /**
	     * Product Category Filter and Exclude Product Filter option.
	     *
         * @since 1.15
	     * @return array
	     */
        public function include_products_from_category() {

            $categories = get_option( 'wwof_filters_product_category_filter' );
            $args = array(
                'category'  => $categories,
                'return'    => 'ids',
                'paginate' => false,
                'exclude'   => get_option( 'wwof_filters_exclude_product_filter' )
            );

            $products = wc_get_products( $args );
            
            return $products;
        }

        /**
	     * When developing via cra development server, allow ajax cross origin.
	     *
         * @since 1.15
	     */
        public function add_cors_http_header() {

            // If developing under cra dev server
            if( isset( $_SERVER[ 'HTTP_ORIGIN' ] ) && $_SERVER[ 'HTTP_ORIGIN' ] === 'http://localhost:3000' )
                header("Access-Control-Allow-Origin: *");

        }
        
        /**
         * Execute model.
         *
         * @since 1.15
         * @access public
         */
        public function run() {

            add_action( 'init' , array( $this, 'add_cors_http_header' ) );
            
            add_action( 'wp_ajax_nopriv_wwof_api_get_products'      , array( $this , 'get_products' ) );
            add_action( 'wp_ajax_wwof_api_get_products'             , array( $this , 'get_products' ) );

            add_action( 'wp_ajax_nopriv_wwof_api_get_categories'    , array( $this , 'get_categories' ) );
            add_action( 'wp_ajax_wwof_api_get_categories'           , array( $this , 'get_categories' ) );

            add_action( 'wp_ajax_nopriv_wwof_api_get_variations'    , array( $this , 'get_variations' ) );
            add_action( 'wp_ajax_wwof_api_get_variations'           , array( $this , 'get_variations' ) );
            
        }
        
    }

}
