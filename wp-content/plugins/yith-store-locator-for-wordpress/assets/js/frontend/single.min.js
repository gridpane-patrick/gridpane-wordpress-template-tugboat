( function ( $ ) {

    var extend_image = function(){

        var main_wrapper            =   $( "#yith-sl-main-wrapper" ),
            main_container_width    =   main_wrapper.outerWidth(),
            page_width              =   $( "body" ).outerWidth(),
            margin_on_left          =   ( page_width - main_container_width ) / 2 + 15;

        main_wrapper.find( ".main-section" ).find( ".wrap-image" ).find( "img" ).css({
            "margin-left"   : "-" + margin_on_left + "px",
            "width"         : "calc(100% + " + margin_on_left + "px)",
            "opacity"       : 1
        });
    };

    jQuery( document ).on( "ready", extend_image );

    jQuery( window ).on( "resize", extend_image );

} )( jQuery );

