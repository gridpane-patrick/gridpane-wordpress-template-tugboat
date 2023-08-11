<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

use SEOPressPro\Helpers\Schemas\Currencies;

///////////////////////////////////////////////////////////////////////////////////////////////////
//Restrict Structured Data Types metaboxes to user roles
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_advanced_security_metaboxe_sdt_role_hook_option() {
    $seopress_advanced_security_metaboxe_sdt_role_hook_option = get_option('seopress_advanced_option_name');
    if ( ! empty($seopress_advanced_security_metaboxe_sdt_role_hook_option)) {
        foreach ($seopress_advanced_security_metaboxe_sdt_role_hook_option as $key => $seopress_advanced_security_metaboxe_sdt_role_hook_value) {
            $options[$key] = $seopress_advanced_security_metaboxe_sdt_role_hook_value;
        }
        if (isset($seopress_advanced_security_metaboxe_sdt_role_hook_option['seopress_advanced_security_metaboxe_sdt_role'])) {
            return $seopress_advanced_security_metaboxe_sdt_role_hook_option['seopress_advanced_security_metaboxe_sdt_role'];
        }
    }
}

/**
 * Get currencies schema.
 *
 * @return array
 */
function seopress_get_options_schema_currencies() {
    return Currencies::getOptions();
}

function seopress_get_schema_html_part($type, $data, $key_schema = 0) {
    switch ($type) {
        case 'article':
            seopress_get_schema_metaboxe_article($data, $key_schema);
            break;
        case 'local-business':
            seopress_get_schema_metaboxe_local_business($data, $key_schema);
            break;
        case 'faq':
            seopress_get_schema_metaboxe_faq($data, $key_schema);
            break;
        case 'how-to':
            seopress_get_schema_metaboxe_how_to($data, $key_schema);
            break;
        case 'course':
            seopress_get_schema_metaboxe_course($data, $key_schema);
            break;
        case 'recipe':
            seopress_get_schema_metaboxe_recipe($data, $key_schema);
            break;
        case 'jobs':
            seopress_get_schema_metaboxe_jobs($data, $key_schema);
            break;
        case 'video':
            seopress_get_schema_metaboxe_video($data, $key_schema);
            break;
        case 'event':
            seopress_get_schema_metaboxe_event($data, $key_schema);
            break;
        case 'product':
            seopress_get_schema_metaboxe_product($data, $key_schema);
            break;
        case 'software':
            seopress_get_schema_metaboxe_software($data, $key_schema);
            break;
        case 'service':
            seopress_get_schema_metaboxe_service($data, $key_schema);
            break;
        case 'review':
            seopress_get_schema_metaboxe_review($data, $key_schema);
            break;
        case 'custom':
            seopress_get_schema_metaboxe_custom($data, $key_schema);
            break;
    }
}

/**
 * @return array
 *
 * @author Thomas Deneulin
 */
function seopress_get_keys_rich_snippets() {
    return [
        '_seopress_pro_rich_snippets_type' => [
            'key' => '_seopress_pro_rich_snippets_type',
            'post_key' => 'seopress_pro_rich_snippets_type',
        ],
        '_seopress_pro_rich_snippets_article_type' => [
            'key' => '_seopress_pro_rich_snippets_article_type',
            'post_key' => 'seopress_pro_rich_snippets_article_type',
        ],
        '_seopress_pro_rich_snippets_article_title' => [
            'key' => '_seopress_pro_rich_snippets_article_title',
            'post_key' => 'seopress_pro_rich_snippets_article_title',
        ],
        '_seopress_pro_rich_snippets_article_desc' => [
            'key' => '_seopress_pro_rich_snippets_article_desc',
            'post_key' => 'seopress_pro_rich_snippets_article_desc',
        ],
        '_seopress_pro_rich_snippets_article_author' => [
            'key' => '_seopress_pro_rich_snippets_article_author',
            'post_key' => 'seopress_pro_rich_snippets_article_author',
        ],
        '_seopress_pro_rich_snippets_article_img' => [
            'key' => '_seopress_pro_rich_snippets_article_img',
            'post_key' => 'seopress_pro_rich_snippets_article_img',
        ],
        '_seopress_pro_rich_snippets_article_img_width' => [
            'key' => '_seopress_pro_rich_snippets_article_img_width',
            'post_key' => 'seopress_pro_rich_snippets_article_img_width',
        ],
        '_seopress_pro_rich_snippets_article_img_height' => [
            'key' => '_seopress_pro_rich_snippets_article_img_height',
            'post_key' => 'seopress_pro_rich_snippets_article_img_height',
        ],
        '_seopress_pro_rich_snippets_article_coverage_start_date' => [
            'key' => '_seopress_pro_rich_snippets_article_coverage_start_date',
            'post_key' => 'seopress_pro_rich_snippets_article_coverage_start_date',
        ],
        '_seopress_pro_rich_snippets_article_coverage_start_time' => [
            'key' => '_seopress_pro_rich_snippets_article_coverage_start_time',
            'post_key' => 'seopress_pro_rich_snippets_article_coverage_start_time',
        ],
        '_seopress_pro_rich_snippets_article_coverage_end_date' => [
            'key' => '_seopress_pro_rich_snippets_article_coverage_end_date',
            'post_key' => 'seopress_pro_rich_snippets_article_coverage_end_date',
        ],
        '_seopress_pro_rich_snippets_article_coverage_end_time' => [
            'key' => '_seopress_pro_rich_snippets_article_coverage_end_time',
            'post_key' => 'seopress_pro_rich_snippets_article_coverage_end_time',
        ],
        '_seopress_pro_rich_snippets_article_speakable_css_selector' => [
            'key' => '_seopress_pro_rich_snippets_article_speakable_css_selector',
            'post_key' => 'seopress_pro_rich_snippets_article_speakable_css_selector',
        ],
        '_seopress_pro_rich_snippets_lb_name' => [
            'key' => '_seopress_pro_rich_snippets_lb_name',
            'post_key' => 'seopress_pro_rich_snippets_lb_name',
        ],
        '_seopress_pro_rich_snippets_lb_type' => [
            'key' => '_seopress_pro_rich_snippets_lb_type',
            'post_key' => 'seopress_pro_rich_snippets_lb_type',
        ],
        '_seopress_pro_rich_snippets_lb_cuisine' => [
            'key' => '_seopress_pro_rich_snippets_lb_cuisine',
            'post_key' => 'seopress_pro_rich_snippets_lb_cuisine',
        ],
        '_seopress_pro_rich_snippets_lb_menu' => [
            'key' => '_seopress_pro_rich_snippets_lb_menu',
            'post_key' => 'seopress_pro_rich_snippets_lb_menu',
        ],
        '_seopress_pro_rich_snippets_lb_accepts_reservations' => [
            'key' => '_seopress_pro_rich_snippets_lb_accepts_reservations',
            'post_key' => 'seopress_pro_rich_snippets_lb_accepts_reservations',
        ],
        '_seopress_pro_rich_snippets_lb_img' => [
            'key' => '_seopress_pro_rich_snippets_lb_img',
            'post_key' => 'seopress_pro_rich_snippets_lb_img',
        ],
        '_seopress_pro_rich_snippets_lb_img_width' => [
            'key' => '_seopress_pro_rich_snippets_lb_img_width',
            'post_key' => 'seopress_pro_rich_snippets_lb_img_width',
        ],
        '_seopress_pro_rich_snippets_lb_img_height' => [
            'key' => '_seopress_pro_rich_snippets_lb_img_height',
            'post_key' => 'seopress_pro_rich_snippets_lb_img_height',
        ],
        '_seopress_pro_rich_snippets_lb_street_addr' => [
            'key' => '_seopress_pro_rich_snippets_lb_street_addr',
            'post_key' => 'seopress_pro_rich_snippets_lb_street_addr',
        ],
        '_seopress_pro_rich_snippets_lb_city' => [
            'key' => '_seopress_pro_rich_snippets_lb_city',
            'post_key' => 'seopress_pro_rich_snippets_lb_city',
        ],
        '_seopress_pro_rich_snippets_lb_state' => [
            'key' => '_seopress_pro_rich_snippets_lb_state',
            'post_key' => 'seopress_pro_rich_snippets_lb_state',
        ],
        '_seopress_pro_rich_snippets_lb_pc' => [
            'key' => '_seopress_pro_rich_snippets_lb_pc',
            'post_key' => 'seopress_pro_rich_snippets_lb_pc',
        ],
        '_seopress_pro_rich_snippets_lb_country' => [
            'key' => '_seopress_pro_rich_snippets_lb_country',
            'post_key' => 'seopress_pro_rich_snippets_lb_country',
        ],
        '_seopress_pro_rich_snippets_lb_lat' => [
            'key' => '_seopress_pro_rich_snippets_lb_lat',
            'post_key' => 'seopress_pro_rich_snippets_lb_lat',
        ],
        '_seopress_pro_rich_snippets_lb_lon' => [
            'key' => '_seopress_pro_rich_snippets_lb_lon',
            'post_key' => 'seopress_pro_rich_snippets_lb_lon',
        ],
        '_seopress_pro_rich_snippets_lb_website' => [
            'key' => '_seopress_pro_rich_snippets_lb_website',
            'post_key' => 'seopress_pro_rich_snippets_lb_website',
        ],
        '_seopress_pro_rich_snippets_lb_tel' => [
            'key' => '_seopress_pro_rich_snippets_lb_tel',
            'post_key' => 'seopress_pro_rich_snippets_lb_tel',
        ],
        '_seopress_pro_rich_snippets_lb_price' => [
            'key' => '_seopress_pro_rich_snippets_lb_price',
            'post_key' => 'seopress_pro_rich_snippets_lb_price',
        ],
        '_seopress_pro_rich_snippets_lb_opening_hours' => [
            'key' => '_seopress_pro_rich_snippets_lb_opening_hours',
            'post_key' => 'seopress_pro_rich_snippets_lb_opening_hours',
        ],
        '_seopress_pro_rich_snippets_faq' => [
            'key' => '_seopress_pro_rich_snippets_faq',
            'post_key' => 'seopress_pro_rich_snippets_faq',
        ],
        '_seopress_pro_rich_snippets_how_to_name' => [
            'key' => '_seopress_pro_rich_snippets_how_to_name',
            'post_key' => 'seopress_pro_rich_snippets_how_to_name',
        ],
        '_seopress_pro_rich_snippets_how_to_desc' => [
            'key' => '_seopress_pro_rich_snippets_how_to_desc',
            'post_key' => 'seopress_pro_rich_snippets_how_to_desc',
        ],
        '_seopress_pro_rich_snippets_how_to_img' => [
            'key' => '_seopress_pro_rich_snippets_how_to_img',
            'post_key' => 'seopress_pro_rich_snippets_how_to_img',
        ],
        '_seopress_pro_rich_snippets_how_to_img_width' => [
            'key' => '_seopress_pro_rich_snippets_how_to_img_width',
            'post_key' => 'seopress_pro_rich_snippets_how_to_img_width',
        ],
        '_seopress_pro_rich_snippets_how_to_img_height' => [
            'key' => '_seopress_pro_rich_snippets_how_to_img_height',
            'post_key' => 'seopress_pro_rich_snippets_how_to_img_height',
        ],
        '_seopress_pro_rich_snippets_how_to_currency' => [
            'key' => '_seopress_pro_rich_snippets_how_to_currency',
            'post_key' => 'seopress_pro_rich_snippets_how_to_currency',
        ],
        '_seopress_pro_rich_snippets_how_to_cost' => [
            'key' => '_seopress_pro_rich_snippets_how_to_cost',
            'post_key' => 'seopress_pro_rich_snippets_how_to_cost',
        ],
        '_seopress_pro_rich_snippets_how_to_total_time' => [
            'key' => '_seopress_pro_rich_snippets_how_to_total_time',
            'post_key' => 'seopress_pro_rich_snippets_how_to_total_time',
        ],
        '_seopress_pro_rich_snippets_how_to' => [
            'key' => '_seopress_pro_rich_snippets_how_to',
            'post_key' => 'seopress_pro_rich_snippets_how_to',
        ],
        '_seopress_pro_rich_snippets_courses_title' => [
            'key' => '_seopress_pro_rich_snippets_courses_title',
            'post_key' => 'seopress_pro_rich_snippets_courses_title',
        ],
        '_seopress_pro_rich_snippets_courses_desc' => [
            'key' => '_seopress_pro_rich_snippets_courses_desc',
            'post_key' => 'seopress_pro_rich_snippets_courses_desc',
        ],
        '_seopress_pro_rich_snippets_courses_school' => [
            'key' => '_seopress_pro_rich_snippets_courses_school',
            'post_key' => 'seopress_pro_rich_snippets_courses_school',
        ],
        '_seopress_pro_rich_snippets_courses_website' => [
            'key' => '_seopress_pro_rich_snippets_courses_website',
            'post_key' => 'seopress_pro_rich_snippets_courses_website',
        ],
        '_seopress_pro_rich_snippets_recipes_name' => [
            'key' => '_seopress_pro_rich_snippets_recipes_name',
            'post_key' => 'seopress_pro_rich_snippets_recipes_name',
        ],
        '_seopress_pro_rich_snippets_recipes_desc' => [
            'key' => '_seopress_pro_rich_snippets_recipes_desc',
            'post_key' => 'seopress_pro_rich_snippets_recipes_desc',
        ],
        '_seopress_pro_rich_snippets_recipes_cat' => [
            'key' => '_seopress_pro_rich_snippets_recipes_cat',
            'post_key' => 'seopress_pro_rich_snippets_recipes_cat',
        ],
        '_seopress_pro_rich_snippets_recipes_img' => [
            'key' => '_seopress_pro_rich_snippets_recipes_img',
            'post_key' => 'seopress_pro_rich_snippets_recipes_img',
        ],
        '_seopress_pro_rich_snippets_recipes_video' => [
            'key' => '_seopress_pro_rich_snippets_recipes_video',
            'post_key' => 'seopress_pro_rich_snippets_recipes_video',
        ],
        '_seopress_pro_rich_snippets_recipes_prep_time' => [
            'key' => '_seopress_pro_rich_snippets_recipes_prep_time',
            'post_key' => 'seopress_pro_rich_snippets_recipes_prep_time',
        ],
        '_seopress_pro_rich_snippets_recipes_cook_time' => [
            'key' => '_seopress_pro_rich_snippets_recipes_cook_time',
            'post_key' => 'seopress_pro_rich_snippets_recipes_cook_time',
        ],
        '_seopress_pro_rich_snippets_recipes_calories' => [
            'key' => '_seopress_pro_rich_snippets_recipes_calories',
            'post_key' => 'seopress_pro_rich_snippets_recipes_calories',
        ],
        '_seopress_pro_rich_snippets_recipes_yield' => [
            'key' => '_seopress_pro_rich_snippets_recipes_yield',
            'post_key' => 'seopress_pro_rich_snippets_recipes_yield',
        ],
        '_seopress_pro_rich_snippets_recipes_keywords' => [
            'key' => '_seopress_pro_rich_snippets_recipes_keywords',
            'post_key' => 'seopress_pro_rich_snippets_recipes_keywords',
        ],
        '_seopress_pro_rich_snippets_recipes_cuisine' => [
            'key' => '_seopress_pro_rich_snippets_recipes_cuisine',
            'post_key' => 'seopress_pro_rich_snippets_recipes_cuisine',
        ],
        '_seopress_pro_rich_snippets_recipes_ingredient' => [
            'key' => '_seopress_pro_rich_snippets_recipes_ingredient',
            'post_key' => 'seopress_pro_rich_snippets_recipes_ingredient',
        ],
        '_seopress_pro_rich_snippets_recipes_instructions' => [
            'key' => '_seopress_pro_rich_snippets_recipes_instructions',
            'post_key' => 'seopress_pro_rich_snippets_recipes_instructions',
        ],
        '_seopress_pro_rich_snippets_jobs_name' => [
            'key' => '_seopress_pro_rich_snippets_jobs_name',
            'post_key' => 'seopress_pro_rich_snippets_jobs_name',
        ],
        '_seopress_pro_rich_snippets_jobs_desc' => [
            'key' => '_seopress_pro_rich_snippets_jobs_desc',
            'post_key' => 'seopress_pro_rich_snippets_jobs_desc',
        ],
        '_seopress_pro_rich_snippets_jobs_date_posted' => [
            'key' => '_seopress_pro_rich_snippets_jobs_date_posted',
            'post_key' => 'seopress_pro_rich_snippets_jobs_date_posted',
        ],
        '_seopress_pro_rich_snippets_jobs_valid_through' => [
            'key' => '_seopress_pro_rich_snippets_jobs_valid_through',
            'post_key' => 'seopress_pro_rich_snippets_jobs_valid_through',
        ],
        '_seopress_pro_rich_snippets_jobs_employment_type' => [
            'key' => '_seopress_pro_rich_snippets_jobs_employment_type',
            'post_key' => 'seopress_pro_rich_snippets_jobs_employment_type',
        ],
        '_seopress_pro_rich_snippets_jobs_identifier_name' => [
            'key' => '_seopress_pro_rich_snippets_jobs_identifier_name',
            'post_key' => 'seopress_pro_rich_snippets_jobs_identifier_name',
        ],
        '_seopress_pro_rich_snippets_jobs_identifier_value' => [
            'key' => '_seopress_pro_rich_snippets_jobs_identifier_value',
            'post_key' => 'seopress_pro_rich_snippets_jobs_identifier_value',
        ],
        '_seopress_pro_rich_snippets_jobs_hiring_organization' => [
            'key' => '_seopress_pro_rich_snippets_jobs_hiring_organization',
            'post_key' => 'seopress_pro_rich_snippets_jobs_hiring_organization',
        ],
        '_seopress_pro_rich_snippets_jobs_hiring_same_as' => [
            'key' => '_seopress_pro_rich_snippets_jobs_hiring_same_as',
            'post_key' => 'seopress_pro_rich_snippets_jobs_hiring_same_as',
        ],
        '_seopress_pro_rich_snippets_jobs_hiring_logo' => [
            'key' => '_seopress_pro_rich_snippets_jobs_hiring_logo',
            'post_key' => 'seopress_pro_rich_snippets_jobs_hiring_logo',
        ],
        '_seopress_pro_rich_snippets_jobs_hiring_logo_width' => [
            'key' => '_seopress_pro_rich_snippets_jobs_hiring_logo_width',
            'post_key' => 'seopress_pro_rich_snippets_jobs_hiring_logo_width',
        ],
        '_seopress_pro_rich_snippets_jobs_hiring_logo_height' => [
            'key' => '_seopress_pro_rich_snippets_jobs_hiring_logo_height',
            'post_key' => 'seopress_pro_rich_snippets_jobs_hiring_logo_height',
        ],
        '_seopress_pro_rich_snippets_jobs_address_street' => [
            'key' => '_seopress_pro_rich_snippets_jobs_address_street',
            'post_key' => 'seopress_pro_rich_snippets_jobs_address_street',
        ],
        '_seopress_pro_rich_snippets_jobs_address_locality' => [
            'key' => '_seopress_pro_rich_snippets_jobs_address_locality',
            'post_key' => 'seopress_pro_rich_snippets_jobs_address_locality',
        ],
        '_seopress_pro_rich_snippets_jobs_address_region' => [
            'key' => '_seopress_pro_rich_snippets_jobs_address_region',
            'post_key' => 'seopress_pro_rich_snippets_jobs_address_region',
        ],
        '_seopress_pro_rich_snippets_jobs_postal_code' => [
            'key' => '_seopress_pro_rich_snippets_jobs_postal_code',
            'post_key' => 'seopress_pro_rich_snippets_jobs_postal_code',
        ],
        '_seopress_pro_rich_snippets_jobs_country' => [
            'key' => '_seopress_pro_rich_snippets_jobs_country',
            'post_key' => 'seopress_pro_rich_snippets_jobs_country',
        ],
        '_seopress_pro_rich_snippets_jobs_remote' => [
            'key' => '_seopress_pro_rich_snippets_jobs_remote',
            'post_key' => 'seopress_pro_rich_snippets_jobs_remote',
        ],
        '_seopress_pro_rich_snippets_jobs_direct_apply' => [
            'key' => '_seopress_pro_rich_snippets_jobs_direct_apply',
            'post_key' => 'seopress_pro_rich_snippets_jobs_direct_apply',
        ],
        '_seopress_pro_rich_snippets_jobs_salary' => [
            'key' => '_seopress_pro_rich_snippets_jobs_salary',
            'post_key' => 'seopress_pro_rich_snippets_jobs_salary',
        ],
        '_seopress_pro_rich_snippets_jobs_salary_currency' => [
            'key' => '_seopress_pro_rich_snippets_jobs_salary_currency',
            'post_key' => 'seopress_pro_rich_snippets_jobs_salary_currency',
        ],
        '_seopress_pro_rich_snippets_jobs_salary_unit' => [
            'key' => '_seopress_pro_rich_snippets_jobs_salary_unit',
            'post_key' => 'seopress_pro_rich_snippets_jobs_salary_unit',
        ],
        '_seopress_pro_rich_snippets_videos_name' => [
            'key' => '_seopress_pro_rich_snippets_videos_name',
            'post_key' => 'seopress_pro_rich_snippets_videos_name',
        ],
        '_seopress_pro_rich_snippets_videos_description' => [
            'key' => '_seopress_pro_rich_snippets_videos_description',
            'post_key' => 'seopress_pro_rich_snippets_videos_description',
        ],
        '_seopress_pro_rich_snippets_videos_img' => [
            'key' => '_seopress_pro_rich_snippets_videos_img',
            'post_key' => 'seopress_pro_rich_snippets_videos_img',
        ],
        '_seopress_pro_rich_snippets_videos_img_width' => [
            'key' => '_seopress_pro_rich_snippets_videos_img_width',
            'post_key' => 'seopress_pro_rich_snippets_videos_img_width',
        ],
        '_seopress_pro_rich_snippets_videos_img_height' => [
            'key' => '_seopress_pro_rich_snippets_videos_img_height',
            'post_key' => 'seopress_pro_rich_snippets_videos_img_height',
        ],
        '_seopress_pro_rich_snippets_videos_duration' => [
            'key' => '_seopress_pro_rich_snippets_videos_duration',
            'post_key' => 'seopress_pro_rich_snippets_videos_duration',
        ],
        '_seopress_pro_rich_snippets_videos_url' => [
            'key' => '_seopress_pro_rich_snippets_videos_url',
            'post_key' => 'seopress_pro_rich_snippets_videos_url',
        ],
        '_seopress_pro_rich_snippets_events_type' => [
            'key' => '_seopress_pro_rich_snippets_events_type',
            'post_key' => 'seopress_pro_rich_snippets_events_type',
        ],
        '_seopress_pro_rich_snippets_events_name' => [
            'key' => '_seopress_pro_rich_snippets_events_name',
            'post_key' => 'seopress_pro_rich_snippets_events_name',
        ],
        '_seopress_pro_rich_snippets_events_desc' => [
            'key' => '_seopress_pro_rich_snippets_events_desc',
            'post_key' => 'seopress_pro_rich_snippets_events_desc',
        ],
        '_seopress_pro_rich_snippets_events_img' => [
            'key' => '_seopress_pro_rich_snippets_events_img',
            'post_key' => 'seopress_pro_rich_snippets_events_img',
        ],
        '_seopress_pro_rich_snippets_events_start_date' => [
            'key' => '_seopress_pro_rich_snippets_events_start_date',
            'post_key' => 'seopress_pro_rich_snippets_events_start_date',
        ],
        '_seopress_pro_rich_snippets_events_start_date_timezone' => [
            'key' => '_seopress_pro_rich_snippets_events_start_date_timezone',
            'post_key' => 'seopress_pro_rich_snippets_events_start_date_timezone',
        ],
        '_seopress_pro_rich_snippets_events_start_time' => [
            'key' => '_seopress_pro_rich_snippets_events_start_time',
            'post_key' => 'seopress_pro_rich_snippets_events_start_time',
        ],
        '_seopress_pro_rich_snippets_events_end_date' => [
            'key' => '_seopress_pro_rich_snippets_events_end_date',
            'post_key' => 'seopress_pro_rich_snippets_events_end_date',
        ],
        '_seopress_pro_rich_snippets_events_end_time' => [
            'key' => '_seopress_pro_rich_snippets_events_end_time',
            'post_key' => 'seopress_pro_rich_snippets_events_end_time',
        ],
        '_seopress_pro_rich_snippets_events_previous_start_date' => [
            'key' => '_seopress_pro_rich_snippets_events_previous_start_date',
            'post_key' => 'seopress_pro_rich_snippets_events_previous_start_date',
        ],
        '_seopress_pro_rich_snippets_events_previous_start_time' => [
            'key' => '_seopress_pro_rich_snippets_events_previous_start_time',
            'post_key' => 'seopress_pro_rich_snippets_events_previous_start_time',
        ],
        '_seopress_pro_rich_snippets_events_location_name' => [
            'key' => '_seopress_pro_rich_snippets_events_location_name',
            'post_key' => 'seopress_pro_rich_snippets_events_location_name',
        ],
        '_seopress_pro_rich_snippets_events_location_url' => [
            'key' => '_seopress_pro_rich_snippets_events_location_url',
            'post_key' => 'seopress_pro_rich_snippets_events_location_url',
        ],
        '_seopress_pro_rich_snippets_events_location_address' => [
            'key' => '_seopress_pro_rich_snippets_events_location_address',
            'post_key' => 'seopress_pro_rich_snippets_events_location_address',
        ],
        '_seopress_pro_rich_snippets_events_offers_name' => [
            'key' => '_seopress_pro_rich_snippets_events_offers_name',
            'post_key' => 'seopress_pro_rich_snippets_events_offers_name',
        ],
        '_seopress_pro_rich_snippets_events_offers_cat' => [
            'key' => '_seopress_pro_rich_snippets_events_offers_cat',
            'post_key' => 'seopress_pro_rich_snippets_events_offers_cat',
        ],
        '_seopress_pro_rich_snippets_events_offers_price' => [
            'key' => '_seopress_pro_rich_snippets_events_offers_price',
            'post_key' => 'seopress_pro_rich_snippets_events_offers_price',
        ],
        '_seopress_pro_rich_snippets_events_offers_price_currency' => [
            'key' => '_seopress_pro_rich_snippets_events_offers_price_currency',
            'post_key' => 'seopress_pro_rich_snippets_events_offers_price_currency',
        ],
        '_seopress_pro_rich_snippets_events_offers_availability' => [
            'key' => '_seopress_pro_rich_snippets_events_offers_availability',
            'post_key' => 'seopress_pro_rich_snippets_events_offers_availability',
        ],
        '_seopress_pro_rich_snippets_events_offers_valid_from_date' => [
            'key' => '_seopress_pro_rich_snippets_events_offers_valid_from_date',
            'post_key' => 'seopress_pro_rich_snippets_events_offers_valid_from_date',
        ],
        '_seopress_pro_rich_snippets_events_offers_valid_from_time' => [
            'key' => '_seopress_pro_rich_snippets_events_offers_valid_from_time',
            'post_key' => 'seopress_pro_rich_snippets_events_offers_valid_from_time',
        ],
        '_seopress_pro_rich_snippets_events_offers_url' => [
            'key' => '_seopress_pro_rich_snippets_events_offers_url',
            'post_key' => 'seopress_pro_rich_snippets_events_offers_url',
        ],
        '_seopress_pro_rich_snippets_events_performer' => [
            'key' => '_seopress_pro_rich_snippets_events_performer',
            'post_key' => 'seopress_pro_rich_snippets_events_performer',
        ],
        '_seopress_pro_rich_snippets_events_organizer_name' => [
            'key' => '_seopress_pro_rich_snippets_events_organizer_name',
            'post_key' => 'seopress_pro_rich_snippets_events_organizer_name',
        ],
        '_seopress_pro_rich_snippets_events_organizer_url' => [
            'key' => '_seopress_pro_rich_snippets_events_organizer_url',
            'post_key' => 'seopress_pro_rich_snippets_events_organizer_url',
        ],
        '_seopress_pro_rich_snippets_events_status' => [
            'key' => '_seopress_pro_rich_snippets_events_status',
            'post_key' => 'seopress_pro_rich_snippets_events_status',
        ],
        '_seopress_pro_rich_snippets_events_attendance_mode' => [
            'key' => '_seopress_pro_rich_snippets_events_attendance_mode',
            'post_key' => 'seopress_pro_rich_snippets_events_attendance_mode',
        ],
        '_seopress_pro_rich_snippets_product_name' => [
            'key' => '_seopress_pro_rich_snippets_product_name',
            'post_key' => 'seopress_pro_rich_snippets_product_name',
        ],
        '_seopress_pro_rich_snippets_product_description' => [
            'key' => '_seopress_pro_rich_snippets_product_description',
            'post_key' => 'seopress_pro_rich_snippets_product_description',
        ],
        '_seopress_pro_rich_snippets_product_img' => [
            'key' => '_seopress_pro_rich_snippets_product_img',
            'post_key' => 'seopress_pro_rich_snippets_product_img',
        ],
        '_seopress_pro_rich_snippets_product_price' => [
            'key' => '_seopress_pro_rich_snippets_product_price',
            'post_key' => 'seopress_pro_rich_snippets_product_price',
        ],
        '_seopress_pro_rich_snippets_product_price_valid_date' => [
            'key' => '_seopress_pro_rich_snippets_product_price_valid_date',
            'post_key' => 'seopress_pro_rich_snippets_product_price_valid_date',
        ],
        '_seopress_pro_rich_snippets_product_sku' => [
            'key' => '_seopress_pro_rich_snippets_product_sku',
            'post_key' => 'seopress_pro_rich_snippets_product_sku',
        ],
        '_seopress_pro_rich_snippets_product_brand' => [
            'key' => '_seopress_pro_rich_snippets_product_brand',
            'post_key' => 'seopress_pro_rich_snippets_product_brand',
        ],
        '_seopress_pro_rich_snippets_product_global_ids' => [
            'key' => '_seopress_pro_rich_snippets_product_global_ids',
            'post_key' => 'seopress_pro_rich_snippets_product_global_ids',
        ],
        '_seopress_pro_rich_snippets_product_global_ids_value' => [
            'key' => '_seopress_pro_rich_snippets_product_global_ids_value',
            'post_key' => 'seopress_pro_rich_snippets_product_global_ids_value',
        ],
        '_seopress_pro_rich_snippets_product_price_currency' => [
            'key' => '_seopress_pro_rich_snippets_product_price_currency',
            'post_key' => 'seopress_pro_rich_snippets_product_price_currency',
        ],
        '_seopress_pro_rich_snippets_product_condition' => [
            'key' => '_seopress_pro_rich_snippets_product_condition',
            'post_key' => 'seopress_pro_rich_snippets_product_condition',
        ],
        '_seopress_pro_rich_snippets_product_availability' => [
            'key' => '_seopress_pro_rich_snippets_product_availability',
            'post_key' => 'seopress_pro_rich_snippets_product_availability',
        ],
        '_seopress_pro_rich_snippets_softwareapp_name' => [
            'key' => '_seopress_pro_rich_snippets_softwareapp_name',
            'post_key' => 'seopress_pro_rich_snippets_softwareapp_name',
        ],
        '_seopress_pro_rich_snippets_softwareapp_os' => [
            'key' => '_seopress_pro_rich_snippets_softwareapp_os',
            'post_key' => 'seopress_pro_rich_snippets_softwareapp_os',
        ],
        '_seopress_pro_rich_snippets_softwareapp_cat' => [
            'key' => '_seopress_pro_rich_snippets_softwareapp_cat',
            'post_key' => 'seopress_pro_rich_snippets_softwareapp_cat',
        ],
        '_seopress_pro_rich_snippets_softwareapp_price' => [
            'key' => '_seopress_pro_rich_snippets_softwareapp_price',
            'post_key' => 'seopress_pro_rich_snippets_softwareapp_price',
        ],
        '_seopress_pro_rich_snippets_softwareapp_currency' => [
            'key' => '_seopress_pro_rich_snippets_softwareapp_currency',
            'post_key' => 'seopress_pro_rich_snippets_softwareapp_currency',
        ],
        '_seopress_pro_rich_snippets_softwareapp_rating' => [
            'key' => '_seopress_pro_rich_snippets_softwareapp_rating',
            'post_key' => 'seopress_pro_rich_snippets_softwareapp_rating',
        ],
        '_seopress_pro_rich_snippets_softwareapp_max_rating' => [
            'key' => '_seopress_pro_rich_snippets_softwareapp_max_rating',
            'post_key' => 'seopress_pro_rich_snippets_softwareapp_max_rating',
        ],
        '_seopress_pro_rich_snippets_service_name' => [
            'key' => '_seopress_pro_rich_snippets_service_name',
            'post_key' => 'seopress_pro_rich_snippets_service_name',
        ],
        '_seopress_pro_rich_snippets_service_type' => [
            'key' => '_seopress_pro_rich_snippets_service_type',
            'post_key' => 'seopress_pro_rich_snippets_service_type',
        ],
        '_seopress_pro_rich_snippets_service_description' => [
            'key' => '_seopress_pro_rich_snippets_service_description',
            'post_key' => 'seopress_pro_rich_snippets_service_description',
        ],
        '_seopress_pro_rich_snippets_service_img' => [
            'key' => '_seopress_pro_rich_snippets_service_img',
            'post_key' => 'seopress_pro_rich_snippets_service_img',
        ],
        '_seopress_pro_rich_snippets_service_area' => [
            'key' => '_seopress_pro_rich_snippets_service_area',
            'post_key' => 'seopress_pro_rich_snippets_service_area',
        ],
        '_seopress_pro_rich_snippets_service_provider_name' => [
            'key' => '_seopress_pro_rich_snippets_service_provider_name',
            'post_key' => 'seopress_pro_rich_snippets_service_provider_name',
        ],
        '_seopress_pro_rich_snippets_service_lb_img' => [
            'key' => '_seopress_pro_rich_snippets_service_lb_img',
            'post_key' => 'seopress_pro_rich_snippets_service_lb_img',
        ],
        '_seopress_pro_rich_snippets_service_provider_mobility' => [
            'key' => '_seopress_pro_rich_snippets_service_provider_mobility',
            'post_key' => 'seopress_pro_rich_snippets_service_provider_mobility',
        ],
        '_seopress_pro_rich_snippets_service_slogan' => [
            'key' => '_seopress_pro_rich_snippets_service_slogan',
            'post_key' => 'seopress_pro_rich_snippets_service_slogan',
        ],
        '_seopress_pro_rich_snippets_service_street_addr' => [
            'key' => '_seopress_pro_rich_snippets_service_street_addr',
            'post_key' => 'seopress_pro_rich_snippets_service_street_addr',
        ],
        '_seopress_pro_rich_snippets_service_city' => [
            'key' => '_seopress_pro_rich_snippets_service_city',
            'post_key' => 'seopress_pro_rich_snippets_service_city',
        ],
        '_seopress_pro_rich_snippets_service_state' => [
            'key' => '_seopress_pro_rich_snippets_service_state',
            'post_key' => 'seopress_pro_rich_snippets_service_state',
        ],
        '_seopress_pro_rich_snippets_service_pc' => [
            'key' => '_seopress_pro_rich_snippets_service_pc',
            'post_key' => 'seopress_pro_rich_snippets_service_pc',
        ],
        '_seopress_pro_rich_snippets_service_country' => [
            'key' => '_seopress_pro_rich_snippets_service_country',
            'post_key' => 'seopress_pro_rich_snippets_service_country',
        ],
        '_seopress_pro_rich_snippets_service_lat' => [
            'key' => '_seopress_pro_rich_snippets_service_lat',
            'post_key' => 'seopress_pro_rich_snippets_service_lat',
        ],
        '_seopress_pro_rich_snippets_service_lon' => [
            'key' => '_seopress_pro_rich_snippets_service_lon',
            'post_key' => 'seopress_pro_rich_snippets_service_lon',
        ],
        '_seopress_pro_rich_snippets_service_tel' => [
            'key' => '_seopress_pro_rich_snippets_service_tel',
            'post_key' => 'seopress_pro_rich_snippets_service_tel',
        ],
        '_seopress_pro_rich_snippets_service_price' => [
            'key' => '_seopress_pro_rich_snippets_service_price',
            'post_key' => 'seopress_pro_rich_snippets_service_price',
        ],
        '_seopress_pro_rich_snippets_review_item' => [
            'key' => '_seopress_pro_rich_snippets_review_item',
            'post_key' => 'seopress_pro_rich_snippets_review_item',
        ],
        '_seopress_pro_rich_snippets_review_item_type' => [
            'key' => '_seopress_pro_rich_snippets_review_item_type',
            'post_key' => 'seopress_pro_rich_snippets_review_item_type',
        ],
        '_seopress_pro_rich_snippets_review_img' => [
            'key' => '_seopress_pro_rich_snippets_review_img',
            'post_key' => 'seopress_pro_rich_snippets_review_img',
        ],
        '_seopress_pro_rich_snippets_review_rating' => [
            'key' => '_seopress_pro_rich_snippets_review_rating',
            'post_key' => 'seopress_pro_rich_snippets_review_rating',
        ],
        '_seopress_pro_rich_snippets_review_max_rating' => [
            'key' => '_seopress_pro_rich_snippets_review_max_rating',
            'post_key' => 'seopress_pro_rich_snippets_review_max_rating',
        ],
        '_seopress_pro_rich_snippets_review_body' => [
            'key' => '_seopress_pro_rich_snippets_review_body',
            'post_key' => 'seopress_pro_rich_snippets_review_body',
        ],
        '_seopress_pro_rich_snippets_custom' => [
            'key' => '_seopress_pro_rich_snippets_custom',
            'post_key' => 'seopress_pro_rich_snippets_custom',
        ],
    ];
}

/**
 * Function to recover old data from manual schemas.
 *
 * @deprecated 4.1
 * @since 3.9
 *
 * @return array
 *
 * @author Thomas Deneulin
 *
 * @param mixed $post
 */
function seopress_get_rich_snippets_fallback($post) {
    return [
        '_seopress_pro_rich_snippets_type' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_type', true),
        '_seopress_pro_rich_snippets_article_type' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_article_type', true),
        '_seopress_pro_rich_snippets_article_title' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_article_title', true),
        '_seopress_pro_rich_snippets_article_img' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_article_img', true),
        '_seopress_pro_rich_snippets_article_img_width' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_article_img_width', true),
        '_seopress_pro_rich_snippets_article_img_height' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_article_img_height', true),
        '_seopress_pro_rich_snippets_article_coverage_start_date' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_article_coverage_start_date', true),
        '_seopress_pro_rich_snippets_article_coverage_start_time' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_article_coverage_start_time', true),
        '_seopress_pro_rich_snippets_article_coverage_end_date' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_article_coverage_end_date', true),
        '_seopress_pro_rich_snippets_article_coverage_end_time' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_article_coverage_end_time', true),
        '_seopress_pro_rich_snippets_lb_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_name', true),
        '_seopress_pro_rich_snippets_lb_type' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_type', true),
        '_seopress_pro_rich_snippets_lb_cuisine' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_cuisine', true),
        '_seopress_pro_rich_snippets_lb_img' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_img', true),
        '_seopress_pro_rich_snippets_lb_img_width' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_img_width', true),
        '_seopress_pro_rich_snippets_lb_img_height' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_img_height', true),
        '_seopress_pro_rich_snippets_lb_street_addr' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_street_addr', true),
        '_seopress_pro_rich_snippets_lb_city' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_city', true),
        '_seopress_pro_rich_snippets_lb_state' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_state', true),
        '_seopress_pro_rich_snippets_lb_pc' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_pc', true),
        '_seopress_pro_rich_snippets_lb_country' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_country', true),
        '_seopress_pro_rich_snippets_lb_lat' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_lat', true),
        '_seopress_pro_rich_snippets_lb_lon' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_lon', true),
        '_seopress_pro_rich_snippets_lb_website' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_website', true),
        '_seopress_pro_rich_snippets_lb_tel' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_tel', true),
        '_seopress_pro_rich_snippets_lb_price' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_price', true),
        '_seopress_pro_rich_snippets_lb_opening_hours' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_opening_hours', false),
        '_seopress_pro_rich_snippets_faq' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_faq'),
        '_seopress_pro_rich_snippets_courses_title' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_courses_title', true),
        '_seopress_pro_rich_snippets_courses_desc' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_courses_desc', true),
        '_seopress_pro_rich_snippets_courses_school' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_courses_school', true),
        '_seopress_pro_rich_snippets_courses_website' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_courses_website', true),
        '_seopress_pro_rich_snippets_recipes_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_name', true),
        '_seopress_pro_rich_snippets_recipes_desc' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_desc', true),
        '_seopress_pro_rich_snippets_recipes_cat' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_cat', true),
        '_seopress_pro_rich_snippets_recipes_img' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_img', true),
        '_seopress_pro_rich_snippets_recipes_prep_time' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_prep_time', true),
        '_seopress_pro_rich_snippets_recipes_cook_time' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_cook_time', true),
        '_seopress_pro_rich_snippets_recipes_calories' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_calories', true),
        '_seopress_pro_rich_snippets_recipes_yield' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_yield', true),
        '_seopress_pro_rich_snippets_recipes_keywords' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_keywords', true),
        '_seopress_pro_rich_snippets_recipes_cuisine' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_cuisine', true),
        '_seopress_pro_rich_snippets_recipes_ingredient' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_ingredient', true),
        '_seopress_pro_rich_snippets_recipes_instructions' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_recipes_instructions', true),
        '_seopress_pro_rich_snippets_jobs_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_name', true),
        '_seopress_pro_rich_snippets_jobs_desc' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_desc', true),
        '_seopress_pro_rich_snippets_jobs_date_posted' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_date_posted', true),
        '_seopress_pro_rich_snippets_jobs_valid_through' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_valid_through', true),
        '_seopress_pro_rich_snippets_jobs_employment_type' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_employment_type', true),
        '_seopress_pro_rich_snippets_jobs_identifier_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_identifier_name', true),
        '_seopress_pro_rich_snippets_jobs_identifier_value' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_identifier_value', true),
        '_seopress_pro_rich_snippets_jobs_hiring_organization' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_hiring_organization', true),
        '_seopress_pro_rich_snippets_jobs_hiring_same_as' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_hiring_same_as', true),
        '_seopress_pro_rich_snippets_jobs_hiring_logo' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_hiring_logo', true),
        '_seopress_pro_rich_snippets_jobs_hiring_logo_width' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_hiring_logo_width', true),
        '_seopress_pro_rich_snippets_jobs_hiring_logo_height' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_hiring_logo_height', true),
        '_seopress_pro_rich_snippets_jobs_address_street' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_address_street', true),
        '_seopress_pro_rich_snippets_jobs_address_locality' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_address_locality', true),
        '_seopress_pro_rich_snippets_jobs_address_region' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_address_region', true),
        '_seopress_pro_rich_snippets_jobs_postal_code' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_postal_code', true),
        '_seopress_pro_rich_snippets_jobs_country' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_country', true),
        '_seopress_pro_rich_snippets_jobs_remote' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_remote', true),
        '_seopress_pro_rich_snippets_jobs_direct_apply' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_direct_apply', true),
        '_seopress_pro_rich_snippets_jobs_salary' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_salary', true),
        '_seopress_pro_rich_snippets_jobs_salary_currency' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_salary_currency', true),
        '_seopress_pro_rich_snippets_jobs_salary_unit' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_jobs_salary_unit', true),
        '_seopress_pro_rich_snippets_videos_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_videos_name', true),
        '_seopress_pro_rich_snippets_videos_description' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_videos_description', true),
        '_seopress_pro_rich_snippets_videos_img' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_videos_img', true),
        '_seopress_pro_rich_snippets_videos_img_width' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_videos_img_width', true),
        '_seopress_pro_rich_snippets_videos_img_height' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_videos_img_height', true),
        '_seopress_pro_rich_snippets_videos_duration' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_videos_duration', true),
        '_seopress_pro_rich_snippets_videos_url' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_videos_url', true),
        '_seopress_pro_rich_snippets_events_type' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_type', true),
        '_seopress_pro_rich_snippets_events_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_name', true),
        '_seopress_pro_rich_snippets_events_desc' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_desc', true),
        '_seopress_pro_rich_snippets_events_img' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_img', true),
        '_seopress_pro_rich_snippets_events_start_date' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_start_date', true),
        '_seopress_pro_rich_snippets_events_start_time' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_start_time', true),
        '_seopress_pro_rich_snippets_events_end_date' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_end_date', true),
        '_seopress_pro_rich_snippets_events_end_time' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_end_time', true),
        '_seopress_pro_rich_snippets_events_previous_start_date' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_previous_start_date', true),
        '_seopress_pro_rich_snippets_events_previous_start_time' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_previous_start_time', true),
        '_seopress_pro_rich_snippets_events_location_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_location_name', true),
        '_seopress_pro_rich_snippets_events_location_url' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_location_url', true),
        '_seopress_pro_rich_snippets_events_location_address' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_location_address', true),
        '_seopress_pro_rich_snippets_events_offers_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_offers_name', true),
        '_seopress_pro_rich_snippets_events_offers_cat' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_offers_cat', true),
        '_seopress_pro_rich_snippets_events_offers_price' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_offers_price', true),
        '_seopress_pro_rich_snippets_events_offers_price_currency' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_offers_price_currency', true),
        '_seopress_pro_rich_snippets_events_offers_availability' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_offers_availability', true),
        '_seopress_pro_rich_snippets_events_offers_valid_from_date' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_offers_valid_from_date', true),
        '_seopress_pro_rich_snippets_events_offers_valid_from_time' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_offers_valid_from_time', true),
        '_seopress_pro_rich_snippets_events_offers_url' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_offers_url', true),
        '_seopress_pro_rich_snippets_events_performer' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_performer', true),
        '_seopress_pro_rich_snippets_events_status' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_status', true),
        '_seopress_pro_rich_snippets_events_attendance_mode' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_events_attendance_mode', true),
        '_seopress_pro_rich_snippets_product_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_name', true),
        '_seopress_pro_rich_snippets_product_description' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_description', true),
        '_seopress_pro_rich_snippets_product_img' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_img', true),
        '_seopress_pro_rich_snippets_product_price' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_price', true),
        '_seopress_pro_rich_snippets_product_price_valid_date' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_price_valid_date', true),
        '_seopress_pro_rich_snippets_product_sku' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_sku', true),
        '_seopress_pro_rich_snippets_product_brand' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_brand', true),
        '_seopress_pro_rich_snippets_product_global_ids' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_global_ids', true),
        '_seopress_pro_rich_snippets_product_global_ids_value' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_global_ids_value', true),
        '_seopress_pro_rich_snippets_product_price_currency' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_price_currency', true),
        '_seopress_pro_rich_snippets_product_condition' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_condition', true),
        '_seopress_pro_rich_snippets_product_availability' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_product_availability', true),
        '_seopress_pro_rich_snippets_softwareapp_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_softwareapp_name', true),
        '_seopress_pro_rich_snippets_softwareapp_os' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_softwareapp_os', true),
        '_seopress_pro_rich_snippets_softwareapp_cat' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_softwareapp_cat', true),
        '_seopress_pro_rich_snippets_softwareapp_price' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_softwareapp_price', true),
        '_seopress_pro_rich_snippets_softwareapp_currency' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_softwareapp_currency', true),
        '_seopress_pro_rich_snippets_softwareapp_rating' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_softwareapp_rating', true),
        '_seopress_pro_rich_snippets_softwareapp_max_rating' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_softwareapp_max_rating', true),
        '_seopress_pro_rich_snippets_service_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_name', true),
        '_seopress_pro_rich_snippets_service_type' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_type', true),
        '_seopress_pro_rich_snippets_service_description' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_description', true),
        '_seopress_pro_rich_snippets_service_img' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_img', true),
        '_seopress_pro_rich_snippets_service_area' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_area', true),
        '_seopress_pro_rich_snippets_service_provider_name' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_provider_name', true),
        '_seopress_pro_rich_snippets_service_lb_img' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_lb_img', true),
        '_seopress_pro_rich_snippets_service_provider_mobility' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_provider_mobility', true),
        '_seopress_pro_rich_snippets_service_slogan' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_slogan', true),
        '_seopress_pro_rich_snippets_service_street_addr' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_street_addr', true),
        '_seopress_pro_rich_snippets_service_city' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_city', true),
        '_seopress_pro_rich_snippets_service_state' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_state', true),
        '_seopress_pro_rich_snippets_service_pc' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_pc', true),
        '_seopress_pro_rich_snippets_service_country' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_country', true),
        '_seopress_pro_rich_snippets_service_lat' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_lat', true),
        '_seopress_pro_rich_snippets_service_lon' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_lon', true),
        '_seopress_pro_rich_snippets_service_tel' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_tel', true),
        '_seopress_pro_rich_snippets_service_price' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_service_price', true),
        '_seopress_pro_rich_snippets_review_item' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_review_item', true),
        '_seopress_pro_rich_snippets_review_item_type' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_review_item_type', true),
        '_seopress_pro_rich_snippets_review_img' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_review_img', true),
        '_seopress_pro_rich_snippets_review_rating' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_review_rating', true),
        '_seopress_pro_rich_snippets_review_max_rating' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_review_max_rating', true),
        '_seopress_pro_rich_snippets_custom' => get_post_meta($post->ID, '_seopress_pro_rich_snippets_custom', true),
    ];
}

/**
 * Function to save old data from manual schemas.
 *
 * @deprecated 4.1
 * @since 3.9
 *
 * @return array
 *
 * @author Thomas Deneulin
 *
 * @param mixed $post_id
 */
function seopress_update_rich_snippets_fallback($post_id) {
    if (isset($_POST['seopress_pro_rich_snippets_type'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_type', esc_html($_POST['seopress_pro_rich_snippets_type']));
    }

    //Article
    if (isset($_POST['seopress_pro_rich_snippets_article_type'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_article_type', esc_html($_POST['seopress_pro_rich_snippets_article_type']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_title'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_article_title', esc_html($_POST['seopress_pro_rich_snippets_article_title']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_img'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_article_img', esc_html($_POST['seopress_pro_rich_snippets_article_img']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_img_width'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_article_img_width', esc_html($_POST['seopress_pro_rich_snippets_article_img_width']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_img_height'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_article_img_height', esc_html($_POST['seopress_pro_rich_snippets_article_img_height']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_start_date'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_article_coverage_start_date', esc_html($_POST['seopress_pro_rich_snippets_article_coverage_start_date']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_start_time'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_article_coverage_start_time', esc_html($_POST['seopress_pro_rich_snippets_article_coverage_start_time']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_end_date'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_article_coverage_end_date', esc_html($_POST['seopress_pro_rich_snippets_article_coverage_end_date']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_end_time'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_article_coverage_end_time', esc_html($_POST['seopress_pro_rich_snippets_article_coverage_end_time']));
    }
    //Local Business
    if (isset($_POST['seopress_pro_rich_snippets_lb_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_name', esc_html($_POST['seopress_pro_rich_snippets_lb_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_type'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_type', esc_html($_POST['seopress_pro_rich_snippets_lb_type']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_cuisine'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_cuisine', esc_html($_POST['seopress_pro_rich_snippets_lb_cuisine']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_img'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_img', esc_html($_POST['seopress_pro_rich_snippets_lb_img']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_img_width'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_img_width', esc_html($_POST['seopress_pro_rich_snippets_lb_img_width']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_img_height'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_img_height', esc_html($_POST['seopress_pro_rich_snippets_lb_img_height']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_street_addr'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_street_addr', esc_html($_POST['seopress_pro_rich_snippets_lb_street_addr']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_city'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_city', esc_html($_POST['seopress_pro_rich_snippets_lb_city']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_state'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_state', esc_html($_POST['seopress_pro_rich_snippets_lb_state']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_pc'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_pc', esc_html($_POST['seopress_pro_rich_snippets_lb_pc']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_country'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_country', esc_html($_POST['seopress_pro_rich_snippets_lb_country']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_lat'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_lat', esc_html($_POST['seopress_pro_rich_snippets_lb_lat']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_lon'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_lon', esc_html($_POST['seopress_pro_rich_snippets_lb_lon']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_website'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_website', esc_html($_POST['seopress_pro_rich_snippets_lb_website']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_tel'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_tel', esc_html($_POST['seopress_pro_rich_snippets_lb_tel']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_price'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_price', esc_html($_POST['seopress_pro_rich_snippets_lb_price']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_opening_hours'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_opening_hours', $_POST['seopress_pro_rich_snippets_lb_opening_hours']);
    }
    //FAQ
    if (isset($_POST['seopress_pro_rich_snippets_faq'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_faq', $_POST['seopress_pro_rich_snippets_faq']);
    }
    //Course
    if (isset($_POST['seopress_pro_rich_snippets_courses_title'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_courses_title', esc_html($_POST['seopress_pro_rich_snippets_courses_title']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_desc'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_courses_desc', esc_textarea($_POST['seopress_pro_rich_snippets_courses_desc']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_school'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_courses_school', esc_html($_POST['seopress_pro_rich_snippets_courses_school']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_website'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_courses_website', esc_html($_POST['seopress_pro_rich_snippets_courses_website']));
    }
    //Recipe
    if (isset($_POST['seopress_pro_rich_snippets_recipes_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_name', esc_html($_POST['seopress_pro_rich_snippets_recipes_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_desc'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_desc', esc_textarea($_POST['seopress_pro_rich_snippets_recipes_desc']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cat'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_cat', esc_html($_POST['seopress_pro_rich_snippets_recipes_cat']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_img'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_img', esc_html($_POST['seopress_pro_rich_snippets_recipes_img']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_prep_time'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_prep_time', esc_html($_POST['seopress_pro_rich_snippets_recipes_prep_time']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cook_time'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_cook_time', esc_html($_POST['seopress_pro_rich_snippets_recipes_cook_time']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_calories'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_calories', esc_html($_POST['seopress_pro_rich_snippets_recipes_calories']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_yield'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_yield', esc_html($_POST['seopress_pro_rich_snippets_recipes_yield']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_keywords'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_keywords', esc_html($_POST['seopress_pro_rich_snippets_recipes_keywords']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cuisine'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_cuisine', esc_html($_POST['seopress_pro_rich_snippets_recipes_cuisine']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_ingredient'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_ingredient', esc_textarea($_POST['seopress_pro_rich_snippets_recipes_ingredient']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_instructions'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_recipes_instructions', esc_textarea($_POST['seopress_pro_rich_snippets_recipes_instructions']));
    }
    //Job
    if (isset($_POST['seopress_pro_rich_snippets_jobs_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_name', esc_html($_POST['seopress_pro_rich_snippets_jobs_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_desc'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_desc', esc_textarea($_POST['seopress_pro_rich_snippets_jobs_desc']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_date_posted'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_date_posted', esc_html($_POST['seopress_pro_rich_snippets_jobs_date_posted']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_valid_through'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_valid_through', esc_html($_POST['seopress_pro_rich_snippets_jobs_valid_through']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_employment_type'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_employment_type', esc_html($_POST['seopress_pro_rich_snippets_jobs_employment_type']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_identifier_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_identifier_name', esc_html($_POST['seopress_pro_rich_snippets_jobs_identifier_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_identifier_value'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_identifier_value', esc_html($_POST['seopress_pro_rich_snippets_jobs_identifier_value']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_organization'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_hiring_organization', esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_organization']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_same_as'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_hiring_same_as', esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_same_as']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_logo'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_hiring_logo', esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_logo']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_width'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_hiring_logo_width', esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_width']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_height'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_hiring_logo_height', esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_height']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_street'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_address_street', esc_html($_POST['seopress_pro_rich_snippets_jobs_address_street']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_locality'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_address_locality', esc_html($_POST['seopress_pro_rich_snippets_jobs_address_locality']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_region'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_address_region', esc_html($_POST['seopress_pro_rich_snippets_jobs_address_region']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_postal_code'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_postal_code', esc_html($_POST['seopress_pro_rich_snippets_jobs_postal_code']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_country'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_country', esc_html($_POST['seopress_pro_rich_snippets_jobs_country']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_remote'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_remote', esc_attr($_POST['seopress_pro_rich_snippets_jobs_remote']));
    } else {
        delete_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_remote');
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_direct_apply'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_direct_apply', esc_attr($_POST['seopress_pro_rich_snippets_jobs_direct_apply']));
    } else {
        delete_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_direct_apply');
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_salary', esc_html($_POST['seopress_pro_rich_snippets_jobs_salary']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_currency'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_salary_currency', esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_currency']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_unit'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_jobs_salary_unit', esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_unit']));
    }
    //Video
    if (isset($_POST['seopress_pro_rich_snippets_videos_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_videos_name', esc_html($_POST['seopress_pro_rich_snippets_videos_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_description'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_videos_description', esc_textarea($_POST['seopress_pro_rich_snippets_videos_description']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_img'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_videos_img', esc_html($_POST['seopress_pro_rich_snippets_videos_img']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_img_width'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_videos_img_width', esc_html($_POST['seopress_pro_rich_snippets_videos_img_width']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_img_height'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_videos_img_height', esc_html($_POST['seopress_pro_rich_snippets_videos_img_height']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_duration'])) {
        $duration = $_POST['seopress_pro_rich_snippets_videos_duration'];
        $findme = ':';
        $pos = strpos($duration, $findme);
        if (false === $pos) {
            $_POST['seopress_pro_rich_snippets_videos_duration'] = '00:' . $_POST['seopress_pro_rich_snippets_videos_duration'];
        }
        update_post_meta($post_id, '_seopress_pro_rich_snippets_videos_duration', esc_html($_POST['seopress_pro_rich_snippets_videos_duration']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_url'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_videos_url', esc_html($_POST['seopress_pro_rich_snippets_videos_url']));
    }
    //Event
    if (isset($_POST['seopress_pro_rich_snippets_events_type'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_type', esc_html($_POST['seopress_pro_rich_snippets_events_type']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_name', esc_html($_POST['seopress_pro_rich_snippets_events_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_desc'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_desc', esc_html($_POST['seopress_pro_rich_snippets_events_desc']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_img'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_img', esc_html($_POST['seopress_pro_rich_snippets_events_img']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_desc'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_desc', esc_textarea($_POST['seopress_pro_rich_snippets_events_desc']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_date'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_start_date', esc_html($_POST['seopress_pro_rich_snippets_events_start_date']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_time'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_start_time', esc_html($_POST['seopress_pro_rich_snippets_events_start_time']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_end_date'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_end_date', esc_html($_POST['seopress_pro_rich_snippets_events_end_date']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_end_time'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_end_time', esc_html($_POST['seopress_pro_rich_snippets_events_end_time']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_previous_start_date'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_previous_start_date', esc_html($_POST['seopress_pro_rich_snippets_events_previous_start_date']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_previous_start_time'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_previous_start_time', esc_html($_POST['seopress_pro_rich_snippets_events_previous_start_time']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_location_name', esc_html($_POST['seopress_pro_rich_snippets_events_location_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_url'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_location_url', esc_html($_POST['seopress_pro_rich_snippets_events_location_url']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_address'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_location_address', esc_html($_POST['seopress_pro_rich_snippets_events_location_address']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_offers_name', esc_html($_POST['seopress_pro_rich_snippets_events_offers_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_cat'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_offers_cat', esc_html($_POST['seopress_pro_rich_snippets_events_offers_cat']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_price'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_offers_price', esc_html($_POST['seopress_pro_rich_snippets_events_offers_price']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_price_currency'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_offers_price_currency', esc_html($_POST['seopress_pro_rich_snippets_events_offers_price_currency']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_availability'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_offers_availability', esc_html($_POST['seopress_pro_rich_snippets_events_offers_availability']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_valid_from_date'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_offers_valid_from_date', esc_html($_POST['seopress_pro_rich_snippets_events_offers_valid_from_date']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_valid_from_time'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_offers_valid_from_time', esc_html($_POST['seopress_pro_rich_snippets_events_offers_valid_from_time']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_url'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_offers_url', esc_html($_POST['seopress_pro_rich_snippets_events_offers_url']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_performer'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_performer', esc_html($_POST['seopress_pro_rich_snippets_events_performer']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_status'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_status', esc_html($_POST['seopress_pro_rich_snippets_events_status']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_attendance_mode'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_events_attendance_mode', esc_html($_POST['seopress_pro_rich_snippets_events_attendance_mode']));
    }
    //Product
    if (isset($_POST['seopress_pro_rich_snippets_product_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_name', esc_html($_POST['seopress_pro_rich_snippets_product_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_description'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_description', esc_textarea($_POST['seopress_pro_rich_snippets_product_description']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_img'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_img', esc_html($_POST['seopress_pro_rich_snippets_product_img']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_price', esc_html($_POST['seopress_pro_rich_snippets_product_price']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_valid_date'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_price_valid_date', esc_html($_POST['seopress_pro_rich_snippets_product_price_valid_date']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_sku'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_sku', esc_html($_POST['seopress_pro_rich_snippets_product_sku']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_global_ids'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_global_ids', esc_html($_POST['seopress_pro_rich_snippets_product_global_ids']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_brand'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_brand', esc_html($_POST['seopress_pro_rich_snippets_product_brand']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_global_ids_value'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_global_ids_value', esc_html($_POST['seopress_pro_rich_snippets_product_global_ids_value']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_currency'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_price_currency', esc_html($_POST['seopress_pro_rich_snippets_product_price_currency']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_condition'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_condition', esc_html($_POST['seopress_pro_rich_snippets_product_condition']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_availability'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_product_availability', esc_html($_POST['seopress_pro_rich_snippets_product_availability']));
    }
    //Service
    if (isset($_POST['seopress_pro_rich_snippets_service_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_name', esc_html($_POST['seopress_pro_rich_snippets_service_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_type'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_type', esc_html($_POST['seopress_pro_rich_snippets_service_type']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_description'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_description', esc_textarea($_POST['seopress_pro_rich_snippets_service_description']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_img'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_img', esc_html($_POST['seopress_pro_rich_snippets_service_img']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_area'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_area', esc_html($_POST['seopress_pro_rich_snippets_service_area']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_provider_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_provider_name', esc_html($_POST['seopress_pro_rich_snippets_service_provider_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lb_img'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_lb_img', esc_html($_POST['seopress_pro_rich_snippets_service_lb_img']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_provider_mobility'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_provider_mobility', esc_html($_POST['seopress_pro_rich_snippets_service_provider_mobility']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_slogan'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_slogan', esc_html($_POST['seopress_pro_rich_snippets_service_slogan']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_street_addr'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_street_addr', esc_html($_POST['seopress_pro_rich_snippets_service_street_addr']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_city'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_city', esc_html($_POST['seopress_pro_rich_snippets_service_city']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_state'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_state', esc_html($_POST['seopress_pro_rich_snippets_service_state']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_pc'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_pc', esc_html($_POST['seopress_pro_rich_snippets_service_pc']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_country'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_country', esc_html($_POST['seopress_pro_rich_snippets_service_country']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lat'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_lat', esc_html($_POST['seopress_pro_rich_snippets_service_lat']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lon'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_lon', esc_html($_POST['seopress_pro_rich_snippets_service_lon']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_tel'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_tel', esc_html($_POST['seopress_pro_rich_snippets_service_tel']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_price'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_service_price', esc_html($_POST['seopress_pro_rich_snippets_service_price']));
    }
    //Software App
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_name'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_softwareapp_name', esc_html($_POST['seopress_pro_rich_snippets_softwareapp_name']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_os'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_softwareapp_os', esc_html($_POST['seopress_pro_rich_snippets_softwareapp_os']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_cat'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_softwareapp_cat', esc_html($_POST['seopress_pro_rich_snippets_softwareapp_cat']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_price'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_softwareapp_price', esc_html($_POST['seopress_pro_rich_snippets_softwareapp_price']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_currency'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_softwareapp_currency', esc_html($_POST['seopress_pro_rich_snippets_softwareapp_currency']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_rating'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_softwareapp_rating', esc_html($_POST['seopress_pro_rich_snippets_softwareapp_rating']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_max_rating'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_softwareapp_max_rating', esc_html($_POST['seopress_pro_rich_snippets_softwareapp_max_rating']));
    }
    //Review
    if (isset($_POST['seopress_pro_rich_snippets_review_item'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_review_item', esc_html($_POST['seopress_pro_rich_snippets_review_item']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_item_type'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_review_item_type', esc_html($_POST['seopress_pro_rich_snippets_review_item_type']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_img'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_review_img', esc_html($_POST['seopress_pro_rich_snippets_review_img']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_rating'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_review_rating', esc_html($_POST['seopress_pro_rich_snippets_review_rating']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_max_rating'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_review_max_rating', esc_html($_POST['seopress_pro_rich_snippets_review_max_rating']));
    }

    //Custom schema
    if (isset($_POST['seopress_pro_rich_snippets_custom'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_custom', esc_textarea($_POST['seopress_pro_rich_snippets_custom']));
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Display Rich Snippets metabox in Custom Post Type
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_pro_admin_std_metaboxe_display() {
    add_action('add_meta_boxes', 'seopress_pro_init_metabox', 20);
    function seopress_pro_init_metabox() {
        if (function_exists('seopress_advanced_appearance_metaboxe_position_option')) {
            $seopress_advanced_appearance_metaboxe_position_option = seopress_advanced_appearance_metaboxe_position_option();
        } else {
            $seopress_advanced_appearance_metaboxe_position_option = 'default';
        }

        if (function_exists('seopress_get_post_types')) {
            $seopress_get_post_types = seopress_get_post_types();

            $seopress_get_post_types = apply_filters('seopress_pro_metaboxe_sdt', $seopress_get_post_types);

            if ( ! empty($seopress_get_post_types)) {
                foreach ($seopress_get_post_types as $key => $value) {
                    add_meta_box('seopress_pro_cpt', __('Structured Data Types', 'wp-seopress-pro'), 'seopress_pro_cpt', $key, 'normal', $seopress_advanced_appearance_metaboxe_position_option);
                }
            }
        }
    }

    function seopress_advanced_appearance_advice_schema_option() {
        $seopress_advanced_appearance_advice_schema_option = get_option('seopress_advanced_option_name');
        if ( ! empty($seopress_advanced_appearance_advice_schema_option)) {
            foreach ($seopress_advanced_appearance_advice_schema_option as $key => $seopress_advanced_appearance_advice_schema_value) {
                $options[$key] = $seopress_advanced_appearance_advice_schema_value;
            }
            if (isset($seopress_advanced_appearance_advice_schema_option['seopress_advanced_appearance_advice_schema'])) {
                return $seopress_advanced_appearance_advice_schema_option['seopress_advanced_appearance_advice_schema'];
            }
        }
    }

    function seopress_pro_cpt($post) {
        $options_schemas_available = [
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Article.php',
                'value' => 'articles',
                'label' => __('Article (WebPage)', 'wp-seopress-pro'),
                'key_html_part' => 'article',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/LocalBusiness.php',
                'value' => 'localbusiness',
                'label' => __('Local Business', 'wp-seopress-pro'),
                'key_html_part' => 'local-business',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Faq.php',
                'value' => 'faq',
                'label' => __('FAQ', 'wp-seopress-pro'),
                'key_html_part' => 'faq',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/HowTo.php',
                'value' => 'howto',
                'label' => __('How-to', 'wp-seopress-pro'),
                'key_html_part' => 'how-to',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Course.php',
                'value' => 'courses',
                'label' => __('Course', 'wp-seopress-pro'),
                'key_html_part' => 'course',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Recipe.php',
                'value' => 'recipes',
                'label' => __('Recipe', 'wp-seopress-pro'),
                'key_html_part' => 'recipe',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Job.php',
                'value' => 'jobs',
                'label' => __('Job', 'wp-seopress-pro'),
                'key_html_part' => 'jobs',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Video.php',
                'value' => 'videos',
                'label' => __('Video', 'wp-seopress-pro'),
                'key_html_part' => 'video',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Event.php',
                'value' => 'events',
                'label' => __('Event', 'wp-seopress-pro'),
                'key_html_part' => 'event',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Product.php',
                'value' => 'products',
                'label' => __('Product', 'wp-seopress-pro'),
                'key_html_part' => 'product',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/SoftwareApp.php',
                'value' => 'softwareapp',
                'label' => __('Software Application', 'wp-seopress-pro'),
                'key_html_part' => 'software',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Service.php',
                'value' => 'services',
                'label' => __('Service', 'wp-seopress-pro'),
                'key_html_part' => 'service',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Review.php',
                'value' => 'review',
                'label' => __('Review', 'wp-seopress-pro'),
                'key_html_part' => 'review',
            ],
            [
                'file' => dirname(__DIR__) . '/schemas/manual/Custom.php',
                'value' => 'custom',
                'label' => __('Custom', 'wp-seopress-pro'),
                'key_html_part' => 'custom',
            ],
        ];

        foreach ($options_schemas_available as $item) {
            include_once $item['file'];
        }

        $prefix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_nonce_field(plugin_basename(__FILE__), 'seopress_pro_cpt_nonce');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('seopress-pro-media-uploader-js', SEOPRESS_PRO_ASSETS_DIR . '/js/seopress-pro-media-uploader' . $prefix . '.js', ['jquery'], SEOPRESS_PRO_VERSION, false);
        wp_enqueue_script('seopress-pro-rich-snippets-js', SEOPRESS_PRO_ASSETS_DIR . '/js/seopress-pro-rich-snippets' . $prefix . '.js', ['jquery', 'jquery-ui-tabs'], SEOPRESS_PRO_VERSION, false);
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-datepicker');

        if (apply_filters('seopress_get_pro_schemas_manual', true)) {
            $seopress_pro_rich_snippets_data = get_post_meta($post->ID, '_seopress_pro_schemas_manual', true);
        } else {
            $seopress_pro_rich_snippets_data = seopress_get_rich_snippets_fallback($post);
        }

        $is_multidimensional = false;
        if (isset($seopress_pro_rich_snippets_data[0]) && is_array($seopress_pro_rich_snippets_data[0])) {
            $is_multidimensional = true;
        }

        $is_multidimensional = apply_filters('seopress_pro_rich_snippets_data_is_multidimensional', $is_multidimensional);

        $tab1 = '<li><a href="#seopress-schemas-tabs-2">' . __('Automatic', 'wp-seopress-pro') . '</a></li>';
        $tab2 = '';

        if ( ! seopress_get_service('EnqueueModuleMetabox')->canEnqueue()) {
            $tab2 = '<li><a href="#seopress-schemas-tabs-1">' . __('Manual', 'wp-seopress-pro') . '</a></li>';
        }
        $tabs = $tab1 . $tab2;
        if (function_exists('seopress_advanced_appearance_schema_default_tab_option') && seopress_advanced_appearance_schema_default_tab_option()) {
            if ('manual' == seopress_advanced_appearance_schema_default_tab_option()) {
                $tabs = $tab2 . $tab1;
            }
        }
        $docs = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';

        //Classic Editor compatibility
        if (function_exists('get_current_screen') && method_exists(get_current_screen(), 'is_block_editor') && true === get_current_screen()->is_block_editor()) {
            $btn_classes_tertiary = 'components-button is-tertiary';
        } else {
            $btn_classes_tertiary = 'submitdelete deletion';
        } ?>
<div id="seopress-schemas-tabs">
    <ul class="wrap-schemas-list">
        <?php if ( ! seopress_get_service('EnqueueModuleMetabox')->canEnqueue()) { ?>
        <li><a href="#seopress-schemas-tabs-1"><?php _e('Manual', 'wp-seopress-pro'); ?></a>
        </li>
        <?php } ?>
        <li><a id="sp-automatic-tab" href="#seopress-schemas-tabs-2"><?php _e('Automatic', 'wp-seopress-pro'); ?><span></span></a>
        </li>
    </ul>
    <input type="hidden" name="can_enqueue_seopress_metabox"
        value="<?php echo seopress_get_service('EnqueueModuleMetabox')->canEnqueue() ? '1' : '0'; ?>">

    <template id="js-select-template-schema">
        <div class="box-schema-item" data-key="[X]">
            <div class="wrap-rich-snippets-type">
                <button type="button" class="js-handle-snippet-type" aria-expanded="true">
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
                <div>
                    <label for="seopress_pro_rich_snippets_type_meta"><?php _e('Select your data type', 'wp-seopress-pro'); ?></label>
                    <select id="seopress_pro_rich_snippets_type" class="js-select_seopress_pro_rich_snippets_type"
                        name="seopress_pro_rich_snippets_data[X][seopress_pro_rich_snippets_type]">
                        <option value="none"><?php _e('None', 'wp-seopress-pro'); ?>
                        </option>
                        <?php foreach ($options_schemas_available as $item) { ?>
                        <option
                            value="<?php echo $item['value']; ?>">
                            <?php echo $item['label']; ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <a href="#"
                    class="js-delete-schema-manual <?php echo $btn_classes_tertiary; ?> is-destructive"
                    data-key="[X]">
                    <?php _e('Delete schema', 'wp-seopress-pro'); ?>
                </a>
            </div>
        </div>
    </template>
    <?php foreach ($options_schemas_available as $item) { ?>
    <template
        id="schema-template-<?php echo $item['value']; ?>">
        <?php seopress_get_schema_html_part($item['key_html_part'], [], 'X'); ?>
    </template>
    <?php } ?>
    <template id="schema-template-none">
        <div class="wrap-rich-snippets-item">
            <ul class="advice seopress-list seopress-notice">
                <li><?php _e('Be sure to select the right structure data type for your content.', 'wp-seopress-pro'); ?>
                </li>
                <li><?php _e('When you choose one, fill all fields with the right format.', 'wp-seopress-pro'); ?>
                </li>
                <li><?php _e('Make sure you don\'t have already included structured data type with a theme or plugin (eg: the default WooCommerce Theme, Storefront, already implements this for single page products).', 'wp-seopress-pro'); ?>
                </li>
                <li><?php _e('You can test your page with Google Data Structure Test tool.', 'wp-seopress-pro'); ?>
                    <a href="https://search.google.com/test/rich-results" target="_blank"><?php _e('Make a test', 'wp-seopress-pro'); ?></a>
                </li>
                <li>
                    <?php
                        /* translators: %s: documentation link */
                        printf(__('If you need help with schema, <a href="%s">read our full tutorial</a>.', 'wp-seopress-pro'), $docs['schemas']['add']); ?>
                </li>
            </ul>
        </div>
    </template>
    <template id="schema-template-empty">
        <div class="box-schema-item" data-key="[X]">
            <div class="wrap-rich-snippets-type">
                <button type="button" class="js-handle-snippet-type" aria-expanded="true">
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
                <div>
                    <label for="seopress_pro_rich_snippets_type_meta"><?php _e('Select your data type', 'wp-seopress-pro'); ?></label>
                    <select id="seopress_pro_rich_snippets_type" class="js-select_seopress_pro_rich_snippets_type"
                        name="seopress_pro_rich_snippets_data[X][seopress_pro_rich_snippets_type]">
                        <option value="none"><?php _e('None', 'wp-seopress-pro'); ?>
                        </option>
                        <?php foreach ($options_schemas_available as $item) { ?>
                        <option
                            value="<?php echo $item['value']; ?>">
                            <?php echo $item['label']; ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <a href="#"
                    class="js-delete-schema-manual <?php echo $btn_classes_tertiary; ?> is-destructive"
                    data-key="[X]">
                    <?php _e('Delete schema', 'wp-seopress-pro'); ?>
                </a>
            </div>
            <div class="wrap-rich-snippets-item">
                <ul class="advice seopress-list seopress-notice">
                    <li><?php _e('Be sure to select the right structure data type for your content.', 'wp-seopress-pro'); ?>
                    </li>
                    <li><?php _e('When you choose one, fill all fields with the right format.', 'wp-seopress-pro'); ?>
                    </li>
                    <li><?php _e('Make sure you don\'t have already included structured data type with a theme or plugin (eg: the default WooCommerce Theme, Storefront, already implements this for single page products).', 'wp-seopress-pro'); ?>
                    </li>
                    <li><?php _e('You can test your page with Google Data Structure Test tool.', 'wp-seopress-pro'); ?>
                        <a href="https://search.google.com/test/rich-results" target="_blank"><?php _e('Make a test', 'wp-seopress-pro'); ?></a>
                    </li>
                    <li>
                        <?php
                            /* translators: %s: documentation link */
                            printf(__('If you need help with schema, <a href="%s">read our full tutorial</a>.', 'wp-seopress-pro'), $docs['schemas']['add']); ?>
                    </li>
                </ul>
            </div>
        </div>
    </template>
    <?php if ( ! seopress_get_service('EnqueueModuleMetabox')->canEnqueue()) {?>
    <div id="seopress-schemas-tabs-1">
        <div class="box-left">
            <div class="schemas-bar-new">
                <p>
                    <a href="#" id="js-add-schema-manual"
                        class="<?php echo seopress_btn_secondary_classes(); ?>">
                        <?php _e('Add a schema', 'wp-seopress-pro'); ?>
                    </a>
                </p>
                <p>
                    <a href="#" class="js-expand-all"><?php _e('Expand', 'wp-seopress-pro'); ?></a>&nbsp;/&nbsp;<a
                        href="#" class="js-close-all"><?php _e('Close', 'wp-seopress-pro'); ?></a>
                </p>
            </div>
            <div id="js-box-list-schemas">
                <?php if ($is_multidimensional) { ?>
                <?php foreach ($seopress_pro_rich_snippets_data as $key => $data) {
                                $seopress_pro_rich_snippets_type = $data['_seopress_pro_rich_snippets_type']; ?>
                <div class="box-schema-item"
                    data-key="<?php echo $key; ?>">
                    <div class="wrap-rich-snippets-type">
                        <button type="button" class="js-handle-snippet-type" aria-expanded="true">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                        <div>
                            <label for="seopress_pro_rich_snippets_type_meta"><?php _e('Select your data type', 'wp-seopress-pro'); ?></label>
                            <select id="seopress_pro_rich_snippets_type"
                                class="js-select_seopress_pro_rich_snippets_type"
                                name="seopress_pro_rich_snippets_data[<?php echo $key; ?>][seopress_pro_rich_snippets_type]">
                                <option <?php echo selected('none', $seopress_pro_rich_snippets_type); ?>
                                    value="none"><?php _e('None', 'wp-seopress-pro'); ?>
                                </option>
                                <?php foreach ($options_schemas_available as $item) { ?>
                                <option <?php echo selected($item['value'], $seopress_pro_rich_snippets_type); ?>
                                    value="<?php echo $item['value']; ?>"><?php echo $item['label']; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>

                        <a href="#"
                            class="js-delete-schema-manual <?php echo $btn_classes_tertiary; ?> is-destructive"
                            data-key="<?php echo $key; ?>">
                            <?php _e('Delete schema', 'wp-seopress-pro'); ?>
                        </a>
                    </div>
                    <?php if ('1' != seopress_advanced_appearance_advice_schema_option() && 'none' === $seopress_pro_rich_snippets_type) { ?>
                    <div class="wrap-rich-snippets-item">
                        <ul class="advice seopress-list seopress-notice">
                            <li><?php _e('Be sure to select the right structure data type for your content.', 'wp-seopress-pro'); ?>
                            </li>
                            <li><?php _e('When you choose one, fill all fields with the right format.', 'wp-seopress-pro'); ?>
                            </li>
                            <li><?php _e('Make sure you don\'t have already included structured data type with a theme or plugin (eg: the default WooCommerce Theme, Storefront, already implements this for single page products).', 'wp-seopress-pro'); ?>
                            </li>
                            <li><?php _e('You can test your page with Google Data Structure Test tool.', 'wp-seopress-pro'); ?>
                                <a href="https://search.google.com/test/rich-results" target="_blank"><?php _e('Make a test', 'wp-seopress-pro'); ?></a>
                            </li>
                            <li>
                                <?php
                                    /* translators: %s: documentation link */
                                    printf(__('If you need help with schema, <a href="%s">read our full tutorial</a>.', 'wp-seopress-pro'), $docs['schemas']['add']);
                                ?>
                            </li>
                        </ul>
                    </div>
                    <?php } ?>


                    <?php

                                foreach ($options_schemas_available as $item) {
                                    if ($item['value'] === $seopress_pro_rich_snippets_type) {
                                        seopress_get_schema_html_part($item['key_html_part'], $data, $key);
                                    }
                                } ?>
                </div>
                <?php
                            } ?>

                <?php } else {
                                $seopress_pro_rich_snippets_type = isset($seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_type']) ? $seopress_pro_rich_snippets_data['_seopress_pro_rich_snippets_type'] : ''; ?>
                <div class="box-schema-item" data-key="0">
                    <div class="wrap-rich-snippets-type">
                        <div>
                            <label for="seopress_pro_rich_snippets_type_meta"><?php _e('Select your data type', 'wp-seopress-pro'); ?></label>
                            <select id="seopress_pro_rich_snippets_type"
                                class="js-select_seopress_pro_rich_snippets_type"
                                name="seopress_pro_rich_snippets_data[0][seopress_pro_rich_snippets_type]">
                                <option <?php echo selected('none', $seopress_pro_rich_snippets_type); ?>
                                    value="none"><?php _e('None', 'wp-seopress-pro'); ?>
                                </option>
                                <?php foreach ($options_schemas_available as $item) { ?>
                                <option <?php echo selected($item['value'], $seopress_pro_rich_snippets_type); ?>
                                    value="<?php echo $item['value']; ?>"><?php echo $item['label']; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <?php if ('1' != seopress_advanced_appearance_advice_schema_option() && 'none' === $seopress_pro_rich_snippets_type || empty($seopress_pro_rich_snippets_type)) { ?>
                    <div class="wrap-rich-snippets-item">
                        <ul class="advice seopress-list seopress-notice">
                            <li><?php _e('Be sure to select the right structure data type for your content.', 'wp-seopress-pro'); ?>
                            </li>
                            <li><?php _e('When you choose one, fill all fields with the right format.', 'wp-seopress-pro'); ?>
                            </li>
                            <li><?php _e('Make sure you don\'t have already included structured data type with a theme or plugin (eg: the default WooCommerce Theme, Storefront, already implements this for single page products).', 'wp-seopress-pro'); ?>
                            </li>
                            <li><?php _e('You can test your page with Google Data Structure Test tool.', 'wp-seopress-pro'); ?>
                                <a href="https://search.google.com/test/rich-results" target="_blank"><?php _e('Make a test', 'wp-seopress-pro'); ?></a>
                            </li>
                            <li>
                                <?php
                                    /* translators: %s: documentation link */
                                    printf(__('If you need help with schema, <a href="%s">read our full tutorial</a>.', 'wp-seopress-pro'), $docs['schemas']['add']);
                                ?>
                            </li>
                        </ul>
                    </div>
                    <?php } ?>

                    <?php

                                foreach ($options_schemas_available as $item) {
                                    if ($item['value'] === $seopress_pro_rich_snippets_type) {
                                        seopress_get_schema_html_part($item['key_html_part'], $seopress_pro_rich_snippets_data);
                                    }
                                } ?>
                </div>

                <?php
                            } ?>
            </div>
            <p>
                <a href="https://search.google.com/test/rich-results?url=<?php echo get_permalink(); ?>"
                    target="_blank"
                    class="<?php echo seopress_btn_secondary_classes(); ?>">
                    <?php _e('Validate my schema', 'wp-seopress-pro'); ?>
                </a>
            </p>
        </div>
    </div>

    <?php } ?>
    <div id="seopress-schemas-tabs-2">
        <?php include_once dirname(__FILE__) . '/admin-metaboxes-schemas.php'; ?>
    </div>
</div>
<?php
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    //Save datas
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    add_action('save_post', 'seopress_pro_save_metabox', 10, 2);
    function seopress_pro_save_metabox($post_id, $post) {
        //Nonce
        if ( ! isset($_POST['seopress_pro_cpt_nonce']) || ! wp_verify_nonce($_POST['seopress_pro_cpt_nonce'], plugin_basename(__FILE__))) {
            return $post_id;
        }

        //Post type object
        $post_type = get_post_type_object($post->post_type);

        //Check permission
        if ( ! current_user_can($post_type->cap->edit_post, $post_id)) {
            return $post_id;
        }

        if ('attachment' !== get_post_type($post_id) && 'seopress_schemas' !== get_post_type($post_id)) {
            //Automatic
            if (isset($_POST['seopress_pro_schemas'])) {
                update_post_meta($post_id, '_seopress_pro_schemas', $_POST['seopress_pro_schemas']);
            }

            //Disable all automatic schemas
            if (isset($_POST['seopress_pro_rich_snippets_disable_all'])) {
                update_post_meta($post_id, '_seopress_pro_rich_snippets_disable_all', esc_attr($_POST['seopress_pro_rich_snippets_disable_all']));
            } else {
                delete_post_meta($post_id, '_seopress_pro_rich_snippets_disable_all', '');
            }

            //Disable automatic schemas individually
            if (isset($_POST['seopress_pro_rich_snippets_disable'])) {
                update_post_meta($post_id, '_seopress_pro_rich_snippets_disable', $_POST['seopress_pro_rich_snippets_disable']);
            } else {
                delete_post_meta($post_id, '_seopress_pro_rich_snippets_disable', '');
            }

            // SEOPress >= 3.9
            if (apply_filters('seopress_get_pro_schemas_manual', true) && ! seopress_get_service('EnqueueModuleMetabox')->canEnqueue()) {
                $_seopress_pro_rich_snippets_videos_duration = null;
                if (isset($_POST['seopress_pro_rich_snippets_videos_duration'])) {
                    $duration = $_POST['seopress_pro_rich_snippets_videos_duration'];
                    $findme = ':';
                    $pos = strpos($duration, $findme);
                    if (false === $pos) {
                        $_POST['seopress_pro_rich_snippets_videos_duration'] = '00:' . $_POST['seopress_pro_rich_snippets_videos_duration'];
                    }
                    $_seopress_pro_rich_snippets_videos_duration = esc_html($_POST['seopress_pro_rich_snippets_videos_duration']);
                }

                if ( ! isset($_POST['seopress_pro_rich_snippets_data'])) {
                    delete_post_meta($post_id, '_seopress_pro_schemas_manual');

                    return;
                }

                $data_schemas = $_POST['seopress_pro_rich_snippets_data'];
                $keys_rich_snippets = seopress_get_keys_rich_snippets();
                $data_pro_rich_snippets = [];

                foreach ($data_schemas as $number_item => $value) {
                    foreach ($keys_rich_snippets as $key => $item) {
                        if (isset($value[$item['post_key']])) {
                            $data_pro_rich_snippets[$number_item][$item['key']] = $value[$item['post_key']];
                        }
                    }
                }

                update_post_meta($post_id, '_seopress_pro_schemas_manual', array_values($data_pro_rich_snippets));
            } else {
                seopress_update_rich_snippets_fallback($post_id);
            }
        }
    }
}

if ('1' == seopress_get_toggle_option('rich-snippets') && '1' == seopress_rich_snippets_enable_option()) {
    if (is_user_logged_in()) {
        if (is_super_admin()) {
            echo seopress_pro_admin_std_metaboxe_display();
        } else {
            global $wp_roles;

            //Get current user role
            if (isset(wp_get_current_user()->roles[0])) {
                $seopress_user_role = wp_get_current_user()->roles[0];
                //If current user role matchs values from Security settings then apply
                if (function_exists('seopress_advanced_security_metaboxe_sdt_role_hook_option') && '' != seopress_advanced_security_metaboxe_sdt_role_hook_option()) {
                    if (array_key_exists($seopress_user_role, seopress_advanced_security_metaboxe_sdt_role_hook_option())) {
                        //do nothing
                    } else {
                        echo seopress_pro_admin_std_metaboxe_display();
                    }
                } else {
                    echo seopress_pro_admin_std_metaboxe_display();
                }
            }
        }
    }
}
