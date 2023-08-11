<?php

/**
 * MyFatoorah WooCommerce Class
 */
class MyfatoorahWoocommercePayment {

//-----------------------------------------------------------------------------------------------------------------------------

    protected $id      = 'myfatoorah_v2';
    protected $code    = 'v2';
    protected $gateway = 'WC_Gateway_Myfatoorah_v2';

    /**
     * Constructor
     */
    public function __construct($code) {

        $this->code    = $code;
        $this->id      = 'myfatoorah_' . $code;
        $this->gateway = 'WC_Gateway_Myfatoorah_' . $code;

        //filters
        add_filter('woocommerce_payment_gateways', array($this, 'register'), 0);
        add_filter('plugin_action_links_' . MYFATOORAH_WOO_PLUGIN, array($this, 'plugin_action_links'));
        add_filter('wc_get_price_decimals', array($this, 'wc_get_price_decimals'), 99);
        add_action('woocommerce_api_myfatoorah_process', array($this, 'initLoader'));
        add_action('woocommerce_api_myfatoorah_complete', array($this, 'getPaymentStatus'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_css_js'));

        $paymentOptions = get_option('woocommerce_myfatoorah_' . $this->code . '_settings');
        if ((isset($paymentOptions['enabled']) && $paymentOptions['enabled'] == 'yes') && (($this->code == 'v2' && (isset($paymentOptions['newDesign']) && $paymentOptions['newDesign'] == 'yes') && (isset($paymentOptions['listOptions']) && $paymentOptions['listOptions'] == 'multigateways')) || ($this->code == 'embedded' ))) {
            add_action('wp_enqueue_scripts', array($this, 'load_css_js'));
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------
    public function wc_get_price_decimals($decimals) {
        $shippingOptions = get_option('woocommerce_myfatoorah_shipping_settings');
        if (!isset($shippingOptions['enabled']) || $shippingOptions['enabled'] == 'no' || wc_get_page_id('checkout') <= 0) {
            return $decimals;
        }

        $chosen_methods = filter_input(INPUT_POST, 'shipping_method', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (isset($chosen_methods[0])) {
            if ($chosen_methods[0] == 'myfatoorah_shipping:1' || $chosen_methods[0] == 'myfatoorah_shipping:2') {
                return 3;
            }
        } else {
            //select diff country with no other shipping methods
            $payment_method = MyFatoorah::filterInputField('payment_method', 'POST');
            if ($payment_method == 'myfatoorah_v2' || $payment_method == 'myfatoorah_embedded') {
                return 3;
            }
        }
        return $decimals;
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Register the gateway to WooCommerce
     */
    public function register($gateways) {
        include_once("includes/payments/class-wc-gateway-myfatoorah-$this->code.php");
        $gateways[] = $this->gateway;
        return $gateways;
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Show action links on the plugin screen.
     *
     * @param mixed $links Plugin Action links.
     *
     * @return array
     */
    public function plugin_action_links($links) {
        //http://wordpress-5.4.2.com/wp-admin/admin.php?page=wc-settings&tab=checkout&section=$this->id
        $name = [
            'v2'       => __('Cards', 'myfatoorah-woocommerce'),
            'embedded' => __('Embedded', 'myfatoorah-woocommerce'),
        ];

        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_gateway_' . $this->id) . '">' . $name[$this->code] . '</a>',
        );
        return array_merge($links, $plugin_links);
    }

//-----------------------------------------------------------------------------------------------------------------------------    
    public function getPaymentStatus() {

        $orderId       = base64_decode(MyFatoorah::filterInputField('oid'));
        $order         = new WC_Order($orderId);
        $paymentMethod = $order->get_payment_method();

        //get Payment Id
        $KeyType = 'PaymentId';
        $key     = MyFatoorah::filterInputField('paymentId');

        $this->validateCallback($orderId, $key, $paymentMethod);

        //get MyFatoorah object
        $calss   = 'WC_Gateway_' . ucfirst($paymentMethod);
        $gateway = new $calss;

        try {
            $error = $gateway->checkStatus($key, $KeyType, $order);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }
        if ($error) {
            $this->redirectToFailURL($gateway, $error);
            exit();
        }

        $this->redirectToSuccessURL($gateway, $orderId, $order);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function validateCallback($orderId, $key, $paymentMethod) {
        if (!$orderId) {
            wp_die(__('The Order is not found. Please, contact the store admin.', 'myfatoorah-woocommerce'));
        }

        if (!$key) {
            wp_die(__('The Order is not found. Please, contact the store admin.', 'myfatoorah-woocommerce'));
        }

        if ($paymentMethod != 'myfatoorah_v2' && $paymentMethod != 'myfatoorah_embedded') {
            wp_die(__('The Order is not found. Please, contact the store admin.', 'myfatoorah-woocommerce'));
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function redirectToFailURL($gateway, $error) {
        if ($gateway->fail_url) {
            wp_redirect($gateway->fail_url . '?error=' . urlencode($error));
        } else {
            wc_add_notice($error, 'error');
            wp_redirect(wc_get_checkout_url());
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function redirectToSuccessURL($gateway, $orderId, $order) {
        if ($gateway->success_url) {
            wp_redirect($gateway->success_url . '/' . $orderId . '/?key=' . $order->get_order_key());
        } else {
            //When "thankyou" order-received page is reached …
            wp_redirect($order->get_checkout_order_received_url());
        }
        exit();
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    public function initLoader() {
        $orderId = MyFatoorah::filterInputField('oid');

        if (!$orderId) {
            wp_die(__('The Order is not found. Please, contact the store admin.', 'myfatoorah-woocommerce'));
        }
        $paymentId = MyFatoorah::filterInputField('paymentId');

        if (!$paymentId) {
            wp_die(__('The Order is not found. Please, contact the store admin.', 'myfatoorah-woocommerce'));
        }

        include_once(MYFATOORAH_WOO_PLUGIN_PATH . 'templates/loader.php');
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function load_css_js() {
        $mdOptions = get_option('woocommerce_myfatoorah_embedded_settings');
        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');

        $isMdEnabled = (isset($mdOptions['enabled']) && $mdOptions['enabled'] == 'yes' );
        $isV2Enabled = (isset($v2Options['enabled']) && $v2Options['enabled'] == 'yes' );

        $isNewDesign = ($isV2Enabled && isset($v2Options['newDesign']) && $v2Options['newDesign'] == 'yes' );

        if (!$isMdEnabled && !$isNewDesign) {
            return;
        }

        $istest      = (isset($v2Options['testMode']) && $v2Options['testMode'] == 'yes' );
        $isSAU       = (isset($v2Options['countryMode']) && $v2Options['countryMode'] == 'SAU' );
        $sessionPath = (($istest) ? 'demo' : ($isSAU ? 'sa' : 'portal'));

        wp_enqueue_script('mfSession', "https://$sessionPath.myfatoorah.com/cardview/v2/session.js", [], MYFATOORAH_WOO_PLUGIN_VERSION, true);

        $isApRegisterd = (isset($v2Options['registerApplePay']) && $v2Options['registerApplePay'] == 'yes' );
        if ($isApRegisterd) {
            wp_enqueue_script('mfApplePay', "https://$sessionPath.myfatoorah.com/applepay/v2/applepay.js", [], MYFATOORAH_WOO_PLUGIN_VERSION, true);
        }
        wp_enqueue_style('myfatoorah-style', plugins_url('assets/css/myfatoorah.css', __FILE__), [], MYFATOORAH_WOO_PLUGIN_VERSION);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function load_admin_css_js() {
        wp_enqueue_style('myfatoorah-admin-style', plugins_url('assets/css/myfatoorah-admin.css', __FILE__), [], MYFATOORAH_WOO_PLUGIN_VERSION);
        wp_enqueue_script('wp-color-picker', "/wp-admin/js/color-picker.min.js", [], false, 1);
        wp_enqueue_script('myfatoorah-admin-js', plugins_url('/assets/js/admin.js', __FILE__), [], MYFATOORAH_WOO_PLUGIN_VERSION, true);
    }

//-----------------------------------------------------------------------------------------------------------------------------
}
