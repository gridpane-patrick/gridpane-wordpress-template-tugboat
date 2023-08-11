<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function print_section_info_robots() {
    print_pro_section('robots'); ?>

<p>
    <a href="<?php echo get_home_url(); ?>/robots.txt"
        class="btn btnSecondary" target="_blank">
        <?php _e('View your robots.txt', 'wp-seopress-pro'); ?>
    </a>

    <button type="button" id="seopress-flush-permalinks2" class="btn btnSecondary">
        <?php _e('Flush permalinks', 'wp-seopress-pro'); ?>
    </button>

    <span class="spinner"></span>
</p>

<?php
    /* translators: %1$s: get_home_url() */ ?>
<div class="seopress-notice">
    <p><?php printf(__('A <strong>robots.txt file</strong> lives at the root of your site. So, for site %1$s, the robots.txt file lives at %1$s/robots.txt.', 'wp-seopress-pro'), get_home_url()); ?>
    </p>

    <p><?php _e('robots.txt is a plain text file that follows the <strong>Robots Exclusion Standard</strong>.', 'wp-seopress-pro'); ?>
    </p>

    <p><?php _e('A robots.txt file consists of one or more rules. <strong>Each rule blocks (disallows or allows) access</strong> for a given crawler to a specified file path in that website.', 'wp-seopress-pro'); ?>
    </p>

    <p><?php _e('Our robots.txt file is <strong>virtual</strong> (like the default WordPress one). It means it‘s not physically present on your server. It‘s generated via <strong>URL rewriting</strong>.', 'wp-seopress-pro'); ?>
    </p>
</div>

<div class="seopress-notice is-warning">
    <p><?php _e('This virtual file will not bypass your real <strong>robots.txt</strong> file if you have one.', 'wp-seopress-pro'); ?>
    </p>
</div>

<?php
}
