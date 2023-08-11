<?php

class Class_Pi_Dcw_Buy_Now{

    public $plugin_name;

    private $setting = array();

    private $active_tab;

    private $this_tab = 'buy-now';

    private $tab_name = "Buy Now Button";

    private $setting_key = 'dcw_buy_now_setting';

    private $pages =array();
    
    private $pro_version = false;

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;
        
        $this->active_tab = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'default';

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }


        add_action($this->plugin_name.'_tab', array($this,'tab'),1);

        $this->settings = array(
            array('field'=>'sunday', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('On product page','pi-dcw'), 'type'=>'setting_category'),
            array('field'=>'pi_dcw_enable_buy_now_button','desc'=>'Buy now button on single product page', 'label'=>__('Buy now button on product page','pi-dcw'),'type'=>'switch', 'default'=>0),
            array('field'=>'pi_dcw_buy_now_button_text','desc'=>'Buy now button label', 'label'=>__('Label of the buy now button'),'type'=>'text', 'default'=>'Buy Now'),
            array('field'=>'pi_dcw_buy_now_button_position','desc'=>'Position of the button', 'label'=>__('Position of the button'),'type'=>'select', 'default'=>'after_button', 'value'=>array( 'after_button'=>'After add to cart button', 'before_button'=>'Before add to cart button')),
            array('field'=>'pi_dcw_buy_now_button_redirect','desc'=>'Redirect to cart or checkout page', 'label'=>__('Redirect to cart/checkout page'),'type'=>'select', 'default'=>'checkout', 'value'=>array('checkout'=>'Checkout', 'cart'=>'Cart')),

            array('field'=>'pisol_dcw_button_size','desc'=>'Buy now button size on product page (PX)', 'label'=>__('Buy now button size on product page'),'type'=>'number', 'default'=>'', 'min'=>200, 'placeholder'=>'px'),



            array('field'=>'sunday', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('On product archive page','pi-dcw'), 'type'=>'setting_category'),
            array('field'=>'pi_dcw_enable_buy_now_button_loop','desc'=>'Buy now button on Product archive page like loop , category', 'label'=>__('Buy now button on product archive page','pi-dcw'),'type'=>'switch', 'default'=>0),
            array('field'=>'pi_dcw_buy_now_button__loop_text','desc'=>'Buy now button label', 'label'=>__('Label of the buy now button'),'type'=>'text', 'default'=>'Buy Now'),
            array('field'=>'pi_dcw_buy_now_button_loop_position','desc'=>'Position of the button', 'label'=>__('Position of the button'),'type'=>'select', 'default'=>'after_button', 'value'=>array('after_button'=>'After add to cart button', 'before_button'=>'Before add to cart button', 'before_image'=>'Before product image')),

            array('field'=>'pi_dcw_buy_now_button_loop_redirect','desc'=>'Redirect to cart or checkout page', 'label'=>__('Redirect to cart/checkout page'),'type'=>'select', 'default'=>'checkout', 'value'=>array('checkout'=>'Checkout', 'cart'=>'Cart', 'dont'=>'No redirect')),
            array('field'=>'pi_dcw_enable_buy_now_for_variable_product_on_loop','desc'=>'this will show the buy now button for variable product, and this buy now button will add the first variation of that product to cart <strong class="text-primary">You must have set Default value for all the required variation attributes, else the buy now button for that product may not work</strong>', 'label'=>__('Show buy now option on variable product, on category / Shop page','pi-dcw'),'type'=>'switch', 'default'=>0),
            
            array('field'=>'sunday', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('Buy now Button behavior'), 'type'=>'setting_category'),

            array('field'=>'pisol_dcw_remove_other_product','desc'=>'When product is added by Buy now button other product from the cart will be removed and only Buy now product will remain in the cart', 'label'=>__('Remove other product from cart','pi-dcw'),'type'=>'switch', 'default'=>0),

            array('field'=>'pisol_dcw_remove_event_same_product','desc'=>'Once enabled it will even remove the same product from then cart that was added earlier', 'label'=>__('Remove even same product that was previously added in cart','pi-dcw'),'type'=>'switch', 'default'=>1),

            array('field'=>'sunday', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('Buy now Button design'), 'type'=>'setting_category'),
            array('field'=>'pi_dcw_buy_now_bg_color','desc'=>__('Background color of Buy now button'), 'label'=>__('Background color'),'type'=>'color', 'default'=>'#ee6443'),
            array('field'=>'pi_dcw_buy_now_text_color','desc'=>__('Text color of Buy now button'), 'label'=>__('Text color'),'type'=>'color', 'default'=>'#ffffff'),

            array('field'=>'pi_dcw_enable_buynow_loading_animation','desc'=>'Loading animation is shown when buy now button is clicked', 'label'=>__('Loading animation on Buy now button click','pi-dcw'),'type'=>'switch', 'default'=>0),
            
        );
        $this->register_settings();
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
            foreach($this->settings as $setting){
                new pisol_class_form_dcw($setting, $this->setting_key);
            }
        ?>
        <input type="submit" class="mt-3 btn btn-primary btn-sm" value="Save Option" />
        </form>
       <?php
    }

   
}

