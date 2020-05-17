<?php
/**
 * Adds the footer script for the data logger.
 *
 * @package CarbonOffset
 *
 * @since 1.0.0
 */

namespace CarbonOffset;

/**
 * Adds the footer script for the data logger.
 *
 * @since 1.0.0
 */
class FooterScript {

	/**
	 * Run.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'wp_footer', [ $this, 'print_script' ] );
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
	public function print_script() {
		$url = add_query_arg(
			[
				'action' => 'carbonOffset',
				'do'     => 'log-visit',
			],
			get_site_url()
		);

		echo '<script>';
		echo 'var fusionPingRequest=new XMLHttpRequest();';
		echo 'fusionPingRequest.open("GET","' . esc_url_raw( $url ) . '",true);';
		echo 'fusionPingRequest.send();';
		echo '</script>';
	}
}
