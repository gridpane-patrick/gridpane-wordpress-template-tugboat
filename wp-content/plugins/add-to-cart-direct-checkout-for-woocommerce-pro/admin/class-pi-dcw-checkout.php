<?php

class Class_Pi_Dcw_Checkout{

    public $plugin_name;

    private $setting = array();

    private $active_tab;

    private $this_tab = 'checkout';

    private $tab_name = "Checkout setting";

    private $setting_key = 'checkout_setting';

    private $pages =array();

   
    
    private $pro_version = false;

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;


        $this->pages = $this->get_pages();
        
        $this->active_tab = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'default';

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }

        $billing_fields = array(
            'billing_first_name'=>'Billing First Name',
            'billing_last_name'=>'Billing Last Name',
            'billing_address_1'=>"Billing Address 1",
            'billing_address_2'=>"Billing Address 2",
            'billing_country'=>"Billing Country",
            'billing_city'=>"Billing City",
            'billing_state'=>"Billing State",
            'billing_postcode'=>"Billing Postcode",
            'billing_company'=>"Billing Company",
            'billing_phone'=>"Billing Phone",
            'billing_email'=>'Billing Email'
        );

        $shipping_fields = array(
            'shipping_first_name'=>'Shipping First Name',
            'shipping_last_name'=>'Shipping Last Name',
            'shipping_address_1'=>"Shipping Address 1",
            'shipping_address_2'=>"Shipping Address 2",
            'shipping_country'=>"Shipping Country",
            'shipping_city'=>"Shipping City",
            'shipping_state'=>"Shipping State",
            'shipping_postcode'=>"Shipping Postcode",
            'shipping_company'=>"Shipping Company"
        );

        add_action($this->plugin_name.'_tab', array($this,'tab'),6);

        $single_page_checkout_enabled = get_option('pi_dcw_single_page_checkout',1);

        if(empty($single_page_checkout_enabled)){
            $warning = 'd-none';
            $disable = '';
        }else{
            $warning = '';
            $disable = 'pi-disable-field';
            update_option('pi_dcw_remove_product_from_checkout_page',0);
            update_option('pi_dcw_change_quantity_from_checkout_page',0);
        }

        
        $url = admin_url( 'admin.php?page=pi-dcw&tab=default#row_pi_dcw_single_page_checkout' );

        $this->settings = array(
            array('field'=>'option_to_remove_qty_on_checkout', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('Change and Remove Quantity on checkout page <div class="pi-checkout-qty-edit-conflict-warning '.$warning.'">This options can\'t be used along with "<a href="'.$url.'" target="_blank">Enable single page checkout</a>" so disable the "Enable single page checkout" option in Basic Setting tab first</div>','pi-dcw'), 'type'=>'setting_category'),

            array('field'=>'pi_dcw_remove_product_from_checkout_page','desc'=>__('It will show remove button before each product in the checkout page so customer can remove it without leaving checkout page','pi-dcw'), 'label'=>__('Remove product option on checkout page','pi-dcw'),'type'=>'switch','default'=>0, 'class'=>$disable),

            array('field'=>'pi_dcw_change_quantity_from_checkout_page','desc'=>__('It will allow customer to change the quantity of product in the checkout page itself','pi-dcw'), 'label'=>__('Change quantity of product','pi-dcw'),'type'=>'switch','default'=>0, 'class'=>$disable),


            array('field'=>'sunday', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('Checkout field setting','pi-dcw'), 'type'=>'setting_category'),

            array('field'=>'pi_dcw_remove_order_comment','desc'=>'Remove order comment from the checkout field', 'label'=>__('Remove order comment','pi-dcw'),'type'=>'switch','default'=>0),

            array('field'=>'pi_dcw_remove_have_coupon','desc'=>'Remove Have a Coupon field from the checkout page', 'label'=>__('Remove coupon field from checkout page','pi-dcw'),'type'=>'switch','default'=>0),

            array('field'=>'pi_dcw_remove_shipping_option','desc'=>'Remove "Ship to a different address?" option', 'label'=>__('Remove all the option of shipping field "Ship to a different address?"','pi-dcw'),'type'=>'switch','default'=>0),

            array('field'=>'pi_dcw_add_link_to_checkout_product_name','desc'=>'Link product name in checkout page, to product page', 'label'=>__('Link product name in checkout page','pi-dcw'),'type'=>'switch','default'=>0),

            array('field'=>'pi_dcw_checkout_link_new_tab','desc'=>'Open product link in checkout page in a new tab', 'label'=>__('Open link in new tab','pi-dcw'),'type'=>'switch','default'=>1),

            array('field'=>'pi_dcw_remove_billing_field','desc'=>'Select fields you want to remove from checkout form billing field, Press Ctrl and click to select more then one field', 'label'=>__('Remove billing fields','pi-dcw'),'type'=>'multiselect','default'=>"", 'value'=>$billing_fields),

            array('field'=>'pi_dcw_remove_shipping_field','desc'=>'Select fields you want to remove from checkout form billing field, Press Ctrl and click to select more then one field', 'label'=>__('Remove shipping fields','pi-dcw'),'type'=>'multiselect','default'=>"", 'value'=>$shipping_fields),
            
            array('field'=>'sunday', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('Checkout redirect setting / Custom thankyou page','pi-dcw'), 'type'=>'setting_category'),

            array('field'=>'pi_dcw_enable_checkout_redirect','desc'=>'', 'label'=>__('Set custom thankyou page','pi-dcw'),'type'=>'switch','default'=>0),

            array('field'=>'pi_dcw_checkout_redirect_to_page','desc'=>'User will be redirected to this page after returning from the payment gateway, set this to "set custom url" and then you can even set custom url', 'label'=>__('Set this page as thank you page','pi-dcw'),'type'=>'select', 'value'=>$this->pages),

            array('field'=>'pi_dcw_custom_checkout_redirect_url','desc'=>'Redirect to this url on checkout e.g.: http://yourwebsite.com', 'label'=>__('Use this custom url as thankyou page','pi-dcw'),'type'=>'text'),

        );
        $this->register_settings();
        
        
    }

    function get_pages(){
        $pages = get_pages( );
        $pages_array = array(""=>__("Set Custom Url","pi-dcw"));
        if($pages){
            foreach ( $pages as $page ) {
                $pages_array[$page->ID] = $page->post_title;
            }
        }
        return $pages_array;
    }

    

    function register_settings(){   

        foreach($this->settings as $setting){
            register_setting( $this->setting_key, $setting['field']);
        }
    
    }

    function tab(){
        ?>
        <a class=" px-3 text-light d-flex align-items-center  border-left border-right  <?php echo ($this->active_tab == $this->this_tab ? 'bg-primary' : 'bg-secondary'); ?>" href="<?php echo admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&tab='.$this->this_tab ); ?>">
            <?php _e( $this->tab_name, 'http2-push-content' ); ?> 
        </a>
        <?php
    }

    function tab_content(){
       ?>
        <form method="post" action="options.php"  class="pisol-setting-form">
        <?php settings_fields( $this->setting_key ); ?>
        <?php
            for($i = 0; $i <= 12; $i++){
                new pisol_class_form_dcw($this->settings[$i], $this->setting_key);
            }

            echo '<div id="pisol-dcw-checkout-redirect-setting">';
            new pisol_class_form_dcw($this->settings[13], $this->setting_key);
            new pisol_class_form_dcw($this->settings[14], $this->setting_key);
            echo '</div>';
        ?>
        <input type="submit" class="mt-3 btn btn-primary btn-sm" value="Save Option" />
        </form>
       <?php
    }

    
}

