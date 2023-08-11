<?php

class WC_Gateway_Moyasar_Payment_Form extends WC_Payment_Gateway
{
    public $new_order_status;
    public $in_test_mode = false;
    public $fixed_width = false;
    public $enable_credit_card = true;
    public $enable_apple_pay = false;
    public $enable_stc_pay = false;
    public $supported_networks = array();
    public $auto_void = false;

    private $api_sk;
    private $api_pk;
    private $api_base_url;

    protected $logger;

    public function __construct()
    {
        global $woocommerce;

        $this->id = 'moyasar-form';
        $this->has_fields = false;
        $this->logger = wc_get_logger();
        $this->method_title = __('Moyasar Payments', 'moyasar-payments-text');
        $this->method_description = __('Moyasar Gateway Settings', 'moyasar-payments-text');

        // Feature Support
        $this->supports[] = 'refunds';

        // Load settings from database
        $this->init_form_fields();
        $this->init_settings();

        $this->title = __('Online Payments', 'moyasar-payments-text');
        $this->description = __('Pay with your credit card, Apple Pay, or stc pay.', 'moyasar-payments-text');

        $this->new_order_status = $this->get_option('new_order_status', 'processing');
        $this->in_test_mode = $this->get_boolean_option('in_test_mode');
        $this->fixed_width = $this->get_boolean_option('fixed_width');
        $this->enable_credit_card = $this->get_boolean_option('enable_creditcard', true);
        $this->enable_apple_pay = $this->get_boolean_option('enable_applepay');
        $this->enable_stc_pay = $this->get_boolean_option('enable_stcpay');
        $this->supported_networks = $this->get_option('supported_networks', array());
        $this->api_sk = $this->get_option('api_sk');
        $this->api_pk = $this->get_option('api_pk');
        $this->api_base_url = MOYASAR_API_BASE_URL;
        $this->auto_void = $this->get_boolean_option('auto_void', false);

        add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this, 'add_payment_scripts'), PHP_INT_MAX);
        add_action('woocommerce_before_checkout_form', array($this, 'add_admin_notices'));
        add_filter('woocommerce_gateway_icon', array($this, 'render_gateway_icon'), 1024, 2);
    }

    public function api_sk()
    {
        return $this->api_sk;
    }

    public function api_pk()
    {
        return $this->api_pk;
    }

    public function render_gateway_icon($icon, $id)
    {
        if ($id !== $this->id) {
            return $icon;
        }

        return $this->icon ? '<img src="' . WC_HTTPS::force_https_url( $this->icon ) . '" alt="' . esc_attr( $this->get_title() ) . '" style="width: auto; height: 32px;" />' : '';
    }

    public function add_admin_notices()
    {
        if (! $this->enabled) {
            // Prevent error messages from showing when method is disabled
            return;
        }

        if (empty($this->api_pk) || empty($this->api_sk)) {
            echo '<div class="woocommerce-error">' . esc_html(__('Moyasar API keys are missing.', 'moyasar-payments-text')) . '</div>';
        }

        if (count($this->supported_methods()) == 0) {
            echo '<div class="woocommerce-error">' . esc_html(__('Moyasar: At least one payment method is required.', 'moyasar-payments-text')) . '</div>';
        }

        if (count($this->supported_networks) == 0) {
            echo '<div class="woocommerce-error">' . esc_html(__('Moyasar: At least one network is required.', 'moyasar-payments-text')) . '</div>';
        }
    }

    public function get_boolean_option($key, $default = false)
    {
        $value = $this->get_option($key, null);

        if (is_null($value)) {
            return $default;
        }

        return filter_var($value, 258);
    }

    public function moyasar_api_url($path = '')
    {
        $url = rtrim($this->api_base_url, '/');

        if (!empty(trim($path))) {
            $url .= '/v1/' . ltrim($path, '/');
        }

        return rtrim($url, '/');
    }

    public function init_form_fields()
    {
        $this->form_fields = require __DIR__ . '/../utils/admin-settings.php';
    }

    public function add_payment_scripts()
    {
        if (! is_checkout()) {
            return;
        }

        $mpf_style = MOYASAR_PAYMENT_URL . '/assets/styles/moyasar.css';
        $mpf_script = MOYASAR_PAYMENT_URL . '/assets/scripts/moyasar.js';
        $plugin_style = MOYASAR_PAYMENT_URL . '/assets/styles/plugin.css';
        $plugin_script = MOYASAR_PAYMENT_URL . '/assets/scripts/plugin.js';

        wp_enqueue_style('moyasar-form-stylesheet', $mpf_style, array(), MOYASAR_PAYMENT_VERSION);
        wp_enqueue_style('moyasar-form-plugin-stylesheet', $plugin_style, array(), MOYASAR_PAYMENT_VERSION);

        wp_enqueue_script('polyfill-io-fetch', 'https://polyfill.io/v3/polyfill.min.js?features=fetch%2CObject.assign', array(), null, false);
        wp_enqueue_script('moyasar-form-js', $mpf_script, array(), MOYASAR_PAYMENT_VERSION, true);
        wp_enqueue_script('moyasar-form-plugin-js', $plugin_script, array(), MOYASAR_PAYMENT_VERSION, true);
    }

    public function payment_fields()
    {
        $form_data = array(
            'amount' => $this->current_order_amount(),
            'currency' => $this->current_order_currency(),
            'description' => $this->current_session_payment_description(),
            'publishable_api_key' => $this->api_pk,
            'methods' => $this->supported_methods(),
            'supported_networks' => $this->supported_networks,
            'callback_url' => moyasar_page_url('return'),
            'base_url' => $this->moyasar_api_url(),
            'site_url' => moy_trimmed_site_url(),
            'fixed_width' => $this->fixed_width,
            'apple_pay' => array(
                'label' => moy_get_site_domain(),
                'country' => $this->current_customer_billing_country(),
                'validate_merchant_url' => $this->moyasar_api_url('applepay/initiate'),
            ),
        );

        require __DIR__ . '/../views/form.php';
    }

    private function supported_methods()
    {
        $methods = array();

        if ($this->enable_credit_card) {
            $methods[] = 'creditcard';
        }

        if ($this->enable_apple_pay) {
            $methods[] = 'applepay';
        }

        if ($this->enable_stc_pay) {
            $methods[] = 'stcpay';
        }

        return $methods;
    }

    private function current_session_payment_description()
    {
        $customer = $this->current_session_customer();
        $description = "A Payment for woocommerce order TBD";

        $order_id = absint($this->get_query_param('order-pay'));

        // Gets order total from "pay for order" page.
        if (0 < $order_id) {
            $description = str_replace('TBD', $order_id, $description);
        }

        if ($email = $customer->get_email()) {
            $description .= ", customer: $email";
        }

        return $description;
    }

    private function current_customer_billing_country()
    {
        $customer = $this->current_session_customer();
        return $customer->get_billing_country();
    }

    private function current_order_currency()
    {
        $order_id = absint($this->get_query_param('order-pay'));

        // Gets order total from "pay for order" page.
        if (0 < $order_id) {
            $order = wc_get_order($order_id);
            return strtoupper($order->get_currency('edit'));
        }

        return strtoupper(get_woocommerce_currency());
    }

    private function current_order_amount()
    {
        $total = floatval($this->get_order_total());
        $currency = $this->current_order_currency();

        return Moyasar_Currency_Helper::amount_to_minor($total, $currency);
    }

    private function current_session_customer()
    {
        return WC()->customer;
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        $order_key = $order->get_order_key();

        // Set status to pending to indicate that we are still processing payment
        $order->set_status('pending', __('Awaiting payment to complete', 'moyasar-payments-text'));
        $order->save();

        $metadata = [];

        if ($order->has_shipping_address()) {
            foreach ($order->get_address('shipping') as $key => $value) {
                $metadata["shipping_$key"] = $value;
            }
        }

        if ($order->has_billing_address()) {
            foreach ($order->get_address('billing') as $key => $value) {
                $metadata["billing_$key"] = $value;
            }
        }

        return [
            'result' => 'success',
            'order_id' => $order_id,
            'order_key' => $order_key,
            'redirect' => moyasar_page_url('return') . "&order-pay=$order_id&key=$order_key",
            'metadata' => $metadata
        ];
    }

    public function get_current_order()
    {
        $session = WC()->session;

        if (! $session) {
            return null;
        }

        $order_id = $session->get('order_awaiting_payment');

        if (absint($order_id) > 0) {
            return wc_get_order($order_id);
        }

        return $this->get_order_from_url();
    }

    public function get_order_from_url()
    {
        $order_id = absint($this->get_query_param('order-pay'));
        $order = wc_get_order($order_id);
        if (! $order || $order_id !== $order->get_id()) {
            return null;
        }

        $order_key = wp_unslash($this->get_query_param('key', 0));
        if (! hash_equals($order->get_order_key(), $order_key)) {
            return null;
        }

        WC()->session->set('order_awaiting_payment', $order_id);
        return $order;
    }

    public function get_current_order_or_fail()
    {
        $order = $this->get_current_order();
        if (! $order instanceof WC_Order) {
            throw new RuntimeException(__('Cannot retrieve current order', 'moyasar-payments-text'));
        }

        return $order;
    }

    public function get_order_from_url_or_fail()
    {
        $order = $this->get_order_from_url();
        if (! $order instanceof WC_Order) {
            throw new RuntimeException(__('Cannot retrieve current order', 'moyasar-payments-text'));
        }

        return $order;
    }

    public function determine_new_order_status($status, $id, $instance)
    {
        return $this->new_order_status;
    }

    private function get_query_param($key, $default = null)
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        try {
            if ($order_id <= 0) {
                throw new RuntimeException('Invalid order ID');
            }

            $order = wc_get_order($order_id);
            if (! $order) {
                throw new RuntimeException("Could not find order with ID $order_id");
            }

            $payment_id = $order->get_transaction_id('edit');
            if (!$payment_id) {
                throw new RuntimeException('No Payment is associated with this order.');
            }

            if ($amount > 0) {
                $amount = Moyasar_Currency_Helper::amount_to_minor($amount, $order->get_currency());
            }

            $payment = Moyasar_Quick_Http::make()
                ->basic_auth($this->api_sk())
                ->get($this->moyasar_api_url("payments/$payment_id"))
                ->json();

            $createdAt = new DateTime($payment['created_at']);
            $nowMinus2Hours = (new DateTime())->sub(new DateInterval('PT2H'));

            if ($nowMinus2Hours < $createdAt && ($amount == 0 || $amount == $payment['amount'])) {
                Moyasar_Quick_Http::make()
                    ->basic_auth($this->api_sk())
                    ->post($this->moyasar_api_url("payments/$payment_id/void"));
            } else {
                Moyasar_Quick_Http::make()
                    ->basic_auth($this->api_sk())
                    ->post(
                        $this->moyasar_api_url("payments/$payment_id/refund"),
                        array_filter(['amount' => $amount])
                    );
            }

            return true;
        } catch (Moyasar_Http_Exception $e) {
            $message = $e->getMessage();

            if ($e->response->isJson()) {
                $body = $e->response->json();
                $message = isset($body['message']) ? $body['message'] : $message;
            }

            return new WP_Error('refund_error', $message);
        } catch (Exception $e) {
            return new WP_Error('refund_error', $e->getMessage());
        }
    }
}
