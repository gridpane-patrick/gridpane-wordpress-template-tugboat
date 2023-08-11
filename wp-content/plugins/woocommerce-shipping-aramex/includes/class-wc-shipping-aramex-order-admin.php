<?php
/**
 * Class that handles order admin related things.
 *
 * @package WC_Shipping_Aramex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order admin stuff for Aramex.
 *
 * @since 1.0.2
 */
class WC_Shipping_Aramex_Order_Admin {

	/**
	 * Cached setting of shipping method instance from `get_option` mixed with
	 * info for pickup request params.
	 *
	 * @var array
	 */
	protected $setting;

	/**
	 * Set callback to WP hooks and cache settings.
	 */
	public function __construct() {
		// Specify a method to run when the "aramex_pickup" AJAX event is fired.
		add_action( 'wp_ajax_aramex_pickup', array( $this, 'ajax_pickup_callback' ) );

		// Register a meta box to display order information from Aramex.
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'pickup_request_form' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Handles pickup AJAX.
	 */
	public function ajax_pickup_callback() {
		try {
			$order_id    = $_POST['order_id'];
			$order       = wc_get_order( $order_id );

			$params = $this->get_pickup_request_params( $order );

			$soap_client = new SoapClient(
				WC_Aramex()->plugin_dir . '/wsdl/shipping-services-api-wsdl.wsdl',
				array(
					'trace' => 1,
				)
			);

			$setting = $this->get_setting( $order );
			if ( 'yes' === $setting['test'] ) {
				$soap_client->__setLocation( 'http://ws.staging.aramex.net/shippingapi/shipping/service_1_0.svc' );
			} else {
				$soap_client->__setLocation( 'http://ws.aramex.net/shippingapi/shipping/service_1_0.svc' );
			}

			$results = $soap_client->CreatePickup( $params );
			

			// Check for error.
			if ( empty( $results->HasErrors ) ) {
				update_post_meta( $order_id, '_pickup_date', $params['Pickup']['PickupDate'] );
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
	 * Render pickup request form in admin order screen.
	 *
	 * @param WC_Order $order Current order instance.
	 */
	public function pickup_request_form( $order ) {
		require_once( WC_Aramex()->plugin_dir . '/includes/views/html-pickup-request-form.php' );
	}

	/**
	 * Enqueue scripts in the admin page.
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		global $post_type;

		if ( 'shop_order' === $post_type ) {
			wp_enqueue_script( 'aramex-script', WC_Aramex()->plugin_url . '/js/aramex.js', array( 'jquery' ) );
		}
	}

	/**
	 * Get pickup request params.
	 *
	 * @param WC_Order $order Order object.
	 *
	 * @return array Request params.
	 */
	protected function get_pickup_request_params( WC_Order $order ) {
		$pickup_date = new DateTime( urldecode( $_POST['pickup_date'] ) );

		$setting = $this->get_setting( $order );

		$destination = version_compare( WC_VERSION, '3.0', '<' )
			? $order->shipping_country
			: $order->get_shipping_country();

		$product_group = WC()->countries->get_base_country() !== $destination ? 'EXP' : 'DOM';
		$product_type  = WC()->countries->get_base_country() !== $destination ? $setting['aramex_product_type'] : 'OND';

		// Get close & start time.
		$close_time       = $setting['pickup_close_time'];
		$close_time_array = explode( ':', $close_time );
		$close_time_h     = isset( $close_time_array['0'] ) ? $close_time_array['0'] : 0;
		$close_time_m     = isset( $close_time_array['1'] ) ? $close_time_array['1'] : 0;
		$close_time       = new DateTime( urldecode( $_POST['pickup_date'] ) );
		$close_time->setTime( $close_time_h, $close_time_m, 0 );

		$open_time       = $setting['pickup_open_time'];
		$open_time_array = explode( ':', $open_time );
		$open_time_h     = isset( $open_time_array['0'] ) ? $open_time_array['0'] : 0;
		$open_time_m     = isset( $open_time_array['1'] ) ? $open_time_array['1'] : 0;
		$open_time       = new DateTime( urldecode( $_POST['pickup_date'] ) );
		$open_time->setTime( $open_time_h, $open_time_m, 0 );

		return array(
			'ClientInfo' => $this->get_client_info( $setting ),
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
				'PickupAddress'  => $this->get_pickup_address_params( $setting ),
				'PickupContact'  => $this->get_pickup_contact_params( $setting ),
				'PickupLocation' => 'Reception',
				'PickupDate'     => date_format( $pickup_date, DATE_W3C ),
				'ReadyTime'      => date_format( $open_time, DATE_W3C ),
				'ClosingTime'    => date_format( $close_time, DATE_W3C ),
				'LastPickupTime' => date_format( $pickup_date, DATE_W3C ),
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
	}

	/**
	 * Get client info params.
	 *
	 * @todo This is dupe of what we have in shipping class. Please make it
	 *       DRY by pulling SOAL request related and its params to its own class.
	 *
	 * @param array $setting Instance setting.
	 *
	 * @return array Client info.
	 */
	protected function get_client_info( array $setting ) {
		if ( 'yes' === $setting['test'] ) {
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

		return array(
			'AccountCountryCode' => WC()->countries->get_base_country(),
			'AccountEntity'      => $setting['account_entity'],
			'AccountNumber'      => $setting['account_number'],
			'AccountPin'         => $setting['account_pin'],
			'UserName'           => $setting['account_user_name'],
			'Password'           => $setting['account_password'],
			'Version'            => 'V1.0',
		);
	}

	/**
	 * Get pickup address parameters.
	 *
	 * @param array $setting Instance setting.
	 *
	 * @return array Pick address.
	 */
	public function get_pickup_address_params( array $setting ) {
		return array(
			'Line1'               => $setting['account_address1'],
			'Line2'               => $setting['account_address2'],
			'Line3'               => $setting['account_address3'],
			'City'                => $setting['account_city'],
			'StateOrProvinceCode' => $setting['account_province_code'],
			'PostCode'            => $setting['account_post_code'],
			'CountryCode'         => WC()->countries->get_base_country(),
		);
	}

	/**
	 * Get pickup contact parameters.
	 *
	 * @param array $setting Instance setting.
	 *
	 * @return array Pickup contact.
	 */
	public function get_pickup_contact_params( array $setting ) {
		return array(
			'Department'      => '',
			'PersonName'      => $setting['pickup_contact_person_name'],
			'Title'           => $setting['pickup_contact_person_title'],
			'CompanyName'     => $setting['pickup_contact_person_company'],
			'PhoneNumber1'    => $setting['pickup_contact_person_phone'],
			'PhoneNumber1Ext' => '',
			'PhoneNumber2'    => '',
			'PhoneNumber2Ext' => '',
			'FaxNumber'       => '',
			'CellPhone'       => $setting['pickup_contact_person_cell_phone'],
			'EmailAddress'    => $setting['pickup_contact_person_email'],
			'Type'            => '',
		);
	}

	/**
	 * Get instance of shipping method setting from a given order mixed with
	 * other info for pickup request params.
	 *
	 * @param WC_Order $order Order object.
	 *
	 * @return array Instance setting.
	 */
	protected function get_setting( WC_Order $order ) {
				
		if ( ! empty( $this->setting ) ) {
			return $this->setting;
		}

		$base_option_name = 'woocommerce_wc_shipping_aramex_settings';
		$option_name      = $base_option_name;

		foreach ( $order->get_shipping_methods() as $shipping_method ) {
			if ( 0 === strpos( $shipping_method->get_method_id(), 'wc_shipping_aramex' ) ) {
				$parts       = explode( ':', $shipping_method->get_method_id() );
				$option_name = ! empty( $parts[1] )
					? 'woocommerce_wc_shipping_aramex_' . $parts[1] . '_settings'
					: $option_name;

				break;
			}
		}

		$setting       = array_filter( get_option( $option_name, array() ) );
		$base_setting  = array_filter( get_option( $base_option_name, array() ) );

		$this->setting = $setting + $base_setting + array(
			'account_address1'                 => (isset($order->shipping_address_1) && (!empty($order->shipping_address_1))) ?  $order->shipping_address_1 : $order->billing_address_1,
			'account_address2'                 => (isset($order->shipping_address_2) && (!empty($order->shipping_address_2))) ?  $order->shipping_address_2 : $order->billing_address_2,
			'account_address3'                 => '',
			'account_city'                     => (isset($order->shipping_city) && (!empty($order->shipping_city))) ?  $order->shipping_city : $order->billing_city,
			'account_province_code'            => (isset($order->shipping_postcode) && (!empty($order->shipping_postcode))) ?  $order->shipping_postcode : $order->billing_postcode,
			'account_post_code'                => (isset($order->shipping_postcode) && (!empty($order->shipping_postcode))) ?  $order->shipping_postcode : $order->billing_postcode,
			'pickup_contact_person_name'       => $order->billing_first_name." ".$order->billing_last_name,
			'pickup_contact_person_title'      => '',
			'pickup_contact_person_company'    => (isset($order->shipping_company) && (!empty($order->shipping_company))) ?  $order->shipping_company : $order->billing_company,
			'pickup_contact_person_phone'      => (isset($order->shipping_phone) && (!empty($order->shipping_phone))) ?  $order->shipping_phone : $order->billing_phone,
			'pickup_contact_person_cell_phone' => (isset($order->shipping_phone) && (!empty($order->shipping_phone))) ?  $order->shipping_phone : $order->billing_phone,
			'pickup_contact_person_email'      => (isset($order->shipping_email) && (!empty($order->shipping_email))) ?  $order->shipping_email : $order->billing_email,
		);

		return $this->setting;
	}
}
