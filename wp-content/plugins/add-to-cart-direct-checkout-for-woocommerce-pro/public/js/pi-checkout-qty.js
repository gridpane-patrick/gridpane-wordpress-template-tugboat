(function ($) {
    'use strict';

    jQuery(function ($) {
        jQuery("form.checkout").on("change", "input.qty", function () {
            pisol_dcw_block_ui();
            var data = {
                action: "pisol_dcw_update_quantity",
                post_data: jQuery("form.checkout").serialize()
            };

            jQuery.post(pisol_dcw_setting.ajax_url, data, function (response) {
                if (response.reload) {
                    location.reload();
                } else {
                    jQuery("body").trigger("update_checkout");
                }
            }).fail(function () {
                pisol_dcw_unblock_ui();
            });
        });

        jQuery("form.checkout").on("click", ".remove", function (e) {
            e.preventDefault()
            pisol_dcw_block_ui();
            var data = {
                action: "pisol_dcw_remove_item",
                url: jQuery(this).attr("href")
            };

            jQuery.post(pisol_dcw_setting.ajax_url, data, function (response) {
                if (response.reload) {
                    location.reload();
                }

                if (response.success) {
                    jQuery("body").trigger("update_checkout");
                }

            }).fail(function () {
                pisol_dcw_unblock_ui();
            });
        });

        function pisol_dcw_unblock_ui() {
            jQuery(".woocommerce-checkout-payment, .woocommerce-checkout-review-order-table").unblock();
        }

        function pisol_dcw_block_ui() {
            jQuery(".woocommerce-checkout-payment, .woocommerce-checkout-review-order-table").block({
                message: null,
                overlayCSS: {
                    background: "#fff",
                    opacity: .6
                }
            })
        }
    });

})(jQuery);