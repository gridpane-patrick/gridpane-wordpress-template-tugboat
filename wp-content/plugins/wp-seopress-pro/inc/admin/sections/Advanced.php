<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function print_section_info_advanced_security_ga()
{
    $docs     = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';
    ?>
    <hr>
<h3>
    <?php _e('Google Analytics Stats in Dashboard widget', 'wp-seopress-pro'); ?>
</h3>

<div class="seopress-notice">
    <p>
        <?php _e('By default, only users with <code>edit_dashboard</code> capability can view and configure the Google Analytics widget.','wp-seopress-pro'); ?>
    </p>
</div>

<p>
    <?php _e('Check a user role below to allow it to view and configure the GA widget:', 'wp-seopress-pro'); ?>
</p>

<?php
}
