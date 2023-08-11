<?php

class pisol_dcw_product_level_button_text{

    function __construct(){
        add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'add_product_single'), 100, 2);
        /**
         * change add to cart text in product loop
         */
        add_filter('woocommerce_product_add_to_cart_text', array($this, 'add_product_loop'), 100, 2);
    }

    function add_product_single($text, $product){
        return $this->add_product_text($text, $product, 'single');
    }

    function add_product_loop($text, $product){
        return $this->add_product_text($text, $product, 'loop');
    }

    function add_product_text($text, $product, $caller='single') {
        $product_id = $product->get_id();

        if($product->is_type('external')) return $text;
        

            if( $caller == 'loop') {
                if($product->is_type('variable')){
                    $saved = esc_html(get_post_meta($product_id,  'pi_dcw_select_option_text', true));
                    if($saved != ""){
                        $text = $saved;
                    }
                }else{
                    if($product->is_in_stock()){
                        $saved = esc_html(get_post_meta($product_id,'pi_dcw_add_to_cart_text', true));
                        if($saved != ""){
                            $text = $saved;
                        }
                    }else{
                        $saved = esc_html(get_post_meta($product_id,'pi_dcw_read_more_text', true));
                        if($saved != ""){
                            $text = $saved;
                        }
                    }
                }
            }else{
                $saved = esc_html(get_post_meta($product_id, 'pi_dcw_add_to_cart_text', true));
                if($saved != ""){
                    $text = $saved;
                }
            }
  
        return $text;
      }
}

new pisol_dcw_product_level_button_text();