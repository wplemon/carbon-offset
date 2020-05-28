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
	 * Public key for the Cloverly API.
	 *
	 * @access private
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $public_key;

	/**
	 * API response.
	 *
	 * @access private
	 *
	 * @since 1.0.0
	 *
	 * @var mixed
	 */
	private $response;

	/**
	 * Init.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		parent::init();
		$this->set_public_key();
		add_action( 'wp_ajax_carbon_offset_cloverly', [ $this, 'ajax_action' ] );
		add_action( 'carbon_offset_admin_page_pending_inside', [ $this, 'admin_page_pending_inside' ] );
		add_action( 'carbon_offset_settings_page_fields', [ $this, 'settings_fields' ] );
	}

	/**
	 * Set the public key in the object.
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function set_public_key() {

		// Get the options.
		$options = get_option( 'carbon_offset_settings', [] );

		// Set the public key.
		$this->public_key = ( isset( $options['cloverly-public-key'] ) ) ? $options['cloverly-public-key'] : '';
	}

	/**
	 * Handle payments.
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
	 * @param float $weight The carbon weight we want to offset.
	 *
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

		$response_code = wp_remote_retrieve_response_code( $response );
		$is_error      = is_wp_error( $response ) || 205 < $response_code;

		// If success, offset our carbon.
		if ( ! $is_error ) {
			$this->offset_weight( $weight );
		}

		return $response;
	}

	/**
	 * Retrieve the purchase response as HTML to inject on the page.
	 *
	 * @access private
	 *
	 * @since 1.0.0
	 *
	 * @param float $weight The weight we want to offset in grams.
	 *
	 * @return string
	 */
	private function the_transaction_html( $weight ) {
		$result        = $this->the_transaction( $weight );
		$response_code = wp_remote_retrieve_response_code( $result );
		$is_error      = is_wp_error( $result ) || 205 < $response_code;

		if ( $is_error ) {
			ob_start();
			?>
			<div class="notice notice-error notice-alt">
				<p><?php esc_html_e( 'There was an error with your transaction. You can see details of the request below to help you debug the issue.', 'carbon-offset' ); ?></p>
				<details>
					<code style="word-wrap:anywhere;">
						<?php echo wp_json_encode( $result ); ?>
					</code>
				</details>
			</div>
			<?php
			return ob_get_clean();
		}

		$body = json_decode( wp_remote_retrieve_body( $result ) );
		ob_start();
		?>
		<div class="notice notice-success notice-alt">
			<p>
				<?php esc_html_e( 'Congratulations! You have successfully offset your website\'s carbon footprint. Thank you for helping save our planet.', 'carbon-offset' ); ?>
			</p>
			<p>
				<a class="button" href="<?php echo esc_url( $body->pretty_url ); ?>" target="_blank" rel="nofollow">
					<?php esc_html_e( 'View Receipt', 'carbon-offset' ); ?>
				</a>
				<button class="button" onclick="location.reload();">
					<?php esc_html_e( 'Refresh Page', 'carbon-offset' ); ?>
				</button>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get the error message.
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
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
	 *
	 * @since 1.0.0
	 *
	 * @param float $weight The carbon weight in grams.
	 *
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
				'cost'    => [
					'currency'    => [
						'value' => $body->cost->currency,
						'label' => esc_html__( 'Currency', 'carbon-offset' ),
					],
					'total'       => [
						'value' => $body->cost->total,
						'label' => esc_html__( 'Total Cost', 'carbon-offset' ),
					],
					'transaction' => [
						'value' => $body->cost->transaction,
						'label' => esc_html__( 'Transaction Cost', 'carbon-offset' ),
					],
					'offset'      => [
						'value' => $body->cost->offset,
						'label' => esc_html__( 'Offset Cost', 'carbon-offset' ),
					],
				],
				'receipt' => $body->pretty_url,
			];
		}
		return [];
	}

	/**
	 * Perform the AJAX actions.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_action() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'carbon-offset-cloverly' ) ) {
			wp_die( esc_html__( 'Failed Security Check', 'carbon-offset' ) );
		}

		$do = 'estimate';
		if ( isset( $_POST['do'] ) ) {
			$do = sanitize_text_field( wp_unslash( $_POST['do'] ) );
		}
		$weight = 0;
		if ( isset( $_POST['weight'] ) ) {
			$weight = (float) sanitize_text_field( wp_unslash( $_POST['weight'] ) );
		}

		switch ( $do ) {
			case 'estimate':
				$estimation = $this->get_estimation( $weight );
				if ( empty( $estimation ) ) {
					wp_die( esc_html__( 'An error occured, could not get estimation.', 'carbon-offset' ) );
				}
				?>
				<h2>
				<?php
				printf(
					/* Translators: Link & name of the offset name. */
					esc_html__( 'Offset Type/Location: %s', 'carbon-offset' ),
					'<a href="' . esc_url( $estimation['url'] ) . '" target="_blank" rel="nofollow">' . esc_html( $estimation['name'] ) . '</a></h2>'
				);
				?>
				</h2>
				<table class="widefat">
					<?php foreach ( $estimation['cost'] as $prop ) : ?>
						<tr>
							<th><?php echo esc_html( $prop['label'] ); ?></th>
							<td><?php echo esc_html( $prop['value'] ); ?></td>
						<tr>
					<?php endforeach; ?>
				</table>
				<?php
				wp_die();
				break;

			case 'purchase':
				// No need to escape this, it has already been properl yescaped in the method itself.
				wp_die( $this->the_transaction_html( $weight ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				break;
		}
		wp_die();
	}

	/**
	 * Print the script.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function the_script() {
		?>
		<script type="text/javascript" >
		jQuery( document ).ready( function() {
			var data = {
					action: 'carbon_offset_cloverly',
				};

			jQuery( '#carbon-footprint-cloverly-estimate,#carbon-footprint-cloverly-purchase' ).on( 'click', function() {
				var data = {
						action: 'carbon_offset_cloverly',
						weight: jQuery( '#carbon-footeprint-offset-kg' ).val() * 1000,
						do: this.id.replace( 'carbon-footprint-cloverly-', '' ),
						nonce: '<?php echo esc_attr( wp_create_nonce( 'carbon-offset-cloverly' ) ); ?>'
					};

				jQuery.post( ajaxurl, data, function( response ) {
					jQuery( '#action-result' ).html( response );
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Adds the admin-page tab contents on the details tab.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_page_pending_inside() {
		?>
		<?php if ( $this->public_key ) : ?>
			<?php add_action( 'admin_footer', [ $this, 'the_script' ] ); ?>
			<div style="text-align:center;">
				<label>
					<p><?php esc_html_e( 'How many Kg should we offset? (All transactions are one-time).', 'carbon-offset' ); ?></p>
					<input id="carbon-footeprint-offset-kg" type="number" value="<?php echo (float) round( $this->get_pending_weight() ) / 1000; ?>"/>
				</label>
				<button class="button" id="carbon-footprint-cloverly-estimate"><?php esc_html_e( 'Estimate Cost', 'carbon-offset' ); ?></button>
				<button class="button" id="carbon-footprint-cloverly-purchase"><?php esc_html_e( 'Purchase Offset', 'carbon-offset' ); ?></button>
			</div>
			<div id="action-result"></div>
		<?php else : ?>
			<div class="notice notice-warning notice-alt">
				<p>
					<?php
					printf(
						/* Translators: %s: URL. */
						__( 'We could not find a saved key for the Cloverly API. Please visit the <a href="%s">Settings Page</a> and add your credentials.', 'carbon-offset' ), // phpcs:ignore WordPress.Security.EscapeOutput
						esc_url( admin_url( 'admin.php?page=carbon-offset&tab=settings' ) )
					);
					?>
				</p>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Add generic settings.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @param array $values An array of saved values.
	 *
	 * @return void
	 */
	public function settings_fields( $values ) {
		$public_key = ( isset( $values['cloverly-public-key'] ) ) ? $values['cloverly-public-key'] : '';
		?>
		<h2><?php esc_html_e( 'Cloverly API Settings', 'carbon-offset' ); ?></h2>

		<label id="cloverly-api-public-key">
			<strong>
				<?php esc_html_e( 'Cloverly API Public Key', 'carbon-offset' ); ?>
			</strong>
		</label>
		<p id="cloverly-api-public-key-desciption" class="description">
			<?php _e( 'After you create an account on the <a href="https://cloverly.com" target="_blank" rel="nofollow">Cloverly website</a>, you can visit your <a href="https://dashboard.cloverly.com/" target="_blank" rel="nofollow">Cloverly dashboard</a> to get your <code>public_key</code>.', 'carbon-offset' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</p>
		<input
			name="cloverly-public-key"
			type="text"
			aria-label="cloverly-api-public-key"
			aria-describedby="cloverly-api-public-key-description"
			value="<?php echo esc_attr( $public_key ); ?>"
		>
		<hr style="margin:2em 0;">
		<?php
	}
}
