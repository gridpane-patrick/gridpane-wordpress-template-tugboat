<?php
/**
 * 404 page not found template
 *
 * @package    404.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );
?>
<div class="page-404 text-center">
	<h2>404</h2>
	<h1><?php esc_html_e('That Page Can\'t Be Found', 'xstore-amp') ?></h1>
    <a href="<?php echo home_url('/'); ?>" class="button"><?php esc_html_e('Go to home', 'xstore-amp'); ?></a>
</div>