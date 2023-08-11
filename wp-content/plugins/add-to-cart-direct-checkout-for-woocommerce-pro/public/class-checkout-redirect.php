<?php

class pisol_dcw_checkout_redirect{

    function __construct(){
        
        add_action( 'woocommerce_thankyou', array($this,'checkoutRedirect'));
    
    }

    function checkoutRedirect($order_id){
        $order = wc_get_order( $order_id );

        $product_specific_redirect_url = $this->getHighWeightRedirectLink($order);
        if($product_specific_redirect_url !== false){
            wp_redirect( $product_specific_redirect_url );
            exit;
        }

        if ( ! $order->has_status( 'failed' ) ) {
            $success_redirect = get_option('pi_dcw_enable_checkout_redirect',0);
            $success_url = $this->successFullOrderRedirectUrl();
            if(!empty($success_redirect) && !empty($success_url)){
                wp_redirect( $success_url );
                exit;
            }
        }
    }

    function getProductLinks($order){
        $order_items = $order->get_items();
        $product_links = array();
        foreach( $order_items as $product ) {
            $product_id = $product['product_id'];
            $variation_id = $product['variation_id'];
            $quantity = $product['quantity'];

            $product_redirect_url = get_post_meta($product_id, 'pi_dcw_product_checkout_redirect_url', true);

            $product_redirect_url_weight = get_post_meta($product_id, 'pi_dcw_product_checkout_redirect_url_weight', true);

            if(!empty($variation_id)){
                $handle_thankyou = get_post_meta($variation_id, 'pisol_dcw_handle_thankyou', true);

                if($handle_thankyou == 'yes'){
                    $product_redirect_url = get_post_meta($variation_id, 'pi_dcw_product_checkout_redirect_url', true);
                    $product_redirect_url_weight = get_post_meta($variation_id, 'pi_dcw_product_checkout_redirect_url_weight', true);
                }
            }

            if(!empty($product_redirect_url)){

                if(empty($product_redirect_url_weight)) $product_redirect_url_weight = 0;

                if(apply_filters('pisol_dcw_qty_for_weight', false)){
                    $product_redirect_url_weight = $product_redirect_url_weight * $quantity;
                }

                $product_links[$product_redirect_url_weight] = $product_redirect_url;
            }
        }
        return $product_links;
    }

    function getHighWeightRedirectLink($order){

        $product_links = $this->getProductLinks($order);

        if(empty($product_links)) return false;
        $highest_weight = 0;
        foreach($product_links as $weight => $url){
            if($weight > $highest_weight){
                $highest_weight = $weight;
            }
        }

        return isset($product_links[$highest_weight]) ? $product_links[$highest_weight] : false;
    }

    function successFullOrderRedirectUrl(){
       
        $redirect_page = get_option('pi_dcw_checkout_redirect_to_page','');
        if(!empty($redirect_page)){
            return get_permalink($redirect_page);
        }else{
            $custom_url = get_option('pi_dcw_custom_checkout_redirect_url','');
            return $custom_url;
        }
    }

    /**
     * use in future for the failed order 
     */
    function failedOrderRedirect(){
        $redirect_page = get_option('pi_dcw_checkout_failed_redirect_to_page','');
        if(!empty($redirect_page)){
            return get_permalink($redirect_page);
        }else{
            $custom_url = get_option('pi_dcw_custom_checkout_failed_redirect_url','');
            return $custom_url;
        }
    }
}

new pisol_dcw_checkout_redirect();