<?php
/**
 * Customer tab view
 *
 * @since   2.2.0
 * @author  YITH
 * @package YITH WooCommerce Points and Rewards
 *
 * @var string $view_type View type: list|history
 * @var string $link Link to customer list.
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="yith-plugin-fw-wp-page__sub-tab-wrap">
	<div id="yith_woocommerce_points_and_rewards_customers" class="wrap yith-plugin-fw">
		<?php if ( 'history' === $view_type ) : ?>
			<a href="<?php echo esc_url( $link ); ?>"
				class="add-new-h2"><?php esc_html_e( '< back to list', 'yith-woocommerce-points-and-rewards' ); ?></a>
		<?php endif ?>
		<div class="yit-admin-panel-content-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Customers\' Points', 'yith-woocommerce-points-and-rewards' ); ?>

			</h1>
			<hr class="wp-header-end">
			<?php require YITH_YWPAR_VIEWS_PATH . '/tabs/customer-' . $view_type . '.php'; ?>
		</div>
	</div>
</div>


