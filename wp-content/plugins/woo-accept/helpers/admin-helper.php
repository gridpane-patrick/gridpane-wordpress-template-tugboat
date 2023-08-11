<?php
class AdminHelper
{
    public static function valid_currency($gateway)
    {
        $store_currency = get_woocommerce_currency();
        $valid          = false;

        if ($gateway == "accept-online") {
            (in_array($store_currency, ['EGP', 'USD', 'EUR', 'GBP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-kiosk") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-wallet") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-valu") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-installments") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-sympl") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-premium") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-souhoola") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-shahry") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-get_go") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-forsa") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        if ($gateway == "accept-lucky") {
            (in_array($store_currency, ['EGP'])) ? $valid = true : $valid = false;
        }
        return $valid;
    }

    public static function echo_admin_content(
        $notify_url,
        $has_iframe,
        $iframe_id,
        $integration_id,
        $gateway_id
    ) {
        $path = plugins_url('/woo-accept/assets/js/admin.js');
        ($has_iframe) ? $has_iframe = 1 : $has_iframe = 0;

        echo "
            <script>
                var url = '$notify_url';
                var has_iframe = $has_iframe;
                var iframe_id = '$iframe_id';
                var integration_id = '$integration_id';
                var method_string  = '$gateway_id';
            </script>
            <script type='text/javascript' src='$path'></script>
		";

        echo '
            <style type="text/css">
                .achide,.acceptLoader.achide{display: none;}
                .acceptLoader{
                    position: relative;
                    margin: 0 auto;
                    margin-bottom: 2.2rem;
                    width: 75%;
                    height: auto;
                    min-height: 48px;
                    line-height: 1.5em;
                    border-radius: 2px;
                    background-color: #323232;
                    padding-top: 10px;
                    padding-bottom: 10px;
                    padding-right: 25px;
                    padding-left: 70px;
                    font-size: 1.1rem;
                    font-weight: 300;
                    color: #fff;
                    display: -webkit-box;
                    display: -webkit-flex;
                    display: -ms-flexbox;
                    display: flex;
                    -webkit-box-align: center;
                    -webkit-align-items: center;
                    -ms-flex-align: center;
                    align-items: center;
                    -webkit-box-pack: justify;
                    -webkit-justify-content: space-between;
                    -ms-flex-pack: justify;
                    justify-content: space-between;
                    cursor: default;
                    transition: all 750ms;
                }
                .acceptLoader svg{
                    width: 100%;
                    height: 100%;
                }
                .acceptLoader .error svg{
                    fill: red;
                }
                .acceptLoader .success svg{
                    fill: green;
                }
                .acceptLoader .success,.acceptLoader .error{
                    position: absolute;
                    left: 2.5%;
                    width: 35px;
                    height: 35px;
                }
                .spinnerac{
                    position: absolute;
                    left: 2.5%;
                    transform: translateX(-50%);
                    width: 25px;
                    height: 25px;
                    padding: 0;
                    margin: 0;
                    line-height: inherit;
                    border-radius: 50%;
                    border: 5px solid transparent;
                    -webkit-animation: spin 500ms linear infinite;
                    animation: spin 500ms linear infinite;
                }

                .spinnerac.default{
                    border-top: 5px solid #01AEF0;
                    border-bottom: 5px solid #01AEF0;
                }
                .spinnerac.green{
                    border-top: 5px solid green;
                    border-bottom: 5px solid green;
                }
                .spinnerac.red{
                    border-top: 5px solid red;
                    border-bottom: 5px solid red;
                }

                @-webkit-keyframes spin {
                    0%{
                        -webkit-transform: rotate(0deg);
                    }
                    100%{
                        -webkit-transform: rotate(360deg);
                    }
                }

                @keyframes spin {
                    0%{
                        transform: rotate(0deg);
                    }
                    100%{
                        transform: rotate(360deg);
                    }
                }
            </style>
            <div class="acceptLoader achide">
                <span class="spinnerac default"></span>
                <span class="success achide"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"> <path fill="none" d="M0 0h24v24H0V0zm0 0h24v24H0V0z"/> <path d="M16.59 7.58L10 14.17l-3.59-3.58L5 12l5 5 8-8zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/> </svg></span>
                <span class="error achide"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px"height="24px" viewBox="0 0 24 24" enable-background="new 0 0 24 24" xml:space="preserve"> <g id="Bounding_Boxes"> <path opacity="0.87" fill="none" d="M0,0h24v24H0V0z"/> </g> <g id="Outline_1_"> <g> <path d="M12,2C6.47,2,2,6.47,2,12c0,5.53,4.47,10,10,10c5.53,0,10-4.47,10-10C22,6.47,17.53,2,12,2z M12,20c-4.41,0-8-3.59-8-8 s3.59-8,8-8s8,3.59,8,8S16.41,20,12,20z"/> <polygon points="15.59,7 12,10.59 8.41,7 7,8.41 10.59,12 7,15.59 8.41,17 12,13.41 15.59,17 17,15.59 13.41,12 17,8.41     "/> </g> </g> </svg></span>
                <span class="detail"></span>
            </div>
		';
    }

    public static function return_admin_options($method_title, $method_description, $callback_url, $has_iframe)
    {
        $options = [
            'api_key'        => array(
                'title'       => __('API KEY'),
                'description' => __('Enter your Accept Api Key.', 'woocommerce'),
                'type'        => 'text',
            ),
            'login_key'      => array(
                'label' => __('<span id="accept-login" class="button-primary">Authenticate</span>', 'woocommerce'),
                'css'   => 'display:none',
                'type'  => 'checkbox',
            ),
            'hmac_secret'    => array(
                'title'       => __('HMAC Secret'),
                'description' => __('Enter your Accept <a href="https://weaccept.co/portal/settings" target="_blank">HMAC Secret</a>.', 'woocommerce'),
                'type'        => 'password',
            ),
            'integration_id' => array(
                'title'   => __('Integration ID'),
                'type'    => 'select',
                'default' => '',
            )
        ];

        if ($has_iframe) {
            $options['iframe_id'] = array(
                'title'   => __('Iframe ID'),
                'type'    => 'select',
                'default' => '',
            );
        }

        $options = array_merge($options, [
            'callback_processed'   => array(
                'title'       => __('Transaction processed callback'),
                'label'       => __('<span class="button-secondary callback_copy">' . $callback_url . '</span>', 'woocommerce'),
                'css'         => 'display:none',
                'type'        => 'checkbox',
            ),
            'callback_response'    => array(
                'title'       => __('Transaction response callback'),
                'label'       => __('<span class="button-secondary callback_copy">' . $callback_url . '</span>', 'woocommerce'),
                'css'         => 'display:none',
                'type'        => 'checkbox',
            ),
            'enabled'              => array(
                'title'   => __('Enable this method?', 'woocommerce'),
                'type'    => 'checkbox',
                'label'   => __('Enable ' . $method_title),
                'default' => 'no',
            ),
            'title'                => array(
                'title'       => __('Method Title', 'woocommerce'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                'default'     => __($method_title),
            ),
            'description'          => array(
                'title'       => __('Method Description', 'woocommerce'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                'default'     => __($method_description),
            ),
            'debug'                => array(
                'title'       => __('Testing ?', 'woocommerce'),
                'type'        => 'checkbox',
                'label'       => __('Enable Debugger'),
                'description' => __('Helpful while testing, please uncheck if live.', 'woocommerce'),
                'default'     => 'no',
            )
        ]);

        if ($method_title == "Visa / Mastercard" || $method_title == "valU"  || $method_title == "Mobile Wallets" || $method_title == "installments") {
            $options['force_notice'] = array(
                'title'       => __('Help Displaying Notices ?', 'woocommerce'),
                'type'        => 'checkbox',
                'label'       => __('Enable Notice Control'),
                'description' => __("Helpful if your theme doesn't display notices after checkout.<br>If unchecked, The ACCEPT PAYMENTS plugin will not interfere with WooCommerce notices.", 'woocommerce'),
                'default'     => 'no'
            );
        }

        return $options;
    }

    public static function callback($hmac)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $post_data = file_get_contents('php://input');
            $json_data = json_decode($post_data, true);
            $obj = $json_data['obj'];
            $string = $json_data['obj'];
            $type = $json_data['type'];
            if ($json_data['type'] === 'TRANSACTION') {
                $string['order']                  = $string['order']['id'];
                $string['is_3d_secure']           = ($string['is_3d_secure'] === true) ? 'true' : 'false';
                $string['is_auth']                = ($string['is_auth'] === true) ? 'true' : 'false';
                $string['is_capture']             = ($string['is_capture'] === true) ? 'true' : 'false';
                $string['is_refunded']            = ($string['is_refunded'] === true) ? 'true' : 'false';
                $string['is_standalone_payment']  = ($string['is_standalone_payment'] === true) ? 'true' : 'false';
                $string['is_voided']              = ($string['is_voided'] === true) ? 'true' : 'false';
                $string['success']                = ($string['success'] === true) ? 'true' : 'false';
                $string['error_occured']          = ($string['error_occured'] === true) ? 'true' : 'false';
                $string['has_parent_transaction'] = ($string['has_parent_transaction'] === true) ? 'true' : 'false';
                $string['pending']                = ($string['pending'] === true) ? 'true' : 'false';
                $string['source_data_pan']        = $string['source_data']['pan'];
                $string['source_data_type']       = $string['source_data']['type'];
                $string['source_data_sub_type']   = $string['source_data']['sub_type'];
            } elseif ($json_data['type'] === 'DELIVERY_STATUS') {
                $string['order'] = $string['order']['id'];
            }
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $obj = $_REQUEST;
            $string = $_GET;
            $type = 'TRANSACTION';
        } else {
            die('METHOD "' . $_SERVER['REQUEST_METHOD'] . '" NOT ALLOWED');
        }

        $hash = self::hash($hmac, $string, $type);

        // secure connection ?
        if ($hash === $_REQUEST['hmac']) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($type == 'TRANSACTION') {
                    // decode order_id
                    $order_id = substr($obj['order']['merchant_order_id'], 0, -11);
                    (function_exists("wc_get_order")) ? $order = wc_get_order($order_id) : $order = new WC_Order($order_id);

                    if (
                        $obj['success'] === true &&
                        $obj['is_voided'] === false &&
                        $obj['is_refunded'] === false &&
                        $obj['pending'] === false &&
                        $obj['is_void'] === false &&
                        $obj['is_refund'] === false &&
                        $obj['error_occured'] === false
                    ) {
                        $order->update_status('processing');
                        $order->add_order_note('(Payment Approved)' . '</br>' . ' Accept transaction ID: ' . '<b style="color:DodgerBlue;">' . $obj['id'] . '</b>' .  '</br>' . 'Accept Order ID: ' . '<b style="color:DodgerBlue;">' . $obj['order']['id'] . '</b>' . '</br>' . '<a href="https://accept.paymob.com/portal2/en/transactions" target="_blank">Visit Paymob Dashboard</a>');
                        if (isset($obj['data']['receipt_url'])) {
                            $order->add_order_note('<a href="' . $obj['data']['receipt_url'] . '" target="_blank">Receipt Link</a>');
                        }

                        if (isset($obj['data']['down_payment']) && isset($obj['data']['currency'])) {
                            $order->add_order_note('Down payment: ' . $obj['data']['down_payment'] . ' ' . $obj['data']['currency'] . '</br>' . ' Accept transaction ID: ' . '<b style="color:DodgerBlue;">' . $obj['id'] . '</b>' .  '</br>' . 'Accept Order ID: ' . '<b style="color:DodgerBlue;">' . $obj['order']['id'] . '</b>' . '</br>' . '<a href="https://accept.paymob.com/portal2/en/transactions" target="_blank">Visit Paymob Dashboard</a>');
                        }
                    } else if (
                        $obj['success'] === true &&
                        $obj['is_refunded'] === true &&
                        $obj['is_voided'] === false &&
                        $obj['pending'] === false &&
                        $obj['is_void'] === false &&
                        $obj['is_refund'] === false
                    ) {
                        $order->update_status('refunded');
                        $order->add_order_note('(Payment Refunded)' . '</br>' . ' Accept transaction ID: ' . '<b style="color:DodgerBlue;">' . $obj['id'] . '</b>' .  '</br>' . 'Accept Order ID: ' . '<b style="color:DodgerBlue;">' . $obj['order']['id'] . '</b>' . '</br>' . '<a href="https://accept.paymob.com/portal2/en/transactions" target="_blank">Visit Paymob Dashboard</a>');
                    } else if (
                        $obj['success'] === true &&
                        $obj['is_voided'] === true &&
                        $obj['is_refunded'] === false &&
                        $obj['pending'] === false &&
                        $obj['is_void'] === false &&
                        $obj['is_refund'] === false
                    ) {
                        $order->update_status('cancelled');
                        $order->add_order_note('(Payment Cancelled)' . '</br>' . ' Accept transaction ID: ' . '<b style="color:DodgerBlue;">' . $obj['id'] . '</b>' .  '</br>' . 'Accept Order ID: ' . '<b style="color:DodgerBlue;">' . $obj['order']['id'] . '</b>' . '</br>' . '<a href="https://accept.paymob.com/portal2/en/transactions" target="_blank">Visit Paymob Dashboard</a>');
                    } else if (
                        $obj['success'] === false &&
                        $obj['is_voided'] === false &&
                        $obj['is_refunded'] === false &&
                        $obj['is_void'] === false &&
                        $obj['is_refund'] === false
                    ) {
                        $order->update_status('pending-payment');
                        $order->add_order_note('(Payment Pending)' . '</br>' . ' Accept transaction ID: ' . '<b style="color:DodgerBlue;">' . $obj['id'] . '</b>' .  '</br>' . 'Accept Order ID: ' . '<b style="color:DodgerBlue;">' . $obj['order']['id'] . '</b>' . '</br>' . '<a href="https://accept.paymob.com/portal2/en/transactions" target="_blank">Visit Paymob Dashboard</a>');
                    }

                    $order->save();
                    die("Order updated: $order_id");
                } else if ($type == 'TOKEN') {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'accept_cards_token';
                    $user = get_user_by('email', $obj['email']);
                    if ($user) {
                        $token = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = '" . $user->ID . "' AND card_subtype = '" . $obj['card_subtype'] . "' AND masked_pan = '" . $obj['masked_pan'] . "'", OBJECT);
                        if (!$token) {
                            $wpdb->insert(
                                $table_name,
                                [
                                    'user_id' => $user->ID,
                                    'token' => $obj['token'],
                                    'masked_pan' => $obj['masked_pan'],
                                    'card_subtype' => $obj['card_subtype']
                                ]
                            );
                        } else {
                            $wpdb->update(
                                $table_name,
                                [
                                    'user_id' => $user->ID,
                                    'token' => $obj['token'],
                                    'masked_pan' => $obj['masked_pan'],
                                    'card_subtype' => $obj['card_subtype']
                                ],
                                [
                                    'user_id' => $user->ID,
                                    'card_subtype' => $obj['card_subtype'],
                                    'masked_pan' => $obj['masked_pan']
                                ]
                            );
                        }

                        die("Token Saved: user id: $user->ID, user email: " . $obj['email']);
                    }
                }
            } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                // decode order_id
                $order_id = substr($_REQUEST['merchant_order_id'], 0, -11);
                if (empty($order_id) || is_null($order_id) || $order_id === false || $order_id === "") {
                    wp_redirect(get_site_url());
                    exit;
                }

                (function_exists("wc_get_order")) ? $order = wc_get_order($order_id) : $order = new WC_Order($order_id);

                if (
                    $obj['success'] === "true" &&
                    $obj['is_voided'] === "false" &&
                    $obj['is_refunded'] === "false" &&
                    $obj['pending'] === "false" &&
                    $obj['is_void'] === "false" &&
                    $obj['is_refund'] === "false" &&
                    $obj['error_occured'] === "false"
                ) {
                    $redirect_url = WC_Payment_Gateway::get_return_url($order);
                    wc_add_notice(__('Payment successful: ' . $obj['data_message']), 'success');
                }
                elseif($obj['data_message']==="Approved" ) {
                    $redirect_url = WC_Payment_Gateway::get_return_url($order);
                    wc_add_notice(__('Payment successful: ' . $obj['data_message']), 'success');
                } 
                else {
                    $redirect_url = $order->get_checkout_payment_url();
                    wc_add_notice(__('Payment declined: ' . $obj['data_message']), 'error');
                }

                wp_redirect($redirect_url, 301);
                exit;
            }
        } else {
            die("This Server is busy try again later!");
        }
        exit;
    }

    public static function hash($key, $data, $type)
    {
        $str = '';
        switch ($type) {
            case 'TRANSACTION':
                $str =
                    $data['amount_cents'] .
                    $data['created_at'] .
                    $data['currency'] .
                    $data['error_occured'] .
                    $data['has_parent_transaction'] .
                    $data['id'] .
                    $data['integration_id'] .
                    $data['is_3d_secure'] .
                    $data['is_auth'] .
                    $data['is_capture'] .
                    $data['is_refunded'] .
                    $data['is_standalone_payment'] .
                    $data['is_voided'] .
                    $data['order'] .
                    $data['owner'] .
                    $data['pending'] .
                    $data['source_data_pan'] .
                    $data['source_data_sub_type'] .
                    $data['source_data_type'] .
                    $data['success'];
                break;
            case 'TOKEN':
                $str =
                    $data['card_subtype'] .
                    $data['created_at'] .
                    $data['email'] .
                    $data['id'] .
                    $data['masked_pan'] .
                    $data['merchant_id'] .
                    $data['order_id'] .
                    $data['token'];
                break;
            case 'DELIVERY_STATUS':
                $str =
                    $data['created_at'] .
                    $data['extra_description'] .
                    $data['gps_lat'] .
                    $data['gps_long'] .
                    $data['id'] .
                    $data['merchant'] .
                    $data['order'] .
                    $data['status'];
                break;
        }
        $hash = hash_hmac('sha512', $str, $key);
        return $hash;
    }
}
