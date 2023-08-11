<?php
/**
 * Single template
 *
 * @package    single.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.1
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $post;
$options = array();
$options['id'] = get_the_ID();
$options['link'] = get_the_permalink();
$options['post_format'] 	= get_post_format();

$options['classes'] 		= array();
$options['classes'][] 		= 'blog-post';
$options['classes'][] 		= 'post-single';

?>

<article <?php post_class( $options['classes'] ); ?> id="post-<?php echo $options['id']; ?>">
    
    <div class="post-heading">
        <?php if ( has_post_thumbnail($options['id'])) {
            $this->render_image(array(
                    'image_id' => get_post_thumbnail_id(),
            ));
        } ?>
        <h2 class="entry-title"><?php the_title(); ?></h2>
        <?php $this->post_published_info($post->ID); ?>
    </div>

    <div class="content-article entry-content">
        <?php the_content(); ?>
    </div>

    <div class="post-navigation"><?php wp_link_pages(); ?></div>

    <?php the_tags( '<div class="single-tags"><span>' . esc_html__( 'Tags: ', 'xstore-amp' ) . '</span>', ', ', '</div>' ); ?>

    <?php if ( is_singular('post') ) : ?>
        <a class="button" href="<?php echo esc_url(remove_query_arg('amp', add_query_arg('no-amp', 'true', get_permalink())).'#respond');?>"><?php echo esc_html__('Write a review', 'xstore-amp'); ?></a>
    <?php endif; ?>
    
</article>