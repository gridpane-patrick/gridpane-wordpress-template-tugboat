<?php
/**
 * Sticky Cart template
 *
 * @package    sticky-cart.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $product;
global $xstore_amp_icons;
global $et_icons;
$product_type = $product->get_type();
?>
<amp-state id="stickyForm">
    <script type="application/json">0</script>
</amp-state>
<div id="sticky-cart" class="sticky-cart pos-fixed with-shadow" [class]="stickyForm == 'no' || !stickyForm ? 'sticky-cart pos-fixed with-shadow' : 'sticky-cart invisible pos-fixed with-shadow'">
    <div class="sticky-cart-wrapper">
        <div class="amp-container flex align-items-center">
            <?php if ( !in_array($product_type, array('external'))) : ?>
                <a class="button bordered return arrow-left icon" href="<?php echo esc_url(get_permalink(wc_get_page_id( 'shop' ))); ?>"><?php echo $et_icons['light']['et_icon-shop']; ?></a>
                <button class="single_add_to_cart_button" on="tap: AMP.setState({stickyForm: 'yes'})"><?php echo esc_html__('Buy now', 'xstore-amp'); ?></button>
            <?php else :
                do_action( 'woocommerce_' . $product_type . '_add_to_cart' );
            endif; ?>
        </div>
    </div>
</div>
<?php if ( !in_array($product_type, array('external'))) : ?>
    <div class="sticky-cart-form invisible pos-fixed with-shadow" [class]="(stickyForm == 'yes') ? 'sticky-cart-form pos-fixed with-shadow' : 'sticky-cart-form invisible pos-fixed with-shadow'">
        <div class="sticky-cart-wrapper">
            <div class="amp-container flex align-items-center">
                <button class="close with-shadow inline-flex align-items-center text-center" on="tap: AMP.setState({stickyForm: 'no'})"><?php echo $xstore_amp_icons['et_icon-close']; ?></button>
                <?php do_action('woocommerce_'.$product_type.'_add_to_cart'); ?>
            </div>
        </div>
    </div>
<?php endif; ?>