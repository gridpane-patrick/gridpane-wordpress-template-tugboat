<?php
/**
 * Mobile panel template
 *
 * @package    mobile-panel.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_vars, $xstore_amp_icons, $xstore_amp_settings;

$xstore_amp = XStore_AMP::get_instance();

$element_options = array();

$element_options['current_link'] = get_permalink(get_queried_object_id());
// default elements from stretch
$element_options['elements'] = array(
	'home',
	'shop',
	'blog',
	'cart',
	'account',
	'mobile_menu'
);
if ( !empty($xstore_amp_settings['mobile_panel']['mobile_panel_elements']) ) {
	$element_options['elements'] = explode(',', $xstore_amp_settings['mobile_panel']['mobile_panel_elements']);
	foreach ( $element_options['elements'] as $element_key => $element_name ) {
		if ( !isset($xstore_amp_settings['mobile_panel'][$element_name.'_visibility']) || !$xstore_amp_settings['mobile_panel'][$element_name.'_visibility'] ) {
			unset($element_options['elements'][$element_key]);
		}
	}
}
$element_options['backup_home_url'] = home_url();
$element_options['elements_settings'] = array(
	'links' => array(
		'shop' => $xstore_amp_vars['is_woocommerce'] ? get_permalink(wc_get_page_id('shop')) : $element_options['backup_home_url'],
		'blog' => get_permalink($xstore_amp_vars['blog_id']),
		'home' => $element_options['backup_home_url'],
		'portfolio' => ( get_theme_mod('portfolio_projects', 1) && get_theme_mod( 'portfolio_page', '' ) != '' ) ? get_permalink(get_theme_mod( 'portfolio_page', '' )) : $element_options['backup_home_url'],
		'cart' => $xstore_amp_vars['is_woocommerce'] ? wc_get_cart_url() : $element_options['backup_home_url'],
        'account' => $xstore_amp_vars['is_woocommerce'] ? get_permalink(wc_get_page_id( 'myaccount' )) : $element_options['backup_home_url']
	),
	'icons' => array(
        'shop' => $xstore_amp_icons['et_icon-shop'],
        'blog' => $xstore_amp_icons['et_icon-calendar'],
        'home' => $xstore_amp_icons['et_icon-home'],
        'portfolio' => $xstore_amp_icons['et_icon-internet'],
        'cart' => $xstore_amp_icons['et_icon-shopping-bag'],
        'account' => $xstore_amp_icons['et_icon-account'],
        'mobile_menu' => $xstore_amp_icons['et_icon-burger']
    ),
	'labels' => array(
		'shop' => esc_html__('Shop', 'xstore-amp'),
		'blog' => esc_html__('Blog', 'xstore-amp'),
		'portfolio' => esc_html__('Portfolio', 'xstore-amp'),
		'cart' => esc_html__('Cart', 'xstore-amp'),
		'home' => esc_html__('Home', 'xstore-amp'),
		'account' => esc_html__('Account', 'xstore-amp'),
		'mobile_menu' => esc_html__('Menu', 'xstore-amp')
	),
);
//$element_options['icons'] = array_merge($xstore_amp_icons, $et_social_icons['type1']);
//$element_options['icons'] = $xstore_amp_icons;

$has_mobile_menu = false;

$mobile_menu = $mobile_panel_menu = isset($xstore_amp_settings['general']['menu']) && $xstore_amp_settings['general']['menu'] > 0 ? $xstore_amp_settings['general']['menu'] : 'main-menu';
if ( isset($xstore_amp_settings['mobile_panel']['menu']) ) {
    if ( $xstore_amp_settings['mobile_panel']['menu'] != 'inherit' && $xstore_amp_settings['mobile_panel']['menu'] > 0) {
	    $mobile_panel_menu = $xstore_amp_settings['mobile_panel']['menu'];
    }
}

//var_dump($mobile_panel_menu);

?>
<div class="mobile-panel-wrapper pos-fixed with-shadow" id="mobile-panel-wrapper">
	<div class="amp-container">
		<div class="mobile-panel flex-row">
		<?php
			foreach ( $element_options['elements'] as $key ) {
				$attr = array();
				$link = $element_options['elements_settings']['links']['home'];
				$link = $element_options['elements_settings']['links'][$key] ?? $link;
				$label = esc_html__('Item', 'xstore-amp');
				$label = $element_options['elements_settings']['labels'][$key] ?? $label;
				$icon = $element_options['elements_settings']['icons'][$key] ?? $xstore_amp_icons['et_icon-star'];
				$is_active = false;
				switch ($key) {
					case 'cart':
//						$icon = $xstore_amp_icons['et_icon-shopping-cart'];
						$link = '';
						$attr[] = 'on="tap:cartCanvas.open"';
						$attr[] = 'role="button"';
						$attr[] = 'tabindex="-1"';
						if ( $xstore_amp_vars['is_woocommerce'] ) {
							if ( is_cart() ) {
								$is_active = true;
							}
						}
						break;
					case 'shop':
//						$icon = $xstore_amp_icons['et_icon-shop'];
						if ( $xstore_amp_vars['is_woocommerce'] ) {
							if ( is_shop() ) {
								$is_active = true;
							}
						}
						break;
					case 'home':
//						$icon = $xstore_amp_icons['et_icon-home'];
						if ( is_front_page() ) {
							$is_active = true;
						}
						break;
					case 'mobile_menu':
						$has_mobile_menu = true;
						if ( $mobile_panel_menu != $mobile_menu) {
							$attr[] = 'on="tap:mobilePanelMenu.open"';
                        }
						else {
							$attr[] = 'on="tap:mobileMenu.open"';
                        }
//						$icon = $xstore_amp_icons['et_icon-burger'];
						$link = '';
						$attr[] = 'role="button"';
						$attr[] = 'tabindex="-1"';
						break;
//                    case 'account':
//                        if ( $xstore_amp_settings['is_woocommerce'] ) {
//                            if ( is_account_page() ) {
//                                $is_active = true;
//                            }
//                        }
////	                    $icon = $xstore_amp_icons['et_icon-burger'];
//                        break;
					default:
//						$icon = $xstore_amp_icons['et_icon-star'];
						if ( $link != '') {
							$local_link = $element_options['current_link'];
							
							if ( is_tax() ) {
								global $wp_query;
								$obj = $wp_query->get_queried_object();
								$local_link = get_term_link($obj);
							}
							
							if ( strpos( $local_link, substr( $link, 0, -2 ) ) !== false ) {
								$is_active = true;
							}
						}
						break;
				}
//                if ( $key == 'blog') {
//                    $icon = $xstore_amp_icons['et_icon-calendar'];
//                }
				?>
				<div class="flex-column inline-flex <?php echo $key . '-element'; ?> <?php if ($is_active) : ?>active<?php endif; ?>" <?php echo implode(' ', $attr); ?>>
					<a class="currentColor flex flex-col text-center align-items-center" <?php if ($link) { ?>href="<?php echo esc_url($link); ?>"<?php } ?>>
						<?php
                            if ( $key == 'cart' ) {
	                            $icon .= $xstore_amp->cartCount();
                            }
							echo '<span class="et_b-icon">' . $icon . '</span>';
							echo '<span class="text-nowrap">' . $label . '</span>';
						?>
					</a>
				</div>
			<?php }
		?>
	</div>
	</div>
</div>

<?php
    if ( $has_mobile_menu ) {
//        var_dump($mobile_panel_menu);
//        var_dump($mobile_menu);
	    if ( $mobile_panel_menu != $mobile_menu ) {
            include_once XStore_AMP_TEMPLATES_PATH . 'extra/mobile_panel_menu_sidebar.php';
	    }
    }
?>