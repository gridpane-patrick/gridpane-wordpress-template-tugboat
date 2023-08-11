<?php namespace TierPricingTable\Integrations;

use TierPricingTable\Integrations\Plugins\MixMatch;
use TierPricingTable\Integrations\Plugins\WooCommerceProductAddons;
use TierPricingTable\Integrations\Plugins\WooCommerceDeposits;
use TierPricingTable\Integrations\Themes\Astra;
use TierPricingTable\Integrations\Themes\Avada;
use TierPricingTable\Integrations\Themes\Divi;
use TierPricingTable\Integrations\Themes\Electro;
use TierPricingTable\Integrations\Themes\Flatsome;
use TierPricingTable\Integrations\Themes\Merchandiser;
use TierPricingTable\Integrations\Themes\Neto;
use TierPricingTable\Integrations\Themes\OceanWp;
use TierPricingTable\Integrations\Themes\Porto;
use TierPricingTable\Integrations\Themes\Shopkeeper;
use TierPricingTable\Integrations\Themes\TheRetailer;

class Integrations {

	private $themes = array();

	private $plugins = array();

	public function __construct() {
		$this->init();
	}

	public function init() {
		$this->themes = apply_filters( 'tier_pricing_table/integrations/themes', array(
			'avada'        => Avada::class,
			'astra'        => Astra::class,
			'divi'         => Divi::class,
			'oceanWP'      => OceanWp::class,
			'flatsome'     => Flatsome::class,
			'shopkeeper'   => Shopkeeper::class,
			'the retailer' => TheRetailer::class,
			'merchandiser' => Merchandiser::class,
			'electro'      => Electro::class,
			'porto'        => Porto::class,
		) );

		$this->plugins = apply_filters( 'tier_pricing_table/integrations/plugins', array(
			'MixMatch'                 => MixMatch::class,
			'WooCommerceProductAddons' => WooCommerceProductAddons::class,
			'WooCommerceDeposits'      => WooCommerceDeposits::class
		) );


		foreach ( $this->themes as $themeName => $theme ) {
			if ( strpos( strtolower( wp_get_theme()->name ), $themeName ) !== false || ( ! empty( wp_get_theme()->template ) && strpos( strtolower( wp_get_theme()->template ), $themeName ) !== false ) ) {
				new $theme();
			}
		}

		foreach ( $this->plugins as $pluginName => $plugin ) {
			new $plugin();
		}
	}
}
