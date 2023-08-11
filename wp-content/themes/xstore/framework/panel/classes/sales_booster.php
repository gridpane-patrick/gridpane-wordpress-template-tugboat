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
		update_option( 'xstore_sales_booster_settings_fake_sale_popup', $_POST['value'] );
		die();
	}
	
	public function et_sales_booster_progress_bar_switch_default(){
		$_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'progress_bar', HOUR_IN_SECONDS);
		update_option( 'xstore_sales_booster_settings_progress_bar', $_POST['value'], 'no');
		die();
	}
	
	public function et_sales_booster_request_quote_switch_default(){
		$_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'request_quote', HOUR_IN_SECONDS);
		update_option( 'xstore_sales_booster_settings_request_quote', $_POST['value'], 'no');
		die();
	}

    public function et_sales_booster_cart_checkout_countdown_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'cart_checkout', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_cart_checkout_countdown', $_POST['value'], 'no');
        die();
    }

    public function et_sales_booster_cart_checkout_progress_bar_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'cart_checkout', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_cart_checkout_progress_bar', $_POST['value'], 'no');
        die();
    }

    public function et_sales_booster_fake_live_viewing_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'fake_live_viewing', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_fake_live_viewing', $_POST['value'], 'yes');
        die();
    }
	
	public function et_sales_booster_fake_product_sales_switch_default(){
		$_POST['value'] = $_POST['value'] == 'false' ? false : true;
		if ( $_POST['value'] )
			set_transient( 'xstore_sales_booster_settings_active_tab', 'fake_product_sales', HOUR_IN_SECONDS);
		update_option( 'xstore_sales_booster_settings_fake_product_sales', $_POST['value'], 'yes');
		die();
	}

    public function et_sales_booster_quantity_discounts_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'quantity_discounts', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_quantity_discounts', $_POST['value'], 'no');
        die();
    }

    public function et_sales_booster_safe_checkout_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'safe_checkout', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_safe_checkout', $_POST['value'], 'no');
        die();
    }

    public function et_sales_booster_account_loyalty_program_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'account', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_account_loyalty_program', $_POST['value'], 'yes');
        die();
    }

    public function et_sales_booster_account_tabs_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'account', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_account_tabs', $_POST['value'], 'yes');
        die();
    }
	
	public function et_sales_booster_floating_menu_switch_default(){
		$_POST['value'] = $_POST['value'] == 'false' ? false : true;
		if ( $_POST['value'] )
			set_transient( 'xstore_sales_booster_settings_active_tab', 'floating_menu', HOUR_IN_SECONDS);
		update_option( 'xstore_sales_booster_settings_floating_menu', $_POST['value'] );
		die();
	}
	
	public function et_sales_booster_estimated_delivery_switch_default(){
		$_POST['value'] = $_POST['value'] == 'false' ? false : true;
		if ( $_POST['value'] )
			set_transient( 'xstore_sales_booster_settings_active_tab', 'estimated_delivery', HOUR_IN_SECONDS);
		update_option( 'xstore_sales_booster_settings_estimated_delivery', $_POST['value'], 'no');
		die();
	}
	
	public function et_sales_booster_customer_reviews_images_switch_default(){
		$_POST['value'] = $_POST['value'] == 'false' ? false : true;
		if ( $_POST['value'] )
			set_transient( 'xstore_sales_booster_settings_active_tab', 'customer_reviews', HOUR_IN_SECONDS);
		update_option( 'xstore_sales_booster_settings_customer_reviews_images', $_POST['value'], 'yes');
		die();
	}

    public function et_sales_booster_customer_reviews_advanced_switch_default(){
        $_POST['value'] = $_POST['value'] == 'false' ? false : true;
        if ( $_POST['value'] )
            set_transient( 'xstore_sales_booster_settings_active_tab', 'customer_reviews', HOUR_IN_SECONDS);
        update_option( 'xstore_sales_booster_settings_customer_reviews_advanced', $_POST['value'], 'yes');
        die();
    }
}

new Sales_Booster();