( function ( $ ) {
    $( document ).ready( function($) {
        $( "#yit_store-locator_options_enable-geolocation").change(function () {
            if( $( this ).val() === 'no'){
                $( "input#yit_store-locator_options_geolocation-style-text" ).prop( "checked", true ).click();
            }
        });

        $( "#yit_store-locator_options_enable-view-all-stores" ).change(function () {
            if( $( this ).val() === "no"){
                $( "input#yit_store-locator_options_view-all-stores-style-text" ).prop( "checked", true).click();
            }
        });

        /* Manage Show Contact Store option */

        $( "#yit_store-locator_options_stores-list-show-contact-store" ).change(function () {
            if( $( this ).val() === "no"){
                $( "input#yit_store-locator_options_stores-list-contact-store-style-link" ).prop( "checked", true).click();
                $( "#yit_store-locator_options_stores-list-color-contact-store-link" ).parents( "tr" ).css({"opacity": "0", "display": "none"});
            }else{
                $( "#yit_store-locator_options_stores-list-color-contact-store-link" ).parents( "tr" ).css({"opacity": "1", "display": "table-row"}).addClass( "fadein" );
            }
        });

        if( $( "#yit_store-locator_options_stores-list-show-contact-store" ).val() === "no"){
            setTimeout(function(){
                $( "#yit_store-locator_options_stores-list-color-contact-store-link" ).parents( "tr" ).css({"opacity": "0", "display": "none"});
            }, 100);
        }else{
            $( "#yit_store-locator_options_stores-list-color-contact-store-link" ).parents( "tr" ).css({"opacity": "1", "display": "table-row"}).addClass( "fadein" );
        }

        /* Manage Show Get Direction option */

        $( "#yit_store-locator_options_stores-list-show-get-directions" ).change(function () {
            if( $( this ).val() === "no"){
                $( "input#yit_store-locator_options_stores-list-get-direction-style-link" ).prop( "checked", true).click();
                $( "#yit_store-locator_options_stores-list-color-get-direction-link" ).parents( "tr" ).css({"opacity": "0", "display": "none"});
            }else{
                $( "#yit_store-locator_options_stores-list-color-get-direction-link" ).parents( "tr" ).css({"opacity": "1", "display": "table-row"}).addClass( "fadein" );
            }
        });

        if( $( "#yit_store-locator_options_stores-list-show-get-directions" ).val() === "no"){
            setTimeout(function(){
                $( "#yit_store-locator_options_stores-list-color-get-direction-link" ).parents( "tr" ).css({"opacity": "0", "display": "none"});
            }, 100);

        }

        /* Manage Show Website option */
        $( "#yit_store-locator_options_stores-list-show-visit-website" ).change(function () {
            if( $( this ).val() === "no"){
                $( "input#yit_store-locator_options_stores-list-visit-website-style-link" ).prop( "checked", true).click();
                $( "#yit_store-locator_options_stores-list-color-visit-website-link" ).parents( "tr" ).css({"opacity": "0", "display": "none"});
            }else{
                $( "#yit_store-locator_options_stores-list-color-visit-website-link" ).parents( "tr" ).css({"opacity": "1", "display": "table-row"}).addClass( "fadein" );
            }
        });

        if( $( "#yit_store-locator_options_stores-list-show-visit-website" ).val() === "no"){
            setTimeout(function(){
                $( "#yit_store-locator_options_stores-list-color-visit-website-link" ).parents( "tr" ).css({"opacity": "0", "display": "none"});
            }, 100);
        }


        var add_filter                  =   $( "#yith-sl-add-filter" ),
            filter_label                =   $( "#filter_label" ),
            loader                      =   $( "#yith-sl-loader" ),
            filters_sortable_container  =   $( "#filters-sortable-container" );

        add_filter.on( "submit", function(e){

            e.preventDefault();

            var $data = {
                form    :   $( this ).serialize(),
                action  :   "yith_sl_add_new_filter",
                context :   "admin"
            };


            if( filter_label.val() === "" ){
                alert( yith_sl_admin.notice_filter_label_required );
                return false;
            }

            loader.show();

            $.ajax( {
                type    : "POST",
                data    : $data,
                url     : yith_sl_admin.ajaxurl,
                success : function ( response ) {
                    var filters_list    =   $( ".wrap.yith-sl-filters" ),
                        table           =   filters_list.find( "table" );

                    filters_list.find( ".notice" ).remove();
                    filters_list.prepend( response.notice );
                    loader.hide();
                    table.find( ".no-filters" ).hide();
                    table.find( ".no-filters-hided" ).hide();
                    table.append( response.new_filter );
                    add_filter.find( "input" ).val("");
                }

            } );
        } );





        /** ------------------------------------------------------------------------
         *  Settings Filters Box - Sortable
         * ------------------------------------------------------------------------- */


        filters_sortable_container.sortable({
            update: function( event, ui ) {

                var $filters = {};

                $( ".filter-item-row" ).each(function(i) {
                    $( this ).attr( "data-order", i ); // updates the attribute
                    var id       = $( this ).data( "id" );
                    $filters[i]  = id;
                });

                var $ajax_data = {
                    action  :   "yith_sl_update_order_filters",
                    context :   "admin",
                    filters :   $filters
                };

                loader.show();

                $.ajax( {
                    type    : "POST",
                    data    : $ajax_data,
                    url     : yith_sl_admin.ajaxurl,
                    success : function ( response ) {
                        loader.hide();
                    },
                    error: function(){

                    }
                } );

            },

        });


    });


    $( document ).on( "click", ".yith-sl-delete-filter", function(e){

        var row =  $( this ).parents( "tr" );

        e.preventDefault();

        if ( window.confirm( yith_sl_admin.alert_delete_filter ) ) {

            var $data = {
                filter_id       :   $( this ).data( "filter-id" ),
                filter_slug     :   $( this ).data( "filter-slug" ),
                action          :   "yith_sl_delete_filter",
                context         :   "admin"
            };

            loader.show();

            $.ajax( {
                type    : "POST",
                data    : $data,
                url     : yith_sl_admin.ajaxurl,
                success : function ( response ) {

                    loader.hide();

                    row.remove();

                    var table = $( ".filters-table.wp-list-table" );

                    if( table.find( "tbody" ).find( "tr" ).length === 0 ){

                        var filters_list    =   $( ".wrap.yith-sl-filters" ),
                            html            =   "<tr class='no-filters'><td colspan='6'>" + yith_sl_admin.notice_no_filters_exists + "</td></tr>",
                            table           =    filters_list.find( "table" );

                        table.find( ".no-filters-hided" ).show();
                        table.find( "tbody" ).append( html );

                    }

                },
                error: function(){

                }
            } );

        }
    } );
} )( jQuery );

