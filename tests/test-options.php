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
		add_option( 'testdelete', 123 );
		$this->assertEquals( 123, get_option( 'testdelete' ) );
		delete_option( 'testdelete' );
		$this->assertEquals( null, get_option( 'testdelete' ) );
	}

	public function test_delete_non_existent() {
		$this->assertFalse( delete_option( 'non_existent' ) );
	}

	/**
	 * Assert that no error is triggered if you try to delete an option before any option is set.
	 * See #16
	 */
	public function test_delete_options_any_exists() {

		wp_cache_flush();
		delete_option( 'foo' );
		$this->assertTrue( true );

	}

	/**
	 * Test that update_option changes the value of an option that has a default value.
	 * The 'home' option has a default value, 'http://example.org'. See Options::get_default_options.
	 */
	public function test_update_option_with_default_value() {
		$new_url = 'https://www.example.com';
		update_option( 'home', $new_url );
		$this->assertSame( $new_url, get_option( 'home', null ) );
	}

}
