<?php
/**
 * Plugin Name: OPay Payment Gateway for WooCommerce
 * Plugin URI: https://open.opayweb.com/egypt-apidocs
 * Description: WooCommerce payment gateway for OPay
 * Version: 2.5.5
 * Author: OPay
 * Author URI: https://opayweb.com/
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	exit;
}

define('OPGFW_WC_OPAY_URL', plugins_url('', __FILE__));
define('OPGFW_TEST_URL', 'https://sandboxapi.opaycheckout.com/api/v2/international/cashier/create');
define('OPGFW_URL', 'https://api.opaycheckout.com/api/v2/international/cashier/create');

function add_opay_gateway_class ( $methods ) {
	$methods[] = 'WC_Gateway_OPay_Bank_Card';
	$methods[] = 'WC_Gateway_OPay_RC_Code';
	$methods[] = 'WC_Gateway_OPay_SHAHRY';
	$methods[] = 'WC_Gateway_OPay_VALU';
	$methods[] = 'WC_Gateway_OPay_WALLET';
	$methods[] = 'WC_Gateway_OPay_Bank_Installment';
	$methods[] = 'WC_Gateway_OPay_Now';
	return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_opay_gateway_class');

function init_opay_gateway_class () {
	include_once 'gateways/bank_card.php';
	include_once 'gateways/rc_code.php';
	include_once 'gateways/shahry.php';
	include_once 'gateways/valu.php';
	include_once 'gateways/m_wallets.php';
	include_once 'gateways/bank_Installment.php';
	include_once 'gateways/opay_now.php';
}
add_action('plugins_loaded', 'init_opay_gateway_class');

//添加hook钩子，设置回调函数
add_action('woocommerce_api_wc_gateway_opay', 'notify');
function notify() {
	$logger = new WC_Logger();
	//获取header信息
	$authorization = !empty($_SERVER['HTTP_AUTHORIZATION'])?sanitize_text_field($_SERVER['HTTP_AUTHORIZATION']):'';
	$timestamp = !empty($_SERVER['HTTP_REQUESTTIMESTAMP'])?sanitize_text_field($_SERVER['HTTP_REQUESTTIMESTAMP']):'';
	if (!$timestamp || !$authorization) {
		throw new Exception('Unknow Header');
	}
	$str = file_get_contents('php://input');
	$logger->add('opay_payment', 'header-authorization：' . $authorization);
	$logger->add('opay_payment', 'header-timestamp：' . $timestamp);
	$logger->add('opay_payment', 'header-body：' . $str);
	$data = json_decode($str, true);
	if (!isset($data['reference'])||!isset($data['orderNo'])) {
		return;
	}
	$opayGatewayPayment = new WC_Gateway_OPay_Bank_Card();
	//验签
	$sign = substr($authorization, 7);
	$logger->add('opay_payment', '签名截取：' . $sign);
	$authJson = 'RequestBody=' . $str . '&RequestTimestamp=' . $timestamp;
	$auth =$opayGatewayPayment->auth($authJson);
	$logger->add('opay_payment', '校验签名：' . $auth);
	if ($sign == $auth) {
    $reference_arr = explode('-',$data['reference']);
    $reference = $reference_arr[2];
		$order = new WC_Order($reference);
		$logger->add('opay_payment', '获取订单信息：' . $order);
		try {
			if (!$order) {
				throw new Exception('Unknow Order id:' . $reference);
			}
			if ('SUCCESS' == $data['status']) {
				if ($order->needs_payment()) {
					if ('BankCard' == $data['payMethod']) {
						$this_obj = new WC_Gateway_OPay_Bank_Card();
						$autocompleteorder = $this_obj->get_option('bankcardautocompleteorder');
					} elseif ('ReferenceCode' == $data['payMethod']) {
						$this_obj = new WC_Gateway_OPay_RC_Code();
						$autocompleteorder = $this_obj->get_option('rccodeautocompleteorder');
					} elseif ('Shahry' == $data['payMethod']) {
						$this_obj = new WC_Gateway_OPay_SHAHRY();
						$autocompleteorder = $this_obj->get_option('shahryautocompleteorder');
					} elseif ('VALU' == $data['payMethod']) {
            $this_obj = new WC_Gateway_OPay_VALU();
            $autocompleteorder = $this_obj->get_option('valuautocompleteorder');
          } elseif ('MWALLET' == $data['payMethod']) {
            $this_obj = new WC_Gateway_OPay_WALLET();
            $autocompleteorder = $this_obj->get_option('walletautocompleteorder');
          } elseif ('BankInstallment' == $data['payMethod']) {
            $this_obj = new WC_Gateway_OPay_Bank_Installment();
            $autocompleteorder = $this_obj->get_option('bankinstallmentautocompleteorder');
          } elseif ('OPAYNOW' == $data['payMethod']) {
            $this_obj = new WC_Gateway_OPay_Now();
            $autocompleteorder = $this_obj->get_option('nowautocompleteorder');
          } else {
						$autocompleteorder = 'no';
					}
					if ('yes' == $autocompleteorder) {
						$order->update_status('completed');
					} else {
						$order->update_status('processing');
					}
				}
			} elseif ('CLOSE' == $data['status']) {
				if ($order->needs_payment()) {
					$order->update_status('cancelled');
				}
			} elseif ('FAIL' == $data['status']) {
				if ($order->needs_payment()) {
					$order->update_status('failed');
				}
			}
		} catch (Exception $e) {
			$params = array(
				'action'=>'fail',
				'errcode'=>$e->getCode(),
				'errmsg'=>$e->getMessage()
			);
			ob_clean();
			print json_encode($params);
			exit;
		}
		$params = array(
			'action'=>'success',
		);
		ob_clean();
		print json_encode($params);
		exit;
	} else {
		$logger->add('opay_payment', '签名有误');
	}
}
