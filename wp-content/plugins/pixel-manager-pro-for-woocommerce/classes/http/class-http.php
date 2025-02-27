<?php

namespace WCPM\Classes\Http;

use WCPM\Classes\Pixels\Trait_Shop;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Http {

	use Trait_Shop;

	protected $options;
	protected $options_obj;
	protected $post_request_args;
	protected $server_base_path;
	protected $mp_purchase_hit_key;
	protected $mp_full_refund_hit_key;
	protected $mp_partial_refund_hit_key;
	protected $logger;
	protected $logger_context;

	public function __construct( $options ) {

//        $this->options     = get_option(WPM_DB_OPTIONS_NAME);
		$this->options     = $options;
		$this->options_obj = $this->get_options_object($options);

		$this->post_request_args = [
			'body'        => '',
			'timeout'     => 5,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => apply_filters('wpm_send_http_api_requests_blocking', apply_filters_deprecated('wooptpm_send_http_api_requests_blocking', [false], '1.13.0', 'wpm_send_http_api_requests_blocking')),
			'headers'     => [],
			'cookies'     => [],
			'sslverify'   => false,
		];

		$this->post_request_args = apply_filters_deprecated('wooptpm_http_post_request_args', [$this->post_request_args], '1.13.0', 'wpm_http_post_request_args');
		$this->post_request_args = apply_filters('wpm_http_post_request_args', $this->post_request_args);

		$this->logger_context = ['source' => 'wpm-http'];
	}

	protected function send_hit( $request_url, $payload = null ) {

//		error_log(print_r($request_url, true));

		if ($payload) {
			$this->post_request_args['body'] = wp_json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}

//		error_log($this->post_request_args['body']);

//		$this->post_request_args['blocking'] = true;

		// if we're sending the request non-blocking we won't receive a response back
		if (true === $this->post_request_args['blocking']) {

//			error_log(print_r($this->post_request_args, true));
//            error_log('request url: ' . $request_url);
//            error_log(print_r($payload, true));

			$response = wp_safe_remote_post($request_url, $this->post_request_args);

//			error_log('hit was sent');
//			error_log('response code: ' . wp_remote_retrieve_response_code($response));
//			error_log(print_r($response, true));

			if (is_wp_error($response)) {
				wc_get_logger()->debug('response error message: ' . $response->get_error_message(), $this->logger_context);
				wc_get_logger()->debug('request url: ' . $request_url, $this->logger_context);
				wc_get_logger()->debug('payload: ' . print_r($payload, true), $this->logger_context);
				wc_get_logger()->debug('response: ' . print_r($response, true), $this->logger_context);
			}

			wc_get_logger()->debug('response code: ' . wp_remote_retrieve_response_code($response), $this->logger_context);

		} else {
			wp_safe_remote_post($request_url, $this->post_request_args);
		}
	}
}
