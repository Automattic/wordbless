<?php

namespace WorDBless;

class Test_SQLite extends BaseTestCase {

	/**
	 * Test that the DB_ENGINE constant is set to 'sqlite'
	 */
	public function test_db_engine_is_sqlite() {
		$this->assertTrue( defined( 'DB_ENGINE' ), 'DB_ENGINE constant should be defined' );
		$this->assertEquals( 'sqlite', DB_ENGINE, 'DB_ENGINE should be set to "sqlite"' );
	}

}
