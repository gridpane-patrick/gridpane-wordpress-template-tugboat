<?php

if (!defined('ABSPATH')) {
    exit;
}
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLoader.php';
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLibrary.php';

if (!class_exists('WC_Gateway_Myfatoorah')) {
    include_once( 'class-wc-gateway-myfatoorah.php' );
}

/**
 * Myfatoorah_embedded Payment Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class       WC_Gateway_Myfatoorah_embedded
 * @extends     WC_Payment_Gateway
 */
class WC_Gateway_Myfatoorah_embedded extends WC_Gateway_Myfatoorah {

    protected $code;

    /**
     * Constructor
     */
    public function __construct() {
        $this->code = 'embedded';
        $this->method_description = __('MyFatoorah Embedded payment.', 'myfatoorah-woocommerce');
        $this->method_title = __('MyFatoorah - Embedded', 'myfatoorah-woocommerce');

        parent::__construct();

        $this->has_fields = true;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Initialize Gateway Settings Form Fields.
     */
    function init_form_fields() {
        $this->form_fields = include(MYFATOORAH_WOO_PLUGIN_PATH . 'includes/admin/payment.php' );

        //used in embedded and need to be read from v2
        unset($this->form_fields['apiKey']);
        unset($this->form_fields['countryMode']);
        unset($this->form_fields['testMode']);
        unset($this->form_fields['debug']);
        unset($this->form_fields['saveCard']);
        unset($this->form_fields['orderStatus']);
        unset($this->form_fields['success_url']);
        unset($this->form_fields['fail_url']);
        unset($this->form_fields['webhookSecretKey']);
        unset($this->form_fields['invoiceItems']);
        unset($this->form_fields['registerApplePay']);
        unset($this->form_fields['supplierCode']);
        unset($this->form_fields['listOptions']);
        unset($this->form_fields['newDesign']);
        unset($this->form_fields['designFont']);
        unset($this->form_fields['designFontSize']);
        unset($this->form_fields['designColor']);
        unset($this->form_fields['themeColor']);
        unset($this->form_fields['cardIcons']);
        unset($this->form_fields['theme']);
        unset($this->form_fields['frontend']);
        unset($this->form_fields['options']);
        unset($this->form_fields['configuration']);
        unset($this->form_fields['design']);
        unset($this->form_fields['resetTheme']);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Process the payment and return the result.
     * 
     * @param int $orderId
     * @return array
     */
    public function process_payment($orderId) {

        $curlData = $this->getPayLoadData($orderId);

        $sessionId = MyFatoorah::filterInputField('mfData', 'POST');

        $mfPayObj = new MyFatoorahPayment($this->myFatoorahConfig);
        $data = $mfPayObj->getInvoiceURL($curlData, null, $orderId, $sessionId);

        update_post_meta($orderId, 'InvoiceId', $data['invoiceId']);

        return array(
            'result' => 'success',
            'redirect' => $data['invoiceURL'],
        );
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    function payment_fields_embedded() {
        try {
            $userDefinedField = ($this->saveCard == 'yes' && get_current_user_id()) ? 'CK-' . get_current_user_id() : '';
            $myfatoorahPayment = new MyFatoorahPayment($this->myFatoorahConfig);
            $this->session = $myfatoorahPayment->getEmbeddedSession($userDefinedField);
            $this->gateways = $myfatoorahPayment->getCachedCheckoutGateways();

            include_once(MYFATOORAH_WOO_PLUGIN_PATH . 'templates/form.php');
            include_once(MYFATOORAH_WOO_PLUGIN_PATH . 'templates/paymentFieldsEmbedded.php');
        } catch (Exception $ex) {
            $this->mfError = $ex->getMessage();
            include(MYFATOORAH_WOO_PLUGIN_PATH . 'templates/error.php');
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's title.
     *
     * @return string
     */
    public function get_title() {

        return apply_filters('woocommerce_gateway_title', $this->title, $this->id);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's icon.
     *
     * @return string
     */
    public function get_icon() {

        $icon = $this->icon ? '<img src="' . WC_HTTPS::force_https_url($this->icon) . '" alt="' . esc_attr($this->get_title()) . '" />' : '';
        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Don't enable this payment, if there is no API key in "MyFatoorah - Cards" payment settings or newDesign is enabled
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_enabled_field($key, $value) {
        if (is_null($value)) {
            return 'no';
        }

        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');

        //don't enable if newDesign is enabled 
        if (isset($v2Options['enabled']) && $v2Options['enabled'] == 'yes' && isset($v2Options['newDesign']) && $v2Options['newDesign'] == 'yes') {
            WC_Admin_Settings::add_error(__('You should disable the new design option in the "MyFatoorah - Cards" payment Settings first, to enable this payment method', 'myfatoorah-woocommerce'));
            return 'no';
        }

        //don't enable if there is no API key
        if (empty($v2Options['apiKey'])) {
            WC_Admin_Settings::add_error(__('You should add the API key in the "MyFatoorah - Cards" payment Settings first, to enable this payment method', 'myfatoorah-woocommerce'));
            return 'no';
        }

        try {
            //This will be set only if the plugin is enabeled
            $this->newMfConfig = [
                'apiKey' => $v2Options['apiKey'],
                'countryCode' => $v2Options['countryMode'],
                'isTest' => ($v2Options['testMode'] === 'yes'),
                'loggerObj' => $this->pluginlog
            ];

            $mfPayObj = new MyFatoorahPayment($this->newMfConfig);
            $mfPayObj->initiatePayment();

            return 'yes';
        } catch (Exception $ex) {
            WC_Admin_Settings::add_error(__($ex->getMessage(), 'myfatoorah-woocommerce'));
            return 'no';
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return whether or not this gateway still requires setup to function.
     *
     * When this gateway is toggled on via AJAX, if this returns true a
     * redirect will occur to the settings page instead.
     *
     * @since 3.4.0
     * @return bool
     */
    public function needs_setup() {

        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');
        if (empty($v2Options['apiKey'])) {
            return true;
        }

        if (isset($v2Options['enabled']) && $v2Options['enabled'] == 'yes' && isset($v2Options['newDesign']) && $v2Options['newDesign'] == 'yes') {
            return true;
        }

        return false;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------    
}
