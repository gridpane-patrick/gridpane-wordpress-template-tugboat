<?php
/**
 * Header mobile menu template
 *
 * @package    mobile_menu.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_icons;

?>
<span class="h-mob-menu inline-flex align-items-center" on="tap:mobileMenu.open" role="button" tabindex="-1" data-element="mob-menu">
	<?php echo str_replace('1em', '.75em', $xstore_amp_icons['et_icon-burger']); ?>
</span>