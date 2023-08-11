<?php
/**
 * HTML Template Email YITH WooCommerce Points and Rewards
 *
 * @package YITH WooCommerce Points and Rewards
 * @since   1.0.0
 * @version 1.1.3
 * @author  YITH
 *
 * @var string $email_heading
 * @var string $email_content
 * @var WC_Email $email
 */

// DO_ACTION : woocommerce_email_header : action to insert the WooCommerce email header.
do_action( 'woocommerce_email_header', $email_heading, $email );
?>
	<style>
		.points_banner {
			background: #ebebeb url('<?php echo YITH_YWPAR_ASSETS_URL; ?>/images/email_expiration.svg') no-repeat;
			background-position: 15px 15px;
			background-size: 32px;
			padding: 10px 20px 10px 60px;
			margin-bottom: 10px;
		}

		.points_banner a {
			text-decoration: none !important;
		}
	</style>

<?php echo wp_kses_post( wpautop( $email_content ) ); ?>

<?php
// DO_ACTION : woocommerce_email_footer : action to insert the WooCommerce email footer.
do_action( 'woocommerce_email_footer', $email );

