<?php namespace TierPricingTable\Admin\ProductManagers;

use TierPricingTable\Core\FileManager;
use TierPricingTable\Settings\Settings;

/**
 * Class ProductManagerAbstract
 *
 * @package TierPricingTable\Admin\ProductManagers
 */
abstract class ProductManagerAbstract {

	/**
	 * FileManager
	 *
	 * @var FileManager
	 */
	protected $fileManager;

	/**
	 * Settings
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Product Manager constructor.
	 *
	 * Register menu items and handlers
	 *
	 * @param FileManager $fileManager
	 * @param Settings $settings
	 */
	public function __construct( FileManager $fileManager, Settings $settings) {
		$this->fileManager = $fileManager;
		$this->settings    = $settings;

		$this->hooks();
	}

	/**
	 * Register manager hooks
	 */
	abstract protected function hooks();
}
