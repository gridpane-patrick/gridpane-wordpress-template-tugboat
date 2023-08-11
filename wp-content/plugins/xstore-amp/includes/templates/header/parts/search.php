<?php
/**
 * Header search template
 *
 * @package    search.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $xstore_amp_vars;
$action_url =  admin_url('admin-ajax.php?action=xstore_amp_search');
$action_url = preg_replace('#^https?:#', '', $action_url);

$search_page_action = $xstore_amp_vars['is_woocommerce'] ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' );

$search_options = array('products', 'posts');
$active_tab = 'products';
if ( isset($xstore_amp_settings['general']['search_results']) && !empty($xstore_amp_settings['general']['search_results']) ) {
	$search_options = explode(',', $xstore_amp_settings['general']['search_results']);
	foreach ( $search_options as $element_key => $element_name ) {
		if ( !isset($xstore_amp_settings['general'][$element_name.'_visibility']) || !$xstore_amp_settings['general'][$element_name.'_visibility'] ) {
			unset($search_options[$element_key]);
		}
	}
	if ( isset($search_options[0]) ) {
		$active_tab = $search_options[0];
	}
}
?>
<amp-state id="search">
    <script type="application/json">
            {
                "show" : false,
                "action": "<?php echo esc_url($search_page_action); ?>",
                "query" : "",
                "results": [],
                "resultsTabs": [],
                "activeTab": "<?php echo $active_tab; ?>",
                "processing": false,
                "clicked_once" : 0
            }
        </script>
</amp-state>
<span class="h-search inline-block" data-element="search">
	<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width=".9em" height=".9em" viewBox="0 0 24 24" fill="currentColor" role="button" tabindex="-1" on="tap: AMP.setState({'search': {'show' : true}})">
        <path d="M23.784 22.8l-6.168-6.144c1.584-1.848 2.448-4.176 2.448-6.576 0-5.52-4.488-10.032-10.032-10.032-5.52 0-10.008 4.488-10.008 10.008s4.488 10.032 10.032 10.032c2.424 0 4.728-0.864 6.576-2.472l6.168 6.144c0.144 0.144 0.312 0.216 0.48 0.216s0.336-0.072 0.456-0.192c0.144-0.12 0.216-0.288 0.24-0.48 0-0.192-0.072-0.384-0.192-0.504zM18.696 10.080c0 4.752-3.888 8.64-8.664 8.64-4.752 0-8.64-3.888-8.64-8.664 0-4.752 3.888-8.64 8.664-8.64s8.64 3.888 8.64 8.664z"></path>
    </svg>
</span>
