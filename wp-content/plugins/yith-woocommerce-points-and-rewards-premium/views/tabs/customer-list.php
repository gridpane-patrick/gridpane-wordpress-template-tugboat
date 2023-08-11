<?php
/**
 * Customer list tab view
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
<div class="yith-plugin-ui--yith-pft-boxed-post_type yith-plugin-ui--boxed-wp-list-style ywpar-customer-list">
	<form method="post">
		<input type="hidden" name="page" value="yith_woocommerce_points_and_rewards" />
		<?php $this->cpt_obj->search_box( __( 'Search', 'yith-woocommerce-points-and-rewards' ), 'search_id' ); ?>
	</form>
	<form id="posts-filter" method="post">
		<?php
		$this->cpt_obj->prepare_items();
		$this->cpt_obj->display();
		?>
	</form>
</div>

