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
	 * @since 1.0.0
	 * @var string
	 */
	private $option_name = 'carbon_offset';

	/**
	 * Get the option.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function get() {
		$value = (array) get_option(
			$this->option_name,
			[
				'carbon_pending' => 0,
				'carbon_offset'  => 0,
			]
		);
		return $value;
	}

	/**
	 * Add to the log
	 *
	 * @access public
	 * @since 1.0.0
	 * @param float $carbon The grams of carbon-footprint to log.
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
	 * @since 1.0.0
	 * @param array $value The value we want to save.
	 * @return void
	 */
	public function save( $value ) {
		update_option( $this->option_name, $value );
	}
}
