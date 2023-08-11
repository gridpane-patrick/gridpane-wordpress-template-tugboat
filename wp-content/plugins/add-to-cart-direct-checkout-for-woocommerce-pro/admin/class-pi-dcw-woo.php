<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       piwebsolution.com
 * @since      1.0.0
 *
 * @package    Pi_Edd
 * @subpackage Pi_Edd/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pi_Edd
 * @subpackage Pi_Edd/admin
 * @author     PI Websolution <rajeshsingh520@gmail.com>
 */
class Pi_Dcw_Pro_Woo {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( ) {
		/** Adding order preparation days */
		add_action( 'woocommerce_product_data_tabs', array($this,'productTab') );
		
		add_action( 'woocommerce_product_data_panels', array($this,'order_preparation_days') );

		add_action( 'woocommerce_process_product_meta', array($this,'order_preparation_days_save') );
	}

	function productTab($tabs){
        $tabs['pisol_mmq'] = array(
            'label'    => 'Add To Cart Setting',
            'target'   => 'pisol_add_to_cart',
			'priority' => 21,
			'class'=>'hide_if_external'
        );
        return $tabs;
	}

	function order_preparation_days() {

			$args1 = array(
			'id' => 'pi_dcw_product_redirect',
			'label' => __( 'Exclude this product from any redirect', 'pi-dcw' ),
			);
			$args2 = array(
				'id' => 'pi_dcw_product_overwrite_global',
				'label' => __( 'Overwrite global redirect setting', 'pi-dcw' ),
			);
			$args3 = array(
				'id' => 'pi_dcw_product_buy_now_disable',
				'label' => __( 'Disable buy now button', 'pi-dcw' ),
			);
			
			echo '<div id="pisol_add_to_cart" class="pi-container panel woocommerce_options_panel hidden free-version">';
			echo '<div class="option-group disable-redirect">';
			woocommerce_wp_checkbox( $args1 );
			echo '</div>';
			echo '<div id="pisol-enabled-redirect" class="option-group local-redirect-setting">';
			woocommerce_wp_checkbox( $args2 );
			echo '<div id="pisol-set-url" class="option-group local-redirect-setting">';
			woocommerce_wp_text_input( 
				array( 
					'id'      => 'pi_dcw_product_redirect_to_page', 
					'label'   => __( 'Redirect to page', 'pi-dcw' ), 
					'type' => 'url'
					)
				);
			echo '</div>';
			echo '</div>';
			echo '<div class="option-group">';
			woocommerce_wp_checkbox( $args3 );
			echo '</div>';
			echo '<hr>';
			echo '<h3 style="margin-left:15px;">Change button text</h3>';
			woocommerce_wp_text_input( 
				array( 
					'id'      => 'pi_dcw_add_to_cart_text', 
					'label'   => __( 'Add to cart button text', 'pi-dcw' ), 
					'type' => 'text',
					'description'=>'This text will be shown inside add to cart button, Leave blank if you don\'t want to change it'
					)
				);
			echo '<div class="show_if_variable">';
			woocommerce_wp_text_input( 
				array( 
						'id'      => 'pi_dcw_select_option_text',
						
						'label'   => __( 'Select options button text', 'pi-dcw' ), 
						'type' => 'text',
						'description'=>'This text will be shown on the archive page for the variable product, Leave blank if you don\'t want to change it'
						)
				);
			echo '</div>';
			woocommerce_wp_text_input( 
					array( 
							'id'      => 'pi_dcw_read_more_text', 
							'label'   => __( 'Read more button text', 'pi-dcw' ), 
							'type' => 'text',
							'description'=>'This text will be shown on archive page for the product when the product is out of stock, Leave blank if you don\'t want to change it'
							)
					);
			echo '<hr>';
			echo '<h3 style="margin-left:15px;">Custom thank you page on order placement</h3>';
					woocommerce_wp_text_input( 
						array( 
							'id'      => 'pi_dcw_product_checkout_redirect_url', 
							'label'   => __( 'Custom Thank you page url', 'pi-dcw' ), 
							'type' => 'url',
							'placeholder'=>'https://google.com/abc/xyz',
							'desc_tip'    => 'true',
							'description'=>'Customer will be redirected to this page on successful order placement'
							)
						);
					woocommerce_wp_text_input( 
							array( 
								'id'      => 'pi_dcw_product_checkout_redirect_url_weight', 
								'label'   => __( 'Weight of this Thank you page url  ', 'pi-dcw' ), 
								'type' => 'number',
								'desc_tip'    => 'true',
								'custom_attributes' => array(
									'step' 	=> '1',
									'min'	=> '0'
								) ,
								'description'=>'If customer order 2 product with different thank you page url, then the product with highest weight will take priority and user will be redirected to that thank you page'
								)
							);
			echo '<hr>';
			echo '<h3 style="margin-left:15px;">Custom buy now redirect url</h3>';
				woocommerce_wp_text_input( 
					array( 
						'id'      => 'pi_dcw_product_buynow_redirect_url', 
						'label'   => __( 'Custom Buy now url', 'pi-dcw' ), 
						'type' => 'url',
						'placeholder'=>'https://google.com/abc/xyz',
						'desc_tip'    => 'true',
						'description'=>'Customer redirect url for buy now button'
						)
					);
			echo '</div>';
			

	   }

	   function order_preparation_days_save( $post_id ) {
			$product = wc_get_product( $post_id );

			$product_redirect = ((isset( $_POST['pi_dcw_product_redirect'] )) ? $_POST['pi_dcw_product_redirect'] : 0);
			$product->update_meta_data( 'pi_dcw_product_redirect', sanitize_text_field( $product_redirect ) );
			
			$overwrite_global = ((isset( $_POST['pi_dcw_product_overwrite_global'] )) ? $_POST['pi_dcw_product_overwrite_global'] : 0);
			$product->update_meta_data( 'pi_dcw_product_overwrite_global', sanitize_text_field( $overwrite_global ) );
			
			$page = ((isset( $_POST['pi_dcw_product_redirect_to_page'] )) ? $_POST['pi_dcw_product_redirect_to_page'] : 0);
			$product->update_meta_data( 'pi_dcw_product_redirect_to_page', sanitize_text_field( $page ) );

			$disable_buy_now = ((isset( $_POST['pi_dcw_product_buy_now_disable'] )) ? $_POST['pi_dcw_product_buy_now_disable'] : 0);
			$product->update_meta_data( 'pi_dcw_product_buy_now_disable', sanitize_text_field( $disable_buy_now ) );


			$add_to_cart = ((isset( $_POST['pi_dcw_add_to_cart_text'] )) ? $_POST['pi_dcw_add_to_cart_text'] : "");
			$product->update_meta_data( 'pi_dcw_add_to_cart_text', sanitize_text_field( $add_to_cart ) );

			$select_options = ((isset( $_POST['pi_dcw_select_option_text'] )) ? $_POST['pi_dcw_select_option_text'] : "");
			$product->update_meta_data( 'pi_dcw_select_option_text', sanitize_text_field( $select_options ) );

			$read_more = ((isset( $_POST['pi_dcw_read_more_text'] )) ? $_POST['pi_dcw_read_more_text'] : "");
			$product->update_meta_data( 'pi_dcw_read_more_text', sanitize_text_field( $read_more ) );

			$thank_you_page = ((isset( $_POST['pi_dcw_product_checkout_redirect_url'] )) ? $_POST['pi_dcw_product_checkout_redirect_url'] : "");
			$product->update_meta_data( 'pi_dcw_product_checkout_redirect_url',  $thank_you_page  );

			$thank_you_page_weight = ((isset( $_POST['pi_dcw_product_checkout_redirect_url_weight'] )) ? $_POST['pi_dcw_product_checkout_redirect_url_weight'] : "");
			$product->update_meta_data( 'pi_dcw_product_checkout_redirect_url_weight',  $thank_you_page_weight  );

			$buy_now_url = ((isset( $_POST['pi_dcw_product_buynow_redirect_url'] )) ? $_POST['pi_dcw_product_buynow_redirect_url'] : "");
			$product->update_meta_data( 'pi_dcw_product_buynow_redirect_url',  $buy_now_url  );
			
			$product->save();
	   }
	
	   function get_pages(){
        $pages = get_pages( );
        $pages_array = array(""=>__("Select page","pi-dcw"));
        if($pages){
            foreach ( $pages as $page ) {
                $pages_array[$page->ID] = $page->post_title;
            }
        }
        return $pages_array;
    	}
}

new Pi_Dcw_Pro_Woo();