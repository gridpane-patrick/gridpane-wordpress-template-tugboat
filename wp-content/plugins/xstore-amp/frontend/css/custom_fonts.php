<?php
/**
 * Description
 *
 * @package    custom_fonts.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );
global $xstore_amp_custom_fonts, $xstore_amp_custom_fonts_to_load;
$xstore_amp_custom_fonts_to_load = array_unique((array)$xstore_amp_custom_fonts_to_load);
$css_fonts = '';
$fonts = get_option( 'etheme-fonts', false );
if ( count($xstore_amp_custom_fonts_to_load) && count($fonts) ) {
	foreach ( $fonts as $font ) {
		if (!in_array($font['name'], $xstore_amp_custom_fonts_to_load)) continue;
		switch ( $font['file']['extension'] ) {
			case 'ttf':
				$format = 'truetype';
				break;
			case 'otf':
				$format = 'opentype';
				break;
			case 'eot?#iefix':
				$format = 'embedded-opentype';
				break;
			case 'woff2':
				$format = 'woff2';
				break;
			case 'woff':
				$format = 'woff';
				break;
			default:
				$format = false;
				break;
		}
		
		$format = ( $format ) ? 'format("' . $format . '")' : '';
		
		$font_url = ( is_ssl() && (strpos($font['file']['url'], 'https') === false) ) ? str_replace('http', 'https', $font['file']['url']) : $font['file']['url'];
		
		// ! Set fonts
		$css_fonts .= '@font-face {
							font-family: "' . $font['name'] . '";
							src: url(' . $font_url . ') ' . $format . ';
						}
					';
	}
}
echo $this->minify_css($css_fonts);