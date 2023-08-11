<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );
/**
 * Template "Patcher" for 8theme dashboard.
 *
 * @since   9.0.4
 * @version 1.0.0
 */

?>

<h2 class="etheme-page-title etheme-page-title-type-2"><?php echo esc_html__('XStore Patcher', 'xstore'); ?></h2>

<div class="etheme-div etheme-patcher-list">
    <?php
        $patcher = new \Etheme_Patcher();
        $patcher->get_patches_list(ETHEME_THEME_VERSION); // set current version as minimum version for patch list
    ?>
</div>

