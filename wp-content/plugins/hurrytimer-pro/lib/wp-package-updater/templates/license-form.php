<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 
$has_license_key = !empty( $license_key );
$has_valid_license_key = empty( $license_error ) && $has_license_key;
?>
<p>Activate your license to get priority support and automatic update from your WordPress dashboard.</p>

<form id="<?php echo esc_attr( 'wrap_license_' . $package_slug ); ?>" >
	<p class="license-message" style="font-weight: bold;"></p>
	
	<p>
		<label><?php esc_html_e( 'License key', 'wp-package-updater' ); ?></label> <input placeholder="Enter license key to activate" class="regular-text license" type="text" id="<?php echo esc_attr( 'license_key_' . $package_id); ?>" value="<?php echo $license_key ?>" >
	
		<button type="button"  class="button-primary deactivate-license" <?php echo $has_license_key ? '' : 'style="display:none"'   ?>
		data-pending-text="Deactivating..."
		value="deactivate">Deactivate license</button>

	<button type="button"  class="button-primary activate-license" <?php echo $has_license_key ?  'style="display:none"' : ''  ?>
		data-pending-text="Activating..."
		value="activate" >
	Activate license
	</button>
	
</p>
</form>

<p class="description" style="font-style: italic;">Having trouble activating your license? <a href="<?php echo $support_page_url ?>" target="_blank">Contact us</a>.</p>

