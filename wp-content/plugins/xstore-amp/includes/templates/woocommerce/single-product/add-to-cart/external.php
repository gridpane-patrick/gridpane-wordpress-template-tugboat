<?php
/**
 * External product add to cart
 *
 * @package    external.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $product;
$product_url = $product->get_product_url();
$button_text = $product->single_add_to_cart_text();

if ( ! empty( $product_url ) ) : ?>
	<p class="amphtml-add-to">
		<a href="<?php echo esc_url( $product_url ); ?>" rel="nofollow" target="_blank"
		   class="single_add_to_cart_button button alt button"><?php echo esc_html( $button_text ); ?></a>
	</p>
<?php endif;