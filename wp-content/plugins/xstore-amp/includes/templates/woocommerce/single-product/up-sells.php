<?php
/**
 * Single Product Up-Sells
 *
 * @package    up-sells.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $upsells ) :
	
	$xstore_amp = XStore_AMP::get_instance();
	
	$xstore_amp->create_carousel($upsells, 'products_array', array(
		'title' => apply_filters( 'woocommerce_product_upsells_products_heading', __( 'You may also like&hellip;', 'xstore-amp' ) ),
		'section_class' => 'up-sells upsells products',
	));

endif;