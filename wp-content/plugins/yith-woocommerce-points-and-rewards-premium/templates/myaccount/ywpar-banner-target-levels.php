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

$banner_colors = $banner->get_banner_colors();

$linked                       = 'yes' === $banner->get_link_status() && ! empty( $banner->get_link_url() );
$current_level                = ywpar_get_level_badge( $customer->get_level() );
$options                      = ywpar_get_option( 'points_on_levels' );
$step_info                    = $banner->get_steps_of_target_level_banner( $customer );
$steps                        = $step_info['steps'];
$steps_to_points              = $step_info['steps_to_points'];
$points                       = $customer->get_points_collected();
$points_to_collect_for_target = 0;

if ( 0 === count( $steps ) ) { /* we have probably reached all levels */
	return;
}


arsort( $steps ); /* sort to have the max rule option value as first value the array has as key the points value and as value the lavel id */

$what_need_to_get       = array_values( $steps );
$what_need_to_get_id    = $what_need_to_get['0']; /* id level to use with placeholder %level% */
$what_need_to_get_level = ywpar_get_level_badge( $what_need_to_get_id );
$what_need_to_get       = array_keys( $steps );
$what_need_to_get       = $what_need_to_get['0']; /* max points value to get in order to get the latest level rule */

$level_name   = $what_need_to_get_level->get_name();
$banner_title = $banner->get_title();
$banner_title = empty( $banner_title ) ? ywpar_get_precompiled_title( $banner->get_action_type() ) : $banner_title;
$banner_title = str_replace( '%level%', $level_name, $banner_title );

$banner_text = $banner->get_subtitle();
$banner_text = empty( $banner_text ) ? ywpar_get_precompiled_text( $banner->get_action_type() ) : $banner_text;
$banner_text = str_replace( '%points%', '<strong>' . $what_need_to_get . ' ' . ywpar_get_option( 'points_label_plural' ) . '</strong>', $banner_text );

$image       = $banner->get_image();
$image_class = ! empty( $image ) ? 'with_image' : '';

?>

<div id="ywpar_banner" class="target_banner <?php echo esc_attr( $image_class ); ?>"
	style="background-color: <?php echo esc_attr( $banner_colors['background'] ); ?>;">

	<?php if ( $linked ) : ?>
	<a href="<?php echo esc_url( $banner->get_link_url() ); ?>">
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
			if ( 'yes' === $banner->get_progress_bar_status() ) :
				$progress_bar_colors = $banner->get_progress_bar_colors();
				?>
				<div id="ywpar_progress_bar">
					<div class="pbar_back"
						style="background-color: <?php echo esc_attr( $progress_bar_colors['bar'] ); ?>;">
						<?php
						$current_progress   = ( $points * 100 ) / $what_need_to_get;
						$current_level_name = $current_level->get_name();
						$bar_fill_style     = 'background:' . $progress_bar_colors['progress'] . ';width:' . $current_progress . '%;';
						$step_style         = 'background:' . $progress_bar_colors['progress'].';left: calc(100% - 3px );';
						$step_label_style   = 'color:' . esc_attr( $progress_bar_colors['bar'] ) . ';background:' . $progress_bar_colors['progress'];
						?>
						<div class="pbar_fill" style="<?php echo esc_attr( $bar_fill_style ); ?>">
							<div class="step" style="<?php echo esc_attr( $step_style ); ?>">
								<span><?php echo esc_html( $points ); ?></span>
								<span class="step_label"
									style="<?php echo esc_attr( $step_label_style ); ?>"><?php echo esc_html( $current_level_name ); ?></span>
							</div>
						</div>
						<?php
						$c = 1;
						asort( $steps );
						foreach ( $steps as $step => $id_level ) {
							$level      = ywpar_get_level_badge( $id_level );
							$level_name = $level->get_name();
							if ( $c < count( $steps ) ) {
								$position = ( (int)$step * 100 ) / (int)$what_need_to_get;
								?>
								<div class="step" style="left:<?php echo esc_attr( $position ); ?>%;">
									<span><?php echo esc_attr( $step ); ?></span>
									<span class="step_label"
										style="color:<?php echo esc_attr( $progress_bar_colors['bar'] ); ?>;background-color:<?php echo esc_attr( $progress_bar_colors['progress'] ); ?>;"><?php echo esc_attr( $level_name ); ?></span>
									<span
										class="step_points"><?php echo '+' . esc_html( $steps_to_points[ $id_level ] ) . ' ' . esc_html( ywpar_get_option( 'points_label_plural' ) ); ?></span>
								</div>
								<?php
							} else {
								?>
								<div class="step final_step">
									<span><?php echo esc_html( $step ); ?></span>
									<span class="step_label"
										style="color:<?php echo esc_attr( $progress_bar_colors['bar'] ); ?>;background-color:<?php echo esc_attr( $progress_bar_colors['progress'] ); ?> ;"><?php echo esc_attr( $level_name ); ?></span>
									<span
										class="step_points"><?php echo '+' . esc_html( $steps_to_points[ $id_level ] ) . ' ' . esc_html( ywpar_get_option( 'points_label_plural' ) ); ?></span>
								</div>
								<?php
							}
							$c++;
						}
						?>
					</div>
				</div>
			<?php
			endif;
			?>
		</div>
		<?php if ( $linked ) : ?>
	</a>
<?php endif ?>
</div>
