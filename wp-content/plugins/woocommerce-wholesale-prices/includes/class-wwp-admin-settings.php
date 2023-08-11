<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WWP_Admin_Settings' ) ) {
    /**
     * Model that houses the logic of WooCommerce Wholesale Prices Settings page.
     *
     * @since 1.0.0
     */
    class WWP_Admin_Settings {

        /**
         * Property that holds script execution mode.
         *
         * @since 2.1.9
         * @var bool|string
         */
        private $is_hmr;

        /**
         * The base URL of the plugin.
         *
         * @since 2.1.9
         * @var string
         */
        private $base_url;

        /**
         * Holds the script entry file.
         *
         * @since 2.1.9
         * @var string
         */
        private $entry_file;

        /**
         * Holds the script CSS file.
         *
         * @since 2.1.9
         * @var string
         */
        private $css_file;

        /**
         * Holds the environment variables.
         *
         * @var array
         */
        private $env;

        /**
         * Property that holds the single main instance of WWP_Dashboard.
         *
         * @since  2.0
         * @access private
         * @var WWP_Dashboard
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since  2.0
         * @access private
         * @var WWP_Wholesale_Roles
         */
        private $_wwp_wholesale_roles;

        /**
         * Holds the script manifest data.
         *
         * @since 3.0
         * @var array The manifest data.
         */
        protected $manifest;

        /**
         * WWP_Admin_Settings constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWP_Admin_Settings model.
         *
         * @since  2.0
         * @access public
         */
        public function __construct( $dependencies ) {

            $this->_wwp_wholesale_roles = $dependencies['WWP_Wholesale_Roles'];

            $this->is_hmr     = defined( 'HMR_DEV' ) && 'wwp' === HMR_DEV;
            $this->entry_file = 'src/apps/settings/index.ts';
            $this->css_file   = 'src/apps/settings/index.css';

            if ( $this->is_hmr ) {
                if ( file_exists( WWP_PLUGIN_PATH . '.env' ) ) {
                    //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                    $env_str   = file_get_contents( WWP_PLUGIN_PATH . '.env' );
                    $this->env = array();
                    if ( $env_str ) {
                        $env_str = preg_split( '/\\r\\n|\\r|\\n/', $env_str );
                        $env_str = is_array( $env_str ) ? array_filter( $env_str ) : array();
                        foreach ( $env_str as $line ) {
                            $line = explode( '=', $line );
                            if ( 2 === count( $line ) ) {
                                $this->env[ $line[0] ] = $line[1];
                            }
                        }
                    }
                }
            }

            $host = 'localhost';
            $port = 3000;
            if ( $this->is_hmr ) {
                $this->parse_manifest_file();
                $protocol = ! empty( $this->env['VITE_DEV_SERVER_HTTPS_KEY'] ) && $this->env['VITE_DEV_SERVER_HTTPS_CERT']
                    ? 'https:'
                    : 'http:';
                $host     = $this->env['VITE_DEV_SERVER_HOST'] ?? $host;
                $port     = $this->env['VITE_DEV_SERVER_PORT'] ?? $port;
            }
            $this->base_url = $this->is_hmr
                ? "$protocol//$host:$port/"
                : WWP_PLUGIN_URL . 'dist/';
        }

        /**
         * Ensure that only one instance of WWP_Admin_Settings is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWP_Admin_Settings model.
         *
         * @since  2.0
         * @access public
         *
         * @return WWP_Admin_Settings
         */
        public static function instance( $dependencies ) {

            if ( ! self::$_instance instanceof self ) {
                self::$_instance = new self( $dependencies );
            }

            return self::$_instance;
        }

        /**
         * Parse the manifest file.
         *
         * @since 3.0
         * @return void
         */
        protected function parse_manifest_file() {

            if ( $this->is_hmr ) {
                return;
            }

            /**************************************************************************
             * Parse the manifest file
             ***************************************************************************
             *
             * In production mode, the manifest file should exist as it is required for
             * the production build script to load properly. If it doesn't exist, then
             * we write to error log file if WP_DEBUG is true.
             */
            $manifest_path = WWP_PLUGIN_PATH . 'dist/manifest.json';
            if ( file_exists( $manifest_path ) ) {
                //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                $this->manifest = json_decode( file_get_contents( $manifest_path ), true );
            } else {
                _doing_it_wrong(
                    __METHOD__,
                    esc_html__(
                        'Manifest file not found. Did you run the build script from package.json file?',
                        'woocommerce-wholesale-prices'
                    ),
                    '3.0'
                );
            }
        }

        /**
         * Enqueue imports from entry file
         *
         * @since 2.1.9
         * @return void
         */
        public function preload_imports() {

            if ( $this->is_hmr ) {
                return;
            }

            /**************************************************************************
             * Load the preload imports
             ***************************************************************************
             *
             * We load the preload imports referenced from the manifest file.
             */
            $imports = $this->manifest['src/apps/settings/index.ts']['imports'] ?? null;
            if ( ! empty( $imports ) ) {
                foreach ( $imports as $import ) {
                    /***************************************************************************
                     * Enqueue styles of directly imported components
                     ***************************************************************************
                     *
                     * We manually enqueue the styles of the components that (were generated
                     * from their `<style>` tag) are directly imported into another component.
                     * This is because the styles are not automatically enqueued/loaded at
                     * runtime unlike the dynamically imported components.
                     */
                    $import_styles = $this->manifest[ $import ]['css'] ?? null;
                    if ( ! empty( $import_styles ) ) {
                        foreach ( $import_styles as $import_style ) {
                            $sanitized_key = sanitize_title_with_dashes( basename( $import_style ) );
                            $css_url       = $this->base_url . $import_style;
                            wp_enqueue_style( "wwp-settings-app-import-$sanitized_key", $css_url, array(), filemtime( WWOF_PLUGIN_FILE ) );
                        }
                    }
                }
            }
        }

        /**
         * Load back end styles and scripts.
         *
         * @since  2.0
         * @access public
         */
        public function load_back_end_styles_and_scripts() {

            global $wc_wholesale_prices;
            if ( isset( $_GET['devmode'] ) && '1' === $_GET['devmode'] ) { // phpcs:ignore.

                /**************************************************************************
                 * Parse the production manifest file
                 ***************************************************************************
                 *
                 * Here we parse the production manifest file which contains the hashed
                 * file names for the production build. This is only used in production
                 * mode and bails out immediately if the app is being served in hot module
                 * replacement context.
                 */
                $this->parse_manifest_file();

                /**************************************************************************
                 * Enqueue scripts and styles
                 ***************************************************************************
                 *
                 * Enqueue scripts and styles including dependencies.
                 */
                add_filter( 'script_loader_tag', array( $this, 'add_script_tag_attributes' ), 10, 2 );
                add_filter( 'style_loader_tag', array( $this, 'add_style_tag_attributes' ), 10, 3 );

                if ( $this->is_hmr ) {
                    wp_enqueue_script(
                        'wwp-settings-app-vite-client',
                        "$this->base_url@vite/client",
                        array(),
                        time(),
                        false
                    );
                }

                $script_version = $this->is_hmr ? time() : $wc_wholesale_prices::VERSION;
                $style_version  = $script_version;
                $entry_file_url = $this->is_hmr ? $this->base_url . $this->entry_file : $this->base_url . $this->manifest[ $this->entry_file ]['file'];
                $css_file_url   = $this->is_hmr ? $this->base_url . $this->css_file : $this->base_url . $this->manifest[ $this->css_file ]['file'];

                // Load vue styles.
                if ( ! $this->is_hmr ) {
                    wp_enqueue_style( 'wwp-settings-app-styles', $css_file_url, array(), $style_version );
                }

                // Load vue scripts.
                wp_enqueue_script( 'wwp-settings-app-scripts', $entry_file_url, array( 'jquery' ), $script_version, true );

                $settings_object = array(
                    'details'         => $this->get_details(),
                    'rest_save_url'   => rest_url( 'wwp/v1/admin/save' ),
                    'rest_action_url' => rest_url( 'wwp/v1/admin/action' ),
                    'settings'        => $this->get_registered_tab_settings(),
                    'pluginDirUrl'    => WWP_PLUGIN_URL,
                );

                wp_localize_script(
                    'wwp-settings-app-scripts',
                    'wwpObj',
                    $settings_object
                );
            }
        }

        /**
         * Modify script tag to include attributes.
         *
         * @param string $tag    The script tag.
         * @param string $handle The script handle.
         *
         * @since 3.0
         * @return string
         */
        public function add_script_tag_attributes( $tag, $handle ) {

            /**************************************************************************
             * Convert script tag to module
             ***************************************************************************
             *
             * We modify the script tag to include the type, crossorigin and integrity
             * attributes in production mode. Otherwise, we add the type module attribute.
             */
            $handles = array(
                'wwp-settings-app-scripts',
            );
            if ( in_array( $handle, $handles, true ) ) {
                if ( $this->is_hmr ) {
                    $tag = str_replace(
                        ' src',
                        ' type="module" src',
                        $tag
                    );
                } else {
                    $integrity = $this->get_file_hash(
                        WWP_PLUGIN_PATH . "dist/{$this->manifest['src/apps/settings/index.ts']['file']}"
                    );
                    $tag       = str_replace(
                        ' src',
                        sprintf( ' type="module" crossorigin="anonymous" integrity="%s" src', $integrity ),
                        $tag
                    );
                }
            }

            return $tag;
        }

        /**
         * Modify style tag to include attributes.
         *
         * @param string $tag    The style tag.
         * @param string $handle The style handle.
         * @param string $href   The style URL.
         *
         * @since 3.0
         * @return string
         */
        public function add_style_tag_attributes( $tag, $handle, $href ) {

            if ( false !== strpos( $handle, 'wwp-settings-app-styles' ) &&
                ! empty( $this->manifest['src/apps/settings/index.css']['css'] ) ) {
                /**************************************************************************
                 * Add crossorigin and integrity attributes
                 ***************************************************************************
                 *
                 * We modify our target style tag to include the crossorigin and integrity
                 * attributes in production mode.
                 */
                foreach ( $this->manifest['src/apps/settings/index.css']['css'] as $style ) {
                    if ( false !== strpos( $href, $style ) ) {
                        $integrity = $this->get_file_hash(
                            WWP_PLUGIN_PATH . "dist/$style"
                        );

                        $tag = str_replace(
                            ' href',
                            sprintf( ' crossorigin="anonymous" integrity="%s" href', $integrity ),
                            $tag
                        );
                    }
                }
            }

            return $tag;
        }

        /**
         * Get file hash.
         *
         * @param string $file The path to the target file.
         *
         * @since 3.0
         * @return string
         */
        protected function get_file_hash( $file ) {

            $algo = 'sha256';

            return "$algo-" .
                //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
                base64_encode(
                    openssl_digest(
                    //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                        file_get_contents( $file ),
                        $algo,
                        true
                    )
                );
        }

        /**
         * REST API for settings page.
         *
         * @since  2.0
         * @access public
         */
        public function rest_api_settings() {

            // Save settings.
            register_rest_route(
                'wwp/v1',
                '/admin/save',
                array(
                    'methods'             => 'POST',
                    'callback'            => array( $this, 'save_registered_settings' ),
                    'permission_callback' => '__return_true',
                )
            );

            // Trigger custom action.
            register_rest_route(
                'wwp/v1',
                'admin/action',
                array(
                    'methods'             => 'POST',
                    'callback'            => array( $this, 'trigger_action' ),
                    'permission_callback' => '__return_true',
                )
            );
        }

        /**
         * Save registered settings.
         *
         * @param WP_REST_Request $request Request object.
         *
         * @since  2.0
         * @access public
         *
         * @return WP_REST_Response
         */
        public function save_registered_settings( $request ) {

            $message = '';
            if ( ! empty( $request ) ) {
                foreach ( $request as $option_name => $option_value ) {
                    // Update or create.
                    update_option( $option_name, $option_value );
                }
            }

            $response = array(
                'success' => true,
                'message' => $message,
            );

            return rest_ensure_response( $response );
        }

        /**
         * Trigger custom action.
         *
         * @param WP_REST_Request $request Request object.
         *
         * @since  2.0
         * @access public
         */
        public function trigger_action( $request ) {

            $action = $request->action;
            // trigger the custom action.
            do_action( 'wwp_trigger_' . $action );
        }

        /**
         * Get settings details.
         *
         * @since  2.0
         * @access public
         *
         * @return array
         */
        public function get_details() {
            $details = array(
                'logo'  => esc_url( WWP_IMAGES_URL ) . 'logo.png',
                'title' => __( 'Settings', 'woocommerce-wholesale-prices' ),
            );

            return $details;
        }

        /**
         * Get registered tabs, controls and data tables.
         *
         * @since  2.0
         * @access public
         *
         * @return array
         */
        public function get_registered_tab_settings() {
            // Get the tabs.
            $default_tabs = $this->_default_tabs();
            $tabs         = apply_filters( 'wwp_admin_setting_tabs', $default_tabs );

            // Get the controls.
            $default_controls = $this->_default_controls();
            $controls         = apply_filters( 'wwp_admin_setting_controls', $default_controls );

            // Get data tables.
            $data_tables = apply_filters( 'wwp_admin_data_tables', array() );

            return array(
                'tabs'       => $tabs,
                'controls'   => $controls,
                'dataTables' => $data_tables,
            );
        }

        /**
         * Get default tabs.
         *
         * @since  2.0
         * @access private
         *
         * @return array
         */
        private function _default_tabs() {
            $settings = array();

            // Parent tab.
            $settings['wholesale_prices'] = array(
                'label' => __( 'Wholesale Prices', 'woocommerce-wholesale-prices' ),
                'child' => array(),
            );

            // General tab.
            $settings['wholesale_prices']['child']['general'] = array(
                'label'    => __( 'General', 'woocommerce-wholesale-prices' ),
                'sections' => array(
                    'order_requirements' => array(
                        'label' => __( 'Wholesale Prices Settings', 'woocommerce-wholesale-prices' ),
                        'desc'  => '',
                    ),
                ),
            );

            // Price tab.
            $settings['wholesale_prices']['child']['price'] = array(
                'label'    => __( 'Price', 'woocommerce-wholesale-prices' ),
                'sections' => array(
                    'price_options'         => array(
                        'label' => __( 'Price Options', 'woocommerce-wholesale-prices' ),
                        'desc'  => '',
                    ),
                    'box_for_non_wholesale' => array(
                        'label'     => __( 'Show Wholesale Prices Box For Non Wholesale Customers', 'woocommerce-wholesale-prices' ),
                        'desc'      => '',
                        'condition' => array(
                            'wwp_prices_settings_show_wholesale_prices_to_non_wholesale' => 'yes',
                        ),
                    ),
                ),
            );

            // Tax tab.
            $tax_exp_mapping_dec = sprintf(
            // translators: %1$s <b> tag, %2$s </b> tag, %3$s link to premium add-on, %4$s </a> tag, %5$s link to bundle.
                __(
                    'Specify tax exemption per wholesale role. Overrides general %1$sTax Exemption%2$s option above. <br><br>In the Premium add-on you can map specific wholesale roles to be tax exempt which gives you more control. This is useful for classifying customers based on their tax exemption status so you can separate those who need to pay tax and those who don\'t. <br><br>This feature and more is available in the %3$sPremium add-on%4$s and we also have other wholesale tools available as part of the %5$sWholesale Suite Bundle%4$s.',
                    'woocommerce-wholesale-prices'
                ),
                '<b>',
                '</b>',
                '<a target="_blank" href="https://wholesalesuiteplugin.com/woocommerce-wholesale-prices-premium/?utm_source=wwp&utm_medium=upsell&utm_campaign=wwptaxexemptionwwpplink">',
                '</a>',
                '<a target="_blank" href="https://wholesalesuiteplugin.com/bundle/?utm_source=wwp&utm_medium=upsell&utm_campaign=wwptaxexemptionbundlelink">'
            );
            $tax_cls_mapping_dec = sprintf(
            // translators: %1$s link to premium add-on, %2$s </a> tag, %3$s link to wholesale suite bundle.
                __(
                    'Specify tax classes per wholesale role. <br><br>In the Premium add-on you can map specific wholesale role to specific tax classes. You can also hide those mapped tax classes from your regular customers making it possible to completely separate tax functionality for wholesale customers. <br><br>This feature and more is available in the %1$sPremium add-on%2$s and we also have other wholesale tools available as part of the %3$sWholesale Suite Bundle%2$s.',
                    'woocommerce-wholesale-prices'
                ),
                '<a target="_blank" href="https://wholesalesuiteplugin.com/woocommerce-wholesale-prices-premium/?utm_source=wwp&utm_medium=upsell&utm_campaign=wwptaxexemptionwwpplink"> ',
                '</a>',
                '<a target="_blank" href="https://wholesalesuiteplugin.com/bundle/?utm_source=wwp&utm_medium=upsell&utm_campaign=wwptaxexemptionbundlelink">'
            );

            $settings['wholesale_prices']['child']['tax'] = array(
                'label'    => __( 'Tax', 'woocommerce-wholesale-prices' ),
                'sections' => array(
                    'tax_options'     => array(
                        'label' => __( 'Tax Options', 'woocommerce-wholesale-prices' ),
                        'desc'  => '',
                    ),
                    'tax_exp_mapping' => array(
                        'label' => __( 'Wholesale Role / Tax Exemption Mapping', 'woocommerce-wholesale-prices' ),
                        'desc'  => $tax_exp_mapping_dec,
                    ),
                    'tax_cls_mapping' => array(
                        'label' => __( 'Wholesale Role / Tax Class Mapping', 'woocommerce-wholesale-prices' ),
                        'desc'  => $tax_cls_mapping_dec,
                    ),
                ),
            );

            // Help tab.
            $help_options_dec = sprintf(
            // translators: %1$s link to premium add-on, %2$s </a> tag.
                __(
                    'Looking for documentation? Please see our growing %1$sKnowledge Base%2$s.',
                    'woocommerce-wholesale-prices'
                ),
                '<a target="_blank" href="https://wholesalesuiteplugin.com/knowledge-base/?utm_source=wwp&utm_medium=kb&utm_campaign=helppagekblink"> ',
                '</a>',
            );

            $settings['wholesale_prices']['child']['help'] = array(
                'label'    => __( 'Help', 'woocommerce-wholesale-prices' ),
                'sections' => array(
                    'shipping_options' => array(
                        'label' => __( 'Help Options', 'woocommerce-wholesale-prices' ),
                        'desc'  => $help_options_dec,
                    ),
                ),
            );

            // Upgrade tab.
            $settings['wholesale_prices']['child']['upgrade'] = array(
                'label'    => __( 'Upgrade', 'woocommerce-wholesale-prices' ),
                'sections' => array(
                    'upgrade_options'  => array(
                        'label' => __( 'Upgrade Plan', 'woocommerce-wholesale-prices' ),
                        'desc'  => '',
                    ),
                    'upgrade_options2' => array(
                        'label' => __( 'Bundle', 'woocommerce-wholesale-prices' ),
                        'desc'  => '',
                    ),
                ),
            );

            // License tab.
            $settings['wholesale_prices']['child']['license'] = array(
                'label'    => __( 'License', 'woocommerce-wholesale-prices' ),
                'link'     => admin_url( 'admin.php?page=wwc_license_settings' ),
                'external' => false,
            );

            $default_tabs = apply_filters( 'wwp_admin_setting_default_tabs', $settings );

            return $default_tabs;
        }

        /**
         * Get default controls.
         *
         * @since  2.0
         * @access private
         *
         * @return array
         */
        private function _default_controls() {
            $controls = array();

            // General tab.
            $controls['wholesale_prices']['general'] = $this->general_tab_controls();

            // Price tab.
            $controls['wholesale_prices']['price'] = $this->prices_tab_controls();

            // Tax tab.
            $controls['wholesale_prices']['tax'] = $this->tax_tab_controls();

            // Upgrade tab.
            $controls['wholesale_prices']['upgrade'] = $this->upgrade_tab_controls();

            $default_controls = apply_filters( 'wwp_admin_setting_default_controls', $controls );

            return $default_controls;
        }

        /**
         * General tab controls.
         *
         * @since  2.0
         * @access private
         * @return array
         */
        private function general_tab_controls() {
            $general_controls = array();

            // General Options - Section.
            $general_controls['order_requirements'] = array(
                array(
                    'type'        => 'checkbox',
                    'label'       => __( 'Disable Coupons For Wholesale Users', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwpp_settings_disable_coupons_for_wholesale_users',
                    'input_label' => __( 'Globally turn off coupons functionality for customers with a wholesale user role.', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'description' => __( 'This applies to all customers with a wholesale role.', 'woocommerce-wholesale-prices' ),
                    'default'     => 'no',
                ),
            );

            // Filter to modify general controls.
            $general_controls = apply_filters( 'wwp_admin_setting_default_general_controls', $general_controls );

            return $general_controls;
        }

        /**
         * Prices tab controls.
         *
         * @since  2.0
         * @access private
         * @return array
         */
        private function prices_tab_controls() {
            $prices_controls = array();

            // Price options - Section.
            $prices_controls['price_options'] = array(
                array(
                    'type'        => 'text',
                    'label'       => __( 'Wholesale Price Text', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwpp_settings_wholesale_price_title_text',
                    'default'     => __( 'Wholesale Price:', 'woocommerce-wholesale-prices' ),
                    'description' => __( 'The text shown immediately before the wholesale price. Default is "Wholesale Price: "', 'woocommerce-wholesale-prices' ),
                ),
                array(
                    'type'        => 'checkbox',
                    'label'       => __( 'Hide Retail Price', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwpp_settings_hide_original_price',
                    'input_label' => __( 'Hide retail price instead of showing a crossed out price if a wholesale price is present.', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'default'     => 'no',
                ),
                array(
                    'type'        => 'checkbox',
                    'label'       => __( 'Always Use Regular Price', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc_dummy',
                    'input_label' => __( 'When calculating the wholesale price by using a percentage (global discount % or category based %) always ensure the Regular Price is used and ignore the Sale Price if present.', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'default'     => 'no',
                ),
                array(
                    'type'     => 'select',
                    'label'    => __( 'Variable Product Price Display', 'woocommerce-wholesale-prices' ),
                    'desc_tip' => __( 'Specify the format in which variable product prices are displayed. Only for wholesale customers.', 'woocommerce-wholesale-prices' ),
                    'id'       => 'wwp_settings_variable_product_price_display_dummy',
                    'classes'  => 'wwp_settings_variable_product_price_display_dummy',
                    'options'  => array(
                        'price-range' => __( 'Price Range', 'woocommerce-wholesale-prices' ),
                        'minimum'     => __( 'Minimum Price (Premium)', 'woocommerce-wholesale-prices' ),
                        'maximum'     => __( 'Maximum Price (Premium)', 'woocommerce-wholesale-prices' ),
                    ),
                    'default'  => 'price-range',
                ),
                array(
                    'type'        => 'checkbox',
                    'label'       => __( 'Hide Wholesale Price on Admin Product Listing', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwpp_hide_wholesale_price_on_product_listing',
                    'input_label' => __( 'If checked, hides wholesale price per wholesale role on the product listing on the admin page.', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'default'     => 'no',
                ),
                array(
                    'type'        => 'checkbox',
                    'label'       => __( 'Hide Price and Add to Cart button', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwp_hide_price_add_to_cart',
                    'input_label' => __( 'If checked, hides price and add to cart button for visitors.', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'default'     => 'no',
                ),
                array(
                    'type'        => 'textarea',
                    'label'       => __( 'Price and Add to Cart Replacement Message', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwp_price_and_add_to_cart_replacement_message',
                    'description' => __( 'This message is only shown if <b>Hide Price and Add to Cart button</b> is enabled. "Login to see prices" is the default message.', 'woocommerce-wholesale-prices' ),
                    'editor'      => true,
                    'default'     => '',
                ),
                array(
                    'type'        => 'checkbox',
                    'label'       => __( 'Show Wholesale Price to non-wholesale users', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwp_prices_settings_show_wholesale_prices_to_non_wholesale',
                    'input_label' => __( 'If checked, displays the wholesale price on the front-end to entice non-wholesale customers to register as wholesale customers. This is only shown for guest, customers, administrator, and shop managers.', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'default'     => 'no',
                ),
            );

            // Price box for non wholesale customers - Section.
            $prices_controls['box_for_non_wholesale'] = array(
                array(
                    'type'        => 'checkbox',
                    'label'       => __( 'Locations', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwp_non_wholesale_show_in_shop',
                    'input_label' => __( 'Shop Archives', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'default'     => 'no',
                ),
                array(
                    'type'        => 'checkbox',
                    'label'       => '',
                    'id'          => 'wwp_non_wholesale_show_in_products',
                    'input_label' => __( 'Single Product', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'default'     => 'no',
                ),
                array(
                    'type'        => 'checkbox',
                    'label'       => '',
                    'id'          => 'wwp_non_wholesale_show_in_wwof',
                    'input_label' => __( 'Wholesale Order Form', 'woocommerce-wholesale-prices' ),
                    'description' => __( 'To use this option, you must have <b>WooCommerce Wholesale Order Form</b> plugin installed and activated.', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'disabled'    => true,
                    'default'     => 'no',
                ),
                array(
                    'type'     => 'text',
                    'label'    => __( 'Click to See Wholesale Prices Text', 'woocommerce-wholesale-prices' ),
                    'id'       => 'wwp_see_wholesale_prices_replacement_text',
                    'default'  => __( 'See wholesale prices', 'woocommerce-wholesale-prices' ),
                    'desc_tip' => __( 'The "Click to See Wholesale Prices Text" seen in the frontpage.', 'woocommerce-wholesale-prices' ),
                ),
                array(
                    'type'     => 'select',
                    'label'    => __( 'Wholesale Roles(s)', 'woocommerce-wholesale-prices' ),
                    'id'       => 'wwp_non_wholesale_wholesale_role_select2',
                    'default'  => 'wholesale_customer',
                    'options'  => array(
                        'wholesale_customer' => __( 'Wholesale Customer', 'woocommerce-wholesale-prices' ),
                    ),
                    'desc_tip' => __( 'The selected wholesale roles and pricing that should show to non-wholesale customers on the front end.', 'woocommerce-wholesale-prices' ),
                    'multiple' => true,
                ),
                array(
                    'type'     => 'text',
                    'label'    => __( 'Register Text', 'woocommerce-wholesale-prices' ),
                    'id'       => 'wwp_see_wholesale_prices_replacement_text',
                    'default'  => __( 'Click here to register as a wholesale customer', 'woocommerce-wholesale-prices' ),
                    'desc_tip' => __( 'This text is linked to the defined registration page in WooCommerce Wholesale Lead Capture settings.', 'woocommerce-wholesale-prices' ),
                ),
            );

            // Filter to modify prices controls.
            $prices_controls = apply_filters( 'wwp_admin_setting_default_prices_controls', $prices_controls );

            return $prices_controls;
        }

        /**
         * Tax Tab Controls.
         *
         * @since  2.0
         * @access private
         * @return array
         */
        private function tax_tab_controls() {
            $tax_controls = array();

            // Tax Options - Section.
            $tax_controls['tax_options'] = array(
                array(
                    'type'        => 'checkbox',
                    'label'       => __( 'Tax Exemption', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwp_settings_tax_exempt_wholesale_users',
                    'input_label' => __( 'Do not apply tax to all wholesale roles', 'woocommerce-wholesale-prices' ),
                    'description' => __( 'Removes tax for all wholesale roles. All wholesale prices will display excluding tax throughout the store, cart and checkout. The display settings below will be ignored.', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'default'     => 'no',
                ),
                array(
                    'type'        => 'select',
                    'label'       => __( 'Display Prices in the Shop', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwp_settings_incl_excl_tax_on_wholesale_price',
                    'options'     => array(
                        ''     => '--' . __( 'Use woocommerce default', 'woocommerce-wholesale-prices' ) . '--',
                        'incl' => __( 'Including tax (Premium)', 'woocommerce-wholesale-prices' ),
                        'excl' => __( 'Excluding tax (Premium)', 'woocommerce-wholesale-prices' ),
                    ),
                    'description' => __( 'Choose how wholesale roles see all prices throughout your shop pages.', 'woocommerce-wholesale-prices' ),
                    'desc_tip'    => __( 'Note: If the option above of "Tax Exempting" wholesale users is enabled, then wholesale prices on shop pages will not include tax regardless the value of this option.', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'default'     => '',
                ),
                array(
                    'type'        => 'select',
                    'label'       => __( 'Display Prices During Cart and Checkout', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwp_settings_wholesale_tax_display_cart',
                    'options'     => array(
                        ''     => '--' . __( 'Use woocommerce default', 'woocommerce-wholesale-prices' ) . '--',
                        'incl' => __( 'Including tax (Premium)', 'woocommerce-wholesale-prices' ),
                        'excl' => __( 'Excluding tax (Premium)', 'woocommerce-wholesale-prices' ),
                    ),
                    'description' => __( 'Choose how wholesale roles see all prices on the cart and checkout pages.', 'woocommerce-wholesale-prices' ),
                    'desc_tip'    => __( 'Note: If the option above of "Tax Exempting" wholesale users is enabled, then wholesale prices on cart and checkout page will not include tax regardless the value of this option.', 'woocommerce-wholesale-prices' ),
                    'multiple'    => false,
                    'default'     => '',
                ),
                array(
                    'type'        => 'text',
                    'label'       => __( 'Override Regular Price Suffix', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwp_settings_override_price_suffix_regular_price',
                    'description' => __( 'Override the price suffix on regular prices for wholesale users.', 'woocommerce-wholesale-prices' ),
                    'desc_tip'    => __( 'Make this blank to use the default price suffix. You can also use prices substituted here using one of the following {price_including_tax} and {price_excluding_tax}.', 'woocommerce-wholesale-prices' ),
                    'default'     => '',
                ),
                array(
                    'type'        => 'text',
                    'label'       => __( 'Wholesale Price Suffix', 'woocommerce-wholesale-prices' ),
                    'id'          => 'wwp_settings_override_price_suffix',
                    'description' => __( 'Set a specific price suffix specifically for wholesale prices.', 'woocommerce-wholesale-prices' ),
                    'desc_tip'    => __( 'Make this blank to use the default price suffix. You can also use prices substituted here using one of the following {price_including_tax} and {price_excluding_tax}.', 'woocommerce-wholesale-prices' ),
                    'default'     => '',
                ),
            );

            // Filter to modify tax controls.
            $tax_controls = apply_filters( 'wwp_admin_setting_default_tax_controls', $tax_controls );

            return $tax_controls;
        }

        /**
         * Upgrade Tab Controls.
         *
         * @since  2.0
         * @access private
         * @return array
         */
        private function upgrade_tab_controls() {
            $upgrade_controls = array();

            // Upgrade Options - Section.
            $upgrade_controls['upgrade_options'] = array(
                array(
                    'type'    => 'html',
                    'id'      => 'wwp_settings_upgrade_code_block',
                    'classes' => 'wwp-upgrade-code-block',
                    'fields'  => array(
                        array(
                            'type'    => 'image',
                            'id'      => 'wwp_image_upgrade',
                            'classes' => 'wwp-img-logo',
                            'url'     => esc_url( WWP_IMAGES_URL ) . 'wholesale-suite-activation-notice-logo.png',
                        ),
                        array(
                            'type'    => 'heading',
                            'id'      => 'wwp_heading_upgrade',
                            'tag'     => 'h2',
                            'content' => __( 'Free vs Premium', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'    => 'paragraph',
                            'id'      => 'wwp_paragraph_upgrade',
                            'content' => __( 'If you are serious about growing your wholesale sales within your WooCommerce store then the Premium add-on to the free WooCommerce Wholesale Prices plugin that you are currently using can help you.', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'       => 'table',
                            'paginated'  => false,
                            'editable'   => false,
                            'can_delete' => false,
                            'fields'     => array(
                                array(
                                    'title'     => __( 'Features', 'woocommerce-wholesale-prices' ),
                                    'sorter'    => true,
                                    'dataIndex' => 'features',
                                    'key'       => 'features',
                                ),
                                array(
                                    'title'     => __( 'Free Plugin', 'woocommerce-wholesale-prices' ),
                                    'dataIndex' => 'free_plugin',
                                    'key'       => 'free_plugin',
                                ),
                                array(
                                    'title'     => __( 'Premium Add-on', 'woocommerce-wholesale-prices' ),
                                    'dataIndex' => 'premium_addon',
                                    'key'       => 'premium_addon',
                                ),
                            ),
                            'data'       => array(
                                array(
                                    'key'           => '1',
                                    'features'      => __( 'Flexible wholesale pricing', 'woocommerce-wholesale-prices' ),
                                    'free_plugin'   => __( 'Not available. Only basic wholesale pricing at the product level allowed.', 'woocommerce-wholesale-prices' ),
                                    'premium_addon' => __( 'Set wholesale pricing at the global (%), category (%) or the product level. Also includes quantity based pricing.', 'woocommerce-wholesale-prices' ),
                                ),
                                array(
                                    'key'           => '2',
                                    'features'      => __( 'Product visibility control', 'woocommerce-wholesale-prices' ),
                                    'free_plugin'   => __( 'Not available', 'woocommerce-wholesale-prices' ),
                                    'premium_addon' => __( 'Make products "Wholesale Only", hide "Retail Only" products from wholesale customers, create variations that are "Wholesale Only".', 'woocommerce-wholesale-prices' ),
                                ),
                                array(
                                    'key'           => '3',
                                    'features'      => __( 'Multiple wholesale role levels', 'woocommerce-wholesale-prices' ),
                                    'free_plugin'   => __( 'Not available. Only one wholesale role.', 'woocommerce-wholesale-prices' ),
                                    'premium_addon' => __( 'Add multiple wholesale role levels and use them to manage wholesale pricing, shipping mapping, payment mapping, tax exemption, order minimums and more.', 'woocommerce-wholesale-prices' ),
                                ),
                                array(
                                    'key'           => '4',
                                    'features'      => __( 'Advanced tax control', 'woocommerce-wholesale-prices' ),
                                    'free_plugin'   => __( 'Not available', 'woocommerce-wholesale-prices' ),
                                    'premium_addon' => __( 'Fine grained control over price tax display for wholesale, tax exemptions per user role and more.', 'woocommerce-wholesale-prices' ),
                                ),
                                array(
                                    'key'           => '5',
                                    'features'      => __( 'Shipping method mapping', 'woocommerce-wholesale-prices' ),
                                    'free_plugin'   => __( 'Not available', 'woocommerce-wholesale-prices' ),
                                    'premium_addon' => __( 'Manage which shipping methods wholesale customers can see and use compared to retail customers.', 'woocommerce-wholesale-prices' ),
                                ),
                                array(
                                    'key'           => '6',
                                    'features'      => __( 'Payment gateway mapping', 'woocommerce-wholesale-prices' ),
                                    'free_plugin'   => __( 'Not available', 'woocommerce-wholesale-prices' ),
                                    'premium_addon' => __( 'Manage which payment gateways wholesale customers can see and use compared to retail customers.', 'woocommerce-wholesale-prices' ),
                                ),
                                array(
                                    'key'           => '7',
                                    'features'      => __( 'Set product and order minimums', 'woocommerce-wholesale-prices' ),
                                    'free_plugin'   => __( 'Not available', 'woocommerce-wholesale-prices' ),
                                    'premium_addon' => __( 'Use product minimums and order minimums to ensure wholesale customers are meeting requirements.', 'woocommerce-wholesale-prices' ),
                                ),
                            ),
                        ),
                        array(
                            'type'    => 'paragraph',
                            'id'      => 'wwp_paragraph_feature',
                            'content' => __( '+100\'s of other premium wholesale features', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'         => 'button',
                            'id'           => 'wwp_feature_list_btn',
                            'button_label' => __( 'See the full feature list', 'woocommerce-wholesale-prices' ),
                            'link'         => 'https://wholesalesuiteplugin.com/woocommerce-wholesale-prices-premium/?utm_source=wwp&utm_medium=upsell&utm_campaign=upgradepagewwppbutton',
                            'external'     => true,
                        ),
                    ),
                ),
            );

            $upgrade_controls['upgrade_options2'] = array(
                array(
                    'type'    => 'html',
                    'id'      => 'wwp_settings_upgrade_code_block2',
                    'classes' => 'wwp-upgrade-code2-block',
                    'fields'  => array(
                        array(
                            'type'    => 'heading',
                            'id'      => 'wwp_heading_upgrade2',
                            'tag'     => 'h2',
                            'content' => __( 'Wholesale Suite Bundle', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'    => 'paragraph',
                            'id'      => 'wwp_paragraph_upgrade2',
                            'content' => __( 'Everything you need to sell to wholesale customers in WooCommerce. The most complete wholesale solution for building wholesale sales into your existing WooCommerce driven store.', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'    => 'heading',
                            'id'      => 'wwp_heading_package1',
                            'tag'     => 'h3',
                            'content' => __( 'WooCommerce Wholesale Prices Premium', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'    => 'paragraph',
                            'id'      => 'wwp_paragraph_package1',
                            'content' => __( 'Easily add wholesale pricing to your products. Control product visibility. Satisfy your country\'s strictest tax requirements & control pricing display . Force wholesalers to use certain shipping & payment gateways . Enforce order minimums and individual product minimums . and 100\'s of other product and pricing related wholesale features.', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'    => 'image',
                            'id'      => 'wwp_bundle_image1',
                            'classes' => 'wwp-bundle-img',
                            'url'     => esc_url( WWP_IMAGES_URL ) . 'upgrade-page-wwpp-box.png',
                        ),
                        array(
                            'type'         => 'button',
                            'id'           => 'wwp_bundle_link1',
                            'button_label' => __( 'Learn more about Prices Premium', 'woocommerce-wholesale-prices' ),
                            'link'         => 'https://wholesalesuiteplugin.com/woocommerce-wholesale-prices-premium/?utm_source=wwp&utm_medium=upsell&utm_campaign=upgradepagewwpplearnmore',
                            'external'     => true,
                            'classes'      => 'wwp-package-link',
                        ),
                        array(
                            'type'    => 'heading',
                            'id'      => 'wwp_heading_package2',
                            'tag'     => 'h3',
                            'content' => __( 'WoWooCommerce Wholesale Order Form', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'    => 'paragraph',
                            'id'      => 'wwp_paragraph_package2',
                            'content' => __( 'Decrease frustration and increase order size with the most efficient one-page WooCommerce order form. Your wholesale customers will love it. No page loading means less back & forth, full ajax enabled add to cart buttons, responsive layout for on-the-go ordering and your whole product catalog available at your customer\'s fingertips.', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'    => 'image',
                            'id'      => 'wwp_bundle_image2',
                            'classes' => 'wwp-bundle-img',
                            'url'     => esc_url( WWP_IMAGES_URL ) . 'upgrade-page-wwof-box.png',
                        ),
                        array(
                            'type'         => 'button',
                            'id'           => 'wwp_bundle_link2',
                            'button_label' => __( 'Learn more about Order Form', 'woocommerce-wholesale-prices' ),
                            'link'         => 'https://wholesalesuiteplugin.com/woocommerce-wholesale-order-form/?utm_source=wwp&utm_medium=upsell&utm_campaign=upgradepagewwoflearnmore',
                            'external'     => true,
                            'classes'      => 'wwp-package-link',
                        ),
                        array(
                            'type'    => 'heading',
                            'id'      => 'wwp_heading_package3',
                            'tag'     => 'h3',
                            'content' => __( 'WooCommerce Wholesale Lead Capture', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'    => 'paragraph',
                            'id'      => 'wwp_paragraph_package3',
                            'content' => __( 'Take the pain out of manually recruiting & registering wholesale customers. Lead Capture will save you admin time and recruit wholesale customers for your WooCommerce store on autopilot. Full registration form builder, automated email onboarding email sequence, full automated or manual approvals system and much more.', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'    => 'image',
                            'id'      => 'wwp_bundle_image3',
                            'classes' => 'wwp-bundle-img',
                            'url'     => esc_url( WWP_IMAGES_URL ) . 'upgrade-page-wwlc-box.png',
                        ),
                        array(
                            'type'         => 'button',
                            'id'           => 'wwp_bundle_link3',
                            'button_label' => __( 'Learn more about Lead Capture', 'woocommerce-wholesale-prices' ),
                            'link'         => 'https://wholesalesuiteplugin.com/woocommerce-wholesale-lead-capture/?utm_source=wwp&utm_medium=upsell&utm_campaign=upgradepagewwlclearnmore',
                            'external'     => true,
                            'classes'      => 'wwp-package-link',
                        ),
                        array(
                            'type'    => 'paragraph',
                            'id'      => 'wwp_paragraph_upgrade2_2',
                            'content' => __( 'The WooCommerce extensions to grow your wholesale business', 'woocommerce-wholesale-prices' ),
                        ),
                        array(
                            'type'         => 'button',
                            'id'           => 'wwp_full_bundle_btn',
                            'button_label' => __( 'See the full bundle now', 'woocommerce-wholesale-prices' ),
                            'link'         => 'https://wholesalesuiteplugin.com/bundle/?utm_source=wwp&utm_medium=upsell&utm_campaign=upgradepagebundlebutton',
                            'external'     => true,
                        ),
                    ),
                ),
            );

            return $upgrade_controls;
        }

        /**
         * Execute model.
         *
         * @since  2.0
         * @access public
         */
        public function run() {

            add_action( 'admin_head', array( $this, 'preload_imports' ), 20 );

            // Load react scripts.
            add_action( 'admin_enqueue_scripts', array( $this, 'load_back_end_styles_and_scripts' ), 10, 1 );

            // REST API for dashboard page.
            add_action( 'rest_api_init', array( $this, 'rest_api_settings' ) );
        }
    }
}
