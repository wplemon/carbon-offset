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
abstract class PaymentAPI {

	/**
	 * The grams threshold for payments.
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $pay_threshold = 1000;

	/**
	 * The Data object.
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
	 * @var \CarbonOffset\Data
	 */
	protected $data;

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
		$this->data = new Data();
	}

	/**
	 * Check if we have automatic payments enabled.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_automatic_payment_enabled() {
		// TODO: We'll have a checkbox/switch in our options to enable/disable automatic payments.
		return false;
	}

	/**
	 * Should we make an automatic payment?
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function maybe_make_automatic_payment() {
		if ( ! $this->is_automatic_payment_enabled() ) {
			return false;
		}

		if ( ! $this->data ) {
			$this->init();
		}
		$value = $this->data->get();

		return ( $this->pay_threshold < $value['carbon_pending'] );
	}

	/**
	 * Get pending weight.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return float
	 */
	public function get_pending_weight() {
		if ( ! $this->data ) {
			$this->init();
		}
		$value = $this->data->get();
		return (float) $value['carbon_pending'];
	}

	/**
	 * Did we trigger a manual payment?
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_manual_payment() {
		// TODO: This will changhe once we have the option to automate payments.
		return true;
	}

	/**
	 * Check if we need to make a payment, and call the pay() method if needed.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Returns the result of the pay() method if a payment was made.
	 */
	public function maybe_pay() {

		return ( $this->maybe_make_automatic_payment() || $this->is_manual_payment() );
	}

	/**
	 * Mark an amount as offset.
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
	 * @param float $weight The carbon weight we want to offset.
	 *
	 * @return bool
	 */
	protected function offset_weight( $weight ) {
		if ( ! $this->data ) {
			$this->init();
		}
		$value = $this->data->get();

		$value['carbon_pending'] -= $weight;
		$value['carbon_offset']  += $weight;

		return $this->data->save( $value );
	}

	/**
	 * The weight we want to offset by making a payment.
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
	 * @param float $weight The carbon weight we want to pay for.
	 *
	 * @return bool|string Return whether the payment was successful or not.
	 *                     If there was an error, return the error message.
	 */
	protected function pay( $weight ) {

		$transaction = $this->the_transaction( $weight );
		if ( $transaction ) {
			$this->offset_weight( $weight );
			return true;
		}
		return $this->get_error_message();
	}

	/**
	 * Make the transaction.
	 *
	 * @abstract
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
	 * @param float $weight The carbon weight we want to offset.
	 *
	 * @return bool
	 */
	abstract protected function the_transaction( $weight );

	/**
	 * Get the error message.
	 *
	 * @abstract
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	abstract protected function get_error_message();
}
