<?php
defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');
if ('1' == seopress_get_toggle_option('404')) {
    if (is_admin()) {
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Register SEOPress Redirections Custom Post Type
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        function seopress_404_fn() {
            $labels = [
                'name' => _x('Redirections', 'Post Type General Name', 'wp-seopress-pro'),
                'singular_name' => _x('Redirections', 'Post Type Singular Name', 'wp-seopress-pro'),
                'menu_name' => __('Redirections', 'wp-seopress-pro'),
                'name_admin_bar' => __('Redirections', 'wp-seopress-pro'),
                'archives' => __('Item Archives', 'wp-seopress-pro'),
                'parent_item_colon' => __('Parent Item:', 'wp-seopress-pro'),
                'all_items' => __('All redirections', 'wp-seopress-pro'),
                'add_new_item' => __('Add New redirection', 'wp-seopress-pro'),
                'add_new' => __('Add redirection', 'wp-seopress-pro'),
                'new_item' => __('New redirection', 'wp-seopress-pro'),
                'edit_item' => __('Edit redirection', 'wp-seopress-pro'),
                'update_item' => __('Update redirection', 'wp-seopress-pro'),
                'view_item' => __('View redirection', 'wp-seopress-pro'),
                'search_items' => __('Search redirection', 'wp-seopress-pro'),
                'not_found' => __('Not found', 'wp-seopress-pro'),
                'not_found_in_trash' => __('Not found in Trash', 'wp-seopress-pro'),
                'featured_image' => __('Featured Image', 'wp-seopress-pro'),
                'set_featured_image' => __('Set featured image', 'wp-seopress-pro'),
                'remove_featured_image' => __('Remove featured image', 'wp-seopress-pro'),
                'use_featured_image' => __('Use as featured image', 'wp-seopress-pro'),
                'insert_into_item' => __('Insert into item', 'wp-seopress-pro'),
                'uploaded_to_this_item' => __('Uploaded to this item', 'wp-seopress-pro'),
                'items_list' => __('Redirections list', 'wp-seopress-pro'),
                'items_list_navigation' => __('Redirections list navigation', 'wp-seopress-pro'),
                'filter_items_list' => __('Filter redirections list', 'wp-seopress-pro'),
            ];
            $args = [
                'label' => __('Redirections', 'wp-seopress-pro'),
                'description' => __('Redirections and Monitoring 404', 'wp-seopress-pro'),
                'labels' => $labels,
                'supports' => ['title'],
                'hierarchical' => false,
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => false,
                'menu_icon' => 'dashicons-admin-links',
                'show_in_admin_bar' => false,
                'show_in_nav_menus' => false,
                'can_export' => true,
                'has_archive' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'capability_type' => 'redirection',
                'capabilities' => [
                    'edit_post' => 'edit_redirection',
                    'edit_posts' => 'edit_redirections',
                    'edit_others_posts' => 'edit_others_redirections',
                    'publish_posts' => 'publish_redirections',
                    'read_post' => 'read_redirection',
                    'read_private_posts' => 'read_private_redirections',
                    'delete_post' => 'delete_redirection',
                    'delete_others_posts' => 'delete_others_redirections',
                    'delete_published_posts' => 'delete_published_redirections',
                ],
            ];
            register_post_type('seopress_404', $args);
        }
        add_action('admin_init', 'seopress_404_fn', 10);

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Map SEOPress 404 caps
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        add_filter('map_meta_cap', 'seopress_404_map_meta_cap', 10, 4);
        function seopress_404_map_meta_cap($caps, $cap, $user_id, $args) {
            /* If editing, deleting, or reading a redirection, get the post and post type object. */
            if ('edit_redirection' === $cap || 'delete_redirection' === $cap || 'read_redirection' === $cap) {
                $post = get_post($args[0]);
                $post_type = get_post_type_object($post->post_type);

                /* Set an empty array for the caps. */
                $caps = [];
            }

            /* If editing a redirection, assign the required capability. */
            if ('edit_redirection' === $cap) {
                if ($user_id == $post->post_author) {
                    $caps[] = $post_type->cap->edit_posts;
                } else {
                    $caps[] = $post_type->cap->edit_others_posts;
                }
            }

            /* If deleting a redirection, assign the required capability. */
            elseif ('delete_redirection' === $cap) {
                if ($user_id == $post->post_author) {
                    $caps[] = $post_type->cap->delete_published_posts;
                } else {
                    $caps[] = $post_type->cap->delete_others_posts;
                }
            }

            /* If reading a private redirection, assign the required capability. */
            elseif ('read_redirection' === $cap) {
                if ('private' !== $post->post_status) {
                    $caps[] = 'read';
                } elseif ($user_id == $post->post_author) {
                    $caps[] = 'read';
                } else {
                    $caps[] = $post_type->cap->read_private_posts;
                }
            }

            /* Return the capabilities required by the user. */
            return $caps;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Register SEOPress Custom Taxonomy Categories for Redirections
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        function seopress_404_cat_fn() {
            $labels = [
                'name' => _x('Categories', 'Taxonomy General Name', 'wp-seopress-pro'),
                'singular_name' => _x('Category', 'Taxonomy Singular Name', 'wp-seopress-pro'),
                'menu_name' => __('Categories', 'wp-seopress-pro'),
                'all_items' => __('All Categories', 'wp-seopress-pro'),
                'parent_item' => __('Parent Category', 'wp-seopress-pro'),
                'parent_item_colon' => __('Parent Category:', 'wp-seopress-pro'),
                'new_item_name' => __('New Category Name', 'wp-seopress-pro'),
                'add_new_item' => __('Add New Category', 'wp-seopress-pro'),
                'edit_item' => __('Edit Category', 'wp-seopress-pro'),
                'update_item' => __('Update Category', 'wp-seopress-pro'),
                'view_item' => __('View Category', 'wp-seopress-pro'),
                'separate_items_with_commas' => __('Separate categories with commas', 'wp-seopress-pro'),
                'add_or_remove_items' => __('Add or remove categories', 'wp-seopress-pro'),
                'choose_from_most_used' => __('Choose from the most used', 'wp-seopress-pro'),
                'popular_items' => __('Popular Categories', 'wp-seopress-pro'),
                'search_items' => __('Search Categories', 'wp-seopress-pro'),
                'not_found' => __('Not Found', 'wp-seopress-pro'),
                'no_terms' => __('No items', 'wp-seopress-pro'),
                'items_list' => __('Categories list', 'wp-seopress-pro'),
                'items_list_navigation' => __('Categories list navigation', 'wp-seopress-pro'),
            ];
            $args = [
                'labels' => $labels,
                'hierarchical' => true,
                'public' => false,
                'show_ui' => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => false,
                'show_tagcloud' => false,
                'rewrite' => false,
                'show_in_rest' => false,
            ];
            register_taxonomy('seopress_404_cat', ['seopress_404'], $args);
        }
        add_action('init', 'seopress_404_cat_fn', 10);

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Add custom buttons to SEOPress Redirections Custom Post Type
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        function seopress_404_btn_cpt() {
            $screen = get_current_screen();
            if ('seopress_404' == $screen->post_type) {
                ?>
<script>
    jQuery(function() {
        jQuery("body.post-type-seopress_404 .wrap h1 ~ a").after(
            '<a href="<?php echo admin_url('admin.php?page=seopress-import-export#tab=tab_seopress_tool_redirects'); ?>" id="seopress-import-redirects" class="page-title-action"><?php _e('Import / Export redirects', 'wp-seopress-pro'); ?></a>'
        );

        jQuery("body.post-type-seopress_404 .wrap h1 ~ #seopress-import-redirects").after(
            '<a href="<?php echo admin_url('admin.php?page=seopress-pro-page#tab=tab_seopress_404'); ?>" id="seopress-redirections-settings" class="page-title-action"><?php _e('Settings', 'wp-seopress-pro'); ?></a>'
        );

        jQuery("body.post-type-seopress_404 .wrap h1 ~ #seopress-redirections-settings").after(
            '<a href="<?php echo admin_url('admin.php?page=seopress-import-export#tab=tab_seopress_tool_redirects'); ?>" id="seopress-clean-404" class="page-title-action"><?php _e('Clean your 404', 'wp-seopress-pro'); ?></a>'
        );

        jQuery("body.post-type-seopress_404 .wrap h1 ~ #seopress-clean-404").after(
            '<a href="<?php echo admin_url('admin.php?page=seopress-import-export#tab=tab_seopress_tool_redirects'); ?>" id="seopress-clean-redirects" class="page-title-action"><?php _e('Clean all entries', 'wp-seopress-pro'); ?></a>'
        );
    });
</script>
<?php
            }
        }
        add_action('admin_head', 'seopress_404_btn_cpt');

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Add buttons to post type list if empty
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        add_action('manage_posts_extra_tablenav', 'seopress_404_maybe_render_blank_state');

        function seopress_404_render_blank_state() { ?>
<div class="seopress-BlankState">

    <h2 class="seopress-BlankState-message">
        <?php esc_html_e('Your redirections and 404 errors will appear here.', 'wp-seopress-pro'); ?>
    </h2>

    <div class="seopress-BlankState-buttons">

        <a class="seopress-BlankState-cta btn btnPrimary"
            href="<?php echo esc_url(admin_url('post-new.php?post_type=seopress_404')); ?>"><?php esc_html_e('Create a redirect', 'wp-seopress-pro'); ?></a>
        <a class="seopress-BlankState-cta button"
            href="<?php echo esc_url(admin_url('admin.php?page=seopress-import-export#tab=tab_seopress_tool_redirects')); ?>"><?php esc_html_e('Start Import', 'wp-seopress-pro'); ?></a>

    </div>

</div>

<?php
        }
        function seopress_404_maybe_render_blank_state($which) {
            global $post_type;

            if ('seopress_404' === $post_type && 'bottom' === $which) {
                $counts = (array) wp_count_posts($post_type);
                unset($counts['auto-draft']);
                $count = array_sum($counts);

                if (0 < $count) {
                    return;
                }

                seopress_404_render_blank_state();

                echo '<style type="text/css">#posts-filter .wp-list-table, #posts-filter .tablenav.top, .tablenav.bottom .actions, .wrap .subsubsub  { display: none; } #posts-filter .tablenav.bottom { height: auto; } </style>';
            }
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Row actions links
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        function seopress_404_row_actions($actions, $post) {
            if ('seopress_404' === get_post_type()) {
                //WPML
                add_filter('wpml_get_home_url', 'seopress_remove_wpml_home_url_filter', 20, 5);

                if ('yes' == get_post_meta(get_the_ID(), '_seopress_redirections_enabled', true)) {
                    $actions['seopress_404_test'] = "<a href='" . get_home_url() . '/' . get_the_title() . "' target='_blank'>" . __('Test redirection', 'wp-seopress-pro') . '</a>';
                }

                //WPML
                remove_filter('wpml_get_home_url', 'seopress_remove_wpml_home_url_filter', 20);
            }

            return $actions;
        }
        add_filter('post_row_actions', 'seopress_404_row_actions', 10, 2);

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Filters view
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        add_filter('views_edit-seopress_404', 'seopress_404_filter_views_cpt');
        function seopress_404_filter_views_cpt($views) {

            $current_view = '';

            if ( isset( $_GET['post_status'] ) ) {
				$current_view = sanitize_text_field( wp_unslash( $_GET['post_status'] ) );
			}

            $views = [
                'redirects' => [
                    'href' => admin_url('edit.php?post_type=seopress_404&post_status=redirects'),
                    'i18n' => __('Redirects','wp-seopress-pro')
                ],
                '404' => [
                    'href' => admin_url('edit.php?post_type=seopress_404&action=-1&m=0&redirect-cat=0&redirection-type=404&redirection-enabled&filter_action=Filter&paged=1&action2=-1&post_status=404'),
                    'i18n' => __('404 errors','wp-seopress-pro')
                ],
                'all' => [
                    'href' => admin_url('edit.php?post_type=seopress_404&post_status=all'),
                    'i18n' => __('All','wp-seopress-pro'),
                    'sub_links' => [
                        0 => [
                            'href' => admin_url('edit.php?post_status=pending&post_type=seopress_404'),
                            'i18n' => __('Pending','wp-seopress-pro')
                        ],
                        1 => [
                            'href' => admin_url('edit.php?post_status=draft&post_type=seopress_404'),
                            'i18n' => __('Draft','wp-seopress-pro')
                        ],
                        2 => [
                            'href' => admin_url('edit.php?post_status=trash&post_type=seopress_404'),
                            'i18n' => __('Trash','wp-seopress-pro')
                        ]
                    ]
                ],
                'categories' => [
                    'href' => admin_url('edit-tags.php?taxonomy=seopress_404_cat&post_type=seopress_404'),
                    'i18n' => __('Categories','wp-seopress-pro')
                ],
            ];

            echo "<ul class='subsubsub'>\n";
            $count = count($views);
            $i = 1;
            foreach ( $views as $key => $view ) {
                $class = '';
                $aria = '';
                if ($key == $current_view) {
                    $class = 'current';
                    $aria = 'aria-current="page"';
                } ?>
                <li class=<?php echo $key; ?>>
                    <a class="<?php echo $class; ?>" <?php echo $aria; ?> href="<?php echo $view['href']; ?>">
                        <?php echo $view['i18n']; ?>
                    </a>
                    <?php if (!empty($view['sub_links'])) {
                        $count_sub = count($view['sub_links']);
                        $i_sub = 1;
                        echo '(';
                        foreach($view['sub_links'] as $_key => $_value) { ?>
                            <a class="<?php echo $class; ?>" <?php echo $aria; ?> href="<?php echo $_value['href']; ?>">
                                <?php echo $_value['i18n']; ?>
                            </a>
                            <?php if ($count_sub !== $i_sub) {
                                echo ' - ';
                            }
                            $i_sub++;
                        }
                        echo ')';
                    }
                    if ($count !== $i) {
                        echo ' |';
                    } ?>
                </li>
                <?php
                $i++;
            }

            echo '</ul>';
        }

        function seopress_404_filters_cpt() {
            global $typenow;

            if ('seopress_404' == $typenow) {
                $args = [
                    'show_option_all' => __('All categories', 'wp-seopress-pro'),
                    'show_option_none' => '',
                    'option_none_value' => '-1',
                    'orderby' => 'ID',
                    'order' => 'ASC',
                    'show_count' => 1,
                    'hide_empty' => 0,
                    'child_of' => 0,
                    'exclude' => '',
                    'include' => '',
                    'echo' => 1,
                    'selected' => 0,
                    'hierarchical' => 0,
                    'name' => 'redirect-cat',
                    'id' => '',
                    'class' => 'postform',
                    'depth' => 0,
                    'tab_index' => 0,
                    'taxonomy' => 'seopress_404_cat',
                    'hide_if_empty' => true,
                    'value_field' => 'slug',
                ];
                wp_dropdown_categories($args);

                $redirections_type = ['301', '302', '307', '404', '410', '451'];
                $redirections_enabled = ['yes' => 'Enabled', 'no' => 'Disabled'];

                echo "<select name='redirection-type' id='redirection-type' class='postform'>";
                echo "<option value=''>" . __('Show All', 'wp-seopress-pro') . '</option>';
                foreach ($redirections_type as $type) {
                    echo '<option value=' . $type, isset($_GET[$type]) == $type ? ' selected="selected"' : '','>' . $type . '</option>';
                }
                echo '</select>';

                echo "<select name='redirection-enabled' id='redirection-enabled' class='postform'>";
                echo "<option value=''>" . __('All status', 'wp-seopress-pro') . '</option>';
                foreach ($redirections_enabled as $enabled => $value) {
                    echo '<option value=' . $enabled, isset($_GET[$enabled]) == $enabled ? ' selected="selected"' : '','>' . $value . '</option>';
                }
                echo '</select>';
            }
        }
        add_action('restrict_manage_posts', 'seopress_404_filters_cpt');

        function seopress_404_filters_action($query) {
            global $pagenow;
            $current_page = isset($_GET['post_type']) ? $_GET['post_type'] : '';

            if (is_admin() && 'seopress_404' == $current_page && 'edit.php' == $pagenow
            && (!isset($_GET['post_status']) && !isset($_GET['s']))) {
                wp_safe_redirect( admin_url( 'edit.php?post_type=seopress_404&post_status=redirects' ), '301' );
                exit();
            }

            if (is_admin() && 'seopress_404' == $current_page && 'edit.php' == $pagenow && (isset($_GET['post_status']) &&
                ('redirects' == $_GET['post_status']))) {
                    $query->query_vars['meta_query'] = [
                        [
                            'key' => '_seopress_redirections_type',
                            'value' => null,
                            'compare' => '!=',
                        ],
                    ];
            }

            if (is_admin() && 'seopress_404' == $current_page && 'edit.php' == $pagenow && (isset($_GET['redirect-cat']) &&
                ('0' != $_GET['redirect-cat']))) {
                $redirection_cat = $_GET['redirect-cat'];
                $query->query_vars['tax_query'] = [
                    [
                        'taxonomy' => 'seopress_404_cat',
                        'field' => 'slug',
                        'terms' => $redirection_cat,
                    ],
                ];
            }

            if (is_admin() && 'seopress_404' == $current_page && 'edit.php' == $pagenow && (isset($_GET['redirect-cat']) &&
                '' != $_GET['redirect-cat'] && isset($_GET['redirection-type']) &&
                '' != $_GET['redirection-type'] && isset($_GET['redirection-enabled']) && '' != $_GET['redirection-enabled'])) {
                $redirection_type = $_GET['redirection-type'];
                $redirection_enabled = $_GET['redirection-enabled'];

                $query->query_vars['meta_relation'] = 'AND';
                if ('no' == $_GET['redirection-enabled']) {
                    $compare = 'NOT EXISTS';
                } else {
                    $compare = '=';
                }
                $query->query_vars['meta_query'] = [
                    'relation' => 'AND',
                    [
                        'key' => '_seopress_redirections_type',
                        'value' => $redirection_type,
                        'compare' => '=',
                    ],
                    [
                        'key' => '_seopress_redirections_enabled',
                        'value' => $redirection_enabled,
                        'compare' => $compare,
                    ],
                ];
            }

            if (is_admin() && 'seopress_404' == $current_page && 'edit.php' == $pagenow && isset($_GET['redirection-type']) &&
                '' != $_GET['redirection-type']) {
                $redirection_type = $_GET['redirection-type'];
                $query->query_vars['meta_key'] = '_seopress_redirections_type';
                $query->query_vars['meta_value'] = $redirection_type;
                $query->query_vars['meta_compare'] = '=';
                if ('404' == $redirection_type) {
                    $query->query_vars['meta_compare'] = 'NOT EXISTS';
                }
            }
            if (is_admin() && 'seopress_404' == $current_page && 'edit.php' == $pagenow && isset($_GET['redirection-enabled']) &&
                '' != $_GET['redirection-enabled']) {
                $redirection_enabled = $_GET['redirection-enabled'];
                $query->query_vars['meta_key'] = '_seopress_redirections_enabled';
                $query->query_vars['meta_value'] = $redirection_enabled;
                if ('no' == $redirection_enabled) {
                    $query->query_vars['meta_compare'] = 'NOT EXISTS';
                } else {
                    $query->query_vars['meta_compare'] = '=';
                }
            }
        }
        add_filter('parse_query', 'seopress_404_filters_action');

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Bulk actions
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //enable 301
        add_filter('bulk_actions-edit-seopress_404', 'seopress_bulk_actions_enable');

        function seopress_bulk_actions_enable($bulk_actions) {
            $bulk_actions['seopress_enable'] = __('Enable redirection', 'wp-seopress-pro');

            return $bulk_actions;
        }

        add_filter('handle_bulk_actions-edit-seopress_404', 'seopress_bulk_action_enable_handler', 10, 3);

        function seopress_bulk_action_enable_handler($redirect_to, $doaction, $post_ids) {
            if ('seopress_enable' !== $doaction) {
                return $redirect_to;
            }
            foreach ($post_ids as $post_id) {
                // Perform action for each post.
                update_post_meta($post_id, '_seopress_redirections_enabled', 'yes');
            }
            $redirect_to = add_query_arg('bulk_enable_posts', count($post_ids), $redirect_to);

            return $redirect_to;
        }

        add_action('seopress_admin_notices', 'seopress_bulk_action_enable_admin_notice');

        function seopress_bulk_action_enable_admin_notice() {
            if ( ! empty($_REQUEST['bulk_enable_posts'])) {
                $enable_count = intval($_REQUEST['bulk_enable_posts']);
                printf('<div id="message" class="updated fade"><p>' .
                        _n(
                            '%s redirection enabled.',
                            '%s redirections enabled.',
                            $enable_count,
                            'wp-seopress-pro'
                        ) . '</p></div>', $enable_count);
            }
        }

        //disable 301
        add_filter('bulk_actions-edit-seopress_404', 'seopress_bulk_actions_disable');

        function seopress_bulk_actions_disable($bulk_actions) {
            $bulk_actions['seopress_disable'] = __('Disable redirection', 'wp-seopress-pro');

            return $bulk_actions;
        }

        add_filter('handle_bulk_actions-edit-seopress_404', 'seopress_bulk_action_disable_handler', 10, 3);

        function seopress_bulk_action_disable_handler($redirect_to, $doaction, $post_ids) {
            if ('seopress_disable' !== $doaction) {
                return $redirect_to;
            }
            foreach ($post_ids as $post_id) {
                // Perform action for each post.
                update_post_meta($post_id, '_seopress_redirections_enabled', '');
            }
            $redirect_to = add_query_arg('bulk_disable_posts', count($post_ids), $redirect_to);

            return $redirect_to;
        }

        add_action('seopress_admin_notices', 'seopress_bulk_action_disable_admin_notice');

        function seopress_bulk_action_disable_admin_notice() {
            if ( ! empty($_REQUEST['bulk_disable_posts'])) {
                $disable_count = intval($_REQUEST['bulk_disable_posts']);
                printf('<div id="message" class="updated fade"><p>' .
                        _n(
                            '%s redirection disabled.',
                            '%s redirections disabled.',
                            $disable_count,
                            'wp-seopress-pro'
                        ) . '</p></div>', $disable_count);
            }
        }

        //enable regex
        add_filter('bulk_actions-edit-seopress_404', 'seopress_bulk_actions_enable_regex');

        function seopress_bulk_actions_enable_regex($bulk_actions) {
            $bulk_actions['seopress_enable_regex'] = __('Enable regex', 'wp-seopress-pro');

            return $bulk_actions;
        }

        add_filter('handle_bulk_actions-edit-seopress_404', 'seopress_bulk_action_enable_regex_handler', 10, 3);

        function seopress_bulk_action_enable_regex_handler($redirect_to, $doaction, $post_ids) {
            if ('seopress_enable_regex' !== $doaction) {
                return $redirect_to;
            }
            foreach ($post_ids as $post_id) {
                // Perform action for each post.
                update_post_meta($post_id, '_seopress_redirections_enabled_regex', 'yes');
            }
            $redirect_to = add_query_arg('bulk_enable_regex_posts', count($post_ids), $redirect_to);

            return $redirect_to;
        }

        add_action('seopress_admin_notices', 'seopress_bulk_action_enable_regex_admin_notice');

        function seopress_bulk_action_enable_regex_admin_notice() {
            if ( ! empty($_REQUEST['bulk_enable_regex_posts'])) {
                $enable_regex_count = intval($_REQUEST['bulk_enable_regex_posts']);
                printf('<div id="message" class="updated fade"><p>' .
                        _n(
                            '%s redirection with regex enabled.',
                            '%s redirections with regex enabled.',
                            $enable_regex_count,
                            'wp-seopress-pro'
                        ) . '</p></div>', $enable_regex_count);
            }
        }

        //disable regex
        add_filter('bulk_actions-edit-seopress_404', 'seopress_bulk_actions_disable_regex');

        function seopress_bulk_actions_disable_regex($bulk_actions) {
            $bulk_actions['seopress_disable_regex'] = __('Disable regex', 'wp-seopress-pro');

            return $bulk_actions;
        }

        add_filter('handle_bulk_actions-edit-seopress_404', 'seopress_bulk_action_disable_regex_handler', 10, 3);

        function seopress_bulk_action_disable_regex_handler($redirect_to, $doaction, $post_ids) {
            if ('seopress_disable_regex' !== $doaction) {
                return $redirect_to;
            }
            foreach ($post_ids as $post_id) {
                // Perform action for each post.
                update_post_meta($post_id, '_seopress_redirections_enabled_regex', '');
            }
            $redirect_to = add_query_arg('bulk_disable_regex_posts', count($post_ids), $redirect_to);

            return $redirect_to;
        }

        add_action('seopress_admin_notices', 'seopress_bulk_action_disable_regex_admin_notice');

        function seopress_bulk_action_disable_regex_admin_notice() {
            if ( ! empty($_REQUEST['bulk_disable_regex_posts'])) {
                $disable_count = intval($_REQUEST['bulk_disable_regex_posts']);
                printf('<div id="message" class="updated fade"><p>' .
                        _n(
                            '%s redirection with regex disabled.',
                            '%s redirections with regex disabled.',
                            $disable_count,
                            'wp-seopress-pro'
                        ) . '</p></div>', $disable_count);
            }
        }

        //Set as 301
        add_filter('bulk_actions-edit-seopress_404', 'seopress_bulk_actions_redirect_301');

        function seopress_bulk_actions_redirect_301($bulk_actions) {
            $bulk_actions['seopress_redirect_301'] = __('Mark as 301', 'wp-seopress-pro');

            return $bulk_actions;
        }
        add_filter('handle_bulk_actions-edit-seopress_404', 'seopress_bulk_action_redirect_301_handler', 10, 3);

        function seopress_bulk_action_redirect_301_handler($redirect_to, $doaction, $post_ids) {
            if ('seopress_redirect_301' !== $doaction) {
                return $redirect_to;
            }
            foreach ($post_ids as $post_id) {
                // Perform action for each post.
                update_post_meta($post_id, '_seopress_redirections_type', '301');
            }
            $redirect_to = add_query_arg('bulk_301_redirects_posts', count($post_ids), $redirect_to);

            return $redirect_to;
        }

        add_action('seopress_admin_notices', 'seopress_bulk_action_redirect_301_admin_notice');

        function seopress_bulk_action_redirect_301_admin_notice() {
            if ( ! empty($_REQUEST['bulk_301_redirects_posts'])) {
                $count_301 = intval($_REQUEST['bulk_301_redirects_posts']);
                printf('<div id="message" class="updated fade"><p>' .
                _n(
                    '%s marked as 301 redirect.',
                    '%s marked as 301 redirect.',
                    $count_301,
                    'wp-seopress-pro'
                ) . '</p></div>', $count_301);
            }
        }

        //Set as 302
        add_filter('bulk_actions-edit-seopress_404', 'seopress_bulk_actions_redirect_302');

        function seopress_bulk_actions_redirect_302($bulk_actions) {
            $bulk_actions['seopress_redirect_302'] = __('Mark as 302', 'wp-seopress-pro');

            return $bulk_actions;
        }
        add_filter('handle_bulk_actions-edit-seopress_404', 'seopress_bulk_action_redirect_302_handler', 10, 3);

        function seopress_bulk_action_redirect_302_handler($redirect_to, $doaction, $post_ids) {
            if ('seopress_redirect_302' !== $doaction) {
                return $redirect_to;
            }
            foreach ($post_ids as $post_id) {
                // Perform action for each post.
                update_post_meta($post_id, '_seopress_redirections_type', '302');
            }
            $redirect_to = add_query_arg('bulk_302_redirects_posts', count($post_ids), $redirect_to);

            return $redirect_to;
        }

        add_action('seopress_admin_notices', 'seopress_bulk_action_redirect_302_admin_notice');

        function seopress_bulk_action_redirect_302_admin_notice() {
            if ( ! empty($_REQUEST['bulk_302_redirects_posts'])) {
                $count_302 = intval($_REQUEST['bulk_302_redirects_posts']);
                printf('<div id="message" class="updated fade"><p>' .
                _n(
                    '%s marked as 302 redirect.',
                    '%s marked as 302 redirect.',
                    $count_302,
                    'wp-seopress-pro'
                ) . '</p></div>', $count_302);
            }
        }

        //Set as 307
        add_filter('bulk_actions-edit-seopress_404', 'seopress_bulk_actions_redirect_307');

        function seopress_bulk_actions_redirect_307($bulk_actions) {
            $bulk_actions['seopress_redirect_307'] = __('Mark as 307', 'wp-seopress-pro');

            return $bulk_actions;
        }
        add_filter('handle_bulk_actions-edit-seopress_404', 'seopress_bulk_action_redirect_307_handler', 10, 3);

        function seopress_bulk_action_redirect_307_handler($redirect_to, $doaction, $post_ids) {
            if ('seopress_redirect_307' !== $doaction) {
                return $redirect_to;
            }
            foreach ($post_ids as $post_id) {
                // Perform action for each post.
                update_post_meta($post_id, '_seopress_redirections_type', '307');
            }
            $redirect_to = add_query_arg('bulk_307_redirects_posts', count($post_ids), $redirect_to);

            return $redirect_to;
        }

        add_action('seopress_admin_notices', 'seopress_bulk_action_redirect_307_admin_notice');

        function seopress_bulk_action_redirect_307_admin_notice() {
            if ( ! empty($_REQUEST['bulk_307_redirects_posts'])) {
                $count_307 = intval($_REQUEST['bulk_307_redirects_posts']);
                printf('<div id="message" class="updated fade"><p>' .
                _n(
                    '%s marked as 307 redirect.',
                    '%s marked as 307 redirect.',
                    $count_307,
                    'wp-seopress-pro'
                ) . '</p></div>', $count_307);
            }
        }

        //Set as 410
        add_filter('bulk_actions-edit-seopress_404', 'seopress_bulk_actions_redirect_410');

        function seopress_bulk_actions_redirect_410($bulk_actions) {
            $bulk_actions['seopress_redirect_410'] = __('Mark as 410', 'wp-seopress-pro');

            return $bulk_actions;
        }
        add_filter('handle_bulk_actions-edit-seopress_404', 'seopress_bulk_action_redirect_410_handler', 10, 3);

        function seopress_bulk_action_redirect_410_handler($redirect_to, $doaction, $post_ids) {
            if ('seopress_redirect_410' !== $doaction) {
                return $redirect_to;
            }
            foreach ($post_ids as $post_id) {
                // Perform action for each post.
                update_post_meta($post_id, '_seopress_redirections_type', '410');
            }
            $redirect_to = add_query_arg('bulk_410_redirects_posts', count($post_ids), $redirect_to);

            return $redirect_to;
        }

        add_action('seopress_admin_notices', 'seopress_bulk_action_redirect_410_admin_notice');

        function seopress_bulk_action_redirect_410_admin_notice() {
            if ( ! empty($_REQUEST['bulk_410_redirects_posts'])) {
                $count_410 = intval($_REQUEST['bulk_410_redirects_posts']);
                printf('<div id="message" class="updated fade"><p>' .
                _n(
                    '%s marked as 410 redirect.',
                    '%s marked as 410 redirect.',
                    $count_410,
                    'wp-seopress-pro'
                ) . '</p></div>', $count_410);
            }
        }

        //Set as 451
        add_filter('bulk_actions-edit-seopress_404', 'seopress_bulk_actions_redirect_451');

        function seopress_bulk_actions_redirect_451($bulk_actions) {
            $bulk_actions['seopress_redirect_451'] = __('Mark as 451', 'wp-seopress-pro');

            return $bulk_actions;
        }
        add_filter('handle_bulk_actions-edit-seopress_404', 'seopress_bulk_action_redirect_451_handler', 10, 3);

        function seopress_bulk_action_redirect_451_handler($redirect_to, $doaction, $post_ids) {
            if ('seopress_redirect_451' !== $doaction) {
                return $redirect_to;
            }
            foreach ($post_ids as $post_id) {
                // Perform action for each post.
                update_post_meta($post_id, '_seopress_redirections_type', '451');
            }
            $redirect_to = add_query_arg('bulk_451_redirects_posts', count($post_ids), $redirect_to);

            return $redirect_to;
        }

        add_action('seopress_admin_notices', 'seopress_bulk_action_redirect_451_admin_notice');

        function seopress_bulk_action_redirect_451_admin_notice() {
            if ( ! empty($_REQUEST['bulk_451_redirects_posts'])) {
                $count_451 = intval($_REQUEST['bulk_451_redirects_posts']);
                printf('<div id="message" class="updated fade"><p>' .
                _n(
                    '%s marked as 451 redirect.',
                    '%s marked as 451 redirect.',
                    $count_451,
                    'wp-seopress-pro'
                ) . '</p></div>', $count_451);
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Set title placeholder for Redirections Custom Post Type
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        function seopress_404_cpt_title($title) {
            if (function_exists('get_current_screen')) {
                $screen = get_current_screen();
                if ('seopress_404' == $screen->post_type) {
                    $title = __('Enter the old URL here without domain name', 'wp-seopress-pro');
                }

                return $title;
            }
        }

        add_filter('enter_title_here', 'seopress_404_cpt_title');

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Display help after title
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        add_action('edit_form_after_title', 'seopress_301_after_title');
        function seopress_301_after_title() {
            global $typenow;
            if (isset($typenow) && 'seopress_404' == $typenow) {
                echo '<p>' . __('Enter your <strong>relative</strong> URL above. Do not use anchors, they are not sent by your browser.', 'wp-seopress-pro') . '<br>';
                _e('Eg: <strong>"my-custom-permalink"</strong>. If you have a permalink structure like <strong>/%category%/%postname%/</strong>, make sure to include the categories: <strong>"category/sub-category/my-custom-permalink".</strong>', 'wp-seopress-pro');
                echo '</p>';
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Set messages for Redirections Custom Post Type
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        function seopress_404_set_messages($messages) {
            global $post, $post_ID, $typenow;
            $post_type = 'seopress_404';
            $seopress_404_test = '';

            if ('seopress_404' === $typenow) {
                $obj = get_post_type_object($post_type);
                $singular = $obj->labels->singular_name;

                //WPML
                add_filter('wpml_get_home_url', 'seopress_remove_wpml_home_url_filter', 20, 5);

                if ('yes' == get_post_meta(get_the_ID(), '_seopress_redirections_enabled', true)) {
                    $seopress_404_test = "<a href='" . get_home_url() . '/' . get_the_title() . "' target='_blank'>" . __('Test redirection', 'wp-seopress-pro') . "</a><span class='dashicons dashicons-external'></span>";
                }

                $messages[$post_type] = [
                    0 => '', // Unused. Messages start at index 1.
                    1 => sprintf(__($singular . ' updated. %s'), $seopress_404_test),
                    2 => __('Custom field updated.'),
                    3 => __('Custom field deleted.'),
                    4 => sprintf(__($singular . ' updated. %s'), $seopress_404_test),
                    5 => isset($_GET['revision']) ? sprintf(__($singular . ' restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
                    6 => sprintf(__($singular . ' published. %s'), $seopress_404_test),
                    7 => __('Redirection saved.'),
                    8 => sprintf(__($singular . ' submitted.'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
                    9 => sprintf(__($singular . ' scheduled for: <strong>%1$s</strong>. '), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
                    10 => sprintf(__($singular . ' draft updated.'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
                ];

                return $messages;
            } else {
                return $messages;
            }
        }

        add_filter('post_updated_messages', 'seopress_404_set_messages');

        function seopress_404_set_messages_list($bulk_messages, $bulk_counts) {
            $bulk_messages['seopress_404'] = [
                'updated' => _n('%s redirection updated.', '%s redirections updated.', $bulk_counts['updated']),
                'locked' => _n('%s redirection not updated, somebody is editing it.', '%s redirections not updated, somebody is editing them.', $bulk_counts['locked']),
                'deleted' => _n('%s redirection permanently deleted.', '%s redirections permanently deleted.', $bulk_counts['deleted']),
                'trashed' => _n('%s redirection moved to the Trash.', '%s redirections moved to the Trash.', $bulk_counts['trashed']),
                'untrashed' => _n('%s redirection restored from the Trash.', '%s redirections restored from the Trash.', $bulk_counts['untrashed']),
            ];

            return $bulk_messages;
        }
        add_filter('bulk_post_updated_messages', 'seopress_404_set_messages_list', 10, 2);

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Items per page
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        add_filter('edit_seopress_404_per_page', 'seopress_404_items_per_page' );
        function seopress_404_items_per_page($per_page) {
            $per_page = 100;
            return $per_page;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Columns for SEOPress Redirections Custom Post Type
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        add_filter('manage_edit-seopress_404_columns', 'seopress_404_count_columns');
        add_action('manage_seopress_404_posts_custom_column', 'seopress_404_count_display_column', 10, 2);

        function seopress_404_count_columns($columns) {
            $columns['seopress_404'] = __('Count', 'wp-seopress-pro');
            $columns['seopress_404_redirect_enable'] = __('Enable?', 'wp-seopress-pro');
            $columns['seopress_404_redirect_regex_enable'] = __('Regex?', 'wp-seopress-pro');
            $columns['seopress_404_redirect_type'] = __('Type', 'wp-seopress-pro');
            $columns['seopress_404_redirect_value'] = __('URL redirect', 'wp-seopress-pro');
            $columns['seopress_404_redirect_date_request'] = __('Last time loaded', 'wp-seopress-pro');
            $columns['seopress_404_redirect_ua'] = __('User agent', 'wp-seopress-pro');
            $columns['seopress_404_redirect_referer'] = __('Referer', 'wp-seopress-pro');
            $columns['seopress_404_redirect_ip'] = __('IP address', 'wp-seopress-pro');

            return $columns;
        }

        function seopress_404_count_display_column($column, $post_id) {
            if ('seopress_404' == $column) {
                echo get_post_meta($post_id, 'seopress_404_count', true);
            }
            if ('seopress_404_redirect_enable' == $column) {
                if ('yes' == get_post_meta($post_id, '_seopress_redirections_enabled', true)) {
                    echo '<span class="dashicons dashicons-yes"></span>';
                }
            }
            if ('seopress_404_redirect_regex_enable' == $column) {
                if ('yes' == get_post_meta($post_id, '_seopress_redirections_enabled_regex', true)) {
                    echo '<span class="dashicons dashicons-yes"></span>';
                }
            }
            if ('seopress_404_redirect_type' == $column) {
                $seopress_redirections_type = get_post_meta($post_id, '_seopress_redirections_type', true);
                switch ($seopress_redirections_type) {
                case '307':
                    echo '<span class="seopress_redirection_307 seopress_redirection_status">' . $seopress_redirections_type . '</span>';
                    break;

                case '302':
                    echo '<span class="seopress_redirection_302 seopress_redirection_status">' . $seopress_redirections_type . '</span>';
                    break;

                case '301':
                    echo '<span class="seopress_redirection_301 seopress_redirection_status">' . $seopress_redirections_type . '</span>';
                    break;

                case '410':
                    echo '<span class="seopress_redirection_410 seopress_redirection_status">' . $seopress_redirections_type . '</span>';
                    break;

                case '451':
                    echo '<span class="seopress_redirection_451 seopress_redirection_status">' . $seopress_redirections_type . '</span>';
                    break;

                default:
                    echo '<span class="seopress_redirection_default seopress_redirection_status">' . __('404', 'wp-seopress-pro') . '</span>';
                    break;
                }
            }
            if ('seopress_404_redirect_value' == $column) {
                echo get_post_meta($post_id, '_seopress_redirections_value', true);
            }
            if ('seopress_404_redirect_date_request' == $column) {
                global $wp_version;
                $timestamp = esc_html(get_post_meta($post_id, '_seopress_404_redirect_date_request', true));
                if ('' != $timestamp) {
                    if (version_compare($wp_version, '5.3') < 0) {
                        echo date('c', $timestamp);
                    } else {
                        echo wp_date(DATE_RFC3339, $timestamp);
                    }
                }
            }
            if ('seopress_404_redirect_ua' == $column) {
                echo esc_html(get_post_meta($post_id, 'seopress_redirections_ua', true));
            }
            if ('seopress_404_redirect_referer' == $column) {
                echo '<a target="_blank" href="' . esc_html(get_post_meta($post_id, 'seopress_redirections_referer', true)) . '">' . esc_html(get_post_meta($post_id, 'seopress_redirections_referer', true)) . '</a>';
            }
            if ('seopress_404_redirect_ip' == $column) {
                echo esc_html(get_post_meta($post_id, '_seopress_redirections_ip', true));
            }
        }

        //Sortable columns
        add_filter('manage_edit-seopress_404_sortable_columns', 'seopress_404_sortable_columns');

        function seopress_404_sortable_columns($columns) {
            $columns['seopress_404'] = 'seopress_404';
            $columns['seopress_404_redirect_enable'] = 'seopress_404_redirect_enable';
            $columns['seopress_404_redirect_regex_enable'] = 'seopress_404_redirect_regex_enable';
            $columns['seopress_404_redirect_type'] = 'seopress_404_redirect_type';

            return $columns;
        }

        add_filter('pre_get_posts', 'seopress_404_sort_columns_by');

        function seopress_404_sort_columns_by($query) {
            if ( ! is_admin()) {
                return $query;
            }

            global $typenow;
            if ('seopress_404' !== $typenow) {
                return $query;
            }

            if ( isset( $_GET['post_status'] ) ) {
				$current_view = sanitize_text_field( wp_unslash( $_GET['post_status'] ) );
			}

            $orderby = $query->get('orderby');

            //Count
            if ('seopress_404' == $orderby) {
                $query->set('meta_query', [
                    [
                        'key' => 'seopress_404_count',
                        'compare' => 'EXISTS',
                    ],
                ]);
                if ('redirects' == $_GET['post_status']) {
                    $query->set('meta_query', [
                        'relation' => 'AND',
                        [
                            'key' => 'seopress_404_count',
                            'compare' => 'EXISTS',
                        ],
                        [
                            'key' => '_seopress_redirections_type',
                            'compare' => 'EXISTS',
                        ],
                    ]);
                }
                $query->set('orderby', 'meta_value_num');
            }
            //Enabled?
            if ('seopress_404_redirect_enable' == $orderby) {
                $query->set('meta_query', [
                    'relation' => 'OR',
                    [
                        'key' => '_seopress_redirections_enabled',
                        'compare' => 'EXISTS',
                    ],
                    [
                        'key' => '_seopress_redirections_enabled',
                        'compare' => 'NOT EXISTS',
                    ],
                ]);
                $query->set('orderby', '_seopress_redirections_enabled');
            }
            //Regex?
            if ('seopress_404_redirect_regex_enable' == $orderby) {
                $query->set('meta_query', [
                    'relation' => 'OR',
                    [
                        'key' => '_seopress_redirections_enabled_regex',
                        'compare' => 'EXISTS',
                    ],
                    [
                        'key' => '_seopress_redirections_enabled_regex',
                        'compare' => 'NOT EXISTS',
                    ],
                ]);
                $query->set('orderby', '_seopress_redirections_enabled_regex');
            }
            //Type
            if ('seopress_404_redirect_type' == $orderby) {
                $query->set('orderby', 'meta_value');
                $query->set('meta_query', [
                    'relation' => 'OR',
                    [
                        'key' => '_seopress_redirections_type',
                        'compare' => 'EXISTS',
                    ],
                    [
                        'key' => '_seopress_redirections_type',
                        'compare' => 'NOT EXISTS',
                    ],
                ]);

                if ('redirects' == $_GET['post_status']) {
                    $query->set('meta_query', [
                        'relation' => 'AND',
                        [
                            'key' => '_seopress_redirections_type',
                            'compare' => 'EXISTS',
                        ],
                    ]);
                }
            }

            return $query;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Quick Edit
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        add_action('quick_edit_custom_box', 'seopress_bulk_quick_edit_301_custom_box', 10, 2);
        function seopress_bulk_quick_edit_301_custom_box($column_name) {
            static $printNonce = true;
            if ($printNonce) {
                $printNonce = false;
                wp_nonce_field(plugin_basename(__FILE__), 'seopress_301_edit_nonce');
            } ?>
<div class="wp-clearfix"></div>
<fieldset class="inline-edit-col-left">
    <div class="inline-edit-col column-<?php echo $column_name; ?>">

        <?php
                        switch ($column_name) {
                        case 'seopress_404_redirect_value':
                        ?>
        <label class="inline-edit-group">
            <span class="title"><?php _e('New URL', 'wp-seopress-pro'); ?></span>
            <span class="input-text-wrap">
                <input type="text" name="seopress_redirections_value" />
            </span>
        </label>
        <?php
                        break;
                        case 'seopress_404_redirect_type':
                        ?>
        <label class="alignleft">
            <span class="title"><?php _e('Redirection type', 'wp-seopress-pro'); ?></span>
            <select name="seopress_redirections_type">
                <option value="301"><?php _e('301 Moved Permanently', 'wp-seopress-pro'); ?>
                </option>
                <option value="302"><?php _e('302 Found / Moved Temporarily', 'wp-seopress-pro'); ?>
                </option>
                <option value="307"><?php _e('307 Moved Temporarily', 'wp-seopress-pro'); ?>
                </option>
                <option value="410"><?php _e('410 Gone', 'wp-seopress-pro'); ?>
                </option>
                <option value="451"><?php _e('451 Unavailable For Legal Reasons', 'wp-seopress-pro'); ?>
                </option>
            </select>
        </label>
        <?php
                        break;
                        case 'seopress_404_redirect_enable':
                        ?>
        <h4><?php _e('Redirection settings', 'wp-seopress-pro'); ?>
        </h4>
        <label class="alignleft">
            <input type="checkbox" name="seopress_redirections_enabled" value="yes">
            <span class="checkbox-title"><?php _e('Enable redirection?', 'wp-seopress-pro'); ?></span>
        </label>
        <?php
                        break;
                        case 'seopress_404_redirect_regex_enable':
                        ?>
        <label class="alignleft">
            <input type="checkbox" name="seopress_redirections_enabled_regex" value="yes">
            <span class="checkbox-title"><?php _e('Regex?', 'wp-seopress-pro'); ?></span>
        </label>
        <?php
                        break;
                        default:
                        break;
                        } ?>
    </div>
</fieldset>
<?php
        }

        add_action('save_post', 'seopress_bulk_quick_edit_301_save_post', 10, 2);
        function seopress_bulk_quick_edit_301_save_post($post_id) {
            // don't save if Elementor library
            if (isset($_REQUEST['post_type']) && 'elementor_library' == $_REQUEST['post_type']) {
                return $post_id;
            }

            // don't save for autosave
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            // dont save for revisions
            if (isset($_REQUEST['post_type']) && 'revision' == $_REQUEST['post_type']) {
                return $post_id;
            }

            if ( ! current_user_can('edit_redirections', $post_id)) {
                return;
            }

            $_REQUEST += ['seopress_301_edit_nonce' => ''];

            if ( ! wp_verify_nonce($_REQUEST['seopress_301_edit_nonce'], plugin_basename(__FILE__))) {
                return;
            }
            if (isset($_REQUEST['seopress_redirections_value'])) {
                update_post_meta($post_id, '_seopress_redirections_value', esc_html($_REQUEST['seopress_redirections_value']));
            }
            if (isset($_REQUEST['seopress_redirections_type'])) {
                update_post_meta($post_id, '_seopress_redirections_type', esc_html($_REQUEST['seopress_redirections_type']));
            }
            if (isset($_REQUEST['seopress_redirections_enabled'])) {
                update_post_meta($post_id, '_seopress_redirections_enabled', 'yes');
            } else {
                delete_post_meta($post_id, '_seopress_redirections_enabled', '');
            }
            if (isset($_REQUEST['seopress_redirections_enabled_regex'])) {
                update_post_meta($post_id, '_seopress_redirections_enabled_regex', 'yes');
            } else {
                delete_post_meta($post_id, '_seopress_redirections_enabled_regex', '');
            }
        }

        add_filter('wp_insert_post_data', 'seopress_filter_post_title', '99', 2);
        function seopress_filter_post_title($data, $postarr) {
            if (isset($data['post_type']) && 'seopress_404' === $data['post_type'] && isset($postarr['ID'])) {
                if ('' != get_post_meta($postarr['ID'], '_seopress_redirections_type', true)) {
                    $title = $data['post_title'];

                    if ($title) {
                        $url = wp_parse_url($title);

                        if (isset($url['path']) && ! empty($url['path'])) {
                            $title = $url['path'];
                            if (isset($url['query']) && ! empty($url['query'])) {
                                $title .= '?' . $url['query'];
                            }
                            $data['post_title'] = ltrim($title, '/');
                        }
                    }
                }
            }

            return $data;
        }
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    //Get page by title
    // @since 3.8.7
    // @author Benjamin
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @deprecated 5.3.0
     * @param mixed $page_title
     * @param mixed $output
     * @param mixed $post_type
     */
    function seopress_get_page_by_title($page_title, $output = OBJECT, $post_type = 'seopress_404') {
        return seopress_pro_get_service('Redirection')->getPageByTitle($page_title);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    //Do redirect
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    function seopress_301_do_redirect() {
        if (is_admin()) {
            return;
        }

        global $wp;
        global $post;

        $home_url = home_url($wp->request);

        //WPML
        if (defined('ICL_SITEPRESS_VERSION')) {
            $home_url = untrailingslashit(home_url($wp->request));
        }

        if ( ! isset($_SERVER['QUERY_STRING'])) {
            $_SERVER['QUERY_STRING'] = '';
        }

        $get_init_current_url = htmlspecialchars(rawurldecode(add_query_arg($_SERVER['QUERY_STRING'], '', $home_url)));
        $get_current_url = wp_parse_url($get_init_current_url);

        if (defined('ICL_SITEPRESS_VERSION')) {
            add_filter('wpml_get_home_url', 'seopress_remove_wpml_home_url_filter', 20, 5);
            $home_url2 = home_url($wp->request);
            $get_init_current_url2 = htmlspecialchars(rawurldecode(add_query_arg($_SERVER['QUERY_STRING'], '', $home_url2)));
            $get_current_url2 = wp_parse_url($get_init_current_url2);
            remove_filter('wpml_get_home_url', 'seopress_remove_wpml_home_url_filter', 20);
        }

        $uri = '';
        $uri2 = '';
        $uri3 = '';
        $seopress_get_page = '';
        $if_exact_match = true;

        //Path and Query
        if (isset($get_current_url['path']) && ! empty($get_current_url['path']) && isset($get_current_url['query']) && ! empty($get_current_url['query'])) {
            $uri = trailingslashit($get_current_url['path']) . '?' . $get_current_url['query'];
            $uri2 = $get_current_url['path'] . '?' . $get_current_url['query'];

            $uri = ltrim($uri, '/');
            $uri2 = ltrim($uri2, '/');

            if (defined('ICL_SITEPRESS_VERSION')) {
                if (isset($get_current_url2['path']) && ! empty($get_current_url2['path']) && isset($get_current_url2['query']) && ! empty($get_current_url2['query'])) {
                    $uri3 = $get_current_url2['path'] . '?' . $get_current_url2['query'];
                    $uri3 = ltrim($uri3, '/');
                }
            }
        }
        //Path only
        elseif (isset($get_current_url['path']) && ! empty($get_current_url['path']) && ! isset($get_current_url['query'])) {
            $uri = $get_current_url['path'];
            $uri = ltrim($uri, '/');

            if (defined('ICL_SITEPRESS_VERSION')) {
                if (isset($get_current_url2['path']) && ! empty($get_current_url2['path']) && ! isset($get_current_url2['query'])) {
                    $uri3 = $get_current_url2['path'];
                    $uri3 = ltrim($uri3, '/');
                }
            }
        }

        //Query only
        elseif (isset($get_current_url['query']) && ! empty($get_current_url['query']) && ! isset($get_current_url['path'])) {
            $uri = '?' . $get_current_url['query'];
            $uri = ltrim($uri, '/');

            if (defined('ICL_SITEPRESS_VERSION')) {
                if (isset($get_current_url2['query']) && ! empty($get_current_url2['query']) && ! isset($get_current_url2['path'])) {
                    $uri3 = '?' . $get_current_url2['query'];
                    $uri3 = ltrim($uri3, '/');
                }
            }
        }
        //default - home
        else {
            $uri = $get_current_url['host'];
        }

        //Necessary to allowed "&" in query
        $uri = htmlspecialchars_decode($uri);
        $uri2 = htmlspecialchars_decode($uri2);
        $uri3 = htmlspecialchars_decode($uri3);

        $page_uri = seopress_get_page_by_title(trailingslashit($uri), '', 'seopress_404');
        $page_uri2 = seopress_get_page_by_title($uri2, '', 'seopress_404');

        if (defined('ICL_SITEPRESS_VERSION')) {
            $page_uri4 = seopress_get_page_by_title($uri3, '', 'seopress_404');
        }

        $page_uri3 = seopress_get_page_by_title($uri, '', 'seopress_404');

        //Find URL in Redirections post type --- EXACT MATCH
        /**With trailing slash**/
        if (isset($uri) && '' != $uri && $page_uri) {
            $seopress_get_page = $page_uri;
        }
        /**Without trailing slash**/
        elseif (isset($uri2) && '' != $uri2 && $page_uri2) {
            $seopress_get_page = $page_uri2;
        }
        /**Without language prefix**/
        elseif (defined('ICL_SITEPRESS_VERSION') && isset($uri3) && '' != $uri3 && $page_uri4) {
            $seopress_get_page = $page_uri4;
        }
        /**Default**/
        else {
            $seopress_get_page = $page_uri3;
        }

        //Find URL in Redirections post type --- IGNORE ALL PARAMETERS
        if (empty($seopress_get_page)) {
            $if_exact_match = false;

            $uri = wp_parse_url($uri, PHP_URL_PATH);
            $uri2 = wp_parse_url($uri2, PHP_URL_PATH);
            $uri3 = wp_parse_url($uri3, PHP_URL_PATH);

            $uri = ltrim($uri, '/');
            $uri2 = ltrim($uri2, '/');
            $uri3 = ltrim($uri3, '/');

            $page_uri = seopress_get_page_by_title(trailingslashit($uri), '', 'seopress_404');
            $page_uri2 = seopress_get_page_by_title($uri2, '', 'seopress_404');

            if (defined('ICL_SITEPRESS_VERSION')) {
                $page_uri4 = seopress_get_page_by_title($uri3, '', 'seopress_404');
            }

            $page_uri3 = seopress_get_page_by_title($uri, '', 'seopress_404');

            $page_uri = seopress_get_page_by_title(trailingslashit($uri), '', 'seopress_404');
            $page_uri2 = seopress_get_page_by_title($uri2, '', 'seopress_404');
            $page_uri3 = seopress_get_page_by_title($uri, '', 'seopress_404');

            /**With trailing slash**/
            if (isset($uri) && '' != $uri && $page_uri) {
                $seopress_get_page = $page_uri;
            }
            /**Without trailing slash**/
            elseif (isset($uri2) && '' != $uri2 && $page_uri2) {
                $seopress_get_page = $page_uri2;
            }
            /**Without language prefix**/
            elseif (defined('ICL_SITEPRESS_VERSION') && isset($uri3) && '' != $uri3 && $page_uri4) {
                $seopress_get_page = $page_uri4;
            }
            /**Default**/
            else {
                $seopress_get_page = $page_uri3;
            }
        }

        do_action('seopress_before_redirect', $seopress_get_page);

        if ( ! isset($seopress_get_page->ID)) {
            seopress_pro_get_service('Redirection')->checkRegexRedirect();
            return;
        }

        if ('publish' !== get_post_status($seopress_get_page->ID)) {
            seopress_pro_get_service('Redirection')->checkRegexRedirect();
            return;
        }


        seopress_pro_get_service('Redirection')->handleRedirectionWithId($seopress_get_page->ID, [
            'init_url' => $get_init_current_url,
            'if_exact_match' => $if_exact_match
        ]);
    }
    add_action('template_redirect', 'seopress_301_do_redirect', 1);

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    //Monitor 404
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    //404 monitoring
    function seopress_404_enable_option() {
        $seopress_404_enable_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_404_enable_option)) {
            foreach ($seopress_404_enable_option as $key => $seopress_404_enable_value) {
                $options[$key] = $seopress_404_enable_value;
            }
            if (isset($seopress_404_enable_option['seopress_404_enable'])) {
                return $seopress_404_enable_option['seopress_404_enable'];
            }
        }
    }
    //Redirect to home
    function seopress_404_redirect_home_option() {
        $seopress_404_redirect_home_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_404_redirect_home_option)) {
            foreach ($seopress_404_redirect_home_option as $key => $seopress_404_redirect_home_value) {
                $options[$key] = $seopress_404_redirect_home_value;
            }
            if (isset($seopress_404_redirect_home_option['seopress_404_redirect_home'])) {
                return $seopress_404_redirect_home_option['seopress_404_redirect_home'];
            }
        }
    }
    //Redirect to custom url
    function seopress_404_redirect_custom_url_option() {
        $seopress_404_redirect_custom_url_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_404_redirect_custom_url_option)) {
            foreach ($seopress_404_redirect_custom_url_option as $key => $seopress_404_redirect_custom_url_value) {
                $options[$key] = $seopress_404_redirect_custom_url_value;
            }
            if (isset($seopress_404_redirect_custom_url_option['seopress_404_redirect_custom_url'])) {
                return $seopress_404_redirect_custom_url_option['seopress_404_redirect_custom_url'];
            }
        }
    }
    //Status code
    function seopress_404_redirect_status_code_option() {
        $seopress_404_redirect_status_code_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_404_redirect_status_code_option)) {
            foreach ($seopress_404_redirect_status_code_option as $key => $seopress_404_redirect_status_code_value) {
                $options[$key] = $seopress_404_redirect_status_code_value;
            }
            if (isset($seopress_404_redirect_status_code_option['seopress_404_redirect_status_code'])) {
                return $seopress_404_redirect_status_code_option['seopress_404_redirect_status_code'];
            }
        }
    }
    //Enable Mail notifications
    function seopress_404_enable_mails_option() {
        $seopress_404_enable_mails_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_404_enable_mails_option)) {
            foreach ($seopress_404_enable_mails_option as $key => $seopress_404_enable_mails_value) {
                $options[$key] = $seopress_404_enable_mails_value;
            }
            if (isset($seopress_404_enable_mails_option['seopress_404_enable_mails'])) {
                return $seopress_404_enable_mails_option['seopress_404_enable_mails'];
            }
        }
    }
    //To Mail Alert
    function seopress_404_enable_mails_from_option() {
        $seopress_404_enable_mails_from_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_404_enable_mails_from_option)) {
            foreach ($seopress_404_enable_mails_from_option as $key => $seopress_404_enable_mails_from_value) {
                $options[$key] = $seopress_404_enable_mails_from_value;
            }
            if (isset($seopress_404_enable_mails_from_option['seopress_404_enable_mails_from'])) {
                return $seopress_404_enable_mails_from_option['seopress_404_enable_mails_from'];
            }
        }
    }
    //IP logging
    function seopress_404_ip_logging_option() {
        $seopress_404_ip_logging_option = get_option('seopress_pro_option_name');
        if ( ! empty($seopress_404_ip_logging_option)) {
            foreach ($seopress_404_ip_logging_option as $key => $seopress_404_ip_logging_value) {
                $options[$key] = $seopress_404_ip_logging_value;
            }
            if (isset($seopress_404_ip_logging_option['seopress_404_ip_logging'])) {
                return $seopress_404_ip_logging_option['seopress_404_ip_logging'];
            }
        }
    }
    function seopress_404_send_alert($get_current_url) {
        function seopress_404_send_alert_content_type() {
            return 'text/html';
        }
        add_filter('wp_mail_content_type', 'seopress_404_send_alert_content_type');

        $to = seopress_404_enable_mails_from_option();
        $subject = '404 alert - ' . get_bloginfo('name');

        $body = "<style>
			#wrapper {
				background-color: #F9F9F9;
				margin: 0;
				padding: 70px 0 70px 0;
				-webkit-text-size-adjust: none !important;
				width: 100%;
			}

			#template_container {
				box-shadow:0 0 0 1px #f3f3f3 !important;
				background-color: #ffffff;
				border: 1px solid #e9e9e9;
				padding: 0;
			}

			#template_header {
				color: #333;
				font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;
			}

			#template_header h1,
			#template_header h1 a {
				color: #232323;
			}

			#template_footer td {
				padding: 0;
			}

			#template_footer #credit {
				font-family: courier, 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif;
				font-size: 12px;
				line-height: 125%;
				text-align: center;
				padding: 12px 28px 28px 28px;
			}

			#body_content {
				background-color: #ffffff;
			}

			#body_content table td {
				padding: 48px;
			}

			#body_content table td td {
				padding: 12px;
			}

			#body_content table td th {
				padding: 12px;
			}

			#body_content p {
				margin: 0 0 16px;
			}

			#body_content_inner {
				color: #505050;
				font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;
				font-size: 14px;
				line-height: 150%;
			}

			.td {
				color: #505050;
				border: 1px solid #E5E5E5;
			}

			.text {
				color: #505050;
				font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;
			}

			.link {
				color: #232323;
			}

			#header_wrapper {
				padding: 12px 0 8px 48px;
				display: block;
				border-bottom: 1px solid #F1F1F1;
			}

			h1 {
				color: #232323;
				font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;
				font-size: 18px;
				margin: 0;
				-webkit-font-smoothing: antialiased;
			}

			h2 {
				color: #232323;
				display: block;
				font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;
				font-size: 18px;
				font-weight: bold;
				line-height: 130%;
				margin: 16px 0 8px;
			}

			h3 {
				color: #232323;
				display: block;
				font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;
				font-size: 16px;
				font-weight: bold;
				line-height: 130%;
				margin: 16px 0 8px;
			}

			a {
				color: #232323;
				font-weight: normal;
				text-decoration: underline;
			}

			img {
				border: none;
				display: inline;
				font-size: 14px;
				font-weight: bold;
				height: auto;
				line-height: 100%;
				outline: none;
				text-decoration: none;
				text-transform: capitalize;
			}
		</style>";
        $body .= '<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
			<div id="wrapper">
				<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
					<tr>
						<td align="center" valign="top">
							<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
								<tr>
									<td align="center" valign="top">
										<!-- Header -->
										<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header">
											<tr>
												<td id="header_wrapper">
													<h1>' . __('404 alert', 'wp-seopress-pro') . '</h1>
												</td>
											</tr>
										</table>
										<!-- End Header -->
									</td>
								</tr>
								<tr>
									<td align="center" valign="top">
										<!-- Body -->
										<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
											<tr>
												<td valign="top" id="body_content">
													<!-- Content -->
													<table border="0" cellpadding="20" cellspacing="0" width="100%">
														<tr>
															<td valign="top">
																<div id="body_content_inner">
																	<p>' . __('You are receiving this email because a new 404 error has been logged on your site. See below:', 'wp-seopress-pro') . '</p>
																	<ul><li>' . get_home_url() . '/' . $get_current_url . '</li></ul>
																</div>
															</td>
														</tr>
													</table>
													<!-- End Content -->
												</td>
											</tr>
										</table>
										<!-- End Body -->
									</td>
								</tr>
								<tr>
									<td align="center" valign="top">
										<!-- Footer -->
										<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
											<tr>
												<td valign="top">
													<table border="0" cellpadding="10" cellspacing="0" width="100%">
														<tr>
															<td colspan="2" id="credit" style="border:0;color: #878787; border-top: 1px solid #F1F1F1;" valign="middle">
																<p><a href="' . get_home_url() . '">' . get_bloginfo('name') . '</a></p>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
										<!-- End Footer -->
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</body>';

        wp_mail($to, $subject, $body);

        remove_filter('wp_mail_content_type', 'seopress_404_send_alert_content_type');
    }

    //Create Redirection in Post Type
    function seopress_404_create_redirect() {
        global $wp;
        global $post;

        $get_current_url = htmlspecialchars(rawurldecode(add_query_arg([], $wp->request)));

        //Exclude URLs from cache
        $match = false;
        $seopress_404_exclude = ['wp-content/cache'];
        $seopress_404_exclude = apply_filters('seopress_404_exclude', $seopress_404_exclude);

        foreach ($seopress_404_exclude as $kw) {
            if (0 === strpos($get_current_url, $kw)) {
                $match = true;
                break;
            }
        }

        //Get Current Time
        $seopress_get_current_time = time();

        //Creating 404 error in seopress_404
        if (false === $match) {
            $seopress_get_page = seopress_get_page_by_title($get_current_url, '', 'seopress_404');

            //Get Title
            if ('' != $seopress_get_page) {
                $seopress_get_post_title = $seopress_get_page->post_title;
            } else {
                $seopress_get_post_title = '';
            }

            //Get User Agent
            $seopress_get_ua = '';
            if ( ! empty($_SERVER['HTTP_USER_AGENT'])) {
                $seopress_get_ua = $_SERVER['HTTP_USER_AGENT'];
            }

            //Get Referer
            $seopress_get_referer = '';
            if (wp_get_referer()) {
                $seopress_get_referer = wp_get_referer();
            }

            //Get IP Address
            $seopress_get_ip = '';
            $ip_logging = 'full';
            if (seopress_404_ip_logging_option()) {
                $ip_logging = seopress_404_ip_logging_option();
            }
            if ($ip_logging === 'full' || $ip_logging === 'anon') {
                if (function_exists('seopress_get_ip_address') && '' != seopress_get_ip_address()) {
                    $seopress_get_ip = seopress_get_ip_address();

                    if ($ip_logging === 'anon' && function_exists('wp_privacy_anonymize_ip')) {
                        $seopress_get_ip = wp_privacy_anonymize_ip(seopress_get_ip_address());
                    }
                }
            }

            if ($get_current_url && $seopress_get_post_title != $get_current_url) {
                wp_insert_post(
                    [
                        'post_title' => $get_current_url,
                        'meta_input' => [
                            'seopress_redirections_ua' => $seopress_get_ua,
                            'seopress_redirections_referer' => $seopress_get_referer,
                            '_seopress_404_redirect_date_request' => $seopress_get_current_time,
                            '_seopress_redirections_ip' => $seopress_get_ip,
                        ],
                        'post_type' => 'seopress_404',
                        'post_status' => 'publish',
                    ]
                );

                if ('1' == seopress_404_enable_mails_option() && '' != seopress_404_enable_mails_from_option()) {
                    seopress_404_send_alert($get_current_url);
                }
            } elseif ($get_current_url && $seopress_get_page->post_title == $get_current_url) {
                $seopress_404_count = get_post_meta($seopress_get_page->ID, 'seopress_404_count', true);
                update_post_meta($seopress_get_page->ID, 'seopress_404_count', ++$seopress_404_count);
                update_post_meta($seopress_get_page->ID, '_seopress_404_redirect_date_request', $seopress_get_current_time);
                update_post_meta($seopress_get_page->ID, 'seopress_redirections_ua', $seopress_get_ua);
                update_post_meta($seopress_get_page->ID, 'seopress_redirections_referer', $seopress_get_referer);
                update_post_meta($seopress_get_page->ID, '_seopress_redirections_ip', $seopress_get_ip);
            }
        }
    }
    function seopress_is_bot() {
        $bot_regex = '/BotLink|bingbot|AhrefsBot|ahoy|AlkalineBOT|anthill|appie|arale|araneo|AraybOt|ariadne|arks|ATN_Worldwide|Atomz|bbot|Bjaaland|Ukonline|borg\-bot\/0\.9|boxseabot|bspider|calif|christcrawler|CMC\/0\.01|combine|confuzzledbot|CoolBot|cosmos|Internet Cruiser Robot|cusco|cyberspyder|cydralspider|desertrealm, desert realm|digger|DIIbot|grabber|downloadexpress|DragonBot|dwcp|ecollector|ebiness|elfinbot|esculapio|esther|fastcrawler|FDSE|FELIX IDE|ESI|fido|H�m�h�kki|KIT\-Fireball|fouineur|Freecrawl|gammaSpider|gazz|gcreep|golem|googlebot|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|iajabot|INGRID\/0\.1|Informant|InfoSpiders|inspectorwww|irobot|Iron33|JBot|jcrawler|Teoma|Jeeves|jobo|image\.kapsi\.net|KDD\-Explorer|ko_yappo_robot|label\-grabber|larbin|legs|Linkidator|linkwalker|Lockon|logo_gif_crawler|marvin|mattie|mediafox|MerzScope|NEC\-MeshExplorer|MindCrawler|udmsearch|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|sharp\-info\-agent|WebMechanic|NetScoop|newscan\-online|ObjectsSearch|Occam|Orbsearch\/1\.0|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|Getterrobo\-Plus|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Search\-AU|searchprocess|Senrigan|Shagseeker|sift|SimBot|Site Valet|skymob|SLCrawler\/2\.0|slurp|ESI|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|nil|suke|http:\/\/www\.sygol\.com|tach_bw|TechBOT|templeton|titin|topiclink|UdmSearch|urlck|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|crawlpaper|wapspider|WebBandit\/1\.0|webcatcher|T\-H\-U\-N\-D\-E\-R\-S\-T\-O\-N\-E|WebMoose|webquest|webreaper|webs|webspider|WebWalker|wget|winona|whowhere|wlm|WOLP|WWWC|none|XGET|Nederland\.zoek|AISearchBot|woriobot|NetSeer|Nutch|YandexBot|YandexMobileBot|SemrushBot|FatBot|MJ12bot|DotBot|AddThis|baiduspider|SeznamBot|mod_pagespeed|CCBot|openstat.ru\/Bot|m2e/i';

        $bot_regex = apply_filters('seopress_404_bots', $bot_regex);

        $userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? false : $_SERVER['HTTP_USER_AGENT'];
        if ('' != $bot_regex && '' != $userAgent) {
            $isBot = ! $userAgent || preg_match($bot_regex, $userAgent);

            return $isBot;
        }
    }

    function seopress_404_log() {
        if (is_404() && ! is_admin() && '' != seopress_404_redirect_home_option()) {
            if ('home' == seopress_404_redirect_home_option()) {
                if ('' != seopress_404_redirect_status_code_option()) {
                    if ('1' != seopress_is_bot() && seopress_404_enable_option()) {
                        seopress_404_create_redirect();
                    }
                    wp_redirect(get_home_url(), seopress_404_redirect_status_code_option());
                    exit;
                } else {
                    if ('1' != seopress_is_bot() && seopress_404_enable_option()) {
                        seopress_404_create_redirect();
                    }
                    wp_redirect(get_home_url(), '301');
                    exit;
                }
            } elseif ('custom' == seopress_404_redirect_home_option() && '' != seopress_404_redirect_custom_url_option()) {
                if ('' != seopress_404_redirect_status_code_option()) {
                    if ('1' != seopress_is_bot() && seopress_404_enable_option()) {
                        seopress_404_create_redirect();
                    }
                    wp_redirect(seopress_404_redirect_custom_url_option(), seopress_404_redirect_status_code_option());
                    exit;
                } else {
                    if ('1' != seopress_is_bot() && seopress_404_enable_option()) {
                        seopress_404_create_redirect();
                    }
                    wp_redirect(seopress_404_redirect_custom_url_option(), '301');
                    exit;
                }
            } else {
                if ('1' != seopress_is_bot() && seopress_404_enable_option()) {
                    seopress_404_create_redirect();
                }
            }
        } elseif (is_404() && ! is_admin() && seopress_404_enable_option()) {
            if ('1' != seopress_is_bot() && seopress_404_enable_option()) {
                seopress_404_create_redirect();
            }
        }
    }
    add_action('template_redirect', 'seopress_404_log');

    add_filter('auto-draft_to_publish', 'seopress_prevent_title_redirection_already_exist');
    add_filter('draft_to_publish', 'seopress_prevent_title_redirection_already_exist');
    function seopress_prevent_title_redirection_already_exist($post) {
        if ('seopress_404' !== $post->post_type) {
            return;
        }

        if (wp_is_post_revision($post)) {
            return;
        }

        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT *
			FROM $wpdb->posts
			WHERE 1=1
			AND post_title = %s
			AND post_type = %s
			AND post_status = 'publish'",
            $post->post_title,
            'seopress_404'
        );

        $wpdb->get_results($sql);

        $count_post_title_exist = $wpdb->num_rows;

        if ($count_post_title_exist > 1) { // already exist
            wp_delete_post($post->ID);
            $exist_redirect_post = get_page_by_title($post->post_title, OBJECT, 'seopress_404');

            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : admin_url('edit.php?post_type=seopress_404');
            $url = remove_query_arg('wp-post-new-reload', $referer);
            set_transient('seopress_prevent_title_redirection_already_exist', [
                'insert_post' => $post,
                'post_exist' => $exist_redirect_post,
                'seopress_redirections_value' => isset($_POST['seopress_redirections_value']) ? $_POST['seopress_redirections_value'] : null,
            ], 3600);

            wp_safe_redirect($url);
            exit;
        }

        // Remove notice watcher if needed
        $notices = seopress_get_option_post_need_redirects();

        if ($notices) {
            foreach ($notices as $key => $notice) {
                if (false !== strpos($notice['before_url'], $post->post_title)) {
                    seopress_remove_notification_for_redirect($notice['id']);
                }
            }
        }
    }

    add_action('seopress_admin_notices', 'seopress_notice_prevent_create_title_redirection');
    function seopress_notice_prevent_create_title_redirection() {
        $transient = get_transient('seopress_prevent_title_redirection_already_exist');
        if ( ! $transient) {
            return;
        }

        // Remove notice watcher if needed
        $notices = seopress_get_option_post_need_redirects();
        if ($notices) {
            foreach ($notices as $key => $notice) {
                if (false !== strpos($notice['before_url'], $transient['insert_post']->post_name)) {
                    seopress_remove_notification_for_redirect($notice['id']);
                }
            }
        }

        delete_transient('seopress_prevent_title_redirection_already_exist');

        $edit_post_link = get_edit_post_link($transient['post_exist']->ID);

        $message = sprintf(
            /* translators: %s: post name (slug) %s: url redirect */
            __('<p>We were unable to create the redirection you requested (<code>%s</code> to <code>%s</code>).</p>', 'wp-seopress-pro'),
            $transient['insert_post']->post_name,
            $transient['seopress_redirections_value']
        );

        $message .= sprintf(
            /* translators: %s: get_edit_post_link() %s: post name (slug) */
            __('<p>This URL has already a redirection: <a href="%s">%s</a> </p>', 'wp-seopress-pro'),
            $edit_post_link,
            $transient['post_exist']->post_name
        ); ?>
<div class="error notice is-dismissable">
    <?php echo $message; ?>
</div>
<?php
    }

    add_action('save_post_seopress_404', 'seopress_need_add_term_auto_redirect', 10, 2);
    function seopress_need_add_term_auto_redirect($post_id, $post) {
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            return;
        }

        $referer = wp_get_referer();
        if ( ! $referer) {
            return;
        }

        $parse_referer = wp_parse_url($referer);
        if (false === strpos($parse_referer['query'], 'prepare_redirect=1')) {
            return;
        }

        $name_term = 'Auto Redirect';
        $slug_term = 'autoredirect_by_seopress';
        $term_autoredirect = get_term_by('slug', $slug_term, 'seopress_404_cat', ARRAY_A);
        if ( ! $term_autoredirect) {
            $term_autoredirect = wp_insert_term($name_term, 'seopress_404_cat', [
                'slug' => $slug_term,
            ]);
        }

        $term_id = $term_autoredirect['term_id'];

        $terms = get_the_terms($post_id, 'seopress_404_cat');
        $terms_id = [$term_id];
        if ($terms) {
            foreach ($terms as $term) {
                $terms_id[] = $term->term_id;
            }
        }

        wp_set_post_terms($post_id, $terms_id, 'seopress_404_cat');
    }
}
