<?php
/**
 * Description
 *
 * @package    sticky-cartConfig.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

$config = array(
	'height' => '80'
);

?>
body {
	--et_amp-extra-bottom-space-h: <?php echo $config['height']; ?>px;
}
.sticky-cart .amp-container {
	height: <?php echo $config['height']; ?>px;
}

