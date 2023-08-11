<?php
/**
 * My Account page
 *
 * @package    my-account.php
 * @since      1.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @version    1.0.0
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="woocommerce-MyAccount-content-wrapper">

<?php
    /**
     * My Account navigation.
     *
     * @since 2.6.0
     */
    do_action( 'woocommerce_account_navigation' ); ?>
    
    <div class="woocommerce-MyAccount-content">
        <?php
        /**
         * My Account content.
         *
         * @since 2.6.0
         */
        do_action( 'woocommerce_account_content' );
        ?>
    </div>
    
</div>