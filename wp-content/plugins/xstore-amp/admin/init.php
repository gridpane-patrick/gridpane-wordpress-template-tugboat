<?php
/**
 * Description
 *
 * @package    init.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

if ( !class_exists('EthemeAdmin')) return;

if ( !method_exists('EthemeAdmin', 'get_instance')) return;

class XStore_AMP_admin {
    
    public $global_admin_class;
	
	public function __construct() {
		
		$this->global_admin_class = EthemeAdmin::get_instance();
		
		$this->global_admin_class->init_vars();
		
		$this->global_admin_class->settings_name = 'xstore_amp_settings';
		
		$this->global_admin_class->xstore_panel_section_settings = get_option( $this->global_admin_class->settings_name, array() );
		
//		delete_option( $this->global_admin_class->settings_name );
		
		add_action( 'admin_menu', array( $this, 'xstore_amp_link' ), 999 );
		
		add_action( 'etheme_last_dashboard_nav_item', array( $this, 'xstore_amp_page_link' ) );
		
		add_action( 'admin_bar_menu', array( $this, 'top_bar_menu' ), 100 );
			
        add_action('admin_init',array($this->global_admin_class,'add_page_admin_settings_scripts'), 1140);
		
		add_action('admin_init',array($this->global_admin_class,'add_page_admin_settings_xstore_icons'), 20);
        
        add_action( 'wp_ajax_xstore_panel_settings_save', array( $this->global_admin_class, 'xstore_panel_settings_save' ) );
		
	}
	
	/**
	 * Add link to xstore submenu page.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function xstore_amp_link() {
		$new_label = '<span style="margin-left: 3px; background: var(--et_admin_green-color, #489c33); letter-spacing: 1px; display: inline-block; text-transform: lowercase; border-radius: 3px; color: #fff; padding: 3px 2px 2px 3px; text-transform: uppercase; font-size: 8px; line-height: 1;">'.esc_html__('new', 'xstore-amp').'</span>';
		add_submenu_page(
			'et-panel-welcome',
			esc_html__( 'AMP XStore', 'xstore-amp' ),
			esc_html__( 'AMP XStore', 'xstore-amp' ),
			'manage_options',
			'et-panel-xstore-amp',
			array( $this, 'xstore_amp_page' )
		);
	}
	
	/**
	 * Section content html.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function xstore_amp_page() {
		
		$theme   = wp_get_theme();
		$version = $theme->get( 'Version' );
		
		if ( is_child_theme() ) {
			$parent  = wp_get_theme( 'xstore' );
			$version = $parent->version;
		}
		
		$version = 'v.' . $version;
		
		ob_start();
		get_template_part( 'framework/panel/templates/page', 'header' );
		get_template_part( 'framework/panel/templates/page', 'navigation' );
		?>

        <div class="et-row etheme-page-content etheme-page-content-amp">
            <h2 class="etheme-page-title etheme-page-title-type-2">⚡ <?php echo esc_html__( 'AMP XStore', 'xstore-amp' ); ?></h2>
            <p class="et-message et-info">
				<?php echo '<strong>' . esc_html__('Welcome to the AMP XStore panel!', 'xstore-amp') . '</strong> &#127881;'; ?>
            </p>
            <ul class="et-filters et-tabs-filters">
                <li class="active" data-tab="general"><?php echo esc_html__( 'General', 'xstore-amp' ); ?></li>
                <li data-tab="appearance"><?php echo esc_html__( 'Appearance', 'xstore-amp' ); ?></li>
                <li data-tab="home_page"><?php echo esc_html__( 'Home Page', 'xstore-amp' ); ?></li>
                <li data-tab="mobile_panel"><?php echo esc_html__( 'Mobile panel', 'xstore-amp' ); ?></li>
                <li data-tab="footer"><?php echo esc_html__( 'Footer', 'xstore-amp' ); ?></li>
                <li data-tab="advanced"><?php echo esc_html__( 'Advanced', 'xstore-amp' ); ?></li>
                <li data-tab="customization"><?php echo esc_html__( 'Custom CSS', 'xstore-amp' ); ?></li>
            </ul>
			<?php $tab_content = 'general'; ?>
            <div class="et-tabs-content active" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <form class="xstore-panel-settings" method="post">
                    <div class="xstore-panel-settings-inner">
						
                        <?php $this->xstore_amp_general_tab($tab_content); ?>
						
                    </div>
                    <button class="et-button et-button-green no-loader"
                            type="submit"><?php echo esc_html__( 'Save changes', 'xstore-amp' ); ?></button>
                </form>
            </div>
	        <?php $tab_content = 'appearance'; ?>
            <div class="et-tabs-content" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <form class="xstore-panel-settings" method="post">
                    <div class="xstore-panel-settings-inner">
				
				        <?php $this->xstore_amp_appearance_tab($tab_content); ?>

                    </div>
                    <button class="et-button et-button-green no-loader"
                            type="submit"><?php echo esc_html__( 'Save changes', 'xstore-amp' ); ?></button>
                </form>
            </div>
	        <?php $tab_content = 'home_page'; ?>
            <div class="et-tabs-content" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <form class="xstore-panel-settings" method="post">
                    <div class="xstore-panel-settings-inner">
				
				        <?php $this->xstore_amp_home_page_tab($tab_content); ?>

                    </div>
                    <button class="et-button et-button-green no-loader"
                            type="submit"><?php echo esc_html__( 'Save changes', 'xstore-amp' ); ?></button>
                </form>
            </div>
	        <?php $tab_content = 'mobile_panel'; ?>
            <div class="et-tabs-content" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <form class="xstore-panel-settings" method="post">
                    <div class="xstore-panel-settings-inner">
				
				        <?php $this->xstore_amp_mobile_panel_tab($tab_content); ?>

                    </div>
                    <button class="et-button et-button-green no-loader"
                            type="submit"><?php echo esc_html__( 'Save changes', 'xstore-amp' ); ?></button>
                </form>
            </div>
	
	        <?php $tab_content = 'footer'; ?>
            <div class="et-tabs-content" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <form class="xstore-panel-settings" method="post">
                    <div class="xstore-panel-settings-inner">
				
				        <?php $this->xstore_amp_footer_tab($tab_content); ?>

                    </div>
                    <button class="et-button et-button-green no-loader"
                            type="submit"><?php echo esc_html__( 'Save changes', 'xstore-amp' ); ?></button>
                </form>
            </div>
	
	        <?php $tab_content = 'advanced'; ?>
            <div class="et-tabs-content" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <form class="xstore-panel-settings" method="post">
                    <div class="xstore-panel-settings-inner">
				
				        <?php $this->xstore_amp_advanced_tab($tab_content); ?>

                    </div>
                    <button class="et-button et-button-green no-loader"
                            type="submit"><?php echo esc_html__( 'Save changes', 'xstore-amp' ); ?></button>
                </form>
            </div>
	
	        <?php $tab_content = 'customization'; ?>
            <div class="et-tabs-content" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <form class="xstore-panel-settings" method="post">
                    <div class="xstore-panel-settings-inner">
				
				        <?php $this->xstore_amp_customization_tab($tab_content); ?>

                    </div>
                    <button class="et-button et-button-green no-loader"
                            type="submit"><?php echo esc_html__( 'Save changes', 'xstore-amp' ); ?></button>
                </form>
            </div>

        </div>
		
		<?php get_template_part( 'framework/panel/templates/page', 'footer' );
		echo ob_get_clean();
	}
	
	public function get_terms( $taxonomies, $with_default = true ) {

		$items = array();
	    if ( $with_default ) {
		    $items[] = esc_html__( 'Select item', 'xstore-amp' );
	    }
		
		// Get the post types.
		$terms = get_terms( $taxonomies );
		
		if ( is_wp_error($terms) ) return $items;
		
		// Build the array.
		foreach ( $terms as $term ) {
			$items[ $term->term_id ] = $term->name . ' (id - ' . $term->term_id . ')';;
		}
		
		return $items;
	}
	
	public function xstore_amp_general_tab($tab_content) {
		
        $this->global_admin_class->xstore_panel_settings_switcher_field(
            $tab_content,
            'sticky_header',
            esc_html__('Sticky header', 'xstore-amp'),
            '',
            true
        );
        
        $this->global_admin_class->xstore_panel_settings_upload_field(
                $tab_content,
                'logo',
                esc_html__( 'Upload custom logo', 'xstore-amp' ),
                'Use this option to upload custom logo for the amp theme.',
                'image',
                'id' );
		
		$this->global_admin_class->xstore_panel_settings_upload_field(
			$tab_content,
			'logo_dark',
			esc_html__( 'Upload custom logo (dark version)', 'xstore-amp' ),
			'Use this option to upload custom logo for the amp theme dark version.',
			'image',
			'id' );
        
        $this->global_admin_class->xstore_panel_settings_upload_field(
            $tab_content,
            'favicon',
            esc_html__( 'Upload favicon', 'xstore-amp' ),
            'Use this option to upload favicon for the amp theme.');
        
        $this->global_admin_class->xstore_panel_settings_select_field(
            $tab_content,
                'menu',
            esc_html__( 'Select menu', 'xstore-amp' ),
            esc_html__('Select menu for mobile sidebar.', 'xstore-amp'),
            $this->get_terms('nav_menu')
        );
        
        $this->global_admin_class->xstore_panel_settings_switcher_field(
            $tab_content,
            'header_search',
            esc_html__('Header search', 'xstore-amp'),
            '',
            true
        );
    
        $this->global_admin_class->xstore_panel_settings_input_text_field(
            $tab_content,
            'search_tags_title',
            esc_html__('Trending searches title', 'xstore-amp'),
            false,
            '',
            esc_html__('Trending searches', 'xstore-amp')
        );
        
    
        $this->global_admin_class->xstore_panel_settings_textarea_field(
            $tab_content,
            'search_tags',
            esc_html__('Search tags', 'xstore-amp'),
            esc_html__('Please, write values with comma separator', 'xstore-amp'),
            'Shirt, Shoes, Cap, Coat, Skirt'
        );
        
        $this->global_admin_class->xstore_panel_settings_sortable_field(
            $tab_content,
            'search_results',
            esc_html__('Search results', 'xstore-amp'),
            '',
            array(
                'products' => array(
                    'name' => esc_html__('Products', 'xstore-amp'),
                ),
                'posts' => array(
                    'name' => esc_html__('Posts', 'xstore-amp'),
                ),
            ),
            array(
                array(
                    'name' => 'header_search',
                    'value' => 'on',
                    'section' => $tab_content,
                    'default' => true
                ),
            )
        );
		
		$this->global_admin_class->xstore_panel_settings_switcher_field(
			$tab_content,
			'back_top',
			esc_html__('Back to top', 'xstore-amp'),
			'',
			true
		);
		
		$this->global_admin_class->xstore_panel_settings_switcher_field(
			$tab_content,
			'dark_version',
			esc_html__('Dark version', 'xstore-amp'),
			''
		);
		
		$this->global_admin_class->xstore_panel_settings_switcher_field(
			$tab_content,
			'dark_light_switcher',
			esc_html__('Dark/Light switcher', 'xstore-amp'),
			''
		);
        
	}
	
	public function xstore_amp_appearance_tab($tab_content) {
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content, 'font_color',
			esc_html__( 'Text color', 'xstore-amp' ),
			'Choose the text color of the amp theme.',
			'#555' );
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content, 'heading_color',
			esc_html__( 'Headings color', 'xstore-amp' ),
			'Choose the headings color of the amp theme.',
			'#222' );
		
		$this->global_admin_class->xstore_panel_settings_select_field(
			$tab_content,
			'font_family',
			esc_html__( 'Select font family', 'xstore-amp' ),
			esc_html__('This font family will be used on your site.', 'xstore-amp'),
			$this->get_fonts_list(),
            'Roboto'
		);
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
            'active_color',
			esc_html__( 'Active color', 'xstore-amp' ),
			'Choose the active color of the amp theme.',
			'#A4004F' );
		
		$this->global_admin_class->xstore_panel_settings_tab_field_start(
		        esc_html__('Buttons', 'xstore-amp')
        );
		
            $this->global_admin_class->xstore_panel_settings_colorpicker_field(
                $tab_content,
                'button_bg_color',
                esc_html__( 'Buttons background color', 'xstore-amp' ),
                'Choose the color for buttons background.',
                '#222222' );
            
            $this->global_admin_class->xstore_panel_settings_colorpicker_field(
                $tab_content,
                'button_color',
                esc_html__( 'Buttons color', 'xstore-amp' ),
                'Choose the color for buttons texts.',
                '#ffffff' );
		
		$this->global_admin_class->xstore_panel_settings_tab_field_end();
		
		$this->global_admin_class->xstore_panel_settings_tab_field_start(
		        esc_html__('Header', 'xstore-amp')
        );

            $this->global_admin_class->xstore_panel_settings_slider_field(
                $tab_content,
                'header_height',
                esc_html__( 'Min-height (px)', 'xstore-amp' ),
                false,
                20,
                140,
                54,
                1,
                'px'
            );

            $this->global_admin_class->xstore_panel_settings_colorpicker_field(
                $tab_content,
                'header_bg_color',
                esc_html__( 'Header background color', 'xstore-amp' ),
                'Choose the background color for header.',
                '#ffffff'
            );

            $this->global_admin_class->xstore_panel_settings_colorpicker_field(
                $tab_content,
                'header_color',
                esc_html__( 'Header color', 'xstore-amp' ),
                'Choose the color for header.',
                '#222222'
            );

		$this->global_admin_class->xstore_panel_settings_tab_field_end();
		
		$this->global_admin_class->xstore_panel_settings_tab_field_start(
			esc_html__('Mobile menu', 'xstore-amp')
		);
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
			'mobile_menu_icon_color',
			esc_html__( 'Mobile menu icon color', 'xstore-amp' ),
			'Choose the mobile menu icon color.',
			'#f1f1f1'
		);
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
			'mobile_menu_icon_bg_color',
			esc_html__( 'Mobile menu icon background color', 'xstore-amp' ),
			'Choose the background color for mobile menu icon.',
			'#f1f1f1'
		);

		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
			'mobile_menu_bg_color',
			esc_html__( 'Mobile menu background color', 'xstore-amp' ),
			'Choose the background color for mobile menu sidebar.',
			'#ffffff'
		);

		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
			'mobile_menu_color',
			esc_html__( 'Mobile menu color', 'xstore-amp' ),
			'Choose the text color for mobile menu.',
			'#222222'
		);

		$this->global_admin_class->xstore_panel_settings_slider_field(
			$tab_content,
			'mobile_menu_content_zoom',
			esc_html__( 'Content zoom (%)', 'xstore-amp' ),
			false,
			50,
			300,
			140,
			1,
			'%'
		);

		$this->global_admin_class->xstore_panel_settings_tab_field_end();
		
		$this->global_admin_class->xstore_panel_settings_tab_field_start(
			esc_html__('Footer', 'xstore-amp')
		);
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
			'footer_bg_color',
			esc_html__( 'Footer background color', 'xstore-amp' ),
			'Choose the background color for footer.',
			'#222'
		);
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
			'footer_color',
			esc_html__( 'Footer color', 'xstore-amp' ),
			'Choose the text color for footer.',
			'#fff'
		);
		
		$this->global_admin_class->xstore_panel_settings_tab_field_end();
		
		$this->global_admin_class->xstore_panel_settings_tab_field_start(
			esc_html__('Copyrights', 'xstore-amp')
		);
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
			'copyrights_bg_color',
			esc_html__( 'Copyrights background color', 'xstore-amp' ),
			'Choose the background color for copyrights.',
			'#222'
		);
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
			'copyrights_color',
			esc_html__( 'Copyrights color', 'xstore-amp' ),
			'Choose the text color for copyrights.',
			'#fff'
		);
		
		$this->global_admin_class->xstore_panel_settings_tab_field_end();
		
	}
	
	public function xstore_amp_home_page_tab($tab_content) {
	    
	    $this->global_admin_class->xstore_panel_settings_sortable_field(
	            $tab_content,
                'page_elements',
                false,
                false,
                array(
                    'slider' => array(
                        'name' => esc_html__('Main slider', 'xstore-amp'),
                        'callbacks' => array(
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_repeater_field'),
		                        'args' => $this->xstore_amp_slider_params($tab_content)
	                        ),
                        )
                    ),
                    'products_categories_01' => array(
                        'name' => esc_html__('Product categories 01', 'xstore-amp'),
                        'callbacks' => array(
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
		                        'args' => array(
			                        $tab_content,
			                        'products_categories_01_title',
			                        esc_html__('Product categories title','xstore-amp'),
                                    false,
                                    false,
                                    esc_html__('Product categories', 'xstore-amp')
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
		                        'args' => array(
			                        $tab_content,
			                        'products_categories_01_order',
			                        esc_html__('Order', 'xstore-amp'),
			                        false,
			                        array(
				                        'ASC' => esc_html__('ASC', 'xstore-amp'),
				                        'DESC' => esc_html__('DESC', 'xstore-amp'),
			                        ),
			                        'DESC',
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_slider_field'),
		                        'args' => array(
			                        $tab_content,
			                        'products_categories_01_limit',
			                        esc_html__( 'Limit', 'xstore-amp' ),
			                        false,
			                        -1,
			                        30,
			                        12,
		                        )
	                        ),
                        )
                    ),
                    'products_categories_02' => array(
	                    'name' => esc_html__('Product categories 02', 'xstore-amp'),
	                    'callbacks' => array(
		                    array(
			                    'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
			                    'args' => array(
				                    $tab_content,
				                    'products_categories_02_title',
				                    esc_html__('Product categories title','xstore-amp'),
				                    false,
				                    false,
				                    esc_html__('Product categories', 'xstore-amp')
			                    )
		                    ),
		                    array(
			                    'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
			                    'args' => array(
				                    $tab_content,
				                    'products_categories_02_order',
				                    esc_html__('Order', 'xstore-amp'),
				                    false,
				                    array(
					                    'ASC' => esc_html__('ASC', 'xstore-amp'),
					                    'DESC' => esc_html__('DESC', 'xstore-amp'),
				                    ),
				                    'DESC',
			                    )
		                    ),
		                    array(
			                    'callback' => array($this->global_admin_class, 'xstore_panel_settings_slider_field'),
			                    'args' => array(
				                    $tab_content,
				                    'products_categories_02_limit',
				                    esc_html__( 'Limit', 'xstore-amp' ),
				                    false,
				                    -1,
				                    30,
				                    12,
			                    )
		                    ),
	                    ),
	                    'visible' => false
                    ),
                    'products_01' => array(
                        'name' => esc_html__('Products 01', 'xstore-amp'),
                        'callbacks' => array(
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
		                        'args' => array(
			                        $tab_content,
			                        'products_01_title',
			                        esc_html__('Products title','xstore-amp'),
			                        false,
			                        false,
			                        esc_html__('Hot Products', 'xstore-amp')
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
		                        'args' => array(
			                        $tab_content,
                                    'products_01_type',
                                    esc_html__('Type', 'xstore-amp'),
                                    false,
                                    array(
                                        'recently_viewed' => esc_html__('Recently viewed', 'xstore-amp'),
                                        'featured' => esc_html__('Featured', 'xstore-amp'),
                                        'sale' => esc_html__('Sale', 'xstore-amp'),
                                        'bestsellings' => esc_html__('Bestsellings', 'xstore-amp'),
                                        'random' => esc_html__('Random', 'xstore-amp')
                                    ),
                                    'random'
                                )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
		                        'args' => array(
			                        $tab_content,
			                        'products_01_order',
			                        esc_html__('Order', 'xstore-amp'),
			                        false,
			                        array(
				                        'ASC' => esc_html__('ASC', 'xstore-amp'),
				                        'DESC' => esc_html__('DESC', 'xstore-amp'),
			                        ),
			                        'DESC',
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_slider_field'),
		                        'args' => array(
			                        $tab_content,
			                        'products_01_limit',
			                        esc_html__( 'Limit', 'xstore-amp' ),
			                        false,
			                        -1,
			                        30,
			                        12,
		                        )
	                        ),
                        )
                    ),
                    'products_02' => array(
	                    'name' => esc_html__('Products 02', 'xstore-amp'),
	                    'callbacks' => array(
		                    array(
			                    'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
			                    'args' => array(
				                    $tab_content,
				                    'products_02_title',
				                    esc_html__('Products title','xstore-amp'),
				                    false,
				                    false,
				                    esc_html__('Hot Products', 'xstore-amp')
			                    )
		                    ),
		                    array(
			                    'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
			                    'args' => array(
				                    $tab_content,
				                    'products_02_type',
				                    esc_html__('Type', 'xstore-amp'),
				                    false,
				                    array(
					                    'recently_viewed' => esc_html__('Recently viewed', 'xstore-amp'),
					                    'featured' => esc_html__('Featured', 'xstore-amp'),
					                    'sale' => esc_html__('Sale', 'xstore-amp'),
					                    'bestsellings' => esc_html__('Bestsellings', 'xstore-amp'),
					                    'random' => esc_html__('Random', 'xstore-amp')
				                    ),
				                    'random'
			                    )
		                    ),
		                    array(
			                    'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
			                    'args' => array(
				                    $tab_content,
				                    'products_02_order',
				                    esc_html__('Order', 'xstore-amp'),
				                    false,
				                    array(
					                    'ASC' => esc_html__('ASC', 'xstore-amp'),
					                    'DESC' => esc_html__('DESC', 'xstore-amp'),
				                    ),
				                    'DESC',
			                    )
		                    ),
		                    array(
			                    'callback' => array($this->global_admin_class, 'xstore_panel_settings_slider_field'),
			                    'args' => array(
				                    $tab_content,
				                    'products_02_limit',
				                    esc_html__( 'Limit', 'xstore-amp' ),
				                    false,
				                    -1,
				                    30,
				                    12,
			                    )
		                    ),
	                    ),
                        'visible' => false
                    ),
                    'posts_01' => array(
                        'name' => esc_html__('Posts', 'xstore-amp'),
                        'callbacks' => array(
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
		                        'args' => array(
			                        $tab_content,
			                        'posts_01_title',
			                        esc_html__('Posts title','xstore-amp'),
			                        false,
			                        false,
			                        esc_html__('Latest posts', 'xstore-amp')
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
		                        'args' => array(
			                        $tab_content,
			                        'posts_01_type',
			                        esc_html__('Type', 'xstore-amp'),
			                        false,
			                        array(
				                        'popular' => esc_html__('Popular', 'xstore-amp'),
				                        'random' => esc_html__('Random', 'xstore-amp')
			                        ),
			                        'random'
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
		                        'args' => array(
			                        $tab_content,
			                        'posts_01_order',
			                        esc_html__('Order', 'xstore-amp'),
			                        false,
			                        array(
				                        'ASC' => esc_html__('ASC', 'xstore-amp'),
				                        'DESC' => esc_html__('DESC', 'xstore-amp'),
			                        ),
			                        'DESC'
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_slider_field'),
		                        'args' => array(
			                        $tab_content,
			                        'posts_01_limit',
			                        esc_html__( 'Limit', 'xstore-amp' ),
			                        false,
			                        -1,
			                        30,
			                        12,
		                        )
	                        ),
                        )
                    ),
                    'banner_01' => array(
                        'name' => esc_html__('Banner', 'xstore-amp'),
                        'callbacks' => array(
                            array(
                                'callback' => array($this->global_admin_class, 'xstore_panel_settings_slider_field'),
                                'args' => array(
                                    $tab_content,
                                    'banner_01_height',
                                    esc_html__( 'Max-Height (px)', 'xstore-amp' ),
                                    false,
                                    100,
                                    500,
                                    300,
                                    1,
                                    'px'
                                )
                            ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_upload_field'),
		                        'args' => array(
			                        $tab_content,
			                        'banner_01_image',
			                        esc_html__( 'Upload image', 'xstore-amp' ),
			                        esc_html__('Use this option to upload banner image', 'xstore-amp')
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
		                        'args' => array(
			                        $tab_content,
			                        'banner_01_title',
			                        esc_html__('Banner title','xstore-amp')
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_textarea_field'),
		                        'args' => array(
			                        $tab_content,
			                        'banner_01_content',
			                        esc_html__('Banner content', 'xstore-amp')
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
		                        'args' => array(
			                        $tab_content,
			                        'banner_01_button_text',
			                        esc_html__('Button text', 'xstore-amp'),
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
		                        'args' => array(
			                        $tab_content,
			                        'banner_01_button_url',
			                        esc_html__('Button url', 'xstore-amp')
		                        )
	                        ),
                        )
                    ),
                    'textarea_block_01' => array(
                        'name' => esc_html__('Textarea block', 'xstore-amp'),
                        'callbacks' => array(
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
		                        'args' => array(
			                        $tab_content,
			                        'textarea_block_01_title',
			                        esc_html__('Title', 'xstore-amp'),
                                    false,
                                    false,
                                    esc_html__('About us', 'xstore-amp')
		                        )
	                        ),
	                        array(
		                        'callback' => array($this->global_admin_class, 'xstore_panel_settings_textarea_field'),
		                        'args' => array(
			                        $tab_content,
			                        'textarea_block_01_content',
			                        esc_html__('Content', 'xstore-amp'),
                                    false,
                                    '<p>Morbi interdum odio sed nisl. Odio malesuada aliquet a egestas nascetur vel.
Aliquam vulputate fringilla sed tellus laoreet vitae, cursus maecenas.
Ac lacus, molestie molestie venenatis mauris, eu lectus dui.
Convallis dolor purus pellentesque gravida feugiat cursus enim condimentum aenean.</p>'
		                        )
	                        )
                        ),
                    ),
        ) );
	}
	
	public function xstore_amp_slider_params($tab_content) {
	    return array(
		    $tab_content,
		    'slider_items',
		    'Slider items',
		    '',
		    array(
			    'slider_items_1' => array(
				    'callbacks' => array(
					    array(
						    'callback' => array($this->global_admin_class, 'xstore_panel_settings_upload_field'),
						    'args' => array(
							    $tab_content,
							    'image',
							    esc_html__('Image','xstore-amp'),
						    )
					    ),
					    array(
						    'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
						    'args' => array(
							    $tab_content,
							    'button_text',
							    esc_html__('Button text','xstore-amp')
						    )
					    ),
					    array(
						    'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
						    'args' => array(
							    $tab_content,
							    'button_url',
							    esc_html__('Button url', 'xstore-amp')
						    )
					    ),
					    array(
						    'callback' => array($this->global_admin_class, 'xstore_panel_settings_textarea_field'),
						    'args' => array(
							    $tab_content,
							    'title',
							    esc_html__('Slide title', 'xstore-amp')
						    )
					    ),
					    array(
						    'callback' => array($this->global_admin_class, 'xstore_panel_settings_textarea_field'),
						    'args' => array(
							    $tab_content,
							    'content',
							    esc_html__('Slide content', 'xstore-amp')
						    )
					    ),
					    array(
						    'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
						    'args' => array(
							    $tab_content,
							    'alignment_x',
							    esc_html__('Content horizontal alignment', 'xstore-amp'),
                                false,
                                array(
                                    'start' => esc_html__('Start', 'xstore-amp'),
                                    'center' => esc_html__('Center', 'xstore-amp'),
                                    'end' => esc_html__('End', 'xstore-amp')
                                ),
                                'center'
						    )
					    ),
					    array(
						    'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
						    'args' => array(
							    $tab_content,
							    'alignment_y',
							    esc_html__('Content vertical alignment', 'xstore-amp'),
							    false,
							    array(
								    'start' => esc_html__('Start', 'xstore-amp'),
								    'center' => esc_html__('Middle', 'xstore-amp'),
								    'end' => esc_html__('End', 'xstore-amp')
							    ),
                                'middle'
						    )
					    ),
					    array(
						    'callback' => array($this->global_admin_class, 'xstore_panel_settings_slider_field'),
						    'args' => array(
							    $tab_content,
							    'content_width',
							    esc_html__('Content max-width (%)', 'xstore-amp'),
							    false,
							    10,
							    100,
							    90,
							    1,
							    '%'
						    )
					    ),
				    ),
			    ),
		    ),
		    array(
			    array(
				    'callback' => array($this->global_admin_class, 'xstore_panel_settings_upload_field'),
				    'args' => array(
					    $tab_content,
					    'image',
					    esc_html__('Image','xstore-amp')
				    )
			    ),
			    array(
				    'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
				    'args' => array(
					    $tab_content,
					    'button_text',
					    esc_html__('Button text','xstore-amp')
				    )
			    ),
			    array(
				    'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
				    'args' => array(
					    $tab_content,
					    'button_url',
					    esc_html__('Button url', 'xstore-amp')
				    )
			    ),
			    array(
				    'callback' => array($this->global_admin_class, 'xstore_panel_settings_textarea_field'),
				    'args' => array(
					    $tab_content,
					    'title',
					    esc_html__('Slide title', 'xstore-amp')
				    )
			    ),
			    array(
				    'callback' => array($this->global_admin_class, 'xstore_panel_settings_textarea_field'),
				    'args' => array(
					    $tab_content,
					    'content',
					    esc_html__('Slide content', 'xstore-amp')
				    )
			    ),
			    array(
				    'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
				    'args' => array(
					    $tab_content,
					    'alignment_x',
					    esc_html__('Content horizontal alignment', 'xstore-amp'),
					    false,
					    array(
						    'start' => esc_html__('Start', 'xstore-amp'),
						    'center' => esc_html__('Center', 'xstore-amp'),
						    'end' => esc_html__('End', 'xstore-amp')
					    ),
				    )
			    ),
			    array(
				    'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
				    'args' => array(
					    $tab_content,
					    'alignment_y',
					    esc_html__('Content vertical alignment', 'xstore-amp'),
					    false,
					    array(
						    'start' => esc_html__('Start', 'xstore-amp'),
						    'center' => esc_html__('Middle', 'xstore-amp'),
						    'end' => esc_html__('End', 'xstore-amp')
					    ),
				    )
			    ),
			    array(
				    'callback' => array($this->global_admin_class, 'xstore_panel_settings_slider_field'),
				    'args' => array(
					    $tab_content,
					    'content_width',
					    esc_html__('Content max-width (%)', 'xstore-amp'),
					    false,
					    10,
					    100,
					    90,
					    1,
					    '%'
				    )
			    ),
		    )
	    );
	}
	
	public function get_fonts_list() {
		global $xstore_amp_fonts, $xstore_amp_custom_fonts;
		return array_merge($xstore_amp_fonts, $xstore_amp_custom_fonts);
    }
	
	public function xstore_amp_mobile_panel_tab($tab_content) {
		$this->global_admin_class->xstore_panel_settings_switcher_field(
			$tab_content,
			'mobile_panel',
			esc_html__('Mobile panel', 'xstore-amp'),
			'',
			true
		);
		
		$this->global_admin_class->xstore_panel_settings_sortable_field(
			$tab_content,
			'mobile_panel_elements',
			esc_html__('Mobile panel elements', 'xstore-amp'),
			'',
			array(
				'home' => array(
					'name' => esc_html__('Home', 'xstore-amp'),
				),
				'shop' => array(
					'name' => esc_html__('Shop', 'xstore-amp'),
				),
				'blog' => array(
					'name' => esc_html__('Blog', 'xstore-amp'),
				),
				'cart' => array(
					'name' => esc_html__('Cart', 'xstore-amp'),
				),
				'account' => array(
					'name' => esc_html__('Account', 'xstore-amp'),
//                        'visible' => false,
				),
				'portfolio' => array(
					'name' => esc_html__('Portfolio', 'xstore-amp'),
					'visible' => false,
				),
				'mobile_menu' => array(
					'name' => esc_html__('Menu', 'xstore-amp'),
					'callbacks' => array(
						array(
							'callback' => array($this->global_admin_class, 'xstore_panel_settings_select_field'),
							'args' => array(
								$tab_content,
								'menu',
								esc_html__( 'Select menu', 'xstore-amp' ),
								esc_html__('Select menu for mobile panel menu.', 'xstore-amp'),
								array('inherit' => 'Inherit from global options') + $this->get_terms('nav_menu', false),
							)
						),
					)
				),
			),
            array(
                array(
                    'name' => 'mobile_panel',
                    'value' => 'on',
                    'section' => 'mobile_panel',
                    'default' => true
                ),
            )
		);
		
		$this->global_admin_class->xstore_panel_settings_tab_field_start(
			esc_html__('Mobile panel styles', 'xstore-amp'),
			array(
				array(
					'name' => 'mobile_panel',
					'value' => 'on',
                    'section' => 'mobile_panel',
                    'default' => true
				),
			)
		);
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
			'mobile_panel_bg_color',
			esc_html__( 'Mobile panel background color', 'xstore-amp' ),
			'Choose the background color for mobile panel.',
			'#ffffff'
		);
		
		$this->global_admin_class->xstore_panel_settings_colorpicker_field(
			$tab_content,
			'mobile_panel_color',
			esc_html__( 'Mobile panel color', 'xstore-amp' ),
			'Choose the color for mobile panel.',
			'#222222'
		);
		
		$this->global_admin_class->xstore_panel_settings_slider_field(
			$tab_content,
			'mobile_panel_content_zoom',
			esc_html__( 'Content zoom (%)', 'xstore-amp' ),
			false,
			50,
			250,
			72,
			1,
			'%'
		);
		
		$this->global_admin_class->xstore_panel_settings_slider_field(
			$tab_content,
			'mobile_panel_height',
			esc_html__( 'Min-height (px)', 'xstore-amp' ),
			false,
			40,
			120,
			58,
			1,
			'px'
		);
		
		$this->global_admin_class->xstore_panel_settings_tab_field_end();
	}
	
	public function xstore_amp_footer_tab($tab_content) {
		
		$this->global_admin_class->xstore_panel_settings_input_text_field(
			$tab_content,
			'title',
			esc_html__('Title', 'xstore-amp'),
			false,
			'',
			esc_html__('Find us', 'xstore-amp')
		);
		
		$this->global_admin_class->xstore_panel_settings_textarea_field(
			$tab_content,
			'content',
			esc_html__('Content', 'xstore-amp'),
			false,
			'<p style="margin-bottom: 10px;"><i class="et-icon et-internet" style="margin-right: 5px;"></i>East 21st Street / 304 New York</p>
<p style="margin-bottom: 10px;"><i class="et-icon et-message" style="margin-right: 5px;"></i>Email: youremail@site.com</p>
<p style="margin-bottom: 10px;"><i class="et-icon et-phone-call" style="margin-right: 5px;"></i>Phone: +1 408 996 1010</p>'
		);
		
	    $this->global_admin_class->xstore_panel_settings_repeater_field(
		    $tab_content,
		    'socials',
		    esc_html__('Socials', 'xstore-amp'),
		    false,
            array(
	            'socials_1' => array(
		            'callbacks' => array(
			            array(
				            'callback' => array($this->global_admin_class, 'xstore_panel_settings_icons_select'),
				            'args' => array(
					            $tab_content,
					            'name',
					            esc_html__( 'Select social', 'xstore-amp' ),
					            false,
					            array(
						            'et_icon-facebook' => 'Facebook',
						            'et_icon-twitter' => 'Twitter',
						            'et_icon-instagram' => 'Instagram',
						            'et_icon-skype' => 'Skype',
						            'et_icon-pinterest' => 'Pinterest',
						            'et_icon-linkedin' => 'Linkedin',
						            'et_icon-whatsapp' => 'Whatsapp',
						            'et_icon-snapchat' => 'Snapchat',
						            'et_icon-etsy' => 'Etsy',
						            'et_icon-tik-tok' => 'Tik-tok',
						            'et_icon-untapped' => 'Untapped',
						            'et_icon-tumblr' => 'Tumblr',
						            'et_icon-youtube' => 'Youtube',
						            'et_icon-telegram' => 'Telegram',
						            'et_icon-vimeo' => 'Vimeo',
						            'et_icon-rss' => 'Rss',
						            'et_icon-vk' => 'Vk',
						            'et_icon-tripadvisor' => 'Tripadvisor',
						            'et_icon-houzz' => 'Houzz',
                                ),
                                'et_icon-facebook'
				            )
			            ),
			            array(
				            'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
				            'args' => array(
					            $tab_content,
					            'link',
					            esc_html__('Link','xstore-amp'),
                                false,
                                false,
                                '#'
				            )
			            ),
            )
                ),
	            'socials_2' => array(
		            'callbacks' => array(
			            array(
				            'callback' => array($this->global_admin_class, 'xstore_panel_settings_icons_select'),
				            'args' => array(
					            $tab_content,
					            'name',
					            esc_html__( 'Select social', 'xstore-amp' ),
					            false,
					            array(
						            'et_icon-facebook' => 'Facebook',
						            'et_icon-twitter' => 'Twitter',
						            'et_icon-instagram' => 'Instagram',
						            'et_icon-skype' => 'Skype',
						            'et_icon-pinterest' => 'Pinterest',
						            'et_icon-linkedin' => 'Linkedin',
						            'et_icon-whatsapp' => 'Whatsapp',
						            'et_icon-snapchat' => 'Snapchat',
						            'et_icon-etsy' => 'Etsy',
						            'et_icon-tik-tok' => 'Tik-tok',
						            'et_icon-untapped' => 'Untapped',
						            'et_icon-tumblr' => 'Tumblr',
						            'et_icon-youtube' => 'Youtube',
						            'et_icon-telegram' => 'Telegram',
						            'et_icon-vimeo' => 'Vimeo',
						            'et_icon-rss' => 'Rss',
						            'et_icon-vk' => 'Vk',
						            'et_icon-tripadvisor' => 'Tripadvisor',
						            'et_icon-houzz' => 'Houzz',
					            ),
					            'et_icon-twitter'
				            )
			            ),
			            array(
				            'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
				            'args' => array(
					            $tab_content,
					            'link',
					            esc_html__('Link','xstore-amp'),
					            false,
					            false,
					            '#'
				            )
			            ),
		            )
	            ),
	            'socials_3' => array(
		            'callbacks' => array(
			            array(
				            'callback' => array($this->global_admin_class, 'xstore_panel_settings_icons_select'),
				            'args' => array(
					            $tab_content,
					            'name',
					            esc_html__( 'Select social', 'xstore-amp' ),
					            false,
					            array(
						            'et_icon-facebook' => 'Facebook',
						            'et_icon-twitter' => 'Twitter',
						            'et_icon-instagram' => 'Instagram',
						            'et_icon-skype' => 'Skype',
						            'et_icon-pinterest' => 'Pinterest',
						            'et_icon-linkedin' => 'Linkedin',
						            'et_icon-whatsapp' => 'Whatsapp',
						            'et_icon-snapchat' => 'Snapchat',
						            'et_icon-etsy' => 'Etsy',
						            'et_icon-tik-tok' => 'Tik-tok',
						            'et_icon-untapped' => 'Untapped',
						            'et_icon-tumblr' => 'Tumblr',
						            'et_icon-youtube' => 'Youtube',
						            'et_icon-telegram' => 'Telegram',
						            'et_icon-vimeo' => 'Vimeo',
						            'et_icon-rss' => 'Rss',
						            'et_icon-vk' => 'Vk',
						            'et_icon-tripadvisor' => 'Tripadvisor',
						            'et_icon-houzz' => 'Houzz',
					            ),
					            'et_icon-instagram'
				            )
			            ),
			            array(
				            'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
				            'args' => array(
					            $tab_content,
					            'link',
					            esc_html__('Link','xstore-amp'),
					            false,
					            false,
					            '#'
				            )
			            ),
		            )
	            ),
            ),
		    array(
			    array(
				    'callback' => array($this->global_admin_class, 'xstore_panel_settings_icons_select'),
				    'args' => array(
					    $tab_content,
					    'name',
					    esc_html__( 'Select social', 'xstore-amp' ),
					    false,
					    array(
						    'et_icon-facebook' => 'Facebook',
						    'et_icon-twitter' => 'Twitter',
						    'et_icon-instagram' => 'Instagram',
						    'et_icon-skype' => 'Skype',
						    'et_icon-pinterest' => 'Pinterest',
						    'et_icon-linkedin' => 'Linkedin',
						    'et_icon-whatsapp' => 'Whatsapp',
						    'et_icon-snapchat' => 'Snapchat',
						    'et_icon-etsy' => 'Etsy',
						    'et_icon-tik-tok' => 'Tik-tok',
						    'et_icon-untapped' => 'Untapped',
						    'et_icon-tumblr' => 'Tumblr',
						    'et_icon-youtube' => 'Youtube',
						    'et_icon-telegram' => 'Telegram',
						    'et_icon-vimeo' => 'Vimeo',
						    'et_icon-rss' => 'Rss',
						    'et_icon-vk' => 'Vk',
						    'et_icon-tripadvisor' => 'Tripadvisor',
						    'et_icon-houzz' => 'Houzz',
					    ),
				    )
			    ),
			    array(
				    'callback' => array($this->global_admin_class, 'xstore_panel_settings_input_text_field'),
				    'args' => array(
					    $tab_content,
					    'link',
					    esc_html__('Link','xstore-amp')
				    )
			    ),
		    )
        );
		
		$this->global_admin_class->xstore_panel_settings_textarea_field(
			$tab_content,
			'copyrights_content',
			esc_html__('Copyrights content', 'xstore-amp'),
			false,
			esc_html__('Ⓒ Created by 8theme - Power Elite ThemeForest Author.', 'xstore-amp')
		);
		
		$this->global_admin_class->xstore_panel_settings_switcher_field(
			$tab_content,
			'link_no_amp',
			esc_html__('Link to non AMP version', 'xstore-amp'),
			'',
			true
		);
	}
	
	public function xstore_amp_advanced_tab($tab_content) {
		$this->global_admin_class->xstore_panel_settings_switcher_field(
			$tab_content,
			'google_analytics',
			esc_html__('Enable Google Analytics', 'xstore-amp'),
			'',
			false
		);
		
		$this->global_admin_class->xstore_panel_settings_input_text_field(
			$tab_content,
			'gtag_id',
			esc_html__('Gtag ID', 'xstore-amp'),
			esc_html__('Please, enter a valid ID', 'xstore-amp'),
			'',
			'',
			array(
				array(
					'name' => 'google_analytics',
					'value' => 'on',
					'section' => 'google_analytics',
					'default' => false
				),
			)
		);
	}
	
	public function xstore_amp_customization_tab($tab_content) {
		$this->global_admin_class->xstore_panel_settings_textarea_field(
			$tab_content,
			'css',
			esc_html__('Custom CSS', 'xstore-amp'),
			false,
			''
		);
    }
	
	/**
	 * Add link to xstore panel dashboard page.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function xstore_amp_page_link() {
		
		printf(
			'<li><a href="%s" class="et-nav%s et-nav-branding">⚡ %s <span style="margin-left: 5px; background: var(--et_admin_green-color, #489c33); letter-spacing: 1px; font-weight: 400; display: inline-block; text-transform: lowercase; border-radius: 3px; color: #fff; padding: 3px 2px 2px 3px; text-transform: uppercase; font-size: 8px; line-height: 1;">new</span></a></li>',
			admin_url( 'admin.php?page=et-panel-xstore-amp' ),
			( $_GET['page'] == 'et-panel-xstore-amp' ) ? ' active' : '',
			esc_html__( 'AMP XStore', 'xstore-amp' )
		);
	}
	
	/**
	 * top_bar_menu.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	function top_bar_menu( $wp_admin_bar ) {
	 
		if ( ! defined( 'ETHEME_CODE_IMAGES' ) || ! current_user_can('manage_options') ) {
			return;
		}
		
//		$wp_admin_bar->add_node( array(
//			'parent' => 'site-name',
//			'id'     => 'xstore-amp-site',
//			'title'  => __( 'Visit AMP Site', 'xstore-amp' ),
//			'href'   => add_query_arg('amp', 1, home_url() ) ,
//		) );

		$new_label = '<span style="margin-left: 3px; background: var(--et_admin_green-color, #489c33); letter-spacing: 1px; display: inline-block; text-transform: lowercase; border-radius: 3px; color: #fff; padding: 3px 2px 2px 3px; text-transform: uppercase; font-size: 8px; line-height: 1;">'.esc_html__('new', 'xstore-amp').'</span>';
		
		$wp_admin_bar->add_node( array(
			'parent' => 'et-top-bar-menu',
			'id'     => 'et-panel-xstore-amp',
			'title'  => esc_html__( 'AMP XStore', 'xstore-amp' ) . $new_label,
			'href'   => admin_url( 'admin.php?page=et-panel-xstore-amp' ),
		) );
	}
	
}

$XStore_AMP_admin = new XStore_AMP_admin();
//$XStore_AMP_admin->init();