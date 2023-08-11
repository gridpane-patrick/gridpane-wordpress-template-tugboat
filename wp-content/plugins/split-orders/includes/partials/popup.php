<?php
/**
 * Template file for the order splitting modal controls
 *
 * @var WC_Order_Item_Product[] $items
 * @var WC_Order                $order
 * @var array                   $notices
 */

use Vibe\Split_Orders\Admin;
use Vibe\Split_Orders\Split_Orders;

defined( 'ABSPATH' ) || exit;
?>

<div id="split-orders-popup" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">

	<?php if ( $notices ) : ?>
		<div id="split-orders-popup-notices">
			<?php Admin::output_notices( $notices ); ?>
		</div>
	<?php endif; ?>

	<table class="widefat">
		<thead>
		<tr>
			<th class="item-name"><?php esc_html_e( 'Product', 'split-orders' ); ?></th>
			<th class="item-quantity"><?php esc_html_e( 'Quantity', 'split-orders' ); ?></th>
			<th class="item-split-quantity"><?php esc_html_e( 'Quantity to Split', 'split-orders' ); ?></th>
		</tr>
		</thead>

		<tbody>
		<?php foreach ( $items as $item ) : ?>

			<?php
			$product = $item->get_product();
			$thumbnail = $product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item->get_id(), $item ) : '';
			?>

			<tr class="item" data-item-id="<?php echo esc_attr( $item->get_id() ); ?>">
				<td>
					<div class="thumbnail"><?php echo wp_kses_post( $thumbnail ); ?></div>

					<div class="item-details">
						<span class="name"><?php echo esc_html( $item->get_name() ); ?></span>

						<?php if ( $product && $product->get_sku() ) : ?>

							<span class="property sku">
								<strong><?php esc_html_e( 'SKU:', 'split-orders' ); ?></strong> <?php echo esc_html( $item->get_product()->get_sku() ); ?>
							</span>

						<?php endif; ?>

						<?php if ( $item->get_variation_id() ) : ?>

							<span class="property variation">
								<strong><?php esc_html_e( 'Variation ID:', 'split-orders' ); ?></strong> <?php echo esc_html( $item->get_variation_id() ); ?>
							</span>

						<?php endif; ?>

						<?php do_action( Split_Orders::hook_prefix( 'after_item_details' ), $product, $order ); ?>
					</div>
				</td>
				<td>
					<small class="times">×</small>
					<?php echo esc_html( wc_stock_amount( $item->get_quantity() ) ); ?>
				</td>
				<td>
					<input class="qty-split" type="number" min="0" max="<?php echo esc_attr( $item->get_quantity() ); ?>" step="<?php echo esc_attr( apply_filters( 'woocommerce_quantity_input_step', 1, $item->get_product() ) ); ?>" value="<?php echo esc_attr( apply_filters( Split_Orders::hook_prefix( 'default_split_quantity' ), 0, $order, $item ) ); ?>" title="Quantity" size="4" autocomplete="off">
				</td>
			</tr>

		<?php endforeach; ?>

		</tbody>

	</table>

</div>
