<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function print_section_info_rich_snippets() {
    print_pro_section('rich-snippets'); ?>

    <a class="btn btnSecondary" href="<?php echo admin_url('edit.php?post_type=seopress_schemas'); ?>">
        <?php _e('View my automatic schemas', 'wp-seopress-pro'); ?>
    </a>

    <?php
}
