<?php
defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');
if ('1' == seopress_get_toggle_option('bot')) {
    if (is_admin()) {
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Register SEOPress BOT Custom Post Type
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        function seopress_bot_fn() {
            $labels = [
                'name'                  => _x('Broken links', 'Post Type General Name', 'wp-seopress-pro'),
                'singular_name'         => _x('Broken links', 'Post Type Singular Name', 'wp-seopress-pro'),
                'menu_name'             => __('Broken links', 'wp-seopress-pro'),
                'name_admin_bar'        => __('Broken links', 'wp-seopress-pro'),
                'archives'              => __('Item Links', 'wp-seopress-pro'),
                'parent_item_colon'     => __('Parent Link:', 'wp-seopress-pro'),
                'all_items'             => __('All Broken links', 'wp-seopress-pro'),
                'add_new_item'          => __('Add New Link', 'wp-seopress-pro'),
                'add_new'               => __('Add link', 'wp-seopress-pro'),
                'new_item'              => __('New link', 'wp-seopress-pro'),
                'edit_item'             => __('Edit link', 'wp-seopress-pro'),
                'update_item'           => __('Update Link', 'wp-seopress-pro'),
                'view_item'             => __('View Link', 'wp-seopress-pro'),
                'search_items'          => __('Search Link', 'wp-seopress-pro'),
                'not_found'             => __('Not found', 'wp-seopress-pro'),
                'not_found_in_trash'    => __('Not found in Trash', 'wp-seopress-pro'),
                'featured_image'        => __('Featured Image', 'wp-seopress-pro'),
                'set_featured_image'    => __('Set featured image', 'wp-seopress-pro'),
                'remove_featured_image' => __('Remove featured image', 'wp-seopress-pro'),
                'use_featured_image'    => __('Use as featured image', 'wp-seopress-pro'),
                'insert_into_item'      => __('Insert into item', 'wp-seopress-pro'),
                'uploaded_to_this_item' => __('Uploaded to this item', 'wp-seopress-pro'),
                'items_list'            => __('Redirections list', 'wp-seopress-pro'),
                'items_list_navigation' => __('Redirections list navigation', 'wp-seopress-pro'),
                'filter_items_list'     => __('Filter redirections list', 'wp-seopress-pro'),
            ];
            $args = [
                'label'                 => __('Broken links', 'wp-seopress-pro'),
                'description'           => __('List of broken links', 'wp-seopress-pro'),
                'labels'                => $labels,
                'supports'              => ['title', 'editor', 'custom-fields'],
                'hierarchical'          => false,
                'public'                => false,
                'show_ui'               => true,
                'show_in_menu'          => false,
                'menu_icon'             => 'dashicons-admin-links',
                'show_in_admin_bar'     => false,
                'show_in_nav_menus'     => false,
                'can_export'            => true,
                'has_archive'           => false,
                'exclude_from_search'   => true,
                'publicly_queryable'    => false,
                'capability_type'       => 'post',
                'capabilities'          => [
                    'create_posts' => 'false',
                ],
                'map_meta_cap' => true,
            ];
            register_post_type('seopress_bot', $args);
        }
        add_action('init', 'seopress_bot_fn', 10);

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Remove bulk / inline edit for BOT Custom Post Type
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        add_filter('post_row_actions', 'seopress_bot_bulk_inline_actions', 10, 2);
        function seopress_bot_bulk_inline_actions($actions, $post) {
            // Check for your post type.
            if ('seopress_bot' == $post->post_type) {
                $edit_link = admin_url('post.php?post=' . get_post_meta($post->ID, 'seopress_bot_source_id', true) . '&action=edit');
                $trash     = $actions['trash'];
                $actions   = [
                    'edit' => sprintf('<a href="%1$s">%2$s</a>',
                    esc_url($edit_link),
                    esc_html(__('Edit source to fix link', 'wp-seopress-pro'))),
                ];

                $actions['trash']=$trash;
            }

            return $actions;
        }

        add_filter('bulk_actions-edit-seopress_bot', 'seopress_bot_bulk_edit_actions');
        function seopress_bot_bulk_edit_actions($actions) {
            unset($actions['edit']);

            return $actions;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Filters view
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        function seopress_bot_filters_cpt() {
            global $typenow;

            if ('seopress_bot' == $typenow) {
                $status = ['200', '301', '302', '307', '400', '401', '402', '403', '404', '410', '451', '500'];

                echo "<select name='bot-status' id='bot-status' class='postform'>";
                echo "<option value=''>" . __('Show All', 'wp-seopress-pro') . '</option>';
                foreach ($status as $code) {
                    echo '<option value=' . $code, isset($_GET[$code]) == $code ? ' selected="selected"' : '','>' . $code . '</option>';
                }
                echo '</select>';
            }
        }
        add_action('restrict_manage_posts', 'seopress_bot_filters_cpt');

        function seopress_bot_filters_action($query) {
            global $pagenow;
            $current_page = isset($_GET['post_type']) ? $_GET['post_type'] : '';

            if (is_admin() && 'seopress_bot' == $current_page && 'edit.php' == $pagenow && isset($_GET['bot-status']) &&
                '' != $_GET['bot-status']) {
                $code                              = $_GET['bot-status'];
                $query->query_vars['meta_key']     = 'seopress_bot_status';
                $query->query_vars['meta_value']   = $code;
                $query->query_vars['meta_compare'] = '=';
            }
        }
        add_filter('parse_query', 'seopress_bot_filters_action');

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Set messages for BOT Custom Post Type
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        function seopress_bot_set_messages($messages) {
            global $post, $post_ID;
            $post_type = 'seopress_bot';

            $obj      = get_post_type_object($post_type);
            $singular = $obj->labels->singular_name;

            $messages[$post_type] = [
                0  => '', // Unused. Messages start at index 1.
                1  => sprintf(__($singular . ' updated.'), esc_url(get_permalink($post_ID))),
                2  => __('Custom field updated.'),
                3  => __('Custom field deleted.'),
                4  => __($singular . ' updated.'),
                5  => isset($_GET['revision']) ? sprintf(__($singular . ' restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
                6  => sprintf(__($singular . ' published.'), esc_url(get_permalink($post_ID))),
                7  => __('Page saved.'),
                8  => sprintf(__($singular . ' submitted.'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
                9  => sprintf(__($singular . ' scheduled for: <strong>%1$s</strong>. '), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
                10 => sprintf(__($singular . ' draft updated.'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
            ];

            return $messages;
        }

        add_filter('post_updated_messages', 'seopress_bot_set_messages');

        function seopress_bot_set_messages_list($bulk_messages, $bulk_counts) {
            $bulk_messages['seopress_bot'] = [
                'updated'   => _n('%s broken link updated.', '%s broken links updated.', $bulk_counts['updated']),
                'locked'    => _n('%s broken link not updated, somebody is editing it.', '%s broken links not updated, somebody is editing them.', $bulk_counts['locked']),
                'deleted'   => _n('%s broken link permanently deleted.', '%s broken links permanently deleted.', $bulk_counts['deleted']),
                'trashed'   => _n('%s broken link moved to the Trash.', '%s broken links moved to the Trash.', $bulk_counts['trashed']),
                'untrashed' => _n('%s broken link restored from the Trash.', '%s broken links restored from the Trash.', $bulk_counts['untrashed']),
            ];

            return $bulk_messages;
        }
        add_filter('bulk_post_updated_messages', 'seopress_bot_set_messages_list', 10, 2);

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Add custom buttons to SEOPress BOT Custom Post Type
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        function seopress_bot_btn() {
            $screen = get_current_screen();
            if ('seopress_bot' == $screen->post_type) {
                ?>
				<script>
				jQuery(function(){
					jQuery("body.post-type-seopress_bot .wrap h1").append('<a href="<?php echo admin_url('admin.php?page=seopress-bot-batch'); ?>" class="page-title-action"><?php esc_attr_e('Scan broken links', 'wp-seopress-pro'); ?></a> <a href="<?php echo admin_url('admin.php?page=seopress-bot-batch'); ?>" class="page-title-action"><?php esc_attr_e('Export to CSV', 'wp-seopress-pro'); ?></a>');
				});
				</script>
			<?php
            }
        }
        add_action('admin_head', 'seopress_bot_btn');

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        //Columns for BOT Custom Post Type
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        add_filter('manage_edit-seopress_bot_columns', 'seopress_bot_count_columns');
        add_action('manage_seopress_bot_posts_custom_column', 'seopress_bot_count_display_column', 10, 2);

        function seopress_bot_count_columns($columns) {
            $columns['seopress_bot_broken_link']    = __('Broken link', 'wp-seopress-pro');
            $columns['seopress_bot_count']          = __('Count', 'wp-seopress-pro');
            $columns['seopress_bot_status']         = __('Status', 'wp-seopress-pro');
            $columns['seopress_bot_type']           = __('Type', 'wp-seopress-pro');
            $columns['seopress_bot_anchor']         = __('Anchor text', 'wp-seopress-pro');
            $columns['seopress_bot_source']         = __('Source', 'wp-seopress-pro');
            $columns['seopress_bot_cpt']            = __('Post type', 'wp-seopress-pro');
            unset($columns['date']);
            unset($columns['title']);

            return $columns;
        }

        function seopress_bot_count_display_column($column, $post_id) {
            if ($post_id) {
                if ('seopress_bot_broken_link' == $column) {
                    if (get_post_meta($post_id, 'seopress_bot_source_id', true)) {
                        $p_id = get_post_meta($post_id, 'seopress_bot_source_id', true);

                        if (isset($p_id)) {
                            $broken_link_edit = get_edit_post_link($p_id);
                            echo '<a href="' . $broken_link_edit . '">';
                            echo get_the_title($post_id);
                            echo ' - <span class="dashicons dashicons-edit"></span>';
                            echo '</a>';
                        }
                    }
                }
                if ('seopress_bot_count' == $column) {
                    echo get_post_meta($post_id, 'seopress_bot_count', true);
                }
                if ('seopress_bot_status' == $column) {
                    $seopress_bot_status = get_post_meta($post_id, 'seopress_bot_status', true);
                    switch ($seopress_bot_status) {
                        case '500':
                            echo '<span class="seopress_bot_500">' . $seopress_bot_status . '</span>';
                            break;

                        case '404':
                        case '403':
                        case '402':
                        case '401':
                        case '400':
                            echo '<span class="seopress_bot_404">' . $seopress_bot_status . '</span>';
                            break;

                        case '307':
                            echo '<span class="seopress_bot_307">' . $seopress_bot_status . '</span>';
                            break;

                        case '302':
                            echo '<span class="seopress_bot_302">' . $seopress_bot_status . '</span>';
                            break;

                        case '301':
                            echo '<span class="seopress_bot_301">' . $seopress_bot_status . '</span>';
                            break;

                        case '200':
                            echo '<span class="seopress_bot_200">' . $seopress_bot_status . '</span>';
                            break;

                        default:
                            echo '<span class="seopress_bot_default">' . esc_html($seopress_bot_status) . '</span>';
                            break;
                    }
                }
                if ('seopress_bot_type' == $column) {
                    echo get_post_meta($post_id, 'seopress_bot_type', true);
                }
                if ('seopress_bot_anchor' == $column) {
                    echo get_post_meta($post_id, 'seopress_bot_a_title', true);
                }
                if ('seopress_bot_cpt' == $column) {
                    echo get_post_meta($post_id, 'seopress_bot_cpt', true);
                }
                if ('seopress_bot_source' == $column) {
                    echo '<a href="' . get_post_meta($post_id, 'seopress_bot_source_url', true) . '">' . get_post_meta($post_id, 'seopress_bot_source_title', true) . '</a>';
                }
            }
        }

        //Sortable columns
        add_filter('manage_edit-seopress_bot_sortable_columns', 'seopress_bot_sortable_columns');

        function seopress_bot_sortable_columns($columns) {
            $columns['seopress_bot_status'] = 'seopress_bot_status';

            return $columns;
        }

        add_filter('pre_get_posts', 'seopress_bot_sort_columns_by');
        function seopress_bot_sort_columns_by($query) {
            if ( ! is_admin()) {
                return;
            } else {
                $orderby = $query->get('orderby');
                if ('seopress_bot_status' == $orderby) {
                    $query->set('meta_key', 'seopress_bot_status');
                    $query->set('orderby', 'meta_value');
                }
            }
        }
    }
}
