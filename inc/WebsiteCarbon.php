<?php
/**
 * WebsiteCarbon API.
 *
 * @package CarbonOffset
 *
 * @since 1.0.0
 */

namespace CarbonOffset;

/**
 * API for websitecarbon.com.
 *
 * @since 1.0.0
 */
class WebsiteCarbon {

	/**
	 * API endpoint.
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $endpoint = 'https://api.websitecarbon.com/b?url=';

	/**
	 * Make the request and get the data.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_carbon_data() {
		global $wp;
		$url   = add_query_arg( $wp->query_vars, trailingslashit( home_url( $wp->request ) ) );
		$cache = get_transient( 'carbon_offset_' . md5( $url ) );

		// Cache was found, return it.
		if ( false !== $cache ) {
			return $cache;
		}

		// If cache is set to 0, we couldn't get the carbon-footprint for this URL.
		// Return the fallback value.
		$options = get_option( 'carbon_offset_settings', [] );
		if ( isset( $options['footprint'] ) ) {
			return (float) $options['footprint'];
		}

		// If we got this far, we need to ping the API and see how much this visit cost the planet.
		$response = wp_safe_remote_get( $this->endpoint . $url, [ 'timeout' => 10 ] );

		$response_code = wp_remote_retrieve_response_code( $response );
		$data          = json_decode( wp_remote_retrieve_body( $response ) );
		$is_error      = is_wp_error( $response ) || 205 < $response_code;

		// Cache the carbon.
		set_transient(
			'carbon_offset_' . md5( $url ),
			$is_error ? 0 : $data->c,
			$is_error ? DAY_IN_SECONDS : WEEK_IN_SECONDS
		);

		return $is_error ? 0 : $data->c;
	}
}
