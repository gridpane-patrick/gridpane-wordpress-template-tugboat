<?php
// Exit if accessed directly
if ( ! defined('ABSPATH')) {
    exit;
}

$license = defined( 'SEOPRESS_LICENSE_KEY' ) && ! empty( SEOPRESS_LICENSE_KEY ) && is_string( SEOPRESS_LICENSE_KEY ) ? SEOPRESS_LICENSE_KEY : get_option('seopress_pro_license_key');
$selected = $license ? '********************************' : '';
$status = get_option('seopress_pro_license_status');
$docs = function_exists('seopress_get_docs_links') ? seopress_get_docs_links() : '';

if (is_plugin_active('wp-seopress/seopress.php')) {
    if (function_exists('seopress_admin_header')) {
        echo seopress_admin_header();
    }
} ?>

<form class="seopress-option" method="post"
    action="<?php echo admin_url('options.php'); ?>">
    <?php echo $this->seopress_feature_title(null); ?>

    <div id="seopress-tabs" class="wrap full-width">
        <div class="seopress-tab active">
            <?php settings_fields('seopress_license'); ?>
            <?php if (isset($_GET['seopress_support']) && $_GET['seopress_support'] === '1'): ?>
            <a href="<?php
                    echo wp_nonce_url(add_query_arg(['action' => 'seopress_relaunch_upgrader',], admin_url('admin-post.php')), 'seopress_relaunch_upgrader'); ?>"
                class="btn btnSecondary">
                <?php _e('Reload upgrader schema', 'wp-seopress-pro'); ?>
            </a>
            <?php endif; ?>

            <p>
                <?php _e('The license key is used to access automatic updates and support.', 'wp-seopress-pro'); ?>
            </p>

            <p>
                <a href="<?php echo $docs['license']['account']; ?>"
                    class="btn btnSecondary" target="_blank">
                    <?php _e('View my account', 'wp-seopress-pro'); ?>
                </a>
                <button type="button" id="seopress_pro_license_reset" class="btn btnSecondary">
                    <?php _e('Reset your license', 'wp-seopress-pro'); ?>
                </button>
            </p>

            <div class="seopress-notice">
                <p>
                    <strong><?php _e('Steps to follow to activate your license:', 'wp-seopress-pro'); ?></strong>
                </p>

                <ol>
                    <li><?php _e('Paste your license key', 'wp-seopress-pro'); ?>
                    </li>
                    <li><?php _e('Save changes', 'wp-seopress-pro'); ?>
                    </li>
                    <li><?php _e('Activate License', 'wp-seopress-pro'); ?>
                    </li>
                </ol>

                <p>
                    <?php _e('That\'s it!', 'wp-seopress-pro'); ?>
                </p>

                <p>
                    <?php printf(__('You can also use the define %1$s to automatically activate your license key. <a href="%2$s" target="_blank">Learn more</a>', 'wp-seopress-pro'), '<code>SEOPRESS_LICENSE_KEY</code>', $docs['license']['license_define']); ?>
                    <span class="seopress-help dashicons dashicons-external"></span>
                </p>

                <p>
                    <a class="seopress-help"
                        href="<?php echo $docs['license']['license_errors']; ?>"
                        target="_blank">
                        <?php _e('Download unauthorized? - Can‘t activate?', 'wp-seopress-pro'); ?>
                    </a>
                    <span class="seopress-help dashicons dashicons-external"></span>
                </p>
            </div>

            <?php if (get_option('seopress_pro_license_key_error')) { ?>
                <div class="seopress-notice is-error">
                    <p>
                        <?php echo get_option('seopress_pro_license_key_error'); ?>
                    </p>
                </div>
            <?php } ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><?php _e('License Key', 'wp-seopress-pro'); ?>
                        </th>

                        <td valign="top">
                            <input id="seopress_pro_license_key" name="seopress_pro_license_key" type="password"
                                autocomplete="off" class="regular-text"
                                value="<?php esc_attr_e($selected); ?>" />
                            <p class="description"><?php _e('Enter your license key', 'wp-seopress-pro'); ?>
                            </p>
                            <?php if (defined( 'SEOPRESS_LICENSE_KEY' ) && ! empty( SEOPRESS_LICENSE_KEY ) && is_string( SEOPRESS_LICENSE_KEY )) { ?>
                                <p class="seopress-notice"><?php _e('Your license key is defined in wp-config.php.','wp-seopress-pro'); ?></p>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php if (false !== $license) { ?>
                    <tr valign="top">
                        <th scope="row"><?php _e('Activate License', 'wp-seopress-pro'); ?>
                        </th>

                        <td scope="row" valign="top">
                            <?php if (false !== $status && 'valid' == $status) { ?>
                            <div class="seopress-notice is-success">
                                <p><?php _e('active', 'wp-seopress-pro'); ?>
                                </p>
                            </div>
                            <?php wp_nonce_field('seopress_nonce', 'seopress_nonce'); ?>

                            <input id="seopress-edd-license-btn" type="submit" class="btn btnSecondary"
                                name="seopress_license_deactivate"
                                value="<?php _e('Deactivate License', 'wp-seopress-pro'); ?>" />

                            <div class="spinner"></div>

                            <?php } else {
                        wp_nonce_field('seopress_nonce', 'seopress_nonce'); ?>
                            <input id="seopress-edd-license-btn" type="submit" class="btn btnSecondary"
                                name="seopress_license_activate"
                                value="<?php _e('Activate License', 'wp-seopress-pro'); ?>" />

                            <div class="spinner"></div>
                            <?php
                    } ?>
                        </td>
                    </tr>

                    <?php if (isset($_GET['sl_activation']) && ! empty($_GET['message'])) { ?>
                    <tr valign="top">
                        <th scope="row"><?php _e('License status', 'wp-seopress-pro'); ?>
                        </th>

                        <td scope="row" valign="top">
                            <?php
switch ($_GET['sl_activation']) {
case 'false':
$message = htmlspecialchars(urldecode($_GET['message']));
?>
                            <p>
                                <?php echo $message; ?>
                            </p>
                            <div class="seopress-notice">
                                <p>
                                    <?php _e('Click <strong>Reset license</strong> button above, enter your <strong>license key</strong>, <strong>save changes</strong>, and click <strong>Activate</strong>.', 'wp-seopress-pro'); ?>
                                </p>
                                <p>
                                    <?php _e('Still can\'t activate your license? Please contact us, thank you!', 'wp-seopress-pro'); ?>
                                </p>
                            </div>
                            <?php
break;
case 'true':
default:
?>
                            <div class="seopress-notice is-success">
                                <p><?php _e('Your license has been successfully activated!', 'wp-seopress-pro'); ?>
                                </p>
                            </div>
                            <?php
break;
} ?>
                        </td>
                    </tr>
                    <?php }
} ?>
                </tbody>
            </table>
            <?php if (false !== $status && 'valid' == $status) {
    //do nothing
} else {
    sp_submit_button(__('Save changes', 'wp-seopress-pro'));
} ?>
        </div>
    </div>
</form>
<?php

function seopress_sanitize_license($new) {
    $old = get_option('seopress_pro_license_key');
    if ($old && $old != $new) {
        delete_option('seopress_pro_license_status'); // new license has been entered, so must reactivate
    }
    if ('********************************' == $new) {
        return $old;
    } else {
        return $new;
    }
}
