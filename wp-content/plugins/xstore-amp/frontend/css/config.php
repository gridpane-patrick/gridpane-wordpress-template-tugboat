<?php
/**
 * Description
 *
 * @package    config.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_settings, $xstore_amp_vars;
$config = array(
    'font_color' => '#555',
    'heading_color' => '#222',
    'active_color' => '#a4004f',
    'button_bg_color' => '#222',
    'button_color' => '#fff',
    'header_bg_color' => '#fff',
    'header_color' => '#222',
    'header_height' => 54,
    'mobile_menu_icon_color' => '',
    'mobile_menu_icon_bg_color' => '#f1f1f1',
    'mobile_menu_bg_color' => '#fff',
    'mobile_menu_color' => '#222',
	'footer_bg_color' => '#222',
    'footer_color' => '#fff',
    'copyrights_bg_color' => '#222',
    'copyrights_color' => '#fff',
    'mobile_menu_content_zoom' => 140,
    
    'body_bg' => '#fff',
    'border' => '#e1e1e1',
    'dark' => '#222',
    'white' => '#fff',
    'link' => '#222',
    'light_grey' => '#f1f1f1',
    'light_grey2' => '#fdfdfd',
    'transparent' => 'rgba(255,255,255, 0)',
    'box_shadow_color' => 'rgb(0 0 0 / 10%)',
    'select_arrow' => 'url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAABmJLR0QA/wD/AP+gvaeTAAAEm0lEQVR4nO3cWaiUZRgH8N85armShlgWLZS0gLQgIVm0UGQgtEBKe110IdSFF0FXhtCNChERFLSQFBUltEC0ZxS0E7QZ0UJEG6aFltmiOV28c3DpvO+cZWa+c+Z7fjAo58x87/99HuSceb9nJIQQQgghhBBCCCGEEEIIIYQQQgghhBBCO01o/nkYrsRp+B7bK0tUL7NwFRZhM7bCOfgdjebjl+bXQmediy321H07ToeP9vriwGMHFlcSsx4ulGq8f92fhm2DfKOBv3BRBWF73cVSbQer+cvwbOabDfyDZV2P3LuWSTXN1ftGOBY/Fp60C9d0OXgvulaqZa7OD6Nv4MlH4svCk3djRfey95wVUg1z9b3TXs0YcCg2Fl7UwMrOZ+85tyrX9HaDNGPAIfi4xQVWdy57z1mtXMtVQ7nILLzX4kJr25u7J61VruEtw7nYTLzd4oL3oL892XtKv1Sb0s/jm0dy4enYULhwA/eKpuytH/fJ1+tfLB/NAlOlNyulpjyKiaNZpEdMxGPKzbihHQtNwQuFhRp4ApPasdg4NUmqQa4+O3F5Oxc8AE8WFmxI7/gnt3PRcWKy1qcdSzux8CSsLyzckP4lTenE4mPUdLwmX48/saSTASZgXSFAA69jRidDjBEz8IZ8HXZIp7od14/7C0Ea0vuYg7sRpiIH4335/f+Gs7oZqE86fyk15QPM7maoLpkt7S237204o4pgfdI5TKkpGzG3inAdMhefye93CxZUlq5plXJTPsfhVYVro8OlveT2+TNOqSzdfm5Rbso3OKaydKN3jLSH3P5+wAmVpcu4WfnM/1vMqyzdyM2Tsuf29R2OqyxdC8ulI4Jc+J8wv7J0wzdfypzbz1c4qrJ0Q3SDclM24aTK0g3dyVLW3D6+wBGVpRumK6Tzm9xmfsXCytK1tlDKmMv/iXQzb1xZqjxhsVWa2BtrFknZcrk/xJzK0o3SEuk8J7e57TivsnT/d76UKZf3Henm3biWm9IbePyBCypLt8di5Zxv4qDK0rXZ2dL5Tm6zf+OSytJxaTNDLt8rmFZZug45U35stSH9vLmsglxXK/8C8qJ057QnLbDvxPf+j124rot5rleeJnxKujnX006VPguRK8JuzRnXDrtJ+WRhvRrdlj5ROv8pNaWTo6utRjvX2fNhpto4XjoHyhWloTOjqytbrPmAGo82HY2vlQvUztHVVqOdgw49102ryfsG1rRhnVajncWh57o5FJ8qF+xuIytYf/O1pWuvGlX6HjXH4J933Psx3NHV/uZrStcc1tBz3czCu8oFfMTQRlcnSmOupd/kRjT0XDcz8ZZyUx5Xfo/QarRz1EPPdTMNryo3JTe62mq0s21Dz3UzFS8pN+V5+46uthoM3yndPAsjdCCeUW7KwOjqjObfc8/r2NBz3UzBc8pN2aA89Byft2+zCdLnuEtNyT26NvRcNxPwoOE1o+tDz3XTh7sMrRmVDT3XTR/uUG7GmBh6rpvbDN6MMTX0XDdr7NuMzdIdyXGrF46bF0u/RW3CQ9L/bBRCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYD/AI5xCCmGn2cGAAAAAElFTkSuQmCC)',
    'no_amp-link-bg-color' => '#fff',
);
if ( isset($xstore_amp_settings['general']['dark_version']) && $xstore_amp_settings['general']['dark_version'] ||
     ( !empty($_SESSION['xstore-amp-siteMode']) && $_SESSION['xstore-amp-siteMode'] == 'dark-mode') ) {
	$config['font_color'] = '#999';
	$config['heading_color'] = '#fff';
	$config['button_bg_color'] = '#fff';
	$config['button_color'] = '#222';
	$config['header_bg_color'] = '#222';
	$config['header_color'] = '#fff';
	$config['mobile_menu_icon_color'] = '';
	$config['mobile_menu_icon_bg_color'] = '#2f2f2f';
	$config['mobile_menu_bg_color'] = '#222';
	$config['mobile_menu_color'] = '#fff';
	
	$config['body_bg'] = '#000';
	$config['transparent'] = 'rgba(0,0,0, 0)';
	$config['border'] = '#555';
	$config['dark'] = '#fff';
	$config['white'] = '#222';
	$config['link'] = '#fff';
	$config['light_grey'] = '#2f2f2f';
	$config['light_grey2'] = '#1f1f1f';
	$config['footer_bg_color'] = '#1b1b1b';
	$config['footer_color'] = '#fff';
	$config['copyrights_bg_color'] = '#2c2c2c';
	$config['copyrights_color'] = '#fff';
	$config['select_arrow'] = 'url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAABmJLR0QA/wD/AP+gvaeTAAAEpUlEQVR4nO3cWYjVVRwH8HM1SyspQ2yRFipaIFroIVooo0ghWskoaHvoIaiHHgSfDKGXDCIiKGghSSoqKIP2lYI2I2gzooWKaLUiy1bNTw//vzXp/P9nZrz3nrne3+dpZO79n+85v4Fxjt8xpRBCCCGEEEIIIYQQQgghhBBCCCGEEEII3dRJKSXslVI6o/7zyk6n803RVEMCs1JK56aUpqeUHut0Op8lzMMv/vMD5pWNuu3Dyfh+xLmvw7EJb9vSb5hfOvS2CgvqM97cyoS1o3wC/sCZpcNva3BWfbajeSbh0YZPwl84v/QmthU4vz7TJlcmHICvWl60AReX3sygwyX1WTZZgc6mF++Dj1pevBFXF97TwMLV9Rk2uenfYYx40x5Y3fImWFJoTwML12TO9IYthjHizbvjncwDruvzngYWrsuc5dKxPGQWVmUedH3vtzPYcH3mDBeP52G74tXMA2/FlB7uaSBhSn02TTZi0UQevDOezwzlthjKf+ph3N5yXn/jiq1ZYEc8kxnKvdiui/saSNgO92WGcXk3FpqBJzNDeQDTurCvgYRp9Rk0WY8Lurng9ngoM5RHMb1riw4ITJe/7VjYi4Wn4cHMUJ7EjK4vPkmpvs++0HIev+P0XgaYiuWZobyImT0LMUlgJl5qOYffsKAfQabgjsxQVmG3nocpBLvhjZb9/4wT+xmoo7p/afMmZvctVJ9gdr23JmtxfIlgHdU9TJvV2LPv4XoEe+L9lv1+j6NLh1yaGcoHmFs0ZBdgbr2XJt/hyNI5U0opYXFmKJ9i/9I5Jwr713to8iUOKZ3zf7BI+53/5ziwdM7xwoF19iZf4KDSOUeFK1RXBE2+xmGlc44VDqszN/kY+5bO2QqXZ4byLQ4vnTMHR9RZm3yIvUvnHBNcqLq/afIjjimdswmOqTM2eRe7l845LliovWHxE44rnXNzOK7O1uQtzCmdc0Jwuuo+p8k6nFI65yY4tc7U5DXsWjrnVtHc0tvkV5w2CXLOz+R8GbuUztkVOEl1v9PkT5xdMN85dYYmz2KnUvl6Aidorq1Sfb85r0Cui7T/BeQp7NjvXH2Bo/2/8b25Dbi0j3ku094mfBjb9ytPETgKa1oOYSOu7EOOq7TfLDxoWP5ZGoeq7n/ahtKz6qp8tXM5pvZq/UkJB6vugdp0vbqKJZk17zSs1Sbsh08yB9S16qp8tXPL0vOwkW/ew7IurJOrdjaXnoeNqnn/XubAbpnIgak6ALdknr20B9sabJhj9N93HGlc1dV6GLdlnjn20vOwUTXvX88c4D3GUF1VVTvvbXnOxErPw0bVvH8lM5T7tfyMIF/t3LrS87DBTnguM5RRq6vy1c7ulJ6Hjap5/3RmKE8YUV2VL4avx4Ul9zXQsAMeyQzlRVWtc2b9cZPelJ6HTf1V/3hmKM9rLz3H79t3k6rkvSIzlCb9KT0Pm3ood41zGP0tPQ8bVZ/45jEOo0zpedjUQ7kxM4zypedhg2sbhjF5Ss/DBss2G8YaHFU619YY+Otm1X+0tiCl9G1K6e5Op/NV4UghhBBCCCGEEEIIIYQQQgghhBBCCCGEECaRfwDxFRW63xAIPwAAAABJRU5ErkJggg==)';
	$config['no_amp-link-bg-color'] = '#111';
}
if ( isset($xstore_amp_settings['appearance']['font_color']) && $xstore_amp_settings['appearance']['font_color'] != '' ) {
	$config['font_color'] = $xstore_amp_settings['appearance']['font_color'];
}
if ( isset($xstore_amp_settings['appearance']['heading_color']) && $xstore_amp_settings['appearance']['heading_color'] != '' ) {
	$config['heading_color'] = $xstore_amp_settings['appearance']['heading_color'];
}
if ( isset($xstore_amp_settings['appearance']['active_color']) && $xstore_amp_settings['appearance']['active_color'] != '' ) {
	$config['active_color'] = $xstore_amp_settings['appearance']['active_color'];
}
if ( isset($xstore_amp_settings['appearance']['button_bg_color']) && $xstore_amp_settings['appearance']['button_bg_color'] != '' ) {
	$config['button_bg_color'] = $xstore_amp_settings['appearance']['button_bg_color'];
}
if ( isset($xstore_amp_settings['appearance']['button_color']) && $xstore_amp_settings['appearance']['button_color'] != '' ) {
	$config['button_color'] = $xstore_amp_settings['appearance']['button_color'];
}

if ( isset($xstore_amp_settings['appearance']['header_bg_color']) && $xstore_amp_settings['appearance']['header_bg_color'] != '' ) {
	$config['header_bg_color'] = $xstore_amp_settings['appearance']['header_bg_color'];
}
if ( isset($xstore_amp_settings['appearance']['header_color']) && $xstore_amp_settings['appearance']['header_color'] != '' ) {
	$config['header_color'] = $xstore_amp_settings['appearance']['header_color'];
}
if ( isset($xstore_amp_settings['appearance']['header_height']) && $xstore_amp_settings['appearance']['header_height'] != '' ) {
	$config['header_height'] = $xstore_amp_settings['appearance']['header_height'];
}

if ( isset($xstore_amp_settings['appearance']['mobile_menu_icon_color']) && $xstore_amp_settings['appearance']['mobile_menu_icon_color'] != '' ) {
	$config['mobile_menu_icon_color'] = $xstore_amp_settings['appearance']['mobile_menu_icon_color'];
}

if ( isset($xstore_amp_settings['appearance']['mobile_menu_icon_bg_color']) && $xstore_amp_settings['appearance']['mobile_menu_icon_bg_color'] != '' ) {
	$config['mobile_menu_icon_bg_color'] = $xstore_amp_settings['appearance']['mobile_menu_icon_bg_color'];
}
	
if ( isset($xstore_amp_settings['appearance']['mobile_menu_bg_color']) && $xstore_amp_settings['appearance']['mobile_menu_bg_color'] != '' ) {
	$config['mobile_menu_bg_color'] = $xstore_amp_settings['appearance']['mobile_menu_bg_color'];
}
if ( isset($xstore_amp_settings['appearance']['mobile_menu_color']) && $xstore_amp_settings['appearance']['mobile_menu_color'] != '' ) {
	$config['mobile_menu_color'] = $xstore_amp_settings['appearance']['mobile_menu_color'];
}
if ( isset($xstore_amp_settings['appearance']['mobile_menu_content_zoom']) && $xstore_amp_settings['appearance']['mobile_menu_content_zoom'] != '' ) {
	$config['mobile_menu_content_zoom'] = $xstore_amp_settings['appearance']['mobile_menu_content_zoom'];
}

if ( isset($xstore_amp_settings['appearance']['footer_bg_color']) && $xstore_amp_settings['appearance']['footer_bg_color'] != '' ) {
	$config['footer_bg_color'] = $xstore_amp_settings['appearance']['footer_bg_color'];
}
if ( isset($xstore_amp_settings['appearance']['footer_color']) && $xstore_amp_settings['appearance']['footer_color'] != '' ) {
	$config['footer_color'] = $xstore_amp_settings['appearance']['footer_color'];
}

if ( isset($xstore_amp_settings['appearance']['copyrights_bg_color']) && $xstore_amp_settings['appearance']['copyrights_bg_color'] != '' ) {
	$config['copyrights_bg_color'] = $xstore_amp_settings['appearance']['copyrights_bg_color'];
}
if ( isset($xstore_amp_settings['appearance']['copyrights_color']) && $xstore_amp_settings['appearance']['copyrights_color'] != '' ) {
	$config['copyrights_color'] = $xstore_amp_settings['appearance']['copyrights_color'];
}
?>

body {
--et_amp-body-bg-c: <?php echo $config['body_bg']; ?>;
--et_amp-d-2-w: <?php echo $config['dark']; ?>; <?php // dark 2 white color ?>
--et_amp-w-2-d: <?php echo $config['white']; ?>; <?php // white 2 dark color ?>
--et_amp-f-c: <?php echo $config['font_color']; ?>; <?php // font color ?>
--et_amp-h-c: <?php echo $config['heading_color']; ?>; <?php // headings color ?>
--et_amp-l-c: <?php echo $config['link']; ?>; <?php // links color ?>
--et_amp-a-c: <?php echo $config['active_color']; ?>; <?php // active color ?>
--et_amp-r-c: #c62828; <?php // red color ?>
--et_amp-g-c: #2e7d32; <?php // green color ?>
--et_amp-b-c: #1565c0; <?php // blue color ?>
--et_amp-y-c: #fdd835; <?php // yellow color ?>
--et_amp-br-c: <?php echo $config['border']; ?>; <?php // border color ?>
--et_amp-tr-c: <?php echo $config['transparent'] ?>; <?php // fix issues for safari ?>

--et_amp-select-arrow: <?php echo $config['select_arrow']; ?>;

--et_amp-l-g-c: <?php echo $config['light_grey']; ?>; <?php // light-grey color ?>
--et_amp-l-g-c2: <?php echo $config['light_grey2']; ?>; <?php // light-grey2 color ?>

--et_amp-btn-bg-c: <?php echo $config['button_bg_color']; ?>; <?php // btn background color ?>
--et_amp-btn-c: <?php echo $config['button_color']; ?>; <?php // btn color ?>

--et_amp-hd-bg-c: <?php echo $config['header_bg_color']; ?>; <?php // header background color ?>
--et_amp-hd-color: <?php echo $config['header_color']; ?>; <?php // header color ?>
--et_amp-hd-h: <?php echo $config['header_height']; ?>px; <?php // header height ?>

<?php if ( !empty($config['mobile_menu_icon_color'])) : ?>
    --et_amp-mb-m-i-c: <?php echo $config['mobile_menu_icon_color']; ?>; <?php // mobile menu icon color ?>
<?php endif; ?>
--et_amp-mb-m-i-bg-c: <?php echo $config['mobile_menu_icon_bg_color']; ?>; <?php // mobile menu icon background color ?>
--et_amp-mb-m-bg-c: <?php echo $config['mobile_menu_bg_color']; ?>; <?php // mobile menu background color ?>
--et_amp-mb-m-c: <?php echo $config['mobile_menu_color']; ?>; <?php // mobile menu color ?>

--et_amp-b-sdw-c: <?php echo $config['box_shadow_color']; ?>; <?php // box shadow color ?>

--et_amp-ft-bg-c: <?php echo $config['footer_bg_color']; ?>; <?php // footer background color ?>
--et_amp-ft-c: <?php echo $config['footer_color']; ?>; <?php // footer color ?>

--et_amp-cprts-c: <?php echo $config['copyrights_color']; ?>; <?php // copyrights color ?>
--et_amp-cprts-bg-c: <?php echo $config['copyrights_bg_color']; ?>; <?php // copyrights background color ?>

--et_amp-no-amp-l-bg-c: <?php echo $config['no_amp-link-bg-color']; ?>; <?php // no-amp section bg color ?>
<?php
if ( $xstore_amp_settings['appearance']['font_family'] != 'system' ) {?>
    --et_amp-f-fml: <?php echo $xstore_amp_settings['appearance']['font_family']; ?>; <?php // font-family ?>
<?php }
else { ?>
    --et_amp-f-fml: -apple-system, system-ui,  BlinkMacSystemFont, "Segoe UI", Verdana,  sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
<?php } ?>
}

.mobile-menu {
    --content-zoom: calc(<?php echo $config['mobile_menu_content_zoom']; ?>rem * .01);
}

<?php unset($config);
