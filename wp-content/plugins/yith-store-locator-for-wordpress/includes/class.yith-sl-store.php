<?php


defined( 'YITH_SL' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'YITH_SL_Store' ) ) {
    /**
     * YITH Store Locator
     *
     * @since 1.0.0
     */
    class YITH_SL_Store {

        /**
         * Single instance of the class
         *
         * @var YITH_SL_Store
         * @since 1.0.0
         */
        protected static $instance;


        protected $id = 0;

        protected $post;

        protected $data = array(
            'address_line1'                 =>  '',
            'address_line2'                 =>  '',
            'postcode'                      =>  '',
            'city'                          =>  '',
            'address_state'                 =>  '',
            'address_country'               =>  '',
            'latitude'                      =>  '',
            'longitude'                     =>  '',
            'custom_url_direction_button'   =>  '',
            'phone'                         =>  '',
            'mobile_phone'                  =>  '',
            'fax'                           =>  '',
            'email'                         =>  '',
            'website'                       =>  '',
            'custom_url_contact_button'     =>  '',
            'opening_hours_text'            =>  '',
            'featured'                      =>  '',
            'store_name_link'               =>  '',
            'store_custom_link'             =>  '',
            'google_map_place_id'           =>  '',
            'marker_icon'                   =>  ''
        );



        public function __construct( $store ){
            if ( $store ) {
                if ( is_numeric( $store ) ) {
                    $this->id   = absint( $store );
                    $this->post = get_post( $this->id );
                } elseif ( $store instanceof WP_Post && $store->post_type == YITH_Store_Locator_Post_Type::$post_type_name ) {
                    $this->id   = absint( $store->ID );
                    $this->post = $store;
                }
            }
            $this->populate_props();
        }


        /**
         * Populate props for store
         */
        private function populate_props(){
            foreach ( $this->data as $prop => $default ){
                $meta = '_yith_sl_' . $prop;
                $this->data[$prop] = metadata_exists( 'post',$this->id, $meta ) ? get_post_meta( $this->id, $meta, true ) : $default;
            }
        }


        /**
         * Get prop for store
         * @param $key
         * @return mixed
         */
        public function get_prop( $key ){

            if( $this->data[$key] )
                $value = $this->data[$key];
            else
                $value = $value = get_post_meta( $this->id, '_yith_sl_' . $key, true );

            return apply_filters( 'yith_sl_' . $key, $value, $key, $this );
        }


        /**
         * Get store ID
         * @return int
         */
        public function get_id(){
            return $this->id;
        }

        /**
         * Get store name
         * @return mixed
         */
        public function get_name(){
            $name = get_the_title( $this->id );
            return apply_filters( 'yith_sl_store_name', $name, $this );
        }


        /**
         * Get store description
         * @return mixed
         */
        public function get_description(){
            $description = wpautop( get_the_content(false,false, $this->id ) );

            return apply_filters( 'yith_sl_store_description', $description, $this );
        }

        /**
         * Get store image
         * @return mixed
         */
        public function get_image(){
            $image = get_the_post_thumbnail( $this->id );
            return apply_filters( 'yith_sl_store_image', $image, $this );
        }

        /**
         * Get store full address
         * @return array|mixed|string
         */
        public function get_full_address(){

            $format = '{address_line1} {address_line2}\n{city} {state} \n {postcode} {country} ';

            $format = apply_filters( 'yith_sl_store_full_address_format',$format );

            $placeholders = array(
                '{address_line1}'  => $this->get_prop( 'address_line1' ),
                '{address_line2}'  => !!( $this->get_prop( 'address_line2' ) ) ? '-' . $this->get_prop( 'address_line2' ) : $this->get_prop( 'address_line2' ),
                '{city}'           => $this->get_prop( 'city' ),
                '{state}'          => !!( $this->get_prop( 'address_state' ) ) ? '- ' . $this->get_prop( 'address_state' ) : $this->get_prop( 'address_state' ),
                '{postcode}'       => !!( $this->get_prop( 'postcode' ) ) ? $this->get_prop( 'postcode' ) . ' - ' : $this->get_prop( 'postcode' ),
                '{country}'        => $this->get_prop( 'address_country' )
            );

            $formatted_address = str_replace( array_keys( $placeholders ), $placeholders, $format );
            $formatted_address = explode( '\n', $formatted_address );
            $formatted_address = implode( '<br />', $formatted_address );

            return  $formatted_address;

        }


        /**
         * Get store name link
         * @return false|mixed|string
         */
        public function get_store_name_link(){

            $store_name_link = $this->data['store_name_link'];

            switch ( $store_name_link ){
                case 'custom'   :
                    $store_name_link = $this->data['store_custom_link'];
                    break;

                case 'store-page'   :
                    $store_name_link = get_the_permalink( $this->id );
                    break;

                default:
                    $store_name_link = 'none';
                    break;
            }

            return $store_name_link;

        }


        /**
         * Get direction link
         * @return mixed|string
         */
        public function get_direction_link(){

            $url = '';
            if( !!$this->data['custom_url_direction_button'] ){
                $url = $this->data['custom_url_direction_button'];
            }elseif( !!$this->data['google_map_place_id'] ){
                $url = 'https://www.google.com/maps/place/?q=place_id:' . $this->data['google_map_place_id'];
            }

            return $url;

        }

        /**
         * Get store marker icon
         * @return mixed
         */
        public function get_marker_icon(){

            $default_icon = yith_sl_get_option( 'map-icon-marker', YITH_SL_ASSETS_URL .'images/store-locator/marker.svg' );

            return !!$this->data[ 'marker_icon' ] ? $this->data[ 'marker_icon' ] : $default_icon;

        }


    }
}

/**
 * Unique access to instance of YITH_SL_Store class
 *
 * @return YITH_SL_Store
 * @since 1.0.0
 */
function YITH_SL_Store( $store ){
    return new YITH_SL_Store( $store );
}
