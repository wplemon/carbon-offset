<?php

class Cloverly {
	private $public_key = '6a531ee6ee6b9a60';
	private $grams_ppl = 4.4;
	private $pay_threshold = '1000';
	private $data;
	private $api_uri = 'https://api.cloverly.app/2019-03-beta/purchases/carbon';
	public function __construct() {
		$this->data = new Data();
		add_action( 'wp_footer', 'maybe_pay' );
	}
	public function maybe_pay() {
		$visits = $this->data->get_visits_count();
		if ( $visits * (float) $this->grams_ppl >= $this->pay_threshold ) {
			$this->pay();
		}
	}
	public function pay() {
		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer public_key:' . $this->public_key,
				'Content-type'  => 'application/json'
			),
			'body' => wp_json_encode([
				'weight' => [
					'value' => $this->data->get_visits_count() * (float) $this->grams_ppl,
					'units' => 'grams'
				]
			]),
			'timeout' => 20,
		);

		// Make an API request.
		$response = wp_safe_remote_post( esc_url_raw( $this->api_uri ), $args );

		// Check the response code.
		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );

		if ( empty( $response_code ) && is_wp_error( $response ) ) {
			return $response;
		}
	}
}
