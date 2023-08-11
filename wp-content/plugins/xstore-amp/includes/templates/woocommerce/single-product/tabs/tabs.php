<?php
/**
 * Single Product tabs
 *
 * @package    tabs.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */

$xstore_amp = XStore_AMP::get_instance();
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );
$index = 0;

if ( ! empty( $product_tabs ) ) : ?>
	
	<div class="woocommerce-tabs wc-tabs-wrapper">
		
		<amp-accordion animate disable-session-states expand-single-section>
			<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
                <section<?php if ($index < 1){?> expanded<?php } ?>>
                    <header class="accordion-heading">
		                <?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
                    </header>
                    <div class="accordion-content">
                        <?php
                            if ( isset( $product_tab['callback'] ) ) {
                                ob_start();
                                call_user_func( $product_tab['callback'], $key, $product_tab );
	                            echo $xstore_amp->render_amp_content(ob_get_clean());
                            }
                        ?>
                    </div>
                </section>
			<?php $index++;
			endforeach; ?>
		</amp-accordion>
		
		<?php do_action( 'woocommerce_product_after_tabs' ); ?>
	</div>

<?php endif;