<?php
/**
 * Description
 *
 * @package    404-config.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_settings;
$config = array(
        'image' => get_template_directory_uri() . '/images/404.png'
);

if ( isset($xstore_amp_settings['general']['dark_version']) && $xstore_amp_settings['general']['dark_version'] ) {
    $config['image'] = get_template_directory_uri() . '/images/404-dark.png';
}
?>
body {
	--et_amp_404-page-bg-image: url(<?php echo $config['image']; ?>);
}
