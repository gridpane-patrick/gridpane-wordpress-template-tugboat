<?php
/**
 * Cross-Sells products
 *
 * @package    cross-sells.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit;

if ( $cross_sells ) :
	
	$xstore_amp = XStore_AMP::get_instance();
	
	$xstore_amp->create_carousel($cross_sells, 'products_array', array(
		'title' => apply_filters( 'woocommerce_product_cross_sells_products_heading', __( 'You may be interested in&hellip;', 'xstore-amp' ) ),
		'section_class' => 'cross-sells',
	));

endif;