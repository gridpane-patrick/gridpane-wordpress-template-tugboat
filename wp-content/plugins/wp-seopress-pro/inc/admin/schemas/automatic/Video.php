<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

?>

<div class="wrap-rich-snippets-videos">
    <div class="seopress-notice">
        <p>
            <?php /* translators: %s: link documentation */
                printf(__('Learn more about the <strong>Video schema</strong> from the <a href="%s" target="_blank">Google official documentation website</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), 'https://developers.google.com/search/docs/data-types/video');
            ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_videos_name_meta">
            <?php _e('Video name', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_videos_name', 'default'); ?>
        <span class="description"><?php _e('The title of your video', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_videos_description_meta"><?php _e('Video description', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_videos_description', 'default'); ?>
        <span class="description"><?php _e('The description of the video', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_videos_img_meta"><?php _e('Video thumbnail', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_videos_img', 'image'); ?>
        <span class="description"><?php _e('Minimum size: 160px by 90px - Max size: 1920x1080px - crawlable and indexable', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_videos_duration_meta">
            <?php _e('Duration of your video (format: hh:mm:ss)', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_videos_duration', 'time'); ?>
        <span class="description"><?php _e('eg: 00:04:30 for 4 minutes and 30 seconds', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_videos_url_meta">
            <?php _e('Video URL', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_videos_url', 'default'); ?>
        <span class="description"><?php _e('Eg: https://example.com/video.mp4', 'wp-seopress-pro'); ?></span>
    </p>
</div>
