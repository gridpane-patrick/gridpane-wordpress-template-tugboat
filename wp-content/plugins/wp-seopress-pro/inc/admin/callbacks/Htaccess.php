<?php

defined('ABSPATH') or exit('Please don&rsquo;t call the plugin directly. Thanks :)');

function seopress_htaccess_file_callback() {
    if (defined('SEOPRESS_BLOCK_HTACCESS') && SEOPRESS_BLOCK_HTACCESS == true) { ?>
<div class="seopress-notice is-error">
    <p>
        <?php _e('Access not allowed by the PHP define.', 'wp-seopress-pro'); ?>
    </p>
</div>
<?php } else {
        if ( ! is_network_admin() && is_multisite()) { ?>
<div class="seopress-notice">
    <p>
        <?php _e('Multisite is enabled, go to network SEO settings to manage your .htaccess file.', 'wp-seopress-pro'); ?>
    </p>
</div>
<?php } else {
            if (isset($_SERVER['SERVER_SOFTWARE'])) {
                $server_software = explode('/', $_SERVER['SERVER_SOFTWARE']);
                reset($server_software);
                if ('nginx' != current($server_software)) {
                    if (file_exists(get_home_path() . '/.htaccess')) {
                        $htaccess = file_get_contents(get_home_path() . '/.htaccess'); ?>

<textarea id="seopress_htaccess_file" name="seopress_pro_option_name[seopress_htaccess_file]" rows="15"
    aria-label="<?php _e('Edit your htaccess file', 'wp-seopress-pro'); ?>"
    placeholder="<?php esc_html_e('This is your htaccess file!', 'wp-seopress-pro'); ?>"><?php echo $htaccess; ?></textarea>

<?php if (isset($options['seopress_htaccess_file'])) {
                            esc_html($options['seopress_htaccess_file']);
                        } ?>

<div class="wrap-tags">

    <button type="button" class="btn btnSecondary tag-title" id="seopress-tag-htaccess-1" data-tag="Options -Indexes">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php _e('Block directory browsing', 'wp-seopress-pro'); ?>
    </button>

    <button type="button" class="btn btnSecondary tag-title" id="seopress-tag-htaccess-2" data-tag="<files wp-config.php>
            order allow,deny
            deny from all
            </files>">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php _e('Protect wp-config.php file', 'wp-seopress-pro'); ?>
    </button>

    <button type="button" class="btn btnSecondary tag-title" id="seopress-tag-htaccess-3" data-tag="redirect 301 /your-old-url/ https://www.example.com/your-new-url">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php _e('301 redirection', 'wp-seopress-pro'); ?>
    </button>

</div>

<button type="button" id="seopress-save-htaccess" class="btn btnSecondary">
    <?php _e('Saves htaccess changes', 'wp-seopress-pro'); ?>
</button>
<span class="spinner"></span>

<?php
                    } else { ?>
<div class="seopress-notice is-error">
    <p>
        <?php _e('You don\'t have an htaccess file on your server.', 'wp-seopress-pro'); ?>
    </p>
</div>
<?php }
                } else { ?>
<div class="seopress-notice">
    <p>
        <?php _e('Your server is running Nginx, you don\'t have htaccess file.', 'wp-seopress-pro'); ?>
    </p>
</div>
<?php }
            }
        }
    }
}
