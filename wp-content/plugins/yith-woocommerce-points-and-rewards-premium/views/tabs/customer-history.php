<?php
/**
 * Customer history tab view
 *
 * @since   2.2.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var string                          $view_type View type: list|history
 * @var string                          $link Link to customer list.
 * @var YITH_WC_Points_Rewards_Customer $ywpar_customer
 */

defined( 'ABSPATH' ) || exit;

$email      = sprintf( '<a href="mailto:%1$s">%1$s</a>', $ywpar_customer->get_wc_customer()->get_email() );
$ban_action = $ywpar_customer->is_banned() ? 'unban' : 'ban';
$ban_label  = $ywpar_customer->is_banned() ? __( 'Unban user', 'yith-woocommerce-points-and-rewards' ) : __( 'Ban user', 'yith-woocommerce-points-and-rewards' );
$reset_link = add_query_arg(
	array(
		'action' => 'reset',
		'user'   => $ywpar_customer->get_id(),
	)
);
$max_points_to_remove = apply_filters('ywpar_disable_negative_point', true ) ? $ywpar_customer->get_total_points() : '';
$ban_link = add_query_arg(
	array(
		'action' => $ban_action,
		'user'   => $ywpar_customer->get_id(),
	)
);

$level = ywpar_get_level_badge( $ywpar_customer->get_level() );


?>
<div class="yith-plugin-ui">
	<div class="ywpar-user-info-wrapper">
		<div class="ywpar-user-info-box ywpar-user-box">
			<div class="header-wrap">
				<h2><?php echo esc_html( $ywpar_customer->get_name() ); ?>
					<?php if ( $ywpar_customer->is_banned() ) : ?>
						<span
							class="ywpar-ban"><?php esc_html_e( 'banned', 'yith-woocommerce-points-and-rewards' ); ?></span>
					<?php endif; ?>
				</h2>

				<span
					class="ywpar-ban-unban-wrapper"><?php printf( '<a href="%1$s" class="ywpar_%2$s_user button">%3$s</a>', esc_url( $ban_link ), esc_attr( $ban_action ), esc_html( $ban_label ) ); ?></span>
			</div>
			<ul>
				<li><span
						class="ywpar-user-email"><?php printf( '<strong>%s</strong> %s', esc_html__( 'Email:', 'yith-woocommerce-points-and-rewards' ), wp_kses_post( $email ) ); ?></span>
				</li>
				<?php if ( $level && 'on' === $level->get_status() && '' !== $level->get_name() ) : ?>
					<li><span
							class="user-level"><?php printf( '<strong>%s</strong> %s', esc_html__( 'Level:', 'yith-woocommerce-points-and-rewards' ), esc_html( $level->get_name() ) ); ?></span>
					</li>
				<?php endif; ?>
			</ul>
		</div>
		<div id="ywpar-points-collected" class="ywpar-user-box">
			<div class="wrapper-content">
				<p class="ywpar-label"><?php esc_html_e( 'Points to redeem', 'yith-woocommerce-points-and-rewards' ); ?>
					:</p>
				<p class="ywpar-points"><?php echo esc_html( $ywpar_customer->get_total_points() ); ?></p>
				<p class="ywpar-label"><?php esc_html_e( 'Total collected', 'yith-woocommerce-points-and-rewards' ); ?>
					: <?php echo esc_html( $ywpar_customer->get_points_collected() ); ?></p>
				<p class="ywpar-actions"><?php yith_plugin_fw_get_action_buttons( $this->cpt_obj->get_user_actions() ); ?></p>
			</div>
		</div>
		<div id="ywpar-customer-rank" class="ywpar-user-box">
			<div class="wrapper-content">
				<p class="ywpar-label"><?php esc_html_e( 'Rank', 'yith-woocommerce-points-and-rewards' ); ?>:</p>
				<p class="ywpar-rank">#<?php echo esc_html( $ywpar_customer->get_rank_position() ); ?></p>
			</div>
		</div>
	</div>
</div>

<div class="yith-plugin-ui--yith-pft-boxed-post_type yith-plugin-ui--boxed-wp-list-style">
	<div class="history-table">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Points history', 'yith-woocommerce-points-and-rewards' ); ?></h1>
		<form method="post">
			<input type="hidden" name="page" value="yith_woocommerce_points_and_rewards"/>
			<?php $this->cpt_obj->search_box( __( 'Search', 'yith-woocommerce-points-and-rewards' ), 'search_id' ); ?>
		</form>
		<form id="posts-filter" method="post">
			<?php
			$this->cpt_obj->prepare_items();
			$this->cpt_obj->display();
			?>
		</form>
	</div>
</div>

<?php if ( isset( $_GET['ywpar_dev'] ) ) : ?>
	<div class="ywpar-customer-debug">
	<pre>
	<?php
	$data = $ywpar_customer->get_data();
	foreach ( $data as $k => $d ) :
		echo '<br>';
		if ( is_array( $d ) ) {
			echo $k . ': ' . print_r( $d, 1 ) . '<br>';
		} else {
			echo $k . ': ' . $d;
		}

	endforeach;
	?>
	</pre>
	</div>
<?php endif; ?>
<div class="ywpar-points-collected__popup_wrapper">
	<form method="post" class="ywpar-update-point-form">
		<input type="hidden" name="user_id" value="<?php echo esc_attr( $ywpar_customer->get_id() ); ?>"/>
		<input type="hidden" name="action_type" value="add"/>
		<input type="hidden" name="max_points_to_remove"
			value="<?php echo esc_attr( $max_points_to_remove ); ?>"/>
		<?php wp_nonce_field( 'ywpar_update_points', 'security' ); ?>

		<div class="ywpar-input-wrapper">
			<p>
				<label
					for="points_amount"><?php esc_html_e( 'Points:', 'yith-woocommerce-points-and-rewards' ); ?></label>
				<input type="number" value="" placeholder="1" id="points_amount" name="points_amount" placeholder="0"
					min="1"/>
			</p>
			<p>
				<label
					for="description"><?php esc_html_e( 'Optional description:', 'yith-woocommerce-points-and-rewards' ); ?></label>
				<input type="text" class="description" name="description" id="description"/>
			</p>
		</div>
	</form>
</div>
