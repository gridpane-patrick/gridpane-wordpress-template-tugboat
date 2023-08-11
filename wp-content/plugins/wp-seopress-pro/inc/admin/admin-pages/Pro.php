<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

if (is_network_admin() && is_multisite()) {
    $this->options = get_option('seopress_pro_mu_option_name');
} else {
    $this->options = get_option('seopress_pro_option_name');
}

if (is_plugin_active('wp-seopress/seopress.php')) {
    if (function_exists('seopress_admin_header')) {
        echo seopress_admin_header();
    }
} ?>
<form method="post"
    action="<?php echo admin_url('options.php'); ?>"
    class="seopress-option">
    <?php
        $current_tab = '';

        echo $this->seopress_feature_title(null);

    if (is_network_admin() && is_multisite()) {
        settings_fields('seopress_pro_mu_option_group');
    } else {
        settings_fields('seopress_pro_option_group');
    } ?>

    <div id="seopress-tabs" class="wrap">
        <?php
                $plugin_settings_tabs = [
                    'tab_seopress_local_business' => __('Local Business', 'wp-seopress-pro'),
                    'tab_seopress_dublin_core'    => __('Dublin Core', 'wp-seopress-pro'),
                    'tab_seopress_rich_snippets'  => __('Structured Data Types', 'wp-seopress-pro'),
                    'tab_seopress_breadcrumbs'    => __('Breadcrumbs', 'wp-seopress-pro'),
                    'tab_seopress_woocommerce'    => __('WooCommerce', 'wp-seopress-pro'),
                    'tab_seopress_edd'            => __('Easy Digital Downloads', 'wp-seopress-pro'),
                    'tab_seopress_page_speed'     => __('PageSpeed Insights', 'wp-seopress-pro'),
                    'tab_seopress_inspect_url'    => __('Google Inspect URL', 'wp-seopress-pro'),
                    'tab_seopress_robots'         => __('robots.txt', 'wp-seopress-pro'),
                    'tab_seopress_news'           => __('Google News', 'wp-seopress-pro'),
                    'tab_seopress_404'            => __('Redirections / 404', 'wp-seopress-pro'),
                    'tab_seopress_htaccess'       => __('.htaccess', 'wp-seopress-pro'),
                    'tab_seopress_rss'            => __('RSS', 'wp-seopress-pro'),
                    'tab_seopress_rewrite'        => __('URL Rewriting', 'wp-seopress-pro'),
                    'tab_seopress_white_label'    => __('White Label', 'wp-seopress-pro'),
                ];

    if (defined('SUBDOMAIN_INSTALL') && false === constant('SUBDOMAIN_INSTALL')) {//if multisite subdirectories
        unset($plugin_settings_tabs['tab_seopress_robots'], $plugin_settings_tabs['tab_seopress_htaccess'], $plugin_settings_tabs['tab_seopress_white_label']);
    }

    $plugin_settings_tabs = apply_filters('seopress_remove_pro_settings_tabs', $plugin_settings_tabs);

    echo '<div class="nav-tab-wrapper">';
    foreach ($plugin_settings_tabs as $tab_key => $tab_caption) {
        echo '<a id="' . $tab_key . '-tab" class="nav-tab" href="?page=seopress-pro-page#tab=' . $tab_key . '">' . $tab_caption . '</a>';
    }
    echo '</div>'; ?>

        <!-- Local Business -->
        <div class="seopress-tab <?php if ('tab_seopress_local_business' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_local_business">
            <?php do_settings_sections('seopress-settings-admin-local-business'); ?>
        </div>

        <!-- WooCommerce -->
        <div class="seopress-tab <?php if ('tab_seopress_woocommerce' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_woocommerce">
            <?php do_settings_sections('seopress-settings-admin-woocommerce'); ?>
        </div>

        <!-- Easy Digital Downloads -->
        <div class="seopress-tab <?php if ('tab_seopress_edd' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_edd">
            <?php do_settings_sections('seopress-settings-admin-edd'); ?>
        </div>

        <!-- Dublin Core -->
        <div class="seopress-tab <?php if ('tab_seopress_dublin_core' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_dublin_core">
            <?php do_settings_sections('seopress-settings-admin-dublin-core'); ?>
        </div>

        <!-- Structured Data Types -->
        <div class="seopress-tab <?php if ('tab_seopress_rich_snippets' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_rich_snippets">
            <?php do_settings_sections('seopress-settings-admin-rich-snippets'); ?>
        </div>

        <!-- Breadcrumbs -->
        <div class="seopress-tab <?php if ('tab_seopress_breadcrumbs' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_breadcrumbs"><?php do_settings_sections('seopress-settings-admin-breadcrumbs'); ?>
        </div>

        <!-- Google Page Speed -->
        <div class="seopress-tab <?php if ('tab_seopress_page_speed' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_page_speed"><?php do_settings_sections('seopress-settings-admin-page-speed'); ?>
        </div>

        <!-- Google Inspect URL -->
        <div class="seopress-tab <?php if ('tab_seopress_inspect_url' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_inspect_url"><?php do_settings_sections('seopress-settings-admin-inspect-url'); ?>
        </div>

        <!-- Robots -->
        <?php if (! defined('SUBDOMAIN_INSTALL') || (defined('SUBDOMAIN_INSTALL') && true === constant('SUBDOMAIN_INSTALL'))) { //if multisite sub-domains?>
        <div class="seopress-tab <?php if ('tab_seopress_robots' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_robots"><?php do_settings_sections('seopress-settings-admin-robots'); ?>
        </div>
        <?php } ?>

        <!-- Google News Sitemap -->
        <div class="seopress-tab <?php if ('tab_seopress_news' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_news">
            <?php if ('0' == seopress_get_toggle_option('xml-sitemap')) { ?>
            <div class="seopress-notice is-error">
                <p>
                    <?php _e('You need to enable XML Sitemap feature, in order to use Google News Sitemap.', 'wp-seopress-pro'); ?>
                    <a
                        href="<?php echo admin_url('admin.php?page=seopress-xml-sitemap'); ?>">
                        <?php _e('Change this settings', 'wp-seopress-pro'); ?>
                    </a>
                </p>
            </div>
            <?php
    } ?>
            <?php do_settings_sections('seopress-settings-admin-news'); ?>
        </div>

        <!-- 404 -->
        <div class="seopress-tab <?php if ('tab_seopress_404' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_404"><?php do_settings_sections('seopress-settings-admin-monitor-404'); ?>
        </div>

        <!-- htaccess -->
        <?php if (! is_multisite()) { ?>
        <div class="seopress-tab <?php if ('tab_seopress_htaccess' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_htaccess"><?php do_settings_sections('seopress-settings-admin-htaccess'); ?>
        </div>
        <?php } ?>

        <!-- RSS -->
        <div class="seopress-tab <?php if ('tab_seopress_rss' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_rss"><?php do_settings_sections('seopress-settings-admin-rss'); ?>
        </div>

        <!-- Rewrite -->
        <div class="seopress-tab <?php if ('tab_seopress_rewrite' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_rewrite"><?php do_settings_sections('seopress-settings-admin-rewrite'); ?>
        </div>

        <!-- White Label -->
        <?php if (! is_multisite()) { ?>
        <div class="seopress-tab <?php if ('tab_seopress_white_label' == $current_tab) {
        echo 'active';
    } ?>" id="tab_seopress_white_label"><?php do_settings_sections('seopress-settings-admin-white-label'); ?>
        </div>
        <?php } ?>

    </div>
    <!--seopress-tabs-->
    <?php echo $this->seopress_feature_save(); ?>
    <?php sp_submit_button(__('Save changes', 'wp-seopress-pro')); ?>
</form>
<?php
