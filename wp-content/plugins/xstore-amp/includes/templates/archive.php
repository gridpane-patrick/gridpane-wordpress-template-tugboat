<?php
/**
 * Blog page template
 *
 * @package    archive.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.1
 * @license    Themeforest Split Licence
 */
defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

?>

<div class="grid">
	<?php if(have_posts()):
		while(have_posts()) : the_post(); ?>
		
			<?php include XStore_AMP_TEMPLATES_PATH . 'content-grid.php'; ?>
		
		<?php endwhile;
		
		?>
	<?php else: ?>
		
		<div class="col-md-12">
			
			<h2><?php esc_html_e('No posts were found!', 'xstore-amp') ?></h2>
			
			<p><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with some different keywords', 'xstore-amp') ?></p>
			
			<?php get_search_form(); ?>
		
		</div>
	
	<?php endif; ?>
</div>

<?php the_posts_pagination( array(
        'prev_text' => '←',
        'next_text'=>'→'
));

wp_reset_postdata();