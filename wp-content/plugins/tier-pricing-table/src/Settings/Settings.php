<?php namespace TierPricingTable\Settings;

use TierPricingTable\Addons\CategoryTiers\CategoryTierAddon;
use TierPricingTable\Addons\RoleBasedPricing\RoleBasedPricingAddon;
use TierPricingTable\Core\FileManager;
use TierPricingTable\TierPricingTablePlugin;

/**
 * Class Settings
 *
 * @package TierPricingTable\Settings
 */
class Settings {

	const SETTINGS_PREFIX = 'tier_pricing_table_';

	const SETTINGS_PAGE = 'tiered_pricing_table_settings';

	/**
	 * FileManager
	 *
	 * @var FileManager
	 */
	private $fileManager;

	/**
	 * Settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Settings constructor.
	 *
	 * @param FileManager $fileManager
	 */
	public function __construct( FileManager $fileManager ) {
		$this->fileManager = $fileManager;

		$this->hooks();
	}

	/**
	 * Handle updating settings
	 */
	public function updateSettings() {
		woocommerce_update_options( $this->settings );
	}

	/**
	 * Init all settings
	 */
	public function initSettings() {
		$this->settings = array(
			array(
				'title' => __( 'Tiered price table settings', 'tier-pricing-table' ),
				'desc'  => __( 'This section controls how the tiered pricing table will look and behave at your store.',
					'tier-pricing-table' ),
				'id'    => self::SETTINGS_PREFIX . 'options',
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Show tiered price table', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'display',
				'type'     => 'checkbox',
				'default'  => 'yes',
				'desc'     => __( 'Display a table on a product page? Prices will be changing even if the table is not displaying.',
					'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Display', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'display_type',
				'type'     => 'select',
				'options'  => array(
					'tooltip' => __( 'Tooltip', 'tier-pricing-table' ),
					'table'   => __( 'Table', 'tier-pricing-table' ),
				),
				'desc'     => __( 'Type of displaying.', 'tier-pricing-table' ),
				'desc_tip' => true,
				'default'  => 'table',
			),
			array(
				'title'    => __( 'Tooltip icon color', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'tooltip_color',
				'type'     => 'color',
				'default'  => '#cc99c2',
				'desc'     => __( 'Color of icon.', 'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Tooltip icon size (px)', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'tooltip_size',
				'type'     => 'number',
				'default'  => '15',
				'desc'     => __( 'Size of icon.', 'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'   => __( 'Tooltip border', 'tier-pricing-table' ),
				'id'      => self::SETTINGS_PREFIX . 'tooltip_border',
				'type'    => 'checkbox',
				'default' => 'yes',
				'desc'    => __( 'Enable tooltip border.', 'tier-pricing-table' ),
			),
			array(
				'title'    => __( 'Table title', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'table_title',
				'type'     => 'text',
				'default'  => '',
				'desc'     => __( 'The name is displaying above the table.', 'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Table position', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'position_hook',
				'type'     => 'select',
				'options'  => array(
					'woocommerce_before_add_to_cart_button'     => __( 'Above buy button', 'tier-pricing-table' ),
					'woocommerce_after_add_to_cart_button'      => __( 'Below buy button', 'tier-pricing-table' ),
					'woocommerce_before_add_to_cart_form'       => __( 'Above add to cart form', 'tier-pricing-table' ),
					'woocommerce_after_add_to_cart_form'        => __( 'Below add to cart form', 'tier-pricing-table' ),
					'woocommerce_single_product_summary'        => __( 'Above product title', 'tier-pricing-table' ),
					'woocommerce_before_single_product_summary' => __( 'Before product summary', 'tier-pricing-table' ),
					'woocommerce_after_single_product_summary'  => __( 'After product summary', 'tier-pricing-table' ),
				),
				'desc'     => __( 'Where to display the table.', 'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Active price background color', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'selected_quantity_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#cc99c2',
				'desc'     => __( 'Active tiered price background color.', 'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Quantity column text', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'head_quantity_text',
				'type'     => 'text',
				'default'  => __( 'Quantity', 'tier-pricing-table' ),
				'desc'     => __( 'Name of the quantity column. Set price column and quantity column name blank to do not show table heading.',
					'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Price column text', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'head_price_text',
				'type'     => 'text',
				'default'  => __( 'Price', 'tier-pricing-table' ),
				'desc'     => __( 'Name of the price column. Set price column and quantity column name blank to do not show table heading.',
					'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'   => __( 'Show discount column for percentage table', 'tier-pricing-table' ),
				'id'      => self::SETTINGS_PREFIX . 'show_discount_column',
				'type'    => 'checkbox',
				'default' => 'yes',

			),
			array(
				'title'    => __( 'Discount column text', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'head_discount_text',
				'type'     => 'text',
				'default'  => __( 'Discount (%)', 'tier-pricing-table' ),
				'desc'     => __( 'Name of discount column.', 'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'CSS class', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'table_css_class',
				'type'     => 'text',
				'default'  => '',
				'desc'     => __( 'Add your own CSS styles for the table.',
					'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => self::SETTINGS_PREFIX . 'options'
			),

			// Price formatting
			array(
				'title' => __( 'Prices', 'tier-pricing-table' ),
				'id'    => self::SETTINGS_PREFIX . 'premium_options',
				'type'  => 'title',
				'desc'  => __( 'Section controls how tiered prices will be displayed on a product.',
					'tier-pricing-table' ),
			),
			array(
				'title'    => __( 'Summarize all product variations during calculation tiered pricing for variable product',
					'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'summarize_variations',
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc'     => __( 'Take into account every product variant and calculate a variant price based on the sum of their quantities for a particular variable product.',
					'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Enable price formatting on the catalog page, widgets, etc.',
					'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'tiered_price_at_catalog',
				'type'     => 'checkbox',
				'default'  => 'yes',
				'desc'     => __( 'Change price formatting for products which have tiered price. Display "range" or "minimal price".',
					'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Display tiered price as', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'tiered_price_at_catalog_type',
				'type'     => 'select',
				'options'  => array(
					'range'  => __( 'Range (from the lowest to the highest product price)', 'tier-pricing-table' ),
					'lowest' => __( 'Lowest price', 'tier-pricing-table' ),
				),
				'desc'     => __( 'How to display tiered price in the catalog.', 'tier-pricing-table' ),
				'desc_tip' => true,

			),
			array(
				'title'    => __( 'Lowest price prefix', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'lowest_prefix',
				'type'     => 'text',
				'default'  => __( 'From', 'tier-pricing-table' ),
				'desc'     => __( 'The prefix before the tiered price.', 'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Enable on catalog, widgets, price formatting for variable products',
					'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'tiered_price_at_catalog_for_variable',
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc'     => __( 'Show tiered price at the catalog for variable products. It uses the lowest and the highest prices from all variations.',
					'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Enable price formatting on product page', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'tiered_price_at_product_page',
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc'     => __( 'Change price for product at a product page (show range or minimal price).',
					'tier-pricing-table' ),
				'desc_tip' => true,

			),
			array(
				'title'   => __( 'Set tiered price on click', 'tier-pricing-table' ),
				'id'      => self::SETTINGS_PREFIX . 'clickable_table_rows',
				'type'    => 'checkbox',
				'default' => 'no',
				'desc'    => __( 'Change product quantity on click at a table pricing row.',
					'tier-pricing-table' ),

			),
			array(
				'title'   => __( 'Show total price', 'tier-pricing-table' ),
				'id'      => self::SETTINGS_PREFIX . 'show_total_price',
				'type'    => 'checkbox',
				'default' => 'no',
				'desc'    => __( 'Calculate and show the total price at a product page instead of the regular price.',
					'tier-pricing-table' ),
			),
			array(
				'title'             => __( 'Show tiered price in cart as the discount', 'tier-pricing-table' ),
				'id'                => self::SETTINGS_PREFIX . 'show_discount_in_cart',
				'desc'              => __( 'Display regular product prices as cross out near tiered price.', 'tier-pricing-table' ),
				'type'              => 'checkbox',
				'default'           => 'yes',
				'desc_tip'          => true,
				'custom_attributes' => array( 'data-tiered-pricing-premium-setting' => 'yes' )
			),
			array(
				'type' => 'sectionend',
				// todo
				'id'   => self::SETTINGS_PREFIX . 'premium_options'
			),

			// Summary section
			array(
				'title' => __( 'Summary block', 'tier-pricing-table' ),
				'id'    => self::SETTINGS_PREFIX . 'summary_section',
				'type'  => 'title',
				'desc'  => __( 'Section controls how summary block at a product page.',
					'tier-pricing-table' ),
			),
			array(
				'title'    => __( 'Show summary block',
					'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'display_summary',
				'type'     => 'checkbox',
				'default'  => 'yes',
				'desc'     => __( 'Show summary block at a product page. Display information about actual unit price, product quantity and total',
					'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Summary block title', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'summary_title',
				'type'     => 'text',
				'desc'     => __( 'The name is displaying above the summary block.', 'tier-pricing-table' ),
				'desc_tip' => true,
				'default'  => '',
			),
			array(
				'title'    => __( 'Summary block type', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'summary_type',
				'type'     => 'select',
				'options'  => array(
					'table'  => __( 'Table', 'tier-pricing-table' ),
					'inline' => __( 'Inline', 'tier-pricing-table' ),
				),
				'desc'     => __( 'Type of displaying.', 'tier-pricing-table' ),
				'desc_tip' => true,
				'default'  => 'table',
			),
			array(
				'title'    => __( '"Total" label', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'summary_total_label',
				'type'     => 'text',
				'default'  => __( 'Total:', 'tier-pricing-table' ),
				'desc'     => __( 'Label for the "total" line.', 'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( '"Each" label', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'summary_each_label',
				'type'     => 'text',
				'default'  => __( 'Each: ', 'tier-pricing-table' ),
				'desc'     => __( 'Label for the "each" line.', 'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Summary position', 'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . 'summary_position_hook',
				'type'     => 'select',
				'options'  => array(
					'woocommerce_before_add_to_cart_button'     => __( 'Above buy button', 'tier-pricing-table' ),
					'woocommerce_after_add_to_cart_button'      => __( 'Below buy button', 'tier-pricing-table' ),
					'woocommerce_before_add_to_cart_form'       => __( 'Above add to cart form', 'tier-pricing-table' ),
					'woocommerce_after_add_to_cart_form'        => __( 'Below add to cart form', 'tier-pricing-table' ),
					'woocommerce_single_product_summary'        => __( 'Above product title', 'tier-pricing-table' ),
					'woocommerce_before_single_product_summary' => __( 'Before product summary', 'tier-pricing-table' ),
					'woocommerce_after_single_product_summary'  => __( 'After product summary', 'tier-pricing-table' ),
				),
				'default'  => 'woocommerce_after_add_to_cart_button',
				'desc'     => __( 'Where to display the summary block.', 'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => self::SETTINGS_PREFIX . 'premium_options'
			),

			// Category Addon
			array(
				'title' => __( 'Tiered pricing for category', 'tier-pricing-table' ),
				'id'    => self::SETTINGS_PREFIX . 'category_addon_section',
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Category tiered pricing',
					'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . CategoryTierAddon::SETTING_ENABLE_KEY,
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc'     => __( 'Set up tiered pricing rules for product categories. Products will inherit the pricing rules.',
					'tier-pricing-table' ),
				'desc_tip' => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => self::SETTINGS_PREFIX . 'category_addon_section_end'
			),

			// Role-based Addon
			array(
				'title' => __( 'Role-based pricing', 'tier-pricing-table' ),
				'id'    => self::SETTINGS_PREFIX . 'role_based_pricing_addon_section',
				'type'  => 'title',
			),
			array(
				'title'    => __( 'Role-based pricing',
					'tier-pricing-table' ),
				'id'       => self::SETTINGS_PREFIX . RoleBasedPricingAddon::SETTING_ENABLE_KEY,
				'type'     => 'checkbox',
				'desc'     => __( 'Set up different prices for roles.',
					'tier-pricing-table' ),
				'default'  => 'yes',
				'desc_tip' => true,

			),
			array(
				'type' => 'sectionend',
				'id'   => self::SETTINGS_PREFIX . 'role_based_pricing_addon_section'
			),
		);
	}

	/**
	 * Register hooks
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'initSettings' ) );

		add_filter( 'woocommerce_settings_tabs_' . self::SETTINGS_PAGE,
			array( $this, 'addTieredPricingTableSettings' ) );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'addSettingsTab' ), 50 );
		add_action( 'woocommerce_update_options_' . self::SETTINGS_PAGE, array( $this, 'updateSettings' ) );
	}

	/**
	 * Add own settings tab
	 *
	 * @param array $settings_tabs
	 *
	 * @return mixed
	 */
	public static function addSettingsTab( $settings_tabs ) {

		$settings_tabs[ self::SETTINGS_PAGE ] = __( 'Tiered Pricing', 'tier-pricing-table' );

		return $settings_tabs;
	}

	/**
	 * Add settings to WooCommerce
	 */
	public function addTieredPricingTableSettings() {

		wp_enqueue_script( 'quantity-table-settings-js', $this->fileManager->locateAsset( 'admin/settings.js' ),
			array( 'jquery' ), TierPricingTablePlugin::VERSION );

		woocommerce_admin_fields( $this->settings );
	}

	/**
	 * Get all settings
	 *
	 * @return array
	 */
	public function getAll() {
		return array(
			'display'                 => $this->get( 'display', 'yes' ),
			'position_hook'           => $this->get( 'position_hook',
				'woocommerce_after_add_to_cart_button' ),
			'head_quantity_text'      => $this->get( 'head_quantity_text',
				__( 'Quantity', 'tier-pricing-table' ) ),
			'head_price_text'         => $this->get( 'head_price_text',
				__( 'Price', 'tier-pricing-table' ) ),
			'display_type'            => $this->get( 'display_type', 'table' ),
			'selected_quantity_color' => $this->get( 'selected_quantity_color', '#cc99c2' ),
			'table_title'             => $this->get( 'table_title', '' ),
			'table_css_class'         => $this->get( 'table_css_class', '#fff' ),
			'tooltip_size'            => $this->get( 'tooltip_size', 15 ),
			'tooltip_border'          => $this->get( 'tooltip_border', 'yes' ),

			'show_discount_in_cart'                => $this->get( 'show_discount_in_cart', 'yes' ),
			'summarize_variations'                 => $this->get( 'summarize_variations', 'no' ),
			'tiered_price_at_catalog'              => $this->get( 'tiered_price_at_catalog', 'yes' ),
			'tiered_price_at_product_page'         => $this->get( 'tiered_price_at_product_page', 'no' ),
			'tiered_price_at_catalog_for_variable' => $this->get( 'tiered_price_at_catalog_for_variable', 'yes' ),
			'tiered_price_at_catalog_type'         => $this->get( 'tiered_price_at_catalog_type', 'lowest' ),
			'lowest_prefix'                        => $this->get( 'lowest_prefix', 'From ' ),
			'show_discount_column'                 => $this->get( 'show_discount_column', 'yes' ),
			'clickable_table_rows'                 => $this->get( 'clickable_table_rows', 'no' ),
			'show_total_price'                     => $this->get( 'show_total_price', 'no' ),
			'head_discount_text'                   => $this->get( 'head_discount_text',
				__( 'Discount (%)', 'tier-pricing-table' ) ),
		);
	}

	/**
	 * Get setting by name
	 *
	 * @param string $option_name
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $option_name, $default = null ) {
		return get_option( self::SETTINGS_PREFIX . $option_name, $default );
	}

	/**
	 * Get url to settings page
	 *
	 * @return string
	 */
	public function getLink() {
		return admin_url( 'admin.php?page=wc-settings&tab=tiered_pricing_table_settings' );
	}
}
