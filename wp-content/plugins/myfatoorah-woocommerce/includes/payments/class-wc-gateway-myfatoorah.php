<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLoader.php';
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLibrary.php';

/**
 * WC_Gateway_Myfatoorah class.
 *
 * handle payments.
 *
 * @extends     WC_Payment_Gateway
 */
class WC_Gateway_Myfatoorah extends WC_Payment_Gateway {

//-----------------------------------------------------------------------------------------------------------------------------

    public $id;
    public $lang;
    public $pluginlog;
    public $enabled, $description, $testMode, $apiKey, $listOptions, $registerApplePay, $orderStatus, $success_url, $fail_url, $debug, $saveCard, $icon, $webhookSecretKey, $supplierCode, $designFont, $designFontSize, $designColor, $themeColor, $cardIcons;
    public $mfCountries      = [];
    public $myFatoorahConfig = [];
    public $title            = '';
    public $countryMode      = 'KWT';

    /**
     * Constructor for your payment class
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->id   = 'myfatoorah_' . $this->code;
        $this->lang = substr(determine_locale(), 0, 2);

        $this->pluginlog = WC_LOG_DIR . $this->id . '.log';

        //this will appeare in the setting details page. For more customize page you override function admin_options()
        $this->supports = array(
            'products',
//            'refunds',
        );

        //Get setting values
        $this->init_settings();

        //enabled, title, description, countryMode, testMode, apiKey, listOptions, orderStatus, success_url, fail_url, debug, icon, 
        foreach ($this->settings as $key => $val) {
            $this->$key = $val;
        }

        $this->init_myfatoorah_options();

        //lookup
        $countries = MyFatoorah::getMFCountries();
        if (is_array($countries)) {
            $nameIndex = 'countryName' . ucfirst($this->lang);
            foreach ($countries as $key => $obj) {
                $this->mfCountries[$key] = $obj[$nameIndex];
            }
        } else {
            $countries = [];
        }

        $this->myFatoorahConfig = [
            'apiKey'      => $this->apiKey,
            'countryCode' => $this->countryMode,
            'isTest'      => ($this->testMode === 'yes') ? true : false,
            'loggerObj'   => ($this->debug === 'yes') ? $this->pluginlog : false
        ];

        //Create plugin admin fields
        $this->init_form_fields();

        //save admin setting action
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * initiate MyFatoorah from V2 Options
     * @return void 
     */
    function init_myfatoorah_options() {
        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');
        if ($v2Options) {
            $this->apiKey           = isset($v2Options['apiKey']) ? $v2Options['apiKey'] : '';
            $this->countryMode      = isset($v2Options['countryMode']) ? $v2Options['countryMode'] : 'KWT';
            $this->testMode         = isset($v2Options['testMode']) ? $v2Options['testMode'] : 'no';
            $this->debug            = isset($v2Options['debug']) ? $v2Options['debug'] : 'yes';
            $this->saveCard         = isset($v2Options['saveCard']) ? $v2Options['saveCard'] : 'no';
            $this->orderStatus      = isset($v2Options['orderStatus']) ? $v2Options['orderStatus'] : 'processing';
            $this->success_url      = isset($v2Options['success_url']) ? $v2Options['success_url'] : '';
            $this->fail_url         = isset($v2Options['fail_url']) ? $v2Options['fail_url'] : '';
            $this->webhookSecretKey = isset($v2Options['webhookSecretKey']) ? $v2Options['webhookSecretKey'] : '';
            $this->invoiceItems     = isset($v2Options['invoiceItems']) ? $v2Options['invoiceItems'] : 'yes';
            $this->registerApplePay = isset($v2Options['registerApplePay']) ? $v2Options['registerApplePay'] : 'no';
            $this->supplierCode     = isset($v2Options['supplierCode']) ? $v2Options['supplierCode'] : 0;
            $this->designColor      = isset($v2Options['designColor']) ? $v2Options['designColor'] : '#888484';
            $this->themeColor       = isset($v2Options['themeColor']) ? $v2Options['themeColor'] : '#0293cc';
            $this->designFont       = isset($v2Options['designFont']) ? $v2Options['designFont'] : "sans-serif";
            $this->designFontSize   = isset($v2Options['designFontSize']) ? $v2Options['designFontSize'] : '12';
            $this->cardIcons        = isset($v2Options['cardIcons']) ? ($v2Options['cardIcons'] == 'yes' ? 'true' : 'false') : 'false';
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Define Settings Form Fields
     * @return void 
     */
    function init_form_fields() {
        $this->form_fields = include(MYFATOORAH_WOO_PLUGIN_PATH . 'includes/admin/payment.php' );
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Process a refund if supported
     *
     * @param  int $orderId
     * @param  float $amount
     * @param  string $reason
     * @return  bool|wp_error True or false based on success, or a WP_Error object
     */
    public function process_refund($orderId, $amount = null, $reason = '') {

        if (!$paymentId = get_post_meta($orderId, 'PaymentId', true)) {
            return new WP_Error('mfMakeRefund', __('Please, refund manually for this order', 'myfatoorah-woocommerce'));
        }

        $order  = new WC_Order($orderId);
        $status = $order->get_status();

        if ($status != 'processing' && $status != 'completed') {
            return new WP_Error('mfMakeRefund', __("Can't refund order with status ", 'myfatoorah-woocommerce') . $status);
        }

        $currencyCode = $order->get_currency();
        $msg          = null;

        try {
            $myFatoorahRefund = new MyFatoorahRefund($this->myFatoorahConfig);
            $json             = $myFatoorahRefund->refund($paymentId, $amount, $currencyCode, $reason, $orderId);
            update_post_meta($orderId, 'RefundReference', $json->Data->RefundReference);
            $myfatoorahList   = new MyFatoorahList($this->myFatoorahConfig);
            $rate             = $myfatoorahList->getCurrencyRate($currencyCode);
            update_post_meta($orderId, 'RefundCurrencyRate', $rate);
            $refundAmountArr  = empty(get_post_meta($orderId, 'RefundAmount')) ? array(0 => 0) : get_post_meta($orderId, 'RefundAmount');

            $refundAmount = isset($refundAmountArr[0]) ? ($refundAmountArr[0] + $json->Data->Amount) : $json->Data->Amount;
            update_post_meta($orderId, 'RefundAmount', $refundAmount);
            if ($refundAmount == $order->get_total()) {
                $order->add_order_note(__('MyFatoorah <b>refund is initiated</b> with refund Reference ID: ', 'myfatoorah-woocommerce') . $json->Data->RefundReference);
                $order->save();
                $msg = 'Please wait until MyFatoorah refund request is confirmed.';
            } else {
                $order->add_order_note(__('MyFatoorah <b>partial refund is initiated</b> with refund Reference ID: ', 'myfatoorah-woocommerce') . $json->Data->RefundReference);
                $order->save();
                $msg = 'Please wait until MyFatoorah Partial refund request is confirmed.';
            }
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
        }
        return new WP_Error('error', __($msg, 'myfatoorah-woocommerce'));
    }

//-----------------------------------------------------------------------------------------------------------------------------
    function getPayLoadData($orderId) {
        $order = new WC_Order($orderId);

        $fName = $order->get_billing_first_name();
        if (!$fName) {
            $fName = $order->get_shipping_first_name();
        }

        $lname = $order->get_billing_last_name();
        if (!$lname) {
            $lname = $order->get_shipping_last_name();
        }

        //phone & email are not exist in shipping address!!
        $email1 = $order->get_billing_email();
        $email  = empty($email1) ? null : $email1;

        $phone    = $order->get_billing_phone();
        $phoneArr = MyFatoorah::getPhone($phone);

        $civilId = get_post_meta($order->get_id(), 'billing_cid', true);

        $userDefinedField = ($this->saveCard == 'yes' && get_current_user_id()) ? 'CK-' . get_current_user_id() : '';

        //get $expiryDate
        $expiryDate = '';
        if (class_exists('WC_Admin_Settings')) {

            $date        = new DateTime('now', new DateTimeZone('Asia/Kuwait'));
            $currentDate = $date->format('Y-m-d\TH:i:s');

            $woocommerce_hold_stock_minutes = get_option('woocommerce_hold_stock_minutes') ?: 60;

            $expires    = strtotime("$currentDate + $woocommerce_hold_stock_minutes minutes");
            $expiryDate = date('Y-m-d\TH:i:s', $expires);
        }

        //set multiused vars
//        $sucess_url = $order->get_checkout_order_received_url();
        $sucess_url = add_query_arg(array('wc-api' => 'myfatoorah_process', 'oid' => base64_encode($orderId)), home_url());
        ////$sucess_url = str_replace('https:', 'http:', add_query_arg('api', 'wc_gateway_myfatoorah_' . $this->code, home_url('?oid=' . base64_encode($orderId))));
        //$err_url = $order->get_cancel_order_url_raw();
        //$err_url = wc_get_checkout_url();


        $currencyIso = $order->get_currency();
        //if the WPML is accivate (need better sol????????)
//        if ($currencyIso = 'CLOUDWAYS') {
//            $currencyIso = get_woocommerce_currency_symbol($currencyIso);
//        }

        $shipingMethod = $this->getShippingMethod();
//        $amount       = $order->get_total();
//        $invoiceItems = [['ItemName' => 'Total amount', 'Quantity' => 1, 'UnitPrice' => "$amount"]];

        $amount = 0;
        if ($this->invoiceItems == 'yes') {
            $invoiceItems = $this->getInvoiceItems($order, $amount, $shipingMethod);
        } else {
            $amount         = $order->get_total();
            $invoiceItems[] = [
                'ItemName'  => __('Total amount for order #', 'myfatoorah-woocommerce') . $orderId,
                'Quantity'  => 1,
                'UnitPrice' => "$amount"];
        }

        //$address = $order->get_shipping_address_1();
        //$city = $order->get_shipping_city();
        //$country = $order->get_shipping_country();

        $address = WC()->customer->get_shipping_address_1() . ' ' . WC()->customer->get_shipping_address_2();

        // custom fields
        /* if(empty($address)){
          $block = get_post_meta( $order->get_id(), 'billing_block', true );
          $street = get_post_meta( $order->get_id(), 'billing_street', true );
          $gada = get_post_meta( $order->get_id(), 'billing_gada', true );
          $house = get_post_meta( $order->get_id(), 'billing_house', true );
          $address =$block. ' , ' .$street . ' , '. $house. ' , '. $gada ;
          } */

        $customerAddress = array(
            'Block'               => 'string',
            'Street'              => 'string',
            'HouseBuildingNo'     => 'string',
            'Address'             => $address,
            'AddressInstructions' => 'string'
        );

        $shippingConsignee = array(
            'PersonName'   => "$fName $lname",
            'Mobile'       => $phoneArr[1],
            'EmailAddress' => $email,
            'LineAddress'  => $address,
            'CityName'     => WC()->customer->get_shipping_city(),
            'PostalCode'   => WC()->customer->get_shipping_postcode(),
            'CountryCode'  => WC()->customer->get_shipping_country()
        );

        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');

        $design = (isset($v2Options['enabled']) && $v2Options['enabled'] == 'yes' && isset($v2Options['newDesign']) && $v2Options['newDesign'] == 'yes' && isset($v2Options['listOptions']) && $v2Options['listOptions'] == 'multigateways') ? ' - New Design' : null;

        return [
            'CustomerName'       => "$fName $lname",
            'InvoiceValue'       => "$amount",
            'DisplayCurrencyIso' => $currencyIso,
            'CustomerEmail'      => $email,
            'CallBackUrl'        => $sucess_url,
            'ErrorUrl'           => $sucess_url,
            'MobileCountryCode'  => $phoneArr[0],
            'CustomerMobile'     => $phoneArr[1],
            'Language'           => ($this->lang == 'ar') ? 'ar' : 'en',
            'CustomerReference'  => $orderId,
            'CustomerCivilId'    => $civilId,
            'UserDefinedField'   => $userDefinedField,
            'ExpiryDate'         => $expiryDate,
            'SourceInfo'         => 'WooCommerce ' . WC_VERSION . ' - ' . $this->id . ' ' . MYFATOORAH_WOO_PLUGIN_VERSION . $design,
            'CustomerAddress'    => $customerAddress,
            'ShippingConsignee'  => ($shipingMethod) ? $shippingConsignee : null,
            'ShippingMethod'     => $shipingMethod,
            'InvoiceItems'       => $invoiceItems,
            'Suppliers'          => $this->getSupplierInfo($amount),
        ];
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param WC_Order $order
     * @param type $amount
     * @param type $shipingMethod
     * @return string
     */
    function getInvoiceItems($order, &$amount, $shipingMethod) {
        $weightRate    = MyFatoorah::getWeightRate(get_option('woocommerce_weight_unit'));
        $dimensionRate = MyFatoorah::getDimensionRate(get_option('woocommerce_dimension_unit'));

        $forceEnglishItemName = ($this->lang == 'ar' && $shipingMethod);

        $invoiceItemsArr = [];
        //Product items
        /** @var WC_Order_Item[] $items */
        $items           = $order->get_items();
        foreach ($items as $item) {
            $product = wc_get_product($item->get_product_id());

            $itemName = $item->get_name();
            if ($shipingMethod && $product->get_attribute('mf_shipping_english_name')) {
                $itemName = $product->get_attribute('mf_shipping_english_name');
            }
            $productFromItem = $item->get_product();

            //check if this is a variation using is_type
            if ($productFromItem->is_type('variation')) {
                $variation_id = $item->get_variation_id();
                $product      = wc_get_product_object('variation', $variation_id);
            }

            $itemSubtotalPrice = $order->get_line_subtotal($item, false);
            $itemPrice         = $itemSubtotalPrice / $item->get_quantity();
            $amount            += $itemSubtotalPrice;
            $invoiceItemsArr[] = [
                'ItemName'  => $itemName,
                'Quantity'  => $item->get_quantity(),
                'UnitPrice' => "$itemPrice",
                'weight'    => ($shipingMethod) ? (float) ($product->get_weight()) * $weightRate : null,
                'Width'     => ($shipingMethod) ? (float) ($product->get_width()) * $dimensionRate : null,
                'Height'    => ($shipingMethod) ? (float) ($product->get_height()) * $dimensionRate : null,
                'Depth'     => ($shipingMethod) ? (float) ($product->get_length()) * $dimensionRate : null,
            ];
        }


        //------------------------------
        //Shippings
        $shipping = round($order->get_shipping_total(), wc_get_price_decimals());
        if ($shipping && $shipingMethod === null) {

            $rateLabel = $order->get_shipping_method();
            foreach (WC()->session->get('shipping_for_package_0')['rates'] as $method_id => $rate) {
                if (WC()->session->get('chosen_shipping_methods')[0] == $method_id) {
                    $rateLabel = $rate->label; // The shipping method label name
                    break;
                }
            }

            $itemName = $forceEnglishItemName ? $rateLabel : __($rateLabel, 'woocommerce');

            $amount            += $shipping;
            $invoiceItemsArr[] = ['ItemName' => $itemName, 'Quantity' => '1', 'UnitPrice' => "$shipping", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];
        }


        //------------------------------
        //Discounds and Coupon
        $discount = round($order->get_discount_total(), wc_get_price_decimals());
        if ($discount) {
            $itemName = $forceEnglishItemName ? 'Discount' : __('Discount', 'woocommerce');

            $amount            -= $discount;
            $invoiceItemsArr[] = ['ItemName' => $itemName, 'Quantity' => '1', 'UnitPrice' => "-$discount", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];
        }


        //------------------------------
        //Other fees
        foreach ($order->get_items('fee') as $item => $item_fee) {
            $total_fees = $item_fee->get_total();
            $itemName   = $forceEnglishItemName ? $item_fee->get_name() : __($item_fee->get_name(), 'woocommerce');

            $amount            += $total_fees;
            $invoiceItemsArr[] = ['ItemName' => $itemName, 'Quantity' => '1', 'UnitPrice' => "$total_fees", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];
        }


        //------------------------------
        //for pw-woocommerce-gift-cards plugin
        foreach ($order->get_items('pw_gift_card') as $line) {
            $gifPrice = $line->get_amount();
            $itemName = $forceEnglishItemName ? 'Gift Card' : __('Gift Card', 'woocommerce');

            $amount            -= $gifPrice;
            $invoiceItemsArr[] = ['ItemName' => $itemName, 'Quantity' => '1', 'UnitPrice' => "-$gifPrice", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];
        }


        //------------------------------
        //Tax
        $MFShipping = 0;
        if ($shipingMethod) {
            $cartTotals = WC()->cart->get_totals();
            $MFShipping = $cartTotals['shipping_total'];
        }

//        $tax = $order->get_total_tax();
        $tax = round($order->get_total() - $amount - $MFShipping, wc_get_price_decimals()); // IMP MF Shipping 
        if ($tax) {
            $itemName = $forceEnglishItemName ? 'Taxes' : __('Taxes', 'woocommerce');

            $amount            += $tax;
            $invoiceItemsArr[] = ['ItemName' => $itemName, 'Quantity' => '1', 'UnitPrice' => "$tax", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];
        }


        //------------------------------
        //total
        $amount = round($amount, 3);
        return $invoiceItemsArr;
    }

//-----------------------------------------------------------------------------------------------------------------------------
    private function getShippingMethod() {

        $chosen_methods = WC()->session->get('chosen_shipping_methods');

        if (isset($chosen_methods[0])) {
            if ($chosen_methods[0] == 'myfatoorah_shipping:1') {
                return 1;
            } else if ($chosen_methods[0] == 'myfatoorah_shipping:2') {
                return 2;
            }
        }
        return null;
    }

    private function getSupplierInfo($amount) {
        if (empty($this->supplierCode)) {
            return null;
        }

        return [[
        'SupplierCode'  => $this->supplierCode,
        'ProposedShare' => null,
        'InvoiceShare'  => $amount
        ]];
    }

//-----------------------------------------------------------------------------------------------------------------------------

    public function updatePostMeta($orderId, $data) {

        update_post_meta($orderId, 'InvoiceId', $data->InvoiceId);
        update_post_meta($orderId, 'InvoiceReference', $data->InvoiceReference);
        update_post_meta($orderId, 'InvoiceDisplayValue', $data->InvoiceDisplayValue);

        //focusTransaction
        update_post_meta($orderId, 'PaymentGateway', $data->focusTransaction->PaymentGateway);
        update_post_meta($orderId, 'PaymentId', $data->focusTransaction->PaymentId);
        update_post_meta($orderId, 'ReferenceId', $data->focusTransaction->ReferenceId);
        update_post_meta($orderId, 'TransactionId', $data->focusTransaction->TransactionId);
    }

//-----------------------------------------------------------------------------------------------------------------------------

    public function addOrderNote(&$order, $data, $source) {
        $note = "MyFatoorah$source Payment Details:<br>";

        $note .= 'InvoiceStatus: ' . $data->InvoiceStatus . '<br>';
        if ($data->InvoiceStatus == 'Failed') {
            $note .= 'InvoiceError: ' . $data->InvoiceError . '<br>';
        }

        $note .= 'InvoiceId: ' . $data->InvoiceId . '<br>';
        $note .= 'InvoiceReference: ' . $data->InvoiceReference . '<br>';
        $note .= 'InvoiceDisplayValue: ' . $data->InvoiceDisplayValue . '<br>';

        //focusTransaction
        $note .= 'PaymentGateway: ' . $data->focusTransaction->PaymentGateway . '<br>';
        $note .= 'PaymentId: ' . $data->focusTransaction->PaymentId . '<br>';
        $note .= 'ReferenceId: ' . $data->focusTransaction->ReferenceId . '<br>';
        $note .= 'TransactionId: ' . $data->focusTransaction->TransactionId . '<br>';

        $order->add_order_note($note);
    }

//-----------------------------------------------------------------------------------------------------------------------------
    public function updateOrderData($orderId, &$order, $status, $data, $source) {

        //update meta data
        $this->updatePostMeta($orderId, $data);

        //update status
        $order->update_status($status, "MyFatoorah$source:<br/>", true);

        //add notes
        $this->addOrderNote($order, $data, $source);
    }

//-----------------------------------------------------------------------------------------------------------------------------
    public function checkStatus($keyId, $KeyType, $order, $source = '') {

        $orderId                 = $order->get_id();
        $myfatoorahPaymentStatus = new MyFatoorahPaymentStatus($this->myFatoorahConfig);
        $data                    = $myfatoorahPaymentStatus->getPaymentStatus($keyId, $KeyType, $orderId);

        if ($data->InvoiceStatus == 'Paid') {
            //pending, processing, on-hold, completed, cancelled, refunded, failed, or customed
            $status = $order->get_status();
            //go back if NOT pending, failed, on-hold
            if ($status != 'pending' && $status != 'failed' && $status != 'on-hold') {
                return '';
            }
            $this->updateOrderData($orderId, $order, $this->orderStatus, $data, $source);
        } else if ($data->InvoiceStatus == 'Failed') {

            $this->updateOrderData($orderId, $order, 'failed', $data, $source);
        } else if ($data->InvoiceStatus == 'Expired') {

            $order->update_status('cancelled', "MyFatoorah$source: <br/>");
            $order->add_order_note("MyFatoorah$source: $data->InvoiceError<br/>");
        }
        $order->save();

        return $data->InvoiceError;
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Processes and saves options.
     * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
     *
     * @return bool was anything saved?
     */
    public function process_admin_options() {
        if (file_exists(MyFatoorahPayment::$pmCachedFile)) {
            unlink(MyFatoorahPayment::$pmCachedFile);
        }
        parent::process_admin_options();
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    function payment_fields() {

        echo '<!-- MyFatoorah version ' . MYFATOORAH_WOO_PLUGIN_VERSION . ' -->';

        try {
            if (!wc_checkout_is_https()) {
                throw new Exception(__('MyFatoorah forces SSL checkout Payment. Your checkout is not secure! Please, contact the site admin to enable SSL and ensure that the server has a valid SSL certificate.', 'myfatoorah-woocommerce'));
            }

            $this->{'payment_fields_' . $this->code}();
        } catch (Exception $ex) {
            $this->mfError = $ex->getMessage();
            include(MYFATOORAH_WOO_PLUGIN_PATH . 'templates/error.php');
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function get_parent_payment_fields() {
        parent::payment_fields();
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
