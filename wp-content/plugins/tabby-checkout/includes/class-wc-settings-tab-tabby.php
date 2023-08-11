<?php
class WC_Settings_Tab_Tabby {

    public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', array(__CLASS__, 'add_settings_tab'), 50 );

        add_action( 'woocommerce_settings_tabs_settings_tab_tabby' , array(__CLASS__, 'tabby_settings_tab'   ) );
        add_action( 'woocommerce_update_options_settings_tab_tabby', array(__CLASS__, 'tabby_update_settings') );

        add_action( 'woocommerce_admin_settings_sanitize_option_tabby_checkout_public_key', array(__CLASS__, 'sanitize_public_key') );
        add_action( 'woocommerce_admin_settings_sanitize_option_tabby_checkout_secret_key', array(__CLASS__, 'sanitize_secret_key') );
    }

    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['settings_tab_tabby'] = __( 'Tabby API', 'tabby-checkout' );
        return $settings_tabs;
    }

    public static function tabby_settings_tab() {
        echo "<style>#tabby_countries option {padding: 10px;} input:invalid { border: red solid 3px;}</style>";
        echo self::getSettingsValidationScript();
        woocommerce_admin_fields( static::tabby_checkout_api_settings([], 'tabby_api') );
    }

    public static function tabby_update_settings() {
        woocommerce_update_options( static::tabby_checkout_api_settings([], 'tabby_api') );
        WC_Tabby_Webhook::register();
    }

    public static function sanitize_public_key($value) {
        $value = trim($value);
        $msg = null;
        if (!preg_match("#^pk_(test_)?[\da-f]{8}\-[\da-f]{4}\-[\da-f]{4}\-[\da-f]{4}\-[\da-f]{12}$#", $value)) {
            $msg = '<div id="message" class="notice notice-error is-dismissible"><p>Wrong PUBLIC key format. Must be: <b>pk_[test_]xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</b>.</p></div>';    
            $value = '';
        }
        add_action( 'admin_notices', function() use($msg) {
            if ($msg) echo $msg;
        });
        return $value;
    }

    public static function sanitize_secret_key($value) {
        $value = trim($value);
        $msg = null;
        if (!preg_match("#^sk_(test_)?[\da-f]{8}\-[\da-f]{4}\-[\da-f]{4}\-[\da-f]{4}\-[\da-f]{12}$#", $value)) {
            $msg = '<div id="message" class="notice notice-error is-dismissible"><p>Wrong SECRET key format. Must be: <b>sk_[test_]xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</b>.</p></div>';    
            $value = '';
        }
        add_action( 'admin_notices', function() use($msg) {
            if ($msg) echo $msg;
        });
        return $value;
    }

    public static function tabby_checkout_api_settings( $settings, $current_section ) {
        if ( $current_section == 'tabby_api' ) {

            $settings_tabby = array();
            $settings_tabby[] = array( 
                'name' => __( 'Tabby Checkout API', 'tabby-checkout' ), 
                'type' => 'title', 
                'desc' => __( 'The following options are used to configure Tabby Checkout API', 'tabby-checkout' ), 
                'id' => 'tabby-checkout'
            );
            $settings_tabby[] = array(
                'name'     => __( 'Merchant Public Key', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_public_key',
                'type'     => 'password',
                'desc'     => __( 'Used for public API calls<br /><b>pk_[test_]xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</b>', 'tabby-checkout' ),
                'custom_attributes' => array(
                    'minlength' => 39,
                    'maxlength' => 44
                ),
            );
            $settings_tabby[] = array(
                'name'     => __( 'Merchant Secret Key', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_secret_key',
                'type'     => 'password',
                'desc'     => __( 'Used for server to server calls<br /><b>sk_[test_]xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</b>', 'tabby-checkout' ),
                'custom_attributes' => array(
                    'minlength' => 39,
                    'maxlength' => 44
                ),
            );
            $settings_tabby[] = array(
                'name'     => __( 'Tabby Language', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_popup_language',
                'type'     => 'select',
                'desc'     => __( 'Tabby popup language', 'tabby-checkout' ),
                'options'  => [
                    'auto'  => __('Auto', 'tabby-checkout'),
                    'en'    => __('English', 'tabby-checkout'),
                    'ar'    => __('Arabic', 'tabby-checkout')
                ],
                'default'   => 'auto'
            );
            $settings_tabby[] = array(
                'name'     => __( 'Tabby order timeout, mins', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_order_timeout',
                'type'     => 'text',
                'desc'     => __( 'Used for deleting unpaid orders', 'tabby-checkout' ),
                'type'              => 'number',
                'custom_attributes' => array(
                    'min'  => 0,
                    'step' => 1,
                ),
                'css'               => 'width: 80px;',
                'default'           => '20',
                'autoload'          => false,
            );
            $settings_tabby[] = array(
                'name'     => __( 'Action on Failed Payment', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_failed_action',
                'type'     => 'text',
                'desc'     => __( 'Delete or cancel unpaid orders', 'tabby-checkout' ),
                'type'     => 'select',
                'options'  => [
                    'delete' => __('Cancel & Delete', 'tabby-checkout'),
                    'trash'  => __('Cancel & Move to Trash', 'tabby-checkout'),
                    'cancel' => __('Cancel Only', 'tabby-checkout')
                ],
                'css'               => 'width: 180px;',
                'default'           => 'delete'
            );
            $settings_tabby[] = array(
                'name'     => __( 'Use phone number for order history lookup', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_use_phone',
                'type'     => 'checkbox',
                'desc'     => __( 'Add order history by phone', 'tabby-checkout' ),
                'default'  => 'yes'
            );
            $settings_tabby[] = array(
                'name'     => __( 'Capture payment on checkout', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_capture',
                'type'     => 'checkbox',
                'desc'     => __( 'Enable payment capture on checkout', 'tabby-checkout' ),
                'default'  => 'yes'
            );
            $settings_tabby[] = array(
                'name'     => __( 'Tabby promotions (product)', 'tabby-checkout' ),
                'id'       => 'tabby_promo',
                'type'     => 'checkbox',
                'desc'     => __( 'Enable Tabby promo on product view pages', 'tabby-checkout' ),
                'default'   => 'yes'
            );
            $settings_tabby[] = array(
                'name'     => __( 'Tabby promotions (cart page)', 'tabby-checkout' ),
                'id'       => 'tabby_promo_cart',
                'type'     => 'checkbox',
                'desc'     => __( 'Enable Tabby promo on shopping cart page', 'tabby-checkout' ),
                'default'   => 'yes'
            );
            $settings_tabby[] = array(
                'name'     => __( 'Minimum price for Tabby promo', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_promo_min_total',
                'type'     => 'text',
                'desc'     => __( 'Minimum price showing Tabby promotions, 0 for unlimited', 'tabby-checkout' ),
                'type'              => 'number',
                'custom_attributes' => array(
                    'min'  => 0,
                    'step' => 1,
                ),
                'css'               => 'width: 80px;',
                'default'           => '0',
                'autoload'          => false,
            );
            $settings_tabby[] = array(
                'name'     => __( 'Tabby promo max price', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_promo_price',
                'type'     => 'text',
                'desc'     => __( 'Maximum product price for showing Tabby promotions, 0 for unlimited', 'tabby-checkout' ),
                'type'              => 'number',
                'custom_attributes' => array(
                    'min'  => 0,
                    'step' => 1,
                ),
                'css'               => 'width: 80px;',
                'default'           => '0',
                'autoload'          => false,
            );
/*
            $settings_tabby[] = array(
                'name'     => __( 'Promotion snippets (additional settings)', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_promo_type_price',
                'type'     => 'text',
                'desc'     => __( 'Please use this settings only in case if you have the recommendations from Tabby team.<br />
<br />
Set up -1 for Standard promotion snippets as a default value<br />
Set up 0 for Credit Card installments promotion snippets as a default value<br />
Set up the limit between 0 - 50000 to show Standard promotion snippets for the amount which is  lower than the limit and Credit Card installments promotion snippets for the amount which is higher than the limit. ', 'tabby-checkout' ),
                'type'              => 'number',
                'custom_attributes' => array(
                    'min'  => -1,
                    'step' => 1,
                ),
                'css'               => 'width: 80px;',
                'default'           => '-1',
                'autoload'          => false,
            );
*/
            $settings_tabby[] = array(
                'name'     => __( 'Tabby promotions theme', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_promo_theme',
                'type'     => 'text',
                'desc'     => __( 'Used for styling Tabby promotions widget (blank for default)', 'tabby-checkout' ),
            );
            $countries = new WC_Countries();
            $options = [];
            foreach (WC_Tabby_Config::ALLOWED_COUNTRIES as $code) {
                $options[$code] = __($countries->countries[$code], 'tabby-checkout'); 
            }
            asort($options);
            $settings_tabby[] = array(
                'name'     => __( 'Allowed Countries', 'tabby-checkout' ),
                'id'       => 'tabby_countries',
                'type'     => 'multiselect',
                'desc'     => __( 'Tabby allowed countries', 'tabby-checkout' ),
                'options'  => $options,
                'default'  => WC_Tabby_Config::ALLOWED_COUNTRIES
            );
            $settings_tabby[] = array(
                'name'     => __( 'Use <html> tag lang', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_locale_html',
                'type'     => 'checkbox',
                'desc'     => __( 'Use documentElement lang attribute for tabby language', 'tabby-checkout' ),
            );
            $settings_tabby[] = array(
                'name'     => __( 'Hide methods', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_hide_methods',
                'type'     => 'checkbox',
                'desc'     => __( 'Hide Tabby payment methods on checkout if not available', 'tabby-checkout' ),
            );
            $settings_tabby[] = array(
                'name'     => __( 'Debug', 'tabby-checkout' ),
                'id'       => 'tabby_checkout_debug',
                'type'     => 'checkbox',
                'desc'     => __( 'Enable API request/reply logging', 'tabby-checkout' ),
                'default'  => 'yes'
            );
            
            $settings_tabby[] = array( 'type' => 'sectionend', 'id' => 'tabby_api' );
            return $settings_tabby;
        
        } else {
            return $settings;
        }
    }
    static public function getSettingsValidationScript() {
        return '
<script type="text/javascript">
jQuery(document).ready(function () {
    jQuery("#tabby_checkout_public_key, #tabby_checkout_secret_key").change(function (el) {
        let public_key = jQuery("#tabby_checkout_public_key");
        let secret_key = jQuery("#tabby_checkout_secret_key");
        let error = false;
        public_key.parent().children(".description").children(".notice").remove();
        if (!/^pk_(test_)?[\da-f]{8}\-[\da-f]{4}\-[\da-f]{4}\-[\da-f]{4}\-[\da-f]{12}$/.test(public_key.val())) {
            public_key.parent().children(".description").prepend(
                jQuery("<div>").addClass("notice notice-error").html("Wrong public key format.")
            );
            error ||= true;
        } else {
            public_key.parent().children(".description").prepend(
                jQuery("<div>").addClass("notice notice-success").html("Public key format is correct.")
            );
        }
        secret_key.parent().children(".description").children(".notice").remove();
        if (!/^sk_(test_)?[\da-f]{8}\-[\da-f]{4}\-[\da-f]{4}\-[\da-f]{4}\-[\da-f]{12}$/.test(secret_key.val())) {
            secret_key.parent().children(".description").prepend(
                jQuery("<div>").addClass("notice notice-error").html("Wrong secret key format.")
            );
            error ||= true;
        } else {
            secret_key.parent().children(".description").prepend(
                jQuery("<div>").addClass("notice notice-success").html("Secret key format is correct.")
            );
        }
        if (error) {
            jQuery(".button-primary.woocommerce-save-button").attr("disabled", "disabled");
        } else {
            jQuery(".button-primary.woocommerce-save-button").removeAttr("disabled");
        }
    });
});
</script>
 ';
    }

}
