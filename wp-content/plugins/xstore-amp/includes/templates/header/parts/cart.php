<?php
/**
 * Header cart template
 *
 * @package    cart.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $et_cart_icons, $xstore_amp_vars;

?>
<amp-state id="cartCount">
    <script type="application/json">
        <?php echo json_encode($xstore_amp_vars['cart_count']); ?>
    </script>
</amp-state>
<span class="h-cart inline-block cart-element pos-relative" on="tap:cartCanvas.open" role="button" tabindex="-1" data-element="cart">
	<?php echo $et_cart_icons['light']['type1']; ?>
	<?php echo $this->cartCount(); ?>
</span>