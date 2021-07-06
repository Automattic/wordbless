<?php

namespace WorDBless;

class Test_Posts extends BaseTestCase {

	public function test_add_post() {
		$id = wp_insert_post( array( 'post_title' => 'This is a post' ) );
		$post = get_post( $id );
		$this->assertEquals( $id, $post->ID );
		$this->assertEquals( 'This is a post', $post->post_title );
	}

	public function test_update_post() {
		$id = wp_insert_post( array( 'post_title' => 'This is a post' ) );
		$post = get_post( $id );
		$this->assertEquals( $id, $post->ID );
		$this->assertEquals( 'This is a post', $post->post_title );

		$post->post_title = 'modified';
		wp_update_post( $post );

		$post = get_post( $id );
		$this->assertEquals( 'modified', $post->post_title );
	}

	public function test_delete_post() {
		$id = wp_insert_post( array( 'post_title' => 'This is a post' ) );
		$post = get_post( $id );
		$this->assertEquals( $id, $post->ID );
		$this->assertEquals( 'This is a post', $post->post_title );

		wp_delete_post( $id, true );

		$this->assertNull( get_post( $id ) );
	}

	public function test_trash_post() {
		$id = wp_insert_post( array( 'post_title' => 'This is a post' ) );
		$post = get_post( $id );
		$this->assertEquals( $id, $post->ID );
		$this->assertEquals( 'This is a post', $post->post_title );

		wp_trash_post( $id );

		$post = get_post( $id );
		$this->assertEquals( 'trash', $post->post_status );
	}

	public function test_many_adds_updates_deletes() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );
		$id2 = wp_insert_post( array( 'post_title' => 'Post 2' ) );
		$id3 = wp_insert_post( array( 'post_title' => 'Post 3' ) );
		$id4 = wp_insert_post( array( 'post_title' => 'Post 4' ) );

		$post1 = get_post( $id1 );
		$post2 = get_post( $id2 );
		$post3 = get_post( $id3 );
		$post4 = get_post( $id4 );

		$this->assertEquals( 'Post 1', $post1->post_title );
		$this->assertEquals( 'Post 2', $post2->post_title );
		$this->assertEquals( 'Post 3', $post3->post_title );
		$this->assertEquals( 'Post 4', $post4->post_title );

		$post2->post_title = 'Modified';
		wp_update_post( $post2 );
		$this->assertEquals( 'Modified', $post2->post_title );

		wp_delete_post( $id1 ); // this will trash
		$trashed_post = get_post( $id1 );
		$this->assertEquals( 'trash', $trashed_post->post_status );

		wp_delete_post( $id3, true ); // this will delete
		$trashed_post = get_post( $id3 );
		$this->assertNull( get_post( $id3 ) );
	}

	public function test_add_get_post_meta() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		$mid = add_post_meta( $id1, 'test', 'value-1' );
		$this->assertTrue( is_int( $mid ) );

		$this->assertEquals( 'value-1', get_post_meta( $id1, 'test', true ) );
		$this->assertEquals( array( 'value-1' ), get_post_meta( $id1, 'test' ) );
	}

	public function test_add_get_serialized_post_meta() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		$mid = add_post_meta( $id1, 'test', array('value1', 'value2') );
		$this->assertTrue( is_int( $mid ) );

		$this->assertEquals( array('value1', 'value2'), get_post_meta( $id1, 'test', true ) );
		$this->assertEquals( array( array('value1', 'value2') ), get_post_meta( $id1, 'test' ) );
	}

	public function test_add_get_many_post_meta() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		add_post_meta( $id1, 'test', 'value1' );
		add_post_meta( $id1, 'test2', 'value2' );
		add_post_meta( $id1, 'test2', 'value3' );

		$this->assertEquals( 'value1', get_post_meta( $id1, 'test', true ) );

		$this->assertEquals( array('value2', 'value3'), get_post_meta( $id1, 'test2' ) );
	}

	public function test_add_get_post_meta_by_mid() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		$mid = add_post_meta( $id1, 'test', 'value-1' );
		$this->assertTrue( is_int( $mid ) );

		$this->assertEquals( 'value-1', get_metadata_by_mid( 'post', $mid ) );
	}

	public function test_untrash_post() { // depends on post meta
		global $wp_version;

		$id = wp_insert_post( array( 'post_title' => 'This is a post', 'post_status' => 'private' ) );
		$post = get_post( $id );
		$this->assertEquals( $id, $post->ID );
		$this->assertEquals( 'This is a post', $post->post_title );

		wp_trash_post( $id );

		$post = get_post( $id );
		$this->assertEquals( 'trash', $post->post_status );

		wp_untrash_post( $id );

		if ( version_compare( $wp_version, '5.6', '>=' ) ) { // https://core.trac.wordpress.org/ticket/23022
			$expected_status = 'draft';
		} else {
			$expected_status = 'private';
		}

		$post = get_post( $id );
		$this->assertEquals( $expected_status, $post->post_status );

	}

	public function test_delete_post_meta() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		add_post_meta( $id1, 'test', 'value-1' );

		delete_post_meta( $id1, 'test' );

		$this->assertEquals( [], get_post_meta( $id1, 'test' ) );

	}

	public function test_delete_post_meta_multiple() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		add_post_meta( $id1, 'test', 'value-1' );
		add_post_meta( $id1, 'test', 'value-2' );

		delete_post_meta( $id1, 'test');

		$this->assertEquals( [], get_post_meta( $id1, 'test' ) );

	}

	public function test_delete_post_meta_with_value() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		add_post_meta( $id1, 'test', 'value-1' );
		add_post_meta( $id1, 'test', 'value-2' );

		delete_post_meta( $id1, 'test', 'value-1' );

		$this->assertEquals( ['value-2'], get_post_meta( $id1, 'test' ) );

	}

	public function test_delete_post_meta_all_objects() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );
		$id2 = wp_insert_post( array( 'post_title' => 'Post 2' ) );

		add_post_meta( $id1, 'test', 'value-1' );
		add_post_meta( $id1, 'test', 'value-2' );
		add_post_meta( $id2, 'test', 'value-3' );

		delete_metadata( 'post', $id1, 'test', '', true);

		$this->assertEquals( [], get_post_meta( $id1, 'test' ) );
		$this->assertEquals( [], get_post_meta( $id2, 'test' ) );

	}

	public function test_delete_post_meta_all_objects_with_value() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );
		$id2 = wp_insert_post( array( 'post_title' => 'Post 2' ) );

		add_post_meta( $id1, 'test', 'value-1' );
		add_post_meta( $id1, 'test', 'value-2' );
		add_post_meta( $id2, 'test', 'value-2' );
		add_post_meta( $id2, 'test', 'value-3' );

		delete_metadata( 'post', $id1, 'test', 'value-2', true);

		$this->assertEquals( ['value-1'], get_post_meta( $id1, 'test' ) );
		$this->assertEquals( ['value-3'], get_post_meta( $id2, 'test' ) );

	}

	public function test_delete_post_meta_by_mid() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		add_post_meta( $id1, 'test', 'value-1' );
		$mid = add_post_meta( $id1, 'test', 'value-2' );

		delete_metadata_by_mid( 'post', $mid );

		$this->assertEquals( ['value-1'], get_post_meta( $id1, 'test' ) );

	}

	public function test_update() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		add_post_meta( $id1, 'test', 'value-1' );

		$this->assertTrue( update_post_meta( $id1, 'test', 'value-2' ) );

		$this->assertEquals( 'value-2', get_post_meta( $id1, 'test', true ) );
	}

	public function test_update_nonexisting_meta() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		$this->assertTrue( update_post_meta( $id1, 'test', 'value-2' ) );

		$this->assertEquals( 'value-2', get_post_meta( $id1, 'test', true ) );
	}

	public function test_update_post_meta_all_values() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		add_post_meta( $id1, 'test', 'value-1' );
		add_post_meta( $id1, 'test', 'value-2' );

		update_post_meta( $id1, 'test', 'new-value');

		$this->assertEquals( ['new-value', 'new-value'], get_post_meta( $id1, 'test' ) );

	}

	public function test_update_post_meta_with_prev_value() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		add_post_meta( $id1, 'test', 'value-1' );
		add_post_meta( $id1, 'test', 'value-2' );

		update_post_meta( $id1, 'test', 'new-value', 'value-1' );

		$this->assertEquals( ['new-value', 'value-2'], get_post_meta( $id1, 'test' ) );

	}

	public function test_update_post_meta_by_mid() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		$mid = add_post_meta( $id1, 'test', 'value-1' );

		update_metadata_by_mid( 'post', $mid, 'new-value' );

		$this->assertEquals( 'new-value', get_post_meta( $id1, 'test', true ) );
	}

	public function test_update_post_meta_by_mid_with_new_key() {
		$id1 = wp_insert_post( array( 'post_title' => 'Post 1' ) );

		$mid = add_post_meta( $id1, 'test', 'value-1' );

		update_metadata_by_mid( 'post', $mid, 'new-value', 'new-key' );

		$this->assertEquals( 'new-value', get_post_meta( $id1, 'new-key', true ) );
		$this->assertEmpty( get_post_meta( $id1, 'test', true ) );
	}

	public function test_get_non_existent_meta() {
		$this->assertSame( '', get_post_meta( 123123, 'asdasd', true ) );
		$this->assertSame( array(), get_post_meta( 123123, 'asdasd' ) );
	}

}
