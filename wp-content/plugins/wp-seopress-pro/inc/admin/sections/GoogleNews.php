<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function print_section_info_news() {
    print_pro_section('news'); ?>

<div class="seopress-notice">
    <p><?php _e('To view your sitemap, enable permalinks (not default one), and save settings to flush them.', 'wp-seopress-pro'); ?>
    </p>

    <p><?php _e('We respect the rules of Google News: Only articles published during the previous two days, and, to a limit of 1000 articles, are visible in the sitemap.', 'wp-seopress-pro'); ?>
    </p>

    <p>
        <a href="<?php echo get_option('home'); ?>/sitemaps/news.xml"
            target="_blank" class="btn btnSecondary">
            <?php _e('View your sitemap', 'wp-seopress-pro'); ?>
        </a>

        <a href="https://www.google.com/ping?sitemap=<?php echo get_option('home'); ?>/sitemaps/"
            target="_blank" class="btn btnSecondary">
            <?php _e('Ping Google manually', 'wp-seopress-pro'); ?>
        </a>

        <button type="button" id="seopress-flush-permalinks" class="btn btnSecondary">
            <?php _e('Flush permalinks', 'wp-seopress-pro'); ?>
        </button>
        <span class="spinner"></span>
    </p>
</div>

<?php
}
