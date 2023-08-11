<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * @package    archive-product.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

$this->add_custom_css_action( 'global', 'woocommerce' );
$this->add_custom_css_action( 'star-rating', 'woocommerce' );
$this->add_custom_css_action('archive', 'woocommerce');
// $this->add_custom_css_action( 'tables', 'base' ); // only for calendar widget

//get_header( 'shop' );

global $xstore_amp_settings;

$config = array(
    'is_search' => is_search(),
    'search_results' => array(
	    'products',
	    'posts',
    )
);

if ( isset($xstore_amp_settings['general']['search_results']) && !empty($xstore_amp_settings['general']['search_results']) ) {
	$config['search_results'] = explode(',', $xstore_amp_settings['general']['search_results']);
	foreach ( $config['search_results'] as $element_key => $element_name ) {
		if ( !isset($xstore_amp_settings['general'][$element_name.'_visibility']) || !$xstore_amp_settings['general'][$element_name.'_visibility'] ) {
			unset($config['search_results'][$element_key]);
		}
	}
}

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

?>
    <header class="woocommerce-products-header">
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
            <h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>
		
		<?php
		/**
		 * Hook: woocommerce_archive_description.
		 *
		 * @hooked woocommerce_taxonomy_archive_description - 10
		 * @hooked woocommerce_product_archive_description - 10
		 */
		do_action( 'woocommerce_archive_description' );
		?>
    </header>
    
    <?php if ( !$config['is_search'] && is_active_sidebar( 'xstore-amp-shop-sidebar' ) ) { ?>
        <div>
            <amp-position-observer on="enter:shopSidebarHideAnim.start; exit:shopSidebarShowAnim.start" layout="nodisplay"></amp-position-observer>
        </div>
    <?php } ?>
<?php
if ( woocommerce_product_loop() ) {
	
	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action( 'woocommerce_before_shop_loop' );
	
	?>
    <div class="toolbar flex align-items-center justify-content-between">
        <?php
            woocommerce_catalog_ordering();
            woocommerce_result_count();
        ?>
    </div>
    
    <?php
	
	do_action( 'xstore_amp_before_product_loop_start', wc_get_loop_prop( 'total' ) );
        
    if ( is_array($config['search_results']) && $config['is_search'] && ! in_array('products', $config['search_results'] ) ):
    else:
	
	woocommerce_product_loop_start();
	
	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();
			
			/**
			 * Hook: woocommerce_shop_loop.
			 */
			do_action( 'woocommerce_shop_loop' );
			
			wc_get_template_part( 'content', 'product' );
		}
	}
	
	woocommerce_product_loop_end();
	endif;
	
	if ( is_array($config['search_results']) && $config['is_search'] && ! in_array('products', $config['search_results'] ) ):
				
    else:
        
        /**
         * Hook: woocommerce_after_shop_loop.
         *
         * @hooked woocommerce_pagination - 10
         */
        do_action( 'woocommerce_after_shop_loop' );
        
    endif;
	
	do_action( 'xstore_amp_after_product_loop_end' );
	
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'xstore_amp_before_product_loop_start' );
	do_action( 'woocommerce_no_products_found' );
	do_action( 'xstore_amp_after_product_loop_start' );
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );