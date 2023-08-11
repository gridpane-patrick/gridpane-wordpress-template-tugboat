/**
 * Reinit select 2 of Proteo in order to not show search box
 */
jQuery( document ).on( "ready", function(){
    setTimeout(function(){
        jQuery( "#yith-sl-main-filters-container" ).find( "select" ).select2("destroy").select2({
            minimumResultsForSearch : -1
        });
    }, 500);

} );

/**
 * Reinit select 2 on remove term
 */
jQuery( document ).on( "click", "#wrapper-active-filters .remove-term", function(){
    var filter  =   jQuery( this ).data( "taxonomy" ),
        value   =   jQuery( this ).data( "value" ),
        input   =   jQuery( "#yith-sl-main-filters-container" ).find( "[data-taxonomy='"+ filter +"'][data-value='"+ value +"']" ),
        wrapper_input   =   input.parents(".checkboxbutton");

    if( input.parents( "select" ).length > 0  ){
        jQuery( "#yith-sl-main-filters-container" ).find( "select[data-taxonomy='"+ filter +"'] option:first" ).prop('selected', true);
        jQuery( "#yith-sl-main-filters-container" ).find( "select[data-taxonomy='"+ filter +"']" ).select2({
            width: "100%",
            minimumResultsForSearch : -1
        });
    }
    wrapper_input.removeClass( "checked" );
} );

/**
 * Remove class checked for input wrapper on reset filters
 */

jQuery( document ).on( "yith_sl_reset_filters", function(){
    var main_filters_coontainer = jQuery( "#yith-sl-main-filters-container" );
    main_filters_coontainer.find( ".checkboxbutton.checked" ).removeClass( "checked" );
    main_filters_coontainer.find( "select" ).select2("destroy").select2({
        minimumResultsForSearch : -1
    });
});
