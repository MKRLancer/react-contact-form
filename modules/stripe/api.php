<?php

/**
 * Class for the Stripe API.
 *
 * @link https://stripe.com/docs/api
 */
class WPCF7_Stripe_API {

	const api_version = '2020-08-27';
	const partner_id = 'pp_partner_HHbvqLh1AaO7Am';

	private $secret;


	public function __construct( $secret ) {
		$this->secret = $secret;
	}


	private function log( $url, $request, $response ) {
		wpcf7_log_remote_request( $url, $request, $response );
	}


	/**
	 * Returns default set of HTTP request headers used for Stripe API.
	 *
	 * @link https://stripe.com/docs/building-plugins#setappinfo
	 *
	 * @return array An associative array of headers.
	 */
	private function default_headers() {
		$app_info = array(
			'name' => 'WordPress Contact Form 7',
			'partner_id' => self::partner_id,
			'url' => 'https://contactform7.com/',
			'version' => WPCF7_VERSION,
		);

		$ua = array(
			'lang' => 'php',
			'lang_version' => PHP_VERSION,
			'application' => $app_info,
		);

		$headers = array(
			'Authorization' => sprintf( 'Bearer %s', $this->secret ),
			'Stripe-Version' => self::api_version,
			'X-Stripe-Client-User-Agent' => json_encode( $ua ),
		);

		return $headers;
	}


	/**
	 * Creates a Payment Intent.
	 *
	 * @link https://stripe.com/docs/api/payment_intents/create
	 *
	 * @param string|array $args Optional. Arguments to control behavior.
	 * @return array|bool An associative array if 200 OK, false otherwise.
	 */
	public function create_payment_intent( $args = '' ) {
		$args = wp_parse_args( $args, array(
			'amount' => 0,
			'currency' => '',
			'receipt_email' => '',
		) );

		$endpoint = 'https://api.stripe.com/v1/payment_intents';

		$request = array(
			'headers' => $this->default_headers(),
			'body' => $args,
		);

		$response = wp_remote_post( esc_url_raw( $endpoint ), $request );

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			if ( WP_DEBUG ) {
				$this->log( $endpoint, $request, $response );
			}

			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$response_body = json_decode( $response_body, true );

		return $response_body;
	}


	/**
	 * Retrieve a Payment Intent.
	 *
	 * @link https://stripe.com/docs/api/payment_intents/retrieve
	 *
	 * @param string $id Payment Intent identifier.
	 * @return array|bool An associative array if 200 OK, false otherwise.
	 */
	public function retrieve_payment_intent( $id ) {
		$endpoint = sprintf(
			'https://api.stripe.com/v1/payment_intents/%s',
			urlencode( $id )
		);

		$request = array(
			'headers' => $this->default_headers(),
		);

		$response = wp_remote_get( esc_url_raw( $endpoint ), $request );

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			if ( WP_DEBUG ) {
				$this->log( $endpoint, $request, $response );
			}

			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$response_body = json_decode( $response_body, true );

		return $response_body;
	}

}
