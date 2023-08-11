<?php
/**
 * Plugin Name: WooCommerce Accept Payments
 * Plugin URI: https://accept.paymob.com/
 * Description: Adds powerful payment methods to your WooCommerce store.
 * Version: 5.7.1
 * Author: Accept
 * Author URI: https://accept.paymob.com/
 * Copyright: © 2018 ~ 2019 Accpet
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

define('ACCEPT_PLUGIN_VERSION', '5.7.1');

class WC_Accept_Payments
{
    public function __construct()
    {
        $this->kiosk = get_option('woocommerce_accept-kiosk_settings');
        $this->online = get_option('woocommerce_accept-online_settings');
        $this->lucky = get_option('woocommerce_accept-lucky_settings');
        $this->valu = get_option('woocommerce_accept-valu_settings');
        $this->wallet = get_option('woocommerce_accept-wallet_settings');
        $this->installments = get_option('woocommerce_accept-installments_settings');
        $this->sympl = get_option('woocommerce_accept-sympl_settings');
        $this->premium = get_option('woocommerce_accept-premium_settings');
        $this->souhoola = get_option('woocommerce_accept-souhoola_settings');
        $this->shahry = get_option('woocommerce_accept-shahry_settings');
        $this->get_go = get_option('woocommerce_accept-get_go_settings');
        $this->forsa = get_option('woocommerce_accept-forsa_settings');
        
        // only display the notice when the user allows the control
        // if ($this->get_go['force_notice'] == "yes" ||$this->shahry['force_notice'] == "yes" ||$this->installments['force_notice'] == "yes" ||$this->souhoola['force_notice'] == "yes"||$this->premium['force_notice'] == "yes"|| $this->sympl['force_notice'] == "yes" || $this->online['force_notice'] == "yes" || $this->valu['force_notice'] == "yes" || $this->wallet['force_notice'] == "yes"|| $this->kiosk['force_notice'] == "yes")
        // {
        //     add_filter('woocommerce_thankyou', array($this, 'notice'), 10);
        // }

        add_action('init', array($this, 'init_gateway'));
        add_action('wp_footer', array($this, 'render'));
        add_filter('woocommerce_default_address_fields', array($this, 'custom_address_fields'));
        add_filter('woocommerce_checkout_fields', array($this, 'add_address_fields'));
        add_action('woocommerce_api_wc-accept-version', array($this, 'debug'));
    }

    public function debug()
    {
        echo ACCEPT_PLUGIN_VERSION;
        exit;
    }

    /**
     * Displays the WooCommerce Notices into the order page, as its not supported by some themes.
     * @since 5.4.0
     */
    public function notice(){
        wc_print_notices();
    }

    public function render()
    {
        if (!is_admin()) {
            if ( is_page('ACCEPT PAYMENTS') )
            {
                $script_url = plugins_url('/woo-accept/assets/js/scripts.js');
                echo "<script src='$script_url'></script>";
            }
        }
    }

    public function init_gateway()
    {

        global $woocommerce;

        if (!class_exists('WC_Payment_Gateway')) {
            echo "Sorry this plugin needs WooCommerce installed on your website.";
            return;
        }
        include_once 'helpers/admin-helper.php';
        include_once 'helpers/pay-helper.php';
        include_once 'gateways/online.php';
        include_once 'gateways/kiosk.php';
        include_once 'gateways/wallet.php';
        include_once 'gateways/valu.php';
        include_once 'gateways/installments.php';
        include_once 'gateways/sympl.php';
        include_once 'gateways/premium.php';
        include_once 'gateways/souhoola.php';
        include_once 'gateways/shahry.php';
        include_once 'gateways/get_go.php';
        include_once 'gateways/forsa.php';
        include_once 'gateways/lucky.php';



        add_filter('woocommerce_payment_gateways', array($this, 'add_accept_gateways'));
    }

    public function add_accept_gateways($methods)
    {
        $methods[] = 'WC_Gateway_Accept_Online';
        $methods[] = 'WC_Gateway_Accept_Kiosk';
        $methods[] = 'WC_Gateway_Accept_Wallet';
        $methods[] = 'WC_Gateway_Accept_Valu';
        $methods[] = 'WC_Gateway_Accept_installments';
        $methods[] = 'WC_Gateway_Accept_Sympl';
        $methods[] = 'WC_Gateway_Accept_Premium';
        $methods[] = 'WC_Gateway_Accept_Souhoola';
        $methods[] = 'WC_Gateway_Accept_Shahry';
        $methods[] = 'WC_Gateway_Accept_Get_go';
        $methods[] = 'WC_Gateway_Accept_Forsa';
        $methods[] = 'WC_Gateway_Accept_Lucky';


        return $methods;
    }

    public function custom_address_fields($fields)
    {
        $fields['address_1'] = array(
            'label'    => __('Street Number', 'woocommerce'),
            'class'    => array('form-row-wide', 'address-field'),
            'required' => true,
            'priority' => 40,
        );

        $fields['address_2'] = array(
            'placeholder' => __('More Address information', 'woocommerce'),
            'class'       => array('form-row-wide', 'address-field'),
            'required'    => false,
            'priority'    => 50,
        );

        $fields['city'] = array(
            'label'    => __('City'),
            'class'    => array('form-row-wide', 'address-field'),
            'required' => true,
        );

        $fields['postcode'] = array(
            'label'    => __('Postcode', 'woocommerce'),
            'class'    => array('form-row-wide', 'address-field'),
            'required' => false,
            'priority' => 80,
        );

        $fields['state'] = array(
            'label'    => __('State', 'woocommerce'),
            'class'    => array('form-row-wide', 'address-field'),
            'required' => false,
            'priority' => 90,
        );

        return $fields;
    }

    public function add_address_fields($fields)
    {
        $fields['billing']['billing_phone'] = array(
            'label'       => __('Phone', 'woocommerce'),
            'placeholder' => _x('Phone', 'placeholder', 'woocommerce'),
            'class'       => array('form-row-wide', 'address-field'),
            'required'    => true,
            'priority'    => 21,
        );

        $fields['billing']['billing_email'] = array(
            'label'       => __('Email', 'woocommerce'),
            'placeholder' => _x('Email', 'placeholder', 'woocommerce'),
            'class'       => array('form-row-wide', 'address-field'),
            'required'    => true,
            'priority'    => 22,
        );

        $fields['shipping']['shipping_phone'] = array(
            'label'       => __('Phone', 'woocommerce'),
            'placeholder' => _x('Phone', 'placeholder', 'woocommerce'),
            'class'       => array('form-row-wide', 'address-field'),
            'clear'       => true,
            'required'    => true,
            'priority'    => 21,
        );

        $fields['shipping']['shipping_email'] = array(
            'label'       => __('Email', 'woocommerce'),
            'placeholder' => _x('Email', 'placeholder', 'woocommerce'),
            'class'       => array('form-row-wide', 'address-field'),
            'clear'       => true,
            'required'    => true,
            'priority'    => 22,
        );
        return $fields;
    }
}

$GLOBALS['wc_accept_payments'] = new WC_Accept_Payments();
register_activation_hook(__FILE__, 'install');
function install()
{
    global $wpdb;
    $content = '
        <style>
            /**
             * @Author Accept.
             * @Description CSS for ACCEPT PAYMENTS page.
             * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
             * It is better not to edit the contents of this page unless you know (HTML, CSS).
             *
            */
            #accepting-container div {display:none;}
            #accepting-container div {text-align:center;}
            #accepting-container {width:100%;margin:0.2rem auto;min-width:300px;min-height:100vh;}
            #accepting-container .accept_error h1 {color:#e91e63;}
            #accepting-container .accept_iframe iframe {border:none !important;width:100%;min-height:100vh;}
            #accepting-container .accept_kiosk h1 {padding:0;color:#4caf50;}
            #accepting-container .accept_kiosk p {color:#03a9f4;}
        </style>
        <div id="accepting-container">
            <div class="accept_error">
                <h1>Something went wrong, Please contact the store owner.</h1>
                <p>
                <code>Error code: EMPTY_SESSION</code>
                </p>
            </div>
            <div class="accept_iframe"></div>
            <div class="accept_kiosk">
                <h2>Your Aman bill reference is</h2>
                <h1 id="accept_kiosk_id"></h1>
                <p>To pay, Please go to the nearest Aman or Masary outlet, ask for "مدفوعات اكسبت/ Madfouaat Accept" and provide your reference number.</p>
            	<p> طريقة الدفع: رجاء التوجه إلى أقرب فرع أمان أو محل به ماكينة أمان أومصارى و أسأل عن "مدفوعات اكسبت" و أخبرهم بالرقم المرجعي</p>
 
	</div>
        </div>
    ';

    if (get_page_by_path('accept-payments') == null) {
        wp_insert_post(
            array(
                'page_template'  => '',
                'comment_status' => 'closed',
                'post_date'      => date('Y-m-d H:i:s'),
                'post_title'     => 'ACCEPT PAYMENTS',
                'post_name'      => 'accept-payments',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_content'   => "$content",
            )
        );
    }else{
        $update_page = array(
            'ID'           => get_page_by_title('ACCEPT PAYMENTS')->ID,
            'post_content' => "$content",
        );
        wp_update_post( $update_page );
    }

    $table_name      = $wpdb->prefix . 'accept_cards_token';
    $charset_collate = $wpdb->get_charset_collate();
    $sql             = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		token varchar(56) DEFAULT '' NOT NULL,
		masked_pan varchar(19) DEFAULT '' NOT NULL,
		card_subtype varchar(56) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id),
		KEY user_id (user_id)
	) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
