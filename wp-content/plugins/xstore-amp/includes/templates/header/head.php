<?php
/**
 * Description
 *
 * @package    head.php
 * @since      1.0.0
 * @author     andrey
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_vars, $xstore_amp_settings;
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta name="description" content="This is the AMP Boilerplate.">
<link rel="preload" as="script" href="https://cdn.ampproject.org/v0.js">
<script async src="https://cdn.ampproject.org/v0.js"></script>
<script async custom-element="amp-install-serviceworker" src="https://cdn.ampproject.org/v0/amp-install-serviceworker-0.1.js"></script>
<meta name="theme-color" content="<?php echo (isset($xstore_amp_settings['appearance']['active_color']) && $xstore_amp_settings['appearance']['active_color'] != '') ? $xstore_amp_settings['appearance']['active_color'] : '#a4004f'; ?>">
<link rel="preconnect dns-prefetch" href="https://fonts.gstatic.com/" crossorigin>
<?php
$this->render_custom_page_preloads();
?>
<?php if ( isset($xstore_amp_settings['general']['favicon']) && $xstore_amp_settings['general']['favicon'] != '' ): ?>
	<link rel="shortcut icon" href="<?php echo esc_url($xstore_amp_settings['general']['favicon']); ?>"/>
<?php endif; ?>
<?php $this->render_custom_page_scripts(); ?>
<style amp-custom>
	<?php $this->render_custom_page_css(); ?>
</style>
<?php
$this->render_page_fonts();
?>
<style amp-boilerplate>
    body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}
    @-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
    @keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
</style>
<noscript>
	<style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style>
</noscript>
<link rel="canonical" href="<?php echo esc_url(remove_query_arg('amp', add_query_arg( 'no-amp', 'true',  $xstore_amp_vars['home_url']))); ?>">
<title><?php echo function_exists( 'wp_get_document_title' ) ? wp_get_document_title() : wp_title( '', false ); ?></title>

<?php echo do_action('xstore_amp_head') ?>