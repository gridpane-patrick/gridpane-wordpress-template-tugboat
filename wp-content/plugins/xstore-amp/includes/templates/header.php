<?php
/**
 * Header template
 *
 * @package    header.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_vars, $xstore_amp_settings;
?>
<!doctype html>
<html amp <?php language_attributes(); ?>>
<head>
    <?php $this->head(); ?>
</head>
<?php $active_mode = 'light-mode';
    if ( isset($xstore_amp_settings['general']['dark_version']) && $xstore_amp_settings['general']['dark_version'] ) {
	    $active_mode = 'dark-mode';
    }
    if ( !empty($_SESSION['xstore-amp-siteMode']) ) {
        $active_mode = $_SESSION['xstore-amp-siteMode'];
    }
?>
<body class="<?php echo $active_mode; ?>"
[class]="siteMode">
<header id="site-header" class="with-shadow <?php if ( !isset($xstore_amp_settings['general']['sticky_header']) || $xstore_amp_settings['general']['sticky_header'] ) {?>sticky<?php } ?>">
    <div class="amp-container">
        <div class="flex-row">
            <?php
                $sections = array(
                    'left' => array(
                        'mobile_menu',
                    ),
                    'middle' => array('logo'),
                    'right' => array()
                );
                if ( !isset($xstore_amp_settings['general']['header_search']) || $xstore_amp_settings['general']['header_search']) {
                    $sections['right'][] = 'search';
                }
                if ( $xstore_amp_vars['is_woocommerce'] ) {
	                $sections['right'][] = 'cart';
                }
                foreach ( $sections as $section ) { ?>
                    <div class="flex-col">
                        <?php
                            foreach ( $section  as $element ) {
	                            require_once XStore_AMP_TEMPLATES_PATH . 'header/parts/' . $element . '.php';
                            }
                        ?>
                    </div>
                <?php }
                
            ?>
        </div>
    </div>
	<?php do_action('xstore_amp_after_header_content'); ?>
</header>
<?php do_action('xstore_amp_after_header'); ?>
<main class="amp-container">
        <?php do_action('xstore_amp_before_main_content');