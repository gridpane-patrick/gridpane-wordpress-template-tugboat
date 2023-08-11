<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function print_section_info_edd() {
    print_pro_section('edd');

    if ( ! is_plugin_active('easy-digital-downloads/easy-digital-downloads.php')) { ?>

<div class="seopress-notice is-warning">
    <p>
        <?php _e('You need to enable <strong>Easy Digital Downloads</strong> to apply these settings.', 'wp-seopress-pro'); ?>
    </p>
</div>

<?php
    }
}
