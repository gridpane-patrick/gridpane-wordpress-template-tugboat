<?php
/**
 * Description
 *
 * @package    mobilePanel-config.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_settings;
$config = array(
	'mobile_panel_bg_color' => '#fff',
    'mobile_panel_color' => '#222',
    'mobile_panel_content_zoom' => 72,
    'mobile_panel_height' => 58
);

if ( isset($xstore_amp_settings['general']['dark_version']) && $xstore_amp_settings['general']['dark_version'] ||
     ( !empty($_SESSION['xstore-amp-siteMode']) && $_SESSION['xstore-amp-siteMode'] == 'dark-mode') ) {
	$config['mobile_panel_bg_color'] = '#222';
	$config['mobile_panel_color'] = '#fff';
}

if ( isset($xstore_amp_settings['mobile_panel']['mobile_panel_bg_color']) && $xstore_amp_settings['mobile_panel']['mobile_panel_bg_color'] != '' ) {
	$config['mobile_panel_bg_color'] = $xstore_amp_settings['mobile_panel']['mobile_panel_bg_color'];
}

if ( isset($xstore_amp_settings['mobile_panel']['mobile_panel_color']) && $xstore_amp_settings['mobile_panel']['mobile_panel_color'] != '' ) {
	$config['mobile_panel_color'] = $xstore_amp_settings['mobile_panel']['mobile_panel_color'];
}

if ( isset($xstore_amp_settings['mobile_panel']['mobile_panel_content_zoom']) && $xstore_amp_settings['mobile_panel']['mobile_panel_content_zoom'] != '' ) {
	$config['mobile_panel_content_zoom'] = $xstore_amp_settings['mobile_panel']['mobile_panel_content_zoom'];
}

if ( isset($xstore_amp_settings['mobile_panel']['mobile_panel_height']) && $xstore_amp_settings['mobile_panel']['mobile_panel_height'] != '' ) {
	$config['mobile_panel_height'] = $xstore_amp_settings['mobile_panel']['mobile_panel_height'];
}

?>

body {
	--et_amp-mobile-panel-color: <?php echo $config['mobile_panel_color']; ?>;
	--et_amp-mobile-panel-bg-color: <?php echo $config['mobile_panel_bg_color']; ?>;
	--et_amp-mobile-panel-h: <?php echo $config['mobile_panel_height']; ?>px;
    --et_amp-extra-bottom-space-h: var(--et_amp-mobile-panel-h);
}

.mobile-panel-wrapper {
    --content-zoom: calc(<?php echo $config['mobile_panel_content_zoom']; ?>rem * .01);
}

<?php unset($config);