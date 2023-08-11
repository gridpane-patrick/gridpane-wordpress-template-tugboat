<?php namespace TierPricingTable\Addons\ManualOrders;

use TierPricingTable\Addons\AbstractAddon;
use TierPricingTable\PriceManager;
use WC_Order_Item_Product;

class ManualOrdersAddon extends AbstractAddon {

	public function getName() {
		return __( 'Manual Orders Addon', 'tier-pricing-table' );
	}

	public function isActive() {
		return apply_filters( 'tier_pricing_table/addons/manual_orders_active', false, $this );
	}

	public function run() {
		add_filter( 'woocommerce_ajax_order_item', array( $this, 'adjustOrderItemPriceSave' ), 3, 10 );
		add_action( 'woocommerce_before_save_order_item', array( $this, 'adjustOrderItemPriceUpdate' ), 1, 10 );
	}

	public function adjustOrderItemPriceSave( $item, $item_id, \WC_Order $order ) {

		if ( $item instanceof WC_Order_Item_Product ) {
			$productId = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
			$qty       = $item->get_quantity();
			$newPrice  = PriceManager::getPriceByRules( $qty, $productId );

			if ( $newPrice ) {
				foreach ( $order->get_items() as $_item ) {
					if ( $item->get_id() === $_item->get_id() && $_item instanceof WC_Order_Item_Product ) {
						$_item->set_total( $newPrice * $qty );
						$_item->set_subtotal( $newPrice * $qty );
						$_item->get_product()->set_price( $newPrice );

						$_item->save();
					}
				}
			}
		}

		return $item;
	}

	public function adjustOrderItemPriceUpdate( \WC_Order_Item $item ) {

		if ( $item instanceof WC_Order_Item_Product ) {

			$productId = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
			$qty       = $item->get_quantity();

			$newPrice = PriceManager::getPriceByRules( $qty, $productId );
			$newPrice = $newPrice ? $newPrice : $item->get_product()->get_price();

			$item->get_product()->set_price( $newPrice );
			$item->set_subtotal( $newPrice * $qty );

			if ( ! $this->isLineItemTotalManuallyChanged( $item->get_id() ) ) {
				$item->set_total( $newPrice * $qty );
			}

			$item->save();
		}

		return $item;
	}

	public function isLineItemTotalManuallyChanged( $itemId ) {
		$data = $_REQUEST;

		$items = isset( $data['items'] ) ? wp_unslash( $data['items'] ) : '';

		parse_str( wp_unslash( $items ), $items );

		if ( ! empty( $items ) ) {
			$total    = isset( $items['line_total'][ $itemId ] ) ? ( $items['line_total'][ $itemId ] ) : false;
			$subtotal = isset( $items['line_subtotal'][ $itemId ] ) ? ( $items['line_subtotal'][ $itemId ] ) : false;

			return $total !== $subtotal;
		}

		return false;
	}
}
