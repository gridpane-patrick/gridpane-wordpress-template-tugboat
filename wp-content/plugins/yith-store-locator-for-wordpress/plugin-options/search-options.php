<?php

$tab = array(
    'search' => array(

        'header'    => array(

            array(
                'name'  => esc_html__( 'Search Settings', 'yith-store-locator' ),
                'type'  => 'title'
            ),

            array( 'type' => 'close' )
        ),

        'search-options'  => array(

            'enable-search-bar' => array(
                'id'        =>  'enable-search-bar',
                'name'      =>  esc_html__( 'Show the search box', 'yith-store-locator' ),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__( 'Choose if you want to display or not the search box', 'yith-store-locator' ),
                'std'       =>  'yes'
            ),

            'search-form-colors' => array(
                'id'            => 'search-form-colors',
                'name'          => esc_html__('Search form colors', 'yith-store-locator'),
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'        => 'background',
                        'name'      => esc_html_x( 'Background', 'yith-store-locator' ),
                        'default'       => '#ffffff'
                    ),
                    array(
                        'id'        => 'color',
                        'name'      => esc_html_x( 'text', 'yith-store-locator' ),
                        'default'       => '#9D9D9D'
                    )
                ),
                'desc'              =>  esc_html__( 'Set the background and placeholder colors of the search form', 'yith-store-locator' ),
                'deps'              => array(
                    'ids'           => 'enable-search-bar',
                    'values'        => 'yes',
                    'type'          => 'hide'
                ),
            ),

            'placeholder-search-form' => array(
                'id'        =>  'placeholder-search-form',
                'name'      =>  esc_html__('Enter a text to display inside the search form', 'yith-store-locator'),
                'type'      =>  'text',
                'std'       =>  esc_html__( 'Enter address / city' ),
                'desc'      =>  esc_html__( 'Enter the placeholder text for the search bar', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'enable-search-bar',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),
            'icon-search-bar' => array(
                'id'        =>  'icon-search-bar',
                'name'      =>  esc_html__('Upload icon for search bar', 'yith-store-locator'),
                'type'      =>  'upload',
                'desc'      =>  esc_html__( 'Upload a custom icon to show in the search bar', 'yith-store-locator' ),
                'std'       =>   YITH_SL_ASSETS_URL . 'images/store-locator/search.svg',
                'deps' => array(
                    'ids'    => 'enable-search-bar',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),
            'title-search-bar' => array(
                'id'        =>  'title-search-bar',
                'name'      =>  esc_html__('Enter a title for "search" feature', 'yith-store-locator'),
                'type'      =>  'text',
                'desc'      =>  esc_html__( 'Enter the text to show above the search bar', 'yith-store-locator' ),
                'std'       =>  esc_html__( 'Find our stores','yith-store-locator' ),
                'deps'      =>  array(
                    'ids'    => 'enable-search-bar',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),
            'enable-instant-search' => array(
                'id'        => 'enable-instant-search',
                'name'      => esc_html__('Use instant search', 'yith-store-locator'),
                'type'      => 'onoff',
                'std'       =>  'yes',
                'desc'      =>  esc_html__( 'When enabled, the instant search will load results automatically. If disabled, the user has to click on the "search" button to see the results.', 'yith-store-locator' ),
            ),
            'text-search-button' => array(
                'id'        => 'text-search-button',
                'name'      => esc_html__('Enter text for search button', 'yith-store-locator'),
                'type'      => 'text',
                'std'       =>  esc_html__( 'Search','yith-store-locator' ),
                'desc'      => esc_html__( 'Enter text for search button', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'enable-instant-search',
                    'values' => 'no',
                    'type'  => 'hide'
                ),
            ),
            'color-search-button' => array(
                'id'            =>  'color-search-button',
                'name'          =>  esc_html__('Search button colors', 'yith-store-locator'),
                'type'          =>  'multi-colorpicker',
                'colorpickers'  =>  array(
                    array(
                        'id'        =>  'normal',
                        'name'      =>  esc_html__( 'Text', 'yith-store-locator' ),
                        'default'       =>  '#ffffff'
                    ),
                    array(
                        'id'        =>  'hover',
                        'name'      =>  esc_html__( 'Text hover', 'yith-store-locator'),
                        'default'       =>  '#ffffff'
                    )
                ),
                'desc'          =>  '',
                'deps'          => array(
                    'ids'    => 'enable-instant-search',
                    'values' => 'no',
                    'type'   => 'hide'
                ),
            ),
            'background-color-search-button' => array(
                'id'            => 'background-color-search-button',
                'name'          => '',
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'        =>  'normal',
                        'name'      =>  esc_html__( 'Background', 'yith-store-locator' ),
                        'default'       =>  '#18BCA9'
                    ),
                    array(
                        'id'        =>  'hover',
                        'name'      =>  esc_html__( 'Background hover', 'yith-store-locator' ),
                        'default'       =>  '#008A81'
                    )
                ),
                'desc'          =>  esc_html__( 'Set colors for search button', 'yith-store-locator' ),
                'deps'          => array(
                    'ids'    => 'enable-instant-search',
                    'values' => 'no',
                    'type'   => 'hide'
                ),
            ),
            array(
                'type' => 'close'
            )
        ),

        'geolocation-options'  => array(

            'title'  =>  array(
                'id'      => 'title',
                'name'    => '',
                'html' => '<h2 class="my-title"> ' . esc_html__( "Geolocation settings", "yith-store-locator" ) . '</h2>',
                'type' => 'html',
                'desc' =>   ''
            ),
            'enable-geolocation' => array(
                'id'        =>  'enable-geolocation',
                'name'      =>  esc_html__('Use geolocation', 'yith-store-locator'),
                'type'      =>  'onoff',
                'std'       =>   'yes',
                'desc'      =>   esc_html__( "Choose to enable the geolocation feature to get the user's position automatically.", 'yith-store-locator')
            ),
            'geolocation-style' => array(
                'id'        =>  'geolocation-style',
                'name'      =>  esc_html__('Geolocation style', 'yith-store-locator'),
                'type'      =>  'radio',
                'desc'      =>  esc_html__( 'Select to use a button or text link, for the user to activate their geolocation', 'yith-store-locator' ),
                'options'   =>  array(
                    'text'      =>  esc_html__( 'Only text','yith-store-locator' ),
                    'button'    =>  esc_html__( 'Button','yith-store-locator' )
                ),
                'std'       =>  'button',
                'deps'      =>  array(
                    'ids'    => 'enable-geolocation',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),
            'color-geolocation-button' => array(
                'id' => 'color-geolocation-button',
                'name' => esc_html__('Geolocation button colors', 'yith-store-locator'),
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'        =>  'normal',
                        'name'      =>  esc_html__( 'Text', 'yith-store-locator' ),
                        'default'       =>  '#ffffff'
                    ),
                    array(
                        'id'        =>  'hover',
                        'name'      =>   esc_html__( 'Text hover', 'yith-store-locator' ),
                        'default'       =>  '#ffffff'
                    )
                ),
                'desc'      =>  '',
                'deps' => array(
                    'ids'    => 'geolocation-style',
                    'values' => 'button',
                    'type'   => 'hide'
                ),
            ),
            'background-color-geolocation-button' => array(
                'id'            =>  'background-color-geolocation-button',
                'name'          =>  '',
                'type'          =>  'multi-colorpicker',
                'colorpickers'  =>  array(
                    array(
                        'id'    => 'normal',
                        'name'  =>  esc_html__( 'Background', 'yith-store-locator' ),
                        'default'   => '#3A3A3A'
                    ),
                    array(
                        'id'    => 'hover',
                        'name'  =>  esc_html__( 'Background hover', 'yith-store-locator' ),
                        'default'   => '#0D0D0D'
                    )
                ),
                'desc'          =>  esc_html__( 'Set colors for geolocation button', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'geolocation-style',
                    'values' => 'button',
                    'type'  => 'hide'
                ),
            ),
            'geolocation-text'  =>  array(
                'id'        =>  'geolocation-text',
                'name'      =>  esc_html__('Geolocation text', 'yith-store-locator'),
                'type'      =>  'text',
                'std'       =>   esc_html__( 'Use my position','yith-store-locator' ),
                'desc'      => esc_html__( 'Enter text for geolocation button', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'enable-geolocation',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),
            'icon-geolocation' => array(
                'id'        =>  'icon-geolocation',
                'name'      =>  esc_html__('Default icon for geolocation', 'yith-store-locator'),
                'type'      =>  'upload',
                'desc'      =>  esc_html__( 'Upload an icon to identify the geolocation option', 'yith-store-locator' ),
                'std'       =>  YITH_SL_ASSETS_URL .'images/store-locator/geolocation.svg',
                'deps' => array(
                    'ids'    => 'enable-geolocation',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),
            'enable-autogeolocation' => array(
                'id'        =>  'enable-autogeolocation',
                'name'      =>  esc_html__('Use auto geolocation', 'yith-store-locator'),
                'type'      =>  'onoff',
                'std'       =>  'no',
                'desc'      =>  esc_html__( 'Choose to enable or disable the auto-geolocation. If enabled, the user will get an alert when opening the store locator page.', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'enable-geolocation',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),
            array(
                'type' => 'close'
            )
        ),
        'view-all-stores-options'  => array(

            'title'         =>  array(
                'id'        => 'title',
                'name'      => '',
                'html'      => '<h2 class="my-title"> ' . esc_html__( "'View all stores' settings", "yith-store-locator" ) . '</h2>',
                'type'      => 'html',
                'desc'      =>   ''
            ),
            'enable-view-all-stores' => array(
                'id'        =>  'enable-view-all-stores',
                'name'      =>  esc_html__('Show "View all stores" button', 'yith-store-locator'),
                'type'      =>  'onoff',
                'std'       =>   'yes',
                'desc'      =>  esc_html__( 'Choose if you want to show the "view all stores" button', 'yith-store-locator' )
            ),
            'view-all-stores-style' => array(
                'id'        =>  'view-all-stores-style',
                'name'      =>  esc_html__('"View all stores" style', 'yith-store-locator'),
                'type'      =>  'radio',
                'desc'      =>  esc_html__( 'Choose to show a simple text link or a button to click "view all stores"', 'yith-store-locator' ),
                'options'   =>   array(
                    'text'      =>  esc_html__( 'Only text','yith-store-locator' ),
                    'button'    =>  esc_html__( 'Button','yith-store-locator' )
                ),
                'std'       =>  'button',
                'deps'      => array(
                    'ids'    => 'enable-view-all-stores',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),
            'color-view-all-stores-button' => array(
                'id'            => 'color-view-all-stores-button',
                'name'          => esc_html__('"View all stores" colors', 'yith-store-locator'),
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'    =>  'normal',
                        'name'  =>  esc_html__( 'Text','yith-store-locator' ),
                        'default'   =>  '#3A3A3A'
                    ),
                    array(
                        'id'    =>  'hover',
                        'name'  =>  esc_html__( 'Text hover','yith-store-locator' ),
                        'default'   =>  '#3A3A3A'
                    )
                ),
                'desc'      =>  '',
                'deps'      => array(
                    'ids'    => 'view-all-stores-style',
                    'values' => 'button',
                    'type'   => 'hide'
                ),
            ),
            'background-color-view-all-stores-button' => array(
                'id'            =>  'background-color-view-all-stores-button',
                'name'          =>  '',
                'type'          =>  'multi-colorpicker',
                'colorpickers'  =>  array(
                    array(
                        'id'    =>  'normal',
                        'name'  =>  esc_html__( 'Background','yith-store-locator' ),
                        'default'   =>  '#ffffff'
                    ),
                    array(
                        'id'    =>  'hover',
                        'name'  =>  esc_html__( 'Background hover','yith-store-locator' ),
                        'default'   =>  '#ffffff'
                    )
                ),
                'desc'      =>  esc_html__( 'Set colors for "View all stores" button', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'view-all-stores-style',
                    'values' => 'button',
                    'type'  => 'hide'
                ),
            ),
            'view-all-stores-text'  =>  array(
                'id'        =>  'view-all-stores-text',
                'name'      =>  esc_html__('Enter a text for "View all stores" button', 'yith-store-locator'),
                'type'      =>  'text',
                'std'       =>  esc_html__( 'View all stores','yith-store-locator' ),
                'desc'      =>  esc_html__( 'Enter text for "view all stores" option','yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'view-all-stores-style',
                    'values' => 'button',
                    'type'   => 'hide'
                ),
            ),
            'all-stores-icon' => array(
                'id'        =>  'all-stores-icon',
                'name'      =>  esc_html__('Default icon for "View all stores" button', 'yith-store-locator'),
                'type'      =>  'upload',
                'desc'      =>  esc_html__( 'Upload a custom icon for "All stores" button', 'yith-store-locator' ),
                'std'       =>   YITH_SL_ASSETS_URL .'images/store-locator/all-stores.svg',
                'deps' => array(
                    'ids'    => 'enable-view-all-stores',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),
        ),
        'search-filter-options'  => array(

            'title'  =>  array(
                'id'      => 'title',
                'name'    => '',
                'html' => '<h2 class="my-title"> ' . esc_html__( "Search results", "yith-store-locator" ) . '</h2>',
                'type' => 'html',
                'desc' =>   ''
            ),
            'results-title' => array(
                'id'        =>  'results-title',
                'name'      =>  esc_html__('Enter a text for "Results" title', 'yith-store-locator'),
                'type'      =>  'text',
                'desc'      =>  esc_html__( 'Enter an optional "results" title to show before the stores list','yith-store-locator' ),
                'std'       =>   esc_html__( 'Results','yith-store-locator' ),
            ),
            'no-results-text' => array(
                'id'        =>  'no-results-text',
                'name'      =>  esc_html__('Enter a text for "no results" title', 'yith-store-locator'),
                'type'      =>  'textarea',
                'desc'      =>  esc_html__( 'Enter a custom text when no results are displayed', 'yith-store-locator' ),
                'std'       =>  esc_html__( "Your search did not show any results, please try with different terms.",'yith-store-locator' ),
            )
        ),
    )
);



return apply_filters( 'yith_sl_panel_search_options', $tab );