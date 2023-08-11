<?php

/**
 * HayperPay main class created to extends from it 
 * when create a new paymentsgateways
 * 
 */
class Hyperpay_main_class extends WC_Payment_Gateway
{

    /**
     * if payments have direct fields on ckeckout page 
     * 
     * @var boolean
     */
    public $has_fields = false;
    protected $loader;
    public $invoice_id;

    /**
     * check if user sigined in or not 
     * 
     * @var boolean
     */
    protected $is_registered_user = false;

    /**
     * Mada BlackBins
     * 
     * @var array
     */
    protected $blackBins = [];

    /**
     * supported brands thats will showing on settings and checkout page
     * 
     * @var array
     */
    protected $supported_brands = [];

    /**
     * displayed error msg
     */
    protected $failed_msg = '';

    /**
     * regular expressions
     */

    public $successCodePattern = '/^(000\.000\.|000\.100\.1|000\.[36])/';
    public $successManualReviewCodePattern = '/^(000\.400\.0|000\.400\.100)/';
    public $pendingCodePattern = '/^(800\.400\.5|100\.400\.500)/';

    /**
     * CopyAndPay script URL
     * 
     * @var string
     */
    protected $script_url = "https://eu-prod.oppwa.com/v1/paymentWidgets.js?checkoutId=";

    /**
     * CopyAndPay prepare checkout link
     * 
     * @method POST
     * @var string
     */
    protected $token_url = "https://eu-prod.oppwa.com/v1/checkouts";

    /**
     * get transaction status
     * @method GET
     * @var string
     * 
     * ##TOKEN## will replace with transaction id when fire the request
     */
    protected $transaction_status_url = "https://eu-prod.oppwa.com/v1/checkouts/##TOKEN##/payment";

    /**
     * get transaction status
     * @method GET
     * @var string
     * 
     * ##TOKEN## will replace with transaction id when fire the request
     */
    protected $capture = "https://eu-test.oppwa.com/v1/payments/";

    /** 
     * Query transaction report
     * 
     * @method GET
     * @var string
     */
    protected $query_url = "https://eu-prod.oppwa.com/v1/query";


    /**
     * payment styles that will show in settings 
     * 
     * @var array
     * 
     */
    protected  $hyperpay_payment_style = [
        'card' =>  'Card',
        'plain' =>  'Plain'
    ];

    protected $dataTosend = [];



    function __construct()
    {

        $this->init_settings(); // <== to get saved settings from database
        $this->init_form_fields(); // <== render form inside admin panel
        $this->is_arabic = substr(get_locale(), 0, 2) == 'ar'; // <== to get current locale 

        $this->testmode = $this->get_option('testmode'); // <== check if payments on test mode 
        $this->title = $this->get_option('title'); // <== get title from setting
        $this->trans_type = $this->get_option('trans_type'); // <== get transaction type [DB / Pre-Auth] from setting
        $this->trans_mode = $this->get_option('trans_mode'); // <== get transaction mode [INTERNAL / EXTERNAL / LIVE] from setting
        $this->accesstoken = $this->get_option('accesstoken'); // <== get accesstoke from setting
        $this->entityid = $this->get_option('entityId'); // <== get entityId from setting
        $this->brands = is_array($this->get_option('hyper_pay_brands')) ? $this->get_option('hyper_pay_brands') : [$this->get_option('hyper_pay_brands')]; // <== get brands from setting

        $this->payment_style = $this->get_option('payment_style'); // <== get style from setting
        $this->mailerrors = $this->get_option('mailerrors'); // <== get if mail error check or not from setting
        $this->order_status = $this->get_option('order_status'); // <== get order status after success from setting
        $this->redirect_page_id = $this->get_option('redirect_page_id'); // <== after order complete redirect to selected page
        $this->custom_style = $this->get_option('custom_style'); // <== get custom style from setting
        $this->latin_validation = $this->get_option('latin_validation'); // <== get custom style from setting
        $this->currency = get_woocommerce_currency();


        /**
         * if test mode is one 
         * overwrite currents URLs ti test URLs
         */
        if ($this->testmode) {
            $this->query_url = "https://test.oppwa.com/v1/query";
            $this->token_url = "https://test.oppwa.com/v1/checkouts";
            $this->script_url = "https://test.oppwa.com/v1/paymentWidgets.js?checkoutId=";
            $this->transaction_status_url = "https://test.oppwa.com/v1/checkouts/##TOKEN##/payment";
        }

        $this->query_url .= "?entityId=" . $this->entityid;
        $this->transaction_status_url .= "?entityId=" . $this->entityid;

        /**
         * default failed message 
         * @var string
         */
        $this->failed_message =  __('Your transaction not completed .',  'hyperpay-payments');
        $this->success_message = __('Your payment has been processed successfully.', 'hyperpay-payments');

        /**
         * overwrite default update function 
         * 
         * @param woocommerce_update_options_payment_gateways_<payment_id>
         * @param array[class,function_name]
         */

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        /**
         * prepare checkout form
         * 
         * @param string woocommerce_receipt_<payments_id>
         * @param array[class,function_name]
         */

        add_action("woocommerce_receipt_{$this->id}", [$this, 'receipt_page']);

        /**
         * set payments icon from assets/images/BRAND-log.png 
         * 
         * make sure when add new image to rename image according this format BRAND_NAME-logo.svg
         * 
         * @param string woocommerce_gateway_icon
         * @param array[class,function_name]
         * 
         */
        add_filter('woocommerce_gateway_icon', [$this, 'set_icons'], 10, 2);

        /**
         * to include assets/js/admin.js <JavaScript>
         * 
         * @param string admin_enqueue_scripts
         * @param array[class,function_name]
         *          
         */
        add_action('admin_enqueue_scripts', [$this, 'admin_script']);
        add_action("woocommerce_thankyou_order_received_text", [$this, "order_received_text"], 10, 2);
        add_action('before_woocommerce_pay', [$this, 'action_before_woocommerce_pay'], 10, 0);

        add_action('woocommerce_order_action_capture_payment', [$this, 'capture_payment']);
    }

    public function capture_payment($order)
    {

        $uniqueId = $order->get_meta('transaction_id');
        $url = $this->capture . $uniqueId;

        $orderAmount = number_format($order->get_total(), 2, '.', '');
        $amount = number_format(round($orderAmount, 2), 2, '.', '');

        $gateway_name = 'WC_' . ucfirst($order->get_payment_method()) . "_Gateway";
        $gateway = new $gateway_name();

        $data = [
            'headers' => [
                "Authorization" => "Bearer {$gateway->accesstoken}"
            ],
            'body' => [
                "entityId" => $gateway->entityid,
                "amount" => $amount,
                "currency" => $gateway->currency,
                "paymentType" => 'CP',
            ]
        ];

        $response = wp_remote_post($url, $data);
        $resultJson = wp_remote_retrieve_body($response);
        $resultJson = json_decode($resultJson, true);
        $resultCode = $resultJson['result']['code'] ?? '';

        if (preg_match($this->successCodePattern, $resultCode) || preg_match($this->successManualReviewCodePattern, $resultCode)) {
            $order->add_order_note("Captured Successfully");
            $order->update_status($this->order_status);
        } else {
            $order->add_order_note("Captured Faild" . $resultCod['result']['description'] ?? 'Unknown reason');
        }

        $location = $_SERVER['HTTP_REFERER'];
        wp_safe_redirect($location);
        die;
    }



    public function action_before_woocommerce_pay()
    {
        global $wp;

        $order_id = absint($wp->query_vars['order-pay']); // The order ID

        $order    = wc_get_order($order_id);

        if ($order->has_status('on-hold')) {
            $order->update_status('pending');
        } elseif ($order->has_status($this->order_status)) {
            wp_redirect($this->get_return_url($order));
        }
    }

    public function order_received_text($thanks_text, $order)
    {

        $msg = $order->get_meta('gateway_note');
        if ($order->get_payment_method() == $this->id && $order->get_status() == 'on-hold' &&  !empty($msg)) {
            wc_add_notice($msg, "notice");
            wc_print_notices();
        } else {
            return $thanks_text;
        }
    }

    /**
     * for validate settings form
     * @return void
     */

    public function admin_script(): void
    {
        global  $current_tab, $current_section;

        /**
         * to make sure load admin.js just when currents pyments opened
         * 
         */
        if ($current_tab == 'checkout' && $current_section == $this->id) {

            $data = [
                'id' => $this->id,
                'url' => $this->token_url,
                'code_setting' => wp_enqueue_code_editor(['type' => 'text/css'])
            ];

            wp_enqueue_script('hyperpay_admin',  HYPERPAY_PLUGIN_DIR . '/assets/js/admin.js', ['jquery'], false, true);
            wp_localize_script('hyperpay_admin', 'data', $data);
        }
    }


    /**
     * to set payment icon based on supported brands
     * 
     * @param string $icon
     * @param string $id currnet payment id
     * 
     * @return string  $icon new icon
     * 
     */

    public function set_icons($icon, $id): string
    {

        if ($id == $this->id) {
            $icons = "";
            foreach ($this->brands as  $brand) {
                $img = HYPERPAY_PLUGIN_DIR . '/assets/images/default.png';

                if (file_exists(HYPERPAY_ABSPATH . '/assets/images/' . esc_attr($brand) . "-logo.svg"))
                    $img = HYPERPAY_PLUGIN_DIR . '/assets/images/' . esc_attr($brand) . "-logo.svg";

                $icons .= "<img  style='padding:2px ; ' src='$img' >";
            }
            return $icons;
        }
        return $icon;
    }

    /**
     * Here you can define all fiels thats will showning in setting page
     * @return void
     */
    public function init_form_fields(): void
    {

        $this->form_fields = [
            'enabled' => [
                'title' => __('Enable/Disable', 'hyperpay-payments'),
                'type' => 'checkbox',
                'label' => __('Enable Payment Module.', 'hyperpay-payments'),
                'default' => 'no'
            ],
            'testmode' => [
                'title' => __('Test mode', 'hyperpay-payments'),
                'type' => 'select',
                'options' => ['0' => __('Off', 'hyperpay-payments'), '1' => __('On', 'hyperpay-payments')]
            ],
            'title' => [
                'title' => __('Title:', 'hyperpay-payments'),
                'type' => 'text',
                'description' => ' ' . __('This controls the title which the user sees during checkout.', 'hyperpay-payments'),
                'default' => $this->method_title ??  __('Credit Card', 'hyperpay-payments')
            ],
            'trans_type' => [
                'title' => __('Transaction type', 'hyperpay-payments'),
                'type' => 'select',
                'options' => $this->get_hyperpay_trans_type(),
            ],
            'trans_mode' => array(
                'title' => __('Transaction mode', 'hyperpay-payments'),
                'type' => 'select',
                'options' => $this->get_hyperpay_trans_mode(),
                'description' => ''
            ),
            'accesstoken' => [
                'title' => __('Access Token', 'hyperpay-payments'),
                'type' => 'text',
            ],
            'entityId' => [
                'title' => __('Entity ID', 'hyperpay-payments'),
                'type' => 'text',
            ],
            'secret' => [
                'title' => __('Webhook Key', 'hyperpay-payments'),
                'type' => 'text',
            ],
            'webhock' => [
                'title' => __('Webhook URL', 'hyperpay-payments'),
                'type' => 'text',
                'class' => 'disabled',
                'default' => get_site_url() . "/?rest_route=/hyperpay/v1/" . get_class($this)
            ],
            'hyper_pay_brands' => [
                'title' => __('Brands', 'hyperpay-payments'),
                'class' => count($this->supported_brands) !== 1 ?:  'disabled',
                'type' => count($this->supported_brands) > 1 ? 'multiselect' : 'select',
                'options' => $this->supported_brands,
            ],
            'payment_style' => [
                'title' => __('Payment Style', 'hyperpay-payments'),
                'type' => 'select',
                'class' => count($this->hyperpay_payment_style) !== 1 ?:  'disabled',
                'options' => $this->hyperpay_payment_style,
                'default' => 'plain'
            ],
            'custom_style' => [
                'title' => __('Custom Style', 'hyperpay-payments'),
                'type' => 'textarea',
                'description' => 'Input custom css for payment (Optional)',
                'class' => 'hyperpay_custom_style'
            ],
            'mailerrors' => [
                'title' => __('Enable error logging by email?', 'hyperpay-payments'),
                'type' => 'checkbox',
                'label' => __('Yes'),
                'default' => 'no',
                'description' => __('If checked, an email will be sent to ' . get_bloginfo('admin_email') . ' whenever a callback fails.'),
            ],
            'latin_validation' => [
                'title' => __('Enable Input validation (Accept English Characters only)', 'hyperpay-payments'),
                'type' => 'checkbox',
                'label' => __('Yes'),
                'default' => 'yes',
                'description' => __('Disable this option may cause transaction declined by bank due to 3DSecure', 'hyperpay-payments'),
            ],
            'redirect_page_id' => [
                'title' => __('Return Page', 'hyperpay-payments'),
                'type' => 'select',
                'options' => $this->get_pages('Select Page'),
                'description' => __("success page", 'hyperpay-payments')
            ],
            'order_status' => [
                'title' => __('Status Of Order', 'hyperpay-payments'),
                'type' => 'select',
                'options' => $this->get_order_status(),
                'description' => __("select order status after success transaction.", 'hyperpay-payments')
            ]
        ];
    }


    /**
     *  to fill order_status select fiels
     * 
     * @return array
     */
    function get_order_status(): array
    {
        $order_status = [

            'processing' =>  __('Processing', 'hyperpay-payments'),
            'completed' =>  __('Completed', 'hyperpay-payments')
        ];

        return $order_status;
    }

    /**
     *  to fill trans_type select fiels
     * 
     * @return array
     */
    function get_hyperpay_trans_type(): array
    {
        $hyperpay_trans_type = [
            'DB' => 'Debit',
            'PA' => 'Pre-Authorization'
        ];

        return $hyperpay_trans_type;
    }

    /**
     *  to fill trans_mode select fiels
     * 
     * @return array
     */
    function get_hyperpay_trans_mode(): array
    {
        $hyperpay_trans_type = [
            'INTERNAL' => 'Internal',
            'EXTERNAL' => 'External',
            'LIVE' => 'Live'
        ];


        return $hyperpay_trans_type;
    }

    /**
     * This function fire when click on Place order at checkout page
     * @param int $order_id
     * 
     * @return void
     */
    function receipt_page($order_id)
    {
        $error = false;
        $order = new WC_Order($order_id);

        // if we have id param that mean the page result ACI redirection 
        if (isset($_GET['id'])) {
            $token = sanitize_text_field($_GET['id']);
            $url = str_replace('##TOKEN##', $token, $this->transaction_status_url);
            // set header request to contain access token
            $auth = [
                'headers' => ['Authorization' => 'Bearer ' . $this->accesstoken]
            ];
            $response = wp_remote_get($url, $auth);
            $resultJson = wp_remote_retrieve_body($response);
            $resultJson = json_decode($resultJson, true);

            if (isset($resultJson['result']['code'])) {
                $status = $this->check_status($resultJson);
                if ($order_id) {
                    // dynamic fire proper function
                    $order = new WC_Order($order_id);
                    return $this->$status($order, $resultJson);
                } else {
                    $error = true;
                }
            } else {
                $error = true;
            }


            if ($error)
                $this->failed($order,  $resultJson);
        } else { // process a new transaction
            $checkout = $this->prepareCheckout($order_id);
            $token = $checkout['token'];
            $transactionKey = $checkout['transactionKey'];
            $this->renderPaymentForm($order, $token, $transactionKey);
        }
    }

    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 
     * render CopyAndPay form
     * @param WC_Order $order
     * @param string $token
     * @return void
     */
    private function renderPaymentForm(WC_Order $order, string $token, int $transactionKey): void
    {

        $scriptURL = $this->script_url;
        $scriptURL .= $token;

        $payment_brands = $this->brands;
        if (is_array($this->brands))
            $payment_brands = implode(' ', $this->brands);

        $postbackURL = $order->get_checkout_payment_url(true);

        if (parse_url($postbackURL, PHP_URL_QUERY)) {
            $postbackURL .= '&';
        } else {
            $postbackURL .= '?';
        }
        $postbackURL .= 'callback=true';
        $postbackURL .= "&transaction-key=$transactionKey";


        $dataObj = [
            'is_arabic' => esc_js($this->is_arabic),
            'style' => esc_js($this->payment_style),
            'postbackURL' => esc_url($postbackURL),
            'payment_brands' => esc_js($payment_brands)
        ];

        // this key used to query the transaction status on ACI

        // include  CopyAndPay script to show the form
        wp_enqueue_script('wpwl_hyperpay_script', $scriptURL, null, null);

        // include assests\js\script.js to set wpwlOptions 
        wp_enqueue_script('hyperpay_script',  HYPERPAY_PLUGIN_DIR . '/assets/js/script.js', ['jquery'], '4', true);

        // pass data to assests\js\script.js
        wp_localize_script('hyperpay_script', 'dataObj', $dataObj);

        // apply custom style that's entered on setting page <custom_style>


        wp_register_style('hyperpay-inline', false); // phpcs:ignore
        wp_enqueue_style('hyperpay-inline');
        wp_add_inline_style('hyperpay-inline', $this->custom_style);

        if ($this->id == 'hyperpay_mada') {
            wp_enqueue_style('hyperpay_mada_style', HYPERPAY_PLUGIN_DIR . '/assets/css/mada.css');
        }
    }


    /**
     * Process the payment and return the result
     * @param int $order_id
     * @return array[redirect,token,result]
     * 
     */
    public function process_payment($order_id): array
    {
        $order = new WC_Order($order_id);
        /**
         * 
         * validate data to prevent arabic character 
         */

        if ($this->latin_validation == 'yes') {
            $firstName = $order->get_billing_first_name();
            $family = $order->get_billing_last_name();
            $street = $order->get_billing_address_1();
            $city = $order->get_billing_city();
            $email = $order->get_billing_email();

            $data_to_validate = [
                'first name' => $firstName,
                'last name' => $family,
                'street' => $street,
                'city' => $city,
                'email' =>  $email,
            ];

            $this->validate_form($data_to_validate);
        }


        // add g2p_token to url query
        return [
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        ];
    }

    public  function prepareCheckout($order_id)
    {

        global $woocommerce;
        $order = new WC_Order($order_id);


        $shipping_cost = number_format($order->get_shipping_total(), 2, '.', '');

        $orderAmount = number_format($order->get_total(), 2, '.', '');
        $amount = number_format(round($orderAmount, 2), 2, '.', '');

        $firstName = $order->get_billing_first_name();
        $family = $order->get_billing_last_name();
        $street = $order->get_billing_address_1();
        $city = $order->get_billing_city();
        $state = $order->get_billing_state() ?? $city;
        $email = $order->get_billing_email();
        $zip = $order->get_billing_postcode();
        $country = $order->get_billing_country();


        $firstName = preg_replace('/\s/', '', str_replace("&", "", $firstName));
        $family = preg_replace('/\s/', '', str_replace("&", "", $family));
        $street = preg_replace('/\s/', '', str_replace("&", "", $street));
        $city = preg_replace('/\s/', '', str_replace("&", "", $city));
        $state = preg_replace('/\s/', '', str_replace("&", "", $state));
        $country = preg_replace('/\s/', '', str_replace("&", "", $country));
        $transactionKey =  rand(11111111, 99999999);


        // set data to post 
        $url = $this->token_url;
        $data = [
            'headers' => [
                "Authorization" => "Bearer {$this->accesstoken}"
            ],
            'body' => [
                "entityId" => $this->entityid,
                "amount" => $amount,
                "currency" => $this->currency,
                "paymentType" => $this->trans_type,
                "merchantTransactionId" => $order_id . "I" . $transactionKey,
                "customer.email" => $email,
                "notificationUrl" =>  $order->get_checkout_payment_url(true),
                "customParameters[bill_number]" => $order_id . "I" . $transactionKey,
                "customer.givenName" => $firstName,
                "customer.surname" => $family,
                "billing.street1" => $street,
                "billing.city" => $city,
                "billing.state" => $state,
                "billing.country" => $country,
                "billing.postcode" => $zip,
                "shipping.postcode" => $zip,
                "shipping.street1" => $street,
                "shipping.city" => $city,
                "shipping.state" => $state,
                "shipping.country" => $country,
                "shipping.cost" => $shipping_cost,
                "customParameters[branch_id]" => '1',
                "customParameters[teller_id]" => '1',
                "customParameters[device_id]" => '1',
                "customParameters[plugin]" => 'wordpress',

            ]
        ];


        if ($this->testmode) {
            $data['body']["testMode"] = $this->trans_mode;
        }



        // add extra parameters if exists
        $data = array_merge_recursive($data, $this->setExtraData($order));

        // HTTP Request to oppwa to get checkout id
        $response = wp_remote_post($url, $data);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
            $description =  json_decode(wp_remote_retrieve_body($response), true)['result']['description'];
            wc_add_notice(__("Problem with payments :$description", 'hyperpay-payments'), 'error');
            throw new \Exception();
        }

        $response = wp_remote_retrieve_body($response);

        $result = json_decode($response, true);



        if (array_key_exists('id', $result)) {
            $token = $result['id'];
        }

        return [
            'token' => $token,
            'transactionKey' => $transactionKey
        ];
    }


    /**
     * to get all pages of website to fill <redirect to> option in admin setting 
     * 
     * @param bool
     * @param bool
     * @return array
     * 
     */
    function get_pages(bool $title = false, bool $indent = true): array
    {
        $wp_pages = get_pages('sort_column=menu_order');
        $page_list = [];

        if ($title)
            $page_list[] = $title;

        foreach ($wp_pages as $page) {
            $prefix = '';
            // show indented child pages?
            if ($indent) {
                $has_parent = $page->post_parent;
                while ($has_parent) {
                    $prefix .= ' - ';
                    $next_page = get_page($has_parent);
                    $has_parent = $next_page->post_parent;
                }
            }
            // add to page list array array
            $page_list[$page->ID] = $prefix . $page->post_title;
        }

        return $page_list;
    }

    /**
     * check if all data valid to post {English Charachter}
     * @param array
     * @return void
     */
    function validate_form(array $data): void
    {
        $errors = [];


        foreach ($data as $key => $field) {
            if (!preg_match("/^[a-zA-Z0-9-._!`'#%&,:;<>=@{}~\$\(\)\*\+\/\\\?\[\]\^\| +]+$/", $field) || strlen($field) < 3)
                $errors[$key] =  __($key, 'hyperpay-payments') . ' ' . __('format error', 'hyperpay-payments');
        }

        if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i', $data['email'])) {
            $errors['email'] =  __('Email format not valid', 'hyperpay-payments');
        }


        if ($errors) {
            foreach ($errors as $msg) {
                wc_add_notice('<strong>*' . $msg  . '</strong>', 'error');
            }
            throw new Exception('Validation Error', 400);
        }
    }

    /**
     * 
     * GET request to transaction report to check if transaction exists or not
     * @param int
     * @return array $response
     * 
     */
    public function queryTransactionReport(string $merchantTrxId): array
    {

        $url =  $this->query_url . "&merchantTransactionId=$merchantTrxId";
        $response = wp_remote_get($url, ["headers" => ["Authorization" => "Bearer {$this->accesstoken}"]]);

        $response = wp_remote_retrieve_body($response);
        $response = json_decode($response, true);

        return $response;
    }



    /**
     * 
     * check the status
     * 
     * @param array $resultJson
     * @return string
     */
    public function check_status(array $resultJson): string
    {

        $status = 'failed';
        $resultCode = $resultJson['result']['code'];

        if (preg_match($this->successCodePattern, $resultCode) || preg_match($this->successManualReviewCodePattern, $resultCode)) {
            $status = 'success';
        } elseif (preg_match($this->pendingCodePattern, $resultCode)) {
            $status = "pending";
        } elseif (isset($resultJson['card']['bin']) && $resultJson['result']['code'] == '800.300.401' && in_array($resultJson['card']['bin'], $this->blackBins)) {
            $this->failed_message = __('Sorry! Please select "mada" payment option in order to be able to complete your purchase successfully.', 'hyperpay-payments');
        }

        return $status;
    }


    /**
     * handel failed pyments 
     * @param WC_Order $order
     * @param string $messege
     * @return void
     */
    public function failed(WC_Order $order,  $resultJson)
    {

        if (isset($_GET["callback"]) && isset($_GET['transaction-key'])) {

            $hpOrderId = $order->get_id();
            $transactionKey = sanitize_text_field($_GET['transaction-key']);
            $merchantTrxId = $hpOrderId . "I" . $transactionKey;
            $queryResponse = $this->queryTransactionReport($merchantTrxId);

            if (array_key_exists("payments", $queryResponse)) {
                $this->processQueryResult($queryResponse, $order);
            }
        }



        $error_code = $resultJson["result"]["code"];
        $error_description =  $resultJson["result"]["description"];
        $aci_msg = $error_code == "600.200.500" ? "configration error" : $error_description;

        $order->add_order_note("{$this->failed_message} $error_code :  $error_description");
        wc_add_notice($this->failed_message, "error");
        wc_add_notice($aci_msg, "error");

        /**
         * get extended description
         */
        if (isset($resultJson["resultDetails"]["ExtendedDescription"])) {
            $resultDetails = $resultJson["resultDetails"]["ExtendedDescription"];
            if ($this->isJson($resultDetails)) {
                $resultDetails = json_decode($resultDetails, true);
                if (array_key_exists("details", $resultDetails)) {
                    $error_list = $resultDetails["details"];
                } elseif (array_key_exists("message", $resultDetails)) {
                    $order->add_order_note("extended description2 : " . $resultDetails['message']);
                    wc_add_notice($resultDetails['message'], "error");
                }
            }
        }

        foreach ($error_list ?? [] as $error) {
            $order->add_order_note("extended description : " .  $error["error"]);
            wc_add_notice($error["error"], "error");
        }


        $order->update_status("cancelled");
        wc_print_notices();
    }

    /**
     * check the result of transaction if success of failed 
     * 
     * @param array $resultJson
     * @param WC_Order $order
     * @return void
     */
    public function processQueryResult(array $resultJson, WC_Order $order)
    {
        unset($_GET["callback"]);

        $payment = end($resultJson["payments"]); // get the last transaction

        if (isset($payment["result"]["code"])) {
            $status = $this->check_status($payment);
            $this->$status($order, $payment);
            die;
        }
    }

    /**
     * set customParameters of requested data 
     * @param WC_Order $order
     * @return array
     */
    public function setExtraData(WC_Order $order): array
    {
        return [];
    }

    /**
     * update success order 
     * @param WC_Order $order
     * @param array $resultJson
     * @return void
     */
    public function success(WC_Order $order, $resultJson)
    {
        global $woocommerce;


        $woocommerce->cart->empty_cart();
        $uniqueId = $resultJson["id"];

        //to add action in order details to capture the pre authorization payments
        if (array_key_exists('paymentType', $resultJson) && $resultJson['paymentType'] == "PA") {
            $order->add_meta_data("is_pre_authorization", true);
            $order->add_meta_data("transaction_id", $uniqueId);
            $order->add_order_note("pre authorization transaction, need to capture");
            $this->order_status = "on-hold";
        }

        if (array_key_exists("invoice_id", $resultJson["resultDetails"])) {
            $this->invoice_id =  $resultJson["resultDetails"]["invoice_id"];
            $order->add_meta_data("invoice_id", $this->invoice_id);
            $order->add_order_note("invoice id : " . $this->invoice_id);
        }

        $order->add_order_note($this->success_message . __("Transaction ID: ", "hyperpay-payments") . esc_html($uniqueId));
        $order->update_status($this->order_status);
        $order->save();



        wp_redirect($this->get_return_url($order));
    }

    /**
     * update pending order 
     * @param WC_Order $order
     * @param array $resultJson
     * @return void
     */
    public function pending(WC_Order $order, $resultJson)
    {
        global $woocommerce;


        $order->update_status("on-hold");
        $order->add_meta_data("gateway_note", __("Transaction is pending confirmation from ", "hyperpay-payments") . str_replace("hyperpay_", "", $order->get_payment_method()));
        $order->save();

        $woocommerce->cart->empty_cart();
        $uniqueId = $resultJson["id"];

        $order->add_order_note("the order waiting gateway confirmation" . __("Transaction ID: ", "hyperpay-payments") . esc_html($uniqueId));
        wp_redirect($this->get_return_url($order));
    }
}
