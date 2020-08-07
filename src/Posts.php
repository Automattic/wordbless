<?php

namespace WorDBless;

class Posts {

	public $posts = array();

	public $auto_increment = 0;

	private static $instance = null;

	private function __construct() {
		//add_action( 'wp_insert_post', array( $this, 'pos_insert_post' ), 10, 3 );
		add_filter( 'wp_insert_post_data', array( $this, 'insert_post' ), 10, 3 );
		add_filter( 'update_post_metadata_cache', '__return_true' );
		add_filter( 'update_term_metadata_cache', '__return_true' );

		add_filter( 'wordbless_wpdb_query', array( $this, 'filter_query' ), 10, 2 );
		//add_filter( 'terms_pre_query', '__return_empty_array' );

		wp_cache_flush();
	}

	public static function init() {
		if ( self::$instance === null ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function filter_query( $return, $query ) {
		global $wpdb;
		$pattern = '/^SELECT \* FROM ' . $wpdb->posts . ' WHERE ID = (\d+) LIMIT 1$/';
		preg_match( $pattern, $query, $matches );
		if( ! empty ( $matches ) ) {
			$post_id = (int) $matches[1];
			if ( isset( $this->posts[ $post_id ] ) ) {
				$return = $this->posts[ $post_id ];
			}
		}
		return $return;
	}

	public function insert_post( $data, $postarr, $unsanitized_postarr ) {
		if ( ! isset( $data['ID'] ) ) {
			$this->auto_increment ++;
			$post_ID = $this->auto_increment;
			$data['ID'] = $post_ID;
		}

		$_post = (object) sanitize_post( $data, 'raw' );
		wp_cache_add( $post_ID, $_post, 'posts' );
		$this->posts[ $post_ID ] = $_post;
		return $data;
	}

	public function pos_insert_post( $post_ID, $post, $update ) {
		$this->posts[ $post_ID ] = $post;
		$_post = sanitize_post( $post, 'raw' );
		wp_cache_add( $_post->ID, $_post, 'posts' );
	}


}
