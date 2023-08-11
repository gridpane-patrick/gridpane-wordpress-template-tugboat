function _getQuery(name) {
    url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

jQuery(document).on('ready', function () {
    var PAYMENT_TOKEN = _getQuery('PAYMENT_TOKEN');
    var KIOSK_ID = _getQuery('KIOSK_ID');
    var IFRAME_ID = _getQuery('IFRAME_ID');

    if (PAYMENT_TOKEN !== null && IFRAME_ID !== null)
    {
        var o_url = "https://accept.paymobsolutions.com/api/acceptance/iframes/" + IFRAME_ID + "?payment_token=" + PAYMENT_TOKEN;
        jQuery('#accepting-container .accept_iframe').html('<iframe src="' + o_url + '"></iframe>').show(750);
    } 
    
    else if (KIOSK_ID !== null)
    {
        jQuery('#accepting-container .accept_kiosk #accept_kiosk_id').html(KIOSK_ID);
        jQuery('#accepting-container .accept_kiosk').show(750);
    }

    else
    {
        jQuery('#accepting-container .accept_error').show(750);
    }
});