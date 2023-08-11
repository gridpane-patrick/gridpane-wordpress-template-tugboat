(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */
  jQuery(function ($) {
    is_redirect_enabled();
    jQuery("#pi_dcw_global_redirect_custom_url").change(function () {
      global_redirect_toggle();
    });

    jQuery("#pi_dcw_global_redirect").change(function () {
      is_redirect_enabled();
    });

    addtocarttext();

    disableWhenChecked("#pi_dcw_product_redirect", "#pisol-enabled-redirect");
    enableWhenChecked("#pi_dcw_product_overwrite_global", "#pisol-set-url");
    enableWhenChecked("#pi_dcw_enable_checkout_redirect", "#pisol-dcw-checkout-redirect-setting");

    enableWhenChecked("#pi_dcw_add_link_to_checkout_product_name", "#row_pi_dcw_checkout_link_new_tab");

    enableWhenChecked("#pisol_dcw_remove_other_product", "#row_pisol_dcw_remove_event_same_product");

    showIfPageEmpty('#pi_dcw_checkout_redirect_to_page', '#row_pi_dcw_custom_checkout_redirect_url');

    if (typeof jQuery.fn.selectWoo != 'undefined') {
      jQuery("#pi_dcw_remove_billing_field, #pi_dcw_remove_shipping_field").selectWoo();
    }

  });

  function global_redirect_toggle() {
    var $ = jQuery;

    if ($("#pi_dcw_global_redirect_custom_url").is(":checked")) {
      $("#row_pi_dcw_global_redirect_to_page").fadeOut();
      $("#row_pi_dcw_global_custom_url").fadeIn();
    } else {
      $("#row_pi_dcw_global_custom_url").fadeOut();
      $("#row_pi_dcw_global_redirect_to_page").fadeIn();
    }
  }

  function is_redirect_enabled() {
    if ($("#pi_dcw_global_redirect").is(":checked")) {
      $("#row_pi_dcw_global_redirect_custom_url").fadeIn();
      global_redirect_toggle();
    } else {
      $("#row_pi_dcw_global_redirect_to_page").fadeOut();
      $("#row_pi_dcw_global_custom_url").fadeOut();
      $("#row_pi_dcw_global_redirect_custom_url").fadeOut();
    }
  }


  function addtocarttext() {
    jQuery("#pi_dcw_change_add_to_cart").change(function () {
      if ($("#pi_dcw_change_add_to_cart").is(":checked")) {
        $("#row_pi_dcw_add_to_cart_text").fadeIn();
        $("#row_pi_dcw_read_more_text").fadeIn();
        $("#row_pi_dcw_select_option_text").fadeIn();
      } else {
        $("#row_pi_dcw_add_to_cart_text").fadeOut();
        $("#row_pi_dcw_read_more_text").fadeOut();
        $("#row_pi_dcw_select_option_text").fadeOut();
      }
    });
    jQuery("#pi_dcw_change_add_to_cart").trigger('change');
  }

  function disableWhenChecked(checkbox, row) {
    jQuery(checkbox).change(function () {
      if (jQuery(checkbox).is(":checked")) {
        jQuery(row).fadeOut();
      } else {
        jQuery(row).fadeIn();
      }
    });
    jQuery(checkbox).trigger('change');
  }

  function enableWhenChecked(checkbox, row) {
    jQuery(checkbox).change(function () {
      if (jQuery(checkbox).is(":checked")) {
        jQuery(row).fadeIn();
      } else {
        jQuery(row).fadeOut();
      }
    });
    jQuery(checkbox).trigger('change');
  }

  function showIfPageEmpty(parent, row) {
    jQuery(parent).change(function () {
      if (jQuery(this).val() == '') {
        jQuery(row).fadeIn();
      } else {
        jQuery(row).fadeOut();
      }
    });
    jQuery(parent).trigger('change');
  }

  function hideRedirectWhenUnCheckedVariation() {
    jQuery(document).on('change', '.pisol_dcw_handle_redirect_variation', function () {
      var loop = jQuery(this).data('loop_id');
      if (jQuery(this).is(":checked")) {
        jQuery(this).parent().next('.form-field').fadeIn();
      } else {
        jQuery(this).parent().next('.form-field').fadeOut();
      }
    })
  }

  function hideThankyouVariation() {
    jQuery(document).on('change', '.pisol_dcw_handle_thankyou_variation', function () {
      var loop = jQuery(this).data('loop_id');
      if (jQuery(this).is(":checked")) {
        jQuery(this).parent().next('.pisol-dcw-variation-thankyou-setting').fadeIn();
      } else {
        jQuery(this).parent().next('.pisol-dcw-variation-thankyou-setting').fadeOut();
      }
    })
  }


  jQuery(function ($) {
    hideRedirectWhenUnCheckedVariation();
    hideThankyouVariation();
    jQuery(document).on('woocommerce_variations_loaded', function () {
      jQuery('.pisol_dcw_handle_redirect_variation').trigger('change');
      jQuery('.pisol_dcw_handle_thankyou_variation').trigger('change');
    });
  });


})(jQuery);
