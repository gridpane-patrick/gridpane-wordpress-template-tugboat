<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

/**
 * Return the conditions for schemas.
 *
 * @since 3.8.1
 *
 * @author Julio Potier
 *
 * @return (array)
 **/
function seopress_get_schemas_conditions() {
    return ['equal' => __('is equal to', 'wp-seopress-pro'), 'not_equal' => __('is NOT equal to', 'wp-seopress-pro')];
}

/**
 * Return the filters for schemas.
 *
 * @since 3.8.1
 *
 * @author Julio Potier
 *
 * @return (array)
 **/
function seopress_get_schemas_filters() {
    return [
        'post_type' => __('Post Type', 'wp-seopress-pro'),
        'taxonomy' => __('Term Taxonomy', 'wp-seopress-pro'),
        'postId' => __('Post ID', 'wp-seopress-pro'),
    ];
}

/**
 * Return default values for retrocompat.
 *
 * @since 3.8.1
 *
 * @author Julio Potier
 *
 * @return (array)
 *
 * @param mixed $rule
 **/
function seopress_get_default_schemas_rules($rule) {
    return [
        [
            [
                'filter' => 'post_type',
                'cpt' => $rule, 'taxo' => 0,
                'cond' => 'equal',
            ],
        ],
    ];
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Register SEOPress Schemas Custom Post Type
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_schemas_fn() {
    $labels = [
        'name' => _x('Schemas', 'Post Type General Name', 'wp-seopress-pro'),
        'singular_name' => _x('Schema', 'Post Type Singular Name', 'wp-seopress-pro'),
        'menu_name' => __('Schemas', 'wp-seopress-pro'),
        'name_admin_bar' => __('Schemas', 'wp-seopress-pro'),
        'archives' => __('Item Archives', 'wp-seopress-pro'),
        'parent_item_colon' => __('Parent Item:', 'wp-seopress-pro'),
        'all_items' => __('All schemas', 'wp-seopress-pro'),
        'add_new_item' => __('Add New schema', 'wp-seopress-pro'),
        'add_new' => __('Add schema', 'wp-seopress-pro'),
        'new_item' => __('New schema', 'wp-seopress-pro'),
        'edit_item' => __('Edit schema', 'wp-seopress-pro'),
        'update_item' => __('Update schema', 'wp-seopress-pro'),
        'view_item' => __('View schema', 'wp-seopress-pro'),
        'search_items' => __('Search schema', 'wp-seopress-pro'),
        'not_found' => __('Not found', 'wp-seopress-pro'),
        'not_found_in_trash' => __('Not found in Trash', 'wp-seopress-pro'),
        'featured_image' => __('Featured Image', 'wp-seopress-pro'),
        'set_featured_image' => __('Set featured image', 'wp-seopress-pro'),
        'remove_featured_image' => __('Remove featured image', 'wp-seopress-pro'),
        'use_featured_image' => __('Use as featured image', 'wp-seopress-pro'),
        'insert_into_item' => __('Insert into item', 'wp-seopress-pro'),
        'uploaded_to_this_item' => __('Uploaded to this item', 'wp-seopress-pro'),
        'items_list' => __('Schemas list', 'wp-seopress-pro'),
        'items_list_navigation' => __('Schemas list navigation', 'wp-seopress-pro'),
        'filter_items_list' => __('Filter schema list', 'wp-seopress-pro'),
    ];
    $args = [
        'label' => __('Schemas', 'wp-seopress-pro'),
        'description' => __('List of Schemas', 'wp-seopress-pro'),
        'labels' => $labels,
        'supports' => ['title'],
        'hierarchical' => false,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'menu_icon' => 'dashicons-excerpt-view',
        'show_in_admin_bar' => false,
        'show_in_nav_menus' => false,
        'can_export' => true,
        'has_archive' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'capability_type' => 'schema',
        'capabilities' => [
            'edit_post' => 'edit_schema',
            'edit_posts' => 'edit_schemas',
            'edit_others_posts' => 'edit_others_schemas',
            'publish_posts' => 'publish_schemas',
            'read_post' => 'read_schema',
            'read_private_posts' => 'read_private_schemas',
            'delete_post' => 'delete_schema',
            'delete_others_posts' => 'delete_others_schemas',
            'delete_published_posts' => 'delete_published_schemas',
        ],
    ];
    register_post_type('seopress_schemas', $args);
}
add_action('admin_init', 'seopress_schemas_fn', 10);

///////////////////////////////////////////////////////////////////////////////////////////////////
//Map SEOPress Schema caps
///////////////////////////////////////////////////////////////////////////////////////////////////
add_filter('map_meta_cap', 'seopress_schemas_map_meta_cap', 10, 4);
function seopress_schemas_map_meta_cap($caps, $cap, $user_id, $args) {
    /* If editing, deleting, or reading a schema, get the post and post type object. */
    if ('edit_schema' === $cap || 'delete_schema' === $cap || 'read_schema' === $cap) {
        $post = get_post($args[0]);
        $post_type = get_post_type_object($post->post_type);

        /* Set an empty array for the caps. */
        $caps = [];
    }

    /* If editing a schema, assign the required capability. */
    if ('edit_schema' === $cap) {
        if ($user_id == $post->post_author) {
            $caps[] = $post_type->cap->edit_posts;
        } else {
            $caps[] = $post_type->cap->edit_others_posts;
        }
    }

    /* If deleting a schema, assign the required capability. */
    elseif ('delete_schema' === $cap) {
        if ($user_id == $post->post_author) {
            $caps[] = $post_type->cap->delete_published_posts;
        } else {
            $caps[] = $post_type->cap->delete_others_posts;
        }
    }

    /* If reading a private schema, assign the required capability. */
    elseif ('read_schema' === $cap) {
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
//Set title placeholder for Schemas Custom Post Type
///////////////////////////////////////////////////////////////////////////////////////////////////
function seopress_schemas_cpt_title($title) {
    $screen = get_current_screen();
    if ('seopress_schemas' == $screen->post_type) {
        $title = __('Enter the name of your schema', 'wp-seopress-pro');
    }

    return $title;
}

add_filter('enter_title_here', 'seopress_schemas_cpt_title');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Add custom buttons to SEOPress Schemas Post Type
///////////////////////////////////////////////////////////////////////////////////////////////////

function seopress_schemas_btn_cpt() {
    $screen = get_current_screen();
    if ('seopress_schemas' == $screen->post_type) {
        ?>
<script>
jQuery(function() {
jQuery("body.post-type-seopress_schemas .wrap h1 ~ a").after(
    '<a href="<?php echo admin_url('admin.php?page=seopress-pro-page#tab=tab_seopress_rich_snippets'); ?>" id="seopress-schemas-settings" class="page-title-action"><?php _e('Settings', 'wp-seopress-pro'); ?></a>'
);
});
</script>
<?php
    }
}
add_action('admin_head', 'seopress_schemas_btn_cpt');

///////////////////////////////////////////////////////////////////////////////////////////////////
//Add buttons to post type list if empty
///////////////////////////////////////////////////////////////////////////////////////////////////
add_action('manage_posts_extra_tablenav', 'seopress_schemas_maybe_render_blank_state');

function seopress_schemas_render_blank_state() {
    $docs = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : ''; ?>
<div class="seopress-BlankState">

    <h2 class="seopress-BlankState-message">
        <?php esc_html_e('Boost your visibility in search results and increase your traffic and conversions.', 'wp-seopress-pro'); ?>
    </h2>

    <div class="seopress-BlankState-buttons">

        <a class="seopress-BlankState-cta btn btnPrimary"
            href="<?php echo esc_url(admin_url('post-new.php?post_type=seopress_schemas')); ?>">
            <?php esc_html_e('Create a schema', 'wp-seopress-pro'); ?>
        </a>

        <a class="seopress-BlankState-cta btn btnSecondary"
            href="<?php echo $docs['schemas']['add']; ?>"
            target="_blank">
            <?php esc_html_e('Learn more about schemas', 'wp-seopress-pro'); ?>
        </a>

    </div>

</div>

<?php
}
function seopress_schemas_maybe_render_blank_state($which) {
    global $post_type;

    if ('seopress_schemas' === $post_type && 'bottom' === $which) {
        $counts = (array) wp_count_posts($post_type);
        unset($counts['auto-draft']);
        $count = array_sum($counts);

        if (isset($_GET['seopress_support']) && '1' === $_GET['seopress_support']) {
            ?>
<a href="<?php
                echo wp_nonce_url(
                add_query_arg(
                    [
                        'action' => 'seopress_relaunch_upgrader',
                    ],
                    admin_url('admin-post.php')
                ),
                'seopress_relaunch_upgrader'
            ); ?>" class="btn btn-primary">
    Reload upgrader schema
</a>
<?php
        }

        if (0 < $count) {
            return;
        }

        seopress_schemas_render_blank_state();

        echo '<style type="text/css">#posts-filter .wp-list-table, #posts-filter .tablenav.top, .tablenav.bottom .actions, .wrap .subsubsub  { display: none; } #posts-filter .tablenav.bottom { height: auto; } </style>';
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Set messages for Schemas Custom Post Type
///////////////////////////////////////////////////////////////////////////////////////////////////

function seopress_schemas_set_messages($messages) {
    global $post, $post_ID, $typenow;
    $post_type = 'seopress_schemas';

    if ('seopress_schemas' === $typenow) {
        $obj = get_post_type_object($post_type);
        $singular = $obj->labels->singular_name;

        $messages[$post_type] = [
            0 => '', // Unused. Messages start at index 1.
            1 => __($singular . ' updated.'),
            2 => __('Custom field updated.'),
            3 => __('Custom field deleted.'),
            4 => __($singular . ' updated.'),
            5 => isset($_GET['revision']) ? sprintf(__($singular . ' restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6 => __($singular . ' published.'),
            7 => __('Schema saved.'),
            8 => sprintf(__($singular . ' submitted.'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
            9 => sprintf(__($singular . ' scheduled for: <strong>%1$s</strong>. '), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
            10 => sprintf(__($singular . ' draft updated.'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
        ];

        return $messages;
    } else {
        return $messages;
    }
}

add_filter('post_updated_messages', 'seopress_schemas_set_messages');

function seopress_schemas_set_messages_list($bulk_messages, $bulk_counts) {
    $bulk_messages['seopress_schemas'] = [
        'updated' => _n('%s schema updated.', '%s schemas updated.', $bulk_counts['updated']),
        'locked' => _n('%s schema not updated, somebody is editing it.', '%s schemas not updated, somebody is editing them.', $bulk_counts['locked']),
        'deleted' => _n('%s schema permanently deleted.', '%s schemas permanently deleted.', $bulk_counts['deleted']),
        'trashed' => _n('%s schema moved to the Trash.', '%s schemas moved to the Trash.', $bulk_counts['trashed']),
        'untrashed' => _n('%s schema restored from the Trash.', '%s schemas restored from the Trash.', $bulk_counts['untrashed']),
    ];

    return $bulk_messages;
}
add_filter('bulk_post_updated_messages', 'seopress_schemas_set_messages_list', 10, 2);

///////////////////////////////////////////////////////////////////////////////////////////////////
//Columns for Schemas Custom Post Type
///////////////////////////////////////////////////////////////////////////////////////////////////

add_filter('manage_edit-seopress_schemas_columns', 'seopress_schemas_columns');
add_action('manage_seopress_schemas_posts_custom_column', 'seopress_schemas_display_column', 10, 2);

function seopress_schemas_columns($columns) {
    $columns['seopress_schemas_type'] = __('Data type', 'wp-seopress-pro');
    $columns['seopress_schemas_cpt'] = __('Post type', 'wp-seopress-pro');
    unset($columns['date']);

    return $columns;
}

function seopress_schemas_display_column($column, $post_id) {
    if ('seopress_schemas_type' == $column) {
        if (get_post_meta($post_id, '_seopress_pro_rich_snippets_type', true)) {
            echo get_post_meta($post_id, '_seopress_pro_rich_snippets_type', true);
        }
    }
    if ('seopress_schemas_cpt' == $column) {
        if (get_post_meta($post_id, '_seopress_pro_rich_snippets_rules', true)) {
            $rules = get_post_meta($post_id, '_seopress_pro_rich_snippets_rules', true);
            if ( ! is_array($rules)) {
                $rules = seopress_get_default_schemas_rules($rules);
            }
            $conditions = seopress_get_schemas_conditions();
            $filters = seopress_get_schemas_filters();
            $n = 0;
            $html = '';
            foreach ($rules as $or => $values) {
                foreach ($values as $and => $value) {
                    $filter = esc_html($filters[$value['filter']]);
                    $cond = $conditions[$value['cond']];
                    if ('post_type' === $value['filter'] && post_type_exists($value['cpt'])) {
                        $label = esc_html(get_post_type_object($value['cpt'])->label);
                        $html .= " <strong>$filter</strong> <em>$cond</em> \"$label\" ";
                    } elseif ('taxonomy' === $value['filter'] && term_exists((int) $value['taxo'])) {
                        $tax = get_term($value['taxo']);
                        if ( ! is_wp_error($tax) && is_object($tax)) {
                            $tax = esc_html(get_taxonomy($tax->taxonomy)->label);
                            $label = esc_html(get_term($value['taxo'])->name);
                            $html .= " <strong>$filter</strong> \"$tax\" <em>$cond</em> \"$label\" ";
                        }
                    } elseif ('postId' === $value['filter']) {
                        $label = esc_html($value['postId']);
                        $html .= " <strong>$filter</strong> <em>$cond</em> \"$label\" ";
                    }
                    $html .= __('and', 'wp-seopress-pro');
                    ++$n;
                    if (3 === $n) {
                        $html = trim($html, __('and', 'wp-seopress-pro') . ' ');
                        $html .= '&hellip;';
                        continue 2;
                    }
                }
                $html = trim($html, __('and', 'wp-seopress-pro'));
                $html .= __('or', 'wp-seopress-pro');
            }
            $html = trim($html, __('or', 'wp-seopress-pro'));
            echo $html;
        }
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Display metabox for Schemas Custom Post Type
///////////////////////////////////////////////////////////////////////////////////////////////////
add_action('add_meta_boxes', 'seopress_schemas_init_metabox');
function seopress_schemas_init_metabox() {
    add_meta_box('seopress_schemas', __('Your schema', 'wp-seopress-pro'), 'seopress_schemas_cpt', 'seopress_schemas', 'normal', 'default');
}

function seopress_schemas_cpt($post) {
    $prefix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

    wp_nonce_field(plugin_basename(__FILE__), 'seopress_schemas_cpt_nonce');

    global $typenow;

    //Enqueue scripts
    wp_enqueue_script('jquery-ui-accordion');

    wp_enqueue_script('seopress-pro-media-uploader-js', plugins_url('assets/js/seopress-pro-media-uploader.js', dirname(dirname(dirname(__FILE__)))), ['jquery'], SEOPRESS_PRO_VERSION, false);

    wp_enqueue_script('seopress-pro-rich-snippets-js', plugins_url('assets/js/seopress-pro-rich-snippets' . $prefix . '.js', dirname(dirname(dirname(__FILE__)))), ['jquery'], SEOPRESS_PRO_VERSION, false);

    wp_enqueue_media();

    wp_enqueue_script('jquery-ui-datepicker');

    //Post types
    if (function_exists('seopress_get_post_types')) {
        $seopress_get_post_types = seopress_get_post_types();
    }

    //Filter taxonomies list to get WC product attributes
    add_filter('seopress_get_taxonomies_args', 'sp_get_taxonomies_args');
    function sp_get_taxonomies_args($args) {
        $args = [];

        return $args;
    }
    add_filter('seopress_get_taxonomies_list', 'sp_get_taxonomies_list');
    function sp_get_taxonomies_list($terms) {
        unset($terms['seopress_404_cat']);
        unset($terms['nav_menu']);
        unset($terms['link_category']);
        unset($terms['post_format']);

        return $terms;
    }

    //Mapping fields
    function seopress_schemas_mapping_array($post_meta_name, $cases) {
        global $post;

        //Custom fields
        if (function_exists('seopress_get_custom_fields')) {
            $seopress_get_custom_fields = seopress_get_custom_fields();
        }

        //init default case array
        $seopress_schemas_mapping_case = [
            'Select an option' => ['none' => __('None', 'wp-seopress-pro')],
            'Site Meta' => [
                'site_title' => __('Site Title', 'wp-seopress-pro'),
                'tagline' => __('Tagline', 'wp-seopress-pro'),
                'site_url' => __('Site URL', 'wp-seopress-pro'),
            ],
            'Post Meta' => [
                'post_id' => __('Post / Product ID', 'wp-seopress-pro'),
                'post_title' => __('Post Title / Product title', 'wp-seopress-pro'),
                'post_excerpt' => __('Excerpt / Product short description', 'wp-seopress-pro'),
                'post_content' => __('Content', 'wp-seopress-pro'),
                'post_permalink' => __('Permalink', 'wp-seopress-pro'),
                'post_author_name' => __('Author', 'wp-seopress-pro'),
                'post_date' => __('Publish date', 'wp-seopress-pro'),
                'post_updated' => __('Last update', 'wp-seopress-pro'),
            ],
            'Product meta (WooCommerce)' => [
                'product_regular_price' => __('Regular Price', 'wp-seopress-pro'),
                'product_sale_price' => __('Sale Price', 'wp-seopress-pro'),
                'product_price_with_tax' => __('Sales price, including tax', 'wp-seopress-pro'),
                'product_date_from' => __('Sale price dates "From"', 'wp-seopress-pro'),
                'product_date_to' => __('Sale price dates "To"', 'wp-seopress-pro'),
                'product_sku' => __('SKU', 'wp-seopress-pro'),
                'product_barcode_type' => __('Product Global Identifier type', 'wp-seopress-pro'),
                'product_barcode' => __('Product Global Identifier', 'wp-seopress-pro'),
                'product_category' => __('Product category', 'wp-seopress-pro'),
                'product_stock' => __('Product availability', 'wp-seopress-pro'),
            ],
            'Custom taxonomy / Product attribute (WooCommerce)' => [
                'custom_taxonomy' => __('Select your custom taxonomy / product attribute', 'wp-seopress-pro'),
            ],
            'Custom fields' => [
                'custom_fields' => __('Select your custom field', 'wp-seopress-pro'),
            ],
        ];

        //Custom field
        $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_cf', true);

        $seopress_schemas_cf = '<select name="' . $post_meta_name . '_cf" class="cf">';

        foreach ($seopress_get_custom_fields as $value) {
            $seopress_schemas_cf .= '<option ' . selected($value, $post_meta_value, false) . ' value="' . $value . '">' . $value . '</option>';
        }

        $seopress_schemas_cf .= '</select>';

        //Custom taxonomy
        $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_tax', true);

        if (function_exists('seopress_get_taxonomies')) {
            $seopress_schemas_tax = '<select name="' . $post_meta_name . '_tax" class="tax">';

            $seopress_get_taxonomies = seopress_get_taxonomies();

            foreach ($seopress_get_taxonomies as $key => $value) {
                $seopress_schemas_tax .= '<option ' . selected($key, $post_meta_value, false) . ' value="' . $key . '">' . $key . '</option>';
            }
            $seopress_schemas_tax .= '</select>';
        }

        if (is_string($cases)) {
            $cases = [$cases];
        }

        foreach ($cases as $case) {
            //LB types list
            if ('lb' === $case) {
                $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_lb', true);

                $seopress_schemas_lb = '<select name="' . $post_meta_name . '_lb" class="lb">';

                foreach (seopress_lb_types_list() as $type_value => $type_i18n) {
                    $seopress_schemas_lb .= '<option ' . selected($type_value, $post_meta_value, false) . ' value="' . $type_value . '">' . __($type_i18n, 'wp-seopress-pro') . '</option>';
                }
                $seopress_schemas_lb .= '</select>';
            }

            switch ($case) {
                case 'default':
                    $seopress_schemas_mapping_case['Manual'] = [
                        'manual_global' => __('Manual text', 'wp-seopress-pro'),
                        'manual_single' => __('Manual text on each post', 'wp-seopress-pro'),
                    ];

                    $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_global', true);

                    $seopress_schemas_manual_global = '<input type="text" id="' . $post_meta_name . '_manual_global" name="' . $post_meta_name . '_manual_global" class="manual_global" placeholder="' . esc_html__('Enter a global value here', 'wp-seopress-pro') . '" aria-label="' . __('Manual value', 'wp-seopress-pro') . '" value="' . $post_meta_value . '" />';

                    break;
                case 'lb':
                    $seopress_schemas_mapping_case['Manual'] = [
                        'manual_global' => __('Manual text', 'wp-seopress-pro'),
                        'manual_single' => __('Manual text on each post', 'wp-seopress-pro'),
                    ];

                    $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_global', true);

                    $seopress_schemas_manual_global = '<input type="text" id="' . $post_meta_name . '_manual_global" name="' . $post_meta_name . '_manual_global" class="manual_global" placeholder="' . esc_html__('Enter a global value here', 'wp-seopress-pro') . '" aria-label="' . __('Manual value', 'wp-seopress-pro') . '" value="' . $post_meta_value . '" />';

                    //lb types case
                    $seopress_schemas_mapping_case['Local Business'] = [
                        'manual_lb_global' => __('Local Business type', 'wp-seopress-pro'),
                    ];

                    $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_lb_global', true);

                    break;
                case 'image':
                        $seopress_schemas_mapping_case = [
                            'Select an option' => ['none' => __('None', 'wp-seopress-pro')],
                            'Site Meta' => [
                                'knowledge_graph_logo' => __('Knowledge Graph logo (SEO > Social)', 'wp-seopress-pro'),
                            ],
                            'Post Meta' => [
                                'post_thumbnail' => __('Featured image / Product image', 'wp-seopress-pro'),
                                'post_author_picture' => __('Post author picture', 'wp-seopress-pro'),
                            ],
                            'Custom fields' => [
                                'custom_fields' => __('Select your custom field', 'wp-seopress-pro'),
                            ],
                            'Manual' => [
                                'manual_img_global' => __('Manual Image URL', 'wp-seopress-pro'),
                                'manual_img_library_global' => __('Manual Image from Library', 'wp-seopress-pro'),
                                'manual_img_single' => __('Manual text on each post', 'wp-seopress-pro'),
                            ],
                        ];

                        $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_img_global', true);

                        $seopress_schemas_manual_img_global = '<input type="text" id="' . $post_meta_name . '_manual_img_global" name="' . $post_meta_name . '_manual_img_global" class="manual_img_global" placeholder="' . esc_html__('Enter a global value here', 'wp-seopress-pro') . '" aria-label="' . __('Manual Image URL', 'wp-seopress-pro') . '" value="' . $post_meta_value . '" />';

                        $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_img_library_global', true);
                        $post_meta_value2 = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_img_library_global_width', true);
                        $post_meta_value3 = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_img_library_global_height', true);

                        $seopress_schemas_manual_img_library_global = '<input type="text" id="' . $post_meta_name . '_manual_img_library_global" name="' . $post_meta_name . '_manual_img_library_global" class="manual_img_library_global" placeholder="' . esc_html__('Select your global image from the media library', 'wp-seopress-pro') . '" aria-label="' . __('Manual Image URL', 'wp-seopress-pro') . '" value="' . $post_meta_value . '" />

						<input id="' . $post_meta_name . '_manual_img_library_global_width" type="hidden" name="' . $post_meta_name . '_manual_img_library_global_width" class="manual_img_library_global_width" value="' . $post_meta_value2 . '" />

						<input id="' . $post_meta_name . '_manual_img_library_global_height" type="hidden" name="' . $post_meta_name . '_manual_img_library_global_height" class="manual_img_library_global_height" value="' . $post_meta_value3 . '" />

						<input id="' . $post_meta_name . '_manual_img_library_global_btn" class="btn btnSecondary manual_img_library_global" type="button" value="' . __('Upload an Image', 'wp-seopress-pro') . '" />';

                    break;
                case 'events':
                        //Events Calendar
                        if (is_plugin_active('the-events-calendar/the-events-calendar.php')) {
                            $seopress_schemas_mapping_case['Events Calendar'] = [
                                'events_start_date' => __('Start date', 'wp-seopress-pro'),
                                'events_start_date_timezone' => __('Timezone start date', 'wp-seopress-pro'),
                                'events_start_time' => __('Start time', 'wp-seopress-pro'),
                                'events_end_date' => __('End date', 'wp-seopress-pro'),
                                'events_end_time' => __('End time', 'wp-seopress-pro'),
                                'events_location_name' => __('Event location name', 'wp-seopress-pro'),
                                'events_location_address' => __('Event location address', 'wp-seopress-pro'),
                                'events_website' => __('Event website', 'wp-seopress-pro'),
                                'events_cost' => __('Event cost', 'wp-seopress-pro'),
                                'events_currency' => __('Event currency', 'wp-seopress-pro'),
                            ];
                        }

                    break;
                case 'date':
                        //date case
                        $seopress_schemas_mapping_case['Manual'] = [
                            'manual_date_global' => __('Manual date', 'wp-seopress-pro'),
                            'manual_date_single' => __('Manual date on each post', 'wp-seopress-pro'),
                        ];

                        $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_date_global', true);

                        $seopress_schemas_manual_date_global = '<input type="text" class="seopress-date-picker manual_date_global" autocomplete="false" name="' . $post_meta_name . '_manual_date_global" class="manual_global" placeholder="' . esc_html__('Eg: YYYY-MM-DD', 'wp-seopress-pro') . '" aria-label="' . __('Global date', 'wp-seopress-pro') . '" value="' . $post_meta_value . '" />';
                    break;
                case 'time':
                        //time case
                        $seopress_schemas_mapping_case['Manual'] = [
                            'manual_time_global' => __('Manual time', 'wp-seopress-pro'),
                            'manual_time_single' => __('Manual time on each post', 'wp-seopress-pro'),
                        ];

                        $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_time_global', true);

                        $seopress_schemas_manual_time_global = '<input type="time" step="2" placeholder="' . __('HH:MM', 'wp-seopress-pro') . '" id="' . $post_meta_name . '_manual_time_global" name="' . $post_meta_name . '_manual_time_global" class="manual_time_global" aria-label="' . __('Time', 'wp-seopress-pro') . '" value="' . $post_meta_value . '" />';
                    break;
                case 'rating':
                        //rating case
                        $seopress_schemas_mapping_case['Manual'] = [
                            'manual_rating_global' => __('Manual rating', 'wp-seopress-pro'),
                            'manual_rating_single' => __('Manual rating on each post', 'wp-seopress-pro'),
                        ];

                        $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_rating_global', true);

                        $seopress_schemas_manual_rating_global = '<input type="number" id="' . $post_meta_name . '_manual_rating_global" name="' . $post_meta_name . '_manual_rating_global" min="1" step="0.1" class="manual_rating_global" aria-label="' . __('Rating', 'wp-seopress-pro') . '" value="' . $post_meta_value . '" />';
                    break;
                case 'custom':
                        //custom case
                        $seopress_schemas_mapping_case = [];
                        $seopress_schemas_mapping_case['custom'] = [
                            'manual_custom_global' => __('Manual custom schema', 'wp-seopress-pro'),
                            'manual_custom_single' => __('Manual custom schema on each post', 'wp-seopress-pro'),
                        ];

                        $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name . '_manual_custom_global', true);

                        $seopress_schemas_manual_custom_global = '<textarea rows="25" id="' . $post_meta_name . '_manual_custom_global" name="' . $post_meta_name . '_manual_custom_global" class="manual_custom_global" aria-label="' . __('Custom schema', 'wp-seopress-pro') . '" value="' . htmlspecialchars($post_meta_value) . '">' . htmlspecialchars($post_meta_value) . '</textarea>';
                    break;
            }
        }

        $post_meta_value = get_post_meta($post->ID, '_' . $post_meta_name, true);

        $seopress_schemas_mapping_case = apply_filters('seopress_schemas_mapping_select', $seopress_schemas_mapping_case);

        $html = '<select name="' . $post_meta_name . '" class="dyn">';
        foreach ($seopress_schemas_mapping_case as $key => $value) {
            $html .= '<optgroup label="' . $key . '">';
            foreach ($value as $_key => $_value) {
                $html .= '<option ' . selected($_key, $post_meta_value, false) . ' value="' . $_key . '">' . __($_value, 'wp-seopress-pro') . '</option>';
            }
            $html .= '</optgroup>';
        }
        $html .= '</select>';

        if (isset($seopress_schemas_manual_global)) {
            $html .= $seopress_schemas_manual_global;
        }
        if (isset($seopress_schemas_manual_img_global)) {
            $html .= $seopress_schemas_manual_img_global;
        }
        if (isset($seopress_schemas_manual_img_library_global)) {
            $html .= $seopress_schemas_manual_img_library_global;
        }
        if (isset($seopress_schemas_manual_date_global)) {
            $html .= $seopress_schemas_manual_date_global;
        }
        if (isset($seopress_schemas_manual_time_global)) {
            $html .= $seopress_schemas_manual_time_global;
        }
        if (isset($seopress_schemas_manual_rating_global)) {
            $html .= $seopress_schemas_manual_rating_global;
        }
        if (isset($seopress_schemas_cf) && 'custom' != $case) {
            $html .= $seopress_schemas_cf;
        }
        if (isset($seopress_schemas_tax) && 'custom' != $case) {
            $html .= $seopress_schemas_tax;
        }
        if (isset($seopress_schemas_lb) && 'custom' != $case) {
            $html .= $seopress_schemas_lb;
        }
        if (isset($seopress_schemas_manual_custom_global)) {
            $html .= $seopress_schemas_manual_custom_global;
        }

        return $html;
    }
    $docs = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';

    //Get datas
    $seopress_pro_rich_snippets_type = get_post_meta($post->ID, '_seopress_pro_rich_snippets_type', true);

    //Article
    $seopress_pro_rich_snippets_article_type = get_post_meta($post->ID, '_seopress_pro_rich_snippets_article_type', true);

    //Local Business
    $seopress_pro_rich_snippets_lb_opening_hours = get_post_meta($post->ID, '_seopress_pro_rich_snippets_lb_opening_hours', false); ?>
<tr id="term-seopress" class="form-field">
    <td>
        <div id="seopress_pro_cpt" class="seopress-your-schema">
            <div class="inside">
                <div id="seopress-your-schema">
                    <div class="box-left">
                        <div class="wrap-rich-snippets-type schema-steps">
                            <label for="seopress_pro_rich_snippets_type_meta"><?php _e('Select your data type:', 'wp-seopress-pro'); ?></label>
                            <select id="seopress_pro_rich_snippets_type" name="seopress_pro_rich_snippets_type">
                                <option <?php echo selected('none', $seopress_pro_rich_snippets_type, false); ?>
                                    value="none"><?php _e('None', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('articles', $seopress_pro_rich_snippets_type, false); ?>
                                    value="articles"><?php _e('Article (WebPage)', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('localbusiness', $seopress_pro_rich_snippets_type, false); ?>
                                    value="localbusiness"><?php _e('Local Business', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('faq', $seopress_pro_rich_snippets_type, false); ?>
                                    value="faq"><?php _e('FAQ', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('courses', $seopress_pro_rich_snippets_type, false); ?>
                                    value="courses"><?php _e('Course', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('recipes', $seopress_pro_rich_snippets_type, false); ?>
                                    value="recipes"><?php _e('Recipe', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('jobs', $seopress_pro_rich_snippets_type, false); ?>
                                    value="jobs"><?php _e('Job', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('videos', $seopress_pro_rich_snippets_type, false); ?>
                                    value="videos"><?php _e('Video', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('events', $seopress_pro_rich_snippets_type, false); ?>
                                    value="events"><?php _e('Event', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('products', $seopress_pro_rich_snippets_type, false); ?>
                                    value="products"><?php _e('Product', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('services', $seopress_pro_rich_snippets_type, false); ?>
                                    value="services"><?php _e('Service', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('softwareapp', $seopress_pro_rich_snippets_type, false); ?>
                                    value="softwareapp"><?php _e('Software Application ', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('review', $seopress_pro_rich_snippets_type, false); ?>
                                    value="review"><?php _e('Review', 'wp-seopress-pro'); ?>
                                </option>
                                <option <?php echo selected('custom', $seopress_pro_rich_snippets_type, false); ?>
                                    value="custom"><?php _e('Custom', 'wp-seopress-pro'); ?>
                                </option>
                            </select>
                        </div>
                        <div class="wrap-rich-snippets-rules schema-steps">
                            <p>
                                <label for="seopress_pro_rich_snippets_rules_meta"><?php _e('Show this schema if your singular post, page or post type has:', 'wp-seopress-pro'); ?></label>
                                <?php
    $_id_name_for = 'seopress_pro_rich_snippets_rules';
    $snippets_rules = get_post_meta($post->ID, '_seopress_pro_rich_snippets_rules', true);
    $_available_rules_filters = seopress_get_schemas_filters();
    $_available_rules_conditions = seopress_get_schemas_conditions();
    // Retrocompat < 3.8.2
    if ( ! is_array($snippets_rules) || empty($snippets_rules)) {
        $snippets_rules = seopress_get_default_schemas_rules($snippets_rules);
    }
    $_g = 0;
    foreach ($snippets_rules as $_group => $_rules) {
        $_group = $_g++;
        $_n = 0;
        echo '<div data-group="' . $_group . '">';
        foreach ($_rules as $_index => $_rule) {
            $_index = $_n++;

            echo '<p data-group="' . $_group . '">';

            // Filters
            echo "\t<select id=\"{$_id_name_for}[g{$_group}][i{$_index}][filter]\" name=\"{$_id_name_for}[g{$_group}][i{$_index}][filter]\" class=\"small-text\">\n";
            foreach ($_available_rules_filters as $_filter => $_filter_label) {
                echo "\t\t" . '<option value="' . $_filter . '" ' . selected($_rule['filter'], $_filter, false) . '>' . $_filter_label . '</option>' . "\n";
            }
            echo '</select>';

            // Condition.
            echo "\t<select id=\"{$_id_name_for}[g{$_group}][i{$_index}][cond]\" name=\"{$_id_name_for}[g{$_group}][i{$_index}][cond]\" class=\"small-text\">\n";
            foreach ($_available_rules_conditions as $_cond => $_cond_label) {
                echo "\t\t" . '<option value="' . $_cond . '" ' . selected($_rule['cond'], $_cond, false) . '>' . $_cond_label . '</option>' . "\n";
            }
            echo '</select>';

            // CPT
            $class = 'post_type' === $_rule['filter'] ? '' : 'hidden';
            echo "\t<select id=\"{$_id_name_for}[g{$_group}][i{$_index}][cpt]\" name=\"{$_id_name_for}[g{$_group}][i{$_index}][cpt]\" class=\"{$class}\">\n";
            foreach (seopress_get_post_types() as $_cpt_slug => $_post_type_obj) {
                echo "\t\t" . '<option ' . selected($_rule['cpt'], $_cpt_slug, false) . ' value="' . $_cpt_slug . '">' . $_post_type_obj->labels->name . '</option>' . "\n";
            }
            echo '</select>';

            // TAXO
            $class = 'taxonomy' === $_rule['filter'] ? '' : 'hidden';
            echo "\t<select id=\"{$_id_name_for}[g{$_group}][i{$_index}][taxo]\" name=\"{$_id_name_for}[g{$_group}][i{$_index}][taxo]\" class=\"{$class}\">\n";
            foreach (seopress_get_taxonomies(true) as $_tax_slug => $_tax) {
                echo "\t\t" . '<optgroup label="' . $_tax->label . '">' . "\n";
                if (isset($_tax->terms)) { // Free version is up to date.
                    foreach ($_tax->terms as $_term) {
                        echo "\t\t" . '<option ' . selected($_rule['taxo'], $_term->term_id, false) . ' value="' . $_term->term_id . '">' . esc_html($_term->name) . '</option>' . "\n";
                    }
                }
                echo '</optgroup>';
            }
            echo '</select>';

            // INPUT
            $class = 'postId' === $_rule['filter'] ? '' : 'hidden';
            $valuePostId = isset($_rule['postId']) ? $_rule['postId'] : '';
            echo "\t<input id=\"{$_id_name_for}[g{$_group}][i{$_index}][postId]\" name=\"{$_id_name_for}[g{$_group}][i{$_index}][postId]\" class=\"{$class}\" value=\"{$valuePostId}\" />\n";

            // Buttons
            echo ' <span class="dashicons dashicons-plus-alt ' . $_id_name_for . '_and" data-group="' . $_group . '"></span>';
            echo ' <span class="hidden dashicons dashicons-no-alt ' . $_id_name_for . '_del" data-group="' . $_group . '"></span>';

            echo '</p>';
        }
        echo '</div>';
        echo '<p class="separat_or"><strong>' . __('or', 'wp-seopress-pro') . '</strong></p>';
    } ?>
                            <p>
                                <button type="button" class="btn btnSecondary"
                                    id="<?php echo $_id_name_for; ?>_add">
                                    <?php _e('Add a rule', 'wp-seopress-pro'); ?>
                                </button>
                            </p>
                        </div>
                        <p>
                            <label><?php _e('Map all schema properties to a value:', 'wp-seopress-pro'); ?></label>
                        </p>

                        <?php
                            require_once dirname(__FILE__) . '/automatic/Article.php';
    require_once dirname(__FILE__) . '/automatic/LocalBusiness.php';
    require_once dirname(__FILE__) . '/automatic/Faq.php';
    require_once dirname(__FILE__) . '/automatic/Course.php';
    require_once dirname(__FILE__) . '/automatic/Recipe.php';
    require_once dirname(__FILE__) . '/automatic/Job.php';
    require_once dirname(__FILE__) . '/automatic/Video.php';
    require_once dirname(__FILE__) . '/automatic/Event.php';
    require_once dirname(__FILE__) . '/automatic/Product.php';
    require_once dirname(__FILE__) . '/automatic/SoftwareApp.php';
    require_once dirname(__FILE__) . '/automatic/Service.php';
    require_once dirname(__FILE__) . '/automatic/Review.php';
    require_once dirname(__FILE__) . '/automatic/Custom.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </td>
</tr>

<?php

    global $pagenow;

    if (isset($pagenow) && $pagenow === 'post-new.php'):

?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const $ = jQuery

        $("#seopress_pro_rich_snippets_type").on("change", function(e) {
            const val = $(this).val()
            if (!val === "products") {
                return;
            }

            $("select[name='seopress_pro_rich_snippets_product_name']").val("post_title")
            $("select[name='seopress_pro_rich_snippets_product_description']").val("post_excerpt")
            $("select[name='seopress_pro_rich_snippets_product_img']").val("post_thumbnail")
            $("select[name='seopress_pro_rich_snippets_product_price']").val(
                "product_regular_price")
            $("select[name='seopress_pro_rich_snippets_product_sku']").val("product_sku")
            $("select[name='seopress_pro_rich_snippets_product_price_valid_date']").val(
                "product_date_to")
            $("select[name='seopress_pro_rich_snippets_product_global_ids']").val(
                "product_barcode_type")
            $("select[name='seopress_pro_rich_snippets_product_global_ids_value']").val(
                "product_barcode")
            $("select[name='seopress_pro_rich_snippets_product_availability']").val("product_stock")
        })
    })
</script>
<?php
    endif;
}

add_action('save_post', 'seopress_schemas_save_metabox', 10, 2);
function seopress_schemas_save_metabox($post_id, $post) {
    //Nonce
    if ( ! isset($_POST['seopress_schemas_cpt_nonce']) || ! wp_verify_nonce(
        $_POST['seopress_schemas_cpt_nonce'],
        plugin_basename(__FILE__)
    )) {
        return $post_id;
    }

    //Post type object
    $post_type = get_post_type_object($post->post_type);

    //Check permission
    if ( ! current_user_can('edit_schemas', $post_id)) {
        return $post_id;
    }

    if (isset($_POST['seopress_pro_rich_snippets_rules'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_rules', $_POST['seopress_pro_rich_snippets_rules']);
    }
    if (isset($_POST['seopress_pro_rich_snippets_type'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_type', esc_html($_POST['seopress_pro_rich_snippets_type']));
    }
    //Article
    if (isset($_POST['seopress_pro_rich_snippets_article_type'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_type',
            esc_html($_POST['seopress_pro_rich_snippets_article_type'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_title'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_title',
            esc_html($_POST['seopress_pro_rich_snippets_article_title'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_title_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_title_cf',
            esc_html($_POST['seopress_pro_rich_snippets_article_title_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_title_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_title_tax',
            esc_html($_POST['seopress_pro_rich_snippets_article_title_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_title_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_title_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_article_title_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_desc'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_desc',
            esc_html($_POST['seopress_pro_rich_snippets_article_desc'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_desc_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_desc_cf',
            esc_html($_POST['seopress_pro_rich_snippets_article_desc_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_desc_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_desc_tax',
            esc_html($_POST['seopress_pro_rich_snippets_article_desc_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_desc_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_desc_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_article_desc_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_author'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_author',
            esc_html($_POST['seopress_pro_rich_snippets_article_author'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_author_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_author_cf',
            esc_html($_POST['seopress_pro_rich_snippets_article_author_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_author_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_author_tax',
            esc_html($_POST['seopress_pro_rich_snippets_article_author_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_author_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_author_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_article_author_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_img'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_img',
            esc_html($_POST['seopress_pro_rich_snippets_article_img'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_img_manual_img_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_img_manual_img_global',
            esc_html($_POST['seopress_pro_rich_snippets_article_img_manual_img_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_img_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_img_cf',
            esc_html($_POST['seopress_pro_rich_snippets_article_img_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_img_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_img_tax',
            esc_html($_POST['seopress_pro_rich_snippets_article_img_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_img_manual_img_library_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_img_manual_img_library_global',
            esc_html($_POST['seopress_pro_rich_snippets_article_img_manual_img_library_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_img_manual_img_library_global_width'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_img_manual_img_library_global_width',
            esc_html($_POST['seopress_pro_rich_snippets_article_img_manual_img_library_global_width'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_img_manual_img_library_global_height'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_img_manual_img_library_global_height',
            esc_html($_POST['seopress_pro_rich_snippets_article_img_manual_img_library_global_height'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_start_date'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_start_date',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_start_date'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_start_date_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_start_date_cf',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_start_date_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_start_date_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_start_date_tax',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_start_date_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_start_date_manual_date_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_start_date_manual_date_global',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_start_date_manual_date_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_start_time'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_start_time',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_start_time'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_start_time_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_start_time_cf',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_start_time_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_start_time_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_start_time_tax',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_start_time_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_start_time_manual_time_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_start_time_manual_time_global',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_start_time_manual_time_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_end_date'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_end_date',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_end_date'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_end_date_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_end_date_cf',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_end_date_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_end_date_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_end_date_tax',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_end_date_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_end_date_manual_date_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_end_date_manual_date_global',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_end_date_manual_date_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_end_time'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_end_time',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_end_time'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_end_time_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_end_time_cf',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_end_time_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_end_time_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_end_time_tax',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_end_time_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_coverage_end_time_manual_time_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_coverage_end_time_manual_time_global',
            esc_html($_POST['seopress_pro_rich_snippets_article_coverage_end_time_manual_time_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_speakable'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_speakable',
            esc_html($_POST['seopress_pro_rich_snippets_article_speakable'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_speakable_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_speakable_cf',
            esc_html($_POST['seopress_pro_rich_snippets_article_speakable_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_speakable_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_speakable_tax',
            esc_html($_POST['seopress_pro_rich_snippets_article_speakable_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_article_speakable_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_article_speakable_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_article_speakable_manual_global'])
        );
    }

    //Local Business
    if (isset($_POST['seopress_pro_rich_snippets_lb_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_name',
            esc_html($_POST['seopress_pro_rich_snippets_lb_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_type'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_type',
            esc_html($_POST['seopress_pro_rich_snippets_lb_type'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_type_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_type_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_type_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_type_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_type_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_type_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_type_lb'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_type_lb',
            esc_html($_POST['seopress_pro_rich_snippets_lb_type_lb'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_type_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_type_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_type_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_img'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_img', esc_html($_POST['seopress_pro_rich_snippets_lb_img']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_img_manual_img_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_img_manual_img_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_img_manual_img_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_img_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_img_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_img_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_img_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_img_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_img_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_img_manual_img_library_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_img_manual_img_library_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_img_manual_img_library_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_img_manual_img_library_global_width'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_img_manual_img_library_global_width',
            esc_html($_POST['seopress_pro_rich_snippets_lb_img_manual_img_library_global_width'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_img_manual_img_library_global_height'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_img_manual_img_library_global_height',
            esc_html($_POST['seopress_pro_rich_snippets_lb_img_manual_img_library_global_height'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_street_addr'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_street_addr',
            esc_html($_POST['seopress_pro_rich_snippets_lb_street_addr'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_street_addr_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_street_addr_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_street_addr_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_street_addr_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_street_addr_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_street_addr_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_street_addr_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_street_addr_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_street_addr_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_city'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_city',
            esc_html($_POST['seopress_pro_rich_snippets_lb_city'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_city_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_city_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_city_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_city_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_city_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_city_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_city_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_city_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_city_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_state'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_state',
            esc_html($_POST['seopress_pro_rich_snippets_lb_state'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_state_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_state_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_state_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_state_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_state_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_state_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_state_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_state_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_state_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_pc'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_pc', esc_html($_POST['seopress_pro_rich_snippets_lb_pc']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_pc_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_pc_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_pc_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_pc_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_pc_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_pc_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_pc_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_pc_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_pc_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_country'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_country',
            esc_html($_POST['seopress_pro_rich_snippets_lb_country'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_country_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_country_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_country_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_country_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_country_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_country_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_country_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_country_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_country_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_lat'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_lat', esc_html($_POST['seopress_pro_rich_snippets_lb_lat']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_lat_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_lat_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_lat_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_lat_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_lat_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_lat_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_lat_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_lat_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_lat_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_lon'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_lon', esc_html($_POST['seopress_pro_rich_snippets_lb_lon']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_lon_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_lon_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_lon_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_lon_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_lon_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_lon_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_lon_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_lon_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_lon_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_website'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_website',
            esc_html($_POST['seopress_pro_rich_snippets_lb_website'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_website_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_website_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_website_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_website_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_website_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_website_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_website_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_website_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_website_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_tel'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_lb_tel', esc_html($_POST['seopress_pro_rich_snippets_lb_tel']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_tel_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_tel_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_tel_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_tel_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_tel_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_tel_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_tel_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_tel_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_tel_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_price'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_price',
            esc_html($_POST['seopress_pro_rich_snippets_lb_price'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_price_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_price_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_price_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_price_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_price_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_price_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_price_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_price_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_price_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_serves_cuisine'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_serves_cuisine',
            esc_html($_POST['seopress_pro_rich_snippets_lb_serves_cuisine'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_serves_cuisine_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_serves_cuisine_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_serves_cuisine_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_serves_cuisine_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_serves_cuisine_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_serves_cuisine_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_serves_cuisine_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_serves_cuisine_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_serves_cuisine_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_menu'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_menu',
            esc_html($_POST['seopress_pro_rich_snippets_lb_menu'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_menu_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_menu_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_menu_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_menu_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_menu_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_menu_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_menu_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_menu_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_menu_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_accepts_reservations'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_accepts_reservations',
            esc_html($_POST['seopress_pro_rich_snippets_lb_accepts_reservations'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_accepts_reservations_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_accepts_reservations_cf',
            esc_html($_POST['seopress_pro_rich_snippets_lb_accepts_reservations_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_accepts_reservations_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_accepts_reservations_tax',
            esc_html($_POST['seopress_pro_rich_snippets_lb_accepts_reservations_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_accepts_reservations_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_accepts_reservations_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_lb_accepts_reservations_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_lb_opening_hours'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_lb_opening_hours',
            $_POST['seopress_pro_rich_snippets_lb_opening_hours']
        );
    }
    //FAQ
    if (isset($_POST['seopress_pro_rich_snippets_faq_q'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_faq_q', esc_html($_POST['seopress_pro_rich_snippets_faq_q']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_faq_q_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_faq_q_cf',
            esc_html($_POST['seopress_pro_rich_snippets_faq_q_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_faq_q_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_faq_q_tax',
            esc_html($_POST['seopress_pro_rich_snippets_faq_q_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_faq_q_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_faq_q_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_faq_q_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_faq_a'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_faq_a', esc_html($_POST['seopress_pro_rich_snippets_faq_a']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_faq_a_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_faq_a_cf',
            esc_html($_POST['seopress_pro_rich_snippets_faq_a_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_faq_a_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_faq_a_tax',
            esc_html($_POST['seopress_pro_rich_snippets_faq_a_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_faq_a_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_faq_a_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_faq_a_manual_global'])
        );
    }
    //Course
    if (isset($_POST['seopress_pro_rich_snippets_courses_title'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_title',
            esc_html($_POST['seopress_pro_rich_snippets_courses_title'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_title_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_title_cf',
            esc_html($_POST['seopress_pro_rich_snippets_courses_title_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_title_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_title_tax',
            esc_html($_POST['seopress_pro_rich_snippets_courses_title_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_title_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_title_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_courses_title_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_desc'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_desc',
            esc_html($_POST['seopress_pro_rich_snippets_courses_desc'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_desc_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_desc_cf',
            esc_html($_POST['seopress_pro_rich_snippets_courses_desc_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_desc_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_desc_tax',
            esc_html($_POST['seopress_pro_rich_snippets_courses_desc_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_desc_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_desc_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_courses_desc_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_school'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_school',
            esc_html($_POST['seopress_pro_rich_snippets_courses_school'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_school_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_school_cf',
            esc_html($_POST['seopress_pro_rich_snippets_courses_school_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_school_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_school_tax',
            esc_html($_POST['seopress_pro_rich_snippets_courses_school_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_school_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_school_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_courses_school_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_website'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_website',
            esc_html($_POST['seopress_pro_rich_snippets_courses_website'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_website_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_website_cf',
            esc_html($_POST['seopress_pro_rich_snippets_courses_website_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_website_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_website_tax',
            esc_html($_POST['seopress_pro_rich_snippets_courses_website_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_courses_website_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_courses_website_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_courses_website_manual_global'])
        );
    }
    //Recipe
    if (isset($_POST['seopress_pro_rich_snippets_recipes_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_name',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_desc'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_desc',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_desc'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_desc_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_desc_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_desc_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_desc_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_desc_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_desc_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_desc_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_desc_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_desc_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cat'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cat',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cat'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cat_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cat_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cat_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cat_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cat_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cat_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cat_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cat_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cat_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_img'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_img',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_img'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_img_manual_img_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_img_manual_img_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_img_manual_img_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_img_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_img_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_img_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_img_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_img_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_img_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_img_manual_img_library_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_img_manual_img_library_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_img_manual_img_library_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_img_manual_img_library_global_width'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_img_manual_img_library_global_width',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_img_manual_img_library_global_width'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_img_manual_img_library_global_height'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_img_manual_img_library_global_height',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_img_manual_img_library_global_height'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_video'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_video',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_video'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_video_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_video_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_video_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_video_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_video_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_video_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_video_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_video_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_video_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_prep_time'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_prep_time',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_prep_time'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_prep_time_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_prep_time_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_prep_time_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_prep_time_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_prep_time_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_prep_time_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_prep_time_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_prep_time_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_prep_time_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cook_time'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cook_time',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cook_time'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cook_time_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cook_time_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cook_time_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cook_time_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cook_time_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cook_time_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cook_time_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cook_time_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cook_time_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_calories'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_calories',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_calories'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_calories_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_calories_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_calories_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_calories_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_calories_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_calories_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_calories_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_calories_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_calories_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_yield'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_yield',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_yield'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_yield_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_yield_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_yield_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_yield_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_yield_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_yield_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_yield_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_yield_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_yield_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_keywords'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_keywords',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_keywords'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_keywords_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_keywords_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_keywords_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_keywords_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_keywords_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_keywords_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_keywords_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_keywords_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_keywords_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cuisine'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cuisine',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cuisine'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cuisine_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cuisine_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cuisine_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cuisine_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cuisine_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cuisine_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_cuisine_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_cuisine_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_cuisine_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_ingredient'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_ingredient',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_ingredient'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_ingredient_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_ingredient_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_ingredient_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_ingredient_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_ingredient_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_ingredient_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_ingredient_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_ingredient_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_ingredient_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_instructions'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_instructions',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_instructions'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_instructions_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_instructions_cf',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_instructions_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_instructions_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_instructions_tax',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_instructions_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_recipes_instructions_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_recipes_instructions_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_recipes_instructions_manual_global'])
        );
    }
    //Job
    if (isset($_POST['seopress_pro_rich_snippets_jobs_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_name',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_desc'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_desc',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_desc'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_desc_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_desc_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_desc_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_desc_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_desc_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_desc_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_desc_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_desc_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_desc_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_date_posted'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_date_posted',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_date_posted'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_date_posted_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_date_posted_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_date_posted_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_date_posted_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_date_posted_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_date_posted_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_date_posted_manual_date_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_date_posted_manual_date_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_date_posted_manual_date_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_valid_through'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_valid_through',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_valid_through'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_valid_through_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_valid_through_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_valid_through_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_valid_through_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_valid_through_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_valid_through_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_valid_through_manual_date_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_valid_through_manual_date_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_valid_through_manual_date_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_employment_type'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_employment_type',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_employment_type'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_employment_type_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_employment_type_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_employment_type_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_employment_type_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_employment_type_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_employment_type_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_employment_type_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_employment_type_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_employment_type_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_identifier_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_identifier_name',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_identifier_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_identifier_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_identifier_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_identifier_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_identifier_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_identifier_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_identifier_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_identifier_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_identifier_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_identifier_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_identifier_value'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_identifier_value',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_identifier_value'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_identifier_value_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_identifier_value_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_identifier_value_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_identifier_value_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_identifier_value_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_identifier_value_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_identifier_value_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_identifier_value_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_identifier_value_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_organization'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_organization',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_organization'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_organization_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_organization_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_organization_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_organization_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_organization_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_organization_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_organization_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_organization_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_organization_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_same_as'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_same_as',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_same_as'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_same_as_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_same_as_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_same_as_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_same_as_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_same_as_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_same_as_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_same_as_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_same_as_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_same_as_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_logo'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_logo',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_logo'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_logo_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_logo_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_library_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_library_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_library_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_library_global_width'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_library_global_width',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_library_global_width'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_library_global_height'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_library_global_height',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_hiring_logo_manual_img_library_global_height'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_street'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_street',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_street'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_street_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_street_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_street_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_street_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_street_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_street_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_street_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_street_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_street_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_locality'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_locality',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_locality'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_locality_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_locality_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_locality_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_locality_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_locality_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_locality_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_locality_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_locality_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_locality_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_region'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_region',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_region'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_region_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_region_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_region_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_region_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_region_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_region_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_address_region_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_address_region_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_address_region_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_postal_code'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_postal_code',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_postal_code'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_postal_code_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_postal_code_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_postal_code_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_postal_code_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_postal_code_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_postal_code_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_postal_code_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_postal_code_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_postal_code_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_country'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_country',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_country'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_country_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_country_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_country_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_country_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_country_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_country_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_country_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_country_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_country_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_remote'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_remote',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_remote'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_remote_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_remote_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_remote_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_remote_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_remote_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_remote_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_remote_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_remote_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_remote_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_direct_apply'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_direct_apply',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_direct_apply'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_direct_apply_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_direct_apply_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_direct_apply_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_direct_apply_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_direct_apply_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_direct_apply_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_direct_apply_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_direct_apply_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_direct_apply_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_currency'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_currency',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_currency'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_currency_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_currency_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_currency_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_currency_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_currency_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_currency_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_currency_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_currency_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_currency_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_unit'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_unit',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_unit'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_unit_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_unit_cf',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_unit_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_unit_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_unit_tax',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_unit_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_jobs_salary_unit_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_jobs_salary_unit_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_jobs_salary_unit_manual_global'])
        );
    }
    //Video
    if (isset($_POST['seopress_pro_rich_snippets_videos_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_name',
            esc_html($_POST['seopress_pro_rich_snippets_videos_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_videos_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_videos_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_videos_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_description'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_description',
            esc_html($_POST['seopress_pro_rich_snippets_videos_description'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_description_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_description_cf',
            esc_html($_POST['seopress_pro_rich_snippets_videos_description_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_description_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_description_tax',
            esc_html($_POST['seopress_pro_rich_snippets_videos_description_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_description_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_description_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_videos_description_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_img'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_img',
            esc_html($_POST['seopress_pro_rich_snippets_videos_img'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_img_manual_img_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_img_manual_img_global',
            esc_html($_POST['seopress_pro_rich_snippets_videos_img_manual_img_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_img_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_img_cf',
            esc_html($_POST['seopress_pro_rich_snippets_videos_img_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_img_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_img_tax',
            esc_html($_POST['seopress_pro_rich_snippets_videos_img_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_img_manual_img_library_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_img_manual_img_library_global',
            esc_html($_POST['seopress_pro_rich_snippets_videos_img_manual_img_library_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_img_manual_img_library_global_width'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_img_manual_img_library_global_width',
            esc_html($_POST['seopress_pro_rich_snippets_videos_img_manual_img_library_global_width'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_img_manual_img_library_global_height'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_img_manual_img_library_global_height',
            esc_html($_POST['seopress_pro_rich_snippets_videos_img_manual_img_library_global_height'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_duration'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_duration',
            esc_html($_POST['seopress_pro_rich_snippets_videos_duration'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_duration_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_duration_cf',
            esc_html($_POST['seopress_pro_rich_snippets_videos_duration_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_duration_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_duration_tax',
            esc_html($_POST['seopress_pro_rich_snippets_videos_duration_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_duration_manual_time_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_duration_manual_time_global',
            esc_html($_POST['seopress_pro_rich_snippets_videos_duration_manual_time_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_url'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_url',
            esc_html($_POST['seopress_pro_rich_snippets_videos_url'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_url_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_url_cf',
            esc_html($_POST['seopress_pro_rich_snippets_videos_url_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_url_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_url_tax',
            esc_html($_POST['seopress_pro_rich_snippets_videos_url_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_videos_url_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_videos_url_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_videos_url_manual_global'])
        );
    }
    //Event
    if (isset($_POST['seopress_pro_rich_snippets_events_type'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_type',
            esc_html($_POST['seopress_pro_rich_snippets_events_type'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_type_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_type_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_type_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_type_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_type_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_type_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_type_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_type_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_type_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_name',
            esc_html($_POST['seopress_pro_rich_snippets_events_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_desc'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_desc',
            esc_html($_POST['seopress_pro_rich_snippets_events_desc'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_desc_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_desc_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_desc_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_desc_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_desc_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_desc_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_desc_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_desc_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_desc_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_img'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_img',
            esc_html($_POST['seopress_pro_rich_snippets_events_img'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_img_manual_img_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_img_manual_img_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_img_manual_img_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_img_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_img_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_img_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_img_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_img_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_img_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_img_manual_img_library_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_img_manual_img_library_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_img_manual_img_library_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_img_manual_img_library_global_width'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_img_manual_img_library_global_width',
            esc_html($_POST['seopress_pro_rich_snippets_events_img_manual_img_library_global_width'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_img_manual_img_library_global_height'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_img_manual_img_library_global_height',
            esc_html($_POST['seopress_pro_rich_snippets_events_img_manual_img_library_global_height'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_desc'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_desc',
            esc_html($_POST['seopress_pro_rich_snippets_events_desc'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_desc_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_desc_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_desc_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_desc_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_desc_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_desc_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_desc_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_desc_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_desc_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_date'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_date',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_date'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_date_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_date_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_date_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_date_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_date_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_date_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_date_manual_date_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_date_manual_date_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_date_manual_date_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_date_timezone'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_date_timezone',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_date_timezone'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_date_timezone_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_date_timezone_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_date_timezone_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_date_timezone_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_date_timezone_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_date_timezone_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_date_timezone_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_date_timezone_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_date_timezone_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_time'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_time',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_time'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_time_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_time_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_time_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_time_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_time_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_time_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_start_time_manual_time_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_start_time_manual_time_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_start_time_manual_time_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_end_date'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_end_date',
            esc_html($_POST['seopress_pro_rich_snippets_events_end_date'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_end_date_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_end_date_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_end_date_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_end_date_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_end_date_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_end_date_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_end_date_manual_date_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_end_date_manual_date_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_end_date_manual_date_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_end_time'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_end_time',
            esc_html($_POST['seopress_pro_rich_snippets_events_end_time'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_end_time_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_end_time_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_end_time_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_end_time_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_end_time_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_end_time_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_end_time_manual_time_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_end_time_manual_time_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_end_time_manual_time_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_previous_start_date'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_previous_start_date',
            esc_html($_POST['seopress_pro_rich_snippets_events_previous_start_date'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_previous_start_date_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_previous_start_date_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_previous_start_date_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_previous_start_date_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_previous_start_date_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_previous_start_date_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_previous_start_date_manual_date_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_previous_start_date_manual_date_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_previous_start_date_manual_date_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_previous_start_time'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_previous_start_time',
            esc_html($_POST['seopress_pro_rich_snippets_events_previous_start_time'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_previous_start_time_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_previous_start_time_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_previous_start_time_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_previous_start_time_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_previous_start_time_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_previous_start_time_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_previous_start_time_manual_time_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_previous_start_time_manual_time_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_previous_start_time_manual_time_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_name',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_url'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_url',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_url'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_url_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_url_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_url_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_url_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_url_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_url_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_url_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_url_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_url_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_address'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_address',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_address'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_address_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_address_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_address_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_address_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_address_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_address_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_location_address_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_location_address_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_location_address_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_name',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_cat'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_cat',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_cat'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_cat_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_cat_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_cat_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_cat_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_cat_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_cat_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_cat_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_cat_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_cat_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_price'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_price',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_price'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_price_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_price_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_price_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_price_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_price_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_price_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_price_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_price_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_price_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_price_currency'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_price_currency',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_price_currency'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_price_currency_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_price_currency_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_price_currency_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_price_currency_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_price_currency_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_price_currency_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_price_currency_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_price_currency_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_price_currency_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_availability'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_availability',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_availability'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_availability_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_availability_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_availability_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_availability_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_availability_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_availability_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_availability_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_availability_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_availability_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_valid_from_date'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_valid_from_date',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_valid_from_date'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_valid_from_date_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_valid_from_date_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_valid_from_date_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_valid_from_date_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_valid_from_date_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_valid_from_date_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_valid_from_date_manual_date_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_valid_from_date_manual_date_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_valid_from_date_manual_date_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_valid_from_time'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_valid_from_time',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_valid_from_time'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_valid_from_time_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_valid_from_time_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_valid_from_time_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_valid_from_time_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_valid_from_time_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_valid_from_time_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_valid_from_time_manual_time_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_valid_from_time_manual_time_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_valid_from_time_manual_time_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_url'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_url',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_url'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_url_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_url_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_url_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_url_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_url_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_url_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_offers_url_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_offers_url_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_offers_url_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_performer'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_performer',
            esc_html($_POST['seopress_pro_rich_snippets_events_performer'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_performer_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_performer_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_performer_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_performer_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_performer_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_performer_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_performer_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_performer_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_performer_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_organizer_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_organizer_name',
            esc_html($_POST['seopress_pro_rich_snippets_events_organizer_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_organizer_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_organizer_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_organizer_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_organizer_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_organizer_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_organizer_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_organizer_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_organizer_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_organizer_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_organizer_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_organizer_url',
            esc_html($_POST['seopress_pro_rich_snippets_events_organizer_url'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_organizer_url_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_organizer_url_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_organizer_url_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_organizer_url_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_organizer_url_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_organizer_url_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_organizer_url_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_organizer_url_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_organizer_url_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_status'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_status',
            esc_html($_POST['seopress_pro_rich_snippets_events_status'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_status_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_status_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_status_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_status_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_status_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_status_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_status_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_status_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_status_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_attendance_mode'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_attendance_mode',
            esc_html($_POST['seopress_pro_rich_snippets_events_attendance_mode'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_attendance_mode_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_attendance_mode_cf',
            esc_html($_POST['seopress_pro_rich_snippets_events_attendance_mode_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_attendance_mode_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_attendance_mode_tax',
            esc_html($_POST['seopress_pro_rich_snippets_events_attendance_mode_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_events_attendance_mode_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_events_attendance_mode_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_events_attendance_mode_manual_global'])
        );
    }
    //Product
    if (isset($_POST['seopress_pro_rich_snippets_product_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_name',
            esc_html($_POST['seopress_pro_rich_snippets_product_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_description'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_description',
            esc_html($_POST['seopress_pro_rich_snippets_product_description'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_description_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_description_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_description_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_description_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_description_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_description_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_description_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_description_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_description_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_img'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_img',
            esc_html($_POST['seopress_pro_rich_snippets_product_img'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_img_manual_img_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_img_manual_img_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_img_manual_img_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_img_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_img_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_img_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_img_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_img_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_img_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_img_manual_img_library_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_img_manual_img_library_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_img_manual_img_library_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_img_manual_img_library_global_width'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_img_manual_img_library_global_width',
            esc_html($_POST['seopress_pro_rich_snippets_product_img_manual_img_library_global_width'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_img_manual_img_library_global_height'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_img_manual_img_library_global_height',
            esc_html($_POST['seopress_pro_rich_snippets_product_img_manual_img_library_global_height'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price',
            esc_html($_POST['seopress_pro_rich_snippets_product_price'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_valid_date'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_valid_date',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_valid_date'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_valid_date_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_valid_date_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_valid_date_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_valid_date_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_valid_date_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_valid_date_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_valid_date_manual_date_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_valid_date_manual_date_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_valid_date_manual_date_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_sku'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_sku',
            esc_html($_POST['seopress_pro_rich_snippets_product_sku'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_sku_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_sku_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_sku_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_sku_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_sku_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_sku_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_sku_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_sku_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_sku_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_global_ids'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_global_ids',
            esc_html($_POST['seopress_pro_rich_snippets_product_global_ids'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_global_ids_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_global_ids_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_global_ids_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_global_ids_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_global_ids_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_global_ids_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_global_ids_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_global_ids_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_global_ids_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_global_ids_value'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_global_ids_value',
            esc_html($_POST['seopress_pro_rich_snippets_product_global_ids_value'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_global_ids_value_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_global_ids_value_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_global_ids_value_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_global_ids_value_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_global_ids_value_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_global_ids_value_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_global_ids_value_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_global_ids_value_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_global_ids_value_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_brand'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_brand',
            esc_html($_POST['seopress_pro_rich_snippets_product_brand'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_brand_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_brand_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_brand_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_brand_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_brand_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_brand_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_brand_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_brand_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_brand_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_currency'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_currency',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_currency'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_currency_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_currency_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_currency_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_currency_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_currency_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_currency_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_price_currency_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_price_currency_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_price_currency_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_condition'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_condition',
            esc_html($_POST['seopress_pro_rich_snippets_product_condition'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_condition_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_condition_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_condition_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_condition_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_condition_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_condition_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_condition_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_condition_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_condition_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_availability'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_availability',
            esc_html($_POST['seopress_pro_rich_snippets_product_availability'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_availability_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_availability_cf',
            esc_html($_POST['seopress_pro_rich_snippets_product_availability_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_availability_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_availability_tax',
            esc_html($_POST['seopress_pro_rich_snippets_product_availability_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_product_availability_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_product_availability_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_product_availability_manual_global'])
        );
    }
    //Software App
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_name',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_os'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_os',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_os'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_os_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_os_cf',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_os_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_os_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_os_tax',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_os_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_os_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_os_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_os_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_cat'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_cat',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_cat'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_cat_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_cat_cf',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_cat_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_cat_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_cat_tax',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_cat_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_cat_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_cat_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_cat_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_price'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_price',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_price'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_price_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_price_cf',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_price_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_price_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_price_tax',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_price_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_price_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_price_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_price_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_currency'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_currency',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_currency'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_currency_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_currency_cf',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_currency_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_currency_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_currency_tax',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_currency_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_currency_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_currency_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_currency_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_rating'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_rating',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_rating'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_rating_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_rating_cf',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_rating_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_rating_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_rating_tax',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_rating_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_rating_manual_rating_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_rating_manual_rating_global',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_rating_manual_rating_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_max_rating'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_max_rating',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_max_rating'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_max_rating_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_max_rating_cf',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_max_rating_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_max_rating_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_max_rating_tax',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_max_rating_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_softwareapp_max_rating_manual_rating_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_softwareapp_max_rating_manual_rating_global',
            esc_html($_POST['seopress_pro_rich_snippets_softwareapp_max_rating_manual_rating_global'])
        );
    }
    //Service
    if (isset($_POST['seopress_pro_rich_snippets_service_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_name',
            esc_html($_POST['seopress_pro_rich_snippets_service_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_type'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_type',
            esc_html($_POST['seopress_pro_rich_snippets_service_type'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_type_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_type_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_type_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_type_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_type_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_type_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_type_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_type_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_type_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_description'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_description',
            esc_html($_POST['seopress_pro_rich_snippets_service_description'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_description_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_description_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_description_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_description_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_description_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_description_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_description_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_description_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_description_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_img'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_img',
            esc_html($_POST['seopress_pro_rich_snippets_service_img'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_img_manual_img_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_img_manual_img_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_img_manual_img_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_img_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_img_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_img_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_img_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_img_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_img_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_img_manual_img_library_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_img_manual_img_library_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_img_manual_img_library_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_img_manual_img_library_global_width'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_img_manual_img_library_global_width',
            esc_html($_POST['seopress_pro_rich_snippets_service_img_manual_img_library_global_width'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_img_manual_img_library_global_height'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_img_manual_img_library_global_height',
            esc_html($_POST['seopress_pro_rich_snippets_service_img_manual_img_library_global_height'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_area'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_area',
            esc_html($_POST['seopress_pro_rich_snippets_service_area'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_area_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_area_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_area_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_area_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_area_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_area_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_area_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_area_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_area_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_provider_name'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_provider_name',
            esc_html($_POST['seopress_pro_rich_snippets_service_provider_name'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_provider_name_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_provider_name_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_provider_name_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_provider_name_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_provider_name_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_provider_name_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_provider_name_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_provider_name_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_provider_name_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lb_img'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lb_img',
            esc_html($_POST['seopress_pro_rich_snippets_service_lb_img'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lb_img_manual_img_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lb_img_manual_img_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_lb_img_manual_img_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lb_img_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lb_img_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_lb_img_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lb_img_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lb_img_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_lb_img_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lb_img_manual_img_library_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lb_img_manual_img_library_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_lb_img_manual_img_library_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lb_img_manual_img_library_global_width'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lb_img_manual_img_library_global_width',
            esc_html($_POST['seopress_pro_rich_snippets_service_lb_img_manual_img_library_global_width'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lb_img_manual_img_library_global_height'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lb_img_manual_img_library_global_height',
            esc_html($_POST['seopress_pro_rich_snippets_service_lb_img_manual_img_library_global_height'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_provider_mobility'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_provider_mobility',
            esc_html($_POST['seopress_pro_rich_snippets_service_provider_mobility'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_provider_mobility_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_provider_mobility_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_provider_mobility_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_provider_mobility_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_provider_mobility_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_provider_mobility_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_provider_mobility_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_provider_mobility_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_provider_mobility_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_slogan'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_slogan',
            esc_html($_POST['seopress_pro_rich_snippets_service_slogan'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_slogan_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_slogan_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_slogan_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_slogan_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_slogan_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_slogan_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_slogan_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_slogan_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_slogan_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_street_addr'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_street_addr',
            esc_html($_POST['seopress_pro_rich_snippets_service_street_addr'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_street_addr_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_street_addr_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_street_addr_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_street_addr_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_street_addr_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_street_addr_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_street_addr_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_street_addr_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_street_addr_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_city'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_city',
            esc_html($_POST['seopress_pro_rich_snippets_service_city'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_city_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_city_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_city_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_city_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_city_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_city_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_city_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_city_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_city_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_state'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_state',
            esc_html($_POST['seopress_pro_rich_snippets_service_state'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_state_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_state_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_state_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_state_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_state_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_state_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_state_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_state_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_state_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_pc'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_pc',
            esc_html($_POST['seopress_pro_rich_snippets_service_pc'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_pc_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_pc_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_pc_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_pc_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_pc_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_pc_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_pc_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_pc_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_pc_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_country'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_country',
            esc_html($_POST['seopress_pro_rich_snippets_service_country'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_country_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_country_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_country_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_country_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_country_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_country_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_country_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_country_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_country_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lat'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lat',
            esc_html($_POST['seopress_pro_rich_snippets_service_lat'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lat_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lat_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_lat_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lat_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lat_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_lat_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lat_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lat_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_lat_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lon'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lon',
            esc_html($_POST['seopress_pro_rich_snippets_service_lon'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lon_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lon_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_lon_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lon_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lon_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_lon_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_lon_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_lon_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_lon_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_tel'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_tel',
            esc_html($_POST['seopress_pro_rich_snippets_service_tel'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_tel_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_tel_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_tel_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_tel_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_tel_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_tel_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_tel_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_tel_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_tel_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_price'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_price',
            esc_html($_POST['seopress_pro_rich_snippets_service_price'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_price_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_price_cf',
            esc_html($_POST['seopress_pro_rich_snippets_service_price_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_price_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_price_tax',
            esc_html($_POST['seopress_pro_rich_snippets_service_price_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_service_price_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_service_price_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_service_price_manual_global'])
        );
    }
    //Review
    if (isset($_POST['seopress_pro_rich_snippets_review_item'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_item',
            esc_html($_POST['seopress_pro_rich_snippets_review_item'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_item_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_item_cf',
            esc_html($_POST['seopress_pro_rich_snippets_review_item_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_item_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_item_tax',
            esc_html($_POST['seopress_pro_rich_snippets_review_item_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_item_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_item_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_review_item_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_item_type'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_item_type',
            esc_html($_POST['seopress_pro_rich_snippets_review_item_type'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_item_type_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_item_type_cf',
            esc_html($_POST['seopress_pro_rich_snippets_review_item_type_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_item_type_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_item_type_tax',
            esc_html($_POST['seopress_pro_rich_snippets_review_item_type_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_item_type_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_item_type_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_review_item_type_manual_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_img'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_img',
            esc_html($_POST['seopress_pro_rich_snippets_review_img'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_img_manual_img_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_img_manual_img_global',
            esc_html($_POST['seopress_pro_rich_snippets_review_img_manual_img_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_img_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_img_cf',
            esc_html($_POST['seopress_pro_rich_snippets_review_img_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_img_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_img_tax',
            esc_html($_POST['seopress_pro_rich_snippets_review_img_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_img_manual_img_library_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_img_manual_img_library_global',
            esc_html($_POST['seopress_pro_rich_snippets_review_img_manual_img_library_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_img_manual_img_library_global_width'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_img_manual_img_library_global_width',
            esc_html($_POST['seopress_pro_rich_snippets_review_img_manual_img_library_global_width'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_img_manual_img_library_global_height'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_img_manual_img_library_global_height',
            esc_html($_POST['seopress_pro_rich_snippets_review_img_manual_img_library_global_height'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_rating'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_rating',
            esc_html($_POST['seopress_pro_rich_snippets_review_rating'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_rating_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_rating_cf',
            esc_html($_POST['seopress_pro_rich_snippets_review_rating_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_rating_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_rating_tax',
            esc_html($_POST['seopress_pro_rich_snippets_review_rating_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_rating_manual_rating_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_rating_manual_rating_global',
            esc_html($_POST['seopress_pro_rich_snippets_review_rating_manual_rating_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_max_rating'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_max_rating',
            esc_html($_POST['seopress_pro_rich_snippets_review_max_rating'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_max_rating_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_max_rating_cf',
            esc_html($_POST['seopress_pro_rich_snippets_review_max_rating_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_max_rating_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_max_rating_tax',
            esc_html($_POST['seopress_pro_rich_snippets_review_max_rating_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_max_rating_manual_rating_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_max_rating_manual_rating_global',
            esc_html($_POST['seopress_pro_rich_snippets_review_max_rating_manual_rating_global'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_body'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_body',
            esc_html($_POST['seopress_pro_rich_snippets_review_body'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_body_cf'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_body_cf',
            esc_html($_POST['seopress_pro_rich_snippets_review_body_cf'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_body_tax'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_body_tax',
            esc_html($_POST['seopress_pro_rich_snippets_review_body_tax'])
        );
    }
    if (isset($_POST['seopress_pro_rich_snippets_review_body_manual_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_review_body_manual_global',
            esc_html($_POST['seopress_pro_rich_snippets_review_body_manual_global'])
        );
    }
    //Custom
    if (isset($_POST['seopress_pro_rich_snippets_custom'])) {
        update_post_meta($post_id, '_seopress_pro_rich_snippets_custom', esc_html($_POST['seopress_pro_rich_snippets_custom']));
    }
    if (isset($_POST['seopress_pro_rich_snippets_custom_manual_custom_global'])) {
        update_post_meta(
            $post_id,
            '_seopress_pro_rich_snippets_custom_manual_custom_global',
            $_POST['seopress_pro_rich_snippets_custom_manual_custom_global']
        );
    }
}
