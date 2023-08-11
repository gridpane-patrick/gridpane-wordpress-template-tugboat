<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wpswings.com/?utm_source=wpswings-official&utm_medium=order-bump-org-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    Upsell_Order_Bump_Offer_For_Woocommerce
 * @subpackage Upsell_Order_Bump_Offer_For_Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
	body{
		background: #000000;
		text-align: center;
		color: #ffffff;
	}
	.wps-warning-text h1, .wps-warning-text h2, .wps-warning-text h3 {
		color: red !important;
	}
</style>
<div class="wps-warning-text">
	<h1>500 Internal Update Error</h1>  
	<h2>Couldn't launch plugin. Please update the free Order Bump plugin to enjoy our latest version.</h2>
	<h3>Page Not Found - lets take you to <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"> Update page</a></h3> 
</div>
<div>
	<?php require plugin_dir_path( __FILE__ ) . 'rocket.svg'; ?>
</div>
