<?php
/**
 * Variable product add to cart
 *
 * @package    variable.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $product, $product_custom_vars;

$xstore_amp = XStore_AMP::get_instance();

$action_url =  admin_url('admin-ajax.php?action=xstore_amp_add_to_cart');
$action_url = preg_replace('#^https?:#', '', $action_url);

$uniq_id = uniqid('variationForm_');
$product_custom_vars['uniq_id'] = $uniq_id;

?>

    <form id="<?php echo esc_attr($uniq_id); ?>" class="variations_form cart" action-xhr="<?php echo $action_url ?>" method="POST" target="_top"
          on="submit-success: AMP.setState({'cartCount':event.response.count, 'cartCount_hidden' : event.response.hide_item, getVariationInfo: event.response.variation });">
        
	    <?php do_action( 'woocommerce_before_variations_form' ); ?>
	
        <?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
            <p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'xstore-amp' ) ) ); ?></p>
        <?php else : ?>
            <table class="variations" cellspacing="0">
                <tbody>
                <?php foreach ( $attributes as $attribute_name => $options ) : ?>
                    <tr>
                        <td class="label"><label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></td>
                        <td class="value">
                            <?php
                            ob_start();
                                wc_dropdown_variation_attribute_options(
                                    array(
                                        'options'   => $options,
                                        'attribute' => $attribute_name,
                                        'product'   => $product,
                                        'selected' => '',
                                        'show_option_none' => __( 'Choose an option', 'xstore-amp' ),
                                    )
                                );
                                $select = ob_get_clean();
                                echo str_replace('<select',
                                    '<select on="change:AMP.setState({\'formGetVariationAction\': \'yes\', \'postVariationAddToCartVal\': \'string\'}), '.$uniq_id.'.submit, mainCarousel.goToSlide(index=0), thumbCarouselSelector.toggle(index=0, value=true), thumbCarousel.goToSlide(index=0)"',
                                    $select);
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <input type="hidden" name="amp-get-variation" value="no" [value]="formGetVariationAction">

        <div class="single_variation_wrap">

            <div class="price" [hidden]="!getVariationInfo.price" hidden>
                <span [hidden]="getVariationInfo.regular_price == getVariationInfo.price" [hidden]>
                    <del><?php echo sprintf( get_woocommerce_price_format(),
		                    '<span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol() . '</span>',
		                    '<span class="amount" [text]="getVariationInfo.regular_price"></span>' ); ?>
                    </del>
                </span>
                <span [class]="getVariationInfo.regular_price != getVariationInfo.price ? 'amount active' : 'amount'">
                    <?php echo sprintf( get_woocommerce_price_format(),
	                    '<span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol() . '</span>',
	                    '<span [text]="getVariationInfo.price"></span>' ); ?>
                </span>
            </div>
            
			<?php
			/**
			 * Hook: woocommerce_before_single_variation.
			 */
			do_action( 'woocommerce_before_single_variation' );
			
			/**
			 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
			 *
			 * @since 2.4.0
			 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
			 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
			 */
			do_action( 'woocommerce_single_variation' );
			
			/**
			 * Hook: woocommerce_after_single_variation.
			 */
			do_action( 'woocommerce_after_single_variation' );
			
			?>
            <input type="hidden" name="variation-add-to-cart" value="1">
        </div>
        
            <?php $xstore_amp->form_submitting(); ?>
    
            <?php $xstore_amp->form_success(); ?>
        
            <?php $xstore_amp->form_error(); ?>

        <?php endif; ?>
	
	    <?php do_action( 'woocommerce_after_variations_form' ); ?>
        
    </form>

<?php

do_action( 'woocommerce_after_add_to_cart_form' );