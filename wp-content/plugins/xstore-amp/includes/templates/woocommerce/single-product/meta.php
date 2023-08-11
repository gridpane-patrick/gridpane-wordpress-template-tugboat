<?php
/**
 * Single Product Meta
 *
 * @package    meta.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $product;
?>
<div class="product_meta">
	
	<?php do_action( 'woocommerce_product_meta_start' ); ?>
	
	<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
		
		<span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'xstore-amp' ); ?> <span class="sku" [text]="getVariationInfo.sku ? getVariationInfo.sku : '<?php echo (( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'xstore-amp' )); ?>'"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'xstore-amp' ); ?></span></span>
	
	<?php endif; ?>
	
	<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'xstore-amp' ) . ' ', '</span>' ); ?>
	
	<?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'xstore-amp' ) . ' ', '</span>' ); ?>
	
	<?php do_action( 'woocommerce_product_meta_end' ); ?>

</div>