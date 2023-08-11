<?php

/**
 * Get option function
 */
if ( ! function_exists( 'yith_sl_get_option' ) ) {

    /**
     * Get plugin option
     *
     * @param   $option  string
     * @param   $default mixed
     *
     * @return  mixed
     * @since   1.0.0
     *
     * @author  Alessio Torrisi
     */
    function yith_sl_get_option( $option, $default = false ) {
        return YITH_Store_Locator_Settings::get_instance()->get_option( 'store-locator', $option, $default );
    }

}


/**
 * Get default value option
 */
if ( ! function_exists( 'yith_sl_get_default' ) ) {

    /**
     * Get options defaults
     *
     * @param $param string
     *
     * @return  mixed
     * @since   1.1.0
     *
     * @author  Alessio Torrisi
     */
    function yith_sl_get_default( $param ) {

        $defaults = array(
            'title-address-section'     =>  esc_html__( 'Address','yith-store-locator' ),
            'contact-address-section'   =>  esc_html__( 'Contact','yith-store-locator' )
        );

        $defaults = apply_filters( 'yith_sl_default_options', $defaults );

        if ( isset( $defaults[ $param ] ) ) {
            return $defaults[ $param ];
        }

        return false;

    }

}

if( !function_exists('yith_sl_get_template') ){

    /**
     * @param $template_name
     * @param $template_path
     * @return string
     */
    function yith_sl_get_template( $template_name, $template_path, $args = array(), $include = true ){

        if ( file_exists( YITH_SL_THEME_PATH . $template_path . $template_name ) ) {

            $template = YITH_SL_THEME_PATH . $template_path . $template_name;

        }elseif ( file_exists( YITH_SL_TEMPLATE_PATH . $template_path . $template_name ) ) {

            $template = YITH_SL_TEMPLATE_PATH . $template_path . $template_name;

        }
        if( $include ){
            return include( $template );
        }else{
            return $template;
        }

    }

}



if( !function_exists('yith_sl_get_terms') ){

    /**
     * Get all terms organized in hierarchical way
     * @param $taxonomy
     * @return array
     */
    function yith_sl_get_terms( $taxonomy ){
        $all_categories = array();
        $parent_categories = yith_sl_get_parent_terms( $taxonomy );
        foreach ( $parent_categories as $category ){
            $all_categories[] = array(
                'parent'    =>  $category,
                'children'  =>  get_terms( $taxonomy, array(
                    'hide_empty' => false,
                    'parent'     => $category->term_id
                ) )
            );
        }
        return $all_categories;
    }

}

if( !function_exists( 'yith_sl_get_parent_terms' ) ){

    /**
     * Get parent terms for taxonomy
     * @param $taxonomy
     * @return array|int|WP_Error
     */
    function yith_sl_get_parent_terms( $taxonomy ){
        $parent_terms = get_terms( $taxonomy, array(
            'hide_empty' => false,
            'parent'     => 0
        ) );
        return $parent_terms;
    }

}


/**
 * Get stores
 * @param null $args
 * @return mixed
 */
function yith_sl_get_stores( $args = null ) {

    $filters        = isset( $args[ 'filters' ] ) ? $args[ 'filters' ] : array();

    $latitude        = isset( $args[ 'latitude' ] ) ? $args[ 'latitude' ] : null;

    $longitude       = isset( $args[ 'latitude' ] ) ? $args[ 'longitude' ] : null;

    $location_range  = isset( $args[ 'filters' ] ) && isset( $args[ 'filters' ]['yisl_radius'] ) ? absint( $args[ 'filters' ]['yisl_radius'][0] ) : null;

    $show_all = isset( $args[ 'show_all' ] ) ? $args[ 'show_all' ] : 'no';

    if( $show_all === 'yes' && !! get_transient( 'yith_sl_stores' ) ){
        $stores = get_transient( 'yith_sl_stores' );
    }else{

        $search_args = array(
            'posts_per_page' => -1,
            'post_type'      => YITH_Store_Locator_Post_Type::$post_type_name,
            'post_status'    => 'publish',
            'fields'         => array('ids','post_title'),
            'meta_query'     => array(
                'relation' => 'AND',
            )
        );

        if (  !!$location_range && !!$latitude && !!$longitude ) {
            // Location approximation for database query
            $earth_radius   = 6371;
            $distance_unit = yith_sl_get_option( 'filter-radius-distance-unit', 'km' );

            $location_range = $distance_unit == 'miles' ? $location_range * 1.60934 : $location_range;
            $location_range = min( $location_range, $earth_radius );
            $lat            = $latitude;
            $lng            = $longitude;
            $delta_lat      = rad2deg( $location_range / $earth_radius );
            $delta_lng      = rad2deg( asin( $location_range / $earth_radius ) / cos( deg2rad( $lat ) ) );
            $max_lat        = min( $lat + $delta_lat, 90 );
            $min_lat        = max( $lat - $delta_lat, -90 );
            $max_lng        = min( $lng + $delta_lng, 180 );
            $min_lng        = max( $lng - $delta_lng, -180 );

            $search_args[ 'meta_query' ][] = array(
                'relation' => 'AND',
                array(
                    'key'     => '_yith_sl_latitude',
                    'value'   => array( $min_lat, $max_lat ),
                    'compare' => 'BETWEEN',
                    'type'    => 'DECIMAL(10,5)'
                ),
                array(
                    'key'     => '_yith_sl_longitude',
                    'value'   => array( $min_lng, $max_lng ),
                    'compare' => 'BETWEEN',
                    'type'    => 'DECIMAL(10,5)'
                ),
            );
        }

        if ( !!$filters && is_array( $filters ) ) {
            foreach ( $filters as $taxonomy => $term_value ){
                if( $taxonomy === 'yisl_radius' )
                    continue;
                $search_args[ 'tax_query' ][] = array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => array_map( 'absint', $term_value ),
                    'operator' => 'AND',
                );
            }

        }

        $posts = get_posts($search_args);

        $stores = yith_sl_organize_store_informations( $posts );
    }

    return apply_filters( 'yith_sl_filtered_stores', $stores, $args );
}



if( !function_exists( 'yith_sl_organize_store_informations' ) ){

    /**
     * Organize store informations for ajax call
     * @param $posts
     * @return array
     */
    function yith_sl_organize_store_informations( $posts ){

        $stores = array();
        $featured_stores = array();
        $enable_pin_info_modal = yith_sl_get_option( 'enable-pin-modal','yes' );

        foreach ( $posts as $post ){

            $store = YITH_SL_Store( $post->ID );

            $args = array(
              'store'   =>  $store
            );

            $is_featured = get_post_meta( $post->ID, '_yith_sl_featured', true );

            if( $enable_pin_info_modal === 'yes' ){
                ob_start();
                yith_sl_get_template( 'pin-info-modal.php', 'frontend/', $args );

                $html = ob_get_clean();
            }


            if( $is_featured ){
                $featured_stores[] = array(
                    'name'              =>  $post->post_title,
                    'id'                =>  $post->ID,
                    'slug'              =>  $post->post_name,
                    'latitude'          =>  get_post_meta( $post->ID,'_yith_sl_latitude',true ),
                    'longitude'         =>  get_post_meta( $post->ID,'_yith_sl_longitude',true ),
                    'pin_modal'         =>  isset( $html ) ? $html : 'undefined',
                    'pin_icon'          => $store->get_marker_icon()
                );
            }else{
                $stores[] = array(
                    'name'              =>  $post->post_title,
                    'id'                =>  $post->ID,
                    'slug'              =>  $post->post_name,
                    'latitude'          =>  get_post_meta( $post->ID,'_yith_sl_latitude',true ),
                    'longitude'         =>  get_post_meta( $post->ID,'_yith_sl_longitude',true ),
                    'pin_modal'         =>  isset( $html ) ? $html : 'undefined',
                    'pin_icon'          => $store->get_marker_icon()
                );
            }

        }

        return array_merge( $featured_stores, $stores );

    }

}



if( !function_exists( 'yith_sl_is_autosearch_enabled' ) ){

    /**
     * Check if auto search is enabled for store locator
     * @return string
     */
    function yith_sl_is_autosearch_enabled(){
        return yith_sl_get_option( 'enable-instant-search', 'yes' );
    }

}


if( !function_exists( 'yith_sl_get_filter_taxonomy' ) ){

    /**
     * Get filter taxonomy by ID
     * @param $id
     * @return bool
     */
    function yith_sl_get_filter_taxonomy( $id ){
        global $wpdb;
        $query = $wpdb->prepare( " SELECT filter_slug, filter_label, filter_icon, filter_show_label, filter_type
                FROM {$wpdb->prefix}". YITH_SL_FILTERS_TABLE ." 
                WHERE filter_id = %s
                ", $id );
        $result = $wpdb->get_results( $query );
        return !! $result ? $result[0] : false;


    }

}


if( !function_exists( 'yith_sl_get_filter_taxonomy_label_by_slug' ) ){

    /**
     * Get filter taxonomy label by slug
     * @param $slug
     * @return bool
     */
    function yith_sl_get_filter_taxonomy_label_by_slug( $slug ){
        global $wpdb;
        $query = $wpdb->prepare(" SELECT filter_label
                FROM {$wpdb->prefix}". YITH_SL_FILTERS_TABLE ." 
                WHERE filter_slug = %s
                ", $slug );
        $result = $wpdb->get_results( $query );
        return !! $result ? $result[0]->filter_label : false;


    }

}


if( !function_exists( 'yith_sl_get_term_name_by_id' ) ){

    /**
     * Get term name by ID
     * @param $id
     * @return string
     */
    function yith_sl_get_term_name_by_id( $id ){
        global $wpdb;
        $query = $wpdb->prepare( "SELECT name FROM {$wpdb->terms} WHERE term_id = %s", $id );
        $results = $wpdb->get_results( $query );
        return !!$results ? $results[0]->name : '';
    }

}


if( !function_exists( 'yith_sl_get_filters_with_terms' ) ){

    /**
     * Get filter including terms
     * @return array
     */
    function yith_sl_get_filters_with_terms(){

        $filters    = YITH_Store_Locator_Filters_Taxonomies()->get_filters();
        $prefix     = YITH_Store_Locator_Filters_Taxonomies()->get_prefix();

        $organized_filters = array();

        if( is_array( $filters ) && !empty( $filters ) ){
            foreach ( $filters as $slug => $filter ){
                if( yith_sl_is_radius_filter( $slug ) ){
                    $organized_filters['radius'] = array(
                        'slug'          =>  $slug,
                        'label'         =>  $filter['label'],
                        'type'          =>  $filter['type'],
                        'show_label'    =>  $filter['show_label'],
                        'terms'         =>  get_terms( $prefix . $slug, 'hide_empty=0' ),
                        'icon'          =>  $filter['icon']
                    );
                }else{
                    $organized_filters['stores'][] = array(
                        'slug'          =>  $slug,
                        'label'         =>  $filter['label'],
                        'type'          =>  $filter['type'],
                        'show_label'    =>  $filter['show_label'],
                        'terms'         =>  get_terms( $prefix . $slug, 'hide_empty=0' ),
                        'icon'          =>  $filter['icon']
                    );
                }

            }
        }

        return $organized_filters;

    }

}

if( !function_exists( 'yith_sl_get_loader_icon' ) ){

    /**
     * Get loader icon
     * @param $loader_type
     * @return mixed|string
     */
    function yith_sl_get_loader_icon( $loader_type ){
        $default_loader =   yith_sl_get_option( 'loader-icon', 'loader1' );
        $custom_icon    =   yith_sl_get_option( 'loader-custom-icon' );
        $icon_url       = $loader_type === 'default' ? YITH_SL_ASSETS_URL . 'images/admin-panel/' . $default_loader . '.svg' : $custom_icon;

        return $icon_url;
    }

}



if( !function_exists( 'yith_sl_get_loader_size' ) ){

    /**
     * Get loader size
     * @return string
     */
    function yith_sl_get_loader_size(){
        $loader_size = yith_sl_get_option( 'loader-icon-size','medium' );
        $size = '';
        switch ( $loader_size ){
            case 'small':
                $size = '100';
                break;

            case 'medium':
                $size = '200';
                break;

            case 'big':
                $size = '300';
                break;

            default:
                $size = '150';
                break;
        }

        return $size;
    }

}


if( !function_exists( 'yith_sl_get_opacity' ) ){

    /**
     * Get opacity for circle radius
     * @param $option
     * @return float
     */
    function yith_sl_get_opacity_( $option ){

        switch( $option ){
            case 'circle_background':
                $value = yith_sl_get_option('circle-colors', array( 'border' => '#18BCA9' ) )['border'];
                var_dump($value);

                break;

            case 'circle_border':

                break;

            default:
                $opacity = 0.5;
                break;
        }

        return $opacity;

    }

}


if( !function_exists( 'yith_sl_is_radius_filter' ) ){

    function yith_sl_is_radius_filter( $filter ){
        $is_radius = false;
        $radius_taxonomy_slug   = YITH_Store_Locator_Filters_Taxonomies()->get_radius_filter_taxonomy_slug();
        $radius_slug            = YITH_Store_Locator_Filters_Taxonomies()->get_radius_filter_slug();
        if( $filter === $radius_taxonomy_slug || $filter === $radius_slug ){
            $is_radius = true;
        }
        return $is_radius;
    }

}