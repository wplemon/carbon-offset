<?php
/**
 * Payments API wrapper.
 *
 * Payment classes can extend this one.
 *
 * @package CarbonOffset
 */

namespace CarbonOffset;

/**
 * Cloverly API wrapper.
 *
 * @since 1.0.0
 */
class PaymentAPI {

	/**
	 * The grams threshold for payments.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var int
	 */
	private $pay_threshold = 1000;

	/**
	 * The Data object.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var \CarbonOffset\Data
	 */
	private $data;

	/**
	 * Object constructor.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		$this->data = new Data();
	}

	/**
	 * Check if we need to make a payment, and call the pay() method if needed.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return mixed Returns the result of the pay() method if a payment was made.
	 */
	public function maybe_pay() {

		$previous_month = [
			'yeah'  => gmdate( 'Y', strtotime( 'first day of last month' ) ),
			'month' => gmdate( 'm', strtotime( 'first day of last month' ) ),
		];

		// Get data for previous month.
		$previous_month_data = $this->data->get_data( $previous_month );

		if ( $this->pay_threshold < $previous_month_data['carbon'] ) {
			$this->mark_done( $previous_month );
			return $this->pay();
		} elseif ( 0 < $previous_month_data['carbon'] ) {
			$this->mark_pending( $previous_month );
			return false;
		}
	}

	/**
	 * Mark a month as paid.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $args The arguments.
	 * @return void
	 */
	protected function mark_done( $args ) {
		$data = get_option( $this->payments_option, [] );

		if ( ! isset( $data[ $args['year'] ] ) ) {
			$data[ $args['year'] ] = [];
		}

		if ( ! isset( $data[ $args['year'] ][ $args['month'] ] ) ) {
			$data[ $args['year'] ][ $args['month'] ] = true;
		}

		update_option( $this->payments_option, $data );
	}

	/**
	 * Mark a month as pending.
	 *
	 * @access protected
	 * @since 1.0
	 * @param array $args The arguments.
	 * @return void
	 */
	protected function mark_pending( $args ) {
		$data = get_option( $this->payments_option, [] );

		if ( ! isset( $data[ $args['year'] ] ) ) {
			$data[ $args['year'] ] = [];
		}

		if ( ! isset( $data[ $args['year'] ][ $args['month'] ] ) ) {
			$data[ $args['year'] ][ $args['month'] ] = false;
		}

		update_option( $this->payments_option, $data );
	}

	/**
	 * Get the weight we need to offset for all pending months.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return float
	 */
	public function get_pending_weight() {
		$pending_months =
	}

	/**
	 * Get pending months.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_pending_months() {
		$data   = get_option( $this->payments_option, [] );
		$result = [];
		foreach ( $data as $year => $months ) {
			foreach ( $months as $month ) {
				if ( ! $month ) {
					$result[] = [ $year, $month ];
				}
			}
		}
		return $result;
	}
}
