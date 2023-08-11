/* global wc_checkout_params */
jQuery(function ($) {

    // wc_checkout_params is required to continue, ensure the object exists
    if (typeof wc_checkout_params === 'undefined') {
        return false;
    }

    var c_form = {
        update_timeout: null,
        in_order_review: $(document.body).hasClass('woocommerce-order-pay'),
        order_review: $('#order_review'),
        checkout_form: $('form.checkout'),

        // Current payment information
        moyasar_form_instance: null,
        current_payment_id: null,
        proceed_callback: null,
        abort_callback: null,
        moyasar_form_load_timeout: null,
        moyasar_form_load_attempts: 0,

        // Business Logic
        init: function () {
            c_form.checkout_form.on(
                'change',
                'input[name="payment_method"]',
                c_form.on_method_changes
            );

            if (c_form.in_order_review) {
                c_form.order_review.on('change', 'input[name="payment_method"]', c_form.on_method_changes);
                c_form.order_review.on('submit', c_form.submitOrderPayment);
                $(document.body).on('update_checkout', c_form.on_checkout_updated);
            }

            // Disable default woocommerce form submit, we will handle it ourselves
            // Works with woocommerce 3.0 and later
            c_form.checkout_form.on('checkout_place_order_moyasar-form', function () {
                return false;
            });

            c_form.checkout_form.on('submit', c_form.submit);
            $(document.body).on('updated_checkout', c_form.on_checkout_updated);

            $(document.body).trigger('update_checkout');
        },
        woo_submit_button: function () {
            if (c_form.in_order_review) {
                return c_form.order_review.find('#place_order');
            }

            return c_form.checkout_form.find('button[name=woocommerce_checkout_place_order]');
        },
        get_payment_endpoint: function (type) {
            var url = '/?rest_route=/moyasar/v2/payment/' + type;

            if (c_form.site_url) {
                url = c_form.site_url + url;
            }

            var id = /order-pay=(\d+)&?/.exec(document.location.href);
            var key = /key=(.+)&?/.exec(document.location.href);

            if (id && key) {
                url += '&order-pay=' + id[1] + '&key=' + key[1];
            }

            if (c_form.order_id && c_form.order_key) {
                url += '&order-pay=' + c_form.order_id + '&key=' + c_form.order_key;
            }

            return url;
        },
        build_shadow_root: function (target) {    
            var headElements = Object.entries(document.head.children).map(function (e) { return e[1]; });
            var sheetLink = headElements.find(e => e instanceof HTMLLinkElement && e.href.includes('moyasar.css'));
            if (!sheetLink) {
                return target;
            }

            var shadow = target.attachShadow({ mode: 'open' });
            
            var rootEl = document.createElement('div');
            shadow.appendChild(rootEl);

            var newLink = document.createElement('link');
            newLink.rel = 'stylesheet';
            newLink.href = sheetLink.href;
            rootEl.appendChild(newLink);

            var formEl = document.createElement('div');
            rootEl.appendChild(formEl);

            return formEl;
        },
        init_moyasar_form: function () {
            // Try for 10 seconds then avoid loading the form
            if ($('.mysr-form').length === 0) {
                c_form.moyasar_form_load_attempts++;

                if (c_form.moyasar_form_load_attempts > 10) {
                    return;
                }

                c_form.moyasar_form_load_timeout = setTimeout(c_form.init_moyasar_form, 100);
                return;
            }

            var formSettings = Object.assign({}, window.moyasar_form_data);

            var formElement = document.getElementById('mysr-form');
            if (formElement.attachShadow !== undefined) {
                formElement = c_form.build_shadow_root(formElement);
            }

            formSettings = Object.assign(formSettings, {
                element: formElement,

                on_completed: function (payment) {
                    return new Promise(function (resolve, reject) {
                        $.ajax({
                            type: 'POST',
                            url: c_form.get_payment_endpoint('initiated'),
                            data: payment,
                            success: function (data, statusText, request) {
                                if (request.status === 201) {
                                    resolve();
                                } else {
                                    reject("Could not save the payment, status returned: " + request.status);
                                }
                            },
                            error: function(request, statusText, errorThrown) {
                                try {
                                    reject(request.responseJSON.message || errorThrown);
                                } catch (e) {
                                    reject(e);
                                }
                            }
                        });
                    });
                },

                on_initiating: function () {
                    return new Promise(function (resolve, reject) {
                        c_form.proceed_callback = resolve;
                        c_form.abort_callback = reject;

                        if (c_form.in_order_review) {
                            c_form.order_review.submit();
                        } else {
                            c_form.checkout_form.submit();
                        }
                    });
                },

                on_failure: function (message) {
                    c_form.detachUnloadEventsOnSubmit();

                    if (message != null) {
                        c_form.submit_error('<div class="woocommerce-error">' + message + '</div>');
                    }

                    if (message instanceof Error) {
                        console.log(message);
                    }

                    // Cancel order at backend
                    $.ajax({
                        type: 'POST',
                        url: c_form.get_payment_endpoint('failed'),
                        data: {
                            message: message
                        },
                        success: function (result) {},
                        error:	function(jqXHR, textStatus, errorThrown) {}
                    });

                    if (c_form.in_order_review) {
                        c_form.order_review.removeClass('moy-processing').unblock();
                    }
                }
            });

            c_form.moyasar_form_instance = Moyasar.init(formSettings);

            // Why the timeout, well it's WordPress, do you expect for everything to go smoothly?
            setTimeout(function () {
                // Prevent jQuery Validation from working if it's there
                // Some plugins will just corrupt the form
                $('.mysr-form-button').attr('formnovalidate', 'formnovalidate');
            }, 800);
        },
        reset_current_payment_session: function () {
            c_form.moyasar_form_instance = null;
            c_form.current_payment_id = null;
            c_form.proceed_callback = null;
            c_form.abort_callback = null;
            c_form.order_id = null;
            c_form.order_key = null;

            if (c_form.moyasar_form_load_timeout) {
                clearTimeout(c_form.moyasar_form_load_timeout);
            }

            c_form.moyasar_form_load_timeout = null;
            c_form.moyasar_form_load_attempts = 0;

            c_form.init_moyasar_form();
        },
        on_method_changes: function (event) {
            var radioBtn = $(event.target);

            if (radioBtn.val() === 'moyasar-form') {
                c_form.woo_submit_button().hide();
            } else {
                c_form.woo_submit_button().show();
            }
        },
        clear_update_timer: function () {
            if (!c_form.update_timeout) {
                return;
            }

            clearTimeout(c_form.update_timeout);
        },
        on_checkout_updated: function(event, args) {
            c_form.clear_update_timer();
            c_form.update_timeout = setTimeout(c_form.tackle_changes, 50);
        },
        tackle_changes: function () {
            c_form.reset_current_payment_session();
            c_form.on_method_changes({ target: $('input[name="payment_method"]:checked') });
        },
        submitOrderPayment: function () {
            var $form = $(this);

            if ($form.is('.moy-processing')) {
                return false;
            }

            $form.removeClass('processing').unblock();
            $form.addClass('moy-processing')
            c_form.proceed_callback(true);

            // Prevent browser submit
            return false;
        },
        submit: function () {
            c_form.clear_update_timer();

            var $form = $(this);

            if ($form.is('.processing')) {
                return false;
            }

            $form.addClass('processing');

            c_form.blockOnSubmit($form);

            // Attach event to block reloading the page when the form has been submitted
            c_form.attachUnloadEventsOnSubmit();

            // ajaxSetup is global, but we use it to ensure JSON is valid once returned.
            $.ajaxSetup({
                dataFilter: function(raw_response, dataType) {
                    // We only want to work with JSON
                    if ('json' !== dataType) {
                        return raw_response;
                    }

                    if (c_form.is_valid_json(raw_response)) {
                        return raw_response;
                    } else {
                        // Attempt to fix the malformed JSON
                        var maybe_valid_json = raw_response.match(/{"result.*}/);

                        if (null === maybe_valid_json) {
                            console.log('Unable to fix malformed JSON');
                        } else if (c_form.is_valid_json(maybe_valid_json[0])) {
                            console.log('Fixed malformed JSON. Original: ');
                            console.log(raw_response);
                            raw_response = maybe_valid_json[0];
                        } else {
                            console.log('Unable to fix malformed JSON');
                        }
                    }

                    return raw_response;
                }
            });

            $.ajax({
                type:		'POST',
                url:		wc_checkout_params.checkout_url,
                data:		$form.serialize(),
                dataType:   'json',
                success:	function(result) {
                    // Detach unload handler that prevents a reload / redirect
                    c_form.detachUnloadEventsOnSubmit();

                    try {
                        if ('success' === result.result) {
                            c_form.order_id = result.order_id;
                            c_form.order_key = result.order_key;

                            c_form.proceed_callback({
                                // Remove any URL fragments
                                callback_url: c_form.callback_url = decodeURI(result.redirect).replace(/#[^&]+/g, ''),
                                description: window.moyasar_form_data.description.replace('TBD', result.order_id),
                                metadata: Object.assign({
                                    order_id: result.order_id,
                                    order_key: result.order_key
                                }, result.metadata)
                            });

                            if (c_form.in_order_review) {
                                c_form.order_review.removeClass('processing').unblock();
                            } else {
                                c_form.checkout_form.removeClass('processing').unblock();
                            }
                        } else if ('failure' === result.result) {
                            throw 'Result failure';
                        } else {
                            throw 'Invalid response';
                        }
                    } catch(err) {
                        // Reload page
                        if (true === result.reload) {
                            window.location.reload();
                            return;
                        }

                        // Trigger update in case we need a fresh nonce
                        if (true === result.refresh) {
                            $(document.body).trigger('update_checkout');
                        }

                        var message = null;

                        // Add new errors
                        if (result.messages) {
                            if (typeof c_form.abort_callback === 'function') c_form.abort_callback(null);
                            message = result.messages;
                        } else {
                            if (typeof c_form.abort_callback === 'function') c_form.abort_callback(null);
                            message = '<div class="woocommerce-error">' + wc_checkout_params.i18n_checkout_error + '</div>';
                        }

                        setTimeout(function () {
                            c_form.submit_error(message);
                        }, 1);
                    }
                },
                error:	function(jqXHR, textStatus, errorThrown) {
                    // Detach the unload handler that prevents a reload / redirect
                    c_form.detachUnloadEventsOnSubmit();
                    if (typeof c_form.abort_callback === 'function') c_form.abort_callback(null);
                    c_form.submit_error('<div class="woocommerce-error">' + errorThrown + '</div>');
                }
            });

            return false;
        },

        // Utils
        is_valid_json: function(raw_json) {
            try {
                var json = JSON.parse(raw_json);

                return (json && 'object' === typeof json);
            } catch (e) {
                return false;
            }
        },
        handleUnloadEvent: function(e) {
            // Modern browsers have their own standard generic messages that they will display.
            // Confirm, alert, prompt or custom message are not allowed during the unload event
            // Browsers will display their own standard messages

            // Check if the browser is Internet Explorer
            if((navigator.userAgent.indexOf('MSIE') !== -1) || (!!document.documentMode)) {
                // IE handles unload events differently than modern browsers
                e.preventDefault();
                return undefined;
            }

            return true;
        },
        attachUnloadEventsOnSubmit: function() {
            $(window).on('beforeunload', this.handleUnloadEvent);
        },
        detachUnloadEventsOnSubmit: function() {
            $(window).unbind('beforeunload', this.handleUnloadEvent);
        },
        blockOnSubmit: function($form) {
            var form_data = $form.data();

            if (1 !== form_data['blockUI.isBlocked']) {
                $form.block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
            }
        },
        submit_error: function(error_message) {
            $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();

            if (c_form.in_order_review) {
                c_form.order_review.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>'); // eslint-disable-line max-len
                c_form.order_review.removeClass('processing').unblock();
                c_form.order_review.find('.input-text, select, input:checkbox').trigger('validate').blur();
            } else {
                c_form.checkout_form.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>'); // eslint-disable-line max-len
                c_form.checkout_form.removeClass('processing').unblock();
                c_form.checkout_form.find('.input-text, select, input:checkbox').trigger('validate').blur();
            }

            c_form.scroll_to_notices();
            $(document.body).trigger('checkout_error' , [error_message]);
        },

        scroll_to_notices: function() {
            var scrollElement = $('.woocommerce-NoticeGroup-updateOrderReview, .woocommerce-NoticeGroup-checkout');

            if (! scrollElement.length) {
                scrollElement = c_form.checkout_form;
            }

            if (! scrollElement.length) {
                scrollElement = c_form.order_review;
            }

            $.scroll_to_notices(scrollElement);
        }
    };

    setTimeout(c_form.init, 1);
});
