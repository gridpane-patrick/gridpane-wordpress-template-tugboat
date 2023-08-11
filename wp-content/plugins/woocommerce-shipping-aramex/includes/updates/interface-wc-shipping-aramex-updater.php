<?php
/**
 * Updater interface.
 *
 * @package WC_Shipping_Aramex
 */

/**
 * Interface for version updater to implement.
 *
 * Real version updater lives in the same directory.
 *
 * @since 1.0.2
 */
interface WC_Shipping_Aramex_Updater {
	/**
	 * Performs update.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @return bool Returns true if succeed.
	 */
	public function update();
}
