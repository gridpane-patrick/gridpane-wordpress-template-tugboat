<?php
class PayHelper
{
    const HOST = "https://accept.paymobsolutions.com/api/";

    public function __construct(WC_Order $order, Array $config)
    {
        $this->order                = $order;
        $this->auth_token           = $this->requestToken($config['api_key']);
        $this->api_key              = $config['api_key'];
        $this->debug                = $config['can_debug'];
        $this->integration_id       = $config['integration_id'];
        $this->integration_currency = $config['integration_currency'];
        $this->has_items            = $config['has_items'];
        $this->has_delivery         = $config['has_delivery'];
        $this->handles_shipping     = $config['handles_shipping'];
        $this->gateway_code         = $config['gateway_code'];
        $this->processOrderData();
    }

    public function isValid()
    {
        return $this->is_valid;
    }

    public function getToken()
    {
        return $this->auth_token;
    }

    public function processOrderData()
    {
        try {
            $order = $this->order;
            $shipping_fees = $order->get_total_shipping() + $order->get_shipping_tax();
            $this->order_unique_id = $order->get_id() .'_'. time();
            $this->order_currency  = $order->get_currency();
            $this->cents = 100;

            if($this->handles_shipping)
            {
                $this->amount_cents = $order->get_total() * $this->cents;
            }else{
                $this->amount_cents = ($order->get_total() - $shipping_fees) * $this->cents;
            }

            if($this->has_items)
            {
                foreach($order->get_items() as $item_id => $item)
                {
                    $product_id = $item->get_product_id();
                    $product = $item->get_product();
                    // var_dump($item['description']);die();
                    $this->order_items[] = [
                        "name" => $item->get_name(),
                        "amount_cents" => intval($product->get_price() * $this->cents),
                        "quantity"=>$item->get_quantity(),
                        "description"=>$product->get_description()
                    ];
                }
            }

            $this->shipping = [
                "email"           => (get_post_meta($order->get_id(), '_shipping_email', true)) ? get_post_meta($order->get_id(), '_shipping_email', true) : "NA",
                "phone_number"    => (get_post_meta($order->get_id(), '_shipping_phone', true)) ? get_post_meta($order->get_id(), '_shipping_phone', true) : "NA",
                "first_name"      => ($order->get_shipping_first_name()) ? $order->get_shipping_first_name() : "NA",
                "last_name"       => ($order->get_shipping_last_name()) ? $order->get_shipping_last_name() : "NA",
                "street"          => $order->get_shipping_address_1() . ' - ' . $order->get_shipping_address_2(),
                "postal_code"     => ($order->get_shipping_postcode()) ? $order->get_shipping_postcode() : "NA",
                "city"            => ($order->get_shipping_city()) ? $order->get_shipping_city() : "NA",
                "country"         => ($order->get_shipping_country()) ? $order->get_shipping_country() : "NA",
                "state"           => ($order->get_shipping_state()) ? $order->get_shipping_state() : "NA",
                "shipping_method" => "PKG",
                "building"        => "NA",
                "apartment"       => "NA",
                "floor"           => "NA",
            ];

            $this->billing = [
                "email"           => $order->get_billing_email(),
                "first_name"      => $order->get_billing_first_name(),
                "last_name"       => $order->get_billing_last_name(),
                "street"          => $order->get_billing_address_1() . ' - ' . $order->get_billing_address_2(),
                "phone_number"    => $order->get_billing_phone(),
                "city"            => ($order->get_billing_city()) ? $order->get_billing_city() : "NA",
                "country"         => ($order->get_billing_country())? $order->get_billing_country() : "NA",
                "state"           => ($order->get_billing_state()) ? $order->get_billing_state() : "NA",
                "postal_code"     => ($order->get_billing_postcode()) ? $order->get_billing_postcode() : "NA",
                "apartment"       => "NA",
                "floor"           => "NA",
                "shipping_method" => "PKG",
                "building"        => "NA",
            ];

            $this->processStoreConfig();

        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }

    }

    public function processStoreConfig()
    {
        $valid = true;
        $reasons = array();

        if ( !version_compare(PHP_VERSION, '5.4.0', '>=') )
        {
            $valid = false;
            $reasons[] = "Required php version >= 5.4.0, your PHP version is: " . PHP_VERSION;
        }

        if ( !extension_loaded('curl'))
        {
            $valid = false;
            $reasons[] = "Required php extension: cURL, this extension is not enabled on this server.";
        }

        $this->error = $reasons;
        $this->is_valid = $valid;
    }

    public function processOrder()
    {
        global $woocommerce;
        $this->order->update_status('pending-payment');
        $this->order->add_order_note('(Awaiting Payment)');
        $this->order->save();
        $woocommerce->cart->empty_cart();
    }

    public function getError()
    {
        $error = "";

        if (is_string($this->error) && $this->error != '')
        {
            $error .= "<li>$this->error</li>";

        } else if (is_array($this->error) && !empty($this->error)) {
            foreach ($this->error as $key => $data) {
                if(is_array($data))
                {
                    $hints  = "";
                    $field = "$key: ";
                    foreach ($data as $text)
                    {
                        $hints .= " $text ";
                    }
                }else{
                    $field = "";
                    $hints = $data;
                }

                $error .= "<li>$field$hints</li>";
            }
        }

        return $error;
    }

    public function throwErrors($error)
    {
        if( $this->debug )
        {
            global $woocommerce;
            global $wp_version;
            $version = ACCEPT_PLUGIN_VERSION;
            echo "<!-- You are seeing this because you have debug turned on in your payment settings -->";
            print_r($this->response_raw_data);
            echo "<!-- WooCommerce settings/Payments/Payment method/ Uncheck Debugger box. -->";
        }

        if( isset($_REQUEST['pay_for_order']) && $_REQUEST['pay_for_order'] === "true" ){
            wc_add_notice($error, 'error');
        }else{
            throw new Exception($error);
        }
    }

    public function requestToken($api_key)
    {
        $data = [
            'api_key' => $api_key
        ];

        $request = $this->HttpPost('auth/tokens', $data);

        if ($request){
            if( isset($request->token) )
            {
                $this->merchant = $request->profile->id;
                return $request->token;
            }
        }

        return false;
    }

    public function requestOrder()
    {
        $data = [
            "delivery_needed"   => $this->has_delivery,
            "merchant_id"       => $this->merchant,
            "amount_cents"      => $this->amount_cents,
            "currency"          => $this->order_currency,
            "merchant_order_id" => $this->order_unique_id,
            "items"             => ($this->order_items) ? $this->order_items : [],
            "shipping_data"     => $this->shipping,
        ];

        $order = $this->HttpPost("ecommerce/orders", $data);

        if( $order ){
            if( isset($order->id) )
            {
                return $order->id;
            }
        }

        return false;
    }

    public function requestPaymentKey($order_id, $saved_card = false)
    {
        $data = [
            "amount_cents"   => $this->amount_cents,
            "expiration"     => 36000,
            "order_id"       => $order_id,
            "billing_data"   => $this->billing,
            "currency"       => $this->order_currency,
            "integration_id" => $this->integration_id,
            "lock_order_when_paid" => true
        ];

        if ($saved_card)
        {
            $data['token'] = $saved_card;
        }

        $payment = $this->HttpPost("acceptance/payment_keys", $data);

        if ($payment){
            if( isset($payment->token) )
            {
                $this->payment_token = $payment->token;
                return $payment->token;
            }
        }

        return false;
    }

    public function requestKioskId()
    {
        $data = [
            "source" => ["identifier"=>"AGGREGATOR", "subtype"=>"AGGREGATOR"],
            "billing" => $this->billing,
            "payment_token" => $this->payment_token,
        ];

        $request = $this->HttpPost("acceptance/payments/pay", $data);

        if ($request){
            if( isset($request->pending) && $request->pending )
            {
                if( isset($request->data->bill_reference) )
                {
                    return $request->data->bill_reference;
                }
            }
        }

        return false;
    }

    public function requestWalletUrl($phone_number)
    {
        $data = [
            "source"        => ["identifier"=> $phone_number, "subtype"=>"WALLET"],
            "billing"       => $this->billing,
            "payment_token" => $this->payment_token,
        ];

        $request = $this->HttpPost("acceptance/payments/pay", $data);

        if ($request) {
            if( isset($request->redirect_url) )
            {
                return $request->redirect_url;
            }
        }

        return false;
    }

    private function HttpPost($url_path, $data = [])
    {
        $response = wp_remote_post( self::HOST.$url_path, [
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => [
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer $this->auth_token"
                ],
                'body' => json_encode($data),
                'cookies' => array()
            ]
        );
        
        if ( is_wp_error( $response ) ) {
            $this->error = $response->get_error_message();
        }
        
        $this->response_raw_data = json_decode($response['body'], false);
        return $this->response_raw_data;
    }
}
