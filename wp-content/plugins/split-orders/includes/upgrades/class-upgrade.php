<?php

namespace Vibe\Split_Orders\Upgrades;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Runs required upgrades for a release
 *
 * @since 1.4.0
 */
abstract class Upgrade {

	/**
	 * Runs the upgrade routine for this release
	 */
	abstract public function run();
}
