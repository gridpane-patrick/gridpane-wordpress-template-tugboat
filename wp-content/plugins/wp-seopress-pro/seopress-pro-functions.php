<?php

if ( ! defined('ABSPATH')) {
    exit;
}

use SEOPressPro\Core\Kernel;

/**
 * Get a service.
 *
 * @since 4.3.0
 *
 * @param string $service
 *
 * @return object
 */
function seopress_pro_get_service($service) {
    return Kernel::getContainer()->getServiceByName($service);
}

/**
 * Enable Google Suggestions
 *
 * @since 5.0
 *
 * @param boolean true
 *
 * @return boolean
 */
add_filter('seopress_ui_metabox_google_suggest', '__return_true');

/**
 * Get Page Speed Mobile Score
 *
 * @since 5.3
 *
 * @return string
 * @param mixed $json
 * @param mixed $mobile
 */
function seopress_pro_get_ps_score($json, $is_mobile = false) {
    if (!is_array($json)) {
        return;
    }
    if ( array_key_first($json) === 'error') {
        return;
    }

    $ps_score = $json['lighthouseResult']['categories']['performance']['score'] ? ($json['lighthouseResult']['categories']['performance']['score']) * 100 : '';
    if ($is_mobile ===  true) {
        $i18n = __('Mobile', 'wp-seopress-pro');
    } else {
        $i18n = __('Desktop', 'wp-seopress-pro');
    }

    if ($ps_score >= 0 && $ps_score < 49) {
        $class_score = 'red';
    } elseif ($ps_score >= 50 && $ps_score < 90) {
        $class_score = 'yellow';
    } elseif ($ps_score >= 90 && $ps_score <= 100) {
        $class_score = 'green';
    } else {
        $class_score = 'grey';
    }

    $perf_score = '<div class="wrap-chart">
    <p>' . $i18n . '</p>
        <div class="ps-score ' . $class_score . '">
            <svg role="img" aria-hidden="true" focusable="false" width="100%" height="100%" viewBox="0 0 36 36" version="1.1" xmlns="http://www.w3.org/2000/svg">
                <path stroke-dasharray="100, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                <path id="bar" stroke-dasharray="' . $ps_score . ', 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
            </svg>
            <span>' . $ps_score . '%</span>
        </div>
    </div>';

    return $perf_score;
}

/**
 * Get Core Web Vitals Score
 *
 * @since 5.3
 *
 * @return string
 * @param mixed $json
 */
function seopress_pro_get_cwv_score($json) {
    if (array_key_first($json) === 'error') {
        return;
    }
    $core_web_vitals_score = false;

    if (!isset($json['loadingExperience']['metrics'])) {
        return $core_web_vitals_score = null;
    }

    if (
                    (isset($json['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['category']) && $json['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['category'] === 'FAST') &&
                (isset($json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['category']) && $json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['category'] === 'FAST') &&
                (isset($json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['category']) && $json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['category'] === 'FAST')) {
        $core_web_vitals_score = true;
    } elseif (
                    (isset($json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['category']) && $json['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['category'] === 'FAST') &&
                    (isset($json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['category']) && $json['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['category'] === 'FAST')
                ) {
        $core_web_vitals_score = true;
    }

    return $core_web_vitals_score;
}

/**
 * Get GA Dashboard widget option
 *
 * @since 5.3
 *
 * @return string
 */
$sp_versions = get_option('seopress_versions');
if (isset($sp_versions['free']) && version_compare($sp_versions['free'], '5.7', '>=')) {
    function seopress_google_analytics_dashboard_widget_option() {
        $seopress_google_analytics_dashboard_widget_option = get_option('seopress_google_analytics_option_name');
        if ( ! empty($seopress_google_analytics_dashboard_widget_option)) {
            foreach ($seopress_google_analytics_dashboard_widget_option as $key => $seopress_google_analytics_dashboard_widget_value) {
                $options[$key] = $seopress_google_analytics_dashboard_widget_value;
            }
            if (isset($seopress_google_analytics_dashboard_widget_option['seopress_google_analytics_dashboard_widget'])) {
                return $seopress_google_analytics_dashboard_widget_option['seopress_google_analytics_dashboard_widget'];
            }
        }
    }
}
