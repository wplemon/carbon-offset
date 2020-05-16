<?php
/**
 * Cloverly API wrapper.
 *
 * @package CarbonOffset
 */

namespace CarbonOffset;

/**
 * Cloverly API wrapper.
 *
 * @since 1.0.0
 */
class PaymentAPICloverly extends PaymentAPI {

	/**
	 * Private key for the Cloverly API.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var string
	 */
	private $public_key = '6a531ee6ee6b9a60';

	/**
	 * The API URL.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var string
	 */
	protected $api_uri = 'https://api.cloverly.app/2019-03-beta/purchases/carbon';

	/**
	 * API response.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var mixed
	 */
	private $response;

	/**
	 * Handle payments.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @param float $weight The carbon weight we want to offset.
	 * @return mixed Returns the response from Cloverly's API.
	 */
	protected function the_transaction( $weight ) {
		$args = [
			'headers' => [
				'Authorization' => 'Bearer public_key:' . $this->public_key,
				'Content-type'  => 'application/json',
			],
			'body'    => wp_json_encode(
				[
					'weight' => [
						'value' => $weight,
						'units' => 'grams',
					],
				]
			),
			'timeout' => 20,
		];

		// Make an API request.
		$response = wp_safe_remote_post( esc_url_raw( $this->api_uri ), $args );

		if ( empty( wp_remote_retrieve_response_code( $response ) ) && is_wp_error( $response ) ) {
			$this->response = $response;
			return false;
		}
		return true;
	}

	/**
	 * Get the error message.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return bool
	 */
	protected function get_error_message() {
		// TODO.
		return '';
	}
}
