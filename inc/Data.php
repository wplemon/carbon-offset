<?php

class Data {
	private $visits_option_name = 'carbon_offset_visits';
	public function get_visits_count() {
		return (int) get_option( $this->visits_option_name, 0 );
	}
	public function set_visits_count( $count ) {
		update_option( $this->visits_option_name, $count );
	}
}
