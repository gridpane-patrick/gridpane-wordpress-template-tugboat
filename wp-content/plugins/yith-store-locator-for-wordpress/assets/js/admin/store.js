( function ( $ ) {

    $(document).ready(function($){

        if ( ! $( "#yith_sl_map" ).length ) {
            return;
        }

        var maps_places_inputs          =   $( ".yith-sl-gmap-places-autocomplete" ),
            address_search              =   $( "#_yith_sl_gmap_location" ),
            address_section             =   $( "#yith_sl_metabox_address" ),
            address_line1               =   $( "#_yith_sl_address_line1" ),
            address_city                =   $( "#_yith_sl_city" ),
            address_state               =   $( "#_yith_sl_address_state" ),
            address_country             =   $( "#_yith_sl_address_country" ),
            address_postcode            =   $( "#_yith_sl_postcode" ),
            address_latitude            =   $( "#_yith_sl_latitude" ),
            address_longitude           =   $( "#_yith_sl_longitude" ),
            place_id                    =   $( "#_yith_sl_google_map_place_id" );

        maps_places_inputs.each( function () {

            if ( typeof google !== "undefined" && typeof google.maps !== "undefined" && typeof google.maps.places !== "undefined" && typeof google.maps.places.Autocomplete !== "undefined" ) {
                var autocomplete = new google.maps.places.Autocomplete( this );

                autocomplete.setFields([ "place_id", "geometry", "name", "address_components" ]);

                google.maps.event.addListener(autocomplete, "place_changed", function () {

                    var place = autocomplete.getPlace(),
                        latitude = parseFloat(place.geometry.location.lat()),
                        longitude = parseFloat(place.geometry.location.lng()),
                        n_address_components = place.address_components.length,
                        value_address_line1,
                        value_address_city,
                        value_address_state,
                        value_address_country,
                        value_address_postal_code,
                        value_street_number,
                        i;

                    for ( i=0; i<n_address_components; i++ ){

                        var type = place.address_components[i].types[0];


                        switch ( type ) {

                            case "route":
                                value_address_line1 = place.address_components[i].long_name;
                                break;

                            case "locality":
                                value_address_city = place.address_components[i].long_name;
                                break;

                            case "administrative_area_level_2":
                                value_address_state = place.address_components[i].long_name;
                                break;

                            case "country":
                                value_address_country = place.address_components[i].long_name;
                                break;

                            case "postal_code":
                                value_address_postal_code = place.address_components[i].long_name;

                                break;

                            case "street_number":
                                value_street_number = place.address_components[i].long_name;
                                break;
                        }
                    }

                    address_line1.val( value_address_line1 + " " + value_street_number );
                    address_city.val( value_address_city );
                    address_state.val( value_address_state );
                    address_country.val( value_address_country );
                    address_postcode.val( value_address_postal_code );

                    if( place.place_id !== "" ){
                        place_id.val( place.place_id );
                    }

                    address_latitude.val(latitude);

                    address_longitude.val(longitude);

                    yithSlInitMap( longitude,latitude );

                    address_section.addClass('show').slideDown();

                });

            } else {
                console.error( "Google Maps Javascript API error while using Autocomplete. Probably other plugins (or your theme) include Google Maps Javascript API without support to the Places library" );
            }


        } );

        if( address_search.val() !== "" ){
            address_section.addClass( "show" );
        }

        showMap();

    });



    function showMap(){

        var latitude = $("#_yith_sl_latitude").val(),
            longitude = $("#_yith_sl_longitude").val();

        yithSlInitMap( longitude,latitude );

    }

    function yithSlInitMap( longitude,latitude  ) {

        // The location of Uluru
        var mapProp= {
            center:new google.maps.LatLng(latitude,longitude),
            zoom:18,
        },
        map = new google.maps.Map(document.getElementById( "yith_sl_map" ),mapProp );

    }


    /**
     * Set default value for filterx taxonomies
     */
    $( document ).on( "click", ".yith-sl-set-default", function(){
        var value       =   $( this ).data( "value" ),
            taxonomy    =   $( this ).data( "taxonomy" ),
            rows        =   $( "#the-list tr" ),
            current_row =   $( this ).closest( "tr" ),
            defaults    =   $( ".yith-sl-set-default" );

        rows.removeClass( "checked" );

        $( "#the-list" ).find( "tr" ).removeClass( "checked" );

        if (  $( this ).is( ":checked" ) ) {
            current_row.addClass( "checked" );
        }
        defaults.not( this ).prop( "checked", false);


        var $data = {
            value           :   value,
            taxonomy        :   taxonomy,
            action  :   "yith_sl_update_default_value",
            context :   "admin"
        };
        $.ajax( {
            type    : "POST",
            data    : $data,
            url     : yith_sl_admin.ajaxurl,
            success : function ( response ) {

            },
            error: function(){

            }
        } );
    } );

    $( document ).on( "ready", function(){
        /* Add checked class to default value selected for radius filter */
        $( ".taxonomy-yisl_radius" ).find( ".yith-sl-set-default:checked" ).parents( "tr" ).addClass( "checked" );
    } );


} )( jQuery );
