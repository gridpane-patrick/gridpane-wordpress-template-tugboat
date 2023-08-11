<?php

$tab = array(
    'stores-page-details' => array(
        'header' => array(

            array(
                'name' => esc_html_x( 'Stores pages', 'Heading for options relative to store page', 'yith-store-locator'),
                'type' => 'title'
            ),

            array('type' => 'close'),

            'single-layout'    =>  array(
                'id'        =>  'single-layout',
                'name'      =>  esc_html__('Store detail page layout', 'yith-wordpress-title-bar-effects'),
                'type'      =>  'radio',
                'options'   =>  array(
                    'classic'       =>  esc_html__( 'Classic layout (image on the left)','yith-store-locator' ),
                    'alternative'   =>  esc_html__( 'Alternative layout (big image)','yith-store-locator' )
                ),
                'desc'      =>   esc_html__( 'Choose the layout for store detail page', 'yith-store-locator '),
                'std'       =>  'classic'
            ),
            'title-address-section' => array(
                'id'        =>  'title-address-section',
                'name'      =>  esc_html__('Enter title for "Address" section', 'yith-store-locator'),
                'type'      =>  'text',
                'desc'      =>  esc_html__( 'Enter a title to identify the Address section', 'yith-store-locator '),
                'std'       =>  esc_html__( 'Address','yith-store-locator' )
            ),

            'icon-address-section' => array(
                'id'        =>  'icon-address-section',
                'name'      =>  esc_html__('Upload an icon for "Address" section', 'yith-store-locator'),
                'type'      =>  'upload',
                'desc'      =>  esc_html__( 'Optional: upload an icon to identify the Address section', 'yith-store-locator '),
                'std'       =>  YITH_SL_ASSETS_URL .'/images/single/address.svg'
            ),

            'title-contact-section' => array(
                'id'        => 'title-contact-section',
                'name'      =>  esc_html__('Enter title for "Contact" section', 'yith-store-locator'),
                'type'      =>  'text',
                'desc'      =>  esc_html__( 'Enter a title to identify the Contact section', 'yith-store-locator '),
                'std'       =>  esc_html__( 'Contact Info','yith-store-locator' )
            ),

            'icon-contact-section' => array(
                'id'        =>  'icon-contact-section',
                'name'      =>  esc_html__('Upload an icon for "Contact Info" section', 'yith-store-locator'),
                'type'      =>  'upload',
                'desc'      =>  esc_html__( 'Optional: upload an icon to identify the Contact section', 'yith-store-locator '),
                'std'       =>  YITH_SL_ASSETS_URL .'images/single/contact.svg'
            ),

            'title-opening-hours-section' => array(
                'id'        =>  'title-opening-hours-section',
                'name'      =>  esc_html__('Enter title for "Opening Hours" section', 'yith-store-locator'),
                'type'      =>  'text',
                'desc'      =>  esc_html__( 'Enter a title to identify the Opening hours section', 'yith-store-locator '),
                'std'       =>  esc_html__("We're open on:", 'yith-store-locator')
            ),

            'icon-opening-hours-section' => array(
                'id'        =>  'icon-opening-hours-section',
                'name'      =>  esc_html__('Upload an icon for "Opening-Hours" section', 'yith-store-locator'),
                'type'      =>  'upload',
                'desc'      =>  esc_html__( 'Optional: upload an icon to identify the Opening hours section', 'yith-store-locator '),
                'std'       =>   YITH_SL_ASSETS_URL .'images/single/opening-hours.svg'
            ),

        ),






    ),

);


return apply_filters('yith_sl_panel_stores_page_details_options', $tab);