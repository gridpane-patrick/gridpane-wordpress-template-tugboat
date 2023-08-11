<?php if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}
/**
 * Template "Email-Builder" for 8theme dashboard.
 *
 * @since   7.2.0
 * @version 1.0.0
 */
?>

<?php

	$email_builder_page_options = array();
	
    $email_builder_page_options['is_enabled'] = get_option('etheme_built_in_email_builder', false);
    $email_builder_page_options['is_enabled_dev_mode'] = get_option('etheme_built_in_email_builder_dev_mode', false);
?>

<h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('Built-in Email Builder', 'xstore'); ?></h2>
<p class="et-message et-info">
    <?php echo esc_html__('The powerful builder you have at your disposal allows for the creation of visually engaging and effective emails to be sent to your customers upon completion of an order or account creation on your website. It is important to note that upon activating the builder, the standard email templates within WooCommerce will be replaced with the new ones created using the builder. However, should you wish to revert to the original templates, simply deactivate the builder.', 'xstore'); ?>
</p>
<p>
	<label class="et-panel-option-switcher<?php if ( $email_builder_page_options['is_enabled']) { ?> switched<?php } ?>" for="et_email_builder">
	    <input type="checkbox" id="et_email_builder" name="et_email_builder" <?php if ( $email_builder_page_options['is_enabled']) { ?>checked<?php } ?>>
	    <span></span>
	</label>
</p>

<?php if ( $email_builder_page_options['is_enabled'] ) : ?>
    <p class="et-message">
        <?php echo esc_html__('Your email builder is activated and you can now try it by clicking the button below.', 'xstore'); ?>
    </p>

    <h4><?php echo esc_html__( 'Developer Mode', 'xstore' ); ?></h4>
    <p><?php echo esc_html__('If you need some time to build your own Email templates and keep the origin ones to be sent for customers - use developer mode.', 'xstore'); ?></p>
    <p>
        <label class="et-panel-option-switcher<?php if ( $email_builder_page_options['is_enabled_dev_mode']) { ?> switched<?php } ?>" for="et_email_builder_develop_mode">
            <input type="checkbox" id="et_email_builder_develop_mode" name="et_email_builder_develop_mode" <?php if ( $email_builder_page_options['is_enabled_dev_mode']) { ?>checked<?php } ?>>
            <span></span>
        </label>
    </p>

    <a href="<?php echo admin_url( 'edit.php?post_type=viwec_template' ); ?>" class="et-button et-button-green no-loader" target="_blank">
		<?php esc_html_e('Go to Email Builder', 'xstore'); ?>
    </a>
<?php endif; ?>

<?php unset($email_builder_page_options);