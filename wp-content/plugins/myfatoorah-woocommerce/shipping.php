<?php

if (!defined('WPINC')) {
    die;
}
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLoader.php';
require_once MYFATOORAH_WOO_PLUGIN_PATH . 'includes/libraries/MyfatoorahLibrary.php';

class MyfatoorahWoocommerceShipping {
//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct() {

        //Those will be avalible when the shipping is enabled
        $options = get_option('woocommerce_myfatoorah_shipping_settings');
        if (isset($options['enabled']) && $options['enabled'] == 'yes') {
            add_action('wp_enqueue_scripts', [$this, 'my_enqueue']);
            add_action('wp_ajax_get_cities', [$this, 'get_cities'], 1);
            add_action('wp_ajax_nopriv_get_cities', [$this, 'get_cities'], 1);
            add_action('wp_ajax_check_cities_field', [$this, 'check_cities_field'], 1);
            add_action('wp_ajax_nopriv_check_cities_field', [$this, 'check_cities_field'], 1);
            add_filter('woocommerce_checkout_fields', [$this, 'get_cities_first_time2']);
        }

        add_filter('woocommerce_shipping_methods', [$this, 'add_woocommerce_shipping_methods']);
        add_filter('woocommerce_available_payment_gateways', [$this, 'disable_shipping']);

        # add this in your plugin file and that's it, the calculate_shipping method of your shipping plugin class will be called again
        add_action('woocommerce_checkout_update_order_review', [$this, 'action_woocommerce_checkout_update_order_review']);
        add_filter('woocommerce_update_cart_action_cart_updated', [$this, 'clear_notices_on_cart_update'], 10, 1);
        add_action('woocommerce_shipping_init', [$this, 'woocommerce_shipping_init']);
        add_filter('plugin_action_links_' . MYFATOORAH_WOO_PLUGIN, [$this, 'plugin_action_links']);
        
        // unsets currently selected shipping method in checkout page 
        add_action(
                'woocommerce_after_checkout_form',
                array($this, 'reset_previous_chosen_shipping_method')
        );
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function woocommerce_shipping_init() {
        if (!class_exists('WC_Shipping_Myfatoorah')) {
            include_once('includes/shipping/class-wc-shipping-myfatoorah.php');
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Add links to plugins page for settings and documentation
     * @param array $links
     * @return array
     */
    function plugin_action_links($links) {

        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=shipping&section=myfatoorah_shipping') . '">' . __('Shipping', 'woocommerce') . '</a>',
        );
//        return array_merge($plugin_links, $links);
        return array_merge($links, $plugin_links);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function add_woocommerce_shipping_methods($methods) {
        $methods[] = 'WC_Shipping_Myfatoorah';
        return $methods;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @snippet       Disable Other Payment Gateway For MyFatoorah Shipping Method
     */
    function disable_shipping($available_gateways) {
        if (!is_admin() && isset(WC()->session)) {

            $chosen_methods = WC()->session->get('chosen_shipping_methods');

            $chosen_shipping = isset($chosen_methods[0]) ? $chosen_methods[0] : null;

            if ($chosen_shipping != 'myfatoorah_shipping:1' && $chosen_shipping != 'myfatoorah_shipping:2') {
                return $available_gateways;
            }

            foreach ($available_gateways as $key => $val) {
                if ($key != 'myfatoorah_v2' && $key != 'myfatoorah_embedded') {
                    unset($available_gateways[$key]);
                } else {
                    if (!class_exists('WC_Shipping_Myfatoorah')) {
                        include_once('includes/shipping/class-wc-shipping-myfatoorah.php');
                    }
                    $mfShippingObj = new WC_Shipping_Myfatoorah();
                    $calss         = 'WC_Gateway_' . ucfirst($key);
                    $gateway       = new $calss;
                    if ($gateway->apiKey != $mfShippingObj->apiKey) {
                        unset($available_gateways[$key]);
                    }
                }
            }
        }

        return $available_gateways;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function action_woocommerce_checkout_update_order_review() {
        wc_clear_notices();
        WC()->cart->calculate_shipping();
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    //clear notices on cart update
    function clear_notices_on_cart_update() {
        wc_clear_notices();
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function get_cities() {

        if (!class_exists('WC_Shipping_Myfatoorah')) {
            include_once('includes/shipping/class-wc-shipping-myfatoorah.php');
        }
        $mfShippingObj = new WC_Shipping_Myfatoorah();

        // this cond. for ajax call
        try {
            $searchValue = MyFatoorah::filterInputField('term');
            $countryCode = MyFatoorah::filterInputField('country_code');
            if (!$countryCode) {
                die(json_encode(array('success' => false, 'error' => __('MyFatoorah: kindly select a country', 'myfatoorah-woocommerce'))));
            }


            $cities = $mfShippingObj->getCities($countryCode, $searchValue);
            die(json_encode($cities));
        } catch (Exception $ex) {
            die(json_encode(array('success' => false, 'error' => $ex->getMessage())));
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function check_cities_field() {
        $countryCode = MyFatoorah::filterInputField('country_code', 'POST');
        if (!$countryCode) {
            die('input');
        }
        $options = get_option('woocommerce_myfatoorah_shipping_settings');
        if (!empty($options['exe_ship_countries']) && (false !== array_search($countryCode, $options['exe_ship_countries']))) {
            die('input');
        } else {
            die(__('Select Town / City', 'myfatoorah-woocommerce'));
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function get_cities_first_time2($fields) {
        // this for first load of page
        $shippingCC                          = WC()->customer->get_shipping_country();
        $billingCC                           = WC()->customer->get_billing_country();
        $shippingField                       = isset($fields['shipping']['shipping_city']) ? $fields['shipping']['shipping_city'] : array();
        $fields['billing']['billing_city']   = $fields['shipping']['shipping_city'] = $this->get_city_args2($shippingCC, $shippingField);
        if ($billingCC != $shippingCC) {
            $fields['billing']['billing_city'] = $this->get_city_args2($billingCC, $fields['billing']['billing_city'], 'get_billing_city');
        }
        return $fields;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function get_city_args2($countryCode, $field, $function = 'get_shipping_city') {
        $options = get_option('woocommerce_myfatoorah_shipping_settings');
        if (!empty($options['exe_ship_countries']) && (false !== array_search($countryCode, $options['exe_ship_countries']))) {
            return wp_parse_args(array('type' => 'text'), $field);
        } else {
            if (!empty(WC()->customer->$function())) {
                $city = WC()->customer->$function();
                return wp_parse_args(array('type' => 'select', 'options' => array($city => ucwords($city))), $field);
            } else {
                return wp_parse_args(array('type' => 'select', 'options' => array('' => __('Select Town / City', 'myfatoorah-woocommerce'))), $field);
            }
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function my_enqueue() {
        wp_enqueue_script('ajax-script', plugins_url('/assets/js/cities.js', __FILE__), ['jquery'], MYFATOORAH_WOO_PLUGIN_VERSION, true);
        wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

        wp_enqueue_style('select2');
    }
    
//-----------------------------------------------------------------------------------------------------------------------------
    function reset_previous_chosen_shipping_method() {
        if (is_checkout() && !is_wc_endpoint_url()) {
            unset(WC()->session->chosen_shipping_methods);
        }
    }
    
//-----------------------------------------------------------------------------------------------------------------------------------------
}
