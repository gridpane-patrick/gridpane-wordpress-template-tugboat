<?php
/**
 * @link              https://xstore.8theme.com
 * @since             1.0.0
 * @package           XStore AMP
 *
 * Plugin Name:       XStore AMP
 * Plugin URI:        http://8theme.com
 * Description:       8theme AMP Plugin for XStore theme.
 * Version:           1.0.2
 * Author:            8theme
 * Author URI:        https://xstore.8theme.com
 * Text Domain:       xstore-amp
 * Domain Path:       /languages
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

if ( !class_exists('XStore_AMP')) :
	
	class XStore_AMP {
		
		protected $post_id;
		
		protected static $instance = null;
		
		public $cart_count;
		
		public $specific_key_amp;
		
		public function init() {
			$this->define_constants();
			$this->define_vars();
			$this->define_is_amp();
//			$this->amp_add_query();
			$this->includes();
			$this->load_app();
			$this->register_sidebars();
			$this->add_action_links();
		}
		
		/**
		 * Add filter for plugin links in admin.
		 *
		 * @since 1.0.2
		 *
		 * @return void
		 */
		public function add_action_links() {
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
        }
		
		/**
		 * Show action links on the plugin screen.
		 *
		 * @param mixed $links Plugin Action links.
		 * @return array
		 *
		 * @since 1.0.2
		 *
		 */
		public static function plugin_action_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=et-panel-xstore-amp' ) . '" aria-label="' . esc_attr__( 'View XStore AMP settings', 'xstore-amp' ) . '">' . esc_html__( 'Settings', 'xstore-amp' ) . '</a>',
			);
			
			return array_merge( $action_links, $links );
		}
		
		private function define_constants() {
			
			$this->define('AMP_QUERY_VAR', 'amp');
			/**
			 * define XStore_AMP_VERSION
			 *
			 * @since 1.0.0
			 */
			$this->define( 'XStore_AMP_VERSION', '1.0.2' );
			
			/**
			 * define XStore_AMP_DIR
			 *
			 * @since 1.0.0
			 */
			$this->define( 'XStore_AMP_DIR', plugin_dir_path( __FILE__ ) );
			
			/**
			 * define XStore_AMP_URL
			 *
			 * @since 1.0.0
			 */
			$this->define( 'XStore_AMP_URL', plugin_dir_url( __FILE__ ) );
			
			/**
			 * define XStore_AMP_INCLUDES_PATH
			 *
			 * @since 1.0.0
			 */
			$this->define( 'XStore_AMP_INCLUDES_PATH', XStore_AMP_DIR . 'includes/' );
			
			/**
			 * define XStore_AMP_TEMPLATES_PATH
			 *
			 * @since 1.0.0
			 */
			$this->define( 'XStore_AMP_TEMPLATES_PATH', XStore_AMP_INCLUDES_PATH . 'templates/' );
			
			/**
			 * define XStore_AMP_ADMIN_PATH
			 *
			 * @since 1.0.0
			 */
			$this->define( 'XStore_AMP_ADMIN_PATH', XStore_AMP_DIR . 'admin/' );
			
			/**
			 * define XStore_AMP_FRONTEND_SCRIPTS_URL
			 *
			 * @since 1.0.0
			 */
			$this->define( 'XStore_AMP_FRONTEND_SCRIPTS_URL', XStore_AMP_URL . 'frontend/' );
			
			/**
			 * define XStore_AMP_INCLUDES_PATH
			 *
			 * @since 1.0.0
			 */
			$this->define( 'XStore_AMP_FRONTEND_IMAGES_URL', XStore_AMP_FRONTEND_SCRIPTS_URL . 'images/' );
		}
		
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}
		
		public static function define_vars() {
			global $xstore_amp_scripts;
			global $xstore_amp_scripts_templates;
			global $xstore_amp_styles;
			global $xstore_amp_vars;
			global $xstore_amp_settings;
			global $xstore_amp_fonts;
			global $xstore_amp_custom_fonts;
			
			$specific_key_amp = md5(get_site_url( get_current_blog_id(), '/' ));
			
			$xstore_amp_fonts = array(
				'system' => 'System fonts',
				'Work+Sans' => '"Work Sans", sans-serif',
				'Alegreya'=>'Alegreya, serif',
				'Fira+Sans'=>'"Fira Sans", sans-serif',
				'Lora' => 'Lora, serif',
				'Merriweather' => 'Merriweather, serif',
				'Montserrat'=>'Montserrat, sans-serif',
				'Open+Sans' => '"Open Sans", sans-serif',
				'Playfair+Display' => '"Playfair Display", serif',
				'Roboto' => 'Roboto, sans-serif',
				'Lato' => 'Lato, sans-serif',
				'Cardo' => 'Cardo, serif',
				'Arvo' => 'Arvo, serif',
			);
			
			$xstore_amp_custom_fonts = array();
			$fonts = get_option( 'etheme-fonts', false );
			if ( $fonts ) {
				foreach ( $fonts as $font ) {
					$xstore_amp_custom_fonts[$font['name']] = $font['name'];
				}
			}
			
			$xstore_amp_scripts = array(
				'bind' => 'amp-bind-0.1',
				'sidebar' => 'amp-sidebar-0.1'
			);
			
			$xstore_amp_scripts_templates = array();
			
			$xstore_amp_styles = array(
				'config.php',
				'style.css',
				'icons.php',
				'custom_fonts.php'
			);
			
			$xstore_amp_vars = array(
				'home_url' => ( function_exists('pll_home_url') && function_exists('pll_current_language'))
					? pll_home_url( pll_current_language() ) : home_url(),
				'home_id' => (int)get_option('page_on_front'),
				'blog_id' => (int)get_option( 'page_for_posts' ),
				'is_woocommerce' => class_exists('WooCommerce'),
				'cart_count' => 0,
				'is_mobile' => function_exists('wp_is_mobile') ? wp_is_mobile() : false
			);
			
			$xstore_amp_settings = (array)get_option( 'xstore_amp_settings', array() );
			$xstore_amp_settings_default = array(
				'general' => array(),
				'appearance' => array(
					'font_family' => $xstore_amp_fonts['Roboto']
                ),
				'home_page' => array(),
				'mobile_panel' => array(),
				'footer' => array(),
				'customization' => array()
			);
			foreach ( $xstore_amp_settings_default as $key => $value ) {
				$xstore_amp_settings[ $key ] = ( isset($xstore_amp_settings[$key]) ) ? array_merge($value, $xstore_amp_settings[ $key ]) : $value;
			}
			
			if ( defined('ET_CORE_DIR')) {
				require_once( ET_CORE_DIR . 'app/models/customizer/icons.php' );
				global $et_icons;
				global $et_cart_icons;
				global $xstore_amp_icons;
				$xstore_amp_icons = $et_icons['light'];
			}
			
		}
		
		public function amp_add_query() {
			add_filter( 'post_link', array($this, 'prefix_custom_link_option'), 10, 2 );
			add_filter( 'page_link', array($this, 'prefix_custom_link_option'), 10, 2 );
			add_filter( 'post_type_link', array($this, 'prefix_custom_link_option'), 10, 2 );
			add_filter('term_link', array($this, 'prefix_custom_link_option'), 10, 2 );
			add_filter('post_link_category', array($this, 'prefix_custom_link_option'), 10, 2 );
			add_filter( 'home_url', array($this, 'prefix_custom_link_option'), 10, 2 );
			
		}
		
		public function prefix_custom_link_option($url, $post) {
			return add_query_arg('amp', '1', $url);
		}
		
		/**
		 * Defines amp/no-amp version.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function define_is_amp() {
   
			if ( !headers_sent()) {
			    global $xstore_amp_vars;
				// session_start(); // on test because WP Site Health shows errors with it
                if ( $this->enable_or_disable_takeover(false)) {
	                session_start();
                }
				if ( ! isset( $_SESSION['xstore-amp-'.$this->specific_key_amp] ) ) {
					$_SESSION['xstore-amp-'.$this->specific_key_amp] = $this->enable_or_disable_takeover();
				} else {
					if ( isset( $_GET['no-amp-'.$this->specific_key_amp] ) ) {
						$_SESSION['xstore-amp-'.$this->specific_key_amp] = false;
					} elseif ( isset( $_GET['amp'] ) ) {
						$_SESSION['xstore-amp-'.$this->specific_key_amp] = true;
					}
				}
			}
		}
		
		/**
		 * Function for including backend and frontend files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function includes() {
			
			if ( $this->is_frontend() ) {
				$this->theme_actions();
				if ( $_SESSION['xstore-amp-'.$this->specific_key_amp] ) {
					$this->includes_frontend();
					function is_amp_endpoint() {
						return true;
					}
				}
			}
			$this->includes_backend();
		}
		
		public function includes_frontend() {
			
			add_action( 'wp_loaded', function () {
				global $woocommerce, $xstore_amp_vars;
				$this->cart_count              = WC()->cart->get_cart_contents_count();
				$xstore_amp_vars['cart_count'] = $this->cart_count;
			} );
			
			include_once XStore_AMP_INCLUDES_PATH . 'actions/global.php';
			include_once XStore_AMP_INCLUDES_PATH . 'actions/filters.php';
			include_once XStore_AMP_INCLUDES_PATH . 'actions/ajax-actions.php';
		}
		
		public function includes_backend() {
			add_action('init', function() {
			    include_once XStore_AMP_ADMIN_PATH . 'init.php';
			});
			require_once XStore_AMP_ADMIN_PATH . 'update.php';
		}
		
		public function theme_actions() {
			add_action('after_page_wrapper', function (){
				global $xstore_amp_vars, $xstore_amp_settings;
				if ( ! isset( $xstore_amp_settings['footer']['link_no_amp'] ) || $xstore_amp_settings['footer']['link_no_amp'] ) {
					if ( !$xstore_amp_vars['is_mobile']) return;
					// if ( !isset($_SESSION['xstore-amp'])) return;
                    global $wp; ?>
                    <div class="text-center" style="padding: 20px 0">
                        <a class="btn black big" href="<?php echo esc_url(remove_query_arg('no-amp', add_query_arg('amp', 1, home_url( $wp->request )))); ?>">
							<?php echo esc_html__('View AMP version', 'xstore-amp'); ?>
                        </a>
                    </div>
					<?php
				}
			}, 999);
		}
		
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		public function load_app() {
			
			if ( $this->enable_or_disable_takeover() ) {
				
				add_filter( 'xstore_theme_amp', '__return_true' );
				
				add_action( 'template_redirect', array( $this, 'amp_prepare_render' ), 99 );
				
			}
			
		}
		
		public function is_frontend() {
			return ( ! is_admin() || wp_doing_ajax() ) && ! wp_doing_cron();
		}
		
		public function enable_or_disable_takeover($check_admin = true) {
			global $xstore_amp_vars;
			if ( isset($_GET['no-amp']) ) {
				return false;
			}
			if ( is_customize_preview() ) {
				return false;
			}
			if ( $check_admin ) {
				if ( is_admin() ) {
					return false;
				}
			}
			if ( isset($_GET['amp']) ) {
				return true;
			}
			if ( isset($_SESSION['xstore-amp-'.$this->specific_key_amp]) ) {
				return $_SESSION['xstore-amp-'.$this->specific_key_amp];
			}
			if ( $xstore_amp_vars['is_mobile'] ) {
				return true;
			}
			return false;
		}
		
		function register_sidebars() {
			if(function_exists('register_sidebar')) {
				add_action('after_setup_theme', function(){
					register_sidebar(array(
						'name' => esc_html__('AMP Shop Sidebar', 'xstore-amp'),
						'id' => 'xstore-amp-shop-sidebar',
						'description' => esc_html__('Shop page amp widget area', 'xstore-amp'),
						'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
						'after_widget' => '</div><!-- //sidebar-widget -->',
						'before_title' => apply_filters('etheme_sidebar_before_title', '<h4 class="widget-title"><span>' ),
						'after_title' => apply_filters('etheme_sidebar_after_title', '</span></h4>'),
					));
				}, 99);
			}
		}
		
		public function render_page_fonts() {
			global $xstore_amp_fonts_to_load;
			$this->get_font_family('font_family', 'Roboto');
			$xstore_amp_fonts_to_load = array_unique((array)$xstore_amp_fonts_to_load);
			if ( count($xstore_amp_fonts_to_load)) {
				foreach ( $xstore_amp_fonts_to_load as $font ) {
					if ( $font == 'system' ) continue;
					?>
                    <link href="https://fonts.googleapis.com/css2?family=<?php echo $font; ?>" rel="stylesheet">
				<?php }
			}
		}
		
		/**
		 * Description of the function.
		 *
		 * @param $option_name
		 * @param $default_family_key - should be the same as default value of option
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public function get_font_family($option_name, $default_family_key) {
			global $xstore_amp_fonts, $xstore_amp_fonts_to_load, $xstore_amp_custom_fonts, $xstore_amp_settings, $xstore_amp_vars;
			if ( isset($xstore_amp_settings['appearance']) && isset($xstore_amp_settings['appearance'][$option_name]) ) {
				if ( !isset($xstore_amp_fonts[$xstore_amp_settings['appearance'][$option_name]]) ) {
					if ( isset($xstore_amp_custom_fonts[$xstore_amp_settings['appearance'][$option_name]])) {
						$xstore_amp_vars[$option_name] = $xstore_amp_settings['appearance'][$option_name];
					}
				}
				else {
					$xstore_amp_fonts_to_load[]                 = $xstore_amp_settings['appearance'][$option_name];
					if ( $xstore_amp_settings['appearance'][$option_name] == 'system' ) {
						$xstore_amp_vars[$option_name] = 'system';
					}
					else {
						$xstore_amp_vars[$option_name] = $xstore_amp_fonts[ $xstore_amp_settings['appearance'][$option_name] ];
					}
				}
			}
			else {
				$xstore_amp_fonts_to_load[] = $default_family_key;
				$xstore_amp_vars[$option_name] = $xstore_amp_fonts[$default_family_key];
			}
		}
		
		/**
		 * Render custom page amp-scripts.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function render_custom_page_scripts() {
			global $xstore_amp_scripts, $xstore_amp_scripts_templates;
			if ( count( $xstore_amp_scripts ) ) {
				foreach ( $xstore_amp_scripts as $script_key => $script_value ) { ?>
                    <script async custom-element="amp-<?php echo $script_key; ?>"
                            src="https://cdn.ampproject.org/v0/<?php echo $script_value; ?>.js"></script>
				<?php }
			}
			if ( count( $xstore_amp_scripts_templates ) ) {
				foreach ( $xstore_amp_scripts_templates as $script_key => $script_value ) { ?>
                    <script async custom-template="amp-<?php echo $script_key; ?>"
                            src="https://cdn.ampproject.org/v0/<?php echo $script_value; ?>.js"></script>
				<?php }
			}
		}
		
		public function render_custom_page_preloads() {
			global $xstore_amp_scripts, $xstore_amp_scripts_templates;
			global $xstore_amp_settings;
			$preloads = array();
			$home_elements = array(
				'slider',
				'products_categories_01',
				'products_01',
				'banner_01',
				'posts_01',
				'textarea_block_01'
			);
			if ( isset($xstore_amp_settings['home_page']['page_elements']) && !empty($xstore_amp_settings['home_page']['page_elements']) ) {
				$home_elements = explode(',', $xstore_amp_settings['home_page']['page_elements']);
				foreach ( $home_elements as $element_key => $element_name ) {
					if ( !isset($xstore_amp_settings['home_page'][$element_name.'_visibility']) || !$xstore_amp_settings['home_page'][$element_name.'_visibility'] ) {
						unset($home_elements[$element_key]);
					}
				}
			}
			if ( in_array('slider', $home_elements) && isset($xstore_amp_settings['home_page']['slider_items']) ) {
				$slides = $xstore_amp_settings['home_page']['slider_items'];
				if ( ! empty( $slides ) ) {
					$slides = explode( ',', $slides );
					foreach ( $slides as $slide_name ) {
						if ( !$xstore_amp_settings['home_page'][$slide_name.'_image'] ) continue;
						$preloads[] = array(
							'type' => 'image',
							'href' => $xstore_amp_settings['home_page'][$slide_name.'_image']
						);
					}
				}
			}
			if ( in_array('banner_01', $home_elements) ) {
				if ( isset( $xstore_amp_settings['home_page']['banner_01_image'] ) && ! empty( $xstore_amp_settings['home_page']['banner_01_image'] ) ) {
					$preloads[] = array(
						'type' => 'image',
						'href' => $xstore_amp_settings['home_page']['banner_01_image']
					);
				}
			}
			if (isset($xstore_amp_settings['general']['logo']) && !empty($xstore_amp_settings['general']['logo'])) {
				$preloads[] = array(
					'type' => 'image',
					'href' => $xstore_amp_settings['general']['logo']
				);
			}
			if (isset($xstore_amp_settings['general']['logo_dark']) && !empty($xstore_amp_settings['general']['logo_dark'])) {
				$preloads[] = array(
					'type' => 'image',
					'href' => $xstore_amp_settings['general']['logo_dark']
				);
			}
			?>
            <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/xstore-icons-light.ttf" as="font" type="font/ttf" crossorigin>
            <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/fonts/xstore-icons-light.woff2" as="font" type="font/woff2" crossorigin>
			<?php
			foreach ($preloads as $preload) { ?>
                <link rel="preload" as="<?php echo $preload['type']; ?>" href="<?php echo $preload['href']; ?>" />
			<?php }
			foreach (array_merge($xstore_amp_scripts, $xstore_amp_scripts_templates) as $script_key => $script_value) { ?>
                <link rel="preload" as="script" href="https://cdn.ampproject.org/v0/<?php echo $script_value; ?>.js" />
			<?php }
		}
		
		/**
		 * Function for minify css.
		 *
		 * @param $style
		 * @return string|string[]
		 *
		 * @since 1.0.0
		 *
		 */
		public function minify_css( $style ) {
			$style = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $style );
			$style = str_replace( ': ', ':', $style );
			$style = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $style );
			
			return ($style);
		}
		
		/**
		 * Creates array of css/php files for output on page.
		 *
		 * @param string $css_file
		 * @param string $subfolder
		 * @param string $format
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public static function add_custom_css_action( $css_file = 'style', $subfolder = '', $format = 'css' ) {
			global $xstore_amp_styles;
			if ( $subfolder ) {
				$css_file = $subfolder . '/' . $css_file;
			}
			
			$xstore_amp_styles[] = $css_file . '.' . $format;
		}
		
		/**
		 * Removes specific css/php files from output for page.
		 *
		 * @param $css_file
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public static function remove_custom_css_action($css_file) {
			global $xstore_amp_styles;
			if (($key = array_search( $css_file, $xstore_amp_styles)) !== false) {
				unset($xstore_amp_styles[$key]);
			}
		}
		
		/**
		 * Renders css for page.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function render_custom_page_css() {
			global $xstore_amp_styles, $xstore_amp_settings;
			if ( count( $xstore_amp_styles ) ) {
				ob_start();
				foreach ( $xstore_amp_styles as $css_file ) {
					include_once 'frontend/css/' . $css_file;
				}
				echo $this->minify_css(ob_get_clean());
			}
			
			$this->render_custom_option_css();
		}
		
		/**
		 * Renders custom css from options for page.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function render_custom_option_css() {
			global $xstore_amp_settings;
			if ( isset($xstore_amp_settings['customization']['css']) ) {
				echo $this->minify_css($xstore_amp_settings['customization']['css']);
			}
		}
		
		/**
		 * Main function for template redirects for amp || no-amp versions.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function amp_prepare_render() {
			if ( $this->enable_or_disable_takeover() ) {
				global $xstore_amp_vars, $post;
				// removed because of errors in paginations links
//				$this->amp_add_query();

//				$this->post_id = $post->ID;
//				if ( is_tax() ) {
					$this->post_id = get_queried_object_id();
//				}
//				if ( !isset($_GET['amp'])){
//				    global $wp;
//                    wp_redirect(add_query_arg('amp', '1', home_url( $wp->request )));
//				    exit;
//				}
				$xstore_amp_vars['home_url'] = ( function_exists('pll_home_url') && function_exists('pll_current_language'))
					? pll_home_url( pll_current_language() ) : home_url();
				do_action( 'pre_amp_render_post', $this->post_id );
				$this->post = get_post( $this->post_id );
				$file       = XStore_AMP_TEMPLATES_PATH . 'page.php';
				$this->load_template( $file, $this->post_id, $this->post );
				exit;
			}
		}
		
		/**
		 * Creates amp-img from parameters.
		 *
		 * @param $options (array)
		 *   @param image_id
		 *   @param (string) size
		 *   @param (bool)  lightbox
		 *   @param (string) layout
		 *   @param (array)  attr
		 *   @param (bool)  force_ratio
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public static function render_image( $options ) {
			$options = shortcode_atts(
				array(
					'image_id' => '',
					'size' => 'full',
					'lightbox' => false,
					'layout' => 'responsive',
					'attr' => array(),
					'force_ratio' => false,
					'is_hero' => false
				), $options);
			if ( empty($options['image_id']) ) return;
			
			$image_attr = wp_get_attachment_image_src( $options['image_id'], $options['size'] );
			if ( !isset($image_attr[0])) {
				return;
			}
			$width = 1;
			$height = 1;
			if ( isset($image_attr[1])) {
				$width = $image_attr[1];
			}
			if ( isset($image_attr[2])) {
				$height = $image_attr[2];
			}
			$attributes      = array(
				'title'                   => get_post_field( 'post_title', $options['image_id'] ),
				'data-caption'            => get_post_field( 'post_excerpt', $options['image_id'] ),
				'src' => $image_attr[0],
				'srcset' => wp_get_attachment_image_srcset( $options['image_id'], $options['size'] ),
				'alt'                     => get_post_meta( $options['image_id'], '_wp_attachment_image_alt', true ),
				'height' => $height,
				'width' => $width,
			);
			if ( empty($attributes['srcset']) )
				unset($attributes['srcset']);
			if ($options['lightbox'])
				$attributes['lightbox'] = $options['lightbox'];
			if ( $options['layout'] ) {
				$attributes['layout'] = $options['layout'];
				if ( $options['layout'] == 'fixed-height' ) {
					$attributes['width'] = 'auto';
				}
			}
			if ( $options['force_ratio'] ) {
				$attributes['height'] = $attributes['width'] = 1;
			}
			foreach ( $options['attr'] as $key => $value ) {
				if ( $key == 'loading') continue;
				$attributes[$key] = $value;
			}
			?>
            <amp-img <?php foreach ( $attributes as $attribute => $value ) {
				echo $attribute . '="' . $value . '" ';
			}
			if ( $options['is_hero'] ) { ?>
                data-hero
			<?php }
			?>></amp-img>
			<?php
		}
		
		static function render_amp_img($img_html, $backup_size = array(40,20)) {
			preg_match_all( "#<img(.*?)\\/?>#", $img_html, $img_matches );
			
			foreach ( $img_matches[1] as $key => $img_tag ) {
				preg_match_all( '/(alt|src|width|height|class|title|style)=["\'](.*?)["\']/i', $img_tag, $attribute_matches );
				$attributes = array_combine( $attribute_matches[1], $attribute_matches[2] );
				
				if ( ! array_key_exists( 'width', $attributes ) || ! array_key_exists( 'height', $attributes ) ) {
					$attributes['layout'] = "responsive";
					if ( array_key_exists( 'src', $attributes ) ) {
						list( $width, $height ) = getimagesize( $attributes['src'] );
						if ( $width && $height) {
							$attributes['width']  = $width;
							$attributes['height'] = $height;
						}
						else {
							$attributes['width'] = $backup_size[0];
                            $attributes['height'] = $backup_size[1];
							$attributes['layout'] = "fixed";
						}
					}
				}
				
				$amp_tag = '<amp-img ';
				foreach ( $attributes as $attribute => $val ) {
					$amp_tag .= $attribute . '="' . $val . '" ';
				}
				
				$amp_tag .= '>';
				$amp_tag .= '</amp-img>';
				
				$img_html = str_replace( $img_matches[0][ $key ], $amp_tag, $img_html );
			}
			
			return $img_html;
		}
		
		/**
		 * Parse content and return amp-content ready. Uses for builders to prevent hard code <img>, <section> tags, columns replaces.
		 *
		 * @param $html
		 * @return string|string[]
		 *
		 * @since 1.0.0
		 *
		 */
		static function render_amp_content ($html) {
			
		    $html = self::render_amp_img($html);
			$html = str_replace(
				array('vc_col-sm', 'vc_col-sm-pull', 'vc_col-sm-push', 'vc_col-sm-offset', 'vc_column_container', 'vc_column-inner'),
				array('amp_col', 'amp_col-pull', 'amp_col-push', 'amp_col-offset', 'amp_column_container', 'amp_column-inner'),
				$html);
			
			$html = str_replace(
				array('vc_col-md', 'vc_col-md-pull', 'vc_col-md-push', 'vc_col-md-offset'),
				array('', '', '', ''),
				$html);
			
			$html = str_replace(
				array('vc_col-lg', 'vc_col-lg-pull', 'vc_col-lg-push', 'vc_col-lg-offset'),
				array('', '', '', ''),
				$html);
			
			$html = str_replace(
				array('<section', '</section>'),
				array('<div', '</div>'),
				$html);
			
			$html = str_replace(
				array('elementor-xs-', 'elementor-sm-', 'elementor-md-', 'elementor-col-'),
				array('amp_col-', 'amp_col-', 'amp_col-', 'amp_col-' ),
				$html);
			
			$html = str_replace(
                array('elementor-container'),
				array('elementor-container clearfix'),
				$html
            );
			
			return $html;
		}
		
		/**
		 * Shows header.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function header() {
			$this->add_custom_css_action( 'cart_sidebar', 'woocommerce' );
			include_once XStore_AMP_TEMPLATES_PATH . 'header.php';
		}
		
		public function head() {
			include_once XStore_AMP_TEMPLATES_PATH . 'header/head.php';
		}
		
		/**
		 * Shows footer.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function footer() {
			include_once XStore_AMP_TEMPLATES_PATH . 'footer.php';
		}
		
		/**
		 * Loads template and parts for page on frontend.
		 *
		 * @param $file
		 * @param $post_id
		 * @param $post
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public function load_template($file, $post_id, $post) {
			$file           = apply_filters( 'amp_post_template_file', $file, $post_id, $post );
			$template_parts = apply_filters( 'amp_post_template_parts', array(), $file, $post_id );
			
			$this->header();
			include_once $file;
			if ( count( $template_parts ) ) {
				foreach ( $template_parts as $template_part ) {
					include_once $template_part;
				}
			}
			$this->footer();
		}
		
		/**
		 * Creates navigation/pagination html for archives.
		 *
		 * @param $wp_query
		 * @return string
		 *
		 * @since 1.0.1
		 *
		 */
		function navigation_pagination($wp_query){
		    $out = '<nav class="navigation pagination" role="navigation">';
                $out .= '<div class="nav-links">';
		            $out .= paginate_links( array(
                        'base' => str_replace( 999999999, '%#%', get_pagenum_link( 999999999 ) ),
                        'format' => '?paged=%#%',
                        'current' => max( 1, get_query_var('paged') ),
                        'total' => $wp_query->max_num_pages,
                        'prev_text' => '←',
                        'next_text'=>'→'
                    ) );
                $out .= '</div>';
            $out .= '</div>';
            return $out;
		}
		
		/**
		 * Shows views count for post.
		 *
		 * @param false $id
		 * @param false $echo
		 * @return int
		 *
		 * @since 1.0.0
		 *
		 */
		function get_post_views($id = false, $echo = false) {
			if( ! $id ) $id = get_the_ID();
			$number = get_post_meta( $id, '_et_views_count', true );
			if( empty($number) ) $number = 0;
			
			if ( $echo ) {
				echo '<span class="views-count">' . $number . '</span>';
			} else {
				return $number;
			}
		}
		
		/**
		 * Shows meta of post.
		 *
		 * @param       $ID
		 * @param array $content
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		function post_published_info($ID, $content = array()) {
			
			$content = shortcode_atts( array(
				'author' => true,
				'time' => true,
				'slide_view' => true,
				'views_counter' => true
			), $content );
			
			?>
            <div class="meta-post">
                <time class="entry-date published updated" datetime="<?php echo get_the_time('F j, Y', $ID); ?>"><?php echo get_the_time(get_option('date_format'), $ID); ?></time>
				
				<?php if ( $content['time']) :
					echo esc_html__( 'at', 'xstore-amp' ) . ' ' . get_the_time( get_option( 'time_format' ), $ID);
				endif; ?>
				
				<?php if ( $content['author']) :
					esc_html_e( 'by', 'xstore-amp' );?> <?php the_author_posts_link();
				endif; ?>
				
				<?php if ( $content['views_counter']) : ?>
                    <span class="meta-divider">/</span>
					
					<?php $this->get_post_views( $ID, true ); ?>
				<?php endif; ?>
				<?php
				if(comments_open($ID) && ! post_password_required($ID) ) :?>
                    <span class="meta-divider">/</span>
					<?php if ($ID): ?>
						<?php
						$comments_number = get_comments_number( $ID );
						
						if ($comments_number === 0) {
							$comments_number = '<span>0</span>';
						} elseif($comments_number === 1){
							$comments_number = '<span>1</span>';
						} else{
							$comments_number = '<span>' . $comments_number . '</span>';
						}
						
						printf(
							'<a href="%s" class="post-comments-count">%s</a>',
							get_the_permalink($ID),
							$comments_number
						
						);
						?>
					<?php else: ?>
						<?php comments_popup_link('<span>0</span>','<span>1</span>','<span>%</span>','post-comments-count'); ?>
					<?php endif; ?>
				<?php endif; ?>
            </div>
			<?php
		}
	
		function portfolio_categories($id) {
		 
			$term_list = wp_get_post_terms($id, 'portfolio_category');
			$_i = 0;
			$out = '';
			foreach ($term_list as $value) {
				$_i++;
				$out .= '<a href="'.get_term_link($value, 'portfolio_category').'">';
				$out .= esc_html($value->name);
				$out .= '</a>';
				if($_i != count($term_list))
					$out .= ', ';
			}
			return $out;
		}
		
		/**
		 * Cart count output function.
		 *
		 * @since 1.0.0
		 *
		 * @return false|string
		 */
		public function cartCount() {
			global $xstore_amp_vars;
			ob_start(); ?>
            <span class="inline-flex align-items-center text-center cart-label" [text]="cartCount"><?php echo esc_html($xstore_amp_vars['cart_count']); ?></span>
			<?php return ob_get_clean();
		}
		
		/**
		 * Renders home slider element.
		 *
		 * @param array $carousel_args
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public function home_slider($carousel_args = array()) {
			global $xstore_amp_settings, $xstore_amp_el_settings;
			$slider_slides = array();
			if ( isset($xstore_amp_settings['home_page']['slider_items']) ) {
				$slides = $xstore_amp_settings['home_page']['slider_items'];
				if ( ! empty( $slides ) ) {
					$slides = explode( ',', $slides );
					foreach ( $slides as $slide_name ) {
						if ( !$xstore_amp_settings['home_page'][$slide_name.'_image'] ) continue;
						$slider_slides[ $slide_name ] = array(
							'image_id' => ($xstore_amp_settings['home_page'][$slide_name.'_image']) ? attachment_url_to_postid($xstore_amp_settings['home_page'][$slide_name.'_image']) : '',
							'button_text' => $xstore_amp_settings['home_page'][$slide_name.'_button_text'],
							'button_url' => $xstore_amp_settings['home_page'][$slide_name.'_button_url'],
							'title' => $xstore_amp_settings['home_page'][$slide_name.'_title'],
							'content' => $xstore_amp_settings['home_page'][$slide_name.'_content'],
							'content_width' => $xstore_amp_settings['home_page'][$slide_name.'_content_width'],
							'alignment_x' => $xstore_amp_settings['home_page'][$slide_name.'_alignment_x'],
							'alignment_y' => $xstore_amp_settings['home_page'][$slide_name.'_alignment_y'],
						);
					}
				}
			}
			
			$carousel_args = shortcode_atts(array(
				'arrows'     => false
			), $carousel_args);
			
			?>
            <section class="carousel-wrapper pos-relative" <?php if (isset($xstore_amp_el_settings['space'])) echo 'style="margin-bottom: '.$xstore_amp_el_settings['space'] . '"'; ?>>
                <amp-carousel id="homeSlider01"
				              <?php if ( !$carousel_args['arrows']) { ?>class="no-arrows"<?php } ?>
                              layout="responsive"
                              width="100"
                              type="slides"
                              height="1"
                              on="slideChange:AMP.setState({homeSlider01: {slide: event.index}})">
					<?php
					if ( count($slider_slides)) {
						foreach ( $slider_slides as $slider_slide ) {
							if ( !$slider_slide['image_id']) continue;
							?>
                            <div class="slide flex-item">
								<?php
								$this->render_image(array(
									'image_id' => $slider_slide['image_id']
								));
								?>
                                <div class="caption-wrapper flex align-items-<?php echo $slider_slide['alignment_y']; ?> text-<?php echo $slider_slide['alignment_x']; ?>">
                                    <div class="caption" style="max-width: <?php echo $slider_slide['content_width'] . '%'; ?>">
                                        <h2><?php echo $slider_slide['title']; ?></h2>
                                        <div><?php echo $slider_slide['content']; ?></div>
										<?php if ( $slider_slide['button_text'] ) { ?>
                                            <a href="<?php echo add_query_arg('amp', '1', $slider_slide['button_url']); ?>" class="button">
												<?php echo esc_html($slider_slide['button_text']); ?>
                                            </a>
										<?php } ?>
                                    </div>
                                </div>
                            </div>
							<?php
						}
					}
					else {
						?>
                        <div class="slide flex-item">
                            <amp-img src="<?php echo XStore_AMP_FRONTEND_IMAGES_URL . 'placeholder.png'; ?>"
                                     layout="responsive"
                                     width="1040"
                                     height="584">
                            </amp-img>
                        </div>

                        <div class="slide flex-item">
                            <amp-img src="<?php echo XStore_AMP_FRONTEND_IMAGES_URL . 'placeholder.png'; ?>"
                                     layout="responsive"
                                     width="1040"
                                     height="584">
                            </amp-img>
                        </div>
						<?php
					}
					?>
                </amp-carousel>
                <amp-state id="homeSlider01">
                    <script type="application/json">
                        <?php echo json_encode(array(
							'slide' => 0
						)); ?>
                    </script>
                </amp-state>
				<?php if ( count($slider_slides) ) { if ( count($slider_slides) < 2) return;
					$dot_number = 0;?>
                    <div class="amp-carousel-dots">
						<?php foreach ( $slider_slides as $slider_slide ) {
							if ( !$slider_slide['image_id']) continue;
							?>
                            <span role="slider-pager" tabindex="<?php echo esc_attr($dot_number + 1);?>" on="tap:homeSlider01.goToSlide(index=<?php echo esc_attr($dot_number); ?>)" [class]="homeSlider01.slide == <?php echo esc_attr($dot_number);?> ? 'current' : ''"
							      <?php if ($dot_number == 0) {?>class="current"<?php } ?>></span>
							<?php
							$dot_number++;
						} ?>
                    </div>
				<?php }
				else { ?>
                    <div class="amp-carousel-dots">
                        <span role="slider-pager" tabindex="1" on="tap:homeSlider01.goToSlide(index=0)" [class]="homeSlider01.slide == 0 ? 'current' : ''" class="current"></span>
                        <span role="slider-pager" tabindex="2" on="tap:homeSlider01.goToSlide(index=1)" [class]="homeSlider01.slide == 1 ? 'current' : ''"></span>
                    </div>
				<?php } ?>
            </section>
			<?php
		}
		
		/**
		 * Render of banner element with parameters.
		 *
		 * @param array $args
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public function banner($args = array()) {
			global $xstore_amp_el_settings;
			$args = shortcode_atts(array(
				'image'     => '',
				'title' => esc_html__('Banner title','xstore-amp'),
				'content' => esc_html__('Banner content', 'xstore-amp'),
				'button_text' => esc_html__('Button', 'xstore-amp'),
				'button_url' => '#',
				'height' => 300
			), $args);
			
			$args['image'] = $args['image'] ? attachment_url_to_postid($args['image']) : '';
			
			?>
            <div class="amp-banner" style="max-height: <?php echo esc_attr($args['height']).'px'; ?>; <?php if (isset($xstore_amp_el_settings['space'])) echo 'margin-bottom: '.$xstore_amp_el_settings['space'] . ';'; ?>">
				<?php
				if ( $args['image'] )
					$this->render_image(array(
						'image_id' => $args['image'],
						'size'=> 'medium'
					));
				else { ?>
                    <amp-img src="<?php echo XStore_AMP_FRONTEND_IMAGES_URL . 'placeholder.png'; ?>"
                             layout="responsive"
                             width="520"
                             height="272">
                    </amp-img>
				<?php }
				?>
                <div class="content-wrapper flex align-items-center text-center">
                    <div class="content-inner">
						<?php
						if ( $args['title'] )
							echo '<h2 class="title">'.stripslashes($args['title']).'</h2>';
						
						if ( $args['content'])
							echo '<div class="content">'.do_shortcode(stripslashes($args['content'])).'</div>';
						
						if ( $args['button_text'])
							echo '<a href="'.$args['button_url'].'" class="button small">'.$args['button_text'].'</a>';
						?>
                    </div>
                </div>
            </div>
			<?php
		}
		
		/**
		 * Render of textarea block.
		 *
		 * @param array $args
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public function textarea_block($args = array()) {
			global $xstore_amp_el_settings;
			$args = shortcode_atts(array(
				'title' => esc_html__('About us','xstore-amp'),
				'content' => '<p>Morbi interdum odio sed nisl. Odio malesuada aliquet a egestas nascetur vel.
                    Aliquam vulputate fringilla sed tellus laoreet vitae, cursus maecenas.
                    Ac lacus, molestie molestie venenatis mauris, eu lectus dui.
                    Convallis dolor purus pellentesque gravida feugiat cursus enim condimentum aenean.</p>',
			), $args);
			?>
            <section <?php if (isset($xstore_amp_el_settings['space'])) echo 'style="margin-bottom: '.$xstore_amp_el_settings['space'].'"'; ?>>
				<?php
				if ( $args['title'] )
					echo '<h2 class="widget-title">'.$args['title'].'</h2>';
				if ( $args['content'] )
					echo '<div>'.do_shortcode(stripslashes($args['content'])).'</div>';
				?>
            </section>
			<?php
		}
		
		/**
		 * Creates amp-carousel for post_type with carousel args.
		 *
		 * @param        $posts (array of posts to output)
		 * @param string $post_type (post, product, product_categories, products_array)
		 * @param array  $carousel_args
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public function create_carousel($posts, $post_type = 'post', $carousel_args = array()) {
			global $xstore_amp_args, $xstore_amp_el_settings;
			$carousel_args = shortcode_atts(array(
				'title' => '',
				'title_tag' => 'h2',
				'width' => 450,
				'height' => 1,
				'layout' => 'responsive',
				'role' => 'region',
				'section_class' => '',
				'class'=> '',
				'controls' => 'never',
				'arrows' => false,
				'data-slides' => 2.5,
			), $carousel_args);
			
			if ( !$carousel_args['arrows']) {
				$carousel_args['class'] .= ' no-arrows';
			}
			$carousel_attr = array();
			foreach ( $carousel_args as $key => $value ) {
				if ( in_array($key, array('arrows', 'section_class', 'title_tag')))
					continue;
                elseif ( $key == 'title')
					$key = 'aria-label';
				
				$carousel_attr[] = $key . '="'.strip_tags($value).'"';
			} ?>
            <section class="carousel-wrapper pos-relative <?php echo esc_attr($carousel_args['section_class']); ?>"
				<?php if (isset($xstore_amp_el_settings['space'])) echo 'style="margin-bottom: '.$xstore_amp_el_settings['space'].'"'; ?>>
				<?php
				if ( $carousel_args['title'] ) {
					echo '<'.$carousel_args['title_tag'].' class="widget-title">'.$carousel_args['title'].'</'.$carousel_args['title_tag'].'>';
				}
				?>
                <amp-carousel <?php echo implode(' ', $carousel_attr); ?> data-type="<?php echo esc_attr($post_type); ?>">
					<?php
					switch ($post_type) {
						case 'product':
							while ( $posts->have_posts() ) :
								$posts->the_post();
								global $product;
								if ( ! (empty( $product ) || ! $product->is_visible()) ) { ?>
                                    <div class="flex-item">
										<?php wc_get_template_part( 'content', 'product' ); ?>
                                    </div>
								<?php }
							endwhile;
							wp_reset_postdata();
							break;
						case 'product_categories':
							foreach ( $posts as $post ) {
								$thumb_id = (int)get_term_meta( $post->term_id, 'thumbnail_id', true );
								$term_link = get_term_link($post, 'product_cat'); ?>
                                <li <?php wc_product_cat_class( 'flex-item', $post ); ?>>
                                    <a href="<?php echo esc_url($term_link); ?>">
										<?php
										if ( $thumb_id && $thumb_id > 0) {
											$this->render_image(
												array(
													'image_id' => $thumb_id,
													'size' => 'woocommerce_thumbnail',
													'force_ratio' => true
												)
											);
										}
										else {
											echo wc_placeholder_img();
										}
										?>
                                    </a>
									<?php woocommerce_template_loop_category_title($post); ?>
                                </li>
							<?php }
							wp_reset_postdata();
							break;
						case 'post':
							$xstore_amp_args['excerpt_length'] = 12;
							$xstore_amp_args['info_args']      = array( 'time' => false, 'author' => false );
							$xstore_amp_args['read_more_button'] = false;
							while ( $posts->have_posts() ) :
								$posts->the_post();
								require XStore_AMP_TEMPLATES_PATH . 'content-grid.php';
							endwhile;
							unset($xstore_amp_args['excerpt_length']);
							unset($xstore_amp_args['info_args']);
							unset($xstore_amp_args['read_more_button']);
							wp_reset_postdata();
							break;
						case 'posts_array':
							$xstore_amp_args['excerpt_length'] = 12;
							$xstore_amp_args['info_args']      = array( 'time' => false, 'author' => false );
							$xstore_amp_args['read_more_button'] = false;
							
							foreach ( $posts as $post ) {
								$post_object = get_post( $post->ID );
								
								setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
								
								require XStore_AMP_TEMPLATES_PATH . 'content-grid.php';
							}
							unset($xstore_amp_args['excerpt_length']);
							unset($xstore_amp_args['info_args']);
							unset($xstore_amp_args['read_more_button']);
							wp_reset_postdata();
							break;
						case 'products_array':
							foreach ( $posts as $post ) {
								$post_object = get_post( $post->get_id() );
								
								setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
								
								?>
                                <div class="flex-item">
									<?php wc_get_template_part( 'content', 'product' ); ?>
                                </div>
								<?php
							}
							wp_reset_postdata();
							break;
						default:
							break;
					}
					
					?>
                </amp-carousel>
            </section>
		<?php }
		
		/**
		 * Creates posts carousel with selected parameters.
		 *
		 * @param string $type
		 * @param array  $args
		 * @param array  $carousel_args
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public function get_posts($type = '', $args = array(), $carousel_args = array()) {
			$wp_query_args = self::get_posts_query($type, $args);
			$posts = new \WP_Query( $wp_query_args );
			$carousel_args = shortcode_atts(array(
				'title' => esc_html__( 'Latest posts', 'xstore-amp' ),
				'slides' => '1',
			), $carousel_args);
			if ( $posts->have_posts() ) {
				$this->create_carousel(
					$posts,
					'post',
					array(
						'title' => $carousel_args['title'],
						'data-slides' => $carousel_args['slides'],
					)
				);
			}
		}
		
		/**
		 * Creates wp_query args for getting posts.
		 *
		 * @param $type
		 * @param $args
		 * @return mixed
		 *
		 * @since 1.0.0
		 *
		 */
		public static function get_posts_query($type, $args) {
			$wp_query_args = array(
				'post_type'           => array( 'post' ),
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
				'posts_per_page'      => 12,
				'limit'               => 12,
				'order' => 'DESC'
			);
			switch ( $type ) {
				case 'popular':
					$wp_query_args['orderby']  = 'meta_value_num';
					$wp_query_args['meta_key'] = '_et_views_count';
					break;
				default: // random
					break;
			}
			$wp_query_args = shortcode_atts($wp_query_args, $args);
			return $wp_query_args;
		}
		
		/**
		 * Creates products_categories carousel.
		 *
		 * @param array $args
		 * @param array $carousel_args
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public function get_products_categories($args = array(), $carousel_args = array()) {
			$wp_query_args = self::get_products_categories_query($args);
			$categories = get_categories( $wp_query_args );
			$carousel_args = shortcode_atts(array(
				'title' => esc_html__( 'Product categories', 'xstore-amp' ),
				'slides'=> 3.5,
				'title_tag' => 'h2'
			), $carousel_args);
			if ( count($categories) ) {
				$this->create_carousel(
					$categories,
					'product_categories',
					array(
						'title' => $carousel_args['title'],
						'class'=> 'circle-images',
						'data-slides'=> $carousel_args['slides'],
						'title_tag' => $carousel_args['title_tag']
					)
				);
			}
		}
		
		/**
		 * Creates query for getting products_categories.
		 *
		 * @param array $args
		 * @return mixed
		 *
		 * @since 1.0.0
		 *
		 */
		public static function get_products_categories_query($args = array()){
			$wp_query_args = array(
				'taxonomy'     => 'product_cat',
				'orderby'      => 'name',
				'order'         => 'ASC',
				'number'        => 12,
				'parent'         => '',
				'child_of'      => 0,
				'show_count'   => 0,
				'pad_counts'   => 0,
				'hierarchical' => 0,
				'object_ids'    => null,
				'include'       => array(),
				'exclude'       => array(),
				'exclude_tree'  => array(),
				'title_li'     => '',
				'hide_empty'   => 1,
				'meta_query'    => '',
			);
			return shortcode_atts($wp_query_args, $args);
		}
		
		/**
		 * Creates products carousel.
		 *
		 * @param string $type
		 * @param array  $args
		 * @param array  $carousel_args
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 */
		public function get_products( $type='random', $args = array(), $carousel_args = array()) {
			$wp_query_args = self::get_products_query( $type, $args );
			$products      = new \WP_Query( $wp_query_args );
			$carousel_args = shortcode_atts(array(
				'title' => esc_html__( 'Hot Products', 'xstore-amp' ),
			), $carousel_args);
			if ( $products->have_posts() ) {
				$this->create_carousel(
					$products,
					'product',
					array(
						'title' => $carousel_args['title'],
					)
				);
			}
		}
		
		/**
		 * Creates query for getting specific type of products.
		 *
		 * @param $type (recently_viewed, featured, sale, bestsellings)
		 * @param $args
		 * @return array|mixed
		 *
		 * @since 1.0.0
		 *
		 */
		public static function get_products_query($type, $args) {
			$wp_query_args = array(
				'post_type'           => array( 'product' ),
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
				'posts_per_page'      => 12,
				'limit'               => 12,
				'orderby'             => 'rand',
				'tax_query' => array(
					array(
						'taxonomy'      => 'product_visibility',
						'field'         => 'slug',
						'terms'         => 'exclude-from-catalog',
						'operator'      => 'NOT IN'
					),
					array(
						'taxonomy'      => 'product_visibility',
						'field'         => 'slug',
						'terms'         => 'exclude-from-search',
						'operator'      => 'NOT IN'
					),
				)
			);
			$wp_query_args = shortcode_atts($wp_query_args, $args);
			switch ( $type ) {
				case 'recently_viewed':
					$wp_query_args = self::get_recently_viewed_products_args($wp_query_args);
					break;
				case 'featured':
					$wp_query_args = self::get_featured_products_args($wp_query_args);
					break;
				case 'sale':
					$wp_query_args = self::get_sale_products_args($wp_query_args);
					break;
				case 'bestsellings':
					$wp_query_args = self::get_bestsellings_products_args($wp_query_args);
					break;
				default: // random
					break;
			}
			return $wp_query_args;
		}
		
		/**
		 * Add wp_query args to get recently viewed products.
		 *
		 * @param array $wp_query_args
		 * @return array|mixed
		 *
		 * @since 1.0.0
		 *
		 */
		public static function get_recently_viewed_products_args($wp_query_args=array()) {
			
			$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|',
				wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : array(); // @codingStandardsIgnoreLine
			$viewed_products = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );
			
			if ( ! empty( $viewed_products ) ) {
				$wp_query_args['post__in'] = $viewed_products;
				$wp_query_args['orderby']  = 'post__in';
			}
			return $wp_query_args;
		}
		
		/**
		 * Add wp_query args to get featured products.
		 *
		 * @param array $wp_query_args
		 * @return array|mixed
		 *
		 * @since 1.0.0
		 *
		 */
		public static function get_featured_products_args($wp_query_args=array()) {
			$featured_product_ids            = wc_get_featured_product_ids();
			$wp_query_args['post__in'] = array_merge( array( 0 ), $featured_product_ids );
			return $wp_query_args;
		}
		
		/**
		 * Add wp_query args to get products on sale.
		 *
		 * @param array $wp_query_args
		 * @return array|mixed
		 *
		 * @since 1.0.0
		 *
		 */
		public static function get_sale_products_args($wp_query_args=array()) {
			$product_ids_on_sale             = wc_get_product_ids_on_sale();
			$wp_query_args['post__in'] = array_merge( array( 0 ), $product_ids_on_sale );
			return $wp_query_args;
		}
		
		/**
		 * Add wp_query args to get bestselling products.
		 *
		 * @param array $wp_query_args
		 * @return array|mixed
		 *
		 * @since 1.0.0
		 *
		 */
		public static function get_bestsellings_products_args($wp_query_args=array()) {
			$wp_query_args['meta_key'] = 'total_sales';
			$wp_query_args['orderby']  = 'meta_value_num';
			return $wp_query_args;
		}
		
		/**
		 * AMP-form submitting mustache template.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function form_submitting() {
			ob_start(); ?>
            <div submitting>
                <template type="amp-mustache">
                </template>
            </div>
			<?php echo ob_get_clean();
		}
		
		/**
		 * AMP-form success mustache template.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function form_success() {
			ob_start(); ?><div submit-success class="amp-form-alert"><template type="amp-mustache">{{#success_detail}}<div class="amp-form-status-success"><div class="form-success">
                            <p><i class="et-icon et-checked"></i> {{{success_detail}}}</p>
                            {{#success_detail_button}}
                            {{{success_detail_button}}}
                            {{/success_detail_button}}</div></div>
                    {{/success_detail}}
                    {{#popup_message}}
                    <div class="amp-form-status-success">
                        <div class="form-success-popup">
                            <div class="form-success-popup-content">{{{popup_message}}}</div>
                        </div>
                    </div>{{/popup_message}}</template></div><?php echo ob_get_clean();
		}
		
		/**
		 * AMP-form error mustache template.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function form_error() {
			ob_start(); ?><div submit-error class="amp-form-alert">
                <template type="amp-mustache">
                    <div class="amp-form-status-error">
                        <div class="form-errors-list">{{#errors}}
                            <div class="form-error"><p><i class="et-icon et-exclamation"></i> {{{error_detail}}}</p></div>
                            {{/errors}}</div></div>
                </template></div><?php echo ob_get_clean();
		}
		
	}
	
	$amp = new XStore_AMP();
//	$amp->init();
	add_action('plugins_loaded', array($amp, 'init') );
endif;
