<?php

class WC_Controller_Moyasar_Return
{
    public static $instance;

    protected $gateway;
    protected $logger;

    public static function init()
    {
        $controller = new static();

        add_action('wp', array($controller, 'handle_user_return'));

        return static::$instance = $controller;
    }

    public function __construct()
    {
        $this->gateway = new WC_Gateway_Moyasar_Payment_Form();
        $this->logger = wc_get_logger();
    }

    private function request_query($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    private function perform_redirect($url)
    {
        wp_safe_redirect($url);
        exit;
    }

    public function handle_user_return(WP $wordpress)
    {
        ini_set('display_errors', 0);

        if ($this->request_query('moyasar_page') != 'return') {
            return;
        }

        if (! $this->request_query('id')) {
            $this->perform_redirect(wc_get_checkout_url());

            return;
        }

        try {
            $order = $this->gateway->get_current_order_or_fail();
            if ($order->is_paid()) {
                $this->logger->info("Order " . $order->get_id() . " already paid");
                $this->perform_redirect($this->gateway->get_return_url($order));

                return;
            }

            $payment_id = $order->get_transaction_id('edit');
            if (!$payment_id) {
                throw new RuntimeException(__('Cannot retrieve saved payment ID for order.', 'moyasar-payments-text'));
            }

            $payment = Moyasar_Quick_Http::make()
                ->basic_auth($this->gateway->api_sk())
                ->get($this->gateway->moyasar_api_url("payments/$payment_id"))
                ->json();

            if ($payment['status'] != 'paid') {
                $message = isset($payment['source']['message']) ? $payment['source']['message'] : 'no message';
                $message = "Payment $payment_id for order was not complete. Message: $message. Payment Status: " . $payment['status'];

                $order->set_status('failed');
                $order->add_order_note($message);
                $order->save();

                wc_add_notice(__($message, 'moyasar-payments-text'), 'error');

                $this->perform_redirect(wc_get_checkout_url());
                return;
            }

            $errors = $this->checkPaymentForErrors($order, $payment);
            if (count($errors) > 0) {
                array_unshift($errors, "Un-matching payment details $payment_id");

                foreach ($errors as $error) {
                    $order->add_order_note($error);
                    wc_add_notice($error, 'error');
                }

                $order->set_status('failed');
                $order->save();

                // Void the payment immediately
                if ($this->gateway->auto_void) {
                    Moyasar_Quick_Http::make()
                        ->basic_auth($this->gateway->api_sk())
                        ->post($this->gateway->moyasar_api_url("payments/$payment_id/void"));
                }

                $this->perform_redirect(wc_get_checkout_url());
                return;
            }

            add_filter('woocommerce_payment_complete_order_status', array($this->gateway, 'determine_new_order_status'), PHP_INT_MAX, 3);

            WC()->cart->empty_cart();

            $paymentSource = $this->paymentSource($payment);

            $order->add_order_note("Payment $payment_id for order is complete.");
            $order->add_order_note("Payment Source: $paymentSource");
            $order->payment_complete($payment_id);
            $order->set_payment_method_title($paymentSource);
            $order->save();

            $this->logger->info("Payment $payment_id is successful. Redirecting to " . $this->gateway->get_return_url($order));

            $this->perform_redirect(
                $this->gateway->get_return_url($order)
            );
        } catch (Moyasar_Http_Exception $e) {
            $message = $e->getMessage();

            if ($e->response->isJson()) {
                $body = $e->response->json();
                $message = isset($body['message']) ? $body['message'] : $message;
            }

            $this->logger->error("Moyasar: $message");
            wc_add_notice($message, 'error');

            $this->perform_redirect(wc_get_checkout_url());
        } catch (Exception $e) {
            $message = $e->getMessage();
            $this->logger->error("Moyasar: $message");
            wc_add_notice($message, 'error');

            $this->perform_redirect(wc_get_checkout_url());
        }
    }

    private function checkPaymentForErrors($order, $payment)
    {
        $errors = [];

        if (strtoupper($order->get_currency()) !== strtoupper($payment['currency'])) {
            $errors[] = 'Order and payment currencies does not match, ' . strtoupper($order->get_currency()) . ' : ' . strtoupper($payment['currency']);
        }

        $orderAmount = Moyasar_Currency_Helper::amount_to_minor($order->get_total(), $order->get_currency());
        if ($orderAmount != $payment['amount']) {
            $errors[] =
                'Order and payment amounts do not match, ' .
                $order->get_total() .
                ' : ' .
                Moyasar_Currency_Helper::amount_to_major($payment['amount'], $payment['currency']);
        }

        return $errors;
    }

    private function paymentSource($payment)
    {
        if (! isset($payment['source']['type'])) {
            return null;
        }

        switch (strtolower($payment['source']['type'])) {
            case 'creditcard':
                return 'Credit Card';
            case 'applepay':
                return 'Apple Pay';
            case 'stcpay':
                return 'stc pay';
            default:
                return null;
        }
    }
}
