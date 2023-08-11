<?php  if ( ! defined('ABSPATH')) exit('No direct script access allowed');

// **********************************************************************// 
// ! Define base constants
// **********************************************************************//

define('ETHEME_FW', '1.0');
define('ETHEME_BASE', get_template_directory() .'/');
define('ETHEME_CHILD', get_stylesheet_directory() .'/');
define('ETHEME_BASE_URI', get_template_directory_uri() .'/');

define('ETHEME_CODE', 'framework/');
define('ETHEME_CODE_DIR', ETHEME_BASE.'framework/');
define('ETHEME_TEMPLATES', ETHEME_CODE . 'templates/');
define('ETHEME_THEME', 'theme/');
define('ETHEME_THEME_DIR', ETHEME_BASE . 'theme/');
define('ETHEME_TEMPLATES_THEME', ETHEME_THEME . 'templates/');
define('ETHEME_CODE_3D', ETHEME_CODE .'thirdparty/');
define('ETHEME_CODE_3D_URI', ETHEME_BASE_URI.ETHEME_CODE .'thirdparty/');
define('ETHEME_CODE_WIDGETS', ETHEME_CODE .'widgets/');
define('ETHEME_CODE_POST_TYPES', ETHEME_CODE .'post-types/');
define('ETHEME_CODE_SHORTCODES', ETHEME_CODE .'shortcodes/');
define('ETHEME_CODE_CSS', ETHEME_BASE_URI . ETHEME_CODE .'assets/admin-css/');
define('ETHEME_CODE_JS', ETHEME_BASE_URI . ETHEME_CODE .'assets/js/');
define('ETHEME_CODE_IMAGES', ETHEME_BASE_URI . ETHEME_THEME .'assets/images/');
define('ETHEME_CODE_CUSTOMIZER_IMAGES', ETHEME_BASE_URI . ETHEME_CODE . 'customizer/images/theme-options');
define('ETHEME_API', 'https://www.8theme.com/themes/api/');

define('ETHEME_PREFIX', '_et_');

define( 'ETHEME_THEME_VERSION', '8.2.3' );
define( 'ETHEME_CORE_MIN_VERSION', '4.2.3' );
define( 'ETHEME_MIN_CSS', get_theme_mod( 'et_load_css_minify', true ) ? '.min' : '' );
// **********************************************************************// 
// ! Helper Framework functions
// **********************************************************************//
require_once( ETHEME_BASE . ETHEME_CODE . 'helpers.php' );

/*
* Theme features
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'features/init.php') );

/*
* Theme f-ns
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'theme-functions.php') );

/*
* Theme template elements
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'template-elements.php') );

// global functions for post types
require_once( apply_filters('etheme_file_url', ETHEME_CODE_POST_TYPES . 'post-functions.php') );
require_once( apply_filters('etheme_file_url', ETHEME_CODE_POST_TYPES . 'menu-functions.php') );
require_once( apply_filters('etheme_file_url', ETHEME_CODE_POST_TYPES . 'social-functions.php') );

/*
* Menu walkers
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'walkers.php') );

// **********************************************************************// 
// ! Framework setup
// **********************************************************************//
require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'theme-init.php') );


/*
* Post types
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE_POST_TYPES . 'static-blocks.php') );
require_once( apply_filters('etheme_file_url', ETHEME_CODE_POST_TYPES . 'portfolio.php') );

/*
* Plugin compatibilities
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'compatibility/init.php') );

/*
* Plugins activation
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE_3D . 'tgm-plugin-activation/class-tgm-plugin-activation.php') );


/*
* Video parse from url
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE_3D . 'parse-video/VideoUrlParser.class.php') );

/*
* Taxonomy metadat
* ******************************************************************* */
require_once apply_filters('etheme_file_url', ETHEME_CODE_3D . 'cmb2-taxonomy/init.php');

/*
* WooCommerce f-ns
* ******************************************************************* */
if(class_exists('WooCommerce') && current_theme_supports('woocommerce') ) {
	require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'woo.php') );
}

/* 
*
* Theme Options 
* ******************************************************************* */

require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'class-kirki-installer-section.php') );

require_once( apply_filters('etheme_file_url',ETHEME_CODE . 'customizer/search/class-customize-search.php' ) );

require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'theme-options.php') );

// customizer Kirki loader style
add_action( 'wp_head', 'etheme_head_config_customizer', 99);

add_action('init', function(){
	if ( get_query_var('et_is_customize_preview', false)  ) {
		// dequeue WooZone style 
		add_action( "admin_print_styles", function() {
			wp_dequeue_style('WooZone-main-style');
		} );
	}

}, 10);

function etheme_head_config_customizer () {
	if ( !get_query_var('et_is_customize_preview', false) ) return;
	if ( !class_exists('Kirki') ) return;
		?>
		<style>
			.kirki-customizer-loading-wrapper {
				background-image: none !important;
			}
			.kirki-customizer-loading-wrapper .kirki-customizer-loading {
			    background: #555 !important;
			    width: 30px !important;
			    height: 30px !important;
			    margin: -15px !important;
			}
		</style>
	<?php
}

add_action( 'customize_controls_print_footer_scripts', 'etheme_load_admin_styles_customizer' );
function etheme_load_admin_styles_customizer() {
	
	$xstore_branding_settings = get_option( 'xstore_white_label_branding_settings', array() );
	
 	if(class_exists('Kirki') ) {
    	wp_dequeue_style( 'woocommerce_admin_styles' );
    }
    wp_enqueue_style('etheme_customizer_css', ETHEME_BASE_URI . ETHEME_CODE.'customizer/css/admin_customizer.css');
 	if ( is_rtl() ) {
	    wp_enqueue_style('etheme_customizer_rtl_css', ETHEME_BASE_URI . ETHEME_CODE.'customizer/css/admin_customizer-rtl.css');
    }

	$icons_type = ( etheme_get_option('bold_icons', 0) ) ? 'bold' : 'light';
	wp_register_style( 'xstore-icons-font', false );
    wp_enqueue_style( 'xstore-icons-font' );
    wp_add_inline_style( 'xstore-icons-font', 
        "@font-face {
		  font-family: 'xstore-icons';
		  src:
		    url('".get_template_directory_uri()."/fonts/xstore-icons-".$icons_type.".ttf') format('truetype'),
		    url('".get_template_directory_uri()."/fonts/xstore-icons-".$icons_type.".woff2') format('woff2'),
		    url('".get_template_directory_uri()."/fonts/xstore-icons-".$icons_type.".woff') format('woff'),
		    url('".get_template_directory_uri()."/fonts/xstore-icons-".$icons_type.".svg#xstore-icons') format('svg');
		  font-weight: normal;
		  font-style: normal;
		}"
	);
    
    if ( count($xstore_branding_settings) && isset($xstore_branding_settings['customizer'])) {
        $output = '';
        if ( isset($xstore_branding_settings['customizer']['main_color']) && $xstore_branding_settings['customizer']['main_color'] ) {
            $output .= ':root {--et_admin_main-color: '.$xstore_branding_settings['customizer']['main_color'] . ';}';
        }
        if ( isset($xstore_branding_settings['customizer']['logo']) && trim($xstore_branding_settings['customizer']['logo']) != '') {
            $output .= '#customize-header-actions {
                background-image: url("'.$xstore_branding_settings['customizer']['logo'].'");
                background-size: contain;
            }';
        }
	    wp_add_inline_style('etheme_customizer_css', $output );
    }
}

function etheme_customizer_live_preview() {

    wp_enqueue_style( 'etheme-customizer-preview-css', ETHEME_BASE_URI . ETHEME_CODE . 'customizer/css/preview.css', null, '0.1', 'all' );
    wp_enqueue_script( 'etheme-customizer-frontend-js', ETHEME_BASE_URI . ETHEME_CODE . 'customizer/js/preview.js', array('jquery'), '0.1', 'all' ); 
}

add_action( 'customize_controls_print_styles', 'etheme_customizer_css', 99 );

function etheme_customizer_css() { ?>
	<style>
    	.wp-customizer:not(.ready) #customize-controls:before,
    	.wp-customizer.et-preload #customize-controls:before {
		    position: absolute;
		    left: 0;
		    top: 0;
		    right: 0;
		    bottom: 0;
		    background: #fff;
		    content: '';
		    z-index: 500002;
		}

		.wp-customizer.et-preload #customize-controls:before {
			opacity: .5;
		}

		.wp-customizer:not(.ready) #customize-controls:after,
		.wp-customizer.et-preload #customize-controls:after {
		    content: '';
		    position: absolute;
		    top: 50%;
		    left: 50%;
		    width: 30px;
		    height: 30px;
		    background: #555;
		    margin: -15px;
		    border-radius: 50%;
		    -webkit-animation: sk-scaleout 1.0s infinite ease-in-out;
		    animation: sk-scaleout 1.0s infinite ease-in-out;
		    z-index: 500002;
		}
		@-webkit-keyframes sk-scaleout {
		    0% { -webkit-transform: scale(0) }
		    100% {
		        -webkit-transform: scale(1.0);
		        opacity: 0;
		    }
		}
		@keyframes sk-scaleout {
		    0% {
		        -webkit-transform: scale(0);
		        transform: scale(0);
		    }
		    100% {
		        -webkit-transform: scale(1.0);
		        transform: scale(1.0);
		        opacity: 0;
		    }
		}
  
	</style>
	<?php
}

add_action( 'customize_controls_print_scripts', 'etheme_customizer_js', 99);

function etheme_customizer_js() {
    
    ?>
    <script type="text/javascript">
        jQuery( document ).ready( function( $ ) {
            <?php $blog_id = get_option( 'page_for_posts' );
            
            if ( $blog_id ) : ?>
                wp.customize.section( 'blog-blog_page', function( section ) {
                    section.expanded.bind( function( isExpanded ) {
                        if ( isExpanded ) {
                            wp.customize.previewer.previewUrl.set( '<?php echo esc_js( get_permalink( $blog_id ) ); ?>' );
                        }
                    } );
                } );
            <?php endif;
	
	        $single_post_link = '';
	        $args = array(
		        'post_type' => 'post',
		        'post_status' => 'publish',
		        'orderby' => 'date',
		        'order' => 'ASC',
		        'posts_per_page' => 1
	        );
	        $loop = new WP_Query( $args );
	        if ( $loop->have_posts() ) {
		        while ( $loop->have_posts() ) : $loop->the_post();
			        $single_post_link = get_permalink(get_the_ID());
		        endwhile;
	        }
	        wp_reset_postdata();
	
	        if ( $single_post_link ) : ?>
            wp.customize.section( 'blog-single-post', function( section ) {
                section.expanded.bind( function( isExpanded ) {
                    if ( isExpanded ) {
                        wp.customize.previewer.previewUrl.set( '<?php echo esc_js( $single_post_link ); ?>' );
                    }
                } );
            } );
	        <?php endif;
	        
	        $portfolio_id = get_theme_mod('portfolio_page', '');
	        if ( $portfolio_id ) { ?>
                wp.customize.section( 'portfolio', function( section ) {
                    section.expanded.bind( function( isExpanded ) {
                        if ( isExpanded ) {
                            wp.customize.previewer.previewUrl.set( '<?php echo esc_js( get_permalink( $portfolio_id ) ); ?>' );
                        }
                    } );
                } );
	        <?php }
	
	        if ( class_exists('WooCommerce')) :
            
                $product_link = '';
                $args = array(
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'ASC',
                    'posts_per_page' => 1
                );
                $loop = new WP_Query( $args );
                if ( $loop->have_posts() ) {
                    while ( $loop->have_posts() ) : $loop->the_post();
	                    $product_link = get_permalink(get_the_ID());
                    endwhile;
                }

                if ( isset($_REQUEST['et_multiple']) ){
	                $product_link = add_query_arg( 'et_multiple', $_REQUEST['et_multiple'], $product_link );
                }

                wp_reset_postdata(); ?>
            
                wp.customize.panel( 'shop', function( section ) {
                    section.expanded.bind( function( isExpanded ) {
                        if ( isExpanded ) {
                            wp.customize.previewer.previewUrl.set( '<?php echo esc_js( wc_get_page_permalink( 'shop' ) ); ?>' );
                        }
                    } );
                } );
                wp.customize.panel( 'shop-elements', function( section ) {
                    section.expanded.bind( function( isExpanded ) {
                        if ( isExpanded ) {
                            wp.customize.previewer.previewUrl.set( '<?php echo esc_js( wc_get_page_permalink( 'shop' ) ); ?>' );
                        }
                    } );
                } );
                wp.customize.panel( 'cart-page', function( section ) {
                    section.expanded.bind( function( isExpanded ) {
                        if ( isExpanded ) {
                            wp.customize.previewer.previewUrl.set( '<?php echo esc_js( wc_get_page_permalink( 'cart' ) ); ?>' );
                        }
                    } );
                } );
                <?php if ( $product_link ) { ?>
                    wp.customize.panel( 'single_product_builder', function( section ) {
                        section.expanded.bind( function( isExpanded ) {
                            if ( isExpanded ) {
                                wp.customize.previewer.previewUrl.set( '<?php echo esc_js( $product_link ); ?>' );
                            }
                        } );
                    } );
                    wp.customize.panel( 'single-product-page', function( section ) {
                        section.expanded.bind( function( isExpanded ) {
                            if ( isExpanded ) {
                                wp.customize.previewer.previewUrl.set( '<?php echo esc_js( $product_link ); ?>' );
                            }
                        } );
                    } );
                <?php }
            endif; ?>
        });
    </script>
    <?php
}

add_action( 'customize_preview_init', 'etheme_customizer_live_preview' );
	

/*
* Sidebars
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'sidebars.php') );

/*
* Custom Metaboxes for pages
* ******************************************************************* */
require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'custom-metaboxes.php') );

/*
* Admin panel setup
* ******************************************************************* */
if ( is_admin() ) {
	require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'system-requirements.php') );

	// require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'thirdparty/fonts_uploader/etheme_fonts_uploader.php') );
	
	require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'admin.php') );

	require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'admin/widgets/class-admin-sidebasr.php') );

	require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'panel/panel.php') );

	require_once( apply_filters('etheme_file_url', ETHEME_CODE_3D . 'menu-images/nav-menu-images.php'));

	/*
	* Check theme version
	* ******************************************************************* */
	require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'version-check.php') );

}

/*
* without core plugin functionality
* ******************************************************************* */
if (! defined('ET_CORE_VERSION')){
	require_once( apply_filters('etheme_file_url', ETHEME_CODE . 'plugin-disabled/init.php') );
}
