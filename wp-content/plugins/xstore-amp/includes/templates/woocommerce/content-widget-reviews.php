<?php
/**
 * The template for displaying product widget entries.
 *
 * @package    content-widget-reviews.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

?>
<li>
	<?php do_action( 'woocommerce_widget_product_review_item_start', $args ); ?>
	
	<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>" class="product-list-image">
		<?php echo $product->get_image(); ?>
	</a>
	
	<div class="product-item-right">
		
		<p class="product-title">
			<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>" title="<?php echo esc_attr( $product->get_name() ); ?>"><?php echo wp_specialchars_decode($product->get_name()); ?></a>
		</p>
		
		<?php echo wc_get_rating_html( intval( get_comment_meta( $comment->comment_ID, 'rating', true ) ) );?>
		
		<span class="reviewer"><?php echo sprintf( esc_html__( 'by %s', 'xstore-amp' ), get_comment_author( $comment->comment_ID ) ); ?></span>
	
	</div>
	
	<?php do_action( 'woocommerce_widget_product_review_item_end', $args ); ?>
</li>