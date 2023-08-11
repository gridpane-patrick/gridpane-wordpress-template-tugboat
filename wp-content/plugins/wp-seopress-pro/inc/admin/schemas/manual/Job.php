<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_get_schema_metaboxe_jobs($seopress_pro_rich_snippets_data, $key_schema = 0) {
    $seopress_pro_rich_snippets_jobs_name                           = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_name']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_name'] : '';
    $seopress_pro_rich_snippets_jobs_desc                           = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_desc']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_desc'] : '';
    $seopress_pro_rich_snippets_jobs_date_posted                    = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_date_posted']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_date_posted'] : '';
    $seopress_pro_rich_snippets_jobs_valid_through                  = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_valid_through']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_valid_through'] : '';
    $seopress_pro_rich_snippets_jobs_employment_type                = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_employment_type']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_employment_type'] : '';
    $seopress_pro_rich_snippets_jobs_identifier_name                = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_identifier_name']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_identifier_name'] : '';
    $seopress_pro_rich_snippets_jobs_identifier_value               = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_identifier_value']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_identifier_value'] : '';
    $seopress_pro_rich_snippets_jobs_hiring_organization            = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_hiring_organization']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_hiring_organization'] : '';
    $seopress_pro_rich_snippets_jobs_hiring_same_as                 = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_hiring_same_as']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_hiring_same_as'] : '';
    $seopress_pro_rich_snippets_jobs_hiring_logo                    = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_hiring_logo']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_hiring_logo'] : '';
    $seopress_pro_rich_snippets_jobs_hiring_logo_width              = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_hiring_logo_width']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_hiring_logo_width'] : '';
    $seopress_pro_rich_snippets_jobs_hiring_logo_height             = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_hiring_logo_height']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_hiring_logo_height'] : '';
    $seopress_pro_rich_snippets_jobs_address_street                 = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_address_street']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_address_street'] : '';
    $seopress_pro_rich_snippets_jobs_address_locality               = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_address_locality']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_address_locality'] : '';
    $seopress_pro_rich_snippets_jobs_address_region                 = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_address_region']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_address_region'] : '';
    $seopress_pro_rich_snippets_jobs_postal_code                    = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_postal_code']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_postal_code'] : '';
    $seopress_pro_rich_snippets_jobs_country                        = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_country']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_country'] : '';
    $seopress_pro_rich_snippets_jobs_remote                         = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_remote']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_remote'] : '';
    $seopress_pro_rich_snippets_jobs_direct_apply                   = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_direct_apply']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_direct_apply'] : '';
    $seopress_pro_rich_snippets_jobs_salary                         = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_salary']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_salary'] : '';
    $seopress_pro_rich_snippets_jobs_salary_currency                = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_salary_currency']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_salary_currency'] : '';
    $seopress_pro_rich_snippets_jobs_salary_unit                    = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_salary_unit']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_jobs_salary_unit'] : ''; ?>
<div class="wrap-rich-snippets-item wrap-rich-snippets-jobs">
    <div class="seopress-notice">
        <p>
            <?php _e('Adding structured data makes your job postings eligible to appear in a special user experience in Google Search results.', 'wp-seopress-pro'); ?>
        </p>
    </div>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_name_meta">
            <?php _e('Job title', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_jobs_name_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_name]"
            placeholder="<?php echo esc_html__('The title of the job (not the title of the posting). For example, "Software Engineer" or "Barista".', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Job title', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_name; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_desc_meta">
            <?php _e('Job description', 'wp-seopress-pro'); ?>
        </label>
        <textarea rows="12" id="seopress_pro_rich_snippets_jobs_desc_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_desc]"
            placeholder="<?php echo esc_html__('The full description of the job in HTML format.', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Job description', 'wp-seopress-pro'); ?>"><?php echo $seopress_pro_rich_snippets_jobs_desc; ?></textarea>
    </p>
    <p>
        <label for="seopress-date-picker4">
            <?php _e('Published date', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress-date-picker4" class="seopress-date-picker" autocomplete="off"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_date_posted]"
            placeholder="<?php echo esc_html__('The original date that employer posted the job in ISO 8601 format. For example, "2017-01-24" or "2017-01-24T19:33:17+00:00".', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Published date', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_date_posted; ?>" />
    </p>
    <p>
        <label for="seopress-date-picker5">
            <?php _e('Expiration date', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress-date-picker5" class="seopress-date-picker" autocomplete="off"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_valid_through]"
            placeholder="<?php echo esc_html__('The date when the job posting will expire in ISO 8601 format. For example, "2017-02-24" or "2017-02-24T19:33:17+00:00".', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Expiration date', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_valid_through; ?>" />
    </p>
    <p class="seopress_pro_rich_snippets_jobs_employment_type_p">
        <label for="seopress_pro_rich_snippets_jobs_employment_type_meta">
            <?php _e('Type of employment', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_jobs_employment_type_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_employment_type]"
            class="seopress_pro_rich_snippets_jobs_employment_type"
            placeholder="<?php echo esc_html__('Type of employment, You can include more than one employmentType property.', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Type of employment', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_employment_type; ?>" />

        <span class="wrap-tags">
            <?php
            $employment_type = [
                'FULL_TIME'  => 'FULL TIME',
                'PART_TIME'  => 'PART TIME',
                'CONTRACTOR' => 'CONTRACTOR',
                'TEMPORARY'  => 'TEMPORARY',
                'INTERN'     => 'INTERN',
                'VOLUNTEER'  => 'VOLUNTEER',
                'PER_DIEM'   => 'PER_DIEM',
                'OTHER'      => 'OTHER',
            ];
    $i = 1;
    foreach ($employment_type as $key => $value) { ?>
            <button type="button" class="<?php echo seopress_btn_secondary_classes(); ?> tag-title" id="seopress-tag-employment-<?php echo $i; ?>"
                data-tag="<?php echo $key; ?>"><span
                    class="dashicons dashicons-plus-alt2"></span><?php echo $value; ?></button>
            <?php
                ++$i;
            } ?>
        </span>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_identifier_name_meta">
            <?php _e('Identifier name', 'wp-seopress-pro'); ?></label>
        <input type="text" id="seopress_pro_rich_snippets_jobs_identifier_name_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_identifier_name]"
            placeholder="<?php echo esc_html__('The hiring organization\'s unique identifier name for the job', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Identifier name', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_identifier_name; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_identifier_value_meta">
            <?php _e('Identifier value', 'wp-seopress-pro'); ?></label>
        <input type="number" id="seopress_pro_rich_snippets_jobs_identifier_value_meta" min="0"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_identifier_value]"
            placeholder="<?php echo esc_html__('The hiring organization\'s value identifier value for the job', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Identifier value', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_identifier_value; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_hiring_organization_meta">
            <?php _e('Organization that hires', 'wp-seopress-pro'); ?>
        </label>
        <span class="description"><?php _e('Default: Organization name from your Knowledge Graph (SEO > Social Networks > Knowledge Graph)', 'wp-seopress-pro'); ?></span>
        <input type="text" id="seopress_pro_rich_snippets_jobs_hiring_organization_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_hiring_organization]"
            placeholder="<?php echo esc_html__('The organization offering the job position. This should be the name of the company.', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Organization that hires', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_hiring_organization; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_hiring_same_as_meta">
            <?php _e('Organization website', 'wp-seopress-pro'); ?>
        </label>
        <span class="description"><?php _e('Default: URL of your site', 'wp-seopress-pro'); ?></span>
        <input type="text" id="seopress_pro_rich_snippets_jobs_hiring_same_as_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_hiring_same_as]"
            placeholder="<?php echo esc_html__('The organization website URL offering the job position.', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Organization URL', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_hiring_same_as; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_hiring_logo_meta">
            <?php _e('Organization logo', 'wp-seopress-pro'); ?>
        </label>
        <span class="description"><?php _e('Default: Logo from your Knowledge Graph (SEO > Social Networks > Knowledge Graph)', 'wp-seopress-pro'); ?>
        </span>
        <input id="seopress_pro_rich_snippets_jobs_hiring_logo_meta" type="text"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_hiring_logo]"
            placeholder="<?php echo esc_html__('Select your image', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Organization logo', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_hiring_logo; ?>" />
        <input id="seopress_pro_rich_snippets_jobs_hiring_logo_width" type="hidden"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_hiring_logo_width]"
            value="<?php ?>" />
        <input id="seopress_pro_rich_snippets_jobs_hiring_logo_height" type="hidden"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_hiring_logo_height]"
            value="<?php ?>" />
        <input id="seopress_pro_rich_snippets_jobs_hiring_logo"
            class="<?php echo seopress_btn_secondary_classes(); ?> seopress_media_upload" type="button"
            value="<?php _e('Upload an Image', 'wp-seopress-pro'); ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_address_street_meta">
            <?php _e('Street address', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_jobs_address_street_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_address_street]"
            placeholder="<?php echo esc_html__('Street address', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Street address', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_address_street; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_address_locality_meta">
            <?php _e('Locality address', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_jobs_address_locality_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_address_locality]"
            placeholder="<?php echo esc_html__('Locality address', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Locality address', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_address_locality; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_address_region_meta">
            <?php _e('Region', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_jobs_address_region_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_address_region]"
            placeholder="<?php echo esc_html__('Region', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Region', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_address_region; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_postal_code_meta">
            <?php _e('Postal code', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_jobs_postal_code_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_postal_code]"
            placeholder="<?php echo esc_html__('Postal code', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Postal code', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_postal_code; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_country_meta">
            <?php _e('Country', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_jobs_country_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_country]"
            placeholder="<?php echo esc_html__('Country', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Country', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_country; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_remote_meta">
            <input type="checkbox" id="seopress_pro_rich_snippets_jobs_remote_meta"
                name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_remote]"
                aria-label="<?php _e('Remote job', 'wp-seopress-pro'); ?>"
                <?php if ('1' == $seopress_pro_rich_snippets_jobs_remote) {
                echo 'checked="yes"';
            } ?>
            value="1"
            />
            <?php _e('Remote job?', 'wp-seopress-pro'); ?>
        </label>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_direct_apply_meta">
            <input type="checkbox" id="seopress_pro_rich_snippets_jobs_direct_apply_meta"
                name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_direct_apply]"
                aria-label="<?php _e('Direct apply', 'wp-seopress-pro'); ?>"
                <?php if ('1' == $seopress_pro_rich_snippets_jobs_direct_apply) {
                echo 'checked="yes"';
            } ?>
            value="1"
            />
            <?php _e('Direct apply?', 'wp-seopress-pro'); ?>
        </label>
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_salary_meta">
            <?php _e('Salary', 'wp-seopress-pro'); ?>
        </label>
        <input type="number" id="seopress_pro_rich_snippets_jobs_salary_meta" step="0.01" min="0"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_salary]"
            placeholder="<?php echo esc_html__('eg: 50.00', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Currency', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_salary; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_salary_currency_meta">
            <?php _e('Currency', 'wp-seopress-pro'); ?>
        </label>
        <input type="text" id="seopress_pro_rich_snippets_jobs_salary_currency_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_salary_currency]"
            placeholder="<?php echo esc_html__('eg: USD', 'wp-seopress-pro'); ?>"
            aria-label="<?php _e('Currency', 'wp-seopress-pro'); ?>"
            value="<?php echo $seopress_pro_rich_snippets_jobs_salary_currency; ?>" />
    </p>
    <p>
        <label for="seopress_pro_rich_snippets_jobs_salary_unit_meta">
            <?php _e('Select your unit text', 'wp-seopress-pro'); ?>
        </label>
        <select id="seopress_pro_rich_snippets_jobs_salary_unit_meta"
            name="seopress_pro_rich_snippets_data[<?php echo $key_schema; ?>][seopress_pro_rich_snippets_jobs_salary_unit]">
            <option <?php selected('HOUR', $seopress_pro_rich_snippets_jobs_salary_unit); ?>
                value="HOUR">
                <?php _e('HOUR', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('DAY', $seopress_pro_rich_snippets_jobs_salary_unit); ?>
                value="DAY">
                <?php _e('DAY', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('WEEK', $seopress_pro_rich_snippets_jobs_salary_unit); ?>
                value="WEEK">
                <?php _e('WEEK', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('MONTH', $seopress_pro_rich_snippets_jobs_salary_unit); ?>
                value="MONTH">
                <?php _e('MONTH', 'wp-seopress-pro'); ?>
            </option>
            <option <?php selected('YEAR', $seopress_pro_rich_snippets_jobs_salary_unit); ?>
                value="YEAR">
                <?php _e('YEAR', 'wp-seopress-pro'); ?>
            </option>
        </select>
    </p>
</div>
<?php
}
