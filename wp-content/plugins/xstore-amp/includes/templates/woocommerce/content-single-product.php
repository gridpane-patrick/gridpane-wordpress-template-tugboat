<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * @package    content-single-product.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}

global $wp_filter;
$summary_actions            = $wp_filter['woocommerce_single_product_summary'];
$actions_2_remove = array();
if ( ! empty( $summary_actions ) ) {
	foreach ( $summary_actions as $item_priority => $item_value) {
		foreach ( $item_value as $action => $args ) {
			if ( strpos($action, 'add_compare_link') != false) {
				$actions_2_remove[] = array(
					'name' => $action,
					'priority' => $item_priority
				);
			}
		}
	}
	foreach ($actions_2_remove as $action) {
		remove_action('woocommerce_single_product_summary', $action['name'], $action['priority']);
	}
}

?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'single-product-content', $product ); ?>>
    
    <div class="single-grid clearfix">
	
        <div class="product-gallery">
            <?php
            /**
             * Hook: woocommerce_before_single_product_summary.
             *
             * @hooked woocommerce_show_product_sale_flash - 10
             * @hooked woocommerce_show_product_images - 20
             */
            do_action( 'woocommerce_before_single_product_summary' );
            ?>
        </div>
        
        <div class="summary entry-summary">
            <?php
            /**
             * Hook: woocommerce_single_product_summary.
             *
             * @hooked woocommerce_template_single_title - 5
             * @hooked woocommerce_template_single_rating - 10
             * @hooked woocommerce_template_single_price - 10
             * @hooked woocommerce_template_single_excerpt - 20
             * @hooked woocommerce_template_single_add_to_cart - 30
             * @hooked woocommerce_template_single_meta - 40
             * @hooked woocommerce_template_single_sharing - 50
             * @hooked WC_Structured_Data::generate_product_data() - 60
             */
            do_action( 'woocommerce_single_product_summary' );
            ?>
        </div>
    
    </div>
	
	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	?>
</div>

<?php  do_action( 'woocommerce_after_single_product' );