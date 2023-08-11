<?php

$width_search_bar                       = yith_sl_get_option( 'search-bar-width',200 );
$background_search_form                 = yith_sl_get_option( 'search-form-colors', array( 'background' => '#ffffff' ) )['background'];
$color_placeholder                      = yith_sl_get_option( 'search-form-colors', array( 'color' => '#9D9D9D' ) )['color'];

$search_background_color_normal         = yith_sl_get_option( 'background-color-search-button', array( 'normal' => '#18BCA9' ) )['normal'];
$search_background_color_hover          = yith_sl_get_option( 'background-color-search-button', array( 'hover' => '#008A81' ) )['hover'];
$search_color_normal                    = yith_sl_get_option( 'color-search-button', array( 'normal' => '#ffffff' ) )['normal'];
$search_color_hover                     = yith_sl_get_option( 'color-search-button', array( 'hover' => '#ffffff' ) )['hover'];

$geolocation_background_normal          = yith_sl_get_option( 'background-color-geolocation-button', array( 'normal' => '#3A3A3A' ) )['normal'];
$geolocation_background_hover           = yith_sl_get_option( 'background-color-geolocation-button', array( 'hover' => '#0D0D0D' ) )['hover'];
$geolocation_color_normal               = yith_sl_get_option( 'color-geolocation-button', array( 'normal' => '#ffffff' ) )['normal'];
$geolocation_color_hover                = yith_sl_get_option( 'color-geolocation-button', array( 'hover' => '#ffffff' ) )['hover'];
$geolocation_icon                       = yith_sl_get_option( 'icon-geolocation', YITH_SL_ASSETS_URL . 'images/store-locator/geolocation.svg' );

$all_stores_background_normal           = yith_sl_get_option( 'background-color-view-all-stores-button', array( 'normal' => '#ffffff' ) )['normal'];
$all_stores_background_hover            = yith_sl_get_option( 'background-color-view-all-stores-button', array( 'hover' => '#ffffff' ) )['hover'];
$all_stores_color_normal                = yith_sl_get_option( 'color-view-all-stores-button', array( 'normal' => '#3A3A3A' ) )['normal'];
$all_stores_color_hover                 = yith_sl_get_option( 'color-view-all-stores-button', array( 'hover' => '#3A3A3A' ) )['hover'];
$all_stores_icon                        = yith_sl_get_option( 'all-stores-icon', YITH_SL_ASSETS_URL . 'images/store-locator/all-stores.svg' );

$view_all_color_normal                  = yith_sl_get_option( 'view-all-color', array( 'normal' => '#18BCA9' ) )['normal'];
$view_all_color_hover                   = yith_sl_get_option( 'view-all-color', array( 'hover' => '#008a81' ) )['hover'];

$featured_store_color                   = yith_sl_get_option( 'stores-list-featured-colors', array( 'label-color' => '#ffffff' ) )['label-color'];
$featured_store_label_background_color  = yith_sl_get_option( 'stores-list-featured-colors', array( 'label-background' => '#18BCA9' ) )['label-background'];
$featured_store_background_color        = yith_sl_get_option( 'stores-list-featured-colors', array( 'wrapper-background' => '#F4F4F4' ) )['wrapper-background'];

$get_direction_button_background_normal = yith_sl_get_option( 'stores-list-background-color-get-direction-button', array( 'normal' => '#18BCA9' ) )['normal'];
$get_direction_button_background_hover  = yith_sl_get_option( 'stores-list-background-color-get-direction-button', array( 'hover' => '#008a81' ) )['hover'];
$get_direction_button_color_normal      = yith_sl_get_option( 'stores-list-color-get-direction-button', array( 'normal' => '#ffffff' ) )['normal'];
$get_direction_button_color_hover       = yith_sl_get_option( 'stores-list-color-get-direction-button', array( 'hover' => '#ffffff' ) )['hover'];
$get_direction_link_color_normal        = yith_sl_get_option( 'stores-list-color-get-direction-link', array( 'normal' => '#18BCA9' ) )['normal'];
$get_direction_link_color_hover         = yith_sl_get_option( 'stores-list-color-get-direction-link', array( 'hover' => '#008a81' ) )['hover'];

$contact_store_button_background_normal = yith_sl_get_option( 'stores-list-background-color-contact-store-button', array( 'normal' => '#18BCA9' ) )['normal'];
$contact_store_button_background_hover  = yith_sl_get_option( 'stores-list-background-color-contact-store-button', array( 'hover' => '#008a81' ) )['hover'];
$contact_store_button_color_normal      = yith_sl_get_option( 'stores-list-color-contact-store-button', array( 'normal' => '#ffffff' ) )['normal'];
$contact_store_button_color_hover       = yith_sl_get_option( 'stores-list-color-contact-store-button', array( 'hover' => '#ffffff' ) )['hover'];
$contact_store_link_color_normal        = yith_sl_get_option( 'stores-list-color-contact-store-link', array( 'normal' => '#18BCA9' ) )['normal'];
$contact_store_link_color_hover         = yith_sl_get_option( 'stores-list-color-contact-store-link', array( 'hover' => '#008a81' ) )['hover'];

$view_website_button_background_normal  = yith_sl_get_option( 'stores-list-background-color-visit-website-button', array( 'normal' => '#18BCA9' ) )['normal'];
$view_website_button_background_hover   = yith_sl_get_option( 'stores-list-background-color-visit-website-button', array( 'hover' => '#008a81' ) )['hover'];
$view_website_button_color_normal       = yith_sl_get_option( 'stores-list-color-visit-website-button', array( 'normal' => '#ffffff' ) )['normal'];
$view_website_button_color_hover        = yith_sl_get_option( 'stores-list-color-visit-website-button', array( 'hover' => '#ffffff' ) )['hover'];
$view_website_link_color_normal         = yith_sl_get_option( 'stores-list-color-visit-website-link', array( 'normal' => '#18BCA9' ) )['normal'];
$view_website_link_color_hover          = yith_sl_get_option( 'stores-list-color-visit-website-link', array( 'hover' => '#008a81' ) )['hover'];

$pin_modal_width                        = yith_sl_get_option( 'pin-modal-width', '400' );
$pin_modal_background_color             = yith_sl_get_option( 'pin-modal-colors', array( 'background' => '#ffffff' ) )['background'];
$pin_modal_color                        = yith_sl_get_option( 'pin-modal-colors', array( 'color' => '#000000' ) )['color'];
$pin_border_radius                      = yith_sl_get_option( 'pin-border-radius','0' );
$pin_modal_padding                      = $pin_border_radius > 50 ? 35 : 16;
$close_button_right                     = intval( $pin_border_radius / 4 );

$map_width                              = yith_sl_get_option( 'map-width','65' );
$map_height                             = yith_sl_get_option( 'map-height','500' );
$section_results_width                  = $map_width == 100 ? 100 : ( 100 - $map_width );
$page_layout                            = yith_sl_get_option( 'map-position','map-right' );
$flex_order_map                         = $page_layout == 'map-right' ? 2 : 1;
$flex_order_results                     = $page_layout == 'map-right' ? 1 : 2;

$results_columns                        = yith_sl_get_option( 'results-columns', 'one' );

$scrollbar_bg_color                     = yith_sl_get_option( 'scrollbar-colors', array( 'main' => '#d0d0d0' ) )['main'];
$scrollbar_bg_color_thumb               = yith_sl_get_option( 'scrollbar-colors', array( 'thumb' => '#18BCA9' ) )['thumb'];
$scrollbar_width                        = yith_sl_get_option( 'scrollbar-width','5' );

$loader_overlay_color                   = yith_sl_get_option( 'loader-overlay-color', 'rgba(255,255,255,0.7)' );
$loader_size                            = yith_sl_get_loader_size() / 2;

$results_hover_color                    = yith_sl_get_option( 'result-hover-color', '#E7F5F5' );

$color_shade_normal                     = defined( 'YITH_PROTEO_VERSION' ) ? get_theme_mod( 'yith_proteo_main_color_shade', '#18BCA9' ) :  yith_sl_get_option( 'color-shades', array( 'normal' => '#18BCA9' ) )['normal'];
//$color_shade_hover                      = defined( 'YITH_PROTEO_VERSION' ) ? get_theme_mod( 'yith_proteo_main_color_shade', '#008a81' ) :  yith_sl_get_option( 'color-shades', array( 'hover' => '#008a81' ) )['hover'];


$style = "
    #yith-sl-main-filters-container.layout-dropdown .wrapper-filter.active .open-dropdown, 
    #yith-sl-main-filters-container.layout-dropdown .wrapper-filter.selected .open-dropdown{
        border-color: {$color_shade_normal};
        background-color: {$color_shade_normal}
    }
    
    #wrapper-active-filters .wrapper-terms li{
        border-color: {$color_shade_normal};
        color: {$color_shade_normal}
    }
    
    #yith-sl-main-filters-container .wrapper-options li:hover label,
    #yith-sl-main-filters-container.layout-dropdown .wrapper-filter .open-dropdown:hover{
        color: {$color_shade_normal}
    }
    
    #yith-store-locator select:hover, #yith-sl-main-filters-container .wrapper-options li:hover .checkboxbutton:before,
    #yith-sl-main-filters-container.layout-dropdown .wrapper-filter .open-dropdown:hover,
    #yith-sl-show-all-stores:hover{
        border-color: {$color_shade_normal};
    }
    
    .yith-sl-pin-modal .store-name span:first-child,
    #yith-sl-reset-all-filters
    {
        color: {$color_shade_normal};
    }
    
    #yith-sl-results .no-image,
    #yith-sl-main-filters-container .wrapper-options li .checkboxbutton.checked:before
    {
        background-color: {$color_shade_normal}
    }

    #yith-sl-wrap-loader{
         background-color: $loader_overlay_color;
    }
    
    #yith-sl-loader{
        margin-top:  -{$loader_size}px!important;
        margin-left: -{$loader_size}px!important;
    }


    #yith-sl-section-map{
        width: {$map_width}%;
        order: {$flex_order_map}; 
    }
    
    #yith-sl-section-results{
        width: {$section_results_width}%;
        order: {$flex_order_results};
    }
    
    .wrapper-main-sections{
        height: {$map_height}px
    }


    #yith-sl-wrap-search-bar{
        background-color: {$background_search_form};
    }
        
    #yith-sl-wrap-search-bar input::-webkit-input-placeholder { 
      color: {$color_placeholder};
    }
    #yith-sl-wrap-search-bar input::-moz-placeholder { /* Firefox 19+ */
      color: {$color_placeholder};
    }
    #yith-sl-wrap-search-bar input:-ms-input-placeholder { /* IE 10+ */
      color: {$color_placeholder};
    }
    #yith-sl-wrap-search-bar input:-moz-placeholder { /* Firefox 18- */
      color: {$color_placeholder};
    }
    #yith-sl-search-button{
      background-color: {$search_background_color_normal};
      color: {$search_color_normal};
    }
    #yith-sl-search-button:hover{
      background-color: {$search_background_color_hover};
      color: {$search_color_hover};
    }
    button#yith-sl-geolocation{
      background-color: {$geolocation_background_normal};
      color: {$geolocation_color_normal};
    }
    button#yith-sl-geolocation:hover{
      background-color: {$geolocation_background_hover};
      color: {$geolocation_color_hover};
    }
    button#yith-sl-geolocation {
        background-image: url('{$geolocation_icon}');
    }
    button#yith-sl-show-all-stores{
        background-color: {$all_stores_background_normal};
        color: {$all_stores_color_normal};
        background-image: url('{$all_stores_icon}');
      
    }
    button#yith-sl-show-all-stores:hover{
        background-color: {$all_stores_background_hover};
        color: {$all_stores_color_hover};
    }
    
    #yith-sl-view-all{    
        color: {$view_all_color_normal};
    }
    
    #yith-sl-view-all:hover{    
        color: {$view_all_color_hover};
    }
    
    #yith-sl-section-results::-webkit-scrollbar,
    .wrapper-options ul::-webkit-scrollbar{
        width: {$scrollbar_width}px;
        background-color: {$scrollbar_bg_color};
        direction: rtl;
    }
    
    #yith-sl-section-results::-webkit-scrollbar-thumb,
    .wrapper-options ul::-webkit-scrollbar-thumb {
        background-color: {$scrollbar_bg_color_thumb};
    }
    
    #yith-sl-results .featured-store,
    .yith-sl-pin-modal .featured-store{
        color: {$featured_store_color};
        background-color: {$featured_store_label_background_color};
    }
    
    #yith-sl-results .wrap-store-details.featured,
    .yith-sl-pin-modal .featured{
        background-color: {$featured_store_background_color};
    }
    
    #yith-sl-results .get-direction.link,
    .yith-sl-pin-modal .get-direction.link{
        color: {$get_direction_link_color_normal};
    }
    
    #yith-sl-results .get-direction.link:hover,
    .yith-sl-pin-modal .get-direction.link:hover{
        color: {$get_direction_link_color_hover};
    }
    
    #yith-sl-results .get-direction.button,
    .yith-sl-pin-modal .get-direction.button{
        background-color: {$get_direction_button_background_normal};
        color: {$get_direction_button_color_normal};
    }
    
    #yith-sl-results .get-direction.button:hover,
    ..yith-sl-pin-modal .get-direction.button:hover{
        background-color: {$get_direction_button_background_hover};
        color: {$get_direction_button_color_hover};
    }
    
    #yith-sl-results .contact-store.link,
    .yith-sl-pin-modal .contact-store.link{
        color: {$contact_store_link_color_normal};
    }
    
    #yith-sl-results .contact-store.link:hover,
    .yith-sl-pin-modal .contact-store.link:hover{
        color: {$contact_store_link_color_hover};
    }
    
    #yith-sl-results .contact-store.button,
    .yith-sl-pin-modal .contact-store.button{
        background-color: {$contact_store_button_background_normal};
        color: {$contact_store_button_color_normal};
    }
    
    #yith-sl-results .contact-store.button:hover,
    .yith-sl-pin-modal .contact-store.button:hover{
        background-color: {$contact_store_button_background_hover};
        color: {$contact_store_button_color_hover};
    }
    
    #yith-sl-results .view-website.button,
    .yith-sl-pin-modal .view-website.button{
        background-color: {$view_website_button_background_normal};
        color: {$view_website_button_color_normal};
    }
    
    #yith-sl-results .view-website.button:hover,
    .yith-sl-pin-modal .view-website.button:hover{
        background-color: {$contact_store_button_background_hover};
        color: {$contact_store_button_color_hover};
    }
    
    #yith-sl-results .view-website.link,
    .yith-sl-pin-modal .view-website.link{
        color: {$view_website_link_color_normal};
    }
    
    #yith-sl-results .view-website.link:hover,
    .yith-sl-pin-modal .view-website.link:hover{
        color: {$view_website_link_color_hover};
    }
    
    #yith-sl-gmap .gm-style .gm-style-iw{
        width: {$pin_modal_width}px;
        background-color: {$pin_modal_background_color};
        color: {$pin_modal_color};
        border-radius: {$pin_border_radius}px;
        padding: {$pin_modal_padding}px;
    }
    
    #yith-sl-gmap button.gm-ui-hover-effect{
        right: {$close_button_right}px!important;
        top: 5px!important;
    } 
    
    #yith-sl-results .wrap-store-details:hover {
        background-color: {$results_hover_color};
    }          

";

if( $results_columns === "two" ){
    $style .= "
    #yith-sl-results .wrap-store-details{
        flex: 0 50%;
    }
    
    #yith-sl-results .stores-list > ul{
        display: flex;
        flex-wrap: wrap;
    }
    ";
}


return $style;