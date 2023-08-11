<?php namespace TierPricingTable\Addons;

use TierPricingTable\Core\FileManager;
use TierPricingTable\Settings\Settings;

abstract class AbstractAddon {

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
	 * AbstractAddon constructor.
	 *
	 * @param FileManager $fileManager
	 * @param Settings $settings
	 */
	public function __construct( FileManager $fileManager, Settings $settings ) {
		$this->fileManager = $fileManager;
		$this->settings = $settings;
	}

	abstract public function getName();

	abstract public function isActive();

	abstract public function run();
}
