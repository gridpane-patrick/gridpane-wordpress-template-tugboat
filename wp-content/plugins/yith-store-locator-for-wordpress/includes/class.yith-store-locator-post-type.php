<?php
/**
 * Main class
 *
 * @author YITH
 * @package YITH Store Locator
 * @version 1.0.0
 */

defined( 'YITH_SL' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'YITH_Store_Locator_Post_Type' ) ) {


    class YITH_Store_Locator_Post_Type {

        /**
         * Single instance of the class
         *
         * @var YITH_Store_Locator_Post_Type
         * @since 1.0.0
         */
        protected static $instance;


        public static $post_type_name = 'yith_sl_store';


        /**
         * Returns single instance of the class
         *
         * @return YITH_Store_Locator_Post_Type
         * @since 1.0.0
         */
        public static function get_instance(){
            if( is_null( self::$instance ) ){
                self::$instance = new self();
            }
            return self::$instance;
        }


        /**
         * YITH_Store_Locator_Post_Type constructor.
         */
        public function __construct()
        {
            add_action( 'init',[ $this,'register_post_type' ] );

            add_action('admin_init', [ $this,'add_role_caps' ],999);

            /* Render custom field Google Map */
            add_action( 'yith_sl_google_map', [ $this,'show_google_map' ] );
            
            /* Manage texonomies */
            add_action( 'init',[ $this,'register_metaboxes' ] );

            /* Add columns to posts list */
            add_filter( 'manage_'. $this::$post_type_name .'_posts_columns', [ $this,'set_custom_columns' ] );
            add_action( 'manage_'. $this::$post_type_name .'_posts_custom_column' , [ $this,'render_custom_columns' ], 10, 2 );

        }


        /**
         * Register post type Store Locator
         */
        public function register_post_type(){
            $labels = array(
                'name'                  => esc_html_x( 'Store', 'Post type general name', 'yith-store-locator' ),
                'singular_name'         => esc_html_x( 'Store', 'Post type singular name', 'yith-store-locator' ),
                'menu_name'             => esc_html_x( 'Stores', 'Admin Menu text', 'yith-store-locator' ),
                'name_admin_bar'        => esc_html_x( 'Store', 'Add New on Toolbar', 'yith-store-locator' ),
                'add_new'               => esc_html__( 'Add New', 'yith-store-locator' ),
                'add_new_item'          => esc_html__( 'Add New Store', 'yith-store-locator' ),
                'new_item'              => esc_html__( 'New Store', 'yith-store-locator' ),
                'edit_item'             => esc_html__( 'Edit Store', 'yith-store-locator' ),
                'view_item'             => esc_html__( 'View Store', 'yith-store-locator' ),
                'all_items'             => esc_html__( 'All Stores', 'yith-store-locator' ),
                'search_items'          => esc_html__( 'Search Store', 'yith-store-locator' ),
                'not_found'             => esc_html__( 'No Stores found.', 'yith-store-locator' ),
                'not_found_in_trash'    => esc_html__( 'No Stores found in Trash.', 'yith-store-locator' ),
                'featured_image'        => esc_html_x( 'Store Cover Image', 'Overrides the “Featured Image” phrase for this post type.', 'yith-store-locator' ),
                'set_featured_image'    => esc_html_x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type.', 'yith-store-locator' ),
                'remove_featured_image' => esc_html_x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type.', 'yith-store-locator' ),
                'use_featured_image'    => esc_html_x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type.', 'yith-store-locator' ),
                'archives'              => esc_html_x( 'Store archives', 'The post type archive label used in nav menus. Default “Post Archives”.', 'yith-store-locator' ),
                'insert_into_item'      => esc_html_x( 'Insert into Store', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). ', 'yith-store-locator' ),
                'uploaded_to_this_item' => esc_html_x( 'Uploaded to this Store', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). ', 'yith-store-locator' ),
                'filter_items_list'     => esc_html_x( 'Filter Stores list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. ', 'yith-store-locator' ),
                'items_list_navigation' => esc_html_x( 'Stores list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. ', 'yith-store-locator' ),
                'items_list'            => esc_html_x( 'Stores list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. ', 'yith-store-locator' ),
            );

            $args = array(
                'labels'             => $labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => false,
                'query_var'          => true,
                'rewrite'            => array( 'slug' => 'store-locator' ),
                'capabilities'       => array(
                    'edit_post'             => 'edit_' . $this::$post_type_name,
                    'edit_posts'            => 'edit_' . $this::$post_type_name . 's',
                    'edit_others_posts'     => 'edit_other_' . $this::$post_type_name . 's',
                    'publish_posts'         => 'publish_'. $this::$post_type_name .'s',
                    'read_post'             => 'read_' . $this::$post_type_name ,
                    'read_private_posts'    => 'read_private_' . $this::$post_type_name . 's',
                    'delete_posts'          => 'delete_' . $this::$post_type_name . 's',
                ),
                'has_archive'        => true,
                'hierarchical'       => false,
                'menu_position'      => null,
                'supports'           => array( 'title', 'editor', 'thumbnail' ),
            );

            register_post_type( $this::$post_type_name, $args );
        }


        public function add_role_caps(){
            $role = get_role('administrator');
            $role->add_cap( 'edit_' . $this::$post_type_name );
            $role->add_cap( 'edit_' . $this::$post_type_name . 's' );
            $role->add_cap( 'edit_other_' . $this::$post_type_name . 's' );
            $role->add_cap( 'publish_'. $this::$post_type_name .'s' );
            $role->add_cap( 'read_' . $this::$post_type_name  );
            $role->add_cap( 'read_private_' . $this::$post_type_name . 's' );
            $role->add_cap( 'delete_' . $this::$post_type_name );
        }


        /**
         * Register metaboxes for the post type Store Locator
         */
        public function register_metaboxes(){

            $metaboxes = array(
                'yith_sl_metabox_search'   =>  array(
                    'label'    =>   esc_html__( 'Search address', 'yith-store-locator' ),
                    'pages'    =>   $this::$post_type_name,
                    'context'  =>   'normal',
                    'priority' =>   'default',
                    'tabs'     =>   array(
                        'search-settings' => array( //tab
                            'label'  =>     esc_html__( 'Address', 'yith-store-locator' ),
                            'fields' =>     array(
                                'yith_sl_gmap_location' => array(
                                    'label'     =>  '',
                                    'desc'      =>  esc_html__( 'Enter the address of this store to search it', 'yith-store-locator' ),
                                    'type'      =>  'text',
                                    'std'       =>  '',
                                    'class'     =>  'yith-sl-gmap-places-autocomplete'
                                )
                            )
                        )
                    )
                ),
                'yith_sl_metabox_address'   =>  array(
                    'label'    =>   esc_html__( 'Address details', 'yith-store-locator' ),
                    'pages'    =>   $this::$post_type_name,
                    'context'  =>   'normal',
                    'priority' =>   'default',
                    'tabs'     =>   array(
                        'address-settings' => array( //tab
                            'label'  =>     esc_html__( 'Settings', 'yith-store-locator' ),
                            'fields' =>     array(
                                'yith_sl_address_line1' => array(
                                    'label'     =>  esc_html__( 'Address line 1', 'yith-store-locator' ),
                                    'type'      =>  'text',
                                    'std'       =>  '',
                                    'desc'      =>  esc_html__( 'The address of this store','yith-store-locator' )
                                ),
                                'yith_sl_address_line2' => array(
                                    'label'     => esc_html__( 'Address line 2', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'If needed, add a second line for the address','yith-store-locator' )
                                ),
                                'yith_sl_postcode' => array(
                                    'label'     => esc_html__( 'ZIP/ CAP/ Postal Code', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'Postal code of this store','yith-store-locator' )
                                ),
                                'yith_sl_city' => array(
                                    'label'     => esc_html__( 'City', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'City where the store is located','yith-store-locator' )
                                ),
                                'yith_sl_address_state' => array(
                                    'label'     => esc_html__( 'State/Province', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'State or Province of this store','yith-store-locator' )
                                ),
                                'yith_sl_address_country' => array(
                                    'label'     => esc_html__( 'Country', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'Country of this store','yith-store-locator' )
                                ),
                                'yith_sl_latitude' => array(
                                    'label'    => esc_html__( 'Latitude', 'yith-store-locator' ),
                                    'type'     => YITH_SL_DEBUG === true ? 'text' : 'hidden',
                                    'std'      => '',
                                ),
                                'yith_sl_longitude' => array(
                                    'label'    => esc_html__( 'Longitude', 'yith-store-locator' ),
                                    'type'     => YITH_SL_DEBUG === true ? 'text' : 'hidden',
                                    'std'      => '',
                                ),
                                'yith_sl_custom_url_direction_button' => array(
                                    'label'     => esc_html__( 'Custom URL for get direction button','yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'Enter a custom url to override the link that redirect the user in google maps to get directions','yith-store-locator' )
                                ),
                                'yith_sl_google_map' => array(
                                    'label'    => esc_html__( 'Map', 'yith-store-locator' ),
                                    'type'     => 'custom',
                                    'action'   => 'yith_sl_google_map',
                                ),
                                'yith_sl_google_map_place_id' => array(
                                    'type'     => 'hidden',
                                ),
                            )
                        )
                    )
                ),
                'yith_sl_metabox_contact_info'   =>  array(
                    'label'    => esc_html__( 'Contact Info',  'yith-store-locator' ),
                    'pages'    => $this::$post_type_name,
                    'context'  => 'normal',
                    'priority' => 'default',
                    'tabs'     => array(
                        'contact-info-settings' => array( //tab
                            'label'  => esc_html__( 'Contact Info',  'yith-store-locator' ),
                            'fields' => array(
                                'yith_sl_phone' => array(
                                    'label'     => esc_html__( 'Phone', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'Enter a phone number for this store','yith-store-locator' )
                                ),
                                'yith_sl_mobile_phone' => array(
                                    'label'     => esc_html__( 'Mobile phone', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'Enter a mobile number this store','yith-store-locator' )
                                ),
                                'yith_sl_fax' => array(
                                    'label'     => esc_html__( 'Fax', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'Enter a fax number this store','yith-store-locator' )
                                ),
                                'yith_sl_email' => array(
                                    'label'     => esc_html__( 'E-mail', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'Enter the email address','yith-store-locator' )
                                ),
                                'yith_sl_website' => array(
                                    'label'     => esc_html__( 'Website', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'Enter the website url of this store','yith-store-locator' )
                                ),
                                'yith_sl_custom_url_contact_button' => array(
                                    'label'     => esc_html__( 'Custom Url for contact button', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'std'       => '',
                                    'desc'      =>  esc_html__( 'Enter a custom url to redirect the user when click on contact button','yith-store-locator' )
                                ),
                            )
                        )
                    )
                ),
                'yith_sl_metabox_advanced'  =>  array(
                    'label'    => esc_html__( 'Advanced settings', 'yith-store-locator' ),
                    'pages'    => $this::$post_type_name,
                    'context'  => 'normal',
                    'priority' => 'default',
                    'tabs'     => array(
                        'advanced-settings' => array( //tab
                            'label'  => esc_html__( 'Advanced settings', 'yith-store-locator' ),
                            'fields' => array(
                                'yith_sl_opening_hours_text' => array(
                                    'label'             => esc_html__( 'Opening hours text', 'yith-store-locator' ),
                                    'type'              => 'textarea-editor',
                                    'textarea_rows'     =>  5,
                                    'desc'              => esc_html__( 'Enter a custom text to inform users about your opening hours holidays and so on.','yith-store-locator' )
                                ),
                                'yith_sl_featured' => array(
                                    'label'     => esc_html__( 'Make featured store', 'yith-store-locator' ),
                                    'type'      => 'onoff',
                                    'std'       => 'no',
                                    'desc'      => esc_html__( 'Choose if you want to promote this store as "featured store"','yith-store-locator' )
                                ),
                                'yith_sl_store_name_link' => array(
                                        'id'        =>  '_yith_sl_store_name_link',
                                        'label'     => esc_html__( 'Store name link options:', 'yith-store-locator' ),
                                        'type'      => 'radio',
                                        'options'   => array(
                                            'none'          => esc_html__( 'No link','yith-store-locator' ),
                                            'store-page'    => esc_html__( 'Link to store page', 'yith-store-locator' ),
                                            'custom'        => esc_html__( 'Link to custom url', 'yith-store-locator' )
                                        ),
                                        'desc'      => esc_html__( 'Choose if the store name has to redirect the user to a specific page or not','yith-store-locator' ),
                                        'std'       => 'store-page'
                                ),
                                'yith_sl_store_custom_link' => array(
                                    'label'     => esc_html__( 'Custom URL for store name', 'yith-store-locator' ),
                                    'type'      => 'text',
                                    'deps'      => array(
                                        'id'        => '_yith_sl_store_name_link',
                                        'value'     => 'custom',
                                        'type'      => 'hide'
                                    ),
                                    'desc'      => esc_html__( 'Enter the url to redirect the user when click on store name','yith-store-locator' ),
                                    'std'       => '',
                                ),
                                'yith_sl_marker_icon' => array(
                                    'label'     => esc_html__( 'Default map icon for this store', 'yith-store-locator' ),
                                    'type'      => 'upload',
                                    'std'       => '',
                                    'desc'      => esc_html__( 'Upload a custom icon to identify this store in map and override the default icon','yith-store-locator' ),
                                )
                            )
                        )
                    )
                )
            );


            foreach ( $metaboxes as $key => $args ){
                $metabox = YIT_Metabox( $key );
                $metabox->init( $args );
            }

        }

        /**
         * Show Google Map custom field
         */
        public function show_google_map(){
            echo '<div id="yith_sl_map" style="width:100%;height:400px;"></div>';
        }


        /**
         * Add custom meta field for taxonomy "Filter"
         */
        public function custom_taxonomy_add_filter_meta_fields( $term ){
            $icon = array(
                'id'        => 'yith-sl-filter-icon',
                'name'      => 'yith-sl-filter-icon',
                'type'      => 'upload',
            );
            $layout = array(
                'id'        => 'yith-sl-filter-layout',
                'name'      => 'yith-sl-filter-layout',
                'type'      => 'select',
                'options'   =>  array(
                    esc_html__( 'Dropdown select','yith-store-locator' ),
                    esc_html__( 'Checkbox','yith-store-locator' )
                )
            );
            ?>
            <div class="form-field term-description-wrap">
                <label for="tag-description"><?php esc_html_e( 'Custom icon','yith-store-locator' ) ?></label>
                <?php yith_plugin_fw_get_field( $icon, true ); ?>
            </div>
            <div class="form-field term-description-wrap">
                <label for="tag-description"><?php esc_html_e( 'Display as','yith-store-locator' ) ?></label>
                <?php yith_plugin_fw_get_field( $layout, true ); ?>
            </div>
            <?php
        }

        /**
         * Edit custom meta field for taxonomy "Filter"
         */
        public function custom_taxonomy_edit_filter_meta_fields( $term ){

            $icon_value = get_term_meta( $term->term_id, 'yith-sl-filter-icon',true );
            $icon_field = array(
                'id'    => 'yith-sl-filter-icon',
                'name'  => 'yith-sl-filter-icon',
                'type'  => 'upload',
                'value' =>  $icon_value
            );
            $layout_value = get_term_meta( $term->term_id, 'yith-sl-filter-layout',true );
            $layout_field = array(
                'id'        => 'yith-sl-filter-layout',
                'name'      => 'yith-sl-filter-layout',
                'type'      => 'select',
                'options'   =>  array(
                    'dropdown'  =>  __( 'Dropdown select','yith-store-locator' ),
                    'checkbox'  =>  __( 'Checkbox','yith-store-locator' )
                ),
                'value'     =>  $layout_value
            );
            ?>
            <table class="form-table" role="presentation">
                <tr class="form-field term-description-wrap">
                    <th scope="row"><label for="description"><?php esc_html_e( 'Custom icon','yith-store-locator' ) ?></label></th>
                    <td>
                        <?php yith_plugin_fw_get_field( $icon_field, true, false ); ?>
                    </td>
                </tr>
                <tr class="form-field term-description-wrap">
                    <th scope="row"><label for="description"><?php esc_html_e( 'Display as','yith-store-locator' ) ?></label></th>
                    <td>
                        <?php yith_plugin_fw_get_field( $layout_field, true, false ); ?>
                    </td>
                </tr>
            </table>
            <?php
        }


        /**
         * Save custom meta fields in edit store filter page
         * @param $term_id
         */
        public function save_store_filters_custom_meta_field( $term_id ){
            if( isset($_POST['yith-sl-filter-icon']) ){
                update_term_meta($term_id, 'yith-sl-filter-icon', $_POST['yith-sl-filter-icon']);
            }
            if( isset($_POST['yith-sl-filter-layout']) ){
                update_term_meta($term_id, 'yith-sl-filter-layout', $_POST['yith-sl-filter-layout']);
            }
        }


        /**
         * Set custom columsn for admin Store Locators post type page
         * @param $columns
         * @return array
         */
        public function set_custom_columns( $columns ){
            $tax_radius_key = 'taxonomy-' . YITH_Store_Locator_Filters_Taxonomies()->get_radius_filter_taxonomy_slug();
            unset( $columns[$tax_radius_key] );
            $new_columns = array(
                'address'       =>  esc_html__( 'Address', 'Custom column name (Stores list admin view)', 'your_text_domain' ),
                'contact_info'  =>  esc_html__( 'Contact Info', 'Custom column name (Stores list admin view)', 'your_text_domain' ),
            );
            $res = array_slice( $columns, 0, 2, true ) +
                $new_columns +
                array_slice( $columns, 1, count($columns)-1, true );

            return $res;
        }


        /**
         * Show custom fields inside the table of Store Locators post type
         * @param $column
         * @param $post_id
         */
        function render_custom_columns( $column, $post_id ) {

            $store = YITH_SL_Store( $post_id );

            switch ( $column ) {

                case 'address' :

                    $format       = '{address_line_1} - {address_line_2}\n{city} - {state} - {postcode}\n{country}';
                    $format       = apply_filters( 'yith_sl_address_format', $format );
                    $placeholders = array(
                        '{address_line_1}' => $store->get_prop( 'address_line1' ),
                        '{address_line_2}' => $store->get_prop( 'address_line2' ),
                        '{city}'           => $store->get_prop( 'city' ),
                        '{state}'          => $store->get_prop( 'address_state' ),
                        '{postcode}'       => $store->get_prop( 'postcode' ),
                        '{country}'        => $store->get_prop( 'address_country' ),
                    );

                    $formatted_address = str_replace( array_keys( $placeholders ), $placeholders, $format );
                    $formatted_address = explode( '\n', $formatted_address );
                    $formatted_address = implode( '<br />', $formatted_address );

                    echo $formatted_address;

                    break;

                case 'contact_info' :

                    $data = array(
                        'phone'        => esc_html__( 'Phone', 'yith-store-locator' ),
                        'mobile_phone' => esc_html__( 'Mobile Phone', 'yith-store-locator' ),
                        'fax'          => esc_html__( 'Fax', 'yith-store-locator' ),
                        'email'        => esc_html__( 'Email', 'yith-store-locator' ),
                    );
                    echo '<ul>';
                        foreach ( $data as $key => $label ) {
                            $value = $store->get_prop($key);
                            if ( ! empty( $value ) ) {
                                echo sprintf( '<li><strong>%s</strong>: %s</li>', $label, $value );
                            }
                        }
                    echo '</ul>';

                    break;

            }
        }
    }
}

/**
 * Unique access to instance of yith_slore_Locator class
 *
 * @return YITH_Store_Locator_Post_Type
 * @since 1.0.0
 */
function YITH_Store_Locator_Post_Type(){
    return YITH_Store_Locator_Post_Type::get_instance();
}
