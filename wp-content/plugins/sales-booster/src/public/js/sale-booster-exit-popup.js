(function($, config){
    var boostExitPopupApp = {
       config: config,
       showPopUp: true,
       exitPopup: function()  {
            $(document).mousemove((e) => {
                if(e.pageY <= 5)
                {
                    if(!this.showPopUp){
                      return;
                    }
                    $("#_sale_booster_popup").show();
                }
            });
        },
        // close popup
        closeExitPopup: function() {
            $("#_sale_booster_popup_close").on('click', () => {
                $("#_sale_booster_popup").hide();
                this.showPopUp = false
                this.setCookie();
            })
        },
        
        getCookieCheck: function(){
            var cookieParam = '_boost_product_exit_popup_'+ this.config.productId;
            var cookieChecking = this.getCookie(cookieParam);
            if(!cookieChecking){
                this.exitPopup();
            }
        },

        setCookie: function() {
            var name = '_boost_product_exit_popup_'+ this.config.productId,
            value    = true,
            days     = 1
            var expires = "";
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
            document.cookie = name + "=" + (value || "")  + expires + "; path=/";
        },

        getCookie: function(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }
            return null;
        },

        init: function() {
            this.getCookieCheck();
            this.closeExitPopup();
        }
    }
    boostExitPopupApp.init();

})(jQuery, window.sale_booster_exit_popup_vars);

