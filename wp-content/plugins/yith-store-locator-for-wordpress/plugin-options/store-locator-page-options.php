<?php
// Exit if accessed directly
!defined('YITH_SL') && exit();

$tab = array(
    'store-locator-page' => array(
        'page-layout-options' => array(
            'general-options' => array(
                'name'      => esc_html__('Store locator page layout', 'yith-store-locator'),
                'type'      => 'title',
                'desc'      => '',
            ),

            'search-bar-filters-position'  => array(
                'id'        =>  'search-bar-filters-position',
                'name'      =>  esc_html__('Search bar and filters position', 'yith-store-locator'),
                'type'      =>  'radio',
                'options'   =>  array(
                    'above-map'             =>  esc_html__( 'Above the map', 'yith-store-locator' ),
                    'beside-map'    =>  esc_html__( 'Beside the map', 'yith-store-locator' )
                ),
                'std'       =>  'beside-map',
                'desc'      =>  esc_html__('Choose the default position of the search bar and filters', 'yith-store-locator'),
            ),

            'full-width-layout' => array(
                'id'        =>  'full-width-layout',
                'name'      =>  esc_html__('Full width page', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Enable to set a full width layout for store locator page', 'yith-store-locator'),
                'std'       =>  'no',
            ),

            'side-padding-full-width' => array(
                'id'        =>  'side-padding-full-width',
                'name'      =>  esc_html__('Side margin in full width layout (px)', 'yith-store-locator'),
                'type'      =>  'text-array',
                'fields' => array(
                    'left'   =>  esc_html__( 'Left', 'yith-store-locator'),
                    'right'  =>  esc_html__( 'Right', 'yith-store-locator'),
                ),
                'desc'      =>  esc_html__('Set the side margins for the content in full-width layout', 'yith-store-locator'),
                'std'       =>  'no',
                'deps' => array(
                    'ids'    => 'full-width-layout',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'map-position'  => array(
                'id'        =>  'map-position',
                'name'      =>  esc_html__('Map position', 'yith-store-locator'),
                'type'      =>  'select-images',
                'options' => array(
                    'map-right' => array(
                        'label' => esc_html__( 'Map right', 'yith-store-locator' ),
                        'image' => YITH_SL_ASSETS_URL . 'images/admin-panel/layout-map-right.svg'
                    ),
                    'map-left' => array(
                        'label' => esc_html__( 'Map left', 'yith-store-locator' ),
                        'image' => YITH_SL_ASSETS_URL . 'images/admin-panel/layout-map-left.svg'
                    )
                ),
                'std'       =>  'map-right',
                'desc'      =>  esc_html__('Choose the position of map and search results in store locator page', 'yith-store-locator'),
            ),

            'map-width'  => array(
                'id'        =>  'map-width',
                'name'      =>  esc_html__('Map width (in %)', 'yith-store-locator'),
                'type'      =>  'number',
                'std'       =>  65,
                'desc'      =>  esc_html__('Choose the width of the map. Default is 65. Width of search results list will be calculated automatically.', 'yith-store-locator'),
            ),

            'map-height'  => array(
                'id'        =>  'map-height',
                'name'      =>  esc_html__('Map height (in pixel)', 'yith-store-locator'),
                'type'      =>  'text',
                'desc'      =>  esc_html__('Set the height of the map', 'yith-store-locator'),
                'std'       =>  500,
            ),

            'results-columns'  => array(
                'id'        =>  'results-columns',
                'name'      =>  esc_html__('Show results in', 'yith-store-locator'),
                'type'      =>  'radio',
                'options'   =>  array(
                    'one'    =>  esc_html__( 'One column', 'yith-store-locator' ),
                    'two'    =>  esc_html__( 'Two columns', 'yith-store-locator' )
                ),
                'std'       =>  'one',
                'desc'      =>  esc_html__('Choose if show results in a single column or in two columns', 'yith-store-locator'),
            ),

            'color-shades'  => array(
                'id'                => 'color-shades',
                'name'              =>  esc_html__('Main Color shade', 'yith-store-locator'),
                'desc'              =>  esc_html__('Choose main color shade for store locator page', 'yith-store-locator'),
                'type'              =>  'multi-colorpicker',
                'colorpickers'      => array(
                    array(
                        'id'        =>  'normal',
                        'name'      =>  '',
                        'default'   =>  '#18BCA9'
                    )
                ),
            ),

            'scrollbar-colors'  => array(
                'id'                => 'scrollbar-colors',
                'name'              =>  esc_html__('Scrollbar colors', 'yith-store-locator'),
                'desc'              =>  esc_html__('Choose colors for scrollbar used to navigate in results', 'yith-store-locator'),
                'type'              => 'multi-colorpicker',
                'colorpickers'      => array(
                    array(
                        'id'        =>  'main',
                        'name'      =>  esc_html__( 'Main scrollbar color', 'yith-store-locator' ),
                        'default'   =>  '#d0d0d0'
                    ),
                    array(
                        'id'        =>  'thumb',
                        'name'      =>  esc_html__( 'Scrollbar thumb color', 'yith-store-locator' ),
                        'default'   => '#18BCA9'
                    )
                ),
            ),

            'scrollbar-width'  => array(
                'id'                => 'scrollbar-width',
                'name'              =>  esc_html__('Scrollbar width', 'yith-store-locator'),
                'desc'              =>  esc_html__('Set width (in pixel) of scrollbar for results section', 'yith-store-locator'),
                'type'              => 'number',
                'std'               =>  '5'
            ),

            array(
                'type' => 'close'
            )
        ),
        'map-options' => array(
            'title'         =>   array(
                'id'        =>  'title',
                'name'      =>  '',
                'html'      =>  '<h2 class="my-title"> ' . __( "Map Settings", "yith-store-locator" ) . '</h2>',
                'type'      =>  'html',
            ),

            'enable-map' => array(
                'id'        =>  'enable-map',
                'name'      =>  esc_html__('Show the map', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or not the map', 'yith-store-locator'),
                'std'       =>  'yes',
            ),

            'map-default-position'  => array(
                'id'        =>  'map-default-position',
                'name'      =>  esc_html__('Default map position', 'yith-store-locator'),
                'type'      =>  'text-array',
                'fields'    =>  array(
                    'latitude'  => esc_html__( 'Latitude','yith-store-locator' ),
                    'longitude' => esc_html__( 'Longitude','yith-store-locator' ),
                ),
                'desc'      =>  esc_html__('Choose the default position for store locator map', 'yith-store-locator'),
                'deps'      => array(
                    'ids'    => 'enable-map',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'map-default-type'  => array(
                'id'        =>  'map-default-type',
                'name'      =>  esc_html__('Default map type', 'yith-store-locator'),
                'type'      =>  'select',
                'options'    => array(
                    'roadmap'   =>  'Roadmap',
                    'satellite' =>  'Satellite',
                ),
                'desc'      =>  esc_html__('Choose the default map type', 'yith-store-locator'),
                'std'       =>  'roadmap',
                'deps' => array(
                    'ids'    => 'enable-map',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'map-zoom'  => array(
                'id'        =>  'map-default-zoom',
                'name'      =>  esc_html__('Default map zoom', 'yith-store-locator'),
                'type'      =>  'slider',
                'desc'      =>   esc_html__('Choose the default zoom for store locator map', 'yith-store-locator'),
                'option'    =>  array(
                    'min' => 1,
                    'max' => 20,
                ),
                'step'      => 1,
                'deps' => array(
                    'ids'    => 'enable-map',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
                'std'       => '10',
            ),

            'map-scroll-type'  => array(
                'id'        =>  'map-scroll-type',
                'name'      =>  esc_html__('Map scroll type', 'yith-store-locator'),
                'type'      =>  'radio',
                'desc'      =>   sprintf ( esc_html__('Choose how to scroll the map of your store locator', 'yith-store-locator'), '<br>', '<b>', '</b>'),
                'options'    =>  array(
                    'cooperative'   =>  esc_html__( 'Mouse wheel+ctrl', 'yith-store-locator' ),
                    'greedy'        =>  esc_html__( 'Mouse wheel', 'yith-store-locator' )
                ),
                'deps' => array(
                    'ids'    => 'enable-map',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
                'std'       => 'cooperative',
            ),


            'map-icon-user-position'  => array(
                'id'        =>  'map-icon-user-position',
                'name'      =>  esc_html__('Default icon for user position in the map', 'yith-store-locator'),
                'type'      =>  'upload',
                'desc'      =>   esc_html__('Upload a custom icon to identify the user position in map', 'yith-store-locator'),
                'std'       =>   YITH_SL_ASSETS_URL .'images/store-locator/user-position.svg',
                'deps' => array(
                    'ids'    => 'enable-map',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'map-icon-marker'  => array(
                'id'        =>  'map-icon-marker',
                'name'      =>  esc_html__('Default icon for store in the map', 'yith-store-locator'),
                'type'      =>  'upload',
                'desc'      =>  sprintf( esc_html__('Upload a custom icon to identify stores in map%s In each store you can override with a different icon.', 'yith-store-locator'), '<br>'),
                'std'       =>  YITH_SL_ASSETS_URL .'images/store-locator/marker.svg',
                'deps' => array(
                    'ids'    => 'enable-map',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),

            'map-style'  => array(
                'id'        =>  'map-style',
                'name'      =>  esc_html__('Map style', 'yith-store-locator'),
                'type'      =>  'textarea-codemirror',
                'desc'    =>    sprintf( esc_html__( 'Paste here the JSON code provided by Google for your custom style. %1$sClick here%2$s to create your favourite one', 'yith-store-locator' ),
                    '<a href="//mapstyle.withgoogle.com/" target="_blank" rel="noopener">',
                    '</a>' ),
                'std'       =>  '',
                'deps' => array(
                    'ids'    => 'enable-map',
                    'values' => 'yes',
                    'type'  => 'hide'
                ),
            ),

            array(
                'type' => 'close'
            )
        ),

        'pin-info-options'  => array(

            'title'         =>   array(
                'id'        =>  'title',
                'name'      =>  '',
                'html'      =>  '<h2 class="my-title"> ' . __( "Pin Info Modal Settings", "yith-store-locator" ) . '</h2>',
                'type'      =>  'html',
            ),

            'enable-pin-modal' => array(
                'id'        =>  'enable-pin-modal',
                'name'      =>  esc_html__('Show the info modals', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or not the modal within info on the map', 'yith-store-locator'),
                'std'       =>  'yes',
            ),

            'pin-modal-trigger-event' => array(
                'id'        =>  'pin-modal-trigger-event',
                'name'      =>  esc_html__('Open the modal at', 'yith-store-locator'),
                'type'      =>  'radio',
                'desc'      =>  '',
                'options'   =>   array(
                    'click'      =>  esc_html_x( 'Click on pin', 'Event on which trigger the info box modal opening', 'yith-store-locator' ),
                    'mouseover'  =>  esc_html_x( 'Mouse hover', 'Event on which trigger the info box modal opening', 'yith-store-locator' )
                ),
                'std'       => 'mouseover',
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'pin-modal-width' => array(
                'id'        => 'pin-modal-width',
                'name'      => esc_html__('Modal width', 'yith-store-locator'),
                'type'      => 'text',
                'desc'      => sprintf( esc_html__( 'Set the width (in px) of the modal window opened on the map%s Height will be dynamic and based on modal content.', 'yith-store-locator' ), '<br>' ),
                'std'       => '400',
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'pin-modal-colors' => array(
                'id'            =>  'pin-modal-colors',
                'name'          =>  esc_html__('Modal colors', 'yith-store-locator'),
                'type'          =>  'multi-colorpicker',
                'colorpickers'  =>  array(
                    array(
                        'id'        =>  'background',
                        'name'      =>  esc_html__( 'Background', 'yith-store-locator' ),
                        'default'   =>  '#ffffff'
                    ),
                    array(
                        'id'        =>  'color',
                        'name'      =>  esc_html__( 'Text', 'yith-store-locator'),
                        'default'   =>  '#000000'
                    )
                ),
                'desc'          =>  esc_html__( 'Set the background and text colors of the pin modal', 'yith-store-locator' ),
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'pin-border-radius' => array(
                'id'        =>  'pin-border-radius',
                'name'      =>  esc_html__('Border radius', 'yith-store-locator'),
                'type'      =>  'slider',
                'desc'      =>  esc_html__('Set 0 if you want a rectangular box, 10 for a circle box.', 'yith-store-locator'),
                'option'    => array(
                    'min' => 0,
                    'max' => 100,
                ),
                'step'      =>  1,
                'std'       =>  0,
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'pin-modal-show-name' => array(
                'id'        =>  'pin-modal-show-name',
                'name'      =>  esc_html__('Show store name', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or not the store name within the pin modal', 'yith-store-locator'),
                'std'       =>  'yes',
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'pin-modal-show-description' => array(
                'id'        =>  'pin-modal-show-description',
                'name'      =>  esc_html__('Show store description', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or not the store description within the pin modal', 'yith-store-locator'),
                'std'       =>  'no',
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'pin-modal-show-address' => array(
                'id'        =>  'pin-modal-show-address',
                'name'      =>  esc_html__('Show store address', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or not the store address within the pin modal', 'yith-store-locator'),
                'std'       =>  'yes',
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'pin-modal-show-contact-info' => array(
                'id'        =>  'pin-modal-show-contact-info',
                'name'      =>  esc_html__('Show store contact info', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or not the store contact info within the pin modal', 'yith-store-locator'),
                'std'       =>  'no',
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'pin-modal-show-contact-get-direction' => array(
                'id'        =>  'pin-modal-show-contact-get-direction',
                'name'      =>  esc_html__('Show get direction link', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or not the store direction link within the pin modal', 'yith-store-locator'),
                'std'       =>  'yes',
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'pin-modal-show-contact-store' => array(
                'id'        =>  'pin-modal-show-contact-store',
                'name'      =>  esc_html__('Show contact link', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or not the store contact link within the pin modal', 'yith-store-locator'),
                'std'       =>  'yes',
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'pin-modal-show-visit-website' => array(
                'id'        =>  'pin-modal-show-visit-website',
                'name'      =>  esc_html__('Show visit website link', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to display or not the store visit website link within the pin modal', 'yith-store-locator'),
                'std'       =>  'no',
                'deps' => array(
                    'ids'    => 'enable-pin-modal',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),
            array(
                'type' => 'close'
            )
        ),
        'radius-circle-options' => array(

            'title'         =>   array(
                'id'        =>  'title',
                'name'      =>  '',
                'html'      =>  '<h2 class="my-title"> ' . __( "Radius circle", "yith-store-locator" ) . '</h2>',
                'type'      =>  'html',
            ),

            'enable-circle' => array(
                'id'        =>  'enable-circle',
                'name'      =>  esc_html__('Show radius circle', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to show the circle to highlight the selected radius on the map', 'yith-store-locator'),
                'std'       =>  'yes',
            ),

            'circle-colors' => array(
                'id'            => 'circle-colors',
                'name'          => esc_html__('Circle colors', 'yith-store-locator'),
                'type'          => 'multi-colorpicker',
                'colorpickers'  => array(
                    array(
                        'id'        => 'background',
                        'name'      => esc_html_x( 'Background', 'yith-store-locator' ),
                        'default'       => '#18BCA9'
                    ),
                    array(
                        'id'        => 'border',
                        'name'      => esc_html_x( 'Border', 'yith-store-locator' ),
                        'default'       => '#18BCA9'
                    )
                ),
                'desc'              =>  esc_html__( 'Set the background and border colors of the radius-circle', 'yith-store-locator' ),
                'deps'              => array(
                    'ids'           => 'enable-circle',
                    'values'        => 'yes',
                    'type'          => 'hide'
                ),
            ),

            'circle-border-weigth'  => array(
                'id'        =>  'circle-border-weigth',
                'name'      =>  esc_html__('Circle border weight', 'yith-store-locator'),
                'type'      =>  'number',
                'desc'      =>  esc_html__('Choose the border weight of the radius-circle', 'yith-store-locator'),
                'std'       =>  '2',
                'deps'      => array(
                    'ids'    => 'enable-circle',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            array(
                'type' => 'close'
            )
        ),

        'filters-options'   =>  array(

            'title'         =>   array(
                'id'        =>  'title',
                'name'      =>  '',
                'html'      =>  '<h2 class="my-title"> ' . __( "Filters settings", "yith-store-locator" ) . '</h2>',
                'type'      =>  'html',
            ),

            'enable-filter-radius' => array(
                'id'        =>  'enable-filter-radius',
                'name'      =>  esc_html__('Show radius filter', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to show the radius filter', 'yith-store-locator'),
                'std'       =>  'yes',
            ),

            'filter-radius-distance-unit'  => array(
                'id'        =>  'filter-radius-distance-unit',
                'name'      =>  esc_html__('Distance unit', 'yith-store-locator'),
                'type'      =>  'select',
                'options'   =>  array(
                    'km'    =>   esc_html__( 'KM', 'yith-store-locator' ),
                    'miles' =>   esc_html__( 'Miles', 'yith-store-locator' ),
                ),
                'desc'      =>  esc_html__('Choose the distance unit to use for map settings', 'yith-store-locator'),
                'std'       =>  'km',
                'deps' => array(
                    'ids'    => 'enable-filter-radius',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),
            'enable-filters' => array(
                'id'        =>  'enable-filters',
                'name'      =>  esc_html__('Show filters', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to show the filters', 'yith-store-locator'),
                'std'       =>  'yes',
            ),

            'filters-display-mode' => array(
                'id'        =>  'filters-display-mode',
                'name'      =>  esc_html__('Filters display mode', 'yith-store-locator'),
                'type'      =>  'radio',
                'desc'      =>  esc_html__('Choose how to to display the filters in store locator page', 'yith-store-locator'),
                'options'   =>  array(
                    'opened'    =>  esc_html__( 'Opened in the page', 'yith-store-locator' ),
                    'dropdown'  =>  esc_html__( 'Dropdown style', 'yith-store-locator' )
                ),
                'std'       =>  'opened',
                'deps'      => array(
                    'ids'    => 'enable-filters',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'show-filters-with-results' => array(
                'id'        =>  'show-filters-with-results',
                'name'      =>  esc_html__('After the search', 'yith-store-locator'),
                'type'      =>  'radio',
                'desc'      =>  esc_html__('Choose if you want to show the section "Filters" when results are showed', 'yith-store-locator'),
                'options'   =>  array(
                    'yes'    =>  esc_html__( 'Keep filters opened in the page', 'yith-store-locator' ),
                    'no'    =>  esc_html__( 'Hide filters and show a link in order to open them again', 'yith-store-locator' )
                ),
                'std'       =>  'yes',
                'deps'      => array(
                    'ids'    => 'enable-filters',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'enable-active-filters-text' => array(
                'id'        =>  'enable-active-filters-text',
                'name'      =>  esc_html__('Show "active filters" text', 'yith-store-locator'),
                'type'      =>  'onoff',
                'desc'      =>  esc_html__('Choose if you want to show the active filters after each search', 'yith-store-locator'),
                'std'       =>  'yes',
                'deps'      => array(
                    'ids'    => 'enable-filters',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),

            'active-filters-text' => array(
                'id'        =>  'active-filters-text',
                'name'      =>  esc_html__('Enter a text for "active filters" text', 'yith-store-locator'),
                'type'      =>  'text',
                'desc'      =>  esc_html__('Enter a text to identify the active filters', 'yith-store-locator'),
                'std'       =>  esc_html__( 'Active filters:', 'yith-store-locator' ),
                'deps'      => array(
                    'ids'    => 'enable-filters',
                    'values' => 'yes',
                    'type'   => 'hide'
                ),
            ),
        )
    )
);

return apply_filters('yith_sl_panel_store_locator_page_options', $tab);



