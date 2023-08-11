<?php

class pisol_dcw_speed{

    private $setting = array();

    private $active_tab;

    private $this_tab = 'speed';

    private $tab_name = "Speed setting";

    private $setting_key = 'pisol_restaurant_speed_setting';

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;


        $this->active_tab = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'default';
        
        $this->settings = array(
           

            array('field'=>'pi_dcw_enable_caching', 'label'=>__('Enable caching','pi-dcw'),'type'=>'switch', 'default'=>'0','desc'=>__('This will cache the buy no button on archive page for variable product','pi-dcw')),

            array('field'=>'pisol_dcw_cache_expiry', 'label'=>__('Cache expiry in minutes','pi-dcw'),'type'=>'number', 'default'=>30,'desc'=>__('How long the cached will be used, ','pi-dcw'), 'min'=>10, 'step'=>1),
            

        );
        

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'),10);
        }

        add_action($this->plugin_name.'_tab', array($this,'tab'),30);
        
        if(isset($_GET['delete_cache']) && isset($_GET['page']) && $_GET['page'] == 'pi-dcw'){
            $this->clearCache();
        }

        $this->register_settings();

        //$this->delete_settings();
        
    }

    function clearCache(){
        global $wpdb;
        $sql = 'DELETE FROM '.$wpdb->prefix.'options WHERE option_name LIKE ("%_transient_pisol_dcw_cache_%");';
        $wpdb->query($sql);
    }

    function delete_settings(){
        foreach($this->settings as $setting){
            delete_option( $setting['field'] );
        }
    }

    function register_settings(){   

        foreach($this->settings as $setting){
                register_setting( $this->setting_key, $setting['field']);
        }
    
    }
   

    function tab(){
        $caching_enabled = get_option('pi_dcw_enable_caching', 0);
        ?>
        <a class="fon-weight-bold px-3 text-light d-flex align-items-center border-left border-right <?php echo $this->active_tab == $this->this_tab || '' ? 'bg-primary' : 'bg-secondary'; ?>" href="<?php echo admin_url( 'admin.php?page='.$_GET['page'].'&tab='.$this->this_tab ); ?>">
            <?php _e( $this->tab_name, 'pi-dcw' ); ?> 
        </a>
        
        <a class="fon-weight-bold px-3 text-light d-flex align-items-center border-left border-right bg-primary" href="<?php echo admin_url( 'admin.php?page='.$_GET['page'].'&tab='.$this->this_tab.'&delete_cache=true' ); ?>">
            <?php _e( 'Delete cache', 'pi-dcw' ); ?> 
        </a>
        
        <?php
    }

    function tab_content(){
        ?>
        <div class="alert alert-info my-3">
       <strong>Make sure to Delete cache after making changes in the <u>Product</u>, or in this <u>Plugin settings</u>, even if you don't clear cache it will start showing updated content after the cache expiry time</strong>
        </div>
        <form method="post" action="options.php"  class="pisol-setting-form">
        <?php settings_fields( $this->setting_key ); ?>
        <?php
           $count = count($this->settings);
            for($i = 0; $i < $count; $i++){
                new pisol_class_form_dcw($this->settings[$i], $this->setting_key);
            }
            
        ?>
        <input type="submit" name="submit" id="submit" class="btn btn-primary btn-md my-3" value="<?php echo __('Save Changes','pi-dcw'); ?>">
        </form>
        <?php
    }

}



