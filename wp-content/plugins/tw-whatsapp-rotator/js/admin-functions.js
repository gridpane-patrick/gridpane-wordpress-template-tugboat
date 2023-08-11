(function($) {
    "use strict";
    jQuery(document).ready(function($) {
        $(".js-range-slider").each(function() {
            var $this = $(this),
                dataStart = $(this).data('input-start'),
                dataEnd = $(this).data('input-end');

            $this.ionRangeSlider({
                skin: "flat",
                type: "double",
                grid: false,
                min: moment("0000", "hhmm").valueOf(),
                max: moment("2359", "hhmm").valueOf(),
                from: moment("0900", "hhmm").valueOf(),
                to: moment("1700", "hhmm").valueOf(),
                force_edges: true,
                drag_interval: true,
                step: 60000,
                min_interval: 60000,
                prettify: function (num) {
                return moment(num).format('HH:mm');
                },
                onChange: function (data) {

                    $('input#'+dataStart).val(data.from.hours);
                    $('input#'+dataEnd).val(data.to.hours);
                    console.log(data.to.hours)
                }
            });
        });
        
        $(".js-range-slider-break").each(function() {
            var $this = $(this),
                dataStart = $(this).data('input-start'),
                dataEnd = $(this).data('input-end');

            $this.ionRangeSlider({
                skin: "flat",
                type: "double",
                grid: false,
                min: moment("0000", "hhmm").valueOf(),
                max: moment("2359", "hhmm").valueOf(),
                from: moment("0900", "hhmm").valueOf(),
                to: moment("1700", "hhmm").valueOf(),
                force_edges: true,
                drag_interval: true,
                step: 60000,
                min_interval: 60000,
                prettify: function (num) {
                return moment(num).format('HH:mm');
                },
                onChange: function (data) {

                    $('input#'+dataStart).val(data.from.hours);
                    $('input#'+dataEnd).val(data.to.hours);
                }
            });
        });
        
        jQuery.each( $('.twwr-whatsapp-chat-timepicker'), function(key, val){
            if( $(this).attr('name').includes('_start') ){
                var time = ($(this).val() != '') ? $(this).val() : '08:00';
            }
            else if( $(this).attr('name').includes('_break_end') ){
                var time = ($(this).val() != '') ? $(this).val() : '13:00';
            }
            else if( $(this).attr('name').includes('_break') ){
                var time = ($(this).val() != '') ? $(this).val() : '12:00';
            }
            else if( $(this).attr('name').includes('_end') ){
                var time = ($(this).val() != '') ? $(this).val() : '17:00';
            }
            else{
                var time = ($(this).val() != '') ? $(this).val() : '10:00';
            }

            var timeOptions = {
                now: time,
                clearable: true,
                title: 'Pick a time',
                timeSeparator: ':',
            }
            $(this).wickedpicker(timeOptions);
        });
        

        $('#twwr_whatsapp_working_day_allday').on('change', function(){
            if ($('#twwr_whatsapp_working_day_allday').is(':checked')) {
                $('.twwr-whatsapp-working-day').hide();
            }
            else{
                $('.twwr-whatsapp-working-day').show();
            }
        });

        $('#twwr_whatsapp_working_day_allday').trigger('change');


        $('.date-dropdown').dateDropdowns({
            daySuffixes: false,
        });

        $('#filter-by-date').trigger('change');
        $('.twwr-whatsapp-button-type').trigger('change');
        jQuery('.date-dropdowns select').trigger('change');
    });

    jQuery(document).click(function(x) {
        if(jQuery(x.target).closest('.close-chat').length) {
            jQuery('.twwr-whatsapp-content').removeClass('open');
        }
    });

    jQuery(document).ready( function($){
        if( jQuery('.parent_button').length > 0 ){
            jQuery( ".parent_button" ).sortable({
                axis: "y"
            });
        }

        jQuery('.twwr_whatsapp_style').on('change', function(){
            if( $(this).val() == 'twwr-floating' ){
                $('.twwr_whatsapp_position_parent').show();
            }
            else{
                $('.twwr_whatsapp_position_parent').hide();
            }
        });

        jQuery('.button-contact-chat').find('.button_type').trigger('change');
        jQuery('.color-field').wpColorPicker();
    });

    jQuery(document).on('click', '.button-plus-agent', function(e){
        e.preventDefault();
        if( jQuery('.button-agent').length > 0 ){
            var row = parseInt( jQuery('.button-agent:last').attr('data-seq') ) + 1;
        }
        else{
            var row = 0;
        }
        var temp = jQuery( "#button-agent-template" ).html();
        temp = temp.replace(/{{row}}/g, row);
        jQuery('.parent-button-agent').append(temp);

        jQuery( ".parent_button" ).sortable({
            axis: "y"
        });

        jQuery('.parent_button .button_min').show();
        jQuery('.parent_button .button_min:last').hide();
    });

    jQuery(document).on('click', '.button-plus-chat', function(e){
        e.preventDefault();
        if( jQuery('.button-contact-chat').length > 0 ){
            var row = parseInt( jQuery('.button-contact-chat:last').attr('data-seq') ) + 1;
        }
        else{
            var row = 0;
        }
        var temp = jQuery( "#button-chat-template" ).html();
        temp = temp.replace(/{{row}}/g, row);
        jQuery('.parent-button-chat').append(temp);

        $( ".parent_button" ).sortable({
            axis: "y"
        });

        jQuery('.parent_button .button_min').show();
        jQuery('.parent_button .button_min:last').hide();
    });

    jQuery(document).on('click', '.button_min', function(e){
        e.preventDefault();
        jQuery( this ).closest( ".button-contact" ).remove();
    });

    jQuery(document).on('click', '.fb_id_button_plus', function(e){
        e.preventDefault();
        if( jQuery('.fb_pixel_id').length < 5 )
            jQuery( ".fb_pixel_id:last" ).clone().appendTo( ".fb_id_parent" );
    });

    jQuery(document).on('click', '.fb_id_button_min', function(e){
        e.preventDefault();
        if( jQuery('.fb_pixel_id').length > 1 )
            jQuery( this ).closest( ".fb_pixel_id" ).remove();
    });

    jQuery(document).on('change', '.button_type', function(e){
        e.preventDefault();
        var val = jQuery(this).val();
        if( val != '' && val != null ){
            var sel = jQuery(this).attr('data-selected');
            var numbs = jQuery('option:selected', this).attr('data-number');
            numbs = decodeURIComponent(numbs);
            numbs = JSON.parse(numbs);

            if( numbs.length > 0 ){
                var option = '';
                jQuery.each( numbs, function(key, val){
                    if( val.number == sel ){
                        option = option+'<option value="'+val.number+'" data-label="'+val.label+'" selected>'+val.number+' - '+val.label+'</option>';
                    }
                    else{
                        option = option+'<option value="'+val.number+'" data-label="'+val.label+'">'+val.number+' - '+val.label+'</option>';
                    }
                } );

                jQuery(this).parents('.button-contact-chat').find('.button_number').html(option);
                jQuery(this).parents('.button-contact-chat').find('.button_number').trigger('change');
            }
        }
    });

    jQuery(document).on('change', '.twwr-whatsapp-button-type', function(e){
        e.preventDefault();
        var val = jQuery(this).val();
        
        if( val == 'icon-text' ){
            jQuery('.twwr-whatsapp-button-text').show();
        }
        else{
            jQuery('.twwr-whatsapp-button-text').hide();
        }
    });

    jQuery(document).on('change', '.button_number', function(e){
        e.preventDefault();
        var label = jQuery('option:selected', this).attr('data-label');

        jQuery(this).parents('.button-contact-chat').find('.button_label').val( label );
    });

    jQuery(document).on('change', '.twwr_whatsapp_pixel_events', function(e){
        e.preventDefault();
        var val = jQuery(this).val();

        if (val == 'Custom'){
            jQuery( '.twwr_whatsapp_custom_event' ).prop("disabled", false);
            jQuery( '.twwr_whatsapp_custom_event' ).show();
        }
        else{
            jQuery( '.twwr_whatsapp_custom_event' ).prop("disabled", true);
            jQuery( '.twwr_whatsapp_custom_event' ).hide();
        }
    });

    jQuery(document).on('change', '#filter-by-date', function(e){
        e.preventDefault();
        var val = jQuery(this).val();

        if (val == 'custom'){
            jQuery( '.custom-dates' ).show();
        }
        else{
            jQuery( '.custom-dates' ).hide();
        }
    });

    jQuery(document).on('click', '#post-query-submit', function(e){
        jQuery( '.custom-dates select' ).attr('name', '');
        jQuery( '#twwr-whatsapp-chat-report-filter').submit();
    });

    jQuery(document).on('click', '.twwr-whatsapp-do-delete', function(e){
        e.preventDefault();
        var val = jQuery('.twwr-whatsapp-option-delete').val();
        var nonce = jQuery('#_wpnonce').val();

        jQuery.ajax({
            async: true,
            url: twwr_whatsapp_admin.ajax_url,
            type: 'POST',
            data: 'action=twwr_do_delete&time=' + val + '&nonce=' + nonce,
            success: function(results) {
                jQuery('.twwr-whatsapp-delete-message').html(results);
                setTimeout(function(){
                    location.reload();
                }, 500);
            }
        });
    });
})(jQuery);