<?php
if (!class_exists('pisol_dcw_change_remove_quantity_checkout')) {

class pisol_dcw_change_remove_quantity_checkout {

    public function __construct() {

        $single_page_checkout = get_option('pi_dcw_single_page_checkout', 1);

        if(!empty($single_page_checkout)) return;

        add_filter ('woocommerce_cart_item_name', array( __CLASS__, 'removeButtonAndQuantity'), PHP_INT_MAX, 3 );

        add_filter ('woocommerce_checkout_cart_item_quantity', array( __CLASS__, 'removeQuantity'), 10, 3 );
        
        add_action('wp_loaded', array(__CLASS__, 'initialize'));

        add_action( 'wp_ajax_nopriv_pisol_dcw_update_quantity', array(__CLASS__, 'updateQuantity' ) );
        add_action( 'wp_ajax_pisol_dcw_update_quantity', array(__CLASS__, 'updateQuantity' ) );

        add_action( 'wp_ajax_nopriv_pisol_dcw_remove_item', array(__CLASS__, 'removeItem' ) );
        add_action( 'wp_ajax_pisol_dcw_remove_item', array(__CLASS__, 'removeItem' ) );

        add_filter( 'wp_kses_allowed_html', array(__CLASS__, 'allowInputField'),PHP_INT_MAX,2);
    }

    /**
     * Adding Remove button and quantity box
     */
    static function removeButtonAndQuantity( $product_title, $cart_item, $cart_item_key ) {

        $remove_product = apply_filters('pi_dcw_remove_product_on_checkout_page', get_option('pi_dcw_remove_product_from_checkout_page',0), $cart_item, $cart_item_key);

        $change_qty = apply_filters('pi_dcw_change_quantity_on_checkout_page', get_option('pi_dcw_change_quantity_from_checkout_page',0), $cart_item, $cart_item_key);
        
        if (  is_checkout() ) {
            
            $product_id = $cart_item['product_id'];
            $product   = $cart_item['data'] ;
            $return_value = '';
            if(!empty($remove_product)){
                $return_value = sprintf(
                    '<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                    esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                    __( 'Remove this item', 'woocommerce' ),
                    esc_attr( $product_id ),
                    esc_attr( $product->get_sku() )
                );
            }

            $return_value .= '<span class = "pisol_product_name" >' . $product_title . '</span>' ;
            
            if(!empty($change_qty)){
                if ( $product->is_sold_individually() ) {
                    $return_value .= sprintf( '<span class="pisol-qty">1 <input type="hidden" name="cart[%s][qty]" value="1" /></span>', $cart_item_key );
                } else {
                    $return_value .= '<span class="pisol-qty">'.woocommerce_quantity_input( array(
                    'input_name'  => "cart[{$cart_item_key}][qty]",
                    'input_value' => $cart_item['quantity'],
                    'max_value'   => apply_filters( 'woocommerce_quantity_input_max',$product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product),
                    'min_value'   => apply_filters( 'woocommerce_quantity_input_min','1', $product)
                        ), $product, false ).'</span>';
                }
            }

            if((!empty($remove_product) && !empty($change_qty)) || empty($remove_product) && !empty($change_qty)){
                return sprintf('<div class="pisol-ck-product-row">%s</div>',$return_value);
            }elseif(!empty($remove_product) && empty($change_qty)){
                return sprintf('<div class="pisol-ck-product-row pi-inline">%s</div>',$return_value);
            }

            return $return_value;
        
        }
        
        return $product_title;
    }

    /*
     * It will remove the selected quantity count from checkout page table.
     */
    public static function removeQuantity($product_quantity, $cart_item, $cart_item_key ) {
        $change_qty = apply_filters('pi_dcw_change_quantity_on_checkout_page', get_option('pi_dcw_change_quantity_from_checkout_page',0), $cart_item, $cart_item_key);

        if(empty($change_qty)) return $product_quantity;

        $product_quantity= '';
      
        return $product_quantity;
    }

    static function initialize(){
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'addJS' ), 10 );
    }
    
    static function addJs(){
         
        if (  is_checkout() ) {       
            
            wp_enqueue_script('pi-dcw-checkout-qty', plugin_dir_url( __FILE__ ) . 'js/pi-checkout-qty.js', array( 'jquery' ),PI_DCW_VERSION, false );
             
        }
    }
    
    static function updateQuantity() {
         
        $values = array();
        parse_str($_POST['post_data'], $values);
        $cart = $values['cart'];
        $cart_updated = false;
        $cart_totals  = isset( $values['cart'] ) ? wp_unslash( $values['cart'] ) : '';

        if ( ! WC()->cart->is_empty() && is_array( $cart_totals ) ) {
            foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
                $_product = $values['data'];
                // Skip product if no updated quantity was posted.
                if ( ! isset( $cart_totals[ $cart_item_key ] ) || ! isset( $cart_totals[ $cart_item_key ]['qty'] ) ) {
                    continue;
                }

                $quantity = apply_filters( 'woocommerce_stock_amount_cart_item', wc_stock_amount( preg_replace( '/[^0-9\.]/', '', $cart_totals[ $cart_item_key ]['qty'] ) ), $cart_item_key );

                if ( '' === $quantity || $quantity === $values['quantity'] ) {
                    continue;
                }

                // Update cart validation.
                $passed_validation = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $values, $quantity );

                // is_sold_individually.
                if ( $_product->is_sold_individually() && $quantity > 1 ) {
                    /* Translators: %s Product title. */
                    wc_add_notice( sprintf( __( 'You can only have 1 %s in your cart.', 'woocommerce' ), $_product->get_name() ), 'error' );
                    $passed_validation = false;
                }

                if ( $passed_validation ) {
                    WC()->cart->set_quantity( $cart_item_key, $quantity, false );
                    $cart_updated = true;
                }
            }
        }

        // Trigger action - let 3rd parties update the cart if they need to and update the $cart_updated variable.
        $cart_updated = apply_filters( 'woocommerce_update_cart_action_cart_updated', $cart_updated );

        if ( $cart_updated ) {
            WC()->cart->calculate_totals();
            
            $remaining = WC()->cart->get_cart_contents_count();

            if($remaining == 0){
                wp_send_json(array('reload' => true));
            }

            wp_send_json(array('success'=>true));
        }
        die;
    }

    static function removeItem(){
        $values = array();
        $result = parse_url($_POST['url'], PHP_URL_QUERY);
        if(!$result){
            wp_send_json(array('error'=>'Improper url'));
        }

        parse_str($result, $values);

        if ( empty( $values['remove_item'] ) || !wp_verify_nonce( $values['_wpnonce'], 'woocommerce-cart' ) ){
            wp_send_json(array('error'=>'Nonce failed || item key missing'));
        }

        /**
         * code reference
         * https://github.com/woocommerce/woocommerce/blob/57569c5168667670524c8aa4a9d3c617028fef4c/includes/class-wc-form-handler.php#L614
         */
        $cart_item_key = sanitize_text_field( wp_unslash( $values['remove_item'] ) );
		$cart_item     = WC()->cart->get_cart_item( $cart_item_key );

        if ( $cart_item ) {
            WC()->cart->remove_cart_item( $cart_item_key );
            $remaining = WC()->cart->get_cart_contents_count();
            $product = wc_get_product( $cart_item['product_id'] );

            $item_removed_title = apply_filters( 'woocommerce_cart_item_removed_title', $product ? sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce' ), $product->get_name() ) : __( 'Item', 'woocommerce' ), $cart_item );

            // Don't show undo link if removed item is out of stock.
            if ( $product && $product->is_in_stock() && $product->has_enough_stock( $cart_item['quantity'] ) ) {
                /* Translators: %s Product title. */
                $removed_notice  = sprintf( __( '%s removed.', 'woocommerce' ), $item_removed_title );
                $removed_notice .= ' <a href="' . esc_url( wc_get_cart_undo_url( $cart_item_key ) ) . '" class="restore-item">' . __( 'Undo?', 'woocommerce' ) . '</a>';
            } else {
                /* Translators: %s Product title. */
                $removed_notice = sprintf( __( '%s removed.', 'woocommerce' ), $item_removed_title );
            }

            wc_add_notice( $removed_notice, apply_filters( 'woocommerce_cart_item_removed_notice_type', 'success' ) );
        }

        if($remaining == 0){
            wp_send_json(array('reload' => true));
        }

        wp_send_json(array('success'=>true));
    }

    /**
     * this is needed as WooCommerce has uses a html tag filter that filter out <input> filed so we have to allow input field in that filter 
     */
    static function allowInputField($allowedposttags, $context){
        if($context === 'post'){
            $allowedposttags['input'] = array(
                'name' => true,
                'type' => true,
                'id' => true,
                'class' => true,
                'step' => true,
                'min' => true,
                'max' => true,
                'value' => true,
                'title' => true,
                'size' => true,
                'placeholder' => true,
                'inputmode' => true
            );
        }
        return $allowedposttags;
    }
}
}
new pisol_dcw_change_remove_quantity_checkout();