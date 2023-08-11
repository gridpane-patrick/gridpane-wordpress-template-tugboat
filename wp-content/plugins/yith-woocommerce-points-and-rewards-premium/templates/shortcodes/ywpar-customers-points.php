<?php
/**
 * HTML ywpar_best_users Shortcode template
 *
 * @class   YITH_WC_Points_Rewards_Shortcodes
 * @since   2.2.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var array $args
 */

defined( 'ABSPATH' ) || exit;

// DO_ACTION: ywpar_before_best_users_tabs: trigger before best users tabs.
do_action( 'ywpar_before_best_users_tabs' );

$style = ( ! empty( $args['style'] ) ) ? $args['style'] : 'simple';

?>
<div id="ywpar_tabs"
	class="ywpar_best_users <?php echo esc_attr( $style ); ?> <?php echo esc_attr( count( $args['times'] ) < 2 ? 'no_tabs' : '' ); ?>">
	<?php if ( count( $args['times'] ) > 1 ) : ?>
		<div class="ywpar_best_users_header ywpar_tabs_header">
			<ul>
				<li class="ywpar_best_users_link ywpar_tabs_links active"
					data-target="all_time"><?php esc_html_e( 'All time', 'yith-woocommerce-points-and-rewards' ); ?></li>
				<li class="ywpar_best_users_link ywpar_tabs_links"
					data-target="last_month"><?php esc_html_e( 'Last 30 days', 'yith-woocommerce-points-and-rewards' ); ?></li>
				<li class="ywpar_best_users_link ywpar_tabs_links"
					data-target="this_week"><?php esc_html_e( 'This week', 'yith-woocommerce-points-and-rewards' ); ?></li>
				<li class="ywpar_best_users_link ywpar_tabs_links"
					data-target="today"><?php esc_html_e( 'Today', 'yith-woocommerce-points-and-rewards' ); ?></li>
			</ul>
		</div>
	<?php endif; ?>
	<?php foreach ( $args['times'] as $time ) { ?>

		<div id="<?php echo esc_attr( $time ); ?>" class="ywpar_best_users_tab ywpar_tabcontent">
			<ul>
				<?php
				$items = yith_points()->points_log->get_best_users( $time, $args['num_of_customers'] );

				$counter = 0;
				if ( $items ) :
					foreach ( $items as $idx => $i ) :

						$counter++;
						$customer = ywpar_get_customer( $i->user_id );
						if ( ! $customer ) {
							continue;
						}

						$username = $customer->get_wc_customer()->get_display_name();
						$avatar   = get_avatar( $customer->get_id() );
						$level_id = $customer->get_level();
						$level    = ywpar_get_level_badge( $level_id );

						?>
					<li class="<?php echo esc_attr( ' list_element_' . $counter ); ?> ">
						<?php echo get_avatar( $customer->get_id() ); ?>

						<div class="user_info" id="<?php echo esc_attr( $i->user_id ); ?>">
							<p class="user_name"><?php echo esc_html( $counter . '. ' . $username ); ?></p>
							<div class="user_points">
								<?php
								if ( $level && 'on' === $level->get_status() ) :
									$img   = $level->get_image();
									$color = $level->get_level_color();
									?>
									<div class="level">
										<?php if ( ! empty( $img ) ) : ?>
											<img src="<?php echo esc_url( $img ); ?>"
												alt="<?php echo esc_attr( $level->get_name() ); ?>"/>
										<?php endif; ?>
										<span
											style="color:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $level->get_name() ); ?></span>
									</div>
								<?php endif; ?>
								<span class="points"><?php echo esc_html( $i->total ) . ' ' . esc_html( ywpar_get_option( 'points_label_plural' ) ); ?></span>
							</div>
						</div>
					</li>
						<?php
				endforeach;
				else :
					?>
				<ul>
					<li>
						<?php esc_html_e( 'No results', 'yith-woocommerce-points-and-rewards' ); ?>
					</li>
				</ul>
					<?php endif; ?>
			</ul>
		</div>
	<?php } ?>
</div>
<?php
// DO_ACTION: ywpar_after_best_users_tabs: trigger after best users tabs.
do_action( 'ywpar_after_best_users_tabs' );
?>
