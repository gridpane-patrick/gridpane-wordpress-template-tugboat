<?php

class pisol_dcw_variation_options{
    function __construct(){
        add_action( 'woocommerce_variation_options_pricing', [$this,'addFields'], 10, 3 );

        add_action( 'woocommerce_save_product_variation', [$this, 'saveVariation'] , 10, 2 );
    }

    function addFields( $loop, $variation_data, $variation  ){
        $handle_redirect = get_post_meta($variation->ID, 'pisol_dcw_handle_redirect', true);
        $handle_thankyou = get_post_meta($variation->ID, 'pisol_dcw_handle_thankyou', true);
        

        $url = get_post_meta($variation->ID, 'pi_dcw_product_redirect_to_page', true);
        $thankyou_url = get_post_meta($variation->ID, 'pi_dcw_product_checkout_redirect_url', true);
        $thankyou_weight = get_post_meta($variation->ID, 'pi_dcw_product_checkout_redirect_url_weight', true);

        echo '<div class="pisol_dcw_redirect_options" style="border-top:2px solid #F00; float:left; width:100%; margin-top:20px; margin-bottom:5px;">';
        woocommerce_wp_checkbox( array(
            'id'      => 'pisol_dcw_handle_redirect[' . $loop . ']',
            'class' => 'pisol_dcw_handle_redirect_variation',
            'label'   => __( 'Manage redirect for this variation '),
            'value'   => $handle_redirect,
            'custom_attributes' => array('data-loop_id' => $loop)
            ) );
        woocommerce_wp_text_input( array( 
                'id'      => 'pi_dcw_product_redirect_to_page[' . $loop . ']', 
                'label'   => __( 'Redirect to page on add to cart', 'pi-dcw' ), 
                'type' => 'url',
                'value' => $url
            ) );
        echo '</div>';
        echo '<div class="pisol_dcw_redirect_options" style=" border-bottom:2px solid #F00; float:left; width:100%; margin-top:5px; margin-bottom:20px;">';
        woocommerce_wp_checkbox( array(
            'id'      => 'pisol_dcw_handle_thankyou[' . $loop . ']',
            'class' => 'pisol_dcw_handle_thankyou_variation',
            'label'   => __( 'Set Thank you page for this variation ', 'pi-dcw'),
            'value'   => $handle_thankyou,
            'custom_attributes' => array('data-loop_id' => $loop)
            ) );
        echo '<div class="pisol-dcw-variation-thankyou-setting">';
        woocommerce_wp_text_input( array( 
                'id'      => 'pi_dcw_product_checkout_redirect_url[' . $loop . ']', 
                'label'   => __( 'Thank you page URL', 'pi-dcw' ), 
                'type' => 'url',
                'value' => $thankyou_url
            ) );
        woocommerce_wp_text_input( array( 
            'id'      => 'pi_dcw_product_checkout_redirect_url_weight[' . $loop . ']', 
            'label'   => __( 'Weight of this Thank you page url ', 'pi-dcw' ), 
            'type' => 'number',
            'desc_tip'    => 'true',
            'custom_attributes' => array(
                'step' 	=> '1',
                'min'	=> '0'
            ) ,
            'value' => $thankyou_weight
        ) );
        echo '</div>';
        echo '</div>';
    }

    function saveVariation( $variation_id, $i ){
        if ( isset( $_POST['pisol_dcw_handle_redirect'] ) &&  isset( $_POST['pisol_dcw_handle_redirect'][$i] )){
            update_post_meta( $variation_id, 'pisol_dcw_handle_redirect', 'yes' );
        }else{
            delete_post_meta( $variation_id, 'pisol_dcw_handle_redirect' );
        }

        if ( isset( $_POST['pi_dcw_product_redirect_to_page'] ) &&  isset( $_POST['pi_dcw_product_redirect_to_page'][$i] )){
            $redirect = $_POST['pi_dcw_product_redirect_to_page'][$i];
            update_post_meta( $variation_id, 'pi_dcw_product_redirect_to_page', $redirect );
        }else{
            delete_post_meta( $variation_id, 'pi_dcw_product_redirect_to_page' );
        }

        if ( isset( $_POST['pisol_dcw_handle_thankyou'] ) &&  isset( $_POST['pisol_dcw_handle_thankyou'][$i] )){
            update_post_meta( $variation_id, 'pisol_dcw_handle_thankyou', 'yes' );
        }else{
            delete_post_meta( $variation_id, 'pisol_dcw_handle_thankyou' );
        }

        if ( isset( $_POST['pi_dcw_product_checkout_redirect_url'] ) &&  isset( $_POST['pi_dcw_product_checkout_redirect_url'][$i] )){
            $thankyou_redirect = $_POST['pi_dcw_product_checkout_redirect_url'][$i];
            update_post_meta( $variation_id, 'pi_dcw_product_checkout_redirect_url', $thankyou_redirect );
        }else{
            delete_post_meta( $variation_id, 'pi_dcw_product_checkout_redirect_url' );
        }

        if ( isset( $_POST['pi_dcw_product_checkout_redirect_url_weight'] ) &&  isset( $_POST['pi_dcw_product_checkout_redirect_url_weight'][$i] )){
            $thankyou_weight = $_POST['pi_dcw_product_checkout_redirect_url_weight'][$i];
            update_post_meta( $variation_id, 'pi_dcw_product_checkout_redirect_url_weight', $thankyou_weight );
        }else{
            delete_post_meta( $variation_id, 'pi_dcw_product_checkout_redirect_url_weight' );
        }

    }
}

new pisol_dcw_variation_options();