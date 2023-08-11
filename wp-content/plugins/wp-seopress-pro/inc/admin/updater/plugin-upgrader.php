<?php

defined('ABSPATH') or exit('Cheatin&#8217; uh?');

/* --------------------------------------------------------------------------------------------- */
/* MIGRATE / UPGRADE =========================================================================== */
/* --------------------------------------------------------------------------------------------- */

add_action('admin_init', 'seopress_pro_upgrader');
/**
 * Tell WP what to do when admin is loaded aka upgrader.
 *
 * @since 3.8.2
 */
function seopress_pro_upgrader() {
    $versions = get_option('seopress_versions');
    $actual_version = isset($versions['pro']) ? $versions['pro'] : 0;

    // You can hook the upgrader to trigger any action when seopress is upgraded.
    // First install.
    if ( ! $actual_version) {
        /*
         * Allow to prevent plugin first install hooks to fire.
         *
         * @since 3.8.2
         *
         * @param (bool) $prevent True to prevent triggering first install hooks. False otherwise.
         */
        if ( ! apply_filters('seopress_pro_prevent_first_install', false)) {
            /*
             * Fires on the plugin first install.
             *
             * @since 3.8.2
             *
             */
            do_action('seopress_pro_first_install');
        }
    }

    if (SEOPRESS_PRO_VERSION !== $actual_version) {
        //Add Redirections caps to user with "manage_options" capability
        $roles = get_editable_roles();
        if ( ! empty($roles)) {
            foreach ($GLOBALS['wp_roles']->role_objects as $key => $role) {
                if (isset($roles[$key]) && $role->has_cap('manage_options')) {
                    $role->add_cap('edit_redirection');
                    $role->add_cap('edit_redirections');
                    $role->add_cap('edit_others_redirections');
                    $role->add_cap('publish_redirections');
                    $role->add_cap('read_redirection');
                    $role->add_cap('read_private_redirections');
                    $role->add_cap('delete_redirection');
                    $role->add_cap('delete_redirections');
                    $role->add_cap('delete_others_redirections');
                    $role->add_cap('delete_published_redirections');
                }
                if (isset($roles[$key]) && $role->has_cap('manage_options')) {
                    $role->add_cap('edit_schema');
                    $role->add_cap('edit_schemas');
                    $role->add_cap('edit_others_schemas');
                    $role->add_cap('publish_schemas');
                    $role->add_cap('read_schema');
                    $role->add_cap('read_private_schemas');
                    $role->add_cap('delete_schema');
                    $role->add_cap('delete_schemas');
                    $role->add_cap('delete_others_schemas');
                    $role->add_cap('delete_published_schemas');
                }
            }
        }

        /*
         * Fires when seopress Pro is upgraded.
         *
         * @since 3.8.2
         *
         * @param (string) $new_pro_version    The version being upgraded to.
         * @param (string) $actual_pro_version The previous version.
         */
        do_action('seopress_pro_upgrade', SEOPRESS_PRO_VERSION, $actual_version);
    }

    // If any upgrade has been done, we flush and update version.
    if (did_action('seopress_pro_first_install') || did_action('seopress_pro_upgrade')) {
        // Do not use seopress_get_option() here.

        $options = get_option('seopress_versions');
        $options = is_array($options) ? $options : [];

        $options['pro'] = SEOPRESS_PRO_VERSION;
        if (is_multisite()) {
            $sites = get_sites();
            foreach ($sites as $site) {
                update_blog_option($site->blog_id, 'seopress_versions', $options);
            }
        } else {
            update_option('seopress_versions', $options);
        }
    }
}

add_action('seopress_pro_upgrade', 'seopress_pro_new_upgrade', 10, 2);

/**
 * What to do when seopress is updated, depending on versions.
 *
 * @since 3.8.2
 *
 * @param (string) $seopress_version The version being upgraded to
 * @param (string) $actual_version   The previous version
 */
function seopress_pro_new_upgrade($seopress_version, $actual_version) {
}
