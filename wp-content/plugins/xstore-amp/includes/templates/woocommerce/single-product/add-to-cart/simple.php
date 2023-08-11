<?php
/**
 * Simple product add to cart
 *
 * @package    simple.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

$xstore_amp = XStore_AMP::get_instance();

$action_url =  admin_url('admin-ajax.php?action=xstore_amp_add_to_cart');
$action_url = preg_replace('#^https?:#', '', $action_url);

// echo wc_get_stock_html( $product ); // WPCS: XSS ok.

if ( $product->is_in_stock() ) : ?>
	
	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>
	
	<form class="cart"  method="POST" action-xhr="<?php echo $action_url; ?>" target="_top" on="submit-success: AMP.setState({'cartCount':event.response.count, 'cartCount_hidden' : event.response.hide_item })">
		
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
        
        <div class="before-addtocart-wrapper">
		
            <?php
            do_action( 'woocommerce_before_add_to_cart_quantity' );
            
            woocommerce_quantity_input(
                array(
                    'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                    'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                    'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
                )
            );
            
            do_action( 'woocommerce_after_add_to_cart_quantity' );
            ?>
    
            <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
            
        </div>
        
		<?php $xstore_amp->form_submitting(); ?>
		
		<?php $xstore_amp->form_success(); ?>
		
		<?php $xstore_amp->form_error(); ?>
		
		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
    </form>
	
	<?php do_action( 'woocommerce_after_add_to_cart_form' );

endif;