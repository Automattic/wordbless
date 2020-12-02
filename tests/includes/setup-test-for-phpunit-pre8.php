<?php

namespace WorDBless;

class Test_Setup_Teardown extends Test_Setup_Teardown_Base {

	public function setUp() {
		$this->setup_called = true;
	}

	public function tearDown() {
		self::$teardown_called = true;
	}

	/**
	 * @before
	 */
	public function custom_setup() {
		$this->custom_setup_called = true;
	}

	/**
	 * @after
	 */
	public function custom_teardown() {
		self::$custom_teardown_called = true;
	}

}
