<?php

namespace WorDBless;

class Test_Setup_Teardown extends Setup_Teardown_Base_Test {

	public function setUp(): void {
		$this->setup_called = true;
	}

	public function tearDown(): void {
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
