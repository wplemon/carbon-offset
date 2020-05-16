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
	private $private_key = '3faf4e3877affa23';

	/**
	 * Public key for the Cloverly API.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var string
	 */
	private $public_key = '6a531ee6ee6b9a60';

	/**
	 * API response.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var mixed
	 */
	private $response;

	/**
	 * Init.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		parent::init();
		add_action( 'carbon_offset_adminpage_end', [ $this, 'admin_page' ] );
	}

	/**
	 * Handle payments.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @param float $weight The carbon weight we want to offset.
	 * @return mixed Returns the response from Cloverly's API.
	 */
	protected function the_transaction( $weight ) {

		// Make an API request.
		$response = wp_safe_remote_post(
			'https://api.cloverly.app/2019-03-beta/purchases/carbon',
			[
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
			]
		);

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

	/**
	 * Estimate the cost.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param float $weight The carbon weight in grams.
	 * @return array Details about the transaction.
	 */
	public function get_estimation( $weight ) {
		$response = wp_remote_post(
			'https://api.cloverly.com/2019-03-beta/estimates/carbon',
			[
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
			]
		);

		if ( ! empty( wp_remote_retrieve_response_code( $response ) ) && ! is_wp_error( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			return [
				'name'    => $body->offset->name,
				'url'     => $body->offset->pretty_url,
				'cost'    => $body->cost,
				'receipt' => $body->pretty_url,
			];
		}
		return [];
	}

	/**
	 * Admin info.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_page() {
		$this->the_script();
		?>
		<div class="postbox">
			<div class="inside">
				<p>TODO: Allow selecting different units & values.</p>
				<label>
					<p>How many Kg should we offset?</p>
					<input id="carbon-footeprint-offset-kg" type="number" />
				</label>
				<button class="button" onclick="carbonFootprintEstimateCloverlyCost();">
					<?php esc_html_e( 'Estimate Cost', 'carbon-offset' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Print the script.
	 * 
	 * @access protected
	 * @since 1.0.0
	 * @return void
	 */
	protected function the_script() {
		echo '<script>';
		include 'cloverly-admin-script.js';
		echo '</script>';
	}
}
