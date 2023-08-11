( function ( $ ) {

    var store_locator = {
        options                 :   yith_sl_store_locator,
        infowindows             :   [],
        is_google_suggestion    :   false,
        radius_default_step     :   yith_sl_store_locator.filter_radius_default_step,
        latitude                :   "",
        longitude               :   "",
        filters                 :   {},
        search_bar_address      :   $( "#yith-sl-search-bar-address" ),

        init    :   function(){
            /* Init Store Locator on document ready */
            $( document ).on( "ready", store_locator.initStoreLocator );

            /* Geolocation on click */
            $( document ).on( "click", "#yith-sl-geolocation", function( e ){
                e.preventDefault();
                $( "#yith-sl-wrap-loader" ).show();

                store_locator.useCurrentLocation();
            });

            /* Search stores on click on icon */
            $( document ).on( "click", "#yith-sl-search-icon", function(){
                store_locator.getResults();
            } );

            /* Show all stores */
            $( document ).on( "click", "#yith-sl-show-all-stores", store_locator.showAllStores );

            /* Search stores */
            $( document ).on( "click", "#yith-sl-search-button", function(e){
                e.preventDefault();
                store_locator.getResults();
            });

            /* Show all results */
            $( document ).on( "click", "#yith-sl-view-all", store_locator.showAllResults );

            /* Get results on type enter on keyboard */
            $( document ).on( "keypress" ,function(e) {

                if( e.which === 13 ) {
                    store_locator.getResults();
                }
            });

            /* Get results on submit/change address */
            $( document ).on( "keypress change", "#yith-sl-search-bar-address", store_locator.getResultsOnChangeAddress );

            /* Show tooltip on change radius if no address is set */
            if( store_locator.options.autosearch === "no" ) {
                $(document).on("click change", ".wrapper-filter[data-taxonomy=yisl_radius] li, select#filter-radius", store_locator.showTooltip );
            }

            /* Full Width layout */
            if( store_locator.options.full_width_layout === "yes" ){
                $( window ).on( "resize", store_locator.makeLayoutFullwidth );
            }

            /* FILTERS: Init filters on change filters type dropdown/radio */
            $( document ).on( "click", "#yith-sl-main-filters-container.layout-dropdown .type-dropdown .wrapper-options label", function(){
                var wrapper_filter  =   $( this ).parents( ".wrapper-filter" );
                wrapper_filter.find( "li" ).removeClass( "active" );
                wrapper_filter.find( "input" ).removeAttr( "checked" );
                $( this ).closest( "li" ).addClass( "active" );
                store_locator.initFilters();
            });

            /* FILTERS: Init filters on change filters type select/checkbox */
            $( document ).on( "change", "#yith-sl-main-filters-container select, #yith-sl-main-filters-container input", function(){
                store_locator.initFilters();
            } );

            /* FILTERS: Set as selected/checked any option is selected */
            $( document ).on( "change", "#yith-sl-main-filters-container input, select", function(){

                if( $( this ).is( "select" )){
                    var value   =   $( this ).val();
                    $( this ).find( "option[value="+ value +"]" ).attr( "selected", "true" );

                }else if( $( this ).is( ":checkbox" ) || $( this ).is( ":radio" ) ){

                    var checked =   $( this ).is(":checked");
                    $( this ).attr( "checked", checked );

                }
            } );

            /* FILTERS: Reload results once filters are initialized (only when instant search is enabled)  */
            $( document ).on( "yith_sl_filters_initialized", function(){
                if( store_locator.options.autosearch === "yes" ){
                    store_locator.getResults();
                }
            } );

            /* FILTERS: Reset Filters */
            $( document ).on( "click", "#yith-sl-reset-all-filters", function(){
                store_locator.resetFilters();
                store_locator.getResults();
            });

            /* FILTERS: Remove filter */
            $( document ).on( "click", "#wrapper-active-filters .remove-term", store_locator.removeFilter );

            /* FILTERS: open/close filters section */
            $( document ).on( "click", "#yith-sl-open-filters", function(){
                $( ".wrap-filters-list" ).slideToggle();
            } );


        },

        initStoreLocator    : function(){
            var firstTime   =   localStorage !== null ? localStorage.getItem("first_time") : null;
            
            if( store_locator.options.show_map === "yes" ){
                store_locator.initMap( store_locator.latitude, store_locator.longitude );
            }

            if( store_locator.options.autogeolocation === 'yes' ){
                store_locator.useCurrentLocation();
            }

            if ( typeof google !== undefined && typeof google.maps !== undefined && typeof google.maps.places !== undefined && typeof google.maps.places.Autocomplete !== undefined ) {
                store_locator.initSearchAddressInput();
            }else {
                console.error( "Google Maps Javascript API error while using Autocomplete. Probably other plugins (or your theme) include Google Maps Javascript API without support to the Places library" );
            }

            /* Show stores by default */
            if( !firstTime ) {
                // first time loaded!
                localStorage.setItem( "first_time", "1" );
            }
            if( firstTime === 1 && store_locator.options.autogeolocation === "no" && store_locator.options.show_stores_by_default === "yes" ){ // Prevent double ajax call to get results when loads all stores by default and is enabled the autogeolocation
                store_locator.ajax_show_all_stores();
            }

            /* Full width layout */
            if( store_locator.options.full_width_layout === "yes" ){
                store_locator.makeLayoutFullwidth();
            }

            /* Init filters */
            store_locator.initFilters();

        },

        showTooltip :   function(){
            var address         =   $( "#yith-sl-search-bar-address" ).val(),
                radius          =   $( this ).find( "input" ).val(),
                proteo_radius   =   $(this).val(),
                address_tooltip =   $( "#address-tooltip" );

            if( address === "" && ( ( radius !== undefined && radius !== 0 && radius !== "selectall" ) || ( proteo_radius !== undefined && proteo_radius !== 0 && proteo_radius !== "selectall" ) ) ) {
                address_tooltip.fadeIn();
                setTimeout(function(){
                    address_tooltip.fadeOut();
                }, 4000);
            }
        },

        makeLayoutFullwidth   :   function(){
            var main_container          =   $( "#yith-store-locator" ),
                main_container_width    =   main_container.outerWidth( true ),
                body_width              =   $( "body" ).width(),
                margin_side             =   ( body_width - main_container_width ) /2;
            main_container.css( "margin", "0 -" + margin_side + "px"  );
            main_container.css({ "padding-left" : store_locator.options.left_padding_full_width_layout + "px", "padding-right" : store_locator.options.right_padding_full_width_layout + "px" });
        },

        getResultsOnChangeAddress   :   function( e ){
            switch ( e.type ) {
                case "keypress":
                    if( e.which === 13 ) {
                        e.stopPropagation();
                        store_locator.getAddress( store_locator.getResults );
                    }
                    break;

                case "change":
                    store_locator.getAddress( store_locator.getResults );
                    break;

                default:
                    break;
            }
        },

        getAddress:  function( cb ){
            var address_text = $( "#yith-sl-search-bar-address" ).val();

            if( address_text !== "" ){
                geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    'address': address_text
                }, function(results, status) {

                    if( results.length > 0 ){

                        store_locator.latitude = parseFloat( results[0].geometry.location.lat() );
                        store_locator.longitude = parseFloat( results[0].geometry.location.lng() );
                        store_locator.is_google_suggestion = false;

                        $( "#yith-sl-geolocation-enabled" ).val( 'no' );

                        if( store_locator.options.autosearch === "yes" ){
                            cb();
                        }

                    }

                });
            }
        },

        showAllResults  :   function( e ){
            e.preventDefault();
            $( this ).fadeTo("fast", 0 );
            var display = store_locator.options.results_columns === "one" ? "block" : "flex";

            $( "#yith-sl-results" ).find( ".additional-stores" ).slideDown({
                start: function () {
                    $(this).css({
                        display: display
                    });
                }
            });
        },


        showAllStores   :   function( e ){
            e.preventDefault();

            $( "#yith-sl-search-bar-address" ).val("");

            store_locator.latitude = "" ;
            store_locator.longitude = "" ;
            store_locator.geolocation = "no";

            store_locator.resetFilters();

            store_locator.getResults( true );
        },

        initSearchAddressInput  :   function(){

            var address         =   $( ".yith-sl-gmap-places-autocomplete" )[0],
                autocomplete    =   new google.maps.places.Autocomplete( address );

            google.maps.event.addListener( autocomplete, "place_changed", function () {

                var place = autocomplete.getPlace();

                if( place.geometry !== undefined ){

                    store_locator.latitude = parseFloat(place.geometry.location.lat());
                    store_locator.longitude = parseFloat(place.geometry.location.lng());
                    store_locator.is_google_suggestion = true;

                    $( "#yith-sl-geolocation-enabled" ).val( 'no' );
                    if( store_locator.options.autosearch === "yes" ){
                        store_locator.getResults();
                    }
                }else{
                    store_locator.is_google_suggestion = false;
                }


            });
        },

        useCurrentLocation  :   function(){
            if ( !navigator.geolocation ){
                alert( store_locator.options.alert_geolocalization_not_supported );
                $( "#yith-sl-wrap-loader" ).hide();
                return;
            }

            var options = {
                enableHighAccuracy: true,
            };

            navigator.geolocation.getCurrentPosition( store_locator.geolocationSuccess, store_locator.geolocationError, options );
        },

        geolocationSuccess  :   function( position ){

            store_locator.latitude      =   position.coords.latitude;
            store_locator.longitude     =   position.coords.longitude;
            store_locator.geolocation   =   "yes";

            var geocoder                =   new google.maps.Geocoder,
                latlng = { lat: store_locator.latitude, lng: store_locator.longitude };

            geocoder.geocode({ "location": latlng }, function( results, status ) {
                if (status === "OK" ) {
                    if (results[0]) {
                        $( "#yith-sl-wrap-loader" ).hide();
                        var address = results[0].formatted_address;
                        store_locator.search_bar_address.val( address );
                        if( store_locator.options.autosearch === "yes" ){
                            store_locator.getResults();
                        }
                    } else {
                        window.alert( store_locator.options.geocode_no_results );
                    }
                } else {
                    window.alert( store_locator.options.geocode_failed_to + " " + status);
                }
            });
        },

        geolocationError    :   function(){
            alert( store_locator.options.alert_calculate_position_error );
            $( "#yith-sl-wrap-loader" ).hide();
        },

        getResults  :   function(){
            // Init results
            var data = {
                latitude                :   store_locator.latitude,
                longitude               :   store_locator.longitude,
                geolocation             :   store_locator.geolocation,
                radius                  :   store_locator.filter_radius_step,
                filters                 :   store_locator.filters,
                action                  :   store_locator.options.action_get_results,
                context                 :   "frontend",
            };
            store_locator.ajaxSearchStores( data );
        },

        ajaxSearchStores    :   function( $data ){
            var results         =   $( "#yith-sl-results" ),
                active_filters  =   $( "#yith-sl-active-filters" );

            $( "#yith-sl-wrap-loader" ).show();

            jQuery.ajax( {
                type    : "POST",
                data    : $data,
                url     : store_locator.options.ajaxurl,
                success : function ( response ) {

                    if( store_locator.options.show_results === "yes" ){

                        results.find( ".title" ).show();

                        results.find( ".stores-list" ).html( response.results );

                        active_filters.html( response.active_filters );

                        if( store_locator.options.autosearch === "yes" ){
                            $( "#yith-sl-search-button" ).hide();
                        }

                    }

                    if( store_locator.options.show_map === "yes" ){
                        store_locator.initMap( $data.latitude, $data.longitude, response.markers, store_locator.options.map_zoom, $data['geolocation'] );
                    }

                    if( active_filters.find( "li" ).length > 0 && ! jQuery.isEmptyObject( store_locator.filters ) && ( $data.show_all === "no" || $data.show_all === undefined )  ){
                        active_filters.show();
                    }

                    /* Hide active filters section if empty */
                    var active_filters_length = active_filters.find( "li" ).length;

                    if( active_filters_length === 0 ){
                        active_filters.hide();
                    }

                    results.show();

                    $( "#yith-sl-wrap-loader" ).hide();



                },
                complete: function () {

                    $( "#yith-sl-main-filters-container.layout-dropdown" ).find( ".wrapper-options" ).hide();

                    /* Show filters with results */
                    if( store_locator.options.show_filters_with_results === "no" ){

                        $( "#yith-sl-main-filters-container" ).find( ".wrap-filters-list" ).hide();
                        $( "#yith-sl-open-filters" ).show();

                    }

                    $( ".wrapper-filter.active" ).removeClass( "active" );


                },
                error: function(){

                }
            } );
        },

        initMap :   function( latitude, longitude, markers, zoom, geolocation ){
            if( markers == null ){
                markers = "";
            }

            if( zoom == null ){
                zoom = store_locator.options.map_zoom;
            }

            if( geolocation == null ){
                geolocation = "no";
            }

            if( latitude === '' ){
                latitude = store_locator.options.map_latitude;
            }

            if( longitude === '' ){
                longitude = store_locator.options.map_longitude;
            }

            var bounds  = new google.maps.LatLngBounds(),
                map     = new google.maps.Map( document.getElementById( "yith-sl-gmap" ), {
                    zoom            : Number(zoom),
                    mapTypeId       : store_locator.options.map_type,
                    scrollWheelZoom : true,
                    styles          : store_locator.options.map_style,
                    gestureHandling : store_locator.options.map_scroll_type,
                    minZoom         : 1
                }),
                opened_marker_window_coordinates = {};

            if( markers.length !== 0 ){

                var showed_markers = [];

                jQuery.each( markers, function( index, marker ){

                    var latlng = new google.maps.LatLng( marker.latitude, marker.longitude ),
                        googleMarker = new google.maps.Marker({
                            position    : latlng,
                            map         : map,
                            title       : marker.name,
                            icon        : marker.pin_icon
                        });

                    bounds.extend( googleMarker.position );

                    if( marker.pin_modal !== "undefined" ){

                        var infowindow = new google.maps.InfoWindow({
                            content: marker.pin_modal
                        });

                        store_locator.infowindows.push( infowindow ) ;

                        googleMarker.addListener( store_locator.options.pin_modal_trigger_event , function( e ) {

                            var offset_difference = '';

                            if( e !== undefined && e.rb !== undefined ){

                                offset_difference =  Math.abs( e.rb.screenY - opened_marker_window_coordinates.y ) ;
                            }

                            store_locator.closeAllInfoWindows()

                            if( e === undefined || offset_difference > 300 ){
                                map.panTo( googleMarker.getPosition() );
                            }

                            infowindow.open( map, googleMarker );

                        });

                    }

                    showed_markers[marker.id] = googleMarker;

                    googleMarker.setMap( map );
                });

                /* SHOW ON MAP */
                var wrap_store_details = $( ".wrap-store-details" );

                wrap_store_details.mouseenter( function(){

                    store_locator.closeAllInfoWindows();

                    var marker_id = $( this ).data( "id" );

                    google.maps.event.trigger( showed_markers[marker_id], store_locator.options.pin_modal_trigger_event );

                } );

                wrap_store_details.mouseleave( function(){

                    store_locator.closeAllInfoWindows();

                } );


                if( geolocation === "yes" ){

                    var userLatlng = new google.maps.LatLng( latitude, longitude ),
                        googleMarker = new google.maps.Marker({
                        position    : userLatlng,
                        map         : map,
                        icon        : yith_sl_store_locator.map_user_icon
                    });

                    bounds.extend( googleMarker.position );
                }

                /* Adjust zoom in case of a single marker */
                if ( bounds.getNorthEast().equals( bounds.getSouthWest() ) ) {
                    var extendPoint = new google.maps.LatLng( bounds.getNorthEast().lat() +0.10, bounds.getNorthEast().lng() + 0.10 );
                    bounds.extend( extendPoint );
                }

                // Add circle overlay and bind to marker

                if( store_locator.options.show_circle === "yes" && store_locator.filters['yisl_radius'] &&  latitude !== undefined && longitude !== undefined && latitude !== store_locator.options.map_latitude && longitude !== store_locator.options.map_longitude ){

                    var radius_address = new google.maps.LatLng( latitude, longitude ),
                        cityCircle = new google.maps.Circle({
                        strokeColor     :   store_locator.options.circle_border_color,
                        center          :   radius_address,
                        strokeWeight    :   store_locator.options.circle_border_weight,
                        fillColor       :   store_locator.options.circle_background_color,
                        map             :   map,
                        radius          :   store_locator.options.filter_radius_distance_unit === "km" ? store_locator.filters['yisl_radius'] * 1000 : store_locator.filters['yisl_radius'] * 1.60934
                    });

                    bounds.union( cityCircle.getBounds() );
                }

                map.fitBounds( bounds );

            }

            else{
                // Show circle also without results
                if( store_locator.options.show_circle === "yes" && store_locator.filters['yisl_radius'] &&  latitude !== undefined && longitude !== undefined && latitude !== store_locator.options.map_latitude && longitude !== store_locator.options.map_longitude ){
                    var radius_address = new google.maps.LatLng( parseFloat(latitude), parseFloat(longitude) ),
                        cityCircle = new google.maps.Circle({
                            strokeColor     :   store_locator.options.circle_border_color,
                            center          :   radius_address,
                            strokeWeight    :   store_locator.options.circle_border_weight,
                            fillColor       :   store_locator.options.circle_background_color,
                            map             :   map,
                            radius          :   store_locator.options.filter_radius_distance_unit === "km" ? store_locator.filters['yisl_radius'] * 1000 : store_locator.filters['yisl_radius'] * 1.60934
                        });

                    map.setCenter( radius_address );

                }else{
                    var latlng = new google.maps.LatLng( latitude, longitude );
                    map.setCenter( latlng );
                }
            }
        },

        closeAllInfoWindows :   function(){
            for ( var i=0; i < store_locator.infowindows.length; i++) {
                store_locator.infowindows[i].close();
            }
        },

        initFilters    :   function(){

            var main_filters_container  =   $( "#yith-sl-main-filters-container" ),
                wrapper_filters         =   main_filters_container.find( ".wrapper-filter" );

            wrapper_filters.each( function(){
                $( this ).removeClass( "selected" );
                var taxonomy = jQuery( this ).data( "taxonomy" );
                store_locator.filters[taxonomy] = [];
            } );


            main_filters_container.find( "li.active input, input:checked, option:selected" ).each( function(){

                var wrapper_filter  =   $( this ).parents( ".wrapper-filter" ),
                    taxonomy        =   $( this ).parents( ".wrapper-filter" ).data( "taxonomy" ),
                    value           =   $( this ).val();

                if( taxonomy !== undefined ){

                    if( value !== "selectall" ){

                        wrapper_filter.addClass( "selected" );
                        store_locator.filters[taxonomy].push( value );
                    }else{
                        store_locator.filters[taxonomy] = [];
                    }
                }

            } );
            $( document ).trigger( "yith_sl_filters_initialized" );
        },

        resetFilters    :   function(){
            var main_filters_container  =   $( "#yith-sl-main-filters-container" ),
                wrapper_filter          =   main_filters_container.find( ".wrapper-filter" );

            $( ".open-dropdown" ).each( function(){
                var label = jQuery( this ).data( "title" );
                $( this ).find( ".text" ).html( label );
            } );

            wrapper_filter.find( "option:selected, input:checked" ).each( function(){
                $( this ).removeAttr( "selected checked" );
            } );

            wrapper_filter.find( "li.active" ).removeClass( "active" );

            wrapper_filter.find( "select" ).prop( "selectedIndex",0);

            wrapper_filter.removeClass( "selected active" );

            $( "#yith-sl-active-filters" ).hide();

            $( document ).trigger( "yith_sl_reset_filters" );

            store_locator.initFilters();
        },

        removeFilter:   function(){
            var filter          =   $( this ).data( "taxonomy" ),
                value           =   $( this ).data( "value" ),
                input           =   $( "#yith-sl-main-filters-container" ).find( "[data-taxonomy='"+ filter +"'][data-value='"+ value +"']" ),
                wrapper_filter  =   input.parents( ".wrapper-filter" ),
                active_filters_for_taxonomy = $( this ).closest( ".wrapper-terms" ).find( "li" ).length ;

            input.removeAttr( "selected checked" );
            input.parents( "li" ).removeClass( "active" );
            wrapper_filter.removeClass( "selected" );

            if( active_filters_for_taxonomy > 1 ){ // In case of checkbox remove only single term from active filters
                $( this ).parent( "li" ).remove();
            }else{ // in other case remove the entire box of filter
                $( this ).parents( "li" ).remove();
            }

            if( input.is( ":radio" ) ){
                var title = wrapper_filter.find( ".open-dropdown" ).data( "title" );
                wrapper_filter.find( ".text" ).html( title );
            }

            store_locator.initFilters();

            var active_filters = $( "#wrapper-active-filters" ).find( "li" ).length;

            if( active_filters === 0 ){
                $( "#yith-sl-active-filters" ).hide();
            }


            if( store_locator.options.autosearch === "yes" ){
                store_locator.getResults();
            }
        }

    };

    store_locator.init();

} )( jQuery );