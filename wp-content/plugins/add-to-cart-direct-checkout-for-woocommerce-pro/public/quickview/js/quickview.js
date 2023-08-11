
jQuery(document).ready(function ($) {

    //shop page button click 
    $(document).on('click', ".quick_view", function () {

        var product_id = $(this).data('product-id');

        $.magnificPopup.open({
            items: {
                src: pisol_frontend_obj.ajaxurl + "?action=pisol_get_product&product_id=" + product_id,
                type: "ajax",
                showCloseBtn: true,
                closeOnContentClick: false,
                closeOnBgClick: false,
                mainClass: 'mfp-fade',
                removalDelay: 300,
            },
            callbacks: {
                ajaxContentAdded: function () {
                    var form_variation = jQuery('body').find('.variations_form');
                    form_variation.wc_variation_form();
                    jQuery('body').find('.woocommerce-product-gallery').flexslider({
                        selector: '.woocommerce-product-gallery__wrapper > .woocommerce-product-gallery__image',
                        start: function () {
                            jQuery('body').find('.woocommerce-product-gallery').css('opacity', 1);
                        },
                        controlNav: 'thumbnails',
                        slideshow: false,
                        smoothHeight: false

                    });
                }
            }
        });

    });

});