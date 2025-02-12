<?php

namespace WorDBless;

class Test_Attachments extends BaseTestCase {

	public function create_upload_object( $file, $parent = 0 ) {
		$contents = file_get_contents($file);
		$upload = wp_upload_bits(basename($file), null, $contents);

		$type = '';
		if ( ! empty($upload['type']) ) {
			$type = $upload['type'];
		} else {
			$mime = wp_check_filetype( $upload['file'], null );
			if ($mime) {
				$type = $mime['type'];
			}
		}

		$attachment = array(
			'post_title' => basename( $upload['file'] ),
			'post_content' => '',
			'post_type' => 'attachment',
			'post_parent' => $parent,
			'post_mime_type' => $type,
			'guid' => $upload[ 'url' ],
		);

		// Save the data
		$id = wp_insert_attachment( $attachment, $upload[ 'file' ], $parent );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

		return $id;
	}

	public function test_add_attachment() {
		$filename = TESTSPATH . '/wp-logo.jpg';
		$contents = file_get_contents($filename);

		// Add the attachment
		$upload = wp_upload_bits(basename($filename), null, $contents);

		$this->assertArrayHasKey('file', $upload, 'Upload result should contain "file" key');
		$this->assertTrue(file_exists($upload['file']), 'Uploaded file should exist');

		$id = $this->create_upload_object($filename);
		$this->assertTrue(is_int($id));
		$attachment = get_post($id);
		$this->assertEquals('attachment', $attachment->post_type);
	}

	public function test_add_attachment_with_parent() {
		$id = wp_insert_post(array('post_title' => 'Post 1'));

		// Debug the upload in create_upload_object
		$attachment_id = $this->create_upload_object(TESTSPATH . '/wp-logo.jpg', $id);

		$attachment = get_post($attachment_id);

		$this->assertEquals('attachment', $attachment->post_type);
		$this->assertEquals($id, $attachment->post_parent);

		$this->assertTrue(wp_attachment_is_image($attachment_id));
		$this->assertStringStartsWith('<img', wp_get_attachment_image($attachment_id));
	}


}
