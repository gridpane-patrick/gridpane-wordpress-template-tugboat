<input type="hidden" disabled data-mfVersion="<?php echo MYFATOORAH_WOO_PLUGIN_VERSION; ?>"/>
<script>var hideMF = false;</script>
<?php
$this->get_parent_payment_fields();


if ($this->listOptions === 'multigateways') {
    if ($this->count == 1 && $this->gateways['all'][0]->PaymentMethodCode == 'ap') {
        ?>
        <script>
            var hideMF = true;
        </script>
        <?php
    }
    if ($this->count > 1) {
        $key = 0;
        foreach ($this->gateways['all'] as $gateway) {
            $checked = ($key == 0) ? 'checked' : '';
            $key++;

            $label   = ($this->lang == 'ar') ? $gateway->PaymentMethodAr : $gateway->PaymentMethodEn;
            $radioId = 'mf-radio-' . $gateway->PaymentMethodId;
            ?>
            <span class="mf-div mf_<?php echo $gateway->PaymentMethodCode; ?>" style="margin: 20px; display: inline-flex;">
                <input class="mf-radio" <?php echo $checked; ?> type="radio"  id="<?php echo $radioId; ?>" name="mf_gateway" value="<?php echo $gateway->PaymentMethodId; ?>" style="margin: 5px; vertical-align: top;"/>
                <label id="mf-label<?php echo $radioId; ?>" for="<?php echo $radioId; ?>">
                    <?php echo $label; ?>&nbsp;
                    <img class="mf-img" src="<?php echo $gateway->ImageUrl; ?>" alt="<?php echo $label; ?>" style="margin: 0px; width: 50px; height: 30px;"/>
                </label>
            </span>
            <?php
        }
    } else if ($this->count == 1) {
        ?>
        <input type="hidden" name="mf_gateway" value="<?php echo $this->gateways['all'][0]->PaymentMethodId; ?>"/>
        <?php
    } else if ($this->count == 0) {
        ?>
        <script>
            jQuery('.payment_method_myfatoorah_v2').hide();
        </script>
        <?php
    }
}
?>
<script>
    if (!window.ApplePaySession) {
        jQuery('.mf_ap').remove();
        if (typeof hideMF !== undefined && hideMF === true) {
            jQuery('.payment_method_myfatoorah_v2').hide();
        }
    }
</script>