<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function print_section_info_htaccess() {
    print_pro_section('htaccess'); ?>

    <div class="seopress-notice is-warning">
        <p>
            <strong><?php _e('SAVE YOUR HTACCESS FILE BEFORE EDIT!', 'wp-seopress-pro'); ?></strong>
        </p>
    </div>

    <?php
}
