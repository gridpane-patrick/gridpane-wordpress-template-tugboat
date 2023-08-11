<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_robots_enable_callback() {
    if (is_network_admin() && is_multisite()) {
        $options = get_option('seopress_pro_mu_option_name');

        $check = isset($options['seopress_mu_robots_enable']); ?>

<label for="seopress_mu_robots_enable">
    <input id="seopress_mu_robots_enable" name="seopress_pro_mu_option_name[seopress_mu_robots_enable]" type="checkbox"
        <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Enable robots.txt virtual file', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_mu_robots_enable'])) {
            esc_attr($options['seopress_mu_robots_enable']);
        }
    } else {
        $options = get_option('seopress_pro_option_name');

        $check = isset($options['seopress_robots_enable']); ?>

<label for="seopress_robots_enable">
    <input id="seopress_robots_enable" name="seopress_pro_option_name[seopress_robots_enable]" type="checkbox" <?php if ('1' == $check) { ?>
    checked="yes"
    <?php } ?>
    value="1"/>

    <?php _e('Enable robots.txt virtual file', 'wp-seopress-pro'); ?>
</label>

<?php if (isset($options['seopress_robots_enable'])) {
            esc_attr($options['seopress_robots_enable']);
        }
    }
}

function seopress_robots_file_callback() {
    $docs     = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';

    if (defined('SEOPRESS_BLOCK_ROBOTS') && SEOPRESS_BLOCK_ROBOTS == true) { ?>
<div class="seopress-notice is-error">
    <p>
        <?php _e('Access not allowed by the PHP define.', 'wp-seopress-pro'); ?>
    </p>
</div>
<?php } else {
        if (is_network_admin() && is_multisite()) {
            $options = get_option('seopress_pro_mu_option_name');
            $check   = isset($options['seopress_mu_robots_file']) ? $options['seopress_mu_robots_file'] : null;

            printf(
            '<textarea id="seopress_mu_robots_file" class="seopress_robots_file" name="seopress_pro_mu_option_name[seopress_mu_robots_file]" rows="15" aria-label="' . __('Virtual Robots.txt file', 'wp-seopress-pro') . '" placeholder="' . esc_html__('This is your robots.txt file!', 'wp-seopress-pro') . '">%s</textarea>',
            esc_html($check)
            );
        } else {
            $options = get_option('seopress_pro_option_name');
            $check   = isset($options['seopress_robots_file']) ? $options['seopress_robots_file'] : null;

            printf(
            '<textarea id="seopress_robots_file" class="seopress_robots_file" name="seopress_pro_option_name[seopress_robots_file]" rows="15" aria-label="' . __('Virtual Robots.txt file', 'wp-seopress-pro') . '" placeholder="' . esc_html__('This is your robots.txt file!', 'wp-seopress-pro') . '">%s</textarea>',
            esc_html($check)
            );
        } ?>
<div class="wrap-tags">

    <button type="button" class="btn btnSecondary tag-title" id="seopress-tag-robots-1" data-tag="User-agent: SemrushBot
Disallow: /
User-agent: SemrushBot-SA
Disallow: /"><span class="dashicons dashicons-plus-alt2"></span><?php _e('Block SemrushBot', 'wp-seopress-pro'); ?></button>

    <button type="button" class="btn btnSecondary tag-title" id="seopress-tag-robots-2" data-tag="User-agent: MJ12bot
Disallow: /"><span class="dashicons dashicons-plus-alt2"></span><?php _e('Block MajesticSEOBot', 'wp-seopress-pro'); ?></button>

    <button type="button" class="btn btnSecondary tag-title" id="seopress-tag-robots-7" data-tag="User-agent: AhrefsBot
Disallow: /"><span class="dashicons dashicons-plus-alt2"></span><?php _e('Block AhrefsBot', 'wp-seopress-pro'); ?></button>

    <button type="button" class="btn btnSecondary tag-title" id="seopress-tag-robots-3" data-tag="Sitemap: <?php echo get_home_url(); ?>/sitemaps.xml"><span
            class="dashicons dashicons-plus-alt2"></span><?php _e('Link to your sitemap', 'wp-seopress-pro'); ?></button>

    <button type="button" class="btn btnSecondary tag-title" id="seopress-tag-robots-4" data-tag="User-agent: Mediapartners-Google
Disallow: "><span class="dashicons dashicons-plus-alt2"></span><?php _e('Allow Google AdSense bot', 'wp-seopress-pro'); ?></button>

    <button type="button" class="btn btnSecondary tag-title" id="seopress-tag-robots-5" data-tag="User-agent: Googlebot-Image
Disallow: "><span class="dashicons dashicons-plus-alt2"></span><?php _e('Allow Google Image bot', 'wp-seopress-pro'); ?></button>

    <button type="button" class="btn btnSecondary tag-title" id="seopress-tag-robots-6" data-tag="User-agent: *
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php "><span class="dashicons dashicons-plus-alt2"></span><?php _e('Default WP rules', 'wp-seopress-pro'); ?></button>

</div>
<?php
    }
    echo seopress_tooltip_link($docs['robots']['file'], __('Guide to edit your robots.txt file - new window', 'wp-seopress-pro'));
}
