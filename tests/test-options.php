<?php

namespace WorDBless;

class Test_Options extends BaseTestCase {

	public function test_add() {
		add_option( 'test', 123 );
		$this->assertEquals( 123, get_option( 'test' ) );
	}

	public function test_clean_on_tear_down() {
		$this->assertEquals( null, get_option( 'test' ) );
	}

	public function test_update() {
		add_option( 'test', 123 );
		$this->assertEquals( 123, get_option( 'test' ) );
		update_option( 'test', 456 );
		$this->assertEquals( 456, get_option( 'test' ) );
	}

	public function test_update_without_add() {
		update_option( 'test', 456 );
		$this->assertEquals( 456, get_option( 'test' ) );
	}

	public function test_delete() {
		add_option( 'test', 123 );
		$this->assertEquals( 123, get_option( 'test' ) );
		delete_option( 'test' );
		$this->assertEquals( null, get_option( 'test' ) );
	}

}
