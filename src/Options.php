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
		wp_cache_flush();
	}

	public function init() {
		if(self::$instance === null){
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function get_default_options() {
		return array(
			'site_url' => 'http://example.org',
			'home'     => 'http://example.org',
		);
	}

	public function get_all_options( $options ) {

		$defaults = $this->get_default_options();

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

		wp_cache_flush();

		return $all_options;
	}

	public function add_option( $option, $value ) {
		$this->options[ $option ] = $value;
		wp_cache_flush();
	}

	public function update_option( $option, $old_value, $value ) {
		$this->options[ $option ] = $value;
		wp_cache_flush();
	}

	public function delete_option( $option ) {
		unset( $this->options[ $option ] );
		wp_cache_flush();
	}

}
