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
		add_action( 'wp_ajax_carbon_offset_cloverly', [ $this, 'ajax_action' ] );
		add_filter( 'carbon_offset_admin_tabs', [ $this, 'admin_page_tab' ] );
		add_action( 'carbon_offset_admin_tab_contents', [ $this, 'details_tab_contents' ], 5 );
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

		if ( is_wp_error( $response ) ) {
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
	 * @since 1.0.0
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
				wp_die( wp_json_encode( $this->the_transaction( $weight ) ) );
				break;
		}
		wp_die();
	}

	/**
	 * Print the script.
	 *
	 * @access public
	 * @since 1.0.0
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
					var responseData;
					if ( 'purchase' === data.do ) {
						if ( true === response ) {
							// purchase successful.
						} else {
							// purchase failed.
						}
					}

					if ( 'estimate' === data.do ) {
						jQuery( '#action-result' ).html( response );
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Adds the admin-page tab.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $tabs Existing tabs.
	 * @return array      Returls existing tabs + cloverly.
	 */
	public function admin_page_tab( $tabs ) {
		$tabs[] = [
			'title' => __( 'Cloverly Settings', 'carbon-offset' ),
			'id'    => 'cloverly',
		];
		return $tabs;
	}

	/**
	 * Adds the admin-page tab contents.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string $tab The current tab.
	 * @return void
	 */
	public function details_tab_contents( $tab ) {
		if ( 'details' !== $tab ) {
			return;
		}

		add_action( 'admin_footer', [ $this, 'the_script' ] );
		?>
		<div class="postbox">
			<div class="inside">
				<p>TODO: Allow selecting different units & values.</p>
				<label>
					<p>How many Kg should we offset?</p>
					<input id="carbon-footeprint-offset-kg" type="number" value="<?php echo (float) $this->get_pending_weight() / 1000; ?>"/>
				</label>
				<button class="button" id="carbon-footprint-cloverly-estimate"><?php esc_html_e( 'Estimate Cost', 'carbon-offset' ); ?></button>
				<button class="button" id="carbon-footprint-cloverly-purchase"><?php esc_html_e( 'Purchase Offset', 'carbon-offset' ); ?></button>
				<div id="action-result"></div>
			</div>
		</div>
		<?php
	}
}
