<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );
/**
 * Template "Welcome" for 8theme dashboard.
 *
 * @since   6.3.10
 * @version 1.0.0
 */
?>

<h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('System Requirements', 'xstore'); ?></h2>
<p class="et-message et-info">
	<?php esc_html_e('Before using theme, first of all, make certain that your server and WordPress meet theme\'s requirements. You can change them by yourself, or contact your hosting provider with a request to increase the following minimums.', 'xstore'); ?>
</p>
<br/>
<?php
$system = new Etheme_System_Requirements();
    $system->html();
$result = $system->result();
?>

<div class="text-center">
	<a href="" class="et-button last-button">
            <span class="et-loader">
            <svg class="loader-circular" viewBox="25 25 50 50"><circle class="loader-path" cx="50" cy="50" r="12" fill="none" stroke-width="2" stroke-miterlimit="10"></circle></svg>
            </span><span class="dashicons dashicons-image-rotate"></span> <?php esc_html_e( 'Check again', 'xstore' ); ?>
	</a>
</div>
