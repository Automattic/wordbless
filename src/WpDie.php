<?php

namespace WorDBless;

class WpDie {

	use Singleton;

	private function __construct() {
		add_filter( 'wp_die_ajax_handler', array( $this, 'die_handler' ) );
		add_filter( 'wp_die_json_handler', array( $this, 'die_handler' ) );
		add_filter( 'wp_die_jsonp_handler', array( $this, 'die_handler' ) );
		add_filter( 'wp_die_xmlrpc_handler', array( $this, 'die_handler' ) );
		add_filter( 'wp_die_xml_handler', array( $this, 'die_handler' ) );
	}

	public function die_handler( $function ) {

		return [ $this, $function ];
	}

	public function _ajax_wp_die_handler( $message, $title = '', $args = array() ) {

		$args['exit'] = false;
		_ajax_wp_die_handler( $message, $title, $args );
	}

	public function _json_wp_die_handler( $message, $title = '', $args = array() ) {
		$args['exit'] = false;
		_json_wp_die_handler( $message, $title, $args );
	}

	public function _jsonp_wp_die_handler( $message, $title = '', $args = array() ) {
		$args['exit'] = false;
		_jsonp_wp_die_handler( $message, $title, $args );
	}

	public function _xmlrpc_wp_die_handler( $message, $title = '', $args = array() ) {
		$args['exit'] = false;
		_xmlrpc_wp_die_handler( $message, $title, $args );
	}

	public function _xml_wp_die_handler( $message, $title = '', $args = array() ) {
		$args['exit'] = false;
		_xml_wp_die_handler( $message, $title, $args );
	}

}
