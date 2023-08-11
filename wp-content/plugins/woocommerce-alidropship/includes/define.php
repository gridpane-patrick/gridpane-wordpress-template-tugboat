<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_ADMIN', VI_WOOCOMMERCE_ALIDROPSHIP_DIR . "admin" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_FRONTEND', VI_WOOCOMMERCE_ALIDROPSHIP_DIR . "frontend" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_LANGUAGES', VI_WOOCOMMERCE_ALIDROPSHIP_DIR . "languages" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_TEMPLATES', VI_WOOCOMMERCE_ALIDROPSHIP_DIR . "templates" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_PLUGINS', VI_WOOCOMMERCE_ALIDROPSHIP_DIR . "plugins" . DIRECTORY_SEPARATOR );
if ( ! defined( 'VI_WOOCOMMERCE_ALIDROPSHIP_CACHE' ) ) {
	define( 'VI_WOOCOMMERCE_ALIDROPSHIP_CACHE', WP_CONTENT_DIR . '/cache/woo-alidropship/' );
}
/*Constants for AliExpress dropshipping API*/
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_APP_KEY', '34058263' );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_GET_SIGNATURE_PLACE_ORDER_URL', 'https://api.villatheme.com/wp-json/aliexpress/get_signature' );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_GET_SIGNATURE_PLACE_ORDER_BATCH_URL', 'https://api.villatheme.com/wp-json/aliexpress/create_orders' );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_GET_SIGNATURE_GET_PRODUCT_URL', 'https://api.villatheme.com/wp-json/aliexpress/get_products' );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_GET_SIGNATURE_GET_PRODUCT_URL_V2', 'https://api.villatheme.com/wp-json/aliexpress/get_products/v2' );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_GET_SIGNATURE_GET_ORDER_URL', 'https://api.villatheme.com/wp-json/aliexpress/get_orders' );
$plugin_url = plugins_url( '', __FILE__ );
$plugin_url = str_replace( '/includes', '', $plugin_url );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_ASSETS', $plugin_url . "/assets/" );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_ASSETS_DIR', VI_WOOCOMMERCE_ALIDROPSHIP_DIR . "assets" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_PACKAGES', VI_WOOCOMMERCE_ALIDROPSHIP_ASSETS_DIR . "packages" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_CSS', VI_WOOCOMMERCE_ALIDROPSHIP_ASSETS . "css/" );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_CSS_DIR', VI_WOOCOMMERCE_ALIDROPSHIP_ASSETS_DIR . "css" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_JS', VI_WOOCOMMERCE_ALIDROPSHIP_ASSETS . "js/" );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_JS_DIR', VI_WOOCOMMERCE_ALIDROPSHIP_ASSETS_DIR . "js" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES', VI_WOOCOMMERCE_ALIDROPSHIP_ASSETS . "images/" );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_IMAGES_DIR', VI_WOOCOMMERCE_ALIDROPSHIP_ASSETS_DIR . "images/" );
define( 'VI_WOOCOMMERCE_ALIDROPSHIP_EXTENSION_VERSION', '1.0' );


/*Include functions file*/
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "functions.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "functions.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "support.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "support.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "check_update.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "check_update.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "update.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "update.php";
}
/*Include functions file*/
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "wp-async-request.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "wp-async-request.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "wp-background-process.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "wp-background-process.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "data.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "data.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-draft-product.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-draft-product.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-error-images-table.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-error-images-table.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-background-download-images.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-background-download-images.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-background-ali-api-get-product-data.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-background-ali-api-get-product-data.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-background-ali-api-get-order-data.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "class-vi-wad-background-ali-api-get-order-data.php";
}
if ( is_file( VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "setup-wizard.php" ) ) {
	require_once VI_WOOCOMMERCE_ALIDROPSHIP_INCLUDES . "setup-wizard.php";
}
vi_include_folder( VI_WOOCOMMERCE_ALIDROPSHIP_ADMIN, 'VI_WOOCOMMERCE_ALIDROPSHIP_Admin_' );
vi_include_folder( VI_WOOCOMMERCE_ALIDROPSHIP_FRONTEND, 'VI_WOOCOMMERCE_ALIDROPSHIP_Frontend_' );
vi_include_folder( VI_WOOCOMMERCE_ALIDROPSHIP_PLUGINS, 'VI_WOOCOMMERCE_ALIDROPSHIP_Plugins_' );
