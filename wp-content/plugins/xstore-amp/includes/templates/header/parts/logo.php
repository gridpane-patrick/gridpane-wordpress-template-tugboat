<?php
/**
 * Header logo template
 *
 * @package    logo.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_settings, $xstore_amp_vars;

$element_options = array();
$element_options['logo_img_alt'] = $element_options['logo_img_dark_alt'] = '';
$element_options['logo_img'] = $element_options['logo_img_dark'] = get_template_directory_uri() . '/images/logo.png';
$element_options['logo_img_et-desktop'] = get_theme_mod( 'logo_img_et-desktop', 'logo' );
$element_options['logo_img_dark_et-desktop'] = get_theme_mod( 'logo_img_et-desktop', 'logo' );
$element_options['logo_img_id'] = $element_options['logo_img_dark_id'] = '';

if ( is_array($element_options['logo_img_et-desktop']) ) {
	if ( isset($element_options['logo_img_et-desktop']['id']) && $element_options['logo_img_et-desktop']['id'] != '' ) {
		$element_options['logo_img_id'] = $element_options['logo_img_dark_id'] = $element_options['logo_img_et-desktop']['id'];
		$element_options['logo_img_alt'] = $element_options['logo_img_dark_alt'] = get_post_meta( $element_options['logo_img_et-desktop']['id'], '_wp_attachment_image_alt', true);
	}
	if ( isset($element_options['logo_img_et-desktop']['url']) && $element_options['logo_img_et-desktop']['url'] != '' ) {
		$element_options['logo_img'] = $element_options['logo_img_dark'] = $element_options['logo_img_et-desktop']['url'];
	}
}

if ( is_array($element_options['logo_img_dark_et-desktop']) ) {
	if ( isset($element_options['logo_img_dark_et-desktop']['id']) && $element_options['logo_img_dark_et-desktop']['id'] != '' ) {
		$element_options['logo_img_dark_id'] = $element_options['logo_img_dark_et-desktop']['id'];
		$element_options['logo_img_dark_alt'] = get_post_meta( $element_options['logo_img_dark_et-desktop']['id'], '_wp_attachment_image_alt', true);
	}
	if ( isset($element_options['logo_img_dark_et-desktop']['url']) && $element_options['logo_img_dark_et-desktop']['url'] != '' ) {
		$element_options['logo_img_dark'] = $element_options['logo_img_dark_et-desktop']['url'];
	}
}

$element_options['logo'] = isset($xstore_amp_settings['general']['logo']) ? attachment_url_to_postid($xstore_amp_settings['general']['logo'])
	: $element_options['logo_img_id'];

$element_options['logo_dark'] = isset($xstore_amp_settings['general']['logo_dark']) ? attachment_url_to_postid($xstore_amp_settings['general']['logo_dark'])
	: $element_options['logo_img_dark_id'];

$active_mode = 'light-mode';
if ( isset($xstore_amp_settings['general']['dark_version']) && $xstore_amp_settings['general']['dark_version'] ) {
	$active_mode = 'dark-mode';
}
if ( !empty($_SESSION['xstore-amp-siteMode']) ) {
	$active_mode = $_SESSION['xstore-amp-siteMode'];
}

$element_options['dark_light_switcher'] = isset($xstore_amp_settings['general']['dark_light_switcher']) && $xstore_amp_settings['general']['dark_light_switcher'];

?>
<div class="h-logo" data-element="logo">
    <?php if ( $element_options['dark_light_switcher'] || $active_mode == 'light-mode') : ?>
        <a class="<?php echo $active_mode == 'dark-mode' ? 'hidden' : ''; ?>" href="<?php echo esc_url($xstore_amp_vars['home_url']); ?>" <?php if ( $element_options['dark_light_switcher'] ) : ?>[class]="siteMode == 'dark-mode' ? 'hidden' : ''"<?php endif; ?>>
            <?php
            if ( $element_options['logo'] ) {
                echo $this->render_image(
                    array(
                        'image_id' => $element_options['logo'],
                        'is_hero' => true
                    )
                );
            }
            else {?>
                <amp-img data-hero src="<?php echo esc_url($element_options['logo_img']); ?>" width="300"
                         height="58"
                         alt="<?php echo esc_attr($element_options['logo_img_alt']); ?>"
                         layout="responsive">
                </amp-img>
            <?php }
            ?>
        </a>
    <?php endif;
    if ( $element_options['dark_light_switcher'] || $active_mode == 'dark-mode' ) : ?>
        <a class="<?php echo $active_mode == 'light-mode' ? 'hidden' : ''; ?>" href="<?php echo esc_url($xstore_amp_vars['home_url']); ?>" <?php if ( $element_options['dark_light_switcher'] ) : ?>[class]="siteMode == 'light-mode' ? 'hidden' : ''"<?php endif; ?>>
            <?php
            if ( $element_options['logo_dark'] ) {
                echo $this->render_image(
                    array(
                        'image_id' => $element_options['logo_dark'],
                        'is_hero' => true
                    )
                );
            }
            else {?>
                <amp-img data-hero src="<?php echo esc_url($element_options['logo_img_dark']); ?>" width="300"
                         height="58"
                         alt="<?php echo esc_attr($element_options['logo_img_dark_alt']); ?>"
                         layout="responsive">
                </amp-img>
            <?php }
            ?>
        </a>
    <?php endif; ?>
</div>