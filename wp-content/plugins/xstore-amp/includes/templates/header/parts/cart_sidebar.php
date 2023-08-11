<?php
/**
 * Header cart sidebar template
 *
 * @package    cart_sidebar.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

//global $xstore_amp_icons;
?>

<amp-sidebar id="cartCanvas" layout="nodisplay" side="right">
<!--    <button class="close" on="tap:cartCanvas.close">--><?php //echo $xstore_amp_icons['et_icon-close']; ?><!--</button>-->
    <h2 class="sidebar-title"><?php echo esc_html__('Shopping Cart', 'xstore-amp'); ?></h2>
	<?php woocommerce_mini_cart(); ?>
</amp-sidebar>