<?php
class WC_Gateway_Accept_Kiosk extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'accept-kiosk';

        if ( !AdminHelper::valid_currency($this->id) || $this->get_option('enabled') == "no") {
            $this->enabled = false;
        }

        $this->icon               = plugins_url('/woo-accept/assets/img/aman.png');
        $this->has_fields         = true;
        $this->method_title       = __('Aman');
        $this->method_description = __('Adds an option to pay with Kiosk by Accept.');
        $this->method_public_description = __('Pay with Aman');
        $this->order_button_text  = __('Proceed');
        $this->notify_url         = WC()->api_request_url('wc-accept-kiosk');
        $this->supports           = ['products'];

        // Gateway unique options
        $this->api_has_iframe   = false;
        $this->api_has_items    = false;
        $this->api_has_delivery = false;
        $this->api_handles_shipping_fees = true;
        
        if( $this->get_option('enabled') == "no" ){
            $this->api_debug = false;
        }else{
            $this->api_debug = true;
        }

        $this->init_form_fields();
        $this->init_settings();
        $this->title                = $this->get_option('title');
        $this->description          = $this->get_option('description');
        $this->api_key              = $this->get_option('api_key');
        $this->integration_id       = $this->get_option('integration_id');
        $this->iframe_id            = null;
        $this->hmac_secret          = $this->get_option('hmac_secret');
        $this->gateway_short_code   = "CAGG";

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_wc-accept-kiosk', array($this, 'callback'));
    }

    public function admin_options()
    {
        if (AdminHelper::valid_currency($this->id)) {
            AdminHelper::echo_admin_content(
                $this->notify_url,
                $this->api_has_iframe,
                $this->iframe_id,
                $this->integration_id,
                $this->id
            );
            parent::admin_options();
        } else {
            echo '<div class="notice notice-warning"><p><strong>Sorry this gateway does not support your store currency.</strong></p></div>';
        }
    }

    public function init_form_fields()
    {
        $this->form_fields = AdminHelper::return_admin_options(
            $this->method_title,
            $this->method_public_description,
            $this->notify_url,
            $this->api_has_iframe
        );
    }

    public function process_payment($order_id)
    {
        global $woocommerce;
        (function_exists("wc_get_order")) ? $order = wc_get_order($order_id) : $order = new WC_Order($order_id);

        $config = [
            "api_key" => $this->api_key,
            "integration_id" => $this->integration_id,
            "integration_currency" => $this->integration_currency,
            "can_debug" => $this->api_debug,
            "has_iframe" => $this->api_has_iframe,
            "has_items" => $this->api_has_items,
            "handles_shipping" => $this->api_handles_shipping_fees,
            "has_delivery" => $this->api_has_delivery,
            "gateway_code" => $this->gateway_short_code,
        ];

        $process = new PayHelper($order, $config);

        // if ( !$process->isValid() ){
        //     return $process->throwErrors("Please solve all the errors below.");
        // }

        if ( !$process->getToken() ){
            return $process->throwErrors("Failed to authenticate.");
        }

        $order_id = $process->requestOrder();

        if ( !$order_id ){
            return $process->throwErrors("Failed to register order.");
        }

        $key = $process->requestPaymentKey($order_id);

        if ( !$key ) {
            return $process->throwErrors("Failed to obtain payment key.");
        }

        $kiosk_id = $process->requestKioskId();

        if ( !$kiosk_id ) {
            return $process->throwErrors("Failed to obtain Aman bill reference.");
        }

        $url = get_permalink(get_page_by_title('ACCEPT PAYMENTS'));
        $query = http_build_query([
            "KIOSK_ID" => $kiosk_id
        ]);
        
        if( strstr($url, "?") ){
            $to = "$url&$query";
        }else{
            $to = "$url?$query";
        }

        $process->processOrder();
        return ['result' => 'success', 'redirect' => $to];
    }

    public function payment_fields()
    {
        echo "<p>$this->description</p>";
    }

    public function callback()
    {
        AdminHelper::callback($this->hmac_secret);
    }
}
