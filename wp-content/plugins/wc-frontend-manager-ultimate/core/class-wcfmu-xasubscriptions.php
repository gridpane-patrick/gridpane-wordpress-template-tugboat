<?php

/**
 * WCFMu plugin core
 *
 * XA Subscriptions Support
 *
 * @author  WC Lovers
 * @package wcfmu/core
 * @version 4.1.0
 */

class WCFMu_XASubscriptions
{

    /**
     * Billing fields.
     *
     * @var array
     */
    protected static $billing_fields = [];

    /**
     * Shipping fields.
     *
     * @var array
     */
    protected static $shipping_fields = [];


    public function __construct()
    {
        global $WCFM, $WCFMu;

        if (wcfm_is_xa_subscription()) {
            // WC Subscriptions Query Var Filter
            add_filter('wcfm_query_vars', [ &$this, 'wcs_wcfm_query_vars' ], 20);
            add_filter('wcfm_endpoint_title', [ &$this, 'wcs_wcfm_endpoint_title' ], 20, 2);
            add_action('init', [ &$this, 'wcs_wcfm_init' ], 20);

            // Subscriptions Endpoint Edit
            add_filter('wcfm_endpoints_slug', [ $this, 'wcs_wcfm_endpoints_slug' ]);

            // WC Subscriptions Menu Filter
            add_filter('wcfm_menus', [ &$this, 'wcs_wcfm_menus' ], 20);

            // Subscriptions Product Type
            // add_filter( 'wcfm_product_types', array( &$this, 'wcs_product_types' ), 40 );
            // Subscriptions Load WCFMu Scripts
            add_action('wcfm_load_scripts', [ &$this, 'wcs_load_scripts' ], 30);

            // Subscriptions Load WCFMu Styles
            add_action('wcfm_load_styles', [ &$this, 'wcs_load_styles' ], 30);

            // Subscriptions Load WCFMu views
            add_action('wcfm_load_views', [ &$this, 'wcs_load_views' ], 30);

            // Subscriptions Ajax Controllers
            add_action('after_wcfm_ajax_controller', [ &$this, 'wcs_ajax_controller' ]);

            // Subscriptions Product options
            // add_filter( 'wcfm_product_manage_fields_general', array( &$this, 'wcs_product_manage_fields_general' ), 40, 2 );
            // add_filter( 'wcfm_product_manage_fields_shipping', array( &$this, 'wcs_product_manage_fields_shipping' ), 40, 2 );
            // add_filter( 'wcfm_product_manage_fields_advanced', array( &$this, 'wcs_product_manage_fields_advanced' ), 40, 2 );
            // add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcs_product_manage_fields_variations' ), 40, 4 );
            // Subscriptions Product Meta Data Save
            // add_action( 'after_wcfm_products_manage_meta_save', array( &$this, 'wcs_wcfm_product_meta_save' ), 40, 2 );
            // add_action( 'after_wcfm_product_variation_meta_save', array( &$this, 'wcs_product_variation_save' ), 40, 4 );
            // Subscription Product Date Edit
            // add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcs_product_data_variations' ), 40, 3 );
            // Subscription Status Update
            add_action('wp_ajax_wcfm_modify_subscription_status', [ &$this, 'wcfm_modify_subscription_status' ]);
        }//end if

    }//end __construct()


    /**
     * WC Subscriptions Query Var
     */
    function wcs_wcfm_query_vars($query_vars)
    {
        $wcfm_modified_endpoints = wcfm_get_option('wcfm_endpoints', []);

        $query_subscriptions_vars = [
            'wcfm-subscriptions'        => ! empty($wcfm_modified_endpoints['wcfm-subscriptions']) ? $wcfm_modified_endpoints['wcfm-subscriptions'] : 'subscriptions',
            'wcfm-subscriptions-manage' => ! empty($wcfm_modified_endpoints['wcfm-subscriptions-manage']) ? $wcfm_modified_endpoints['wcfm-subscriptions-manage'] : 'subscriptions-manage',
        ];

        $query_vars = array_merge($query_vars, $query_subscriptions_vars);

        return $query_vars;

    }//end wcs_wcfm_query_vars()


    /**
     * WC Subscriptions End Point Title
     */
    function wcs_wcfm_endpoint_title($title, $endpoint)
    {
        global $wp;
        switch ($endpoint) {
            case 'wcfm-subscriptions':
                $title = __('Subscriptions List', 'wc-frontend-manager-ultimate');
                break;

            case 'wcfm-subscriptions-manage':
                $title = sprintf(__('Subscription Manage #%s', 'wc-frontend-manager-ultimate'), $wp->query_vars['wcfm-subscriptions-manage']);
                break;
        }

        return $title;

    }//end wcs_wcfm_endpoint_title()


    /**
     * WC Subscriptions Endpoint Intialize
     */
    function wcs_wcfm_init()
    {
        global $WCFM_Query;

        // Intialize WCFM End points
        $WCFM_Query->init_query_vars();
        $WCFM_Query->add_endpoints();

        if (! get_option('wcfm_updated_end_point_wc_subscriptions')) {
            // Flush rules after endpoint update
            flush_rewrite_rules();
            update_option('wcfm_updated_end_point_wc_subscriptions', 1);
        }

    }//end wcs_wcfm_init()


    /**
     * WC Subscriptions Menu
     */
    function wcs_wcfm_menus($menus)
    {
        global $WCFM;

        if (apply_filters('wcfm_is_allow_subscriptions', true) && apply_filters('wcfm_is_allow_subscription_list', true)) {
            $menus = (array_slice($menus, 0, 3, true) + [
                'wcfm-subscriptions' => [
                    'label'    => __('Subscriptions', 'woocommerce-subscriptions'),
                    'url'      => get_wcfm_subscriptions_url(),
                    'icon'     => 'money',
                    'priority' => 21,
                ],
            ] + array_slice($menus, 3, (count($menus) - 3), true));
        }

        return $menus;

    }//end wcs_wcfm_menus()


    /**
     * Subscriptions Endpoiint Edit
     */
    function wcs_wcfm_endpoints_slug($endpoints)
    {
        $subscriptions_endpoints = [
            'wcfm-subscriptions'        => 'subscriptions',
            'wcfm-subscriptions-manage' => 'subscriptions-manage',
        ];

        $endpoints = array_merge($endpoints, $subscriptions_endpoints);

        return $endpoints;

    }//end wcs_wcfm_endpoints_slug()


    /**
     * WC Subscriptions Product Type
     */
    function wcs_product_types($pro_types)
    {
        global $WCFM, $WCFMu;

        $pro_types['variable-subscription'] = __('Variable subscription', 'woocommerce-subscriptions');

        return $pro_types;

    }//end wcs_product_types()


    /**
     * WC Subscription Scripts
     */
    public function wcs_load_scripts($end_point)
    {
        global $WCFM, $WCFMu;

        switch ($end_point) {
            case 'wcfm-subscriptions':
                $WCFM->library->load_datatable_lib();
                $WCFM->library->load_select2_lib();
                wp_enqueue_script('wcfm_subscriptions_js', $WCFMu->library->js_lib_url.'xa_subscriptions/wcfmu-script-xasubscriptions.js', [ 'jquery', 'dataTables_js' ], $WCFMu->version, true);

                // Screen manager
                $wcfm_screen_manager      = (array) get_option('wcfm_screen_manager');
                $wcfm_screen_manager_data = [];
                if (isset($wcfm_screen_manager['subscription'])) {
                    $wcfm_screen_manager_data = $wcfm_screen_manager['subscription'];
                }

                if (! isset($wcfm_screen_manager_data['admin'])) {
                    $wcfm_screen_manager_data['admin']  = $wcfm_screen_manager_data;
                    $wcfm_screen_manager_data['vendor'] = $wcfm_screen_manager_data;
                }

                if (wcfm_is_vendor()) {
                    $wcfm_screen_manager_data = $wcfm_screen_manager_data['vendor'];
                } else {
                    $wcfm_screen_manager_data = $wcfm_screen_manager_data['admin'];
                }

                if (apply_filters('wcfm_subscriptions_additonal_data_hidden', true)) {
                    $wcfm_screen_manager_data[10] = 'yes';
                }

                wp_localize_script('wcfm_subscriptions_js', 'wcfm_subscriptions_screen_manage', $wcfm_screen_manager_data);
                break;

            case 'wcfm-subscriptions-manage':
                $WCFM->library->load_datepicker_lib();
                // wp_register_script( 'wcfm_jstz', plugin_dir_url( WC_Subscriptions::$plugin_file ) . 'assets/js/admin/jstz.min.js' );
                // wp_register_script( 'wcfm_momentjs', plugin_dir_url( WC_Subscriptions::$plugin_file ) . 'assets/js/admin/moment.min.js' );
                  wp_enqueue_script('wcfm_subscriptions_manage_js', $WCFMu->library->js_lib_url.'xa_subscriptions/wcfmu-script-xasubscriptions-manage.js', [ 'jquery' ], $WCFMu->version, true);

                  $script_params = [
                      'ajax_url'                     => admin_url('admin-ajax.php'),
                      'ProductType'                  => 'subscription',
                      'SingularLocalizedTrialPeriod' => Hforce_Date_Time_Utils::get_available_time_periods(),
                      'PluralLocalizedTrialPeriod'   => Hforce_Date_Time_Utils::get_available_time_periods('plural'),
                      'LocalizedSubscriptionLengths' => Hforce_Date_Time_Utils::hforce_get_subscription_ranges(),
                      'BulkEditPeriodMessage'        => __('Enter the new period, either day, week, month or year:', 'xa-woocommerce-subscription'),
                      'BulkEditLengthMessage'        => __('Enter a new length (e.g. 5):', 'xa-woocommerce-subscription'),
                      'BulkEditIntervalMessage'      => __('Enter a new interval as a single number (e.g. to charge every 2nd month, enter 2):', 'xa-woocommerce-subscription'),
                  ];
                  // wp_enqueue_script('hf_subscription_admin', HFORCE_BASE_URL . 'admin/js/hf-woocommerce-subscription-admin.js', array('jquery'), filemtime(HFORCE_SUBSCRIPTION_MAIN_PATH . 'admin/js/hf-woocommerce-subscription-admin.js'));
                  // wp_localize_script('hf_subscription_admin', 'HFSubscriptions_OBJ', apply_filters('hf_subscription_admin_script_parameters', $script_params));
                break;
        }//end switch

    }//end wcs_load_scripts()


    /**
     * WC Subscription Styles
     */
    public function wcs_load_styles($end_point)
    {
        global $WCFM, $WCFMu;

        switch ($end_point) {
            case 'wcfm-subscriptions':
                wp_enqueue_style('wcfm_subscriptions_css', $WCFMu->library->css_lib_url.'xa_subscriptions/wcfmu-style-xasubscriptions.css', [], $WCFMu->version);
                break;

            case 'wcfm-subscriptions-manage':
                wp_enqueue_style('collapsible_css', $WCFM->library->css_lib_url.'wcfm-style-collapsible.css', [], $WCFMu->version);
                wp_enqueue_style('wcfm_subscriptions_manage_css', $WCFMu->library->css_lib_url.'xa_subscriptions/wcfmu-style-xasubscriptions-manage.css', [], $WCFMu->version);
                break;
        }

    }//end wcs_load_styles()


    /**
     * WC Subscription Views
     */
    public function wcs_load_views($end_point)
    {
        global $WCFM, $WCFMu;

        switch ($end_point) {
            case 'wcfm-subscriptions':
                $WCFMu->template->get_template('xa_subscriptions/wcfmu-view-xasubscriptions.php');
                break;

            case 'wcfm-subscriptions-manage':
                $WCFMu->template->get_template('xa_subscriptions/wcfmu-view-xasubscriptions-manage.php');
                break;
        }

    }//end wcs_load_views()


    /**
     * WC Subscription Ajax Controllers
     */
    public function wcs_ajax_controller()
    {
        global $WCFM, $WCFMu;

        $controllers_path = $WCFMu->plugin_path.'controllers/xa_subscriptions/';

        $controller = '';
        if (isset($_POST['controller'])) {
            $controller = $_POST['controller'];

            switch ($controller) {
                case 'wcfm-subscriptions':
                    include_once $controllers_path.'wcfmu-controller-xasubscriptions.php';
                    new WCFMu_XASubscriptions_Controller();
                    break;

                case 'wcfm-subscriptions-manage':
                    include_once $controllers_path.'wcfmu-controller-xasubscriptions-manage.php';
                    new WCFMu_XASubscriptions_Manage_Controller();
                    break;
            }
        }

    }//end wcs_ajax_controller()


    /**
     * WC Subscriptions Product General options
     */
    function wcs_product_manage_fields_general($general_fields, $product_id)
    {
        global $WCFM, $WCFMu;

        $sign_up_fee         = '';
        $chosen_trial_length = 0;
        $chosen_trial_period = '';

        if ($product_id) {
            $sign_up_fee         = get_post_meta($product_id, '_subscription_sign_up_fee', true);
            $chosen_trial_length = WC_Subscriptions_Product::get_trial_length($product_id);
            $chosen_trial_period = WC_Subscriptions_Product::get_trial_period($product_id);
        }

        $general_fields = (array_slice($general_fields, 0, 12, true) + [
            '_subscription_sign_up_fee'  => [
                'label'       => sprintf(esc_html__('Sign-up fee (%s)', 'woocommerce-subscriptions'), esc_html(get_woocommerce_currency_symbol())),
                'type'        => 'text',
                'placeholder' => 'e.g. 9.90',
                'class'       => 'wcfm-text wcfm_ele subscription',
                'label_class' => 'wcfm_title wcfm_ele subscription',
                'hints'       => __('Optionally include an amount to be charged at the outset of the subscription. The sign-up fee will be charged immediately, even if the product has a free trial or the payment dates are synced.', 'woocommerce-subscriptions'),
                'value'       => $sign_up_fee,
            ],
            '_subscription_trial_length' => [
                'label'       => esc_html__('Free Trial', 'woocommerce-subscriptions'),
                'type'        => 'number',
                'class'       => 'wcfm-text wcfm_ele subscription_price_ele subscription',
                'label_class' => 'wcfm_title wcfm_ele subscription',
                'hints'       => __('An optional period of time to wait before charging the first recurring payment. Any sign up fee will still be charged at the outset of the subscription.', 'woocommerce-subscriptions'),
                'value'       => $chosen_trial_length,
            ],
            '_subscription_trial_period' => [
                'type'        => 'select',
                'options'     => wcs_get_available_time_periods(),
                'class'       => 'wcfm-select wcfm_ele subscription_price_ele subscription',
                'label_class' => 'wcfm_title wcfm_ele subscription',
                'value'       => $chosen_trial_period,
            ],
        ] + array_slice($general_fields, 12, (count($general_fields) - 1), true));
        return $general_fields;

    }//end wcs_product_manage_fields_general()


    /**
     * WC Subscriptions Product Shipping options
     */
    function wcs_product_manage_fields_shipping($shipping_fields, $product_id)
    {
        global $WCFM, $WCFMu;

        $one_time_shipping = 'no';

        if ($product_id) {
            $one_time_shipping = get_post_meta($product_id, '_subscription_one_time_shipping', true) ? get_post_meta($product_id, '_subscription_one_time_shipping', true) : 'no';
        }

        $shipping_fields = (array_slice($shipping_fields, 0, 5, true) + [
            '_subscription_one_time_shipping' => [
                'label'       => esc_html__('One time shipping', 'woocommerce-subscriptions'),
                'type'        => 'checkbox',
                'class'       => 'wcfm-checkbox wcfm_ele subscription variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele subscription variable-subscription',
                'hints'       => __('Shipping for subscription products is normally charged on the initial order and all renewal orders. Enable this to only charge shipping once on the initial order. Note: for this setting to be enabled the subscription must not have a free trial or a synced renewal date.', 'woocommerce-subscriptions'),
                'value'       => 'yes',
                'dfvalue'     => $one_time_shipping,
            ],
        ] + array_slice($shipping_fields, 5, (count($shipping_fields) - 1), true));
        return $shipping_fields;

    }//end wcs_product_manage_fields_shipping()


    /**
     * WC Subscriptions Product Advanced options
     */
    function wcs_product_manage_fields_advanced($advanced_fields, $product_id)
    {
        global $WCFM, $WCFMu;

        $subscription_limit = '';

        if ($product_id) {
            $subscription_limit = get_post_meta($product_id, '_subscription_limit', true);
        }

        $advanced_fields = (array_slice($advanced_fields, 0, 3, true) + [
            '_subscription_limit' => [
                'label'       => esc_html__('Limit subscription', 'woocommerce-subscriptions'),
                'type'        => 'select',
                'options'     => [
                    'no'     => __('Do not limit', 'woocommerce-subscriptions'),
                    'active' => __('Limit to one active subscription', 'woocommerce-subscriptions'),
                    'any'    => __('Limit to one of any status', 'woocommerce-subscriptions'),
                ],
                'class'       => 'wcfm-select wcfm_ele subscription variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele subscription variable-subscription',
                'hints'       => __('Only allow a customer to have one subscription to this product.', 'woocommerce-subscriptions'),
                'value'       => $subscription_limit,
            ],
        ] + array_slice($advanced_fields, 3, (count($advanced_fields) - 1), true));
        return $advanced_fields;

    }//end wcs_product_manage_fields_advanced()


    /**
     * WC Subscriptions Variation aditional options
     */
    function wcs_product_manage_fields_variations($variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options)
    {
        global $WCFM, $WCFMu;

        $variation_fileds = (array_slice($variation_fileds, 0, 6, true) + [
            '_subscription_price'           => [
                'label'       => sprintf(esc_html__('Subscription price (%s)', 'woocommerce-subscriptions'), esc_html(get_woocommerce_currency_symbol())),
                'type'        => 'text',
                'class'       => 'wcfm-text wcfm_ele subscription_price_ele variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele variable-subscription',
                'hints'       => __('Choose the subscription price, billing interval and period.', 'woocommerce-subscriptions'),
            ],
            '_subscription_period_interval' => [
                'type'        => 'select',
                'options'     => wcs_get_subscription_period_interval_strings(),
                'class'       => 'wcfm-select wcfm_ele subscription_price_ele variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele variable-subscription',
            ],
            '_subscription_period'          => [
                'type'        => 'select',
                'options'     => wcs_get_subscription_period_strings(),
                'class'       => 'wcfm-select wcfm_ele subscription_price_ele variable-subscription_period variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele variable-subscription',
            ],
            '_subscription_length_day'      => [
                'label'       => __('Subscription length', 'woocommerce-subscriptions'),
                'type'        => 'select',
                'options'     => wcs_get_subscription_ranges('day'),
                'class'       => 'wcfm-select wcfm_ele variable-subscription_length_ele variable-subscription_length_day variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele variable-subscription_length_ele variable-subscription_length_day variable-subscription',
                'hints'       => __('Automatically expire the subscription after this length of time. This length is in addition to any free trial or amount of time provided before a synchronised first renewal date.', 'woocommerce-subscriptions'),
            ],
            '_subscription_length_week'     => [
                'label'       => __('Subscription length', 'woocommerce-subscriptions'),
                'type'        => 'select',
                'options'     => wcs_get_subscription_ranges('week'),
                'class'       => 'wcfm-select wcfm_ele variable-subscription_length_ele variable-subscription_length_week variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele variable-subscription_length_ele variable-subscription_length_week variable-subscription',
                'hints'       => __('Automatically expire the subscription after this length of time. This length is in addition to any free trial or amount of time provided before a synchronised first renewal date.', 'woocommerce-subscriptions'),
            ],
            '_subscription_length_month'    => [
                'label'       => __('Subscription length', 'woocommerce-subscriptions'),
                'type'        => 'select',
                'options'     => wcs_get_subscription_ranges('month'),
                'class'       => 'wcfm-select wcfm_ele variable-subscription_length_ele variable-subscription_length_month variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele variable-subscription_length_ele variable-subscription_length_month variable-subscription',
                'hints'       => __('Automatically expire the subscription after this length of time. This length is in addition to any free trial or amount of time provided before a synchronised first renewal date.', 'woocommerce-subscriptions'),
            ],
            '_subscription_length_year'     => [
                'label'       => __('Subscription length', 'woocommerce-subscriptions'),
                'type'        => 'select',
                'options'     => wcs_get_subscription_ranges('year'),
                'class'       => 'wcfm-select wcfm_ele variable-subscription_length_ele variable-subscription_length_year variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele variable-subscription_length_ele variable-subscription_length_year variable-subscription',
                'hints'       => __('Automatically expire the subscription after this length of time. This length is in addition to any free trial or amount of time provided before a synchronised first renewal date.', 'woocommerce-subscriptions'),
            ],
            '_subscription_sign_up_fee'     => [
                'label'       => sprintf(esc_html__('Sign-up fee (%s)', 'woocommerce-subscriptions'), esc_html(get_woocommerce_currency_symbol())),
                'type'        => 'text',
                'placeholder' => 'e.g. 9.90',
                'class'       => 'wcfm-text wcfm_ele variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele variable-subscription',
                'hints'       => __('Optionally include an amount to be charged at the outset of the subscription. The sign-up fee will be charged immediately, even if the product has a free trial or the payment dates are synced.', 'woocommerce-subscriptions'),
            ],
            '_subscription_trial_length'    => [
                'label'       => esc_html__('Free Trial', 'woocommerce-subscriptions'),
                'type'        => 'number',
                'class'       => 'wcfm-text wcfm_ele subscription_trial_ele variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele variable-subscription',
                'hints'       => __('An optional period of time to wait before charging the first recurring payment. Any sign up fee will still be charged at the outset of the subscription.', 'woocommerce-subscriptions'),
            ],
            '_subscription_trial_period'    => [
                'type'        => 'select',
                'options'     => wcs_get_available_time_periods(),
                'class'       => 'wcfm-select wcfm_ele subscription_trial_ele variable-subscription',
                'label_class' => 'wcfm_title wcfm_ele variable-subscription',
            ],
        ] + array_slice($variation_fileds, 6, (count($variation_fileds) - 1), true));

        return $variation_fileds;

    }//end wcs_product_manage_fields_variations()


    /**
     * WC Subscriptions Product Meta data save
     */
    function wcs_wcfm_product_meta_save($new_product_id, $wcfm_products_manage_form_data)
    {
        global $wpdb, $WCFM, $WCFMu, $_POST;

        if ($wcfm_products_manage_form_data['product_type'] == 'subscription') {
            // Make sure trial period is within allowable range
            $subscription_ranges = wcs_get_subscription_ranges();

            $max_trial_length = (count($subscription_ranges[$wcfm_products_manage_form_data['_subscription_trial_period']]) - 1);

            $wcfm_products_manage_form_data['_subscription_trial_length'] = absint($wcfm_products_manage_form_data['_subscription_trial_length']);

            if ($wcfm_products_manage_form_data['_subscription_trial_length'] > $max_trial_length) {
                $wcfm_products_manage_form_data['_subscription_trial_length'] = $max_trial_length;
            }

            update_post_meta($new_product_id, '_subscription_trial_length', $wcfm_products_manage_form_data['_subscription_trial_length']);

            $wcfm_products_manage_form_data['_subscription_sign_up_fee']       = wc_format_decimal($wcfm_products_manage_form_data['_subscription_sign_up_fee']);
            $wcfm_products_manage_form_data['_subscription_one_time_shipping'] = isset($wcfm_products_manage_form_data['_subscription_one_time_shipping']) ? 'yes' : 'no';

            $subscription_fields = [
                '_subscription_sign_up_fee',
                '_subscription_trial_period',
                '_subscription_limit',
                '_subscription_one_time_shipping',
            ];

            foreach ($subscription_fields as $field_name) {
                if (isset($wcfm_products_manage_form_data[$field_name])) {
                    update_post_meta($new_product_id, $field_name, stripslashes($wcfm_products_manage_form_data[$field_name]));
                }
            }
        }//end if

    }//end wcs_wcfm_product_meta_save()


    /**
     * WC Subscriptions Variation Data Save
     */
    function wcs_product_variation_save($new_product_id, $variation_id, $variations, $wcfm_products_manage_form_data)
    {
        global $wpdb, $WCFM, $WCFMu;

        if (WC_Subscriptions_Product::is_subscription($new_product_id)) {
            $subscription_price = isset($variations['_subscription_price']) ? wc_format_decimal($variations['_subscription_price']) : '';
            update_post_meta($variation_id, '_subscription_price', $subscription_price);
            update_post_meta($variation_id, '_regular_price', $subscription_price);
            update_post_meta($new_product_id, '_price', $subscription_price);
            update_post_meta($variation_id, '_price', $subscription_price);

            $subscription_fields = [
                '_subscription_period',
                '_subscription_period_interval',
                '_subscription_sign_up_fee',
                '_subscription_trial_period',
                '_subscription_trial_length',
            ];

            foreach ($subscription_fields as $field_name) {
                if (isset($variations[$field_name])) {
                    update_post_meta($variation_id, $field_name, stripslashes($variations[$field_name]));
                }
            }

            update_post_meta($variation_id, '_subscription_length', stripslashes($variations['_subscription_length_'.$variations['_subscription_period']]));

            if (WC_Subscriptions::is_woocommerce_pre('3.0')) {
                $variable_subscription = wc_get_product($new_product_id);
                $variable_subscription->variable_product_sync();
            } else {
                WC_Product_Variable::sync($new_product_id);
            }
        }//end if

    }//end wcs_product_variation_save()


    /**
     * WC Subscriptions Variaton edit data
     */
    function wcs_product_data_variations($variations, $variation_id, $variation_id_key)
    {
        global $WCFM, $WCFMu;

        if ($variation_id) {
            $variations[$variation_id_key]['_subscription_price']           = get_post_meta($variation_id, '_subscription_price', true);
            $variations[$variation_id_key]['_subscription_period']          = get_post_meta($variation_id, '_subscription_period', true);
            $variations[$variation_id_key]['_subscription_period_interval'] = get_post_meta($variation_id, '_subscription_period_interval', true);
            $variations[$variation_id_key]['_subscription_sign_up_fee']     = get_post_meta($variation_id, '_subscription_sign_up_fee', true);
            $variations[$variation_id_key]['_subscription_trial_period']    = get_post_meta($variation_id, '_subscription_trial_period', true);
            $variations[$variation_id_key]['_subscription_trial_length']    = get_post_meta($variation_id, '_subscription_trial_length', true);
            $variations[$variation_id_key]['_subscription_length_day']      = get_post_meta($variation_id, '_subscription_length', true);
            $variations[$variation_id_key]['_subscription_length_week']     = get_post_meta($variation_id, '_subscription_length', true);
            $variations[$variation_id_key]['_subscription_length_month']    = get_post_meta($variation_id, '_subscription_length', true);
            $variations[$variation_id_key]['_subscription_length_year']     = get_post_meta($variation_id, '_subscription_length', true);
        }

        return $variations;

    }//end wcs_product_data_variations()


    /**
     * Handle Subscriptions Details Status Update
     */
    public function wcfm_modify_subscription_status()
    {
        global $WCFM, $WCFMu;

        $subscription_id     = $_POST['subscription_id'];
        $subscription_status = $_POST['subscription_status'];

        $subscription = hforce_get_subscription($subscription_id);
        $subscription->update_status($subscription_status);

        // Status Update Notification
        $user_id   = apply_filters('wcfm_current_vendor_id', get_current_user_id());
        $shop_name = get_user_by('ID', $user_id)->display_name;
        if (wcfm_is_vendor()) {
            $shop_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor(absint($user_id));
        }

        $wcfm_messages = sprintf(__('<b>%1$s</b> subscription status updated to <b>%2$s</b> by <b>%3$s</b>', 'wc-frontend-manager-ultimate'), '#<a target="_blank" class="wcfm_dashboard_item_title" href="'.get_wcfm_subscriptions_manage_url($subscription_id).'">'.$subscription_id.'</a>', ucfirst($subscription_status), $shop_name);

        $raw_message = [
            'l10n'	=> [
                'text' 		=> '<b>%1$s</b> subscription status updated to <b>%2$s</b> by <b>%3$s</b>',
                'domain'    => 'wc-frontend-manager-ultimate',
                'wrapper'	=> [
                    'function' 	=> 'sprintf',
                    'args' 		=> [
                        '#<a target="_blank" class="wcfm_dashboard_item_title" href="'.get_wcfm_subscriptions_manage_url($subscription_id).'">'.$subscription_id.'</a>', 
                        ucfirst($subscription_status), 
                        $shop_name
                    ]
                ]
            ]
        ];

        $WCFM->wcfm_notification->wcfm_send_direct_message(-2, 0, 1, 0, $wcfm_messages, 'status-update', true, $raw_message);

        echo '{"status": true, "message": "'.__('Subscription status updated.', 'wc-frontend-manager-ultimate').'"}';

        die;

    }//end wcfm_modify_subscription_status()


    public static function init_address_fields()
    {
        self::$billing_fields = apply_filters(
            'woocommerce_admin_billing_fields',
            [
                'first_name' => [
                    'label' => __('First Name', 'woocommerce'),
                    'show'  => false,
                ],
                'last_name'  => [
                    'label' => __('Last Name', 'woocommerce'),
                    'show'  => false,
                ],
                'company'    => [
                    'label' => __('Company', 'woocommerce'),
                    'show'  => false,
                ],
                'address_1'  => [
                    'label' => __('Address 1', 'woocommerce'),
                    'show'  => false,
                ],
                'address_2'  => [
                    'label' => __('Address 2', 'woocommerce'),
                    'show'  => false,
                ],
                'city'       => [
                    'label' => __('City', 'woocommerce'),
                    'show'  => false,
                ],
                'postcode'   => [
                    'label' => __('Postcode', 'woocommerce'),
                    'show'  => false,
                ],
                'country'    => [
                    'label'   => __('Country', 'woocommerce'),
                    'show'    => false,
                    'class'   => 'js_field-country select short',
                    'type'    => 'select',
                    'options' => ([ '' => __('Select a country&hellip;', 'woocommerce') ] + WC()->countries->get_allowed_countries()),
                ],
                'state'      => [
                    'label' => __('State/County', 'woocommerce'),
                    'class' => 'js_field-state select short',
                    'show'  => false,
                ],
                'email'      => [
                    'label' => __('Email', 'woocommerce'),
                ],
                'phone'      => [
                    'label' => __('Phone', 'woocommerce'),
                ],
            ]
        );

        self::$shipping_fields = apply_filters(
            'woocommerce_admin_shipping_fields',
            [
                'first_name' => [
                    'label' => __('First Name', 'woocommerce'),
                    'show'  => false,
                ],
                'last_name'  => [
                    'label' => __('Last Name', 'woocommerce'),
                    'show'  => false,
                ],
                'company'    => [
                    'label' => __('Company', 'woocommerce'),
                    'show'  => false,
                ],
                'address_1'  => [
                    'label' => __('Address 1', 'woocommerce'),
                    'show'  => false,
                ],
                'address_2'  => [
                    'label' => __('Address 2', 'woocommerce'),
                    'show'  => false,
                ],
                'city'       => [
                    'label' => __('City', 'woocommerce'),
                    'show'  => false,
                ],
                'postcode'   => [
                    'label' => __('Postcode', 'woocommerce'),
                    'show'  => false,
                ],
                'country'    => [
                    'label'   => __('Country', 'woocommerce'),
                    'show'    => false,
                    'type'    => 'select',
                    'class'   => 'js_field-country select short',
                    'options' => ([ '' => __('Select a country&hellip;', 'woocommerce') ] + WC()->countries->get_shipping_countries()),
                ],
                'state'      => [
                    'label' => __('State/County', 'woocommerce'),
                    'class' => 'js_field-state select short',
                    'show'  => false,
                ],
            ]
        );

    }//end init_address_fields()


}//end class
