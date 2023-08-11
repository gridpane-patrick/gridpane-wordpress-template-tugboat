(function ($, config) {
    var boostApp = {
        config: config,

        setCookie: function(name,value,days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days*24*60*60*1000));
                expires = "; expires=" + date.toUTCString();
            }
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

        getUserLandedTimeSpan: function() {
            var cookieParam = '_boost_product_'+this.config.product_id;
            var landedTimeStamp = this.getCookie(cookieParam);

            var currentTimeStamp = Math.round((new Date()).getTime() / 1000);

            if(!landedTimeStamp) {
                this.setCookie(cookieParam, currentTimeStamp, 1);
                return 0;
            }
            return  Math.round(currentTimeStamp) - Math.round((landedTimeStamp));
        },

        initClock: function($wrapper, timeLeft) {
            if(timeLeft < 0) {
                $('._sale_booster_countdown_wrap').hide();
            }
            var clockInnerHtml = "<div class='_sale-discount-countdown-timer'>" +
                "   <span class='_sale-discount-time _timer_days'></span>" +
                "   <span class='_sale-discount-units'>"+this.config.trans.days+"</span>" +
                "</div>" +
                "<div class='_sale-discount-countdown-timer'>" +
                "  <span class='_sale-discount-time _timer_hours'></span>" +
                "  <span class='_sale-discount-units'>"+this.config.trans.hours+"</span>" +
                "</div>" +
                "<div class='_sale-discount-countdown-timer'>" +
                "   <span class='_sale-discount-time _timer_minutes'></span>" +
                "   <span class='_sale-discount-units'>"+this.config.trans.minutes+"</span>" +
                "</div>" +
                "<div class='_sale-discount-countdown-timer'>" +
                "   <span class='_sale-discount-time _timer_seconds'></span>" +
                "   <span class='_sale-discount-units'>"+this.config.trans.seconds+"</span>" +
                "</div>"
            $wrapper.html(clockInnerHtml);
            function updateTime() {
                timeLeft = timeLeft - 1;
                if(timeLeft < 0) {
                    $wrapper.closest('._sale_booster_countdown_wrap').hide();
                    if($wrapper.hasClass('_sale_top_clock')) {
                        $('body').removeClass('_sale_top_clock_loaded');
                    }
                    return;
                }

                $wrapper.closest('._sale_booster_countdown_wrap').show();
                if($wrapper.hasClass('_sale_top_clock')) {
                    $('body').addClass('_sale_top_clock_loaded');
                }

                var days = Math.floor(timeLeft / 86400);
                var hours = Math.floor((timeLeft - (days * 86400)) / 3600);
                var minutes = Math.floor((timeLeft - (days * 86400) - (hours * 3600 )) / 60);
                var seconds = Math.floor((timeLeft - (days * 86400) - (hours * 3600) - (minutes * 60)));
                if (hours < "10") { hours = "0" + hours; }
                if (minutes < "10") { minutes = "0" + minutes; }
                if (seconds < "10") { seconds = "0" + seconds; }

                if(days == 0 || days == '0') {
                    $wrapper.find('._timer_days').parent().hide();
                }

                $wrapper.find('._timer_days').html(days);
                $wrapper.find('._timer_hours').html(hours);
                $wrapper.find('._timer_minutes').html(minutes);
                $wrapper.find('._timer_seconds').html(seconds);
            }
            updateTime();
            setInterval(updateTime, 1000);
        },

        initTimers: function() {
            var clocks = $('._sale-booster-countdown');
            var timestamp = this.config.timerSeconds;
            // now we have to calculate the timestamp here
            if(this.config.type == 'user_based_time') {
                let timeSpent = this.getUserLandedTimeSpan();
                timestamp = timestamp - timeSpent;
            }
            if(!timestamp) {
                return;
            }

            var that = this;
            $.each(clocks, function (index, clock) {
                $clock = $(clock);
                that.initClock($clock, timestamp);
            });
        },
        
        init: function () {
            this.initTimers();
        }
    }
    boostApp.init();
})(jQuery, window.sale_booster_countdown_vars);


