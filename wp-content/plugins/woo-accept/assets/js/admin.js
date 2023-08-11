jQuery(document).ready(function () {
    // Apply Saved data
    if (iframe_id) {
        jQuery('#woocommerce_' + method_string + '_iframe_id').html('<option value="' + iframe_id + '" selected="selected">' + iframe_id + '</option><optgroup label="Available Frame IDs." id="accept-frame-list"> <option disabled="disabled">Nothing.</option> </optgroup>');
    } else {
        jQuery('#woocommerce_' + method_string + '_iframe_id').html('<optgroup label="Available Frame IDs." id="accept-frame-list"> <option disabled="disabled">Nothing.</option> </optgroup>');
    }
    if (integration_id) {
        jQuery('#woocommerce_' + method_string + '_integration_id').html('<option value="' + integration_id + '" selected="selected">' + integration_id + '</option><optgroup label="Available Integration IDs." id="accept-int-list"> <option disabled="disabled">Nothing.</option> </optgroup>');
    } else {
        jQuery('#woocommerce_' + method_string + '_integration_id').html('<optgroup label="Available Integration IDs." id="accept-int-list"> <option disabled="disabled">Nothing.</option> </optgroup>');
    }

    // Log
    function _log(status, msg) {
        document.querySelector('.acceptLoader').style.display = 'flex';
        jQuery('.acceptLoader .error, .acceptLoader .success, .acceptLoader .spinnerac').hide(250, function () {
            if (status == 'err') { jQuery('.acceptLoader .error').show(); }
            if (status == 'suc') { jQuery('.acceptLoader .success').show(); }
            if (status == 'spin') { jQuery('.acceptLoader .spinnerac').show(); }
        });

        jQuery('.acceptLoader .detail').append(msg);
    }

    // copy to clipboard
    jQuery('.callback_copy').click(function (ev) {
        ev.preventDefault();
        var callback = document.createElement("input");
        document.body.appendChild(callback);
        callback.setAttribute("id", "callback_url_accepting");
        document.getElementById("callback_url_accepting").value = url;
        callback.select();
        document.execCommand("copy");
        document.body.removeChild(callback);
        _log('suc', "Copied <br><span class='button-link' style='color:#03a9f4;'>" + url + "</span><br> to clipboard!<hr>")
        jQuery("html, body").animate({ scrollTop: 0 }, 750);
    });

    jQuery('#accept-login').click(function () { _login(); });

    _login();

    function _login() {
        var api_key = jQuery('#woocommerce_' + method_string + '_api_key').val();
        if (api_key.length == 0) {
            _log('err', 'Please Enter your "API KEY"<hr>Waiting for key..<hr>');
        } else {
            _log('spin', '<hr>Loading..<hr>');
            var details = {
                "api_key": jQuery('#woocommerce_' + method_string + '_api_key').val(),
            };
            var requestData = JSON.stringify(details);
            var target = "https://accept.paymobsolutions.com/api/auth/tokens";
            jQuery.ajax({
                method: "POST",
                contentType: 'application/json',
                url: target,
                data: requestData
            }).done(function (response) {
                _log('spin', 'API Key Accepted.<hr>');
                get_hmac(response.token)
                get_integration(response.token);
            }).fail(function (response) {
                console.log(response)
                _log('err', 'Please Enter a valid API KEY.');
            });
        }
    }
    
    function get_hmac(token){
        // get int ids
        jQuery.ajax({
            method: "GET",
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer '+ token,
            },
            url: "https://accept.paymobsolutions.com/api/auth/hmac_secret/get_hmac",
        }).done(function (response) {
            jQuery('#woocommerce_' + method_string + '_hmac_secret').val(response.hmac_secret);
            _log('suc', 'Loaded HMAC Secret.<hr>');
        }).fail(function (response){
            console.log(response)
            _log('err', 'Unable to get HMAC secret please manually enter it.<hr>');
        })
    }

    function get_integration(token) {
        // get int ids
        jQuery.ajax({
            method: "GET",
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer '+ token,
            },
            // url: "https://accept.paymobsolutions.com/api/ecommerce/integrations",
            url: "https://accept.paymobsolutions.com/api/ecommerce/integrations?page_size=20",
        }).done(function (response) {
            if (has_iframe == 1) {
                get_iframe(token);
                _log('spin', 'Loaded integration ids.<hr>');
            } else {
                _log('suc', 'Loaded integration ids.<hr>');
            }
            jQuery('#accept-int-list').html("");

            jQuery.each(response.results, function (i, integration) {
                var type = integration.gateway_type;
                var status= integration.is_live;
                if(integration.gateway_type == "VPC"){
                  type = "Card";
                }
                if(integration.gateway_type == "CAGG"){
                  type = "Aman";
                }
                if(integration.gateway_type == "UIG"){
                  type = "Wallet";
                }
                if(integration.is_live==false){
                    status= "Test";
                }
                if(integration.is_live==true){
                    status= "Live";
                }
                if(integration.is_standalone== false){
 
                jQuery('#accept-int-list').append(jQuery('<option>', {
                    value: integration.id,
                    text : integration.id+' - '+integration.currency+' - '+type +' -> ' +status
                }))};
            });
        }).fail(function (response) {
            console.log(response)
            _log('err', 'Unable to get integration ids.<hr>');
        });
    }

    function get_iframe(token) {
        // get iframes
        jQuery.ajax({
            method: "GET",
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer '+ token,
            },
            url: "https://accept.paymobsolutions.com/api/acceptance/iframes",
        }).done(function (response) {
            _log('suc', 'Loaded iframe ids.<hr>');
            jQuery('#accept-frame-list').html("");
            jQuery.each(response.results, function (i, iframe) {
                jQuery('#accept-frame-list').append(jQuery('<option>', {
                    value: iframe.id,
                    text: iframe.id
                }));
            });
        }).fail(function (response) {
            console.log(response)
            _log('err', 'Unable to get iframe ids.<hr>');
        });
    }
});