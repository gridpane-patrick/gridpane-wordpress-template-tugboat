<script>
    jQuery(document).ready(function ($) {
        if (window.ApplePaySession) {
            var config = {
                countryCode: "<?php echo $this->session->CountryCode; ?>",
                sessionId: "<?php echo $this->session->SessionId; ?>",
                currencyCode: "<?php echo $this->gateways['ap']->GatewayData['GatewayCurrency']; ?>", // Here, add your Currency Code.
                amount: "<?php echo $this->gateways['ap']->GatewayData['GatewayTotalAmount']; ?>", // Add the invoice amount.
                cardViewId: "mf-apple-button",
                callback: myFatoorahPayment
            };

            myFatoorahAP.init(config);

            function myFatoorahPayment(response) {
                var fc = 'form.checkout';
                $(fc).append('<input type="hidden" id="mfData" name="mfData" value="' + response.sessionId + '">');
                $(fc).submit();
            }
        }
    });
</script>
