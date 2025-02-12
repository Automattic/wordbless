<?php
namespace WorDBless;

class Test_Uploads extends BaseTestCase {
	/**
	 * @covers Load::load
	 * @covers dbless_UPLOADS
	 *
	 * This tests the custom uploads directory is set correctly.
	 * The default directory is tested in test-setup-teardown.php.
	 */
    public function test_custom_uploads_directory() {
        $this->assertEquals(WP_CONTENT_DIR . '/custom-uploads', ABSPATH . UPLOADS);
    }

	/**
	 * @covers Load::load
	 * @covers dbless_UPLOADS
	 *
	 * This tests the custom uploads directory is created correctly by Load::load.
	 *
	 * This test must run before any tests that use the uploads directory or call wp_upload_dir() which will create the uploads directory if it doesn't exist.
	 */
	public function test_custom_uploads_directory_is_created() {
		$this->assertTrue(file_exists(ABSPATH . UPLOADS));
	}

	/**
	 * @covers Load::load
	 *
	 * This test confirms that WordPress sees the custom uploads directory.
	 */
	public function test_custom_uploads_directory_is_recognized() {
		$this->assertEquals( wp_upload_dir()['path'], ABSPATH . UPLOADS);
	}
}
