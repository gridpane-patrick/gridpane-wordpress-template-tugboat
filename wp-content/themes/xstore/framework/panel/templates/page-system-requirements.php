<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );
/**
 * Template "System requirements" for 8theme dashboard.
 *
 * @since   6.3.10
 * @version 1.0.0
 */

if (isset($_GET['et_clear_wc_system_status_theme_info'])){
	delete_transient( 'wc_system_status_theme_info' );
	wp_redirect('?page=et-panel-system-requirements');
	exit;
}

if (isset($_GET['et_clear_system_information'])){
    delete_transient( 'etheme_system_information' );
    delete_transient( 'etheme_system_requirements_test_result' );
    wp_redirect('?page=et-panel-system-requirements');
    exit;
}
?>

<h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('System Requirements', 'xstore'); ?></h2>
<p class="et-message et-info">
    <?php echo esc_html__('Before using the theme, please ensure that your server and WordPress meet the theme\'s requirements. You can make these changes on your own or contact your hosting provider to request an increase in the following minimums.', 'xstore'); ?>
</p>
<br/>
<?php
$system = new Etheme_System_Requirements();
    $system->html();
$result = $system->result();
?>

<div class="text-center">
    <a href="?page=et-panel-system-requirements&et_clear_system_information" class="et-button last-button">
            <span class="et-loader">
            <svg class="loader-circular" viewBox="25 25 50 50"><circle class="loader-path" cx="50" cy="50" r="12" fill="none" stroke-width="2" stroke-miterlimit="10"></circle></svg>
            </span><span class="dashicons dashicons-image-rotate"></span> <?php esc_html_e( 'Check again', 'xstore' ); ?>
    </a>
</div>

<br/>
<br/>

<h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('Additional information', 'xstore'); ?></h2>
<br/>
    <?php
        $system->wp_html();
    ?>
<br/>
<br/>

<h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('Active plugins', 'xstore'); ?></h2>
<br/>
<?php
$system->wp_active_plugins();
?>
<br/>
<br/>

<h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('WooCommerce system info cache', 'xstore'); ?></h2>
<p class="et-message et-info">
    <?php echo esc_html__('Please ensure that you clear the WooCommerce system cache after updating the theme, as this may cause outdated files to remain.', 'xstore'); ?>
</p>
<br/>

<div class="text-center">
    <a href="?page=et-panel-system-requirements&et_clear_wc_system_status_theme_info" class="et-button last-button">
            <span class="et-loader">
            <svg class="loader-circular" viewBox="25 25 50 50"><circle class="loader-path" cx="50" cy="50" r="12" fill="none" stroke-width="2" stroke-miterlimit="10"></circle></svg>
            </span><span class="dashicons dashicons-image-rotate"></span> <?php esc_html_e( 'Clear cache', 'xstore' ); ?>
    </a>
</div>
