<?php

namespace WorDBless;

abstract class Test_Setup_Teardown_Base extends BaseTestCase {

	protected $setup_called = false;
	protected $custom_setup_called = false;
	protected static $teardown_called = false;
	protected static $custom_teardown_called = false;
	protected static $wordbless_teardown_called = false;

	public function test_setup_called() {
		$this->assertTrue( $this->setup_called, static::class . ' setup called?' );
		$this->assertTrue( $this->custom_setup_called, static::class . ' custom setup called?' );
		$this->assertNotEmpty( self::$hooks_saved, 'WorDBless setup called?' );

		return true;
	}

	/**
	 * @depends test_setup_called
	 */
	public function test_teardown_called( $setup ) {
		if ( ! $setup ) {
			$this->markTestSkipped( static::class . '::test_setup_called was not run?' );
		}

		$this->assertTrue( self::$teardown_called, static::class . ' teardown called?' );
		$this->assertTrue( self::$custom_teardown_called, static::class . ' custom teardown called?' );
		$this->assertTrue( self::$wordbless_teardown_called, 'WorDBless teardown called?' );
	}

	/**
	 * We override this as a proxy for WorDBless's teardown being called.
	 */
	protected function _restore_hooks() {
		self::$wordbless_teardown_called = true;
		parent::_restore_hooks();
	}

}

if ( class_exists( \PHPUnit\Runner\Version::class ) && version_compare( \PHPUnit\Runner\Version::id(), '8.0', '>=' ) ) {
	require 'tests/includes/setup-test-for-phpunit8+.php';
} else {
	require 'tests/includes/setup-test-for-phpunit-pre8.php';
}
