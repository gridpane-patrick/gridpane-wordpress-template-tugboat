<?php
/**
 * Single Product Meta
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/meta.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see           https://docs.woocommerce.com/document/template-structure/
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $product;

$settings = array();

$settings['elements'] = array(
	'sku',
	'categories',
	'tags'
);

$settings['content'] = array();

if ( get_option( 'etheme_single_product_builder', false ) && function_exists( 'etheme_core_hooks' ) ) {
	$settings['elements'] = get_theme_mod( 'product_meta_content', 
		array(
			'sku',
			'categories',
			'tags',
		)
	);
}

$settings['elements'] = apply_filters( 'product_meta_elements', $settings['elements'] );
$settings['product_type'] = $product->get_type();
$settings['product_id'] = $product->get_id();
if ( $settings['product_type'] == 'variation' ) {
	$settings['product_id'] = $product->get_parent_id();
}

ob_start();

if ( wc_product_sku_enabled() && ( $product->get_sku() || $settings['product_type'] == 'variable' ) ) : ?>

    <span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'xstore' ); ?> <span
                class="sku"><?php echo esc_html( ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'xstore' ) ); ?></span></span>

<?php endif;

$settings['content']['sku'] = ob_get_clean();

ob_start();

etheme_product_cats( true );

$settings['content']['categories'] = ob_get_clean();

ob_start();

echo wc_get_product_tag_list( $settings['product_id'], ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'xstore' ) . ' ', '</span>' );

$settings['content']['tags'] = ob_get_clean();

if ( count( $settings['elements'] ) < 1 ) {
	return;
}

?>
<div class="product_meta"><?php do_action( 'woocommerce_product_meta_start' );
	foreach ( $settings['elements'] as $key ) {
		echo !empty($settings['content'][ $key ]) ? ' ' . $settings['content'][ $key ] : '';
	}
	do_action( 'woocommerce_product_meta_end' ); ?></div>

<?php unset( $settings ); ?>