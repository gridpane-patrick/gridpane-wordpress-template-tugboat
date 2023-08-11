<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_get_schema_metaboxe_course($seopress_pro_rich_snippets_data, $key_schema = 0) {
    $seopress_pro_rich_snippets_courses_title   = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_courses_title']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_courses_title'] : '';
    $seopress_pro_rich_snippets_courses_desc    = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_courses_desc']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_courses_desc'] : '';
    $seopress_pro_rich_snippets_courses_school  = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_courses_school']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_courses_school'] : '';
    $seopress_pro_rich_snippets_courses_website = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_courses_website']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_courses_website'] : ''; ?>
<div class="wrap-rich-snippets-item wrap-rich-snippets-courses">
    <div class="seopress-notice">
        <p>
            <?php _e('Mark up your course lists with structured data so prospective students find you through Google Search.', 'wp-seopress-pro'); ?>
        </p>
    </div>
    <div class="seopress-notice is-warning">
        <ul class="seopress-list advice">
            <li><?php _e('Only use course markup for educational content that fits the following definition of a course: A series or unit of curriculum that contains lectures, lessons, or modules in a particular subject and/or topic.', 'wp-seopress-pro'); ?>
            </li>
            <li><?php _e('A course must have an explicit educational outcome of knowledge and/or skill in a particular subject and/or topic, and be led by one or more instructors with a roster of students.', 'wp-seopress-pro'); ?>
            </li>
            <li><?php _e('A general public event such as "Astronomy Day" is not a course, and a single 2-minute "How to make a Sandwich Video" is not a course.', 'wp-seopress-pro'); ?>
            </li>
        </ul>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_courses_title_meta">
            <?php _e('Title', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_courses_title_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_courses_title]"
            placeholder="<?php echo esc_html__('The title of your lesson, course...', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Title', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_courses_title; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_courses_desc">
            <?php _e('Course description', 'wp-seopress-pro'); ?>
        </label>
        <textarea id="seopress_pro_rich_snippets_courses_desc" class="seopress_pro_rich_snippets_courses_desc"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_courses_desc]"
            placeholder="<?php echo esc_html__('Enter your course/lesson description', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Course description', 'wp-seopress-pro'); ?>"><?php echo $seopress_pro_rich_snippets_courses_desc; ?></textarea>
    <div class="wrap-seopress-counters">
        <div class="seopress_rich_snippets_courses_counters"></div>
        <?php _e('(maximum limit)', 'wp-seopress-pro'); ?>
    </div>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_courses_school_meta">
            <?php _e('School/Organization', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_courses_school_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_courses_school]"
            placeholder="<?php echo esc_html__('Name of university, organization...', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('School/Organization', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_courses_school; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_courses_website_meta">
            <?php _e('School/Organization Website', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_courses_website_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_courses_website]"
            placeholder="<?php echo esc_html__('Enter the URL like https://example.com/', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('School/Organization Website', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_courses_website; ?>" />
    </p>
</div>
<?php
}
