<?php
/**
 * Data handler.
 *
 * @package CarbonOffset
 */

namespace CarbonOffset;

/**
 * Data handler.
 *
 * @since 1.0.0
 */
class Data {

	/**
	 * The name we'll be using for the DB option.
	 *
	 * @access private
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $option_name = 'carbon_offset';

	/**
	 * Get the option.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @param int $blog_id The blog-ID on a multisite installation.
	 *
	 * @return array
	 */
	public function get( $blog_id = 0 ) {
		if ( $blog_id ) {
			switch_to_blog( $blog_id );
		}
		$value = (array) get_option(
			$this->option_name,
			[
				'carbon_pending' => 0,
				'carbon_offset'  => 0,
			]
		);
		if ( $blog_id ) {
			restore_current_blog();
		}
		return $value;
	}

	/**
	 * Add to the log
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @param float $carbon The grams of carbon-footprint to log.
	 *
	 * @return void
	 */
	public function add( $carbon = 1 ) {
		static $added;

		if ( ! $added ) {
			$saved = $this->get();

			$saved['carbon_pending'] += $carbon;

			$this->save( $saved );
			$added = true;
		}
	}

	/**
	 * Save a new option value.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @param array $value   The value we want to save.
	 * @param int   $blog_id The blog-ID in a multisite installation.
	 *
	 * @return void
	 */
	public function save( $value, $blog_id = 0 ) {
		if ( $blog_id ) {
			switch_to_blog( $blog_id );
		}
		update_option( $this->option_name, $value );
		if ( $blog_id ) {
			restore_current_blog();
		}
	}
}
