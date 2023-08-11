<?php
/**
 * Single variation cart button
 *
 * @package    tabs.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $product, $product_custom_vars;

?>
<div class="woocommerce-variation-add-to-cart variations_button">

    <div class="before-addtocart-wrapper">
        
        <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
        
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
        
        <button type="button" class="single_add_to_cart_button button alt" on="tap: AMP.setState({'postVariationAddToCartVal': '<?php echo absint( $product->get_id() ); ?>', 'formGetVariationAction': 'no'}), <?php echo esc_attr($product_custom_vars['uniq_id']); ?>.submit"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
        
        <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
        
    </div>
	
	<input type="hidden" [value]="postVariationAddToCartVal" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
    <input type="hidden" name="amp-add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>