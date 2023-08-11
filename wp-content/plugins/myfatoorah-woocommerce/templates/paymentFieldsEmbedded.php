<input type="hidden" disabled data-mfVersion="<?php echo MYFATOORAH_WOO_PLUGIN_VERSION; ?>"/>
<?php
$this->get_parent_payment_fields();

if (!empty($this->gateways['form'])) {
    ?>
    <div class="mf-embed-container">
        <div id="mf-card-element" style="margin-inline-start: 0.25rem; margin-top: 0.25rem;"></div>
    </div>
    <?php
} else {
    ?>
    <script>
        jQuery('.payment_method_myfatoorah_embedded').hide();
    </script>
    <?php
}