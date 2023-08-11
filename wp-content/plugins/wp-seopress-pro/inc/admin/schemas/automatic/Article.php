<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

?>
<div class="wrap-rich-snippets-articles schema-steps">
    <div class="seopress-notice">
        <p class="seopress-help">
            <?php /* translators: %s: link documentation */
                printf(__('Learn more about the <strong>Article schema</strong> from the <a href="%s" target="_blank">Google official documentation website</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), 'https://developers.google.com/search/docs/data-types/article'); ?>
        </p>
    </div>

    <?php if (function_exists('seopress_rich_snippets_publisher_logo_option') && '' != seopress_rich_snippets_publisher_logo_option()) { ?>
    <div class="seopress-notice is-success">
        <p>
            <?php _e('You have set a publisher logo. Good!', 'wp-seopress-pro'); ?>
        </p>
    </div>
    <?php } else { ?>
    <div class="seopress-notice is-error">
        <p><span class="dashicons dashicons-no-alt"></span>
            <?php
            /* translators: %s: link to settings page */
            printf(__('You don\'t have set a <a href="%s">publisher logo</a>. It\'s required for Article content types.', 'wp-seopress-pro'), admin_url('admin.php?page=seopress-pro-page#tab=tab_seopress_rich_snippets'));
        ?>
        </p>
    </div>

    <?php } ?>
    <p>
        <label for="seopress_pro_rich_snippets_article_type_meta"><?php _e('Select your article type', 'wp-seopress-pro'); ?></label>
        <select name="seopress_pro_rich_snippets_article_type">
            <option <?php echo selected('Article', $seopress_pro_rich_snippets_article_type, false); ?>
                value="Article">
                <?php _e('Article (generic)', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('AdvertiserContentArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="AdvertiserContentArticle">
                <?php _e('Advertiser Content Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('NewsArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="NewsArticle">
                <?php _e('News Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('Report', $seopress_pro_rich_snippets_article_type, false); ?>
                value="Report">
                <?php _e('Report', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('SatiricalArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="SatiricalArticle">
                <?php _e('Satirical Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('ScholarlyArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="ScholarlyArticle">
                <?php _e('Scholarly Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('SocialMediaPosting', $seopress_pro_rich_snippets_article_type, false); ?>
                value="SocialMediaPosting">
                <?php _e('Social Media Posting', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('BlogPosting', $seopress_pro_rich_snippets_article_type, false); ?>
                value="BlogPosting">
                <?php _e('Blog Posting', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('TechArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="TechArticle">
                <?php _e('Tech Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('AnalysisNewsArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="AnalysisNewsArticle">
                <?php _e('Analysis News Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('AskPublicNewsArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="AskPublicNewsArticle">
                <?php _e('Ask Public News Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('BackgroundNewsArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="BackgroundNewsArticle">
                <?php _e('Background News Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('OpinionNewsArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="OpinionNewsArticle">
                <?php _e('Opinion News Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('ReportageNewsArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="ReportageNewsArticle">
                <?php _e('Reportage News Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('ReviewNewsArticle', $seopress_pro_rich_snippets_article_type, false); ?>
                value="ReviewNewsArticle">
                <?php _e('Review News Article', 'wp-seopress-pro'); ?>
            </option>
            <option <?php echo selected('LiveBlogPosting', $seopress_pro_rich_snippets_article_type, false); ?>
                value="LiveBlogPosting">
                <?php _e('Live Blog Posting', 'wp-seopress-pro'); ?>
            </option>
        </select>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_article_title_meta">
            <?php _e('Headline <em>(max limit: 110)</em>', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_article_title', 'default'); ?>
        <span class="description"><?php _e('The headline of the article', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_article_desc_meta">
            <?php _e('Description', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_article_desc', 'default'); ?>
        <span class="description"><?php _e('The description of the article', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_article_author_meta">
            <?php _e('Post author', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_article_author', 'default'); ?>
        <span class="description"><?php _e('The author of the article', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_article_img_meta"><?php _e('Image', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_article_img', 'image'); ?>
        <span class="description"><?php _e('The representative image of the article. Only a marked-up image that directly belongs to the article should be specified. ', 'wp-seopress-pro'); ?><br>
            <?php _e('Default value if empty: Post thumbnail (featured image)', 'wp-seopress-pro'); ?></span>
        <span class="field-required"><?php _e('Minimum size: 696px wide, JPG, PNG or GIF, crawlable and indexable (default: post thumbnail if available)', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_article_coverage_start_date_meta">
            <?php _e('Coverage Start Date', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_article_coverage_start_date', 'date'); ?>
        <span class="description"><?php _e('Eg: YYYY-MM-DD - To use with <strong>Live Blog Posting</strong> article type only', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_article_coverage_start_time_meta">
            <?php _e('Coverage Start Time', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_article_coverage_start_time', 'time'); ?>
        <span class="description"><?php _e('Eg: HH:MM - To use with <strong>Live Blog Posting</strong> article type only', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_article_coverage_end_date_meta">
            <?php _e('Coverage End Date', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_article_coverage_end_date', 'date'); ?>
        <span class="description"><?php _e('Eg: YYYY-MM-DD - To use with <strong>Live Blog Posting</strong> article type only', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_article_coverage_end_time_meta">
            <?php _e('Coverage End Time', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_article_coverage_end_time', 'time'); ?>
        <span class="description"><?php _e('Eg: HH:MM - To use with <strong>Live Blog Posting</strong> article type only', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_article_speakable_meta">
            <?php _e('Speakable CSS Selector', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_article_speakable', 'default'); ?>
        <span class="description"><?php _e('Addresses content in the annotated pages (such as class attribute)', 'wp-seopress-pro'); ?></span>
    </p>
</div>
