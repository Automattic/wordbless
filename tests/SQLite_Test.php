<?php

namespace WorDBless;

class SQLite_Test extends BaseTestCase {

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

	/**
	 * Test that the $wpdb global is an instance of WP_SQLite_DB
	 */
	public function test_wpdb_is_sqlite_instance() {
		global $wpdb;
		$this->assertInstanceOf( 'WP_SQLite_DB', $wpdb );
	}

	/**
	 * Test that WordPress tables are installed and core options are present
	 */
	public function test_wordpress_installation() {
		global $wpdb;

		// Check if essential tables exist
		$tables = array(
			$wpdb->prefix . 'options',
			$wpdb->prefix . 'users',
			$wpdb->prefix . 'posts',
			$wpdb->prefix . 'postmeta',
			$wpdb->prefix . 'terms',
			$wpdb->prefix . 'term_taxonomy',
			$wpdb->prefix . 'term_relationships',
		);

		foreach ( $tables as $table ) {
			$exists = $wpdb->get_var( "SELECT name FROM sqlite_master WHERE type='table' AND name='$table'" );
			$this->assertNotNull( $exists, "Table $table should exist" );
		}

		// Check if core options are present
		$options = array(
			'siteurl',
			'home',
			'blogname',
			'admin_email',
			'users_can_register',
			'gmt_offset',
			'timezone_string',
		);

		foreach ( $options as $option ) {
			$value = get_option( $option );
			$this->assertNotNull( $value, "Option $option should be set" );
		}

		// Verify admin_email is a valid email address
		$admin_email = get_option( 'admin_email' );
		$this->assertIsString( $admin_email, 'Admin email should be a string' );
		$this->assertIsString( is_email( $admin_email ), 'Admin email should be a valid email address' ); // is_email is funny in that it's false or the email address string.
	}

	/**
	 * Test creating a post and verifying it exists via database and REST API
	 */
	public function test_post_creation_and_retrieval() {
		global $wpdb;

		// Create a test post
		$post_data = array(
			'post_title'    => 'Test Post',
			'post_content'  => 'Test Content',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type'     => 'post',
		);

		$post_id = wp_insert_post( $post_data );

		// Verify post was created successfully
		$this->assertGreaterThan( 0, $post_id, 'Post should be created successfully' );

		// Verify post exists in database
		$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $post_id ) );
		$this->assertNotNull( $post, 'Post should exist in database' );
		$this->assertEquals( 'Test Post', $post->post_title, 'Post title should match' );
		$this->assertEquals( 'Test Content', $post->post_content, 'Post content should match' );

		// Verify post exists via REST API
		$request = new \WP_REST_Request( 'GET', '/wp/v2/posts/' . $post_id );
		$response = rest_do_request( $request );
		$this->assertEquals( 200, $response->get_status(), 'REST API should return 200 for existing post' );

		$data = $response->get_data();
		$this->assertEquals( 'Test Post', $data['title']['rendered'], 'REST API post title should match' );
		$this->assertEquals( '<p>Test Content</p>' . "\n", $data['content']['rendered'], 'REST API post content should match' );
	}
}
