<?php
/**
 * Class that loads shipping method Aramex.
 *
 * This is legacy class for WC < 2.6.
 *
 * @package WC_Shipping_Aramex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add aramex shipping method.
 */
class WC_Shipping_Aramex extends WC_Shipping_Method {
	/**
	 * Constructor.
	 *
	 * Set basic properies and does initialization.
	 */
	public function __construct() {
		$this->id           = 'wc_shipping_aramex';
		$this->method_title = __( 'Aramex', 'woocommerce-shipping-aramex' );

		$this->init();

		$this->maybe_init_actions();
	}

	/**
	 * Make sure certain actions only run on the first instantiation of this class.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function maybe_init_actions() {
		if ( false === get_transient( $this->id . '_has_run_actions' ) ) {
			if ( '' == $this->account_entity || '' == $this->account_number || '' == $this->account_pin || '' == $this->account_user_name || '' == $this->account_password ) {
				// Display an admin notice if API credentials are required, and not on the shipping method screen itself.
				add_action( 'admin_notices', array( $this, 'notice_specify_api_credentials' ) );
			} else {
				// Specify a method to run when the "aramex_pickup" AJAX event is fired.
				add_action( 'wp_ajax_aramex_pickup', array( $this, 'ajax_pickup_callback' ) );

				// Register a meta box to display order information from Aramex.
				add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'pickup_request_form' ) );

				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			}

			set_transient( $this->id . '_has_run_actions', '' );
		} else {
			delete_transient( $this->id . '_has_run_actions' );
		}
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
		$this->init_settings();

		$this->title                            = $this->get_option( 'title' );
		$this->debug                            = $this->get_option( 'debug' );
		$this->type                             = $this->get_option( 'type' );
		$this->availability                     = $this->get_option( 'availability' );
		$this->countries                        = $this->get_option( 'countries' );
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
		$this->test                             = $this->get_option( 'test' );

		$this->boxes = isset( $this->settings['boxes'] ) ? $this->settings['boxes'] : array();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

		if ( 'yes' === $this->debug ) {
			$this->log = new WC_Logger();
		}
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
			$aramex_boxes_name         = isset( $_POST['aramex_boxes_name'] ) ? $_POST['aramex_boxes_name'] : array();
			$aramex_boxes_outer_length = $_POST['aramex_boxes_outer_length'];
			$aramex_boxes_outer_width  = $_POST['aramex_boxes_outer_width'];
			$aramex_boxes_outer_height = $_POST['aramex_boxes_outer_height'];
			$aramex_boxes_inner_length = $_POST['aramex_boxes_inner_length'];
			$aramex_boxes_inner_width  = $_POST['aramex_boxes_inner_width'];
			$aramex_boxes_inner_height = $_POST['aramex_boxes_inner_height'];
			$aramex_boxes_box_weight   = $_POST['aramex_boxes_box_weight'];
			$aramex_boxes_max_weight   = $_POST['aramex_boxes_max_weight'];

			for ( $i = 0; $i < sizeof( $aramex_boxes_outer_length ); $i ++ ) {

				$has_outer_dimensions = (
					$aramex_boxes_outer_length[ $i ]
					&&
					$aramex_boxes_outer_width[ $i ]
					&&
					$aramex_boxes_outer_height[ $i ]
				);

				$has_inner_dimensions = (
					$aramex_boxes_inner_length[ $i ] &&
					$aramex_boxes_inner_width[ $i ] &&
					$aramex_boxes_inner_height[ $i ]
				);

				if ( $has_outer_dimensions && $has_inner_dimensions ) {

					$boxes[] = array(
						'name'         => wc_clean( $aramex_boxes_name[ $i ] ),
						'outer_length' => floatval( $aramex_boxes_outer_length[ $i ] ),
						'outer_width'  => floatval( $aramex_boxes_outer_width[ $i ] ),
						'outer_height' => floatval( $aramex_boxes_outer_height[ $i ] ),
						'inner_length' => floatval( $aramex_boxes_inner_length[ $i ] ),
						'inner_width'  => floatval( $aramex_boxes_inner_width[ $i ] ),
						'inner_height' => floatval( $aramex_boxes_inner_height[ $i ] ),
						'box_weight'   => floatval( $aramex_boxes_box_weight[ $i ] ),
						'max_weight'   => floatval( $aramex_boxes_max_weight[ $i ] ),
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

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-shipping-aramex' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this shipping method', 'woocommerce-shipping-aramex' ),
				'default' => 'no',
			),
			'test' => array(
				'title'   => __( 'Test Mode', 'woocommerce-shipping-aramex' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enabled', 'woocommerce-shipping-aramex' ),
				'default' => 'yes',
			),
			'title' => array(
				'title'       => __( 'Method Title', 'woocommerce-shipping-aramex' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-shipping-aramex' ),
				'default'     => __( 'Aramex Shipping Delivery', 'woocommerce-shipping-aramex' ),
				'desc_tip'    => true,
			),
			'availability' => array(
				'title'         => __( 'Availability', 'woocommerce-shipping-aramex' ),
				'type'          => 'select',
				'class'         => 'availability_select',
				'description'   => '',
				'default'       => 'including',
				'options'       => array(
					'all'       => __( 'All Countries', 'woocommerce-shipping-aramex' ),
					'including' => __( 'Selected countries', 'woocommerce-shipping-aramex' ),
					'excluding' => __( 'Excluding selected countries', 'woocommerce-shipping-aramex' ),
				),
			),
			'countries' => array(
				'title'   => __( 'Countries', 'woocommerce-shipping-aramex' ),
				'type'    => 'multiselect',
				'class'   => 'chosen_select availability_countries_select',
				'css'     => 'width: 450px;',
				'default' => '',
				'options' => WC()->countries->get_allowed_countries(),
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
			'location_Address' => array(
				'title' => __( 'Address', 'woocommerce-shipping-aramex' ),
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

		global $woocommerce;

		if ( 'no' === $this->enabled ) {
			return false;
		}

		if ( 'including' === $this->availability ) {
			if ( is_array( $this->countries ) ) {
				if ( ! in_array( $package['destination']['country'], $this->countries ) ) {
					return false;
				}
			}
		} elseif ( 'excluding' === $this->availability ) {
			if ( is_array( $this->countries ) ) {
				if ( in_array( $package['destination']['country'], $this->countries ) ) {
					return false;
				}
			}
		}
		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', true );
	}

	/**
	 * Render the admin settings.
	 */
	public function admin_options() {
		?>
		<h3><?php echo esc_html( $this->method_title ); ?></h3>
		<p><?php _e( 'Aramex delivery shipping method.', 'woocommerce-shipping-aramex' ); ?></p>
		<strong><?php _e( 'Aramex will use your account country for rate currency.', 'woocommerce-shipping-aramex' ); ?></strong>
		<?php
		if ( ! extension_loaded( 'soap' ) ) {
			?>
			<p style="color: red">Note: the <b>SOAP</b> extension must be loaded for Aramex shipping to work. Contact your hosting company, ISP or systems administrator and request that the soap extension be activated.</span></p>
			<?php
		}
		?>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table> <?php
	}

	/**
	 * Get production client info.
	 *
	 * @return array Client info.
	 */
	public function get_client_info() {
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
			'AccountCountryCode' => 'JO',
			'AccountEntity'      => 'AMM',
			'AccountNumber'      => '20016',
			'AccountPin'         => '331421',
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
			$params['ClientInfo'] = $this->get_client_info();
			$params['OriginAddress'] = $this->rates_get_origin_address();

			$soap_client = new SoapClient(
				WC_Aramex()->plugin_dir . '/wsdl/aramex-rates-calculator-wsdl.wsdl',
				array(
					'trace' => 1,
				)
			);

			$results = $soap_client->CalculateRate( $params );

			return $results;
		} catch ( SoapFault $fault ) {
			return null;
		}
	}

	/**
	 * Get pickup address parameters.
	 *
	 * @return array Pick address.
	 */
	public function get_pickup_address() {
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
	 * Get pickup contact parameters.
	 *
	 * @return array Pickup contact.
	 */
	public function get_pickup_contact() {
		return array(
			'Department'      => '',
			'PersonName'      => $this->pickup_contact_person_name,
			'Title'           => $this->pickup_contact_person_title,
			'CompanyName'     => $this->pickup_contact_person_company,
			'PhoneNumber1'    => $this->pickup_contact_person_phone,
			'PhoneNumber1Ext' => '',
			'PhoneNumber2'    => '',
			'PhoneNumber2Ext' => '',
			'FaxNumber'       => '',
			'CellPhone'       => $this->pickup_contact_person_cell_phone,
			'EmailAddress'    => $this->pickup_contact_person_email,
			'Type'            => '',
		);
	}

	/**
	 * Handles pickup AJAX.
	 */
	public function ajax_pickup_callback() {
		try {
			$order_id    = $_POST['order_id'];
			$order       = wc_get_order( $order_id );
			$pickup_date = new DateTime( urldecode( $_POST['pickup_date'] ) );

			// Check for product group.
			$product_group = 'DOM';
			$product_type  = 'ONP';
			if ( WC()->countries->get_base_country() !== $order->shipping_country ) {
				$product_group = 'EXP';
				$product_type = $this->aramex_product_type;
			}

			$params = array(
				'Transaction' => array(
					'Reference1' => time(),
					'Reference2' => '',
					'Reference3' => '',
					'Reference4' => '',
					'Reference5' => '',
				),
				'LabelInfo' => array(
					'ReportID' => 9201,
					'ReportType' => 'URL',
				),
				'Pickup' => array(
					'PickupAddress'  => $this->get_pickup_address(),
					'PickupContact'  => $this->get_pickup_contact(),
					'PickupLocation' => 'Reception',
					'PickupDate'     => date_format( $pickup_date, DATE_W3C ),
					'Comments'       => '',
					'Reference1'     => 'Ref Pickup ' . time(),
					'Reference2'     => '',
					'Vehicle'        => '',
					'Status'         => 'Ready',
					'PickupItems'    => array(
						array(
							'ProductGroup'      => $product_group,
							'ProductType'       => $product_type,
							'Payment'           => 'P',
							'NumberOfPieces'    => $order->get_item_count(),
							'NumberOfShipments' => 1,
							'PackageType'       => '',
							'Comments'          => '',
						),
					),
				),
			);

			$params['ClientInfo'] = ( 'yes' === $this->test )
				? $this->get_test_client_info()
				: $this->get_client_info();

			$params['Pickup']['LastPickupTime'] = date_format( $pickup_date, DATE_W3C );

			// Get close & start time.
			$close_time       = $this->pickup_close_time;
			$close_time_h     = 00;
			$close_time_m     = 00;
			$close_time_array = explode( ':', $close_time );

			if ( isset( $close_time_array['0'] ) ) {
				$close_time_h = $close_time_array['0'];
			}

			if ( isset( $close_time_array['1'] ) ) {
				$close_time_m = $close_time_array['1'];
			}

			$open_time       = $this->pickup_open_time;
			$open_time_h     = 00;
			$open_time_m     = 00;
			$open_time_array = explode( ':', $open_time );

			if ( isset( $open_time_array['0'] ) ) {
				$open_time_h = $open_time_array['0'];
			}
			if ( isset( $open_time_array['1'] ) ) {
				$open_time_m = $open_time_array['1'];
			}

			$pickup_date->setTime( $open_time_h, $open_time_m, 00 );
			$params['Pickup']['ReadyTime'] = date_format( $pickup_date, DATE_W3C );

			$pickup_date->setTime( $close_time_h, $close_time_m, 00 );
			$params['Pickup']['ClosingTime'] = date_format( $pickup_date, DATE_W3C );

			$soap_client = new SoapClient(
				WC_Aramex()->plugin_dir . '/wsdl/shipping-services-api-wsdl.wsdl',
				array(
					'trace' => 1,
				)
			);

			if ( 'yes' === $this->test ) {
				$soap_client->__setLocation( 'http://ws.dev.aramex.net/shippingapi/shipping/service_1_0.svc' );
			} else {
				$soap_client->__setLocation( 'http://ws.aramex.net/shippingapi/shipping/service_1_0.svc' );
			}

			$results = $soap_client->CreatePickup( $params );

			// Check for error.
			if ( empty( $results->HasErrors ) ) {
				update_post_meta( $order_id, '_pickup_id', $results->ProcessedPickup->ID );
				update_post_meta( $order_id, '_pickup_guid', $results->ProcessedPickup->GUID );

				_e( 'done', 'woocommerce-shipping-aramex' );
			} else {
				$errors = array();
				if ( is_array( $results->Notifications->Notification ) ) {
					foreach ( $results->Notifications->Notification as $error ) {
						$errors[] = $error->Message;
					}
				} else {
					$errors[] = $results->Notifications->Notification->Message;
				}
				echo json_encode( $errors );
			}
		} catch ( Exception $e ) {
			echo json_encode( array(
				$e->getMessage(),
			) );
		}

		die();
	}

	/**
	 * Calculate shipping for a given package.
	 *
	 * @param array $package Package to calculate the cost.
	 */
	public function calculate_shipping( $package = array() ) {
		if ( 'yes' === $this->debug ) {
			/* translators: placeholder is shipping method's title. */
			$this->log->add( $this->id, sprintf( __( '%s: Enter calculate shipping function.', 'woocommerce-shipping-aramex' ), $this->method_title ) );
		}

		// Get dimensions.
		$length       = array();
		$width        = array();
		$height       = array();
		$total_weight = WC()->cart->cart_contents_weight;
		$total_volume = 0;
		$boxes        = $this->boxes;

		foreach ( WC()->cart->get_cart() as $package ) {
			$item = wc_get_product( $package['product_id'] );

			if ( $item->has_dimensions() ) {
				$w = $item->get_width();
				$h = $item->get_height();
				$l = $item->get_length();

				$total_volume += $w * $h * $l * $package['quantity'];

				$length[]        = $l;
				$width[]         = $w;
				$height[]        = $h;
				$default_height[] = $h * $package['quantity'];
			} else {
				if ( $this->debug ) {
					/* translators: item is item's title */
					wc_add_notice( sprintf( __( 'There is no dimension for product <strong>%s</strong>.', 'woocommerce-shipping-aramex' ), $item->get_title() ), 'error' );
				}
			}

			// Check for weight.
			if ( ! $item->get_weight() && 'yes' === $this->debug ) {
					/* translators: item is item's title */
				$this->log->add( $this->id, sprintf( __( 'There is no weight for product <strong>%s</strong>.', 'woocommerce-shipping-aramex' ), $item->get_title() ) );
			}
		}

		// Check for dimensions.
		if ( count( $width ) == 0 || count( $length ) == 0 || count( $height ) == 0 ) {
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, __( 'There is no dimension for shippment.', 'woocommerce-shipping-aramex' ) );
			}
		}

		// Check for weight.
		if ( WC()->cart->cart_contents_weight == 0 ) {
			if ( 'yes' == $this->debug ) {
				$this->log->add( $this->id, __( 'There is no weight for shippment.', 'woocommerce-shipping-aramex' ) );
			}
		}

		// Check for product group.
		$product_group = 'DOM';
		$product_type  = 'OND';
		if ( WC()->customer->get_shipping_country() != WC()->countries->get_base_country() ) {
			$product_group = 'EXP';
			$product_type = $this->aramex_product_type;
		}

		$params = array(
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
				'ProductGroup'   => $product_group,
				'ProductType'    => $product_type,
				'NumberOfPieces' => WC()->cart->cart_contents_count,
				'Dimensions'     => array(),
			),
		);

		// Check if box packing enabled.
		if ( 'yes' === $this->enable_box_packing ) {

			// Check for box.
			$fit_box_index = null;
			foreach ( $boxes as $key => $box ) {
				$box_max_weight = $box['max_weight'];
				$box_volume     = $box['inner_length'] * $box['inner_width'] * $box['inner_height'];

				// Check if bix fit for weight & volume.
				if ( $box_max_weight >= $total_weight && $box_volume >= $total_volume ) {
					// Check if box fit for dimensions.
					if ( $box['inner_length'] >= max( $length ) && $box['inner_width'] >= max( $width ) && $box['inner_height'] >= max( $height ) || $box['inner_length'] >= max( $length ) && $box['inner_width'] >= max( $height ) && $box['inner_height'] >= max( $width ) ) {
						$fit_box_index = $key;
						break;
					}
				}
			}

			if ( isset( $boxes[ $fit_box_index ] ) ) {
				$fit_box = $boxes[ $fit_box_index ];

				$params['ShipmentDetails']['ActualWeight'] = array(
					'Value' => WC()->cart->cart_contents_weight + $fit_box['box_weight'],
					'Unit'  => get_option( 'woocommerce_weight_unit' ),
				);

				$params['ShipmentDetails']['ChargeableWeight'] = array(
					'Value' => WC()->cart->cart_contents_weight + $fit_box['box_weight'],
					'Unit'  => get_option( 'woocommerce_weight_unit' ),
				);

				$params['ShipmentDetails']['Dimensions']['Length'] = $fit_box['outer_length'];
				$params['ShipmentDetails']['Dimensions']['Width']  = $fit_box['outer_width'];
				$params['ShipmentDetails']['Dimensions']['Height'] = $fit_box['outer_height'];
				$params['ShipmentDetails']['Dimensions']['Unit']   = get_option( 'woocommerce_dimension_unit' );

				$results = $this->calculate_rates( $params );

				if ( ! is_null( $results ) && empty( $results->HasErrors ) ) {
					$rate = array(
						'id'       => $this->id,
						'label'    => $this->title,
						'cost'     => empty( $results->TotalAmount->Value ) ? 0.0 : $results->TotalAmount->Value,
						'calc_tax' => 'per_item',
					);

					// Register the rate.
					$this->add_rate( $rate );
				} else {
					if ( 'yes' === $this->debug ) {
						/* translators: placeholder is dumped response from API. */
						$this->log->add( $this->id, sprintf( __( 'Response: %s', 'woocommerce-shipping-aramex' ), print_r( $results, true ) ) );
					}
				}
			} else {
				$params['ShipmentDetails']['ActualWeight'] = array(
					'Value' => WC()->cart->cart_contents_weight,
					'Unit'  => get_option( 'woocommerce_weight_unit' ),
				);

				$params['ShipmentDetails']['ChargeableWeight'] = array(
					'Value' => WC()->cart->cart_contents_weight,
					'Unit'  => get_option( 'woocommerce_weight_unit' ),
				);

				$params['ShipmentDetails']['Dimensions']['Length'] = max( $length );
				$params['ShipmentDetails']['Dimensions']['Width']  = max( $width );
				$params['ShipmentDetails']['Dimensions']['Height'] = array_sum( $default_height );
				$params['ShipmentDetails']['Dimensions']['Unit']   = get_option( 'woocommerce_dimension_unit' );

				$results = $this->calculate_rates( $params );

				if ( ! is_null( $results ) && empty( $results->HasErrors ) ) {
					$rate = array(
						'id'       => $this->id,
						'label'    => $this->title,
						'cost'     => empty( $results->TotalAmount->Value ) ? 0.0 : $results->TotalAmount->Value,
						'calc_tax' => 'per_item',
					);

					// Register the rate.
					$this->add_rate( $rate );
				} else {
					if ( 'yes' === $this->debug ) {
						/* translators: placeholder is dumped response from API. */
						$this->log->add( $this->id, sprintf( __( 'Response: %s', 'woocommerce-shipping-aramex' ), print_r( $results, true ) ) );
					}
				}
			}
		} else {
			$params['ShipmentDetails']['ActualWeight'] = array(
				'Value' => WC()->cart->cart_contents_weight,
				'Unit'  => get_option( 'woocommerce_weight_unit' ),
			);

			$params['ShipmentDetails']['ChargeableWeight'] = array(
				'Value' => WC()->cart->cart_contents_weight,
				'Unit'  => get_option( 'woocommerce_weight_unit' ),
			);

			$params['ShipmentDetails']['Dimensions']['Length'] = max( $length );
			$params['ShipmentDetails']['Dimensions']['Width']  = max( $width );
			$params['ShipmentDetails']['Dimensions']['Height'] = array_sum( $default_height );
			$params['ShipmentDetails']['Dimensions']['Unit']   = get_option( 'woocommerce_dimension_unit' );

			$results = $this->calculate_rates( $params );

			if ( ! is_null( $results ) && empty( $results->HasErrors ) ) {
				$rate = array(
					'id'       => $this->id,
					'label'    => $this->title,
					'cost'     => empty( $results->TotalAmount->Value ) ? 0.0 : $results->TotalAmount->Value,
					'calc_tax' => 'per_item',
				);

				// Register the rate.
				$this->add_rate( $rate );
			} else {
				if ( 'yes' == $this->debug ) {
					/* translators: placeholder is dumped response from API. */
					$this->log->add( $this->id, sprintf( __( 'Response: %s', 'woocommerce-shipping-aramex' ), print_r( $results, true ) ) );
				}
			}
		}
	}

	/**
	 * Render pickup request form in admin order screen.
	 *
	 * @param WC_Order $order Current order instance.
	 */
	public function pickup_request_form( $order ) {
		// Check if pickup exist.
		$pickup_id   = get_post_meta( $order->ID, '_pickup_id', true );
		$pickup_guid = get_post_meta( $order->ID, '_pickup_guid', true );
		?>
		<p class="form-field form-field-wide">
			<label for="order_date"><?php _e( 'Pickup date:', 'woocommerce-shipping-aramex' ) ?></label>
			<input
				type="text"
				class="pickup_date date-picker"
				name="pickup_date" id="pickup_date"
				maxlength="10"
				value="<?php echo date_i18n( 'Y-m-d', strtotime( $order->post_date ) ); ?>"
				pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />@
			<input
				type="text"
				class="hour pickup_date_hour"
				placeholder="<?php _e( 'h', 'woocommerce-shipping-aramex' ) ?>"
				name="pickup_date_hour"
				id="pickup_date_hour"
				maxlength="2"
				size="2"
				value="<?php echo date_i18n( 'H', strtotime( $order->post_date ) ); ?>"
				pattern="\-?\d+(\.\d{0,})?" />:
			<input
				type="text"
				class="minute pickup_date_minute"
				placeholder="<?php _e( 'm', 'woocommerce-shipping-aramex' ) ?>"
				name="pickup_date_minute"
				id="pickup_date_minute"
				maxlength="2"
				size="2"
				value="<?php echo date_i18n( 'i', strtotime( $order->post_date ) ); ?>"
				pattern="\-?\d+(\.\d{0,})?" />

			<?php if ( empty( $pickup_id ) ) : ?>
				<a class="button-secondary aramex-pickup" href="#" data-id="<?php echo esc_attr( $order->ID ); ?>"><?php _e( 'Request Pickup', 'woocommerce-shipping-aramex' ); ?></a>
				<img src="<?php echo plugins_url( '/../images/ajax-loader.gif', __FILE__ ); ?>" class="ajax-loader" style="display: none;"/>
				<div class="pickup_errors"></div>
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * Enqueue scripts in the admin page.
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		global $post_type;

		if ( 'shop_order' === $post_type ) {
			wp_enqueue_script( 'aramex-script', plugins_url( '/../js/aramex.js', __FILE__ ), array( 'jquery' ) );
		}
	}
}
