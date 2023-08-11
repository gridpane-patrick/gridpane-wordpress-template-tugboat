<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_Gateway_OPay_Bank_Card extends WC_Payment_Gateway {

	public function __construct() {
		$this->id = 'bank_card';
		$this->icon = OPGFW_WC_OPAY_URL . '/img/BANK_CARD.png';
		$this->has_fields = false;
		$this->method_title = 'Bank Card';
		$this->method_description = 'OPay provide merchants with the tools and services needed to accept online payments. Supports consumers to pay with Visa, MasterCard and Meeza cards.';

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled = $this->get_option('enabled');
		$this->title = $this->settings['title'];
		$this->description = $this->settings['description'];
		$this->testmode = 'yes' === $this->get_option('testmode', 'no');
		$this->secretkey = $this->get_option('secretkey');
		$this->publicsecretkey = $this->get_option('publicsecretkey');
		$this->merchantid = $this->get_option('merchantid');
		$this->orderexpirationtime = $this->get_option('orderexpirationtime');
		$this->bankcardcurrency = $this->get_option('bankcardcurrency');
		if ('PKR' == $this->bankcardcurrency) {
			$this->icon = OPGFW_WC_OPAY_URL . '/img/BANK_CARD_1.png';
		}

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ));
		add_action('woocommerce_thankyou_opay', array(&$this, 'thankyou_page'));
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' => __('Enable/Disable', 'woothemes'),
				'type' => 'checkbox',
				'label' => 'Open OPay Payment',
				'default' => 'no'
			),
			'title' => array(
				'title' => __('Method Title', 'woothemes'),
				'type' => 'text',
				'custom_attributes' => array( 'required' => 'required' ),
				'description' => 'This is the payment method title which customer sees during checkout.',
				'default' => 'Visa/MasterCard',
			),
			'description' => array(
				'title' => 'Method Description',
				'type' => 'textarea',
				'description' => 'This is the payment method description which customer sees during checkout.',
				'default' => 'OPay payment gateway supports Bank Card, Reference Code, Shahry, ValU, Mobile Wallets, Bank Installment, OPayNow payment method etc'
			),
			'merchantid' => array(
				'title' => 'Merchant ID',
				'type' => 'text',
				'default' => '',
				'description' => 'The merchant id in OPay merchant dashboard.',
				'custom_attributes' => array( 'required' => 'required' ),
			),
			'secretkey' => array(
				'title' => 'Secret Key',
				'type' => 'text',
				'default' => '',
				'description' => 'The secret key in OPay merchant dashboard.',
				'custom_attributes' => array( 'required' => 'required' ),
			),
			'publicsecretkey' => array(
				'title' => 'Public Key',
				'type' => 'text',
				'default' => '',
				'description' => 'The public key in OPay merchant dashboard.',
				'custom_attributes' => array( 'required' => 'required' ),
			),
			'orderexpirationtime' => array(
				'title' => 'Order Expiration Time',
				'type' => 'text',
				'default' => 30,
				'description' => 'For example, if you enter 30, the unpaid order will be cancelled automatically after 30 minutes.',
				'custom_attributes' => array( 'required' => 'required' ),
			),
			'bankcardautocompleteorder' => array(
				'title'       => 'Auto Complete Order',
				'label'       => 'Automatically update the status of paid orders.',
				'type'        => 'checkbox',
				'description' => 'If you are selling a virtual product, the status of the paid order will automatically change from Processing to Completed when checked.',
				'default'     => 'no',
			),
			'bankcardcurrency' => array(
				'title'       => 'Monetary Unit',
				'type'        => 'select',
				'description' => 'Please select payment currency.',
				'default'     => 'EGP',
				'options'     => array(
					'EGP'=>'EGP',
					'PKR'=>'PKR',
				),
				'custom_attributes' => array( 'required' => 'required' ),
			),
			'testmode' => array(
				'title'       => 'Test Mode',
				'label'       => 'Enable Debugger',
				'type'        => 'checkbox',
				'description' => 'Helpful while testing, please uncheck if live.',
				'default'     => 'yes',
			),
		);
	}

	public function thankyou_page() {
		if ($this->description) {
			return wpautop(wptexturize($this->description));
		}
	}

	public function process_payment ( $order_id ) {
		$order = new WC_Order($order_id);
		$logger = new WC_Logger();
		if (!$order||!$order->needs_payment()) {
			return array (
				'result' => 'success',
				'redirect' => $this->get_return_url($order)
			);
		}
		$ip = !empty($_SERVER['REMOTE_ADDR'])?sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
		$order_item = $this->get_order_item($order);
		$order_data = $order->get_data();
		$notify_url = add_query_arg('wc-api', 'wc_gateway_opay', home_url('/'));

		$currency = $this->bankcardcurrency;
		if (!$currency) {
			$currency = 'EGP';
			$country = 'EG';
		} else {
			if ('EGP' == $currency) {
				$country = 'EG';
			} elseif ('PKR' == $currency) {
				$country = 'PK';
			}
		}
    $order_str = 'WC-'.date('Ymd',time()).mt_rand(10,99);
		$data=[
			'amount' => [
				'currency' => $currency,
				'total' => $order_item['price'] * 100,
			],
			'userInfo' => [
				'userName' => $order_data['billing']['first_name'].' '.$order_data['billing']['last_name'],
				'userMobile' => $order_data['billing']['phone'],
				'userEmail' => $order_data['billing']['email'],
			],
			'callbackUrl'=> $notify_url,
			'cancelUrl'=> home_url('/'),
			'country' => $country,
			'expireAt'=> $this->orderexpirationtime,
			'payMethod' => 'BankCard',
			'product' => [
				'description' => $order_item['name'],
				'name' => $order_item['name'],
			],
      'reference' => $order_str.'-'.$order_id,
			'returnUrl'=> $this->get_return_url($order),
			'userClientIP'=> "$ip",

		];

		if ($this->testmode) {
			$url = OPGFW_TEST_URL;
		} else {
			$url = OPGFW_URL;
		}
		//拼接header
		$data2 = (string) json_encode($data);
		$logger->add('bank_card', 'body string：' . $data2);
		$timestamp = time();
		$authString = 'RequestBody=' . $data2 . '&RequestTimestamp=' . $timestamp;
		$auth = $this->auth($authString);
		$logger->add('bank_card', 'auth：' . $auth);
		$header = [
		  'Authorization' => 'Bearer '.$auth,
		  'MerchantId' => $this->merchantid,
		  'RequestTimestamp' => $timestamp,
		  'content-type' => 'application/json',
		  'ClientSource' => 'WOOCOMMERCE',
    ];
		try {
			$response = $this->http_post($url, $header, json_encode($data));
			$logger->add('bank_card', 'Order return result：' . $response);
			$result = $response?json_decode($response,true):null;
      if (!$result) {
				throw new Exception('Internal server error', 500);
			}
			if ('00000' != $result['code']) {
				throw new Exception($result['message'], $result['code']);
			}
			return array(
				'result'  => 'success',
				'redirect'=> $result['data']['cashierUrl']
			);
		} catch (Exception $e) {
			wc_add_notice("errcode:{$e->getCode()},errmsg:{$e->getMessage()}", 'error');
			return array(
				'result' => 'fail',
				'redirect' => $this->get_return_url($order)
			);
		}
	}

	public function get_order_item ( $order ) {
		$name = '';
		$code = '';
		$quantity  = 0;
		$price = $order->get_total();
		$order_items = $order->get_items();
		if ($order_items) {
			foreach ($order_items as $item_id => $item) {
				$name .= "{$item['name']} ";
				$code .= "{$item['product_id']} ";
				$quantity += $item['quantity'];
			}
		}
		return ['name' => $name, 'code' => $code, 'quantity' => $quantity, 'price' => $price];
	}

	private function http_post ( $url, $header, $data ) {
    $args = array(
      'body'        => $data,
      'timeout'     => '60',
      'redirection' => '5',
      'httpversion' => '1.0',
      'blocking'    => true,
      'headers'     => $header,
      'cookies'     => array(),
    );
    $result = wp_remote_post( $url, $args );
		if (200 != $result['response']['code']) {
			print_r("invalid httpstatus:{$result['response']['code']} ,response:{$result['response']},detail_error:" . $result['response']['message'], $result['response']['code']);
		}
		return $result['body'];
	}

	public function auth ( $data ) {
		$secretKey = $this->secretkey;
		$auth = hash_hmac('sha512', $data, $secretKey);
		return $auth;
	}
}
