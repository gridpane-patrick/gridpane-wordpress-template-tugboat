<?php
/**
 * Description
 *
 * @package    wcmp.php
 * @since      8.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

// **********************************************************************//
// ! WC Marketplace fix
// **********************************************************************//
if ( class_exists( 'WCMp_Ajax' ) ) {
	add_action( 'wp_head', 'single_product_multiple_vendor_class' );
}

if ( ! function_exists( 'single_product_multiple_vendor_class' ) ) :
	function single_product_multiple_vendor_class(){
		?>
		<script type="text/javascript">
            var themeSingleProductMultivendor = '#content_tab_singleproductmultivendor';
		</script>
		<?php
	}
endif;

add_action( 'wp_enqueue_scripts', function (){
	if ( function_exists('wcmp_is_store_page') && wcmp_is_store_page() ) {
		etheme_enqueue_style( 'star-rating' );
		etheme_enqueue_style( 'comments' );
		
		wp_enqueue_script( 'comment-reply' );
	}
}, 35 );

add_filter('do_shortcode_tag', function ($content, $shortcode, $atts) {
	if ( $shortcode == 'wcmp_vendor' ) {
		if ( !is_user_logged_in() ) {
			etheme_enqueue_style( 'account-page' );
			$content = str_replace( 'wcmp-dashboard', 'wcmp-dashboard woocommerce-account', $content );
		}
	}
	return $content;
},10,3);