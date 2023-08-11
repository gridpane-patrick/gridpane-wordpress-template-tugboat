<?php

if (!defined('ABSPATH')) {
    exit;
}
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLoader.php';
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLibrary.php';

if (!class_exists('WC_Gateway_Myfatoorah')) {
    include_once('class-wc-gateway-myfatoorah.php');
}

/**
 * Myfatoorah_V2 Payment Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class       WC_Gateway_Myfatoorah_pg
 * @extends     WC_Payment_Gateway
 */
class WC_Gateway_Myfatoorah_v2 extends WC_Gateway_Myfatoorah {

    protected $code;
    protected $count           = 0;
    protected $gateways        = [];
    protected $appleRegistered = false;
    protected $totalAmount     = 0;
    protected $myfatoorah;
    public $session;

    /**
     * Constructor
     */
    public function __construct() {
        $this->code               = 'v2';
        $this->method_description = __("MyFatoorah Debit/Credit Card payment.", 'myfatoorah-woocommerce');
        $this->method_title       = __('MyFatoorah - Cards', 'myfatoorah-woocommerce');
        parent::__construct();

        $this->has_fields = true;
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

        $gateway = MyFatoorah::filterInputField('mf_gateway', 'POST') ?? 'myfatoorah';

        $configId          = MyFatoorah::filterInputField('mfData', 'POST');
        $myfatoorahPayment = new MyFatoorahPayment($this->myFatoorahConfig);
        $data              = $myfatoorahPayment->getInvoiceURL($curlData, $gateway, $orderId, $configId);
        update_post_meta($orderId, 'InvoiceId', $data['invoiceId']);

        return array(
            'result'   => 'success',
            'redirect' => $data['invoiceURL'],
        );
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    function payment_fields_v2() {
        if (!empty($this->mfError)) {
            include(MYFATOORAH_WOO_PLUGIN_PATH . 'templates/error.php');
            return;
        }

        if (isset($this->newDesign) && $this->newDesign == 'yes' && $this->listOptions === 'multigateways') {
            try {
                $userDefinedField  = ($this->saveCard == 'yes' && get_current_user_id()) ? 'CK-' . get_current_user_id() : '';
                $myfatoorahPayment = new MyFatoorahPayment($this->myFatoorahConfig);
                $this->session     = $myfatoorahPayment->getEmbeddedSession($userDefinedField);
                include_once(MYFATOORAH_WOO_PLUGIN_PATH . 'templates/form.php');
            } catch (Exception $exc) {
                $this->mfError = $exc->getMessage();
                include(MYFATOORAH_WOO_PLUGIN_PATH . 'templates/error.php');
                return;
            }
            include_once(MYFATOORAH_WOO_PLUGIN_PATH . 'templates/paymentFields.php');
        } else {
            include_once(MYFATOORAH_WOO_PLUGIN_PATH . 'templates/paymentFieldsV2.php');
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's title.
     *
     * @return string
     */
    public function get_title() {
        $this->setGateways();
        if ($this->listOptions === 'multigateways' && $this->count == 1) {
            return ($this->lang == 'ar') ? $this->gateways['all'][0]->PaymentMethodAr : $this->gateways['all'][0]->PaymentMethodEn;
        } else {
            return apply_filters('woocommerce_gateway_title', $this->title, $this->id);
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return the gateway's icon.
     *
     * @return string
     */
    public function get_icon() {


        if ($this->listOptions === 'multigateways' && $this->count == 1) {
            $icon = '<img src="' . $this->gateways['all'][0]->ImageUrl . '" alt="' . esc_attr($this->get_title()) . '" style="margin: 0px; width: 50px; height: 30px;"/>';
        } else {
            $icon = $this->icon ? '<img src="' . WC_HTTPS::force_https_url($this->icon) . '" alt="' . esc_attr($this->get_title()) . '" />' : '';
        }

        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    protected function setGateways() {
        if ($this->listOptions === 'myfatoorah' || count($this->gateways) != 0) {
            return;
        }

        if (!is_ajax() || !isset($_SERVER['HTTP_REFERER']) || stripos($_SERVER['HTTP_REFERER'], get_permalink(wc_get_page_id('checkout'))) === false) {
            return;
        }

        // option new design 
        try {
            if (isset($this->newDesign) && $this->newDesign == 'yes') {
                $totals                    = WC()->cart->get_totals();
                $this->appleRegistered     = ($this->registerApplePay == 'yes') ? true : false;
                $myfatoorahPaymentEmbedded = new MyFatoorahPaymentEmbedded($this->myFatoorahConfig);
                $this->gateways            = $myfatoorahPaymentEmbedded->getCheckoutGateways($totals['total'], get_woocommerce_currency(), $this->appleRegistered);
            } else {
                $myfatoorahPayment     = new MyFatoorahPayment($this->myFatoorahConfig);
                $gateways              = $myfatoorahPayment->getCachedCheckoutGateways();
                $embedOptions          = get_option('woocommerce_myfatoorah_embedded_settings');
                $this->gateways['all'] = (isset($embedOptions) && $embedOptions['enabled'] == 'yes') ? $gateways['cards'] : $gateways['all'];
            }
            $this->count = count($this->gateways['all']);
        } catch (Exception $ex) {
            $this->mfError = $ex->getMessage();
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Don't enable this payment, if there is no API key
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

        //don't enable if there is no API key
        $apiKey = $this->get_field_value('apiKey', $this->form_fields['apiKey']);
        if (!$apiKey) {
            WC_Admin_Settings::add_error(__('You should add the API key first, to enable this payment method', 'myfatoorah-woocommerce'));
            return 'no';
        }

        $countryMode = $this->get_field_value('countryMode', $this->form_fields['countryMode']);
        $testMode    = $this->get_field_value('testMode', $this->form_fields['testMode']);

        try {
            //This will be set only if the plugin is enabeled
            $this->newMfConfig = [
                'apiKey'      => $apiKey,
                'countryCode' => $countryMode,
                'isTest'      => ($testMode === 'yes'),
                'loggerObj'   => $this->pluginlog
            ];

            $mfPayObj = new MyFatoorahPayment($this->newMfConfig);
            $mfPayObj->initiatePayment();

            return 'yes';
        } catch (Exception $ex) {
            //Unset this due to the plugin is not enabeled
            $this->newMfConfig = null;
            WC_Admin_Settings::add_error(__($ex->getMessage(), 'myfatoorah-woocommerce'));
            return 'no';
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Don't disable this invoiceItem, if shipping method is enabled
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_invoiceItems_field($key, $value) {
        $active = is_null($value) ? 'no' : 'yes';

        $shippingOptions = get_option('woocommerce_myfatoorah_shipping_settings');
        if ($active == 'no' && isset($shippingOptions['enabled']) && $shippingOptions['enabled'] == 'yes') {
            WC_Admin_Settings::add_error(__('You can not disable invoice items option while MyFatoorah Shipping is enabled', 'myfatoorah-woocommerce'));
            $active = 'yes';
        }

        return $active;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Disable the embedded if the new design enabled
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_newDesign_field($key, $value) {
        $active = is_null($value) ? 'no' : 'yes';

        if ($active == 'yes') {
            $embedOptions = get_option('woocommerce_myfatoorah_embedded_settings');

            $enableFieldValue = MyFatoorah::filterInputField($this->get_field_key('enabled'), 'POST'); //don't use get_field_value to avoid duplicate validation and error message
            $apiKey           = $this->get_field_value('apiKey', $this->form_fields['apiKey']); //don't disable if there is no API key
            if ($apiKey && $enableFieldValue && isset($embedOptions['enabled']) && $embedOptions['enabled'] == 'yes') {
                $embedOptions['enabled'] = 'no';
                update_option('woocommerce_myfatoorah_embedded_settings', apply_filters('woocommerce_settings_api_sanitized_fields_' . 'myfatoorah_embedded', $embedOptions), 'yes');
            }
        }

        return $active;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Keep register Apple Pay value
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_registerApplePay_field($key, $value) {

        $active = is_null($value) ? 'no' : 'yes';
        //Also, check if plugin is enabled using newMfConfig, don't use get_field_value to avoid duplicate API request and validation errors
        if ($active == 'no' || !isset($this->newMfConfig)) {
            return $active;
        }

        $listOptions = $this->get_field_value('listOptions', $this->form_fields['listOptions']);
        $newDesign   = $this->get_field_value('newDesign', $this->form_fields['newDesign']);
        if ($listOptions == 'myfatoorah' || $newDesign == 'no') {
            WC_Admin_Settings::add_error(__('Please make sure to select New design and List all gateway option to enable Apple Pay Embedded.', 'myfatoorah-woocommerce'));
            return 'no';
        }

        try {
            $myfatoorahPayment = new MyFatoorahPayment($this->newMfConfig);

            $data = $myfatoorahPayment->registerApplePayDomain(get_site_url());
            if ($data->Message == 'OK') {
                return 'yes';
            }

            $error = $data->Message;
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        WC_Admin_Settings::add_error(__('Error: ', 'myfatoorah-woocommerce') . $key . ': ' . $error);
        return 'no';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Validate supplier code
     * 
     * @param type $key
     * @param type $value
     * 
     * @return string
     */
    public function validate_supplierCode_field($key, $value) {
        //Also, check if plugin is enabled using newMfConfig, don't use get_field_value to avoid duplicate API request and validation errors
        if (empty($value) || !isset($this->newMfConfig)) {
            return $value;
        }

        try {
            $myfatoorahSupplier = new MyFatoorahSupplier($this->newMfConfig);
            if ($myfatoorahSupplier->isSupplierApproved($value)) {
                return $value;
            }

            $error = __('Supplier code is not active in vendor account, please contact MyFatoorah team to activate it.', 'myfatoorah-woocommerce');
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        WC_Admin_Settings::add_error(__('Error: ', 'myfatoorah-woocommerce') . $key . ': ' . $error);
        return null;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Return whether or not this gateway still requires setup to function.
     *
     * When this gateway is toggled on via AJAX, if this returns true a
     * redirect will occur to the settings page instead.
     * @since 3.4.0
     *
     * @return bool
     */
    public function needs_setup() {

        if (empty($this->apiKey)) {
            return true;
        }

        $embedOptions = get_option('woocommerce_myfatoorah_embedded_settings');
        if (isset($this->newDesign) && $this->newDesign == 'yes' && isset($embedOptions['enabled']) && $embedOptions['enabled'] == 'yes') {
            return true;
        }
        return false;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
