<?php
/**
 * Target Banner template
 *
 * @since   3.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var YITH_WC_Points_Rewards_Customer $customer Current customer.
 * @var YITH_WC_Points_Rewards_Banner   $banner Banner Object.
 */

defined( 'ABSPATH' ) || exit;


$max_review         = $banner->get_max_review_products_to_show();
$products_to_review = $customer->get_products_to_review( $max_review );

if ( ! $products_to_review ) {
	return;
}

$banner_colors      = $banner->get_banner_colors();
$points_to_get = $customer->calculate_points_from_renews( $max_review );

if ( $points_to_get <= 0 ) {
	return;
}

$banner_title = $banner->get_title();
$banner_title = empty( $banner_title ) ? ywpar_get_precompiled_title( $banner->get_action_type() ) : $banner_title;

$banner_text = $banner->get_subtitle();
$banner_text = empty( $banner_text ) ? ywpar_get_precompiled_text( $banner->get_action_type() ) : $banner_text;
$banner_text = str_replace( '%points%', '<strong>' . $points_to_get . ' ' . ywpar_get_option( 'points_label_plural' ) . '</strong>', $banner_text );
$banner_text = str_replace( '%products%', '<strong>' . count( $products_to_review ) . ' ' . _n( 'product', 'products', count( $products_to_review ), 'yith-woocommerce-points-and-rewards' ) . '</strong>', $banner_text );

$image       = $banner->get_image();
$image_class = ! empty( $image ) ? 'with_image' : '';
?>
<div id="ywpar_banner"
	class="getpoints_banner <?php echo esc_attr( $banner->get_action_type() ); ?> <?php echo $image_class; ?>"
	style="background-color: <?php echo esc_attr( $banner_colors['background'] ); ?>;">

	<?php if ( ! empty( $image ) ) : ?>
		<img class="banner_image" src="<?php echo esc_url( $image ); ?>"/>
	<?php endif; ?>

	<div class="ywpar_banner_content">
		<div class="banner_header">
			<?php if ( ! empty( $banner_title ) ) : ?>
				<h3 style="color:<?php echo esc_attr( $banner_colors['title'] ); ?>"><?php echo wp_kses_post( $banner_title ); ?></h3>
			<?php endif; ?>

			<?php if ( ! empty( $banner_text ) ) : ?>
				<h4 style="color:<?php echo esc_attr( $banner_colors['text'] ); ?>"><?php echo wp_kses_post( $banner_text ); ?></h4>
			<?php endif; ?>

			<div style="clear:both;"></div>
		</div>

		<?php

		foreach ( $products_to_review as $product_id => $date ) :
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );
			?>
			<div class="product">
				<?php if ( isset( $image[0] ) ) : ?>
					<img src="<?php echo esc_url( $image[0] ); ?>">
				<?php endif ?>
				<p>
					<span class="title"><?php echo esc_html( get_the_title( $product_id ) ); ?></span>
					<span
						class="date"><?php echo esc_html( __( 'Purchased on ', 'yith-woocommerce-points-and-rewards' ) ) . esc_html( $date ); ?></span>
					<a href="<?php echo esc_url( get_the_permalink( $product_id ) . '#tab-reviews' ); ?>"
						target="_blank"><?php echo esc_html_x( 'Leave a review >', 'link to leave a review inside the review banner on My Account page', 'yith-woocommerce-points-and-rewards' ); ?></a>
				</p>
			</div>
			<div style="clear:both"></div>
		<?php endforeach; ?>
	</div>
</div>
