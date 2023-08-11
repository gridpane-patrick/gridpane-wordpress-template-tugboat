<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) { //this is for secuirty
    exit;
}

//do not run if user is not in back-end
if (!is_admin()) {
    return;
} //this is for performance


//if the call is from "wp-cli" don't run the code below
if (defined('WP_CLI') && WP_CLI) {
    return;
} //this is important that controlpanel can see all the plugins



//do not run if the call is ajax
if (defined('DOING_AJAX') && DOING_AJAX) {
    return;
} //we do not need to run this in javascript ajax call = This is for perforamnce

if (get_stylesheet() == 'xstore') {
    add_action('admin_menu', function () {
        add_options_page('التصميمات', 'التصميمات ', 'manage_options', 'designs', '_designs');
    });
}
function _designs()
{
    $GLOBALS['plans_designs'] = [];
    $files_plans = array_diff(scandir(WPMU_PLUGIN_DIR . '/json/plans/'), array('.', '..'));
    foreach ($files_plans as  $f_plans) {
        $_f_plans = json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/plans/' . $f_plans), true);
        foreach ($_f_plans['designs'] as  $p_value) {
            if (!in_array($_f_plans['name'], $GLOBALS['plans_designs'][$p_value])) {

                $GLOBALS['plans_designs'][$p_value][] = $_f_plans['name'];
            }
        }
    }

    $front_design = get_option('front_design', 1);
    $plan = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . '/json/plans/' . WAAS1_RESTRICTION_GROUP_ID . '.json'), true);
    if (!isset($plan['designs'])) {
        $plan['designs'] = [];
    }

    $designs = scandir(WPMU_PLUGIN_DIR . '/designs');
    $designs = array_diff($designs, ['.', '..']);

    if (WAAS1_RESTRICTION_GROUP_ID == 1) {

        $plan['designs'] = $designs;
    }


    if (isset($_GET['enable']) and $_GET['enable']) {
        if (in_array($_GET['enable'], $plan['designs'])) {
            shah_import_home($_GET['enable']);
            shah_import_customizer($_GET['enable']);
            // shah_wdigets_import_data($_GET['enable']);
            wp_redirect(home_url());
            exit;
        }
    }

?>
    <style>
        .row {
            display: inline-block;
            width: 100%;
        }

        .active {
            background: #005c9630;
        }

        .row::after {
            content: "";
            clear: both;
            display: inline-block;
        }

        [class*="column-"] {
            float: right;
            margin: 6px 4px;
            /* padding: 15px; */
        }

        /* For mobile phones: */
        [class*="column-"] {
            width: 100%;
            text-align: center;
        }

        [class*="column-"] img {
            max-width: 100%;
        }

        a.designs_btn {
            background: #135e96;
            padding: 0.5rem 1rem;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            width: fit-content;
            margin: auto;
        }

        @media only screen and (min-width: 768px) {

            /* For desktop: */
            .column-1 {
                width: 8.33%;
            }

            .column-2 {
                width: 16.66%;
            }

            .column-3 {
                width: 23%;
                padding: 5px;
                border: dotted 1px #1234;
            }

            .column-4 {
                width: 31%;
            }

            .column-5 {
                width: 41.66%;
            }

            .column-6 {
                width: 50%;
            }

            .column-7 {
                width: 58.33%;
            }

            .column-8 {
                width: 66.66%;
            }

            .column-9 {
                width: 75%;
            }

            .column-10 {
                width: 83.33%;
            }

            .column-11 {
                width: 91.66%;
            }

            .column-12 {
                width: 100%;
            }
        }
    </style>
    <h1>التصميمات</h1>
    <div class="row">
        <?php foreach ($designs as $key => $design) {
            if ($settings = file_get_contents(WPMU_PLUGIN_DIR . "/designs/$design/settings.json")) {
                $settings = json_decode($settings)
        ?>
                <div class="column-3 <?= $design == $front_design ? 'active' : null ?>">
                    <img src="<?= content_url("/mu-plugins/designs/$design/screenshot.png") ?>">
                    <?php if (in_array($design, $plan['designs'])) : ?>
                        <a href="<?= admin_url("options-general.php?page=designs&enable=$design") ?>" class="designs_btn">تثبيت</a>
                        <a href="<?= $settings->preview ?>" class="designs_btn" target="_blank" style="background: #2d8934;">معاينة</a>
                    <?php else :  ?>
                        <a href="#" class="button button-primary" style="    pointer-events: none;
                                    cursor: default;
                                    background: #ddd;
                                    color: red;
                                    padding: 0;">
                            <?php
                            if ($GLOBALS['plans_designs'][$design]) {
                                echo "<p style='width: 99%;white-space: break-spaces;margin: 0;'>متوفر في " . implode(' , ', $GLOBALS['plans_designs'][$design]) . "</p>";
                            }
                            ?></a>
                        <a href="<?= $settings->preview ?>" class="designs_btn" target="_blank" style="background: #2d8934;">معاينة</a>

                    <?php endif; ?>
                </div>
        <?php  }
        } ?>

    </div>


<?php
}

function shah_wdigets_import_data($id)
{
    $data = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . "/designs/$id/wdigets.wie"));
    if (!$data) {
        return null;
    }
    global $wp_registered_sidebars;

    $available_widgets = shah_available_widgets();

    $widget_instances = array();
    foreach ($available_widgets as $widget_data) {
        $widget_instances[$widget_data['id_base']] = get_option('widget_' . $widget_data['id_base']);
    }
    $results = array();


    foreach ($data as $sidebar_id => $widgets) {

        if ('wp_inactive_widgets' === $sidebar_id) {
            continue;
        }

        if (isset($wp_registered_sidebars[$sidebar_id])) {
            $sidebar_available    = true;
            $use_sidebar_id       = $sidebar_id;
            $sidebar_message_type = 'success';
            $sidebar_message      = '';
        } else {
            $sidebar_available    = false;
            $use_sidebar_id       = 'wp_inactive_widgets';
            $sidebar_message_type = 'error';
            $sidebar_message      = esc_html__('Widget area does not exist in theme (using Inactive)', 'widget-importer-exporter');
        }

        $results[$sidebar_id]['name']         = !empty($wp_registered_sidebars[$sidebar_id]['name']) ? $wp_registered_sidebars[$sidebar_id]['name'] : $sidebar_id;
        $results[$sidebar_id]['message_type'] = $sidebar_message_type;
        $results[$sidebar_id]['message']      = $sidebar_message;
        $results[$sidebar_id]['widgets']      = array();

        // Loop widgets.
        foreach ($widgets as $widget_instance_id => $widget) {
            $fail = false;

            // Get id_base (remove -# from end) and instance ID number.
            $id_base            = preg_replace('/-[0-9]+$/', '', $widget_instance_id);
            $instance_id_number = str_replace($id_base . '-', '', $widget_instance_id);

            // Does site support this widget?
            if (!$fail && !isset($available_widgets[$id_base])) {
                $fail                = true;
                $widget_message_type = 'error';
                $widget_message      = esc_html__('Site does not support widget', 'widget-importer-exporter'); // Explain why widget not imported.
            }

            // Filter to modify settings object before conversion to array and import
            // Leave this filter here for backwards compatibility with manipulating objects (before conversion to array below)
            // Ideally the newer wie_widget_settings_array below will be used instead of this.
            $widget = apply_filters('wie_widget_settings', $widget);

            // Convert multidimensional objects to multidimensional arrays
            // Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays
            // Without this, they are imported as objects and cause fatal error on Widgets page
            // If this creates problems for plugins that do actually intend settings in objects then may need to consider other approach: https://wordpress.org/support/topic/problem-with-array-of-arrays
            // It is probably much more likely that arrays are used than objects, however.
            $widget = json_decode(wp_json_encode($widget), true);

            // Filter to modify settings array
            // This is preferred over the older wie_widget_settings filter above
            // Do before identical check because changes may make it identical to end result (such as URL replacements).
            $widget = apply_filters('wie_widget_settings_array', $widget);

            // Does widget with identical settings already exist in same sidebar?
            if (!$fail && isset($widget_instances[$id_base])) {
                // Get existing widgets in this sidebar.
                $sidebars_widgets = get_option('sidebars_widgets');
                $sidebar_widgets  = isset($sidebars_widgets[$use_sidebar_id]) ? $sidebars_widgets[$use_sidebar_id] : array(); // Check Inactive if that's where will go.

                // Loop widgets with ID base.
                $single_widget_instances = !empty($widget_instances[$id_base]) ? $widget_instances[$id_base] : array();
                foreach ($single_widget_instances as $check_id => $check_widget) {
                    // Is widget in same sidebar and has identical settings?
                    if (in_array("$id_base-$check_id", $sidebar_widgets, true) && (array) $widget === $check_widget) {
                        $fail                = true;
                        $widget_message_type = 'warning';

                        // Explain why widget not imported.
                        $widget_message = esc_html__('Widget already exists', 'widget-importer-exporter');

                        break;
                    }
                }
            }

            // No failure.
            if (!$fail) {
                // Add widget instance
                $single_widget_instances   = get_option('widget_' . $id_base); // All instances for that widget ID base, get fresh every time.
                $single_widget_instances   = !empty($single_widget_instances) ? $single_widget_instances : array(
                    '_multiwidget' => 1,   // Start fresh if have to.
                );
                $single_widget_instances[] = $widget; // Add it.

                // Get the key it was given.
                end($single_widget_instances);
                $new_instance_id_number = key($single_widget_instances);

                // If key is 0, make it 1
                // When 0, an issue can occur where adding a widget causes data from other widget to load,
                // and the widget doesn't stick (reload wipes it).
                if ('0' === strval($new_instance_id_number)) {
                    $new_instance_id_number = 1;
                    $single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
                    unset($single_widget_instances[0]);
                }

                // Move _multiwidget to end of array for uniformity.
                if (isset($single_widget_instances['_multiwidget'])) {
                    $multiwidget = $single_widget_instances['_multiwidget'];
                    unset($single_widget_instances['_multiwidget']);
                    $single_widget_instances['_multiwidget'] = $multiwidget;
                }

                // Update option with new widget.
                update_option('widget_' . $id_base, $single_widget_instances);

                // Assign widget instance to sidebar.
                // Which sidebars have which widgets, get fresh every time.
                $sidebars_widgets = get_option('sidebars_widgets');

                // Avoid rarely fatal error when the option is an empty string
                // https://github.com/churchthemes/widget-importer-exporter/pull/11.
                if (!$sidebars_widgets) {
                    $sidebars_widgets = array();
                }

                // Use ID number from new widget instance.
                $new_instance_id = $id_base . '-' . $new_instance_id_number;

                // Add new instance to sidebar.
                $sidebars_widgets[$use_sidebar_id][] = $new_instance_id;

                // Save the amended data.
                update_option('sidebars_widgets', $sidebars_widgets);

                // After widget import action.
                $after_widget_import = array(
                    'sidebar'           => $use_sidebar_id,
                    'sidebar_old'       => $sidebar_id,
                    'widget'            => $widget,
                    'widget_type'       => $id_base,
                    'widget_id'         => $new_instance_id,
                    'widget_id_old'     => $widget_instance_id,
                    'widget_id_num'     => $new_instance_id_number,
                    'widget_id_num_old' => $instance_id_number,
                );
                do_action('wie_after_widget_import', $after_widget_import);

                // Success message.
                if ($sidebar_available) {
                    $widget_message_type = 'success';
                    $widget_message      = esc_html__('Imported', 'widget-importer-exporter');
                } else {
                    $widget_message_type = 'warning';
                    $widget_message      = esc_html__('Imported to Inactive', 'widget-importer-exporter');
                }
            }

            // Result for widget instance
            $results[$sidebar_id]['widgets'][$widget_instance_id]['name']         = isset($available_widgets[$id_base]['name']) ? $available_widgets[$id_base]['name'] : $id_base;      // Widget name or ID if name not available (not supported by site).
            $results[$sidebar_id]['widgets'][$widget_instance_id]['title']        = !empty($widget['title']) ? $widget['title'] : esc_html__('No Title', 'widget-importer-exporter');  // Show "No Title" if widget instance is untitled.
            $results[$sidebar_id]['widgets'][$widget_instance_id]['message_type'] = $widget_message_type;
            $results[$sidebar_id]['widgets'][$widget_instance_id]['message']      = $widget_message;
        }
    }

    // Hook after import.
    do_action('wie_after_import');

    // Return results.
    return apply_filters('wie_import_results', $results);
}
function shah_available_widgets()
{
    global $wp_registered_widget_controls;

    $widget_controls = $wp_registered_widget_controls;

    $available_widgets = array();

    foreach ($widget_controls as $widget) {
        // No duplicates.
        if (!empty($widget['id_base']) && !isset($available_widgets[$widget['id_base']])) {
            $available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
            $available_widgets[$widget['id_base']]['name']    = $widget['name'];
        }
    }

    return apply_filters('wie_available_widgets', $available_widgets);
}

function shah_import_customizer($id)
{
    $raw  = @file_get_contents(WPMU_PLUGIN_DIR . "/designs/$id/customizer.dat");
    if (!$raw) {
        return null;
    }
    $data = @unserialize($raw);
    if (function_exists('wp_update_custom_css_post') && isset($data['wp_css']) && '' !== $data['wp_css']) {
        wp_update_custom_css_post($data['wp_css']);
    }

    foreach ($data['mods'] as $key => $val) {
        do_action('customize_save_' . $key);

        set_theme_mod($key, $val);
    }

    if (isset($data['options'])) {

        foreach ($data['options'] as $option_key => $option_value) {
            if (is_array($option_value)) {
                $option_value = maybe_serialize($option_value);
            }
            update_option($option_key, $option_value);
        }
    }
    do_action('customize_save_after');
    do_action('customize_controls_print_styles');
}
function shah_import_home($id)
{
    $content = @json_decode(file_get_contents(WPMU_PLUGIN_DIR . "/designs/$id/home.json"), true);
    if (!$content) {
        return null;
    }
    wp_delete_post(get_option('page_on_front'), true);

    $wp_post_data = array(
        'post_type'   => 'page',
        'post_title'  => $content['title'],
        'post_content' => '',
        'post_status' => 'publish',

    );
    $post_id = wp_insert_post($wp_post_data);


    update_post_meta($post_id, '_edit_lock', time());
    update_post_meta($post_id, '_elementor_template_type', 'wp-page');
    update_post_meta($post_id, '_elementor_version', '3.6.7');
    update_post_meta($post_id, '_wp_page_template', 'elementor_header_footer');
    update_post_meta($post_id, '_elementor_edit_mode', 'builder');
    update_post_meta($post_id, '_elementor_data', maybe_serialize(json_encode($content['content'], JSON_UNESCAPED_UNICODE)));
    update_option('page_on_front', $post_id);
    update_option('front_design', $id);
    update_option('show_on_front', 'page');
    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
    }
    if (class_exists('autoptimizeCache'))
        autoptimizeCache::clearall();

    if (class_exists('W3_Plugin_TotalCacheAdmin')) {
        $plugin_totalcacheadmin = &w3_instance('W3_Plugin_TotalCacheAdmin');
        $plugin_totalcacheadmin->flush_all();
    }
    global $wp_object_cache;

    $wp_object_cache->flush();
}
