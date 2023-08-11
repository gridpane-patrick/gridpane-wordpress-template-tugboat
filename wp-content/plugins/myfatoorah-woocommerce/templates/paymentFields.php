<input type="hidden" disabled data-mfVersion="<?php echo MYFATOORAH_WOO_PLUGIN_VERSION; ?>" />
<script>
    var hideMF = false;
    var removeDivider = false;
</script>

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
    if (
        !empty($this->gateways['cards']) && count($this->gateways['cards']) == 1
        && $this->gateways['cards'][0]->PaymentMethodCode == 'ap'
    ) {
        ?>
<script>
    var removeDivider = true;
</script>
<?php
    }

    if ($this->count > 1 || (!empty($this->gateways['form'])) && count($this->gateways['form']) >= 1) {
        $txtPayWith = __('Pay With', 'myfatoorah-woocommerce');
?>
<div class="mf-payment-methods-container" style='font-family:<?php echo $this->designFont; ?>; font-size: <?php echo $this->designFontSize?>px; color: <?php echo $this->designColor?>;'>
    <!--Start Card Section-->
    <?php if (empty($this->gateways['cards']) && empty($this->gateways['form']) && !empty($this->gateways['ap'])) { ?>
    <div id="mf-apple-button" style="height: 40px;"></div>
    <?php } if (!empty($this->gateways['cards'])) { ?>
    <div class="mf-grey-text" style='font-family:<?php echo $this->designFont; ?>; font-size: <?php echo $this->designFontSize+2?>px; '><?php echo __('How would you like to pay?', 'myfatoorah-woocommerce'); ?></div>
    <div id="mf-apple-button" style="height: 40px; padding-top: 12px;"></div>
    <div class="mf-divider card-divider" style='<?php echo $this->designFont?>;'>
        <span class="mf-divider-span" style="color: <?php echo $this->designColor?>;"><?php echo $txtPayWith; ?></span>
    </div>
    <input id="mf_gateway" name="mf_gateway" type="hidden" value="">

    <?php foreach ($this->gateways['cards'] as $mfCard) { ?>
    <?php
                $mfPaymentTitle = ($this->lang == 'ar') ? $mfCard->PaymentMethodAr : $mfCard->PaymentMethodEn;
                if ($mfCard->PaymentMethodCode == 'ap' && $this->appleRegistered) {
                    continue;
                }
    ?>
    <button class="mf-card-container mf_<?php echo $mfCard->PaymentMethodCode; ?>" style="width: unset;"
        mfCardId="<?php echo $mfCard->PaymentMethodId; ?>" title="<?php echo ($txtPayWith . ' ' . $mfPaymentTitle); ?>">
        <div class="mf-row-container">
            <img class="mf-payment-logo" src="<?php echo $mfCard->ImageUrl; ?>" alt="<?php echo $mfPaymentTitle; ?>">
            <span class="mf-payment-text mf-card-title" style='font-family:<?php echo $this->designFont; ?>; font-size: <?php echo $this->designFontSize?>px; color: <?php echo $this->themeColor;?>'><?php echo $mfPaymentTitle; ?></span>
        </div>
        <span class="mf-payment-text" style='text-align: end; font-family:<?php echo $this->designFont; ?>; font-size: <?php echo $this->designFontSize?>px; color: <?php echo $this->themeColor;?>'>
            <?php echo $mfCard->GatewayData['GatewayTotalAmount']; ?> <?php echo $mfCard->GatewayData['GatewayCurrency']; ?>
        </span>
    </button>
    <?php }
    ?>
    <script>
        jQuery(document).ready(function ($) {
            //card button clicked
            $("[mfCardId]").on('click', function (e) {
                e.preventDefault();
                $('#mf_gateway').val($(this).attr('mfCardId'));
                var fc = 'form.checkout';
                $(fc).submit();
            });
        });
    </script>
    <!--End Card Section-->
    <!--Start Form Section-->
    <?php
        }
        if (!empty($this->gateways['form'])) {
    ?>
    <?php if ($this->appleRegistered && empty($this->gateways['cards'])) { ?>
    <div class="mf-grey-text" style='font-family:<?php echo $this->designFont; ?>; font-size: <?php echo $this->designFontSize?>px; font-weight: 500'><?php echo __('How would you like to pay?', 'myfatoorah-woocommerce'); ?></div>
    <div id="mf-apple-button" style="height: 40px; padding-top: 12px;"></div>
    <?php } ?>
    <div class="mf-divider">
        <span class="mf-divider-span">
            <span class="or-divider">
                <?php
            if (count($this->gateways['cards']) > 0) {
                echo __('Or ', 'myfatoorah-woocommerce');
            }
                ?>
            </span>
            <?php
            echo __('Insert Card Details', 'myfatoorah-woocommerce');
            ?>
        </span>
    </div>
    <div id="mf-card-element" style="width:99%; max-width:800px; padding: 0rem 0.2rem"></div>
    <button class="mf-btn mf-pay-now-btn" type="button" style="background-color:<?php echo $this->themeColor;?>; 
                        border: none; border-radius: 8px;
                        padding: 7px 3px;">
        <span class="mf-pay-now-span"  style='font-size:<?php echo $this->designFontSize;?>px; font-family:<?php echo $this->designFont;?>;'>
            <?php echo __('Pay Now', 'myfatoorah-woocommerce'); ?>
        </span>
    </button>
    <input type="hidden" id="mfData" name="mfData" value="">

    <?php } ?>
    <!--End Form Section-->
</div>

<?php
    } else if ($this->count == 1 && !empty($this->gateways['all'][0]->PaymentMethodCode)) {
        if (!$this->appleRegistered) {
            $card = isset($this->gateways['all'][0]->PaymentMethodId) ? $this->gateways['all'][0]->PaymentMethodId : null;
?>
<input id="mf_gateway" name="mf_gateway" type="hidden" value="<?php echo $card; ?>">
<?php } else{ ?>
<div class="mf-payment-methods-container">
    <div id="mf-apple-button" style="height: 40px;"></div>
</div>
<?php
        }
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
        jQuery('#mf-apple-button').remove();
        if (typeof hideMF !== undefined && hideMF === true) {
            jQuery('.payment_method_myfatoorah_v2').hide();
        }
        if (typeof removeDivider !== undefined && removeDivider === true) {
            jQuery('.card-divider').remove();
            jQuery('.or-divider').remove();
        }
    }
    if(jQuery('.mf-payment-methods-container').children().length == 0){
        jQuery('.mf-payment-methods-container').remove();
    }
</script>