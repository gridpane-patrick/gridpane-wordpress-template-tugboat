<?php
/**
 * Plugin Name: Update Cart On Checkout For Woocommerce
 * Author: Plugify
 * Author URI: https://woocommerce.com/vendor/plugify/
 * Version: 1.0.2
 * Developed By: Plugify Team
 * Description: Allow customers to change item quantity or remove items from checkout pages with Update Cart on Checkout for WooCommerce.
 * Woo: 8984762:6fead47dc7053174968ded20ae266dd5
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 4.4
 * Tested up to: 5.3.2
 * WC requires at least: 3.0
 * WC tested up to: 5.*.*
 */
if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}
/**
 * Check if WooCommerce is active
 * if wooCommerce is not active, this module will not work.
 **/



if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function my_admin_notice1() {		
		deactivate_plugins(__FILE__);
		$error_message = __('This plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be installed and active!', 'woocommerce');
		echo esc_attr( $error_message );
		wp_die();
	}
	add_action( 'admin_notices', 'my_admin_notice1' );
}
error_reporting(0);
if (is_admin()) {
	add_action('init', 'plugify_scripts1');		
	add_action('woocommerce_settings_plgfy_ucoc', 'plgfy_pysd_callback_against_mainsetting_content11');		
	add_filter('woocommerce_settings_tabs_array', 'plgfy_pysd_filter_woocommerce_settings_tabs11', 50);
	add_action('wp_ajax_save_gnrl_settings_plgfyrestrictions1', 'save_gnrl_settings_plgfyrestrictions1');
	
	add_action( 'wp_ajax_remove_icon_cupplugify', 'remove_icon_cupplugify'  );
	add_action( 'wp_ajax_nopriv_remove_icon_cupplugify', 'remove_icon_cupplugify'  );

	add_action( 'wp_ajax_clear_all_cart_plugify', 'clear_all_cart_plugify'  );
	add_action( 'wp_ajax_nopriv_clear_all_cart_plugify', 'clear_all_cart_plugify'  );

	add_filter( 'plugin_action_links', 'plugify_add_row_meta1ucoc' , 10, 2 );

	add_action( 'wp_ajax_plugify_qty_change', 'plugify_qty_change'  );
	add_action( 'wp_ajax_nopriv_plugify_qty_change', 'plugify_qty_change'  );
}

function clear_all_cart_plugify() {

		WC()->cart->empty_cart();
		wp_die();
}
function save_gnrl_settings_plgfyrestrictions1() {
	update_option('save_gnrl_settings_plgfyrestrictions1', $_REQUEST);
	wp_die();
}
function plugify_scripts1 () {




	if ( isset ($_GET['page']) && ( 'wc-settings' == $_GET['page'] && isset ( $_GET['tab'] ) && 'plgfy_ucoc' == $_GET['tab'] ) ) {
		wp_enqueue_script('jquery');
		wp_enqueue_style('date_picker_css_plgfyqdp', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', false, '1.0', 'all');
		wp_enqueue_script( 'select2', plugins_url( 'js/select2.min.js', __FILE__ ), false, '1.0', 'all');
		wp_enqueue_style( 'select2', plugins_url( 'js/select2.min.css', __FILE__ ), false, '1.0', 'all' );
		wp_enqueue_script( 'yaml', plugins_url('/js/yaml.min.js', __FILE__), false, '1.0', 'all' );
	}
}
function plgfy_pysd_filter_woocommerce_settings_tabs11 ( $tabs ) {
	$tabs['plgfy_ucoc'] = __('Update Cart On Checkout', 'plgfy_ucoc');		
	return $tabs;
}	
function plgfy_pysd_callback_against_mainsetting_content11 () {

	include('helping_settings_bckend.php');
}	
function plugify_add_row_meta1ucoc( $links, $file ) {	

	if ( 'update-cart-on-checkout-for-woocommerce/update-cart-on-checkout-for-woocommerce.php' == $file ) {
		
		$settings = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=plgfy_ucoc' ) . '">' . esc_html__( 'General Settings', 'woocommerce' ) . '</a>';



	

		array_unshift( $links, $settings);

	}


	return (array) $links;
}

if (!is_admin()) {
	add_action('woocommerce_review_order_before_payment', 'del_all_plgfy');	
	add_action('wp_footer', 'plugify_restri_scrip_disable1');	
	// add_action('wp_head', 'plugify_restri_scrip_disable1');
	add_filter('woocommerce_cart_item_name', 'plgfycup_modify_wc_cart_item_name', 10, 3);
	add_filter ('woocommerce_checkout_cart_item_quantity', 'plgfyucoc_remove_quantity_count', 10, 3 );

}

function del_all_plgfy () {
	
	$plgfydc_all_data=get_option('save_gnrl_settings_plgfyrestrictions1');
	if ('' == $plgfydc_all_data) {
		$plgfydc_all_data=array(

			'enplable'=>'true',
			'plgfyqdp_gst'=>'true',
			'plgfyqdp_rmvallbtnnn'=>'false',

			'woospca_icons'=>'trash',
			'clrfrtrsh'=>'#000000',
			'woosppo_applied_onc'=>'Products',
			'applied_on_ids'=>array(),

			'plgfyqdp_customer_role'=>array()


		);
	}
	
	if (!isset($plgfydc_all_data['plgfyqdp_rmvallbtnnn'])) {
		$plgfydc_all_data['plgfyqdp_rmvallbtnnn'] = 'false';
	}
	if ( 'true' == $plgfydc_all_data['plgfyqdp_rmvallbtnnn'] ) {
		$plugifybdp_shop_link=get_permalink( wc_get_page_id( 'shop' ) );

		?>
	<button type="button" class="clear_all_cart_plugify">Remove All Products</button>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('.clear_all_cart_plugify').on('click', function(e){
				e.preventDefault();

				jQuery.ajax ({
					url: '<?php echo filter_var(admin_url('admin-ajax.php')); ?>',
					type:'POST',
					data:{
						action : 'clear_all_cart_plugify',


					},
					success:function(response) {

						
						window.location.assign('<?php echo filter_var($plugifybdp_shop_link); ?>');
						
						



					}
				});
			});
		});
	</script>
		<?php
	}
	
}
function plgfycup_modify_wc_cart_item_name ( $product_name, $cart_item, $cart_item_key) {

	if (is_checkout()) {
		$plgfydc_all_data=get_option('save_gnrl_settings_plgfyrestrictions1');
		if ('' == $plgfydc_all_data) {
			$plgfydc_all_data=array(
				'enplable'=>'true',
				'plgfyqdp_gst'=>'true',
				'woospca_icons'=>'trash',
				'clrfrtrsh'=>'ff0000',
				'woosppo_applied_onc'=>'Products',
				'applied_on_ids'=>array(),

				'plgfyqdp_customer_role'=>array()

			);
		}
		if ('true' != $plgfydc_all_data['enplable']) {
			return $product_name;
		}

		$found_user_role=false;
		if ('0' != get_current_user_ID() || '' != get_current_user_ID()) {

			$user_meta=get_userdata(get_current_user_ID());
			$user_roles=$user_meta->roles;
			foreach ($user_roles as $key_1 => $value_1) {
				if (isset($plgfydc_all_data['plgfyqdp_customer_role']) && 0 < count($plgfydc_all_data['plgfyqdp_customer_role'])) {
					if (in_array($value_1, $plgfydc_all_data['plgfyqdp_customer_role'])) {
						$found_user_role=true;

					}
				} else {
					$found_user_role=true;

				}

			}
		} else {
			if ('true' == $plgfydc_all_data['plgfyqdp_gst']) {
				$found_user_role=true;
			}
			
		}
		if (!$found_user_role) {
			return $product_name;
		}

		$prodducy=$cart_item['data'];
		$c_p_id=$prodducy->get_id();
		if ('variation' == $prodducy->get_type()) { 
			$c_p_id = wp_get_post_parent_id( $c_p_id );	

		}
		if (isset($plgfydc_all_data['woosppo_applied_onc']) && 'whole'== $plgfydc_all_data['woosppo_applied_onc']) {
			$product_name = ' <i style="color:' . $plgfydc_all_data['clrfrtrsh'] . ';cursor:pointer;" class="fa fa-' . $plgfydc_all_data['woospca_icons'] . ' remove-icon" aria-hidden="true"><button style="display:none !important;" class="finded" id="finded" value="' . $cart_item_key . '" >Remove</button>	</i>							' . $product_name;
			return $product_name;
		} else {

			if (isset($plgfydc_all_data['woosppo_applied_onc']) && 'Products' == $plgfydc_all_data['woosppo_applied_onc']) {

				if (isset($plgfydc_all_data['applied_on_ids']) && 0 < count($plgfydc_all_data['applied_on_ids'])) {

					if (in_array($c_p_id, $plgfydc_all_data['applied_on_ids'])) {

						$product_name = ' <i style="color:' . $plgfydc_all_data['clrfrtrsh'] . ';cursor:pointer;" class="fa fa-' . $plgfydc_all_data['woospca_icons'] . ' remove-icon" aria-hidden="true"><button style="display:none !important;" class="finded" id="finded" value="' . $cart_item_key . '" >Remove</button>	</i>							' . $product_name;
						return $product_name;
					}
				}
			} else if (isset($plgfydc_all_data['woosppo_applied_onc']) && 'Category' == $plgfydc_all_data['woosppo_applied_onc']) {
				if (isset($plgfydc_all_data['applied_on_ids']) && 0 < count($plgfydc_all_data['applied_on_ids'])) {

					$cat_ids = wp_get_post_terms($c_p_id, 'product_cat', array('fields'=>'ids'));
					foreach ($cat_ids as $key1 => $value1) {							
						if (isset($plgfydc_all_data['applied_on_ids']) && in_array($value1, $plgfydc_all_data['applied_on_ids'])) {
							$product_name = ' <i style="color:' . $plgfydc_all_data['clrfrtrsh'] . ';cursor:pointer;" class="fa fa-' . $plgfydc_all_data['woospca_icons'] . ' remove-icon" aria-hidden="true"><button style="display:none !important;" class="finded" id="finded" value="' . $cart_item_key . '" >Remove</button>	</i>							' . $product_name;
							return $product_name;
						}
					}
				}
			}

		}



	}
	return $product_name;

}




function plgfyucoc_remove_quantity_count ( $product_name, $cart_item, $cart_item_key ) {

	if (is_checkout()) {

		$cart     = WC()->cart->get_cart();
		$plgfydc_all_data=get_option('save_gnrl_settings_plgfyrestrictions1');
		if ('' == $plgfydc_all_data) {
			$plgfydc_all_data=array(
				'enplable'=>'true',
				'plgfyqdp_gst'=>'true',
				'woospca_icons'=>'trash',
				'clrfrtrsh'=>'ff0000',
				'woosppo_applied_onc'=>'Products',
				'applied_on_ids'=>array(),

				'plgfyqdp_customer_role'=>array()

			);
		}
		if ('true' != $plgfydc_all_data['enplable']) {
			return '×' . $cart_item['quantity'];
		}
		$found_user_role=false;
		if ('0' != get_current_user_ID() || '' != get_current_user_ID()) {

			$user_meta=get_userdata(get_current_user_ID());
			$user_roles=$user_meta->roles;
			foreach ($user_roles as $key_1 => $value_1) {
				if (isset($plgfydc_all_data['plgfyqdp_customer_role']) && 0 < count($plgfydc_all_data['plgfyqdp_customer_role'])) {
					if (in_array($value_1, $plgfydc_all_data['plgfyqdp_customer_role'])) {
						$found_user_role=true;

					}
				} else {
					$found_user_role=true;

				}

			}
		} else {
			if ('true' == $plgfydc_all_data['plgfyqdp_gst']) {
				$found_user_role=true;
			}
		}
		if (!$found_user_role) {
			return '×' . $cart_item['quantity'];
		}

		$prodducy=$cart_item['data'];
		$c_p_id=$prodducy->get_id();
		if ('variation' == $prodducy->get_type()) { 
			$c_p_id = wp_get_post_parent_id( $c_p_id );	

		}
		if (isset($plgfydc_all_data['woosppo_applied_onc']) && 'whole'== $plgfydc_all_data['woosppo_applied_onc']) {
			?>
			<input type="number" value="<?php echo filter_var($cart_item['quantity']); ?>" cartitemattr="<?php echo filter_var($cart_item_key); ?>" class="pro_quantity" style="width: 50%;" >
			<?php
			return;
		} else {

			if (isset($plgfydc_all_data['woosppo_applied_onc']) && 'Products' == $plgfydc_all_data['woosppo_applied_onc']) {

				if (isset($plgfydc_all_data['applied_on_ids']) && 0 < count($plgfydc_all_data['applied_on_ids'])) {

					if (in_array($c_p_id, $plgfydc_all_data['applied_on_ids'])) {

						?>
						<input type="number" value="<?php echo filter_var($cart_item['quantity']); ?>" cartitemattr="<?php echo filter_var($cart_item_key); ?>" class="pro_quantity" style="width: 50%;" >
						<?php
						return;
					}
				}
			} else if (isset($plgfydc_all_data['woosppo_applied_onc']) && 'Category' == $plgfydc_all_data['woosppo_applied_onc']) {
				if (isset($plgfydc_all_data['applied_on_ids']) && 0 < count($plgfydc_all_data['applied_on_ids'])) {

					$cat_ids = wp_get_post_terms($c_p_id, 'product_cat', array('fields'=>'ids'));
					foreach ($cat_ids as $key1 => $value1) {							
						if (isset($plgfydc_all_data['applied_on_ids']) && in_array($value1, $plgfydc_all_data['applied_on_ids'])) {
							?>
							<input type="number" value="<?php echo filter_var($cart_item['quantity']); ?>" cartitemattr="<?php echo filter_var($cart_item_key); ?>" class="pro_quantity" style="width: 50%;" >
							<?php
							return;
						}
					}
				}
			}

		}



	}


	return '×' . $cart_item['quantity'];

}






function plugify_restri_scrip_disable1() {
	if (is_product()) {
		?>
		<style type="text/css">
			.hide_it_on_product_page{
				display: none !important;
			}
		</style>
		<?php



	}
	$actve_gtn=false;
	if ( in_array( 'woo-gutenberg-products-block/woocommerce-gutenberg-products-block.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$actve_gtn=true;
		$plgfydc_all_data=get_option('save_gnrl_settings_plgfyrestrictions1');
		if ('' == $plgfydc_all_data) {
			$plgfydc_all_data=array(
				'enplable'=>'true',
				'plgfyqdp_gst'=>'true',
				'woospca_icons'=>'trash',
				'clrfrtrsh'=>'ff0000',
				'woosppo_applied_onc'=>'Products',
				'applied_on_ids'=>array(),

				'plgfyqdp_customer_role'=>array()

			);
		}
		
		?>
		<script type="text/javascript">
			setTimeout(function(){
				jQuery('.wp-block-woocommerce-checkout-totals-block').children(':first').before('<button class="button alt plugify_load_it" style="width:100%;"> Update Cart </button>')
				jQuery('.hide_it_on_product_page').each(function(){
					var itemkey=jQuery(this).find('#finded').val();
					var prevqty=jQuery(this).parentsUntil('.wc-block-components-order-summary-item').parent().find('.wc-block-components-order-summary-item__quantity').find('span:first').html();
					var prev_html=jQuery(this).html();
					if (jQuery(this).find('#finded').length>0){
						jQuery(this).html( prev_html + '<input type="number" value="'+prevqty+'" cartitemattr="'+itemkey+'" class="pro_quantity" style="width: 60%;">');
					}
					


				});

			},2000);
		</script>
		<style type="text/css">
			.remove-icon{
				border: 1px solid <?php echo filter_var($plgfydc_all_data['clrfrtrsh']); ?>;
				padding: 12px 6px;
				border-radius: 5px;
				margin-right: 3px;
			}
		</style>
		<?php

	}
	wp_enqueue_style('date_picker_css_plgfyqdp', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', false, '1.0', 'all');
	wp_enqueue_script('jquery');
	$plugifybdp_shop_link=get_permalink( wc_get_page_id( 'shop' ) );
	?>
	<style type="text/css">
		.finded{
			display: none !important;
		}
	</style>
	<script type="text/javascript">

		
		
		
		jQuery('body').on('click', '.plugify_load_it' , function(){
			location.reload()
		});
		jQuery('body').on('click', '.remove-icon' , function(e){
			
			e.preventDefault();

			jQuery.ajax ({
				url: '<?php echo filter_var(admin_url('admin-ajax.php')); ?>',
				type:'POST',
				data:{
					action : 'remove_icon_cupplugify',
					crtttk: jQuery(this).find('#finded').val(),

				},
				success:function(response) {

					if (true == '<?php echo filter_var($actve_gtn); ?>') {
						location.reload();
					}
					if ('shop' ==response) {

						window.location.assign('<?php echo filter_var($plugifybdp_shop_link); ?>');
					}
					jQuery( 'body' ).trigger( 'wc_fragment_refresh' );
					jQuery( document ).trigger( 'wc_fragment_refresh' );
					jQuery(document.body).trigger("update_checkout");
					jQuery('body').trigger("update_checkout");



				}
			});

		});
		
		jQuery('body').on('change', '.pro_quantity' , function(e){
			e.preventDefault();
			var thiss=this;
			var valis=jQuery(this).val();
		
			if ('' == valis) {
				return;
			}
			jQuery.ajax ({
				url: '<?php echo filter_var(admin_url('admin-ajax.php')); ?>',
				type:'POST',
				data:{
					action : 'plugify_qty_change',

					crtttk: jQuery(this).attr('cartitemattr'),
					currentqty:valis,

				},
				success:function(response) {

					
					jQuery( 'body' ).trigger( 'wc_fragment_refresh' );
					jQuery(document.body  ).trigger( 'wc_fragment_refresh' );
					jQuery(document.body).trigger("update_checkout");
					jQuery('body').trigger("update_checkout");
					// jQuery(thiss).val(valis)
					

				}
			});
		});

	</script>
	<?php

	if (is_checkout()) {
		
		?>
	
		<style>
			.woocommerce input[type="number"] {
				-moz-appearance: unset!important;
			}
		
		</style>
		<?php
	}
}



function remove_icon_cupplugify() {
	if (isset($_REQUEST['crtttk'])) {
		$crtttk=sanitize_text_field($_REQUEST['crtttk']);
	}
	WC()->cart->remove_cart_item($crtttk);
	if (0 == WC()->cart->cart_contents_count) {
		echo filter_var('shop');
		wp_die();
	}
	wp_die();
}
function plugify_qty_change() {
	if (isset($_REQUEST['crtttk'])) {
		$crtttk=sanitize_text_field($_REQUEST['crtttk']);
	}
	if (isset($_REQUEST['currentqty'])) {
		$currentqty=sanitize_text_field($_REQUEST['currentqty']);
	}

	WC()->cart->set_quantity($crtttk, $currentqty);
	wp_die();
}

if ( in_array( 'woo-gutenberg-products-block/woocommerce-gutenberg-products-block.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_filter('woocommerce_add_cart_item', 'lasplugify');
	
}

function lasplugify ( $xart_item ) {


	$qty_html='';
	$delhtml='';
	$plgfydc_all_data=get_option('save_gnrl_settings_plgfyrestrictions1');
	if ('' == $plgfydc_all_data) {
		$plgfydc_all_data=array(
			'enplable'=>'true',
			'plgfyqdp_gst'=>'true',
			'woospca_icons'=>'trash',
			'clrfrtrsh'=>'ff0000',
			'woosppo_applied_onc'=>'Products',
			'applied_on_ids'=>array(),

			'plgfyqdp_customer_role'=>array()

		);
	}
	if ('true' != $plgfydc_all_data['enplable']) {
		return $xart_item;
	}
	$found_user_role=false;
	if ('0' != get_current_user_ID() || '' != get_current_user_ID()) {

		$user_meta=get_userdata(get_current_user_ID());
		$user_roles=$user_meta->roles;
		foreach ($user_roles as $key_1 => $value_1) {
			if (isset($plgfydc_all_data['plgfyqdp_customer_role']) && 0 < count($plgfydc_all_data['plgfyqdp_customer_role'])) {
				if (in_array($value_1, $plgfydc_all_data['plgfyqdp_customer_role'])) {
					$found_user_role=true;

				}
			} else {
				$found_user_role=true;

			}

		}
	} else {
		if ('true' == $plgfydc_all_data['plgfyqdp_gst']) {
			$found_user_role=true;
		}
	}
	if (!$found_user_role) {
		return $xart_item['quantity'];
	}

	$prodducy=$xart_item['data'];
	$c_p_id=$prodducy->get_id();
	if ('variation' == $prodducy->get_type()) { 
		$c_p_id = wp_get_post_parent_id( $c_p_id );	

	}
	if (isset($plgfydc_all_data['woosppo_applied_onc']) && 'whole'== $plgfydc_all_data['woosppo_applied_onc']) {
		$qty_html='<input type="number" value="' . $xart_item['quantity'] . '" cartitemattr="' . $xart_item['key'] . '" class="pro_quantity" style="width: 50%;" >';
		$delhtml='<i style="color:' . $plgfydc_all_data['clrfrtrsh'] . ';cursor:pointer;" class="fa fa-' . $plgfydc_all_data['woospca_icons'] . ' remove-icon" aria-hidden="true"><button style="display:none !important;" class="finded" id="finded" value="' . $xart_item['key'] . '" >Remove</button>	</i>';

	} else {

		if (isset($plgfydc_all_data['woosppo_applied_onc']) && 'Products' == $plgfydc_all_data['woosppo_applied_onc']) {

			if (isset($plgfydc_all_data['applied_on_ids']) && 0 < count($plgfydc_all_data['applied_on_ids'])) {

				if (in_array($c_p_id, $plgfydc_all_data['applied_on_ids'])) {

					$qty_html='<input type="number" value="' . $xart_item['quantity'] . '" cartitemattr="' . $xart_item['key'] . '" class="pro_quantity" style="width: 50%;" >';
					$delhtml='<i style="color:' . $plgfydc_all_data['clrfrtrsh'] . ';cursor:pointer;" class="fa fa-' . $plgfydc_all_data['woospca_icons'] . ' remove-icon" aria-hidden="true"><button style="display:none !important;" class="finded" id="finded" value="' . $xart_item['key'] . '" >Remove</button>	</i>';
				}
			}
		} else if (isset($plgfydc_all_data['woosppo_applied_onc']) && 'Category' == $plgfydc_all_data['woosppo_applied_onc']) {
			if (isset($plgfydc_all_data['applied_on_ids']) && 0 < count($plgfydc_all_data['applied_on_ids'])) {

				$cat_ids = wp_get_post_terms($c_p_id, 'product_cat', array('fields'=>'ids'));
				foreach ($cat_ids as $key1 => $value1) {							
					if (isset($plgfydc_all_data['applied_on_ids']) && in_array($value1, $plgfydc_all_data['applied_on_ids'])) {
						$qty_html='<input type="number" value="' . $xart_item['quantity'] . '" cartitemattr="' . $xart_item['key'] . '" class="pro_quantity" style="width: 50%;" >';
						$delhtml='<i style="color:' . $plgfydc_all_data['clrfrtrsh'] . ';cursor:pointer;" class="fa fa-' . $plgfydc_all_data['woospca_icons'] . ' remove-icon" aria-hidden="true"><button style="display:none !important;" class="finded" id="finded" value="' . $xart_item['key'] . '" >Remove</button>	</i>';
					}
				}
			}
		}

	}
	
	$product=$xart_item['data'];
	$original_desc=get_post_meta($xart_item['product_id'], '_original_post_meta_desc', true);
	if ('' == $original_desc) {
		$original_desc = $xart_item['data']->short_description; 
	}
	$new_desc = '<div class="hide_it_on_product_page">' . $qty_html . $delhtml . '</div>' . $original_desc;

	global $wpdb;
	
	$wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_excerpt= %s WHERE ID=%s ", stripcslashes($new_desc), $xart_item[product_id] ));
	update_post_meta($xart_item['product_id'], '_original_post_meta_desc', $original_desc);
	return $xart_item;
}
function deactivate_respasdf_sliderplugify() {

	global $wpdb;
	$page_W = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type= %s  AND post_status= %s", 'product', 'publish' ) );
	foreach ($page_W as $key => $value) {
		$iddis=$value->ID;
		$original_desc=get_post_meta($iddis, '_original_post_meta_desc', true);
		if ('' != $original_desc) {
			$wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_excerpt= %s WHERE ID=%s ", stripcslashes($original_desc), $iddis ));
			
		}


	}
}

register_deactivation_hook( __FILE__, 'deactivate_respasdf_sliderplugify' );
