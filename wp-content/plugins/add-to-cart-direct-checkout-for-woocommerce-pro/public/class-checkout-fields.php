<?php
class Pisol_dcw_checkout_fields{
    function __construct(){
        add_filter('woocommerce_billing_fields', array($this,'remove_billing_fields'));
        add_filter('woocommerce_shipping_fields', array($this,'remove_shipping_fields'));
        add_filter('woocommerce_cart_item_name', array($this,'addLink'),100,3);
        $this->completelyRemoveShippingAddressOption();
    }

    function billingFieldsToRemove(){
        $fields_to_remove = get_option("pi_dcw_remove_billing_field",array());
        
        return $fields_to_remove;
    }

    function completelyRemoveShippingAddressOption(){
        $remove = get_option("pi_dcw_remove_shipping_option",0);
        if($remove == 1){
            add_filter( 'woocommerce_cart_needs_shipping_address',  '__return_false' );
        }
    }

    function shippingFieldsToRemove(){
        $fields_to_remove = get_option("pi_dcw_remove_shipping_field",array());
        
        return $fields_to_remove;
    }

    function remove_billing_fields($fields){
        //print_r($fields);
        $fields_to_remove = $this->billingFieldsToRemove();
        if(is_array($fields_to_remove)){
        foreach($fields_to_remove as $field){
            if(isset($fields[$field])){
                unset($fields[$field]);
            }
        }
        }
        return $fields;
    }

    function remove_shipping_fields($fields){
        //print_r($fields);
        $fields_to_remove = $this->shippingFieldsToRemove();
        if(is_array($fields_to_remove)){
        foreach($fields_to_remove as $field){
            if(isset($fields[$field])){
                unset($fields[$field]);
            }
        }
        }
        return $fields;
    }

    function addLink($product_name, $cart_item, $cart_item_key){
        if(!is_checkout() || get_option('pi_dcw_add_link_to_checkout_product_name',0) != 1){
            return $product_name;
        }
        if($cart_item['variation_id'] == 0){
            $product = wc_get_product($cart_item['product_id']);
        }else{
            $product = wc_get_product($cart_item['variation_id']);
        }
        $url = $product->get_permalink();

        $target = '';
        $new_tab = get_option('pi_dcw_checkout_link_new_tab', 1);
        if(!empty($new_tab)){
            $target = ' target="_blank" ';
        }

        return '<a href="'.$url.'" '.$target.'>'.$product_name.'</a>';
    }
}

new Pisol_dcw_checkout_fields();