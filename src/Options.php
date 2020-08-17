<?php

namespace WorDBless;

/**
 * Implements support to Options
 */
class Options {

	use Singleton;

	/**
	 * Holds the stored options
	 *
	 * @var array
	 */
	public $options = array();

	private function __construct() {
		add_filter( 'alloptions', array( $this, 'get_all_options' ), 10 );
		add_filter( 'update_option', array( $this, 'update_option' ), 10, 3 );
		add_filter( 'add_option', array( $this, 'add_option' ), 10, 2 );
		add_filter( 'deleted_option', array( $this, 'delete_option' ) );

		add_filter( 'wordbless_wpdb_query_results', array( $this, 'filter_query' ), 10, 2 );
		$this->clear_cache_group();

	}

	/**
	 * Clear all stored options
	 *
	 * @return void
	 */
	public function clear_options() {
		$this->options = array();
	}

	/**
	 * Makes sure option is found when trying to delete it
	 *
	 * @param array  $query_results
	 * @param string $query
	 * @return void
	 */
	public function filter_query( $query_results, $query ) {
		global $wpdb;
		$pattern = '/^SELECT autoload FROM ' . preg_quote( $wpdb->options ) . ' WHERE option_name = [^ ]+$/';
		if( 1 === preg_match( $pattern, $query, $matches ) ) {
			return array( 'yes' );
		}
		return $query_results;
	}

	/**
	 * Gets the default options, always present
	 *
	 * @return array
	 */
	public function get_default_options() {
		return array(
			'site_url' => 'http://example.org',
			'home'     => 'http://example.org',
		);
	}

	/**
	 * Filters alloptions
	 *
	 * @param array $options
	 * @return array
	 */
	public function get_all_options( $options ) {

		$defaults = $this->get_default_options();

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$custom_defaults = array();
		if ( function_exists( '\dbless_default_options' ) ) {
			$custom_defaults = \dbless_default_options();
		}

		$all_options = array_merge(
			$this->options,
			$defaults,
			$options,
			$custom_defaults
		);
		$this->clear_cache_group();

		return $all_options;
	}

	public function add_option( $option, $value ) {
		$this->options[ $option ] = $value;
		$this->clear_cache_group();
	}

	public function update_option( $option, $old_value, $value ) {
		$this->options[ $option ] = $value;
		$this->clear_cache_group();
	}

	public function delete_option( $option ) {
		unset( $this->options[ $option ] );
		$this->clear_cache_group();

	}

	/**
	 * Clears the cache for the 'options' group
	 *
	 * @return void
	 */
	public function clear_cache_group() {
		global $wp_object_cache;

		foreach ( array_keys( $wp_object_cache->cache['options'] ) as $key ) {
			wp_cache_delete( $key, 'options' );
		}

	}

}
