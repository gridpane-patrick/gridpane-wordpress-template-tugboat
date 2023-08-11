<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');
///////////////////////////////////////////////////////////////////////////////////////////////////
//SEOPress Bot
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_request_bot() {
    check_ajax_referer('seopress_request_bot_nonce');

    if (current_user_can(seopress_capability('manage_options', 'bot')) && is_admin()) {
        //Init
        $data = [];

        //Links cleaning
        function seopress_bot_scan_settings_cleaning_option() {
            $seopress_bot_scan_settings_cleaning_option = get_option('seopress_bot_option_name');
            if ( ! empty($seopress_bot_scan_settings_cleaning_option)) {
                foreach ($seopress_bot_scan_settings_cleaning_option as $key => $seopress_bot_scan_settings_cleaning_value) {
                    $options[$key] = $seopress_bot_scan_settings_cleaning_value;
                }
                if (isset($seopress_bot_scan_settings_cleaning_option['seopress_bot_scan_settings_cleaning'])) {
                    return $seopress_bot_scan_settings_cleaning_option['seopress_bot_scan_settings_cleaning'];
                }
            }
        }

        //Cleaning seopress_bot post type
        if (1 == seopress_bot_scan_settings_cleaning_option() && isset($_POST['offset']) && 0 == $_POST['offset']) {
            global $wpdb;

            // delete all posts by post type.
            $sql = 'DELETE `posts`, `pm`
				FROM `' . $wpdb->prefix . 'posts` AS `posts`
				LEFT JOIN `' . $wpdb->prefix . 'postmeta` AS `pm` ON `pm`.`post_id` = `posts`.`ID`
				WHERE `posts`.`post_type` = \'seopress_bot\'';
            $wpdb->query($sql);
        }

        if (isset($_POST['offset'])) {
            $offset = absint($_POST['offset']);
        }

        //Type of links
        function seopress_bot_scan_settings_type_option() {
            $seopress_bot_scan_settings_type_option = get_option('seopress_bot_option_name');
            if ( ! empty($seopress_bot_scan_settings_type_option)) {
                foreach ($seopress_bot_scan_settings_type_option as $key => $seopress_bot_scan_settings_type_value) {
                    $options[$key] = $seopress_bot_scan_settings_type_value;
                }
                if (isset($seopress_bot_scan_settings_type_option['seopress_bot_scan_settings_type'])) {
                    return $seopress_bot_scan_settings_type_option['seopress_bot_scan_settings_type'];
                }
            }
        }

        //Find links in
        function seopress_bot_scan_settings_where_option() {
            $seopress_bot_scan_settings_where_option = get_option('seopress_bot_option_name');
            if ( ! empty($seopress_bot_scan_settings_where_option)) {
                foreach ($seopress_bot_scan_settings_where_option as $key => $seopress_bot_scan_settings_where_value) {
                    $options[$key] = $seopress_bot_scan_settings_where_value;
                }
                if (isset($seopress_bot_scan_settings_where_option['seopress_bot_scan_settings_where'])) {
                    return $seopress_bot_scan_settings_where_option['seopress_bot_scan_settings_where'];
                }
            }
        }

        //404 only
        function seopress_bot_scan_settings_404_option() {
            $seopress_bot_scan_settings_404_option = get_option('seopress_bot_option_name');
            if ( ! empty($seopress_bot_scan_settings_404_option)) {
                foreach ($seopress_bot_scan_settings_404_option as $key => $seopress_bot_scan_settings_404_value) {
                    $options[$key] = $seopress_bot_scan_settings_404_value;
                }
                if (isset($seopress_bot_scan_settings_404_option['seopress_bot_scan_settings_404'])) {
                    return $seopress_bot_scan_settings_404_option['seopress_bot_scan_settings_404'];
                }
            }
        }

        //Timeout
        function seopress_bot_scan_settings_timeout_option() {
            $seopress_bot_scan_settings_timeout_option = get_option('seopress_bot_option_name');
            if ( ! empty($seopress_bot_scan_settings_timeout_option)) {
                foreach ($seopress_bot_scan_settings_timeout_option as $key => $seopress_bot_scan_settings_timeout_value) {
                    $options[$key] = $seopress_bot_scan_settings_timeout_value;
                }
                if (isset($seopress_bot_scan_settings_timeout_option['seopress_bot_scan_settings_timeout'])) {
                    return $seopress_bot_scan_settings_timeout_option['seopress_bot_scan_settings_timeout'];
                }
            }
        }

        //Number of content to scan
        function seopress_bot_scan_settings_number_option() {
            $seopress_bot_scan_settings_number_option = get_option('seopress_bot_option_name');
            if ( ! empty($seopress_bot_scan_settings_number_option)) {
                foreach ($seopress_bot_scan_settings_number_option as $key => $seopress_bot_scan_settings_number_value) {
                    $options[$key] = $seopress_bot_scan_settings_number_value;
                }
                if (isset($seopress_bot_scan_settings_number_option['seopress_bot_scan_settings_number'])) {
                    return $seopress_bot_scan_settings_number_option['seopress_bot_scan_settings_number'];
                }
            }
        }

        //Include Custom Post Types
        function seopress_bot_scan_settings_post_types_option() {
            $seopress_bot_scan_settings_post_types_option = get_option('seopress_bot_option_name');
            if ( ! empty($seopress_bot_scan_settings_post_types_option)) {
                foreach ($seopress_bot_scan_settings_post_types_option as $key => $seopress_bot_scan_settings_post_types_value) {
                    $options[$key] = $seopress_bot_scan_settings_post_types_value;
                }
                if (isset($seopress_bot_scan_settings_post_types_option['seopress_bot_scan_settings_post_types'])) {
                    return $seopress_bot_scan_settings_post_types_option['seopress_bot_scan_settings_post_types'];
                }
            }
        }
        if ('' != seopress_bot_scan_settings_post_types_option()) {
            $seopress_bot_post_types_cpt_array = [];
            foreach (seopress_bot_scan_settings_post_types_option() as $cpt_key => $cpt_value) {
                foreach ($cpt_value as $_cpt_key => $_cpt_value) {
                    if ('1' == $_cpt_value) {
                        array_push($seopress_bot_post_types_cpt_array, $cpt_key);
                    }
                }
            }

            if ('' != seopress_bot_scan_settings_number_option() && seopress_bot_scan_settings_number_option() >= 10) {
                $limit = seopress_bot_scan_settings_number_option();
            } else {
                $limit = 100;
            }

            global $post;

            if ($offset > $limit) {
                wp_reset_query();
                //Log date
                update_option('seopress-bot-log', current_time('Y-m-d H:i'), 'yes');

                $offset = 'done';
            } else {
                $args = [
                    'posts_per_page' => 1,
                    'offset' => $offset,
                    'cache_results' => false,
                    'order' => 'DESC',
                    'orderby' => 'date',
                    'post_type' => $seopress_bot_post_types_cpt_array,
                    'post_status' => 'publish',
                    'fields' => 'ids',
                ];
                $args = apply_filters('seopress_bot_query', $args);
                $bot_query = get_posts($args);

                if ($bot_query) {
                    //DOM
                    $dom = new DOMDocument();
                    $internalErrors = libxml_use_internal_errors(true);
                    $dom->preserveWhiteSpace = false;

                    //Get source code
                    if ('' != seopress_bot_scan_settings_timeout_option()) {
                        $timeout = seopress_bot_scan_settings_timeout_option();
                    } else {
                        $timeout = 5;
                    }

                    //Get cookies
                    if (isset($_COOKIE)) {
                        $cookies = [];

                        foreach ($_COOKIE as $name => $value) {
                            if ('PHPSESSID' !== $name) {
                                $cookies[] = new WP_Http_Cookie(['name' => $name, 'value' => $value]);
                            }
                        }
                    }

                    $args = [
                        'blocking' => true,
                        'timeout' => $timeout,
                        'sslverify' => false,
                        'compress' => true,
                        'redirection' => 4,
                    ];

                    if (isset($cookies) && ! empty($cookies)) {
                        $args['cookies'] = $cookies;
                    }

                    foreach ($bot_query as $post) {
                        if ('' == seopress_bot_scan_settings_where_option() || 'post_content' == seopress_bot_scan_settings_where_option()) {//post content
                            //this code will not run shortcodes
                            $response = get_post_field('post_content', $post);
                        } else { //body page
                            $response = wp_remote_get(get_permalink($post), $args);

                            //Check for error
                            if (is_wp_error($response) || '404' === wp_remote_retrieve_response_code($response)) {
                                $data['post_title'] = __('Unable to request page: ', 'wp-seopress-pro') . get_the_title($post);
                            } else {
                                $response = wp_remote_retrieve_body($response);
                            }
                        }

                        if ( ! is_wp_error($response) || '404' !== wp_remote_retrieve_response_code($response)) {
                            if (get_the_title($post)) {
                                $data['post_title'] = get_the_title($post) . ' (' . get_permalink($post) . ')';

                                if ($dom->loadHTML('<?xml encoding="utf-8" ?>' . $response)) {
                                    $xpath = new DOMXPath($dom);

                                    //Links
                                    $links = $xpath->query('//a');

                                    if ( ! empty($links)) {
                                        foreach ($links as $key => $link) {
                                            $links2 = [];
                                            $links3 = [];

                                            $href = $link->getAttribute('href');
                                            $text = esc_attr($link->textContent);

                                            //remove anchors
                                            if ('#' != $href) {
                                                $links2[$text] = $href;
                                            }

                                            //remove duplicates
                                            $links2 = array_unique($links2);

                                            foreach ($links2 as $_key => $_value) {
                                                $args = [
                                                    'timeout' => $timeout,
                                                    'blocking' => true,
                                                    'sslverify' => false,
                                                    'compress' => true,
                                                ];

                                                $response = wp_remote_get($_value, $args);

                                                $bot_status_code = wp_remote_retrieve_response_code($response);

                                                if ( ! $bot_status_code) {
                                                    $bot_status_code = __('domain not found', 'wp-seopress-pro');
                                                }

                                                if ('1' == seopress_bot_scan_settings_type_option()) {
                                                    $bot_status_type = wp_remote_retrieve_header($response, 'content-type');
                                                }

                                                if ('1' == seopress_bot_scan_settings_404_option()) {
                                                    if ('404' == $bot_status_code || strpos(json_encode($response), 'cURL error 6')) {
                                                        $links3[] = $_value;
                                                    }
                                                } else {
                                                    $links3[] = $_value;
                                                }
                                            }

                                            foreach ($links3 as $_key => $_value) {
                                                $check_page_id = get_page_by_title($_value, OBJECT, 'seopress_bot');

                                                if ($check_page_id->post_title != $_value && get_post_meta($check_page_id->ID, 'seopress_bot_source_url', true) != $_value) {
                                                    wp_insert_post(
                                                        [
                                                            'post_title' => $_value,
                                                            'post_type' => 'seopress_bot',
                                                            'post_status' => 'publish',
                                                            'meta_input' => [
                                                                'seopress_bot_response' => json_encode($response),
                                                                'seopress_bot_type' => $bot_status_type,
                                                                'seopress_bot_status' => $bot_status_code,
                                                                'seopress_bot_source_url' => get_permalink($post),
                                                                'seopress_bot_source_id' => $post,
                                                                'seopress_bot_cpt' => get_post_type($post),
                                                                'seopress_bot_source_title' => get_the_title($post),
                                                                'seopress_bot_a_title' => $text,
                                                            ],
                                                        ]
                                                    );
                                                } elseif ($check_page_id->post_title == $_value) {
                                                    $seopress_bot_count = get_post_meta($check_page_id->ID, 'seopress_bot_count', true);
                                                    update_post_meta($check_page_id->ID, 'seopress_bot_count', ++$seopress_bot_count);
                                                }

                                                $data['link'][] = $_value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }//End foreach
                    libxml_use_internal_errors($internalErrors);
                    ++$offset;
                } else {
                    wp_reset_query();
                    //Log date
                    update_option('seopress-bot-log', current_time('Y-m-d H:i'), 'yes');

                    $offset = 'done';
                }
            }
        }
        $data['offset'] = $offset;

        //Return
        wp_send_json_success($data);
    }
}
add_action('wp_ajax_seopress_request_bot', 'seopress_request_bot');
///////////////////////////////////////////////////////////////////////////////////////////////////
//Admin Columns PRO
///////////////////////////////////////////////////////////////////////////////////////////////////
if (is_plugin_active('admin-columns-pro/admin-columns-pro.php')) {
    add_action('ac/column_groups', 'ac_register_seopress_column_group');
    function ac_register_seopress_column_group(AC\Groups $groups) {
        $groups->register_group('seopress', 'SEOPress');
    }

    add_action('ac/column_types', 'ac_register_seopress_columns');
    function ac_register_seopress_columns(AC\ListScreen $list_screen) {
        if ($list_screen instanceof ACP\ListScreen\Post) {
            require_once plugin_dir_path(__FILE__) . 'admin-columns/acp-column-sp_title.php';
            require_once plugin_dir_path(__FILE__) . 'admin-columns/acp-column-sp_desc.php';
            require_once plugin_dir_path(__FILE__) . 'admin-columns/acp-column-sp_noindex.php';
            require_once plugin_dir_path(__FILE__) . 'admin-columns/acp-column-sp_nofollow.php';
            require_once plugin_dir_path(__FILE__) . 'admin-columns/acp-column-sp_target_kw.php';

            $list_screen->register_column_type(new ACP_Column_sp_title());
            $list_screen->register_column_type(new ACP_Column_sp_desc());
            $list_screen->register_column_type(new ACP_Column_sp_noindex());
            $list_screen->register_column_type(new ACP_Column_sp_nofollow());
            $list_screen->register_column_type(new ACP_Column_sp_target_kw());
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//LB Widget order
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_pro_lb_widget() {
    check_ajax_referer('seopress_pro_lb_widget_nonce');
    if (current_user_can('edit_theme_options') && is_admin()) {
        if (isset($_POST['order']) && $_POST['order'] && isset($_POST['id']) && $_POST['id']) {
            $widget_option = get_option('widget_seopress_pro_lb_widget');

            $widget_option[(int)$_POST['id']]['order'] = $_POST['order'];

            update_option('widget_seopress_pro_lb_widget', $widget_option);
        }
    }

    wp_send_json_success();
}
add_action('wp_ajax_seopress_pro_lb_widget', 'seopress_pro_lb_widget');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Request Reverse domains
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_request_reverse() {
    check_ajax_referer('seopress_request_reverse_nonce');

    delete_transient('seopress_results_reverse');
    if (false === ($seopress_results_reverse_cache = get_transient('seopress_results_reverse'))) {
        $clean_url = str_replace(['http://', 'https://'], '', '' . get_home_url());

        $response = wp_remote_post('https://domains.yougetsignal.com/domains.php?remoteAddress=' . $clean_url);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            $response_body = __('An error occurred, please try again.', 'wp-seopress-pro');
        } else {
            $response_body = wp_remote_retrieve_body($response);
        }

        $seopress_results_reverse_cache = $response_body;
        set_transient('seopress_results_reverse', $seopress_results_reverse_cache, 365 * DAY_IN_SECONDS);
    }
    wp_send_json_success($data);
}
add_action('wp_ajax_seopress_request_reverse', 'seopress_request_reverse');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Clear Google Page Speed cache
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_clear_page_speed_cache() {
    check_ajax_referer('seopress_clear_page_speed_cache_nonce');

    global $wpdb;

    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_seopress_results_page_speed' ");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_seopress_results_page_speed' ");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_seopress_results_page_speed_desktop' ");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_seopress_results_page_speed_desktop' ");

    exit();
}
add_action('wp_ajax_seopress_clear_page_speed_cache', 'seopress_clear_page_speed_cache');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Reset License
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_request_reset_license() {
    check_ajax_referer('seopress_request_reset_license_nonce');

    if (current_user_can(seopress_capability('manage_options', 'license')) && is_admin()) {
        delete_option('seopress_pro_license_status');
        delete_option('seopress_pro_license_key');
        delete_option('seopress_pro_license_key_error');
        delete_option('seopress_pro_license_automatic_attempt');
        delete_option('seopress_pro_license_home_url');

        $data = ['url' => admin_url('admin.php?page=seopress-license')];
        wp_send_json_success($data);
    }
}
add_action('wp_ajax_seopress_request_reset_license', 'seopress_request_reset_license');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Lock Google Analytics view
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_google_analytics_lock() {
    check_ajax_referer('seopress_google_analytics_lock_nonce');

    update_option('seopress_google_analytics_lock_option_name', '1', 'yes');

    wp_send_json_success();
}
add_action('wp_ajax_seopress_google_analytics_lock', 'seopress_google_analytics_lock');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Save htaccess file
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_save_htaccess() {
    check_ajax_referer('seopress_save_htaccess_nonce');

    if (current_user_can(seopress_capability('manage_options', 'htaccess')) && is_admin()) {
        $filename = get_home_path() . '/.htaccess';

        if (isset($_POST['htaccess_content'])) {
            $current_htaccess = stripslashes($_POST['htaccess_content']);
        }

        if (is_writable($filename)) {
            if ( ! $handle = fopen($filename, 'w')) {
                _e('Impossible to open file: ', 'wp-seopress-pro') . $filename;
                exit;
            }

            if (false === fwrite($handle, $current_htaccess)) {
                _e('Impossible to write in file: ', 'wp-seopress-pro') . $filename;
                exit;
            }

            _e('htaccess successfully updated!', 'wp-seopress-pro');

            fclose($handle);
        } else {
            _e('Your htaccess is not writable.', 'wp-seopress-pro');
        }
    }
}
add_action('wp_ajax_seopress_save_htaccess', 'seopress_save_htaccess');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Inspect URL with Google Search Console API
///////////////////////////////////////////////////////////////////////////////////////////////////
add_action('wp_ajax_seopress_inspect_url', 'seopress_inspect_url');
function seopress_inspect_url() {
    check_ajax_referer('seopress_inspect_url_nonce');

    if ( ! current_user_can('edit_posts') && ! is_admin()) {
        return;
    }


    $data = [];

    //Get post id
    if (isset($_POST['post_id'])) {
        $id = $_POST['post_id'];
    }

    if (empty($id)) {
        return;
    }

    $data = seopress_pro_get_service('InspectUrlGoogle')->handle($id);

    wp_send_json_success($data);
}
