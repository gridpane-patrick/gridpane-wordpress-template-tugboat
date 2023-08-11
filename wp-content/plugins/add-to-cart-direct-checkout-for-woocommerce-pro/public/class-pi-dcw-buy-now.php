<?php
/**
 * functionality of the plugin.
 *
 * @link       @TODO
 * @since      1.0
 *
 * @package    @TODO
 * @subpackage @TODO
 * @author     Varun Sridharan <varunsridharan23@gmail.com>
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Pi_WooCommerce_Quick_Buy_Auto_Add {

	/**
	 * Class Constructor
	 */
	public function __construct() {
		$this->show_on_product = $this->showOnProduct();
		$this->show_on_archive = $this->showOnArchive();
		

		$this->product_position = get_option('pi_dcw_buy_now_button_position','after_button');
		$this->loop_position = get_option('pi_dcw_buy_now_button_loop_position','after_button');

		$this->product_redirect  = get_option('pi_dcw_buy_now_button_redirect','checkout');
		$this->loop_redirect  = get_option('pi_dcw_buy_now_button_loop_redirect','checkout');

		$this->setup_single_product_quick_buy();
		$this->setup_shop_loop_quick_buy();

		add_filter('woocommerce_add_to_cart_fragments', array($this, 'buyNowUrlInCartFragment'));

		add_shortcode('pi_loop_buynow', array($this, 'shortCodeLoopBuyNow'));
	}

	function showOnProduct(){
		$setting = get_option('pi_dcw_enable_buy_now_button',0);
		$return = $setting == 0 || $setting == "" ? false : true;
		return $return;
	}

	function showOnArchive(){
		$setting = get_option('pi_dcw_enable_buy_now_button_loop',0);
		$return = $setting == 0 || $setting == "" ? false : true;
		return $return;
	}

	public function setup_single_product_quick_buy() {
		$single_pos  = $this->product_position;

		if ( $this->show_on_product == true ) {
			if ( ! empty( $single_pos ) && ! $single_pos == null ) {
				$pos = '';
				if ( $single_pos == 'before_form' ) {
					$pos = 'woocommerce_before_add_to_cart_button';
				}
				if ( $single_pos == 'after_form' ) {
					$pos = 'woocommerce_after_add_to_cart_button';
				}
				if ( $single_pos == 'after_button' ) {
					$pos = 'woocommerce_after_add_to_cart_button';
				}
				if ( $single_pos == 'before_button' ) {
					$pos = 'woocommerce_before_add_to_cart_button';
				}
				add_action( $pos, array( $this, 'add_quick_buy_button' ), 99 );
			}
		}
	}

	public function setup_shop_loop_quick_buy() {
		$single_pos  = $this->loop_position;

		if ( $this->show_on_archive == true ) {
			if ( ! empty( $single_pos ) && ! $single_pos == null ) {

				if($single_pos == 'before_image'){
					add_action( 'woocommerce_before_shop_loop_item', array( $this, 'add_shop_quick_buy_button' ), 1 );
				}else{
					$pos = 'woocommerce_after_shop_loop_item';
					$p   = 5;
					if ( $single_pos == 'after_button' ) {
						$p = 11;
					}
					if ( $single_pos == 'before_button' ) {
						$p = 9;
					}
					add_action( $pos, array( $this, 'add_shop_quick_buy_button' ), $p );
				}
			}
		}
	}

	public function add_quick_buy_button() {
		global $product;
		if(!is_object($product)) return;

		if($product->is_type('external')) return;

		$this->label_product = get_option('pi_dcw_buy_now_button_text','Buy Now');

		$product_id = $product->get_id();
		$class = 'pisol_type_'.$product->get_type();
		$custom_buy_now_class = esc_attr(apply_filters('pi_add_buy_now_custom_class', ''));
		if(!$this->productLevelDisable($product_id)){

			/** old way of working */
			/*
			if ( $product->get_type() == 'variable'){
				echo '<input class="button pisol_single_buy_now pisol_buy_now_button '.$class.'" type="submit" name="pi_quick_checkout" value="'.$this->label_product.'">';
			}else{
				echo '<button class="button pisol_single_buy_now pisol_buy_now_button '.$class.'" type="submit" name="add-to-cart" value="'.$product_id.'">'.$this->label_product.'</button>
				
				';
			}
			*/
			/* removed */

			echo '<button class="button pisol_single_buy_now pisol_buy_now_button '.$class.' '.$custom_buy_now_class.' " type="submit" name="pi_quick_checkout" value="'.esc_attr($product_id).'">'.esc_html($this->label_product).'</button>';

			if($product->is_type('simple')){
				echo '<input  type="hidden" name="add-to-cart" value="'.esc_attr($product_id).'">';
			}
		}
	}
	
	public function add_shop_quick_buy_button() {
		global $product;
		if(!is_object($product)) return;
		
		if($product->is_type('external')) return;
		
		$this->label_loop = get_option('pi_dcw_buy_now_button__loop_text','Buy Now');

		$product_id = $product->get_id();
		if ( $product->get_type() == 'simple' && !$this->productLevelDisable($product_id) ) {
			$link  = $this->get_product_addtocartLink($product, 1, $this->loop_redirect);
			if($link !== false && $product->is_in_stock()){
				echo $this->buttonHtml($link, $this->label_loop);
			}
		}elseif($product->get_type() == 'variable' && !$this->productLevelDisable($product_id)){

			if(get_option('pi_dcw_enable_buy_now_for_variable_product_on_loop',0) == 1){
				$key = md5($product_id);
				$html = pisol_dcw_get_transient($key);
				
				if(false === $html){
					$first_variation = self::getFirstVariation($product);
					if($first_variation === false) return;
					
					$parent_id = $product->get_id();
					$default_attributes = $product->get_default_attributes();
					$attributes = self::getAttributes($first_variation, $default_attributes);
					$link  = $this->get_variableproduct_addtocartLink($parent_id, $first_variation['variation_id'],$attributes, 1, $this->loop_redirect);
					$variation_product = wc_get_product($first_variation['variation_id']);
					if($link !== false && $variation_product->is_in_stock()){
						$html = $this->buttonHtml($link, $this->label_loop);
					}
					
					pisol_dcw_set_transient($key,$html);
					
				}

				echo $html;
			}
		}
	}

	static function getFirstVariation($product){
		$all_variations = $product->get_available_variations();
		$first_variation = self::selectInStockVariation($all_variations);
		return $first_variation;
	}

	static function selectInStockVariation($variations){
        if(empty($variations) || !is_array($variations)) return false;

        foreach($variations as $variation){
            $variation_id = $variation['variation_id'];
            $variation_obj = wc_get_product($variation_id);
            if(!is_object($variation_obj)) continue;

            if($variation_obj->is_in_stock() || $variation_obj->is_on_backorder()){
                return $variation;
            }
        }

        return false;

    }

	static function getAttributes($variation, $default_attributes){
		$list = "";
		foreach($variation['attributes'] as $name => $value){
			$att_name = str_replace('attribute_',"",$name);
			if(empty($value)){
				$value = isset($default_attributes[$att_name]) ? $default_attributes[$att_name] : "";
			}
			$list .='&'.$name.'='.$value;
		}
		return $list;
	}

	public function get_variableproduct_addtocartLink($parent_id, $variation_id,$attributes, $qty = 1 , $page= 'checkout') {
		if ( $variation_id != 0 ) {
			if($page == 'checkout'){
				$checkout = wc_get_checkout_url();
			}elseif($page == 'cart'){
				$checkout = wc_get_cart_url();
			}else{
				$checkout = "";
			}

			$checkout = apply_filters('pi_dcw_buy_now_loop_url', $checkout, $parent_id);

			$link = $checkout.'?add-to-cart='.$parent_id.'&pi_quick_checkout='.$parent_id.'&variation_id='.$variation_id.$attributes;
			return $link;
		}
		return false;
	}

	function productLevelDisable($product_id){
		$disabled = get_post_meta($product_id, 'pi_dcw_product_buy_now_disable',true);
		if($disabled == 'yes'){
			return true;
		}
		return false;
	}

	function buttonHtml($link, $label){
		return '<a class="pisol_buy_now_button" href="'.$link.'">'.$label.'</a>';
	}

	public function get_product_addtocartLink( $product, $qty = 1 , $page= 'checkout') {
		if ( $product->get_type() == 'simple' ) {
			if($page == 'checkout'){
				$checkout = wc_get_checkout_url();
			}elseif($page == 'cart'){
				$checkout = wc_get_cart_url();
			}else{
				$checkout = "";
			}
			/*$link = $checkout.'?pi_quick_checkout=1&add-to-cart='.$product->get_id();*/
			$checkout = apply_filters('pi_dcw_buy_now_loop_url', $checkout, $product->get_id());
			$link = add_query_arg(array(
				'pi_quick_checkout' => $product->get_id(),
				'add-to-cart'=> $product->get_id()
			),
			$checkout);
			return $link;
		}
		return false;
	}

	

	static function get_redirect_url(){
		/** $_POST is from product page and $_GET is from loop */
		$loop_redirect = "";
		$product_id = '';
		if(isset($_POST['pi_quick_checkout'])){
			$loop_redirect = get_option('pi_dcw_buy_now_button_redirect','checkout');
			$product_id = isset($_POST['add-to-cart']) ? $_POST['add-to-cart'] : '';
		}elseif(isset($_GET['pi_quick_checkout'])){
			$loop_redirect = get_option('pi_dcw_buy_now_button_loop_redirect','checkout');
			$product_id = isset($_GET['add-to-cart']) ? $_GET['add-to-cart'] : '';
		}
		
		if($loop_redirect == 'checkout'){
			$checkout = wc_get_checkout_url();
		}elseif($loop_redirect == 'cart'){
			$checkout = wc_get_cart_url();
		}else{
			$checkout = wp_get_referer();
		}

		if(!empty( $product_id )){
			$custom_buy_now_url = get_post_meta($product_id, 'pi_dcw_product_buynow_redirect_url',true);
			if(!empty($custom_buy_now_url)){
				$checkout = $custom_buy_now_url;
			}
		}
		
		$link = apply_filters('pi_dcw_buy_now_loop_redirect_url',$checkout);
		return $link;
	}

	function buyNowUrlInCartFragment($fragment){
		$url = self::get_redirect_url();
		if(!empty($url)) $fragment['pisol_buy_now_redirect_url'] = $url;
		return $fragment;
	}

	function shortCodeLoopBuyNow(){
		ob_start();
		$this->add_shop_quick_buy_button();
		$buy_now = ob_get_contents();
   		ob_end_clean();
		return $buy_now;
	}
}

new Pi_WooCommerce_Quick_Buy_Auto_Add();