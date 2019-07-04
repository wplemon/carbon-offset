<?php

class Log {
	private $data;
	public function __construct() {
		$this->data = new Data();
		add_action( 'wp_footer', 'log_visit' );
	}
	public function log_visit() {
		$this->data->set_visits_count( $this->data->get_visits_count() + 1 );
	}
	public function delete_visits_count() {
		$this->data->set_visits_count( 0 );
	}
}
