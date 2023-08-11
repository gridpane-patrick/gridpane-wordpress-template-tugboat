<?php
/**
 * Related Products
 *
 * @package    related.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $related_products ) :
	
	    $xstore_amp = XStore_AMP::get_instance();
  
		$xstore_amp->create_carousel($related_products, 'products_array', array(
			'title' => apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'xstore-amp' ) ),
			'section_class' => 'related products'
		));

endif;