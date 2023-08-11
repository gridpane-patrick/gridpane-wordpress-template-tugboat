<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

?>

<div class="wrap-rich-snippets-courses">
    <div class="seopress-notice">
        <p>
            <?php
                /* translators: %s: link documentation */
                printf(__('Learn more about the <strong>Course schema</strong> from the <a href="%s" target="_blank">Google official documentation website</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), 'https://developers.google.com/search/docs/data-types/course');
            ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_courses_title_meta">
            <?php _e('Title', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_courses_title', 'default'); ?>
        <span class="description"><?php _e('The title of your lesson, course...', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_courses_desc_meta"><?php _e('Course description', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_courses_desc', 'default'); ?>
        <span class="description"><?php _e('Enter your course/lesson description', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_courses_school_meta">
            <?php _e('School/Organization', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_courses_school', 'default'); ?>
        <span class="description"><?php _e('Name of university, organization...', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_courses_website_meta">
            <?php _e('School/Organization Website', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_courses_website', 'default'); ?>
        <span class="description"><?php _e('Enter the URL like https://example.com/', 'wp-seopress-pro'); ?></span>
    </p>
</div>
