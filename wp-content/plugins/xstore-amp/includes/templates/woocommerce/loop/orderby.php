<?php
/**
 * Show options for ordering
 *
 * @package    orderby.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

$current_url="http://".esc_attr($_SERVER['HTTP_HOST']).esc_attr($_SERVER['REQUEST_URI']);
$actionXhrUrl = preg_replace('#^https?:#', '', $current_url);

?>
<form class="woocommerce-ordering" id="woocommerce-ordering" action="<?php echo $actionXhrUrl; ?>" method="get" novalidate target="_top">
	<select name="orderby" class="orderby" aria-label="<?php esc_attr_e( 'Shop order', 'xstore-amp' ); ?>" on="change: woocommerce-ordering.submit">
		<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
			<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
		<?php endforeach; ?>
	</select>
	<input type="hidden" name="paged" value="1" />
	<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
</form>