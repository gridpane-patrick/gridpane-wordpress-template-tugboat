<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );
/**
 * Etheme Admin Panel Plugins Class.
 *
 *
 * @since   7.2.0
 * @version 1.0.0
 *
 */
class Sales_Booster{
	
	// ! Main construct/ add actions
	function __construct(){
	}
	
	public function et_sales_booster_fake_sale_popup_switch_default(){
		$_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'fake_sale_popup', HOUR_IN_SECONDS );
		update_option( 'xstore_sales_booster_settings_fake_sale_popup', $_POST['value']);
		die();
	}
	
	public function et_sales_booster_progress_bar_switch_default(){
		$_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'progress_bar', HOUR_IN_SECONDS);
		update_option( 'xstore_sales_booster_settings_progress_bar', $_POST['value']);
		die();
	}
	
	public function et_sales_booster_request_quote_switch_default(){
		$_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'request_quote', HOUR_IN_SECONDS);
		update_option( 'xstore_sales_booster_settings_request_quote', $_POST['value']);
		die();
	}

    public function et_sales_booster_cart_checkout_countdown_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'cart_checkout', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_cart_checkout_countdown', $_POST['value']);
        die();
    }

    public function et_sales_booster_cart_checkout_progress_bar_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'cart_checkout', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_cart_checkout_progress_bar', $_POST['value']);
        die();
    }

    public function et_sales_booster_fake_live_viewing_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'fake_live_viewing', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_fake_live_viewing', $_POST['value']);
        die();
    }
}

new Sales_Booster();