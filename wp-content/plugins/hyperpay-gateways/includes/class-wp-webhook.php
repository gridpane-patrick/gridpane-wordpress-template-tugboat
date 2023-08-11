<?php

add_action('rest_api_init', 'hyperpay_rest_orders');

function hyperpay_rest_orders($request)
{
  register_rest_route('hyperpay/v1', '/(?P<method>[a-zA-Z0-9_]+)', array(
    'methods' => 'POST',
    'callback' => 'hyperpay_handel_order_status',
    'permission_callback' => 'hyperpay_auth_chech'

  ));
}


function hyperpay_handel_order_status($request)
{

  $payment_method = $request['method'];
  $gateway = new $payment_method();

  $secret = $gateway->get_option('secret');
  $initialization_vector = getallheaders()['X-Initialization-Vector'];
  $authentication_tag = getallheaders()['X-Authentication-Tag'];

  $key = hex2bin($secret);
  $iv = hex2bin($initialization_vector);
  $auth_tag = hex2bin($authentication_tag);
  $cipher_text = hex2bin($request->get_body());

  $result = openssl_decrypt($cipher_text, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $auth_tag);
  $result = json_decode($result, true);

  if ($result['type'] == 'test') {
    return new WP_REST_Response('test success', 200);
  }

  $order_id = $result['payload']['merchantTransactionId'];

  $order = wc_get_order($order_id);

  if (!$order) {
    return new WP_Error("not_found", "Not Found", array("status" => 404));
  }

  if ($order->get_payment_method() == 'hyperpay_zoodpay' && $result['payload']['result']['code'] == '100.396.103') {
    $order->update_status('on-hold');
    return new WP_REST_Response('updated to on-hold', 200);
  }

  if (!in_array($order->get_status(), ['on-hold', 'pending']) || $order->get_payment_method() != $gateway->id) {
    return new WP_Error("rest_forbidden", __("Sorry, you are not allowed to do that."), array("status" => 401));
  }

  if (preg_match($gateway->successCodePattern, $result['payload']['result']['code'])) {
    $uniqueId = $result['payload']['id'];
    $order_final_status = $result['payload']['paymentType'] == 'PA' ? 'on-hold' : $gateway->get_option('order_status');
    $order->add_order_note("Updated by webhook " . __("Transaction ID: ", "hyperpay-payments") . esc_html($uniqueId));
    if ($result['payload']['paymentType'] == 'CP')
      $order->add_order_note("Captured by webhook ");
    $order->update_status($order_final_status);
    $order->save();
  }


  return new WP_REST_Response('updated to success', 200);
}


function hyperpay_auth_chech($request)
{
  if (!class_exists($request['method'])) {
    return false;
  }
  return true;
}

if (!function_exists('getallheaders')) {
  foreach ($_SERVER as $name => $value) {
    /* RFC2616 (HTTP/1.1) defines header fields as case-insensitive entities. */
    if (strtolower(substr($name, 0, 5)) == 'http_') {
      $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
    }
  }
  return $headers;
}
