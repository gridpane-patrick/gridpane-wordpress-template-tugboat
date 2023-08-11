<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_white_label_admin_header_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');

        $check = isset($options['seopress_mu_white_label_admin_header']); ?>

<label for="seopress_mu_white_label_admin_header">
    <input id="seopress_mu_white_label_admin_header"
        name="seopress_pro_mu_option_name[seopress_mu_white_label_admin_header]" type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>
    <?php _e('Remove the SEOPress admin header including Notifications Center, SEO tools, Latest SEO News, Onboarding and Useful links', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_mu_white_label_admin_header'])) {
            esc_attr($options['seopress_mu_white_label_admin_header']);
        }
    } else {
        $options = get_option('seopress_pro_option_name');

        $check = isset($options['seopress_white_label_admin_header']); ?>

<label for="seopress_white_label_admin_header">
    <input id="seopress_white_label_admin_header" name="seopress_pro_option_name[seopress_white_label_admin_header]"
        type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>
    <?php _e('Remove the SEOPress admin header including Notifications Center, SEO tools, Latest SEO News, Onboarding and Useful links', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_white_label_admin_header'])) {
            esc_attr($options['seopress_white_label_admin_header']);
        }
    }
}

function seopress_white_label_admin_notices_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');

        $check = isset($options['seopress_mu_white_label_admin_notices']); ?>

<label for="seopress_mu_white_label_admin_notices">
    <input id="seopress_mu_white_label_admin_notices"
        name="seopress_pro_mu_option_name[seopress_mu_white_label_admin_notices]" type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>
    <?php _e('Remove SEOPress icons on the right in header (changelog, YouTube, Twitter...)', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_mu_white_label_admin_notices'])) {
            esc_attr($options['seopress_mu_white_label_admin_notices']);
        }
    } else {
        $options = get_option('seopress_pro_option_name');

        $check = isset($options['seopress_white_label_admin_notices']); ?>

<label for="seopress_white_label_admin_notices">
    <input id="seopress_white_label_admin_notices" name="seopress_pro_option_name[seopress_white_label_admin_notices]"
        type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>
    <?php _e('Remove SEOPress icons on the right in header (changelog, YouTube, Twitter...)', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_white_label_admin_notices'])) {
            esc_attr($options['seopress_white_label_admin_notices']);
        }
    }
}

function seopress_white_label_admin_menu_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');
        $check = isset($options['seopress_mu_white_label_admin_menu']) ? esc_attr($options['seopress_mu_white_label_admin_menu']) : null; ?>

<input type="text" name="seopress_pro_mu_option_name[seopress_mu_white_label_admin_menu]"
    placeholder="<?php esc_html_e('Enter your dashicons CSS class name', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('CSS Dashicons class name without quotes', 'wp-seopress-pro'); ?>"
    value="<?php echo esc_attr($check); ?>" />
<?php
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_admin_menu']) ? esc_attr($options['seopress_white_label_admin_menu']) : null; ?>
<input type="text" name="seopress_pro_option_name[seopress_white_label_admin_menu]"
    placeholder="<?php esc_html_e('Enter your dashicons CSS class name', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('CSS Dashicons class name without quotes', 'wp-seopress-pro'); ?>"
    value="<?php echo esc_attr($check); ?>" />
<?php
    } ?>

<p class="description">
    <a class="seopress-help" href="https://developer.wordpress.org/resource/dashicons/" target="_blank">
        <?php _e('Find your Dashicons CSS class name on the official website', 'wp-seopress-pro'); ?>
    </a>
    <span class="seopress-help dashicons dashicons-external"></span>
</p>

<?php
}

function seopress_white_label_admin_bar_icon_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');
        $check = isset($options['seopress_mu_white_label_admin_bar_icon']) ? esc_attr($options['seopress_mu_white_label_admin_bar_icon']) : null; ?>

<input type="text" name="seopress_pro_mu_option_name[seopress_mu_white_label_admin_bar_icon]"
    placeholder="<?php esc_html_e('default value: <span class="ab-icon icon-seopress-seopress"></span> SEO', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter the label of the link for admin bar', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />
<?php
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_admin_bar_icon']) ? esc_attr($options['seopress_white_label_admin_bar_icon']) : null; ?>
<input type="text" name="seopress_pro_option_name[seopress_white_label_admin_bar_icon]"
    placeholder="<?php esc_html_e('default value: <span class="ab-icon icon-seopress-seopress"></span> SEO', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter the label of the link for admin bar', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />
<?php
    }
}

function seopress_white_label_admin_title_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');
        $check = isset($options['seopress_mu_white_label_admin_title']) ? esc_attr($options['seopress_mu_white_label_admin_title']) : null; ?>

<input type="text" name="seopress_pro_mu_option_name[seopress_mu_white_label_admin_title]"
    placeholder="<?php esc_html_e('default value: SEO', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter the title for the main menu', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />
<?php
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_admin_title']) ? esc_attr($options['seopress_white_label_admin_title']) : null; ?>

<input type="text" name="seopress_pro_option_name[seopress_white_label_admin_title]"
    placeholder="<?php esc_html_e('default value: SEO', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter the title for the main menu', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />
<?php
    }
}

function seopress_white_label_admin_bar_logo_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');

        $check = isset($options['seopress_mu_white_label_admin_bar_logo']) ? esc_attr($options['seopress_mu_white_label_admin_bar_logo']) : null; ?>

<input type="text" name="seopress_pro_mu_option_name[seopress_mu_white_label_admin_bar_logo]"
    placeholder="<?php esc_html_e('eg: https://www.example.com/my-custom-image.png', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter the absolute URL to your logo', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />
<?php
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_admin_bar_logo']) ? esc_attr($options['seopress_white_label_admin_bar_logo']) : null; ?>

<input type="text" name="seopress_pro_option_name[seopress_white_label_admin_bar_logo]"
    placeholder="<?php esc_html_e('eg: https://www.example.com/my-custom-image.png', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter the absolute URL to your logo', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />
<?php
    }
}

function seopress_white_label_footer_credits_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');

        $check = isset($options['seopress_mu_white_label_footer_credits']); ?>

<input id="seopress_mu_white_label_footer_credits"
    name="seopress_pro_mu_option_name[seopress_mu_white_label_footer_credits]" type="checkbox" <?php if ('1' == $check) { ?>
checked="yes"
<?php } ?>
value="1"/>

<label for="seopress_mu_white_label_footer_credits">
    <?php _e('Remove "You like SEOPress? Don\'t forget to rate it 5 stars!"', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_mu_white_label_footer_credits'])) {
            esc_attr($options['seopress_mu_white_label_footer_credits']);
        }
    } else {
        $options = get_option('seopress_pro_option_name');

        $check = isset($options['seopress_white_label_footer_credits']); ?>

<label for="seopress_white_label_footer_credits">
    <input id="seopress_white_label_footer_credits" name="seopress_pro_option_name[seopress_white_label_footer_credits]"
        type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>


    <?php _e('Remove "You like SEOPress? Don\'t forget to rate it 5 stars!"', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_white_label_footer_credits'])) {
            esc_attr($options['seopress_white_label_footer_credits']);
        }
    }
}

function seopress_white_label_help_links_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');

        $check = isset($options['seopress_mu_white_label_help_links']); ?>

<label for="seopress_mu_white_label_help_links">
    <input id="seopress_mu_white_label_help_links"
        name="seopress_pro_mu_option_name[seopress_mu_white_label_help_links]" type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>
    <?php _e('Hide help icons and SEOPress documentation links', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_mu_white_label_help_links'])) {
            esc_attr($options['seopress_mu_white_label_help_links']);
        }
    } else {
        $options = get_option('seopress_pro_option_name');

        $check = isset($options['seopress_white_label_help_links']); ?>

<label for="seopress_white_label_help_links">
    <input id="seopress_white_label_help_links" name="seopress_pro_option_name[seopress_white_label_help_links]"
        type="checkbox" <?php if (' 1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Hide help icons and SEOPress documentation links', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_white_label_help_links'])) {
            esc_attr($options['seopress_white_label_help_links']);
        }
    }
}
function seopress_white_label_plugin_list_title_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');
        $check = isset($options['seopress_mu_white_label_plugin_list_title']) ?
    esc_attr($options['seopress_mu_white_label_plugin_list_title']) : null; ?>

<input type="text" name="seopress_pro_mu_option_name[seopress_mu_white_label_plugin_list_title]"
    placeholder="<?php esc_html_e('eg: SEO plugin', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e(
        'Enter a plugin title',
        'wp-seopress-pro'
    ); ?>" value="<?php echo $check; ?>" />

<?php
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_plugin_list_title']) ?
    esc_attr($options['seopress_white_label_plugin_list_title']) : null; ?>

<input type="text" name="seopress_pro_option_name[seopress_white_label_plugin_list_title]"
    placeholder="<?php esc_html_e('eg: SEO plugin', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter a plugin title', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<?php
    }
}
function seopress_white_label_plugin_list_title_pro_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');
        $check = isset($options['seopress_mu_white_label_plugin_list_title_pro']) ?
    esc_attr($options['seopress_mu_white_label_plugin_list_title_pro']) : null; ?>

<input type="text" name="seopress_pro_mu_option_name[seopress_mu_white_label_plugin_list_title_pro]"
    placeholder="<?php esc_html_e('eg: SEO plugin', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter a plugin title', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<?php
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_plugin_list_title_pro']) ?
    esc_attr($options['seopress_white_label_plugin_list_title_pro']) : null; ?>

<input type="text" name="seopress_pro_option_name[seopress_white_label_plugin_list_title_pro]"
    placeholder="<?php esc_html_e('eg: SEO plugin', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter a plugin title', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />
<?php
    }
}
function seopress_white_label_plugin_list_desc_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');
        $check = isset($options['seopress_mu_white_label_plugin_list_desc']) ?
    esc_attr($options['seopress_mu_white_label_plugin_list_desc']) : null; ?>

<input type="text" name="seopress_pro_mu_option_name[seopress_mu_white_label_plugin_list_desc]"
    placeholder="<?php esc_html_e('eg: Best SEO WordPress plugin', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter a description', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<?php
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_plugin_list_desc']) ?
    esc_attr($options['seopress_white_label_plugin_list_desc']) : null; ?>

<input type="text" name="seopress_pro_option_name[seopress_white_label_plugin_list_desc]"
    placeholder="<?php esc_html_e('eg: Best SEO WordPress plugin', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter a description', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<?php
    }
}
function seopress_white_label_plugin_list_desc_pro_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');
        $check = isset($options['seopress_mu_white_label_plugin_list_desc_pro']) ?
    esc_attr($options['seopress_mu_white_label_plugin_list_desc_pro']) : null; ?>

<input type="text" name="seopress_pro_mu_option_name[seopress_mu_white_label_plugin_list_desc_pro]"
    placeholder="<?php esc_html_e('eg: Best SEO WordPress plugin', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter a description', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<?php
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_plugin_list_desc_pro'])
    ? esc_attr($options['seopress_white_label_plugin_list_desc_pro']) : null; ?>

<input type="text" name="seopress_pro_option_name[seopress_white_label_plugin_list_desc_pro]"
    placeholder="<?php esc_html_e('eg: Best SEO WordPress plugin', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter a description', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<?php
    }
}
function seopress_white_label_plugin_list_author_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');
        $check = isset($options['seopress_mu_white_label_plugin_list_author']) ?
    esc_attr($options['seopress_mu_white_label_plugin_list_author']) : null; ?>

<input type="text" name="seopress_pro_mu_option_name[seopress_mu_white_label_plugin_list_author]"
    placeholder="<?php esc_html_e('eg: John Doe', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter the author name', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<?php
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_plugin_list_author']) ?
    esc_attr($options['seopress_white_label_plugin_list_author']) : null; ?>

<input type="text" name="seopress_pro_option_name[seopress_white_label_plugin_list_author]"
    placeholder="<?php esc_html_e('eg: John Doe', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter the author name', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<?php
    } ?>

<p class="description">
    <?php _e('This option will apply to both SEOPress and SEOPress PRO.', 'wp-seopress-pro'); ?>
</p>

<?php
}
function seopress_white_label_plugin_list_website_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');
        $check = isset($options['seopress_mu_white_label_plugin_list_website']) ?
    esc_attr($options['seopress_mu_white_label_plugin_list_website']) : null; ?>

<input type="text" name="seopress_pro_mu_option_name[seopress_mu_white_label_plugin_list_website]"
    placeholder="<?php esc_html_e('eg: https://www.example.com/', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter a website URL', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<?php
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_plugin_list_website'])
    ? esc_attr($options['seopress_white_label_plugin_list_website']) : null; ?>

<input type="text" name="seopress_pro_option_name[seopress_white_label_plugin_list_website]"
    placeholder="<?php esc_html_e('eg: https://www.example.com/', 'wp-seopress-pro'); ?>"
    aria-label="<?php _e('Enter a website URL', 'wp-seopress-pro'); ?>"
    value="<?php echo $check; ?>" />

<?php
    } ?>

<p class="description">
    <?php _e('This option will apply to both SEOPress and SEOPress PRO.', 'wp-seopress-pro'); ?>
</p>

<?php
}
function seopress_white_label_plugin_list_view_details_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');
        $check = isset($options['seopress_mu_white_label_plugin_list_view_details']); ?>

<label for="seopress_mu_white_label_plugin_list_view_details">
    <input id="seopress_mu_white_label_plugin_list_view_details"
        name="seopress_pro_mu_option_name[seopress_mu_white_label_plugin_list_view_details]" type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Remove View details modal link', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_mu_white_label_plugin_list_view_details'])) {
            esc_attr($options['seopress_mu_white_label_plugin_list_view_details']);
        }
    } else {
        $options = get_option('seopress_pro_option_name');
        $check = isset($options['seopress_white_label_plugin_list_view_details']); ?>

<label for="seopress_white_label_plugin_list_view_details">
    <input id="seopress_white_label_plugin_list_view_details"
        name="seopress_pro_option_name[seopress_white_label_plugin_list_view_details]" type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Remove View details modal link', 'wp-seopress-pro'); ?>
</label>

<?php
        if (isset($options['seopress_white_label_plugin_list_view_details'])) {
            esc_attr($options['seopress_white_label_plugin_list_view_details']);
        }
    } ?>

<p class="description">
    <?php _e('This option will apply to both SEOPress and SEOPress PRO.', 'wp-seopress-pro'); ?>
</p>
<?php
}
function seopress_white_label_menu_pages_callback() {
    $seopress_menu_pages = [
        'seopress-option' => __('SEO', 'wp-seopress-pro'),
        'seopress-titles' => __('Titles & Metas', 'wp-seopress-pro'),
        'seopress-xml-sitemap' => __('XML / HTML Sitemap', 'wp-seopress-pro'),
        'seopress-social' => __('Social Networks', 'wp-seopress-pro'),
        'seopress-google-analytics' => __('Analytics', 'wp-seopress-pro'),
        'seopress-advanced' => __('Advanced', 'wp-seopress-pro'),
        'seopress-import-export' => __('Tools', 'wp-seopress-pro'),
        'seopress-bot-batch' => __('BOT', 'wp-seopress-pro'),
        'seopress-license' => __('License', 'wp-seopress-pro'),
        'seopress-pro-page' => __('PRO', 'wp-seopress-pro'),
        'edit.php?post_type=seopress_404' => __('Redirections', 'wp-seopress-pro'),
        'edit.php?post_type=seopress_bot' => __('Broken links', 'wp-seopress-pro'),
        'edit.php?post_type=seopress_schemas' => __('Schemas', 'wp-seopress-pro'),
    ];

    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');

        foreach ($seopress_menu_pages as $seopress_menu_pages_key => $seopress_menu_pages_value) { ?>
<div class="seopress_wrap_single_cpt">

    <?php $check = isset($options['seopress_mu_white_label_menu_pages'][$seopress_menu_pages_key]['include']); ?>

    <label
        for="seopress_mu_white_label_menu_pages_list[<?php echo $seopress_menu_pages_key; ?>]">

        <input
            id="seopress_mu_white_label_menu_pages_list[<?php echo $seopress_menu_pages_key; ?>]"
            name="seopress_pro_mu_option_name[seopress_mu_white_label_menu_pages][<?php echo $seopress_menu_pages_key; ?>][include]"
            type="checkbox" <?php if (' 1' == $check) { ?>
        checked="yes"
        <?php } ?>
        value="1"/>

        <?php echo $seopress_menu_pages_value; ?>
    </label>
    <?php if (isset($options['seopress_mu_white_label_menu_pages'][$seopress_menu_pages_key]['include'])) {
            esc_attr($options['seopress_mu_white_label_menu_pages'][$seopress_menu_pages_key]['include']);
        } ?>
</div>
<?php }
    } ?>

<p class="description">
    <?php _e('Users with the "manage_options" capability will still see the menus.', 'wp-seopress-pro'); ?>
</p>
<?php
}
