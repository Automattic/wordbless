<?php

namespace WorDBless;

class Test_Posts extends BaseTestCase {

	public function test_add_post() {
		$id = wp_insert_post( array( 'post_title' => 'This is a post' ) );
		$post = get_post( $id );
		$this->assertEquals( $id, $post->ID );
		$this->assertEquals( 'This is a post', $post->post_title );
	}

}
