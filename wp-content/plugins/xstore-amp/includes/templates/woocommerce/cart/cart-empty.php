<?php
/**
 * Empty cart page
 *
 * @package    cart-empty.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $et_cart_icons;
/*
 * @hooked wc_empty_cart_message - 10
 */
do_action( 'woocommerce_cart_is_empty' );

if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
	<div class="return-to-shop text-center">
        <h3><?php echo str_replace(array('<svg', '1em'), array('<svg fill="currentColor"', '2.5em'), $et_cart_icons['light']['type3']); ?></h3>
        <h1><?php esc_html_e('Your shopping cart is empty', 'xstore-amp') ?></h1>
        <p><?php esc_html_e('We invite you to get acquainted with an assortment of our shop. Surely you can find something for yourself!', 'xstore-amp') ?></p>
        <p>
            <a class="button wc-backward arrow-left" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
                <?php
                /**
                 * Filter "Return To Shop" text.
                 *
                 * @since 4.6.0
                 * @param string $default_text Default text.
                 */
                echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'xstore-amp' ) ) );
                ?>
            </a>
        </p>
    </div>
<?php endif;