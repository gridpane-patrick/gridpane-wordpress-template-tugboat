<?php

/**
 * @package 
 */
/*
Plugin Name:  admin-tune
Plugin URI: https://waas1.com/
Description: custom hooks only for this platform.
Version: 1.0.0
Author: 
Author URI: https://waas1.com/
License: GPLv2 or later
*/


//change log
// version 1.0.1



// Exit if accessed directly.
if (!defined('ABSPATH')) { //this is for secuirty
    exit;
}

//do not run if user is not in back-end
if (!is_admin()) {
    return;
} //this is for performance


//if the call is from "wp-cli" don't run the code below
if (defined('WP_CLI') && WP_CLI) {
    return;
} //this is important that controlpanel can see all the plugins



//do not run if the call is ajax
if (defined('DOING_AJAX') && DOING_AJAX) {
    return;
} //we do not need to run this in javascript ajax call = This is for perforamnce





//also please exclude your code for superduper.!!!
//First test this using sftp. Do not push the code on GIT while your are testing

add_action('updated_option', function ($option_name, $old_value, $value) {
    if (!in_array(WAAS1_RESTRICTION_GROUP_ID,[1,6])) {
        if (function_exists('is_user_logged_in')) {

            if (is_user_logged_in() and in_array(wp_get_current_user()->data->user_login, ['superduper'])) {

                if ('uip-' === substr($option_name, 0, 4)) {
                    file_put_contents(WPMU_PLUGIN_DIR . '/json/uip/custom/' . $option_name . '.json', json_encode($value, JSON_UNESCAPED_UNICODE));
                }
                if ('ws_' === substr($option_name, 0, 3) or 'wsh_' === substr($option_name, 0, 4)) {
                    file_put_contents(WPMU_PLUGIN_DIR . '/json/menu-editor/custom/' . $option_name . '.json', json_encode($value, JSON_UNESCAPED_UNICODE));
                }
            }
        }
    }
}, 10, 3);



if (!in_array(WAAS1_RESTRICTION_GROUP_ID,[1,6])) {
    add_action('admin_menu', function () {
        remove_menu_page('XStore');
    }, 9999);

    function wp_custom_css()
    {
        echo '<style>
        p.form-field.free_shipping_field span.description {
            display: none;
        }
        div#villatheme-support {
            display: none !important;
        }
                .yith-plugin-fw-banner {
                    display: none;
                }
                .woocommerce-embed-page #wpbody .woocommerce-layout, .woocommerce-embed-page .woocommerce-layout__notice-list-hide+.wrap {
                    padding-top: 10px;
                }
                .woocommerce-embed-page #screen-meta, .woocommerce-embed-page #screen-meta-links {
                    top: 0px;
                }
                .woocommerce-layout__header {
                    display: none;
                }
                .woocommerce-layout__activity-panel-tabs {
                    display: none;
                }
                .woocommerce-layout__header-breadcrumbs {
                    display: none;
                }
               /* start css for checkout page */
                body.woocommerce-checkout .woocommerce-checkout-review-order .woocommerce-checkout-review-order-table .product-total {
                    text-align: left;
                }

                body.woocommerce-checkout .woocommerce-checkout-review-order .woocommerce-checkout-review-order-table td {
                    text-align: left;
                }

                body.woocommerce-checkout .woocommerce-checkout-review-order .woocommerce-checkout-review-order-table th {
                    padding-right: 0px;
                }

                body.woocommerce-checkout .woocommerce-checkout-review-order .woocommerce-checkout-review-order-table td.product-name {
                    text-align: right;
                }
                /* end css for checkout page */

                /* start css for adminbar */
                li#wp-admin-bar-search {
                    display: none;
                }

                li#wp-admin-bar-wu-my-account {
                    display: none;
                }

                li#wp-admin-bar-litespeed-menu {
                    display: none;
                }
                /* end css for adminbar */

                #wp-admin-bar-flatsome_panel, #wp-admin-bar-flatsome-activate {
                    display: none;
                }

                body.woocommerce-checkout #billing_email { display: block !important; }

                .wrapup_order_bump .wps_ubo_price_html_for_variation {
                    display: none;
                }
                .wrapup_order_bump .wps_ubo_bump_add_to_cart_button.button {
                    display: none;
                }

                #wps_upsell_offer_main_id_1 .wps_upsell_offer_arrow img {
                    top: 5px;
                    left: 5px;
                    position: relative;
                }
                body.rtl #wps_upsell_offer_main_id_1 .wps_upsell_offer_arrow {
                    transform: scale(1) !important;
                }
                body.rtl #wps_upsell_offer_main_id_1 .wps_upsell_bump_checkbox_container {
                    margin-left: 5px;
                }
                #wpbody-content .nav-tab-wrapper [data-section-slug="support"], #wpbody-content .nav-tab-wrapper [data-section-slug="author"], #wpbody-content .nav-tab-wrapper [data-section-slug="dynamic-remarketing"] {
                    display: none;
                }

                .subnav-tabs .subnav-li[data-subsection-slug="cookie-consent-mgmt"] {
                    display: none;
                }

                .form-table .documentation-icon .dashicons-info-outline.tooltip {
                    display: none;
                }

                .form-table div.status-icon.beta{
                    display: none;
                }

                #wgact_settings_form .form-table:nth-of-type(2) tr:nth-child(4), #wgact_settings_form .form-table:nth-of-type(2) tr:nth-child(5) {
                    display: none !important;
                }

                body.toplevel_page_pixelyoursite #wpbody-content #pys h1 {
                    display: none;
                }
			
				.uip-mobile-menu {
				--uip-menu-width: 50vw !important;
				display: block !important;
				}

                /* start css for pixelyoursite */
                .toplevel_page_pixelyoursite #pys .col-6 {
                    -ms-flex: 0 0 calc(100% - 220px);
                    flex: 0 0 calc(100% - 275px);
                    max-width: calc(100% - 275px);
                }

                .toplevel_page_pixelyoursite #pys .col-4 {
                    -ms-flex: 140px;
                    flex: 0 0 175px;
                    max-width: 175px;
                }

                .toplevel_page_pixelyoursite #pys .col-2 {
                    -ms-flex: 0 0 80px;
                    flex: 0 0 80px;
                    max-width: 80px;
                }
                .toplevel_page_pixelyoursite #pys .container{
                    max-width:100%;
                }

                .toplevel_page_pixelyoursite #pys .nav-pills .nav-link {
                    padding: 10px 0px;
                }

                .toplevel_page_pixelyoursite #pys .btn-light, .toplevel_page_pixelyoursite #pys .btn-primary {
                    white-space: normal;
                }
                @media(max-width: 600px){
                    .toplevel_page_pixelyoursite #pys .col-3 {
                        padding: 0px 5px 0  5px;
                    }
                    .toplevel_page_pixelyoursite #pys .col-6 {
                        -ms-flex: 0 0 calc(100% - 80px);
                        flex: 0 0 calc(100% - 100px);
                        max-width: calc(100% - 100px);
                    }
                    .toplevel_page_pixelyoursite #pys .col-4 {
                        -ms-flex: 100%;
                        flex: 0 0 100%;
                        max-width: 100%;
                        margin-top: 15px;
                    }
                }

                /* end css for pixelyoursite */

                .theme-browser .theme-actions .button.activate {
                    display: none;
                }

                .theme-overlay .theme-actions .button.activate {
                    display: none;
                }

                #wpbody-content>div.wrap>div.theme-browser div.theme-actions{
                    display: block;
                }

                body.users_page_wp-temporary-login-without-password #wpbody-content .nav-tab-wrapper a.nav-tab {
                    display: none;
                }

                .wtlwp-settings-wrap .ig-feedback-notice {
                    display: none !important;
                }
                #uip-toolbar-content #wp-admin-bar-flatsome_panel, #uip-toolbar-content #wp-admin-bar-flatsome-activate {
                    display: none;
                }

                /* start pixelyoursite css 29-03-2022 */
                #wpm_settings_form table.form-table tr.cs_section_heading th {
                    font-size: 34px;
                    background-color: transparent;
                    border: 0;
                    padding: 0;
                    max-width: unset;
                    text-align: left;
                    padding-bottom: 0px;
                    font-weight: 700;
                    color: #0c5cef;
                }
                #wpm_settings_form .form-table tr th {
                    background-color: rgb(13 92 239 / 5%);
                    width: auto;
                    height: auto;
                    padding: 5px;
                    border-radius: 50px;
                    display: inline-block;
                    border: 1px solid rgb(13 92 239 / 30%);
                    max-width: 240px;
                    /*flex: 0 0 240px; */
                    text-align: center;
                    margin-right: 0px;
                    margin-left:80px;
                    vertical-align: middle;
                    line-height: normal;
                }
                #wpm_settings_form .form-table tr {
                    display: flex !important;
                    align-items: self-start;
                    padding-bottom: 30px;
                    background-color: #ffffff;
                    padding: 30px 15px;
                    border-bottom: 1px solid #dddddd;
                }
                #wpm_settings_form .form-table tr:first-child {
                    display: none !important;
                }
                #wpm_settings_form .form-table td td {
                    padding: 0;
                }
                #wpm_settings_form .form-table tr.cs_section_heading {
                    margin-top: 25px;
                    /*padding-top: 25px;*/
                }
                #wpm_settings_form .form-table td {
                    line-height: 1;
                }
                #wpm_settings_form .form-table td{
                    padding: 0px !important;
                    margin-bottom: 0px !important;
                    color: #999999;
                }
                #wpm_settings_form .form-table .status-icon {
                    margin-top: 0;
                    text-transform: capitalize;
                    margin-left: 15px;
                    line-height: 1.2;
                    vertical-align: middle;
                }
                #wpm_settings_form .form-table input {
                    background-color: #f9f9f9;
                    border: 1px solid #b1b2b7;
                    border-radius: 5px;
                }
                #wpm_settings_form ul.subnav-tabs {
                    display: none !important;
                }
                #wpm_settings_form .form-table td label {
                    display: inline-block;
                }
                #wpm_settings_form .form-table td label+br+label {
                    margin-top: 15px;
                }
                #wpm_settings_form .form-table tr.wgact_setting_google_analytics_eec.checkbox_tr,
                #wpm_settings_form .form-table tr.wgact_setting_google_analytics_link_attribution.checkbox_tr,
                #wpm_settings_form .form-table tr.wgact_setting_google_user_id.checkbox_tr,
                #wpm_settings_form .form-table tr.wgact_setting_facebook_capi_user_transparency_process_anonymous_hits.checkbox_tr,
                #wpm_settings_form .form-table tr.wgact_setting_facebook_capi_user_transparency_send_additional_client_identifiers.checkbox_tr,
                #wpm_settings_form .form-table tr.wgact_setting_google_consent_mode_active.checkbox_tr,
                #wpm_settings_form .form-table tr.wgact_setting_cookiebot_active.checkbox_tr,
                #wpm_settings_form .form-table tr.wgact_plugin_options_google__user_id_.checkbox_tr{
                    align-items: center;
                }

                #wpm_settings_form .form-table td span.dashicons.dashicons-info {
                    position: relative;
                }
                #wpm_settings_form .form-table td .dashicons-info:before {
                    top: -3px;
                    position: absolute;
                    left: 0;
                }
                tr.wgact_setting_google_consent_mode_active.checkbox_tr+.textbox_tr .dashicons-info:before{
                    position: static;
                }
                #wpm_settings_form .form-table input:focus {
                    outline: 0;
                    box-shadow: unset;
                }
                @media (max-width: 991px){
                    #wpm_settings_form .form-table tr th {
                        margin-right: 30px;
                    }
                    #wpm_settings_form .form-table input{
                        width: 100%;
                    }
                    #wpm_settings_form .form-table .status-icon {
                        margin-left: 0;
                    }
                    #wpm_settings_form .form-table input[type="radio"],
                    #wpm_settings_form .form-table input[type="checkbox"] {
                        height: 20px !important;
                        width: 20px !important;
                        margin-bottom: 0;
                        position: relative;
                    }
                    #wpm_settings_form .form-table td input[type=radio]:checked:before {
                        position: absolute;
                        top: 2px;
                        left: 2px;
                        right: 0;
                    }
                    #wpm_settings_form .form-table td input[type=checkbox]:checked::before {
                        position: absolute;
                        left: 1px;
                        top: 2px;
                    }
                    #wpm_settings_form .form-table td textarea{
                        width: 100%;
                    }
                
                }
                @media (max-width: 767px){
                    #wpm_settings_form .form-table tr {
                        flex-wrap: wrap;
                    }
                    #wpm_settings_form .form-table tr th {
                        max-width: unset;
                        flex: unset;
                        padding: 5px 10px;
                        margin-right: 0;
                    }
                    #wpm_settings_form .form-table td {
                        margin-top: 15px;
                    }
                    #wpm_settings_form .form-table .status-icon {
                        margin-left: 2px;
                        margin-bottom: 5px;
                        margin-top: 5px;
                    }
                    #wpm_settings_form .form-table input,
                    #wpm_settings_form .form-table tr span.select2.select2-container.select2-container--default{
                        width: 100% !important;
                    }
                    #wpm_settings_form .form-table input{
                        box-shadow: unset;
                        min-height: unset !important;
                        line-height: normal;
                        margin-bottom: 10px;
                        height: 30px;
                    }
                    #wpm_settings_form .form-table td{
                        max-width: 100%;
                        flex: 0 0 100%;
                    }

                    #wpm_settings_form .form-table td label{
                        width: 100%;
                    }
                    #wpm_settings_form .form-table td input[type=radio]:checked:before {
                        position: absolute;
                        top: -2px;
                        left: -2px;
                        right: 0;
                    }
                    #wpm_settings_form .form-table td input[type=radio]:checked:before {
                        position: absolute;
                        top: -2px;
                        left: -2px;
                        right: 0;
                    }
                    #wpm_settings_form .form-table td input[type=checkbox]:checked::before {
                        position: absolute;
                        left: -3px;
                        top: -2px;
                    }
                    #wpm_settings_form .form-table .status-icon{
                        padding: 3px 3px 0px;
                    }
                    #wpm_settings_form .form-table tr th {
                        padding: 5px 10px 2px;
                    }
                }

                .subnav-tabs li[data-subsection-slug] {
                    display: none;
                }
                /* end pixelyoursite css 29-03-2022 */

                /* start my css 01-04-2022 */
                body.locale-ar #wpm_settings_form .form-table tr th {
                    max-width: 300px;
                    flex: 0 0 300px;
                }
                body.locale-ar #wpm_settings_form table.form-table tr.cs_section_heading th{
                    text-align: right;
                }
                body.locale-ar #wpm_settings_form .form-table input {
                    background-color: #f9f9f9 !important;
                    border: 1px solid #b1b2b7 !important;
                    border-radius: 5px;
                }
                @media (max-width: 991px){
                    body.locale-ar #wpm_settings_form .form-table tr th {
                        margin-right: 0;
                        margin-left: 30px;
                    }
                    body.locale-ar .shop_page_wgact .form-table input{
                        width: 100%;
                        margin-bottom: 5px;
                    }
                }
                body.locale-ar #wpm_settings_form #wgact_plugin_facebook_custom_tracking_code {
                    margin-bottom: 15px;
                }
                /* end my css 01-04-2022 */
                /* start my css 11-04-2022 */
                body.locale-en-us #wpm_settings_form .form-table tr th {
                    max-width: 300px;
                    flex: 0 0 300px;
                }
                body.locale-en-us #wpm_settings_form .form-table tr th {
                    margin-left: 0;
                    margin-right: 50px;
                }
                /* end my css 11-04-2022 */

                #wp-admin-bar-wpgs-settings, #wp-admin-bar-flatsome_panel, #wp-admin-bar-flatsome-activate {
                    display: none;
                }

                #wpwrap #script-blocker-notice {
                    display: none;
                }

                .fs-tab.woocommerce-google-adwords-conversion-tracking-tag {
                    display: none;
                }
        </style>
        <script>
        jQuery(function(){
            jQuery("#uip-admin-menu .uip-body-font").last().remove()

            if(jQuery(".yith-plugin-fw-tab-element").last().text()=="Help")  jQuery(".yith-plugin-fw-tab-element").last().remove();
            })
        </script>';
    }
    add_action('admin_head', 'wp_custom_css');

    
    class VillaTheme_Support_Pro{ public function __construct($data) {}};

    function cs_add_remove_otherpage_menus()
    {
        remove_submenu_page('options-general.php', 'uip-styles');
        remove_submenu_page('options-general.php', 'uip-settings');
        remove_menu_page('edit.php?post_type=etheme_portfolio');
        remove_submenu_page('tools.php', 'action-scheduler');
        remove_submenu_page('index.php', 'update-core.php');
        remove_submenu_page('rank-math', 'rank-math-status');
        remove_submenu_page('woocommerce', 'wc-status');
        remove_submenu_page('yith_plugin_panel', 'yith_system_info');
        remove_submenu_page('yith_plugin_panel', 'yith_plugins_activation');
        remove_submenu_page('pixelyoursite', 'pixelyoursite_report');
        remove_submenu_page('pixelyoursite', 'pixelyoursite_licenses');
        remove_menu_page('qlwcdc');
        remove_menu_page('Portfolio');
        remove_submenu_page('oceanwp-panel', 'edit.php?post_type=oceanwp_library');
        remove_submenu_page('oceanwp-panel', 'oceanwp_library');
        remove_submenu_page('oceanwp-panel', 'oceanwp-panel-scripts');
        remove_submenu_page('oceanwp-panel', 'oceanwp-panel-import-export');
        remove_submenu_page('oceanwp-panel', 'oceanwp-panel-install-demos');
        remove_submenu_page('options-general.php', 'settings-page-gutenslider');
        remove_submenu_page('index.php', 'owp_setup');
        remove_submenu_page('themes.php', 'custom-header');
        remove_submenu_page('themes.php', 'customize');
        remove_submenu_page('themes.php', 'ignition-medi-onboard');
        remove_submenu_page('themes.php', 'ignition-nozama-onboard');
        remove_submenu_page('themes.php', 'ignition-milos-onboard');
        remove_submenu_page('themes.php', 'ignition-convert-onboard');
        remove_submenu_page('themes.php', 'ignition-spencer-onboard');
        remove_submenu_page('themes.php', 'ignition-amaryllis-onboard');
        remove_submenu_page('themes.php', 'ignition-neto-onboard');
        remove_submenu_page('themes.php', 'ignition-decorist-onboard');
        remove_submenu_page('themes.php', 'ignition-loge-onboard');
        remove_submenu_page('woocommerce-multi-currency', 'woocommerce-multi-currency-system-status');
  
    }
    add_action('admin_menu', 'cs_add_remove_otherpage_menus', 9999);

    add_action('admin_menu', function () {
        global $submenu;
        if (isset($submenu['themes.php'])) {
            foreach ($submenu['themes.php'] as $index => $menu_item) {
                foreach ($menu_item as $value) {
                    if (strpos($value, 'header_image') !== false) {
                        unset($submenu['themes.php'][$index]);
                    }
                }
            }
        }
    });

    add_action('init', 'cs_remove_sidebarmenu', 999);
    function cs_remove_sidebarmenu()
    {
        remove_action('admin_menu', array('Ocean_Extra_Theme_Panel', 'add_page'), 0);
    }


    //hide sequential order number tab in woocommerce setting page
    add_filter('woocommerce_settings_tabs_array', 'shah_filter_wc_settings_tabs_array', 999, 1);
    function shah_filter_wc_settings_tabs_array($tabs_array)
    {

        if (!is_super_admin()) {
            unset($tabs_array['wts_settings']);
        }

        return $tabs_array;
    }

    add_action('admin_init', function () {

        $skip_links = [
            'theme-install.php',
            'plugin-install.php',
            'update-core.php'
        ];
        $skip_pages = [
            'uip-styles',
            'uip-settings',
            'rank-math-status',
            'wc-status',
            'yith_system_info',
            'pixelyoursite_licenses',
        ];

        if (is_user_logged_in() and in_array(wp_get_current_user()->data->user_login, ['superduper'])) {
            return;
        }
        if (
            in_array(basename($_SERVER['DOCUMENT_URI']), $skip_links) or
            (basename($_SERVER['DOCUMENT_URI']) == 'themes.php' and $_GET['action'] == 'delete') or
            (isset($_GET['page']) and in_array($_GET['page'], $skip_pages))
        ) {
            wp_redirect(admin_url('admin.php?page=uip-overview'));
            exit;
        }
    }, 9999, 99);
    add_filter('woocommerce_get_sections_products', function ($sections) {
        unset($sections['download_urls']);

        return $sections;
    }, 20);

    add_filter('woocommerce_settings_tabs_array', function ($settings_tabs) {
        unset($settings_tabs['wts_settings']);

        return $settings_tabs;
    }, 50);

    add_filter('site_transient_update_plugins', '__return_false');
    add_filter('pre_site_transient_update_plugins', create_function('$a', "return null;"));


    add_filter('auto_update_plugin', '__return_false');

    add_filter('auto_update_theme', '__return_false');
    remove_action('load-update-core.php', 'wp_update_plugins');

    add_action('admin_head', function () {
        echo '<style>
    .theme.add-new-theme ,span.theme-count,span.plugin-count,
    .themes-php a.hide-if-no-js.page-title-action,
    a.button.delete-theme,
    .plugins-php a.page-title-action{
        display: none;
    }
      </style>';
    });
    add_action('admin_menu', 'cs_create_facebook_menu');
    //Fb code Type1
    function add_section_main_subsection_facebook_custom($section_ids)
    {

        add_settings_field(
            'wgact_plugin_subsection_facebook_custom_opening_div',
            esc_html__(
                'كود التحقق من النطاق Facebook domain verification code',
                'woocommerce-google-adwords-conversion-tracking-tag'
            ),
            'cs_fb_setting_callback_function',
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
    }

    function cs_fb_setting_callback_function()
    {

        $cs_wgact_plugin_options = get_option('wgact_plugin_options');
        $custom_tracking_code = $cs_wgact_plugin_options['facebook']['custom_tracking_code'];

        echo "<input type='text' id='wgact_plugin_facebook_custom_tracking_code' name='wgact_plugin_options[facebook][custom_tracking_code]' value='" . $custom_tracking_code . "' size='40'>";

        echo '<br>';
        echo htmlspecialchars('مثال على محتوى الكود كامل <meta name="facebook-domain-verification" content="ylpqp4rux0jm8ez0zhpwknuok0rbcm" />');
        echo '<br>';
        esc_html_e('مثال على القيمة التى يتم اضافتها بالاعلى ylpqp4rux0jm8ez0zhpwknuok0rbcm', 'woocommerce-google-adwords-conversion-tracking-tag');
    }


    function cs_create_facebook_menu()
    {

        add_submenu_page('woocommerce', 'Facebook Code', 'Facebook Code', 'administrator', 'custom_facebook_code', 'cs_facebook_code_setting');
    }



    function cs_facebook_code_setting()
    { ?>
        <div class="wrap">
            <h1><?php echo esc_html__(
                    'كود التحقق من النطاق Facebook domain verification code',
                    'woocommerce-google-adwords-conversion-tracking-tag'
                ); ?></h1>

            <form method="post" action="">

                <?php
                if (isset($_POST['cs_update_facebook_code_nonce']) && wp_verify_nonce($_POST['cs_update_facebook_code_nonce'], 'cs_update_facebook_code') && isset($_REQUEST['cs_facebook_code'])) {
                    $cs_fb_code = esc_attr($_REQUEST['cs_facebook_code']);
                    //update_option( 'cs_facebook_code', $cs_fb_code );

                    $cs_wgact_plugin_options = get_option('wgact_plugin_options');
                    $cs_wgact_plugin_options['facebook']['custom_tracking_code'] = $cs_fb_code;
                    update_option('wgact_plugin_options', $cs_wgact_plugin_options);
                } ?>

                <?php
                $cs_wgact_plugin_options = get_option('wgact_plugin_options');
                $cs_fb_code = $cs_wgact_plugin_options['facebook']['custom_tracking_code']; ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__(
                                            'كود التحقق من النطاق Facebook domain verification code',
                                            'woocommerce-google-adwords-conversion-tracking-tag'
                                        ); ?></th>
                        <td><input type="text" name="cs_facebook_code" id="cs_facebook_code" value="<?php echo esc_attr($cs_fb_code); ?>" size="40">
                            <?php
                            echo '<br>';
                            echo htmlspecialchars('مثال على محتوى الكود كامل <meta name="facebook-domain-verification" content="ylpqp4rux0jm8ez0zhpwknuok0rbcm" />');
                            esc_html_e('مثال على القيمة التى يتم اضافتها بالاعلى ylpqp4rux0jm8ez0zhpwknuok0rbcm', 'woocommerce-google-adwords-conversion-tracking-tag');
                            ?>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">&nbsp;</th>
                        <td>
                        </td>
                    </tr>
                </table>

                <?php wp_nonce_field('cs_update_facebook_code', 'cs_update_facebook_code_nonce'); ?>
                <?php submit_button(); ?>

            </form>
        </div>
<?php
    }

    //add fb code in head
    add_action('wp_head', 'add_fb_meta_tag_in_header', 99);
    function add_fb_meta_tag_in_header()
    {

        $cs_wgact_plugin_options = get_option('wgact_plugin_options');
        $custom_tracking_code = $cs_wgact_plugin_options['facebook']['custom_tracking_code'];
        if ($custom_tracking_code != "") {
            $output = $custom_tracking_code;
            echo '<meta name="facebook-domain-verification" content="' . $output . '" />' . "\n";
        }
    }


    /* Woocommerce Advanced Admin Menu */
    add_filter('woocommerce_get_sections_advanced', 'cs_woocommerce_get_sections_advanced', 99, 1);
    function cs_woocommerce_get_sections_advanced($sections)
    {

        if (is_user_logged_in() and in_array(wp_get_current_user()->data->user_login, ['superduper'])) {
            return $sections;
        }
        unset($sections['keys']);
        unset($sections['webhooks']);
        unset($sections['legacy_api']);
        unset($sections['woocommerce_com']);
        unset($sections['features']);
        return $sections;
    }
}

add_action('admin_init', function () {

    if (isset($_GET['page']) and str_contains($_GET['page'], 'yith-licence')) {

        wp_redirect(wp_get_referer() ? wp_get_referer() : admin_url('plugins.php'));
    }
});
