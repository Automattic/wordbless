<?php

namespace WorDBless;

class PostMeta {

	public $meta = array();

	public $meta_type = 'post'; // Let's prepare for a future abstraction and use a super class to handle all types of metadata

	private static $instance = null;

	private function __construct() {

		$this->column = $this->meta_type . '_id';

		add_filter( "add_{$this->meta_type}_metadata", array( $this, 'add' ), 10, 5 );
		add_filter( "get_{$this->meta_type}_metadata", array( $this, 'get' ), 10, 4 );
		add_filter( "get_{$this->meta_type}_metadata_by_mid", array( $this, 'get_by_mid' ), 10, 2 );

	}

	public static function init() {
		if ( self::$instance === null ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function key_exists_for_object( $meta_key, $object_id ) {
		if ( ! isset( $this->meta[ $object_id ] ) ) {
			return false;
		}

		foreach ( $this->meta[ $object_id ] as $meta ) {
			if ( isset( $meta[ 'meta_key' ] ) && $meta_key === $meta[ 'meta_key' ] ) {
				return true;
			}
		}

		return false;

	}

	public function add( $return, $object_id, $meta_key, $meta_value, $unique ) {

		if ( $unique && $this->key_exists_for_object( $meta_key, $object_id ) ) {
			return false;
		}

		$_meta_value = $meta_value;
		$meta_value  = maybe_serialize( $meta_value );

		if ( ! isset( $this->meta[ $object_id ] ) ) {
			$this->meta[ $object_id ] = array();
		}

		do_action( "add_{$this->meta_type}_meta", $object_id, $meta_key, $_meta_value );

		$mid = InsertId::bump_and_get();

		$this->meta[ $object_id ][]  = array(
			'mid'        => $mid,
			'meta_key'   => $meta_key,
			'meta_value' => $meta_value
		);

		do_action( "added_{$this->meta_type}_meta", $mid, $object_id, $meta_key, $_meta_value );

		return $mid;

	}

	public function get( $return, $object_id, $meta_key, $single ) {
		$return = array();
		if ( isset( $this->meta[ $object_id ] ) ) {
			foreach ( $this->meta[ $object_id ] as $meta ) {
				if ( isset( $meta[ 'meta_key' ] ) && $meta_key === $meta[ 'meta_key' ] ) {
					$return[] = maybe_unserialize( $meta['meta_value'] );
					if ( $single ) {
						break;
					}
				}
			}
		}
		return $return;
	}

	public function get_by_mid( $return, $mid ) {
		$return = false;
		foreach ( $this->meta as $object_meta ) {
			foreach ( $object_meta as $meta ) {
				if ( isset( $meta[ 'mid' ] ) && $mid === $meta[ 'mid' ] ) {
					$return = maybe_unserialize( $meta['meta_value'] );
					break;
				}
			}
		}
		return $return;
	}

}
