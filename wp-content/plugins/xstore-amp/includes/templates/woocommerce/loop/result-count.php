<?php
/**
 * Result Count
 *
 * Shows text: Showing x - x of x results.
 *
 * @package    result-count.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

?>
<p class="woocommerce-result-count">
	<?php
	// phpcs:disable WordPress.Security
	if ( 1 === intval( $total ) ) {
		_e( '1 result', 'xstore-amp' );
	} elseif ( $total <= $per_page || -1 === $per_page ) {
		/* translators: %d: total results */
		printf( _n( '%d Result', '%d Results', $total, 'xstore-amp' ), $total );
	} else {
		$first = ( $per_page * $current ) - $per_page + 1;
		$last  = min( $total, $per_page * $current );
		/* translators: 1: first result 2: last result 3: total results */
		printf( _nx( '%1$d&ndash;%2$d of %3$d', '%1$d&ndash;%2$d of %3$d', $total, 'with first and last result', 'xstore-amp' ), $first, $last, $total );
	}
	// phpcs:enable WordPress.Security
	?>
</p>