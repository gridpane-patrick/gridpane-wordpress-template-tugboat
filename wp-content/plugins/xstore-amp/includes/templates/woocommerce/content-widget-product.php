<?php
/**
 * The template for displaying product widget entries.
 *
 * @package    content-widget-product.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $product;

if ( ! is_a( $product, 'WC_Product' ) ) {
    return;
}
?>

<li>
    <?php do_action( 'woocommerce_widget_product_item_start', $args ); ?>
    
    <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>" class="product-list-image">
        <?php echo $product->get_image(); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </a>

    <div class="product-item-right">

        <p class="product-title"><a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>"><?php echo wp_kses_post( $product->get_name() ); ?></a></p>

        <?php if ( ! empty( $show_rating ) ) : ?>
            <?php echo wp_kses_post( wc_get_rating_html( $product->get_average_rating() ) ); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php endif; ?>

        <div class="price">
            <?php echo $product->get_price_html(); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        
    </div>
    
    <?php do_action( 'woocommerce_widget_product_item_end', $args ); ?>
</li>