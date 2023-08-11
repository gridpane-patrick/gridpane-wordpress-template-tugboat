<?php

$tab = array(
    'general' => array(

        'header'    => array(

            array(
                'name' =>   esc_html__( 'Google API Key', 'yith-store-locator' ),
                'type' =>   'title'
            ),

            array( 'type' => 'close' )
        ),

        'gmap-options'  => array(

            'google-maps-api-key' => array(
                'id'      =>    'google-maps-api-key',
                'name'    =>    esc_html__( 'Google Maps API Key', 'yith-store-locator' ),
                'type'    =>    'text',
                'desc'    =>    sprintf( esc_html__( 'You need Google API Key to show a google map in your site. %1$sRead more%2$s', 'yith-store-locator' ),
                            '<a href="https://docs.yithemes.com/yith-store-locator-for-wordpress/google-map/google-map-api-keys/" target="_blank">',
                              '</a>' ),
                'std'     =>    ''
            ),

            array(
                'type'    => 'close'
            )
        ),

        'loader-options'  => array(

            'title'     =>  array(
                'id'        =>  'title',
                'name'      =>  '',
                'html'      =>  '<h2 class="my-title"> ' . esc_html__( "Loader", "yith-store-locator" ) . '</h2>',
                'type'      =>  'html',
                'desc'      =>    ''
            ),

            'loader-type' => array(
                'id'        =>  'loader-type',
                'name'      =>  esc_html__('Set a default loader or custom loader', 'yith-store-locator'),
                'type'      =>  'radio',
                'desc'      =>  'You can choose to use a default or upload a custom loader',
                'options'   =>  array(
                    'default'   =>  esc_html__('Default loader', 'yith-store-locator' ),
                    'custom'    =>  esc_html__( 'Custom loader', 'yith-store-locator' )
                ),
                'std'       =>  'default'
            ),

            'loader-custom-icon' => array(
                'id'        =>  'loader-custom-icon',
                'name'      =>  esc_html__( 'Upload a custom loader', 'yith-store-locator' ),
                'type'      =>  'upload',
                'desc'      =>  esc_html__( 'Upload here your custom loader', 'yith-store-locator' ),
                'deps'      =>  array(
                    'ids'    => 'loader-type',
                    'values' => 'custom',
                    'type'   => 'hide'
                ),
            ),

            'loader-icon'   => array(
                'id'        =>  'loader-icon',
                'name'      =>  esc_html__( 'Default loading icon', 'yith-store-locator' ),
                'type'      =>  'select-images',
                'desc'      =>  esc_html__( 'Choose the loader icon', 'yith-store-locator' ),
                'options'   =>  array(
                    'loader1'   =>  array(
                        'label' =>  esc_html__( 'Loader 1', 'yith-store-locator' ),
                        'image' => YITH_SL_ASSETS_URL . 'images/admin-panel/loader1.svg'
                    ),
                    'loader2'   =>  array(
                        'label' =>  esc_html__( 'Loader 2', 'yith-store-locator' ),
                        'image' => YITH_SL_ASSETS_URL . 'images/admin-panel/loader2.svg'
                    ),
                    'loader3'   =>  array(
                        'label' =>  esc_html__( 'Loader 3', 'yith-store-locator' ),
                        'image' => YITH_SL_ASSETS_URL . 'images/admin-panel/loader3.svg'
                    ),
                    'loader4'   =>  array(
                        'label' =>  esc_html__( 'Loader 4', 'yith-store-locator' ),
                        'image' => YITH_SL_ASSETS_URL . 'images/admin-panel/loader4.svg'
                    ),
                ),
                'deps'      =>  array(
                    'ids'    => 'loader-type',
                    'values' => 'default',
                    'type'   => 'hide'
                ),
                'std'       =>  'loader1'
            ),
            'loader-icon-size' => array(
                'id'        =>  'loader-icon-size',
                'name'      =>  esc_html__( 'Loading icon size', 'yith-store-locator' ),
                'type'      =>  'select',
                'desc'      =>  esc_html__( 'Choose the loader icon size', 'yith-store-locator' ),
                'options'   =>  array(
                   'small'      =>  esc_html__( 'Small', 'yith-store-locator' ),
                   'medium'     =>  esc_html__( 'Medium', 'yith-store-locator' ),
                   'big'        =>  esc_html__( 'Big', 'yith-store-locator' )
                ),
                'std'       =>  'medium',
                'deps'      =>  array(
                    'ids'    => 'loader-type',
                    'values' => 'default',
                    'type'   => 'hide'
                ),
            ),
            'loader-icon-color' => array(
                'id'        =>  'loader-icon-color',
                'name'      =>  esc_html__( 'Loading icon color', 'yith-store-locator' ),
                'type'      =>  'colorpicker',
                'desc'      =>  esc_html__( 'Choose the loader icon color', 'yith-store-locator' ),
                'std'       =>  '#18BCA9',
                'deps'      =>  array(
                    'ids'    => 'loader-type',
                    'values' => 'default',
                    'type'   => 'hide'
                ),
            ),
            'loader-overlay-color' => array(
                'id'        =>  'loader-overlay-color',
                'name'      =>  esc_html__( 'Overlay color', 'yith-store-locator' ),
                'type'      =>  'colorpicker',
                'desc'      =>  esc_html__( 'Choose the overlay color in loading process', 'yith-store-locator' ),
                'std'       =>  '#ffffff',
                'deps'      =>  array(
                    'ids'    => 'loader-type',
                    'values' => 'default',
                    'type'   => 'hide'
                ),
            ),
        ),

        'custom-code'  => array(

            'title'  =>  array(
                'id'        =>  'title',
                'name'      =>  '',
                'html'      =>  '<h2 class="my-title"> ' . esc_html__( "Custom code", "yith-store-locator" ) . '</h2>',
                'type'      =>  'html',
            ),

            'custom-css' => array(
                'id'        =>  'custom-css',
                'name'      =>  esc_html__('Custom CSS', 'yith-store-locator'),
                'type'      =>  'textarea-codemirror',
                'class'     =>   'codemirror',
                'desc'      =>  esc_html__( 'Enter some stylesheet rules if you want to customize some elements','yith-store-locator')
            ),
            'custom-js' => array(
                'id'        =>  'custom-js',
                'name'      =>  esc_html__('Custom JS', 'yith-store-locator'),
                'type'      =>  'textarea-codemirror',
                'class'     =>  'codemirror',
                'desc'      =>  esc_html__( 'Here you can add some javascript if you want.','yith-store-locator')
            )
        ),


    ),

);



return apply_filters( 'yith_sl_panel_general_options', $tab );