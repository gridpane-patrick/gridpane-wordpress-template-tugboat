<?php
/**
 * pre fetching can make the redirect process much faster, as it pre fetch redirect link of all the product on the page which makes the redirect fast as compared to fetching redirect link when product is added to cart
 */
class pi_dcw_enable_prefetch{
    function __construct(){
        add_filter('pisol_dcw_prefetch_redirect', array($this, 'redirectPrefetch'),10);
    }

    function redirectPrefetch($prefetch){
        if($this->pluginPresent()) return true;

        return $prefetch;
    }

    function pluginPresent(){
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        if(is_plugin_active( 'woo-gutenberg-products-block/woocommerce-gutenberg-products-block.php')){
            return true;
        }

        return false;
    }
}

new pi_dcw_enable_prefetch();