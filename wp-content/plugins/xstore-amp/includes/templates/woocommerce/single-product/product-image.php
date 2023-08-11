<?php
/**
 * Single Product Image
 *
 * @package    product-image.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

global $product;

$xstore_amp = XStore_AMP::get_instance();

$options = array();
$options['id'] = (int)get_the_ID();
$options['attachment_ids']     = $product->get_gallery_image_ids();
$options['has_thumbnail'] = has_post_thumbnail( $options['id'] );

$options['slider_dimensions'] = array(
	'width' => 317,
	'height' => 317
);
$options['thumbs_slider_dimensions'] = array(
	'height' => 120
);
$options['i'] = 0;
$options['j'] = 0;

if ( $options['has_thumbnail'] ) :
	$options['get_post_thumbnail_id'] = get_post_thumbnail_id( $options['id'] );
    $size_data  = wp_get_attachment_image_src( $options['get_post_thumbnail_id'], 'woocommerce_single' );
    $options['slider_dimensions']['width'] = $size_data[1];
    $options['slider_dimensions']['height'] = $size_data[2];
?>
<amp-state id="galleryImages">
    <script type="application/json">
        <?php
            $thumb_image = wp_get_attachment_image_src( $options['get_post_thumbnail_id'], array(100, 100) );
            $images = array(
                'main' => array(
                    'src' => $size_data[0],
                    'width' => $size_data[1],
                    'height' => $size_data[2],
                ),
                'thumb' => array(
	                'src' => $thumb_image[0],
	                'width' => $thumb_image[1],
	                'height' => $thumb_image[2],
                )
            );
	        echo json_encode($images);
        ?>
    </script>
</amp-state>
<?php endif; ?>
<div class="main-carousel">
	<?php  if ( $options['has_thumbnail'] ) { ?>
		 <amp-carousel id="mainCarousel"
           width="<?php echo esc_attr($options['slider_dimensions']['width']); ?>"
		  height="<?php echo esc_attr($options['slider_dimensions']['height']); ?>"
		  layout="responsive"
		  type="slides"
		  aria-label="Basic carousel"
		   on="slideChange: thumbCarouselSelector.toggle(index=event.index, value=true),
	      thumbCarousel.goToSlide(index=event.index)"
		  data-next-button-aria-label="Next button aria label"
		  data-prev-button-aria-label="Prev button aria label">
		 <div class="slide">
             <?php echo $xstore_amp->render_image(
                     array(
                             'image_id' => $options['get_post_thumbnail_id'],
                             'size' => 'woocommerce_single',
                             'lightbox' => 'mainCarousel',
                              'attr' => array(
                                      '[src]'=> "getVariationInfo.main_image.src ? getVariationInfo.main_image.src : galleryImages.main.src",
                                      '[width]' => "getVariationInfo.main_image.width ? getVariationInfo.main_image.width : galleryImages.main.width",
                                      '[height]' => "getVariationInfo.main_image.height ? getVariationInfo.main_image.height : galleryImages.main.height"
                              )
                     )
             ); ?>
		 </div>
		 <?php
			if ( count( $options['attachment_ids'] ) > 0 ) {
				foreach ( $options['attachment_ids'] as $key => $attachment_id ) {
//				    if ( !wp_attachment_is_image($attachment_id)) continue;
					echo '<div class="slide">';
                        echo $xstore_amp->render_image(
                                array(
                                    'image_id' => $attachment_id,
                                    'size' => 'woocommerce_single',
                                    'lightbox' => 'mainCarousel'
                                )
                        );
					echo '</div>';
				}
			}
		 ?>
		</amp-carousel>
	<?php }
	else {
		woocommerce_placeholder_img();
	} ?>
</div>
<?php if ( $options['has_thumbnail'] ) :
	    $size_data  = wp_get_attachment_image_src( $options['get_post_thumbnail_id'], array(100,100) );
	    $options['thumbs_slider_dimensions']['height'] = $size_data[2];
    ?>
	<div class="thumbnails-carousel">
	<amp-selector id="thumbCarouselSelector" name="single_image_select" layout="container"
				  on="select:mainCarousel.goToSlide(index=event.targetOption)">
		<amp-carousel id="thumbCarousel"
					  height="<?php echo esc_attr($options['thumbs_slider_dimensions']['height']); ?>"
					  layout="fixed-height"
					  type="carousel">
			<div class="slide"
				 option="<?php echo esc_attr($options['j']); ?>"
				 selected>
                <?php
                    echo $xstore_amp->render_image(
                            array(
                                'image_id' => $options['get_post_thumbnail_id'],
                                'size'=> array(100, 100),
                                'layout' => false,
                                'attr' => array('[src]'=> "getVariationInfo.thumb_image.src ? getVariationInfo.thumb_image.src : galleryImages.thumb.src",
                                                '[width]' => "getVariationInfo.thumb_image.width ? getVariationInfo.thumb_image.width : galleryImages.thumb.width",
                                                '[height]' => "getVariationInfo.thumb_image.height ? getVariationInfo.thumb_image.height : galleryImages.thumb.height")
                            )
                    )
                ?>
			</div>
			<?php
				if ( count( $options['attachment_ids'] ) > 0 ) {
					foreach ( $options['attachment_ids'] as $key => $attachment_id ) {
//						if ( !wp_attachment_is_image($attachment_id)) continue;
						$options['j']++;
						?>
						<div class="slide" option="<?php echo esc_attr($options['j']); ?>">
							<?php
                                echo $xstore_amp->render_image(
                                    array(
                                        'image_id' => $attachment_id,
                                        'size'=> array(100, 100),
                                        'layout' => false
                                    )
                                )
                            ?>
						</div>
						<?php
					}
				}
			?>
		</amp-carousel>
	</amp-selector>
</div>
<?php endif;