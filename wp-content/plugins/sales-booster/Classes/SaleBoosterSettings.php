<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'SaleBoosterSettings' ) ) :

	/**
	 * Settings class
	 *
	 * @since 2.0.5
	 */
class SaleBoosterSettings extends WC_Settings_Page {
	
	/**
	 * Setup settings class
	 *
	 * @since  2.0.5
	 */

	public static $getFluentFormsOptions = [];
	public static $getFluentFormInstallUrl = [];

	public function __construct() {
	
		$this->id    = 'sale_booster';
		$this->label = __( 'Sale Booster', 'sale_booster' );
		
		self::$getFluentFormsOptions   = SaleBooster\Classes\ProductSettings::getFluentFormsOptions();
		self::$getFluentFormInstallUrl = SaleBooster\Classes\ProductSettings::sales_get_fluentFormInstallUrl();
		
		add_filter( 'woocommerce_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'woocommerce_sections_' . $this->id,      array( $this, 'output_sections' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueueScripts' ), 20 );
	}
	
	public function adminEnqueueScripts() {
		wp_enqueue_media();
		wp_enqueue_script("sale_booster_settings_js", SALE_BOOSTER_PLUGIN_DIR_URL . "src/admin/js/sale_booster_settings.js", array('jquery'), SALE_BOOSTER_PLUGIN_DIR_VERSION, true);
	}

	
	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			'' => __( 'Look Customizations', 'sale_booster' ),
			'home_ad_settings'   => __( 'Home And Ad Settings', 'sale_booster')
		);
		
		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

    
	/**
	 * Get settings array
	 *
	 * @since 2.0.5
	 * @param string $current_section Optional. Defaults to empty string.
	 * @return array Array of settings
	 */

		
	public function get_settings( $current_section = '' ) {

		$upgradeToPro = '[ To use this features upgrade to pro ]';
		
		if ( 'home_ad_settings' == $current_section ) {
			$settings = apply_filters('sale_booster_home_ad_settings_data', array(
			
				array(
                    'name'     => __( 'Home And Ad Settings',  'sale_booster' ),
                    'type'     => 'title',
                    'desc'     => '',
                    'id'       => 'sale_booster_home_ad_settings_title'
                ),
				
				array(
                    'name'     => __( 'Home Page Exit Popup', 'sale_booster' ),
                    'type'     => 'select',
                    'id'       => 'home_page_exit_popup',
                    'options'  => self::$getFluentFormsOptions,
                    'desc'     => !defined('FLUENTFORM') ? self::$getFluentFormInstallUrl : "",
					'class'    => 'wc-enhanced-select',
				),
			
				array(
                    'name'     => "",
                    'type'     => 'textarea',
                    'id'       => 'home_page_exit_custom_popup',
                    'css'      => 'height:200px',
                ),
				
				array(
                    'title'    => "Home Page Banner Below Menu",
                    'id'       => 'home_page_banner_below_select',
                    'default'  => '',
                    'type'     => 'radio',
                    'class' => '_sale_booster_list_options',
					'options'  => array(
                        'none'    	    => __( 'None', 'sale-booster' ),
						'shop_page'     => __( 'Shop Page', 'sale-booster' ),
						'category_pages'=> __( 'Category pages', 'sale-booster' ),
						'all_pages'     => __("All pages ".$upgradeToPro, 'sale_booster')
                    ),
				),
				array(
                    'name'     => '',
                    'type'     => 'url',
                    'id'       => 'home_page_banner_below',
					'placeholder' => 'Paste your image url here',
					'desc'     =>  __('<button id="upload_image_banner_below" class="button-primary upload_image">Upload Image</button>', 'sale-booster'),
				),

				array(
                    'name'     => "",
                    'type'     => 'url',
                    'id'       => 'home_page_banner_below_link',
                    'class'    => 'sale_booster_setting_img',
					'placeholder' => 'https://',
					'css'	   => 'display:flex; margin-bottom:20px',
                    'desc' => '<img src="'.get_option('home_page_banner_below').'" style="height:180px;">' 
				),

				array(
                    'title'    => "Home Page Banner Above Footer",
                    'id'       => 'home_page_banner_above_footer_select',
                    'default'  => '',
                    'type'     => 'radio',
                    'class' => '_sale_booster_list_options',
					'options'  => array(
						'none'    	    => __( 'None', 'sale-booster' ),
                        'shop_page'     => __( 'Shop Page', 'sale-booster' ),
						'category_pages'=> __( 'Category pages', 'sale-booster' ),
						'all_pages'     => __("All pages ".$upgradeToPro, 'sale_booster')
                    ),
				),
				array(
					'name'     => __('', 'sale_booster' ),
                    'type'     => 'url',
                    'id'       => 'home_page_banner_above_footer',
					'placeholder' => 'Paste your image url here',
					'desc'    =>  __('<button id="upload_image_above_footer" class="button-primary upload_image">Upload Image</button>', 'sale_booster'),
				),
				array(
                    'name'     => "",
                    'type'     => 'url',
                    'id'       => 'home_page_banner_above_footer_link',
                    'class'    => 'sale_booster_setting_img',
					'placeholder' => 'https://',
					'css'	   => 'display:flex; margin-bottom:20px',
                    'desc' => '<img src="'.get_option('home_page_banner_above_footer').'" style="height:180px;">' 
				),
				
				array(
                    'title'    => __( 'Corner Ad', 'sale_booster' ),
                    'id'       => 'home_corner_ad_position',
                    'default'  => 'no',
                    'type'     => 'radio',
                    'class' => '_sale_booster_list_options',
                    'options'  => array(
                        'top_right'    => __( 'Top Right', 'sale_booster' ),
                        'top_left'     => __( 'Top Left', 'sale_booster' ),
                        'bottom_right' => __( 'Bottom Right', 'sale_booster' ),
                        'bottom_left'  => __( 'Bottom Left', 'sale_booster' ),
                    ),
				),

				array(
                    'title'    => "",
                    'id'       => 'corner_page_select',
                    'default'  => '',
                    'type'     => 'radio',
                    'class' => '_sale_booster_list_options',
                    'options'  => array(
						'none'    	    => __( 'None', 'sale-booster' ),
                        'shop_page'    => __( 'Shop Page', 'sale_booster' ),
						'category_pages' => __( 'Category pages', 'sale_booster' ),
						'all_pages'    => __("All pages ".$upgradeToPro, 'sale_booster')
					),
				),
				
				array(
					'name'     => "",
					'type'     => 'url',
					'id'       => 'home_page_corner_ad',
					'placeholder' => 'Paste your image url here',
					'desc'    =>  __('<button id="upload_image_corner_ad" class="button-primary upload_image">Upload Image</button>', 'sale_booster'),
				),
				array(
                    'name'     => "",
                    'type'     => 'url',
                    'id'       => 'home_page_corner_ad_link',
                    'class'    => 'sale_booster_setting_img',
					'placeholder' => 'https://',
					'css'	   => 'display:flex; margin-bottom:20px',
                    'desc' => '<img src="'.get_option('home_page_corner_ad').'" style="height:180px;">' 
				),
				
				array(
                    'type'  => 'sectionend',
                    'id'    => 'sale_booster_home_ad_settings_section_end'
                )
			) );
		
		} else {
			$settings = apply_filters('sale_booster_lock_customization_settings_data', array(

				array(
					'name'     => __( 'Sale Booster', 'sale_booster' ),
					'type'     => 'title',
					'desc'     => 'Customize the look and feel for Sales booster countdown time and bar colors',
					'id'       => 'sale_booster_settings_title'
				),

				array(
					'name'     => __( 'Topbar Primary Background Color', 'sale_booster' ),
					'type'     => 'color',
					'desc'     => __( 'Topbar timer first color', 'sale_booster' ),
					'desc_tip' => true,
					'id'       => 'sale_booster_settings_primary_bg_color',
					'css'      => 'width:80px;',
					'default'  => '#7901ff',
				),

				array(
					'name'     => __( 'Topbar Secondary Background Color', 'sale_booster' ),
					'type'     => 'color',
					'desc'     => __( 'Topbar timer Second color', 'sale_booster'),
					'desc_tip' => true,
					'id'	   => 'sale_booster_settings_secondary_bg_color',
					'css'      => 'width:80px',
					'default'  => '#ff185f',
				),

				array(
					'name'     => __( 'Topbar Text Color', 'sale_booster' ),
					'type'     => 'color',
					'desc'     => __( 'Topbar Text Color', 'sale_booster'),
					'desc_tip' => true,
					'id'	   => 'sale_booster_settings_topbar_text_color',
					'css'      => 'width:80px',
					'default'  => '#ffffff',
				),

				array(
					'name'     => __( 'Countdown Background Color', 'sale_booster' ),
					'type'     => 'color',
					'desc'     => __( 'Countdown Background Color', 'sale_booster'),
					'desc_tip' => true,
					'id'	   => 'sale_booster_settings_countdown_bg_color',
					'css'      => 'width:80px',
					'default'  => '#9B4DCA',
				),

				array(
					'name'     => __( 'Countdown Timer Color', 'sale_booster' ),
					'type'     => 'color',
					'desc'     => __( 'Countdown Timer Color', 'sale_booster'),
					'desc_tip' => true,
					'id'	   => 'sale_booster_settings_countdown_timer_color',
					'css'      => 'width:80px',
					'default'  => '#ffffff',
				),

				array(
					'name'     => __( 'Countdown Text Color', 'sale_booster' ),
					'type'     => 'color',
					'desc'     => __( 'Countdown Text Color', 'sale_booster'),
					'desc_tip' => true,
					'id'	   => 'sale_booster_settings_countdown_text_color',
					'css'      => 'width:80px',
					'default'  => '#9a07d7',
				),

				array(
					'name'     => __( 'Stock Quantity Color', 'sale_booster' ),
					'type'     => 'color',
					'desc'     => __( 'Stock Quantity Color', 'sale_booster'),
					'desc_tip' => true,
					'id'	   => 'sale_booster_settings_stock_color',
					'css'      => 'width:80px',
					'default'  => '#ff0400',
				),

				array(
					'type'  => 'sectionend',
					'id'    => 'sale_booster_settings_section_end'
				)
			));	
			
			/**
			 * Pro Notice
			*/
			if (!defined('SALES_BOOTER_PRO_INSTALLED')) {
				$settings['pro_notice'] = [
					'name'     => __( 'This is a pro feature', 'sale_booster' ),
					'type'     => 'title',
					'desc' 	   => 'Please upgrade to pro to use this feature. The settings will not effect on frontend unless you install pro version',
					'id'       => 'sale_booster_settings_pro_notice_title'
				];
				$settings['pro_notice_end'] = array(
					'type' => 'sectionend',
					'id'   => 'sale_booster_pro_notice_title_end',
				);
			}
		}

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}
	
	
	/**
	 * Output the settings
	 *
	 * @since 2.0.5
	 */
	public function output() {
	
		global $current_section;
		
		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
	}
	
	
	/**
	 * Save settings
		*
		* @since 2.0.5
		*/
	public function save() {
	
		global $current_section;
		
		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}
}

return new SaleBoosterSettings();

endif;