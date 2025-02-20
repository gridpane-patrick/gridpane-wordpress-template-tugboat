<?php
/**
 * Copyright (c) Bytedance, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package TikTok
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once __DIR__ . '/../utils/utilities.php';

class Tt4b_Pixel_Class {
	// TTCLID Cookie name
	const TTCLID_COOKIE    = 'tiktok_ttclid';
	private static $events = [];


	/**
	 * Fires the view content event
	 *
	 * @return void
	 */
	public static function inject_view_content_event() {
		$event  = 'ViewContent';
		$logger = new Logger( wc_get_logger() );
		$logger->log( __METHOD__, "hit $event" );
		$mapi = new Tt4b_Mapi_Class( new Logger( wc_get_logger() ) );
		global $post;
		if ( ! isset( $post->ID ) ) {
			return;
		}
		$fields = self::pixel_event_tracking_field_track( __METHOD__ );
		if ( 0 === count( $fields ) ) {
			return;
		}

		$pixel_obj              = new Tt4b_Pixel_Class();
		$should_send_event_data = $pixel_obj->confirm_to_send_s2s_events( $fields['access_token'], $fields['advertiser_id'], $fields['pixel_code'] );
		if ( ! $should_send_event_data ) {
			$logger->log( __METHOD__, 'will not send event data for this pixel' );
			return;
		}

		$timestamp  = gmdate( 'c', time() );
		$product    = wc_get_product( $post->ID );
		$content_id = (string) $product->get_sku();
		if ( '' === $content_id ) {
			$content_id = (string) $product->get_id();
		}
		$content_type = 'product';
		if ( $product->is_type( 'variable' ) ) {
			$content_type = 'product_group';
		}
		$event_id   = self::get_event_id( $content_id );
		$event_data = [
			'price'        => (int) $product->get_price(),
			'currency'     => get_woocommerce_currency(),
			'content_name' => $product->get_name(),
			'content_id'   => $content_id,
			'content_type' => $content_type,
			'value'        => (int) $product->get_price(),
		];
		$properties = [
			'contents' => [
				$event_data,
			],
		];

		$context      = self::get_context();
		$hashed_email = $context['user']['email'];
		$hashed_phone = $context['user']['phone_number'];
		$params       = [
			'partner_name' => 'WooCommerce',
			'pixel_code'   => $fields['pixel_code'],
			'event'        => $event,
			'timestamp'    => $timestamp,
			'properties'   => $properties,
			'context'      => $context,
		];

		// js pixel track
		self::add_event( $event, $fields['pixel_code'], $event_data, $hashed_email, $hashed_phone, $event_id );

		// events API track
		$mapi->mapi_post( 'pixel/track/', $fields['access_token'], $params );
	}

	/**
	 * Fires the add to cart event
	 *
	 * @param string $cart_item_key The cart item id
	 * @param string $product_id The product id
	 * @param string $quantity The quantity of products
	 * @param string $variation_id The variant id
	 *
	 * @return void
	 */
	public static function inject_add_to_cart_event( $cart_item_key, $product_id, $quantity, $variation_id ) {
		$event  = 'AddToCart';
		$logger = new Logger( wc_get_logger() );
		$logger->log( __METHOD__, "hit $event" );
		$mapi    = new Tt4b_Mapi_Class( $logger );
		$product = wc_get_product( $product_id );

		$fields = self::pixel_event_tracking_field_track( __METHOD__ );
		if ( 0 === count( $fields ) ) {
			return;
		}

		$pixel_obj              = new Tt4b_Pixel_Class();
		$should_send_event_data = $pixel_obj->confirm_to_send_s2s_events( $fields['access_token'], $fields['advertiser_id'], $fields['pixel_code'] );
		if ( ! $should_send_event_data ) {
			$logger->log( __METHOD__, 'will not send event data for this pixel' );

			return;
		}
		$timestamp = gmdate( 'c', time() );

		$content_id = (string) $product->get_sku();
		if ( '' === $content_id ) {
			$content_id = (string) $product->get_id();
		}
		$content_type = 'product';
		$price        = $product->get_price();
		// variation_id will be > 0 if product variation is added, variation_id is post ID
		if ( $variation_id > 0 ) {
			$variation = wc_get_product( $variation_id );
			// if variation sku is same as parent product id, update content_id to match synced SKU_ID synced during catalog sync
			// otherwise use variation sku
			$content_id = variation_content_id_helper( Method::ADDTOCART, $content_id, $variation->get_sku(), $variation_id );
			// use variation price
			$price = $variation->get_price();
		}

		$event_id   = self::get_event_id( $content_id );
		$event_data = [
			'price'        => (int) $price,
			'currency'     => get_woocommerce_currency(),
			'content_name' => $product->get_name(),
			'quantity'     => (int) $quantity,
			'content_type' => $content_type,
			'content_id'   => $content_id,
			'value'        => (int) $product->get_price() * (int) $quantity,
		];
		$properties = [
			'contents' => [
				$event_data,
			],
		];

		$context      = self::get_context();
		$hashed_email = $context['user']['email'];
		$hashed_phone = $context['user']['phone_number'];
		$params       = [
			'partner_name' => 'WooCommerce',
			'pixel_code'   => $fields['pixel_code'],
			'event'        => $event,
			'timestamp'    => $timestamp,
			'properties'   => $properties,
			'context'      => $context,
		];

		// js pixel track
		self::add_event( $event, $fields['pixel_code'], $event_data, $hashed_email, $hashed_phone, $event_id );

		// events API track
		$mapi->mapi_post( 'pixel/track/', $fields['access_token'], $params );
	}

	/**
	 * Fires the start checkout event
	 *
	 * @return void
	 */
	public static function inject_start_checkout() {
		$event  = 'InitiateCheckout';
		$logger = new Logger( wc_get_logger() );
		$logger->log( __METHOD__, "hit $event" );
		$mapi = new Tt4b_Mapi_Class( $logger );
		// if registration required, and can't register in checkout and user not logged in, don't fire event
		if ( ! WC()->checkout()->is_registration_enabled()
			 && WC()->checkout()->is_registration_required()
			 && ! is_user_logged_in()
		) {
			return;
		}
		$fields = self::pixel_event_tracking_field_track( __METHOD__ );
		if ( 0 === count( $fields ) ) {
			return;
		}

		$pixel_obj              = new Tt4b_Pixel_Class();
		$should_send_event_data = $pixel_obj->confirm_to_send_s2s_events( $fields['access_token'], $fields['advertiser_id'], $fields['pixel_code'] );
		if ( ! $should_send_event_data ) {
			$logger->log( __METHOD__, 'will not send event data for this pixel' );

			return;
		}

		$timestamp = gmdate( 'c', time() );

		$event_data = [];
		$value      = 0;
		$event_id   = self::get_event_id( '' );
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product      = $cart_item['data'];
			$quantity     = (int) $cart_item['quantity'];
			$variation_id = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
			$price        = self::get_product_subtotal_as_int( $product );
			$content_type = 'product';
			$content_id   = (string) $product->get_sku();
			if ( '' === $content_id ) {
				$content_id = (string) $product->get_id();
			}
			if ( $variation_id > 0 ) {
				$variation  = wc_get_product( $variation_id );
				$content_id = variation_content_id_helper( Method::STARTCHECKOUT, $content_id, $variation->get_sku(), $variation_id );
				// calculate subtotal based on variation pricing
				WC()->cart->get_subtotal();
				$price = self::get_product_subtotal_as_int( $variation );
			}
			$content = [
				'price'        => $price,
				'content_name' => $product->get_name(),
				'content_id'   => $content_id,
				'content_type' => $content_type,
				'quantity'     => $quantity,
			];
			$value  += $price * $quantity;
			array_push( $event_data, $content );
		}

		$properties = [
			'contents' => $event_data,
			'currency' => get_woocommerce_currency(),
			'value'    => $value,
		];

		$context      = self::get_context();
		$hashed_email = $context['user']['email'];
		$hashed_phone = $context['user']['phone_number'];
		$params       = [
			'partner_name' => 'WooCommerce',
			'pixel_code'   => $fields['pixel_code'],
			'event'        => $event,
			'timestamp'    => $timestamp,
			'properties'   => $properties,
			'context'      => $context,
		];

		// js pixel track
		self::add_event( $event, $fields['pixel_code'], $properties, $hashed_email, $hashed_phone, $event_id );

		// events API track
		$mapi->mapi_post( 'pixel/track/', $fields['access_token'], $params );
	}

	/**
	 * Fires the purchase event
	 *
	 * @param string $order_id the order id
	 *
	 * @return void
	 */
	public static function inject_purchase_event( $order_id ) {
		$event  = 'Purchase';
		$logger = new Logger( wc_get_logger() );
		$logger->log( __METHOD__, "hit $event" );
		$mapi   = new Tt4b_Mapi_Class( $logger );
		$fields = self::pixel_event_tracking_field_track( __METHOD__ );
		if ( 0 === count( $fields ) ) {
			return;
		}

		$pixel_obj              = new Tt4b_Pixel_Class();
		$should_send_event_data = $pixel_obj->confirm_to_send_s2s_events( $fields['access_token'], $fields['advertiser_id'], $fields['pixel_code'] );
		if ( ! $should_send_event_data ) {
			$logger->log( __METHOD__, 'will not send event data for this pixel' );

			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}
		$value      = 0;
		$event_data = [];
		$event_id   = self::get_event_id( '' );
		foreach ( $order->get_items() as $item ) {
			$product      = $item->get_product();
			$price        = $product->get_price();
			$quantity     = $item->get_quantity();
			$content_type = 'product';
			$content_id   = (string) $product->get_sku();
			if ( '' === $content_id ) {
				$content_id = (string) $product->get_id();
			}
			// check if order item is variation with parent
			$parent_product_id = $product->get_parent_id();
			if ( $parent_product_id > 0 ) {
				$parent_product = wc_get_product( $parent_product_id );
				// check if parent_id matches variation id, update content_id according to method used in catalog sync
				$parent_id = $parent_product->get_sku();
				if ( '' === $parent_id ) {
					$parent_id = $parent_product->get_id();
				}
				$content_id = variation_content_id_helper( Method::PURCHASE, $parent_id, $content_id, $product->get_id() );
			}
			$content = [
				'price'        => (int) $price,
				'content_name' => $product->get_name(),
				'content_id'   => $content_id,
				'content_type' => $content_type,
				'quantity'     => (int) $quantity,
			];
			$value  += (int) $item->get_quantity() * (int) $product->get_price();
			array_push( $event_data, $content );
		}
		$timestamp  = gmdate( 'c', time() );
		$properties = [
			'contents' => $event_data,
			'value'    => $value,
			'currency' => get_woocommerce_currency(),
		];

		$context      = self::get_context();
		$hashed_email = $context['user']['email'];
		$hashed_phone = $context['user']['phone_number'];
		$params       = [
			'partner_name' => 'WooCommerce',
			'pixel_code'   => $fields['pixel_code'],
			'event'        => $event,
			'timestamp'    => $timestamp,
			'properties'   => $properties,
			'context'      => $context,
		];

		// js pixel track
		self::add_event( $event, $fields['pixel_code'], $properties, $hashed_email, $hashed_phone, $event_id );

		// events API track
		$mapi->mapi_post( 'pixel/track/', $fields['access_token'], $params );
	}

	/**
	 *  Gets the context param needed for view content, add to cart, start checkout, complete payment.
	 */
	public static function get_context() {
		$pixel_obj    = new Tt4b_Pixel_Class();
		$current_user = wp_get_current_user();
		$email        = $current_user->user_email;
		$customer     = new WC_Customer();
		$phone_number = $customer->get_billing_phone();
		$hashed_email = $pixel_obj->get_advanced_matching_hashed_info( $email );

		// set empty hashed email / phone to empty string instead of hash of empty string
		if ( '' === $email ) {
			$hashed_email = '';
		}
		$hashed_phone = $pixel_obj->get_advanced_matching_hashed_info( $phone_number );
		if ( '' === $phone_number ) {
			$hashed_phone = '';
		}

		$ipaddress  = WC_Geolocation::get_ip_address();
		$user_agent = '';
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
		}
		$url = '';
		if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			$url = esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) . wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		$context = [
			'page'       => [
				'url' => $url,
			],
			'ip'         => $ipaddress,
			'user_agent' => $user_agent,
			'user'       => [
				'email'        => $hashed_email,
				'phone_number' => $hashed_phone,
			],
		];

		return self::get_ttclid( $context ); // add ttclid if available

	}

	public static function get_event_id( $content_id ) {
		$external_business_id = get_option( 'tt4b_external_business_id' );
		$unique_id            = uniqid();
		if ( '' !== $content_id ) {
			return sprintf( '%s_%s_%s', $unique_id, $external_business_id, $content_id );
		}

		return sprintf( '%s_%s', $unique_id, $external_business_id );
	}

	/**
	 *  Gets all pixels associated to an ad account.
	 *
	 * @param string $access_token The MAPI issued access token.
	 * @param string $advertiser_id The users advertiser id.
	 * @param string $pixel_code The users pixel code.
	 */
	public function get_pixels( $access_token, $advertiser_id, $pixel_code ) {
		// returns a raw API response from TikTok pixel/list/ endpoint
		$params = [
			'advertiser_id' => $advertiser_id,
			'code'          => $pixel_code,
		];
		$url    = 'https://business-api.tiktok.com/open_api/v1.3/pixel/list/?' . http_build_query( $params );
		$args   = [
			'method'  => 'GET',
			'headers' => [
				'Access-Token' => $access_token,
				'Content-Type' => 'application/json',
			],
		];
		$logger = new Logger( wc_get_logger() );
		$logger->log_request( $url, $args );
		$result = wp_remote_get( $url, $args );
		$logger->log_response( __METHOD__, $result );

		return wp_remote_retrieve_body( $result );
	}

	/**
	 * Gets whether advanced matching is enabled for the user.
	 *
	 * @param string $info The users email or phone
	 *
	 * @return false|string
	 */
	public function get_advanced_matching_hashed_info( $info ) {
		// returns the SHA256 encrypted email if advanced_matching is enabled. If advanced_matching is not
		// enabled, then return an empty string
		$advanced_matching = get_option( 'tt4b_advanced_matching' );
		$hashed_info       = '';
		if ( $advanced_matching ) {
			$hashed_info = hash( 'SHA256', strtolower( $info ) );
		}

		return $hashed_info;
	}

	/**
	 *  Preprocess to ensure we have the required fields to call the event track API
	 *
	 * @param string $method The hook that is executed.
	 *
	 * @return array
	 */
	public static function pixel_event_tracking_field_track( $method ) {
		$logger = new Logger( wc_get_logger() );
		try {
			$access_token  = self::get_and_validate_option( 'access_token' );
			$pixel_code    = self::get_and_validate_option( 'pixel_code' );
			$advertiser_id = self::get_and_validate_option( 'advertiser_id' );
		} catch ( Exception $e ) {
			$logger->log( $method, $e->getMessage() );

			return [];
		}

		return [
			'access_token'  => $access_token,
			'advertiser_id' => $advertiser_id,
			'pixel_code'    => $pixel_code,
		];
	}

	/**
	 *  Validates to ensure tt4b options are stored, and return the option if it is.
	 *
	 * @param string $option_name The tt4b data option
	 * @param bool   $default The default option boolean
	 *
	 * @return string
	 * @throws Exception          Throws exception when the given option is missing.
	 */
	protected static function get_and_validate_option( $option_name, $default = false ) {
		$option = get_option( "tt4b_{$option_name}", $default );
		if ( false === $option ) {
			throw new Exception( sprintf( 'Missing option "%s"', $option_name ) );
		}

		return $option;
	}

	/**
	 *  Checks to see whether to track events s2s
	 *
	 * @param string $access_token The access token
	 * @param string $advertiser_id The advertiser_id
	 * @param string $pixel_code The pixel_code
	 *
	 * @return bool
	 */
	public function confirm_to_send_s2s_events( $access_token, $advertiser_id, $pixel_code ) {
		$should_send_events = get_option( 'tt4b_should_send_s2s_events' );
		if ( false === $should_send_events ) {
			$pixel_obj = new Tt4b_Pixel_Class();
			$pixel_rsp = $pixel_obj->get_pixels(
				$access_token,
				$advertiser_id,
				$pixel_code
			);
			$pixel     = json_decode( $pixel_rsp, true );
			// case 1: always send events for woo_commerce pixels
			update_option( 'tt4b_should_send_s2s_events', 'YES' );
			if ( '' !== $pixel ) {
				$connected_pixel = $pixel['data']['pixels'][0];
				$partner         = $connected_pixel['partner_name'];
				if ( 'WOO_COMMERCE' !== $partner ) {
					update_option( 'tt4b_should_send_s2s_events', 'NO' );
					// case 2: if the pixel is not a partner pixel, send events if no recent activity
					if ( 'ACTIVE' !== $connected_pixel['activity_status'] ) {
						update_option( 'tt4b_should_send_s2s_events', 'YES' );
					}
				}
			}
		}

		$should_send_event_data = get_option( 'tt4b_should_send_s2s_events' );
		if ( 'NO' === $should_send_event_data ) {
			return false;
		}

		return true;
	}

	/**
	 *  Grab ttclid from URL and set cookie for 30 days
	 */
	public static function set_ttclid() {
		if ( isset( $_GET['ttclid'] ) ) {
			setcookie( self::TTCLID_COOKIE, sanitize_text_field( $_GET['ttclid'] ), time() + 30 * 86400, '/' );
		}
	}

	/**
	 *  Add ajax event tracking
	 */
	public static function add_ajax_snippet() {
		$pixel_code = get_option( 'tt4b_pixel_code' );
		if ( ! $pixel_code ) {
			return;
		}

		wp_register_script( 'tt4b_ajax_script', plugins_url( '/admin/js/ajaxSnippet.js', dirname( __DIR__ ) . '/tiktok-for-woocommerce.php' ), [ 'jquery' ], 'v1', false );
		wp_enqueue_script( 'tt4b_ajax_script' );
		wp_localize_script(
			'tt4b_ajax_script',
			'tt4b_script_vars',
			[
				'pixel_code' => $pixel_code,
				'currency'   => get_woocommerce_currency(),
			]
		);
	}

	/**
	 *  Add ttclid if it is available
	 *
	 * @param string $context The pixel context
	 *
	 * @return context|object
	 */
	protected static function get_ttclid( $context ) {
		if ( isset( $_COOKIE[ self::TTCLID_COOKIE ] ) ) {
			// TTCLID cookie is set, append it to the $context
			$context['ad'] = [
				'callback' => sanitize_text_field( $_COOKIE[ self::TTCLID_COOKIE ] ),
			];
		}

		return $context;
	}

	/**
	 * Get cart subtotal for a product with tax if appropriate
	 *
	 * @param WC_Product $product  the product to calculate row subtotal
	 * @param int        $quantity quantity of product being purchase
	 *
	 * @return int the appropriate price with tax for the product row subtotal
	 */
	protected static function get_product_subtotal_as_int( $product ) {
		$row_price = $product->get_price();

		if ( $product->is_taxable() ) {
			if ( WC()->cart->display_prices_including_tax() ) {
				$row_price = wc_get_price_including_tax( $product, [ 'qty' => 1 ] );
			} else {
				$row_price = wc_get_price_excluding_tax( $product, [ 'qty' => 1 ] );
			}
		}

		return (int) $row_price;
	}

	/**
	 * Prints the given event.
	 *
	 * @param string $event The event's type.
	 * @param string $pixel_code The pixel code.
	 * @param array  $data The data to be passed to the JS function.
	 * @param string $hashed_email The hashed email.
	 * @param string $hashed_phone The hashed phone.
	 * @param string $event_id The unique id for the event.
	 *
	 * @return void
	 */
	public static function add_event( $event, $pixel_code, $data, $hashed_email, $hashed_phone, $event_id ) {
		if ( did_action( 'wp_head' ) ) {
			self::print_event( $event, $pixel_code, $data, $hashed_email, $hashed_phone, $event_id );
		} else {
			self::enqueue_event( $event, $pixel_code, $data, $hashed_email, $hashed_phone, $event_id );
		}
	}


	/**
	 * Gets the event's JS code to be enqueued or printed.
	 *
	 * @param string $event The event's type.
	 * @param string $pixel_code The pixel code
	 * @param array  $data The data to be passed to the JS function.
	 * @param string $event_id The unique id corresponding to the event.
	 *
	 * @return string
	 */
	private static function prepare_event_code( $event, $pixel_code, $data, $event_id ) {
		$data_string = empty( $data ) ? null : wp_json_encode( $data );

		return sprintf(
			'ttq.instance(\'%s\').track(\'%s\', %s, {\'event_id\': \'%s\'})',
			$pixel_code,
			$event,
			$data_string,
			$event_id
		);
	}

	/**
	 * Gets the AM to be enqueued or printed.
	 *
	 * @param string $pixel_code The pixel code.
	 * @param string $hashed_email The hashed email
	 *
	 * @return string
	 */
	private static function prepare_advanced_matching( $pixel_code, $hashed_email, $hashed_phone ) {
		return sprintf(
			'ttq.instance(\'%s\').identify({
            email: \'%s\', phone_number: \'%s\'})',
			$pixel_code,
			$hashed_email,
			$hashed_phone
		);
	}

	/**
	 * Prints the given event.
	 *
	 * @param string $event The event's type.
	 * @param string $pixel_code The pixel code.
	 * @param array  $data The data to be passed to the JS function.
	 * @param string $hashed_email The hashed email.
	 * @param string $hashed_phone The hashed phone.
	 *
	 * @return void
	 */
	private static function print_event( $event, $pixel_code, $data, $hashed_email, $hashed_phone, $event_id ) {
		wp_register_script( 'tiktok-tracking-handle-header', '', '', 'v1' );
		wp_enqueue_script( 'tiktok-tracking-handle-header' );
		$event_code_script = '<script>' . self::prepare_event_code( $event, $pixel_code, $data, $event_id ) . '</script>';
		wp_add_inline_script( 'tiktok-tracking-handle-header', $event_code_script );
		$advanced_matching_script = '<script>' . self::prepare_advanced_matching( $pixel_code, $hashed_email, $hashed_phone ) . '</script>';
		wp_add_inline_script( 'tiktok-tracking-handle-header', $advanced_matching_script );

	}

	/**
	 * Enqueues the given event.
	 *
	 * @param string $event The event's type.
	 * @param string $pixel_code The pixel code.
	 * @param array  $data The data to be passed to the JS function.
	 * @param string $hashed_email The hashed email.
	 * @param string $hashed_phone The hashed phone.
	 *
	 * @return void
	 */
	private static function enqueue_event( $event, $pixel_code, $data, $hashed_email, $hashed_phone, $event_id ) {
		self::$events[ self::prepare_event_code( $event, $pixel_code, $data, $event_id ) ] = self::prepare_advanced_matching( $pixel_code, $hashed_email, $hashed_phone );
	}


	/**
	 * Prints the enqueued base code and events snippets.
	 * Meant to be used in wp_head.
	 *
	 * @return void
	 */
	public static function print_script() {
		$pixel_code = get_option( 'tt4b_pixel_code' );
		if ( ! $pixel_code ) {
			return;
		}

		$script = '!function (w, d, t) {
		 w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{},ttq._partner=ttq._partner||"WooCommerce";var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
		 ttq.load(';
		$script = $script . "'$pixel_code'";
		$script = $script . ');
		 ttq.page();
		 }(window, document, \'ttq\');';
		wp_register_script( 'tiktok-pixel-tracking-handle-header', '', '', 'v1' );
		wp_enqueue_script( 'tiktok-pixel-tracking-handle-header' );
		wp_add_inline_script( 'tiktok-pixel-tracking-handle-header', $script );

		if ( ! empty( self::$events ) ) {
			foreach ( self::$events as $key => $value ) {
				// register a dummy script to add small inline snippet
				wp_register_script( 'tiktok-tracking-handle-header', '', '', 'v1' );
				wp_enqueue_script( 'tiktok-tracking-handle-header' );
				wp_add_inline_script( 'tiktok-tracking-handle-header', $key );
				wp_add_inline_script( 'tiktok-tracking-handle-header', $value );
			}
			self::$events = [];
		}
	}

	public function get_key( $key ) {
		return $key;
	}

	/**
	 * Filter the "Add to cart" button attributes to include more data.
	 *
	 * @see woocommerce_template_loop_add_to_cart()
	 *
	 * @since 1.0.11
	 *
	 * @param array      $args The arguments used for the Add to cart button.
	 * @param WC_Product $product The product object.
	 *
	 * @return array The filtered arguments for the Add to cart button.
	 */
	public static function filter_add_to_cart_attributes( array $args, WC_Product $product ) {
		$attributes = [
			'data-product_name' => $product->get_name(),
			'data-price'        => $product->get_price(),
		];

		$args['attributes'] = array_merge( $args['attributes'], $attributes );

		return $args;
	}
}
