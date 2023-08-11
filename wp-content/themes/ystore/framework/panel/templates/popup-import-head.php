<?php
/**
 * Template "Demos" for 8theme dashboard.
 *
 * @since   
 * @version 1.0.0
 */

$versions = etheme_get_demo_versions();

$version = $versions[$_POST['version']];
?>

<div class="popup-import-head">
	<img
		class="version-img"
		src="http://8theme.com/import/xstore-demos/<?php echo esc_attr( $_POST['version'] ); ?>/screenshot.jpg"
		alt="<?php echo esc_attr( $_POST['version'] ); ?>">
</div>