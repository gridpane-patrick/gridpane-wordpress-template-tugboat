jQuery(document).ready(function ($) {
    'use strict';
    $(document).on('change', '#product-type', function () {
        let $simple_attributes = $('.vi-wad-original-attributes-simple');
        if ($(this).val() === 'variable') {
            $simple_attributes.fadeOut(200)
        } else {
            $simple_attributes.fadeIn(200)
        }
    }).trigger('change');
    $(document).on('change', '.vi-wad-original-attributes-select', function () {
        let $sku_attr = $(this),
            $sku_id = $sku_attr.closest('.vi-wad-original-attributes').find('.vi-wad-original-variation-id');
        if ($sku_attr.val()) {
            $sku_id.val($sku_attr.find(':selected').data('vi_wad_sku_id'));
        } else {
            $sku_id.val('');
        }

    }).trigger('change');
    $('.ald-video-shortcode').on('click', function () {
        $(this).select();
        document.execCommand('copy');
        villatheme_admin_show_message(vi_wad_admin_product_params.i18n_video_shortcode_copied, 'success', '', false, 5000);
    });
});