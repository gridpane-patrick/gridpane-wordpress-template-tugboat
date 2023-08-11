<?php
defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_404_disable_automatic_redirects_option() {
    $seopress_404_disable_automatic_redirects_option = get_option('seopress_pro_option_name');
    if ( ! empty($seopress_404_disable_automatic_redirects_option)) {
        foreach ($seopress_404_disable_automatic_redirects_option as $key => $seopress_404_disable_automatic_redirects_value) {
            $options[$key] = $seopress_404_disable_automatic_redirects_value;
        }
        if (isset($seopress_404_disable_automatic_redirects_option['seopress_404_disable_automatic_redirects'])) {
            return $seopress_404_disable_automatic_redirects_option['seopress_404_disable_automatic_redirects'];
        }
    }
}

if ('1' === seopress_404_disable_automatic_redirects_option()) {
    add_filter('seopress_post_automatic_redirect', '__return_false');
}

function seopress_get_option_post_need_redirects() {
    return get_option('seopress_can_post_redirect');
}

if ('1' == seopress_get_toggle_option('404') && apply_filters('seopress_post_automatic_redirect', true)) {
    function seopress_get_permalink_for_updated_post($post) {
        $url = wp_parse_url(get_permalink($post));
        if (is_array($url) && isset($url['path'])) {
            return $url['path'];
        }

        return '';
    }

    /**
     * Update of the option to propose a redirection.
     *
     * @return void
     *
     * @author Thomas Deneulin
     *
     * @param mixed $message
     */
    function seopress_create_notifaction_for_redirect($message) {
        $messages = seopress_get_option_post_need_redirects();
        if ( ! $messages) {
            $messages = [];
        }

        $messages[] = $message;

        update_option('seopress_can_post_redirect', $messages, false);
    }

    /**
     * Delete the option to propose a redirection.
     *
     * @return void
     *
     * @author Thomas Deneulin
     *
     * @param mixed $id
     */
    function seopress_remove_notification_for_redirect($id) {
        $messages = seopress_get_option_post_need_redirects();
        if ( ! $messages) {
            return;
        }

        foreach ($messages as $key => $message) {
            if ($id === $message['id']) {
                unset($messages[$key]);
            }
        }

        if (empty($messages)) {
            delete_option('seopress_can_post_redirect');

            return;
        }

        update_option('seopress_can_post_redirect', $messages, false);
    }

    /**
     * Checks if a post needs to be repeated.
     *
     * @param int $post_id
     *
     * @return bool
     */
    function seopress_can_post_autoredirect($post_id) {

        $post_type = get_post_type_object(get_post_type($post_id));

        if ( ! $post_type) {
            return false;
        }

        $post_types          = seopress_get_post_types();
        $post_types          = apply_filters('seopress_automatic_redirect_cpt', $post_types);

        $post_type_authorized = [];
        foreach ($post_types as $key => $type) {
            $post_type_authorized[] = $type->name;
        }

        return in_array($post_type->name, $post_type_authorized, true);
    }

    add_action('admin_notices', 'seopress_notice_need_to_redirect');

    /**
     * Notice proposing to create a redirection.
     *
     * @return void
     *
     * @author Thomas Deneulin
     */
    function seopress_notice_need_to_redirect() {
        $notices = seopress_get_option_post_need_redirects();
        if ( ! $notices) {
            return;
        }

        if ( ! current_user_can(seopress_capability('edit_redirections', 'notice'))) {
            return;
        }

        if (count($notices) > 1) {
            $remove_all_notices_url = wp_nonce_url(
                add_query_arg(
                    [
                        'action' => 'seopress_dismiss_all_notice_need_to_redirect',
                    ],
                    admin_url('admin-post.php')
                ),
                'seopress_dismiss_all_notice_need_to_redirect'
            );
            $info     = __('We have %s redirections that needs your attention', 'wp-seopress-pro');
            $view_all = __('View all notices (%s)', 'wp-seopress-pro'); ?>
<div class="notice notice-warning">
    <p>
        <?php
                    printf($info, count($notices)); ?>
    </p>
    <p>
        <a href="#" id="js-view-all-notices" class="button button-secondary">
            <?php printf($view_all, count($notices)); ?>
        </a>
        <a href="<?php echo $remove_all_notices_url; ?>"
            class="button button-link">
            <?php _e('Remove all notices', 'wp-seopress-pro'); ?>
        </a>
    </p>
</div>


<?php
        }

        $notices = array_reverse($notices);
        foreach ($notices as $key => $notice) {
            $before_url = trim($notice['before_url'], '\/');

            $href_button = admin_url(sprintf('post-new.php?post_type=seopress_404&post_title=%s&prepare_redirect=1&key=%s', $before_url, $key));

            if ('update' === $notice['type']) {
                $href_button = add_query_arg(
                    [
                        'redirect_to' => trim($notice['new_url'], '\/'),
                    ],
                    $href_button
                );
            } ?>

<div class="notice notice-warning <?php if ($key > 0) { ?>notice-redirect-hide<?php } ?>"
    style="position:relative; <?php if ($key > 0) { ?>display:none;<?php } ?>">
    <?php
                printf('<a href="%s" class="notice-dismiss" style="text-decoration:none;"><span class="screen-reader-text">' . __('Dismiss this notice', 'wp-seopress-pro') . '</span></a>', wp_nonce_url(
                add_query_arg(
                        [
                            'action' => 'seopress_dismiss_notice_need_to_redirect',
                            'id'     => $notice['id'],
                        ],
                        admin_url('admin-post.php')
                    ),
                'seopress_dismiss_notice_need_to_redirect'
            )); ?>
    <?php echo $notice['message']; ?>
    <p>
        <a href="<?php echo esc_url($href_button); ?>"
            class="button button-secondary">
            <?php _e('Create a redirection', 'wp-seopress-pro'); ?>
        </a>
    </p>
</div>
<?php
        }

        if (count($notices) > 1) {
            ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const $ = jQuery
        $("#js-view-all-notices").on("click", function(e) {
            e.preventDefault()
            $(".notice-redirect-hide").each(function(key, item) {
                $(item).slideToggle()
            })
        })
    })
</script>
<?php
        }
    }

    add_action('admin_post_seopress_dismiss_notice_need_to_redirect', 'seopress_dismiss_notice_need_to_redirect');

    /**
     * Deleting need to redirect notice.
     *
     * @return void
     *
     * @author Thomas Deneulin
     */
    function seopress_dismiss_notice_need_to_redirect() {
        if ( ! wp_verify_nonce($_GET['_wpnonce'], 'seopress_dismiss_notice_need_to_redirect')) {
            wp_redirect(admin_url('admin.php?page=seopress-option'));
            exit;
        }

        if ( ! current_user_can(seopress_capability('edit_redirections', 'notice'))) {
            wp_redirect(admin_url('admin.php?page=seopress-option'));
            exit;
        }

        if ( ! isset($_GET['id'])) {
            wp_redirect(admin_url('admin.php?page=seopress-option'));
            exit;
        }

        seopress_remove_notification_for_redirect($_GET['id']);

        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : admin_url('admin.php?page=seopress-option');

        wp_redirect($redirect);
    }

    add_action('admin_post_seopress_dismiss_all_notice_need_to_redirect', 'seopress_dismiss_all_notice_need_to_redirect');

    /**
     * Deleting all notices need to redirect.
     *
     * @return void
     *
     * @author Thomas Deneulin
     */
    function seopress_dismiss_all_notice_need_to_redirect() {
        if ( ! wp_verify_nonce($_GET['_wpnonce'], 'seopress_dismiss_all_notice_need_to_redirect')) {
            wp_redirect(admin_url('admin.php?page=seopress-option'));
            exit;
        }

        if ( ! current_user_can(seopress_capability('edit_redirections', 'notice'))) {
            wp_redirect(admin_url('admin.php?page=seopress-option'));
            exit;
        }

        delete_option('seopress_can_post_redirect');

        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : admin_url('admin.php?page=seopress-option');
        wp_redirect($redirect);
    }

    add_action('admin_init', 'seopress_pre_filling_data_need_to_redirect');

    /**
     * Pre-populate the redirect if we try to create one through the watcher.
     *
     * @return void
     *
     * @author Thomas Deneulin
     */
    function seopress_pre_filling_data_need_to_redirect() {
        if ( ! is_seopress_page()) {
            return;
        }

        if ( ! isset($_GET['post_type']) || 'seopress_404' !== $_GET['post_type']) {
            return;
        }

        global $pagenow;
        if ( ! in_array($pagenow, ['post-new.php']) || ! isset($_GET['prepare_redirect'])) {
            return;
        }

        add_filter('get_post_metadata', function ($metadata, $object_id, $meta_key, $single) {
            $can_filters = [
            '_seopress_redirections_value',
            '_seopress_redirections_enabled',
        ];

            if ( ! in_array($meta_key, $can_filters, true)) {
                return $metadata;
            }

            if ('_seopress_redirections_enabled' === $meta_key) {
                return 'yes';
            }
            if ('_seopress_redirections_value' === $meta_key && isset($_GET['redirect_to'])) {
                if (seopress_advanced_advanced_trailingslash_option()) {
                    $url_redirect = sprintf('%s/%s', site_url(), $_GET['redirect_to']);
                } else {
                    $url_redirect = trailingslashit(sprintf('%s/%s', site_url(), $_GET['redirect_to']));
                }

                return esc_url($url_redirect);
            }

            return $metadata;
        }, 1, 4);
    }

    require_once __DIR__ . '/post-watcher.php';
}
