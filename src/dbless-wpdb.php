<?php
/**
 * DBLess WPDB
 *
 * This class short-circuits original wpdb class and make it do nothing
 * while not throwing any errors.
 *
 * Place it under wp-content/db.php in your WP installation
 */
class Db_Less_Wpdb extends wpdb {

	public function __construct() {
		$this->insert_id ++;
		return;
	}

	public function set_sql_mode( $modes = array() ) {
		return;
	}

	public function select( $db, $dbh = null ) {
		return;
	}

	function _real_escape( $string ) {
		return $this->add_placeholder_escape( $string );
	}

	public function print_error( $str = '' ) {
		echo $str;
	}

	public function flush() {
		return;
	}

	public function db_connect( $allow_bail = true ) {
		return true;
	}

	public function check_connection( $allow_bail = true ) {
		return true;
	}

	public function query( $query ) {
		$query = apply_filters( 'wordbless_wpdb_query', false, $query );
		$this->last_result = false === $query ? array() : array( $query );
		$this->insert_id  = \WorDBless\InsertId::$id;
		return true;
	}

	public function get_col_charset( $table, $column ) {
		return 'UTF-8';
	}

	protected function load_col_info() {
		return;
	}

	public function bail( $message, $error_code = '500' ) {
		return false;
	}

	public function close() {
		return true;
	}

	public function has_cap( $db_cap ) {
		return 1;
	}

	public function db_version() {
		return '10';
	}
}

global $wpdb;
$wpdb = new Db_Less_Wpdb();
