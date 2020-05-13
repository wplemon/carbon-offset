<?php
/**
 * Admin Page Handler.
 *
 * @package CarbonOffset
 * @since 1.0.0
 */

namespace CarbonOffset;

/**
 * Admin Page Handler.
 *
 * @since 1.0.0
 */
class AdminPage {

	/**
	 * Init the admin page.
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
	}

	/**
	 * Add the admin page.
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_menu_page() {
		add_menu_page(
			esc_html__( 'Carbon Offset', 'carbon-offset' ),
			esc_html__( 'Carbon Offset', 'carbon-offset' ),
			'manage_options',
			'fusion-offset',
			[ $this, 'page' ],
			'dashicons-carrot'
		);
	}

	/**
	 * The admin-page contents.
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function page() {
		$data        = new Data();
		$carbon_data = $data->get_option();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Carbon Offset', 'carbon-offset' ); ?></h1>

			<!-- Just adds some whitespace. -->
			<div style="height: 2em"></div>

			<div class="postbox">
				<div class="inside" style="font-size:1.2em;max-width:50em;">
					TODO - Add settings:
					<ul style="list-style:disc;margin-left: 2em;">
						<li>A field where they can enter their carbon footprint per-page-load. We'll need to add instruction on where they can see that number, a link to <a href="https://www.websitecarbon.com/" target="_blank">https://www.websitecarbon.com/</a></li>
						<li>API settings for the Cloverly API. These should be hooked from within the cloverly class, we may want to add more services in the future.</li>
						<li>A checkbox they can click to enable automatic payments at a certain threshold. If they don't enable this option (recommended) they'll need to manually do payments by clicking a button below in the stats details.</li>
					</ul>
				</div>
			</div>

			<div class="postbox">
				<div class="inside">
					<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(25em, 1fr));grid-gap:1px;background:#aaa;">
						<div class="carbon-offset-pending" style="padding:2em;background:#fff;">
							<h2 style="line-height:3;"><?php esc_html_e( 'Pending', 'carbon-offset' ); ?></h2>
							<table class="widefat">
								<thead>
									<th style="font-weight:600;text-align:center;"><?php esc_html_e( 'Visits', 'carbon-offset' ); ?></th>
									<th style="font-weight:600;text-align:center;"><?php esc_html_e( 'Carbon', 'carbon-offset' ); ?></th>
								</thead>
								<tbody>
									<tr>
										<td style="font-size:4em;font-weight:200;text-align:center;"><?php echo (int) $carbon_data['balance']['pending']['visits']; ?></td>
										<td style="font-size:4em;font-weight:200;text-align:center;"><?php echo (float) $carbon_data['balance']['pending']['carbon']; ?></td>
									</tr>
								</tbody>
							</table>
							<p>TODO: We need a text input here where users can select the number they want to offset. Default value for the field should be the pending grams. We also need a submit button so they can make the payment.</p>
						</div>

						<div class="carbon-offset-complete" style="padding:2em;background:#fff;">
							<h2 style="line-height:3;"><?php esc_html_e( 'Complete', 'carbon-offset' ); ?></h2>
							<table class="widefat">
								<thead>
									<th style="font-weight:600;text-align:center;"><?php esc_html_e( 'Visits', 'carbon-offset' ); ?></th>
									<th style="font-weight:600;text-align:center;"><?php esc_html_e( 'Carbon', 'carbon-offset' ); ?></th>
								</thead>
								<tbody>
									<tr>
										<td style="font-size:4em;font-weight:200;text-align:center;"><?php echo (int) $carbon_data['balance']['offset']['visits']; ?></td>
										<td style="font-size:4em;font-weight:200;text-align:center;"><?php echo (float) $carbon_data['balance']['offset']['carbon']; ?></td>
									</tr>
								</tbody>
							</table>
							<p>TODO: Add motivational text here and congratulations if they've already offset some of their carbon footprint.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
