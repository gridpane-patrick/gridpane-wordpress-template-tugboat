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

$banner_colors       = $banner->get_banner_colors();
$progress_bar_colors = $banner->get_progress_bar_colors();
$linked              = 'yes' === $banner->get_link_status() && ! empty( $banner->get_link_url() );
$usable_amount       = $customer->get_usable_amount();

$steps = $banner->get_steps_of_amount_spent_banner( $customer );
if ( 0 === count( $steps ) ) {
	return;
} else {
	$total_points_can_get  = 0;
	$total_amount_to_spent = 0;

	foreach ( $steps as $key => $value ) {
		$total_points_can_get  += (int) $value;
		$total_amount_to_spent += (int) $key;
	}
}



$banner_title = $banner->get_title();
$banner_title = empty( $banner_title ) ? ywpar_get_precompiled_title( $banner->get_action_target_type() ) : $banner_title;
$banner_title = str_replace( '%points%', $total_points_can_get . ' ' . ywpar_get_option( 'points_label_plural' ), $banner_title );
$banner_title = str_replace( '%amount%', wc_price( $total_amount_to_spent ), $banner_title );

$banner_text = $banner->get_subtitle();
$banner_text = empty( $banner_text ) ? ywpar_get_precompiled_text( $banner->get_action_target_type() ) : $banner_text;
$banner_text = str_replace( '%amount%', '<strong>' . wc_price( $total_amount_to_spent ) . '</strong>', $banner_text );
$banner_text = str_replace( '%points%', '<strong>' . $total_points_can_get . ' ' . ywpar_get_option( 'points_label_plural' ) . '</strong>', $banner_text );

$image       = $banner->get_image();
$image_class = ! empty( $image ) ? 'with_image' : '';

?>

	<div id="ywpar_banner" class="target_banner <?php echo $image_class; ?>"
		style="background-color: <?php echo esc_attr( $banner_colors['background'] ); ?>;">
		<?php if ( $linked ) : ?>
		<a href="<?php echo esc_url( $banner->get_link_url() ); ?> ">
			<?php endif ?>
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
				if ( 'yes' === $banner->get_progress_bar_status() ) {
					?>
					<div id="ywpar_progress_bar">
						<div class="pbar_back"
							style="background-color: <?php echo esc_attr( $progress_bar_colors['bar'] ); ?>;">
							<?php
							$rules                = array_keys( $steps );
							$max_value_to_achieve = end( $rules );
							$current_progress     = ( $usable_amount * 100 ) / (int) $max_value_to_achieve;
							$step_style           = 'position:absolute;left: calc(100% - 3px ); background:' . $progress_bar_colors['progress'];

							echo '<div class="pbar_fill" style="background:' . $progress_bar_colors['progress'] . ';width:' . $current_progress . '%;"><div class="step" style="' . $step_style . '"><span>' . wp_kses_post( wc_price( $usable_amount ) ) . '</span></div></div>';

							$c = 1;
							foreach ( $steps as $step => $points_to_get ) {
								if ( (int) $c < count( $steps ) ) {
									$position = ( (int) $step * 100 ) / (int) $max_value_to_achieve;
									?>
									<div class="step" style="left:<?php echo esc_attr( $position ); ?>%;">
										<span><?php echo wp_kses_post( ( wc_price( $step ) ) ); ?></span>
										<span
											class="step_points"><?php echo '+' . esc_attr( $points_to_get ) . ' ' . ywpar_get_option( 'points_label_plural' ); ?></span>
									</div>
									<?php
								} else {
									?>
									<div class="step final_step">
										<span><?php echo wp_kses_post( ( wc_price( $step ) ) ); ?></span>
										<span
											class="step_points"><?php echo '+' . esc_attr( $points_to_get ) . ' ' . ywpar_get_option( 'points_label_plural' ); ?></span>
									</div>
									<?php
								}
								$c += 1;
							}
							?>
						</div>
					</div>
					<?php
				}
				?>
			</div>
			<?php if ( $linked ) : ?>
		</a>
	<?php endif ?>
	</div>

<?php


