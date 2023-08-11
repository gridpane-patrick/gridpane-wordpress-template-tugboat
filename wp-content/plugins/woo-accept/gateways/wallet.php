<?php
class WC_Gateway_Accept_Wallet extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'accept-wallet';

        if ( !AdminHelper::valid_currency($this->id) || $this->get_option('enabled') == "no") {
            $this->enabled = false;
        }
        $this->has_fields         = true;
        $this->icon               = plugins_url('/woo-accept/assets/img/Wallets.png');
        $this->method_title       = __('Mobile Wallets');
        $this->method_description = __('Adds an option to pay with Mobile Wallets by Accept.');
        $this->method_public_description = __('Pay with your mobile wallet.');
        $this->order_button_text  = __('Proceed');
        $this->notify_url         = WC()->api_request_url('wc-accept-wallet');
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
        $this->gateway_short_code   = "UIG";

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_wc-accept-wallet', array($this, 'callback'));
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

        $phone_number = $_POST['accept_wallet_number'];

        if (!$phone_number || empty($phone_number) || !is_numeric($phone_number) || strlen($phone_number) < 10) {
            return $process->throwErrors("Invalid phone number.");
        }
        
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

        $wallet_url = $process->requestWalletUrl($phone_number);

        if ( !$wallet_url ) {
            return $process->throwErrors("Failed to obtain wallet url.");
        }

        $process->processOrder();
        return ['result' => 'success', 'redirect' => $wallet_url];
    }

    public function payment_fields()
    {
        echo "<p>$this->description</p>";
        ?>
        <hr>
        <p>
            <label for="accept_wallet_number">Wallet Phone Number&nbsp;<abbr class="required" title="required">*</abbr></label>
            <span class="woocommerce-input-wrapper">
                <input type="number" class="input-text accept_wallet_number" name="accept_wallet_number" id="accept_wallet_number" value="01">
            </span>
        </p>
    <?php
    }

    public function callback()
    {
        AdminHelper::callback($this->hmac_secret);
    }
}
