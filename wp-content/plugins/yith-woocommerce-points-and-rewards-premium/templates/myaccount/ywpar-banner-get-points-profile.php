<?php
/**
 * Get Points Banner template
 *
 * @since   3.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var YITH_WC_Points_Rewards_Customer $customer Current customer.
 * @var YITH_WC_Points_Rewards_Banner   $banner Banner Object.
 */

defined( 'ABSPATH' ) || exit;

if ( 'yes' === $customer->get_completed_profile() ) {
	return;
}

$banner_colors = $banner->get_banner_colors();
$points_to_get = ywpar_get_option( 'points_on_completed_profile' );

$banner_title = $banner->get_title();
$banner_title = empty( $banner_title ) ? ywpar_get_precompiled_title( $banner->get_action_type() ) : $banner_title;

$banner_text = $banner->get_subtitle();
$banner_text = empty( $banner_text ) ? ywpar_get_precompiled_text( $banner->get_action_type() ) : $banner_text;
$banner_text = str_replace( '%points%', '<strong>' . $points_to_get . ' ' . ywpar_get_option( 'points_label_plural' ) . '</strong>', $banner_text );

$image        = $banner->get_image();
$image_class  = ! empty( $image ) ? 'with_image' : '';
$edit_account = defined( 'YITH_PROTEO_VERSION' ) ? wc_get_account_endpoint_url( 'account-info' ) : wc_customer_edit_account_url();
?>
<div id="ywpar_banner"
	class="getpoints_banner <?php echo esc_attr( $banner->get_action_type() ); ?> <?php echo esc_attr( $image_class ); ?>"
	style="background-color: <?php echo esc_attr( $banner_colors['background'] ); ?>;">
	<a href="<?php echo esc_url( apply_filters( 'ywpar_customer_complete_profile_endpoint_url', $edit_account ) ); ?>">

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
			<div class="ywpar_arrow_go"></div>
		</div>
	</a>
</div>
