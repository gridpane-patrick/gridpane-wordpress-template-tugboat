<?php
/**
 * Description
 *
 * @package    optimization.php
 * @since      1.0.0
 * @author     theme
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */


class XStore_OptimizationCSS {
	function init(){
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_page_css_files' ), 99999 );
	}
	function enqueue_page_css_files(){

	    // Dequeue theme swiper-slider (enqueued in theme-init.php)
        if (etheme_get_option( 'disable_theme_swiper_js', false )){
            wp_dequeue_script( 'et_swiper-slider' );
        }
		
		// and not preview/edit view
		if ( defined( 'ELEMENTOR_VERSION' ) ) {

            // Enqueue elementor or theme swiper-slider
            if (etheme_get_option( 'disable_theme_swiper_js', false )) {
                require_once(apply_filters('etheme_file_url', ETHEME_CODE . 'features/swiper.php'));
            }
			
			if ( !(
				is_preview() ||
				Elementor\Plugin::$instance->preview->is_preview_mode()
			)
			) {
				
				if ( get_theme_mod( 'disable_elementor_dialog_js', 1 ) ) {
					$scripts = wp_scripts();
					if ( ! ( $scripts instanceof WP_Scripts ) ) {
						return;
					}
					
					$handles_to_remove = [
						'elementor-dialog',
					];
					
					$handles_updated = false;
					
					foreach ( $scripts->registered as $dependency_object_id => $dependency_object ) {
						if ( 'elementor-frontend' === $dependency_object_id ) {
							if ( ! ( $dependency_object instanceof _WP_Dependency ) || empty( $dependency_object->deps ) ) {
								return;
							}
							
							foreach ( $dependency_object->deps as $dep_key => $handle ) {
								if ( in_array( $handle, $handles_to_remove ) ) { // phpcs:ignore
									unset( $dependency_object->deps[ $dep_key ] );
									$dependency_object->deps = array_values( $dependency_object->deps );
									$handles_updated         = true;
								}
							}
						}
					}
					
					if ( $handles_updated ) {
						wp_deregister_script( 'elementor-dialog' );
						wp_dequeue_script( 'elementor-dialog' );
					}
				}
			}
		}



		if ( get_theme_mod( 'disable_block_css', 0 ) ){
			wp_deregister_style( 'wp-block-library' );
			wp_deregister_style( 'wp-block-library-theme' );
			wp_deregister_style( 'wc-block-style' );
			wp_deregister_style( 'wc-blocks-vendors-style' );

			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'wc-block-style' );
			wp_dequeue_style( 'wc-blocks-editor-style' );
		}
	}
}
$optimizationCss = new XStore_OptimizationCSS;
$optimizationCss->init();