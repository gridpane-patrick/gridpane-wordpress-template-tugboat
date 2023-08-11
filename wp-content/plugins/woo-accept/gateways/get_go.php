<?php
class WC_Gateway_Accept_Get_go extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'accept-get_go';

        if ( !AdminHelper::valid_currency($this->id) || $this->get_option('enabled') == "no") {
            $this->enabled = false;
        }

        $this->icon               = plugins_url('/woo-accept/assets/img/getgo.png');
        $this->has_fields         = true;
        $this->method_title       = __('Get_Go');
        $this->method_description = __('Adds an option to pay with Get_Go by Accept.');
        $this->method_public_description = __('Pay with your Get_Go.');
        $this->order_button_text  = __('Proceed');
        $this->notify_url         = WC()->api_request_url('wc-accept-get_go');
        $this->supports           = ['products'];

        // Gateway unique options
        $this->api_has_iframe   = true;
        $this->api_has_items    = true;
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
        $this->iframe_id            = $this->get_option('iframe_id');
        $this->hmac_secret          = $this->get_option('hmac_secret');
        $this->gateway_short_code   = "VPC";

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_wc-accept-get_go', array($this, 'callback'));
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

        if (isset($_POST['card_token'])) {
            $saved_card = $_POST['card_token'];
        } else {
            $saved_card = false;
        }

        $key = $process->requestPaymentKey($order_id, $saved_card);

        if ( !$key ) {
            return $process->throwErrors("Failed to obtain payment key.");
        }


        $url = "https://accept.paymobsolutions.com/api/acceptance/iframes/$this->iframe_id?payment_token=$key";
        $query = http_build_query([
            "PAYMENT_TOKEN" => $key,
            "IFRAME_ID" => $this->iframe_id
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
        
        if (is_user_logged_in()) {
            global $wpdb;
            $current_user = wp_get_current_user();
            $user_id      = $current_user->ID;
            $table_name   = $wpdb->prefix . 'accept_cards_token';
            $results      = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id", OBJECT);
            if ($results) {
                ?>
				<hr>
				<p>Welcome <?php echo $current_user->first_name ?>, looks like you have saved cards please select which to use or u may use a new one.</p>
				<div style="width: 100%;">
                <?php
                foreach ($results as $i => $row) {
                    ?>
					<input style="display: inline-block;" class="input-radio" id="card_<?php echo $i ?>" type="radio" name="card_token" value="<?php echo $row->token ?>">
					<label style="display: inline-block;" for="card_<?php echo $i ?>"><b><?php echo $row->card_subtype ?></b>: <?php echo $row->masked_pan ?></label>
				<br>
                <?php
                }
                ?>
					<input style="display: inline-block;" class="input-radio"  id="card_other" checked="checked" type="radio" name="card_token" value="">
					<label style="display: inline-block;" for="card_other"><b><?php echo __('Use a new card.'); ?></b></label>
				</div>
                <?php
            }
        }
    }

    public function callback()
    {
        AdminHelper::callback($this->hmac_secret);
    }
}