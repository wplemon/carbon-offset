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
	public function get_option() {
		$value = (array) get_option( $this->option_name, [] );

		$current_year  = gmdate( 'Y' );
		$current_month = gmdate( 'm' );
		$current_day   = gmdate( 'd' );

		if ( ! isset( $value[ $current_year ] ) ) {
			$value[ $current_year ] = [];
		}

		if ( ! isset( $value[ $current_year ][ $current_month ] ) ) {
			$value[ $current_year ][ $current_month ] = [];
		}

		if ( ! isset( $value[ $current_year ][ $current_month ][ $current_day ] ) ) {
			$value[ $current_year ][ $current_month ][ $current_day ] = [
				'visits' => 0,
				'carbon' => 0,
			];
		}

		if ( ! isset( $value['balance'] ) ) {
			$value['balance'] = [
				'pending' => [
					'visits' => 0,
					'carbon' => 0,
				],
				'offset'  => [
					'visits' => 0,
					'carbon' => 0,
				],
				'data'    => [],
			];
		}

		return $value;
	}

	/**
	 * Add to the log
	 *
	 * @access public
	 * @since 1.0.0
	 * @param int   $visits The number of visits to log.
	 * @param float $carbon The grams of carbon-footprint to log.
	 * @return void
	 */
	public function add( $visits = 1, $carbon = 1 ) {
		static $added;

		if ( ! $added ) {
			$saved = $this->get_option();

			$current_year  = gmdate( 'Y' );
			$current_month = gmdate( 'm' );
			$current_day   = gmdate( 'd' );

			$saved[ $current_year ][ $current_month ][ $current_day ]['visits'] += $visits;
			$saved[ $current_year ][ $current_month ][ $current_day ]['carbon'] += $carbon;

			$saved['balance']['pending']['visits'] += $visits;
			$saved['balance']['pending']['carbon'] += $carbon;

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

	/**
	 * Get data.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $args The arguments for the data we want to get.
	 * @return mixed
	 */
	public function get_data( $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'year'  => 0,
				'month' => 0,
				'day'   => 0,
			]
		);

		// Get saved data.
		$data = $this->get_option();

		$results = [
			'visits' => 0,
			'carbon' => 0,
		];

		// If year is 0, get all.
		if ( 0 === $args['year'] ) {
			foreach ( $data as $year => $months_data ) {
				foreach ( $months_data as $month => $days_data ) {
					foreach ( $days_data as $day => $day_data ) {
						$results['visits'] += $day_data['visits'];
						$results['carbon'] += $day_data['carbon'];
					}
				}
			}

			return $results;
		}

		// Early exit if the year defined doesn't exist in the log.
		if ( ! isset( $data[ $args['year'] ] ) ) {
			return $results;
		}

		// If month is 0, get all for that year.
		if ( 0 === $args['month'] ) {
			$months_data = $data[ $args['year'] ];
			foreach ( $months_data as $month => $days_data ) {
				foreach ( $days_data as $day => $day_data ) {
					$results['visits'] += $day_data['visits'];
					$results['carbon'] += $day_data['carbon'];
				}
			}
		}

		// Early exit if the month defined doesn't exist in the log.
		if ( ! isset( $data[ $args['year'] ][ $args['month'] ] ) ) {
			return $results;
		}

		// If day is 0, get all for that month.
		if ( 0 === $args['day'] ) {
			$days_data = $data[ $args['year'] ][ $args['month'] ];
			foreach ( $days_data as $day => $day_data ) {
				$results['visits'] += $day_data['visits'];
				$results['carbon'] += $day_data['carbon'];
			}
		}

		// early exit if the day defined doesn't exist in the log.
		if ( ! isset( $data[ $args['year'] ][ $args['month'] ][ $args['day'] ] ) ) {
			return $results;
		}

		return $data[ $args['year'] ][ $args['month'] ][ $args['day'] ];
	}
}
