<?php

namespace WorDBless;

use WP_User;

class Users_Test extends BaseTestCase {

	public function test_add() {
		$id = wp_insert_user(
			array(
				'user_login' => 'zumbi',
				'user_pass'  => '123',
			)
		);

		$this->assertArrayHasKey($id, Users::init()->users );
		$this->assertSame( 'zumbi', Users::init()->users[ $id ]['user_login'] );

	}

	public function test_init_wp_user() {
		$id = wp_insert_user(
			array(
				'user_login' => 'zumbi',
				'user_pass'  => '123',
				'role'       => 'subscriber',
			)
		);

		// by id.
		$user = new WP_User( $id );

		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertSame( $id, $user->ID );
		$this->assertSame( 'zumbi', $user->user_login );

		// by login.
		$user = new WP_User( 'zumbi' );

		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertSame( $id, $user->ID );
		$this->assertSame( 'zumbi', $user->user_login );

	}

	public function test_update() {
		$id = wp_insert_user(
			array(
				'user_login' => 'zumbi',
				'user_pass'  => '123',
			)
		);

		wp_update_user( array( 'ID' => $id, 'user_email' => 'zumbi@palmares.qlb' ) );

		$this->assertArrayHasKey($id, Users::init()->users );
		$this->assertSame( 'zumbi', Users::init()->users[ $id ]['user_login'] );
		$this->assertSame( 'zumbi@palmares.qlb', Users::init()->users[ $id ]['user_email'] );
	}

	public function test_delete() {
		$id = wp_insert_user(
			array(
				'user_login' => 'zumbi',
				'user_pass'  => '123',
			)
		);

		$post = wp_insert_post(
			array(
				'post_title' => 'Test post',
				'post_content' => 'Test content',
				'post_status' => 'draft',
				'post_author' => $id
			)
		);

		$user = new WP_User( $id );
		$saved_post = get_post( $post );

		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertInstanceOf( 'WP_Post', $saved_post );
		$this->assertSame( 'zumbi', get_user_meta( $id, 'nickname', true ) );

		wp_delete_user( $id );

		$this->assertFalse( get_userdata( $id ), 'User was not deleted' );
		$this->assertNull( get_post( $post ), 'Posts from deleted user were not deleted' );
		$this->assertSame( '', get_user_meta( $id, 'nickname', true ), 'User metadata was not deleted' );

	}

	public function test_delete_reassign() {
		$id = wp_insert_user(
			array(
				'user_login' => 'zumbi',
				'user_pass'  => '123',
			)
		);

		$post = wp_insert_post(
			array(
				'post_title' => 'Test post',
				'post_content' => 'Test content',
				'post_status' => 'draft',
				'post_author' => $id
			)
		);

		$user = new WP_User( $id );
		$saved_post = get_post( $post );

		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertInstanceOf( 'WP_Post', $saved_post );

		wp_delete_user( $id, 2222 );

		$this->assertFalse( get_userdata( $id ) );

		$saved_post = get_post( $post );
		$this->assertSame( 2222, $saved_post->post_author );

	}

	public function test_capabilities() {
		$id = wp_insert_user(
			array(
				'user_login' => 'zumbi',
				'user_pass'  => '123',
				'role'       => 'author',
			)
		);

		$this->assertTrue( user_can( $id, 'read' ) );
		$this->assertTrue( user_can( $id, 'edit_posts' ) );
		$this->assertFalse( user_can( $id, 'manage_options' ) );
		$this->assertFalse( user_can( $id, 'edit_others_posts' ) );
		$this->assertFalse( user_can( $id, 'custom_cap' ) );

		$user = get_userdata( $id );
		$user->add_cap( 'custom_cap' );

		$this->assertTrue( user_can( $id, 'custom_cap' ) );
	}

	public function test_meta_capabilities() {
		$id = wp_insert_user(
			array(
				'user_login' => 'zumbi',
				'user_pass'  => '123',
				'role'       => 'author',
			)
		);

		$post = wp_insert_post(
			array(
				'post_title' => 'Test post',
				'post_content' => 'Test content',
				'post_status' => 'draft',
				'post_author' => $id
			)
		);

		$others = wp_insert_post(
			array(
				'post_title' => 'Test post 2',
				'post_content' => 'Test content 2',
				'post_status' => 'draft',
				'post_author' => 9999
			)
		);

		$this->assertTrue( user_can( $id, 'edit_post', $post ) );
		$this->assertFalse( user_can( $id, 'edit_post', $others ) );

	}

	public function test_get_user_by_dont_find() {
		$this->assertFalse( get_user_by( 'login', 'asdasd' ) );
	}

	public function test_get_non_existent_meta() {
		$this->assertSame( '', get_user_meta( 123123, 'asdasd', true ) );
		$this->assertSame( array(), get_user_meta( 123123, 'asdasd' ) );
		$this->assertSame( false, metadata_exists('user', 1234, 'asdasd') );
	}

	public function test_get_existent_meta() {
		$id = wp_insert_user(
			array(
				'user_login' => 'zumbi',
				'user_pass'  => '123',
				'role'       => 'author',
			)
		);
		$this->assertSame( false, metadata_exists('user', $id, 'test') );
		add_user_meta( $id, 'test', 'value-1' );
		$this->assertSame( true, metadata_exists('user', $id, 'test') );
	}
}
