<?php

class Class_Pi_Dcw_Quick_View{

    public $plugin_name;

    private $setting = array();

    private $active_tab;

    private $this_tab = 'quickview';

    private $tab_name = "Quick View";

    private $setting_key = 'dcw_quick_view_setting';

    private $pages =array();
    
    private $pro_version = false;

    private $open_animation = array("None", "bounceIn",
    "bounceInDown",
    "bounceInLeft",
    "bounceInRight",
    "bounceInUp",
    "fadeIn",
    "fadeInDown",
    "fadeInDownBig",
    "fadeInLeft",
    "fadeInLeftBig",
    "fadeInRight",
    "fadeInRightBig",
    "fadeInUp",
    "fadeInUpBig",
    "flipInX",
    "flipInY",
    "lightSpeedIn",
    "rotateIn",
    "rotateInDownLeft",
    "rotateInDownRight",
    "rotateInUpLeft",
    "rotateInUpRight",
    "slideInUp",
    "slideInDown",
    "slideInLeft",
    "slideInRight",
    "zoomIn",
    "zoomInDown",
    "zoomInLeft",
    "zoomInRight",
    "zoomInUp",
    "rollIn");

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;
        $this->open_animation = $this->creatingArray($this->open_animation);
        $this->active_tab = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'default';

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }


        add_action($this->plugin_name.'_tab', array($this,'tab'),1);

        $this->settings = array(
            array('field'=>'quickview', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('Basic Setting Quick View','pi-dcw'), 'type'=>'setting_category'),
            array('field'=>'pi_dcw_enable_quick_view_button','desc'=>'This will show a quick view button on the product archive page, or category page', 'label'=>__('Enable Quick View button','pi-dcw'),'type'=>'switch', 'default'=>0),
            array('field'=>'pi_dcw_quick_view_text','desc'=>__('Quick view button text'), 'label'=>__('Text shown inside the quick view button'),'type'=>'text','default'=>'Quick View'),

            array('field'=>'quickview', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('Quick View box size','pi-dcw'), 'type'=>'setting_category'),
            array('field'=>'pi_dcw_quick_view_box_width','desc'=>__('Popup box size'), 'label'=>__('Qucikview popup box size'),'type'=>'select','value'=> array('50'=>'50%', '55'=>'55%', '60'=>'60%', '65'=>'65%', '70'=>'70%', '75'=>'75%', '80'=>'80%', '85'=>'85%', '90'=>'90%', '100'=>'100%'), 'default'=>'70'),
            array('field'=>'pi_dcw_quick_view_box_image_width','desc'=>__('Product image width in the popup box '), 'label'=>__('Product image width'),'type'=>'select','value'=> array('0'=>'0%', '20'=>'20%', '25'=>'25%', '30'=>'30%', '35'=>'35%', '40'=>'45%', '50'=>'50%', '55'=>'55%', '60'=>'65%'), 'default'=>'30'),

            array('field'=>'quickview', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('Button Design','pi-dcw'), 'type'=>'setting_category'),
            array('field'=>'pi_dcw_quick_view_bg_color','desc'=>__('Background color of Quick View button'), 'label'=>__('Background color'),'type'=>'color', 'default'=>'#ee6443'),
            array('field'=>'pi_dcw_quick_view_text_color','desc'=>__('Text color of Quick View button'), 'label'=>__('Text color'),'type'=>'color', 'default'=>'#ffffff'),

            array('field'=>'quickview', 'class'=> 'bg-secondary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('Quick view popup box design','pi-dcw'), 'type'=>'setting_category'),
            array('field'=>'pi_dcw_quick_view_modal_bg_color','desc'=>__('Background color of Quick View popup box'), 'label'=>__('Background color'),'type'=>'color', 'default'=>'#FFFFFF'),
            array('field'=>'pi_dcw_quick_view_modal_text_color','desc'=>__('Text color of Quick View box content, this affect the color of product title and paragraph content'), 'label'=>__('Text color'),'type'=>'color', 'default'=>'#000000'),
            array('field'=>'pi_dcw_quick_view_modal_close_bg_color','desc'=>__('Close popup background color'), 'label'=>__('Close button background color'),'type'=>'color', 'default'=>'#000000'),
            array('field'=>'pi_dcw_quick_view_modal_close_color','desc'=>__('Close popup icon color'), 'label'=>__('Close button icon color'),'type'=>'color', 'default'=>'#ffffff'),
            array('field'=>'pi_dcw_quick_view_light_box','desc'=>'Enable light box for the product image in quick view', 'label'=>__('Enable light box','pi-dcw'),'type'=>'switch', 'default'=>0),

            array('field'=>'pi_dcw_quick_view_modal_padding','desc'=>__('Popup box padding'), 'label'=>__('Popup box padding in terms of (px)'),'type'=>'number','min'=>0, 'default'=>10),

            array('field'=>'pi_dcw_quick_view_modal_open_animation','desc'=>__('Popup box Open animation'), 'label'=>__('Animation when opening popup'),'type'=>'select','value'=> $this->open_animation, 'default'=>'fadeInUp'),
           
        );
        $this->register_settings();

        if(PISOL_DCW_DELETE_SETTING){
            $this->delete_settings();
        }
    }

   
    

    function register_settings(){   

        foreach($this->settings as $setting){
            register_setting( $this->setting_key, $setting['field']);
        }
    
    }

    function creatingArray($arrays){
        $return = array();
        foreach($arrays as $array){
            $return[$array] = $array;
        }
        return $return;
    }


    function delete_settings(){
        foreach($this->settings as $setting){
            delete_option( $setting['field'] );
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

