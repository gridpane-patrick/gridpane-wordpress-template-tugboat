<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

/**
 * Automatic license key activation
 * @since 5.7
 * @return void
 * @author Benjamin
 */
function seopress_automatic_license_activation() {
    // Security
    if (!current_user_can(seopress_capability('manage_options', 'license'))) {
        return;
    }

    // Get license key from SEOPRESS_LICENSE_KEY define
    $license = defined( 'SEOPRESS_LICENSE_KEY' ) && ! empty( SEOPRESS_LICENSE_KEY ) && is_string( SEOPRESS_LICENSE_KEY ) ? SEOPRESS_LICENSE_KEY : null;
    if (!isset($license)) {
        return;
    }

    // Handle domain name changes (local / staging / live site)
    $prev_home_url = get_option('seopress_pro_license_home_url');
    if (empty($prev_home_url)) {
        update_option('seopress_pro_license_home_url', get_home_url());
    }
    if (isset($prev_home_url) && $prev_home_url !== get_home_url()) {
        delete_option('seopress_pro_license_status');
        delete_option('seopress_pro_license_automatic_attempt');
        update_option('seopress_pro_license_home_url', get_home_url());
    } else {
        return false;
    }

    // Already activated?
    if ('valid' === get_option('seopress_pro_license_status')) {
        return;
    }

    // Already executed?
    if (get_option('seopress_pro_license_automatic_attempt') === '1') {
        return;
    }
    update_option( 'seopress_pro_license_automatic_attempt', 1 );

    // data to send in our API request
    $api_params = [
        'edd_action' => 'activate_license',
        'license'    => $license,
        'item_id' 	  => ITEM_ID_SEOPRESS, // the ID of our product in EDD
        'url'        => home_url(),
        'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
    ];

    // Call the custom API.
    $response = wp_remote_post(
        STORE_URL_SEOPRESS,
        [
            'user-agent' =>'WordPress/' . get_bloginfo('version'),
            'timeout' => 15,
            'sslverify' => false,
            'body' => $api_params
        ]
    );

    // make sure the response came back okay
    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
        if (is_wp_error($response)) {
            $message = $response->get_error_message();
        } else {
            $message = __('An error occurred, please try again. Response code: ', 'wp-seopress-pro') . wp_remote_retrieve_response_code($response);
        }
    } else {
        $license_data = json_decode(wp_remote_retrieve_body($response));
        if (false === $license_data->success) {
            switch ($license_data->error) {
            case 'expired':
            /* translators: %s: localized expiration date */
            $message = sprintf(
                __('Your license key expired on %s.', 'wp-seopress-pro'),
                date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
            );
            break;

            case 'disabled':
            case 'revoked':
            $message = __('Your license key has been disabled.', 'wp-seopress-pro');
            break;

            case 'missing':
            $message = __('Invalid license.', 'wp-seopress-pro');
            break;

            case 'invalid':
            case 'site_inactive':
            $message = __('Your license is not active for this URL.', 'wp-seopress-pro');
            break;

            case 'item_name_mismatch':
            /* translators: %s: SEOPress PRO */
            $message = sprintf(__('This appears to be an invalid license key for %s.', 'wp-seopress-pro'), ITEM_NAME_SEOPRESS);
            break;

            case 'no_activations_left':
            $message = __('Your license key has reached its activation limit.', 'wp-seopress-pro');
            break;

            default:
            $message = __('An error occurred, please try again.', 'wp-seopress-pro');
            break;
            }
        }
    }

    // Check if anything passed on a message constituting a failure
    if (! empty($message)) {
        update_option('seopress_pro_license_key_error', $message);
        return;
    }

    // $license_data->license will be either "valid" or "invalid"
    if ( 'valid' === $license_data->license ) {
        update_option( 'seopress_pro_license_key', $license );
    }

    update_option('seopress_pro_license_status', $license_data->license);
    return;
}
add_action('admin_init', 'seopress_automatic_license_activation');

/************************************
* this illustrates how to activate
* a license key
*************************************/

function seopress_activate_license()
{
    // listen for our activate button to be clicked
    if (isset($_POST['seopress_license_activate'])) {
        // run a quick security check
        if (! check_admin_referer('seopress_nonce', 'seopress_nonce')) {
            return;
        } // get out if we didn't click the Activate button

        // retrieve the license from the database
        $license = defined( 'SEOPRESS_LICENSE_KEY' ) && ! empty( SEOPRESS_LICENSE_KEY ) && is_string( SEOPRESS_LICENSE_KEY ) ? SEOPRESS_LICENSE_KEY : trim(get_option('seopress_pro_license_key'));

        // data to send in our API request
        $api_params = [
            'edd_action' => 'activate_license',
            'license'    => $license,
            'item_id' 	  => ITEM_ID_SEOPRESS, // the ID of our product in EDD
            'url'        => home_url(),
            'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
        ];

        // Call the custom API.
        $response = wp_remote_post(
            STORE_URL_SEOPRESS,
            [
                'user-agent' =>'WordPress/' . get_bloginfo('version'),
                'timeout' => 15,
                'sslverify' => false,
                'body' => $api_params
            ]
        );

        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            if (is_wp_error($response)) {
                $message = $response->get_error_message();
            } else {
                $message = __('An error occurred, please try again. Response code: ', 'wp-seopress-pro') . wp_remote_retrieve_response_code($response);
            }
        } else {
            $license_data = json_decode(wp_remote_retrieve_body($response));
            if (false === $license_data->success) {
                switch ($license_data->error) {
                case 'expired':
                /* translators: %s: localized expiration date */
                $message = sprintf(
                    __('Your license key expired on %s.', 'wp-seopress-pro'),
                    date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                );
                break;

                case 'disabled':
                case 'revoked':
                $message = __('Your license key has been disabled.', 'wp-seopress-pro');
                break;

                case 'missing':
                $message = __('Invalid license.', 'wp-seopress-pro');
                break;

                case 'invalid':
                case 'site_inactive':
                $message = __('Your license is not active for this URL.', 'wp-seopress-pro');
                break;

                case 'item_name_mismatch':
                /* translators: %s: SEOPress PRO */
                $message = sprintf(__('This appears to be an invalid license key for %s.', 'wp-seopress-pro'), ITEM_NAME_SEOPRESS);
                break;

                case 'no_activations_left':
                $message = __('Your license key has reached its activation limit.', 'wp-seopress-pro');
                break;

                default:
                $message = __('An error occurred, please try again.', 'wp-seopress-pro');
                break;
                }
            }
        }

        // Check if anything passed on a message constituting a failure
        if (! empty($message)) {
            $base_url = admin_url('admin.php?page=' . SEOPRESS_LICENSE_PAGE);
            $redirect = add_query_arg(['sl_activation' => 'false', 'message' => urlencode($message)], $base_url);

            wp_redirect($redirect);
            exit();
        }

        // $license_data->license will be either "valid" or "invalid"
        if ( 'valid' === $license_data->license ) {
            update_option( 'seopress_pro_license_key', $license );
        }

        update_option('seopress_pro_license_status', $license_data->license);

        wp_safe_redirect(admin_url('admin.php?page=' . SEOPRESS_LICENSE_PAGE));
        exit();
    }
}
add_action('admin_init', 'seopress_activate_license');

/***********************************************
* Illustrates how to deactivate a license key.
* This will descrease the site count
***********************************************/

function seopress_deactivate_license()
{
    // listen for our activate button to be clicked
    if (isset($_POST['seopress_license_deactivate'])) {
        // run a quick security check
        if (! check_admin_referer('seopress_nonce', 'seopress_nonce')) {
            return;
        } // get out if we didn't click the Activate button

        // retrieve the license from the database
        $license = defined( 'SEOPRESS_LICENSE_KEY' ) && ! empty( SEOPRESS_LICENSE_KEY ) && is_string( SEOPRESS_LICENSE_KEY ) ? SEOPRESS_LICENSE_KEY : trim(get_option('seopress_pro_license_key'));

        // data to send in our API request
        $api_params = [
            'edd_action'=> 'deactivate_license',
            'license' 	 => $license,
            'item_id' 	 => ITEM_ID_SEOPRESS, // the ID of our product in EDD
            'url'       => home_url(),
            'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
        ];

        // Call the custom API.
        $response = wp_remote_post(
            STORE_URL_SEOPRESS,
            [
                'user-agent' => 'WordPress/' . get_bloginfo('version'),
                'timeout' => 15,
                'sslverify' => false,
                'body' => $api_params
            ]
        );

        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            if (is_wp_error($response)) {
                $message = $response->get_error_message();
            } else {
                $message = __('An error occurred, please try again.', 'wp-seopress-pro');
            }

            $base_url = admin_url('admin.php?page=' . SEOPRESS_LICENSE_PAGE);
            $redirect = add_query_arg(['sl_activation' => 'false', 'message' => urlencode($message)], $base_url);

            wp_safe_redirect($redirect);
            exit();
        }

        // decode the license data
        $license_data = json_decode(wp_remote_retrieve_body($response));

        // $license_data->license will be either "deactivated" or "failed"
        if ('deactivated' == $license_data->license) {
            delete_option('seopress_pro_license_status');
        }

        wp_safe_redirect(admin_url('admin.php?page=' . SEOPRESS_LICENSE_PAGE));
        exit();
    }
}
add_action('admin_init', 'seopress_deactivate_license');
