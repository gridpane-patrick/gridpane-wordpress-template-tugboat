<?php
/**
 * Header mobile menu sidebar template
 *
 * @package    mobile_menu_sidebar.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_icons, $xstore_amp_settings;

?>
<amp-sidebar id="mobileMenu" class="mobile-menu" layout="nodisplay" side="left">
    <button class="close" on="tap:mobileMenu.close"><?php echo $xstore_amp_icons['et_icon-close']; ?></button>
	<?php echo wp_nav_menu(
		array(
			'menu' => isset($xstore_amp_settings['general']['menu']) && $xstore_amp_settings['general']['menu'] > 0 ? $xstore_amp_settings['general']['menu'] : 'main-menu',
			'container_class' => 'menu-main-container',
			'after' => '',
			'link_before' => '',
			'link_after' => '',
			'depth' => 1, // @todo with multilevels
			'echo' => false,
			'fallback_cb' => false,
			//            'walker' => new XStore_AMP_Walkers
		)
	); ?>
</amp-sidebar>