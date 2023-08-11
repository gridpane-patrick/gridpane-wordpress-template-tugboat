(function($){

    var boostEnquiryUsApp = {

        //inquire us modal 
        inquireUsButtonModal: function() {
            var modal = $("#_sale_booster_inquire_us_modal").length;
            if(!modal){
                return;
            }
            $("#_sale_booster_inquire_us_btn").click(function(){
               $("#_sale_booster_inquire_us_modal").show();
            })

            $("._sale_booster_inquire_us_close").click(function(){
                $("#_sale_booster_inquire_us_modal").hide();
            })

            $(window).click(function(e) {
               if (e.target == _sale_booster_inquire_us_modal) {
                    $("#_sale_booster_inquire_us_modal").hide();
                }
            })
        },

        init: function(){
            this.inquireUsButtonModal();
        }
    } 
    boostEnquiryUsApp.init();

})(jQuery)