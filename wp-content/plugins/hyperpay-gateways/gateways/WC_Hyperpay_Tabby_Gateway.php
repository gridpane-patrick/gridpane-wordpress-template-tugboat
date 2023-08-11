<?php

require_once HYPERPAY_ABSPATH . "includes/Hyperpay_main_class.php";
class WC_Hyperpay_Tabby_Gateway extends Hyperpay_main_class
{
  /**
   * should be lower case and uniqe
   * @var string $id 
   */
  public $id = "hyperpay_tabby";
  
  /**
   * The title which appear next to gateway on setting page 
   * @var string $method_title
   */
  public $method_title = "Tabby";

  /**
   * Describtion of gateways which will appear next to title
   * @var string $method_description
   */
  public $method_description = "Tabby Plugin for Woocommerce";


  /**
   * you can overwrite styles options by 
   * uncomment array below
   * 
   * @var array $hyperpay_payment_style
   */

    //    protected  $hyperpay_payment_style = [
    //     "card" => "Card",
    //     "plain" => "Plain"
    // ];


  /**
   * 
   * the Brands supported by the gateway
   * @var array $supported_brands
   */
  protected $supported_brands = [
		"TABBY" => "Tabby",
    ];


  public function __construct()
  {
    parent::__construct();
  }


  /**
   * to set extra parameter on requested data to connector 
   * just uncomment the function below
   * 
   * @param object $order
   * @return array 
   */

  public function setExtraData(WC_Order $order): array
  {
    global $woocommerce;

    $data = [
      "customer.mobile" =>  $order->billing_phone,
    ];

    $cart_index = 0;

    foreach ($woocommerce->cart->get_cart() as $cart_item) {
      $data["cart.items[$cart_index].name"] = $cart_item['data']->get_title();
      $data["cart.items[$cart_index].price"] = (float) number_format((float)$cart_item['data']->get_price(), 2, '.', '');
      $data["cart.items[$cart_index].quantity"] = $cart_item['quantity'];
      $data["cart.items[$cart_index].sku"] =  rand(111111,99999);

      $cart_index++;
    }

    return ['body' => $data];
  }
}
