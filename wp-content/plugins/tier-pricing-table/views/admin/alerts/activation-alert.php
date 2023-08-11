<?php if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Activation plugin message
 *
 * @var string $link
 */
?>

<div id="message" class="updated notice is-dismissible">
	<p>
		<strong>
			<?php esc_attr__( 'Thanks for installing Tiered Price Table for WooCommerce! You can customize it ', 'tier-pricing-table' ); ?>
			<a href="<?php echo esc_url( $link ); ?>"><?php esc_attr__( 'here', 'tier-pricing-table' ); ?></a>
		</strong>
	</p>
	<button type="button" class="notice-dismiss"></button>
</div>
