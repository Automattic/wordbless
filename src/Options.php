<?php

namespace WorDBless;

class Options {

	public $options = array();

	private static $instance = null;

	private function __construct() {
		add_filter( 'alloptions', array( $this, 'get_all_options' ), 10 );
		add_filter( 'update_option', array( $this, 'update_option' ), 10, 3 );
		add_filter( 'add_option', array( $this, 'add_option' ), 10, 2 );
		add_filter( 'deleted_option', array( $this, 'delete_option' ) );

		add_filter( 'wordbless_wpdb_query', array( $this, 'filter_query' ), 10, 2 );
		$this->clear_cache_group();

	}

	public function clear_options() {
		$this->options = array();
	}

	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	/**
	 * Makes sure option is found when trying to delete it
	 *
	 * @param [type] $query_results
	 * @param [type] $query
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

	public function get_default_options() {
		return array(
			'site_url' => 'http://example.org',
			'home'     => 'http://example.org',
		);
	}

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

	public function clear_cache_group() {
		global $wp_object_cache;

		foreach ( array_keys( $wp_object_cache->cache['options'] ) as $key ) {
			wp_cache_delete( $key, 'options' );
		}

	}

}
