<?php
/**
 * Portfolio project content template
 *
 * @package    content-portfolio.php
 * @since      1.0.1
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

// get permalink before content because if content has products then link is bloken
global $xstore_amp_args;
$options = array();
$options['id'] = get_the_ID();
$options['link'] = get_the_permalink();
$options['excerpt'] = get_the_excerpt();
$options['classes'] = array(
	'grid-item',
	'flex-item'
);
$options['excerpt_length'] = $xstore_amp_args['excerpt_length'] ?? 30;
$options['info_args'] = $xstore_amp_args['info_args'] ?? array('time'=> false);
$options['read_more_button'] = $xstore_amp_args['read_more_button'] ?? true;

?>

<article <?php post_class($options['classes']); ?> id="post-<?php echo $options['id']; ?>">
	<div class="post-heading">
		<?php
		if ( has_post_thumbnail($options['id'])) { ?>
			<a href="<?php echo $options['link']; ?>">
				<?php
				$this->render_image(array(
					'image_id' => get_post_thumbnail_id(),
				));
				?>
			</a>
		<?php } ?>
		<h2><a href="<?php echo $options['link']; ?>"><?php the_title(); ?></a></h2>
		<?php echo $this->portfolio_categories($options['id']); ?>
	</div>
	
	<a href="<?php echo esc_url($options['link']); ?>" class="<?php if( $options['read_more_button']) { ?>button small <?php } ?>read-more">
		<span><?php echo esc_html__('Continue reading', 'xstore-amp'); ?></span>
	</a>
</article>