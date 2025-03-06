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

	/**
	 * Test that the SQLite database class is loaded and Db_Less_Wpdb is not.
	 */
	public function test_sqlite_database_class() {
		$this->assertTrue( class_exists( 'WP_SQLite_DB' ), 'WP_SQLite_DB class should be loaded' );
		$this->assertFalse( class_exists( 'Db_Less_Wpdb' ), 'Db_Less_Wpdb class should not be loaded' );
	}

}
