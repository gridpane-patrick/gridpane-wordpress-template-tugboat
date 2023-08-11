<?php
defined('ABSPATH') or die('Please don&rsquo;t call the plugin directly. Thanks :)');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Robots.txt
///////////////////////////////////////////////////////////////////////////////////////////////////
//Robots Enable
if (is_multisite() && defined('SUBDOMAIN_INSTALL') && constant('SUBDOMAIN_INSTALL') === false) {//subdirectories
    function seopress_pro_robots_enable_option() {
        $seopress_pro_mu_robots_enable_option = get_option('seopress_pro_mu_option_name');
        if ( ! empty($seopress_pro_mu_robots_enable_option)) {
            foreach ($seopress_pro_mu_robots_enable_option as $key => $seopress_pro_mu_robots_enable_value) {
                $options[$key] = $seopress_pro_mu_robots_enable_value;
            }
            if (isset($seopress_pro_mu_robots_enable_option['seopress_mu_robots_enable'])) {
                return $seopress_pro_mu_robots_enable_option['seopress_mu_robots_enable'];
            }
        }
    }
} else {
    function seopress_pro_robots_enable_option() {
        $seopress_pro_robots_enable_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_pro_robots_enable_option)) {
            foreach ($seopress_pro_robots_enable_option as $key => $seopress_pro_robots_enable_value) {
                $options[$key] = $seopress_pro_robots_enable_value;
            }
            if (isset($seopress_pro_robots_enable_option['seopress_robots_enable'])) {
                return $seopress_pro_robots_enable_option['seopress_robots_enable'];
            }
        }
    }
}
//Options Robots.txt
if (seopress_pro_robots_enable_option() == '1') {
    if (is_multisite() && defined('SUBDOMAIN_INSTALL') && constant('SUBDOMAIN_INSTALL') === false) {//subdirectories
        function seopress_pro_robots_file_option() {
            $seopress_pro_mu_robots_file_option = get_option('seopress_pro_mu_option_name');
            if ( ! empty($seopress_pro_mu_robots_file_option)) {
                foreach ($seopress_pro_mu_robots_file_option as $key => $seopress_pro_mu_robots_file_value) {
                    $options[$key] = $seopress_pro_mu_robots_file_value;
                }
                if (isset($seopress_pro_mu_robots_file_option['seopress_mu_robots_file'])) {
                    return $seopress_pro_mu_robots_file_option['seopress_mu_robots_file'];
                }
            }
        }
    } else {
        function seopress_pro_robots_file_option() {
            $seopress_pro_robots_file_option = get_option('seopress_pro_option_name');
            if ( ! empty($seopress_pro_robots_file_option)) {
                foreach ($seopress_pro_robots_file_option as $key => $seopress_pro_robots_file_value) {
                    $options[$key] = $seopress_pro_robots_file_value;
                }
                if (isset($seopress_pro_robots_file_option['seopress_robots_file'])) {
                    return $seopress_pro_robots_file_option['seopress_robots_file'];
                }
            }
        }
    }

    function seopress_filter_robots_txt($output, $public) {
        $seopress_robots = seopress_pro_robots_file_option();
        $seopress_robots = apply_filters('seopress_robots_txt_file', $seopress_robots);
        return $seopress_robots;
    };
    add_filter('robots_txt', 'seopress_filter_robots_txt', 10, 2);
}
