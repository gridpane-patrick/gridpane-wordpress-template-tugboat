jQuery(document).ready( function( $ ) {
    
    // Help Section
    var errorMessageDuration = '10000',
        successMessageDuration = '10000';

    // Create Lead Pages
    $( "#wwlc_help_create_wwlc_pages" )
        .removeAttr( "disabled" ) // On load
        .click( function(){

            var $this = $( this );

            $this
                .attr( "disabled" , "disabled" )
                .siblings( ".spinner" )
                    .addClass( "visible" )
                    .css( "display" , "inline-block" );

            wwlcBackEndAjaxServices.createLeadPages()
                .done( function( data , textStatus , jqXHR ) {

                    if(data.status == "success"){

                        var table = '<br/><table class="lead-capture-pages">';

                        for ( var i = 0; i < data.wwlc_lead_pages.length; i++ ) {
                            
                            table += '<tr>';
                            table += '<td><a href="' + data.wwlc_lead_pages[ i ][ 'url' ] + '">' + data.wwlc_lead_pages[ i ][ 'name' ] + '</td>';
                            table += '</tr>';

                        };

                        table += '</table>';

                        toastr.success( "" , HelpSettingsVars.success_message + table , { "closeButton" : true , "showDuration" : successMessageDuration } );

                    }else{

                        toastr.error( data.error_message , HelpSettingsVars.error_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                        console.log( HelpSettingsVars.error_message );
                        console.log( data );
                        console.log( "----------" );

                    }

                })
                .fail( function( jqXHR , textStatus , errorThrown ) {

                    toastr.error( jqXHR.responseText , HelpSettingsVars.error_message , { "closeButton" : true, "showDuration" : errorMessageDuration } );

                    console.log( HelpSettingsVars.error_message );
                    console.log( jqXHR );
                    console.log( "----------" );

                })
                .always( function(){

                    $this
                        .removeAttr( "disabled" )
                        .siblings( ".spinner" )
                            .removeClass( "visible" )
                            .css( "display" , "none" );

                });

        });
});