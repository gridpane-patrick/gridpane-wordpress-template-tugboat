<?php
/**
 * Class that loads shipping method Aramex.
 *
 * This class will be loaded for WC >= 2.6 where shipping zone already supported.
 * The legacy class with the same name can be found in /legacy/ directory.
 *
 * @package WC_Shipping_Aramex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add aramex shipping method.
 *
 * @since 1.0.2
 */
class WC_Shipping_Aramex extends WC_Shipping_Method {
	/**
	 * Constructor.
	 *
	 * Set basic properies and does initialization.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param int $instance_id Instance ID.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'wc_shipping_aramex';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Aramex', 'woocommerce-shipping-aramex' );
		$this->method_description = __( 'Aramex delivery shipping method. Your account country will be used by Aramex for rate currency.', 'woocommerce-shipping-aramex' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'settings',
		);

		$this->init();
	}

	/**
	 * Init.
	 */
	public function init() {
		// Check for soap.
		if ( ! class_exists( 'SoapClient' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_soap_error' ) );
		}

		$this->init_form_fields();
		$this->set_settings();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

		// Display an admin notice if API credentials are required, and not on the shipping method screen itself.
		if ( '' == $this->account_entity || '' == $this->account_number || '' == $this->account_pin || '' == $this->account_user_name || '' == $this->account_password ) {
			add_action( 'admin_notices', array( $this, 'notice_specify_api_credentials' ) );
		}
	}

	/**
	 * Set settings from DB to instance properties for handy retrieval.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 */
	public function set_settings() {
		$this->title                            = $this->get_option( 'title' );
		$this->debug                            = $this->get_option( 'debug' );
		$this->type                             = $this->get_option( 'type' );
		$this->aramex_product_type              = $this->get_option( 'aramex_product_type' );
		$this->account_entity                   = $this->get_option( 'account_entity' );
		$this->account_number                   = $this->get_option( 'account_number' );
		$this->account_pin                      = $this->get_option( 'account_pin' );
		$this->account_user_name                = $this->get_option( 'account_user_name' );
		$this->account_password                 = $this->get_option( 'account_password' );
		$this->account_city                     = $this->get_option( 'account_city' );
		$this->account_address1                 = $this->get_option( 'account_address1' );
		$this->account_address2                 = $this->get_option( 'account_address2' );
		$this->account_address3                 = $this->get_option( 'account_address3' );
		$this->account_province_code            = $this->get_option( 'account_province_code' );
		$this->account_post_code                = $this->get_option( 'account_post_code' );
		$this->pickup_open_time                 = $this->get_option( 'pickup_open_time' );
		$this->pickup_close_time                = $this->get_option( 'pickup_close_time' );
		$this->pickup_contact_person_name       = $this->get_option( 'pickup_contact_person_name' );
		$this->pickup_contact_person_title      = $this->get_option( 'pickup_contact_person_title' );
		$this->pickup_contact_person_company    = $this->get_option( 'pickup_contact_person_company' );
		$this->pickup_contact_person_phone      = $this->get_option( 'pickup_contact_person_phone' );
		$this->pickup_contact_person_cell_phone = $this->get_option( 'pickup_contact_person_cell_phone' );
		$this->pickup_contact_person_email      = $this->get_option( 'pickup_contact_person_email' );
		$this->enable_box_packing               = $this->get_option( 'enable_box_packing' );
		$this->boxes                            = $this->get_option( 'boxes', array() );
		$this->test                             = $this->get_option( 'test' );
	}

	/**
	 * Process admin options when setting is updated.
	 *
	 * This makes sure instance's properties are set too.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 */
	public function process_admin_options() {
		parent::process_admin_options();

		$this->set_settings();
	}

	/**
	 * Display an admin notice, if not on the shipping method screen and if the
	 * account isn't yet connected.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function notice_specify_api_credentials() {
		// Don't show these notices on our admin screen.
		if ( ( isset( $_GET['page'] ) && ( 'woocommerce_settings' == $_GET['page'] || 'wc-settings' == $_GET['page'] ) ) && ( isset( $_GET['section'] ) && 'wc_shipping_aramex' == $_GET['section'] ) ) {
			return;
		}

		$url = add_query_arg(
			array(
				'page'    => 'wc-settings',
				'tab'     => 'shipping',
				'section' => $this->id,
			),
			admin_url( 'admin.php' )
		);

		/* translators: 1) opening <strong>, 2) closing </strong>, 3) opening anchor tag to Aramex setting, and 4) closing anchor tag. */
		echo '<div class="updated fade"><p>' . sprintf( __( '%1$sWooCommerce Aramex is almost ready.%2$s To get started, %3$senter your Aramex account details%4$s.', 'woocommerce-shipping-aramex' ), '<strong>', '</strong>', '<a href="' . esc_url( $url ) . '">', '</a>' ) . '</p></div>' . "\n";
	}

	/**
	 * Render SOAP notice.
	 */
	public function notice_soap_error() {
		/* translators: 1) opening <strong>, 2) closing </strong>, 3) opening anchor tag to php.net, and 4) closing anchor tag. */
		echo '<div class="error fade"><p>' . sprintf( __( '%1$sYour server does not have the %3$sSOAP Client%4$s enabled.%2$s Please contact your web hosting provider and request that SOAP be enabled.', 'woocommerce-shipping-aramex' ), '<strong>', '</strong>', '<a href="' . esc_url( 'http://php.net/manual/en/class.soapclient.php' ) . '" target="_blank">', '</a>' ) . '</p></div>' . "\n";
	}

	/**
	 * Render box packing form.
	 */
	public function generate_box_packing_html() {
		ob_start();
		include( 'views/html-box-packing.php' );
		return ob_get_clean();
	}

	/**
	 * Validate box packing field.
	 *
	 * @return array Boxes.
	 */
	public function validate_box_packing_field() {
		$boxes = array();

		if ( isset( $_POST['aramex_boxes_outer_length'] ) ) {
			$boxes_name         = isset( $_POST['aramex_boxes_name'] ) ? $_POST['aramex_boxes_name'] : array();
			$boxes_outer_length = $_POST['aramex_boxes_outer_length'];
			$boxes_outer_width  = $_POST['aramex_boxes_outer_width'];
			$boxes_outer_height = $_POST['aramex_boxes_outer_height'];
			$boxes_inner_length = $_POST['aramex_boxes_inner_length'];
			$boxes_inner_width  = $_POST['aramex_boxes_inner_width'];
			$boxes_inner_height = $_POST['aramex_boxes_inner_height'];
			$boxes_box_weight   = $_POST['aramex_boxes_box_weight'];
			$boxes_max_weight   = $_POST['aramex_boxes_max_weight'];

			for ( $i = 0; $i < sizeof( $boxes_outer_length ); $i ++ ) {

				$has_outer_dimensions = (
					$boxes_outer_length[ $i ]
					&&
					$boxes_outer_width[ $i ]
					&&
					$boxes_outer_height[ $i ]
				);

				$has_inner_dimensions = (
					$boxes_inner_length[ $i ] &&
					$boxes_inner_width[ $i ] &&
					$boxes_inner_height[ $i ]
				);

				if ( $has_outer_dimensions && $has_inner_dimensions ) {

					$boxes[] = array(
						'name'         => wc_clean( $boxes_name[ $i ] ),
						'outer_length' => floatval( $boxes_outer_length[ $i ] ),
						'outer_width'  => floatval( $boxes_outer_width[ $i ] ),
						'outer_height' => floatval( $boxes_outer_height[ $i ] ),
						'inner_length' => floatval( $boxes_inner_length[ $i ] ),
						'inner_width'  => floatval( $boxes_inner_width[ $i ] ),
						'inner_height' => floatval( $boxes_inner_height[ $i ] ),
						'box_weight'   => floatval( $boxes_box_weight[ $i ] ),
						'max_weight'   => floatval( $boxes_max_weight[ $i ] ),
					);
				}
			}
		}

		return $boxes;
	}

	/**
	 * Register form fields for Aramex setting.
	 */
	public function init_form_fields() {
		/* translators: placeholders are opening and closing anchor tags. */
		$product_type_description = __( 'For international shipping. The product type involves the specification of certain features concerning the delivery of the product such as: Priority, Time Sensitivity, and whether it is a Document or Non-Document.' , 'woocommerce-shipping-aramex' );

		$product_type_description .= '<br />';

		/* translators: placeholders are opening and closing anchor tags. */
		$product_type_description .= sprintf( __( '%1$sView the differences between each of the product types.%2$s', 'woocommerce-shipping-aramex' ), '<a href="' . esc_url( 'http://www.aramex.com/content/uploads/109/232/42007/aramex-rates-calculator-manual.pdf#page=25&zoom=auto,60,634' ) . '" target="_blank">', '</a>' );

		$this->instance_form_fields = array(
			'title' => array(
				'title'       => __( 'Method Title', 'woocommerce-shipping-aramex' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-shipping-aramex' ),
				'default'     => __( 'Aramex Shipping Delivery', 'woocommerce-shipping-aramex' ),
				'desc_tip'    => true,
			),
			'aramex_product_type' => array(
				'title'       => __( 'Aramex Product Type', 'woocommerce-shipping-aramex' ),
				'type'        => 'select',
				'description' => $product_type_description,
				'default'     => 'PDX',
				'options'     => array(
					'PDX' => __( 'Priority Document', 'woocommerce-shipping-aramex' ),
					'PPX' => __( 'Priority Parcel', 'woocommerce-shipping-aramex' ),
					'PLX' => __( 'Priority Letter', 'woocommerce-shipping-aramex' ),
					'DDX' => __( 'Deferred Document', 'woocommerce-shipping-aramex' ),
					'DPX' => __( 'Deferred Parcel', 'woocommerce-shipping-aramex' ),
					'GDX' => __( 'Ground Document', 'woocommerce-shipping-aramex' ),
					'GPX' => __( 'Ground Parcel', 'woocommerce-shipping-aramex' ),
				),
			),
			'origin_address' => array(
				'title' => __( 'Origin Address', 'woocommerce-shipping-aramex' ),
				'type'  => 'title',
			),
			'account_city' => array(
				'title' => __( 'City', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'account_address1' => array(
				'title' => __( 'Address1', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'account_address2' => array(
				'title' => __( 'Address2', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'account_address3' => array(
				'title' => __( 'Address3', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'account_province_code' => array(
				'title' => __( 'State Or Province Code', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'account_post_code' => array(
				'title' => __( 'Post Code', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'pickup_details' => array(
				'title' => __( 'Pickup Details', 'woocommerce-shipping-aramex' ),
				'type'  => 'title',
			),
			'pickup_open_time' => array(
				'title'       => __( 'Open Time', 'woocommerce-shipping-aramex' ),
				'type'        => 'select',
				'description' => __( 'The time your store is open for pickup.', 'woocommerce-shipping-aramex' ),
				'options'     => $this->_get_time_options(),
			),
			'pickup_close_time' => array(
				'title'       => __( 'Close Time', 'woocommerce-shipping-aramex' ),
				'type'        => 'select',
				'description' => __( 'The time your store closes and isn\'t available for pickup.', 'woocommerce-shipping-aramex' ),
				'options'     => $this->_get_time_options(),
			),
			'pickup_contact_person' => array(
				'title' => __( 'Pickup Contact Person', 'woocommerce-shipping-aramex' ),
				'type'  => 'title',
			),
			'pickup_contact_person_name' => array(
				'title' => __( 'Contact Person Name', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'pickup_contact_person_title' => array(
				'title' => __( 'Contact Person Title', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'pickup_contact_person_company' => array(
				'title' => __( 'Contact Person Company', 'woocommerce-shipping-aramex' ),
				'type' => 'text',
			),
			'pickup_contact_person_phone' => array(
				'title' => __( 'Contact Person Phone', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'pickup_contact_person_cell_phone' => array(
				'title' => __( 'Contact Person Cell Phone', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'pickup_contact_person_email' => array(
				'title' => __( 'Contact Person Email', 'woocommerce-shipping-aramex' ),
				'type'  => 'text',
			),
			'enable_box_packing_title' => array(
				'title' => __( 'Box Packing', 'woocommerce-shipping-aramex' ),
				'type'  => 'title',
			),
			'enable_box_packing' => array(
				'title'   => __( 'Enable Box Packing', 'woocommerce-shipping-aramex' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable', 'woocommerce-shipping-aramex' ),
				'default' => 'no',
				'class'   => 'enable_box_packing',
			),
			'boxes' => array(
				'type'  => 'box_packing',
				'class' => 'box_packing_html',
			),
		);

		$this->form_fields = array(
			'test' => array(
				'title'   => __( 'Test Mode', 'woocommerce-shipping-aramex' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enabled', 'woocommerce-shipping-aramex' ),
				'default' => 'yes',
			),
			'client_account_info' => array(
				'title' => __( 'Aramex Account Info', 'woocommerce-shipping-aramex' ),
				'type'  => 'title',
			),
			'account_entity' => array(
				'title'       => __( 'Account Entity', 'woocommerce-shipping-aramex' ),
				'type'        => 'text',
				'description' => __( 'Identification Code/Number for Transmitting Party. This code should be provided to you by Aramex.', 'woocommerce-shipping-aramex' ),
				'desc_tip'    => false,
			),
			'account_number' => array(
				'title'       => __( 'Account Number', 'woocommerce-shipping-aramex' ),
				'type'        => 'text',
				'description' => __( 'The Customer’s Account number provided by Aramex when the contract is signed.', 'woocommerce-shipping-aramex' ),
				'desc_tip'    => false,
			),
			'account_pin' => array(
				'title'       => __( 'Account Pin', 'woocommerce-shipping-aramex' ),
				'type'        => 'text',
				'description' => __( 'A key that is given to account customers associated with the account number, so as to validate customer identity.', 'woocommerce-shipping-aramex' ),
				'desc_tip'    => false,
			),
			'account_user_name' => array(
				'title'       => __( 'User Name', 'woocommerce-shipping-aramex' ),
				'type'        => 'text',
				'description' => __( 'A unique user name sent to the customer upon registration with Aramex.', 'woocommerce-shipping-aramex' ),
				'desc_tip'    => false,
			),
			'account_password' => array(
				'title'       => __( 'Password', 'woocommerce-shipping-aramex' ),
				'type'        => 'text',
				'description' => __( 'A unique password to verify the user name, sent to the client upon registration with Aramex.', 'woocommerce-shipping-aramex' ),
				'desc_tip'    => false,
			),
			'enable_debug_title' => array(
				'title' => __( 'Debug', 'woocommerce-shipping-aramex' ),
				'type'  => 'title',
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'woocommerce-shipping-aramex' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce-shipping-aramex' ),
				'default'     => 'no',
				/* translators: log pathfile. */
				'description' => sprintf( __( 'Log Aramex events, such as API requests, inside %s', 'woocommerce-shipping-aramex' ), '<code>woocommerce/logs/' . $this->id . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.txt</code>' ),
			),
		);
	}

	/**
	 * Generate an array of available time options.
	 *
	 * @since  1.0.0
	 * @return array Time options.
	 */
	private function _get_time_options() {
		$response = array();
		for ( $i = 0; $i <= 23; $i++ ) {
			$value = str_pad( $i, 2, '0', STR_PAD_LEFT );
			$response[ $value . ':00' ] = $value . ':00';
			$response[ $value . ':15' ] = $value . ':15';
			$response[ $value . ':30' ] = $value . ':30';
			$response[ $value . ':45' ] = $value . ':45';
		}
		return $response;
	}

	/**
	 * Check whether this shipping method is available or not.
	 *
	 * @param array $package Package.
	 *
	 * @return bool Returns true if available.
	 */
	public function is_available( $package ) {
		if ( empty( $package['destination']['country'] ) ) {
			return false;
		}

		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', true, $package );
	}

	/**
	 * Render the admin settings.
	 *
	 * @todo there are some SOAP notices renderer scattered, make sure to
	 *       consolidate in a single location.
	 *
	 * @since 1.0.0
	 * @version 1.0.2
	 */
	public function admin_options() {
		if ( ! extension_loaded( 'soap' ) ) {
			?>
			<p style="color: red">Note: the <b>SOAP</b> extension must be loaded for Aramex shipping to work. Contact your hosting company, ISP or systems administrator and request that the soap extension be activated.</span></p>
			<?php
		}

		parent::admin_options();
	}

	/**
	 * Get production client info.
	 *
	 * @return array Client info.
	 */
	public function get_client_info() {
		if ( 'yes' === $this->test ) {
			return $this->get_test_client_info();
		}

		return array(
			'AccountCountryCode' => WC()->countries->get_base_country(),
			'AccountEntity'      => $this->account_entity,
			'AccountNumber'      => $this->account_number,
			'AccountPin'         => $this->account_pin,
			'UserName'           => $this->account_user_name,
			'Password'           => $this->account_password,
			'Version'            => 'V1.0',
		);
	}

	/**
	 * Get test client info.
	 *
	 * @return array Client info.
	 */
	public function get_test_client_info() {
		return array(
			'AccountCountryCode' => '',
			'AccountEntity'      => '',
			'AccountNumber'      => '',
			'AccountPin'         => '',
			'UserName'           => 'testingapi@aramex.com',
			'Password'           => 'R123456789$r',
			'Version'            => 'v1.0',
		);
	}

	/**
	 * Get parameters for origin address when requesting rates calculation.
	 *
	 * @return array Origin address.
	 */
	public function rates_get_origin_address() {
		return array(
			'Line1'               => $this->account_address1,
			'Line2'               => $this->account_address2,
			'Line3'               => $this->account_address3,
			'City'                => $this->account_city,
			'StateOrProvinceCode' => $this->account_province_code,
			'PostCode'            => $this->account_post_code,
			'CountryCode'         => WC()->countries->get_base_country(),
		);
	}

	/**
	 * Exchange rates calculator
	 *
	 * @param array $params Request params.
	 *
	 * @return mixed Response.
	 */
	public function calculate_rates( $params ) {
		try {
			$params['ClientInfo']    = $this->get_client_info();
			$params['OriginAddress'] = $this->rates_get_origin_address();

			$debugParams = $params;
			unset( $debugParams['ClientInfo']['Password'] );

			/* translators: placeholder is dumped API request parameters. */
			$this->log_debug( sprintf( __( 'Aramex calculate rates API request params:<br><pre>%s</pre>', 'woocommerce-shipping-aramex' ), print_r( $debugParams, true ) ) );

			$soap_client = new SoapClient(
				WC_Aramex()->plugin_dir . '/wsdl/aramex-rates-calculator-wsdl.wsdl',
				array(
					'trace' => 1,
				)
			);

			$results = $soap_client->CalculateRate( $params );

			/* translators: placeholder is dumped response from API. */
			$this->log_debug( sprintf( __( 'Aramex calculate rates API response:<br><pre>%s</pre>', 'woocommerce-shipping-aramex' ), print_r( $results, true ) ) );

			return $results;
		} catch ( SoapFault $e ) {
			/* translators: placeholder is SOAP error message. */
			$this->log_debug( sprintf( __( 'Aramex SOAP error: %s.', 'woocommerce-shipping-aramex' ), $e->getMessage() ), 'error' );

			return null;
		}
	}

	/**
	 * Get params for rates calculator request.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param array $package Package to calculate.
	 *
	 * @return array Request params.
	 */
	public function get_calculate_rates_params( $package ) {
		return array(
			'Transaction' => array(
				'Reference1' => time(),
			),
			'DestinationAddress' => array(
				'Line1'               => WC()->customer->get_shipping_address(),
				'Line2'               => WC()->customer->get_shipping_address_2(),
				'City'                => WC()->customer->get_shipping_city(),
				'StateOrProvinceCode' => WC()->customer->get_shipping_state(),
				'PostCode'            => WC()->customer->get_shipping_postcode(),
				'CountryCode'         => WC()->customer->get_shipping_country(),
			),
			'ShipmentDetails' => array(
				'PaymentType'    => 'P',
				'ProductGroup'   => $this->get_package_product_group( $package ),
				'ProductType'    => $this->get_package_product_type( $package ),
				'NumberOfPieces' => WC()->cart->cart_contents_count,
				'ActualWeight'   => array(
					'Value' => wc_get_weight( WC()->cart->cart_contents_weight, 'kg' ),
					'Unit'  => 'KG', // Aramex accepts KG and LB, use KG here for weight-related.
				),
				'ChargeableWeight'   => array(
					'Value' => wc_get_weight( WC()->cart->cart_contents_weight, 'kg' ),
					'Unit'  => 'KG', // Aramex accepts KG and LB, use KG here for weight-related.
				),
			),
		);
	}

	/**
	 * Calculate shipping for a given package.
	 *
	 * @param array $package Package to calculate the cost.
	 */
	public function calculate_shipping( $package = array() ) {
		$this->calculate_shipping_check_cart_items();

		// Use box packing if box packing enabled and user custom boxes exist.
		// Otherwise calculate per item.
		if ( 'yes' === $this->enable_box_packing && ! empty( $this->boxes ) ) {
			$this->log_debug( __( 'Aramex calculate shipping cost with box packing.', 'woocommerce-shipping-aramex' ) );
			$this->calculate_shipping_with_box_packing( $package );
		} else {
			$this->log_debug( __( 'Aramex calculate shipping cost per item based.', 'woocommerce-shipping-aramex' ) );
			$this->calculate_shipping_per_item( $package );
		}
	}

	/**
	 * Check items in cart before calculate the shipping costs.
	 *
	 * This will log any notices or errors.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 */
	protected function calculate_shipping_check_cart_items() {
		foreach ( WC()->cart->get_cart() as $item ) {
			$product = wc_get_product( $item['product_id'] );

			if ( ! $product->needs_shipping() ) {
				/* translators: item is item's title */
				$this->log_debug( sprintf( __( 'Aramex: Product <strong>%s</strong> is virtual. Skipping.', 'woocommerce-shipping-aramex' ), $product->get_title() ) );
				continue;
			}

			if ( ! $product->has_dimensions() ) {
				/* translators: item is item's title */
				$this->log_debug( sprintf( __( 'Aramex error: There is no dimension for product <strong>%s</strong>.', 'woocommerce-shipping-aramex' ), $product->get_title() ), 'error' );
			}

			// Check for weight.
			if ( ! $product->get_weight() ) {
				/* translators: item is item's title */
				$this->log_debug( sprintf( __( 'Aramex error: There is no weight for product <strong>%s</strong>.', 'woocommerce-shipping-aramex' ), $product->get_title() ), 'error' );
			}
		}
	}

	/**
	 * Calculate shipping costs with box packing enabled.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param array $package Package to calculate.
	 */
	protected function calculate_shipping_with_box_packing( $package ) {
		if ( ! class_exists( 'WC_Boxpack' ) ) {
			require_once( WC_Aramex()->plugin_dir . '/includes/box-packer/class-wc-boxpack.php' );
		}

		$boxpack = new WC_Boxpack();

		// Define boxes.
		foreach ( $this->boxes as $box ) {
			// Convert dimension to cm and weight to kg as Aramex accept those
			// units.
			$b = $boxpack->add_box(
				wc_get_dimension( $box['outer_length'], 'cm' ),
				wc_get_dimension( $box['outer_width'], 'cm' ),
				wc_get_dimension( $box['outer_height'], 'cm' ),
				wc_get_weight( $box['box_weight'], 'kg' )
			);
			$b->set_inner_dimensions(
				wc_get_dimension( $box['inner_length'], 'cm' ),
				wc_get_dimension( $box['inner_width'], 'cm' ),
				wc_get_dimension( $box['inner_height'], 'cm' )
			);

			if ( $box['max_weight'] ) {
				$b->set_max_weight( wc_get_weight( $box['max_weight'], 'kg' ) );
			}

			if ( ! empty( $box['name'] ) ) {
				$b->set_id( $box['name'] );
			}
		}

		// Add items to defined boxes.
		foreach ( $package['contents'] as $item_id => $values ) {
			if ( ! $values['data']->needs_shipping() ) {
				continue;
			}

			$has_dimensions_and_weight = (
				$values['data']->get_length() &&
				$values['data']->get_height() &&
				$values['data']->get_width() &&
				$values['data']->get_weight()
			);

			if ( ! $has_dimensions_and_weight ) {
				$this->log_debug( __( 'Aramex error: Product missing dimensions or weight. Abort box packing calculation.', 'woocommerce-shipping-aramex' ), 'error' );
				return;
			}

			for ( $i = 0; $i < $values['quantity']; $i ++ ) {
				// Convert dimension to cm and weight to kg as Aramex accept those
				// units.
				$boxpack->add_item(
					wc_get_dimension( $values['data']->get_length(), 'cm' ),
					wc_get_dimension( $values['data']->get_height(), 'cm' ),
					wc_get_dimension( $values['data']->get_width(), 'cm' ),
					wc_get_weight( $values['data']->get_weight(), 'kg' ),
					$values['data']->get_price()
				);
			}
		}

		// Pack it.
		$boxpack->pack();
		$box_packages = $boxpack->get_packages();

		// Nothing fits.
		if ( empty( $box_packages ) ) {
			$this->log_debug( __( 'Aramex: no box fits for the pacakge.', 'woocommerce-shipping-aramex' ) );
			return;
		}

		$params = $this->get_calculate_rates_params( $package );

		$items        = array();
		$total_weight = 0;
		foreach ( $box_packages as $key => $box_package ) {
			/* translators: 1) box package index 2) box package contents */
			$this->log_debug( sprintf( __( 'Aramex box package #%1$s:<br><pre>%2$s</pre>', 'woocommerce-shipping-aramex' ), $key, print_r( $box_package, true ) ) );

			// Convert weight to kg as Aramex accepts that.
			$items[] = array(
				'Weight'   => array(
					'Unit'  => 'KG',
					'Value' => wc_get_weight( $box_package->weight, 'kg' ),
				),
				'Quantity' => 1,
			);

			$total_weight += $box_package->weight;
		}

		$params['ShipmentDetails']['Items']                     = $items;
		$params['ShipmentDetails']['ActualWeight']['Value']     = $total_weight;
		$params['ShipmentDetails']['ChargeableWeight']['Value'] = $total_weight;
		$params['ShipmentDetails']['NumberOfPieces']            = count( $box_packages );

		$results = $this->calculate_rates( $params );

		if ( ! is_null( $results ) && empty( $results->HasErrors ) ) {
			$rate = array(
				'label'    => $this->title,
				'cost'     => empty( $results->TotalAmount->Value ) ? 0.0 : $results->TotalAmount->Value,
				'calc_tax' => 'per_item',
				'package'  => $package,
			);

			// Register the rate.
			$this->add_rate( $rate );
		} else {
			/* translators: placeholder is dumped response from API. */
			$this->log_debug( sprintf( __( 'Aramex calculate rates API response:<br><pre>%s</pre>', 'woocommerce-shipping-aramex' ), print_r( $results, true ) ) );
		}
	}

	/**
	 * Calculate shipping cost per item.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param array $package Package to calculate.
	 */
	protected function calculate_shipping_per_item( $package ) {
		$items = array();
		foreach ( $package['contents'] as $values ) {
			if ( ! $values['data']->needs_shipping() ) {
				continue;
			}

			$has_dimensions_and_weight = (
				$values['data']->get_length() &&
				$values['data']->get_height() &&
				$values['data']->get_width() &&
				$values['data']->get_weight()
			);

			if ( $has_dimensions_and_weight ) {
				// Convert weight to kg as Aramex accepts that.
				$items[] = array(
					'Weight'   => array(
						'Unit'  => 'KG',
						'Value' => $values['quantity'] * wc_get_weight( $values['data']->get_weight(), 'kg' ),
					),
					'Quantity' => $values['quantity'],
				);
			}
		}

		if ( empty( $items ) ) {
			$this->log_debug( __( 'Aramex: no pacakge to calculate.', 'woocommerce-shipping-aramex' ) );
			return;
		}

		$params  = $this->get_calculate_rates_params( $package );

		$params['ShipmentDetails']['Items'] = $items;

		$results = $this->calculate_rates( $params );

		if ( ! is_null( $results ) && empty( $results->HasErrors ) ) {
			$this->log_debug( __( 'Found rates from Aramex.', 'woocommerce-shipping-aramex' ) );

			$rate = array(
				'label'    => $this->title,
				'cost'     => empty( $results->TotalAmount->Value ) ? 0.0 : $results->TotalAmount->Value,
				'calc_tax' => 'per_item',
			);

			// Register the rate.
			$this->add_rate( $rate );
		} else {
			$this->log_debug( __( 'No rates returned from Aramex.', 'woocommerce-shipping-aramex' ) );
		}
	}

	/**
	 * Get product type, to be passed to Aramex, from a given package.
	 *
	 * For product group domestic ('DOM') valid product type is only 'OND', otherwise
	 * use selected product type in setting instance.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param array $package Package to check. See `self::calculate_shipping`.
	 *
	 * @return string Product type.
	 */
	protected function get_package_product_type( $package ) {
		return ( WC()->countries->get_base_country() !== $package['destination']['country'] ) ? $this->aramex_product_type : 'OND';
	}

	/**
	 * Get product type, to be passed to Aramex, from a given order.
	 *
	 * For product group domestic ('DOM') valid product type is only 'OND', otherwise
	 * use selected product type in setting instance.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param WC_Order $order Order to check.
	 *
	 * @return string Product type.
	 */
	protected function get_order_product_type( WC_Order $order ) {
		$destination = version_compare( WC_VERSION, '3.0', '<' ) ? $order->shipping_country : $order->get_shipping_country();
		return WC()->countries->get_base_country() !== $destination ? $this->aramex_product_type : 'OND';
	}

	/**
	 * Get product group, to be passed to Aramex, from a given package.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param array $package Package to check. See `self::calculate_shipping`.
	 *
	 * @return string Product group.
	 */
	protected function get_package_product_group( array $package ) {
		return ( WC()->countries->get_base_country() !== $package['destination']['country'] ) ? 'EXP' : 'DOM';
	}

	/**
	 * Get product group, to be passed to Aramex, from a given order.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param WC_Order $order Order to check.
	 *
	 * @return string Product group.
	 */
	protected function get_order_product_group( WC_Order $order ) {
		$destination = version_compare( WC_VERSION, '3.0', '<' ) ? $order->shipping_country : $order->get_shipping_country();
		return WC()->countries->get_base_country() !== $destination ? 'EXP' : 'DOM';
	}

	/**
	 * Log debug message.
	 *
	 * @since 1.0.2
	 * @version 1.0.2
	 *
	 * @param string $message Debug message.
	 * @param string $type    Debug message type.
	 */
	public function log_debug( $message, $type = 'notice' ) {
		if ( 'yes' !== $this->debug ) {
			return;
		}

		// Make sure only admin can see the notice. The check for wc_add_notice
		// is necessary in case it's called from non front-end request where
		// `wc_add_notice` is not available.
		if ( current_user_can( 'manage_options' ) && function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( $message, $type );
		}

		if ( empty( $this->log ) ) {
			$this->log = new WC_Logger();
		}
		$this->log->add( $this->id, $message );
	}
}
