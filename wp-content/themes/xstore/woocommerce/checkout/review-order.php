<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

// keep direct check with theme mode because mostly this area is refreshed by ajax and query vars don't work
$cart_checkout_advanced_layout = get_theme_mod('cart_checkout_advanced_layout', false);
$product_image_checkout_details = $cart_checkout_advanced_layout && get_theme_mod('cart_checkout_order_product_images', true);
$product_quantity_checkout_details = $cart_checkout_advanced_layout && get_theme_mod('cart_checkout_order_product_quantity', false);
$product_remove_checkout_details = $cart_checkout_advanced_layout && get_theme_mod('cart_checkout_order_product_remove', false);
$product_link_checkout_details = $cart_checkout_advanced_layout && get_theme_mod('cart_checkout_order_product_link', false);
?>
<table class="shop_table woocommerce-checkout-review-order-table">
    <thead>
    <tr>
        <th class="product-name"><?php esc_html_e( 'Product', 'xstore' ); ?></th>
        <th class="product-total"><?php esc_html_e( 'Subtotal', 'xstore' ); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    do_action( 'woocommerce_review_order_before_cart_contents' );

    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $_product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
        $product_title     = wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) );
        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
        $product_image     = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

        if ( $product_permalink && $product_link_checkout_details ) {
            $product_image = sprintf( '<a href="%1s">%2s</a>', esc_url($product_permalink), $product_image );
            $product_title = sprintf( '<a href="%1s">%2s</a>', esc_url($product_permalink), $product_title );
        }

        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
            ?>
            <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
                <td class="product-name">
                    <?php if ( $product_remove_checkout_details ) : ?>
                    <?php endif; ?>
                    <?php if ( $product_image_checkout_details ) echo $product_image; // phpcs:ignore. ?>
                    <?php if ( $product_image_checkout_details || $product_quantity_checkout_details || $product_remove_checkout_details ) echo '<span class="product-name-info">'; ?>
                        <?php echo $product_title; // phpcs:ignore. ?>
                        <?php
                            if ( $product_quantity_checkout_details ) {
                                ob_start(); ?>
                                    <div class="quantity-wrapper clearfix">
                                        <?php woocommerce_quantity_input(
                                            array(
                                                'input_name'   => "cart[{$cart_item_key}][qty]",
                                                'input_value'  => $cart_item['quantity'],
                                                'max_value'    => apply_filters('woocommerce_quantity_input_max', $_product->get_max_purchase_quantity(), $_product),
                                                'min_value'    => apply_filters('woocommerce_quantity_input_min', 0, $_product),
                                                'product_name' => $_product->get_name(),
                                            ),
                                            $_product,
                                            true
                                        ); ?>
                                    </div>
                                <?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ob_get_clean(), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            }
                            else
                                echo '&nbsp;' . apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                        <?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php
                        if ( $product_remove_checkout_details ) {
                            echo apply_filters('woocommerce_cart_item_remove_link',
                                sprintf(
                                    '<a href="%s" class="remove-item text-underline" title="%s">%s</a>',
                                    esc_url(wc_get_cart_remove_url($cart_item_key)),
                                    /* translators: %s is the product name */
                                    esc_attr( sprintf( __( 'Remove %s from cart', 'xstore' ), $product_title ) ),
                                    esc_html__('Remove', 'xstore')
                                ),
                                $cart_item_key);
                        }
                        ?>
                    <?php if ( $product_image_checkout_details || $product_quantity_checkout_details || $product_remove_checkout_details ) echo '</span>'; ?>
                </td>
                <td class="product-total">
                    <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </td>
            </tr>
            <?php
        }
    }

    do_action( 'woocommerce_review_order_after_cart_contents' );
    ?>
    </tbody>
    <tfoot>

    <tr class="cart-subtotal">
        <th><?php esc_html_e( 'Subtotal', 'xstore' ); ?></th>
        <td><?php wc_cart_totals_subtotal_html(); ?></td>
    </tr>

    <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
        <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
            <th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
            <td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
        </tr>
    <?php endforeach; ?>

    <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

        <?php do_action( 'woocommerce_review_order_before_shipping' );
        if ( $cart_checkout_advanced_layout && get_theme_mod('cart_checkout_layout_type', 'default') != 'default') add_filter('etheme_show_chosen_shipping_method', '__return_true'); ?>

        <?php wc_cart_totals_shipping_html(); ?>

        <?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

    <?php endif; ?>

    <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
        <tr class="fee">
            <th><?php echo esc_html( $fee->name ); ?></th>
            <td><?php wc_cart_totals_fee_html( $fee ); ?></td>
        </tr>
    <?php endforeach; ?>

    <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
        <?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
            <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
                <tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                    <th><?php echo esc_html( $tax->label ); ?></th>
                    <td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr class="tax-total">
                <th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
                <td><?php wc_cart_totals_taxes_total_html(); ?></td>
            </tr>
        <?php endif; ?>
    <?php endif; ?>

    <?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

    <tr class="order-total">
        <th><?php esc_html_e( 'Total', 'xstore' ); ?></th>
        <td><?php wc_cart_totals_order_total_html(); ?></td>
    </tr>

    <?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

    </tfoot>
</table>
