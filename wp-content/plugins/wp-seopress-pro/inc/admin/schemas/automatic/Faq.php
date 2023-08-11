<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

?>

<div class="wrap-rich-snippets-faq">
    <div class="seopress-notice">
        <p>
            <?php
                /* translators: %s: link documentation */
                printf(__('Learn more about the <strong>FAQ schema</strong> from the <a href="%s" target="_blank">Google official documentation website</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), 'https://developers.google.com/search/docs/data-types/faqpage'); ?>
        </p>
    </div>
    <div class="seopress-notice">
        <p>
            <?php /* translators: %s: link documentation */
                printf(__('Using <strong>Advanced Custom Fields</strong> plugin? Learn <a href="%s" target="_blank">how to use repeater fields to build an automatic FAQ schema</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), $docs['schemas']['faq_acf']); ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_faq_q_meta">
            <?php _e('Question', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_faq_q', 'default'); ?>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_faq_a_meta">
            <?php _e('Answer', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_faq_a', 'default'); ?>
    </p>
</div>
