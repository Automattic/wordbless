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
        $this->assertEquals(WP_CONTENT_DIR . '/custom-uploads', UPLOADS);
    }
}
