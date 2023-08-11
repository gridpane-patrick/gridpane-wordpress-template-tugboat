<?php
// Exit if accessed directly
!defined('YITH_SL') && exit();

$tab = array(
    'stores-results' => array(
        'stores-results-options' => array(
            'general-options' => array(
                'name' => esc_html__('Stores results', 'yith-store-locator'),
                'type' => 'title',
                'desc' => '',
            ),

            'enable-stores-list' => array(
                'id'        => 'enable-stores-list',
                'name'      => esc_html__('Show the stores list', 'yith-store-locator'),
                'type'      => 'onoff',
                'desc'      => esc_html__('Choose if you want to display or hide the stores list in the result.', 'yith-store-locator'),
                'std'       => 'yes',
            ),


            'stores-list-by-default'  => array(
                'id'        => 'stores-list-by-default',
                'name'      => esc_html__('Show stores list by default', 'yith-store-locator'),
                'type'      => 'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or hide the stores list by default', 'yith-store-locator'),
                'deps'      => array(
                    'ids'    => 'enable-stores-list',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
                'std'       => 'no',
            ),

            'number-results-to-show'  => array(
                'id'        => 'number-results-to-show',
                'name'      => esc_html__('Set how many results to show (before showing the "view all link")', 'yith-store-locator'),
                'type'      => 'number',
                'desc'      =>  esc_html__('Choose how many stores to show in the list', 'yith-store-locator'),
                'std'       =>  5,
                'deps' => array(
                    'ids'    => 'enable-stores-list',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),

            'result-hover-color'  => array(
                'id'    => 'result-hover-color',
                'name'  =>  esc_html__('Stores list hover effect', 'yith-store-locator'),
                'desc'  =>  esc_html__('Set the background color for the hover effect on the stores list', 'yith-store-locator'),
                'type'  =>  'colorpicker',
                'std'   =>  '#E7F5F5'
            ),


            'view-all-text'  => array(
                'id'        => 'view-all-text',
                'name'      => esc_html__('Enter the text for "view all" link', 'yith-store-locator'),
                'type'      => 'text',
                'desc'      =>  esc_html__('Enter text for "view all stores" pagination link', 'yith-store-locator'),
                'std'       =>  esc_html__( 'View all stores', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'enable-stores-list',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),

            'view-all-color'  => array(
                'id'                => 'view-all-color',
                'name'              =>  esc_html__('"view all" link color', 'yith-store-locator'),
                'desc'              =>  esc_html__('Choose the color for the "view all" link', 'yith-store-locator'),
                'type'              => 'multi-colorpicker',
                'colorpickers'      => array(
                    array(
                        'id'        =>  'normal',
                        'name'      =>  esc_html_x( 'Normal state', 'Color used for the background when user see the button', 'yith-store-locator' ),
                        'default'       =>  '#18BCA9'
                    ),
                    array(
                        'id'        =>  'hover',
                        'name'      =>  esc_html_x( 'Hover state', 'Color used for the background when user hover the button', 'yith-store-locator' ),
                        'default'       => '#008a81'
                    )
                ),
            ),

        ),

        'store-list-info-options'  => array(

            'title'         =>  array(
                'id'        => 'title',
                'name'      => '',
                'html'      => '<h2 class="my-title"> ' . __( "Stores list - Info to show", "yith-store-locator" ) . '</h2>',
                'type'      => 'html',
                'desc'      =>   ''
            ),

            'stores-list-show-store-name' => array(
                'id'        =>  'stores-list-show-store-name',
                'name'      =>  esc_html__('Show store name', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__( 'Choose if you want to display or hide the store names', 'yith-store-locator' ),
                'std'       =>  'yes'
            ),

            'stores-list-show-store-image' => array(
                'id'        =>  'stores-list-show-store-image',
                'name'      =>  esc_html__('Show store image', 'yith-store-locator'),
                'type'      => 'onoff',
                'desc'      =>  esc_html__( 'Choose if you want to display or hide the store images', 'yith-store-locator' ),
                'std'       =>  'yes'
            ),

            'stores-list-image-position' => array(
                'id'        =>  'stores-list-image-position',
                'name'      =>  esc_html__('Image position', 'yith-store-locator'),
                'type'      => 'radio',
                'desc'      =>  esc_html__('Choose the store image position in stores list', 'yith-store-locator'),
                'std'       =>  'left',
                'options'   =>  array(
                    'left'  =>  esc_html__( 'Left', 'yith-sore-locator' ),
                    'right' =>  esc_html__( 'Right', 'yith-sore-locator' ),
                    'top'   =>  esc_html__( 'Top', 'yith-sore-locator' )
                ),
                'deps' => array(
                    'ids'    => 'stores-list-show-store-image',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),

            'stores-list-show-store-description' => array(
                'id'        =>  'stores-list-show-store-description',
                'name'      =>  esc_html__('Show store description', 'yith-store-locator'),
                'type'      => 'onoff',
                'desc'      =>  esc_html__( 'Choose if you want to display or hide the store descriptions', 'yith-store-locator' ),
                'std'       =>  'no'
            ),


            'stores-list-show-store-address' => array(
                'id'        =>  'stores-list-show-store-address',
                'name'      =>  esc_html__('Show store address', 'yith-store-locator'),
                'type'      => 'onoff',
                'desc'      =>  esc_html__( 'Choose if you want to display or hide the store addresses', 'yith-store-locator' ),
                'std'       =>  'yes'
            ),

            'stores-list-show-get-directions' => array(
                'id'        =>  'stores-list-show-get-directions',
                'name'      =>  esc_html__('Show "Get Direction"', 'yith-store-locator'),
                'type'      => 'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or hide the "get directions" link', 'yith-store-locator'),
                'std'       =>  'yes'
            ),

            'stores-list-get-direction-style' => array(
                'id'        =>  'stores-list-get-direction-style',
                'name'      =>  esc_html__('"Get direction" link style', 'yith-store-locator'),
                'type'      => 'radio',
                'desc'      =>  esc_html__('Choose if you want to use a simple text or a button for "get direction" option', 'yith-store-locator'),
                'std'       =>  'link',
                'options'   =>  array(
                    'link'      =>  esc_html__( 'Textual', 'yith-sore-locator' ),
                    'button'    =>  esc_html__( 'Button', 'yith-sore-locator' ),
                ),
                'deps' => array(
                    'ids'    => 'stores-list-show-get-directions',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'stores-list-get-direction-text' => array(
                'id'        =>  'stores-list-get-direction-text',
                'name'      =>  esc_html__('Enter a text for "Get Direction" button', 'yith-store-locator'),
                'type'      => 'text',
                'desc'      =>  esc_html__('Enter a text for "get direction" link', 'yith-store-locator'),
                'std'       =>  esc_html__( 'Get direction', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'stores-list-show-get-directions',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'stores-list-color-get-direction-link' => array(
                'id'            => 'stores-list-color-get-direction-link',
                'name'          => esc_html__('"Get direction" colors', 'yith-store-locator'),
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'        => 'normal',
                        'name'      => esc_html_x( 'Normal state', 'Color used for the background when user see the button', 'yith-store-locator' ),
                        'default'       => '#18BCA9'
                    ),
                    array(
                        'id'        => 'hover',
                        'name'      => esc_html_x( 'Hover state', 'Color used for the background when user hover the button', 'yith-store-locator' ),
                        'default'       => '#008a81'
                    )
                ),
                'desc'      =>  esc_html__( 'Set colors for "Get direction" option', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'stores-list-get-direction-style',
                    'values' => 'link',
                    'type'  => 'hide'
                ),
            ),

            'stores-list-color-get-direction-button' => array(
                'id'            => 'stores-list-color-get-direction-button',
                'name'          => esc_html__('"Get direction" colors', 'yith-store-locator'),
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'        => 'normal',
                        'name'      => esc_html_x( 'Text', 'Color used for the text when user see the button', 'yith-store-locator' ),
                        'default'       => '#ffffff'
                    ),
                    array(
                        'id'        => 'hover',
                        'name'      => esc_html_x( 'Text hover', 'Color used for the text when user hover the button', 'yith-store-locator' ),
                        'default'       => '#ffffff'
                    )
                ),
                'desc'              =>  '',
                'deps'              => array(
                    'ids'           => 'stores-list-get-direction-style',
                    'values'        => 'button',
                    'type'          => 'hide'
                ),
            ),


            'stores-list-background-color-get-direction-button' => array(
                'id'            => 'stores-list-background-color-get-direction-button',
                'name'          => '',
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'        => 'normal',
                        'name'      => esc_html_x( 'Background', 'Color used for the background when user see the button', 'yith-store-locator' ),
                        'default'       => '#18BCA9'
                    ),
                    array(
                        'id'        => 'hover',
                        'name'      => esc_html_x( 'Background hover', 'Color used for the background when user hover the button', 'yith-store-locator' ),
                        'default'       => '#008a81'
                    )
                ),
                'desc'      =>  esc_html__( 'Set colors for "Get direction" option', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'stores-list-get-direction-style',
                    'values' => 'button',
                    'type'  => 'hide'
                ),
            ),


            'stores-list-icon-get-direction-button' => array(
                'id'                =>  'stores-list-icon-get-direction-button',
                'name'              =>  esc_html__('Upload an icon for "Get direction" button', 'yith-store-locator'),
                'type'              =>  'upload',
                'desc'              =>  esc_html__( 'Upload an optional icon to identify the "Get direction" option', 'yith-store-locator' ),
                'std'               =>  '#',
                'deps' => array(
                    'ids'    => 'stores-list-get-direction-style',
                    'values' => 'button',
                    'type'  => 'hide'
                ),
            ),

            'stores-list-show-store-contact-info' => array(
                'id'        =>  'stores-list-show-store-contact-info',
                'name'      =>  esc_html__('Show "Contact info"', 'yith-store-locator'),
                'type'      => 'onoff',
                'desc'      =>  esc_html__( 'Choose if you want to display or hide the contact info section', 'yith-store-locator' ),
                'std'       =>  'yes'
            ),

            'stores-list-show-contact-store' => array(
                'id'        =>  'stores-list-show-contact-store',
                'name'      =>  esc_html__('Show "Contact store"', 'yith-store-locator'),
                'type'      => 'onoff',
                'desc'      =>  esc_html__( 'Choose if you want to display or hide the "contact store" link', 'yith-store-locator' ),
                'std'       =>  'yes'
            ),

            'stores-list-contact-store-text' => array(
                'id'        =>  'stores-list-contact-store-text',
                'name'      =>  esc_html__('Enter text for "Contact" button', 'yith-store-locator'),
                'type'      => 'text',
                'desc'      =>  esc_html__( 'Enter text for "contact store" link','yith-store-locator' ),
                'std'       =>  esc_html__( 'Contact store >', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'stores-list-show-contact-store',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),


            'stores-list-contact-store-style' => array(
                'id'        =>  'stores-list-contact-store-style',
                'name'      =>  esc_html__('"Contact store" style', 'yith-store-locator'),
                'type'      => 'radio',
                'desc'      =>  esc_html__( 'Choose if you want to use a  text or button for "contact store" option','yith-store-locator' ),
                'std'       =>  'link',
                'options'   =>  array(
                    'link'      =>  esc_html__( 'Textual', 'yith-sore-locator' ),
                    'button'    =>  esc_html__( 'Button', 'yith-sore-locator' ),
                ),
                'deps' => array(
                    'ids'    => 'stores-list-show-contact-store',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),


            'stores-list-color-contact-store-link' => array(
                'id'            => 'stores-list-color-contact-store-link',
                'name'          => esc_html__('"Contact store" colors', 'yith-store-locator'),
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'        => 'normal',
                        'name'      => esc_html_x( 'Normal state', 'Color used for the text when user see the link', 'yith-store-locator' ),
                        'default'       => '#18BCA9'
                    ),
                    array(
                        'id'        => 'hover',
                        'name'      => esc_html_x( 'Hover state', 'Color used for the text when user hover the link', 'yith-store-locator' ),
                        'default'       => '#008a81'
                    )
                ),
                'desc'              =>  esc_html__( 'Set colors for "contact store" option','yith-store-locator' ),
                'deps'              => array(
                    'ids'           => 'stores-list-contact-store-style',
                    'values'        => 'link',
                    'type'          => 'hide'
                ),
            ),

            'stores-list-color-contact-store-button' => array(
                'id'            => 'stores-list-color-contact-store-button',
                'name'          => esc_html__('"Contact store" colors', 'yith-store-locator'),
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'        => 'normal',
                        'name'      => esc_html_x( 'Text', 'Color used for the text when user see the button', 'yith-store-locator' ),
                        'default'   => '#ffffff'
                    ),
                    array(
                        'id'        =>  'hover',
                        'name'      =>  esc_html_x( 'Text hover', 'Color used for the text when user hover the button', 'yith-store-locator' ),
                        'default'   =>  '#ffffff'
                    )
                ),
                'desc'              =>  '',
                'deps'              =>  array(
                    'ids'           =>  'stores-list-contact-store-style',
                    'values'        =>  'button',
                    'type'          =>  'hide'
                ),
            ),

            'stores-list-background-color-contact-store-button' => array(
                'id'            =>  'stores-list-background-color-contact-store-button',
                'name'          =>  '',
                'type'          =>  'multi-colorpicker',
                'colorpickers'  =>  array(
                    array(
                        'id'        => 'normal',
                        'name'      => esc_html_x( 'Background', 'Color used for the background when user see the button', 'yith-store-locator' ),
                        'default'   => '#18BCA9'
                    ),
                    array(
                        'id'        => 'hover',
                        'name'      => esc_html_x( 'Background hover', 'Color used for the background when user hover the button', 'yith-store-locator' ),
                        'default'   => '#008a81'
                    )
                ),
                'desc'              =>  esc_html__( 'Set colors for "contact store" option','yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'stores-list-contact-store-style',
                    'values' => 'button',
                    'type'  => 'hide'
                ),
            ),

            'stores-list-icon-contact-store-button' => array(
                'id'                =>  'stores-list-icon-contact-store-button',
                'name'              =>  esc_html__('Upload an icon for "Contact store" button', 'yith-store-locator'),
                'type'              =>  'upload',
                'desc'              =>  esc_html__( 'Upload an optional icon to identify the "contact store" option', 'yith-store-locator' ),
                'std'               =>  '#',
                'deps'              => array(
                    'ids'           => 'stores-list-get-direction-style',
                    'values'        => 'button',
                    'type'          => 'hide'
                ),
            ),

            'stores-list-contact-store-page' => array(
                'id'        =>  'stores-list-contact-store-page',
                'name'      =>  esc_html__('Select the contact page to link', 'yith-store-locator'),
                'type'      => 'ajax-posts',
                'data'     => array(
                    'placeholder' => __('Search Pages', 'text-domain'),
                    'post_type'   => 'page',
                ),
                'desc'      =>  esc_html__( 'Choose where to redirect the user when clicking the "contact store" link', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'stores-list-show-contact-store',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),


            'stores-list-show-visit-website' => array(
                'id'        =>  'stores-list-show-visit-website',
                'name'      =>  esc_html__('Show "Visit website" link', 'yith-store-locator'),
                'type'      => 'onoff',
                'desc'      =>  esc_html__( 'Choose if you want to display or hide the "visit website" link', 'yith-store-locator' ),
                'std'       =>  'yes'
            ),


            'stores-list-visit-website-style' => array(
                'id'        =>  'stores-list-visit-website-style',
                'name'      =>  esc_html__('"Visit website" link style', 'yith-store-locator'),
                'type'      => 'radio',
                'desc'      =>  esc_html__( 'Choose if you want to use a simple text or a button for "visit website" option', 'yith-store-locator' ),
                'std'       =>  'link',
                'options'   =>  array(
                    'link'      =>  esc_html__( 'Textual', 'yith-sore-locator' ),
                    'button'    =>  esc_html__( 'Button', 'yith-sore-locator' ),
                ),
                'deps'              => array(
                    'ids'           => 'stores-list-show-visit-website',
                    'values'        => 'yes',
                    'type'          => 'hide'
                ),
            ),

            'stores-list-visit-website-text' => array(
                'id'        =>  'stores-list-visit-website-text',
                'name'      =>  esc_html__('Enter text for "Visit website" link', 'yith-store-locator'),
                'type'      => 'text',
                'desc'      =>  esc_html__( 'Enter text for "visit website" link', 'yith-store-locator' ),
                'std'       =>  esc_html__( 'View website', 'yith-store-locator' ),
                'deps'              => array(
                    'ids'           => 'stores-list-show-visit-website',
                    'values'        => 'yes',
                    'type'          => 'hide'
                ),
            ),

            'stores-list-color-visit-website-link' => array(
                'id'            => 'stores-list-color-visit-website-link',
                'name'          => esc_html__('"Visit Website" colors', 'yith-store-locator'),
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'        => 'normal',
                        'name'      => esc_html_x( 'Normal state', 'Color used for the text when user see the link', 'yith-store-locator' ),
                        'default'   => '#18BCA9'
                    ),
                    array(
                        'id'        => 'hover',
                        'name'      => esc_html_x( 'Hover state', 'Color used for the text when user hover the link', 'yith-store-locator' ),
                        'default'   => '#008a81'
                    )
                ),
                'desc'              =>  esc_html__( 'Set colors for "visit website" option', 'yith-store-locator' ),
                'deps'              => array(
                    'ids'           => 'stores-list-visit-website-style',
                    'values'        => 'link',
                    'type'          => 'hide'
                ),
            ),

            'stores-list-color-visit-website-button' => array(
                'id'            =>  'stores-list-color-visit-website-button',
                'name'          =>  esc_html__('"Visit website" colors', 'yith-store-locator'),
                'type'          =>  'multi-colorpicker',
                'colorpickers'  =>  array(
                    array(
                        'id'        => 'normal',
                        'name'      => esc_html_x( 'Text', 'Color used for the text when user see the button', 'yith-store-locator' ),
                        'default'   => '#ffffff'
                    ),
                    array(
                        'id'        => 'hover',
                        'name'      => esc_html_x( 'Text hover', 'Color used for the text when user hover the button', 'yith-store-locator' ),
                        'default'   => '#ffffff'
                    )
                ),
                'desc'              =>  '',
                'deps'              => array(
                    'ids'           => 'stores-list-visit-website-style',
                    'values'        => 'button',
                    'type'          => 'hide'
                ),
            ),

            'stores-list-background-color-visit-website-button' => array(
                'id'            =>  'stores-list-background-color-visit-website-button',
                'name'          =>  '',
                'type'          =>  'multi-colorpicker',
                'colorpickers'  =>  array(
                    array(
                        'id'        => 'normal',
                        'name'      => esc_html_x( 'Background', 'Color used for the background when user see the button', 'yith-store-locator' ),
                        'default'   => '#18BCA9'
                    ),
                    array(
                        'id'        => 'hover',
                        'name'      => esc_html_x( 'Background hover', 'Color used for the background when user hover the button', 'yith-store-locator' ),
                        'default'   => '#008a81'
                    )
                ),
                'desc'              =>  esc_html__( 'Set colors for "visit website" option', 'yith-store-locator' ),
                'deps'      => array(
                    'ids'    => 'stores-list-visit-website-style',
                    'values' => 'button',
                    'type'  => 'hide'
                ),
            ),

            'stores-list-icon-visit-website-button' => array(
                'id'                =>  'stores-list-icon-visit-website-button',
                'name'              =>  esc_html__('Upload an icon for "Visit website" button', 'yith-store-locator'),
                'type'              =>  'upload',
                'desc'              =>  esc_html__( 'Upload an optional icon to identify the "visit website" option', 'yith-store-locator' ),
                'std'               =>  '#',
                'deps'              => array(
                    'ids'           => 'stores-list-visit-website-style',
                    'values'        => 'button',
                    'type'          => 'hide'
                ),
            ),

            'stores-list-enable-opening-hours' => array(
                'id'        =>  'stores-list-enable-opening-hours',
                'name'      =>  esc_html__('Show "Opening Hours"', 'yith-store-locator'),
                'type'      => 'onoff',
                'desc'      =>  esc_html__( 'Choose if you want to display or hide the "Opening Hours" section', 'yith-store-locator' ),
                'std'       =>  'yes'
            ),

        ),

        'stores-list-featured-options'  => array(

            'title'  =>  array(
                'id'      => 'title',
                'name'    => '',
                'html' => '<h2 class="my-title"> ' . esc_html__( "Featured stores settings", "yith-store-locator" ) . '</h2>',
                'type' => 'html',
                'desc' =>   ''
            ),

            'stores-list-featured-icon' => array(
                'id'        =>  'stores-list-featured-icon',
                'name'      =>  esc_html__( 'Icon for featured store', 'yith-store-locator'),
                'type'      =>  'upload',
                'desc'      =>  esc_html__( 'Upload an icon to identify the featured stores', 'yith-store-locator' ),
                'std'       =>  YITH_SL_ASSETS_URL .'images/store-locator/featured.svg'
            ),

            'stores-list-featured-label' => array(
                'id'        =>  'stores-list-featured-label',
                'name'      =>  esc_html__( 'Enter a badge text for featured stores', 'yith-store-locator'),
                'type'      =>  'text',
                'desc'      =>  esc_html__( 'Set a text to identify the featured stores', 'yith-store-locator' ),
                'std'       =>  esc_html__( 'Featured', 'yith-store-locator' )
            ),

            'stores-list-featured-colors' => array(
                'id'            =>  'stores-list-featured-colors',
                'name'          =>  esc_html__( 'Featured stores colors', 'yith-store-locator' ),
                'type'          =>  'multi-colorpicker',
                'colorpickers'  =>  array(
                    array(
                        'id'        => 'label-background',
                        'name'      => esc_html__( 'Badge background',  'yith-store-locator' ),
                        'default'       => '#18BCA9'
                    ),
                    array(
                        'id'        => 'label-color',
                        'name'      => esc_html_x( 'Badge text', 'yith-store-locator' ),
                        'default'       => '#ffffff'
                    ),
                    array(
                        'id'        => 'wrapper-background',
                        'name'      => esc_html_x( 'Container background','yith-store-locator' ),
                        'default'       => '#F4F4F4'
                    )
                ),
                'desc'              =>  esc_html__( 'Set colors for "Featured stores"', 'yith-store-locator' ),
            ),

        ),

        array(
            'type' => 'close'
        )
    )
);

return apply_filters('yith_sl_panel_stores_results_options', $tab);



