<?php
/**
 * DBLess WPDB
 *
 * This class short-circuits original wpdb class and make it do nothing
 * while not throwing any errors.
 *
 * Place it under wp-content/db.php in your WP installation
 */

if ( defined( 'DB_ENGINE' ) && DB_ENGINE === 'dbless' ) {
	class Db_Less_Wpdb extends wpdb {

		public function __construct() {
			++$this->insert_id;
			return;
		}

		public function set_sql_mode( $modes = array() ) {
			return;
		}

		public function select( $db, $dbh = null ) {
			return;
		}

		public function _real_escape( $str ) {
			return $this->add_placeholder_escape( (string) $str );
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

		public function update( $table, $data, $where, $format = null, $where_format = null ) {
			$result = parent::update( $table, $data, $where, $format, $where_format );

			/**
			 * Filters the return of $wpdb->update
			 *
			 * @param int|false    $result The number of rows affected, or false on error.
			 * @param string       $table The database table.
			 * @param array        $data Data to update.
			 * @param array        $where A named array of WHERE clauses.
			 * @param array|string $format (Optional) An array of formats to be mapped to each of the values in $data. A named array of WHERE clauses.
			 * @param array|string $where_format (Optional) An array of formats to be mapped to each of the values in $where.
			 */
			return apply_filters( 'wordbless_wpdb_update', $result, $table, $data, $where, $format, $where_format );
		}

		public function insert( $table, $data, $format = null ) {
			$result = parent::insert( $table, $data, $format );

			/**
			 * Filters the return of $wpdb->insert
			 *
			 * @param int|false    $result The number of rows inserted, or false on error.
			 * @param string       $table The database table.
			 * @param array        $data Data to insert.
			 * @param array|string $format (Optional) An array of formats to be mapped to each of the values in $data. A named array of WHERE clauses.
			 */
			return apply_filters( 'wordbless_wpdb_insert', $result, $table, $data, $format );
		}

		public function delete( $table, $where, $where_format = null ) {
			$result = parent::delete( $table, $where, $where_format );

			/**
			 * Filters the return of $wpdb->delete
			 *
			 * @param int|false    $result The number of rows affected, or false on error.
			 * @param string       $table The database table.
			 * @param array        $where A named array of WHERE clauses.
			 * @param array|string $where_format (Optional) An array of formats to be mapped to each of the values in $where.
			 */
			return apply_filters( 'wordbless_wpdb_delete', $result, $table, $where, $where_format );
		}

		public function replace( $table, $data, $format = null ) {
			$result = parent::replace( $table, $data, $format );

			/**
			 * Filters the return of $wpdb->replace
			 *
			 * @param int|false    $result The number of rows affected, or false on error.
			 * @param string       $table The database table.
			 * @param array        $data The data to be inserted.
			 * @param array|string $format (Optional) An array of formats to be mapped to each of the value in $data.
			 */
			return apply_filters( 'wordbless_wpdb_replace', $result, $table, $data, $format );
		}

		public function query( $query ) {

			/**
			 * Filters the result of the query
			 *
			 * @param array  $query_results The results of the query. Must be an array. Default is an empty array
			 * @param string $query The SQL query
			 */
			$result = apply_filters( 'wordbless_wpdb_query_results', array(), $query );
			if ( ! is_array( $result ) ) {
				$result = array();
			}
			$this->last_result = $result;

			$this->insert_id = \WorDBless\InsertId::$id;
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
			return true;
		}

		public function db_version() {
			return '10';
		}

		public function db_server_info() {
			return '';
		}
	}

	global $wpdb;
	$wpdb = new Db_Less_Wpdb();
} elseif ( defined( 'DB_ENGINE' ) && DB_ENGINE === 'sqlite' ) {
	class WP_SQLite_DB extends wpdb {
		/**
		 * Constructor
		 */
		public function __construct() {
			parent::__construct( '', '', '', '' );
		}

		/**
		 * Connect to the database
		 */
		public function db_connect( $allow_bail = true ) {
			return true;
		}

		/**
		 * Check the database connection
		 */
		public function check_connection( $allow_bail = true ) {
			return true;
		}

		/**
		 * Perform a database query
		 */
		public function query( $query ) {
			// For now, just return true
			return true;
		}

		/**
		 * Get the database version
		 */
		public function db_version() {
			return '3.0.0';
		}

		/**
		 * Get the database server info
		 */
		public function db_server_info() {
			return 'SQLite';
		}

		/**
		 * Check if the database has a specific capability
		 */
		public function has_cap( $db_cap ) {
			return true;
		}

		/**
		 * Escaping temporary fix
		 */
		public function _real_escape( $data ) {
			return addslashes( $data );
		}
	}

	global $wpdb;
	$wpdb = new WP_SQLite_DB();
}
