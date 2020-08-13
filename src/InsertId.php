<?php

namespace WorDBless;

class InsertId {

	public static $id = 10;

	public static function bump_and_get() {
		self::$id ++;
		return self::$id;
	}

}
