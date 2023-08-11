<?php
/**
 * The admin class for the XStore White Label Branding plugin.
 *
 * @package xstore-panel-option
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main admin class for the plugin.
 *
 * @since 7.0.0
 * @version 1.0.1
 */
class XStore_White_Label_Branding_Admin {
	
	/**
	 * Projects.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private $settings = [];
	
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function __construct() {
		
		$this->settings_name = 'xstore_white_label_branding_settings';
		
		$this->settings = get_option( $this->settings_name, array() );
		
		add_action( 'admin_menu', array( $this, 'xstore_panel_white_label_branding_link' ), 999 );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ), 120 );
		
		add_action( 'etheme_last_dashboard_nav_item', array( $this, 'xstore_panel_white_label_branding_page_link' ) );
		
		add_action( 'wp_ajax_et_branding_save_settings', array( $this, 'save_settings' ) );
		
		add_action( 'wp_ajax_et_branding_export_settings', array( $this, 'export_settings' ) );
		
		add_action( 'wp_ajax_et_branding_import_settings', array( $this, 'import_settings' ) );
		
		add_action( 'wp_ajax_et_branding_reset_settings', array( $this, 'reset_settings' ) );
		
		add_action( 'admin_bar_menu', array( $this, 'top_bar_menu' ), 100 );
	}
	
	/**
	 * Add link to xstore panel dashboard page.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function xstore_panel_white_label_branding_page_link() {
		$new_label = '<span style="margin-left: 5px; background: var(--et_admin_red-color, #c62828); letter-spacing: 1px; font-weight: 400; display: inline-block; text-transform: lowercase; border-radius: 3px; color: #fff; padding: 3px 2px 2px 3px; text-transform: uppercase; font-size: 8px; line-height: 1;">' . esc_html__( 'extra', 'xstore-white-label-branding' ) . '</span>';
		
		printf(
			'<li><a href="%s" class="et-nav%s et-nav-branding">%s</a></li>',
			admin_url( 'admin.php?page=et-panel-white-label-branding' ),
			( $_GET['page'] == 'et-panel-white-label-branding' ) ? ' active' : '',
			esc_html__( 'White Label Branding', 'xstore-white-label-branding' ) . $new_label
		);
	}
	
	/**
	 * Add link to xstore submenu page.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function xstore_panel_white_label_branding_link() {
		add_submenu_page(
			'et-panel-welcome',
			esc_html__( 'White Label Branding', 'xstore-white-label-branding' ),
			esc_html__( 'White Label Branding', 'xstore-white-label-branding' ),
			'manage_options',
			'et-panel-white-label-branding',
			array( $this, 'xstore_panel_white_label_branding_page' )
		);
	}
	
	/**
	 * Load css/js for section.
	 *
	 * @return void
	 * @since 1.0.0
     *
     * @version 1.0.1
	 *
	 */
	public function load_scripts() {
		
		global $pagenow;
		
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		if ( strpos($screen_id, 'et-panel-white-label-branding') ) {
			
			wp_enqueue_script( 'jquery-color' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			
			wp_enqueue_media();
			
			wp_enqueue_style( 'xstore_panel_white_label_branding_admin_css', XSTORE_WHITE_LABEL_BRANDING_CSS . 'styles.css' );
			wp_enqueue_script( 'xstore_panel_white_label_branding_admin_js', XSTORE_WHITE_LABEL_BRANDING_JS . 'scripts.min.js', array('wp-color-picker') );
			
			$config = array(
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'resetOptions'     => __( 'All your settings will be reset to default values. Are you sure you want to do this ?', 'xstore-white-label-branding' ),
				'pasteYourOptions' => __( 'Please, paste your options there.', 'xstore-white-label-branding' ),
				'loadingOptions'   => __( 'Loading options', 'xstore-white-label-branding' ) . '...',
				'ajaxError'        => __( 'Ajax error', 'xstore-white-label-branding' ),
			);
			
			wp_localize_script( 'xstore_panel_white_label_branding_admin_js', 'XStoreBrandingConfig', $config );
			
		}
	}
	
	/**
	 * Section content html.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function xstore_panel_white_label_branding_page() {
		
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

        <div class="et-row etheme-page-content etheme-page-content-branding">
            <h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__( 'White Label Branding', 'xstore-white-label-branding' ); ?></h2>
            <p class="et-message et-info">
				<?php echo '<strong>' . esc_html__('Welcome to the White Label Branding panel!', 'xstore-white-label-branding') . '</strong> &#127881;<br/>' .
				           esc_html__('A better experience for both you and anyone else who will see the internal workings of your WordPress project. Once you have made all the changes you can delete the plugin and its settings will be saved and will be activated for your customers. You can also export options and use them in each of your next projects.', 'xstore-white-label-branding'); ?>
            </p>
            <ul class="et-filters et-tabs-filters">
                <li class="active" data-tab="control_panel"><?php echo esc_html__( 'Control Panel', 'xstore-white-label-branding' ); ?></li>
                <li data-tab="customizer"><?php echo esc_html__( 'Theme Options', 'xstore-white-label-branding' ); ?></li>
                <li data-tab="advanced"><?php echo esc_html__( 'Advanced', 'xstore-white-label-branding' ); ?></li>
                <li data-tab="import"><?php echo esc_html__( 'Import/Export', 'xstore-white-label-branding' ); ?></li>
            </ul>
			<?php $tab_content = 'control_panel'; ?>
            <div class="et-tabs-content active" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <form class="xstore-panel-settings" method="post">
                    <div class="xstore-panel-settings-inner">
						
						<?php $this->colorpicker_field_type( $tab_content, 'main_color', esc_html__( 'Main color', 'xstore-white-label-branding' ), 'Choose the background color of the theme dashboard area.', '#A4004F', 'main-color' ); ?>
						
						<?php $this->colorpicker_field_type( $tab_content, 'nav_bg_color', esc_html__( 'Navigation background color', 'xstore-white-label-branding' ), 'Choose the background color of the navigation area of theme dashboard.', '#333', 'nav-bg-color' ); ?>
						
						<?php $this->colorpicker_field_type( $tab_content, 'nav_color', esc_html__( 'Navigation text color', 'xstore-white-label-branding' ), 'Choose the text color of the navigation area of theme dashboard.', '#fff', 'nav-color' ); ?>
						
						<?php $this->upload_field_type( $tab_content, 'logo', esc_html__( 'Upload custom logo for dashboard', 'xstore-white-label-branding' ), 'Use this option to upload custom logo for the theme dashboard.', '.etheme-logo .logo-img', 'dashboard_logo' ); ?>
						
						<?php $this->input_text_field_type( $tab_content, 'theme_version', esc_html__( 'Custom theme version in dashboard', 'xstore-white-label-branding' ), 'Use this option if you want to change the default theme version to custom one.', $version, '.etheme-page-header .theme-version' ); ?>
						
						<?php $this->input_text_field_type( $tab_content, 'label', esc_html__( 'Custom menu label', 'xstore-white-label-branding' ), 'Use this option to change the default theme title in the side WordPress menu.', esc_html( 'XStore', 'xstore-white-label-branding' ), '.toplevel_page_et-panel-welcome .wp-menu-name, #wp-admin-bar-et-top-bar-menu .ab-label span:not([class])' ); ?>
						
						<?php $this->upload_field_type( $tab_content, 'icon', esc_html__( 'Upload custom icon', 'xstore-white-label-branding' ), 'Use this option to upload custom logo before the theme title in the side WordPress menu.', '.toplevel_page_et-panel-welcome .wp-menu-image img, #wp-admin-bar-et-top-bar-menu .ab-label img', 'top_bar_logo_src' ); ?>
						
						
						<?php $this->textarea_field_type( $tab_content, 'welcome_text', esc_html__( 'Welcome to dashboard text', 'xstore-white-label-branding' ), 'Use this option to change the welcome title on the Welcome tab of theme dashboard.' ); ?>
						
						<?php $this->textarea_field_type( $tab_content, 'thank_you_text', esc_html__( 'Thank you text', 'xstore-white-label-branding' ), 'Use this option to change the thank you text below the welcome title on the Welcome tab of theme dashboard.' ); ?>
						
						<?php $this->switcher_field_type( $tab_content, 'hide_registration_form', esc_html__( 'Hide registration form', 'xstore-white-label-branding' ), 'Use this option to hide registration form on the Welcome tab of theme dashboard.' ); ?>
						
						<?php $this->switcher_field_type( $tab_content, 'hide_buy_license', esc_html__( 'Hide buy license text ', 'xstore-white-label-branding' ), 'Use this option to hide license area on the Welcome tab of theme dashboard.' ); ?>

						<?php $this->switcher_field_type( $tab_content, 'hide_updates', esc_html__( 'Hide update notices ', 'xstore-white-label-branding' ), 'Use this option to hide all update notices of "XStore" theme and "XStore core" plugin.' ); ?>

						<?php $this->multicheckbox_field_type( $tab_content, 'admin_panel_links', esc_html__( 'Admin panel links', 'xstore-white-label-branding' ), 'Use this option to enable/disable navigation tabs of theme dashboard.' ); ?>

                    </div>
                    <button class="et-button et-button-green no-loader"
                            type="submit"><?php echo esc_html__( 'Save changes', 'xstore-white-label-branding' ); ?></button>
                </form>
            </div>
			<?php $tab_content = 'customizer'; ?>
            <div class="et-tabs-content" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <form class="xstore-panel-settings" method="post">

                    <div class="xstore-panel-settings-inner">
						
						<?php $this->upload_field_type( $tab_content, 'logo', esc_html__( 'Upload custom logo for customizer header', 'xstore-white-label-branding' ), 'Use this option to upload custom logo for the Theme Options in the Customizer.' ); ?>
						
						<?php $this->colorpicker_field_type( $tab_content, 'main_color', esc_html__( 'Main color', 'xstore-white-label-branding' ), 'Use this option to change background color for the Theme Options in the Customizer.' ); ?>

                    </div>

                    <button class="et-button et-button-green no-loader"
                            type="submit"><?php echo esc_html__( 'Save changes', 'xstore-white-label-branding' ); ?></button>
                </form>
            </div>
			<?php $tab_content = 'advanced'; ?>
            <div class="et-tabs-content" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <form class="xstore-panel-settings" method="post">
                    <div class="xstore-panel-settings-inner">
						
						<?php $this->upload_field_type( $tab_content, 'screenshot', esc_html__( 'Upload custom screenshot of theme', 'xstore-white-label-branding' ), 'Recomended size 880x660' ); ?>
						
						<?php $this->textarea_field_type( $tab_content, 'admin_css', esc_html__( 'Custom css for admin backend', 'xstore-white-label-branding' ), 'Use this option to add custom CSS to change styles for the theme dashboard.', true ); ?>
                    </div>
                    <button class="et-button et-button-green no-loader"
                            type="submit"><?php echo esc_html__( 'Save changes', 'xstore-white-label-branding' ); ?></button>
                </form>
            </div>
			<?php $tab_content = 'import'; ?>
            <div class="et-tabs-content" data-tab-content="<?php echo esc_attr( $tab_content ); ?>">
                <div class="xstore-panel-settings-inner">

                    <div class="et-panel-loader"></div>
					
					<?php $this->import_field_type(); ?>
					
					<?php $this->export_field_type(); ?>

                </div>
            </div>
        </div>
		
		<?php get_template_part( 'framework/panel/templates/page', 'footer' );
		echo ob_get_clean();
	}
	
	/**
	 * Upload media field type.
	 *
	 * @param string $section
	 * @param string $setting
	 * @param string $setting_title
	 * @param string $setting_descr
	 * @param string $js_selector
	 * @param string $js_img_var
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 */
	public function upload_field_type( $section = '', $setting = '', $setting_title = '', $setting_descr = '', $js_selector = '', $js_img_var = '' ) {
		
		$settings = $this->settings;

//		$setting = $this->slug . '_' . $setting;
		
		ob_start(); ?>
        <div class="xstore-panel-option xstore-panel-option-upload">
            <div class="xstore-panel-option-title">

                <h4><?php echo esc_html( $setting_title ); ?>:</h4>
				
				<?php if ( $setting_descr ) : ?>
                    <p class="description"><?php echo esc_html( $setting_descr ); ?></p>
				<?php endif; ?>

            </div>
            <div class="xstore-panel-option-input">
                <div class="<?php echo esc_attr( $setting ); ?>_preview xstore-panel-option-file-preview">
					<?php
					if ( ! empty( $settings[ $section ][ $setting ] ) ) {
						echo '<img src="' . esc_url( $settings[ $section ][ $setting ] ) . '" />';
					}
					?>
                </div>
                <div class="file-upload-container">
                    <div class="upload-field-input">
                        <input type="text" id="<?php echo esc_html( $setting ); ?>"
                               name="<?php echo esc_html( $setting ); ?>" class="image-field"
                               value="<?php echo ( isset( $settings[ $section ][ $setting ] ) ) ? esc_html( $settings[ $section ][ $setting ] ) : ''; ?>"
						       <?php if ( $js_selector ) : ?>data-js-selector="<?php echo esc_attr( $js_selector ); ?>"<?php endif; ?>
							<?php if ( $js_img_var ) : ?> data-js-img-var="<?php echo esc_attr( $js_img_var ); ?>" <?php endif; ?>/>
                    </div>
                    <div class="upload-field-buttons">
                        <input type="button"
                               data-title="<?php esc_html_e( 'Login Screen Background Image', 'xstore-white-label-branding' ); ?>"
                               data-button-title="<?php esc_html_e( 'Use Image', 'xstore-white-label-branding' ); ?>"
                               data-image-id="<?php echo esc_html( $setting ); ?>"
                               class="et-button et-button-dark-grey no-loader button-upload-image button-default"
                               value="<?php esc_html_e( 'Upload Image', 'xstore-white-label-branding' ); ?>"/>
                        <input type="button"
                               data-option-name="<?php echo esc_html( $setting ); ?>"
                               class="et-button et-button-semiactive no-loader button-remove-image button-default <?php echo ( ! isset( $settings[ $section ][ $setting ] ) || '' === $settings[ $section ][ $setting ] ) ? 'hidden' : ''; ?>"
                               value="<?php esc_html_e( 'Remove Image', 'xstore-white-label-branding' ); ?> "/>
                    </div>
                </div>
            </div>
        </div>
		<?php echo ob_get_clean();
	}
	
	/**
	 * Textarea/codeeditor field type..
	 *
	 * @param string $section
	 * @param string $setting
	 * @param string $setting_title
	 * @param string $setting_descr
	 * @param bool   $codeeditor // not working with saving correct
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 */
	public function textarea_field_type( $section = '', $setting = '', $setting_title = '', $setting_descr = '', $codeeditor = false ) {
		
		global $allowedposttags;
		
		$settings = $this->settings;

//		$setting = $this->slug . '_' . $setting;
		
		ob_start(); ?>

        <div class="xstore-panel-option xstore-panel-option-code-editor">
            <div class="xstore-panel-option-title">

                <h4><?php echo esc_html( $setting_title ); ?>:</h4>
				
				<?php if ( $setting_descr ) : ?>
                    <p class="description"><?php echo esc_html( $setting_descr ); ?></p>
				<?php endif; ?>

            </div>
            <div class="xstore-panel-option-input">
                                <textarea id="<?php echo $setting; ?>" name="<?php echo $setting; ?>"
                                          style="width: 100%; height: 120px;"
                                          class="regular-textarea"><?php echo ( isset( $settings[ $section ][ $setting ] ) ) ? wp_kses( $settings[ $section ][ $setting ], $allowedposttags ) : ''; ?></textarea>
				<?php
				// Enqueue code editor and settings for manipulating CSS.
				//				if ( $codeeditor && function_exists( 'wp_enqueue_code_editor' ) ) {
				//					$code_editor_settings = wp_enqueue_code_editor( array( 'type' => 'text/css', 'codemirror' =>  ) );
				//
				//					wp_add_inline_script(
				//						'code-editor',
				//						sprintf(
				//							'jQuery( function() { wp.codeEditor.initialize( "' . $setting . '", %s );  } );',
				//							wp_json_encode( $code_editor_settings )
				//						)
				//					);
				//
				//				}
				?>
            </div>
        </div>
		
		<?php echo ob_get_clean();
	}
	
	/**
	 * Input [text] field type.
	 *
	 * @param string $section
	 * @param string $setting
	 * @param string $setting_title
	 * @param string $setting_descr
	 * @param string $placeholder
	 * @param string $js_selector
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 */
	public function input_text_field_type( $section = '', $setting = '', $setting_title = '', $setting_descr = '', $placeholder = '', $js_selector = '' ) {
		
		$settings = $this->settings;

//		$setting = $this->slug . '_' . $setting;
		
		ob_start(); ?>

        <div class="xstore-panel-option xstore-panel-option-input">
            <div class="xstore-panel-option-title">

                <h4><?php echo esc_html( $setting_title ); ?>:</h4>
				
				<?php if ( $setting_descr ) : ?>
                    <p class="description"><?php echo esc_html( $setting_descr ); ?></p>
				<?php endif; ?>

            </div>
            <div class="xstore-panel-option-input">
                <input type="text" id="<?php echo $setting; ?>" name="<?php echo $setting; ?>"
                       placeholder="<?php echo esc_attr( $placeholder ); ?>"
                       value="<?php echo ( isset( $settings[ $section ][ $setting ] ) ) ? esc_attr( $settings[ $section ][ $setting ] ) : ''; ?>"
				       <?php if ( $js_selector ) : ?>data-js-selector="<?php echo esc_attr( $js_selector ); ?>" <?php endif; ?>>
            </div>
        </div>
		
		<?php echo ob_get_clean();
	}
	
	/**
	 * Switcher field type.
	 *
	 * @param string $section
	 * @param string $setting
	 * @param string $setting_title
	 * @param string $setting_descr
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 */
	public function switcher_field_type( $section = '', $setting = '', $setting_title = '', $setting_descr = '' ) {
		
		$settings = $this->settings;

//		$setting = $this->slug . '_' . $setting;
		
		ob_start(); ?>

        <div class="xstore-panel-option xstore-panel-option-switcher">
            <div class="xstore-panel-option-input">
                <h4>
                    <label for="<?php echo $setting; ?>">
						<?php echo esc_html( $setting_title ); ?>:
                        <input class="screen-reader-text" id="<?php echo $setting; ?>" name="<?php echo $setting; ?>"
                               type="checkbox"
							<?php echo ( isset( $settings[ $section ][ $setting ] ) && $settings[ $section ][ $setting ] ) ? 'checked' : ''; ?>>
                        <span class="switch"></span>
                    </label>
                </h4>
            </div>
            <div class="xstore-panel-option-title">
				<?php if ( $setting_descr ) : ?>
                    <p class="description"><?php echo esc_html( $setting_descr ); ?></p>
				<?php endif; ?>
            </div>
        </div>
		
		<?php echo ob_get_clean();
	}
	
	/**
	 * Multicheckbox field type.
	 *
	 * @param string $section
	 * @param string $setting
	 * @param string $setting_title
	 * @param string $setting_descr
	 * @param string $type
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 */
	public function multicheckbox_field_type( $section = '', $setting = '', $setting_title = '', $setting_descr = '', $type = 'page' ) {
		
		$settings = $this->settings;

//		$setting = $this->slug . '_' . $setting;
		
		$elements = array(
			'welcome' => esc_html__('Welcome', 'xstore-white-label-branding'),
			'system_requirements' => esc_html__('System Requirements', 'xstore-white-label-branding'),
			'demos' => esc_html__('Import Demos', 'xstore-white-label-branding'),
			'plugins' => esc_html__('Plugin Installer', 'xstore-white-label-branding'),
			'customize' => esc_html__('Theme Options', 'xstore-white-label-branding'),
			'email_builder' => esc_html__('Built-in Email Builder', 'xstore-white-label-branding'),
			'sales_booster' => esc_html__('Sales Booster', 'xstore-white-label-branding'),
			'custom_fonts' => esc_html__('Custom Fonts', 'xstore-white-label-branding'),
			'maintenance_mode' => esc_html__('Maintenance Mode', 'xstore-white-label-branding'),
            'patcher' => esc_html__('Patcher', 'xstore-white-label-branding'),
            'open_ai' => esc_html__('ChatGPT (Open AI)', 'xstore-white-label-branding'),
			'social' => esc_html__('Instagram API', 'xstore-white-label-branding'),
			'support' => esc_html__('Support', 'xstore-white-label-branding'),
			'changelog' => esc_html__('Changelog', 'xstore-white-label-branding'),
			'sponsors' => esc_html__('Sponsors links', 'xstore-white-label-branding'),
		);
		
		ob_start(); ?>

        <div class="xstore-panel-option xstore-panel-option-multicheckbox" data-type="panel_pages">
            <div class="xstore-panel-option-title">

                <h4><?php echo esc_html( $setting_title ); ?>:</h4>
				
				<?php if ( $setting_descr ) : ?>
                    <p class="description"><?php echo esc_html( $setting_descr ); ?></p>
				<?php endif; ?>

            </div>
	        
            <div class="xstore-panel-option-input">
				<?php foreach ( $elements as $key => $val) {
					$key = $type . '_' . $key; ?>
                    <label for="<?php echo esc_attr($key); ?>">
                        <input id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>"
                               type="checkbox"
							<?php echo ( !isset($settings[ $section ]) || ( isset( $settings[ $section ][ $key ] ) && $settings[ $section ][ $key ] ) ) ? 'checked' : ''; ?>>
						<?php echo esc_attr( $val ); ?>
                    </label>
				<?php } ?>
            </div>
        </div>
		
		<?php echo ob_get_clean();
	}
	
	/**
	 * Colorpicker field type.
	 *
	 * @param string $section
	 * @param string $setting
	 * @param string $setting_title
	 * @param string $setting_descr
	 * @param string $default
	 * @param string $config_var
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 */
	public function colorpicker_field_type( $section = '', $setting = '', $setting_title = '', $setting_descr = '', $default = '', $config_var = '' ) {
		
		$settings = $this->settings;

//		$setting = $this->slug . '_' . $setting;
		
		ob_start(); ?>

        <div class="xstore-panel-option xstore-panel-option-color">
            <div class="xstore-panel-option-title">

                <h4><?php echo esc_html( $setting_title ); ?>:</h4>
				
				<?php if ( $setting_descr ) : ?>
                    <p class="description"><?php echo esc_html( $setting_descr ); ?></p>
				<?php endif; ?>

            </div>
            <div class="xstore-panel-option-input">
                <input type="text" data-alpha="true" id="<?php echo $setting; ?>" name="<?php echo $setting; ?>"
                       class="color-field color-picker"
                       value="<?php echo ( isset( $settings[ $section ][ $setting ] ) ) ? esc_attr( $settings[ $section ][ $setting ] ) : ''; ?>"
				       <?php if ( $default ) : ?>data-default="<?php echo esc_attr($default); ?>"<?php endif; ?>
                       data-css-var="<?php echo esc_attr( $config_var ); ?>"/>
            </div>
        </div>
		
		<?php echo ob_get_clean();
	}
	
	/**
	 * Import field type.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function import_field_type() {
		$settings = $this->settings;

//		$setting = $this->slug . '_import';
		
		$setting = 'import';
		
		ob_start(); ?>

        <div class="xstore-panel-option xstore-panel-option-input">
            <div class="xstore-panel-option-title">

                <h4><?php echo esc_html__( 'Import', 'xstore-white-label-branding' ); ?>:</h4>

                <p class="description"><?php esc_html__( 'You can import ET-Branding options from other site created with xstore', 'xstore-white-label-branding' ); ?></p>

            </div>
            <div class="xstore-panel-option-input">
                <form class="xstore-panel-import-settings" method="post">
                    <textarea type="text" id="<?php echo $setting; ?>" name="<?php echo $setting; ?>" rows="5"></textarea>
                    <br/>
                    <br/>
                    <button type="submit" class="et-button no-loader et-button-green"><?php echo esc_html__( 'Save changes', 'xstore-white-label-branding' ); ?></button>
                    <span class="et-button et-button-dark-grey no-loader" id="reset_settings"><?php esc_html_e('Reset options', 'xstore-white-label-branding'); ?></span>
                </form>
            </div>
        </div>
		
		<?php echo ob_get_clean();
	}
	
	/**
	 * Export field type.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function export_field_type() {
		$settings = $this->settings;

//		$setting = $this->slug . '_export';
		
		$setting = 'export';
		
		ob_start(); ?>

        <div class="xstore-panel-option xstore-panel-option-input">
            <div class="xstore-panel-option-title">

                <h4><?php echo esc_html__( 'Export', 'xstore-white-label-branding' ); ?>:</h4>

                <p class="description"><?php esc_html__( 'You can export ET-Branding options from your site and import them on other one', 'xstore-white-label-branding' ); ?></p>

            </div>
            <div class="xstore-panel-option-input">
                <form class="xstore-panel-export-settings" method="post">
                    <textarea type="text" id="<?php echo esc_attr( $setting ); ?>"
                              name="<?php echo esc_attr( $setting ); ?>" rows="5"><?php echo esc_html__( 'Loading options ...', 'xstore-white-label-branding' ); ?></textarea>
                    <br/>
                    <br/>
                </form>
            </div>
        </div>
		
		<?php echo ob_get_clean();
	}
	
	/**
	 * Save settings
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	function save_settings() {
		$all_settings            = $this->settings;
		$local_settings          = isset( $_POST['settings'] ) ? $_POST['settings'] : array();
		$local_settings_key      = isset( $_POST['type'] ) ? $_POST['type'] : 'general';
		$updated                 = false;
		$local_settings_parsed   = array();
		
		foreach ( $local_settings as $setting ) {
			$local_settings_parsed[ $local_settings_key ][ $setting['name'] ] = $setting['value'];
		}
		
		$all_settings = array_merge( $all_settings, $local_settings_parsed );
		
		update_option( $this->settings_name, $all_settings );
		$updated = true;
		
		$this_response['response'] = array(
			'msg'  => '<h4 style="margin-bottom: 15px;">' . ( ( $updated ) ? esc_html__( 'Settings successfully saved!', 'xstore-white-label-branding' ) : esc_html__( 'Settings saving error!', 'xstore-white-label-branding' ) ) . '</h4>',
			'icon' => ( $updated ) ? '<img src="' . ETHEME_BASE_URI . ETHEME_CODE . 'assets/images/success-icon.png" alt="installed icon" style="margin-top: 15px;"><br/><br/>' : '',
		);
		
		wp_send_json( $this_response );
	}
	
	/**
	 * Import settings.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	function import_settings() {
		$settings        = isset( $_POST['settings'] ) ? $_POST['settings'] : $this->settings;
		$settings_parsed = array();
		
		foreach ( $settings as $key => $value ) {
			foreach ( $value as $setting_key => $setting_value ) {
				$settings_parsed[ $setting_key ] = $setting_value;
			}
		}
		
		update_option( $this->settings_name, $settings_parsed );
		
		$this_response['response'] = array(
			'msg'  => '<h4 style="margin-bottom: 15px;">' . esc_html__( 'Settings successfully saved!', 'xstore-white-label-branding' ) . '</h4>',
			'icon' => '<img src="' . ETHEME_BASE_URI . ETHEME_CODE . 'assets/images/success-icon.png" alt="installed icon" style="margin-top: 15px;"><br/><br/>',
		);
		
		wp_send_json( $this_response );
	}
	
	/**
	 * Reset settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function reset_settings() {
		
		delete_option( $this->settings_name );
		
		$this_response['response'] = array(
			'msg'  => '<h4 style="margin-bottom: 15px;">' . esc_html__( 'Settings successfully reset!', 'xstore-white-label-branding' ) . '</h4>',
			'icon' => '<img src="' . ETHEME_BASE_URI . ETHEME_CODE . 'assets/images/success-icon.png" alt="installed icon" style="margin-top: 15px;"><br/><br/>',
		);
		
		wp_send_json( $this_response );
	}
	
	/**
	 * Export settings.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	function export_settings() {
		$settings              = isset( $_POST['form'] ) ? $_POST['form'] : array();
		$type                  = isset( $_POST['type'] ) ? $_POST['type'] : 'general';
		$local_settings_parsed = array();
		
		foreach ( $settings as $setting ) {
			$local_settings_parsed[ $type ][ $setting['name'] ] = $setting['value'];
		}
		
		wp_send_json( $local_settings_parsed );
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
		$wp_admin_bar->add_node( array(
			'parent' => 'et-top-bar-menu',
			'id'     => 'et-panel-white-label-branding',
			'title'  => esc_html__( 'White Label Branding', 'xstore-white-label-branding' ),
			'href'   => admin_url( 'admin.php?page=et-panel-white-label-branding' ),
		) );
	}
}

new XStore_White_Label_Branding_Admin();
