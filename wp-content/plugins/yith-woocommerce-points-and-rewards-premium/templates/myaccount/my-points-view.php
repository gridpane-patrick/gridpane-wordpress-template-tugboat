<?php
/**
 * My Points
 *
 * Shows total of user's points account page
 *
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) : ?>
	<p><?php esc_html_e( 'You must to be logged in to view your points.', 'yith-woocommerce-points-and-rewards' ); ?></p>
	<?php
	return;
endif;

$customer = ywpar_get_current_customer();

if ( ! $customer || ! $customer->is_enabled() ) :
	?>
	<p><?php esc_html_e( 'You are not enabled to see this page.', 'yith-woocommerce-points-and-rewards' ); ?></p>
	<?php
endif;
$points            = $customer->get_total_points();
$singular          = ywpar_get_option( 'points_label_singular' );
$plural            = ywpar_get_option( 'points_label_plural' );
$history           = $customer->get_history();
$currency          = ywpar_get_currency();
$level             = ywpar_get_level_badge( $customer->get_level() );
$total_amount      = $points;
$show_share_points = YITH_WC_Points_Rewards_Share_Points::is_enabled();

if ( 'yes' === ywpar_get_option( 'show_point_worth_my_account', 'yes' ) ) {
	$to_redeem = '';
	$to_redeem = yith_points()->redeeming->calculate_price_worth_from_points( $points, $customer );
	$to_redeem = apply_filters( 'ywpar_my_account_worth_value', $to_redeem );
}
?>
<div class="ywpar-wrapper">
	<h2>
		<?php
		// translators:Placeholder: label of points.
		echo wp_kses_post( apply_filters( 'ywpar_my_account_my_points_title', sprintf( _x( 'My %s', 'Placeholder: label of points;', 'yith-woocommerce-points-and-rewards' ), $plural ) ) );
		?>
	</h2>
	<div class="ywpar_myaccount_entry_info">
		<div class="ywpar_summary_badge">
			<span class="ywpar_entry_info_title"><?php echo esc_html( $plural ); ?></span>
			<span class="ywpar_to_redeem_title"><?php echo esc_html_x( 'to redeem', 'sub title inside my account recap points badge', 'yith-woocommerce-points-and-rewards' ); ?></span>
			<span class="points_collected"><?php echo wp_kses_post( $points ); ?></span>
			<?php
			if ( ! empty( $to_redeem ) && 'yes' === ywpar_get_option( 'show_point_worth_my_account', 'yes' ) ) {
				echo '<span class="points_worth">' . esc_html_x( 'worth', 'label to show the worth amount', 'yith-woocommerce-points-and-rewards' ) . ' ' . wp_kses_post( $to_redeem ) . '</span>';
			}
			?>
			<span class="ywpar_total_collected_title"><?php echo esc_html_x( 'total collected:', 'sub title inside my account recap points badge', 'yith-woocommerce-points-and-rewards' ); ?><span><?php echo wp_kses_post( $customer->get_points_collected() ); ?></span></span>
		</div>
		<?php if ( $level && '' !== $level->get_name() && 'on' === $level->get_status() ) : ?>
			<div class="ywpar_levels_badges">
				<span
					class="ywpar_entry_info_title"><?php echo esc_html_x( 'My level', 'My account banner title', 'yith-woocommerce-points-and-rewards' ); ?></span>
				<?php echo wp_kses_post( $level->get_badge_html() ); ?>
			</div>
		<?php endif; ?>
		<?php if ( 'yes' === ywpar_get_option( 'enable_ranking', 'yes' ) && 'yes' === ywpar_get_option( 'show_ranking', 'yes' ) && $customer->get_rank_position() ) : ?>
			<div class="ywpar_rank_badges">
				<span
					class="ywpar_entry_info_title"><?php echo esc_html_x( 'My rank', 'My account banner title', 'yith-woocommerce-points-and-rewards' ); ?></span>
				<span class="rank"><?php echo '#' . $customer->get_rank_position(); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<?php
	/* default title and text for banners */
	$default_title = ywpar_get_banner_precompiled_titles();
	$default_text  = ywpar_get_banner_precompiled_texts();

	$general_args = array(
		'customer' => $customer,
	);

	$target_banners     = (array) apply_filters( 'ywpar_target_banners_array', $customer->get_banners( 'target' ) );
	$get_points_banners = (array) apply_filters( 'ywpar_get_points_banners_array', $customer->get_banners( 'get_points' ) );

	if ( ! $target_banners && ! $history && ! $get_points_banners && ! $show_share_points ) {
		return;
	}
	?>
	<!-- Tab links -->
	<div id="ywpar_tabs">
		<div class="ywpar_tabs_header">
			<ul>
				<?php if ( $history ) : ?>
					<li class="ywpar_tabs_links"
						data-target="history"><?php esc_html_e( 'Points history', 'yith-woocommerce-points-and-rewards' ); ?></li>
				<?php endif; ?>

				<?php if ( $target_banners ) : ?>
					<li class="ywpar_tabs_links"
						data-target="targets"><?php esc_html_e( 'Targets to achieve', 'yith-woocommerce-points-and-rewards' ); ?></li>
				<?php endif; ?>

				<?php if ( $get_points_banners ) : ?>
					<li class="ywpar_tabs_links"
						data-target="getpoints"><?php esc_html_e( 'Get points!', 'yith-woocommerce-points-and-rewards' ); ?></li>
				<?php endif; ?>

				<?php if ( $show_share_points ) : ?>
					<li class="ywpar_tabs_links"
						data-target="share_points"><?php esc_html_e( 'Manage points', 'yith-woocommerce-points-and-rewards' ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="ywpar_tabs_content_container">
		<?php if ( $history ) : ?>
			<div id="history" class="ywpar_tabcontent">
				<!-- Tab content -->

				<table class="shop_table ywpar_points_rewards my_account_orders shop_table_responsive">
					<thead>
					<tr>
						<th class="ywpar_points_rewards-date"><?php esc_html_e( 'Date', 'yith-woocommerce-points-and-rewards' ); ?></th>
						<th class="ywpar_points_rewards-action"><?php esc_html_e( 'Reason', 'yith-woocommerce-points-and-rewards' ); ?></th>
						<th class="ywpar_points_rewards-order"><?php esc_html_e( 'Order No.', 'yith-woocommerce-points-and-rewards' ); ?></th>
						<th class="ywpar_points_rewards-points"><?php echo esc_html( $plural ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $history as $item ) : ?>
						<tr class="ywpar-item">
							<td class="ywpar_points_rewards-date" data-title="<?php esc_attr_e( 'Date', 'yith-woocommerce-points-and-rewards' ); ?>">
								<?php echo wp_kses_post( date_i18n( wc_date_format(), strtotime( $item['date_earning'] ) ) ); ?>
							</td>
							<td class="ywpar_points_rewards-action" data-title="<?php esc_attr_e( 'Reason', 'yith-woocommerce-points-and-rewards' ); ?>">
								<?php echo esc_html( ( $item['description'] ) ? stripslashes( $item['description'] ) : ywpar_get_action_label( $item['action'] ) ); ?>
							</td>
							<td class="ywpar_points_rewards-order" data-title="<?php esc_attr_e( 'Order No.', 'yith-woocommerce-points-and-rewards' ); ?>">
								<?php
								if ( 0 !== $item['order_id'] && 'referral_purchase_exp' !== $item['action'] ) :
									$order = wc_get_order( $item['order_id'] );
									if ( $order ) {
										echo wp_kses_post( '<a href="' . esc_url( $order->get_view_order_url() ) . '">#' . esc_html( $order->get_order_number() ) . '</a>' );
									} else {
										echo esc_html( '-' );
									}
								endif
								?>
							</td>
							<td class="ywpar_points_rewards-points" width="1%" data-title="<?php echo esc_attr( $plural ); ?>">
								<?php
								$type = '-' === substr( $item['amount'], 0, 1 ) ? 'remove' : 'add';

								if ( '-' === substr( $item['amount'], 0, 1 ) ) {
									$class = 'ywpar_minus';
								} else {
									$class          = 'ywpar_plus';
									$item['amount'] = '+' . $item['amount'];
								}

								echo '<span class="' . $class . '">' . wp_kses_post( apply_filters( 'ywpar_product_points_formatted', $item['amount'] ) ) . '</span>' . apply_filters( 'ywpar_product_points_formatted', $total_amount ) . ' ' . $plural;

								if ( 'remove' === $type ) {
									$total_amount = intval( $total_amount ) + substr( $item['amount'], 1, strlen( $item['amount'] - 1 ) );
								} else {
									$total_amount = intval( $total_amount ) - substr( $item['amount'], 1, strlen( $item['amount'] - 1 ) );
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

			</div>
		<?php endif; ?>
		</div>
		<?php if ( $target_banners ) : ?>
			<!-- Tab content -->
			<div id="targets" class="ywpar_tabcontent">
				<?php
				foreach ( $target_banners as $banner ) {
					$banner->get_template();
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( $get_points_banners ) : ?>
			<div id="getpoints" class="ywpar_tabcontent">
				<?php
				foreach ( $get_points_banners as $banner ) {
					$banner->get_template();
				}
				?>
			</div>
		<?php endif; ?>
		<?php if ( $show_share_points ) : ?>
			<?php echo YITH_WC_Points_Rewards_Share_Points::print_tab(); //phpcs:ignore ?>
		<?php endif; ?>
	</div>
</div>
