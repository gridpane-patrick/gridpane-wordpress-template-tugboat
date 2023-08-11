<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       piwebsolution.com
 * @since      1.0.0
 *
 * @package    Pi_Dcw
 * @subpackage Pi_Dcw/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Pi_Dcw
 * @subpackage Pi_Dcw/public
 * @author     PI Websolution <sales@piwebsolution.com>
 */
class Pi_Dcw_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public $continue_shopping;
	public $global_redirect;
	public $ajax_support;
	public $redirect_page;
	public $redirect_custom_url;
	public $custom_url;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->continue_shopping = get_option('pi_dcw_global_disable_continue_shopping_btn',0);
		$this->global_redirect = get_option('pi_dcw_global_redirect',1);
		$this->ajax_support = get_option('pi_dcw_global_ajax_support',1);
		$this->redirect_page = get_option('pi_dcw_global_redirect_to_page',0);
		$this->redirect_custom_url = get_option('pi_dcw_global_redirect_custom_url',0);
		$this->custom_url = get_option('pi_dcw_global_custom_url',"");
		$this->disable_cart = get_option('pi_dcw_disable_cart',1);
		$this->single_page_checkout = get_option('pi_dcw_single_page_checkout',1);
		
		add_filter( 'woocommerce_add_to_cart_redirect', array($this,'redirect_to_selected_page'),1000 );

		
		add_filter( 'woocommerce_get_script_data', array( $this, 'add_script_data' ), 10, 2 );
		
		add_filter( 'allowed_redirect_hosts', array( $this, 'allowedHost' ),10, 2);

		if($this->continue_shopping){
			add_filter( 'wc_add_to_cart_message_html', array($this,'remove_continue_shopping'));
		}

		if($this->disable_cart == 1){
			add_action( 'template_redirect',array($this,'cart_redirect'));
			remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
		}

		if($this->single_page_checkout == 1){
		add_filter( 'the_content', array($this,'get_checklist_template') ) ;
		}
	}

	
	function get_checklist_template($content) {
		global $post;
		$cart_id = wc_get_page_id('checkout');
		if ($post->ID == $cart_id) {
			wp_enqueue_style($this->plugin_name);
			wp_enqueue_script($this->plugin_name);
			ob_start();
			if ( !is_wc_endpoint_url( 'order-received' ) ){
				echo do_shortcode('[woocommerce_cart]');
			}
			echo do_shortcode('[woocommerce_checkout]');
			
			
			
			$output = ob_get_contents();
			ob_end_clean();
			$content = $output;
		}
		return $content;
	}

	function cart_redirect($permalink) {
		$cart_id = wc_get_page_id('cart');
		$checkout_id = wc_get_page_id('checkout');

		if($cart_id == $checkout_id) { return; }

		if ( ! is_cart() ) { return; }
		if ( WC()->cart->get_cart_contents_count() == 0 ) {
            wp_redirect( apply_filters( 'wcdcp_redirect', wc_get_page_permalink( 'shop' ) ) );
            exit;
        }

        // Redirect to checkout page
        wp_redirect( wc_get_checkout_url(), '301' );
        exit;
	}

	function redirect_to_selected_page( $url ) {

		if (defined('DOING_AJAX') && DOING_AJAX) return "";

		$global_redirect = $this->global_redirect;

		if(isset($_POST['pi_quick_checkout']) || isset($_GET['pi_quick_checkout'])){

			$remove_other_product_on_quick_checkout = get_option('pisol_dcw_remove_other_product', 0);

			if(!empty($remove_other_product_on_quick_checkout)){
				$quick_checkout_product_id = !empty($_POST['pi_quick_checkout']) ? $_POST['pi_quick_checkout'] : ( !empty($_GET['pi_quick_checkout']) ? $_GET['pi_quick_checkout'] : '');

				if(isset($_POST['quantity'])){
					$quantity = $_POST['quantity'];
				}elseif(isset($_GET['quantity'])){
					$quantity = $_GET['quantity'];
				}else{
					$quantity = 1;
				} 

				if(isset($_POST['variation_id'])){
					$variation_id = $_POST['variation_id'];
				}elseif(isset($_GET['variation_id'])){
					$variation_id = $_GET['variation_id'];
				}else{
					$variation_id = 0;
				} 

				self::removeOtherProductFromCart($quick_checkout_product_id, $variation_id,  $quantity);
			}

			$url = Pi_WooCommerce_Quick_Buy_Auto_Add::get_redirect_url();
			return $url;
		}



		if($global_redirect == 1){
			$redirect_to = (int)$this->redirect_page;
			if(empty($this->redirect_custom_url)){
				if($redirect_to == 0 || $redirect_to == ""){
					$url = wc_get_checkout_url();
				}else{
					$url = get_permalink($redirect_to);
				}
			}else{
				if($this->custom_url == ""){
					$url = wc_get_checkout_url();
				}else{
					$url = $this->custom_url;
				}
			}
		}

		
		if ( ! isset( $_REQUEST['add-to-cart'] ) || ! is_numeric( $_REQUEST['add-to-cart'] ) ) {
			
			return $url;
		}
		

		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );

		$variation_id = isset($_REQUEST['variation_id']) && is_numeric($_REQUEST['variation_id']) ? absint($_REQUEST['variation_id']) : 0;

		$disable_redirect_for_this_product = apply_filters("pi_disable_redirect_for_this_product","no", $product_id, $variation_id); 
		if($disable_redirect_for_this_product == "yes"){
			return $url = "#";
		}

		$overwrite_global = apply_filters("pi_dcw_product_overwrite_global","no", $product_id, $variation_id); 
		$product_redirect_to_page = apply_filters("pi_dcw_product_redirect_to_page","no", $product_id, $variation_id); 
		
		if($overwrite_global == 'yes' && $disable_redirect_for_this_product != 'yes'){
			if($product_redirect_to_page == ""){
				$url = wc_get_checkout_url();
			}else{
				$url = $product_redirect_to_page;
			}
		}
		
		return $url;
	}

	static function removeOtherProductFromCart($product_id, $variation_id, $quantity){
		if(empty($product_id)) return;

		if(function_exists('WC') && isset(WC()->cart)){
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( $cart_item['product_id'] != $product_id || (!empty($cart_item['variation_id']) && $cart_item['variation_id'] != $variation_id)) {

						WC()->cart->remove_cart_item($cart_item_key);

				}else{
					$remove_even_same_product = get_option('pisol_dcw_remove_event_same_product', 1);

					if(!empty($remove_even_same_product)){
						WC()->cart->set_quantity( $cart_item_key, $quantity ); 
					}
				}
			}
		}
	}

	function remove_continue_shopping( $string, $product_id = 0 ) {
		$start = strpos( $string, '<a href=' ) ?: 0;
		$end = strpos( $string, '</a>', $start ) ?: 0;
		return substr( $string, $end ) ?: $string;
	}

	public function add_script_data( $params, $handle ) {
		$global_redirect = $this->global_redirect;

		if($global_redirect == 1){
			if ( 'wc-add-to-cart' == $handle ) {
				if(!is_product()){
					$params = array_merge( $params, array(
						'cart_redirect_after_add' => 'no', 
						/* 
						keep this no as we are implementing our own redirect script in pro version to handle product specific overwrite in ajax, 
						in FREE version we are making this as yes as in free version we dont implement redirect
						*/
					) );
				}else{
					global $post;
					$product_id = $post->ID;
					$redirect_url = $this->productRedirectUrl($product_id);
					$params = array_merge( $params, array(
						'cart_redirect_after_add' => empty($redirect_url) ? 'no' : 'yes', 
						'cart_url'=>$redirect_url
						/* 
						keep this no as we are implementing our own redirect script in pro version to handle product specific overwrite in ajax, 
						in FREE version we are making this as yes as in free version we dont implement redirect
						*/
					) );
				}
			}
		}else{
			if ( 'wc-add-to-cart' == $handle ) {
				$params = array_merge( $params, array(
					'cart_redirect_after_add' => 'no',
				) );
			}
		}
		
		
		
		return $params;
	}

	function productRedirectUrl($product_id){
		$overwrite_global = apply_filters("pi_dcw_product_overwrite_global","no", $product_id); 

		$product_redirect_to_page = apply_filters("pi_dcw_product_redirect_to_page","no", $product_id);

		$disable_redirect_for_this_product = apply_filters("pi_disable_redirect_for_this_product","no", $product_id); 

		$url ="";
		if($disable_redirect_for_this_product != 'yes'){
			if($overwrite_global == 'yes'){
				if($product_redirect_to_page == ""){
					$url = wc_get_checkout_url();
				}else{
					$url = $product_redirect_to_page;
				}
			}else{
				$global_redirect_enabled = get_option('pi_dcw_global_redirect',1);
				$redirect_custom_url = get_option('pi_dcw_global_redirect_custom_url',0);
				if(!empty($global_redirect_enabled)){
					if(!empty($redirect_custom_url)){
						$url = get_option('pi_dcw_global_custom_url',"");
					}else{
						$page_id = get_option('pi_dcw_global_redirect_to_page',0);
						$url = get_permalink($page_id);
					}
				}
			}
		}	

		return $url;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pi_Dcw_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pi_Dcw_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pi-dcw-public.css', array(), $this->version, 'all' );
		

		$pi_dcw_buy_now_bg_color = get_option('pi_dcw_buy_now_bg_color', '#ee6443');
		$pi_dcw_buy_now_text_color = get_option('pi_dcw_buy_now_text_color', '#ffffff');

		$buy_now_button_size = get_option('pisol_dcw_button_size','');

		$button_size = "";
		if(!empty($buy_now_button_size)){
			$button_size = "
				.pisol_buy_now_button.pisol_single_buy_now{
					width:{$buy_now_button_size}px !important;
					max-width:100% !important;
				}
			";
		}

		$loading_animation = '';
		if(get_option('pi_dcw_enable_buynow_loading_animation', 0)){
			$loading_animation = '

			.pisol_buy_now_button{
				position:relative;
			}

			.pi-buy-now-clicked:after{
				position: absolute;
				top: 50%;
				left: 15px;
				margin-top: -9px;
				margin-left: -9px;
				opacity: 0;
				transition: opacity 0s ease;
				content: "";
				display: inline-block;
				width: 18px;
				height: 18px;
				border: 2px solid rgba(255, 255, 255, 0.3);
				border-left-color: #FFF;
				border-radius: 50%;
				vertical-align: middle;
				opacity: 1;
				transition: opacity .25s ease;
				-webkit-animation: spin 450ms infinite linear;
				animation: spin 450ms infinite linear;
			}

			';
		}

		$css = "
		.pisol_buy_now_button{
			color:{$pi_dcw_buy_now_text_color} !important;
			background-color: {$pi_dcw_buy_now_bg_color} !important;
		}
		";
		wp_add_inline_style($this->plugin_name, $css.$button_size.$loading_animation);

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pi_Dcw_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pi_Dcw_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pi-dcw-public.js', array( 'jquery' ), $this->version, false );
		
	}

	function allowedHost($host, $redirect_to){
		$new_hosts = array();
		$new_hosts[] = $redirect_to;
		$merged = array_merge( $host ,$new_hosts );
		return $merged;
	}

}
