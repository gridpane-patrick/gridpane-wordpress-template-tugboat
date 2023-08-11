<?php
/**
 * Blog post content template
 *
 * @package    content-grid.php
 * @since      1.0.0
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
        <?php $this->post_published_info($options['id'], $options['info_args']); ?>
    </div>
 
    <div class="content-article">
	    <?php if ( $options['excerpt_length'] > 0 ) {
		    if ( strlen($options['excerpt']) <= 0) {
			    ob_start();
			        the_excerpt();
			    $options['excerpt'] = ob_get_clean();
		    }
		    if ( strlen($options['excerpt']) > 0 ) {
			    $options['excerpt_length'] = apply_filters( 'excerpt_length', $options['excerpt_length'] );
			    $options['excerpt_more'] = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
			    $options['text']         = wp_trim_words( $options['excerpt'], $options['excerpt_length'], $options['excerpt_more'] );
			    echo apply_filters( 'wp_trim_excerpt', $options['text'], $options['text'] );
		    }
	    }
	    else {
		    the_excerpt();
	    }?>
    </div>
    <a href="<?php echo esc_url($options['link']); ?>" class="<?php if( $options['read_more_button']) { ?>button small <?php } ?>read-more">
        <span><?php echo esc_html__('Continue reading', 'xstore-amp'); ?></span>
    </a>
</article>