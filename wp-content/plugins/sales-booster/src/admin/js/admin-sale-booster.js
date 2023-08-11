(function($) {
/**
 *   inquire us button
 */ 
    function inquireUsButton(value){
        if(value == true){
            $("#_sale_booster_inquire").show();
        } else {
            $("#_sale_booster_inquire").hide();
        }
    }
    //inquire us 
    var inquire_us =  $('input[name=_sale_booster_inquire_us]:checked').val();
        inquire_us = inquire_us ? true : false
    inquireUsButton(inquire_us);
    // when inquire us change
    $("#_sale_booster_inquire_us").change(function(e){
        var value = e.target.checked; 
        inquireUsButton(value);
    });
   

/**
 * inquire us custom html box
 */ 
    function inquireUsCustomHtml(value){
        if(value === 'custom_html'){
            $("#_sale_booster_inquire_us_custom_html_text_box").show();
        } else {
            $("#_sale_booster_inquire_us_custom_html_text_box").hide();
        }
    }
    var customHtmlValue = $("#_sale_booster_inquire_us_form").val();
    inquireUsCustomHtml(customHtmlValue);
    // inquire us custom html change
    $("#_sale_booster_inquire_us_form").change(function(e){
        var customHTML = e.target.value;
        inquireUsCustomHtml(customHTML);
    });
    
/**
 * Exit Popup
 */
    function exitPopup(value){
        if(value === 'custom_html'){
            $("#_sale_booster_exit_custom_html_text_box").show();
        } else {
            $("#_sale_booster_exit_custom_html_text_box").hide();
        }
    }
    var exitPopupValue = $("#_sale_booster_exit_popup").val();
    exitPopup(exitPopupValue);
    // exit_popup change
    $("#_sale_booster_exit_popup").change(function(e){
        var popUpValue = e.target.value;
        exitPopup(popUpValue);
    });

/**
 * 
 * discount Timer
 */
    function checkDomElementsForTimer(value) {
        var userBasePromo = $('#sales_booster_user_time_promo');
        userBasePromo.hide();
        if(value === 'fixed_date'){
            $("#_sale_booster_discounttimer_showhide").show();
            $("#_sale_booster_fixed_date").show();
            $("#_sale_booster_user_based_timer").hide();
        } else if( value === 'user_based_time'){
            if(userBasePromo.length) {
                $('#_sale_booster_discounttimer_showhide').hide();
                userBasePromo.show();
                return;
            }
            $("#_sale_booster_discounttimer_showhide").show();
            $("#_sale_booster_fixed_date").hide();
            $("#_sale_booster_user_based_timer").show();
        }
        else {
            $("#_sale_booster_discounttimer_showhide").hide();
        }
    }
    var discountTimerVars = jQuery('input[name=_sale_booster_discount_timer]:checked').val();
    checkDomElementsForTimer(discountTimerVars);
    // when discount Timer change
    $("._sale_booster_discount_timer_field").change(function(e){
        var value = e.target.defaultValue;
        checkDomElementsForTimer(value);
    }); 
   
/**
 * date picker
 */
    $('#_sale_booster_expire_date_time').datetimepicker({
        format:'Y/m/d H:i:s',
        minDate: 0
    });
  
})( jQuery );
