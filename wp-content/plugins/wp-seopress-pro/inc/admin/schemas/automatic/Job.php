<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

?>

<div class="wrap-rich-snippets-jobs">
    <div class="seopress-notice">
        <p>
            <?php
                /* translators: %s: link documentation */
                printf(__('Learn more about the <strong>Job Posting schema</strong> from the <a href="%s" target="_blank">Google official documentation website</a><span class="dashicons dashicons-external"></span>', 'wp-seopress-pro'), 'https://developers.google.com/search/docs/data-types/job-posting');
            ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_name_meta">
            <?php _e('Job title', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_name', 'default'); ?>
        <span class="description"><?php _e('Job title', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_desc_meta">
            <?php _e('Job description', 'wp-seopress-pro'); ?>
        </label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_desc', 'default'); ?>
        <span class="description"><?php _e('Job description', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_date_posted_meta"><?php _e('Published date', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_date_posted', 'date'); ?>
        <span class="description"><?php _e('The original date that employer posted the job in ISO 8601 format. For example, "2017-01-24" or "2017-01-24T19:33:17+00:00".', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_valid_through_meta"><?php _e('Expiration date', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_valid_through', 'date'); ?>
        <span class="description"><?php _e('The date when the job posting will expire in ISO 8601 format. For example, "2017-02-24" or "2017-02-24T19:33:17+00:00".', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_employment_type_meta"><?php _e('Type of employment', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_employment_type', 'default'); ?>
        <span class="description">
            <?php
                /* translators: do not translate authorized values, eg: FULL_TIME  */
                _e('Type of employment, You can include more than one employmentType property. Authorized values: "FULL_TIME", "PART_TIME", "CONTRACTOR", "TEMPORARY", "INTERN", "VOLUNTEER", "PER_DIEM", "OTHER"', 'wp-seopress-pro');
            ?>
        </span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_identifier_name_meta"><?php _e('Identifier name', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_identifier_name', 'default'); ?>
        <span class="description"><?php _e('The hiring organization\'s unique identifier name for the job', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_identifier_value_meta"><?php _e('Identifier value', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_identifier_value', 'default'); ?>
        <span class="description"><?php _e('The hiring organization\'s value identifier value for the job', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_hiring_organization_meta"><?php _e('Organization that hires', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_hiring_organization', 'default'); ?>
        <span class="description"><?php _e('The organization offering the job position. This should be the name of the company.', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_hiring_same_as_meta"><?php _e('Organization website', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_hiring_same_as', 'default'); ?>
        <span class="description"><?php _e('Enter the URL like https://example.com/', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_hiring_logo_meta"><?php _e('Image', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_hiring_logo', 'image'); ?>
        <span class="description"><?php _e('Default: Logo from your Knowledge Graph (SEO > Social Networks > Knowledge Graph)', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_address_street_meta"><?php _e('Street address', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_address_street', 'default'); ?>
        <span class="description"><?php _e('Street address', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_address_locality_meta"><?php _e('Locality address', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_address_locality', 'default'); ?>
        <span class="description"><?php _e('Locality address', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_address_region_meta"><?php _e('Region', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_address_region', 'default'); ?>
        <span class="description"><?php _e('Region', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_postal_code_meta"><?php _e('Postal code', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_postal_code', 'default'); ?>
        <span class="description"><?php _e('Postal code', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_country_meta"><?php _e('Country', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_country', 'default'); ?>
        <span class="description"><?php _e('Country', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_remote_meta"><?php _e('Remote job?', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_remote', 'default'); ?>
        <span class="description">
            <?php _e('If a value exists (eg: "yes"), the job offer will be marked as fully remote. Don\'t mark up jobs that allow occasional work-from-home, jobs for which remote work is a negotiable benefit, or have other arrangements that are not 100% remote. The "gig economy" nature of a job doesn\'t imply that it is or is not remote.', 'wp-seopress-pro'); ?>
        </span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_direct_apply_meta"><?php _e('Direct apply?', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_direct_apply', 'default'); ?>
        <span class="description">
            <?php
            /* translators: do not translate expected values, true / false  */
            _e('Indicates whether the URL that\'s associated with this job posting enables direct application for the job. Expected value: "true" or "false".', 'wp-seopress-pro'); ?>
        </span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_salary_meta"><?php _e('Salary', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_salary', 'default'); ?>
        <span class="description"><?php _e('eg: 50.00', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_salary_currency_meta"><?php _e('Currency', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_salary_currency', 'default'); ?>
        <span class="description"><?php _e('eg: USD', 'wp-seopress-pro'); ?></span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_salary_unit_meta"><?php _e('Select your unit text', 'wp-seopress-pro'); ?></label>
        <?php echo seopress_schemas_mapping_array('seopress_pro_rich_snippets_jobs_salary_unit', 'default'); ?>
        <span class="description"><?php _e('Authorized values: "HOUR", "DAY", "WEEK", "MONTH", "YEAR"', 'wp-seopress-pro'); ?></span>
    </p>
</div>
