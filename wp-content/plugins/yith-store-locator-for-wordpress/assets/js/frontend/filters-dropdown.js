( function ( $ ) {

    var dropdownFilter  =   {

        init    :   function(){
            $( document ).on( "click", ".wrapper-filter.type-dropdown li", dropdownFilter.manageSelectDropdown  );
            $( document ).on( "click", ".open-dropdown", dropdownFilter.manageOpenDropdown  );
            $( document ).on( "click", dropdownFilter.closeDropdown );
            $( document ).on( "click", ".wrapper-filter.type-checkbox", function( e ) {
                e.stopPropagation();
            });
            $( document ).on( "click", ".wrapper-filter .filter-label", function(){
                $( this ).next( ".open-dropdown" ).click();
            } );
        },

        manageSelectDropdown : function( e ){
            e.stopPropagation();

            var wrapper_filter = $( this ).parents( ".wrapper-filter" );

            $( this ).parents( "ul" ).find( "li" ).removeClass( "active" );
            wrapper_filter.find( "input" ).removeAttr( "checked" );
            $( this ).addClass( "active" );

            var open_dropdown   =   wrapper_filter.find( ".open-dropdown" ),
                label           =   $( this ).find( "label" ).data( "title" );

            if( open_dropdown.hasClass( "radius" ) ){
                label = yith_sl_store_locator.filter_radius_title + ': ' + label;
            }

            open_dropdown.find( ".text" ).html( label );

            $( this ).find( "input" ).attr( "checked", "checked" );
            $( this ).parents( ".wrapper-options" ).hide();

            $( ".wrapper-filter.type-dropdown.active" ).removeClass( "active" );
        },

        manageOpenDropdown : function( e ){

            e.stopPropagation();

            var wrapper_filter          =   $( this ).parents( ".wrapper-filter" ),
                wrapper_options         =   $( this ).next( ".wrapper-options" ),
                is_active               =   wrapper_filter.hasClass( "active" ),
                main_filters_container  =   $( "#yith-sl-main-filters-container" );


            $( ".wrapper-filter.active" ).removeClass( "active" );


            if( main_filters_container.hasClass( "layout-dropdown" ) ){
                main_filters_container.find( ".wrapper-options" ).hide();
            }

            if( is_active ){
                wrapper_filter.removeClass( "active" );
                wrapper_options.hide();

            }else{
                wrapper_filter.addClass( "active" );
                wrapper_options.show();
            }
        },

        closeDropdown : function(){

            var filters_container = $( "#yith-sl-main-filters-container" );

            if( filters_container.hasClass( "layout-dropdown" ) ){
                filters_container.find( ".wrapper-filter" ).find( ".wrapper-options" ).hide();
            }else{
                filters_container.find( ".wrapper-filter.type-dropdown" ).find( ".wrapper-options" ).hide();
            }

            filters_container.find( ".wrapper-filter" ).removeClass( "active" );
        }

    };

    dropdownFilter.init();

} )( jQuery );