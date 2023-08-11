<?php
if (!defined('ABSPATH')) {
    exit;
}


add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;

    $wp_admin_bar->remove_menu('et-top-bar-general-menu');
    $wp_admin_bar->remove_menu('et-panel-welcome');
    $wp_admin_bar->remove_menu('et-theme-settings');
    $wp_admin_bar->remove_menu('w3tc');
    $wp_admin_bar->remove_menu('w3tc_flush');
    $wp_admin_bar->remove_menu('w3tc_flush_objectcache');
    $wp_admin_bar->remove_menu('new-elementor_library');
    $wp_admin_bar->remove_menu('w3tc_flush_pgcache');
    $wp_admin_bar->remove_menu('autoptimize-cache-info');
    $wp_admin_bar->remove_menu('autoptimize-delete-cache');
    $wp_admin_bar->remove_menu('et-theme-settings');
    $wp_admin_bar->remove_menu('et-settings-general');
    $wp_admin_bar->remove_menu('new-e-landing-page');
    $wp_admin_bar->remove_menu('rank-math');
    $wp_admin_bar->remove_menu('seopress_custom_top_level');
    $wp_admin_bar->remove_menu('trp_edit_translation');
    $wp_admin_bar->add_menu(
        array(
            'title'     => 'مسح الكاش',
            'href'  => '?remove_cash=remove_cash',
        )
    );
});
add_action('init', function () {
    if (isset($_GET['remove_cash']) and $_GET['remove_cash']) {
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        wp_redirect($_SERVER['HTTP_REFERER']);
        exit;
    }
});

add_filter('login_title', function ($login_title) {
    return str_replace(array(' &lsaquo;', ' &#8212; WordPress', __('WordPress'), ' &#8212; ووردبريس', 'ووردبريس'), array(' &lsaquo;', ''), $login_title);
});


add_filter('admin_title', function ($admin_title) {
    return str_replace(array(' &lsaquo;', ' &#8212; WordPress', ('WordPress'), ' &lsaquo;', ' &#8212; ووردبريس'), array(' &lsaquo;', ''), $admin_title);
});
if (!in_array(WAAS1_RESTRICTION_GROUP_ID,[1,6])) {

foreach (array_diff(scandir(WPMU_PLUGIN_DIR . '/json/uip/'), array('.', '..')) as $key => $__uip) {
    add_filter('pre_option_' . basename($__uip, '.json'), function ($plugins) use ($__uip) {
        if (file_exists(WPMU_PLUGIN_DIR . '/json/uip/custom/' . $__uip)) {
            return json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/uip/custom/' . $__uip, true), true);
        } else {

            return json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/uip/' . $__uip, true), true);
        }
    });
}

foreach (array_diff(scandir(WPMU_PLUGIN_DIR . '/json/pys/'), array('.', '..')) as $key => $__uip) {
    add_filter('pre_option_' . basename($__uip, '.json'), function ($plugins) use ($__uip) {
        return json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/pys/' . $__uip, true), true);
    });
}
foreach (array_diff(scandir(WPMU_PLUGIN_DIR . '/json/menu-editor/'), array('.', '..')) as $key => $__editor) {
    add_filter('pre_option_' . basename($__editor, '.json'), function ($plugins) use ($__editor) {
        if (file_exists(WPMU_PLUGIN_DIR . '/json/menu-editor/custom/' . $__editor)) {
            return json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/menu-editor/custom/' . $__editor, true), true);
        } else {

            return json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/menu-editor/' . $__editor, true), true);
        }
    });
}
}
add_action('admin_footer', function () {
    if (!in_array(WAAS1_RESTRICTION_GROUP_ID, [1, 6])) {
        if (current_user_can('administrator')) {
?>
        <script>
  window.intercomSettings = {
    api_base: "https://api-iam.intercom.io",
    app_id: "p2sejd98"
  };
</script>

<script>
// We pre-filled your app ID in the widget URL: 'https://widget.intercom.io/widget/p2sejd98'
(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/p2sejd98';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
</script>
<?php
        }
    }
});
add_filter( 'ywpar_referral_link',function($referral_link, $user_id){

    return str_replace('encrypted-admin','',$referral_link);
 },99,2 );