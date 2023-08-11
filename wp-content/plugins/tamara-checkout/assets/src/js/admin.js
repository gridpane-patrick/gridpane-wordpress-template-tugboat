(function ($) {
    $('document').ready(function () {

        let tamaraEnvToggle = $('#woocommerce_tamara-gateway_environment');
        let valueSelected;
        const liveApiUrl = 'https://api.tamara.co';
        const sandboxApiUrl = 'https://api-sandbox.tamara.co';

        // Display/Hide fields on selected
        function getValueSelected() {
            valueSelected = tamaraEnvToggle.val();
            let liveApiUrlEl = $('#woocommerce_tamara-gateway_live_api_url');
            let liveApiTokenEl = $('#woocommerce_tamara-gateway_live_api_token');
            let liveNotifKeyEl = $('#woocommerce_tamara-gateway_live_notification_token');
            let livePublicKeyEl = $('#woocommerce_tamara-gateway_live_public_key');
            let sandboxApiUrlEl = $('#woocommerce_tamara-gateway_sandbox_api_url');
            let sandboxApiTokenEl = $('#woocommerce_tamara-gateway_sandbox_api_token');
            let sandboxNotifKeyEl = $('#woocommerce_tamara-gateway_sandbox_notification_token');
            let sandboxPublicKeyEl = $('#woocommerce_tamara-gateway_sandbox_public_key');

            if ('live_mode' === valueSelected) {
                liveApiUrlEl.closest('tr').css('display', 'table-row');
                liveApiUrlEl.attr('required', 'required');
                liveApiTokenEl.closest('tr').css('display', 'table-row');
                liveApiTokenEl.attr('required', 'required');
                liveNotifKeyEl.closest('tr').css('display', 'table-row');
                liveNotifKeyEl.attr('required', 'required');
                livePublicKeyEl.closest('tr').css('display', 'table-row');
                // livePublicKeyEl.attr('required', 'required');

                sandboxApiUrlEl.closest('tr').css('display', 'none');
                sandboxApiUrlEl.attr('required', false);
                sandboxApiTokenEl.closest('tr').css('display', 'none');
                sandboxApiTokenEl.attr('required', false);
                sandboxNotifKeyEl.closest('tr').css('display', 'none');
                sandboxNotifKeyEl.attr('required', false);
                sandboxPublicKeyEl.closest('tr').css('display', 'none');
                sandboxPublicKeyEl.attr('required', false);

                if (!liveApiUrlEl.val()) {
                    liveApiUrlEl.val(liveApiUrl);
                }

            } else if ('sandbox_mode' === valueSelected) {
                liveApiUrlEl.closest('tr').css('display', 'none');
                liveApiUrlEl.attr('required', false);
                liveApiTokenEl.closest('tr').css('display', 'none');
                liveApiTokenEl.attr('required', false);
                liveNotifKeyEl.closest('tr').css('display', 'none');
                liveNotifKeyEl.attr('required', false);
                livePublicKeyEl.closest('tr').css('display', 'none');
                livePublicKeyEl.attr('required', false);

                sandboxApiUrlEl.closest('tr').css('display', 'table-row');
                sandboxApiUrlEl.attr('required', 'required');
                sandboxApiTokenEl.closest('tr').css('display', 'table-row');
                sandboxApiTokenEl.attr('required', 'required');
                sandboxNotifKeyEl.closest('tr').css('display', 'table-row');
                sandboxNotifKeyEl.attr('required', 'required');
                sandboxPublicKeyEl.closest('tr').css('display', 'table-row');
                // sandboxPublicKeyEl.attr('required', 'required');

                if (!sandboxApiUrlEl.val()) {
                    sandboxApiUrlEl.val(sandboxApiUrl);
                }
            }
        }

        // Get the selected value on change
        tamaraEnvToggle.change(getValueSelected);

        // Get the selected value on save
        $(window).load(getValueSelected);

        /*
         Hide Help texts field and show/hide on toggle
        */
        document.addEventListener('click', function (e) {
            // loop parent nodes from the target to the delegation node
            for (let target = e.target; target && target !== this; target = target.parentNode) {
                if (target.matches('.tamara-settings-help-texts__manage')) {
                    $('.tamara-settings-help-texts__manage').toggleClass('tamara-opened');
                    $('.tamara-settings-help-texts__manage').parent().find('.tamara-settings-help-texts__content').slideToggle();
                    break;
                }
            }
        }, false);

        /*
         Trigger buttons in Admin
        */
        triggerManageButton('tamara-order-statuses-mappings-manage');
        triggerManageButton('tamara-order-statuses-trigger-manage');
        triggerManageButton('tamara-advanced-settings-manage');
        triggerManageButton('tamara-custom-settings-manage');
        triggerDebugButton('debug-info-manage');
        tamaraEnvToggle.closest('table').addClass('widefat tamara-setting-table')
        $('.tamara-payinx').closest('table').addClass('widefat tamara-setting-table')
        $('.tamara-settings-help-texts__manage').parent().find('.tamara-settings-help-texts__content').addClass('tamara-widefat');
    });

    /*
     Function to trigger Pay In X Option button
     */
    function triggerPayInXOption(country, currency) {
        let buttonPayInX = [];
        buttonPayInX[currency] = $('.tamara-payinx-' + currency + '-manage')
        let buttonPayInXNextP = buttonPayInX[currency].next('p')
        buttonPayInXNextP.next('table').find('tbody').addClass('tamara-display-block')
        // Global event listener
        document.addEventListener('click', function (e) {
            // Loop parent nodes from the target to the delegation node
            for (let target = e.target; target && target !== this; target = target.parentNode) {
                if (target.matches('.tamara-payinx-' + currency + '-manage')) {
                    buttonPayInXNextP.next('table').find('tbody').slideToggle();
                    break;
                }
            }
        }, false);

        // Hide option fields if payment type unavailable
        let buttonPayInXClass = [];
        let buttonPayInXNoteClass = [];
        let buttonPayInXId = [];
        buttonPayInXClass[currency] = $('.tamara-payinx-' + country);
        buttonPayInXNoteClass[currency] = $('.pay-in-x-' + currency + '-note');
        buttonPayInXId[currency] = $('#woocommerce_tamara-gateway_pay_by_instalments_options_' + currency);
        if (!$(buttonPayInXClass[currency]).length) {
            buttonPayInX[currency].hide();
            buttonPayInXNoteClass[currency].hide();
            buttonPayInXId[currency].hide();
        }
    }

    /*
     Function to trigger genetal setting option buttons
     */
    function triggerManageButton(className) {
        // Show/hide trigger events order statuses options on toggle
        let buttonTrigger = $('.' + className).next('p')
        buttonTrigger.next('table').addClass('widefat tamara-setting-table');
        buttonTrigger.next('table').find('tbody').addClass('tamara-display-block');
        buttonTrigger.next('table').find('tbody').hide();
        // Global event listener
        document.addEventListener('click', function (e) {
            // Loop parent nodes from the target to the delegation node
            for (let target = e.target; target && target !== this; target = target.parentNode) {
                if (target.matches('.' + className)) {
                    $('.' + className).toggleClass('tamara-opened');
                    buttonTrigger.next('table').find('tbody').slideToggle();
                    break;
                }
            }
        }, false);
    }

    /*
     Function to trigger genetal setting option buttons
     */
    function triggerDebugButton(className) {
        // Show/hide trigger events order statuses options on toggle
        let buttonTrigger = $('.' + className).next('p')
        buttonTrigger.next('table').addClass('widefat tamara-setting-table');
        buttonTrigger.next('table').find('tbody').addClass('tamara-display-block');
        buttonTrigger.next('table').find('tbody').hide();
        $('#woocommerce_tamara-gateway_debug_info_text').hide();

        // Global event listener
        document.addEventListener('click', function (e) {
            // Loop parent nodes from the target to the delegation node
            for (let target = e.target; target && target !== this; target = target.parentNode) {
                if (target.matches('.' + className)) {
                    $('.' + className).toggleClass('tamara-opened');
                    buttonTrigger.next('table').find('tbody').slideToggle();
                    break;
                }
            }
        }, false);
    }

})(jQuery);
