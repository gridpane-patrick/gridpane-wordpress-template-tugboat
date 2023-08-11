<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

if (is_plugin_active('wp-seopress/seopress.php')) {
    if (function_exists('seopress_admin_header')) {
        echo seopress_admin_header();
    } ?>

<form method="post"
    action="<?php echo admin_url('options.php'); ?>"
    class="seopress-option">

    <?php
            if (isset($_GET['settings-updated']) && 'true' === $_GET['settings-updated']) { ?>
    <div class="sp-components-snackbar-list">
        <div class="sp-components-snackbar">
            <div class="sp-components-snackbar__content">
                <span class="dashicons dashicons-yes"></span>
                <?php _e('Your settings have been saved.', 'wp-seopress-pro'); ?>
            </div>
        </div>
    </div>
    <?php }

    global $wp_version, $title;
    $current_tab = '';
    $tag         = version_compare($wp_version, '4.4') >= 0 ? 'h1' : 'h2';
    echo '<' . $tag . '>' . $title . '</' . $tag . '>';
    if (is_network_admin() && is_multisite()) {
        settings_fields('seopress_pro_mu_option_group');
    } else {
        settings_fields('seopress_pro_option_group');
    } ?>

    <div id="seopress-tabs" class="wrap">
        <?php
                $plugin_settings_tabs = [
                    'tab_seopress_robots'      => __('robots.txt', 'wp-seopress-pro'),
                    'tab_seopress_htaccess'    => __('.htaccess', 'wp-seopress-pro'),
                    'tab_seopress_white_label' => __('White Label', 'wp-seopress-pro'),
                ];

    if ( ! is_network_admin() && is_multisite()) {
        unset($plugin_settings_tabs['tab_seopress_htaccess'], $plugin_settings_tabs['tab_seopress_white_label']);
    }

    if (defined('SUBDOMAIN_INSTALL') && true === constant('SUBDOMAIN_INSTALL')) {//if subdomains
        unset($plugin_settings_tabs['tab_seopress_robots']);
    }

    echo '<div class="nav-tab-wrapper">';
    foreach ($plugin_settings_tabs as $tab_key => $tab_caption) {
        echo '<a id="' . $tab_key . '-tab" class="nav-tab" href="?page=seopress-network-option#tab=' . $tab_key . '">' . $tab_caption . '</a>';
    }
    echo '</div>'; ?>

        <!-- Robots -->
        <?php if (defined('SUBDOMAIN_INSTALL') && false === constant('SUBDOMAIN_INSTALL')) {//if subdirectories?>
        <div class="seopress-tab <?php if ('tab_seopress_robots' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_robots"><?php do_settings_sections('seopress-mu-settings-admin-robots'); ?>
        </div>
        <?php } ?>

        <!-- htaccess -->
        <div class="seopress-tab <?php if ('tab_seopress_htaccess' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_htaccess"><?php do_settings_sections('seopress-settings-admin-htaccess'); ?>
        </div>

        <!-- white label -->
        <div class="seopress-tab <?php if ('tab_seopress_white_label' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_white_label"><?php do_settings_sections('seopress-mu-settings-admin-white-label'); ?>
        </div>

    </div>
    <!--seopress-tabs-->
    <?php echo $this->seopress_feature_save(); ?>

    <?php sp_submit_button(__('Save changes', 'wp-seopress-pro')); ?>
</form>
<?php
}
