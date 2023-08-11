<?php
/**
 * Cart Page
 *
 * @package    cart.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

$submit_url =  admin_url('admin-ajax.php?action=xstore_amp_cart_update');
$submit_url = preg_replace('#^https?:#', '', $submit_url);

?>
<h5 class="cart-count"><?php echo $this->cart_count;?> <?php echo _n( 'product', 'products', $this->cart_count, 'xstore-amp' ); ?></h5>
<?php

do_action( 'woocommerce_before_cart' ); ?>

<div class="cart-grid">

	<form class="woocommerce-cart-form" action-xhr="<?php echo esc_url( $submit_url ); ?>" method="post" target="_top">
		<?php do_action( 'woocommerce_before_cart_table' ); ?>
		
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>
			
			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
				
				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<div class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
						
						<div class="product-image">
							<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
							
							if ( ! $product_permalink ) {
								echo $thumbnail; // PHPCS: XSS ok.
							} else {
								printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
							}
							?>
						</div>
						<div class="product-details">
							<div class="product-name">
								<?php
									if ( ! $product_permalink ) {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
									} else {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
									}
									
									do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
									
									// Meta data.
									echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.
									
									// Backorder notification.
									if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'xstore-amp' ) . '</p>', $product_id ) );
									}
								?>
							</div>
							<div class="product-price">
								<?php
									echo $cart_item['quantity'] . ' x ' . apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
								?>
							</div>
							<div class="product-quantity">
								<?php
								if ( $_product->is_sold_individually() ) {
									$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
								} else {
									$product_quantity = woocommerce_quantity_input(
										array(
											'input_name'   => "cart[{$cart_item_key}][qty]",
											'input_value'  => $cart_item['quantity'],
											'max_value'    => $_product->get_max_purchase_quantity(),
											'min_value'    => '0',
											'product_name' => $_product->get_name(),
										),
										$_product,
										false
									);
								}
								
								echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
								?>
							</div>
							<div class="product-remove">
								<?php
									echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										'woocommerce_cart_item_remove_link',
										sprintf(
											'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											esc_html__( 'Remove this item', 'xstore-amp' ),
											esc_attr( $product_id ),
											esc_attr( $_product->get_sku() ),
											esc_html__('Remove item', 'xstore-amp')
										),
										$cart_item_key
									);
								?>
							</div>
						</div>
						
					</div>
					<?php
				}
			}
			?>
			
			<?php do_action( 'woocommerce_cart_contents' ); ?>
		
		<div class="actions flex align-items-center justify-content-between">
			<?php if ( wc_coupons_enabled() ) { ?>
				<div class="coupon flex align-items-center">
					<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'xstore-amp' ); ?>" /> <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply', 'xstore-amp' ); ?>"><?php esc_attr_e( 'Apply', 'xstore-amp' ); ?></button>
					<?php do_action( 'woocommerce_cart_coupon' ); ?>
				</div>
			<?php } ?>
			<button type="submit" class="button bordered" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'xstore-amp' ); ?>"><i class="et-icon et-compare"></i> <?php esc_html_e( 'Update cart', 'xstore-amp' ); ?></button>
			
			<?php do_action( 'woocommerce_cart_actions' ); ?>
			
			<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
		</div>
			
			
			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		
		<?php do_action( 'woocommerce_after_cart_table' ); ?>
		
		<?php $this->form_submitting(); ?>
		
		<?php $this->form_success(); ?>
		
		<?php $this->form_error(); ?>
  
		
	</form>
	
	<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
	
	<div class="cart-collaterals">
		<?php
		/**
		 * Cart collaterals hook.
		 *
		 * @hooked woocommerce_cross_sell_display
		 * @hooked woocommerce_cart_totals - 10
		 */
		// hide shipping methods because there is no posibility to update them for next steps
//		add_filter('woocommerce_cart_ready_to_calc_shipping', '__return_false');
        do_action( 'woocommerce_cart_collaterals' );
        ?>
	</div>
	
</div>

<?php do_action( 'woocommerce_after_cart' );
