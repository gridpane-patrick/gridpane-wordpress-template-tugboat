<?php

global $post;

$page_layout = yith_sl_get_option('single-layout', 'classic' );

$store = YITH_SL_Store($post);

$args = array(
    'name'                      =>  $store->get_name(),
    'image'                     =>  $store->get_image(),
    'description'               =>  $store->get_description(),
    'address_title'             =>  yith_sl_get_option('title-address-section', esc_html__( 'Address','yith-store-locator' )  ),
    'address_icon'              =>  yith_sl_get_option('icon-address-section', YITH_SL_ASSETS_URL .'images/single/address.svg' ),
    'address'                   =>  $store->get_full_address(),
    'custom_direction_link'     =>  $store->get_direction_link(),
    'custom_direction_label'    =>  apply_filters( 'yith-sl-custom-direction-label', esc_html__( 'Get direction >', 'yith-store-locator' ) ),
    'contact_info_title'        =>  yith_sl_get_option('title-contact-section', esc_html__( 'Contact Info','yith-store-locator' ) ),
    'contact_info_icon'         =>  yith_sl_get_option('icon-contact-section', YITH_SL_ASSETS_URL .'images/single/contact.svg' ),
    'phone'                     =>  $store->get_prop( 'phone' ),
    'mobile_phone'              =>  $store->get_prop( 'mobile_phone' ),
    'email'                     =>  $store->get_prop('email' ),
    'website'                   =>  $store->get_prop('website' ),
    'custom_contact_link'       =>  $store->get_prop('custom_url_contact_button' ),
    'custom_contact_label'      =>  apply_filters( 'yith-sl-custom-contact-label', esc_html__( 'Contact store', 'yith-store-locator' ) ),
    'opening_hours_title'       =>  yith_sl_get_option('title-opening-hours-section', esc_html__( "We're open on:", 'yith-store-locator' ) ),
    'opening_hours_icon'        =>  yith_sl_get_option('icon-opening-hours-section', YITH_SL_ASSETS_URL .'images/single/opening-hours.svg' ),
    'opening_hours_text'        =>  wpautop( $store->get_prop('opening_hours_text' ) ),
);

get_header();

do_action('yith_sl_single_after_header');

$template = yith_sl_get_template( $page_layout . '.php', 'frontend/single/', $args );

?>

<?php
do_action('yith_sl_single_before_footer');

wp_footer();

get_footer();