<?php

class pisol_dcw_bulk_editing_option{
    function __construct(){
        add_action( 'woocommerce_product_bulk_edit_end', array($this, 'form') );
        add_action( 'woocommerce_product_bulk_edit_save', array($this, 'save') );
    }

    function form(){
        include_once 'partials/bulk-edit.php';
    }

    function save( $product ) {
        $post_id = $product->get_id();    
        if ( isset( $_GET['pi_dcw_product_redirect_to_page'] ) &&  $_GET['pi_dcw_product_redirect_to_page'] !== '' && filter_var($_GET['pi_dcw_product_redirect_to_page'], FILTER_VALIDATE_URL) !== FALSE) {
            $url = $_GET['pi_dcw_product_redirect_to_page'];
            update_post_meta( $post_id, 'pi_dcw_product_redirect_to_page', $url );
            update_post_meta( $post_id, 'pi_dcw_product_overwrite_global', 'yes' );
        }

        if ( isset( $_GET['pi_dcw_product_redirect'] ) &&  $_GET['pi_dcw_product_redirect'] !== '') {
            if( $_GET['pi_dcw_product_redirect'] == 'yes'){
                update_post_meta( $post_id, 'pi_dcw_product_redirect', 'yes' );
            }elseif( $_GET['pi_dcw_product_redirect'] == 'no'){
                update_post_meta( $post_id, 'pi_dcw_product_redirect', '' );
            }
        }

        if ( isset( $_GET['pi_dcw_product_buy_now_disable'] ) &&  $_GET['pi_dcw_product_buy_now_disable'] !== '') {
            if( $_GET['pi_dcw_product_buy_now_disable'] == 'yes'){
                update_post_meta( $post_id, 'pi_dcw_product_buy_now_disable', 'yes' );
            }elseif( $_GET['pi_dcw_product_buy_now_disable'] == 'no'){
                update_post_meta( $post_id, 'pi_dcw_product_buy_now_disable', '' );
            }
        }

        if ( isset( $_GET['pi_dcw_add_to_cart_text'] ) &&  $_GET['pi_dcw_add_to_cart_text'] !== '') {
            update_post_meta( $post_id, 'pi_dcw_add_to_cart_text', sanitize_text_field($_GET['pi_dcw_add_to_cart_text']) );
        }

        if ( isset( $_GET['pi_dcw_select_option_text'] ) &&  $_GET['pi_dcw_select_option_text'] !== '') {
            update_post_meta( $post_id, 'pi_dcw_select_option_text', sanitize_text_field($_GET['pi_dcw_select_option_text']) );
        }

        if ( isset( $_GET['pi_dcw_read_more_text'] ) &&  $_GET['pi_dcw_read_more_text'] !== '') {
            update_post_meta( $post_id, 'pi_dcw_read_more_text', sanitize_text_field($_GET['pi_dcw_read_more_text']) );
        }
    }
}
new pisol_dcw_bulk_editing_option();