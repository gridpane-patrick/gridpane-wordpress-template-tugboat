<?php

//kindly refer to the https://karthikbhat.net/woocommerce-api-custom-endpoint/
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLibrary.php';

class MyfatoorahWoocommerceWebhook {
//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct() {
        add_action('woocommerce_api_myfatoorah_webhook', array($this, 'checkEventType'));
    }

//-----------------------------------------------------------------------------------------------------------------------------

    function checkEventType() {
        $v2Options = get_option('woocommerce_myfatoorah_v2_settings');
        $secretKey = empty($v2Options['webhookSecretKey']) ? die : $v2Options['webhookSecretKey'];

        $apache      = apache_request_headers();
        $headers     = array_change_key_case($apache);
        $mfSignature = empty($headers['myfatoorah-signature']) ? die : $headers['myfatoorah-signature'];

        $body    = file_get_contents("php://input");
        $webhook = json_decode($body, true);
        $eventType = (isset($webhook['EventType']) && $webhook['EventType'] == 1) ? $webhook['EventType'] : die;
        $data      = (empty($webhook['Data'])) ? die : $webhook['Data'];

        MyFatoorah::isSignatureValid($data, $secretKey, $mfSignature, $eventType) ? $this->{$webhook['Event']}($data) : die;
    }

//-----------------------------------------------------------------------------------------------------------------------------

    function TransactionsStatusChanged($data) {

        //to allow the callback code run 1st
        sleep(30);

        $orderId = $data['CustomerReference'];
        $order   = new WC_Order($orderId);

        $orderPaymentId = get_post_meta($orderId, 'PaymentId', true);

        if ($orderPaymentId == $data['PaymentId']) {
            die;
        }

        $paymentMethod = $order->get_payment_method();
        if ($paymentMethod != 'myfatoorah_v2' && $paymentMethod != 'myfatoorah_embedded') {
            die;
        }
        $calss   = 'WC_Gateway_' . ucfirst($paymentMethod);
        $gateway = new $calss;

        try {
            $gateway->checkStatus($data['InvoiceId'], 'InvoiceId', $order, ' - WebHook');
        } catch (Exception $ex) {
            MyFatoorah::$loggerObj = $gateway->pluginlog;
            MyFatoorah::log("Order #$orderId ----- WebHook TransactionsStatusChanged - Error: " . $ex->getMessage());
        }
        die;
    }

//-----------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param type $data
     * @param type $order
     * @param type $gateway
     * @return type
     */
    function RefundStatusChanged($data) {
        $orderList = wc_get_orders(array(
            'limit'      => -1, // Query all orders
            'meta_key'   => 'InvoiceId', // The postmeta key field
            'meta_value' => $data['InvoiceId'], // The comparison argument
        ));
        $order     = isset($orderList[0]) ? $orderList[0] : die;
        $orderId   = $order->get_id();
        $status    = $order->get_status();
        if ($status != 'processing' && $status != 'completed') {
            die;
        }
        $currencyRate = get_post_meta($orderId, 'RefundCurrencyRate', true);
        if (empty($currencyRate)) {
            die;
        }
        $refundAmount = get_post_meta($orderId, 'RefundAmount', true);
        var_dump($refundAmount);
        if (!is_numeric($refundAmount)) {
            die;
        }
        if ($data['RefundStatus'] == 'CANCELED') {
            $refundAmount -= $data['Amount'] * $currencyRate;
            update_post_meta($orderId, 'RefundAmount', $refundAmount);
            $order->add_order_note(__('MyFatoorah: <b>cancelled refund request</b> with the Refund Reference: ', 'myfatoorah-woocommerce') . $data['RefundReference']);
        } else if ($data['RefundStatus'] == 'REFUNDED' && $refundAmount == $order->get_total()) {
            $order->update_status('refunded', __('MyFatoorah: '));
            $order->add_order_note(__('MyFatoorah: refund <b>(Fully Refunded)</b> is accepted with the Refund Reference: ', 'myfatoorah-woocommerce') . $data['RefundReference']);
        } else {
            $default_args = array(
                'amount'   => $data['Amount'] * $currencyRate,
                'reason'   => null,
                'order_id' => $orderId
            );
            wc_create_refund($default_args);
            $order->add_order_note(__('MyFatoorah: refund <b>(Partial Refunded)</b> is accepted with the Refund Reference: ', 'myfatoorah-woocommerce') . $data['RefundReference']);
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------
}
